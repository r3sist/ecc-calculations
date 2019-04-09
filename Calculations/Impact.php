<?php

namespace Calculation;

Class Test extends \Ecc
{

    /**
     * @var $f3 \Base
     * @var $blc \Blc
     * @var $ec \Ec\Ec
     * @throws \Exception
     */
    public function calc(object $f3, object $blc, object $ec): void
    {
        $blc->h1('Gépjárműütközés egyenértékű statikus terhe függőleges tartószerkezeti elemeken');
        $blc->lst('location', ['Autópálya, főút' => 'highway', 'Országút' => 'road', 'Lakott területen út' => 'city', 'Garázs, udvar' => 'garage', 'Raktár, targonca' => 'storage'], 'Hely, típus', 'storage');
    }
}
