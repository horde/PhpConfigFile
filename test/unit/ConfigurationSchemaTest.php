<?php

declare(strict_types=1);

namespace Horde\PhpConfigFile\Test\Unit;

use Horde\PhpConfigFile\PhpConfigFile;
use PHPUnit\Framework\TestCase;
use Stringable;
use Horde\PhpConfigFile\ConfigurationSchema;

/**
 * @coversNothing
 */
class ConfigurationSchemaTest extends TestCase
{
    public function testConfigurationSchema(): void
    {
        $schema = new ConfigurationSchema(name: 'TestSchema');
        $this->assertTrue($schema->isRoot(), 'Schema should be root');
        $this->assertEquals('TestSchema', $schema->getName(), 'Schema name should match');
        $this->assertTrue($schema->isSerializable(), 'Schema should be serializable');
    }

    public function testConfigurationSchemaWithElements(): void
    {
        $schema = new ConfigurationSchema(name: 'TestSchema2');
        $element1 = $schema->addElement(name: 'element1', value: 'value1');
        $element2 = $schema->addElement(name: 'element2', value: 42);

        $this->assertEquals('value1', $element1->getValue(), 'Element1 value should match');
        $this->assertEquals(42, $element2->getValue(), 'Element2 value should match');

        $this->assertTrue($schema->hasElement('element1'), 'Schema should have element1');
        $this->assertTrue($schema->hasElement('element2'), 'Schema should have element2');
        $this->assertFalse($schema->hasElement('nonexistent'), 'Schema should not have nonexistent element');
    }
}