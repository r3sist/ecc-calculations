<?php

namespace Calculation;

Class Concrete extends \Ecc
{

    public function calc($f3)
    {
        $ec = \Ec::instance();
        $blc = \Blc::instance();

        $blc->note('A számítások [Tóth Bertalan programja](https://structure.hu/berci/material) alapján történnek.');

        $ec->matList('mat', 'C25/30', 'Beton anyag');
        $ec->saveMaterialData($f3->_mat, false);
        $blc->txt('', 'A fent megadott anyagjellemzők a beton 28 napos korában érvényesek.');
        $blc->note('A szilárdsági osztályhoz tartozó jellemzők a 28 napos korban meghatározott, hengeren mért nyomószilárdság fck karakterisztikus értékén alapulnak.');

        $blc->h1('Beton jellemzői `t` napos korban');
        $blc->input('t', 'Idő', '10', 'nap', '', false);
        $cem = [
            'CEM 52,5 R' => 0.2,
            'CEM 42,5 R' => 0.2,
            'CEM 32,5 R' => 0.25,
            'CEM 52,5 N' => 0.2,
            'CEM 42,5 N' => 0.25,
            'CEM 32,5 N' => 0.38
        ];
        $blc->note('R: nagy kezdő szilárdság; N: normál kezdő szilárdság');
        $blc->lst('cem', $cem, 'Cement típus', 0.25, '', false);
        $blc->def('betacc', exp($f3->_cem*(1-sqrt(28/$f3->_t))), 'beta_c = %%');
        $blc->def('fcmt', number_format($f3->_fcm*$f3->_betacc, 2), 'f_(c,m)(t) = beta_c*f_(c,m) = %% [N/(mm^2)]', 'Nyomószilárdság várható értéke');
        $y = $f3->_fck;
        if ($f3->_t < 28) {
            $y = ($f3->_fcmt - 8);
        }
        $blc->def('fckt', number_format($y, 2), 'f_(c,k)(t) = %% [N/(mm^2)]', 'Nyomószilárdság karakterisztikus értéke (5% kvantilis)');
        $y2 = 2/3;
        if ($f3->_t < 28) {
            $y2 = 1;
        }
        $blc->def('fctmt', number_format(pow($f3->_betacc, $y2)*$f3->_fctm, 2), 'f_(c,t,m)(t) = %% [N/(mm^2)]', 'Húzószilárdság várható értéke');
        $blc->def('fctk005t', number_format(0.7*$f3->_fctmt, 2), 'f_(c,t,k(0.05))(t) = 0.7*f_(c,t,m)(t) = %% [N/(mm^2)]', 'Húzószilárdság karakterisztikus értéke (5% kvantilis)');
        $blc->def('fctk095t', number_format(1.3*$f3->_fctmt, 2), 'f_(c,t,k(0.95))(t) = 1.3*f_(c,t,m)(t) = %% [N/(mm^2)]', 'Húzószilárdság karakterisztikus értéke (95% kvantilis)');
        $blc->def('Ecmt', number_format(pow($f3->_fcmt/$f3->_fcm, 0.3)*$f3->_Ecm, 2), 'E_(c,m)(t) = %% [(kN)/(mm^2)]', 'Rugalmassági modulus');

        $blc->jsxDriver();
        $js = '

var board = JXG.JSXGraph.initBoard("concrete", {boundingbox: [-3,70,40,-8], axis:true, showCopyright:false, keepaspectratio: false, showNavigation: true});

							var x = '.$f3->_fck.';
							var s = '.$f3->_cem.';
							//var t = '.$f3->_t.';
							fcmt=board.create("functiongraph", [function f(t){return Math.exp(s*(1-Math.sqrt(28/t)))*(1*x+8);},2], {withLabel: true, name:"fcm(t)", strokeColor:"grey"});
							fckt=board.create("functiongraph", [function f(t){ var y; if (t<28) { return y=(Math.exp(s*(1-Math.sqrt(28/t)))*(1*x+8)-8);} else {return y=x;}},2], {withLabel: true, name:"fck(t)", strokeColor:"blue"});
							q = board.create("glider", [28, 1*x, fckt], {name: "t; fck", withLabel: true});
							
							//board.create(\'text\', [
							//		  function(){ return q.X()+1; },
							//		  function(){ return q.Y()-5; },
							//		  function(){ return "(" + q.X().toFixed(1) + " , " + q.Y().toFixed(1) + ")"; }
							//	  ], 
							//	  {fontSize:12});
//							//p1=board.create("point", [[28,function() {return fckt.Y(28);}]],{name:"", color:"yellow", size:1});	
//							//p2=board.create("point", [28,0],{name:"", color:"yellow", size:1});
//							//p3=board.create("point", [[3,function() {return fckt.Y(3);}]],{name:"", size:1});
//							//p4=board.create("point", [3,0],{name:"", size:1});
							//p5=board.create(\'point\', [0,function() {return q.Y();}],{name:"", size:0.5});
							//p6=board.create(\'point\', [function() {return q.X();},0],{name:"", size:0.5});
							//board.create("line", [p1,p2],{strokeColor:"red", strokeWidth:0.2, dash:1});
							//board.create("line", [p3,p4],{strokeColor:"red", strokeWidth:0.2, dash:1});
							
							//board.create(\'line\', [p5,q],{strokeColor:"red", strokeWidth:2, dash:1});
							//board.create(\'line\', [q,p6],{strokeColor:"red", strokeWidth:2, dash:1});
						
';
        $blc->jsx('concrete', $js);
    }
}
