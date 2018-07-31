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
    
    .row {
        display: flex;
    }
}
</style>
<div class="row">
    <div class="col-12">    
        <h1 class="text-center text-uppercase droid">Megbízási szerződés</h1>
    </div>
</div>
    
<div class="row">
    <div class="col-12">    
        <h2 class="droid">1. Szerződő felek</h2>
    </div>
</div>
    
    <div class="row">
        <div class="col-11 offset-1">
            <h3 class="droid">1.1. Megbízó:</h3>
        </div>
    </div>
    
        <div class="row">
            <div class="col-8 offset-4">    
                <strong>'.$f3->_megbizoNev.'</strong>
            </div>
        </div>
        
        <div class="row">
            <div class="col-2 offset-2 text-right">    
                Cím:
            </div>
            <div class="col-8 d-block">    
                '.$f3->_megbizoCim.'
            </div>
        </div>
        
        <div class="row">
            <div class="col-2 offset-2 text-right">    
                Adószám:
            </div>
            <div class="col-8">    
                '.$f3->_megbizoAdoszam.'
            </div>
        </div>
        
        <div class="row">
            <div class="col-2 offset-2 text-right">    
                Bankszámlaszám:
            </div>
            <div class="col-8">    
                '.$f3->_megbizoBankszamla.'
            </div>
        </div>
        
        <div class="row">
            <div class="col-10 offset-2 text-right">    
                A továbbiakban: <strong>Megbízó</strong>
            </div>
        </div>
        
    <div class="row">
        <div class="col-11 offset-1">
            <h3 class="droid">1.2. Megbízott:</h3>
        </div>
    </div>
    
        <div class="row">
            <div class="col-8 offset-4">    
                <strong>'.$f3->_megbizottNev.'</strong>, KATA szerinti egyéni vállalkozó
            </div>
        </div>
        
        <div class="row">
            <div class="col-2 offset-2 text-right">    
                Cím:
            </div>
            <div class="col-8">    
                '.$f3->_megbizottCim.'
             </div>
        </div>
        
        <div class="row">
            <div class="col-2 offset-2 text-right">    
                Adószám:
            </div>
            <div class="col-8">    
                '.$f3->_megbizottAdoszam.'
            </div>
        </div>
        
        <div class="row">
            <div class="col-2 offset-2 text-right">    
                Bankszámlaszám:
            </div>
            <div class="col-8">    
                '.$f3->_megbizottBankszamla.'
            </div>
        </div>
        
        <div class="row">
            <div class="col-10 offset-2 text-right">    
                A továbbiakban: <strong>Megbízott</strong>
            </div>
        </div>
    
<div class="row">
    <div class="col-12">    
        <h2 class="droid">2. Szerződés tárgya</h2>
    </div>
</div>    

    <div class="row">
        <div class="col-11 offset-1">
            <p><strong><em>'.$f3->_targy.'</em></strong></p>
        </div>
    </div>

<div class="row">
    <div class="col-12">    
        <h2 class="droid">3. A megbízás határideje</h2>
    </div>
</div>    

    <div class="row">
        <div class="col-11 offset-1">
            <p><strong><em>'.$f3->_hatarido.'</em></strong></p>
        </div>
    </div>
    
<div class="row">
    <div class="col-12">    
        <h2 class="droid">4. A megbízás díja</h2>
    </div>
</div>    

    <div class="row">
        <div class="col-11 offset-1">
            <p>
                <strong>4.1.</strong> A Megbízottat a 2. pontban megjelölt munkáért az alábbi megbízási díj illeti meg:
            </p>
            <p class="ml-5">
                Megbízási díj: <strong>'.$f3->_dijSzam.' Ft</strong> (azaz '.$f3->_dijBetu.' forint), ÁFA alanyi mentes.
            </p>
            <p>
                <strong>4.2.</strong> A megbízási díj tartalmaz valamennyi, Megbízott által felszámítható költséget, így további költségtérítésnek – a Felek eltérő rendelkezései hiányában - nincs helye.
            </p>      
            <p>
                <strong>4.3.</strong> 4.3.	Megbízott számláját a megbízás utolsó napjának megfelelő teljesítési időponttal jogosult benyújtani.
            </p>     
        </div>
    </div> 

