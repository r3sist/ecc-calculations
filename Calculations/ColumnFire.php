<?php

namespace Calculation;

Class ColumnFire extends \Ecc
{

    /**
     * @var $f3 \Base
     * @var $blc \Blc
     * @var $ec \Ec\Ec
     * @throws \Exception
     */
    public function calc(object $f3, object $blc, object $ec): void
    {
        $column = new \Calculation\Column();
        $column->moduleColumnData($f3, $blc, $ec);

//        $blc->h1('Tűzállósági határérték meghatározása', 'MSZ-EN 1992-1-2:2013 szabvány 5.3.2. pontja szerint: ***A*** módszer');
        $ec->rebarList('phi', 20, ['phi', 'Fő vas átmérő']);
        $ec->rebarList('phis', 20, ['phi_s', 'Kengyel vas átmérő']);
        $blc->def('as', $f3->_cnom + $f3->_phis + 0.5*$f3->_phi, 'a_s = c_(nom) + phi_s + phi/2 = %% [mm]', 'Betontakarás fővas tengelyen. $25 < a_s < 80 [mm]$');
        if ($f3->_as < 25) {
            $blc->danger('Szükséges betontakarás: $25 [mm]$', 'Túl kicsi betontakarás');
        }
        if ($f3->_as < $f3->_As) {
            $blc->danger('Maximális betontakarás: $80 [mm]$', 'Túl nagy betontakarás');
        }
        $blc->numeric('lfi', ['l_(fi)', 'Pillér hálózati hossza'], 3, 'm', '');
        $blc->numeric('beta', ['beta', 'Pillér kihajlási csökkentő tényező tűzhatásra'], 0.7, '', '');
        $blc->note('30 percnél hosszabb tűz esetén alsó szinten 0.5, felső szinten 0.7 megengedett. 1.0 mindig alkalamzható.');
        $blc->boo('l0fired', 'Hálózati hossz csökkentése', false, 'MSZ EN 1992-1-2:2013 5.3.2. (2) $l_(0,fi) < 3 [m]$.');
        if ($f3->_l0fired) {
            $blc->def('l0fi', min($f3->_beta*$f3->_lfi, 3), 'l_(0,fi) = min{(beta*l_(fi)),(3):} = %% [m]', 'Pillér hatékony kihajlási hossza');
        } else {
            $blc->def('l0fi', $f3->_beta*$f3->_lfi, 'l_(0,fi) = beta*l_(fi) = %% [m]', 'Pillér hatékony kihajlási hossza');
        }
        $blc->def('b1', \H3::n0((2*$f3->_Ac)/($f3->_a + $f3->_b)), 'b\' = (2*A_c)/(a + b) = %% [mm]', '');

        $blc->txt('Vasátmérő darabszámok:');
        $As = $ec->rebarTable();
        $blc->def('As', $As, 'A_s = %% [mm^2]', 'Táblázat alapján');
        $blc->numeric('As', ['A_s', 'Alkalmazott vas mennyiség'], 1256, 'mm2', '');
        if ($f3->_A_s_min > $f3->_As) {
            $blc->danger('Szükséges vasmennyiség: $A_(s,min) = '.$f3->_A_s_min.' [mm^2]$', 'Túl kevés vas');
        }
        if ($f3->_A_s_max < $f3->_As) {
            $blc->danger('Maximális vasmennyiség: $A_(s,max) = '.$f3->_A_s_max.' [mm^2]$', 'Túl sok vas');
        }
        $blc->def('omega', \H3::n2(($f3->_As*$f3->_rfyd)/($f3->_Ac*$f3->_cfcd)), 'omega = (A_s*f_(yd))/(A_c*f_(cd)) = %%', 'Mechanikai acélhányad normálhőmérsékleten');
        $blc->numeric('alphacc', ['alpha_(c c)', 'Nyomószilárdság szorzótényezője'], 1, '', 'Lásd EN 1992-1-1');
        $blc->numeric('mufi', ['mu_(fi)', 'Pillér kihasználtsága tűzhatás esetén'], 0.4, '', '$0 < (mu_(fi) = N_(Ed,fi)/N_(Rd)) < 1$');
        $blc->def('Ra', \H3::n2(1.6*($f3->_as - 30)), 'R_a = 1.6*(a_s - 30) = %%', '');
        $blc->def('Rl', \H3::n2(9.6*(5 - $f3->_l0fi)), 'R_l = 9.6*(5 - l_(0,fi)) = %%', '');
        $blc->def('Rb', \H3::n2(0.09*$f3->_b1), 'R_b = 0.09*b\' = %%', '');
        $blc->boo('Rn0', 'Csak sarok vasak vannak', false, '');
        if ($f3->_Rn0) {
            $blc->def('Rn', 0, 'R_n = %%', 'Összesen 4 db sarokvas');
        } else {
            $blc->def('Rn', 12, 'R_n = %%', 'Vasak nem csak a sarkokban vannak');
        }
        $blc->def('Retafi', \H3::n2(83*(1-($f3->_mufi*((1 + $f3->_omega)/((0.85/$f3->_alphacc) + $f3->_omega))))), 'R_(eta,fi) = %%');
        $blc->def('R', \H3::n0(120*pow(($f3->_Retafi + $f3->_Ra + $f3->_Rl + $f3->_Rb + $f3->_Rn)/120, 1.8)), 'R = 120*((R_(eta,fi) + R_a + R_l + R_b + R_n)/120)^1.8 = %%');
        if ($f3->_R < 30) {
            $blc->info('R0', 'Tűzállóság');
        } else if ($f3->_R < 60) {
            $blc->info('R30', 'Tűzállóság');
        } else if ($f3->_R < 90) {
            $blc->info('R60', 'Tűzállóság');
        } else if ($f3->_R < 120) {
            $blc->info('R90', 'Tűzállóság');
        } else if ($f3->_R < 180) {
            $blc->info('R120', 'Tűzállóság');
        } else if ($f3->_R < 240) {
            $blc->info('R180', 'Tűzállóság');
        } else {
            $blc->info('R240', 'Tűzállóság');
        }
    }
}
