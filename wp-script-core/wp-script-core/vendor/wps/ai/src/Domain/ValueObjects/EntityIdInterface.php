<?php

namespace WPS\Ai\Domain\ValueObjects;

/**
 * The Entity Id Interface.
 */
interface EntityIdInterface
{
    /**
     * Factory method to create an instance of the Entity ID.
     *
     * @param string $id The ID.
     *
     * @return EntityIdInterface
     */
    public static function from($id);

    /**
     * Get the value of the ID.
     *
     * @return string The ID.
     */
    public function getValue();
}
