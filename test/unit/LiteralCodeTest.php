<?php

declare(strict_types=1);

namespace Horde\PhpConfigFile\Test\Unit;

use Horde\PhpConfigFile\LiteralCode;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use ReflectionClass;

#[CoversClass(LiteralCode::class)]
class LiteralCodeTest extends TestCase
{
    public function testConstructorStoresCode(): void
    {
        $literal = new LiteralCode('SomeClass::CONSTANT');
        $this->assertEquals('SomeClass::CONSTANT', $literal->getCode());
    }

    public function testToStringReturnsCode(): void
    {
        $literal = new LiteralCode('60 * 60');
        $this->assertEquals('60 * 60', (string) $literal);
    }

    public function testSupportsExpression(): void
    {
        $literal = new LiteralCode('Horde_Log::DEBUG');
        $this->assertEquals('Horde_Log::DEBUG', $literal->getCode());
    }

    public function testSupportsComplexExpression(): void
    {
        $literal = new LiteralCode('defined("DEBUG") ? 1 : 0');
        $this->assertEquals('defined("DEBUG") ? 1 : 0', $literal->getCode());
    }

    public function testIsReadonly(): void
    {
        $literal = new LiteralCode('test');
        $reflection = new ReflectionClass($literal);
        $this->assertTrue($reflection->isReadOnly());
    }
}
