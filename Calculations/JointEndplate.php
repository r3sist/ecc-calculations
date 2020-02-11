<?php declare(strict_types = 1);
// Analysis of steel rigid end plate joint according to Eurocodes - Calculation class for ECC framework
// (c) Bence VÁNKOS | https://structure.hu | https://github.com/r3sist/ecc-calculations

namespace Calculation;

use \Base;
use \Ecc\Blc;
use \Ec\Ec;
use \H3;

Class JointEndplate
{
    protected Bolt $bolt;
    protected Weld $weld;

    public function __construct(Bolt $bolt, Weld $weld)
    {
        $this->bolt = $bolt;
        $this->weld = $weld;
    }

    public function calc(Base $f3, Blc $blc, Ec $ec): void
    {
        $ec->matList('steelMaterialName', 'S235', ['', 'Szelvények, lemezek anyagminősége']);
        $ec->spreadMaterialData($f3->_steelMaterialName, 's');
    }
}
