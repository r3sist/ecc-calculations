<?php declare(strict_types = 1);
// Analysis of steel rigid end plate joint according to Eurocodes - Calculation class for ECC framework
// (c) Bence VÁNKOS | https://structure.hu | https://github.com/r3sist/ecc-calculations

namespace Calculation;

use \Base;
use \Ecc\Blc;
use \Ec\Ec;
use \H3;

Class JointEndplate
{
    protected Bolt $bolt;
    protected Weld $weld;

    public function __construct(Bolt $bolt, Weld $weld)
    {
        $this->bolt = $bolt;
        $this->weld = $weld;
    }

    public function calc(Base $f3, Blc $blc, Ec $ec): void
    {
        $blc->lst('type', ['Gerenda-gerenda' => 'gg', 'Oszlop-gerenda (keretsarok)' => 'og'], ['', 'Csomópont típusa'], 'gg', '');

        $blc->info0('Fogadó szelvény');
            $blc->lst('sectionFamily0', ['HEA', 'HEB', 'IPE'], ['', 'Szelvény típus'], 'HEA');
            $ec->sectionList($f3->_sectionFamily0, 'section0');
            $ec->spreadSectionData($f3->_section0, true, 'section0Data');
            $blc->def('t0f', $f3->_section0Data['tf']*10, 't_(0,f) = %% [mm]', 'Fogadó szelvény övvastagsága');
            $blc->def('W0pl', $f3->_section0Data['W1pl']*1000, 'W_(0,pl) = %% [mm^3]', 'Fogadó szelvény képlékeny fő keresztmetszeti modulusa');
        $blc->info1();

        $blc->info0('Csatlakozó szelvény');
            $blc->lst('sectionFamily1', ['HEA', 'HEB', 'IPE'], ['', 'Szelvény típus'], 'HEA');
            $ec->sectionList($f3->_sectionFamily1, 'section1');
            $ec->spreadSectionData($f3->_section1, true, 'section1Data');
            $blc->def('t1w', $f3->_section1Data['tw']*10, 't_(1,w) = %% [mm]', 'Csatlakozó szelvény gerincvastagsága');
        $blc->info1();



        $blc->note('A nyomóerő a keresztmetszeti ellenállás 5%-át nem haladhatja meg!');
        
//        $ec->matList('steelMaterialName', 'S235', ['', 'Szelvények, lemezek anyagminősége'], 'steel');
        $ec->structuralSteelMaterialListBlock('steelMaterialName', 'S235', ['', 'Szelvények, lemezek anyagminősége']);
        $ec->spreadMaterialData($f3->_steelMaterialName, 's');

//        $ec->matList('boltMaterialName', '8.8', ['', 'Csavarok anyagminősége'], 'bolt');
        $ec->boltMaterialListBlock('boltMaterialName', '8.8', ['', 'Csavarok anyagminősége']);
        $ec->spreadMaterialData($f3->_boltMaterialName, 'b');
        $ec->boltListBlock('boltName', 'M12');
        $blc->def('As', $ec->boltProp($f3->_boltName, 'As'), 'A_s = %% [mm^2]', 'Csavar húzási keresztmetszet');
        $f3->_d = $ec->boltProp($f3->_boltName, 'd');
        $blc->def('dm', $ec->boltProp($f3->_boltName, 'dm'), 'd_m = %% [mm]', 'Kigombolódási átmérő.');
        $blc->note('Kigombolódási átmérő számítható $1.6*d = '.$f3->_d*1.6.' [mm]$ képlettel is.');

        $blc->numeric('nrt', ['n_(r,t)', 'Huzott csavarsorok száma'], 1, '', '');

        $blc->h1('Egyszerűsített eljárás');
        $blc->note('*[Acélszerkezetek 2. Speciális eljárások (2007) 2.9. 28.o.]*');
        $blc->note('A méretezés alapja szerkesztési szabályok betartása; EC3-1-8 előírásain alapulva.');

        $blc->def('FtRd', \H3::n2($ec->FtRd($f3->_boltName, $f3->_boltMaterialName, true)), 'F_(t,Rd) = %% [kN]', 'Egy csavar húzási ellenállása');
        $blc->info0();
            $blc->def('tpminm', ceil(0.175*$ec->boltProp($f3->_boltName, 'd')*($f3->_bfu/$f3->_sfy)), 't_(p,min,m) ge 0.175*d*(f_(ub)/f_y) = %% [mm]', 'Homloklemezek javasolt vastagsága kigombolódás kizáráshoz');
        $blc->info1();

        $blc->numeric('etaj1', ['eta_(j,1) = M_(Ed)/M_(pl,Rd)', 'Kapcsolódó elemek nyomatéki kihasználtsága'], 0.9);

        $blc->note('$m$ a csavarnak a megtámasztástól való távolsága. Minimumra kell törekedni!');
        $blc->note('$e$ a csavarnak a lemez szélétől való távolsága. Maximumra kell törekedni! Jellemzően $e > m$');
        $blc->note('Csp. teherbírása a csavarátmérővel arányos, ezért feltételezett a lehető legnagyobb átmérőjű csavar alkalmazása.');

        $blc->def('ep', 5, 'e_p := %% [mm]', 'Homloklemez helyzete');
        $blc->numeric('w', ['w', 'Csavar oszlopok távolsága'], 60, 'mm', 'Homloklemez felső síkjától számítva');
        $blc->def('mopt', ceil(1.2*$f3->_d), 'm_(opt) = 1.2*d = %% [mm]', 'Javasolt csavar pozíció');
        $blc->def('m', ceil(($f3->_w - $f3->_t1w)/2), 'm = (w - t_(1,w))/2 = %% [mm]', 'Csavar megtámasztási viszony');
        $blc->numeric('e1', ['e_1', 'Felső csavarsor helyzete'], ceil(2.5*$f3->_d), 'mm', 'Homloklemez felső síkjától számítva. $e_1 :>= (2.2*m = '. 2.2*$f3->_m.' [mm])$');
        if ($f3->_e1 < 2.2*$f3->_m) {
            $blc->danger('$e_1 < (2.2*m = '. 2.2*$f3->_m.' [mm])$');
        }

        if ($f3->_nrt > 1) {
            $blc->def('popt', ceil(5*$f3->_m), 'p_(opt) = 5*m = %% [mm]', 'Javasolt csavarsor távolság');
            $blc->note('Ha a felső csavarsor a felső öv fölé kerül, javasolt $p_(opt) = 3*m$');
        }

        if ((int)$f3->_nrt === 1) {
            $blc->txt('Egy csavarsoros kialakítás.');

            $blc->def('dmin', ceil(2.9*$f3->_t0f*sqrt($f3->_sfy/$f3->_bfu)), 'd_(min) = 2.9*t_(0,f)*sqrt(f_y/f_(ub)) = %% [mm]', 'Övlemez teherbírásának megfelelő csavarátmérő');
            if ($f3->_dmin > $f3->_d) {
                $blc->danger('Csavarátmérő túl kicsi!');
            }

            $blc->def('zmin', $f3->_etaj1*($f3->_W0pl/(5*($f3->_t0f ** 2))), 'z_(min) = eta_(j1)* W_(0,pl)/(5*t_(0,f)^2 ) = %% [mm]', 'Oszlop teherbírás biztosítása - húzott és nyomott elemek távolsága.');
            $blc->note('Oszlop gerinc horpadásra ellenőrizendő (főleg HEA szelvények esetében).');
        }
    }
}
