# BlocksInterface

***Statika\BlocksInterface*** 

General "blocks" for Statika framework - renders and handles UI blocks and their data.  


## Public methods 

### boo()

Renders checkbox field  


```php
public function boo(string $variableName, array $title, bool $defaultValue = `false`, @string  $description = `empty string`): void
```

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string`  | **$variableName** |  | Stores data in F3-Hive with a name of _ prefix and this ID |
| `array`  | **$title** |  | Header of block: [(string) ASCIIMath expression or empty string, (string) title text] |
| `bool`  | **$defaultValue** | `false` | Input default value |
| @string  | **$description** | `empty string` | Markdown help text. Can contain $$ math. |
### bulk()

Renders multi-input table as dynamic block.  


```php
public function bulk(string $variableName, array $fields, $defaultRow = []): void
```

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string`  | **$variableName** |  | Stores data in F3-Hive with a name of _ prefix and this ID |
| `array`  | **$fields** |  | Source data of table. Sub arrays keys: "name" => string, "title" => string, "type" => string: "value" "input", "key" => string: for value types, "sum" => bool |
|  | **$defaultRow** | [] |  |
### danger()

Renders danger block.  


```php
public function danger(string $mdLight, string $title = `empty string`): void
```

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string`  | **$mdLight** |  | Multi-line markdown text as content. Can contain $$ math expression. |
| `string`  | **$title** | `empty string` |  |
### danger0()

Renders danger region INITIAL block. Call danger1() method for closing.  


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

Renders definition block, stores given value to named variable and renders math block with custom text.  


```php
public function def(string $variableName, @mixed  $result, string $mathExpression = "%%", string $description = `empty string`, string $gumpValidation = `empty string`): void
```

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string`  | **$variableName** |  | Stores data in F3-Hive with a name of _ prefix and this ID |
| @mixed  | **$result** |  | Stores this as data |
| `string`  | **$mathExpression** | "%%" | ASCIIMath expression without $ delimiters. Use "%%" to substitute result. |
| `string`  | **$description** | `empty string` |  |
| `string`  | **$gumpValidation** | `empty string` | GUMP validator string: https://github.com/Wixel/GUMP#star-available-validators |
### get()

```php
public function get(string $key)
```

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string`  | **$key** |  |  |
### h1()

Renders first order header block.  


```php
public function h1(string $headingTitle, string $subTitle = `empty string`): void
```

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string`  | **$headingTitle** |  | Text of header |
| `string`  | **$subTitle** | `empty string` | Markdown text of sub-header |
### h2()

Renders second order header block.  


```php
public function h2(string $headingTitle, string $subTitle = `empty string`): void
```

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string`  | **$headingTitle** |  | Text of header |
| `string`  | **$subTitle** | `empty string` | Markdown text of sub-header |
### h3()

Renders third order header block.  


```php
public function h3(string $headingTitle, string $subTitle = `empty string`): void
```

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string`  | **$headingTitle** |  | Text of header |
| `string`  | **$subTitle** | `empty string` | Markdown text of sub-header |
### h4()

Renders fourth order header block.  


```php
public function h4(string $headingTitle, string $subTitle = `empty string`): void
```

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string`  | **$headingTitle** |  | Text of header |
| `string`  | **$subTitle** | `empty string` | Markdown text of sub-header |
### hr()

Renders horizontal line (hr HTML tag) block.  


```php
public function hr(): void
```

### html()

Renders html block.  


```php
public function html(string $html): void
```

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string`  | **$html** |  | HTML content |
### img()

Renders image block.  


```php
public function img(string $URLorBase64, string $caption = `empty string`): void
```

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string`  | **$URLorBase64** |  | Valid HTTP URL or Base64 image content. Base64 is PNG only, without "data:image/png;base64," |
| `string`  | **$caption** | `empty string` |  |
### info()

Renders info block.  


```php
public function info(string $mdLight, string $title = `empty string`): void
```

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string`  | **$mdLight** |  | Multi-line markdown text as content. Can contain $$ math expression. |
| `string`  | **$title** | `empty string` |  |
### info0()

Renders info region INITIAL block. Call info1() method for closing.  


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

Renders general input field  


```php
public function input(string $variableName, array $title, @string  $defaultValue = "", string $unit = `empty string`, string $descriptionValue = `empty string`, string $gumpValidation = `empty string`): void
```

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string`  | **$variableName** |  | Stores data in F3-Hive with a name of _ prefix and this ID |
| `array`  | **$title** |  | Header of block: [(string) ASCIIMath expression or empty string, (string) title text] |
| @string  | **$defaultValue** | "" | Input default value |
| `string`  | **$unit** | `empty string` | Input group extension, UI only. |
| `string`  | **$descriptionValue** | `empty string` |  |
| `string`  | **$gumpValidation** | `empty string` | GUMP validator string: https://github.com/Wixel/GUMP#star-available-validators |
### jsx()

Builds html block with JSXGraph content. Load buildJsxDriverBlock() first.  


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

Renders label block.  


```php
public function label(@float|int|string  $value, string $text = `empty string`, string $description = `empty string`): void
```

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| @float,int,string  | **$value** |  | Can be "yes" for green, "no" for red or numeric converted to %. If $value < 1 then label is green. |
| `string`  | **$text** | `empty string` | Additional text in label |
| `string`  | **$description** | `empty string` |  |
### lst()

Renders selection list  


```php
public function lst(string $variableName, array $source, array $title, @float|int|string  $defaultValue = `empty string`, string $description = `empty string`): void
```

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string`  | **$variableName** |  | Stores data in F3-Hive with a name of _ prefix and this ID |
| `array`  | **$source** |  | List items. Items: (int|float|string) name => (int|float|string) value |
| `array`  | **$title** |  | Header of block: [(string) ASCIIMath expression or empty string, (string) title text] |
| @float,int,string  | **$defaultValue** | `empty string` | Input default value |
| `string`  | **$description** | `empty string` | Markdown help text. Can contain $$ math. |
### math()

