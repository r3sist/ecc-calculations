# Blc

> General user interface "blocks" for Ecc framework  


Auto generated public API documentation of class ***Ecc\Blc*** at 2020.08.05.

## Public methods 

### __construct()

> Blc constructor.  


**__construct(** *Base* $f3 **):** 

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| *Base* | **$f3** |  | Fatfree Framework Base class |
### boo()

> Renders checkbox field  


**boo(** `string`  $variableName, `array`  $title, [`bool`  $defaultValue], [ $help] **):** `string` 

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string`  | **$variableName** |  |  |
| `array`  | **$title** |  |  |
| `bool`  | **$defaultValue** | `false` |  |
|  | **$help** | `empty string` |  |
### bulk()

**bulk(** `string`  $variableName, `array`  $fields **):** 

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string`  | **$variableName** |  |  |
| `array`  | **$fields** |  |  |
### danger()

**danger(** `string`  $mdLight, [`string`  $title] **):** `void` 

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string`  | **$mdLight** |  |  |
| `string`  | **$title** | `empty string` |  |
### danger0()

**danger0(** [`string`  $title] **):** `void` 

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string`  | **$title** | `empty string` |  |
### danger1()

**danger1(**  **):** `void` 

### def()

**def(** `string`  $variableName,  $result, [`string`  $mathExpression], [`string`  $help], [`string`  $gumpValidation] **):** `string` 

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string`  | **$variableName** |  |  |
| @mixed  | **$result** |  |  |
| `string`  | **$mathExpression** | "%%" |  |
| `string`  | **$help** | `empty string` |  |
| `string`  | **$gumpValidation** | `empty string` |  |
### h1()

> Renders first order header  


**h1(** `string`  $headingTitle, [`string`  $subTitle] **):** `string` 

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string`  | **$headingTitle** |  |  |
| `string`  | **$subTitle** | `empty string` |  |
### h2()

> Renders second order header  


**h2(** `string`  $headingTitle, [`string`  $subTitle] **):** `string` 

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string`  | **$headingTitle** |  |  |
| `string`  | **$subTitle** | `empty string` |  |
### h3()

> Renders third order header  


**h3(** `string`  $headingTitle, [`string`  $subTitle] **):** `string` 

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string`  | **$headingTitle** |  |  |
| `string`  | **$subTitle** | `empty string` |  |
### h4()

> Renders 4th order header  


**h4(** `string`  $headingTitle, [`string`  $subTitle] **):** `string` 

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string`  | **$headingTitle** |  |  |
| `string`  | **$subTitle** | `empty string` |  |
### hr()

> Renders horizontal line  


**hr(**  **):** `void` 

### html()

> Renders html  


**html(** `string`  $html **):** `void` 

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string`  | **$html** |  |  |
### img()

> Renders image  


**img(** `string`  $URLorBase64, [`string`  $caption] **):** `void` 

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string`  | **$URLorBase64** |  |  |
| `string`  | **$caption** | `empty string` |  |
### info()

**info(** `string`  $mdLight, [`string`  $title] **):** `void` 

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string`  | **$mdLight** |  |  |
| `string`  | **$title** | `empty string` |  |
### info0()

**info0(** [`string`  $title] **):** `void` 

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string`  | **$title** | `empty string` |  |
### info1()

**info1(**  **):** `void` 

### input()

> Renders input field  


**input(** `string`  $variableName, `array`  $title, [ $defaultValue], [`string`  $unit], [`string`  $help], [`string`  $gumpValidation] **):** `string` 

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string`  | **$variableName** |  |  |
| `array`  | **$title** |  |  |
| @mixed  | **$defaultValue** | `empty string` |  |
| `string`  | **$unit** | `empty string` |  |
| `string`  | **$help** | `empty string` |  |
| `string`  | **$gumpValidation** | `empty string` |  |
### instance()

**DEPRECATED** This static method is used by a few template // TODO remove them

**instance(**  **):** 

### jsx()

