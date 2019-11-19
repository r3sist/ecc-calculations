<?php

namespace Calculation;

/**
 * Analysis of bolts and bolted joints according to Eurocodes - Calculation class for ECC framework
 *
 * (c) Bence VÁNKOS
 * https:// structure.hu
 */

Class Bolt extends \Ecc
{
    /**
     * Module: Optimal e1, e2, p1, p2 and d sizes calculation for Shear
     * @param string $boltMatName
     * @param string $steelMatName
     * @param int $tPlate
     * @param int $d0Bolt
     */
    public function moduleOptimalForShear(string $boltMatName, string $steelMatName, int $tPlate, int $d0Bolt) {
        $f3 = \Base::instance();
        $blc = \Blc::instance();
        $ec = \Ec\Ec::instance();

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
        $delta = $deltaDb[$ec->matProp($boltMatName, 'fu')][$ec->matProp($steelMatName, 'fu')];
        $blc->info0('Javasolt átmérő és peremtávolságok:');
            $blc->def('delta', $delta, 'delta = %%', '');
            $blc->note('*[Acélszerkezetek - 1. Általános eljárások (2007) 6.3. táblázat]*');
            $blc->def('d_min', ceil($f3->_delta * $tPlate),'d_(min) = delta*t = %% [mm]', 'Javasolt csavar átmérő');
            $blc->def('e1_opt', ceil(2*$d0Bolt),'e_(1,opt) = 2*d_0 = %% [mm]', 'Szélső peremtávolság (csavarképpel párhuzamos)');
            $blc->def('e2_opt', ceil(1.5*$d0Bolt),'e_(2,opt) = 1.5*d_0 = %% [mm]', 'Szélső peremtávolság (csavarképre merőleges)');
            $blc->def('p1_opt', ceil(3*$d0Bolt),'p_(1,opt) = 3*d_0 = %% [mm]', 'Belső csavartávolság (csavarképpel párhuzamos)');
            $blc->def('p2_opt', ceil(3*$d0Bolt),'p_(2,opt) = 3*d_0 = %% [mm]', 'Belső csavartávolság (csavarképre merőleges)');
        $blc->info1();
    }

    /**
     * Module: Shear force and bearing resistance per bolt for the ultimate limit state
     * @param int $e1
     * @param int $e2
     * @param int $p1
     * @param int $p2
     * @param string $boltName
     * @param string $boltMaterialName
     * @param string $steelMaterialName
     * @param float $VEd
     * @param int $tPlate
     * @param int $numberOfShearPlanes
     * @param bool $innerBolt
     */
    public function moduleShearAndBearingPerBolt(int $e1, int $e2, int $p1, int $p2, string $boltName, string $boltMaterialName, string $steelMaterialName, float $VEd, int $tPlate, int $numberOfShearPlanes = 1, bool $innerBolt = true) {
        $f3 = \Base::instance();
        $blc = \Blc::instance();
        $ec = \Ec\Ec::instance();

        $ep1 = $e1;
        $ep2 = $e2;
        if ($innerBolt && $p1 != 0) {
            $ep1 = $p1;
        }
        if ($innerBolt && $p2 != 0) {
            $ep2 = $p2;
        }

        $this->moduleShear($boltName, $boltMaterialName, $VEd, $numberOfShearPlanes);
        $this->moduleBearing($boltName, $boltMaterialName, $VEd, $steelMaterialName, $ep1, $ep2, $tPlate, $innerBolt);
    }

    /**
     * Module: Shear force per bolt for the ultimate limit state
     * @param string $boltName
     * @param string $boltMaterialName
     * @param float $VEd
     * @param int $numberOfShearPlanes
     * @param float $betaLf
     */
    public function moduleShear(string $boltName, string $boltMaterialName, float $VEd, int $numberOfShearPlanes = 1, float $betaLf = 1) {
        $f3 = \Base::instance();
        $blc = \Blc::instance();
        $ec = \Ec\Ec::instance();

        $blc->boo('useA', 'Teljes keresztmetszeti terület figyelembe vétele', false, 'Menetes rész nem kerülhet nyírt zónába!');
        if ($f3->_useA) {
            $blc->def('F_vRd', $ec->FvRd($boltName, $boltMaterialName, $numberOfShearPlanes, $ec->boltProp($boltName, 'A')),'F_(v,Rd) = %% [kN]', 'Csavar nyírási ellenállása');
        } else {
            $blc->def('F_vRd', $ec->FvRd($boltName, $boltMaterialName, $numberOfShearPlanes),'F_(v,Rd) = %% [kN]', 'Csavar nyírási ellenállása');
        }

        if ($betaLf < 1.0) {
            $blc->def('F_vRd', $betaLf*$f3->_F_vRd, 'F_(v,Rd) := beta_(lf)*F_(v,Rd) = %% [kN]', 'Hosszú kapcsolat vagy béléslemez figyelembe vétele');
        }

        $blc->label($VEd/$f3->_F_vRd, 'Nyírási kihasználtság');
    }

    /**
     * Module: Bearing force per bolt for the ultimate limit state
     * @param string $boltName
     * @param string $boltMaterialName
     * @param float $VEd
     * @param string $steelMaterialName
     * @param int $ep1
     * @param int $ep2
     * @param int $tPlate
     * @param bool $innerBolt
     * @param float $betaLf
     */
    public function moduleBearing(string $boltName, string $boltMaterialName, float $VEd, string $steelMaterialName, int $ep1, int $ep2, int $tPlate, bool $innerBolt, float $betaLf = 1) {
        $f3 = \Base::instance();
        $blc = \Blc::instance();
        $ec = \Ec\Ec::instance();

        $blc->def('F_bRd', $ec->FbRd($boltName, $boltMaterialName, $steelMaterialName, $ep1, $ep2, $tPlate, $innerBolt),'F_(b,Rd) = %% [kN]', 'Csavar palástnyomási ellenállása');
        if ($betaLf < 1.0) {
            $blc->def('F_bRd', $betaLf*$f3->_F_bRd, 'F_(b,Rd) := beta_(lf)*F_(b,Rd) = %% [kN]', 'Hosszú kapcsolat vagy béléslemez figyelembevétele');
        }
        $blc->note('$k_1 = '.$f3->___k1.'; alpha_b = '.$f3->___alphab.'$');
        $blc->label($VEd/$f3->_F_bRd, 'Palástnyomási kihasználtság');
    }

    public function calc(\Base $f3, \Blc $blc, \Ec\Ec $ec): void
    {
        $blc->region0('r1', 'Csavar adatbázis');
        $boltDb = $ec->readData('bolt');
        // Generate multidimensional array for tbl() scheme:
        $scheme = array_keys($boltDb['M12']);
        array_unshift($scheme, 'Csavar');
        $rows = [];
        foreach ($boltDb as $key => $value) {
            array_unshift($value, $key);
            array_push($rows, $value);
        }
        $blc->tbl($scheme, $rows);
        $blc->region1();

        $blc->boo('group', 'Csavarkép számítás', 0);
        $f3->_nr = 1;
        $f3->_nc = 1;
        if ($f3->_group) {
            $ec->wrapNumerics('nr', 'nc', ['n_r × n_c', 'Sorok- és oszlopok száma'], 2, 2, '', '', '×');
            $blc->boo('groupLL', 'Egyik szárukon kapcsolt szögacélok ellenőrzése húzásra', 0);
            if ($f3->_groupLL) {
                $f3->_nc = 1;
                $blc->txt('$n_c := 1$, csak egy csavaroszloppal számol!');
            }
        }
        $f3->_nb = $f3->_nr * $f3->_nc;

        $ec->boltList('bName');
        $blc->region0('r0', 'Csavar jellemzők');
        $blc->def('d_0', $ec->boltProp($f3->_bName, 'd0'), 'd_0 = %% [mm]', 'Lyuk átmérő');
        $f3->_d = $ec->boltProp($f3->_bName, 'd');
        $blc->def('A', $ec->boltProp($f3->_bName, 'A'), 'A = %% [mm^2]', 'Csavar keresztmetszeti terület');
        $blc->def('A_s', $ec->boltProp($f3->_bName, 'As'), 'A_s = %% [mm^2]', 'Csavar húzási keresztmetszet');
        $blc->region1();

        $ec->matList('bMat', '8.8', 'Csavar anyagminőség', 'bolt');
        $ec->saveMaterialData($f3->_bMat, 'b');
        $ec->matList('sMat', 'S235', 'Acél anyagminőség', 'steel');
        $ec->saveMaterialData($f3->_sMat, 's');

        $blc->numeric('t', ['t', 'Kisebbik lemez vastagság'], 10, 'mm', '');
        $blc->numeric('n', ['n', 'Nyírási síkok száma'], 1, '', '');
        $blc->numeric('N', ['F_(t,Ed)', 'Húzóerő csavarra vagy csavarképre'], 20, 'kN', '');
        $blc->numeric('V', ['F_(v,Ed)', 'Nyíróerő csavarra vagy csavarképre'], 30, 'kN', '');
        $f3->_sfy = $ec->fy($f3->_sMat, $f3->_t);
        $blc->note('$f_y(t) = ' . $f3->_sfy . ' [N/(mm^2)]$');

        $blc->h1('Egy csavar nyírási- és palástnyomási ellenállása', '***A*** osztály: nem feszített, nyírt csavar');
        $this->moduleOptimalForShear($f3->_bMat, $f3->_sMat, $f3->_t, $f3->_d_0);
        $blc->numeric('e1', ['e_1', 'Peremtávolság (csavarképpel párhuzamos)'], 50, 'mm', '');
        $blc->numeric('e2', ['e_2', 'Peremtávolság (csavarképre merőleges)'], 50, 'mm', '');

        $f3->_inner = 0;
        $f3->_p1 = 0;
        $f3->_p2 = 0;
        if ($f3->_group && $f3->_nr > 1) {
            $f3->_inner = 1;
            $blc->note('Belső csavar számítása (csavarkép és $n_r > 1$. $p_1, p_2$ figyelembevétele');
            $blc->numeric('p1', ['p_1', 'Csavartávolság (csavarképpel párhuzamos)'], 50, 'mm', '');
            if ($f3->_nc > 1 && !$f3->_groupLL) {
                $blc->numeric('p2', ['p_2', 'Csavartávolság (csavarképre merőleges)'], 50, 'mm', '');
            }
        }
        $blc->note('Egy csavar esetén külsőként számolható. Több csavar esetén belső a mértékadó.');
        $this->moduleShearAndBearingPerBolt($f3->_e1, $f3->_e2, $f3->_p1, $f3->_p2, $f3->_bName, $f3->_bMat, $f3->_sMat, $f3->_V/$f3->_nb, $f3->_t, $f3->_n, $f3->_inner);

        $blc->h1('Egy csavar húzási- és kigombolódási ellenállása', '***D*** nem feszített, húzott és ***E*** feszített, húzott csavarok');
        $blc->def('F_tRd', $ec->FtRd($f3->_bName, $f3->_bMat), 'F_(t,Rd) = %% [kN]', 'Csavar húzási ellenállása');
        $blc->label($f3->_N / $f3->_F_tRd, 'Húzási kihasználtság');
        $blc->def('B_pRd', $ec->BpRd($f3->_bName, $f3->_sMat, $f3->_t), 'B_(p,Rd) = %% [kN]', 'Csavar kigombolódási ellenállása');
        $blc->label($f3->_N / $f3->_B_pRd, 'Kigombolódási kihasználtság');

        $blc->h1('Egy csavar húzás és nyírás interakciója', '***AD*** osztály');
        $blc->def('U_vt', \H3::n1((($f3->_V / $f3->_F_vRd) + ($f3->_N / (1.4 * $f3->_F_tRd))) * 100), 'U_(vt) = F_(v,Ed)/F_(v,Rd) + F_(t,Ed)/(1.4*F_(t,Rd)) = %% [%]', 'Interakciós kihasználtság');
        $blc->label($f3->_U_vt / 100, 'Interakciós kihasználtság');

        if ($f3->_bMat == '10.9') {
            $blc->h1('Feszített csavarok nyírásra');
            $blc->numeric('n_s', ['n_s', 'Súrlódó felületek száma'], 1, '', '');
            $blc->numeric('mu', ['mu', 'Súrlódási tényező'], 0.2, '', '**Súrlódási tényezők:** **0.5** *Sörétezett vagy szemcsefújt festetlen felület*, **0.4** *Sörétezett vagy szemcsefújt festett felület*, **0.3** *Drótkefézett vagy lángszórással tisztított felület*, **0.2** *Kezeletlen felület*');
            $blc->numeric('F_Ed_ser', ['F_(Ed,ser)', 'Nyíróerő használhatósági határállapotban'], 10, 'kN', '');

            $blc->md('***B*** osztályú nyírt csavarok használhatósági határállapotig működnek feszített csavarként.');
            $blc->md('Teherbírási határállapotban ***A*** csavarként. Használhatósági határállapotban:');

            $blc->def('F_pC', 0.7 * $f3->_bfu * $f3->_A_s / 1000, 'F_(p,C) = 0.7*f_(u,b)*A_s = %% [kN]', 'Előírt feszítőerő');
            $blc->def('F_s_Rd', (($f3->_n_s * $f3->_mu) / $f3->__GM3) * $f3->_F_pC, 'F_(s,Rd) = (n_s*mu)/gamma_(M3)*F_(p,C) = %% [kN]', 'Megcsúszással szembeni ellenállás');
            $f3->_U_s_ser = $f3->_F_Ed_ser / $f3->_F_s_Rd;
            $blc->label($f3->_U_s_ser, '*B* Kihasználtság használhatósági határállapotban');

            $blc->md('***C*** osztályú nyírt csavar:');
            $f3->_U_s = $f3->_V / $f3->_F_s_Rd;
            $blc->math('F_(v,Ed)/F_(s,Rd) = ' . \H3::n3($f3->_U_s));
            $blc->label(\H3::n3($f3->_U_s), '*C* Megcsúszási kihasználtság teherbírási határállpotban');
            $blc->math('F_(v,Ed)/F_(b,Rd) = ' . \H3::n3($f3->_V / $f3->_F_bRd));
            $blc->label($f3->_V / $f3->_F_bRd, '*C* Palástnyomási kihasználtság');

            $blc->md('***CE*** osztályú húzott-nyírt csavar:');
            $blc->def('F_s_tv_Rd', (($f3->_n_s * $f3->_mu) / $f3->__GM3) * ($f3->_F_pC - 0.8 * $f3->_N), 'F_(s,tv,Rd) = (n_s*mu)/gamma_(M3)*(F_(p,C)-0.8*F_(t,Ed)) = %% [kN]', 'Interakciós ellenállás');
            $f3->_U_s_tv = $f3->_V / $f3->_F_s_tv_Rd;
            $blc->math('F_(v,Ed)/F_(s,tv,Rd) = ' . \H3::n3($f3->_U_s_tv));
            $blc->label($f3->_U_s_tv, 'Interakciós kihasználtság');
            $blc->note('*B* osztályú csavar esetén *BE* interakció ugyanez, $F_(Ed,ser)$ alkalmazásával.');
        }

        if ($f3->_group) {
            $blc->h1('Csavarkép');
            $nb = $f3->_nc * $f3->_nr;
            $blc->txt('Csavarok száma: ' . $nb);

//            $x = 2 * $f3->_e2 + ($f3->_nc - 1) * $f3->_p2;
//            $x1 = 200 / $x;
//            $y = 2 * $f3->_e1 + ($f3->_nr - 1) * $f3->_p1;
//            $y1 = 200 / $y;
//            $boltPic = [
//                ['size' => 10, 'x' => 140, 'y' => 290, 'text' => $x],
//                ['size' => 10, 'x' => 50, 'y' => 275, 'text' => $f3->_e2],
//                ['size' => 10, 'x' => 140, 'y' => 275, 'text' => ($f3->_nc - 1) . '×' . $f3->_p2],
//                ['size' => 10, 'x' => 240, 'y' => 275, 'text' => $f3->_e2],
//                ['size' => 10, 'x' => 10, 'y' => 140, 'text' => $y],
//                ['size' => 10, 'x' => 255, 'y' => 65, 'text' => $f3->_e1],
//                ['size' => 10, 'x' => 255, 'y' => 140, 'text' => ($f3->_nr - 1) . '×' . $f3->_e2],
//                ['size' => 10, 'x' => 255, 'y' => 235, 'text' => $f3->_e1],
//            ];
//
//            for ($j = 0; $j < $f3->_nr; $j++) {
//                for ($i = 0; $i < $f3->_nc; $i++) {
//                    array_push($boltPic, ['size' => 24, 'x' => 50 + $f3->_e2 * $x1 + $i * $f3->_p2 * $x1 - 12, 'y' => 50 + $f3->_e1 * $y1 + $j * $f3->_p1 * $y1 + 12, 'text' => '+']);
//                }
//            }
//            $blc->write('vendor/resist/ecc-calculations/canvas/bolt0.jpg', $boltPic, '');

            // SVG init
            $xp = 2*$f3->_e2 + ($f3->_nc - 1)*$f3->_p2; // Plate dimensions
            $yp = 2*$f3->_e1 + ($f3->_nr - 1)*$f3->_p1; // Plate dimensions
            $svg = new \resist\SVG(450, 450);
            // Plate
            $svg->makeRatio(350, 350, $xp, $yp);
            $svg->setColor('blue');
            $svg->addRectangle(0, 0, $xp, $yp, 25, 25);
            // Bolts
            for ($row = 0; $row <= $f3->_nr - 1; $row++) {
                for ($col = 0; $col <= $f3->_nc - 1; $col++) {
                    $xi = ($f3->_e2 + $col*$f3->_p2);
                    $yi = ($f3->_e1 + $row*$f3->_p1);
                    $svg->addCircle($xi, $yi, ($f3->_d_0/2), 25, 25);
                    $svg->makeRatio(350, 350, $xp, $yp);
                }
            }
            // Dimensions
            $svg->setColor('magenta');
            $svg->addDimH(0, $xp, 430, $xp, 25); // Plate horizontal
            $svg->addDimV(0, $yp, 430, $yp, 25); // Plate vertical
            $svg->addDimH(0, $f3->_e2, 400, $f3->_e2, 25); // e2
            ($f3->_nc > 1)?$svg->addDimH($f3->_e2, $f3->_p2, 400, \H3::n0($f3->_p2), 25):false; // p2
            $svg->addDimV(0, $f3->_e1, 400, $f3->_e1, 25); // e1
            ($f3->_nr > 1)?$svg->addDimV($f3->_e1, $f3->_p1, 400, \H3::n0($f3->_p1), 25):false; // p1
            // Texts & symbols
            $svg->setColor('black');
            $svg->addSymbol(200, 5, 'arrow-up');
            $blc->svg($svg);

            $blc->h2('Csavarkép nyírási teherbírása');
            $FRd = min($ec->FbRd($f3->_bName, $f3->_bMat, $f3->_sMat, $f3->_e1, $f3->_e2, $f3->_t, 0), $ec->FbRd($f3->_bName, $f3->_bMat, $f3->_sMat, ($f3->_p1 != 0)?$f3->_p1:$f3->_e1, ($f3->_p2 != 0)?$f3->_p2:$f3->_e2, $f3->_t, 1), $ec->FvRd($f3->_bName, $f3->_bMat, $f3->_n, 0)) * $nb;
            if ($ec->FvRd($f3->_bName, $f3->_bMat, $f3->_n, 0) >= min($ec->FbRd($f3->_bName, $f3->_bMat, $f3->_sMat, $f3->_e1, $f3->_e2, $f3->_t, 0), $ec->FbRd($f3->_bName, $f3->_bMat, $f3->_sMat, ($f3->_p1 != 0)?$f3->_p1:$f3->_e1, ($f3->_p2 != 0)?$f3->_p2:$f3->_e2, $f3->_t, 1))) {
                $blc->txt('Minden csavar nyírási ellenállása nagyobb bármely másik csavar palástnyomási ellenállásnál, ezért a csavarkép ellenállása lehetne a csavarok palástnyomási ellenállásának összege.');
            }
            $blc->def('FboltEd', $f3->_V / $nb, 'F_(Ed, bol t) = V_(Ed)/' . $nb . ' = %% [kN]', 'Egy csvarra jutó nyíróerő centrikus kapcsolat esetén');
            $blc->success0('Csavarkép teherbírása:');
                if (($f3->_nr - 1) * $f3->_p1 > 15 * $f3->_d) {
                    $blc->def('Lj', max(0.75, 1 - (($f3->_nr - 1) * $f3->_p1 - 15 * $f3->_d) / (200 * $f3->_d)), 'L_j = %%', 'Hosszú kapcsolat csavaronként értelmezett csökkentő tényezője');
                    $blc->def('FRd', $FRd, 'F_(Rd) = %% [kN]', 'Szélső és belső csavarok nyírási vagy palástnyomási ellenállásának minimuma csavarszámmal felszorozva');
                    $blc->def('FRd', $FRd * $f3->_Lj, 'F_(Rd, red) = F_(Rd)*L_j = %% [kN]', 'Szélső és belső csavarok nyírási vagy palástnyomási ellenállásának minimuma csavarszámmal felszorozva');
                } else {
                    $blc->def('FRd', $FRd, 'F_(Rd) = %% [kN]', 'Szélső és belső csavarok nyírási vagy palástnyomási ellenállásának minimuma csavarszámmal felszorozva');
                }
            $blc->success1();
            $blc->label($f3->_FboltEd / $f3->_FRd, 'legjobban igénybevett csavar nyírási kihasználtsága');

            $blc->h2('Lemez nettó keresztmetszet vizsgálata');
            $blc->def('l_net', $f3->_e2 * 2 + ($f3->_nc - 1) * $f3->_p2 - $f3->_nc * $f3->_d_0, 'l_(n\et) = 2*e_2 + (n_c-1)*p_2 - n_c*d_0 = %% [mm]', 'Lemez hossz lyukgyengítéssel');
            $blc->def('A', ($f3->_e2 * 2 + ($f3->_nc - 1) * $f3->_p2) * $f3->_t, 'A = (2*e_2 + (n_c-1)*p_2)*t = %% [mm^2]', 'Vizsgált keresztmetszeti terület');
            $blc->def('A_net', $f3->_l_net * $f3->_t, 'A_(n et) = l_(n et)*t = %% [mm^2]', 'Vizsgált nettó keresztmetszeti terület');
            $blc->def('NplRd', $ec->NplRd($f3->_A, $f3->_sMat, $f3->_t), 'N_(pl,Rd) = (A*f_y)/gamma_(M,0) = %% [kN]', 'Teljes km. folyási ellenállása');
            $blc->def('NuRd', $ec->NuRd($f3->_A_net, $f3->_sMat, $f3->_t), 'N_(u,Rd) = (0.9*A_(n\et)*f_u)/gamma_(M,2) = %% [kN]', 'Nettó km. képlékeny töréssel szembeni ellenállása');
            $blc->def('NtRd', min($f3->_NplRd, $f3->_NuRd), 'N_(t,Rd) = min{(N_(pl,Rd)), (N_(u,Rd)):} = %% [kN]', 'Húzási ellenállás');
            $blc->label($f3->_V / $f3->_NtRd, 'Keresztmetszet kihasználtsága húzásra');

            $blc->h2('Csoportos kiszakadás');
            $blc->boo('exc', 'Excentrikus csavarkép', 0, '');
            $exc = 1;
            if ($f3->_exc) {
                $exc = 0.5;
            }
            $blc->def('A_nt', ($f3->_nc - 1) * $f3->_p2 * $f3->_t, 'A_(nt) = (n_c - 1)*p_2*t = %% [mm^2]');
            $blc->def('A_nv', ($f3->_e1 + ($f3->_nr - 1) * $f3->_p1) * $f3->_t, 'A_(nv) = (e_1 + (n_r - 1)*p_1)*t = %% [mm^2]');
            $blc->def('Veff1Rd', $exc * (($f3->_sfu * $f3->_A_nt) / ($f3->__GM2 * 1000)) + ($f3->_sfy * $f3->_A_nv) / (sqrt(3) * $f3->__GM0 * 1000), 'V_(eff,1,Rd) = exc*(f_u*A_(nt))/gamma_(M2) + (f_y*A_(nv))/(gamma_(M0)*sqrt(3)) = %% [kN]');
            $blc->label($f3->_V / $f3->_Veff1Rd, 'csoportos kiszakadás kihasználtsága');
            $blc->note('Féloldalas kiszakadást nem vizsgál!');

            if ($f3->_groupLL) {
                $blc->h1('Egyik szárukon kapcsolt szögacélok húzásra');
                $blc->note('Külpontosság elhanyagolható.');
                $blc->math('n_r = ' . $f3->_nr . ' × ' . $f3->_bName . '%%%(n_c = 1)', 'Függőleges csavarkép átvétele');
                $ec->sectionFamilyList('sectionFamily', 'Szelvény család', 'L');
                $ec->sectionList($f3->_sectionFamily);
                $ec->saveSectionData($f3->_sectionName, true);
                $blc->math('d_0 = ' . $f3->_d_0 . '[mm]%%%t_(w,L) = ' . $f3->_sectionData['tw'] * 10 . '[mm]%%%f_(u,s) = ' . $f3->_sfu . '[N/(mm^2)] %%% A_(n\et) = ' . $f3->_A_net . ' [mm^2]');
                $blc->def('AnetL', $f3->_sectionData['Ax'] * 100 - $f3->_d_0 * $f3->_sectionData['tw'] * 10, 'A_(n et,L) = A_(x,L) - 1*d_0*t_(w,L) = %% [mm^2]');
                $blc->note('Egy oszlop csavar csak!');

                $blc->def('e1', $f3->_e1_opt, 'e_(1,min) := e_(1,opt) = ' . $f3->_e1_opt . ' [mm]', 'Peremtávolság erő irányban, fenti számítások alapján');
                $blc->def('e2', (min($f3->_sectionData['b'], $f3->_sectionData['h']) * 10) / 2, 'e_2 = (min{(h_L),(b_L):})/2 = %% [mm]', 'Peremtávolság erő irányra merőlegesen');

                if ($f3->_nr == 1) {
                    $blc->txt('Erőátadás irányában egy csavar esete:');
                    $NuRd = (2 * ($f3->_e2 - 0.5 * $f3->_d_0) * $f3->_sectionData['tw'] * 10 * $f3->_sfu) / ($f3->__GM2 * 1000);
                    $blc->success0('Rúd keresztmetszeti ellenállása húzásra:');
                    $blc->def('NuRd', $NuRd, 'N_(u,Rd) = (2*(e_2-0.5*d_0)*t_(w,L)*f_(u,s))/gamma_(M2) = %% [kN]');
                    $blc->success1();
                } else if ($f3->_nr == 2) {
                    $blc->txt('Erőátadás irányában két csavar esete:');
                    if ($f3->_p1 <= 2.5 * $f3->_d_0) {
                        $blc->def('beta_2', 0.4, 'beta_2 = %%', 'p_1 < 2.5*d_0');
                    } else if ($f3->_p1 >= 5 * $f3->_d_0) {
                        $blc->def('beta_2', 0.7, 'beta_2 = %%', 'p_1 > 5*d_0');
                    } else {
                        $blc->def('beta_2', $ec->linterp(2.5 * $f3->_d_0, 0.4, 5 * $f3->_d_0, 0.7, $f3->_p1), 'beta_2 = %%', 'Lineárisan interpolált érték!');
                    }
                    $NuRd = ($f3->_beta_2 * $f3->_AnetL * $f3->_sfu) / ($f3->__GM2 * 1000);
                    $blc->success0('Rúd keresztmetszeti ellenállása húzásra:');
                    $blc->def('NuRd', $NuRd, 'N_(u,Rd) = (beta_2*A_(n et,L)*f_(u,s))/gamma_(M2) = %% [kN]');
                    $blc->success1();
                } else {
                    if ($f3->_p1 <= 2.5 * $f3->_d_0) {
                        $blc->def('beta_3', 0.5, 'beta_2 = %%', 'p_1 < 2.5*d_0');
                    } else if ($f3->_p1 >= 5 * $f3->_d_0) {
                        $blc->def('beta_3', 0.7, 'beta_2 = %%', 'p_1 > 5*d_0');
                    } else {
                        $blc->def('beta_3', $ec->linterp(2.5 * $f3->_d_0, 0.5, 5 * $f3->_d_0, 0.7, $f3->_p1), 'beta_3 = %%', 'Lineárisan interpolált érték!');
                    }
                    $NuRd = ($f3->_beta_3 * $f3->_AnetL * $f3->_sfu) / ($f3->__GM2 * 1000);
                    $blc->success0('Rúd keresztmetszeti ellenállása húzásra:');
                    $blc->def('NuRd', $NuRd, 'N_(u,Rd) = (beta_3*A_(n et,L)*f_(u,s))/gamma_(M2) = %% [kN]');
                    $blc->success1();
                }
                $blc->label($f3->_V / $f3->_NuRd, 'Húzási kihasználtság');
                $blc->note('`beta_2` és `beta_3` külpontosság miatti tényezők. Táblázatos érték [Acélszuerkezetek általános eljárások (2007) 5.1/5.2 táblázat 40.o.]');
            }
        }
    }
}
