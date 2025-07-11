<?php

namespace WPS\RestApi\GenerateContentBulkRequestDto;

class GenerateContentBulkRequestDtoActionsItemParams
{
    /**
     * Schema used to validate input for creating instances of this class
     *
     * @var array
     */
    private static $schema = [
        'type' => 'object',
        'description' => 'The parameters for the action.',
        'properties' => [
            'video_title' => [
                'type' => 'string',
                'description' => 'The original title of the video',
            ],
            'video_id' => [
                'type' => 'string',
                'description' => 'The unique identifier of the video.',
            ],
            'partner_id' => [
                'type' => 'string',
                'description' => 'The partner ID of the video.',
            ],
        ],
        'required' => [
            'video_title',
            'video_id',
            'partner_id',
        ],
        'additionalItems' => false,
        'additionalProperties' => false,
    ];

    /**
     * The original title of the video
     *
     * @var string
     */
    private $videoTitle;

    /**
     * The unique identifier of the video.
     *
     * @var string
     */
    private $videoId;

    /**
     * The partner ID of the video.
     *
     * @var string
     */
    private $partnerId;

    /**
     * @param string $videoTitle
     * @param string $videoId
     * @param string $partnerId
     */
    public function __construct(string $videoTitle, string $videoId, string $partnerId)
    {
        $this->videoTitle = $videoTitle;
        $this->videoId = $videoId;
        $this->partnerId = $partnerId;
    }

    /**
     * @return string
     */
    public function getVideoTitle()
    {
        return $this->videoTitle;
    }

    /**
     * @return string
     */
    public function getVideoId()
    {
        return $this->videoId;
    }

    /**
     * @return string
     */
    public function getPartnerId()
    {
        return $this->partnerId;
    }

    /**
     * @param string $videoTitle
     * @return self
     */
    public function withVideoTitle(string $videoTitle)
    {
        $validator = new \JsonSchema\Validator();
        $validator->validate($videoTitle, self::$schema['properties']['video_title']);
        if (!$validator->isValid()) {
            throw new \InvalidArgumentException($validator->getErrors()[0]['message']);
        }

        $clone = clone $this;
        $clone->videoTitle = $videoTitle;

        return $clone;
    }

    /**
     * @param string $videoId
     * @return self
     */
    public function withVideoId(string $videoId)
    {
        $validator = new \JsonSchema\Validator();
        $validator->validate($videoId, self::$schema['properties']['video_id']);
        if (!$validator->isValid()) {
            throw new \InvalidArgumentException($validator->getErrors()[0]['message']);
        }

        $clone = clone $this;
        $clone->videoId = $videoId;

        return $clone;
    }

    /**
     * @param string $partnerId
     * @return self
     */
    public function withPartnerId(string $partnerId)
    {
        $validator = new \JsonSchema\Validator();
        $validator->validate($partnerId, self::$schema['properties']['partner_id']);
        if (!$validator->isValid()) {
            throw new \InvalidArgumentException($validator->getErrors()[0]['message']);
        }

        $clone = clone $this;
        $clone->partnerId = $partnerId;

        return $clone;
    }

    /**
     * Builds a new instance from an input array
     *
     * @param array|object $input Input data
     * @param bool $validate Set this to false to skip validation; use at own risk
     * @return GenerateContentBulkRequestDtoActionsItemParams Created instance
     * @throws \InvalidArgumentException
     */
    public static function buildFromInput($input, bool $validate = true)
    {
        $input = is_array($input) ? \JsonSchema\Validator::arrayToObjectRecursive($input) : $input;
        if ($validate) {
            static::validateInput($input);
        }

        $videoTitle = $input->{'video_title'};
        $videoId = $input->{'video_id'};
        $partnerId = $input->{'partner_id'};

        $obj = new self($videoTitle, $videoId, $partnerId);

        return $obj;
    }

    /**
     * Converts this object back to a simple array that can be JSON-serialized
     *
     * @return array Converted array
     */
    public function toJson()
    {
        $output = [];
        $output['video_title'] = $this->videoTitle;
        $output['video_id'] = $this->videoId;
        $output['partner_id'] = $this->partnerId;

        return $output;
    }

    /**
     * Validates an input array
     *
     * @param array|object $input Input data
     * @param bool $return Return instead of throwing errors
     * @return bool Validation result
     * @throws \InvalidArgumentException
     */
    public static function validateInput($input, $return = false)
    {
        $validator = new \JsonSchema\Validator();
        $input = is_array($input) ? \JsonSchema\Validator::arrayToObjectRecursive($input) : $input;
        $validator->validate($input, self::$schema);

        if (!$validator->isValid() && !$return) {
            $errors = array_map(function ($e) {
                return $e["property"] . ": " . $e["message"];
            }, $validator->getErrors());
            throw new \InvalidArgumentException(join(", ", $errors));
        }

        return $validator->isValid();
    }

    public function __clone()
    {
    }
}
