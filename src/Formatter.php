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
use Testomat\TerminalColour\Contract\WrappableFormatter as WrappableFormatterContract;
use Testomat\TerminalColour\Exception\InvalidArgumentException;

final class Formatter implements WrappableFormatterContract
{
    /** @var string */
    public const VERSION = '1.0.0';

    /** @var bool */
    private $decorated;

    /** @var array<int, Style> */
    private $styles = [];

    /** @var Stack */
    private $styleStack;

    /*
     * @param Style[] $styles Array of "name => Style" instances
     */
    public function __construct(bool $decorated = false, array $styles = [])
    {
        $this->decorated = $decorated;

        $this->setStyle('error', new Style('white', 'red'));
        $this->setStyle('info', new Style('green'));
        $this->setStyle('comment', new Style('yellow'));
        $this->setStyle('question', new Style('black', 'cyan'));

        foreach ($styles as $name => $style) {
            $this->setStyle($name, $style);
        }

        $this->styleStack = new Stack();
    }

    /**
     * {@inheritDoc}
     */
    public function isDecorated(): bool
    {
        return $this->decorated;
    }

    /**
     * {@inheritDoc}
     */
    public function setDecorated(bool $decorated): void
    {
        $this->decorated = $decorated;
    }

    /**
     * {@inheritDoc}
     */
    public function getStyleStack(): Stack
    {
        return $this->styleStack;
    }

    /**
     * Escapes "<" special char in given text.
     *
     * @return string Escaped text
     */
    public static function escape(string $text): string
    {
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
     * {@inheritDoc}
     */
    public function setStyle(string $name, StyleContract $style): void
    {
        $this->styles[strtolower($name)] = $style;
    }

    /**
     * {@inheritDoc}
     */
    public function hasStyle(string $name): bool
    {
        return isset($this->styles[strtolower($name)]);
    }

    /**
     * {@inheritDoc}
     */
    public function getStyle(string $name): StyleContract
    {
        if (! $this->hasStyle($name)) {
            throw new InvalidArgumentException(\Safe\sprintf('Undefined style: [%s].', $name));
        }

        return $this->styles[strtolower($name)];
    }

    /**
     * {@inheritDoc}
     */
    public function format(?string $message): string
    {
        return $this->formatAndWrap($message, 0);
    }

    /**
     * {@inheritDoc}
     */
    public function formatAndWrap(?string $message, int $width): string
    {
        $offset = 0;
        $output = '';
        $tagRegex = '[a-z][^<>]*+';
        $currentLineLength = 0;

        \Safe\preg_match_all("#<(({$tagRegex}) | /({$tagRegex})?)>#ix", $message, $matches, \PREG_OFFSET_CAPTURE);

        foreach ($matches[0] as $i => $match) {
            $pos = $match[1];
            $text = $match[0];

            if ($pos !== 0 && $message[$pos - 1] === '\\') {
                continue;
            }

            // add the text up to the next tag
            $output .= $this->applyCurrentStyle(\Safe\substr($message, $offset, $pos - $offset), $output, $width, $currentLineLength);
            $offset = $pos + \strlen($text);

            // opening tag?
            if ($open = ('/' !== $text[1])) {
                $tag = $matches[1][$i][0];
            } else {
                $tag = $matches[3][$i][0] ?? '';
            }

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
    private function createStyleFromString(string $string): ?Style
    {
        if (isset($this->styles[$string])) {
            return $this->styles[$string];
        }

        if (! \Safe\preg_match_all('/([^=]+)=([^;]+)(;|$)/', $string, $matches, \PREG_SET_ORDER)) {
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
            } elseif ('options' === $match[0]) {
                \Safe\preg_match_all('([^,;]+)', strtolower($match[1]), $options);

                $options = array_shift($options);

                foreach ($options as $option) {
                    $style->setOption($option);
                }
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
            $prefix = \Safe\substr($text, 0, $i = $width - $currentLineLength) . "\n";
            $text = \Safe\substr($text, $i);
            var_dump($text);
        } else {
            $prefix = '';
        }

        \Safe\preg_match('~(\\n)$~', $text, $matches);

        $text = $prefix . \Safe\preg_replace('~([^\\n]{' . $width . '})\\ *~', "\$1\n", $text);
        $text = rtrim($text, "\n") . ($matches[1] ?? '');

        if (! $currentLineLength && '' !== $current && "\n" !== \Safe\substr($current, -1)) {
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
