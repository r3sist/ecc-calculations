<?php declare(strict_types=1);
/**
 * Welded joint analysis according to Eurocodes - Calculation class for Statika framework
 * (c) Bence VÁNKOS | https://structure.hu | https://github.com/r3sist/ecc-calculations
 */

namespace Statika\Calculations;

use \Base;
use \Statika\Blc;
use \H3;
use Statika\Ec;
use Statika\EurocodeInterface;

Class Weld
{
    private EurocodeInterface $ec;

    /**
     * Weld constructor.
     * @param Ec $ec
     */
    public function __construct(EurocodeInterface $ec)
    {
        $this->ec = $ec;
    }

    // DEFINES: w, l, bw, fy, fu, FwEd, FwRd, FwRdS
    public function moduleWeld(float $length, float $a, float $force, string $steelMaterialName = 'S235', float $tPlate = 10, bool $weldOnBothSide = false): void
    {
        $this->ec->region0('r0', 'Varrat számítások');
            $this->ec->def('ws', ($weldOnBothSide?2:1), 'w_(sarok) = %%');
            $this->ec->def('l', $length - 2 * $a, 'l = L - 2*a = %% [mm]', 'Figyelembe vett varrathossz');
            $this->ec->def('bw', $this->ec->getMaterial($steelMaterialName)->betaw, 'beta_w = %%', 'Hegesztési tényező');
            $this->ec->def('fy', $this->ec->fy($steelMaterialName, $tPlate), 'f_y=%% [N/(mm^2)]', 'Folyáshatár');
            $this->ec->def('fu', $this->ec->fu($steelMaterialName, $tPlate), 'f_u=%% [N/(mm^2)]', 'Szakítószilárdság');
            $this->ec->math('F_(Ed) = '.$force.'[kN]');
            $this->ec->def('FwEd', $force / $this->ec->l * 1000, 'F_(w,Ed) = F_(Ed)/l = %% [(kN)/m]', 'Fajlagos igénybevétel');
        $this->ec->region1();

        if ($this->ec->l <= 30) {
            $this->ec->danger('Varrathossz rövidebb $30 [mm]$-nél! Szabvány szerint nem figyelembe vehető.');
        }

        if ($this->ec->l <= 6 * $a) {
            $this->ec->danger('Varrathossz rövidebb $6*a = ' . 6 * $a . '[mm]$-nél! Szabvány szerint nem figyelembe vehető.');
        }

        if (150 * $a < $this->ec->l) {
            $this->ec->danger('$l >= 150*a = ' . 150 * $a . ' [mm]$, ezért indokolt a varrat teherbírásának csökkentése, nem zártszelvények esetén.');
        }

        $this->ec->def('FwRd', H3::n2(($this->ec->fu * $a) / (sqrt(3) * $this->ec->bw * $this->ec::GM2) * ($this->ec->ws)), 'F_(w,Rd) = (f_u*a)/(sqrt(3)*beta_w*gamma_(M2))*w_(sarok)= %% [(kN)/m]', 'Fajlagos teherbírás');
        $this->ec->def('FwRdS', H3::n2(($this->ec->FwRd * $this->ec->l / 1000)), 'F_(w,Rd,sum) = F_(w,Rd)*l = %% [kN]', 'Varratkép teljes teherbírása');
        $this->ec->label($force / $this->ec->FwRdS, 'Kihasználtság');
        $this->ec->txt('', '$(F = '.$force.'[kN])/F_(w,Rd,sum)$');
    }

    /**
     * @param Ec $ec
     */
    public function calc(EurocodeInterface $ec): void
    {
        $ec->numeric('a', ['a', 'Varrat gyökméret'], 4, 'mm', '');
        $ec->numeric('L', ['L', 'Varrat egyoldali bruttó hossz'], 100, 'mm', 'Pl. lemezszélesség');
//        $ec->matList('mat', 'S235', [ '', 'Anyagminőség'], 'steel');
        $ec->structuralSteelMaterialListBlock();
        $ec->numeric('t', ['t', 'Lemezvastagság'], 10, 'mm');
        $ec->numeric('F', ['F', 'Erő'], 10, 'kN');
        $ec->boo('w', ['w', 'Kétoldali sarokvarrat'], false);
//        $ec->numeric('w', ['w', 'Kétoldali sarokvarrat'], 1);

        $this->moduleWeld($ec->L, $ec->a, $ec->F, $ec->steelMaterialName, $ec->t, (bool)$ec->w);

        $ec->hr();
        $ec->img('https://structure.hu/ecc/weld0.jpg', 'CÉH Tekla hegesztési utasítás');
        $ec->hr();

        $ec->h1('Feszültség alapú általános eljárás');
        $ec->input('sigma_T', ['sigma_(_|_)', 'Merőleges normálfeszültség'], 100, 'N/mm2');
        $ec->input('tau_T', ['tau_(_|_)', 'Merőleges nyírófeszültség'], 100, 'N/mm2');
        $ec->input('tau_ii', ['tau_(||)', 'Párhuzamos nyírófeszültség'], 100, 'N/mm2');
        $ec->txt('Megfelelőségi feltételek:');
        $a = sqrt(($ec->sigma_T ** 2) + 3*(($ec->tau_ii ** 2) + ($ec->tau_T ** 2)));
        $b = ($ec->fu/($ec->bw*$ec::GM2));
        $ec->math('sqrt(sigma_(_|_)^2 + 3*(tau_(||)^2 + tau_(_|_)^2)) =  '.\H3::n2($a).' %%% :< %%% f_u/(beta_w*gamma_(M2)) = '.\H3::n2($b).' [N/(mm^2)]');
        $ec->label($a/$b, 'kihasználtság');
    }
}
