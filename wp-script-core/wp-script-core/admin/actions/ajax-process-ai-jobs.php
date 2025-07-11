<?php
/**
 * Ajax Method to process AI jobs.
 *
 * @package admin\actions
 */

use Core\Ai\Ai_Service_Api;
use WPS\Ai\Application\Services\AiUtils;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Delete WP-Script Core logs.
 *
 * @return void
 */
function wpscore_process_ai_jobs() {
	check_ajax_referer( 'ajax-nonce', 'nonce' );
	try {
		$ai_service_api = new Ai_Service_Api();
		$ai_service_api->process_pending_ai_jobs( 'edit.php' );
		wp_send_json_success(
			array(
				'code'    => 'success',
				'message' => 'AI jobs processed successfully',
				'data'    => array(
					'status'           => 200,
					'wps_credits_left' => AiUtils::getCreditsLeftFromCache(),
				),
			),
			200
		);
	} catch ( Exception $e ) {
		wp_send_json_error(
			array(
				'code'    => 'error',
				'message' => $e->getMessage(),
				'data'    => array(
					'status' => 500,
				),
			),
			500
		);
	}
}
add_action( 'wp_ajax_wpscore_process_ai_jobs', 'wpscore_process_ai_jobs' );
