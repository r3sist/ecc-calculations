<?php declare(strict_types = 1);
// Eurocode earthquake calculations - Calculation class for ECC framework
// (c) Bence VÁNKOS | https://structure.hu | https://github.com/r3sist/ecc-calculations


namespace Calculation;

use Base;
use Ec\Ec;
use Ecc\Blc;
use H3;
use Symfony\Component\Yaml\Yaml;
use function array_key_exists;

class Eq
{
    private Yaml $yaml;

    public function __construct(Yaml $yaml)
    {
        $this->yaml = $yaml;
    }

    public function calc(Base $f3, Blc $blc, Ec $ec): void
    {
        $agrData = $this->yaml::parseFile(PATH.'structure_data/ecc/agrHu.yaml');
        $blc->input('place', ['', 'Magyar településnév'], 'Budapest');
        $f3->_place = mb_convert_case($f3->_place, MB_CASE_TITLE);
        if (array_key_exists($f3->_place, $agrData)) {
            $agR0 = $agrData[$f3->_place];
            $g = 9.807813;
            $agR = H3::n3($agR0*$g);

            $blc->note('$g_("Magyarország") = '.$g.' [m/s^2]$');
            $blc->def('agR', $agR, 'a_(gR,"'.$f3->_place.'") = '.$agR0.'*g = %% [m/s^2]', 'Talajgyorsulási refernciaérték');
        } else {
            $blc->danger('Hibás település név.');
        }

        $blc->img('https://structure.hu/ecc/eqHu.jpg');

    }
}