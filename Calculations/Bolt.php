<?php

namespace Calculation;

Class Bolt extends \Ecc
{

    /**
     * @var $f3 \Base
     * @var $blc \Blc
     * @var $ec \Ec\Ec
     * @throws \Exception
     */
    public function calc(object $f3, object $blc, object $ec): void
    {
        $blc->toc();

        $ec->boltList('bName');

        $ec->matList('bMat', '8.8', 'Csavar anyagminőség');
        $ec->saveMaterialData($f3->_bMat, 'b');
        $ec->matList('sMat', 'S235', 'Acél anyagminőség');
        $ec->saveMaterialData($f3->_sMat, 's');

        $blc->input('t', 'Kisebbik lemez vastagság', 10, 'mm', '');
        $blc->input('n', 'Nyírási síkok száma', 1, '', '');
        $blc->input('N', '`F_(t.Ed)` Húzóerő egy csavarra', 20, 'kN', '');
        $blc->input('V', '`F_(v.Ed)` Nyíróerő egy csavarra', 30, 'kN', '');
        
        $blc->region0('r0', 'Jellemzők');
            $blc->math('btMat = '.$f3->_bMat);
            $blc->def('d_0', $ec->boltProp($f3->_bName, 'd0'),'d_0 = %% [mm]', 'Lyuk átmérő');
            $f3->_d = $ec->boltProp($f3->_bName, 'd');
            $blc->def('A', $ec->boltProp($f3->_bName, 'A'),'A = %% [mm^2]', 'Csavar keresztmetszeti terület');
            $blc->def('A_s', $ec->boltProp($f3->_bName, 'As'),'A_s = %% [mm^2]', 'Csavar húzási keresztmetszet');
            $f3->_sfy = $ec->fy($f3->_sMat, $f3->_t);
        $blc->region1('r0');

        $blc->region0('r1', 'Csavar adatbázis');
            $blc->table($ec->getBoltArray(), 'Csavar','');
        $blc->region1('r1');

        $blc->h1('Egy csavar húzási- és kigombolódási ellenállása', '***D*** nem feszített, húzott és ***E*** feszített, húzott csavarok');
        $blc->def('F_tRd', $ec->FtRd($f3->_bName, $f3->_bMat),'F_(t,Rd) = %% [kN]', 'Csavar húzási ellenállása');
        $blc->label($f3->_N/$f3->_F_tRd, 'Húzási kihasználtság');
        $blc->def('B_pRd', $ec->BpRd($f3->_bName, $f3->_sMat, $f3->_t),'B_(p,Rd) = %% [kN]', 'Csavar kigombolódási ellenállása');
        $blc->label($f3->_N/$f3->_B_pRd, 'Kigombolódási kihasználtság');

        $blc->h1('Egy csavar nyírási- és palástnyomási ellenállása', '***A*** osztály: nem feszített, nyírt csavar');
        $blc->boo('inner', 'Belső csavar', 1, '\`p_1, p_2\` figyelembe vétele');
        $blc->input('e1', 'Peremtávolság (csavarképpel párhuzamos)', 50, 'mm', '');
        $blc->input('e2', 'Peremtávolság (csavarképre merőleges)', 50, 'mm', '');
        $blc->input('p1', 'Csavartávolság (csavarképpel párhuzamos)', 50, 'mm', '');
        $blc->input('p2', 'Csavartávolság (csavarképre merőleges)', 50, 'mm', '');
        $ep1 = $f3->_e1;
        $ep2 = $f3->_e2;
        if ($f3->_inner) {
            $ep1 = $f3->_p1;
            $ep2 = $f3->_p2;
        }
        $blc->def('F_vRd', $ec->FvRd($f3->_bName, $f3->_bMat, $f3->_n),'F_(v,Rd) = %% [kN]', 'Csavar nyírási ellenállása');
        $blc->label($f3->_V/$f3->_F_vRd, 'Nyírási kihasználtság');
        $blc->def('F_bRd', $ec->FbRd($f3->_bName, $f3->_bMat, $f3->_sMat, $ep1, $ep2, $f3->_t, $f3->_inner),'F_(b,Rd) = %% [kN]', 'Csavar palástnyomási ellenállása');
        $blc->label($f3->_V/$f3->_F_bRd, 'Palástnyomási kihasználtság');

        $blc->region0('r2', '*k.1* és *&alpha;.b* tényezők');
            $blc->math('k_1 = '.$f3->___k1.'%%%alpha_b = '.$f3->___alphab);
        $blc->region1('r2');

        $blc->h1('Egy csavar húzás és nyírás interakciója', '***AD*** osztály');
        $blc->def('U_vt', (($f3->_V / $f3->_F_vRd) + ($f3->_N / (1.4*$f3->_F_tRd)))*100, 'U_(vt) = F_(v,Ed)/F_(v,Rd) + F_(t,Ed)/(1.4*F_(t,Rd)) = %% [%]', 'Interakciós kihasználtság');
        $blc->label($f3->_U_vt/100, 'Interakciós kihasználtság');

        $blc->h1('Egyszerűsített eljárás nyírásra optimalizálásra');
        $deltaDb = array(
            '400' => array(
                '360' => 3.18
            ),
            '500' => array(
                '360' => 2.53,
                '430' => 3.04
            ),
            '600' => array(
                '360' => 2.12,
                '430' => 2.54,
                '510' => 3.01,
                '530' => 3.13
            ),
            '800' => array(
                '360' => 1.59,
                '430' => 1.90,
                '510' => 2.26,
                '530' => 2.34
            ),
            '1000' => array(
                '360' => 1.27,
                '430' => 1.52,
                '510' => 1.80,
                '530' => 1.82
            )
        );
        $delta = $deltaDb[$ec->matProp($f3->_bMat, 'fu')][$ec->matProp($f3->_sMat, 'fu')];
        $blc->def('delta', $delta, 'delta = %%', '*[Általános eljárások 6.3. táblázat]*');
        $blc->info0('r3');
            $blc->def('d_min', number_format($f3->_delta * $f3->_t, 0),'d_(min) = delta*t = %% [mm]');
            $blc->def('e1_opt', number_format(2*$f3->_d_0, 0),'e_(1,opt) = 2*d_0 = %% [mm]', 'Szélső peremtávolság (csavarképpel párhuzamos)');
            $blc->def('e2_opt', number_format(1.5*$f3->_d_0, 0),'e_(2,opt) = 1.5*d_0 = %% [mm]', 'Szélső peremtávolság (csavarképre merőleges)');
            $blc->def('p1_opt', number_format(3*$f3->_d_0, 0),'p_(1,opt) = 3*d_0 = %% [mm]', 'Belső csvaar távolság (csavarképpel párhuzamos)');
            $blc->def('p2_opt', number_format(3*$f3->_d_0, 0),'p_(2,opt) = 3*d_0 = %% [mm]', 'Belső csvavar távolság (csavarképre merőleges)');
        $blc->info1('r3');

        $blc->h1('Feszített csavarok nyírásra');
        if ($f3->_bMat != '10.9') {
            $blc->label('no', 'Nem 10.9 csavar');
        } else {
            $blc->input('n_s', 'Súrlódó felületek száma', 1, '', '');
            $blc->input('mu', 'Súrlódási tényező', 0.2, '', '**Súrlódási tényezők:** **0.5** *Sörétezett vagy szemcsefújt festetlen felület*, **0.4** *Sörétezett vagy szemcsefújt festett felület*, **0.3** *Drótkefézett vagy lángszórással tisztított felület*, **0.2** *Kezeletlen felület*');
            $blc->input('F_Ed_ser', '`F_(Ed,ser):` Nyíróerő használhatósági határállapotban', 10, 'kN', '');

            $blc->md('***B*** osztályú nyírt csavarok használhatósági határállapotig működnek feszített csavarként.');
            $blc->md('Teherbírási határállapotban ***A*** csavarként. Használhatósági határállapotban:');

            $blc->def('F_pC', 0.7*$f3->_bfu*$f3->_A_s/1000,'F_(p,C) = 0.7*f_(u,b)*A_s = %% [kN]', 'Előírt feszítőerő');
            $blc->def('F_s_Rd', (($f3->_n_s*$f3->_mu)/$f3->__GM3)*$f3->_F_pC,'F_(s,Rd) = (n_s*mu)/gamma_(M3)*F_(p,C) = %% [kN]', 'Megcsúszással szembeni ellenállás');
            $f3->_U_s_ser = $f3->_F_Ed_ser/$f3->_F_s_Rd;
            $blc->label($f3->_U_s_ser, 'Kihasználtság használhatósági határállapotban');

            $blc->md('***C*** osztályú nyírt csavar:');
            $f3->_U_s = $f3->_V/$f3->_F_s_Rd;
            $blc->label($f3->_U_s, 'Kihasználtság teherbírási határállpotban');
            $blc->label($f3->_V/$f3->_F_bRd, 'Palástnyomási kihasználtság');

            $blc->md('***CE*** osztályú húzott-nyírt csavar:');
            $blc->def('F_s_tv_Rd', (($f3->_n_s*$f3->_mu)/$f3->__GM3)*($f3->_F_pC-0.8*$f3->_N),'F_(s,tv,Rd) = (n_s*mu)/gamma_(M3)*(F_(p,C)-0.8*F_(t,Ed)) = %% [kN]', 'Interakciós ellenállás');
            $f3->_U_s_tv = $f3->_V/$f3->_F_s_tv_Rd;
            $blc->label($f3->_U_s_tv, 'Interakciós kihasználtság');
        }

        $blc->h1('Csavarkép');
        $blc->input('n_c', 'Csavar oszlopok száma', 1, '', '');
        $blc->input('n_r', 'Csavar sorok száma', 1, '', '');
        $nb = $f3->_n_c*$f3->_n_r;
        $blc->txt('Csavarok száma: '.$nb);

        $x = 2*$f3->_e2 + ($f3->_n_c - 1)*$f3->_p2;
        $x1 = 200/$x;
        $y = 2*$f3->_e1 + ($f3->_n_r - 1)*$f3->_p1;
        $y1 = 200/$y;
        $boltPic = [
            ['size' => 10, 'x' => 140, 'y' => 290, 'text' => $x],
            ['size' => 10, 'x' => 50, 'y' => 275, 'text' => $f3->_e2],
            ['size' => 10, 'x' => 140, 'y' => 275, 'text' => ($f3->_n_c - 1).'×'.$f3->_p2],
            ['size' => 10, 'x' => 240, 'y' => 275, 'text' => $f3->_e2],
            ['size' => 10, 'x' => 10, 'y' => 140, 'text' => $y],
            ['size' => 10, 'x' => 255, 'y' => 65, 'text' => $f3->_e1],
            ['size' => 10, 'x' => 255, 'y' => 140, 'text' => ($f3->_n_r - 1).'×'.$f3->_e2],
            ['size' => 10, 'x' => 255, 'y' => 235, 'text' => $f3->_e1],
        ];
        $i = 0;
        $j = 0;
        for ($j = 0; $j < $f3->_n_r; $j++) {
            for ($i = 0; $i < $f3->_n_c; $i++) {
                array_push($boltPic, ['size' => 24, 'x' => 50 + $f3->_e2*$x1 + $i*$f3->_p2*$x1 - 12, 'y' => 50 + $f3->_e1*$y1 + $j*$f3->_p1*$y1 + 12, 'text' => '+']);
            }
        }
        $blc->write('vendor/resist/ecc-calculations/canvas/bolt0.jpg', $boltPic, '');
        $blc->h2('Csavarkép nyírási teherbírása');
        $blc->input('FvEd','`F_(sum,v,Ed)` Csavarképre ható erő', $nb*$f3->_V, 'kN');
        $FRd = min(
        $ec->FbRd($f3->_bName, $f3->_bMat, $f3->_sMat, $f3->_e1, $f3->_e2, $f3->_t, 0),
        $ec->FbRd($f3->_bName, $f3->_bMat, $f3->_sMat, $f3->_p1, $f3->_p2, $f3->_t, 1),
        $ec->FvRd($f3->_bName, $f3->_bMat, $f3->_n)
        )*$nb;
        if ($ec->FvRd($f3->_bName, $f3->_bMat, $f3->_n) >= min(
                $ec->FbRd($f3->_bName, $f3->_bMat, $f3->_sMat, $f3->_e1, $f3->_e2, $f3->_t, 0),
                $ec->FbRd($f3->_bName, $f3->_bMat, $f3->_sMat, $f3->_p1, $f3->_p2, $f3->_t, 1)
            )) {
            $blc->txt('Minden csavar nyírási ellenállása nagyobb bármely másik csavar palástnyomási ellenállásnál, ezért a csavarkép ellenállása lehetne a csavarok palástnyomási ellenállásának összege.');
        }
        $blc->def('FboltEd', $f3->_FvEd/$nb, 'F_(Ed, bol\t) = %% [kN]', 'Egy csvarra jutó nyíróerő centrikus kapcsolat esetén');
        $blc->success0('FRd');
        $blc->txt('Csavarkép teherbírása:');
        if (($f3->_n_r - 1)*$f3->_p1 > 15*$f3->_d) {
            $blc->def('Lj', max(0.75, 1-(($f3->_n_r - 1)*$f3->_p1 - 15*$f3->_d)/(200*$f3->_d)), 'L_j = %%', 'Hosszú kapcsolat csavaronként értelmezett csökkentő tényezője');
            $blc->def('FRd', $FRd, 'F_(Rd) = = %% [kN]', 'Szélső és belső csavarok nyírási vagy palástnyomási ellenállásának minimuma csavarszámmal felszorozva');
            $blc->def('FRd', $FRd*$f3->_Lj, 'F_(Rd, red) = F_(Rd)*L_j = %% [kN]', 'Szélső és belső csavarok nyírási vagy palástnyomási ellenállásának minimuma csavarszámmal felszorozva');
        } else {
            $blc->def('FRd', $FRd, 'F_(Rd) = %% [kN]', 'Szélső és belső csavarok nyírási vagy palástnyomási ellenállásának minimuma csavarszámmal felszorozva');
        }
        $blc->success1('FRd');
        $blc->label($f3->_FboltEd/$f3->_FRd, 'leggyengébb csavar nyírási kihasználtsága');

        $blc->h2('Lemez nettó keresztmetszet vizsgálata');
        $blc->def('l_net', $f3->_e2*2 + ($f3->_n_c - 1)*$f3->_p2 - $f3->_n_c*$f3->_d_0, 'l_(n\et) = 2*e_2 + (n_c-1)*p_2 - n_c*d_0 = %% [mm]', 'Lemez hossz lyukgyengítéssel');
        $blc->def('A', ($f3->_e2*2 + ($f3->_n_c - 1)*$f3->_p2)*$f3->_t, 'A = (2*e_2 + (n_c-1)*p_2)*t = %% [mm^2]', 'Vizsgált keresztmetszeti terület');
        $blc->def('A_net', $f3->_l_net*$f3->_t, 'A = l_(n\et)*t = %% [mm^2]', 'Vizsgált nettó keresztmetszeti terület');
        $blc->def('NplRd', $ec->NplRd($f3->_A, $f3->_sMat, $f3->_t), 'N_(pl,Rd) = (A*f_y)/gamma_(M,0) = %% [kN]', 'Teljes km. folyási ellenállása');
        $blc->def('NuRd', $ec->NuRd($f3->_A_net, $f3->_sMat, $f3->_t), 'N_(u,Rd) = (0.9*A_(n\et)*f_u)/gamma_(M,2) = %% [kN]', 'Nettó km. képlékeny töréssel szembeni ellenállása');
        $blc->def('NtRd', $ec->NtRd($f3->_A, $f3->_A_net, $f3->_sMat, $f3->_t), 'N_(t,Rd) = min(N_(pl,Rd), N_(u,Rd)) = %% [kN]', 'Húzási ellenállás');
        $blc->label($f3->_FvEd/$f3->_NtRd, 'Keresztmetszet kihasználtsága húzásra');

        $blc->h2('Csoportos kiszakadás');
        $blc->boo('exc', 'Excentirkus csavarkép', 0, '');
        $exc = 1;
        if ($f3->_exc) {
            $exc = 0.5;
        }
        $blc->def('A_nt', ($f3->_n_c - 1)*$f3->_p2*$f3->_t, 'A_(nt) = (n_c - 1)*p_2*t = %% [mm^2]');
        $blc->def('A_nv', ($f3->_e1 + ($f3->_n_r - 1)*$f3->_p1)*$f3->_t, 'A_(nv) = (e_1 + (n_r - 1)*p_1)*t = %% [mm^2]');
        $blc->def('Veff1Rd', $exc*(($f3->_sfu*$f3->_A_nt)/($f3->__GM2*1000)) + ($f3->_sfy*$f3->_A_nv)/(sqrt(3)*$f3->__GM0*1000), 'V_(eff,1,Rd) = exc*(f_u*A_(nt))/gamma_(M2) + (f_y*A_(nv))/(gamma_(M0)*sqrt(3)) = %% [kN]');
        $blc->label($f3->_FvEd/$f3->_Veff1Rd, 'csoportos kiszakadás kihasználtsága');
    }
}
