<?php declare(strict_types = 1);
// User settings and system configurator of Ecc framework - Calculation class for ECC framework
// (c) Bence VÁNKOS | https://structure.hu | https://github.com/r3sist/ecc-calculations

namespace Calculation;

use \Base;
use DB\SQL\Mapper;
use \Ecc\Blc;
use \Ec\Ec;
use \resist\H3\Logger;
use resist\U3\Map;
use Respect\Validation\Validator as v;

Class Config
{
    private Logger $logger;
    private v $vLabel;
    private Mapper $userMap;

    public function __construct(Logger $logger, Mapper $userMap)
    {
        $this->logger = $logger;
        $this->vLabel = v::phpLabel();
        $this->userMap = $userMap;
    }

    public function calc(Base $f3, Blc $blc, Ec $ec): void
    {
        $blc->h1('Sablonok', 'MS Word export');

        $blc->lst('template', [$f3->get('udata.ufirm') => $f3->get('firms')[$f3->get('udata.ufirm')], 'Structure' => 'Structure'], ['', 'Sablon'], $f3->get('udata.utemplate'));

        if ($this->userMap) {
            $this->userMap->utemplate = $f3->_template;
            $this->userMap->save();
        }


        $blc->h1('Képletek kezelése');
        $blc->boo('nativeMath', ['', 'Szerveroldali ASCIIMath konvertálás MathML formátumba'], (bool)$f3->udata['ueccnativemathml'], 'Csak Firefox alatt! MathJax helyett szerverordali képlet renderelés. Rondább, de gyorsabb és ugrálás nélküli megjelenítés.');
        $blc->boo('svgMath', ['', 'Képletek SVG képekként'], (bool)$f3->udata['ueccsvgmath'], 'A képletek képként kerülnek megjelenítésre.');
        $f3->u->map->load(['uid = :uid', ':uid' => $f3->get('uid')]);
        if (!$f3->u->map->dry()) {
            $f3->u->map->ueccnativemathml = $f3->_nativeMath;
            $f3->u->map->ueccsvgmath = $f3->_svgMath;
            $f3->u->map->save();
        }
        $blc->txt('', 'A módosítások aktiválásához a teljes oldal újratöltése szükséges.');

        if ($f3->urole >= 30) {
            $blc->h1('Admin');
            $blc->boo('doUpdate', ['', 'Számítás meta adatok szerkesztése/mentése'], false, '');
            if (!$f3->mc->dry() && $f3->_doUpdate) {

                $query = "SELECT * FROM ecc_calculations ORDER BY cname ASC";
                $calcList = $f3->get('db')->exec($query);
                $this->logger->create('ecc', 'edit', 'Edited calcs meta');
                foreach ($calcList as $calcData) {
                    $f3->mc->reset();
                    $f3->mc->load(['cname = :cname', ':cname' => $calcData['cname']]);

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

                    if (!$f3->mc->dry() && $f3->_doUpdate) {
                        $f3->mc->ctitle = $f3->get('_' . $calcData['cname'] . '___ctitle');
                        $f3->mc->csubtitle = $f3->get('_' . $calcData['cname'] . '___csubtitle');
                        $f3->mc->cdescription = $f3->get('_' . $calcData['cname'] . '___cdescription');
                        $f3->mc->cgroup = $f3->get('_' . $calcData['cname'] . '___cgroup');
                        $f3->mc->cexperimental = $f3->get('_' . $calcData['cname'] . '___cexpreimental');
                        $f3->mc->chidden = $f3->get('_' . $calcData['cname'] . '___chidden');
                        $f3->mc->cprivate = $f3->get('_' . $calcData['cname'] . '___cprivate');
                        $f3->mc->csecondary = $f3->get('_' . $calcData['cname'] . '___csecondary');
                        $f3->mc->save();
                    }
                    $blc->region1();
                }
            }

            $blc->h1('Új számítás hozzáadása');
            $blc->input('cnameNew', ['', 'Osztály azonosító'], '', '', 'ecc-calculation osztály azonosító (URL azonosító lesz)', 'alpha');
            $blc->txt('Composer autoloader frissítése szükséges!');
            if ($f3->_cnameNew) {
                $f3->mc->reset();
                $f3->mc->cname = ucfirst($f3->get('_cnameNew'));
                $f3->mc->save();

                $blc->toast('Új osztály beszúrva', 'success', '');
                $blc->html('<script>$("#_cname").val("");</script>');
            }
        }
    }
}
