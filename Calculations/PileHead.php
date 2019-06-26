<?php

namespace Calculation;

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
        $blc->note('KG alapján');
        $blc->note('**Egyedi** oszlop esetén; cölöpfej pillér alatt.');

        $blc->toc();

        $blc->h1('Kezdeti bemenő adatok');

        $blc->h2('Anyagok');
        $ec->matList('cMat', 'C30/37', 'Beton anyagminőség');
        $ec->saveMaterialData($f3->_cMat, 'c');
        $ec->matList('rMat', 'B500', 'Betonvas anyagminőség');
        $ec->saveMaterialData($f3->_rMat, 'r');
        $blc->def('rfywd', $f3->_rfyd, 'f_(ywd) = %% [N/(mm2)]', 'A nyíróbetét acélszilárdság tervezési értéke megegyezik a betonvas anyagminőségével');

        $blc->h2('Geometria, vasalás');
        $blc->numeric('h', ['h', 'Lemezvastagság'], 800, 'mm', '');
        $blc->numeric('cnom', ['c_(nom)', 'Betontakarás'], 35, 'mm', '');
        $blc->numeric('cplus', ['c_(+)', 'Betontakarásban'], 28, 'mm', 'Betonfedésben alsó Ø14/20/20 hálóval');
        $blc->def('c', $f3->_cnom + $f3->_cplus, 'c = %% [mm]', 'Teljes betonfedés');
        $ec->rebarList('fix', 25, ['phi_x', 'Vasátmérő x irányban']);
        $ec->rebarList('fiy', 25, ['phi_y', 'Vasátmérő y irányban']);
        $blc->def('deff', $f3->_h - ($f3->_c + ($f3->_fix + $f3->_fiy)/2), 'd_(eff) = h - (c + (phi_x + phi_y)/2) = %% [mm]', 'Hatékony lemezvastagság - a húzott acélbetétek súlypontja (két irányú háló középvonala) és a nyomott szélső szál közötti távolság');

        $blc->h2('Igénybevétel');
        $blc->numeric('VEd', ['V_(Ed)', 'Átszúródó teher'], 4000, 'kN', 'Nyíróerő tervezési értéke');
