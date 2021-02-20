<?php declare(strict_types = 1);
/**
 * cba FEM wrapper - Calculation class for Statika framework
 * (c) Bence VÁNKOS | https://structure.hu | https://github.com/r3sist/ecc-calculations
 */

namespace Statika\Calculations;

use Base;
use H3;
use Statika\Ec;
use Statika\EurocodeInterface;

Class Fem
{
    private Base $f3;

    public function __construct(Base $f3)
    {
        $this->f3 = $f3;
    }

    /**
     * @param Ec $ec
     */
    public function calc(EurocodeInterface $ec): void
    {
        $ec->note('[cba motorral](http://cbeam.sourceforge.net/cbeam_class.html)');
        $ec->note('Elsőrendű numerikus gerenda megoldó');

        $pathTemp = $_ENV['APP_PATH'].$this->f3->TEMP.'cba/';
        $gnuplotScriptFileName = $pathTemp.$this->f3->uid.'_gnuplot.txt';
        $gnuplotFigureFileName = $pathTemp.$this->f3->uid.'_figure.svg';
        $cbaResultsFileName = $pathTemp.$this->f3->uid.'_results.txt';
        $cbaInputFileName = $pathTemp.$this->f3->uid.'_input.txt';

        $ec->input('spans', ['', 'Tartó szakaszok hossza'], '5', 'm', 'Szóközzel elválasztott listája a fesztávolságoknak');

        $listPattern = '~(?:^|[=\s])\K\d{4}(?=\s|$)~mi';
        $spans = preg_replace($listPattern, '', $ec->spans);
        $spansArray = (array)explode(' ', $spans);

        $ec->input('constraints', ['', 'Támasz definíciók'], 'oo', '', '**`x` merev befogás, `o` csukló, `-` szabad csomópont.** Eggyel több elemű lista, mint a tartó szakaszok listája.');
        $ec->constraints = preg_replace('/\s+/', '', $ec->constraints); // Remove white space

        $constraints = '';
        $constraintOnPlot = '';
        $spanCounter = 0;
        $x = 0;
        foreach (str_split($ec->constraints) as $character) {
            switch ($character) {
                case 'o':
                    $constraints .= ' -1 0';
                    $pointType = '8';
                    break;
                case '-':
                    $constraints .= ' 0 0';
                    $pointType = '1';
                    break;
                case 'x':
                    $constraints .= ' -1 -1';
                    $pointType = '4';
                    break;
            }
            $constraintOnPlot .= "\n".'set label at '.$x.', 0, 0 "" point pointtype '.$pointType.' pointsize 3 lt rgb "grey"';
            $x += $spansArray[$spanCounter];
            $spanCounter++;
        }

        $ec->note('`q`: Teljes tartó szakaszon egyenletesen megoszló teher. `F` és `M`: Adott tartó szakaszon `x` pozícióban koncentrált erő/nyomaték.');
        $fields = [
            ['name' => 'span', 'title' => 'Szakasz száma', 'type' => 'input'],
            ['name' => 'q', 'title' => 'q [kNm/m]', 'type' => 'input'],
            ['name' => 'x', 'title' => 'Pozíció [m] >', 'type' => 'input'],
            ['name' => 'F', 'title' => 'F [kN]', 'type' => 'input'],
            ['name' => 'M', 'title' => 'M [kNm]', 'type' => 'input'],
        ];
        $ec->bulk('loads', $fields, [1,2,2.5,10,0]);

        $cbaInput = <<<EOS
            SPANS $spans
            INERTIA 1 1
            ELASTICITY 2.1e8
            CONSTRAINTS $constraints
            FACTORS 1 1
            EOS;

        // Loads
        if (!empty($ec->loads)) {
            foreach ($ec->loads as $key => $value) {
                if (isset($value['F'])) {
                    $cbaInput .= "\nLOAD ".$value['span'].' 2 '.$value['F'].' 0 '.$value['x'].' 0';
                }

                if (isset($value['M'])) {
                    $cbaInput .= "\nLOAD ".$value['span'].' 4 '.$value['M'].' 0 '.$value['x'].' 0';
                }

                if (isset($value['q'])) {
                    $cbaInput .= "\nLOAD ".$value['span'].' 1 '.$value['q'].' 0 0 0';
                }
            }
        }

        $gnuplotScript = '
            set terminal svg size 800,300
            set output "'.$gnuplotFigureFileName.'"
            set grid back lc rgb "#808080" lt 0 lw 1
            set lmargin 10
            set tmargin 1
            set xlabel "Gerenda pozíció [m]"
            #set offset graph 0.01, graph 0.01, graph 0.01, graph 0.01
            
            '.$constraintOnPlot.'
            
            plot "'.$cbaResultsFileName.'" using 1:($2*(-1)) title "M" with lines lt rgb "red", \
                 "'.$cbaResultsFileName.'" using 1:($3*(-1)) notitle with lines lt rgb "red", \
                 "'.$cbaResultsFileName.'" using 1:4 title "V" with lines lt rgb "blue", \
                 "'.$cbaResultsFileName.'" using 1:5 notitle with lines lt rgb "blue", \
                 0 notitle with line lw 5 lt rgb "black"
                 
            #plot "'.$cbaResultsFileName.'" using 1:($6*(-1000000)) title "Lehajlás" with lines lt rgb "green", \
            #     "'.$cbaResultsFileName.'" using 1:($7*(-1000000)) notitle with lines lt rgb "green", \
            #     0 notitle with line lw 5 lt rgb "black"
        ';

        // Generate cba input file
        $cbaInputFile = fopen($cbaInputFileName, 'wb');
        $bytes = fwrite($cbaInputFile,$cbaInput);
        fclose($cbaInputFile);

        // Run cba
        $cbaCommand = 'cba -i '.$pathTemp.$this->f3->uid.'_input.txt -p '.$cbaResultsFileName;
        $cbaCliOutput = shell_exec($cbaCommand);

        // Generate Gnuplot script file
        $gnuplotScriptFile = fopen($gnuplotScriptFileName, 'wb');
        $bytes = fwrite($gnuplotScriptFile,$gnuplotScript);
        fclose($gnuplotScriptFile);

        // Run Gnuplot
        $gnuplotCommand = 'gnuplot < '.$gnuplotScriptFileName;
        $gnuplotResult = shell_exec($gnuplotCommand);

        // Show figure and clean tmp folder
        if (is_file($gnuplotFigureFileName) && is_file($cbaResultsFileName)) {
            $ec->html(file_get_contents($gnuplotFigureFileName));
            $cbaResults = file_get_contents($cbaResultsFileName);
            unlink($gnuplotFigureFileName);
            unlink($cbaResultsFileName);
        }

        // Error checking
        if (strncmp($cbaCliOutput, 'continuous beam', 15) !==0 ) {
            $ec->danger('VEM hiba: '.$cbaCliOutput);
        }

        $ec->region0('results', 'Eredmények');
//            $ec->pre($cbaInput);
            $ec->pre($cbaCliOutput);
            $ec->pre($cbaResults);
        $ec->region1();

        $cbaResultsArray = [];
        foreach(explode(PHP_EOL, $cbaResults) as $line) {
            if ($line !== '' && $line[0] !== '#') {
                $lineArray = (array)explode("\t", $line);
                array_map('\floatval', $lineArray);
                $cbaResultsArray[$lineArray[0]] = $lineArray;
            }
        }

        $ec->numeric('x', ['x', 'Lekérdezés pozícióban'], 2, 'm', 'Gerenda szakaszok összevonásával értelmezett pozíció balról');
        $closestFloorX = (float)$ec->getFloorClosest(array_column($cbaResultsArray, 0), (string)$ec->x);
        $closestCeilX = (float)$ec->getCeilClosest(array_column($cbaResultsArray, 0), (string)$ec->x);
        $Mfloor = ($cbaResultsArray[(string)$closestFloorX][1] + $cbaResultsArray[(string)$closestFloorX][2]);
        $Mceil = ($cbaResultsArray[(string)$closestCeilX][1] + $cbaResultsArray[(string)$closestCeilX][2]);
        $Vfloor = ($cbaResultsArray[(string)$closestFloorX][3] + $cbaResultsArray[(string)$closestFloorX][4]);
        $Vceil = ($cbaResultsArray[(string)$closestCeilX][3] + $cbaResultsArray[(string)$closestCeilX][4]);
        $ec->note('$M('.$closestFloorX.') = '.$Mfloor.'$');
        $ec->note('$M('.$closestCeilX.') = '.$Mceil.'$');
        $ec->note('$V('.$closestFloorX.') = '.$Vfloor.'$');
        $ec->note('$V('.$closestCeilX.') = '.$Vceil.'$');
        $ec->note('Numerikus értékek között lineáris interpolációval számol.');
        $ec->math('M = '.H3::n2($ec->linterp($closestFloorX, $Mfloor, $closestCeilX, $Mceil, $ec->x)).' [kNm]');
        $ec->math('V = '.H3::n2($ec->linterp($closestFloorX, $Vfloor, $closestCeilX, $Vceil, $ec->x)).' [kNm]');
    }
}
