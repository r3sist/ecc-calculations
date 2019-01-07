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
        $blc->toc();

        $blc->region0('material', 'Anyagminőségek megadása');
            $ec->matList('cMat', 'C30/37', 'Beton anyagminőség');
            $ec->saveMaterialData($f3->_cMat, 'c');
            $ec->matList('rMat', 'B500', 'Betonvas anyagminőség');
            $ec->saveMaterialData($f3->_rMat, 'r');
        $blc->region1('material');

        $blc->region0('geom', 'Geometria megadása');
            $blc->input('a', '`a:` Pillér méret egyik irányban', '400', 'mm', '');
            $blc->input('b', '`b:` Pillér méret másik irányban', '400', 'mm', '');
            $blc->def('Ac', \H3::n0($f3->_a*$f3->_b), 'A_(c) = %% [mm^2]');
            $blc->numeric('cnom', '`c_(nom):` Tervezett betontakarás', 25, 'mm', '');
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
        $blc->numeric('phi', '`phi:` Fő vas átmérő', 20, 'mm', '');
        $blc->numeric('phis', '`phi_s:` Kengyel vas átmérő', 10, 'mm', '');
        $blc->def('as', $f3->_cnom + $f3->_phis + 0.5*$f3->_phi, 'a_s = c_(nom) + phi_s + phi/2 = %% [mm]', 'Betontakarás fővas tengelyen. `25 < a_s < 80 [mm]`');
        $blc->numeric('lfi', '`l_(fi):` Pillér hálózati hossza', 3, 'm', '');
        $blc->numeric('beta', '`beta:` Pillér kihajlási csökkentő tényező tűzhatásra', 0.7, 'm', '');
        $blc->note('30 percnél hosszabb tűz esetén alsó szinten 0.5, felső szinten 0.7 megengedett. 1.0 mindig alkalamzható.');
        $blc->boo('l0fired', 'Hálózati hossz csökkentése', false, 'MSZ EN 1992-1-2:2013 5.3.2. (2) `l_(0,fi)` maximum 3 m.');
        if ($f3->_l0fired) {
            $blc->def('l0fi', min($f3->_beta*$f3->_lfi, 3), 'l_(0,fi) = min{(beta*l_(fi)),(3):} = %% [m]', 'Pillér hatékony kihajlási hossza');
        } else {
            $blc->def('l0fi', $f3->_beta*$f3->_lfi, 'l_(0,fi) = beta*l_(fi) = %% [m]', 'Pillér hatékony kihajlási hossza');
        }
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
        $blc->def('Retafi', \H3::n2(83*(1-($f3->_mufi*((1 + $f3->_omega)/((0.85/$f3->_alphacc) + $f3->_omega))))), 'R_(eta,fi) = %%');
        $blc->def('R', \H3::n0(120*pow(($f3->_Retafi + $f3->_Ra + $f3->_Rl + $f3->_Rb + $f3->_Rn)/120, 1.8)), 'R = 120*((R_(eta,fi) + R_a + R_l + R_b + R_n)/120)^1.8 = %%');
        if ($f3->_R < 30) {
            $blc->info('R0', 'Tűzállóság');
        } else if ($f3->_R < 60) {
            $blc->info('R30', 'Tűzállóság');
        } else if ($f3->_R < 90) {
            $blc->info('R60', 'Tűzállóság');
        } else if ($f3->_R < 120) {
            $blc->info('R90', 'Tűzállóság');
        } else if ($f3->_R < 180) {
            $blc->info('R120', 'Tűzállóság');
        } else if ($f3->_R < 240) {
            $blc->info('R180', 'Tűzállóság');
        } else {
            $blc->info('R240', 'Tűzállóság');
        }

        $blc->h1('Imperfekció', 'Mértékadó normálerőkből számított imperfekciós vízszintes terhelés');
        $blc->note('Tartószerkezet geometriai pontatlanságának figyelembe vétele a merevítőrendszert terheli.');
        $blc->note('+x +y -x -y irányokban is hathat.');
        $blc->numeric('Phi0', '`Phi_0:` Elfordulás alapértéke', 0.005, '', 'Alapértelmezetten 1/200');
        $blc->numeric('m', 'Egy szinten lévő pillérek száma', 10, 'db', '');
        $blc->def('alpham', sqrt(0.5*(1 + 1/$f3->_m)), 'alpha_m = sqrt(0.5*(1+1/m)) = %%', 'Csökkentő tényező oszlopszámtól függően');
        $blc->numeric('l', '`l:` Pillér magassága', 3, 'm');
        $blc->def('alphan', min(1, max(0.6667, 2/sqrt($f3->_l))), 'alpha_n = 2/sqrt(l) = %%', '`2/3 <= alpha_n <= 1`');
        $blc->def('Phi', $f3->_alpham*$f3->_alphan*$f3->_Phi0, 'Phi = alpha_m*alpha_n*Phi_0 = %%', 'Elfordulás oszlopszámtól függő értéke');
        $blc->lst('upsilon', ['Merevített szerkezet (1)' => 1, 'Kilendülő szerkezet (2)' => 2], '`upsilon:` Oszlop kihajlási fél-hullámhossza', 1);
        $blc->def('l0', $f3->_l*$f3->_upsilon, 'l_0 = upsilon*l = %% [m]', 'Kihajlási hossz');
        $blc->def('e', \H3::n1($f3->_l0*1000*$f3->_Phi), 'e_(calc) = l_0*Phi = %% [mm] = l/'.\H3::n0(($f3->_l*1000)/\H3::n1($f3->_l0*1000*$f3->_Phi)), 'Egyenértékű elmozdulás');
        $blc->note('Egyszerűsítésként `l/400` használatát engedi a szabvány');

        $fields = [
            ['name' => 'name', 'title' => 'Pillér csoport neve', 'type' => 'input', 'sum' => false],
            ['name' => 'N', 'title' => '`Delta N; sum N` [kN]', 'type' => 'input', 'sum' => false],
            ['name' => 'db', 'title' => 'darab', 'type' => 'input', 'sum' => true],
            ['name' => 'H', 'title' => 'H [kN]', 'type' => 'value', 'key' => 'H0', 'sum' => false],
            ['name' => 'SH', 'title' => '`sumH` [kN]', 'type' => 'value', 'key' => 'SH', 'sum' => true],
        ];
        if ($f3->exists('POST._imperfections')) {
            foreach ($f3->get('POST._imperfections') as $key => $value) {
                $f3->set("POST._imperfections.$key.H0", \H3::n2(\V3::numeric($value['N'])*$f3->_Phi));
                $f3->set("POST._imperfections.$key.SH", \H3::n2(\V3::numeric($value['db'])*\H3::n2(\V3::numeric($value['N'])*$f3->_Phi)));
            }
        }
        $blc->bulk('imperfections', $fields);
        $blc->txt(false, 'Vízszintes erők: `H_i = N*Phi`.');

        $blc->h1('Rövidkonzol');
        $blc->img($f3->home.'ecc/column0.jpg');

        $blc->numeric('ac', '`a_c:` Teher távolsága az oszloptól', 125, 'mm', '');
        $blc->numeric('hc', '`h_c:` Konzol magassága', 250, 'mm', '');
        $blc->numeric('FEd', '`F_(Ed):` Függőleges erő', 500, 'kN');
        $blc->numeric('HEd', '`H_(Ed):` Vízszintes erő', 50, 'kN');
        if ($f3->_HEd < 0.1*$f3->_FEd) {
            $blc->danger('Javasolt `H_(Ed,min) = 0.1*F_(Ed) = '. 0.1*$f3->_FEd.' [kN]`');
        }
        if ($f3->_HEd > 0.2*$f3->_FEd) {
            $blc->danger('Javasolt `H_(Ed,max) = 0.2*F_(Ed) = '. 0.2*$f3->_FEd.' [kN]`');
        }

        $ec->rebarList('phis1', 20, '`phi_(s1):` Felvett hurkos fővas átmérő', '');
        $blc->def('ns1', 2, 'n_(s1) = %%', 'Hurkos fővasnak két szára van.');
        $blc->note('Nagyon széles konzolba beférne 2 keskeny hurkos vas egymás melé egy sorba, de itt csak kettővel számol.');
        $blc->numeric('ns1row', '`n_(s1,row):` Alkalmazott `phi'.$f3->_phis1.'` hurkos fővas sor', 1);
        $ec->rebarList('phisw1', 10, '`phi_(sw1):` Felvett vízszintes kengyel vasátmérő', '');
        $ec->rebarList('phisw2', 12, '`phi_(sw2):` Felvett függőleges kengyel vasátmérő', '');

        $blc->def('alpha', $f3->_HEd/$f3->_FEd, 'alpha = H_(Ed)/F_(Ed) = %%', '');
        $blc->math('b = '.$f3->_b.' [mm]', 'Rövidkonzol szélessége (= oszlop `b` szélessége)');
        $blc->note('A továbbiakban feltételezzük, hogy a rövidkonzol keresztirányú *b* mérete minden vizsgált részén azonos, továbbá a *b* betonszélességen szimmetrikusan elhelyezett teherolsztó lemez keresztirányú *t* méretére teljesül a `t >= b - 2*s_0` feltétel, ahol `s_0` a húzott fővasak felső oldali betonfedésének és fél vasátmérőjének az összege. Ellenkező esetben ügyelni kell a *b* szélesség csomópontonkénti helyes felvételére és a vasalás hatékony zónában történő elhelyezésére.');

        $blc->h2('Nyomott rácsrúd ellenőrzése');

        $blc->note('Az elérhető legnagyobb teherbírás a még lehetséges, illetve megengedett legnagyobb `theta` szöghöz tartozik.');

        $blc->def('ts1', max(24 + $f3->_phis1, 2*$f3->_phis1), 't_(s1) = %% [mm]', '2 fővas sor közti távolság, 24 mm max szemcseátmérőre vagy 2D-re.');
        $blc->def('d', $f3->_hc - $f3->_cnom - $f3->_phisw2 - ($f3->_ns1row*$f3->_phis1 + ($f3->_ns1row - 1)*$f3->_ts1)/2, 'd = h_c - c_(nom) - phi_(sw2) - (n_(s1,row)*phi_(s1) + (n_(s1,row) - 1)*t_(s1))/2 = %% [mm]', 'Hatékony magasság');
