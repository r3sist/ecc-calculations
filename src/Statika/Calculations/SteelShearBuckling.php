<?php declare(strict_types = 1);
// Calculation of buckling of plates under shear according to Eurocodes - Calculation class for ECC framework
// (c) Bence VÁNKOS |  https://structure.hu

namespace Calculation;

use \Base;
use \Ecc\Blc;
use \Ec\Ec;
use \H3;

Class SteelShearBuckling
{
    public function calc(Base $f3, Blc $blc, Ec $ec): void
    {
        $blc->note('[ *Acélszerkezetek 1. Általános eljárások* (2007) 5.5.], [ *Acélszerkezetek méretezése Eurocode 3 szerint - Gyakorlati útmutató* jegyzet (2009) 3.14. példa 42. o. ]');

        $blc->numeric('VEd', ['V_(Ed)', 'Nyíróerő'], 10, 'kN', '');
//        $ec->matList('smat', 'S235', ['', 'Lemez alapanyag'], 'S');
        $ec->structuralSteelMaterialListBlock('smat', 'S235', ['', 'Lemez alapanyag']);
        $ec->spreadMaterialData($f3->_smat, 's');
        $blc->numeric('hw', ['h_w', 'Nyírt lemez magassága'], 100, 'mm', '');
        $blc->numeric('tw', ['t_w', 'Nyírt lemez vastagsága'], 6, 'mm', '');
        $blc->note('Téglalap lemezmező vizsgálatát tárgyalja a szabvány: 10° eltérés lehet az oldalak párhuzamosságában. Lyukgyengítés 5% lehet maximum.');
        $blc->numeric('a', ['a', 'Nyírt zóna szélessége'], 500, 'mm', '');
        $blc->def('hwptw', H3::n2($f3->_hw/$f3->_tw), 'h_w/t_w = %%');
        $blc->def('alpha', H3::n2($f3->_a/$f3->_hw), 'alpha = a/h_w = %%', 'Arányszám');
        $blc->boo('stiffened', ['', 'Merevített lemezmező'], false, 'Gerinclemezek vizsgálata');
        $blc->def('epsilon', sqrt(235/$f3->_sfy), 'epsilon = sqrt(235/f_y) = %%');
        $blc->def('eta', 1.20, 'eta = %%', '');
        if ($f3->_alpha < 1.0) {
            $ktau = 4 + (5.34/pow($f3->_alpha, 2));
        } else {
            $ktau = 5.34 + (4/pow($f3->_alpha, 2));
        }
        $blc->def('ktau', $ktau, 'k_(tau) = {(4 + 5.34/alpha^2, "ha " alpha lt 1), (5.34 + 4/alpha^2, "ha " alpha ge 1):} = %%', 'Nyírási horpadási tényező');
        unset($ktau);
        $blc->note('Módosító tényező, EC3 szerint 1.20');
        if (!$f3->_stiffened) {
            $blc->txt('Merevítettlen lemez vizsgálata:');
            $cond = (float) H3::n2(72*$f3->_epsilon/$f3->_eta);
            $blc->txt('Vizsgálat nem szükséges, ha: $(h_w/t_w = '.$f3->_hwptw.') le (72*epsilon/eta = '.$cond.')$');
            if ($f3->_hwptw <= $cond) {
                unset($cond);
                $blc->label('yes', 'Nyírási horpadás nem mértékadó');
            } else {
                $blc->label('no', 'Nyírási horpadás mértékadó');
            }
        } else {
            $blc->txt('Merevített lemez vizsgálata:');
            $cond = (float) H3::n2(31*$f3->_epsilon*sqrt($f3->_ktau)/$f3->_eta);
            $blc->txt('Vizsgálat nem szükséges, ha: $(h_w/t_w = '.$f3->_hwptw.') le (31*epsilon*sqrt(k_(tau))/eta = '.$cond.')$');
            if ($f3->_hwptw <= $cond) {
                unset($cond);
                $blc->label('yes', 'Nyírási horpadás nem mértékadó');
            } else {
                $blc->label('no', 'Nyírási horpadás mértékadó');
            }
        }

        $blc->h1('Nyírási horpadási ellenállás');
        $blc->note('Ha a horpadás mértékadó, merev keresztbordákkal a zóna csökkentendő. Ellenkező esetben a horpadási ellenállás ellenőrizendő.');
        $blc->note('A nyírási horpadási ellenállás meghatározása a horpadás utáni tartalék figyelembevételével történik: a nyírt gerinclemezt az övek és a keresztbordák támasztják meg, azaz a gerinc és az övek teherbírási hozzájárulásának összege.');
        $blc->note('Az övek teherbírási hozzájárulását a lenti számítás elhanyagolja!');

        $blc->def('taucr', $f3->_ktau* ((pi()*pi()*210_000)/(12*(1-0.09))) *pow($f3->_tw/$f3->_hw,2), 'tau_(cr) = k_(tau)*(pi^2*E)/(12*(1-nu^2))*(t_w/h_w)^2 = %% [N/(mm^2)]', 'Lemezmező kritikus nyírási feszültsége');
        $blc->def('lambdawa', sqrt(($f3->_sfy/sqrt(3))/$f3->_taucr), 'bar lambda_w = sqrt((f_y/sqrt(3))/tau_(cr)) = %%', 'Viszonyított nyírási horpadási karcsúság');
        $blc->def('lambdawa2', $f3->_hw/(86.4*$f3->_tw*$f3->_epsilon), 'bar lambda_(w,2) = h_w/(86.4*t_w*epsilon) = %%', 'Viszonyított nyírási horpadási karcsúság egyszerűsített módszer általános magasépítési szerkezetekhez');
        $blc->lst('anchoring', ['merev' => '=|=|=', 'nem merev' => '=|='], ['', 'Véglehorgonyzás típusa'], '=|=', 'Merev pl. dupla borda tartóvégen');
        switch ($f3->_anchoring) {
            case '=|=|=':
                $chiw = 0;
                if ($f3->_lambdawa < 0.83/$f3->_eta) {
                    $chiw = $f3->_eta;
                } elseif ($f3->_lambdawa >= 0.83/$f3->_eta && $f3->_lambdawa < 1.08) {
                    $chiw = 0.83/$f3->_lambdawa;
                } elseif ($f3->_lambdawa >= 1.08) {
                    $chiw = 1.37/(0.7+$f3->_lambdawa);
                }
                $blc->def('chiw', $chiw, 'chi_w = {(eta, "ha " bar lambda_w lt 0.83/eta), (0.83/bar lambda_w, "ha " 0.83/eta le bar lambda_w lt 1.08), (1.37/(0.7+bar lambda_w), "ha " 1.08 le bar lambda_w):} = %%', 'Horpadási csökkentő tényező');
                break;
            case '=|=':
                $chiw = 0;
                if ($f3->_lambdawa < 0.83/$f3->_eta) {
                    $chiw = $f3->_eta;
                } elseif ($f3->_lambdawa >= 0.83/$f3->_eta) {
                    $chiw = 0.83/$f3->_lambdawa;
                }
                $blc->def('chiw', $chiw, 'chi_w = {(eta, "ha " bar lambda_w lt 0.83/eta), (0.83/bar lambda_w, "ha " 0.83/eta le bar lambda_w):} = %%', 'Horpadási csökkentő tényező');
                break;
        }
        $blc->def('VbwRd', H3::n2(($f3->_chiw*$f3->_sfy*$f3->_hw*$f3->_tw)/(sqrt(3)*$f3->__GM1)/1000), 'V_(b,w,Rd) = (chi_w*f_y*h_w*t_w)/(sqrt(3)*gamma_(M1)) = %% [kN]');
        $blc->info0();
            $blc->def('VbRd', $f3->_VbwRd, 'V_(b,Rd) = V_(b,w,Rd) + [V_(b,f,Rd) := 0] = %% [kN]');
            $blc->label($f3->_VEd/$f3->_VbRd, 'kihasználtság');
        $blc->info1();
        $blc->def('VplRd', H3::n2($ec->VplRd($f3->_tw*$f3->_hw, $f3->_smat, $f3->_tw)), 'V_(pl,Rd) = %% [kN]', '1 és 2. kmo.-ként számolt '.$f3->_tw.'×'.$f3->_hw.' km. összehasonlító nyírási ellenállása');

        $blc->h1('Közbenső keresztbordák méretezése');
        $blc->numeric('b', ['b', 'Borda szélesség'], 50, 'mm');
        $blc->numeric('t', ['t', 'Borda vastagság'], 6, 'mm');
        $blc->def('aeff', H3::n1(15*$f3->_tw*$f3->_epsilon), 'a_(eff) = 15*t_w*epsilon = %% [mm]', 'Effektív merevített hossz borda egy oldalán');
        $blc->h2('Merevség');
        $blc->lst('calcI', ['Egy oldali borda, eff. T km.' => 'T', 'Két oldali borda, egyszerűsített I km.' => 'I'], ['', 'Borda kialakítás'], 'I');
        function IT(float $H, float $h, float $B, float $b): float
        {
            // https://www.amesweb.info/SectionalPropertiesTabs/SectionalPropertiesTbeam.aspx
            // https://calcresource.com/moment-of-inertia-tee.html
            $A = $B*$h + $H*$b;
            $y = (($H + $h/2)*$h*$B + $H*$H*$b/2)/$A;
            return $b*$H*(pow($y - $H/2, 2)) + ($b*(pow($H, 3)))/12 + $h*$B*(pow($H + $h/2 - $y, 2)) + (pow($h, 3)*$B)/12;
        }
        switch ($f3->_calcI) {
            case 'T':
                $blc->def('I', H3::n0(IT($f3->_b, $f3->_tw, 2*$f3->_aeff+$f3->_t, $f3->_t)), 'I_T = %% [mm^4]', 'Merevített km. inerciája egyoldali bordával');
                $blc->note('[  [Forrás1](https://www.amesweb.info/SectionalPropertiesTabs/SectionalPropertiesTbeam.aspx), [Forrás2](https://calcresource.com/moment-of-inertia-tee.html) ]');
                break;
            case 'I':
                $blc->def('I', H3::n0($f3->_t*pow($f3->_b*2+$f3->_tw, 3)/12), 'I = (t*(2*b+t_w)^3)/12 = %% [mm^4]', 'Merevített km. inerciája egyoldali bordával');
                break;
        }
        if ($f3->_a/$f3->_hw < sqrt(2)) {
            $Imin = (1.5*pow($f3->_hw, 3)*pow($f3->_tw, 3))/pow($f3->_a, 2);
        } else {
            $Imin = 0.75*$f3->_hw*pow($f3->_tw, 3);
        }
        $blc->def('Imin', $Imin, 'I_(mi n) = {((1.5*h_w^3*t_w^3)/a^2 " ha "a/h_w lt sqrt(2) ) , (0.75*h_w*t_w^3 ) :} = %% [mm^4]');
        $blc->label($Imin/$f3->_I, 'inercia *kihasználtság*');

        $blc->h2('Keresztmetszet kihajlása');
        $blc->def('NEd', max(0, $f3->_VEd - ((1/pow($f3->_lambdawa, 2))*(($f3->_sfy*$f3->_hw*$f3->_tw)/(sqrt(3)*$f3->__GM1)))/1000), 'N_(Ed) = V_(Ed) - 1/lambda_w^2 * (f_y*t_w*h_w)/(sqrt(3)*gamma_(M1)) = %% [kN]', 'Km. ellenőrzése nyomóerőre, $0.75*h_w$ kihajlási hossz és *c* görbe alkalmazásával');
        $blc->txt('`Kézzel ellenőrizendő`');

        $blc->h1('Nyírási horpadás kölcsönhatása más tönkremeneteli módokkal');
        $blc->txt('`TODO`');
    }
}
