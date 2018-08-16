<?php

namespace Calculation;

Class Slab extends \Ecc
{

    public function calc($f3)
    {
        $ec = \Ec::instance();
        $blc = \Blc::instance();

        $ec->matList('mat', 'C25/30', 'Beton anyagminőség');
        $blc->input('d', 'Lemez hatékony vastagsága', '20', 'cm');

        $blc->h1('Pontokon megtámasztott síklemez födémek átlyukadása', 'Átszúródási vizsgálatok');
        $blc->note('[Világos kék 6.8 49.o]');
        $blc->input('c_a', 'Pillér méret egyik dimenziója', '40', 'cm');
        $blc->input('c_b', 'Pillér méret másik dimenziója', '40', 'cm');
        $blc->boo('c_o', 'Körpillér', 0, '\`c_(phi) = max(c_a, c_b)\`');
        if ($f3->_c_o) {
            $f3->_c_a = max($f3->_c_a, $f3->_c_b);
            $f3->_c_b = max($f3->_c_a, $f3->_c_b);
        }

        $blc->h2('Nyíróerők központos reakcióerő esetében');
        $blc->input('VEd', '`V_(Ed)`: Központos reakcióerő', '500', 'kN', 'Lemez alatti és feletti oszlop normálerejének különbsége');
        $betas = [
          'Sarok oszlop 1.5' => 1.5,
          'Szélső oszlop 1.4' => 1.4,
          'Közbenső oszlop 1.15' => 1.15,
        ];
        $blc->lst('beta', $betas, '`beta:` Teher- és megtámasztás bizonytalansági tényező', '1.5', 'Számításba nem vett hajéítónyomaték hatása');

        $blc->region0('piller0', 'Pillérkiosztás szerkesztési szabályainak ellenőrzése');
            $blc->md('Az ellenőrzés *x* és *y* irányban is lefuttatandó!');
            $blc->input('l_1', 'Pillérköz 1', '6', 'm');
            $blc->input('l_2', 'Szomszédos pillérköz', '5', 'm');
            $blc->input('l_c', 'Lemezszél és szélső pillér távolsága', '0.5', 'm', 'Lemez konzolhossz');
            $blc->math('0.8 < l_i/l_(i+1)='.$f3->_l_1/$f3->_l_2.' < 1.25', 'Szabvány szerinti feltétel');
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

        if ($f3->_l_c*100 <= 2*$f3->_d + max($f3->_c_a, $f3->_c_b)/2 && $f3->_beta == 1.5 && $f3->_c_o == 0) {
            $blc->txt('Sarok négyszögpillér, kisebb konzol esete:', '\`l_c = '.$f3->_l_c.'\ [m]`');
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

        $blc->hr();

        $blc->h1('Kétirányban teherhordó lemez képlékeny igénybevétele');
        $blc->input('p_Ed', '`p_(Ed)`: Felületen megoszló teher', '5', 'kN/m²', '');
        $blc->input('l_x', 'Lemez szélesség x irányban', '10', 'm', '');
        $blc->input('l_y', 'Lemez szélesség x irányban', '10', 'm', '');
        
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
        $blc->def('p_y', (1-(4/3)*$f3->_eta)*$f3->_p_Ed, 'p_y = %% [(kN)/m^2]');
        $blc->def('p_x', ((4/3)*$f3->_eta)*$f3->_p_Ed, 'p_x = %% [(kN)/m^2]');
        $blc->def('p_check', $f3->_p_x + $f3->_p_y, 'p_x + p_y = %% [(kN)/m^2]');
        $blc->region1('r0');

        $blc->def('m_y', ($f3->_p_y * $f3->_l_y * $f3->_l_y)/8, 'm_y = (p_y * l_y^2)/8 = %% [(kNm)/m]', '1 m széles lemezsávra jutó nyomaték');
        $blc->def('m_x', ($f3->_p_x * $f3->_l_x * $f3->_l_x)/8, 'm_x = (p_x * l_x^2)/8 = %% [(kNm)/m]', '1 m széles lemezsávra jutó nyomaték');
        $blc->def('m_xyA', $f3->_m_y, 'm_(xyA) := %% [(kNm)/m]', 'Sarkok gátolt felemelkedéséből származó csvarónyomaték');

        if ($f3->_m_y >= $f3->_m_x) {
            $blc->label('yes', '\`m_y >= m_x\`');
        } else {
            $blc->label('no', '\`m_y < m_x\`');
        }
        $blc->note('Test note');
    }
}
