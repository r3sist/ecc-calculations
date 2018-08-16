<?php

namespace Calculation;

Class Kata extends \Ecc
{

    public function calc($f3)
    {
        $blc = \Blc::instance();

        $blc->region0('adatok', 'Szerződés adatok');
            $blc->input('megbizoNev', '``Megbízó név');
            $blc->input('megbizoCim', '``Megbízó címe');
            $blc->input('megbizoAdoszam', '``Megbízó adószáma');
            $blc->input('megbizoBankszamla', '``Megbízó bankszámla száma');

            $blc->input('megbizottNev', '``Megbízott név');
            $blc->input('megbizottCim', '``Megbízott címe');
            $blc->input('megbizottAdoszam', '``Megbízott adószáma');
            $blc->input('megbizottBankszamla', '``Megbízott bankszámla száma');

            $blc->area('targy', '``Szerződés tárgya');
            $blc->input('hatarido', '``Megbízás határideje');
            $blc->input('dijSzam', '``Megbízási díj számmal');
            $blc->input('dijBetu', '``Megbízási díj betűvel');
            $blc->input('dijHatarido', '``Megbízási díj átutalása ennyi napon belül', 15);

            $blc->input('szerzodesIdo', '``Szerződés keltének dátuma');
            $blc->input('szerzodesHelye', '``Szerződés helye', 'Budapest');

            $blc->input('teljesitesIdo', '``Teljesítés várható dátuma');
        $blc->region1('adatok');

        $html = '
<style>
@media print {
    h1 {
        page-break-before: always;
    }

    h2 {
        font-size: 1.75rem !important;
        page-break-after: avoid;
    }
    
    h3 {
        font-size: 1.2rem !important;
        page-break-after: avoid;
    }
}

h2 {
        font-size: 1.75rem !important;
}

h3 {
        font-size: 1.2rem !important;
}
</style>
<input name="_noFooter" id="_noFooter" type="hidden" value="1">
<div class="row">
    <div class="col-12">    
        <h3>Számlára írni:</h3>
        <p>Teljesítés: <strong>'.$f3->_teljesitesIdo.'</strong></p>
        <p>Számla kelte: ma - <strong>'.date('Y.m.d.', time()).'</strong></p>
        <p>Teljesítés határidő: ma+15nap - <strong>'.date('Y.m.d.', time() + 60*60*24*15).'</strong></p>
        
        <h1 class="text-center text-uppercase droid">Megbízási szerződés</h1>
        
        <h2 class="droid pt-3 pb-2">1. Szerződő felek</h2>
        
        <h3 class="droid ml-2 pt-3 pb-2">1.1. Megbízó:</h3>
        <p class="ml-4">        
            <strong>'.$f3->_megbizoNev.'</strong>
        </p>
        <table class="ml-4 w-100">
            <tr>
                <td class="w-25 align-left">Cím:</td>
                <td>'.$f3->_megbizoCim.'</td>
            </tr>
            <tr>
                <td class="w-25 align-left">Adószám:</td>
                <td>'.$f3->_megbizoAdoszam.'</td>
            </tr>
            <tr>
                <td class="w-25 align-left">Bankszámlaszám:</td>
                <td>'.$f3->_megbizoBankszamla.'</td>
            </tr>
            <tr>
                <td class="w-25 align-left">A továbbiakban:</td>
                <td><strong>Megbízó</strong></td>
            </tr>
        </table>

        <h3 class="droid ml-2 pt-3 pb-2">1.2. Megbízott:</h3>
        <p class="ml-4">        
            <strong>'.$f3->_megbizottNev.'</strong>, KATA szerinti egyéni vállalkozó
        </p>
        <table class="ml-4 w-100">
            <tr>
                <td class="w-25 align-left">Cím:</td>
                <td>'.$f3->_megbizottCim.'</td>
            </tr>
            <tr>
                <td class="w-25 align-left">Adószám:</td>
                <td>'.$f3->_megbizottAdoszam.'</td>
            </tr>
            <tr>
                <td class="w-25 align-left">Bankszámlaszám:</td>
                <td>'.$f3->_megbizottBankszamla.'</td>
            </tr>
            <tr>
                <td class="w-25 align-left">A továbbiakban:</td>
                <td><strong>Megbízott</strong></td>
            </tr>
        </table>

        <h2 class="droid pt-3 pb-2">2. Szerződés tárgya</h2>
        <p class="ml-4">
            <strong><em>'.$f3->_targy.'</em></strong>
        </p>
        
        <h2 class="droid pt-3 pb-2">3. A megbízás határideje</h2>
        <p class="ml-4">
            <strong>'.$f3->_hatarido.'</strong>
        </p>
        
        <h2 class="droid pt-3 pb-2">4. A megbízás díja</h2>
        <h3 class="droid ml-2">4.1.</h3>
        <p class="ml-4">
           A Megbízottat a 2. pontban megjelölt munkáért az alábbi megbízási díj illeti meg:
        </p>
        <p class="ml-5">
            Megbízási díj: <strong>'.$f3->_dijSzam.' Ft</strong> (azaz '.$f3->_dijBetu.' forint), ÁFA alanyi mentes.
        </p>
        <h3 class="droid ml-2">4.2.</h3>
        <p class="ml-4">
            A megbízási díj tartalmaz valamennyi, Megbízott által felszámítható költséget, így további költségtérítésnek – a Felek eltérő rendelkezései hiányában - nincs helye.
        </p>
        <h3 class="droid ml-2">4.3.</h3>      
        <p class="ml-4">
            Megbízott számláját a megbízás utolsó napjának megfelelő teljesítési időponttal jogosult benyújtani.
        </p>
        <section>        
            <h2 class="droid pt-3 pb-2">5. A szerződés érvényessége</h2>
            <h3 class="droid ml-2">5.1.</h3>
            <p class="ml-4">
                A szerződés a Felek cégszerű aláírásakor lép érvénybe.
            </p>
            <h3 class="droid ml-2">5.2.</h3>
            <p class="ml-4">
               A szerződést a Felek csak írásban, közös akarattal módosíthatják.
            </p>
        </section>
        
        <h2 class="droid pt-3 pb-2">6. Teljesítés</h2>
        <h3 class="droid ml-2">6.1.</h3>
        <p class="ml-4">
            Amennyiben a Megbízónak felróható okból hiúsul meg Megbízott bármely kötelezettségének teljesítése, a Megbízott jogosult a szolgáltatást időarányosan elszámolni, és az időarányos díjat Megbízónak leszámlázni.
        </p>
        
        <section>        
            <h2 class="droid pt-3 pb-2">7. A szerződés felmondása</h2>
            <h3 class="droid ml-2">7.1.</h3>
            <p class="ml-4">
                Felek jogosultak a szerződés felmondására, ha másik Fél fizetésképtelenné válik, csődbe megy, felszámolják vagy feloszlatják.
            </p>
            <h3 class="droid ml-2">7.2.</h3>
            <p class="ml-4">
                Felek jogosultak a szerződést azonnali hatállyal felbontani, ha a másik Fél nyilvánvalóan durva, vagy ismételt szerződésszegést követ el, vagy nyilvánvalóan megszegi a jelen szerződésre alkalmazandó jogszabályokat és ezeket nem orvosolja haladéktalanul.
            </p>
        </section>
        
        <h2 class="droid pt-3 pb-2">8. Védelmi- és információs jog</h2>
        <h3 class="droid ml-2">8.1.</h3>
        <p class="ml-4">
            Megbízó köteles a Megbízottat a tevékenysége sikeres kifejtéséhez szükséges információkhoz juttatni, a szükséges adatokat rendelkezésre bocsátani.
        </p>
        <h3 class="droid ml-2">8.2.</h3>
        <p class="ml-4">
            Megbízott köteles a közreműködése következtében tudomására jutott adatokat – így különösen az üzleti titok kategóriájában tartozó információkat – bizalmasan kezelni, azokat harmadik személy részére nem szolgáltathatja ki.
        </p>
        
        <h2 class="droid pt-3 pb-2">9. Fizetési feltételek</h2>
        <h3 class="droid ml-2">9.1.</h3>
        <p class="ml-4">
            Megbízó köteles Megbízott számlája alapján a megbízási díjat '.$f3->_dijHatarido.' napon belül Megbízott <strong>'.$f3->_megbizottBankszamla.'</strong> számú bankszámlájára átutalni.
        </p>
        
        <h2 class="droid pt-3 pb-2">10. Egyéb feltételek</h2>
        <h3 class="droid ml-2">10.1.</h3>
        <p class="ml-4">
            Jelen szerződésben nem szabályozott kérdésekben a Polgári Törvénykönyv megbízásra vonatkozó rendelkezései az irányadók.
        </p>
        
        <p class="" style="margin-top: 150px;">
            '.$f3->_szerzodesHelye.', '.$f3->_szerzodesIdo.'
        </p>
        
        <p class="text-center" style="margin-top: 150px;">
            <span class="w-25 border-top px-5" style="margin-right: 200px;"><strong><em>Megbízó</em></strong></span>
            <span class="w-25 border-top px-5"><strong><em>Megbízott</em></strong></span>
        </p>
        
        <h1 class="text-center text-uppercase droid mb-5">Teljesítésigazolás</h1>
        
        <table class="w-100 mt-5">
            <tr>
                <td class="w-25 align-left">Megbízó:</td>
                <td><strong>'.$f3->_megbizoNev.'</strong></td>
            </tr>
            <tr>
                <td class="w-25 align-left">Cím:</td>
                <td>'.$f3->_megbizoCim.'</td>
            </tr>
        </table>
        
        <table class="w-100 mt-3">
            <tr>
                <td class="w-25 align-left">Megbízott:</td>
                <td><strong>'.$f3->_megbizottNev.'</strong>, KATA szerinti egyéni vállalkozó</td>
            </tr>
            <tr>
                <td class="w-25 align-left">Cím:</td>
                <td>'.$f3->_megbizottCim.'</td>
            </tr>
        </table>
        
        <p class="mt-3">
            Tárgy: <em>'.$f3->_targy.'</em>
        </p>
        
        <p class="mt-3">
            Megbízott mai napon leszállította, elvégezte tárgyi munkát. Megbízó elismeri a teljesítést.
            <br>A teljesítés alapján Megbízott <strong>'.$f3->_dijSzam.' Ft</strong>, ÁFA alanyi mentes számla benyújtására jogosult.
        </p>
        
        <p class="" style="margin-top: 150px;">
            '.$f3->_szerzodesHelye.', '.$f3->_teljesitesIdo.'
        </p>
        
        <p class="text-center" style="margin-top: 150px;">
            <span class="w-25 border-top px-5"><strong><em>Megbízó</em></strong></span>
        </p>
        
    </div>
</div>
        ';
        $blc->html($html);
    }
}
