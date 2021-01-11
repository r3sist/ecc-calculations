<?php declare(strict_types = 1);
/**
 * Eurocode earthquake calculations - Calculation class for Statika framework
 * (c) Bence VÁNKOS | https://structure.hu | https://github.com/r3sist/ecc-calculations
 */

namespace Statika\Calculations;

use H3;
use Statika\Ec;
use Statika\EurocodeInterface;
use Symfony\Component\Yaml\Yaml;
use function array_key_exists;

class Eq
{
    private Yaml $yamlService;

    public function __construct(Yaml $yamlService)
    {
        $this->yamlService = $yamlService;
    }

    /**
     * @param Ec $ec
     */
    public function calc(EurocodeInterface $ec): void
    {
        $agrData = $this->yamlService::parseFile($_ENV['PATH'].'structure_data/ecc/agrHu.yaml');
        $ec->input('place', ['', 'Magyar településnév'], 'Budapest');
        $ec->place = mb_convert_case($ec->place, MB_CASE_TITLE);
        if (array_key_exists($ec->place, $agrData)) {
            $agR0 = $agrData[$ec->place];
            $g = 9.807813;
            $agR = H3::n3($agR0*$g);

            $ec->note('$g_("Magyarország") = '.$g.' [m/s^2]$');
            $ec->def('agR', $agR, 'a_(gR,"'.$ec->place.'") = '.$agR0.'*g = %% [m/s^2]', 'Talajgyorsulási refernciaérték');
        } else {
            $ec->danger('Hibás település név.');
        }

        $ec->img('https://structure.hu/ecc/eqHu.jpg');
    }
}
