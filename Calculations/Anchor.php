<?php

namespace Calculation;

Class Anchor extends \Ecc
{

    /**
     * @var $f3 \Base
     * @var $blc \Blc
     * @var $ec \Ec\Ec
     * @throws \Exception
     */
    public function calc(object $f3, object $blc, object $ec): void
    {
        $ec->matList('aMat','B500', ['', 'Tüske anyagminőség']);
        $ec->matList('cMat','C40/50', ['', 'Beton anyagminőség']);
        $blc->numeric('D', ['D', 'Csapátmérő'], 20, 'mm', '');
        $blc->numeric('e', ['e', 'Beton felületek közti hézag'], 10, 'mm', '');
        $blc->numeric('c1t', ['c_(1t)', 'Tüske tengely erő irányra merőlegesen'], 100, 'mm', '');
        $blc->numeric('c2t', ['c_(2t)', 'Tüske tengely erő irányban'], 100, 'mm', '');
        $blc->numeric('n', ['n', 'Csapok száma'], 2, '', '');
        $steelPlateDOF = [
            '0' => 0,
            '1' => 1,
            '2' => 2
        ];
        $blc->lst('n_s', $steelPlateDOF, ['n_s', 'Acéllemez befogási tényező'], '0');
        $blc->numeric('GRd', ['gamma_(Rd)', ''], 1.3, '', '');
        $blc->numeric('FVEd', ['F_(V,Ed)', 'Mértékadó nyíróerő csap képen'], 30, 'kN', '');
        $states = [
            'Tartós, ideiglenes' => 1,
            'Földrengés' => 2
        ];
        $blc->lst('limit', $states, ['', 'Tervezési állapot'], 2);

        $blc->region0('r0', 'Számítások');
            $blc->def('fyk', $ec->matProp($f3->_aMat,'fy'), 'f_(y,k) = %% [N/(mm^2)]', 'Csap karakterisztikus folyáshatára');
            $blc->def('fyd', $ec->matProp($f3->_aMat,'fyd'), 'f_(y,d) = %% [N/(mm^2)]', 'Csap tervezési folyáshatára');
            $blc->def('fck', $ec->matProp($f3->_cMat,'fck'), 'f_(c,k) = %% [N/(mm^2)]', 'Beton szilárdság karakterisztikus értéke');
            $blc->def('fcd', \H3::n2($f3->_fck/$f3->__Gc), 'f_(c,d) = f_(c,k)/gamma_c = %% [N/(mm^2)]', 'Beton szilárdság tervezési értéke');
            $blc->def('c1', $f3->_c1t - ($f3->_D/2), 'c_1 = c_(1t) - D/2 = %% [mm]', '');
            $blc->def('c2', $f3->_c2t - ($f3->_D/2), 'c_2 = c_(2t) - D/2 = %% [mm]', '');
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

            $locDb = [
                1 => 0.6 + $f3->_c1D*(0.027*$f3->_c2D + 0.1),
                2 => 0.9 + 0.08*$f3->_c2D,
                3 => 0.6 + 0.233*$f3->_c1D,
                4 => 1.3,
            ];
            $loc = 2*$f3->_c2x + $f3->_c1x + 1;
            $blc->def('loc', $loc, 'loc = 2*c_(2x) + c_(1x) + 1 = %%', 'Szabad szélhez viszonyított elhelyezkedés');

            $blc->def('alpha_c', $locDb[$loc], 'alpha_c = %%', '');

            $alpha_r = sqrt(2);
            if ($f3->_n_s == 0) {
                $alpha_r = 1;
            }
            $blc->def('alpha_r', $alpha_r, 'alpha_r = %%', '');

            $epsilon_0 = 1;
            if ($f3->_n_s == 0) {
                $epsilon_0 = 2;
            }
            $blc->def('epsilon_0', $epsilon_0, 'epsilon_0 = %%', '');

            $blc->def('alpha_0', $f3->_alpha_c/$f3->_GRd, 'alpha_0 = alpha_c/gamma_(Rd) = %%', '');
            $blc->def('epsilon', (((3*$f3->_e)/$f3->_epsilon_0)/$f3->_D)*sqrt($f3->_fck/$f3->_fyk), 'epsilon = ((3*e)/epsilon_0)/D*sqrt(f_(ck)/f_(yk)) = %%', '');
            $blc->def('alpha_er', sqrt(pow($f3->_alpha_r, 2) + pow($f3->_epsilon*$f3->_alpha_0, 2)) - $f3->_epsilon*$f3->_alpha_0, 'alpha_(er) = sqrt(alpha_r^2 + (epsilon*alpha_0)^2) - epsilon*alpha_0 = %%', '');

            $blc->math('l imit = '.$f3->_limit);
            $blc->def('alpha_s', (-0.35)*$f3->_limit + 1.35, 'alpha_s = -0.35*l imit + 1.35 = %%');

            if ($f3->_n_s == 2) {
                $blc->def('f', $f3->_fyd*(pow($f3->_D, 3)/(6*$f3->_e)), 'f = (f_(yd)*D^3)/(6*e)) = %% [N]');
            } else {
                $blc->def('f', ($f3->_D*$f3->_D*sqrt($f3->_fcd*$f3->_fyd)*$f3->_alpha_c*$f3->_alpha_er)/$f3->_GRd, 'f = (D^2*sqrt(f_(cd)*f_(yd))*alpha_c*alpha_(er))/gamma_(Rd) = %% [N]');
            }
        $blc->region1('r0');

        $blc->def('FVRd', $f3->_alpha_s*$f3->_f/1000, 'F_(V,Rd) = alpha_s*f = %% [kN]', 'Egy nyírási csap ellenállása');
        $blc->success0('s0');
            $blc->def('FVRdS', $f3->_FVRd*$f3->_n, 'F_(V,Rd,sum) = F_(V,Rd)*n = %% [kN]', 'Nyírási *csap kép* ellenállása');
            $blc->label($f3->_FVEd/$f3->_FVRdS, 'Csap kép kihasználtság');
        $blc->success1('s0');

        $blc->h1('Csavarásból származó húzóerő felvétele');
        $blc->numeric('TEd', ['T_(Ed)', 'Csavarás'], 10, 'kNm');
        $blc->numeric('b', ['b', 'Tüskék távolsága'], 100, 'mm', 'Gerenda tengely irányra merőlegesen - Nyomaték erőkar');
        $blc->lst('nN', ['1' => 1, '2' => 2, '3' => 3], ['n_N', 'Tüske párok száma'], 1, 'Húzóerőt ennyi tüske veszi fel');
        $blc->def('NEd', $f3->_TEd/($f3->_b/1000), 'N_(Ed) = T_(Ed)/b = %% [kN]', 'Erőkar');
        $blc->def('NplRd', \H3::n2($f3->_nN*(($ec->A($f3->_D)*$f3->_fyd)/($f3->__GM0*1000))), 'N_(pl,Rd) = (D^2 *pi*f_(yd) )/(4*gamma_(M0)) = %% [kN]', '1 db tüske húzűsi ellenállása folyáshatárig');
        $blc->label(($f3->_NEd/$f3->_nN)/$f3->_NplRd, 'Húzási kihasználtság tüske képre');
        $blc->txt(false, '$(N_(Ed)/n_N)/N_(pl,Rd)$');
    }
}
