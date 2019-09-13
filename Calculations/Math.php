<?php

namespace Calculation;

/**
 * Math routines and incubator of test calculations for structural design - ECC calculation class
 *
 * (c) Bence VÁNKOS
 * https:// structure.hu
 */

Class Math extends \Ecc
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

        $blc->h1('Vas keresztmetszet');
        $f3->_As = $ec->rebarTable('AS');
        $blc->math('A_s = '.$f3->_As.' [mm^2]');
        $blc->region0('rebars', 'Keresztmetszetek');
            $blc->math('phi_(8): '.floor($ec->A(8)).' [mm^2]');
            $blc->math('phi_(10): '.floor($ec->A(10)).' [mm^2]');
            $blc->math('phi_(12): '.floor($ec->A(12)).' [mm^2]');
            $blc->math('phi_(16): '.floor($ec->A(16)).' [mm^2]');
            $blc->math('phi_(20): '.floor($ec->A(20)).' [mm^2]');
            $blc->math('phi_(25): '.floor($ec->A(25)).' [mm^2]');
            $blc->math('phi_(28): '.floor($ec->A(25)).' [mm^2]');
            $blc->math('phi_(32): '.floor($ec->A(32)).' [mm^2]');
        $blc->region1();

        $blc->h1('Lejtés');
        $blc->numeric('slope', ['', 'Lejtés'], 3, '% / °', '');
        $slope_deg = rad2deg(atan($f3->_slope/100));
        $slope_per = tan(deg2rad($f3->_slope))*100;
        $blc->def('slope_deg', \H3::n2($slope_deg), $f3->_slope.'% = %% [deg]', '');
        $blc->def('slope_per', \H3::n2($slope_per), $f3->_slope.'[deg] = %% [%]', '');
        $blc->numeric('L', ['L', 'Hossz'], 10, 'm', '');
        $blc->def('hdeg', \H3::n2($f3->_L*$f3->_slope*0.01), 'h_('.$f3->_slope.'%) = %% [m]', 'Emelkedés ');
        $blc->def('hper', \H3::n2($f3->_L*$slope_per*0.01), 'h_('.\H3::n2($slope_per).'%) = %% [m]', 'Emelkedés');

        $blc->h1('Hőmérséklet rudakon');
        $blc->math('L = '.$f3->_L.'[m]', 'Rúdhossz');
        $blc->def('alpha_T_st', number_format(0.000012, 6), 'alpha_(T,steel) = %% [1/K]', '');
        $blc->numeric('DeltaT', ['Delta T', 'Hőmérséklet változás'], 40, 'deg', '');
        $blc->def('DeltaL', number_format($f3->_alpha_T_st*($f3->_L*1000)*$f3->_DeltaT, 2), 'Delta L = %% [mm]', '');

        $blc->h1('Lineáris interpoláció');
        $blc->numeric('x1', ['x_1', ''], 1, '', '');
        $blc->numeric('y1', ['y_1', ''], 5, '', '');
        $blc->numeric('x2', ['x_2', ''], 2, '', '');
        $blc->numeric('y2', ['y_2', ''], 20, '', '');
        $blc->info0();
            $blc->numeric('x', ['x', ''], 3, '', '');
            $blc->def('y', (($f3->_x - $f3->_x1)*($f3->_y2 - $f3->_y1)/($f3->_x2 - $f3->_x1)) + $f3->_y1, 'y = %%');
        $blc->info1();

        $blc->jsxDriver();
        $js = '
            var b = JXG.JSXGraph.initBoard("interpolation", {boundingbox: ['.min($f3->_x/2, $f3->_x1/2, $f3->_x2/2).', '.max($f3->_y*2, $f3->_y1*2, $f3->_y2*2).', '.max($f3->_x*2, $f3->_x1*2, $f3->_x2*2).', '.min($f3->_y/2, $f3->_y1/2, $f3->_y2/2).'], axis:true, showCopyright:false, keepaspectratio: false, showNavigation: true});
            var p1 = b.create("point", ['.$f3->_x1.', '.$f3->_y1.'], {name: "x1, y1", size:2, fillColor: "blue", strokeColor: "blue"});
            var p2 = b.create("point", ['.$f3->_x2.', '.$f3->_y2.'], {name: "x2, y2", size:2, fillColor: "blue", strokeColor: "blue"});
            var li = b.create("line",["x1, y1","x2, y2"], {strokeColor:"#00ff00", strokeWidth:2});
            var p2 = b.create("point", ['.$f3->_x.', '.$f3->_y.'], {name: "x, y", size:2, attractors: [li], attractorDistance:0.2, snatchDistance: 2});
            ';
        $blc->jsx('interpolation', $js);

        $blc->h1('Cső tömeg számítás');
        $blc->numeric('D', ['D', 'Cső külső átmérő'], 600, 'mm', '');
        $blc->numeric('t', ['t', 'Cső falvastagság'], 12, 'mm', '');
        $blc->def('d', $f3->_D - 2*$f3->_t, 'd = D- 2*t = %% [mm]', 'Belső átmérő');
        $blc->def('As', $ec->A($f3->_D) - $ec->A($f3->_d), 'A_(steel) = (D^2 pi)/4 - (d^2 pi)/4 = %% [mm^2]');
        $blc->def('Al', $ec->A($f3->_d), 'A_(liqu i d) = %% [mm^2]');
        $blc->def('gs', 78.5, 'gamma_(steel) = %% [(kN)/m^3]', '');
        $blc->numeric('gl', ['gamma_(liqu i d)', 'Folyadék fajsúly'], 10, 'kN/m3', '');
        $blc->def('qk', \H3::n3(($f3->_As/1000000)*$f3->_gs + ($f3->_Al/1000000)*$f3->_gl), 'q_k = A_(steel)*gamma_(sttel) + A_(liqu i d)*gamma_(liqu i d) = %% [(kN)/(fm)]');
    }
}
