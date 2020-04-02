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
     * @noRector \Rector\SOLID\Rector\ClassConst\PrivatizeLocalClassConstantRector
     *
     * @var int
     */
    public const STDIN = 0;

    /**
     * @noRector \Rector\SOLID\Rector\ClassConst\PrivatizeLocalClassConstantRector
     *
     * @var int
     */
    public const STDOUT = 1;

    /**
     * @noRector \Rector\SOLID\Rector\ClassConst\PrivatizeLocalClassConstantRector
     *
     * @var int
     */
    public const STDERR = 2;

    /** @var null|int */
    private static $colorCache;

    public static function resetColorCache(): void
    {
        self::$colorCache = null;
    }

    /**
     * Returns if the file descriptor is an interactive terminal or not.
     *
     * Normally, we want to use a resource as a parameter, yet sadly it's not always available,
     * eg when running code in interactive console (`php -a`), STDIN/STDOUT/STDERR constants are not defined.
     *
     * @codeCoverageIgnore
     *
     * @param int|resource $fileDescriptor
     */
    public static function isInteractive($fileDescriptor = self::STDOUT): bool
    {
        if (\is_resource($fileDescriptor)) {
            // These functions require a descriptor that is a real resource, not a numeric ID of it
            /** @noRector \Rector\Renaming\Rector\Function_\RenameFunctionRector */
            if (\function_exists('stream_isatty') && @stream_isatty($fileDescriptor)) {
                return true;
            }

            $stat = @fstat(\STDOUT);
            // Check if formatted mode is S_IFCHR
            return \is_array($stat) && 0020000 === ($stat['mode'] & 0170000);
        }

        return \function_exists('posix_isatty') && @posix_isatty($fileDescriptor);
    }

    /**
     * Returns the number of columns of the terminal.
     *
     * @codeCoverageIgnore
     */
    public static function getNumberOfColumns(): int
    {
        if (! self::isInteractive(\defined('STDIN') ? \STDIN : self::STDIN)) {
            return 80;
        }

        if (\DIRECTORY_SEPARATOR === '\\') {
            return self::getNumberOfColumnsWindows();
        }

        return self::getNumberOfColumnsInteractive();
    }

    /**
     * @param null|false|resource|string $stream
     */
    public static function getSupportedColor($stream = null): int
    {
        if (self::$colorCache !== null) {
            return self::$colorCache;
        }

        $colorSupport = self::NO_COLOR_TERMINAL;

        if ($stream === false || ($stream === null && ! \defined('STDOUT'))) {
            return self::$colorCache = $colorSupport;
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

        return self::$colorCache = $colorSupport;
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
        if (\DIRECTORY_SEPARATOR === '\\') {
            try {
                return (\function_exists('sapi_windows_vt100_support') && \Safe\sapi_windows_vt100_support($output))
                    || getenv('ANSICON') !== false
                    || getenv('ConEmuANSI') === 'ON'
                    || getenv('TERM') === 'xterm';
            } catch (MiscException $exception) {
                return false;
            }
        }

        return self::isInteractive($output);
        // @codeCoverageIgnoreEnd
    }

    private static function checkEnvVariable(string $varName, string $checkFor): bool
    {
        if (($env = getenv($varName)) !== false) {
            return strpos($env, $checkFor) !== false;
        }

        return false;
    }

    /**
     * @codeCoverageIgnore
     */
    private static function getNumberOfColumnsInteractive(): int
    {
        $size = '';
        $shell = false;

        if (\function_exists('shell_exec') && \is_string($exec = shell_exec('stty size')) && $exec !== '') {
            $shell = true;
            $size = $exec;
        }

        /** @noRector Rector\SOLID\Rector\If_\ChangeNestedIfsToEarlyReturnRector */
        if ($shell && \Safe\preg_match('#\d+ (\d+)#', (string) $size, $match) === 1 && (int) $match[1] > 0) {
            return (int) $match[1];
        }

        if (\function_exists('shell_exec') && \is_string($exec = shell_exec('stty')) && $exec !== '') {
            $size = $exec;
        }

        /** @noRector Rector\SOLID\Rector\If_\ChangeNestedIfsToEarlyReturnRector */
        if ($shell && \Safe\preg_match('#columns = (\d+);#', (string) $size, $match) === 1 && (int) $match[1] > 0) {
            return (int) $match[1];
        }

        return 80;
    }

    /**
     * @codeCoverageIgnore
     */
    private static function getNumberOfColumnsWindows(): int
    {
        $ansicon = getenv('ANSICON');
        $columns = 80;

        if (\is_string($ansicon) && \Safe\preg_match('/^(\d+)x\d+ \(\d+x(\d+)\)$/', trim($ansicon), $matches) === 1) {
            $columns = (int) $matches[1];
        } elseif (\function_exists('proc_open')) {
            $process = proc_open(
                'mode CON',
                [
                    1 => ['pipe', 'w'],
                    2 => ['pipe', 'w'],
                ],
                $pipes,
                null,
                null,
                ['suppress_errors' => true]
            );

            if (\is_resource($process)) {
                $info = \Safe\stream_get_contents($pipes[1]);

                \Safe\fclose($pipes[1]);
                \Safe\fclose($pipes[2]);
                proc_close($process);

                if (\Safe\preg_match('/--------+\r?\n.+?(\d+)\r?\n.+?(\d+)\r?\n/', $info, $matches) !== 0) {
                    $columns = (int) $matches[2];
                }
            }
        }

        return $columns - 1;
    }
}
