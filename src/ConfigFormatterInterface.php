<?php

declare(strict_types=1);

/**
 * Copyright 2013-2026 The Horde Project (http://www.horde.org/)
 *
 * See the enclosed file LICENSE for license information (LGPL). If you
 * did not receive this file, see http://www.horde.org/licenses/lgpl21.
 */

namespace Horde\PhpConfigFile;

/**
 * Interface for configuration file formatters
 *
 * Formatters convert configuration arrays into PHP code strings.
 */
interface ConfigFormatterInterface
{
    /**
     * Format a configuration array into PHP code string
     *
     * @param array<string, mixed> $config Configuration data to format
     * @return string PHP code (without opening <?php tag)
     */
    public function format(array $config): string;
}
