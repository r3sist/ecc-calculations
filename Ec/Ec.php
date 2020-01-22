<?php declare(strict_types = 1);

namespace Ec;

use \Base;
use H3;
use \Ecc\Blc;
use Respect\Validation\Validator as v;


/**
 * Eurocode globals and predefined GUI elements for ECC framework
 *
 * (c) Bence VÁNKOS
 * https:// structure.hu
 */

class Ec
{
    public Base $f3;
    private Blc $blc;

    private v $vAlnum;

    /**
     * Ec constructor.
     * Defines Eurocode parameters in hive: __GG, __GQ, __GM0, __GM2, __GM3, __GM3ser, __GM6ser, __Gc, __Gs, __GS, __GcA, __GSA
     */
    public function __construct(Base $f3, Blc $blc)
    {
        $this->f3 = $f3;
        $this->blc = $blc;

        $this->vAlnum = v::alnum()->noWhitespace();

        $this->f3->set('__GG', 1.35);
        $this->f3->set('__GQ', 1.5);
        $this->f3->set('__GM0', 1.0);
        $this->f3->set('__GM1', 1.0);
        $this->f3->set('__GM2', 1.25);
        $this->f3->set('__GM3', 1.25);
        $this->f3->set('__GM3ser', 1.1);
        $this->f3->set('__GM6ser', 1.0);
        $this->f3->set('__Gc', 1.5);
        $this->f3->set('__Gs', 1.15);
        $this->f3->set('__GS', 1.15);
        $this->f3->set('__GcA', 1.2);
        $this->f3->set('__GSA', 1.0);
    }

    /**
     * Get data record by data_id from ecc_data table
     * @param string $dbName
     * @return array Assoc. array of read data
     */
    public function readData(string $dbName): array
    {
        $this->f3->get('md')->load(['dname = :dname', ':dname' => $dbName]);
        if (!$this->f3->get('md')->dry()) {
            return json_decode($this->f3->get('md')->djson, true);
        }
        return [];
    }

    /**
     * Get all material data as array from database
     * Units in database:
     * ﻿0 [-]
     * fy [Mpa]
     * fu [Mpa]
     * fy40 [Mpa]
     * fu40 [Mpa]
     * betaw [-]
     * fyd [Mpa]
     * fck [Mpa]
     * fckcube [Mpa]
     * fcm [Mpa]
     * fctm [Mpa]
     * fctk005 [Mpa]
     * fctk095 [Mpa]
     * Ecm color(teal)( GPa)
     * Ecu3 [-]
     * Euk [-]
     * Es [Gpa]
     * Fc0 [-]
     * F_c0 [-]
     * fbd [Mpa]
     * fcd [Mpa]
     * fctd [Mpa]
     * fiinf28 [-]
     * Eceff [Gpa]
     * alfat [1/Cdeg]
     * Epsiloncsinf [-]
     * @return array Assoc. array of read material data
     */
    public function getMaterialArray(): array
    {
        return $this->readData('mat');
    }

    public function matProp(string $name, string $property): float
    {
        $matDb = $this->getMaterialArray();
        return $matDb[$name][$property];
    }

    public function boltProp(string $name, string $property): float
    {
        $boltDb = $this->readData('bolt');
        return $boltDb[$name][$property];
    }

    public function fy(string $matName, float $t): float
    {
        if ($t > 40) {
            $this->blc->txt('Lemezvastagság miatt csökkentett szilárdság figyelembe véve:', '');
            return $this->matProp($matName, 'fy40');
        }
        return $this->matProp($matName, 'fy');
    }

    public function fu(string $matName, float $t): float
    {
        if ($t > 40) {
            $this->blc->txt('Lemezvastagság miatt csökkentett szilárdság figyelembe véve:', '');
            return $this->matProp($matName, 'fu40');
        }
        return $this->matProp($matName, 'fu');
    }

