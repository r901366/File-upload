<?php
/**
 * Ajax Method to get site verification data.
 *
 * @api
 * @package admin\actions
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Get site verification data.
 *
 * @return void
 */
function wpscore_get_site_verification_data() {
	check_ajax_referer( 'ajax-nonce', 'nonce' );
	wp_send_json(
		array(
			'site_key'                        => WPSCORE()->get_option( 'site_key' ),
			'deadline_to_verify_site_in_days' => WPSCORE()->get_deadline_to_verify_site_in_days(),
		)
	);
}
add_action( 'wp_ajax_wpscore_get_site_verification_data', 'wpscore_get_site_verification_data' );
