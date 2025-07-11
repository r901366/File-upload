<?php

namespace WPS\Ai\Application\UseCases\SendRequestToGenerateContentBulk;

use Error;
use WPS\Ai\Application\UseCases\UseCaseInterface;
use WPS\Ai\Domain\Repositories\AiJobRepositoryInterface;

class SendRequestToGenerateContentBulk implements UseCaseInterface
{
    /**
     * The AI job repository.
     *
     * @var AiJobRepositoryInterface
     */
    private $aiJobRepository;

    /**
     * The presenter.
     *
     * @var SendRequestToGenerateContentBulkPresenterInterface
     */
    private $presenter;

    public function __construct(
        AiJobRepositoryInterface $aiJobRepository,
        SendRequestToGenerateContentBulkPresenterInterface $presenter
    ) {
        $this->aiJobRepository = $aiJobRepository;
        $this->presenter = $presenter;
    }

    /**
     * Execute the usecase.
     *
     * @param SendRequestToGenerateContentBulkRequest $request The request.
     *
     * @return void
     */
    public function execute($request)
    {
        // To be implemented.
    }
}
