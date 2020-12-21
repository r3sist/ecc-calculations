# Blc

***Ecc\Blc*** 

General user interface "blocks" for Ecc framework - renders and handles blocks.  
Contains wrapper methods only for \Ecc\Block\*Block() classes that extend BlcHandler().  


## Public methods 

### __construct()

```php
public function __construct(Base $f3)
```

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| *Base* | **$f3** |  | Fatfree Framework Base class |
### boo()

Builds checkbox field  


```php
public function boo(string $variableName, array $title, bool $defaultValue = `false`, @string  $help = ""): string
```

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string`  | **$variableName** |  | Stores data in F3-Hive with a name of _ prefix and this ID |
| `array`  | **$title** |  | Header of block: [(string) ASCIIMath expression or empty string, (string) title text] |
| `bool`  | **$defaultValue** | `false` | Input default value |
| @string  | **$help** | "" | Markdown help text. Can contain $$ math. |
### bulk()

Builds multi-input table as dynamic block.  


```php
public function bulk(string $variableName, array $fields, $defaultRow = []): void
```

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string`  | **$variableName** |  | Stores data in F3-Hive with a name of _ prefix and this ID |
| `array`  | **$fields** |  | Source data of table. Sub arrays keys: "name" => string, "title" => string, "type" => string: "value" "input", "key" => string: for value types, "sum" => bool |
|  | **$defaultRow** | [] |  |
### danger()

Builds danger block.  


```php
public function danger(string $mdLight, string $title = `empty string`): void
```

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string`  | **$mdLight** |  | Multi-line markdown text as content. Can contain $$ math expression. |
| `string`  | **$title** | `empty string` |  |
### danger0()

Builds danger region INITIAL block. Call danger1() method for closing.  


```php
public function danger0(string $title = `empty string`): void
```

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string`  | **$title** | `empty string` | Title of region. |
### danger1()

Closes danger0() block.  


```php
public function danger1(): void
```

### def()

Builds definition block, stores given value to named variable and renders math block with custom text.  


```php
public function def(string $variableName, @mixed  $result, string $mathExpression = "%%", string $help = `empty string`, string $gumpValidation = `empty string`): string
```

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string`  | **$variableName** |  | Stores data in F3-Hive with a name of _ prefix and this ID |
| @mixed  | **$result** |  | Stores this as data |
| `string`  | **$mathExpression** | "%%" | ASCIIMath expression without $ delimiters. Use "%%" to substitute result. |
| `string`  | **$help** | `empty string` | Markdown help text |
| `string`  | **$gumpValidation** | `empty string` | GUMP validator string: https://github.com/Wixel/GUMP#star-available-validators |
### h1()

Builds first order header block.  


```php
public function h1(string $headingTitle, string $subTitle = `empty string`): string
```

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string`  | **$headingTitle** |  | Text of header |
| `string`  | **$subTitle** | `empty string` | Markdown text of sub-header |
### h2()

Builds second order header block.  


```php
public function h2(string $headingTitle, string $subTitle = `empty string`): string
```

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string`  | **$headingTitle** |  | Text of header |
| `string`  | **$subTitle** | `empty string` | Markdown text of sub-header |
### h3()

Builds third order header block.  


```php
public function h3(string $headingTitle, string $subTitle = `empty string`): string
```

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string`  | **$headingTitle** |  | Text of header |
| `string`  | **$subTitle** | `empty string` | Markdown text of sub-header |
### h4()

Builds fourth order header block.  


```php
public function h4(string $headingTitle, string $subTitle = `empty string`): string
```

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string`  | **$headingTitle** |  | Text of header |
| `string`  | **$subTitle** | `empty string` | Markdown text of sub-header |
### hr()

Builds horizontal line (hr HTML tag) block.  


```php
public function hr(): void
```

### html()

Builds html block.  


```php
public function html(string $html): void
```

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string`  | **$html** |  | HTML content |
### img()

Builds image block.  


