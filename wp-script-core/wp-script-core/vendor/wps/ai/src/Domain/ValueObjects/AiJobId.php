<?php

namespace WPS\Ai\Domain\ValueObjects;

/**
 * The User ID Value Object.
 */
class AiJobId extends AbstractEntityId
{
    /**
     * Set the id.
     *
     * @param string $id The ID.
     *
     * @return self The ID.
     */
    public static function from($id)
    {
        return new self($id);
    }
}
