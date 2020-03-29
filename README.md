# Terminal color

* The easiest  style your text in the command line / terminal
* Change text color to red, green, yellow ...
* Change background color to red, green, yellow ...
* Change text style to bold, dim, underlined, blink ...
* Has support for [0 , 16, 256 and true color](asset/colors.png) ...

## Installation

Run

```
$ composer require testomat/terminal-colour
```

> note : By default, the Windows command console doesn’t support output coloring.
> This library disables output coloring for Windows systems.
> But if your commands or scripts invoke other scripts which emit color sequences, they will be wrongly displayed as raw escape characters.
> Install the [Cmder](https://cmder.net/), [ConEmu](https://conemu.github.io/), [ANSICON](https://github.com/adoxa/ansicon/releases) or [Mintty](https://mintty.github.io/) (used by default in GitBash and Cygwin) to add coloring support to your Windows command console.

## Usage

### Using Color Styles

```php
<?php

declare(strict_types=1);

use Testomat\TerminalColour\Formatter;

$formatter = new Formatter();

// green text
$formatter->format('<info>foo</info>');

// yellow text
$formatter->format('<comment>foo</comment>');

// black text on a cyan background
$formatter->format('<question>foo</question>');

// white text on a red background
$formatter->format('<error>foo</error>');
```

The closing tag can be replaced by `</>`, which revokes all formatting options established by the last opened tag.

It’s possible to define your own styles using the Formatter class:
```php
<?php

declare(strict_types=1);

use Testomat\TerminalColour\Formatter;
use Testomat\TerminalColour\Style;

$style = new Style('red', 'yellow', ['bold', 'blink']);

$formatter = new Formatter(false, ['fire' => $style]);

$formatter->format('<fire>foo</fire>');
```

Available foreground and background colors are `black, red, green, yellow, blue, magenta, cyan, white, default, dark_grey, light_grey, light_red, light_green, light_yellow, light_blue, light_magenta, light_cyan, light_white`.

And available options are: `none, bold, dark, italic, underscore, blink, blink_fast, crossed_out, double_underline, curly_underline, overlinem, reverse` (enables the "reverse video" mode where the background and foreground colors are swapped) and `conceal` (sets the foreground color to transparent, making the typed text invisible—although it can be selected and copied; this option is commonly used when asking the user to type sensitive information).

You want to use `256 colors` or `true colors` than you must use the `StyleCode` class.

```php
<?php

declare(strict_types=1);

use Testomat\TerminalColour\Formatter;
use Testomat\TerminalColour\StyleCode;

$style256 = new StyleCode(34); // will be transformed to '38;5;34' blue
$trueStyle = new StyleCode("38;2;1;1;1"); // same goes for 1;1;1 as value; will be transformed to '38;2;1;1;1'

$formatter = new Formatter(false, ['color256Blue' => $style256,  'trueColor' => $trueStyle]);

$formatter->format('<color256Blue>foo</color256Blue>');
```

You can also set these colors and options directly inside the tag name:

```php
// green text
$formatter->format('<fg=green>foo</>');

// black text on a cyan background
$formatter->format('<fg=black;bg=cyan>foo</>');

// bold text on a yellow background
$formatter->format('<bg=yellow;options=bold>foo</>');

// bold text with underscore
$formatter->format('<options=bold,underscore>foo</>');
```

> Note: if you need to render a tag literally, escape it with a backslash: \<info> or use the escape() method to escape all the tags included in the given string.

You have the possibility with `formatAndWrap(string $message, int $width)` function to wrap you text to a specific width, 0 means no wrapping.

> Note: you want to check the color support for you terminal check the example folder out.

### Displaying Clickable Links

Commands can use the special `<href>` tag to display links like the `<a>` elements of web pages:

```php
$formatter->format('<href=https://narrowspark.com>Narrowspark Homepage</>');
```

If your terminal belongs to the [list of terminal emulators that support links][1] you can click on the "Narrowspark Homepage" text to open its URL in your default browser. Otherwise, you’ll see "Narrowspark Homepage" as regular text and the URL will be lost.

## Links
[Colors 3/4 bit](https://en.wikipedia.org/wiki/ANSI_escape_code#3/4_bit)

[XVilka/TrueColour.md](https://gist.github.com/XVilka/8346728)

[Hyperlinks (a.k.a. HTML-like anchors) in terminal emulators][1]

## Versioning

This library follows semantic versioning, and additions to the code ruleset are performed in major releases.

## Changelog

Please have a look at [`CHANGELOG.md`](CHANGELOG.md).

## Contributing

Please have a look at [`CONTRIBUTING.md`](.github/CONTRIBUTING.md).

## Code of Conduct

Please have a look at [`CODE_OF_CONDUCT.md`](.github/CODE_OF_CONDUCT.md).

## License

This package is licensed using the MIT License.

Please have a look at [`LICENSE.md`](LICENSE.md).

[1]: https://gist.github.com/egmontkob/eb114294efbcd5adb1944c9f3cb5feda
