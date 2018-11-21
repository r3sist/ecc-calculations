<?php

namespace Calculation;

Class Wall extends \Ecc
{

    /**
     * @var $f3 \Base
     * @var $blc \Blc
     * @var $ec \Ec\Ec
     * @throws \Exception
     */
    public function calc(object $f3, object $blc, object $ec): void
    {
        $blc->input('l', 'Fal hossz', '1', 'm', '');
        $blc->input('t_w', 'Fal vastagság', '200', 'mm', '');
        $blc->input('h', 'Fal magasság', '3', 'm', '');

        $blc->txt('Fal/faltartó ellenőrzés:');
        if ($f3->_l/($f3->_t_w/1000) < 4) {
            $f3->_wc = 1;
            $f3->_wcText = 'Faltartó';
            $blc->math('l/t_w = '.$f3->_l/$f3->_t_w.'\< 4');
            $blc->label('no', $f3->_wcText);
            $blc->input('b', 'Faltartók támaszköze', '4', 'm', '');
        } else {
            $f3->_wc = 0;
            $f3->_wcText = 'Fal';
            $blc->math('l/t_w = '.$f3->_l/($f3->_t_w/1000).'\>= 4');
            $blc->label('yes', $f3->_wcText);
        }

        $blc->h1('Minimális vasmennyiség és vastávolságok', 'Szerkesztési szabályok ('.$f3->_wcText.')');

        $blc->def('A_c', $f3->_t_w*1000, 'A_c = t_w*1[m] = %% [mm^2]', 'Beton keresztmetszet 1 [m] hosszon');
        switch ($f3->_wc) {
            case 0:
                if ($f3->_t_w <= 100) {
                    $blc->txt('`t_w < 100 [mm]`: Egy rétegű vasalás elegendő');
                }
                if ($f3->_t_w >= 200) {
                    $blc->txt('`t_w >= 200 [mm]`: Két rétegű vasalás szükséges');
                }
                $blc->def('A_sminv', $f3->_A_c*0.003, 'A_(s,min,v) = A_c*0.3% = %% [(mm^2) / m]', 'Minimális függőleges vasmennyiség');
                $blc->def('A_smaxv', $f3->_A_c*0.04, 'A_(s,max,v) = A_c*4% = %% [(mm^2) / m]', 'Maximális függőleges vasmennyiség');
                $blc->def('s_maxv', min($f3->_t_w*3, 400), 's_(max,v) = min(3*t_w, 400) = %% [mm]', 'Maximális függőleges vastávolság');
                $blc->def('A_sminh', min($f3->_A_sminv*0.25, $f3->_A_c*0.0015), 'A_(s,min,h) = min(A_(s,min,v)*25%, A_c*0.15%) = %% [(mm^2) / m]', 'Minimális vízszintes vasmennyiség');
                $blc->def('A_smaxh', $f3->_A_c*0.04, 'A_(s,max,h) = A_c*4% = %% [(mm^2) / m]', 'Maximális vízszintes vasmennyiség');
                $blc->def('s_maxh', 400, 's_(max,h) = %% [mm]', 'Maximális vízszintes vastávolság');
                $blc->txt('', 'Két- vagy többrétegű vasalás esetén összekötő vasalás alkalmazandó legalább \`4 [(db)/m^2]\` és minimum \`150 [(mm^2)/m^2]\` összesített keresztmetszettel.');
                break;
            case 1:
                if ($f3->_b/$f3->_h < 2) {
                    $blc->txt('`b/h = '.number_format($f3->_b/$f3->_h, 1).' < 2`: Faltartóba 2 rétegű vasalás kell');
                } else {
                    $blc->txt('`b/h = '.number_format($f3->_b/$f3->_h, 1).' > 2`: Faltartóba 1 rétegű vasalás elég');
                }
                $blc->def('A_sminvh', max($f3->_A_c*0.001, 150), 'A_(s,min,vh) = max(150, A_c*0.1%) = %% [(mm^2) / m]', 'Minimális vízszintes és függőleges vasmennyiség');
                $blc->def('A_smaxvh', $f3->_A_c*0.04, 'A_(s,max,vh) = A_c*4% = %% [(mm^2) / m]', 'Maximális vízszintes és függőleges vasmennyiség');
                $blc->def('s_maxvh', min(3*$f3->_t_w, 300), 's_(max,vh) = min(2*t_w, 300) = %% [mm]', 'Maximális vízszintes és függőleges vastávolság');
                $blc->txt('', 'Ha faltartó valamely szakaszán a függőleges vasalás mennyisége \`A_s>0.02A_c\`, akkor ott kengyelekből álló kereszt irányú vasalást kell elhelyezni az oszlopokra vonatkozó követelmények használatával.');
                break;
        }

        $blc->h1('Földrengés');
        $blc->h2('Duktilis falak');
        $blc->input('h_s', 'Szabad szintmagasság', 3, 'm');
        $blc->boo('n_s', 'Szintek száma `n_s >= 7`', 0, '');
        $ns = 1;
        if ($f3->_n_s) {
            $ns = 2;
        }
        $blc->def('bwo', max(150, ($f3->_h_s*1000)/20), 'b_(wo) >= max{(0.15[m]), (h_s/20):} = %% [mm]', 'Minimáslis fal gerincvastagság');
        $blc->def('hcr', min(min(max($f3->_l, $f3->_h/6), 2*$f3->_l), $ns*$f3->_h_s), 'h_(cr) >= min{(min{(max{(l), (h/6):}), (h_s):}), (2*l_w):} = %% [m]', 'Fal alapsíkja feletti kritikus tartomány');
    }
}
