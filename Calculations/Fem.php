<?php declare(strict_types = 1);
// FEM - Calculation class for ECC framework
// (c) Bence VÁNKOS | https://structure.hu | https://github.com/r3sist/ecc-calculations

namespace Calculation;

use \Base;
use \Ecc\Blc;
use \Ec\Ec;
use \H3;
use \resist\SVG\SVG;

Class Fem
{
    public function calc(Base $f3, Blc $blc, Ec $ec): void
    {
        $blc->txt('ok');
    }
}
