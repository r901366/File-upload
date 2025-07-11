<?php
/**
 * Ajax Method to check user account.
 *
 * @api
 * @package admin\actions
 */

use WPS\Ai\Application\Services\UniqueIdGenerator;
use WPS\Ai\Domain\Entities\AiJob;
use WPS\Ai\Domain\ValueObjects\AiJobId;
use WPS\Ai\Domain\ValueObjects\AiJobStatus;
use WPS\Ai\Domain\ValueObjects\AiJobType;
use WPS\Ai\Infrastructure\AiJobRepositoryInWpPostType;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Check if the user account already exists or not
 *
 * @return void
 */
function wpscore_generate_content_in_posts_listing() {
	try {
		check_ajax_referer( 'ajax-nonce', 'nonce' );
	} catch ( Exception $e ) {
		wp_send_json_error(
			array(
				'message' => 'Invalid nonce',
			)
		);
	}

	if ( ! isset( $_POST['post_id'] ) ) {
		wp_send_json_error(
			array(
				'message' => 'post_id needed',
			)
		);
	}

	if ( ! isset( $_POST['ai_job_type'] ) ) {
		wp_send_json_error(
			array(
				'message' => 'ai_job_type needed',
			)
		);
	}

	$post_id   = sanitize_text_field( wp_unslash( $_POST['post_id'] ) );
	$job_type  = sanitize_text_field( wp_unslash( $_POST['ai_job_type'] ) );
	$ai_job_id = isset( $_POST['ai_job_id'] ) ? sanitize_text_field( wp_unslash( $_POST['ai_job_id'] ) ) : '';
	$post      = get_post( (int) $post_id );

	if ( ! $post instanceof WP_Post ) {
		wp_send_json_error(
			array(
				'message' => 'Post not found',
			)
		);
	}

	$ai_job_repo = new AiJobRepositoryInWpPostType();

	$existing_ai_job = $ai_job_repo->findOne( $ai_job_id );

	// If Ai job exists, update it.
	if ( $existing_ai_job instanceof AiJob ) {
		$ai_job = new AiJob(
			$existing_ai_job->getId(),
			$existing_ai_job->getAiJobId(),
			AiJobStatus::fromPending(),
			$existing_ai_job->getType(),
			'',
			$existing_ai_job->getParams()
		);
		$ai_job_repo->save( $ai_job );

		wp_send_json_success(
			array(
				'status'    => AiJobStatus::fromPending()->getValue(),
				'ai_job_id' => $ai_job_id,
			)
		);
	}

	// Else create a new Ai Job.
	$new_ai_job_unique_id = UniqueIdGenerator::generate();
	$new_ai_job           = new AiJob(
		'',
		AiJobId::from( $new_ai_job_unique_id ),
		AiJobStatus::fromPending(),
		AiJobType::from( $job_type ),
		'',
		array(
			'post_title'        => $post->post_title,
			'post_id_to_update' => $post_id,
		)
	);

	try {
		$new_ai_job_id = $ai_job_repo->save( $new_ai_job );
		wp_send_json_success(
			array(
				'status'    => AiJobStatus::fromPending()->getValue(),
				'ai_job_id' => $new_ai_job_id,
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
add_action( 'wp_ajax_wpscore_generate_content_in_posts_listing', 'wpscore_generate_content_in_posts_listing' );
