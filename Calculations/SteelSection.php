<?php

namespace Calculation;

Class SteelSection extends \Ecc
{

    public function calc($f3)
    {
        $ec = \Ec::instance();
        $blc = \Blc::instance();

        $ec->matList();
        $blc->input('t', 'Lemezvastagság', 10, '`mm`');
        $blc->def('fy',$ec->fy($f3->_mat, $f3->_t),'f_y=%%[MPa]', 'Folyáshatár');
        $blc->def('fu',$ec->fu($f3->_mat, $f3->_t),'f_u=%%[MPa]', 'Szakítószilárdság');
        $blc->input('A_v', 'Nyírási keresztmetszet', 1206, '`mm^2`');
        $blc->input('A', 'Húzási keresztmetszet', 1206, '`mm^2`');
        $blc->input('A_net', '`A_(n\et):` Lyukkal gyengített keresztmetszet', 1206, '`mm^2`');

        $blc->h1('Nyírt keresztmetszet');
        $blc->note('[Szürke 2007: 5.1.5 41.o]');
        $blc->success0('v');
        $blc->def('VplRd', $ec->VplRd($f3->_A_v, $f3->_mat, $f3->_t), 'V_(c,Rd) = V_(pl,Rd) = (A_v*f_y)/(sqrt(3)*gamma_(M0)) = %% kN', 'Nyírási ellenállás 1 és 2. kmo. esetén');
        $blc->success1('v');
        $blc->note('3 és 4. kmo. esetén `V_(c,Rd) = (A_w*f_y)/(sqrt(3)*gamma_(M0))` H és I szelvénynél. Általános esetben `(I*t)/S*f_y/(sqrt(3)*gamma_(M0))`');

        $blc->h1('Központosan húzott keresztmetszet');
        $blc->note('[Szürke 2007: 5.1.2 40.o]');
        $blc->note('Keresztmetszeti besorolásra nincs szükség.');
        $blc->def('NplRd', $ec->NplRd($f3->_A, $f3->_mat, $f3->_t), 'N_(pl,Rd) = (A*f_y)/gamma_(M,0) = %% kN', 'Teljes km. folyási ellenállása');
        $blc->def('NuRd', $ec->NuRd($f3->_A_net, $f3->_mat, $f3->_t), 'N_(u,Rd) = (0.9*A_(n\et)*f_u)/gamma_(M,2) = %% kN', 'Nettó km. képlékeny töréssel szembeni ellenállása');
        $blc->success0('t');
        $blc->def('NcRd', $ec->NtRd($f3->_A, $f3->_A_net, $f3->_mat, $f3->_t), 'N_(t,Rd) = min(N_(pl,Rd), N_(u,Rd)) = %% kN', 'Húzási ellenállás');
        $blc->success1('t');
    }
}
