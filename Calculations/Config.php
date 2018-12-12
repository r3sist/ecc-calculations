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
        $blc->h1('Beállítások');
        $blc->boo('nativeMath', 'Szerveroldali ASCIIMath konvertálás MathML formátumba.', false, '');

        $f3->mu->load(array('uid = :uid', ':uid'=>$f3->get('uid')));
        if (!$this->f3->mu->dry()) {
            $this->f3->mu->ueccnativemathml = \V3::boo($f3->_nativeMath);
            $this->f3->mu->save();
        }
    }
}
