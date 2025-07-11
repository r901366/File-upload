<?php

namespace WPS\Ai\Application\UseCases;

interface UseCaseInterface
{
    /**
     * Execute the usecase.
     *
     * @param mixed $request The request.
     *
     * @return void
     */
    public function execute($request);
}
