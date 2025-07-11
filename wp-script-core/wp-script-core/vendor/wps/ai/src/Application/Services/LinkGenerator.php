<?php

namespace WPS\Ai\Application\Services;

final class LinkGenerator
{
    /**
     * Generate a unique ID.
     *
     * @return string The unique ID.
     */
    public static function generate($link)
    {
        $site_key          = WPSCORE()->get_option('site_key');
        $license_key       = WPSCORE()->get_option('wps_license');
        error_log('site_key: ' . $site_key);
        error_log('license_key: ' . $license_key);
        return $link;
    }
}
