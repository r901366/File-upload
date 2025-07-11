<?php

namespace WPS\Ai\Domain\Repositories;

use WPS\Ai\Domain\Entities\AiJob;
use WPS\Ai\Domain\ValueObjects\AiJobStatus;
use WPS\Ai\Domain\ValueObjects\AiJobType;

/**
 * Interface for AI job repository.
 */
interface AiJobRepositoryInterface
{
    /**
     * Get all ai jobs.
     *
     * @param ?array{ai_job_type?:AiJobType,ai_job_status?:AiJobStatus,limit?:int} $params Parameters.
     *
     * @return array<AiJob> AI jobs.
     */
    public function find($params = array());

    /**
     * Save AiJob.
     *
     * @param AiJob $ai_job Data to save.
     *
     * @return void
     */
    public function save($ai_job);

    /**
     * Delete AI job.
     *
     * @param string $id Job ID to delete.
     * @return void
     */
    public function delete($id);

    /**
     * Get AI job from DB.
     *
     * @param string $id Job ID.
     * @return ?AiJob AI job or null if not found.
     */
    public function findOne($id);
}