<div class="row">
    <div class="col-12">    
        <h2 class="droid">5. A szerződés érvényessége</h2>
    </div>
</div>   
    <div class="row">
        <div class="col-11 offset-1">
            <p>
                <strong>5.1.</strong> A szerződés a Felek cégszerű aláírásakor lép érvénybe.
            </p>
            <p>
                <strong>5.2.</strong> A szerződést a Felek csak írásban, közös akarattal módosíthatják.
            </p>
        </div>
    </div> 
    
<div class="row">
    <div class="col-12">    
        <h2 class="droid">6. Teljesítés</h2>
    </div>
</div>   
    <div class="row">
        <div class="col-11 offset-1">
            <p>
                <strong>6.1.</strong>Amennyiben a Megbízónak felróható okból hiúsul meg Megbízott bármely kötelezettségének teljesítése, a Megbízott jogosult a szolgáltatást időarányosan elszámolni, és az időarányos díjat Megbízónak leszámlázni.
            </p>
        </div>
    </div> 
    
<div class="row">
    <div class="col-12">    
        <h2 class="droid">7. A szerződés felmondása</h2>
    </div>
</div>   
    <div class="row">
        <div class="col-11 offset-1">
            <p>
                <strong>7.1.</strong> Felek jogosultak a szerződés felmondására, ha másik Fél fizetésképtelenné válik, csődbe megy, felszámolják vagy feloszlatják.
            </p>
            <p>
                <strong>7.2.</strong> Felek jogosultak a szerződést azonnali hatállyal felbontani, ha a másik Fél nyilvánvalóan durva, vagy ismételt szerződésszegést követ el, vagy nyilvánvalóan megszegi a jelen szerződésre alkalmazandó jogszabályokat és ezeket nem orvosolja haladéktalanul.
            </p>
        </div>
    </div> 
    
<div class="row">
    <div class="col-12">    
        <h2 class="droid">8. Védelmi- és információs jog</h2>
    </div>
</div>   
    <div class="row">
        <div class="col-11 offset-1">
            <p>
                <strong>8.1.</strong> Megbízó köteles a Megbízottat a tevékenysége sikeres kifejtéséhez szükséges információkhoz juttatni, a szükséges adatokat rendelkezésre bocsátani.
            </p>
            <p>
                <strong>8.2.</strong> Megbízott köteles a közreműködése következtében tudomására jutott adatokat – így különösen az üzleti titok kategóriájában tartozó információkat – bizalmasan kezelni, azokat harmadik személy részére nem szolgáltathatja ki.
            </p>
        </div>
    </div> 
    
<div class="row">
    <div class="col-12">    
        <h2 class="droid">9. Fizetési feltételek</h2>
    </div>
</div>   
    <div class="row">
        <div class="col-11 offset-1">
            <p>
                <strong>9.1.</strong> Megbízó köteles Megbízott számlája alapján a megbízási díjat '.$f3->_dijHatarido.' napon belül Megbízott <strong>'.$f3->_megbizottBankszamla.'</strong> számú bankszámlájára átutalni.
            </p>
        </div>
    </div> 

<div class="row">
    <div class="col-12">    
        <h2 class="droid">10. Egyéb feltételek</h2>
    </div>
</div>   
    <div class="row">
        <div class="col-11 offset-1">
            <p>
                <strong>10.1.</strong> Jelen szerződésben nem szabályozott kérdésekben a Polgári Törvénykönyv megbízásra vonatkozó rendelkezései az irányadók.
            </p>
        </div>
    </div>
    
<div class="row mt-5">
    <div class="col-12">    
        '.$f3->_szerzodesHelye.', '.$f3->_szerzodesIdo.'
    </div>
</div>   

<div class="row mt-5">
    <div class="col-3 offset-2 mp-5">    
        <p class="text-center border-top"><strong><em>Megbízó</em></strong></p>
    </div>
    <div class="col-3 offset-2 mp-5">    
        <p class="text-center border-top"><strong><em>Megbízott</em></strong></p>
    </div>
</div>  
        ';
        $blc->html($html);
    }
}
