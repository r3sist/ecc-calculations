<?php declare(strict_types = 1);
/**
 * Fire resistance of RC columns analysis according to Eurocodes - Calculation class for Statika framework
 * (c) Bence VÁNKOS | https://structure.hu | https://github.com/r3sist/ecc-calculations
 */

namespace Statika\Calculations;

use Statika\Calculations\Column;
use \H3;
use Statika\EurocodeInterface;

Class ColumnFire
{
    private Column $columnCalculation;

    public function __construct(Column $columnCalculation)
    {
        $this->columnCalculation = $columnCalculation;
    }

    /**
     * @param Ec $ec
     */
    public function calc(EurocodeInterface $ec): void
    {
        $this->columnCalculation->moduleColumnData();
        $concreteMaterial = $ec->getMaterial($ec->concreteMaterialName);
        $rebarMaterial = $ec->getMaterial($ec->rebarMaterialName);

        $ec->rebarList('phi', 20, ['phi', 'Fő vas átmérő']);
        $ec->rebarList('phis', 12, ['phi_s', 'Kengyel vas átmérő']);
        $ec->def('as', $ec->cnom + $ec->phis + 0.5*$ec->phi, 'a_s = c_(nom) + phi_s + phi/2 = %% [mm]', 'Betontakarás fővas tengelyen. $25 < a_s < 80 [mm]$');
        if ($ec->as < 25) {
            $ec->danger('Szükséges betontakarás: $25 [mm]$', 'Túl kicsi betontakarás');
        }
        if ($ec->as > 80) {
            $ec->danger('Maximális betontakarás: $80 [mm]$', 'Túl nagy betontakarás');
        }
        $ec->numeric('lfi', ['l_(fi)', 'Pillér hálózati hossza'], 3, 'm', '');
        $ec->numeric('beta', ['beta', 'Pillér kihajlási csökkentő tényező tűzhatásra'], 0.7, '', '');
        $ec->note('30 percnél hosszabb tűz esetén alsó szinten 0.5, felső szinten 0.7 megengedett. 1.0 mindig alkalamzható.');
        $ec->boo('l0fired', ['', 'Hálózati hossz csökkentése'], false, 'MSZ EN 1992-1-2:2013 5.3.2. (2) $l_(0,fi) < 3 [m]$.');
        if ($ec->l0fired) {
            $ec->def('l0fi', min($ec->beta*$ec->lfi, 3), 'l_(0,fi) = min{(beta*l_(fi)),(3):} = %% [m]', 'Pillér hatékony kihajlási hossza');
        } else {
            $ec->def('l0fi', $ec->beta*$ec->lfi, 'l_(0,fi) = beta*l_(fi) = %% [m]', 'Pillér hatékony kihajlási hossza');
        }
        $ec->def('b1', H3::n0((2*$ec->Ac)/($ec->a + $ec->b)), 'b\' = (2*A_c)/(a + b) = %% [mm]', '');

        $ec->def('A_s_min', H3::n0(0.002*$ec->Ac), 'A_(s, min) = %% [mm^2]', 'Minimális vasmennyiség: 2‰');
        $ec->def('A_s_max', H3::n0(0.04*$ec->Ac), 'A_(s, max) = %% [mm^2]', 'Maximális vasmennyiség: 4%');

        $ec->txt('Vasátmérő darabszámok:');
        $As = $ec->rebarTable();
        $ec->def('As', $As, 'A_s = %% [mm^2]', 'Táblázat alapján');
        $ec->boo('AsCustom', ['', 'Táblázatos vasmennyiség felülírása'], false, '');
        if ($ec->AsCustom) {
            $ec->numeric('As', ['A_s', 'Alkalmazott vas mennyiség'], 1256, 'mm2', '');
        }
        if ($ec->A_s_min > $ec->As) {
            $ec->danger('Szükséges vasmennyiség: $A_(s,min) = '.$ec->A_s_min.' [mm^2]$', 'Túl kevés vas');
        }
        if ($ec->A_s_max < $ec->As) {
            $ec->danger('Maximális vasmennyiség: $A_(s,max) = '.$ec->A_s_max.' [mm^2]$', 'Túl sok vas');
        }
        $ec->txt('$'. H3::n1(($ec->As/$ec->Ac)*100).'%$ vas');
        $ec->def('omega', H3::n2(($ec->As*$rebarMaterial->fyd)/($ec->Ac*$concreteMaterial->fcd)), 'omega = (A_s*f_(yd))/(A_c*f_(cd)) = %%', 'Mechanikai acélhányad normálhőmérsékleten');
        $ec->numeric('alphacc', ['alpha_(c,c)', 'Nyomószilárdság szorzótényezője'], 1, '', 'Lásd EN 1992-1-1');
        $ec->numeric('mufi', ['mu_(fi)', 'Pillér kihasználtsága tűzhatás esetén'], 0.4, '', '$0 < (mu_(fi) = N_(Ed,fi)/N_(Rd)) < 1$');
        $ec->def('Ra', H3::n2(1.6*($ec->as - 30)), 'R_a = 1.6*(a_s - 30) = %%', '');
        $ec->def('Rl', H3::n2(9.6*(5 - $ec->l0fi)), 'R_l = 9.6*(5 - l_(0,fi)) = %%', '');
        $ec->def('Rb', H3::n2(0.09*$ec->b1), 'R_b = 0.09*b\' = %%', '');
        $ec->boo('Rn0', ['', 'Csak sarok vasak vannak'], false, '');
        if ($ec->Rn0) {
            $ec->def('Rn', 0, 'R_n = %%', 'Összesen 4 db sarokvas');
        } else {
            $ec->def('Rn', 12, 'R_n = %%', 'Vasak nem csak a sarkokban vannak');
        }
        $ec->def('Retafi', H3::n2(83*(1-($ec->mufi*((1 + $ec->omega)/((0.85/$ec->alphacc) + $ec->omega))))), 'R_(eta,fi) = 83*(1.00 - mu_(fi)*(1 + omega)/(0.85/alpha_(c,c) + omega)) = %%');
        $ec->def('R', H3::n0(120* ((($ec->Retafi + $ec->Ra + $ec->Rl + $ec->Rb + $ec->Rn) / 120) ** 1.8)), 'R = 120*((R_(eta,fi) + R_a + R_l + R_b + R_n)/120)^1.8 = %%');
        if ($ec->R < 30) {
            $ec->danger('R0', 'Tűzállóság');
        } else if ($ec->R < 60) {
            $ec->success('R30', 'Tűzállóság');
        } else if ($ec->R < 90) {
            $ec->success('R60', 'Tűzállóság');
        } else if ($ec->R < 120) {
            $ec->success('R90', 'Tűzállóság');
        } else if ($ec->R < 180) {
            $ec->success('R120', 'Tűzállóság');
        } else if ($ec->R < 240) {
            $ec->success('R180', 'Tűzállóság');
        } else {
            $ec->success('R240', 'Tűzállóság');
        }
    }
}
