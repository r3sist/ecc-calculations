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

        $blc->boo('nativeMath', 'Szerveroldali ASCIIMath konvertálás MathML formátumba', $f3->udata['ueccnativemathml'], 'A módosítások aktiválásához teljes oldal újratöltése szükséges.');
        $f3->mu->load(array('uid = :uid', ':uid' => $f3->get('uid')));
        if (!$f3->mu->dry()) {
            $f3->mu->ueccnativemathml = \V3::boo($f3->_nativeMath);
            $f3->mu->save();
        }

        if ($f3->urole >= 30) {
            $blc->h1('Admin');
            $query = "SELECT * FROM ecc_calculations ORDER BY cname ASC";
            $calcList = $f3->get('db')->exec($query);
            foreach ($calcList as $calcData) {
                $f3->mc->reset();
                $f3->mc->load(['cname = :cname', ':cname' => $calcData['cname']]);

                $blc->region0('admin'.$calcData['cname'], $calcData['cname']);
                $blc->h2($calcData['cname']);

                $blc->input($calcData['cname'].'___ctitle', 'title', $calcData['ctitle'], '', '');
                $f3->mc->ctitle = $f3->get('_'.$calcData['cname'].'___ctitle');

                $blc->input($calcData['cname'].'___csubtitle', 'subtitle', $calcData['csubtitle'], '', '');
                $f3->mc->csubtitle = $f3->get('_'.$calcData['cname'].'___csubtitle');

                $blc->input($calcData['cname'].'___cdescription', 'description', $calcData['cdescription'], '', '');
                $f3->mc->cdescription = $f3->get('_'.$calcData['cname'].'___cdescription');

                $blc->lst($calcData['cname'].'___cgroup', ['S' => 'S', 'L' => 'L', 'G' => 'G', 'C' => 'C'], 'group', $calcData['cgroup'], '', '');
                $f3->mc->cgroup = $f3->get('_'.$calcData['cname'].'___cgroup');

                $blc->boo($calcData['cname'].'___cexpreimental', 'experimental', $calcData['cexperimental'], '');
                $f3->mc->cexpreimental = $f3->get('_'.$calcData['cname'].'___cexpreimental');
                if (!$f3->mc->dry()) {
                    $f3->mc->save();
                }
                $blc->region1('admin'.$calcData['cname']);
            }
        }
    }
}
