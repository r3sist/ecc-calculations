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
        $blc->region0('baseData', 'Alap adatok');
            $blc->numeric('A', ['A', 'Tengerszint feletti magasság'], 400, 'm');
            $blc->numeric('C', ['C_e*C_t', 'Szél- és hőmérséklet összevont tényező'], 1, '', 'Védett terep esetén megfontolandó: (környező terepnél alacsonyabb fekvés): $C_e = 1.2$');

            if($f3->_A <= 400){
                $f3->_s_k = 1.25;
            } else {
                $f3->_s_k = 1.25 + ($f3->_A - 400)/400;
            }
            $blc->math('s_k = '.$f3->_s_k*$f3->_C.'[(kN)/m^2]', 'Felszíni hó karakterisztikus értéke');
            $blc->math('psi_0 = 0.5 %%%psi_1 = 0.2 %%%psi_2 = 0', 'Kombinációs tényezők');
        $blc->region1('baseData');

        // =============================================================================================================
        $blc->h1('Hóteher félnyereg-, nyereg- és összekapcsolódó nyeregtetők esetén');
        $blc->numeric('alpha', ['alpha', 'Nyeregtető hajlása'], 0, '$[deg]$');
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

        $f3->_mu_1 = \H3::n2($_mu[$_find]['mu1']);
        $f3->_mu_2 = \H3::n2($_mu[$_find]['mu2']);
        $f3->_mu_3 = \H3::n2($_mu[$_find]['mu3']);
        $f3->_s_1 = \H3::n2($f3->_mu_1*$f3->_s_k);
        $f3->_s_2 = \H3::n2($f3->_mu_2*$f3->_s_k);
        $f3->_s_3 = \H3::n2($f3->_mu_3*$f3->_s_k);

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

        // =============================================================================================================
        $blc->h1('Hófelhalmozódás kiálló részek mellett, vízszinteshez közeli tetőkön', 'Helyi hóhatások a tetőn');
        $blc->numeric('h', ['h', 'Kiálló rész magassága'], 1.2, 'm', '');
        $blc->def('mu_w2',max(0.8, min((2*$f3->_h)/$f3->_s_k, 2)),'mu_(w2) = %%', 'Alaki tényező');
        $blc->def('l_s', min(15, max(5, 2*$f3->_h)),'l_s = %% [m]', 'Hózug szélessége');
        $blc->def('q_sum', $f3->_mu_w2*$f3->_s_k,'q_(sum) = %% [(kN)/m^2]', 'Teljes megoszló terhelés sarokban');
        $blc->def('q_plus', ($f3->_mu_w2 - 0.8)*$f3->_s_k,'q_(plus) = %% [(kN)/m^2]', 'Megoszló terhelés többlet alap hóhoz képest');

