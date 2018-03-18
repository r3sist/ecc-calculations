<?php

namespace Calculation;

Class Weld extends \Ecc
{

    public function calc($f3)
    {
        \Ec::load();

        \Blc::input('a', 'Varrat gyökméret', 4, 'mm');
        \Blc::input('L', 'Varrat összes hossz', 100, 'mm');
        \Ec::matList();
        \Blc::input('t', 'Lemezvastagság', 10, 'mm');
        \Blc::input('F', '`F_(Ed)`: Erő', 10, 'kN');
        \Blc::boo('w', 'Kétoldali sarokvarrat', 0);
        \Blc::def('w', $f3->_w + 1, 'w_(sarok) = %%');

        \Blc::region0('r0');
        \Blc::math('mat = '.$f3->_mat);
        \Blc::def('l',$f3->_L - 2*$f3->_a,'l = L - 2*a = %%[mm]', 'Figyelembe vett varrathossz');
        \Blc::def('bw',\Ec::matProp($f3->_mat, 'betaw'),'beta_w=%%', 'Hegesztési tényező');
        \Blc::def('fy',\Ec::fy($f3->_mat, $f3->_t),'f_y=%%[MPa]', 'Folyáshatár');
        \Blc::def('fu',\Ec::fu($f3->_mat, $f3->_t),'f_u=%%[MPa]', 'Szakítószilárdság');
        \Blc::def('FwEd',$f3->_F / $f3->_l *1000,'F_(w.Ed) = F_(Ed)/l = %%[(kN)/m]', 'Fajlagos igénybevétel');
        \Blc::region1('r0');

        \Blc::success0('s0');
        \Blc::def('FwRd',($f3->_fu*$f3->_a)/(sqrt(3)*$f3->_bw*$f3->__GM2)*($f3->_w),'F_(w.Rd) (f_u*a)/(sqrt(3)*beta_w*gamma_(M2))*w_(sarok)= %%[(kN)/m]', 'Fajlagos teherbírás');
        \Blc::def('FwRdS',($f3->_FwRd*$f3->_l/1000),'F_(w.Rd.sum) = %%[kN]', 'Varratkép teljes teherbírása:');
        \Blc::label($f3->_F/$f3->_FwRdS, 'Kihasználtság');
        \Blc::success1('s0');
    }
}
