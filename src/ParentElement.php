<?php

declare(strict_types=1);

namespace Horde\PhpConfigFile;

interface ParentElement extends ConfigurationElement
{
    /**
     * Get the child elements of this parent element.
     *
     * @return ConfigurationElement[] An array of child elements.
     */
    public function getChildren(): array;

    /**
     * Add a child element to this parent element.
     *
     * @param ConfigurationElement $child The child element to add.
     */
    public function addChild(ConfigurationElement $child): void;
    public function addChildren(ConfigurationElement ...$children): void;
    public function isLeaf(): false;

    /**
     * Remove a child element from this parent element.
     *
     * @param ConfigurationElement $child The child element to remove.
     */
    public function removeChild(ConfigurationElement $child): void;
}
