<?php
/**
 * Ajax Method to check user account.
 *
 * @api
 * @package admin\actions
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Check if the user account already exists or not
 *
 * @return void
 */
function wpscore_check_account() {
	check_ajax_referer( 'ajax-nonce', 'nonce' );

	if ( ! isset( $_POST['email'] ) ) {
		wp_die( 'email needed' );
	}

	$api_params = array(
		'core_version' => WPSCORE_VERSION,
		'server_addr'  => WPSCORE()->get_server_addr(),
		'server_name'  => WPSCORE()->get_server_name(),
		'signature'    => WPSCORE()->get_client_signature(),
		'time'         => ceil( time() / 1000 ), // 100
		'email'        => sanitize_email( wp_unslash( $_POST['email'] ) ),
	);

	$args = array(
		'timeout'   => 10,
		'sslverify' => false,
	);

	// PHPCS:ignore WordPress.PHP.DiscouragedPHPFunctions.serialize_serialize
	$base64_params = base64_encode( serialize( $api_params ) );

	// Send the request.
	$response = wp_remote_get( WPSCORE()->get_api_url( 'check_account', $base64_params ), $args );

	$response_content_type = wp_remote_retrieve_header( $response, 'content-type' );
	$response_content_type = is_array( $response_content_type ) ? $response_content_type[0] : $response_content_type;

	if ( ! is_wp_error( $response ) && strpos( $response_content_type, 'application/json' ) !== false ) {

		$response_body = json_decode( wp_remote_retrieve_body( $response ) );

		if ( 200 !== $response_body->data->status ) {
			WPSCORE()->write_log( 'error', 'Connection to API (check_account) failed (status: <code>' . $response_body->data->status . '</code> message: <code>' . $response_body->message . '</code>)', __FILE__, __LINE__ );
		} elseif ( 'success' === $response_body->code ) {
				WPSCORE()->update_license_key( $response_body->data->license );
				WPSCORE()->init( true );
		} else {
			WPSCORE()->write_log( 'error', 'Connection to API (check_account) failed (status: <code>' . $response_body->data->status . '</code> message: <code>' . $response_body->message . '</code>)', __FILE__, __LINE__ );
		}

		wp_send_json( $response_body );

	} else {
		WPSCORE()->update_license_key( '' );
		$message = wp_json_encode( $response );
		$message = false === $message ? 'Unknown error' : $message;
		WPSCORE()->write_log( 'error', 'Connection to API (check_account) failed (status: <code>' . $message . '</code>)', __FILE__, __LINE__ );
		if ( strpos( $message, 'cURL error 35' ) !== false ) {
			$message = 'Please update your cUrl version';
		}
		$output = array(
			'code'    => 'error',
			'message' => $message,
		);
		wp_send_json( $output );
	}
}
add_action( 'wp_ajax_wpscore_check_account', 'wpscore_check_account' );
