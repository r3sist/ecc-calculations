<?php declare(strict_types = 1);
/**
 * Analysis of bolts and bolted joints according to Eurocodes - Calculation class for Statika framework
 * (c) Bence VÁNKOS | https://structure.hu | https://github.com/r3sist/ecc-calculations
 */

namespace Statika\Calculations;

use \Base;
use \Statika\Blc;
use Statika\BlocksInterface;
use Statika\Bolt\BoltFactory;
use Statika\Bolt\InvalidBoltNameException;
use Statika\Material\InvalidMaterialNameException;
use \H3;
use Profil\Exceptions\InvalidSectionNameException;
use \resist\SVG\SVG;
use Statika\EurocodeInterface;

Class Bolt
{
    private BlocksInterface $blc;
    private EurocodeInterface $ec;
    private BoltFactory $boltFactory;
    private SteelSection $steelSection;

    /**
     * Bolt constructor.
     * @param Ec $ec
     * @param BoltFactory $boltFactory
     * @param SteelSection $steelSection
     */
    public function __construct(EurocodeInterface $ec, BoltFactory $boltFactory, SteelSection $steelSection)
    {
        $this->ec = $ec;
        $this->boltFactory = $boltFactory;
        $this->steelSection = $steelSection;
    }

    /**
     * @throws \Statika\Material\InvalidMaterialNameException
     * @todo test
     */
    public function moduleFtRd(string $boltName, $botlMaterialName, bool $verbose = true): float
    {
        $botlMaterialName = (string) $botlMaterialName;

        $result = (0.9 * $this->ec->getMaterial($botlMaterialName)->fu * $this->ec->getBolt($boltName)->As) / (1000 * $this->ec::GM2);
        if ($verbose) {
            $this->ec->note('Húzás általános képlet: $F_(t,Rd) = (0.9*f_(u,b)*A_s)/(gamma_(M2))$');
        }
        return $result;
    }

    /**
     * @todo test
     */
    public function moduleBpRd(string $boltName, string $steelMaterialname, float $t): float
    {
        $this->ec->note('`BpRd` kigombolódás általános képlet: $(0.6* pi *d_m*f_(u,s)*t)/(gamma_(M2))$');
        return (0.6 * pi() * $this->ec->getBolt($boltName)->dm * $this->ec->fu($steelMaterialname, $t) * $t) / (1000 * $this->ec::GM2);
    }

    // Nyírt csavar

    /**
     * @param float|string $boltMaterialName
     * @todo test
     */
    public function moduleFvRd(string $boltName, $boltMaterialName, float $n, float $As = 0): float
    {
        $boltMaterialName = (string) $boltMaterialName;

        if ($As == 0) {
            $As = $this->ec->getBolt($boltName)->As;
        }
        $result = (($this->ec->getMaterial($boltMaterialName)->fu * $As * 0.6) / (1000 * $this->ec::GM2)) * $n;
        $this->ec->note('$F_(v,Rd)$ nyírás általános képlet: $n*(0.6*f_(u,b)*A_s)/(gamma_(M2))$');

        if (in_array($boltMaterialName, ['4.8', '5.8', '6.8', '10.9'])) {
            $this->ec->note('$F_(v,Rd)$ nyírás: ' . $boltMaterialName . ' csavar anyag miatt az eredmény 80%-ra csökkentve.');
            return $result * 0.8;
        }
        return $result;
    }

    // Csavar palástnyomás

    /**
     * @param float|string $boltMaterialName
     * @throws InvalidMaterialNameException
     * @todo test
     */
    public function moduleFbRd(string $boltName, $boltMaterialName, string $steelMaterialName, float $ep1, float $ep2, float $t, bool $inner): float
    {
        $boltMaterialName = (string) $boltMaterialName;

        $bolt = $this->ec->getBolt($boltName);
        $boltMaterial = $this->ec->getMaterial($boltMaterialName);
        $fust = $this->ec->fu($steelMaterialName, $t);
        $k1 = min(2.8 * ($ep2 / $bolt->d0) - 1.7, 2.5);
        $alphab = min(($ep1 / (3 * $bolt->d0)), $boltMaterial->fu / $fust, 1);
        if ($inner) {
            $k1 = min(1.4 * ($ep2 / $bolt->d0) - 1.7, 2.5);
            $alphab = min(($ep1 / (3 * $bolt->d0)) - 0.25, $boltMaterial->fu / $this->ec->fu($steelMaterialName, $t), 1);
        }
        $result = $k1 * (($alphab * $fust * $bolt->d * $t) / (1000 * $this->ec::GM2));
        $this->ec->set('__k1', $k1);
        $this->ec->set('__alphab', $alphab);
        if ($result <= 0) {
            return 0;
        }
        $this->ec->note('$F_(b,Rd)$ palástnyomás általános képlet: $k_1*(alpha_b*f_(u,s)*d*t)/(gamma_(M2))$');
        return $result;
    }

    /**
     * Module: Optimal e1, e2, p1, p2 and d sizes calculation for Shear
     */
    public function moduleOptimalForShear($boltMaterialName, string $steelMaterialName, float $tPlate, float $d0Bolt): void
    {
        $boltMaterialName = (string) $boltMaterialName;

        $deltaDb = [
            '400' => ['360' => 3.18],
            '500' => ['360' => 2.53, '430' => 3.04],
            '600' => ['360' => 2.12, '430' => 2.54, '510' => 3.01, '530' => 3.13],
            '800' => ['360' => 1.59, '430' => 1.90, '510' => 2.26, '530' => 2.34],
            '1000' => ['360' => 1.27, '430' => 1.52, '510' => 1.80, '530' => 1.82],
        ];
        $delta = $deltaDb[(string)$this->ec->getMaterial($boltMaterialName)->fu][$this->ec->getMaterial($steelMaterialName)->fu];
        $this->ec->info0('Javasolt átmérő és peremtávolságok:');
            $this->ec->def('delta', $delta, 'delta = %%', '');
            $this->ec->note('*[Acélszerkezetek - 1. Általános eljárások (2007) 6.3. táblázat]*');
            $this->ec->def('d_min', ceil($this->ec->delta * $tPlate),'d_(min) = delta*t = %% [mm]', 'Javasolt csavar átmérő');
            $this->ec->def('e1_opt', ceil(2*$d0Bolt),'e_(1,opt) = 2*d_0 = %% [mm]', 'Szélső peremtávolság (csavarképpel párhuzamos)');
            $this->ec->def('e2_opt', ceil(1.5*$d0Bolt),'e_(2,opt) = 1.5*d_0 = %% [mm]', 'Szélső peremtávolság (csavarképre merőleges)');
            $this->ec->def('p1_opt', ceil(3*$d0Bolt),'p_(1,opt) = 3*d_0 = %% [mm]', 'Belső csavartávolság (csavarképpel párhuzamos)');
            $this->ec->def('p2_opt', ceil(3*$d0Bolt),'p_(2,opt) = 3*d_0 = %% [mm]', 'Belső csavartávolság (csavarképre merőleges)');
        $this->ec->info1();
    }

    /**
     * Module: Shear force and bearing resistance per bolt for the ultimate limit state
     */
    public function moduleShearAndBearingPerBolt(float $e1, float $e2, float $p1, float $p2, string $boltName, $boltMaterialName, string $steelMaterialName, float $VEd, float $tPlate, float $numberOfShearPlanes = 1, bool $innerBolt = true): void
    {
        $boltMaterialName = (string) $boltMaterialName;

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
     */
    public function moduleShear(string $boltName, $boltMaterialName, float $VEd, float $numberOfShearPlanes = 1, float $betaLf = 1) {
        $boltMaterialName = (string) $boltMaterialName;

        $this->ec->boo('useA', ['', 'Teljes keresztmetszeti terület figyelembe vétele'], false, 'Menetes rész nem kerülhet nyírt zónába!');
        if ($this->ec->useA) {
            $this->ec->def('F_vRd', H3::n2($this->moduleFvRd($boltName, $boltMaterialName, $numberOfShearPlanes, $this->ec->getBolt($boltName)->A)), 'F_(v,Rd) = %% [kN]', 'Csavar nyírási ellenállása');
        } else {
            $this->ec->def('F_vRd', H3::n2($this->moduleFvRd($boltName, $boltMaterialName, $numberOfShearPlanes)), 'F_(v,Rd) = %% [kN]', 'Csavar nyírási ellenállása');
        }

        if ($betaLf < 1.0) {
            $this->ec->def('F_vRd', H3::n2($betaLf*$this->ec->F_vRd), 'F_(v,Rd) := beta_(lf)*F_(v,Rd) = %% [kN]', 'Hosszú kapcsolat vagy béléslemez figyelembe vétele');
        }

        $this->ec->label($VEd/$this->ec->F_vRd, 'Nyírási kihasználtság');
    }

    /**
     * Module: Bearing force per bolt for the ultimate limit state
     * @param float|string $boltMaterialName
     */
    public function moduleBearing(string $boltName, $boltMaterialName, float $VEd, string $steelMaterialName, float $ep1, float $ep2, float $tPlate, bool $innerBolt, float $betaLf = 1) {
        $boltMaterialName = (string) $boltMaterialName;

        $this->ec->def('F_bRd', H3::n2($this->moduleFbRd($boltName, $boltMaterialName, $steelMaterialName, $ep1, $ep2, $tPlate, $innerBolt)), 'F_(b,Rd) = %% [kN]', 'Csavar palástnyomási ellenállása');
        if ($betaLf < 1.0) {
            $this->ec->def('F_bRd', H3::n2($betaLf*$this->ec->F_bRd), 'F_(b,Rd) := beta_(lf)*F_(b,Rd) = %% [kN]', 'Hosszú kapcsolat vagy béléslemez figyelembevétele');
        }
        $this->ec->note('$k_1 = '.$this->ec->__k1.'; alpha_b = '.$this->ec->__alphab.'$');
        $this->ec->label($VEd/$this->ec->F_bRd, 'Palástnyomási kihasználtság');
    }

    /**
     * @param \Statika\Calculations\Ec $ec
     * @throws InvalidBoltNameException
     * @throws InvalidMaterialNameException
     * @throws InvalidSectionNameException
     */
    public function calc(EurocodeInterface $ec): void
    {
        $ec->region0('r1', 'Csavar adatbázis');

        $boltDb = $this->boltFactory::BOLTS;

        // Generate multidimensional array for tbl() scheme:
        $scheme = array_keys($boltDb['M12']);
        array_unshift($scheme, 'Csavar');
        $rows = [];
        foreach ($boltDb as $key => $value) {
            array_unshift($value, $key);
            $rows[] = $value;
        }
        $ec->tbl($scheme, $rows);
        $ec->region1();

        $ec->boo('group', ['', 'Csavarkép számítás'], false);
        $ec->nr = 1;
        $ec->nc = 1;
        if ($ec->group) {
            $this->ec->wrapNumerics('nr', 'nc', '$n_r × n_c$ Sorok- és oszlopok száma', 2, 2, '', '', '×');
            $ec->boo('groupLL', ['', 'Egyik szárukon kapcsolt szögacélok ellenőrzése húzásra'], false);
            if ($ec->groupLL) {
                $ec->nc = 1;
                $ec->info('$n_c := 1$, csak egy csavaroszloppal számol!');
            }
        }
        $ec->nb = $ec->nr * $ec->nc;

        $this->ec->boltListBlock('boltName');
        $ec->region0('r0', 'Csavar jellemzők');
            $bolt = $ec->getBolt($ec->boltName);
            $ec->def('d_0', $bolt->d0, 'd_0 = %% [mm]', 'Lyuk átmérő');
            $ec->d = $bolt->d;
            $ec->def('A', $bolt->A, 'A = %% [mm^2]', 'Csavar keresztmetszeti terület');
            $ec->def('A_s', $bolt->As, 'A_s = %% [mm^2]', 'Csavar húzási keresztmetszet');
        $ec->region1();

        $this->ec->boltMaterialListBlock('boltMaterialName');
        $boltMaterial = $ec->getMaterial((string)$ec->boltMaterialName);

        $this->ec->structuralSteelMaterialListBlock('steelMaterialName');
        $steelMaterial = $ec->getMaterial($ec->steelMaterialName);

        $ec->numeric('t', ['t', 'Kisebbik lemez vastagság'], 10, 'mm', '');
        $ec->numeric('n', ['n', 'Nyírási síkok száma'], 1, '', '');
        $ec->numeric('N', ['F_(t,Ed)', 'Húzóerő '.($ec->group?'csavarképre':'csavarra')], 20, 'kN', '');
        $ec->numeric('V', ['F_(v,Ed)', 'Nyíróerő '.($ec->group?'csavarképre':'csavarra')], 30, 'kN', '');

        $ec->Nb = $ec->N;
        $ec->Vb = $ec->V;
        if ($ec->group) {
            $ec->txt('Csavarok száma: ' . $ec->nb);
            $ec->def('Nb', \H3::n2($ec->N/$ec->nb), 'F_(t,Ed,b) = F_(t,Ed)/'.$ec->nb.' = %% [kN]', 'Egy csavarra jutó húzóerő');
            $ec->def('Vb', \H3::n2($ec->V/$ec->nb), 'F_(v,Ed,b) = F_(v,Ed)/'.$ec->nb.' = %% [kN]', 'Egy csavarra jutó nyíróerő');
        }

        $steelMaterial->fy = $this->ec->fy($ec->steelMaterialName, $ec->t);
        $ec->note('$f_y(t) = ' . $steelMaterial->fy . ' [N/(mm^2)]$ alkalmazott folyáshatár lemezvastagság alapján.');

        $ec->h1('Egy csavar nyírási- és palástnyomási ellenállása', '***A*** osztály: nem feszített, nyírt csavar');
        $this->moduleOptimalForShear((string)$ec->boltMaterialName, $ec->steelMaterialName, $ec->t, $ec->d_0);
        $ec->numeric('e1', ['e_1', 'Peremtávolság (csavarképpel párhuzamos)'], 50, 'mm', '');
        $ec->numeric('e2', ['e_2', 'Peremtávolság (csavarképre merőleges)'], 50, 'mm', '');

        $ec->inner = false;
        $ec->p1 = 0;
        $ec->p2 = 0;
        if ($ec->group && $ec->nr > 1) {
            $ec->inner = true;
            $ec->note('Belső csavar számítása a mértékadó, mert csavarkép eset van és $n_r > 1$. Figyelembe van véve $p_1, p_2$.');
            $ec->numeric('p1', ['p_1', 'Csavartávolság (csavarképpel párhuzamos)'], 50, 'mm', '');
            if ($ec->nc > 1 && !$ec->groupLL) {
                $ec->numeric('p2', ['p_2', 'Csavartávolság (csavarképre merőleges)'], 50, 'mm', '');
            }
        }
        $ec->note('Egy csavar esetén külsőként számolható. Több csavar esetén belső a mértékadó.');
        $this->moduleShearAndBearingPerBolt($ec->e1, $ec->e2, $ec->p1, $ec->p2, $ec->boltName, (string)$ec->boltMaterialName, $ec->steelMaterialName, $ec->Vb, $ec->t, $ec->n, $ec->inner);

        $ec->h1('Egy csavar húzási- és kigombolódási ellenállása', '***D*** nem feszített, húzott és ***E*** feszített, húzott csavarok');
        $ec->def('F_tRd', H3::n2($this->moduleFtRd($ec->boltName, $ec->boltMaterialName)), 'F_(t,Rd) = %% [kN]', 'Csavar húzási ellenállása');
        $ec->label($ec->Nb / $ec->F_tRd, 'Húzási kihasználtság');
        $ec->def('B_pRd', H3::n2($this->moduleBpRd($ec->boltName, $ec->steelMaterialName, $ec->t)), 'B_(p,Rd) = %% [kN]', 'Csavar kigombolódási ellenállása');
        $ec->label($ec->Nb / $ec->B_pRd, 'Kigombolódási kihasználtság');

        $ec->h1('Egy csavar húzás és nyírás interakciója', '***AD*** osztály');
        $ec->def('U_vt', H3::n1((($ec->Vb / $ec->F_vRd) + ($ec->Nb / (1.4 * $ec->F_tRd))) * 100), 'U_(vt) = F_(v,Ed)/F_(v,Rd) + F_(t,Ed)/(1.4*F_(t,Rd)) = %% [%]', 'Interakciós kihasználtság');
        $ec->label($ec->U_vt / 100, 'Interakciós kihasználtság');

        if ((string) $ec->boltMaterialName === '10.9') {
            $ec->h1('Egy feszített csavar nyírásra');
            $ec->numeric('n_s', ['n_s', 'Súrlódó felületek száma'], 1, '', '');
            $ec->numeric('mu', ['mu', 'Súrlódási tényező'], 0.2, '', '**Súrlódási tényezők:** **0.5** *Sörétezett vagy szemcsefújt festetlen felület*, **0.4** *Sörétezett vagy szemcsefújt festett felület*, **0.3** *Drótkefézett vagy lángszórással tisztított felület*, **0.2** *Kezeletlen felület*');
            $ec->numeric('F_Ed_ser', ['F_(Ed,ser)', 'Nyíróerő '.($ec->group?'csavarképre':'egy csavarra').' használhatósági határállapotban'], 10, 'kN', '');
            $ec->def('F_Ed_ser_b', $ec->F_Ed_ser/$ec->nb, 'F_(Ed,ser,b) = F_(Ed,ser)/'.$ec->nb.' = %% [kN]', 'Egy csvarra eső erő');

            $ec->md('***B*** osztályú nyírt csavarok használhatósági határállapotig működnek feszített csavarként.');
            $ec->md('Teherbírási határállapotban ***A*** csavarként. Használhatósági határállapotban:');

            $ec->def('F_pC', 0.7 * $ec->bfu * $ec->A_s / 1000, 'F_(p,C) = 0.7*f_(u,b)*A_s = %% [kN]', 'Előírt feszítőerő');
            $ec->def('F_s_Rd', (($ec->n_s * $ec->mu) / $ec->_GM3) * $ec->F_pC, 'F_(s,Rd) = (n_s*mu)/gamma_(M3)*F_(p,C) = %% [kN]', 'Megcsúszással szembeni ellenállás');
            $ec->U_s_ser = $ec->F_Ed_ser_b / $ec->F_s_Rd;
            $ec->label($ec->U_s_ser, '*B* Kihasználtság használhatósági határállapotban');

            $ec->md('***C*** osztályú nyírt csavar:');
            $ec->U_s = $ec->Vb / $ec->F_s_Rd;
            $ec->math('F_(v,Ed,b)/F_(s,Rd) = ' . H3::n3($ec->U_s));
            $ec->label(H3::n3($ec->U_s), '*C* Megcsúszási kihasználtság teherbírási határállpotban');
            $ec->math('F_(v,Ed,b)/F_(b,Rd) = ' . H3::n3($ec->Vb / $ec->F_bRd));
            $ec->label($ec->Vb / $ec->F_bRd, '*C* Palástnyomási kihasználtság');

            $ec->md('***CE*** osztályú húzott-nyírt csavar:');
            $ec->def('F_s_tv_Rd', (($ec->n_s * $ec->mu) / $ec->_GM3) * ($ec->F_pC - 0.8 * $ec->N), 'F_(s,tv,Rd) = (n_s*mu)/gamma_(M3)*(F_(p,C)-0.8*F_(t,Ed)) = %% [kN]', 'Interakciós ellenállás');
            $ec->U_s_tv = $ec->Vb / $ec->F_s_tv_Rd;
            $ec->math('F_(v,Ed,b)/F_(s,tv,Rd) = ' . H3::n3($ec->U_s_tv));
            $ec->label($ec->U_s_tv, 'Interakciós kihasználtság');
            $ec->note('*B* osztályú csavar esetén *BE* interakció ugyanez, $F_(Ed,ser,b)$ alkalmazásával.');
        }

        if ($ec->group) {
            $ec->h1('Csavarkép');

            // SVG init
            $xp = 2*$ec->e2 + ($ec->nc - 1)*$ec->p2; // Plate dimensions
            $yp = 2*$ec->e1 + ($ec->nr - 1)*$ec->p1; // Plate dimensions
            $svg = new SVG(450, 450);
            // Plate
            $svg->makeRatio(350, 350, $xp, $yp);
            $svg->setColor('blue');
            $svg->addRectangle(0, 0, $xp, $yp, 25, 25);
            // Bolts
            for ($row = 0; $row <= $ec->nr - 1; $row++) {
                for ($col = 0; $col <= $ec->nc - 1; $col++) {
                    $xi = ($ec->e2 + $col*$ec->p2);
                    $yi = ($ec->e1 + $row*$ec->p1);
                    $svg->addCircle($xi, $yi, ($ec->d_0/2), 25, 25);
                    $svg->makeRatio(350, 350, $xp, $yp);
                }
            }
            // Dimensions
            $svg->setColor('magenta');
            $svg->addDimH(0, $xp, 430, $xp, 25); // Plate horizontal
            $svg->addDimV(0, $yp, 430, $yp, 25); // Plate vertical
            $svg->addDimH(0, $ec->e2, 400, $ec->e2, 25); // e2
            ($ec->nc > 1)?$svg->addDimH($ec->e2, $ec->p2, 400, H3::n0($ec->p2), 25):false; // p2
            $svg->addDimV(0, $ec->e1, 400, $ec->e1, 25); // e1
            ($ec->nr > 1)?$svg->addDimV($ec->e1, $ec->p1, 400, H3::n0($ec->p1), 25):false; // p1
            // Texts & symbols
            $svg->setColor('black');
            $svg->addSymbol(200, 5, 'arrow-up');
            $ec->svg($svg);

            $ec->h2('Csavarkép nyírási teherbírása');
            $FRd = min($this->moduleFbRd($ec->boltName, $ec->boltMaterialName, $ec->steelMaterialName, $ec->e1, $ec->e2, $ec->t, false), $this->moduleFbRd($ec->boltName, $ec->boltMaterialName, $ec->steelMaterialName, ($ec->p1 != 0)?$ec->p1:$ec->e1, ($ec->p2 != 0)?$ec->p2:$ec->e2, $ec->t, true), $this->moduleFvRd($ec->boltName, $ec->boltMaterialName, $ec->n, 0)) * $ec->nb;
            if ($this->moduleFvRd($ec->boltName, $ec->boltMaterialName, $ec->n, 0) >= min($this->moduleFbRd($ec->boltName, $ec->boltMaterialName, $ec->steelMaterialName, $ec->e1, $ec->e2, $ec->t, false), $this->moduleFbRd($ec->boltName, $ec->boltMaterialName, $ec->steelMaterialName, ($ec->p1 != 0)?$ec->p1:$ec->e1, ($ec->p2 != 0)?$ec->p2:$ec->e2, $ec->t, true))) {
                $ec->txt('Minden csavar nyírási ellenállása nagyobb bármely másik csavar palástnyomási ellenállásnál, ezért a csavarkép ellenállása lehetne a csavarok palástnyomási ellenállásának összege.');
            }
            $ec->math('F_(v,Ed,b) = F_(v,Ed)/'.$ec->nb.' = '.$ec->Vb.' [kN]', 'Egy csavarra jutó nyíróerő centrikus kapcsolat esetén');
            $ec->success0('Csavarkép teherbírása:');
                if (($ec->nr - 1) * $ec->p1 > 15 * $ec->d) {
                    $ec->def('Lj', max(0.75, 1 - (($ec->nr - 1) * $ec->p1 - 15 * $ec->d) / (200 * $ec->d)), 'L_j = %%', 'Hosszú kapcsolat csavaronként értelmezett csökkentő tényezője');
                    $ec->def('FRd', $FRd, 'F_(Rd) = %% [kN]', 'Szélső és belső csavarok nyírási vagy palástnyomási ellenállásának minimuma csavarszámmal felszorozva');
                    $ec->def('FRd', $FRd * $ec->Lj, 'F_(Rd, red) = F_(Rd)*L_j = %% [kN]', 'Szélső és belső csavarok nyírási vagy palástnyomási ellenállásának minimuma csavarszámmal felszorozva');
                } else {
                    $ec->def('FRd', $FRd, 'F_(Rd) = %% [kN]', 'Szélső és belső csavarok nyírási vagy palástnyomási ellenállásának minimuma csavarszámmal felszorozva');
                }
            $ec->success1();
            $ec->label($ec->Vb / $ec->FRd, 'legjobban igénybevett csavar nyírási kihasználtsága');

            $ec->h2('Lemez nettó keresztmetszet vizsgálata');
            $ec->def('l_net', $ec->e2 * 2 + ($ec->nc - 1) * $ec->p2 - $ec->nc * $ec->d_0, 'l_(n\et) = 2*e_2 + (n_c-1)*p_2 - n_c*d_0 = %% [mm]', 'Lemez hossz lyukgyengítéssel');
            $ec->def('A', ($ec->e2 * 2 + ($ec->nc - 1) * $ec->p2) * $ec->t, 'A = (2*e_2 + (n_c-1)*p_2)*t = %% [mm^2]', 'Vizsgált keresztmetszeti terület');
            $ec->def('A_net', $ec->l_net * $ec->t, 'A_(n et) = l_(n et)*t = %% [mm^2]', 'Vizsgált nettó keresztmetszeti terület');
            $ec->def('NplRd', H3::n2($this->steelSection->moduleNplRd($ec->A, $ec->steelMaterialName, $ec->t)), 'N_(pl,Rd) = (A*f_y)/gamma_(M,0) = %% [kN]', 'Teljes km. folyási ellenállása');
            $ec->def('NuRd', H3::n2($this->steelSection->moduleNuRd($ec->A_net, $ec->steelMaterialName, $ec->t)), 'N_(u,Rd) = (0.9*A_(n\et)*f_u)/gamma_(M,2) = %% [kN]', 'Nettó km. képlékeny töréssel szembeni ellenállása');
            $ec->def('NtRd', H3::n2(min($ec->NplRd, $ec->NuRd)), 'N_(t,Rd) = min{(N_(pl,Rd)), (N_(u,Rd)):} = %% [kN]', 'Húzási ellenállás');
            $ec->math('F_(v,Ed)/N_(t,Rd) = ('.$ec->V.'[kN])/('.$ec->NtRd.'[kN])');
            $ec->label($ec->V / $ec->NtRd, 'Keresztmetszet kihasználtsága húzásra');

            $ec->h2('Csoportos kiszakadás');
            $ec->boo('exc', ['', 'Excentrikus csavarkép'], false, '');
            $exc = 1;
            if ($ec->exc) {
                $exc = 0.5;
            }
            $ec->math('exc = '.$exc);
            $ec->def('A_nt', ($ec->nc - 1) * $ec->p2 * $ec->t, 'A_(nt) = (n_c - 1)*p_2*t = %% [mm^2]');
            $ec->def('A_nv', ($ec->e1 + ($ec->nr - 1) * $ec->p1) * $ec->t, 'A_(nv) = (e_1 + (n_r - 1)*p_1)*t = %% [mm^2]');
            $ec->def('Veff1Rd', $exc * (($steelMaterial->fu * $ec->A_nt) / ($ec::GM2 * 1000)) + ($steelMaterial->fy * $ec->A_nv) / (sqrt(3) * $ec::GM0 * 1000), 'V_(eff,1,Rd) = exc*(f_u*A_(nt))/gamma_(M2) + (f_y*A_(nv))/(gamma_(M0)*sqrt(3)) = %% [kN]');
            $ec->math('F_(v,Ed)/V_(eff,1,Rd) = ('.$ec->V.'[kN])/('.$ec->Veff1Rd.'[kN])');
            $ec->label($ec->V / $ec->Veff1Rd, 'csoportos kiszakadás kihasználtsága');
            $ec->note('Féloldalas kiszakadást nem vizsgál!');

            if ($ec->groupLL) {
                $ec->h1('Egyik szárukon kapcsolt szögacélok húzásra');
                $ec->note('Külpontosság elhanyagolható.');
                $ec->math('n_r = ' . $ec->nr . ' × ' . $ec->boltName . '%%%(n_c = 1)', 'Függőleges csavarkép átvétele');
                $this->ec->sectionFamilyListBlock('sectionFamily', ['', 'Szelvény család'], 'L');
                $this->ec->sectionListBlock($ec->sectionFamily);
                $section = $ec->getSection($ec->sectionName);
                $ec->math('d_0 = ' . $ec->d_0 . '[mm]%%%t_(w,L) = ' . $section->_tw * 10 . '[mm]%%%f_(u,s) = ' . $steelMaterial->fu . '[N/(mm^2)] %%% A_(n\et) = ' . $ec->A_net . ' [mm^2]');
                $ec->def('AnetL', $section->_Ax * 100 - $ec->d_0 * $section->_tw * 10, 'A_(n et,L) = A_(x,L) - 1*d_0*t_(w,L) = %% [mm^2]');
                $ec->note('Egy oszlop csavar csak!');

                $ec->def('e1', $ec->e1_opt, 'e_(1,min) := e_(1,opt) = ' . $ec->e1_opt . ' [mm]', 'Peremtávolság erő irányban, fenti számítások alapján');
                $ec->def('e2', (min($section->_b, $section->_h) * 10) / 2, 'e_2 = (min{(h_L),(b_L):})/2 = %% [mm]', 'Peremtávolság erő irányra merőlegesen');

                if ($ec->nr == 1) {
                    $ec->txt('Erőátadás irányában egy csavar esete:');
                    $NuRd = (2 * ($ec->e2 - 0.5 * $ec->d_0) * $section->_tw * 10 * $steelMaterial->fu) / ($ec::GM2 * 1000);
                    $ec->success0('Rúd keresztmetszeti ellenállása húzásra:');
                    $ec->def('NuRd', $NuRd, 'N_(u,Rd) = (2*(e_2-0.5*d_0)*t_(w,L)*f_(u,s))/gamma_(M2) = %% [kN]');
                    $ec->success1();
                } else if ($ec->nr == 2) {
                    $ec->txt('Erőátadás irányában két csavar esete:');
                    if ($ec->p1 <= 2.5 * $ec->d_0) {
                        $ec->def('beta_2', 0.4, 'beta_2 = %%', 'p_1 < 2.5*d_0');
                    } else if ($ec->p1 >= 5 * $ec->d_0) {
                        $ec->def('beta_2', 0.7, 'beta_2 = %%', 'p_1 > 5*d_0');
                    } else {
                        $ec->def('beta_2', $this->ec->linterp(2.5 * $ec->d_0, 0.4, 5 * $ec->d_0, 0.7, $ec->p1), 'beta_2 = %%', 'Lineárisan interpolált érték!');
                    }
                    $NuRd = ($ec->beta_2 * $ec->AnetL * $steelMaterial->fu) / ($ec::GM2 * 1000);
                    $ec->success0('Rúd keresztmetszeti ellenállása húzásra:');
                    $ec->def('NuRd', $NuRd, 'N_(u,Rd) = (beta_2*A_(n et,L)*f_(u,s))/gamma_(M2) = %% [kN]');
                    $ec->success1();
                } else {
                    if ($ec->p1 <= 2.5 * $ec->d_0) {
                        $ec->def('beta_3', 0.5, 'beta_2 = %%', 'p_1 < 2.5*d_0');
                    } else if ($ec->p1 >= 5 * $ec->d_0) {
                        $ec->def('beta_3', 0.7, 'beta_2 = %%', 'p_1 > 5*d_0');
                    } else {
                        $ec->def('beta_3', $this->ec->linterp(2.5 * $ec->d_0, 0.5, 5 * $ec->d_0, 0.7, $ec->p1), 'beta_3 = %%', 'Lineárisan interpolált érték!');
                    }
                    $NuRd = ($ec->beta_3 * $ec->AnetL * $steelMaterial->fu) / ($ec::GM2 * 1000);
                    $ec->success0('Rúd keresztmetszeti ellenállása húzásra:');
                    $ec->def('NuRd', $NuRd, 'N_(u,Rd) = (beta_3*A_(n et,L)*f_(u,s))/gamma_(M2) = %% [kN]');
                    $ec->success1();
                }
                $ec->label($ec->V / $ec->NuRd, 'Húzási kihasználtság');
                $ec->note('`beta_2` és `beta_3` külpontosság miatti tényezők. Táblázatos érték [Acélszuerkezetek általános eljárások (2007) 5.1/5.2 táblázat 40.o.]');
            }
        }
    }
}
