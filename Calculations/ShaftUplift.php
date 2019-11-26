<?php

namespace Calculation;

/**
 * Analysis of RC shaft uplifting according to Eurocodes - Calculation class for ECC framework
 *
 * (c) Bence VÁNKOS
 * https:// structure.hu
 */

Class ShaftUplift
{
    public function calc(\Base $f3, \Ecc\Blc $blc, \Ec\Ec $ec): void
    {
        $blc->numeric('gammac', ['gamma_c', 'Beton fajsúly'], 24, 'kN/m3');
        $blc->def('gammav', 10, 'gamma_v = %% [(kN)/m^3]');
        $blc->numeric('gammastb', ['gamma_(stb)', 'Stabilizáló erők biztonsági tényezője'], 0.9, '');
        $blc->numeric('gammadst', ['gamma_(dst)', 'Destabilizáló erők biztonsági tényezője'], 1.1, '');
        $blc->numeric('Gxk', ['G_(x,k)', 'Egyéb stabilizáló jellegű teher'], 0, 'kN', 'Karakterisztikus érték');

        $blc->txt('Szerkezeti vastagságok:');
        $blc->numeric('vf', ['v_f', 'Aknafal vastagság'], 400, 'mm', '');
        $blc->numeric('vl', ['v_l', 'Aknafenék vastagság'], 400, 'mm', '');
        $blc->numeric('xl', ['x_l', 'Aknafenék túlnyúlás'], 0, 'mm', '');
        $blc->txt('Belső (tiszta) akna méretek:');
        $blc->numeric('a', ['a', ''], 1600, 'mm', '');
        $blc->numeric('b', ['b', ''], 2800, 'mm', '');
        $blc->numeric('h', ['h', 'Mélység'], 2200, 'mm', 'Padló felső síkjától fenéklemez felső síkjáig');
        $blc->numeric('delta', ['Delta', 'Vízszint különbség padlótól'], 400, 'mm', '(+) padló felett -> víznyomás; (-) padló alatt alacsonyabb szinten');

        $blc->md('**Számítások:**');
        $blc->txt('Fal súlya:');
        $blc->def('Kf', 2*($f3->_a + $f3->_b + 2*$f3->_vf), 'K_f = 2*(a + b + 2*v_f) = %% [mm]', 'Fal kerülete');
        $blc->def('Vf', $f3->_vf*$f3->_h*$f3->_Kf*0.000000001, 'V_f = v_f*h*K_f = %% [m^3]', 'Fal térfogata');
        $blc->def('Gfd', $f3->_gammastb*$f3->_gammac*$f3->_Vf, 'G_(f,d) = gamma_(stb)*gamma_c*V_f = %% [kN]', 'Fal súlyának tervezési értéke');
        $blc->txt('Lemez súlya:');
        $blc->def('Al', ($f3->_b + 2*$f3->_xl + 2*$f3->_vf)*($f3->_a + 2*$f3->_xl + 2*$f3->_vf)/1000000, 'A_l = (b+2*x_l+2*v_f)*(a+2*x_l+2*v_f) = %% [m^2]', 'Lemez területe');
        $blc->def('Vl', $f3->_Al*($f3->_vl/1000), 'V_l = A_l*v_l = %% [m^3]', 'Lemez térfogata');
        $blc->def('Gld', $f3->_gammastb*$f3->_gammac*$f3->_Vl, 'G_(l,d) = gamma_(stb)*gamma_c*V_l = %% [kN]', 'Lemez súlyának tervezési értéke');
        $blc->def('Gxd', $f3->_gammastb*$f3->_Gxk, 'G_(x,d) = %% [kN]');
        $blc->txt('Víz felhajtó ereje:');
        $blc->def('hv', ($f3->_delta + $f3->_h + $f3->_vl)/1000, 'h_v = Delta*h*v_l = %% [m]', 'Vízoszlop magassága');
        $blc->def('Av', ($f3->_b + 2*$f3->_vf)*($f3->_a + 2*$f3->_vf)/1000000, 'A_v = (b+2v_f)*(a+2v_f) = %% [m^2]', 'Vízoszlop területe');
        $blc->def('Vv', $f3->_Av*$f3->_hv + ($f3->_Al - $f3->_Av)*($f3->_vl/1000), 'V_v = A_v*h_v + (A_l - A_v)*v_l = %% [m^3]', 'Szerkezet által kiszorított térfogat');
        $blc->note('A lemezszélesítésre kerülő föld súlya nem vesz részt a stabilizáló erőkben, de a szélesítés tömege és kiszorítása igen.');

        $blc->info0();
            $blc->txt('Stabilizáló erő:');
            $blc->def('Gstbd', $f3->_Gld + $f3->_Gfd + $f3->_Gxd, 'G_(stb,d) = G_(l,d) + G_(f,d) + G_(x,d) = %% [kN]');
            $blc->txt('Destabilizáló erő:');
            $blc->def('Qdstd', $f3->_gammadst*$f3->_gammav*$f3->_Vv, 'Q_(dst,d) = gamma_(dst)*gamma_v*V_v = %% [kN]');
            $blc->label($f3->_Qdstd/$f3->_Gstbd, 'Kihasználtság');
        $blc->info1();
       }
}
