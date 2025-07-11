<?php

namespace WPS\RestApi\GenerateContentBulkRequestDto;

class GenerateContentBulkRequestDto
{
    /**
     * Schema used to validate input for creating instances of this class
     *
     * @var array
     */
    private static $schema = [
        '$schema' => 'http://json-schema.org/draft-07/schema#',
        'properties' => [
            'license_key' => [
                'type' => 'string',
                'description' => 'The license key of the user.',
            ],
            'server_name' => [
                'type' => 'string',
                'description' => 'The server name.',
            ],
            'webhook_url' => [
                'type' => 'string',
                'description' => 'The webhook URL to send the generated content to.',
            ],
            'debug' => [
                'type' => 'boolean',
                'description' => 'Whether to enable debug mode or not.',
            ],
            'actions' => [
                'type' => 'array',
                'description' => 'The actions to perform.',
                'items' => [
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
                ],
            ],
        ],
        'required' => [
            'license_key',
            'server_name',
            'webhook_url',
            'debug',
            'actions',
        ],
        'additionalItems' => false,
        'additionalProperties' => false,
    ];

    /**
     * The license key of the user.
     *
     * @var string
     */
    private $licenseKey;

    /**
     * The server name.
     *
     * @var string
     */
    private $serverName;

    /**
     * The webhook URL to send the generated content to.
     *
     * @var string
     */
    private $webhookUrl;

    /**
     * Whether to enable debug mode or not.
     *
     * @var bool
     */
    private $debug;

    /**
     * The actions to perform.
     *
     * @var GenerateContentBulkRequestDtoActionsItem[]
     */
    private $actions;

    /**
     * @param string $licenseKey
     * @param string $serverName
     * @param string $webhookUrl
     * @param bool $debug
     * @param GenerateContentBulkRequestDtoActionsItem[] $actions
     */
    public function __construct(string $licenseKey, string $serverName, string $webhookUrl, bool $debug, array $actions)
    {
        $this->licenseKey = $licenseKey;
        $this->serverName = $serverName;
        $this->webhookUrl = $webhookUrl;
        $this->debug = $debug;
        $this->actions = $actions;
    }

    /**
     * @return string
     */
    public function getLicenseKey()
    {
        return $this->licenseKey;
    }

    /**
     * @return string
     */
    public function getServerName()
    {
        return $this->serverName;
    }

    /**
     * @return string
     */
    public function getWebhookUrl()
    {
        return $this->webhookUrl;
    }

    /**
     * @return bool
     */
    public function getDebug()
    {
        return $this->debug;
    }

    /**
     * @return GenerateContentBulkRequestDtoActionsItem[]
     */
    public function getActions()
    {
        return $this->actions;
    }

    /**
     * @param string $licenseKey
     * @return self
     */
    public function withLicenseKey(string $licenseKey)
    {
        $validator = new \JsonSchema\Validator();
        $validator->validate($licenseKey, self::$schema['properties']['license_key']);
        if (!$validator->isValid()) {
            throw new \InvalidArgumentException($validator->getErrors()[0]['message']);
        }

        $clone = clone $this;
        $clone->licenseKey = $licenseKey;

        return $clone;
    }

    /**
     * @param string $serverName
     * @return self
     */
    public function withServerName(string $serverName)
    {
        $validator = new \JsonSchema\Validator();
        $validator->validate($serverName, self::$schema['properties']['server_name']);
        if (!$validator->isValid()) {
            throw new \InvalidArgumentException($validator->getErrors()[0]['message']);
        }

        $clone = clone $this;
        $clone->serverName = $serverName;

        return $clone;
    }

    /**
     * @param string $webhookUrl
     * @return self
     */
    public function withWebhookUrl(string $webhookUrl)
    {
        $validator = new \JsonSchema\Validator();
        $validator->validate($webhookUrl, self::$schema['properties']['webhook_url']);
        if (!$validator->isValid()) {
            throw new \InvalidArgumentException($validator->getErrors()[0]['message']);
        }

        $clone = clone $this;
        $clone->webhookUrl = $webhookUrl;

        return $clone;
    }

    /**
     * @param bool $debug
     * @return self
     */
    public function withDebug(bool $debug)
    {
        $validator = new \JsonSchema\Validator();
        $validator->validate($debug, self::$schema['properties']['debug']);
        if (!$validator->isValid()) {
            throw new \InvalidArgumentException($validator->getErrors()[0]['message']);
        }

        $clone = clone $this;
        $clone->debug = $debug;

        return $clone;
    }

    /**
     * @param GenerateContentBulkRequestDtoActionsItem[] $actions
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
     * @return GenerateContentBulkRequestDto Created instance
     * @throws \InvalidArgumentException
     */
    public static function buildFromInput($input, bool $validate = true)
    {
        $input = is_array($input) ? \JsonSchema\Validator::arrayToObjectRecursive($input) : $input;
        if ($validate) {
            static::validateInput($input);
        }

        $licenseKey = $input->{'license_key'};
        $serverName = $input->{'server_name'};
        $webhookUrl = $input->{'webhook_url'};
        $debug = (bool)($input->{'debug'});
        $actions = array_map(function ($i) use ($validate) {
            return GenerateContentBulkRequestDtoActionsItem::buildFromInput($i, $validate);
        }, $input->{'actions'});

        $obj = new self($licenseKey, $serverName, $webhookUrl, $debug, $actions);

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
        $output['license_key'] = $this->licenseKey;
        $output['server_name'] = $this->serverName;
        $output['webhook_url'] = $this->webhookUrl;
        $output['debug'] = $this->debug;
        $output['actions'] = array_map(function (GenerateContentBulkRequestDtoActionsItem $i) {
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
        $this->actions = array_map(function (GenerateContentBulkRequestDtoActionsItem $i) {
            return clone $i;
        }, $this->actions);
    }
}
