<?php declare(strict_types = 1);
// Analysis of CK sheets according to Eurocodes - Calculation class for ECC framework
// (c) Bence VÁNKOS | https://structure.hu | https://github.com/r3sist/ecc-calculations

namespace Calculation;

use \Base;
use \Ecc\Blc;
use \Ec\Ec;
use \H3;

Class Ck
{
    public function calc(Base $f3, Blc $blc, Ec $ec): void
    {
        $blc->numeric('hck', ['h', 'Lap vastagság'], 10, 'mm', '');
        $blc->numeric('Lck', ['L', 'Fesztávolság'], 650, 'mm', '');
        $blc->numeric('gck', ['gamma', 'Lap fajsúly'], 14, 'kN/m3', '');
        $blc->numeric('sckk', ['sigma_k', 'Lap szilárdság'], 9, 'N/mm2', '');
        $blc->numeric('gM', ['gamma_M', 'Anyagi biztonsági tényező'], 1.2, '', '');
        $blc->def('sckd', $f3->_sckk/$f3->_gM, 'sigma_d = sigma_k/gamma_M = %% [N/(mm^2)]');
        $blc->def('bck', 1000, 'b = 1000 [mm]', 'Táblaszélesség');
        $blc->def('Iyck', ceil(($f3->_bck*pow($f3->_hck, 3))/12), 'I_y = (b*h^3)/12 = %% [mm^4]', 'Inercia');
        $blc->def('Wck', ceil(($f3->_bck*pow($f3->_hck, 2))/6), 'W = (b*h^2)/6 = %% [mm^3]', 'Keresztmetszeti modulus');
        $blc->def('gkck', \H3::n3(($f3->_gck*$f3->_hck)/1000), 'g_(0,k) = gamma*h = %% [(kN)/m^2]', 'Önsúly');
        $blc->numeric('gk', ['g_k', 'Rétegrendből adódó teher'], 0.5, 'kN/m2', 'CK önsúlyon felül');
        $blc->numeric('qk', ['q_k', 'Hasznos teher'], 1, 'kN/m2', '');
        $blc->def('qd', \H3::n3(1.35*($f3->_gk + $f3->_gkck) + 1.5*$f3->_qk), 'q_d = 1.35*(g_(0,k) + g_k) + 1.5*q_k = %% [(kN)/m^2]', 'Összes teher', '');
        $blc->def('MRdck', \H3::n3(($f3->_sckd*$f3->_Wck)/1000000), 'M_(Rd) = sigma_d*W = %% [kNm]', 'Nyomatéki teherbírás');
        $blc->def('MEdck', \H3::n3(($f3->_qd*pow($f3->_Lck/1000, 2))/8), 'M_(Ed) = (q_d*L^2)/8 = %% [kNm]', 'Nyomatéki igénybevétel');
        $blc->label($f3->_MEdck/$f3->_MRdck, 'Kihasználtság');
    }
}