```php
public function img(string $URLorBase64, string $caption = `empty string`): void
```

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string`  | **$URLorBase64** |  | Valid HTTP URL or Base64 image content. Base64 is PNG only, without "data:image/png;base64," |
| `string`  | **$caption** | `empty string` |  |
### info()

Builds info block.  


```php
public function info(string $mdLight, string $title = `empty string`): void
```

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string`  | **$mdLight** |  | Multi-line markdown text as content. Can contain $$ math expression. |
| `string`  | **$title** | `empty string` |  |
### info0()

Builds info region INITIAL block. Call info1() method for closing.  


```php
public function info0(string $title = `empty string`): void
```

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string`  | **$title** | `empty string` | Title of region. |
### info1()

Closes info0() block.  


```php
public function info1(): void
```

### input()

Builds general input field  


```php
public function input(string $variableName, array $title, @string  $defaultValue = "", string $unit = `empty string`, string $help = `empty string`, string $gumpValidation = `empty string`): string
```

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string`  | **$variableName** |  | Stores data in F3-Hive with a name of _ prefix and this ID |
| `array`  | **$title** |  | Header of block: [(string) ASCIIMath expression or empty string, (string) title text] |
| @string  | **$defaultValue** | "" | Input default value |
| `string`  | **$unit** | `empty string` | Input group extension, UI only. |
| `string`  | **$help** | `empty string` | Markdown help text. Can contain $$ math. |
| `string`  | **$gumpValidation** | `empty string` | GUMP validator string: https://github.com/Wixel/GUMP#star-available-validators |
### ::instance()

**DEPRECATED** This static method is used by a few template

```php
public static function instance()
```

### jsx()

Builds html block with JSXGraph content. Load driver() first.  


```php
public function jsx(string $id, string $js = "console.log("jsx");", int $height = 200): void
```

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string`  | **$id** |  | Block ID |
| `string`  | **$js** | "console.log("jsx");" | JSXGraph JS content |
| `int`  | **$height** | 200 | Height of rendered block |
### jsxDriver()

Injects JSXGraph library to calculation.  


```php
public function jsxDriver(): void
```

### label()

Build label block.  


```php
public function label(@float|int|string  $value, string $text = `empty string`): string
```

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| @float,int,string  | **$value** |  | Can be "yes" for green, "no" for red or numeric converted to %. If $value < 1 then label is green. |
| `string`  | **$text** | `empty string` | Additional text in label |
### lst()

Builds selection list  


```php
public function lst(string $variableName, array $source, array $title, @float|int|string  $defaultValue = "", string $help = `empty string`): string
```

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string`  | **$variableName** |  | Stores data in F3-Hive with a name of _ prefix and this ID |
| `array`  | **$source** |  | List items. Items: (int|float|string) name => (int|float|string) value |
| `array`  | **$title** |  | Header of block: [(string) ASCIIMath expression or empty string, (string) title text] |
| @float,int,string  | **$defaultValue** | "" | Input default value |
| `string`  | **$help** | `empty string` | Markdown help text. Can contain $$ math. |
### math()

Builds math expression block.  


```php
public function math(string $mathExpression, string $help = `empty string`): void
```

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string`  | **$mathExpression** |  | ASCIIMath expression without $ delimiters. Use "%%%" to substitute horizontal separator. |
| `string`  | **$help** | `empty string` | Markdown help text |
### md()

Builds markdown block.  


```php
public function md(string $mdLight): string
```

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string`  | **$mdLight** |  | Multi-line markdown text |
### note()

Builds note block.  


```php
public function note(string $mdStrict): void
```

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string`  | **$mdStrict** |  | Markdown text |
### numeric()

Builds numeric input field. (General HTML input tag with numeric validation). Accepts and converts math expression strings in "=2+3" format  


```php
public function numeric(string $variableName, array $title, float $defaultValue, string $unit = `empty string`, string $help = `empty string`, string $additionalGumpValidation = `empty string`): string
```

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string`  | **$variableName** |  | Stores data in F3-Hive with a name of _ prefix and this ID |
| `array`  | **$title** |  | Header of block: [(string) ASCIIMath expression or empty string, (string) title text] |
| `float`  | **$defaultValue** |  | Input default value |
| `string`  | **$unit** | `empty string` | Input group extension, UI only. |
| `string`  | **$help** | `empty string` | Markdown help text. Can contain $$ math. |
| `string`  | **$additionalGumpValidation** | `empty string` | GUMP validator string ("numeric" is set always): https://github.com/Wixel/GUMP#star-available-validators |
### pre()

