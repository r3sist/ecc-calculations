<?php

namespace Calculation;

Class Weld extends \Ecc
{

    /**
     * Required in hive: a, L, w, t, F, mat
     * @var $f3 \Base
     * @var $blc \Blc
     * @var $ec \Ec\Ec
     * @throws \Exception
     */
    public function moduleWeldOld(object $f3, object $blc, object $ec) {

        $blc->region0('r0', 'Varrat számítások');
        $blc->def('w', $f3->_w + 1, 'w_(sarok) = %%');
        $blc->def('l', $f3->_L - 2 * $f3->_a, 'l = L - 2*a = %% [mm]', 'Figyelembe vett varrathossz');
        $blc->def('bw', $ec->matProp($f3->_mat, 'betaw'), 'beta_w = %%', 'Hegesztési tényező');
        $blc->def('fy', $ec->fy($f3->_mat, $f3->_t), 'f_y=%% [N/(mm^2)]', 'Folyáshatár');
        $blc->def('fu', $ec->fu($f3->_mat, $f3->_t), 'f_u=%% [N/(mm^2)]', 'Szakítószilárdság');
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
        $blc->def('FwRdS', \H3::n2(($f3->_FwRd * $f3->_l / 1000)), 'F_(w,Rd,sum) = F_(w,Rd)*l = %% [kN]', 'Varratkép teljes teherbírása');
        $blc->label($f3->_F / $f3->_FwRdS, 'Kihasználtság');
        $blc->txt('', '$(F = '.$f3->_F.'[kN])/F_(w,Rd,sum)$');
        $blc->success1('s0');
    }

    /**
     *
     * @var $f3 \Base
     * @var $blc \Blc
     * @var $ec \Ec\Ec
     * @throws \Exception
     */
    public function moduleWeld(int $length, int $a, float $F, string $steelMaterialName = 'S235', int $tPlate = 10, $weldOnBothSide = false) {
        $f3 = \Base::instance();
        $blc = \Blc::instance();
        $ec = \Ec\Ec::instance();

        $blc->region0('r0', 'Varrat számítások');
            $blc->def('w', \V3::numeric($weldOnBothSide) + 1, 'w_(sarok) = %%');
            $blc->def('l', $length - 2 * $a, 'l = L - 2*a = %% [mm]', 'Figyelembe vett varrathossz');
            $blc->def('bw', $ec->matProp($steelMaterialName, 'betaw'), 'beta_w = %%', 'Hegesztési tényező');
            $blc->def('fy', $ec->fy($steelMaterialName, $tPlate), 'f_y=%% [N/(mm^2)]', 'Folyáshatár');
            $blc->def('fu', $ec->fu($steelMaterialName, $tPlate), 'f_u=%% [N/(mm^2)]', 'Szakítószilárdság');
            $blc->math('F_(Ed) = '.$F.'[kN]');
            $blc->def('FwEd', $F / $f3->_l * 1000, 'F_(w,Ed) = F_(Ed)/l = %% [(kN)/m]', 'Fajlagos igénybevétel');
        $blc->region1('r0');

        if ($f3->_l <= 30) {
            $blc->danger('Varrathossz rövidebb $30 [mm]$-nél! Szabvány szerint nem figyelembe vehető.');
        }

        if ($f3->_l <= 6 * $a) {
            $blc->danger('Varrathossz rövidebb $6*a = ' . 6 * $a . '[mm]$-nél! Szabvány szerint nem figyelembe vehető.');
        }

        if (150 * $a < $f3->_l) {
            $blc->danger('$l >= 150*a = ' . 150 * $a . ' [mm]$, ezért indokolt a varrat teherbírásának csökkentése, nem zártszelvények esetén.');
        }

        $blc->def('FwRd', \H3::n2(($f3->_fu * $a) / (sqrt(3) * $f3->_bw * $f3->__GM2) * ($f3->_w)), 'F_(w,Rd) = (f_u*a)/(sqrt(3)*beta_w*gamma_(M2))*w_(sarok)= %% [(kN)/m]', 'Fajlagos teherbírás');
        $blc->def('FwRdS', \H3::n2(($f3->_FwRd * $f3->_l / 1000)), 'F_(w,Rd,sum) = F_(w,Rd)*l = %% [kN]', 'Varratkép teljes teherbírása');
        $blc->label($F / $f3->_FwRdS, 'Kihasználtság');
        $blc->txt('', '$(F = '.$F.'[kN])/F_(w,Rd,sum)$');
    }

    public function calc(object $f3, object $blc, object $ec): void
    {
        $blc->numeric('a', ['a', 'Varrat gyökméret'], 4, 'mm', '');
        $blc->numeric('L', ['L', 'Varrat egyoldali bruttó hossz'], 100, 'mm', 'Pl. lemezszélesség');
        $ec->matList();
        $blc->numeric('t', ['t', 'Lemezvastagság'], 10, 'mm');
        $blc->numeric('F', ['F', 'Erő'], 10, 'kN');
        $blc->boo('w', 'Kétoldali sarokvarrat', 0);

        $this->moduleWeldOld($f3, $blc, $ec);

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
