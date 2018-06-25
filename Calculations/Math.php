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
        $blc->def('alpha_T_st', 0.000012, 'alpha_(T,steel) = %% 1/K', '');
        $blc->input('L', 'Rúdhossz', '10', 'm', '');
        $blc->input('DeltaT', 'Hőmérséklet változás', '40', '°', '');
        $blc->def('DeltaL', $f3->_alpha_T_st*$f3->_L*$f3->_deltaT*1000, 'DeltaL = %% mm', '');
    }
}
