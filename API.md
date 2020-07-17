# Blc

> General user interface "blocks" for Ecc framework  


Auto generated public API documentation of class ***Ecc\Blc*** at 2020.07.17.

## Public methods 

### __construct()

> Blc constructor.  


**__construct(** *Base* $f3 **):** ` `

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| *Base* | **$f3** |  | Fatfree Framework Base class |
### boo()

> Renders checkbox field  


**boo(** `string ` $variableName, `array ` $title, [`bool ` $defaultValue], [` ` $help] **):** `string `

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string ` | **$variableName** |  |  |
| `array ` | **$title** |  |  |
| `bool ` | **$defaultValue** | `false` |  |
|  | **$help** | `empty string` |  |
### bulk()

**bulk(** `string ` $variableName, `array ` $fields **):** ` `

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string ` | **$variableName** |  |  |
| `array ` | **$fields** |  |  |
### danger()

**danger(** `string ` $mdLight, [`string ` $title] **):** `void `

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string ` | **$mdLight** |  |  |
| `string ` | **$title** | `empty string` |  |
### danger0()

**danger0(** [`string ` $title] **):** `void `

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string ` | **$title** | `empty string` |  |
### danger1()

**danger1(**  **):** `void `

### def()

**def(** `string ` $variableName, ` ` $result, [`string ` $mathExpression], [`string ` $help], [`string ` $gumpValidation] **):** `string `

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string ` | **$variableName** |  |  |
| @mixed  | **$result** |  |  |
| `string ` | **$mathExpression** | "%%" |  |
| `string ` | **$help** | `empty string` |  |
| `string ` | **$gumpValidation** | `empty string` |  |
### h1()

> Renders first order header  


**h1(** `string ` $headingTitle, [`string ` $subTitle] **):** `string `

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string ` | **$headingTitle** |  |  |
| `string ` | **$subTitle** | `empty string` |  |
### h2()

> Renders second order header  


**h2(** `string ` $headingTitle, [`string ` $subTitle] **):** `string `

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string ` | **$headingTitle** |  |  |
| `string ` | **$subTitle** | `empty string` |  |
### h3()

> Renders third order header  


**h3(** `string ` $headingTitle, [`string ` $subTitle] **):** `string `

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string ` | **$headingTitle** |  |  |
| `string ` | **$subTitle** | `empty string` |  |
### h4()

> Renders 4th order header  


**h4(** `string ` $headingTitle, [`string ` $subTitle] **):** `string `

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string ` | **$headingTitle** |  |  |
| `string ` | **$subTitle** | `empty string` |  |
### hr()

> Renders horizontal line  


**hr(**  **):** `void `

### html()

> Renders html  


**html(** `string ` $html **):** `void `

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string ` | **$html** |  |  |
### img()

> Renders image  


**img(** `string ` $URLorBase64, [`string ` $caption] **):** `void `

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string ` | **$URLorBase64** |  |  |
| `string ` | **$caption** | `empty string` |  |
### info()

**info(** `string ` $mdLight, [`string ` $title] **):** `void `

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string ` | **$mdLight** |  |  |
| `string ` | **$title** | `empty string` |  |
### info0()

**info0(** [`string ` $title] **):** `void `

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string ` | **$title** | `empty string` |  |
### info1()

**info1(**  **):** `void `

### input()

> Renders input field  


**input(** `string ` $variableName, `array ` $title, [` ` $defaultValue], [`string ` $unit], [`string ` $help], [`string ` $gumpValidation] **):** `string `

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string ` | **$variableName** |  |  |
| `array ` | **$title** |  |  |
| @mixed  | **$defaultValue** | `empty string` |  |
| `string ` | **$unit** | `empty string` |  |
| `string ` | **$help** | `empty string` |  |
| `string ` | **$gumpValidation** | `empty string` |  |
### instance()

**DEPRECATED** This static method is used by a few template // TODO remove them

**instance(**  **):** ` `

### jsx()

**jsx(** `string ` $id, [`string ` $js], [`int ` $height] **):** `void `

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string ` | **$id** |  |  |
| `string ` | **$js** | "console.log("jsx");" |  |
| `int ` | **$height** | 200 |  |
### jsxDriver()

**jsxDriver(**  **):** `void `

### label()

**label(** ` ` $value, [`string ` $text] **):** `string `

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| @mixed  | **$value** |  |  |
| `string ` | **$text** | `empty string` |  |
### lst()

> Renders selection list  


**lst(** `string ` $variableName, `array ` $source, `array ` $title, [` ` $defaultValue], [`string ` $help] **):** `string `

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string ` | **$variableName** |  |  |
| `array ` | **$source** |  |  |
| `array ` | **$title** |  |  |
| @mixed  | **$defaultValue** | `empty string` |  |
| `string ` | **$help** | `empty string` |  |
### math()

