<?php

namespace WPS\Ai\Application\Services;

use Exception;

/**
 * Ai utils class.
 */
final class AiUtils
{
    /**
     * Set WPS credits left.
     *
     * @param int $credits_left The number of WPS credits left.
     *
     * @return void
     */
    public static function setCreditsLeft($credits_left)
    {
        WPSCORE()->update_option('wps_credits_left_updated_at', time());
        WPSCORE()->update_option('wps_credits_left', (int) $credits_left);
    }

    /**
     * Set WPS credits left.
     *
     * @param int $credits_left The number of WPS credits left.
     *
     * @return int The number of WPS credits left.
     */
    public static function getCreditsLeftFromCache()
    {
        return (int) WPSCORE()->get_option('wps_credits_left');
    }

    /**
     * Get WPS credits left.
     * It uses a transient to cache the credits left for 5 seconds.
     *
     * @throws Exception If the API call fails.
     *
     * @return int The number of WPS credits left.
     */
    public static function getCreditsLeft()
    {
        // If the credits left are in the transient and the transient was updated less than 5 seconds ago, return the cached value.
        $cached_credits_left = (int) WPSCORE()->get_option('wps_credits_left');
        $cached_credits_left_updated_at = (int) WPSCORE()->get_option('wps_credits_left_updated_at');
        if ($cached_credits_left !== false && $cached_credits_left_updated_at !== false && (time() - $cached_credits_left_updated_at) < 5) {
            return (int) $cached_credits_left;
        }

        $api_params = array(
            'license_key'  => WPSCORE()->get_license_key(),
            'signature'    => WPSCORE()->get_client_signature(),
            'server_addr'  => WPSCORE()->get_server_addr(),
            'server_name'  => WPSCORE()->get_server_name(),
            'core_version' => WPSCORE_VERSION,
            'time'         => ceil(time() / 1000),
        );

        $args = array(
            'timeout'   => 10,
            'sslverify' => false,
        );

		// phpcs:ignore
		$base64_params = base64_encode( serialize( $api_params ) );

        $response = wp_remote_get(WPSCORE()->get_api_url('get_wps_credits_left', $base64_params), $args);

        if (is_wp_error($response)) {
            throw new Exception(esc_html($response->get_error_message()));
        }

        $response_body = wp_remote_retrieve_body($response);

        $response_data = json_decode($response_body, true);

        if (! is_array($response_data)) {
            throw new Exception('Invalid response from the API call to get WPS credits left');
        }

        if (! isset($response_data['code']) || ('success' !== $response_data['code'])) {
            throw new Exception(esc_html($response_data['message']));
        }

        if (! isset($response_data['data']['wps_credits_left'])) {
            throw new Exception('Invalid response from the API call to get WPS credits left');
        }

        // save the credits left in a transient for 5seconds.
        $credits_left = intval($response_data['data']['wps_credits_left']);

        self::setCreditsLeft($credits_left);

        return $credits_left;
    }

    /**
     * Check if the given title can be used to generate content.
     *
     * @param string $title The title to check.
     * @param int    $min_words The minimum number of words the title must have to be valid.
     *
     * @return boolean True if the title can be used to generate content, false if not.
     */
    public static function canGenerateContentFromTitle($title, $min_words = 5)
    {
        if ('' === $title) {
            return false;
        }

        if (str_word_count($title) < $min_words) {
            return false;
        }

        if (! LanguageDetector::isEnglish($title)) {
            return false;
        }

        return true;
    }
}
