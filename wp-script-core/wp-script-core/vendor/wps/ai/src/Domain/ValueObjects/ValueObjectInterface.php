<?php

namespace WPS\Ai\Domain\ValueObjects;

interface ValueObjectInterface
{
    /**
     * Validate the Value Object.
     *
     * @throws \InvalidArgumentException If the Value Object is invalid.
     *
     * @return void
     */
    public function validate();

    /**
     * Check if the Value Object is equal to another Value Object.
     *
     * @param mixed $value_object The other Value Object.
     *
     * @return bool True if the Value Object is equal to the other Value Object, false otherwise.
     */
    public function equals($value_object);
}
