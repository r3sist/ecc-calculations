<?php declare(strict_types = 1);
/**
 * Analysis of shear connection of beams of composite steel and concretes structures according to Eurocodes - Calculation class for Statika framework
 * (c) Bence VÁNKOS | https://structure.hu | https://github.com/r3sist/ecc-calculations
 */
namespace Statika\Calculations;

use \H3;
use Statika\Ec;
use Statika\EurocodeInterface;

Class CompositeBeamConnection
{
    /**
     * @param Ec $ec
     */
    public function calc(EurocodeInterface $ec): void
    {
        $ec->note('[Szabó B. - Hajlított, nyírt öszvértartók tervezése az Eurocode-dal összhangban, 2017]. [MSZ-EN 1994]');
    }
}
