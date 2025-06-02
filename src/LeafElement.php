<?php

declare(strict_types=1);

namespace Horde\PhpConfigFile;

interface LeafElement extends ConfigurationElement
{
    /**
     * Get the value of this leaf element.
     */
    public function getValue(): mixed;

    public function isLeaf(): true;

    /**
     * Set the value of this leaf element.
     *
     */
    public function setValue(null $value): void;

    /**
     * Convert the leaf element to a PHP value.
     *
     * @return mixed The PHP value representation of the leaf element.
     */
    public function toPhpValue(): mixed;

    /**
     * Check if the leaf element is serializable.
     *
     * @return bool True if the leaf element can be serialized, false otherwise.
     */
    public function isSerializable(): bool;
}
