<?php declare(strict_types = 1);
/**
 * Functional tests for Statika framework
 * (c) Bence VÁNKOS | https://structure.hu | https://github.com/r3sist/ecc-calculations
 */

namespace Statika\Calculations;

use Exception;
use resist\SVG\SVG;
use Statika\Ec;
use Statika\EurocodeInterface;

Class Test
{
    /**
     * @param Ec $ec
     * @throws Exception
     */
    public function calc(EurocodeInterface $ec): void
    {
        $ec->txt('This calculation class contains framework block functional tests: UI rendering and block behaviour with default and extreme data. This is also a ***text*** block test with *markdown*, without secondary or description value.');

        $ec->h1('***def*** tests');
        $ec->def('s1', 'szöveg', 's1 = %%', 'var `s1`, result `(string)szöveg`, expr `s1 = %%`, no validation');
        $ec->txt('s1: '.$ec->s1);
        $ec->def('i1', 1+2, 'i1 = %%', 'var `i1`, result `(int)1+2`, expr `i1 = %%`, no validation');
        $ec->txt('i1: '.$ec->i1);
        $ec->def('i2', 1+2, 'i2 = %%', 'var `i2`, result `(int)1+2`, expr `i2 = %%`, min_numeric,4', 'min_numeric,4');
        $ec->txt('i2: '.$ec->i2);
        $ec->def('i3', 'szöveg', 'i3 = %%', 'var `i3`, result  `(string)szöveg`, expr `i3 = %%`, numeric', 'numeric');
        $ec->txt('i2: '.$ec->i3);

        $ec->h1('***input*** and ***numeric*** tests');
        $ec->input('n0', ['n_0', 'Tile *markdown* and <script>alert("HTML")</script> and $Math$ test'], 'stringInput', 'm2', '*markdown* and $Math$. Has string default value.');
        $ec->numeric('n1', ['n_1', 'Numeric test $le 10$'], 9, 'm2', '', 'max_numeric,10');
        $ec->def('var', $ec->n1, 'var = %%', 'dynamic definition test');
        $ec->input('i1', ['i_1', 'Text input with $math_1$ and $math_2$'], 'test', 'unit', 'md *help*', '');
        $ec->txt('Test set value: '.$ec->i1);
        $ec->input('i2', ['i_2', 'Text input with **md**'], 12, 'kN/m2', '', '');
        $ec->txt('Test set value: '.$ec->i2);
        $ec->input('i3', ['i_3', 'Numeric input with <strong>html</strong>'], 4, 'unit', 'with `min_numeric,4` validation', 'min_numeric,4');
        $ec->txt('Test set value: '.$ec->i3);
        $ec->numeric('i4', ['i_4', 'Numeric input'], 4, 'unit', 'without additional validation', '');
        $ec->txt('Test set value: '.$ec->i4);
        $ec->numeric('i5', ['i_5', 'Numeric input'], 4, 'unit', 'with additional `min_numeric,4` validation', 'min_numeric,4');
        $ec->txt('Test set value: '.$ec->i5);
        $ec->txt($ec->n0);
        $ec->numeric('i5', ['i_5', 'No unit'], 4, '', 'with additional `min_numeric,4` validation', 'min_numeric,4');

        $ec->h1('***boo*** tests');
        $ec->boo('b1', ['b_1', 'boo1'], true, 'default `true`');
        $ec->txt('Test b1 value: '.$ec->b1);
        $ec->boo('b2', ['b_2', 'boo2'], false, 'default `false`');
        $ec->txt('Test b2 value: '.$ec->b2);
        $ec->boo('b3', ['', 'boo3'], true, 'default `true` without math title');
        $ec->boo('b4', ['b_4', 'boo4 $b_4$ *md*'], true, 'Math and md title');

        $ec->h1('***lst*** tests');
        $ec->lst('L1', ['x - 1' => 1, 'y - 2' => 2, 'z - 3' => 3], ['L1', 'integer értékű lista'], 2, 'md **help**');
        $ec->txt('Test L1 value: '.$ec->L1);
        $ec->lst('L2', ['x - 1' => '1', 'y - 2' => '2', 'z - 3' => '3'], ['L2', 'string értékű lista'], '2', '');
        $ec->txt('Test L2 value: '.$ec->L2);

        $ec->h1('***numericArrayInput*** tests');
        $ec->numericArrayInput('order', ['', 'Numeric array input'], '1 10 11.1');
        $ec->txt('Array input in a `pre` block:');
        $ec->pre(print_r($ec->order, true));

        $ec->h1('***wrapper*** tests');
        $ec->wrapNumerics('wa', 'wb', 'Simple numeric wrap', 100, 200, 'kg', 'Help *md*', '×');
        $ec->wrapRebarCount('wc', 'wr', 'Rebar wrap', 2, 20, '2D20 *md* help', 'A');
        $ec->wrapRebarDistance('wd', 'wr2', 'Rebar distance', 200, 10, 'Description value');

        $ec->h1('***label*** tests');
        $ec->label(0.1, '***md additional text*** and numeric value 0.1');
        $ec->label('0.1', 'string value 0.1');
        $ec->label(1.1, '<code>html code</code> and numeric value 1.1');
        $ec->label('yes', 'yes text');
        $ec->label('no', 'no text', 'Has description *md*');

        $ec->h1('***pre*** tests');
        $ec->pre('<script>alert("test");</script>');
        $ec->pre(<<< EOS
Preformatted text test as heredoc
<strong>escaped strings</strong> & <?php echo 'this'; ?> &copy;
EOS
        );

        $ec->h1('***txt*** tests');
        $ec->txt('Text block test.');
        $ec->txt('Text block test: ***Markdown strict***');
        $ec->txt('Text block test: ***Markdown strict***'.PHP_EOL.PHP_EOL.'+ multi line');
        $ec->txt('Text block test: <script>alert("Filter this!");</script>');
        $ec->txt('Text block test.', 'With description');
        $ec->txt('', 'Only description');
        $ec->txt('Text block test.', 'With ***markdown [description](https://structure.hu)*** description');
        $ec->txt('Text block test.', 'With <script>alert("Filter this!");</script>');
        $ec->txt('Text block $Math$ test.', 'With $Math$');
        $ec->txt('***txt*** test with markdown: *em* **strong** [link]() `code`');
        $ec->txt('***txt*** test', 'With ***markdown*** help and $math$');
        $ec->txt('***txt*** test  $math_2 = x^2$', 'With ***markdown*** help and $math_2 = x^2$');

        $ec->h1('***h1*** tests', 'md *subtitle* $math$');
        $ec->h1('header 1 with `md` test $math$', 'md *subtitle* $math$');
        $ec->h2('***h2*** test', 'md *subtitle*');
        $ec->h3('***h3*** test', 'md *subtitle*');
        $ec->h4('***h4*** test', 'md *subtitle*');

        $ec->h1('***math*** tests');
        $m0 = 'f_{ck} = 20 [N/(mm^2)]';
        $m1 = 'omega = (A_s*f_(yd))/(A_c*f_(cd)) = 0';
        $m2 = 'alpha_m = sqrt(0.5*(1+1/m))';
        $m3 = 'l_{0,fi} = min{(beta*l_(fi)),(3):} = 10 [m]';
        $m4 = '"text line and break" %%% x=2 %%% x = 3';
        $ec->h1('***math*** tests');
        $ec->math($m0, '***md*** $x_x$');
        $ec->math($m1);
        $ec->math($m2);
        $ec->math($m3);
        $ec->math($m4);

        $ec->h1('***html*** tests');
        $ec->html('<strong>strong</strong><p>new paragraph</p>');
        $ec->html('<font color="red">HTML FONT</font>');

        $ec->h1('***md*** tests');
        $ec->md('***md*** `x(y)` $x(y)$ [a](http://)');
        $ec->md(
            <<< EOS
            # multi line md
            
            + egy
            + kettő
            EOS);

        $ec->h1('***success0/1 info0/1 danger0/1*** tests');
        $ec->success0('*md* title');
            $ec->txt('blabla');
        $ec->success1();
        $ec->success0();
            $ec->txt('without defined title');
        $ec->success1();
        $ec->info0('*md* title');
            $ec->txt('blabla');
        $ec->info1();
        $ec->danger0('*md* title');
            $ec->txt('blabla');
        $ec->danger1();

        $ec->h1('***success info danger*** tests');
        $ec->success('*md* content', '*md* title');
        $ec->info('*md* content', 'title');
        $ec->danger('*md* content', 'title');
        $ec->danger('*md* content without title');

        $ec->h1('***region*** tests');
        $ec->region0('r1', 'Custom title with **md** and $math$');
        $ec->txt('Region content');
        $ec->region1();
        $ec->region0('r2');
        $ec->txt('Region test with default title');
        $ec->region1();

        // SVG init
        $ec->h1('***svg*** tests');
        $svg = new SVG(200, 200);
        $svg->setFill('#eeeeee');
        $svg->addRectangle(10, 10, 180, 180);
        $ec->svg($svg, false, 'caption');

        $svg = new SVG(200, 200);
        $svg->setFill('#eeeeee');
        $svg->addRectangle(10, 10, 180, 180);
        $ec->svg($svg, true, 'force jpg');

        $ec->h1('***img*** tests');
        $ec->img('iVBORw0KGgoAAAANSUhEUgAAAHAAAAAQAQMAAAD02mlSAAAABlBMVEX///8AAABVwtN+AAAAbUlEQVQImZXPIQ6AMAyF4QaNaFAIQgjHWCpenphAICZQVTsH4ewUMoOkadL/kxX5M5hE4+jbseuXm5xRZ6Mfpk5TuolTaqFWUlmjKSxQEEpEP7TG6+XeNeJhXTggc8jAyCw+W49kfYKNTL8+uAGbtBj5c5IqYwAAAABJRU5ErkJggg==', 'base64 encoded PNG');
        $ec->img('https://structure.hu/img/firms/CEH.svg', '***md caption*** [SVG source](https://structure.hu/img/firms/CEH.svg)');
        $ec->img('https://sorfi.org/assets/img/brands/webextensions.png', '[PNG source](https://sorfi.org/assets/img/brands/webextensions.png)');

        $ec->h1('***note*** tests');
        $ec->note('lorem ipsum ***md*** <code>html code</code> $x_x$ `x_x`');

        $ec->h1('***table*** tests');
        $scheme = ['Plain string title', 'Math title $gamma_k [(kN)/m^3]$ and integer content', '**md** tite', 10, '<em>html is not allowed</em>'];
        $rows = [
            ['***md*** content', 18, '', 'integer title', ''],
            ['<em>html content allowed</em>', 24, '', 'integer title', ''],
            ['$x_2$ math content', 24, '', 'integer title', ''],
            ['<!--success-->success td', '<!--danger-->danger td', '<!--info-->info td', 'integer title', ''],
        ];
        $ec->tbl($scheme, $rows, 'table_name', 'Table **md** caption with math $x_2$');

////        $fields = [
////            ['name' => 50, 'title' => 'Ø8', 'type' => 'input', 'sum' => true],
////            ['name' => 79, 'title' => 'Ø10', 'type' => 'input', 'sum' => true],
////            ['name' => 112, 'title' => 'Ø12', 'type' => 'input', 'sum' => true],
////            ['name' => 154, 'title' => 'Ø16', 'type' => 'input', 'sum' => true],
////            ['name' => 314, 'title' => 'Ø20', 'type' => 'input', 'sum' => true],
////            ['name' => 491, 'title' => 'Ø25', 'type' => 'input', 'sum' => true],
////            ['name' => 616, 'title' => 'Ø28', 'type' => 'input', 'sum' => true],
////            ['name' => 804, 'title' => 'Ø32', 'type' => 'input', 'sum' => true],
////        ];
////        $blc->bulk('x', $fields);
//
//
////        $stack = [
////            100 => 100,
////            150 => 150,
////            200 => 200,
////            300 => 300,
////        ];
////        $blc->pre(var_export($stack, true));
////        $blc->txt('closest 10: **'.$ec->getClosest(10, $stack, 'closest').'**');
////        $blc->txt('closest 99: **'.$ec->getClosest(99, $stack, 'closest').'**');
////        $blc->txt('closest 100: **'.$ec->getClosest(100, $stack, 'closest').'**');
////        $blc->txt('closest 101: **'.$ec->getClosest(101, $stack, 'closest').'**');
////        $blc->txt('closest 199: **'.$ec->getClosest(199, $stack, 'closest').'**');
////        $blc->txt('closest 200: **'.$ec->getClosest(200, $stack, 'closest').'**');
////        $blc->txt('closest 201: **'.$ec->getClosest(201, $stack, 'closest').'**');
////        $blc->txt('closest 240: **'.$ec->getClosest(240, $stack, 'closest').'**');
////        $blc->txt('closest 270: **'.$ec->getClosest(270, $stack, 'closest').'**');
////        $blc->txt('closest 400: **'.$ec->getClosest(400, $stack, 'closest').'**');
////        $blc->hr();
////        $blc->txt('floor 10: **'.$ec->getClosest(10, $stack, 'floor').'**');
////        $blc->txt('floor 99: **'.$ec->getClosest(99, $stack, 'floor').'**');
////        $blc->txt('floor 100: **'.$ec->getClosest(100, $stack, 'floor').'**');
////        $blc->txt('floor 101: **'.$ec->getClosest(101, $stack, 'floor').'**');
////        $blc->txt('floor 199: **'.$ec->getClosest(199, $stack, 'floor').'**');
////        $blc->txt('floor 200: **'.$ec->getClosest(200, $stack, 'floor').'**');
////        $blc->txt('floor 201: **'.$ec->getClosest(201, $stack, 'floor').'**');
////        $blc->txt('floor 240: **'.$ec->getClosest(240, $stack, 'floor').'**');
////        $blc->txt('floor 270: **'.$ec->getClosest(270, $stack, 'floor').'**');
////        $blc->txt('floor 400: **'.$ec->getClosest(400, $stack, 'floor').'**');
////        $blc->hr();
////        $blc->txt('ceil 10: **'.$ec->getClosest(10, $stack, 'ceil').'**');
////        $blc->txt('ceil 99: **'.$ec->getClosest(99, $stack, 'ceil').'**');
////        $blc->txt('ceil 100: **'.$ec->getClosest(100, $stack, 'ceil').'**');
////        $blc->txt('ceil 101: **'.$ec->getClosest(101, $stack, 'ceil').'**');
////        $blc->txt('ceil 199: **'.$ec->getClosest(199, $stack, 'ceil').'**');
////        $blc->txt('ceil 200: **'.$ec->getClosest(200, $stack, 'ceil').'**');
////        $blc->txt('ceil 201: **'.$ec->getClosest(201, $stack, 'ceil').'**');
////        $blc->txt('ceil 240: **'.$ec->getClosest(240, $stack, 'ceil').'**');
////        $blc->txt('ceil 270: **'.$ec->getClosest(270, $stack, 'ceil').'**');
////        $blc->txt('ceil 400: **'.$ec->getClosest(400, $stack, 'ceil').'**');
////        $blc->hr();
////        $blc->txt('linterp 10: **'.$ec->getClosest(10, $stack, 'linterp').'**');
////        $blc->txt('linterp 99: **'.$ec->getClosest(99, $stack, 'linterp').'**');
////        $blc->txt('linterp 100: **'.$ec->getClosest(100, $stack, 'linterp').'**');
////        $blc->txt('linterp 101: **'.$ec->getClosest(101, $stack, 'linterp').'**');
////        $blc->txt('linterp 199: **'.$ec->getClosest(199, $stack, 'linterp').'**');
////        $blc->txt('linterp 200: **'.$ec->getClosest(200, $stack, 'linterp').'**');
////        $blc->txt('linterp 201: **'.$ec->getClosest(201, $stack, 'linterp').'**');
////        $blc->txt('linterp 240: **'.$ec->getClosest(240, $stack, 'linterp').'**');
////        $blc->txt('linterp 270: **'.$ec->getClosest(270, $stack, 'linterp').'**');
////        $blc->txt('linterp 400: **'.$ec->getClosest(400, $stack, 'linterp').'**');
//



    }
}