**jsx(** `string`  $id, [`string`  $js], [`int`  $height] **):** `void` 

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string`  | **$id** |  |  |
| `string`  | **$js** | "console.log("jsx");" |  |
| `int`  | **$height** | 200 |  |
### jsxDriver()

**jsxDriver(**  **):** `void` 

### label()

**label(**  $value, [`string`  $text] **):** `string` 

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| @mixed  | **$value** |  |  |
| `string`  | **$text** | `empty string` |  |
### lst()

> Renders selection list  


**lst(** `string`  $variableName, `array`  $source, `array`  $title, [ $defaultValue], [`string`  $help] **):** `string` 

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string`  | **$variableName** |  |  |
| `array`  | **$source** |  |  |
| `array`  | **$title** |  |  |
| @mixed  | **$defaultValue** | `empty string` |  |
| `string`  | **$help** | `empty string` |  |
### math()

> Renders math expression  


**math(** `string`  $mathExpression, [`string`  $help] **):** `void` 

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string`  | **$mathExpression** |  |  |
| `string`  | **$help** | `empty string` |  |
### md()

> Renders markdown  


**md(** `string`  $mdLight **):** `string` 

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string`  | **$mdLight** |  |  |
### note()

> Renders note  


**note(** `string`  $mdStrict **):** `void` 

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string`  | **$mdStrict** |  |  |
### numeric()

> Renders numeric input field  


**numeric(** `string`  $variableName, `array`  $title, `float`  $defaultValue, [`string`  $unit], [`string`  $help], [`string`  $additionalGumpValidation] **):** `string` 

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string`  | **$variableName** |  |  |
| `array`  | **$title** |  |  |
| `float`  | **$defaultValue** |  |  |
| `string`  | **$unit** | `empty string` |  |
| `string`  | **$help** | `empty string` |  |
| `string`  | **$additionalGumpValidation** | `empty string` |  |
### pre()

> Renders pre text  


**pre(** `string`  $plainText **):** `void` 

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string`  | **$plainText** |  |  |
### region0()

**region0(** `string`  $name, [`string`  $title] **):** `void` 

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string`  | **$name** |  |  |
| `string`  | **$title** | "Számítások" |  |
### region1()

**region1(**  **):** 

### success()

**success(** `string`  $mdLight, [`string`  $title] **):** `void` 

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string`  | **$mdLight** |  |  |
| `string`  | **$title** | `empty string` |  |
### success0()

**success0(** [`string`  $title] **):** `void` 

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string`  | **$title** | `empty string` |  |
### success1()

**success1(**  **):** `void` 

### svg()

**svg(** *resist\SVG\SVG* $svgObject, [`bool`  $forceJpg], [`string`  $caption] **):** `void` 

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| *resist\SVG\SVG* | **$svgObject** |  |  |
| `bool`  | **$forceJpg** | `false` |  |
| `string`  | **$caption** | `empty string` |  |
### tbl()

> Table rendering  


**tbl(** `array`  $scheme, `array`  $rows, [`string`  $name], [ $caption] **):** `void` 

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `array`  | **$scheme** |  | 1 dim array of header |
| `array`  | **$rows** |  | Nested array of rows and cols |
| `string`  | **$name** | "tbl" | Class name of tbody tag for scripting |
|  | **$caption** | `empty string` |  |
### toast()

**toast(** `string`  $textMdStrict, [`string`  $type], [`string`  $titleMdStrict] **):** 

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string`  | **$textMdStrict** |  |  |
| `string`  | **$type** | "info" |  |
| `string`  | **$titleMdStrict** | `empty string` |  |
### toc()

**toc(**  **):** `void` 

### txt()

> Renders plain text  


**txt(** `string`  $mdStrict, [`string`  $help] **):** `string` 

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string`  | **$mdStrict** |  |  |
| `string`  | **$help** | `empty string` |  |
### wrapper0()

**wrapper0(** [`string`  $stringTitle] **):** 

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string`  | **$stringTitle** | `empty string` |  |
### wrapper1()

**wrapper1(** [`string`  $middleTextMd] **):** 

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string`  | **$middleTextMd** | `empty string` |  |
### wrapper2()

