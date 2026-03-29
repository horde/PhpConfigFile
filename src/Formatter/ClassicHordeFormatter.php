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
use Horde\PhpConfigFile\LiteralCode;
use InvalidArgumentException;

use function is_array;
use function is_bool;
use function is_int;
use function is_float;
use function is_string;
use function ksort;
use function addslashes;
use function array_shift;
use function get_debug_type;

/**
 * Classic Horde config formatter
 *
 * Outputs configuration in classic Horde format:
 * $conf['key1']['key2'] = 'value';
 *
 * Features:
 * - Alphabetical key sorting for deterministic output
 * - Numeric keys unquoted: $var[0] not $var['0']
 * - LiteralCode support for constants and expressions
 * - Empty arrays produce no output
 * - Only scalar values and LiteralCode supported at leaf level
 */
class ClassicHordeFormatter implements ConfigFormatterInterface
{
    public function format(array $config): string
    {
        // Sort root keys alphabetically
        ksort($config);

        $output = '';
        foreach ($config as $rootKey => $value) {
            $output .= $this->formatValue($rootKey, [], $value);
        }
        return $output;
    }

    /**
     * Recursively format a value into classic Horde notation
     *
     * @param string|int $currentKey Current key being processed
     * @param array<string|int> $keyPath Path of keys leading to this point
     * @param mixed $value Value to format
     * @return string Formatted PHP code lines
     */
    private function formatValue(string|int $currentKey, array $keyPath, mixed $value): string
    {
        $keyPath[] = $currentKey;

        if (is_array($value)) {
            // Sort keys alphabetically for deterministic output
            ksort($value);

            // Recursively process nested arrays
            $output = '';
            foreach ($value as $subKey => $subValue) {
                $output .= $this->formatValue($subKey, $keyPath, $subValue);
            }
            return $output;
        }

        // Leaf value - generate assignment statement
        return $this->formatLeafAssignment($keyPath, $value);
    }

    /**
     * Format a leaf assignment: $var['key1'][2]['key3'] = value;
     *
     * @param array<string|int> $keyPath Full path of keys
     * @param mixed $value Leaf value (scalar or LiteralCode)
     * @return string Formatted assignment statement
     */
    private function formatLeafAssignment(array $keyPath, mixed $value): string
    {
        // Build $var['key1'][2]['key3']...
        $varPath = '$' . array_shift($keyPath);
        foreach ($keyPath as $key) {
            if (is_int($key)) {
                // Numeric keys unquoted
                $varPath .= "[{$key}]";
            } else {
                // String keys quoted and escaped
                $varPath .= "['" . addslashes((string) $key) . "']";
            }
        }

        // Format value based on type
        $formattedValue = $this->formatScalar($value);

        return "{$varPath} = {$formattedValue};\n";
    }

    /**
     * Format a scalar value for PHP output
     *
     * @param mixed $value Value to format
     * @return string Formatted value representation
     * @throws InvalidArgumentException If value is not scalar or LiteralCode
     */
    private function formatScalar(mixed $value): string
    {
        // Handle literal code expressions
        if ($value instanceof LiteralCode) {
            return $value->getCode();
        }

        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }
        if (is_int($value) || is_float($value)) {
            return (string) $value;
        }
        if (is_string($value)) {
            return "'" . addslashes($value) . "'";
        }
        if ($value === null) {
            return 'null';
        }

        // Reject non-scalar values
        throw new InvalidArgumentException(
            'Only scalar values and LiteralCode are supported. Got: ' . get_debug_type($value)
        );
    }
}
