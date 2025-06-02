<?php

declare(strict_types=1);

namespace Horde\PhpConfigFile;

use InvalidArgumentException;

trait LeafElementTrait
{
    private ?ParentElement $parent = null;
    private string $name;

    public function getName(): string
    {
        return $this->name;
    }

    public function isLeaf(): true
    {
        // Parent elements are not leaf elements, they can contain other elements.
        return true;
    }

    public function isRoot(): false
    {
        // Parent elements are not root elements, they are collections of child elements.
        return false;
    }

    public function getParent(): ?ParentElement
    {
        // Parent elements do not have a parent in the same way leaf elements do.
        return $this->parent;
    }

    public function toPhpValue(): mixed
    {
        // Parent elements do not have a direct PHP value representation. They are collections of child elements.
        $elements = [];
        foreach ($this->children as $child) {
            if ($child->isSerializable()) {
                $elements[$child->getName()] = $child->toPhpValue();
            }
        }
        return $elements;
    }
}
