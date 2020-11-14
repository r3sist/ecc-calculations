<?php declare(strict_types = 1);
// Eurocode earthquake calculations - Calculation class for ECC framework
// (c) Bence VÁNKOS | https://structure.hu | https://github.com/r3sist/ecc-calculations


namespace Calculation;

use Base;
use Ec\Ec;
use Ecc\Blc;
use Symfony\Component\Yaml\Yaml;

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
        \H3::dump($agrData);
    }
}