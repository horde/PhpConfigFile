<?php

declare(strict_types=1);

namespace Horde\PhpConfigFile;

class BooleanElement implements LeafElement
{
    use LeafElementTrait;
    private bool $value;

    public function __construct(private ?ParentElement $parent, string $name, bool $value = false)
    {
        $this->value = $value;
        $this->name = $name;
        $this->parent = $parent;
    }

    public function getValue(): bool
    {
        return $this->value;
    }

    public function setValue(?bool $value): void
    {
        $this->value = $value;
    }

    public function toPhpValue(): bool
    {
        return $this->value;
    }

    public function getAllowedTypes(): array
    {
        return ['bool'];
    }

    public function isSerializable(): bool
    {
        return true; // Boolean elements can always be serialized.
    }
}
