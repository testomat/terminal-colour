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
use Testomat\TerminalColour\Style;
use Testomat\TerminalColour\Tests\Unit\Traits\EffectsTestTrait;
use Testomat\TerminalColour\Tests\Unit\Traits\HrefTestTrait;

/**
 * @internal
 *
 * @covers \Testomat\TerminalColour\AbstractStyle
 * @covers \Testomat\TerminalColour\Style
 *
 * @medium
 */
final class StyleTest extends TestCase
{
    use EffectsTestTrait;
    use HrefTestTrait;

    /**
     * @dataProvider provideConstructorCases
     *
     * @param array<string, int|string> $effects
     */
    public function testConstructor(?string $fg, ?string $bg, array $effects, string $expected): void
    {
        $style = new Style($fg, $bg, $effects);

        self::assertEquals($expected, $style->apply('foo'));
    }

    /**
     * @return iterable<int, array<int, null|array<int, string>|string>>
     */
    public static function provideConstructorCases(): iterable
    {
        return [
            ['green', 'black', ['bold', 'underscore'], "\033[32;40;1;4mfoo\033[39;49;22;24m"],
            ['red', null, ['blink'], "\033[31;5mfoo\033[39;25m"],
            [null, 'white', [], "\033[47mfoo\033[49m"],
        ];
    }

    /**
     * @dataProvider provideSetForegroundCases
     */
    public function testSetForeground(?string $fg, string $expected): void
    {
        $style = new Style();
        $style->setForeground($fg);

        self::assertEquals($expected, $style->apply('foo'));
    }

    /**
     * @return array<int, array<int, null|string>>
     */
    public static function provideSetForegroundCases(): iterable
    {
        return [
            ['black', "\033[30mfoo\033[39m"],
            ['blue', "\033[34mfoo\033[39m"],
            ['default', "\033[39mfoo\033[39m"],
            [null, 'foo'],
        ];
    }

    public function testSetForegroundWithInvalidColor(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid foreground color specified: [undefined-color]. Expected one of [black, red, green, yellow, blue, magenta, cyan, white, default, dark_grey, light_grey, light_red, light_green, light_yellow, light_blue, light_magenta, light_cyan, light_white].');

        $style = new Style();
        $style->setForeground('undefined-color');
    }

    public function testSetForegroundWithInvalidValue(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected null or string; received [stdClass].');

        $style = new Style();
        $style->setForeground(new stdClass());
    }

    /**
     * @dataProvider provideSetBackgroundCases
     */
    public function testSetBackground(?string $bg, string $expected): void
    {
        $style = new Style();
        $style->setBackground($bg);

        self::assertEquals($expected, $style->apply('foo'));
    }

    /**
     * @return array<int, array<int, null|string>>
     */
    public static function provideSetBackgroundCases(): iterable
    {
        return [
            ['black', "\033[40mfoo\033[49m"],
            ['yellow', "\033[43mfoo\033[49m"],
            ['default', "\033[49mfoo\033[49m"],
            [null, 'foo'],
        ];
    }

    public function testSetBackgroundWithInvalidColor(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid background color specified: [undefined-color]. Expected one of [black, red, green, yellow, blue, magenta, cyan, white, default, dark_grey, light_grey, light_red, light_green, light_yellow, light_blue, light_magenta, light_cyan, light_white].');

        $style = new Style();

        $style->setBackground('undefined-color');
    }

    public function testSetBackgroundWithInvalidValue(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected null or string; received [stdClass].');

        $style = new Style();
        $style->setBackground(new stdClass());
    }

    /**
     * {@inheritdoc}
     */
    protected function getStyleInstance(): StyleContract
    {
        return new Style();
    }
}