/*
         // Legacy write method
        $write = array(
            array('size' => 14, 'x' => 10, 'y' => 35, 'text' => $f3->_q_sum.'kN/m²'),
            array('size' => 14, 'x' => 40, 'y' => 120, 'text' => $f3->_l_s.'m')
        );
        $blc->write('vendor/resist/ecc-calculations/canvas/snow0.jpg', $write, 'Hófelhalmozódás kiálló részek mellett, vízszinteshez közeli tetőkön');
*/
        $svgSnow0 = new \resist\SVG(300, 170);
        $svgSnow0->addPolygon('10,120 200,120 200,100 150,100 10,50 10,120');
        $svgSnow0->setColor('red');
        $svgSnow0->addDimH(10, 140, 150, $f3->_l_s.' m');
        $svgSnow0->addText(10, 30, $f3->_q_sum.' kN/m²');
        $svgSnow0->addText(150, 80, 0.8*$f3->_s_k.' kN/m²');
        $blc->svg($svgSnow0, false, 'Hófelhalmozódás kiálló részek mellett, vízszinteshez közeli tetőkön');

        // =============================================================================================================
        $blc->h1('Magasabb szerkezethez kapcsolódó tetők', 'Felhalmozódó hóteher átrendeződés után');
        $blc->math('alpha = '.$f3->_alpha.'[deg]%%%h = '.$f3->_h.'[m]%%%mu_1 = '.$f3->_mu_1.'%%%s_k = '.$f3->_s_k.'[(kN)/m^2]', 'Alkalmazott értékek');
        $blc->def('gamma_set', 2, 'gamma_(set) = %% [(kN)/m^3]', 'Megülepedett hó térfogatsúlya');
        $blc->input('b_3', ['b_3', 'Lecsúszó hó hossza'], 10, 'm');
        $blc->input('b_1', ['b_1', 'Szomszédos épület hossza'], 20, 'm');
        $blc->input('b_2', ['b_2', 'Alacsonyabb épület hossza'], 20, 'm');
        if ($f3->_alpha > 15) {
            $blc->def('mu_s', \H3::n2($f3->_mu_1*($f3->_b_3/$f3->_l_s)), 'mu_s = mu_1(alpha)*(b_3/l_s) = %%', 'Felső tetőszakaszról lecsúszó mennyiséghez tartozó alaki tényező');
        } else {
            $blc->def('mu_s', 0, 'mu_s = %%', 'Felső tetőszakaszról lecsúszó mennyiséghez tartozó alaki tényező $(alpha < 15 [deg])$');
        }
        $blc->def('mu_w', \H3::n2(max(min(4.0, ($f3->_b_1 + $f3->_b_2)/(2*$f3->_h), ($f3->_gamma_set*$f3->_h)/$f3->_s_k), 0.8)), 'mu_w = max{(min{(4.0), ((b_1 + b_2)/(2h)), ((gamma_(set)*h)/s_k):}), (0.8):} = %%', 'Szél átrendező hatásához tartozó alaki tényezője');
        $blc->def('l_s2', \H3::n2(min(max(5, 2*$f3->_h), 15)), 'l_(s2) = min{(max{(5 [m]), (2*h):}), (15 [m]):} = %% [m]', 'Lecsúszó hó eloszlása');
        $blc->def('q_plus2', \H3::n2((($f3->_mu_s + $f3->_mu_w) - 0.8)*$f3->_s_k),'q_(plus2) = %% [(kN)/m^2]', 'Megoszló terhelés többlet alap hóhoz képest');
        $blc->success0('qsum2');
            $blc->def('q_sum2', \H3::n2(($f3->_mu_s + $f3->_mu_w)*$f3->_s_k),'q_(sum2) = %% [(kN)/m^2]', 'Teljes megoszló terhelés sarokban');
            $write = array(
                array('size' => 14, 'x' => 390, 'y' => 175, 'text' => $f3->_l_s2.'m'),
                array('size' => 14, 'x' => 130, 'y' => 230, 'text' => $f3->_mu_s.''),
                array('size' => 14, 'x' => 130, 'y' => 270, 'text' => $f3->_mu_w.''),
                array('size' => 14, 'x' => 130, 'y' => 325, 'text' => 0.8 .''),
                array('size' => 12, 'x' => 245, 'y' => 45, 'text' => $f3->_b_3.'m'),
                array('size' => 12, 'x' => 570, 'y' => 230, 'text' => $f3->_h.'m'),
                array('size' => 12, 'x' => 170, 'y' => 500, 'text' => $f3->_b_1.'m'),
                array('size' => 12, 'x' => 470, 'y' => 500, 'text' => $f3->_b_2.'m'),
                array('size' => 14, 'x' => 305, 'y' => 210, 'text' => '∑:'.$f3->_q_sum2.'kN/m²'),
                array('size' => 14, 'x' => 550, 'y' => 280, 'text' => $f3->_s_k*0.8.'kN/m²'),
            );
            if ($f3->_b_2 < $f3->_l_s2) {
                $blc->txt('$b_2 < l_s$, ezért a tető másik szélén teher teljes értéke:');
                $qsum22 = $ec->linterp(0, $f3->_q_sum2, $f3->_l_s2, $f3->_s_k*0.8, $f3->_b_2);
                $blc->txt('$q_(∑2,'.$f3->_b_2.'[m]) = '.$qsum22.' [(kN)/m^2]$', '$l i n t e r p(0,'.$f3->_q_sum2.','.$f3->_l_s2.','.$f3->_s_k*0.8.','.$f3->_b_2.')$');
                array_push($write, array('size' => 14, 'x' => 440, 'y' => 260, 'text' => "3m-nél:\n".$qsum22.'kN/m²'));
                $write[0] = array('size' => 14, 'x' => 390, 'y' => 175, 'text' => $f3->_l_s2.'m -> '.$f3->_b_2.'m');
            }
        $blc->success1('qsum2');
        $blc->write('vendor/resist/ecc-calculations/canvas/snow3.jpg', $write, 'Hófelhalmozódást is tartalmazó hóteher átrendeződés magasabb épülethez csatlakozó tető esetén');
    }
}
