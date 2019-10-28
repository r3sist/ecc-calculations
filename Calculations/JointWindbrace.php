<?php

namespace Calculation;

/**
 * Analysis of steel windbrace joint according to Eurocodes - Calculation class for ECC framework
 *
 * (c) Bence VÁNKOS
 * https:// structure.hu
 */

Class JointWindbrace extends \Ecc
{

    /**
     * @var $f3 \Base
     * @var $blc \Blc
     * @var $ec \Ec\Ec
     * @throws \Exception
     */
    public function calc(object $f3, object $blc, object $ec): void
    {
        $weldClass = new \Calculation\Weld();
        $boltClass = new \Calculation\Bolt();

        $blc->img('https://structure.hu/ecc/JointWindbrace0.jpg');

        $ec->matList('steelMaterialName', 'S235', 'Szelvények, lemezek anyagminősége');
        $ec->saveMaterialData($f3->_steelMaterialName, 's');
        $blc->lst('forceSource', ['Bekötés szelvény húzásra' => 'huzas', 'Bekötés erőre' => 'ero'], 'Erő megadása', 'ero', '');

        switch ($f3->_forceSource) {
            case 'huzas':
                $ec->sectionFamilyList('sectionFamily', 'Szelvény család', 'O');
                $ec->sectionList($f3->_sectionFamily, 'sectionName', 'Szelvény', 'D20');
                $ec->saveSectionData($f3->_sectionName, true, 'section');
                $blc->def('FEd', $ec->NplRd($f3->_section['Ax']*100, $f3->_steelMaterialName, max($f3->_section['tw']/10, $f3->_section['tf']/10)), 'F_(Ed) := N_(pl,Rd) = (A*f_y)/gamma_(M,0) = %% [kN]', 'Teljes km. folyási ellenállása');
                break;
            case 'ero':
                $blc->numeric('FEd', ['F_(Ed)', 'Húzó-nyomó erő rácsrúdban'], 50, 'kN');
                break;
        }

        $blc->numeric('t1', ['t_1', 'Bekötőlemez lemezvastagság'], 10, 'mm', 'Bázislemezhez hegesztett csomólemez');
        $blc->boo('n1', ['n_1', 'Dupla bekötőlemez bázislemezen'], 0, 'Nyírási síkok számához');
        $f3->_n1 = \V3::numeric($f3->_n1) + 1;
        $blc->numeric('t2', ['t_2', 'Csomólemez lemezvastagság'], 10, 'mm', 'Rácsrúdhoz hegesztett csomólemez');
        $blc->boo('n2', ['n_2', 'Dupla csomólemez rácsrúdon'], 0, 'Nyírási síkok számához');
        $f3->_n2 = \V3::numeric($f3->_n2) + 1;
        $nv0 = $f3->_n1 + $f3->_n2 - 1;
        $blc->def('nv', ($nv0 > 2)?2:$nv0, 'n_v = %%', 'Nyírási síkok száma csavarképben');
        $blc->note('Nyírási síkok száma kettőre van korlátozva.');

        $blc->lst('connectionType', ['Csavaros bekötés' => 'b', 'Hegesztett bekötés' => 'w'], 'Kialakítás', 'b', 'Csavaros bekötés: *bekötő-* és *csomólemez* csavarral kapcsolva. Hegesztett bekötés: *bekötő-* és *csomólemez* M12 oválfuratos csavarral szerelve és összehegesztve.');
        switch ($f3->_connectionType) {
            case 'b':
                $blc->h1('Csavarkép ellenőrzése');
                $ec->boltList('boltName');
                $ec->matList('boltMaterialName', '8.8', 'Csavarok anyagminősége');
                $ec->saveMaterialData($f3->_boltMaterialName, 'b');
                $blc->def('d0', $ec->boltProp($f3->_boltName, 'd0'),'d_0 = %% [mm]', 'Lyuk átmérő');
                $f3->_d = $ec->boltProp($f3->_boltName, 'd');
                $f3->_As = $ec->boltProp($f3->_boltName, 'As');

                $boltClass->moduleOptimalForShear($f3->_boltMaterialName, $f3->_steelMaterialName, min($f3->_t1, $f3->_t2), $f3->_d0);

                $blc->numeric('nr', ['n_r', 'Csavar sorok száma'], 1, '', 'Erőre merőleges irányban');
                $blc->numeric('nc', ['n_c', 'Csavar oszlopok száma'], 1, '', 'Erővel párhuzamos irányban');
                $blc->def('nb', $f3->_nr*$f3->_nc, 'n_b = %%', 'Csavarok száma');
                $blc->numeric('e1', ['e_1', 'Peremtávolság (csavarképpel párhuzamos)'], 50, 'mm', 'Erővel párhuzamos irányban');
                $blc->numeric('e2', ['e_2', 'Peremtávolság (csavarképre merőleges)'], 50, 'mm', 'Erőre merőleges irányban');
                if ($f3->_nc > 1) {
                    $blc->numeric('p1', ['p_1', 'Csavartávolság (csavarképpel párhuzamos)'], 50, 'mm', 'Erővel párhuzamos irányban');
                } else {
                    $f3->_p1 = $f3->_e1;
                }
                if ($f3->_nr > 1) {
                    $blc->numeric('p2', ['p_2', 'Csavartávolság (csavarképre merőleges)'], 50, 'mm', 'Erőre merőleges irányban');
                } else {
                    $f3->_p2 = $f3->_e2;
                }

                $blc->h2('Egy csavar nyírási- és palástnyomási ellenállásának ellenőrzése', '***A*** osztály: nem feszített, nyírt csavar');
                $blc->def('tmin', min($f3->_t1, $f3->_t2), 't_(min) = %% [mm]', 'Palástnyomás ellenőrzése kisebbik csomólemezre');

                $blc->def('VEd', $f3->_FEd/$f3->_nb, 'V_(Ed) = F_(Ed)/n_b = %% [kN]', 'Egy csavarra jutó nyíróerő');
                $f3->_betaLf = 1;
                if (($f3->_nc-1)*$f3->_p1 >= 15*$f3->_d) {
                    $blc->math('L_j = (n_c - 1)*p_1%%%ge15*d');
                    $blc->txt('Hosszú kapcsolat!');
                    $blc->def('betaLf', 1-(($f3->_nc-1)*$f3->_p1 - 15*$f3->_d)/(200*$f3->_d), 'beta_(Lf) = 1 - ((n_c-1)*p_1 - 15*d)/(200*d) = %%', 'Csökkentő tényező csavar ellenállásokhoz');
                    if ($f3->_betaLf < 0.75) {
                        $blc->def('betaLf', 0.75, 'beta_(Lf) := %%');
                    }
                }

                $blc->boo('shim', 'Bekötőlemez számítása béléslemezként', false);
                if ($f3->_shim) {
                    $f3->_betap = 1;
                    if ($f3->_t1 > $f3->_d/3) {
                        $blc->math('(t_1 = '.$f3->_t1.') gt (d/3 = '.\H3::n1($f3->_d/3) .') [mm]');
                        $blc->def('betap', \H3::n3((9*$f3->_d)/(8*$f3->_d + 3*$f3->_t1)), 'beta_p = 9d/(8d+3t_1) = %%', 'Csökkentő tényező csavar ellenállásokhoz');
                        if ($f3->_betap > 1) {
                            $blc->def('betap', 1, 'beta_p := %%');
                        }
                        $blc->txt('*Továbbiakban $beta_p$ tényező kombinált $beta_(Lf)$ tényezőként hivatkozva.*');
                    } else {
                        $blc->math('t_1 = '.$f3->_t1.') le (d/3 = '.\H3::n1($f3->_d/3) .') [mm]');
                        $blc->txt('Csökkentés nem szükséges.');
                    }
                    $f3->_betaLf = $f3->_betaLf*$f3->_betap; // beta_Lf és beta_p összegyúrása
                }

                $blc->h3('Csavar nyírás ellenőrzése:');
                $boltClass->moduleShear($f3->_boltName, $f3->_boltMaterialName, $f3->_VEd, $f3->_nv, $f3->_betaLf);

                if ($f3->_nc > 1 || $f3->_nr > 1) {
                    $blc->h3('Csavar palástnyomás ellenőrzése, belső csavarokhoz:');
                    $blc->math('t_1 = '.$f3->_t1.'[mm]:');
                    $boltClass->moduleBearing($f3->_boltName, $f3->_boltMaterialName, $f3->_VEd, $f3->_steelMaterialName, $f3->_p1, $f3->_p2, $f3->_t1, true, $f3->_betaLf);
                    $blc->math('t_2 = '.$f3->_t2.'[mm]:');
                    $boltClass->moduleBearing($f3->_boltName, $f3->_boltMaterialName, $f3->_VEd, $f3->_steelMaterialName, $f3->_p1, $f3->_p2, $f3->_t2, true, 1);
                } else {
                    $blc->h3('Csavar palástnyomás ellenőrzése (külső csavarként):');
                    $blc->math('t_1 = '.$f3->_t1.'[mm]:');
                    $boltClass->moduleBearing($f3->_boltName, $f3->_boltMaterialName, $f3->_VEd, $f3->_steelMaterialName, $f3->_e1, $f3->_e2, $f3->_t1, false, $f3->_betaLf);
                    $blc->math('t_2 = '.$f3->_t2.'[mm]:');
                    $boltClass->moduleBearing($f3->_boltName, $f3->_boltMaterialName, $f3->_VEd, $f3->_steelMaterialName, $f3->_e1, $f3->_e2, $f3->_t2, false, 1);
                }

                if ($f3->_boltMaterialName == '10.9') {
                    $blc->h3('Egy feszített csavar megcsúszása', '');
                    $blc->note('*C* osztályúnak feltételezve, azaz a tönkremenetel ULS állapotban vett megcsúszást és palástnyomásra való tönkremenetelt jelent.');
                    $blc->numeric('mu', ['mu', 'Súrlódási tényező'], 0.2, '', '**Súrlódási tényezők:** **0.5** *Sörétezett vagy szemcsefújt festetlen felület*, **0.4** *Sörétezett vagy szemcsefújt festett felület*, **0.3** *Drótkefézett vagy lángszórással tisztított felület*, **0.2** *Kezeletlen felület*');
                    $blc->def('F_pC', 0.7*$f3->_bfu*$f3->_As/1000,'F_(p,C) = 0.7*f_(u,b)*A_s = %% [kN]', 'Előírt feszítőerő');
                    $blc->def('F_s_Rd', (($f3->_nv*$f3->_mu)/$f3->__GM3)*$f3->_F_pC,'F_(s,Rd) = (n_v*mu)/gamma_(M3)*F_(p,C) = %% [kN]', 'Megcsúszással szembeni ellenállás');
                    $blc->label($f3->_VEd/$f3->_F_s_Rd, '*C* ULS megcsúszási kihasználtság');
                }

                $blc->h3('Csoportos kiszakadás vizsgálata');
                $blc->boo('exc', 'Excentrikus csavarkép', 0, '');
                $exc = 1;
                if ($f3->_exc) {
                    $exc = 0.5;
                }
                $blc->math('t_(min) = '.$f3->_tmin.'[mm]');

                $blc->txt('Központos kiszakadás:');
                $blc->def('A_nt', ($f3->_nr - 1)*$f3->_p2*$f3->_tmin, 'A_(nt) = (n_r - 1)*p_2*t_(min) = %% [mm^2]', 'Húzott keresztmetszeti terület');
                $blc->def('A_nv', 2*($f3->_e1 + ($f3->_nc - 1)*$f3->_p1)*$f3->_tmin, 'A_(nv) = 2*(e_1 + (n_c - 1)*p_1)*t_(min) = %% [mm^2]', 'Nyírt keresztmetszeti terület');
                $blc->def('Veff1Rd', $exc*(($f3->_sfu*$f3->_A_nt)/($f3->__GM2*1000)) + ($f3->_sfy*$f3->_A_nv)/(sqrt(3)*$f3->__GM0*1000), 'V_(eff,1,Rd) = exc*(f_u*A_(nt))/gamma_(M2) + (f_y*A_(nv))/(gamma_(M0)*sqrt(3)) = %% [kN]');
                $blc->label($f3->_FEd/$f3->_Veff1Rd, 'csoportos kp. kiszakadás kihasználtság');

                $blc->txt('Féloldalas kiszakadás:');
                $blc->def('A_nt', (($f3->_nr - 1)*$f3->_p2 + $f3->_e2)*$f3->_tmin, 'A_(nt) = ((n_r - 1)*p_2 + e_2)*t_(min) = %% [mm^2]', 'Húzott keresztmetszeti terület');
                $blc->def('A_nv', 1*($f3->_e1 + ($f3->_nc - 1)*$f3->_p1)*$f3->_tmin, 'A_(nv) = 1*(e_1 + (n_c - 1)*p_1)*t_(min) = %% [mm^2]', 'Nyírt keresztmetszeti terület');
                $blc->def('Veff1Rd', $exc*(($f3->_sfu*$f3->_A_nt)/($f3->__GM2*1000)) + ($f3->_sfy*$f3->_A_nv)/(sqrt(3)*$f3->__GM0*1000), 'V_(eff,1,Rd) = exc*(f_u*A_(nt))/gamma_(M2) + (f_y*A_(nv))/(gamma_(M0)*sqrt(3)) = %% [kN]');
                $blc->label($f3->_FEd/$f3->_Veff1Rd, 'csoportos féloldalas kiszakadás kihasználtság');
                $blc->note('Csoportos kiszakadás egyszer nyírt kapcsolattal számolva.');
                break;
            case 'w':
                $f3->_d0 = 13;
                $f3->_nb = 1;
                $f3->_nc = 1;
                $f3->_p1 = 0;
                $f3->_p2 = 0;
                $f3->_e1 = 0;
                $blc->numeric('Lwwy', ['L_(ww,y)', 'Csomó- és bekötőlemez szélesség'], 100, 'mm', '');
                $f3->_e2 = $f3->_L0w/2;
                $blc->numeric('Lwwx', ['L_(ww,x)', 'Csomólemezek közti átfedés'], 100, 'mm', '');
                $blc->def('Lww', 2*$f3->_Lwwx + $f3->_Lwwy, 'L_(ww) = 2*L_(ww,x) + L_(ww,y) = %% [mm]', 'Átfedés 3 oldalon körbevarrva, egyszeres sarokvarrattal');
                $blc->numeric('aww', ['a_(ww)', 'Varrat gyökméret'], 4, 'mm', '');
                $weldClass->moduleWeld($f3->_Lww, $f3->_aww, $f3->_FEd, $f3->_steelMaterialName, max($f3->_t2, $f3->_t1), false);
                break;
        }

        $blc->h1('Lemezek vizsgálata');
        $blc->def('L0', $f3->_e2*2 + ($f3->_nr - 1)*$f3->_p2, 'L = 2*e_2 + (n_r - 1)*p_2 = %% [mm]', 'Csomólemezek minimum szélessége, csavarképből');
        $blc->numeric('Lp1', ['L_(+,1)', 'Bekötőlemez szélességének növelése alapszélességhez képest'], 0, 'mm', 'Bázislemezen');
        $blc->boo('Lp1Baseonly', 'Bekötőlemez bázisának növelése csak', false, 'Nem párhuzamos bekötőlemez ($t_1$)');
        $blc->numeric('Lp2', ['L_(+,2)', 'Csomólemez bázisának növelése alapszélességhez képest'], 0, 'mm', 'Rácsrúdon');
        $blc->note('Rácsrúd csomólemeze mindig párhuzamos oldalúként van értelmezve.');
        $blc->def('L1base', $f3->_L0 + $f3->_Lp1, 'L_(1,base) = %% [mm]', 'Bekötőlemez szélessége bázislemeznél');
        if ($f3->_Lp1Baseonly) {
            $blc->def('L1end', $f3->_L0, 'L_(1,end) = %% [mm]','Bekötőlemez szélessége rácsrúd felé');
        } else {
            $blc->def('L1end', $f3->_L0 + $f3->_Lp1, 'L_(1,end) = %% [mm]', 'Bekötőlemez szélessége rácsrúd felé');
        }
        $blc->def('L2', $f3->_L0 + $f3->_Lp2, 'L_(2,base) = L_(2,end) = %% [mm]', 'Csomólemez szélessége rácsrúdon');

        $blc->h2('Bekötőlemez ellenőrzése húzásra', 'Bázislemezen');
        $blc->math('t_1 = '.$f3->_t1.'[mm]%%%n_1 = '.$f3->_n1);
        $blc->def('NEd1', $f3->_FEd/$f3->_n1, 'N_(Ed,1) = F_(Ed)/n_1 = %% [kN]');
        $blc->def('L1net', $f3->_L1end - $f3->_nr*$f3->_d0, 'L_(1,n et) = L_(1,end) - n_r*d_0 = %% [mm]', 'Lemez szélesség lyukgyengítéssel');
        $blc->def('A1', $f3->_L1end*$f3->_t1, 'A_1 = L_(1,end)*t_1 = %% [mm^2]', 'Vizsgált keresztmetszeti terület');
        $blc->def('A1net', $f3->_L1net*$f3->_t1, 'A_(1,n et) = L_(1,n et)*t_1 = %% [mm^2]', 'Vizsgált nettó keresztmetszeti terület');
        $blc->def('NplRd1', $ec->NplRd($f3->_A1, $f3->_steelMaterialName, $f3->_t1), 'N_(pl,Rd,1) = (A_1*f_y)/gamma_(M,0) = %% [kN]', 'Teljes km. folyási ellenállása');
        $blc->def('NuRd1', $ec->NuRd($f3->_A1net, $f3->_steelMaterialName, $f3->_t1), 'N_(u,Rd,1) = (0.9*A_(1,n et)*f_u)/gamma_(M,2) = %% [kN]', 'Nettó km. képlékeny töréssel szembeni ellenállása');
        $blc->def('NtRd1', min($f3->_NplRd1, $f3->_NuRd1), 'N_(t,Rd,1) = min{(N_(pl,Rd,1)), (N_(u,Rd,1)):} = %% [kN]', 'Húzási ellenállás');
        $blc->label($f3->_NEd1/$f3->_NtRd1, 'Keresztmetszet kihasználtsága húzásra');

        $blc->h2('Csomólemez ellenőrzése húzásra', 'Rácsrúdon');
        $blc->math('t_2 = '.$f3->_t2.'[mm]%%%n_2 = '.$f3->_n2);
        $blc->def('NEd2', $f3->_FEd/$f3->_n2, 'N_(Ed,2) = F_(Ed)/n_2 = %% [kN]');
        $blc->def('L2net', $f3->_L2 - $f3->_nr*$f3->_d0, 'L_(2,n et) = L_(2) - n_r*d_0 = %% [mm]', 'Lemez szélesség lyukgyengítéssel');
        $blc->def('A2', $f3->_L2*$f3->_t2, 'A_2 = L_(2)*t_2 = %% [mm^2]', 'Vizsgált keresztmetszeti terület');
        $blc->def('A2net', $f3->_L2net*$f3->_t2, 'A_(2,n et) = L_(2,n et)*t_2 = %% [mm^2]', 'Vizsgált nettó keresztmetszeti terület');
        $blc->def('NplRd2', $ec->NplRd($f3->_A2, $f3->_steelMaterialName, $f3->_t2), 'N_(pl,Rd,2) = (A_2*f_y)/gamma_(M,0) = %% [kN]', 'Teljes km. folyási ellenállása');
        $blc->def('NuRd2', $ec->NuRd($f3->_A2net, $f3->_steelMaterialName, $f3->_t2), 'N_(u,Rd,2) = (0.9*A_(2,n et)*f_u)/gamma_(M,2) = %% [kN]', 'Nettó km. képlékeny töréssel szembeni ellenállása');
        $blc->def('NtRd2', min($f3->_NplRd2, $f3->_NuRd2), 'N_(t,Rd,2) = min{(N_(pl,Rd,2)), (N_(u,Rd,2)):} = %% [kN]', 'Húzási ellenállás');
        $blc->label($f3->_NEd2/$f3->_NtRd2, 'Keresztmetszet kihasználtsága húzásra');

        $blc->h2('Bekötőlemez ellenőrzése nyomásra');
        $blc->numeric('nu', ['nu', 'Befogási tényező'], 0.7, '', '**1** csuklós-csuklós; **0.7** csuklós-befogott; **0.5** befogott-befogott');
        $blc->def('epsilon', sqrt(235/$f3->_sfy), 'epsilon = sqrt(235/f_y) = %%', 'Anyagminőségre jellemző segédmennyiség');
        $blc->def('I', $f3->_L1end*pow($f3->_t1, 3)/12, 'I = (L_(1,end)*t_1^3)/12 = %% [mm^4]', 'Inercia');
        $blc->def('i', sqrt($f3->_I/$f3->_A1), 'i = sqrt(I/A_1) = %% [mm]', 'Inerciasugár');
        $blc->numeric('lx', ['L_x', 'Eltartás bázislemeztől'], 50, 'mm');
        $blc->def('l0', $f3->_lx + 2*$f3->_e1 + ($f3->_nc - 1)*$f3->_p1, 'l_0 = l_x + 2*e_1 + (n_c - 1)*p_1 = %% [mm]');
        $blc->def('Lcr', $f3->_nu*$f3->_l0, 'L_(cr) = nu*l_0 = %% [mm]', 'Kihajlási hossz');
        $blc->def('lamda1', $f3->_epsilon*93.9, 'lambda_1 = 93.9*epsilon = %%', 'Euler karcsúság');
        $blc->def('lamdad', $f3->_Lcr/($f3->_i*$f3->_lamda1), 'bar lamda = L_(cr)/(i*lamda_1) = %%', 'Viszonyított karcsúság');
        $blc->def('alpha', 0.49, 'alpha = %%', 'Alakhiba tényező');
        $blc->def('phi', (1 + $f3->_alpha*($f3->_lamdad - 0.2) + pow($f3->_lamdad, 2))/2, 'phi = (1 + alpha*(bar lamda - 0.2)+lamda^2)/2 = %%', 'Segédmennyiség');
        $blc->def('chi',1/($f3->_phi + sqrt(pow($f3->_phi, 2) - pow($f3->_lamdad, 2))), 'chi = (1 + alpha*(bar lamda - 0.2)+lamda^2)/2 = %%', 'Segédmennyiség');
        if ($f3->_chi > 1.0) {
            $blc->def('chi', 1, 'chi := %%');
        }
        $blc->def('NbRd1', ($f3->_chi*$f3->_A1*$f3->_sfy)/($f3->__GM1*1000), 'N_(b,Rd,1) = (chi*A_1*f_y)/gamma_(M1) = %% [kN]');
        $blc->label($f3->_NEd1/$f3->_NbRd1, 'Keresztmetszet kihasználtsága nyomásra');

        $blc->h1('Varratok vizsgálata');

        $blc->h2('Bekötőlemez varratának ellenőrzése bázislemezhez');
        $blc->boo('weldOnBothSide1', 'Kétoldali sarokvarrat figyelembevétele', true);
        $blc->numeric('a1', ['a_1', 'Varrat gyökméret'], 4, 'mm', '');
        $blc->math('L_(1,base) = '.$f3->_L1base.'[mm]%%%n_1 = '.$f3->_n1.'%%%t_1 = '.$f3->_t1.'[mm]');
        $weldClass->moduleWeld($f3->_L1base, $f3->_a1, $f3->_NEd1, $f3->_steelMaterialName, $f3->_t1, $f3->_weldOnBothSide1);

        $blc->h2('Csomólemez és rácsrúd kapcsolata');
        $blc->numeric('Lw0', ['L_(w,0)', 'Varrathossz'], 75, 'mm', '');
        $blc->numeric('nw0', ['n_(w,0)', 'Varrat multiplikátor'], 2, '', '');
        $blc->boo('weldOnBothSidew', 'Kétoldali sarokvarrat figyelembevétele', false);
        $blc->def('Lw', $f3->_Lw0*$f3->_nw0, 'L_w = %% [mm]', 'Összes varrathossz');
        $blc->numeric('aw', ['a_w', 'Varrat gyökméret'], 4, 'mm', '');
        $weldClass->moduleWeld($f3->_Lw, $f3->_aw, $f3->_NEd2, $f3->_steelMaterialName, $f3->_t2, $f3->_weldOnBothSidew);
    }
}
