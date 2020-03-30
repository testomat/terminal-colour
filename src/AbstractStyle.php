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
use Testomat\TerminalColour\Contract\Style as StyleContract;
use Testomat\TerminalColour\Contract\TrueColorAware as TrueColorAwareContract;
use Testomat\TerminalColour\Exception\InvalidArgumentException;

/**
 * @noRector \Rector\SOLID\Rector\ClassMethod\ChangeReadOnlyVariableWithDefaultValueToConstantRector
 */
abstract class AbstractStyle implements StyleContract
{
    /** @var array<string, int|string> */
    private const AVAILABLE_EFFECTS = [
        'none' => ['set' => 0, 'unset' => 0],
        'bold' => ['set' => 1, 'unset' => 22],
        'dark' => ['set' => 2, 'unset' => 22],
        'italic' => ['set' => 3, 'unset' => 23],
        'underscore' => ['set' => 4, 'unset' => 24],
        'blink' => ['set' => 5, 'unset' => 25],
        'blink_fast' => ['set' => 6, 'unset' => 25], // Limited support
        'reverse' => ['set' => 7, 'unset' => 27],
        'conceal' => ['set' => 8, 'unset' => 28],
        'crossed_out' => ['set' => 9, 'unset' => 29],
        'double_underline' => ['set' => 21, 'unset' => 24],
        'curly_underline' => ['set' => '4:3', 'unset' => '4:0'], // Limited support
        'overline' => ['set' => 53, 'unset' => 55], // Limited support
    ];

    /** @var null|array<string, int|string> */
    protected $foreground;

    /** @var null|array<string, int|string> */
    protected $background;

    /** @var null|string */
    protected $href;

    /**
     * @var array<int, array<string, int|string>>
     */
    protected $effects = [];

    /** @var null|bool */
    protected $handlesHrefGracefully;

    /** @var int */
    protected $colorLevel;

    /**
     * {@inheritdoc}
     */
    final public function setHref(string $url): void
    {
        $this->href = $url;
    }

    /**
     * {@inheritdoc}
     */
    final public function setEffects(array $effects): void
    {
        $this->effects = [];

        foreach ($effects as $effect) {
            $this->setEffect($effect);
        }
    }

    /**
     * {@inheritdoc}
     */
    final public function setColorLevel(int $color): void
    {
        $this->colorLevel = $color;
    }

    /**
     * {@inheritdoc}
     */
    final public function setEffect($effect): void
    {
        if (\is_string($effect)) {
            if (! isset(self::AVAILABLE_EFFECTS[$effect])) {
                throw new InvalidArgumentException(\Safe\sprintf('Invalid effect specified: [%s]. Expected one of [%s].', $effect, implode(', ', array_keys(self::AVAILABLE_EFFECTS))));
            }

            if (! \in_array(self::AVAILABLE_EFFECTS[$effect], $this->effects, true)) {
                $this->effects[] = self::AVAILABLE_EFFECTS[$effect];
            }

            return;
        }

        if (\is_array($effect)) {
            if (! (\array_key_exists('set', $effect) && \array_key_exists('unset', $effect))) {
                throw new InvalidArgumentException(\Safe\sprintf('Provided array is missing [set] or [unset] key; The array must look like [\'set\' => int, \'unset\' => int]; received [%s].', var_export($effect, true)));
            }

            $this->effects[] = $effect;

            return;
        }

        throw new InvalidArgumentException(\Safe\sprintf('Expected array or string; received [%s].', \is_object($effect) ? \get_class($effect) : \gettype($effect)));
    }

    /**
     * {@inheritdoc}
     */
    final public function unsetEffect($effect): void
    {
        if (! \is_string($effect) && ! \is_array($effect)) {
            throw new InvalidArgumentException(\Safe\sprintf('Expected array or string; received [%s].', \is_object($effect) ? \get_class($effect) : \gettype($effect)));
        }

        if (\is_string($effect)) {
            if (! isset(self::AVAILABLE_EFFECTS[$effect])) {
                throw new InvalidArgumentException(\Safe\sprintf('Invalid effect specified: [%s]. Expected one of [%s].', $effect, implode(', ', array_keys(self::AVAILABLE_EFFECTS))));
            }

            /** @noRector \Rector\CodeQuality\Rector\Identical\SimplifyBoolIdenticalTrueRector */
            if (($pos = array_search(self::AVAILABLE_EFFECTS[$effect], $this->effects, true)) !== false) {
                unset($this->effects[$pos]);
            }

            return;
        }

        /** @noRector \Rector\CodeQuality\Rector\Identical\SimplifyBoolIdenticalTrueRector */
        if (($pos = array_search($effect, $this->effects, true)) !== false) {
            unset($this->effects[$pos]);
        }
    }

    /**
     * {@inheritdoc}
     */
    final public function apply(string $text): string
    {
        if ($this->handlesHrefGracefully === null) {
            $this->handlesHrefGracefully = getenv('TERMINAL_EMULATOR') !== 'JetBrains-JediTerm' && getenv('KONSOLE_VERSION') !== false;
        }

        $setCodes = [];
        $unsetCodes = [];

        if ($this->foreground !== null) {
            $setCodes[] = $this->foreground['set'];
            $unsetCodes[] = $this->foreground['unset'];
        }

        if ($this->background !== null) {
            $setCodes[] = $this->background['set'];
            $unsetCodes[] = $this->background['unset'];
        }

        foreach ($this->effects as $effect) {
            $setCodes[] = $effect['set'];
            $unsetCodes[] = $effect['unset'];
        }

        if ($this->href !== null && $this->handlesHrefGracefully) {
            $text = "\033]8;;{$this->href}\033\\{$text}\033]8;;\033\\";
        }

        if (\count($setCodes) === 0) {
            return $text;
        }

        if ($this->colorLevel >= Util::COLOR256_TERMINAL && ($this instanceof Color256AwareContract || $this instanceof TrueColorAwareContract) && \count($this->effects) === 0) {
            if ($this->background === null && $this->foreground !== null && strpos((string) $this->foreground['set'], '38;5') === false) {
                $setCodes = array_merge(['38;5'], $setCodes);
            }

            if ($this->foreground === null && $this->background !== null && strpos((string) $this->background['set'], '48;5') === false) {
                $setCodes = array_merge(['48;5'], $setCodes);
            }
        }

        return \Safe\sprintf("\033[%sm%s\033[%sm", implode(';', $setCodes), $text, implode(';', $unsetCodes));
    }
}
