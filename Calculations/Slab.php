<?php

namespace Calculation;

Class Slab extends \Ecc
{

    public function calc($f3)
    {
        $ec = \Ec::instance();
        $blc = \Blc::instance();

        $blc->h1('Kétirányban teherhordó lemez képlékeny igénybevétele');
        $blc->input('p_Ed', '`p_(Ed)`: Felületen megoszló teher', '5', 'kN/m²', '');
        $blc->input('l_x', 'Lemez szélesség x irányban', '10', 'm', '');
        $blc->input('l_y', 'Lemez szélesség x irányban', '10', 'm', '');
        
        if ($f3->_l_x/$f3->_l_y <= 2) {
            $blc->label('yes', 'Két irányban teherhordó lemez');
        } else {
            $blc->label('no', 'Nem két irányban teherhordó lemez');
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
        $blc->lst('eta', $eta, 'Töréskép');
        $blc->math('eta = '. $f3->_eta);

        $blc->region0('r0');
        $blc->def('p_y', (1-(4/3)*$f3->_eta)*$f3->_p_Ed, 'p_y = %% [(kN)/m^2]');
        $blc->def('p_x', ((4/3)*$f3->_eta)*$f3->_p_Ed, 'p_x = %% [(kN)/m^2]');
        $blc->def('p_check', $f3->_p_x + $f3->_p_y, 'p_x + p_y = %% [(kN)/m^2]');
        $blc->region1('r0');

        $blc->def('m_y', ($f3->_p_y * $f3->_l_y * $f3->_l_y)/8, 'm_y = (p_y * l_y^2)/8 = %% [(kNm)/m]', '1 m széles lemezsávra jutó nyomaték');
        $blc->def('m_x', ($f3->_p_x * $f3->_l_x * $f3->_l_x)/8, 'm_x = (p_x * l_x^2)/8 = %% [(kNm)/m]', '1 m széles lemezsávra jutó nyomaték');
        $blc->def('m_xyA', $f3->_m_y, 'm_(xyA) := %% [(kNm)/m]', 'Sarkok gátolt felemelkedéséből származó csvarónyomaték');

        if ($f3->_m_y >= $f3->_m_x) {
            $blc->label('yes', 'm_y >= m_x');
        } else {
            $blc->label('no', 'm_y < m_x');
        }
    }
}
