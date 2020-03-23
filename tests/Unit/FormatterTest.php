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
use ReflectionMethod;
use Testomat\TerminalColour\Formatter;
use Testomat\TerminalColour\Style;
use Testomat\TerminalColour\Tests\Fixture\TableCell;

/**
 * @internal
 *
 * @covers \Testomat\TerminalColour\Stack
 *
 * @small
 */
final class FormatterTest extends TestCase
{
    public function testEmptyTag(): void
    {
        $formatter = new Formatter(true);

        self::assertEquals('foo<>bar', $formatter->format('foo<>bar'));
    }

    public function testLGCharEscaping(): void
    {
        $formatter = new Formatter(true);

        self::assertEquals('foo<bar', $formatter->format('foo\\<bar'));
        self::assertEquals('foo << bar', $formatter->format('foo << bar'));
        self::assertEquals('foo << bar \\', $formatter->format('foo << bar \\'));
        self::assertEquals("foo << \033[32mbar \\ baz\033[39m \\", $formatter->format('foo << <info>bar \\ baz</info> \\'));
        self::assertEquals('<info>some info</info>', $formatter->format('\\<info>some info\\</info>'));
        self::assertEquals('\\<info>some info\\</info>', Formatter::escape('<info>some info</info>'));

        self::assertEquals(
            "\033[33mNarrowspark\\Component\\Console does work very well!\033[39m",
            $formatter->format('<comment>Narrowspark\Component\Console does work very well!</comment>')
        );
    }

    public function testBundledStyles(): void
    {
        $formatter = new Formatter(true);

        self::assertTrue($formatter->hasStyle('error'));
        self::assertTrue($formatter->hasStyle('info'));
        self::assertTrue($formatter->hasStyle('comment'));
        self::assertTrue($formatter->hasStyle('question'));

        self::assertEquals(
            "\033[37;41msome error\033[39;49m",
            $formatter->format('<error>some error</error>')
        );
        self::assertEquals(
            "\033[32msome info\033[39m",
            $formatter->format('<info>some info</info>')
        );
        self::assertEquals(
            "\033[33msome comment\033[39m",
            $formatter->format('<comment>some comment</comment>')
        );
        self::assertEquals(
            "\033[30;46msome question\033[39;49m",
            $formatter->format('<question>some question</question>')
        );
    }

    public function testNestedStyles(): void
    {
        $formatter = new Formatter(true);

        self::assertEquals(
            "\033[37;41msome \033[39;49m\033[32msome info\033[39m\033[37;41m error\033[39;49m",
            $formatter->format('<error>some <info>some info</info> error</error>')
        );
    }

    public function testAdjacentStyles(): void
    {
        $formatter = new Formatter(true);

        self::assertEquals(
            "\033[37;41msome error\033[39;49m\033[32msome info\033[39m",
            $formatter->format('<error>some error</error><info>some info</info>')
        );
    }

    public function testStyleMatchingNotGreedy(): void
    {
        $formatter = new Formatter(true);

        self::assertEquals(
            "(\033[32m>=2.0,<2.3\033[39m)",
            $formatter->format('(<info>>=2.0,<2.3</info>)')
        );
    }

    public function testStyleEscaping(): void
    {
        $formatter = new Formatter(true);

        self::assertEquals(
            "(\033[32mz>=2.0,<<<a2.3\\\033[39m)",
            $formatter->format('(<info>' . Formatter::escape('z>=2.0,<\\<<a2.3\\') . '</info>)')
        );

        self::assertEquals(
            "\033[32m<error>some error</error>\033[39m",
            $formatter->format('<info>' . Formatter::escape('<error>some error</error>') . '</info>')
        );
    }

    public function testDeepNestedStyles(): void
    {
        $formatter = new Formatter(true);

        self::assertEquals(
            "\033[37;41merror\033[39;49m\033[32minfo\033[39m\033[33mcomment\033[39m\033[37;41merror\033[39;49m",
            $formatter->format('<error>error<info>info<comment>comment</info>error</error>')
        );
    }

    public function testNewStyle(): void
    {
        $formatter = new Formatter(true);

        $style = new Style('blue', 'white');
        $formatter->setStyle('test', $style);

        self::assertEquals($style, $formatter->getStyle('test'));
        self::assertNotEquals($style, $formatter->getStyle('info'));

        $style = new Style('blue', 'white');
        $formatter->setStyle('b', $style);

        self::assertEquals("\033[34;47msome \033[39;49m\033[34;47mcustom\033[39;49m\033[34;47m msg\033[39;49m", $formatter->format('<test>some <b>custom</b> msg</test>'));
    }

    public function testRedefineStyle(): void
    {
        $formatter = new Formatter(true);

        $style = new Style('blue', 'white');
        $formatter->setStyle('info', $style);

        self::assertEquals("\033[34;47msome custom msg\033[39;49m", $formatter->format('<info>some custom msg</info>'));
    }

