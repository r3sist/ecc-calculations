<?php declare(strict_types=1);
/**
 * (c) Bence VÁNKOS | https:// structure.hu
 * Statika framework: Eurocode based calculations for structural design
 */

namespace Statika;

use resist\SVG\SVG;

/**
 * General "blocks" for Statika framework - renders and handles UI blocks and their data.
 * @todo document interface in README
 */
interface BlocksInterface
{
    /**
     * Renders general input field
     * @param string $variableName Stores data in F3-Hive with a name of _ prefix and this ID
     * @param string[] $title Header of block: [(string) ASCIIMath expression or empty string, (string) title text]
     * @param string $defaultValue Input default value
     * @param string $unit Input group extension, UI only.
     * @param string $help Markdown help text. Can contain $$ math.
     * @param string $gumpValidation GUMP validator string: https://github.com/Wixel/GUMP#star-available-validators
     */
    public function input(string $variableName, array $title, $defaultValue = null, string $unit = '', string $descriptionValue = '', string $gumpValidation = ''): void;

    /**
     * Renders numeric input field. (General HTML input tag with numeric validation). Accepts and converts math expression strings in "=2+3" format
     * @param string $variableName Stores data in F3-Hive with a name of _ prefix and this ID
     * @param string[] $title Header of block: [(string) ASCIIMath expression or empty string, (string) title text]
     * @param float $defaultValue Input default value
     * @param string $unit Input group extension, UI only.
     * @param string $description Allowed: Basic markdown, math expression between $..$
     * @param string $additionalGumpValidation GUMP validator string ("numeric" is set always): https://github.com/Wixel/GUMP#star-available-validators
     */
    public function numeric(string $variableName, array $title, float $defaultValue, string $unit = '', string $description = '', string $additionalGumpValidation = ''): void;

    /**
     * Renders checkbox field
     * @param string $variableName Stores data in F3-Hive with a name of _ prefix and this ID
     * @param string[] $title Header of block: [(string) ASCIIMath expression or empty string, (string) title text]
     * @param bool $defaultValue Input default value
     * @param string $description Markdown help text. Can contain $$ math.
     */
    public function boo(string $variableName, array $title, bool $defaultValue = false, $description = ''): void;

    /**
     * Renders selection list
     * @param string $variableName Stores data in F3-Hive with a name of _ prefix and this ID
     * @param array $source List items. Items: (int|float|string) name => (int|float|string) value
     * @param string[] $title Header of block: [(string) ASCIIMath expression or empty string, (string) title text]
     * @param float|int|string $defaultValue Input default value
     * @param string $description Markdown help text. Can contain $$ math.
     */
    public function lst(string $variableName, array $source, array $title, $defaultValue = '', string $description = ''): void;

    /**
     * Renders definition block, stores given value to named variable and renders math block with custom text.
     * @param string $variableName Stores data in F3-Hive with a name of _ prefix and this ID
     * @param mixed $result Stores this as data
     * @param string $mathExpression ASCIIMath expression without $ delimiters. Use "%%" to substitute result.
     * @param string $help Markdown help text
     * @param string $gumpValidation GUMP validator string: https://github.com/Wixel/GUMP#star-available-validators
     */
    public function def(string $variableName, $result, string $mathExpression = '%%', string $description = '', string $gumpValidation = ''): void;

    /**
     * Renders multi-input table as dynamic block.
     * @param string $variableName Stores data in F3-Hive with a name of _ prefix and this ID
     * @param array[] $fields Source data of table. Sub arrays keys: "name" => string, "title" => string, "type" => string: "value" "input", "key" => string: for value types, "sum" => bool
     */
    public function bulk(string $variableName, array $fields, $defaultRow = [], bool $enableController = true): void;

    /**
     * Renders plain text block.
     * @param string $mdStrict Allowed: Basic markdown, math expression between $..$
     * @param string $description Allowed: Basic markdown, math expression between $..$
     */
    public function txt(string $mdStrict, string $description = ''): void;

    /**
     * Renders horizontal line (hr HTML tag) block.
     */
    public function hr(): void;

    /**
     * Renders first order header block.
     * @param string $headingTitle Text of header
     * @param string $subTitle Markdown text of sub-header
     */
    public function h1(string $headingTitle, string $subTitle = ''): void;

    /**
     * Renders second order header block.
     * @param string $headingTitle Text of header
     * @param string $subTitle Markdown text of sub-header
     */
    public function h2(string $headingTitle, string $subTitle = ''): void;

    /**
     * Renders third order header block.
     * @param string $headingTitle Text of header
     * @param string $subTitle Markdown text of sub-header
     */
    public function h3(string $headingTitle, string $subTitle = ''): void;

