<?php

namespace Calculation;

Class JointWindbrace extends \Ecc
{

    /**
     * @var $f3 \Base
     * @var $blc \Blc
     * @var $ec \Ec\Ec
     * @throws \Exception
     */
    public function calc(object $f3, object $blc, object $ec): void
    {
        $blc->h1('$t_1$ bekötő lemez');

        $weld = new \Calculation\Weld();
        $blc->numeric('a', ['a', 'Varrat gyökméret'], 6, 'mm');
        $blc->numeric('L', ['L', 'Lemez szélesség'], 300, 'mm');
        $f3->_w = 1;
        $f3->_t = max($f3->_t1, $f3->_t2);
        $f3->_F = $f3->_REd;
        $f3->_mat = $f3->_stMat;
        $weld->moduleWeld($f3, $blc, $ec);
        $blc->note('[Varrat modul](https://structure.hu/calc/Weld) betöltésével számítva.');
    }
}
