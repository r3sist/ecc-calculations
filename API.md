# Blc

**Ecc\Blc** 

> General user interface "blocks" for Ecc framework - renders and handles blocks.  
Contains wrapper methods only for \Ecc\Block\*Block() classes that extend BlcHandler().  


## Public methods 

### __construct()

**__construct(** *Base* $f3 **):** 

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| *Base* | **$f3** |  | Fatfree Framework Base class |
### boo()

> Builds checkbox field  


**boo(** `string`  $variableName, `array`  $title, [`bool`  $defaultValue], [ $help] **):** `string` 

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string`  | **$variableName** |  | Stores data in F3-Hive with a name of _ prefix and this ID |
| `array`  | **$title** |  | Header of block: [(string) ASCIIMath expression or empty string, (string) title text] |
| `bool`  | **$defaultValue** | `false` | Input default value |
| @string  | **$help** | "" | Markdown help text. Can contain $$ math. |
### bulk()

> Builds multi-input table as dynamic block.  


**bulk(** `string`  $variableName, `array`  $fields, [ $defaultRow] **):** `void` 

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string`  | **$variableName** |  | Stores data in F3-Hive with a name of _ prefix and this ID |
| `array`  | **$fields** |  | Source data of table. Sub arrays keys: "name" => string, "title" => string, "type" => string: "value" "input", "key" => string: for value types, "sum" => bool |
|  | **$defaultRow** | [] |  |
### danger()

> Builds danger block.  


**danger(** `string`  $mdLight, [`string`  $title] **):** `void` 

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string`  | **$mdLight** |  | Multi-line markdown text as content. Can contain $$ math expression. |
| `string`  | **$title** | `empty string` |  |
### danger0()

> Builds danger region INITIAL block. Call danger1() method for closing.  


**danger0(** [`string`  $title] **):** `void` 

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string`  | **$title** | `empty string` | Title of region. |
### danger1()

> Closes danger0() block.  


**danger1(**  **):** `void` 

### def()

> Builds definition block, stores given value to named variable and renders math block with custom text.  


**def(** `string`  $variableName,  $result, [`string`  $mathExpression], [`string`  $help], [`string`  $gumpValidation] **):** `string` 

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string`  | **$variableName** |  | Stores data in F3-Hive with a name of _ prefix and this ID |
| @mixed  | **$result** |  | Stores this as data |
| `string`  | **$mathExpression** | "%%" | ASCIIMath expression without $ delimiters. Use "%%" to substitute result. |
| `string`  | **$help** | `empty string` | Markdown help text |
| `string`  | **$gumpValidation** | `empty string` | GUMP validator string: https://github.com/Wixel/GUMP#star-available-validators |
### h1()

> Builds first order header block.  


**h1(** `string`  $headingTitle, [`string`  $subTitle] **):** `string` 

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string`  | **$headingTitle** |  | Text of header |
| `string`  | **$subTitle** | `empty string` | Markdown text of sub-header |
### h2()

> Builds second order header block.  


**h2(** `string`  $headingTitle, [`string`  $subTitle] **):** `string` 

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string`  | **$headingTitle** |  | Text of header |
| `string`  | **$subTitle** | `empty string` | Markdown text of sub-header |
### h3()

> Builds third order header block.  


**h3(** `string`  $headingTitle, [`string`  $subTitle] **):** `string` 

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string`  | **$headingTitle** |  | Text of header |
| `string`  | **$subTitle** | `empty string` | Markdown text of sub-header |
### h4()

> Builds fourth order header block.  


**h4(** `string`  $headingTitle, [`string`  $subTitle] **):** `string` 

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string`  | **$headingTitle** |  | Text of header |
| `string`  | **$subTitle** | `empty string` | Markdown text of sub-header |
### hr()

> Builds horizontal line (hr HTML tag) block.  


**hr(**  **):** `void` 

### html()

> Builds html block.  


**html(** `string`  $html **):** `void` 

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string`  | **$html** |  | HTML content |
### img()

> Builds image block.  


