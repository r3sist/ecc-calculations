<?php declare(strict_types = 1);
/**
 * Snow load analysis according to Eurocodes  - Calculation class for Statika framework
 * (c) Bence VÁNKOS | https://structure.hu | https://github.com/r3sist/ecc-calculations
 */

namespace Statika\Calculations;

use \H3;
use \resist\SVG\SVG;
use Statika\EurocodeInterface;

Class Snow
{
    /**
     * @param Ec $ec
     * @throws \Exception
     */
    public function calc(EurocodeInterface $ec): void
    {
        $ec->math('psi_i = 0.5//0.2//0', 'Kombinációs tényezők');

        $ec->region0('baseData', 'Alap adatok');
            $ec->numeric('A', ['A', 'Tengerszint feletti magasság'], 400, 'm');
            $ec->numeric('C', ['C_e*C_t', 'Szél- és hőmérséklet összevont tényező'], 1, '', 'Védett terep esetén megfontolandó: (környező terepnél alacsonyabb fekvés): $C_e = 1.2$');

            if($ec->A <= 400){
                $ec->s_k = 1.25;
            } else {
                $ec->s_k = 1.25 + ($ec->A - 400)/400;
            }
            $ec->math('s_k = '.$ec->s_k*$ec->C.'[(kN)/m^2]', 'Felszíni hó karakterisztikus értéke');
        $ec->region1();

        $ec->numeric('alpha', ['alpha', 'Nyeregtető hajlása'], 0, '°');
        $ec->boo('bs', ['', 'Akadályozott hólecsúszás'], false, '');

        // =============================================================================================================
        $ec->region0('h1', 'Hóteher félnyereg-, nyereg- és összekapcsolódó nyeregtetők esetén');
            if($ec->bs) {
                $_3060_mu1 = 0.8;
            } else {
                $_3060_mu1 = 0.8*(2 - ($ec->alpha / 30));
            }

            $_mu = [
                '-30' => [
                    'mu1' => 0.8,
                    'mu2' => 0.8,
                    'mu3' => 0.8*(1 + ($ec->alpha / 30))
                ],
                '30-60' => [
                    'mu1' => $_3060_mu1,
                    'mu2' => $_3060_mu1,
                    'mu3' => 1.6
                ],
                '60-' => [
                    'mu1' => 0,
                    'mu2' => 0.8,
                    'mu3' => false
                ],
            ];

            if($ec->alpha <= 30) {
                $_find = '-30';
            } elseif($ec->alpha > 30 && $ec->alpha < 60) {
                $_find = '30-60';
            } else {
                $_find = '60-';
            }

            $ec->mu_1 = H3::n2($_mu[$_find]['mu1']);
            $ec->mu_2 = H3::n2($_mu[$_find]['mu2']);
            $ec->mu_3 = H3::n2($_mu[$_find]['mu3']);
            $ec->s_1 = H3::n2($ec->mu_1*$ec->s_k);
            $ec->s_2 = H3::n2($ec->mu_2*$ec->s_k);
            $ec->s_3 = H3::n2($ec->mu_3*$ec->s_k);

            $ec->math('mu_1 = '.$ec->mu_1.'%%%mu_2 = '.$ec->mu_2.'%%%mu_3 = '.$ec->mu_3.'', 'Alaki tényezők');
            $ec->math('s_1 = '.$ec->s_1.'%%%s_2 = '.$ec->s_2.'%%%s_3 = '.$ec->s_3.'[(kN)/m^2]', 'Tető hóteher karakterisztikus értékei');

            $svg = new SVG(600, 200);
            $svg->addPolygon([[10,180], [210,180], [210,140], [110,110], [10,140], [10,180]]);
            $svg->addPolygon([[220,180], [420,180], [420,140], [320,110], [220,140], [220,180]]);
            $svg->addPolygon([[490,180], [590,180], [590,140], [490,110], [490,180]]);
            $svg->setFill('#eeeeee');
            $svg->addRectangle(10, 80, 200, 20);
            $svg->addRectangle(220, 80, 100, 20);
            $svg->addRectangle(320, 90, 100, 10);
            $svg->addRectangle(490, 80, 100, 20);
            $svg->setColor('red');
            $svg->addText(10, 30, $ec->s_1.' kN/m²');
            $svg->addText(10, 60, 'μ2=0.8');
            $svg->addText(220, 30, $ec->s_1.' kN/m²');
            $svg->addText(220, 60, 'μ2=0.8');
            $svg->addText(320, 30, 0.5*$ec->s_1.' kN/m²');
            $svg->addText(320, 60, '0.5×μ2=0.4');
            $svg->addText(490, 30, $ec->s_1.' kN/m²');
            $svg->addText(490, 60, 'μ2=0.8');
            $svg->setColor('green');
            $svg->addLine(455, 10, 455, 190);
            $ec->svg($svg, false, 'Nyeregtető és félnyeregtető teherelrendezése és alaki tényezője');
            unset($svg);

        $ec->region1();

        // =============================================================================================================
        $ec->h1('Hózug', '');
        $ec->numeric('b_2', ['b_2', 'Vizsgált (alacsonyabb) tető szélessége'], 20, 'm', '', 'min_numeric,0.1');
        $ec->b_red = $ec->b_2;
        $ec->boo('b_real_bool', ['b_(real)', 'Eltérő felhalmozódási szélesség'], false, 'Hóteher értékének számítása ekkora szélességen $b_2$ helyett. Szétosztás $b_2$-n történik.');
        $ec->b_real = 0;
        if ($ec->b_real_bool) {
            $ec->numeric('b_real', ['b_(real)', 'Felhalmozódási szélesség'], 20, 'm', '', 'min_numeric,0.1');
        }
        $ec->numeric('h', ['h', 'Kiálló rész magassága, vagy tetők szintkülönbsége'], 1.2, 'm', '', 'min_numeric,0.05');

        $ec->h2('Hófelhalmozódás kiálló részek mellett, vízszinteshez közeli tetőkön', 'Helyi hóhatások a tetőn');
        $ec->region0('calc0');
            $ec->def('mu_w2',max(0.8, min((2*$ec->h)/$ec->s_k, 2)),'mu_(w2) = %%', 'Alaki tényező');
            $ec->def('l_s', min(15, max(5, 2*$ec->h)),'l_s = %% [m]', 'Hózug szélessége');
        $ec->def('q_plus', ($ec->mu_w2 - 0.8)*$ec->s_k,'q_(plus) = %% [(kN)/m^2]', 'Megoszló terhelés többlet alap hóhoz képest');
        $ec->region1();

        $ec->def('q_sum', $ec->mu_w2*$ec->s_k,'q_(sum) = %% [(kN)/m^2]', 'Teljes megoszló terhelés sarokban');

        if ($ec->q_sum > 0.8*$ec->s_k) {
            $svg = new SVG(600, 170);
            $svg->setFill('#eeeeee');
            if ($ec->b_2 < $ec->l_s) {
                $svg->addPolygon([[10,120], [200,120], [200,90], [10,50], [10,120]]);
                $svg->setColor('red');
                $svg->addText(150, 75, $ec->q_sum - $ec->proportion($ec->l_s, $ec->q_plus, $ec->b_2).' kN/m²');
            } else {
                $svg->addPolygon([[10,120], [200,120], [200,100], [150,100], [10,50], [10,120]]);
                $svg->setColor('red');
                $svg->addDimH(10, 140, 140, $ec->l_s);
                $svg->addText(150, 75, 0.8*$ec->s_k.' kN/m²');
            }
            $svg->addText(10, 30, $ec->q_sum.' kN/m²');

            $svg->setColor('black');
            $svg->addDimH(10, 190, 160, 'b2='.$ec->b_2);
            $ec->svg($svg, false, 'Hófelhalmozódás kiálló részek mellett, vízszinteshez közeli tetőkön');
            unset($svg);
        } else {
            $ec->txt('Nem alakul ki hófelhalmozódás.');
        }


        // =============================================================================================================
        $ec->h2('Magasabb szerkezethez kapcsolódó tetők', 'Felhalmozódó hóteher átrendeződés után');

        if ($ec->alpha > 0) {
            $ec->input('b_3', ['b_3', 'Lecsúszó hó szélessége'], 10, 'm');
        } else {
            $ec->b_3 = 0;
            $ec->note('Lecsúszó hó szélessége lapostető miatt 0.');
        }
        $ec->input('b_1', ['b_1', 'Szomszédos épület szélessége'], 20, 'm');

        $ec->region0('calcData1');
            $ec->math('alpha = '.$ec->alpha.'[deg]%%%h = '.$ec->h.'[m]%%%mu_1 = '.$ec->mu_1.'%%%s_k = '.$ec->s_k.'[(kN)/m^2]', 'Alkalmazott értékek');
            $ec->def('gamma_set', 2, 'gamma_(set) = %% [(kN)/m^3]', 'Megülepedett hó térfogatsúlya');

            if ($ec->alpha > 15) {
                $ec->def('mu_s', H3::n2($ec->mu_1*($ec->b_3/$ec->l_s)), 'mu_s = mu_1(alpha)*(b_3/l_s) = %%', 'Felső tetőszakaszról lecsúszó mennyiséghez tartozó alaki tényező $(alpha gt 15 [deg])$');
            } else {
                $ec->def('mu_s', 0, 'mu_s = %%', 'Felső tetőszakaszról lecsúszó mennyiséghez tartozó alaki tényező $(alpha le 15 [deg])$');
            }
            $ec->def('mu_w', H3::n2(max(min(4.0, ($ec->b_1 + $ec->b_real)/(2*$ec->h), ($ec->gamma_set*$ec->h)/$ec->s_k), 0.8)), 'mu_w = max{(min{(4.0), ((b_1 + '.($ec->b_real_bool?'b_(real)':'b_2').')/(2h)), ((gamma_(set)*h)/s_k):}), (0.8):} = %%', 'Szél átrendező hatásához tartozó alaki tényezője');
            $ec->def('l_s2', H3::n2(min(max(5, 2*$ec->h), 15)), 'l_(s2) = min{(max{(5 [m]), (2*h):}), (15 [m]):} = %% [m]', 'Lecsúszó hó eloszlása');
            $ec->def('q_plus2', H3::n2((($ec->mu_s + $ec->mu_w) - 0.8)*$ec->s_k),'q_(plus2) = %% [(kN)/m^2]', 'Megoszló terhelés többlet alap hóhoz képest');
        $ec->region1();

        $ec->def('q_sum2', H3::n2(($ec->mu_s + $ec->mu_w)*$ec->s_k),'q_(sum2) = %% [(kN)/m^2]', 'Teljes megoszló terhelés sarokban');

        $svg = new SVG(600, 300);
        $svg->addPolygon([[10,250], [450,250], [450,200], [210,200], [210,80], [110,50], [10,80],]);
        $svg->addText(210, 230, 'Csatlakozó épület');
        $svg->addText(190, 90, 'α');

        $svg->setFill('#cccccc');
        $svg->addPolygon([[210,200], [450,200], [450,180], [210,180],]);
        $svg->setFill('#eeeeee');
        if ($ec->b_2 < $ec->l_s2) {
            $ec->txt('$b_2 < l_s$, ezért a tető másik szélén teher teljes értéke:');
            $qsum22 = H3::n2($ec->linterp(0, $ec->q_sum2, $ec->l_s2, $ec->s_k*0.8, $ec->b_2));
            $ec->txt('$q_(∑2,'.$ec->b_2.'m) = '.$qsum22.' [(kN)/m^2]$');
            $ec->note('`linterp(0,'.$ec->q_sum2.','.$ec->l_s2.','.$ec->s_k*0.8.','.$ec->b_2.')`');
            $svg->addPolygon([[450,180], [450,160], [210,100], [210,180],]);
            if ($ec->mu_s) {
                $svg->addLine(450, 170, 210, 130); // mu_s-mu_w osztó
            }
            $svg->setColor('red');
            $svg->addText(380, 130, $qsum22.' kN/m²');
        } else {
            $svg->addPolygon([[350,180], [210,100], [210,180],]);
            if ($ec->mu_s) {
                $svg->addLine(350, 180, 210, 130); // mu_s-mu_w osztó
            }
            $svg->setColor('red');
            $svg->addText(400, 160, $ec->s_k*0.8.' kN/m²');
            $svg->addDimH(210, 140, 20, 'ls='.$ec->l_s2);
        }
        $svg->addText(220, 90, $ec->q_sum2.' kN/m²');

        $svg->setFill('none');
        $svg->setColor('blue');
        $svg->addDimH(10, 200, 280, 'b1='.$ec->b_1);
        $svg->addDimH(210, 240, 280, 'b2='.$ec->b_2);
        $svg->addDimH(110, 100, 20, 'b3='.$ec->b_3);
        $svg->addText(120, 40, 'Lecsúszó hó');
        $svg->addDimV(80, 120, 480, 'h='.$ec->h);

        $svg->setColor('red');
        $svg->addText(140, 195, 'μ1='.$ec->mu_1);
        $svg->addText(140, 160, 'μw='.$ec->mu_w);
        $svg->addText(140, 120, 'μs='.$ec->mu_s);
        $ec->svg($svg, false, 'Hófelhalmozódást is tartalmazó hóteher átrendeződés magasabb épülethez csatlakozó tető esetén');
        unset($svg);
    }
}
