<?php
/**
 * Plugin Quiet Skin.
 *
 * @package admin\class
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';

/**
 * Quiet Skin for Plugin Installer.
 */
class WPSCORE_Plugin_Quiet_Skin extends Plugin_Installer_Skin {
	/**
	 * Displays a message about the update.
	 *
	 * @param string $feedback Message data.
	 * @param mixed  ...$args  Optional text replacements.
	 */
	public function feedback( $feedback, ...$args ) {
		// just keep it quiet.
	}
}
