<?php declare(strict_types = 1);
/**
 * (c) Bence VÁNKOS | https:// structure.hu | https://github.com/r3sist/ecc-calculations
 * Structure app - Ecc: Eurocode based calculations for structural design
 */

namespace Statika\Calculations;

use Base;
use Statika\Block\BlockService;
use Statika\Bolt\BoltDTO;
use Statika\Bolt\BoltFactory;
use Statika\Bolt\InvalidBoltNameException;
use Statika\EurocodeInterface;
use Statika\Material\InvalidMaterialNameException;
use Statika\Material\MaterialDTO;
use Statika\Material\MaterialFactory;
use H3;
use Statika\Blc;
use InvalidArgumentException;
use Exception;
use Profil\Exceptions\InvalidSectionNameException;
use Profil\ProfilService;
use Profil\Section\SectionDTO;
use Respect\Validation\Validator as v;
use function in_array;


/** Eurocode globals, helpers and predefined GUI elements for ECC framework */
class Ec extends Blc implements EurocodeInterface
{
    private MaterialFactory $materialFactory;
    private BoltFactory $boltFactory;
    private ProfilService $profilService;

    private v $vAlnum;

    public const GG =  1.35;
    public const GQ =  1.5;
    public const GM0 =  1.0;
    public const GM1 =  1.0;
    public const GM2 =  1.25;
    public const GM3 =  1.25;
    public const GM3ser =  1.1;
    public const GM6ser =  1.0;
    public const Gc =  1.5;
    public const Gs =  1.15;
    public const GS =  1.15;
    public const GcA =  1.2;
    public const GSA =  1.0;

    /**
     * Ec constructor.
     * Defines Eurocode parameters in hive: __GG, __GQ, __GM0, __GM2, __GM3, __GM3ser, __GM6ser, __Gc, __Gs, __GS, __GcA, __GSA
     */
    public function __construct(Base $f3, BlockService $blockService, MaterialFactory $materialFactory, BoltFactory $boltFactory, ProfilService $profilService)
    {
        parent::__construct($f3, $blockService);

        $this->materialFactory = $materialFactory;
        $this->boltFactory = $boltFactory;
        $this->profilService = $profilService;


        $this->vAlnum = v::alnum()->noWhitespace();
    }

    /**
     * @param string|float $materialName
     * @return MaterialDTO
     * @throws InvalidMaterialNameException
     */
    public function getMaterial($materialName): MaterialDTO
    {
        return $this->materialFactory->getMaterialByName((string) $materialName);
    }

    public function getBolt(string $boltName): BoltDTO
    {
        return $this->boltFactory->getBoltByName($boltName);
    }

    public function fy(string $materialName, float $t): float
    {
        if ($t > 40) {
            $this->txt('Lemezvastagság miatt csökkentett szilárdság figyelembe véve:');

            return $this->getMaterial($materialName)->fy40;
        }

        return $this->getMaterial($materialName)->fy;
    }

    public function fu(string $matName, float $t): float
    {
        if ($t > 40) {
            $this->txt('Lemezvastagság miatt csökkentett szilárdság figyelembe véve:');

            return $this->getMaterial($matName)->fu40;
        }

        return $this->getMaterial($matName)->fu;
    }

    public function materialListBlock(string $variableName = 'mat', string $default = 'S235', array $title = ['', 'Anyagminőség']): void
    {
        $list = $this->materialFactory->getMaterialNames();
        $source = array_combine($list, $list);

        $this->lst($variableName, $source, $title, $default);
    }

    public function structuralSteelMaterialListBlock(string $variableName = 'mat', string $default = 'S235', array $title = ['', 'Szerkezeti acél anyagminőség']): void
    {
        $list = $this->materialFactory->getStructuralSteelNames();
        $source = array_combine($list, $list);

        $this->lst($variableName, $source, $title, $default);
    }