**wrapper2(** [`string`  $help] **):** 

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string`  | **$help** | `empty string` |  |
### write()

**write(** `string`  $imageFile, `array`  $textArray, [`string`  $caption] **):** `void` 

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string`  | **$imageFile** |  |  |
| `array`  | **$textArray** |  |  |
| `string`  | **$caption** | `empty string` |  |


---

# Ec

> Eurocode globals, helpers and predefined GUI elements for ECC framework  
(c) Bence VÁNKOS  
https:// structure.hu  


Auto generated public API documentation of class ***Ec\Ec*** at 2020.08.05.

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


**__construct(** *Base* $f3, *Ecc\Blc* $blc, *DB\SQL* $db **):** 

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| *Base* | **$f3** |  |  |
| *Ecc\Blc* | **$blc** |  |  |
| *DB\SQL* | **$db** |  |  |
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

**wrapRebarCount(** `string`  $variableNameCount, `string`  $variableNameRebar, `string`  $titleString, `float`  $defaultValueCount, [`float`  $defaultValueRebar], [`string`  $help] **):** `void` 

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string`  | **$variableNameCount** |  |  |
| `string`  | **$variableNameRebar** |  |  |
| `string`  | **$titleString** |  |  |
| `float`  | **$defaultValueCount** |  |  |
| `float`  | **$defaultValueRebar** | 16 |  |
| `string`  | **$help** | `empty string` |  |
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


---

# SVG



Auto generated public API documentation of class ***resist\SVG\SVG*** at 2020.08.05.

## Public methods 

### __construct()

> SVG constructor  


**__construct(** `int`  $width, `int`  $height **):** 

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `int`  | **$width** |  | SVG width |
| `int`  | **$height** |  | SVG height |
### addBorder()

> Add border to the generated figure  


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

> Add horizontal dimension line (use color, size, ratio)  


**addDimH(** `float`  $xLeft, `float`  $length, `float`  $y,  $text, [`float`  $xLeft0] **):** `void` 

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `float`  | **$xLeft** |  | Coordinate X of base point (left) - modified by $ratio[0] |
| `float`  | **$length** |  | Length of dimension line - modified by $ratio[0] |
| `float`  | **$y** |  | Coordinate Y of dimension line - NOT modified by ratio |
| @string,int,float  | **$text** |  | Text above dim. line |
| `float`  | **$xLeft0** | 0 | Added to $xLeft - NOT modified by $ratio[0] |
### addDimV()

> Add vertical dimension line (use color, size, ratio)  


**addDimV(** `float`  $yTop, `float`  $length, `float`  $x,  $text, [`float`  $yTop0] **):** `void` 

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `float`  | **$yTop** |  | Coordinate Y of base point (top) - modified by $ratio[1] |
| `float`  | **$length** |  | Length of dimension line - modified by $ratio[1] |
| `float`  | **$x** |  | Coordinate X of dimension line - NOT modified by ratio |
| @string,float,int  | **$text** |  | Text next to the dim. line |
| `float`  | **$yTop0** | 0 | Added to $yTop - NOT modified by $ratio[1] |
### addLine()

> Add simple line (use color, line, fill)  


**addLine(** `float`  $x1, `float`  $y1, `float`  $x2, `float`  $y2 **):** `void` 

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `float`  | **$x1** |  | Coordinate X of start point - modified by $ratio[0] |
| `float`  | **$y1** |  | Coordinate Y of start point - modified by $ratio[1] |
| `float`  | **$x2** |  | Coordinate X of end point - modified by $ratio[0] |
| `float`  | **$y2** |  | Coordinate Y of end point - modified by $ratio[1] |
### addLineRatio()

> Add simple line (use color, line, fill, RATIO)  


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

> Add path (use color, line, fill)  


**addPath(** `string`  $path **):** `void` 

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string`  | **$path** |  | Alphanumeric string of SVG path object |
### addPolygon()

> Add polygon (use color, line, fill)  


**addPolygon(**  $points **):** `void` 

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| @string  | **$points** |  | Alphanumeric list of points |
### addRectangle()

