<?php declare(strict_types = 1);
/**
 * Dead load analysis according to Eurocodes - Calculation class for Statika framework
 * (c) Bence VÁNKOS | https://structure.hu | https://github.com/r3sist/ecc-calculations
 */

namespace Statika\Calculations;

use Statika\EurocodeInterface;

Class Layers
{
    /**
     * @param \Statika\Ec $ec
     */
    public function calc(EurocodeInterface $ec): void
    {
        $ec->note('Ha a réteghez nincs felületi súly megadva, vastagságból és térfogatsúlyból számol, egyébként a felületi súly a mértékadó.');

        $bulkName = 'layers';

        if (isset($_POST[$bulkName])) {
            foreach ($_POST[$bulkName] as $key => $value) {
                if (isset($value['p']) && is_numeric($value['p']) && $value['p'] > 0) {
                    $_POST[$bulkName][$key]['pcalc'] = (float)$value['p'];
                } else if (isset($value['v']) && is_numeric($value['v']) && is_numeric($value['q'])) {
                    $_POST[$bulkName][$key]['pcalc'] = (float)$value['v']*(float)$value['q']/100;
                } else {
                    $_POST[$bulkName][$key]['pcalc'] = 0;
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

        $ec->bulk($bulkName, $fields, ['PVC Vízszigetelés', 0.2, 17, 0, 0.034], true);

        $ec->region0('t2', 'Szigetelőanyagok');
            $scheme = ['Szigetelő anyagok', '$gamma_k [(kN)/m^3]$'];
            $rows = [
                ['EPS', '0.1 - 0.15'],
                ['Kőzetgyapot - lapostető', '1.4 - 1.5'],
                ['Kőzetgyapot - magastető', '0.3 - 0.8'],
                ['Kőzetgyapot - hang', '0.9 - 1.1'],
                ['PVC', 17],
                ['XPS hab általános', 0.4],
                ['XPS hab padló', 0.6],
                ['XPS hab homlokzat', '0.5 - 0.9'],
            ];
            $ec->tbl($scheme, $rows);
        $ec->region1();

        $ec->region0('t1', 'Burkolóanyagok');
            $scheme = ['Burkolóanyagok', '$gamma_k [(kN)/m^3]$'];
            $rows = [
                ['Esztrich', 18],
                ['Greslap', 24],
                ['Csempe', 17],
            ];
            $ec->tbl($scheme, $rows);
        $ec->region1();

        $ec->region0('t0', 'Lindab trapézlemez önsúlyok');
            $scheme = ['Trapézlemez', '$gamma_k [(kN)/m^3]$'];
            $rows = [
                ["LTP20×0.4",  0.032],
                ["LTP20×0.5", 0.041],
                ["LTP20×0.6", 0.050],
                ["LTP20×0.7", 0.059],

                ["LTP45×0.5", 0.045],
                ["LTP45×0.6", 0.054],
                ["LTP45×0.7", 0.064],

                ["LTP85×0.75", 0.072],
                ["LTP85×0.88", 0.086],
                ["LTP85×1.00", 0.098],
                ["LTP85×1.13", 0.111],
                ["LTP85×1.25", 0.123],
                ["LTP85×1.50<!--success-->", '<!--success-->0.149'],

                ["LTP100×0.75", 0.081],
                ["LTP100×0.88", 0.095],
                ["LTP100×1.00", 0.109],
                ["LTP100×1.13", 0.124],
                ["LTP100×1.25", 0.137],
                ["LTP100×1.50", 0.166],

                ["LTP135×0.75", 0.088],
                ["LTP135×0.88", 0.104],
                ["LTP135×1.00", 0.118],
                ["LTP135×1.13", 0.134],
                ["LTP135×1.25", 0.149],
                ["LTP135×1.50", 0.180],

                ["LTP150×0.75", 0.097],
                ["LTP150×0.88", 0.114],
                ["LTP150×1.00", 0.131],
                ["LTP150×1.25", 0.165],
                ["LTP150×1.50<!--success-->", '<!--success-->0.199'],
            ];
            $ec->tbl($scheme, $rows);
        $ec->region1();
    }
}
