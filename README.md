# Ecc

**Native classes of Eurocode based calculations for structural design on [structure.hu](https://structure.hu)**

![BV](https://structure.hu/img/bV.png)  
![email](https://structure.hu/img/emailB.png)

--- 

## General notes for development

+ ***Ecc*** (actually *Ecc()* class) is the PHP framework that runs, renders, updates etc. calculations (in fact the native calculation classes' *calc()* method).
+ *Ecc* (and whole website) uses [Fatfree Framework](https://fatfreeframework.com) as engine, it's autoloaded and Fatfree singleton object is available everywhere globally as **`$f3`**. *Hive* is *f3*'s global storage.
+ ***Blc()*** class is part of the *Ecc* framework and it's supposed to render GUI elements and handle calculation related variables like inputs, definitions and other blocks. Calculation methods are built up from these blocks, but may contain vanilla PHP code too.
+ ***Ec()*** class contains Eurocode specific methods, datas or predefined GUI assemblies. *Ec()* autoloaded as well.
+ [AsciiMath](http://asciimath.org/) **math expressions** are compiled by [MathJax](https://www.mathjax.org) on client side, write them between with `$` code marks anywhere.
+ HTML tags are cleaned generally, [Markdown](https://en.wikipedia.org/wiki/Markdown) is enabled generally.
+ [GUMP](https://github.com/Wixel/GUMP) and [V3](https://bitbucket.org/resist/v3/) validation libraries are available.
+ Reserved variable final names: `_project*`, `_stamp*`, `print` - e.g. `$blc->def('project', 1, '%%')` creates `f3->_project` variable in *Hive*, which is reserved for saved data name.

---

## Documentation

To be documented.

# License

CDDL-1.0 Copyright
