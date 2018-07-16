<?php

namespace Calculation;

Class Readme extends \Ecc
{

    public function calc($f3)
    {
        $blc = \Blc::instance();

        $text1 = file_get_contents($f3->BASE.'vendor/resist/ecc-calculations/README.md');
        $blc->md($text1);
    }
}