Builds block contains pre HTML tag  


```php
public function pre(string $plainText): void
```

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string`  | **$plainText** |  |  |
### region0()

Builds collapsible region INITIAL block. Call region1() method for closing.  


```php
public function region0(string $name, string $title = "Számítások"): void
```

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string`  | **$name** |  | Block ID |
| `string`  | **$title** | "Számítások" | Title of region |
### region1()

Closes region0() block.  


```php
public function region1(): void
```

### success()

Builds success block.  


```php
public function success(string $mdLight, string $title = `empty string`): void
```

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string`  | **$mdLight** |  | Multi-line markdown text as content. Can contain $$ math expression. |
| `string`  | **$title** | `empty string` |  |
### success0()

Builds success region INITIAL block. Call success1() method for closing.  


```php
public function success0(string $title = `empty string`): void
```

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string`  | **$title** | `empty string` | Title of region. |
### success1()

Closes success0() block.  


```php
public function success1(): void
```

### svg()

Builds SVG content block.  


```php
public function svg(resist\SVG\SVG $svgObject, bool $forceJpg = `false`, string $caption = `empty string`): void
```

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| *resist\SVG\SVG* | **$svgObject** |  | SVG object generated by resist\SVG\SVG |
| `bool`  | **$forceJpg** | `false` | Renders SVG as JPG if true |
| `string`  | **$caption** | `empty string` | Help text of block |
### tbl()

Builds table block from array.  


```php
public function tbl(array $scheme, array $rows, string $name = "tbl", string $caption = `empty string`): void
```

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `array`  | **$scheme** |  | 1 dimensional array of header columns |
| `array`  | **$rows** |  | Array of rows' content array |
| `string`  | **$name** | "tbl" | Block ID - Class name of tbody tag for scripting |
| `string`  | **$caption** | `empty string` | Help text of table |
### toast()

Builds toaster block.  


```php
public function toast(string $textMdStrict, string $type = "info", string $titleMdStrict = `empty string`): void
```

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string`  | **$textMdStrict** |  | One-line markdown string |
| `string`  | **$type** | "info" | Toaster type (color and icon) |
| `string`  | **$titleMdStrict** | `empty string` | One-line markdown string for toaster heading |
### toc()

Builds table of content block.  


```php
public function toc(): void
```

### txt()

Builds plain text block.  


```php
public function txt(string $mdStrict, string $help = `empty string`): string
```

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string`  | **$mdStrict** |  | Markdown text, can contain $$ math expression |
| `string`  | **$help** | `empty string` | Markdown help text, can contain $$ math expression |
### wrapper0()

Builds wrapped blocks. STARTER block.  


```php
public function wrapper0(string $stringTitle = `empty string`): void
```

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string`  | **$stringTitle** | `empty string` | Title line of whole wrapped blocks |
### wrapper1()

Builds wrapped blocks. SEPARATOR block.  


```php
public function wrapper1(string $middleTextMd = `empty string`): void
```

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string`  | **$middleTextMd** | `empty string` | Separator text between wrapped blocks |
### wrapper2()

```php
public function wrapper2(string $help = `empty string`): void
```

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string`  | **$help** | `empty string` | Help text below wrapped blocks |
### write()

Builds and handles write blocks - Generates new image with text on base image; Renders img block  
["text" => (string),  
"x" => (int) position,  
"y" => (int) position,  
"size" => (int) text size]  


```php
public function write(string $imageFile, array $textArray, string $caption = `empty string`): void
```

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string`  | **$imageFile** |  | Base image as canvas relative to codebase home, e.g.: "vendor/resist/ecc-calculations/canvas/linQ0.jpg" |
| `array`  | **$textArray** |  | Array of texts. Sub array keys: |
| `string`  | **$caption** | `empty string` | Help text of block |


---

# Ec

***Ec\Ec*** 

Eurocode globals, helpers and predefined GUI elements for ECC framework  
(c) Bence VÁNKOS  
https:// structure.hu  


