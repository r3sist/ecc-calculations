<?php declare(strict_types = 1);
// Analysis of bolts and bolted joints according to Eurocodes - Calculation class for ECC framework
// (c) Bence VÁNKOS | https://structure.hu | https://github.com/r3sist/ecc-calculations

namespace Calculation;

use \Base;
use \Ecc\Blc;
use \Ec\Ec;
use Ecc\Bolt\BoltFactory;
use \H3;
use \resist\SVG\SVG;

Class Bolt
{
    public Base $f3;
    private Blc $blc;
    private Ec $ec;
    private BoltFactory $boltFactory;

    public function __construct(Base $f3, Blc $blc, Ec $ec, BoltFactory $boltFactory)
    {
        $this->f3 = $f3;
        $this->blc = $blc;
        $this->ec = $ec;
        $this->boltFactory = $boltFactory;
    }

    /** Module: Optimal e1, e2, p1, p2 and d sizes calculation for Shear */
    public function moduleOptimalForShear($boltMatName, string $steelMatName, float $tPlate, float $d0Bolt): void
    {
        $boltMatName = (string)$boltMatName;

        $deltaDb = [
            '400' => ['360' => 3.18],
            '500' => ['360' => 2.53, '430' => 3.04],
            '600' => ['360' => 2.12, '430' => 2.54, '510' => 3.01, '530' => 3.13],
            '800' => ['360' => 1.59, '430' => 1.90, '510' => 2.26, '530' => 2.34],
            '1000' => ['360' => 1.27, '430' => 1.52, '510' => 1.80, '530' => 1.82],
        ];
//        $delta = $deltaDb[$this->ec->matProp($boltMatName, 'fu')][$this->ec->matProp($steelMatName, 'fu')];
        $delta = $deltaDb[(string)$this->ec->getMaterial($boltMatName)->fu][$this->ec->getMaterial($steelMatName)->fu];
        $this->blc->info0('Javasolt átmérő és peremtávolságok:');
            $this->blc->def('delta', $delta, 'delta = %%', '');
            $this->blc->note('*[Acélszerkezetek - 1. Általános eljárások (2007) 6.3. táblázat]*');
            $this->blc->def('d_min', ceil($this->f3->_delta * $tPlate),'d_(min) = delta*t = %% [mm]', 'Javasolt csavar átmérő');
            $this->blc->def('e1_opt', ceil(2*$d0Bolt),'e_(1,opt) = 2*d_0 = %% [mm]', 'Szélső peremtávolság (csavarképpel párhuzamos)');
            $this->blc->def('e2_opt', ceil(1.5*$d0Bolt),'e_(2,opt) = 1.5*d_0 = %% [mm]', 'Szélső peremtávolság (csavarképre merőleges)');
            $this->blc->def('p1_opt', ceil(3*$d0Bolt),'p_(1,opt) = 3*d_0 = %% [mm]', 'Belső csavartávolság (csavarképpel párhuzamos)');
            $this->blc->def('p2_opt', ceil(3*$d0Bolt),'p_(2,opt) = 3*d_0 = %% [mm]', 'Belső csavartávolság (csavarképre merőleges)');
        $this->blc->info1();
    }

    /**
     * Module: Shear force and bearing resistance per bolt for the ultimate limit state
     * @param float|string $boltMaterialName
     */
    public function moduleShearAndBearingPerBolt(float $e1, float $e2, float $p1, float $p2, string $boltName, $boltMaterialName, string $steelMaterialName, float $VEd, float $tPlate, float $numberOfShearPlanes = 1, bool $innerBolt = true): void
    {
        $boltMaterialName = (string)$boltMaterialName;
        $numberOfShearPlanes = (int)$numberOfShearPlanes;

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
     * @param float|string $boltMaterialName
     */
    public function moduleShear(string $boltName, $boltMaterialName, float $VEd, float $numberOfShearPlanes = 1, float $betaLf = 1) {
        $boltMaterialName = (float)$boltMaterialName;
        $numberOfShearPlanes = (int)$numberOfShearPlanes;

        $this->blc->boo('useA', ['', 'Teljes keresztmetszeti terület figyelembe vétele'], false, 'Menetes rész nem kerülhet nyírt zónába!');
        if ($this->f3->_useA) {
            $this->blc->def('F_vRd', H3::n2($this->ec->FvRd($boltName, $boltMaterialName, $numberOfShearPlanes, $this->ec->getBolt($boltName)->A)), 'F_(v,Rd) = %% [kN]', 'Csavar nyírási ellenállása');
        } else {
            $this->blc->def('F_vRd', H3::n2($this->ec->FvRd($boltName, $boltMaterialName, $numberOfShearPlanes)), 'F_(v,Rd) = %% [kN]', 'Csavar nyírási ellenállása');
        }

        if ($betaLf < 1.0) {
            $this->blc->def('F_vRd', H3::n2($betaLf*$this->f3->_F_vRd), 'F_(v,Rd) := beta_(lf)*F_(v,Rd) = %% [kN]', 'Hosszú kapcsolat vagy béléslemez figyelembe vétele');
        }

        $this->blc->label($VEd/$this->f3->_F_vRd, 'Nyírási kihasználtság');
    }

    /**
     * Module: Bearing force per bolt for the ultimate limit state
     * @param float|string $boltMaterialName
     */
    public function moduleBearing(string $boltName, $boltMaterialName, float $VEd, string $steelMaterialName, float $ep1, float $ep2, float $tPlate, bool $innerBolt, float $betaLf = 1) {
        $boltMaterialName = (float)$boltMaterialName;

        $this->blc->def('F_bRd', H3::n2($this->ec->FbRd($boltName, $boltMaterialName, $steelMaterialName, $ep1, $ep2, $tPlate, $innerBolt)), 'F_(b,Rd) = %% [kN]', 'Csavar palástnyomási ellenállása');
        if ($betaLf < 1.0) {
            $this->blc->def('F_bRd', H3::n2($betaLf*$this->f3->_F_bRd), 'F_(b,Rd) := beta_(lf)*F_(b,Rd) = %% [kN]', 'Hosszú kapcsolat vagy béléslemez figyelembevétele');
        }
        $this->blc->note('$k_1 = '.$this->f3->___k1.'; alpha_b = '.$this->f3->___alphab.'$');
        $this->blc->label($VEd/$this->f3->_F_bRd, 'Palástnyomási kihasználtság');
    }

    public function calc(Base $f3, Blc $blc, Ec $ec): void
    {
        $this->blc->region0('r1', 'Csavar adatbázis');

        $boltDb = $this->boltFactory::BOLTS;

        // Generate multidimensional array for tbl() scheme:
        $scheme = array_keys($boltDb['M12']);
        array_unshift($scheme, 'Csavar');
        $rows = [];
        foreach ($boltDb as $key => $value) {
            array_unshift($value, $key);
            $rows[] = $value;
        }
        $this->blc->tbl($scheme, $rows);
        $this->blc->region1();

        $this->blc->boo('group', ['', 'Csavarkép számítás'], false);
        $this->f3->_nr = 1;
        $this->f3->_nc = 1;
        if ($this->f3->_group) {
            $this->ec->wrapNumerics('nr', 'nc', '$n_r × n_c$ Sorok- és oszlopok száma', 2, 2, '', '', '×');
            $this->blc->boo('groupLL', ['', 'Egyik szárukon kapcsolt szögacélok ellenőrzése húzásra'], false);
            if ($this->f3->_groupLL) {
                $this->f3->_nc = 1;
                $this->blc->info('$n_c := 1$, csak egy csavaroszloppal számol!');
            }
        }
        $this->f3->_nb = $this->f3->_nr * $this->f3->_nc;

        $this->ec->boltListBlock('bName');
        $this->blc->region0('r0', 'Csavar jellemzők');
        $this->blc->def('d_0', $this->ec->getBolt($this->f3->_bName)->d0, 'd_0 = %% [mm]', 'Lyuk átmérő');
        $this->f3->_d = $this->ec->getBolt($this->f3->_bName)->d;
        $this->blc->def('A', $this->ec->getBolt($this->f3->_bName)->A, 'A = %% [mm^2]', 'Csavar keresztmetszeti terület');
        $this->blc->def('A_s', $this->ec->getBolt($this->f3->_bName)->As, 'A_s = %% [mm^2]', 'Csavar húzási keresztmetszet');
        $this->blc->region1();

//        $this->ec->matList('bMat', '8.8', ['', 'Csavar anyagminőség'], 'bolt');
        $this->ec->boltMaterialListBlock('bMat');
        $this->ec->spreadMaterialData($this->f3->_bMat, 'b');
//        $this->ec->matList('sMat', 'S235', ['', 'Acél anyagminőség'], 'steel');
        $this->ec->structuralSteelMaterialListBlock('sMat');
        $this->ec->spreadMaterialData($this->f3->_sMat, 's');

        $this->blc->numeric('t', ['t', 'Kisebbik lemez vastagság'], 10, 'mm', '');
        $this->blc->numeric('n', ['n', 'Nyírási síkok száma'], 1, '', '');
        $this->blc->numeric('N', ['F_(t,Ed)', 'Húzóerő '.($this->f3->_group?'csavarképre':'csavarra')], 20, 'kN', '');
        $this->blc->numeric('V', ['F_(v,Ed)', 'Nyíróerő '.($this->f3->_group?'csavarképre':'csavarra')], 30, 'kN', '');

        $this->f3->_Nb = $this->f3->_N;
        $this->f3->_Vb = $this->f3->_V;
        if ($this->f3->_group) {
            $this->blc->txt('Csavarok száma: ' . $this->f3->_nb);
            $this->blc->def('Nb', \H3::n2($this->f3->_N/$this->f3->_nb), 'F_(t,Ed,b) = F_(t,Ed)/'.$this->f3->_nb.' = %% [kN]', 'Egy csavarra jutó húzóerő');
            $this->blc->def('Vb', \H3::n2($this->f3->_V/$this->f3->_nb), 'F_(v,Ed,b) = F_(v,Ed)/'.$this->f3->_nb.' = %% [kN]', 'Egy csavarra jutó nyíróerő');
        }

        $this->f3->_sfy = $this->ec->fy($this->f3->_sMat, $this->f3->_t);
        $this->blc->note('$f_y(t) = ' . $this->f3->_sfy . ' [N/(mm^2)]$ alkalmazott folyáshatár lemezvastagság alapján.');

        $this->blc->h1('Egy csavar nyírási- és palástnyomási ellenállása', '***A*** osztály: nem feszített, nyírt csavar');
        $this->moduleOptimalForShear($this->f3->_bMat, $this->f3->_sMat, $this->f3->_t, $this->f3->_d_0);
        $this->blc->numeric('e1', ['e_1', 'Peremtávolság (csavarképpel párhuzamos)'], 50, 'mm', '');
        $this->blc->numeric('e2', ['e_2', 'Peremtávolság (csavarképre merőleges)'], 50, 'mm', '');

        $this->f3->_inner = false;
        $this->f3->_p1 = 0;
        $this->f3->_p2 = 0;
        if ($this->f3->_group && $this->f3->_nr > 1) {
            $this->f3->_inner = true;
            $this->blc->note('Belső csavar számítása a mértékadó, mert csavarkép eset van és $n_r > 1$. Figyelembe van véve $p_1, p_2$.');
            $this->blc->numeric('p1', ['p_1', 'Csavartávolság (csavarképpel párhuzamos)'], 50, 'mm', '');
            if ($this->f3->_nc > 1 && !$this->f3->_groupLL) {
                $this->blc->numeric('p2', ['p_2', 'Csavartávolság (csavarképre merőleges)'], 50, 'mm', '');
            }
        }
        $this->blc->note('Egy csavar esetén külsőként számolható. Több csavar esetén belső a mértékadó.');
        $this->moduleShearAndBearingPerBolt($this->f3->_e1, $this->f3->_e2, $this->f3->_p1, $this->f3->_p2, $this->f3->_bName, $this->f3->_bMat, $this->f3->_sMat, $this->f3->_Vb, $this->f3->_t, $this->f3->_n, $this->f3->_inner);

        $this->blc->h1('Egy csavar húzási- és kigombolódási ellenállása', '***D*** nem feszített, húzott és ***E*** feszített, húzott csavarok');
        $this->blc->def('F_tRd', H3::n2($this->ec->FtRd($this->f3->_bName, $this->f3->_bMat)), 'F_(t,Rd) = %% [kN]', 'Csavar húzási ellenállása');
        $this->blc->label($this->f3->_Nb / $this->f3->_F_tRd, 'Húzási kihasználtság');
        $this->blc->def('B_pRd', H3::n2($this->ec->BpRd($this->f3->_bName, $this->f3->_sMat, $this->f3->_t)), 'B_(p,Rd) = %% [kN]', 'Csavar kigombolódási ellenállása');
        $this->blc->label($this->f3->_Nb / $this->f3->_B_pRd, 'Kigombolódási kihasználtság');

        $this->blc->h1('Egy csavar húzás és nyírás interakciója', '***AD*** osztály');
        $this->blc->def('U_vt', H3::n1((($this->f3->_Vb / $this->f3->_F_vRd) + ($this->f3->_Nb / (1.4 * $this->f3->_F_tRd))) * 100), 'U_(vt) = F_(v,Ed)/F_(v,Rd) + F_(t,Ed)/(1.4*F_(t,Rd)) = %% [%]', 'Interakciós kihasználtság');
        $this->blc->label($this->f3->_U_vt / 100, 'Interakciós kihasználtság');

        if ((string) $this->f3->_bMat === '10.9') {
            $this->blc->h1('Egy feszített csavar nyírásra');
            $this->blc->numeric('n_s', ['n_s', 'Súrlódó felületek száma'], 1, '', '');
            $this->blc->numeric('mu', ['mu', 'Súrlódási tényező'], 0.2, '', '**Súrlódási tényezők:** **0.5** *Sörétezett vagy szemcsefújt festetlen felület*, **0.4** *Sörétezett vagy szemcsefújt festett felület*, **0.3** *Drótkefézett vagy lángszórással tisztított felület*, **0.2** *Kezeletlen felület*');
            $this->blc->numeric('F_Ed_ser', ['F_(Ed,ser)', 'Nyíróerő '.($this->f3->_group?'csavarképre':'egy csavarra').' használhatósági határállapotban'], 10, 'kN', '');
            $this->blc->def('F_Ed_ser_b', $this->f3->_F_Ed_ser/$this->f3->_nb, 'F_(Ed,ser,b) = F_(Ed,ser)/'.$this->f3->_nb.' = %% [kN]', 'Egy csvarra eső erő');

            $this->blc->md('***B*** osztályú nyírt csavarok használhatósági határállapotig működnek feszített csavarként.');
            $this->blc->md('Teherbírási határállapotban ***A*** csavarként. Használhatósági határállapotban:');

            $this->blc->def('F_pC', 0.7 * $this->f3->_bfu * $this->f3->_A_s / 1000, 'F_(p,C) = 0.7*f_(u,b)*A_s = %% [kN]', 'Előírt feszítőerő');
            $this->blc->def('F_s_Rd', (($this->f3->_n_s * $this->f3->_mu) / $this->f3->__GM3) * $this->f3->_F_pC, 'F_(s,Rd) = (n_s*mu)/gamma_(M3)*F_(p,C) = %% [kN]', 'Megcsúszással szembeni ellenállás');
            $this->f3->_U_s_ser = $this->f3->_F_Ed_ser_b / $this->f3->_F_s_Rd;
            $this->blc->label($this->f3->_U_s_ser, '*B* Kihasználtság használhatósági határállapotban');

            $this->blc->md('***C*** osztályú nyírt csavar:');
            $this->f3->_U_s = $this->f3->_Vb / $this->f3->_F_s_Rd;
            $this->blc->math('F_(v,Ed,b)/F_(s,Rd) = ' . H3::n3($this->f3->_U_s));
            $this->blc->label(H3::n3($this->f3->_U_s), '*C* Megcsúszási kihasználtság teherbírási határállpotban');
            $this->blc->math('F_(v,Ed,b)/F_(b,Rd) = ' . H3::n3($this->f3->_Vb / $this->f3->_F_bRd));
            $this->blc->label($this->f3->_Vb / $this->f3->_F_bRd, '*C* Palástnyomási kihasználtság');

            $this->blc->md('***CE*** osztályú húzott-nyírt csavar:');
            $this->blc->def('F_s_tv_Rd', (($this->f3->_n_s * $this->f3->_mu) / $this->f3->__GM3) * ($this->f3->_F_pC - 0.8 * $this->f3->_N), 'F_(s,tv,Rd) = (n_s*mu)/gamma_(M3)*(F_(p,C)-0.8*F_(t,Ed)) = %% [kN]', 'Interakciós ellenállás');
            $this->f3->_U_s_tv = $this->f3->_Vb / $this->f3->_F_s_tv_Rd;
            $this->blc->math('F_(v,Ed,b)/F_(s,tv,Rd) = ' . H3::n3($this->f3->_U_s_tv));
            $this->blc->label($this->f3->_U_s_tv, 'Interakciós kihasználtság');
            $this->blc->note('*B* osztályú csavar esetén *BE* interakció ugyanez, $F_(Ed,ser,b)$ alkalmazásával.');
        }

        if ($this->f3->_group) {
            $this->blc->h1('Csavarkép');

            // SVG init
            $xp = 2*$this->f3->_e2 + ($this->f3->_nc - 1)*$this->f3->_p2; // Plate dimensions
            $yp = 2*$this->f3->_e1 + ($this->f3->_nr - 1)*$this->f3->_p1; // Plate dimensions
            $svg = new SVG(450, 450);
            // Plate
            $svg->makeRatio(350, 350, $xp, $yp);
            $svg->setColor('blue');
            $svg->addRectangle(0, 0, $xp, $yp, 25, 25);
            // Bolts
            for ($row = 0; $row <= $this->f3->_nr - 1; $row++) {
                for ($col = 0; $col <= $this->f3->_nc - 1; $col++) {
                    $xi = ($this->f3->_e2 + $col*$this->f3->_p2);
                    $yi = ($this->f3->_e1 + $row*$this->f3->_p1);
                    $svg->addCircle($xi, $yi, ($this->f3->_d_0/2), 25, 25);
                    $svg->makeRatio(350, 350, $xp, $yp);
                }
            }
            // Dimensions
            $svg->setColor('magenta');
            $svg->addDimH(0, $xp, 430, $xp, 25); // Plate horizontal
            $svg->addDimV(0, $yp, 430, $yp, 25); // Plate vertical
            $svg->addDimH(0, $this->f3->_e2, 400, $this->f3->_e2, 25); // e2
            ($this->f3->_nc > 1)?$svg->addDimH($this->f3->_e2, $this->f3->_p2, 400, H3::n0($this->f3->_p2), 25):false; // p2
            $svg->addDimV(0, $this->f3->_e1, 400, $this->f3->_e1, 25); // e1
            ($this->f3->_nr > 1)?$svg->addDimV($this->f3->_e1, $this->f3->_p1, 400, H3::n0($this->f3->_p1), 25):false; // p1
            // Texts & symbols
            $svg->setColor('black');
            $svg->addSymbol(200, 5, 'arrow-up');
            $this->blc->svg($svg);

            $this->blc->h2('Csavarkép nyírási teherbírása');
            $FRd = min($this->ec->FbRd($this->f3->_bName, $this->f3->_bMat, $this->f3->_sMat, $this->f3->_e1, $this->f3->_e2, $this->f3->_t, false), $this->ec->FbRd($this->f3->_bName, $this->f3->_bMat, $this->f3->_sMat, ($this->f3->_p1 != 0)?$this->f3->_p1:$this->f3->_e1, ($this->f3->_p2 != 0)?$this->f3->_p2:$this->f3->_e2, $this->f3->_t, true), $this->ec->FvRd($this->f3->_bName, $this->f3->_bMat, $this->f3->_n, 0)) * $this->f3->_nb;
            if ($this->ec->FvRd($this->f3->_bName, $this->f3->_bMat, $this->f3->_n, 0) >= min($this->ec->FbRd($this->f3->_bName, $this->f3->_bMat, $this->f3->_sMat, $this->f3->_e1, $this->f3->_e2, $this->f3->_t, false), $this->ec->FbRd($this->f3->_bName, $this->f3->_bMat, $this->f3->_sMat, ($this->f3->_p1 != 0)?$this->f3->_p1:$this->f3->_e1, ($this->f3->_p2 != 0)?$this->f3->_p2:$this->f3->_e2, $this->f3->_t, true))) {
                $this->blc->txt('Minden csavar nyírási ellenállása nagyobb bármely másik csavar palástnyomási ellenállásnál, ezért a csavarkép ellenállása lehetne a csavarok palástnyomási ellenállásának összege.');
            }
            $this->blc->math('F_(v,Ed,b) = F_(v,Ed)/'.$this->f3->_nb.' = '.$this->f3->_Vb.' [kN]', 'Egy csavarra jutó nyíróerő centrikus kapcsolat esetén');
            $this->blc->success0('Csavarkép teherbírása:');
                if (($this->f3->_nr - 1) * $this->f3->_p1 > 15 * $this->f3->_d) {
                    $this->blc->def('Lj', max(0.75, 1 - (($this->f3->_nr - 1) * $this->f3->_p1 - 15 * $this->f3->_d) / (200 * $this->f3->_d)), 'L_j = %%', 'Hosszú kapcsolat csavaronként értelmezett csökkentő tényezője');
                    $this->blc->def('FRd', $FRd, 'F_(Rd) = %% [kN]', 'Szélső és belső csavarok nyírási vagy palástnyomási ellenállásának minimuma csavarszámmal felszorozva');
                    $this->blc->def('FRd', $FRd * $this->f3->_Lj, 'F_(Rd, red) = F_(Rd)*L_j = %% [kN]', 'Szélső és belső csavarok nyírási vagy palástnyomási ellenállásának minimuma csavarszámmal felszorozva');
                } else {
                    $this->blc->def('FRd', $FRd, 'F_(Rd) = %% [kN]', 'Szélső és belső csavarok nyírási vagy palástnyomási ellenállásának minimuma csavarszámmal felszorozva');
                }
            $this->blc->success1();
            $this->blc->label($this->f3->_Vb / $this->f3->_FRd, 'legjobban igénybevett csavar nyírási kihasználtsága');

            $this->blc->h2('Lemez nettó keresztmetszet vizsgálata');
            $this->blc->def('l_net', $this->f3->_e2 * 2 + ($this->f3->_nc - 1) * $this->f3->_p2 - $this->f3->_nc * $this->f3->_d_0, 'l_(n\et) = 2*e_2 + (n_c-1)*p_2 - n_c*d_0 = %% [mm]', 'Lemez hossz lyukgyengítéssel');
            $this->blc->def('A', ($this->f3->_e2 * 2 + ($this->f3->_nc - 1) * $this->f3->_p2) * $this->f3->_t, 'A = (2*e_2 + (n_c-1)*p_2)*t = %% [mm^2]', 'Vizsgált keresztmetszeti terület');
            $this->blc->def('A_net', $this->f3->_l_net * $this->f3->_t, 'A_(n et) = l_(n et)*t = %% [mm^2]', 'Vizsgált nettó keresztmetszeti terület');
            $this->blc->def('NplRd', H3::n2($this->ec->NplRd($this->f3->_A, $this->f3->_sMat, $this->f3->_t)), 'N_(pl,Rd) = (A*f_y)/gamma_(M,0) = %% [kN]', 'Teljes km. folyási ellenállása');
            $this->blc->def('NuRd', H3::n2($this->ec->NuRd($this->f3->_A_net, $this->f3->_sMat, $this->f3->_t)), 'N_(u,Rd) = (0.9*A_(n\et)*f_u)/gamma_(M,2) = %% [kN]', 'Nettó km. képlékeny töréssel szembeni ellenállása');
            $this->blc->def('NtRd', H3::n2(min($this->f3->_NplRd, $this->f3->_NuRd)), 'N_(t,Rd) = min{(N_(pl,Rd)), (N_(u,Rd)):} = %% [kN]', 'Húzási ellenállás');
            $this->blc->math('F_(v,Ed)/N_(t,Rd) = ('.$this->f3->_V.'[kN])/('.$this->f3->_NtRd.'[kN])');
            $this->blc->label($this->f3->_V / $this->f3->_NtRd, 'Keresztmetszet kihasználtsága húzásra');

            $this->blc->h2('Csoportos kiszakadás');
            $this->blc->boo('exc', ['', 'Excentrikus csavarkép'], false, '');
            $exc = 1;
            if ($this->f3->_exc) {
                $exc = 0.5;
            }
            $this->blc->math('exc = '.$exc);
            $this->blc->def('A_nt', ($this->f3->_nc - 1) * $this->f3->_p2 * $this->f3->_t, 'A_(nt) = (n_c - 1)*p_2*t = %% [mm^2]');
            $this->blc->def('A_nv', ($this->f3->_e1 + ($this->f3->_nr - 1) * $this->f3->_p1) * $this->f3->_t, 'A_(nv) = (e_1 + (n_r - 1)*p_1)*t = %% [mm^2]');
            $this->blc->def('Veff1Rd', $exc * (($this->f3->_sfu * $this->f3->_A_nt) / ($this->f3->__GM2 * 1000)) + ($this->f3->_sfy * $this->f3->_A_nv) / (sqrt(3) * $this->f3->__GM0 * 1000), 'V_(eff,1,Rd) = exc*(f_u*A_(nt))/gamma_(M2) + (f_y*A_(nv))/(gamma_(M0)*sqrt(3)) = %% [kN]');
            $this->blc->math('F_(v,Ed)/V_(eff,1,Rd) = ('.$this->f3->_V.'[kN])/('.$this->f3->_Veff1Rd.'[kN])');
            $this->blc->label($this->f3->_V / $this->f3->_Veff1Rd, 'csoportos kiszakadás kihasználtsága');
            $this->blc->note('Féloldalas kiszakadást nem vizsgál!');

            if ($this->f3->_groupLL) {
                $this->blc->h1('Egyik szárukon kapcsolt szögacélok húzásra');
                $this->blc->note('Külpontosság elhanyagolható.');
                $this->blc->math('n_r = ' . $this->f3->_nr . ' × ' . $this->f3->_bName . '%%%(n_c = 1)', 'Függőleges csavarkép átvétele');
                $this->ec->sectionFamilyListBlock('sectionFamily', ['', 'Szelvény család'], 'L');
                $this->ec->sectionListBlock($this->f3->_sectionFamily);
                $this->ec->spreadSectionData($this->f3->_sectionName, true);
                $this->blc->math('d_0 = ' . $this->f3->_d_0 . '[mm]%%%t_(w,L) = ' . $this->f3->_sectionData['tw'] * 10 . '[mm]%%%f_(u,s) = ' . $this->f3->_sfu . '[N/(mm^2)] %%% A_(n\et) = ' . $this->f3->_A_net . ' [mm^2]');
                $this->blc->def('AnetL', $this->f3->_sectionData['Ax'] * 100 - $this->f3->_d_0 * $this->f3->_sectionData['tw'] * 10, 'A_(n et,L) = A_(x,L) - 1*d_0*t_(w,L) = %% [mm^2]');
                $this->blc->note('Egy oszlop csavar csak!');

                $this->blc->def('e1', $this->f3->_e1_opt, 'e_(1,min) := e_(1,opt) = ' . $this->f3->_e1_opt . ' [mm]', 'Peremtávolság erő irányban, fenti számítások alapján');
                $this->blc->def('e2', (min($this->f3->_sectionData['b'], $this->f3->_sectionData['h']) * 10) / 2, 'e_2 = (min{(h_L),(b_L):})/2 = %% [mm]', 'Peremtávolság erő irányra merőlegesen');

                if ($this->f3->_nr == 1) {
                    $this->blc->txt('Erőátadás irányában egy csavar esete:');
                    $NuRd = (2 * ($this->f3->_e2 - 0.5 * $this->f3->_d_0) * $this->f3->_sectionData['tw'] * 10 * $this->f3->_sfu) / ($this->f3->__GM2 * 1000);
                    $this->blc->success0('Rúd keresztmetszeti ellenállása húzásra:');
                    $this->blc->def('NuRd', $NuRd, 'N_(u,Rd) = (2*(e_2-0.5*d_0)*t_(w,L)*f_(u,s))/gamma_(M2) = %% [kN]');
                    $this->blc->success1();
                } else if ($this->f3->_nr == 2) {
                    $this->blc->txt('Erőátadás irányában két csavar esete:');
                    if ($this->f3->_p1 <= 2.5 * $this->f3->_d_0) {
                        $this->blc->def('beta_2', 0.4, 'beta_2 = %%', 'p_1 < 2.5*d_0');
                    } else if ($this->f3->_p1 >= 5 * $this->f3->_d_0) {
                        $this->blc->def('beta_2', 0.7, 'beta_2 = %%', 'p_1 > 5*d_0');
                    } else {
                        $this->blc->def('beta_2', $this->ec->linterp(2.5 * $this->f3->_d_0, 0.4, 5 * $this->f3->_d_0, 0.7, $this->f3->_p1), 'beta_2 = %%', 'Lineárisan interpolált érték!');
                    }
                    $NuRd = ($this->f3->_beta_2 * $this->f3->_AnetL * $this->f3->_sfu) / ($this->f3->__GM2 * 1000);
                    $this->blc->success0('Rúd keresztmetszeti ellenállása húzásra:');
                    $this->blc->def('NuRd', $NuRd, 'N_(u,Rd) = (beta_2*A_(n et,L)*f_(u,s))/gamma_(M2) = %% [kN]');
                    $this->blc->success1();
                } else {
                    if ($this->f3->_p1 <= 2.5 * $this->f3->_d_0) {
                        $this->blc->def('beta_3', 0.5, 'beta_2 = %%', 'p_1 < 2.5*d_0');
                    } else if ($this->f3->_p1 >= 5 * $this->f3->_d_0) {
                        $this->blc->def('beta_3', 0.7, 'beta_2 = %%', 'p_1 > 5*d_0');
                    } else {
                        $this->blc->def('beta_3', $this->ec->linterp(2.5 * $this->f3->_d_0, 0.5, 5 * $this->f3->_d_0, 0.7, $this->f3->_p1), 'beta_3 = %%', 'Lineárisan interpolált érték!');
                    }
                    $NuRd = ($this->f3->_beta_3 * $this->f3->_AnetL * $this->f3->_sfu) / ($this->f3->__GM2 * 1000);
                    $this->blc->success0('Rúd keresztmetszeti ellenállása húzásra:');
                    $this->blc->def('NuRd', $NuRd, 'N_(u,Rd) = (beta_3*A_(n et,L)*f_(u,s))/gamma_(M2) = %% [kN]');
                    $this->blc->success1();
                }
                $this->blc->label($this->f3->_V / $this->f3->_NuRd, 'Húzási kihasználtság');
                $this->blc->note('`beta_2` és `beta_3` külpontosság miatti tényezők. Táblázatos érték [Acélszuerkezetek általános eljárások (2007) 5.1/5.2 táblázat 40.o.]');
            }
        }
    }
}