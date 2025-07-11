<?php

namespace WPS\Ai\Application\UseCases\SendRequestToGenerateContentBulk;

class SendRequestToGenerateContentBulkRequest
{
    /**
     * @var SendRequestToGenerateContentBulkRequestAction[]
     */
    private $actions;

    /**
     * Constructor.
     *
     * @param string $webhook_url Webhook URL.
     * @param SendRequestToGenerateContentBulkRequestAction[] $actions Items.
     *
     * @param SendRequestToGenerateContentBulkRequestAction[] $items Items.
     */
    public function __construct($actions)
    {
        $this->actions = $actions;
    }

    /**
     * Get the actions.
     *
     * @return SendRequestToGenerateContentBulkRequestAction[]
     */
    public function getActions()
    {
        return $this->actions;
    }
}