## Public methods 

### A()

```php
public function A(float $D, float $multiplicator = 1): float
```

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `float`  | **$D** |  |  |
| `float`  | **$multiplicator** | 1 |  |
### BpRd()

```php
public function BpRd(string $btName, string $stMat, float $t): float
```

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string`  | **$btName** |  |  |
| `string`  | **$stMat** |  |  |
| `float`  | **$t** |  |  |
### FbRd()

```php
public function FbRd(string $btName, @float|string  $btMat, string $stMat, float $ep1, float $ep2, float $t, bool $inner): float
```

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string`  | **$btName** |  |  |
| @float,string  | **$btMat** |  |  |
| `string`  | **$stMat** |  |  |
| `float`  | **$ep1** |  |  |
| `float`  | **$ep2** |  |  |
| `float`  | **$t** |  |  |
| `bool`  | **$inner** |  |  |
### FtRd()

```php
public function FtRd(string $btName, $btMat, bool $verbose = `true`): float
```

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string`  | **$btName** |  |  |
|  | **$btMat** |  |  |
| `bool`  | **$verbose** | `true` |  |
### FvRd()

```php
public function FvRd(string $btName, @float|string  $btMat, float $n, float $As): float
```

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string`  | **$btName** |  |  |
| @float,string  | **$btMat** |  |  |
| `float`  | **$n** |  |  |
| `float`  | **$As** | 0 |  |
### McRd()

```php
public function McRd(float $W, float $fy): float
```

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `float`  | **$W** |  |  |
| `float`  | **$fy** |  |  |
### NcRd()

```php
public function NcRd(float $A, float $fy): float
```

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `float`  | **$A** |  |  |
| `float`  | **$fy** |  |  |
### NplRd()

```php
public function NplRd($A, string $matName, $t): float
```

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
|  | **$A** |  |  |
| `string`  | **$matName** |  |  |
|  | **$t** |  |  |
### NtRd()

```php
public function NtRd($A, $Anet, string $matName, $t): float
```

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
|  | **$A** |  |  |
|  | **$Anet** |  |  |
| `string`  | **$matName** |  |  |
|  | **$t** |  |  |
### NuRd()

```php
public function NuRd($Anet, string $matName, $t): float
```

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
|  | **$Anet** |  |  |
| `string`  | **$matName** |  |  |
|  | **$t** |  |  |
### VplRd()

```php
public function VplRd(float $Av, string $matName, float $t): float
```

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `float`  | **$Av** |  |  |
| `string`  | **$matName** |  |  |
| `float`  | **$t** |  |  |
### __construct()

Ec constructor.  
Defines Eurocode parameters in hive: __GG, __GQ, __GM0, __GM2, __GM3, __GM3ser, __GM6ser, __Gc, __Gs, __GS, __GcA, __GSA  


```php
public function __construct(Base $f3, Ecc\Blc $blc, DB\SQL $db, Ecc\Map\DataMap $dataMap)
```

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| *Base* | **$f3** |  |  |
| *Ecc\Blc* | **$blc** |  |  |
| *DB\SQL* | **$db** |  |  |
| *Ecc\Map\DataMap* | **$dataMap** |  |  |
### boltList()

```php
public function boltList(string $variableName = "bolt", string $default = "M16", string $title = "Csavar betöltése"): void
```

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string`  | **$variableName** | "bolt" |  |
| `string`  | **$default** | "M16" |  |
| `string`  | **$title** | "Csavar betöltése" |  |
### boltProp()

```php
public function boltProp(string $boltName, string $propertyName): float
```

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string`  | **$boltName** |  |  |
| `string`  | **$propertyName** |  |  |
### chooseRoot()

```php
public function chooseRoot(float $estimation, array $roots): float
```

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `float`  | **$estimation** |  |  |
| `array`  | **$roots** |  |  |
### fu()

```php
public function fu(string $matName, float $t): float
```

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string`  | **$matName** |  |  |
| `float`  | **$t** |  | Thickness of relevant plate [mm] |
Returns: `float` Ultimate strength in [N/mm^2; MPa]

### fy()

```php
public function fy(string $materialName, float $t): float
```

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string`  | **$materialName** |  |  |
| `float`  | **$t** |  | Thickness of relevant plate [mm] |
Returns: `float` Yield strength in [N/mm^2; MPa]

