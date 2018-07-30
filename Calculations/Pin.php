<?php

namespace Calculation;

Class Pin extends \Ecc
{

    public function calc($f3)
    {
        $ec = \Ec::instance();
        $blc = \Blc::instance();

        $blc->toc();

        $blc->img('https://structure.hu/ecc/pin0.jpg');

        $blc->h1('Saru erők', '');
        $blc->input('REdz', '`R_(Ed,z)` Függőleges reakcióerő', 950, 'kN', '');
        $blc->input('REdx', '`R_(Ed,x)` Hídtengely irányú vízszintes reakcióerő', 10, 'kN', '');
        $blc->input('REdy', '`R_(Ed,y)` Kereszt irányú vízszintes reakcióerő', 90, 'kN', '');
        $blc->input('REdzser', '`R_(Ed,z,ser)` Függőleges reakcióerő használhatósági határállapotban', 460, 'kN', '');
        $blc->input('REdxser', '`R_(Ed,x,ser)` Hídtengely irányú vízszintes reakcióerő használhatósági határállapotban', 200, 'kN', '');
        $blc->input('lamda', 'Dinamikus tényező', 1.2, '', '');
        $blc->def('REd', $f3->_lamda*sqrt(pow($f3->_REdz, 2) + pow($f3->_REdx, 2)), 'R_(Ed) = lamda*sqrt(R_(Ed,z)^2 + R_(Ed,x)^2) = %% [kN]');
        $blc->def('REdser', sqrt(pow($f3->_REdzser, 2) + pow($f3->_REdxser, 2)), 'R_(Ed,ser) = lamda*sqrt(R_(Ed,z,ser)^2 + R_(Ed,x,ser)^2) = %% [kN]');

        $blc->h1('Csap', 'Csavar osztály: ***A*** - tengelyre merőlegesen terhelt, nem feszített');
        $ec->matList('btMat', '6.8', 'Csap/csavar anyag');
        $blc->def('fyb', $ec->matProp($f3->_btMat, 'fy'), 'f_(y,b) = %% [MPa]', 'Csavar folyáshatár');
        $blc->def('fub', $ec->matProp($f3->_btMat, 'fu'), 'f_(u,b) = %% [MPa]', 'Csavar szakítószilárdság');
        $blc->success0('s0');
            $blc->def('d_y', ceil(sqrt((4*$f3->_REd*1000)/(pi()*$f3->_fyb))), 'd_y = sqrt((4*R_(Ed))/(pi*f_(y,b))) = %% [mm]', 'Csavar folyáshoz számított javasolt átmérő');
        $blc->success1('s0');
        $blc->input('d', 'Csap átmérő', 70, 'mm', '');
        $blc->def('d0', $f3->_d + 3, 'd_0 = d + 3 = %% [mm]', 'Figyelembe vett lukméret');
        $blc->input('n', 'Nyírási síkok száma', 2, '', '');
        $blc->def('e1', 2*$f3->_d0, 'e_1 = 2*d_0 = %% [mm]', 'Figyelembe vett peremtávolság');
        $blc->def('e2', 2*$f3->_d0, 'e_2 = 2*d_0 = %% [mm]', 'Figyelembe vett peremtávolság');
        $blc->def('A_b', (pow($f3->_d, 2)*pi())/4, 'A_b = (d^2*pi)/4 = %% [mm^2]', 'Figyelembe vett csap keresztmetszeti terület');

        $blc->h1('Sarulemezek', '');
        $blc->input('t1', 'Szélső talp lemezvastagság', 25, 'mm', '');
        $blc->input('t2', 'Belső hídlemez lemezvastagság', 40, 'mm', '');
        $blc->def('c', 2, 'c = 2 [mm]', 'Lemezek közti hézag');
        $ec->matList('stMat', 'S355', 'Lemez anyagminőség');
        $blc->def('fy', $ec->fy($f3->_stMat, max($f3->_t1, $f3->_t2)), 'f_y = %% [MPa]', 'Lemez folyáshatár');
        $blc->def('fu', $ec->fu($f3->_stMat, max($f3->_t1, $f3->_t2)), 'f_u = %% [MPa]', 'Lemez szakítószilárdság');

        $write = array(
            array('size' => 10, 'x' => 90, 'y' => 360, 'text' => 't1='.$f3->_t1.''),
            array('size' => 10, 'x' => 255, 'y' => 360, 'text' => 't1='.$f3->_t1.''),
            array('size' => 10, 'x' => 175, 'y' => 360, 'text' => 't2='.$f3->_t2.''),
            array('size' => 10, 'x' => 320, 'y' => 245, 'text' => 'd='.$f3->_d.''),
            array('size' => 10, 'x' => 25, 'y' => 245, 'text' => 'd0='.$f3->_d0.''),

        );
        $blc->write('vendor/resist/ecc-calculations/canvas/pin0.jpg', $write, 'Saru elrendezés');

        $blc->h1('Nyírt csavar ellenállása', '');
        $blc->def('FVRd', $ec->FvRd('M12', $f3->_btMat, $f3->_n, $f3->_A_b), 'F_(V,Rd) = %% [kN]', 'Csavarként vett csap nyírási ellenállása');

        $blc->h1('Nyírt rugalmas csap ellenállása', '');
        $blc->def('FVRdel', ($f3->_n*0.6*$f3->_A_b*$f3->_fyb)/($f3->__GM2*1000), 'F_(V,Rd, el) = (n*0.6*A_b*f_(y,b))/gamma_(M2) = %% [kN]', 'Csap rugalmas nyírási ellenállása');

        $blc->h1('Csavar palástnyomási ellenállása', '');
        $k1 = min(2.8*($f3->_e2/ $f3->_d0) - 1.7, 2.5);
        $alphab = min(($f3->_e1/ (3* $f3->_d0)), $f3->_fub/ $f3->_fu, 1);
        $FbRd1 = $k1*(($alphab*$f3->_fu* $f3->_d * $f3->_t1)/(1000 * $f3->__GM2));
        $FbRd2 = $k1*(($alphab*$f3->_fu* $f3->_d * $f3->_t2)/(1000 * $f3->__GM2));
        $blc->def('FbRd1', $FbRd1, 'F_(b,Rd,t1) = %% [kN]', 'Csavarként vett csap palástnyomási ellenállása, \`t1\` lemezhez');
        $blc->def('FbRd2', $FbRd2, 'F_(b,Rd,t2) = %% [kN]', 'Csavarként vett csap palástnyomási ellenállása, \`t2\` lemezhez');

        $blc->h1('Rugalmas csap palástnyomási ellenállása', '');
        $FbRdel1 = (1.5*$f3->_fy*$f3->_d*$f3->_t1)/($f3->__GM2*1000);
        $FbRdel2 = (1.5*$f3->_fy*$f3->_d*$f3->_t2)/($f3->__GM2*1000);
        $blc->def('FbRdel1', $FbRdel1, 'F_(b,Rd,el,t1) = (1.5*f_y*d*t_1)/gamma_(M2) = %% [kN]', 'Csap palástnyomási ellenállása, \`t1\` lemezhez');
        $blc->def('FbRdel2', $FbRdel2, 'F_(b,Rd,el,t2) = (1.5*f_y*d*t_2)/gamma_(M2) = %% [kN]', 'Csap palástnyomási ellenállása, \`t2\` lemezhez');

        $blc->h1('Cserélhető csap használhatósági feltétele nyírásból', 'Csak tervezetten cserélhető csapok esetén releváns');
        $FbRdser1 = (0.6*$f3->_d*$f3->_t1*$f3->_fy)/($f3->__GM6ser*1000);
        $FbRdser2 = (0.6*$f3->_d*$f3->_t2*$f3->_fy)/($f3->__GM6ser*1000);
        $blc->def('FbRdser1', $FbRdser1, 'F_(b,Rd,ser,t1) = (0.6*f_y*d*t_1)/gamma_(M6,ser) = %% [kN]', 'Használati határteherbírás, \`t1\` lemezhez');
        $blc->def('FbRdser2', $FbRdser2, 'F_(b,Rd,ser,t2) = (0.6*f_y*d*t_2)/gamma_(M6,ser) = %% [kN]', 'Használati határteherbírás, \`t2\` lemezhez');

        $blc->h1('Csap hajlítási ellenállása', '');
        $blc->def('MbEd', ($f3->_REd/8)*($f3->_t1*2 + $f3->_t2 + 2*$f3->_c)/1000, 'M_(b,Ed) = R_(Ed)/8*(2*t_1 + t_2 + 2*c) = %% [kNm]');
        $blc->def('Wel', 0.1*pow($f3->_d, 3), 'W_(el) = 0.1*d^3 = %% [mm^3]');
        $blc->def('MbRdel', (1.5*$f3->_Wel*$f3->_fyb)/($f3->__GM0*1000000), 'M_(b,Rd,el) = (1.5*W_(el)*f_(y,b))/gamma_(M0) = %% [kNm]');

        $blc->h1('Cserélhető csap használhatósági feltétele hajlításból', 'Csak tervezetten cserélhető csapok esetén releváns');
        $blc->def('MbEdser', ($f3->_REdser/8)*($f3->_t1*2 + $f3->_t2 + 2*$f3->_c)/1000, 'M_(b,Ed,ser) = R_(Ed)/8*(2*t_1 + t_2 + 2*c) = %% [kNm]');
        $blc->def('MbRdser', (0.8*$f3->_Wel*$f3->_fyb)/($f3->__GM6ser*1000000), 'M_(b,Rd,ser) = (0.8*W_(el)*f_(y,b))/gamma_(M6,ser) = %% [kNm]');

        $blc->h1('Nyírás és hajlítás interakciója');
        $blc->def('U70', pow($f3->_MbEd/$f3->_MbRdel, 2) + pow($f3->_REd/$f3->_FVRdel, 2), '%%');
        $blc->md('`TODO`');

        $blc->h1('Egyszerűsített varratellenőrzések');
        $blc->md('`TODO`');

        $blc->h1('Szerkesztési szabályok');
        $blc->md('`TODO`');

        $blc->h1('Bebetonozott tőcsavarok ellenőrzése');
        $blc->md('`TODO`');
    }
}
