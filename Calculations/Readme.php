<?php

namespace Calculation;

Class Readme extends \Ecc
{

    /**
     * @var $f3 \Base
     * @var $blc \Blc
     * @var $ec \Ec\Ec
     * @throws \Exception
     */
    public function calc(object $f3, object $blc, object $ec): void
    {
        $README = <<<EOT

## Számítások futtatása

A menü *Futtatás* parancsára vagy *Enter* billentyűparancsra a számítás lefut. Automatikusan lefut továbbá bizonyos beviteli mezők változatásakor.

EOT;

        $blc->md($README);
    }
}