> Add rectangle (use color, line, fill, ratio)  


**addRectangle(** `float`  $x, `float`  $y, `float`  $w, `float`  $h, [`float`  $x0], [`float`  $y0] **):** `void` 

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `float`  | **$x** |  | Coordinate X of base point (top left) - modified by $ratio[0] |
| `float`  | **$y** |  | Coordinate Y of base point (top left) - modified by $ratio[1] |
| `float`  | **$w** |  | Width/X direction of rectangle - modified by $ratio[0] |
| `float`  | **$h** |  | Height/Y direction of rectangle - modified by $ratio[1] |
| `float`  | **$x0** | 0 | Added to coordinate X - not modified by $ratio[0] |
| `float`  | **$y0** | 0 | Added to coordinate Y - not modified by $ratio[1] |
### addSymbol()

> Add simple icon font symbol by mapping (use color, size)  


**addSymbol(** `float`  $x, `float`  $y, `string`  $symbolName **):** `void` 

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `float`  | **$x** |  | Coordinate X of base point |
| `float`  | **$y** |  | Coordinate Y of base point |
| `string`  | **$symbolName** |  | Key in mapping array |
### addText()

> Add simple text (use color, size)  


**addText(** `float`  $x, `float`  $y, `string`  $text, [`bool`  $rotate], [`string`  $style] **):** `void` 

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `float`  | **$x** |  | Coordinate X of base point |
| `float`  | **$y** |  | Coordinate Y of base point |
| `string`  | **$text** |  |  |
| `bool`  | **$rotate** | `false` | Rotate text by 270 deg if true |
| `string`  | **$style** | `empty string` | String of style tag |
### addTrustedRaw()

> Add raw SVG content  
Not validated against XSS - use this method with getImgJpg() or getImgSvg()  


**addTrustedRaw(** `string`  $raw **):** `void` 

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string`  | **$raw** |  | SVG content |
### getImgJpg()

> Return img tag with base64 encoded jpg image  


**getImgJpg(** [`string`  $title] **):** `string` 

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string`  | **$title** | `empty string` | Title attribute of img tag |
Returns: `string`  

### getImgSvg()

> Return img tag with base64 encoded SVG content  


**getImgSvg(** [`string`  $title] **):** `string` 

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string`  | **$title** | `empty string` |  |
Returns: `string`  

### getRatio()

> Return set ratio  


**getRatio(**  **):** `array` 

### getRatioRaw()

**getRatioRaw(** `int`  $canvasWidth, `int`  $canvasHeight, `float`  $x, `float`  $y **):** 

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `int`  | **$canvasWidth** |  |  |
| `int`  | **$canvasHeight** |  |  |
| `float`  | **$x** |  |  |
| `float`  | **$y** |  |  |
### getSvg()

> Add end tag and return SVG string  


**getSvg(**  **):** `string` 

Returns: `string`  

### makeRatio()

> Generae and set ratio  


**makeRatio(** `int`  $canvasWidth, `int`  $canvasHeight, `float`  $x, `float`  $y **):** `void` 

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `int`  | **$canvasWidth** |  | Width of canvas |
| `int`  | **$canvasHeight** |  | Height of canvas |
| `float`  | **$x** |  | Set ratio[0] according to this |
| `float`  | **$y** |  | Set ratio[1] according to this |
### reset()

> Reset properties (line, color, size, fill and ratio) to default  


**reset(**  **):** `void` 

### setColor()

> Set color  


**setColor(** `string`  $color **):** `void` 

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string`  | **$color** |  |  |
### setFill()

> Set fill color  


**setFill(** `string`  $color **):** `void` 

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string`  | **$color** |  |  |
### setLine()

> Set line-width  


**setLine(** `int`  $line **):** `void` 

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `int`  | **$line** |  | line-width |
### setRatio()

> Set ratio  


**setRatio(** `array`  $ratio **):** `void` 

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `array`  | **$ratio** |  | Array of X and Y ratio |
### setSize()

> Set size (of text)  


**setSize(** `int`  $size **):** `void` 

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `int`  | **$size** |  |  |


---

