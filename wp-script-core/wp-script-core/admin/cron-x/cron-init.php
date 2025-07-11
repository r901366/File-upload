<?php
/**
 * Cron file.
 *
 * @package CORE\Cron
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Lauch WPSCORE->init() on cron init (= on plugin activation)
 *
 * @return void
 */
function wpscore_cron_init() {
	WPSCORE()->init( true );
}

// Clear old cron jobs and schedule new one.
wp_clear_scheduled_hook( 'WPSCORE_init' );
if ( ! wp_next_scheduled( 'wpscore_init' ) ) {
	wp_schedule_event( time(), 'daily', 'wpscore_init' );
}
