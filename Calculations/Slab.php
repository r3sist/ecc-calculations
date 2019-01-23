<?php

namespace Calculation;

Class Slab extends \Ecc
{

    /**
     * @var $f3 \Base
     * @var $blc \Blc
     * @var $ec \Ec\Ec
     * @throws \Exception
     */
    public function calc(object $f3, object $blc, object $ec): void
    {
        $blc->toc();

        $ec->matList('cMat', 'C25/30', 'Beton anyagminőség');
        $ec->saveMaterialData($f3->_cMat, 'c');
        $ec->matList('rMat', 'B500', 'Betonvas anyagminőség');
        $ec->saveMaterialData($f3->_rMat, 'r');
        $blc->lst('dir', ['Egy irányban teherhordó' => 1, 'Két irányban teherhordó' => 2], 'Teherhordás módja', 2);
        $blc->numeric('h', ['h', 'Lemez vastagsága'], 250, 'mm');
        $blc->numeric('d', ['d', 'Lemez hatékony vastagsága'], 200, 'mm');

        $blc->h1('Lemezekre vonatkozó szerkesztési szabályok');

        $blc->txt('Monolit lemezek legkisebb vastagsága:');
        $blc->txt('Nyírási vasalás nélkül: $h_(min) = 70 [mm]$, nyírási vasalással: $h_(min) = 200 [mm]$');
        $blc->txt('Minimális húzott vasmennyiség:');
        $blc->note('A húzott hajlítási fővasalásra előírt minimális és maximális vashányad a gerendákéval megegyező. [Vasbeton szerkezetek 8.5.1]');
        if ($f3->_dir == 1) {
            $blc->txt('Egyirányban teherhordó lemezek keresztirányú elosztó vasalásának keresztmetszete legalább a fővasalásénak 20%-a legyen.');
            $blc->note('Azonos acél szilárdsági osztály esetén. Egyébként aránnyal felszorozva.');
        }
        $blc->def('rhoMin', max(0.26*($f3->_cfctm/$f3->_rfy), 0.0015), 'rho_(min) = max{(0.26*f_(ctm)/f_(yk)),(0.0015):} = max{(0.26*'.$f3->_cfctm.'/'.$f3->_rfy.'),(0.0015):} = %%', 'Minimális húzott vashányad');
        $blc->def('AsMin', $f3->_rhoMin*1000*$f3->_d, 'A_(s,min) = rho_(min)*1000[mm]*d = %% [(mm^2)/m]', 'Előírt minimális húzott vasmennyiség négyszög keresztmetszet esetén');
        $blc->txt('Maximális összes vasmennyiség:');
        $blc->def('AsMax', 0.04*1000*$f3->_h, 'A_(s,max) = 0.04*1000[mm]*h = %% [(mm^2)/m]', 'Összes hosszvasalás megengedett legnagyobb mennyisége négyszög keresztmetszetben');
        $blc->txt('Legnagyobb vastávolság');
        if ($f3->_h > 100) {
            $blc->def('sMax', min(2*$f3->_h, 300), 's_(max) = min{(2h),(300):} = %% [mm]', 'Fő vasalás max vastávolsága');
            if ($f3->_dir == 1) {
                $blc->def('sMax2', min(3 * $f3->_h, 400), 's_(max,2) = min{(3h),(400):} = %% [mm]', 'Mellék vasalás max vastávolsága');
            }
        } else {
            $blc->txt('`h le 100 [mm]`');
            $blc->def('sMax', 200, 's_(max) = %% [mm]', 'Fő vasalás max vastávolsága');
            if ($f3->_dir == 1) {
                $blc->def('sMax2', 300, 's_(max,2) = %% [mm]', 'Mellék vasalás max vastávolsága');
            }
        }
        $blc->note('Kétirányban teherhordó lemez esetén mindkét irányban a fő vasalásra jellemző értéket kell figyelembe venni.');
        $blc->txt('Legnagyobb vasátmérő:');
        $blc->def('phiMax', $f3->_h/10, 'phi_(max) = h/10 = %% [mm]');

        $blc->region0('more0', 'További megjegyzések');
        $blc->h2('Vasalás támaszok környezetében');
        $blc->md('
A méretezett alsó mezővasalás legalább 50%-át a támaszig kell vezetni és ott megfe|elóen le kell horgonyozni.

A felső vasalást:

+ szélső, nem befogott támasz esetén a szélsó mezőnyomaték 15%-ára kell méretezni,és a mező 0,l-szeres hosszáig be kell vezetni,
+ belső támasz esetén a szomszédos mezőnyomatékok nagyobbikának legalább 25%-ára kell méretezni, és mindkét mező minimum 0.2-szeres hosszán végig kell vezetni.
        
Sarkainál felemelkedésben gátolt, kétirányban teherhordó lemezek sarkainál a csavarónyomatékok felvételére kétirányú felső vasalást kell tervezni, amelynek intenzitása pontosabb számítás hiányában megegyezik a rövidebb irányban futó alsó vasaláséval, és mindkét irányban a megfelelő támaszköz 0,2-szereséig be kell vezetni.');

        $blc->h3('Konzollemez, szabad lemezszél');
        $blc->img('https://structure.hu/ecc/slab0.jpg', 'Konzollemez, szabad lemezszél');
        $blc->region1('more0');
        $blc->h1('Pontokon megtámasztott síklemez födémek átlyukadása', 'Átszúródási vizsgálatok');
        $blc->note('[Vasbeton szerkezetek 6.8 (49.o)]');
        $blc->numeric('c_a', ['c_a', 'Pillér méret egyik dimenziója'], 40, 'cm');
        $blc->numeric('c_b', ['c_b', 'Pillér méret másik dimenziója'], 40, 'cm');
        $blc->boo('c_o', 'Körpillér', 0, '$c_(phi) = max(c_a, c_b)$');
        if ($f3->_c_o) {
            $f3->_c_a = max($f3->_c_a, $f3->_c_b);
            $f3->_c_b = max($f3->_c_a, $f3->_c_b);
        }

        $blc->h2('Nyíróerők központos reakcióerő esetében');
        $blc->input('VEd', ['V_(Ed)', 'Központos reakcióerő'], 500, 'kN', 'Lemez alatti és feletti oszlop normálerejének különbsége');
        $betas = [
          'Sarok oszlop 1.5' => 1.5,
          'Szélső oszlop 1.4' => 1.4,
          'Közbenső oszlop 1.15' => 1.15,
        ];
        $blc->lst('beta', $betas,  ['beta', 'Teher- és megtámasztás bizonytalansági tényező'], 1.5, 'Számításba nem vett hajéítónyomaték hatása');

        $blc->region0('piller0', 'Pillérkiosztás szerkesztési szabályainak ellenőrzése');
            $blc->txt('**Az ellenőrzés *x* és *y* irányban is lefuttatandó!**');
            $blc->numeric('l_1', ['l_1', 'Pillérköz 1'], 6, 'm');
            $blc->numeric('l_2', ['l_2', 'Szomszédos pillérköz'], 5, 'm');
            $blc->numeric('l_c', ['l_c', 'Lemezszél és szélső pillér távolsága'], 0.5, 'm', 'Lemez konzolhossz');
            $blc->math('0.8 < (l_i/l_(i+1)='.$f3->_l_1/$f3->_l_2.') < 1.25', 'Szabvány szerinti feltétel');
            if (0.8 <= $f3->_l_1/$f3->_l_2 && $f3->_l_1/$f3->_l_2 <= 1.25) {
                $blc->label('yes', 'ok');
            } else {
                $blc->label('no', 'nem ok');
            }
            $blc->md('Konzol hossz javaslat:');
            $blc->math('0.2*l_1 < l_c < 0.4*l_1');
            if (0.2*$f3->_l_1 <= $f3->_l_c && $f3->_l_c <= 0.4*$f3->_l_1) {
                $blc->label('yes', 'ok');
            } else {
                $blc->label('no', 'nem ok');
            }
            $blc->note('Javaslat [Világos kék 6.8 49.o] alapján. Nem teljesülés esetén peremgerenda tervezése javasolt, vagy tényleges nyomatékok figyelembevételével kell az átlyukadást ellenőrizni!');
        $blc->region1('piller0');

        $f3->_d = $f3->_d/10;
        if ($f3->_l_c*100 <= 2*$f3->_d + max($f3->_c_a, $f3->_c_b)/2 && $f3->_beta == 1.5 && $f3->_c_o == 0) {
            $blc->txt('Sarok négyszögpillér, kisebb konzol esete:', '$l_c = '.$f3->_l_c.' [m]$');
            $blc->def('u', $f3->_c_a + $f3->_c_b + 4*$f3->_d, 'u = c_a + c_b + 4*d= %% [cm]');
        } elseif ($f3->_l_c*100 <= 2*$f3->_d + $f3->_c_a/2 && $f3->_beta == 1.5 && $f3->_c_o == 1) {
            $blc->txt('Sarok körpillér, kisebb konzol esete:');
            $blc->def('u', 2*(2*$f3->_d + 0.5*$f3->_c_a)*pi()*0.5, 'u = 0.5*2*(2*d + 0.5*c_(phi))*pi = %% [cm]', 'Közelítés kerület felével');
        } elseif ($f3->_l_c*100 <= 2*$f3->_d + max($f3->_c_a, $f3->_c_b)/2 && $f3->_beta == 1.4 && $f3->_c_o == 0) {
            $blc->txt('Szélső négyszögpillér, kisebb konzol esete:');
            $blc->def('u', 2*min($f3->_c_a, $f3->_c_b) + max($f3->_c_a, $f3->_c_b) + 8*$f3->_d, 'u = 2*min(c_a, c_b) + max(c_a, c_b) + 8*d = %% [cm]');
        } elseif ($f3->_l_c*100 <= 2*$f3->_d + $f3->_c_a/2 && $f3->_beta == 1.4 && $f3->_c_o == 1) {
            $blc->txt('Szélső körpillér, kisebb konzol esete:');
            $blc->def('u', 2*(2*$f3->_d + 0.5*$f3->_c_a)*pi()*0.5, 'u = 0.5*2*(2*d + 0.5*c_(phi))*pi = %% [cm]', 'Közelítés kerület felével');
        } elseif ($f3->_beta == 1.15 && $f3->_c_o == 1) {
            $blc->txt('Közbenső körpillér vagy kellően nagy konzol esete:');
            $blc->def('u', 2*(2*$f3->_d + 0.5*$f3->_c_a)*pi(), 'u = 2*(2*d + 0.5*c_(phi))*pi = %% [cm]');
        } else {
            $blc->txt('Közbenső négyszögpillér vagy kellően nagy konzol esete:');
            $blc->def('u', 2*$f3->_c_a + 2*$f3->_c_b + 16*$f3->_d, 'u = 2*c_a + 2*c_b + 16*d = %% [cm]');
        }

        $blc->write('vendor/resist/ecc-calculations/canvas/slab0.jpg', []);

        $blc->def('vEd', ($f3->_beta*$f3->_VEd)/($f3->_u*$f3->_d)*10000, 'v_(Ed) = (beta*V_(Ed))/(u*d) = %% [(kN)/m^2]');

        $blc->h2('Beton teherbírásának ellenőrzése ');
        $blc->txt('`TODO`');
        $blc->hr();

        $blc->h1('Kétirányban teherhordó lemez képlékeny igénybevétele');
        $blc->numeric('p_Ed', ['p_Ed', 'Felületen megoszló teher'], 5, 'kN/m²', '');
        $blc->numeric('l_x', ['l_x', 'Lemez szélesség x irányban'], 10, 'm', '');
        $blc->numeric('l_y', ['l_y', 'Lemez szélesség x irányban'], 10, 'm', '');
        
        if ($f3->_l_x/$f3->_l_y <= 2) {
            $blc->label('yes', 'Két irányban teherhordó lemez');
        } else {
            $blc->label('no', 'Nem két irányban teherhordó lemez');
        }

        $etaOpt = 0.5*($f3->_l_y/$f3->_l_x)*($f3->_l_y/$f3->_l_x);
        $eta = array(
            'η optimális minimális vasmennyiség: '. $etaOpt => $etaOpt,
            'η 45°'=> 0.5*($f3->_l_y/$f3->_l_x),
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
        $f3->_eta = 0.1;
        $blc->lst('eta', $eta, 'Töréskép');
        if(!$f3->_eta) {
            $f3->_eta = $etaOpt;
        }
        $blc->math('eta = '. $f3->_eta);

        $blc->region0('r0');
        $blc->def('p_y', \H3::n2((1-(4/3)*$f3->_eta)*$f3->_p_Ed), 'p_y = %% [(kN)/m^2]');
        $blc->def('p_x', \H3::n2(((4/3)*$f3->_eta)*$f3->_p_Ed), 'p_x = %% [(kN)/m^2]');
        $blc->def('p_check', \H3::n2($f3->_p_x + $f3->_p_y), 'p_x + p_y = %% [(kN)/m^2]');
        $blc->region1('r0');

        $blc->def('m_y', \H3::n2(($f3->_p_y * $f3->_l_y * $f3->_l_y)/8), 'm_y = (p_y * l_y^2)/8 = %% [(kNm)/m]', '1 m széles lemezsávra jutó nyomaték');
        $blc->def('m_x', \H3::n2(($f3->_p_x * $f3->_l_x * $f3->_l_x)/8), 'm_x = (p_x * l_x^2)/8 = %% [(kNm)/m]', '1 m széles lemezsávra jutó nyomaték');
        $blc->def('m_xyA', \H3::n2($f3->_m_y), 'm_(xyA) := %% [(kNm)/m]', 'Sarkok gátolt felemelkedéséből származó csvarónyomaték');

        if ($f3->_m_y >= $f3->_m_x) {
            $blc->label('yes', '$m_y >= m_x$');
        } else {
            $blc->label('no', '$m_y < m_x$');
        }
    }
}
