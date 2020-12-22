<?php declare(strict_types = 1);
/**
 * (c) Bence VÁNKOS | https:// structure.hu | https://github.com/r3sist/ecc-calculations
 * Structure app - Ecc: Eurocode based calculations for structural design
 */

namespace Ec;

use Base;
use DB\SQL;
use Ecc\Bolt\BoltDTO;
use Ecc\Bolt\BoltFactory;
use Ecc\Bolt\InvalidBoltNameException;
use Ecc\Material\InvalidMaterialNameException;
use Ecc\Material\MaterialDTO;
use Ecc\Material\MaterialFactory;
use Ecc\Map\DataMap;
use H3;
use Ecc\Blc;
use InvalidArgumentException;
use Exception;
use Respect\Validation\Validator as v;


/** Eurocode globals, helpers and predefined GUI elements for ECC framework */
class Ec
{
    /**
     * @var string TABLE_PROFILES
     * @deprecated
     */
    private const TABLE_PROFILES = 'steel_sections';

    private Base $f3;
    private Blc $blc;

    /**
     * @var SQL $db
     * @deprecated
     */
    private SQL $db;

    /**
     * @var DataMap $dataMap
     * @deprecated
     */
    private DataMap $dataMap; // Structure app

    private MaterialFactory $materialFactory;
    private BoltFactory $boltFactory;

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
    public function __construct(Base $f3, Blc $blc, SQL $db, DataMap $dataMap, MaterialFactory $materialFactory, BoltFactory $boltFactory)
    {
        $this->f3 = $f3;
        $this->blc = $blc;
        $this->db = $db;
        $this->dataMap = $dataMap;
        $this->materialFactory = $materialFactory;
        $this->boltFactory = $boltFactory;

        $this->vAlnum = v::alnum()->noWhitespace();

        $this->f3->set('__GG', self::GG);
        $this->f3->set('__GQ', self::GQ);
        $this->f3->set('__GM0', self::GM0);
        $this->f3->set('__GM1', self::GM1);
        $this->f3->set('__GM2', self::GM2);
        $this->f3->set('__GM3', self::GM3);
        $this->f3->set('__GM3ser', self::GM3ser);
        $this->f3->set('__GM6ser', self::GM6ser);
        $this->f3->set('__Gc', self::Gc);
        $this->f3->set('__Gs', self::GS);
        $this->f3->set('__GS', self::GS);
        $this->f3->set('__GcA', self::GcA);
        $this->f3->set('__GSA', self::GSA);
    }

    /**
     * Returns Material DTO by material name
     * @param string $materialName Name of material like 'S235', '8.8', 'C25/30', 'B500'
     * @throws InvalidMaterialNameException
     */
    public function getMaterial(string $materialName): MaterialDTO
    {
        return $this->materialFactory->getMaterialByName($materialName);
    }

    /**
     * Get data record by data_id from ecc_data table
     * @param string $dataName Name of data as dataset identifier
     * @return array Associative array of read data
     * @deprecated use dedicated methods instead
     */
    public function readData(string $dataName): array
    {
        $this->dataMap->load(['dname = :dname', ':dname' => $dataName]);
        if (!$this->dataMap->dry()) {
            return json_decode($this->dataMap->djson, true, 512, JSON_THROW_ON_ERROR);
        }
        return [];
    }

    /**
     * Returns Bolt DTO by bolt name
     * @param string $boltName Name of bolt like 'M12' or 'M16'
     * @throws InvalidBoltNameException
     */
    public function getBolt(string $boltName): BoltDTO
    {
        return $this->boltFactory->getBoltByName($boltName);
    }

    /**
     * @param string $materialName
     * @param float $t Thickness of relevant plate [mm]
     * @return float Yield strength in [N/mm^2; MPa]
     * @throws InvalidMaterialNameException
     */
    public function fy(string $materialName, float $t): float
    {
        if ($t > 40) {
            $this->blc->txt('Lemezvastagság miatt csökkentett szilárdság figyelembe véve:', '');
            return $this->getMaterial($materialName)->fy40;
        }
        return $this->getMaterial($materialName)->fy;
    }

