<?php

declare(strict_types=1);

namespace Horde\PhpConfigFile;

/**
 * ConfigurationSchema root class for Horde PhpConfigFile.
 *
 * This is the root element of an OO configuration schema for Horde PhpConfigFile.
 * The design evolved from the config xml files used in Horde 3 to 6.
 */
interface ConfigurationElement
{
    public function isRoot(): bool;
    /**
     * Null if this is the root element, otherwise the parent element.
     */
    public function getParent(): ?ConfigurationElement;

    /**
     * The name of the element, e.g. 'database', 'cache', 'session'.
     *
     * This is used to identify the element in the configuration file.
     * It is also the parent key for child elements in a non-leaf element.
     */
    public function getName(): string;
    /**
     * Leaf elements render to an atomic PHP value (boolean, integer, float, string, or null).
     */
    public function isLeaf(): bool;

    /**
     * Will this serialize to a line in the config file?
     *
     * @return bool true if this element will get written to the config file or represents a hierarchy level, false if it is a display-only element.
     */
    public function isSerializable(): bool;
    /**
     * Render the element to a boolean, integer, float, string or null value or a hash-like array of sub values.
     */
    public function toPhpValue(): mixed;
}
