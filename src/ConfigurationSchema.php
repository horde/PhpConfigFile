<?php

declare(strict_types=1);

namespace Horde\PhpConfigFile;

use InvalidArgumentException;

/**
 * ConfigurationSchema root class for Horde PhpConfigFile.
 *
 * This is the root element of an OO configuration schema for Horde PhpConfigFile.
 * The design evolved from the config xml files used in Horde 3 to 6.
 */
class ConfigurationSchema implements ParentElement
{
    use ParentElementTrait;
    public function __construct(
        /** The name of the root element, defaults to 'root'. */
        public readonly string $name = 'root',
        public readonly bool $omitRootKey = true
    ) {
        if ($this->parent) {
            throw new InvalidArgumentException('Root element cannot have a parent.');
        }
        // The root element is initialized with a name and an optional flag to omit the root key.
        if (strlen($this->name) === 0) {
            throw new InvalidArgumentException('Root element name cannot be empty. Use omitRootKey to avoid writing the root key to config file.');
        }
    }
    public function isRoot(): bool
    {
        return true;
    }
    public function getParent(): ?ConfigurationElement
    {
        return null;
    }
    public function getAllowedTypes(): array
    {
        // The root element does not have sub-elements, so it does not define allowed types.
        return [];
    }
    public function getName(): string
    {
        return $this->name;
    }
    /**
     * Will this serialize to a line in the config file?
     *
     * @return bool true if this element will get written to the config file or represents a hierarchy level, false if it is a display-only element.
     */
    public function isSerializable(): bool
    {
        return true;
    }
}
