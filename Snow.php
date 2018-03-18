<?php

namespace Calculation;

Class Snow extends \Ecc
{

    public function calc($f3)
    {
        \Ec::load();

        \Blc::input('A', 'Tengerszint feletti magasság', 400, 'm');
        \Blc::input('C', '`C_e*C_t` Szél- és hőmérséklet összevont tényező', 1, '', '');

        \Blc::hr();

        if($f3->_A <= 400){
            $f3->_s_k = 1.25;
        } else {
            $f3->_s_k = 1.25 + ($f3->_A - 400)/400;
        }
        \Blc::math('s_k = '.$f3->_s_k*$f3->_C.'[(kN)/m^2]');

        \Blc::h1('Hóteher félnyereg-, nyereg- és összekapcsolódó nyeregtetők esetén');
        \Blc::input('alpha', 'Nyeregtető hajlása', 0, '°');
        \Blc::boo('bs', 'Akadályozott hólecsúszás', 0, '');

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

        \Blc::def('mu_1',$_mu[$_find]['mu1'],'mu_1=%%', '');
        \Blc::def('mu_2',$_mu[$_find]['mu2'],'mu_2=%%', '');
        \Blc::def('mu_3',$_mu[$_find]['mu3'],'mu_3=%%', '');

        \Blc::def('s_1',number_format($f3->_mu_1*$f3->_s_k,2),'s_1=%%[(kN)/m^2]', '');
        \Blc::def('s_2',number_format($f3->_mu_2*$f3->_s_k,2),'s_2=%%[(kN)/m^2]', '');
        \Blc::def('s_3',number_format($f3->_mu_3*$f3->_s_k,2),'s_3=%%[(kN)/m^2]', '');

        \Blc::txt('`s_1` félnyereg tetőhöz.');

        \Blc::h1('Hófelhalmozódás kiálló részek mellett, vízszinteshez közeli tetőkön');
        \Blc::input('h', 'Kiálló rész magassága', 1.2, 'm', '');
        \Blc::def('mu_w2',max(0.8, min((2*$f3->_h)/$f3->_s_k, 2)),'mu_(w2) = %%', 'Alaki tényező');
        \Blc::def('l_s', min(15, max(5, 2*$f3->_h)),'l_s = %% [m]', 'Hózug szélessége');
        \Blc::def('q_sum', $f3->_mu_w2*$f3->_s_k,'q_(sum) = %% [(kN)/m^2]', 'Teljes megoszló terhelés sarokban');
        \Blc::def('q_plus', ($f3->_mu_w2 - 0.8)*$f3->_s_k,'q_(plus) = %% [(kN)/m^2]', 'Megoszló terhelés többlet alap hóhoz képest');

        $write = array(
            array(
                'size' => 14,
                'x' => 10,
                'y' => 35,
                'text' => $f3->_q_sum.'kN/m²'
            ),
            array (
                'size' => 14,
                'x' => 40,
                'y' => 120,
                'text' => $f3->_l_s.'m'
            )
        );

        \Blc::write('data/canvas/snow0.jpg', $write, 'Hófelhalmozódás kiálló részek mellett, vízszinteshez közeli tetőkön');
    }
}
