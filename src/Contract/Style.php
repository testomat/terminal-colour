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

interface Style
{
    /**
     * Sets style foreground color.
     */
    public function setForeground(?string $color = null): void;

    /**
     * Sets style background color.
     */
    public function setBackground(?string $color = null): void;

    /**
     * Sets some specific style option.
     */
    public function setOption(string $option): void;

    /**
     * Unsets some specific style option.
     */
    public function unsetOption(string $option): void;

    /**
     * Sets multiple style options at once.
     */
    public function setEffects(array $options): void;

    /**
     * Applies the style to a given text.
     */
    public function apply(string $text): string;
}
