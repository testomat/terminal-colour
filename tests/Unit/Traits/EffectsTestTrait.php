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

namespace Testomat\TerminalColour\Tests\Unit\Traits;

use stdClass;
use Testomat\TerminalColour\Contract\Style as StyleContract;
use Testomat\TerminalColour\Exception\InvalidArgumentException;

trait EffectsTestTrait
{
    public function testEffects(): void
    {
        $style = $this->getStyleInstance();

        $style->setEffects(['reverse', 'conceal']);

        static::assertEquals("\033[7;8mfoo1\033[27;28m", $style->apply('foo1'));

        $style->setEffect('bold');

        static::assertEquals("\033[7;8;1mfoo2\033[27;28;22m", $style->apply('foo2'));

        $style->unsetEffect('reverse');

        static::assertEquals("\033[8;1mfoo3\033[28;22m", $style->apply('foo3'));

        $style->setEffect('bold');

        static::assertEquals("\033[8;1mfoo4\033[28;22m", $style->apply('foo4'));

        $style->setEffects(['bold']);

        static::assertEquals("\033[1mfoo5\033[22m", $style->apply('foo5'));

        $style->setEffects([]);

        $bold = ['set' => 1, 'unset' => 22];

        $style->setEffect($bold);

        static::assertEquals("\033[1mfoo6\033[22m", $style->apply('foo6'));

        $style->unsetEffect($bold);

        static::assertEquals('foo6', $style->apply('foo6'));
    }

    public function testSetEffectToThrowExceptionOnInvalidValue(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected array or string; received [stdClass].');

        $style = $this->getStyleInstance();
        $style->setEffect(new stdClass());
    }

    public function testSetEffectToThrowExceptionOnNotExistEffect(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid effect specified: [foo]. Expected one of [none, bold, dark, italic, underscore, blink, blink_fast, reverse, conceal, crossed_out, double_underline, curly_underline, overline].');

        $style = $this->getStyleInstance();
        $style->setEffect('foo');
    }

    /**
     * @dataProvider provideSetEffectToThrowExceptionOnInvalidEffectArrayCases
     */
    public function testSetEffectToThrowExceptionOnInvalidEffectArray(string $key): void
    {
        $this->expectException(InvalidArgumentException::class);

        $effect = [$key => 4];

        $this->expectExceptionMessage(\Safe\sprintf('Provided array is missing [set] or [unset] key; The array must look like [\'set\' => int, \'unset\' => int]; received [%s].', var_export($effect, true)));

        $style = $this->getStyleInstance();
        $style->setEffect($effect);
    }

    /**
     * @psalm-return iterable<array-key, array<array-key, string>>
     *
     * @return iterable<int, array<int, string>>
     */
    public static function provideSetEffectToThrowExceptionOnInvalidEffectArrayCases(): iterable
    {
        return [
            ['set'],
            ['unset'],
        ];
    }

    public function testUnsetEffectToThrowExceptionOnInvalidValue(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected array or string; received [stdClass].');

        $style = $this->getStyleInstance();
        $style->unsetEffect(new stdClass());
    }

    public function testUnsetEffectToThrowExceptionOnNotExistEffect(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid effect specified: [foo]. Expected one of [none, bold, dark, italic, underscore, blink, blink_fast, reverse, conceal, crossed_out, double_underline, curly_underline, overline].');

        $style = $this->getStyleInstance();
        $style->unsetEffect('foo');
    }

    abstract protected function getStyleInstance(): StyleContract;

    /**
     * {@inheritdoc}
     */
    abstract protected function expectException(string $exception);

    /**
     * {@inheritdoc}
     */
    abstract protected function expectExceptionMessage(string $message): void;
}
