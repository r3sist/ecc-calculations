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
        $blc->math('mat = ' . $f3->_mat);
        $blc->def('l', $f3->_L - 2 * $f3->_a, 'l = L - 2*a = %% [mm]', 'Figyelembe vett varrathossz');
        $blc->def('bw', $ec->matProp($f3->_mat, 'betaw'), 'beta_w = %%', 'Hegesztési tényező');
        $blc->def('fy', $ec->fy($f3->_mat, $f3->_t), 'f_y=%% [MPa]', 'Folyáshatár');
        $blc->def('fu', $ec->fu($f3->_mat, $f3->_t), 'f_u=%% [MPa]', 'Szakítószilárdság');
        $blc->def('FwEd', $f3->_F / $f3->_l * 1000, 'F_(w,Ed) = F_(Ed)/l = %% [(kN)/m]', 'Fajlagos igénybevétel');
        $blc->region1('r0');

        if ($f3->_l <= 30) {
            $blc->danger('Varrathossz rövidebb 30 mm-nél! Szabvány szerint nem figyelembe vehető.');
        }

        if ($f3->_l <= 6 * $f3->_a) {
            $blc->danger('Varrathossz rövidebb \`6*a = ' . 6 * $f3->_a . '[mm]\`-nél! Szabvány szerint nem figyelembe vehető.');
        }

        if (150 * $f3->_a < $f3->_l) {
            $blc->danger('\`l >= 150*a = ' . 150 * $f3->_a . ' [mm]\`, ezért indokolt a varrat teherbírásának csökkentése, nem zártszelvények esetén.');
        }

        $blc->success0('s0');
        $blc->def('FwRd', ($f3->_fu * $f3->_a) / (sqrt(3) * $f3->_bw * $f3->__GM2) * ($f3->_w), 'F_(w,Rd) = (f_u*a)/(sqrt(3)*beta_w*gamma_(M2))*w_(sarok)= %% [(kN)/m]', 'Fajlagos teherbírás');
        $blc->def('FwRdS', ($f3->_FwRd * $f3->_l / 1000), 'F_(w,Rd,sum) = F_(w,Rd)*l = %% [kN]', 'Varratkép teljes teherbírása:');
        $blc->label($f3->_F / $f3->_FwRdS, 'Kihasználtság');
        $blc->success1('s0');

        $blc->hr();
        $blc->img('https://structure.hu/ecc/weld0.jpg', 'CÉH Tekla hegesztési utasítás');
        $blc->hr();

        $blc->h1('Feszültség alapú általános eljárás');
        $blc->input('sigma_T', '`sigma_⊥`: Merőleges normálfeszültség', 100, 'MPa');
        $blc->input('tau_T', '`tao_⊥`: Merőleges nyírófeszültség', 100, 'MPa');
        $blc->input('tau_ii', '`tao_(||)`: Párhuzamos nyírófeszültség', 100, 'MPa');
        $blc->txt('Megfelelőségi feltételek [MPa]:');
        $a = sqrt(pow($f3->_sigma_T, 2) + 3*(pow($f3->_tau_ii, 2) + pow($f3->_tau_T, 2)));
        $b = ($f3->_fu/($f3->_bw*$f3->__GM2));
        $blc->math("sqrt(sigma_⊥^2 + 3*(tau_(||)^2 + tau_⊥^2)) =  $a %%% < %%% f_u/(beta_w*gamma_(M2)) = $b");
        $blc->label($a/$b, 'Kihasználtság');
    }
}
