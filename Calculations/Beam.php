<?php

namespace Calculation;

Class Beam extends \Ecc
{

    public function calc($f3)
    {
        $ec = \Ec::instance();
        $blc = \Blc::instance();

        $ec->matList('mat', 'C25/30', 'Beton anyagminőség');
        $fck = $ec->matProp($f3->_mat, 'fck');
        $fctd = $ec->matProp($f3->_mat, 'fctd');
        $blc->math('f_(ck) = '.$fck.' [MPa]');
        $blc->math('f_(ctd) = '.$fctd.' [MPa]');

        $blc->h1('Nyírt keresztmetszet egyszerűsített számítása');
        $blc->note('[Világos kék 6.3 32.o]');
        $blc->txt('Húzott vashányad meghatározása:');

        $blc->input('A_sl', 'Húzott vasalás vizsgált keresztmetszeten átvezetve', '0', 'mm²', '\`l_(bd) + d\`-vel túlvezetett húzott vasak vehetők figyelembe');
        $blc->note('`l_(bd)` a lehorgonyzási hossz tervezési értéke.');
        $blc->input('d', 'Keresztmetszet hatékony magasság', '200', 'mm');
        $blc->input('b_w', 'Keresztmetszet gerinc szélesség', '200', 'mm');
        $blc->def('rho_lcalc', min($f3->_A_sl/($f3->_b_w*$f3->_d), 0.02), 'rho_l = min(A_(sl)/(b_w*d), 0.02) = %%', 'Húzott vashányad');
        $blc->note('A húzott vashányad a biztonság javára való közelítéssel mindig lehet 0. Támasznál általában 0.');

        $rhos = [
            '0.00 %' => 0.00/100,
            '0.25 %' => 0.25/100,
            '0.50 %' => 0.50/100,
            '1.00 %' => 1.00/100,
            '2.00 %' => 2.00/100,
        ];
        $blc->lst('rho_l', $rhos, '`rho_(l,calc):` Húzott vashányad', 0);
        $blc->note('`V_(Rd,c) = c*b_w*d*f_(ctd)` képlethez `c(f_(ctd))` értékei meghatározhtaók táblázatosan. Dulácska biztonság javára történő közelítő képletével van itt számolva a `c`. [Világos kék [19]]');
        $c = (1.2 -  $fck/150)*(0.15*$f3->_rho_l + 0.45/(1 + $f3->_d/1000));
        $blc->def('c', $c, 'c = (1.2 - f_(ck)/150)*(0.15*rho_l + 0.45/(1+d/1000)) = %%');

        $blc->success0('VRdc');
            $blc->def('VRdc', $c*$f3->_b_w*$f3->_d*$fctd/1000, 'V_(Rd,c) = c*b_w*d*f_(ctd) = %% [kN]');
        $blc->success1('VRdc');




    }
}
