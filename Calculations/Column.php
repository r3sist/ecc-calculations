<?php

namespace Calculation;

Class Column extends \Ecc
{

    public function calc($f3)
    {
        \Ec::load();

        \Blc::h1('Közelítő méretfelvétel');
        \Blc::input('N_Ed', '`N_(Ed)`: Nyomóerő', '1000', 'kN', '');
        \Ec::matList('mat', 'C35/45');
        \Blc::input('b_c', 'Pillér méret egyik irányban', '40', 'cm', '');

        \Blc::def('f_ck', \Ec::matProp($f3->_mat, 'fck'), 'f_(c,k) = %% [MPa]');
        \Blc::def('f_cd', number_format($f3->_f_ck/$f3->__Gc,2), 'f_(c,d) = %% [MPa]');
        \Blc::def('A_c', number_format($f3->_N_Ed/$f3->_f_cd*10, 0), 'A_c = N_(Ed)/f_(c,d) = %% [cm^2]');
        \Blc::def('a_c_min', number_format($f3->_A_c/$f3->_b_c*10, 0), 'a_(c,min) = N_(Ed)/f_(c,d) = %% [cm]');

        \Blc::h1('Vasalási keresztmetszet');
        \Blc::def('A_s_min', number_format(0.002*$f3->_A_c*100, 0), 'A_(s, min) = %% [mm^2]');
        \Blc::def('A_s_max', number_format(0.04*$f3->_A_c*100, 0), 'A_(s, max) = %% [mm^2]');
    }
}
