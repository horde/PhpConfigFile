<?php

declare(strict_types=1);

namespace Horde\PhpConfigFile;

use InvalidArgumentException;

trait ParentElementTrait
{
    // There's no need to let child classes mess with the children array directly.
    private array $children = [];
    private ?ParentElement $parent = null;

    public function getChildren(): array
    {
        return $this->children;
    }

    public function getParent(): ?ParentElement
    {
        // Parent elements do not have a parent in the same way leaf elements do.
        return $this->parent;
    }

    public function addChild(ConfigurationElement $child): void
    {
        $this->children[] = $child;
    }

    public function addChildren(ConfigurationElement ...$children): void
    {
        foreach ($children as $child) {
            $this->addChild($child);
        }
    }

    public function removeChild(ConfigurationElement $child): void
    {
        $this->children = array_filter($this->children, fn($c) => $c !== $child);
    }

    /**
     * Check if this parent element has an immediate child with the given name and/or class.
     *
     * @param string $name The name of the child element to check for. Leave empty to check for any child of a given class.
     * @param string $class The class of the child element to check for (optional). Leave empty to check for any child with the given name.
     * @return bool True if a child with the given name and/or exists, false otherwise.
     */
    public function hasChild(string $name = '', string $class = ''): bool
    {
        if (empty($name) && empty($class)) {
            throw new InvalidArgumentException('Either name or class must be provided to check for a child element.');
        }
        foreach ($this->children as $child) {
            if ($child->getName() === $name) {
                return true;
            }
        }
        return false;
    }

    public function isLeaf(): false
    {
        // Parent elements are not leaf elements, they can contain other elements.
        return false;
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