### getCeilClosest()

Returns closest ceil number from series of keys. Note that array keys can not be floats, that's why there are string types.  


```php
public function getCeilClosest(array $array, string $find): string
```

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `array`  | **$array** |  | One dimensional array of keys |
| `string`  | **$find** |  | Compare array keys to this |
Returns: `string` Floor key as string

### getClosest()

**DEPRECATED** &nbsp;

```php
public function getClosest(float $find, array $stackArray, string $returnType = "closest"): float
```

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `float`  | **$find** |  |  |
| `array`  | **$stackArray** |  |  |
| `string`  | **$returnType** | "closest" |  |
### getFloorClosest()

Returns closest floor number from series of keys. Note that array keys can not be floats, that's why there are string types.  


```php
public function getFloorClosest(array $array, string $find): string
```

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `array`  | **$array** |  | One dimensional array of keys |
| `string`  | **$find** |  | Compare array keys to this |
Returns: `string` Floor key as string

### getMaterialArray()

Get all material data as array from database  
Units in database:  
﻿0 [-]  
fy [Mpa]  
fu [Mpa]  
fy40 [Mpa]  
fu40 [Mpa]  
betaw [-]  
fyd [Mpa]  
fck [Mpa]  
fckcube [Mpa]  
fcm [Mpa]  
fctm [Mpa]  
fctk005 [Mpa]  
fctk095 [Mpa]  
Ecm color(teal)( GPa)  
Ecu3 [-]  
Euk [-]  
Es [Gpa]  
Fc0 [-]  
F_c0 [-]  
fbd [Mpa]  
fcd [Mpa]  
fctd [Mpa]  
fiinf28 [-]  
Eceff [Gpa]  
alfat [1/Cdeg]  
Epsiloncsinf [-]  


```php
public function getMaterialArray(): array
```

Returns: `array` Assoc. array of read material data

### linterp()

```php
public function linterp(float $x1, float $y1, float $x2, float $y2, float $x): float
```

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `float`  | **$x1** |  |  |
| `float`  | **$y1** |  |  |
| `float`  | **$x2** |  |  |
| `float`  | **$y2** |  |  |
| `float`  | **$x** |  |  |
### matList()

```php
public function matList(string $variableName = "mat", string $default = "S235", array $title = ["","Anyagminőség"], string $category = `empty string`): void
```

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string`  | **$variableName** | "mat" |  |
| `string`  | **$default** | "S235" |  |
| `array`  | **$title** | ["","Anyagminőség"] |  |
| `string`  | **$category** | `empty string` |  |
### matProp()

```php
public function matProp(string $materialName, string $propertyName): float
```

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string`  | **$materialName** |  |  |
| `string`  | **$propertyName** |  |  |
### proportion()

```php
public function proportion(float $x0, float $y0, float $x1): float
```

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `float`  | **$x0** |  |  |
| `float`  | **$y0** |  |  |
| `float`  | **$x1** |  |  |
### qpz()

```php
public function qpz(float $z, float $terrainCat): float
```

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `float`  | **$z** |  |  |
| `float`  | **$terrainCat** |  |  |
### quadratic()

Original: https://github.com/hellofromtonya/Quadratic/blob/master/solver.php  


```php
public function quadratic(float $a, float $b, float $c, int $precision = 3): array
```

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `float`  | **$a** |  |  |
| `float`  | **$b** |  |  |
| `float`  | **$c** |  |  |
| `int`  | **$precision** | 3 |  |
Returns: `array` string[]|float[] may contain 'i'

### readData()

Get data record by data_id from ecc_data table  


```php
public function readData(string $dataName): array
```

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string`  | **$dataName** |  | Name of data as dataset identifier |
Returns: `array` Associative array of read data

### rebarList()

```php
public function rebarList(string $variableName = "fi", float $default = 16, array $title = ["","Vasátmérő"], string $help = `empty string`): void
```

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string`  | **$variableName** | "fi" |  |
| `float`  | **$default** | 16 |  |
| `array`  | **$title** | ["","Vasátmérő"] |  |
| `string`  | **$help** | `empty string` |  |
### rebarTable()

