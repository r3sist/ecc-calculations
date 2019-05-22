<?php

namespace Calculation;

Class PileHead extends \Ecc
{

    /**
     * @var $f3 \Base
     * @var $blc \Blc
     * @var $ec \Ec\Ec
     * @throws \Exception
     */
    public function calc(object $f3, object $blc, object $ec): void
    {
        $blc->note('KG alapján');

        $ec->matList('cMat', 'C30/37', 'Beton anyagminőség');
        $ec->saveMaterialData($f3->_cMat, 'c');
        $ec->matList('rMat', 'B500', 'Betonvas anyagminőség');
        $ec->saveMaterialData($f3->_rMat, 'r');
        $blc->txt(false, 'A nyíróbetét acélszilárdság tervezési értéke megegyezik a betonvas anyagminőségével.');
    }
}
