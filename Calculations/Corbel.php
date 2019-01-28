<?php

namespace Calculation;

Class Corbel extends \Ecc
{

    /**
     * @var $f3 \Base
     * @var $blc \Blc
     * @var $ec \Ec\Ec
     * @throws \Exception
     */
    public function calc(object $f3, object $blc, object $ec): void
    {
        $column = new \Calculation\Column();
        $column->moduleColumnData($f3, $blc, $ec);

        $blc->img($f3->home.'ecc/column0.jpg', 'övidkonzol erőjáték');

        $blc->numeric('ac', ['a_c', 'Teher távolsága az oszloptól'], 125, 'mm', '');
        $blc->numeric('hc', ['h_c', 'Konzol magassága'], 250, 'mm', '');
        $blc->numeric('FEd', ['F_(Ed)', 'Függőleges erő'], 500, 'kN');
        $blc->numeric('HEd', ['H_(Ed)', 'Vízszintes erő'], 50, 'kN');
        if ($f3->_HEd < 0.1*$f3->_FEd) {
            $blc->danger('Javasolt $H_(Ed,min) = 0.1*F_(Ed) = '. 0.1*$f3->_FEd.' [kN]$');
        }
        if ($f3->_HEd > 0.2*$f3->_FEd) {
            $blc->danger('Javasolt $H_(Ed,max) = 0.2*F_(Ed) = '. 0.2*$f3->_FEd.' [kN]$');
        }

        $ec->rebarList('phis1', 20, ['phi_(s1)', 'Felvett hurkos fővas átmérő'], '');
        $blc->def('ns1', 2, 'n_(s1) = %%', 'Hurkos fővasnak két szára van.');
        $blc->note('Nagyon széles konzolba beférne 2 keskeny hurkos vas egymás melé egy sorba, de itt csak kettővel számol.');
        $blc->numeric('ns1row', ['n_(s1,row)', 'Alkalmazott $phi'.$f3->_phis1.'$ hurkos fővas sor'], 1);
        $ec->rebarList('phisw1', 10, ['phi_(sw1)', 'Felvett vízszintes kengyel vasátmérő'], '');
        $ec->rebarList('phisw2', 12, ['phi_(sw2)', 'Felvett függőleges kengyel vasátmérő'], '');

        $blc->def('alpha', $f3->_HEd/$f3->_FEd, 'alpha = H_(Ed)/F_(Ed) = '.($f3->_HEd/$f3->_FEd)*100 .' [%]', 'Függőleges - vízszintes erő aránya');
        $blc->math('b = '.$f3->_b.' [mm]', 'Rövidkonzol szélessége (= oszlop $b$ szélessége)');
        $blc->note('A továbbiakban feltételezzük, hogy a rövidkonzol keresztirányú $b$ mérete minden vizsgált részén azonos, továbbá a $b$ betonszélességen szimmetrikusan elhelyezett teherolsztó lemez keresztirányú $t$ méretére teljesül a $t >= b - 2*s_0$ feltétel, ahol $s_0$ a húzott fővasak felső oldali betonfedésének és fél vasátmérőjének az összege. Ellenkező esetben ügyelni kell a $b$ szélesség csomópontonkénti helyes felvételére és a vasalás hatékony zónában történő elhelyezésére.');

        $blc->h2('Nyomott rácsrúd ellenőrzése');

        $blc->note('Az elérhető legnagyobb teherbírás a még lehetséges, illetve megengedett legnagyobb $theta$ szöghöz tartozik.');

        $blc->def('ts1', max(24 + $f3->_phis1, 2*$f3->_phis1), 't_(s1) = %% [mm]', '2 fővas sor közti távolság, 24 mm max szemcseátmérőre vagy 2D-re.');
        $blc->def('d', \H3::n0($f3->_hc - $f3->_cnom - $f3->_phisw2 - ($f3->_ns1row*$f3->_phis1 + ($f3->_ns1row - 1)*$f3->_ts1)/2), 'd = h_c - c_(nom) - phi_(sw2) - (n_(s1,row)*phi_(s1) + (n_(s1,row) - 1)*t_(s1))/2 = %% [mm]', 'Hatékony magasság');
//        $blc->def('d', $f3->_hc - $f3->_cnom - $f3->_phisw1 - $f3->_phis1/2, 'd = h_c - c_(nom) - phi_(sw1) - phi_(s1)/2 = %% [mm]', 'Hatékony magasság');
        $blc->def('aH', $f3->_hc - $f3->_d, 'a_H = h_c - d = %% [mm]', '');

        $blc->input('param1', 'Paraméter nyomott rácsrúd ($theta$) beállításához', 0.8, '', 'Értéke 0 és 1 között', 'min_numeric,0|max_numeric,1');

        $blc->def('z0', $f3->_param1*$f3->_d, 'z_0 ='.$f3->_param1.'*d = %% [mm]', 'Erőkar értéke');
        $z0 = $f3->_z0;

        $theta = \H3::n1(rad2deg(atan($f3->_z0/$f3->_ac)));
        if (45 < $theta && $theta < 68) {
            $blc->def('theta', $theta, 'theta = %% [deg]', '45 és 68 [deg] között kell lennie');
        } else if ($theta <= 45) {
            $blc->danger('$theta = '.$theta.' [deg]$: 45 és 68[deg] között kell lennie!');
            $blc->def('theta', 45, 'theta = %% [deg]');
        } else {
            $blc->def('theta', 68, 'theta = %% [deg]');
            $blc->danger('$theta = '.$theta.' [deg]$: 45 és 68 [deg] között kell lennie!');
        }

        $blc->def('z0', \H3::n0(tan(deg2rad($f3->_theta))*$f3->_ac), 'z_0 = tan(theta)*a_c = %% [mm]', 'Erőkar értéke');
        if ($f3->_ac <= $f3->_z0) {
            $blc->txt('$a_c < = z_0$, a vasbeton konzol rövidkonzolként méretezhető.');
        } else {
            $blc->danger('$a_c > z_0$, a vasbeton konzol nem méretezhető rövidkonzolként.');
        }

        if ($f3->_z0/$z0 > 1.05) {
            $blc->danger('$z_0$ értékek 5%-nál jobban eltérnek!');
        }

        $blc->def('zNc', \H3::n1(($f3->_d - $f3->_ac)*cos(deg2rad($f3->_theta))), 'z_(Nc) = (d - a_c)*cos(theta) = %% [mm]', '$N_c$ hatásvonala a rövidkonzol belső-alsó sarkától');
        $blc->def('NRd', \H3::n1((2*$f3->_zNc*$f3->_b*$f3->_cfcd)/1000), 'N_(Rd) = 2*z_(Nc)*b*f_(cd) = %% [kN]', 'Ferde beton rácsrúd');
        $blc->def('Nc', \H3::n1($f3->_FEd/sin(deg2rad($f3->_theta))), 'N_c = F_(Ed)/sin(theta) = %% [kN]', 'Függőleges vetületi egyensúlyi egyenlet');
        if($f3->_Nc < $f3->_NRd) {
            $blc->label('yes', '$N_c < N_(Rd)$ Megfelel');
        } else {
            $blc->label('no', 'Nem felel meg, konzolmagasság növelése szükséges');
        }

        $blc->h2('Hosszvasalás ellenőrzése');
        $blc->def('Fs', \H3::n2($f3->_FEd*($f3->_ac/$f3->_z0 + 0.1)), 'F_s = F_(Ed)*(a_c/z_0 + 0.1) = %% [kN]', 'Vasalással felvett erő');
        $blc->def('Asmin', ceil($f3->_Fs/$f3->_rfyd*1000), 'A_(s,min) = F_s/(f_(yd)) = %% [mm^2]', 'Szükséges vasmennyiség');
        $blc->def('Ascalc', ceil($ec->A($f3->_phis1, $f3->_ns1*$f3->_ns1row)), 'A_(s,calc) = %% [mm^2]', 'Alkalmazaott vasmennyiség');
        $blc->label($f3->_Asmin/$f3->_Ascalc, 'Kihasználtság húzásra');
        $blc->def('rs1', ceil(3*pi()/8*$f3->_rfyd/$f3->_cfcd*$f3->_phis1*0.5), 'r_(s1) = (3pi)/8*f_(yd)/(f_(cd))*phi_(s1) = %% [mm]', 'Kerekítési sugár');
        $blc->def('lbmin', ceil(($f3->_phis1/4)*($f3->_rfyd/$f3->_cfbd)), 'l_(b,min) = (phi_(s1))/4*f_(yd)/(f_(bd)) = %% [mm]', 'Szükséges lehorgonyzási hossz, 90°-os kampó nélkül');
        $blc->def('lbmin90', ceil((0.7*$f3->_lbmin)), 'l_(b,min,90) = %% [mm]', 'Szükséges lehorgonyzási hossz 90°-os kampóval');
        $blc->def('lb', ceil($f3->_b - $f3->_cnom), 'l_b = b - c_(nom) = %% [mm]', 'Rendelkezésre álló lehorgonyzási hossz');
        if ($f3->_lbmin < $f3->_lb) {
            $blc->label('yes', 'Nem szükséges a kampózás');
        } else if ($f3->_lbmin >= $f3->_lb && $f3->_lb > $f3->_lbmin90) {
            $blc->label('yes', '90 [deg]-os kapmózás szükséges!');
        } else {
            $blc->label('no', 'Lehorgonyzás az oszlopban!');
        }

        $blc->h2('Kengyelezés');
        $blc->txt('A vízszintes kengyelezés nem hagyható el a nyomott rácsrúd felhasadásának megakadályozásához.');
        $blc->numeric('nsw1', ['n_(sw1)', 'Vízszintes zárt $phi'.$f3->_phisw1.'$ kengyelek száma'], floor(($f3->_ac + 50)/50), '');
        $blc->def('Asw1', floor($f3->_nsw1*$ec->A($f3->_phisw1, 2)), 'A_(sw1) = %% [mm^2]', 'Alkalamazott vízszintes kengyel keresztmetszet');
        $blc->def('Asw1min', ceil(0.5*$f3->_Ascalc), 'A_(sw1,min) = 0.5*A_(s,calc) = %% [mm^2]', 'Szükséges vízszintes kengyel keresztmetszet');
        $blc->note('A Nemzeti Melléklet a 0.25 szorzót 0.5-re módosítja.');
        $blc->label($f3->_Asw1min/$f3->_Asw1, 'kengyel kihasználtság');
        if ($f3->_ac > 0.5*$f3->_hc) {
            $blc->txt('$a_c > 0.5*h_c$ -  Függőleges kengyelezés nem hagyható el!');
        } else {
            $blc->txt('$a_c < 0.5*h_c$  - Függőleges kengyelezés elhagyható.');
        }
        $blc->numeric('nsw2', ['n_(sw2)', 'Függőleges zárt $phi'.$f3->_phisw2.'$ kengyelek száma'], 2, '');
        $blc->def('Asw2', floor($f3->_nsw2*$ec->A($f3->_phisw2, 2)), 'A_(sw2) = %% [mm^2]', 'Alkalamazott függőleges kengyel keresztmetszet');
        $blc->def('Asw2min', ceil(0.5*($f3->_FEd/$f3->_rfyd)*1000), 'A_(sw2,min) = 0.5*F_(Ed)/(f_(yd)) = %% [mm^2]', 'Szükséges függőleges kengyel keresztmetszet');
        $blc->label($f3->_Asw2min/$f3->_Asw2, 'függőleges kengyel kihasználtság');
        $blc->note('Vsz. kengyelek kampói az oszlopba kerüljenek. A függ. kengyelek kampói a rk. alsó felén legyenek. A függ. kengyelek vegyék körbe a vővasat és vsz. kengyeleket.');

        $blc->def('k1', 1, 'k_1 = %%');
        $blc->def('upsilon', 1 - $f3->_cfck/250, 'upsilon = 1 - f_(ck)/250 = %%');
        $blc->def('sigmaRdmax', $f3->_k1*$f3->_upsilon*$f3->_cfcd, 'sigma_(Rd,max) = k_1*upsilon*f_(cd) = %% [N/(mm^2)]', 'Helyi nyomószilárdság');
        $blc->note('[1] csomópont mindhárom lapján egyforma nyomófeszültségek lépnek fel, a legmeredekebb $theta$ szögnél megegyezik a fenti helyi nyomószilárdsággal.');
        $blc->def('x', $f3->_FEd/($f3->_b*$f3->_sigmaRdmax)*1000, 'x = F_(Ed)/(b*sigma_(Rd,max)) = %% [mm]');
        $qa = -0.5;
        $qb = $f3->_d;
        $qc = -1*(0.5*pow($f3->_x, 2) + $f3->_ac*$f3->_x + $f3->_alpha*$f3->_aH*$f3->_x);
        $blc->def('y1', $ec->quadratic($qa, $qb, $qc, 'root1'), 'y1 = %% [mm]');
        $blc->def('y2', $ec->quadratic($qa, $qb, $qc, 'root2'), 'y2 = %% [mm]');
        $blc->def('theta', rad2deg(atan($f3->_x/$f3->_y1)), 'theta = %% [deg]');
        // itt: van valid theta, fölső számítás törölhető; z0 számítása jön itt a fenti az jó képlet
    }
}