```php
public function rebarTable(string $variableNameBulk = "As"): float
```

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string`  | **$variableNameBulk** | "As" |  |
### sectionFamilyList()

```php
public function sectionFamilyList(string $variableName = "sectionFamily", string $title = "Szelvény család", string $default = "HEA"): void
```

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string`  | **$variableName** | "sectionFamily" |  |
| `string`  | **$title** | "Szelvény család" |  |
| `string`  | **$default** | "HEA" |  |
### sectionList()

```php
public function sectionList(string $familyName = "HEA", string $variableName = "sectionName", string $title = "Szelvény név", string $default = "HEA200"): void
```

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string`  | **$familyName** | "HEA" |  |
| `string`  | **$variableName** | "sectionName" |  |
| `string`  | **$title** | "Szelvény név" |  |
| `string`  | **$default** | "HEA200" |  |
### spreadMaterialData()

Saves all material properties from DB to Hive variables, with prefix. e.g.: _prefixfck, _prefixfy etc  


```php
public function spreadMaterialData(@float|string  $matName, string $prefix = `empty string`): void
```

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| @float,string  | **$matName** |  |  |
| `string`  | **$prefix** | `empty string` |  |
### spreadSectionData()

```php
public function spreadSectionData(string $sectionName, bool $renderTable = `false`, string $arrayName = "sectionData"): void
```

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string`  | **$sectionName** |  |  |
| `bool`  | **$renderTable** | `false` |  |
| `string`  | **$arrayName** | "sectionData" |  |
### wrapNumerics()

```php
public function wrapNumerics(string $variableNameA, string $variableNameB, string $stringTitle, @mixed  $defaultValueA, @mixed  $defaultValueB, string $unitAB = `empty string`, string $helpAB = `empty string`, string $middleText = `empty string`)
```

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string`  | **$variableNameA** |  |  |
| `string`  | **$variableNameB** |  |  |
| `string`  | **$stringTitle** |  |  |
| @mixed  | **$defaultValueA** |  |  |
| @mixed  | **$defaultValueB** |  |  |
| `string`  | **$unitAB** | `empty string` |  |
| `string`  | **$helpAB** | `empty string` |  |
| `string`  | **$middleText** | `empty string` |  |
### wrapRebarCount()

Wraps a numeric (as rebar count) and a rebarList (as rebar diameter) block  


```php
public function wrapRebarCount(string $variableNameCount, string $variableNameRebar, string $titleString, int $defaultValueCount, int $defaultValueRebar = 16, string $help = `empty string`, string $variableNameA = `empty string`): void
```

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string`  | **$variableNameCount** |  | Saves rebar count with this name |
| `string`  | **$variableNameRebar** |  | Saves rebar diameter with this name |
| `string`  | **$titleString** |  |  |
| `int`  | **$defaultValueCount** |  |  |
| `int`  | **$defaultValueRebar** | 16 |  |
| `string`  | **$help** | `empty string` | If empty, sum section area displayed |
| `string`  | **$variableNameA** | `empty string` | If not empty, saves sum section area with this name |
### wrapRebarDistance()

