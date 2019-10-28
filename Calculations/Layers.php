<?php

namespace Calculation;

/**
 * Dead load analysis according to Eurocodes - Calculation class for ECC framework
 *
 * (c) Bence VÁNKOS
 * https:// structure.hu
 */

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

        $blc->region0('t2', 'Szigetelőanyagok');
        $table = array(
            "Kőzetgyapot - lapostető" => array('$gamma_k [(kN)/m^3]$' => '1.4 - 1.5'),
            "Kőzetgyapot - magastető" => array('$gamma_k [(kN)/m^3]$' => '0.3 - 0.8'),
            "Kőzetgyapot - hang" => array('$gamma_k [(kN)/m^3]$' => '0.9 - 1.1'),
            "PVC" => array('$gamma_k [(kN)/m^3]$' => '17'),
            "EPS" => array('$gamma_k [(kN)/m^3]$' => '0.1 - 0.15'),
        );
        $blc->table($table, 'Szigetelőanyagok');
        $blc->region1('t2');

        $blc->region0('t1', 'Burkolóanyagok');
        $table = array(
            "Esztrich" => array('$gamma_k [(kN)/m^3]$' => 18),
        );
        $blc->table($table, 'Burkolóanyagok');
        $blc->region1('t1');

        $blc->region0('t0', 'Lindab trapézlemez önsúlyok');
        $table = array(
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
        $blc->table($table, 'Lindab trapézlemez');
        $blc->region1('t0');
    }
}
