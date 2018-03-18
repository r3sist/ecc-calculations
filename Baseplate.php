<?php

namespace Calculation;

Class Baseplate extends \Ecc
{

    public function calc($f3)
    {
        \Ec::load();

        \Blc::input('t_b', 'Bázislemez vastagság','16', 'mm','');
        \Blc::input('b_a', 'Két lehorgonyzás közti távolság','280', 'mm','');
        \Blc::input('N', '`N_(Ed)`: Húzóerő','20', 'kN','');
        \Ec::matList('stMat','S235');
        \Blc::hr();

        \Blc::def('M',$f3->_N*0.25*$f3->_b_a/1000, 'M_(Ed) = N_(Ed)*0.25*b_a = %% [kNm]','Nyomaték a lemezben');
        \Blc::def('W',($f3->_b_a*$f3->_t_b*$f3->_t_b)/6, 'W = (b_a*(t_b)^2)/6 = %% [mm^3]', 'Keresztmetszeti modulus');
        \Blc::def('sigma',($f3->_M/$f3->_W)*1000000, 'sigma_(Ed) = M_(Ed)/W = %% [MPa]', 'Lemez feszültség');
        \Blc::def('f_y',\Ec::fy($f3->_stMat,$f3->_t_b), 'f_y = %% [MPa]', 'Lemez folyáshatár');
        \Blc::label($f3->_sigma/$f3->_f_y,'Lemez kihasználtság');
    }
}