    public function matList(string $variableName = 'mat', string $default = 'S235', array $title = ['', 'Anyagminőség'], string $category = ''): void
    {
        $blc = $this->blc;
        $matDb = $this->getMaterialArray();

        if ($category != false) {
            foreach ($matDb as $key => $value) {
                switch ($category) {
                    case 'bolt':
                        if ($value['0'] !== 'bolt') {
                            unset($matDb[$key]);
                        }
                        break;
                    case 'rebar':
                        if ($value['0'] !== 'rebar') {
                            unset($matDb[$key]);
                        }
                        break;
                    case 'concrete':
                        if ($value['0'] !== 'concrete') {
                            unset($matDb[$key]);
                        }
                        break;
                    case 'steel':
                        if ($value['0'] !== 'steel') {
                            unset($matDb[$key]);
                        }
                        break;
                    case 'steels':
                        if (!in_array($value['0'], ['steel', 'bolt', 'rebar'])) {
                            unset($matDb[$key]);
                        }
                        break;
                }
            }
        }

        $keys =  array_keys($matDb);
        $list = [];
        foreach ($keys as $key) {
            $list[$key] = $key;
        }
        $blc->lst($variableName, $list, $title, $default, '');
    }

    public function boltList(string $variableName = 'bolt', string $default = 'M16', string $title = 'Csavar betöltése'): void
    {
        $boltDb = $this->readData('bolt');
        $keys =  array_keys($boltDb);
        $list = [];
        foreach ($keys as $key) {
            $list[$key] = $key;
        }
        $this->blc->lst($variableName, $list, ['', $title], $default, '');
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

    public function wrapRebarCount(string $variableNameCount, string $variableNameRebar, string $titleString, float $defaultValueCount, float $defaultValueRebar = 16, string $help = ''): void
    {
        $defaultValueCount = (int)$defaultValueCount;
        $defaultValueRebar = (int)$defaultValueRebar;

        $this->blc->wrapper0($titleString);
            $this->blc->numeric($variableNameCount, [], $defaultValueCount, '', '');
            $this->blc->wrapper1('');
            $this->rebarList($variableNameRebar, $defaultValueRebar, [], '');
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

    public function rebarList(string $variableName = 'fi', float $default = 16, array $title = ['', 'Vasátmérő'], string $help = ''): void
    {
        $default = (int)$default;

        $source = [
            'ϕ6' => 6,
            'ϕ8' => 8,
            'ϕ10' => 10,
            'ϕ12' => 12,
            'ϕ14' => 14,
            'ϕ16' => 16,
            'ϕ20' => 20,
            'ϕ25' => 25,
            'ϕ28' => 28,
            'ϕ32' => 32,
            'ϕ36' => 36,
            'ϕ40' => 40,
        ];
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
        $this->blc->bulk('bulk'.H3::slug($variableNameBulk), $fields);
        $As = 0;
        if ($this->f3->exists('sum')) {
            foreach ($this->f3->get('sum') as $key => $value) {
                $As = $As + $value*$key;
            }
        }
        return $As;
    }

    public function sectionFamilyList(string $variableName = 'sectionFamily', string $title = 'Szelvény család', string $default = 'HEA'): void
    {
        $list = [
            'HEA' => 'HEA',
            'HEB' => 'HEB',
            'HEM' => 'HEM',
            'I' => 'I',
            'IPE' => 'IPE',
            'IPEO' => 'IPEO',
            'IPN' => 'IPN',
            'UPN' => 'UPN',
            'UPE' => 'UPE',
            'L' => 'L',
            'O' => 'O',
            'D' => 'D',
            'ROR' => 'ROR',
            'RHS' => 'RHS',
            'C' => 'C',
        ];
        $this->blc->lst($variableName, $list, ['', $title], $default, '');
    }

    public function sectionList(string $familyName = 'HEA', string $variableName = 'sectionName', string $title = 'Szelvény név', string $default = 'HEA200'): void
    {
        $this->vAlnum->assert($familyName);

        $query = "SELECT * FROM steelSection WHERE name2 LIKE '$familyName%'";
        $result = $this->f3->get('db')->exec($query);
        $list = [];
        foreach ($result as $section) {
            $list[$section['name2']] = $section['name2'];
        }
        $this->blc->lst($variableName, $list, ['', $title], $default, '');
    }

    public function spreadSectionData(string $sectionName, bool $renderTable = false, string $arrayName = 'sectionData'): void
    {
        $sectionName = $this->f3->clean($sectionName);

        $query = "
            SELECT name2, _h, _b, _tf, _tw, _Ax, _Ay, _Az, _Ix, _Iy, _Iz, _I1, _I2, _W1elt, _W1elb, _W2elt, _W2elb, _W1pl, _W2pl FROM steelSection WHERE name2 = '$sectionName' LIMIT 1";
        $result = $this->f3->get('db')->exec($query);

        // Remove underscores
        $resultCleaned = [];
        foreach ($result[0] as $key => $value) {
            if (substr($key, 0, 1) == '_') {
                $resultCleaned[substr($key, 1, 10)] = $result[0][$key];
            }
        }
        $this->f3->set('_'.$arrayName, $resultCleaned);

        if ($renderTable) {
            $rows = [];
            $scheme = array_keys($result[0]);
            foreach ($result as $key => $row) {
                array_push($rows, array_values($row));
            }

            $this->blc->region0('sectionTable', $result[0]['name2'].' szelvény adatok');
                $this->blc->tbl($scheme, $rows, 'tbl'.H3::slug($result[0]['name2']), 'Mértékegységek, további információk: <a href="https://structure.hu/profil">structure.hu/profil</a>');
            $this->blc->region1();
        }
    }

    /**
     * Saves all material properties from DB to Hive variables, with prefix. e.g.: _prefixfck, _prefixfy etc
     * @param float|string $matName
     */
    public function spreadMaterialData($matName, string $prefix = ''): void
    {
        $matName = (string)$matName;

        if ($prefix !== '') {
            $this->vAlnum->assert($prefix);
        }

        $matDb = $this->getMaterialArray();
        $matData = $matDb[$matName]; // TODO check if exists

        $this->blc->region0('materialData'.$prefix, 'Anyagjellemzők');
        foreach ($matData as $key => $value) {
            if ($value != 0 && $value != '' && $key != '0') {
                $this->f3->set('_'.$prefix.$key, $value);

                switch($key) {
                    case 'fy':
                        $this->blc->math('f_y = '.$value.' [N/(mm^2)]', 'Folyáshatár');
                        break;
                    case 'fu':
                        $this->blc->math('f_u = '.$value.' [N/(mm^2)]', 'Szakító szilárdság');
                        break;
                    case 'fy40':
                        $this->blc->math('f_(y,40) = '.$value.' [N/(mm^2)]', 'Folyáshatár 40+ mm lemez esetén');
                        break;
                    case 'fu40':
                        $this->blc->math('f_(u,40) = '.$value.' [N/(mm^2)]', 'Szakító szilárdság 40+ mm lemez esetén');
                        break;
                    case 'betaw':
                        $this->blc->math('beta_w = '.$value.'', 'Hegesztési tényező');
                        break;
                    case 'fyd':
                        $this->blc->math('f_(yd) = '.$value.' [N/(mm^2)]', 'Folyáshatár tervezési értéke');
                        break;
                    case 'fck':
                        $this->blc->math('f_(ck) = '.$value.'  [N/(mm^2)]', 'Nyomószilárdság karakterisztikus értéke (5% kvantilis) ($phi$ 150×300 henger)');
                        break;
                    case 'fckcube':
                        $this->blc->math('f_(ck,cube) = '.$value.' [N/(mm^2)]', 'Nyomószilárdság karakterisztikus értéke (5% kvantilis) (□150×150×150 kocka)');
                        break;
                    case 'fcm':
                        $this->blc->math('f_(cm) = '.$value.' [N/(mm^2)]', 'Nyomószilárdság várható értéke');
                        $this->blc->note('`f.cm = f.ck + 8`');
                        break;
                    case 'fctm':
                        $this->blc->math('f_(ctm) = '.$value.' [N/(mm^2)]', 'Húzószilárdság várható értéke');
                        break;
                    case 'fctd':
                        $this->blc->math('f_(ctd) = '.$value.' [N/(mm^2)]', 'Húzószilárdság tervezési értéke');
                        break;
                    case 'fctk005':
                        $this->blc->math('f_(ctk,0.05) = '.$value.' [N/(mm^2)]', 'Húzószilárdság karakterisztikus értéke (5% kvantilis)');
                        break;
                    case 'fctk095':
                        $this->blc->math('f_(ctk,0.95) = '.$value.' [N/(mm^2)]', 'Húzószilárdság karakterisztikus értéke (95% kvantilis)');
                        break;
                    case 'Ecm':
                        $this->blc->math('E_(cm)= '.$value.' [(kN)/(mm^2)]', 'Beton rugalmassági modulusa');
                        $this->blc->note('`E.cm = 22(f.cm/10)^0.3`');
                        $this->blc->note('Húrmodulus `sigma.c = 0` és `sigma.c = 0.4f.cm` között.');
                        break;
                    case 'Ecu3':
                        $this->blc->math('E_(cu3)= '.$value.' []', '');
                        break;
                    case 'Euk':
                        $this->blc->math('E_(uk)= '.$value.' []', '');
                        break;
                    case 'Es':
                        $this->blc->math('E_s= '.$value.' [(kN)/(mm^2)]', 'Betonacél rugalmassági modulusa');
                        break;
                    case 'Fc0':
                        $this->blc->math('F_(c0)= '.$value.' []', '');
                        break;
                    case 'F_c0':
                        $this->blc->math('F_(_c0)= '.$value.' []', '');
                        break;
                    case 'fbd':
                        $this->blc->math('f_(bd)= '.$value.' [N/(mm^2)]', 'Beton és acél közti kapcsolati szilárdság bordás betonacéloknál, jó tapadás esetén');
                        $this->blc->boo($prefix.'fbd07', ['', 'Rossz tapadás vagy 300 mm-nél magasabb gerendák felső vasa'], true, 'Csökkentés 70%-ra');
                        if ($this->f3->get('_'.$prefix.'fbd07')) {
                            $this->blc->def($prefix.'fbd', $this->f3->get('_'.$prefix.'fbd')*0.7, 'f_(bd) = f_(bd,eff) = f_(bd)*0.7 = %%');
                        }
                        break;
                    case 'fcd':
                        $this->blc->math('f_(cd) = '.$value.' [N/(mm^2)]', 'Nyomószilárdság tervezési értéke');
                        $this->blc->note('`f.cd = f.ck/gamma.c`');
                        break;
                    case 'fiinf28':
                        $this->blc->math('phi(infty,28) = '.$value.'', 'Kúszási tényező átlagos végértéke. (Állandó/tartós terhelés, 70% párat., 28 n. szil. terhelése, képlékeny konzisztencia betonozása, 100 mm egyenértékű lemezvast.)');
                        break;
                    case 'Eceff':
                        $this->blc->math('E_(c,eff) = '.$value.' [(kN)/(mm^2)] = '.$value*100 .' [(kN)/(cm^2)]', 'Beton hatásos alakváltozási tényezője a kúszás végértékével');
                        $this->blc->note('`E.c.eff = E.cm/(1+fi.inf.28)`');
                        break;
                    case 'alfat':
                        $this->blc->math('alpha_t = '.$value.' [1/K]', 'Hőtágulási együttható');
                        break;
                    case 'Epsiloncsinf':
                        $this->blc->math('epsilon_(cs,infty) = '.$value.'', 'Beton zsugorodásának végértéke (kúszási tényezőnél adott feltételeknél)');
                        break;
                }
            }
        }
        $this->blc->region1();
    }

    public function FtRd(string $btName, $btMat, bool $verbose = true): float
    {
        $btMat = (string)$btMat;
        $result = (0.9 * $this->matProp($btMat, 'fu') * $this->boltProp($btName, 'As')) / (1000 * $this->f3->get('__GM2'));
        if ($verbose) {
            $this->blc->note('`FtRd` húzás általános képlet: $(0.9*f_(u,b)*A_s)/(gamma_(M2))$');
        }
        return $result;
    }

    public function BpRd(string $btName, string $stMat, float $t): float
    {
        $this->blc->note('`BpRd` kigombolódás általános képlet: $(0.6* pi *d_m*f_(u,s)*t)/(gamma_(M2))$');
        return (0.6 * pi() * $this->boltProp($btName, 'dm') * $this->fu($stMat, $t) * $t) / (1000 * $this->f3->get('__GM2'));
    }

    // Nyírt csavar
    /** @param float|string $btMat */
    public function FvRd(string $btName, $btMat, float $n, float $As = 0): float
    {
        $btMat = (string)$btMat;
        $n = (int)$n;

        if ($As == 0) {
            $As = $this->boltProp($btName, 'As');
        }
        $result = (( $this->matProp($btMat, 'fu') * $As * 0.6) / (1000 * $this->f3->get('__GM2')) )*$n;
        $this->blc->note('$F_(v,Rd)$ nyírás általános képlet: $n*(0.6*f_(u,b)*A_s)/(gamma_(M2))$');

        if ($btMat == '4.8' || $btMat == '5.8' || $btMat == '6.8' || $btMat == '10.9') {
            $this->blc->note('$F_(v,Rd)$ nyírás: '.$btMat.' csavar anyag miatt az eredmény 80%-ra csökkentve.');
            return $result*0.8;
        }
        return $result;
    }

    // Csavar palástnyomás
    /** @param float|string $btMat */
    public function FbRd(string $btName, $btMat, string $stMat, float $ep1, float $ep2, float $t, bool $inner): float
    {
        $btMat = (string)$btMat;

        $fust = $this->fu($stMat, $t);
        $k1 = min(2.8*($ep2/ $this->boltProp($btName, 'd0')) - 1.7, 2.5);
        $alphab = min(($ep1/ (3* $this->boltProp($btName, 'd0'))), $this->matProp($btMat, 'fu')/ $fust, 1);
        if ($inner) {
            $k1 = min(1.4*($ep2/ $this->boltProp($btName, 'd0')) - 1.7, 2.5);
            $alphab = min(($ep1/ (3* $this->boltProp($btName, 'd0'))) - 0.25, $this->matProp($btMat, 'fu')/ $this->fu($stMat, $t), 1);
        }
        $result = $k1*(($alphab*$fust* $this->boltProp($btName, 'd') * $t)/(1000 * $this->f3->get('__GM2')));
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
        return ($Av*$fy)/(sqrt(3)*$this->f3->get('__GM0')*1000);
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
        return (0.9*$Anet*$fu)/($this->f3->get('__GM2')*1000);
    }

    public function NplRd($A, string $matName, $t): float
    {
        $A = (float)$A;
        $t = (float)$t;
        $fy = $this->fy($matName, $t);
        return ($A*$fy)/($this->f3->get('__GM0')*1000);
    }

    public function NcRd(float $A, float $fy): float
    {
        return ($A*$fy)/($this->f3->get('__GM0')*1000);
    }

    public function McRd(float $W, float $fy): float
    {
        return ($W*$fy)/($this->f3->get('__GM0')*1000000);
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
        $vb = $vb0*$cDir*$cSeason*$cProb*$cAlt;
        $c0z = 1;
        $z0 = $terrainDb[$terrainCat]['z0'];
        $zmin = $terrainDb[$terrainCat]['zmin'];
        $kr = $terrainDb[$terrainCat]['kr'];
        if ($z < $zmin) {
            $z = $zmin;
        }
        $crz = $kr*log($z/$z0);
        $vmz = $crz*$c0z*$vb;
        $ki = 1;
        $sigmaV = $kr*$ki*$vb;
        $Ivz = $sigmaV/$vmz;
        $qpz = H3::n3((1 + 7*$Ivz)*0.5*(1.25/1000)*($vmz*$vmz));

        $blc = $this->blc;
        $blc->note('$v_(b,0) = '.$vb0.'; c_(dir) = '.$cDir.'; c_(season) = '.$cSeason.'; c_(al t) = '.$cAlt.'$');
        $blc->note('$v_b = v_(b,0)*c_(dir)*c_(season)*c_(prob)*c_(al t) = '.$vb.'$');
        $blc->note('$c_(0,z) = '.$c0z.'; z_0 = '.$z0.'; z_(min) = '.$zmin.'; k_r = '.$kr.'; z = '.$z.'$');
        $blc->note('$c_(r,z) = k_r*log(z/z_0) = '.$crz.'$');
        $blc->note('$v_(m,z) = c_(r,z)*c_(0,z)*v_b = '.$vmz.'$');
        $blc->note('$k_i = '.$ki.'; sigma_V = k_r*k_i*v_b = '.$sigmaV.'$');
        $blc->note('$I_(v,z) = sigma_v/v_(m,z) = '.$Ivz.'$');
        $blc->note('$q_p(z) = (1 + 7*I_(v,z))*0.5*(1.25/1000)*v_(m,z)^2 = '.$qpz.'$');

        return $qpz;
    }

    public function linterp(float $x1, float $y1, float $x2, float $y2, float $x): float
    {
        return (($x - $x1)*($y2 - $y1)/($x2 - $x1)) + $y1;
    }

    public function A(float $D, float $multiplicator = 1): float
    {
        return $D*$D*pi()*0.25*$multiplicator;
    }

    /** Original: https://github.com/hellofromtonya/Quadratic/blob/master/solver.php */
    // TODO tests
    public function quadratic(float $a, float $b, float $c, int $precision = 3): array
    {
        $bsmfac = $b * $b - 4 * $a * $c;
        if ( $bsmfac < 0 ) { // Accounts for complex roots.
            $plusminusone = ' + ';
            $plusminustwo = ' - ';
            $bsmfac *= - 1;
            $complex = (sqrt( $bsmfac ) / (2 * $a));
            if ( $a < 0 ) { // If negative imaginary term, tidies appearance.
                $plusminustwo = ' + ';
                $plusminusone = ' - ';
                $complex *= - 1;
            }
            $lambdaone = round( -$b / (2 * $a), $precision ) . $plusminusone . round( $complex, $precision ) . 'i';
            $lambdatwo = round( -$b / (2 * $a), $precision ) . $plusminustwo . round( $complex, $precision ) . 'i';
        } else if ( $bsmfac == 0 ) { // Simplifies if b^2 = 4ac (real roots).
            $lambdaone = round( -$b / (2 * $a), $precision );
            $lambdatwo = round( -$b / (2 * $a), $precision );
        } else { // Finds real roots when b^2 != 4ac.
            $lambdaone = (-$b + sqrt( $bsmfac )) / (2 * $a);
            $lambdaone = round( $lambdaone, $precision );
            $lambdatwo = (-$b - sqrt( $bsmfac )) / (2 * $a);
            $lambdatwo = round( $lambdatwo, $precision );
        }

        return [$lambdaone, $lambdatwo];
    }

    public function getClosest(float $find, array $stackArray, string $returnType = 'closest'): float
    {
        // returnTypes: closest, ceil, floor, linterp

        $returnValue = null;
        $returnKey = null;
        ksort($stackArray);
        $pointerArray = [];

        foreach ($stackArray as $key => $value) {
            array_push($pointerArray, ['k' => $key, 'v' => $value]);
        }
        $keys = array_keys($stackArray);
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
                        return $this->linterp(array_search($floor, $stackArray), $floor, array_search($ceil, $stackArray), $ceil, $find);
                    }
                    return $ceil;
            }
        }
        return $find;
    }
}
