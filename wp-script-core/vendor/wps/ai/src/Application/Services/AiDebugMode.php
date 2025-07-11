<?php

namespace WPS\Ai\Application\Services;

/**
 * Debug Mode class.
 */
final class AiDebugMode
{
    /**
     * Check if the debug mode is enabled.
     *
     * @return bool true if the debug mode is enabled, false if not.
     */
    public static function isEnabled()
    {
        return isset($_COOKIE['wps-debug-ai']);
    }
}
