<?php

namespace WPS\Ai\Domain\ValueObjects;

use InvalidArgumentException;

/**
 * Abstract Entity Id.
 */
abstract class AbstractEntityId implements EntityIdInterface, ValueObjectInterface
{
    /**
     * @var string The ID.
     */
    protected $id;

    /**
     * Constructor.
     *
     * @param string $id The ID.
     *
     * @throws InvalidArgumentException If the ID is invalid.
     */
    protected function __construct($id)
    {
        if (! is_string($id)) {
            throw new InvalidArgumentException('Entity Id must be of type string.');
        }
        $this->id = $id;
    }

    /**
     * Get the value of the ID.
     *
     * return string The ID.
     */
    public function getValue()
    {
        return $this->id;
    }

    /**
     * Check if this ID is equal to another ID.
     *
     * @param EntityIdInterface $other The other ID.
     *
     * @return bool True if they are equal, false otherwise.
     */
    public function equals($other)
    {
        if (! $other instanceof EntityIdInterface) {
            return false;
        }
        return $this->id === $other->getValue();
    }

    /**
     * Validate the ID.
     *
     * @return bool True if the ID is valid, false otherwise.
     */
    public function validate()
    {
        return $this->id !== '';
    }

    /**
     * Set the id.
     *
     * @param string $id The ID.
     *
     * @return self The ID.
     */
    abstract public static function from($id);
}
