<?php declare(strict_types = 1);
// User settings and system configurator of Ecc framework
// (c) Bence VÁNKOS | https://structure.hu | https://github.com/r3sist/ecc-calculations

namespace Calculation;

use Base;
use DB\SQL;
use Ecc\Map\CalculationMap;
use resist\Auth3\Auth3Helper;
use DB\SQL\Mapper;
use Ecc\Blc;
use Ec\Ec;
use resist\Auth3\UserMap;
use resist\H3\Logger;

Class Config
{
    private Logger $logger;
    private Mapper $userMap;
    private CalculationMap $calculationMap;
    private SQL $db;

    public function __construct(Logger $logger, UserMap $userMap, CalculationMap $calculationMap, SQL $db)
    {
        $this->logger = $logger;
        $this->userMap = $userMap;
        $this->calculationMap = $calculationMap;
        $this->db = $db;
    }

    public function calc(Base $f3, Blc $blc, Ec $ec): void
    {
        $this->userMap->loadByUid($f3->get('uid'));

        if ($f3->uname === 'CÉH') {
            $blc->danger('Ez a CÉH közös fiók, a módosítások mindenkinél számítanak!');
        }

        $blc->h1('Sablonok', 'MS Word export');

        $blc->lst('template', [$f3->get('udata.ufirm') => $f3->get('firms')[$f3->get('udata.ufirm')], 'Structure' => 'Structure'], ['', 'Sablon'], $f3->get('udata.utemplate'));

        if ($this->userMap) {
            $this->userMap->utemplate = $f3->_template;
            $this->userMap->save();
        }


        $blc->h1('Képletek kezelése');
        $blc->boo('nativeMath', ['', 'Szerveroldali ASCIIMath konvertálás MathML formátumba'], (bool)$f3->udata['ueccnativemathml'], 'Csak Firefox alatt! MathJax helyett szerverordali képlet renderelés. Rondább, de gyorsabb és ugrálás nélküli megjelenítés.');
        if ($this->userMap) {
            $this->userMap->ueccnativemathml = $f3->_nativeMath;
            $this->userMap->save();
        }
        $blc->txt('', 'A módosítások aktiválásához a teljes oldal újratöltése szükséges.');

        if (Auth3Helper::isAdmin()) {
            $blc->h1('Admin');
            $blc->boo('doUpdate', ['', 'Számítás meta adatok szerkesztése/mentése'], false, '');
            if ($f3->_doUpdate) {

                $query = 'SELECT * FROM ecc_calculations ORDER BY cname ASC';
                $calcList = $this->db->exec($query);
                $this->logger->create('ecc', 'edit', 'Edited calcs meta');
                foreach ($calcList as $calcData) {
                    $this->calculationMap->reset();
                    $this->calculationMap->load(['cname = :cname', ':cname' => $calcData['cname']]);

                    $blc->region0('admin' . $calcData['cname'], $calcData['cname']);
                        $blc->h2('[' . $calcData['cname'] . '](https://structure.hu/calc/' . $calcData['cname'] . ')');

                        $blc->input($calcData['cname'] . '___ctitle', ['', 'title'], $calcData['ctitle'], '', '');
                        $blc->input($calcData['cname'] . '___csubtitle', ['', 'subtitle'], $calcData['csubtitle'], '', '');
                        $blc->input($calcData['cname'] . '___cdescription', ['', 'description'], $calcData['cdescription'], '', '');
                        $blc->lst($calcData['cname'] . '___cgroup', ['S' => 'S', 'L' => 'L', 'G' => 'G', 'C' => 'C'], ['', 'group'], $calcData['cgroup'], '');
                        $blc->boo($calcData['cname'] . '___cexpreimental', ['', 'experimental'], (bool)$calcData['cexperimental'], '');
                        $blc->boo($calcData['cname'] . '___chidden', ['', 'hidden'], (bool)$calcData['chidden'], '');
                        $blc->boo($calcData['cname'] . '___cprivate', ['', 'private'], (bool)$calcData['cprivate'], '');
                        $blc->boo($calcData['cname'] . '___csecondary', ['', 'secondary'], (bool)$calcData['csecondary'], '');

                        $this->calculationMap->ctitle = $f3->get('_' . $calcData['cname'] . '___ctitle');
                        $this->calculationMap->csubtitle = $f3->get('_' . $calcData['cname'] . '___csubtitle');
                        $this->calculationMap->cdescription = $f3->get('_' . $calcData['cname'] . '___cdescription');
                        $this->calculationMap->cgroup = $f3->get('_' . $calcData['cname'] . '___cgroup');
                        $this->calculationMap->cexperimental = $f3->get('_' . $calcData['cname'] . '___cexpreimental');
                        $this->calculationMap->chidden = $f3->get('_' . $calcData['cname'] . '___chidden');
                        $this->calculationMap->cprivate = $f3->get('_' . $calcData['cname'] . '___cprivate');
                        $this->calculationMap->csecondary = $f3->get('_' . $calcData['cname'] . '___csecondary');
                        $this->calculationMap->save();
                    $blc->region1();
                }
            }

            $blc->h1('Új számítás hozzáadása');
            $blc->input('cnameNew', ['', 'Osztály azonosító'], '', '', 'ecc-calculation osztály azonosító (URL azonosító lesz)', 'alpha');
            $blc->txt('Composer autoloader frissítése szükséges!');
            if ($f3->_cnameNew) {
                $this->calculationMap->reset();
                $this->calculationMap->cname = ucfirst($f3->get('_cnameNew'));
                $this->calculationMap->save();

                $blc->toast('Új osztály beszúrva', 'success', '');
                $blc->html('<script>$("#_cname").val("");</script>');
            }
        }
    }
}