    /**
     * @param float $t Thickness of relevant plate [mm]
     * @return float Ultimate strength in [N/mm^2; MPa]
     * @throws InvalidMaterialNameException
     */
    public function fu(string $matName, float $t): float
    {
        if ($t > 40) {
            $this->blc->txt('Lemezvastagság miatt csökkentett szilárdság figyelembe véve:', '');
            return $this->getMaterial($matName)->fu40;
        }
        return $this->getMaterial($matName)->fu;
    }

    /**
     * @param string[] $title
     * @throws Exception
     */
    public function materialListBlock(string $variableName = 'mat', string $default = 'S235', array $title = ['', 'Anyagminőség']): void
    {
        $list = $this->materialFactory->getMaterialNames();
        $source = array_combine($list, $list);

        $this->blc->lst($variableName, $source, $title, $default);
    }

    /**
     * @param string[] $title
     * @throws Exception
     */
    public function structuralSteelMaterialListBlock(string $variableName = 'mat', string $default = 'S235', array $title = ['', 'Szerkezeti acél anyagminőség']): void
    {
        $list = $this->materialFactory->getStructuralSteelNames();
        $source = array_combine($list, $list);

        $this->blc->lst($variableName, $source, $title, $default);
    }

    /**
     * @param string[] $title
     * @throws Exception
     */
    public function rebarMaterialListBlock(string $variableName = 'mat', string $default = 'B500', array $title = ['', 'Betonacél anyagminőség']): void
    {
        $list = $this->materialFactory->getRebarNames();
        $source = array_combine($list, $list);

        $this->blc->lst($variableName, $source, $title, $default);
    }

    /**
     * @param string[] $title
     * @throws Exception
     */
    public function boltMaterialListBlock(string $variableName = 'mat', string $default = '8.8', array $title = ['', 'Csavar anyagminőség']): void
    {
        $list = $this->materialFactory->getBoltNames();
        $source = array_combine($list, $list);

        $this->blc->lst($variableName, $source, $title, $default);
    }

    /**
     * @param string[] $title
     * @throws Exception
     */
    public function concreteMaterialListBlock(string $variableName = 'mat', string $default = 'C25/30', array $title = ['', 'Beton anyagminőség']): void
    {
        $list = $this->materialFactory->getConcreteNames();
        $source = array_combine($list, $list);

        $this->blc->lst($variableName, $source, $title, $default);
    }

    /**
     * @param string[] $title
     * @throws Exception
     */
    public function steelMaterialListBlock(string $variableName = 'mat', string $default = 'S235', array $title = ['', 'Acél anyagminőség']): void
    {
        $list = $this->materialFactory->getSteelNames();
        $source = array_combine($list, $list);

        $this->blc->lst($variableName, $source, $title, $default);
    }

    /**
     * @throws Exception
     */
    public function boltListBlock(string $variableName = 'bolt', string $default = 'M16', array $title = ['', 'Csavar név']): void
    {
        $list = $this->boltFactory->getBoltNames();
        $source = array_combine($list, $list);

        $this->blc->lst($variableName, $source, $title, $default);
    }

    /**
     * @param mixed $defaultValueA
     * @param mixed $defaultValueB
     */
    public function wrapNumerics(string $variableNameA, string $variableNameB, string $stringTitle, $defaultValueA, $defaultValueB, string $unitAB = '', string $helpAB = '', string $middleText = '')
    {
        $blc = $this->blc;

        $blc->wrapper0($stringTitle);
        $blc->numeric($variableNameA, [], (float)$defaultValueA, $unitAB, '', '');
        $blc->wrapper1($middleText);
        $blc->numeric($variableNameB, [], (float)$defaultValueB, $unitAB, '', '');
        $blc->wrapper2($helpAB);
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
        $this->blc->wrapper0($titleString);
        $this->blc->numeric($variableNameCount, [], $defaultValueCount, '', '');
        $this->blc->wrapper1('×');
        $this->rebarList($variableNameRebar, $defaultValueRebar, [], '');

        $A = floor($this->A($this->f3->get('_' . $variableNameRebar), $this->f3->get('_' . $variableNameCount)));

        if ($help === '') {
            $help = '$ = ' . $A . ' [mm^2]$';
        }

        if ($variableNameA !== '') {
            $this->f3->set('_' . $variableNameA, $A);
        }

        $this->blc->wrapper2($help);
    }

