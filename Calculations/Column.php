<?php

namespace Calculation;

Class Column extends \Ecc
{

    /**
     * @var $f3 \Base
     * @var $blc \Blc
     * @var $ec \Ec\Ec
     * @throws \Exception
     */
    public function calc(object $f3, object $blc, object $ec): void
    {
        $blc->region0('material', 'Anyagminőségek megadása');
            $ec->matList('cMat', 'C30/37', 'Beton anyagminőség');
            $ec->saveMaterialData($f3->_cMat, 'c');
            $ec->matList('rMat', 'B500', 'Betonvas anyagminőség');
            $ec->saveMaterialData($f3->_rMat, 'r');
        $blc->region1('material');

        $blc->region0('geom', 'Geometria megadása');
            $blc->input('a', 'Pillér méret egyik irányban', '400', 'mm', '');
            $blc->input('b', 'Pillér méret másik irányban', '400', 'mm', '');
            $blc->def('Ac', \H3::n0($f3->_a*$f3->_b), 'A_(c) = %% [mm^2]');
        $blc->region1('geom');

        $blc->h1('Közelítő méretfelvétel');
        $blc->input('NEd', '`N_(Ed)`: Nyomóerő', '1000', 'kN', '');
        $blc->def('Acmin', \H3::n0($f3->_NEd/$f3->_cfcd*1000), 'A_(c,min) = N_(Ed)/(f_(c,d)) = %% [mm^2]');
        $blc->math('a = '.$f3->_a.' [mm]');
        $blc->def('bmin', ceil(\H3::n0($f3->_Acmin/$f3->_a)), 'b_(min) = A_c/a = %% [mm]');

        $blc->h1('Vasalási keresztmetszet');
        $blc->def('A_s_min', number_format(0.002*$f3->_Ac*100, 0), 'A_(s, min) = %% [mm^2]');
        $blc->def('A_s_max', number_format(0.04*$f3->_Ac*100, 0), 'A_(s, max) = %% [mm^2]');

        $blc->h1('Tűzállósági határérték meghatározása', 'MSZ-EN 1992-1-2:2013 szabvány 5.3.2. pontja szerint: ***A*** módszer');
        $blc->numeric('phi', 'Fő vas átmérő', 20, 'mm', '');
        $blc->numeric('phis', '`phi_s:` Kengyel vas átmérő', 10, 'mm', '');
        $blc->numeric('cnom', '`c_(nom):` Tervezett betontakarás', 30, 'mm', '');
        $blc->def('as', $f3->_cnom + $f3->_phis + 0.5*$f3->_phi, 'a_s = %% [mm]', 'Betontakarás fővas tengelyen. `25 < a_s < 80 [mm]`');
        $blc->numeric('l', 'Pillér hálózati hossza', 3, 'm', '');
        $blc->numeric('beta', 'Pillér kihajlási csökkentő tényező tűzhatásra', 0.7, 'm', '');
        $blc->note('30 percnél hosszabb tűz esetén alsó szinten 0.5, felső szinten 0.7. 1.0 mindig alkalamzható.');
        $blc->def('l0fi', min($f3->_beta*$f3->_l, 3), 'l_(0,fi) = min{(beta*l),(3):} = %% [m]', 'Pillér hatékony kihajlási hossza');
        $blc->def('b1', \H3::n0((2*$f3->_Ac)/($f3->_a + $f3->_b)), 'b\' = %% [mm]', '');

        $blc->txt('Vasátmérő darabszámok:');
        $fields = [
            ['name' => 50, 'title' => 'Ø8', 'type' => 'input', 'sum' => true],
            ['name' => 79, 'title' => 'Ø10', 'type' => 'input', 'sum' => true],
            ['name' => 112, 'title' => 'Ø12', 'type' => 'input', 'sum' => true],
            ['name' => 154, 'title' => 'Ø16', 'type' => 'input', 'sum' => true],
            ['name' => 314, 'title' => 'Ø20', 'type' => 'input', 'sum' => true],
            ['name' => 491, 'title' => 'Ø25', 'type' => 'input', 'sum' => true],
            ['name' => 616, 'title' => 'Ø28', 'type' => 'input', 'sum' => true],
            ['name' => 804, 'title' => 'Ø32', 'type' => 'input', 'sum' => true],
        ];
        $blc->bulk('diameters', $fields);
        $As = 0;
        if (isset($f3->sum)) {
            foreach ($f3->sum as $key => $value) {
                $As = $As + $value*$key;
            }
        }
        $blc->def('As', $As, 'A_s = %% [mm^2]', 'Alkalmazott vas mennyiség');
        $blc->numeric('As', '`A_s:` Alkalmazott vas mennyiség', 7856, 'mm2', '`A_s < 0.04*A_c(='. 0.04*$f3->_Ac.')`');
        $blc->def('omega', \H3::n4(($f3->_As*$f3->_rfyd)/($f3->_Ac*$f3->_cfcd)), 'omega = (A_s*f_(yd))/(A_c*f_(cd)) = %%', 'Mechanikai acélhányad normálhőmérsékleten');
        $blc->numeric('alphacc', '`alpha_(c c):` Nyomószilárdság szorzótényezője', 1, '', 'Lásd EN 1992-1-1');
        $blc->numeric('mufi', '`mu_(fi):` Pillér kihasználtsága tűzhatás esetén', 0.4, '', '`0 < mu_(fi)=N_(Ed,fi)/N_(Rd) < 1`');
        $blc->def('Ra', \H3::n2(1.6*($f3->_as - 30)), 'R_a = 1.6*(a_s - 30) = %%', '');
        $blc->def('Rl', \H3::n2(9.6*(5 - $f3->_l0fi)), 'R_l = 9.6*(5 - l_(0,fi)) = %%', '');
        $blc->def('Rb', \H3::n2(0.09*$f3->_b1), 'R_b = 0.09*b\' = %%', '');
        $blc->boo('Rn0', 'Csak sarok vasak vannak', false, '');
        if ($f3->_Rn0) {
            $blc->def('Rn', 0, 'R_n = %%', 'Összesen 4 db sarokvas');
        } else {
            $blc->def('Rn', 12, 'R_n = %%', 'Vasak nem csak a sarkokban vannak');
        }
        $blc->def('Retafi', 83*(1-($f3->_mufi*((1 + $f3->_omega)/((0.85/$f3->_alphacc) + $f3->_omega)))), 'R_(eta,fi) = %%');
        $blc->def('R', \H3::n0(120*pow(($f3->_Retafi + $f3->_Ra + $f3->_Rl + $f3->_Rb + $f3->_Rn)/120, 1.8)), 'R = 120*((R_(eta,fi) + R_a + R_l + R_b + R_n)/120)^1.8 = %%');
        if ($f3->_R < 30) {
            $blc->txt('R0', 'Tűzállóság');
        } else if ($f3->_R < 60) {
            $blc->txt('R30', 'Tűzállóság');
        } else if ($f3->_R < 90) {
            $blc->txt('R60', 'Tűzállóság');
        } else if ($f3->_R < 120) {
            $blc->txt('R90', 'Tűzállóság');
        } else if ($f3->_R < 180) {
            $blc->txt('R120', 'Tűzállóság');
        } else if ($f3->_R < 240) {
            $blc->txt('R180', 'Tűzállóság');
        } else {
            $blc->txt('R240', 'Tűzállóság');
        }

        $blc->h1('Imperfekció', 'Mértékadó normálerőkből számított imperfekciós vízszintes terhelés');
        $blc->note('Tartószerkezet geometriai pontatlanságának figyelembe vétele a merevítőrendszert terheli.');
        $blc->note('+x +y -x -y irányokban is hathat.');
        $blc->numeric('Phi0', '`Phi_0:` Elfordulás alapértéke', 0.005, '', 'Alapértelmezetten 1/200');
        $blc->numeric('m', 'Egy szinten lévő pillérek száma', 10, 'db', '');
        $blc->def('alphan', sqrt(0.5*(1 + 1/$f3->_m)), 'alpha_n = sqrt(0.5*(1+1/m)) = %%', 'Csökkentő tényező oszlopszámtól függően');
        $blc->def('Phi', $f3->_alphan*$f3->_Phi0, 'Phi = alpha_n*Phi_0 = %%', 'Elfordulás oszlopszámtól függő értéke');
        $blc->lst('upsilon', ['Merevített szerkezet (1)' => 1, 'Kilendülő szerkezet (2)' => 2], '`upsilon:` Oszlop kihajlási fél-hullámhossza', 1);
        $blc->numeric('l', '`l:` Pillér magassága', 3, 'm');
        $blc->def('l0', $f3->_l*$f3->_upsilon, 'l_0 = upsilon*l = %% [m]', 'Kihajlási hossz');
        $blc->def('e', \H3::n1($f3->_l0*0.5*1000*$f3->_Phi), 'e = l_0/2*Phi = %% [mm]', 'Egyenértékű elmozdulás');
        $fields = [
            ['name' => 'name', 'title' => 'Pillér csoport neve', 'type' => 'input', 'sum' => false],
            ['name' => 'Qdmax', 'title' => '`Q_(d,max) [kN]`', 'type' => 'input', 'sum' => false],
            ['name' => 'H', 'title' => '`H [kN]` vízszintes erő', 'type' => 'value', 'key' => 'H0', 'sum' => false],
        ];
        if ($f3->exists('POST._imperfections')) {
            foreach ($f3->get('POST._imperfections') as $key => $value) {
                $f3->set("POST._imperfections.$key.H0", \H3::n2(\V3::numeric($value['Qdmax'])*$f3->_Phi));
            }
        }
        $blc->bulk('imperfections', $fields);
        $blc->txt(false, 'Vízszintes erők: `H_i = Q_(d,max,i)*Phi`');
    }
}
