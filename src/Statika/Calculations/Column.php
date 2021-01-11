<?php declare(strict_types = 1);
/**
 * Analysis of RC columns according to Eurocodes - Calculation class for Statika framework
 * (c) Bence VÁNKOS | https://structure.hu | https://github.com/r3sist/ecc-calculations
 */

namespace Statika\Calculations;

use Base;
use \H3;
use Statika\EurocodeInterface;

Class Column
{
    private EurocodeInterface $ec;
    private Base $f3;

    /**
     * Column constructor.
     * @param Ec $ec
     */
    public function __construct(EurocodeInterface $ec, Base $f3)
    {
        $this->ec = $ec;
        $this->f3 = $f3;
    }

    public function moduleColumnData(): void
    {
        $this->ec->concreteMaterialListBlock('concreteMaterialName', 'C30/37');
        $this->ec->rebarMaterialListBlock('rebarMaterialName');

        $this->ec->wrapNumerics('a', 'b', '$a×b$ Pillér méretek', 400, 400, 'mm', '', '×');
        if (max($this->ec->a, $this->ec->b)/min($this->ec->a, $this->ec->b) > 4) {
            $this->ec->danger('$a/b > 4$: nem pillérként méretezendő!');
        }
        $this->ec->note('$a/b le 4$, min. oldalhosszúság fekve betonozott pillérnél 120 mm, állva betonozottnál 200 mm.');
        $this->ec->def('Ac', H3::n0($this->ec->a*$this->ec->b), 'A_c = %% [mm^2]');
        $this->ec->numeric('cnom', ['c_(nom)', 'Tervezett betontakarás'], 25, 'mm', '');
    }

    /**
     * @param Ec $ec
     */
    public function calc(EurocodeInterface $ec): void
    {
        $this->moduleColumnData();

        $ec->img('https://structure.hu/ecc/column0.jpg');
        $ec->numeric('l', ['l', 'Pillér hálózati magassága'], 3, 'm');
        $ec->numeric('upsilon', ['upsilon', 'Kihajlási hossz tényező'], 0.7, '', '');
        $ec->def('l0', $ec->upsilon*$ec->l, 'l_0 = upsilon*l = %% [m]', 'Kihajlási hossz');

        $ec->numeric('NEd', ['N_(Ed)', 'Nyomóerő'], 1000, 'kN', '');

        // =============================================================================================================
        $ec->h1('Közelítő méretfelvétel');

        $concreteMaterial = $ec->getMaterial($ec->concreteMaterialName);
        $rebarMaterial = $ec->getMaterial($ec->rebarMaterialName);

        $ec->def('Acmin', H3::n0($ec->NEd/$concreteMaterial->fcd*1000), 'A_(c,min) = N_(Ed)/(f_(c,d)) = %% [mm^2]', 'Szükséges beton keresztmetszet');
        $ec->math('a = '.$ec->a.' [mm]', 'Felvett egyik méret');
        $ec->def('bmin', ceil(H3::n0($ec->Acmin/$ec->a)), 'b_(min) = A_(c,min)/a = %% [mm]', 'Szükséges másik méret');
        $ec->txt('Szükséges vasmennyiségek **'.$ec->a.' × '.$ec->b.'** keresztmetszethez:');
        $ec->def('A_s_min', H3::n0(max(0.003*$ec->Ac, (($ec->NEd*1000)/$rebarMaterial->fyd)*0.1)), 'A_(s, min) = max{(0.1*N_(Ed)/f_(yd)),(0.003*A_c):} = %% [mm^2]', '');
        $ec->def('A_s_max', H3::n0(0.04*$ec->Ac), 'A_(s, max) = %% [mm^2]', 'Maximális vasmennyiség: 4%');

        // =============================================================================================================
        $ec->h1('Központosan nyomott pillérek teherbírása', 'Egyszerűsített módszer, $e_e := 0$');
        $ec->note('*[Vasbeton Szerkezetek 2016 6.6.3 B)]*');
        $ec->math('N_(Rd) := varphi*N\'_u', 'Nyomási teherbírás számítása, ahol $varphi$ a kihajlási csökkentő tényező és $N\'_u$ a km. névleges képlékeny teherbírása, $N\'_u = A_c*f_(cd) + A_s*f_(yd)$');

        $phiMaxStack = [
            150 => 0.6,
            200 => 0.68,
            250 => 0.73,
            300 => 0.77,
            400 => 0.81,
            500 => 0.84,
            600 => 0.87,
        ];
        $rows = [];
        foreach ($phiMaxStack as $key => $value) {
            $rows[] = [$key, $value];
        }
        $ec->tbl(['$a [mm]$', '$varphi_(max)$'], $rows, 'phiMaxTable', '');
        $ec->def('phiMax', $ec->getClosest($ec->a, $phiMaxStack, $returnType = 'floor'), 'varphi_(max) = %%', 'Keresés alsó értékhez');

        $ec->lst('reinforcing', ['2 sávban elhelyezett vasalás' => 2, '3 vagy több sávban elhelyezett vasalás' => 3], ['', 'Vasalás']);

        $sigma = 0.94;
        if ($concreteMaterial->fck <= 30) {
            $sigma = 0.8;
        }
        if ($ec->reinforcing == 3) {
            $sigma = 0.99;
            if ($concreteMaterial->fck <= 30) {
                $sigma = 0.94;
            }
        }
        $scheme = ['Téglalap km. 2 sávban elhelyezett vasalás', 'Téglalap km. 3 vagy több sávban elhelyezett vasalás'];
        $rows = [[0.8, 0.94],[0.94, 0.99]];
        $ec->tbl($scheme, $rows, 'Beton', '$sigma$ javasolt értékei');
        $ec->def('sigma', $sigma, 'sigma = %%');
        $ec->def('phi', min($ec->phiMax, min(0.85, 1.1 - $ec->sigma*((($ec->l0*1000)/$ec->a)/30))), 'varphi = min{(varphi_(max)), (min{(0.85), (1.1 - sigma*(l_0/a)/30):}):} = %%');
        if (($ec->l0*1000)/$ec->a > 30) {
            $ec->danger('$(l_0/a = '.($ec->l0*1000)/$ec->a.') > 30$');
        } else {
            $ec->math('(l_0/a = '.($ec->l0*1000)/$ec->a.') le 30');
        }

        $ec->As = $ec->rebarTable('AS');
        $ec->math('A_s = '.$ec->As.' [mm^2]');
        $ec->def('NRd', floor(($ec->Ac*$concreteMaterial->fcd + $ec->As*$rebarMaterial->fyd)*$ec->phi*0.001), 'N_(Rd) = varphi*N\'_u = varphi*(A_c*f_(cd) + A_s*f_(yd)) = %% [kN]');
        $ec->label($ec->NEd/$ec->NRd, 'Kihasználtság');

        // =============================================================================================================
        $ec->h1('Kengyelezés ellenőrzése');
        $ec->rebarList('phiMin', 16, ['', 'Legkisebb hosszvas átmérő']);
        $ec->def('ssmax', floor(min(12*$ec->phiMin, min($ec->a, $ec->b), 400)), 's_(s,max) = min{(12*phi_(min)),(min(a,b)),(400):} = %% [mm]', 'Maximális kengyeltávolság');
        $ec->note('$12$ szorzó MSZ és DIN szerint. EC $20$, MSZ-EN $15$');
        $ec->txt('Erőbevezetésnél, lemezcsatlakozásnál, hosszvas toldásnál: $s_(s,max,d) = 0.6*s_(s,max) = '. floor(0.6*$ec->ssmax).'[mm]$');

        // =============================================================================================================
        $ec->h1('Imperfekció', 'Mértékadó normálerőkből számított imperfekciós vízszintes terhelés');
        $ec->note('*[Vasbeton szerkezetek (2016) 5.2.]*');
        $ec->note('Tartószerkezet geometriai pontatlanságának figyelembe vétele a merevítőrendszert terheli.');
        $ec->note('+x +y -x -y irányokban is hathat.');
        $ec->numeric('Phi0', ['Phi_0', 'Elfordulás alapértéke'], 0.005, '', 'Alapértelmezetten 1/200');
        $ec->numeric('m', ['m', 'Egy szinten lévő pillérek száma'], 10, 'db', '');
        $ec->def('alpham', sqrt(0.5*(1 + 1/$ec->m)), 'alpha_m = sqrt(0.5*(1+1/m)) = %%', 'Csökkentő tényező oszlopszámtól függően');
        $ec->def('alphan', min(1, max(0.6667, 2/sqrt($ec->l))), 'alpha_n = min{(1),( max{(2/sqrt(l) = '. 2/sqrt($ec->l).'),(0.667):}):} = %%', '');
        $ec->note('$l$ az épület teljes magassága');
        $ec->def('Phi', $ec->alpham*$ec->alphan*$ec->Phi0, 'Phi = alpha_m*alpha_n*Phi_0 = %%', 'Elfordulás oszlopszámtól függő értéke');
//        $ec->lst('upsilon', ['Merevített szerkezet (1)' => 1, 'Kilendülő szerkezet (2)' => 2], ['upsilon', 'Oszlop kihajlási fél-hullámhossza'], 1);
        $ec->def('l0', $ec->l*$ec->upsilon, 'l_0 = upsilon*l = %% [m]', 'Kihajlási hossz');
        $ec->def('e', H3::n1($ec->l0*1000*$ec->Phi), 'e_(calc) = l_0*Phi = %% [mm] = l/'. H3::n0(($ec->l*1000)/ H3::n1($ec->l0*1000*$ec->Phi)), 'Egyenértékű elmozdulás');
        $ec->note('Egyszerűsítésként $l/400$ használatát engedi a szabvány');
        $ec->note('A vsz. többleterők eredője lehet szintenként egy eltolóerő, vagy ± irányból származó csavarónyomaték');

        $fields = [
            ['name' => 'name', 'title' => 'Pillér csoport neve', 'type' => 'input', 'sum' => false],
            ['name' => 'N', 'title' => '$Delta N; sum N [kN]$', 'type' => 'input', 'sum' => false],
            ['name' => 'db', 'title' => 'darab', 'type' => 'input', 'sum' => true],
            ['name' => 'H', 'title' => '$H [kN]$', 'type' => 'value', 'key' => 'H0', 'sum' => false],
            ['name' => 'SH', 'title' => '$sum H [kN]$', 'type' => 'value', 'key' => 'SH', 'sum' => true],
        ];
        if ($this->f3->exists('POST.imperfections')) {
            foreach ($this->f3->get('POST.imperfections') as $key => $value) {
                $this->f3->set("POST.imperfections.$key.H0", H3::n2((float)$value['N']*$ec->Phi));
                $this->f3->set("POST.imperfections.$key.SH", H3::n2((float)$value['db']* H3::n2((float)$value['N']*$ec->Phi)));
            }
        }
        $ec->bulk('imperfections', $fields);
        $ec->txt('', 'Vízszintes erők: $H_i = N*Phi$.');
    }
}
