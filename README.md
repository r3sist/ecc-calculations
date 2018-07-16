# Ecc Calculations

**Native classes of Eurocode based calculations for structural design on [structure.hu](https://structure.hu)**

(c) 2018 Bence VÁNKOS

![email](https://structure.hu/img/emailB.png) | [structure.hu](https://structure.hu)

+ **Repository:** Open Source [bitbucket/resist/ecc-calculations](https://bitbucket.org/resist/ecc-calculations)
+ **Issue tracker:** [bitbucket/resist/ecc-calculations/issues](https://bitbucket.org/resist/ecc-calculations/issues)
+ **Changelog:** - [bitbucket/resist/ecc-calculations/commits](https://bitbucket.org/resist/ecc-calculations/commits)
+ **License:** CDDL-1.0 Copyright 2018 Bence VÁNKOS
+ Code quality: [![Codacy Badge](https://api.codacy.com/project/badge/Grade/c7eb0b4d706e4dafbd3cbe44564c2718)](https://www.codacy.com/app/resist/ecc-calculations?utm_source=resist@bitbucket.org&amp;utm_medium=referral&amp;utm_content=resist/ecc-calculations&amp;utm_campaign=Badge_Grade) [![CodeFactor](https://www.codefactor.io/repository/bitbucket/resist/ecc-calculations/badge)](https://www.codefactor.io/repository/bitbucket/resist/ecc-calculations) [![Scrutinizer Code Quality](https://scrutinizer-ci.com/b/resist/ecc-calculations/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/b/resist/ecc-calculations/?branch=master)

--- 

## Initialization / Boilerplate calculation class

```php

namespace Calculation;

// This class will extend Ecc framework
Class Boilerplate extends \Ecc
{
    // This function will be called by Ecc, uses Fatfree Framework's singleton
    public function calc($f3)
    {
        // Load Eurocode
        $ec = \Ec::instance();
        
        // Load Ecc framework
        $blc = \Blc::instance();

        // Business logic via Ecc
        $blc->txt('Hello World!');
        
        // Custom PHP code
        if (1 == 1) {
            $f3->var = 1;
        }
    }
}
```

## Ecc framework blocks and regions

### General notes

+ `f3` is reference of [Fatfree Framework](https://fatfreeframework.com)'s singleton object. *Hive* is f3's global storage. *f3* are methods avaliable everywhere, generated variables are stored is *Hive*.
+ *Ecc* is the framework that runs calculation classes. `Ec()` class for Eurocode specific methods and `Blc()` class for GUI extend the `Ec()` general class.
+ [AsciiMath](http://asciimath.org/) *math expressions* are compiled by [MathJax](https://www.mathjax.org), use with code tags anywhere (needs to be escaped in Markdown regions). e.g. \\`x=1\\`.
+ HTML tags are cleaned generally.
+ [Markdown](https://en.wikipedia.org/wiki/Markdown) is enabled generally.
+ `$help` parameter creates small muted text block generally. Markdown is enabled, renders only: `br, em, i, strong, b, a, code` tags.
+ Toggled GUI elements are not printed.
+ Regions always need two line of code using the same identifier name: starter and finsher blocks (marked by 0 and 1)

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

+ In `math` define expression without code marks \` \`
+ *%%%* adds vertical spacing.
+ This block does not defines variable.

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
$blc->region0($name, [$help]) // for start
    // some blocks
$blc->region1($name, [$help]) // for end
```

Notes:

+ `$name` for start and end blocks has to be the same
+ Hidden regions are not printed
+ By default `$title = 'Számítások'`

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

+ `$name` for start and end blocks has to be the same
+ *success* region is green, *danger* is red, *info* is blue

### *table* block

Generates table from associative array.

`$blc->table($array, [$mainKey, $help])`

Notes:

+ `$mainKey` is 1/1 (top left corner) cell's text
+ `$help` is table caption
+ `$array` is associative array. Outer array contains rows, inner arrays contain columns. Outer array keys are row titles (first column), inner arrays' keys are column titles (means these are repeated in every sub-array!). e.g.: 

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

## Installation

Via composer: `"resist/ecc-calculations": "dev-master"`