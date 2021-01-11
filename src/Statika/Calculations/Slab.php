<?php declare(strict_types = 1);
/**
 * RC slab analysis according to Eurocodes - Calculation class for Statika framework
 * (c) Bence VÁNKOS | https://structure.hu | https://github.com/r3sist/ecc-calculations
 */

namespace Statika\Calculations;

use \H3;
use Statika\Ec;
use Statika\EurocodeInterface;

Class Slab
{
    /**
     * @param Ec $ec
     * @throws \Exception
     */
    public function calc(EurocodeInterface $ec): void
    {
        $ec->concreteMaterialListBlock('concreteMaterialName');
        $ec->rebarMaterialListBlock('rebarMaterialName');
        $rebarMaterial = $ec->getMaterial($ec->rebarMaterialName);
        $concreteMaterial = $ec->getMaterial($ec->concreteMaterialName);

        $ec->lst('dir', ['Egy irányban teherhordó' => 1, 'Két irányban teherhordó' => 2], ['', 'Teherhordás módja'], 2);
        $ec->numeric('h', ['h', 'Lemez vastagsága'], 250, 'mm');
        $ec->wrapRebarCount('sx', 'fix', '$s_x \ \ phi_x$ x irány vastávolság és átmérő', 200, 12, '');
        $ec->wrapRebarCount('sy', 'fiy', '$s_y \ \ phi_y$ y irány vastávolság és átmérő', 200, 12, '');
        $ec->numeric('c', ['c', 'Alsó betonfedés'], 30, 'mm');
        $ec->def('d', $ec->h - $ec->c - ($ec->fix + $ec->fiy)/2, 'd = h - c - (phi_x + phi_y)/2 = %% [mm]', 'Hatékony magasság');


        $ec->h1('Pontokon megtámasztott síklemez födémek átlyukadása', 'Átszúródási vizsgálatok');
        $ec->note('[Vasbeton szerkezetek 6.8 (49.o)]');
        $ec->numeric('acol', ['a_(col)', 'Pillér méret egyik dimenziója'], 400, 'mm');
        $ec->numeric('bcol', ['b_(col)', 'Pillér méret másik dimenziója'], 400, 'mm');
        $ec->boo('colO', ['', 'Körpillér'], false, '');
        if ($ec->colO) {
            $ec->txt('$phi_(col) = max(a_(col), b_(col))$');
            $ec->acol = max($ec->acol, $ec->bcol);
            $ec->bcol = $ec->acol;
        }

        $ec->h2('Nyíróerők központos reakcióerő esetében');
        $ec->input('VEd', ['V_(Ed)', 'Központos reakcióerő'], 500, 'kN', 'Lemez alatti és feletti oszlop normálerejének különbsége');
        $betas = [
          'Sarok oszlop 1.5' => 1.5,
          'Szélső oszlop 1.4' => 1.4,
          'Közbenső oszlop 1.15' => 1.15,
        ];
        $ec->lst('beta', $betas,  ['beta', 'Teher- és megtámasztás bizonytalansági tényező'], 1.5, 'Számításba nem vett hajéítónyomaték hatása');
        $ec->numeric('lc', ['l_c', 'Lemezszél és szélső pillér távolsága'], 500, 'mm', 'Lemez konzolhossz');

        $ec->region0('piller0', 'Pillérkiosztás szerkesztési szabályainak ellenőrzése');
            $ec->txt('**Az ellenőrzés *x* és *y* irányban is lefuttatandó!**');
            $ec->numeric('l1', ['l_1', 'Pillérköz 1'], 6000, 'mm');
            $ec->numeric('l2', ['l_2', 'Szomszédos pillérköz'], 5000, 'mm');
            $ec->math('0.8 < (l_i/l_(i+1)='. H3::n2($ec->l1/$ec->l2).') < 1.25', 'Szabvány szerinti feltétel');
            if (0.8 <= $ec->l1/$ec->l2 && $ec->l1/$ec->l2 <= 1.25) {
                $ec->label('yes', 'ok');
            } else {
                $ec->label('no', 'nem ok');
            }
            $ec->md('Konzol hossz javaslat:');
            $ec->math('0.2*l_1 < l_c < 0.4*l_1');
            if (0.2*$ec->l1 <= $ec->lc && $ec->lc <= 0.4*$ec->l1) {
                $ec->label('yes', 'ok');
            } else {
                $ec->label('no', 'nem ok');
            }
            $ec->note('Javaslat [Vasbeton szerkezetek (2016) 6.8 49.o.] alapján. Nem teljesülés esetén peremgerenda tervezése javasolt, vagy tényleges nyomatékok figyelembevételével kell az átlyukadást ellenőrizni! ß számítható pontosan');
        $ec->region1();

        if ($ec->lc <= 2*$ec->d + max($ec->acol, $ec->bcol)/2 && $ec->beta == 1.5 && $ec->colO == false) {
            $ec->txt('**Sarok négyszögpillér, kisebb konzol esete:**', '$l_c = '.$ec->lc.' [m]$');
            $ec->def('u', $ec->acol + $ec->bcol + 4*$ec->d, 'u = a_(col) + b_(col) + 4*d= %% [mm]');
        } elseif ($ec->lc <= 2*$ec->d + $ec->acol/2 && $ec->beta === 1.5 && $ec->colO == true) {
            $ec->txt('**Sarok körpillér, kisebb konzol esete:**');
            $ec->def('u', 2*(2*$ec->d + 0.5*$ec->acol)*pi()*0.5, 'u = 0.5*2*(2*d + 0.5*phi_(col))*pi = %% [mm]', 'Közelítés kerület felével');
        } elseif ($ec->lc <= 2*$ec->d + max($ec->acol, $ec->bcol)/2 && $ec->beta == 1.4 && $ec->colO == false) {
            $ec->txt('**Szélső négyszögpillér, kisebb konzol esete:**');
            $ec->def('u', 2*min($ec->acol, $ec->bcol) + max($ec->acol, $ec->bcol) + 8*$ec->d, 'u = 2*min(a_(col),b_(col)) + max(a_(col),b_(col)) + 8*d = %% [mm]');
        } elseif ($ec->lc*100 <= 2*$ec->d + $ec->acol/2 && $ec->beta === 1.4 && $ec->colO == true) {
            $ec->txt('**Szélső körpillér, kisebb konzol esete:**');
            $ec->def('u', 2*(2*$ec->d + 0.5*$ec->acol)*pi()*0.5, 'u = 0.5*2*(2*d + 0.5*phi_(col))*pi = %% [mm]', 'Közelítés kerület felével');
        } elseif ($ec->beta == 1.15 && $ec->colO == true) {
            $ec->txt('**Közbenső körpillér vagy kellően nagy konzol esete:**');
            $ec->def('u', 2*(2*$ec->d + 0.5*$ec->acol)*pi(), 'u = 2*(2*d + 0.5*phi_(col))*pi = %% [mm]');
        } else {
            $ec->txt('**Közbenső négyszögpillér vagy kellően nagy konzol esete:**');
            $ec->def('u', 2*$ec->acol + 2*$ec->bcol + 16*$ec->d, 'u = 2*a_(col) + 2*b_(col) + 16*d = %% [mm]');
        }

//        $ec->write('vendor/resist/ecc-calculations/canvas/slab0.jpg', []); // TODO replace write()

        $ec->def('vEd', ($ec->beta*$ec->VEd)/($ec->u*$ec->d)*10000, 'v_(Ed) = (beta*V_(Ed))/(u*d) = %% [(kN)/m^2]');

        $ec->h2('Beton teherbírásának ellenőrzése', 'Ferde nyomásra $u_0$ kerület mentén');
        switch ($ec->beta) {
            case 1.15:
                if ($ec->colO == true) {
                    $ec->def('u0', H3::n0(2*$ec->acol*0.5*pi()), 'u_0 = 2*(phi_(col)/2)*pi = %% [mm]');
                } else {
                    $ec->def('u0', H3::n0(2*$ec->acol + 2*$ec->bcol), 'u_0 = 2*a_(col) + 2*b_(col) = %% [mm]');
                }
                break;
            case 1.4:
                // TODO itt tartok
                $ec->def('u0', H3::n0(min(1, 1)), 'u_0 = 2*a_(col) + 2*b_(col) = %% [mm]');
                break;
        }
        $ec->def('vRdmax', 0.5*(0.6*(1 - $concreteMaterial->fck/250))*$concreteMaterial->fcd, 'v_(Rd,max) = 0.5*0.6*(1-f_(ck)/250)*f_(cd) = %% [N/(mm^2)]');
        $ec->hr();

        $ec->h1('Kétirányban teherhordó lemez képlékeny igénybevétele');
        $ec->numeric('p_Ed', ['p_Ed', 'Felületen megoszló teher'], 5, 'kN/m²', '');
        $ec->numeric('l_x', ['l_x', 'Lemez szélesség x irányban'], 10, 'm', '');
        $ec->numeric('l_y', ['l_y', 'Lemez szélesség x irányban'], 10, 'm', '');
        
        if ($ec->l_x/$ec->l_y <= 2) {
            $ec->label('yes', 'Két irányban teherhordó lemez');
        } else {
            $ec->label('no', 'Nem két irányban teherhordó lemez');
        }

        $etaOpt = 0.5*($ec->l_y/$ec->l_x)*($ec->l_y/$ec->l_x);
        $eta = array(
            'η optimális minimális vasmennyiség: '. $etaOpt => $etaOpt,
            'η 45°'=> 0.5*($ec->l_y/$ec->l_x),
            '0.10' => 0.10,
            '0.15' => 0.15,
            '0.20' => 0.20,
            '0.25' => 0.25,
            '0.30' => 0.30,
            '0.35' => 0.35,
            '0.40' => 0.40,
            '0.45' => 0.45,
            '0.50' => 0.5,
        );
        $ec->eta = 0.1;
        $ec->lst('eta', $eta, ['', 'Töréskép']);
        if(!$ec->eta) {
            $ec->eta = $etaOpt;
        }
        $ec->math('eta = '. $ec->eta);

        $ec->region0('r0');
        $ec->def('p_y', H3::n2((1-(4/3)*$ec->eta)*$ec->p_Ed), 'p_y = %% [(kN)/m^2]');
        $ec->def('p_x', H3::n2(((4/3)*$ec->eta)*$ec->p_Ed), 'p_x = %% [(kN)/m^2]');
        $ec->def('p_check', H3::n2($ec->p_x + $ec->p_y), 'p_x + p_y = %% [(kN)/m^2]');
        $ec->region1();

        $ec->def('m_y', H3::n2(($ec->p_y * $ec->l_y * $ec->l_y)/8), 'm_y = (p_y * l_y^2)/8 = %% [(kNm)/m]', '1 m széles lemezsávra jutó nyomaték');
        $ec->def('m_x', H3::n2(($ec->p_x * $ec->l_x * $ec->l_x)/8), 'm_x = (p_x * l_x^2)/8 = %% [(kNm)/m]', '1 m széles lemezsávra jutó nyomaték');
        $ec->def('m_xyA', H3::n2($ec->m_y), 'm_(xyA) := %% [(kNm)/m]', 'Sarkok gátolt felemelkedéséből származó csvarónyomaték');

        if ($ec->m_y >= $ec->m_x) {
            $ec->label('yes', '$m_y >= m_x$');
        } else {
            $ec->label('no', '$m_y < m_x$');
        }


        $ec->h1('Lemezekre vonatkozó szerkesztési szabályok');
        $ec->txt('Monolit lemezek legkisebb vastagsága:');
        $ec->txt('Nyírási vasalás nélkül: $h_(min) = 70 [mm]$, nyírási vasalással: $h_(min) = 200 [mm]$');
        $ec->txt('Minimális húzott vasmennyiség:');
        $ec->note('A húzott hajlítási fővasalásra előírt minimális és maximális vashányad a gerendákéval megegyező. [Vasbeton szerkezetek 8.5.1]');
        if ($ec->dir == 1) {
            $ec->txt('Egyirányban teherhordó lemezek keresztirányú elosztó vasalásának keresztmetszete legalább a fővasalásénak 20%-a legyen.');
            $ec->note('Azonos acél szilárdsági osztály esetén. Egyébként aránnyal felszorozva.');
        }
        $ec->def('rhoMin', max(0.26*($concreteMaterial->fctm/$rebarMaterial->fy), 0.0015), 'rho_(min) = max{(0.26*f_(ctm)/f_(yk)),(0.0015):} = max{(0.26*'.$concreteMaterial->fctm.'/'.$rebarMaterial->fy.'),(0.0015):} = %%', 'Minimális húzott vashányad');
        $ec->def('AsMin', $ec->rhoMin*1000*$ec->d, 'A_(s,min) = rho_(min)*1000[mm]*d = %% [(mm^2)/m]', 'Előírt minimális húzott vasmennyiség négyszög keresztmetszet esetén');
        $ec->txt('Maximális összes vasmennyiség:');
        $ec->def('AsMax', 0.04*1000*$ec->h, 'A_(s,max) = 0.04*1000[mm]*h = %% [(mm^2)/m]', 'Összes hosszvasalás megengedett legnagyobb mennyisége négyszög keresztmetszetben');
        $ec->txt('Legnagyobb vastávolság');
        if ($ec->h > 100) {
            $ec->def('sMax', min(2*$ec->h, 300), 's_(max) = min{(2h),(300):} = %% [mm]', 'Fő vasalás max vastávolsága');
            if ($ec->dir == 1) {
                $ec->def('sMax2', min(3 * $ec->h, 400), 's_(max,2) = min{(3h),(400):} = %% [mm]', 'Mellék vasalás max vastávolsága');
            }
        } else {
            $ec->txt('`h le 100 [mm]`');
            $ec->def('sMax', 200, 's_(max) = %% [mm]', 'Fő vasalás max vastávolsága');
            if ($ec->dir == 1) {
                $ec->def('sMax2', 300, 's_(max,2) = %% [mm]', 'Mellék vasalás max vastávolsága');
            }
        }
        $ec->note('Kétirányban teherhordó lemez esetén mindkét irányban a fő vasalásra jellemző értéket kell figyelembe venni.');
        $ec->txt('Legnagyobb vasátmérő:');
        $ec->def('phiMax', $ec->h/10, 'phi_(max) = h/10 = %% [mm]');

        $ec->region0('more0', 'További megjegyzések');
        $ec->h2('Vasalás támaszok környezetében');
        $ec->md('
A méretezett alsó mezővasalás legalább 50%-át a támaszig kell vezetni és ott megfe|elóen le kell horgonyozni.

A felső vasalást:

+ szélső, nem befogott támasz esetén a szélsó mezőnyomaték 15%-ára kell méretezni,és a mező 0,l-szeres hosszáig be kell vezetni,
+ belső támasz esetén a szomszédos mezőnyomatékok nagyobbikának legalább 25%-ára kell méretezni, és mindkét mező minimum 0.2-szeres hosszán végig kell vezetni.
        
Sarkainál felemelkedésben gátolt, kétirányban teherhordó lemezek sarkainál a csavarónyomatékok felvételére kétirányú felső vasalást kell tervezni, amelynek intenzitása pontosabb számítás hiányában megegyezik a rövidebb irányban futó alsó vasaláséval, és mindkét irányban a megfelelő támaszköz 0,2-szereséig be kell vezetni.');

        $ec->h3('Konzollemez, szabad lemezszél');
        $ec->img('https://structure.hu/ecc/slab0.jpg', 'Konzollemez, szabad lemezszél');
        $ec->region1();
    }
}
