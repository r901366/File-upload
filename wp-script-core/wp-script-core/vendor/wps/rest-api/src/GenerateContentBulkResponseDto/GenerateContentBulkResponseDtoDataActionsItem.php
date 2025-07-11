<?php

namespace WPS\RestApi\GenerateContentBulkResponseDto;

class GenerateContentBulkResponseDtoDataActionsItem
{
    /**
     * Schema used to validate input for creating instances of this class
     *
     * @var array
     */
    private static $schema = [
        'type' => 'object',
        'properties' => [
            'action_name' => [
                'type' => 'string',
            ],
            'action_id' => [
                'type' => 'string',
            ],
            'code' => [
                'type' => 'string',
                'pattern' => '^(?:success|error)$',
            ],
            'message' => [
                'type' => 'string',
            ],
            'ai_job_created' => [
                'type' => 'object',
                'properties' => [
                    'id' => [
                        'type' => 'string',
                    ],
                    'status' => [
                        'type' => 'string',
                        'pattern' => '^(?:pending|processing|completed|failed)$',
                    ],
                ],
                'required' => [
                    'id',
                    'status',
                ],
                'additionalItems' => false,
                'additionalProperties' => false,
            ],
        ],
        'required' => [
            'action_name',
            'action_id',
            'code',
            'message',
        ],
        'additionalItems' => false,
        'additionalProperties' => false,
    ];

    /**
     * @var string
     */
    private $actionName;

    /**
     * @var string
     */
    private $actionId;

    /**
     * @var string
     */
    private $code;

    /**
     * @var string
     */
    private $message;

    /**
     * @var GenerateContentBulkResponseDtoDataActionsItemAiJobCreated|null
     */
    private $aiJobCreated = null;

    /**
     * @param string $actionName
     * @param string $actionId
     * @param string $code
     * @param string $message
     */
    public function __construct(string $actionName, string $actionId, string $code, string $message)
    {
        $this->actionName = $actionName;
        $this->actionId = $actionId;
        $this->code = $code;
        $this->message = $message;
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
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @return GenerateContentBulkResponseDtoDataActionsItemAiJobCreated|null
     */
    public function getAiJobCreated()
    {
        return $this->aiJobCreated;
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
     * @param string $code
     * @return self
     */
    public function withCode(string $code)
    {
        $validator = new \JsonSchema\Validator();
        $validator->validate($code, self::$schema['properties']['code']);
        if (!$validator->isValid()) {
            throw new \InvalidArgumentException($validator->getErrors()[0]['message']);
        }

        $clone = clone $this;
        $clone->code = $code;

        return $clone;
    }

    /**
     * @param string $message
     * @return self
     */
    public function withMessage(string $message)
    {
        $validator = new \JsonSchema\Validator();
        $validator->validate($message, self::$schema['properties']['message']);
        if (!$validator->isValid()) {
            throw new \InvalidArgumentException($validator->getErrors()[0]['message']);
        }

        $clone = clone $this;
        $clone->message = $message;

        return $clone;
    }

    /**
     * @param GenerateContentBulkResponseDtoDataActionsItemAiJobCreated $aiJobCreated
     * @return self
     */
    public function withAiJobCreated(GenerateContentBulkResponseDtoDataActionsItemAiJobCreated $aiJobCreated)
    {
        $clone = clone $this;
        $clone->aiJobCreated = $aiJobCreated;

        return $clone;
    }

    /**
     * @return self
     */
    public function withoutAiJobCreated()
    {
        $clone = clone $this;
        unset($clone->aiJobCreated);

        return $clone;
    }

    /**
     * Builds a new instance from an input array
     *
     * @param array|object $input Input data
     * @param bool $validate Set this to false to skip validation; use at own risk
     * @return GenerateContentBulkResponseDtoDataActionsItem Created instance
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
        $code = $input->{'code'};
        $message = $input->{'message'};
        $aiJobCreated = null;
        if (isset($input->{'ai_job_created'})) {
            $aiJobCreated = GenerateContentBulkResponseDtoDataActionsItemAiJobCreated::buildFromInput($input->{'ai_job_created'}, $validate);
        }

        $obj = new self($actionName, $actionId, $code, $message);
        $obj->aiJobCreated = $aiJobCreated;
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
        $output['code'] = $this->code;
        $output['message'] = $this->message;
        if (isset($this->aiJobCreated)) {
            $output['ai_job_created'] = ($this->aiJobCreated)->toJson();
        }

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
        if (isset($this->aiJobCreated)) {
            $this->aiJobCreated = clone $this->aiJobCreated;
        }
    }
}