    public function wrapRebarDistance(string $variableNameDistance, string $variableNameRebar, string $titleString, int $defaultValueDistance, int $defaultValueRebar = 16, string $help = ''): void
    {
        $this->blc->wrapper0($titleString);
        $this->rebarList($variableNameRebar, $defaultValueRebar, [], '');
        $this->blc->wrapper1('/');
        $this->blc->numeric($variableNameDistance, [], $defaultValueDistance, 'mm', '');
        $this->blc->wrapper2($help);
    }

    /**
     * @deprecated
     */
    public function rebarList(string $variableName = 'fi', float $default = 16, array $title = ['', 'Vasátmérő'], string $help = ''): void
    {
        $default = (int)$default;

        $source = ['ϕ6' => 6, 'ϕ8' => 8, 'ϕ10' => 10, 'ϕ12' => 12, 'ϕ14' => 14, 'ϕ16' => 16, 'ϕ20' => 20, 'ϕ25' => 25, 'ϕ28' => 28, 'ϕ32' => 32, 'ϕ36' => 36, 'ϕ40' => 40,];
        $this->blc->lst($variableName, $source, $title, $default, $help);
    }

    public function rebarTable(string $variableNameBulk = 'As'): float
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
        $this->blc->bulk('bulk' . H3::slug($variableNameBulk), $fields);
        $As = 0;
        if ($this->f3->exists('sum')) {
            foreach ($this->f3->get('sum') as $key => $value) {
                $As = $As + $value * $key;
            }
        }
        return $As;
    }

    public function sectionFamilyList(string $variableName = 'sectionFamily', string $title = 'Szelvény család', string $default = 'HEA'): void
    {
        $list = ['HEA' => 'HEA', 'HEB' => 'HEB', 'HEM' => 'HEM', 'I' => 'I', 'IPE' => 'IPE', 'IPEO' => 'IPEO', 'IPN' => 'IPN', 'UPN' => 'UPN', 'UPE' => 'UPE', 'L' => 'L', 'O' => 'O', 'D' => 'D', 'ROR' => 'ROR', 'RHS' => 'RHS', 'C' => 'C',];
        $this->blc->lst($variableName, $list, ['', $title], $default, '');
    }

    public function sectionList(string $familyName = 'HEA', string $variableName = 'sectionName', string $title = 'Szelvény név', string $default = 'HEA200'): void
    {
        $this->vAlnum->assert($familyName);

        $query = "SELECT * FROM ".self::TABLE_PROFILES." WHERE name LIKE '$familyName%'";
        $result = $this->db->exec($query);
        $list = [];
        foreach ($result as $section) {
            $list[$section['name']] = $section['name'];
        }
        $this->blc->lst($variableName, $list, ['', $title], $default, '');
    }

    public function spreadSectionData(string $sectionName, bool $renderTable = false, string $arrayName = 'sectionData'): void
    {
        $sectionName = (string)$this->f3->clean($sectionName);

        $query = "
            SELECT name, _h, _b, _tf, _tw, _Ax, _Ay, _Az, _Ix, _Iy, _Iz, _I1, _I2, _W1elt, _W1elb, _W2elt, _W2elb, _W1pl, _W2pl FROM ".self::TABLE_PROFILES." WHERE name = '$sectionName' LIMIT 1";
        /** @var array $result */
        $result = $this->db->exec($query);

        // Remove underscores
        $resultCleaned = [];
        foreach ($result[0] as $key => $value) {
            if ($key[0] === '_') {
                $resultCleaned[(string)substr($key, 1, 10)] = $result[0][$key];
            }
        }
        $this->f3->set('_' . $arrayName, $resultCleaned);

        if ($renderTable) {
            $rows = [];
            $scheme = array_keys($result[0]);
            foreach ($result as $key => $row) {
                $rows[] = array_values($row);
            }

            $this->blc->region0('sectionTable', $result[0]['name'] . ' szelvény adatok');
            $this->blc->tbl($scheme, $rows, 'tbl' . H3::slug($result[0]['name']), 'Mértékegységek, további információk: <a href="https://structure.hu/profil">structure.hu/profil</a>');
            $this->blc->region1();
        }
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

        $this->blc->region0('materialData' . $prefixForHive, 'Anyagjellemzők (' . $materialName . ')');
        foreach ($matData as $key => $value) {
            if ($value != 0) {
                $this->f3->set('_' . $prefixForHive . $key, $value);

                switch ($key) {
                    case 'fy':
                        $this->blc->math('f_y = ' . $value . ' [N/(mm^2)]', 'Folyáshatár');
                        break;
                    case 'fu':
                        $this->blc->math('f_u = ' . $value . ' [N/(mm^2)]', 'Szakító szilárdság');
                        break;
                    case 'fy40':
                        $this->blc->math('f_(y,40) = ' . $value . ' [N/(mm^2)]', 'Folyáshatár 40+ mm lemez esetén');
                        break;
                    case 'fu40':
                        $this->blc->math('f_(u,40) = ' . $value . ' [N/(mm^2)]', 'Szakító szilárdság 40+ mm lemez esetén');
                        break;
                    case 'betaw':
                        $this->blc->math('beta_w = ' . $value . '', 'Hegesztési tényező');
                        break;
                    case 'fyd':
                        $this->blc->math('f_(yd) = ' . $value . ' [N/(mm^2)]', 'Folyáshatár tervezési értéke');
                        break;
                    case 'fck':
                        $this->blc->math('f_(ck) = ' . $value . '  [N/(mm^2)]', 'Nyomószilárdság karakterisztikus értéke (5% kvantilis) ($phi$ 150×300 henger)');
                        break;
                    case 'fckcube':
                        $this->blc->math('f_(ck,cube) = ' . $value . ' [N/(mm^2)]', 'Nyomószilárdság karakterisztikus értéke (5% kvantilis) (□150×150×150 kocka)');
                        break;
                    case 'fcm':
                        $this->blc->math('f_(cm) = ' . $value . ' [N/(mm^2)]', 'Nyomószilárdság várható értéke');
                        $this->blc->note('`f.cm = f.ck + 8`');
                        break;
                    case 'fctm':
                        $this->blc->math('f_(ctm) = ' . $value . ' [N/(mm^2)]', 'Húzószilárdság várható értéke');
                        break;
                    case 'fctd':
                        $this->blc->math('f_(ctd) = ' . $value . ' [N/(mm^2)]', 'Húzószilárdság tervezési értéke');
                        break;
                    case 'fctk005':
                        $this->blc->math('f_(ctk,0.05) = ' . $value . ' [N/(mm^2)]', 'Húzószilárdság karakterisztikus értéke (5% kvantilis)');
                        break;
                    case 'fctk095':
                        $this->blc->math('f_(ctk,0.95) = ' . $value . ' [N/(mm^2)]', 'Húzószilárdság karakterisztikus értéke (95% kvantilis)');
                        break;
                    case 'Ecm':
                        $this->blc->math('E_(cm)= ' . $value . ' [(kN)/(mm^2)]', 'Beton rugalmassági modulusa');
                        $this->blc->note('`E.cm = 22(f.cm/10)^0.3`');
                        $this->blc->note('Húrmodulus `sigma.c = 0` és `sigma.c = 0.4f.cm` között.');
                        break;
                    case 'Ecu3':
                        $this->blc->math('E_(cu3)= ' . $value . ' []', '');
                        break;
                    case 'Euk':
                        $this->blc->math('E_(uk)= ' . $value . ' []', '');
                        break;
                    case 'Es':
                        $this->blc->math('E_s= ' . $value . ' [(kN)/(mm^2)]', 'Betonacél rugalmassági modulusa');
                        break;
                    case 'Fc0':
                        $this->blc->math('F_(c0)= ' . $value . ' []', '');
                        break;
                    case 'F_c0':
                        $this->blc->math('F_(_c0)= ' . $value . ' []', '');
                        break;
                    case 'fbd':
                        $this->blc->math('f_(bd)= ' . $value . ' [N/(mm^2)]', 'Beton és acél közti kapcsolati szilárdság bordás betonacéloknál, jó tapadás esetén');
                        $this->blc->boo($prefixForHive . 'fbd07', ['', 'Rossz tapadás vagy 300 mm-nél magasabb gerendák felső vasa'], true, 'Csökkentés 70%-ra');
                        if ($this->f3->get('_' . $prefixForHive . 'fbd07')) {
                            $this->blc->def($prefixForHive . 'fbd', $this->f3->get('_' . $prefixForHive . 'fbd') * 0.7, 'f_(bd) = f_(bd,eff) = f_(bd)*0.7 = %%');
                        }
                        break;
                    case 'fcd':
                        $this->blc->math('f_(cd) = ' . $value . ' [N/(mm^2)]', 'Nyomószilárdság tervezési értéke');
                        $this->blc->note('`f.cd = f.ck/gamma.c`');
                        break;
                    case 'fiinf28':
                        $this->blc->math('phi(infty,28) = ' . $value . '', 'Kúszási tényező átlagos végértéke. (Állandó/tartós terhelés, 70% párat., 28 n. szil. terhelése, képlékeny konzisztencia betonozása, 100 mm egyenértékű lemezvast.)');
                        break;
                    case 'Eceff':
                        $this->blc->math('E_(c,eff) = ' . $value . ' [(kN)/(mm^2)] = ' . $value * 100 . ' [(kN)/(cm^2)] = '.$value * 1000 . ' [N/(mm^2)]', 'Beton hatásos alakváltozási tényezője a kúszás végértékével');
                        $this->blc->note('`E.c.eff = E.cm/(1+fi.inf.28)`');
                        break;
                    case 'alfat':
                        $this->blc->math('alpha_t = ' . $value . ' [1/K]', 'Hőtágulási együttható');
                        break;
                    case 'Epsiloncsinf':
                        $this->blc->math('epsilon_(cs,infty) = ' . $value . '', 'Beton zsugorodásának végértéke (kúszási tényezőnél adott feltételeknél)');
                        break;
                }
            }
        }
        $this->blc->region1();
    }

    /**
     * @throws InvalidMaterialNameException
     */
    public function FtRd(string $btName, $btMat, bool $verbose = true): float
    {
        $btMat = (string)$btMat;
        $result = (0.9 * $this->getMaterial($btMat)->fu * $this->getBolt($btName)->As) / (1000 * $this->f3->get('__GM2'));
        if ($verbose) {
            $this->blc->note('Húzás általános képlet: $F_(t,Rd) = (0.9*f_(u,b)*A_s)/(gamma_(M2))$');
        }
        return $result;
    }

    public function BpRd(string $btName, string $stMat, float $t): float
    {
        $this->blc->note('`BpRd` kigombolódás általános képlet: $(0.6* pi *d_m*f_(u,s)*t)/(gamma_(M2))$');
        return (0.6 * pi() * $this->getBolt($btName)->dm * $this->fu($stMat, $t) * $t) / (1000 * $this->f3->get('__GM2'));
    }

    // Nyírt csavar

    /** @param float|string $btMat */
    public function FvRd(string $btName, $btMat, float $n, float $As = 0): float
    {
        $btMat = (string)$btMat;
        $n = (int)$n;

        if ($As === (float)0) {
            $As = $this->getBolt($btName)->As;
        }
        $result = (($this->getMaterial($btMat)->fu * $As * 0.6) / (1000 * $this->f3->get('__GM2'))) * $n;
        $this->blc->note('$F_(v,Rd)$ nyírás általános képlet: $n*(0.6*f_(u,b)*A_s)/(gamma_(M2))$');

        if ($btMat === '4.8' || $btMat === '5.8' || $btMat === '6.8' || $btMat === '10.9') {
            $this->blc->note('$F_(v,Rd)$ nyírás: ' . $btMat . ' csavar anyag miatt az eredmény 80%-ra csökkentve.');
            return $result * 0.8;
        }
        return $result;
    }

    // Csavar palástnyomás

    /**
     * @param float|string $btMat
     * @throws InvalidMaterialNameException
     */
    public function FbRd(string $btName, $btMat, string $stMat, float $ep1, float $ep2, float $t, bool $inner): float
    {
        $btMat = (string)$btMat;

        $fust = $this->fu($stMat, $t);
        $k1 = min(2.8 * ($ep2 / $this->getBolt($btName)->d0) - 1.7, 2.5);
        $alphab = min(($ep1 / (3 * $this->getBolt($btName)->d0)), $this->getMaterial($btMat)->fu / $fust, 1);
        if ($inner) {
            $k1 = min(1.4 * ($ep2 / $this->getBolt($btName)->d0) - 1.7, 2.5);
            $alphab = min(($ep1 / (3 * $this->getBolt($btName)->d0)) - 0.25, $this->getMaterial($btMat)->fu / $this->fu($stMat, $t), 1);
        }
        $result = $k1 * (($alphab * $fust * $this->getBolt($btName)->d * $t) / (1000 * $this->f3->get('__GM2')));
        $this->f3->set('___k1', $k1);
        $this->f3->set('___alphab', $alphab);
        if ($result <= 0) {
            return 0;
        }
        $this->blc->note('$F_(b,Rd)$ palástnyomás általános képlet: $k_1*(alpha_b*f_(u,s)*d*t)/(gamma_(M2))$');
        return $result;
    }

    // Acél keresztmetszet nyírási ellenállása: Av [mm2], t [mm], returns [kN]
    public function VplRd(float $Av, string $matName, float $t): float
    {
        $Av = (float)$Av;
        $t = (float)$t;
        $fy = $this->fy($matName, $t);
        return ($Av * $fy) / (sqrt(3) * $this->f3->get('__GM0') * 1000);
    }

    // Acél keresztmetszet húzási ellenállása: A Anet [mm2], t [mm], returns [kN]
    public function NtRd($A, $Anet, string $matName, $t): float
    {
        $A = (float)$A;
        $Anet = (float)$Anet;
        $t = (float)$t;
        return min($this->NuRd($Anet, $matName, $t), $this->NplRd($A, $matName, $t));
    }

    public function NuRd($Anet, string $matName, $t): float
    {
        $Anet = (float)$Anet;
        $t = (float)$t;
        $fu = $this->fu($matName, $t);
        return (0.9 * $Anet * $fu) / ($this->f3->get('__GM2') * 1000);
    }

    public function NplRd($A, string $matName, $t): float
    {
        $A = (float)$A;
        $t = (float)$t;
        $fy = $this->fy($matName, $t);
        return ($A * $fy) / ($this->f3->get('__GM0') * 1000);
    }

    public function NcRd(float $A, float $fy): float
    {
        return ($A * $fy) / ($this->f3->get('__GM0') * 1000);
    }

    public function McRd(float $W, float $fy): float
    {
        return ($W * $fy) / ($this->f3->get('__GM0') * 1000000);
    }

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

        $blc = $this->blc;
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

    public function linterp(float $x1, float $y1, float $x2, float $y2, float $x): float
    {
        if ($x1 === $x2 && $y1 === $y2) {
            return $y2;
        }

        return (($x - $x1) * ($y2 - $y1) / ($x2 - $x1)) + $y1;
    }

    public function A(float $D, float $multiplicator = 1): float
    {
        return $D * $D * pi() * 0.25 * $multiplicator;
    }

    /**
     * Original: https://github.com/hellofromtonya/Quadratic/blob/master/solver.php
     * @return string[]|float[] may contain 'i'
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
     */
    public function chooseRoot(float $estimation, array $roots): float
    {
        $validRoot = $roots[0];

        if ((abs($estimation - $validRoot) > abs($roots[1] - $estimation))) {
            $validRoot = $roots[1];
        }

        return $validRoot;
    }

    /** @deprecated  */
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

    public function proportion(float $x0, float $y0, float $x1): float
    {
        return ($x1*$y0)/$x0;
    }

    /**
     * Returns closest floor number from series of keys. Note that array keys can not be floats, that's why there are string types.
     * @param string[] $array One dimensional array of keys
     * @param string $find Compare array keys to this
     * @return string Floor key as string
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
