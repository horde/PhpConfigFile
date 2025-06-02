<?php

declare(strict_types=1);

namespace Horde\PhpConfigFile;

class IntegerElement implements LeafElement
{
    use LeafElementTrait;
    private int $value;

    public function __construct(ParentElement $parent, string $name, int $value)
    {
        $this->value = $value;
        $this->name = $name;
        $this->parent = $parent;
    }

    public function getValue(): int
    {
        return $this->value;
    }

    public function setValue(?int $value): void
    {
        $this->value = $value;
    }

    public function toPhpValue(): int
    {
        return $this->value;
    }

    public function isSerializable(): bool
    {
        return true; // Boolean elements can always be serialized.
    }
}
