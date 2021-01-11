<?php declare(strict_types = 1);
/**
 * Pilehead analysis according to Eurocodes - Calculation class for Statika framework
 * (c) Bence VÁNKOS | https://structure.hu | https://github.com/r3sist/ecc-calculations
 */

namespace Statika\Calculations;

use \H3;
use Statika\EurocodeInterface;

Class PileHead
{
    private Concrete $concreteCalculation;

    public function __construct(Concrete $concreteCalculation)
    {
        $this->concreteCalculation = $concreteCalculation;
    }

    /**
     * @param Ec $ec
     * @throws \Exception
     */
    public function calc(EurocodeInterface $ec): void
    {
        $ec->note('[Whitepaper 1](https://structure.hu/ecc/pileheadWp0.pdf) Irodalom: [1.]: *KG CÉH Excel*; [2.]: *Alapozások és földmegtámasztó szerkezetek tervezése az MSZ EN 1997 szerint (2012)*; [3.]: *Vasbeton szerkezetek korszerű vasalása III. - 169 Iparterv tervezélsi segédlet (1964)* [4.]: *MSZ EN 1992-1-1:2010*.');

        $ec->h1('Fejtömb kialakítás');
        $ec->lst('type', ['2 cölöpös cölöpfej (gerenda)' => '2piles'], ['', 'Cölöpfej kialakítás'], '2piles', '');
        $ec->numeric('Dp', ['D_p', 'Cölöp átmérő'], 800, 'mm', '');

        $ec->numeric('h', ['h', 'Lemezvastagság'], 800, 'mm', '');
        $ec->rebarList('fix', 25, ['phi_x', 'Alsó fő vasátmérő x fő irányban'], 'Kengyelen');
        $rowfix = 1;
        $ec->boo('rowfix2', ['', 'Húzott fővasak 2 sorban'], false, '');
        if ($ec->rowfix2) {
            $rowfix = 3; // elosztó vas is
        }
        $ec->wrapNumerics('fix0', 'sx0', '$phi_(x,0)\ \ \ s_(x,0)$ Alsó alapháló vasátmérője és kiosztása', 0, 200, 'mm', 'Kengyel alatti alapháló x fő irányban, ha van', '/');
        $ec->rebarList('fiw', 16, ['phi_w', 'Kengyel vagy felkötő vasátmérő'], '');

        $ec->md('Szerkezeti osztály: ***S4***; Karbonátosodás okozta korrózió: ***XC2***');
        $ec->lst('XA', ['XA1 enyhén (XD1 XF3)' => 'XA1', 'XA2 mérsékelten (XD2 XF4)' => 'XA2', 'XA3 erősen (XD3)' => 'XA3'], ['', 'Kémiai környezet agresszivitása'], 'XA2', 'Illetve további környezeti osztály feltételek');
        $XAcmindur = ['XA1' => 35, 'XA2' => 40, 'XA3' => 45];

        $ec->concreteMaterialListBlock('concreteMaterialName', 'C30/37');
        $concreteMaterial = $ec->getMaterial($ec->concreteMaterialName);

        $ec->rebarMaterialListBlock('rebarMaterialName');
        $rebarMaterial = $ec->getMaterial($ec->rebarMaterialName);


        $ec->def('cmindur', $XAcmindur[$ec->XA], 'c_(min,dur) = %% [mm]', '*MSZ 4798:2016 NAD N1 táblázat* szerinti betonszerkezettől és környezettől függő minimális betonfedés, minimum *XC2* osztály feltételezésével');
        $ec->def('cminb', $ec->fiw, 'c_(min,b) := %% [mm]', 'Kívül kengyel feltételezésével kengyel átmérő');
        $ec->def('cmin', max($ec->cminb, $ec->cmindur, 10), 'c_(min) = max{(c_(min,b)),(c_(min,dur)),(10):} = %% [mm]', '');
        $ec->def('Deltacdev', 10, 'Delta c_(dev) = %% [mm]', 'Kötelező ráhagyás ajánlott értéke *MSZ EN 1992-1-1:2010 4.4.1.3. (1)P* és *NA 3.2.1.* szerint');
        $ec->def('cnom', $ec->cmin + $ec->Deltacdev, 'c_(nom) = c_(min) + Delta c_(dev) = %% [mm]', 'Névleges betonfedés');

        $ec->def('deff', floor($ec->h - ($ec->cnom + ($rowfix*$ec->fix)/2 + $ec->fiw + $ec->fix0)), 'd_(eff) = h - (c_(nom) + (phi_x*'.$rowfix.')/2 + phi_w + phi_(x,0)) = %% [mm]', 'Hatékony lemezvastagság - a húzott acélbetétek súlypontja és a nyomott szélső szál közötti távolság');

        $ec->h1('Megtámasztott szerkezet');
        $ec->numeric('FEdz', ['F_(Ed,z)', 'Átszúródó függőleges teher pillérről'], 4000, 'kN', 'Nyíróerő tervezési értéke');
        $ec->numeric('FEdy', ['F_(Ed,y)', 'Többlet vízszintes teher pillérről'], 0, 'kN', '');

        $ec->wrapNumerics('acol', 'bcol', '$a_(col)×b_(col)$ Pillér méret', 400, 400, 'mm', '', '×');
        $ec->boo('sleeve', ['', 'Bővítés kehelynyakra'], false, '50 mm hézag, 250 mm fal - erőátadás fal tengelyében');
        if ($ec->sleeve) {
            $ec->acol = $ec->acol + 350;
            $ec->bcol = $ec->bcol + 350;
            $ec->math($ec->acol.'×'.$ec->bcol.' [mm]');
        }

        $ec->numeric('xp0', ['x_(p,0)', 'Cölöpök tengelytávolsága'], 2400, 'mm', '');
        $ec->def('xp', $ec->xp0 + 2*100, 'x_p := x_(p,0) + 2*e_m = '.$ec->xp0.' + 2*100 = %% [mm]', 'Cölöpök véletlen elmozdulásával növelt távolság');
        $ec->note('D &lt; 1000 mm cölöpátmérőig. [MSZ EN 1536:2001 7.2 23.o.]');

        $ec->def('z', H3::n0(0.9*$ec->deff), 'z ~= 0.9*d_(eff) = %% [mm]', 'Nyomott betönöv magasságának közelítő felvétele');
        $ec->def('xc', H3::n0(0.2*$ec->deff), 'x_c ~= 0.2*d_(eff) = %% [mm]', '$z = d_(eff)-x_c/2 = 0.9d_(eff)$ összefüggésből');

        $ec->h1('Erőjáték meghatározása', $ec->type);

        switch ($ec->type) {
            case '2piles':
                $ec->np = 2;
                $ec->def('theta', H3::n2(rad2deg(atan($ec->z/(0.5*$ec->xp)))), 'Theta = arctan(z/(0.5*x_p)) = %%°', 'Belső erők szöge');
                ($ec->theta < 26.0)?$ec->danger('$Theta < 26 [deg]$ (Javasolt min 30°)'):true;
                $ec->def('NEd', ceil($ec->FEdz/(2*sin(deg2rad($ec->theta))) + $ec->FEdy/(2*cos(deg2rad($ec->theta)))), 'N_(Ed) = F_(Ed,z)/(2*sin(Theta)) + F_(Ed,y)/(2*cos(Theta)) = %% [kN]');
                $ec->def('HEd', ceil($ec->FEdz/(2*tan(deg2rad($ec->theta)))), 'H_(Ed) = F_(Ed,z)/(2*tan(Theta)) = %% [kN]');
                $ec->note('HEd pillér vízszintes erő az egyik rácsrudat húzza, a másikat nyomja - vízszintes komponensben ez nem jelentkezik.');
                break;
        }

        $ec->h1('Húzott vasalás pillérsorok fölött');
        $ec->boo('fydFactor', ['', 'Folyáshatár csökkentése 0.7 faktorral repedéstágasság figyelembevételéhez'], false);
        $fydFactor = 1;
        if ($ec->fydFactor) {
            $fydFactor = 0.7;
        }
        $ec->def('As', ceil(($ec->HEd*1000)/($rebarMaterial->fyd*$fydFactor)), 'A_s = H_(Ed)/(f_(y,d)) = %% [mm^2]', 'Szükséges húzott vasmennyiség');
        $ec->numeric('bs', ['b_s', 'Húzott vasak kiosztási szélessége cölöp felett'], 1.0*$ec->Dp, 'mm', 'Jellemzően cölöpszélesség');
        $ec->boo('minusAs0', ['', 'Ø'.$ec->fix0.'/'.$ec->sx0.' alapháló figyelembevétele'], false, '');
        if ($ec->minusAs0) {
            $ec->def('As', ceil($ec->As - floor($ec->bs/$ec->sx0)*$ec->A($ec->fix0)), 'A_s = A_s - floor(b_s/s_(x,0))*A_(phi_(x,0)) = %% [mm^2]');
        }
        $ec->def('ns', ceil($ec->As/$ec->A($ec->fix)), 'n_s = ceil(A_s/A_(phi'.$ec->fix.')) = %%', 'Húzott fővas száma');
        $ec->def('sx', ceil($ec->bs/($ec->ns - 1)), 's_x = b_s/(n_s -1) = %% [mm]', 'Húzott fővas osztásköze');
        $ec->note('[3] 260.o.: Sorok száma s_x &lt; 2*phi_x feltétel alapján.');
        if ($ec->sx < 2*$ec->fix) {
            if ($ec->rowfix2) {
                $ns0 = ceil($ec->ns/2);
                $sx0 = H3::n0(($ec->bs/($ns0 - 1))/10)*10.0;
                $ns1 = $ec->ns - $ns0;
                $sx1 = H3::n0(($ec->bs/($ns1 - 1))/10)*10.0;
                $ec->label('yes', $ns0.'Ø'.$ec->fix.'/'.$sx0.' és '.$ns1.'Ø'.$ec->fix.'/'.$sx1.' 2 sorban');
                if ($sx0 < 2*$ec->fix || $sx1 < 2*$ec->fix) {
                    $ec->danger('3 vagy több sorban kéne a vasakat elhelyezni.');
                }
                $ec->sx = min($sx0, $sx1);
            } else {
                $ec->label('no', $ec->ns.'Ø'.$ec->fix.'/'. H3::n0($ec->sx/10)*10.0.' - 2 sor kellene!');
            }
            $ec->txt('', '$s_x < 2*phi_x$');
        } else {
            if ($ec->rowfix2) {
                $ns0 = ceil($ec->ns/2);
                $sx0 = H3::n0(($ec->bs/($ns0 - 1))/10)*10.0;
                $ns1 = $ec->ns - $ns0;
                $sx1 = H3::n0(($ec->bs/($ns1 - 1))/10)*10.0;
                $ec->label('no', $ns0.'Ø'.$ec->fix.'/'.$sx0.' és '.$ns1.'Ø'.$ec->fix.'/'.$sx1.' 2 sorban (1 elég)');
                $ec->sx = min($sx0, $sx1);
            } else {
                $ec->label('yes', $ec->ns.'Ø'.$ec->fix.'/'. H3::n0($ec->sx/10)*10.0.' 1 sorban');
            }
        }

        $ec->h2('Húzott vasak lehorgonyzása');
        $ec->note('A vasra ható keresztirányú nyomóerőtől eltekintve, 100%-ra kihasznált vas szálakat feltételezve.');
        if ($ec->sx < 3*$ec->fix) {
            $alphaa = 1;
            $ec->math('s_x lt 3*phi_x rarr alpha_a = 1', 'Kampózás nem vehető figyelembe');
        } else {
            $alphaa = 0.7;
            $ec->math('s_x gt 3*phi_x rarr alpha_a = 0.7', 'Kampózás figyelembe vehető');
        }
        if ($ec->__isset('fbd07')) {
            $ec->txt('Anyagminőségnél **'.(($concreteMaterial->fbd07)?'rossz tapadás':'jó tapadás').'** ($f_(b,d) = '.$concreteMaterial->fbd.'[N/(mm^2)]$) van beállítva');
        }
        $ec->numeric('nsPlus', ['n_(s+)', 'Többlet vas figyelembevétele: további Ø'.$ec->fix], 0, 'db', 'Húzott acélok 100%-nál alacsonyabb kihasználtságának figyelembevételéhez');

        $this->concreteCalculation->moduleAnchorageLength($ec->fix, $rebarMaterial->fyd, $concreteMaterial->fbd, $alphaa, $ec->ns, $ec->ns + $ec->nsPlus);

        $ec->h1('Húzott vasalás felkötő vasai', 'Kengyelek');
        $ec->note('[3] 260.o.: Rövid gerendáknál nyírási húzóerők nem lépnek fel. Felkötő vasak húzott vas lenyomódása ellen kellenek.');
        $ec->def('Fw', ceil($ec->FEdz/(1.5*$ec->np)), 'F_w = F_(Ed,z)/(1.5*n_p) = %% [kN]', 'Kengyelekkel felveendő erő, ahol $n_p$ a cölöpök száma');
        $ec->lst('nwb', ['2' => 2, '4' => 4, '6' => 6], ['n_(w,b)', 'Kengyel szárak száma'], 2);
        $sw = floor(($ec->xp0/ceil(($ec->Fw*1000)/(($ec->A($ec->fiw)*$ec->nwb)*$rebarMaterial->fyd))));
        $ec->def('sw', $sw, 's_w = x_(p,0)/(F_w/(A_(phi_w)*n_(w,b)*f_(y,d))) = %%', 'Szükséges kengyel távolság');
        $ec->success('$phi'.$ec->fiw.'"/"'.min(floor($sw/10)*10.0, 200).'×'.$ec->nwb/2.0.'$ kengyelezés');

        $ec->h1('A ferde nyomott betonöv nyomási teherbírásának ellenőrzése', 'Rácsmodellek alapján');
        $ec->note('[4.] 6.2.5');
        $ec->def('sigmaRdMax', $concreteMaterial->fcd, 'sigma_(Rd,max) = f_(cd) = %% [N/(mm^2)]');

        $c = 0.5*$ec->xp - 0.5*$ec->acol - 0.1*$ec->deff + 0.5*$ec->Dp;
        $gamma = atan($ec->z/$c);
        $beta = deg2rad(90 - $ec->theta);
        $epszilon = deg2rad(180 - rad2deg($gamma) - rad2deg($beta));
        $h2 = floor($ec->Dp*(sin($gamma)/sin($epszilon)));
        $ec->def('h2', $h2, 'h_2 = %% [mm]', 'Szerkesztésből adódó rácsrúd magasság cölöp felett');

        $a = 0.5*$ec->xp + 0.5*$ec->acol - 0.5*$ec->Dp + $ec->deff*0.1;
        $alfa = atan($ec->z/$a);
        $h1 = floor(sin($alfa)*($ec->acol+0.2*$ec->deff)); // TODO szembe oldalra állít merőlegest, nem az erővonalra most
        $ec->def('h1', $h1, 'h_1 = %% [mm]', 'Szerkesztésből adódó rácsrúd magasság pillér alatt');

        $ec->note('Szerkesztéssel ellenőrizendő!');

        $ec->def('Ac', min($ec->Dp*$ec->h2*(pi()/4), $ec->Dp*$ec->h1), 'A_c = min{(D_p*h_2*pi/4),(D_p*h_1):} = %% [mm^2]', 'Nyomott rácsrúd felülete');
        $ec->def('NRd', floor($ec->Ac*$concreteMaterial->fcd*0.001), 'N_(Rd) = A_c*f_(cd) = %% [kN]', 'Nyomott rácsrúd ellenállása');
        $ec->label($ec->NEd/$ec->NRd, 'Kihasználtság');

        $ec->h2('Fejtömb alsó csomópontjának ellenőrzése');
        $ec->note('[4.]: 6.5.4 (4) b)');
        $ec->def('sigmaRdMax2', 0.85*(1 - $concreteMaterial->fck/250)*$concreteMaterial->fcd, 'sigma_(Rd,max,2) = k_2*(1-f_(ck)/250)*f_(cd) = %% [N/(mm^2)]');
        $ec->def('Ac2', $ec->Dp*$ec->h2*(pi()/4), 'A_(c,2) = %% [mm^2]');
        $ec->def('NRd2', floor($ec->Ac2*$ec->sigmaRdMax2*0.001), 'N_(Rd,2) = A_(c,2)*sigma_(Rd,max,2) = %% [kN]');
        $ec->label($ec->NEd/$ec->NRd2, 'Kihasználtság');

        $ec->h2('Fejtömb felső csomópontjának ellenőrzése');
        $ec->note('[4.]: 6.5.4 (4) a)');
        $ec->def('sigmaRdMax1', 1.0*(1 - $concreteMaterial->fck/250)*$concreteMaterial->fcd, 'sigma_(Rd,max,1) = k_1*(1-f_(ck)/250)*f_(cd) = %% [N/(mm^2)]');
        $ec->def('Ac1', $ec->Dp*$ec->h1, 'A_(c,1) = %% [mm^2]');
        $ec->def('NRd1', floor($ec->Ac1*$ec->sigmaRdMax1*0.001), 'N_(Rd,1) = A_(c,2)*sigma_(Rd,max) = %% [kN]');
        $ec->label($ec->NEd/$ec->NRd1, 'Kihasználtság');

        $ec->h2('Ferde betonöv ellenőrzése nyírási vasalást nem igénylő szerkezet ferde nyomási teherbírásával ellenőrizve');
        $ec->note('[4.]: 6.2.2 (6)');
        $ec->def('VRdmax', floor(0.5*$ec->Dp*$ec->deff*0.6*(1 - $concreteMaterial->fck/250)*$concreteMaterial->fcd*0.001), 'V_(Rd,max) = 0.5*D_p*d_(eff)*0.6*(1-f_(ck)/250)*f_(cd) = %% [kN]');
        $ec->label($ec->NEd/$ec->VRdmax, 'Kihasználtság');
        $ec->txt('', '$N_(Ed)/V_(Rd,max)$');

        $ec->h1('Nyírás és átszúródás ellenőrzése', '`TODO`');
// ============================ old ================================
   /*

        $ec->hr();
        $ec->VEd = $ec->FEdz;
        $ec->hr();


        $ec->numeric('beta', ['beta', 'Teherelosztás'], 1.15, '', 'Tehereloszlás egyenlőtlenségéből származó hatás tényezője');
        $ec->h2('Segéd geometria a helyettesítő terhelő pillér méretfelvételéhez');
        $ec->numeric('hv', ['h_v', 'Teherelosztó lemez vastagsága'], 0, 'mm', '2/1-es szétterjedéssel számolva');
        $ec->def('hsum', $ec->h + $ec->hv, 'h_(sum) = h + h_v = %% [mm]', 'Teljes vastagság');

            $ec->numeric('a', ['a', 'Pillér méret'], 800, 'mm');
            $ec->numeric('b', ['b', 'Pillér méret'], 800, 'mm', 'Számításban használt négyszög pillérek méretei');
            if ($ec->hv != 0) {
                $ec->def('a', $ec->a + $ec->hv, 'a := a_(eff) = a + h_v = %% [mm]');
                $ec->def('b', $ec->b + $ec->hv, 'b := b_(eff) = b + h_v = %% [mm]');
            }


        $ec->h1('Beton tönkremenetele ferde nyomásra', '$u_0$ keresztmetszet ellenőrzése - a nyírási teherbírás felső korlátjának ellőrzése');
        $ec->note('Minimum lemezvastagság meghatározásához');

            $ec->def('u0', floor(2*($ec->a + $ec->b)), 'u_0 = 2*(a+b) = %% [mm]', 'Nyírt kerület pillér tövénél');

        $ec->note('Belső pillérként számolva: *[Vasbetonszerkezetek (2016) 6.8.1 a)]*');
        $ec->def('v', 0.6*(1 - $ec->cfck/250), 'v = 0.6*(1-f_(ck)/250) = %%', 'Beton szilárdsági tényező - segédmennyiség');
        $ec->def('vRdmax', 0.5*$ec->v*$ec->cfcd, 'v_(Rd,max) = 0.5*v*f_(cd) = %% [N/(mm^2)]', 'Nyomott beton rácsrúd teherbírásához tartozó fajlagos határnyíróeró. (Vasalással maximálisan átadható fajlagos nyíróerő tervezési értéke)');
        $ec->def('vEd', \H3::n2(($ec->beta*($ec->VEd*1000))/($ec->u0*$ec->deff)), 'v_(Ed) = (beta*V_(Ed))/(u_0*d_(eff)) = %% [N/(mm^2)]', 'Nyíróerő fajlagos értéke');
        $ec->label($ec->vEd/$ec->vRdmax, 'kihasználtság $v_(Ed)/v_(Rd,max)$');

        $ec->h1('Minimális fejtömb vastagság meghatározása', '');
        $ec->h2('Megfeleltetés átszúródásra $u_0$ keresztmetszetben', '');
        $ec->def('hminu0', ceil(($ec->beta*($ec->VEd*1000))/$ec->vRdmax/$ec->u0 + ($ec->c + ($ec->fix + $ec->fiy)/2)), 'h_(min,u_0) = (beta*V_(Ed))/(v_(Rd,max)/u_0) + (c+(phi_x+phi_y)/2) = %% [mm]');
        if ($ec->h >= $ec->hminu0) {
            $ec->label('yes', 'Alkalmazott lemezvastagság megfelel');
        } else {
            $ec->label('no', 'Alkalmazott lemezvastagság túl kicsi');
        }

   */
    }
}
