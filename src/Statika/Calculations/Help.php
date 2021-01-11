<?php declare(strict_types = 1);
/**
 * UHelp & documentation of Statika framework as a claculation class
 * (c) Bence VÁNKOS | https://structure.hu | https://github.com/r3sist/ecc-calculations
 */

namespace Statika\Calculations;

use Statika\EurocodeInterface;

Class Help
{
    /**
     * @param Ec $ec
     */
    public function calc(EurocodeInterface $ec): void
    {
        $md = '
### Menü

A dokumentum az oldalsó lenyíló menüvel vezérelhető.

1. *Futtatás* parancs
2. Dokumentum menü
3. Mentett dokumentumok listája

![menü](https://structure.hu/ecc/helpMenu.jpg)
        
### Számítások futtatása

> A Menü *Futtatás* gombbal vagy az `[⏎ Enter]` billentyűparanccsal futtatható le a számítás a módosított paraméterekkel. 

Jelölőnégyzet vagy lenyíló menü esetén automatikusan lefut.

### Számítások mentése és betöltése

> Menü *Mentés szerverre* vagy `[s]`

A fejléc alatti vízszintes vonalra írt néven a számítást (eredményeket és paramétereket) el lehet menteni. 

+ Azonos nevűt felülírja
+ Név nélkülit `0` néven menti
+ A számításokat felhasználói fiókonként tárolja a rendszer

![menü](https://structure.hu/ecc/helpSave.jpg)

Adott számításnál ezek a mentett dokumentumok előhívhatók:

![menü](https://structure.hu/ecc/helpLoad.jpg)

Lásd még: Menü *Mentések kezelése* -> [rendező lapot](https://structure.hu/ecc/cloud).

### Jegyzetek

> Menü *Jegyzetek* vagy `[n]`

A legtöbb számításhoz tartoznak megjegyzések (források, magyarázatok).

### Pecsét

> Menü *Pecsét* vagy `[p]`

A számítás fejlécéhez pecsét kérhető.

### Számítási komment

> Menü *Megjegyzés szerkesztése* vagy `[r]`

A számítás fejlécéhez komment blokk fűzhető. (El kell menteni hozzá a számítást.)

### Dokumentálás: MS Word export

> Menü *MS Word export*

Az összecsukott blokkok (összecsukott fejezetek, régiók, megjegyzések) nem kerülnek exportálásra.

Az exportált tartalom egyszerűsített - a definíció magyarázatok jellemzően nem kerülnek exportálsára.

Exportálás után a számítást újra kell futtatni, ha az (SVG) ábrákat tartalmazott.

### Dokumentálás: Nyomtatás

> Menü *Nyomtatás*

Az összecsukott blokkok (összecsukott fejezetek, régiók, megjegyzések) nem kerülnek nyomtatásra.

A nyomtatási kép eltér a böngésző által megjelenítetthez képest.

### Txt kimenet

> Manü *Eredménynapló* vagy `[l]`

Egyes számítások, ha támogatják a funkciót, gyors eredmény összefoglalót tudnak mutatni.

Ez a kimenet a lementett fájlokban is tárolásra kerül.

### Mentett számítás letöltése, feltöltése - Rendező

> Menü *Mentések kezelése*

> Menü *Export*

[https://structure.hu/ecc/cloud - rendező lapon](https://structure.hu/ecc/cloud) minden a felhasználó által mentett számítás listázható. 

Ezen a lapon a mentések letölthetők *.ecc.txt* szöveges fájlként. Ha a számítás támogatja az eredménynaplózást, 
akkor az eredményeket is elmenti, egyébként csak a bemenő paramétereket:

![menü](https://structure.hu/ecc/helpTxt.jpg)

Ugyanezen a lapon az *.ecc.txt* fájlok visszatölthetők.

Archivált mentések nem listázódnak a számítást vezérlő menüben.

### Számítások fejlesztése, forráskódja

> Menü *Forrás*

A számítási dokumentumok nyílt forráskódúak: [GitHub repository](https://github.com/r3sist/ecc-calculations) - bárki által fejleszthetők Githubon keresztül vagy hagyományos [Git verziókezelő munkafolyamatokkal](https://rogerdudler.github.io/git-guide/).

Röviden: minden számítás egy [PHP](https://hu.wikipedia.org/wiki/PHP) [osztály](https://hu.wikipedia.org/wiki/Objektumorient%C3%A1lt_programoz%C3%A1s). 
[DI](https://hu.wikipedia.org/wiki/A_f%C3%BCgg%C5%91s%C3%A9g_befecskendez%C3%A9se) miatt, ha a `calc()` metódus implementálva van, 
a számítási osztály a továbbiakban keretrendszer független. 
(Az alap kinézethez és funkciókhoz azonban legegyszerűbb használni a belső *blokk építő* segéd metódusokat.)
Kiindulásnak egy egyszerű számítás a belső keretrendszer felhasználásával: [CK lap teherbírása](https://github.com/r3sist/ecc-calculations/blob/master/Calculations/Ck.php).

A `calc(Base $f3, Blc $blc, Ec $ec)` metódus a paramétereit megkapja, melyek rendre:

+ [Fat-Free Framework](https://fatfreeframework.com) külső `Base` osztály, globális változó tároláshoz
+ [Blokk kezelő belső `Blc` osztály](https://github.com/r3sist/ecc-calculations/blob/master/API.md#Blc): tulajdonképpen ennek a segítségével kérhetők az egyes számítási és megjelenítési blokkok - ez adja a számítási dokumentum absztarkt logikáját
+ [Eurocode belső `Ec` osztály](https://github.com/r3sist/ecc-calculations/blob/master/API.md#Ec) a statika vonatkozású funkciókhoz

A számításokhoz használt további belső könyvtárak:

+ [SVG osztály](https://github.com/r3sist/ecc-calculations/blob/master/API.md#SVG-1)
+ Számformázáshoz: [H3::ni](https://github.com/r3sist/h3/blob/master/API.md#n0)

A matematikai kifejezéseket [ASCIIMath](http://asciimath.org/) formában kell megírni. Használt határolójel: `$ .. $` szabad szövegeknél. Szabad szövegek jellemzően elfogadnak [Markdown](https://en.wikipedia.org/wiki/Markdown) formázást.

### Regisztráció

[Reg](https://structure.hu/signup) oldalon, céhes email címmel, `voyager3` meghívóval.
        
';
        $ec->txt('&nbsp;');
        $ec->md($md);
    }
}
