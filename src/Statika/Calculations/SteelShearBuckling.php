<?php declare(strict_types = 1);
/**
 * Calculation of buckling of plates under shear according to Eurocodes - Calculation class for Statika framework
 * (c) Bence VÁNKOS | https://structure.hu | https://github.com/r3sist/ecc-calculations
 */

namespace Statika\Calculations;

use \H3;
use Statika\Ec;
use Statika\EurocodeInterface;

Class SteelShearBuckling
{
    private SteelSection $steelSection;

    public function __construct(SteelSection $steelSection)
    {
        $this->steelSection = $steelSection;
    }

    public function moduleIT(float $H, float $h, float $B, float $b): float
    {
        // https://www.amesweb.info/SectionalPropertiesTabs/SectionalPropertiesTbeam.aspx
        // https://calcresource.com/moment-of-inertia-tee.html
        $A = $B*$h + $H*$b;
        $y = (($H + $h/2)*$h*$B + $H*$H*$b/2)/$A;
        return $b*$H*(($y - $H / 2) ** 2) + ($b*($H ** 3))/12 + $h*$B*(($H + $h / 2 - $y) ** 2) + (($h ** 3) *$B)/12;
    }

    /**
     * @param Ec $ec
     * @throws \Exception
     */
    public function calc(EurocodeInterface $ec): void
    {
        $ec->note('[ *Acélszerkezetek 1. Általános eljárások* (2007) 5.5.], [ *Acélszerkezetek méretezése Eurocode 3 szerint - Gyakorlati útmutató* jegyzet (2009) 3.14. példa 42. o. ]');

        $ec->numeric('VEd', ['V_(Ed)', 'Nyíróerő'], 10, 'kN', '');

        $ec->structuralSteelMaterialListBlock('steelMaterialName', 'S235', ['', 'Lemez alapanyag']);
        $material = $ec->getMaterial($ec->steelMaterialName);

        $ec->numeric('hw', ['h_w', 'Nyírt lemez magassága'], 100, 'mm', '');
        $ec->numeric('tw', ['t_w', 'Nyírt lemez vastagsága'], 6, 'mm', '');
        $ec->note('Téglalap lemezmező vizsgálatát tárgyalja a szabvány: 10° eltérés lehet az oldalak párhuzamosságában. Lyukgyengítés 5% lehet maximum.');
        $ec->numeric('a', ['a', 'Nyírt zóna szélessége'], 500, 'mm', '');
        $ec->def('hwptw', H3::n2($ec->hw/$ec->tw), 'h_w/t_w = %%');
        $ec->def('alpha', H3::n2($ec->a/$ec->hw), 'alpha = a/h_w = %%', 'Arányszám');
        $ec->boo('stiffened', ['', 'Merevített lemezmező'], false, 'Gerinclemezek vizsgálata');
        $ec->def('epsilon', sqrt(235/$material->fy), 'epsilon = sqrt(235/f_y) = %%');
        $ec->def('eta', 1.20, 'eta = %%', '');
        if ($ec->alpha < 1.0) {
            $ktau = 4 + (5.34/ ($ec->alpha ** 2));
        } else {
            $ktau = 5.34 + (4/ ($ec->alpha ** 2));
        }
        $ec->def('ktau', $ktau, 'k_(tau) = {(4 + 5.34/alpha^2, leftarrow alpha lt 1), (5.34 + 4/alpha^2, leftarrow alpha ge 1):} = %%', 'Nyírási horpadási tényező');
        unset($ktau);
        $ec->note('Módosító tényező, EC3 szerint 1.20');
        if (!$ec->stiffened) {
            $ec->txt('Merevítettlen lemez vizsgálata:');
            $cond = H3::n2(72*$ec->epsilon/$ec->eta);
            $ec->txt('Vizsgálat nem szükséges, ha: $(h_w/t_w = '.$ec->hwptw.') le (72*epsilon/eta = '.$cond.')$');
            if ($ec->hwptw <= $cond) {
                unset($cond);
                $ec->label('yes', 'Nyírási horpadás nem mértékadó');
            } else {
                $ec->label('no', 'Nyírási horpadás mértékadó');
            }
        } else {
            $ec->txt('Merevített lemez vizsgálata:');
            $cond = H3::n2(31*$ec->epsilon*sqrt($ec->ktau)/$ec->eta);
            $ec->txt('Vizsgálat nem szükséges, ha: $(h_w/t_w = '.$ec->hwptw.') le (31*epsilon*sqrt(k_(tau))/eta = '.$cond.')$');
            if ($ec->hwptw <= $cond) {
                unset($cond);
                $ec->label('yes', 'Nyírási horpadás nem mértékadó');
            } else {
                $ec->label('no', 'Nyírási horpadás mértékadó');
            }
        }

        $ec->h1('Nyírási horpadási ellenállás');
        $ec->note('Ha a horpadás mértékadó, merev keresztbordákkal a zóna csökkentendő. Ellenkező esetben a horpadási ellenállás ellenőrizendő.');
        $ec->note('A nyírási horpadási ellenállás meghatározása a horpadás utáni tartalék figyelembevételével történik: a nyírt gerinclemezt az övek és a keresztbordák támasztják meg, azaz a gerinc és az övek teherbírási hozzájárulásának összege.');
        $ec->note('Az övek teherbírási hozzájárulását a lenti számítás elhanyagolja!');

        $ec->def('taucr', H3::n2($ec->ktau* ((pi()*pi()*210_000)/(12*(1-0.09))) * (($ec->tw / $ec->hw) ** 2)), 'tau_(cr) = k_(tau)*(pi^2*E)/(12*(1-nu^2))*(t_w/h_w)^2 = %% [N/(mm^2)]', 'Lemezmező kritikus nyírási feszültsége');
        $ec->def('lambdawa', sqrt(($material->fy/sqrt(3))/$ec->taucr), 'bar lambda_w = sqrt((f_y/sqrt(3))/tau_(cr)) = %%', 'Viszonyított nyírási horpadási karcsúság');
        $ec->def('lambdawa2', $ec->hw/(86.4*$ec->tw*$ec->epsilon), 'bar lambda_(w,2) = h_w/(86.4*t_w*epsilon) = %%', 'Viszonyított nyírási horpadási karcsúság egyszerűsített módszer általános magasépítési szerkezetekhez');
        $ec->lst('anchoring', ['merev' => '=|=|=', 'nem merev' => '=|='], ['', 'Véglehorgonyzás típusa'], '=|=', 'Merev pl. dupla borda tartóvégen');
        switch ($ec->anchoring) {
            case '=|=|=':
                $chiw = 0;
                if ($ec->lambdawa < 0.83/$ec->eta) {
                    $chiw = $ec->eta;
                } elseif ($ec->lambdawa >= 0.83/$ec->eta && $ec->lambdawa < 1.08) {
                    $chiw = 0.83/$ec->lambdawa;
                } elseif ($ec->lambdawa >= 1.08) {
                    $chiw = 1.37/(0.7+$ec->lambdawa);
                }
                $ec->def('chiw', $chiw, 'chi_w = {(eta, leftarrow bar lambda_w lt 0.83/eta), (0.83/bar lambda_w, leftarrow 0.83/eta le bar lambda_w lt 1.08), (1.37/(0.7+bar lambda_w), leftarrow 1.08 le bar lambda_w):} = %%', 'Horpadási csökkentő tényező');
                break;
            case '=|=':
                $chiw = 0;
                if ($ec->lambdawa < 0.83/$ec->eta) {
                    $chiw = $ec->eta;
                } elseif ($ec->lambdawa >= 0.83/$ec->eta) {
                    $chiw = 0.83/$ec->lambdawa;
                }
                $ec->def('chiw', $chiw, 'chi_w = {(eta, leftarrow bar lambda_w lt 0.83/eta), (0.83/bar lambda_w, leftarrow 0.83/eta le bar lambda_w):} = %%', 'Horpadási csökkentő tényező');
                break;
        }
        $ec->def('VbwRd', H3::n2(($ec->chiw*$material->fy*$ec->hw*$ec->tw)/(sqrt(3)*$ec::GM1)/1000), 'V_(b,w,Rd) = (chi_w*f_y*h_w*t_w)/(sqrt(3)*gamma_(M1)) = %% [kN]');
        $ec->info0();
            $ec->def('VbRd', $ec->VbwRd, 'V_(b,Rd) = V_(b,w,Rd) + [V_(b,f,Rd) := 0] = %% [kN]');
            $ec->label($ec->VEd/$ec->VbRd, 'kihasználtság');
        $ec->info1();
        $ec->def('VplRd', H3::n2($this->steelSection->moduleVplRd($ec->tw*$ec->hw, $ec->steelMaterialName, $ec->tw)), 'V_(pl,Rd) = %% [kN]', '1 és 2. kmo.-ként számolt '.$ec->tw.'×'.$ec->hw.' km. összehasonlító nyírási ellenállása');

        $ec->h1('Közbenső keresztbordák méretezése');
        $ec->numeric('b', ['b', 'Borda szélesség'], 50, 'mm');
        $ec->numeric('t', ['t', 'Borda vastagság'], 6, 'mm');
        $ec->def('aeff', H3::n1(15*$ec->tw*$ec->epsilon), 'a_(eff) = 15*t_w*epsilon = %% [mm]', 'Effektív merevített hossz borda egy oldalán');
        $ec->h2('Merevség');
        $ec->lst('calcI', ['Egy oldali borda, eff. T km.' => 'T', 'Két oldali borda, egyszerűsített I km.' => 'I'], ['', 'Borda kialakítás'], 'I');

        switch ($ec->calcI) {
            case 'T':
                $ec->def('I', H3::n0($this->moduleIT($ec->b, $ec->tw, 2*$ec->aeff+$ec->t, $ec->t)), 'I_T = %% [mm^4]', 'Merevített km. inerciája egyoldali bordával');
                $ec->note('[  [Forrás1](https://www.amesweb.info/SectionalPropertiesTabs/SectionalPropertiesTbeam.aspx), [Forrás2](https://calcresource.com/moment-of-inertia-tee.html) ]');
                break;
            case 'I':
                $ec->def('I', H3::n0($ec->t* (($ec->b * 2 + $ec->tw) ** 3) /12), 'I = (t*(2*b+t_w)^3)/12 = %% [mm^4]', 'Merevített km. inerciája egyoldali bordával');
                break;
        }
        if ($ec->a/$ec->hw < sqrt(2)) {
            $Imin = (1.5* ($ec->hw ** 3) * ($ec->tw ** 3))/ ($ec->a ** 2);
        } else {
            $Imin = 0.75*$ec->hw* ($ec->tw ** 3);
        }
        $ec->def('Imin', $Imin, 'I_(mi n) = {((1.5*h_w^3*t_w^3)/a^2, leftarrow a/h_w lt sqrt(2) ) , (0.75*h_w*t_w^3, ) :} = %% [mm^4]');
        $ec->label($Imin/$ec->I, 'inercia *kihasználtság*');

        $ec->h2('Keresztmetszet kihajlása');
        $ec->def('NEd', max(0, $ec->VEd - ((1/ ($ec->lambdawa ** 2))*(($material->fy*$ec->hw*$ec->tw)/(sqrt(3)*$ec::GM1)))/1000), 'N_(Ed) = V_(Ed) - 1/lambda_w^2 * (f_y*t_w*h_w)/(sqrt(3)*gamma_(M1)) = %% [kN]', 'Km. ellenőrzése nyomóerőre, $0.75*h_w$ kihajlási hossz és *c* görbe alkalmazásával');
        $ec->txt('`Kézzel ellenőrizendő`');

        $ec->h1('Nyírási horpadás kölcsönhatása más tönkremeneteli módokkal');
        $ec->txt('`TODO`');
    }
}
