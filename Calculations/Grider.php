<?php

namespace Calculation;

Class Grider extends \Ecc
{

    /**
     * @var $f3 \Base
     * @var $blc \Blc
     * @var $ec \Ec\Ec
     * @throws \Exception
     */
    public function calc(object $f3, object $blc, object $ec): void
    {
        $blc->note('KG számításai alapján.');
        $blc->region0('mat', 'Anyagminőségek');
            $ec->matList('cmat', 'C40/50', 'Beton:');
            $ec->saveMaterialData($f3->_cmat, 'c');
            $blc->def('gamma', 25, 'gamma := %% [(kN)/m^3]', 'Beton térfogatsúlya');
            $ec->matList('rmat', 'B500', 'Lágyvas');
            $ec->saveMaterialData($f3->_rmat, 'r');
            $blc->input('pmat', 'Feszítőbetét jele', 'Fp100-R2', '', '');
        $blc->region1('mat');

        $blc->region0('geometry', 'Geometria');
            $griderTypes = ['Párhuzamos övű' => 0, 'Lejtett felső övű' => 1];
            $blc->lst('griderType', $griderTypes, 'Tartó típus', 0, '');
            $f3->_1l = 0;
            if ($f3->_griderType == 1) {
                $blc->numeric('1l', ['', 'Övek közti lejtés'], 3, '%', 'Középtől szélek felé egyenletesen süllyedő lejtés felső övön');
            }
            $blc->numeric('l', ['l', 'Tartó teljes hossza'], 12.0, 'm', '');
            $griderSections = ['Négyszög' => 'r', 'T' => 't', 'I' => 'i'];
            $blc->lst('griderSection', $griderSections, 'Tartó keresztmetszet', 't', '');
            $blc->numeric('h', ['h', 'Tartó teljes magassága'], 900, 'mm', 'Középső keresztmetszetben');
            if ($f3->_griderSection == 't' || $f3->_griderSection == 'i') {
                $blc->numeric('b_ft', ['b_(ft)', 'Fejlemez szélessége'], 500, 'mm', '');
                $blc->numeric('h_ft', ['h_(ft)', 'Fejlemez magassága'], 150, 'mm', '');
                $blc->numeric('h_ht', ['h_(ht)', 'Fejkiékelés magassága'], 30, 'mm', '');
            } else {
                $f3->_b_ft = 0;
                $f3->_h_ft = 0;
                $f3->_h_ht = 0;
            }

            if ($f3->_griderSection == 'i') {
                $blc->numeric('b_fb', ['b_(fb)', 'Alsó öv szélessége'], 400, 'mm', '');
                $blc->numeric('h_fb', ['h_(fb)', 'Alsó öv magassága'], 300, 'mm', '');
                $blc->numeric('h_hb', ['h_(hb)', 'Alsó öv kiékelés magassága'], 30, 'mm', '');
            } else {
                $f3->_b_fb = 0;
                $f3->_h_fb = 0;
                $f3->_h_hb = 0;
            }
            $blc->numeric('b_w', ['b_w', 'Gerincvastagság'], 160, 'mm', '');
            $blc->def('h_w', $f3->_h-($f3->_h_ft + $f3->_h_fb), 'h_w = %% [mm]', 'Gerinc magasság');
            $Act = $f3->_b_ft*$f3->_h_ft;
            $Acb = $f3->_b_fb*$f3->_h_fb;
            $Acw = $f3->_h_w*$f3->_b_w;
            $Acwt = (($f3->_b_ft - $f3->_b_w)/2)*$f3->_h_ht;
            $Acwb = (($f3->_b_fb - $f3->_b_w)/2)*$f3->_h_hb;
            $blc->def('A_c', $Act + $Acb + $Acw + $Acwt + $Acwb, 'A_c = %% [mm^2]', 'Keresztmetszeti terület');

            if ($f3->_griderType == 1) {
                $blc->def('h_0', $f3->_h - $f3->_l*1000*0.5*$f3->_1l/100, 'h_0 = %% [mm]', 'Lejtett tartó végső keresztmetszeti magassága');
                $blc->def('h_w0', $f3->_h_0 -($f3->_h_ft + $f3->_h_fb), 'h_(w,0) = %% [mm]', 'Lejtett tartó végső keresztmetszeti gerinc magassága');
                $Acw0 = $f3->_h_w0*$f3->_b_w;
                $blc->def('A_c0', $Act + $Acb + $Acw0 + $Acwt + $Acwb, 'A_(c,0) = %% [mm^2]', 'Lejtett tartó végső keresztmetszeti terület');
            } else {
                $f3->_A_c0 = $f3->_A_c;
            }
            $G = \H3::n2(($f3->_A_c + $f3->_A_c0)/2*$f3->_l*$f3->_gamma/1000000);
            $blc->def('G', $G, 'G = %% [kN]', 'Tartó súlya, $G = '.\H3::n2($G/10) .' [t]$');
            $blc->numeric('b', ['b', 'Terhelő mező szélessége, gerenda osztás'], 4, 'm', 'Szelemenes rendszer esetén a szelemenek terhelt szelemenszakaszok összes hossza a két oldalon');
        $blc->region1('geometry');

        // =============================================================================================================
        $blc->h1('Teher számítás');
        $blc->boo('makeEd', 'Mértékadó terhek kézi megadása', false);
        if ($f3->_makeEd) {
            $blc->numeric('MEd', ['M_(Ed)', 'Mértékadó nyomaték tervezési értéke tartóközépen'], 100, 'kNm', '');
            $blc->numeric('VEd', ['V_(Ed)', 'Mértékadó nyíróerő tervezési értéke tartóvégen'], 100, 'kN', '');
        } else {
            $blc->h2('Egyenletesen megoszló terhek', 'Trapézlemezről átadva');
            $blc->numeric('m', ['m', 'Teherátadási módosító tényező'], 1.15, '', 'Trapézlemez többtámaszú hatása, közbenső támasznál; 1-től 1.25-ig');
            $blc->h3('Önsúly terhek');
            $blc->math('gamma_G = ' . $f3->__GG);
            $blc->def('gd', \H3::n2(((2 * $f3->_A_c + $f3->_A_c0) / 3 * ($f3->_gamma / 1000000)) * $f3->__GG), 'g_d = ((2A_c + A_(c,0))/3*gamma)*gamma_G = %% [(kN)/m]', 'Súlyozott vonalmenti átlagsúly tervezési értéke 1/3 hossznál lévő keresztmetszettel');
            $blc->numeric('glk', ['g_(l,k)', 'Rétegrend karakterisztikus felületi terhe'], 0.6, 'kN/m2');
            $blc->def('pld', \H3::n2($f3->_glk * $f3->_b * $f3->__GG * $f3->_m), 'p_(l,d) = g_(l,k)*b*gamma_G*m = %% [(kN)/m]', 'Vonalmenti teher tervezési értéke');
            $blc->h3('Installációs és egyéb terhek');
            $psi0i = 1;
            $blc->math('gamma_Q = ' . $f3->__GQ . '%%% Psi_(0,i) = ' . $psi0i);
            $blc->numeric('qik', ['q_(i,k)', 'Installációs felületi teher karakterisztikus értéke'], 0.5, 'kN/m2');
            $blc->def('pid', \H3::n2($f3->_qik * $f3->_b * $f3->__GQ * $f3->_m * $psi0i), 'p_(i,d) = q_(i,k)*b*gamma_Q*m*Psi_(0,i) = %% [(kN)/m]', 'Vonalmenti teher tervezési értéke');
            $blc->h3('Szél nyomásból adódó teher');
            $psi0w = 0.6;
            $blc->math('gamma_Q = ' . $f3->__GQ . '%%% Psi_(0,w) = ' . $psi0w);
            $blc->numeric('qwk', ['q_(w,k)', 'Szél felületi teher karakterisztikus értéke'], 0.4, 'kN/m2', 'Torlónyomásból, belső szélnyomással, szélnyomáshoz ( **I** ) zóna');
            $blc->def('pwd', \H3::n2($f3->_qwk * $f3->_b * $f3->__GQ * $f3->_m * $psi0w), 'p_(w,d) = q_(w,k)*b*gamma_Q*m*Psi_(0,w) = %% [(kN)/m]', 'Vonalmenti teher tervezési értéke');
            $blc->h3('Hó/hózug terhe', 'Kiemelt teher');
            $blc->math('gamma_Q = ' . $f3->__GQ);
            $blc->numeric('qsk', ['q_(s,k)', 'Hó/hózug felületi teher karakterisztikus értéke'], 1, 'kN/m2', '');
            $blc->def('psd', \H3::n2($f3->_qsk * $f3->_b * $f3->__GQ * $f3->_m), 'p_(s,d) = q_(w,k)*b*gamma_Q*m = %% [(kN)/m]', 'Vonalmenti teher tervezési értéke');
            $blc->h3('Mértékadó megoszló teher');
            $blc->def('pd', $f3->_gd + $f3->_pld + $f3->_pid + $f3->_pwd + $f3->_psd, 'p_d = g_d + p_(l,d) + p_(i,d) + p_(w,d) + p_(s,d) = %% [(kN)/m]', 'Vonalmenti tervezési érték');
            $blc->h2('Koncentrált terhek');
            $blc->boo('makeP', 'Fenti megoszló terhek másodlagos tartóra hatnak először', false, 'A másodlagos tartók koncentrált erőként jelennek meg');
            $Mq = ($f3->_pd * $f3->_l * $f3->_l) / 8;
            $Vq = $f3->_pd * ($f3->_l / 2);
            if ($f3->_makeP) {
                $blc->lst('QPart', ['2' => 2, '3' => 3, '4' => 4, '5' => 5, '6' => 6], 'Tartó felosztása részekre', 2);
                $blc->numeric('Acp', ['A_(c,p)', 'Másodlagos tartók keresztmetszeti területe'], 160000, 'mm2', 'Önsúly számításhoz');
                $blc->def('Gpd', \H3::n2(($f3->_Acp / 1000000) * $f3->_b * $f3->_gamma * $f3->__GG), 'G_(p,d) = 2*(A_(c,p)*b/2)*gamma*gamma_G = %% [kN]', 'Másodlagos tartók önsúlyából származó koncentrált erő tervezési értéke. Főtartóra mindkét oldalról támaszkodik másodlagos tartó.');
                $blc->txt('Tartóra megoszló önsúly hat ( $g_d = ' . $f3->_gd . ' [(kN)/m]$ ), egyedi koncentrált erő ( $P_1$ ), szelemen önsúlyból származó koncentrált erő ( $G_(p,d)$ ) és szétosztott koncentrált erő ( $Q_d$ ) (installációból, rétegrendről, hóból, szélből).');
                $Mq = ($f3->_gd * $f3->_l * $f3->_l) / 8;
                $Vq = $f3->_gd * ($f3->_l / 2);
                $blc->def('Qtot', ($f3->_pd - $f3->_gd) * $f3->_l, 'Q_(t ot) = (p_d - g_d)*l = %% [kN]', 'Másodlagos tartók által gyűjtött erő főtartó terhelési mezőjében');
//            $blc->def('PPartMode', 'Főtartók végein is van másodlagos tartó', true, '$P_(tot)$ erő szétosztásának módja');
                $blc->txt('Főtartók végein is van másodlagos tartó.');
                $blc->def('Qd', \H3::n2($f3->_Qtot / $f3->_QPart + $f3->_Gpd), 'Q_d = Q_(t ot)/' . $f3->_QPart . ' + G_(p,d) = %% [kN]', 'Másodlagos tartók által leadott koncentrált erő');
                $blc->hr();
            }
//        $blc->numeric('P1', ['P_1', 'Egyedi koncentrált teher **tervezési** értéke'], 0, 'kN', '');
//        $blc->numeric('l1', ['l_1', 'Egyedi koncentrált teher távolsága tartó végtől'], 1, 'm');

            $blc->h2('Mértékadó igénybevételek');
            $blc->def('MEdq', $Mq, 'M_(Ed,q) = %% [kNm]', 'Nyomaték tartóközépen, tartóra ható megoszló terhelésből');
            $blc->def('VEdq', $Vq, 'V_(Ed,q) = %% [kN]', 'Nyíróerő tartóvégen, tartóra ható megoszló terhelésből');
            $MQ = 0;
            $VQ = 0;
            if ($f3->_makeP) {
                switch ($f3->_QPart) {
                    case 2:
                        $MQ = $f3->_Qd * $f3->_l / 4;
                        $VQ = $f3->_Qd / 2;
                        break;
                    case 3:
                        $MQ = $f3->_Qd * $f3->_l / 3;
                        $VQ = $f3->_Qd;
                        break;
                    case 4:
                        $MQ = $f3->_Qd * $f3->_l / 2;
                        $VQ = 3 * $f3->_Qd / 2;
                        break;
                    case 5:
                        $MQ = $f3->_Qd * $f3->_l * 3 / 5;
                        $VQ = 2 * $f3->_Qd;
                        break;
                    case 6:
                        $MQ = $f3->_Qd * $f3->_l * 3 / 4;
                        $VQ = $f3->_Qd * 5 / 2;
                        break;
                }
                $blc->def('MEdQ', $MQ, 'M_(Ed,Q) = %% [kNm]', 'Nyomaték tartóközépen, másodlagos tartók terheléséből');
                $blc->def('VEdQ', $VQ, 'V_(Ed,Q) = %% [kN]', 'Nyíróerő tartóvégen, másodlagos tartók terheléséből');
            }
//          $MP1 = $f3->_P1;
//          $blc->def('MEdP1', $MP1, 'M_(Ed,Q) = %% [kNm]', 'Nyomaték tartóközépen, másodlagos tartók terheléséből');
//          $blc->def('VEdP1', $VP1, 'V_(Ed,Q) = %% [kN]', 'Nyíróerő tartóvégen, másodlagos tartók terheléséből');
            $blc->success0('Ed');
                $blc->def('MEd', $MQ + $Mq, 'M_(Ed) = %% [kNm]', 'Nyomaték tartóközépen');
                $blc->def('VEd', $VQ + $Vq, 'V_(Ed) = %% [kN]', 'Nyíróerő tartóvégen');
            $blc->success1('Ed');
        }

        // =============================================================================================================
        $blc->h1('Közelítő méretfelvétel');
        $blc->note('@AR');
        $blc->def('prenp', ceil((($f3->_MEd*1000000)/(0.8*$f3->_h*0.8*$f3->_rfyd))/314), 'n_(phi 20) = ceil(((M_(Ed)*10^6)/(0.8*h*0.8*f_(yd)))/A_(phi 20)) = %%', 'Húzott pászma és lágyvas száma összesen');
        $blc->note('$0.8h$ hatékony magasság közelítése. $0.8f_(yd)$ repedés tágasság "biztosítására".');

        // =============================================================================================================
        $blc->h1('Keresztmetszet ellenőrzése');
        $blc->h2('Középső keresztmetszet ellenőrzése hajlításra');
    }
}
