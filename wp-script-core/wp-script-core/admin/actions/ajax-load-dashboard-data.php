<?php
/**
 * Ajax Method to load dashboard data.
 *
 * @api
 * @package admin\actions
 */

use WPS\Utils\Application\Services\LinkBuilder;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Load dashboard page data.
 *
 * @return void
 */
function wpscore_load_dashboard_data() {
	check_ajax_referer( 'ajax-nonce', 'nonce' );
	$current_user = wp_get_current_user();

	$installed_products = WPSCORE()->get_installed_products();
	$products           = WPSCORE()->get_products_options( array( 'data', 'eval' ) );
	if ( is_array( $installed_products ) && count( $installed_products ) === 0 ) {
		$installed_products = array(
			'themes'  => array(),
			'plugins' => array(),
		);
		$products           = array(
			'themes'  => array(),
			'plugins' => array(),
		);
	}

	$data = array(
		'user_email'         => $current_user->user_email,
		'core'               => WPSCORE()->get_core_options(),
		'installed_products' => $installed_products,
		'full_lifetime'      => WPSCORE()->get_option( 'full_lifetime' ),
		'products'           => $products,
		'user_has_license'   => '' !== (string) WPSCORE()->get_license_key(),
	);
	wp_send_json( $data );
}
add_action( 'wp_ajax_wpscore_load_dashboard_data', 'wpscore_load_dashboard_data' );