    /**
     * @param string[] $title
     * @throws Exception
     */
    public function rebarMaterialListBlock(string $variableName = 'mat', string $default = 'B500', array $title = ['', 'Betonacél anyagminőség']): void
    {
        $list = $this->materialFactory->getRebarNames();
        $source = array_combine($list, $list);

        $this->lst($variableName, $source, $title, $default);
    }

    /**
     * @param string[] $title
     * @throws Exception
     */
    public function boltMaterialListBlock(string $variableName = 'mat', string $default = '8.8', array $title = ['', 'Csavar anyagminőség']): void
    {
        $list = $this->materialFactory->getBoltNames();
        $source = array_combine($list, $list);

        $this->lst($variableName, $source, $title, $default);
    }

    /**
     * @param string[] $title
     * @throws Exception
     */
    public function concreteMaterialListBlock(string $variableName = 'mat', string $default = 'C25/30', array $title = ['', 'Beton anyagminőség']): void
    {
        $list = $this->materialFactory->getConcreteNames();
        $source = array_combine($list, $list);

        $this->lst($variableName, $source, $title, $default);
    }

    /**
     * @param string[] $title
     * @throws Exception
     */
    public function steelMaterialListBlock(string $variableName = 'mat', string $default = 'S235', array $title = ['', 'Acél anyagminőség']): void
    {
        $list = $this->materialFactory->getSteelNames();
        $source = array_combine($list, $list);

        $this->lst($variableName, $source, $title, $default);
    }

    /**
     * Renders bolt selector block
     * @throws Exception
     */
    public function boltListBlock(string $variableName = 'bolt', string $default = 'M16', array $title = ['', 'Csavar név']): void
    {
        $list = $this->boltFactory->getBoltNames();
        $source = array_combine($list, $list);

        $this->lst($variableName, $source, $title, $default);
    }

    public function wrapNumerics(string $variableNameA, string $variableNameB, string $stringTitle, $defaultValueA, $defaultValueB, string $unitAB = '', string $helpAB = '', string $middleText = '')
    {
         $this->wrapper0($stringTitle);
            $this->numeric($variableNameA, ['', ''], (float)$defaultValueA, $unitAB, '', '');
        $this->wrapper1($middleText);
            $this->numeric($variableNameB, ['', ''], (float)$defaultValueB, $unitAB, '', '');
        $this->wrapper2($helpAB);
    }

    /**
     * Wraps a numeric (as rebar count) and a rebarList (as rebar diameter) block
     * @param string $variableNameCount Saves rebar count with this name
     * @param string $variableNameRebar Saves rebar diameter with this name
     * @param string $help If empty, sum section area displayed
     * @param string $variableNameA If not empty, saves sum section area with this name
     */
    public function wrapRebarCount(string $variableNameCount, string $variableNameRebar, string $titleString, int $defaultValueCount, int $defaultValueRebar = 16, string $help = '', string $variableNameA = ''): void
    {
        $this->wrapper0($titleString);
            $this->numeric($variableNameCount, ['', ''], $defaultValueCount, '', '');
        $this->wrapper1('×');
            $this->rebarList($variableNameRebar, $defaultValueRebar, ['', ''], '');

        $A = floor($this->A($this->get($variableNameRebar), $this->get($variableNameCount)));

        if ($help === '') {
            $help = '$ = ' . $A . ' [mm^2]$';
        }

        if ($variableNameA !== '') {
            $this->set($variableNameA, $A);
        }

        $this->wrapper2($help);
    }

    public function wrapRebarDistance(string $variableNameDistance, string $variableNameRebar, string $titleString, int $defaultValueDistance, int $defaultValueRebar = 16, string $help = ''): void
    {
        $this->wrapper0($titleString);
            $this->rebarList($variableNameRebar, $defaultValueRebar, ['', ''], '');
        $this->wrapper1('/');
            $this->numeric($variableNameDistance, ['', ''], $defaultValueDistance, 'mm', '');
        $this->wrapper2($help);
    }

