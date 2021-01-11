<?php declare(strict_types = 1);
/**
 * Mechanical analysis of steel sections according to Eurocodes - Calculation class for Statika framework
 * (c) Bence VÁNKOS | https://structure.hu | https://github.com/r3sist/ecc-calculations
 */

namespace Statika\Calculations;

use \H3;
use Statika\BlocksInterface;
use Statika\EurocodeInterface;

Class SteelSection
{
    private EurocodeInterface $ec;
    private BlocksInterface $blc;

    public function __construct(EurocodeInterface $ec, BlocksInterface $blc)
    {
        $this->ec = $ec;
        $this->blc = $blc;
    }

    /**
     * Acél keresztmetszet nyírási ellenállása
     * @param float $Av [mm2]
     * @param string $matName
     * @param float $t [mm]
     * @return float [kN]
     * @todo test
     */
    public function moduleVplRd(float $Av, string $matName, float $t): float
    {
        $fy = $this->ec->fy($matName, $t);
        return ($Av * $fy) / (sqrt(3) * $this->ec::GM0 * 1000);
    }

    /**
     * @todo test
     */
    public function moduleMcRd(float $W, float $fy): float
    {
        return ($W * $fy) / ($this->ec::GM0 * 1000000);
    }

    /**
     * @todo test
     */
    public function moduleNplRd($A, string $matName, $t): float
    {
        $A = (float)$A;
        $t = (float)$t;
        $fy = $this->ec->fy($matName, $t);
        return ($A * $fy) / ($this->ec::GM0 * 1000);
    }

    /**
     * Acél keresztmetszet húzási ellenállása: A Anet [mm2], t [mm], returns [kN]
     * @todo test
     */
    public function moduleNtRd($A, $Anet, string $matName, $t): float
    {
        $A = (float)$A;
        $Anet = (float)$Anet;
        $t = (float)$t;
        return min($this->moduleNuRd($Anet, $matName, $t), $this->moduleNplRd($A, $matName, $t));
    }

    /**
     * @todo test
     */
    public function moduleNuRd($Anet, string $matName, $t): float
    {
        $Anet = (float)$Anet;
        $t = (float)$t;
        $fu = $this->ec->fu($matName, $t);
        return (0.9 * $Anet * $fu) / ($this->ec::GM2 * 1000);
    }

    /**
     * @todo test
     */
    public function moduleNcRd(float $A, float $fy): float
    {
        return ($A * $fy) / ($this->ec::GM0 * 1000);
    }

    /**
     * @param \Statika\Ec $s
     * @throws \Profil\Exceptions\InvalidSectionNameException
     */
    public function calc(EurocodeInterface $s): void
    {
        $s->info0();
            $s->numeric('VEd', ['V_(Ed)', 'Nyíróerő'], 100, 'kN');
            $s->numeric('MEd', ['M_(Ed)', 'Hajlítónyomaték'], 100, 'kNm');
            $s->numeric('NEd', ['N_(Ed)', 'Húzóerő'], 200, 'kN');
        $s->info1();

        $s->sectionFamilyListBlock();
        $s->sectionListBlock($s->sectionFamily);
        $section = $s->getSection($s->sectionName);

        $s->structuralSteelMaterialListBlock();
        $material = $s->getMaterial($s->mat);
        $s->numeric('t', ['t', 'Lemezvastagság'], 10, 'mm');
        $s->txt('*'.$s->sectionName.'* legnagyobb lemezvastagsága: $t_(max) = '. 10*max($section->_tf, $section->_tw).' [mm]$');

        $s->fy = $material->fy;
        $s->fu = $material->fu;
        if ($s->t >= 40) {
            $s->fy = $material->fy40;
            $s->fu = $material->fu40;
            $s->txt('$f_y$ és $f_u$ lemezvastagság miatt csökkentve.');
        }

        $s->region0('rEgyedi', 'Egyedi keresztmetszeti paraméterek megadása');
            $s->txt('Az itt megadott *nemnulla* érték felülírja a betöltött szelvény adatokat.');
            $s->numeric('A_v', ['A_v', 'Egyedi nyírási keresztmetszet'], 0, 'mm²', '');
            $s->numeric('A', ['A', 'Egyedi húzási keresztmetszet'], 0, 'mm²', '');
            $s->numeric('Wpl', ['W_(pl)', 'Egyedi képlékeny keresztmetszeti modulus'], 0, 'mm³', '');
            $s->numeric('Wel', ['W_(el)', 'Egyedi rugalmas keresztmetszeti modulus'], 0, 'mm³', '');
        $s->region1();

        if ($s->A_v == 0) {
            $s->def('A_v', $section->_Az*100, 'A_v = A_(z, '.$s->sectionName.') = %% [mm^2]', 'Nyírási keresztmetszet');
        }

        if ($s->A == 0) {
            $s->def('A', $section->_Ax*100, 'A = A_(x, '.$s->sectionName.') = %% [mm^2]', 'Húzási keresztmetszet');
        }

        if ($s->Wpl == 0) {
            $s->def('Wpl', $section->_W1pl*1000, 'W_(pl) = W_(1, pl, '.$s->sectionName.') = %% [mm^3]', 'Képlékeny keresztmetszeti modulus');
        }

        if ($s->Wel == 0) {
            $s->def('Wel', $section->_W1elt*1000, 'W_(el) = W_(1, el, t, '.$s->sectionName.') = %% [mm^3]', 'Rugalmas keresztmetszeti modulus');
        }

        $s->boo('calcAnet', ['', 'Eltérő nettó keresztmetszet'], false);
        if ($s->calcAnet) {
            $s->numeric('n', ['n', 'Csavarok száma'], 0, '', 'Nettó keresztmetszet számítás csavarszámból. Nemnulla esetén $A_(n et)$ számítása innen.');
            $s->boltListBlock('btName');
            $d0 = $s->getBolt($s->btName)->d0;
            if ($s->n != 0) {
                $s->def('A_net', $s->A - $s->n*$d0*$s->t, 'A_(n et) = A - n*d_0*t = %% [mm^2]', 'Számított nettó keresztmetszet');
            } else {
                $s->numeric('A_net', ['A_(n et)', 'Lyukkal gyengített keresztmetszet'], 0.8*$s->A, 'mm2', 'Alapérték: $0.8*A$');
            }
        } else {
            $s->def('A_net', $s->A, 'A_(n et) = A = %% [mm^2]', 'Figyelembe vett nettó keresztmetszet');
        }

        $s->h1('Nyírt keresztmetszet');
        $s->note('[Szürke 2007: 5.1.5 41.o]');
        $s->success0();
            $s->def('VplRd', H3::n2($this->moduleVplRd((float)$s->A_v, $s->mat, (float)$s->t)), 'V_(c,Rd) = V_(pl,Rd) = (A_v*f_y)/(sqrt(3)*gamma_(M0)) = %% [kN]', 'Nyírási ellenállás 1 és 2. kmo. esetén');
        $s->success1();
        $s->note('3 és 4. kmo. esetén $V_(c,Rd) = (A_w*f_y)/(sqrt(3)*gamma_(M0))$ *H* és *I* szelvénynél. Általános esetben $(I*t)/S*f_y/(sqrt(3)*gamma_(M0))$');
                $s->label($s->VEd/$s->VplRd, 'Nyírási kihasználtság', '$V_(Ed)/V_(pl,Rd)$');

        $s->h1('Hajlított keresztmetszet');
        $s->note('[Szürke 2007: 5.1.4 41.o]');
        $s->success0();
            $s->def('McRdpl', H3::n2($this->moduleMcRd($s->Wpl, $s->fy)), 'M_(c,Rd,pl) = (W_(pl)*f_y)/gamma_(M0) = %% [kNm]', '1 és 2. kmo. esetén');
        $s->success1();
        $s->def('McRdel', H3::n2($this->moduleMcRd($s->Wel, $s->fy)), 'M_(c,Rd,el) = (W_(el)*f_y)/gamma_(M0) = %% [kNm]', '3 . kmo. esetén');
        $s->note('4. kmo. esetén $W_(eff)$ hatékony keresztmetszet rugalmas keresztmetszeti modulussal kell számolni.');
        $s->note('Nyomott zónában a nem oválfuratos lyukgyengítést nem kell figyelembe venni. Húzott öv lyukgyengítésére vizsgálat szükséges:');
        $s->txt('Húzott öv lyukgyengítése elhagyható, ha az alábbi feltétel teljesül:', '$A_(f,n et)$ az öv nettó keresztmetszeti területe, $A_f$ az öv bruttó keresztmetszeti területe.');
        $s->math('(A_(f,n et)*0.9*f_u)/gamma_(M2) >= (A_f*f_y)/gamma_(M0)', '');
        $s->label($s->MEd/$s->McRdpl, 'Képlékeny hajlítási kihasználtság');
        $s->txt('', '$M_(Ed)/M_(c,Rd,pl)$');
        $s->note('3 kmo. esetén:');
        $s->label($s->MEd/$s->McRdel, 'Rugalmas hajlítási kihasználtság');
        $s->txt('', '$M_(Ed)/M_(c,Rd,el)$');

        $s->h1('Hajlítás és nyírás kölcsönhatása');
        if ($s->VEd >= 0.5*$s->VplRd) {
            $s->math('V_(Ed) = '.$s->VEd.'[kN] %%% >= %%% 0.5*V_(c,Rd) = '. H3::n2(0.5*$s->VplRd).' [kN]');
            $s->txt('Ezért a kölcsönhatás figyelembe veendő!');
            $s->def('rho', H3::n4(pow(((2*$s->VEd)/$s->VplRd - 1), 2)), 'rho = ((2*V_(Ed))/V_(pl,Rd) - 1)^2 = %%');
            $s->note('Nyomatéki teherbírás számításakor a folyáshatárt a nyírt területen $(1 - rho)$ csökkentő tényezővel kell figyelembe venni.');
            $s->txt('1 és 2. kmo. nagy tengely körül hajlított I szelvények esetében a nyomatéki teherbírás:');
            $s->math('A_w = A_v = '.$s->A_v.' [mm^2] %%% t_w = t = '.$s->t.' [mm]', 'Gerinc keresztmetszeti terület és gerincvastagság értékadása.');
            $s->def('MyVRd', H3::n2(($s->Wpl - ($s->rho*pow($s->A_v, 2))/(4*$s->t))*($s->fy/($s::GM0*1000000))), 'M_(y,V,Rd) = (W_(pl,y) - (rho*A_w^2)/(4*t_w))*f_y/gamma_(M0) = %% [kNm]', '');
            $s->txt('', 'De: $M_(y,V,Rd) < M_(c,Rd)$');
            $s->note('Rugalmas számítás során a 3. és 4. kmo. szelvényekre a kölcsönhatást a feszültség alapú általános formulával kell meghatározni.');
        } else {
            $s->math('V_(Ed) = '.$s->VEd.'[kN] %%% le %%% 0.5*V_(c,Rd) = '. H3::n2(0.5*$s->VplRd).' [kN]');
            $s->label('yes', 'Kölcsönhatás vizsgálata nem szükséges.');
        }

        $s->h1('Központosan húzott keresztmetszet');
        $s->note('[Szürke 2007: 5.1.2 40.o]');
        $s->note('Keresztmetszeti besorolásra nincs szükség.');
        $s->def('NplRd', $this->moduleNplRd($s->A, $s->mat, $s->t), 'N_(pl,Rd) = (A*f_y)/gamma_(M,0) = %% [kN]', 'Teljes km. folyási ellenállása');
        $s->def('NuRd', $this->moduleNuRd($s->A_net, $s->mat, $s->t), 'N_(u,Rd) = (0.9*A_(n\et)*f_u)/gamma_(M,2) = %% [kN]', 'Nettó km. képlékeny töréssel szembeni ellenállása');
        $s->success0();
            $s->def('NtRd', $this->moduleNtRd($s->A, $s->A_net, $s->mat, $s->t), 'N_(t,Rd) = min(N_(pl,Rd), N_(u,Rd)) = %% [kN]', 'Húzási ellenállás');
        $s->success1();
        $s->label($s->NEd/$s->NtRd, 'Húzási kihasználtság');
        $s->txt('', '$N_(Ed)/N_(t,Rd)$');


        // KÖZPONTOS NYOMÁS ////////////////////////////////////////////////////////////////////////////////////////////

        $s->h1('Központosan nyomott rudak rugalmas ellenállása');
        $s->note('1, 2, 3. kmo. szelvényekre. $A = A_(eff)$ 4. kmo.-ra.');
        $s->success0();
            $s->def('NcRd', $this->moduleNcRd($s->A, $s->fy), 'N_(c,Rd) = %% [kN]');
        $s->success1();
        $s->math('N_(Ed) = '.$s->NEd.' [kN]', 'Húzó-nyomó erő');
        $s->label($s->NEd/$s->NcRd, 'Nyomási kihasználtság');
        $s->note('Oválfuratok esetén a furatok nem kitöltöttek, ezért nettó keresztmetszettel kell számolni.');


        // KIHAJLÁSI ELLENÁLLÁS ////////////////////////////////////////////////////////////////////////////////////////

//        $s->h1('Központosan nyomott rudak kihajlási ellenállása');



        $s->h1('Hajlítás és normálerő kölcsönhatása');
        $s->note('Ha hajlítónyomatékkal egyidejűleg nomiálerő is hat, akkor kölcsönhatásukra tekintettel kell lemii. Ez képlékeny vizsgálat
esetén a nyomatéki ellenállás csökkentésével, rugalmas vizsgálat esetén a két hatásból származó feszültségek összegzésével
történhet.');
        $s->note('A keresztmetszet besorolásához az EC3 nem ad egyértelmű előírásokat, Az első lehetőség  hogy normáleröre történő
vizsgálatnál a normálerőből számított besorolást használjuk, míg hajlítás esetén hajlitásra soroljuk be a szelvényt. A második
módszer szerint a keresztmetüeti osztályba soroláshoz mindkét igénybevételt együttesen kell figyelembe venni, és az
együttes hatásra kapott keresztmetszeti osztályt kell mindkét vizsgálathoz használni.');
        $s->note('Az első módszer egyaerübb, viszont elvi ellentmondásra vezet, ha eredményeképpen hajlításnál képlékeny eljárást
alkalmazunk, de tiszta nyomásra 4. osztályává válik szelvényünk. A második módszer konzekvensebb, de elvégzése
általában bonyolultabb, különösen a haj ütött-nyomott gerinclemez besorolásakor.');
        $s->note('l. és 2. osztályú szelvényeknél általában az első módszer használható. Az előbb bemutatott speciális esetben a második
módszer segíthet, mert a gerinc besorolása összetett igénybevételre kedvezőbb, mint a tiszta nyomásnál. 3. keresztmetszeti
osztályú szelvény rugalmas ellenőrzése során a feszültségeket egyébként is kiszámoljuk, tehát a második módszer használata
sem okoz külön nehézséget, ezt javasoljuk. A 4. osztályú szelvények vizsgálatához való (5.34) összefüggésben pedig az EC3
előírja az első módszer használatát.');
        $s->txt('Az alábbi feltételek teljesülése esetén 1 és 2. kmo. övlemezes szelvények esetén a normálerő nem csökkenti az *y* tengely körüli nyomatéki ellenállást:');
        $s->math('N_(Ed) = '.$s->NEd.'[kN] %%% < %%% 0.25*N_(pl,Rd) = '. 0.25*$s->NplRd.' [kN]');
        $s->math('N_(Ed) = '.$s->NEd.'[kN] %%% < %%% (0.5*h_w*t_w*f_y)/gamma_(M0) = ');
        $s->md('`TODO h_w miatt nem ellenőrizhető`');
    }
}