    /**
     * Renders fourth order header block.
     * @param string $headingTitle Text of header
     * @param string $subTitle Markdown text of sub-header
     */
    public function h4(string $headingTitle, string $subTitle = ''): void;

    /**
     * Renders math expression block.
     * @param string $mathExpression ASCIIMath expression without $ delimiters. Use "%%%" to substitute horizontal separator.
     * @param string $help Markdown help text
     */
    public function math(string $mathExpression, string $help = ''): void;

    /**
     * Renders html block.
     * @param string $html HTML content
     */
    public function html(string $html): void;

    /**
     * Builds markdown block.
     * @param string $markdownWithoutHtml Multi-line markdown text
     */
    public function md(string $markdownWithoutHtml): void;

    /**
     * Renders calculation note block.
     * @param string $mdStrict Markdown text
     */
    public function note(string $mdStrict): void;

    /**
     * Renders block with pre HTML tag
     * @param string $plainText
     */
    public function pre(string $plainText): void;

    /**
     * Renders image block.
     * @param string $URLorBase64 Valid HTTP URL or Base64 image content. Base64 is PNG only, without "data:image/png;base64,"
     * @param string $caption
     */
    public function img(string $URLorBase64, string $caption = ''): void;

    /**
     * Renders label block.
     * @param float|int|string $value Can be "yes" for green, "no" for red or numeric converted to %. If $value < 1 then label is green.
     * @param string $text Additional text in label
     */
    public function label($value, string $text = '', string $description = ''): void;

    /**
     * Renders collapsible region INITIAL block. Call region1() method for closing.
     * @param string $name Block ID
     * @param string $title Title of region
     */
    public function region0(string $name, string $title = 'Számítások'): void;

    /**
     * Closes region0() block.
     */
    public function region1(): void;

    /**
     * Renders success region INITIAL block. Call success1() method for closing.
     * @param string $title Title of region.
     */
    public function success0(string $title = ''): void;

    /**
     * Closes success0() block.
     */
    public function success1(): void;

    /**
     * Renders info region INITIAL block. Call info1() method for closing.
     * @param string $title Title of region.
     */
    public function info0(string $title = ''): void;

    /**
     * Closes info0() block.
     */
    public function info1(): void;

    /**
     * Renders danger region INITIAL block. Call danger1() method for closing.
     * @param string $title Title of region.
     */
    public function danger0(string $title = ''): void;

    /**
     * Closes danger0() block.
     */
    public function danger1(): void;

    /**
     * Renders success block.
     * @param string $mdLight Multi-line markdown text as content. Can contain $$ math expression.
     * @param string $title
     */
    public function success(string $mdLight, string $title = ''): void;

    /**
     * Renders info block.
     * @param string $mdLight Multi-line markdown text as content. Can contain $$ math expression.
     * @param string $title
     */
    public function info(string $mdLight, string $title = ''): void;

    /**
     * Renders danger block.
     * @param string $mdLight Multi-line markdown text as content. Can contain $$ math expression.
     * @param string $title
     */
    public function danger(string $mdLight, string $title = ''): void;

    /**
     * Renders SVG content block.
     * @param SVG $svgObject SVG object generated by resist\SVG\SVG
     * @param bool $forceJpg Renders SVG as JPG if true
     * @param string $caption Help text of block
     */
    public function svg(SVG $svgObject, bool $forceJpg = false, string $caption = ''): void;

    /**
     * Injects JSXGraph library to calculation.
     */
    public function jsxDriver(): void;

    /**
     * Builds html block with JSXGraph content. Load buildJsxDriverBlock() first.
     * @param string $id Block ID
     * @param string $js JSXGraph JS content
     * @param int $height Height of rendered block
     */
    public function jsx(string $id, string $js = 'console.log("jsx");', int $height = 200): void;

    /**
     * Renders table block from array.
     * @param array $scheme 1 dimensional array of header columns
     * @param array[] $rows Array of rows' content array
     * @param string $name Block ID - Class name of tbody tag for scripting
     * @param string $caption Help text of table
     */
    public function tbl(array $scheme, array $rows, string $name = 'tbl', string $caption = ''): void;

    /**
     * Renders wrapped blocks. STARTER block.
     * @param string $stringTitle Title line of whole wrapped blocks
     */
    public function wrapper0(string $stringTitle = ''): void;

    /**
     * Renders wrapped blocks. SEPARATOR block.
     * @param string $middleTextMd Separator text between wrapped blocks
     */
    public function wrapper1(string $middleTextMd = ''): void;

    /**
     * Renders wrapped blocks. END block.
     * @param string $help Help text below wrapped blocks
     */
    public function wrapper2(string $help = ''): void;

    public function set(string $key, $value): void;

    public function get(string $key);
}
