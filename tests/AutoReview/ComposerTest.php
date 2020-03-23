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

namespace Narrowspark\Library\Tests\AutoReview;

use PHPUnit\Framework\TestCase;
use Testomat\TerminalColour\Formatter;

/**
 * @internal
 *
 * @coversNothing
 * @group auto-review
 * @group covers-nothing
 *
 * @medium
 */
final class ComposerTest extends TestCase
{
    public function testBranchAlias(): void
    {
        /** @var array<string, mixed> $composerJson */
        $composerJson = json_decode(
            (string) file_get_contents(__DIR__ . '/../../composer.json'),
            true,
            512,
            \JSON_THROW_ON_ERROR
        );

        if (! isset($composerJson['extra']['branch-alias'])) {
            /** @psalm-suppress InternalMethod */
            $this->addToAssertionCount(1); // composer.json doesn't contain branch alias, all good!

            return;
        }

        self::assertSame(
            ['dev-master' => $this->convertAppVersionToAliasedVersion(Formatter::VERSION)],
            $composerJson['extra']['branch-alias']
        );
    }

    private function convertAppVersionToAliasedVersion(string $version): string
    {
        $parts = explode('.', $version, 3);

        return sprintf('%d.%d-dev', $parts[0], $parts[1]);
    }
}
