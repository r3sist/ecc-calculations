<?php declare(strict_types = 1);
/**
 * Analysis of steel rigid end plate joint according to Eurocodes - Calculation class for Statika framework
 * (c) Bence VÁNKOS | https://structure.hu | https://github.com/r3sist/ecc-calculations
 */

namespace Statika\Calculations;

use Statika\Ec;
use Statika\EurocodeInterface;

Class JointEndplate
{
    protected Bolt $bolt;
    protected Weld $weld;

    public function __construct(Bolt $bolt, Weld $weld)
    {
        $this->bolt = $bolt;
        $this->weld = $weld;
    }

    /**
     * @param Ec $ec
     * @throws \Profil\Exceptions\InvalidSectionNameException
     */
    public function calc(EurocodeInterface $ec): void
    {
        $ec->lst('type', ['Gerenda-gerenda' => 'gg', 'Oszlop-gerenda (keretsarok)' => 'og'], ['', 'Csomópont típusa'], 'gg', '');

        $ec->info0('Fogadó szelvény');
            $ec->lst('sectionFamily0', ['HEA', 'HEB', 'IPE'], ['', 'Szelvény típus'], 'HEA');
            $ec->sectionListBlock($ec->sectionFamily0, 'sectionName0');
            $section0 = $ec->getSection($ec->sectionName0);
            $ec->def('t0f', $section0->_tf*10, 't_(0,f) = %% [mm]', 'Fogadó szelvény övvastagsága');
            $ec->def('W0pl', $section0->_W1pl*1000, 'W_(0,pl) = %% [mm^3]', 'Fogadó szelvény képlékeny fő keresztmetszeti modulusa');
        $ec->info1();

        $ec->info0('Csatlakozó szelvény');
            $ec->lst('sectionFamily1', ['HEA', 'HEB', 'IPE'], ['', 'Szelvény típus'], 'HEA');
            $ec->sectionListBlock($ec->sectionFamily1, 'sectionName1');
            $section1 = $ec->getSection($ec->sectionName1);
            $ec->def('t1w', $section1->_tw*10, 't_(1,w) = %% [mm]', 'Csatlakozó szelvény gerincvastagsága');
        $ec->info1();



        $ec->note('A nyomóerő a keresztmetszeti ellenállás 5%-át nem haladhatja meg!');

        $ec->structuralSteelMaterialListBlock('steelMaterialName', 'S235', ['', 'Szelvények, lemezek anyagminősége']);
        $steelMaterial = $ec->getMaterial($ec->steelMaterialName);

        $ec->boltMaterialListBlock('boltMaterialName', '8.8', ['', 'Csavarok anyagminősége']);
        $boltMaterial = $ec->getMaterial($ec->boltMaterialName);

        $ec->boltListBlock('boltName', 'M12');
        $ec->def('As', $ec->getBolt($ec->boltName)->As, 'A_s = %% [mm^2]', 'Csavar húzási keresztmetszet');
        $ec->d = $ec->getBolt($ec->boltName)->d;
        $ec->def('dm', $ec->getBolt($ec->boltName)->dm, 'd_m = %% [mm]', 'Kigombolódási átmérő.');
        $ec->note('Kigombolódási átmérő számítható $1.6*d = '.$ec->d*1.6.' [mm]$ képlettel is.');

        $ec->numeric('nrt', ['n_(r,t)', 'Huzott csavarsorok száma'], 1, '', '');

        $ec->h1('Egyszerűsített eljárás');
        $ec->note('*[Acélszerkezetek 2. Speciális eljárások (2007) 2.9. 28.o.]*');
        $ec->note('A méretezés alapja szerkesztési szabályok betartása; EC3-1-8 előírásain alapulva.');

        $ec->def('FtRd', \H3::n2($this->bolt->moduleFtRd($ec->boltName, $ec->boltMaterialName, true)), 'F_(t,Rd) = %% [kN]', 'Egy csavar húzási ellenállása');
        $ec->info0();
            $ec->def('tpminm', ceil(0.175*$ec->getBolt($ec->boltName)->d*($boltMaterial->fu/$steelMaterial->fy)), 't_(p,min,m) ge 0.175*d*(f_(ub)/f_y) = %% [mm]', 'Homloklemezek javasolt vastagsága kigombolódás kizáráshoz');
        $ec->info1();

        $ec->numeric('etaj1', ['eta_(j,1) = M_(Ed)/M_(pl,Rd)', 'Kapcsolódó elemek nyomatéki kihasználtsága'], 0.9);

        $ec->note('$m$ a csavarnak a megtámasztástól való távolsága. Minimumra kell törekedni!');
        $ec->note('$e$ a csavarnak a lemez szélétől való távolsága. Maximumra kell törekedni! Jellemzően $e > m$');
        $ec->note('Csp. teherbírása a csavarátmérővel arányos, ezért feltételezett a lehető legnagyobb átmérőjű csavar alkalmazása.');

        $ec->def('ep', 5, 'e_p := %% [mm]', 'Homloklemez helyzete');
        $ec->numeric('w', ['w', 'Csavar oszlopok távolsága'], 60, 'mm', 'Homloklemez felső síkjától számítva');
        $ec->def('mopt', ceil(1.2*$ec->d), 'm_(opt) = 1.2*d = %% [mm]', 'Javasolt csavar pozíció');
        $ec->def('m', ceil(($ec->w - $ec->t1w)/2), 'm = (w - t_(1,w))/2 = %% [mm]', 'Csavar megtámasztási viszony');
        $ec->numeric('e1', ['e_1', 'Felső csavarsor helyzete'], ceil(2.5*$ec->d), 'mm', 'Homloklemez felső síkjától számítva. $e_1 :>= (2.2*m = '. 2.2*$ec->m.' [mm])$');
        if ($ec->e1 < 2.2*$ec->m) {
            $ec->danger('$e_1 < (2.2*m = '. 2.2*$ec->m.' [mm])$');
        }

        if ($ec->nrt > 1) {
            $ec->def('popt', ceil(5*$ec->m), 'p_(opt) = 5*m = %% [mm]', 'Javasolt csavarsor távolság');
            $ec->note('Ha a felső csavarsor a felső öv fölé kerül, javasolt $p_(opt) = 3*m$');
        }

        if ((int)$ec->nrt === 1) {
            $ec->txt('Egy csavarsoros kialakítás.');

            $ec->def('dmin', ceil(2.9*$ec->t0f*sqrt($steelMaterial->fy/$boltMaterial->fu)), 'd_(min) = 2.9*t_(0,f)*sqrt(f_y/f_(ub)) = %% [mm]', 'Övlemez teherbírásának megfelelő csavarátmérő');
            if ($ec->dmin > $ec->d) {
                $ec->danger('Csavarátmérő túl kicsi!');
            }

            $ec->def('zmin', $ec->etaj1*($ec->W0pl/(5*($ec->t0f ** 2))), 'z_(min) = eta_(j1)* W_(0,pl)/(5*t_(0,f)^2 ) = %% [mm]', 'Oszlop teherbírás biztosítása - húzott és nyomott elemek távolsága.');
            $ec->note('Oszlop gerinc horpadásra ellenőrizendő (főleg HEA szelvények esetében).');
        }
    }
}
