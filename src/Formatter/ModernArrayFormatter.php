<?php

declare(strict_types=1);

/**
 * Copyright 2013-2026 The Horde Project (http://www.horde.org/)
 *
 * See the enclosed file LICENSE for license information (LGPL). If you
 * did not receive this file, see http://www.horde.org/licenses/lgpl21.
 */

namespace Horde\PhpConfigFile\Formatter;

use Horde\PhpConfigFile\ConfigFormatterInterface;

use function var_export;
use function is_array;

/**
 * Modern PHP array formatter using var_export()
 *
 * Outputs configuration as modern PHP array syntax:
 * $config = ['key' => 'value'];
 *
 * This is the default formatter, maintaining backward compatibility
 * with the original PhpConfigFile behavior.
 */
class ModernArrayFormatter implements ConfigFormatterInterface
{
    public function format(array $config): string
    {
        $output = '';
        foreach ($config as $key => $value) {
            if (is_array($value)) {
                $output .= '$' . $key . ' = ' . var_export($value, true) . ";\n";
            } else {
                // Scalar values as string (original behavior)
                $output .= '$' . $key . " = '" . $value . "';\n";
            }
        }
        return $output;
    }
}
