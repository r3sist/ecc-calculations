<?php

namespace Calculation;

Class SteelSection extends \Ecc
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
        $ec->sectionFamilyList();
        $ec->sectionList($f3->_sectionFamily);
        $ec->saveSectionData($f3->_sectionName, true);

        $ec->matList('mat', 'S235', 'Acél anyagminőség');
        $ec->saveMaterialData($f3->_mat, false);
        $blc->input('t', 'Lemezvastagság', 10, 'mm');
        $blc->txt($f3->_sectionName.' legnagyobb lemezvastagsága: `t_(max) = '. 10*max($f3->_sectionData['tf'], $f3->_sectionData['tw']).' [mm]`');

        if ($f3->_t >= 40) {
            $f3->_fy = $f3->_fy40;
            $f3->_fu = $f3->_fu40;
            $blc->txt('`f_y` és `f_u` lemezvastagság miatt csökkentve.');
        }

        $blc->region0('rEgyedi', 'Egyedi keresztmetszeti paraméterek megadása');
            $blc->input('A_v', 'Egyedi nyírási keresztmetszet', 0, 'mm²', 'Az itt megadott nemnulla érték felülírja a betöltött szelvény adatokat!');
            $blc->input('A', 'Egyedi húzási keresztmetszet', 0, 'mm²', 'Az itt megadott nemnulla érték felülírja a betöltött szelvény adatokat!');
            $blc->input('Wpl', 'Egyedi képlékeny keresztmetszeti modulus', 0, 'mm³', 'Az itt megadott nemnulla érték felülírja a betöltött szelvény adatokat!');
            $blc->input('Wel', 'Egyedi rugalmas keresztmetszeti modulus', 0, 'mm³', 'Az itt megadott nemnulla érték felülírja a betöltött szelvény adatokat!');
        $blc->region1('rEgyedi');

        if ($f3->_A_v == 0) {
            $blc->def('A_v', $f3->_sectionData['Az']*100, 'A_v = A_(z,'.$f3->_sectionName.') = %% [mm^2]', 'Nyírási keresztmetszet');
        }

        if ($f3->_A == 0) {
            $blc->def('A', $f3->_sectionData['Ax']*100, 'A = A_(x,"'.$f3->_sectionName.'") = %% [mm^2]', 'Húzási keresztmetszet');
        }

        if ($f3->_Wpl == 0) {
            $blc->def('Wpl', $f3->_sectionData['W1pl']*1000, 'W_(pl) = W_(1,pl,"'.$f3->_sectionName.'") = %% [mm^3]', 'Képlékeny keresztmetszeti modulus');
        }

        if ($f3->_Wel == 0) {
            $blc->def('Wel', $f3->_sectionData['W1elt']*1000, 'W_(el) = W_(1,el,t,"'.$f3->_sectionName.'") = %% [mm^3]', 'Rugalmas keresztmetszeti modulus');
        }

        $blc->region0('r0', 'Nettó keresztmetszet számítás, csavarszám megadása');
            $blc->input('n', 'Csavarok száma', 0, '', 'Nemnulla esetén \`A_(n\et)\` számítása innen');
            $ec->boltList('btName');
            $d0 = $ec->boltProp($f3->_btName, 'd0');
            $blc->def('A_net_calculated', $f3->_A - $f3->_n*$d0*$f3->_t, 'A_(n\et, calcu\lated) = A - n*d_0*t = %% [mm^2]', 'Számított nettó keresztmetszet');
        $blc->region1('r0');
        if ($f3->_n >0 ) {
            $blc->def('A_net', $f3->_A_net_calculated, 'A_(n\et) = %% [mm^2]');
        } else {
            $blc->input('A_net', '`A_(n\et):` Lyukkal gyengített keresztmetszet', 0.8*$f3->_A, '`mm^2`');
        }

        $blc->h1('Nyírt keresztmetszet');
        $blc->note('[Szürke 2007: 5.1.5 41.o]');
        $blc->success0('v');
        $blc->def('VplRd', $ec->VplRd($f3->_A_v, $f3->_mat, $f3->_t), 'V_(c,Rd) = V_(pl,Rd) = (A_v*f_y)/(sqrt(3)*gamma_(M0)) = %% [kN]', 'Nyírási ellenállás 1 és 2. kmo. esetén');
        $blc->success1('v');
        $blc->note('3 és 4. kmo. esetén `V_(c,Rd) = (A_w*f_y)/(sqrt(3)*gamma_(M0))` H és I szelvénynél. Általános esetben `(I*t)/S*f_y/(sqrt(3)*gamma_(M0))`');

        $blc->input('VEd', '`V_(Ed)` nyíróerő', 100, 'kN');
        $blc->label($f3->_VEd/$f3->_VplRd, 'Nyírási kihasználtság');

        $blc->h1('Hajlított keresztmetszet');
        $blc->note('[Szürke 2007: 5.1.4 41.o]');
        $blc->success0('McRdpl');
        $blc->def('McRdpl', $ec->McRd($f3->_Wpl, $f3->_fy), 'M_(c,Rd) = (W_(pl)*f_y)/gamma_(M0) = %% [kNm]', '1 és 2. kmo. esetén');
        $blc->success1('McRdpl');
        $blc->def('McRdel', $ec->McRd($f3->_Wel, $f3->_fy), 'M_(c,Rd) = (W_(el)*f_y)/gamma_(M0) = %% [kNm]', '3. kmo. esetén');
        $blc->note('4. kmo. esetén \`W_(eff)\` hatékony keresztmetszet rugalmas keresztmetszeti modulussal kell számolni.');
        $blc->note('Nyomott zónában a nem oválfuratos lyukgyengítést nem kell figyelembe venni. Húzott öv lyukgyengítésére vizsgálat szükséges:');
        $blc->txt('Húzott öv lyukgyengítése elhagyható, ha az alábbi feltétel teljesül.', '\`A_(f,n\et)\` az öv nettó keresztmetszeti területe, \`A_f\` az öv bruttó keresztmetszeti területe.');
        $blc->math('(A_(f,n\et)*0.9*f_u)/gamma_(M2) >= (A_f*f_y)/gamma_(M0)', '');
        $blc->input('MEd', '`M_(Ed)` hajlítónyomaték', 100, 'kNm');
        $blc->label($f3->_MEd/$f3->_McRdpl, 'Hajlítási kihasználtság');

        $blc->h1('Hajlítás és nyírás kölcsönhatása');
        if ($f3->_VEd >= 0.5*$f3->_VplRd) {
            $blc->txt('Kölcsönhatás figyelembe veendő!');
            $blc->math('v_(Ed) = '.$f3->_VEd.'[kN] %%% >= %%% 0.5*V_(c,Rd) = '. 0.5*$f3->_VplRd.' [kN]');
            $blc->def('rho', pow(((2*$f3->_VEd)/$f3->_VplRd - 1), 2), 'rho = ((2*V_(Ed))/V_(pl,Rd) - 1)^2 = %%');
            $blc->note('Nyomatéki teherbírás számításakor a folyáshatárt a nyírt területen `(1 - rho)` csökkentő tényezővel kell figyelembe venni.');
            $blc->txt('1 és 2. kmo. nagy tengely körül hajlított I szelvények esetében a nyomatéki teherbírás:');
            $blc->math('A_w = A_v = '.$f3->_A_v.' [mm^2] %%% t_w = t = '.$f3->_t.' [mm]', 'Gerinc keresztmetszeti terület és gerincvastagság értékadása.');
            $blc->def('MyVRd', ($f3->_Wpl - ($f3->_rho*pow($f3->_A_v, 2))/(4*$f3->_t))*($f3->_fy/($f3->__GM0*1000000)), 'M_(y,V,Rd) = (W_(pl,y) - (rho*A_w^2)/(4*t_w))*f_y/gamma_(M0) = %% [kNm]', '');
            $blc->txt('', 'De: \`M_(y,V,Rd) < M_(c,Rd)\`');
            $blc->note('Rugalmas számítás során a 3. és 4. kmo. szelvényekre a kölcsönhatást a feszültség alapú általános formulával kell meghatározni.');
        } else {
            $blc->label('yes', 'Kölcsönhatás vizsgálata nem szükséges.');
        }

        $blc->h1('Központosan húzott keresztmetszet');
        $blc->note('[Szürke 2007: 5.1.2 40.o]');
        $blc->note('Keresztmetszeti besorolásra nincs szükség.');
        $blc->def('NplRd', $ec->NplRd($f3->_A, $f3->_mat, $f3->_t), 'N_(pl,Rd) = (A*f_y)/gamma_(M,0) = %% [kN]', 'Teljes km. folyási ellenállása');
        $blc->def('NuRd', $ec->NuRd($f3->_A_net, $f3->_mat, $f3->_t), 'N_(u,Rd) = (0.9*A_(n\et)*f_u)/gamma_(M,2) = %% [kN]', 'Nettó km. képlékeny töréssel szembeni ellenállása');
        $blc->success0('NtRd');
        $blc->def('NtRd', $ec->NtRd($f3->_A, $f3->_A_net, $f3->_mat, $f3->_t), 'N_(t,Rd) = min(N_(pl,Rd), N_(u,Rd)) = %% [kN]', 'Húzási ellenállás');
        $blc->success1('NtRd');

        $blc->input('NEd', '`N_(Ed)` húzóerő', 200, 'kN');
        $blc->label($f3->_NEd/$f3->_NtRd, 'Húzási kihasználtság');

        $blc->h1('Egyik szárukon kapcsolt szögacélok húzásra');
        $blc->note('Külpontosság elhanyagolható.');
        $blc->math('n = '.$f3->_n.' %%% d_0 = '.$d0.'[mm] %%% t = '.$f3->_t.'[mm] %%% f_u = '.$f3->_fu.'[MPa] %%% A_(n\et) = '.$f3->_A_net.' [mm^2]');
