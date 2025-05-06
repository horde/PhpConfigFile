<?php

declare(strict_types=1);

namespace Horde\PhpConfigFile;

use Stringable;
use RuntimeException;
use InvalidArgumentException;
use function file_exists;
use function file_get_contents;
use function file_put_contents;
use function strpos;
use function substr;
use function str_ends_with;
use function str_starts_with;
use function trim;
use function ltrim;
use function get_defined_vars;
use function eval;
use function var_export;
use function in_array;

/**
 * Read and write a horde-like config file
 * One or multiple deep arrays between header and footer comments. The original content before the header and after the footer will be preserved.
 */
class PhpConfigFile
{
    private string $contentBeforeHeader = '';
    private string $contentAfterFooter = '';
    private string $contentBetweenHeaderAndFooter = '';
    private string $content = '';
    private string $untrustedContent = '';

    public function __construct(
        public readonly string|Stringable $configFilePath,
        public readonly string|Stringable $header = '/* This file is auto-generated. Do not edit anything below this line! */',
        public readonly string|Stringable $footer = '/* This file is auto-generated. Do not edit anything above this line! */',
    ) {}

    public function getContent(): string
    {
        return $this->content;
    }
    public function getContentBeforeHeader(): string
    {
        return $this->contentBeforeHeader;
    }
    public function getContentAfterFooter(): string
    {
        return $this->contentAfterFooter;
    }
    public function getContentBetweenHeaderAndFooter(): string
    {
        return $this->contentBetweenHeaderAndFooter;
    }

    public function readConfigFile()
    {
        // Read the config file and parse it into an array
        if (!file_exists($this->configFilePath)) {
            throw new \RuntimeException("Config file does not exist: {$this->configFilePath}");
        }
        $configContent = file_get_contents($this->configFilePath);
        if ($configContent === false) {
            throw new \RuntimeException("Failed to read config file: {$this->configFilePath}");
        }
        // Strip leading and trailing php tags
        $configContent = trim($configContent);
        if (str_starts_with($configContent, '<?php')) {
            $configContent = substr($configContent, 5);
        }
        $configContent = ltrim($configContent);
        if (str_ends_with($configContent, '?>')) {
            $configContent = substr($configContent, 0, -2);
        }
        $this->content = $configContent;
        // Get everything before $header
        $headerStartPos = strpos($configContent, $this->header);
        $headerEndPos = 0;
        $this->contentBeforeHeader = '';
        if ($headerStartPos === false) {
            $headerStartPos = 0;
        } else {
            $headerEndPos = $headerStartPos + strlen($this->header);
            $this->contentBeforeHeader = substr($configContent, 0, $headerStartPos);
        }

        // Get everything after $footer
        $footerStartPos = strpos($configContent, $this->footer);
        if ($footerStartPos === false) {
            $this->contentAfterFooter = '';
            $footerStartPos = strlen($configContent);
            $footerEndPos = $footerStartPos;
        } else {
            $footerEndPos = $footerStartPos + strlen($this->footer);
            $this->contentAfterFooter = substr($configContent, $footerEndPos);
        }
        $this->contentBetweenHeaderAndFooter = substr($configContent, $headerEndPos, $footerStartPos - $headerEndPos);
        // Parse the content into an array (assuming it's a PHP array)
    }


    public function parseContent(string $area = 'content'): array
    {
        // TODO: Ensure to prevent any output from eval
        if (in_array($area, ['contentBetweenHeaderAndFooter', 'contentBeforeHeader', 'contentAfterFooter', 'content'])) {
            // Don't pollute the namespace
            $this->untrustedContent = $this->{$area};
            unset($area);
            eval($this->untrustedContent);
            $res = get_defined_vars();
            $this->untrustedContent = '';
            return $res;
        }
        throw new \InvalidArgumentException("Invalid area to parse from: $area");
    }

    public function writeConfigFile(array $config): void
    {
        // Convert the array back to a string
        $configContent = "<?php\n" .
        $this->contentBeforeHeader . "\n" .
        $this->header . "\n";
        foreach ($config as $key => $value) {
            if (is_array($value)) {
                $configContent .= '$' . $key . ' = ' . var_export($value, true) . ";\n";
            } else {
                $configContent .= '$' . $key . ' = \'' . $value . "';\n";
            }
        }
        $configContent .= "\n" .

        $this->footer . "\n" .
        $this->contentAfterFooter;
        // Write the content back to the file
        file_put_contents($this->configFilePath, $configContent);
    }
}