    public function rebarList(string $variableName = 'fi', float $default = 16, array $title = ['', 'Vasátmérő'], string $help = ''): void
    {
        $default = (int)$default;

        $source = ['ϕ6' => 6, 'ϕ8' => 8, 'ϕ10' => 10, 'ϕ12' => 12, 'ϕ14' => 14, 'ϕ16' => 16, 'ϕ20' => 20, 'ϕ25' => 25, 'ϕ28' => 28, 'ϕ32' => 32, 'ϕ36' => 36, 'ϕ40' => 40,];
        $this->lst($variableName, $source, $title, $default, $help);
    }

    /**
     * @param string $bulkName
     * @return float
     */
    public function rebarTable(string $bulkName = 'As_bulk'): float
    {
        $fields = [
            ['name' => 50, 'title' => 'Ø8', 'type' => 'input', 'sum' => true],
            ['name' => 79, 'title' => 'Ø10', 'type' => 'input', 'sum' => true],
            ['name' => 112, 'title' => 'Ø12', 'type' => 'input', 'sum' => true],
            ['name' => 154, 'title' => 'Ø16', 'type' => 'input', 'sum' => true],
            ['name' => 314, 'title' => 'Ø20', 'type' => 'input', 'sum' => true],
            ['name' => 491, 'title' => 'Ø25', 'type' => 'input', 'sum' => true],
            ['name' => 616, 'title' => 'Ø28', 'type' => 'input', 'sum' => true],
            ['name' => 804, 'title' => 'Ø32', 'type' => 'input', 'sum' => true],
        ];
        $this->bulk($bulkName, $fields);
        $As = 0;
        if ($this->__isset($bulkName.'_sum')) {
            foreach ($this->__get($bulkName.'_sum') as $key => $value) {
                $As = $As + $value * $key;
            }
        }
        return $As;
    }

    /**
     * Renders steel section family selector block
     * @param string $variableName
     * @param string[] $title
     * @param string $default
     * @throws Exception
     */
    public function sectionFamilyListBlock(string $variableName = 'sectionFamily', array $title =['', 'Szelvény család'], string $default = 'HEA'): void
    {
        $list = $this->profilService->getSectionFamilies();
        $source = array_combine($list, $list);
        $this->lst($variableName, $source, $title, $default, '');
    }

    /**
     * @throws InvalidSectionNameException
     */
    public function sectionListBlock(string $familyName = 'HEA', string $variableName = 'sectionName', array $title = ['', 'Szelvény név'], string $default = 'HEA200'): void
    {
        if (!in_array($familyName, $this->profilService->getSectionFamilies())) {
            throw new InvalidSectionNameException('Nincs ilyen szelvény család az adatbázisban.');
        }

        $results = $this->profilService->readSectionsForSearch('family', 'e', $familyName, 'name', 'e', '');

        $list = [];
        foreach ($results as $section) {
            $list[$section['name']] = $section['name'];
        }

        $this->lst($variableName, $list, $title, $default);
    }

    /**
     * @throws Exception
     * @deprecated Use getSection() instead
     */
    public function spreadSectionData(string $sectionName, bool $renderTable = false, string $arrayName = 'sectionData'): void
    {
        $sectionData = $this->profilService->getSection($sectionName)->toArray();

        // Remove underscores
        $cleanedSectionData = [];
        foreach ($sectionData as $key => $value) {
            if ($key[0] === '_') {
                $cleanedSectionData[(string)substr($key, 1, 10)] = $sectionData[$key];
            }
        }
        $this->f3->set('_' . $arrayName, $cleanedSectionData);

        if ($renderTable) {
            $rows = [];
            $scheme = array_keys($cleanedSectionData);
            $rows[] = array_values($cleanedSectionData);

            $this->region0('sectionTable', $cleanedSectionData['name'] . ' szelvény adatok');
            $this->tbl($scheme, $rows, 'tbl' . H3::slug($cleanedSectionData['name']), 'Mértékegységek, további információk: [structure.hu/profil](https://structure.hu/profil)'); // TODO check markdown why does not work
            $this->region1();
        }
    }

