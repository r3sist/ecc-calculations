<?php declare(strict_types = 1);
// Analysis of baseplate in RC elements according to Eurocodes - Calculation class for ECC framework
// (c) Bence VÁNKOS | https://structure.hu | https://github.com/r3sist/ecc-calculations

namespace Calculation;

use Base;
use Ecc\Blc;
use Ec\Ec;
use H3;
use resist\SVG\SVG;

Class Baseplate
{
    private Concrete $concrete;

    public function __construct(Concrete $concrete)
    {
        $this->concrete = $concrete;
    }

    public function calc(Base $f3, Blc $blc, Ec $ec): void
    {
        $blc->region0('mat', 'Anyagok');
//            $ec->matList('steelMaterialName', 'S235', ['', 'Lemez anyag'], 'steel');
            $ec->structuralSteelMaterialListBlock('steelMaterialName', 'S235', ['', 'Lemez anyagminőség']);
            $ec->spreadMaterialData($f3->_steelMaterialName, 's');
//            $ec->matList('anchorMaterialName', 'B500', ['', 'Horgony anyag'], 'steels');
            $ec->steelMaterialListBlock('anchorMaterialName', 'B500', ['', 'Horgony anyagminőség']);
            $ec->spreadMaterialData($f3->_anchorMaterialName, 'a');
//            $ec->matList('concreteMaterialName', 'C25/30', ['', 'Beton anyag'], 'concrete');
            $ec->concreteMaterialListBlock('concreteMaterialName');
            $ec->spreadMaterialData($f3->_concreteMaterialName, 'c');
        $blc->region1();

        $blc->region0('quicktbl', 'Egyszerűsített táblázat bekötő karmokhoz');
            $scheme = ['Átmérő $[mm]$', 'Keresztmetszet $[mm^2]$', 'Húzás $[kN]$', 'Nyírás $[kN]$', 'Varrat gyökméret húzáshoz $[mm]$'];
            $rows = [
                [12, floor($ec->A(12)), H3::n1($f3->_sfy*floor($ec->A(12))/1000), H3::n1(($f3->_sfy/sqrt(3))*floor($ec->A(12))/1000), 4],
                [14, floor($ec->A(14)), H3::n1($f3->_sfy*floor($ec->A(14))/1000), H3::n1(($f3->_sfy/sqrt(3))*floor($ec->A(14))/1000), 5],
                [16, floor($ec->A(16)), H3::n1($f3->_sfy*floor($ec->A(16))/1000), H3::n1(($f3->_sfy/sqrt(3))*floor($ec->A(16))/1000), 6],
                [20, floor($ec->A(20)), H3::n1($f3->_sfy*floor($ec->A(20))/1000), H3::n1(($f3->_sfy/sqrt(3))*floor($ec->A(20))/1000), 7],
                [25, floor($ec->A(25)), H3::n1($f3->_sfy*floor($ec->A(25))/1000), H3::n1(($f3->_sfy/sqrt(3))*floor($ec->A(25))/1000), 9],
            ];
            $blc->tbl($scheme, $rows, 'quicktblTbl', '$f_(yd) = '.$f3->_sfy.' [N/(mm^2)]$ Nem karom anyagával számol!');
            $blc->note('[K.G. *MBM csomópontok*]');
        $blc->region1();

        $blc->info0('Erők:');
            $blc->numeric('NEd', ['N_(Ed)', 'Húzóerő'], 56, 'kN','Bázislemezre ható eredő húzóerő');
            $blc->numeric('VEd', ['V_(Ed)', 'Nyróerő'], 100, 'kN','Bázislemezre ható eredő nyíróerő');
            $blc->numeric('MEd', ['M_(Ed)', 'Nyomaték'], 0, 'kN','Húzott-nyomott szál között ható nyomaték');
        $blc->info1();

        $blc->info0('Geometria:');
            $blc->numeric('tb', ['t_b', 'Bázislemez vastagság'], 16, 'mm','');
            $blc->numeric('ba', ['b_a', 'Szélső lehorgonyzások közti vízszintes távolság'], 250, 'mm', '');
            $blc->numeric('ha', ['h_a', 'Szélső lehorgonyzások közti függőleges távolság'], 250, 'mm', '');
            $blc->input('nr', ['n_r', 'Horgony sorok száma'], 2, '', '', 'numeric|min_numeric,2');
            $blc->input('nc', ['n_c', 'Horgony oszlopok száma'], 2, '', '', 'numeric|min_numeric,2');
            $blc->input('nu', ['n_u', 'Felső, húzott lehorgonyzás sorok száma'], 1, '', 'Húzás felvételéhez', 'numeric|max_numeric,'.H3::n0($f3->_nr - 1.0).'|min_numeric,1');
            $ec->rebarList('phi', 16, ['phi', 'Horgony átmérő'], '');
            $blc->input('ht', ['h_t', 'Húzott sorok közti távolság'], 50, 'mm', '', 'numeric|max_numeric,'.H3::n0($f3->_ha/($f3->_nr - 1)).'|min_numeric,'. 2.0*$f3->_phi);
            $blc->boo('useShearProfile', ['', 'Nyírószelvény alkalmazása'], false);
            if ($f3->_useShearProfile) {
                $blc->boo('useCustomShearA', ['', 'Egyedi keresztmetszet megadása'], false);
                if ($f3->_useCustomShearA) {
                    $blc->numeric('shearProfileA', ['A_v', 'Nyírási keresztmetszet'], 0, 'mm^2');
                } else {
                    $ec->sectionFamilyList('shearProfileFamily');
                    $ec->sectionList($f3->_shearProfileFamily, 'shearProfileName');
                    $ec->spreadSectionData($f3->_shearProfileName, true, 'shearProfileData');
                    $blc->lst('shearProfileProp', ['Ax teljes km alkalmazása' => 'Ax', 'Az' => 'Az', 'Ay' => 'Ay'], ['', 'Nyírási felület választása'], 'Ax');
                    $blc->def('shearProfileA', $f3->_shearProfileData[$f3->_shearProfileProp]*100, 'A_(v, "'.$f3->_shearProfileName.'") = %% [mm^2]; "'.$f3->_steelMaterialName.'"');
                }
            }
        $blc->info1();

        $blc->def('nv', ($f3->_nr - $f3->_nu)*$f3->_nc, 'n_v = %%', 'Nyíráshozhoz figyelembevett horgonyok száma');
        $blc->def('nt', $f3->_nu*$f3->_nc, 'n_t = %%', 'Húzáshoz figyelembevett horgonyok száma');
        $blc->def('As', H3::n0($ec->A($f3->_phi)), 'A_s = %% [mm^2]', 'Egy horgony keresztmetszete');

        // SVG init
        $e = 3*$f3->_phi; // Peremtávolság
        $xp = 2*$e + $f3->_ba; // Plate dimensions
        $yp = 2*$e + $f3->_ha;
//        $pv = $f3->_ha/($f3->_nr - 1); // Vertical order
        $ph = $f3->_ba/($f3->_nc - 1); // Horizontal order
        $lever = $f3->_ha - ($f3->_nu - 1)*$f3->_ht/2 - ($f3->_nr - $f3->_nu - 1)*$f3->_ht/2;
        $lever0 = $e + (($f3->_nu - 1)*$f3->_ht)/2;
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
        for ($row = 0; $row <= $f3->_nu - 1; $row++) {
            for ($col = 0; $col <= $f3->_nc - 1; $col++) {
                $xi = ($e + $col*$ph);
                $yi = ($e + $row*$f3->_ht);
                $svg->addCircle($xi, $yi, 5, 200, 50);
            }
            $svg->addLineRatio(0, $yi, 20, $yi, 40, 50, false, true);
        }
        // Anchors for pressure
        $svg->setFill('green');
        for ($row = 0; $row <= ($f3->_nr - $f3->_nu) - 1; $row++) {
            for ($col = 0; $col <= $f3->_nc - 1; $col++) {
                $xi = ($xp - ($e + $col*$ph));
                $yi = ($yp - ($e + $row*$f3->_ht));
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
        ($f3->_nu > 1)?$svg->addDimV($e, $f3->_ht, 180, H3::n0($f3->_ht), 50):false; // p1
        $svg->addDimV(0, $e, 180, $e, 50); // e1
        $svg->addDimV($lever0, $lever, 520, $lever, 50); // vertical lever
        $svg->reset();
        $svg->setColor('magenta');
        $svg->addDimH(60, 10, 390, $f3->_tb); // Cross-section of plate
        // Texts & symbols
        $svg->setColor('black');
        $svg->addSymbol(100, 200, 'arrow-right');
        $svg->addSymbol(80, 200, 'back-left');
        $svg->addText(120, 210, 'M, N');
        $svg->addSymbol(80, 220, 'arrow-down');
        $svg->addText(100, 240, 'V');
        $blc->svg($svg);

        $blc->h1('Húzott horgonyok ellenőrzése');
        $blc->def('NRda', H3::n2(($f3->_As*$f3->_afyd)/1000), 'N_(Rd,a) = A_s * f_(yd,a) = %% [kN]', 'Egy horgony húzási ellenállása');
        $blc->def('lever', $lever, 'l = %% [mm]', 'Nyomaték erőkarja húzott és nyomott sorok súlypontjához');
        $blc->def('NEdM', H3::n2($f3->_MEd/($f3->_lever/1000)), 'N_(Ed,M) = M_(Ed)/l = %% [kN]', 'Nyomatékból származó erőpár');
        $blc->note('Húzásra csak a felső sor horgonyai vannak figyelembe véve!');
        $blc->def('NRdt', $f3->_NRda*$f3->_nt, 'N_(Rd,t) = n_t*N_(Rd,a) = %% [kN]', 'Húzott (felső) horgonyok húzási ellenállása');
        $blc->txt('Húzott (felső) horgonyok kihasználtsága:');
        $blc->def('ntreq', ceil((4/(($f3->_phi ** 2)*M_PI))*($f3->_NEd*1000/$f3->_afyd)), 'n_(t,req) = %%', 'Minimális húzott horgonyszám');
        /** @noinspection AdditionOperationOnArraysInspection */
        $blc->def('NEdsum', $f3->_NEd + $f3->_NEdM, 'N_(Ed,sum) = N_(Ed) + N_(Ed,M) = %% [kN]');
        $blc->label($f3->_NEdsum/$f3->_NRdt, 'húzási kihasználtság');

        $blc->h1('Nyírt horgonyok ellenőrzése');
        if ($f3->_useShearProfile) {
            $blc->info0('Nyírt csap számítása:');
                $blc->def('VplRdp', H3::n1(($f3->_shearProfileA*$f3->_afyd)/(sqrt(3)*$f3->__GM0*1000)), 'V_(pl,Rd,p) = %% [kN]', 'Nyírási csap ellenállása');
                $f3->_VEd -= $f3->_VplRdp;
                $blc->math('V_(Ed) := V_(Ed) - V_(pl,Rd,p) = '.$f3->_VEd.'  [kN]', 'Nyírási igénybevétel csökkentve a továbbiakban mindenhol');
            $blc->info1();
        }
        $blc->note('Nyírásra csak az alsó sor horgonyai vannak figyelembe véve!');
        $blc->def('VplRda', H3::n1(($ec->A($f3->_phi)*$f3->_afyd)/(sqrt(3)*$f3->__GM0*1000)), 'V_(pl,Rd,a) = %% [kN]', 'Egy horgony nyírási ellenállása');
        $blc->def('VplRd', $f3->_nv*$f3->_VplRda, 'V_(pl,Rd) = n_v*V_(pl,Rd,a) = %% [kN]', 'Nyírt (alsó) horgonyok nyírási ellenállása');
        $blc->txt('Nyírt (alsó) horgonyok kihasználtsága:');
        $blc->label($f3->_VEd/$f3->_VplRd,'nyírási kihasználtság');

        $blc->h1('Nyírás-húzás interakció összes horgonyra');
        /** @noinspection AdditionOperationOnArraysInspection */
        $Uvt = $f3->_VEd/(($f3->_nt + $f3->_nv)*$f3->_VplRda) + $f3->_NEd/(1.4*($f3->_nt + $f3->_nv)*$f3->_NRda);
        $blc->math('V_(Ed)/((n_t+n_v)*V_(pl,Rd,a)) + N_(Ed)/(1.4*(n_t+n_v)*N_(Rd,a)) = '.H3::n1($Uvt*100.0).'[%]', '$M_(Ed)$ figyelmenkívül hagyásával');
        $blc->label($Uvt, 'interakciós kihaználtság');

        $blc->h1('Hajlított lemez ellenőrzése', 'Függőleges erőkarra, húzásból és nyomatékból');
        $blc->boo('useRigid', ['', 'Befogott lemez'], false, '');
        ($f3->_useRigid)?$rigidFactor = 8:$rigidFactor = 4;
        $blc->def('MEdp',$f3->_MEd/2 + ($f3->_NEd*($f3->_lever/1000))/$rigidFactor, 'M_(Ed,p) = M_(Ed)/2 + (N_(Ed)*l)/'.$rigidFactor.' = %% [kNm]','Nyomaték a lemezben');
        $blc->def('Ws',floor(($f3->_ba* ($f3->_tb ** 2))/6), 'W_s = (b_a*t_b^2)/6 = %% [mm^3]', 'Gyenge tengely körüli keresztmetszeti modulus');
        $blc->def('sigmaEds',H3::n1(($f3->_MEdp/$f3->_Ws)*1000000), 'sigma_(Ed,s) = M_(Ed)/W_s = %%; f_(y) = '.$f3->_sfy.' [N/(mm^2)]', 'Lemez feszültség');
        $blc->label($f3->_sigmaEds/$f3->_sfy,'lemez rugalmas kihasználtság');

        $blc->h1('Lehorgonyzási hossz számítása');
        $blc->boo('usenprov', ['', 'Húzott horgony kihasználtság figyelembevétele'], false, '$n_(t,req)/n_t = '.H3::n3($f3->_ntreq/$f3->_nt).'$ alkalmazható csökkentő tényező húzás alapján');
        $nprov = 1;
        $nreq = 1;
        if ($f3->_usenprov) {
            $nprov = $f3->_nt;
            $nreq = $f3->_ntreq;
        }
        $blc->lst('alphaa', ['Egyenes: 1.0' => 1.0, 'Kampó, hurok, hajlítás: 0.7' => 0.7], ['alpha_a', 'Lehorgonyzás módja'], '1.0', '');
        $blc->txt('Anyagminőségnél **'.(($f3->_cfbd07)?'rossz tapadás':'jó tapadás').'** ($f_(b,d) = '.$f3->_cfbd.'[N/(mm^2)]$) van beállítva');

        $this->concrete->moduleAnchorageLength($f3->_phi, $f3->_afyd, $f3->_cfbd, $f3->_alphaa, $nreq, $nprov);

        $blc->h1('Horgony-varrat meghatározása', '');
        $wFactor = 1;
        $blc->boo('useDoubleWeld', ['', 'Dupla varrat alkalmazása'], true, '');
        if ($f3->_useDoubleWeld) {
            $wFactor = 2;
        }
        $blc->def('lw', H3::n1($wFactor*$f3->_phi*M_PI), 'l_w = %% [mm]', 'Egy- vagy kétszeres varrathossz horgony kerülete mentén, teljes kerület figyelembevételével');
        $blc->def('betaw', $f3->_sbetaw, 'beta_w = %%', 'Hegesztési tényező');
        $blc->def('NwEd', ($f3->_NEdsum/$f3->_nt)/$f3->_lw * 1000, 'N_(w,Ed) = N_(Ed,sum)/n_t/l_w = %% [(kN)/m]', 'Fajlagos igénybevétel húzásból');
        $blc->def('VwEd', ($f3->_VEd/$f3->_nv)/$f3->_lw * 1000, 'V_(w,Ed) = V_(Ed)/n_v/l_w = %% [(kN)/m]', 'Fajlagos igénybevétel nyírásból');
        $blc->def('a', ceil((max($f3->_NwEd, $f3->_VwEd)*sqrt(3)*$f3->_betaw*$f3->__GM2)/$f3->_sfu), 'a = ceil((max{(N_(w,Ed)),(V_(w,Ed)):}*sqrt(3)*beta_w*gamma_(M2))/(f_u)) = %% [mm]', 'Minimális varrat gyökméret');
        $blc->success0();
        $blc->math('a = '.$f3->_a.' [mm]', 'Minimális varrat gyökméret');
        $blc->success1();

        $blc->h1('Beton pecsétnyomás ellenőrzése nyírt csapok alatt');
        $blc->note('*[Vasbeton szerkezetek (2017) 6.10. 55.o.]*. Nyíróerő átadás $3*phi$ hosszon számítva, fél hengerpalást felületre. Térbeli feszültségállapot szabadon felléphet.');
        $blc->def('FRd', H3::n2($f3->_phi*M_PI*0.5*3*$f3->_phi*3*$f3->_cfcd/1000), 'F_(Rd) = A_(cl)*alpha*f_(cd) = (phi*pi)/2*(3*phi)*3*f_(cd) = %% [kN]');
        $blc->def('FEd', H3::n2($f3->_VEd/$f3->_nv), 'F_(Ed) = V_(Ed)/n_v = %% [kN]');
        $blc->label($f3->_FEd/$f3->_FRd,'kihasználtság');

        //TODO Egyszerűsített Csap táblázat
    }
}
