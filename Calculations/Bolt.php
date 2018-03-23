<?php

namespace Calculation;

Class Bolt extends \Ecc
{

    public function calc($f3)
    {
        \Ec::load();

        \Ec::boltList('btName');
        \Ec::matList('btMat', '8.8', 'Csavar anyag');
        \Ec::matList('stMat', 'S235', 'Lemez anyag');
        \Blc::input('t', 'Kisebbik lemez vastagság', 10, 'mm', '');
        \Blc::input('n', 'Nyírási síkok száma', 1, '', '');
        \Blc::input('N', '`F_(t.Ed)` Húzóerő', 20, 'kN', '');
        \Blc::input('V', '`F_(v.Ed)` Nyíróerő', 30, 'kN', '');

        \Blc::region0('r0', 'Jellemzők');
            \Blc::math('btMat = '.$f3->_btMat);
            \Blc::def('f_yb', \Ec::matProp($f3->_btMat, 'fy'),'f_(y,b) = %% [MPa]', 'Csavar folyáshatár');
            \Blc::def('f_ub', \Ec::matProp($f3->_btMat, 'fu'),'f_(u,b) = %% [MPa]', 'Csavar szakítószilárdság');
            \Blc::def('d_0', \Ec::boltProp($f3->_btName, 'd0'),'d_0 = %% [mm]', 'Lyuk átmérő');
            \Blc::def('A', \Ec::boltProp($f3->_btName, 'A'),'A = %% [mm^2]', 'Csavar keresztmetszeti terület');
            \Blc::def('A_s', \Ec::boltProp($f3->_btName, 'As'),'A_s = %% [mm^2]', 'Csavar húzási keresztmetszet');
            \Blc::math('stMat = '.$f3->_stMat);
            \Blc::def('f_u', \Ec::fu($f3->_stMat, $f3->_t),'f_u = %% [MPa]', 'Lemez szakítószilárdság');
        \Blc::region1('r0');

        \Blc::region0('r1', 'Csavar adatbázis');
            \Blc::table(\Ec::boltDb(), 'Csavar','');
        \Blc::region1('r1');

        \Blc::h1('Egy csavar húzási- és kigombolódási ellenállása', '***D*** nem feszített, húzott és ***E*** feszített, húzott csavarok');
        \Blc::def('F_tRd', \Ec::FtRd($f3->_btName, $f3->_btMat),'F_(t,Rd) = %% [kN]', 'Csavar húzási ellenállása');
        \Blc::label($f3->_N/$f3->_F_tRd, 'Húzási kihasználtság');
        \Blc::def('B_pRd', \Ec::BpRd($f3->_btName, $f3->_stMat, $f3->_t),'B_(p,Rd) = %% [kN]', 'Csavar kigombolódási ellenállása');
        \Blc::label($f3->_N/$f3->_B_pRd, 'Kigombolódási kihasználtság');

        \Blc::h1('Egy csavar nyírási- és palástnyomási ellenállása', '***A*** osztály: nem feszített, nyírt csavar');
        \Blc::boo('inner', 'Belső csavar', 1, '');
        \Blc::input('e1', 'Peremtávolság (csavarképpel párhuzamos)', 50, 'mm', '');
        \Blc::input('e2', 'Peremtávolság (csavarképre merőleges)', 50, 'mm', '');
        \Blc::def('F_vRd', \Ec::FvRd($f3->_btName, $f3->_btMat, $f3->_n),'F_(v,Rd) = %% [kN]', 'Csavar nyírási ellenállása');
        \Blc::label($f3->_V/$f3->_F_vRd, 'Nyírási kihasználtság');
        \Blc::def('F_bRd', \Ec::FbRd($f3->_btName, $f3->_btMat, $f3->_stMat, $f3->_e1, $f3->_e2, $f3->_t, $f3->_inner),'F_(b,Rd) = %% [kN]', 'Csavar palástnyomási ellenállása');
        \Blc::label($f3->_V/$f3->_F_bRd, 'Palástnyomási kihasználtság');

        \Blc::region0('r2', '*k.1* és *&alpha;.b* tényezők');
            \Blc::math('k_1 = '.$f3->___k1.'%%%alpha_b = '.$f3->___alphab);
        \Blc::region1('r2');

        \Blc::h1('Egy csavar húzás és nyírás interakciója', '***AD*** osztály');
        \Blc::def('U_vt', (($f3->_V / $f3->_F_vRd) + ($f3->_N / (1.4*$f3->_F_tRd)))*100, 'U_(vt) = F_(v,Ed)/F_(v,Rd) + F_(t,Ed)/(1.4*F_(t,Rd)) = %% [%]', 'Interakciós kihasználtság');
        \Blc::label($f3->_U_vt/100, 'Interakciós kihasználtság');

        \Blc::h1('Egyszerűsített eljárás nyírásra optimalizálásra');
        $deltaDb = array(
            '400' => array(
                '360' => 3.18
            ),
            '500' => array(
                '360' => 2.53,
                '430' => 3.04
            ),
            '600' => array(
                '360' => 2.12,
                '430' => 2.54,
                '510' => 3.01,
                '530' => 3.13
            ),
            '800' => array(
                '360' => 1.59,
                '430' => 1.90,
                '510' => 2.26,
                '530' => 2.34
            ),
            '1000' => array(
                '360' => 1.27,
                '430' => 1.52,
                '510' => 1.80,
                '530' => 1.82
            )
        );
        $delta = $deltaDb[\Ec::matProp($f3->_btMat, 'fu')][\Ec::matProp($f3->_stMat, 'fu')];
        \Blc::def('delta', $delta, 'delta = %%', '*[Általános eljárások 6.3. táblázat]*');
        \Blc::info0('r3');
            \Blc::def('d_min', number_format($f3->_delta * $f3->_t, 0),'d_(min) = delta*t = %% [mm]');
            \Blc::def('e1_opt', number_format(2*$f3->_d_0, 0),'e_(1,opt) = 2*d_0 = %% [mm]', 'Szélső peremtávolság (csavarképpel párhuzamos)');
            \Blc::def('e2_opt', number_format(1.5*$f3->_d_0, 0),'e_(2,opt) = 1.5*d_0 = %% [mm]', 'Szélső peremtávolság (csavarképre merőleges)');
            \Blc::def('p1_opt', number_format(3*$f3->_d_0, 0),'p_(1,opt) = 3*d_0 = %% [mm]', 'Belső csvaar távolság (csavarképpel párhuzamos)');
            \Blc::def('p2_opt', number_format(3*$f3->_d_0, 0),'p_(2,opt) = 3*d_0 = %% [mm]', 'Belső csvavar távolság (csavarképre merőleges)');
        \Blc::info1('r3');

        \Blc::h2('Feszített csavarok nyírásra');
        if ($f3->_btMat != '10.9') {
            \Blc::label('no', 'Nem 10.9 csavar');
        }
        \Blc::input('n_s', 'Súrlódó felületek száma', 1, '', '');
        \Blc::input('mu', 'Súrlódási tényező', 0.2, '', '**Súrlódási tényezők:** **0.5** *Sörétezett vagy szemcsefújt festetlen felület*, **0.4** *Sörétezett vagy szemcsefújt festett felület*, **0.3** *Drótkefézett vagy lángszórással tisztított felület*, **0.2** *Kezeletlen felület*');
        \Blc::input('F_Ed_ser', '`F_(Ed,ser):` Nyíróerő használhatósági határállapotban', 10, 'kN', '');

        \Blc::md('***B*** osztályú nyírt csavarok használhatósági határállapotig működnek feszített csavarként.');
        \Blc::md('Teherbírási határállapotban ***A*** csavarként. Használhatósági határállapotban:');

        \Blc::def('F_pC', 0.7*$f3->_f_ub*$f3->_A_s/1000,'F_(p,C) = 0.7*f_(u,b)*A_s = %% [kN]', 'Előírt feszítőerő');
        \Blc::def('F_s_Rd', (($f3->_n_s*$f3->_mu)/$f3->__GM3)*$f3->_F_pC,'F_(s,Rd) = (n_s*mu)/gamma_(M3)*F_(p,C) = %% [kN]', 'Megcsúszással szembeni ellenállás');
        $f3->_U_s_ser = $f3->_F_Ed_ser/$f3->_F_s_Rd;
        \Blc::label($f3->_U_s_ser, 'Kihasználtság használhatósági határállapotban');

        \Blc::md('***C*** osztályú nyírt csavar:');
        $f3->_U_s = $f3->_V/$f3->_F_s_Rd;
        \Blc::label($f3->_U_s, 'Kihasználtság teherbírási határállpotban');
        \Blc::label($f3->_V/$f3->_F_bRd, 'Palástnyomási kihasználtság');

        \Blc::md('***CE*** osztályú húzott-nyírt csavar:');
        \Blc::def('F_s_tv_Rd', (($f3->_n_s*$f3->_mu)/$f3->__GM3)*($f3->_F_pC-0.8*$f3->_N),'F_(s,tv,Rd) = (n_s*mu)/gamma_(M3)*(F_(p,C)-0.8*F_(t,Ed)) = %% [kN]', 'Interakciós ellenállás');
        $f3->_U_s_tv = $f3->_V/$f3->_F_s_tv_Rd;
        \Blc::label($f3->_U_s_tv, 'Interakciós kihasználtság');

        \Blc::h2('Csoportos kiszakadás');
    }
}
