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

        $blc->h1('Lejtés');
        $blc->numeric('slope', ['', 'Lejtés'], 3, '% / °', '');
        $slope_deg = rad2deg(atan($f3->_slope/100));
        $slope_per = tan(deg2rad($f3->_slope))*100;
        $blc->def('slope_deg', number_format($slope_deg, 2), $f3->_slope.'% = %% [deg]', '');
        $blc->def('slope_deg', number_format($slope_per, 2), $f3->_slope.'° = %% [%]', '');

        $blc->h1('Hőmérséklet rudakon');
        $blc->def('alpha_T_st', number_format(0.000012, 6), 'alpha_(T,steel) = %% [1/K]', '');
        $blc->numeric('L', ['L', 'Rúdhossz'], 10, 'm', '');
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
    }
}
