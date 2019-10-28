<?php

declare(strict_types = 0);

namespace Ec;

/**
 * Eurocode globals and predefined GUI elements for ECC framework
 *
 * (c) Bence VÁNKOS
 * https:// structure.hu
 */

class Ec extends \Prefab
{
    /** @var \Base */
    protected $f3; // Structure hive

    /**
     * Ec constructor.
     * Defines Eurocode parameters in hive: __GG, __GQ, __GM0, __GM2, __GM3, __GM3ser, __GM6ser, __Gc, __Gs, __GS, __GcA, __GSA
     */
    public function __construct()
    {
        $this->f3 = \Base::instance();
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
        $this->f3->md->load(['dname = :dname', ':dname' => $dbName]);
        if (!$this->f3->md->dry()) {
            return json_decode($this->f3->md->djson, true);
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

    /**
     * Get all bolt data as array from database
     * @return array Assoc. array of read bolt data
     */
    public function getBoltArray(): array
    {
        return $this->readData('bolt');
    }

    public function matProp(string $name, string $property): float
    {
        $matDb = $this->getMaterialArray();
        try {
            return $matDb[$name][$property];
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function boltProp(string $name, string $property): float
    {
        $boltDb = $this->getBoltArray();
        try {
            return $boltDb[$name][$property];
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function fy(string $matName, float $t): float
    {
        if ($t > 40) {
            \Blc::instance()->html('','Lemezvastagság miatt csökkentett szilárdság figyelembe véve:', '');
            return $this->matProp($matName, 'fy40');
        }
        return $this->matProp($matName, 'fy');
    }

    public function fu(string $matName, float $t): float
    {
        if ($t > 40) {
            \Blc::instance()->html('','Lemezvastagság miatt csökkentett szilárdság figyelembe véve:', '');
            return $this->matProp($matName, 'fu40');
        }
        return $this->matProp($matName, 'fu');
    }

    public function matList(string $variableName = 'mat', string $default = 'S235', $title = ['', 'Anyagminőség'], $category = false): void
    {
        $blc = \Blc::instance();
        $matDb = $this->getMaterialArray();

        if ($category != false) {
            foreach ($matDb as $key => $value) {
                switch ($category) {
                    case 'bolt':
//                        \H3::dump($value['name']);
                        if ($value['0'] != 'bolt') {
                            unset($matDb[$key]);
                        }
                        break;
                    case 'rebar':
                        if ($value['0'] != 'rebar') {
                            unset($matDb[$key]);
                        }
                        break;
                    case 'concrete':
                        if ($value['0'] != 'concrete') {
                            unset($matDb[$key]);
                        }
                        break;
                    case 'steel':
                        if ($value['0'] != 'steel') {
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
        $boltDb = $this->getBoltArray();
        $keys =  array_keys($boltDb);
        $list = [];
        foreach ($keys as $key) {
            $list[$key] = $key;
        }
        \Blc::instance()->lst($variableName, $list, $title, $default, '');
    }

    public function wrapNumerics($variableNameA, $variableNameB, $titleArrayAB, $defaultValueA, $defaultValueB, $unitAB = false, $helpAB = false, $middleText = false)
    {
        $blc = \Blc::instance();

        $blc->wrapper0($titleArrayAB);
            $blc->numeric($variableNameA, false, $defaultValueA, $unitAB, false);
            $blc->wrapper1($middleText);
            $blc->numeric($variableNameB, false, $defaultValueB, $unitAB, false);
        $blc->wrapper2($helpAB);
    }

    public function wrapRebarCount($variableNameCount, $variableNameRebar, $titleArray, $defaultValueCount, $defaultValueRebar = 16, $help = false)
    {
        $blc = \Blc::instance();

        $blc->wrapper0($titleArray);
            $blc->numeric($variableNameCount, false, $defaultValueCount, false, false);
            $blc->wrapper1(false);
            $this->rebarList($variableNameRebar, $defaultValueRebar, false, false);
        $blc->wrapper2($help);
    }

    public function rebarList(string $variableName = 'fi', int $default = 16, $title = [false, 'Vasátmérő'], string $help = ''): void
    {
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
        \Blc::instance()->lst($variableName, $source, $title, $default, $help);
    }

    public function rebarTable(string $variableNameBulk = 'As'): float
    {
        $blc = \Blc::instance();
        $f3 = \Base::instance();
        $variableNameBulk = \V3::varname($variableNameBulk);
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
        $blc->bulk($variableNameBulk.'Bulk', $fields);
        $As = 0;
        if (isset($f3->sum)) {
            foreach ($f3->sum as $key => $value) {
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
        \Blc::instance()->lst($variableName, $list, $title, $default, '');
    }

    public function sectionList(string $familyName = 'HEA', string $variableName = 'sectionName', string $title = 'Szelvény név', string $default = 'HEA200'): void
    {
        $familyName = \V3::alphanumeric($familyName);
        $query = "SELECT * FROM steelSection WHERE name2 LIKE '$familyName%'";
        $result = $this->f3->db->exec($query);
        $list = [];
        foreach ($result as $section) {
            $list[$section['name2']] = $section['name2'];
        }
        \Blc::instance()->lst($variableName, $list, $title, $default, '');
    }

    public function saveSectionData(string $sectionName, bool $renderTable = false, string $arrayName = 'sectionData'): void
    {
        $sectionName = $this->f3->clean($sectionName);

        $query = "SELECT * FROM steelSection WHERE name2 = '$sectionName' LIMIT 1";
        $result = $this->f3->db->exec($query);
        // Remove underscores
        foreach ($result[0] as $key => $value) {
            if (substr($key, 0,1) == '_') {
                $resultCleaned[substr($key, 1,10)] = $result[0][$key];
            }
        }
        $this->f3->set('_'.$arrayName, $resultCleaned);

        if ($renderTable) {
            $row = [];
            foreach ($result[0] as $key => $value) {
                if (substr($key, 0,1) == '_') {
                    $row[substr($key, 1,10)] = $value;
                }
            }
            // Update self-weight
            $row['G'] = number_format((($row['Ax'])/10000)*7850, 2, '.', ' ');
            $table = [$result[0]['name2'] => $row];

            \Blc::instance()->region0('sectionTable', $result[0]['name2'].' szelvény adatok');
                \Blc::instance()->table($table,'Szelvényadatok ', 'Mértékegységek, további információk: <a href="https://structure.hu/profil">structure.hu/profil</a>');
            \Blc::instance()->region1('sectionTable');
        }
    }

    public function saveMaterialData(string $matName, string $prefix = ''): void
    {
        // Saves all material properties from DB to hive variables, with prefix. e.g.: prefixfck, prefixfy etc
        $matName = $this->f3->clean($matName);
        if ($prefix != '' && $prefix != false) {
            $prefix = \V3::alphanumeric($prefix);
        } else {
            $prefix = '';
        }
        $matDb = $this->getMaterialArray();

        try {
            $matData = $matDb[$matName];
        } catch (Exception $e) {
            throw new \Exception($e->getMessage());
        }

        \Blc::instance()->region0('materialData'.$prefix, 'Anyagjellemzők');
        foreach ($matData as $key => $value) {
            if ($value != 0 && $value != '' && $key != '0') {
                $this->f3->set('_' . $prefix . $key, $value);

                switch($key) {
                    case 'fy':
                        \Blc::instance()->math('f_y = '.$value.' [N/(mm^2)]', 'Folyáshatár');
                        break;
                    case 'fu':
                        \Blc::instance()->math('f_u = '.$value.' [N/(mm^2)]', 'Szakító szilárdság');
                        break;
                    case 'fy40':
                        \Blc::instance()->math('f_(y,40) = '.$value.' [N/(mm^2)]', 'Folyáshatár 40+ mm lemez esetén');
                        break;
                    case 'fu40':
                        \Blc::instance()->math('f_(u,40) = '.$value.' [N/(mm^2)]', 'Szakító szilárdság 40+ mm lemez esetén');
                        break;
                    case 'betaw':
                        \Blc::instance()->math('beta_w = '.$value.'', 'Hegesztési tényező');
                        break;
                    case 'fyd':
                        \Blc::instance()->math('f_(yd) = '.$value.' [N/(mm^2)]', 'Folyáshatár tervezési értéke');
                        break;
                    case 'fck':
                        \Blc::instance()->math('f_(ck) = '.$value.'  [N/(mm^2)]', 'Nyomószilárdság karakterisztikus értéke (5% kvantilis) ($phi$ 150×300 henger)');
                        break;
                    case 'fckcube':
                        \Blc::instance()->math('f_(ck,cube) = '.$value.' [N/(mm^2)]', 'Nyomószilárdság karakterisztikus értéke (5% kvantilis) (□150×150×150 kocka)');
                        break;
                    case 'fcm':
                        \Blc::instance()->math('f_(cm) = '.$value.' [N/(mm^2)]', 'Nyomószilárdság várható értéke');
                        \Blc::instance()->note('`f.cm = f.ck + 8`');
                        break;
                    case 'fctm':
                        \Blc::instance()->math('f_(ctm) = '.$value.' [N/(mm^2)]', 'Húzószilárdság várható értéke');
                        break;
                    case 'fctd':
                        \Blc::instance()->math('f_(ctd) = '.$value.' [N/(mm^2)]', 'Húzószilárdság tervezési értéke');
                        break;
                    case 'fctk005':
                        \Blc::instance()->math('f_(ctk,0.05) = '.$value.' [N/(mm^2)]', 'Húzószilárdság karakterisztikus értéke (5% kvantilis)');
                        break;
                    case 'fctk095':
                        \Blc::instance()->math('f_(ctk,0.95) = '.$value.' [N/(mm^2)]', 'Húzószilárdság karakterisztikus értéke (95% kvantilis)');
                        break;
                    case 'Ecm':
                        \Blc::instance()->math('E_(cm)= '.$value.' [(kN)/(mm^2)]', 'Beton rugalmassági modulusa');
                        \Blc::instance()->note('`E.cm = 22(f.cm/10)^0.3`');
                        \Blc::instance()->note('Húrmodulus `sigma.c = 0` és `sigma.c = 0.4f.cm` között.');
                        break;
                    case 'Ecu3':
                        \Blc::instance()->math('E_(cu3)= '.$value.' []', '');
                        break;
                    case 'Euk':
                        \Blc::instance()->math('E_(uk)= '.$value.' []', '');
                        break;
                    case 'Es':
                        \Blc::instance()->math('E_s= '.$value.' [(kN)/(mm^2)]', 'Betonacél rugalmassági modulusa');
                        break;
                    case 'Fc0':
                        \Blc::instance()->math('F_(c0)= '.$value.' []', '');
                        break;
                    case 'F_c0':
                        \Blc::instance()->math('F_(_c0)= '.$value.' []', '');
                        break;
                    case 'fbd':
                        \Blc::instance()->math('f_(bd)= '.$value.' [N/(mm^2)]', 'Beton és acél közti kapcsolati szilárdság bordás betonacéloknál, jó tapadás esetén');
                        \Blc::instance()->boo($prefix.'fbd07', 'Rossz tapadás vagy 300 mm-nél magasabb gerendák felső vasa', 1, 'Csökkentés 70%-ra');
                        if ($this->f3->get('_'.$prefix.'fbd07')) {
                            \Blc::instance()->def($prefix.'fbd', $this->f3->get('_'.$prefix.'fbd')*0.7, 'f_(bd) = f_(bd,eff) = f_(bd)*0.7 = %%');
                        }
                        break;
                    case 'fcd':
                        \Blc::instance()->math('f_(cd) = '.$value.' [N/(mm^2)]', 'Nyomószilárdság tervezési értéke');
                        \Blc::instance()->note('`f.cd = f.ck/gamma.c`');
                        break;
                    case 'fiinf28':
                        \Blc::instance()->math('phi(infty,28) = '.$value.'', 'Kúszási tényező átlagos végértéke. (Állandó/tartós terhelés, 70% párat., 28 n. szil. terhelése, képlékeny konzisztencia betonozása, 100 mm egyenértékű lemezvast.)');
                        break;
                    case 'Eceff':
                        \Blc::instance()->math('E_(c,eff) = '.$value.' [(kN)/(mm^2)] = '.$value*100 .' [(kN)/(cm^2)]', 'Beton hatásos alakváltozási tényezője a kúszás végértékével');
                        \Blc::instance()->note('`E.c.eff = E.cm/(1+fi.inf.28)`');
                        break;
                    case 'alfat':
                        \Blc::instance()->math('alpha_t = '.$value.' [1/K]', 'Hőtágulási együttható');
                        break;
                    case 'Epsiloncsinf':
                        \Blc::instance()->math('epsilon_(cs,infty) = '.$value.'', 'Beton zsugorodásának végértéke (kúszási tényezőnél adott feltételeknél)');
                        break;
                }
            }
        }
        \Blc::instance()->region1('materialData'.$prefix);
    }

    public function FtRd(string $btName, string $btMat, bool $verbose = true): float
    {
        $result = (0.9 * $this->matProp($btMat, 'fu') * $this->boltProp($btName, 'As')) / (1000 * $this->f3->__GM2);
        if ($verbose) {
            \Blc::instance()->note('`FtRd` húzás általános képlet: $(0.9*f_(u,b)*A_s)/(gamma_(M2))$');
        }
        return $result;
    }

    public function BpRd(string $btName, string $stMat, float $t): float
    {
        \Blc::instance()->note('`BpRd` kigombolódás általános képlet: $(0.6* pi *d_m*f_(u,s)*t)/(gamma_(M2))$');
        return (0.6 * pi() * $this->boltProp($btName, 'dm') * $this->fu($stMat, $t) * $t) / (1000 * $this->f3->__GM2);
    }

    // Nyírt csavar
    public function FvRd(string $btName, string $btMat, int $n, float $As = 0): float
    {
        if ($As == 0) {
            $As = $this->boltProp($btName, 'As');
        }
        $result = (( $this->matProp($btMat, 'fu') * $As * 0.6) / (1000 * $this->f3->__GM2) )*$n;
        \Blc::instance()->note('$F_(v,Rd)$ nyírás általános képlet: $n*(0.6*f_(u,b)*A_s)/(gamma_(M2))$');

        if ($btMat == '4.8' || $btMat == '5.8' || $btMat == '6.8' || $btMat == '10.9') {
            \Blc::instance()->note('$F_(v,Rd)$ nyírás: '.$btMat.' csavar anyag miatt az eredmény 80%-ra csökkentve.');
            return $result*0.8;
        }
        return $result;
    }

    // Csavar palástnyomás
    public function FbRd(string $btName, string $btMat, string $stMat, float $ep1, float $ep2, float $t, bool $inner): float
    {
        $fust = $this->fu($stMat, $t);
        $k1 = min(2.8*($ep2/ $this->boltProp($btName, 'd0')) - 1.7, 2.5);
        $alphab = min(($ep1/ (3* $this->boltProp($btName, 'd0'))), $this->matProp($btMat, 'fu')/ $fust, 1);
        if ($inner) {
            $k1 = min(1.4*($ep2/ $this->boltProp($btName, 'd0')) - 1.7, 2.5);
            $alphab = min(($ep1/ (3* $this->boltProp($btName, 'd0'))) - 0.25, $this->matProp($btMat, 'fu')/ $this->fu($stMat, $t), 1);
        }
        $result = $k1*(($alphab*$fust* $this->boltProp($btName, 'd') * $t)/(1000 * $this->f3->__GM2));
        $this->f3->set('___k1', $k1);
        $this->f3->set('___alphab', $alphab);
        if ($result <= 0) {
            return 0;
        }
        \Blc::instance()->note('$F_(b,Rd)$ palástnyomás általános képlet: $k_1*(alpha_b*f_(u,s)*d*t)/(gamma_(M2))$');
        return $result;
    }

    // Acél keresztmetszet nyírási ellenállása: Av [mm2], t [mm], returns [kN]
    public function VplRd(float $Av, string $matName, float $t): float
    {
        $fy = $this->fy($matName, $t);
        return ($Av*$fy)/(sqrt(3)*$this->f3->__GM0*1000);
    }

    // Acél keresztmetszet húzási ellenállása: A Anet [mm2], t [mm], returns [kN]
    public function NtRd(float $A, float $Anet, string $matName, float $t): float
    {
        return min($this->NuRd($Anet, $matName, $t), $this->NplRd($A, $matName, $t));
    }

    public function NuRd(float $Anet, string $matName, float $t): float
    {
        $fu = $this->fu($matName, $t);
        return (0.9*$Anet*$fu)/($this->f3->__GM2*1000);
    }

    public function NplRd(float $A, string $matName, float $t): float
    {
        $fy = $this->fy($matName, $t);
        return ($A*$fy)/($this->f3->__GM0*1000);
    }

    public function NcRd(float $A, float $fy): float
    {
        return ($A*$fy)/($this->f3->__GM0*1000);
    }

    public function McRd(float $W, float $fy): float
    {
        return ($W*$fy)/($this->f3->__GM0*1000000);
    }

    public function qpz(float $z, int $terrainCat): float
    {
        $terrainDb = array(
            '1' => array(
                'z0' => 0.01,
                'zmin' => 1.0,
                'kr' => 0.170,
            ),
            '2' => array(
                'z0' => 0.05,
                'zmin' => 2.0,
                'kr' => 0.190,
            ),
            '3' => array(
                'z0' => 0.3,
                'zmin' => 5.0,
                'kr' => 0.215,
            ),
            '4' => array(
                'z0' => 1.0,
                'zmin' => 10.0,
                'kr' => 0.234,
            )
        );
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
        $qpz = number_format((1 + 7*$Ivz)*0.5*(1.25/1000)*($vmz*$vmz), 3);

        $blc = \Blc::instance();
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

    public static function qpzNSEN(float $z, int $terrainCat, float $cAlt, float $c0z, float $vb0): float
    {
        $terrainDb = array(
            '0' => array(
                'z0' => 0.003,
                'zmin' => 2.0,
                'kr' => 0.160,
            ),
            '1' => array(
                'z0' => 0.01,
                'zmin' => 2.0,
                'kr' => 0.170,
            ),
            '2' => array(
                'z0' => 0.05,
                'zmin' => 4.0,
                'kr' => 0.190,
            ),
            '3' => array(
                'z0' => 0.3,
                'zmin' => 8.0,
                'kr' => 0.220,
            ),
            '4' => array(
                'z0' => 1.0,
                'zmin' => 16.0,
                'kr' => 0.240,
            )
        );
        $cDir = 1.0;
        $cSeason = 1.0;
        $cProb = 1.0;
        $vb = $vb0*$cDir*$cSeason*$cAlt*$cProb;
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
        return number_format((1 + 7*$Ivz)*0.5*(1.25/1000)*($vmz*$vmz), 3);
    }

    public function linterp(float $x1, float $y1, float $x2, float $y2, float $x): float
    {
        return (($x - $x1)*($y2 - $y1)/($x2 - $x1)) + $y1;
    }

    public function A(float $D, int $multiplicator = 1): float
    {
        return $D*$D*pi()*0.25*$multiplicator;
    }

    // Original: https://github.com/hellofromtonya/Quadratic/blob/master/solver.php
    public function quadratic( $a, $b, $c, $root = 'both', $precision = 3 ) {
        $bsmfac = $b * $b - 4 * $a * $c;
        if ( $bsmfac < 0 ) { // Accounts for complex roots.
            $plusminusone = ' + ';
            $plusminustwo = ' - ';
            $bsmfac *= - 1;
            $complex = (sqrt( $bsmfac ) / (2 * $a));
            if ( $a < 0 ) { //if negative imaginary term, tidies appearance.
                $plusminustwo = ' + ';
                $plusminusone = ' - ';
                $complex *= - 1;
            } // End if ($a < 0)
            $lambdaone = round( -$b / (2 * $a), $precision ) . $plusminusone . round( $complex, $precision ) . 'i';
            $lambdatwo = round( -$b / (2 * $a), $precision ) . $plusminustwo . round( $complex, $precision ) . 'i';
        } // End if ($bsmfac < 0)
        else if ( $bsmfac == 0 ) { // Simplifies if b^2 = 4ac (real roots).
            $lambdaone = round( -$b / (2 * $a), $precision );
            $lambdatwo = round( -$b / (2 * $a), $precision );
        } // End else if (bsmfac == 0)
        else { // Finds real roots when b^2 != 4ac.
            $lambdaone = (-$b + sqrt( $bsmfac )) / (2 * $a);
            $lambdaone = round( $lambdaone, $precision );
            $lambdatwo = (-$b - sqrt( $bsmfac )) / (2 * $a);
            $lambdatwo = round( $lambdatwo, $precision );
        } // End else
        // Return what is asked for.
        if ( 'root1' == $root ) {
            return $lambdaone;
        }
        if ( 'root2' == $root ) {
            return $lambdatwo;
        }
        if ( 'both' == $root ) {
            return $lambdaone . ' and ' . $lambdatwo;
        }
    }

    public function getClosest(float $find, $stackArray, string $returnType = 'closest')
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
                        $returnValue = $value;
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
                        $returnValue = $value;
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
                        $returnValue = $value;
                    }
                    break;
                case 'linterp':
                    if ($key == $find) {
                        return $value;
                    }
                    $floor = $this->getClosest($find, $stackArray, 'floor');
                    $ceil = $this->getClosest($find, $stackArray, 'ceil');
                    if ($floor != $ceil) {
                        return $this->linterp(array_search($floor, $stackArray), $floor, array_search($ceil, $stackArray), $ceil, $find);
                    }
                    return $ceil;
                    break;
            }
        }
        return $returnValue;
    }
}