```php
public function wrapRebarDistance(string $variableNameDistance, string $variableNameRebar, string $titleString, int $defaultValueDistance, int $defaultValueRebar = 16, string $help = `empty string`): void
```

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string`  | **$variableNameDistance** |  |  |
| `string`  | **$variableNameRebar** |  |  |
| `string`  | **$titleString** |  |  |
| `int`  | **$defaultValueDistance** |  |  |
| `int`  | **$defaultValueRebar** | 16 |  |
| `string`  | **$help** | `empty string` |  |


---

# SVG

***resist\SVG\SVG*** 

## Public methods 

### __construct()

SVG constructor  


```php
public function __construct(int $width, int $height)
```

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `int`  | **$width** |  | SVG width (x) |
| `int`  | **$height** |  | SVG height (y) |
### addBorder()

Adds border to the generated figure  


```php
public function addBorder(): void
```

### addCircle()

Add circle (use color, line, fill, ratio)  


```php
public function addCircle(float $x, float $y, float $r, float $x0, float $y0): void
```

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `float`  | **$x** |  | Coordinate X of base point - modified by $ratio[0] |
| `float`  | **$y** |  | Coordinate Y of base point - modified by $ratio[1] |
| `float`  | **$r** |  | Radius of circle - not modified by ratio |
| `float`  | **$x0** | 0 | Added to coordinate X - not modified by $ratio[0] |
| `float`  | **$y0** | 0 | Added to coordinate Y - not modified by $ratio[1] |
### addDimH()

Adds horizontal dimension line (uses color, size, ratio)  


```php
public function addDimH(float $xLeft, float $length, float $y, @string|float  $text, float $xLeft0): void
```

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `float`  | **$xLeft** |  | Coordinate X of base point (left) - modified by $ratio[0] |
| `float`  | **$length** |  | Length of dimension line - modified by $ratio[0] |
| `float`  | **$y** |  | Coordinate Y of dimension line - NOT modified by ratio |
| @string,float  | **$text** |  | Text above dim. line |
| `float`  | **$xLeft0** | 0 | Added to $xLeft - NOT modified by $ratio[0] |
### addDimV()

Adds vertical dimension line (uses color, size, ratio)  


```php
public function addDimV(float $yTop, float $length, float $x, @string|float  $text, float $yTop0): void
```

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `float`  | **$yTop** |  | Coordinate Y of base point (top) - modified by $ratio[1] |
| `float`  | **$length** |  | Length of dimension line - modified by $ratio[1] |
| `float`  | **$x** |  | Coordinate X of dimension line - NOT modified by ratio |
| @string,float  | **$text** |  | Text next to the dim. line |
| `float`  | **$yTop0** | 0 | Added to $yTop - NOT modified by $ratio[1] |
### addLine()

Adds simple line (uses color, line, fill)  


```php
public function addLine(float $x1, float $y1, float $x2, float $y2): void
```

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `float`  | **$x1** |  | Coordinate X of start point - modified by $ratio[0] |
| `float`  | **$y1** |  | Coordinate Y of start point - modified by $ratio[1] |
| `float`  | **$x2** |  | Coordinate X of end point - modified by $ratio[0] |
| `float`  | **$y2** |  | Coordinate Y of end point - modified by $ratio[1] |
### addLineRatio()

Adds simple line (use color, line, fill, RATIO)  


```php
public function addLineRatio(float $x1, float $y1, float $x2, float $y2, float $x0, float $y0, bool $useRatio0 = `true`, bool $useRatio1 = `true`): void
```

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `float`  | **$x1** |  | Coordinate X of start point - modified by $ratio[0] |
| `float`  | **$y1** |  | Coordinate Y of start point - modified by $ratio[1] |
| `float`  | **$x2** |  | Coordinate X of end point - modified by $ratio[0] |
| `float`  | **$y2** |  | Coordinate Y of end point - modified by $ratio[1] |
| `float`  | **$x0** | 0 | Added to x1 - NOT modified by $ratio[0] |
| `float`  | **$y0** | 0 | Added to y1 - NOT modified by $ratio[1] |
| `bool`  | **$useRatio0** | `true` | Flag for use or ignore $ratio[0] for x direction |
| `bool`  | **$useRatio1** | `true` | Flag for use or ignore $ratio[1] for y direction |
### addPath()

Adds path (uses color, line, fill)  


```php
public function addPath(string $path): void
```

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string`  | **$path** |  | Alphanumeric string of SVG path object |
### addPolygon()

Adds polygon (uses color, line, fill)  


```php
public function addPolygon(array $points, float $x0, float $y0): void
```

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `array`  | **$points** |  | Nested array of integers of point coordinates: e.g.: [ [10, 20], [100, 200]]. MOdified by $ratio[] |
| `float`  | **$x0** | 0 | Added to coordinate X - not modified by $ratio[0] |
| `float`  | **$y0** | 0 | Added to coordinate Y - not modified by $ratio[1] |
### addRectangle()

