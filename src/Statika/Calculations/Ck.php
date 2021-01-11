<?php declare(strict_types = 1);
/**
 * Analysis of CK sheets according to Eurocodes - Calculation class for Statika framework
 * (c) Bence VÁNKOS | https://structure.hu | https://github.com/r3sist/ecc-calculations
 */
namespace Statika\Calculations;

use \H3;
use Statika\Ec;
use Statika\EurocodeInterface;

Class Ck
{
    /**
     * @param Ec $ec
     */
    public function calc(EurocodeInterface $ec): void
    {
        $ec->numeric('hck', ['h', 'Lap vastagság'], 10, 'mm', '');
        $ec->numeric('Lck', ['L', 'Fesztávolság'], 650, 'mm', '');
        $ec->numeric('gck', ['gamma', 'Lap fajsúly'], 14, 'kN/m3', '');
        $ec->numeric('sckk', ['sigma_k', 'Lap szilárdság'], 9, 'N/mm2', '');
        $ec->numeric('gM', ['gamma_M', 'Anyagi biztonsági tényező'], 1.2, '', '');
        $ec->def('sckd', $ec->sckk/$ec->gM, 'sigma_d = sigma_k/gamma_M = %% [N/(mm^2)]');
        $ec->def('bck', 1000, 'b = 1000 [mm]', 'Táblaszélesség');
        $ec->def('Iyck', ceil(($ec->bck* ($ec->hck ** 3))/12), 'I_y = (b*h^3)/12 = %% [mm^4]', 'Inercia');
        $ec->def('Wck', ceil(($ec->bck* ($ec->hck ** 2))/6), 'W = (b*h^2)/6 = %% [mm^3]', 'Keresztmetszeti modulus');
        $ec->def('gkck', H3::n3(($ec->gck*$ec->hck)/1000), 'g_(0,k) = gamma*h = %% [(kN)/m^2]', 'Önsúly');
        $ec->numeric('gk', ['g_k', 'Rétegrendből adódó teher'], 0.5, 'kN/m2', 'CK önsúlyon felül');
        $ec->numeric('qk', ['q_k', 'Hasznos teher'], 1, 'kN/m2', '');
        $ec->def('qd', H3::n3(1.35*($ec->gk + $ec->gkck) + 1.5*$ec->qk), 'q_d = 1.35*(g_(0,k) + g_k) + 1.5*q_k = %% [(kN)/m^2]', 'Összes teher', '');
        $ec->def('MRdck', H3::n3(($ec->sckd*$ec->Wck)/1000000), 'M_(Rd) = sigma_d*W = %% [kNm]', 'Nyomatéki teherbírás');
        $ec->def('MEdck', H3::n3(($ec->qd* (($ec->Lck / 1000) ** 2))/8), 'M_(Ed) = (q_d*L^2)/8 = %% [kNm]', 'Nyomatéki igénybevétel');
        $ec->label($ec->MEdck/$ec->MRdck, 'Kihasználtság');
    }
}
