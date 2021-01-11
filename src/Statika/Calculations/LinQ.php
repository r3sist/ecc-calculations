<?php declare(strict_types = 1);
/**
 * Beams' line loads calculation of variable distributed load - Calculation class for Statika framework
 * (c) Bence VÁNKOS | https://structure.hu | https://github.com/r3sist/ecc-calculations
 */

namespace Statika\Calculations;

use \H3;
use resist\H3\Validator;
use Statika\EurocodeInterface;

Class LinQ
{
    private Validator $validator;

    public function __construct(Validator $validator)
    {
        $this->validator = $validator;
    }

    /**
     * @param Ec $ec
     */
    public function calc(EurocodeInterface $ec): void
    {
        $ec->note('Számítás apropója, hogy gerenda irányra merőlegesen, egyenletesen változó megoszló felületi teher (pl. hózug teher) szétosztást a Consteel nem tudott régen kezelni. A felületi teher vonalmenti teherré alakítható a lenti számítás alapján.');
        $ec->input('x', ['x_i,x_j,..', 'Gerenda koordináták'], '2,6,10,14', 'm', 'Gerenda $x$ pozíciói (koordinátái), vesszővel elválasztva, $0[m]$-től számítva.');
        if ($this->validator->isAlphanumericList($ec->x) === false) { // TODO use spcae instead
            $ec->danger('Hibás lista formátum. (Futtatás `2,6,10,14` értékkel.)');
            $ec->x = '2,6,10,14';
        }

        $ec->numeric('xMAX', ['x_(max)', 'Teher felület hossza'], 16, 'm', '');
        $ec->numeric('qMAX', ['q_(max)', 'Megoszló teher (növekvő) maximális értéke'], 10, 'kN/m²', '');
        $ec->numeric('qMIN', ['x_(min)', 'Megoszló teher minimális értéke'], 2, 'kN/m²', '');

        $ec->set('qd',$ec->qMAX - $ec->qMIN);
        $ec->set('q1',$ec->qd / $ec->xMAX);

        $x = explode(',', $ec->x);
        if (count($x) < 2) {
            $ec->danger('Minimum 2 gerendára kell szétosztani a terhet!', 'Hiba!');
        }

        $i = 0;
        $scheme = ['Mező', 'Mező szélesség: $b [m]$', 'Vonalmenti teher: $p [(kN)/m]$'];
        $rows = [];
        foreach ($x as $xAct) {
            if (isset($x[$i + 1]) && $x[$i + 1] <= $x[$i]) {
                $ec->danger('Hibás koordináta sorrend vagy fedő gerendák.', 'Hiba!');
            }
            if ($i == 0) {
                $colNameA = H3::n2(($x[$i + 1] - $x[$i])/2 + $x[$i]);
                $colNameB = H3::n2((((($x[$i + 1] - $x[$i])/2 + $x[$i])*$ec->q1)/2 + $ec->qMIN)*$colNameA);
            } elseif ($i === count($x) - 1) {
                $colNameA = H3::n2(($x[$i] - $x[$i - 1])/2 + $ec->xMAX - $x[$i]);
                $colNameB = H3::n2((((($x[$i] - ($x[$i] - $x[$i - 1])/2 + $ec->xMAX))*$ec->q1)/2 + $ec->qMIN)*$colNameA);
            } else {
                $colNameA = H3::n2(($x[$i + 1] - $x[$i])/2 + ($x[$i] - $x[$i - 1])/2);
                $colNameB = H3::n2((((( $x[$i] - (($x[$i] - $x[$i - 1])/2) + $x[$i] + (($x[$i + 1] - $x[$i])/2) ))*$ec->q1)/2 + $ec->qMIN)*$colNameA);
            }
            $pos = (string)($i+1).'.';
            $rows[] = [$pos, $colNameA, $colNameB];
            $i++;
        }
        $ec->tbl($scheme, $rows, 'tbl', 'Gerenda pozíció: x [m]');

        $write = array(
            array(
                'size' => 14,
                'x' => 40,
                'y' => 140,
                'text' => $ec->qMIN.'kN/m²'
            ),
            array(
                'size' => 14,
                'x' => 680,
                'y' => 30,
                'text' => $ec->qMAX.'kN/m²'
            ),
            array(
                'size' => 14,
                'x' => 630,
                'y' => 310,
                'text' => $ec->xMAX.'m'
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
                'text' => $rows[0][1].'kN/m'
            ),
            array(
                'size' => 14,
                'x' => 260,
                'y' => 215,
                'text' => $rows[1][1].'kN/m   ...'
            )
        );
//        $ec->write('vendor/resist/ecc-calculations/canvas/linQ0.jpg', $write, 'Gerenda kiosztás geometriája');
        // TODO port write() to SVG
    }
}
