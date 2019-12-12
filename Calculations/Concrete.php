<?php
// Calculation class for ECC framework
// Concrete material related calculations according to Eurocodes
// (c) Bence VÁNKOS | https://structure.hu

namespace Calculation;

use \Base;
use \Ecc\Blc;
use \Ec\Ec;

Class Concrete
{
    public Base $f3;
    private Blc $blc;
    private Ec $ec;

    public function __construct(Base $f3, Blc $blc, Ec $ec)
    {
        $this->f3 = $f3;
        $this->blc = $blc;
        $this->ec = $ec;
    }

    public function helperCheckMinC(int $fckMin)
    {
        if ($fckMin > $this->f3->_fck) {
            $this->blc->label('no', 'Alacsony nyomószilárdsági osztály');
            $this->blc->txt('$f_(ck,min) = '.$fckMin.' [N/(mm^2)]$');
        }
    }

    public function moduleAnchorageLength(int $fi, float $rfyd, float $cfbd, float $alphaa = 1.0, int $nrequ = 1, int $nprov = 1): void
    {
        $f3 = $this->f3;
        $blc = $this->blc;

        $blc->def('al_lb', ceil(($fi/4)*($rfyd/$cfbd)), 'l_b = phi_l/4*f_(yd)/(f_(bd)) = %% [mm]', 'Lehorgonyzás alapértéke');
        $blc->def('al_lbeq', ceil($alphaa*$f3->_al_lb), 'l_(b,eq) = alpha_a*l_b = %% [mm]', 'Húzásra kihasznált betonacél lehorgonyzási hossza');
        $blc->def('al_lbmin', max(10*$fi, 100), 'l_(b,min) = max{(10*phi_l),(100):} = %% [mm]', 'Minimális lehorgonyzási hossz');
        $blc->def('al_lbd', ceil(max($f3->_al_lbeq*($nrequ/$nprov), $f3->_lbmin)), 'l_(b,d) = max{(l_(b,eq)*n_(requ)/n_(prov)),(l_(b,min)):} = %% [mm]', 'Lehorgonyzási hossz tervezési értéke, ahol $n_(requ)/n_(prov)$ a szükséges és biztosított vasak aránya');
        $blc->success('Lehorgonyzási hossz: $'.$f3->_al_lbd.' [mm]$');
    }

    public function calc(Base $f3, Blc $blc, Ec $ec): void
    {
        $blc->note('A számítások [Tóth Bertalan programja](https://structure.hu/berci/material) alapján történnek.');

        $ec->matList('concreteMaterialName', 'C25/30', 'Beton anyagminőség', 'concrete');
        $ec->saveMaterialData($f3->_concreteMaterialName, false);
        $blc->txt(false, 'A fent megadott anyagjellemzők a beton 28 napos korában érvényesek.');
        $blc->note('A szilárdsági osztályhoz tartozó jellemzők a 28 napos korban meghatározott, hengeren mért nyomószilárdság fck karakterisztikus értékén alapulnak.');

        $blc->h1('Lehorgonyzási hossz');
        $ec->matList('rebarMaterialName', 'B500', 'Betonvas anyagminőség', 'rebar');
        $ec->saveMaterialData($f3->_rebarMaterialName, 'r');
        $ec->rebarList('phil', 20, ['phi_l', 'Lehorgonyzandó vas átmérője']);
        $blc->numeric('nrequ', ['n_(requ)', 'Szükséges vas szál'], 1, '', '$A_(s,requ)$ szükséges vaskeresztmetszet helyett');
        $blc->numeric('nprov', ['n_(prov)', 'Biztosított vas szál'], 1, '', '$A_(s,prov)$ biztosított vaskeresztmetszet helyett');
        $blc->lst('alphaa', ['Egyenes: 1.0' => 1.0, 'Kampó, hurok, hajlítás: 0.7' => 0.7], ['alpha_a', 'Lehorgonyzás módja'], '1.0', '');
        $blc->txt('Anyagminőségnél **'.(($f3->_fbd07)?'rossz tapadás':'jó tapadás').'** ($f_(b,d) = '.$f3->_fbd.'[N/(mm^2)]$) van beállítva');
        $this->moduleAnchorageLength($f3->_phil, $f3->_rfyd, $f3->_fbd, $f3->_alphaa, $f3->_nrequ, $f3->_nprov);

        $blc->h1('Betonfedés');
        $blc->note('[ *Vasbetonszerkezetek* (2016) 8. 68.o. ]');
        $blc->region0('classification', 'Kitéti osztály választása');
            $blc->img('https://structure.hu/ecc/concreteClassification0.jpg');
        $blc->region1();
        $XC = [
            'X0 Száraz környezet' => 'X0',
            'XC1 Száraz vagy tartósan nedves helyen (állandóan víz alatt)' => 'XC1',
            'XC2 Nedves, ritkán száraz helyen (épület alapok)' => 'XC2',
            'XC3 Mérsékelten nedves helyen' => 'XC3',
            'XC4 Váltakozva nedves és száraz helyen' => 'XC4',
        ];

        $cmindurXC = 10;
        $blc->lst('XC', $XC,'**XC** Karbonátosodás okozta korrózió', 'XC1', '');
        switch ($f3->_XC) {
            case 'X0':
                $this->helperCheckMinC(12);
                $blc->note('X0: Pl. Belső száraz tér');
                break;
            case 'XC1':
                $this->helperCheckMinC(20);
                $cmindurXC = 15;
                $blc->note('XC1: Pl. Közepes légnedvesség tartalmú belső terek, víz alatti építmények');
                break;
            case 'XC2':
                $this->helperCheckMinC(25);
                $cmindurXC = 25;
                $blc->note('XC2: Pl. Víztározók, alapozási szerkezetek illetve nyitott csarnokok, gépkocsi tárolók, magas légnedvesség tartalmú belső terek');
                break;
            case 'XC3':
                $this->helperCheckMinC(30);
                $cmindurXC = 25;
                $blc->note('XC3: Pl. Víztározók, alapozási szerkezetek illetve nyitott csarnokok, gépkocsi tárolók, magas légnedvesség tartalmú belső terek');
                break;
            case 'XC4':
                $this->helperCheckMinC(30);
                $cmindurXC = 30;
                $blc->note('XC3: Pl. Esőnek kitett építményrészek');
                break;
        }
        $blc->def('cmindurXC', $cmindurXC, 'c_(min,dur,XC) = %% [mm]', '**XC** alapján');

        $XD = [
            'Nincs kitéve' => '-',
            'XD1 Mérsékelten nedves helyen, levegőből származó klorid' => 'XD1',
            'XD2 Nedves, ritkán száraz helyen, vízben lévő klorid' => 'XD2',
            'XD3 Váltakozva nedves és szárazhelyen, klorid permet' => 'XD3',
        ];
        $blc->lst('XD', $XD,'**XD** Nem tengervízből származó klorid által okozott korrózió', '-', '');
        $cmindurXD = 0;
        switch ($f3->_XD) {
            case '-':
                break;
            case 'XD1':
                $this->helperCheckMinC(30);
                $cmindurXD = 35;
                break;
            case 'XD2':
                $this->helperCheckMinC(30);
                $cmindurXD = 40;
                break;
            case 'XD3':
                $this->helperCheckMinC(35);
                $cmindurXD = 45;
                break;
        }
        $blc->def('cmindurXD', $cmindurXD, 'c_(min,dur,XD) = %% [mm]', '**XD** alapján');

        $blc->boo('XS2', '**XS2** Tengervíz állandó hatása', false);
        $f3->_cmindurXS2 = 0;
        if ($f3->_XS2) {
            $blc->def('cmindurXS2', 40, 'c_(min,dur,XS2) = %% [mm]', '**XS2** alapján');
            $this->helperCheckMinC(35);
        }

        $XF = [
            'Nincs kitéve' => '-',
            'XF1 Mérsékelt víztelítettségű, esőnek és fagynak kitett függőleges felület' => 'XF1',
            'XF2 Mérsékelt víztelítettségű, fagynak és jégolvasztó sók permetének kitett függőleges felület' => 'XF2',
            'XF3 Nagy víztelítettségű, esőnek és fagynak kitett vsz. felület' => 'XF3',
            'XF4 Mérsékelt víztelítettségű, fagynak és jégolvasztó sóknak kitett vsz. felület' => 'XF3',
        ];
        $blc->lst('XF', $XF,'**XF** Fagyási/olvadási korrózió jégolvasztó anyaggal vagy anélkül', '-', '');
        switch ($f3->_XF) {
            case '-':
                break;
            case 'XF1':
            case 'XF3':
            case 'XF4':
                $this->helperCheckMinC(30);
                break;
            case 'XF2':
                $this->helperCheckMinC(25);
                break;
        }

        $XA = [
            'Nincs kitéve' => '-',
            'XA1 Enyhén agresszív' => 'XA1',
            'XA2 Mérsékelten agresszív' => 'XA2',
            'XA3 Erősen agresszív' => 'XA3',
        ];
        $blc->lst('XA', $XA,'**XA** Kémiai környezet', '-', '');
        switch ($f3->_XF) {
            case '-':
                break;
            case 'XA1':
            case 'XA2':
                $this->helperCheckMinC(30);
                break;
            case 'XA3':
                $this->helperCheckMinC(35);
                break;
        }

        $ec->rebarList('fi', 20, ['phi', 'Fedendő vas']);
        $blc->def('cmindur', max($f3->_cmindurXS2, $f3->_cmindurXD, $f3->_cmindurXC), 'c_(min,dur) = %% [mm]');

        $blc->boo('y100', '50 évnél hosszabb tervezett élettartam', false);
        if ($f3->_y100) {
            $blc->def('cmindur', $f3->_cmindur + 10, 'c_(min,dur) + 10 [mm] = %% [mm]', '50 évnél hosszabb tervezett élettartam');
        }

        $blc->lst('erode', ['Nincs koptató hatás' => '0', 'Személyforgalom' => '5', 'Targonca' => '10', 'Teherautó' => '15'], 'Koptató hatás', '0');
        if ($f3->_erode != '0') {
            $erode = (int)$f3->_erode;
            $blc->def('cmindur', $f3->_cmindur + $erode, 'c_(min,dur) + '.$erode.' [mm] = %% [mm]', 'Koptató hatás');
            $blc->txt('**A koptatási '.$erode.' mm növekményt a lemezvastagságba a mértezésnél nem lehet figyelembe venni!**');
        }

        if ($f3->_fck > 30) {
            $blc->boo('red30', 'Minimum C35/45 beton figyelembevétele', false);
            if ($f3->_red30) {
                $blc->def('cmindur', $f3->_cmindur - 5, 'c_(min,dur) - 5 [mm] = %% [mm]', 'Min. C35/45 beton figyelembevétele');
            }
        }

        $blc->boo('redCor', 'Korrózióálló acél alkalmazása', false);
        if ($f3->_redCor) {
            $blc->def('cmindur', $f3->_cmindur - 5, 'c_(min,dur) - 5 [mm] = %% [mm]', 'Korrózióálló acél alkalmazása');
        }

        $blc->lst('earth', ['Nem talajra betonozás' => '0', 'Egyenetlen talajra betonozás' => '30', 'Előkészített talajra betonozás' => '5'], 'Talajra betonozás', '0');
        if ($f3->_earth != '0') {
            $earth = (int)$f3->_earth;
            $blc->def('cmindur', $f3->_cmindur + $earth, 'c_(min,dur) + '.$earth.' [mm] = %% [mm]', 'Talajra betonozás');
        }

        $blc->def('cnom', 10 + max($f3->_fi, $f3->_cmindur, 10), 'c_(nom) = 10 [mm] + max{(10 [mm]),((c_(min,b) := phi)),(c_(min,dur)):} = %% [mm]');

        $blc->boo('redPrecast', 'Minőségbiztosított előregyártás', false);
        if ($f3->_redPrecast) {
            $blc->def('cnom', $f3->_cnom - 5, 'c_(nom) - 5 [mm] = %% [mm]', 'Minőségbiztosított előregyártás');
        }

        $blc->boo('redSlabOrWall', 'Lemez- vagy falszerkezet', false);
        if ($f3->_redSlabOrWall) {
            $blc->def('cnom', $f3->_cnom - 5, 'c_(nom) - 5 [mm] = %% [mm]', 'Lemez- vagy falszerkezet');
        }

        $blc->success0();
            $blc->math('c_(nom) = '.$f3->_cnom.' [mm]');
        $blc->success1();


        $blc->h1('Beton jellemzői $t$ napos korban');
        $blc->numeric('t', ['t', 'Idő'], '10', 'nap', '');
        $cem = [
            'CEM 52,5 R' => 0.2,
            'CEM 42,5 R' => 0.2,
            'CEM 32,5 R' => 0.25,
            'CEM 52,5 N' => 0.2,
            'CEM 42,5 N' => 0.25,
            'CEM 32,5 N' => 0.38
        ];
        $blc->note('R: nagy kezdő szilárdság; N: normál kezdő szilárdság');
        $blc->lst('cem', $cem, ['', 'Cement típus'], 0.25, '', false);
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
