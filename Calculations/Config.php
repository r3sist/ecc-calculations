<?php

namespace Calculation;

Class Config extends \Ecc
{

    /**
     * @var $f3 \Base
     * @var $blc \Blc
     * @var $ec \Ec\Ec
     * @throws \Exception
     */
    public function calc(object $f3, object $blc, object $ec): void
    {
        $f3->set('mu', new \DB\SQL\Mapper($f3->get('db'), 'users'));

        $blc->h1('Mentés feltöltése');
        $blc->info0('upload');
        $blc->input('saveJson', '*.ecc* fájl tartalma', false, false, 'Kezdőoldalról letöltött *.ecc* fájl tartalmát bemásolva lehet mentést feltölteni. Ez egy JSON sztring. Más felhasználótól származó mentést is bevesz a rendszer. Hibás, hiányzó vagy azóta megváltozott paraméterek esetén alapértelmezésekkel fog számolni.', 'valid_json_string');
        if ($f3->_saveJson) {
            $saveDataArray = json_decode($f3->_saveJson, true);
            $saveDataJson = json_encode($saveDataArray);
            $blc->pre(json_encode($saveDataArray, JSON_PRETTY_PRINT));
            $f3->ms->reset();
            $f3->ms->load();
            $f3->ms->cname = \V3::varname($saveDataArray['_project_cname']);
            $f3->ms->sname = $saveDataArray['_project'];
            $f3->ms->uid = $f3->uid;
            $f3->ms->sdata = $saveDataJson;
            $f3->ms->save();
            $blc->toast('Mentés importálva!');
            $blc->html('<script>window.location.replace("'.$f3->home.'calc/'.$saveDataArray['_project_cname'].'/load/'.$f3->ms->get('sid').'");</script>');
        }
        $blc->info1('upload');

        $blc->h1('Felhasználói beállítások');
        $blc->h2('Képletek kezelése');
        $blc->boo('nativeMath', 'Szerveroldali ASCIIMath konvertálás MathML formátumba', $f3->udata['ueccnativemathml'], 'MathJax helyett szerverordali képlet generálás. Csak Firefox alatt. Rondább, de gyorsabb megjelenítés.');
        $blc->boo('svgMath', 'Képletek SVG képekként', $f3->udata['ueccsvgmath'], 'A képletek képként kerülnek megjelenítésre.');
        $f3->mu->load(array('uid = :uid', ':uid' => $f3->get('uid')));
        if (!$f3->mu->dry()) {
            $f3->mu->ueccnativemathml = $f3->_nativeMath;
            $f3->mu->ueccsvgmath = $f3->_svgMath;
            $f3->mu->save();
        }
        $blc->txt('', 'A módosítások aktiválásához a teljes oldal újratöltése szükséges.');

        $blc->h2('Sablonok', 'MS Word export');
        $blc->lst('template', ['CÉH' => '', 'Structure' => 'Str'], false, '');
        $f3->mu->load(array('uid = :uid', ':uid' => $f3->get('uid')));
        if (!$f3->mu->dry()) {
            $f3->mu->utemplate = $f3->_template;
            $f3->mu->save();
        }

        if ($f3->urole >= 30) {
            $blc->h1('Admin');
            $blc->boo('doUpdate', 'Számítás meta adatok szerkesztése/mentése', 0, '');
            if (!$f3->mc->dry() && $f3->_doUpdate) {

                $query = "SELECT * FROM ecc_calculations ORDER BY cname ASC";
                $calcList = $f3->get('db')->exec($query);
                \L3::instance()->put('ecc', 'edit', 'Edited calcs meta');
                foreach ($calcList as $calcData) {
                    $f3->mc->reset();
                    $f3->mc->load(['cname = :cname', ':cname' => $calcData['cname']]);

                    $blc->region0('admin' . $calcData['cname'], $calcData['cname']);
                    $blc->h2('[' . $calcData['cname'] . '](https://structure.hu/calc/' . $calcData['cname'] . ')');

                    $blc->input($calcData['cname'] . '___ctitle', 'title', $calcData['ctitle'], '', '');
                    $f3->mc->ctitle = $f3->get('_' . $calcData['cname'] . '___ctitle');

                    $blc->input($calcData['cname'] . '___csubtitle', 'subtitle', $calcData['csubtitle'], '', '');
                    $f3->mc->csubtitle = $f3->get('_' . $calcData['cname'] . '___csubtitle');

                    $blc->input($calcData['cname'] . '___cdescription', 'description', $calcData['cdescription'], '', '');
                    $f3->mc->cdescription = $f3->get('_' . $calcData['cname'] . '___cdescription');

                    $blc->lst($calcData['cname'] . '___cgroup', ['S' => 'S', 'L' => 'L', 'G' => 'G', 'C' => 'C'], 'group', $calcData['cgroup'], '', '');
                    $f3->mc->cgroup = $f3->get('_' . $calcData['cname'] . '___cgroup');

                    $blc->boo($calcData['cname'] . '___cexpreimental', 'experimental', $calcData['cexperimental'], '');
                    $f3->mc->cexpreimental = $f3->get('_' . $calcData['cname'] . '___cexpreimental');

                    $blc->boo($calcData['cname'] . '___chidden', 'hidden', $calcData['chidden'], '');
                    $f3->mc->chidden = $f3->get('_' . $calcData['cname'] . '___chidden');

                    if (!$f3->mc->dry() && $f3->_doUpdate) {
                        $f3->mc->save();
                    }
                    $blc->region1('admin' . $calcData['cname']);
                }
            }

            $blc->h2('Új számítás hozzáadása');
            $blc->input('cnameNew', 'Osztály azonosító', '', '', 'ecc-calculation osztály azonosító (URL azonosító lesz)', 'alpha');
            $blc->txt('Composer autoload lefuttatása szükséges!');
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
