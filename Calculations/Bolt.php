<?php

namespace Calculation;

Class Bolt extends \Ecc
{

    public function calc($f3)
    {
        $ec = \Ec::instance();
        $blc = \Blc::instance();

        $blc->toc();

        $ec->boltList('btName');
        $ec->matList('btMat', '8.8', 'Csavar anyag');
        $ec->matList('stMat', 'S235', 'Lemez anyag');
        $blc->input('t', 'Kisebbik lemez vastagság', 10, 'mm', '');
        $blc->input('n', 'Nyírási síkok száma', 1, '', '');
        $blc->input('N', '`F_(t.Ed)` Húzóerő', 20, 'kN', '');
        $blc->input('V', '`F_(v.Ed)` Nyíróerő', 30, 'kN', '');

        $blc->region0('r0', 'Jellemzők');
            $blc->math('btMat = '.$f3->_btMat);
            $blc->def('f_yb', $ec->matProp($f3->_btMat, 'fy'),'f_(y,b) = %% [MPa]', 'Csavar folyáshatár');
            $blc->def('f_ub', $ec->matProp($f3->_btMat, 'fu'),'f_(u,b) = %% [MPa]', 'Csavar szakítószilárdság');
            $blc->def('d_0', $ec->boltProp($f3->_btName, 'd0'),'d_0 = %% [mm]', 'Lyuk átmérő');
            $blc->def('A', $ec->boltProp($f3->_btName, 'A'),'A = %% [mm^2]', 'Csavar keresztmetszeti terület');
            $blc->def('A_s', $ec->boltProp($f3->_btName, 'As'),'A_s = %% [mm^2]', 'Csavar húzási keresztmetszet');
            $blc->math('stMat = '.$f3->_stMat);
            $blc->def('f_u', $ec->fu($f3->_stMat, $f3->_t),'f_u = %% [MPa]', 'Lemez szakítószilárdság');
        $blc->region1('r0');

        $blc->region0('r1', 'Csavar adatbázis');
            $blc->table($ec->boltDb(), 'Csavar','');
        $blc->region1('r1');

        $blc->h1('Egy csavar húzási- és kigombolódási ellenállása', '***D*** nem feszített, húzott és ***E*** feszített, húzott csavarok');
        $blc->def('F_tRd', $ec->FtRd($f3->_btName, $f3->_btMat),'F_(t,Rd) = %% [kN]', 'Csavar húzási ellenállása');
        $blc->label($f3->_N/$f3->_F_tRd, 'Húzási kihasználtság');
        $blc->def('B_pRd', $ec->BpRd($f3->_btName, $f3->_stMat, $f3->_t),'B_(p,Rd) = %% [kN]', 'Csavar kigombolódási ellenállása');
        $blc->label($f3->_N/$f3->_B_pRd, 'Kigombolódási kihasználtság');

        $blc->h1('Egy csavar nyírási- és palástnyomási ellenállása', '***A*** osztály: nem feszített, nyírt csavar');
        $blc->boo('inner', 'Belső csavar', 1, '');
        $blc->input('e1', 'Peremtávolság (csavarképpel párhuzamos)', 50, 'mm', '');
        $blc->input('e2', 'Peremtávolság (csavarképre merőleges)', 50, 'mm', '');
        $blc->def('F_vRd', $ec->FvRd($f3->_btName, $f3->_btMat, $f3->_n),'F_(v,Rd) = %% [kN]', 'Csavar nyírási ellenállása');
        $blc->label($f3->_V/$f3->_F_vRd, 'Nyírási kihasználtság');
        $blc->def('F_bRd', $ec->FbRd($f3->_btName, $f3->_btMat, $f3->_stMat, $f3->_e1, $f3->_e2, $f3->_t, $f3->_inner),'F_(b,Rd) = %% [kN]', 'Csavar palástnyomási ellenállása');
        $blc->label($f3->_V/$f3->_F_bRd, 'Palástnyomási kihasználtság');

        $blc->region0('r2', '*k.1* és *&alpha;.b* tényezők');
            $blc->math('k_1 = '.$f3->___k1.'%%%alpha_b = '.$f3->___alphab);
        $blc->region1('r2');

        $blc->h1('Egy csavar húzás és nyírás interakciója', '***AD*** osztály');
        $blc->def('U_vt', (($f3->_V / $f3->_F_vRd) + ($f3->_N / (1.4*$f3->_F_tRd)))*100, 'U_(vt) = F_(v,Ed)/F_(v,Rd) + F_(t,Ed)/(1.4*F_(t,Rd)) = %% [%]', 'Interakciós kihasználtság');
        $blc->label($f3->_U_vt/100, 'Interakciós kihasználtság');

        $blc->h1('Egyszerűsített eljárás nyírásra optimalizálásra');
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
        $delta = $deltaDb[$ec->matProp($f3->_btMat, 'fu')][$ec->matProp($f3->_stMat, 'fu')];
        $blc->def('delta', $delta, 'delta = %%', '*[Általános eljárások 6.3. táblázat]*');
        $blc->info0('r3');
            $blc->def('d_min', number_format($f3->_delta * $f3->_t, 0),'d_(min) = delta*t = %% [mm]');
            $blc->def('e1_opt', number_format(2*$f3->_d_0, 0),'e_(1,opt) = 2*d_0 = %% [mm]', 'Szélső peremtávolság (csavarképpel párhuzamos)');
            $blc->def('e2_opt', number_format(1.5*$f3->_d_0, 0),'e_(2,opt) = 1.5*d_0 = %% [mm]', 'Szélső peremtávolság (csavarképre merőleges)');
            $blc->def('p1_opt', number_format(3*$f3->_d_0, 0),'p_(1,opt) = 3*d_0 = %% [mm]', 'Belső csvaar távolság (csavarképpel párhuzamos)');
            $blc->def('p2_opt', number_format(3*$f3->_d_0, 0),'p_(2,opt) = 3*d_0 = %% [mm]', 'Belső csvavar távolság (csavarképre merőleges)');
        $blc->info1('r3');

        $blc->h1('Feszített csavarok nyírásra');
        if ($f3->_btMat != '10.9') {
            $blc->label('no', 'Nem 10.9 csavar');
        }
        $blc->input('n_s', 'Súrlódó felületek száma', 1, '', '');
        $blc->input('mu', 'Súrlódási tényező', 0.2, '', '**Súrlódási tényezők:** **0.5** *Sörétezett vagy szemcsefújt festetlen felület*, **0.4** *Sörétezett vagy szemcsefújt festett felület*, **0.3** *Drótkefézett vagy lángszórással tisztított felület*, **0.2** *Kezeletlen felület*');
        $blc->input('F_Ed_ser', '`F_(Ed,ser):` Nyíróerő használhatósági határállapotban', 10, 'kN', '');

        $blc->md('***B*** osztályú nyírt csavarok használhatósági határállapotig működnek feszített csavarként.');
        $blc->md('Teherbírási határállapotban ***A*** csavarként. Használhatósági határállapotban:');

        $blc->def('F_pC', 0.7*$f3->_f_ub*$f3->_A_s/1000,'F_(p,C) = 0.7*f_(u,b)*A_s = %% [kN]', 'Előírt feszítőerő');
        $blc->def('F_s_Rd', (($f3->_n_s*$f3->_mu)/$f3->__GM3)*$f3->_F_pC,'F_(s,Rd) = (n_s*mu)/gamma_(M3)*F_(p,C) = %% [kN]', 'Megcsúszással szembeni ellenállás');
        $f3->_U_s_ser = $f3->_F_Ed_ser/$f3->_F_s_Rd;
        $blc->label($f3->_U_s_ser, 'Kihasználtság használhatósági határállapotban');

        $blc->md('***C*** osztályú nyírt csavar:');
        $f3->_U_s = $f3->_V/$f3->_F_s_Rd;
        $blc->label($f3->_U_s, 'Kihasználtság teherbírási határállpotban');
        $blc->label($f3->_V/$f3->_F_bRd, 'Palástnyomási kihasználtság');

        $blc->md('***CE*** osztályú húzott-nyírt csavar:');
        $blc->def('F_s_tv_Rd', (($f3->_n_s*$f3->_mu)/$f3->__GM3)*($f3->_F_pC-0.8*$f3->_N),'F_(s,tv,Rd) = (n_s*mu)/gamma_(M3)*(F_(p,C)-0.8*F_(t,Ed)) = %% [kN]', 'Interakciós ellenállás');
        $f3->_U_s_tv = $f3->_V/$f3->_F_s_tv_Rd;
        $blc->label($f3->_U_s_tv, 'Interakciós kihasználtság');

        $blc->h1('Csoportos kiszakadás');
        $blc->md('`TODO`');
    }
}
