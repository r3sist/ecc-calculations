<?php declare(strict_types = 1);
// PC pretensioned beam analysis according to Eurocodes - Calculation class for ECC framework
// (c) Bence VÁNKOS | https://structure.hu | https://github.com/r3sist/ecc-calculations

namespace Calculation;

use \Base;
use \Ecc\Blc;
use \Ec\Ec;
use \H3;
use \resist\SVG\SVG;

Class Girder
{
    public function calc(Base $f3, Blc $blc, Ec $ec): void
    {
        $blc->note('KG számításai alapján.');
        $blc->region0('mat', 'Anyagminőségek');
            $ec->matList('cmat', 'C40/50', ['', 'Beton']);
            $ec->spreadMaterialData($f3->_cmat, 'c');
            $blc->def('gamma', 25, 'gamma := %% [(kN)/m^3]', 'Beton térfogatsúlya');
            $ec->matList('rmat', 'B500', ['', 'Lágyvas']);
            $ec->spreadMaterialData($f3->_rmat, 'r');
            $blc->input('pmat', ['', 'Feszítőbetét jele'], 'Fp100-R2', '', '');
        $blc->region1();

        $blc->region0('geometry', 'Geometria');
            $griderTypes = ['Párhuzamos övű' => 0, 'Lejtett felső övű' => 1];
            $blc->lst('griderType', $griderTypes, ['', 'Tartó típus'], '', '');
            $f3->_1l = 0;
            if ($f3->_griderType == 1) {
                $blc->numeric('1l', ['', 'Övek közti lejtés'], 3, '%', 'Középtől szélek felé egyenletesen süllyedő lejtés felső övön');
            }
            $blc->numeric('l', ['l', 'Tartó teljes hossza'], 12.0, 'm', '');
            $griderSections = ['Négyszög' => 'r', 'T' => 't', 'I' => 'i'];
            $blc->lst('griderSection', $griderSections, ['', 'Tartó keresztmetszet'], 't', '');
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

            $blc->numeric('b_w', ['b_w', 'Gerincvastagság'], 160, 'mm', '');

            if ($f3->_griderSection == 'i') {
                $blc->numeric('b_fb', ['b_(fb)', 'Alsó öv szélessége'], 400, 'mm', '');
                $blc->numeric('h_fb', ['h_(fb)', 'Alsó öv magassága'], 300, 'mm', '');
                $blc->numeric('h_hb', ['h_(hb)', 'Alsó öv kiékelés magassága'], 30, 'mm', '');
            } else {
                $f3->_b_fb = $f3->_b_w;
                $f3->_h_fb = 0;
                $f3->_h_hb = 0;
            }

            $blc->def('h_w', $f3->_h-($f3->_h_ft + $f3->_h_fb), 'h_w = %% [mm]', 'Gerinc magasság');
            $blc->note('^ KG-nél T keresztmetszet esetén is van alsó öv');
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
            $G = H3::n2(($f3->_A_c + $f3->_A_c0)/2*$f3->_l*$f3->_gamma/1000000);
            $blc->def('G', $G, 'G = %% [kN]', 'Tartó súlya, $G = '. H3::n2($G/10) .' [t]$');
            $blc->numeric('b', ['b', 'Terhelő mező szélessége, gerenda osztás'], 4, 'm', 'Szelemenes rendszer esetén a szelemenek terhelt szelemenszakaszok összes hossza a két oldalon');
        $blc->region1();

        $r = min(400/$f3->_h, 600/max($f3->_b_ft, $f3->_b_fb, $f3->_b_w)); // ratio
        $svg = new SVG(600, 400);
        $svg->addLine(0, 0, $r*$f3->_b_ft, 0); // top line
        $svg->addLine(0, 0, 0, $r*$f3->_h_ft); // top flange left border
        $svg->addLine($r*$f3->_b_ft, 0, $r*$f3->_b_ft, $r*$f3->_h_ft); // top flange right border
        $svg->addLine(0, $r*$f3->_h_ft, $r*$f3->_b_ft/2-$r*$f3->_b_w/2, $r*$f3->_h_ft+$r*$f3->_h_ht);
        $svg->addLine($r*$f3->_b_ft, $r*$f3->_h_ft, $r*$f3->_b_ft/2+$r*$f3->_b_w/2, $r*$f3->_h_ft+$r*$f3->_h_ht);
        $svg->addLine($r*$f3->_b_ft/2+$r*$f3->_b_w/2, $r*$f3->_h_ft+$r*$f3->_h_ht, $r*$f3->_b_ft/2+$r*$f3->_b_w/2, $r*($f3->_h-$f3->_h_ft-$f3->_h_ht-$f3->_h_hb-$f3->_h_fb)); // web left
        $svg->addLine($r*$f3->_b_ft/2-$r*$f3->_b_w/2, $r*$f3->_h_ft+$r*$f3->_h_ht, $r*$f3->_b_ft/2-$r*$f3->_b_w/2, $r*($f3->_h-$f3->_h_ft-$f3->_h_ht-$f3->_h_hb-$f3->_h_fb)); // web right
//        $blc->svg($svg);
        unset($svg);

//        $blc->def('c', 40, 'c = %% [mm]', 'Betonfedés');
        $blc->numeric('c', ['c', 'Betonfedés'], 40, 'mm', 'Alul');

        // =============================================================================================================
        $blc->h1('Teher számítás');
        $blc->boo('makeEd', ['', 'Mértékadó terhek kézi megadása'], false);
        if ($f3->_makeEd) {
            $blc->numeric('MEd', ['M_(Ed)', 'Mértékadó nyomaték tervezési értéke tartóközépen'], 100, 'kNm', '');
            $blc->numeric('VEd', ['V_(Ed)', 'Mértékadó nyíróerő tervezési értéke tartóvégen'], 100, 'kN', '');
        } else {
            $blc->h2('Egyenletesen megoszló terhek', 'Trapézlemezről átadva');
            $blc->numeric('m', ['m', 'Teherátadási módosító tényező'], 1.15, '', 'Trapézlemez többtámaszú hatása, közbenső támasznál; 1-től 1.25-ig');
            $blc->h3('Önsúly terhek');
            $blc->math('gamma_G = ' . $f3->__GG);
            $blc->def('gd', H3::n2(((2 * $f3->_A_c + $f3->_A_c0) / 3 * ($f3->_gamma / 1000000)) * $f3->__GG), 'g_d = ((2A_c + A_(c,0))/3*gamma)*gamma_G = %% [(kN)/m]', 'Súlyozott vonalmenti átlagsúly tervezési értéke 1/3 hossznál lévő keresztmetszettel');
            $blc->numeric('glk', ['g_(l,k)', 'Rétegrend karakterisztikus felületi terhe'], 0.6, 'kN/m2');
            $blc->def('pld', H3::n2($f3->_glk * $f3->_b * $f3->__GG * $f3->_m), 'p_(l,d) = g_(l,k)*b*gamma_G*m = %% [(kN)/m]', 'Vonalmenti teher tervezési értéke');
            $blc->h3('Installációs és egyéb terhek');
            $psi0i = 1;
            $blc->math('gamma_Q = ' . $f3->__GQ . '%%% Psi_(0,i) = ' . $psi0i);
            $blc->numeric('qik', ['q_(i,k)', 'Installációs felületi teher karakterisztikus értéke'], 0.5, 'kN/m2');
            $blc->def('pid', H3::n2($f3->_qik * $f3->_b * $f3->__GQ * $f3->_m * $psi0i), 'p_(i,d) = q_(i,k)*b*gamma_Q*m*Psi_(0,i) = %% [(kN)/m]', 'Vonalmenti teher tervezési értéke');
            $blc->h3('Szél nyomásból adódó teher');
            $psi0w = 0.6;
            $blc->math('gamma_Q = ' . $f3->__GQ . '%%% Psi_(0,w) = ' . $psi0w);
            $blc->numeric('qwk', ['q_(w,k)', 'Szél felületi teher karakterisztikus értéke'], 0.4, 'kN/m2', 'Torlónyomásból, belső szélnyomással, szélnyomáshoz ( **I** ) zóna');
            $blc->def('pwd', H3::n2($f3->_qwk * $f3->_b * $f3->__GQ * $f3->_m * $psi0w), 'p_(w,d) = q_(w,k)*b*gamma_Q*m*Psi_(0,w) = %% [(kN)/m]', 'Vonalmenti teher tervezési értéke');
            $blc->h3('Hó/hózug terhe', 'Kiemelt teher');
            $blc->math('gamma_Q = ' . $f3->__GQ);
            $blc->numeric('qsk', ['q_(s,k)', 'Hó/hózug felületi teher karakterisztikus értéke'], 1, 'kN/m2', '');
            $blc->def('psd', H3::n2($f3->_qsk * $f3->_b * $f3->__GQ * $f3->_m), 'p_(s,d) = q_(w,k)*b*gamma_Q*m = %% [(kN)/m]', 'Vonalmenti teher tervezési értéke');
            $blc->h3('Mértékadó megoszló teher');
            $blc->def('pd', $f3->_gd + $f3->_pld + $f3->_pid + $f3->_pwd + $f3->_psd, 'p_d = g_d + p_(l,d) + p_(i,d) + p_(w,d) + p_(s,d) = %% [(kN)/m]', 'Vonalmenti tervezési érték');
            $blc->h2('Koncentrált terhek');
            $blc->boo('makeP', ['', 'Fenti megoszló terhek másodlagos tartóra hatnak először'], false, 'A másodlagos tartók koncentrált erőként jelennek meg');
            $Mq = ($f3->_pd * $f3->_l * $f3->_l) / 8;
            $Vq = $f3->_pd * ($f3->_l / 2);
            if ($f3->_makeP) {
                $blc->lst('QPart', ['2' => 2, '3' => 3, '4' => 4, '5' => 5, '6' => 6], ['', 'Tartó felosztása részekre'], 2);
                $blc->numeric('Acp', ['A_(c,p)', 'Másodlagos tartók keresztmetszeti területe'], 160000, 'mm2', 'Önsúly számításhoz');
                $blc->def('Gpd', H3::n2(($f3->_Acp / 1000000) * $f3->_b * $f3->_gamma * $f3->__GG), 'G_(p,d) = 2*(A_(c,p)*b/2)*gamma*gamma_G = %% [kN]', 'Másodlagos tartók önsúlyából származó koncentrált erő tervezési értéke. Főtartóra mindkét oldalról támaszkodik másodlagos tartó.');
                $blc->txt('Tartóra megoszló önsúly hat ( $g_d = ' . $f3->_gd . ' [(kN)/m]$ ), egyedi koncentrált erő ( $P_1$ ), szelemen önsúlyból származó koncentrált erő ( $G_(p,d)$ ) és szétosztott koncentrált erő ( $Q_d$ ) (installációból, rétegrendről, hóból, szélből).');
                $Mq = ($f3->_gd * $f3->_l * $f3->_l) / 8;
                $Vq = $f3->_gd * ($f3->_l / 2);
                $blc->def('Qtot', ($f3->_pd - $f3->_gd) * $f3->_l, 'Q_(t ot) = (p_d - g_d)*l = %% [kN]', 'Másodlagos tartók által gyűjtött erő főtartó terhelési mezőjében');
//            $blc->def('PPartMode', 'Főtartók végein is van másodlagos tartó', true, '$P_(tot)$ erő szétosztásának módja');
                $blc->txt('Főtartók végein is van másodlagos tartó.');
                $blc->def('Qd', H3::n2($f3->_Qtot / $f3->_QPart + $f3->_Gpd), 'Q_d = Q_(t ot)/' . $f3->_QPart . ' + G_(p,d) = %% [kN]', 'Másodlagos tartók által leadott koncentrált erő');
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
            $blc->success1();
        }



        // =============================================================================================================
        $blc->h1('Közelítő méretfelvétel');
        $blc->note('@AR');
        $blc->def('base', 20, 'base = %% [mm]', '$phi$ vasátmérő alkalmazása');
        $f3->_Abase = $ec->A($f3->_base);
        $blc->def('prenp', ceil((($f3->_MEd*1000000)/(0.8*$f3->_h*0.8*$f3->_rfyd))/$f3->_Abase), 'n_(phi 20) = ceil(((M_(Ed)*10^6)/(0.8*h*0.8*f_(yd)))/A_(phi 20)) = %%', 'Húzott pászma és lágyvas becsült száma összesen');
        $blc->note('$0.8h$ hatékony magasság közelítése. $0.8f_(yd)$ repedés tágasság "biztosítására".');

        // =============================================================================================================
        $blc->h1('Keresztmetszet közelítő ellenőrzése');
        $blc->h2('Középső keresztmetszet közelítő ellenőrzése hajlításra');

        $blc->h3('Húzott acélok becsült elrendezése');
        $blc->def('prenp', ceil(($f3->_b_ft*$f3->_h_ft + ($f3->_b_ft - $f3->_b_w)/2*$f3->_h_ht)*($f3->_cfcd/($f3->_rfyd*$f3->_Abase))), '(n_(phi 20)) = ceil((b_(ft)*h_(ft) - (b_(ft) - b_w)/2*h_(ht))*f_(cd)/(f_(yd)*A_(phi20))) = %%', ' Nyomásra teljesen kihasznált felső öv alapján becsült összes vasszám');
        $blc->def('a', floor((($f3->_b_fb - 2*$f3->_c)/(2*$f3->_base)) + 1), '(a) = floor((b_(fb) - 2c)/(2*D_(phi20)) + 1) = %%', 'Vaselrendezés - pászma oszlopok száma');
        $blc->def('ratio', 0.75, 'ratio := %%', 'Pászma-lágyvas arány');
        $blc->def('bs', 1, 'b_s := %%', 'Lágyvas sorok száma');
        $blc->def('b', ceil((0.75*$f3->_prenp)/$f3->_a + $f3->_bs), '(b) = ceil((ratio*n_(phi 20))/a + b_s) = %%', 'Vaselrendezés - sorok száma');
        $blc->def('as', ($f3->_b*$f3->_base + ($f3->_b - 1)*$f3->_base)/2 + $f3->_c, '(a_s) = (b*base + (b - 1)*base)/2 + c = %% [mm]', 'Húzott acélok távolsága alsó széltől');
        $blc->note('^ KG-nél más - ELLENŐRIZENDŐ!');
        $blc->def('d', $f3->_h - $f3->_as, '(d) = h - a_s = %% [mm]');

        $blc->h3('Szükséges húzott acél mennyisége');
        // [kNm] [cm]:
        $blc->def('z', H3::n4(1-sqrt(1-200*($f3->_MEd)/(0.1*$f3->_b_ft*pow(0.1*$f3->_d,2)*($f3->_cfcd/10)))), 'zeta = 1-sqrt(1-200*M_(Ed)/(b_(ft)*d^2*f_(cd))) =%%', 'Nyomott zóna magassághányada');
        $blc->def('x', H3::n0($f3->_z*$f3->_d), '(x) = zeta*d = %% [mm]', 'Nyomott zóna becsült magassága');
        $blc->def('Asmin', H3::n0($f3->_b_ft*$f3->_x*($f3->_cfcd/$f3->_rfyd)), 'A_(s,min) = b*x*f_(cd)/f_(yd) = %% [mm^2]', 'Szükséges húzott acélmennyiség');
        $blc->def('umin', H3::n4($f3->_Asmin/$f3->_A_c), 'mu_(s,min) = A_(s,min)/A_c = %%', 'Szükséges húzott vashányad');

        $blc->txt('Javasolt vasalás:');
        $blc->def('np', ceil($f3->_Asmin/$f3->_Abase*$f3->_ratio), 'n_p = ceil(A_(s,min)/A_(phi'.$f3->_base.')*ratio) = %%', 'Pászmák száma');
        // TODO min 2
        $blc->def('ns', ceil(($f3->_Asmin - $f3->_np*$f3->_Abase)/$f3->_Abase), 'n_s = %%', 'Lágyvasak száma');
        $blc->txt('', 'Pászva-lágyvas arány: $'.$f3->_np/($f3->_np + $f3->_ns)*100 .'%$');
        $blc->txt('Alkalmazott vasalás:');
        $blc->numeric('np', ['n_p', 'Alkalmazott pászmák száma'], $f3->_np, '', '');
        $blc->numeric('ns', ['n_s', 'Alkalmazott lágyvasak száma'], $f3->_ns, '', '');
        $As = $f3->_ns + $f3->_np;
        $blc->def('n', $As, 'n = %%');
        $blc->numeric('a', ['a', 'Alkalmazott vaselrendezés pászma oszlopok száma'], $f3->_a, '', '');
        $blc->def('b', ceil($f3->_np/$f3->_a) + $f3->_bs, 'b = ceil(n_p/a) + b_s = %%', 'Alkalamzott vaselrendezés sorok száma');
        $blc->def('As', H3::n0($f3->_n*$f3->_Abase), 'A_s = n * A_(phi '.$f3->_base.') = %% [mm^2]', 'Alkalamzott húzott acélmennyiség');
        $blc->def('u', H3::n4($f3->_As/$f3->_A_c), 'mu_s = A_(s)/A_c = %%', 'Alkalamzott helyettesítő húzott vashányad');
        $blc->def('as', ($f3->_b*$f3->_base + ($f3->_b - 1)*$f3->_base)/2 + $f3->_c, 'a_s = (b*base + (b - 1)*base)/2 + c = %% [mm]', 'Húzott acélok súlypontjának távolsága alsó széltől');
        $blc->note('^ KG-nél nagyon más - ELLENŐRIZENDŐ!');
        $blc->def('d', $f3->_h - $f3->_as, 'd = h - a_s = %% [mm]');
        $blc->def('x', H3::n0($f3->_As*$f3->_rfyd/($f3->_b_ft*$f3->_cfcd)), 'x = A_s*f_(yd)/(b_(ft)*f_(cd)) = %% [mm]', 'Nyomott zóna magassága');
        if ($f3->_x <= $f3->_h_ft + $f3->_h_ht) {
            $blc->label('yes', 'A nyomott zóna a kiékelt fejlemezen belül van');
        } else {
            $blc->label('no', 'A fejlemez magasságát növelni javasolt!');
        }
        $blc->def('z', H3::n4($f3->_x/$f3->_d), 'zeta = x/d = %%', 'Nyomott zóna magassághányada');
        $blc->def('zmax', H3::n4((0.16 - 0.27)/(22 - 11)*($f3->_l*1000)/$f3->_d + 0.38), 'zeta_(max L/H) =  (0.16-0.27)/(22-11)*l/d*100+0.38 = %%', 'Nyomott zóna magassághányadának korlátozása');
        $blc->note('^ KG önkényes képlet');
        if ($f3->_z < $f3->_zmax/1.2) {
            $blc->label('yes', 'Kevésbé kihasznált km.');
        } elseif ($f3->_z > $f3->_zmax*1.1) {
            $blc->label('no', 'Erősen kihasznált km.');
        } else {
            $blc->label('yes', 'Jól kihasznált km.');
        }

        $blc->success0('MRd');
            $blc->def('MRd', ($f3->_As*$f3->_rfyd*($f3->_d-$f3->_x/2))/1000000, 'M_(Rd) = A_s*f_(yd)*(d-x/2) = %% [kNm]', 'Hajlítási teherbírás tervezési értéke. $M_(Ed) = '.$f3->_MEd.' [kNm]$');
            $blc->label($f3->_MEd/$f3->_MRd, 'kihasználtság');
        $blc->success1();

        $blc->h2('Öv és gerinc elnyíródásának vizsgálata', 'Csatlakozási felületeken fellépő nyírófeszültség');
        $blc->note('KG alapján. *Vasbeton szerkezetek (2016) 6.5.3. Nyírás a gerinc és a fejlemez között* fejezete is tartalamaz egy eljárást');
        $blc->note('$V_(Ed) = beta*V_(Ed)/(z*b_i)$');
        $blc->def('beta', 1, 'beta = %%', 'A később készült betonban a működő hosszirányú erő és a teljes hosszirányú erő aránya a vizsgált keresztmetszetben');
        $blc->math('V_(Ed) = '.$f3->_VEd.' [kN]', 'Kereszt irányú nyíróerő');
        $blc->def('zV', $f3->_d - $f3->_x/2, 'z_V = d - x/2 = %% [mm]', 'Együttdolgozó keresztmetszet belső karja');
        $blc->def('bV', $f3->_b_w - 120, 'b_V = b_w - 120 [mm] = %% [mm]', 'Csatlakozási felület szélessége');
        $blc->def('vEd', $f3->_beta*($f3->_VEd*1000)/$f3->_zV/$f3->_bV, 'v_(Ed) = beta*V_(Ed)/(z_V*b_V) = %% [N/(mm^2)]');

        $blc->info0();
            $blc->txt('*STB* képlet alapján - $(S*T)/(b*I)$:', '$T = V_(Ed); b = b_V$');
            $blc->def('hf', $f3->_x, 'h_f = x = %% [mm]', 'Nyomott öv vastagsága');
            $blc->math('b_(ft) = '.$f3->_b_ft.' [mm]', 'Övszélesség');
            $blc->def('hw2', $f3->_h_ht + $f3->_h_w + $f3->_h_hb + ($f3->_h_ft - $f3->_x), 'h_(w2) = %% [mm]', 'Gerincmagasság');
            $blc->def('sp', H3::n0(($f3->_hf*$f3->_b_ft*$f3->_hf/2 + $f3->_hw2*$f3->_b_w*($f3->_hf + $f3->_hw2/2) + $f3->_h_fb*$f3->_b_fb*($f3->_hf + $f3->_hw2 + $f3->_h_fb/2))/($f3->_hf*$f3->_b_ft + $f3->_hw2*$f3->_b_w + $f3->_h_fb*$f3->_b_fb)), 's_p = %% [mm]', 'Súlypont fentről számítva');
            $blc->note('^ KG-nél T keresztmetszet esetén is van alsó öv');
            $blc->def('S', H3::n0(($f3->_hf*$f3->_b_ft*($f3->_sp - $f3->_hf/2))/1000), 'S = h_f*b_(ft)*(s_p - h_f/2) = %% [cm^3]', 'Öv tehetetlenségi nyomatéka');
            $blc->def('I', H3::n0(($f3->_hf*$f3->_b_ft*pow($f3->_hf/2 - $f3->_sp, 2) + $f3->_hw2*$f3->_b_w*pow($f3->_hf + $f3->_hw2/2 - $f3->_sp, 2) + $f3->_h_fb*$f3->_b_fb*pow($f3->_hf + $f3->_hw2 + $f3->_h_fb/2 - $f3->_sp, 2))/10000), 'I = %% [cm^4]', 'Inercia');
            $blc->def('I', (     $f3->_hw2*$f3->_b_w*pow($f3->_hf + $f3->_hw2/2 - $f3->_sp, 2)      )/10000 +1, 'I = %% [cm^4]', 'Inercia');
            $blc->def('vEdSTB', ($f3->_S*$f3->_VEd)/(($f3->_bV/10)*$f3->_I), 'v_(Ed,STB) = (S*V_(Ed))/(b_V*I) = %% [(kN)/(cm^2)]');
            $blc->note('^ TODO rossz');
        $blc->info1();
    }
}
