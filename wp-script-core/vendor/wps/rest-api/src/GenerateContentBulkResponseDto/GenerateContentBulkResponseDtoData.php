<?php

namespace WPS\RestApi\GenerateContentBulkResponseDto;

class GenerateContentBulkResponseDtoData
{
    /**
     * Schema used to validate input for creating instances of this class
     *
     * @var array
     */
    private static $schema = [
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
    ];

    /**
     * @var int
     */
    private $status;

    /**
     * @var int
     */
    private $creditsLeft;

    /**
     * @var GenerateContentBulkResponseDtoDataActionsItem[]
     */
    private $actions;

    /**
     * @param int $status
     * @param int $creditsLeft
     * @param GenerateContentBulkResponseDtoDataActionsItem[] $actions
     */
    public function __construct(int $status, int $creditsLeft, array $actions)
    {
        $this->status = $status;
        $this->creditsLeft = $creditsLeft;
        $this->actions = $actions;
    }

    /**
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @return int
     */
    public function getCreditsLeft()
    {
        return $this->creditsLeft;
    }

    /**
     * @return GenerateContentBulkResponseDtoDataActionsItem[]
     */
    public function getActions()
    {
        return $this->actions;
    }

    /**
     * @param int $status
     * @return self
     */
    public function withStatus(int $status)
    {
        $validator = new \JsonSchema\Validator();
        $validator->validate($status, self::$schema['properties']['status']);
        if (!$validator->isValid()) {
            throw new \InvalidArgumentException($validator->getErrors()[0]['message']);
        }

        $clone = clone $this;
        $clone->status = $status;

        return $clone;
    }

    /**
     * @param int $creditsLeft
     * @return self
     */
    public function withCreditsLeft(int $creditsLeft)
    {
        $validator = new \JsonSchema\Validator();
        $validator->validate($creditsLeft, self::$schema['properties']['credits_left']);
        if (!$validator->isValid()) {
            throw new \InvalidArgumentException($validator->getErrors()[0]['message']);
        }

        $clone = clone $this;
        $clone->creditsLeft = $creditsLeft;

        return $clone;
    }

    /**
     * @param GenerateContentBulkResponseDtoDataActionsItem[] $actions
     * @return self
     */
    public function withActions(array $actions)
    {
        $clone = clone $this;
        $clone->actions = $actions;

        return $clone;
    }

    /**
     * Builds a new instance from an input array
     *
     * @param array|object $input Input data
     * @param bool $validate Set this to false to skip validation; use at own risk
     * @return GenerateContentBulkResponseDtoData Created instance
     * @throws \InvalidArgumentException
     */
    public static function buildFromInput($input, bool $validate = true)
    {
        $input = is_array($input) ? \JsonSchema\Validator::arrayToObjectRecursive($input) : $input;
        if ($validate) {
            static::validateInput($input);
        }

        $status = (int)($input->{'status'});
        $creditsLeft = (int)($input->{'credits_left'});
        $actions = array_map(function ($i) use ($validate) {
            return GenerateContentBulkResponseDtoDataActionsItem::buildFromInput($i, $validate);
        }, $input->{'actions'});

        $obj = new self($status, $creditsLeft, $actions);

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
        $output['status'] = $this->status;
        $output['credits_left'] = $this->creditsLeft;
        $output['actions'] = array_map(function (GenerateContentBulkResponseDtoDataActionsItem $i) {
            return $i->toJson();
        }, $this->actions);

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
        $this->actions = array_map(function (GenerateContentBulkResponseDtoDataActionsItem $i) {
            return clone $i;
        }, $this->actions);
    }
}
