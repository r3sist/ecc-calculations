<?php

namespace Calculation;

Class Weld extends \Ecc
{

    /**
     * @var $f3 \Base
     * @var $blc \Blc
     * @var $ec \Ec\Ec
     * @throws \Exception
     */
    public function moduleWeld(object $f3, object $blc, object $ec) {
        /*
        // Module usage:
        $weld = new \Calculation\Weld();
        $blc->numeric('a', ['a', 'Varrat gyökméret'], 6, 'mm');
        $blc->numeric('L', ['L', 'Lemez szélesség'], 300, 'mm');
        $f3->_w = 1;
        $f3->_t = max($f3->_t1, $f3->_t2);
        $f3->_F = $f3->_REd;
        $f3->_mat = $f3->_stMat;
        $weld->simpleWeld($f3, $blc, $ec);
        $blc->note('[Varrat modul](https://structure.hu/calc/Weld) betöltésével számítva.');
        */

        $blc->region0('r0', 'Varrat számítások');
        $blc->def('w', $f3->_w + 1, 'w_(sarok) = %%');
        $blc->def('l', $f3->_L - 2 * $f3->_a, 'l = L - 2*a = %% [mm]', 'Figyelembe vett varrathossz');
        $blc->def('bw', $ec->matProp($f3->_mat, 'betaw'), 'beta_w = %%', 'Hegesztési tényező');
        $blc->def('fy', $ec->fy($f3->_mat, $f3->_t), 'f_y=%% [MPa]', 'Folyáshatár');
        $blc->def('fu', $ec->fu($f3->_mat, $f3->_t), 'f_u=%% [MPa]', 'Szakítószilárdság');
        $blc->math('F_(Ed) = '.$f3->_F.'[kN]');
        $blc->def('FwEd', $f3->_F / $f3->_l * 1000, 'F_(w,Ed) = F_(Ed)/l = %% [(kN)/m]', 'Fajlagos igénybevétel');
        $blc->region1('r0');

        if ($f3->_l <= 30) {
            $blc->danger('Varrathossz rövidebb $30 [mm]$-nél! Szabvány szerint nem figyelembe vehető.');
        }

        if ($f3->_l <= 6 * $f3->_a) {
            $blc->danger('Varrathossz rövidebb $6*a = ' . 6 * $f3->_a . '[mm]$-nél! Szabvány szerint nem figyelembe vehető.');
        }

        if (150 * $f3->_a < $f3->_l) {
            $blc->danger('$l >= 150*a = ' . 150 * $f3->_a . ' [mm]$, ezért indokolt a varrat teherbírásának csökkentése, nem zártszelvények esetén.');
        }

        $blc->success0('s0');
        $blc->def('FwRd', \H3::n2(($f3->_fu * $f3->_a) / (sqrt(3) * $f3->_bw * $f3->__GM2) * ($f3->_w)), 'F_(w,Rd) = (f_u*a)/(sqrt(3)*beta_w*gamma_(M2))*w_(sarok)= %% [(kN)/m]', 'Fajlagos teherbírás');
        $blc->def('FwRdS', \H3::n2(($f3->_FwRd * $f3->_l / 1000)), 'F_(w,Rd,sum) = F_(w,Rd)*l = %% [kN]', 'Varratkép teljes teherbírása:');
        $blc->label($f3->_F / $f3->_FwRdS, 'Kihasználtság');
        $blc->txt('', '$(F = '.$f3->_F.'[kN])/F_(w,Rd,sum)$');
        $blc->success1('s0');
    }

    public function calc(object $f3, object $blc, object $ec): void
    {
        $blc->numeric('a', ['a', 'Varrat gyökméret'], 4, 'mm', '');
        $blc->numeric('L', ['L', 'Varrat egyoldali bruttó hossz'], 100, 'mm', 'Pl. lemezszélesség');
        $ec->matList();
        $blc->numeric('t', ['t', 'Lemezvastagság'], 10, 'mm');
        $blc->numeric('F', ['F', 'Erő'], 10, 'kN');
        $blc->boo('w', 'Kétoldali sarokvarrat', 0);

        $this->moduleWeld($f3, $blc, $ec);

        $blc->hr();
        $blc->img('https://structure.hu/ecc/weld0.jpg', 'CÉH Tekla hegesztési utasítás');
        $blc->hr();

        $blc->h1('Feszültség alapú általános eljárás');
        $blc->input('sigma_T', ['sigma_(_|_)', 'Merőleges normálfeszültség'], 100, 'N/mm2');
        $blc->input('tau_T', ['tau_(_|_)', 'Merőleges nyírófeszültség'], 100, 'N/mm2');
        $blc->input('tau_ii', ['tau_(||)', 'Párhuzamos nyírófeszültség'], 100, 'N/mm2');
        $blc->txt('Megfelelőségi feltételek $[N/(mm^2)]$:');
        $a = sqrt(pow($f3->_sigma_T, 2) + 3*(pow($f3->_tau_ii, 2) + pow($f3->_tau_T, 2)));
        $b = ($f3->_fu/($f3->_bw*$f3->__GM2));
        $blc->math("sqrt(sigma_(_|_)^2 + 3*(tau_(||)^2 + tau_(_|_)^2)) =  $a %%% < %%% f_u/(beta_w*gamma_(M2)) = $b");
        $blc->label($a/$b, 'kihasználtság');
    }
}