//        $blc->def('d', $f3->_hc - $f3->_cnom - $f3->_phisw1 - $f3->_phis1/2, 'd = h_c - c_(nom) - phi_(sw1) - phi_(s1)/2 = %% [mm]', 'Hatékony magasság');
        $blc->def('aH', $f3->_hc - $f3->_d, 'a_H = h_c - d = %% [mm]', '');

        $blc->input('param1', 'Paraméter nyomott rácsrúd (`theta`) beállításához', 0.8, '', 'Értéke 0 és 1 között', 'min_numeric,0|max_numeric,1');

        $blc->def('z0', $f3->_param1*$f3->_d, 'z_0 ='.$f3->_param1.'*d = %% [mm]', 'Erőkar értéke');
        $z0 = $f3->_z0;

        $theta = \H3::n1(rad2deg(atan($f3->_z0/$f3->_ac)));
        if (45 < $theta && $theta < 68) {
            $blc->def('theta', $theta, 'theta = %% °', '45° és 68° között kell lennie');
        } else if ($theta <= 45) {
            $blc->danger('`theta = '.$theta.' °`: 45° és 68° között kell lennie!');
            $blc->def('theta', 45, 'theta = %% °');
        } else {
            $blc->def('theta', 68, 'theta = %% °');
            $blc->danger('`theta = '.$theta.' °`: 45° és 68° között kell lennie!');
        }

        $blc->def('z0', \H3::n0(tan(deg2rad($f3->_theta))*$f3->_ac), 'z_0 = tan(theta)*a_c = %% [mm]', 'Erőkar értéke');
        if ($f3->_ac <= $f3->_z0) {
            $blc->txt('`a_c < = z_0`, a vasbeton konzol rövidkonzolként méretezhető.');
        } else {
            $blc->danger('`a_c > z_0`, a vasbeton konzol nem méretezhető rövidkonzolként.');
        }

        if ($f3->_z0/$z0 > 1.05) {
            $blc->danger('`z_0` értékek 5%-nál jobban eltérnek!');
        }

        $blc->def('zNc', \H3::n1(($f3->_d - $f3->_ac)*cos(deg2rad($f3->_theta))), 'z_(Nc) = (d - a_c)*cos(theta) = %% [mm]', '`N_c` hatásvonala a rövidkonzol belső-alsó sarkától');
        $blc->def('NRd', \H3::n1((2*$f3->_zNc*$f3->_b*$f3->_cfcd)/1000), 'N_(Rd) = 2*z_(Nc)*b*f_(cd) = %% [kN]', 'Ferde beton rácsrúd');
        $blc->def('Nc', \H3::n1($f3->_FEd/sin(deg2rad($f3->_theta))), 'N_c = F_(Ed)/sin(theta) = %% [kN]', 'Függőleges vetületi egyensúlyi egyenlet');
        if($f3->_Nc < $f3->_NRd) {
            $blc->label('yes', 'Megfelel');
        } else {
            $blc->label('no', 'Nem felel meg, konzolmagasság növelése szükséges');
        }

        $blc->h2('Hosszvasalás ellenőrzése');
        $blc->def('Fs', \H3::n2($f3->_FEd*($f3->_ac/$f3->_z0 + 0.1)), 'F_s = F_(Ed)*(a_c/z_0 + 0.1) = %% [kN]', 'Vasalással felvett erő');
        $blc->def('Asmin', ceil($f3->_Fs/$f3->_rfyd*1000), 'A_(s,min) = F_s/(f_(yd)) = %% [mm^2]', 'Szükséges vasmennyiség');
        $blc->def('Ascalc', ceil($ec->A($f3->_phis1, $f3->_ns1*$f3->_ns1row)), 'A_(s,calc) = %% [mm^2]', 'Alkalmazaott vasmennyiség');
        $blc->label($f3->_Asmin/$f3->_Ascalc, 'Kihasználtság húzásra');
        $blc->def('rs1', ceil(3*pi()/8*$f3->_rfyd/$f3->_cfcd*$f3->_phis1*0.5), 'r_(s1) = (3pi)/8*f_(yd)/(f_(cd))*phi_(s1) = %% [mm]', 'Kerekítési sugár');
        $blc->def('lbmin', ceil(($f3->_phis1/4)*($f3->_rfyd/$f3->_cfbd)), 'l_(b,min) = (phi_(s1))/4*f_(yd)/(f_(bd)) = %% [mm]', 'Szükséges lehorgonyzási hossz, 90°-os kampó nélkül');
        $blc->def('lbmin90', ceil((0.7*$f3->_lbmin)), 'l_(b,min,90) = %% [mm]', 'Szükséges lehorgonyzási hossz 90°-os kampóval');
        $blc->def('lb', ceil($f3->_b - $f3->_cnom), 'l_b = b - c_(nom) = %% [mm]', 'Rendelkezésre álló lehorgonyzási hossz');
        if ($f3->_lbmin < $f3->_lb) {
            $blc->label('yes', 'Nem szükséges a kampózás');
        } else if ($f3->_lbmin >= $f3->_lb && $f3->_lb > $f3->_lbmin90) {
            $blc->label('yes', '90°-os kapmózás szükséges!');
        } else {
            $blc->label('no', 'Lehorgonyzás az oszlopban!');
        }

        $blc->h2('Kengyelezés');
        $blc->txt('A vízszintes kengyelezés nem hagyható el a nyomott rácsrúd felhasadásának megakadályozásához.');
        $blc->numeric('nsw1', '`n_(sw1):` Vízszintes zárt `phi'.$f3->_phisw1.'` kengyelek száma', floor(($f3->_ac + 50)/50), '');
        $blc->def('Asw1', floor($f3->_nsw1*$ec->A($f3->_phisw1, 2)), 'A_(sw1) = %% [mm^2]', 'Alkalamazott vízszintes kengyel keresztmetszet');
        $blc->def('Asw1min', ceil(0.5*$f3->_Ascalc), 'A_(sw1,min) = 0.5*A_(s,calc) = %% [mm^2]', 'Szükséges vízszintes kengyel keresztmetszet');
        $blc->note('A Nemzeti Melléklet a 0.25 szorzót 0.5-re módosítja.');
        $blc->label($f3->_Asw1min/$f3->_Asw1, 'kengyel kihasználtság');
        if ($f3->_ac > 0.5*$f3->_hc) {
            $blc->md('`a_c > 0.5*h_c:` Függőleges kengyelezés nem hagyható el!');
        } else {
            $blc->md('`a_c < 0.5*h_c:` Függőleges kengyelezés elhagyható.');
        }
        $blc->numeric('nsw2', '`n_(sw2):` Függőleges zárt `phi'.$f3->_phisw2.'` kengyelek száma', 2, '');
        $blc->def('Asw2', floor($f3->_nsw2*$ec->A($f3->_phisw2, 2)), 'A_(sw2) = %% [mm^2]', 'Alkalamazott függőleges kengyel keresztmetszet');
        $blc->def('Asw2min', ceil(0.5*($f3->_FEd/$f3->_rfyd)*1000), 'A_(sw2,min) = 0.5*F_(Ed)/(f_(yd)) = %% [mm^2]', 'Szükséges függőleges kengyel keresztmetszet');
        $blc->label($f3->_Asw2min/$f3->_Asw2, 'függőleges kengyel kihasználtság');
        $blc->note('Vsz. kengyelek kampói az oszlopba kerüljenek. A függ. kengyelek kampói a rk. alsó felén legyenek. A függ. kengyelek vegyék körbe a vővasat és vsz. kengyeleket.');

        $blc->def('k1', 1, 'k_1 = %%');
        $blc->def('upsilon', 1 - $f3->_cfck/250, 'upsilon = 1 - f_(ck)/250 = %%');
        $blc->def('sigmaRdmax', $f3->_k1*$f3->_upsilon*$f3->_cfcd, 'sigma_(Rd,max) = k_1*upsilon*f_(cd) = %% [N/(mm^2)]', 'Helyi nyomószilárdság');
        $blc->note('[1] csomópont mindhárom lapján egyforma nyomófeszültségek lépnek fel, a legmeredekebb `theta` szögnél megegyezik a fenti helyi nyomószilárdsággal.');
        $blc->def('x', $f3->_FEd/($f3->_b*$f3->_sigmaRdmax)*1000, 'x = F_(Ed)/(b*sigma_(Rd,max)) = %% [mm]');
        $qa = -0.5;
        $qb = $f3->_d;
        $qc = -1*(0.5*pow($f3->_x, 2) + $f3->_ac*$f3->_x + $f3->_alpha*$f3->_aH*$f3->_x);
        $blc->def('y1', $ec->quadratic($qa, $qb, $qc, 'root1'), 'y1 = %% [mm]');
        $blc->def('y2', $ec->quadratic($qa, $qb, $qc, 'root2'), 'y2 = %% [mm]');
        $blc->def('theta', rad2deg(atan($f3->_x/$f3->_y1)), 'theta = %%°');
        // itt: van valid theta, fölső számítás törölhető; z0 számítása jön itt a fenti az jó képlet
    }
}
