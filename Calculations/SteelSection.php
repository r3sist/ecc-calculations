<?php

namespace Calculation;

Class SteelSection extends \Ecc
{

    public function calc($f3)
    {
        $ec = \Ec::instance();
        $blc = \Blc::instance();

        $ec->sectionFamilyList();
        $ec->sectionList($f3->_sectionFamily);
        $ec->sectionData($f3->_sectionName, true);

        $ec->matList();
        $blc->input('t', 'Lemezvastagság', 10, '`mm`');
        $blc->txt($f3->_sectionName.' legnagyobb lemezvastagsága: `t_(max) = '. 10*max($f3->_sectionData['tf'], $f3->_sectionData['tw']).' [mm]`');
        $blc->def('fy',$ec->fy($f3->_mat, $f3->_t),'f_y=%%[MPa]', 'Folyáshatár');
        $blc->def('fu',$ec->fu($f3->_mat, $f3->_t),'f_u=%%[MPa]', 'Szakítószilárdság');

        $blc->region0('rEgyedi', 'Egyedi keresztmetszeti területek megadása');
            $blc->input('A_v', 'Egyedi nyírási keresztmetszet', 0, '`mm^2`', 'Az itt megadott nemnulla érték felülírja a betöltött szelvény adatokat!');
            $blc->input('A', 'Egyedi húzási keresztmetszet', 0, '`mm^2`', 'Az itt megadott nemnulla érték felülírja a betöltött szelvény adatokat!');
        $blc->region1('rEgyedi');

        if ($f3->_A_v == 0) {
            $blc->def('A_v', $f3->_sectionData['Az']*100, 'A_v = A_(z,'.$f3->_sectionName.') = %% [mm^2]', 'Nyírási keresztmetszet');
        }

        if ($f3->_A == 0) {
            $blc->def('A', $f3->_sectionData['Ax']*100, 'A = A_(x,'.$f3->_sectionName.') = %% [mm^2]', 'Húzási keresztmetszet');
        }

        $blc->region0('r0', 'Nettó keresztmetszet számítás');
            $blc->input('n', 'Csavarok száma', 1, '');
            $ec->boltList('btName');
            $blc->def('A_net_calculated', $f3->_A - $f3->_n*$ec->boltProp($f3->_btName, 'd0')*$f3->_t, 'A_(n\et, calcu\lated) = A - n*d_0*t = %% [mm^2]', 'Számított nettó keresztmetszet');
        $blc->region1('r0');
        $blc->input('A_net', '`A_(n\et):` Lyukkal gyengített keresztmetszet', 1206, '`mm^2`');

        $blc->h1('Nyírt keresztmetszet');
        $blc->note('[Szürke 2007: 5.1.5 41.o]');
        $blc->success0('v');
        $blc->def('VplRd', $ec->VplRd($f3->_A_v, $f3->_mat, $f3->_t), 'V_(c,Rd) = V_(pl,Rd) = (A_v*f_y)/(sqrt(3)*gamma_(M0)) = %% [kN]', 'Nyírási ellenállás 1 és 2. kmo. esetén');
        $blc->success1('v');
        $blc->note('3 és 4. kmo. esetén `V_(c,Rd) = (A_w*f_y)/(sqrt(3)*gamma_(M0))` H és I szelvénynél. Általános esetben `(I*t)/S*f_y/(sqrt(3)*gamma_(M0))`');

        $blc->input('VEd', '`V_(Ed)` nyíróerő', 100, 'kN');
        $blc->label($f3->_VEd/$f3->_VplRd, 'Nyírási kihasználtság');

        $blc->h1('Központosan húzott keresztmetszet');
        $blc->note('[Szürke 2007: 5.1.2 40.o]');
        $blc->note('Keresztmetszeti besorolásra nincs szükség.');
        $blc->def('NplRd', $ec->NplRd($f3->_A, $f3->_mat, $f3->_t), 'N_(pl,Rd) = (A*f_y)/gamma_(M,0) = %% [kN]', 'Teljes km. folyási ellenállása');
        $blc->def('NuRd', $ec->NuRd($f3->_A_net, $f3->_mat, $f3->_t), 'N_(u,Rd) = (0.9*A_(n\et)*f_u)/gamma_(M,2) = %% [kN]', 'Nettó km. képlékeny töréssel szembeni ellenállása');
        $blc->success0('t');
        $blc->def('NtRd', $ec->NtRd($f3->_A, $f3->_A_net, $f3->_mat, $f3->_t), 'N_(t,Rd) = min(N_(pl,Rd), N_(u,Rd)) = %% [kN]', 'Húzási ellenállás');
        $blc->success1('t');

        $blc->input('NEd', '`N_(Ed)` húzóerő', 200, 'kN');
        $blc->label($f3->_NEd/$f3->_NtRd, 'Húzási kihasználtság');
    }
}
