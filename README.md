# Ecc Calculations

**Native classes of Eurocode based calculations for structural design on [structure.hu](https://structure.hu)**

(c) 2018 Bence VÁNKOS  
[email](https://structure.hu/img/emailB.png) | https://structure.hu

[![Codacy Badge](https://api.codacy.com/project/badge/Grade/c7eb0b4d706e4dafbd3cbe44564c2718)](https://www.codacy.com/app/resist/ecc-calculations?utm_source=resist@bitbucket.org&amp;utm_medium=referral&amp;utm_content=resist/ecc-calculations&amp;utm_campaign=Badge_Grade)
[![CodeFactor](https://www.codefactor.io/repository/bitbucket/resist/ecc%20calculations/badge)](https://www.codefactor.io/repository/bitbucket/resist/ecc%20calculations)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/b/resist/ecc-calculations/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/b/resist/ecc-calculations/?branch=master)

+ **Repository:** Open Source https://bitbucket.org/resist/ecc-calculations
+ **Issue tracker:** https://bitbucket.org/resist/ecc-calculations/issues
+ **Changelog:** - https://bitbucket.org/resist/ecc-calculations/commits
+ **License:** CDDL-1.0 Copyright 2018 Bence VÁNKOS [resist]

## Boilerplate calculation class

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

## Ecc framework blocks

### General notes

+ [AsciiMath](http://asciimath.org/) *math expressions* are compiled by [MathJax](https://www.mathjax.org), use with code tags anywhere (needs to be escaped in Markdown regions), e.g. *\`C_e\*C_t\`*
+ HTML tags are cleaned generally
+ [Markdown](https://en.wikipedia.org/wiki/Markdown) is enabled generally
+ `help` parameter creates small muted text block generally. Markdown is enabled, renders only: `br, em, i, strong, b, a, code` tags

### input block

General input field that defines variable in f3 Hive.

`$blc->input(variableName, title, [defaultValue, unit, help])`

Notes:

+ `variableName` creates variable in f3 Hive with underscore accessible globally: eg. `$f3->_variableName` or `$f3->get('_variableName')`
+ `variableName` is added to title by default, if `title` does not conatin additional *math expression*

### math block

Simple math text for mathematical expressions, compiled by MathJax.

`$blc->math(math, [help]);`

Notes: 

+ In `math` define expression without code mark `  
+ `%%%` adds vertical spacing
+ This block does not defines variable

### h1, h2, h3 blocks

Headings with lead paragraphs as help

`$blc->h1(head, [help]);`

`$blc->h2(head, [help]);`

`$blc->h3(head, [help]);`

### hr block

Simple separator line

`$blc->hr();`

## Installation

Via composer: `"resist/ecc-calculations": "dev-master"`