    /**
     * Returns Section data transfer object by section name
     * @throws InvalidSectionNameException
     * @todo test
     */
    public function getSection($sectionName): SectionDTO
    {
        return $this->profilService->getSection($sectionName);
    }

    /**
     * Saves all material properties from DB to Hive variables, with prefix. e.g.: _prefixfck, _prefixfy etc
     * @param float|string $materialName
     * @throws InvalidArgumentException|Exception
     * @deprecated
     */
    public function spreadMaterialData($materialName, string $prefixForHive = ''): void
    {
        $materialName = (string)$materialName;

        if ($prefixForHive !== '') {
            $this->vAlnum->assert($prefixForHive);
        }

        $matData = $this->materialFactory->getMaterialByName($materialName)->toArray();

        $this->region0('materialData' . $prefixForHive, 'Anyagjellemzők (' . $materialName . ')');
        foreach ($matData as $key => $value) {
            if ($value != 0) {
                $this->f3->set('_' . $prefixForHive . $key, $value);

                switch ($key) {
                    case 'fy':
                        $this->math('f_y = ' . $value . ' [N/(mm^2)]', 'Folyáshatár');
                        break;
                    case 'fu':
                        $this->math('f_u = ' . $value . ' [N/(mm^2)]', 'Szakító szilárdság');
                        break;
                    case 'fy40':
                        $this->math('f_(y,40) = ' . $value . ' [N/(mm^2)]', 'Folyáshatár 40+ mm lemez esetén');
                        break;
                    case 'fu40':
                        $this->math('f_(u,40) = ' . $value . ' [N/(mm^2)]', 'Szakító szilárdság 40+ mm lemez esetén');
                        break;
                    case 'betaw':
                        $this->math('beta_w = ' . $value . '', 'Hegesztési tényező');
                        break;
                    case 'fyd':
                        $this->math('f_(yd) = ' . $value . ' [N/(mm^2)]', 'Folyáshatár tervezési értéke');
                        break;
                    case 'fck':
                        $this->math('f_(ck) = ' . $value . '  [N/(mm^2)]', 'Nyomószilárdság karakterisztikus értéke (5% kvantilis) ($phi$ 150×300 henger)');
                        break;
                    case 'fckcube':
                        $this->math('f_(ck,cube) = ' . $value . ' [N/(mm^2)]', 'Nyomószilárdság karakterisztikus értéke (5% kvantilis) (□150×150×150 kocka)');
                        break;
                    case 'fcm':
                        $this->math('f_(cm) = ' . $value . ' [N/(mm^2)]', 'Nyomószilárdság várható értéke');
                        $this->note('`f.cm = f.ck + 8`');
                        break;
                    case 'fctm':
                        $this->math('f_(ctm) = ' . $value . ' [N/(mm^2)]', 'Húzószilárdság várható értéke');
                        break;
                    case 'fctd':
                        $this->math('f_(ctd) = ' . $value . ' [N/(mm^2)]', 'Húzószilárdság tervezési értéke');
                        break;
                    case 'fctk005':
                        $this->math('f_(ctk,0.05) = ' . $value . ' [N/(mm^2)]', 'Húzószilárdság karakterisztikus értéke (5% kvantilis)');
                        break;
                    case 'fctk095':
                        $this->math('f_(ctk,0.95) = ' . $value . ' [N/(mm^2)]', 'Húzószilárdság karakterisztikus értéke (95% kvantilis)');
                        break;
                    case 'Ecm':
                        $this->math('E_(cm)= ' . $value . ' [(kN)/(mm^2)]', 'Beton rugalmassági modulusa');
                        $this->note('`E.cm = 22(f.cm/10)^0.3`');
                        $this->note('Húrmodulus `sigma.c = 0` és `sigma.c = 0.4f.cm` között.');
                        break;
                    case 'Ecu3':
                        $this->math('E_(cu3)= ' . $value . ' []', '');
                        break;
                    case 'Euk':
                        $this->math('E_(uk)= ' . $value . ' []', '');
                        break;
                    case 'Es':
                        $this->math('E_s= ' . $value . ' [(kN)/(mm^2)]', 'Betonacél rugalmassági modulusa');
                        break;
                    case 'Fc0':
                        $this->math('F_(c0)= ' . $value . ' []', '');
                        break;
                    case 'F_c0':
                        $this->math('F_(_c0)= ' . $value . ' []', '');
                        break;
                    case 'fbd':
                        $this->math('f_(bd)= ' . $value . ' [N/(mm^2)]', 'Beton és acél közti kapcsolati szilárdság bordás betonacéloknál, jó tapadás esetén');
                        $this->boo($prefixForHive . 'fbd07', ['', 'Rossz tapadás vagy 300 mm-nél magasabb gerendák felső vasa'], true, 'Csökkentés 70%-ra');
                        if ($this->f3->get('_' . $prefixForHive . 'fbd07')) {
                            $this->def($prefixForHive . 'fbd', $this->f3->get('_' . $prefixForHive . 'fbd') * 0.7, 'f_(bd) = f_(bd,eff) = f_(bd)*0.7 = %%');
                        }
                        break;
                    case 'fcd':
                        $this->math('f_(cd) = ' . $value . ' [N/(mm^2)]', 'Nyomószilárdság tervezési értéke');
                        $this->note('`f.cd = f.ck/gamma.c`');
                        break;
                    case 'fiinf28':
                        $this->math('phi(infty,28) = ' . $value . '', 'Kúszási tényező átlagos végértéke. (Állandó/tartós terhelés, 70% párat., 28 n. szil. terhelése, képlékeny konzisztencia betonozása, 100 mm egyenértékű lemezvast.)');
                        break;
                    case 'Eceff':
                        $this->math('E_(c,eff) = ' . $value . ' [(kN)/(mm^2)] = ' . $value * 100 . ' [(kN)/(cm^2)] = '.$value * 1000 . ' [N/(mm^2)]', 'Beton hatásos alakváltozási tényezője a kúszás végértékével');
                        $this->note('`E.c.eff = E.cm/(1+fi.inf.28)`');
                        break;
                    case 'alfat':
                        $this->math('alpha_t = ' . $value . ' [1/K]', 'Hőtágulási együttható');
                        break;
                    case 'Epsiloncsinf':
                        $this->math('epsilon_(cs,infty) = ' . $value . '', 'Beton zsugorodásának végértéke (kúszási tényezőnél adott feltételeknél)');
                        break;
                }
            }
        }
        $this->region1();
    }

