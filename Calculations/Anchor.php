<?php

namespace Calculation;

Class Anchor extends \Ecc
{

    public function calc($f3)
    {
        $ec = \Ec::instance();
        $blc = \Blc::instance();

        $ec->matList('aMat','B500', 'Tüske anyagminőség');
        $ec->matList('cMat','C30/40', 'Beton anyagminőség');
        $blc->input('D', 'Csapátmérő', '20', 'mm', '');
        $blc->input('e', 'Beton felületek közti hézag', '10', 'mm', '');
        $blc->input('c_1t', '`c_(1t)` Tüske tengely erő irányra merőlegesen', '100', 'mm', '');
        $blc->input('c_2t', '`c_(2t)` Tüske tengely erő irányban', '100', 'mm', '');
        $blc->input('n', 'Csapok száma', '2', '', '');
        $steelPlateDOF = [
            '0' => 0,
            '1' => 1,
            '2' => 2
        ];
        $blc->lst('n_s', $steelPlateDOF, 'Acéllemez befogási tényező', '0');
        $blc->input('GRd', '`gamma_(Rd)`', '1.3', '', '');
        $blc->input('FVEd', '`F_(V,Rd)` Mértékadó nyíróerő csapban', '30', 'kN', '');
        $state = [
            'Tartós, ideiglenes' => 1,
            'Földrengés' => 2
        ];
        $blc->lst('n_s', $state, 'Tervezési állapot', 'Földrengés');

        $blc->region0('r0', 'Számítások');
        $blc->def('fyk', $ec->matProp($f3->_aMat,'fy'), 'f_(y,k) = %% MPa', 'Csap karakterisztikus folyáshatára');
        $blc->def('fyd', $ec->matProp($f3->_aMat,'fyd'), 'f_(y,d) = %% MPa', 'Csap tervezési folyáshatára');
        $blc->def('fck', $ec->matProp($f3->_cMat,'fck'), 'f_(c,k) = %% MPa', 'Beton szilárdság karakterisztikus értéke');
        $blc->def('fcd', $f3->_fck/$f3->__Gc, 'f_(c,d) = f_(c,k)/gamma_c = %% MPa', 'Beton szilárdság tervezési értéke');
        $blc->def('c1', $f3->_c1t - ($f3->_D/2), 'c_1 = c_(1t) - D/2 = %% mm', '');
        $blc->def('c2', $f3->_c2t - ($f3->_D/2), 'c_2 = c_(2t) - D/2 = %% mm', '');
        $blc->def('c1D', $f3->_c1/$f3->_D, 'c_(1D) = c_1/D = %%', '');
        $blc->def('c2D', $f3->_c2/$f3->_D, 'c_(2D) = c_2/D = %%', '');

        $c1x = 1;
        if ($f3->_c1D < 3) {
            $c1x = 0;
        }
        $c2x = 1;
        if ($f3->_c2D < 5) {
            $c2x = 0;
        }
        $blc->def('c1x', $c1x, 'c_(1x) = %%', '');
        $blc->def('c2x', $c2x, 'c_(2x) = %%', '');

        $blc->region1('r0');
    }
}
