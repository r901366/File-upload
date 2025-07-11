<?php
/**
 * Admin Action plugin file.
 *
 * @package AMVE\Admin\Actions
 */

use WPS\Ai\Application\Services\AiUtils;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Change feed status in Ajax.
 *
 * @since 1.0.0
 *
 * @return void
 */
function wpscore_get_wps_credits_left() {
	check_ajax_referer( 'ajax-nonce', 'nonce' );
	try {
		$credits_left = AiUtils::getCreditsLeft();
		wp_send_json_success(
			array(
				'wps_credits_left' => $credits_left,
			)
		);
	} catch ( Exception $e ) {
		wp_send_json_error(
			array(
				'message' => $e->getMessage(),
			)
		);
	}
}
add_action( 'wp_ajax_wpscore_get_wps_credits_left', 'wpscore_get_wps_credits_left' );
