<?php

namespace Calculation;

use \Calculation\Column;

/**
 * Analysis RC columns' corbels according to Eurocodes - Calculation class for ECC framework
 *
 * (c) Bence VÁNKOS
 * https:// structure.hu
 */

Class Corbel
{
    private $column;

    public function __construct(Column $column)
    {
        $this->column = $column;
    }

    public function calc(\Base $f3, \Ecc\Blc $blc, \Ec\Ec $ec): void
    {
        $blc->note('Lásd még: *EC2 6.5.: Tervezés rácsmodellek alapján.*');

        $this->column->moduleColumnData($f3, $blc, $ec);
        $blc->txt('', '$b$ méret a rövidkonzol szélessége');

        $blc->region0('img', 'Elrendezési képek');
            $blc->img($f3->home.'ecc/corbel0.jpg', 'Rövidkonzol erőjáték');
            $blc->img($f3->home.'ecc/corbel1.jpg', 'Rövidkonzol vasalás');
            $blc->img($f3->home.'ecc/corbel3.jpg', 'Felső csomópont');
        $blc->region1();

        $blc->numeric('ac', ['a_c', 'Terheló függőleges erő hatásvonala az oszlop szélétől'], 125, 'mm', '');
        $blc->numeric('hc', ['h_c', 'Konzol magassága'], 250, 'mm', '');
        $blc->math('b = '.$f3->_b.' [mm]', 'Rövidkonzol szélessége ($=$ oszlop $b$ szélessége)');
        $blc->note('A továbbiakban feltételezzük, hogy a rövidkonzol keresztirányú $b$ mérete minden vizsgált részén azonos, továbbá a $b$ betonszélességen szimmetrikusan elhelyezett teherolsztó lemez keresztirányú $t$ méretére teljesül a $t >= b - 2*s_0$ feltétel, ahol $s_0$ a húzott fővasak felső oldali betonfedésének és fél vasátmérőjének az összege. Ellenkező esetben ügyelni kell a $b$ szélesség csomópontonkénti helyes felvételére és a vasalás hatékony zónában történő elhelyezésére.');
        $blc->numeric('FEd', ['F_(Ed)', 'Függőleges erő'], 500, 'kN');
        $blc->numeric('HEd', ['H_(Ed)', 'Vízszintes erő'], 50, 'kN');
        if ($f3->_HEd < 0.1*$f3->_FEd) {
            $blc->danger('Javasolt $H_(Ed,min) = 0.1*F_(Ed) = '. 0.1*$f3->_FEd.' [kN]$');
        }
        if ($f3->_HEd > 0.2*$f3->_FEd) {
            $blc->danger('Javasolt $H_(Ed,max) = 0.2*F_(Ed) = '. 0.2*$f3->_FEd.' [kN]$');
        }
        $blc->def('alpha', $f3->_HEd/$f3->_FEd, 'alpha = H_(Ed)/F_(Ed) = '.($f3->_HEd/$f3->_FEd)*100 .' [%]', 'Függőleges - vízszintes erő aránya');

        $ec->rebarList('phiwh', 10, ['phi_(s,w,h)', 'Felvett vízszintes kengyel vasátmérő'], '');
        $ec->rebarList('phiwv', 12, ['phi_(s,w,v)', 'Felvett függőleges kengyel vasátmérő'], '');
        $ec->rebarList('phiMain', 20, ['phi_(s,mai n)', 'Felvett hurkos fővas átmérő *(A)*'], '');
        $blc->lst('mainType', ['Hurkos fővas, lehorgonyzás hagyományosan' => 'hook', 'Csapos szerelvény kialakítás' => 'custom'], 'Húzott fővas kialakítása', 'hook');
        if ($f3->_mainType == 'hook') {
            $blc->def('nMain', 2, 'n_(s,mai n) = %%', 'Hurkos fővasnak két szára van.');
            $blc->note('Nagyon széles konzolba beférne 2 keskeny hurkos vas egymás mellé egy sorba, de itt csak kettővel számol.');
        } else {
            $nMainMax = ($f3->_b - 2*$f3->_cnom - 2*$f3->_phiwv - 2*$f3->_phiwh + 2*$f3->_phiMain)/(3*$f3->_phiMain);
            $blc->def('nMainMax', floor($nMainMax), 'n_(s,mai n, max) = (b-2*c_(nom) - 2*phi_(s,w,v) - 2*phi_(s,w,h) + 2*phi_(s,mai n))/(3*phi_(s,mai n)) = floor('.\H3::n2($nMainMax).') = %%', 'Fővasak $2*phi$ távolságra egymástól, vízszintes kengyelen belül');
            $blc->numeric('nMain', ['n_(s,mai n)', 'Alkalmazott $phi '.$f3->_phiMain.'$ hurkos fővasak száma egy sorban a szerelvényben'], 2);
        }

        $blc->numeric('nMainRow', ['n_(s,mai n,row)', 'Alkalmazott $phi '.$f3->_phiMain.'$ hurkos fővas sor'], 1);
        $blc->def('s0', $f3->_s0 = $f3->_cnom + $f3->_phiMain/2, 's_0 = c_(nom) + 2*phi_(s,mai n) = %% [mm]');
        $blc->def('tp', 10, 't_p = 10 [mm]', 'Teherelosztó lemez vastagsága *(E)*');
        $blc->numeric('ap', ['a_p', 'Lemez hossz pillér $b$ szélességére merőlegesen'], 200, 'mm');

        $blc->def('deltaMain', max(24 + $f3->_phiMain, 2*$f3->_phiMain), 'Delta_(s,mai n) = %% [mm]', '2 fővas sor közti távolság, $24 [mm]$ max szemcseátmérőre vagy $2 phi$-re');
        $blc->def('delta', 10, 'delta = 10 [mm]', 'Véletlen vaselmozdulás');
        $blc->def('d', \H3::n0($f3->_hc - $f3->_cnom - $f3->_phiwv - ($f3->_nMainRow*$f3->_phiMain + ($f3->_nMainRow - 1)*$f3->_deltaMain)/2) - $f3->_delta, 'd = h_c - c_(nom) - phi_(s,w,v) - (n_(s,mai n,row)*phi_(s,mai n) + (n_(s,mai n,row) - 1)*Delta_(s,mai n))/2 - delta = %% [mm]', 'Hatékony magasság húzott vaskép tengelyében');
        $blc->def('aH', ($f3->_hc - $f3->_d) + $f3->_tp, 'a_H = (h_c - d) - t_p = %% [mm]', 'Vízszintes erő hatásvonala húzott vasaktól');

        $blc->info0();
            $blc->def('bp', $f3->_b - 2*$f3->_s0, 'b_(p,min) >= b - 2*(c_(nom) + phi_(s,mai n)/2) = %% [mm]', 'Teherelosztó lemez minimum hossza pillér $b$ szélességével párhuzamosan');
            $blc->def('aMin', ceil($f3->_ac + $f3->_ap*0.5 + 2*$f3->_s0 + sqrt(2)*($f3->_hc - $f3->_d)), 'a_(min) = a_c + a_p/2 + 2*s_0 + sqrt(2)*(h_c - d) = %% [mm]', 'Konzol mélység minimális mérete, hurkos fővas lehorgonyzásához rövidkonzol külső oldalán');
        $blc->info1();
        $blc->note('A teherelosztó lemez bektösének a súrlódás figyelembevétele nélkül fel kell tudnia venni $H_(Ed)$ erőt.');


        $blc->h1('Nyomott rácsrúd ellenőrzése', '(1)');
        $blc->note('Az elérhető legnagyobb teherbírás a még lehetséges, illetve megengedett legnagyobb $theta$ szöghöz tartozik.');

        $blc->def('k1', 1, 'k_1 = %%');
        $blc->def('upsilon', 1 - $f3->_cfck/250, 'upsilon = 1 - f_(ck)/250 = %%');
        $blc->def('sigmaRdmax', $f3->_k1*$f3->_upsilon*$f3->_cfcd, 'sigma_(Rd,max) = k_1*upsilon*f_(cd) = %% [N/(mm^2)]', 'Helyi nyomószilárdság');
        $blc->note('*(1)* csomópont mindhárom lapján egyforma nyomófeszültségek lépnek fel, a legmeredekebb $theta$ szögnél megegyezik a fenti helyi nyomószilárdsággal.');

        $blc->def('x', \H3::n2($f3->_FEd/($f3->_b*$f3->_sigmaRdmax)*1000), 'x = F_(Ed)/(b*sigma_(Rd,max)) = %% [mm]', 'Derékszögű betonék $x$ mérete függőleges vetületi egyenletből');
        $blc->math('tan(theta) = x/y = (d - y/2)/(x/2 + a_c + alpha*a_H ) to y to theta', 'Derékszögű betonék $y$ méretének meghatározása geometriai viszonyokból');
        $qa = -0.5;
        $qb = $f3->_d;
        $qc = -1*(0.5*pow($f3->_x, 2) + $f3->_ac*$f3->_x + $f3->_alpha*$f3->_aH*$f3->_x);
        $blc->def('y1', $ec->quadratic($qa, $qb, $qc, 'root1'), 'y1 = %%');
        $blc->def('y2', $ec->quadratic($qa, $qb, $qc, 'root2'), 'y2 = %%');

        function getValidRoot($search, $rootsArray) {
            $closest = null;
            foreach ($rootsArray as $item) {
                if ($closest === null || (abs($search - $closest) > abs($item - $search) && $item > 0)) {
                    $closest = $item;
                }
            }
            return $closest;
        }

        $blc->def('y', \H3::n2(getValidRoot($f3->_x, [$f3->_y1, $f3->_y2])), 'y = %% [mm]', 'Fizikailag lehetséges gyök választása');
        $blc->def('theta', rad2deg(atan($f3->_x/$f3->_y1)), 'theta = %% [deg]', '$N_c$ nyomott rácsrúd ferdesége');
        if ($f3->_theta <= 45) {
            $blc->danger0();
                $blc->txt('$theta = '.$f3->_theta.' [deg]$: $45$ és $68 [deg]$ között kell lennie!');
                $blc->txt('A ferde nyomóerő túl lapos. A rövidkonzol magassága vagy szélessége növelendő. $f_(cd)$ növelése nem gazdaságos.');
            $blc->danger1();
        }
        if ($f3->_theta > 68) {
            $blc->danger0();
                $blc->txt('$theta = '.$f3->_theta.' [deg]$, de $45$ és $68 [deg]$ között kell lennie!');
                $blc->txt('A rövidkonzol teljes magassága nem használható ki, a betonék túl magasra kerül.');
                $blc->def('theta', 68, 'theta := %% [deg]', '$theta$ felülírása.');
                $blc->def('y', \H3::n2($f3->_x/2.5), 'y = x/2.5 = %% [mm]', 'Derékszögű betonék $y$ mérete');
            $blc->danger1();
        }

        $blc->def('z0', floor(tan(deg2rad($f3->_theta))*($f3->_ac + $f3->_alpha*$f3->_aH)), 'z_0 = tan(theta)*(a_c + alpha*a_H) = %% [mm]');
        if ($f3->_ac <= $f3->_z0) {
            $blc->txt('$a_c < = z_0$, a vasbeton konzol rövidkonzolként méretezhető.');
        } else {
            $blc->danger('$a_c > z_0$, a vasbeton konzol nem méretezhető rövidkonzolként.');
        }

        $blc->def('zNc', \H3::n1(($f3->_d - $f3->_z0)*cos(deg2rad($f3->_theta))), 'z_(Nc) = (d - z_0)*cos(theta) = %% [mm]', '$N_c$ hatásvonala a rövidkonzol belső-alsó sarkától');
        $blc->def('NRd', \H3::n1((2*$f3->_zNc*$f3->_b*$f3->_cfcd)/1000), 'N_(Rd) = 2*z_(Nc)*b*f_(cd) = %% [kN]', 'Ferde beton rácsrúd által felvehető erő');
        $blc->def('Nc', \H3::n1($f3->_FEd/sin(deg2rad($f3->_theta))), 'N_c = F_(Ed)/sin(theta) = %% [kN]', 'Függőleges vetületi egyensúlyi egyenlet');
        if($f3->_Nc < $f3->_NRd) {
            $blc->label('yes', ''.\H3::n3($f3->_Nc/$f3->_NRd)*100 .' % Kihasználtság');
        } else {
            $blc->label('no', 'Nem felel meg, konzolmagasság növelése szükséges');
        }

        $blc->h1('Húzott fővas ellenőrzése');
        $blc->def('Fs1', ceil(\H3::n2($f3->_y*$f3->_b*$f3->_sigmaRdmax + $f3->_HEd*1000)/1000), 'F_(s,1) = y*b*sigma_(Rd,max) + H_(Ed) = %% [kN]', 'Vasalással felvett erő nyomott rácsrúd teherbírásához');
        $blc->def('Fs2', ceil(($f3->_ac/$f3->_z0)*$f3->_FEd + $f3->_HEd), 'F_(s,2) = F_(Ed)*a_c/z_0 + H_(Ed) = %% [kN]', 'Vasalással felvett erő nyomott függőleges teherből');
        $Fs3 = ceil((($f3->_ac/$f3->_z0 + ($f3->_HEd/$f3->_FEd)/(($f3->_hc-$f3->_d)/$f3->_z0 + 1)))*$f3->_FEd);
        $blc->note('*[Vasbeton szerkezetek (2017) 6.9. 53.o.]:* $F_(s) = F_(Ed)*(a_c/z_0 + H_(Ed)/F_(Ed) * (a_H(=h_c-d))/z_0 + 1)) = '.$Fs3.' [kN]$');
        $blc->def('Fs', max($f3->_Fs1, $f3->_Fs2), 'F_s = max{(F_(s,1)),(F_(s,2)):} = %% [kN]');
        $blc->def('Asmin', ceil(1.25*$f3->_Fs/$f3->_rfyd*1000), 'A_(s,min) = 1.25*F_s/(f_(yd)) = %% [mm^2]', '$F_s$ erő felvételéhez szükséges vasmennyiség');
        $blc->note('Az 1.25-ös növelés 80%-ra csökkenti a betonacélok nyúlását használhatósági határállapotban, ezzel csökkenti a repedéstágasságot. Rep.tág. csökkentésére konzol tövébe tett felső ferde vasak jók még.');
        $blc->def('Ascalc', floor($ec->A($f3->_phiMain, $f3->_nMain*$f3->_nMainRow)), 'A_(s,calc) = (phi_(mai n)^2*pi)/4 * n_(mai n)*n_(mai n,row) = %% [mm^2]', 'Alkalmazaott vasmennyiség');
        $blc->label($f3->_Asmin/$f3->_Ascalc, 'Kihasználtság húzásra');

        $blc->h2('Hosszvas lehorgonyzása konzolban');
        $blc->txt('Konzol szabad vég felé eső hurkos lehorgonyzáshoz $a_(min) = '.$f3->_aMin.' [mm]$ konzolmélység alkalmazása szükséges!');
        if ($f3->_mainType == 'hook') {
            $blc->txt('Pillér felőli lehorgonyzás számítása:');
            $blc->def('rs1', ceil(3*pi()/8*$f3->_rfyd/$f3->_cfcd*$f3->_phiMain*0.5), 'r_(s1) = (3pi)/8*f_(yd)/(f_(cd))*phi_(s1) = %% [mm]', 'Kerekítési sugár');
            $blc->def('lbmin', ceil(($f3->_phiMain/4)*($f3->_rfyd/$f3->_cfbd)), 'l_(b,min) = (phi_(s,mai n))/4*f_(yd)/(f_(bd)) = %% [mm]', 'Szükséges lehorgonyzási hossz, 90°-os kampó nélkül');
            $blc->def('lbmin90', ceil((0.7*$f3->_lbmin)), 'l_(b,min,90) = 0.7*l_(b,min) = %% [mm]', 'Szükséges lehorgonyzási hossz 90°-os kampóval');
            $blc->def('lb', ceil($f3->_b - $f3->_cnom), 'l_b = b - c_(nom) = %% [mm]', 'Rendelkezésre álló lehorgonyzási hossz');
            if ($f3->_lbmin < $f3->_lb) {
                $blc->label('yes', 'Nem szükséges a kampózás');
            } else if ($f3->_lbmin >= $f3->_lb && $f3->_lb > $f3->_lbmin90) {
                $blc->label('yes', '90 °-os kapmózás szükséges!');
            } else {
                $blc->label('no', 'Lehorgonyzás az oszlopban - nem felel meg!');
            }
        } else {
            $blc->txt('**Csapos kialakítás esetén egyedi lehorgonyzás szükséges!**');
            $blc->img('https://structure.hu/ecc/corbel4.jpg');
            $blc->txt('Pecsétnyomás számítása:');
            $blc->note('*[Vasbeton szerkezetek (2017) 6.10. 55.o.]*. Nyíróerő átadás $(3*phi)^2$ felületen. Térbeli feszültségállapot nem léphet fel szabadon, mert $2*phi$ távolságra vannak a vasak és $A_(cl)$ felület nem metszhet össze: $sqrt(A_(cl)/A_(c0)) := 1$');
            $blc->def('FRd', \H3::n2(pow((3*$f3->_phiMain),2)*1*$f3->_cfcd/1000), 'F_(Rd) = A_(c0)*alpha*f_(cd) = (3*phi)^2*min{(3),(sqrt(A_(cl)/A_(c0))):}*f_(cd) = '.pow((3*$f3->_phiMain),2).'*1*'.$f3->_cfcd/1000.0.' = %% [kN]');
            $blc->def('FEd', \H3::n2($f3->_Fs/($f3->_nMain*$f3->_nMainRow)), 'F_(Ed) = F_s/(phi_(s,mai n)*phi_(s,mai n,row)) '.$f3->_Fs.'/'.($f3->_nMain*$f3->_nMainRow).'= %% [kN]');
            $blc->label($f3->_FEd/$f3->_FRd,'kihasználtság');
        }

        $blc->h1('Kengyelezés');
        $blc->txt('A vízszintes kengyelezés nem hagyható el a nyomott rácsrúd felhasadásának megakadályozásához.');
        $blc->numeric('nswh', ['n_(s,w,h)', 'Vízszintes zárt $phi '.$f3->_phiwh.'$ kengyelek száma'], floor(($f3->_ac + 50)/50), '');
        $blc->txt('Vízszintes kengyelek alulról rendezve kb.: $Delta_(w,h) = '.floor(($f3->_d-2*$f3->_cnom)/$f3->_nswh).' [mm]$ távolságra kerülnek egymástól');
        $blc->def('Aswh', floor($f3->_nswh*$ec->A($f3->_phiwh, 2)), 'A_(s,w,h) = %% [mm^2]', 'Alkalmazott vízszintes kengyel keresztmetszet');
        $blc->def('Aswhmin', ceil(0.5*$f3->_Ascalc), 'A_(s,w,h,min) = 0.5*A_(s,calc) = %% [mm^2]', 'Szükséges vízszintes kengyel keresztmetszet');
        $blc->note('A Nemzeti Melléklet a 0.25 szorzót 0.5-re módosítja.');
        $blc->label($f3->_Aswhmin/$f3->_Aswh, ' vsz. kengyel kihasználtság');
        if ($f3->_ac > 0.5*$f3->_hc) {
            $blc->info('$a_c > 0.5*h_c$ ezért a függőleges kengyelezés nem hagyható el!');
        } else {
            $blc->info('$a_c < 0.5*h_c$  ezért a függőleges kengyelezés elhagyható. Ha nem lenne elhagyható, az alábbi feltételnek kell teljesülnie:');
        }
        $blc->numeric('nswv', ['n_(s,w,v)', 'Függőleges zárt $phi '.$f3->_phiwv.'$ kengyelek száma'], 2, '');
        $blc->def('Aswv', floor($f3->_nswv*$ec->A($f3->_phiwv, 2)), 'A_(s,w,v) = %% [mm^2]', 'Alkalmazott függőleges kengyel keresztmetszet');
        $blc->def('Aswvmin', ceil(0.5*($f3->_FEd/$f3->_rfyd)*1000), 'A_(s,w,v,min) = 0.5*F_(Ed)/(f_(yd)) = %% [mm^2]', 'Szükséges függőleges kengyel keresztmetszet');
        $blc->label($f3->_Aswvmin/$f3->_Aswv, 'függ. kengyel kihasználtság');
        $blc->note('Vsz. kengyelek kampói az oszlopba kerüljenek. A függ. kengyelek kampói a rk. alsó felén legyenek. A függ. kengyelek vegyék körbe a vővasat és vsz. kengyeleket.');

        $blc->h1('Felső csomópont ellenőrzése', '(2)');
        $blc->txt('Ellenőrzési feltétel: $max{(sigma_(c1)),(sigma_(c2)):} le (k_2*upsilon*f_(cd) = '. 0.85*$f3->_upsilon*$f3->_cfcd.'[N/(mm^2)])$ és $k_2 = 0.85$');
        $blc->note('Jellemzően az (1) csp. veszélyesebb.');
    }
}
