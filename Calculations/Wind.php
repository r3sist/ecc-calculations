<?php

namespace Calculation;

Class Wind extends \Ecc
{

    public function calc($f3)
    {
        \Ec::load();

        \Blc::input('h', 'Épület magasság', 10, 'm');
        \Blc::input('b', 'Épület hossz', 20, 'm');
        \Blc::input('d', 'Épület szélesség', 12, 'm');

        $terrainCats = array(
            'I. Nyílt terep' => 1,
            'II. Mezőgazdasági terület' => 2,
            'III. Alacsony beépítés' => 3,
            'IV. Intenzív beépítés' => 4,
        );
        \Blc::lst('terrainCat', $terrainCats, 'Terep kategória', '2');

        \Blc::success0('success0');
        \Blc::def('qpz', \Ec::qpz($f3->_h, $f3->_terrainCat), 'q_(p)(z) = %% [(kN)/m^2]', 'Torlónyomás');
        \Blc::success1('success0');

        \Blc::boo('internal', 'Belső szél figyelembevétele', '1');

        \Blc::region0('more0', 'További paraméterek');
        if ($f3->_internal) {
            \Blc::input('cp', '`c_(p,i+)` Belső szél alaki tényező belső nyomáshoz', 0.2, '');
            \Blc::input('cm', '`c_(p,i-)` Belső szél alaki tényező belső szíváshoz', -0.3, '');
        } else {
            $f3->set('_cp', 0);
            $f3->set('_cm', 0);
        }

        \Blc::boo('flatRef', '`10 m^2` referencia felület', '1', 'Egyébként `1 m^2`');
        if ($f3->_flatRef == 1) {
            $f3->set('_flatRef', '10');
        } else {
            $f3->set('_flatRef', '1');
        }
        \Blc::region1('more0', '', 1);

        \Blc::info0('info0');
        \Blc::h1('Eredmény számítás');
        \Blc::lst('wind', array('Szívás' => '-', 'Nyomás' => '+'), 'Szél eset');
        if ($f3->_wind == '-') {
            $ci = $f3->_cm;
        } else {
            $ci = $f3->_cp;
        }
        \Blc::lst('dir', array('Hosszra (b) merőleges' => '0', 'Szélességre (d) merőleges' => '1'), 'Szél irány');
        if ($f3->_dir == 0) {
            $f3->set('_b0', $f3->get('_b'));
            $f3->set('_d0', $f3->get('_d'));
        } else {
            $f3->set('_b0', $f3->get('_d'));
            $f3->set('_d0', $f3->get('_b'));
        }
        \Blc::info1('info0');

        \Blc::h1('Lapostetők');
        $flatTypes = array(
            'Szögletes perem' => 'a',
            'Attika hp/h=0.025' => 'b1',
            'Attika hp/h=0.05' => 'b2',
            'Attika hp/h=0.1' => 'b3',
            'Lekerekített r/h=0.05' => 'c1',
            'Lekerekített r/h=0.1' => 'c2',
            'Lekerekített r/h=0.2' => 'c3',
            'Levágott α=30°' => 'd1',
            'Levágott α=45°' => 'd2',
            'Levágott α=60°' => 'd3'
        );
        \Blc::lst('flatType', $flatTypes, 'Tető kialakítás');
        $flatDb = array(
            'a-' => array(
                'F10' => -1.8,
                'F1' => -2.5,
                'G10' => -1.2,
                'G1' => -2,
                'H10' => -0.7,
                'H1' => -1.2,
                'I10' => -0.2,
                'I1' => -0.2
            ),
            'a+' => array(
                'F10' => 0,
                'F1' => 0,
                'G10' => 0,
                'G1' => 0,
                'H10' => 0,
                'H1' => 0,
                'I10' => 0.2,
                'I1' => 0.2
            ),
            'b1-' => array(
                'F10' => -1.6,
                'F1' => -2.2,
                'G10' => -1.1,
                'G1' => -1.8,
                'H10' => -0.7,
                'H1' => -1.2,
                'I10' => -0.2,
                'I1' => -0.2
            ),
            'b1+' => array(
                'F10' => 0,
                'F1' => 0,
                'G10' => 0,
                'G1' => 0,
                'H10' => 0,
                'H1' => 0,
                'I10' => 0.2,
                'I1' => 0.2
            ),
            'b2-' => array(
                'F10' => -1.4,
                'F1' => -2,
                'G10' => -0.9,
                'G1' => -1.6,
                'H10' => -0.7,
                'H1' => -1.2,
                'I10' => -0.2,
                'I1' => -0.2
            ),
            'b2+' => array(
                'F10' => 0,
                'F1' => 0,
                'G10' => 0,
                'G1' => 0,
                'H10' => 0,
                'H1' => 0,
                'I10' => 0.2,
                'I1' => 0.2
            ),
            'b3-' => array(
                'F10' => -1.2,
                'F1' => -1.8,
                'G10' => -0.8,
                'G1' => -1.4,
                'H10' => -0.7,
                'H1' => -1.2,
                'I10' => -0.2,
                'I1' => -0.2
            ),
            'b3+' => array(
                'F10' => 0,
                'F1' => 0,
                'G10' => 0,
                'G1' => 0,
                'H10' => 0,
                'H1' => 0,
                'I10' => 0.2,
                'I1' => 0.2
            ),
            'c1-' => array(
                'F10' => -1,
                'F1' => -1.5,
                'G10' => -1.2,
                'G1' => -1.8,
                'H10' => -0.4,
                'H1' => -0.4,
                'I10' => -0.2,
                'I1' => -0.2
            ),
            'c1+' => array(
                'F10' => 0,
                'F1' => 0,
                'G10' => 0,
                'G1' => 0,
                'H10' => 0,
                'H1' => 0,
                'I10' => 0.2,
                'I1' => 0.2
            ),
            'c2-' => array(
                'F10' => -0.7,
                'F1' => -1.2,
                'G10' => -0.8,
                'G1' => -1.4,
                'H10' => -0.3,
                'H1' => -0.3,
                'I10' => -0.2,
                'I1' => -0.2
            ),
            'c2+' => array(
                'F10' => 0,
                'F1' => 0,
                'G10' => 0,
                'G1' => 0,
                'H10' => 0,
                'H1' => 0,
                'I10' => 0.2,
                'I1' => 0.2
            ),
            'c3-' => array(
                'F10' => -0.5,
                'F1' => -0.8,
                'G10' => -0.5,
                'G1' => -0.8,
                'H10' => -0.3,
                'H1' => -0.3,
                'I10' => -0.2,
                'I1' => -0.2
            ),
            'c3+' => array(
                'F10' => 0,
                'F1' => 0,
                'G10' => 0,
                'G1' => 0,
                'H10' => 0,
                'H1' => 0,
                'I10' => 0.2,
                'I1' => 0.2
            ),
            'd1-' => array(
                'F10' => -1,
                'F1' => -1.5,
                'G10' => -1,
                'G1' => -1.5,
                'H10' => -0.3,
                'H1' => -0.3,
                'I10' => -0.2,
                'I1' => -0.2
            ),
            'd1+' => array(
                'F10' => 0,
                'F1' => 0,
                'G10' => 0,
                'G1' => 0,
                'H10' => 0,
                'H1' => 0,
                'I10' => 0.2,
                'I1' => 0.2
            ),
            'd2-' => array(
                'F10' => -1.2,
                'F1' => -1.8,
                'G10' => -1.3,
                'G1' => -1.9,
                'H10' => -0.4,
                'H1' => -0.4,
                'I10' => -0.2,
                'I1' => -0.2
            ),
            'd2+' => array(
                'F10' => 0,
                'F1' => 0,
                'G10' => 0,
                'G1' => 0,
                'H10' => 0,
                'H1' => 0,
                'I10' => 0.2,
                'I1' => 0.2
            ),
            'd3-' => array(
                'F10' => -1.3,
                'F1' => -1.9,
                'G10' => -1.3,
                'G1' => -1.9,
                'H10' => -0.5,
                'H1' => -0.5,
                'I10' => -0.2,
                'I1' => -0.2
            ),
            'd3+' => array(
                'F10' => 0,
                'F1' => 0,
                'G10' => 0,
                'G1' => 0,
                'H10' => 0,
                'H1' => 0,
                'I10' => 0.2,
                'I1' => 0.2
            )
        );
        $cpF = $flatDb[$f3->_flatType . $f3->_wind]['F'.$f3->_flatRef] + $ci;
        $cpG = $flatDb[$f3->_flatType . $f3->_wind]['G'.$f3->_flatRef] + $ci;
        $cpH = $flatDb[$f3->_flatType . $f3->_wind]['H'.$f3->_flatRef] + $ci;
        $cpI = $flatDb[$f3->_flatType . $f3->_wind]['I'.$f3->_flatRef] + $ci;
        $wF = number_format($cpF*$f3->_qpz, 2);
        $wG = number_format($cpG*$f3->_qpz, 2);
        $wH = number_format($cpH*$f3->_qpz, 2);
        $wI = number_format($cpI*$f3->_qpz, 2);
        $e = min($f3->_b0, 2*$f3->_h);
        $flat = array(
            '`c`' => array(
                'F' => $cpF,
                'G' => $cpG,
                'H' => $cpH,
                'I' => $cpI
            ),
            '`w [(kN)/m^2]`<!--success-->' => array(
                'F' => $wF,
                'G' => $wG,
                'H' => $wH,
                'I' => $wI
            ),
            'Zóna szélesség `[m]`' => array(
                'F' => $e/4,
                'G' => $f3->_b0-2*($e/4),
                'H' => $f3->_b0,
                'I' => ($f3->_d0 - $e/2 > 0 ? $f3->_b0 : 0)
            ),
            'Zóna mélység `[m]`' => array(
                'F' => $e/10,
                'G' => $e/10,
                'H' => $e/2-$e/10,
                'I' => ($f3->_d0 - $e/2 > 0 ? $f3->_d0 - $e/2 : 0)
            )
        );

        \Blc::math('c_(p,i) = '.$ci, '');
        \Blc::math('`e = '.$e.'`', '');

        \Blc::table($flat);
        \Blc::region0('flat0', 'Lapostető zóna elrendezés');
        $write = array(
            array('size' => 14, 'x' => 180, 'y' => 385, 'text' => 'F:'.$wF.'kN/m²'),
            array('size' => 14, 'x' => 180, 'y' => 550, 'text' => 'F:'.$wF.'kN/m²'),
            array('size' => 14, 'x' => 180, 'y' => 480, 'text' => 'G:'.$wG.'kN/m²'),
            array('size' => 14, 'x' => 270, 'y' => 420, 'text' => 'H:'.$wH.'kN/m²'),
            array('size' => 14, 'x' => 360, 'y' => 385, 'text' => 'I:'.$wI.'kN/m²'),
            array('size' => 14, 'x' => 300, 'y' => 610, 'text' => $f3->_d0.'m'),
            array('size' => 14, 'x' => 540, 'y' => 450, 'text' => $f3->_b0.'m'),
            array('size' => 14, 'x' => 240, 'y' => 140, 'text' => $e/10 .'m'),
            array('size' => 14, 'x' => 300, 'y' => 140, 'text' => $e/2-$e/10 .'m'),
            array('size' => 14, 'x' => 360, 'y' => 140, 'text' => ($f3->_d0 - $e/2 > 0 ? $f3->_d0 - $e/2 : 0) .'m'),
            array('size' => 14, 'x' => 150, 'y' => 385, 'text' => $e/4 .'m'),
            array('size' => 14, 'x' => 150, 'y' => 445, 'text' => $f3->_b0-2*($e/4) .'m'),
            array('size' => 14, 'x' => 150, 'y' => 510, 'text' => $e/4 .'m'),
        );
        \Blc::write('vendor/resist/ecc-calculations/canvas/wind0.jpg', $write, 'Lapostető zóna elrendezés');
        \Blc::region1('flat0');

        \Blc::h1('Falak');
        \Blc::def('wallRow', number_format($f3->_h/$f3->_d0, 2), 'h/d = %%', '');
        if ($f3->_wallRow >= 5) {
            $find = 'a';
        } elseif ($f3->_wallRow <= 0.25) {
            $find = 'c';
        } else {
            $find = 'b';
        }
        \Blc::txt('Sor: `'.$find.'`');
        $wallDb = array(
            'a' => array(
                'A10' => -1.2,
                'A1' => -1.4,
                'B10' => -0.8,
                'B1' => -1.1,
                'C10' => -0.5,
                'C1' => -0.5,
                'D10' => 0.8,
                'D1' => 1,
                'E10' => -0.7,
                'E1' => -0.7,
            ),
            'b' => array(
                'A10' => -1.2,
                'A1' => -1.4,
                'B10' => -0.8,
                'B1' => -1.1,
                'C10' => -0.5,
                'C1' => -0.5,
                'D10' => 0.8,
                'D1' => 1,
                'E10' => -0.5,
                'E1' => -0.5,
            ),
            'c' => array(
                'A10' => -1.2,
                'A1' => -1.4,
                'B10' => -0.8,
                'B1' => -1.1,
                'C10' => -0.5,
                'C1' => -0.5,
                'D10' => 0.7,
                'D1' => 1,
                'E10' => -0.3,
                'E1' => -0.3,
            ),
        );
        $cpA = $wallDb[$find]['A'.$f3->_flatRef] + $f3->_cm;
        $cpB = $wallDb[$find]['B'.$f3->_flatRef] + $f3->_cm;
        $cpC = $wallDb[$find]['C'.$f3->_flatRef] + $f3->_cm;
        $cpD = $wallDb[$find]['D'.$f3->_flatRef] + $f3->_cp;
        $cpE = $wallDb[$find]['E'.$f3->_flatRef] + $f3->_cm;
        $wA = number_format($cpA*$f3->_qpz, 2);
        $wB = number_format($cpB*$f3->_qpz, 2);
        $wC = number_format($cpC*$f3->_qpz, 2);
        $wD = number_format($cpD*$f3->_qpz, 2);
        $wE = number_format($cpE*$f3->_qpz, 2);
        $e = min($f3->_b0, 2*$f3->_h);
        $wall = array(
            '`c`' => array(
                'A' => $cpA,
                'B' => $cpB,
                'C' => $cpC,
                'D' => $cpD,
                'E' => $cpE
            ),
            '`w [(kN)/m^2]`<!--success-->' => array(
                'A' => $wA,
                'B' => $wB,
                'C' => ($f3->_d0 - $e > 0 ? $wC : 0),
                'D' => $wD,
                'E' => $wE
            ),
            'Zóna szélesség `[m]`' => array(
                'A' => $e/5,
                'B' => $e - $e/5,
                'C' => ($f3->_d0 - $e > 0 ? $f3->_d0 - $e : 0),
                'D' => $f3->_b0,
                'E' => $f3->_b0
            )
        );
        \Blc::math('c_(p,i,+) = '.$f3->_cp.'', '');
        \Blc::math('c_(p,i,-) = '.$f3->_cm.'', '');
        \Blc::math('e = '.$e.'', '');

        \Blc::table($wall);
        \Blc::region0('wall0', 'Fal zóna elrendezés');
        $write = array(
            array('size' => 14, 'x' => 30, 'y' => 400, 'text' => 'D:'.$wD.'kN/m²'),
            array('size' => 14, 'x' => 380, 'y' => 400, 'text' => 'E:'.$wE.'kN/m²'),
            array('size' => 14, 'x' => 65, 'y' => 260, 'text' => 'A:'.$wA.'kN/m²'),
            array('size' => 14, 'x' => 235, 'y' => 260, 'text' => 'B:'.$wB.'kN/m²'),
            array('size' => 14, 'x' => 380, 'y' => 260, 'text' => 'C:'.($f3->_d0 - $e > 0 ? $wC : 0).'kN/m²'),
            array('size' => 14, 'x' => 450, 'y' => 360, 'text' => ''.$f3->_b0.'m'),
            array('size' => 14, 'x' => 250, 'y' => 580, 'text' => ''.$f3->_d0.'m'),
            array('size' => 14, 'x' => 180, 'y' => 100, 'text' => ''.$e/5 .'m'),
            array('size' => 14, 'x' => 240, 'y' => 100, 'text' => ''.$e - $e/5 .'m'),
            array('size' => 14, 'x' => 315, 'y' => 100, 'text' => ''.($f3->_d0 - $e > 0 ? $f3->_d0 - $e : 0).'m'),
        );
        \Blc::write('vendor/resist/ecc-calculations/canvas/wind2.jpg', $write, 'Fal zóna elrendezés');
        \Blc::region1('wall0');

        \Blc::h1('Oldalain nyitott ferdesíkú pilletető');
        \Blc::boo('phi', 'Torlasz', '1', '');
        \Blc::math('phi = '.$f3->_phi, '');

        $canopyTypes = array(
            '0°' => '0',
            '5°' => '5',
            '10°' => '10',
            '15°' => '15',
            '20°' => '20',
            '25°' => '25',
            '30°' => '30'
        );
        \Blc::lst('canopyType', $canopyTypes, 'Tető hajlás','0');
        $canopyDb = array (
            '0+' => array ('A' => 0.5, 'B' => 1.8, 'C' => 1.1,),
            '0-0' => array ('A' => -0.6, 'B' => -1.3, 'C' => -1.4,),
            '0-1' => array ('A' => -1.5, 'B' => -1.8, 'C' => -2.2,),
            '5+' => array ('A' => 0.8, 'B' => 2.1, 'C' => 1.3,),
            '5-0' => array ('A' => -1.1, 'B' => -1.7, 'C' => -1.8,),
            '5-1' => array ('A' => -1.6, 'B' => -2.2, 'C' => -2.5,),
            '10+' => array ('A' => 1.2, 'B' => 2.4, 'C' => 1.6,),
            '10-0' => array ('A' => -1.5, 'B' => -2, 'C' => -2.1,),
            '10-1' => array ('A' => -2.1, 'B' => -2.6, 'C' => -2.7,),
            '15+' =>array ('A' => 1.4, 'B' => 2.7, 'C' => 1.8,),
            '15-0' => array ('A' => -1.8, 'B' => -2.4, 'C' => -2.5,),
            '15-1' => array ('A' => -1.6, 'B' => -2.9, 'C' => -3,),
            '20+' => array ('A' => 1.7, 'B' => 2.9, 'C' => 2.1,),
            '20-0' => array ('A' => -2.2, 'B' => -2.8, 'C' => -2.9,),
            '20-1' => array ('A' => 1.6, 'B' => -2.9, 'C' => -3,),
            '25+' => array ('A' => 2, 'B' => 3.1, 'C' => 2.3,),
            '25-0' => array ('A' => -2.6, 'B' => -3.2, 'C' => -3.2,),
            '25-1' => array ('A' => -1.5, 'B' => -2.5, 'C' => -2.8,),
            '30+' => array ('A' => 2.2, 'B' => 3.2, 'C' => 2.4,),
            '30-0' => array ('A' => -3, 'B' => -3.8, 'C' => -3.6,),
            '30-1' => array ('A' => -1.5, 'B' => -2.2, 'C' => -2.7,),
        );
        $cases = array('+', '-');
        foreach ($cases as $case) {
            $find = ($case == '+' ? $f3->_canopyType.$case : $f3->_canopyType.$case . $f3->_phi);
            $cpA = $canopyDb[$find]['A'];
            $cpB = $canopyDb[$find]['B'];
            $cpC = $canopyDb[$find]['C'];
            $wA = number_format($cpA*$f3->_qpz, 2);
            $wB = number_format($cpB*$f3->_qpz, 2);
            $wC = number_format($cpC*$f3->_qpz, 2);
            $canopy = array(
                '`c`' => array(
                    'A' => $cpA,
                    'B' => $cpB,
                    'C' => $cpC,
                ),
                '`w [(kN)/m^2]`<!--success-->' => array(
                    'A' => $wA,
                    'B' => $wB,
                    'C' => $wC,
                ),
                'Zóna szélesség `[m]`' => array(
                    'A' => 0,
                    'B' => $f3->_b0/10,
                    'C' => $f3->_d0/10,
                )
            );
            \Blc::table($canopy,'Pilletető '.$case, '');
            $class = '1';
            if ($case == '+') {
                $class = '2';
            }
            \Blc::region0('canopy0'.$class, 'Pilletető zóna elrendezés '.$case);
            $write = array(
                array('size' => 20, 'x' => 25, 'y' => 25, 'text' => '('.$case.')'),
                array('size' => 12, 'x' => 180, 'y' => 150, 'text' => 'A:'.$wA.'kN/m²'),
                array('size' => 12, 'x' => 180, 'y' => 30, 'text' => 'B:'.$wB.'kN/m²'),
                array('size' => 12, 'x' => 10, 'y' => 150, 'text' => 'C:'.$wC.'kN/m²'),
                array('size' => 12, 'x' => 225, 'y' => 300, 'text' => $f3->_d0.'m'),
                array('size' => 12, 'x' => 420, 'y' => 100, 'text' => $f3->_b0.'m'),
                array('size' => 12, 'x' => 340, 'y' => 25, 'text' => $f3->_b0/10 .'m'),
                array('size' => 12, 'x' => 185, 'y' => 225, 'text' => $f3->_d0/10 .'m'),
            );
            \Blc::write('vendor/resist/ecc-calculations/canvas/wind1.jpg', $write, 'Pilletető elrendezés '.$case);
            \Blc::region1('canopy0'.$class);
        }
        \Blc::txt('', '(+) szélnyomás&nbsp;&nbsp;&nbsp; (-) szélszívás');


        \Blc::h1('Szabadon álló falak és mellvédek');
        \Blc::input('h_a', 'Fal magasság', 1.2, 'm');
        \Blc::input('l_a', 'Fal szélesség', 40, 'm');
        \Blc::input('x_a', 'Visszaforduló falszakasz hossza', 10, 'm');
        \Blc::lst('fi_a', array('Tömör' => 1.0, '20%-os áttörtség' => 0.8), 'Áttörtség', '1.0', '');
        \Blc::math('l_a/h_a = '.number_format($f3->_l_a/$f3->_h_a, 1));
        $atticTypeSource = array(
            "b/h≤3" => "a",
            "b/h=5" => "b",
            "b/h≥10" => "c",
        );
        \Blc::lst('type_a', $atticTypeSource, 'Tábla arány', 'c', '');
        $atticFind = $f3->_type_a;
        if ($f3->_x_a >= $f3->_h_a && $f3->_fi_a == 1) {
            $atticFind = 'd';
        }
        if ($f3->_fi_a == 0.8) {
            $atticFind = 'e';
        }
        $atticDb = array(
            'a' => array ('A' => 2.3, 'B' => 1.4, 'C' => 1.2, 'D' => 1.2),
            'b' => array ('A' => 2.9, 'B' => 1.8, 'C' => 1.4, 'D' => 1.2),
            'c' => array ('A' => 3.4, 'B' => 2.1, 'C' => 1.7, 'D' => 1.2),
            'd' => array ('A' => 2.1, 'B' => 1.8, 'C' => 1.4, 'D' => 1.2),
            'e' => array ('A' => 1.2, 'B' => 1.2, 'C' => 1.2, 'D' => 1.2),
        );

        $caA = $atticDb[$atticFind]['A'];
        $caB = $atticDb[$atticFind]['B'];
        $caC = $atticDb[$atticFind]['C'];
        $caD = $atticDb[$atticFind]['D'];
        $waA = number_format($caA*$f3->_qpz, 2);
        $waB = number_format($caB*$f3->_qpz, 2);
        $waC = number_format($caC*$f3->_qpz, 2);
        $waD = number_format($caD*$f3->_qpz, 2);
        $paA = number_format($waA*$f3->_h_a, 2);
        $paB = number_format($waB*$f3->_h_a, 2);
        $paC = number_format($waC*$f3->_h_a, 2);
        $paD = number_format($waD*$f3->_h_a, 2);

        $atticTable = array(
            '`c`' => array(
                'A' => $caA,
                'B' => $caB,
                'C' => $caC,
                'D' => $caC,
            ),
            '`w [(kN)/m^2]`<!--success-->' => array(
                'A' => $waA,
                'B' => $waB,
                'C' => $waC,
                'D' => $waD,
            ),
            '`p [(kN)/m]` vízszintes teher' => array(
                'A' => $paA,
                'B' => $paB,
                'C' => $paC,
                'D' => $paD,
            ),
            'Zóna szélesség `[m]`' => array(
                'A' => 0.3*$f3->_h_a,
                'B' => 2*$f3->_h_a - 0.3*$f3->_h_a,
                'C' => 2*$f3->_h_a,
                'D' => $f3->_l_a - 4*$f3->_h_a,
            )
        );
        \Blc::table($atticTable,'Szabadon álló fal', '');
        \Blc::write('vendor/resist/ecc-calculations/canvas/wind3.jpg', array(), 'Szabadon álló fal');

        \Blc::h1('Egyedi szélteher');
        \Blc::h1('Nyeregtetők');
        \Blc::txt('', '[*[Terhek és hatások]*](https://structure.hu/silent/book/DeakGyorgyErdelyiTamasFernezelyiSandorKollarLaszloVisnovitzGyorgy-TerhekeshatasokTervezesazEurocodealapjan-2006.pdf)');
    }
}
