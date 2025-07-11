<?php
/**
 * Cron file.
 *
 * @package CORE\Cron
 */

use Core\Ai\Ai_Service_Api;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Add every five minutes cron schedule.
 *
 * @param array<string,array{interval:int,display:string}> $schedules Schedules.
 *
 * @return array<string,array{interval:int,display:string}> Schedules.
 */
function wpscore_add_every_five_min_cron_schedule( $schedules ) {
	$schedules['every_five_mins'] = array(
		'interval' => 300, // 6 mins in seconds.
		'display'  => __( 'Every 5 mins', 'wpscore_lang' ),
	);
	return $schedules;
}
add_filter( 'cron_schedules', 'wpscore_add_every_five_min_cron_schedule' );

// Schedule bulk AI cron job.
if ( ! wp_next_scheduled( 'wpscore_bulk_ai' ) ) {
	wp_schedule_event( time(), 'every_five_mins', 'wpscore_bulk_ai' );
}

/**
 * Callback for bulk AI cron job.
 *
 * @return void
 */
function wpscore_cron_bulk_ai() {
	$ai_service_api = new Ai_Service_Api();
	$ai_service_api->process_pending_ai_jobs( 'bulk-cron' );
}
