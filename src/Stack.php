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

namespace Testomat\TerminalColour;

use Testomat\TerminalColour\Contract\Style as StyleContract;
use Testomat\TerminalColour\Exception\InvalidArgumentException;

/**
 * @internal
 */
final class Stack
{
    /** @var array<int, \Testomat\TerminalColour\Contract\Style> */
    private $styles = [];

    /** @var \Testomat\TerminalColour\Contract\Style */
    private $emptyStyle;

    public function __construct()
    {
        $this->emptyStyle = new Style();

        $this->reset();
    }

    /**
     * Resets stack (ie. empty internal arrays).
     */
    public function reset(): void
    {
        $this->styles = [];
    }

    /**
     * Pushes a style in the stack.
     */
    public function push(StyleContract $style): void
    {
        $this->styles[] = $style;
    }

    /**
     * Pops a style from the stack.
     *
     * @throws \Testomat\TerminalColour\Exception\InvalidArgumentException When style tags incorrectly nested
     */
    public function pop(?StyleContract $style = null): StyleContract
    {
        if (\count($this->styles) === 0) {
            return $this->emptyStyle;
        }

        if ($style === null) {
            return array_pop($this->styles);
        }

        foreach (array_reverse($this->styles, true) as $index => $stackedStyle) {
            if ($style->apply('') === $stackedStyle->apply('')) {
                $this->styles = \array_slice($this->styles, 0, $index);

                return $stackedStyle;
            }
        }

        throw new InvalidArgumentException('Incorrectly nested style tag found.');
    }

    /**
     * Computes current style with stacks top codes.
     */
    public function getCurrent(): StyleContract
    {
        if (\count($this->styles) === 0) {
            return $this->emptyStyle;
        }

        return $this->styles[\count($this->styles) - 1];
    }
}
