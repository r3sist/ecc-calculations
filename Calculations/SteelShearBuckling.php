<?php

namespace Calculation;

/**
 * Calculation of buckling of plates under shear according to Eurocodes - Calculation class for ECC framework
 *
 * (c) Bence VÁNKOS
 * https:// structure.hu
 */

Class SteelShearBuckling
{
    protected $f3;
    protected $blc;
    protected $ec;

    public function __construct(\Base $f3, \Ecc\Blc $blc, \Ec\Ec $ec)
    {
        $this->f3 = $f3;
        $this->blc = $blc;
        $this->ec = $ec;
    }

    public function calc(\Base $f3, \Ecc\Blc $blc, \Ec\Ec $ec): void
    {
        
    }
}
