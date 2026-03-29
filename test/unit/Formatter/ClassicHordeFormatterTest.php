<?php

declare(strict_types=1);

namespace Horde\PhpConfigFile\Test\Unit\Formatter;

use Horde\PhpConfigFile\Formatter\ClassicHordeFormatter;
use Horde\PhpConfigFile\LiteralCode;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use stdClass;

use function strpos;

#[CoversClass(ClassicHordeFormatter::class)]
class ClassicHordeFormatterTest extends TestCase
{
    public function testFormatSimpleString(): void
    {
        $formatter = new ClassicHordeFormatter();
        $result = $formatter->format(['conf' => ['key' => 'value']]);
        $this->assertStringContainsString("\$conf['key'] = 'value';", $result);
    }

    public function testFormatNestedArray(): void
    {
        $formatter = new ClassicHordeFormatter();
        $config = ['conf' => ['sql' => ['host' => 'localhost']]];
        $result = $formatter->format($config);
        $this->assertStringContainsString("\$conf['sql']['host'] = 'localhost';", $result);
    }

    public function testFormatBoolean(): void
    {
        $formatter = new ClassicHordeFormatter();
        $result = $formatter->format(['conf' => ['enabled' => true, 'disabled' => false]]);
        $this->assertStringContainsString("\$conf['enabled'] = true;", $result);
        $this->assertStringContainsString("\$conf['disabled'] = false;", $result);
    }

    public function testFormatInteger(): void
    {
        $formatter = new ClassicHordeFormatter();
        $result = $formatter->format(['conf' => ['port' => 3306]]);
        $this->assertStringContainsString("\$conf['port'] = 3306;", $result);
    }

    public function testFormatFloat(): void
    {
        $formatter = new ClassicHordeFormatter();
        $result = $formatter->format(['conf' => ['ratio' => 1.5]]);
        $this->assertStringContainsString("\$conf['ratio'] = 1.5;", $result);
    }

    public function testFormatNull(): void
    {
        $formatter = new ClassicHordeFormatter();
        $result = $formatter->format(['conf' => ['nullable' => null]]);
        $this->assertStringContainsString("\$conf['nullable'] = null;", $result);
    }

    public function testFormatLiteralCode(): void
    {
        $formatter = new ClassicHordeFormatter();
        $result = $formatter->format(['conf' => ['log_level' => new LiteralCode('Horde_Log::DEBUG')]]);
        $this->assertStringContainsString("\$conf['log_level'] = Horde_Log::DEBUG;", $result);
    }

    public function testFormatLiteralCodeExpression(): void
    {
        $formatter = new ClassicHordeFormatter();
        $result = $formatter->format(['conf' => ['timeout' => new LiteralCode('60 * 60')]]);
        $this->assertStringContainsString("\$conf['timeout'] = 60 * 60;", $result);
    }

    public function testNumericKeysUnquoted(): void
    {
        $formatter = new ClassicHordeFormatter();
        $result = $formatter->format(['items' => [0 => 'first', 1 => 'second']]);
        $this->assertStringContainsString("\$items[0] = 'first';", $result);
        $this->assertStringContainsString("\$items[1] = 'second';", $result);
        $this->assertStringNotContainsString("['0']", $result);
        $this->assertStringNotContainsString("['1']", $result);
    }

    public function testEscapesQuotesInKeys(): void
    {
        $formatter = new ClassicHordeFormatter();
        $result = $formatter->format(['conf' => ["key'with'quotes" => 'value']]);
        $this->assertStringContainsString("key\\'with\\'quotes", $result);
    }

    public function testEscapesQuotesInValues(): void
    {
        $formatter = new ClassicHordeFormatter();
        $result = $formatter->format(['conf' => ['key' => "val'ue"]]);
        $this->assertStringContainsString("val\\'ue", $result);
    }

    public function testEscapesBackslashesInValues(): void
    {
        $formatter = new ClassicHordeFormatter();
        $result = $formatter->format(['conf' => ['path' => 'C:\\Windows\\System']]);
        $this->assertStringContainsString("C:\\\\Windows\\\\System", $result);
    }

