<?php

namespace Calculation;

Class Pin extends \Ecc
{

    /**
     * @var $f3 \Base
     * @var $blc \Blc
     * @var $ec \Ec\Ec
     * @throws \Exception
     */
    public function calc(object $f3, object $blc, object $ec): void
    {
        $blc->toc();

        $blc->img('https://structure.hu/ecc/pin0.jpg', 'Híd saru');

        $blc->h1('Saru erők', '');
        $blc->numeric('REdz', ['R_(Ed,z)', 'Függőleges reakcióerő'], 950, 'kN', '');
        $blc->numeric('REdx', ['R_(Ed,x)', 'Hídtengely irányú vízszintes reakcióerő'], 10, 'kN', '');
        $blc->numeric('REdy', ['R_(Ed,y)', 'Kereszt irányú vízszintes reakcióerő'], 90, 'kN', '');
        $blc->numeric('REdzser', ['R_(Ed,z,ser)', 'Függőleges reakcióerő használhatósági határállapotban'], 460, 'kN', '');
        $blc->numeric('REdxser', ['R_(Ed,x,ser)', 'Hídtengely irányú vízszintes reakcióerő használhatósági határállapotban'], 200, 'kN', '');
        $blc->numeric('lamda', ['lamda', 'Dinamikus tényező'], 1.2, '', '');
        $blc->info0('REd');
            $blc->def('REd', \H3::n1($f3->_lamda*sqrt(pow($f3->_REdz, 2) + pow($f3->_REdx, 2))), 'R_(Ed) = lamda*sqrt(R_(Ed,z)^2 + R_(Ed,x)^2) = %% [kN]');
            $blc->def('REdser', \H3::n1($f3->_lamda*sqrt(pow($f3->_REdzser, 2) + pow($f3->_REdxser, 2))), 'R_(Ed,ser) = lamda*sqrt(R_(Ed,z,ser)^2 + R_(Ed,x,ser)^2) = %% [kN]');
        $blc->info1('REd');

        $blc->h1('Csap kialakítás', 'Csavar osztály: ***A*** - tengelyre merőlegesen terhelt, nem feszített');
        $ec->matList('btMat', '6.8', 'Csap anyagminőség');
        $ec->saveMaterialData($f3->_btMat, 'b');
        $blc->success0('s0');
            $blc->def('d_y', ceil(sqrt((4*$f3->_REd*1000)/(pi()*$f3->_bfy))), 'd_(y) = sqrt((4*R_(Ed))/(pi*f_(y,b))) = %% [mm]', 'Csavar folyás alapján felvett átmérő');
        $blc->success1('s0');
        $blc->numeric('d', ['d', 'Alkalamzott csap átmérő'], 70, 'mm', '');
        $blc->def('d0', $f3->_d + 3, 'd_0 = d + 3 = %% [mm]', 'Figyelembe vett lukméret');
        $blc->lst('n', ['1' => 1, '2' => 2], ['n', 'Nyírási síkok száma'], 2, '', '');
        $blc->def('e1', 2*$f3->_d0, 'e_1 = 2*d_0 = %% [mm]', 'Figyelembe vett peremtávolság');
        $blc->def('e2', 2*$f3->_d0, 'e_2 = 2*d_0 = %% [mm]', 'Figyelembe vett peremtávolság');
        $blc->def('A_b', \H3::n0($ec->A($f3->_d)), 'A_b = (d^2*pi)/4 = %% [mm^2]', 'Figyelembe vett csap keresztmetszeti terület');

        $blc->h1('Sarulemezek', '');
        $blc->numeric('t1', ['t_1', 'Szélső talp lemezvastagság'], 25, 'mm', 'Közrefogó lemezek');
        $blc->numeric('t2', ['t_2', 'Belső hídlemez lemezvastagság'], 40, 'mm', 'Belső lemez');
        $blc->def('c', 2, 'c = 2 [mm]', 'Lemezek közti hézag');
        $ec->matList('stMat', 'S355', 'Lemez anyagminőség');
        $ec->saveMaterialData($f3->_stMat, 's');
        if (max($f3->_t1, $f3->_t2) >= 40) {
            $blc->def('sfy', $ec->fy($f3->_stMat, max($f3->_t1, $f3->_t2)), 'f_(y,b)(t_(max)) = %% [N/(mm^2)]', 'Lemezvastagság miatt csökkentett folyáshatár');
            $blc->def('sfu', $ec->fu($f3->_stMat, max($f3->_t1, $f3->_t2)), 'f_(u,b)(t_(max)) = %% [N/(mm^2)]', 'Lemezvastagság miatt csökkentett szakítószilárdság');
        }

        $write = array(
            array('size' => 10, 'x' => 90, 'y' => 360, 'text' => 't1='.$f3->_t1.''),
            array('size' => 10, 'x' => 255, 'y' => 360, 'text' => 't1='.$f3->_t1.''),
            array('size' => 10, 'x' => 175, 'y' => 360, 'text' => 't2='.$f3->_t2.''),
            array('size' => 10, 'x' => 320, 'y' => 245, 'text' => 'd='.$f3->_d.''),
            array('size' => 10, 'x' => 25, 'y' => 245, 'text' => 'd0='.$f3->_d0.''),

        );
        $blc->write('vendor/resist/ecc-calculations/canvas/pin0.jpg', $write, 'Saru elrendezés');

        $blc->h2('Lemezek varratellenőrzése');
        $weld = new \Calculation\Weld();
        $blc->numeric('a', ['a', 'Varrat gyökméret'], 6, 'mm');
        $blc->numeric('L', ['L', 'Lemez szélesség'], 300, 'mm');
        $f3->_w = 1;
        $f3->_t = max($f3->_t1, $f3->_t2);
        $f3->_F = $f3->_REd;
        $f3->_mat = $f3->_stMat;
        $weld->moduleWeld($f3, $blc, $ec);
        $blc->note('[Varrat modul](https://structure.hu/calc/Weld) betöltésével számítva.');

        $blc->h1('Csap ellenőrzése');
        $blc->h2('Nyírt csavar ellenállása', 'Csap csavarként való figyelembevételéhez');
        $blc->def('FVRd', $ec->FvRd('M12', $f3->_btMat, $f3->_n, $f3->_A_b), 'F_(V,Rd) = %% [kN]', 'Csavarként vett csap nyírási ellenállása');
        $blc->label($f3->_REd/$f3->_FVRd, 'kihasználtság');
        $blc->txt('', '$R_(Ed)/F_(V,Rd)$');

        $blc->h2('Nyírt rugalmas csap ellenállása', '');
        $blc->def('FVRdel', ($f3->_n*0.6*$f3->_A_b*$f3->_bfy)/($f3->__GM2*1000), 'F_(V,Rd, el) = (n*0.6*A_b*f_(y,b))/gamma_(M2) = %% [kN]', 'Csap rugalmas nyírási ellenállása');
        $blc->label($f3->_REd/$f3->_FVRdel, 'kihasználtság');
        $blc->txt('', '$R_(Ed)/F_(V,Rd,el)$');

        $blc->h2('Csavar palástnyomási ellenállása', 'Csap csavarként való figyelembevételéhez ');
        $k1 = min(2.8*($f3->_e2/ $f3->_d0) - 1.7, 2.5);
        $alphab = min(($f3->_e1/ (3* $f3->_d0)), $f3->_bfu/ $f3->_sfu, 1);
        $FbRd1 = \H3::n2($k1*(($alphab*$f3->_sfu* $f3->_d * $f3->_t1)/(1000 * $f3->__GM2)));
        $FbRd2 = \H3::n2($k1*(($alphab*$f3->_sfu* $f3->_d * $f3->_t2)/(1000 * $f3->__GM2)));
        $blc->def('FbRd1', $FbRd1, 'F_(b,Rd,t1) = %% [kN]', 'Csavarként vett csap palástnyomási ellenállása, $t_1$ lemezhez');
        $blc->label((0.5*$f3->_REd)/$f3->_FbRd1, 'kihasználtság');
        $blc->txt('', '$(0.5*R_(Ed))/F_(b,Rd,t1)$');
        $blc->def('FbRd2', $FbRd2, 'F_(b,Rd,t2) = %% [kN]', 'Csavarként vett csap palástnyomási ellenállása, $t_2$ lemezhez');
        $blc->label($f3->_REd/$f3->_FbRd2, 'kihasználtság');
        $blc->txt('', '$R_(Ed)/F_(b,Rd,t2)$');

        $blc->h2('Rugalmas csap palástnyomási ellenállása', '');
        $FbRdel1 = \H3::n2((1.5*$f3->_sfy*$f3->_d*$f3->_t1)/($f3->__GM2*1000));
        $FbRdel2 = \H3::n2((1.5*$f3->_sfy*$f3->_d*$f3->_t2)/($f3->__GM2*1000));
        $blc->def('FbRdel1', $FbRdel1, 'F_(b,Rd,el,t1) = (1.5*f_y*d*t_1)/gamma_(M2) = %% [kN]', 'Csap palástnyomási ellenállása, $t1$ lemezhez');
        $blc->label((0.5*$f3->_REd)/$f3->_FbRdel1, 'kihasználtság');
        $blc->txt('', '$(0.5*R_(Ed))/F_(b,Rd,el,t1)$');
        $blc->def('FbRdel2', $FbRdel2, 'F_(b,Rd,el,t2) = (1.5*f_y*d*t_2)/gamma_(M2) = %% [kN]', 'Csap palástnyomási ellenállása, $t2$ lemezhez');
        $blc->label(($f3->_REd)/$f3->_FbRdel2, 'kihasználtság');
        $blc->txt('', '$(R_(Ed))/F_(b,Rd,el,t2)$');

        $blc->h2('Cserélhető csap használhatósági feltétele nyírásból', 'Csak tervezetten cserélhető csapok esetén releváns');
        $FbRdser1 = \H3::n2((0.6*$f3->_d*$f3->_t1*$f3->_sfy)/($f3->__GM6ser*1000));
        $FbRdser2 = \H3::n2((0.6*$f3->_d*$f3->_t2*$f3->_sfy)/($f3->__GM6ser*1000));
        $blc->def('FbRdser1', $FbRdser1, 'F_(b,Rd,ser,t1) = (0.6*f_y*d*t_1)/gamma_(M6,ser) = %% [kN]', 'Használati határteherbírás, $t1$ lemezhez');
        $blc->label(((0.5*$f3->_REdser))/$f3->_FbRdser1, 'kihasználtság');
        $blc->txt('', '$(0.5*R_(Ed,ser))/F_(b,Rd,ser,t1)$');
        $blc->def('FbRdser2', $FbRdser2, 'F_(b,Rd,ser,t2) = (0.6*f_y*d*t_2)/gamma_(M6,ser) = %% [kN]', 'Használati határteherbírás, $t2$ lemezhez');
        $blc->label(($f3->_REdser)/$f3->_FbRdser2, 'kihasználtság');
        $blc->txt('', '$(R_(Ed,ser))/F_(b,Rd,ser,t2)$');

        $blc->h2('Csap hajlítási ellenállása', '');
        $blc->def('MbEd', \H3::n2(($f3->_REd/8)*($f3->_t1*2 + $f3->_t2 + 2*$f3->_c)/1000), 'M_(b,Ed) = R_(Ed)/8*(2*t_1 + t_2 + 2*c) = %% [kNm]');
        $blc->def('Wel', 0.1*pow($f3->_d, 3), 'W_(el) = 0.1*d^3 = %% [mm^3]');
        $blc->def('MbRdel', \H3::n2((1.5*$f3->_Wel*$f3->_bfy)/($f3->__GM0*1000000)), 'M_(b,Rd,el) = (1.5*W_(el)*f_(y,b))/gamma_(M0) = %% [kNm]');
        $blc->label(($f3->_MbEd)/$f3->_MbRdel, 'kihasználtság');
        $blc->txt('', '$M_(b,Ed)/M_(b,Rd,el)$');

        $blc->h2('Cserélhető csap használhatósági feltétele hajlításból', 'Csak tervezetten cserélhető csapok esetén releváns');
        $blc->def('MbEdser', \H3::n2(($f3->_REdser/8)*($f3->_t1*2 + $f3->_t2 + 2*$f3->_c)/1000), 'M_(b,Ed,ser) = R_(Ed,ser)/8*(2*t_1 + t_2 + 2*c) = %% [kNm]');
        $blc->def('MbRdser', \H3::n2((0.8*$f3->_Wel*$f3->_bfy)/($f3->__GM6ser*1000000)), 'M_(b,Rd,ser) = (0.8*W_(el)*f_(y,b))/gamma_(M6,ser) = %% [kNm]');
        $blc->label($f3->_MbEdser/$f3->_MbRdser, 'kihasználtság');
        $blc->txt('', '$M_(b,Ed,ser)/M_(b,Rd,ser)$');

        $blc->h2('Nyírás és hajlítás interakciója');
        $blc->def('UMV', \H3::n2(pow($f3->_MbEd/$f3->_MbRdel, 2) + pow($f3->_REd/$f3->_FVRdel, 2)), '(M_(b,Ed)/M_(b,Rd,el))^2 + (R_(Ed)/F_(V,Rd,el))^2 = %%');
        $blc->label($f3->_UMV, 'kihasználtság');

        $blc->h1('Szerkesztési szabályok');
        $blc->img('https://structure.hu/ecc/pin1.jpg', 'Geometriai ellenőrzés');
        $blc->h2('Ellenőrzés adott lemezvastagsághoz');
        $blc->def('amint1', ceil(($f3->_REd*1000*$f3->__GM0)/(2*$f3->_t1*$f3->_sfy) + (2*$f3->_d0)/3), 'a_(min,t1) = (R_(Ed)*gamma_(M0))/(2*t_1*f_(y,s)) + (2*d_0)/3 = %%');
        $blc->def('amint2', ceil(($f3->_REd*1000*$f3->__GM0)/(2*$f3->_t2*$f3->_sfy) + (2*$f3->_d0)/3), 'a_(min,t1) = (R_(Ed)*gamma_(M0))/(2*t_2*f_(y,s)) + (2*d_0)/3 = %%');
        $blc->def('cmint1', ceil(($f3->_REd*1000*$f3->__GM0)/(2*$f3->_t1*$f3->_sfy) + ($f3->_d0)/3), 'c_(min,t1) = (R_(Ed)*gamma_(M0))/(2*t_1*f_(y,s)) + d_0/3 = %%');
        $blc->def('cmint2', ceil(($f3->_REd*1000*$f3->__GM0)/(2*$f3->_t2*$f3->_sfy) + ($f3->_d0)/3), 'c_(min,t2) = (R_(Ed)*gamma_(M0))/(2*t_2*f_(y,s)) + d_0/3 = %%');

        $blc->h2('Ellenőrzés adott peremtávolságokhoz és geometriához');
        $blc->txt('Lemez lekerekítési sugár csap középponttól $0.3d_0 = '. ceil(0.3*$f3->_d0).'[mm]$ távolságra indítva: $1.3d_0 = '. ceil(1.3*$f3->_d0).'[mm]$');
        $blc->txt('Bruttó peremtávolság: $1.6d_0 = '. ceil(1.6*$f3->_d0).'[mm]$, nettó peremtávolság: $0.75d_0 = '. ceil(0.75*$f3->_d0).'[mm]$');
        $blc->txt('Minimum lemez szélesség:  $2.5d_0 = '. ceil(2.5*$f3->_d0).'[mm]$');
        $blc->def('tmin', ceil(0.7*sqrt(($f3->_REd*1000*$f3->__GM0)/$f3->_sfy)), 't_(min) = 0.7*sqrt((R_(Ed)*gamma_(M0))/f_(y,s)) = %% [mm]');
        $blc->def('dmax', floor(2.5*$f3->_tmin), 'd_(max) = 2.5*t_(min) = %% [mm]');
    }
}
