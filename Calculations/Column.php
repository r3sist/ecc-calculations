<?php declare(strict_types = 1);
// Analysis of RC columns according to Eurocodes - Calculation class for ECC framework
// (c) Bence VÁNKOS | https://structure.hu | https://github.com/r3sist/ecc-calculations

namespace Calculation;

use \Base;
use \Ecc\Blc;
use \Ec\Ec;
use \H3;

Class Column
{
    public Base $f3;
    private Blc $blc;
    private Ec $ec;

    public function __construct(Base $f3, Blc $blc, Ec $ec)
    {
        $this->f3 = $f3;
        $this->blc = $blc;
        $this->ec = $ec;
    }

    public function moduleColumnData(): void
    {
        $this->ec->matList('cMat', 'C30/37', ['', 'Beton anyagminőség']);
        $this->ec->spreadMaterialData($this->f3->_cMat, 'c');
        $this->ec->matList('rMat', 'B500', ['', 'Betonvas anyagminőség']);
        $this->ec->spreadMaterialData($this->f3->_rMat, 'r');

        $this->ec->wrapNumerics('a', 'b', '$a×b$ Pillér méretek', 400, 400, 'mm', '', '×');
        if (max($this->f3->_a, $this->f3->_b)/min($this->f3->_a, $this->f3->_b) > 4) {
            $this->blc->danger('$a/b > 4$: nem pillérként méretezendő!');
        }
        $this->blc->note('$a/b le 4$, min. oldalhosszúság fekve betonozott pillérnél 120 mm, állva betonozottnál 200 mm.');
        $this->blc->def('Ac', H3::n0($this->f3->_a*$this->f3->_b), 'A_(c) = %% [mm^2]');
        $this->blc->numeric('cnom', ['c_(nom)', 'Tervezett betontakarás'], 25, 'mm', '');
    }

    public function calc(Base $f3, Blc $blc, Ec $ec): void
    {
        $blc->toc();

        $this->moduleColumnData();

//        $blc->img('https://structure.hu/ecc/column0.jpg');
        $blc->numeric('l', ['l', 'Pillér hálózati magassága'], 3, 'm');
        $blc->numeric('upsilon', ['upsilon', 'Kihajlási hossz tényező'], 0.7, '', '');
        $blc->def('l0', $f3->_upsilon*$f3->_l, 'l_0 = upsilon*l = %% [m]', 'Kihajlási hossz');

        $blc->numeric('NEd', ['N_(Ed)', 'Nyomóerő'], 1000, 'kN', '');

        // =============================================================================================================
        $blc->h1('Közelítő méretfelvétel');

        $blc->def('Acmin', H3::n0($f3->_NEd/$f3->_cfcd*1000), 'A_(c,min) = N_(Ed)/(f_(c,d)) = %% [mm^2]', 'Szükséges beton keresztmetszet');
        $blc->math('a = '.$f3->_a.' [mm]', 'Felvett egyik méret');
        $blc->def('bmin', ceil(H3::n0($f3->_Acmin/$f3->_a)), 'b_(min) = A_(c,min)/a = %% [mm]', 'Szükséges másik méret');
        $blc->txt('Szükséges vasmennyiségek **'.$f3->_a.' × '.$f3->_b.'** keresztmetszethez:');
        $blc->def('A_s_min', H3::n0(max(0.003*$f3->_Ac, (($f3->_NEd*1000)/$f3->_rfyd)*0.1)), 'A_(s, min) = max{(0.1*N_(Ed)/f_(yd)),(0.003*A_c):} = %% [mm^2]', '');
        $blc->def('A_s_max', H3::n0(0.04*$f3->_Ac), 'A_(s, max) = %% [mm^2]', 'Maximális vasmennyiség: 4%');

        // =============================================================================================================
        $blc->h1('Központosan nyomott pillérek teherbírása', 'Egyszerűsített módszer, $e_e := 0$');
        $blc->note('*[Vasbeton Szerkezetek 2016 6.6.3 B)]*');
        $blc->math('N_(Rd) := varphi*N\'_u', 'Nyomási teherbírás számítása, ahol $varphi$ a kihajlási csökkentő tényező és $N\'_u$ a km. névleges képlékeny teherbírása, $N\'_u = A_c*f_(cd) + A_s*f_(yd)$');

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
            array_push($rows, [$key, $value]);
        }
        $blc->tbl(['$a [mm]$', '$varphi_(max)$'], $rows, 'phiMaxTable', '');
        $blc->def('phiMax', $ec->getClosest($f3->_a, $phiMaxStack, $returnType = 'floor'), 'varphi_(max) = %%', 'Keresés alsó értékhez');

        $blc->lst('reinforcing', ['2 sávban elhelyezett vasalás' => 2, '3 vagy több sávban elhelyezett vasalás' => 3], ['', 'Vasalás']);

        $sigma = 0.94;
        if ($f3->cfck <= 30) {
            $sigma = 0.8;
        }
        if ($f3->_reinforcing == 3) {
            $sigma = 0.99;
            if ($f3->cfck <= 30) {
                $sigma = 0.94;
            }
        }
        $scheme = ['Téglalap km. 2 sávban elhelyezett vasalás', 'Téglalap km. 3 vagy több sávban elhelyezett vasalás'];
        $rows = [[0.8, 0.94],[0.94, 0.99]];
        $blc->tbl($scheme, $rows, 'Beton', '$sigma$ javasolt értékei');
        $blc->def('sigma', $sigma, 'sigma = %%');
        $blc->def('phi', min($f3->_phiMax, min(0.85, 1.1 - $f3->_sigma*((($f3->_l0*1000)/$f3->_a)/30))), 'varphi = min{(varphi_(max)), (min{(0.85), (1.1 - sigma*(l_0/a)/30):}):} = %%');
        if (($f3->_l0*1000)/$f3->_a > 30) {
            $blc->danger('$(l_0/a = '.($f3->_l0*1000)/$f3->_a.') > 30$');
        } else {
            $blc->math('(l_0/a = '.($f3->_l0*1000)/$f3->_a.') le 30');
        }

        $f3->_As = $ec->rebarTable('AS');
        $blc->math('A_s = '.$f3->_As.' [mm^2]');
        $blc->def('NRd', floor(($f3->_Ac*$f3->_cfcd + $f3->_As*$f3->_rfyd)*$f3->_phi*0.001), 'N_(Rd) = varphi*N\'_u = varphi*(A_c*f_(cd) + A_s*f_(yd)) = %% [kN]');
        $blc->label($f3->_NEd/$f3->_NRd, 'Kihasználtság');

        // =============================================================================================================
        $blc->h1('Kengyelezés ellenőrzése');
        $ec->rebarList('phiMin', 16, ['', 'Legkisebb hosszvas átmérő']);
        $blc->def('ssmax', floor(min(12*$f3->_phiMin, min($f3->_a, $f3->_b), 400)), 's_(s,max) = min{(12*phi_(min)),(min(a,b)),(400):} = %% [mm]', 'Maximális kengyeltávolság');
        $blc->note('$12$ szorzó MSZ és DIN szerint. EC $20$, MSZ-EN $15$');
        $blc->txt('Erőbevezetésnél, lemezcsatlakozásnál, hosszvas toldásnál: $s_(s,max,d) = 0.6*s_(s,max) = '. floor(0.6*$f3->_ssmax).'[mm]$');

        // =============================================================================================================
        $blc->h1('Imperfekció', 'Mértékadó normálerőkből számított imperfekciós vízszintes terhelés');
        $blc->note('*[Vasbeton szerkezetek (2016) 5.2.]*');
        $blc->note('Tartószerkezet geometriai pontatlanságának figyelembe vétele a merevítőrendszert terheli.');
        $blc->note('+x +y -x -y irányokban is hathat.');
        $blc->numeric('Phi0', ['Phi_0', 'Elfordulás alapértéke'], 0.005, '', 'Alapértelmezetten 1/200');
        $blc->numeric('m', ['m', 'Egy szinten lévő pillérek száma'], 10, 'db', '');
        $blc->def('alpham', sqrt(0.5*(1 + 1/$f3->_m)), 'alpha_m = sqrt(0.5*(1+1/m)) = %%', 'Csökkentő tényező oszlopszámtól függően');
        $blc->def('alphan', min(1, max(0.6667, 2/sqrt($f3->_l))), 'alpha_n = min{(1),( max{(2/sqrt(l) = '. 2/sqrt($f3->_l).'),(0.667):}):} = %%', '');
        $blc->note('$l$ az épület teljes magassága');
        $blc->def('Phi', $f3->_alpham*$f3->_alphan*$f3->_Phi0, 'Phi = alpha_m*alpha_n*Phi_0 = %%', 'Elfordulás oszlopszámtól függő értéke');
//        $blc->lst('upsilon', ['Merevített szerkezet (1)' => 1, 'Kilendülő szerkezet (2)' => 2], ['upsilon', 'Oszlop kihajlási fél-hullámhossza'], 1);
        $blc->def('l0', $f3->_l*$f3->_upsilon, 'l_0 = upsilon*l = %% [m]', 'Kihajlási hossz');
        $blc->def('e', H3::n1($f3->_l0*1000*$f3->_Phi), 'e_(calc) = l_0*Phi = %% [mm] = l/'. H3::n0(($f3->_l*1000)/ H3::n1($f3->_l0*1000*$f3->_Phi)), 'Egyenértékű elmozdulás');
        $blc->note('Egyszerűsítésként $l/400$ használatát engedi a szabvány');
        $blc->note('A vsz. többleterők eredője lehet szintenként egy eltolóerő, vagy ± irányból származó csavarónyomaték');

        $fields = [
            ['name' => 'name', 'title' => 'Pillér csoport neve', 'type' => 'input', 'sum' => false],
            ['name' => 'N', 'title' => '$Delta N; sum N [kN]$', 'type' => 'input', 'sum' => false],
            ['name' => 'db', 'title' => 'darab', 'type' => 'input', 'sum' => true],
            ['name' => 'H', 'title' => '$H [kN]$', 'type' => 'value', 'key' => 'H0', 'sum' => false],
            ['name' => 'SH', 'title' => '$sum H [kN]$', 'type' => 'value', 'key' => 'SH', 'sum' => true],
        ];
        if ($f3->exists('POST._imperfections')) {
            foreach ($f3->get('POST._imperfections') as $key => $value) {
                $f3->set("POST._imperfections.$key.H0", H3::n2((float)$value['N']*$f3->_Phi));
                $f3->set("POST._imperfections.$key.SH", H3::n2((float)$value['db']* H3::n2((float)$value['N']*$f3->_Phi)));
            }
        }
        $blc->bulk('imperfections', $fields);
        $blc->txt('', 'Vízszintes erők: $H_i = N*Phi$.');
    }
}