    public function testInlineStyle(): void
    {
        $formatter = new Formatter(true);

        self::assertEquals("\033[34;41msome text\033[39;49m", $formatter->format('<fg=blue;bg=red>some text</>'));
        self::assertEquals("\033[34;41msome text\033[39;49m", $formatter->format('<fg=blue;bg=red>some text</fg=blue;bg=red>'));
    }

    /**
     * @dataProvider provideInlineStyleOptionsCases
     */
    public function testInlineStyleOptions(string $tag, ?string $expected = null, ?string $input = null): void
    {
        $styleString = substr($tag, 1, -1);
        $formatter = new Formatter(true);

        $method = new ReflectionMethod($formatter, 'createStyleFromString');
        $method->setAccessible(true);

        $result = $method->invoke($formatter, $styleString);

        if ($expected === null) {
            self::assertNull($result);

            $expected = $tag . $input . '</' . $styleString . '>';

            self::assertSame($expected, $formatter->format($expected));
        } else {
            /* @var Style $result */
            self::assertInstanceOf(Style::class, $result);
            self::assertSame($expected, $formatter->format($tag . $input . '</>'));
            self::assertSame($expected, $formatter->format($tag . $input . '</' . $styleString . '>'));
        }
    }

    /**
     * @return iterable<string>
     */
    public static function provideInlineStyleOptionsCases(): iterable
    {
        return [
            ['<unknown=_unknown_>'],
            ['<unknown=_unknown_;a=1;b>'],
            ['<fg=green;>', "\033[32m[test]\033[39m", '[test]'],
            ['<fg=green;bg=blue;>', "\033[32;44ma\033[39;49m", 'a'],
            ['<fg=green;options=bold>', "\033[32;1mb\033[39;22m", 'b'],
            ['<fg=green;options=reverse;>', "\033[32;7m<a>\033[39;27m", '<a>'],
            ['<fg=green;options=bold,underscore>', "\033[32;1;4mz\033[39;22;24m", 'z'],
            ['<fg=green;options=bold,underscore,reverse;>', "\033[32;1;4;7md\033[39;22;24;27m", 'd'],
        ];
    }

    /**
     * @return iterable<string>
     */
    public function provideInlineStyleTagsWithUnknownOptions(): iterable
    {
        return [
            ['<options=abc;>', 'abc'],
            ['<options=abc,def;>', 'abc'],
            ['<fg=green;options=xyz;>', 'xyz'],
            ['<fg=green;options=efg,abc>', 'efg'],
        ];
    }

    public function testNonStyleTag(): void
    {
        $formatter = new Formatter(true);

        self::assertEquals("\033[32msome \033[39m\033[32m<tag>\033[39m\033[32m \033[39m\033[32m<setting=value>\033[39m\033[32m styled \033[39m\033[32m<p>\033[39m\033[32msingle-char tag\033[39m\033[32m</p>\033[39m", $formatter->format('<info>some <tag> <setting=value> styled <p>single-char tag</p></info>'));
    }

    public function testFormatLongString(): void
    {
        $formatter = new Formatter(true);
        $long = str_repeat('\\', 14000);
        self::assertEquals("\033[37;41msome error\033[39;49m" . $long, $formatter->format('<error>some error</error>' . $long));
    }

    public function testFormatToStringObject(): void
    {
        $formatter = new Formatter(false);
        self::assertEquals(
            'some info',
            $formatter->format((string) new TableCell())
        );
    }

    public function testFormatterHasStyles(): void
    {
        $formatter = new Formatter(false);

        self::assertTrue($formatter->hasStyle('error'));
        self::assertTrue($formatter->hasStyle('info'));
        self::assertTrue($formatter->hasStyle('comment'));
        self::assertTrue($formatter->hasStyle('question'));
    }

    /**
     * @dataProvider provideNotDecoratedFormatterCases
     */
    public function testNotDecoratedFormatter(
        string $input,
        string $expectedNonDecoratedOutput,
        string $expectedDecoratedOutput,
        string $terminalEmulator = 'foo'
    ): void {
        $prevTerminalEmulator = getenv('TERMINAL_EMULATOR');
        putenv('TERMINAL_EMULATOR=' . $terminalEmulator);

        try {
            self::assertEquals($expectedDecoratedOutput, (new Formatter(true))->format($input));
            self::assertEquals($expectedNonDecoratedOutput, (new Formatter(false))->format($input));
        } finally {
            putenv('TERMINAL_EMULATOR' . ($prevTerminalEmulator ? "={$prevTerminalEmulator}" : ''));
        }
    }

