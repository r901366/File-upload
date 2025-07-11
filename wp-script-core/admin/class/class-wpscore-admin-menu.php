<?php
/**
 * Menu Class
 *
 * @package admin\class
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Menu Class
 *
 * @since 1.0.0
 */
class WPSCORE_Admin_Menu {
	/**
	 * Constructor method
	 *
	 * @return void
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'register_menus' ) );
	}

	/**
	 * Register the menu callback
	 *
	 * @return void
	 */
	public function register_menus() {
		$menu_icon = 'data:image/svg+xml;base64,' . base64_encode( '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 666.4 436.34"><path fill="black" d="M437.59,129.61a389.31,389.31,0,0,1,52.86,39.69,466.37,466.37,0,0,1,50.91,53.78q5.28,6.67,10.42,13.64a14.54,14.54,0,0,0,19.08,4.14A110.64,110.64,0,0,0,598.92,217c35.85-42.48,33.86-107.17-4.38-147.5A110.82,110.82,0,0,0,413,100.27a14.42,14.42,0,0,0,6.23,18.52Q428.34,123.83,437.59,129.61Z"/><path fill="black" d="M95.92,240.68a14.51,14.51,0,0,0,19-4.14q5.15-7,10.42-13.64A466.89,466.89,0,0,1,176.27,169a387.91,387.91,0,0,1,52.88-39.67q9.25-5.82,18.38-10.75a14.32,14.32,0,0,0,6.27-18.35A110.3,110.3,0,0,0,229.21,65.4c-44-42.09-116.24-39.9-157.7,4.66A110.76,110.76,0,0,0,95.92,240.68Z"/><path fill="black" d="M369.56,264.84a45.68,45.68,0,0,0-36.35,17.93,45.81,45.81,0,0,0-82.16,27.88c0,56.69,82.16,90.82,82.16,90.82s82.06-33.66,82.06-90.84a45.81,45.81,0,0,0-45.81-45.81Z"/></svg>' );
		add_menu_page( WPSCORE_NAME, WPSCORE_NAME, 'manage_options', 'wpscore-dashboard', 'wpscore_dashboard_page', $menu_icon );
		WPSCORE()->generate_sub_menu();
	}
}

$wpscore_menu = new WPSCORE_Admin_Menu();
