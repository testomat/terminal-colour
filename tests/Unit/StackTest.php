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
use Testomat\TerminalColour\Stack;
use Testomat\TerminalColour\Style;

/**
 * @internal
 *
 * @covers \Testomat\TerminalColour\Stack
 * @covers \Testomat\TerminalColour\Style
 *
 * @small
 */
final class StackTest extends TestCase
{
    public function testPush(): void
    {
        $stack = new Stack();
        $stack->push($s1 = new Style('white', 'black'));
        $stack->push($s2 = new Style('yellow', 'blue'));

        self::assertEquals($s2, $stack->getCurrent());

        $stack->push($s3 = new Style('green', 'red'));

        self::assertEquals($s3, $stack->getCurrent());
    }

    public function testGetCurrentShouldReturnEmptyStyle(): void
    {
        $stack = new Stack();

        self::assertEquals(new Style(), $stack->getCurrent());
    }

    public function testPop(): void
    {
        $stack = new Stack();
        $stack->push($s1 = new Style('white', 'black'));
        $stack->push($s2 = new Style('yellow', 'blue'));

        self::assertEquals($s2, $stack->pop());
        self::assertEquals($s1, $stack->pop());
    }

    public function testPopEmpty(): void
    {
        $stack = new Stack();
        $style = new Style();

        self::assertEquals($style, $stack->pop());
    }

    public function testPopNotLast(): void
    {
        $stack = new Stack();
        $stack->push($s1 = new Style('white', 'black'));
        $stack->push($s2 = new Style('yellow', 'blue'));
        $stack->push($s3 = new Style('green', 'red'));

        self::assertEquals($s2, $stack->pop($s2));
        self::assertEquals($s1, $stack->pop());
    }

    public function testInvalidPop(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $stack = new Stack();
        $stack->push(new Style('white', 'black'));
        $stack->pop(new Style('yellow', 'blue'));
    }
}
