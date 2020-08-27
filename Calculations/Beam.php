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
            $ec->wrapRebarCount('Ascdb', 'Ascfi', '$A_(sc)$ Hosszirányú nyomott felső vasalás', 2, 20, '', 'Asc');
            $ec->wrapRebarCount('Astdb', 'Astfi', '$A_(st)$ Hosszirányú húzott alsó vasalás', 2, 20, '', 'Ast');
            $blc->def('As', H3::n0((float)$f3->_Ast + (float)$f3->_Asc), 'A_s = %% [mm^2] = '.H3::n1(((float)$f3->_Ast + (float)$f3->_Asc)/$f3->_Ac*100).' [%]', 'Alkalmazott összes vasmennyiség');
            $blc->hr();
            $ec->wrapRebarDistance('Asws1', 'Aswfi1', '$A_(sw)$ Kengyelezés vasalás kiosztása', 200, 10, '');
            $blc->input('Aswdb1', ['A_(swdb)', 'Kengyelszárak száma'], 2, '', '', 'numeric|min_numeric,1|max_numeric,10');
            $blc->def('Asw', H3::n0(($f3->_Aswdb1*($ec->A($f3->_Aswfi1)/$f3->_Asws1))*1000), 'A_(sw) = %% [(mm^2)/m]', 'Nyírási kengyel vasalás fajlagos keresztmetszeti területe');

            // Berci legacy bypass:
            $f3->_Aswdb2 = 2;
            $f3->_Aswfi2 = 0;
            $f3->_Asws2 = 0;
        $blc->info1();

        $dst = 0;
        if ($f3->_Astdb > 0) {
            $dst = $f3->_h - $f3->_cnom - $f3->_Aswfi1 - $f3->_Astfi/2;
        }
        $blc->def('dst', $dst, 'd_(st) = h - c_(nom) - phi_(sw) = %% [mm]', 'Húzott vasalás hasznos magassága');

        $blc->h1('Vasbeton keresztmetszet teherbírási számítása');
        $blc->note('Egy sor húzott vas figyelembe vétele.');
        $blc->def('d', $f3->_h - $f3->_cnom - max($f3->_Aswfi1, $f3->_Aswfi2) - $f3->_Astfi/2, 'd = h - c_(nom) - phi_(sw,max) - phi_(st)/2 = %% [mm]', 'Hatékony magasság');
        $blc->numeric('teta', ['theta', 'Nyomott rácsrúd dőlésszöge'], 38, '°', 'Javasolt érték $min 36[deg], 1 lt cot(theta) lt 2.5; 21.8[deg] lt theta lt 45[deg]$', 'min_numeric,21.8|max_numeric,45');

        $xs = 600; // SVG size
        $ys = 300;
        $x0 = 50; // Margin of figure
        $y0 = 50;
        $xc = $xs-2*$x0; // Figure canvas size
        $yc = $ys-2*$y0;
        $svg = new SVG($xs, $ys);
