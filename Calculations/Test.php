<?php declare(strict_types = 1);

namespace Calculation;

use resist\SVG\SVG;
use \Base;
use \Ecc\Blc;
use \Ec\Ec;
use \H3;

Class Test
{
    public function calc(Base $f3, Blc $blc, Ec $ec): void
    {
        $log = '';
        $blc->toast('Test', 'success', 'Title');
        $blc->toc();

        $log.=$blc->txt('Init test as txt block with plain text.');


        $blc->txt('***hr*** test:');
        $blc->hr();

        $log.=$blc->txt('***txt*** test with markdown: *em* **strong** [link]() `code`');
        $blc->txt('***txt*** test', 'With ***markdown*** help and $math$');
        $blc->txt('***txt*** test  $math_2 = x^2$', 'With ***markdown*** help and $math_2 = x^2$');

        $log.=$blc->h1('***h1*** test $math$', 'md *subtitle* $math$');
        $log.=$blc->h2('***h2*** test', 'md *subtitle*');
        $log.=$blc->h3('***h3*** test', 'md *subtitle*');
        $log.=$blc->h4('***h4*** test', 'md *subtitle*');

        $blc->hr();
        $m0 = 'f_{ck} = 20 [N/(mm^2)]';
        $m1 = 'omega = (A_s*f_(yd))/(A_c*f_(cd)) = 0';
        $m2 = 'alpha_m = sqrt(0.5*(1+1/m))';
        $m3 = 'l_{0,fi} = min{(beta*l_(fi)),(3):} = 10 [m]';
        $m4 = '"text line and break" %%% x=2 %%% x = 3';
        $blc->h1('***math*** tests');
        $blc->math($m0, '***md*** $x_x$');
        $blc->math($m1);
        $blc->math($m2);
        $blc->math($m3);
        $blc->math($m4);

        $blc->h1('***html*** tests');
        $blc->html('<strong>strong</strong><p>new paragraph</p>');

        $blc->h1('***def*** tests');
        $blc->def('x', 'x', 'x = %%', 'var `x`, result string `x`, expr `x = %%`, no validation');
        $blc->def('x', 1+2, 'x = %%', 'var `x`, result `1+2`, expr `x = %%`, no validation');
        $blc->def('x', 1+2, 'x = %%', 'var `x`, result `1+2`, expr `x = %%`, min_numeric,4', 'min_numeric,4');
        $log.=$blc->def('y', 'x', 'y = %%', 'var `y`, result  string `x`, expr `y = %%`, numeric', 'numeric');
        $log.=$blc->def('z', $f3->_x + 1, 'z = x + 1 = %%', 'var `z`, result `_x + 1`, min_numeric,4; Shoud be 4', 'min_numeric,4');

        $blc->h1('***input*** tests');
        $log.=$blc->input('i1', ['i_1', 'Text input with $math_1$ and $math_2$'], 'test', 'unit', 'md *help*', '');
        $blc->txt('Test set value: '.$f3->_i1);
        $log.=$blc->input('i2', ['i_2', 'Text input with **md**'], 12, 'kN/m2', '', '');
        $blc->txt('Test set value: '.$f3->_i2);
        $blc->input('i3', ['i_3', 'Numeric input with <strong>html</strong>'], 4, 'unit', 'with `min_numeric,4` validation', 'min_numeric,4');
        $blc->txt('Test set value: '.$f3->_i3);
        $blc->h1('***numeric*** tests');
        $log.=$blc->numeric('i4', ['i_4', 'Numeric input'], 4, 'unit', 'without additional validation', '');
        $blc->txt('Test set value: '.$f3->_i4);
        $blc->numeric('i5', ['i_5', 'Numeric input'], 4, 'unit', 'with additional `min_numeric,4` validation', 'min_numeric,4');
        $blc->txt('Test set value: '.$f3->_i4);

        $blc->h1('***boo*** tests');
        $log.=$blc->boo('b1', ['b_1', 'boo1'], true, 'default `true`');
        $blc->txt('Test b1 value: '.$f3->_b1);

        $log.=$blc->boo('b2', ['b_2', 'boo2'], false, 'default `false`');
        $blc->txt('Test b2 value: '.$f3->_b2);

        $blc->h1('***lst*** tests');
        $log.=$blc->lst('L1', ['x - 1' => 1, 'y - 2' => 2, 'z - 3' => 3], ['L1', 'integer értékű lista'], 2, 'md **help**');
        $blc->txt('Test L1 value: '.$f3->_L1);
        $log.=$blc->lst('L2', ['x - 1' => '1', 'y - 2' => '2', 'z - 3' => '3'], ['L2', 'string értékű lista'], '2', '');
        $blc->txt('Test L2 value: '.$f3->_L2);

        $blc->h1('***label*** tests');
        $log.=$blc->label(0.1, '***md additional text*** and numeric value 0.1');
        $blc->label('0.1', 'string value 0.1');
        $log.=$blc->label(1.1, '<code>html code</code> and numeric value 1.1');
        $blc->label('yes', 'yes text');
        $log.=$blc->label('no', 'no text');

        $blc->h1('***region0/1*** tests');
        $blc->region0('r1', '*md* title');
            $blc->txt('blabla');
        $blc->region1();
        $blc->region0('r2');
            $blc->txt('without defined title');
        $blc->region1();

        $blc->h1('***success0/1 info0/1 danger0/1*** tests');
        $blc->success0('*md* title');
            $blc->txt('blabla');
        $blc->success1();
        $blc->success0();
            $blc->txt('without defined title');
        $blc->success1();
        $blc->info0('*md* title');
            $blc->txt('blabla');
        $blc->info1();
        $blc->danger0('*md* title');
            $blc->txt('blabla');
        $blc->danger1();

        $blc->h1('***success info danger*** tests');
        $blc->success('*md* content', 'title');
        $blc->info('*md* content', 'title');
        $blc->danger('*md* content', 'title');
        $blc->danger('*md* content without title');

        // SVG init
        $blc->h1('***svg*** tests');
        $svg = new SVG(200, 200);
        $svg->setFill('#eeeeee');
        $svg->addRectangle(10, 10, 180, 180);
        $blc->svg($svg, false, 'caption');

        $svg = new SVG(200, 200);
        $svg->setFill('#eeeeee');
        $svg->addRectangle(10, 10, 180, 180);
        $blc->svg($svg, true, 'force jpg');

        $blc->h1('***note*** tests');
        $blc->note('pre ***md*** <code>html code</code> $x_x$ `x_x`');

        $blc->h1('***write*** tests');
        $write = [['size' => 14, 'x' => 40, 'y' => 140, 'text' => '210 kN/m²']];
        $blc->write('vendor/resist/ecc-calculations/canvas/linQ0.jpg', $write, 'Gerenda kiosztás geometriája');

        $blc->h1('***md*** tests');
        $log.=$blc->md('***md*** `x(y)` $x(y)$ [a](http://)');

//        $fields = [
//            ['name' => 50, 'title' => 'Ø8', 'type' => 'input', 'sum' => true],
//            ['name' => 79, 'title' => 'Ø10', 'type' => 'input', 'sum' => true],
//            ['name' => 112, 'title' => 'Ø12', 'type' => 'input', 'sum' => true],
//            ['name' => 154, 'title' => 'Ø16', 'type' => 'input', 'sum' => true],
//            ['name' => 314, 'title' => 'Ø20', 'type' => 'input', 'sum' => true],
//            ['name' => 491, 'title' => 'Ø25', 'type' => 'input', 'sum' => true],
//            ['name' => 616, 'title' => 'Ø28', 'type' => 'input', 'sum' => true],
//            ['name' => 804, 'title' => 'Ø32', 'type' => 'input', 'sum' => true],
//        ];
//        $blc->bulk('x', $fields);


//        $stack = [
//            100 => 100,
//            150 => 150,
//            200 => 200,
//            300 => 300,
//        ];
//        $blc->pre(var_export($stack, true));
//        $blc->txt('closest 10: **'.$ec->getClosest(10, $stack, 'closest').'**');
//        $blc->txt('closest 99: **'.$ec->getClosest(99, $stack, 'closest').'**');
//        $blc->txt('closest 100: **'.$ec->getClosest(100, $stack, 'closest').'**');
//        $blc->txt('closest 101: **'.$ec->getClosest(101, $stack, 'closest').'**');
//        $blc->txt('closest 199: **'.$ec->getClosest(199, $stack, 'closest').'**');
//        $blc->txt('closest 200: **'.$ec->getClosest(200, $stack, 'closest').'**');
//        $blc->txt('closest 201: **'.$ec->getClosest(201, $stack, 'closest').'**');
//        $blc->txt('closest 240: **'.$ec->getClosest(240, $stack, 'closest').'**');
//        $blc->txt('closest 270: **'.$ec->getClosest(270, $stack, 'closest').'**');
//        $blc->txt('closest 400: **'.$ec->getClosest(400, $stack, 'closest').'**');
//        $blc->hr();
//        $blc->txt('floor 10: **'.$ec->getClosest(10, $stack, 'floor').'**');
//        $blc->txt('floor 99: **'.$ec->getClosest(99, $stack, 'floor').'**');
//        $blc->txt('floor 100: **'.$ec->getClosest(100, $stack, 'floor').'**');
//        $blc->txt('floor 101: **'.$ec->getClosest(101, $stack, 'floor').'**');
//        $blc->txt('floor 199: **'.$ec->getClosest(199, $stack, 'floor').'**');
//        $blc->txt('floor 200: **'.$ec->getClosest(200, $stack, 'floor').'**');
//        $blc->txt('floor 201: **'.$ec->getClosest(201, $stack, 'floor').'**');
//        $blc->txt('floor 240: **'.$ec->getClosest(240, $stack, 'floor').'**');
//        $blc->txt('floor 270: **'.$ec->getClosest(270, $stack, 'floor').'**');
//        $blc->txt('floor 400: **'.$ec->getClosest(400, $stack, 'floor').'**');
//        $blc->hr();
//        $blc->txt('ceil 10: **'.$ec->getClosest(10, $stack, 'ceil').'**');
//        $blc->txt('ceil 99: **'.$ec->getClosest(99, $stack, 'ceil').'**');
//        $blc->txt('ceil 100: **'.$ec->getClosest(100, $stack, 'ceil').'**');
//        $blc->txt('ceil 101: **'.$ec->getClosest(101, $stack, 'ceil').'**');
//        $blc->txt('ceil 199: **'.$ec->getClosest(199, $stack, 'ceil').'**');
//        $blc->txt('ceil 200: **'.$ec->getClosest(200, $stack, 'ceil').'**');
//        $blc->txt('ceil 201: **'.$ec->getClosest(201, $stack, 'ceil').'**');
//        $blc->txt('ceil 240: **'.$ec->getClosest(240, $stack, 'ceil').'**');
//        $blc->txt('ceil 270: **'.$ec->getClosest(270, $stack, 'ceil').'**');
//        $blc->txt('ceil 400: **'.$ec->getClosest(400, $stack, 'ceil').'**');
//        $blc->hr();
//        $blc->txt('linterp 10: **'.$ec->getClosest(10, $stack, 'linterp').'**');
//        $blc->txt('linterp 99: **'.$ec->getClosest(99, $stack, 'linterp').'**');
//        $blc->txt('linterp 100: **'.$ec->getClosest(100, $stack, 'linterp').'**');
//        $blc->txt('linterp 101: **'.$ec->getClosest(101, $stack, 'linterp').'**');
//        $blc->txt('linterp 199: **'.$ec->getClosest(199, $stack, 'linterp').'**');
//        $blc->txt('linterp 200: **'.$ec->getClosest(200, $stack, 'linterp').'**');
//        $blc->txt('linterp 201: **'.$ec->getClosest(201, $stack, 'linterp').'**');
//        $blc->txt('linterp 240: **'.$ec->getClosest(240, $stack, 'linterp').'**');
//        $blc->txt('linterp 270: **'.$ec->getClosest(270, $stack, 'linterp').'**');
//        $blc->txt('linterp 400: **'.$ec->getClosest(400, $stack, 'linterp').'**');

//        $blc->h4('input tests', '');
//        $blc->numeric('xw', ['x_w', 'array title *md* <code>html code</code>'], '11.1', '$m^2$', '*md* $x_x$ ');
//        $blc->numeric('xw', ['x_w', 'array title *md* <code>html code</code>'], 11, '$m^2$', '*md* $x_x$ ');
//        $blc->input('xx', ['x_x', 'array title *md* <code>html code</code>'], 'general input', 'kN/m2', '*md* $x_x$ ');
//        $blc->boo('yx', ['y_x', 'array title *md* <code>html code</code>'], true, '$m^2$');
//        $blc->lst('zy', ['S' => 'S', 'L' => 'L', 'G' => 'G', 'C' => 'C'], ['z_y', 'array title *md*'], 'S', 'help');
//        $blc->hr();
//
//        $blc->hr();
//        $blc->h4('txt');
//        $blc->txt('*md* $'.$m0.'$ Lorem', '***md*** help');
//        $blc->txt('$'.$m0.'$');
//        $blc->txt('$'.$m1.'$');
//        $blc->txt('$'.$m2.'$');
//        $blc->txt('$'.$m3.'$');
//        $blc->txt('', 'small text *md* $x_x$');
//        $blc->hr();



//
//        $blc->hr();
//        $blc->h4('img tests', '');
//        $blc->img('https://structure.hu/img/ceh.svg', '***md caption*** [SVG source](https://structure.hu/img/ceh.svg)');
//        $blc->img('https://sorfi.org/assets/img/brands/webextensions.png', '[PNG source](https://sorfi.org/assets/img/brands/webextensions.png)');
//        $blc->write('vendor/resist/ecc-calculations/canvas/snow3.jpg', [['size' => 14, 'x' => 90, 'y' => 35, 'text' => 'test']], '***write/md test***');
//        $blc->hr();
//
//        $blc->hr();
//        $blc->h4('label tests', '');
//
//        $blc->hr();
//
//        $blc->hr();
//        $blc->h4('pre tests', '');
//        $blc->pre('pre ***md*** <code>html code</code> $x_x$ `x_x`');
//
//        $blc->hr();
//        $blc->h4('note tests', '');
//        $blc->note('pre ***md*** <code>html code</code> $x_x$ `x_x`');

        $blc->pre($log);
    }
}
