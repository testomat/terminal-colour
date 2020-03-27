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

use Testomat\TerminalColour\Contract\Color256Aware as Color256AwareContract;
use Testomat\TerminalColour\Contract\TrueColorAware as TrueColorAwareContract;
use Testomat\TerminalColour\Exception\InvalidArgumentException;

/**
 * @noRector \Rector\SOLID\Rector\ClassMethod\ChangeReadOnlyVariableWithDefaultValueToConstantRector
 */
final class StyleCode extends AbstractStyle implements Color256AwareContract, TrueColorAwareContract
{
    public function __construct($foreground = null, $background = null, array $effects = [])
    {
        if ($foreground !== null) {
            $this->setForeground($foreground);
        }

        if ($background !== null) {
            $this->setBackground($background);
        }

        if (\count($effects) !== 0) {
            $this->setEffects($effects);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setForeground($color = null): void
    {
        if (! \is_int($color) && ! \is_string($color) && $color !== null) {
            throw new InvalidArgumentException(\Safe\sprintf('Expected null, string or integer; received [%s].', \is_object($color) ? \get_class($color) : \gettype($color)));
        }

        if ($color === null) {
            $this->foreground = null;

            return;
        }

        if (\is_int($color) && ! ((0 <= $color) && ($color <= 255))) {
            throw new InvalidArgumentException(\Safe\sprintf('Invalid foreground color code specified: [%s]. Expected one code between 0 and 255.', $color));
        }

        $this->foreground = ['set' => $color, 'unset' => 39];
    }

    /**
     * {@inheritdoc}
     */
    public function setBackground($color = null): void
    {
        if (! \is_int($color) && ! \is_string($color) && $color !== null) {
            throw new InvalidArgumentException(\Safe\sprintf('Expected null, string or integer; received [%s].', \is_object($color) ? \get_class($color) : \gettype($color)));
        }

        if ($color === null) {
            $this->background = null;

            return;
        }

        if (\is_int($color) && ! ((0 <= $color) && ($color <= 255))) {
            throw new InvalidArgumentException(\Safe\sprintf('Invalid background color code specified: [%s]. Expected one code between 0 and 255.', $color));
        }

        $this->background = ['set' => $color, 'unset' => 49];
    }
}
