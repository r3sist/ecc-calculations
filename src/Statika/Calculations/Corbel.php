<?php declare(strict_types = 1);
/**
 * Analysis RC columns' corbels according to Eurocodes - Calculation class for Statika framework
 * (c) Bence VÁNKOS | https://structure.hu | https://github.com/r3sist/ecc-calculations
 */

namespace Statika\Calculations;

use \H3;
use Statika\Ec;
use Statika\EurocodeInterface;

Class Corbel
{
    private Column $columnCalculation;
    private Concrete $concreteCalculation;

    public function __construct(Column $columnCalculation, Concrete $concreteCalculation)
    {
        $this->columnCalculation = $columnCalculation;
        $this->concreteCalculation = $concreteCalculation;
    }

    /**
     * @param Ec $ec
     */
    public function calc(EurocodeInterface $ec): void
    {
        $ec->note('Lásd még: *EC2 6.5.: Tervezés rácsmodellek alapján.*');

        $this->columnCalculation->moduleColumnData();
        $ec->txt('', '$b$ méret a rövidkonzol szélessége');
        $rebarMaterial = $ec->getMaterial($ec->rebarMaterialName);
        $concreteMaterial = $ec->getMaterial($ec->concreteMaterialName);

        $ec->region0('img', 'Elrendezési képek');
            $ec->img('https://structure.hu/ecc/corbel0.jpg', 'Rövidkonzol erőjáték');
            $ec->img('https://structure.hu/ecc/corbel1.jpg', 'Rövidkonzol vasalás');
            $ec->img('https://structure.hu/ecc/corbel3.jpg', 'Felső csomópont');
        $ec->region1();

        $ec->numeric('ac', ['a_c', 'Terheló függőleges erő hatásvonala az oszlop szélétől'], 125, 'mm', '');
        $ec->numeric('hc', ['h_c', 'Konzol magassága'], 250, 'mm', '');
        $ec->math('b = '.$ec->b.' [mm]', 'Rövidkonzol szélessége ($=$ oszlop $b$ szélessége)');
        $ec->note('A továbbiakban feltételezzük, hogy a rövidkonzol keresztirányú $b$ mérete minden vizsgált részén azonos, továbbá a $b$ betonszélességen szimmetrikusan elhelyezett teherolsztó lemez keresztirányú $t$ méretére teljesül a $t >= b - 2*s_0$ feltétel, ahol $s_0$ a húzott fővasak felső oldali betonfedésének és fél vasátmérőjének az összege. Ellenkező esetben ügyelni kell a $b$ szélesség csomópontonkénti helyes felvételére és a vasalás hatékony zónában történő elhelyezésére.');
        $ec->numeric('FEd', ['F_(Ed)', 'Függőleges erő'], 500, 'kN');
        $ec->numeric('HEd', ['H_(Ed)', 'Vízszintes erő'], 50, 'kN');
        if ($ec->HEd < 0.1*$ec->FEd) {
            $ec->danger('Javasolt $H_(Ed,min) = 0.1*F_(Ed) = '. 0.1*$ec->FEd.' [kN]$');
        }
        if ($ec->HEd > 0.2*$ec->FEd) {
            $ec->danger('Javasolt $H_(Ed,max) = 0.2*F_(Ed) = '. 0.2*$ec->FEd.' [kN]$');
        }
        $ec->def('alpha', $ec->HEd/$ec->FEd, 'alpha = H_(Ed)/F_(Ed) = '.($ec->HEd/$ec->FEd)*100 .' [%]', 'Függőleges - vízszintes erő aránya');

        $ec->rebarList('phiwh', 10, ['phi_(s,w,h)', 'Felvett vízszintes kengyel vasátmérő'], '');
        $ec->rebarList('phiwv', 12, ['phi_(s,w,v)', 'Felvett függőleges kengyel vasátmérő'], '');
        $ec->rebarList('phiMain', 20, ['phi_(s,mai n)', 'Felvett hurkos fővas átmérő *(A)*'], '');
        $ec->lst('mainType', ['Hurkos fővas, lehorgonyzás hagyományosan' => 'hook', 'Csapos szerelvény kialakítás' => 'custom'], ['', 'Húzott fővas kialakítása'], 'hook');
        if ($ec->mainType === 'hook') {
            $ec->def('nMain', 2, 'n_(s,mai n) = %%', 'Hurkos fővasnak két szára van.');
            $ec->note('Nagyon széles konzolba beférne 2 keskeny hurkos vas egymás mellé egy sorba, de itt csak kettővel számol.');
        } else {
            $nMainMax = ($ec->b - 2*$ec->cnom - 2*$ec->phiwv - 2*$ec->phiwh + 2*$ec->phiMain)/(3*$ec->phiMain);
            $ec->def('nMainMax', floor($nMainMax), 'n_(s,mai n, max) = (b-2*c_(nom) - 2*phi_(s,w,v) - 2*phi_(s,w,h) + 2*phi_(s,mai n))/(3*phi_(s,mai n)) = floor('. H3::n2($nMainMax).') = %%', 'Fővasak $2*phi$ távolságra egymástól, vízszintes kengyelen belül');
            $ec->numeric('nMain', ['n_(s,mai n)', 'Alkalmazott $phi '.$ec->phiMain.'$ hurkos fővasak száma egy sorban a szerelvényben'], 2);
        }

        $ec->numeric('nMainRow', ['n_(s,mai n,row)', 'Alkalmazott $phi '.$ec->phiMain.'$ hurkos fővas sor'], 1);
        $ec->def('s0', $ec->s0 = $ec->cnom + $ec->phiMain/2, 's_0 = c_(nom) + 2*phi_(s,mai n) = %% [mm]');
        $ec->def('tp', 10, 't_p = 10 [mm]', 'Teherelosztó lemez vastagsága *(E)*');
        $ec->numeric('ap', ['a_p', 'Lemez hossz pillér $b$ szélességére merőlegesen'], 200, 'mm');

        $ec->def('deltaMain', max(24 + $ec->phiMain, 2*$ec->phiMain), 'Delta_(s,mai n) = %% [mm]', '2 fővas sor közti távolság, $24 [mm]$ max szemcseátmérőre vagy $2 phi$-re');
        $ec->def('delta', 10, 'delta = 10 [mm]', 'Véletlen vaselmozdulás');
        $ec->def('d', H3::n0($ec->hc - $ec->cnom - $ec->phiwv - ($ec->nMainRow*$ec->phiMain + ($ec->nMainRow - 1)*$ec->deltaMain)/2) - $ec->delta, 'd = h_c - c_(nom) - phi_(s,w,v) - (n_(s,mai n,row)*phi_(s,mai n) + (n_(s,mai n,row) - 1)*Delta_(s,mai n))/2 - delta = %% [mm]', 'Hatékony magasság húzott vaskép tengelyében');
        $ec->def('aH', ($ec->hc - $ec->d) + $ec->tp, 'a_H = (h_c - d) - t_p = %% [mm]', 'Vízszintes erő hatásvonala húzott vasaktól');

        $ec->info0();
            $ec->def('bp', $ec->b - 2*$ec->s0, 'b_(p,min) >= b - 2*(c_(nom) + phi_(s,mai n)/2) = %% [mm]', 'Teherelosztó lemez minimum hossza pillér $b$ szélességével párhuzamosan');
            $ec->def('aMin', ceil($ec->ac + $ec->ap*0.5 + 2*$ec->s0 + sqrt(2)*($ec->hc - $ec->d)), 'a_(min) = a_c + a_p/2 + 2*s_0 + sqrt(2)*(h_c - d) = %% [mm]', 'Konzol mélység minimális mérete, hurkos fővas lehorgonyzásához rövidkonzol külső oldalán');
        $ec->info1();
        $ec->note('A teherelosztó lemez bektösének a súrlódás figyelembevétele nélkül fel kell tudnia venni $H_(Ed)$ erőt.');


        $ec->h1('Nyomott rácsrúd ellenőrzése', '(1)');
        $ec->note('Az elérhető legnagyobb teherbírás a még lehetséges, illetve megengedett legnagyobb $theta$ szöghöz tartozik.');

        $ec->def('k1', 1, 'k_1 = %%');
        $ec->def('upsilon', 1 - $concreteMaterial->fck/250, 'upsilon = 1 - f_(ck)/250 = %%');
        $ec->def('sigmaRdmax', $ec->k1*$ec->upsilon*$concreteMaterial->fcd, 'sigma_(Rd,max) = k_1*upsilon*f_(cd) = %% [N/(mm^2)]', 'Helyi nyomószilárdság');
        $ec->note('*(1)* csomópont mindhárom lapján egyforma nyomófeszültségek lépnek fel, a legmeredekebb $theta$ szögnél megegyezik a fenti helyi nyomószilárdsággal.');

        $ec->def('x', H3::n2($ec->FEd/($ec->b*$ec->sigmaRdmax)*1000), 'x = F_(Ed)/(b*sigma_(Rd,max)) = %% [mm]', 'Derékszögű betonék $x$ mérete függőleges vetületi egyenletből');
        $ec->math('tan(theta) = x/y = (d - y/2)/(x/2 + a_c + alpha*a_H ) to y to theta', 'Derékszögű betonék $y$ méretének meghatározása geometriai viszonyokból');
        $qa = -0.5;
        $qb = $ec->d;
        $qc = -1*(0.5* ($ec->x ** 2) + $ec->ac*$ec->x + $ec->alpha*$ec->aH*$ec->x);
        $roots = $ec->quadratic($qa, $qb, $qc, 4);
        $ec->def('y1', $roots[0], 'y1 = %%');
        $ec->def('y2', $roots[1], 'y2 = %%');



        $ec->def('y', H3::n2($ec->chooseRoot($ec->x, [$ec->y1, $ec->y2])), 'y = %% [mm]', 'Fizikailag lehetséges gyök választása');
        $ec->def('theta', rad2deg(atan($ec->x/$ec->y1)), 'theta = %% [deg]', '$N_c$ nyomott rácsrúd ferdesége');
        if ($ec->theta <= 45) {
            $ec->danger0();
                $ec->txt('$theta = '.H3::n2($ec->theta).' [deg]$: $45$ és $68 [deg]$ között kell lennie!');
                $ec->txt('A ferde nyomóerő túl lapos. A rövidkonzol magassága vagy szélessége növelendő.');
                $ec->note('$f_(cd)$ növelése nem gazdaságos.');
            $ec->danger1();
        }
        if ($ec->theta > 68) {
            $ec->danger0();
                $ec->txt('$theta = '.$ec->theta.' [deg]$, de $45$ és $68 [deg]$ között kell lennie!');
                $ec->txt('A rövidkonzol teljes magassága nem használható ki, a betonék túl magasra kerül.');
                $ec->def('theta', 68, 'theta := %% [deg]', '$theta$ felülírása.');
                $ec->def('y', H3::n2($ec->x/2.5), 'y = x/2.5 = %% [mm]', 'Derékszögű betonék $y$ mérete');
            $ec->danger1();
        }

        $ec->def('z0', floor(tan(deg2rad($ec->theta))*($ec->ac + $ec->alpha*$ec->aH)), 'z_0 = tan(theta)*(a_c + alpha*a_H) = %% [mm]');
        if ($ec->ac <= $ec->z0) {
            $ec->txt('$a_c < = z_0$, a vasbeton konzol rövidkonzolként méretezhető.');
        } else {
            $ec->danger('$a_c > z_0$, a vasbeton konzol nem méretezhető rövidkonzolként.');
        }

        $ec->def('zNc', H3::n1(($ec->d - $ec->z0)*cos(deg2rad($ec->theta))), 'z_(Nc) = (d - z_0)*cos(theta) = %% [mm]', '$N_c$ hatásvonala a rövidkonzol belső-alsó sarkától');
        $ec->def('NRd', H3::n1((2*$ec->zNc*$ec->b*$concreteMaterial->fcd)/1000), 'N_(Rd) = 2*z_(Nc)*b*f_(cd) = %% [kN]', 'Ferde beton rácsrúd által felvehető erő');
        $ec->def('Nc', H3::n1($ec->FEd/sin(deg2rad($ec->theta))), 'N_c = F_(Ed)/sin(theta) = %% [kN]', 'Függőleges vetületi egyensúlyi egyenlet');
        if($ec->Nc < $ec->NRd) {
            $ec->label('yes', ''. H3::n3($ec->Nc/$ec->NRd)*100 .' % Kihasználtság');
        } else {
            $ec->label('no', 'Nem felel meg, konzolmagasság növelése szükséges');
        }

        $ec->h1('Húzott fővas ellenőrzése');
        $ec->def('Fs1', ceil(H3::n2($ec->y*$ec->b*$ec->sigmaRdmax + $ec->HEd*1000)/1000), 'F_(s,1) = y*b*sigma_(Rd,max) + H_(Ed) = %% [kN]', 'Vasalással felvett erő nyomott rácsrúd teherbírásához');
        $ec->def('Fs2', ceil(($ec->ac/$ec->z0)*$ec->FEd + $ec->HEd), 'F_(s,2) = F_(Ed)*a_c/z_0 + H_(Ed) = %% [kN]', 'Vasalással felvett erő nyomott függőleges teherből');
        $Fs3 = ceil((($ec->ac/$ec->z0 + ($ec->HEd/$ec->FEd)/(($ec->hc-$ec->d)/$ec->z0 + 1)))*$ec->FEd);
        $ec->note('*[Vasbeton szerkezetek (2017) 6.9. 53.o.]:* $F_(s) = F_(Ed)*(a_c/z_0 + H_(Ed)/F_(Ed) * (a_H(=h_c-d))/z_0 + 1)) = '.$Fs3.' [kN]$');
        $ec->def('Fs', max($ec->Fs1, $ec->Fs2), 'F_s = max{(F_(s,1)),(F_(s,2)):} = %% [kN]');
        $ec->def('Asmin', ceil(1.25*$ec->Fs/$rebarMaterial->fyd*1000), 'A_(s,min) = 1.25*F_s/(f_(yd)) = %% [mm^2]', '$F_s$ erő felvételéhez szükséges vasmennyiség');
        $ec->note('Az 1.25-ös növelés 80%-ra csökkenti a betonacélok nyúlását használhatósági határállapotban, ezzel csökkenti a repedéstágasságot. Rep.tág. csökkentésére konzol tövébe tett felső ferde vasak jók még.');
        $ec->def('nrequ', ceil($ec->Asmin/$ec->A($ec->phiMain, 1)), 'n_(requ) = %%', 'Szükséges vasszám. Biztosított vasszám: '.$ec->nMain*$ec->nMainRow);
        $ec->def('Ascalc', floor($ec->A($ec->phiMain, $ec->nMain*$ec->nMainRow)), 'A_(s,calc) = (phi_(mai n)^2*pi)/4 * n_(mai n)*n_(mai n,row) = %% [mm^2]', 'Alkalmazott vasmennyiség');
        $ec->label($ec->Asmin/$ec->Ascalc, 'Kihasználtság húzásra');

        $ec->h2('Hosszvas lehorgonyzása konzolban');
        $ec->txt('Konzol szabad vég felé eső hurkos lehorgonyzáshoz $a_(min) = '.$ec->aMin.' [mm]$ konzolmélység alkalmazása szükséges!');
        if ($ec->mainType === 'hook') {
            $ec->txt('Pillér felőli lehorgonyzás számítása:');
            $ec->def('rs1', ceil(3*pi()/8*$rebarMaterial->fyd/$concreteMaterial->fcd*$ec->phiMain*0.5), 'r_(s1) = (3pi)/8*f_(yd)/(f_(cd))*phi_(s1) = %% [mm]', 'Kerekítési sugár');
            $ec->def('lbmin', ceil(($ec->phiMain/4)*($rebarMaterial->fyd/$concreteMaterial->fbd)), 'l_(b,min) = (phi_(s,mai n))/4*f_(yd)/(f_(bd)) = %% [mm]', 'Szükséges lehorgonyzási hossz, 90°-os kampó nélkül');
            $ec->def('lbmin90', ceil((0.7*$ec->lbmin)), 'l_(b,min,90) = 0.7*l_(b,min) = %% [mm]', 'Szükséges lehorgonyzási hossz 90°-os kampóval');
            $ec->def('lb', ceil($ec->b - $ec->cnom), 'l_b = b - c_(nom) = %% [mm]', 'Rendelkezésre álló lehorgonyzási hossz');
            if ($ec->lbmin < $ec->lb) {
                $ec->label('yes', 'Nem szükséges a kampózás');
            } else if ($ec->lbmin >= $ec->lb && $ec->lb > $ec->lbmin90) {
                $ec->label('yes', '90 °-os kapmózás szükséges!');
            } else {
                $ec->label('no', 'Lehorgonyzás az oszlopban - nem felel meg!');
            }
        } else {
            $ec->txt('**Csapos kialakítás esetén egyedi lehorgonyzás szükséges!**');
            $ec->img('https://structure.hu/ecc/corbel4.jpg');
            $ec->txt('Pecsétnyomás számítása:');
            $ec->note('*[Vasbeton szerkezetek (2017) 6.10. 55.o.]*. Nyíróerő átadás $(3*phi)^2$ felületen. Térbeli feszültségállapot nem léphet fel szabadon, mert $2*phi$ távolságra vannak a vasak és $A_(cl)$ felület nem metszhet össze: $sqrt(A_(cl)/A_(c0)) := 1$');
            $ec->def('FRd', H3::n2(((3 * $ec->phiMain) ** 2) *1*$concreteMaterial->fcd/1000), 'F_(Rd) = A_(c0)*alpha*f_(cd) = (3*phi)^2*min{(3),(sqrt(A_(cl)/A_(c0))):}*f_(cd) = '. ((3 * $ec->phiMain) ** 2) .'*1*'.$concreteMaterial->fcd/1000.0.' = %% [kN]');
            $ec->def('FEd', H3::n2($ec->Fs/($ec->nMain*$ec->nMainRow)), 'F_(Ed) = F_s/(phi_(s,mai n)*phi_(s,mai n,row)) '.$ec->Fs.'/'.($ec->nMain*$ec->nMainRow).'= %% [kN]');
            $ec->label($ec->FEd/$ec->FRd,'kihasználtság');
        }

        $ec->h2('Hosszvas lehorgonyzása pillérben');
        $ec->lst('alphaa', ['Egyenes: 1.0' => 1.0, 'Kampó, hurok, hajlítás: 0.7' => 0.7], ['alpha_a', 'Lehorgonyzás módja'], '1.0', '');
        $ec->math('n_(requ)/n_(prov) = '. H3::n4($ec->nrequ/($ec->nMain*$ec->nMainRow)));
        $this->concreteCalculation->moduleAnchorageLength($ec->phiMain, $rebarMaterial->fyd, $concreteMaterial->fbd, $ec->alphaa, $ec->nrequ, $ec->nMain*$ec->nMainRow);

        $ec->h1('Kengyelezés');
        $ec->txt('A vízszintes kengyelezés nem hagyható el a nyomott rácsrúd felhasadásának megakadályozásához.');
        $ec->numeric('nswh', ['n_(s,w,h)', 'Vízszintes zárt $phi '.$ec->phiwh.'$ kengyelek száma'], floor(($ec->ac + 50)/50), '');
        $ec->txt('Vízszintes kengyelek alulról rendezve kb.: $Delta_(w,h) = '.floor(($ec->d-2*$ec->cnom)/$ec->nswh).' [mm]$ távolságra kerülnek egymástól');
        $ec->def('Aswh', floor($ec->nswh*$ec->A($ec->phiwh, 2)), 'A_(s,w,h) = %% [mm^2]', 'Alkalmazott vízszintes kengyel keresztmetszet');
        $ec->def('Aswhmin', ceil(0.5*$ec->Ascalc), 'A_(s,w,h,min) = 0.5*A_(s,calc) = %% [mm^2]', 'Szükséges vízszintes kengyel keresztmetszet');
        $ec->note('A Nemzeti Melléklet a 0.25 szorzót 0.5-re módosítja.');
        $ec->label($ec->Aswhmin/$ec->Aswh, ' vsz. kengyel kihasználtság');
        if ($ec->ac > 0.5*$ec->hc) {
            $ec->info('$a_c > 0.5*h_c$ ezért a függőleges kengyelezés nem hagyható el!');
        } else {
            $ec->info('$a_c < 0.5*h_c$  ezért a függőleges kengyelezés elhagyható. Ha nem lenne elhagyható, az alábbi feltételnek kell teljesülnie:');
        }
        $ec->numeric('nswv', ['n_(s,w,v)', 'Függőleges zárt $phi '.$ec->phiwv.'$ kengyelek száma'], 2, '');
        $ec->def('Aswv', floor($ec->nswv*$ec->A($ec->phiwv, 2)), 'A_(s,w,v) = %% [mm^2]', 'Alkalmazott függőleges kengyel keresztmetszet');
        $ec->def('Aswvmin', ceil(0.5*($ec->FEd/$rebarMaterial->fyd)*1000), 'A_(s,w,v,min) = 0.5*F_(Ed)/(f_(yd)) = %% [mm^2]', 'Szükséges függőleges kengyel keresztmetszet');
        $ec->label($ec->Aswvmin/$ec->Aswv, 'függ. kengyel kihasználtság');
        $ec->note('Vsz. kengyelek kampói az oszlopba kerüljenek. A függ. kengyelek kampói a rk. alsó felén legyenek. A függ. kengyelek vegyék körbe a vővasat és vsz. kengyeleket.');

        $ec->h1('Felső csomópont ellenőrzése', '(2)');
        $ec->txt('Ellenőrzési feltétel: $max{(sigma_(c1)),(sigma_(c2)):} le (k_2*upsilon*f_(cd) = '. 0.85*$ec->upsilon*$concreteMaterial->fcd.'[N/(mm^2)])$ és $k_2 = 0.85$');
        $ec->note('Jellemzően az (1) csp. veszélyesebb.');
    }
}
