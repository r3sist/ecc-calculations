<?php

namespace Calculation;

/**
 * Wind load analysis according to Eurocodes - Calculation class for ECC framework
 *
 * (c) Bence VÁNKOS
 * https:// structure.hu
 */

Class Wind extends \Ecc
{

    /**
     * @var $f3 \Base
     * @var $blc \Blc
     * @var $ec \Ec\Ec
     */
    public function calc(object $f3, object $blc, object $ec): void
    {
        $blc->numeric('h', ['h', 'Épület magasság'], 10, 'm');
        $blc->numeric('b', ['b', 'Épület hossz'], 20, 'm');
        $blc->numeric('d', ['d', 'Épület szélesség'], 12, 'm');

        $terrainCats = [
            'I. Nyílt terep' => 1,
            'II. Mezőgazdasági terület' => 2,
            'III. Alacsony beépítés' => 3,
            'IV. Intenzív beépítés' => 4,
        ];
        $blc->lst('terrainCat', $terrainCats, 'Terep kategória', '2');

        $blc->boo('internal', 'Belső szél figyelembevétele', '1');

        $blc->region0('more0', 'További paraméterek');

        $blc->numeric('hz', ['h_z', 'Terepszint feletti magasság'], 0, 'm', 'qp(z) számításakor hozzáadódik a h magassághoz, de a zónaszélesség számításához nem. Pl. tetőfelépítmények számításához.');

        if ($f3->_internal) {
            $blc->numeric('cp', ['c_(p,i+)', 'Belső szél alaki tényező belső nyomáshoz'], 0.2, '');
            $blc->numeric('cm', ['c_(p,i-)', 'Belső szél alaki tényező belső szíváshoz'], -0.3, '');
        } else {
            $f3->set('_cp', 0);
            $f3->set('_cm', 0);
        }

        $blc->boo('flatRef', '$10 [m^2]$ referencia felület', 1, 'Egyébként $1 [m^2]$');
        if ($f3->_flatRef == 1) {
            $f3->set('_flatRef', '10');
        } else {
            $f3->set('_flatRef', '1');
        }
        $blc->math('v_(b,0) = 23.6 [m/s]');
        $blc->math('c_(dir) = 1.0');
        $blc->math('c_(season) = 1.0');
        $blc->math('c_(prob) = 1.0');

        $blc->boo('NSEN', '*NS-EN 1991-1-4:2005/NA:2009* Norvég Nemzeti Melléklet alkalmazása', 0);
        $blc->numeric('NSEN_vb0', '$v_(b, 0, NSEN)$ (!!)', '30', 'm/s');
        $blc->numeric('NSEN_calt', '$c_(al t , NSEN)$ Altitude factor (1)', 1, '');
        $blc->numeric('NSEN_c0z', '$c_(0, NSEN)(z)$ Domborzati tényező (!)', 1.1, '');
        $blc->region1();

        $blc->success0();
        if ($f3->_hz > 0) {
            $h = $f3->_h + $f3->_hz;
            $blc->txt('$h = '.$h.' [m]$ magasság figyelembevételével:');
        } else {
            $h=$f3->_h;
        }
        $blc->def('qpz', $ec->qpz($h, $f3->_terrainCat), 'q_(p)(z, cat) = %% [(kN)/m^2]', 'Torlónyomás');
        if ($f3->_NSEN) {
            $blc->math('v_(b, NSEN) = c_(al t, NSEN)*c_(dir)*c_(season)*c_(prob)*v_(b, 0, NSEN) = '.$f3->_NSEN_calt.'*1.0*1.0*'.$f3->_NSEN_vb0);
            $blc->def('qpz', $ec->qpzNSEN($h, $f3->_terrainCat, $f3->_NSEN_calt, $f3->_NSEN_c0z, $f3->_NSEN_vb0), 'q_(p, NSEN)(z) = %% [(kN)/m^2]', 'Torlónyomás');
        }
        $blc->success1();

        // FLAT
        $blc->h1('Lapostetők');
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
        $blc->lst('flatType', $flatTypes, 'Tető kialakítás', 'a');

        $blc->note('Szélnyomás esetén belső szélszívással számol: *F, G, H* zónák szélszívása ezért kisebb!');

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
                'F10' => -1.8,
                'F1' => -2.5,
                'G10' => -1.2,
                'G1' => -2,
                'H10' => -0.7,
                'H1' => -1.2,
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
                'F10' => -1.6,
                'F1' => -2.2,
                'G10' => -1.1,
                'G1' => -1.8,
                'H10' => -0.7,
                'H1' => -1.2,
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
                'F10' => -1.4,
                'F1' => -2,
                'G10' => -0.9,
                'G1' => -1.6,
                'H10' => -0.7,
                'H1' => -1.2,
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
                'F10' => -1.2,
                'F1' => -1.8,
                'G10' => -0.8,
                'G1' => -1.4,
                'H10' => -0.7,
                'H1' => -1.2,
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
                'F10' => -1,
                'F1' => -1.5,
                'G10' => -1.2,
                'G1' => -1.8,
                'H10' => -0.4,
                'H1' => -0.4,
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
                'F10' => -0.7,
                'F1' => -1.2,
                'G10' => -0.8,
                'G1' => -1.4,
                'H10' => -0.3,
                'H1' => -0.3,
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
                'F10' => -0.5,
                'F1' => -0.8,
                'G10' => -0.5,
                'G1' => -0.8,
                'H10' => -0.3,
                'H1' => -0.3,
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
                'F10' => -1,
                'F1' => -1.5,
                'G10' => -1,
                'G1' => -1.5,
                'H10' => -0.3,
                'H1' => -0.3,
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
                'F10' => -1.2,
                'F1' => -1.8,
                'G10' => -1.3,
                'G1' => -1.9,
                'H10' => -0.4,
                'H1' => -0.4,
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
                'F10' => -1.3,
                'F1' => -1.9,
                'G10' => -1.3,
                'G1' => -1.9,
                'H10' => -0.5,
                'H1' => -0.5,
                'I10' => 0.2,
                'I1' => 0.2
            )
        );
        $flatCases = [
            ['id' => 0, 'case' => 'Hosszra ($b$) merőleges szél szívás', 'wind' => '-', 'dir' => 0],
            ['id' => 1, 'case' => 'Hosszra ($b$) merőleges szél nyomás', 'wind' => '+', 'dir' => 0],
            ['id' => 2, 'case' => 'Szélességre ($d$) merőleges szél szívás', 'wind' => '-', 'dir' => 1],
            ['id' => 3, 'case' => 'Szélességre ($d$) merőleges szél nyomás', 'wind' => '+', 'dir' => 1],
        ];

        foreach ($flatCases as $case) {
            if ($case['dir'] == 0) {
                $f3->set('_b0', $f3->get('_b'));
                $f3->set('_d0', $f3->get('_d'));
            } else {
                $f3->set('_b0', $f3->get('_d'));
                $f3->set('_d0', $f3->get('_b'));
            }

            if ($case['wind'] == '-') {
                $ci = $f3->_cm;
            } else {
                $ci = $f3->_cp;
            }

            $blc->h3($case['case']);
            $cpF = $flatDb[$f3->_flatType . $case['wind']]['F'.$f3->_flatRef] + $ci;
            $cpG = $flatDb[$f3->_flatType . $case['wind']]['G'.$f3->_flatRef] + $ci;
            $cpH = $flatDb[$f3->_flatType . $case['wind']]['H'.$f3->_flatRef] + $ci;
            $cpI = $flatDb[$f3->_flatType . $case['wind']]['I'.$f3->_flatRef] + $ci;
            $wF = number_format($cpF*$f3->_qpz, 1);
            $wG = number_format($cpG*$f3->_qpz, 1);
            $wH = number_format($cpH*$f3->_qpz, 1);
            $wI = number_format($cpI*$f3->_qpz, 1);
            $e = min($f3->_b0, 2*$f3->_h);
            $flat = array(
                '$c\ (c_p, c_i)$' => array(
                    'F' => $cpF.' ('.$flatDb[$f3->_flatType . $case['wind']]['F'.$f3->_flatRef].', '.$ci.')',
                    'G' => $cpG.' ('.$flatDb[$f3->_flatType . $case['wind']]['G'.$f3->_flatRef].', '.$ci.')',
                    'H' => $cpH.' ('.$flatDb[$f3->_flatType . $case['wind']]['H'.$f3->_flatRef].', '.$ci.')',
                    'I' => $cpI.' ('.$flatDb[$f3->_flatType . $case['wind']]['I'.$f3->_flatRef].', '.$ci.')'
                ),
                '$w [(kN)/m^2]$<!--success-->' => array(
                    'F' => $wF,
                    'G' => $wG,
                    'H' => $wH,
                    'I' => $wI
                ),
                'Zóna szélesség $[m]$' => array(
                    'F' => $e/4,
                    'G' => $f3->_b0-2*($e/4),
                    'H' => $f3->_b0,
                    'I' => ($f3->_d0 - $e/2 > 0 ? $f3->_b0 : 0)
                ),
                'Zóna mélység $[m]$' => array(
                    'F' => $e/10,
                    'G' => $e/10,
                    'H' => $e/2-$e/10,
                    'I' => ($f3->_d0 - $e/2 > 0 ? $f3->_d0 - $e/2 : 0)
                )
            );

            $blc->math('c_(p,i) = '.$ci.'%%%b = '.$f3->_b0.' [m]%%%d = '.$f3->_d0.' [m]', '');
            $blc->math('e = '.$e.' [m]', '');

            $blc->table($flat);

            /*
            // Legacy write mode:
            if ($f3->_makeWrite) {
                $blc->region0('flat'.$case['id'], 'Zóna elrendezés: Lapostető - '.$case['case']);
                    $write = array(
                        array('size' => 20, 'x' => 25, 'y' => 25, 'text' => '('.$case['wind'].')'),
                        array('size' => 14, 'x' => 180, 'y' => 385, 'text' => 'F:'.$wF.'kN/m²'),
                        array('size' => 14, 'x' => 180, 'y' => 550, 'text' => 'F:'.$wF.'kN/m²'),
                        array('size' => 14, 'x' => 180, 'y' => 480, 'text' => 'G:'.$wG.'kN/m²'),
                        array('size' => 14, 'x' => 270, 'y' => 420, 'text' => 'H:'.$wH.'kN/m²'),
                        array('size' => 14, 'x' => 360, 'y' => 385, 'text' => 'I:'.$wI.'kN/m²'),
                        array('size' => 14, 'x' => 300, 'y' => 610, 'text' => number_format($f3->_d0, 1).'m'),
                        array('size' => 14, 'x' => 540, 'y' => 450, 'text' => number_format($f3->_b0, 1).'m'),
                        array('size' => 14, 'x' => 240, 'y' => 140, 'text' => number_format($e/10, 1) .'m'),
                        array('size' => 14, 'x' => 300, 'y' => 140, 'text' => number_format($e/2-$e/10, 1) .'m'),
                        array('size' => 14, 'x' => 360, 'y' => 140, 'text' => number_format(($f3->_d0 - $e/2 > 0 ? $f3->_d0 - $e/2 : 0), 1) .'m'),
                        array('size' => 14, 'x' => 50, 'y' => 385, 'text' => number_format($e/4, 1) .'m'),
                        array('size' => 14, 'x' => 50, 'y' => 445, 'text' => number_format($f3->_b0-2*($e/4), 1) .'m'),
                        array('size' => 14, 'x' => 50, 'y' => 510, 'text' => number_format($e/4, 1) .'m'),
                    );
                    $blc->write('vendor/resist/ecc-calculations/canvas/wind0.jpg', $write, 'Zóna elrendezés: Lapostető - '.$case['case']);
                $blc->region1('flat'.$case['id']);
            }
            */

            $svg = new \resist\SVG(600, 350);
            // Generate raw part:
            $svg->setColor('blue');
            $svg->addText(0, 250, '>>', false, 'font-size: 40px;');
            $svg->setColor(false);
            $svg->addRectangle(100, 70, 200, 50); // Building section
            $svg->setFill('#eeeeee'); // Forces
            $svg->addRectangle(100, 40, 50, 30); // FG
            $svg->addText(105, 65, 'F G');
            $svg->addRectangle(150, 50, 100, 20); // H
            $svg->addText(155, 65, 'H');
            $svg->addRectangle(250, 60, 50, 10); // I
            $svg->addText(255, 65, 'I');
            $svg->setFill('none');
            $svg->addRectangle(100, 140, 50, 50); // Building layout F1
            $svg->addText(105, 160, 'F');
            $svg->addRectangle(100, 190, 50, 70); // Building layout G
            $svg->addText(105, 210, 'G');
            $svg->addRectangle(100, 260, 50, 50); // Building layout F2
            $svg->addText(105, 280, 'F');
            $svg->addRectangle(150, 140, 100, 170); // Building layout H
            $svg->addText(155, 280, 'H');
            $svg->addRectangle(250, 140, 50, 170); // Building layout I
            $svg->addText(255, 280, 'I');
            // Dynamic part:
            $svg->setColor('blue');
            $svg->addText(0, 20, ($case['wind'] == '-' ? 'Szívás [-]' : 'Nyomás [+] [kN/m2]'), false, ''); // Case
            $svg->setColor('red');
            $svg->addText(105, 180, $wF.'', false, 'fill: red; font-weight: bold');
            $svg->addText(105, 230, $wG.'', false, 'fill: red; font-weight: bold');
            $svg->addText(155, 300, $wH.'', false, 'fill: red; font-weight: bold');
            $svg->addText(255, 300, $wI.'', false, 'fill: red; font-weight: bold');
            $svg->addDimH(100, 50, 100, \H3::n1($e/10)); // F dim
            $svg->addDimH(150, 100, 100, \H3::n1($e/2-$e/10)); // F dim
            $svg->addDimH(250, 50, 100, \H3::n1(($f3->_d0 - $e/2 > 0 ? $f3->_d0 - $e/2 : 0))); // F dim
            $svg->addDimV(140, 170, 330, 'b='.\H3::n1($f3->_b0));
            $svg->addDimV(140, 50, 80, ''.\H3::n1($e/4));
            $svg->addDimV(190, 70, 80, ''.\H3::n1($f3->_b0 - $e/2));
            $svg->addDimV(260, 50, 80, ''.\H3::n1($e/4));
            $svg->addDimH(100, 200, 340, 'd='.\H3::n1($f3->_d0));
            $blc->svg($svg);
            unset($svg);
        }

        // WALL
        $blc->h1('Falak');
        $wallCases = [
            ['id' => 0, 'case' => 'Hosszra ($b$) merőleges szél', 'dir' => 0],
            ['id' => 1, 'case' => 'Szélességre ($d$) merőleges szél', 'dir' => 1],
        ];

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

        foreach ($wallCases as $case) {
            $blc->h3($case['case']);

            if ($case['dir'] == 0) {
                $f3->set('_b0', $f3->get('_b'));
                $f3->set('_d0', $f3->get('_d'));
            } else {
                $f3->set('_b0', $f3->get('_d'));
                $f3->set('_d0', $f3->get('_b'));
            }

            $blc->def('wallRow', number_format($f3->_h/$f3->_d0, 2), 'h/d = %%', '');

            if ($f3->_wallRow >= 5) {
                $find = 'a';
            } elseif ($f3->_wallRow <= 0.25) {
                $find = 'c';
            } else {
                $find = 'b';
            }
            $blc->md('Táblázat: *'.$find.'* sor');

            $cpA = $wallDb[$find]['A'.$f3->_flatRef] + $f3->_cm;
            $cpB = $wallDb[$find]['B'.$f3->_flatRef] + $f3->_cm;
            $cpC = $wallDb[$find]['C'.$f3->_flatRef] + $f3->_cm;
            $cpD = $wallDb[$find]['D'.$f3->_flatRef] + $f3->_cp;
            $cpE = $wallDb[$find]['E'.$f3->_flatRef] + $f3->_cm;
            $wA = number_format($cpA*$f3->_qpz, 1);
            $wB = number_format($cpB*$f3->_qpz, 1);
            $wC = number_format($cpC*$f3->_qpz, 1);
            $wD = number_format($cpD*$f3->_qpz, 1);
            $wE = number_format($cpE*$f3->_qpz, 1);
            $e = min($f3->_b0, 2*$f3->_h);
            $widthB = \H3::n1(($e < $f3->_d0 ? $e - $e/5 : $f3->_d0 - $e/5));
            $wall = array(
                '$c$' => array(
                    'A' => $cpA,
                    'B' => $cpB,
                    'C' => $cpC,
                    'D' => $cpD,
                    'E' => $cpE
                ),
                '$w [(kN)/m^2]$<!--success-->' => array(
                    'A' => $wA,
                    'B' => $wB,
                    'C' => ($f3->_d0 - $e > 0 ? $wC : 0),
                    'D' => $wD,
                    'E' => $wE
                ),
                'Zóna szélesség $[m]$' => array(
                    'A' => $e/5,
                    'B' => $widthB,
                    'C' => ($f3->_d0 - $e > 0 ? $f3->_d0 - $e : 0),
                    'D' => $f3->_b0,
                    'E' => $f3->_b0
                )
            );
            $blc->math('c_(p,i,+) = '.$f3->_cp.'%%%c_(p,i,-) = '.$f3->_cm.'%%%b = '.$f3->_b0.' [m]%%%d = '.$f3->_d0.' [m]', '');
            $blc->math('e = min{(b),(2h):} = '.$e.'', '');

            $blc->table($wall);
            /*
            // Legacy write mode:
            if ($f3->_makeWrite) {
                $blc->region0('wall0'.$case['id'], 'Fal zóna elrendezés');
                    $write = array(
                        array('size' => 14, 'x' => 30, 'y' => 400, 'text' => 'D:'.$wD.'kN/m²'),
                        array('size' => 14, 'x' => 380, 'y' => 400, 'text' => 'E:'.$wE.'kN/m²'),
                        array('size' => 14, 'x' => 65, 'y' => 260, 'text' => 'A:'.$wA.'kN/m²'),
                        array('size' => 14, 'x' => 235, 'y' => 260, 'text' => 'B:'.$wB.'kN/m²'),
                        array('size' => 14, 'x' => 380, 'y' => 260, 'text' => 'C:'.($f3->_d0 - $e > 0 ? $wC : 0).'kN/m²'),
                        array('size' => 14, 'x' => 450, 'y' => 360, 'text' => ''.number_format($f3->_b0, 1).'m'),
                        array('size' => 14, 'x' => 250, 'y' => 580, 'text' => ''.number_format($f3->_d0, 1).'m'),
                        array('size' => 14, 'x' => 180, 'y' => 100, 'text' => ''.number_format($e/5, 1) .'m'),
                        array('size' => 14, 'x' => 240, 'y' => 100, 'text' => ''.number_format($e - $e/5, 1) .'m'),
                        array('size' => 14, 'x' => 315, 'y' => 100, 'text' => ''.number_format(($f3->_d0 - $e > 0 ? $f3->_d0 - $e : 0), 1).'m'),
                    );
                    $blc->write('vendor/resist/ecc-calculations/canvas/wind2.jpg', $write, 'Fal zóna elrendezés');
                $blc->region1('wall0');
            }
            */
            $svg = new \resist\SVG(600, 350);
            // Generate raw part:
            $svg->setColor('blue');
            $svg->addText(0, 250, '>>', false, 'font-size: 40px;');
            $svg->setColor(false);
            $svg->addRectangle(100, 50, 200, 50); // Building section
            $svg->addRectangle(100, 140, 200, 150); // Building layout
            $svg->setFill('#eeeeee'); // Forces
            $svg->addRectangle(100, 110, 50, 30); // A
            $svg->addRectangle(100, 290, 50, 30); // A bottom
            $svg->addText(105, 65, 'A');
            $svg->addText(105, 130, '↑');
            $svg->addText(105, 310, '↓');
            $svg->addRectangle(150, 120, 100, 20); // B
            $svg->addRectangle(150, 290, 100, 20); // B bottom
            $svg->addText(155, 65, 'B');
            $svg->addRectangle(250, 130, 50, 10); // C
            $svg->addRectangle(250, 290, 50, 10); // C bottom
            $svg->addText(255, 65, 'C');
            $svg->addRectangle(70, 140, 30, 150); // D
            $svg->addText(105, 220, 'D');
            $svg->addText(80, 220, '>>');
            $svg->addRectangle(300, 140, 20, 150); // E
            $svg->addText(280, 220, 'E');
            $svg->addText(300, 220, '>>');
            // Dynamic part:
            $svg->setColor('red');
            $svg->addText(115, 130, $wA.'', false, 'fill: red; font-weight: bold');
            $svg->addText(155, 130, $wB.'', false, 'fill: red; font-weight: bold');
            $svg->addText(255, 130, ($f3->_d0 - $e > 0 ? $wC : 0).'', false, 'fill: red; font-weight: bold');
            $svg->addText(80, 240, $wD.'', false, 'fill: red; font-weight: bold');
            $svg->addText(300, 240, $wE.'', false, 'fill: red; font-weight: bold');
            $svg->addDimH(100, 50, 90, \H3::n1($e/5)); // A width
            $svg->addDimH(150, 100, 90, $widthB); // B width
            $svg->addDimH(250, 50, 90, \H3::n1(($f3->_d0 - $e > 0 ? $f3->_d0 - $e : 0)));
            $svg->addDimV(140, 150, 350, 'b='.\H3::n1($f3->_b0));
            $svg->addDimH(100, 200, 340, 'd='.\H3::n1($f3->_d0));
            $blc->svg($svg);
            unset($svg);
        }

        $blc->h1('Oldalain nyitott ferdesíkú pilletető');
        $blc->boo('phi', 'Torlasz', true, '');
        $blc->math('phi = '.$f3->_phi, '');

        $canopyTypes = array(
            '0°' => '0',
            '5°' => '5',
            '10°' => '10',
            '15°' => '15',
            '20°' => '20',
            '25°' => '25',
            '30°' => '30'
        );
        $blc->lst('canopyType', $canopyTypes, 'Tető hajlás','0');
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

        $canopyCases = [
            ['id' => 0, 'case' => 'Hosszra ($b$) merőleges szél szívás', 'wind' => '-', 'dir' => 0],
            ['id' => 1, 'case' => 'Hosszra ($b$) merőleges szél nyomás', 'wind' => '+', 'dir' => 0],
            ['id' => 2, 'case' => 'Szélességre ($d$) merőleges szél szívás', 'wind' => '-', 'dir' => 1],
            ['id' => 3, 'case' => 'Szélességre ($d$) merőleges szél nyomás', 'wind' => '+', 'dir' => 1],
        ];

        foreach ($canopyCases as $case) {
            $blc->h3($case['case']);

            if ($case['dir'] == 0) {
                $f3->set('_b0', $f3->get('_b'));
                $f3->set('_d0', $f3->get('_d'));
            } else {
                $f3->set('_b0', $f3->get('_d'));
                $f3->set('_d0', $f3->get('_b'));
            }

            if ($case['wind'] == '-') {
                $find = $f3->_canopyType . $case['wind'] . $f3->_phi;
            } else {
                $find = $f3->_canopyType . $case['wind'];
            }

            $cpA = $canopyDb[$find]['A'];
            $cpB = $canopyDb[$find]['B'];
            $cpC = $canopyDb[$find]['C'];
            $wA = number_format($cpA*$f3->_qpz, 1);
            $wB = number_format($cpB*$f3->_qpz, 1);
            $wC = number_format($cpC*$f3->_qpz, 1);
            $canopy = array(
                '$c$' => array(
                    'A' => $cpA,
                    'B' => $cpB,
                    'C' => $cpC,
                ),
                '$w [(kN)/m^2]$<!--success-->' => array(
                    'A' => $wA,
                    'B' => $wB,
                    'C' => $wC,
                ),
                'Zóna szélesség $[m]$' => array(
                    'A' => 0,
                    'B' => $f3->_b0/10,
                    'C' => $f3->_d0/10,
                )
            );
            $blc->table($canopy,'Pilletető '.$case['wind'], '');
            $class = '1';
            if ($case['wind'] == '+') {
                $class = '2';
            }

            /*
            // Legacy write mode:
            if ($f3->_makeWrite) {
                $blc->region0('canopy0'.$class.$case['id'], 'Pilletető zóna elrendezés '.$case['wind']);
                    $write = array(
                        array('size' => 20, 'x' => 25, 'y' => 25, 'text' => '('.$case['wind'].')'),
                        array('size' => 12, 'x' => 180, 'y' => 150, 'text' => 'A:'.$wA.'kN/m²'),
                        array('size' => 12, 'x' => 180, 'y' => 30, 'text' => 'B:'.$wB.'kN/m²'),
                        array('size' => 12, 'x' => 10, 'y' => 150, 'text' => 'C:'.$wC.'kN/m²'),
                        array('size' => 12, 'x' => 225, 'y' => 300, 'text' => number_format($f3->_d0, 1).'m'),
                        array('size' => 12, 'x' => 420, 'y' => 100, 'text' => number_format($f3->_b0, 1).'m'),
                        array('size' => 12, 'x' => 340, 'y' => 25, 'text' => number_format($f3->_b0/10, 1) .'m'),
                        array('size' => 12, 'x' => 185, 'y' => 225, 'text' => number_format($f3->_d0/10, 1) .'m'),
                    );
                    $blc->write('vendor/resist/ecc-calculations/canvas/wind1.jpg', $write, 'Pilletető elrendezés '.$case['wind']);
                $blc->region1('canopy0'.$class);
            } else {
                $blc->txt('', 'Elrendezési kép generálás kikapcsolva.');
            }
            */
            $svg = new \resist\SVG(600, 300);
            // Generate raw part:
            $svg->setColor('blue');
            $svg->addText(0, 170, '>>', false, 'font-size: 40px;');
            $svg->setColor(false);
            $svg->addRectangle(100, 70, 50, 200); // C1
            $svg->addText(105, 170, 'C');
            $svg->addRectangle(100, 70, 200, 50); // B1
            $svg->addText(200, 95, 'B');
            $svg->addRectangle(100, 220, 200, 50); // B2
            $svg->addText(200, 260, 'B');
            $svg->addRectangle(250, 70, 50, 200); // C2
            $svg->addText(255, 170, 'C');
            $svg->addText(200, 170, 'A');
            // Dynamic part:
            $svg->setColor('blue');
            $svg->addText(0, 20, ($case['wind'] == '-' ? 'Szívás (-) [kN/m2]' : 'Nyomás (+) [kN/m2]'), false, ''); // Case
            $svg->setColor('red');
            $svg->addText(200, 190, $wA.'', false, 'font-weight: bold');
            $svg->addText(200, 115, $wB.'', false, 'font-weight: bold');
            $svg->addText(105, 190, $wC.'', false, 'font-weight: bold');
            $svg->addDimH(100, 50, 50, \H3::n1($f3->_d0/10));
            $svg->addDimH(150, 100, 50, \H3::n1($f3->_d0 - 2*0.1*$f3->_d0));
            $svg->addDimH(250, 50, 50, \H3::n1($f3->_d0/10));
            $svg->addDimV(70, 50, 70, ''.\H3::n1($f3->_b0/10));
            $svg->addDimV(120, 100, 70, ''.\H3::n1($f3->_b0 - 2*0.1*$f3->_b0));
            $svg->addDimV(220, 50, 70, ''.\H3::n1($f3->_b0/10));
            $svg->addDimV(70, 200, 340, 'b='.\H3::n1($f3->_b0));
            $svg->addDimH(100, 200, 300, 'd='.\H3::n1($f3->_d0));
            $blc->svg($svg);
            unset($svg);
        }
        $blc->txt('', '(+) szélnyomás&nbsp;&nbsp;&nbsp; (-) szélszívás');


        $blc->h1('Szabadon álló falak és mellvédek');
        $blc->numeric('h_a', ['h_a', 'Fal magasság'], 1.2, 'm');
        $blc->numeric('l_a', ['l_a', 'Fal szélesség'], 40, 'm');
        $blc->numeric('x_a', ['x_a', 'Visszaforduló falszakasz hossza'], 10, 'm');
        $blc->lst('fi_a', array('Tömör' => 1.0, '20%-os áttörtség' => 0.8), 'Áttörtség', '1.0', '');
        $blc->note('20%-nál nagyobb áttörtség esetén a felületet rácsos tartóként kell kezelni.');
        $blc->math('l_a/h_a = '.\H3::n1($f3->_l_a/$f3->_h_a).'%%%phi = '.$f3->_fi_a);
        $atticTypeSource = array(
            "b/h≤3" => "a",
            "b/h=5" => "b",
            "b/h≥10" => "c",
        );
        $blc->lst('type_a', $atticTypeSource, 'Tábla arány', 'c', '');
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
            '$c$' => array(
                'A' => $caA,
                'B' => $caB,
                'C' => $caC,
                'D' => $caC,
            ),
            '$w [(kN)/m^2]$<!--success-->' => array(
                'A' => $waA,
                'B' => $waB,
                'C' => $waC,
                'D' => $waD,
            ),
            '$p [(kN)/m]$ vízszintes teher' => array(
                'A' => $paA,
                'B' => $paB,
                'C' => $paC,
                'D' => $paD,
            ),
            'Zóna szélesség $[m]$' => array(
                'A' => 0.3*$f3->_h_a,
                'B' => 2*$f3->_h_a - 0.3*$f3->_h_a,
                'C' => 2*$f3->_h_a,
                'D' => $f3->_l_a - 4*$f3->_h_a,
            )
        );
        // TODO zónák mínuszban
        // TODO áttört esetén nem kellenek zónák
        $blc->table($atticTable,'Szabadon álló fal', '');
    }
}
