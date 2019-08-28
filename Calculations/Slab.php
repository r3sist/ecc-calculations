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
        $ec->wrapRebarCount('sx', 'fix', ['s_x \ \ phi_x', 'x irány vastávolság és átmérő'], 200, 12, '');
        $ec->wrapRebarCount('sy', 'fiy', ['s_y \ \ phi_y', 'y irány vastávolság és átmérő'], 200, 12, '');
        $blc->numeric('c', ['c', 'Alsó betonfedés'], 30, 'mm');
        $blc->def('d', $f3->_h - $f3->_c - ($f3->_fix + $f3->_fiy)/2, 'd = h - c - (phi_x + phi_y)/2 = %% [mm]', 'Hatékony magasság');


        $blc->h1('Pontokon megtámasztott síklemez födémek átlyukadása', 'Átszúródási vizsgálatok');
        $blc->note('[Vasbeton szerkezetek 6.8 (49.o)]');
        $blc->numeric('acol', ['a_(col)', 'Pillér méret egyik dimenziója'], 400, 'mm');
        $blc->numeric('bcol', ['b_(col)', 'Pillér méret másik dimenziója'], 400, 'mm');
        $blc->boo('colO', 'Körpillér', false, '');
        if ($f3->_colO) {
            $blc->txt('$phi_(col) = max(a_(col), b_(col))$');
            $f3->_acol = max($f3->_acol, $f3->_bcol);
            $f3->_bcol = $f3->_acol;
        }

        $blc->h2('Nyíróerők központos reakcióerő esetében');
        $blc->input('VEd', ['V_(Ed)', 'Központos reakcióerő'], 500, 'kN', 'Lemez alatti és feletti oszlop normálerejének különbsége');
        $betas = [
          'Sarok oszlop 1.5' => 1.5,
          'Szélső oszlop 1.4' => 1.4,
          'Közbenső oszlop 1.15' => 1.15,
        ];
        $blc->lst('beta', $betas,  ['beta', 'Teher- és megtámasztás bizonytalansági tényező'], 1.5, 'Számításba nem vett hajéítónyomaték hatása');
        $blc->numeric('lc', ['l_c', 'Lemezszél és szélső pillér távolsága'], 500, 'mm', 'Lemez konzolhossz');

        $blc->region0('piller0', 'Pillérkiosztás szerkesztési szabályainak ellenőrzése');
            $blc->txt('**Az ellenőrzés *x* és *y* irányban is lefuttatandó!**');
            $blc->numeric('l1', ['l_1', 'Pillérköz 1'], 6000, 'mm');
            $blc->numeric('l2', ['l_2', 'Szomszédos pillérköz'], 5000, 'mm');
            $blc->math('0.8 < (l_i/l_(i+1)='.\H3::n2($f3->_l1/$f3->_l2).') < 1.25', 'Szabvány szerinti feltétel');
            if (0.8 <= $f3->_l1/$f3->_l2 && $f3->_l1/$f3->_l2 <= 1.25) {
                $blc->label('yes', 'ok');
            } else {
                $blc->label('no', 'nem ok');
            }
            $blc->md('Konzol hossz javaslat:');
            $blc->math('0.2*l_1 < l_c < 0.4*l_1');
            if (0.2*$f3->_l1 <= $f3->_lc && $f3->_lc <= 0.4*$f3->_l1) {
                $blc->label('yes', 'ok');
            } else {
                $blc->label('no', 'nem ok');
            }
            $blc->note('Javaslat [Vasbeton szerkezetek (2016) 6.8 49.o.] alapján. Nem teljesülés esetén peremgerenda tervezése javasolt, vagy tényleges nyomatékok figyelembevételével kell az átlyukadást ellenőrizni! ß számítható pontosan');
        $blc->region1('piller0');

        if ($f3->_lc <= 2*$f3->_d + max($f3->_acol, $f3->_bcol)/2 && $f3->_beta == 1.5 && $f3->_colO == false) {
            $blc->txt('**Sarok négyszögpillér, kisebb konzol esete:**', '$l_c = '.$f3->_lc.' [m]$');
            $blc->def('u', $f3->_acol + $f3->_bcol + 4*$f3->_d, 'u = a_(col) + b_(col) + 4*d= %% [mm]');
        } elseif ($f3->_lc <= 2*$f3->_d + $f3->_acol/2 && $f3->_beta == 1.5 && $f3->_colO == true) {
            $blc->txt('**Sarok körpillér, kisebb konzol esete:**');
            $blc->def('u', 2*(2*$f3->_d + 0.5*$f3->_acol)*pi()*0.5, 'u = 0.5*2*(2*d + 0.5*phi_(col))*pi = %% [mm]', 'Közelítés kerület felével');
        } elseif ($f3->_lc <= 2*$f3->_d + max($f3->_acol, $f3->_bcol)/2 && $f3->_beta == 1.4 && $f3->_colO == false) {
            $blc->txt('**Szélső négyszögpillér, kisebb konzol esete:**');
            $blc->def('u', 2*min($f3->_acol, $f3->_bcol) + max($f3->_acol, $f3->_bcol) + 8*$f3->_d, 'u = 2*min(a_(col),b_(col)) + max(a_(col),b_(col)) + 8*d = %% [mm]');
        } elseif ($f3->_lc*100 <= 2*$f3->_d + $f3->_acol/2 && $f3->_beta == 1.4 && $f3->_colO == true) {
            $blc->txt('**Szélső körpillér, kisebb konzol esete:**');
            $blc->def('u', 2*(2*$f3->_d + 0.5*$f3->_acol)*pi()*0.5, 'u = 0.5*2*(2*d + 0.5*phi_(col))*pi = %% [mm]', 'Közelítés kerület felével');
        } elseif ($f3->_beta == 1.15 && $f3->_colO == true) {
            $blc->txt('**Közbenső körpillér vagy kellően nagy konzol esete:**');
            $blc->def('u', 2*(2*$f3->_d + 0.5*$f3->_acol)*pi(), 'u = 2*(2*d + 0.5*phi_(col))*pi = %% [mm]');
        } else {
            $blc->txt('**Közbenső négyszögpillér vagy kellően nagy konzol esete:**');
            $blc->def('u', 2*$f3->_acol + 2*$f3->_bcol + 16*$f3->_d, 'u = 2*a_(col) + 2*b_(col) + 16*d = %% [mm]');
        }

        $blc->write('vendor/resist/ecc-calculations/canvas/slab0.jpg', []);

        $blc->def('vEd', ($f3->_beta*$f3->_VEd)/($f3->_u*$f3->_d)*10000, 'v_(Ed) = (beta*V_(Ed))/(u*d) = %% [(kN)/m^2]');

        $blc->h2('Beton teherbírásának ellenőrzése', 'Ferde nyomásra $u_0$ kerület mentén');
        switch ($f3->_beta) {
            case 1.15:
                if ($f3->_colO == true) {
                    $blc->def('u0', \H3::n0(2*$f3->_acol*0.5*pi()), 'u_0 = 2*(phi_(col)/2)*pi = %% [mm]');
                } else {
                    $blc->def('u0', \H3::n0(2*$f3->_acol + 2*$f3->_bcol), 'u_0 = 2*a_(col) + 2*b_(col) = %% [mm]');
                }
                break;
            case 1.4:
                // TODO itt tartok
                $blc->def('u0', \H3::n0(min(1, 1)), 'u_0 = 2*a_(col) + 2*b_(col) = %% [mm]');
                break;
        }
        $blc->def('vRdmax', 0.5*(0.6*(1 - $f3->_cfck/250))*$f3->_cfcd, 'v_(Rd,max) = 0.5*0.6*(1-f_(ck)/250)*f_(cd) = %% [N/(mm^2)]');
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
    }
}
