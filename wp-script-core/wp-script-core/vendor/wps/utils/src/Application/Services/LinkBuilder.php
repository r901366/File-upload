<?php

namespace WPS\Utils\Application\Services;

final class LinkBuilder
{
    /**
     * Generate a unique ID.
     *
     * @return string The unique ID.
     */
    public static function get($link, $autologin = true)
    {
        $base_url = WPSCORE_WPSCRIPT_URL;

        // Remove '/' from the start of the link if it's there.
        $link = ltrim($link, DIRECTORY_SEPARATOR);

        // Add base URL to the link if it's not already there.
        if (false === strpos($link, $base_url)) {
            $link = $base_url . DIRECTORY_SEPARATOR . $link;
        }

        // Add '/' to the end of the link if it's not there.
        if (substr($link, -1) !== DIRECTORY_SEPARATOR) {
            $link .= DIRECTORY_SEPARATOR;
        }

        // Don't auto login if the current user is not the main user
        if (false === $autologin) {
            return $link;
        }

        // Don't auto login if the current user has not admin role.
        if (! current_user_can('manage_options')) {
            return $link;
        }

        // Don't auto login if we are not in the admin dashboard.
        if (! is_admin()) {
            return $link;
        }


        return add_query_arg(self::getAutoLoginParams(), $link);
    }

    /**
     * Get the auto login parameters.
     *
     * @return array The auto login parameters or an empty array if auto login is not allowed.
     */
    public static function getAutoLoginParams()
    {
        // Don't auto login if the current user has not admin role.
        if (! current_user_can('manage_options')) {
            return array();
        }

        // Don't auto login if we are not in the admin dashboard.
        if (! is_admin()) {
            return array();
        }

        $server_name = (string) WPSCORE()->get_server_name();
        $site_key    = (string) WPSCORE()->get_option('site_key');
        $license_key = (string) WPSCORE()->get_license_key();

        if ('' === $site_key || '' === $server_name || '' === $license_key) {
            return array();
        }

        $l = self::encodeLicenseKey($license_key);

        $query_args = [
            'sn' => $server_name,
            'sk' => $site_key,
            'l' => $l,
        ];

        return $query_args;
    }

    /**
     * Encode the license key.
     *
     * @param string $license_key The license key.
     *
     * @return string The encoded license key.
     */
    private static function encodeLicenseKey($license_key)
    {
        $license_key = str_replace('wpscript_', '', $license_key);
        $license_key = implode('', array_map(function ($char) {
            return chr(ord($char) + 1);
        }, str_split($license_key)));
        return urlencode($license_key);
    }
}