**img(** `string`  $URLorBase64, [`string`  $caption] **):** `void` 

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string`  | **$URLorBase64** |  | Valid HTTP URL or Base64 image content. Base64 is PNG only, without "data:image/png;base64," |
| `string`  | **$caption** | `empty string` |  |
### info()

> Builds info block.  


**info(** `string`  $mdLight, [`string`  $title] **):** `void` 

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string`  | **$mdLight** |  | Multi-line markdown text as content. Can contain $$ math expression. |
| `string`  | **$title** | `empty string` |  |
### info0()

> Builds info region INITIAL block. Call info1() method for closing.  


**info0(** [`string`  $title] **):** `void` 

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string`  | **$title** | `empty string` | Title of region. |
### info1()

> Closes info0() block.  


**info1(**  **):** `void` 

### input()

> Builds general input field  


**input(** `string`  $variableName, `array`  $title, [ $defaultValue], [`string`  $unit], [`string`  $help], [`string`  $gumpValidation] **):** `string` 

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

**instance(**  **):** 

### jsx()

> Builds html block with JSXGraph content. Load driver() first.  


**jsx(** `string`  $id, [`string`  $js], [`int`  $height] **):** `void` 

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string`  | **$id** |  | Block ID |
| `string`  | **$js** | "console.log("jsx");" | JSXGraph JS content |
| `int`  | **$height** | 200 | Height of rendered block |
### jsxDriver()

> Injects JSXGraph library to calculation.  


**jsxDriver(**  **):** `void` 

### label()

> Build label block.  


**label(**  $value, [`string`  $text] **):** `string` 

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| @float,int,string  | **$value** |  | Can be "yes" for green, "no" for red or numeric converted to %. If $value < 1 then label is green. |
| `string`  | **$text** | `empty string` | Additional text in label |
### lst()

> Builds selection list  


**lst(** `string`  $variableName, `array`  $source, `array`  $title, [ $defaultValue], [`string`  $help] **):** `string` 

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string`  | **$variableName** |  | Stores data in F3-Hive with a name of _ prefix and this ID |
| `array`  | **$source** |  | List items. Items: (int|float|string) name => (int|float|string) value |
| `array`  | **$title** |  | Header of block: [(string) ASCIIMath expression or empty string, (string) title text] |
| @float,int,string  | **$defaultValue** | "" | Input default value |
| `string`  | **$help** | `empty string` | Markdown help text. Can contain $$ math. |
### math()

> Builds math expression block.  


**math(** `string`  $mathExpression, [`string`  $help] **):** `void` 

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string`  | **$mathExpression** |  | ASCIIMath expression without $ delimiters. Use "%%%" to substitute horizontal separator. |
| `string`  | **$help** | `empty string` | Markdown help text |
### md()

> Builds markdown block.  


**md(** `string`  $mdLight **):** `string` 

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string`  | **$mdLight** |  | Multi-line markdown text |
### note()

> Builds note block.  


**note(** `string`  $mdStrict **):** `void` 

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string`  | **$mdStrict** |  | Markdown text |
### numeric()

> Builds numeric input field. (General HTML input tag with numeric validation). Accepts and converts math expression strings in "=2+3" format  


**numeric(** `string`  $variableName, `array`  $title, `float`  $defaultValue, [`string`  $unit], [`string`  $help], [`string`  $additionalGumpValidation] **):** `string` 

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string`  | **$variableName** |  | Stores data in F3-Hive with a name of _ prefix and this ID |
| `array`  | **$title** |  | Header of block: [(string) ASCIIMath expression or empty string, (string) title text] |
| `float`  | **$defaultValue** |  | Input default value |
| `string`  | **$unit** | `empty string` | Input group extension, UI only. |
| `string`  | **$help** | `empty string` | Markdown help text. Can contain $$ math. |
| `string`  | **$additionalGumpValidation** | `empty string` | GUMP validator string ("numeric" is set always): https://github.com/Wixel/GUMP#star-available-validators |
### pre()

> Builds block contains pre HTML tag  


**pre(** `string`  $plainText **):** `void` 

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string`  | **$plainText** |  |  |
### region0()

> Builds collapsible region INITIAL block. Call region1() method for closing.  


