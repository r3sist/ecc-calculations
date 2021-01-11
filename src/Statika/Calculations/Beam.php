<?php declare(strict_types = 1);
/**
 * RC beam analysis according to Eurocodes - Calculation class for Statika framework
 * (c) Bence VÁNKOS | https://structure.hu | https://github.com/r3sist/ecc-calculations
 */

namespace Statika\Calculations;

use H3;
use Exception;
use resist\SVG\SVG;
use Statika\EurocodeInterface;

Class Beam
{
    /**
     * @param Ec $ec
     * @throws Exception
     */
    public function calc(EurocodeInterface $ec): void
    {
        $ec->note('[Lásd még](https://structure.hu/berci/section): &copy; Tóth Bertalan');

        $ec->info0('Igénybevételek');
            $ec->numeric('MEd', ['M_(Ed)', 'Nyomatéki igénybevétel'], 100, 'kNm');
            $ec->numeric('VEd', ['V_(Ed)', 'Nyíróerő igénybevétel'], 500, 'kN');
            $ec->numeric('NEd', ['N_(Ed)', 'Normál igénybevétel'], 0, 'kN');
        $ec->info1();

        $ec->concreteMaterialListBlock('concreteMaterialName', 'C30/37');
        $concreteMaterial = $ec->getMaterial($ec->concreteMaterialName);

        $ec->rebarMaterialListBlock('rebarMaterialName');
        $rebarMaterial = $ec->getMaterial($ec->rebarMaterialName);

        $ec->numeric('cnom', ['c_(nom)', 'Betonfedés'], 25, 'mm', '');
        $ec->note('Betonfedés [kapcsolódó számítása](https://structure.hu/calc/Concrete#i-chapterhead2-betonfedes)');

        $ec->info0('Geometria');
            $ec->boo('sectionT', ['', 'T keresztmetszet'], true);
            $ec->numeric('h', ['h', 'Keresztmetszet teljes magassága'], 700, 'mm', '');
            $ec->numeric('b', ['b', 'Keresztmetszet alsó szélessége'], 250, 'mm', '');
            $ec->hf = 0;
            $ec->bf = $ec->b;
            if ($ec->sectionT) {
                $ec->numeric('hf', ['h_f', 'Keresztmetszet felső övének magassága'], 150, 'mm', '');
                $ec->numeric('bf', ['b_f', 'Keresztmetszet felső szélessége'], 600, 'mm', '');
            }
            $ec->def('Ac', $ec->hf*$ec->bf + (float)($ec->h - $ec->hf)*$ec->b, 'A_c = %% [mm^2]', 'Beton keresztmetszet területe');
        $ec->info1();

        $ec->info0('Hossz- és nyírási vasalás');
            $ec->wrapRebarCount('nc', 'Dc', '$A_(sc)$ Hosszirányú nyomott felső vasalás', 2, 20, '', 'Asc');
            $ec->wrapRebarCount('nt', 'Dt', '$A_(st)$ Hosszirányú húzott alsó vasalás', 2, 20, '', 'Ast');
            $ec->def('As', H3::n0((float)$ec->Ast + (float)$ec->Asc), 'A_s = %% [mm^2] = '.H3::n1(((float)$ec->Ast + (float)$ec->Asc)/$ec->Ac*100).' [%]', 'Alkalmazott összes vasmennyiség');
            $ec->hr();
            $ec->wrapRebarDistance('sw', 'Dw', '$A_(sw)$ Kengyelezés vasalás kiosztása', 200, 10, '');
            $ec->input('nw', ['n_w', 'Kengyelszárak száma'], 2, '', '', 'numeric|min_numeric,1|max_numeric,10');
            $ec->def('Asw', H3::n0(($ec->nw*($ec->A($ec->Dw)/$ec->sw))*1000), 'A_(sw) = %% [(mm^2)/m]', 'Nyírási kengyel vasalás fajlagos keresztmetszeti területe');
        $ec->info1();

        // SVG
        $xs = 600; // SVG size
        $ys = 300;
        $x0 = 50; // Margin of figure
        $y0 = 50;
        $xc = $xs-2*$x0; // Figure canvas size
        $yc = $ys-2*$y0;
        $svg = new SVG($xs, $ys);
        $svg->makeRatio($xc, $yc, $ec->bf, $ec->h);
        // Contour
        $bfx = $ec->bf - $ec->b;
        $svg->setFill('#f4f4f4');
        $svg->addPolygon([
            [0, 0],
            [$ec->bf, 0],
            [$ec->bf, $ec->hf],
            [$bfx/2 + $ec->b, $ec->hf],
            [$bfx/2 + $ec->b, $ec->h],
            [$bfx/2, $ec->h],
            [$bfx/2, $ec->hf],
            [0, $ec->hf],
        ], $x0, $y0);
        // Dimensions
        $svg->setFill('none');
        $svg->setColor('grey');
        $svg->setSize(12);
        $svg->addDimH(0, $ec->bf, $y0/2, $ec->bf, $x0);
        $svg->addDimV(0, $ec->h, $x0/2, $ec->h, $y0);
        if ($ec->sectionT) {
            $svg->addDimH($bfx/2, $ec->b, $ys-$y0/2, $ec->b, $x0);
            $svg->addDimV(0, $ec->hf, $ec->hf + $x0*2, $ec->hf, $y0);
        }
        // Sirrups
        $svg->setColor('green');
        $svg->addRectangle($bfx/2 + $ec->cnom + $ec->Dw/2, $ec->cnom + $ec->Dw/2, $ec->b - 2*$ec->cnom - $ec->Dw, $ec->h - 2*$ec->cnom - $ec->Dw, $x0, $y0, 2*$ec->Dw);
        if ($ec->sectionT) {
            $svg->addRectangle($ec->cnom + $ec->Dw/2, $ec->cnom + $ec->Dw/2, $ec->bf - 2*$ec->cnom - $ec->Dw, $ec->hf - 2*$ec->cnom - $ec->Dw, $x0, $y0, 2*$ec->Dw);
        }
        // Rebars
        $svg->setColor('blue');
        $svg->setFill('blue');
        $rebarPos = $ec->cnom + $ec->Dw + $ec->Dc/2;
        $topLength = $ec->bf - 2*$rebarPos;
        $bottomLength = $ec->b - 2*$rebarPos;
        $topDelta = $topLength/($ec->nc - 1);
        $bottomDelta = $bottomLength/($ec->nt - 1);
        for ($i = 0; $i < $ec->nc; $i++) {
            $svg->addCircle($rebarPos + $i*$topDelta, $rebarPos, $ec->Dc/2, $x0, $y0);
        }
        for ($i = 0; $i < $ec->nt; $i++) {
            $svg->addCircle($bfx/2 + $rebarPos + $i*$bottomDelta, $ec->h - $rebarPos, $ec->Dt/2, $x0, $y0);
        }
        $ec->svg($svg);

        $ec->note('Egy sor húzott vas figyelembe vétele.');
        $dst = 0;
        if ($ec->nt > 0) {
            $dst = $ec->h - $ec->cnom - $ec->Dw - $ec->Dc/2;
        }
        $ec->def('dst', $dst, 'd_(st) = h - c_(nom) - phi_w - phi_t/2 = %% [mm]', 'Húzott vasalás hasznos magassága');
        $dsc = 0;
        if ($ec->nc > 0) {
            $dsc = $ec->cnom + $ec->Dw + $ec->Dt/2;
        }
        $ec->def('dsc', $dsc, 'd_(sc) = c_(nom) + phi_w + phi_c/2 = %% [mm]', 'Nyomott vasalás hasznos magassága');
        $ec->def('z', H3::n0($dst*0.9), 'z = d_(st)*0.9 = %% [mm]', 'Belső kar');
        $ec->numeric('theta', ['theta', 'Nyomott rácsrúd dőlésszöge'], 38, '°', '$1 lt cot(theta) lt 2.5 rArr 21.8[deg] lt theta lt 45[deg]$', 'min_numeric,21.8|max_numeric,45');

        $ec->h1('Nyírási teherbírás');
        $ec->region0('V');
            $ec->math('gamma_c = '.$ec::Gc, 'Beton biztonsági tényező');
            $ec->def('CRdc', 0.18/$ec::Gc, 'C_(Rd,c) = 0.18/gamma_c = %%');
            $ec->def('k', min(1 + sqrt(200/$ec->dst), 2), 'k = min{(1 + sqrt(200/d_(st))), (2):} = %%');
            $ec->def('rho1', H3::n4(min($ec->Ast/$ec->b/$ec->dst, 0.02)), 'rho_1 = {(A_(st)/b/d_(st)), (0.02):} = %%', 'Húzott acélhányad értéke, felülről korlátozva');
            $ec->math('b = '.$ec->b.' [mm]', 'Keresztmetszet alsó szélessége');
            $ec->math('f_(ck) = '.$concreteMaterial->fck.' [N/(mm^2)]', 'Nyomószilárdság karakterisztikus értéke (5% kvantilis) (□150×150×150 kocka)');
            $VRdc = ($ec->CRdc*$ec->k*((100*$ec->rho1 * $concreteMaterial->fck)**(1/3))*$ec->b*$ec->dst)/1000;
            $ec->def('VRdc', H3::n2($VRdc), 'V_(Rdc) = C_(Rdc)*(100*rho_1*f_(ck))^(1/3)*b*d_(st) = %% [kN]', 'Keresztmetszet nyírási teherbírása nyírási valaslás nélkül');
            $ec->def('alphacw', 1.0, 'alpha_(cw) = %%', '');
            $ec->def('nu', (0.6*(1-$concreteMaterial->fck/250)), 'nu = 0.6*(1 - f_(ck)/250) = %%', 'Nyírásra berepedt beton szilárdsági csökkentő tényezője');
            $VRdmax = $ec->alphacw*$ec->b*$ec->z*$ec->nu*$concreteMaterial->fcd*1/((1/tan($ec->theta*M_PI/180)) + tan($ec->theta*M_PI/180))/1000;
            $ec->def('VRdmax', H3::n2($VRdmax), 'V_(Rd,max) = alpha_(cw)*b*z*nu*f_(cd)*1/(cot(theta) + tan(theta)) = %% [kN]', 'Nyomott rácsrúd nyírási teherbírása');
            $ec->def('thetaOpt', H3::n1(max(atan((1-$VRdc/$ec->VEd)/1.2)*180/M_PI, 30)), 'theta_(opt) = max{( arctan( (1 - V_(Rd,c)/V_(Ed))/1.2) ),(30):} = %% [deg] hArr color(red)(theta = '.$ec->theta.') [deg]', '$theta$ ajánlott értéke. Normálerő nincs figyelembe véve!');
            $VRds = $ec->Asw*$ec->z*$rebarMaterial->fyd*(1/tan($ec->theta*M_PI/180))/1000000;
            $ec->def('VRds', H3::n2($VRds), 'V_(Rd,s) = A_(sw)*z*fy_(w,d)*(cot(theta)) = %% [kN]', '');

        $ec->region1();
        if (abs($ec->theta - $ec->thetaOpt) > $ec->theta*0.1) {
            $ec->danger('10%-nál nagyobb mértékben eltér $theta$ a javasolt értéktől.');
        }
        $ec->success0();
            $ec->math('V_(Rd,c) = '. $ec->VRdc.' [kN]', 'Keresztmetszet nyírási teherbírása nyírási vasalás nélkül');
            $ec->math('V_(Rd,max) = '. $ec->VRdmax.' [kN]', 'Nyomott rácsrúd nyírási teherbírása');
            $ec->math('V_(Rd,s) = '. $ec->VRds.' [kN]', '');
            $ec->hr();
            $ec->def('VRd', min($ec->VRdmax, $ec->VRds), 'V_(Rd) = min{(V_(Rd,max)),(V_(Rd,s)):} = %% [kN]', 'Nyírási teherbírás tervezési értéke');
        $ec->success1();

        $ec->h1('Hajlítási teherbírás');
        $ec->def('nE', H3::n1(($rebarMaterial->Es*1000)/($concreteMaterial->Eceff*1000)), 'n_E = E_s/E_(c,Eff) = %%', 'Betonacél és beton rugalmassái modulus aránya');
        $ec->h2('I. repedés mentes feszültség állapot');
        $ec->def('AiI', $ec->b*$ec->h + ($ec->bf-$ec->b)*$ec->hf + ($ec->nE-1)*$ec->Ast + ($ec->nE-1)*$ec->Asc, 'A_(i,I) = %% [mm^2]', 'Ideális keresztmetszeti terület');
        $ec->def('SiI', $ec->b*$ec->h*$ec->h/2 + ($ec->bf-$ec->b)*$ec->hf*$ec->hf/2 + ($ec->nE-1)*$ec->Asc*$ec->dsc + ($ec->nE-1)*$ec->Ast*$ec->dst, 'S_(i,I) = %% [mm^2]', 'Statikai nyomaték a felső (nyomott) szélső szálra');
        $ec->def('XiI', H3::n2($ec->SiI/$ec->AiI), 'X_(i,I) = S_(i,I)/A_(i,I) = %% [mm]', 'Semleges tengely távolsága a felső (nyomott) szélső száltól');
        $ec->def('IiI', $ec->b* ($ec->h ** 3) /12 + $ec->b*$ec->h* (($ec->h / 2 - $ec->XiI) ** 2) + ($ec->bf-$ec->b)* ($ec->hf ** 3) /12 + ($ec->bf-$ec->b)*$ec->hf* (($ec->XiI - $ec->hf / 2) ** 2) + ($ec->nE-1)*$ec->Ast* (($ec->dst - $ec->XiI) ** 2) + ($ec->nE-1)*$ec->Asc* (($ec->XiI - $ec->dsc) ** 2), 'I_(i,I) = %% [mm^4]', 'Tehetetlenségi inercia a semleges tengelyre');
        $ec->def('Mcr', H3::n2($concreteMaterial->fctm*$ec->IiI/($ec->h-$ec->XiI)/1000000), 'M_(cr) = f_(ctm)*I_(i,I)/(h-X_(i,I)) = %% [kNm]', 'Repesztő nyomaték értéke $f_(ctm)$-hez');
        $ec->def('sigmaccI', H3::n2(min(($ec->Mcr*1000000)/$ec->IiI*$ec->XiI, $concreteMaterial->fcd)), 'sigma_(c,c,I) = min{(M_(cr)/(I_(i,I)*X_(i,I))), (f_(cd)):} = %% [N/(mm^2)]', 'Beton nyomófeszültség a felső szélső szálban repesztőnyomatékból');
        $ec->def('sigmactI', (($ec->MEd*1000000)/$ec->IiI*($ec->h - $ec->XiI) <= 1.1*$concreteMaterial->fctm ? H3::n2(($ec->MEd*1000000)/$ec->IiI*($ec->h-$ec->XiI)) : 0), 'sigma_(c,t,I) = {(M_(Ed)/I_(i,I)*(h-X_(i,I)) le 1.1 f_(ctm) rarr M_(Ed)/I_(i,I)*(h-X_(i,I))), (0):} = %% [N/(mm^2)]', 'Beton húzófeszültség a felső szélső szálban repesztőnyomatékból');
        $ec->def('sigmastI', H3::n2($ec->nE*($ec->Mcr*1000000)/$ec->IiI*($ec->dst - $ec->XiI)), 'sigma_(s,t,I) = n_E*M_(cr)/I_(i,I)*(d_(s,t) - X_(i,I)) = %% [N/(mm^2)]', 'Feszültség az alsó húzott betonacélban repesztőnyomatékból');

        $ec->h2('II. berepedt (rugalmas) feszültség állapot');
        $ec->def('aII', $ec->b/2, 'a_(II) = b/2 = %%', 'Másodfokú megoldó képlet elemei');
        $ec->def('bII', ($ec->bf-$ec->b)*$ec->hf + ($ec->nE-1)*$ec->Asc + $ec->nE*$ec->Ast, 'b_(II) = (b_f-b)*h_f + (n_E-1)*A_(sc) + n_E*A_(st) = %%');
        $ec->def('cII', (-1)*($ec->bf-$ec->b)*($ec->hf**2)/2 - $ec->nE*$ec->Asc*$ec->dsc - $ec->nE*$ec->Ast*$ec->dst, 'c_(II) = - (b_f-b)*(h_f^2)/2 - n_E*A_(sc)*d_(sc) - n_E*A_(st)*d_(st) = %%');
//        $ec->def('XiII', ((-1)*$ec->bII + sqrt(($ec->bII**2) - 4*$ec->aII*$ec->cII))/(2*$ec->aII), '%%', 'Semleges tengely távolsága a felső (nyomott) szélső száltól'); // Berci féle megoldás
        $ec->def('XiII', $ec->chooseRoot($ec->XiI, $ec->quadratic($ec->aII, $ec->bII, $ec->cII, 2)), 'X_(i,II) = %% [mm]', 'Semleges tengely távolsága a felső (nyomott) szélső száltól');
        $ec->def('IiII', $ec->b*$ec->XiII**3/3 + ($ec->bf - $ec->b)*$ec->hf**3/12 + ($ec->bf-$ec->b)*$ec->hf*($ec->XiII-$ec->hf/2)**2 + $ec->nE*$ec->Ast*($ec->dst-$ec->XiII)**2 + ($ec->nE-1)*$ec->Asc*($ec->XiII-$ec->dsc)**2, 'I_(i,II) = %% [mm^4]', 'Tehetetlenségi inercia  asmeleges tengelyre');
        // TODO epssy is what?
//        $ec->def('kappaIIc', $ec->epscy/$ec->XiII, '%%', 'Görbület a II. feszültség állapot határhelyzetében a nyomott beton megfolyásának pillanatában');
//        $ec->def('kappaIIs', $ec->epssy/($ec->dst-$ec->XiII), '%%', 'Görbület a II. feszültség állapot határhelyzetében a húzott acél megfolyásának pillanatában');


        $ec->h1('Szerkesztési szabályok ellenőrzése');
        $ec->h4('Minimális húzott vasmennyiség:');
        $ec->def('rhoMin', max(0.26*($concreteMaterial->fctm/$rebarMaterial->fy), 0.0015), 'rho_(min) = max{(0.26*f_(ctm)/f_(yk)),(0.0015):} = max{(0.26*'.$concreteMaterial->fctm.'/'.$rebarMaterial->fy.'),(0.0015):} = %%', 'Minimális húzott vashányad');
        $ec->def('AstMin', H3::n0($ec->rhoMin*$ec->b*$ec->dst), 'A_(st,min) = rho_(min)*b*d = '.$ec->rhoMin.'*'.$ec->b.'*'.$ec->dst.' = %% [mm^2]', 'Előírt minimális húzott vasmennyiség négyszög keresztmetszet esetén');
        $ec->note('T szelvény esetén, ha a fejlemez nyomott, csak a gerinc szélességét kell `b` számításnál figyelembe venni, ha a fejlemez húzott, `b` a nyomott borda szélességének kétszerese.');
        $ec->label(($ec->AstMin/$ec->Ast>=1?'no':'yes'), $ec->Ast.' mm² = '. H3::n1(1/($ec->AstMin/$ec->Ast)*100) .' %');

        $ec->h4('Maximális összes vasmennyiség:');
        $ec->def('AsMax', 0.04*$ec->Ac, 'A_(s,max) = 0.04*A_c = %% [mm^2]', 'Összes hosszvasalás megengedett legnagyobb mennyisége');
        $ec->label(($ec->As/$ec->AsMax>=1?'no':'yes'), $ec->As.' mm² = '. H3::n1(($ec->As/$ec->AsMax)*100) .' %');

        $ec->h4('Nyírási acélhányad:');
        if ($ec->h > $ec->b) {
            $ec->def('rhowmin', max(0.08*sqrt($concreteMaterial->fck)/$rebarMaterial->fy, 0.001), 'rho_(w,min) = %%', 'Nyírási acélhányad minimális értéke');
        } else {
            $ec->def('rhowmin', (0.07 + 0.03*$ec->h/$ec->b)/100,'rho_(w,min) = %%');
        }
        $ec->def('rhow', $ec->Asw/$ec->b*0.001, 'rho_w = A_(sw)/b = %%');
        $uRhowMin = 'no';
        if ($ec->rhowmin/$ec->rhow <= 1) {
            $uRhowMin = 'yes';
        }
        $ec->label($uRhowMin, H3::n0($ec->rhow/$ec->rhowmin*100).'%-a a min. vashányadnak');

        $ec->h4('Egy sorban elhelyezhető vasak száma:');
        $ec->def('amin', max($ec->Dt, 20), 'a_(min) = max{(phi_t = '.$ec->Dt.'),(20):} = %% [mm]', 'Húzott betonacélok közötti min távolság');
        $ec->def('ntmax', floor(($ec->b - 2*$ec->cnom - 2*$ec->Dw + $ec->amin - 5)/((float)$ec->Dt + $ec->amin)), 'n_(t, max) = (b-2*c_(nom)-2*phi_w+a_(min)-5)/(phi_t + a_(min)) = %%', 'Egy sorban elhelyezhető vasak száma');
        $ec->note('Kengyelgörbület miatt 5mm-rel csökkentve a hely');
        if ($ec->nt > $ec->ntmax) {
            $ec->label('no', 'Túl nagy húzott vas szám');
        }

        $ec->h4('Nyírási kengyelek maximális távolsága:');
        $ec->def('s1max', min(0.5*$ec->dst,1.5*$ec->b,300), 's_(1,max) = %% [mm]');
        if ($ec->sw >= $ec->s1max) {
            $ec->label('no', $ec->sw.'mm &lt; '.$ec->s1max.' mm');
        }
    }
}
