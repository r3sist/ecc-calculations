<?php declare(strict_types = 1);
/**
 * Analysis of RC shaft uplifting according to Eurocodes - Calculation class for Statika framework
 * (c) Bence VÁNKOS | https://structure.hu | https://github.com/r3sist/ecc-calculations
 */

namespace Statika\Calculations;

use Statika\Ec;
use Statika\EurocodeInterface;

Class ShaftUplift
{
    /**
     * @param Ec $ec
     */
    public function calc(EurocodeInterface $ec): void
    {
        $ec->numeric('gammac', ['gamma_c', 'Beton fajsúly'], 24, 'kN/m3');
        $ec->def('gammav', 10, 'gamma_v = %% [(kN)/m^3]');
        $ec->numeric('gammastb', ['gamma_(stb)', 'Stabilizáló erők biztonsági tényezője'], 0.9, '');
        $ec->numeric('gammadst', ['gamma_(dst)', 'Destabilizáló erők biztonsági tényezője'], 1.1, '');
        $ec->numeric('Gxk', ['G_(x,k)', 'Egyéb stabilizáló jellegű teher'], 0, 'kN', 'Karakterisztikus érték');

        $ec->txt('Szerkezeti vastagságok:');
        $ec->numeric('vf', ['v_f', 'Aknafal vastagság'], 400, 'mm', '');
        $ec->numeric('vl', ['v_l', 'Aknafenék vastagság'], 400, 'mm', '');
        $ec->numeric('xl', ['x_l', 'Aknafenék túlnyúlás'], 0, 'mm', '');
        $ec->txt('Belső (tiszta) akna méretek:');
        $ec->numeric('a', ['a', ''], 1600, 'mm', '');
        $ec->numeric('b', ['b', ''], 2800, 'mm', '');
        $ec->numeric('h', ['h', 'Mélység'], 2200, 'mm', 'Padló felső síkjától fenéklemez felső síkjáig');
        $ec->numeric('delta', ['Delta', 'Vízszint különbség padlótól'], 400, 'mm', '(+) padló felett -> víznyomás; (-) padló alatt alacsonyabb szinten');

        $ec->md('**Számítások:**');
        $ec->txt('Fal súlya:');
        $ec->def('Kf', 2*($ec->a + $ec->b + 2*$ec->vf), 'K_f = 2*(a + b + 2*v_f) = %% [mm]', 'Fal kerülete');
        $ec->def('Vf', $ec->vf*$ec->h*$ec->Kf*0.000000001, 'V_f = v_f*h*K_f = %% [m^3]', 'Fal térfogata');
        $ec->def('Gfd', $ec->gammastb*$ec->gammac*$ec->Vf, 'G_(f,d) = gamma_(stb)*gamma_c*V_f = %% [kN]', 'Fal súlyának tervezési értéke');
        $ec->txt('Lemez súlya:');
        $ec->def('Al', ($ec->b + 2*$ec->xl + 2*$ec->vf)*($ec->a + 2*$ec->xl + 2*$ec->vf)/1000000, 'A_l = (b+2*x_l+2*v_f)*(a+2*x_l+2*v_f) = %% [m^2]', 'Lemez területe');
        $ec->def('Vl', $ec->Al*($ec->vl/1000), 'V_l = A_l*v_l = %% [m^3]', 'Lemez térfogata');
        $ec->def('Gld', $ec->gammastb*$ec->gammac*$ec->Vl, 'G_(l,d) = gamma_(stb)*gamma_c*V_l = %% [kN]', 'Lemez súlyának tervezési értéke');
        $ec->def('Gxd', $ec->gammastb*$ec->Gxk, 'G_(x,d) = %% [kN]');
        $ec->txt('Víz felhajtó ereje:');
        $ec->def('hv', ($ec->delta + $ec->h + $ec->vl)/1000, 'h_v = Delta*h*v_l = %% [m]', 'Vízoszlop magassága');
        $ec->def('Av', ($ec->b + 2*$ec->vf)*($ec->a + 2*$ec->vf)/1000000, 'A_v = (b+2v_f)*(a+2v_f) = %% [m^2]', 'Vízoszlop területe');
        $ec->def('Vv', $ec->Av*$ec->hv + ($ec->Al - $ec->Av)*($ec->vl/1000), 'V_v = A_v*h_v + (A_l - A_v)*v_l = %% [m^3]', 'Szerkezet által kiszorított térfogat');
        $ec->note('A lemezszélesítésre kerülő föld súlya nem vesz részt a stabilizáló erőkben, de a szélesítés tömege és kiszorítása igen.');

        $ec->info0();
            $ec->txt('Stabilizáló erő:');
            $ec->def('Gstbd', $ec->Gld + $ec->Gfd + $ec->Gxd, 'G_(stb,d) = G_(l,d) + G_(f,d) + G_(x,d) = %% [kN]');
            $ec->txt('Destabilizáló erő:');
            $ec->def('Qdstd', $ec->gammadst*$ec->gammav*$ec->Vv, 'Q_(dst,d) = gamma_(dst)*gamma_v*V_v = %% [kN]');
            $ec->label($ec->Qdstd/$ec->Gstbd, 'Kihasználtság');
        $ec->info1();
       }
}
