# Használati útmutató

Az ***Ecc*** egy webes számítási dokumentumokat kezelő szerveroldali keretrendszer. 

Egy dokumentum beviteli mező, kép, szöveg, matematikai kifejezés stb. blokkokból épül fel. A rendszer része egy Eurocode alapú függvény- és adatgyűjtemény is.

A dokumentumok forráskódja megtekinthető: [bitbucket/resist/ecc-calculations](https://bitbucket.org/resist/ecc-calculations/src) 

## Számítások futtatása

Dokumentumvezérlő *Futtatás* parancsára vagy *Enter* billentyűparancsra a számítás lefut. Automatikusan lefut továbbá bizonyos beviteli mezők változatásakor.

## Számítások mentése szerverre

A módosított beviteli mezők elmenthetők a szerverre a Dokumentumvezérlő *Mentés szerverre* parancsára vagy az *s* billentyűparancsra.

A rendszer a legfelső sorba (vízszintes vonal a cím alatt) írt *Projekt néven* fogja elmenteni a paramétereket.

A mentés a bejelentkezett felhasználónak lesz csak elérhető. Mentés betölthető vagy törölhető a [kezdőlapi](https://structure.hu/calc) listázásnál.

## Nyomtatás, PDF mentés

Nyomtatás és PDF mentés elérhető a dokumentumvezérlőből. 

Az értesítési-, tartalomjegyzék- és összecsukott blokkok (összecsukott fejezetek, régiók, megjegyzések) nem kerülnek nyomtatásra.

A PDF mentés szerver oldalon történik, így egyes blokkok (pl. beviteli mezők) helyettesítésre kerülnek. 

## Jegyzetek

Megjegyzések a számításhoz - előhívható a dokumentumvezérlőből vagy a *n* billetyűparancsra.

## Pecsét

Dokumentumvezérlőből vagy *p* billentyűparancsra előhívható tervpecsét mentett dokumentumokhoz. 

## Billentyűparancsok

+ *Enter*: futtatás
+ *n*: jegyzetek mutatása/rejtése
+ *s*: dokuemntum mentése szerverre
+ *p*: pecsét mutatása
+ *t* lap teteje

---

---

# Ecc Calculations

**Native classes of Eurocode based calculations for structural design on [structure.hu](https://structure.hu)**

(c) 2018 Bence VÁNKOS &nbsp;&nbsp;&nbsp;&nbsp; ![email](https://structure.hu/img/emailB.png)

+ **Repository:** [bitbucket/resist/ecc-calculations](https://bitbucket.org/resist/ecc-calculations)
+ **Issue tracker:** [bitbucket/resist/ecc-calculations/issues](https://bitbucket.org/resist/ecc-calculations/issues)
+ **Changelog:** - [bitbucket/resist/ecc-calculations/commits](https://bitbucket.org/resist/ecc-calculations/commits)
+ **License:** CDDL-1.0 Copyright 2018 Bence VÁNKOS

--- 

## General notes for development

+ ***Ecc*** (actually *Ecc()* class) is the PHP framework that runs, renders, updates etc. calculations (in fact the calculation classes' *calc()* method).
+ *Ecc* (and whole website) uses [Fatfree Framework](https://fatfreeframework.com) as engine, it's autoloaded and Fatfree singleton object is available everywhere globally as **`$f3`**. *Hive* is *f3*'s global storage.
+ ***Blc()*** class is part of the *Ecc* framework and it's supposed to render GUI elements like inputs, definitions and other blocks. Calculation methods are built up from these blocks, but may contain vanilla PHP code too.
+ ***Ec()*** class contains Eurocode specific methods, datas or predefined GUI elements. Using this class is optional. ***EcNSEN()*** is similar to *Ec()* but contains Eurocode Norwegian National Annex specific methods. The two classes can be used parallelly.
+ [AsciiMath](http://asciimath.org/) **math expressions** are compiled by [MathJax](https://www.mathjax.org), write them between with code marks anywhere (needs to be escaped in Markdown regions). e.g. \\`x=1\\`.
+ HTML tags are cleaned generally.
+ [Markdown](https://en.wikipedia.org/wiki/Markdown) is enabled generally.
+ Toggled GUI elements are not printed.
+ Regions always need two lines of code using the same identifier name: starter and end blocks (marked in method name by 0 and 1)
+ `$help` parameter creates small muted text block generally. Markdown is enabled, renders only: `br, em, i, strong, b, a, code` tags.

---

## Initialization / Boilerplate calculation class

Example of a calculation class

```php

namespace Calculation;

// This class will extend Ecc framework
Class Boilerplate extends \Ecc
{
    // This function will be called by Ecc, parameter is Fatfree Framework's singleton
    public function calc($f3)
    {
        // Load Eurocode
        $ec = \Ec::instance();
        
        // Load Blc
        $blc = \Blc::instance();
        
        // Load LavaChart if needed
        $lava = new \Khill\Lavacharts\Lavacharts;

        // Business logic via Blc
        $blc->txt('Hello World!');
        
        // Custom PHP code
        if (1 == 1) {
            $f3->var = 1;
        }
        
        // Business logic with mixed Blc and custom code
        $blc->def('x', $f3->var + 1, 'x = %%', 'Súgó');
        $blc->txt($f3->_x, 'Autosaved x variable in Hive');
    }
}
```

---

## Ecc framework blocks and regions

### *input* block

General input field that defines variable in *f3 Hive*.

`$blc->input($variableName, $title, [$defaultValue, $unit, $help])`

Notes:

+ `$variableName` creates global variable in f3 Hive with underscore: e.g. `$f3->_variableName`
+ `$variableName` is added to title by default, if `title` does not conatin additional *math expression*

### *boo* block

Boolean checkbox input field that defines variable in *f3 Hive*.

`$blc->boo($variableName, $title, [$defaultValue, $help])`

Notes:

+ `$variableName` creates global variable in f3 Hive with underscore: e.g. `$f3->_variableName`
+ values are `1` or `0`

### *lst* block

List / Selection menu that defines variable in *f3 Hive*.

`$blc->lst($variableName, $source, $title, [$defaultValue, $help])`

Notes:

+ `$variableName` creates global variable in f3 Hive with underscore: e.g. `$f3->_variableName`
+ `$source` is associative array: e.g. `['First GUI member' => 0, 'Second GUI member' => 1]`
+ returned value is `$source`'s 2nd column
+ `$defaultValue` is referenced by `$source` 2nd column

### *area* block

Textarea input field that defines variable in *f3 Hive*.

`$blc->area($variableName, $title, [$default, $help])`

+ `$variableName` creates global variable in f3 Hive with underscore: e.g. `$f3->_variableName`

### *def* block

Variable definition block with math expression support.

`$blc->def($variableName, $result, $math, [$help])`

Notes:

+ `$variableName` creates global variable in f3 Hive with underscore: e.g. `$f3->_variableName`
+ `$result` may contain any PHP calculation that will be stored in `$f3->_variableName`
+ `$math` is mathematical expression without \` \` code marks. *%%* is replaced by `$result`

### *hr* block

Simple separator line

`$blc->hr();`

### *h1*, *h2*, *h3* blocks

Headings with lead paragraphs as help.

`$blc->h1(head, [help]);`

`$blc->h2(head, [help]);`

`$blc->h3(head, [help]);`

Notes:

+ *h1* can be toggled on GUI.

### *math* block

Simple math text for mathematical expressions, compiled by MathJax.

`$blc->math($math, [$help]);`

Notes: 

+ In `$math` define expression without code marks \` \`
+ *%%%* adds vertical spacing.
+ This block does not define variable.

### *txt* block

Simple text block

`$blc->txt([$text, $help])`

Notes:

+ Both `$txt` and `$help` are optional, since muted helper text can be generated

### *html* block

Simple HTML5 block

`$blc->txt($html)`

### *md* block

Simple Markdown block

`$blc->txt($md)`

### *note* block

Hidden by default notes and foot-notes for users.

`$blc->note($text)`

Notes:

+ `$note` may contain math expression.

### *pre* block

Preformatted text block.

`$blc->pre($text)`

### *img* block

Image block with URL and Base64 encoded inline data support.

`$blc->img($src_base64, [$help])`

Notes:

+ `$src_base64` may be valid URL or Base64 encoded image data without *data:* clarification
+ *title* attributum of *img* tag will be the `$help` text

### *write* block

Generates image block from canvas file and text source.

`$blc->write($imageFile, $textArray, [$help])`

Notes:

+ `$imageFile` is canvas file and it is located on server (in *ecc-calculations/canvas* folder): e.g. `'vendor/resist/ecc-calculations/canvas/wind0.jpg'`
+ Texts are always red
+ `$textArray` is multidimensional associative array contains font-size, coordinates and texts:

```php
$textArray = array(
    array(
        'size' => 14, 
        'x' => 180, 
        'y' => 385, 
        'text' => 'F:'.$wF.'kN/m²'
    ),
    array(
        'size' => 14, 
        'x' => 180, 
        'y' => 550, 
        'text' => 'F:'.$wF.'kN/m²'
    )
);
```

+ Coordinates: x/y 0/0: canvas's top left corner, x is horizontal distance; text is placed by it's bottom left corner

### Charts

#### JSXGraph

`TODOC` see Annex/Snippets

#### Google Charts / LavaCharts

`TODOC` see Annex/Snippets

### *label* block

Creates label.

`$blc->label($value, [$help])`

Notes:

+ Set `$value = 'yes'` for green label
+ Set `$value = 'no'` for red label
+ `$help` is added as label text
+ If `value` is numeric, label is represented as unitilization label converted to [%]: `$value` integer larger than 1.0 will be red. Use `$help` to extend label.

### *region* region

General region that can be toggled.


```php
$blc->region0($name, [$help = 'Számítások']) // for start
    // some blocks
$blc->region1($name) // for end
```

Notes:

+ `$name` for start and end blocks have to be the same
+ Hidden regions are not printed

### *success*, *danger*, *info* regions

Colored regions.


```php
$blc->success0($name) // for start
    // some blocks
$blc->success1($name) // for end

$blc->danger0($name) // for start
    // some blocks
$blc->danger1($name) // for end

$blc->info0($name) // for start
    // some blocks
$blc->info1($name) // for end
```

Notes:

+ `$name` for start and end blocks have to be the same
+ *success* region is green, *danger* is red, *info* is blue

### *table* block

Generates table from associative array.

`$blc->table($array, [$mainKey, $help])`

Notes:

+ `$mainKey` is 1/1 (top left corner) cell's text
+ `$help` is table caption
+ `$array` is a multidimensional associative array. Outer array contains rows, inner arrays contain columns. Outer array keys are row titles (first column), inner arrays' keys are column titles (means these are repeated in every sub-array!). e.g.: 

```php
$table0 = array(
    "Col-1 Row-1 title" => array(
        "Col-2 title" => "Col-2 Row-1 value", 
        "Col-3 title" => "Col-3 Row-1 value",
    ),
    "Col-1 Row-2 title" => array(
            "Col-2 title" => "Col-2 Row-2 value", 
            "Col-3 title" => "Col-3 Row-2 value",
        )        
);
```

### *success*, *danger*, *info* blocks

Generates identical regions with title.

`$blc->success($md, [$title])`

`$blc->danger($md, [$title])`

`$blc->info($md, [$title])`

Notes:

+ `$md` is Markdown text

### toc() block

Generates Table of Contents  - numbered list by javascript from heading blocks.

`$blc->toc()`

---

## *Ec()* Eurocode class

For Eurocode methods call `Ec()` class first.

### Globals

Call predefined global from *Hive*: e.g. `$f3->__GG`

Available globals:

+ `__GG` \`gamma_(G) = 1.35\`
+ `__GQ` \`gamma_(Q) = 1.5\`
+ `__GM0` \`gamma_(M0) = 1.0\`
+ `__GM1` \`gamma_(M1) = 1.0\`
+ `__GM2` \`gamma_(M2) = 1.25\`
+ `__GM3` \`gamma_(M3) = 1.25\`
+ `__GM3ser` \`gamma_(M3,ser) = 1.1\`
+ `__GM6ser` \`gamma_(M6,ser) = 1.0\`
+ `__Gc` \`gamma_(c) = 1.5\`
+ `__Gs` \`gamma_(s) = 1.15\`
+ `__GS` \`gamma_(S) = 1.15\`
+ `__GcA` \`gamma_(c,A) = 1.2\`
+ `__GSA` \`gamma_(S,A) = 1.0\`

### *data()*, *boltDb()* and *matDb()* methods and databases

`$ec->data($dbName)` method returns associative array as DB.

Available database names:

+ `$dbName = 'bolt'` for bolts or use `$ec->boltDb()` method directly
+ `$dbName = 'mat'` for materials or use `$ec->matDb()` method directly

Notes:

+ For datas see Annex of this documentation. Note that `data()` method returns array not JSON formatted data as in Annex.
+ Returned measure-of-units are represented in databases (last record)

### *matProp()* and *boltProp()* methods

Returns material or bolt property.

`$ec->matProp($name, $property)`

`$ec->boltProp($name, $property)`

Notes:

+ `$name` is material/bolt ID name in DB 
+ For materials use name schemas like `'S235'` `'8.8'` `'C60/75'` `'B500'`
+ For bolts use name schema like `'M12'`
+ `$property` is column name in DB e.g. `'fy'` `'fctk005'`, `'d0'`
+ Returned measure-of-units are represented in databases (last record)
+ For exact IDs and properties see Annex of this documentation.

### *fy()* and *fu()* methods

`$ec->fy($matName, $t)` returns yield strength of steel.

`$ec->fu($matName, $t)` returns ultimate strength of steel.

Notes:

+ `$matName` is material ID in material DB like `'S235'` `'8.8'` `'C60/75'` `'B500'`
+ `$t` is plate thickness
+ Reduced strengths of thick plates are considered if \`t >= 40 mm\`
+ For rebars `fu` returns \`0\`

### *matList()* and *boltList()* methods

Renders *lst* block with available materials/bolts.

`$ec->matList([$matName = 'mat', $default = 'S235', $title = 'Anyagminőség'])`

`$ec->boltList($name = 'btName', $default = 'M16', $title = 'Csavar átmérő')`

### sectionFamilyList() method

Renders *lst* block for steel section families and creates variable for selected one.

`sectionFamilyList([$variableName = 'sectionFamily', $title = 'Szelvény család', $default = 'HEA'])`

Notes: 

+ Selected (or default) section family is stored in *Hive*, variable name is defined by `$variableName`, e.g. `$f3->_variableName`
+ List is hardcoded

### sectionList() method

Renders *lst* block with list of section is actual family and creates variable for selected one.

`sectionList([$familyName = 'HEA', $variableName = 'sectionName', $title = 'Szelvény név', $default = 'HEA200'])`

Notes: 

+ Selected (or default) section is stored in *Hive*, variable name is defined by `$variableName`, e.g. `$f3->_variableName`
+ `$familyName` is the name of section family/group, e.g. `'HEA'`
+ Sections for family are queried from database realtime

### sectionData() method

Save section data associative array in *Hive*.

`sectionData($sectionName, [$renderTable = false, $arrayName = 'sectionData'])`

Notes:

+ `$sectionName` is the name of queried section, e.g. `'HEA120'`
+ If `$renderTable` is *true*, data table is rendered for output
+ `$arrayName` is created variable in *Hive*, e.g. `$f3->_arrayName`, `$f3->_arrayName['Iy']`
+ Array keys of values: *G	h	b	tw	tf	r1	r2	r3	Ax	Ay	Az	Ix	Iy	Iz	Iyz	I1	I2	Iw	W1elt	W1elb	W2elt	W2elb	W1pl	W2pl	i_y	i_z	yG	zG	ys	zs	fp*. For more information and units check [https://structure.hu/profil](https://structure.hu/profil)

### *FtRd()* method

`$ec->FtRd($btName, $btMat)`

### *BpRd()* method

`$ec->BpRd($btName, $stMat, $t)`

### *FvRd()* method

`$ec->FvRd($btName, $btMat, $n, [$As = 0])`

### *FbRd()* method

`$ec->FbRd($btName, $btMat, $stMat, $ep1, $ep2, $t, $inner)`

### *VplRd()* method

`$ec->VplRd($Av, $matName, $t)`

### *NtRd()* method

`$ec->NtRd($A, $Anet, $matName, $t)`

### *NuRd()* method

`$ec->NuRd($Anet, $matName, $t)`

### *NplRd()* method

`$ec->NplRd($A, $matName, $t)`

### *NcRd()* method

`$ec->NcRd($A, $fy)`

### *McRd() method

`$ec->McRd($W, $fy)`

### *qpz()* method

`$ec->qpz($z, $terrainCat)`

### *linterp* method

`$ec->linterp($x1, $y1, $x2, $y2, $x)`

---

## EcNSEN() class - Norwegian National Annnex

For NS-EN Eurocode methods call `EcNSEN()` class first.

### *qpzNSEN()* method

`$ecNSEN->qpzNSEN($z, $terrainCat, $cAlt, $c0z, $vb0)`

--- 
# Notes 

```
<input name="_noFooter" id="_noFooter" type="hidden" value="1">
``` 

print without footer