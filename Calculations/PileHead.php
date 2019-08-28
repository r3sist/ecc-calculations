<?php

namespace Calculation;

/**
 * Pilehead analysis according to Eurocodes - ECC calculation class
 *
 * (c) Bence VÁNKOS
 * https:// structure.hu
*/

Class PileHead extends \Ecc
{

    /**
     * @var $f3 \Base
     * @var $blc \Blc
     * @var $ec \Ec\Ec
     * @throws \Exception
     */
    public function calc(object $f3, object $blc, object $ec): void
    {
        $blc->note('Irodalom: [1.]: *KG CÉH Excel*; [2.]: *Alapozások és földmegtámasztó szerkezetek tervezése az MSZ EN 1997 szerint (2012)*; [3.]: *Vasbeton szerkezetek korszerű vasalása III. - 169 Iparterv tervezélsi segédlet (1964)* [4.]: *MSZ EN 1992-1-1:2010*');

        $blc->h1('Fejtömb kialakítás');
//        $blc->lst('type', ['2 cölöpös cölöpfej (gerenda)' => '2piles', '3 cölöpös cölöpfej' => '3piles', '4 cölöpös cölöpfej' => '4piles', 'Sok cölöpös lemez' => 'plate'], 'Cölöpfej kialakítás', '2piles', '');
        $blc->lst('type', ['2 cölöpös cölöpfej (gerenda)' => '2piles'], 'Cölöpfej kialakítás', '2piles', '');
        $blc->numeric('Dp', ['D_p', 'Cölöp átmérő'], 800, 'mm', '');

        $blc->numeric('h', ['h', 'Lemezvastagság'], 800, 'mm', '');
        $ec->rebarList('fix', 25, ['phi_x', 'Alsó fő vasátmérő x fő irányban'], 'Kengyelen');
        $rowfix = 1;
        $blc->boo('rowfix2', 'Húzott fővasak 2 sorban', false, '');
        if ($f3->_rowfix2) {
            $rowfix = 3; // elosztó vas is
        }
        $ec->wrapNumerics('fix0', 'sx0', ['phi_(x,0)\ \ \ s_(x,0)', 'Alsó alapháló vasátmérője és kiosztása'], 0, 200, 'mm', 'Kengyel alatti alapháló x fő irányban, ha van', '/');
        $ec->rebarList('fiw', 16, ['phi_w', 'Kengyel vagy felkötő vasátmérő'], '');

        $blc->md('Szerkezeti osztály: ***S4***; Karbonátosodás okozta korrózió: ***XC2***');
        $blc->lst('XA', ['XA1 enyhén (XD1 XF3)' => 'XA1', 'XA2 mérsékelten (XD2 XF4)' => 'XA2', 'XA3 erősen (XD3)' => 'XA3'], 'Kémiai környezet agresszivitása', 'XA2', 'Illetve további környezeti osztály feltételek');
        $XAcmindur = ['XA1' => 35, 'XA2' => 40, 'XA3' => 45];
        $ec->matList('concreteMaterialName', 'C30/37', 'Beton anyagminőség');
        $ec->saveMaterialData($f3->_concreteMaterialName, 'c');
        $ec->matList('rebarMaterialName', 'B500', 'Betonvas anyagminőség');
        $ec->saveMaterialData($f3->_rebarMaterialName, 'r');
        $blc->def('cmindur', $XAcmindur[$f3->_XA], 'c_(min,dur) = %% [mm]', '*MSZ 4798:2016 NAD N1 táblázat* szerinti betonszerkezettől és környezettől függő minimális betonfedés, minimum *XC2* osztály feltételezésével');
        $blc->def('cminb', $f3->_fiw, 'c_(min,b) := %% [mm]', 'Kívül kengyel feltételezésével kengyel átmérő');
        $blc->def('cmin', max($f3->_cminb, $f3->_cmindur, 10), 'c_(min) = max{(c_(min,b)),(c_(min,dur)),(10):} = %% [mm]', '');
        $blc->def('Deltacdev', 10, 'Delta c_(dev) = %% [mm]', 'Kötelező ráhagyás ajánlott értéke *MSZ EN 1992-1-1:2010 4.4.1.3. (1)P* és *NA 3.2.1.* szerint');
        $blc->def('cnom', $f3->_cmin + $f3->_Deltacdev, 'c_(nom) = c_(min) + Delta c_(dev) = %% [mm]', 'Névleges betonfedés');

        $blc->def('deff', floor($f3->_h - ($f3->_cnom + ($rowfix*$f3->_fix)/2 + $f3->_fiw + $f3->_fix0)), 'd_(eff) = h - (c_(nom) + (phi_x*'.$rowfix.')/2 + phi_w + phi_(x,0)) = %% [mm]', 'Hatékony lemezvastagság - a húzott acélbetétek súlypontja és a nyomott szélső szál közötti távolság');

        $blc->h1('Megtámasztott szerkezet');
        $blc->numeric('FEdz', ['F_(Ed,z)', 'Átszúródó függőleges teher pillérről'], 4000, 'kN', 'Nyíróerő tervezési értéke');
        $blc->numeric('FEdy', ['F_(Ed,y)', 'Többlet vízszintes teher pillérről'], 0, 'kN', '');

        $ec->wrapNumerics('acol', 'bcol', ['a_(col)×b_(col)', 'Pillér méret'], 400, 400, 'mm', false, '×');
        $blc->boo('sleeve', 'Bővítés kehelynyakra', false, '50 mm hézag, 250 mm fal - erőátadás fal tengelyében');
        if ($f3->_sleeve) {
            $f3->_acol = $f3->_acol + 350;
            $f3->_bcol = $f3->_bcol + 350;
            $blc->math($f3->_acol.'×'.$f3->_bcol.' [mm]');
        }

        $blc->numeric('xp0', ['x_(p,0)', 'Cölöpök tengelytávolsága'], 2400, 'mm', '');
        $blc->def('xp', $f3->_xp0 + 2*100, 'x_p := x_(p,0) + 2*e_m = '.$f3->_xp0.' + 2*100 = %% [mm]', 'Cölöpök véletlen elmozdulásával növelt távolság');
        $blc->note('D &lt; 1000 mm cölöpátmérőig. [MSZ EN 1536:2001 7.2 23.o.]');

        $blc->def('z', \H3::n0(0.9*$f3->_deff), 'z ~= 0.9*d_(eff) = %% [mm]', 'Nyomott betönöv magasságának közelítő felvétele');
        $blc->def('xc', \H3::n0(0.2*$f3->_deff), 'x_c ~= 0.2*d_(eff) = %% [mm]', '$z = d_(eff)-x_c/2 = 0.9d_(eff)$ összefüggésből');

        $blc->h1('Erőjáték meghatározása', $f3->_type);

        switch ($f3->_type) {
            case '2piles':
                $f3->_np = 2;
                $blc->def('theta', \H3::n2(rad2deg(atan($f3->_z/(0.5*$f3->_xp)))), 'Theta = arctan(z/(0.5*x_p)) = %%°', 'Belső erők szöge');
                ($f3->_theta < 26.0)?$blc->danger('$Theta < 26 [deg]$ (Javasolt min 30°)'):true;
                $blc->def('NEd', ceil($f3->_FEdz/(2*sin(deg2rad($f3->_theta))) + $f3->_FEdy/(2*cos(deg2rad($f3->_theta)))), 'N_(Ed) = F_(Ed,z)/(2*sin(Theta)) + F_(Ed,y)/(2*cos(Theta)) = %% [kN]');
                $blc->def('HEd', ceil($f3->_FEdz/(2*tan(deg2rad($f3->_theta)))), 'H_(Ed) = F_(Ed,z)/(2*tan(Theta)) = %% [kN]');
                $blc->note('HEd pillér vízszintes erő az egyik rácsrudat húzza, a másikat nyomja - vízszintes komponensben ez nem jelentkezik.');
                break;
        }

        $blc->h1('Húzott vasalás pillérsorok fölött');
        $blc->boo('fydFactor', 'Folyáshatár csökkentése 0.7 faktorral repedéstágasság figyelembevételéhez', false);
        $fydFactor = 1;
        if ($f3->_fydFactor) {
            $fydFactor = 0.7;
        }
        $blc->def('As', ceil(($f3->_HEd*1000)/($f3->_rfyd*$fydFactor)), 'A_s = H_(Ed)/(f_(y,d)) = %% [mm^2]', 'Szükséges húzott vasmennyiség');
        $blc->numeric('bs', ['b_s', 'Húzott vasak kiosztási szélessége cölöp felett'], 1.0*$f3->_Dp, 'mm', 'Jellemzően cölöpszélesség');
        $blc->boo('minusAs0', 'Ø'.$f3->_fix0.'/'.$f3->_sx0.' alapháló figyelembevétele');
        if ($f3->_minusAs0) {
            $blc->def('As', ceil($f3->_As - floor($f3->_bs/$f3->_sx0)*$ec->A($f3->_fix0)), 'A_s = A_s - floor(b_s/s_(x,0))*A_(phi_(x,0)) = %% [mm^2]');
        }
        $blc->def('ns', ceil($f3->_As/$ec->A($f3->_fix)), 'n_s = ceil(A_s/A_(phi'.$f3->_fix.')) = %%', 'Húzott fővas száma');
        $blc->def('sx', ceil($f3->_bs/($f3->_ns - 1)), 's_x = b_s/(n_s -1) = %% [mm]', 'Húzott fővas osztásköze');
        $blc->note('[3] 260.o.: Sorok száma s_x &lt; 2*phi_x feltétel alapján.');
        if ($f3->_sx < 2*$f3->_fix) {
            if ($f3->_rowfix2) {
                $ns0 = ceil($f3->_ns/2);
                $sx0 = \H3::n0(($f3->_bs/($ns0 - 1))/10)*10.0;
                $ns1 = $f3->_ns - $ns0;
                $sx1 = \H3::n0(($f3->_bs/($ns1 - 1))/10)*10.0;
                $blc->label('yes', $ns0.'Ø'.$f3->_fix.'/'.$sx0.' és '.$ns1.'Ø'.$f3->_fix.'/'.$sx1.' 2 sorban');
                if ($sx0 < 2*$f3->_fix || $sx1 < 2*$f3->_fix) {
                    $blc->danger('3 vagy több sorban kéne a vasakat elhelyezni.');
                }
                $f3->_sx = min($sx0, $sx1);
            } else {
                $blc->label('no', $f3->_ns.'Ø'.$f3->_fix.'/'.\H3::n0($f3->_sx/10)*10.0.' - 2 sor kellene!');
            }
            $blc->txt('', '$s_x < 2*phi_x$');
        } else {
            if ($f3->_rowfix2) {
                $ns0 = ceil($f3->_ns/2);
                $sx0 = \H3::n0(($f3->_bs/($ns0 - 1))/10)*10.0;
                $ns1 = $f3->_ns - $ns0;
                $sx1 = \H3::n0(($f3->_bs/($ns1 - 1))/10)*10.0;
                $blc->label('no', $ns0.'Ø'.$f3->_fix.'/'.$sx0.' és '.$ns1.'Ø'.$f3->_fix.'/'.$sx1.' 2 sorban (1 elég)');
                $f3->_sx = min($sx0, $sx1);
            } else {
                $blc->label('yes', $f3->_ns.'Ø'.$f3->_fix.'/'.\H3::n0($f3->_sx/10)*10.0.' 1 sorban');
            }
        }

        $blc->h2('Húzott vasak lehorgonyzása');
        $blc->note('A vasra ható keresztirányú nyomóerőtől eltekintve, 100%-ra kihasznált vas szálakat feltételezve.');
        if ($f3->_sx < 3*$f3->_fix) {
            $alphaa = 1;
            $blc->math('s_x lt 3*phi_x rarr alpha_a = 1', 'Kampózás nem vehető figyelembe');
        } else {
            $alphaa = 0.7;
            $blc->math('s_x gt 3*phi_x rarr alpha_a = 0.7', 'Kampózás figyelembe vehető');
        }
        $blc->txt('Anyagminőségnél **'.(($f3->_cfbd07)?'rossz tapadás':'jó tapadás').'** ($f_(b,d) = '.$f3->_cfbd.'[N/(mm^2)]$) van beállítva');
        $blc->numeric('nsPlus', ['n_(s+)', 'Többlet vas figyelembevétele: további Ø'.$f3->_fix], 0, 'db', 'Húzott acélok 100%-nál alacsonyabb kihasználtságának figyelembevételéhez');
        $concrete = new \Calculation\Concrete();
        $concrete->moduleAnchorageLength($f3->_fix, $f3->_rfyd, $f3->_cfbd, $alphaa, $f3->_ns, $f3->_ns + $f3->_nsPlus);

        $blc->h1('Húzott vasalás felkötő vasai', 'Kengyelek');
        $blc->note('[3] 260.o.: Rövid gerendáknál nyírási húzóerők nem lépnek fel. Felkötő vasak húzott vas lenyomódása ellen kellenek.');
        $blc->def('Fw', ceil($f3->_FEdz/(1.5*$f3->_np)), 'F_w = F_(Ed,z)/(1.5*n_p) = %% [kN]', 'Kengyelekkel felveendő erő, ahol $n_p$ a cölöpök száma');
        $blc->lst('nwb', ['2' => 2, '4' => 4, '6' => 6], ['n_(w,b)', 'Kengyel szárak száma'], 2);
        $sw = floor(($f3->_xp0/ceil(($f3->_Fw*1000)/(($ec->A($f3->_fiw)*$f3->_nwb)*$f3->_rfyd))));
        $blc->def('sw', $sw, 's_w = x_(p,0)/(F_w/(A_(phi_w)*n_(w,b)*f_(y,d))) = %%', 'Szükséges kengyel távolság');
        $blc->success('$phi'.$f3->_fiw.'"/"'.min(floor($sw/10)*10.0, 200).'×'.$f3->_nwb/2.0.'$ kengyelezés');

        $blc->h1('A ferde nyomott betonöv nyomási teherbírásának ellenőrzése', 'Rácsmodellek alapján');
        $blc->note('[4.] 6.2.5');
        $blc->def('sigmaRdMax', $f3->_cfcd, 'sigma_(Rd,max) = f_(cd) = %% [N/(mm^2)]');

        $c = 0.5*$f3->_xp - 0.5*$f3->_acol - 0.1*$f3->_deff + 0.5*$f3->_Dp;
        $gamma = atan($f3->_z/$c);
        $beta = deg2rad(90 - $f3->_theta);
        $epszilon = deg2rad(180 - rad2deg($gamma) - rad2deg($beta));
        $h2 = floor($f3->_Dp*(sin($gamma)/sin($epszilon)));
        $blc->def('h2', $h2, 'h_2 = %% [mm]', 'Szerkesztésből adódó rácsrúd magasság cölöp felett');

        $a = 0.5*$f3->_xp + 0.5*$f3->_acol - 0.5*$f3->_Dp + $f3->_deff*0.1;
        $alfa = atan($f3->_z/$a);
        $h1 = floor(sin($alfa)*($f3->_acol+0.2*$f3->_deff)); // TODO szembe oldalra állít merőlegest, nem az erővonalra most
        $blc->def('h1', $h1, 'h_1 = %% [mm]', 'Szerkesztésből adódó rácsrúd magasság pillér alatt');

        $blc->note('Szerkesztéssel ellenőrizendő!');

        $blc->def('Ac', min($f3->_Dp*$f3->_h2*(pi()/4), $f3->_Dp*$f3->_h1), 'A_c = min{(D_p*h_2*pi/4),(D_p*h_1):} = %% [mm^2]', 'Nyomott rácsrúd felülete');
        $blc->def('NRd', floor($f3->_Ac*$f3->_cfcd*0.001), 'N_(Rd) = A_c*f_(cd) = %% [kN]', 'Nyomott rácsrúd ellenállása');
        $blc->label($f3->_NEd/$f3->_NRd, 'Kihasználtság');

        $blc->h2('Fejtömb alsó csomópontjának ellenőrzése');
        $blc->note('[4.]: 6.5.4 (4) b)');
        $blc->def('sigmaRdMax2', 0.85*(1 - $f3->_cfck/250)*$f3->_cfcd, 'sigma_(Rd,max,2) = k_2*(1-f_(ck)/250)*f_(cd) = %% [N/(mm^2)]');
        $blc->def('Ac2', $f3->_Dp*$f3->_h2*(pi()/4), 'A_(c,2) = %% [mm^2]');
        $blc->def('NRd2', floor($f3->_Ac2*$f3->_sigmaRdMax2*0.001), 'N_(Rd,2) = A_(c,2)*sigma_(Rd,max,2) = %% [kN]');
        $blc->label($f3->_NEd/$f3->_NRd2, 'Kihasználtság');

        $blc->h2('Fejtömb felső csomópontjának ellenőrzése');
        $blc->note('[4.]: 6.5.4 (4) a)');
        $blc->def('sigmaRdMax1', 1.0*(1 - $f3->_cfck/250)*$f3->_cfcd, 'sigma_(Rd,max,1) = k_1*(1-f_(ck)/250)*f_(cd) = %% [N/(mm^2)]');
        $blc->def('Ac1', $f3->_Dp*$f3->_h1, 'A_(c,1) = %% [mm^2]');
        $blc->def('NRd1', floor($f3->_Ac1*$f3->_sigmaRdMax1*0.001), 'N_(Rd,1) = A_(c,2)*sigma_(Rd,max) = %% [kN]');
        $blc->label($f3->_NEd/$f3->_NRd1, 'Kihasználtság');

        $blc->h2('Ferde betonöv ellenőrzése nyírási vasalást nem igénylő szerkezet ferde nyomási teherbírásával ellenőrizve');
        $blc->note('[4.]: 6.2.2 (6)');
        $blc->def('VRdmax', floor(0.5*$f3->_Dp*$f3->_deff*0.6*(1 - $f3->_cfck/250)*$f3->_cfcd*0.001), 'V_(Rd,max) = 0.5*D_p*d_(eff)*0.6*(1-f_(ck)/250)*f_(cd) = %% [kN]');
        $blc->label($f3->_NEd/$f3->_VRdmax, 'Kihasználtság');
        $blc->txt(false, '$N_(Ed)/V_(Rd,max)$');

        $blc->h1('Nyírás ás átszúródás ellenőrzése', '`TODO`');
// ============================ old ================================
   /*

        $blc->hr();
        $f3->_VEd = $f3->_FEdz;
        $blc->hr();


        $blc->numeric('beta', ['beta', 'Teherelosztás'], 1.15, '', 'Tehereloszlás egyenlőtlenségéből származó hatás tényezője');
        $blc->h2('Segéd geometria a helyettesítő terhelő pillér méretfelvételéhez');
        $blc->numeric('hv', ['h_v', 'Teherelosztó lemez vastagsága'], 0, 'mm', '2/1-es szétterjedéssel számolva');
        $blc->def('hsum', $f3->_h + $f3->_hv, 'h_(sum) = h + h_v = %% [mm]', 'Teljes vastagság');

            $blc->numeric('a', ['a', 'Pillér méret'], 800, 'mm');
            $blc->numeric('b', ['b', 'Pillér méret'], 800, 'mm', 'Számításban használt négyszög pillérek méretei');
            if ($f3->_hv != 0) {
                $blc->def('a', $f3->_a + $f3->_hv, 'a := a_(eff) = a + h_v = %% [mm]');
                $blc->def('b', $f3->_b + $f3->_hv, 'b := b_(eff) = b + h_v = %% [mm]');
            }


        $blc->h1('Beton tönkremenetele ferde nyomásra', '$u_0$ keresztmetszet ellenőrzése - a nyírási teherbírás felső korlátjának ellőrzése');
        $blc->note('Minimum lemezvastagság meghatározásához');

            $blc->def('u0', floor(2*($f3->_a + $f3->_b)), 'u_0 = 2*(a+b) = %% [mm]', 'Nyírt kerület pillér tövénél');

        $blc->note('Belső pillérként számolva: *[Vasbetonszerkezetek (2016) 6.8.1 a)]*');
        $blc->def('v', 0.6*(1 - $f3->_cfck/250), 'v = 0.6*(1-f_(ck)/250) = %%', 'Beton szilárdsági tényező - segédmennyiség');
        $blc->def('vRdmax', 0.5*$f3->_v*$f3->_cfcd, 'v_(Rd,max) = 0.5*v*f_(cd) = %% [N/(mm^2)]', 'Nyomott beton rácsrúd teherbírásához tartozó fajlagos határnyíróeró. (Vasalással maximálisan átadható fajlagos nyíróerő tervezési értéke)');
        $blc->def('vEd', \H3::n2(($f3->_beta*($f3->_VEd*1000))/($f3->_u0*$f3->_deff)), 'v_(Ed) = (beta*V_(Ed))/(u_0*d_(eff)) = %% [N/(mm^2)]', 'Nyíróerő fajlagos értéke');
        $blc->label($f3->_vEd/$f3->_vRdmax, 'kihasználtság $v_(Ed)/v_(Rd,max)$');

        $blc->h1('Minimális fejtömb vastagság meghatározása', '');
        $blc->h2('Megfeleltetés átszúródásra $u_0$ keresztmetszetben', '');
        $blc->def('hminu0', ceil(($f3->_beta*($f3->_VEd*1000))/$f3->_vRdmax/$f3->_u0 + ($f3->_c + ($f3->_fix + $f3->_fiy)/2)), 'h_(min,u_0) = (beta*V_(Ed))/(v_(Rd,max)/u_0) + (c+(phi_x+phi_y)/2) = %% [mm]');
        if ($f3->_h >= $f3->_hminu0) {
            $blc->label('yes', 'Alkalmazott lemezvastagság megfelel');
        } else {
            $blc->label('no', 'Alkalmazott lemezvastagság túl kicsi');
        }

   */
    }
}
