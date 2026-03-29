<?php

declare(strict_types=1);

namespace Horde\PhpConfigFile\Test\Unit;

use PHPUnit\Framework\TestCase;
use Stringable;
use Horde\PhpConfigFile\ConfigurationSchema;
use Horde\PhpConfigFile\StringElement;
use Horde\PhpConfigFile\IntegerElement;
use PHPUnit\Framework\Attributes\CoversNothing;

#[CoversNothing]
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
        $schema->addChild(new StringElement($schema, name: 'element1', value: 'value1'));
        $schema->addChild(new IntegerElement($schema, name: 'element2', value: 42));

        $this->assertTrue($schema->hasChild('element1'), 'Schema should have element1');
        $this->assertTrue($schema->hasChild('element2'), 'Schema should have element2');
        $this->assertFalse($schema->hasChild('nonexistent'), 'Schema should not have nonexistent element');
        $element1 = $schema->getChild('element1');
        $this->assertInstanceOf(StringElement::class, $element1, 'Element1 should be a StringElement');
        $this->assertEquals('value1', $element1->getValue(), 'Element1 value should match');
        $element2 = $schema->getChild('element2');
        $this->assertInstanceOf(IntegerElement::class, $element2, 'Element2 should be an IntegerElement');
        $this->assertEquals(42, $element2->getValue(), 'Element2 value should match');
    }
}
