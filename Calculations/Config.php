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
    }
}
