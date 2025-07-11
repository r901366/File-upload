<?php

namespace WPS\Ai\Domain\ValueObjects;

use WPS\Ai\Domain\Exceptions\InvalidAiJobTypeException;

final class AiJobType
{
    /**
     * AI Job Type.
     *
     * @var string
     */
    private $ai_job_type;

    /**
     * Create a new AiJobType object.
     *
     * @param string $ai_job_type The AI Job Type.
     */
    private function __construct($ai_job_type)
    {
        $this->ai_job_type = trim(mb_strtolower($ai_job_type));
        $this->validate();
    }

    /**
     * Create a new AiJobType object from a string.
     *
     * @param string $ai_job_type The AI Job Type.
     *
     * @return self The AI Job Type object.
     */
    public static function from($ai_job_type)
    {
        return new self($ai_job_type);
    }

    /**
     * Create a new AiJobType object from Title.
     *
     * @return self The AI Job Type object.
     */
    public static function fromTitle()
    {
        return new self('title');
    }

    /**
     * Create a new AiJobType object from Description.
     *
     * @return self The AI Job Type object.
     */
    public static function fromDescription()
    {
        return new self('description');
    }

    /**
     * Check if the AI Job Type is Title.
     *
     * @return bool True if the AI Job Type is Title, false otherwise.
     */
    public function isTitle()
    {
        return $this->ai_job_type === 'title';
    }

    /**
     * Check if the AI Job Type is Description.
     *
     * @return bool True if the AI Job Type is Description, false otherwise.
     */
    public function isDescription()
    {
        return $this->ai_job_type === 'description';
    }

    /**
     * Validate the AI Job Type.
     *
     * @throws InvalidAiJobTypeException If the SKU is invalid.
     *
     * @return void
     */
    public function validate()
    {
        if (!is_string($this->ai_job_type)) {
            throw new InvalidAiJobTypeException('AI Job Type must be a string.');
        }

        if (strlen($this->ai_job_type) === 0) {
            throw new InvalidAiJobTypeException('AI Job Type cannot be empty.');
        }

        if (! in_array($this->ai_job_type, $this->allowedAiJobTypes(), true)) {
            throw new InvalidAiJobTypeException('Invalid Ai Job Type: ' . $this->ai_job_type);
        }
    }

    /**
     * Check if the object is equal to another object.
     *
     * @param self $ai_job_type The other SKU.
     *
     * @return bool True if the objects are the same, false otherwise.
     */
    public function equals($ai_job_type)
    {
        if (! $ai_job_type instanceof self) {
            return false;
        }

        return $this->getValue() === $ai_job_type->getValue();
    }

    /**
     * Get the AI Job Type as a string.
     *
     * @return string The AI Job Type.
     */
    public function getValue()
    {
        return $this->ai_job_type;
    }

    /**
     * Get the allowed SKUs.
     *
     * @return array<string> The allowed SKUs.
     */
    private function allowedAiJobTypes()
    {
        return array(
            'title',
            'description',
        );
    }
}