**region0(** `string`  $name, [`string`  $title] **):** `void` 

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string`  | **$name** |  | Block ID |
| `string`  | **$title** | "Számítások" | Title of region |
### region1()

> Closes region0() block.  


**region1(**  **):** `void` 

### success()

> Builds success block.  


**success(** `string`  $mdLight, [`string`  $title] **):** `void` 

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string`  | **$mdLight** |  | Multi-line markdown text as content. Can contain $$ math expression. |
| `string`  | **$title** | `empty string` |  |
### success0()

> Builds success region INITIAL block. Call success1() method for closing.  


**success0(** [`string`  $title] **):** `void` 

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string`  | **$title** | `empty string` | Title of region. |
### success1()

> Closes success0() block.  


**success1(**  **):** `void` 

### svg()

> Builds SVG content block.  


**svg(** *resist\SVG\SVG* $svgObject, [`bool`  $forceJpg], [`string`  $caption] **):** `void` 

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| *resist\SVG\SVG* | **$svgObject** |  | SVG object generated by resist\SVG\SVG |
| `bool`  | **$forceJpg** | `false` | Renders SVG as JPG if true |
| `string`  | **$caption** | `empty string` | Help text of block |
### tbl()

> Builds table block from array.  


**tbl(** `array`  $scheme, `array`  $rows, [`string`  $name], [`string`  $caption] **):** `void` 

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `array`  | **$scheme** |  | 1 dimensional array of header columns |
| `array`  | **$rows** |  | Array of rows' content array |
| `string`  | **$name** | "tbl" | Block ID - Class name of tbody tag for scripting |
| `string`  | **$caption** | `empty string` | Help text of table |
### toast()

> Builds toaster block.  


**toast(** `string`  $textMdStrict, [`string`  $type], [`string`  $titleMdStrict] **):** `void` 

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string`  | **$textMdStrict** |  | One-line markdown string |
| `string`  | **$type** | "info" | Toaster type (color and icon) |
| `string`  | **$titleMdStrict** | `empty string` | One-line markdown string for toaster heading |
### toc()

> Builds table of content block.  


**toc(**  **):** `void` 

### txt()

> Builds plain text block.  


**txt(** `string`  $mdStrict, [`string`  $help] **):** `string` 

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string`  | **$mdStrict** |  | Markdown text, can contain $$ math expression |
| `string`  | **$help** | `empty string` | Markdown help text, can contain $$ math expression |
### wrapper0()

> Builds wrapped blocks. STARTER block.  


**wrapper0(** [`string`  $stringTitle] **):** `void` 

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string`  | **$stringTitle** | `empty string` | Title line of whole wrapped blocks |
### wrapper1()

> Builds wrapped blocks. SEPARATOR block.  


**wrapper1(** [`string`  $middleTextMd] **):** `void` 

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string`  | **$middleTextMd** | `empty string` | Separator text between wrapped blocks |
### wrapper2()

**wrapper2(** [`string`  $help] **):** `void` 

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string`  | **$help** | `empty string` | Help text below wrapped blocks |
### write()

> Builds and handles write blocks - Generates new image with text on base image; Renders img block  
["text" => (string),  
"x" => (int) position,  
"y" => (int) position,  
"size" => (int) text size]  


**write(** `string`  $imageFile, `array`  $textArray, [`string`  $caption] **):** `void` 

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string`  | **$imageFile** |  | Base image as canvas relative to codebase home, e.g.: "vendor/resist/ecc-calculations/canvas/linQ0.jpg" |
| `array`  | **$textArray** |  | Array of texts. Sub array keys: |
| `string`  | **$caption** | `empty string` | Help text of block |
Auto generated public API documentation at 2020.10.17.



---

# Ec

**Ec\Ec** 

> Eurocode globals, helpers and predefined GUI elements for ECC framework  
(c) Bence VÁNKOS  
https:// structure.hu  


## Public methods 

### A()

**A(** `float`  $D, [`float`  $multiplicator] **):** `float` 

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `float`  | **$D** |  |  |
| `float`  | **$multiplicator** | 1 |  |
### BpRd()

**BpRd(** `string`  $btName, `string`  $stMat, `float`  $t **):** `float` 

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string`  | **$btName** |  |  |
| `string`  | **$stMat** |  |  |
| `float`  | **$t** |  |  |
### FbRd()

