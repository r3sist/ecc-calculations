<?php

namespace Calculation;

Class Layers extends \Ecc
{

    /**
     * @var $f3 \Base
     * @var $blc \Blc
     * @var $ec \Ec\Ec
     * @throws \Exception
     */
    public function calc(object $f3, object $blc, object $ec): void
    {
        $blc->note('Ha a réteghez nincs felületi súly megadva, vastagságból és térfogatsúlyból számol, egyébként a felületi súly a mértékadó.');
        $bulkName = 'layers';
        if ($f3->exists('POST._'.$bulkName)) {
            foreach ($f3->get('POST._'.$bulkName) as $key => $value) {
                if (is_numeric($value['p']) && $value['p'] > 0) {
                    $f3->set("POST._$bulkName.$key.pcalc", \V3::numeric($value['p']));
                } else if (is_numeric($value['v']) && is_numeric($value['q'])) {
                    $f3->set("POST._$bulkName.$key.pcalc", \V3::numeric($value['v'])*\V3::numeric($value['q'])/100);
                } else {
                    $f3->set("POST._$bulkName.$key.pcalc", 0);
                }
            }
        }

        $fields = [
            ['name' => 'n', 'title' => 'Réteg neve', 'type' => 'input'],
            ['name' => 'v', 'title' => 'Vastagság [cm]', 'type' => 'input', 'sum' => true],
            ['name' => 'q', 'title' => 'Térfogatsúly [kN/m3]', 'type' => 'input'],
            ['name' => 'p', 'title' => 'Felület súly [kN/m2]', 'type' => 'input'],
            ['name' => 'pcalc', 'title' => 'Számított teher [kN/m2]', 'type' => 'value', 'key' => 'pcalc', 'sum' => true],
        ];
        $blc->bulk($bulkName, $fields);

//        $blc->region0('r0', 'Rétegek megadása');
//
//        $blc->info0('i0');
//        $blc->input('cnt','Beviteli mezők száma','2','','');
//        $blc->info1('i0');
//
//        $i = 1;
//        while($i <= $f3->_cnt){
//            $blc->txt($i.'. réteg');
//            $blc->input('n'.$i,'`n_'.$i.'`: Réteg neve','','','');
//            $blc->input('v'.$i,'`v_'.$i.'`: Vastagság','','cm','');
//            $blc->input('q'.$i,'`q_'.$i.'`: Térfogatsúly','','kN/m³','');
//            $blc->input('p'.$i,'`p_'.$i.'`: Felület súly','','kN/m²','');
//            $blc->hr();
//            $i++;
//        }
//        $blc->region1('r0');
//
//        $table = array();
//        $i = 1;
//        $p = 0;
//        while($i <= $f3->_cnt){
//            if ($f3->get('_n'.$i)) {
//                if (!$f3->get('_p'.$i)) {
//                    $table[$f3->get('_n'.$i)]['Vastagság `[cm]`'] = '`'.$f3->get('_v'.$i).'`';
//                    $table[$f3->get('_n'.$i)]['Térfogatsúly `[(kN)/m^3]`'] = '`'.$f3->get('_q'.$i).'`';
//                    $p0 = number_format($f3->get('_q'.$i)*($f3->get('_v'.$i)/100), 3);
//                    $p = $p + $p0;
//                    $table[$f3->get('_n'.$i)]['Felület súly `[(kN)/m^2]`'] = '`= '.$p0.'`';
//                } else {
//                    $table[$f3->get('_n'.$i)]['Vastagság `[cm]`'] = '';
//                    $table[$f3->get('_n'.$i)]['Térfogatsúly `[(kN)/m^3]`'] = '';
//                    $table[$f3->get('_n'.$i)]['Felület súly `[(kN)/m^2]`'] = '`'.$f3->get('_p'.$i).'`';
//                    $p = $p + $f3->get('_p'.$i);
//                }
//            }
//            $i++;
//        }
//        $table['Összesen<!--success-->']['Vastagság `[cm]`'] = '';
//        $table['Összesen<!--success-->']['Térfogatsúly `[(kN)/m^3]`'] = '';
//        $table['Összesen<!--success-->']['Felület súly `[(kN)/m^2]`'] = '`'.$p.'`';
//
//       $blc->table($table,'Réteg');

        $blc->region0('t0', 'Lindab trapézlemez önsúlyok');
        $table0 = array(
            "LTP20×0.4" => array("Önsúly [kN/m&sup2;]" => 0.032),
            "LTP20×0.5" => array("Önsúly [kN/m&sup2;]" =>0.041),
            "LTP20×0.6" => array("Önsúly [kN/m&sup2;]" =>0.050),
            "LTP20×0.7" => array("Önsúly [kN/m&sup2;]" =>0.059),

            "LTP45×0.5" => array("Önsúly [kN/m&sup2;]" =>0.045),
            "LTP45×0.6" => array("Önsúly [kN/m&sup2;]" =>0.054),
            "LTP45×0.7" => array("Önsúly [kN/m&sup2;]" =>0.064),

            "LTP85×0.75" => array("Önsúly [kN/m&sup2;]" =>0.072),
            "LTP85×0.88" => array("Önsúly [kN/m&sup2;]" =>0.086),
            "LTP85×1.00" => array("Önsúly [kN/m&sup2;]" =>0.098),
            "LTP85×1.13" => array("Önsúly [kN/m&sup2;]" =>0.111),
            "LTP85×1.25" => array("Önsúly [kN/m&sup2;]" =>0.123),
            "LTP85×1.50<!--info-->" => array("Önsúly [kN/m&sup2;]" =>0.149),

            "LTP100×0.75" => array("Önsúly [kN/m&sup2;]" =>0.081),
            "LTP100×0.88" => array("Önsúly [kN/m&sup2;]" =>0.095),
            "LTP100×1.00" => array("Önsúly [kN/m&sup2;]" =>0.109),
            "LTP100×1.13" => array("Önsúly [kN/m&sup2;]" =>0.124),
            "LTP100×1.25" => array("Önsúly [kN/m&sup2;]" =>0.137),
            "LTP100×1.50" => array("Önsúly [kN/m&sup2;]" =>0.166),

            "LTP135×0.75" => array("Önsúly [kN/m&sup2;]" =>0.088),
            "LTP135×0.88" => array("Önsúly [kN/m&sup2;]" =>0.104),
            "LTP135×1.00" => array("Önsúly [kN/m&sup2;]" =>0.118),
            "LTP135×1.13" => array("Önsúly [kN/m&sup2;]" =>0.134),
            "LTP135×1.25" => array("Önsúly [kN/m&sup2;]" =>0.149),
            "LTP135×1.50" => array("Önsúly [kN/m&sup2;]" =>0.180),

            "LTP150×0.75" => array("Önsúly [kN/m&sup2;]" =>0.097),
            "LTP150×0.88" => array("Önsúly [kN/m&sup2;]" =>0.114),
            "LTP150×1.00" => array("Önsúly [kN/m&sup2;]" =>0.131),
            "LTP150×1.25" => array("Önsúly [kN/m&sup2;]" =>0.165),
            "LTP150×1.50<!--info-->" => array("Önsúly [kN/m&sup2;]" =>0.199),
        );
        $blc->table($table0, 'Lindab trapézlemez');
        $blc->region1('t0');
    }
}
