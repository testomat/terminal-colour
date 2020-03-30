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

use Safe\Exceptions\MiscException;
use Testomat\TerminalColour\Exception\InvalidArgumentException;

final class Util
{
    /**
     * @noRector \Rector\SOLID\Rector\ClassConst\PrivatizeLocalClassConstantRector
     *
     * @var int
     */
    public const NO_COLOR_TERMINAL = 0;

    /**
     * @noRector \Rector\SOLID\Rector\ClassConst\PrivatizeLocalClassConstantRector
     *
     * @var int
     */
    public const COLOR_TERMINAL = 16;

    /**
     * @noRector \Rector\SOLID\Rector\ClassConst\PrivatizeLocalClassConstantRector
     *
     * @var int
     */
    public const COLOR256_TERMINAL = 255;

    /**
     * @noRector \Rector\SOLID\Rector\ClassConst\PrivatizeLocalClassConstantRector
     *
     * @var int
     */
    public const TRUECOLOR_TERMINAL = 65535;

    /**
     * @param null|false|resource|string $stream
     */
    public static function getSupportedColor($stream = null): int
    {
        $colorSupport = self::NO_COLOR_TERMINAL;

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
            $colorSupport = self::COLOR_TERMINAL;

            if (self::checkEnvVariable('TERM', '256color')
                || self::checkEnvVariable('DOCKER_TERM', '256color')) {
                $colorSupport = self::COLOR256_TERMINAL;
            }

            if (self::checkEnvVariable('COLORTERM', 'truecolor')) {
                $colorSupport = self::TRUECOLOR_TERMINAL;
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
     * @param resource $output A valid CLI output stream
     *
     * @return bool true if the stream supports colorization, false otherwise
     */
    private static function streamHasColorSupport($output): bool
    {
        if (getenv('TERM_PROGRAM') === 'Hyper') {
            return true;
        }

        // @codeCoverageIgnoreStart
        if (\defined('PHP_WINDOWS_VERSION_BUILD')) {
            try {
                return (\function_exists('sapi_windows_vt100_support') && \Safe\sapi_windows_vt100_support($output))
                    || getenv('ANSICON') !== false
                    || getenv('ConEmuANSI') === 'ON'
                    || getenv('TERM') === 'xterm';
            } catch (MiscException $exception) {
                return false;
            }
        }

        if (\function_exists('stream_isatty')) {
            /** @noRector \Rector\Renaming\Rector\Function_\RenameFunctionRector */
            return @stream_isatty($output);
        }

        if (\function_exists('posix_isatty')) {
            return @posix_isatty($output);
        }

        $stat = fstat($output);
        // Check if formatted mode is S_IFCHR
        return \is_array($stat) && 0020000 === ($stat['mode'] & 0170000);
        // @codeCoverageIgnoreEnd
    }

    private static function checkEnvVariable(string $varName, string $checkFor): bool
    {
        if (($env = getenv($varName)) !== false) {
            return strpos($env, $checkFor) !== false;
        }

        return false;
    }
}
