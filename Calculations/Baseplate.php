<?php

namespace Calculation;

Class Baseplate extends \Ecc
{

    public function calc($f3)
    {
        $ec = \Ec::instance();
        $blc = \Blc::instance();

        $blc->h1('Bázislemez ellenőrzése húzásra');

        $blc->input('t_b', 'Bázislemez vastagság','16', 'mm','');
        $blc->input('b_a', 'Két lehorgonyzás közti távolság','280', 'mm','');
        $blc->input('N', '`N_(Ed)`: Húzóerő','20', 'kN','');
        $ec->matList('stMat','S235');
        $blc->hr();

        $blc->def('M',$f3->_N*0.25*$f3->_b_a/1000, 'M_(Ed) = N_(Ed)*0.25*b_a = %% [kNm]','Nyomaték a lemezben');
        $blc->def('W',($f3->_b_a*$f3->_t_b*$f3->_t_b)/6, 'W = (b_a*(t_b)^2)/6 = %% [mm^3]', 'Keresztmetszeti modulus');
        $blc->def('sigma',($f3->_M/$f3->_W)*1000000, 'sigma_(Ed) = M_(Ed)/W = %% [MPa]', 'Lemez feszültség');
        $blc->def('f_y',$ec->fy($f3->_stMat,$f3->_t_b), 'f_y = %% [MPa]', 'Lemez folyáshatár');
        $blc->label($f3->_sigma/$f3->_f_y,'Lemez kihasználtság');

        $blc->h1('Lehorgonyzás ellenőrzés');
        $blc->md('`TODO`');

        $blc->h1('Szelemen összekötő szerelvény ellenőrzés');
        $blc->md('`TODO`');

        $blc->h1('Csatlakozó pengelemez ellenőrzés');
        $blc->md('`TODO`');

        $blc->h1('Csatlakozó pengelemez varrathossz ellenőrzés');
        $blc->md('`TODO`');

        $blc->h1('Egyszerűsített csap számítás');
        $blc->md('`TODO`');

        $blc->h1('Neoprén saru felület méretezés');
        $blc->md('`TODO`');

    }
}
