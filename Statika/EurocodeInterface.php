<?php declare(strict_types=1);
/**
 * (c) Bence VÁNKOS | https:// structure.hu
 * Statika framework: Eurocode based calculations for structural design
 */

namespace Statika;

/**
 * Extension of BlocksInterface of Statika framework - renders and handles Eurocode related UI blocks and their data.
 * @todo document interface in README
 */
interface EurocodeInterface extends BlocksInterface
{
    public function __construct();
}
