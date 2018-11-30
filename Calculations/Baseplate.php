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
        $blc->h1('Bázislemez');

        $blc->numeric('N_Ed', '`N_(Ed)`: Húzóerő','56', 'kN','Bázislemezre ható eredő húzóerő');
        $blc->numeric('V_Ed', '`V_(Ed)`: Nyróerő','56', 'kN','Bázislemezre ható eredő nyíróerő');
        $blc->numeric('t_b', 'Bázislemez vastagság','16', 'mm','');
        $blc->numeric('b_a', 'Szélső lehorgonyzások közti vízszintes távolság','280', 'mm','');
        $blc->numeric('h_a', 'Szélső lehorgonyzások közti függőleges távolság','280', 'mm','');
        $blc->region0('mat', 'Anyagok');
            $ec->matList('smat', 'S235', 'Lemez anyag');
            $ec->saveMaterialData($f3->_smat, 's');
            $ec->matList('amat', 'B500', 'Horgony anyag');
            $ec->saveMaterialData($f3->_amat, 'a');
            $ec->matList('cmat', 'C25/30', 'Beton anyag');
            $ec->saveMaterialData($f3->_cmat, 'c');
        $blc->region1('mat');
        $blc->numeric('n_u', 'Felső lehorgonyzások száma', 2, '', 'Húzás felvételéhez');
        $blc->numeric('n_d', 'Alsó lehorgonyzások száma', 2, '', 'Nyírás felvételéhez');
        $blc->numeric('phi', 'Horgony átmérő', 16, 'mm', '');
        $blc->def('A_s', \H3::n0($ec->A($f3->_phi)), 'A_s = %% [mm^2]', 'Egy horgony keresztmetszete');
        $blc->def('b', 2*20 + $f3->_phi + $f3->_b_a, 'b = 2*20 + 2* phi/2 + b_a = %% [mm]', 'Bázislemez ajánlott szélessége');

        $blc->h1('Húzott horgonyok ellenőrzése');
        $blc->note('Húzásra csak a felső sor horgonyai vannak figyelembe véve!');
        $blc->def('N_Rda', ($f3->_A_s*$f3->_afyd)/1000, 'N_(Rd,a) = A_s * f_(yd,a) = %% [kN]', 'Egy horgony húzási ellenállása');
        $blc->def('N_Rd', $f3->_N_Rda*$f3->_n_u, 'N_(Rd) = n_u*N_(Rd,a) = %% [kN]', 'Húzott (felső) horgonyok húzási ellenállása');
        $blc->label($f3->_N_Ed/$f3->_N_Rd, 'húzási kihasználtság');

        $blc->h1('Horgony-varrat meghatározása');
        $blc->region0('weld0');
            $blc->def('l', \H3::n1($f3->_phi*pi() - 10), 'l = phi*pi - 10 = %% [mm]', 'Varrathossz horgony kerülete mentén');
            $blc->def('b_w', $f3->_sbetaw, 'beta_w = %%', 'Hegesztési tényező');
            $blc->def('F_wEd', ($f3->_N_Ed/$f3->_n_u)/$f3->_l * 1000, 'F_(w,Ed) = N_(Ed)/n_u/l = %% [(kN)/m]', 'Fajlagos igénybevétel');
            $blc->def('a', ceil(($f3->_F_wEd*sqrt(3)*$f3->_b_w*$f3->__GM2)/$f3->_sfu), 'a = ceil((F_(w,Ed)*sqrt(3)*beta_w*gamma_(M2))/(f_u)) = %% [mm]', 'Minimális varrat gyökméret');
        $blc->region1('weld0');
        $blc->success0('weld1');
            $blc->math('a = '.$f3->_a.' [mm]');
        $blc->success1('weld1');

        $blc->h1('Hajlított lemez ellenőrzése');
        $blc->def('M_Ed',$f3->_N_Ed*0.25*max($f3->_b_a, $f3->_h_a)/1000, 'M_(Ed) = N_(Ed)*0.25*max{(b_a),(h_a):} = %% [kNm]','Nyomaték a lemezben');
        $blc->def('W_s',(min($f3->_b_a, $f3->_h_a)*$f3->_t_b*$f3->_t_b)/6, 'W_s = ((min{(b_a),(h_a):})*t_b^2)/6 = %% [mm^3]', 'Gyenge tengely körüli keresztmetszeti modulus');
        $blc->math('f_(y,s) = '.$f3->_sfy. '[N/(mm^2)]');
        $blc->def('sigmaEds',\H3::n1(($f3->_M_Ed/$f3->_W_s)*1000000), 'sigma_(Ed,s) = M_(Ed)/W_s = %% [N/(mm^2)]', 'Lemez feszültség');
        $blc->label($f3->_sigmaEds/$f3->_sfy,'lemez kihasználtság');

        $blc->h1('Nyírt horgonyok ellenőrzése');
        $blc->note('Nyírásra csak az alsó sor horgonyai vannak figyelembe véve!');
        $blc->def('VplRda', \H3::n1(($ec->A($f3->_phi)*$f3->_afyd)/(sqrt(3)*$f3->__GM0*1000)), 'V_(pl,Rd,a) = %% [kN]', 'Egy horgony nyírási ellenállása');
        $blc->def('VplRd', $f3->_n_d*$f3->_VplRda, 'V_(pl,Rd) = n_d*V_(pl,Rd,a) = %% [kN]', 'Nyírt (alsó) horgonyok nyírási ellenállása');
        $blc->label($f3->_V_Ed/$f3->_VplRd,'nyírási kihasználtság');

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
