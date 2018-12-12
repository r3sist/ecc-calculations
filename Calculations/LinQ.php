<?php

namespace Calculation;

Class LinQ extends \Ecc
{

    /**
     * @var $f3 \Base
     * @var $blc \Blc
     * @var $ec \Ec\Ec
     * @throws \Exception
     */
    public function calc(object $f3, object $blc, object $ec): void
    {
        $blc->note('Számítás apropója, hogy gerenda irányra merőlegesen, egyenletesen változó megoszló felületi teher (pl. hózug teher) szétosztást a Consteel nem tud kezelni. A felületi teher vonalmenti teherré alakítható a lenti számítás alapján.');
        $blc->input('x', '`x"[ ]":` Gerenda koordináták', '2,6,10,14', 'm', 'Gerenda `x_i` pozíciói (koordinátái), vesszővel elválasztva, 0-tól számítva.');
        $blc->input('xMAX', '`x_(max):` Teher felület hossza', '16', 'm', '');
        $blc->input('qMAX', '`q_(max):` Megoszló teher (növekvő) maximális értéke', '10', 'kN/m²', '');
        $blc->input('qMIN', '`x_(min):` Megoszló teher minimális értéke', '2', 'kN/m²', '');

        $f3->set('_qd',$f3->_qMAX - $f3->_qMIN);
        $f3->set('_q1',$f3->_qd / $f3->_xMAX);

        $x = explode(',', $f3->_x);
        if (count($x) < 2) {
            $blc->danger('Minimum 2 gerendára kell szétosztani a terhet!', 'Hiba!');
        }
        $table = array();
        $i = 0;
        foreach ($x as $xAct) {
            if (isset($x[$i + 1]) && $x[$i + 1] <= $x[$i]) {
                $blc->danger('Hibás koordináta sorrend vagy fedő gerendák.', 'Hiba!');
            }
            if ($i == 0) {
                $table[$xAct]['b [m]'] = \H3::n2(($x[$i + 1] - $x[$i])/2 + $x[$i]);
                $table[$xAct]['p [kN/m]'] = \H3::n2((((($x[$i + 1] - $x[$i])/2 + $x[$i])*$f3->_q1)/2 + $f3->_qMIN)*$table[$xAct]['b [m]']);
            } elseif ($i == count($x) - 1) {
                $table[$xAct]['b [m]'] = \H3::n2(($x[$i] - $x[$i - 1])/2 + $f3->_xMAX - $x[$i]);
                $table[$xAct]['p [kN/m]'] = \H3::n2((((($x[$i] - ($x[$i] - $x[$i - 1])/2 + $f3->_xMAX))*$f3->_q1)/2 + $f3->_qMIN)*$table[$xAct]['b [m]']);
            } else {
                $table[$xAct]['b [m]'] = \H3::n2(($x[$i + 1] - $x[$i])/2 + ($x[$i] - $x[$i - 1])/2);
                $table[$xAct]['p [kN/m]'] = \H3::n2((((( $x[$i] - (($x[$i] - $x[$i - 1])/2) + $x[$i] + (($x[$i + 1] - $x[$i])/2) ))*$f3->_q1)/2 + $f3->_qMIN)*$table[$xAct]['b [m]']);
            }
            $i++;
        }
        $blc->table($table, 'Gerenda pozíció: x [m]');

        $write = array(
            array(
                'size' => 14,
                'x' => 40,
                'y' => 140,
                'text' => $f3->_qMIN.'kN/m²'
            ),
            array(
                'size' => 14,
                'x' => 680,
                'y' => 30,
                'text' => $f3->_qMAX.'kN/m²'
            ),
            array(
                'size' => 14,
                'x' => 630,
                'y' => 310,
                'text' => $f3->_xMAX.'m'
            ),
            array(
                'size' => 14,
                'x' => 100,
                'y' => 310,
                'text' => '0m'
            ),
            array(
                'size' => 14,
                'x' => 160,
                'y' => 310,
                'text' => $x[0].'m'
            ),
            array(
                'size' => 14,
                'x' => 270,
                'y' => 310,
                'text' => $x[1].'m   ...'
            ),
            array(
                'size' => 14,
                'x' => 120,
                'y' => 215,
                'text' => $table[$x[0]]['p [kN/m]'].'kN/m'
            ),
            array(
                'size' => 14,
                'x' => 260,
                'y' => 215,
                'text' => $table[$x[1]]['p [kN/m]'].'kN/m   ...'
            )
        );
        $blc->write('vendor/resist/ecc-calculations/canvas/linQ0.jpg', $write, 'Gerenda kiosztás geometriája');
    }
}
