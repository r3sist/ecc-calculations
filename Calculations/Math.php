<?php

namespace Calculation;

Class Math extends \Ecc
{

    /**
     * @var $f3 \Base
     * @var $blc \Blc
     * @var $ec \Ec\Ec
     * @throws \Exception
     */
    public function calc(object $f3, object $blc, object $ec): void
    {
        $lava = new \Khill\Lavacharts\Lavacharts;

        $blc->toc();

        $blc->h1('Vas keresztmetszet');
        $f3->_As = $ec->rebarTable('AS');
        $blc->math('A_s = '.$f3->_As.' [mm^2]');
        $blc->region0('rebars', 'Keresztmetszetek');
            $blc->math('phi_(8): '.floor($ec->A(8)).' [mm^2]');
            $blc->math('phi_(10): '.floor($ec->A(10)).' [mm^2]');
            $blc->math('phi_(12): '.floor($ec->A(12)).' [mm^2]');
            $blc->math('phi_(16): '.floor($ec->A(16)).' [mm^2]');
            $blc->math('phi_(20): '.floor($ec->A(20)).' [mm^2]');
            $blc->math('phi_(25): '.floor($ec->A(25)).' [mm^2]');
            $blc->math('phi_(28): '.floor($ec->A(25)).' [mm^2]');
            $blc->math('phi_(32): '.floor($ec->A(32)).' [mm^2]');
        $blc->region1('rebars', 'Keresztmetszetek');

        $blc->h1('Lejtés');
        $blc->numeric('slope', ['', 'Lejtés'], 3, '% / °', '');
        $slope_deg = rad2deg(atan($f3->_slope/100));
        $slope_per = tan(deg2rad($f3->_slope))*100;
        $blc->def('slope_deg', \H3::n2($slope_deg), $f3->_slope.'% = %% [deg]', '');
        $blc->def('slope_per', \H3::n2($slope_per), $f3->_slope.'[deg] = %% [%]', '');
        $blc->numeric('L', ['L', 'Hossz'], 10, 'm', '');
        $blc->def('hdeg', \H3::n2($f3->_L*$f3->_slope*0.01), 'h_('.$f3->_slope.'%) = %% [m]', 'Emelkedés ');
        $blc->def('hper', \H3::n2($f3->_L*$slope_per*0.01), 'h_('.\H3::n2($slope_per).'%) = %% [m]', 'Emelkedés');

        $blc->h1('Hőmérséklet rudakon');
        $blc->math('L = '.$f3->_L.'[m]', 'Rúdhossz');
        $blc->def('alpha_T_st', number_format(0.000012, 6), 'alpha_(T,steel) = %% [1/K]', '');
        $blc->numeric('DeltaT', ['Delta T', 'Hőmérséklet változás'], 40, 'deg', '');
        $blc->def('DeltaL', number_format($f3->_alpha_T_st*($f3->_L*1000)*$f3->_DeltaT, 2), 'Delta L = %% [mm]', '');

        $blc->h1('Lineáris interpoláció');
        $blc->numeric('x1', ['x_1', ''], 1, '', '');
        $blc->numeric('y1', ['y_1', ''], 5, '', '');
        $blc->numeric('x2', ['x_2', ''], 2, '', '');
        $blc->numeric('y2', ['y_2', ''], 20, '', '');
        $blc->info0('i0');
            $blc->numeric('x', ['x', ''], 3, '', '');
            $blc->def('y', (($f3->_x - $f3->_x1)*($f3->_y2 - $f3->_y1)/($f3->_x2 - $f3->_x1)) + $f3->_y1, 'y = %%');
        $blc->info1('i0');

        $dataTable1 = $lava->DataTable();
        $dataTable1->addNumberColumn('x');
        $dataTable1->addNumberColumn('y');
        $dataTable1->addRow([$f3->_x1, $f3->_y1]);
        $dataTable1->addRow([$f3->_x2, $f3->_y2]);
        $dataTable2 = $lava->DataTable();
        $dataTable2->addNumberColumn('x');
        $dataTable2->addNumberColumn('y');
        $dataTable2->addRow([$f3->_x, $f3->_y]);
        $tables = new \Khill\Lavacharts\DataTables\JoinedDataTable($dataTable1,$dataTable2);
        $lava->ScatterChart('3', $tables, ['legend' => ['position' => 'none'], 'title' => 'Lineáris interpoláció',]);
        $blc->chart('ScatterChart', '3', $lava);

        $blc->h1('Cső tömeg számítás');
        $blc->numeric('D', ['D', 'Cső külső átmérő'], 600, 'mm', '');
        $blc->numeric('t', ['t', 'Cső falvastagság'], 12, 'mm', '');
        $blc->def('d', $f3->_D - 2*$f3->_t, 'd = D- 2*t = %% [mm]', 'Belső átmérő');
        $blc->def('As', $ec->A($f3->_D) - $ec->A($f3->_d), 'A_(steel) = (D^2 pi)/4 - (d^2 pi)/4 = %% [mm^2]');
        $blc->def('Al', $ec->A($f3->_d), 'A_(liqu i d) = %% [mm^2]');
        $blc->def('gs', 78.5, 'gamma_(steel) = %% [(kN)/m^3]', '');
        $blc->numeric('gl', ['gamma_(liqu i d)', 'Folyadék fajsúly'], 10, 'kN/m3', '');
        $blc->def('qk', \H3::n3(($f3->_As/1000000)*$f3->_gs + ($f3->_Al/1000000)*$f3->_gl), 'q_k = A_(steel)*gamma_(sttel) + A_(liqu i d)*gamma_(liqu i d) = %% [(kN)/(fm)]');

        $blc->h1('CK- / Építőlap teherbírás');
        $blc->numeric('hck', ['h', 'Lap vastagság'], 16, 'mm', '');
        $blc->numeric('Lck', ['L', 'Fesztávolság'], 1300, 'mm', '');
        $blc->region0('ck', 'Részletek');
            $blc->numeric('gck', ['gamma', 'Lap fajsúly'], 14, 'kN/m3', '');
            $blc->numeric('sckk', ['sigma_k', 'Lap szilárdság'], 9, 'N/mm2', '');
            $blc->numeric('gM', ['gamma_M', 'Anyagi biztonsági tényező'], 1.2, false, '');
            $blc->def('sckd', $f3->_sckk/$f3->_gM, 'sigma_d = sigma_k/gamma_M = %% [N/(mm^2)]');
//            $blc->numeric('bck', ['b', 'Szélesség'], 1000, 'mm', '');
            $blc->def('bck', 1000, 'b = 1000 [mm]', 'Táblaszélesség');
            $blc->def('Iyck', ceil(($f3->_bck*pow($f3->_hck, 3))/12), 'I_y = (b*h^3)/12 = %% [mm^4]', 'Inercia');
            $blc->def('Wck', ceil(($f3->_bck*pow($f3->_hck, 2))/6), 'W = (b*h^2)/6 = %% [mm^3]', 'Keresztmetszeti modulus');
            $blc->def('gkck', \H3::n3(($f3->_gck*$f3->_hck)/1000), 'g_(0,k) = gamma*h = %% [(kN)/m^2]', 'Önsúly');
            $blc->numeric('gk', ['g_k', 'Rétegrendből adódó teher'], 0.5, 'kN/m2', '');
            $blc->numeric('qk', ['q_k', 'Hasznos teher'], 1, 'kN/m2', '');
            $blc->def('qd', \H3::n3(1.35*($f3->_gk + $f3->_gkck) + 1.5*$f3->_qk), 'q_d = 1.35*(g_(0,k) + g_k) + 1.5*q_k = %% [(kN)/m^2]', 'kN/m2', 'Összes teher');

        $blc->region1('ck');
        $blc->def('MRdck', \H3::n3(($f3->_sckd*$f3->_Wck)/1000000), 'M_(Rd) = sigma_d*W = %% [kNm]', 'Nyomatéki teherbírás');
        $blc->def('MEdck', \H3::n3(($f3->_qd*pow($f3->_Lck/1000, 2))/8), 'M_(Ed) = (q_d*L^2)/8 = %% [kNm]', 'Nyomatéki igénybevétel');
        $blc->label($f3->_MEdck/$f3->_MRdck, 'Kihasználtság');

        $blc->h1('Lehorgonyzási hossz');
        $ec->matList('concreteMaterialName', 'C25/30', 'Beton anyagminőség');
        $ec->saveMaterialData($f3->_concreteMaterialName, 'c');
        $ec->matList('rebarMaterialName', 'B500', 'Betonvas anyagminőség');
        $ec->saveMaterialData($f3->_rebarMaterialName, 'r');
        $ec->rebarList('phil', 20, ['phi_l', 'Lehorgonyzandó vas átmérője']);
        $blc->numeric('nrequ', ['n_(requ)', 'Szükséges vas szál'], 1, '', '$A_(s,requ)$ szükséges vaskeresztmetszet helyett');
        $blc->numeric('nprov', ['n_(prov)', 'Biztosított vas szál'], 1, '', '$A_(s,prov)$ biztosított vaskeresztmetszet helyett');
        $blc->lst('alphaa', ['Egyenes: 1.0' => 1.0, 'Kampó, hurok, hjlítás: 0.7' => 0.7], ['alpha_a', 'Lehorgonyzás módja'], '1.0', '');
        $blc->def('lb', ceil(($f3->_phil/4)*($f3->_rfyd/$f3->_cfbd)), 'l_b = phi_l/4*(f_(yd)/f_(bd)) = %% [mm]', 'Lehorgonyzás alapértéke');
        $blc->def('lbeq', ceil($f3->_alphaa*$f3->_lb), 'l_(b,eq) = alpha_a*l_b = %% [mm]', 'Húzásra kihasznált betonacél lehorgonyzási hossza');
        $blc->def('lbmin', max(10*$f3->_phil, 100), 'l_(b,min) = max{(10*phi_l),(100):} = %% [mm]', 'Minimális lehorgonyzási hossz');
        $blc->def('lbd', ceil(max($f3->_lbeq*($f3->_nrequ/$f3->_nprov), $f3->_lbmin)), 'l_(b,d) = max{(l_(b,eq)*n_(requ)/n_(prov)),(l_(b,min)):} = %% [mm]', 'Lehorgonyzási hossz tervezési értéke');
    }
}
