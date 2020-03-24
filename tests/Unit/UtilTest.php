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
use Testomat\TerminalColour\Exception\InvalidArgumentException;
use Testomat\TerminalColour\Util;

/**
 * @internal
 *
 * @medium
 * @covers \Testomat\TerminalColour\Util
 */
final class UtilTest extends TestCase
{
    public function testSupportsColorWithFalseValue(): void
    {
        self::assertSame(Util::NO_COLOR_TERMINAL, Util::supportsColor(false));
    }

    public function testSupportsColorThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Util::supportsColor('');
    }

    public function testSupportsColorWithTermProgram(): void
    {
        \Safe\putenv('TERM_PROGRAM=Hyper');

        $stream = \Safe\fopen(sys_get_temp_dir(), 'r');

        self::assertSame(Util::COLOR_TERMINAL, Util::supportsColor($stream));

        \Safe\fclose($stream);

        \Safe\putenv('TERM_PROGRAM=');
        \Safe\putenv('TERM_PROGRAM');
    }

    public function testSupportsColorWithTermProgramAndTerm(): void
    {
        \Safe\putenv('TERM_PROGRAM=Hyper');
        \Safe\putenv('TERM=256color');

        $stream = \Safe\fopen(sys_get_temp_dir(), 'r');

        self::assertSame(Util::COLOR256_TERMINAL, Util::supportsColor($stream));

        \Safe\fclose($stream);

        \Safe\putenv('TERM_PROGRAM=');
        \Safe\putenv('TERM_PROGRAM');
        \Safe\putenv('TERM=');
        \Safe\putenv('TERM');
    }

    public function testSupportsColorWithTermProgramAndColorTerm(): void
    {
        \Safe\putenv('TERM_PROGRAM=Hyper');
        \Safe\putenv('COLORTERM=truecolor');

        $stream = \Safe\fopen(sys_get_temp_dir(), 'r');

        self::assertSame(Util::TRUECOLOR_TERMINAL, Util::supportsColor($stream));

        \Safe\fclose($stream);

        \Safe\putenv('TERM_PROGRAM=');
        \Safe\putenv('TERM_PROGRAM');
        \Safe\putenv('COLORTERM=');
        \Safe\putenv('COLORTERM');
    }
}
