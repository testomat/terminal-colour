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
 * @noRector \Rector\SOLID\Rector\ClassMethod\ChangeReadOnlyVariableWithDefaultValueToConstantRector
 */
final class Style implements StyleContract
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
    ];

    /** @var array<string, int> */
    private const AVAILABLE_EFFECTS = [
        'bold' => ['set' => 1, 'unset' => 22],
        'underscore' => ['set' => 4, 'unset' => 24],
        'blink' => ['set' => 5, 'unset' => 25],
        'reverse' => ['set' => 7, 'unset' => 27],
        'conceal' => ['set' => 8, 'unset' => 28],
    ];

    /** @var null|array<string, int> */
    private $foreground;

    /** @var null|array<string, int> */
    private $background;

    /** @var null|string */
    private $href;

    /** @var array */
    private $effects = [];

    /** @var null|bool */
    private $handlesHrefGracefully;

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
    public function setForeground(?string $color = null): void
    {
        if ($color === null) {
            $this->foreground = null;

            return;
        }

        if (! isset(self::AVAILABLE_FOREGROUND_COLORS[$color])) {
            $message = \Safe\sprintf('Invalid foreground color specified: [%s]. Expected one of [%s].', $color, implode(', ', array_keys(self::AVAILABLE_FOREGROUND_COLORS)));

            throw new InvalidArgumentException($message);
        }

        $this->foreground = self::AVAILABLE_FOREGROUND_COLORS[$color];
    }

    /**
     * {@inheritdoc}
     */
    public function setBackground(?string $color = null): void
    {
        if ($color === null) {
            $this->background = null;

            return;
        }

        if (! isset(self::AVAILABLE_BACKGROUND_COLORS[$color])) {
            $message = \Safe\sprintf('Invalid background color specified: [%s]. Expected one of [%s].', $color, implode(', ', array_keys(self::AVAILABLE_BACKGROUND_COLORS)));

            throw new InvalidArgumentException($message);
        }

        $this->background = self::AVAILABLE_BACKGROUND_COLORS[$color];
    }

    /**
     * {@inheritdoc}
     */
    public function setHref(string $url): void
    {
        $this->href = $url;
    }

    /**
     * {@inheritdoc}
     */
    public function setEffects(array $effects): void
    {
        $this->effects = [];

        foreach ($effects as $option) {
            $this->setOption($option);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setOption(string $option): void
    {
        if (! isset(self::AVAILABLE_EFFECTS[$option])) {
            $message = \Safe\sprintf('Invalid option specified: [%s]. Expected one of [%s].', $option, implode(', ', array_keys(self::AVAILABLE_EFFECTS)));

            throw new InvalidArgumentException($message);
        }

        if (! \in_array(self::AVAILABLE_EFFECTS[$option], $this->effects, true)) {
            $this->effects[] = self::AVAILABLE_EFFECTS[$option];
        }
    }

    /**
     * {@inheritdoc}
     */
    public function unsetOption(string $option): void
    {
        if (! isset(self::AVAILABLE_EFFECTS[$option])) {
            $message = \Safe\sprintf('Invalid option specified: [%s]. Expected one of [%s].', $option, implode(', ', array_keys(self::AVAILABLE_EFFECTS)));

            throw new InvalidArgumentException($message);
        }

        $pos = array_search(self::AVAILABLE_EFFECTS[$option], $this->effects, true);

        if (false !== $pos) {
            unset($this->effects[$pos]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function apply(string $text): string
    {
        $setCodes = [];
        $unsetCodes = [];

        if (null === $this->handlesHrefGracefully) {
            $this->handlesHrefGracefully = 'JetBrains-JediTerm' !== getenv('TERMINAL_EMULATOR') && ! getenv('KONSOLE_VERSION');
        }

        if ($this->foreground !== null) {
            $setCodes[] = $this->foreground['set'];
            $unsetCodes[] = $this->foreground['unset'];
        }

        if ($this->background !== null) {
            $setCodes[] = $this->background['set'];
            $unsetCodes[] = $this->background['unset'];
        }

        foreach ($this->effects as $option) {
            $setCodes[] = $option['set'];
            $unsetCodes[] = $option['unset'];
        }

        if ($this->href !== null && $this->handlesHrefGracefully) {
            $text = "\033]8;;{$this->href}\033\\{$text}\033]8;;\033\\";
        }

        if (\count($setCodes) === 0) {
            return $text;
        }

        return \Safe\sprintf("\033[%sm%s\033[%sm", implode(';', $setCodes), $text, implode(';', $unsetCodes));
    }
}
