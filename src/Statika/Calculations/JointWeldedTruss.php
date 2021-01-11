<?php declare(strict_types = 1);
// Analysis of joints of welded steel truss according to Eurocodes - Calculation class for ECC framework
// (c) Bence VÁNKOS | https://structure.hu | https://github.com/r3sist/ecc-calculations

namespace Calculation;

use \Base;
use \Ecc\Blc;
use \Ec\Ec;
use \H3;

Class JointWeldedTruss
{
    protected Weld $weld;

    public function __construct(Weld $weld)
    {
        $this->weld = $weld;
    }

    public function calc(Base $f3, Blc $blc, Ec $ec): void
    {
        $blc->note('[Acélszerkezetek - 2. Speciális eljárások: 3. fejezet alapján] [EC3-1-8 7.]');

        $types = [
            'T' => 'T',
            'K' => 'K',
            'KT' => 'KT',
            'X' => 'X',
            'N' => 'N',
            'Y' => 'Y',
        ];
        $blc->lst('type', $types, ['', 'Csomópont típus'], 'T');
    }
}
