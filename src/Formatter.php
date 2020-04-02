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

use Safe\Exceptions\StringsException;
use Testomat\TerminalColour\Contract\Style as StyleContract;
use Testomat\TerminalColour\Contract\WrappableFormatter as WrappableFormatterContract;
use Testomat\TerminalColour\Exception\InvalidArgumentException;

/**
 * @noRector \Rector\SOLID\Rector\ClassMethod\ChangeReadOnlyVariableWithDefaultValueToConstantRector
 */
final class Formatter implements WrappableFormatterContract
{
    /**
     * @noRector \Rector\DeadCode\Rector\ClassConst\RemoveUnusedClassConstantRector
     *
     * @var string
     */
    public const VERSION = '1.1.1';

    /** @var bool */
    private $decorated;

    /** @var array<string, \Testomat\TerminalColour\Contract\Style> */
    private $styles = [];

    /** @var Stack */
    private $styleStack;

    /** @var int */
    private $colorLevel;

    /**
     * @param array<string, \Testomat\TerminalColour\Contract\Style> $styles Array of "name => Style" instances
     * @param null|resource                                          $stream
     */
    public function __construct(bool $decorated = false, array $styles = [], $stream = null)
    {
        $this->decorated = $decorated;
        $this->colorLevel = Util::getSupportedColor($stream);

        $defaultStyles = [
            'error' => new Style('white', 'red'),
            'info' => new Style('green'),
            'comment' => new Style('yellow'),
            'question' => new Style('black', 'cyan'),
        ];

        foreach (array_merge($defaultStyles, $styles) as $name => $style) {
            $style->setColorLevel($this->colorLevel);

            $this->setStyle($name, $style);
        }

        $this->styleStack = new Stack();
    }

    /**
     * {@inheritdoc}
     */
    public function isDecorated(): bool
    {
        return $this->decorated;
    }

    /**
     * {@inheritdoc}
     */
    public function setDecorated(bool $decorated): void
    {
        $this->decorated = $decorated;
    }

    /**
     * @codeCoverageIgnore
     */
    public function getStyleStack(): Stack
    {
        return $this->styleStack;
    }

    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public function getColorLevel(): int
    {
        return $this->colorLevel;
    }

    /**
     * Escapes "<" special char in given text.
     *
     * @return string Escaped text
     */
    public static function escape(string $text): string
    {
        /** @var string $text */
        $text = \Safe\preg_replace('/([^\\\\]?)</', '$1\\<', $text);

        return self::escapeTrailingBackslash($text);
    }

    /**
     * Escapes trailing "\" in given text.
     *
     * @internal
     */
    public static function escapeTrailingBackslash(string $text): string
    {
        if (\Safe\substr($text, -1) === '\\') {
            $len = \strlen($text);

            $text = rtrim($text, '\\');
            $text = str_replace("\0", '', $text);
            $text .= str_repeat("\0", $len - \strlen($text));
        }

        return $text;
    }

    /**
     * {@inheritdoc}
     */
    public function setStyle(string $name, StyleContract $style): void
    {
        $this->styles[strtolower($name)] = $style;
    }

    /**
     * {@inheritdoc}
     */
    public function hasStyle(string $name): bool
    {
        return isset($this->styles[strtolower($name)]);
    }

    /**
     * {@inheritdoc}
     */
    public function getStyle(string $name): StyleContract
    {
        if (! $this->hasStyle($name)) {
            throw new InvalidArgumentException(\Safe\sprintf('Undefined style: [%s].', $name));
        }

        return $this->styles[strtolower($name)];
    }

    /**
     * {@inheritdoc}
     */
    public function format(string $message): string
    {
        return $this->formatAndWrap($message, 0);
    }

