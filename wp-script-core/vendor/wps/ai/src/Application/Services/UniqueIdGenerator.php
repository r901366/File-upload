<?php

namespace WPS\Ai\Application\Services;

final class UniqueIdGenerator
{
    /**
     * Generate a unique ID.
     *
     * @return string The unique ID.
     */
    public static function generate()
    {
        return uniqid('wps_ai_job_', false);
    }
}