    public static function provideNotDecoratedFormatterCases(): iterable
    {
        return [
            ['<error>some error</error>', 'some error', "\033[37;41msome error\033[39;49m"],
            ['<info>some info</info>', 'some info', "\033[32msome info\033[39m"],
            ['<comment>some comment</comment>', 'some comment', "\033[33msome comment\033[39m"],
            ['<question>some question</question>', 'some question', "\033[30;46msome question\033[39;49m"],
            ['<fg=red>some text with inline style</>', 'some text with inline style', "\033[31msome text with inline style\033[39m"],
            ['<href=idea://open/?file=/path/SomeFile.php&line=12>some URL</>', 'some URL', "\033]8;;idea://open/?file=/path/SomeFile.php&line=12\033\\some URL\033]8;;\033\\"],
            ['<href=idea://open/?file=/path/SomeFile.php&line=12>some URL</>', 'some URL', 'some URL', 'JetBrains-JediTerm'],
        ];
    }

    public function testContentWithLineBreaks(): void
    {
        $formatter = new Formatter(true);

        self::assertEquals(<<<EOF
\033[32m
some text\033[39m
EOF
            , $formatter->format(
                <<<'EOF'
<info>
some text</info>
EOF
            ));

        self::assertEquals(<<<EOF
\033[32msome text
\033[39m
EOF
            , $formatter->format(
                <<<'EOF'
<info>some text
</info>
EOF
            ));

        self::assertEquals(<<<EOF
\033[32m
some text
\033[39m
EOF
            , $formatter->format(
                <<<'EOF'
<info>
some text
</info>
EOF
            ));

        self::assertEquals(<<<EOF
\033[32m
some text
more text
\033[39m
EOF
            , $formatter->format(
                <<<'EOF'
<info>
some text
more text
</info>
EOF
            ));
    }

    public function testFormatAndWrap(): void
    {
        $formatter = new Formatter(true);

        self::assertSame("fo\no\e[37;41mb\e[39;49m\n\e[37;41mar\e[39;49m\nba\nz", $formatter->formatAndWrap('foo<error>bar</error> baz', 2));
        self::assertSame("pr\ne \e[37;41m\e[39;49m\n\e[37;41mfo\e[39;49m\n\e[37;41mo \e[39;49m\n\e[37;41mba\e[39;49m\n\e[37;41mr \e[39;49m\n\e[37;41mba\e[39;49m\n\e[37;41mz\e[39;49m \npo\nst", $formatter->formatAndWrap('pre <error>foo bar baz</error> post', 2));
        self::assertSame("pre\e[37;41m\e[39;49m\n\e[37;41mfoo\e[39;49m\n\e[37;41mbar\e[39;49m\n\e[37;41mbaz\e[39;49m\npos\nt", $formatter->formatAndWrap('pre <error>foo bar baz</error> post', 3));
        self::assertSame("pre \e[37;41m\e[39;49m\n\e[37;41mfoo \e[39;49m\n\e[37;41mbar \e[39;49m\n\e[37;41mbaz\e[39;49m \npost", $formatter->formatAndWrap('pre <error>foo bar baz</error> post', 4));
        self::assertSame("pre \e[37;41mf\e[39;49m\n\e[37;41moo ba\e[39;49m\n\e[37;41mr baz\e[39;49m\npost", $formatter->formatAndWrap('pre <error>foo bar baz</error> post', 5));
        self::assertSame("Lore\nm \e[37;41mip\e[39;49m\n\e[37;41msum\e[39;49m \ndolo\nr \e[32msi\e[39m\n\e[32mt\e[39m am\net", $formatter->formatAndWrap('Lorem <error>ipsum</error> dolor <info>sit</info> amet', 4));
        self::assertSame("Lorem \e[37;41mip\e[39;49m\n\e[37;41msum\e[39;49m dolo\nr \e[32msit\e[39m am\net", $formatter->formatAndWrap('Lorem <error>ipsum</error> dolor <info>sit</info> amet', 8));
        self::assertSame("Lorem \e[37;41mipsum\e[39;49m dolor \e[32m\e[39m\n\e[32msit\e[39m, \e[37;41mamet\e[39;49m et \e[32mlauda\e[39m\n\e[32mntium\e[39m architecto", $formatter->formatAndWrap('Lorem <error>ipsum</error> dolor <info>sit</info>, <error>amet</error> et <info>laudantium</info> architecto', 18));

        $formatter = new Formatter();

        self::assertSame("fo\nob\nar\nba\nz", $formatter->formatAndWrap('foo<error>bar</error> baz', 2));
        self::assertSame("pr\ne \nfo\no \nba\nr \nba\nz \npo\nst", $formatter->formatAndWrap('pre <error>foo bar baz</error> post', 2));
        self::assertSame("pre\nfoo\nbar\nbaz\npos\nt", $formatter->formatAndWrap('pre <error>foo bar baz</error> post', 3));
        self::assertSame("pre \nfoo \nbar \nbaz \npost", $formatter->formatAndWrap('pre <error>foo bar baz</error> post', 4));
        self::assertSame("pre f\noo ba\nr baz\npost", $formatter->formatAndWrap('pre <error>foo bar baz</error> post', 5));
    }
}
