<?php

declare(strict_types=1);

/**
 * Copyright (c) 2020 Daniel Bannert
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source code.
 *
 * @see https://github.com/narrowspark/php-library-template
 */

namespace Narrowspark\Library;

final class Example
{
    private $name;

    private function __construct(string $name)
    {
        $this->name = $name;
    }

    public function name(): string
    {
        return $this->name;
    }

    public static function fromName(string $name): self
    {
        return new self($name);
    }
}
