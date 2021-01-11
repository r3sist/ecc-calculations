<?php declare(strict_types = 1);
/**
 * Concrete material related calculations according to Eurocodes - Calculation class for Statika framework
 * (c) Bence VÁNKOS | https://structure.hu | https://github.com/r3sist/ecc-calculations
 */

namespace Statika\Calculations;

use \H3;
use Statika\EurocodeInterface;
use Statika\Material\MaterialDTO;

Class Concrete
{
    private EurocodeInterface $ec;

    /**
     * Concrete constructor.
     * @param Ec $ec
     */
    public function __construct(EurocodeInterface $ec)
    {
        $this->ec = $ec;
    }

    private function helperCheckMinC(int $fckMin, MaterialDTO $concreteMaterialDTO)
    {
        if ($fckMin > $concreteMaterialDTO->fck) {
            $this->ec->label('no', 'Alacsony nyomószilárdsági osztály');
            $this->ec->txt('$f_(ck,min) = '.$fckMin.' [N/(mm^2)]$');
        }
    }

    public function moduleAnchorageLength(float $fi, float $rebar_fyd, float $concrete_fbd, float $alphaa = 1.0, float $nrequ = 1, float $nprov = 1): void
    {
        $nrequ = (int)$nrequ;
        $nprov = (int)$nprov;

        $this->ec->def('al_lb', ceil(($fi/4)*($rebar_fyd/$concrete_fbd)), 'l_b = phi_l/4*f_(yd)/(f_(bd)) = %% [mm]', 'Lehorgonyzás alapértéke');
        $this->ec->def('al_lbeq', ceil($alphaa*$this->ec->al_lb), 'l_(b,eq) = alpha_a*l_b = %% [mm]', 'Húzásra kihasznált betonacél lehorgonyzási hossza');
        $this->ec->def('al_lbmin', max(10*$fi, 100), 'l_(b,min) = max{(10*phi_l),(100):} = %% [mm]', 'Minimális lehorgonyzási hossz');
        $this->ec->def('al_lbd', ceil(max($this->ec->al_lbeq*($nrequ/$nprov), $this->ec->al_lbmin)), 'l_(b,d) = max{(l_(b,eq)*n_(requ)/n_(prov)),(l_(b,min)):} = %% [mm]', 'Lehorgonyzási hossz tervezési értéke, ahol $n_(requ)/n_(prov)$ a szükséges és biztosított vasak aránya');
        $this->ec->success('Lehorgonyzási hossz: $'.$this->ec->al_lbd.' [mm]$');
    }

    /**
     * @param Ec $ec
     * @throws \Exception
     */
    public function calc(EurocodeInterface $ec): void
    {
        $ec->h1('Vas keresztmetszet');
            $ec->As = $ec->rebarTable('AS');
            $ec->math('A_s = '.$ec->As.' [mm^2]');
            $ec->region0('rebars', 'Keresztmetszetek');
            $ec->math('phi_(8): '.floor($ec->A(8)).' [mm^2]');
            $ec->math('phi_(10): '.floor($ec->A(10)).' [mm^2]');
            $ec->math('phi_(12): '.floor($ec->A(12)).' [mm^2]');
            $ec->math('phi_(16): '.floor($ec->A(16)).' [mm^2]');
            $ec->math('phi_(20): '.floor($ec->A(20)).' [mm^2]');
            $ec->math('phi_(25): '.floor($ec->A(25)).' [mm^2]');
            $ec->math('phi_(28): '.floor($ec->A(25)).' [mm^2]');
            $ec->math('phi_(32): '.floor($ec->A(32)).' [mm^2]');
        $ec->region1();

        $ec->h1('Beton anyagminőségek');
        $ec->concreteMaterialListBlock('concreteMaterialName');
        $concreteMaterial = $ec->getMaterial($ec->concreteMaterialName);
        $ec->txt('', 'A fent megadott anyagjellemzők a beton 28 napos korában érvényesek.');
        $ec->note('A szilárdsági osztályhoz tartozó jellemzők a 28 napos korban meghatározott, hengeren mért nyomószilárdság fck karakterisztikus értékén alapulnak.');

        $ec->h1('Lehorgonyzási hossz');
        $ec->rebarMaterialListBlock('rebarMaterialName');
        $rebarMaterial = $ec->getMaterial($ec->rebarMaterialName);
        $ec->rebarList('phil', 20, ['phi_l', 'Lehorgonyzandó vas átmérője']);
        $ec->numeric('nrequ', ['n_(requ)', 'Szükséges vas szál'], 1, '', '$A_(s,requ)$ szükséges vaskeresztmetszet helyett');
        $ec->numeric('nprov', ['n_(prov)', 'Biztosított vas szál'], 1, '', '$A_(s,prov)$ biztosított vaskeresztmetszet helyett');
        $ec->lst('alphaa', ['Egyenes: 1.0' => 1.0, 'Kampó, hurok, hajlítás: 0.7' => 0.7], ['alpha_a', 'Lehorgonyzás módja'], '1.0', '');
        if ($ec->__isset('fbd07')) {
            $ec->txt('Anyagminőségnél **'.(($concreteMaterial->fbd07)?'rossz tapadás':'jó tapadás').'** ($f_(b,d) = '.$concreteMaterial->fbd.'[N/(mm^2)]$) van beállítva');
        }
        $this->moduleAnchorageLength($ec->phil, $rebarMaterial->fyd, $concreteMaterial->fbd, $ec->alphaa, $ec->nrequ, $ec->nprov);


        // BETONFEDÉS //////////////////////////////////////////////////////////////////////////////////////////////////

        $ec->h1('Betonfedés');
        $ec->note('*[Vasbetonszerkezetek (2016) 8. 68.o.]* alapján. Fontos szabvány: *MSZ EN 4798:2016 NAD N1 táblázat*. Nem **S4** szerkezeti osztály esetén $c_(min,d)$ értékeit lásd: *[Betonszerkezetek méretezése az Eurocode alapján (2008) 3M3. melléklet]*');
        $ec->region0('classification', 'Kitéti osztály választása');
            $ec->img('https://structure.hu/ecc/concreteClassification0.jpg');
        $ec->region1();
        $XC = [
            'X0 Száraz környezet' => 'X0',
            'XC1 Száraz vagy tartósan nedves helyen (állandóan víz alatt)' => 'XC1',
            'XC2 Nedves, ritkán száraz helyen (épület alapok)' => 'XC2',
            'XC3 Mérsékelten nedves helyen' => 'XC3',
            'XC4 Váltakozva nedves és száraz helyen' => 'XC4',
        ];

        $cmindurXC = 10;
        $ec->lst('XC', $XC,['', '**XC** Karbonátosodás okozta korrózió'], 'XC1', '');
        switch ($ec->XC) {
            case 'X0':
                $this->helperCheckMinC(12, $concreteMaterial);
                $ec->note('X0: Pl. Belső száraz tér, max 35% relatív páratartalom');
                break;
            case 'XC1':
                $this->helperCheckMinC(20, $concreteMaterial);
                $cmindurXC = 15;
                $ec->note('XC1: Pl. Közepes légnedvesség tartalmú belső terek, víz alatti építmények');
                break;
            case 'XC2':
                $this->helperCheckMinC(25, $concreteMaterial);
                $cmindurXC = 25;
                $ec->note('XC2: Pl. Víztározók, alapozási szerkezetek illetve nyitott csarnokok, gépkocsi tárolók, magas légnedvesség tartalmú belső terek');
                break;
            case 'XC3':
                $this->helperCheckMinC(30, $concreteMaterial);
                $cmindurXC = 25;
                $ec->note('XC3: Pl. Víztározók, alapozási szerkezetek illetve nyitott csarnokok, gépkocsi tárolók, magas légnedvesség tartalmú belső terek, esőtől védett, szabadon álló betonszerkezetek');
                break;
            case 'XC4':
                $this->helperCheckMinC(30, $concreteMaterial);
                $cmindurXC = 30;
                $ec->note('XC3: Pl. Esőnek kitett építményrészek');
                break;
        }
        $ec->def('cmindurXC', $cmindurXC, 'c_(min,dur,XC) = %% [mm]', '**XC** alapján');

        $XD = [
            'Nincs kitéve' => '-',
            'XD1 Mérsékelten nedves helyen, levegőből származó klorid' => 'XD1',
            'XD2 Nedves, ritkán száraz helyen, vízben lévő klorid' => 'XD2',
            'XD3 Váltakozva nedves és szárazhelyen, klorid permet' => 'XD3',
        ];
        $ec->lst('XD', $XD,['', '**XD** Nem tengervízből származó klorid által okozott korrózió'], '-', '');
        $cmindurXD = 0;
        switch ($ec->XD) {
            case '-':
                break;
            case 'XD1':
                $this->helperCheckMinC(30, $concreteMaterial);
                $cmindurXD = 35;
                break;
            case 'XD2':
                $this->helperCheckMinC(30, $concreteMaterial);
                $cmindurXD = 40;
                break;
            case 'XD3':
                $this->helperCheckMinC(35, $concreteMaterial);
                $cmindurXD = 45;
                break;
        }
        $ec->def('cmindurXD', $cmindurXD, 'c_(min,dur,XD) = %% [mm]', '**XD** alapján');

        $ec->boo('XS2', ['', '**XS2** Tengervíz állandó hatása'], false);
        $ec->cmindurXS2 = 0;
        if ($ec->XS2) {
            $ec->def('cmindurXS2', 40, 'c_(min,dur,XS2) = %% [mm]', '**XS2** alapján');
            $this->helperCheckMinC(35, $concreteMaterial);
        }

        $XF = [
            'Nincs kitéve' => '-',
            'XF1 Mérsékelt víztelítettségű, esőnek és fagynak kitett függőleges felület' => 'XF1',
            'XF2 Mérsékelt víztelítettségű, fagynak és jégolvasztó sók permetének kitett függőleges felület' => 'XF2',
            'XF3 Nagy víztelítettségű, esőnek és fagynak kitett vsz. felület' => 'XF3',
            'XF4 Mérsékelt víztelítettségű, fagynak és jégolvasztó sóknak kitett vsz. felület' => 'XF3',
        ];
        $ec->lst('XF', $XF,['', '**XF** Fagyási/olvadási korrózió jégolvasztó anyaggal vagy anélkül'], '-', '');
        switch ($ec->XF) {
            case '-':
                break;
            case 'XF1':
            case 'XF3':
            case 'XF4':
                $this->helperCheckMinC(30, $concreteMaterial);
                break;
            case 'XF2':
                $this->helperCheckMinC(25, $concreteMaterial);
                break;
        }

        $XA = [
            'Nincs kitéve' => '-',
            'XA1 Enyhén agresszív' => 'XA1',
            'XA2 Mérsékelten agresszív' => 'XA2',
            'XA3 Erősen agresszív' => 'XA3',
        ];
        $ec->lst('XA', $XA,['', '**XA** Kémiai környezet'], '-', '');
        switch ($ec->XF) {
            case '-':
                break;
            case 'XA1':
            case 'XA2':
                $this->helperCheckMinC(30, $concreteMaterial);
                break;
            case 'XA3':
                $this->helperCheckMinC(35, $concreteMaterial);
                break;
        }

        $ec->rebarList('fi', 20, ['phi', 'Fedendő vas']);
        $ec->def('cmindur', max($ec->cmindurXS2, $ec->cmindurXD, $ec->cmindurXC), 'c_(min,dur) = %% [mm]');

        $ec->boo('y100', ['', '50 évnél hosszabb tervezett élettartam'], false);
        if ($ec->y100) {
            $ec->def('cmindur', $ec->cmindur + 10, 'c_(min,dur) + 10 [mm] = %% [mm]', '50 évnél hosszabb tervezett élettartam');
        }

        $ec->lst('erode', ['Nincs koptató hatás' => '0', 'Személyforgalom' => '5', 'Targonca' => '10', 'Teherautó' => '15'], ['', 'Koptató hatás'], '0');
        if ($ec->erode != '0') {
            $erode = (int)$ec->erode;
            $ec->def('cmindur', $ec->cmindur + $erode, 'c_(min,dur) + '.$erode.' [mm] = %% [mm]', 'Koptató hatás');
            $ec->txt('**A koptatási '.$erode.' mm növekményt a lemezvastagságba a mértezésnél nem lehet figyelembe venni!**');
        }

        if ($concreteMaterial->fck > 30) {
            $ec->boo('red30', ['', 'Minimum C35/45 beton figyelembevétele'], false);
            if ($ec->red30) {
                $ec->def('cmindur', $ec->cmindur - 5, 'c_(min,dur) - 5 [mm] = %% [mm]', 'Min. C35/45 beton figyelembevétele');
            }
        }

        $ec->boo('redCor', ['', 'Korrózióálló acél alkalmazása'], false);
        if ($ec->redCor) {
            $ec->def('cmindur', $ec->cmindur - 5, 'c_(min,dur) - 5 [mm] = %% [mm]', 'Korrózióálló acél alkalmazása');
        }

        $ec->lst('earth', ['Nem talajra betonozás' => '0', 'Egyenetlen talajra betonozás' => '30', 'Előkészített talajra betonozás' => '5'], ['', 'Talajra betonozás'], '0');
        if ($ec->earth != '0') {
            $earth = (int)$ec->earth;
            $ec->def('cmindur', $ec->cmindur + $earth, 'c_(min,dur) + '.$earth.' [mm] = %% [mm]', 'Talajra betonozás');
        }

        $ec->def('cnom', 10 + max($ec->fi, $ec->cmindur, 10), 'c_(nom) = 10 [mm] + max{(10 [mm]),((c_(min,b) := phi)),(c_(min,dur)):} = %% [mm]');

        $ec->boo('redPrecast', ['', 'Minőségbiztosított előregyártás'], false);
        if ($ec->redPrecast) {
            $ec->def('cnom', $ec->cnom - 5, 'c_(nom) - 5 [mm] = %% [mm]', 'Minőségbiztosított előregyártás');
        }

        $ec->boo('redSlabOrWall', ['', 'Lemez- vagy falszerkezet'], false);
        if ($ec->redSlabOrWall) {
            $ec->def('cnom', $ec->cnom - 5, 'c_(nom) - 5 [mm] = %% [mm]', 'Lemez- vagy falszerkezet');
        }

        $ec->success0();
            $ec->math('c_(nom) = '.$ec->cnom.' [mm]');
        $ec->success1();

        $ec->h1('Pecsétnyomás és kereszt irányú húzás');
        $ec->numeric('Tb', ['b', 'Elem szélesség'], 300, 'mm');
        $ec->numeric('Ta', ['a', 'Felfekvés szélessége'], 200, 'mm');
        $ec->numeric('TVEd', ['V_(Ed)', 'Bevezetett erő'], 100, 'kN');
        $ec->note('Központos eset számítása. Külpontos esetben a felső szakaszon is keletkezik húzóerő.');
        $ec->def('T', H3::n2(0.25*(1 - $ec->Ta/$ec->Tb)*$ec->TVEd), 'T = 0.25*(1- a/b)*V_(Ed) = %% [kN]');
        $ec->rebarList('Tfi', 10, ['', 'Vas átmérő']);
        $ec->def('TA', ceil($ec->T*1000/435), 'A_min = %% [mm^2]');
        $ec->def('Tn', ceil($ec->TA/$ec->A($ec->Tfi)), 'n = %%');


        $ec->h1('Beton jellemzői $t$ napos korban');
        $ec->note('A számítások [Tóth Bertalan programja](https://structure.hu/berci/material) alapján történnek.');
        $ec->numeric('t', ['t', 'Idő'], 10, 'nap', '');
        $cem = [
            'CEM 52,5 R' => 0.2,
            'CEM 42,5 R' => 0.2,
            'CEM 32,5 R' => 0.25,
            'CEM 52,5 N' => 0.2,
            'CEM 42,5 N' => 0.25,
            'CEM 32,5 N' => 0.38
        ];
        $ec->note('R: nagy kezdő szilárdság; N: normál kezdő szilárdság');
        $ec->lst('cem', $cem, ['', 'Cement típus'], 0.25, '');
        $ec->def('betacc', exp($ec->cem*(1-sqrt(28/$ec->t))), 'beta_c = %%');
        $ec->def('fcmt', number_format($concreteMaterial->fcm*$ec->betacc, 2), 'f_(c,m)(t) = beta_c*f_(c,m) = %% [N/(mm^2)]', 'Nyomószilárdság várható értéke');
        $y = $concreteMaterial->fck;
        if ($ec->t < 28) {
            $y = ($ec->fcmt - 8);
        }
        $ec->def('fckt', number_format($y, 2), 'f_(c,k)(t) = %% [N/(mm^2)]', 'Nyomószilárdság karakterisztikus értéke (5% kvantilis)');
        $y2 = 2/3;
        if ($ec->t < 28) {
            $y2 = 1;
        }
        $ec->def('fctmt', number_format(($ec->betacc ** $y2) *$concreteMaterial->fctm, 2), 'f_(c,t,m)(t) = %% [N/(mm^2)]', 'Húzószilárdság várható értéke');
        $ec->def('fctk005t', number_format(0.7*$ec->fctmt, 2), 'f_(c,t,k(0.05))(t) = 0.7*f_(c,t,m)(t) = %% [N/(mm^2)]', 'Húzószilárdság karakterisztikus értéke (5% kvantilis)');
        $ec->def('fctk095t', number_format(1.3*$ec->fctmt, 2), 'f_(c,t,k(0.95))(t) = 1.3*f_(c,t,m)(t) = %% [N/(mm^2)]', 'Húzószilárdság karakterisztikus értéke (95% kvantilis)');
        $ec->def('Ecmt', number_format((($ec->fcmt / $concreteMaterial->fcm) ** 0.3) *$concreteMaterial->Ecm, 2), 'E_(c,m)(t) = %% [(kN)/(mm^2)]', 'Rugalmassági modulus');

        $ec->jsxDriver();
        $js = '

var board = JXG.JSXGraph.initBoard("concrete", {boundingbox: [-3,70,40,-8], axis:true, showCopyright:false, keepaspectratio: false, showNavigation: true});

                            var x = '.$concreteMaterial->fck.';
                            var s = '.$ec->cem.';
                            //var t = '.$ec->t.';
                            fcmt=board.create("functiongraph", [function f(t){return Math.exp(s*(1-Math.sqrt(28/t)))*(1*x+8);},2], {withLabel: true, name:"fcm(t)", strokeColor:"grey"});
                            fckt=board.create("functiongraph", [function f(t){ var y; if (t<28) { return y=(Math.exp(s*(1-Math.sqrt(28/t)))*(1*x+8)-8);} else {return y=x;}},2], {withLabel: true, name:"fck(t)", strokeColor:"blue"});
                            q = board.create("glider", [28, 1*x, fckt], {name: "t; fck", withLabel: true});
                            
                            //board.create(\'text\', [
                            //          function(){ return q.X()+1; },
                            //          function(){ return q.Y()-5; },
                            //          function(){ return "(" + q.X().toFixed(1) + " , " + q.Y().toFixed(1) + ")"; }
                            //      ], 
                            //      {fontSize:12});
//                            //p1=board.create("point", [[28,function() {return fckt.Y(28);}]],{name:"", color:"yellow", size:1});    
//                            //p2=board.create("point", [28,0],{name:"", color:"yellow", size:1});
//                            //p3=board.create("point", [[3,function() {return fckt.Y(3);}]],{name:"", size:1});
//                            //p4=board.create("point", [3,0],{name:"", size:1});
                            //p5=board.create(\'point\', [0,function() {return q.Y();}],{name:"", size:0.5});
                            //p6=board.create(\'point\', [function() {return q.X();},0],{name:"", size:0.5});
                            //board.create("line", [p1,p2],{strokeColor:"red", strokeWidth:0.2, dash:1});
                            //board.create("line", [p3,p4],{strokeColor:"red", strokeWidth:0.2, dash:1});
                            
                            //board.create(\'line\', [p5,q],{strokeColor:"red", strokeWidth:2, dash:1});
                            //board.create(\'line\', [q,p6],{strokeColor:"red", strokeWidth:2, dash:1});
                        
';
        $ec->jsx('concrete', $js);
    }
}
