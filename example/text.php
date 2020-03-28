<?php

declare(strict_types=1);

/**
 * Copyright (c) 2020 Daniel Bannert
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source code.
 *
 * @see https://github.com/testomat/terminal-colour
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Testomat\TerminalColour\Formatter;
use Testomat\TerminalColour\Style;
use Testomat\TerminalColour\Util;

$color = Util::getSupportedColor();
$isColorSupported = $color !== 0;

$styles = [];

$styles['fire'] = new Style('light_yellow', 'light_red', ['bold']);
$styles['lagoon'] = new Style('black', 'light_blue', ['underscore']);
$styles['blink'] = new Style('light_cyan', null, ['blink']);
$styles['alert'] = new Style('white', 'light_red', ['blink']);

$formatter = new Formatter($isColorSupported, $styles);

echo $formatter->format('<info>This is "info" text.</info>') . \PHP_EOL . \PHP_EOL;
echo $formatter->format('<comment>This is "comment" text.</comment>') . \PHP_EOL . \PHP_EOL;
echo $formatter->format('<question>This is "question" text.</question>') . \PHP_EOL . \PHP_EOL;
echo $formatter->format('<fire>This is "fire" text.</fire>') . \PHP_EOL . \PHP_EOL;
echo $formatter->format('<lagoon>This is "lagoon" text.</lagoon>') . \PHP_EOL . \PHP_EOL;
echo $formatter->format('<blink>This text is light cyan and blinking.</blink>') . \PHP_EOL . \PHP_EOL;
echo $formatter->format('<error>This is "error" text.</error>') . \PHP_EOL . \PHP_EOL;
echo $formatter->format('<alert>This is "alert" text.</alert>') . \PHP_EOL . \PHP_EOL;

// Start Hyperlink
echo \PHP_EOL . 'Hyperlink:' . \PHP_EOL . \PHP_EOL;

echo $formatter->format('<href=https://narrowspark.com>Narrowspark Homepage</>') . \PHP_EOL;
// End Hyperlink
