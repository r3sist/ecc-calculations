<?php

namespace Calculation;

Class Plate extends \Ecc
{

    public function calc($f3)
    {
        \Ec::load();

        \Blc::h1('Kétirányban teherhordó lemez képlékeny igénybevétele');
        \Blc::input('p_Ed', '`p_(Ed)`: Felületen megoszló teher', '5', 'kN/m²', '');
        \Blc::input('l_x', 'Lemez szélesség x irányban', '10', 'm', '');
        \Blc::input('l_y', 'Lemez szélesség x irányban', '10', 'm', '');
        
        if ($f3->_l_x/$f3->_l_y <= 2) {
            \Blc::label('yes', 'Két irányban teherhordó lemez');
        } else {
            \Blc::label('no', 'Nem két irányban teherhordó lemez');
        }

        $etaOpt = 0.5*($f3->_l_y/$f3->_l_x)*($f3->_l_y/$f3->_l_x);
        $eta = array(
            'η optimális minimális vasmennyiség: '. $etaOpt => $etaOpt,
            'η 45°'=> 0.5*($f3->_l_y/$f3->_l_x),
            '0.10' => 0.10,
            '0.15' => 0.15,
            '0.20' => 0.20,
            '0.25' => 0.25,
            '0.30' => 0.30,
            '0.35' => 0.35,
            '0.40' => 0.40,
            '0.45' => 0.45,
            '0.50' => 0.5,
        );
        $f3->_eta = 0.1;
        \Blc::lst('eta', $eta, 'Töréskép');
        \Blc::math('eta = '. $f3->_eta);

        \Blc::region0('r0');
        \Blc::def('p_y', (1-(4/3)*$f3->_eta)*$f3->_p_Ed, 'p_y = %% [(kN)/m^2]');
        \Blc::def('p_x', ((4/3)*$f3->_eta)*$f3->_p_Ed, 'p_x = %% [(kN)/m^2]');
        \Blc::def('p_check', $f3->_p_x + $f3->_p_y, 'p_x + p_y = %% [(kN)/m^2]');
        \Blc::region1('r0');

        \Blc::def('m_y', ($f3->_p_y * $f3->_l_y * $f3->_l_y)/8, 'm_y = (p_y * l_y^2)/8 = %% [(kNm)/m]', '1 m széles lemezsávra jutó nyomaték');
        \Blc::def('m_x', ($f3->_p_x * $f3->_l_x * $f3->_l_x)/8, 'm_x = (p_x * l_x^2)/8 = %% [(kNm)/m]', '1 m széles lemezsávra jutó nyomaték');
        \Blc::def('m_xyA', $f3->_m_y, 'm_(xyA) := %% [(kNm)/m]', 'Sarkok gátolt felemelkedéséből származó csvarónyomaték');

        if ($f3->_m_y >= $f3->_m_x) {
            \Blc::label('yes', 'm_y >= m_x');
        } else {
            \Blc::label('no', 'm_y < m_x');
        }
    }
}
