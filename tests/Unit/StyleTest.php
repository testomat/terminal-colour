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

use Exception;
use PHPUnit\Framework\TestCase;
use Testomat\TerminalColour\Exception\InvalidArgumentException;
use Testomat\TerminalColour\Style;

/**
 * @internal
 *
 * @covers \Testomat\TerminalColour\Style
 * @covers \Testomat\TerminalColour\AbstractStyle
 *
 * @medium
 */
final class StyleTest extends TestCase
{
    /**
     * @dataProvider provideConstructorCases
     */
    public function testConstructor(?string $fg, ?string $bg, array $effects, string $expected): void
    {
        $style = new Style($fg, $bg, $effects);

        self::assertEquals($expected, $style->apply('foo'));
    }

    public static function provideConstructorCases(): iterable
    {
        return [
            ['green', 'black', ['bold', 'underscore'], "\033[32;40;1;4mfoo\033[39;49;21;24m"],
            ['red', null, ['blink'], "\033[31;5mfoo\033[39;25m"],
            [null, 'white', [], "\033[47mfoo\033[49m"],
        ];
    }

    /**
     * @dataProvider provideForegroundCases
     */
    public function testForeground(?string $fg, string $expected): void
    {
        $style = new Style();

        $style->setForeground($fg);

        self::assertEquals($expected, $style->apply('foo'));

        $this->expectException(InvalidArgumentException::class);

        $style->setForeground('undefined-color');
    }

    public static function provideForegroundCases(): iterable
    {
        return [
            ['black', "\033[30mfoo\033[39m"],
            ['blue', "\033[34mfoo\033[39m"],
            ['default', "\033[39mfoo\033[39m"],
            [null, 'foo'],
        ];
    }

    public function testForegroundThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $style = new Style();

        $style->setForeground('undefined-color');
    }

    /**
     * @dataProvider provideBackgroundCases
     */
    public function testBackground(?string $bg, string $expected): void
    {
        $style = new Style();

        $style->setBackground($bg);

        self::assertEquals($expected, $style->apply('foo'));

        $this->expectException(InvalidArgumentException::class);

        $style->setBackground('undefined-color');
    }

    public static function provideBackgroundCases(): iterable
    {
        return [
            ['black', "\033[40mfoo\033[49m"],
            ['yellow', "\033[43mfoo\033[49m"],
            ['default', "\033[49mfoo\033[49m"],
            [null, 'foo'],
        ];
    }

    public function testBackgroundThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $style = new Style();

        $style->setBackground('undefined-color');
    }

    public function testEffects(): void
    {
        $style = new Style();

        $style->setEffects(['reverse', 'conceal']);

        self::assertEquals("\033[7;8mfoo1\033[27;28m", $style->apply('foo1'));

        $style->setEffect('bold');

        self::assertEquals("\033[7;8;1mfoo2\033[27;28;21m", $style->apply('foo2'));

        $style->unsetEffect('reverse');

        self::assertEquals("\033[8;1mfoo3\033[28;21m", $style->apply('foo3'));

        $style->setEffect('bold');

        self::assertEquals("\033[8;1mfoo4\033[28;21m", $style->apply('foo4'));

        $style->setEffects(['bold']);

        self::assertEquals("\033[1mfoo5\033[21m", $style->apply('foo5'));

        try {
            $style->setEffect('foo6');

            self::fail('->setEffect() throws an \InvalidArgumentException when the option does not exist in the available options');
        } catch (Exception $exception) {
            self::assertInstanceOf(InvalidArgumentException::class, $exception, '->setEffect() throws an \InvalidArgumentException when the option does not exist in the available options');
            self::assertStringContainsString('Invalid option specified: [foo6]', $exception->getMessage(), '->setEffect() throws an \InvalidArgumentException when the option does not exist in the available options');
        }

        try {
            $style->unsetEffect('foo7');

            self::fail('->unsetEffect() throws an \InvalidArgumentException when the option does not exist in the available options');
        } catch (Exception $exception) {
            self::assertInstanceOf(InvalidArgumentException::class, $exception, '->unsetEffect() throws an \InvalidArgumentException when the option does not exist in the available options');
            self::assertStringContainsString('Invalid option specified: [foo7]', $exception->getMessage(), '->unsetEffect() throws an \InvalidArgumentException when the option does not exist in the available options');
        }
    }

    public function testHref(): void
    {
        $prevTerminalEmulator = getenv('TERMINAL_EMULATOR');
        putenv('TERMINAL_EMULATOR');

        $style = new Style();

        try {
            $style->setHref('idea://open/?file=/path/SomeFile.php&line=12');
            self::assertSame("\e]8;;idea://open/?file=/path/SomeFile.php&line=12\e\\some URL\e]8;;\e\\", $style->apply('some URL'));
        } finally {
            putenv('TERMINAL_EMULATOR' . ($prevTerminalEmulator ? "={$prevTerminalEmulator}" : ''));
        }
    }
}
