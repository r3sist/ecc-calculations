<?php

namespace Calculation;

Class Grider extends \Ecc
{

    /**
     * @var $f3 \Base
     * @var $blc \Blc
     * @var $ec \Ec\Ec
     * @throws \Exception
     */
    public function calc(object $f3, object $blc, object $ec): void
    {
        $blc->note('KG számításai alapján.');
        $blc->region0('mat', 'Anyagminőségek');
            $ec->matList('cmat', 'C40/50', 'Beton:');
            $ec->saveMaterialData($f3->_cmat, 'c');
            $blc->def('gamma', 25, 'gamma := %% [(kN)/m^3]', 'Beton térfogatsúlya');
            $ec->matList('rmat', 'B500', 'Lágyvas');
            $ec->saveMaterialData($f3->_rmat, 'r');
            $blc->input('pmat', 'Feszítőbetét jele', 'Fp100-R2', '', '');
        $blc->region1('mat');

        $blc->region0('geometry', 'Geometria');
            $griderTypes = ['Párhuzamos övű' => 0, 'Lejtett felső övű' => 1];
            $blc->lst('griderType', $griderTypes, 'Tartó típus', 0, '');
            $f3->_1l = 0;
            if ($f3->_griderType == 1) {
                $blc->numeric('1l', ['', 'Övek közti lejtés'], 3, '%', 'Középtől szélek felé egyenletesen süllyedő lejtés felső övön');
            }
            $blc->numeric('l', ['l', 'Tartó teljes hossza'], 12.0, 'm', '');
            $griderSections = ['Négyszög' => 'r', 'T' => 't', 'I' => 'i'];
            $blc->lst('griderSection', $griderSections, 'Tartó keresztmetszet', 't', '');
            $blc->numeric('h', ['h', 'Tartó teljes magassága'], 900, 'mm', 'Középső keresztmetszetben');
            if ($f3->_griderSection == 't' || $f3->_griderSection == 'i') {
                $blc->numeric('b_ft', ['b_(ft)', 'Fejlemez szélessége'], 500, 'mm', '');
                $blc->numeric('h_ft', ['h_(ft)', 'Fejlemez magassága'], 150, 'mm', '');
                $blc->numeric('h_ht', ['h_(ht)', 'Fejkiékelés magassága'], 30, 'mm', '');
            } else {
                $f3->_b_ft = 0;
                $f3->_h_ft = 0;
                $f3->_h_ht = 0;
            }

            if ($f3->_griderSection == 'i') {
                $blc->numeric('b_fb', ['b_(fb)', 'Alsó öv szélessége'], 400, 'mm', '');
                $blc->numeric('h_fb', ['h_(fb)', 'Alsó öv magassága'], 300, 'mm', '');
                $blc->numeric('h_hb', ['h_(hb)', 'Alsó öv kiékelés magassága'], 30, 'mm', '');
            } else {
                $f3->_b_fb = 0;
                $f3->_h_fb = 0;
                $f3->_h_hb = 0;
            }
            $blc->numeric('b_w', ['b_w', 'Gerincvastagság'], 160, 'mm', '');
            $blc->def('h_w', $f3->_h-($f3->_h_ft + $f3->_h_fb), 'h_w = %% [mm]', 'Gerinc magasság');
            $Act = $f3->_b_ft*$f3->_h_ft;
            $Acb = $f3->_b_fb*$f3->_h_fb;
            $Acw = $f3->_h_w*$f3->_b_w;
            $Acwt = (($f3->_b_ft - $f3->_b_w)/2)*$f3->_h_ht;
            $Acwb = (($f3->_b_fb - $f3->_b_w)/2)*$f3->_h_hb;
            $blc->def('A_c', $Act + $Acb + $Acw + $Acwt + $Acwb, 'A_c = %% [mm^2]', 'Keresztmetszeti terület');

            if ($f3->_griderType == 1) {
                $blc->def('h_0', $f3->_h - $f3->_l*1000*0.5*$f3->_1l/100, 'h_0 = %% [mm]', 'Lejtett tartó végső keresztmetszeti magassága');
                $blc->def('h_w0', $f3->_h_0 -($f3->_h_ft + $f3->_h_fb), 'h_(w,0) = %% [mm]', 'Lejtett tartó végső keresztmetszeti gerinc magassága');
                $Acw0 = $f3->_h_w0*$f3->_b_w;
                $blc->def('A_c0', $Act + $Acb + $Acw0 + $Acwt + $Acwb, 'A_(c,0) = %% [mm^2]', 'Lejtett tartó végső keresztmetszeti terület');
            } else {
                $f3->_A_c0 = $f3->_A_c;
            }
            $G = \H3::n2(($f3->_A_c + $f3->_A_c0)/2*$f3->_l*$f3->_gamma/1000000);
            $blc->def('G', $G, 'G = %% [kN]', 'Tartó súlya, $G = '.\H3::n2($G/10) .' [t]$');
        $blc->region1('geometry');

    }
}