**FbRd(** `string`  $btName,  $btMat, `string`  $stMat, `float`  $ep1, `float`  $ep2, `float`  $t, `bool`  $inner **):** `float` 

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

**FtRd(** `string`  $btName,  $btMat, [`bool`  $verbose] **):** `float` 

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string`  | **$btName** |  |  |
|  | **$btMat** |  |  |
| `bool`  | **$verbose** | `true` |  |
### FvRd()

**FvRd(** `string`  $btName,  $btMat, `float`  $n, [`float`  $As] **):** `float` 

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string`  | **$btName** |  |  |
| @float,string  | **$btMat** |  |  |
| `float`  | **$n** |  |  |
| `float`  | **$As** | 0 |  |
### McRd()

**McRd(** `float`  $W, `float`  $fy **):** `float` 

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `float`  | **$W** |  |  |
| `float`  | **$fy** |  |  |
### NcRd()

**NcRd(** `float`  $A, `float`  $fy **):** `float` 

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `float`  | **$A** |  |  |
| `float`  | **$fy** |  |  |
### NplRd()

**NplRd(**  $A, `string`  $matName,  $t **):** `float` 

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
|  | **$A** |  |  |
| `string`  | **$matName** |  |  |
|  | **$t** |  |  |
### NtRd()

**NtRd(**  $A,  $Anet, `string`  $matName,  $t **):** `float` 

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
|  | **$A** |  |  |
|  | **$Anet** |  |  |
| `string`  | **$matName** |  |  |
|  | **$t** |  |  |
### NuRd()

**NuRd(**  $Anet, `string`  $matName,  $t **):** `float` 

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
|  | **$Anet** |  |  |
| `string`  | **$matName** |  |  |
|  | **$t** |  |  |
### VplRd()

**VplRd(** `float`  $Av, `string`  $matName, `float`  $t **):** `float` 

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `float`  | **$Av** |  |  |
| `string`  | **$matName** |  |  |
| `float`  | **$t** |  |  |
### __construct()

> Ec constructor.  
Defines Eurocode parameters in hive: __GG, __GQ, __GM0, __GM2, __GM3, __GM3ser, __GM6ser, __Gc, __Gs, __GS, __GcA, __GSA  


**__construct(** *Base* $f3, *Ecc\Blc* $blc, *DB\SQL* $db, *Ecc\Map\DataMap* $dataMap **):** 

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| *Base* | **$f3** |  |  |
| *Ecc\Blc* | **$blc** |  |  |
| *DB\SQL* | **$db** |  |  |
| *Ecc\Map\DataMap* | **$dataMap** |  |  |
### boltList()

**boltList(** [`string`  $variableName], [`string`  $default], [`string`  $title] **):** `void` 

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string`  | **$variableName** | "bolt" |  |
| `string`  | **$default** | "M16" |  |
| `string`  | **$title** | "Csavar betöltése" |  |
### boltProp()

**boltProp(** `string`  $boltName, `string`  $propertyName **):** `float` 

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string`  | **$boltName** |  |  |
| `string`  | **$propertyName** |  |  |
### chooseRoot()

**chooseRoot(** `float`  $estimation, `array`  $roots **):** `float` 

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `float`  | **$estimation** |  |  |
| `array`  | **$roots** |  |  |
### fu()

**fu(** `string`  $matName, `float`  $t **):** `float` 

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string`  | **$matName** |  |  |
| `float`  | **$t** |  | Thickness of relevant plate [mm] |
Returns: `float`  Ultimate strength in [N/mm^2; MPa]

### fy()

**fy(** `string`  $materialName, `float`  $t **):** `float` 

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string`  | **$materialName** |  |  |
| `float`  | **$t** |  | Thickness of relevant plate [mm] |
Returns: `float`  Yield strength in [N/mm^2; MPa]

### getClosest()

**getClosest(** `float`  $find, `array`  $stackArray, [`string`  $returnType] **):** `float` 

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `float`  | **$find** |  |  |
| `array`  | **$stackArray** |  |  |
| `string`  | **$returnType** | "closest" |  |
### getMaterialArray()

> Get all material data as array from database  
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


