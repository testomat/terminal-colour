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

namespace Testomat\TerminalColour\Tests\Unit;

use PHPUnit\Framework\TestCase;
use stdClass;
use Testomat\TerminalColour\Contract\Style as StyleContract;
use Testomat\TerminalColour\Exception\InvalidArgumentException;
use Testomat\TerminalColour\StyleCode;
use Testomat\TerminalColour\Tests\Unit\Traits\EffectsTestTrait;
use Testomat\TerminalColour\Tests\Unit\Traits\HrefTestTrait;

/**
 * @internal
 *
 * @covers \Testomat\TerminalColour\AbstractStyle
 * @covers \Testomat\TerminalColour\StyleCode
 *
 * @medium
 */
final class StyleCodeTest extends TestCase
{
    use EffectsTestTrait;
    use HrefTestTrait;

    /**
     * @dataProvider provideConstructorCases
     *
     * @param array<int, array<int, int>|string> $effects
     */
    public function testConstructor(?int $fg, ?int $bg, array $effects, string $expected): void
    {
        $style = new StyleCode($fg, $bg, $effects);

        self::assertEquals($expected, $style->apply('foo'));
    }

    /**
     * @return array<int, array<int, null|array|int|string>>
     */
    public static function provideConstructorCases(): iterable
    {
        return [
            [32, 40, ['bold', 'underscore'], "\033[32;40;1;4mfoo\033[39;49;22;24m"],
            [31, null, ['blink'], "\033[31;5mfoo\033[39;25m"],
            [null, 47, [], "\033[47mfoo\033[49m"],
        ];
    }

    /**
     * @dataProvider provideSetForegroundCases
     */
    public function testSetForeground(?int $fg, string $expected, int $colorLevel = 16): void
    {
        $style = new StyleCode();
        $style->setColorLevel($colorLevel);
        $style->setForeground($fg);

        self::assertEquals($expected, $style->apply('foo'));
    }

    /**
     * @return array<int, array<int, null|int|string>>
     */
    public static function provideSetForegroundCases(): iterable
    {
        return [
            [30, "\033[30mfoo\033[39m"],
            [null, 'foo'],
            [30, "\033[30mfoo\033[39m", 0],
            [255, "\033[38;5;255mfoo\033[39m", 255],
        ];
    }

    public function testSetForegroundWithInvalidColor(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid foreground color code specified: [277]. Expected one code between 0 and 255.');

        $style = new StyleCode();
        $style->setForeground(277);
    }

    public function testSetForegroundWithInvalidValue(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected null, string or integer; received [stdClass].');

        $style = new StyleCode();
        $style->setForeground(new stdClass());
    }

    /**
     * @dataProvider provideSetBackgroundCases
     */
    public function testSetBackground(?int $bg, string $expected, int $colorLevel = 16): void
    {
        $style = new StyleCode();
        $style->setColorLevel($colorLevel);
        $style->setBackground($bg);

        self::assertEquals($expected, $style->apply('foo'));
    }

    /**
     * @return array<int, array<int, null|int|string>>
     */
    public static function provideSetBackgroundCases(): iterable
    {
        return [
            [30, "\033[30mfoo\033[49m"],
            [null, 'foo'],
            [30, "\033[30mfoo\033[49m", 0],
            [255, "\033[48;5;255mfoo\033[49m", 255],
        ];
    }

    public function testSetBackgroundWithInvalidColor(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid background color code specified: [277]. Expected one code between 0 and 255.');

        $style = new StyleCode();

        $style->setBackground(277);
    }

    public function testSetBackgroundWithInvalidValue(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected null, string or integer; received [stdClass].');

        $style = new StyleCode();
        $style->setBackground(new stdClass());
    }

    /**
     * {@inheritdoc}
     */
    protected function getStyleInstance(): StyleContract
    {
        return new StyleCode();
    }
}
