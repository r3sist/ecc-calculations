<?php

namespace Calculation;

Class Math extends \Ecc
{

    public function calc($f3)
    {
        \Ec::load();

        \Blc::h1('Lejtés');
        \Blc::input('slope', '``Lejtés', '3', '% vagy °', '');
        $slope_deg = rad2deg(atan($f3->_slope/100));
        $slope_per = tan(deg2rad($f3->_slope))*100;
        \Blc::def('slope_deg', number_format($slope_deg, 2), $f3->_slope.'% = %% °', '');
        \Blc::def('slope_deg', number_format($slope_per, 2), $f3->_slope.'° = %% %', '');

        \Blc::h1('Hőmérséklet rudakon');
        \Blc::def('alpha_T_st', 0.000012, 'alpha_(T,steel) = %% °', '');
    }
}
