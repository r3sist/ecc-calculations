<?php declare(strict_types = 1);
/**
 * RC beam analysis according to Eurocodes - Calculation class for ECC framework
 * (c) Bence VÁNKOS | https://structure.hu | https://github.com/r3sist/ecc-calculations
 */

namespace Calculation;

use Base;
use Ecc\Blc;
use Ec\Ec;
use H3;
use Exception;
use resist\SVG\SVG;

Class Beam
{
    /**
     * @throws Exception
     */
    public function calc(Base $f3, Blc $blc, Ec $ec): void
    {
        $blc->note('[Lásd még](https://structure.hu/berci/section): &copy; Tóth Bertalan');

        $blc->info0('Igénybevételek');
            $blc->numeric('MEd', ['M_(Ed)', 'Nyomatéki igénybevétel'], 100, 'kNm');
            $blc->numeric('VEd', ['V_(Ed)', 'Nyíróerő igénybevétel'], 500, 'kN');
            $blc->numeric('NEd', ['N_(Ed)', 'Normál igénybevétel'], 0, 'kN');
        $blc->info1();

        $ec->matList('cMat', 'C30/37', ['', 'Beton anyagminőség']);
        $ec->spreadMaterialData($f3->_cMat, 'c');
        $ec->matList('rMat', 'B500', ['', 'Betonvas anyagminőség']);
        $ec->spreadMaterialData($f3->_rMat, 'r');

        $blc->numeric('cnom', ['c_(nom)', 'Betonfedés'], 25, 'mm', '');
        $blc->note('Betonfedés [kapcsolódó számítása](https://structure.hu/calc/Concrete#i-chapterhead2-betonfedes)');

        $blc->info0('Geometria');
            $blc->boo('sectionT', ['', 'T keresztmetszet'], true);
            $blc->numeric('h', ['h', 'Keresztmetszet teljes magassága'], 700, 'mm', '');
            $blc->numeric('b', ['b', 'Keresztmetszet alsó szélessége'], 250, 'mm', '');
            $f3->_hf = 0;
            $f3->_bf = $f3->_b;
            if ($f3->_sectionT) {
                $blc->numeric('hf', ['h_f', 'Keresztmetszet felső övének magassága'], 150, 'mm', '');
                $blc->numeric('bf', ['b_f', 'Keresztmetszet felső szélessége'], 600, 'mm', '');
            }
            $blc->def('Ac', $f3->_hf*$f3->_bf + (float)($f3->_h - $f3->_hf)*$f3->_b, 'A_c = %% [mm^2]', 'Beton keresztmetszet területe');
        $blc->info1();

        $blc->info0('Hossz- és nyírási vasalás');
            $ec->wrapRebarCount('nc', 'Dc', '$A_(sc)$ Hosszirányú nyomott felső vasalás', 2, 20, '', 'Asc');
            $ec->wrapRebarCount('nt', 'Dt', '$A_(st)$ Hosszirányú húzott alsó vasalás', 2, 20, '', 'Ast');
            $blc->def('As', H3::n0((float)$f3->_Ast + (float)$f3->_Asc), 'A_s = %% [mm^2] = '.H3::n1(((float)$f3->_Ast + (float)$f3->_Asc)/$f3->_Ac*100).' [%]', 'Alkalmazott összes vasmennyiség');
            $blc->hr();
            $ec->wrapRebarDistance('sw', 'Dw', '$A_(sw)$ Kengyelezés vasalás kiosztása', 200, 10, '');
            $blc->input('nw', ['n_w', 'Kengyelszárak száma'], 2, '', '', 'numeric|min_numeric,1|max_numeric,10');
            $blc->def('Asw', H3::n0(($f3->_nw*($ec->A($f3->_Dw)/$f3->_sw))*1000), 'A_(sw) = %% [(mm^2)/m]', 'Nyírási kengyel vasalás fajlagos keresztmetszeti területe');
        $blc->info1();

        // SVG
        $xs = 600; // SVG size
        $ys = 300;
        $x0 = 50; // Margin of figure
        $y0 = 50;
        $xc = $xs-2*$x0; // Figure canvas size
        $yc = $ys-2*$y0;
        $svg = new SVG($xs, $ys);
        $svg->makeRatio($xc, $yc, $f3->_bf, $f3->_h);
        // Contour
        $bfx = $f3->_bf - $f3->_b;
        $svg->setFill('#f4f4f4');
        $svg->addPolygon([
            [0, 0],
            [$f3->_bf, 0],
            [$f3->_bf, $f3->_hf],
            [$bfx/2 + $f3->_b, $f3->_hf],
            [$bfx/2 + $f3->_b, $f3->_h],
            [$bfx/2, $f3->_h],
            [$bfx/2, $f3->_hf],
            [0, $f3->_hf],
        ], $x0, $y0);
        // Dimensions
        $svg->setFill('none');
        $svg->setColor('grey');
        $svg->setSize(12);
        $svg->addDimH(0, $f3->_bf, $y0/2, $f3->_bf, $x0);
        $svg->addDimV(0, $f3->_h, $x0/2, $f3->_h, $y0);
        if ($f3->_sectionT) {
            $svg->addDimH($bfx/2, $f3->_b, $ys-$y0/2, $f3->_b, $x0);
            $svg->addDimV(0, $f3->_hf, $f3->_hf + $x0*2, $f3->_hf, $y0);
        }
        // Sirrups
        $svg->setColor('green');
        $svg->addRectangle($bfx/2 + $f3->_cnom + $f3->_Dw/2, $f3->_cnom + $f3->_Dw/2, $f3->_b - 2*$f3->_cnom - $f3->_Dw, $f3->_h - 2*$f3->_cnom - $f3->_Dw, $x0, $y0, 2*$f3->_Dw);
        if ($f3->_sectionT) {
            $svg->addRectangle($f3->_cnom + $f3->_Dw/2, $f3->_cnom + $f3->_Dw/2, $f3->_bf - 2*$f3->_cnom - $f3->_Dw, $f3->_hf - 2*$f3->_cnom - $f3->_Dw, $x0, $y0, 2*$f3->_Dw);
        }
        // Rebars
        $svg->setColor('blue');
        $svg->setFill('blue');
        $rebarPos = $f3->_cnom + $f3->_Dw + $f3->_Dc/2;
        $topLength = $f3->_bf - 2*$rebarPos;
        $bottomLength = $f3->_b - 2*$rebarPos;
        $topDelta = $topLength/($f3->_nc - 1);
        $bottomDelta = $bottomLength/($f3->_nt - 1);
        for ($i = 0; $i < $f3->_nc; $i++) {
            $svg->addCircle($rebarPos + $i*$topDelta, $rebarPos, $f3->_Dc/2, $x0, $y0);
        }
        for ($i = 0; $i < $f3->_nt; $i++) {
            $svg->addCircle($bfx/2 + $rebarPos + $i*$bottomDelta, $f3->_h - $rebarPos, $f3->_Dt/2, $x0, $y0);
        }
        $blc->svg($svg);

        $blc->note('Egy sor húzott vas figyelembe vétele.');
        $dst = 0;
        if ($f3->_nt > 0) {
            $dst = $f3->_h - $f3->_cnom - $f3->_Dw - $f3->_Dc/2;
        }
        $blc->def('dst', $dst, 'd_(st) = h - c_(nom) - phi_w - phi_t/2 = %% [mm]', 'Húzott vasalás hasznos magassága');
        $dsc = 0;
        if ($f3->_nc > 0) {
            $dsc = $f3->_cnom + $f3->_Dw + $f3->_Dt/2;
        }
        $blc->def('dsc', $dsc, 'd_(sc) = c_(nom) + phi_w + phi_c/2 = %% [mm]', 'Nyomott vasalás hasznos magassága');
        $blc->def('z', H3::n0($dst*0.9), 'z = d_(st)*0.9 = %% [mm]', 'Belső kar');
        $blc->numeric('theta', ['theta', 'Nyomott rácsrúd dőlésszöge'], 38, '°', '$1 lt cot(theta) lt 2.5 rArr 21.8[deg] lt theta lt 45[deg]$', 'min_numeric,21.8|max_numeric,45');

        $blc->h1('Nyírási teherbírás');
        $blc->region0('V');
            $blc->math('gamma_c = '.$f3->__Gc, 'Beton biztonsági tényező');
            $blc->def('CRdc', 0.18/$f3->__Gc, 'C_(Rd,c) = 0.18/gamma_c = %%');
            $blc->def('k', min(1 + sqrt(200/$f3->_dst), 2), 'k = min{(1 + sqrt(200/d_(st))), (2):} = %%');
            $blc->def('rho1', H3::n4(min($f3->_Ast/$f3->_b/$f3->_dst, 0.02)), 'rho_1 = {(A_(st)/b/d_(st)), (0.02):} = %%', 'Húzott acélhányad értéke, felülről korlátozva');
            $blc->math('b = '.$f3->_b.' [mm]', 'Keresztmetszet alsó szélessége');
            $blc->math('f_(ck) = '.$f3->_cfck.' [N/(mm^2)]', 'Nyomószilárdság karakterisztikus értéke (5% kvantilis) (□150×150×150 kocka)');
            $VRdc = ($f3->_CRdc*$f3->_k*((100*$f3->_rho1 * $f3->_cfck)**(1/3))*$f3->_b*$f3->_dst)/1000;
            $blc->def('VRdc', H3::n2($VRdc), 'V_(Rdc) = C_(Rdc)*(100*rho_1*f_(ck))^(1/3)*b*d_(st) = %% [kN]', 'Keresztmetszet nyírási teherbírása nyírási valaslás nélkül');
            $blc->def('alphacw', 1.0, 'alpha_(cw) = %%', '');
            $blc->def('nu', (0.6*(1-$f3->_cfck/250)), 'nu = 0.6*(1 - f_(ck)/250) = %%', 'Nyírásra berepedt beton szilárdsági csökkentő tényezője');
            $VRdmax = $f3->_alphacw*$f3->_b*$f3->_z*$f3->_nu*$f3->_cfcd*1/((1/tan($f3->_theta*M_PI/180)) + tan($f3->_theta*M_PI/180))/1000;
            $blc->def('VRdmax', H3::n2($VRdmax), 'V_(Rd,max) = alpha_(cw)*b*z*nu*f_(cd)*1/(cot(theta) + tan(theta)) = %% [kN]', 'Nyomott rácsrúd nyírási teherbírása');
            $blc->def('thetaOpt', H3::n1(max(atan((1-$VRdc/$f3->_VEd)/1.2)*180/M_PI, 30)), 'theta_(opt) = max{( arctan( (1 - V_(Rd,c)/V_(Ed))/1.2) ),(30):} = %% [deg] hArr color(red)(theta = '.$f3->_theta.') [deg]', '$theta$ ajánlott értéke. Normálerő nincs figyelembe véve!');
            $VRds = $f3->_Asw*$f3->_z*$f3->_rfyd*(1/tan($f3->_theta*M_PI/180))/1000000;
            $blc->def('VRds', H3::n2($VRds), 'V_(Rd,s) = A_(sw)*z*fy_(w,d)*(cot(theta)) = %% [kN]', '');

        $blc->region1();
        if (abs($f3->_theta - $f3->_thetaOpt) > $f3->_theta*0.1) {
            $blc->danger('10%-nál nagyobb mértékben eltér $theta$ a javasolt értéktől.');
        }
        $blc->success0();
            $blc->math('V_(Rd,c) = '. $f3->_VRdc.' [kN]', 'Keresztmetszet nyírási teherbírása nyírási vasalás nélkül');
            $blc->math('V_(Rd,max) = '. $f3->_VRdmax.' [kN]', 'Nyomott rácsrúd nyírási teherbírása');
            $blc->math('V_(Rd,s) = '. $f3->_VRds.' [kN]', '');
            $blc->hr();
            $blc->def('VRd', min($f3->_VRdmax, $f3->_VRds), 'V_(Rd) = min{(V_(Rd,max)),(V_(Rd,s)):} = %% [kN]', 'Nyírási teherbírás tervezési értéke');
        $blc->success1();

        $blc->h1('Hajlítási teherbírás');
        $blc->def('nE', H3::n1(($f3->_rEs*1000)/($f3->_cEceff*1000)), 'n_E = E_s/E_(c,Eff) = %%', 'Betonacél és beton rugalmassái modulus aránya');
        $blc->h2('I. repedés mentes feszültség állapot');
        $blc->def('AiI', $f3->_b*$f3->_h + ($f3->_bf-$f3->_b)*$f3->_hf + ($f3->_nE-1)*$f3->_Ast + ($f3->_nE-1)*$f3->_Asc, 'A_(i,I) = %% [mm^2]', 'Ideális keresztmetszeti terület');
        $blc->def('SiI', $f3->_b*$f3->_h*$f3->_h/2 + ($f3->_bf-$f3->_b)*$f3->_hf*$f3->_hf/2 + ($f3->_nE-1)*$f3->_Asc*$f3->_dsc + ($f3->_nE-1)*$f3->_Ast*$f3->_dst, 'S_(i,I) = %% [mm^2]', 'Statikai nyomaték a felső (nyomott) szélső szálra');
        $blc->def('XiI', H3::n2($f3->_SiI/$f3->_AiI), 'X_(i,I) = S_(i,I)/A_(i,I) = %% [mm]', 'Semleges tengely távolsága a felső (nyomott) szélső száltól');
        $blc->def('IiI', $f3->_b* ($f3->_h ** 3) /12 + $f3->_b*$f3->_h* (($f3->_h / 2 - $f3->_XiI) ** 2) + ($f3->_bf-$f3->_b)* ($f3->_hf ** 3) /12 + ($f3->_bf-$f3->_b)*$f3->_hf* (($f3->_XiI - $f3->_hf / 2) ** 2) + ($f3->_nE-1)*$f3->_Ast* (($f3->_dst - $f3->_XiI) ** 2) + ($f3->_nE-1)*$f3->_Asc* (($f3->_XiI - $f3->_dsc) ** 2), 'I_(i,I) = %% [mm^4]', 'Tehetetlenségi inercia a semleges tengelyre');
        $blc->def('Mcr', H3::n2($f3->_cfctm*$f3->_IiI/($f3->_h-$f3->_XiI)/1000000), 'M_(cr) = f_(ctm)*I_(i,I)/(h-X_(i,I)) = %% [kNm]', 'Repesztő nyomaték értéke $f_(ctm)$-hez');
        $blc->def('sigmaccI', H3::n2(min(($f3->_Mcr*1000000)/$f3->_IiI*$f3->_XiI, $f3->_cfcd)), 'sigma_(c,c,I) = min{(M_(cr)/(I_(i,I)*X_(i,I))), (f_(cd)):} = %% [N/(mm^2)]', 'Beton nyomófeszültség a felső szélső szálban repesztőnyomatékból');
        $blc->def('sigmactI', (($f3->_MEd*1000000)/$f3->_IiI*($f3->_h - $f3->_XiI) <= 1.1*$f3->_cfctm ? H3::n2(($f3->_MEd*1000000)/$f3->_IiI*($f3->_h-$f3->_XiI)) : 0), 'sigma_(c,t,I) = {(M_(Ed)/I_(i,I)*(h-X_(i,I)) le 1.1 f_(ctm) rarr M_(Ed)/I_(i,I)*(h-X_(i,I))), (0):} = %% [N/(mm^2)]', 'Beton húzófeszültség a felső szélső szálban repesztőnyomatékból');
        $blc->def('sigmastI', H3::n2($f3->_nE*($f3->_Mcr*1000000)/$f3->_IiI*($f3->_dst - $f3->_XiI)), 'sigma_(s,t,I) = n_E*M_(cr)/I_(i,I)*(d_(s,t) - X_(i,I)) = %% [N/(mm^2)]', 'Feszültség az alsó húzott betonacélban repesztőnyomatékból');

        $blc->h2('II. berepedt (rugalmas) feszültség állapot');
        $blc->def('aII', $f3->_b/2, 'a_(II) = b/2 = %%', 'Másodfokú megoldó képlet elemei');
        $blc->def('bII', ($f3->_bf-$f3->_b)*$f3->_hf + ($f3->_nE-1)*$f3->_Asc + $f3->_nE*$f3->_Ast, 'b_(II) = (b_f-b)*h_f + (n_E-1)*A_(sc) + n_E*A_(st) = %%');
        $blc->def('cII', (-1)*($f3->_bf-$f3->_b)*($f3->_hf**2)/2 - $f3->_nE*$f3->_Asc*$f3->_dsc - $f3->_nE*$f3->_Ast*$f3->_dst, 'c_(II) = - (b_f-b)*(h_f^2)/2 - n_E*A_(sc)*d_(sc) - n_E*A_(st)*d_(st) = %%');
//        $blc->def('XiII', ((-1)*$f3->_bII + sqrt(($f3->_bII**2) - 4*$f3->_aII*$f3->_cII))/(2*$f3->_aII), '%%', 'Semleges tengely távolsága a felső (nyomott) szélső száltól'); // Berci féle megoldás
        $blc->def('XiII', $ec->chooseRoot($f3->_XiI, $ec->quadratic($f3->_aII, $f3->_bII, $f3->_cII, 2)), 'X_(i,II) = %% [mm]', 'Semleges tengely távolsága a felső (nyomott) szélső száltól');
        $blc->def('IiII', $f3->_b*$f3->_XiII**3/3 + ($f3->_bf - $f3->b)*$f3->_hf**3/12 + ($f3->_bf-$f3->_b)*$f3->_hf*($f3->_XiII-$f3->_hf/2)**2 + $f3->_nE*$f3->_Ast*($f3->_dst-$f3->_XiII)**2 + ($f3->_nE-1)*$f3->_Asc*($f3->_XiII-$f3->_dsc)**2, 'I_(i,II) = %% [mm^4]', 'Tehetetlenségi inercia  asmeleges tengelyre');
        $blc->def('kappaIIc', $f3->_epscy/$f3->_XiII, '%%', 'Görbület a II. feszültség állapot határhelyzetében a nyomott beton megfolyásának pillanatában');
        $blc->def('kappaIIs', $f3->_epssy/($f3->_dst-$f3->_XiII), '%%', 'Görbület a II. feszültség állapot határhelyzetében a húzott acél megfolyásának pillanatában');


        $blc->h1('Szerkesztési szabályok ellenőrzése');
        $blc->h4('Minimális húzott vasmennyiség:');
        $blc->def('rhoMin', max(0.26*($f3->_cfctm/$f3->_rfy), 0.0015), 'rho_(min) = max{(0.26*f_(ctm)/f_(yk)),(0.0015):} = max{(0.26*'.$f3->_cfctm.'/'.$f3->_rfy.'),(0.0015):} = %%', 'Minimális húzott vashányad');
        $blc->def('AstMin', H3::n0($f3->_rhoMin*$f3->_b*$f3->_dst), 'A_(st,min) = rho_(min)*b*d = '.$f3->_rhoMin.'*'.$f3->_b.'*'.$f3->_dst.' = %% [mm^2]', 'Előírt minimális húzott vasmennyiség négyszög keresztmetszet esetén');
        $blc->note('T szelvény esetén, ha a fejlemez nyomott, csak a gerinc szélességét kell `b` számításnál figyelembe venni, ha a fejlemez húzott, `b` a nyomott borda szélességének kétszerese.');
        $blc->label(($f3->_AstMin/$f3->_Ast>=1?'no':'yes'), $f3->_Ast.' mm² = '. H3::n1(1/($f3->_AstMin/$f3->_Ast)*100) .' %');

        $blc->h4('Maximális összes vasmennyiség:');
        $blc->def('AsMax', 0.04*$f3->_Ac, 'A_(s,max) = 0.04*A_c = %% [mm^2]', 'Összes hosszvasalás megengedett legnagyobb mennyisége');
        $blc->label(($f3->_As/$f3->_AsMax>=1?'no':'yes'), $f3->_As.' mm² = '. H3::n1(($f3->_As/$f3->_AsMax)*100) .' %');

        $blc->h4('Nyírási acélhányad:');
        if ($f3->_h > $f3->_b) {
            $blc->def('rhowmin', max(0.08*sqrt($f3->_cfck)/$f3->_rfy, 0.001), 'rho_(w,min) = %%', 'Nyírási acélhányad minimális értéke');
        } else {
            $blc->def('rhowmin', (0.07 + 0.03*$f3->_h/$f3->_b)/100,'rho_(w,min) = %%');
        }
        $blc->def('rhow', $f3->_Asw/$f3->_b*0.001, 'rho_w = A_(sw)/b = %%');
        $uRhowMin = 'no';
        if ($f3->_rhowmin/$f3->_rhow <= 1) {
            $uRhowMin = 'yes';
        }
        $blc->label($uRhowMin, H3::n0($f3->_rhow/$f3->_rhowmin*100).'%-a a min. vashányadnak');

        $blc->h4('Egy sorban elhelyezhető vasak száma:');
        $blc->def('amin', max($f3->_Dt, 20), 'a_(min) = max{(phi_t = '.$f3->_Dt.'),(20):} = %% [mm]', 'Húzott betonacélok közötti min távolság');
        $blc->def('ntmax', floor(($f3->_b - 2*$f3->_cnom - 2*$f3->_Dw + $f3->_amin - 5)/((float)$f3->_Dt + $f3->_amin)), 'n_(t, max) = (b-2*c_(nom)-2*phi_w+a_(min)-5)/(phi_t + a_(min)) = %%', 'Egy sorban elhelyezhető vasak száma');
        $blc->note('Kengyelgörbület miatt 5mm-rel csökkentve a hely');
        if ($f3->_nt > $f3->_ntmax) {
            $blc->label('no', 'Túl nagy húzott vas szám');
        }

        $blc->h4('Nyírási kengyelek maximális távolsága:');
        $blc->def('s1max', min(0.5*$f3->_dst,1.5*$f3->_b,300), 's_(1,max) = %% [mm]');
        if ($f3->_sw >= $f3->_s1max) {
            $blc->label('no', $f3->_sw.'mm &lt; '.$f3->_s1max.' mm');
        }
    }
}