    public function testAlphabeticalKeyOrdering(): void
    {
        $formatter = new ClassicHordeFormatter();
        $config = ['conf' => ['z' => 'last', 'a' => 'first', 'm' => 'middle']];
        $result = $formatter->format($config);

        // Extract line positions
        $posA = strpos($result, "\$conf['a']");
        $posM = strpos($result, "\$conf['m']");
        $posZ = strpos($result, "\$conf['z']");

        $this->assertLessThan($posM, $posA, 'Key "a" should come before "m"');
        $this->assertLessThan($posZ, $posM, 'Key "m" should come before "z"');
    }

    public function testAlphabeticalKeyOrderingNested(): void
    {
        $formatter = new ClassicHordeFormatter();
        $config = ['conf' => ['section' => ['z' => 'last', 'a' => 'first']]];
        $result = $formatter->format($config);

        $posA = strpos($result, "\$conf['section']['a']");
        $posZ = strpos($result, "\$conf['section']['z']");

        $this->assertLessThan($posZ, $posA, 'Nested key "a" should come before "z"');
    }

    public function testEmptyArrayProducesNoOutput(): void
    {
        $formatter = new ClassicHordeFormatter();
        $result = $formatter->format(['conf' => ['empty' => []]]);
        $this->assertStringNotContainsString('empty', $result, 'Empty arrays should produce no output');
        $this->assertEquals('', $result);
    }

    public function testNestedEmptyArraysProduceNoOutput(): void
    {
        $formatter = new ClassicHordeFormatter();
        $result = $formatter->format(['conf' => ['section' => ['subsection' => []]]]);
        $this->assertEquals('', $result);
    }

    public function testThrowsExceptionForNonScalarLeafValue(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Only scalar values and LiteralCode are supported');

        $formatter = new ClassicHordeFormatter();
        $formatter->format(['conf' => ['object' => new stdClass()]]);
    }

    public function testDeepNesting(): void
    {
        $formatter = new ClassicHordeFormatter();
        $config = ['conf' => ['l1' => ['l2' => ['l3' => ['l4' => 'deep']]]]];
        $result = $formatter->format($config);
        $this->assertStringContainsString("\$conf['l1']['l2']['l3']['l4'] = 'deep';", $result);
    }

    public function testMixedNumericAndStringKeys(): void
    {
        $formatter = new ClassicHordeFormatter();
        $config = ['conf' => ['items' => [0 => 'zero', 'name' => 'value', 1 => 'one']]];
        $result = $formatter->format($config);

        // Numeric keys come first when sorted (0, 1, then 'name')
        $this->assertStringContainsString("\$conf['items'][0] = 'zero';", $result);
        $this->assertStringContainsString("\$conf['items'][1] = 'one';", $result);
        $this->assertStringContainsString("\$conf['items']['name'] = 'value';", $result);
    }

    public function testRealWorldHordeConfig(): void
    {
        $formatter = new ClassicHordeFormatter();
        $config = [
            'conf' => [
                'sql' => [
                    'hostspec' => 'localhost',
                    'username' => 'horde',
                    'password' => 'secret',
                    'port' => 3306,
                ],
                'readwritesplit' => true,
                'log_level' => new LiteralCode('Horde_Log::DEBUG'),
            ],
        ];

        $result = $formatter->format($config);

        $this->assertStringContainsString("\$conf['log_level'] = Horde_Log::DEBUG;", $result);
        $this->assertStringContainsString("\$conf['readwritesplit'] = true;", $result);
        $this->assertStringContainsString("\$conf['sql']['hostspec'] = 'localhost';", $result);
        $this->assertStringContainsString("\$conf['sql']['password'] = 'secret';", $result);
        $this->assertStringContainsString("\$conf['sql']['port'] = 3306;", $result);
        $this->assertStringContainsString("\$conf['sql']['username'] = 'horde';", $result);
    }
}
