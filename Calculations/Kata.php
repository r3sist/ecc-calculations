<?php

namespace Calculation;

Class Kata extends \Ecc
{

    public function calc($f3)
    {
        $blc = \Blc::instance();

        $blc->region0('adatok', 'SZerződés adatok');
            $blc->input('megbizoNev', 'Megbízó név');
            $blc->input('megbizoCim', 'Megbízó címe');
            $blc->input('megbizoAdoszam', 'Megbízó adószáma');
            $blc->input('megbizoBankszamla', 'Megbízó bankszámla száma');

            $blc->input('megbizottNev', 'Megbízott név');
            $blc->input('megbizottCim', 'Megbízott címe');
            $blc->input('megbizottAdoszam', 'Megbízott adószáma');
            $blc->input('megbizottBankszamla', 'Megbízott bankszámla száma');

            $blc->area('targy', 'Szerződés tárgya');
            $blc->input('hataido', 'Megbízás határideje');
            $blc->input('dijSzam', 'Megbízási díj számmal');
            $blc->input('dijBetu', 'Megbízási díj betűvel');
            $blc->input('dijHatarido', 'Megbízási díj átutalása ennyi napon belül', 15);

            $blc->input('szerzodesIdo', 'Szerződés keltének dátuma');
            $blc->input('szerzodesHelye', 'Szerződés helye');

            $blc->input('teljesitesIdo', 'Teljesítés várható dátuma');
        $blc->region1('adatok');

        $html = '
<h1>MEGBÍZÁSI SZERZŐDÉS</h1>
<h2>1. Szerződő felek</h2>
<h3>1.1. Megbízó</h3>
        ';
        $blc->html($html);
    }
}
