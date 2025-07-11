<?php

namespace WPS\Ai\Application\UseCases\SendRequestToGenerateContentBulk;

use Error;

final class SendRequestToGenerateContentBulkResponse
{
    /**
     * Errors.
     *
     * @var array<Error>
     */
    public $errors;

    /**
     * Response action.
     *
     * @var array<SendRequestToGenerateContentBulkResponseAction>
     */
    public $response_actions;

    /**
     * Code.
     *
     * @var int
     */
    public $code;

    /**
     * Message.
     *
     * @var string
     */
    public $message;

    /**
     * Status.
     *
     * @var string
     */
    public $status;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->errors = array();
        $this->response_actions = array();
        $this->code = 200;
        $this->status = 'success';
        $this->message = '';
    }

    /**
     * Add an error.
     *
     * @param Error $error The error.
     *
     * @return void
     */
    public function addError($error)
    {
        $this->errors[] = $error;
        $this->status = 'error';
    }

    /**
     * Get errors.
     *
     * @return array<Error> The errors.
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * Add a response action.
     *
     * @param SendRequestToGenerateContentBulkResponseAction $response_action The response action.
     *
     * @return void
     */
    public function addResponseAction($response_action)
    {
        $this->response_actions[] = $response_action;
    }

    /**
     * Set code.
     *
     * @param int $code The code.
     */
    public function setCode($code)
    {
        $this->code = $code;
    }

    /**
     * Get response actions.
     *
     * @return array<SendRequestToGenerateContentBulkResponseAction> The response actions.
     */
    public function getResponseActions()
    {
        return $this->response_actions;
    }

    /**
     * Get code.
     *
     * @return int The code.
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Get status.
     *
     * @return string The status.
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set message.
     *
     * @param string $message The message.
     */
    public function setMessage($message)
    {
        $this->message = $message;
    }

    /**
     * Get message.
     *
     * @return string The message.
     */
    public function getMessage()
    {
        return $this->message;
    }
}
