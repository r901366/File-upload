<?php

namespace WPS\Ai\Domain\ValueObjects;

use WPS\Ai\Domain\Exceptions\InvalidAiJobStatusException;

final class AiJobStatus
{
    /**
     * AI Job status.
     *
     * @var string
     */
    private $ai_job_status;

    /**
     * Create a new AiJobStatus object.
     *
     * @param string $ai_job_status The AI Job status.
     */
    private function __construct($ai_job_status)
    {
        $this->ai_job_status = trim(mb_strtolower($ai_job_status));
        $this->validate();
    }

    /**
     * Create a new AiJobStatus object from a string.
     *
     * @param string $ai_job_status The AI Job status.
     *
     * @return self The AI Job status object.
     */
    public static function from($ai_job_status)
    {
        return new self($ai_job_status);
    }

    /**
     * Create a new AiJobStatus object from pending.
     *
     * @return self The AI Job status object.
     */
    public static function fromPending()
    {
        return new self('pending');
    }

    /**
     * Create a new AiJobStatus object from processing.
     *
     * @return self The AI Job status object.
     */
    public static function fromProcessing()
    {
        return new self('processing');
    }

    /**
     * Create a new AiJobStatus object from success.
     *
     * @return self The AI Job status object.
     */
    public static function fromSuccess()
    {
        return new self('success');
    }

    /**
     * Create a new AiJobStatus object from error.
     *
     * @return self The AI Job status object.
     */
    public static function fromError()
    {
        return new self('error');
    }

    /**
     * Check if the AI Job status is pending.
     *
     * @return bool True if the AI Job status is pending, false otherwise.
     */
    public function isPending()
    {
        return $this->ai_job_status === 'pending';
    }

    /**
     * Check if the AI Job status is processing.
     *
     * @return bool True if the AI Job status is processing, false otherwise.
     */
    public function isProcessing()
    {
        return $this->ai_job_status === 'processing';
    }

    /**
     * Check if the AI Job status is success.
     *
     * @return bool True if the AI Job status is success, false otherwise.
     */
    public function isSuccess()
    {
        return $this->ai_job_status === 'success';
    }

    /**
     * Check if the AI Job status is error.
     *
     * @return bool True if the AI Job status is error, false otherwise.
     */
    public function isError()
    {
        return $this->ai_job_status === 'error';
    }

    /**
     * Validate the AI Job status.
     *
     * @throws InvalidAiJobStatusException If the SKU is invalid.
     *
     * @return void
     */
    public function validate()
    {
        if (!is_string($this->ai_job_status)) {
            throw new InvalidAiJobStatusException('AI Job Status must be a string.');
        }

        if (strlen($this->ai_job_status) === 0) {
            throw new InvalidAiJobStatusException('AI Job Status cannot be empty.');
        }

        if (! in_array($this->ai_job_status, $this->allowedAiJobStatus(), true)) {
            throw new InvalidAiJobStatusException('Invalid Ai Job Status: ' . $this->ai_job_status);
        }
    }

    /**
     * Check if the object is equal to another object.
     *
     * @param self $ai_job_status The other SKU.
     *
     * @return bool True if the objects are the same, false otherwise.
     */
    public function equals($ai_job_status)
    {
        if (! $ai_job_status instanceof self) {
            return false;
        }

        return $this->getValue() === $ai_job_status->getValue();
    }

    /**
     * Get the
     *
     * @return string The SKU value.
     */
    public function getValue()
    {
        return $this->ai_job_status;
    }

    /**
     * Get the allowed SKUs.
     *
     * @return array<string> The allowed SKUs.
     */
    private function allowedAiJobStatus()
    {
        return array(
            'pending',
            'processing',
            'success',
            'error',
        );
    }
}
