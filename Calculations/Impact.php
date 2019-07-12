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
        $blc->note('*Kemény ütközés*: az energiát elsősorban az ütköző jármű nyeli el. Nincs ütközésvédelem. Nem függ az anyagtól.');

        $blc->h1('Gépjárműütközés egyenértékű statikus terhe függőleges tartószerkezeti elemeken', 'Rendkívüli tervezési állapot');
        $blc->lst('location', ['Autópálya, főút / Teherautó, busz' => 'highway', 'Országút / Teherautó, busz' => 'road', 'Lakott területen út / Teherautó, busz' => 'city', 'Garázs, udvar / Csak autó' => 'garage0', 'Garázs, udvar / Teherautó' => 'garage1', 'Raktár, targonca' => 'storage'], 'Hely, típus', 'storage');

        $FxText = 'Ütközési irány kijelölt haladási irányban';
        $FyText = 'Ütközési irány kijelölt haladási irányra merőlegesen';
        switch ($f3->_location) {
            case 'highway':
                $blc->def('Fdx', 1000, 'F_(d,x) = %% [kN]', $FxText);
                $blc->def('Fdy', 500, 'F_(d,y) = %% [kN]', $FyText);
                $blc->def('h', 1.25, 'h_(tr) = %% [m]', 'Teherautó ütközési magasság');
                $blc->md('Ütközési felület: $0.5×min{(1.5),("elemszélesség"):}[m]$');
                break;
            case 'road':
                $blc->def('Fdx', 750, 'F_(d,x) = %% [kN]', $FxText);
                $blc->def('Fdy', 375, 'F_(d,y) = %% [kN]', $FyText);
                $blc->def('h', 1.25, 'h_(tr) = %% [m]', 'Teherautó ütközési magasság');
                $blc->md('Ütközési felület: $0.5×min{(1.5),("elemszélesség"):}[m]$');
                break;
            case 'city':
                $blc->def('Fdx', 500, 'F_(d,x) = %% [kN]', $FxText);
                $blc->def('Fdy', 250, 'F_(d,y) = %% [kN]', $FyText);
                $blc->def('h', 1.25, 'h_(tr) = %% [m]', 'Teherautó ütközési magasság');
                $blc->md('Ütközési felület: $0.5×min{(1.5),("elemszélesség"):}[m]$');
                break;
            case 'garage0':
                $blc->math('lt 3 [t]');
                $blc->def('Fdx', 50, 'F_(d,x) = %% [kN]', $FxText);
                $blc->def('Fdy', 25, 'F_(d,y) = %% [kN]', $FyText);
                $blc->def('h', 0.5, 'h_(car) = %% [m]', 'Személyautó ütközési magasság');
                $blc->md('Ütközési felület: $0.25×min{(1.5),("elemszélesség"):}[m]$');
                break;
            case 'garage1':
                $blc->math('>= 3 [t]');
                $blc->def('Fdx', 150, 'F_(d,x) = %% [kN]', $FxText);
                $blc->def('Fdy', 75, 'F_(d,y) = %% [kN]', $FyText);
                $blc->def('h', 1.25, 'h_(tr) = %% [m]', 'Teherautó ütközési magasság');
                $blc->md('Ütközési felület: $0.5×min{(1.5),("elemszélesség"):}[m]$');
                break;
            case 'storage':
                $blc->region0('fl', 'Targoncák osztályozása');
                $blc->img('https://structure.hu/ecc/impactFl.jpg', 'Targonca osztályozás');
                $blc->region1('fl');
                $blc->numeric('W', ['W', 'Targonca bruttó súlya'], 100, 'kN', 'Önsúly + megengedett terhelés összesen');
                $blc->def('Fdx', 5*$f3->_W, 'F_(d,x) = F_(d,y) = 5W = %% [kN]', 'Ütközési erő mindkét irányban');
                $blc->def('h', 0.75, 'h_(fl) = %% [m]', 'Ütközési magasság');
                break;
        }
        $blc->note('$F_(d,x)$ és $F_(d,y)$ erőket nem kell egyidejűleg figyelembe venni.');
        $blc->note('Biztonsági korlát vagy 3 m széles forgalommentes sáv vagy 0.4 m mély árok esetén 50 %-kal csökkenthetők az erők! Valamint korláttal ellátott és 0.25 m magas, min. 0.5 m széles szegély esetén elhagygható.');

        $blc->h1('Gépjárműütközés egyenértékű statikus terhe útpálya feletti szerkezeteken', 'Rendkívüli tervezési állapot');
        $blc->numeric('hu', ['h_u', 'Űrszelvény magassága'], 3, 'm');
        if ($f3->_hu >= 6) {
            $blc->def('r', 0, 'r = %%', '$h ge 6 [m]$');
            $blc->label('yes', 'Nincs ütközés');
        } else if ($f3->_hu > 4.7 && $f3->_hu < 6) {
            $blc->def('r', \H3::n2($ec->linterp(4.7, 1, 6, 0, $f3->_hu)), 'r = %%', '$4.7 lt h lt 6 [m]$ Lineáris interpolálással');
        } else {
            $blc->def('r', 1, 'r = %%', '$h le 4.7 [m]$');
        }
        $blc->def('Fdxu', 0.5*$f3->_r*$f3->_Fdx, 'F_(d,x,u) = 0.5*r*F_(d,x) = %% [kN]', 'Ütközési erő függőleges szerkezet ütközéséből származtatva');
        if ($f3->_Fdxu != 0) {
            $blc->img('https://structure.hu/ecc/impact0.jpg', 'Teheresetek');
            $blc->md('Ütközési felület: $0.25×0.25 [m]$');
        }

        $blc->h1('Járműütközés vízszintes terhe parkolóházak korlátain és mellvédjén', 'Rendkívüli tervezési állapot');

        // TODO Járműütközés vízszintes terhe parkolóházak korlátain és mellvédjén [Terhek 57]
        $blc->md('`TODO Járműütközés vízszintes terhe parkolóházak korlátain és mellvédjén [Terhek 57]`');

        // TODO Mellvédek és elválasztó falak vízszintes hasznos terhei emberi használatból [Terhek 57]
        $blc->md('`TODO Mellvédek és elválasztó falak vízszintes hasznos terhei emberi használatból [Terhek 57]`');

        // TODO ütközés rámpa mellett [Terhek 57 táblázat]
        $blc->md('`TODO ütközés rámpa mellett [Terhek 57 táblázat]`');

    }
}
