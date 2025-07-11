<?php

namespace WPS\Ai\Application\UseCases\SendRequestToGenerateContentBulk;

use WPS\Ai\Domain\Entities\AiJob;

class SendRequestToGenerateContentBulkResponseAction
{
    /**
     * @var SendRequestToGenerateContentBulkRequestAction
     */
    private $request_action;

    /**
     * @var AiJob
     */
    private $ai_job_created;


    /**
     * Constructor.
     *
     * @param SendRequestToGenerateContentBulkRequestAction $request_action The request action.
     * @param AiJob $ai_job_created The AI job created.
     */
    public function __construct($request_action, $ai_job_id, $ai_job_created)
    {
        $this->request_action = $request_action;
        $this->ai_job_created = $ai_job_created;
    }

    /**
     * Get the request action.
     *
     * @return SendRequestToGenerateContentBulkRequestAction The request action.
     */
    public function getRequestAction()
    {
        return $this->request_action;
    }

    /**
     * Get the AI job created.
     *
     * @return AiJob The AI job created.
     */
    public function getAiJobCreated()
    {
        return $this->ai_job_created;
    }
}
