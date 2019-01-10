# Használati útmutató

Az ***Ecc*** egy webes számítási dokumentumokat kezelő szerveroldali keretrendszer. 

Egy dokumentum ( *natív számítási osztály* ) beviteli mező, kép, szöveg, matematikai kifejezés stb. blokkokból épül fel. A rendszer része egy Eurocode alapú függvény- és adatgyűjtemény is. 

## Számítások futtatása

A menü *Futtatás* parancsára vagy *Enter* billentyűparancsra a számítás lefut. Automatikusan lefut továbbá bizonyos beviteli mezők változatásakor.

## Számítások mentése szerverre

A módosított beviteli mezők elmenthetők a szerverre a menü *Mentés szerverre* parancsára vagy az *s* billentyűparancsra.

A rendszer a legfelső sorba (vízszintes vonal a cím alatt) írt *Projekt néven* fogja elmenteni a paramétereket. Azonos név esetén felülírás van!

Mentés betölthető vagy törölhető a [kezdőlapi](https://structure.hu/calc) listázásnál vagy a menüből.

## Nyomtatás, PDF nyomtatás

Az értesítés-, tartalomjegyzék- és összecsukott blokkok (összecsukott fejezetek, régiók, megjegyzések) nem kerülnek nyomtatásra.

Nyomtatáshoz a rendszer automatikusan átalakított megjelenést biztosít.

## Jegyzetek

Megjegyzések a számításhoz - előhívható a menüből vagy a *n* billetyűparancsra.

## Pecsét

Menüből vagy *p* billentyűparancsra előhívható tervpecsét mentett dokumentumokhoz. 

## Txt kimenet

Menüből vagy *x* billentyűparancsra előhívható szöveges kimenet.
Fix hosszúságú sorok 75 oszlopból állnak. 
Nem kezelt blokkok: kép, html (pl. táblázatok), tartalomjegyzék

## Billentyűparancsok

+ *Enter*: futtatás
+ *n*: jegyzetek mutatása/rejtése
+ *s*: dokuemntum mentése szerverre
+ *p*: pecsét mutatása/rejtése
+ *t*: lap teteje
+ *x*: txt output mutatása/rejtése

## Forráskód, changelog, fejlesztés

A dokumentumok forráskódja megtekinthető: [bitbucket/resist/ecc-calculations](https://bitbucket.org/resist/ecc-calculations/src)

Forráskód, changelog és hozzászólás linkek megtalálhatók a menüben.

Hozzászólások, észrevételek a tároló hibajegy kezelő rendszerén keresztül, megfelelő címkézéssel történhet.

---

# Ecc

**Native classes of Eurocode based calculations for structural design on [structure.hu](https://structure.hu)**

(c) 2018 Bence VÁNKOS &nbsp;&nbsp;&nbsp;&nbsp; ![email](https://structure.hu/img/emailB.png)

+ **Repository:** [bitbucket/resist/ecc-calculations](https://bitbucket.org/resist/ecc-calculations)
+ **Issue tracker:** [bitbucket/resist/ecc-calculations/issues](https://bitbucket.org/resist/ecc-calculations/issues)
+ **Changelog:** - [bitbucket/resist/ecc-calculations/commits](https://bitbucket.org/resist/ecc-calculations/commits)

--- 

## General notes for development

+ ***Ecc*** (actually *Ecc()* class) is the PHP framework that runs, renders, updates etc. calculations (in fact the native calculation classes' *calc()* method).
+ *Ecc* (and whole website) uses [Fatfree Framework](https://fatfreeframework.com) as engine, it's autoloaded and Fatfree singleton object is available everywhere globally as **`$f3`**. *Hive* is *f3*'s global storage.
+ ***Blc()*** class is part of the *Ecc* framework and it's supposed to render GUI elements like inputs, definitions and other blocks. Calculation methods are built up from these blocks, but may contain vanilla PHP code too.
+ ***Ec()*** class contains Eurocode specific methods, datas or predefined GUI elements. *Ec()* autoloaded as well.
+ [AsciiMath](http://asciimath.org/) **math expressions** are compiled by [MathJax](https://www.mathjax.org) on client side, write them between with &grave; code marks anywhere. (Needs to be escaped in Markdown regions unless mentioned otherwise.)
+ HTML tags are cleaned generally.
+ [Markdown](https://en.wikipedia.org/wiki/Markdown) is enabled generally.
+ [GUMP](https://github.com/Wixel/GUMP) and [V3](https://bitbucket.org/resist/v3/) validation libraries are available.
+ Regions always need two lines of code using the same identifier name: starter and end blocks (marked in method name by 0 and 1)
+ Reserved variable final names: `_project*`, `_stamp*`, `print` - e.g. `$blc->def('project', 1, '%%')` creates `f3->_project` variable in *Hive*, which is reserved for saved data name.

---

## Initialization / Boilerplate calculation class

Example of a calculation class:

```php

namespace Calculation;

// This class will extend Ecc framework
Class Boilerplate extends \Ecc
{
    // "calc" method will be called by Ecc - parameters are given by the consctructor:
    // $f3 = \Base::instance(); // Ftafree Framework
    // $blc = \Blc::instance(); // Blc GUI methods
    // $ec = \Ec::instance(); // Ec Eurocode methods
    
    /**
     * @var $f3 \Base
     * @var $blc \Blc
     * @var $ec \Ec\Ec
     */
    public function calc(object $f3, object $blc, object $ec): void
    {
        // Load LavaChart if needed
        $lava = new \Khill\Lavacharts\Lavacharts;

        // Business logic via Blc
        $blc->txt('Hello World!');
        
        // Custom PHP code
        if (1 === 1) {
            $f3->var = 1;
        }
        
        // Business logic with mixed Blc and custom code
        $blc->def('x', $f3->var + 1, 'x = %%', 'Súgó');
        $blc->txt($f3->_x, 'Autosaved x variable in Hive');
    }
}
```

---

## Ecc/Blc framework blocks and regions

### Dynamic blocks

Rendered dynamic blocks are generally form elements that interact with user and handle data submitting.

These blocks create global variable in *f3 Hive* with underscore: e.g. `$f3->_x` if `$variableName = 'x'`. Set value is the default value of field or submitted POST data. 

Validation of submitted data is via GUMP if given GUMP validation string (`$gumpValidation`) is not set to `false`. 
Error block is rendered if validation fails, but calculation will be run with default value of that field.
e.g. `$gumpValidation = 'alpha_numeric|max_len,100|min_len,6'` 

#### *input* block

Renders general input field that defines variable in *f3 Hive*.

```php
$blc->input($variableNname, $title [, $defaultValue = false, $unit = false, $help = false, $gumpValidation = false])
```

+ `$variableName` (string) creates variable in *Hive* with underscore.
+ `$title` (string) creates label for input field. `$variableName` is added to title by default as math expression, if `$title` does not contain additional *math expression*.
+ `$defaultValue` (mixed) default value of field.
+ `$unit` (string) creates appended label for input field. GUI only feature.
+ `$help` (string) creates help/description text for input field. Markdown and math expressions without escaping are enabled.
+ `$gumpValidation` (string) GUMP validation string.

#### *numeric* block

Renders numeric input field that defines variable in *f3 Hive*. 

```php
numeric($variableNname, $title [, $defaultValue = false, $unit = false, $help = false])
```

+ No additional validation available. This field is an *input block* with `$gumpValidation = 'numeric'`.
+ `$variableName` (string) creates variable in *Hive* with underscore.
+ `title` (string) creates label for input field. `$variableName` is added to title by default as math expression, if `$title` does not contain additional *math expression*.
+ `$defaultValue` (numeric) default value of field.
+ `$unit` (string) creates appended label for input field. GUI only feature.
+ `$help` (string) creates help/description text for input field. Markdown and math expressions without escaping are enabled.

#### *boo* block

Renders boolean checkbox input field that defines variable in *f3 Hive*.

```php
$blc->boo($variableName, $title [, $defaultValue = false, $help = false])
```

+ `$variableName` (string) creates variable in *Hive* with underscore.
+ `title` (string) creates label for input field. `$variableName` is added to title by default as math expression, if `$title` does not contain additional *math expression*.
+ `$defaultValue` (boolean) default value of field.
+ `$help` (string) creates help/description text for input field. Markdown and math expressions without escaping are enabled.
+ values are `1` or `0`

#### *lst* block

Renders list/dropdown menu that defines variable in *f3 Hive*.

```php
$blc->lst($variableName, $source, $title [, $defaultValue = false, $help = false, $gumpValidation = false])
```

+ `$variableName` (string) creates variable in *Hive* with underscore.
+ `$source` (array, mixed) is associative array with GUI text and value pairs: e.g. `['First GUI member' => 0, 'Second GUI member' => 1]`
+ `title` (string) creates label for input field. `$variableName` is added to title by default as math expression, if `$title` does not contain additional *math expression*.
+ returned value is `$source`'s 2nd column.
+ `$defaultValue` (mixed) default value of field and referenced by `$source`'s 2nd column (aka *value*).
+ `$help` (string) creates help/description text for input field. Markdown and math expressions without escaping are enabled.
+ `$gumpValidation` (string) GUMP validation string. **TODO: ignored yet**

#### *area* block

Renders textarea input field that defines variable in *f3 Hive*.

```php
$blc->area($variableName, $title [, $defaultValue = false, $help = false, $gumpValidation = false])
```

+ `$variableName` (string) creates variable in *Hive* with underscore.
+ `title` (string) creates label for input field. `$variableName` is added to title by default as math expression, if `$title` does not contain additional *math expression*.
+ `$defaultValue` (string) default value of field.
+ `$help` (string) creates help/description text for input field. Markdown and math expressions without escaping are enabled.
+ `$gumpValidation` (string) GUMP validation string. **TODO: ignored yet**

#### *def* block

Variable definition block with rendered math expression support.

```php
$blc->def($variableName, $result, $mathExpression [, $help = false])
```

+ `$variableName` (string) creates variable in *Hive* with underscore.
+ `$result` (mixed) contains any PHP result or calculation that will be stored in `$f3->_variableName`.
+ `$mathExpression` (string) is mathematical expression without \` code marks. *%%* is replaced by `$result`.
+ `$help` (string) creates help/description text for input field. Markdown and math expressions without escaping are enabled.

### Static blocks

Rendered static blocks are simple GUI elements.

#### *hr* block

Renders simple separator line.

```php
$blc->hr();
```

#### *h1*, *h2*, *h3*, *h4* blocks

Renders headings with lead paragraphs.

```php
$blc->h1($headingTitle [, $subTitle = false]);
```

```php
$blc->h2($headingTitle [, $subTitle = false]);
```

```php
$blc->h3($headingTitle [, $subTitle = false]);
```

```php
$blc->h4($headingTitle [, $subTitle = false]);
```

+ `$headingTitle` (string) creates heading element. Some HTML is enabled: *span, em, strong, small, code*
+ `$subTitle` (string) creates lead paragraph as subtitle. Markdown and math expressions without escaping are enabled.
+ *h1* block can be toggled on GUI.
+ Heading blocks are not necessary paired with corresponding HTML tag.

#### *math* block

Renders simple math text for mathematical expressions, compiled by MathJax.

```php
$blc->math($mathExpression [, $help = false]);
```

+ In `$mathExpression` (string) define expression without \` code marks.
+ *%%%* replaced by vertical spacing in `$mathExpression`.
+ `$help` (string) creates help/description text for input field. Markdown and math expressions without escaping are enabled.
+ This block does not define variable.

#### *txt* block

Renders simple text block.

```php
$blc->txt([$text, $help])
```

+ `$help` (string) creates muted text. Markdown and math expressions without escaping are enabled.
+ Both `$txt` and `$help` parameters are optional, since muted helper text can be generated.

#### *html* block

Renders simple HTML5 block. USE IT CAREFULLY!

```php
$blc->txt($html)
```

+ `$html` (string) Not cleaned, validated or sanitized!

#### *md* block

Renders simple Markdown block.

```php
$blc->md($md)
```

+ `$md` (string) Markdown string. HTML tags are cleaned (except *br*).
+ Math expressions should be written with escaped \` code marks.


#### *note* block

Renders hidden by default notes and foot-notes for users.

```php
$blc->note($noteText);
```

+ `$noteText` (string) is text of note. Markdown and math expressions without escaping are enabled.

#### *pre* block

Renders preformatted text block.

```php
$blc->pre($text)
```

+ `$text` (string) is the text of block.
+ HTML tags are cleaned in `$text`

#### *img* block

Renders image block with URL and Base64 encoded inline data support.

```php
$blc->img($src_base64 [, $caption = ''])
```

+ `$src_base64` (string) may be valid URL or Base64 encoded image data without *data:* clarification
+ `$caption` (string) is set as image caption and *img* tag's title attributum. Markdown and math expressions without escaping are enabled.

#### *write* block

Generates img block and renders tagged image from canvas file and text source.

```php
$blc->write($imageFile, $textArray [, $caption = ''])
```

+ `$imageFile` (string) is the canvas file located on server (in *ecc-calculations/canvas* folder): e.g. `'vendor/resist/ecc-calculations/canvas/wind0.jpg'`
+ `$textArray` (array) is multidimensional associative array contains font-size, coordinates and texts:

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
+ Texts are always red
+ `$caption` (string) is set as image caption and *img* tag's title attribute. Markdown and math expressions without escaping are enabled.

#### *label* block

Renders label/badge element.

```php
$blc->label($value [, $text = ''])
```

+ Set `$value = 'yes'` for green label
+ Set `$value = 'no'` for red label
+ If `$value` is numeric, label is represented as unitilization label converted to [%]: `$value` integer larger than 1.0 will be red. Use `$text` to extend label.
+ `$text` (string) is added as label text. Markdown and math expressions without escaping are enabled.

#### *table* block

Renders table from associative array.

```php
$blc->table($array [, $mainKey = '-', $caption])
```

+ `$mainKey` (string) is 1/1 (top left corner) cell's text
+ `$caption` (string) is table caption
+ `$array` (array) is a multidimensional associative array. Outer array contains rows, inner arrays contain columns. Outer array keys are row titles (first column), inner arrays' keys are column titles (means these are repeated in every sub-array!). e.g.: 

```php
// Table controller array
$array = array(
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

#### *success*, *danger*, *info* blocks

Renders identical regions with title. Learn more at regions.

```php
$blc->success($md [, $title = false])

$blc->danger($md [, $title = false])

$blc->info($md [, $title = false])
```

+ `$md` (string) is Markdown text of block, math expressions should be escaped.
+ Generated region is with random identifier name.

#### *toast* block

Displays toast alert via javascript.

```php
$blc->toast($text [, $type = 'info', $title = false])
```

+ `$text` (string) is toast message. Some HTML is enabled (*em, strong*).
+ `$type` (string) is toast type: *info, warning, danger, success*
+ `$title` (string) is toast title. Some HTML is enabled (*em*).

#### *toc* block

Renders Table of Contents  - numbered list generated by javascript from heading blocks.

```php
$blc->toc()
```

#### *bulk* block

Renders spreadsheet  - input fields in table rows. Calculations on posted data can be done by hooks. Supports auto-summary of table columns.

```php
$blc->bulk($bulkName, $fields);
```

+ `$bulkName` (string) is the identifier name of spreadsheet and the variable (array) name of collected and handled data. Variable name is created in *Hive* with underscore.
+ `$fields` (array) is a multidimensional associative array as the controller of spreadsheet, columns generated by this. Keys of array: `name` for column identifier, `title` for column title in *thead* row, `type` for column type, `key` for calculations if needed, `sum` for column summary, `text` for fixed text if needed.

```php
// Spreadsheet controller array
$fields = [
            ['name' => 'varName1', 'title' => 'General input column', 'type' => 'input', 'sum' => false],
            ['name' => 'varName2', 'title' => 'Input column with summary', 'type' => 'input', 'sum' => true],
            ['name' => 'varName3', 'title' => 'Calculation', 'type' => 'value', 'key' => 'hookVarName', 'sum' => false],
            ['name' => 'varName4', 'title' => 'Simple text', 'type' => 'text', 'text' => '[cm]', 'sum' => false],
        ];
```

Supported types:

+ `input` for simple input field
+ `value` for calculation. Value of `key` key of `$fields` controller is displayed. *foreach* processing hook is needed before `$blc->bulk($bulkName, $fields);` line.
+ `text` for simple text. Value of `text` key of `$fields` controller is displayed.

Example of hook *foreach*:

```php
if ($f3->exists('POST._'.$bulkName)) {
    foreach ($f3->get('POST._'.$bulkName) as $key => $value) {
        $f3->set("POST._$bulkName.$key.hookVarName", \V3::numeric($value['varName2'])*2);
    }
}
```

Make sure `'POST._'.$bulkName` existed in hive before *foreach*. Also make sure numeric value were in summarized columns. (Note that *V3* numeric validator always returns number.)

### Regions

Regions always need two lines of code using the same identifier name: starter and end blocks (marked in method name by 0 and 1)

#### *region* region

Renders general region that can be toggled.

```php
$blc->region0($name [, $title = 'Számítások']) // for start
    // some blocks
$blc->region1($name) // for end
```

+ `$name` (string) is identifier. For start and end blocks have to be the same.
+ `$title` (string) is heading of the block
+ Hidden regions are not printed
+ Can be toggled

#### *success*, *danger*, *info* regions

Render olored regions.

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

+ `$name` (string) is identifier. For start and end blocks have to be the same.
+ *success* region is green, *danger* is red, *info* is blue

### Charts

#### JSXGraph

See Annex/Snippets.

#### Google Charts / LavaCharts

See Annex/Snippets.

---

## *Ec()* Eurocode class

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

### Datas

#### *readData(), getBoltArray(), getMaterialArray()* methods

Returns associative array from database.

```php
$ec->readData($dbName)
```

+ `$dbName` (string) database identifier name. Available database names: *bolt, mat*

Direct methods for *bolt* and *material* datas:

```php
$ec->getBoltArray() // returns bolt database

$ec->getMaterialArray() // returns material database
```

+ For datas see Annex of this documentation. Note that these methods return array instead of JSON formatted data as it is in Annex.
+ Returned measure-of-units are represented in databases (last record)

#### *matProp()* and *boltProp()* methods

Returns material or bolt property.

```php
$ec->matProp($name, $property) // returns material data

$ec->boltProp($name, $property) // returns bolt data
```

+ `$name` (string) is material/bolt identifier name in database 
+ For materials use name schemas like `'S235'` `'8.8'` `'C60/75'` `'B500'`
+ For bolts use name schema like `'M12'`
+ `$property` (string) is column name in database e.g. `'fy'` `'fctk005'`, `'d0'`
+ Returned measure-of-units are represented in databases (last record)
+ For exact identifiers and properties see Annex of this documentation.

#### *fy()* and *fu()* methods

Returns material yield and ultimate strengths.

```php
$ec->fy($matName, $t) // returns yield strength of steel

$ec->fu($matName, $t) //returns ultimate strength of steel
```

+ `$matName` (string) is material identifier in material database like `'S235'` `'8.8'` `'C60/75'` `'B500'`
+ `$t` (numeric) is plate thickness in [mm]. Reduced strengths of thick plates are considered if \`t >= 40 mm\`. Alert block is rendered if so.
+ For rebars `fu` returns \`0\`

#### *rebarList()*, *matList()* and *boltList()* methods

Renders *lst* block with available rebar diameters, materials and bolts. Sets selected identifier to a *Hive* variable.

```php
$ec->rebarList([$variableName = 'fi', $default = 16, $title = 'Vasátmérő', $help = '']) // renders rebar list

$ec->matList([$variableName = 'mat', $default = 'S235', $title = 'Anyagminőség']) // renders material list

$ec->boltList($variableName = 'bolt', $default = 'M16', $title = 'Csavar átmérő') // renders bolt list
```

+ `$variableName` (string) is saved name of variable in *Hive*.
+ `$default` (string) is material/bolt identifier name in database, e.g.: `'S235'`, `'M16'`.
+ `$title` (string) is the title of list block. Defined variable name is not part of the title automatically.
+ `$help` (string) is subtext for list block.
+ If user selects *Units* is in displayed list, *txt* block with units is rendered.

#### *sectionFamilyList()* method

Renders *lst* block for steel section families and creates variable for selected one.

```php
sectionFamilyList([$variableName = 'sectionFamily', $title = 'Szelvény család', $default = 'HEA'])
```

+ `$variableName` (string) is the variable in *Hive* selected (or default) section family identifier name in it, e.g. `$f3->_variableName`.
+ `$title` (string) is the title of list block. Defined variable name is not part of the title automatically.
+ `$default` (string) is section family identifier name in database, e.g.: `'HEA'`.

#### *sectionList()* method

Renders *lst* block with list of section in actual family and creates variable for selected one's identifier name.

```php
sectionList([$familyName = 'HEA', $variableName = 'sectionName', $title = 'Szelvény név', $default = 'HEA200'])
```

+ `$familyName` (string) is the name of section family/group, e.g. `'HEA'`
+ `$variableName` (string) is the variable in *Hive* selected (or default) section identifier name in it, e.g. `$f3->_variableName`.
+ `$title` (string) is the title of list block. Defined variable name is not part of the title automatically.
+ `$default` (string) is section identifier name in database, e.g.: `'HEA220'`.
+ Sections for family are queried from database realtime

#### *saveSectionData()* method

Save section data as associative array in *Hive*.

```php
saveSectionData($sectionName, [$renderTable = false, $arrayName = 'sectionData'])
```

+ `$sectionName` (string) is the name of queried section, e.g. `'HEA120'`
+ If `$renderTable` is *true*, data table is rendered for output
+ `$arrayName` is created variable in *Hive*, e.g. `$f3->_arrayName`, `$f3->_arrayName['Iy']`
+ Array keys of values: *G	h	b	tw	tf	r1	r2	r3	Ax	Ay	Az	Ix	Iy	Iz	Iyz	I1	I2	Iw	W1elt	W1elb	W2elt	W2elb	W1pl	W2pl	i_y	i_z	yG	zG	ys	zs	fp*. For more information and units check [https://structure.hu/profil](https://structure.hu/profil)

### Steel related routines

General notes:

+ `$t` (numeric) [mm] is plate thickness in millimeter.
+ `$btMat` (string) is bolt material identifier name.
+ `$stMat` (string) is steel material identifier name.
+ `$btName` (string) is bolt (size) identifier name.

#### *FtRd()* method

Returns tension resistance per bolt in [kN].

```php
$ec->FtRd($btName, $btMat)
```

#### *BpRd()* method

Returns punching shear resistance of the bolt head and the nut in [kN].

```php
$ec->BpRd($btName, $stMat, $t)
```

#### *FvRd()* method

Returns Shear force per bolt for the ultimate limit state in [kN].

```php
$ec->FvRd($btName, $btMat, $n, [$As = 0])
```

+ For *4.8, 5.8, 6.8* and *10.9* bolt materials returns 80% reduced resistance. Information block is rendered if so.
+ `$n` (numeric) is number of shear planes or number of the friction surfaces.
+ `$As` (numeric) [mm^2] is Tensile stress area. It can be overwritten with set of `$As`.

#### *FbRd()* method

Returns bearing resistance per bolt in [kN].

```php
$ec->FbRd($btName, $btMat, $stMat, $ep1, $ep2, $t, $inner)
```

+ `$ep1` (numeric) [mm]
    + The end distance from the centre of a fastener hole to the adjacent end of any part, measured in the direction of load transfer
    + The spacing between centres of fasteners in an outer line in the direction of load transfer
+ `$ep2` (numeric) [mm]
    + The edge distance from the centre of a fastener hole to the adjacent edge of any part, measured at right angles to the direction of load transfer
    + The spacing measured perpendicular to the load transfer direction between adjacent lines of fasteners
+ `$inner` (boolean) sets inner or end bolt. For inner bolts use `$inner = 1`, for end bolts use `$inner = 0`.

#### *VplRd()* method

Returns plastic shear resistance for 1-2 Cross-section class in [kN].

```php
$ec->VplRd($Av, $matName, $t)
```

+ `$Av` (numeric) [mm^2] is cross-section area in [mm^2]

#### *NtRd()* method

Returns design value of the resistance of tension force in [kN].

```php
$ec->NtRd($A, $Anet, $matName, $t)
```

+ Uses *NuRd(), NplRd()*
+ `$A` (numeric) [mm^2] is gross cross-section area in [mm^2]
+ `$Anet` (numeric) [mm^2] is net area of section without holes in [mm^2]

#### *NuRd()* method

Returns design ultimate resistance to normal forces of the net cross-section at holes for fasteners in [kN]

```php
$ec->NuRd($Anet, $matName, $t)
```

+ `$Anet` (numeric) [mm^2] is net area of a cross section without holes in [mm^2]

#### *NplRd()* method

Returns design plastic resistance to normal forces of the gross cross-section in [kN].

```php
$ec->NplRd($A, $matName, $t)
```

+ `$A` (numeric) [mm^2] is gross cross-section area in [mm^2]

#### *NcRd()* method

// TODO doc

```php
$ec->NcRd($A, $fy)
```

+ `$A`
+ `$fy`

#### *McRd()* method

// TODO doc

```php
$ec->McRd($W, $fy)
```

+ `$W`
+ `$fy`

### Load routines

#### *qpz()* method

Returns wind peak velocity pressure.

```php
$ec->qpz($z, $terrainCat)
```

+ `$z` (numeric) [m] is height in meter
+ `$terrainCat` (numeric) is terrain category: *1, 2, 3, 4*

Hard coded constants:

```php
$vb0 = 23.6;
$cDir = 1.0;
$cSeason = 1.0;
$cAlt = 1.0;
$cProb = 1.0;
$c0z = 1;
$ki = 1;
```

### Mathematical routines

#### *linterp()* method

Returns linear interpolated result.

```php
$ec->linterp($x1, $y1, $x2, $y2, $x)
```

+ `$x1, $y1` and `$x2, $y2` pairs are known scalars
+ returned value is *y* for given `$x`

### Methods and routines for NSEN/Norwegian National Annnex

#### *qpzNSEN()* method

Returns Norwegian wind peak velocity pressure.

```php
$ecNSEN->qpzNSEN($z, $terrainCat, $cAlt, $c0z, $vb0)
```

--- 

# License

CDDL-1.0 Copyright 2018 Bence VÁNKOS
