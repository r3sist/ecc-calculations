<?php declare(strict_types=1);
/**
 * (c) Bence VÁNKOS | https:// structure.hu
 * Statika framework: Eurocode based calculations for structural design
 */

namespace Statika;

use Base;
use Profil\ProfilService;
use Statika\Block\BlockService;
use Statika\Bolt\BoltFactory;
use Statika\Material\MaterialFactory;

/**
 * Extension of BlocksInterface of Statika framework - renders and handles Eurocode related UI blocks and their data.
 * @todo document interface in README
 */
interface EurocodeInterface extends BlocksInterface
{
    public function __construct(Base $f3, BlockService $blockService, MaterialFactory $materialFactory, BoltFactory $boltFactory, ProfilService $profilService);
}
