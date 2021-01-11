<?php declare(strict_types = 1);
/**
 * Impact and horizontal load calculations according to Eurocodes - Calculation class for Statika framework
 * (c) Bence VÁNKOS | https://structure.hu | https://github.com/r3sist/ecc-calculations
 */

namespace Statika\Calculations;

use \H3;
use Statika\EurocodeInterface;

Class Impact
{
    /**
     * @param Ec $ec
     */
    public function calc(EurocodeInterface $ec): void
    {
        $ec->note('Mellvédek és elválasztó falak vízszintes hasznos terhei. Emberi használatból - *ULS* hasznos teherként:');
        $ec->note('Magasság: $1.2 [m]$. Osztályok: *A, B, C1*: $0.5$; *C2, C3, C4, D*: $1.0$; *E*: $2.0 $; *C5*, tömeg: $3.0 [(kN)/(fm)]$');

        $ec->h1('Gépjárműütközés egyenértékű statikus terhe függőleges tartószerkezeti elemeken', 'Rendkívüli tervezési állapot');
        $ec->note('*[MSZ EN 1991-1-7:2015 4.3.2. Ütközés felszerkezetekhez]*');
        $ec->note('*[Terhek és hatások (2017) 14.2.1 103.o.]*');
        $ec->note('*Kemény ütközés*: az energiát elsősorban az ütköző jármű nyeli el. Nincs ütközésvédelem. Nem függ az anyagtól.');
        $ec->lst('location', ['Autópálya, főút / Teherautó, busz' => 'highway', 'Országút / Teherautó, busz' => 'road', 'Lakott területen út / Teherautó, busz' => 'city', 'Garázs, udvar / Csak autó' => 'garage0', 'Garázs, udvar / Teherautó' => 'garage1', 'Raktár, targonca' => 'storage'], ['', 'Hely, típus'], 'storage');

        $FxText = 'Ütközési irány kijelölt haladási irányban';
        $FyText = 'Ütközési irány kijelölt haladási irányra merőlegesen';
        switch ($ec->location) {
            case 'highway':
                $ec->def('Fdx', 1000, 'F_(d,x) = %% [kN]', $FxText);
                $ec->def('Fdy', 500, 'F_(d,y) = %% [kN]', $FyText);
                $ec->def('h', 1.25, 'h_(tr) = %% [m]', 'Teherautó ütközési magasság');
                $ec->md('Ütközési felület: $0.5×min{(1.5),("elemszélesség"):}[m]$');
                break;
            case 'road':
                $ec->def('Fdx', 750, 'F_(d,x) = %% [kN]', $FxText);
                $ec->def('Fdy', 375, 'F_(d,y) = %% [kN]', $FyText);
                $ec->def('h', 1.25, 'h_(tr) = %% [m]', 'Teherautó ütközési magasság');
                $ec->md('Ütközési felület: $0.5×min{(1.5),("elemszélesség"):}[m]$');
                break;
            case 'city':
                $ec->def('Fdx', 500, 'F_(d,x) = %% [kN]', $FxText);
                $ec->def('Fdy', 250, 'F_(d,y) = %% [kN]', $FyText);
                $ec->def('h', 1.25, 'h_(tr) = %% [m]', 'Teherautó ütközési magasság');
                $ec->md('Ütközési felület: $0.5×min{(1.5),("elemszélesség"):}[m]$');
                break;
            case 'garage0':
                $ec->math('lt 3 [t]');
                $ec->def('Fdx', 50, 'F_(d,x) = %% [kN]', $FxText);
                $ec->def('Fdy', 25, 'F_(d,y) = %% [kN]', $FyText);
                $ec->def('h', 0.5, 'h_(car) = %% [m]', 'Személyautó ütközési magasság');
                $ec->md('Ütközési felület: $0.25×min{(1.5),("elemszélesség"):}[m]$');
                break;
            case 'garage1':
                $ec->math('>= 3 [t]');
                $ec->def('Fdx', 150, 'F_(d,x) = %% [kN]', $FxText);
                $ec->def('Fdy', 75, 'F_(d,y) = %% [kN]', $FyText);
                $ec->def('h', 1.25, 'h_(tr) = %% [m]', 'Teherautó ütközési magasság');
                $ec->md('Ütközési felület: $0.5×min{(1.5),("elemszélesség"):}[m]$');
                break;
            case 'storage':
                $ec->region0('fl', 'Targoncák osztályozása');
                $ec->img('https://structure.hu/ecc/impactFl.jpg', 'Targonca osztályozás');
                $ec->region1();
                $ec->numeric('W', ['W', 'Targonca bruttó súlya'], 100, 'kN', 'Önsúly + megengedett terhelés összesen');
                $ec->def('Fdx', 5*$ec->W, 'F_(d,x) = F_(d,y) = 5W = %% [kN]', 'Ütközési erő mindkét irányban');
                $ec->def('h', 0.75, 'h_(fl) = %% [m]', 'Ütközési magasság');
                break;
        }
        $ec->note('$F_(d,x)$ és $F_(d,y)$ erőket nem kell egyidejűleg figyelembe venni.');
        $ec->note('Biztonsági korlát vagy 3 m széles forgalommentes sáv vagy 0.4 m mély árok esetén 50 %-kal csökkenthetők az erők! Valamint korláttal ellátott és 0.25 m magas, min. 0.5 m széles szegély esetén elhagyható.');

        $ec->h1('Gépjárműütközés egyenértékű statikus terhe útpálya feletti szerkezeteken', 'Rendkívüli tervezési állapot');
        $ec->numeric('hu', ['h_u', 'Űrszelvény magassága'], 3, 'm');
        if ($ec->hu >= 6) {
            $ec->def('r', 0, 'r = %%', '$h ge 6 [m]$');
            $ec->label('yes', 'Nincs ütközés');
        } else if ($ec->hu > 4.7 && $ec->hu < 6) {
            $ec->def('r', H3::n2($ec->linterp(4.7, 1, 6, 0, $ec->hu)), 'r = %%', '$4.7 lt h lt 6 [m]$ Lineáris interpolálással');
        } else {
            $ec->def('r', 1, 'r = %%', '$h le 4.7 [m]$');
        }
        $ec->def('Fdxu', 0.5*$ec->r*$ec->Fdx, 'F_(d,x,u) = 0.5*r*F_(d,x) = %% [kN]', 'Ütközési erő függőleges szerkezet ütközéséből származtatva');
        if ($ec->Fdxu != 0) {
            $ec->img('https://structure.hu/ecc/impact0.jpg', 'Teheresetek');
            $ec->md('Ütközési felület: $0.25×0.25 [m]$');
        }

        $ec->h1('Járműütközés vízszintes terhe parkolóházak korlátain és mellvédjén', '*ULS* hasznos teherként');
        $ec->note('*[MSZ EN 1991-1-1:2005 B melléklet]*');
        $ec->note('*[Terhek és hatások (2017) 7.5 57.o.]*');
        $ec->note('$sigma_b, sigma_c$ korlát és jármű alakváltozása ütközés esetén. Merev korlát esetén $sigma_b = 0$. Nincs javasolt adat ($: >= 0$)');
        $ec->txt('Ütközési szélesség: $1.5 [m]$');
        $ec->lst('weight0', ['Kevesebb, mint 2.5 [to]' => 'lt2500', 'Több, mint 2.5 [to]' => 'gt2500'], ['', 'Tömeg'], 'lt2500');
        $ec->txt('Ütközési magasság: $0.375 [m]$');
        switch ($ec->weight0) {
            case 'lt2500':
                $ec->math('F_k := 150 [kN]', 'Egyszerűsített módszer, merev korlát.');
                $ec->math('p_k = (150 [kN])/(1.5 [m]) = 100 [(kN)/(fm)]', 'Vonalmenti teherként');
                break;
        }
        $ec->numeric('m', ['m', 'Számításba veendő össztömeg'], 1500, 'kg', ($ec->weight0 === 'gt2500')?'$color(red)"Beruházói adatszolgáltatásból!"$ Személyautó: 1500':'Személyautó: 1500');
        $ec->numeric('v', ['v', 'Korlátra merőleges sebesség'], 4.5, 'm/s', '');
        $ec->note('Parkolóházakban 4.5 m/s sebességnél nagyobb is előfordulhat!');
        $ec->math('sigma_c := 100 [mm]%%%sigma_b := 0');
        $ec->def('Fk', ceil((0.5*$ec->m* ($ec->v ** 2))/(100)),'F_k = (0.5mv^2)/(sigma_c+sigma_b) = %% [kN]');
        $ec->h2('Rámpák mellett');
        $ec->boo('longRamp', ['', '20 m-nél hosszabb rámpa'], true, '');
        if ($ec->longRamp) {
            $ec->def('Frk', 2*$ec->Fk, 'F_(r,k) = 2*F_k = %% [kN]');
        } else {
            $ec->def('Frk', 0.5*$ec->Fk, 'F_(r,k) = 0.5*F_k = %% [kN]');
        }
        $ec->txt('Ütközési magasság: $0.610 [m]$');
    }
}
