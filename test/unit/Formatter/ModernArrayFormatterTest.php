<?php

declare(strict_types=1);

namespace Horde\PhpConfigFile\Test\Unit\Formatter;

use Horde\PhpConfigFile\Formatter\ModernArrayFormatter;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ModernArrayFormatter::class)]
class ModernArrayFormatterTest extends TestCase
{
    public function testFormatSimpleArray(): void
    {
        $formatter = new ModernArrayFormatter();
        $result = $formatter->format(['key' => 'value']);
        $this->assertStringContainsString("\$key = 'value';", $result);
    }

    public function testFormatNestedArray(): void
    {
        $formatter = new ModernArrayFormatter();
        $config = ['outer' => ['inner' => 'value']];
        $result = $formatter->format($config);
        $this->assertStringContainsString("'inner' => 'value'", $result);
        $this->assertStringContainsString('$outer = ', $result);
    }

    public function testFormatMultipleKeys(): void
    {
        $formatter = new ModernArrayFormatter();
        $config = ['key1' => 'value1', 'key2' => 'value2'];
        $result = $formatter->format($config);
        $this->assertStringContainsString("\$key1 = 'value1';", $result);
        $this->assertStringContainsString("\$key2 = 'value2';", $result);
    }

    public function testFormatArrayWithVarExport(): void
    {
        $formatter = new ModernArrayFormatter();
        $config = ['config' => ['key' => 'value', 'num' => 42]];
        $result = $formatter->format($config);
        $this->assertStringContainsString('array (', $result);
        $this->assertStringContainsString("'key' => 'value'", $result);
    }

    public function testFormatPreservesScalarBehavior(): void
    {
        $formatter = new ModernArrayFormatter();
        $result = $formatter->format(['simple' => 'test']);
        $this->assertEquals("\$simple = 'test';\n", $result);
    }
}
