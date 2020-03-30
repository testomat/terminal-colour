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

use Testomat\TerminalColour\Contract\Style as StyleContract;

trait HrefTestTrait
{
    public function testHref(): void
    {
        $prevTerminalEmulator = getenv('TERMINAL_EMULATOR');

        putenv('TERMINAL_EMULATOR');

        $style = $this->getStyleInstance();

        try {
            $style->setHref('idea://open/?file=/path/SomeFile.php&line=12');

            self::assertSame("\e]8;;idea://open/?file=/path/SomeFile.php&line=12\e\\some URL\e]8;;\e\\", $style->apply('some URL'));
        } finally {
            putenv('TERMINAL_EMULATOR' . ($prevTerminalEmulator !== false ? "={$prevTerminalEmulator}" : ''));
        }
    }

    abstract protected function getStyleInstance(): StyleContract;
}