**getMaterialArray(**  **):** `array` 

Returns: `array`  Assoc. array of read material data

### linterp()

**linterp(** `float`  $x1, `float`  $y1, `float`  $x2, `float`  $y2, `float`  $x **):** `float` 

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `float`  | **$x1** |  |  |
| `float`  | **$y1** |  |  |
| `float`  | **$x2** |  |  |
| `float`  | **$y2** |  |  |
| `float`  | **$x** |  |  |
### matList()

**matList(** [`string`  $variableName], [`string`  $default], [`array`  $title], [`string`  $category] **):** `void` 

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string`  | **$variableName** | "mat" |  |
| `string`  | **$default** | "S235" |  |
| `array`  | **$title** | ["","Anyagminőség"] |  |
| `string`  | **$category** | `empty string` |  |
### matProp()

**matProp(** `string`  $materialName, `string`  $propertyName **):** `float` 

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string`  | **$materialName** |  |  |
| `string`  | **$propertyName** |  |  |
### qpz()

**qpz(** `float`  $z, `float`  $terrainCat **):** `float` 

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `float`  | **$z** |  |  |
| `float`  | **$terrainCat** |  |  |
### quadratic()

> Original: https://github.com/hellofromtonya/Quadratic/blob/master/solver.php  


**quadratic(** `float`  $a, `float`  $b, `float`  $c, [`int`  $precision] **):** `array` 

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `float`  | **$a** |  |  |
| `float`  | **$b** |  |  |
| `float`  | **$c** |  |  |
| `int`  | **$precision** | 3 |  |
Returns: `array`  may contain 'i'

### readData()

> Get data record by data_id from ecc_data table  


**readData(** `string`  $dataName **):** `array` 

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string`  | **$dataName** |  | Name of data as dataset identifier |
Returns: `array`  Associative array of read data

### rebarList()

**rebarList(** [`string`  $variableName], [`float`  $default], [`array`  $title], [`string`  $help] **):** `void` 

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string`  | **$variableName** | "fi" |  |
| `float`  | **$default** | 16 |  |
| `array`  | **$title** | ["","Vasátmérő"] |  |
| `string`  | **$help** | `empty string` |  |
### rebarTable()

**rebarTable(** [`string`  $variableNameBulk] **):** `float` 

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string`  | **$variableNameBulk** | "As" |  |
### sectionFamilyList()

**sectionFamilyList(** [`string`  $variableName], [`string`  $title], [`string`  $default] **):** `void` 

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string`  | **$variableName** | "sectionFamily" |  |
| `string`  | **$title** | "Szelvény család" |  |
| `string`  | **$default** | "HEA" |  |
### sectionList()

**sectionList(** [`string`  $familyName], [`string`  $variableName], [`string`  $title], [`string`  $default] **):** `void` 

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string`  | **$familyName** | "HEA" |  |
| `string`  | **$variableName** | "sectionName" |  |
| `string`  | **$title** | "Szelvény név" |  |
| `string`  | **$default** | "HEA200" |  |
### spreadMaterialData()

> Saves all material properties from DB to Hive variables, with prefix. e.g.: _prefixfck, _prefixfy etc  


**spreadMaterialData(**  $matName, [`string`  $prefix] **):** `void` 

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| @float,string  | **$matName** |  |  |
| `string`  | **$prefix** | `empty string` |  |
### spreadSectionData()

**spreadSectionData(** `string`  $sectionName, [`bool`  $renderTable], [`string`  $arrayName] **):** `void` 

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string`  | **$sectionName** |  |  |
| `bool`  | **$renderTable** | `false` |  |
| `string`  | **$arrayName** | "sectionData" |  |
### wrapNumerics()

**wrapNumerics(** `string`  $variableNameA, `string`  $variableNameB, `string`  $stringTitle,  $defaultValueA,  $defaultValueB, [`string`  $unitAB], [`string`  $helpAB], [`string`  $middleText] **):** 

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

> Wraps a numeric (as rebar count) and a rebarList (as rebar diameter) block  


**wrapRebarCount(** `string`  $variableNameCount, `string`  $variableNameRebar, `string`  $titleString, `int`  $defaultValueCount, [`int`  $defaultValueRebar], [`string`  $help], [`string`  $variableNameA] **):** `void` 

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

