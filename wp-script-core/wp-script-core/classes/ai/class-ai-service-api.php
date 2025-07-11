<?php
/**
 * AI Service class.
 *
 * @package Core\Ai
 */

namespace Core\Ai;

use Exception;
use WP_Error;
use WP_Post;
use WPS\Ai\Application\Services\AiDebugMode;
use WPS\Ai\Domain\Entities\AiJob;
use WPS\Ai\Domain\ValueObjects\AiJobStatus;
use WPS\Ai\Infrastructure\AiJobRepositoryInWpPostType;
use WPS\RestApi\GenerateContentBulkRequestDto\GenerateContentBulkRequestDto;
use WPS\RestApi\GenerateContentBulkRequestDto\GenerateContentBulkRequestDtoActionsItem;
use WPS\RestApi\GenerateContentBulkRequestDto\GenerateContentBulkRequestDtoActionsItemParams;
use WPS\RestApi\GenerateContentBulkResponseDto\GenerateContentBulkResponseDto;
use WPS\RestApi\GenerateContentBulkResponseDto\GenerateContentBulkResponseDtoData;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Service class for AI features.
 */
final class Ai_Service_Api {

	/**
	 * Process pending AI jobs.
	 * This method is called by:
	 * - the core ajax action `wpscore_process_ai_jobs`
	 * - the core cron job `wpscore_bulk_ai`
	 *
	 * @param string $from The source of the call.
	 *
	 * @return void
	 */
	public function process_pending_ai_jobs( $from = '' ) {
		$ai_job_repo = new AiJobRepositoryInWpPostType();
		$from       .= ' at ' . gmdate( 'Y-m-d H:i:s' );

		// Retrive 5 latest ai jobs with status 'pending'.
		$ai_jobs = $ai_job_repo->find(
			array(
				'ai_job_status' => AiJobStatus::fromPending(),
				'limit'         => 6,
			)
		);

		if ( 0 === count( $ai_jobs ) ) {
			return;
		}

		// Prepare data to send to WP-Script API Bulk AI endpoint .
		/** @var array<string,GenerateContentBulkRequestDtoActionsItem> $request_actions */
		$request_actions = array();
		foreach ( $ai_jobs as $ai_job ) {
			if ( ! $ai_job->getType()->isDescription() && ! $ai_job->getType()->isTitle() ) {
				$updated_ai_job = new AiJob(
					$ai_job->getId(),
					$ai_job->getAiJobId(),
					AiJobStatus::fromError(),
					$ai_job->getType(),
					'Job type `' . $ai_job->getType()->getValue() . '` not supported. It should be either `title` or `description`.',
					array_merge(
						$ai_job->getParams(),
						array( 'processed_from' => $from )
					)
				);
				$ai_job_repo->save( $updated_ai_job );
				continue;
			}

			// Make sure there is a post to update with the generated content.
			$post = get_post( (int) $ai_job->getParam( 'post_id_to_update' ) );
			if ( ! $post instanceof WP_Post ) {
				$updated_ai_job = new AiJob(
					$ai_job->getId(),
					$ai_job->getAiJobId(),
					AiJobStatus::fromError(),
					$ai_job->getType(),
					'Post #' . (string) $ai_job->getParam( 'post_id_to_update' ) . ' should be updated but it was not found',
					array_merge(
						$ai_job->getParams(),
						array( 'processed_from' => $from )
					)
				);
				$ai_job_repo->save( $updated_ai_job );
				continue;
			}

			if ( $ai_job->getType()->isTitle() ) {
				$request_actions[ $ai_job->getAiJobId()->getValue() ] = new GenerateContentBulkRequestDtoActionsItem(
					'generate_title_from_title',
					$ai_job->getAiJobId()->getValue(),
					new GenerateContentBulkRequestDtoActionsItemParams(
						$post->post_title,
						(string) get_post_meta( $post->ID, 'video_id', true ),
						(string) get_post_meta( $post->ID, 'partner', true )
					)
				);

				$ai_job_to_save = new AiJob(
					$ai_job->getId(),
					$ai_job->getAiJobId(),
					AiJobStatus::fromProcessing(),
					$ai_job->getType(),
					$ai_job->getContent(),
					array_merge(
						$ai_job->getParams(),
						array( 'processed_from' => $from )
					)
				);
				$ai_job_repo->save( $ai_job_to_save );
			}

			if ( $ai_job->getType()->isDescription() ) {
				$request_actions[ $ai_job->getAiJobId()->getValue() ] = new GenerateContentBulkRequestDtoActionsItem(
					'generate_description_from_title',
					$ai_job->getAiJobId()->getValue(),
					new GenerateContentBulkRequestDtoActionsItemParams(
						$post->post_title,
						(string) get_post_meta( $post->ID, 'video_id', true ),
						(string) get_post_meta( $post->ID, 'partner', true )
					)
				);

				$ai_job_to_save = new AiJob(
					$ai_job->getId(),
					$ai_job->getAiJobId(),
					AiJobStatus::fromProcessing(),
					$ai_job->getType(),
					$ai_job->getContent(),
					array_merge(
						$ai_job->getParams(),
						array( 'processed_from' => $from )
					)
				);
				$ai_job_repo->save( $ai_job_to_save );
			}
		}

		// Send request to WP-Script API Bulk AI endpoint .
		$api_response = $this->send_request_to_generate_content_bulk( array_values( $request_actions ) );

		if ( 'success' !== $api_response->getCode() ) {
			// API Call failed .
			WPSCORE()->write_log( 'error', 'Bulk AI API Call failed: ' . $api_response->getMessage(), __FILE__, __LINE__ );
			// TODO: Save all jobs as error here?
		} else {
			// API Call success but any action_response in the api response will be an error.
			// Successful actions will be sent back via the updat-content webhook route.
			foreach ( $api_response->getData()->getActions() as $action_response ) {
				// Make sure that the AI job to update exists in the actions sent to the API.
				$ai_job_with_error = $request_actions[ $action_response->getActionId() ] ?? null;

				if ( ! $ai_job_with_error instanceof GenerateContentBulkRequestDtoActionsItem ) {
					WPSCORE()->write_log( 'error', 'AI job not found: ' . wp_json_encode( $action_response ), __FILE__, __LINE__ );
					continue;
				}

				$ai_job = array_filter(
					$ai_jobs,
					function ( AiJob $ai_job ) use ( $ai_job_with_error ) {
						return $ai_job->getAiJobId()->getValue() === $ai_job_with_error->getActionId();
					}
				);

				if ( 0 === count( $ai_job ) ) {
					WPSCORE()->write_log( 'error', 'AI job not found: ' . wp_json_encode( $ai_job_with_error ), __FILE__, __LINE__ );
					continue;
				}

				$ai_job           = reset( $ai_job );
				$ai_job_status    = null !== $action_response->getAiJobCreated()
				? AiJobStatus::from( $action_response->getAiJobCreated()->getStatus() )
				: AiJobStatus::fromError();
				$ai_job_to_update = new AiJob(
					$ai_job->getId(),
					$ai_job->getAiJobId(),
					$ai_job_status,
					$ai_job->getType(),
					$action_response->getMessage(),
					array_merge(
						$ai_job->getParams(),
						array( 'processed_from' => $from )
					)
				);

				$ai_job_repo->save( $ai_job_to_update );
			}
		}
	}

