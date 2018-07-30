<?php

namespace Calculation;

Class Wall extends \Ecc
{

    public function calc($f3)
    {
        $ec = \Ec::instance();
        $blc = \Blc::instance();

        $blc->input('l', 'Fal hossz', '1', 'm', '');
        $blc->input('t_w', 'Fal vastagság', '20', 'cm', '');
        $blc->def('t_w', $f3->_t_w/100,'t_w = %% [m]');
        if ($f3->_t_w <= 0.1) {
            $blc->txt('`t_w < 100 [mm]`: Egy rétegű vasalás');
        }
        if ($f3->_t_w >= 0.2) {
            $blc->txt('`t_w >= 200 [mm]`: Két rétegű vasalás');
        }

        $blc->input('h', 'Fal magasság', '3', 'm', '');

        $blc->h1('Fal/faltartó');
        $blc->input('b', 'Faltartók támaszköze', '4', 'm', '');

        if ($f3->_l/$f3->_t_w < 4) {
            $f3->_wc = 1;
            $blc->math('l/t_w = '.$f3->_l/$f3->_t_w.' < 4');
            $blc->label('no', 'Faltartó!');
            if ($f3->_b/$f3->_h < 2) {
                $blc->txt('`b/h = '.number_format($f3->_b/$f3->_h, 1).' < 2`: Faltartóba 2 rétegű vasalás kell');
            } else {
                $blc->txt('`b/h = '.number_format($f3->_b/$f3->_h, 1).' > 2`: Faltartóba 1 rétegű vasalás elég');
            }
        } else {
            $f3->_wc = 0;
            $blc->math('l/t_w = '.$f3->_l/$f3->_t_w.' >= 4');
            $blc->label('yes', 'Fal');
        }

        $blc->h1('Minimális vasmennyiség és vastávolságok', 'Szerkesztési szabályok');

        $blc->def('A_c', $f3->_t_w*1000000, 'A_c = t_w*1[m] = %% [mm^2]', 'Beton keresztmetszet');
        switch ($f3->_wc) {
            case 0:
                $blc->def('A_sminv', $f3->_A_c*0.003, 'A_(s,min,v) = A_c*0.3% = %% [(mm^2) / m]', 'Minimális függőleges vasmennyiség');
                $blc->def('A_smaxv', $f3->_A_c*0.04, 'A_(s,max,v) = A_c*4% = %% [(mm^2) / m]', 'Maximális függőleges vasmennyiség');
                $blc->def('s_maxv', min($f3->_t_w*1000*3, 400), 's_(max,v) = min(3*t_w, 400) = %% [mm]', 'Maximális függőleges vastávolság');
                $blc->def('A_sminh', min($f3->_A_sminv*0.25, $f3->_A_c*0.0015), 'A_(s,min,v) = min(A_(s,min,v)*25%, A_c*0.15%) = %% [(mm^2) / m]', 'Minimális vízszintes vasmennyiség');
                $blc->def('A_smaxh', $f3->_A_c*0.04, 'A_(s,max,v) = A_c*4% = %% [(mm^2) / m]', 'Maximális vízszintes vasmennyiség');
                $blc->def('s_maxh', 400, 's_(max,h) = %% [mm]', 'Maximális vízszintes vastávolság');
                $blc->txt('', 'Két- vagy többrétegű vasalás esetén összekötő vasalás alkalmazandó legalább \`4 [(db)/m^2]\` és minimum \`150 [(mm^2)/m^2]\` összesített keresztmetszettel.');
                break;
            case 1:
                $blc->def('A_sminvh', max($f3->_A_c*0.001, 150), 'A_(s,min,vh) = max(150, A_c*0.1%) = %% [(mm^2) / m]', 'Minimális vízszintes és függőleges vasmennyiség');
                $blc->def('A_smaxvh', $f3->_A_c*0.04, 'A_(s,max,vh) = A_c*4% = %% [(mm^2) / m]', 'Maximális vízszintes és függőleges vasmennyiség');
                $blc->def('s_maxvh', min(3*$f3->_t_w*1000, 300), 's_(max,vh) = min(2*t_w, 300) = %% [mm]', 'Maximális vízszintes és függőleges vastávolság');
                $blc->txt('', 'Ha faltartó valamely szakaszán a függőleges vasalás mennyisége \`A_s>0.02A_c\`, akkor ott kengyelekből álló kereszt irányú vasalást kell elhelyezni az oszlopokra vonatkozó követelmények használatával.');
                break;
        }
    }
}