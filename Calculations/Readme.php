<?php

namespace Calculation;

Class Readme extends \Ecc
{

    public function calc($f3)
    {
        $blc = \Blc::instance();

        $text0 = '
A dokumentum a [structure](https://bitbucket.org/resist/source-structure) és az 
[ecc-calculations](https://bitbucket.org/resist/ecc-calculations) repository-k README.md fájlját jeleníti meg. 
A számítások changelogja a [ecc-calculations commitjainál](https://bitbucket.org/resist/ecc-calculations/commits) követhetők.

---
';

        $blc->md($text0);

        $blc->hr();
        $blc->md('[ecc-calculations]()');

        $text1 = file_get_contents($f3->BASE.'vendor/resist/ecc-calculations/README.md');
        $blc->md($text1);

        $text2 = file_get_contents($f3->BASE.'README.md');
        $blc->md($text2);
    }
}
