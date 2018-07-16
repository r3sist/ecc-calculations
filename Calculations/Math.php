<?php

namespace Calculation;

Class Math extends \Ecc
{

    public function calc($f3)
    {
        $ec = \Ec::instance();
        $blc = \Blc::instance();

        $blc->h1('Lejtés');
        $blc->input('slope', '``Lejtés', '3', '% vagy °', '');
        $slope_deg = rad2deg(atan($f3->_slope/100));
        $slope_per = tan(deg2rad($f3->_slope))*100;
        $blc->def('slope_deg', number_format($slope_deg, 2), $f3->_slope.'% = %% °', '');
        $blc->def('slope_deg', number_format($slope_per, 2), $f3->_slope.'° = %% %', '');

        $blc->h1('Hőmérséklet rudakon');
        $blc->def('alpha_T_st', number_format(0.000012, 6), 'alpha_(T,steel) = %% 1/K', '');
        $blc->input('L', 'Rúdhossz', '10', 'm', '');
        $blc->input('DeltaT', 'Hőmérséklet változás', '40', '°', '');
        $blc->def('DeltaL', number_format($f3->_alpha_T_st*($f3->_L*1000)*$f3->_DeltaT, 2), 'DeltaL = %% mm', '');

        $blc->h1('Lineáris interpoláció');
        $blc->input('x1', '', '1', '', '');
        $blc->input('y1', '', '5', '', '');
        $blc->input('x2', '', '2', '', '');
        $blc->input('y2', '', '20', '', '');
        $blc->info0('i0');
        $blc->input('x', '', '3', '', '');
        $blc->def('y', (($f3->_x - $f3->_x1)*($f3->_y2 - $f3->_y1)/($f3->_x2 - $f3->_x1)) + $f3->_y1, 'y = %%');
        $blc->info1('i0');
    }
}