**wrapRebarDistance(** `string`  $variableNameDistance, `string`  $variableNameRebar, `string`  $titleString, `int`  $defaultValueDistance, [`int`  $defaultValueRebar], [`string`  $help] **):** `void` 

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string`  | **$variableNameDistance** |  |  |
| `string`  | **$variableNameRebar** |  |  |
| `string`  | **$titleString** |  |  |
| `int`  | **$defaultValueDistance** |  |  |
| `int`  | **$defaultValueRebar** | 16 |  |
| `string`  | **$help** | `empty string` |  |
Auto generated public API documentation at 2020.10.17.



---

# SVG

**resist\SVG\SVG** 



## Public methods 

### __construct()

> SVG constructor  


**__construct(** `int`  $width, `int`  $height **):** 

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `int`  | **$width** |  | SVG width (x) |
| `int`  | **$height** |  | SVG height (y) |
### addBorder()

> Adds border to the generated figure  


**addBorder(**  **):** `void` 

### addCircle()

> Add circle (use color, line, fill, ratio)  


**addCircle(** `float`  $x, `float`  $y, `float`  $r, [`float`  $x0], [`float`  $y0] **):** `void` 

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `float`  | **$x** |  | Coordinate X of base point - modified by $ratio[0] |
| `float`  | **$y** |  | Coordinate Y of base point - modified by $ratio[1] |
| `float`  | **$r** |  | Radius of circle - not modified by ratio |
| `float`  | **$x0** | 0 | Added to coordinate X - not modified by $ratio[0] |
| `float`  | **$y0** | 0 | Added to coordinate Y - not modified by $ratio[1] |
### addDimH()

> Adds horizontal dimension line (uses color, size, ratio)  


**addDimH(** `float`  $xLeft, `float`  $length, `float`  $y,  $text, [`float`  $xLeft0] **):** `void` 

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `float`  | **$xLeft** |  | Coordinate X of base point (left) - modified by $ratio[0] |
| `float`  | **$length** |  | Length of dimension line - modified by $ratio[0] |
| `float`  | **$y** |  | Coordinate Y of dimension line - NOT modified by ratio |
| @string,float  | **$text** |  | Text above dim. line |
| `float`  | **$xLeft0** | 0 | Added to $xLeft - NOT modified by $ratio[0] |
### addDimV()

> Adds vertical dimension line (uses color, size, ratio)  


**addDimV(** `float`  $yTop, `float`  $length, `float`  $x,  $text, [`float`  $yTop0] **):** `void` 

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `float`  | **$yTop** |  | Coordinate Y of base point (top) - modified by $ratio[1] |
| `float`  | **$length** |  | Length of dimension line - modified by $ratio[1] |
| `float`  | **$x** |  | Coordinate X of dimension line - NOT modified by ratio |
| @string,float  | **$text** |  | Text next to the dim. line |
| `float`  | **$yTop0** | 0 | Added to $yTop - NOT modified by $ratio[1] |
### addLine()

> Adds simple line (uses color, line, fill)  


**addLine(** `float`  $x1, `float`  $y1, `float`  $x2, `float`  $y2 **):** `void` 

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `float`  | **$x1** |  | Coordinate X of start point - modified by $ratio[0] |
| `float`  | **$y1** |  | Coordinate Y of start point - modified by $ratio[1] |
| `float`  | **$x2** |  | Coordinate X of end point - modified by $ratio[0] |
| `float`  | **$y2** |  | Coordinate Y of end point - modified by $ratio[1] |
### addLineRatio()

> Adds simple line (use color, line, fill, RATIO)  


**addLineRatio(** `float`  $x1, `float`  $y1, `float`  $x2, `float`  $y2, [`float`  $x0], [`float`  $y0], [`bool`  $useRatio0], [`bool`  $useRatio1] **):** `void` 

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

> Adds path (uses color, line, fill)  


**addPath(** `string`  $path **):** `void` 

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string`  | **$path** |  | Alphanumeric string of SVG path object |
### addPolygon()

> Adds polygon (uses color, line, fill)  