Renders math expression block.  


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
public function md(string $markdownWithoutHtml): void
```

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string`  | **$markdownWithoutHtml** |  | Multi-line markdown text |
### note()

Renders calculation note block.  


```php
public function note(string $mdStrict): void
```

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string`  | **$mdStrict** |  | Markdown text |
### numeric()

Renders numeric input field. (General HTML input tag with numeric validation). Accepts and converts math expression strings in "=2+3" format  


```php
public function numeric(string $variableName, array $title, float $defaultValue, string $unit = `empty string`, string $description = `empty string`, string $additionalGumpValidation = `empty string`): void
```

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string`  | **$variableName** |  | Stores data in F3-Hive with a name of _ prefix and this ID |
| `array`  | **$title** |  | Header of block: [(string) ASCIIMath expression or empty string, (string) title text] |
| `float`  | **$defaultValue** |  | Input default value |
| `string`  | **$unit** | `empty string` | Input group extension, UI only. |
| `string`  | **$description** | `empty string` | Allowed: Basic markdown, math expression between $..$ |
| `string`  | **$additionalGumpValidation** | `empty string` | GUMP validator string ("numeric" is set always): https://github.com/Wixel/GUMP#star-available-validators |
### pre()

Renders block with pre HTML tag  


```php
public function pre(string $plainText): void
```

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string`  | **$plainText** |  |  |
### region0()

Renders collapsible region INITIAL block. Call region1() method for closing.  


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

### set()

```php
public function set(string $key, $value): void
```

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string`  | **$key** |  |  |
|  | **$value** |  |  |
### success()

Renders success block.  


```php
public function success(string $mdLight, string $title = `empty string`): void
```

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string`  | **$mdLight** |  | Multi-line markdown text as content. Can contain $$ math expression. |
| `string`  | **$title** | `empty string` |  |
### success0()

Renders success region INITIAL block. Call success1() method for closing.  


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

Renders SVG content block.  


```php
public function svg(resist\SVG\SVG $svgObject, bool $forceJpg = `false`, string $caption = `empty string`): void
```

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| *resist\SVG\SVG* | **$svgObject** |  | SVG object generated by resist\SVG\SVG |
| `bool`  | **$forceJpg** | `false` | Renders SVG as JPG if true |
| `string`  | **$caption** | `empty string` | Help text of block |
### tbl()

Renders table block from array.  


```php
public function tbl(array $scheme, array $rows, string $name = "tbl", string $caption = `empty string`): void
```

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `array`  | **$scheme** |  | 1 dimensional array of header columns |
| `array`  | **$rows** |  | Array of rows' content array |
| `string`  | **$name** | "tbl" | Block ID - Class name of tbody tag for scripting |
| `string`  | **$caption** | `empty string` | Help text of table |
### txt()

Renders plain text block.  


```php
public function txt(string $mdStrict, string $description = `empty string`): void
```

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string`  | **$mdStrict** |  | Allowed: Basic markdown, math expression between $..$ |
| `string`  | **$description** | `empty string` | Allowed: Basic markdown, math expression between $..$ |
### wrapper0()

Renders wrapped blocks. STARTER block.  


```php
public function wrapper0(string $stringTitle = `empty string`): void
```

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string`  | **$stringTitle** | `empty string` | Title line of whole wrapped blocks |
### wrapper1()

Renders wrapped blocks. SEPARATOR block.  


```php
public function wrapper1(string $middleTextMd = `empty string`): void
```

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string`  | **$middleTextMd** | `empty string` | Separator text between wrapped blocks |
### wrapper2()

Renders wrapped blocks. END block.  


