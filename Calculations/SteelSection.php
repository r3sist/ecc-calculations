<?php declare(strict_types = 1);
// Mechanical analysis of steel sections according to Eurocodes - Calculation class for ECC framework
// (c) Bence VÁNKOS | https://structure.hu | https://github.com/r3sist/ecc-calculations

namespace Calculation;

use \Base;
use \Ecc\Blc;
use \Ec\Ec;
use \H3;

Class SteelSection
{
    public function calc(Base $f3, Blc $blc, Ec $ec): void
    {
        $blc->toc();

        $blc->info0();
        $blc->numeric('VEd', ['V_(Ed)', 'Nyíróerő'], 100, 'kN');
        $blc->numeric('MEd', ['M_(Ed)', 'Hajlítónyomaték'], 100, 'kNm');
        $blc->numeric('NEd', ['N_(Ed)', 'Húzóerő'], 200, 'kN');
        $blc->info1();

        $ec->sectionFamilyList();
        $ec->sectionList($f3->_sectionFamily);
        $ec->spreadSectionData($f3->_sectionName, true);

        $ec->structuralSteelMaterialListBlock('mat', 'S235');
        $ec->spreadMaterialData($f3->_mat, '');
        $blc->numeric('t', ['t', 'Lemezvastagság'], 10, 'mm');
        $blc->txt('*'.$f3->_sectionName.'* legnagyobb lemezvastagsága: $t_(max) = '. 10*max($f3->_sectionData['tf'], $f3->_sectionData['tw']).' [mm]$');

        if ($f3->_t >= 40) {
            $f3->_fy = $f3->_fy40;
            $f3->_fu = $f3->_fu40;
            $blc->txt('$f_y$ és $f_u$ lemezvastagság miatt csökkentve.');
        }

        $blc->region0('rEgyedi', 'Egyedi keresztmetszeti paraméterek megadása');
            $blc->txt('Az itt megadott *nemnulla* érték felülírja a betöltött szelvény adatokat.');
            $blc->numeric('A_v', ['A_v', 'Egyedi nyírási keresztmetszet'], 0, 'mm²', '');
            $blc->numeric('A', ['A', 'Egyedi húzási keresztmetszet'], 0, 'mm²', '');
            $blc->numeric('Wpl', ['W_(pl)', 'Egyedi képlékeny keresztmetszeti modulus'], 0, 'mm³', '');
            $blc->numeric('Wel', ['W_(el)', 'Egyedi rugalmas keresztmetszeti modulus'], 0, 'mm³', '');
        $blc->region1();

        if ($f3->_A_v == 0) {
            $blc->def('A_v', $f3->_sectionData['Az']*100, 'A_v = A_(z, '.$f3->_sectionName.') = %% [mm^2]', 'Nyírási keresztmetszet');
        }

        if ($f3->_A == 0) {
            $blc->def('A', $f3->_sectionData['Ax']*100, 'A = A_(x, '.$f3->_sectionName.') = %% [mm^2]', 'Húzási keresztmetszet');
        }

        if ($f3->_Wpl == 0) {
            $blc->def('Wpl', $f3->_sectionData['W1pl']*1000, 'W_(pl) = W_(1, pl, '.$f3->_sectionName.') = %% [mm^3]', 'Képlékeny keresztmetszeti modulus');
        }

        if ($f3->_Wel == 0) {
            $blc->def('Wel', $f3->_sectionData['W1elt']*1000, 'W_(el) = W_(1, el, t, '.$f3->_sectionName.') = %% [mm^3]', 'Rugalmas keresztmetszeti modulus');
        }

        $blc->boo('calcAnet', ['', 'Eltérő nettó keresztmetszet'], false);
        if ($f3->_calcAnet) {
            $blc->numeric('n', ['n', 'Csavarok száma'], 0, '', 'Nettó keresztmetszet számítás csavarszámból. Nemnulla esetén $A_(n et)$ számítása innen.');
            $ec->boltListBlock('btName');
            $d0 = $ec->boltProp($f3->_btName, 'd0');
            if ($f3->_n != 0) {
                $blc->def('A_net', $f3->_A - $f3->_n*$d0*$f3->_t, 'A_(n et) = A - n*d_0*t = %% [mm^2]', 'Számított nettó keresztmetszet');
            } else {
                $blc->numeric('A_net', ['A_(n et)', 'Lyukkal gyengített keresztmetszet'], 0.8*$f3->_A, 'mm2', 'Alapérték: $0.8*A$');
            }
        } else {
            $blc->def('A_net', $f3->_A, 'A_(n et) = A = %% [mm^2]', 'Figyelembe vett nettó keresztmetszet');
        }

        $blc->h1('Nyírt keresztmetszet');
        $blc->note('[Szürke 2007: 5.1.5 41.o]');
        $blc->success0();
            $blc->def('VplRd', H3::n2($ec->VplRd((float)$f3->_A_v, $f3->_mat, (float)$f3->_t)), 'V_(c,Rd) = V_(pl,Rd) = (A_v*f_y)/(sqrt(3)*gamma_(M0)) = %% [kN]', 'Nyírási ellenállás 1 és 2. kmo. esetén');
        $blc->success1();
        $blc->note('3 és 4. kmo. esetén $V_(c,Rd) = (A_w*f_y)/(sqrt(3)*gamma_(M0))$ *H* és *I* szelvénynél. Általános esetben $(I*t)/S*f_y/(sqrt(3)*gamma_(M0))$');
        $blc->label($f3->_VEd/$f3->_VplRd, 'Nyírási kihasználtság');
        $blc->txt('', '$V_(Ed)/V_(pl,Rd)$');

        $blc->h1('Hajlított keresztmetszet');
        $blc->note('[Szürke 2007: 5.1.4 41.o]');
        $blc->success0();
            $blc->def('McRdpl', H3::n2($ec->McRd($f3->_Wpl, $f3->_fy)), 'M_(c,Rd,pl) = (W_(pl)*f_y)/gamma_(M0) = %% [kNm]', '1 és 2. kmo. esetén');
        $blc->success1();
        $blc->def('McRdel', H3::n2($ec->McRd($f3->_Wel, $f3->_fy)), 'M_(c,Rd,el) = (W_(el)*f_y)/gamma_(M0) = %% [kNm]', '3 . kmo. esetén');
        $blc->note('4. kmo. esetén $W_(eff)$ hatékony keresztmetszet rugalmas keresztmetszeti modulussal kell számolni.');
        $blc->note('Nyomott zónában a nem oválfuratos lyukgyengítést nem kell figyelembe venni. Húzott öv lyukgyengítésére vizsgálat szükséges:');
        $blc->txt('Húzott öv lyukgyengítése elhagyható, ha az alábbi feltétel teljesül:', '$A_(f,n et)$ az öv nettó keresztmetszeti területe, $A_f$ az öv bruttó keresztmetszeti területe.');
        $blc->math('(A_(f,n et)*0.9*f_u)/gamma_(M2) >= (A_f*f_y)/gamma_(M0)', '');
        $blc->label($f3->_MEd/$f3->_McRdpl, 'Képlékeny hajlítási kihasználtság');
        $blc->txt('', '$M_(Ed)/M_(c,Rd,pl)$');
        $blc->note('3 kmo. esetén:');
        $blc->label($f3->_MEd/$f3->_McRdel, 'Rugalmas hajlítási kihasználtság');
        $blc->txt('', '$M_(Ed)/M_(c,Rd,el)$');

        $blc->h1('Hajlítás és nyírás kölcsönhatása');
        if ($f3->_VEd >= 0.5*$f3->_VplRd) {
            $blc->math('V_(Ed) = '.$f3->_VEd.'[kN] %%% >= %%% 0.5*V_(c,Rd) = '. H3::n2(0.5*$f3->_VplRd).' [kN]');
            $blc->txt('Ezért a kölcsönhatás figyelembe veendő!');
            $blc->def('rho', H3::n4(pow(((2*$f3->_VEd)/$f3->_VplRd - 1), 2)), 'rho = ((2*V_(Ed))/V_(pl,Rd) - 1)^2 = %%');
            $blc->note('Nyomatéki teherbírás számításakor a folyáshatárt a nyírt területen $(1 - rho)$ csökkentő tényezővel kell figyelembe venni.');
            $blc->txt('1 és 2. kmo. nagy tengely körül hajlított I szelvények esetében a nyomatéki teherbírás:');
            $blc->math('A_w = A_v = '.$f3->_A_v.' [mm^2] %%% t_w = t = '.$f3->_t.' [mm]', 'Gerinc keresztmetszeti terület és gerincvastagság értékadása.');
            $blc->def('MyVRd', H3::n2(($f3->_Wpl - ($f3->_rho*pow($f3->_A_v, 2))/(4*$f3->_t))*($f3->_fy/($f3->__GM0*1000000))), 'M_(y,V,Rd) = (W_(pl,y) - (rho*A_w^2)/(4*t_w))*f_y/gamma_(M0) = %% [kNm]', '');
            $blc->txt('', 'De: $M_(y,V,Rd) < M_(c,Rd)$');
            $blc->note('Rugalmas számítás során a 3. és 4. kmo. szelvényekre a kölcsönhatást a feszültség alapú általános formulával kell meghatározni.');
        } else {
            $blc->math('V_(Ed) = '.$f3->_VEd.'[kN] %%% le %%% 0.5*V_(c,Rd) = '. H3::n2(0.5*$f3->_VplRd).' [kN]');
            $blc->label('yes', 'Kölcsönhatás vizsgálata nem szükséges.');
        }

        $blc->h1('Központosan húzott keresztmetszet');
        $blc->note('[Szürke 2007: 5.1.2 40.o]');
        $blc->note('Keresztmetszeti besorolásra nincs szükség.');
        $blc->def('NplRd', $ec->NplRd($f3->_A, $f3->_mat, $f3->_t), 'N_(pl,Rd) = (A*f_y)/gamma_(M,0) = %% [kN]', 'Teljes km. folyási ellenállása');
        $blc->def('NuRd', $ec->NuRd($f3->_A_net, $f3->_mat, $f3->_t), 'N_(u,Rd) = (0.9*A_(n\et)*f_u)/gamma_(M,2) = %% [kN]', 'Nettó km. képlékeny töréssel szembeni ellenállása');
        $blc->success0();
            $blc->def('NtRd', $ec->NtRd($f3->_A, $f3->_A_net, $f3->_mat, $f3->_t), 'N_(t,Rd) = min(N_(pl,Rd), N_(u,Rd)) = %% [kN]', 'Húzási ellenállás');
        $blc->success1();
        $blc->label($f3->_NEd/$f3->_NtRd, 'Húzási kihasználtság');
        $blc->txt('', '$N_(Ed)/N_(t,Rd)$');


        // KÖZPONTOS NYOMÁS ////////////////////////////////////////////////////////////////////////////////////////////

        $blc->h1('Központosan nyomott rudak rugalmas ellenállása');
        $blc->note('1, 2, 3. kmo. szelvényekre. $A = A_(eff)$ 4. kmo.-ra.');
        $blc->success0();
            $blc->def('NcRd', $ec->NcRd($f3->_A, $f3->_fy), 'N_(c,Rd) = %% [kN]');
        $blc->success1();
        $blc->math('N_(Ed) = '.$f3->_NEd.' [kN]', 'Húzó-nyomó erő');
        $blc->label($f3->_NEd/$f3->_NcRd, 'Nyomási kihasználtság');
        $blc->note('Oválfuratok esetén a furatok nem kitöltöttek, ezért nettó keresztmetszettel kell számolni.');


        // KIHAJLÁSI ELLENÁLLÁS ////////////////////////////////////////////////////////////////////////////////////////

//        $blc->h1('Központosan nyomott rudak kihajlási ellenállása');



        $blc->h1('Hajlítás és normálerő kölcsönhatása');
        $blc->note('Ha hajlítónyomatékkal egyidejűleg nomiálerő is hat, akkor kölcsönhatásukra tekintettel kell lemii. Ez képlékeny vizsgálat
esetén a nyomatéki ellenállás csökkentésével, rugalmas vizsgálat esetén a két hatásból származó feszültségek összegzésével
történhet.');
        $blc->note('A keresztmetszet besorolásához az EC3 nem ad egyértelmű előírásokat, Az első lehetőség  hogy normáleröre történő
vizsgálatnál a normálerőből számított besorolást használjuk, míg hajlítás esetén hajlitásra soroljuk be a szelvényt. A második
módszer szerint a keresztmetüeti osztályba soroláshoz mindkét igénybevételt együttesen kell figyelembe venni, és az
együttes hatásra kapott keresztmetszeti osztályt kell mindkét vizsgálathoz használni.');
        $blc->note('Az első módszer egyaerübb, viszont elvi ellentmondásra vezet, ha eredményeképpen hajlításnál képlékeny eljárást
alkalmazunk, de tiszta nyomásra 4. osztályává válik szelvényünk. A második módszer konzekvensebb, de elvégzése
általában bonyolultabb, különösen a haj ütött-nyomott gerinclemez besorolásakor.');
        $blc->note('l. és 2. osztályú szelvényeknél általában az első módszer használható. Az előbb bemutatott speciális esetben a második
módszer segíthet, mert a gerinc besorolása összetett igénybevételre kedvezőbb, mint a tiszta nyomásnál. 3. keresztmetszeti
osztályú szelvény rugalmas ellenőrzése során a feszültségeket egyébként is kiszámoljuk, tehát a második módszer használata
sem okoz külön nehézséget, ezt javasoljuk. A 4. osztályú szelvények vizsgálatához való (5.34) összefüggésben pedig az EC3
előírja az első módszer használatát.');
        $blc->txt('Az alábbi feltételek teljesülése esetén 1 és 2. kmo. övlemezes szelvények esetén a normálerő nem csökkenti az *y* tengely körüli nyomatéki ellenállást:');
        $blc->math('N_(Ed) = '.$f3->_NEd.'[kN] %%% < %%% 0.25*N_(pl,Rd) = '. 0.25*$f3->_NplRd.' [kN]');
        $blc->math('N_(Ed) = '.$f3->_NEd.'[kN] %%% < %%% (0.5*h_w*t_w*f_y)/gamma_(M0) = ');
        $blc->md('`TODO h_w miatt nem ellenőrizhető`');
    }
}
