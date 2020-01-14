<?php declare(strict_types = 1);
// Wind load analysis according to Eurocodes - Calculation class for ECC framework
// (c) Bence VÁNKOS | https://structure.hu | https://github.com/r3sist/ecc-calculations

namespace Calculation;

use \Base;
use \Ecc\Blc;
use \Ec\Ec;
use \H3;
use \resist\SVG\SVG;

Class Wind
{
    public $f3;
    private $blc;
    private $ec;

    public function __construct(Base $f3, Blc $blc, Ec $ec)
    {
        $this->f3 = $f3;
        $this->blc = $blc;
        $this->ec = $ec;
    }

    public function moduleQpz(): void
    {
        if (!$this->f3->exists('_reslog')) {
            $this->f3->_reslog = '';
        }

        $this->f3->_reslog .=$this->blc->numeric('h', ['h', 'Épület magasság'], 10, 'm');
        $this->f3->_reslog .=$this->blc->numeric('b', ['b', 'Épület hossz'], 20, 'm');
        $this->f3->_reslog .=$this->blc->numeric('d', ['d', 'Épület szélesség'], 12, 'm');

        $terrainCats = [
            'I. Nyílt terep' => 1,
            'II. Mezőgazdasági terület' => 2,
            'III. Alacsony beépítés' => 3,
            'IV. Intenzív beépítés' => 4,
        ];
        $this->f3->_reslog .=$this->blc->lst('terrainCat', $terrainCats, ['', 'Terep kategória'], '2');

        $this->blc->boo('internal', ['', 'Belső szél figyelembevétele'], true);

        $this->blc->region0('more0', 'További paraméterek');
            $this->blc->numeric('hz', ['h_z', 'Terepszint feletti magasság'], 0, 'm', '$qp(z)$ számításakor hozzáadódik a $h$ magassághoz, de a zónaszélesség számításához nem (pl. tetőfelépítmények esetében).');

            if ($this->f3->_internal) {
                $this->blc->numeric('cp', ['c_(p,i+)', 'Belső szél alaki tényező belső nyomáshoz'], 0.2, '');
                $this->blc->numeric('cm', ['c_(p,i-)', 'Belső szél alaki tényező belső szíváshoz'], -0.3, '');
            } else {
                $this->f3->set('_cp', 0);
                $this->f3->set('_cm', 0);
            }

            $this->blc->boo('flatRef', ['10 [m^2]', 'referencia felület'], true, 'Egyébként $1 [m^2]$');
            if ($this->f3->_flatRef == 1) {
                $this->f3->set('_flatRef', '10');
            } else {
                $this->f3->set('_flatRef', '1');
            }
            $this->blc->math('v_(b,0) = 23.6 [m/s]');
            $this->blc->math('c_(dir) = 1.0');
            $this->blc->math('c_(season) = 1.0');
            $this->blc->math('c_(prob) = 1.0');
        $this->blc->region1();

        $this->blc->success0();
            if ($this->f3->_hz > 0) {
                $h = $this->f3->_h + $this->f3->_hz;
                $this->blc->txt('$h = '.$h.' [m]$ magasság figyelembevételével:');
            } else {
                $h=$this->f3->_h;
            }
            $this->f3->_reslog .= $this->blc->def('qpz', $this->ec->qpz($h, $this->f3->_terrainCat), 'q_(p)(z, cat) = %% [(kN)/m^2]', 'Torlónyomás');
        $this->blc->success1();
    }

    public function calc(Base $f3, Blc $blc, Ec $ec): void
    {
        $this->f3->_reslog = '';

        $blc->math('psi = 0.6//0.2//0', 'Kombinációs tényezők');

        $this->moduleQpz();

        $blc->boo('calcFlat', ['', 'Lapostető számítása'], false);
        if ($f3->_calcFlat === true) {
            $blc->h1('Lapostetők');
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
            $blc->lst('flatType', $flatTypes, ['', 'Tető kialakítás'], 'a');

            $blc->note('Külső szélnyomás esetén belső szélszívással számol: *F, G, H* szívott zónák szélszívása ezért kisebb szélnyomás esetben!');

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
                    $f3->set('_b0', $f3->get('_b'));
                    $f3->set('_d0', $f3->get('_d'));
                } else {
                    $f3->set('_b0', $f3->get('_d'));
                    $f3->set('_d0', $f3->get('_b'));
                }

                if ($case['wind'] === '-') {
                    $ci = -1*$f3->_cp;
                } else {
                    $ci = -1*$f3->_cm;
                }

                $blc->h3($case['case']);

                $cpF0 = $flatDb[$f3->_flatType . $case['wind']]['F'.$f3->_flatRef];
                $cpF = $cpF0 + $ci;
                $cpG0 = $flatDb[$f3->_flatType . $case['wind']]['G'.$f3->_flatRef];
                $cpG = $cpG0 + $ci;
                $cpH0 = $flatDb[$f3->_flatType . $case['wind']]['H'.$f3->_flatRef];
                $cpH = $cpH0 + $ci;
                $cpI0 = $flatDb[$f3->_flatType . $case['wind']]['I'.$f3->_flatRef];
                $cpI = $cpI0 + $ci;
                $wF = H3::n2($cpF*$f3->_qpz);
                $wG = H3::n2($cpG*$f3->_qpz);
                $wH = H3::n2($cpH*$f3->_qpz);
                $wI = H3::n2($cpI*$f3->_qpz);
                $e = min($f3->_b0, 2*$f3->_h);
                $blc->math('e = min{(b_0),(2*h):} = '.$e.' [m]', '');
                $aF = \H3::n1($e/4);
                $aG = \H3::n1($f3->_b0-2*($e/4));
                $aH = \H3::n1($f3->_b0);
                $aI = \H3::n1(($f3->_d0 - $e/2 > 0 ? $f3->_b0 : 0));
                $bF = \H3::n1($e/10);
                $bG = \H3::n1($e/10);
                $bH = \H3::n1($e/2-$e/10);
                $bI = \H3::n1(($f3->_d0 - $e/2 > 0 ? $f3->_d0 - $e/2 : 0));
                $blc->txt('$c_(p,i) = '.$ci.'$ többlet, belső szélből', 'Belső szél előjele domináns szélhez igazítva: külső szíváshoz belső nyomás és fordítva.');

                $scheme = ['Zóna', 'Felületi erő: $w [(kN)/m^2]$', 'Alaki tényező: $c (c_p, c_i)$', 'Zóna szélesség $[m]$', 'Zóna mélység $[m]$'];
                $tbl = [
                    ['F', $wF.'<!--success-->', $cpF. ' ('.$cpF0.', '.$ci.')', $aF, $bF],
                    ['G', $wG.'<!--success-->', $cpG. ' ('.$cpG0.', '.$ci.')', $aG, $bG],
                    ['H', $wH.'<!--success-->', $cpH. ' ('.$cpH0.', '.$ci.')', $aH, $bH],
                    ['I', $wI.'<!--success-->', $cpI. ' ('.$cpI0.', '.$ci.')', $aI, $bI],
                ];
                $blc->tbl($scheme, $tbl);

                $svg = new SVG(600, 350);
                // Raw part:
                $svg->setColor('blue');
                $svg->addSymbol(0, 210, 'control-shuffle');
                $svg->setColor('');
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
                $svg->addDimH(250, 50, 100, H3::n1(($f3->_d0 - $e/2 > 0 ? $f3->_d0 - $e/2 : 0))); // F dim
                $svg->addDimV(140, 170, 330, 'b='. H3::n1($f3->_b0));
                $svg->addDimV(140, 50, 80, ''. H3::n1($e/4));
                $svg->addDimV(190, 70, 80, ''. H3::n1($f3->_b0 - $e/2));
                $svg->addDimV(260, 50, 80, ''. H3::n1($e/4));
                $svg->addDimH(100, 200, 340, 'd='. H3::n1($f3->_d0));
                $blc->svg($svg);
                unset($svg);
            }
        }

        $blc->boo('calcWall', ['', 'Fal számítása'], false);
        if ($f3->_calcWall === true) {
            $blc->h1('Falak');
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
                $blc->h3($case['case']);

                if ($case['dir'] === 0) {
                    $f3->set('_b0', $f3->get('_b'));
                    $f3->set('_d0', $f3->get('_d'));
                } else {
                    $f3->set('_b0', $f3->get('_d'));
                    $f3->set('_d0', $f3->get('_b'));
                }

                $blc->def('wallRow', H3::n2($f3->_h/$f3->_d0), 'h/d = %%', '');

                if ($f3->_wallRow >= 5) {
                    $find = 'a';
                } elseif ($f3->_wallRow <= 0.25) {
                    $find = 'c';
                } else {
                    $find = 'b';
                }
                $blc->md('Táblázat: *'.$find.'* sor');

                $e = min($f3->_b0, 2*$f3->_h);
                $cpA0 = $wallDb[$find]['A'.$f3->_flatRef];
                $cpA = $cpA0 - $f3->_cp;
                $cpB0 = $wallDb[$find]['B'.$f3->_flatRef];
                $cpB = $cpB0 - $f3->_cp;
                $cpC0 = $wallDb[$find]['C'.$f3->_flatRef];
                $cpC = $cpC0 - $f3->_cp;
                $cpD0 = $wallDb[$find]['D'.$f3->_flatRef];
                $cpD = $cpD0 - $f3->_cm;
                $cpE0 = $wallDb[$find]['E'.$f3->_flatRef];
                $cpE = $cpE0 - $f3->_cp;
                $wA = H3::n2($cpA*$f3->_qpz);
                $wB = H3::n2($cpB*$f3->_qpz);
                $wC = ($f3->_d0 - $e > 0 ? H3::n2($cpC*$f3->_qpz) : 0);
                $wD = H3::n2($cpD*$f3->_qpz);
                $wE = H3::n2($cpE*$f3->_qpz);
                $widthA = \H3::n1($e/5);
                $widthB = H3::n1(($e < $f3->_d0 ? $e - $e/5 : $f3->_d0 - $e/5));
                $widthC = \H3::n1(($f3->_d0 - $e > 0 ? $f3->_d0 - $e : 0));
                $widthD = \H3::n1($f3->_b0);
                $widthE = \H3::n1($f3->_b0);

                $blc->math('e = min{(b),(2h):} = '.$e.'', '');
                $blc->txt('', 'Belső szél előjele domináns szélhez igazítva: külső szíváshoz belső nyomás és fordítva.');

                $scheme = ['Zóna', 'Felületi erő: $w [(kN)/m^2]$', 'Alaki tényező: $c (c_p, c_i)$', 'Zóna szélesség $[m]$'];
                $tbl = [
                    ['A', $wA.'<!--success-->', $cpA. ' ('.$cpA0.', '.$f3->_cp.')', $widthA],
                    ['B', $wB.'<!--success-->', $cpB. ' ('.$cpB0.', '.$f3->_cp.')', $widthB],
                    ['C', $wC.'<!--success-->', $cpC. ' ('.$cpC0.', '.$f3->_cp.')', $widthC],
                    ['D', $wD.'<!--success-->', $cpD. ' ('.$cpD0.', '.$f3->_cm.')', $widthD],
                    ['E', $wE.'<!--success-->', $cpE. ' ('.$cpE0.', '.$f3->_cp.')', $widthE],
                ];
                $blc->tbl($scheme, $tbl);

                $svg = new SVG(600, 350);
                // Generate raw part:
                $svg->setColor('blue');
                $svg->addSymbol(0, 210, 'control-shuffle');
                $svg->setColor('');
                $svg->addRectangle(100, 50, 200, 50); // Building section
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
                $svg->addDimH(100, 200, 340, 'd='. H3::n2($f3->_d0));
                $blc->svg($svg);
                unset($svg);
            }
        }

        $blc->boo('calcCanopy', ['', 'Oldalain nyitott ferdesíkú pilletető számítása']);
        if ($f3->_calcCanopy === true) {
            $blc->h1('Oldalain nyitott ferdesíkú pilletető');
            $blc->boo('phi', ['Phi', 'Torlasz'], true, '');

            $canopyTypes = ['0°' => '0', '5°' => '5', '10°' => '10', '15°' => '15', '20°' => '20', '25°' => '25', '30°' => '30'];
            $blc->lst('canopyType', $canopyTypes, ['', 'Tető hajlás'],'0');
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
                $blc->h3($case['case']);

                if ($case['dir'] === 0) {
                    $f3->set('_b0', $f3->get('_b'));
                    $f3->set('_d0', $f3->get('_d'));
                } else {
                    $f3->set('_b0', $f3->get('_d'));
                    $f3->set('_d0', $f3->get('_b'));
                }

                if ($case['wind'] === '-') {
                    $find = $f3->_canopyType . $case['wind'] . $f3->_phi;
                } else {
                    $find = $f3->_canopyType . $case['wind'];
                }

                $cpA = $canopyDb[$find]['A'];
                $cpB = $canopyDb[$find]['B'];
                $cpC = $canopyDb[$find]['C'];
                $wA = H3::n2($cpA*$f3->_qpz);
                $wB = H3::n2($cpB*$f3->_qpz);
                $wC = H3::n2($cpC*$f3->_qpz);
                $aA = 0;
                $aB = H3::n1($f3->_b0/10);
                $aC = H3::n1($f3->_d0/10);

                $scheme = ['Zóna', 'Felületi erő: $w [(kN)/m^2]$', 'Alaki tényező: $c$', 'Zóna szélesség $[m]$'];
                $tbl = [
                    ['A', $wA.'<!--success-->', $cpA, ''],
                    ['B', $wB.'<!--success-->', $cpB, $aB],
                    ['C', $wC.'<!--success-->', $cpC, $aC],
                ];
                $blc->tbl($scheme, $tbl);

                $svg = new SVG(600, 300);
                // Generate raw part:
                $svg->setColor('blue');
                $svg->addSymbol(0, 150, 'control-shuffle');
                $svg->setColor('');
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
                $svg->addDimH(100, 50, 50, H3::n1($f3->_d0/10));
                $svg->addDimH(150, 100, 50, H3::n1($f3->_d0 - 2*0.1*$f3->_d0));
                $svg->addDimH(250, 50, 50, H3::n1($f3->_d0/10));
                $svg->addDimV(70, 50, 70, ''. H3::n1($f3->_b0/10));
                $svg->addDimV(120, 100, 70, ''. H3::n1($f3->_b0 - 2*0.1*$f3->_b0));
                $svg->addDimV(220, 50, 70, ''. H3::n1($f3->_b0/10));
                $svg->addDimV(70, 200, 340, 'b='. H3::n1($f3->_b0));
                $svg->addDimH(100, 200, 300, 'd='. H3::n1($f3->_d0));
                $blc->svg($svg);
                unset($svg);
            }
            $blc->txt('', '(+) szélnyomás&nbsp;&nbsp;&nbsp; (-) szélszívás');
        }

        $blc->boo('calcAttic', ['', 'Szabadon álló fal és mellvéd számítása'], false);
        if ($f3->_calcAttic === true) {
            $blc->h1('Szabadon álló falak és mellvédek');
            $blc->numeric('h_a', ['h_a', 'Fal magasság'], 1.2, 'm');
            $blc->numeric('l_a', ['l_a', 'Fal szélesség'], 40, 'm');
            $blc->numeric('x_a', ['x_a', 'Visszaforduló falszakasz hossza'], 10, 'm');
            $blc->lst('fi_a', ['Tömör' => 1.0, '20%-os áttörtség' => 0.8], ['', 'Áttörtség'], '1.0', '');
            $blc->note('20%-nál nagyobb áttörtség esetén a felületet rácsos tartóként kell kezelni.');
            $blc->math('l_a/h_a = '. H3::n1($f3->_l_a/$f3->_h_a).'%%%phi = '.$f3->_fi_a);
            $atticTypeSource = ['b/h≤3' => 'a', 'b/h=5' => 'b', 'b/h≥10' => 'c',];
            $blc->lst('type_a', $atticTypeSource, ['', 'Tábla arány'], 'c', '');
            $atticFind = $f3->_type_a;
            if ($f3->_x_a >= $f3->_h_a && $f3->_fi_a == 1) {
                $atticFind = 'd';
            }
            if ($f3->_fi_a == 0.8) {
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
            $aA = \H3::n1(0.3*$f3->_h_a);
            $aB = \H3::n1(2*$f3->_h_a - 0.3*$f3->_h_a);
            $aC = \H3::n1(2*$f3->_h_a);
            $aD = \H3::n1($f3->_l_a - 4*$f3->_h_a);
            $waA = H3::n2($caA*$f3->_qpz);
            $waB = H3::n2($caB*$f3->_qpz);
            $waC = H3::n2($caC*$f3->_qpz);
            $waD = H3::n2($caD*$f3->_qpz);
            $paA = H3::n2($waA*$f3->_h_a);
            $paB = H3::n2($waB*$f3->_h_a);
            $paC = H3::n2($waC*$f3->_h_a);
            $paD = H3::n2($waD*$f3->_h_a);

            $scheme = ['Zóna', 'Felületi erő: $w [(kN)/m^2]$', 'Vízszintes teherként értelmezve: $p$', 'Alaki tényező: $c$', 'Zóna szélesség $[m]$'];
            $tbl = [
                ['A', $waA.'<!--success-->', $paA, $caA, $aA],
                ['B', $waB.'<!--success-->', $paB, $caB, $aB],
                ['C', $waC.'<!--success-->', $paC, $caC, $aC],
                ['D', $waD.'<!--success-->', $paD, $caD, $aD],
            ];
            $blc->tbl($scheme, $tbl);
        }
    }
}
