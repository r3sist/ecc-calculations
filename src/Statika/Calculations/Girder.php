<?php declare(strict_types = 1);
/**
 * PC pretensioned beam analysis according to Eurocodes - Calculation class for Statika framework
 * (c) Bence VÁNKOS | https://structure.hu | https://github.com/r3sist/ecc-calculations
 */

namespace Statika\Calculations;

use \H3;
use \resist\SVG\SVG;
use Statika\EurocodeInterface;

Class Girder
{
    /**
     * @param Ec $ec
     * @throws \Exception
     */
    public function calc(EurocodeInterface $ec): void
    {
        $ec->note('KG számításai alapján.');
        $ec->region0('mat', 'Anyagminőségek');

            $ec->concreteMaterialListBlock('concreteMaterialName', 'C40/50');
            $concreteMaterial = $ec->getMaterial($ec->concreteMaterialName);
            $ec->def('gamma', 25, 'gamma := %% [(kN)/m^3]', 'Beton térfogatsúlya');

            $ec->rebarMaterialListBlock('rebarMaterialName', 'B500', ['', 'Lágyvas']);
            $rebarMaterial = $ec->getMaterial($ec->rebarMaterialName);
            $ec->input('pmat', ['', 'Feszítőbetét jele'], 'Fp100-R2', '', '');
        $ec->region1();

        $ec->region0('geometry', 'Geometria');
            $griderTypes = ['Párhuzamos övű' => 0, 'Lejtett felső övű' => 1];
            $ec->lst('griderType', $griderTypes, ['', 'Tartó típus'], '', '');
            $ec->il = 0;
            if ($ec->griderType === '1') {
                $ec->numeric('il', ['', 'Övek közti lejtés'], 3, '%', 'Középtől szélek felé egyenletesen süllyedő lejtés felső övön');
            }
            $ec->numeric('l', ['l', 'Tartó teljes hossza'], 12.0, 'm', '');
            $griderSections = ['Négyszög' => 'r', 'T' => 't', 'I' => 'i'];
            $ec->lst('griderSection', $griderSections, ['', 'Tartó keresztmetszet'], 't', '');
            $ec->numeric('h', ['h', 'Tartó teljes magassága'], 900, 'mm', 'Középső keresztmetszetben');
            if ($ec->griderSection === 't' || $ec->griderSection === 'i') {
                $ec->numeric('b_ft', ['b_(ft)', 'Fejlemez szélessége'], 500, 'mm', '');
                $ec->numeric('h_ft', ['h_(ft)', 'Fejlemez magassága'], 150, 'mm', '');
                $ec->numeric('h_ht', ['h_(ht)', 'Fejkiékelés magassága'], 30, 'mm', '');
            } else {
                $ec->b_ft = 0;
                $ec->h_ft = 0;
                $ec->h_ht = 0;
            }

            $ec->numeric('b_w', ['b_w', 'Gerincvastagság'], 160, 'mm', '');

            if ($ec->griderSection === 'i') {
                $ec->numeric('b_fb', ['b_(fb)', 'Alsó öv szélessége'], 400, 'mm', '');
                $ec->numeric('h_fb', ['h_(fb)', 'Alsó öv magassága'], 300, 'mm', '');
                $ec->numeric('h_hb', ['h_(hb)', 'Alsó öv kiékelés magassága'], 30, 'mm', '');
            } else {
                $ec->b_fb = $ec->b_w;
                $ec->h_fb = 0;
                $ec->h_hb = 0;
            }

            $ec->def('h_w', $ec->h-($ec->h_ft + $ec->h_fb), 'h_w = %% [mm]', 'Gerinc magasság');
            $ec->note('^ KG-nél T keresztmetszet esetén is van alsó öv');
            $Act = $ec->b_ft*$ec->h_ft;
            $Acb = $ec->b_fb*$ec->h_fb;
            $Acw = $ec->h_w*$ec->b_w;
            $Acwt = (($ec->b_ft - $ec->b_w)/2)*$ec->h_ht;
            $Acwb = (($ec->b_fb - $ec->b_w)/2)*$ec->h_hb;
            $ec->def('A_c', $Act + $Acb + $Acw + $Acwt + $Acwb, 'A_c = %% [mm^2]', 'Keresztmetszeti terület');

            if ($ec->griderType == 1) {
                $ec->def('h_0', $ec->h - $ec->l*1000*0.5*$ec->il/100, 'h_0 = %% [mm]', 'Lejtett tartó végső keresztmetszeti magassága');
                $ec->def('h_w0', $ec->h_0 -($ec->h_ft + $ec->h_fb), 'h_(w,0) = %% [mm]', 'Lejtett tartó végső keresztmetszeti gerinc magassága');
                $Acw0 = $ec->h_w0*$ec->b_w;
                $ec->def('A_c0', $Act + $Acb + $Acw0 + $Acwt + $Acwb, 'A_(c,0) = %% [mm^2]', 'Lejtett tartó végső keresztmetszeti terület');
            } else {
                $ec->A_c0 = $ec->A_c;
            }
            $G = H3::n2(($ec->A_c + $ec->A_c0)/2*$ec->l*$ec->gamma/1000000);
            $ec->def('G', $G, 'G = %% [kN]', 'Tartó súlya, $G = '. H3::n2($G/10) .' [t]$');
            $ec->numeric('b', ['b', 'Terhelő mező szélessége, gerenda osztás'], 4, 'm', 'Szelemenes rendszer esetén a szelemenek terhelt szelemenszakaszok összes hossza a két oldalon');
        $ec->region1();

        $r = min(400/$ec->h, 600/max($ec->b_ft, $ec->b_fb, $ec->b_w)); // ratio
        $svg = new SVG(600, 400);
        $svg->addLine(0, 0, $r*$ec->b_ft, 0); // top line
        $svg->addLine(0, 0, 0, $r*$ec->h_ft); // top flange left border
        $svg->addLine($r*$ec->b_ft, 0, $r*$ec->b_ft, $r*$ec->h_ft); // top flange right border
        $svg->addLine(0, $r*$ec->h_ft, $r*$ec->b_ft/2-$r*$ec->b_w/2, $r*$ec->h_ft+$r*$ec->h_ht);
        $svg->addLine($r*$ec->b_ft, $r*$ec->h_ft, $r*$ec->b_ft/2+$r*$ec->b_w/2, $r*$ec->h_ft+$r*$ec->h_ht);
        $svg->addLine($r*$ec->b_ft/2+$r*$ec->b_w/2, $r*$ec->h_ft+$r*$ec->h_ht, $r*$ec->b_ft/2+$r*$ec->b_w/2, $r*($ec->h-$ec->h_ft-$ec->h_ht-$ec->h_hb-$ec->h_fb)); // web left
        $svg->addLine($r*$ec->b_ft/2-$r*$ec->b_w/2, $r*$ec->h_ft+$r*$ec->h_ht, $r*$ec->b_ft/2-$r*$ec->b_w/2, $r*($ec->h-$ec->h_ft-$ec->h_ht-$ec->h_hb-$ec->h_fb)); // web right
        $ec->svg($svg);
        unset($svg);

        $ec->numeric('c', ['c', 'Betonfedés'], 40, 'mm', 'Alul');

        // =============================================================================================================
        $ec->h1('Teher számítás');
        $ec->boo('makeEd', ['', 'Mértékadó terhek kézi megadása'], false);
        if ($ec->makeEd) {
            $ec->numeric('MEd', ['M_(Ed)', 'Mértékadó nyomaték tervezési értéke tartóközépen'], 100, 'kNm', '');
            $ec->numeric('VEd', ['V_(Ed)', 'Mértékadó nyíróerő tervezési értéke tartóvégen'], 100, 'kN', '');
        } else {
            $ec->h2('Egyenletesen megoszló terhek', 'Trapézlemezről átadva');
            $ec->numeric('m', ['m', 'Teherátadási módosító tényező'], 1.15, '', 'Trapézlemez többtámaszú hatása, közbenső támasznál; 1-től 1.25-ig');
            $ec->h3('Önsúly terhek');
            $ec->math('gamma_G = ' . $ec::GG);
            $ec->def('gd', H3::n2(((2 * $ec->A_c + $ec->A_c0) / 3 * ($ec->gamma / 1000000)) * $ec::GG), 'g_d = ((2A_c + A_(c,0))/3*gamma)*gamma_G = %% [(kN)/m]', 'Súlyozott vonalmenti átlagsúly tervezési értéke 1/3 hossznál lévő keresztmetszettel');
            $ec->numeric('glk', ['g_(l,k)', 'Rétegrend karakterisztikus felületi terhe'], 0.6, 'kN/m2');
            $ec->def('pld', H3::n2($ec->glk * $ec->b * $ec::GG * $ec->m), 'p_(l,d) = g_(l,k)*b*gamma_G*m = %% [(kN)/m]', 'Vonalmenti teher tervezési értéke');
            $ec->h3('Installációs és egyéb terhek');
            $psi0i = 1;
            $ec->math('gamma_Q = ' . $ec::GQ . '%%% Psi_(0,i) = ' . $psi0i);
            $ec->numeric('qik', ['q_(i,k)', 'Installációs felületi teher karakterisztikus értéke'], 0.5, 'kN/m2');
            $ec->def('pid', H3::n2($ec->qik * $ec->b * $ec::GQ * $ec->m * $psi0i), 'p_(i,d) = q_(i,k)*b*gamma_Q*m*Psi_(0,i) = %% [(kN)/m]', 'Vonalmenti teher tervezési értéke');
            $ec->h3('Szél nyomásból adódó teher');
            $psi0w = 0.6;
            $ec->math('gamma_Q = ' . $ec::GQ . '%%% Psi_(0,w) = ' . $psi0w);
            $ec->numeric('qwk', ['q_(w,k)', 'Szél felületi teher karakterisztikus értéke'], 0.4, 'kN/m2', 'Torlónyomásból, belső szélnyomással, szélnyomáshoz ( **I** ) zóna');
            $ec->def('pwd', H3::n2($ec->qwk * $ec->b * $ec::GQ * $ec->m * $psi0w), 'p_(w,d) = q_(w,k)*b*gamma_Q*m*Psi_(0,w) = %% [(kN)/m]', 'Vonalmenti teher tervezési értéke');
            $ec->h3('Hó/hózug terhe', 'Kiemelt teher');
            $ec->math('gamma_Q = ' . $ec::GQ);
            $ec->numeric('qsk', ['q_(s,k)', 'Hó/hózug felületi teher karakterisztikus értéke'], 1, 'kN/m2', '');
            $ec->def('psd', H3::n2($ec->qsk * $ec->b * $ec::GQ * $ec->m), 'p_(s,d) = q_(w,k)*b*gamma_Q*m = %% [(kN)/m]', 'Vonalmenti teher tervezési értéke');
            $ec->h3('Mértékadó megoszló teher');
            $ec->def('pd', $ec->gd + $ec->pld + $ec->pid + $ec->pwd + $ec->psd, 'p_d = g_d + p_(l,d) + p_(i,d) + p_(w,d) + p_(s,d) = %% [(kN)/m]', 'Vonalmenti tervezési érték');
            $ec->h2('Koncentrált terhek');
            $ec->boo('makeP', ['', 'Fenti megoszló terhek másodlagos tartóra hatnak először'], false, 'A másodlagos tartók koncentrált erőként jelennek meg');
            $Mq = ($ec->pd * $ec->l * $ec->l) / 8;
            $Vq = $ec->pd * ($ec->l / 2);
            if ($ec->makeP) {
                $ec->lst('QPart', ['2' => 2, '3' => 3, '4' => 4, '5' => 5, '6' => 6], ['', 'Tartó felosztása részekre'], 2);
                $ec->numeric('Acp', ['A_(c,p)', 'Másodlagos tartók keresztmetszeti területe'], 160000, 'mm2', 'Önsúly számításhoz');
                $ec->def('Gpd', H3::n2(($ec->Acp / 1000000) * $ec->b * $ec->gamma * $ec::GG), 'G_(p,d) = 2*(A_(c,p)*b/2)*gamma*gamma_G = %% [kN]', 'Másodlagos tartók önsúlyából származó koncentrált erő tervezési értéke. Főtartóra mindkét oldalról támaszkodik másodlagos tartó.');
                $ec->txt('Tartóra megoszló önsúly hat ( $g_d = ' . $ec->gd . ' [(kN)/m]$ ), egyedi koncentrált erő ( $P_1$ ), szelemen önsúlyból származó koncentrált erő ( $G_(p,d)$ ) és szétosztott koncentrált erő ( $Q_d$ ) (installációból, rétegrendről, hóból, szélből).');
                $Mq = ($ec->gd * $ec->l * $ec->l) / 8;
                $Vq = $ec->gd * ($ec->l / 2);
                $ec->def('Qtot', ($ec->pd - $ec->gd) * $ec->l, 'Q_(t ot) = (p_d - g_d)*l = %% [kN]', 'Másodlagos tartók által gyűjtött erő főtartó terhelési mezőjében');
//            $ec->def('PPartMode', 'Főtartók végein is van másodlagos tartó', true, '$P_(tot)$ erő szétosztásának módja');
                $ec->txt('Főtartók végein is van másodlagos tartó.');
                $ec->def('Qd', H3::n2($ec->Qtot / $ec->QPart + $ec->Gpd), 'Q_d = Q_(t ot)/' . $ec->QPart . ' + G_(p,d) = %% [kN]', 'Másodlagos tartók által leadott koncentrált erő');
                $ec->hr();
            }
//        $ec->numeric('P1', ['P_1', 'Egyedi koncentrált teher **tervezési** értéke'], 0, 'kN', '');
//        $ec->numeric('l1', ['l_1', 'Egyedi koncentrált teher távolsága tartó végtől'], 1, 'm');

            $ec->h2('Mértékadó igénybevételek');
            $ec->def('MEdq', $Mq, 'M_(Ed,q) = %% [kNm]', 'Nyomaték tartóközépen, tartóra ható megoszló terhelésből');
            $ec->def('VEdq', $Vq, 'V_(Ed,q) = %% [kN]', 'Nyíróerő tartóvégen, tartóra ható megoszló terhelésből');
            $MQ = 0;
            $VQ = 0;
            if ($ec->makeP) {
                switch ($ec->QPart) {
                    case 2:
                        $MQ = $ec->Qd * $ec->l / 4;
                        $VQ = $ec->Qd / 2;
                        break;
                    case 3:
                        $MQ = $ec->Qd * $ec->l / 3;
                        $VQ = $ec->Qd;
                        break;
                    case 4:
                        $MQ = $ec->Qd * $ec->l / 2;
                        $VQ = 3 * $ec->Qd / 2;
                        break;
                    case 5:
                        $MQ = $ec->Qd * $ec->l * 3 / 5;
                        $VQ = 2 * $ec->Qd;
                        break;
                    case 6:
                        $MQ = $ec->Qd * $ec->l * 3 / 4;
                        $VQ = $ec->Qd * 5 / 2;
                        break;
                }
                $ec->def('MEdQ', $MQ, 'M_(Ed,Q) = %% [kNm]', 'Nyomaték tartóközépen, másodlagos tartók terheléséből');
                $ec->def('VEdQ', $VQ, 'V_(Ed,Q) = %% [kN]', 'Nyíróerő tartóvégen, másodlagos tartók terheléséből');
            }
//          $MP1 = $ec->P1;
//          $ec->def('MEdP1', $MP1, 'M_(Ed,Q) = %% [kNm]', 'Nyomaték tartóközépen, másodlagos tartók terheléséből');
//          $ec->def('VEdP1', $VP1, 'V_(Ed,Q) = %% [kN]', 'Nyíróerő tartóvégen, másodlagos tartók terheléséből');
            $ec->success0('Ed');
                $ec->def('MEd', $MQ + $Mq, 'M_(Ed) = %% [kNm]', 'Nyomaték tartóközépen');
                $ec->def('VEd', $VQ + $Vq, 'V_(Ed) = %% [kN]', 'Nyíróerő tartóvégen');
            $ec->success1();
        }



        // =============================================================================================================
        $ec->h1('Közelítő méretfelvétel');
        $ec->note('@AR');
        $ec->def('base', 20, 'base = %% [mm]', '$phi$ vasátmérő alkalmazása');
        $ec->Abase = $ec->A($ec->base);
        $ec->def('prenp', ceil((($ec->MEd*1000000)/(0.8*$ec->h*0.8*$rebarMaterial->fyd))/$ec->Abase), 'n_(phi 20) = ceil(((M_(Ed)*10^6)/(0.8*h*0.8*f_(yd)))/A_(phi 20)) = %%', 'Húzott pászma és lágyvas becsült száma összesen');
        $ec->note('$0.8h$ hatékony magasság közelítése. $0.8f_(yd)$ repedés tágasság "biztosítására".');

        // =============================================================================================================
        $ec->h1('Keresztmetszet közelítő ellenőrzése');
        $ec->h2('Középső keresztmetszet közelítő ellenőrzése hajlításra');

        $ec->h3('Húzott acélok becsült elrendezése');
        $ec->def('prenp', ceil(($ec->b_ft*$ec->h_ft + ($ec->b_ft - $ec->b_w)/2*$ec->h_ht)*($concreteMaterial->fcd/($rebarMaterial->fyd*$ec->Abase))), '(n_(phi 20)) = ceil((b_(ft)*h_(ft) - (b_(ft) - b_w)/2*h_(ht))*f_(cd)/(f_(yd)*A_(phi20))) = %%', ' Nyomásra teljesen kihasznált felső öv alapján becsült összes vasszám');
        $ec->def('a', floor((($ec->b_fb - 2*$ec->c)/(2*$ec->base)) + 1), '(a) = floor((b_(fb) - 2c)/(2*D_(phi20)) + 1) = %%', 'Vaselrendezés - pászma oszlopok száma');
        $ec->def('ratio', 0.75, 'ratio := %%', 'Pászma-lágyvas arány');
        $ec->def('bs', 1, 'b_s := %%', 'Lágyvas sorok száma');
        $ec->def('b', ceil((0.75*$ec->prenp)/$ec->a + $ec->bs), '(b) = ceil((ratio*n_(phi 20))/a + b_s) = %%', 'Vaselrendezés - sorok száma');
        $ec->def('as', ($ec->b*$ec->base + ($ec->b - 1)*$ec->base)/2 + $ec->c, '(a_s) = (b*base + (b - 1)*base)/2 + c = %% [mm]', 'Húzott acélok távolsága alsó széltől');
        $ec->note('^ KG-nél más - ELLENŐRIZENDŐ!');
        $ec->def('d', $ec->h - $ec->as, '(d) = h - a_s = %% [mm]');

        $ec->h3('Szükséges húzott acél mennyisége');
        // [kNm] [cm]:
        $ec->def('z', H3::n4(1-sqrt(1-200*($ec->MEd)/(0.1*$ec->b_ft* ((0.1 * $ec->d) ** 2) *($concreteMaterial->fcd/10)))), 'zeta = 1-sqrt(1-200*M_(Ed)/(b_(ft)*d^2*f_(cd))) =%%', 'Nyomott zóna magassághányada');
        $ec->def('x', H3::n0($ec->z*$ec->d), '(x) = zeta*d = %% [mm]', 'Nyomott zóna becsült magassága');
        $ec->def('Asmin', H3::n0($ec->b_ft*$ec->x*($concreteMaterial->fcd/$rebarMaterial->fyd)), 'A_(s,min) = b*x*f_(cd)/f_(yd) = %% [mm^2]', 'Szükséges húzott acélmennyiség');
        $ec->def('umin', H3::n4($ec->Asmin/$ec->A_c), 'mu_(s,min) = A_(s,min)/A_c = %%', 'Szükséges húzott vashányad');

        $ec->txt('Javasolt vasalás:');
        $ec->def('np', ceil($ec->Asmin/$ec->Abase*$ec->ratio), 'n_p = ceil(A_(s,min)/A_(phi'.$ec->base.')*ratio) = %%', 'Pászmák száma');
        // TODO min 2
        $ec->def('ns', ceil(($ec->Asmin - $ec->np*$ec->Abase)/$ec->Abase), 'n_s = %%', 'Lágyvasak száma');
        $ec->txt('', 'Pászva-lágyvas arány: $'.$ec->np/($ec->np + $ec->ns)*100 .'%$');
        $ec->txt('Alkalmazott vasalás:');
        $ec->numeric('np', ['n_p', 'Alkalmazott pászmák száma'], $ec->np, '', '');
        $ec->numeric('ns', ['n_s', 'Alkalmazott lágyvasak száma'], $ec->ns, '', '');
        $As = $ec->ns + $ec->np;
        $ec->def('n', $As, 'n = %%');
        $ec->numeric('a', ['a', 'Alkalmazott vaselrendezés pászma oszlopok száma'], $ec->a, '', '');
        $ec->def('b', ceil($ec->np/$ec->a) + $ec->bs, 'b = ceil(n_p/a) + b_s = %%', 'Alkalamzott vaselrendezés sorok száma');
        $ec->def('As', H3::n0($ec->n*$ec->Abase), 'A_s = n * A_(phi '.$ec->base.') = %% [mm^2]', 'Alkalamzott húzott acélmennyiség');
        $ec->def('u', H3::n4($ec->As/$ec->A_c), 'mu_s = A_(s)/A_c = %%', 'Alkalamzott helyettesítő húzott vashányad');
        $ec->def('as', ($ec->b*$ec->base + ($ec->b - 1)*$ec->base)/2 + $ec->c, 'a_s = (b*base + (b - 1)*base)/2 + c = %% [mm]', 'Húzott acélok súlypontjának távolsága alsó széltől');
        $ec->note('^ KG-nél nagyon más - ELLENŐRIZENDŐ!');
        $ec->def('d', $ec->h - $ec->as, 'd = h - a_s = %% [mm]');
        $ec->def('x', H3::n0($ec->As*$rebarMaterial->fyd/($ec->b_ft*$concreteMaterial->fcd)), 'x = A_s*f_(yd)/(b_(ft)*f_(cd)) = %% [mm]', 'Nyomott zóna magassága');
        if ($ec->x <= $ec->h_ft + $ec->h_ht) {
            $ec->label('yes', 'A nyomott zóna a kiékelt fejlemezen belül van');
        } else {
            $ec->label('no', 'A fejlemez magasságát növelni javasolt!');
        }
        $ec->def('z', H3::n4($ec->x/$ec->d), 'zeta = x/d = %%', 'Nyomott zóna magassághányada');
        $ec->def('zmax', H3::n4((0.16 - 0.27)/(22 - 11)*($ec->l*1000)/$ec->d + 0.38), 'zeta_(max L/H) =  (0.16-0.27)/(22-11)*l/d*100+0.38 = %%', 'Nyomott zóna magassághányadának korlátozása');
        $ec->note('^ KG önkényes képlet');
        if ($ec->z < $ec->zmax/1.2) {
            $ec->label('yes', 'Kevésbé kihasznált km.');
        } elseif ($ec->z > $ec->zmax*1.1) {
            $ec->label('no', 'Erősen kihasznált km.');
        } else {
            $ec->label('yes', 'Jól kihasznált km.');
        }

        $ec->success0('MRd');
            $ec->def('MRd', ($ec->As*$rebarMaterial->fyd*($ec->d-$ec->x/2))/1000000, 'M_(Rd) = A_s*f_(yd)*(d-x/2) = %% [kNm]', 'Hajlítási teherbírás tervezési értéke. $M_(Ed) = '.$ec->MEd.' [kNm]$');
            $ec->label($ec->MEd/$ec->MRd, 'kihasználtság');
        $ec->success1();

        $ec->h2('Öv és gerinc elnyíródásának vizsgálata', 'Csatlakozási felületeken fellépő nyírófeszültség');
        $ec->note('KG alapján. *Vasbeton szerkezetek (2016) 6.5.3. Nyírás a gerinc és a fejlemez között* fejezete is tartalamaz egy eljárást');
        $ec->note('$V_(Ed) = beta*V_(Ed)/(z*b_i)$');
        $ec->def('beta', 1, 'beta = %%', 'A később készült betonban a működő hosszirányú erő és a teljes hosszirányú erő aránya a vizsgált keresztmetszetben');
        $ec->math('V_(Ed) = '.$ec->VEd.' [kN]', 'Kereszt irányú nyíróerő');
        $ec->def('zV', $ec->d - $ec->x/2, 'z_V = d - x/2 = %% [mm]', 'Együttdolgozó keresztmetszet belső karja');
        $ec->def('bV', $ec->b_w - 120, 'b_V = b_w - 120 [mm] = %% [mm]', 'Csatlakozási felület szélessége');
        $ec->def('vEd', $ec->beta*($ec->VEd*1000)/$ec->zV/$ec->bV, 'v_(Ed) = beta*V_(Ed)/(z_V*b_V) = %% [N/(mm^2)]');

        $ec->info0();
            $ec->txt('*STB* képlet alapján - $(S*T)/(b*I)$:', '$T = V_(Ed); b = b_V$');
            $ec->def('hf', $ec->x, 'h_f = x = %% [mm]', 'Nyomott öv vastagsága');
            $ec->math('b_(ft) = '.$ec->b_ft.' [mm]', 'Övszélesség');
            $ec->def('hw2', $ec->h_ht + $ec->h_w + $ec->h_hb + ($ec->h_ft - $ec->x), 'h_(w2) = %% [mm]', 'Gerincmagasság');
            $ec->def('sp', H3::n0(($ec->hf*$ec->b_ft*$ec->hf/2 + $ec->hw2*$ec->b_w*($ec->hf + $ec->hw2/2) + $ec->h_fb*$ec->b_fb*($ec->hf + $ec->hw2 + $ec->h_fb/2))/($ec->hf*$ec->b_ft + $ec->hw2*$ec->b_w + $ec->h_fb*$ec->b_fb)), 's_p = %% [mm]', 'Súlypont fentről számítva');
            $ec->note('^ KG-nél T keresztmetszet esetén is van alsó öv');
            $ec->def('S', H3::n0(($ec->hf*$ec->b_ft*($ec->sp - $ec->hf/2))/1000), 'S = h_f*b_(ft)*(s_p - h_f/2) = %% [cm^3]', 'Öv tehetetlenségi nyomatéka');
            $ec->def('I', H3::n0(($ec->hf*$ec->b_ft* (($ec->hf / 2 - $ec->sp) ** 2) + $ec->hw2*$ec->b_w*pow($ec->hf + $ec->hw2/2 - $ec->sp, 2) + $ec->h_fb*$ec->b_fb*pow($ec->hf + $ec->hw2 + $ec->h_fb/2 - $ec->sp, 2))/10000), 'I = %% [cm^4]', 'Inercia');
            $ec->def('I', (     $ec->hw2*$ec->b_w* (($ec->hf + $ec->hw2 / 2 - $ec->sp) ** 2))/10000 +1, 'I = %% [cm^4]', 'Inercia');
            $ec->def('vEdSTB', ($ec->S*$ec->VEd)/(($ec->bV/10)*$ec->I), 'v_(Ed,STB) = (S*V_(Ed))/(b_V*I) = %% [(kN)/(cm^2)]');
            $ec->note('^ TODO rossz');
        $ec->info1();
    }
}