**addPolygon(** `array`  $points, [`float`  $x0], [`float`  $y0] **):** `void` 

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `array`  | **$points** |  | Nested array of integers of point coordinates: e.g.: [ [10, 20], [100, 200]]. MOdified by $ratio[] |
| `float`  | **$x0** | 0 | Added to coordinate X - not modified by $ratio[0] |
| `float`  | **$y0** | 0 | Added to coordinate Y - not modified by $ratio[1] |
### addRectangle()

> Adds rectangle (uses color, line, fill, ratio)  


**addRectangle(** `float`  $x, `float`  $y, `float`  $w, `float`  $h, [`float`  $x0], [`float`  $y0], [`float`  $rx] **):** `void` 

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

> Adds simple icon font symbol by mapping (uses color, size)  


**addSymbol(** `float`  $x, `float`  $y, `string`  $symbolName **):** `void` 

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `float`  | **$x** |  | Coordinate X of base point |
| `float`  | **$y** |  | Coordinate Y of base point |
| `string`  | **$symbolName** |  | Key in mapping array |
### addText()

> Adds simple text (uses color, size)  


**addText(** `float`  $x, `float`  $y, `string`  $text, [`bool`  $rotate], [`string`  $style], [`bool`  $center] **):** `void` 

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `float`  | **$x** |  | Coordinate X of base point |
| `float`  | **$y** |  | Coordinate Y of base point |
| `string`  | **$text** |  |  |
| `bool`  | **$rotate** | `false` | Rotate text by 270 deg if true |
| `string`  | **$style** | `empty string` | String of style tag |
| `bool`  | **$center** | `false` |  |
### addTrustedRaw()

> Adds raw SVG content  
Not validated against XSS - use this method with getImgJpg() or getImgSvg()  


**addTrustedRaw(** `string`  $raw **):** `void` 

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string`  | **$raw** |  | SVG content |
### getImgJpg()

> Returns img tag with base64 encoded jpg image  


**getImgJpg(** [`string`  $title] **):** `string` 

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string`  | **$title** | `empty string` | Title attribute of img tag |
### getImgSvg()

> Returns img tag with base64 encoded SVG content  


**getImgSvg(** [`string`  $title] **):** `string` 

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string`  | **$title** | `empty string` |  |
Returns: `string`  

### getRatio()

> Returns ratio  


**getRatio(**  **):** `array` 

### getRatioRaw()

> Returns array of X and Y ratio for specific object-lengths to fit object into the canvas  


**getRatioRaw(** `int`  $canvasWidth, `int`  $canvasHeight, `float`  $x, `float`  $y **):** `array` 

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `int`  | **$canvasWidth** |  |  |
| `int`  | **$canvasHeight** |  |  |
| `float`  | **$x** |  |  |
| `float`  | **$y** |  |  |
Returns: `array`  

### getSvg()

> Adds end tag and return SVG string  


**getSvg(**  **):** `string` 

### makeRatio()

> Generates and sets ratio  


**makeRatio(** `int`  $canvasWidth, `int`  $canvasHeight, `float`  $x, `float`  $y **):** `void` 

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `int`  | **$canvasWidth** |  | Width of canvas |
| `int`  | **$canvasHeight** |  | Height of canvas |
| `float`  | **$x** |  | Set ratio[0] according to this |
| `float`  | **$y** |  | Set ratio[1] according to this |
### reset()

> Resets properties (line, color, size, fill and ratio) to default  


**reset(**  **):** `void` 

### setColor()

> Sets color  


**setColor(** `string`  $color **):** `void` 

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string`  | **$color** |  |  |
### setFill()

> Sets fill color  


**setFill(** `string`  $color **):** `void` 

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string`  | **$color** |  |  |
### setLine()

> Sets line-width  


**setLine(** `int`  $line **):** `void` 

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `int`  | **$line** |  | line-width |
### setRatio()

> Sets ratio  


**setRatio(** `array`  $ratio **):** `void` 

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `array`  | **$ratio** |  | Array of X and Y ratio |
### setSize()

> Sets size (of text)  


**setSize(** `int`  $size **):** `void` 

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `int`  | **$size** |  |  |
Auto generated public API documentation at 2020.10.17.



---