    /**
     * @todo Move to Wind()
     * @todo test
     */
    public function qpz(float $z, float $terrainCat): float
    {
        $terrainCat = (int)$terrainCat;

        $terrainDb = [
            '1' => ['z0' => 0.01, 'zmin' => 1.00, 'kr' => 0.170],
            '2' => ['z0' => 0.05, 'zmin' => 2.00, 'kr' => 0.190],
            '3' => ['z0' => 0.30, 'zmin' => 5.00, 'kr' => 0.215],
            '4' => ['z0' => 1.00, 'zmin' => 10.0, 'kr' => 0.234]
        ];

        $vb0 = 23.6;
        $cDir = 1.0;
        $cSeason = 1.0;
        $cAlt = 1.0;
        $cProb = 1.0;
        $vb = $vb0 * $cDir * $cSeason * $cProb * $cAlt;
        $c0z = 1;
        $z0 = $terrainDb[$terrainCat]['z0'];
        $zmin = $terrainDb[$terrainCat]['zmin'];
        $kr = $terrainDb[$terrainCat]['kr'];
        if ($z < $zmin) {
            $z = $zmin;
        }
        $crz = $kr * log($z / $z0);
        $vmz = $crz * $c0z * $vb;
        $ki = 1;
        $sigmaV = $kr * $ki * $vb;
        $Ivz = $sigmaV / $vmz;
        $qpz = H3::n3((1 + 7 * $Ivz) * 0.5 * (1.25 / 1000) * ($vmz * $vmz));

        $blc = $this;
        $blc->note('$v_(b,0) = ' . $vb0 . '; c_(dir) = ' . $cDir . '; c_(season) = ' . $cSeason . '; c_(al t) = ' . $cAlt . '$');
        $blc->note('$v_b = v_(b,0)*c_(dir)*c_(season)*c_(prob)*c_(al t) = ' . $vb . '$');
        $blc->note('$c_(0,z) = ' . $c0z . '; z_0 = ' . $z0 . '; z_(min) = ' . $zmin . '; k_r = ' . $kr . '; z = ' . $z . '$');
        $blc->note('$c_(r,z) = k_r*log(z/z_0) = ' . $crz . '$');
        $blc->note('$v_(m,z) = c_(r,z)*c_(0,z)*v_b = ' . $vmz . '$');
        $blc->note('$k_i = ' . $ki . '; sigma_V = k_r*k_i*v_b = ' . $sigmaV . '$');
        $blc->note('$I_(v,z) = sigma_v/v_(m,z) = ' . $Ivz . '$');
        $blc->note('$q_p(z) = (1 + 7*I_(v,z))*0.5*(1.25/1000)*v_(m,z)^2 = ' . $qpz . '$');

        return $qpz;
    }

