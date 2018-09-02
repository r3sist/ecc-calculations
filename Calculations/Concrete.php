<?php

namespace Calculation;

Class Concrete extends \Ecc
{

    public function calc($f3)
    {
        $ec = \Ec::instance();
        $blc = \Blc::instance();
        $lava = new \Khill\Lavacharts\Lavacharts;

        $blc->note('A számítások Tóth Bertalan programja alapján történnek: https://structure.hu/berci/material ');

        $ec->matList('mat', 'C25/30', 'Beton anyag');

        $blc->note('A szilárdsági osztályhoz tartozó jellemzők a 28 napos korban meghatározott, hengeren mért nyomószilárdság fck karakterisztikus értékén alapulnak.');

        
    }
}