	/**
	 * Call API to generate a description from a video title.
	 *
	 * @param string $video_id Video ID.
	 * @param string $partner_id Partner ID.
	 * @param string $video_title Video title.
	 *
	 * @return GenerateContentBulkResponseDto The response from the API Call.
	 */
	public function send_request_to_generate_video_description_from_title( $video_id, $partner_id, $video_title ) {
		$action = new GenerateContentBulkRequestDtoActionsItem(
			'generate_description_from_title',
			(string) $video_id,
			new GenerateContentBulkRequestDtoActionsItemParams(
				$video_title,
				$video_id,
				$partner_id
			)
		);
		return $this->send_request_to_generate_content_bulk( array( $action ) );
	}

	/**
	 * Call API to generate a title from the original title.
	 *
	 * @param string $video_id Video ID.
	 * @param string $partner_id Partner ID.
	 * @param string $video_title Video title.
	 *
	 * @return GenerateContentBulkResponseDto The response from the API Call.
	 */
	public function send_request_to_generate_video_title_from_original_title( $video_id, $partner_id, $video_title ) {
		$action = new GenerateContentBulkRequestDtoActionsItem(
			'generate_title_from_title',
			(string) $video_id,
			new GenerateContentBulkRequestDtoActionsItemParams(
				$video_title,
				$video_id,
				$partner_id
			)
		);
		return $this->send_request_to_generate_content_bulk( array( $action ) );
	}

	/**
	 * Call API to generate a title from the original title.
	 *
	 * @param GenerateContentBulkRequestDtoActionsItem[] $actions Parameters to send to the API endpoint call.
	 *
	 * @return GenerateContentBulkResponseDto The response from the API Call.
	 */
	public function send_request_to_generate_content_bulk( $actions ) {
		$webhook_url = get_home_url( null, 'wps/core/v1/update-content' );
		$api_params  = ( new GenerateContentBulkRequestDto(
			WPSCORE()->get_api_auth_params()['license_key'],
			WPSCORE()->get_api_auth_params()['server_name'],
			$webhook_url,
			AiDebugMode::isEnabled(),
			$actions
		) )->toJson();

		try {
			$url  = WPSCORE()->get_api_url( 'generate-content-bulk', '', 'v3' );
			$args = array(
				'timeout'   => 30,
				'sslverify' => false,
				'headers'   => array(
					'Content-Type' => 'application/json',
				),
				'body'      => (string) wp_json_encode( $api_params ),
			);

			$api_response = wp_remote_post( $url, $args );
			if ( $api_response instanceof WP_Error ) {
				$error_message = 'Response error when calling API to generate a description: ' . $api_response->get_error_message();
				WPSCORE()->write_log( 'error', $error_message, __FILE__, __LINE__ );

				return new GenerateContentBulkResponseDto(
					'error',
					$error_message,
					new GenerateContentBulkResponseDtoData(
						200,
						0,
						array()
					)
				);
			}

			$response_data = json_decode( wp_remote_retrieve_body( $api_response ), true );
			return GenerateContentBulkResponseDto::buildFromInput( $response_data );

		} catch ( Exception $e ) {
			$error_message = 'Response error when calling API to generate a description: ' . $e->getMessage();
			WPSCORE()->write_log( 'error', $error_message, __FILE__, __LINE__ );

			return new GenerateContentBulkResponseDto(
				'error',
				$error_message,
				new GenerateContentBulkResponseDtoData(
					200,
					0,
					array()
				)
			);
		}
	}
}