```php
public function wrapper2(string $help = `empty string`): void
```

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string`  | **$help** | `empty string` | Help text below wrapped blocks |


---

# EurocodeInterface

***Statika\EurocodeInterface*** 

Extension of BlocksInterface of Statika framework - renders and handles Eurocode related UI blocks and their data.  


## Public methods 

### A()

```php
public function A(float $D, float $multiplicator = 1): float
```

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `float`  | **$D** |  |  |
| `float`  | **$multiplicator** | 1 |  |
Returns: `float` 

### __construct()

```php
public function __construct(Base $f3, Statika\Block\BlockService $blockService, Statika\Material\MaterialFactory $materialFactory, Statika\Bolt\BoltFactory $boltFactory, Profil\ProfilService $profilService)
```

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| *Base* | **$f3** |  |  |
| *Statika\Block\BlockService* | **$blockService** |  |  |
| *Statika\Material\MaterialFactory* | **$materialFactory** |  |  |
| *Statika\Bolt\BoltFactory* | **$boltFactory** |  |  |
| *Profil\ProfilService* | **$profilService** |  |  |
### boltListBlock()

Renders bolt selector block  


```php
public function boltListBlock(string $variableName = "bolt", string $default = "M16", array $title = ["","Csavar név"]): void
```

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string`  | **$variableName** | "bolt" |  |
| `string`  | **$default** | "M16" |  |
| `array`  | **$title** | ["","Csavar név"] |  |
### boltMaterialListBlock()

```php
public function boltMaterialListBlock(string $variableName = "mat", string $default = 8.8, array $title = ["","Csavar anyagminőség"]): void
```

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string`  | **$variableName** | "mat" |  |
| `string`  | **$default** | 8.8 |  |
| `array`  | **$title** | ["","Csavar anyagminőség"] |  |
### concreteMaterialListBlock()

```php
public function concreteMaterialListBlock(string $variableName = "mat", string $default = "C25/30", array $title = ["","Beton anyagminőség"]): void
```

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string`  | **$variableName** | "mat" |  |
| `string`  | **$default** | "C25/30" |  |
| `array`  | **$title** | ["","Beton anyagminőség"] |  |
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

### getBolt()

Returns Bolt DTO by bolt name  


```php
public function getBolt(string $boltName): Statika\Bolt\BoltDTO
```

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string`  | **$boltName** |  | Name of bolt like 'M12' or 'M16' |
### getMaterial()

Returns Material DTO by material name  


```php
public function getMaterial(string $materialName): Statika\Material\MaterialDTO
```

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string`  | **$materialName** |  | Name of material like 'S235', '8.8', 'C25/30', 'B500' |
### getSection()

Returns Section data transfer object by section name  


```php
public function getSection($sectionName): Profil\Section\SectionDTO
```

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
|  | **$sectionName** |  |  |
### materialListBlock()

```php
public function materialListBlock(string $variableName = "mat", string $default = "S235", array $title = ["","Anyagminőség"]): void
```

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string`  | **$variableName** | "mat" |  |
| `string`  | **$default** | "S235" |  |
| `array`  | **$title** | ["","Anyagminőség"] |  |
### rebarMaterialListBlock()

```php
public function rebarMaterialListBlock(string $variableName = "mat", string $default = "B500", array $title = ["","Betonacél anyagminőség"]): void
```

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string`  | **$variableName** | "mat" |  |
| `string`  | **$default** | "B500" |  |
| `array`  | **$title** | ["","Betonacél anyagminőség"] |  |
### sectionFamilyListBlock()

Renders steel section family selector block  


```php
public function sectionFamilyListBlock(string $variableName = "sectionFamily", array $title = ["","Szelvény család"], string $default = "HEA"): void
```

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string`  | **$variableName** | "sectionFamily" |  |
| `array`  | **$title** | ["","Szelvény család"] |  |
| `string`  | **$default** | "HEA" |  |
### sectionListBlock()

```php
public function sectionListBlock(string $familyName = "HEA", string $variableName = "sectionName", array $title = ["","Szelvény név"], string $default = "HEA200"): void
```

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string`  | **$familyName** | "HEA" |  |
| `string`  | **$variableName** | "sectionName" |  |
| `array`  | **$title** | ["","Szelvény név"] |  |
| `string`  | **$default** | "HEA200" |  |
### steelMaterialListBlock()

```php
public function steelMaterialListBlock(string $variableName = "mat", string $default = "S235", array $title = ["","Acél anyagminőség"]): void
```

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string`  | **$variableName** | "mat" |  |
| `string`  | **$default** | "S235" |  |
| `array`  | **$title** | ["","Acél anyagminőség"] |  |
### structuralSteelMaterialListBlock()

```php
public function structuralSteelMaterialListBlock(string $variableName = "mat", string $default = "S235", array $title = ["","Szerkezeti acél anyagminőség"]): void
```

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string`  | **$variableName** | "mat" |  |
| `string`  | **$default** | "S235" |  |
| `array`  | **$title** | ["","Szerkezeti acél anyagminőség"] |  |


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
Not validated against XSS - use this method with getImgTagWithEncodedPng() or getImgSvg()  


```php
public function addTrustedRaw(string $raw): void
```

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string`  | **$raw** |  | SVG content |
### getImgSvg()

Returns img tag with base64 encoded SVG content  


```php
public function getImgSvg(string $title = `empty string`): string
```

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string`  | **$title** | `empty string` |  |
Returns: `string` 

### getImgTagWithEncodedPng()

Returns img tag with base64 encoded jpg image  


```php
public function getImgTagWithEncodedPng(string $title = `empty string`): string
```

| Type | Parameter name | Default value | Description |
| --- | --- | --- | --- |
| `string`  | **$title** | `empty string` | Title attribute of img tag |
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

