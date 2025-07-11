<?php

namespace WPS\RestApi\GenerateContentBulkRequestDto;

class GenerateContentBulkRequestDtoActionsItem
{
    /**
     * Schema used to validate input for creating instances of this class
     *
     * @var array
     */
    private static $schema = [
        'properties' => [
            'action_name' => [
                'type' => 'string',
                'description' => 'The name of the action to perform.',
            ],
            'action_id' => [
                'type' => 'string',
                'description' => 'The unique identifier of the action.',
            ],
            'params' => [
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
            ],
        ],
        'required' => [
            'action_name',
            'action_id',
            'params',
        ],
        'additionalItems' => false,
        'additionalProperties' => false,
    ];

    /**
     * The name of the action to perform.
     *
     * @var string
     */
    private $actionName;

    /**
     * The unique identifier of the action.
     *
     * @var string
     */
    private $actionId;

    /**
     * The parameters for the action.
     *
     * @var GenerateContentBulkRequestDtoActionsItemParams
     */
    private $params;

    /**
     * @param string $actionName
     * @param string $actionId
     * @param GenerateContentBulkRequestDtoActionsItemParams $params
     */
    public function __construct(string $actionName, string $actionId, GenerateContentBulkRequestDtoActionsItemParams $params)
    {
        $this->actionName = $actionName;
        $this->actionId = $actionId;
        $this->params = $params;
    }

    /**
     * @return string
     */
    public function getActionName()
    {
        return $this->actionName;
    }

    /**
     * @return string
     */
    public function getActionId()
    {
        return $this->actionId;
    }

    /**
     * @return GenerateContentBulkRequestDtoActionsItemParams
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * @param string $actionName
     * @return self
     */
    public function withActionName(string $actionName)
    {
        $validator = new \JsonSchema\Validator();
        $validator->validate($actionName, self::$schema['properties']['action_name']);
        if (!$validator->isValid()) {
            throw new \InvalidArgumentException($validator->getErrors()[0]['message']);
        }

        $clone = clone $this;
        $clone->actionName = $actionName;

        return $clone;
    }

    /**
     * @param string $actionId
     * @return self
     */
    public function withActionId(string $actionId)
    {
        $validator = new \JsonSchema\Validator();
        $validator->validate($actionId, self::$schema['properties']['action_id']);
        if (!$validator->isValid()) {
            throw new \InvalidArgumentException($validator->getErrors()[0]['message']);
        }

        $clone = clone $this;
        $clone->actionId = $actionId;

        return $clone;
    }

    /**
     * @param GenerateContentBulkRequestDtoActionsItemParams $params
     * @return self
     */
    public function withParams(GenerateContentBulkRequestDtoActionsItemParams $params)
    {
        $clone = clone $this;
        $clone->params = $params;

        return $clone;
    }

    /**
     * Builds a new instance from an input array
     *
     * @param array|object $input Input data
     * @param bool $validate Set this to false to skip validation; use at own risk
     * @return GenerateContentBulkRequestDtoActionsItem Created instance
     * @throws \InvalidArgumentException
     */
    public static function buildFromInput($input, bool $validate = true)
    {
        $input = is_array($input) ? \JsonSchema\Validator::arrayToObjectRecursive($input) : $input;
        if ($validate) {
            static::validateInput($input);
        }

        $actionName = $input->{'action_name'};
        $actionId = $input->{'action_id'};
        $params = GenerateContentBulkRequestDtoActionsItemParams::buildFromInput($input->{'params'}, $validate);

        $obj = new self($actionName, $actionId, $params);

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
        $output['action_name'] = $this->actionName;
        $output['action_id'] = $this->actionId;
        $output['params'] = ($this->params)->toJson();

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
        $this->params = clone $this->params;
    }
}
