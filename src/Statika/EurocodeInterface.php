<?php declare(strict_types=1);
/**
 * (c) Bence VÁNKOS | https:// structure.hu
 * Statika framework: Eurocode based calculations for structural design
 */

namespace Statika;

use Base;
use Profil\Exceptions\InvalidSectionNameException;
use Profil\ProfilService;
use Profil\Section\SectionDTO;
use Statika\Block\BlockService;
use Statika\Bolt\BoltDTO;
use Statika\Bolt\BoltFactory;
use Statika\Bolt\InvalidBoltNameException;
use Statika\Material\InvalidMaterialNameException;
use Statika\Material\MaterialDTO;
use Statika\Material\MaterialFactory;

/**
 * Extension of BlocksInterface of Statika framework - renders and handles Eurocode related UI blocks and their data.
 * @todo document interface in README
 */
interface EurocodeInterface extends BlocksInterface
{
    public function __construct(Base $f3, BlockService $blockService, MaterialFactory $materialFactory, BoltFactory $boltFactory, ProfilService $profilService);

    /**
     * Returns Material DTO by material name
     * @param string $materialName Name of material like 'S235', '8.8', 'C25/30', 'B500'
     */
    public function getMaterial(string $materialName): MaterialDTO;

    /**
     * Returns Bolt DTO by bolt name
     * @param string $boltName Name of bolt like 'M12' or 'M16'
     */
    public function getBolt(string $boltName): BoltDTO;

    /**
     * @param string $materialName
     * @param float $t Thickness of relevant plate [mm]
     * @return float Yield strength in [N/mm^2; MPa]
     */
    public function fy(string $materialName, float $t): float;

    /**
     * @param float $t Thickness of relevant plate [mm]
     * @return float Ultimate strength in [N/mm^2; MPa]
     */
    public function fu(string $matName, float $t): float;

    /**
     * @param string[] $title
     */
    public function materialListBlock(string $variableName = 'materialName', string $default = 'S235', array $title = ['', 'Anyagminőség']): void;

    /**
     * @param string[] $title
     */
    public function structuralSteelMaterialListBlock(string $variableName = 'steelMaterialName', string $default = 'S235', array $title = ['', 'Szerkezeti acél anyagminőség']): void;

    /**
     * @param string[] $title
     */
    public function rebarMaterialListBlock(string $variableName = 'rebarMaterialName', string $default = 'B500', array $title = ['', 'Betonacél anyagminőség']): void;

    /**
     * @param string[] $title
     */
    public function boltMaterialListBlock(string $variableName = 'boltMaterialName', string $default = '8.8', array $title = ['', 'Csavar anyagminőség']): void;

    /**
     * @param string[] $title
     */
    public function concreteMaterialListBlock(string $variableName = 'concreteMaterialName', string $default = 'C25/30', array $title = ['', 'Beton anyagminőség']): void;

    /**
     * @param string[] $title
     */
    public function steelMaterialListBlock(string $variableName = 'steelMaterialName', string $default = 'S235', array $title = ['', 'Acél anyagminőség']): void;

    /**
     * Renders bolt selector block
     */
    public function boltListBlock(string $variableName = 'boltName', string $default = 'M16', array $title = ['', 'Csavar név']): void;

    /**
     * Renders input field that accepts space separated numeric list
     * @param string $variableName
     * @param string[] $title
     * @param string $default
     * @param string $description
     */
    public function numericArrayInput(string $variableName, array $title = ['', 'Lista'], string $default = '1 1', string $description = 'Szóközzel elválasztott szám lista'): void;

    /**
     * Renders steel section family selector block
     * @param string $variableName
     * @param string[] $title
     * @param string $default
     */
    public function sectionFamilyListBlock(string $variableName = 'sectionFamily', array $title =['', 'Szelvény család'], string $default = 'HEA'): void;

    /**
     *
     */
    public function sectionListBlock(string $familyName = 'HEA', string $variableName = 'sectionName', array $title = ['', 'Szelvény név'], string $default = 'HEA200'): void;

    /**
     * Returns Section data transfer object by section name
     * @todo test
     */
    public function getSection($sectionName): SectionDTO;

    /**
     * @param float $D
     * @param float|int $multiplicator
     * @return float
     * @todo test
     */
    public function A(float $D, float $multiplicator = 1): float;
}
