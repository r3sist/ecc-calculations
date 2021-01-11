<?php declare(strict_types = 1);
/**
 * Analysis of achoring of PC beams according to Eurocodes - Calculation class for Statika framework
 * (c) Bence VÁNKOS | https://structure.hu | https://github.com/r3sist/ecc-calculations
 */

namespace Statika\Calculations;

use \H3;
use Statika\EurocodeInterface;

Class Anchor
{
    /**
     * @param Ec $ec
     * @throws \Statika\Material\InvalidMaterialNameException
     */
    public function calc(EurocodeInterface $ec): void
    {
        $ec->steelMaterialListBlock('anchorMaterialName','B500', ['', 'Tüske anyagminőség']);
        $ec->concreteMaterialListBlock('concreteMaterialName','C40/50');
        $ec->rebarList('D', 20, ['D', 'Csapátmérő'], '');
        $ec->numeric('e', ['e', 'Beton felületek közti hézag'], 10, 'mm', '');
        $ec->numeric('c1t', ['c_(1t)', 'Tüske tengely erő irányra merőlegesen'], 100, 'mm', 'Gerenda végétől tüske tengelyig vett érték');
        $ec->numeric('c2t', ['c_(2t)', 'Tüske tengely erő irányban'], 100, 'mm', 'Gerenda hossz irányú szélétől tüske tengelyig vett legkisebb érték');
        $ec->numeric('n', ['n', 'Csapok száma'], 2, '', '');
        $steelPlateDOF = ['0' => 0, '1' => 1, '2' => 2];
        $ec->lst('n_s', $steelPlateDOF, ['n_s', 'Acéllemez befogási tényező'], '0');
        $ec->numeric('GRd', ['gamma_(Rd)', ''], 1.3, '', '');
        $ec->numeric('FVEd', ['F_(V,Ed)', 'Mértékadó nyíróerő csap képen'], 30, 'kN', '');
        $states = [
            'Tartós, ideiglenes' => 1,
            'Földrengés' => 2
        ];
        $ec->lst('limit', $states, ['', 'Tervezési állapot'], 2);

        $ec->region0('r0');
            $ec->def('fyk', $ec->getMaterial((string)$ec->anchorMaterialName)->fy, 'f_(y,k) = %% [N/(mm^2)]', 'Csap karakterisztikus folyáshatára');
            if ($ec->getMaterial((string)$ec->anchorMaterialName)->fyd != '0') {
                $ec->def('fyd', $ec->getMaterial((string)$ec->anchorMaterialName)->fyd, 'f_(y,d) = %% [N/(mm^2)]', 'Csap tervezési folyáshatára');
            } else {
                $ec->def('fyd', $ec->getMaterial((string)$ec->anchorMaterialName)->fy, 'f_(y,d) = f_(y,bol t) = %% [N/(mm^2)]', 'Csap tervezési folyáshatára');
                $ec->txt('Betonacél helyett menetesszár: csavar anyaggal számolva. $gamma = 1.0 $');
            }
            // TODO check materials! fy fyd fyk for differenet material groups
            $ec->def('fck', $ec->getMaterial((string)$ec->concreteMaterialName)->fck, 'f_(c,k) = %% [N/(mm^2)]', 'Beton szilárdság karakterisztikus értéke');
            $ec->def('fcd', H3::n2($ec->fck/$ec::Gc), 'f_(c,d) = f_(c,k)/gamma_c = %% [N/(mm^2)]', 'Beton szilárdság tervezési értéke');
            $ec->def('c1', $ec->c1t - ($ec->D/2), 'c_1 = c_(1t) - D/2 = %% [mm]', 'Betontakarás erő irányára merőlegesen');
            $ec->def('c2', $ec->c2t - ($ec->D/2), 'c_2 = c_(2t) - D/2 = %% [mm]', 'Betontakarás erő irányával párhuzamosan');
            $ec->def('c1D', $ec->c1/$ec->D, 'c_(1D) = c_1/D = %%', '');
            $ec->def('c2D', $ec->c2/$ec->D, 'c_(2D) = c_2/D = %%', '');

            $c1x = 1;
            if ($ec->c1D < 3) {
                $c1x = 0;
            }
            $c2x = 1;
            if ($ec->c2D < 5) {
                $c2x = 0;
            }
            $ec->def('c1x', $c1x, 'c_(1x) = %%', '');
            $ec->def('c2x', $c2x, 'c_(2x) = %%', '');

            $locDb = [
                1 => 0.6 + $ec->c1D*(0.027*$ec->c2D + 0.1),
                2 => 0.9 + 0.08*$ec->c2D,
                3 => 0.6 + 0.233*$ec->c1D,
                4 => 1.3,
            ];
            $loc = 2*$ec->c2x + $ec->c1x + 1;
            $ec->def('loc', $loc, 'loc = 2*c_(2x) + c_(1x) + 1 = %%', 'Szabad szélhez viszonyított elhelyezkedés');

            $ec->def('alpha_c', $locDb[$loc], 'alpha_c = %%', '');

            $alpha_r = sqrt(2);
            if ($ec->n_s == 0) {
                $alpha_r = 1;
            }
            $ec->def('alpha_r', $alpha_r, 'alpha_r = %%', '');

            $epsilon_0 = 1;
            if ($ec->n_s == 0) {
                $epsilon_0 = 2;
            }
            $ec->def('epsilon_0', $epsilon_0, 'epsilon_0 = %%', '');

            $ec->def('alpha_0', $ec->alpha_c/$ec->GRd, 'alpha_0 = alpha_c/gamma_(Rd) = %%', '');
            $ec->def('epsilon', (((3*$ec->e)/$ec->epsilon_0)/$ec->D)*sqrt($ec->fck/$ec->fyk), 'epsilon = ((3*e)/epsilon_0)/D*sqrt(f_(ck)/f_(yk)) = %%', '');
            $ec->def('alpha_er', sqrt(($ec->alpha_r ** 2) + (($ec->epsilon * $ec->alpha_0) ** 2)) - $ec->epsilon*$ec->alpha_0, 'alpha_(er) = sqrt(alpha_r^2 + (epsilon*alpha_0)^2) - epsilon*alpha_0 = %%', '');

            $ec->math('l imit = '.$ec->limit);
            $ec->def('alpha_s', (-0.35)*$ec->limit + 1.35, 'alpha_s = -0.35*l imit + 1.35 = %%');

            if ($ec->n_s == 2) {
                $ec->def('f', $ec->fyd*(($ec->D ** 3) /(6*$ec->e)), 'f = (f_(yd)*D^3)/(6*e)) = %% [N]');
            } else {
                $ec->def('f', ($ec->D*$ec->D*sqrt($ec->fcd*$ec->fyd)*$ec->alpha_c*$ec->alpha_er)/$ec->GRd, 'f = (D^2*sqrt(f_(cd)*f_(yd))*alpha_c*alpha_(er))/gamma_(Rd) = %% [N]');
            }
        $ec->region1();

        $ec->def('FVRd', $ec->alpha_s*$ec->f/1000, 'F_(V,Rd) = alpha_s*f = %% [kN]', 'Egy nyírási csap ellenállása');
        $ec->success0();
            $ec->def('FVRdS', $ec->FVRd*$ec->n, 'F_(V,Rd,sum) = F_(V,Rd)*n = %% [kN]', 'Nyírási *csap kép* ellenállása');
            $ec->label($ec->FVEd/$ec->FVRdS, 'Csap kép kihasználtság');
        $ec->success1();

        $ec->h1('Csavarásból származó húzóerő felvétele');
        $ec->numeric('TEd', ['T_(Ed)', 'Csavarás'], 10, 'kNm');
        $ec->numeric('b', ['b', 'Tüskék távolsága'], 100, 'mm', 'Gerenda tengely irányra merőlegesen - Nyomaték erőkar');
        $ec->lst('nN', ['1' => 1, '2' => 2, '3' => 3], ['n_N', 'Tüske párok száma'], 1, 'Húzóerőt ennyi tüske veszi fel');
        $ec->def('NEd', $ec->TEd/($ec->b/1000), 'N_(Ed) = T_(Ed)/b = %% [kN]', 'Erőkar');
        $ec->def('NplRd', H3::n2($ec->nN*(($ec->A((float)$ec->D)*$ec->fyd)/($ec::GM0*1000))), 'N_(pl,Rd) = (D^2 *pi*f_(yd) )/(4*gamma_(M0)) = %% [kN]', '1 db tüske húzűsi ellenállása folyáshatárig');
        $ec->label(($ec->NEd/$ec->nN)/$ec->NplRd, 'Húzási kihasználtság tüske képre');
        $ec->txt('', '$(N_(Ed)/n_N)/N_(pl,Rd)$');
    }
}
