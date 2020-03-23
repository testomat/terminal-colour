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

namespace Testomat\TerminalColour\Contract;

interface Format
{
    public const NONE = 0;
    public const BOLD = 1;
    public const DARK = 2;
    public const ITALIC = 3;
    public const UNDERLINE = 4;
    public const BLINK = 5;
    public const BLINK_FAST = 6;

    /**
     * Limited support.
     */
    public const REVERSE = 7;
    public const CONCEALED = 8;
    public const CROSSED_OUT = 9;

    public const DOUBLE_UNDERLINE = 21;
}
