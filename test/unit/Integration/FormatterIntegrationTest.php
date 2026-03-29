<?php

declare(strict_types=1);

namespace Horde\PhpConfigFile\Test\Unit\Integration;

use Horde\PhpConfigFile\PhpConfigFile;
use Horde\PhpConfigFile\LiteralCode;
use Horde\PhpConfigFile\Formatter\ClassicHordeFormatter;
use Horde\PhpConfigFile\Formatter\ModernArrayFormatter;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversNothing;

use function file_get_contents;
use function unlink;
use function sys_get_temp_dir;
use function uniqid;

#[CoversNothing]
class FormatterIntegrationTest extends TestCase
{
    private function getTempFile(): string
    {
        return sys_get_temp_dir() . '/phpconfigfile_test_' . uniqid() . '.php';
    }

    public function testWriteWithDefaultFormatterUsesModernFormat(): void
    {
        $path = $this->getTempFile();
        $file = new PhpConfigFile($path);
        $file->writeConfigFile(['config' => ['key' => 'value']]);

        $content = file_get_contents($path);
        $this->assertStringContainsString("'key' => 'value'", $content);
        $this->assertStringNotContainsString("\$config['key']", $content);
        unlink($path);
    }

    public function testWriteWithConstructorFormatterUsesClassicFormat(): void
    {
        $path = $this->getTempFile();
        $file = new PhpConfigFile(
            $path,
            formatter: new ClassicHordeFormatter()
        );
        $file->writeConfigFile(['conf' => ['key' => 'value']]);

        $content = file_get_contents($path);
        $this->assertStringContainsString("\$conf['key'] = 'value';", $content);
        unlink($path);
    }

    public function testWriteWithMethodFormatterOverridesConstructor(): void
    {
        $path = $this->getTempFile();
        // Constructor has modern formatter
        $file = new PhpConfigFile(
            $path,
            formatter: new ModernArrayFormatter()
        );
        // Method call overrides with classic formatter
        $file->writeConfigFile(
            ['conf' => ['key' => 'value']],
            new ClassicHordeFormatter()
        );

        $content = file_get_contents($path);
        $this->assertStringContainsString("\$conf['key'] = 'value';", $content);
        $this->assertStringNotContainsString("'key' => 'value'", $content);
        unlink($path);
    }

    public function testRoundTripClassicFormat(): void
    {
        $path = $this->getTempFile();
        $formatter = new ClassicHordeFormatter();
        $file = new PhpConfigFile($path, formatter: $formatter);

        $original = [
            'conf' => [
                'sql' => [
                    'host' => 'localhost',
                    'port' => 3306,
                ],
                'enabled' => true,
            ],
        ];

        $file->writeConfigFile($original);
        $file->readConfigFile();
        $parsed = $file->parseContent();

        $this->assertEquals('localhost', $parsed['conf']['sql']['host']);
        $this->assertEquals(3306, $parsed['conf']['sql']['port']);
        $this->assertTrue($parsed['conf']['enabled']);

        unlink($path);
    }

    public function testRoundTripModernFormat(): void
    {
        $path = $this->getTempFile();
        $file = new PhpConfigFile($path);

        $original = [
            'config' => [
                'key1' => 'value1',
                'key2' => 'value2',
            ],
        ];

        $file->writeConfigFile($original);
        $file->readConfigFile();
        $parsed = $file->parseContent();

        $this->assertEquals('value1', $parsed['config']['key1']);
        $this->assertEquals('value2', $parsed['config']['key2']);

        unlink($path);
    }

    public function testRoundTripWithLiteralCode(): void
    {
        $path = $this->getTempFile();
        $formatter = new ClassicHordeFormatter();
        $file = new PhpConfigFile($path, formatter: $formatter);

        // Define constant for test
        if (!defined('TEST_CONSTANT')) {
            define('TEST_CONSTANT', 42);
        }

        $original = [
            'conf' => [
                'value' => new LiteralCode('TEST_CONSTANT'),
            ],
        ];

        $file->writeConfigFile($original);
        $file->readConfigFile();
        $parsed = $file->parseContent();

        $this->assertEquals(42, $parsed['conf']['value']);

        unlink($path);
    }

