<?php

namespace Calculation;

Class Baseplate extends \Ecc
{

    /**
     * @var $f3 \Base
     * @var $blc \Blc
     * @var $ec \Ec\Ec
     * @throws \Exception
     */
    public function calc(object $f3, object $blc, object $ec): void
    {
        $blc->numeric('NEd', ['N_(Ed)', 'Húzóerő'], 56, 'kN','Bázislemezre ható eredő húzóerő');
        $blc->numeric('VEd', ['V_(Ed)', 'Nyróerő'], 56, 'kN','Bázislemezre ható eredő nyíróerő');
        $blc->numeric('tb', ['t_b', 'Bázislemez vastagság'], 16, 'mm','');
        $blc->numeric('ba', ['b_a', 'Szélső lehorgonyzások közti vízszintes távolság'], 280, 'mm', '');
        $blc->numeric('ha', ['h_a', 'Szélső lehorgonyzások közti függőleges távolság'], 280, 'mm', '');

        $blc->region0('mat', 'Anyagok');
            $ec->matList('steelMaterialName', 'S235', 'Lemez anyag');
            $ec->saveMaterialData($f3->_steelMaterialName, 's');
            $ec->matList('anchorMaterialName', 'B500', 'Horgony anyag');
            $ec->saveMaterialData($f3->_anchorMaterialName, 'a');
            $ec->matList('concreteMaterialName', 'C25/30', 'Beton anyag');
            $ec->saveMaterialData($f3->_concreteMaterialName, 'c');
        $blc->region1('mat');

        $blc->input('nr', ['n_r', 'Horgony sorok száma'], 2, '', '', 'numeric|min_numeric,2');
        $blc->input('nc', ['n_c', 'Horgony oszlopok száma'], 2, '', '', 'numeric|min_numeric,2');
        $blc->input('nu', ['n_u', 'Felső, húzott lehorgonyzás sorok száma'], 1, '', 'Húzás felvételéhez', 'numeric|max_numeric,'.$f3->_nr.'|min_numeric,1');
        $blc->def('nt', $f3->_nu*$f3->_nc, 'n_t = %%', 'Húzáshoz figyelembevett horgonyok száma');
        $blc->def('nv', ($f3->_nr - $f3->_nu)*$f3->_nc, 'n_v = %%', 'Nyíráshozhoz figyelembevett horgonyok száma');
        $ec->rebarList('phi', 16, ['phi', 'Horgony átmérő'], '');
        $blc->def('As', \H3::n0($ec->A($f3->_phi)), 'A_s = %% [mm^2]', 'Egy horgony keresztmetszete');

        $e = 3*$f3->_phi; // Peremtávolság
        $xp = 2*$e + $f3->_ba; // lemez méretek
        $yp = 2*$e + $f3->_ha;
        $pv = $f3->_ha/($f3->_nr - 1); // Függőleges kiosztás
        $ph = $f3->_ba/($f3->_nc - 1); // Vízszintes kiosztás
        $svg = new \resist\SVG(600, 400, [1, 1]);
        // Column
        $svg->setFill('#eeeeee');
        $svg->addRectangle(10, 10, 60, 380);
        $svg->setFill('black');
        $svg->addRectangle(60, 50, 10, 300);
        $svg->reset();
        // Plate
        $svg->makeRatio(350, 300, $xp, $yp);
        $svg->setColor('red');
        $svg->addRectangle(0, 0, $xp, $yp, 200, 50);
        $svg->addDimH(0, $xp, 380, $xp, 200);
        // Anchors
        for($row = 0; $row <= $f3->_nr - 1; $row++) {
            for($col = 0; $col <= $f3->_nc - 1; $col++) {
                $xi = ($e + $col*$ph);
                $yi = ($e + $row*$pv);
                $svg->addCircle($xi, $yi, 5, 200, 50);
            }
        }
        $svg->reset();
        // Dimensions
        $svg->setColor('blue');
        $svg->addDimH(60, 10, 380, $f3->_tb);
        $svg->reset();
        // Export
        $svg->addBorder();
        $blc->html($svg->getSvg());


        $blc->h1('Húzott horgonyok ellenőrzése');
        $blc->note('Húzásra csak a felső sor horgonyai vannak figyelembe véve!');
        $blc->def('NRda', ($f3->_As*$f3->_afyd)/1000, 'N_(Rd,a) = A_s * f_(yd,a) = %% [kN]', 'Egy horgony húzási ellenállása');
        $blc->def('NRd', $f3->_NRda*$f3->_nt, 'N_(Rd) = n_t*N_(Rd,a) = %% [kN]', 'Húzott (felső) horgonyok húzási ellenállása');
        $blc->txt('Húzott (felső) horgonyok kihasználtsága:');
        $blc->label($f3->_NEd/$f3->_NRd, 'húzási kihasználtság');

        $blc->h1('Horgony-varrat meghatározása');
        $blc->region0('weld0');
            $blc->def('l', \H3::n1($f3->_phi*pi() - 10), 'l = phi*pi - 10 = %% [mm]', 'Varrathossz horgony kerülete mentén');
            $blc->def('betaw', $f3->_sbetaw, 'beta_w = %%', 'Hegesztési tényező');
            $blc->def('FwEd', ($f3->_NEd/$f3->_nt)/$f3->_l * 1000, 'F_(w,Ed) = N_(Ed)/n_t/l = %% [(kN)/m]', 'Fajlagos igénybevétel');
            $blc->def('a', ceil(($f3->_FwEd*sqrt(3)*$f3->_betaw*$f3->__GM2)/$f3->_sfu), 'a = ceil((F_(w,Ed)*sqrt(3)*beta_w*gamma_(M2))/(f_u)) = %% [mm]', 'Minimális varrat gyökméret');
        $blc->region1('weld0');
        $blc->success0('weld1');
            $blc->math('a = '.$f3->_a.' [mm]', 'Minimális varrat gyökméret');
        $blc->success1();

        $blc->h1('Hajlított lemez ellenőrzése');
        $blc->def('MEd',$f3->_NEd*0.25*max($f3->_ba, $f3->_ha)/1000, 'M_(Ed) = N_(Ed)*0.25*max{(b_a),(h_a):} = %% [kNm]','Nyomaték a lemezben');
        $blc->def('Ws',(min($f3->_ba, $f3->_ha)*$f3->_tb*$f3->_tb)/6, 'W_s = ((min{(b_a),(h_a):})*t_b^2)/6 = %% [mm^3]', 'Gyenge tengely körüli keresztmetszeti modulus');
        $blc->math('f_(y,s) = '.$f3->_sfy. '[N/(mm^2)]');
        $blc->def('sigmaEds',\H3::n1(($f3->_MEd/$f3->_Ws)*1000000), 'sigma_(Ed,s) = M_(Ed)/W_s = %% [N/(mm^2)]', 'Lemez feszültség');
        $blc->label($f3->_sigmaEds/$f3->_sfy,'lemez kihasználtság');

        $blc->h1('Nyírt horgonyok ellenőrzése');
        $blc->note('Nyírásra csak az alsó sor horgonyai vannak figyelembe véve!');
        $blc->def('VplRda', \H3::n1(($ec->A($f3->_phi)*$f3->_afyd)/(sqrt(3)*$f3->__GM0*1000)), 'V_(pl,Rd,a) = %% [kN]', 'Egy horgony nyírási ellenállása');
        $blc->def('VplRd', $f3->_nv*$f3->_VplRda, 'V_(pl,Rd) = n_v*V_(pl,Rd,a) = %% [kN]', 'Nyírt (alsó) horgonyok nyírási ellenállása');
        $blc->label($f3->_VEd/$f3->_VplRd,'nyírási kihasználtság');

        $blc->h1('Lehorgonyzási hossz számítása');
        $blc->h1('Csap táblázat');
        $blc->h1('write block');
        $blc->h1('Beton nyomás ellenőrzése nyírt csapok alatt');
//
//        $blc->h1('Szelemen összekötő szerelvény ellenőrzés');
//        $blc->md('`TODO`');
//
//        $blc->h1('Csatlakozó pengelemez ellenőrzés');
//        $blc->md('`TODO`');
//
//        $blc->h1('Csatlakozó pengelemez varrathossz ellenőrzés');
//        $blc->md('`TODO`');
//
//        $blc->h1('Egyszerűsített csap számítás');
//        $blc->md('`TODO`');
//
//        $blc->h1('Neoprén saru felület méretezés');
//        $blc->md('`TODO`');

    }
}
