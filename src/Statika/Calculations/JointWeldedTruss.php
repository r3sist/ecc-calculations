<?php declare(strict_types = 1);
/**
 * Analysis of joints of welded steel truss according to Eurocodes - Calculation class for Statika framework
 * (c) Bence VÁNKOS | https://structure.hu | https://github.com/r3sist/ecc-calculations
 */

namespace Statika\Calculations;

use Statika\Ec;
use Statika\EurocodeInterface;

Class JointWeldedTruss
{
    protected Weld $weld;

    public function __construct(Weld $weld)
    {
        $this->weld = $weld;
    }

    /**
     * @param Ec $ec
     */
    public function calc(EurocodeInterface $ec): void
    {
        $ec->note('[Acélszerkezetek - 2. Speciális eljárások: 3. fejezet alapján] [EC3-1-8 7.]');

        $types = [
            'T' => 'T',
            'K' => 'K',
            'KT' => 'KT',
            'X' => 'X',
            'N' => 'N',
            'Y' => 'Y',
        ];
        $ec->lst('type', $types, ['', 'Csomópont típus'], 'T');
    }
}
