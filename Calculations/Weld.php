<?php declare(strict_types=1);
// Welded joint analysis according to Eurocodes - Calculation class for ECC framework
// (c) Bence VÁNKOS | https://structure.hu | https://github.com/r3sist/ecc-calculations

namespace Calculation;

use \Base;
use \Ecc\Blc;
use \Ec\Ec;
use \H3;

Class Weld
{
    public Base $f3;
    private Blc $blc;
    private Ec $ec;

    public function __construct(Base $f3, Blc $blc, Ec $ec)
    {
        $this->f3 = $f3;
        $this->blc = $blc;
        $this->ec = $ec;
    }

    // DEFINES: w, l, bw, fy, fu, FwEd, FwRd, FwRdS
    public function moduleWeld(float $length, float $a, float $force, string $steelMaterialName = 'S235', float $tPlate = 10, bool $weldOnBothSide = false): void
    {
        $this->blc->region0('r0', 'Varrat számítások');
            $this->blc->def('w', (int)$weldOnBothSide + 1, 'w_(sarok) = %%');
            $this->blc->def('l', $length - 2 * $a, 'l = L - 2*a = %% [mm]', 'Figyelembe vett varrathossz');
            $this->blc->def('bw', $this->ec->matProp($steelMaterialName, 'betaw'), 'beta_w = %%', 'Hegesztési tényező');
            $this->blc->def('fy', $this->ec->fy($steelMaterialName, $tPlate), 'f_y=%% [N/(mm^2)]', 'Folyáshatár');
            $this->blc->def('fu', $this->ec->fu($steelMaterialName, $tPlate), 'f_u=%% [N/(mm^2)]', 'Szakítószilárdság');
            $this->blc->math('F_(Ed) = '.$force.'[kN]');
            $this->blc->def('FwEd', $force / $this->f3->_l * 1000, 'F_(w,Ed) = F_(Ed)/l = %% [(kN)/m]', 'Fajlagos igénybevétel');
        $this->blc->region1();

        if ($this->f3->_l <= 30) {
            $this->blc->danger('Varrathossz rövidebb $30 [mm]$-nél! Szabvány szerint nem figyelembe vehető.');
        }

        if ($this->f3->_l <= 6 * $a) {
            $this->blc->danger('Varrathossz rövidebb $6*a = ' . 6 * $a . '[mm]$-nél! Szabvány szerint nem figyelembe vehető.');
        }

        if (150 * $a < $this->f3->_l) {
            $this->blc->danger('$l >= 150*a = ' . 150 * $a . ' [mm]$, ezért indokolt a varrat teherbírásának csökkentése, nem zártszelvények esetén.');
        }

        $this->blc->def('FwRd', H3::n2(($this->f3->_fu * $a) / (sqrt(3) * $this->f3->_bw * $this->f3->__GM2) * ($this->f3->_w)), 'F_(w,Rd) = (f_u*a)/(sqrt(3)*beta_w*gamma_(M2))*w_(sarok)= %% [(kN)/m]', 'Fajlagos teherbírás');
        $this->blc->def('FwRdS', H3::n2(($this->f3->_FwRd * $this->f3->_l / 1000)), 'F_(w,Rd,sum) = F_(w,Rd)*l = %% [kN]', 'Varratkép teljes teherbírása');
        $this->blc->label($force / $this->f3->_FwRdS, 'Kihasználtság');
        $this->blc->txt('', '$(F = '.$force.'[kN])/F_(w,Rd,sum)$');
    }

    public function calc(Base $f3, Blc $blc, Ec $ec): void
    {
        $blc->numeric('a', ['a', 'Varrat gyökméret'], 4, 'mm', '');
        $blc->numeric('L', ['L', 'Varrat egyoldali bruttó hossz'], 100, 'mm', 'Pl. lemezszélesség');
        $ec->matList();
        $blc->numeric('t', ['t', 'Lemezvastagság'], 10, 'mm');
        $blc->numeric('F', ['F', 'Erő'], 10, 'kN');
        $blc->boo('w', ['w', 'Kétoldali sarokvarrat'], false);

        $this->moduleWeld($f3->_L, $f3->_a, $f3->_F, $f3->_mat, $f3->_t, (bool) $f3->_w);

        $blc->hr();
        $blc->img('https://structure.hu/ecc/weld0.jpg', 'CÉH Tekla hegesztési utasítás');
        $blc->hr();

        $blc->h1('Feszültség alapú általános eljárás');
        $blc->input('sigma_T', ['sigma_(_|_)', 'Merőleges normálfeszültség'], 100, 'N/mm2');
        $blc->input('tau_T', ['tau_(_|_)', 'Merőleges nyírófeszültség'], 100, 'N/mm2');
        $blc->input('tau_ii', ['tau_(||)', 'Párhuzamos nyírófeszültség'], 100, 'N/mm2');
        $blc->txt('Megfelelőségi feltételek $[N/(mm^2)]$:');
        $a = sqrt(($f3->_sigma_T ** 2) + 3*(($f3->_tau_ii ** 2) + ($f3->_tau_T ** 2)));
        $b = ($f3->_fu/($f3->_bw*$f3->__GM2));
        $blc->math("sqrt(sigma_(_|_)^2 + 3*(tau_(||)^2 + tau_(_|_)^2)) =  $a %%% < %%% f_u/(beta_w*gamma_(M2)) = $b");
        $blc->label($a/$b, 'kihasználtság');
    }
}