> Renders math expression  


**math(** `string ` $mathExpression, [`string ` $help] **):** `void `

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string ` | **$mathExpression** |  |  |
| `string ` | **$help** | `empty string` |  |
### md()

> Renders markdown  


**md(** `string ` $mdLight **):** `string `

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string ` | **$mdLight** |  |  |
### note()

> Renders note  


**note(** `string ` $mdStrict **):** `void `

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string ` | **$mdStrict** |  |  |
### numeric()

> Renders numeric input field  


**numeric(** `string ` $variableName, `array ` $title, `float ` $defaultValue, [`string ` $unit], [`string ` $help], [`string ` $additionalGumpValidation] **):** `string `

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string ` | **$variableName** |  |  |
| `array ` | **$title** |  |  |
| `float ` | **$defaultValue** |  |  |
| `string ` | **$unit** | `empty string` |  |
| `string ` | **$help** | `empty string` |  |
| `string ` | **$additionalGumpValidation** | `empty string` |  |
### pre()

> Renders pre text  


**pre(** `string ` $plainText **):** `void `

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string ` | **$plainText** |  |  |
### region0()

**region0(** `string ` $name, [`string ` $title] **):** `void `

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string ` | **$name** |  |  |
| `string ` | **$title** | "Számítások" |  |
### region1()

**region1(**  **):** ` `

### success()

**success(** `string ` $mdLight, [`string ` $title] **):** `void `

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string ` | **$mdLight** |  |  |
| `string ` | **$title** | `empty string` |  |
### success0()

**success0(** [`string ` $title] **):** `void `

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string ` | **$title** | `empty string` |  |
### success1()

**success1(**  **):** `void `

### svg()

**svg(** *resist\SVG\SVG* $svgObject, [`bool ` $forceJpg], [`string ` $caption] **):** `void `

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| *resist\SVG\SVG* | **$svgObject** |  |  |
| `bool ` | **$forceJpg** | `false` |  |
| `string ` | **$caption** | `empty string` |  |
### tbl()

> Table rendering  


**tbl(** `array ` $scheme, `array ` $rows, [`string ` $name], [` ` $caption] **):** `void `

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `array ` | **$scheme** |  | 1 dim array of header |
| `array ` | **$rows** |  | Nested array of rows and cols |
| `string ` | **$name** | "tbl" | Class name of tbody tag for scripting |
|  | **$caption** | `empty string` |  |
### toast()

**toast(** `string ` $textMdStrict, [`string ` $type], [`string ` $titleMdStrict] **):** ` `

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string ` | **$textMdStrict** |  |  |
| `string ` | **$type** | "info" |  |
| `string ` | **$titleMdStrict** | `empty string` |  |
### toc()

**toc(**  **):** `void `

### txt()

> Renders plain text  


**txt(** `string ` $mdStrict, [`string ` $help] **):** `string `

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string ` | **$mdStrict** |  |  |
| `string ` | **$help** | `empty string` |  |
### wrapper0()

**wrapper0(** [`string ` $stringTitle] **):** ` `

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string ` | **$stringTitle** | `empty string` |  |
### wrapper1()

**wrapper1(** [`string ` $middleTextMd] **):** ` `

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string ` | **$middleTextMd** | `empty string` |  |
### wrapper2()

**wrapper2(** [`string ` $help] **):** ` `

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string ` | **$help** | `empty string` |  |
### write()

**write(** `string ` $imageFile, `array ` $textArray, [`string ` $caption] **):** `void `

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string ` | **$imageFile** |  |  |
| `array ` | **$textArray** |  |  |
| `string ` | **$caption** | `empty string` |  |


---

# Ec

> Eurocode globals and predefined GUI elements for ECC framework  
(c) Bence VÁNKOS  
https:// structure.hu  


Auto generated public API documentation of class ***Ec\Ec*** at 2020.07.17.

## Public properties 

| Type | Property name |
| --- | --- |
| *Base* | **$f3** |
## Public methods 

### A()

**A(** `float ` $D, [`float ` $multiplicator] **):** `float `

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `float ` | **$D** |  |  |
| `float ` | **$multiplicator** | 1 |  |
### BpRd()

