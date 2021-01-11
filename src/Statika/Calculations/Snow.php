<?php declare(strict_types = 1);
// Snow load analysis according to Eurocodes - Calculation class for ECC framework
// (c) Bence VÁNKOS | https://structure.hu | https://github.com/r3sist/ecc-calculations

namespace Calculation;

use \Base;
use \Ecc\Blc;
use \Ec\Ec;
use \H3;
use \resist\SVG\SVG;

Class Snow
{
    public function calc(Base $f3, Blc $blc, Ec $ec): void
    {
        $blc->math('psi_i = 0.5//0.2//0', 'Kombinációs tényezők');

        $blc->region0('baseData', 'Alap adatok');
            $blc->numeric('A', ['A', 'Tengerszint feletti magasság'], 400, 'm');
            $blc->numeric('C', ['C_e*C_t', 'Szél- és hőmérséklet összevont tényező'], 1, '', 'Védett terep esetén megfontolandó: (környező terepnél alacsonyabb fekvés): $C_e = 1.2$');

            if($f3->_A <= 400){
                $f3->_s_k = 1.25;
            } else {
                $f3->_s_k = 1.25 + ($f3->_A - 400)/400;
            }
            $blc->math('s_k = '.$f3->_s_k*$f3->_C.'[(kN)/m^2]', 'Felszíni hó karakterisztikus értéke');
        $blc->region1();

        $blc->numeric('alpha', ['alpha', 'Nyeregtető hajlása'], 0, '°');
        $blc->boo('bs', ['', 'Akadályozott hólecsúszás'], false, '');

        // =============================================================================================================
        $blc->region0('h1', 'Hóteher félnyereg-, nyereg- és összekapcsolódó nyeregtetők esetén');
            if($f3->_bs) {
                $_3060_mu1 = 0.8;
            } else {
                $_3060_mu1 = 0.8*(2 - ($f3->_alpha / 30));
            }

            $_mu = [
                '-30' => [
                    'mu1' => 0.8,
                    'mu2' => 0.8,
                    'mu3' => 0.8*(1 + ($f3->_alpha / 30))
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

            if($f3->_alpha <= 30) {
                $_find = '-30';
            } elseif($f3->_alpha > 30 && $f3->_alpha < 60) {
                $_find = '30-60';
            } else {
                $_find = '60-';
            }

            $f3->_mu_1 = H3::n2($_mu[$_find]['mu1']);
            $f3->_mu_2 = H3::n2($_mu[$_find]['mu2']);
            $f3->_mu_3 = H3::n2($_mu[$_find]['mu3']);
            $f3->_s_1 = H3::n2($f3->_mu_1*$f3->_s_k);
            $f3->_s_2 = H3::n2($f3->_mu_2*$f3->_s_k);
            $f3->_s_3 = H3::n2($f3->_mu_3*$f3->_s_k);

            $blc->math('mu_1 = '.$f3->_mu_1.'%%%mu_2 = '.$f3->_mu_2.'%%%mu_3 = '.$f3->_mu_3.'', 'Alaki tényezők');
            $blc->math('s_1 = '.$f3->_s_1.'%%%s_2 = '.$f3->_s_2.'%%%s_3 = '.$f3->_s_3.'[(kN)/m^2]', 'Tető hóteher karakterisztikus értékei');

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
            $svg->addText(10, 30, $f3->_s_1.' kN/m²');
            $svg->addText(10, 60, 'μ2=0.8');
            $svg->addText(220, 30, $f3->_s_1.' kN/m²');
            $svg->addText(220, 60, 'μ2=0.8');
            $svg->addText(320, 30, 0.5*$f3->_s_1.' kN/m²');
            $svg->addText(320, 60, '0.5×μ2=0.4');
            $svg->addText(490, 30, $f3->_s_1.' kN/m²');
            $svg->addText(490, 60, 'μ2=0.8');
            $svg->setColor('green');
            $svg->addLine(455, 10, 455, 190);
            $blc->svg($svg, false, 'Nyeregtető és félnyeregtető teherelrendezése és alaki tényezője');
            unset($svg);

        $blc->region1();

        // =============================================================================================================
        $blc->h1('Hózug', '');
        $blc->numeric('b_2', ['b_2', 'Vizsgált (alacsonyabb) tető szélessége'], 20, 'm', '', 'min_numeric,0.1');
        $f3->_b_red = $f3->_b_2;
        $blc->boo('b_real_bool', ['b_(real)', 'Eltérő felhalmozódási szélesség'], false, 'Hóteher értékének számítása ekkora szélességen $b_2$ helyett. Szétosztás $b_2$-n történik.');
        if ($f3->_b_real_bool) {
            $blc->numeric('b_real', ['b_(real)', 'Felhalmozódási szélesség'], 20, 'm', '', 'min_numeric,0.1');
        }
        $blc->numeric('h', ['h', 'Kiálló rész magassága, vagy tetők szintkülönbsége'], 1.2, 'm', '', 'min_numeric,0.05');

        $blc->h2('Hófelhalmozódás kiálló részek mellett, vízszinteshez közeli tetőkön', 'Helyi hóhatások a tetőn');
        $blc->region0('calc0');
            $blc->def('mu_w2',max(0.8, min((2*$f3->_h)/$f3->_s_k, 2)),'mu_(w2) = %%', 'Alaki tényező');
            $blc->def('l_s', min(15, max(5, 2*$f3->_h)),'l_s = %% [m]', 'Hózug szélessége');
        $blc->def('q_plus', ($f3->_mu_w2 - 0.8)*$f3->_s_k,'q_(plus) = %% [(kN)/m^2]', 'Megoszló terhelés többlet alap hóhoz képest');
        $blc->region1();

        $blc->def('q_sum', $f3->_mu_w2*$f3->_s_k,'q_(sum) = %% [(kN)/m^2]', 'Teljes megoszló terhelés sarokban');

        if ($f3->_q_sum > 0.8*$f3->_s_k) {
            $svg = new SVG(600, 170);
            $svg->setFill('#eeeeee');
            if ($f3->_b_2 < $f3->_l_s) {
                $svg->addPolygon([[10,120], [200,120], [200,90], [10,50], [10,120]]);
                $svg->setColor('red');
                $svg->addText(150, 75, $f3->_q_sum - $ec->proportion($f3->_l_s, $f3->_q_plus, $f3->_b_2).' kN/m²');
            } else {
                $svg->addPolygon([[10,120], [200,120], [200,100], [150,100], [10,50], [10,120]]);
                $svg->setColor('red');
                $svg->addDimH(10, 140, 140, $f3->_l_s);
                $svg->addText(150, 75, 0.8*$f3->_s_k.' kN/m²');
            }
            $svg->addText(10, 30, $f3->_q_sum.' kN/m²');

            $svg->setColor('black');
            $svg->addDimH(10, 190, 160, 'b2='.$f3->_b_2);
            $blc->svg($svg, false, 'Hófelhalmozódás kiálló részek mellett, vízszinteshez közeli tetőkön');
            unset($svg);
        } else {
            $blc->txt('Nem alakul ki hófelhalmozódás.');
        }


        // =============================================================================================================
        $blc->h2('Magasabb szerkezethez kapcsolódó tetők', 'Felhalmozódó hóteher átrendeződés után');

        if ($f3->_alpha > 0) {
            $blc->input('b_3', ['b_3', 'Lecsúszó hó szélessége'], 10, 'm');
        } else {
            $f3->_b_3 = 0;
            $blc->note('Lecsúszó hó szélessége lapostető miatt 0.');
        }
        $blc->input('b_1', ['b_1', 'Szomszédos épület szélessége'], 20, 'm');

        $blc->region0('calcData1');
            $blc->math('alpha = '.$f3->_alpha.'[deg]%%%h = '.$f3->_h.'[m]%%%mu_1 = '.$f3->_mu_1.'%%%s_k = '.$f3->_s_k.'[(kN)/m^2]', 'Alkalmazott értékek');
            $blc->def('gamma_set', 2, 'gamma_(set) = %% [(kN)/m^3]', 'Megülepedett hó térfogatsúlya');

            if ($f3->_alpha > 15) {
                $blc->def('mu_s', H3::n2($f3->_mu_1*($f3->_b_3/$f3->_l_s)), 'mu_s = mu_1(alpha)*(b_3/l_s) = %%', 'Felső tetőszakaszról lecsúszó mennyiséghez tartozó alaki tényező $(alpha gt 15 [deg])$');
            } else {
                $blc->def('mu_s', 0, 'mu_s = %%', 'Felső tetőszakaszról lecsúszó mennyiséghez tartozó alaki tényező $(alpha le 15 [deg])$');
            }
            $blc->def('mu_w', H3::n2(max(min(4.0, ($f3->_b_1 + $f3->_b_real)/(2*$f3->_h), ($f3->_gamma_set*$f3->_h)/$f3->_s_k), 0.8)), 'mu_w = max{(min{(4.0), ((b_1 + '.($f3->_b_real_bool?'b_(real)':'b_2').')/(2h)), ((gamma_(set)*h)/s_k):}), (0.8):} = %%', 'Szél átrendező hatásához tartozó alaki tényezője');
            $blc->def('l_s2', H3::n2(min(max(5, 2*$f3->_h), 15)), 'l_(s2) = min{(max{(5 [m]), (2*h):}), (15 [m]):} = %% [m]', 'Lecsúszó hó eloszlása');
            $blc->def('q_plus2', H3::n2((($f3->_mu_s + $f3->_mu_w) - 0.8)*$f3->_s_k),'q_(plus2) = %% [(kN)/m^2]', 'Megoszló terhelés többlet alap hóhoz képest');
        $blc->region1();

        $blc->def('q_sum2', H3::n2(($f3->_mu_s + $f3->_mu_w)*$f3->_s_k),'q_(sum2) = %% [(kN)/m^2]', 'Teljes megoszló terhelés sarokban');

        $svg = new SVG(600, 300);
        $svg->addPolygon([[10,250], [450,250], [450,200], [210,200], [210,80], [110,50], [10,80],]);
        $svg->addText(210, 230, 'Csatlakozó épület');
        $svg->addText(190, 90, 'α');

        $svg->setFill('#cccccc');
        $svg->addPolygon([[210,200], [450,200], [450,180], [210,180],]);
        $svg->setFill('#eeeeee');
        if ($f3->_b_2 < $f3->_l_s2) {
            $blc->txt('$b_2 < l_s$, ezért a tető másik szélén teher teljes értéke:');
            $qsum22 = H3::n2($ec->linterp(0, $f3->_q_sum2, $f3->_l_s2, $f3->_s_k*0.8, $f3->_b_2));
            $blc->txt('$q_(∑2,'.$f3->_b_2.'m) = '.$qsum22.' [(kN)/m^2]$');
            $blc->note('`linterp(0,'.$f3->_q_sum2.','.$f3->_l_s2.','.$f3->_s_k*0.8.','.$f3->_b_2.')`');
            $svg->addPolygon([[450,180], [450,160], [210,100], [210,180],]);
            if ($f3->_mu_s) {
                $svg->addLine(450, 170, 210, 130); // mu_s-mu_w osztó
            }
            $svg->setColor('red');
            $svg->addText(380, 130, $qsum22.' kN/m²');
        } else {
            $svg->addPolygon([[350,180], [210,100], [210,180],]);
            if ($f3->_mu_s) {
                $svg->addLine(350, 180, 210, 130); // mu_s-mu_w osztó
            }
            $svg->setColor('red');
            $svg->addText(400, 160, $f3->_s_k*0.8.' kN/m²');
            $svg->addDimH(210, 140, 20, 'ls='.$f3->_l_s2);
        }
        $svg->addText(220, 90, $f3->_q_sum2.' kN/m²');

        $svg->setFill('none');
        $svg->setColor('blue');
        $svg->addDimH(10, 200, 280, 'b1='.$f3->_b_1);
        $svg->addDimH(210, 240, 280, 'b2='.$f3->_b_2);
        $svg->addDimH(110, 100, 20, 'b3='.$f3->_b_3);
        $svg->addText(120, 40, 'Lecsúszó hó');
        $svg->addDimV(80, 120, 480, 'h='.$f3->_h);

        $svg->setColor('red');
        $svg->addText(140, 195, 'μ1='.$f3->_mu_1);
        $svg->addText(140, 160, 'μw='.$f3->_mu_w);
        $svg->addText(140, 120, 'μs='.$f3->_mu_s);
        $blc->svg($svg, false, 'Hófelhalmozódást is tartalmazó hóteher átrendeződés magasabb épülethez csatlakozó tető esetén');
        unset($svg);
    }
}