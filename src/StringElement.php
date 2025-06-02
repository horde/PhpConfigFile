<?php

declare(strict_types=1);

namespace Horde\PhpConfigFile;

use Stringable;

class StringElement implements LeafElement
{
    use LeafElementTrait;
    private string $value;

    public function __construct(ParentElement $parent, string $name, string|Stringable $value = '')
    {
        $this->value = $value;
        $this->name = $name;
        $this->parent = $parent;
    }

    public function getValue(): string
    {
        return $this->value;
    }
    public function setValue(string|Stringable|null $value): void
    {
        if ($value instanceof Stringable) {
            $value = (string) $value; // Convert Stringable to string
        }
        $this->value = $value;
    }

    public function getAllowedTypes(): array
    {
        return ['string', Stringable::class, 'null'];
    }

    public function toPhpValue(): string
    {
        return $this->value;
    }

    public function isSerializable(): bool
    {
        return true; // String elements can always be serialized.
    }
}
