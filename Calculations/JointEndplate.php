<?php declare(strict_types = 1);
// Analysis of steel rigid end plate joint according to Eurocodes - Calculation class for ECC framework
// (c) Bence VÁNKOS | https://structure.hu | https://github.com/r3sist/ecc-calculations

namespace Calculation;

use \Base;
use \Ecc\Blc;
use \Ec\Ec;
use \H3;

Class JointEndplate
{
    protected Bolt $bolt;
    protected Weld $weld;

    public function __construct(Bolt $bolt, Weld $weld)
    {
        $this->bolt = $bolt;
        $this->weld = $weld;
    }

    public function calc(Base $f3, Blc $blc, Ec $ec): void
    {
        $blc->note('A nyomóerő a keresztmetszeti ellenállás 5%-át nem haladhatja meg!');
        
        $ec->matList('steelMaterialName', 'S235', ['', 'Szelvények, lemezek anyagminősége'], 'steel');
        $ec->spreadMaterialData($f3->_steelMaterialName, 's');

        $ec->matList('boltMaterialName', '8.8', ['', 'Csavarok anyagminősége'], 'bolt');
        $ec->spreadMaterialData($f3->_boltMaterialName, 'b');
        $ec->boltList('boltName', 'M12', 'Csavar átmérő választása');
        $blc->def('As', $ec->boltProp($f3->_boltName, 'As'), 'A_s = %% [mm^2]', 'Csavar húzási keresztmetszet');
        $blc->def('dm', $ec->boltProp($f3->_boltName, 'dm'), 'd_m = %% [mm]', 'Kigombolódási átmérő');

        $blc->def('FtRd', $ec->FtRd($f3->_boltName, $f3->_boltMaterialName, true), 'F_(t,Rd) = %% [kN]', 'Egy csavar húzási ellenállása');
        $blc->info0();
            $blc->def('tpminm', ceil(0.175*$ec->boltProp($f3->_boltName, 'd')*($f3->_bfu/$f3->_sfy)), 't_(p,min,m) ge 0.175*d*(f_(ub)/f_y) = %% [mm]', 'Homloklemezek javasolt vastagsága kigombolódás kizáráshoz');
        $blc->info1();

        $blc->numeric('etaj1', ['eta_(j,1) = M_(Ed)/M_(pl,Rd)', 'Kapcsolódó elemek nyomatéki kihasználtsága'], 0.9);

        

    }
}
