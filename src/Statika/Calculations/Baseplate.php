<?php declare(strict_types = 1);
/**
 * Analysis of baseplate in RC elements according to Eurocodes - Calculation class for Statika framework
 * (c) Bence VÁNKOS | https://structure.hu | https://github.com/r3sist/ecc-calculations
 */

namespace Statika\Calculations;

use H3;
use resist\SVG\SVG;
use Statika\EurocodeInterface;

Class Baseplate
{
    private Concrete $concrete;

    public function __construct(Concrete $concrete)
    {
        $this->concrete = $concrete;
    }

    /**
     * @param Ec $ec
     * @throws \Profil\Exceptions\InvalidSectionNameException
     */
    public function calc(EurocodeInterface $ec): void
    {
        $ec->region0('mat', 'Anyagok');
            $ec->structuralSteelMaterialListBlock('steelMaterialName', 'S235', ['', 'Lemez anyagminőség']);
//            $ec->spreadMaterialData($ec->steelMaterialName, 's');
            $ec->steelMaterialListBlock('anchorMaterialName', 'B500', ['', 'Horgony anyagminőség']);
//            $ec->spreadMaterialData($ec->anchorMaterialName, 'a');
            $ec->concreteMaterialListBlock('concreteMaterialName');
//            $ec->spreadMaterialData($ec->concreteMaterialName, 'c');

            $steelMaterial = $ec->getMaterial($ec->steelMaterialName); // TODO show table like old spreadMaterialData()
            $anchorMaterial = $ec->getMaterial($ec->anchorMaterialName);
            $concreteMaterial = $ec->getMaterial($ec->concreteMaterialName);
        $ec->region1();

        $ec->region0('quicktbl', 'Egyszerűsített táblázat bekötő karmokhoz');
            $scheme = ['Átmérő $[mm]$', 'Keresztmetszet $[mm^2]$', 'Húzás $[kN]$', 'Nyírás $[kN]$', 'Varrat gyökméret húzáshoz $[mm]$'];
            $rows = [
                [12, floor($ec->A(12)), H3::n1($steelMaterial->fy*floor($ec->A(12))/1000), H3::n1(($steelMaterial->fy/sqrt(3))*floor($ec->A(12))/1000), 4],
                [14, floor($ec->A(14)), H3::n1($steelMaterial->fy*floor($ec->A(14))/1000), H3::n1(($steelMaterial->fy/sqrt(3))*floor($ec->A(14))/1000), 5],
                [16, floor($ec->A(16)), H3::n1($steelMaterial->fy*floor($ec->A(16))/1000), H3::n1(($steelMaterial->fy/sqrt(3))*floor($ec->A(16))/1000), 6],
                [20, floor($ec->A(20)), H3::n1($steelMaterial->fy*floor($ec->A(20))/1000), H3::n1(($steelMaterial->fy/sqrt(3))*floor($ec->A(20))/1000), 7],
                [25, floor($ec->A(25)), H3::n1($steelMaterial->fy*floor($ec->A(25))/1000), H3::n1(($steelMaterial->fy/sqrt(3))*floor($ec->A(25))/1000), 9],
            ];
            $ec->tbl($scheme, $rows, 'quicktblTbl', '$f_(yd) = '.$steelMaterial->fy.' [N/(mm^2)]$ Nem karom anyagával számol!');
            $ec->note('[K.G. *MBM csomópontok*]');
        $ec->region1();

        $ec->info0('Erők:');
            $ec->numeric('NEd', ['N_(Ed)', 'Húzóerő'], 56, 'kN','Bázislemezre ható eredő húzóerő');
            $ec->numeric('VEd', ['V_(Ed)', 'Nyróerő'], 100, 'kN','Bázislemezre ható eredő nyíróerő');
            $ec->numeric('MEd', ['M_(Ed)', 'Nyomaték'], 0, 'kN','Húzott-nyomott szál között ható nyomaték');
        $ec->info1();

        $ec->info0('Geometria:');
            $ec->numeric('tb', ['t_b', 'Bázislemez vastagság'], 16, 'mm','');
            $ec->numeric('ba', ['b_a', 'Szélső lehorgonyzások közti vízszintes távolság'], 250, 'mm', '');
            $ec->numeric('ha', ['h_a', 'Szélső lehorgonyzások közti függőleges távolság'], 250, 'mm', '');
            $ec->input('nr', ['n_r', 'Horgony sorok száma'], 2, '', '', 'numeric|min_numeric,2');
            $ec->input('nc', ['n_c', 'Horgony oszlopok száma'], 2, '', '', 'numeric|min_numeric,2');
            $ec->input('nu', ['n_u', 'Felső, húzott lehorgonyzás sorok száma'], 1, '', 'Húzás felvételéhez', 'numeric|max_numeric,'.H3::n0($ec->nr - 1.0).'|min_numeric,1');
            $ec->rebarList('phi', 16, ['phi', 'Horgony átmérő'], '');
            $ec->input('ht', ['h_t', 'Húzott sorok közti távolság'], 50, 'mm', '', 'numeric|max_numeric,'.H3::n0($ec->ha/($ec->nr - 1)).'|min_numeric,'. 2.0*$ec->phi);
            $ec->boo('useShearProfile', ['', 'Nyírószelvény alkalmazása'], false);
            if ($ec->useShearProfile) {
                $ec->boo('useCustomShearA', ['', 'Egyedi keresztmetszet megadása'], false);
                if ($ec->useCustomShearA) {
                    $ec->numeric('shearProfileA', ['A_v', 'Nyírási keresztmetszet'], 0, 'mm^2');
                } else {
                    $ec->sectionFamilyListBlock('shearProfileFamily');
                    $ec->sectionListBlock($ec->shearProfileFamily, 'shearProfileName');
//                    $ec->spreadSectionData($ec->shearProfileName, true, 'shearProfileData');
                    $shearProfile = $ec->getSection($ec->shearProfileName);
                    $ec->lst('shearProfileProp', ['Ax teljes km alkalmazása' => 'Ax', 'Az' => 'Az', 'Ay' => 'Ay'], ['', 'Nyírási felület választása'], 'Ax');
                    $ec->def('shearProfileA', $shearProfile->{'_'.$ec->shearProfileProp}*100, 'A_(v, "'.$ec->shearProfileName.'") = %% [mm^2]; "'.$ec->steelMaterialName.'"');
                }
            }
        $ec->info1();

        $ec->def('nv', ($ec->nr - $ec->nu)*$ec->nc, 'n_v = %%', 'Nyíráshozhoz figyelembevett horgonyok száma');
        $ec->def('nt', $ec->nu*$ec->nc, 'n_t = %%', 'Húzáshoz figyelembevett horgonyok száma');
        $ec->def('As', H3::n0($ec->A($ec->phi)), 'A_s = %% [mm^2]', 'Egy horgony keresztmetszete');

        // SVG init
        $e = 3*$ec->phi; // Peremtávolság
        $xp = 2*$e + $ec->ba; // Plate dimensions
        $yp = 2*$e + $ec->ha;
//        $pv = $ec->ha/($ec->nr - 1); // Vertical order
        $ph = $ec->ba/($ec->nc - 1); // Horizontal order
        $lever = $ec->ha - ($ec->nu - 1)*$ec->ht/2 - ($ec->nr - $ec->nu - 1)*$ec->ht/2;
        $lever0 = $e + (($ec->nu - 1)*$ec->ht)/2;
        $svg = new SVG(600, 400); // TODO DI
        // Column
        $svg->setFill('#eeeeee');
        $svg->addRectangle(10, 10, 60, 380);
        $svg->setFill('blue');
        $svg->addRectangle(60, 50, 10, 300);
        $svg->reset();
        // Plate
        $svg->makeRatio(350, 300, $xp, $yp);
        $svg->setColor('blue');
        $svg->addRectangle(0, 0, $xp, $yp, 200, 50);
        $svg->setFill('red');
        // Anchors for tension
        $yi = 0;
        for ($row = 0; $row <= $ec->nu - 1; $row++) {
            for ($col = 0; $col <= $ec->nc - 1; $col++) {
                $xi = ($e + $col*$ph);
                $yi = ($e + $row*$ec->ht);
                $svg->addCircle($xi, $yi, 5, 200, 50);
            }
            $svg->addLineRatio(0, $yi, 20, $yi, 40, 50, false, true);
        }
        // Anchors for pressure
        $svg->setFill('green');
        for ($row = 0; $row <= ($ec->nr - $ec->nu) - 1; $row++) {
            for ($col = 0; $col <= $ec->nc - 1; $col++) {
                $xi = ($xp - ($e + $col*$ph));
                $yi = ($yp - ($e + $row*$ec->ht));
                $svg->addCircle($xi, $yi, 5, 200, 50);
            }
            $svg->addLineRatio(0, $yi, 20, $yi, 40, 50, false, true);
        }
        // Dimensions
        $svg->setColor('magenta');
        $svg->addDimH(0, $xp, 395, $xp, 200); // Plate horizontal
        $svg->addDimV(0, $yp, 570, $yp, 50); // Plate vertical
        $svg->addDimV($e, $yp - 2*$e, 545, $yp - 2*$e, 50); // Plate inner vertical
        $svg->addDimH($e, $xp - 2*$e, 370, $xp - 2*$e, 200); // Plate inner horizontal
        $svg->addDimH(0, $e, 30, $e, 200); // e2
        $svg->addDimH($e, $ph, 30, H3::n0($ph), 200); // p2
        ($ec->nu > 1)?$svg->addDimV($e, $ec->ht, 180, H3::n0($ec->ht), 50):false; // p1
        $svg->addDimV(0, $e, 180, $e, 50); // e1
        $svg->addDimV($lever0, $lever, 520, $lever, 50); // vertical lever
        $svg->reset();
        $svg->setColor('magenta');
        $svg->addDimH(60, 10, 390, $ec->tb); // Cross-section of plate
        // Texts & symbols
        $svg->setColor('black');
        $svg->addSymbol(100, 200, 'arrow-right');
        $svg->addSymbol(80, 200, 'back-left');
        $svg->addText(120, 210, 'M, N');
        $svg->addSymbol(80, 220, 'arrow-down');
        $svg->addText(100, 240, 'V');
        $ec->svg($svg);

        $ec->h1('Húzott horgonyok ellenőrzése');
        $ec->def('NRda', H3::n2(($ec->As*$anchorMaterial->fyd)/1000), 'N_(Rd,a) = A_s * f_(yd,a) = %% [kN]', 'Egy horgony húzási ellenállása');
        $ec->def('lever', $lever, 'l = %% [mm]', 'Nyomaték erőkarja húzott és nyomott sorok súlypontjához');
        $ec->def('NEdM', H3::n2($ec->MEd/($ec->lever/1000)), 'N_(Ed,M) = M_(Ed)/l = %% [kN]', 'Nyomatékból származó erőpár');
        $ec->note('Húzásra csak a felső sor horgonyai vannak figyelembe véve!');
        $ec->def('NRdt', $ec->NRda*$ec->nt, 'N_(Rd,t) = n_t*N_(Rd,a) = %% [kN]', 'Húzott (felső) horgonyok húzási ellenállása');
        $ec->txt('Húzott (felső) horgonyok kihasználtsága:');
        $ec->def('ntreq', ceil((4/(($ec->phi ** 2)*M_PI))*($ec->NEd*1000/$anchorMaterial->fyd)), 'n_(t,req) = %%', 'Minimális húzott horgonyszám');
        /** @noinspection AdditionOperationOnArraysInspection */
        $ec->def('NEdsum', $ec->NEd + $ec->NEdM, 'N_(Ed,sum) = N_(Ed) + N_(Ed,M) = %% [kN]');
        $ec->label($ec->NEdsum/$ec->NRdt, 'húzási kihasználtság');

        $ec->h1('Nyírt horgonyok ellenőrzése');
        if ($ec->useShearProfile) {
            $ec->info0('Nyírt csap számítása:');
                $ec->def('VplRdp', H3::n1(($ec->shearProfileA*$anchorMaterial->fyd)/(sqrt(3)*$ec::GM0*1000)), 'V_(pl,Rd,p) = %% [kN]', 'Nyírási csap ellenállása');
                $ec->VEd -= $ec->VplRdp;
                $ec->math('V_(Ed) := V_(Ed) - V_(pl,Rd,p) = '.$ec->VEd.'  [kN]', 'Nyírási igénybevétel csökkentve a továbbiakban mindenhol');
            $ec->info1();
        }
        $ec->note('Nyírásra csak az alsó sor horgonyai vannak figyelembe véve!');
        $ec->def('VplRda', H3::n1(($ec->A($ec->phi)*$anchorMaterial->fyd)/(sqrt(3)*$ec::GM0*1000)), 'V_(pl,Rd,a) = %% [kN]', 'Egy horgony nyírási ellenállása');
        $ec->def('VplRd', $ec->nv*$ec->VplRda, 'V_(pl,Rd) = n_v*V_(pl,Rd,a) = %% [kN]', 'Nyírt (alsó) horgonyok nyírási ellenállása');
        $ec->txt('Nyírt (alsó) horgonyok kihasználtsága:');
        $ec->label($ec->VEd/$ec->VplRd,'nyírási kihasználtság');

        $ec->h1('Nyírás-húzás interakció összes horgonyra');
        /** @noinspection AdditionOperationOnArraysInspection */
        $Uvt = $ec->VEd/(($ec->nt + $ec->nv)*$ec->VplRda) + $ec->NEd/(1.4*($ec->nt + $ec->nv)*$ec->NRda);
        $ec->math('V_(Ed)/((n_t+n_v)*V_(pl,Rd,a)) + N_(Ed)/(1.4*(n_t+n_v)*N_(Rd,a)) = '.H3::n1($Uvt*100.0).'[%]', '$M_(Ed)$ figyelmenkívül hagyásával');
        $ec->label($Uvt, 'interakciós kihaználtság');

        $ec->h1('Hajlított lemez ellenőrzése', 'Függőleges erőkarra, húzásból és nyomatékból');
        $ec->boo('useRigid', ['', 'Befogott lemez'], false, '');
        ($ec->useRigid)?$rigidFactor = 8:$rigidFactor = 4;
        $ec->def('MEdp',$ec->MEd/2 + ($ec->NEd*($ec->lever/1000))/$rigidFactor, 'M_(Ed,p) = M_(Ed)/2 + (N_(Ed)*l)/'.$rigidFactor.' = %% [kNm]','Nyomaték a lemezben');
        $ec->def('Ws',floor(($ec->ba* ($ec->tb ** 2))/6), 'W_s = (b_a*t_b^2)/6 = %% [mm^3]', 'Gyenge tengely körüli keresztmetszeti modulus');
        $ec->def('sigmaEds',H3::n1(($ec->MEdp/$ec->Ws)*1000000), 'sigma_(Ed,s) = M_(Ed)/W_s = %%; f_(y) = '.$steelMaterial->fy.' [N/(mm^2)]', 'Lemez feszültség');
        $ec->label($ec->sigmaEds/$steelMaterial->fy,'lemez rugalmas kihasználtság');

        $ec->h1('Lehorgonyzási hossz számítása');
        $ec->boo('usenprov', ['', 'Húzott horgony kihasználtság figyelembevétele'], false, '$n_(t,req)/n_t = '.H3::n3($ec->ntreq/$ec->nt).'$ alkalmazható csökkentő tényező húzás alapján');
        $nprov = 1;
        $nreq = 1;
        if ($ec->usenprov) {
            $nprov = $ec->nt;
            $nreq = $ec->ntreq;
        }
        $ec->lst('alphaa', ['Egyenes: 1.0' => 1.0, 'Kampó, hurok, hajlítás: 0.7' => 0.7], ['alpha_a', 'Lehorgonyzás módja'], '1.0', '');
        $ec->txt('Anyagminőségnél **'.((0)?'rossz tapadás':'jó tapadás').'** ($f_(b,d) = '.$concreteMaterial->fbd.'[N/(mm^2)]$) van beállítva'); // TODO jó-rossz tapadás

        $this->concrete->moduleAnchorageLength($ec->phi, $anchorMaterial->fyd, $concreteMaterial->fbd, $ec->alphaa, $nreq, $nprov);

        $ec->h1('Horgony-varrat meghatározása', '');
        $wFactor = 1;
        $ec->boo('useDoubleWeld', ['', 'Dupla varrat alkalmazása'], true, '');
        if ($ec->useDoubleWeld) {
            $wFactor = 2;
        }
        $ec->def('lw', H3::n1($wFactor*$ec->phi*M_PI), 'l_w = %% [mm]', 'Egy- vagy kétszeres varrathossz horgony kerülete mentén, teljes kerület figyelembevételével');
        $ec->def('betaw', $steelMaterial->betaw, 'beta_w = %%', 'Hegesztési tényező');
        $ec->def('NwEd', ($ec->NEdsum/$ec->nt)/$ec->lw * 1000, 'N_(w,Ed) = N_(Ed,sum)/n_t/l_w = %% [(kN)/m]', 'Fajlagos igénybevétel húzásból');
        $ec->def('VwEd', ($ec->VEd/$ec->nv)/$ec->lw * 1000, 'V_(w,Ed) = V_(Ed)/n_v/l_w = %% [(kN)/m]', 'Fajlagos igénybevétel nyírásból');
        $ec->def('a', ceil((max($ec->NwEd, $ec->VwEd)*sqrt(3)*$ec->betaw*$ec::GM2)/$steelMaterial->fu), 'a = ceil((max{(N_(w,Ed)),(V_(w,Ed)):}*sqrt(3)*beta_w*gamma_(M2))/(f_u)) = %% [mm]', 'Minimális varrat gyökméret');
        $ec->success0();
        $ec->math('a = '.$ec->a.' [mm]', 'Minimális varrat gyökméret');
        $ec->success1();

        $ec->h1('Beton pecsétnyomás ellenőrzése nyírt csapok alatt');
        $ec->note('*[Vasbeton szerkezetek (2017) 6.10. 55.o.]*. Nyíróerő átadás $3*phi$ hosszon számítva, fél hengerpalást felületre. Térbeli feszültségállapot szabadon felléphet.');
        $ec->def('FRd', H3::n2($ec->phi*M_PI*0.5*3*$ec->phi*3*$concreteMaterial->fcd/1000), 'F_(Rd) = A_(cl)*alpha*f_(cd) = (phi*pi)/2*(3*phi)*3*f_(cd) = %% [kN]');
        $ec->def('FEd', H3::n2($ec->VEd/$ec->nv), 'F_(Ed) = V_(Ed)/n_v = %% [kN]');
        $ec->label($ec->FEd/$ec->FRd,'kihasználtság');

        //TODO Egyszerűsített Csap táblázat
    }
}
