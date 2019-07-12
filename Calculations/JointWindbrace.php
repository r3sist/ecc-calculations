<?php

namespace Calculation;

Class JointWindbrace extends \Ecc
{

    /**
     * @var $f3 \Base
     * @var $blc \Blc
     * @var $ec \Ec\Ec
     * @throws \Exception
     */
    public function calc(object $f3, object $blc, object $ec): void
    {
        $moduleWeld = new \Calculation\Weld();

        $ec->matList('mat', 'S235', 'Szelvények, lemezek anyagminősége');
        $blc->lst('forceSource', ['Bekötés szelvény húzásra' => 'h', 'Bekötés szelvény nyomásra' => 'n', 'Bekötés erőre' => 'e'], 'Erő megadása', 'h', '');

        switch ($f3->_forceSource) {
            case 'h':
            case 'n':
            $ec->sectionFamilyList('sectionFamily', 'Szelvény család', 'O');
            $ec->sectionList($f3->_sectionFamily, 'sectionName', 'Szelvény', 'O20');
            $ec->saveSectionData($f3->_sectionName, true, 'section');
                $blc->def('FEd', $ec->NplRd($f3->_section['Ax']*100, $f3->_mat, max($f3->_section['tw']/10, $f3->_section['tf']/10)), 'F_(Ed) := N_(pl,Rd) = (A*f_y)/gamma_(M,0) = %% [kN]', 'Teljes km. folyási ellenállása');
                break;
        }

        $blc->lst('connectionType', ['Csavaros bekötés' => 'b', 'Hegesztett bekötés' => 'w'], 'Kialakítás', 'b', 'Csavaros bekötés: *bekötő-* és *csomólemez* csavarral kapcsolva. Hegesztett bekötés: *bekötő-* és *csomólemez* M12 oválfuratos csavarral szerelve és összehegesztve.');
        switch ($f3->_connectionType) {
            case 'b':
                $ec->boltList('bName');
                $ec->matList('bMat', '8.8', 'Csavarok anyagminősége');
                $blc->def('d0', $ec->boltProp($f3->_bName, 'd0'),'d_0 = %% [mm]', 'Lyuk átmérő');
                $f3->_d = $ec->boltProp($f3->_bName, 'd');
                break;
            case 'w':
                $f3->_d0 = 13;
                break;
        }


        $blc->h1('$t_1$ bekötőlemez', 'Bázislemezhez hegesztve');
        $blc->note('A bázislemez ellenőrzését nem tartalmazza a számítás'); // TODO
        $blc->numeric('t1', ['t_1', 'Lemezvastagság'], 8, 'mm');
        $blc->numeric('L', ['L', 'Lemez szélesség'], 300, 'mm');
        $blc->boo('n1', ['n_1', 'Dupla bekötőlemez'], false, 'Nyírási síkok számához');
        $f3->_n1 = \V3::numeric($f3->_n1);

        $blc->h2('Varrat ellenőrzése bázislemezhez');
        $blc->numeric('a', ['a', 'Varrat gyökméret'], 4, 'mm');
        $f3->_w = 1;
        $blc->note('Kétoldalú sarokvarrattal számolva.');
        $f3->_F = $f3->_FEd/($f3->_n1 + 1);
        $blc->note('Dupla bekötőlemez az erőt felezi.');
        $f3->_t = $f3->_t1;
        $moduleWeld->moduleWeld($f3, $blc, $ec);

        $blc->h2('Ellenőrzés húzásra'); // TODO
        $blc->h2('Ellenőrzés nyomásra'); // TODO
        $blc->h2('Ellenőrzés palástnyomásra'); // TODO

        $blc->h1('$t_2$ csomólemez', 'Szélrácshoz hegesztve');
        $blc->boo('n2', ['n_2', 'Dupla csomólemez'], false, 'Nyírási síkok számához');
        $f3->_n2 = \V3::numeric($f3->_n2);
        $blc->h2('Varrat ellenőrzése szélrácshoz'); // TODO
        $blc->h2('Ellenőrzés húzásra'); // TODO
        $blc->h2('Ellenőrzés nyomásra'); // TODO
        $blc->h2('Ellenőrzés palástnyomásra'); // TODO

        $blc->h1('Csavarkép', ''); // TODO
    }
}
