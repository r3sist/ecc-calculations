<?php

namespace Calculation;

Class Impact extends \Ecc
{

    /**
     * @var $f3 \Base
     * @var $blc \Blc
     * @var $ec \Ec\Ec
     * @throws \Exception
     */
    public function calc(object $f3, object $blc, object $ec): void
    {
        $blc->note('*[Terhek és hatások (2017) 14.2.1 103.o.]*');
        $blc->note('*Kemény ütközés*: az energiát elsősorban az ütköző jármű nyeli el.');
        $blc->h1('Gépjárműütközés egyenértékű statikus terhe függőleges tartószerkezeti elemeken', 'Rendkívüli tervezési állapot');
        $blc->lst('location', ['Autópálya, főút / Teherautó, busz' => 'highway', 'Országút / Teherautó, busz' => 'road', 'Lakott területen út / Teherautó, busz' => 'city', 'Garázs, udvar / Csak autó' => 'garage0', 'Garázs, udvar / Teherautó' => 'garage1', 'Raktár, targonca' => 'storage'], 'Hely, típus', 'storage');

        switch ($f3->_location) {
            case 'highway':
                $blc->def('Fdx', 1000, 'F_(d,x) = %% [kN]', 'Ütközési erő');
                $blc->def('Fdy', 500, 'F_(d,y) = %% [kN]', 'Ütközési erő');
                $blc->def('h', 1.25, 'h_(tr) = %% [m]', 'Teherautó ütközési magasság');
                break;
            case 'road':
                $blc->def('Fdx', 750, 'F_(d,x) = %% [kN]', 'Ütközési erő');
                $blc->def('Fdy', 375, 'F_(d,y) = %% [kN]', 'Ütközési erő');
                $blc->def('h', 1.25, 'h_(tr) = %% [m]', 'Teherautó ütközési magasság');
                break;
            case 'city':
                $blc->def('Fdx', 500, 'F_(d,x) = %% [kN]', 'Ütközési erő');
                $blc->def('Fdy', 250, 'F_(d,y) = %% [kN]', 'Ütközési erő');
                $blc->def('h', 1.25, 'h_(tr) = %% [m]', 'Teherautó ütközési magasság');
                break;
            case 'garage0':
                $blc->math('lt 3 [t]');
                $blc->def('Fdx', 50, 'F_(d,x) = %% [kN]', 'Ütközési erő');
                $blc->def('Fdy', 25, 'F_(d,y) = %% [kN]', 'Ütközési erő');
                $blc->def('h', 0.5, 'h_(car) = %% [m]', 'Személyautó ütközési magasság');
                break;
            case 'garage1':
                $blc->math('>= 3 [t]');
                $blc->def('Fdx', 150, 'F_(d,x) = %% [kN]', 'Ütközési erő');
                $blc->def('Fdy', 75, 'F_(d,y) = %% [kN]', 'Ütközési erő');
                $blc->def('h', 1.25, 'h_(tr) = %% [m]', 'Teherautó ütközési magasság');
                break;
            case 'storage':
                $blc->region0('fl', 'Targoncák osztályozása');
                $blc->img('https://structure.hu/ecc/impactFl.jpg', 'Targonca osztályozás');
                $blc->region1('fl');
                $blc->numeric('W', ['W', 'Targonca bruttó súlya'], 100, 'kN', 'Önsúly + megengedett terhelés összesen');
                $blc->def('Fdxy', 5*$f3->_W, 'F_(d,x) = F_(d,y) = 5W = %% [kN]', 'Ütközési erők');
                $blc->def('h', 0.75, 'h_(fl) = %% [m]', 'Ütközési magasság');
                break;
        }
        $blc->note('$F_(d,x)$ és $F_(d,y)$ erőket nem kell egyidejűleg figyelembe venni.');
    }
}