**BpRd(** `string ` $btName, `string ` $stMat, `float ` $t **):** `float `

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string ` | **$btName** |  |  |
| `string ` | **$stMat** |  |  |
| `float ` | **$t** |  |  |
### FbRd()

**FbRd(** `string ` $btName, ` ` $btMat, `string ` $stMat, `float ` $ep1, `float ` $ep2, `float ` $t, `bool ` $inner **):** `float `

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string ` | **$btName** |  |  |
| @float,string  | **$btMat** |  |  |
| `string ` | **$stMat** |  |  |
| `float ` | **$ep1** |  |  |
| `float ` | **$ep2** |  |  |
| `float ` | **$t** |  |  |
| `bool ` | **$inner** |  |  |
### FtRd()

**FtRd(** `string ` $btName, ` ` $btMat, [`bool ` $verbose] **):** `float `

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string ` | **$btName** |  |  |
|  | **$btMat** |  |  |
| `bool ` | **$verbose** | `true` |  |
### FvRd()

**FvRd(** `string ` $btName, ` ` $btMat, `float ` $n, [`float ` $As] **):** `float `

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string ` | **$btName** |  |  |
| @float,string  | **$btMat** |  |  |
| `float ` | **$n** |  |  |
| `float ` | **$As** | 0 |  |
### McRd()

**McRd(** `float ` $W, `float ` $fy **):** `float `

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `float ` | **$W** |  |  |
| `float ` | **$fy** |  |  |
### NcRd()

**NcRd(** `float ` $A, `float ` $fy **):** `float `

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `float ` | **$A** |  |  |
| `float ` | **$fy** |  |  |
### NplRd()

**NplRd(** ` ` $A, `string ` $matName, ` ` $t **):** `float `

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
|  | **$A** |  |  |
| `string ` | **$matName** |  |  |
|  | **$t** |  |  |
### NtRd()

**NtRd(** ` ` $A, ` ` $Anet, `string ` $matName, ` ` $t **):** `float `

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
|  | **$A** |  |  |
|  | **$Anet** |  |  |
| `string ` | **$matName** |  |  |
|  | **$t** |  |  |
### NuRd()

**NuRd(** ` ` $Anet, `string ` $matName, ` ` $t **):** `float `

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
|  | **$Anet** |  |  |
| `string ` | **$matName** |  |  |
|  | **$t** |  |  |
### VplRd()

**VplRd(** `float ` $Av, `string ` $matName, `float ` $t **):** `float `

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `float ` | **$Av** |  |  |
| `string ` | **$matName** |  |  |
| `float ` | **$t** |  |  |
### __construct()

> Ec constructor.  
Defines Eurocode parameters in hive: __GG, __GQ, __GM0, __GM2, __GM3, __GM3ser, __GM6ser, __Gc, __Gs, __GS, __GcA, __GSA  


**__construct(** *Base* $f3, *Ecc\Blc* $blc, *DB\SQL* $db **):** ` `

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| *Base* | **$f3** |  |  |
| *Ecc\Blc* | **$blc** |  |  |
| *DB\SQL* | **$db** |  |  |
### boltList()

**boltList(** [`string ` $variableName], [`string ` $default], [`string ` $title] **):** `void `

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string ` | **$variableName** | "bolt" |  |
| `string ` | **$default** | "M16" |  |
| `string ` | **$title** | "Csavar betöltése" |  |
### boltProp()

**boltProp(** `string ` $name, `string ` $property **):** `float `

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string ` | **$name** |  |  |
| `string ` | **$property** |  |  |
### fu()

**fu(** `string ` $matName, `float ` $t **):** `float `

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string ` | **$matName** |  |  |
| `float ` | **$t** |  |  |
### fy()

**fy(** `string ` $matName, `float ` $t **):** `float `

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string ` | **$matName** |  |  |
| `float ` | **$t** |  |  |
### getClosest()

**getClosest(** `float ` $find, `array ` $stackArray, [`string ` $returnType] **):** `float `

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `float ` | **$find** |  |  |
| `array ` | **$stackArray** |  |  |
| `string ` | **$returnType** | "closest" |  |
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


**getMaterialArray(**  **):** `array `

Returns: `array ` Assoc. array of read material data

### linterp()

**linterp(** `float ` $x1, `float ` $y1, `float ` $x2, `float ` $y2, `float ` $x **):** `float `

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `float ` | **$x1** |  |  |
| `float ` | **$y1** |  |  |
| `float ` | **$x2** |  |  |
| `float ` | **$y2** |  |  |
| `float ` | **$x** |  |  |
### matList()

**matList(** [`string ` $variableName], [`string ` $default], [`array ` $title], [`string ` $category] **):** `void `

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string ` | **$variableName** | "mat" |  |
| `string ` | **$default** | "S235" |  |
| `array ` | **$title** | ["","Anyagminőség"] |  |
| `string ` | **$category** | `empty string` |  |
### matProp()

**matProp(** `string ` $name, `string ` $property **):** `float `

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string ` | **$name** |  |  |
| `string ` | **$property** |  |  |
### qpz()

