<?php declare(strict_types = 1);
// Math routines and incubator of test calculations for structural design - Calculation class for ECC framework
// (c) Bence VÁNKOS | https://structure.hu | https://github.com/r3sist/ecc-calculations

namespace Calculation;

use \Base;
use \Ecc\Blc;
use \Ec\Ec;
use \H3;

Class Math
{
    public function calc(Base $f3, Blc $blc, Ec $ec): void
    {
        $blc->toc();

        $blc->h1('ASCIIMath 2 docx');
        $blc->input('ASCIIMath', ['', 'ASCIIMath bemenet'], 'x_2', '', 'Tetszőleges [ASCIIMath](http://asciimath.org) szöveg mutatása');
        $blc->math($f3->_ASCIIMath, 'MathJAX kimenet');
        $blc->img('https://structure.hu/ecc/mathASCIIMath.jpg', 'Jobb egérgombbal másolt MathML kód beilleszthető MS Wordbe');

        $blc->h1('Lejtés');
        $blc->numeric('slope', ['', 'Lejtés'], 3, '% vagy °', '');
        $slope_deg = rad2deg(atan($f3->_slope/100));
        $slope_per = tan(deg2rad($f3->_slope))*100;
        $blc->def('slope_deg', H3::n2($slope_deg), $f3->_slope.'% = %% [deg]', '');
        $blc->def('slope_per', H3::n2($slope_per), $f3->_slope.'[deg] = %% [%]', '');
        $blc->numeric('L', ['L', 'Hossz'], 10, 'm', '');
        $blc->def('hdeg', H3::n2($f3->_L*$f3->_slope*0.01), 'h_('.$f3->_slope.'%) = %% [m]', 'Emelkedés ');
        $blc->def('hper', H3::n2($f3->_L*$slope_per*0.01), 'h_('. H3::n2($slope_per).'%) = %% [m]', 'Emelkedés');

        $blc->h1('Hőmérséklet rudakon');
        $blc->math('L = '.$f3->_L.'[m]', 'Rúdhossz');
        $blc->def('alpha_T_st', number_format(0.000012, 6), 'alpha_(T,steel) = %% [1/K]', '');
        $blc->numeric('DeltaT', ['Delta T', 'Hőmérséklet változás'], 40, 'deg', '');
        $blc->def('DeltaL', H3::n2($f3->_alpha_T_st*($f3->_L*1000)*$f3->_DeltaT), 'Delta L = %% [mm]', '');

        $blc->h1('Lineáris interpoláció');
        $blc->numeric('x1', ['x_1', ''], 1, '', '');
        $blc->numeric('y1', ['y_1', ''], 5, '', '');
        $blc->numeric('x2', ['x_2', ''], 2, '', '');
        $blc->numeric('y2', ['y_2', ''], 20, '', '');
        $blc->info0();
            $blc->numeric('x', ['x', ''], 3, '', '');
            $blc->def('y', (($f3->_x - $f3->_x1)*($f3->_y2 - $f3->_y1)/($f3->_x2 - $f3->_x1)) + $f3->_y1, 'y = %%');
        $blc->info1();

        $blc->jsxDriver();
        $js = '
            var b = JXG.JSXGraph.initBoard("interpolation", {boundingbox: ['.min($f3->_x/2, $f3->_x1/2, $f3->_x2/2).', '.max($f3->_y*2, $f3->_y1*2, $f3->_y2*2).', '.max($f3->_x*2, $f3->_x1*2, $f3->_x2*2).', '.min($f3->_y/2, $f3->_y1/2, $f3->_y2/2).'], axis:true, showCopyright:false, keepaspectratio: false, showNavigation: true});
            var p1 = b.create("point", ['.$f3->_x1.', '.$f3->_y1.'], {name: "x1, y1", size:2, fillColor: "blue", strokeColor: "blue"});
            var p2 = b.create("point", ['.$f3->_x2.', '.$f3->_y2.'], {name: "x2, y2", size:2, fillColor: "blue", strokeColor: "blue"});
            var li = b.create("line",["x1, y1","x2, y2"], {strokeColor:"#00ff00", strokeWidth:2});
            var p2 = b.create("point", ['.$f3->_x.', '.$f3->_y.'], {name: "x, y", size:2, attractors: [li], attractorDistance:0.2, snatchDistance: 2});
            ';
        $blc->jsx('interpolation', $js);

        $blc->h1('Cső tömeg számítás');
        $blc->numeric('D', ['D', 'Cső külső átmérő'], 600, 'mm', '');
        $blc->numeric('t', ['t', 'Cső falvastagság'], 12, 'mm', '');
        $blc->def('d', $f3->_D - 2*$f3->_t, 'd = D- 2*t = %% [mm]', 'Belső átmérő');
        $blc->def('As', $ec->A($f3->_D) - $ec->A($f3->_d), 'A_(steel) = (D^2 pi)/4 - (d^2 pi)/4 = %% [mm^2]');
        $blc->def('Al', $ec->A($f3->_d), 'A_(liqu i d) = %% [mm^2]');
        $blc->def('gs', 78.5, 'gamma_(steel) = %% [(kN)/m^3]', '');
        $blc->numeric('gl', ['gamma_(liqu i d)', 'Folyadék fajsúly'], 10, 'kN/m3', '');
        $blc->def('qk', H3::n3(($f3->_As/1000000)*$f3->_gs + ($f3->_Al/1000000)*$f3->_gl), 'q_k = A_(steel)*gamma_(sttel) + A_(liqu i d)*gamma_(liqu i d) = %% [(kN)/(fm)]');

        $blc->img('https://structure.hu/ecc/piperack0.jpg', 'Erőterv/APOLLO');

        $blc->h1('Mean diameter');
        $blc->note('The value of the mean diameter $d_m$ is estimated as follows. The distance across flats $s$ of the nut is given in the standard *ISO 898-2*. By approximately ignoring the corner rounding for a perfect hexagon the relation of the distance across points $s\'$ and the distance across flats $s$ is $s\' = s / cos(30°) = 1.1547*s$. Therefore the mean diameter $d_m$ is approximately: $d_m = (s + 1.1547*s)/2=1.07735*s$');
        $blc->numeric('s', ['s', 'Szemben lévő felületek távolsága csavarfejen'], 10, 'mm', 'ISO 898-2');
        $blc->def('dm', H3::n1(1.07735*$f3->_s), 'd_m = %% [mm]', '');

        $blc->h1('NAGÉV tömeg');
        $blc->lst('nagevh', [20, 25, 30, 35, 40, 45, 50, 60, 70, 80, 90, 100], ['h [mm]', 'Járórács magasság'], 30);
        $blc->lst('nagevv', [2, 3, 4 , 5], ['v [mm]', 'Borda vastagság'], 2);
        $blc->lst('nagevl', [11, 22, 33, 44, 55, 66], ['l [mm]', 'Borda osztásköz'], 33);
        $blc->def('nagevn', ceil(1000/$f3->_nagevl) + 1, 'n = 1000/l + 1 = %% [db]', 'Bordák száma 1 m-en.');
        $blc->note('Mellékbordát fél magassággal veszi figyelembe.');
        $blc->def('nagevg', ceil($f3->_nagevn*$f3->_nagevh*1.5*$f3->_nagevv*1000/1000000000*7850), 'g = n*1.5*h*7850[(kg)/m^3] = %% [(kg)/m^2]', '');

        /* $blc->h1('Teherelemzés');
        $blc->h2('Terhek');
        $blc->input('m', ['m', 'Teherátadási módosító tényező'], 1.15, '', 'Trapézlemez többtámaszú hatása, közbenső támasznál; 1-től 1.25-ig', 'numeric|min_numeric,1|max_numeric,2');

        $blc->h3('Rétegrend');
        $blc->numeric('glk', ['g_(l,k)', 'Rétegrend karakterisztikus felületi terhe'], 0.6, 'kN/m2');
        $blc->math('gamma_G = ' . $f3->__GG);

        $blc->h3('Hasznos teher');
        $blc->numeric('qq', ['q_(q,k)', 'Hasznos felületi teher karakterisztikus értéke'], 5, 'kN/m2', '');

        $blc->h3('Installációs és egyéb terhek');
        $blc->numeric('qik', ['q_(i,k)', 'Installációs felületi teher karakterisztikus értéke'], 0.5, 'kN/m2');
        $blc->math('gamma_Q = ' . $f3->__GQ);

        $blc->h3('Szélnyomásból adódó teher');
        $blc->boo('qwkcalc', '$q_p(z)$ számítása itt', false);
        if (!$f3->_qwkcalc) {
            $blc->numeric('qwk', ['q_(w,k)', 'Szél felületi teher karakterisztikus értéke'], 0.4, 'kN/m2', 'Torlónyomásból, belső szélnyomással, szélnyomáshoz ( **I** ) zóna');
        } else {
            $moduleQpz = new \Calculation\Wind($f3, $blc, $ec); // TODO DI
            $blc->info0();
                $moduleQpz->moduleQpz();
            $blc->info1();
            $blc->def('qwk', \H3::n2((abs($f3->_cm) + 0.2)*$f3->_qpz), 'q_(w,k,I+) = (abs(c_m) + 0.2)*q_p(z) = %% [(kN)/m^2]', 'Torlónyomásból, belső szélnyomással, szélnyomáshoz ( **I** ) zóna');
        }

        $blc->h3('Hó/hózug terhe');
        $blc->numeric('qsk', ['q_(s,k)', 'Hó/hózug felületi teher karakterisztikus értéke'], 1, 'kN/m2', '');
        */
    }
}