Adds rectangle (uses color, line, fill, ratio)  


```php
public function addRectangle(float $x, float $y, float $w, float $h, float $x0, float $y0, float $rx): void
```

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `float`  | **$x** |  | Coordinate X of base point (top left) - modified by $ratio[0] |
| `float`  | **$y** |  | Coordinate Y of base point (top left) - modified by $ratio[1] |
| `float`  | **$w** |  | Width/X direction of rectangle - modified by $ratio[0] |
| `float`  | **$h** |  | Height/Y direction of rectangle - modified by $ratio[1] |
| `float`  | **$x0** | 0 | Added to coordinate X - not modified by $ratio[0] |
| `float`  | **$y0** | 0 | Added to coordinate Y - not modified by $ratio[1] |
| `float`  | **$rx** | 0 | Radius of corners |
### addSymbol()

Adds simple icon font symbol by mapping (uses color, size)  


```php
public function addSymbol(float $x, float $y, string $symbolName): void
```

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `float`  | **$x** |  | Coordinate X of base point |
| `float`  | **$y** |  | Coordinate Y of base point |
| `string`  | **$symbolName** |  | Key in mapping array |
### addText()

Adds simple text (uses color, size)  


```php
public function addText(float $x, float $y, string $text, bool $rotate = `false`, string $style = `empty string`, bool $center = `false`): void
```

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `float`  | **$x** |  | Coordinate X of base point |
| `float`  | **$y** |  | Coordinate Y of base point |
| `string`  | **$text** |  |  |
| `bool`  | **$rotate** | `false` | Rotate text by 270 deg if true |
| `string`  | **$style** | `empty string` | String of style tag |
| `bool`  | **$center** | `false` |  |
### addTrustedRaw()

Adds raw SVG content  
Not validated against XSS - use this method with getImgJpg() or getImgSvg()  


```php
public function addTrustedRaw(string $raw): void
```

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string`  | **$raw** |  | SVG content |
### getImgJpg()

Returns img tag with base64 encoded jpg image  


```php
public function getImgJpg(string $title = `empty string`): string
```

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string`  | **$title** | `empty string` | Title attribute of img tag |
### getImgSvg()

Returns img tag with base64 encoded SVG content  


```php
public function getImgSvg(string $title = `empty string`): string
```

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string`  | **$title** | `empty string` |  |
Returns: `string` 

### getRatio()

Returns ratio  


```php
public function getRatio(): array
```

### getRatioRaw()

Returns array of X and Y ratio for specific object-lengths to fit object into the canvas  


```php
public function getRatioRaw(int $canvasWidth, int $canvasHeight, float $x, float $y): array
```

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `int`  | **$canvasWidth** |  |  |
| `int`  | **$canvasHeight** |  |  |
| `float`  | **$x** |  | X size of figure |
| `float`  | **$y** |  | Y size of figure |
Returns: `array` float[] Array of ratios of X and Y

### getSvg()

Adds end tag and return SVG string  


```php
public function getSvg(): string
```

### makeRatio()

Generates and sets ratio  


```php
public function makeRatio(int $canvasWidth, int $canvasHeight, float $x, float $y): void
```

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `int`  | **$canvasWidth** |  | Width of canvas |
| `int`  | **$canvasHeight** |  | Height of canvas |
| `float`  | **$x** |  | Set ratio[0] according to this |
| `float`  | **$y** |  | Set ratio[1] according to this |
### reset()

Resets properties (line, color, size, fill and ratio) to default  


```php
public function reset(): void
```

### setColor()

Sets color  


```php
public function setColor(string $color): void
```

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string`  | **$color** |  |  |
### setFill()

Sets fill color  


```php
public function setFill(string $color): void
```

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string`  | **$color** |  |  |
### setLine()

Sets line-width  


```php
public function setLine(int $line): void
```

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `int`  | **$line** |  | line-width |
### setRatio()

Sets ratio  


```php
public function setRatio(array $ratio): void
```

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `array`  | **$ratio** |  | Array of X and Y ratio |
### setSize()

Sets size (of text)  


```php
public function setSize(int $size): void
```

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `int`  | **$size** |  |  |


---