    public function testPreservesContentBeforeHeader(): void
    {
        $path = $this->getTempFile();

        // Write initial content with pre-header section
        file_put_contents($path, "<?php\n// Pre-header comment\n\$prevar = 'preserved';\n/* BEGIN */\n\$old = 'data';\n/* END */\n");

        $file = new PhpConfigFile(
            $path,
            header: '/* BEGIN */',
            footer: '/* END */',
            formatter: new ClassicHordeFormatter()
        );

        $file->readConfigFile();
        $file->writeConfigFile(['conf' => ['key' => 'value']]);

        $content = file_get_contents($path);
        $this->assertStringContainsString('// Pre-header comment', $content);
        $this->assertStringContainsString("\$prevar = 'preserved';", $content);
        $this->assertStringContainsString("\$conf['key'] = 'value';", $content);

        unlink($path);
    }

    public function testPreservesContentAfterFooter(): void
    {
        $path = $this->getTempFile();
        $file = new PhpConfigFile(
            $path,
            header: '/* BEGIN */',
            footer: '/* END */',
            formatter: new ClassicHordeFormatter()
        );

        // Write initial content with footer content
        file_put_contents(
            $path,
            "<?php\n/* BEGIN */\n/* END */\n// Post-footer comment\n\$postvar = 'preserved';\n"
        );

        $file->readConfigFile();
        $file->writeConfigFile(['conf' => ['key' => 'value']]);

        $content = file_get_contents($path);
        $this->assertStringContainsString('// Post-footer comment', $content);
        $this->assertStringContainsString("\$postvar = 'preserved';", $content);
        $this->assertStringContainsString("\$conf['key'] = 'value';", $content);

        unlink($path);
    }

    public function testAlphabeticalOrderingInOutput(): void
    {
        $path = $this->getTempFile();
        $file = new PhpConfigFile($path, formatter: new ClassicHordeFormatter());

        $config = [
            'conf' => [
                'z_last' => 'last',
                'a_first' => 'first',
                'm_middle' => 'middle',
            ],
        ];

        $file->writeConfigFile($config);
        $content = file_get_contents($path);

        // Verify alphabetical order
        $posA = strpos($content, "\$conf['a_first']");
        $posM = strpos($content, "\$conf['m_middle']");
        $posZ = strpos($content, "\$conf['z_last']");

        $this->assertLessThan($posM, $posA);
        $this->assertLessThan($posZ, $posM);

        unlink($path);
    }

    public function testMultipleWritesWithSameFormatter(): void
    {
        $path = $this->getTempFile();
        $file = new PhpConfigFile($path, formatter: new ClassicHordeFormatter());

        $file->writeConfigFile(['conf' => ['key1' => 'value1']]);
        $content1 = file_get_contents($path);
        $this->assertStringContainsString("\$conf['key1'] = 'value1';", $content1);

        // Second write with different data
        $file->writeConfigFile(['conf' => ['key2' => 'value2']]);
        $content2 = file_get_contents($path);
        $this->assertStringContainsString("\$conf['key2'] = 'value2';", $content2);
        $this->assertStringNotContainsString('key1', $content2);

        unlink($path);
    }

    public function testSwitchFormatterBetweenWrites(): void
    {
        $path = $this->getTempFile();
        $file = new PhpConfigFile($path);

        // Write with classic format
        $file->writeConfigFile(['conf' => ['key' => 'value']], new ClassicHordeFormatter());
        $content1 = file_get_contents($path);
        $this->assertStringContainsString("\$conf['key'] = 'value';", $content1);

        // Write with modern format
        $file->writeConfigFile(['config' => ['key' => 'value']], new ModernArrayFormatter());
        $content2 = file_get_contents($path);
        $this->assertStringContainsString("'key' => 'value'", $content2);
        $this->assertStringNotContainsString("\$config['key']", $content2);

        unlink($path);
    }
}