**qpz(** `float ` $z, `float ` $terrainCat **):** `float `

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `float ` | **$z** |  |  |
| `float ` | **$terrainCat** |  |  |
### quadratic()

> Original: https://github.com/hellofromtonya/Quadratic/blob/master/solver.php  


**quadratic(** `float ` $a, `float ` $b, `float ` $c, [`int ` $precision] **):** `array `

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `float ` | **$a** |  |  |
| `float ` | **$b** |  |  |
| `float ` | **$c** |  |  |
| `int ` | **$precision** | 3 |  |
### readData()

> Get data record by data_id from ecc_data table  


**readData(** `string ` $dbName **):** `array `

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string ` | **$dbName** |  |  |
Returns: `array ` Assoc. array of read data

### rebarList()

**rebarList(** [`string ` $variableName], [`float ` $default], [`array ` $title], [`string ` $help] **):** `void `

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string ` | **$variableName** | "fi" |  |
| `float ` | **$default** | 16 |  |
| `array ` | **$title** | ["","Vasátmérő"] |  |
| `string ` | **$help** | `empty string` |  |
### rebarTable()

**rebarTable(** [`string ` $variableNameBulk] **):** `float `

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string ` | **$variableNameBulk** | "As" |  |
### sectionFamilyList()

**sectionFamilyList(** [`string ` $variableName], [`string ` $title], [`string ` $default] **):** `void `

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string ` | **$variableName** | "sectionFamily" |  |
| `string ` | **$title** | "Szelvény család" |  |
| `string ` | **$default** | "HEA" |  |
### sectionList()

**sectionList(** [`string ` $familyName], [`string ` $variableName], [`string ` $title], [`string ` $default] **):** `void `

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string ` | **$familyName** | "HEA" |  |
| `string ` | **$variableName** | "sectionName" |  |
| `string ` | **$title** | "Szelvény név" |  |
| `string ` | **$default** | "HEA200" |  |
### spreadMaterialData()

> Saves all material properties from DB to Hive variables, with prefix. e.g.: _prefixfck, _prefixfy etc  


**spreadMaterialData(** ` ` $matName, [`string ` $prefix] **):** `void `

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| @float,string  | **$matName** |  |  |
| `string ` | **$prefix** | `empty string` |  |
### spreadSectionData()

**spreadSectionData(** `string ` $sectionName, [`bool ` $renderTable], [`string ` $arrayName] **):** `void `

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string ` | **$sectionName** |  |  |
| `bool ` | **$renderTable** | `false` |  |
| `string ` | **$arrayName** | "sectionData" |  |
### wrapNumerics()

**wrapNumerics(** `string ` $variableNameA, `string ` $variableNameB, `string ` $stringTitle, ` ` $defaultValueA, ` ` $defaultValueB, [`string ` $unitAB], [`string ` $helpAB], [`string ` $middleText] **):** ` `

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string ` | **$variableNameA** |  |  |
| `string ` | **$variableNameB** |  |  |
| `string ` | **$stringTitle** |  |  |
| @mixed  | **$defaultValueA** |  |  |
| @mixed  | **$defaultValueB** |  |  |
| `string ` | **$unitAB** | `empty string` |  |
| `string ` | **$helpAB** | `empty string` |  |
| `string ` | **$middleText** | `empty string` |  |
### wrapRebarCount()

**wrapRebarCount(** `string ` $variableNameCount, `string ` $variableNameRebar, `string ` $titleString, `float ` $defaultValueCount, [`float ` $defaultValueRebar], [`string ` $help] **):** `void `

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string ` | **$variableNameCount** |  |  |
| `string ` | **$variableNameRebar** |  |  |
| `string ` | **$titleString** |  |  |
| `float ` | **$defaultValueCount** |  |  |
| `float ` | **$defaultValueRebar** | 16 |  |
| `string ` | **$help** | `empty string` |  |
### wrapRebarDistance()

**wrapRebarDistance(** `string ` $variableNameDistance, `string ` $variableNameRebar, `string ` $titleString, `int ` $defaultValueDistance, [`int ` $defaultValueRebar], [`string ` $help] **):** `void `

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string ` | **$variableNameDistance** |  |  |
| `string ` | **$variableNameRebar** |  |  |
| `string ` | **$titleString** |  |  |
| `int ` | **$defaultValueDistance** |  |  |
| `int ` | **$defaultValueRebar** | 16 |  |
| `string ` | **$help** | `empty string` |  |


---

