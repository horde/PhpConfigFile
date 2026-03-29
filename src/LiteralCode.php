<?php

declare(strict_types=1);

/**
 * Copyright 2013-2026 The Horde Project (http://www.horde.org/)
 *
 * See the enclosed file LICENSE for license information (LGPL). If you
 * did not receive this file, see http://www.horde.org/licenses/lgpl21.
 */

namespace Horde\PhpConfigFile;

use Stringable;

/**
 * Wrapper for literal PHP code expressions that should be output as-is
 *
 * Use this when you need to output PHP constants, class constants, or other
 * expressions that shouldn't be quoted or escaped.
 *
 * @example
 * $config = [
 *     'conf' => [
 *         'log_level' => new LiteralCode('Horde_Log::DEBUG'),
 *         'timeout' => new LiteralCode('60 * 60'), // 1 hour
 *     ],
 * ];
 * // Output: $conf['log_level'] = Horde_Log::DEBUG;
 * //         $conf['timeout'] = 60 * 60;
 */
final readonly class LiteralCode implements Stringable
{
    public function __construct(
        private string $code,
    ) {}

    public function __toString(): string
    {
        return $this->code;
    }

    public function getCode(): string
    {
        return $this->code;
    }
}
