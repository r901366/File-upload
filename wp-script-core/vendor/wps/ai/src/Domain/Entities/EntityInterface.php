<?php

namespace WPS\Ai\Domain\Entities;

/**
 * The Entity Id Interface.
 */
interface EntityInterface
{
    /**
     * Get the ID.
     *
     * @return string The ID.
     */
    public function getId();

    /**
     * Get the entity as an array.
     *
     * @return array<mixed> The entity as an array.
     */
    public function toArray();
}
