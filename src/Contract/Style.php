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
     *
     * @param null|int|string $color
     */
    public function setForeground($color = null): void;

    /**
     * Sets style background color.
     *
     * @param null|int|string $color
     */
    public function setBackground($color = null): void;

    /**
     * Sets some specific style effect.
     *
     * @param string|array<string, int|string> $effect
     */
    public function setEffect($effect): void;

    /**
     * Unsets some specific style effect.
     *
     * @param string|array<string, int|string> $effect
     */
    public function unsetEffect($effect): void;

    /**
     * Sets multiple style effects at once.
     *
     * @param array<int, string|array<string, int|string>> $effects
     */
    public function setEffects(array $effects): void;

    /**
     * Sets the supported color level of the terminal.
     */
    public function setColorLevel(int $color): void;

    /**
     * Applies the style to a given text.
     */
    public function apply(string $text): string;
}