//        $blc->numeric('beta', ['beta', 'Teherelosztás'], 1.15, '', 'Tehereloszlás egyenlőtlenségéből származó hatás tényezője');
        $blc->def('beta', 1.15, 'beta = %%', 'Tehereloszlás egyenlőtlenségéből származó hatás tényezője - Közbenső pillér esete');

        $blc->h2('Segéd geometria a helyettesítő terhelő pillér méretfelvételéhez');
        $blc->numeric('hv', ['h_v', 'Teherelosztó lemez vastagsága'], 0, 'mm', '2/1-es szétterjedéssel számolva');
        $blc->def('hsum', $f3->_h + $f3->_hv, 'h_(sum) = h + h_v = %% [mm]', 'Teljes vastagság');
        $blc->lst('columnType', ['Körpillér erő alapján felvéve' => 'o', 'Adott méretű négyszögpillér' => 'n'], 'Helyettesítő pillér alkalmazása', 'o', '');
        if ($f3->_columnType == 'o') {
            $ec->matList('c2Mat', 'C40/50', 'Pillér beton anyagminőség');
            $ec->saveMaterialData($f3->_c2Mat, 'c2');
            $blc->def('Acmin', ceil($f3->_VEd/$f3->_c2fcd*1000), 'A_(c,min) = V_(Ed)/(f_(cd)) = %% [mm2]');
            $blc->def('Dcmin', ceil(pow($f3->_Acmin*4/pi(), 0.5)), 'D_(c,min) = %% [mm]', 'Teherből becsült szükséges körpillér méret');
            if ($f3->_hv != 0) {
                $blc->def('Dcmin', $f3->_Dcmin + $f3->_hv, 'D_(c,min) := D_(c,eff) = D_(c,min) + h_v = %% [mm]');
            }
        } else {
            $blc->numeric('a', ['a', 'Pillér méret'], 800, 'mm');
            $blc->numeric('b', ['b', 'Pillér méret'], 800, 'mm', 'Számításban használt négyszög pillérek méretei');
            if ($f3->_hv != 0) {
                $blc->def('a', $f3->_a + $f3->_hv, 'a := a_(eff) = a + h_v = %% [mm]');
                $blc->def('b', $f3->_b + $f3->_hv, 'b := b_(eff) = b + h_v = %% [mm]');
            }
        }

        $blc->h1('Beton tönkremenetele ferde nyomásra', '$u_0$ keresztmetszet ellenőrzése - a nyírási teherbírás felső korlátjának ellőrzése');
        $blc->note('Minimum lemezvastagság meghatározásához');
        if ($f3->_columnType == 'o') {
            $blc->def('u0', floor($f3->_Dcmin*pi()), 'u_0 = D_(c,min)*pi = %% [mm]', 'Nyírt kerület pillér tövénél');
        } else {
            $blc->def('u0', floor(2*($f3->_a + $f3->_b)), 'u_0 = 2*(a+b) = %% [mm]', 'Nyírt kerület pillér tövénél');
        }
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

        $blc->h2('Megfeleltetés átszúródásra **vasalatlan** $u_1$ keresztmetszetben', '');

        $blc->hr();


        $blc->h2('A betonkeresztmetszet ellenőrzése', 'A beton tönkremenetele nyírásra $u_1$ keresztmetszetben');
        $blc->lst('z', ['0.8×d_eff = '. ceil(0.8*$f3->_deff) => ceil(0.8*$f3->_deff), '0.9×d_eff = '. ceil(0.9*$f3->_deff) => ceil(0.9*$f3->_deff)], ['z [ mm]', 'Belső erők karja'], ceil(0.9*$f3->_deff));
        if ($f3->_columnType == 'o') {
            $blc->def('r1', ceil($f3->_Dcmin/2 + 2*$f3->_deff), 'r_1 = D_(c,min)/2 + 2*d_(eff) = %% [mm]', 'Nyírt keresztmetszet a pillértengelytől körpillér esetén');
            $blc->def('u1', floor(2*$f3->_r1*pi()), 'u_1 = 2*r_1*pi = %% [mm]');
        } else {
            $blc->note('KG: $2*(a+b) + 2*d_(eff)*pi$ - ez jóval kisebb értéked ad. Itt máshogy számol - központos pillér: *[Vasbetonszerkezetek (2016) 6.8.1 b)]*');
            $blc->def('u1', 2*$f3->_a + 2*$f3->_b + 16*$f3->_deff, 'u_1 = 2*a + 2*b + 16*_(eff) = %% [mm]');
        }

        $blc->h1('Cölöpfej méretezése', '4 cölöpös cölpfej');
        $blc->def('n', 4, 'n = %%', 'Cölöpök száma');
        $blc->numeric('Dp', ['D_p', 'Cölöp átmérő'], 600, 'mm', '');
        $blc->numeric('xp', ['x_p', 'Cölöp tengely távolság'], 1800, 'mm', 'Javasolt: $3*D_p = '. 3*$f3->_Dp .'[mm]$');
        if (3*$f3->_Dp - 1 >= $f3->_xp) {
            $blc->label('no', 'Tengelytávok túl kicsik');
        }
        $blc->def('xdp', floor($f3->_xp*pow(2, 0.5)), 'x_(d,p) = %% [mm]', 'Átlós cölöptáv');
        $blc->numeric('aph', ['a_(ph)', 'Cölöpfej méret'], 2800, 'mm');
        $blc->numeric('bph', ['b_(ph)', 'Cölöpfej méret'], 2800, 'mm', '');
        $blc->def('M', \H3::n3($f3->_aph*$f3->_bph*$f3->_h/1000000000), 'M = %% [m^3]', 'Cölöpfej mbetonmennyiség');
        // TODO itt miért /4?
        $blc->def('deffmin', ceil($f3->_xdp/4), 'd_(eff,min) = x_(d,p)/4 = %% [mm]', 'Hatékony magasság minimális értéke');
        if ($f3->_deff >= $f3->_deffmin) {
            $blc->label('yes', 'Alkalmazott hatékony magasság megfelel');
        } else {
            $blc->label('no', 'Alkalmazott hatékony magasság túl kicsi');
        }

        $blc->h2('Erőjáték');
        $blc->def('hmin', ceil($f3->_deffmin + ($f3->_c + ($f3->_fix + $f3->_fiy)/2)), 'h_(min) = d_(eff,min) + c + (phi_x+phi_y)/2 = %% [mm]', 'Javasolható minimum a vektoriális erőjáték biztosításához a nyírásra vasalatlan cölöpfejben');
        $blc->def('xd2p', \H3::n0($f3->_xdp/2), 'x_(d,p) /2 = %% [mm]', 'Félátló');
        $blc->def('VEdp', $f3->_VEd/$f3->_n, 'V_(Ed,p) = V_(Ed)/n = %% [kN]', 'Cölöperő');
        $blc->def('HEdp', $f3->_VEdp/$f3->_deff*$f3->_xd2p, 'H_(Ed,p) = V_(Ed,p)/d_(eff)*(x_(d,p)/2) = %% [kN]', 'Vízszintes teher egy cölöp felé');
        $blc->def('HAp', $f3->_HEdp/pow(2, 0.5), 'H_(A,p) = H_(Ed,p)/sqrt(2) = %% [kN]', 'Vízszintes teher cölöpök között');

        $blc->h2('Vasalás');
        $blc->def('Asmin', ceil($f3->_HAp*1000/$f3->_rfyd), 'A_(s,min) = H_(A,p) / (f_(yd)) = %% [mm^2]', 'Szükséges húzott betonacél mennyiség - alsó vas');
        $blc->success0('ns');
            $blc->def('fis', $f3->_fix, 'phi_s = phi_x = %% [mm]', 'Húzott betonacél átmérő');
            $blc->def('ns', ceil($f3->_Asmin/$ec->A($f3->_fis)), 'n_s = %%', 'Szükséges húzott vas szám');
        $blc->success1('ns');

        $blc->h2('Vasmennyiség számítása');
        $blc->def('nsSum', $f3->_n*$f3->_ns, 'n_(s,sum) = n*n_s = %%', 'Fővas szám');
        $blc->def('ls', ceil($f3->_aph - 2*$f3->_c + 2*400)/1000, 'l_s = a_(ph) - 2*c + 2*400 =%% [m]', 'Fővas hossz');
        $blc->def('gs', \H3::n2(($ec->A($f3->_fis)/1000000)*7850), 'g_s = (phi_s^2*pi)/4*gamma_(steel) = %% [(kg)/m]', 'Fővas fajlagos tömeg');

        $blc->numeric('lfi0', ['l_(phi_0)', 'Elosztóvasalás kiosztása'], 200, 'mm', 'Alsó-felső alapháló');
        $ec->rebarList('fi0', 20, ['phi_0', 'Elosztóvasalás vasátmérő']);
        $blc->def('n0', ceil(($f3->_aph - 2*$f3->_c)/$f3->_lfi0)*4, 'n_0 = ceil((a_(ph) - 2*c)/l_(phi_0))*4 = %%', 'Elosztóvas szám');
        // TODO itt miért *20?
        $blc->note('TODO miért *20?');
        $blc->def('l0', \H3::n2(($f3->_aph - 2*$f3->_c + 2*(($f3->_hsum - 2*$f3->_c)/2 + 20*$f3->_fi0))/1000), 'l_s = a_(ph) - 2*c + 2*(h_(sum)-2c)/2 + 20*phi_0 = %% [m]', 'Elosztóvas hossz');
        $blc->def('g0', \H3::n2(($ec->A($f3->_fi0)/1000000)*7850), 'g_0 = (phi_0^2*pi)/4*gamma_(steel) = %% [(kg)/m]', 'Elosztóvas fajlagos tömeg');

        $ec->rebarList('fiw', 10, ['phi_w', 'Nyíróvasalás vasátmérő']);
        // TODO nyíróvasalás számítása
    }
}
