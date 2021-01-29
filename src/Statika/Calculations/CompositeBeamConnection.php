<?php declare(strict_types = 1);
/**
 * Analysis of shear connection of beams of composite steel and concrete structures according to Eurocodes - Calculation class for Statika framework
 * (c) Bence VÁNKOS | https://structure.hu | https://github.com/r3sist/ecc-calculations
 */
namespace Statika\Calculations;

use Exception;
use \H3;
use Statika\Ec;
use Statika\EurocodeInterface;

Class CompositeBeamConnection
{
    private const BEAM_POSITION_PERPENDICULAR = 'perpendicular';
    private const BEAM_POSITION_PARALLEL = 'parallel';
    private const CONNECTION_TYPE_WELDING = 'welding';
    private const CONNECTION_TYPE_OPENING = 'opening';

    /**
     * @param Ec $ec
     * @throws Exception
     */
    public function calc(EurocodeInterface $ec): void
    {
        $ec->note('1.: [Szabó B. - Hajlított, nyírt öszvértartók tervezése az Eurocode-dal összhangban, 2017]. [MSZ-EN 1994]');

        $ec->concreteMaterialListBlock('concreteMaterialName');
        $concreteMaterial = $ec->materialTable($ec->concreteMaterialName, 'concreteMaterialName');
        $ec->structuralSteelMaterialListBlock('steelMaterialName', 'S235', ['', 'Acél gerenda anyagminőség']);
        $steelMaterial = $ec->materialTable($ec->steelMaterialName, 'steelMaterial');
        $ec->structuralSteelMaterialListBlock('shearConnectorMaterialName', 'S235', ['', 'Nyíró csap anyagminőség']);
        $sheraConnectorMaterial = $ec->materialTable($ec->shearConnectorMaterialName, 'shearConnectorMaterial');

        $ec->numeric('h', ['h', 'Teljes lemezvastagság'], 180, 'mm', 'Minimum 90 mm [1.) 6.1.]. Nem együttdolgozó lemez esetén 80 mm elegendő.', 'min_numeric,90');

        $ec->h1('Nyírt kapcsolóelemek tervezési ellenállása');
        $ec->note('[1. 4.4.3 120.o.]');
        $ec->def('Gv', 1.25, 'gamma_v = %%', 'Biztonsági tényező');
        $ec->numeric('d', ['d', 'Csap szárátmérő'], 16, 'mm', 'Trapézlemezes kapcsolatnál max 20 (lyukasztott trapézlemezzel 22) mm', 'min_numeric,6');
        $ec->numeric('hsc', ['h_(sc)', 'Csap teljes hossza'], ceil(3*$ec->d), 'mm', 'Minimum $3d$', 'min_numeric,'.ceil(3*$ec->d));
        $ec->numeric('tsc', ['t_(sc)', 'Csap fejvastagsága'], 10, 'mm', '', 'min_numeric,1');
        $ec->def('hsc_d', $ec->hsc/$ec->d, 'h_(sc)/d = %%', '', 'min_numeric,3');
        $ec->alpha = 1;
        if (3 <= $ec->hsc_d && $ec->hsc_d < 4) {
            $ec->alpha = \H3::n4(0.2*($ec->hsc_d + 1));
        }
        $ec->math('alpha = {(1, larr 3 le h_(sc)/d lt 4),(0.2*((h_sc)/d + 1), larr h_(sc)/d gt 4):} = '.$ec->alpha);

        $ec->def('PRd1', \H3::n2((0.8*$sheraConnectorMaterial->fu*($ec->d**2*M_PI)/4)/$ec->Gv/1000), 'P_(Rd,1) = (0.8*f_u*(d^2 pi)/4)/gamma_v = %% [kN]', 'Acél csap szempontjából vett ellenállás');
        $ec->def('PRd2', \H3::n2(((0.29*$ec->alpha*($ec->d**2)*sqrt($concreteMaterial->fck*$concreteMaterial->Ecm*1000))/$ec->Gv)/1000), 'P_(Rd,2) = (0.29*alpha*d^2*sqrt(f_(ck)*E_(cm)))/gamma_v = %% [kN]', 'Beton szempontjából vett ellenállás');
        $ec->info0();
            $ec->def('PRd', min($ec->PRd1, $ec->PRd2), 'P_(Rd) = min{(P_(Rd,1)),(P_(Rd,2)):} = %% [kN]', 'Fejes csap nyírási ellenállása');
        $ec->info1();
        $ec->note('A csapokra 10%-nál ('. 0.1*$ec->PRd .' kN) kisebb közvetlen húzóerő hathat csak!');

        $ec->h2('Trapézlemez hatása a nyírási ellenállásra');
        $ec->numeric('b0', ['b_0', 'Trapézlemez hullámfalak átlagos távolsága'], 100, 'mm');
        $ec->numeric('hp', ['h_p', 'Trapézlemez magassága'], 55, 'mm', '46..76..85 mm', 'max_numeric,85');
        $ec->note('76 mm magassági határ [1. 6.1.]');
        if ($ec->hp > 85) {
            $ec->danger('$h_p$ nem lehet nagyobb, mint $85 [mm]$!');
        }
        if ($ec->b0 < $ec->hp) {
            $ec->danger('$b_0$ nem lehet kiesebb, mint $h_p$!');
        }
        if (($ec->hp + 75) < $ec->hsc) {
            $ec->danger('$h_(sc)$ nem lehet nagyobb, mint $h_p + 75 = '.($ec->hp + 75).' [mm]$!');
        }
        if (($ec->hp + 2*$ec->d) >= $ec->hsc) {
            $ec->danger('Csap $2d = '.($ec->d*2.0).' [mm]$-rel legyen magassabb a trapézlemeznél ($h_(sc,min) = '.($ec->hp+2*$ec->d+1).'$)!');
        }
        $ec->def('hc', $ec->h - $ec->hp, 'h_c = h-h_p = %% [mm]', 'Trapézlemez feletti tiszta betonvastagság');
        if ($ec->hc < 50) {
            $ec->danger('Trapézlemez ('.$ec->hp.') feletti betonvastagságnak ('.$ec->hc.') minimum 50 mm-nek kell lennie! Ajánlott lemezvastagság: '.($ec->hp+50).' mm. (Nem együttdolgozó lemez esetén 40 mm elegendő.)');
        }

        $ec->numeric('br', ['b_r', 'Trapézlemez felső hullámszélesség'], 39, 'mm', '', '');
        $ec->numeric('bs', ['b_s', 'Trapézlemez hullámtengelyek távolsága'], 150, 'mm', '150..30 mm', 'min_numeric,150|max_numeric,300');
        $ec->note('Hullámtengelyek távolsága: hullámdomb tengely felül.');
        $ec->def('br_bs', \H3::n4($ec->br/$ec->bs), 'b_r/b_s = %%', 'Szoros gerinckiosztású trapézlemez engedélyezett csak, NA szerint minimum 0.6', 'min_numeric,0.6');

        $ec->numeric('tp', ['t_p', 'Trapézlemez vastagság'], 1, 'mm', '', 'min_numeric,0.5|max_numeric,2');
        $ec->lst('nr', ['1' => '1', '2' => '2'], ['n_r', 'Egy bordába kerülő csapszám'], '1', 'Gerenda és *hullámvölgy* keresztezésénél');
        $ec->lst('beam_position', ['Gerendával párhuzamos bordázat' => self::BEAM_POSITION_PARALLEL, 'Gerendára merőleges bordázat' => self::BEAM_POSITION_PERPENDICULAR], ['', 'Gerenda és trapézlemez borda helyzete'], 'parallel', '');
        switch ($ec->beam_position) {
            case self::BEAM_POSITION_PARALLEL:
                $ec->img('https://structure.hu/ecc/CompositeBeamConnection01.png');
                $ec->def('kt', \H3::n4(min(1, 0.6*($ec->b0/$ec->hp)*($ec->hsc/$ec->hp - 1))), 'k_t = min{(1), (0.6* b_0/h_p * (h_(sc)/h_p -1 )):} = %%', 'Nyírási ellenállás csökkentő tényezője');
                break;
            case self::BEAM_POSITION_PERPENDICULAR:
                $ec->img('https://structure.hu/ecc/CompositeBeamConnection02.png');
                if ($sheraConnectorMaterial->fu > 450) {
                    $ec->danger('$f_u$ nem lehet nagyobb $450 [N/(mm^2)]$-nél!');
                }
                $ec->def('kt', \H3::n4(min(1, (0.7/sqrt($ec->nr))*($ec->b0/$ec->hp)*($ec->hsc/$ec->hp - 1))), 'k_t = min{(1), (0.7/sqrt(n_r)* b_0/h_p * (h_(sc)/h_p -1 )):} = %%', 'Nyírási ellenállás csökkentő tényezője');
                break;
        }

        $ec->lst('connection', ['Áthegesztett' => self::CONNECTION_TYPE_WELDING, 'Lyukasztott trapézlemez' => self::CONNECTION_TYPE_OPENING], ['', 'Csapok rögzítése trapézlemezen'], 'parallel', '');

        if ($ec->connection === self::CONNECTION_TYPE_WELDING && $ec->d > 20) {
            $ec->danger('Áthegesztéses csapok átmérője nem lehet nagyobb 20 mm-nél!');
        }

        if ($ec->connection === self::CONNECTION_TYPE_OPENING && $ec->d > 22) {
            $ec->danger('Lyukasztott trapézlemezes csapok átmérője nem lehet nagyobb 22 mm-nél!');
        }

        $ec->ktmax = 0;

        if ($ec->connection === self::CONNECTION_TYPE_WELDING && $ec->d <= 20) {
           if ($ec->nr === 1) {
               if ($ec->tp <= 1) {
                   $ec->def('ktmax', 0.85, 'k_(t,max) = %%', 'Felső korlát: Áthegeszett, $d le 20, n_r = 1, t_p le 1$ eset.');
               } else {
                   $ec->def('ktmax', 1, 'k_(t,max) = %%', 'Felső korlát: Áthegeszett, $d le 20, n_r = 1, t_p gt 1$ eset.');
               }
           } else {
               if ($ec->tp <= 1) {
                   $ec->def('ktmax', 0.7, 'k_(t,max) = %%', 'Felső korlát: Áthegeszett, $d le 20, n_r = 2, t_p le 1$ eset.');
               } else {
                   $ec->def('ktmax', 0.8, 'k_(t,max) = %%', 'Felső korlát: Áthegeszett, $d le 20, n_r = 2, t_p gt 1$ eset.');
               }
           }
        }

        if ($ec->connection === self::CONNECTION_TYPE_OPENING && ($ec->d === 19 || $ec->d === 20)) {
            if ($ec->nr === 1) {
                if ($ec->tp <= 1) {
                    $ec->def('ktmax', 0.75, 'k_(t,max) = %%', 'Felső korlát: Lyukasztott trapézlemez, $d = 19..20, n_r = 1, t_p le 1$ eset.');
                } else {
                    $ec->def('ktmax', 0.75, 'k_(t,max) = %%', 'Felső korlát: Lyukasztott trapézlemez, $d = 19..20, n_r = 1, t_p gt 1$ eset.');
                }
            } else {
                if ($ec->tp <= 1) {
                    $ec->def('ktmax', 0.6, 'k_(t,max) = %%', 'Felső korlát: Lyukasztott trapézlemez, $d = 19..20, n_r = 2, t_p le 1$ eset.');
                } else {
                    $ec->def('ktmax', 0.6, 'k_(t,max) = %%', 'Felső korlát: Lyukasztott trapézlemez, $d = 19..20, n_r = 2, t_p gt 1$ eset.');
                }
            }
        }

        if ($ec->ktmax === 0) {
            $ec->def('ktmax', 1, 'k_(t,max) = %%', 'Általános eset.');
        }

        $ec->def('kt', min($ec->kt, $ec->ktmax), 'k_t = min{(k_t),(k_(t,max)):} = %%');

        $ec->success0();
            $ec->def('PRdred', \H3::n2($ec->PRd*$ec->kt), 'P_(Rd,r) = P_(Rd)*k_t = %% [kN]', 'Fejes csap redukált nyírási ellenállása', 'min_numeric,0');
        $ec->success1();

        $ec->h1('Szerkesztési szabályok, vasalás');

        $reinforcementFields = [
            ['name' => 'cnom_top', 'title' => '$c_(nom,top)$', 'type' => 'input'],
            ['name' => 'cnom_bottom', 'title' => '$c_(nom,bot)$', 'type' => 'input'],
            ['name' => 'D_x_top', 'title' => '$phi_(x,top)$', 'type' => 'input'],
            ['name' => 'D_x_bottom', 'title' => '$phi_(x,bot)$', 'type' => 'input'],
            ['name' => 'D_y_top', 'title' => '$phi_(y,top)$', 'type' => 'input'],
            ['name' => 'D_y_bottom', 'title' => '$phi_(y,bot)$', 'type' => 'input'],
        ];
        $reinforcementDefaults = [30, 25, 12, 12, 12, 12];
        $ec->bulk('reinforcement', $reinforcementFields, $reinforcementDefaults, false);
        $ec->reinforcement = $ec->reinforcement[0];
        $ec->note('x: gerendával párhuzamos irány');


        $ec->def('cnom_sc_min', max(20, $ec->reinforcement['cnom_top'] - 5), 'c_(nom,sc, min) = min{(20),(c_(nom,top) - 5):} = %% [mm]', 'Minimum betontakarás csapon');
        $ec->def('cnom_sc', $ec->h - $ec->hsc, 'c_(nom,sc) = %% [mm]', 'Geometriából adód betontakarás csapon', 'min_numeric,'.$ec->cnom_sc_min);

        $ec->boo('rebar_in_valley', ['', 'Bordával párhuzamos vasalás trapézlemez hullámba kerül'], true);
        if ($ec->rebar_in_valley) {
            $ec->note('Ebben az esetben alsó a betontakarás és a kereszt irányú vasalás a borda hullámtól van figyelembe véve.');
            $ec->def('hh', ($ec->hsc - $ec->tsc) - ($ec->hp + $ec->reinforcement['cnom_bottom'] + $ec->reinforcement['D_'.($ec->beam_position === self::BEAM_POSITION_PERPENDICULAR?'y':'x').'_bottom']), 'h_h = (h_(sc)-t_(sc)) - (h_p + c_(nom,bot) + phi_('.($ec->beam_position === self::BEAM_POSITION_PERPENDICULAR?'y':'x').',bot) ) = %%', 'Alsó vasalás és csapfej közti távolság, minimum 30 mm', 'min_numeric,30');
        } else {
            $ec->note('Ebben az esetben a trapézlemez nincs-, de 2 irányú vasalás figyelembe van véve.');
            $ec->def('hh', ($ec->hsc - $ec->tsc) - ($ec->reinforcement['cnom_bottom'] + $ec->reinforcement['D_x_bottom'] + $ec->reinforcement['D_y_bottom']), 'h_h = (h_(sc)-t_(sc)) - (c_(nom,bot) + phi_(x,bot) + phi_(y,bot)) = %%', 'Alsó vasalás és csapfej közti távolság, minimum 30 mm', 'min_numeric,30');
        }

        $ec->numeric('tf', ['t_f', 'Acél gerenda öv vastagsága'], 10, 'mm');

        $ec->boo('bridge', ['', 'Híd szerkezet'], false);
        $ec->def('p1_max', min($ec->h*($ec->bridge?4:6), 800), 'p_(1,max) = min{(800),(h*'.($ec->bridge?4:6).'):} = %% [mm]', 'Hidaknál 4h, szerekezeteknél 6h a maximális tengelytávolság.');

        $ec->boo('section_class_34', ['', 'Önmagában 3. vagy 4. keresztmetszeti osztályú acél öv']);
        if ($ec->section_class_34) {
            $ec->txt('3 vagy 4. keresztmetszeti osztályú nyomott acél övre a csap tengelytávolság és kereszt irányú peremtávolság feltétele:');
            if ($ec->beam_position === self::BEAM_POSITION_PARALLEL) {
                $ec->note('Gerenda helyzet alapján a lemez teljes hosszon érintkezettnek feltételezve.');
                $ec->def('p1_max_2', ceil(22*$ec->tf*sqrt(235/$ec->fy($ec->steelMaterialName, $ec->tf))), 'p_(1,max,2) = 22*t_f*sqrt(235/f_y) = %% [mm]');
            }

            if ($ec->beam_position === self::BEAM_POSITION_PERPENDICULAR) {
                $ec->note('Gerenda helyzet alapján a lemez nem érintkezik teljes hosszon.');
                $ec->def('p1_max_2', ceil(15*$ec->tf*sqrt(235/$ec->fy($ec->steelMaterialName, $ec->tf))), 'p_(1,max,2) = 15*t_f*sqrt(235/f_y) = %% [mm]');
            }

            $ec->def('eD_max', ceil(9*$ec->tf*sqrt(235/$ec->fy($ec->steelMaterialName, $ec->tf))), 'e_(D,max) = 9*t_f*sqrt(235/f_y) = %% [mm]');
        }

        $ec->txt('További feltételek övlemezhez');

        $ec->def('hsc_min', 3*$ec->d, 'h_(sc,min) = 3d = %% [mm]', 'Minimum csap magasság');
        if ($ec->hsc < $ec->hsc_min) {
            $ec->danger('$h_(sc) = '.$ec->hsc.'$ csap magasság kisebb a megengedettnél ('.$ec->hsc.')!');
        }

        $ec->def('d_II_max', 2.5*$ec->tf, 'd_(I,max) = 2.5t_f = %% [mm]', 'Nem acélgerenda tengelyben elhelyezett csap maximális átmérője');
        $ec->def('d_w_max', 1.5*$ec->tf, 'd_(w,max) = 1.5t_f = %% [mm]', 'Csap maximális átmérője, ha fárasztóterhelés léphet fel');

        $ec->def('D_min', 1.5*$ec->d, 'D_(min) = 1.5d = %% [mm]', 'Csapfej minimális átmérője');

        $ec->def('tsc_min', 0.4*$ec->d, 't_(sc,min) = 0.4d = %% [mm]', 'Csapfej minimális vastagsága');
        if ($ec->tsc < $ec->tsc_min) {
            $ec->danger('$t_(sc) = '.$ec->tsc.'$ csapfej vastagság kisebb a megengedettnél ('.$ec->tsc_min.')!');
        }

        $ec->def('e2', 20 + $ec->d/2, 'e_2 = 20+d/2 = %% [mm]', 'Keresztirányú peremtávolság acél gerenda öv szélétől csap tengelyig');
        $ec->def('eV_min', 50, 'e_V = %% [mm]', 'Nem trapézlemezes borda esetében beton széle és csap **széle** közti minimális távolság a keresztmetszetben');
        $ec->note('[1.) 4.4.3.2.] Nem trapézlemezes kiékelésnél a kiékelés oldaléle essen kívül a kapcsolóelem szélétől húzott 45°-os egyenesen.');
        $ec->def('p2_min', 2.5*$ec->d, 'p_(2,min) = 2.5d = %% [mm]', 'Keresztirányú csap tengelytávolság minimuma tömör/sík vb. lemez esetén');
        $ec->def('p2_p_min', 4*$ec->d, 'p_(2,p,min) = 4d = %% [mm]', 'Keresztirányú csap tengelytávolság minimuma nem sík vb. lemez esetén (trapézlemez)');
        $ec->def('p1_min', 5*$ec->d, 'p_(1,min) = 5d = %% [mm]', 'Csapok távolsága egymástól erő irányban');

        $ec->h1('Vasalás segédszámítások');
        $ec->def('s_max', min(2*$ec->h, 350), 's_(max) = min{(2h),(350):} = %% [mm]', 'Vasbetétek maximális távolsága');
        // TODO egy gerinc beroppanása [1.) 186.o.] 1993-1-5-6.1.7.3(2)
    }
}