//      $blc->input('e1', 'Peremtávolság erő irányban', 30, 'mm');

        if ($f3->_n == 0) {
            $blc->danger('Csavarszámot meg kell adni a nettó keresztmetszet számításnál.');
        } else if ($f3->_n == 1) {
            $blc->txt('Erőátadás irányában egy csavar esete:');
            $blc->input('e2', 'Peremtávolság erő irányra merőleges irányban', 25, 'mm');
            $NuRd = (2*($f3->_e2 - 0.5*$d0)*$f3->_t*$f3->_fu)/($f3->__GM2*1000);
            $blc->success0('NuRd');
            $blc->def('NuRd', $NuRd, 'N_(u,Rd) = (2*(e_2-0.5*d_0)*t*f_u)/gamma_(M2) = %% [kN]');
        } else if ($f3->_n == 2) {
            $blc->txt('Erőátadás irányában két csavar esete:');
            $blc->input('p1', 'Csavar távolság erő irányban', 50, 'mm');
            if ($f3->_p1 <= 2.5*$d0) {
                $blc->def('beta_2', 0.4, 'beta_2 = %%');
            } else if ($f3->_p1 >= 5*$d0) {
                $blc->def('beta_2', 0.7, 'beta_2 = %%');
            } else {
                $blc->def('beta_2', $ec->linterp(2.5*$d0, 0.4, 5*$d0, 0.7, $f3->_p1), 'beta_2 = %%', 'Lineárisan interpolált érték!');
            }
            $NuRd = ($f3->_beta_2*$f3->_A_net*$f3->_fu)/($f3->__GM2*1000);
            $blc->success0('NuRd');
            $blc->def('NuRd', $NuRd, 'N_(u,Rd) = (beta_2*A_(n\et)*f_u)/gamma_(M2) = %% [kN]');
        } else {
            $blc->txt('Erőátadás irányában három vagy több csavar esete:');
            $blc->input('p1', 'Csavar távolság erő irányban', 50, 'mm');
            if ($f3->_p1 <= 2.5*$d0) {
                $blc->def('beta_3', 0.5, 'beta_2 = %%');
            } else if ($f3->_p1 >= 5*$d0) {
                $blc->def('beta_3', 0.7, 'beta_2 = %%');
            } else {
                $blc->def('beta_3', $ec->linterp(2.5*$d0, 0.5, 5*$d0, 0.7, $f3->_p1), 'beta_3 = %%', 'Lineárisan interpolált érték!');
            }
            $NuRd = ($f3->_beta_3*$f3->_A_net*$f3->_fu)/($f3->__GM2*1000);
            $blc->success0('NuRd');
            $blc->def('NuRd', $NuRd, 'N_(u,Rd) = (beta_3*A_(n\et)*f_u)/gamma_(M2) = %% [kN]');
        }
        $blc->success1('NuRd');
        $blc->label($f3->_NEd/$f3->_NuRd, 'Húzási kihasználtság');
        $blc->note('`beta_1` és `beta_2` külpontosság miatti tényezők. Táblázatos érték [Szürke 5.1/5.2 táblázat 40.o.]');

        $blc->h1('Központosan nyomott rudak ellenállása');
        $blc->note('1, 2, 3. kmo. szelvényekre. `A = A_(eff)` 4. kmo.-ra.');
        $blc->success0('NcRd');
            $blc->def('NcRd', $ec->NcRd($f3->_A, $f3->_fy), 'N_(c,Rd) = %% [kN]');
        $blc->success1('NcRd');
        $blc->math('N_(Ed) = '.$f3->_NEd.' [kN]', 'Húzó-nyomó erő');
        $blc->label($f3->_NEd/$f3->_NcRd, 'Nyomási kihasználtság');
        $blc->note('Oválfuratok esetén a furatok nem kitöltöttek, ezért nettó keresztmetszettel kell számolni.');

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
        $blc->txt('Az alábbi feltételek teljesülése esetén 1 és 2. kmo. övlemezes szelvények esetén a normálerő nem csökkenti az y tengely körüli nyomatéki ellenállást:');
        $blc->math('N_(Ed) = '.$f3->_NEd.'[kN] %%% < %%% 0.25*N_(pl,Rd) = '. 0.25*$f3->_NplRd.' [kN]');
        $blc->math('N_(Ed) = '.$f3->_NEd.'[kN] %%% < %%% (0.5*h_w*t_w*f_y)/gamma_(M0) = ');
        $blc->md('`TODO` h_w miatt nem ellenőrizhető');
    }
}
