<?php

namespace WPS\RestApi\GenerateContentBulkResponseDto;

class GenerateContentBulkResponseDto
{
    /**
     * Schema used to validate input for creating instances of this class
     *
     * @var array
     */
    private static $schema = [
        '$schema' => 'http://json-schema.org/draft-07/schema#',
        'properties' => [
            'code' => [
                'type' => 'string',
                'pattern' => '^(?:success|error)$',
            ],
            'message' => [
                'type' => 'string',
            ],
            'data' => [
                'type' => 'object',
                'properties' => [
                    'status' => [
                        'type' => 'integer',
                    ],
                    'credits_left' => [
                        'type' => 'integer',
                    ],
                    'actions' => [
                        'type' => 'array',
                        'items' => [
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
                        ],
                    ],
                ],
                'required' => [
                    'status',
                    'credits_left',
                    'actions',
                ],
                'additionalItems' => false,
                'additionalProperties' => false,
            ],
        ],
        'required' => [
            'code',
            'message',
            'data',
        ],
        'additionalItems' => false,
        'additionalProperties' => false,
    ];

    /**
     * @var string
     */
    private $code;

    /**
     * @var string
     */
    private $message;

    /**
     * @var GenerateContentBulkResponseDtoData
     */
    private $data;

    /**
     * @param string $code
     * @param string $message
     * @param GenerateContentBulkResponseDtoData $data
     */
    public function __construct(string $code, string $message, GenerateContentBulkResponseDtoData $data)
    {
        $this->code = $code;
        $this->message = $message;
        $this->data = $data;
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
     * @return GenerateContentBulkResponseDtoData
     */
    public function getData()
    {
        return $this->data;
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
     * @param GenerateContentBulkResponseDtoData $data
     * @return self
     */
    public function withData(GenerateContentBulkResponseDtoData $data)
    {
        $clone = clone $this;
        $clone->data = $data;

        return $clone;
    }

    /**
     * Builds a new instance from an input array
     *
     * @param array|object $input Input data
     * @param bool $validate Set this to false to skip validation; use at own risk
     * @return GenerateContentBulkResponseDto Created instance
     * @throws \InvalidArgumentException
     */
    public static function buildFromInput($input, bool $validate = true)
    {
        $input = is_array($input) ? \JsonSchema\Validator::arrayToObjectRecursive($input) : $input;
        if ($validate) {
            static::validateInput($input);
        }

        $code = $input->{'code'};
        $message = $input->{'message'};
        $data = GenerateContentBulkResponseDtoData::buildFromInput($input->{'data'}, $validate);

        $obj = new self($code, $message, $data);

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
        $output['code'] = $this->code;
        $output['message'] = $this->message;
        $output['data'] = ($this->data)->toJson();

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
        $this->data = clone $this->data;
    }
}
