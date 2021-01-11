<?php declare(strict_types = 1);
/**
 * Math routines and incubator of test calculations for structural design - Calculation class for Statika framework
 * (c) Bence VÁNKOS | https://structure.hu | https://github.com/r3sist/ecc-calculations
 */

namespace Statika\Calculations;

use ASCIIMath2MathML\ASCIIMathPHP;
use \H3;
use Statika\EurocodeInterface;

Class Math
{
    private ASCIIMathPHP $ASCIIMathPHP;

    public function __construct(ASCIIMathPHP $ASCIIMathPHP)
    {
        $this->ASCIIMathPHP = $ASCIIMathPHP;
    }

    /**
     * @param Ec $ec
     */
    public function calc(EurocodeInterface $ec): void
    {
        $ec->h1('ASCIIMath 2 docx');
        $ec->input('ASCIIMath', ['', 'ASCIIMath bemenet'], 'x_2', '', 'Tetszőleges [ASCIIMath](http://asciimath.org) szöveg mutatása');
        $ec->math($ec->ASCIIMath, 'MathJAX kimenet');
//        $ec->img('https://structure.hu/ecc/mathASCIIMath.jpg', 'Jobb egérgombbal másolt MathML kód beilleszthető MS Wordbe');

        $this->ASCIIMathPHP->setExpr($ec->ASCIIMath);
        $this->ASCIIMathPHP->genMathML();
        $ec->html('<button type="button" onclick="copyText()" class="btn btn-success float-start me-3"><span class="ti-layers"></span> Másol:</button><input type="text" name="ASCIIMath_MathML" id="ASCIIMath_MathML" class="form-control col-1" value="'. htmlentities($this->ASCIIMathPHP->getMathML(), ENT_QUOTES | ENT_HTML5) .'">'.<<<EOS
            <script>
                function copyText() {
                  var inputElement = document.getElementById("ASCIIMath_MathML");
                  
                  console.log(inputElement.value);
                
                  inputElement.select();
                  // inputElement.setSelectionRange(0, 99999); /* For mobile devices */

                  document.execCommand("copy");

                  console.log("Copied the text: " + inputElement.value);
                }
            </script>
            EOS);
        $ec->h1('Lejtés');
        $ec->numeric('slope', ['', 'Lejtés'], 3, '% vagy °', '');
        $slope_deg = rad2deg(atan($ec->slope/100));
        $slope_per = tan(deg2rad($ec->slope))*100;
        $ec->def('slope_deg', H3::n2($slope_deg), $ec->slope.'% = %% [deg]', '');
        $ec->def('slope_per', H3::n2($slope_per), $ec->slope.'[deg] = %% [%]', '');
        $ec->numeric('L', ['L', 'Hossz'], 10, 'm', '');
        $ec->def('hdeg', H3::n2($ec->L*$ec->slope*0.01), 'h_('.$ec->slope.'%) = %% [m]', 'Emelkedés ');
        $ec->def('hper', H3::n2($ec->L*$slope_per*0.01), 'h_('. H3::n2($slope_per).'%) = %% [m]', 'Emelkedés');

        $ec->h1('Hőmérséklet rudakon');
        $ec->math('L = '.$ec->L.'[m]', 'Rúdhossz');
        $ec->def('alpha_T_st', number_format(0.000012, 6), 'alpha_(T,steel) = %% [1/K]', '');
        $ec->numeric('DeltaT', ['Delta T', 'Hőmérséklet változás'], 40, 'deg', '');
        $ec->def('DeltaL', H3::n2($ec->alpha_T_st*($ec->L*1000)*$ec->DeltaT), 'Delta L = %% [mm]', '');

        $ec->h1('Lineáris interpoláció');
        $ec->numeric('x1', ['x_1', ''], 1, '', '');
        $ec->numeric('y1', ['y_1', ''], 5, '', '');
        $ec->numeric('x2', ['x_2', ''], 2, '', '');
        $ec->numeric('y2', ['y_2', ''], 20, '', '');
        $ec->info0();
            $ec->numeric('x', ['x', ''], 3, '', '');
            $ec->def('y', (($ec->x - $ec->x1)*($ec->y2 - $ec->y1)/($ec->x2 - $ec->x1)) + $ec->y1, 'y = %%');
        $ec->info1();

        $ec->jsxDriver();
        $js = '
            var b = JXG.JSXGraph.initBoard("interpolation", {boundingbox: ['.min($ec->x/2, $ec->x1/2, $ec->x2/2).', '.max($ec->y*2, $ec->y1*2, $ec->y2*2).', '.max($ec->x*2, $ec->x1*2, $ec->x2*2).', '.min($ec->y/2, $ec->y1/2, $ec->y2/2).'], axis:true, showCopyright:false, keepaspectratio: false, showNavigation: true});
            var p1 = b.create("point", ['.$ec->x1.', '.$ec->y1.'], {name: "x1, y1", size:2, fillColor: "blue", strokeColor: "blue"});
            var p2 = b.create("point", ['.$ec->x2.', '.$ec->y2.'], {name: "x2, y2", size:2, fillColor: "blue", strokeColor: "blue"});
            var li = b.create("line",["x1, y1","x2, y2"], {strokeColor:"#00ff00", strokeWidth:2});
            var p2 = b.create("point", ['.$ec->x.', '.$ec->y.'], {name: "x, y", size:2, attractors: [li], attractorDistance:0.2, snatchDistance: 2});
            ';
        $ec->jsx('interpolation', $js, 200);

        $ec->h1('Cső tömeg számítás');
        $ec->numeric('D', ['D', 'Cső külső átmérő'], 600, 'mm', '');
        $ec->numeric('t', ['t', 'Cső falvastagság'], 12, 'mm', '');
        $ec->def('d', $ec->D - 2*$ec->t, 'd = D- 2*t = %% [mm]', 'Belső átmérő');
        $ec->def('As', $ec->A($ec->D) - $ec->A($ec->d), 'A_(steel) = (D^2 pi)/4 - (d^2 pi)/4 = %% [mm^2]');
        $ec->def('Al', $ec->A($ec->d), 'A_(liqu i d) = %% [mm^2]');
        $ec->def('gs', 78.5, 'gamma_(steel) = %% [(kN)/m^3]', '');
        $ec->numeric('gl', ['gamma_(liqu i d)', 'Folyadék fajsúly'], 10, 'kN/m3', '');
        $ec->def('qk', H3::n3(($ec->As/1000000)*$ec->gs + ($ec->Al/1000000)*$ec->gl), 'q_k = A_(steel)*gamma_(sttel) + A_(liqu i d)*gamma_(liqu i d) = %% [(kN)/(fm)]');

        $ec->img('https://structure.hu/ecc/piperack0.jpg', 'Erőterv/APOLLO');

        $ec->h1('Mean diameter');
        $ec->note('The value of the mean diameter $d_m$ is estimated as follows. The distance across flats $s$ of the nut is given in the standard *ISO 898-2*. By approximately ignoring the corner rounding for a perfect hexagon the relation of the distance across points $s\'$ and the distance across flats $s$ is $s\' = s / cos(30°) = 1.1547*s$. Therefore the mean diameter $d_m$ is approximately: $d_m = (s + 1.1547*s)/2=1.07735*s$');
        $ec->numeric('s', ['s', 'Szemben lévő felületek távolsága csavarfejen'], 10, 'mm', 'ISO 898-2');
        $ec->def('dm', H3::n1(1.07735*$ec->s), 'd_m = %% [mm]', '');

        $ec->h1('NAGÉV tömeg');
        $ec->lst('nagevh', [20, 25, 30, 35, 40, 45, 50, 60, 70, 80, 90, 100], ['h [mm]', 'Járórács magasság'], 30);
        $ec->lst('nagevv', [2, 3, 4 , 5], ['v [mm]', 'Borda vastagság'], 2);
        $ec->lst('nagevl', [11, 22, 33, 44, 55, 66], ['l [mm]', 'Borda osztásköz'], 33);
        $ec->def('nagevn', ceil(1000/$ec->nagevl) + 1, 'n = 1000/l + 1 = %% [db]', 'Bordák száma 1 m-en.');
        $ec->note('Mellékbordát fél magassággal veszi figyelembe.');
        $ec->def('nagevg', ceil($ec->nagevn*$ec->nagevh*1.5*$ec->nagevv*1000/1000000000*7850), 'g = n*1.5*h*7850[(kg)/m^3] = %% [(kg)/m^2]', '');

        /* $ec->h1('Teherelemzés');
        $ec->h2('Terhek');
        $ec->input('m', ['m', 'Teherátadási módosító tényező'], 1.15, '', 'Trapézlemez többtámaszú hatása, közbenső támasznál; 1-től 1.25-ig', 'numeric|min_numeric,1|max_numeric,2');

        $ec->h3('Rétegrend');
        $ec->numeric('glk', ['g_(l,k)', 'Rétegrend karakterisztikus felületi terhe'], 0.6, 'kN/m2');
        $ec->math('gamma_G = ' . $ec->_GG);

        $ec->h3('Hasznos teher');
        $ec->numeric('qq', ['q_(q,k)', 'Hasznos felületi teher karakterisztikus értéke'], 5, 'kN/m2', '');

        $ec->h3('Installációs és egyéb terhek');
        $ec->numeric('qik', ['q_(i,k)', 'Installációs felületi teher karakterisztikus értéke'], 0.5, 'kN/m2');
        $ec->math('gamma_Q = ' . $ec->_GQ);

        $ec->h3('Szélnyomásból adódó teher');
        $ec->boo('qwkcalc', '$q_p(z)$ számítása itt', false);
        if (!$ec->qwkcalc) {
            $ec->numeric('qwk', ['q_(w,k)', 'Szél felületi teher karakterisztikus értéke'], 0.4, 'kN/m2', 'Torlónyomásból, belső szélnyomással, szélnyomáshoz ( **I** ) zóna');
        } else {
            $moduleQpz = new \Calculation\Wind($f3, $blc, $ec); // TODO DI
            $ec->info0();
                $moduleQpz->moduleQpz();
            $ec->info1();
            $ec->def('qwk', \H3::n2((abs($ec->cm) + 0.2)*$ec->qpz), 'q_(w,k,I+) = (abs(c_m) + 0.2)*q_p(z) = %% [(kN)/m^2]', 'Torlónyomásból, belső szélnyomással, szélnyomáshoz ( **I** ) zóna');
        }

        $ec->h3('Hó/hózug terhe');
        $ec->numeric('qsk', ['q_(s,k)', 'Hó/hózug felületi teher karakterisztikus értéke'], 1, 'kN/m2', '');
        */
    }
}
