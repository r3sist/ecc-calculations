<?php declare(strict_types = 1);
/**
 * Analysis of steel windbrace joint according to Eurocodes - Calculation class for Statika framework
 * (c) Bence VÁNKOS | https://structure.hu | https://github.com/r3sist/ecc-calculations
 */

namespace Statika\Calculations;

use \H3;
use Statika\Ec;
use Statika\EurocodeInterface;

Class JointWindbrace
{
    private Bolt $boltCalculation;
    private Weld $weldCalculation;
    private SteelSection $steelSectionCalculation;

    public function __construct(Bolt $boltCalculation, Weld $weldCalculation, SteelSection $steelSectionCalculation)
    {
        $this->boltCalculation = $boltCalculation;
        $this->weldCalculation = $weldCalculation;
        $this->steelSectionCalculation = $steelSectionCalculation;
    }

    /**
     * @param Ec $ec
     * @throws \Profil\Exceptions\InvalidSectionNameException
     * @throws \Statika\Material\InvalidMaterialNameException
     */
    public function calc(EurocodeInterface $ec): void
    {
        $ec->img('https://structure.hu/ecc/JointWindbrace0.jpg');

        $ec->structuralSteelMaterialListBlock('steelMaterialName', 'S235', ['', 'Szelvények, lemezek anyagminősége']);
        $steelMaterial = $ec->getMaterial($ec->steelMaterialName);
        $ec->lst('forceSource', ['Bekötés szelvény húzásra' => 'huzas', 'Bekötés erőre' => 'ero'], ['', 'Erő megadása'], 'ero', '');

        switch ($ec->forceSource) {
            case 'huzas':
                $ec->sectionFamilyListBlock('sectionFamily', ['', 'Szelvény család'], 'O');
                $ec->sectionListBlock($ec->sectionFamily, 'sectionName', ['', 'Szelvény'], 'D20');
                $section = $ec->getSection($ec->sectionName);
                $ec->def('FEd', $this->steelSectionCalculation->moduleNplRd($section->_Ax*100, $ec->steelMaterialName, max($section->_tw/10, $section->_tf/10)), 'F_(Ed) := N_(pl,Rd) = (A*f_y)/gamma_(M,0) = %% [kN]', 'Teljes km. folyási ellenállása');
                break;
            case 'ero':
                $ec->numeric('FEd', ['F_(Ed)', 'Húzó-nyomó erő rácsrúdban'], 50, 'kN');
                break;
        }

        $ec->numeric('t1', ['t_1', 'Bekötőlemez lemezvastagság'], 10, 'mm', 'Bázislemezhez hegesztett csomólemez');
        $ec->boo('n1', ['n_1', 'Dupla bekötőlemez bázislemezen'], false, 'Nyírási síkok számához');
        $ec->n1 = (int)$ec->n1 + 1;
        $ec->numeric('t2', ['t_2', 'Csomólemez lemezvastagság'], 10, 'mm', 'Rácsrúdhoz hegesztett csomólemez');
        $ec->boo('n2', ['n_2', 'Dupla csomólemez rácsrúdon'], false, 'Nyírási síkok számához');
        $ec->n2 = (int)$ec->n2 + 1;
        $nv0 = $ec->n1 + $ec->n2 - 1;
        $ec->def('nv', ($nv0 > 2)?2:$nv0, 'n_v = %%', 'Nyírási síkok száma csavarképben');
        $ec->note('Nyírási síkok száma kettőre van korlátozva.');

        $ec->lst('connectionType', ['Csavaros bekötés' => 'b', 'Hegesztett bekötés' => 'w'], ['', 'Kialakítás'], 'b', 'Csavaros bekötés: *bekötő-* és *csomólemez* csavarral kapcsolva. Hegesztett bekötés: *bekötő-* és *csomólemez* M12 oválfuratos csavarral szerelve és összehegesztve.');
        switch ($ec->connectionType) {
            case 'b':
                $ec->h1('Csavarkép ellenőrzése');
                $ec->boltListBlock('boltName');
                $bolt = $ec->getBolt($ec->boltName);
                $ec->boltMaterialListBlock('boltMaterialName', '8.8', ['', 'Csavarok anyagminősége']);
                $boltMaterial = $ec->getMaterial($ec->boltMaterialName);
                $ec->def('d0', $bolt->d0,'d_0 = %% [mm]', 'Lyuk átmérő');
                $ec->d = $bolt->d;
                $ec->As = $bolt->As;

                $this->boltCalculation->moduleOptimalForShear($ec->boltMaterialName, $ec->steelMaterialName, min($ec->t1, $ec->t2), $ec->d0);

                $ec->numeric('nr', ['n_r', 'Csavar sorok száma'], 1, '', 'Erőre merőleges irányban');
                $ec->numeric('nc', ['n_c', 'Csavar oszlopok száma'], 1, '', 'Erővel párhuzamos irányban');
                $ec->def('nb', $ec->nr*$ec->nc, 'n_b = %%', 'Csavarok száma');
                $ec->numeric('e1', ['e_1', 'Peremtávolság (csavarképpel párhuzamos)'], 50, 'mm', 'Erővel párhuzamos irányban');
                $ec->numeric('e2', ['e_2', 'Peremtávolság (csavarképre merőleges)'], 50, 'mm', 'Erőre merőleges irányban');
                if ($ec->nc > 1) {
                    $ec->numeric('p1', ['p_1', 'Csavartávolság (csavarképpel párhuzamos)'], 50, 'mm', 'Erővel párhuzamos irányban');
                } else {
                    $ec->p1 = $ec->e1;
                }
                if ($ec->nr > 1) {
                    $ec->numeric('p2', ['p_2', 'Csavartávolság (csavarképre merőleges)'], 50, 'mm', 'Erőre merőleges irányban');
                } else {
                    $ec->p2 = $ec->e2;
                }

                $ec->h2('Egy csavar nyírási- és palástnyomási ellenállásának ellenőrzése', '***A*** osztály: nem feszített, nyírt csavar');
                $ec->def('tmin', min($ec->t1, $ec->t2), 't_(min) = %% [mm]', 'Palástnyomás ellenőrzése kisebbik csomólemezre');

                $ec->def('VEd', $ec->FEd/$ec->nb, 'V_(Ed) = F_(Ed)/n_b = %% [kN]', 'Egy csavarra jutó nyíróerő');
                $ec->betaLf = 1;
                if (($ec->nc-1)*$ec->p1 >= 15*$ec->d) {
                    $ec->math('L_j = (n_c - 1)*p_1%%%ge15*d');
                    $ec->txt('Hosszú kapcsolat!');
                    $ec->def('betaLf', 1-(($ec->nc-1)*$ec->p1 - 15*$ec->d)/(200*$ec->d), 'beta_(Lf) = 1 - ((n_c-1)*p_1 - 15*d)/(200*d) = %%', 'Csökkentő tényező csavar ellenállásokhoz');
                    if ($ec->betaLf < 0.75) {
                        $ec->def('betaLf', 0.75, 'beta_(Lf) := %%');
                    }
                }

                $ec->boo('shim', ['', 'Bekötőlemez számítása béléslemezként'], false);
                if ($ec->shim) {
                    $ec->betap = 1;
                    if ($ec->t1 > $ec->d/3) {
                        $ec->math('(t_1 = '.$ec->t1.') gt (d/3 = '. H3::n1($ec->d/3) .') [mm]');
                        $ec->def('betap', H3::n3((9*$ec->d)/(8*$ec->d + 3*$ec->t1)), 'beta_p = 9d/(8d+3t_1) = %%', 'Csökkentő tényező csavar ellenállásokhoz');
                        if ($ec->betap > 1) {
                            $ec->def('betap', 1, 'beta_p := %%');
                        }
                        $ec->txt('*Továbbiakban $beta_p$ tényező kombinált $beta_(Lf)$ tényezőként hivatkozva.*');
                    } else {
                        $ec->math('t_1 = '.$ec->t1.') le (d/3 = '. H3::n1($ec->d/3) .') [mm]');
                        $ec->txt('Csökkentés nem szükséges.');
                    }
                    $ec->betaLf = $ec->betaLf*$ec->betap; // beta_Lf és beta_p összegyúrása
                }

                $ec->h3('Csavar nyírás ellenőrzése:');
                $this->boltCalculation->moduleShear($ec->boltName, $ec->boltMaterialName, $ec->VEd, $ec->nv, $ec->betaLf);

                if ($ec->nc > 1 || $ec->nr > 1) {
                    $ec->h3('Csavar palástnyomás ellenőrzése, belső csavarokhoz:');
                    $ec->math('t_1 = '.$ec->t1.'[mm]:');
                    $this->boltCalculation->moduleBearing($ec->boltName, $ec->boltMaterialName, $ec->VEd, $ec->steelMaterialName, $ec->p1, $ec->p2, $ec->t1, true, $ec->betaLf);
                    $ec->math('t_2 = '.$ec->t2.'[mm]:');
                    $this->boltCalculation->moduleBearing($ec->boltName, $ec->boltMaterialName, $ec->VEd, $ec->steelMaterialName, $ec->p1, $ec->p2, $ec->t2, true, 1);
                } else {
                    $ec->h3('Csavar palástnyomás ellenőrzése (külső csavarként):');
                    $ec->math('t_1 = '.$ec->t1.'[mm]:');
                    $this->boltCalculation->moduleBearing($ec->boltName, $ec->boltMaterialName, $ec->VEd, $ec->steelMaterialName, $ec->e1, $ec->e2, $ec->t1, false, $ec->betaLf);
                    $ec->math('t_2 = '.$ec->t2.'[mm]:');
                    $this->boltCalculation->moduleBearing($ec->boltName, $ec->boltMaterialName, $ec->VEd, $ec->steelMaterialName, $ec->e1, $ec->e2, $ec->t2, false, 1);
                }

                if ($ec->boltMaterialName === '10.9') {
                    $ec->h3('Egy feszített csavar megcsúszása', '');
                    $ec->note('*C* osztályúnak feltételezve, azaz a tönkremenetel ULS állapotban vett megcsúszást és palástnyomásra való tönkremenetelt jelent.');
                    $ec->numeric('mu', ['mu', 'Súrlódási tényező'], 0.2, '', '**Súrlódási tényezők:** **0.5** *Sörétezett vagy szemcsefújt festetlen felület*, **0.4** *Sörétezett vagy szemcsefújt festett felület*, **0.3** *Drótkefézett vagy lángszórással tisztított felület*, **0.2** *Kezeletlen felület*');
                    $ec->def('F_pC', 0.7*$boltMaterial->fu*$ec->As/1000,'F_(p,C) = 0.7*f_(u,b)*A_s = %% [kN]', 'Előírt feszítőerő');
                    $ec->def('F_s_Rd', (($ec->nv*$ec->mu)/$ec::GM3)*$ec->F_pC,'F_(s,Rd) = (n_v*mu)/gamma_(M3)*F_(p,C) = %% [kN]', 'Megcsúszással szembeni ellenállás');
                    $ec->label($ec->VEd/$ec->F_s_Rd, '*C* ULS megcsúszási kihasználtság');
                }

                $ec->h3('Csoportos kiszakadás vizsgálata');
                $ec->boo('exc', ['', 'Excentrikus csavarkép'], false, '');
                $exc = 1;
                if ($ec->exc) {
                    $exc = 0.5;
                }
                $ec->math('t_(min) = '.$ec->tmin.'[mm]');

                $ec->txt('Központos kiszakadás:');
                $ec->def('A_nt', ($ec->nr - 1)*$ec->p2*$ec->tmin, 'A_(nt) = (n_r - 1)*p_2*t_(min) = %% [mm^2]', 'Húzott keresztmetszeti terület');
                $ec->def('A_nv', 2*($ec->e1 + ($ec->nc - 1)*$ec->p1)*$ec->tmin, 'A_(nv) = 2*(e_1 + (n_c - 1)*p_1)*t_(min) = %% [mm^2]', 'Nyírt keresztmetszeti terület');
                $ec->def('Veff1Rd', $exc*(($steelMaterial->fu*$ec->A_nt)/($ec::GM2*1000)) + ($steelMaterial->fy*$ec->A_nv)/(sqrt(3)*$ec::GM0*1000), 'V_(eff,1,Rd) = exc*(f_u*A_(nt))/gamma_(M2) + (f_y*A_(nv))/(gamma_(M0)*sqrt(3)) = %% [kN]');
                $ec->label($ec->FEd/$ec->Veff1Rd, 'csoportos kp. kiszakadás kihasználtság');

                $ec->txt('Féloldalas kiszakadás:');
                $ec->def('A_nt', (($ec->nr - 1)*$ec->p2 + $ec->e2)*$ec->tmin, 'A_(nt) = ((n_r - 1)*p_2 + e_2)*t_(min) = %% [mm^2]', 'Húzott keresztmetszeti terület');
                $ec->def('A_nv', 1*($ec->e1 + ($ec->nc - 1)*$ec->p1)*$ec->tmin, 'A_(nv) = 1*(e_1 + (n_c - 1)*p_1)*t_(min) = %% [mm^2]', 'Nyírt keresztmetszeti terület');
                $ec->def('Veff1Rd', $exc*(($steelMaterial->fu*$ec->A_nt)/($ec::GM2*1000)) + ($steelMaterial->fy*$ec->A_nv)/(sqrt(3)*$ec::GM0*1000), 'V_(eff,1,Rd) = exc*(f_u*A_(nt))/gamma_(M2) + (f_y*A_(nv))/(gamma_(M0)*sqrt(3)) = %% [kN]');
                $ec->label($ec->FEd/$ec->Veff1Rd, 'csoportos féloldalas kiszakadás kihasználtság');
                $ec->note('Csoportos kiszakadás egyszer nyírt kapcsolattal számolva.');
                break;
            case 'w':
                $ec->d0 = 13;
                $ec->nb = 1;
                $ec->nc = 1;
                $ec->p1 = 0;
                $ec->p2 = 0;
                $ec->e1 = 0;
                $ec->numeric('Lwwy', ['L_(ww,y)', 'Csomó- és bekötőlemez szélesség'], 100, 'mm', '');
                $ec->e2 = $ec->L0w/2;
                $ec->numeric('Lwwx', ['L_(ww,x)', 'Csomólemezek közti átfedés'], 100, 'mm', '');
                $ec->def('Lww', 2*$ec->Lwwx + $ec->Lwwy, 'L_(ww) = 2*L_(ww,x) + L_(ww,y) = %% [mm]', 'Átfedés 3 oldalon körbevarrva, egyszeres sarokvarrattal');
                $ec->numeric('aww', ['a_(ww)', 'Varrat gyökméret'], 4, 'mm', '');
                $this->weldCalculation->moduleWeld($ec->Lww, $ec->aww, $ec->FEd, $ec->steelMaterialName, max($ec->t2, $ec->t1), false);
                break;
        }

        $ec->h1('Lemezek vizsgálata');
        $ec->def('L0', $ec->e2*2 + ($ec->nr - 1)*$ec->p2, 'L = 2*e_2 + (n_r - 1)*p_2 = %% [mm]', 'Csomólemezek minimum szélessége, csavarképből');
        $ec->numeric('Lp1', ['L_(+,1)', 'Bekötőlemez szélességének növelése alapszélességhez képest'], 0, 'mm', 'Bázislemezen');
        $ec->boo('Lp1Baseonly', ['', 'Bekötőlemez bázisának növelése csak'], false, 'Nem párhuzamos bekötőlemez ($t_1$)');
        $ec->numeric('Lp2', ['L_(+,2)', 'Csomólemez bázisának növelése alapszélességhez képest'], 0, 'mm', 'Rácsrúdon');
        $ec->note('Rácsrúd csomólemeze mindig párhuzamos oldalúként van értelmezve.');
        $ec->def('L1base', $ec->L0 + $ec->Lp1, 'L_(1,base) = %% [mm]', 'Bekötőlemez szélessége bázislemeznél');
        if ($ec->Lp1Baseonly) {
            $ec->def('L1end', $ec->L0, 'L_(1,end) = %% [mm]','Bekötőlemez szélessége rácsrúd felé');
        } else {
            $ec->def('L1end', $ec->L0 + $ec->Lp1, 'L_(1,end) = %% [mm]', 'Bekötőlemez szélessége rácsrúd felé');
        }
        $ec->def('L2', $ec->L0 + $ec->Lp2, 'L_(2,base) = L_(2,end) = %% [mm]', 'Csomólemez szélessége rácsrúdon');

        $ec->h2('Bekötőlemez ellenőrzése húzásra', 'Bázislemezen');
        $ec->math('t_1 = '.$ec->t1.'[mm]%%%n_1 = '.$ec->n1);
        $ec->def('NEd1', $ec->FEd/$ec->n1, 'N_(Ed,1) = F_(Ed)/n_1 = %% [kN]');
        $ec->def('L1net', $ec->L1end - $ec->nr*$ec->d0, 'L_(1,n et) = L_(1,end) - n_r*d_0 = %% [mm]', 'Lemez szélesség lyukgyengítéssel');
        $ec->def('A1', $ec->L1end*$ec->t1, 'A_1 = L_(1,end)*t_1 = %% [mm^2]', 'Vizsgált keresztmetszeti terület');
        $ec->def('A1net', $ec->L1net*$ec->t1, 'A_(1,n et) = L_(1,n et)*t_1 = %% [mm^2]', 'Vizsgált nettó keresztmetszeti terület');
        $ec->def('NplRd1', $this->steelSectionCalculation->moduleNplRd($ec->A1, $ec->steelMaterialName, $ec->t1), 'N_(pl,Rd,1) = (A_1*f_y)/gamma_(M,0) = %% [kN]', 'Teljes km. folyási ellenállása');
        $ec->def('NuRd1', $this->steelSectionCalculation->moduleNuRd($ec->A1net, $ec->steelMaterialName, $ec->t1), 'N_(u,Rd,1) = (0.9*A_(1,n et)*f_u)/gamma_(M,2) = %% [kN]', 'Nettó km. képlékeny töréssel szembeni ellenállása');
        $ec->def('NtRd1', min($ec->NplRd1, $ec->NuRd1), 'N_(t,Rd,1) = min{(N_(pl,Rd,1)), (N_(u,Rd,1)):} = %% [kN]', 'Húzási ellenállás');
        $ec->label($ec->NEd1/$ec->NtRd1, 'Keresztmetszet kihasználtsága húzásra');

        $ec->h2('Csomólemez ellenőrzése húzásra', 'Rácsrúdon');
        $ec->math('t_2 = '.$ec->t2.'[mm]%%%n_2 = '.$ec->n2);
        $ec->def('NEd2', $ec->FEd/$ec->n2, 'N_(Ed,2) = F_(Ed)/n_2 = %% [kN]');
        $ec->def('L2net', $ec->L2 - $ec->nr*$ec->d0, 'L_(2,n et) = L_(2) - n_r*d_0 = %% [mm]', 'Lemez szélesség lyukgyengítéssel');
        $ec->def('A2', $ec->L2*$ec->t2, 'A_2 = L_(2)*t_2 = %% [mm^2]', 'Vizsgált keresztmetszeti terület');
        $ec->def('A2net', $ec->L2net*$ec->t2, 'A_(2,n et) = L_(2,n et)*t_2 = %% [mm^2]', 'Vizsgált nettó keresztmetszeti terület');
        $ec->def('NplRd2', $this->steelSectionCalculation->moduleNplRd($ec->A2, $ec->steelMaterialName, $ec->t2), 'N_(pl,Rd,2) = (A_2*f_y)/gamma_(M,0) = %% [kN]', 'Teljes km. folyási ellenállása');
        $ec->def('NuRd2', $this->steelSectionCalculation->moduleNuRd($ec->A2net, $ec->steelMaterialName, $ec->t2), 'N_(u,Rd,2) = (0.9*A_(2,n et)*f_u)/gamma_(M,2) = %% [kN]', 'Nettó km. képlékeny töréssel szembeni ellenállása');
        $ec->def('NtRd2', min($ec->NplRd2, $ec->NuRd2), 'N_(t,Rd,2) = min{(N_(pl,Rd,2)), (N_(u,Rd,2)):} = %% [kN]', 'Húzási ellenállás');
        $ec->label($ec->NEd2/$ec->NtRd2, 'Keresztmetszet kihasználtsága húzásra');

        $ec->h2('Bekötőlemez ellenőrzése nyomásra');
        $ec->numeric('nu', ['nu', 'Befogási tényező'], 0.7, '', '**1** csuklós-csuklós; **0.7** csuklós-befogott; **0.5** befogott-befogott');
        $ec->def('epsilon', sqrt(235/$steelMaterial->fy), 'epsilon = sqrt(235/f_y) = %%', 'Anyagminőségre jellemző segédmennyiség');
        $ec->def('I', $ec->L1end* ($ec->t1 ** 3) /12, 'I = (L_(1,end)*t_1^3)/12 = %% [mm^4]', 'Inercia');
        $ec->def('i', sqrt($ec->I/$ec->A1), 'i = sqrt(I/A_1) = %% [mm]', 'Inerciasugár');
        $ec->numeric('lx', ['L_x', 'Eltartás bázislemeztől'], 50, 'mm');
        $ec->def('l0', $ec->lx + 2*$ec->e1 + ($ec->nc - 1)*$ec->p1, 'l_0 = l_x + 2*e_1 + (n_c - 1)*p_1 = %% [mm]');
        $ec->def('Lcr', $ec->nu*$ec->l0, 'L_(cr) = nu*l_0 = %% [mm]', 'Kihajlási hossz');
        $ec->def('lamda1', $ec->epsilon*93.9, 'lambda_1 = 93.9*epsilon = %%', 'Euler karcsúság');
        $ec->def('lamdad', $ec->Lcr/($ec->i*$ec->lamda1), 'bar lamda = L_(cr)/(i*lamda_1) = %%', 'Viszonyított karcsúság');
        $ec->def('alpha', 0.49, 'alpha = %%', 'Alakhiba tényező');
        $ec->def('phi', (1 + $ec->alpha*($ec->lamdad - 0.2) + ($ec->lamdad ** 2))/2, 'phi = (1 + alpha*(bar lamda - 0.2)+lamda^2)/2 = %%', 'Segédmennyiség');
        $ec->def('chi',1/($ec->phi + sqrt(($ec->phi ** 2) - ($ec->lamdad ** 2))), 'chi = (1 + alpha*(bar lamda - 0.2)+lamda^2)/2 = %%', 'Segédmennyiség');
        if ($ec->chi > 1.0) {
            $ec->def('chi', 1, 'chi := %%');
        }
        $ec->def('NbRd1', ($ec->chi*$ec->A1*$steelMaterial->fy)/($ec::GM1*1000), 'N_(b,Rd,1) = (chi*A_1*f_y)/gamma_(M1) = %% [kN]');
        $ec->label($ec->NEd1/$ec->NbRd1, 'Keresztmetszet kihasználtsága nyomásra');

        $ec->h1('Varratok vizsgálata');

        $ec->h2('Bekötőlemez varratának ellenőrzése bázislemezhez');
        $ec->boo('weldOnBothSide1', ['', 'Kétoldali sarokvarrat figyelembevétele'], true);
        $ec->numeric('a1', ['a_1', 'Varrat gyökméret'], 4, 'mm', '');
        $ec->math('L_(1,base) = '.$ec->L1base.'[mm]%%%n_1 = '.$ec->n1.'%%%t_1 = '.$ec->t1.'[mm]');
        $this->weldCalculation->moduleWeld($ec->L1base, $ec->a1, $ec->NEd1, $ec->steelMaterialName, $ec->t1, (bool)$ec->weldOnBothSide1);

        $ec->h2('Csomólemez és rácsrúd kapcsolata');
        $ec->numeric('Lw0', ['L_(w,0)', 'Varrathossz'], 75, 'mm', '');
        $ec->numeric('nw0', ['n_(w,0)', 'Varrat multiplikátor'], 2, '', '');
        $ec->boo('weldOnBothSidew', ['', 'Kétoldali sarokvarrat figyelembevétele'], false);
        $ec->def('Lw', $ec->Lw0*$ec->nw0, 'L_w = %% [mm]', 'Összes varrathossz');
        $ec->numeric('aw', ['a_w', 'Varrat gyökméret'], 4, 'mm', '');
        $this->weldCalculation->moduleWeld($ec->Lw, $ec->aw, $ec->NEd2, $ec->steelMaterialName, $ec->t2, (bool)$ec->weldOnBothSidew);
    }
}