//        $svg->addBorder();
        $svg->makeRatio($xc, $yc, $f3->_bf, $f3->_h);
        $bfx = $f3->_bf - $f3->_b;
        // Contour
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
        $svg->setColor('#cccccc');
        $svg->setSize(12);
        $svg->addDimH(0, $f3->_bf, $y0/2, $f3->_bf, $x0);
        $svg->addDimH($bfx/2, $f3->_b, $ys-$y0/2, $f3->_b, $x0);
        $svg->addDimV(0, $f3->_h, $x0/2, $f3->_h, $y0);
        $svg->addDimV(0, $f3->_hf, $xc+$x0*1.5, $f3->_hf, $y0);
        // Sirrups
        $svg->setColor('green');
        $svg->addRectangle($bfx/2 + $f3->_cnom + $f3->_Aswfi1/2, $f3->_cnom + $f3->_Aswfi1/2, $f3->_b - 2*$f3->_cnom - $f3->_Aswfi1, $f3->_h - 2*$f3->_cnom - $f3->_Aswfi1, $x0, $y0);
        if ($f3->_sectionT) {
            $svg->addRectangle($f3->_cnom + $f3->_Aswfi1/2, $f3->_cnom + $f3->_Aswfi1/2, $f3->_bf - 2*$f3->_cnom - $f3->_Aswfi1, $f3->_hf - 2*$f3->_cnom - $f3->_Aswfi1, $x0, $y0);
        }
        // Rebars
        $svg->setColor('blue');

        $blc->svg($svg);


        $blc->h2('Nyírási teherbírás');
        $blc->region0('V');
            $blc->math('gamma_c = '.$f3->__Gc, 'Beton biztonsági tényező');
            $blc->def('CRdc', 0.18/$f3->__Gc, 'C_(Rd,c) = 0.18/gamma_c = %%');
            $blc->def('k', min(1 + sqrt(200/$f3->_dst), 2), 'k = min{(1 + sqrt(200/d_(st))), (2):} = %%');
            $blc->def('rho1', H3::n4(min($f3->_Ast/$f3->_b/$f3->_dst, 0.02)), 'rho_1 = {(A_(st)/b/d_(st)), (0.02):} = %%', 'Húzott acélhányad értéke, felülről korlátozva');
            $blc->math('b = '.$f3->_b.' [mm]', 'Keresztmetszet alsó szélessége');
            $blc->math('f_(ck) = '.$f3->_cfck.' [N/(mm^2)]', 'Nyomószilárdság karakterisztikus értéke (5% kvantilis) (□150×150×150 kocka)');
            $VRdc = ($f3->_CRdc*$f3->_k*((100*$f3->_rho1 * $f3->_cfck)**(1/3))*$f3->_b*$f3->_dst)/1000;
            $blc->def('VRdc', $VRdc, 'V_(Rdc) = C_(Rdc)*(100*rho_1*f_(ck))^(1/3)*b*d_(st) = %% [kN]', 'Keresztmetszet nyírási teherbírása');
        $blc->region1();
        $blc->success0();
            $blc->math('V_(Rd,c) = '. H3::n2($VRdc).' [kN]', 'Keresztmetszet nyírási teherbírása');
            $blc->label($f3->_VEd/$f3->_VRdc, 'kihasználtság');
        $blc->success1();

        $blc->h1('Szerkesztési szabályok ellenőrzése');
        $blc->h4('Minimális húzott vasmennyiség:');
        $blc->def('rhoMin', max(0.26*($f3->_cfctm/$f3->_rfy), 0.0015), 'rho_(min) = max{(0.26*f_(ctm)/f_(yk)),(0.0015):} = max{(0.26*'.$f3->_cfctm.'/'.$f3->_rfy.'),(0.0015):} = %%', 'Minimális húzott vashányad');
        $blc->def('AstMin', H3::n0($f3->_rhoMin*$f3->_b*$f3->_d), 'A_(st,min) = rho_(min)*b*d = '.$f3->_rhoMin.'*'.$f3->_b.'*'.$f3->_d.' = %% [mm^2]', 'Előírt minimális húzott vasmennyiség négyszög keresztmetszet esetén');
        $blc->note('T szelvény esetén, ha a fejlemez nyomott, csak a gerinc szélességét kell `b` számításnál figyelembe venni, ha a fejlemez húzott, `b` a nyomott borda szélességének kétszerese.');
        $uAsMin = 'no';
        if ($f3->_AstMin/$f3->_Ast <= 1) {
            $uAsMin = 'yes';
        }
        $blc->label($uAsMin, H3::n0($f3->_Ast/$f3->_AstMin*100).'%-a a min vasmennyiségnek');

        $blc->h4('Maximális összes vasmennyiség:');
        $blc->def('AsMax', 0.04*$f3->_Ac, 'A_(s,max) = 0.04*A_c = %% [mm^2]', 'Összes hosszvasalás megengedett legnagyobb mennyisége');
        $blc->label($f3->_As/$f3->_AsMax, '-a a max vasmennyiségnek');

        $blc->h4('Egy sorban elhelyezhető vasak száma:');
        $blc->def('amin', max($f3->_Astfi, 20), 'a_(min) = max{(phi_t = '.$f3->_Astfi.'),(20):} = %% [mm]', 'Húzott betonacélok közötti min távolság');
        $blc->def('ntmax', floor(($f3->_b - 2*$f3->_cnom - 2*max($f3->_Aswfi1, $f3->_Aswfi2) + $f3->_amin - 5)/((float)$f3->_Astfi + $f3->_amin)), 'n_(t, max) = %%', 'Egy sorban elhelyezhető vasak száma');
        $blc->note('Kengyelgörbület miatt 5mm-rel csökkentve a hely');
        $blc->label(($f3->_Astdb <= $f3->_ntmax?'yes':'no'), 'Vas szám');

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
        $blc->label($uRhowMin, H3::n0($f3->_rhow/$f3->_rhowmin*100).'%-a a min vashányadnak');

        $blc->h4('Nyírási kengyelek maximális távolsága:');
        $blc->def('s1max', min(0.5*$f3->_dst,1.5*$f3->_b,300), 's_(1,max) = %% [mm]');
        if (max($f3->_Asws1, $f3->_Asws2) >= $f3->_s1max) {
            $blc->label('no', max($f3->_Asws1, $f3->_Asws2).' &lt; '.$f3->_s1max);
        } else {
            $blc->label('yes', max($f3->_Asws1, $f3->_Asws2).' &gt; '.$f3->_s1max);
        }

        $blc->h4('Betonacél és beton rugalmassái modulus aránya:');
