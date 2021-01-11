<?php declare(strict_types = 1);
/**
 * RC wall analysis according to Eurocodes - Calculation class for Statika framework
 * (c) Bence VÁNKOS | https://structure.hu | https://github.com/r3sist/ecc-calculations
 */

namespace Statika\Calculations;

use \H3;
use Statika\EurocodeInterface;

Class Wall
{
    /**
     * @param Ec $ec
     */
    public function calc(EurocodeInterface $ec): void
    {
        $ec->numeric('l', ['l', 'Fal hossz'], 1, 'm', '');
        $ec->numeric('t_w', ['t_w', 'Fal vastagság'], 200, 'mm', '');
        $ec->numeric('h', ['h', 'Fal magasság'], 3, 'm', '');

        $ec->txt('Fal/faltartó ellenőrzés:');
        if ($ec->l/($ec->t_w/1000) < 4) {
            $ec->wc = 1;
            $ec->wcText = 'Faltartó';
            $ec->math('l/t_w = '. H3::n3($ec->l/$ec->t_w).'\< 4');
            $ec->label('no', $ec->wcText);
            $ec->numeric('b', ['b', 'Faltartók támaszköze'], 4, 'm', '');
        } else {
            $ec->wc = 0;
            $ec->wcText = 'Fal';
            $ec->math('l/t_w = '.$ec->l/($ec->t_w/1000).'\>= 4');
            $ec->label('yes', $ec->wcText);
        }

        $ec->h1('Szerkesztési szabályok ('.$ec->wcText.')', 'Minimális vasmennyiség és vastávolságok');

        $ec->def('A_c', $ec->t_w*1000, 'A_c = t_w*1[m] = %% [mm^2]', 'Beton keresztmetszet $1 [m]$ hosszon');
        switch ($ec->wc) {
            case 0:
                if ($ec->t_w <= 100) {
                    $ec->txt('$t_w < 100 [mm]$: Egy rétegű vasalás elegendő');
                }
                if ($ec->t_w >= 200) {
                    $ec->txt('$t_w >= 200 [mm]$: Két rétegű vasalás szükséges');
                }
                $ec->def('A_sminv', $ec->A_c*0.003, 'A_(s,min,v) = A_c*0.3% = %% [(mm^2)/m]', 'Minimális függőleges vasmennyiség');
                $ec->def('A_smaxv', $ec->A_c*0.04, 'A_(s,max,v) = A_c*4% = %% [(mm^2)/m]', 'Maximális függőleges vasmennyiség');
                $ec->def('s_maxv', min($ec->t_w*3, 400), 's_(max,v) = min{(3*t_w), (400):} = %% [mm]', 'Maximális függőleges vastávolság');
                $ec->def('A_sminh', min($ec->A_sminv*0.25, $ec->A_c*0.0015), 'A_(s,min,h) = min{(A_(s,min,v)*25%), (A_c*0.15%):} = %% [(mm^2)/m]', 'Minimális vízszintes vasmennyiség');
                $ec->def('A_smaxh', $ec->A_c*0.04, 'A_(s,max,h) = A_c*4% = %% [(mm^2)/m]', 'Maximális vízszintes vasmennyiség');
                $ec->def('s_maxh', 400, 's_(max,h) = %% [mm]', 'Maximális vízszintes vastávolság');
                $ec->txt('', 'Két- vagy többrétegű vasalás esetén összekötő vasalás alkalmazandó legalább $4 [(db)/m^2]$ és minimum $150 [(mm^2)/m^2]$ összesített keresztmetszettel.');
                break;
            case 1:
                if ($ec->b/$ec->h < 2) {
                    $ec->txt('$b/h = '.number_format($ec->b/$ec->h, 1).' < 2 $: Faltartóba 2 rétegű vasalás kell');
                } else {
                    $ec->txt('$b/h = '.number_format($ec->b/$ec->h, 1).' > 2 $: Faltartóba 1 rétegű vasalás elég');
                }
                $ec->def('A_sminvh', max($ec->A_c*0.001, 150), 'A_(s,min,vh) = max{(150), (A_c*0.1%):} = %% [(mm^2) / m]', 'Minimális vízszintes és függőleges vasmennyiség');
                $ec->def('A_smaxvh', $ec->A_c*0.04, 'A_(s,max,vh) = A_c*4% = %% [(mm^2) / m]', 'Maximális vízszintes és függőleges vasmennyiség');
                $ec->def('s_maxvh', min(3*$ec->t_w, 300), 's_(max,vh) = min{(2*t_w), (300):} = %% [mm]', 'Maximális vízszintes és függőleges vastávolság');
                $ec->txt('', 'Ha faltartó valamely szakaszán a függőleges vasalás mennyisége $A_s > 0.02A_c$, akkor ott kengyelekből álló kereszt irányú vasalást kell elhelyezni az oszlopokra vonatkozó követelmények használatával.');
                break;
        }

        $ec->h1('Földrengés');
        $ec->h2('Duktilis falak');
        $ec->numeric('h_s', ['h_s', 'Szabad szintmagasság'], 3, 'm');
        $ec->boo('n_s', ['', 'Szintek száma $n_s >= 7$'], false, '');
        $ns = 1;
        if ($ec->n_s) {
            $ns = 2;
        }
        $ec->def('bwo', max(150, ($ec->h_s*1000)/20), 'b_(wo) >= max{(0.15[m]), (h_s/20):} = %% [mm]', 'Minimáslis fal gerincvastagság');
        $ec->def('hcr', min(min(max($ec->l, $ec->h/6), 2*$ec->l), $ns*$ec->h_s), 'h_(cr) >= min{(min{(max{(l), (h/6):}), (h_s):}), (2*l_w):} = %% [m]', 'Fal alapsíkja feletti kritikus tartomány');
    }
}
