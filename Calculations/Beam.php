<?php

namespace Calculation;

Class Beam extends \Ecc
{

    public function calc($f3)
    {
        $ec = \Ec::instance();
        $blc = \Blc::instance();

        $blc->h1('Kétirányban teherhordó lemez képlékeny igénybevétele');
        $blc->input('p_Ed', '`p_(Ed)`: Felületen megoszló teher', '5', 'kN/m²', '');
        $blc->input('l_x', 'Lemez szélesség x irányban', '10', 'm', '');
        $blc->input('l_y', 'Lemez szélesség x irányban', '10', 'm', '');
        
        
    }
}
