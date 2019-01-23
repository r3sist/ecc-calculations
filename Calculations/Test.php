<?php

namespace Calculation;

Class Test extends \Ecc
{

    /**
     * @var $f3 \Base
     * @var $blc \Blc
     * @var $ec \Ec\Ec
     * @throws \Exception
     */
    public function calc(object $f3, object $blc, object $ec): void
    {
        // Deafult test strings
        $m0 = 'f_{ck} = 20 [N/(mm^2)]';
        $m1 = 'omega = (A_s*f_(yd))/(A_c*f_(cd)) = 0';
        $m2 = 'alpha_m = sqrt(0.5*(1+1/m))';
        $m3 = 'l_{0,fi} = min{(beta*l_(fi)),(3):} = 10 [m]';

        $blc->h4('input tests', '');
        $blc->numeric('xw', ['x_w', 'array title *md* <code>html code</code>'], '11.1', '$m^2$', '*md* $x_x$ ');
        $blc->input('xx', ['x_x', 'array title *md* <code>html code</code>'], 'general input', 'kN/m2', '*md* $x_x$ ');
        $blc->input('xy', '$x_y:$ string title *md* <code>html code</code>', 'general input', 'unit', '*md* $x_y$ ');
        $blc->boo('yx', ['y_x', 'array title *md* <code>html code</code>'], 1, '$m^2$');
        $blc->boo('yy', '$y_y:$ string title *md* <code>html code</code>', 0, 'help');
        $blc->lst('zx', ['S' => 'S', 'L' => 'L', 'G' => 'G', 'C' => 'C'], '$z_x:$ string title *md*', 'S', 'help', '');
        $blc->lst('zy', ['S' => 'S', 'L' => 'L', 'G' => 'G', 'C' => 'C'], ['z_y', 'array title *md*'], 'S', 'help', '');
        $blc->hr();

        $blc->h4('txt');
        $blc->txt('*md* $'.$m0.'$ Lorem', '***md*** help');
        $blc->txt('$'.$m0.'$');
        $blc->txt('$'.$m1.'$');
        $blc->txt('$'.$m2.'$');
        $blc->txt('$'.$m3.'$');
        $blc->txt('', 'small text *md* $x_x$');
        $blc->hr();

        $blc->h4('math tests');
        $blc->math($m0, '***md*** $x_x$');
        $blc->math($m1);
        $blc->math($m2);
        $blc->math($m3);
        $blc->hr();

        $blc->h4('md tests');
        $blc->md('***md*** `x(y)` $x(y)$ [a](http://)');
        $blc->hr();

        $blc->h1('heading tests ***md***', 'sub ***title***');
        $blc->h2('heading tests ***md***', 'sub ***title***');
        $blc->h3('heading tests ***md***', 'sub ***title***');
        $blc->h4('heading tests ***md***', 'sub ***title***');
        $blc->hr();

        $blc->h4('img tests', '');
        $blc->img('https://structure.hu/img/ceh.svg', '***md caption*** [SVG source](https://structure.hu/img/ceh.svg)');
        $blc->img('https://sorfi.org/assets/img/brands/webextensions.png', '[PNG source](https://sorfi.org/assets/img/brands/webextensions.png)');
        $blc->write('vendor/resist/ecc-calculations/canvas/snow3.jpg', [['size' => 14, 'x' => 90, 'y' => 35, 'text' => 'test']], '***md***');
        $blc->hr();

        $blc->h4('label tests', '');
        $blc->label(0.1, '***md***');
        $blc->label(1.1, '<code>html code</code>');
        $blc->label('yes', 'yes');
        $blc->label('no', 'no');
        $blc->hr();

        $blc->h4('pre tests', '');
        $blc->pre('pre ***md*** <code>html code</code> $x_x$ `x_x`');

        $blc->h4('note tests', '');
        $blc->note('pre ***md*** <code>html code</code> $x_x$ `x_x`');
    }
}
