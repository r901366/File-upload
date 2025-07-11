<?php

namespace WPS\Ai\Domain\Entities;

use WPS\Ai\Domain\ValueObjects\AiJobId;
use WPS\Ai\Domain\ValueObjects\AiJobStatus;
use WPS\Ai\Domain\ValueObjects\AiJobType;

final class AiJob implements EntityInterface
{
    /**
     * @var string ID.
     */
    public $id;

    /**
     * @var AiJobId AI Job ID.
     */
    public $ai_job_id;

    /**
     * @var AiJobStatus AI Job status.
     */
    public $ai_job_status;

    /**
     * @var string AI Job content.
     */
    public $ai_job_content;

    /**
     * @var AiJobType AI Job type.
     */
    public $ai_job_type;

    /**
     * @var array<string,string> Additional parameters.
     */
    public $params;

    /**
     * Constructor.
     *
     * @param string $id ID.
     * @param AiJobId $ai_job_id Generation Job ID.
     * @param AiJobStatus $ai_job_status Generation Job status.
     * @param AiJobType $ai_job_type AI type.
     * @param string $ai_job_content AI content.
     * @param array<string,string> $params Additional parameters. Default is an empty array.
     */
    public function __construct(
        $id,
        $ai_job_id,
        $ai_job_status,
        $ai_job_type,
        $ai_job_content = '',
        $params = array()
    ) {
        $this->id                = $id;
        $this->ai_job_id         = $ai_job_id;
        $this->ai_job_status     = $ai_job_status;
        $this->ai_job_type       = $ai_job_type;
        $this->ai_job_content    = $ai_job_content;
        $this->params            = $params;
    }

    /**
     * Get ID.
     *
     * @return string The ID
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get Ai Job ID.
     *
     * @return AiJobId The Ai Job ID
     */
    public function getAiJobId()
    {
        return $this->ai_job_id;
    }

    /**
     * Get Ai Job status.
     *
     * @return AiJobStatus AI Job status.
     */
    public function getStatus()
    {
        return $this->ai_job_status;
    }

    /**
     * Get AI content.
     *
     * @return string AI Job content.
     */
    public function getContent()
    {
        return $this->ai_job_content;
    }

    /**
     * Get AI type.
     * Can be 'title' or 'description'.
     *
     * @return AiJobType AI Job type.
     */
    public function getType()
    {
        return $this->ai_job_type;
    }

    /**
     * Get additional parameters.
     *
     * @return array<string,string> Additional parameters.
     */
    public function getParams()
    {
        return $this->params;
    }


    /**
     * Get a parameter.
     *
     * @param string $key Parameter key.
     *
     * @return string|null Parameter value.
     */
    public function getParam($key, $default_value = '')
    {
        if (! is_string($key)) {
            throw new \InvalidArgumentException('Parameter key must be a string.');
        }

        if (! array_key_exists($key, $this->params)) {
            return $default_value;
        }

        return $this->params[ $key ];
    }

    /**
     * Get data as array.
     *
     * @return array Data as an array.
     */
    public function toArray()
    {
        return array(
            'id' => $this->id,
            'ai_job_id' => $this->ai_job_id->getValue(),
            'ai_job_status' => $this->ai_job_status->getValue(),
            'ai_job_content' => $this->ai_job_content,
            'ai_job_type' => $this->ai_job_type->getValue(),
            'params' => $this->params,
        );
    }
}