    /**
     * {@inheritdoc}
     */
    public function formatAndWrap(string $message, int $width): string
    {
        $offset = 0;
        $output = '';
        $tagRegex = '[a-z][^<>]*+';
        $currentLineLength = 0;

        \Safe\preg_match_all("#<(({$tagRegex}) | /({$tagRegex})?)>#ix", $message, $matches, \PREG_OFFSET_CAPTURE);

        foreach ((array) $matches[0] as $i => $match) {
            [$text, $pos] = $match;

            if ($pos !== 0 && $message[$pos - 1] === '\\') {
                continue;
            }

            // add the text up to the next tag
            $output .= $this->applyCurrentStyle(\Safe\substr($message, $offset, $pos - $offset), $output, $width, $currentLineLength);
            $offset = $pos + \strlen($text);

            $tag = ($open = ($text[1] !== '/')) ? $matches[1][$i][0] : $matches[3][$i][0] ?? '';

            if (! $open && ! $tag) {
                // </>
                $this->styleStack->pop();
            } elseif (null === $style = $this->createStyleFromString($tag)) {
                $output .= $this->applyCurrentStyle($text, $output, $width, $currentLineLength);
            } elseif ($open) {
                $this->styleStack->push($style);
            } else {
                $this->styleStack->pop($style);
            }
        }

        $output .= $this->applyCurrentStyle(\Safe\substr($message, $offset), $output, $width, $currentLineLength);

        if (strpos($output, "\0") !== false) {
            return strtr($output, ["\0" => '\\', '\\<' => '<']);
        }

        return str_replace('\\<', '<', $output);
    }

    /**
     * Tries to create new style instance from string.
     */
    private function createStyleFromString(string $string): ?StyleContract
    {
        if (isset($this->styles[$string])) {
            return $this->styles[$string];
        }

        if (\Safe\preg_match_all('/([^=]+)=([^;]+)(;|$)/', $string, $matches, \PREG_SET_ORDER) === 0) {
            return null;
        }

        $style = new Style();

        foreach ($matches as $match) {
            array_shift($match);

            $match[0] = strtolower($match[0]);

            if ('fg' === $match[0]) {
                $style->setForeground(strtolower($match[1]));
            } elseif ('bg' === $match[0]) {
                $style->setBackground(strtolower($match[1]));
            } elseif ('href' === $match[0]) {
                $style->setHref($match[1]);
            } elseif ('effects' === $match[0]) {
                \Safe\preg_match_all('([^,;]+)', strtolower($match[1]), $effects);

                $effects = array_shift($effects);

                $style->setEffects($effects);
            } else {
                return null;
            }
        }

        return $style;
    }

    /**
     * Applies current style from stack to text, if must be applied.
     */
    private function applyCurrentStyle(string $text, string $current, int $width, int &$currentLineLength): string
    {
        if ($text === '') {
            return '';
        }

        if ($width === 0) {
            return $this->isDecorated() ? $this->styleStack->getCurrent()->apply($text) : $text;
        }

        if ($currentLineLength === 0 && $current !== '') {
            $text = ltrim($text);
        }

        if ($currentLineLength !== 0) {
            $i = $width - $currentLineLength;
            $prefix = \Safe\substr($text, 0, $i) . "\n";

            try {
                $text = \Safe\substr($text, $i);
            } catch (StringsException $exception) {
                $text = '';
            }
        } else {
            $prefix = '';
        }

        \Safe\preg_match('~(\\n)$~', $text, $matches);

        /** @noRector \Rector\DeadCode\Rector\Concat\RemoveConcatAutocastRector */
        $stringWidth = (string) $width;

        /** @var string $replacedText */
        $replacedText = \Safe\preg_replace('~([^\\n]{' . $stringWidth . '})\\ *~', "\$1\n", $text);

        $text = $prefix . $replacedText;
        $text = rtrim($text, "\n") . ($matches[1] ?? '');

        if ($currentLineLength === 0 && $current !== '' && \Safe\substr($current, -1) !== "\n") {
            $text = "\n" . $text;
        }

        $lines = explode("\n", $text);

        foreach ($lines as $line) {
            $currentLineLength += \strlen($line);

            if ($width <= $currentLineLength) {
                $currentLineLength = 0;
            }
        }

        if ($this->isDecorated()) {
            foreach ($lines as $i => $line) {
                $lines[$i] = $this->styleStack->getCurrent()->apply($line);
            }
        }

        return implode("\n", $lines);
    }
}
