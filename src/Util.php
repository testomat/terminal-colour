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

use Testomat\TerminalColour\Exception\InvalidArgumentException;

final class Util
{
    private function __construct()
    {
    }

    public static function supportsColor($stream = null): int
    {
        $colorSupport = 0;

        if ($stream === false) {
            return $colorSupport;
        }

        $stream = $stream ?? \STDOUT;

        if (! \is_resource($stream)) {
            throw new InvalidArgumentException(
                \Safe\sprintf('Expecting parameter 1 to be resource, [%s] given', \is_object($stream) ? \get_class($stream) : \gettype($stream))
            );
        }

        if (self::streamHasColorSupport($stream)) {
            $colorSupport = 16;

            if (self::checkEnvVariable('TERM', '256color')
                || self::checkEnvVariable('DOCKER_TERM', '256color')) {
                $colorSupport = 255;
            }

            if (self::checkEnvVariable('COLORTERM', 'truecolor')) {
                $colorSupport = 65535;
            }
        }

        return $colorSupport;
    }

    /**
     * Returns true if the output stream supports colors.
     *
     * Colorization is disabled if not supported by the stream:
     *
     * This is tricky on Windows, because Cygwin, Msys2 etc emulate pseudo
     * terminals via named pipes, so we can only check the environment.
     *
     * Reference: Composer\XdebugHandler\Process::supportsColor
     * https://github.com/composer/xdebug-handler
     *
     * Reference: Symfony\Component\Console\Output\StreamOutput::hasColorSupport()
     * https://github.com/symfony/console
     *
     * @param null|bool|resource $output A valid CLI output stream
     *
     * @return bool true if the stream supports colorization, false otherwise
     */
    private static function streamHasColorSupport($output): bool
    {
        if ('Hyper' === getenv('TERM_PROGRAM')) {
            return true;
        }

        if (\defined('PHP_WINDOWS_VERSION_BUILD')) {
            return (\function_exists('sapi_windows_vt100_support')
                    && \Safe\sapi_windows_vt100_support($output))
                || false !== getenv('ANSICON')
                || 'ON' === getenv('ConEmuANSI')
                || 'xterm' === getenv('TERM');
        }

        if (\function_exists('stream_isatty')) {
            return \Safe\stream_isatty($output);
        }

        if (\function_exists('posix_isatty')) {
            return posix_isatty($output);
        }

        $stat = fstat($output);
        // Check if formatted mode is S_IFCHR
        return $stat && 0020000 === ($stat['mode'] & 0170000);
    }

    private static function checkEnvVariable(string $varName, string $checkFor): bool
    {
        if ($env = getenv($varName)) {
            return strpos($env, $checkFor) !== false;
        }

        return false;
    }
}
