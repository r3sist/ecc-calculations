<?php

namespace Calculation;

Class Readme extends \Ecc
{

    public function calc($f3)
    {
        $blc = \Blc::instance();
        $ec = \Ec::instance();

        $text1 = file_get_contents($f3->BASE.'vendor/resist/ecc-calculations/README.md');
        $blc->md($text1);

        $blc->hr();
        $blc->h1('Annex', 'Autogenerated tests, lists and snippets');

        $blc->h2('Snippets');

        $blc->h3('JSXGraph charts');
        $blc->jsxDriver();
        $js = '
        var board = JXG.JSXGraph.initBoard("test", {boundingbox: [-5,40,5,-5], axis:true, showCopyright:false, keepaspectratio: false, showNavigation: true});
        var p = board.create("point",[1,5], {name:"1", face:"o", color:"green"});
        var p = board.create("point",[2,20], {name:"2", face:"o", color:"green"});
        var p = board.create("point",[3,35], {name:"?", face:"o", color:"red"});
        ';
        $blc->jsx('test', $js);
        $blc->pre('
$blc->jsxDriver();
$js = \'
var board = JXG.JSXGraph.initBoard("test", {boundingbox: [-5,40,5,-5], axis:true, showCopyright:false, keepaspectratio: false, showNavigation: true});
var p = board.create("point",[1,5], {name:"1", face:"o", color:"green"});
var p = board.create("point",[2,20], {name:"2", face:"o", color:"green"});
var p = board.create("point",[3,35], {name:"?", face:"o", color:"red"});
\';
$blc->jsx(\'test\', $js);
        ');

        $blc->h3('Google Charts based Lavacharts');
        $lava = new \Khill\Lavacharts\Lavacharts;
        $dataTableSlope = $lava->DataTable();

        $blc->pre('
// Initialize Lavacharts driver:
$lava = new \Khill\Lavacharts\Lavacharts;        
        ');

        $dataTableSlope->addNumberColumn('x')
            ->addNumberColumn('y')
            ->addRow([0, 0])
            ->addRow([100, 3]);
        $lava->LineChart('slope', $dataTableSlope, [
            'title' => 'lejtés',
            'legend' => ['position' => 'none'],
        ]);
        $blc->chart('LineChart', 'slope', $lava);
        $blc->pre('
// Line chart:
$dataTableSlope->addNumberColumn(\'x\')
            ->addNumberColumn(\'y\')
            ->addRow([0, 0])
            ->addRow([100, $f3->_slope]);
        $lava->LineChart(\'slope\', $dataTableSlope, [
            \'title\' => \'lejtés\',
            \'legend\' => [\'position\' => \'none\'],
        ]);
        $blc->chart(\'LineChart\', \'slope\', $lava);     
        ');

        $dataTable1 = $lava->DataTable();
        $dataTable1->addNumberColumn('x');
        $dataTable1->addNumberColumn('y');
        $dataTable1->addRow([1, 5]);
        $dataTable1->addRow([2, 20]);

        $dataTable2 = $lava->DataTable();
        $dataTable2->addNumberColumn('x');
        $dataTable2->addNumberColumn('y');
        $dataTable2->addRow([3, 35]);

        $tables = new \Khill\Lavacharts\DataTables\JoinedDataTable($dataTable1,$dataTable2);

        $lava->ScatterChart('3', $tables, ['legend' => ['position' => 'none'],'title' => 'Multiple dataTable scatter plot test',]);
        $blc->chart('ScatterChart', '3', $lava);

        $blc->pre('
// Scatter from two datasets        
$dataTable1 = $lava->DataTable();
$dataTable1->addNumberColumn(\'x\');
$dataTable1->addNumberColumn(\'y\');
$dataTable1->addRow([1, 5]);
$dataTable1->addRow([2, 20]);

$dataTable2 = $lava->DataTable();
$dataTable2->addNumberColumn(\'x\');
$dataTable2->addNumberColumn(\'y\');
$dataTable2->addRow([3, 35]);

$tables = new \Khill\Lavacharts\DataTables\JoinedDataTable($dataTable1,$dataTable2);

$lava->ScatterChart(\'3\', $tables, [\'legend\' => [\'position\' => \'none\'],\'title\' => \'Multiple dataTable scatter plot test\',]);
$blc->chart(\'ScatterChart\', \'3\', $lava);        
        ');

        $blc->h2('Bolt database');
        $blc->pre(json_encode($ec->data('bolt'), JSON_PRETTY_PRINT));

        $blc->h2('Material database');
        $blc->pre(json_encode($ec->data('mat'), JSON_PRETTY_PRINT));
    }
}
