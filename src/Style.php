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

use Testomat\TerminalColour\Contract\Color16Aware as Color16AwareContract;
use Testomat\TerminalColour\Exception\InvalidArgumentException;

/**
 * @noRector \Rector\SOLID\Rector\ClassMethod\ChangeReadOnlyVariableWithDefaultValueToConstantRector
 */
final class Style extends AbstractStyle implements Color16AwareContract
{
    /** @var array<string, int> */
    private const AVAILABLE_FOREGROUND_COLORS = [
        'black' => ['set' => 30, 'unset' => 39],
        'red' => ['set' => 31, 'unset' => 39],
        'green' => ['set' => 32, 'unset' => 39],
        'yellow' => ['set' => 33, 'unset' => 39],
        'blue' => ['set' => 34, 'unset' => 39],
        'magenta' => ['set' => 35, 'unset' => 39],
        'cyan' => ['set' => 36, 'unset' => 39],
        'white' => ['set' => 37, 'unset' => 39],
        'default' => ['set' => 39, 'unset' => 39],
        'dark_grey' => ['set' => 90, 'unset' => 39],
        'light_grey' => ['set' => 37, 'unset' => 39],
        'light_red' => ['set' => 91, 'unset' => 39],
        'light_green' => ['set' => 92, 'unset' => 39],
        'light_yellow' => ['set' => 93, 'unset' => 39],
        'light_blue' => ['set' => 94, 'unset' => 39],
        'light_magenta' => ['set' => 95, 'unset' => 39],
        'light_cyan' => ['set' => 96, 'unset' => 39],
        'light_white' => ['set' => 97, 'unset' => 39],
    ];

    /** @var array<string, int> */
    private const AVAILABLE_BACKGROUND_COLORS = [
        'black' => ['set' => 40, 'unset' => 49],
        'red' => ['set' => 41, 'unset' => 49],
        'green' => ['set' => 42, 'unset' => 49],
        'yellow' => ['set' => 43, 'unset' => 49],
        'blue' => ['set' => 44, 'unset' => 49],
        'magenta' => ['set' => 45, 'unset' => 49],
        'cyan' => ['set' => 46, 'unset' => 49],
        'white' => ['set' => 47, 'unset' => 49],
        'default' => ['set' => 49, 'unset' => 49],
        'dark_grey' => ['set' => 100, 'unset' => 49],
        'light_grey' => ['set' => 47, 'unset' => 49],
        'light_red' => ['set' => 101, 'unset' => 49],
        'light_green' => ['set' => 102, 'unset' => 49],
        'light_yellow' => ['set' => 103, 'unset' => 49],
        'light_blue' => ['set' => 104, 'unset' => 49],
        'light_magenta' => ['set' => 105, 'unset' => 49],
        'light_cyan' => ['set' => 106, 'unset' => 49],
        'light_white' => ['set' => 107, 'unset' => 49],
    ];

    public function __construct(?string $foreground = null, ?string $background = null, array $effects = [])
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
        if (! \is_string($color) && $color !== null) {
            throw new InvalidArgumentException(\Safe\sprintf('Expected null or string; received [%s].', \is_object($color) ? \get_class($color) : \gettype($color)));
        }

        if ($color === null) {
            $this->foreground = null;

            return;
        }

        if (! isset(self::AVAILABLE_FOREGROUND_COLORS[$color])) {
            throw new InvalidArgumentException(\Safe\sprintf('Invalid foreground color specified: [%s]. Expected one of [%s].', $color, implode(', ', array_keys(self::AVAILABLE_FOREGROUND_COLORS))));
        }

        $this->foreground = self::AVAILABLE_FOREGROUND_COLORS[$color];
    }

    /**
     * {@inheritdoc}
     */
    public function setBackground($color = null): void
    {
        if (! \is_string($color) && $color !== null) {
            throw new InvalidArgumentException(\Safe\sprintf('Expected null or string; received [%s].', \is_object($color) ? \get_class($color) : \gettype($color)));
        }

        if ($color === null) {
            $this->background = null;

            return;
        }

        if (! isset(self::AVAILABLE_BACKGROUND_COLORS[$color])) {
            throw new InvalidArgumentException(\Safe\sprintf('Invalid background color specified: [%s]. Expected one of [%s].', $color, implode(', ', array_keys(self::AVAILABLE_BACKGROUND_COLORS))));
        }

        $this->background = self::AVAILABLE_BACKGROUND_COLORS[$color];
    }
}
