<?php declare(strict_types = 1);
/**
 * Pinned joint analysis according to Eurocodes - Calculation class for Statika framework
 * (c) Bence VÁNKOS | https://structure.hu | https://github.com/r3sist/ecc-calculations
 */

namespace Statika\Calculations;

use \H3;
use Statika\EurocodeInterface;

Class Pin
{
    private Weld $weldCalculation;
    private Bolt $boltCalculation;

    public function __construct(Weld $weldCalculation, Bolt $boltCalculation)
    {
        $this->weldCalculation = $weldCalculation;
        $this->boltCalculation = $boltCalculation;
    }

    /**
     * @param Ec $ec
     * @throws \Exception
     */
    public function calc(EurocodeInterface $ec): void
    {
        $ec->h1('Saru erők', '');
        $ec->numeric('REdz', ['R_(Ed,z)', 'Függőleges reakcióerő'], 950, 'kN', '');
        $ec->numeric('REdx', ['R_(Ed,x)', 'Hídtengely irányú vízszintes reakcióerő'], 10, 'kN', '');
        $ec->numeric('REdy', ['R_(Ed,y)', 'Kereszt irányú vízszintes reakcióerő'], 90, 'kN', '');
        $ec->numeric('REdzser', ['R_(Ed,z,ser)', 'Függőleges reakcióerő használhatósági határállapotban'], 460, 'kN', '');
        $ec->numeric('REdxser', ['R_(Ed,x,ser)', 'Hídtengely irányú vízszintes reakcióerő használhatósági határállapotban'], 200, 'kN', '');
        $ec->numeric('lamda', ['lamda', 'Dinamikus tényező'], 1.2, '', '');
        $ec->info0();
            $ec->def('REd', H3::n1($ec->lamda*sqrt(($ec->REdz ** 2) + ($ec->REdx ** 2))), 'R_(Ed) = lamda*sqrt(R_(Ed,z)^2 + R_(Ed,x)^2) = %% [kN]');
            $ec->def('REdser', H3::n1($ec->lamda*sqrt(($ec->REdzser ** 2) + ($ec->REdxser ** 2))), 'R_(Ed,ser) = lamda*sqrt(R_(Ed,z,ser)^2 + R_(Ed,x,ser)^2) = %% [kN]');
        $ec->info1();

        $ec->h1('Csap kialakítás', 'Csavar osztály: ***A*** - tengelyre merőlegesen terhelt, nem feszített');
        $ec->steelMaterialListBlock('boltMaterialName', '6.8', ['', 'Csap anyagminőség']);
        $boltMaterial = $ec->getMaterial($ec->boltMaterialName);
        $ec->success0();
            $ec->def('d_y', ceil(sqrt((4*$ec->REd*1000)/(pi()*$boltMaterial->fy))), 'd_(y) = sqrt((4*R_(Ed))/(pi*f_(y,b))) = %% [mm]', 'Csavar folyás alapján felvett átmérő');
        $ec->success1();
        $ec->numeric('d', ['d', 'Alkalamzott csap átmérő'], 70, 'mm', '');
        $ec->def('d0', $ec->d + 3, 'd_0 = d + 3 = %% [mm]', 'Figyelembe vett lukméret');
        $ec->lst('n', ['1' => 1, '2' => 2], ['n', 'Nyírási síkok száma'], 2, '');
        $ec->def('e1', 2*$ec->d0, 'e_1 = 2*d_0 = %% [mm]', 'Figyelembe vett peremtávolság');
        $ec->def('e2', 2*$ec->d0, 'e_2 = 2*d_0 = %% [mm]', 'Figyelembe vett peremtávolság');
        $ec->def('A_b', H3::n0($ec->A($ec->d)), 'A_b = (d^2*pi)/4 = %% [mm^2]', 'Figyelembe vett csap keresztmetszeti terület');

        $ec->h1('Sarulemezek', '');
        $ec->numeric('t1', ['t_1', 'Szélső talp lemezvastagság'], 25, 'mm', 'Közrefogó lemezek');
        $ec->numeric('t2', ['t_2', 'Belső hídlemez lemezvastagság'], 40, 'mm', 'Belső lemez');
        $ec->def('c', 2, 'c = 2 [mm]', 'Lemezek közti hézag');
        $ec->structuralSteelMaterialListBlock('steelMaterialName', 'S355', ['', 'Lemez anyagminőség']);
        $steelMaterial = $ec->getMaterial($ec->steelMaterialName);
        if (max($ec->t1, $ec->t2) >= 40) {
            $ec->def('sfy', $ec->fy($ec->steelMaterialName, max($ec->t1, $ec->t2)), 'f_(y,b)(t_(max)) = %% [N/(mm^2)]', 'Lemezvastagság miatt csökkentett folyáshatár');
            $ec->def('sfu', $ec->fu($ec->steelMaterialName, max($ec->t1, $ec->t2)), 'f_(u,b)(t_(max)) = %% [N/(mm^2)]', 'Lemezvastagság miatt csökkentett szakítószilárdság');
        }

        $write = array(
            array('size' => 10, 'x' => 90, 'y' => 360, 'text' => 't1='.$ec->t1.''),
            array('size' => 10, 'x' => 255, 'y' => 360, 'text' => 't1='.$ec->t1.''),
            array('size' => 10, 'x' => 175, 'y' => 360, 'text' => 't2='.$ec->t2.''),
            array('size' => 10, 'x' => 320, 'y' => 245, 'text' => 'd='.$ec->d.''),
            array('size' => 10, 'x' => 25, 'y' => 245, 'text' => 'd0='.$ec->d0.''),

        );
        // @todo SVG image for Pin
//        $ec->write('vendor/resist/ecc-calculations/canvas/pin0.jpg', $write, 'Saru elrendezés');

        $ec->h2('Lemezek varratellenőrzése');
        $ec->numeric('a', ['a', 'Varrat gyökméret'], 6, 'mm');
        $ec->numeric('L', ['L', 'Lemez szélesség'], 300, 'mm');
        $ec->t = max($ec->t1, $ec->t2);
        $ec->F = $ec->REd;
        $this->weldCalculation->moduleWeld($ec->L, $ec->a, $ec->F, $ec->steelMaterialName, $ec->t, true);
        $ec->note('[Varrat modul](https://structure.hu/calc/Weld) betöltésével számítva.');

        $ec->h1('Csap ellenőrzése');
        $ec->h2('Nyírt csavar ellenállása', 'Csap csavarként való figyelembevételéhez');
        $ec->def('FVRd', $this->boltCalculation->moduleFvRd('M12', $ec->boltMaterialName, $ec->n, $ec->A_b), 'F_(V,Rd) = %% [kN]', 'Csavarként vett csap nyírási ellenállása');
        $ec->label($ec->REd/$ec->FVRd, 'kihasználtság');
        $ec->txt('', '$R_(Ed)/F_(V,Rd)$');

        $ec->h2('Nyírt rugalmas csap ellenállása', '');
        $ec->def('FVRdel', ($ec->n*0.6*$ec->A_b*$boltMaterial->fy)/($ec::GM2*1000), 'F_(V,Rd, el) = (n*0.6*A_b*f_(y,b))/gamma_(M2) = %% [kN]', 'Csap rugalmas nyírási ellenállása');
        $ec->label($ec->REd/$ec->FVRdel, 'kihasználtság');
        $ec->txt('', '$R_(Ed)/F_(V,Rd,el)$');

        $ec->h2('Csavar palástnyomási ellenállása', 'Csap csavarként való figyelembevételéhez ');
        $k1 = min(2.8*($ec->e2/ $ec->d0) - 1.7, 2.5);
        $alphab = min(($ec->e1/ (3* $ec->d0)), $boltMaterial->fu/ $steelMaterial->fu, 1);
        $FbRd1 = H3::n2($k1*(($alphab*$steelMaterial->fu* $ec->d * $ec->t1)/(1000 * $ec::GM2)));
        $FbRd2 = H3::n2($k1*(($alphab*$steelMaterial->fu* $ec->d * $ec->t2)/(1000 * $ec::GM2)));
        $ec->def('FbRd1', $FbRd1, 'F_(b,Rd,t1) = %% [kN]', 'Csavarként vett csap palástnyomási ellenállása, $t_1$ lemezhez');
        $ec->label((0.5*$ec->REd)/$ec->FbRd1, 'kihasználtság');
        $ec->txt('', '$(0.5*R_(Ed))/F_(b,Rd,t1)$');
        $ec->def('FbRd2', $FbRd2, 'F_(b,Rd,t2) = %% [kN]', 'Csavarként vett csap palástnyomási ellenállása, $t_2$ lemezhez');
        $ec->label($ec->REd/$ec->FbRd2, 'kihasználtság');
        $ec->txt('', '$R_(Ed)/F_(b,Rd,t2)$');

        $ec->h2('Rugalmas csap palástnyomási ellenállása', '');
        $FbRdel1 = H3::n2((1.5*$steelMaterial->fy*$ec->d*$ec->t1)/($ec::GM2*1000));
        $FbRdel2 = H3::n2((1.5*$steelMaterial->fy*$ec->d*$ec->t2)/($ec::GM2*1000));
        $ec->def('FbRdel1', $FbRdel1, 'F_(b,Rd,el,t1) = (1.5*f_y*d*t_1)/gamma_(M2) = %% [kN]', 'Csap palástnyomási ellenállása, $t1$ lemezhez');
        $ec->label((0.5*$ec->REd)/$ec->FbRdel1, 'kihasználtság');
        $ec->txt('', '$(0.5*R_(Ed))/F_(b,Rd,el,t1)$');
        $ec->def('FbRdel2', $FbRdel2, 'F_(b,Rd,el,t2) = (1.5*f_y*d*t_2)/gamma_(M2) = %% [kN]', 'Csap palástnyomási ellenállása, $t2$ lemezhez');
        $ec->label(($ec->REd)/$ec->FbRdel2, 'kihasználtság');
        $ec->txt('', '$(R_(Ed))/F_(b,Rd,el,t2)$');

        $ec->h2('Cserélhető csap használhatósági feltétele nyírásból', 'Csak tervezetten cserélhető csapok esetén releváns');
        $FbRdser1 = H3::n2((0.6*$ec->d*$ec->t1*$steelMaterial->fy)/($ec::GM6ser*1000));
        $FbRdser2 = H3::n2((0.6*$ec->d*$ec->t2*$steelMaterial->fy)/($ec::GM6ser*1000));
        $ec->def('FbRdser1', $FbRdser1, 'F_(b,Rd,ser,t1) = (0.6*f_y*d*t_1)/gamma_(M6,ser) = %% [kN]', 'Használati határteherbírás, $t1$ lemezhez');
        $ec->label(((0.5*$ec->REdser))/$ec->FbRdser1, 'kihasználtság');
        $ec->txt('', '$(0.5*R_(Ed,ser))/F_(b,Rd,ser,t1)$');
        $ec->def('FbRdser2', $FbRdser2, 'F_(b,Rd,ser,t2) = (0.6*f_y*d*t_2)/gamma_(M6,ser) = %% [kN]', 'Használati határteherbírás, $t2$ lemezhez');
        $ec->label(($ec->REdser)/$ec->FbRdser2, 'kihasználtság');
        $ec->txt('', '$(R_(Ed,ser))/F_(b,Rd,ser,t2)$');

        $ec->h2('Csap hajlítási ellenállása', '');
        $ec->def('MbEd', H3::n2(($ec->REd/8)*($ec->t1*2 + $ec->t2 + 2*$ec->c)/1000), 'M_(b,Ed) = R_(Ed)/8*(2*t_1 + t_2 + 2*c) = %% [kNm]');
        $ec->def('Wel', 0.1* ($ec->d ** 3), 'W_(el) = 0.1*d^3 = %% [mm^3]');
        $ec->def('MbRdel', H3::n2((1.5*$ec->Wel*$boltMaterial->fy)/($ec::GM0*1000000)), 'M_(b,Rd,el) = (1.5*W_(el)*f_(y,b))/gamma_(M0) = %% [kNm]');
        $ec->label(($ec->MbEd)/$ec->MbRdel, 'kihasználtság');
        $ec->txt('', '$M_(b,Ed)/M_(b,Rd,el)$');

        $ec->h2('Cserélhető csap használhatósági feltétele hajlításból', 'Csak tervezetten cserélhető csapok esetén releváns');
        $ec->def('MbEdser', H3::n2(($ec->REdser/8)*($ec->t1*2 + $ec->t2 + 2*$ec->c)/1000), 'M_(b,Ed,ser) = R_(Ed,ser)/8*(2*t_1 + t_2 + 2*c) = %% [kNm]');
        $ec->def('MbRdser', H3::n2((0.8*$ec->Wel*$boltMaterial->fy)/($ec::GM6ser*1000000)), 'M_(b,Rd,ser) = (0.8*W_(el)*f_(y,b))/gamma_(M6,ser) = %% [kNm]');
        $ec->label($ec->MbEdser/$ec->MbRdser, 'kihasználtság');
        $ec->txt('', '$M_(b,Ed,ser)/M_(b,Rd,ser)$');

        $ec->h2('Nyírás és hajlítás interakciója');
        $ec->def('UMV', H3::n2((($ec->MbEd / $ec->MbRdel) ** 2) + (($ec->REd / $ec->FVRdel) ** 2)), '(M_(b,Ed)/M_(b,Rd,el))^2 + (R_(Ed)/F_(V,Rd,el))^2 = %%');
        $ec->label($ec->UMV, 'kihasználtság');

        $ec->h1('Szerkesztési szabályok');
        $ec->img('https://structure.hu/ecc/pin1.jpg', 'Geometriai ellenőrzés');
        $ec->h2('Ellenőrzés adott lemezvastagsághoz');
        $ec->def('amint1', ceil(($ec->REd*1000*$ec::GM0)/(2*$ec->t1*$steelMaterial->fy) + (2*$ec->d0)/3), 'a_(min,t1) = (R_(Ed)*gamma_(M0))/(2*t_1*f_(y,s)) + (2*d_0)/3 = %%');
        $ec->def('amint2', ceil(($ec->REd*1000*$ec::GM0)/(2*$ec->t2*$steelMaterial->fy) + (2*$ec->d0)/3), 'a_(min,t1) = (R_(Ed)*gamma_(M0))/(2*t_2*f_(y,s)) + (2*d_0)/3 = %%');
        $ec->def('cmint1', ceil(($ec->REd*1000*$ec::GM0)/(2*$ec->t1*$steelMaterial->fy) + ($ec->d0)/3), 'c_(min,t1) = (R_(Ed)*gamma_(M0))/(2*t_1*f_(y,s)) + d_0/3 = %%');
        $ec->def('cmint2', ceil(($ec->REd*1000*$ec::GM0)/(2*$ec->t2*$steelMaterial->fy) + ($ec->d0)/3), 'c_(min,t2) = (R_(Ed)*gamma_(M0))/(2*t_2*f_(y,s)) + d_0/3 = %%');

        $ec->h2('Ellenőrzés adott peremtávolságokhoz és geometriához');
        $ec->txt('Lemez lekerekítési sugár csap középponttól $0.3d_0 = '. ceil(0.3*$ec->d0).'[mm]$ távolságra indítva: $1.3d_0 = '. ceil(1.3*$ec->d0).'[mm]$');
        $ec->txt('Bruttó peremtávolság: $1.6d_0 = '. ceil(1.6*$ec->d0).'[mm]$, nettó peremtávolság: $0.75d_0 = '. ceil(0.75*$ec->d0).'[mm]$');
        $ec->txt('Minimum lemez szélesség:  $2.5d_0 = '. ceil(2.5*$ec->d0).'[mm]$');
        $ec->def('tmin', ceil(0.7*sqrt(($ec->REd*1000*$ec::GM0)/$steelMaterial->fy)), 't_(min) = 0.7*sqrt((R_(Ed)*gamma_(M0))/f_(y,s)) = %% [mm]');
        $ec->def('dmax', floor(2.5*$ec->tmin), 'd_(max) = 2.5*t_(min) = %% [mm]');

        $ec->hr();
        $ec->img('https://structure.hu/ecc/pin0.jpg', 'Híd saru @ATL');
    }
}
