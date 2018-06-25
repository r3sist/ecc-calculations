<?php

namespace Calculation;

Class Weld extends \Ecc
{

    public function calc($f3)
    {
        $ec = \Ec::instance();
        $blc = \Blc::instance();

        $blc->input('a', 'Varrat gyökméret', 4, 'mm');
        $blc->input('L', 'Varrat összes hossz', 100, 'mm');
        $ec->matList();
        $blc->input('t', 'Lemezvastagság', 10, 'mm');
        $blc->input('F', '`F_(Ed)`: Erő', 10, 'kN');
        $blc->boo('w', 'Kétoldali sarokvarrat', 0);
        $blc->def('w', $f3->_w + 1, 'w_(sarok) = %%');

        $blc->region0('r0');
        $blc->math('mat = '.$f3->_mat);
        $blc->def('l',$f3->_L - 2*$f3->_a,'l = L - 2*a = %%[mm]', 'Figyelembe vett varrathossz');
        $blc->def('bw',$ec->matProp($f3->_mat, 'betaw'),'beta_w=%%', 'Hegesztési tényező');
        $blc->def('fy',$ec->fy($f3->_mat, $f3->_t),'f_y=%%[MPa]', 'Folyáshatár');
        $blc->def('fu',$ec->fu($f3->_mat, $f3->_t),'f_u=%%[MPa]', 'Szakítószilárdság');
        $blc->def('FwEd',$f3->_F / $f3->_l *1000,'F_(w.Ed) = F_(Ed)/l = %%[(kN)/m]', 'Fajlagos igénybevétel');
        $blc->region1('r0');

        $blc->success0('s0');
        $blc->def('FwRd',($f3->_fu*$f3->_a)/(sqrt(3)*$f3->_bw*$f3->__GM2)*($f3->_w),'F_(w.Rd) (f_u*a)/(sqrt(3)*beta_w*gamma_(M2))*w_(sarok)= %%[(kN)/m]', 'Fajlagos teherbírás');
        $blc->def('FwRdS',($f3->_FwRd*$f3->_l/1000),'F_(w.Rd.sum) = %%[kN]', 'Varratkép teljes teherbírása:');
        $blc->label($f3->_F/$f3->_FwRdS, 'Kihasználtság');
        $blc->success1('s0');
    }
}
