<?php
/**
 * Copyright (c) 2018. Bence VÁNKOS
 * https://resist.hu
 */

namespace Ec;

class Ec extends \Prefab
{
    /** @var \Base */
    protected $f3; // Structure hive

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

    public function quick($value, $param1, $param2, $param3)
    {
        switch ($value) {
            case 'matList':
                if ($param1 != '' && $param2 != '' && $param3 != '') {
                    $this->matList($param1, $param2, $param3);
                } else {
                    $this->matList();
                }
                break;
        }
    }

    public function readData($dbName)
    {
        $this->f3->md->load(array('dname = :dname', ':dname' => $dbName));
        if (!$this->f3->md->dry()) {
            $this->f3->md->copyto('cdata');
            return json_decode($this->f3->md->djson, true);
        }
        return array('');
    }

    public function getMaterialArray()
    {
        return $this->readData('mat');
    }

    public function getBoltArray()
    {
        return $this->readData('bolt');
    }

    public function matProp($name, $property)
    {
        $matDb = $this->getMaterialArray();
        try {
            return $matDb[$name][$property];
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function boltProp($name, $property)
    {
        $boltDb = $this->getBoltArray();
        try {
            return $boltDb[$name][$property];
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function fy($matName, $t)
    {
        if ($t > 40) {
            \Blc::instance()->html('','Lemezvastagság miatt csökkentett szilárdság figyelembe véve:', '');
            return $this->matProp($matName, 'fy40');
        }
        return $this->matProp($matName, 'fy');
    }

    public function fu($matName, $t)
    {
        if ($t > 40) {
            \Blc::instance()->html('','Lemezvastagság miatt csökkentett szilárdság figyelembe véve:', '');
            return $this->matProp($matName, 'fu40');
        }
        return $this->matProp($matName, 'fu');
    }

    public function matList($variableName = 'mat', $default = 'S235', $title = 'Anyagminőség')
    {
        $blc = \Blc::instance();
        $matDb = $this->getMaterialArray();
        $keys =  array_keys($matDb);
        $list = [];
        foreach ($keys as $key) {
            $list[$key] = $key;
        }
        $blc->lst($variableName, $list, $title, $default, '');

        if (\Base::instance()->get('_'.$variableName) == 'Units') {
            $units = 'A számítás a továbbiakban az alapértelmezett értékkel fut le: *'.$default.'*
            
Jellemzők és mértékegységek: 
            
';
            foreach ($matDb['Units'] as $prop => $unit) {
                if ($prop != '' && $prop != 'name') {
                    if ($unit === 0 || $unit == '') {
                        $unit = '-';
                    }
                    $units .= '+ '.$prop.' ['.$unit.']
';
                }
            }
            $blc->md($units);
            \Base::instance()->set('_'.$variableName, $default);
        }
    }

    public function boltList($variableName = 'bolt', $default = 'M16', $title = 'Csavar betöltése')
    {
        $blc = \Blc::instance();
        $boltDb = $this->getBoltArray();
        $keys =  array_keys($boltDb);
        $list = [];
        foreach ($keys as $key) {
            $list[$key] = $key;
        }
        \Blc::instance()->lst($variableName, $list, $title, $default, '');

        if (\Base::instance()->get('_'.$variableName) == 'Units') {
            $units = 'A számítás a továbbiakban az alapértelmezett értékkel fut le: *'.$default.'*
            
Jellemzők és mértékegységek: 
            
';
            foreach ($boltDb['Units'] as $prop => $unit) {
                if ($prop != '' && $prop != 'name') {
                    if ($unit === 0 || $unit == '') {
                        $unit = '-';
                    }
                    $units .= '+ '.$prop.' ['.$unit.']
';
                }
            }
            $blc->txt($units);
        }
    }

    public function sectionFamilyList($variableName = 'sectionFamily', $title = 'Szelvény család', $default = 'HEA')
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
            'ROR' => 'ROR',
        ];
        \Blc::instance()->lst($variableName, $list, $title, $default, '');
    }

    public function sectionList($familyName = 'HEA', $variableName = 'sectionName', $title = 'Szelvény név', $default = 'HEA200')
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

    public function saveSectionData($sectionName, $renderTable = false, $arrayName = 'sectionData')
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

    public function saveMaterialData($matName, $prefix = '')
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
            return $e->getMessage();
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
                        \Blc::instance()->math('f_(ck) = '.$value.' [N/(mm^2)]', 'Nyomószilárdság karakterisztikus értéke (5% kvantilis) (`D150/300` henger)');
                        break;
                    case 'fckcube':
                        \Blc::instance()->math('f_(ck,cu\be) = '.$value.' [N/(mm^2)]', 'Nyomószilárdság karakterisztikus értéke (5% kvantilis) (`150/150/150` kocka)');
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
                        \Blc::instance()->boo('fbd07', '`` Rossz tapadás vagy 300 mm-nél magasabb gerendák felső vasa', 1, 'Csökkentés 70%-ra');
                        if ($this->f3->_fbd07) {
                            \Blc::instance()->def('fbd', $this->f3->get('_'.$prefix.'fbd')*0.7, 'f_(bd,eff) = f_(bd)*0.7 = %%');
                        }
                        break;
                    case 'fcd':
                        \Blc::instance()->math('f_(cd) = '.$value.' [N/(mm^2)]', 'Nyomószilárdság tervezési értéke');
                        \Blc::instance()->note('`f.cd = f.ck/gamma.c`');
                        break;
                    case 'fiinf28':
                        \Blc::instance()->math('phi(infty,28) = '.$value.'', 'Kúszási tényező átlagos végértéke. (Állandó/tartós terhelés, 70% páratartalom, 28 napos szilárdság terhelése, képlékeny konzisztencia betonozása, 100 mm egyenértékű lemezvastagság.)');
                        break;
                    case 'Eceff':
                        \Blc::instance()->math('E_(c,eff) = '.$value.' [(kN)/(mm^2)] = '.$value*100 .' [(kN)/(cm^2)]', 'Beton hatásos alakváltozási tényezője a kúszás végértékével');
                        \Blc::instance()->note('`E.c.eff = E.cm/(1+fi.inf.28)`');
                        break;
                    case 'alfat':
                        \Blc::instance()->math('alpha_t = '.$value.' [1/(C°)]', 'Hőtágulási együttható');
                        break;
                    case 'Epsiloncsinf':
                        \Blc::instance()->math('epsilon_(cs,infty) = '.$value.'', 'Beton zsugorodásának végértéke (kúszási tényezőnél adott feltételeknél)');
                        break;
                }
            }
        }
        \Blc::instance()->region1('materialData'.$prefix);
    }

    public function FtRd($btName, $btMat)
    {
        return (0.9 * $this->matProp($btMat, 'fu') * $this->boltProp($btName, 'As')) / (1000 * $this->f3->__GM2);
    }

    public function BpRd($btName, $stMat, $t)
    {
        return (0.6 * pi() * $this->boltProp($btName, 'dm') * $this->fu($stMat, $t) * $t) / (1000 * $this->f3->__GM2);
    }

    // Nyírt csavar
    public function FvRd($btName, $btMat, $n, $As = 0)
    {
        if ($As == 0) {
            $As = $this->boltProp($btName, 'As');
        }
        $result = (( $this->matProp($btMat, 'fu') * $As * 0.6) / (1000 * $this->f3->__GM2) )*$n;
        if ($btMat == '4.8' || $btMat == '5.8' || $btMat == '6.8' || $btMat == '10.9') {
            \Blc::instance()->txt('', $btMat.' csavar anyag miatt az eredmény 80%-ra csökkentve:');
            return $result*0.8;
        }
        return $result;
    }

    // Csavar palástnyomás
    public function FbRd($btName, $btMat, $stMat, $ep1, $ep2, $t, $inner)
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
        return $result;
    }

    // Acél keresztmetszet nyírási ellenállása: Av [mm2], t [mm], returns [kN]
    public function VplRd($Av, $matName, $t)
    {
        $fy = $this->fy($matName, $t);
        return ($Av*$fy)/(sqrt(3)*$this->f3->__GM0*1000);
    }

    // Acél keresztmetszet húzási ellenállása: A Anet [mm2], t [mm], returns [kN]
    public function NtRd($A, $Anet, $matName, $t)
    {
        return min($this->NuRd($Anet, $matName, $t), $this->NplRd($A, $matName, $t));
    }

    public function NuRd($Anet, $matName, $t)
    {
        $fu = $this->fu($matName, $t);
        return (0.9*$Anet*$fu)/($this->f3->__GM2*1000);
    }

    public function NplRd($A, $matName, $t)
    {
        $fy = $this->fy($matName, $t);
        return ($A*$fy)/($this->f3->__GM0*1000);
    }

    public function NcRd($A, $fy) {
        return ($A*$fy)/($this->f3->__GM0*1000);
    }

    public function McRd($W, $fy) {
        return ($W*$fy)/($this->f3->__GM0*1000000);
    }

    public function qpz($z, $terrainCat)
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
        return number_format((1 + 7*$Ivz)*0.5*(1.25/1000)*($vmz*$vmz), 3);
    }

    public static function qpzNSEN($z, $terrainCat, $cAlt, $c0z, $vb0)
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

    public function linterp($x1, $y1, $x2, $y2, $x)
    {
        return (($x - $x1)*($y2 - $y1)/($x2 - $x1)) + $y1;
    }

    public function A($D, $multiplicator = 1)
    {
        return $D*$D*pi()*0.25*$multiplicator;
    }

}
