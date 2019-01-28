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
    public function moduleColumnData(object $f3, object $blc, object $ec): void
    {
        $blc->region0('material', 'Anyagminőségek megadása');
            $ec->matList('cMat', 'C30/37', 'Beton anyagminőség');
            $ec->saveMaterialData($f3->_cMat, 'c');
            $ec->matList('rMat', 'B500', 'Betonvas anyagminőség');
            $ec->saveMaterialData($f3->_rMat, 'r');
        $blc->region1('material');

        $blc->region0('geom', 'Geometria megadása');
            $blc->numeric('a', ['a', 'Pillér méret egyik irányban'], '400', 'mm', '');
            $blc->numeric('b', ['b', 'Pillér méret másik irányban'], '400', 'mm', '');
            $blc->def('Ac', \H3::n0($f3->_a*$f3->_b), 'A_(c) = %% [mm^2]');
            $blc->numeric('cnom', ['c_(nom)', 'Tervezett betontakarás'], 25, 'mm', '');
        $blc->region1('geom');
    }

    /**
     * @var $f3 \Base
     * @var $blc \Blc
     * @var $ec \Ec\Ec
     * @throws \Exception
     */
    public function calc(object $f3, object $blc, object $ec): void
    {
        $blc->toc();

        $this->moduleColumnData($f3, $blc, $ec);

        $blc->numeric('NEd', ['N_(Ed)', 'Nyomóerő'], 1000, 'kN', '');

        $blc->h1('Közelítő méretfelvétel');
        $blc->def('Acmin', \H3::n0($f3->_NEd/$f3->_cfcd*1000), 'A_(c,min) = N_(Ed)/(f_(c,d)) = %% [mm^2]', 'Szükséges beton keresztmetszet');
        $blc->math('a = '.$f3->_a.' [mm]', 'Felvett egyik méret');
        $blc->def('bmin', ceil(\H3::n0($f3->_Acmin/$f3->_a)), 'b_(min) = A_(c,min)/a = %% [mm]', 'Szükséges másik méret');
        $blc->txt('Szükséges vasmennyiségek **'.$f3->_a.' × '.$f3->_b.'** keresztmetszethez:');
        $blc->def('A_s_min', \H3::n0(0.002*$f3->_Ac), 'A_(s, min) = %% [mm^2]', 'Minimális vasmennyiség: 2‰');
        $blc->def('A_s_max', \H3::n0(0.04*$f3->_Ac), 'A_(s, max) = %% [mm^2]', 'Maximális vasmennyiség: 4%');

        $blc->h1('Imperfekció', 'Mértékadó normálerőkből számított imperfekciós vízszintes terhelés');
        $blc->note('Tartószerkezet geometriai pontatlanságának figyelembe vétele a merevítőrendszert terheli.');
        $blc->note('+x +y -x -y irányokban is hathat.');
        $blc->numeric('Phi0', ['Phi_0', 'Elfordulás alapértéke'], 0.005, '', 'Alapértelmezetten 1/200');
        $blc->numeric('m', ['m', 'Egy szinten lévő pillérek száma'], 10, 'db', '');
        $blc->def('alpham', sqrt(0.5*(1 + 1/$f3->_m)), 'alpha_m = sqrt(0.5*(1+1/m)) = %%', 'Csökkentő tényező oszlopszámtól függően');
        $blc->numeric('l', ['l', 'Pillér magassága'], 3, 'm');
        $blc->def('alphan', min(1, max(0.6667, 2/sqrt($f3->_l))), 'alpha_n = min{(1),( max{(2/sqrt(l) = '. 2/sqrt($f3->_l).'),(0.667):}):} = %%', '');
        $blc->def('Phi', $f3->_alpham*$f3->_alphan*$f3->_Phi0, 'Phi = alpha_m*alpha_n*Phi_0 = %%', 'Elfordulás oszlopszámtól függő értéke');
        $blc->lst('upsilon', ['Merevített szerkezet (1)' => 1, 'Kilendülő szerkezet (2)' => 2], ['upsilon', 'Oszlop kihajlási fél-hullámhossza'], 1);
        $blc->def('l0', $f3->_l*$f3->_upsilon, 'l_0 = upsilon*l = %% [m]', 'Kihajlási hossz');
        $blc->def('e', \H3::n1($f3->_l0*1000*$f3->_Phi), 'e_(calc) = l_0*Phi = %% [mm] = l/'.\H3::n0(($f3->_l*1000)/\H3::n1($f3->_l0*1000*$f3->_Phi)), 'Egyenértékű elmozdulás');
        $blc->note('Egyszerűsítésként $l/400$ használatát engedi a szabvány');

        $fields = [
            ['name' => 'name', 'title' => 'Pillér csoport neve', 'type' => 'input', 'sum' => false],
            ['name' => 'N', 'title' => '$Delta N; sum N [kN]$', 'type' => 'input', 'sum' => false],
            ['name' => 'db', 'title' => 'darab', 'type' => 'input', 'sum' => true],
            ['name' => 'H', 'title' => '$H [kN]$', 'type' => 'value', 'key' => 'H0', 'sum' => false],
            ['name' => 'SH', 'title' => '$sum H [kN]$', 'type' => 'value', 'key' => 'SH', 'sum' => true],
        ];
        if ($f3->exists('POST._imperfections')) {
            foreach ($f3->get('POST._imperfections') as $key => $value) {
                $f3->set("POST._imperfections.$key.H0", \H3::n2(\V3::numeric($value['N'])*$f3->_Phi));
                $f3->set("POST._imperfections.$key.SH", \H3::n2(\V3::numeric($value['db'])*\H3::n2(\V3::numeric($value['N'])*$f3->_Phi)));
            }
        }
        $blc->bulk('imperfections', $fields);
        $blc->txt(false, 'Vízszintes erők: $H_i = N*Phi$.');
    }
}
