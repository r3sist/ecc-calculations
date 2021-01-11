<?php declare(strict_types = 1);
/**
 * Wind load analysis according to Eurocodes - Calculation class for Statika framework
 * (c) Bence VÁNKOS | https://structure.hu | https://github.com/r3sist/ecc-calculations
 */

namespace Statika\Calculations;

use H3;
use resist\SVG\SVG;
use Exception;
use Statika\Ec;
use Statika\EurocodeInterface;

Class Wind
{
    /**
     * Wind constructor.
     * @param Ec $ec
     */
    public function __construct(EurocodeInterface $ec)
    {
        $this->ec = $ec;
    }

    public function moduleQpz(): void
    {
        
        $this->ec->numeric('h', ['h', 'Épület magasság'], 10, 'm');
        $this->ec->numeric('b', ['b', 'Épület hossz'], 20, 'm');
        $this->ec->numeric('d', ['d', 'Épület szélesség'], 12, 'm');

        $terrainCats = [
            'I. Nyílt terep' => 1,
            'II. Mezőgazdasági terület' => 2,
            'III. Alacsony beépítés' => 3,
            'IV. Intenzív beépítés' => 4,
        ];
        $this->ec->lst('terrainCat', $terrainCats, ['', 'Terep kategória'], '2');

        $this->ec->boo('internal', ['', 'Belső szél figyelembevétele'], true);

        $this->ec->region0('more0', 'További paraméterek');
            $this->ec->numeric('hz', ['h_z', 'Terepszint feletti magasság'], 0, 'm', '$qp(z)$ számításakor hozzáadódik a $h$ magassághoz, de a zónaszélesség számításához nem (pl. tetőfelépítmények esetében).');

            if ($this->ec->internal) {
                $this->ec->numeric('cp', ['c_(p,i+)', 'Belső szél alaki tényező belső nyomáshoz'], 0.2, '');
                $this->ec->numeric('cm', ['c_(p,i-)', 'Belső szél alaki tényező belső szíváshoz'], -0.3, '');
            } else {
                $this->ec->set('cp', 0);
                $this->ec->set('cm', 0);
            }

            $this->ec->boo('flatRef', ['10 [m^2]', 'referencia felület'], true, 'Egyébként $1 [m^2]$');
            if ($this->ec->flatRef == 1) {
                $this->ec->set('flatRef', '10');
            } else {
                $this->ec->set('flatRef', '1');
            }
            $this->ec->math('v_(b,0) = 23.6 [m/s]');
            $this->ec->math('c_(dir) = 1.0');
            $this->ec->math('c_(season) = 1.0');
            $this->ec->math('c_(prob) = 1.0');
        $this->ec->region1();

        $this->ec->success0();
            if ($this->ec->hz > 0) {
                $h = $this->ec->h + $this->ec->hz;
                $this->ec->txt('$h = '.$h.' [m]$ magasság figyelembevételével:');
            } else {
                $h=$this->ec->h;
            }
            $this->ec->def('qpz', $this->ec->qpz($h, $this->ec->terrainCat), 'q_(p)(z, cat) = %% [(kN)/m^2]', 'Torlónyomás');
        $this->ec->success1();
    }

    /**
     * @param Ec $ec
     * @throws Exception
     */
    public function calc(EurocodeInterface $ec): void
    {
        $ec->math('psi = 0.6//0.2//0', 'Kombinációs tényezők');

        $this->moduleQpz();

        $ec->h1('Lapostetők');
        $ec->boo('calcFlat', ['', 'Lapostető számítása'], false);
        if ($ec->calcFlat === true) {
            $flatTypes = [
                'Szögletes perem' => 'a',
                'Attika hp/h=0.025' => 'b1',
                'Attika hp/h=0.05' => 'b2',
                'Attika hp/h=0.1' => 'b3',
                'Lekerekített r/h=0.05' => 'c1',
                'Lekerekített r/h=0.1' => 'c2',
                'Lekerekített r/h=0.2' => 'c3',
                'Levágott α=30°' => 'd1',
                'Levágott α=45°' => 'd2',
                'Levágott α=60°' => 'd3',
            ];
            $ec->lst('flatType', $flatTypes, ['', 'Tető kialakítás'], 'a');

            $ec->note('Külső szélnyomás esetén belső szélszívással számol: *F, G, H* szívott zónák szélszívása ezért kisebb szélnyomás esetben!');

            $flatDb = [
                'a-' => ['F10' => -1.8, 'F1' => -2.5, 'G10' => -1.2, 'G1' => -2, 'H10' => -0.7, 'H1' => -1.2, 'I10' => -0.2, 'I1' => -0.2],
                'a+' => [ 'F10' => -1.8, 'F1' => -2.5, 'G10' => -1.2, 'G1' => -2, 'H10' => -0.7, 'H1' => -1.2, 'I10' => 0.2, 'I1' => 0.2],
                'b1-' => ['F10' => -1.6, 'F1' => -2.2, 'G10' => -1.1, 'G1' => -1.8, 'H10' => -0.7, 'H1' => -1.2, 'I10' => -0.2, 'I1' => -0.2],
                'b1+' => ['F10' => -1.6, 'F1' => -2.2, 'G10' => -1.1, 'G1' => -1.8, 'H10' => -0.7, 'H1' => -1.2, 'I10' => 0.2, 'I1' => 0.2],
                'b2-' => ['F10' => -1.4, 'F1' => -2, 'G10' => -0.9, 'G1' => -1.6, 'H10' => -0.7, 'H1' => -1.2, 'I10' => -0.2, 'I1' => -0.2],
                'b2+' => ['F10' => -1.4, 'F1' => -2, 'G10' => -0.9, 'G1' => -1.6, 'H10' => -0.7, 'H1' => -1.2, 'I10' => 0.2, 'I1' => 0.2],
                'b3-' => ['F10' => -1.2, 'F1' => -1.8, 'G10' => -0.8, 'G1' => -1.4, 'H10' => -0.7, 'H1' => -1.2, 'I10' => -0.2, 'I1' => -0.2],
                'b3+' => ['F10' => -1.2, 'F1' => -1.8, 'G10' => -0.8, 'G1' => -1.4, 'H10' => -0.7, 'H1' => -1.2, 'I10' => 0.2, 'I1' => 0.2],
                'c1-' => ['F10' => -1, 'F1' => -1.5, 'G10' => -1.2, 'G1' => -1.8, 'H10' => -0.4, 'H1' => -0.4, 'I10' => -0.2, 'I1' => -0.2],
                'c1+' => ['F10' => -1, 'F1' => -1.5, 'G10' => -1.2, 'G1' => -1.8, 'H10' => -0.4, 'H1' => -0.4, 'I10' => 0.2, 'I1' => 0.2],
                'c2-' => ['F10' => -0.7, 'F1' => -1.2, 'G10' => -0.8, 'G1' => -1.4, 'H10' => -0.3, 'H1' => -0.3, 'I10' => -0.2, 'I1' => -0.2],
                'c2+' => ['F10' => -0.7, 'F1' => -1.2, 'G10' => -0.8, 'G1' => -1.4, 'H10' => -0.3, 'H1' => -0.3, 'I10' => 0.2, 'I1' => 0.2],
                'c3-' => ['F10' => -0.5, 'F1' => -0.8, 'G10' => -0.5, 'G1' => -0.8, 'H10' => -0.3, 'H1' => -0.3, 'I10' => -0.2, 'I1' => -0.2],
                'c3+' => ['F10' => -0.5, 'F1' => -0.8, 'G10' => -0.5, 'G1' => -0.8, 'H10' => -0.3, 'H1' => -0.3, 'I10' => 0.2, 'I1' => 0.2],
                'd1-' => ['F10' => -1, 'F1' => -1.5, 'G10' => -1, 'G1' => -1.5, 'H10' => -0.3, 'H1' => -0.3, 'I10' => -0.2, 'I1' => -0.2],
                'd1+' => ['F10' => -1, 'F1' => -1.5, 'G10' => -1, 'G1' => -1.5, 'H10' => -0.3, 'H1' => -0.3, 'I10' => 0.2, 'I1' => 0.2],
                'd2-' => ['F10' => -1.2, 'F1' => -1.8, 'G10' => -1.3, 'G1' => -1.9, 'H10' => -0.4, 'H1' => -0.4, 'I10' => -0.2, 'I1' => -0.2],
                'd2+' => ['F10' => -1.2, 'F1' => -1.8, 'G10' => -1.3, 'G1' => -1.9, 'H10' => -0.4, 'H1' => -0.4, 'I10' => 0.2, 'I1' => 0.2],
                'd3-' => ['F10' => -1.3, 'F1' => -1.9, 'G10' => -1.3, 'G1' => -1.9, 'H10' => -0.5, 'H1' => -0.5, 'I10' => -0.2, 'I1' => -0.2],
                'd3+' => ['F10' => -1.3, 'F1' => -1.9, 'G10' => -1.3, 'G1' => -1.9, 'H10' => -0.5, 'H1' => -0.5, 'I10' => 0.2, 'I1' => 0.2]
            ];

            $flatCases = [
                ['id' => 0, 'case' => '$b$ Hosszra merőleges szél szívás', 'wind' => '-', 'dir' => 0],
                ['id' => 1, 'case' => '$b$ Hosszra merőleges szél nyomás', 'wind' => '+', 'dir' => 0],
                ['id' => 2, 'case' => '$d$ Szélességre merőleges szél szívás', 'wind' => '-', 'dir' => 1],
                ['id' => 3, 'case' => '$d$ Szélességre merőleges szél nyomás', 'wind' => '+', 'dir' => 1],
            ];

            foreach ($flatCases as $case) {
                if ($case['dir'] === 0) {
                    $ec->set('b0', $ec->get('b'));
                    $ec->set('d0', $ec->get('d'));
                } else {
                    $ec->set('b0', $ec->get('d'));
                    $ec->set('d0', $ec->get('b'));
                }

                if ($case['wind'] === '-') {
                    $ci = -1*$ec->cp;
                } else {
                    $ci = -1*$ec->cm;
                }

                $ec->h3($case['case']);

                $cpF0 = $flatDb[$ec->flatType . $case['wind']]['F'.$ec->flatRef];
                $cpF = $cpF0 + $ci;
                $cpG0 = $flatDb[$ec->flatType . $case['wind']]['G'.$ec->flatRef];
                $cpG = $cpG0 + $ci;
                $cpH0 = $flatDb[$ec->flatType . $case['wind']]['H'.$ec->flatRef];
                $cpH = $cpH0 + $ci;
                $cpI0 = $flatDb[$ec->flatType . $case['wind']]['I'.$ec->flatRef];
                $cpI = $cpI0 + $ci;
                $wF = H3::n2($cpF*$ec->qpz);
                $wG = H3::n2($cpG*$ec->qpz);
                $wH = H3::n2($cpH*$ec->qpz);
                $wI = H3::n2($cpI*$ec->qpz);
                $e = min($ec->b0, 2*$ec->h);
                $ec->math('e = min{(b_0),(2*h):} = '.$e.' [m]', '');
                $aF = H3::n1($e/4);
                $aG = H3::n1($ec->b0-2*($e/4));
                $aH = H3::n1($ec->b0);
                $aI = H3::n1(($ec->d0 - $e/2 > 0 ? $ec->b0 : 0));
                $bF = H3::n1($e/10);
                $bG = H3::n1($e/10);
                $bH = H3::n1($e/2-$e/10);
                $bI = H3::n1(($ec->d0 - $e/2 > 0 ? $ec->d0 - $e/2 : 0));
                $ec->txt('$c_(p,i) = '.$ci.'$ többlet, belső szélből', 'Belső szél előjele domináns szélhez igazítva: külső szíváshoz belső nyomás és fordítva.');

                $scheme = ['Zóna', 'Felületi erő: $w [(kN)/m^2]$', 'Alaki tényező: $c (c_p, c_i)$', 'Zóna szélesség $[m]$', 'Zóna mélység $[m]$'];
                $tbl = [
                    ['F', $wF.'<!--success-->', $cpF. ' ('.$cpF0.', '.$ci.')', $aF, $bF],
                    ['G', $wG.'<!--success-->', $cpG. ' ('.$cpG0.', '.$ci.')', $aG, $bG],
                    ['H', $wH.'<!--success-->', $cpH. ' ('.$cpH0.', '.$ci.')', $aH, $bH],
                    ['I', $wI.'<!--success-->', $cpI. ' ('.$cpI0.', '.$ci.')', $aI, $bI],
                ];
                $ec->tbl($scheme, $tbl);

                $svg = new SVG(600, 350);
                // Raw part:
                $svg->setColor('green');
//                $svg->addSymbol(0, 210, 'control-shuffle');
                $svg->addText(20, 210, '>>>');
                $svg->setColor('black');
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
                $svg->addText(0, 20, ($case['wind'] === '-' ? 'Szívás [-] [kN/m2]' : 'Nyomás [+] [kN/m2]'), false, ''); // Case
                $svg->setColor('red');
                $svg->addText(105, 180, $wF.'', false, 'fill: red; font-weight: bold');
                $svg->addText(105, 230, $wG.'', false, 'fill: red; font-weight: bold');
                $svg->addText(155, 300, $wH.'', false, 'fill: red; font-weight: bold');
                $svg->addText(255, 300, $wI.'', false, 'fill: red; font-weight: bold');
                $svg->addDimH(100, 50, 100, H3::n1($e/10)); // F dim
                $svg->addDimH(150, 100, 100, H3::n1($e/2-$e/10)); // F dim
                $svg->addDimH(250, 50, 100, H3::n1(($ec->d0 - $e/2 > 0 ? $ec->d0 - $e/2 : 0))); // F dim
                $svg->addDimV(140, 170, 330, 'b='. H3::n1($ec->b0));
                $svg->addDimV(140, 50, 80, ''. H3::n1($e/4));
                $svg->addDimV(190, 70, 80, ''. H3::n1($ec->b0 - $e/2));
                $svg->addDimV(260, 50, 80, ''. H3::n1($e/4));
                $svg->addDimH(100, 200, 340, 'd='. H3::n1($ec->d0));
                $ec->svg($svg);
                unset($svg);
            }
        }

        $ec->h1('Falak');
        $ec->boo('calcWall', ['', 'Fal számítása'], false);
        if ($ec->calcWall === true) {
            $wallCases = [
                ['id' => 0, 'case' => '$b$ Hosszra merőleges szél', 'dir' => 0],
                ['id' => 1, 'case' => '$d$ Szélességre merőleges szél', 'dir' => 1],
            ];

            $wallDb = [
                'a' => ['A10' => -1.2, 'A1' => -1.4, 'B10' => -0.8, 'B1' => -1.1, 'C10' => -0.5, 'C1' => -0.5, 'D10' => 0.8, 'D1' => 1, 'E10' => -0.7, 'E1' => -0.7,],
                'b' => ['A10' => -1.2, 'A1' => -1.4, 'B10' => -0.8, 'B1' => -1.1, 'C10' => -0.5, 'C1' => -0.5, 'D10' => 0.8, 'D1' => 1, 'E10' => -0.5, 'E1' => -0.5,],
                'c' => ['A10' => -1.2, 'A1' => -1.4, 'B10' => -0.8, 'B1' => -1.1, 'C10' => -0.5, 'C1' => -0.5, 'D10' => 0.7, 'D1' => 1, 'E10' => -0.3, 'E1' => -0.3,],
            ];

            foreach ($wallCases as $case) {
                $ec->h3($case['case']);

                if ($case['dir'] === 0) {
                    $ec->set('b0', $ec->get('b'));
                    $ec->set('d0', $ec->get('d'));
                } else {
                    $ec->set('b0', $ec->get('d'));
                    $ec->set('d0', $ec->get('b'));
                }

                $ec->region0('wallCalculations');
                    $ec->def('wallRow', H3::n2($ec->h/$ec->d0), 'h/d = %%', '');

                    if ($ec->wallRow >= 5) {
                        $find = 'a';
                    } elseif ($ec->wallRow <= 0.25) {
                        $find = 'c';
                    } else {
                        $find = 'b';
                    }
                    $ec->md('Táblázat: *'.$find.'* sor');

                    $e = min($ec->b0, 2*$ec->h);
                    $cpA0 = $wallDb[$find]['A'.$ec->flatRef];
                    $cpA = $cpA0 - $ec->cp;
                    $cpB0 = $wallDb[$find]['B'.$ec->flatRef];
                    $cpB = $cpB0 - $ec->cp;
                    $cpC0 = $wallDb[$find]['C'.$ec->flatRef];
                    $cpC = $cpC0 - $ec->cp;
                    $cpD0 = $wallDb[$find]['D'.$ec->flatRef];
                    $cpD = $cpD0 - $ec->cm;
                    $cpE0 = $wallDb[$find]['E'.$ec->flatRef];
                    $cpE = $cpE0 - $ec->cp;
                    $wA = H3::n2($cpA*$ec->qpz);
                    $wB = H3::n2($cpB*$ec->qpz);
                    $wC = ($ec->d0 - $e > 0 ? H3::n2($cpC*$ec->qpz) : 0);
                    $wD = H3::n2($cpD*$ec->qpz);
                    $wE = H3::n2($cpE*$ec->qpz);
                    $widthA = H3::n1($e/5);
                    $widthB = H3::n1(($e < $ec->d0 ? $e - $e/5 : $ec->d0 - $e/5));
                    $widthC = H3::n1(($ec->d0 - $e > 0 ? $ec->d0 - $e : 0));
                    $widthD = H3::n1($ec->b0);
                    $widthE = H3::n1($ec->b0);

                    $ec->math('e = min{(b),(2h):} = '.$e.'', '');
                    $ec->txt('', 'Belső szél előjele domináns szélhez igazítva: külső szíváshoz belső nyomás és fordítva.');
                $ec->region1();

                $scheme = ['Zóna', 'Felületi erő: $w [(kN)/m^2]$', 'Alaki tényező: $c (c_p, c_i)$', 'Zóna szélesség $[m]$'];
                $tbl = [
                    ['A', $wA.'<!--success-->', $cpA. ' ('.$cpA0.', '.$ec->cp.')', $widthA],
                    ['B', $wB.'<!--success-->', $cpB. ' ('.$cpB0.', '.$ec->cp.')', $widthB],
                    ['C', $wC.'<!--success-->', $cpC. ' ('.$cpC0.', '.$ec->cp.')', $widthC],
                    ['D', $wD.'<!--success-->', $cpD. ' ('.$cpD0.', '.$ec->cm.')', $widthD],
                    ['E', $wE.'<!--success-->', $cpE. ' ('.$cpE0.', '.$ec->cp.')', $widthE],
                ];
                $ec->tbl($scheme, $tbl);

                $svg = new SVG(400, 400);
                $svg->setColor('green');
                $svg->addText(20, 210, '>>>');
                $svg->setColor('black');
                $svg->addRectangle(100, 140, 200, 150); // Building layout
                $svg->setFill('#eeeeee'); // Forces
                $svg->addRectangle(100, 110, 50, 30); // A
                $svg->addRectangle(100, 290, 50, 30); // A bottom
                $svg->addText(105, 65, 'A');
                $svg->addSymbol(100, 120, 'arrow-up');
                $svg->addSymbol(100, 290, 'arrow-down');
                $svg->addRectangle(150, 120, 100, 20); // B
                $svg->addRectangle(150, 290, 100, 20); // B bottom
                $svg->addText(155, 65, 'B');
                $svg->addRectangle(250, 130, 50, 10); // C
                $svg->addRectangle(250, 290, 50, 10); // C bottom
                $svg->addText(255, 65, 'C');
                $svg->addRectangle(70, 140, 30, 150); // D
                $svg->addText(105, 220, 'D');
                $svg->addSymbol(80, 200, 'arrow-right');
                $svg->addRectangle(300, 140, 20, 150); // E
                $svg->addText(280, 220, 'E');
                $svg->addSymbol(300, 200, 'arrow-right');
                // Dynamic part:
                $svg->setColor('red');
                $svg->addText(115, 160, $wA.'', false, 'fill: red; font-weight: bold');
                $svg->addText(170, 160, $wB.'', false, 'fill: red; font-weight: bold');
                $svg->addText(255, 160, $wC.'', false, 'fill: red; font-weight: bold');
                $svg->addText(105, 240, $wD.'', false, 'fill: red; font-weight: bold');
                $svg->addText(260, 240, $wE.'', false, 'fill: red; font-weight: bold');
                $svg->addDimH(100, 50, 90, $widthA); // A width
                $svg->addDimH(150, 100, 90, $widthB); // B width
                $svg->addDimH(250, 50, 90, $widthC);
                $svg->addDimV(140, 150, 360, 'b='. $widthD);
                $svg->addDimH(100, 200, 340, 'd='. H3::n2($ec->d0));
                $ec->svg($svg);
                unset($svg);
            }
        }

        $ec->h1('Oldalain nyitott ferdesíkú pilletető');
        $ec->boo('calcCanopy', ['', 'Oldalain nyitott ferdesíkú pilletető számítása']);
        if ($ec->calcCanopy === true) {
            $ec->boo('phi', ['Phi', 'Torlasz'], true, '');

            $canopyTypes = ['0°' => '0', '5°' => '5', '10°' => '10', '15°' => '15', '20°' => '20', '25°' => '25', '30°' => '30'];
            $ec->lst('canopyType', $canopyTypes, ['', 'Tető hajlás'],'0');
            $canopyDb = [
                '0+' => ['A' => 0.5, 'B' => 1.8, 'C' => 1.1,],
                '0-0' => ['A' => -0.6, 'B' => -1.3, 'C' => -1.4,],
                '0-1' => ['A' => -1.5, 'B' => -1.8, 'C' => -2.2,],
                '5+' => ['A' => 0.8, 'B' => 2.1, 'C' => 1.3,],
                '5-0' => ['A' => -1.1, 'B' => -1.7, 'C' => -1.8,],
                '5-1' => ['A' => -1.6, 'B' => -2.2, 'C' => -2.5,],
                '10+' => ['A' => 1.2, 'B' => 2.4, 'C' => 1.6,],
                '10-0' => ['A' => -1.5, 'B' => -2, 'C' => -2.1,],
                '10-1' => ['A' => -2.1, 'B' => -2.6, 'C' => -2.7,],
                '15+' => ['A' => 1.4, 'B' => 2.7, 'C' => 1.8,],
                '15-0' => ['A' => -1.8, 'B' => -2.4, 'C' => -2.5,],
                '15-1' => ['A' => -1.6, 'B' => -2.9, 'C' => -3,],
                '20+' => ['A' => 1.7, 'B' => 2.9, 'C' => 2.1,],
                '20-0' => ['A' => -2.2, 'B' => -2.8, 'C' => -2.9,],
                '20-1' => ['A' => 1.6, 'B' => -2.9, 'C' => -3,],
                '25+' => ['A' => 2, 'B' => 3.1, 'C' => 2.3,],
                '25-0' => ['A' => -2.6, 'B' => -3.2, 'C' => -3.2,],
                '25-1' => ['A' => -1.5, 'B' => -2.5, 'C' => -2.8,],
                '30+' => ['A' => 2.2, 'B' => 3.2, 'C' => 2.4,],
                '30-0' => ['A' => -3, 'B' => -3.8, 'C' => -3.6,],
                '30-1' => ['A' => -1.5, 'B' => -2.2, 'C' => -2.7,],
            ];

            $canopyCases = [
                ['id' => 0, 'case' => '$b$ Hosszra merőleges szél szívás', 'wind' => '-', 'dir' => 0],
                ['id' => 1, 'case' => '$b$ Hosszra merőleges szél nyomás', 'wind' => '+', 'dir' => 0],
                ['id' => 2, 'case' => '$d$ Szélességre merőleges szél szívás', 'wind' => '-', 'dir' => 1],
                ['id' => 3, 'case' => '$d$ Szélességre merőleges szél nyomás', 'wind' => '+', 'dir' => 1],
            ];

            foreach ($canopyCases as $case) {
                $ec->h3($case['case']);

                if ($case['dir'] === 0) {
                    $ec->set('b0', $ec->get('b'));
                    $ec->set('d0', $ec->get('d'));
                } else {
                    $ec->set('b0', $ec->get('d'));
                    $ec->set('d0', $ec->get('b'));
                }

                if ($case['wind'] === '-') {
                    $find = $ec->canopyType . $case['wind'] . $ec->phi;
                } else {
                    $find = $ec->canopyType . $case['wind'];
                }

                $cpA = $canopyDb[$find]['A'];
                $cpB = $canopyDb[$find]['B'];
                $cpC = $canopyDb[$find]['C'];
                $wA = H3::n2($cpA*$ec->qpz);
                $wB = H3::n2($cpB*$ec->qpz);
                $wC = H3::n2($cpC*$ec->qpz);
                $aA = 0;
                $aB = H3::n1($ec->b0/10);
                $aC = H3::n1($ec->d0/10);

                $scheme = ['Zóna', 'Felületi erő: $w [(kN)/m^2]$', 'Alaki tényező: $c$', 'Zóna szélesség $[m]$'];
                $tbl = [
                    ['A', $wA.'<!--success-->', $cpA, ''],
                    ['B', $wB.'<!--success-->', $cpB, $aB],
                    ['C', $wC.'<!--success-->', $cpC, $aC],
                ];
                $ec->tbl($scheme, $tbl);

                $svg = new SVG(600, 300);
                // Generate raw part:
//                $svg->addSymbol(0, 150, 'control-shuffle');
                $svg->setColor('green');
                $svg->addText(20, 150, '>>>');
                $svg->setColor('black');
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
                $svg->addText(0, 20, ($case['wind'] === '-' ? 'Szívás (-) [kN/m2]' : 'Nyomás (+) [kN/m2]'), false, ''); // Case
                $svg->setColor('red');
                $svg->addText(200, 190, $wA.'', false, 'font-weight: bold');
                $svg->addText(200, 115, $wB.'', false, 'font-weight: bold');
                $svg->addText(105, 190, $wC.'', false, 'font-weight: bold');
                $svg->addDimH(100, 50, 50, H3::n1($ec->d0/10));
                $svg->addDimH(150, 100, 50, H3::n1($ec->d0 - 2*0.1*$ec->d0));
                $svg->addDimH(250, 50, 50, H3::n1($ec->d0/10));
                $svg->addDimV(70, 50, 70, ''. H3::n1($ec->b0/10));
                $svg->addDimV(120, 100, 70, ''. H3::n1($ec->b0 - 2*0.1*$ec->b0));
                $svg->addDimV(220, 50, 70, ''. H3::n1($ec->b0/10));
                $svg->addDimV(70, 200, 340, 'b='. H3::n1($ec->b0));
                $svg->addDimH(100, 200, 300, 'd='. H3::n1($ec->d0));
                $ec->svg($svg);
                unset($svg);
            }
            $ec->txt('', '(+) szélnyomás&nbsp;&nbsp;&nbsp; (-) szélszívás');
        }

        $ec->h1('Szabadon álló falak és mellvédek');
        $ec->boo('calcAttic', ['', 'Szabadon álló fal és mellvéd számítása'], false);
        if ($ec->calcAttic === true) {
            $ec->numeric('h_a', ['h_a', 'Fal magasság'], 1.2, 'm');
            $ec->numeric('l_a', ['l_a', 'Fal szélesség'], 40, 'm');
            $ec->numeric('x_a', ['x_a', 'Visszaforduló falszakasz hossza'], 10, 'm');
            $ec->lst('fi_a', ['Tömör' => 1.0, '20%-os áttörtség' => 0.8], ['', 'Áttörtség'], '1.0', '');
            $ec->note('20%-nál nagyobb áttörtség esetén a felületet rácsos tartóként kell kezelni.');
            $ec->math('l_a/h_a = '. H3::n1($ec->l_a/$ec->h_a).'%%%phi = '.$ec->fi_a);
            $atticTypeSource = ['b/h≤3' => 'a', 'b/h=5' => 'b', 'b/h≥10' => 'c',];
            $ec->lst('type_a', $atticTypeSource, ['', 'Tábla arány'], 'c', '');
            $atticFind = $ec->type_a;
            if ($ec->x_a >= $ec->h_a && $ec->fi_a == 1) {
                $atticFind = 'd';
            }
            if ($ec->fi_a == 0.8) {
                $atticFind = 'e';
            }
            $atticDb = [
                'a' => ['A' => 2.3, 'B' => 1.4, 'C' => 1.2, 'D' => 1.2],
                'b' => ['A' => 2.9, 'B' => 1.8, 'C' => 1.4, 'D' => 1.2],
                'c' => ['A' => 3.4, 'B' => 2.1, 'C' => 1.7, 'D' => 1.2],
                'd' => ['A' => 2.1, 'B' => 1.8, 'C' => 1.4, 'D' => 1.2],
                'e' => ['A' => 1.2, 'B' => 1.2, 'C' => 1.2, 'D' => 1.2],
            ];

            $caA = $atticDb[$atticFind]['A'];
            $caB = $atticDb[$atticFind]['B'];
            $caC = $atticDb[$atticFind]['C'];
            $caD = $atticDb[$atticFind]['D'];
            $aA = H3::n1(0.3*$ec->h_a);
            $aB = H3::n1(2*$ec->h_a - 0.3*$ec->h_a);
            $aC = H3::n1(2*$ec->h_a);
            $aD = H3::n1($ec->l_a - 4*$ec->h_a);
            $waA = H3::n2($caA*$ec->qpz);
            $waB = H3::n2($caB*$ec->qpz);
            $waC = H3::n2($caC*$ec->qpz);
            $waD = H3::n2($caD*$ec->qpz);
            $paA = H3::n2($waA*$ec->h_a);
            $paB = H3::n2($waB*$ec->h_a);
            $paC = H3::n2($waC*$ec->h_a);
            $paD = H3::n2($waD*$ec->h_a);

            $scheme = ['Zóna', 'Felületi erő: $w [(kN)/m^2]$', 'Vízszintes teherként értelmezve: $p$', 'Alaki tényező: $c$', 'Zóna szélesség $[m]$'];
            $tbl = [
                ['A', $waA.'<!--success-->', $paA, $caA, $aA],
                ['B', $waB.'<!--success-->', $paB, $caB, $aB],
                ['C', $waC.'<!--success-->', $paC, $caC, $aC],
                ['D', $waD.'<!--success-->', $paD, $caD, $aD],
            ];
            $ec->tbl($scheme, $tbl);
        }
    }
}
