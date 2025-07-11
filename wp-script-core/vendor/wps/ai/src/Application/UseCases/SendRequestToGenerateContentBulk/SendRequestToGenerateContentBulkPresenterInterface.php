<?php

namespace WPS\Ai\Application\UseCases\SendRequestToGenerateContentBulk;

interface SendRequestToGenerateContentBulkPresenterInterface
{
    /**
     * Present the result.
     *
     * @param SendRequestToGenerateContentBulkResponse $response The response.
     *
     * @return void
     */
    public function present($response);
}
