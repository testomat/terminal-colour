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

interface Formatter
{
    /**
     * Returns 0, 16, 255, 65535 for the supported terminal colors.
     */
    public function getColorLevel(): int;

    /**
     * Sets the decorated flag.
     */
    public function setDecorated(bool $decorated): void;

    /**
     * Gets the decorated flag.
     *
     * @return bool true if the output will decorate messages, false otherwise
     */
    public function isDecorated(): bool;

    /**
     * Sets a new style.
     */
    public function setStyle(string $name, Style $style): void;

    /**
     * Checks if output formatter has style with specified name.
     */
    public function hasStyle(string $name): bool;

    /**
     * Gets style options from style with specified name.
     *
     * @throws \Testomat\TerminalColour\Exception\InvalidArgumentException When style isn't defined
     *
     * @return \Testomat\TerminalColour\Contract\Style
     */
    public function getStyle(string $name): Style;

    /**
     * Formats a message according to the given styles.
     */
    public function format(string $message): string;
}