    /**
     * @param float $x1
     * @param float $y1
     * @param float $x2
     * @param float $y2
     * @param float $x
     * @return float
     * @todo test
     */
    public function linterp(float $x1, float $y1, float $x2, float $y2, float $x): float
    {
        if ($x1 === $x2 && $y1 === $y2) {
            return $y2;
        }

        return (($x - $x1) * ($y2 - $y1) / ($x2 - $x1)) + $y1;
    }

    /**
     * @param float $D
     * @param float|int $multiplicator
     * @return float
     * @todo test
     */
    public function A(float $D, float $multiplicator = 1): float
    {
        return $D * $D * pi() * 0.25 * $multiplicator;
    }

    /**
     * Original: https://github.com/hellofromtonya/Quadratic/blob/master/solver.php
     * @return string[]|float[] may contain 'i'
     * @todo test
     */
    public function quadratic(float $a, float $b, float $c, int $precision = 3): array
    {
        $bsmfac = $b * $b - 4 * $a * $c;

        if ($bsmfac < 0) { // Accounts for complex roots.
            $plusminusone = ' + ';
            $plusminustwo = ' - ';
            $bsmfac *= -1;
            $complex = (sqrt($bsmfac) / (2 * $a));
            if ($a < 0) { // If negative imaginary term, tidies appearance.
                $plusminustwo = ' + ';
                $plusminusone = ' - ';
                $complex *= -1;
            }
            $lambdaone = round(-$b / (2 * $a), $precision) . $plusminusone . round($complex, $precision) . 'i';
            $lambdatwo = round(-$b / (2 * $a), $precision) . $plusminustwo . round($complex, $precision) . 'i';

            return [$lambdaone, $lambdatwo];
        }

        // Simplifies if b^2 = 4ac (real roots).
        if ($bsmfac === 0) {
            $lambdaone = round(-$b / (2 * $a), $precision);
            $lambdatwo = round(-$b / (2 * $a), $precision);

            return [$lambdaone, $lambdatwo];
        }

        // Finds real roots when b^2 != 4ac.
        $lambdaone = (-$b + sqrt($bsmfac)) / (2 * $a);
        $lambdaone = round($lambdaone, $precision);
        $lambdatwo = (-$b - sqrt($bsmfac)) / (2 * $a);
        $lambdatwo = round($lambdatwo, $precision);

        return [$lambdaone, $lambdatwo];
    }

