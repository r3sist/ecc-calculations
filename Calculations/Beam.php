<?php

namespace Calculation;

Class Beam extends \Ecc
{

    /**
     * @var $f3 \Base
     * @var $blc \Blc
     * @var $ec \Ec\Ec
     * @throws \Exception
     */
    public function calc($f3, $blc, $ec)
    {
        $blc->toc();

        $blc->note('A számítások [Tóth Bertalan programja](https://structure.hu/berci/section) alapján történnek.');

        $blc->region0('material', 'Anyagminőségek megadása');
            $ec->matList('cMat', 'C30/37', 'Beton anyagminőség');
            $ec->saveMaterialData($f3->_cMat, 'c');
            $ec->matList('rMat', 'B500', 'Betonvas anyagminőség');
            $ec->saveMaterialData($f3->_rMat, 'r');
        $blc->region1('material');

        $blc->region0('geometry', 'Geometria megadása');
            $blc->numeric('h', 'Keresztmetszet teljes magassága', 700, 'mm', '');
            $blc->numeric('hf', '`h_f:` Keresztmetszet felső övének magassága', 150, 'mm', '');
            $blc->numeric('b', 'Keresztmetszet alsó szélessége', 250, 'mm', '');
            $blc->numeric('bf', '`b_f:` Keresztmetszet felső szélessége', 600, 'mm', '');
            $blc->info0('Ac');
                $blc->def('Ac', $f3->_hf*$f3->_bf + ($f3->_h - $f3->_hf)*$f3->_b, 'A_c = %% [mm^2]', 'Beton keresztmetszet területe');
            $blc->info1('Ac');
        $blc->region1('geometry');

        $blc->region0('reinforcement', 'Gerenda vasalás megadása');
            $blc->numeric('Ascdb', '`A_(sc):` Hosszirányú nyomott felső vasalás', 2, 'db', '');
            $blc->numeric('Ascfi', '`phi_(sc):` Hosszirányú nyomott felső vasalás átmérője', 20, 'mm', '');
            $blc->numeric('Astdb', '`A_(st):` Hosszirányú húzott alsó vasalás', 2, 'db', '');
            $blc->numeric('Astfi', '`phi_(st):` Hosszirányú húzott alsó vasalás átmérője', 20, 'mm', '');
            $blc->numeric('cnom', '`c_(nom):` Betonfedés', 25, 'mm', '');
            $blc->info0('As');
                $blc->def('Ast', \H3::n0($ec->A($f3->_Astfi, $f3->_Astdb)), 'A_(st) = %% [mm^2]', 'Alkalmazott húzott vasmennyiség');
                $blc->def('Asc', \H3::n0($ec->A($f3->_Ascfi, $f3->_Ascdb)), 'A_(sc) = %% [mm^2]', 'Alkalmazott nyomott vasmennyiség');
                $blc->def('As', \H3::n0($f3->_Ast + $f3->_Asc), 'A_s = %% [mm^2]', 'Alkalmazott összes vasmennyiség');
            $blc->info1('As');
            $dst = 0;
            if ($f3->_Astdb > 0) {
                $dst = $f3->_h - $f3->_cnom - max($f3->_Aswfi1, $f3->_Aswfi2) - $f3->_Astfi/2;
            }
            $blc->def('dst', $dst, 'd_(st) = %% [mm]', 'Húzott vasalás hasznos magassága');
        $blc->region1('reinforcement');

        $blc->region0('stirrup', 'Nyírási vasalás megadása');
            $f3->_Aswdb1 = 2;
            $f3->_Aswdb2 = 2;
            $blc->numeric('Aswfi1', '`phi_(sw,1):` Nyírási vasalás átmérője', 10, 'mm', 'Álló kengyelezés, 2 szárral');
            $blc->numeric('Asws1', '`s_(sw,1):` Nyírási vasalás kiosztása', 200, 'mm', '');
            $blc->numeric('Aswfi2', '`phi_(sw,2):` Nyírási vasalás átmérője', 0, 'mm', 'Másodlagos álló kengyelezés 2 szárral');
            $blc->numeric('Asws2', '`s_(sw,2):` Nyírási vasalás kiosztása', 200, 'mm', '');
            $blc->info0('Aw');
                $blc->def('Asw', \H3::n0(($f3->_Aswdb1*($ec->A($f3->_Aswfi1)/$f3->_Asws1) + $f3->_Aswdb2*($ec->A($f3->_Aswfi2)/$f3->_Asws2))*1000), 'A_(sw) = %% [mm^2]', 'Nyírási kengyel vasalás fajlagos keresztmetszeti területe');
            $blc->info1('Aw');
        $blc->region1('stirrup');

        $blc->numeric('MEd', '`M_(Ed)` Nyomatéki igénybevétel', 100, 'kNm');
        $blc->numeric('VEd', '`V_(Ed)` Nyíróerő igénybevétel', 500, 'kN');
        $blc->numeric('NEd', '`N_(Ed)` Normál igénybevétel', 0, 'kN');

        $blc->h1('Vasbeton keresztmetszet teherbírási számítása');
        $blc->def('d', $f3->_h - $f3->_cnom - max($f3->_Aswfi1, $f3->_Aswfi2) - $f3->_Astfi/2, 'd = h - c_(nom) - phi_(sw,max) - phi_(st)/2 = %% [mm]', 'Hatékony magasság');

        $blc->jsxDriver();
        $js = '
        var bf = '.$f3->_bf.';
        var hf = '.$f3->_hf.';
        var h = '.$f3->_h.';
        var b = '.$f3->_b.';
        var Astdb = '.$f3->_Astdb.';
        var Ascdb = '.$f3->_Ascdb.';
        var Astfi = '.$f3->_Astfi.';
        var Ascfi = '.$f3->_Ascfi.';
        var Aswfi1 = '.$f3->_Aswfi1.';
        var cnom = '.$f3->_cnom.';
        var abra = Math.max(1.5*bf/2, 1.5*h/2);
        var board = JXG.JSXGraph.initBoard("geom", {boundingbox:[-abra,abra+h/2,abra,-abra+h/2], axis: false, grid: false,
        keepaspectratio: true, showCopyright: false, showNavigation: false});
        var kota1= board.create("segment", [[-bf/2-30, 0.8*abra+h/2],[bf/2+30,0.8*abra+h/2]],{withLabel:true, name:bf, strokeColor:"black", label:{offset:[-10,8]}});
        board.create("ticks",[kota1,[30,bf+30]], {majorHeight:10, drawLabels: false});
        var kota2= board.create("segment", [[-b/2-30, -0.85*abra+h/2],[b/2+30,-0.85*abra+h/2]],{withLabel:true, name:b, strokeColor:"black", label:{offset:[-10,8]}});
        board.create("ticks",[kota2,[30,1*b+30]], {majorHeight:10, drawLabels: false});
        var kota3= board.create("segment", [[0.1*abra+bf/2, -30],[0.1*abra+bf/2,1*h+30]],{withLabel:true, name:h, strokeColor:"black", label:{offset:[3,0]}});
        board.create("ticks",[kota3,[30,1*h+30]], {majorHeight:10, drawLabels: false});
        var kota4= board.create("segment", [[-0.1*abra-bf/2, (h-hf)-30],[-0.1*abra-bf/2,1*h+30]],{withLabel:true, name:hf, strokeColor:"black", label:{offset:[-25,0]}});
        board.create("ticks",[kota4,[30,1*hf+30]], {majorHeight:10, drawLabels: false});
	
        //board.renderer.container.style.backgroundColor = "#fcfcf7"; // background color board
        //vb kontúr vonallánc:
        var geomX = [b/2,b/2,bf/2,bf/2,-bf/2,-bf/2,-b/2,-b/2,b/2];
        var geomY = [0,h-hf,h-hf,h,h,h-hf,h-hf,0,0];
        board.create("curve", [geomX,geomY],{strokeColor:"black",strokeWidth:1.5, fillColor:"#CCCCCC", fillOpacity:0.5, shadow:false});
        //alsó hosszvas ábrázolása:
        var i; 
        //var t=(b-2*cnom-2*Aswfi1-2*Astfi); 
        //var t1=t/(Astdb-1);
        for (i=0;i<Astdb;i++) 
            { 
            board.createElement("circle",
                [[i*(1*b-2*cnom-2*Aswfi1-2*Astfi)/(Astdb-1)-(1*b-2*cnom-2*Aswfi1-2*Astfi)/2,1*cnom+1*Aswfi1+Astfi/2],Astfi/2],
                {name:"",strokeWidth:1,fillColor:"blue", fillOpacity:0.5});
            }
        //felső hosszvas ábrázolása:
        var i;
        for (i=0;i<Ascdb;i++) 
            { 
            board.createElement("circle",
                [[i*(1*bf-2*cnom-2*Aswfi1-2*Ascfi)/(Ascdb-1)-(1*bf-2*cnom-2*Aswfi1-2*Ascfi)/2,h-(1*cnom+1*Aswfi1+Ascfi/2)],Ascfi/2],
                {name:"",strokeWidth:1,fillColor:"blue", fillOpacity:0.5});
            }
        
        
        //board.create("circle",[[AstX,200],Astfi/2], {name:"",strokeWidth:1,fillColor:"blue", fillOpacity:0.5});
        //board.create("circle",[[b/2-1*cnom-1*Aswfi1-Astfi/2,1*cnom+1*Aswfi1+Astfi/2],Astfi/2], {name:"",strokeWidth:1,fillColor:"blue", fillOpacity:0.5});
        //board.create("point",[b/2-1*cnom-1*Aswfi1-Astfi/2,1*cnom+1*Aswfi1+Astfi/2], {name:"", size:1, color:"blue"});
        //board.create("point",[-b/2+1*cnom+1*Aswfi1+Astfi/2,1*cnom+1*Aswfi1+Astfi/2], {name:"", size:1, color:"blue"});
        board.create("line",[[0,h-XiII],[1,h-XiII]], {strokeColor:"#00ff00",strokeWidth:1, dash:1, color:"red"});
        board.create("line",[[0,h-XiI],[1,h-XiI]], {strokeColor:"#00ff00",strokeWidth:1, dash:2, color:"green"});
	    ';
        $blc->jsx('geom', $js);

        $blc->h1('Szerkesztési szabályok ellenőrzése');
        $blc->h4('Minimális húzott vasmennyiség:');
        $blc->def('rhoMin', max(0.26*($f3->_cfctm/$f3->_rfy), 0.0015), 'rho_(min) = max{(0.26*f_(ctm)/f_(yk)),(0.0015):} = max{(0.26*'.$f3->_cfctm.'/'.$f3->_rfy.'),(0.0015):} = %%', 'Minimális húzott vashányad');
        $blc->def('AstMin', \H3::n0($f3->_rhoMin*$f3->_b*$f3->_d), 'A_(st,min) = rho_(min)*b*d = '.$f3->_rhoMin.'*'.$f3->_b.'*'.$f3->_d.' = %% [mm^2]', 'Előírt minimális húzott vasmennyiség négyszög keresztmetszet esetén');
        $blc->note('T szelvény esetén, ha a fejlemez nyomott, csak a gerinc szélességét kell `b` számításnál figyelembe venni, ha a fejlemez húzott, `b` a nyomott borda szélességének kétszerese.');
        $uAsMin = 'no';
        if ($f3->_AstMin/$f3->_Ast <= 1) {
            $uAsMin = 'yes';
        }
        $blc->label($uAsMin, \H3::n0($f3->_Ast/$f3->_AstMin*100).'%-a a min vasmennyiségnek');

        $blc->h4('Maximális összes vasmennyiség:');
        $blc->def('AsMax', 0.04*$f3->_Ac, 'A_(s,max) = 0.04*A_c = %% [mm^2]', 'Összes hosszvasalás megengedett legnagyobb mennyisége');
        $blc->label($f3->_As/$f3->_AsMax, '-a a max vasmennyiségnek');

        $blc->h4('Egy sorban elhelyezhető vasak száma:');
        $blc->def('amin', max($f3->_Astfi, 20), 'a_(min) = %% [mm]', 'Húzott betonacélok közötti min távolság');
        $blc->def('ntmax', floor(($f3->_b - 2*$f3->_cnom - 2*max($f3->_Aswfi1, $f3->_Aswfi2) + $f3->_amin - 5)/($f3->_Astfi + $f3->_amin)), 'n_(t, max) = %%', 'Egy sorban elhelyezhető vasak száma');
        $blc->note('Kengyelgörbület miatt 5mm-rel csökkentve a hely');
        $blc->label($f3->_Astdb/$f3->_ntmax, '-a a max vasak számának');

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
        $blc->label($uRhowMin, \H3::n0($f3->_rhow/$f3->_rhowmin*100).'%-a a min vashányadnak');

        $blc->h4('Nyírási kengyelek maximális távolsága:');
        $blc->def('s1max', min(0.5*$f3->_dst,1.5*$f3->_b,300), 's_(1,max) = %% [mm]');
        if (max($f3->_Asws1, $f3->_Asws2) >= $f3->_s1max) {
            $blc->label('no', max($f3->_Asws1, $f3->_Asws2).' &lt; '.$f3->_s1max);
        } else {
            $blc->label('yes', max($f3->_Asws1, $f3->_Asws2).' &gt; '.$f3->_s1max);
        }

        $blc->h4('Betonacél és beton rugalmassái modulus aránya:');
        $blc->def('nE', \H3::n1(($f3->_rEs*1000)/($f3->_cEceff*1000)), 'n_E = %%');

        $blc->h1('Nyírt keresztmetszet egyszerűsített számítása');
        $blc->note('[Vasbeton szerkezetek 6.3 (32.o)]');
        $blc->txt('Húzott vashányad meghatározása:');

        $blc->numeric('A_sl', 'Húzott vasalás vizsgált keresztmetszeten átvezetve', 0, 'mm²', '\`l_(bd) + d\`-vel túlvezetett húzott vasak vehetők figyelembe');
        $blc->note('`l_(bd)` a lehorgonyzási hossz tervezési értéke.');
        $blc->numeric('d', 'Keresztmetszet hatékony magasság', 200, 'mm');
        $blc->numeric('b_w', 'Keresztmetszet gerinc szélesség', 200, 'mm');
        $blc->def('rho_lcalc', min($f3->_A_sl/($f3->_b_w*$f3->_d), 0.02), 'rho_l = min(A_(sl)/(b_w*d), 0.02) = %%', 'Húzott vashányad');
        $blc->note('A húzott vashányad a biztonság javára való közelítéssel mindig lehet 0. Támasznál általában 0.');

        $rhos = [
            '0.00 %' => 0.00/100,
            '0.25 %' => 0.25/100,
            '0.50 %' => 0.50/100,
            '1.00 %' => 1.00/100,
            '2.00 %' => 2.00/100,
        ];
        $blc->lst('rho_l', $rhos, '`rho_(l,calc):` Húzott vashányad', 0);
        $blc->note('`V_(Rd,c) = c*b_w*d*f_(ctd)` képlethez `c(f_(ctd))` értékei meghatározhtaók táblázatosan. Dulácska biztonság javára történő közelítő képletével van itt számolva a `c`. [Világos kék [19]]');
        $c = (1.2 -  $f3->_cfck/150)*(0.15*$f3->_rho_l + 0.45/(1 + $f3->_d/1000));
        $blc->def('c', $c, 'c = (1.2 - f_(ck)/150)*(0.15*rho_l + 0.45/(1+d/1000)) = %%');

        $blc->success0('VRdc');
            $blc->def('VRdc', $c*$f3->_b_w*$f3->_d*$f3->_cfctd/1000, 'V_(Rd,c) = c*b_w*d*f_(ctd) = %% [kN]');
        $blc->success1('VRdc');




    }
}