//        $blc->def('nE', \H3::n1(($f3->_rEs*1000)/($f3->_cEceff*1000)), 'n_E = %%');
        $f3->_nE = 1;
        $blc->txt('`TODO`');

        $blc->h1('Nyírt keresztmetszet egyszerűsített számítása');
        $blc->note('[Vasbeton szerkezetek 6.3 (32.o)]');
        $blc->txt('Húzott vashányad meghatározása:');

        $blc->numeric('A_sl', ['A_(sl)', 'Húzott vasalás vizsgált keresztmetszeten átvezetve'], 0, 'mm²', '$l_(bd) + d$ -vel túlvezetett húzott vasak vehetők figyelembe');
        $blc->note('$l_(bd)$ a lehorgonyzási hossz tervezési értéke.');
        $blc->numeric('d', ['d', 'Keresztmetszet hatékony magasság'], 200, 'mm');
        $blc->numeric('b_w', ['b_w', 'Keresztmetszet gerinc szélesség'], 200, 'mm');
        $blc->def('rho_lcalc', H3::n2(min($f3->_A_sl/($f3->_b_w*$f3->_d), 0.02)*100), 'rho_l = min(A_(sl)/(b_w*d), 0.02) = %% %', 'Húzott vashányad');
        $blc->note('A húzott vashányad a biztonság javára való közelítéssel mindig lehet 0. Támasznál általában 0.');

        $rhos = [
            '0.00 %' => 0.00/100,
            '0.25 %' => 0.25/100,
            '0.50 %' => 0.50/100,
            '1.00 %' => 1.00/100,
            '2.00 %' => 2.00/100,
        ];
        $blc->lst('rho_l', $rhos, ['rho_(l,calc)', 'Húzott vashányad'], 0);
        $blc->note('$V_(Rd,c) = c*b_w*d*f_(ctd)$ képlethez $c(f_(ctd))$ értékei meghatározhtaók táblázatosan. Dulácska biztonság javára történő közelítő képletével van itt számolva a $c$. [Világos kék [19]]');
        $c = (1.2 -  $f3->_cfck/150)*(0.15*$f3->_rho_l + 0.45/(1 + $f3->_d/1000));
        $blc->def('c', H3::n4($c), 'c = (1.2 - f_(ck)/150)*(0.15*rho_l + 0.45/(1+d/1000)) = %%');

        $blc->success0('VRdc');
            $blc->def('VRdc', H3::n2($c*$f3->_b_w*$f3->_d*$f3->_cfctd/1000), 'V_(Rd,c) = c*b_w*d*f_(ctd) = %% [kN]');
        $blc->success1();
    }
}