    /**
     * @param float[] $roots
     * @todo test
     */
    public function chooseRoot(float $estimation, array $roots): float
    {
        $validRoot = $roots[0];

        if ((abs($estimation - $validRoot) > abs($roots[1] - $estimation))) {
            $validRoot = $roots[1];
        }

        return $validRoot;
    }

    /**
     * @deprecated
     * @todo test
     */
    public function getClosest(float $find, array $stackArray, string $returnType = 'closest'): float
    {
        // returnTypes: closest, ceil, floor, linterp

        $returnValue = null;
        $returnKey = null;
        ksort($stackArray);
        $pointerArray = [];

        foreach ($stackArray as $key => $value) {
            $pointerArray[] = ['k' => (float)$key, 'v' => $value];
        }
        $keys = array_column($pointerArray, 'k');
        $values = array_values($stackArray);
        $lowestKey = min($keys);
        $highestKey = max($keys);

        foreach ($pointerArray as $pointer => $valueArray) {
            $key = $valueArray['k'];
            $value = $valueArray['v'];

            switch ($returnType) {
                case 'closest':
                    if ($key == $find) {
                        return $value;
                    }
                    if ($returnKey === null || (abs($find - $returnKey) > abs($key - $find))) {
                        $returnKey = $key;
                        \H3::dump($returnKey);
                        return $value;
                    }
                    break;
                case 'floor':
                    if ($key == $find) {
                        return $value;
                    }
                    if ($find <= $lowestKey) {
                        return $stackArray[$lowestKey];
                    }
                    if ($returnKey === null || ($returnKey < $key && $key <= $find)) {
                        $returnKey = $key;
                        return $value;
                    }
                    break;
                case 'ceil':
                    if ($key == $find) {
                        return $value;
                    }
                    if ($find >= $highestKey) {
                        return $stackArray[$highestKey];
                    }
                    if ($returnKey === null && ($returnKey < $key && $key >= $find)) {
                        $returnKey = $key;
                        return $value;
                    }
                    break;
                case 'linterp':
                    if ($key === $find) {
                        return $value;
                    }
                    $floor = $this->getClosest($find, $stackArray, 'floor');
                    $ceil = $this->getClosest($find, $stackArray, 'ceil');
                    if ($floor != $ceil) {
                        return $this->linterp(array_search($floor, $stackArray, true), $floor, array_search($ceil, $stackArray, true), $ceil, $find);
                    }
                    return $ceil;
                    break;
            }
        }
        return $find;
    }

    /** @todo test */
    public function proportion(float $x0, float $y0, float $x1): float
    {
        return ($x1*$y0)/$x0;
    }

    /**
     * Returns closest floor number from series of keys. Note that array keys can not be floats, that's why there are string types.
     * @param string[] $array One dimensional array of keys
     * @param string $find Compare array keys to this
     * @return string Floor key as string
     * @todo test
     */
    public function getFloorClosest(array $array, string $find): string
    {
        $match = null;

        sort($array);

        foreach ($array as $value) {
            $value = (float)$value;

            if ($value === null) {
                $match = $value;
            }

            if ($value <= $find && abs($value - $find) <= abs($value - $match)) {
                $match = $value;
            }
        }

        return (string)$match;
    }

    /**
     * Returns closest ceil number from series of keys. Note that array keys can not be floats, that's why there are string types.
     * @param string[] $array One dimensional array of keys
     * @param string $find Compare array keys to this
     * @return string Floor key as string
     * @todo test
     */
    public function getCeilClosest(array $array, string $find): string
    {
        $match = null;

        sort($array);

        foreach ($array as $value) {
            $value = (float)$value;

            if ($value === null) {
                $match = $value;
            }

            if ($value >= $find && abs($value - $find) < abs($value - $match)) {
                $match = $value;
            }
        }

        return (string)$match;
    }
}
