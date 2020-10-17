<?php declare(strict_types = 1);
// cba FEM wrapper - Calculation class for ECC framework
// (c) Bence VÁNKOS | https://structure.hu | https://github.com/r3sist/ecc-calculations

namespace Calculation;

use \Base;
use \Ecc\Blc;
use \Ec\Ec;

Class Fem
{
    public function calc(Base $f3, Blc $blc, Ec $ec): void
    {
//        $blc->note('[cba motorral](http://cbeam.sourceforge.net/cbeam_class.html)');
        $blc->note('Elsőrendű numerikus gerenda megoldó');

        $pathTemp = PATH.$f3->TEMP.'cba/';

        $blc->input('spans', ['', 'Tartó szakaszok hossza'], '5', 'm', 'Szóközzel elválasztott listája a fesztávolságoknak');

        $listPattern = '~(?:^|[=\s])\K\d{4}(?=\s|$)~mi';
        $spans = preg_replace($listPattern, '', $f3->_spans);

        $blc->input('constraints', ['', 'Támasz definíciók'], 'oo', '', '**`x` merev befogás, `o` csukló, `-` szabad csomópont.** Eggyel több elemű lista, mint a tartó szakaszok listája.');
        $f3->_constraints = preg_replace('/\s+/', '', $f3->_constraints); // Remove white space

        $constraints = '';
        foreach (str_split($f3->_constraints) as $character) {
            switch ($character) {
                case 'o':
                    $constraints .= ' -1 0';
                    break;
                case '-':
                    $constraints .= ' 0 0';
                    break;
                case 'x':
                    $constraints .= ' -1 -1';
                    break;
            }
        }

        $blc->txt('Terhek definiálása:');
        $blc->note('`q`: Teljes tartó szakaszon egyenletesen megoszló teher. `F` és `M`: Adott tartó szakaszon `x` pozícióban koncentrált erő/nyomaték.');
        $fields = [
            ['name' => 'span', 'title' => 'Szakasz száma', 'type' => 'input'],
            ['name' => 'q', 'title' => 'q [kNm/m]', 'type' => 'input'],
            ['name' => 'x', 'title' => 'Pozíció [m] >', 'type' => 'input'],
            ['name' => 'F', 'title' => 'F [kN]', 'type' => 'input'],
            ['name' => 'M', 'title' => 'M [kNm]', 'type' => 'input'],
        ];
        $blc->bulk('loads', $fields, [1,2,2.5,10,0]);

        $cbaInput = '        
            SPANS '.$spans.'
            INERTIA 1 1
            ELASTICITY 2.1e8
            CONSTRAINTS '.$constraints.'
            FACTORS 1 1
        ';

        // Loads
        if (!empty($f3->_loads)) {
            foreach ($f3->_loads as $key => $value) {
                if (isset($value['F'])) {
                    $cbaInput .= "\nLOAD ".$value['span']." 2 ".$value['F'].' 0 '.$value['x'].' 0';
                }

                if (isset($value['M'])) {
                    $cbaInput .= "\nLOAD ".$value['span']." 4 ".$value['M'].' 0 '.$value['x'].' 0';
                }

                if (isset($value['q'])) {
                    $cbaInput .= "\nLOAD ".$value['span']." 1 ".$value['q'].' 0 0 0';
                }
            }
        }

        $gnuplotScript = '
            set terminal svg size 800,300
            set output "'.$pathTemp.$f3->uid.'_figure.svg"
            set grid back lc rgb "#808080" lt 0 lw 1
            set lmargin 10
            set tmargin 1
            set xlabel "Gerenda pozíció [m]"
            #set offset graph 0.01, graph 0.01, graph 0.01, graph 0.01
            
            plot "'.$pathTemp.$f3->uid.'_results.txt" using 1:($2*(-1)) title "M" with lines lt rgb "red", \
                 "'.$pathTemp.$f3->uid.'_results.txt" using 1:($3*(-1)) notitle with lines lt rgb "red", \
                 "'.$pathTemp.$f3->uid.'_results.txt" using 1:4 title "V" with lines lt rgb "blue", \
                 "'.$pathTemp.$f3->uid.'_results.txt" using 1:5 notitle with lines lt rgb "blue", \
                 0 notitle with line lw 5 lt rgb "black"
                 
            #plot "'.$pathTemp.$f3->uid.'_results.txt" using 1:($6*(-1000000)) title "Lehajlás" with lines lt rgb "green", \
            #     "'.$pathTemp.$f3->uid.'_results.txt" using 1:($7*(-1000000)) notitle with lines lt rgb "green", \
            #     0 notitle with line lw 5 lt rgb "black"
            
        ';

        // Generate cba input file
        $cbaInputFile = fopen($pathTemp.$f3->uid.'_input.txt', 'wb');
        $bytes = fwrite($cbaInputFile,$cbaInput);
        fclose($cbaInputFile);

        // Run cba
        $resultsFileName = $pathTemp.$f3->uid.'_results.txt';
        $cbaCommand = 'cba -i '.$pathTemp.$f3->uid.'_input.txt -p '.$resultsFileName;
        $cbaResult = shell_exec($cbaCommand);

        // Generate Gnuplot script file
        $gnuplotScriptFile = fopen($pathTemp.$f3->uid.'_gnuplot.txt', 'wb');
        $bytes = fwrite($gnuplotScriptFile,$gnuplotScript);
        fclose($gnuplotScriptFile);

        // Run Gnuplot
        $gnuplotCommand = 'gnuplot < '.$pathTemp.$f3->uid.'_gnuplot.txt';
        $gnuplotResult = shell_exec($gnuplotCommand);

        // Show figure and clean tmp folder
        $figureName = $pathTemp.$f3->uid.'_figure.svg';
        if (is_file($figureName) && is_file($resultsFileName)) {
            $blc->html(file_get_contents($figureName));
            unlink($figureName);
            unlink($resultsFileName);
        }

        // Error checking
        if (strncmp($cbaResult, 'continuous beam', 15) !==0 ) {
            $blc->danger('VEM hiba: '.$cbaResult);
        }

        $blc->region0('results', 'Eredmények');
            $blc->pre($cbaInput);
            $blc->pre($cbaResult);
        $blc->region1();
    }

}
