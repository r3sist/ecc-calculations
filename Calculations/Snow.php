<?php

namespace Calculation;

Class Snow extends \Ecc
{
    /**
     * @var $f3 \Base
     * @var $blc \Blc
     * @var $ec \Ec\Ec
     * @throws \Exception
     */
    public function calc(object $f3, object $blc, object $ec): void
    {
        $blc->input('A', 'Tengerszint feletti magasság', 400, 'm');
        $blc->input('C', '`C_e*C_t` Szél- és hőmérséklet összevont tényező', 1, '', 'Védett terep esetén megfontolandó: (környező terepnél alacsonyabb fekvés): \`C.e = 1.2\`');

        $blc->hr();

        if($f3->_A <= 400){
            $f3->_s_k = 1.25;
        } else {
            $f3->_s_k = 1.25 + ($f3->_A - 400)/400;
        }
        $blc->math('s_k = '.$f3->_s_k*$f3->_C.'[(kN)/m^2]', 'Felszíni hó karakterisztikus értéke');
        $blc->math('psi_0 = 0.5 %%%psi_1 = 0.2 %%%psi_2 = 0', 'Kombinációs tényezők');

        $blc->h1('Hóteher félnyereg-, nyereg- és összekapcsolódó nyeregtetők esetén');
        $blc->input('alpha', 'Nyeregtető hajlása', 0, '°');
        $blc->boo('bs', 'Akadályozott hólecsúszás', 0, '');

        if($f3->_bs) {
            $_3060_mu1 = 0.8;
        } else {
            $_3060_mu1 = 0.8*(2 - ($f3->_alpha / 30));
        }

        $_mu = array(
            '-30' => array(
                'mu1' => 0.8,
                'mu2' => 0.8,
                'mu3' => 0.8*(1 + ($f3->_alpha / 30))
            ),
            '30-60' => array(
                'mu1' => $_3060_mu1,
                'mu2' => $_3060_mu1,
                'mu3' => 1.6
            ),
            '60-' => array(
                'mu1' => 0,
                'mu2' => 0.8,
                'mu3' => false
            ),
        );

        if($f3->_alpha <= 30) {
            $_find = '-30';
        } elseif($f3->_alpha > 30 && $f3->_alpha < 60) {
            $_find = '30-60';
        } else {
            $_find = '60-';
        }

        $f3->_mu_1 = number_format($_mu[$_find]['mu1'], 2);
        $f3->_mu_2 = number_format($_mu[$_find]['mu2'], 2);
        $f3->_mu_3 = number_format($_mu[$_find]['mu3'], 2);
        $f3->_s_1 = number_format($f3->_mu_1*$f3->_s_k,2);
        $f3->_s_2 = number_format($f3->_mu_2*$f3->_s_k,2);
        $f3->_s_3 = number_format($f3->_mu_3*$f3->_s_k,2);

        $blc->math('mu_1 = '.$f3->_mu_1.'%%%mu_2 = '.$f3->_mu_2.'%%%mu_3 = '.$f3->_mu_3.'', 'Alaki tényezők');
        $blc->math('s_1 = '.$f3->_s_1.'%%%s_2 = '.$f3->_s_2.'%%%s_3 = '.$f3->_s_3.'[(kN)/m^2]', 'Tető hóteher karakterisztikus értékei');

        $blc->region0('r0','Félnyeregtető teherelrendezése és alaki tényezője');
        $write = array(
            array('size' => 14, 'x' => 125, 'y' => 135, 'text' => $f3->_alpha.'°'),
            array('size' => 14, 'x' => 285, 'y' => 35, 'text' => $f3->_mu_1.''),
            array('size' => 14, 'x' => 220, 'y' => 90, 'text' => $f3->_s_1.'[kN/m²]')
        );
        $blc->write('vendor/resist/ecc-calculations/canvas/snow1.jpg', $write, 'Félnyeregtető teherelrendezése és alaki tényezője');
        $blc->region1('r0');

        $blc->region0('r1','Nyeregtető teherelrendezése és alaki tényezője');
        $write = array(
            array('size' => 14, 'x' => 135, 'y' => 135, 'text' => $f3->_alpha.'°'),
            array('size' => 14, 'x' => 135, 'y' => 35, 'text' => 'μ₂='.$f3->_mu_2.''),
            array('size' => 14, 'x' => 135, 'y' => 90, 'text' => $f3->_s_2.'[kN/m²]'),
            array('size' => 14, 'x' => 425, 'y' => 35, 'text' => 'μ₂='.$f3->_mu_2.''),
            array('size' => 14, 'x' => 425, 'y' => 90, 'text' => $f3->_s_2.'[kN/m²]'),
            array('size' => 14, 'x' => 625, 'y' => 35, 'text' => '0.5×μ₂='. 0.5*$f3->_mu_2.''),
            array('size' => 14, 'x' => 625, 'y' => 90, 'text' => 0.5*$f3->_s_2.'[kN/m²]')
        );
        $blc->write('vendor/resist/ecc-calculations/canvas/snow2.jpg', $write, 'Nyeregtető teherelrendezése és alaki tényezője');
        $blc->region1('r1');

        $blc->h1('Hófelhalmozódás kiálló részek mellett, vízszinteshez közeli tetőkön', 'Helyi hóhatások a tetőn');
        $blc->input('h', 'Kiálló rész magassága', 1.2, 'm', '');
        $blc->def('mu_w2',max(0.8, min((2*$f3->_h)/$f3->_s_k, 2)),'mu_(w2) = %%', 'Alaki tényező');
        $blc->def('l_s', min(15, max(5, 2*$f3->_h)),'l_s = %% [m]', 'Hózug szélessége');
        $blc->def('q_sum', $f3->_mu_w2*$f3->_s_k,'q_(sum) = %% [(kN)/m^2]', 'Teljes megoszló terhelés sarokban');
        $blc->def('q_plus', ($f3->_mu_w2 - 0.8)*$f3->_s_k,'q_(plus) = %% [(kN)/m^2]', 'Megoszló terhelés többlet alap hóhoz képest');

        $write = array(
            array('size' => 14, 'x' => 10, 'y' => 35, 'text' => $f3->_q_sum.'kN/m²'),
            array('size' => 14, 'x' => 40, 'y' => 120, 'text' => $f3->_l_s.'m')
        );
        $blc->write('vendor/resist/ecc-calculations/canvas/snow0.jpg', $write, 'Hófelhalmozódás kiálló részek mellett, vízszinteshez közeli tetőkön');

        $blc->h1('Magasabb szerkezethez kapcsolódó tetők', 'Felhalmozódó hóteher átrendeződés után');
        $blc->math('alpha = '.$f3->_alpha.'°%%%h = '.$f3->_h.'[m]%%%mu_1 = '.$f3->_mu_1.'%%%s_k = '.$f3->_s_k.'[(kN)/m^2]', 'Alkalmazott értékek');
        $blc->def('gamma_set', 2, 'gamma_(set) = %% [(kN)/m^3]', 'Megülepedett hó térfogatsúlya');
        $blc->input('b_3', 'Lecsúszó hó hossza', 10, 'm');
        $blc->input('b_1', 'Szomszédos épület hossza', 20, 'm');
        $blc->input('b_2', 'Alacsonyabb épület hossza', 20, 'm');
        $f3->_mu_s = 0;
        if ($f3->_alpha > 15) {
            $f3->_mu_s = $f3->_mu_1*($f3->_b_3/$f3->_l_s);
        }
        $blc->math('mu_s = '.$f3->_mu_s.'', 'Alacsonybab épület alaki tényezője');
        $blc->def('mu_w', number_format(max(min(4.0, ($f3->_b_1 + $f3->_b_2)/(2*$f3->_h), ($f3->_gamma_set*$f3->_h)/$f3->_s_k), 0.8),2), 'mu_w = max(min(4.0; (b_1 + b_2)/(2h); (gamma_(set)*h)/s_k), 0.8) = %%', 'Szél átrendező hatásához tartozó alaki tényezője');
        $blc->def('l_s2', number_format(min(max(5, 2*$f3->_h), 15), 2), 'l_(s2) = min(max(5 [m]; 2*h), 15 [m]) = %% [m]', 'Lecsúszó hó eloszlása');
        $blc->def('q_sum2', number_format(($f3->_mu_s + $f3->_mu_w)*$f3->_s_k, 2),'q_(sum2) = %% [(kN)/m^2]', 'Teljes megoszló terhelés sarokban');
        $blc->def('q_plus2', number_format((($f3->_mu_s + $f3->_mu_w) - 0.8)*$f3->_s_k, 2),'q_(plus2) = %% [(kN)/m^2]', 'Megoszló terhelés többlet alap hóhoz képest');
        $write = array(
            array('size' => 14, 'x' => 90, 'y' => 35, 'text' => $f3->_mu_s.''),
            array('size' => 14, 'x' => 90, 'y' => 85, 'text' => $f3->_mu_w.''),
            array('size' => 14, 'x' => 140, 'y' => 165, 'text' => $f3->_b_3.'m'),
            array('size' => 14, 'x' => 255, 'y' => 165, 'text' => $f3->_l_s2.'m'),
            array('size' => 14, 'x' => 455, 'y' => 265, 'text' => $f3->_h.'m'),
            array('size' => 14, 'x' => 110, 'y' => 340, 'text' => $f3->_b_1.'m'),
            array('size' => 14, 'x' => 290, 'y' => 340, 'text' => $f3->_b_2.'m'),
            array('size' => 14, 'x' => 210, 'y' => 250, 'text' => $f3->_q_sum2.'kN/m²'),
            array('size' => 14, 'x' => 355, 'y' => 250, 'text' => $f3->_s_k*0.8.'kN/m²'),
        );
        $blc->write('vendor/resist/ecc-calculations/canvas/snow3.jpg', $write, 'Hófelhalmozódást is tartalmazó hóteher átrendeződés magasabb épülethez csatlakozó tető esetén');
        if ($f3->_b_2 < $f3->_l_s2) {
            $blc->html('`b_2 <= l_s`, ezért a tető szélén a megoszló terhelés: `'.number_format(($f3->_b_2*$f3->_q_plus2)/$f3->_l_s2 + $f3->_s_k*0.8, 2).'[(kN)/m^2]`');
        }
    }
}
