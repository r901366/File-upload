<?php

namespace WPS\RestApi\GenerateContentBulkResponseDto;

class GenerateContentBulkResponseDtoDataActionsItemAiJobCreated
{
    /**
     * Schema used to validate input for creating instances of this class
     *
     * @var array
     */
    private static $schema = [
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
    ];

    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $status;

    /**
     * @param string $id
     * @param string $status
     */
    public function __construct(string $id, string $status)
    {
        $this->id = $id;
        $this->status = $status;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param string $id
     * @return self
     */
    public function withId(string $id)
    {
        $validator = new \JsonSchema\Validator();
        $validator->validate($id, self::$schema['properties']['id']);
        if (!$validator->isValid()) {
            throw new \InvalidArgumentException($validator->getErrors()[0]['message']);
        }

        $clone = clone $this;
        $clone->id = $id;

        return $clone;
    }

    /**
     * @param string $status
     * @return self
     */
    public function withStatus(string $status)
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
     * Builds a new instance from an input array
     *
     * @param array|object $input Input data
     * @param bool $validate Set this to false to skip validation; use at own risk
     * @return GenerateContentBulkResponseDtoDataActionsItemAiJobCreated Created instance
     * @throws \InvalidArgumentException
     */
    public static function buildFromInput($input, bool $validate = true)
    {
        $input = is_array($input) ? \JsonSchema\Validator::arrayToObjectRecursive($input) : $input;
        if ($validate) {
            static::validateInput($input);
        }

        $id = $input->{'id'};
        $status = $input->{'status'};

        $obj = new self($id, $status);

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
        $output['id'] = $this->id;
        $output['status'] = $this->status;

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
