<?php

namespace Calculation;

Class Column extends \Ecc
{

    /**
     * @var $f3 \Base
     * @var $blc \Blc
     * @var $ec \Ec\Ec
     * @throws \Exception
     */
    public function calc(object $f3, object $blc, object $ec): void
    {
        $blc->h1('Közelítő méretfelvétel');
        $blc->input('N_Ed', '`N_(Ed)`: Nyomóerő', '1000', 'kN', '');
        $ec->matList('mat', 'C35/45');
        $blc->input('b_c', 'Pillér méret egyik irányban', '40', 'cm', '');

        $blc->def('f_ck', $ec->matProp($f3->_mat, 'fck'), 'f_(c,k) = %% [MPa]');
        $blc->def('f_cd', number_format($f3->_f_ck/$f3->__Gc,2), 'f_(c,d) = %% [MPa]');
        $blc->def('A_c', number_format($f3->_N_Ed/$f3->_f_cd*10, 0), 'A_c = N_(Ed)/f_(c,d) = %% [cm^2]');
        $blc->def('a_c_min', number_format($f3->_A_c/$f3->_b_c*10, 0), 'a_(c,min) = N_(Ed)/f_(c,d) = %% [cm]');

        $blc->h1('Vasalási keresztmetszet');
        $blc->def('A_s_min', number_format(0.002*$f3->_A_c*100, 0), 'A_(s, min) = %% [mm^2]');
        $blc->def('A_s_max', number_format(0.04*$f3->_A_c*100, 0), 'A_(s, max) = %% [mm^2]');
    }
}
