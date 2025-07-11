<?php
/**
 * WPSCORE Hooks.
 *
 * @api
 * @package CORE\admin\hooks
 */

use WPS\Ai\Application\Services\AiUtils;
use WPS\Ai\Infrastructure\AiJobRepositoryInWpPostType;
use WPS\Utils\Application\Services\LinkBuilder;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Filter to add new columns when editing posts.
 * - Thumb and Partner
 * - Content Generation status
 *
 * @param array<string,string> $defaults Array of default columns.
 *
 * @return array<string,string> Array of columns.
 */
function wpscore_add_columns( $defaults ) {
	// Add inline style for buttons.
	echo '<style>
		th#title {
			width: 30%;
		}
		th#thumb {
			width: 100px;
		}
		th#taxonomy-actors {
			width: 15%;
		}
		th#ai_status {
			width: 220px;
		}
		.wps-ai-generation-status {
			font-size: 12px;
			position:relative;
			border: 1px solid #c3c4c7;
			box-shadow: 0 1px 1px rgba(0, 0, 0, 0.04);
			border-radius: 5px;
			background: white;
			padding: 10px 5px 10px 10px;
			display:flex;
			justify-content: space-between;
			flex-wrap: wrap;
			align-items: center;
			margin-top: 5px;
			gap: 5px;
			min-height: 30px;
		}
		.wps-ai-type {
			position:absolute;
			font-size: 10px;
			color: #50575e;
			top: -7px;
			left: 7px;
			background: white;
			padding: 0 5px;
			line-height: 12px;
			border-radius: 3px 3px 0 0;
		}
		.wps-ai-status {
			font-size: 11px;
			padding-left: 2px;
		}
		.wps-ai-details {
			line-height: normal;
		}
		.wps-ai-status .dashicons {
			position: absolute;
			left: -10px;
			background: white;
			border-radius: 50%;
			font-size: 18px;
			line-height: 20px;
		}
		.wps-ai-button {
			display: flex;
			align-items: center;
		}
		.wps-ai-cost {
			font-size: 9px;
			font-weight: normal;
			position: absolute;
			bottom: -13px;
			right: 14px;
			background: #fff;
			color: #50575e;
			width: 50px;
			border: 1px solid #c3c4c7;
			border-top: 0;
			border-radius: 5px;
			border-top-left-radius: 0;
			border-top-right-radius: 0;
		}
		.wps-ai-generate-content-button {
			background: linear-gradient(45deg, #e94190 0%, #744de6 100%);
			box-shadow: 0px 20px 30px -10px rgba(221, 62, 136, 0.4);
			color: #fff;
			border: none;
			position: relative;
			border-radius: 50px;
			padding: 5px 10px;
			cursor: pointer;
			line-height: 100%;
			font-size: 12px;
			font-weight: bold;
			gap: 2px;
			width: 80px;
			transform: translateY(-6px);
		}
		.wps-ai-status .loader  {
			width: 60px;
			display: inline-block;
			aspect-ratio: 4;
			background: radial-gradient(circle closest-side,#2271b1 90%,#0000) 0/calc(100%/3) 100% space;
			clip-path: inset(0 100% 0 0);
			animation: l1 2s steps(4) infinite;
			transform: scale(0.25) translate(-90px, 20px);
		}
		@keyframes l1 {to{clip-path: inset(0 -34% 0 0)}}

		.wps-ai-generate-content-button:hover {
			background: linear-gradient(-45deg, #932493 0%, #dd3e88 100%);
		}
		.wps-ai-generate-content-button.show{
			display: flex;
			flex-direction: column;
		}
		.wps-ai-generate-content-button span {
			display: flex;
			align-items: center;
			justify-content: center;
			gap: 2px;
		}
		.wps-ai-generate-content-button.hide{
			display: none;
		}
		.wps-ai-cancel-generation-button {
			box-shadow: 0px 20px 30px -10px rgba(221, 62, 136, 0.4);
			background: #50575e;
			color: #fff;
			border: none;
			border-radius: 50px;
			padding: 5px 10px;
			cursor: pointer;
			line-height: 100%;
			font-size: 12px;
		}
	</style>';

	$defaults['thumb']     = __( 'Thumbnail', 'wpscore_lang' );
	$defaults['ai_status'] = __( 'AI Content Generation', 'wpscore_lang' );
	return $defaults;
}
add_filter( 'manage_edit-post_columns', 'wpscore_add_columns' );

/**
 * Action to add thumb and partner content in columns when editig posts.
 *
 * @param string $name The name of the column to add content in.
 *
 * @return void
 */
function wpscore_columns_content( $name ) {
	/** @var WP_Post $post */
	global $post;
	$ai_job_repo = new AiJobRepositoryInWpPostType();

	switch ( $name ) {
		case 'thumb':
			$partner = get_post_meta( $post->ID, 'partner', true );

			$thumb_url = 'https://res.cloudinary.com/themabiz/image/upload/wpscript/sources/admin-no-image.jpg';
			if ( has_post_thumbnail() ) {
				$thumb_url = get_the_post_thumbnail_url( $post->ID, 'wpscript_thumb_admin' );
			} elseif ( get_post_meta( $post->ID, 'thumb', true ) ) {
				$thumb_url = get_post_meta( $post->ID, 'thumb', true );
			}
			echo '<div style="line-height: 0; display: flex; flex-direction: column; gap: 2px;">';
			echo '<div style="width: 95px; height: 70px; display: flex; justify-content: center; align-items: center; background: #000; border-radius: 5px; overflow: hidden;">';
			echo wp_kses(
				'<img src="' . esc_url( $thumb_url ) . '" alt="' . esc_attr( get_the_title() ) . '" width="95" height="70" style="width: 100%; height: auto; max-width: 95px; object-fit: cover;" />',
				wp_kses_allowed_html(
					array(
						'img' => array(
							'alt'    => array(),
							'class'  => array(),
							'height' => array(),
							'src'    => array(),
							'width'  => array(),
							'style'  => array(),
						),
					)
				)
			);
			echo '</div>';

			if ( '' !== $partner ) {
				$partner_img_url = apply_filters( 'wpscore_partner_img_url', 'https://res.cloudinary.com/themabiz/image/upload/wpscript/sources/' . $partner . '.jpg', $partner );
				echo wp_kses(
					'<img src="' . $partner_img_url . '" alt="' . $partner . '" width="95" height="31" style="width: 100%; height: auto; max-width: 95px; border-radius: 5px;"  />',
					wp_kses_allowed_html(
						array(
							'img' => array(
								'alt'    => array(),
								'class'  => array(),
								'height' => array(),
								'src'    => array(),
								'width'  => array(),
								'style'  => array(),
							),
						)
					)
				);
			}
			echo '</div>';
			break;
		case 'ai_status':
			// Find ai jobs with the current post id as post_id_to_update.
			$ai_jobs                = $ai_job_repo->find(
				array(
					'post_id_to_update' => (string) $post->ID,
				)
			);
			$data                   = array(
				'pending'    => array(
					'color'        => '#2271b1',
					'border-color' => '#2271b1',
					'icon'         => 'dashicons-clock',
					'label'        => array(
						'title'       => esc_html__( 'Rewriting the title', 'wpscore_lang' ) . ' <span class="loader"></span>',
						'description' => esc_html__( 'Generating the description', 'wpscore_lang' ) . ' <span class="loader"></span>',
					),
					'status'       => 'pending',
				),
				'processing' => array(
					'color'        => '#2271b1',
					'border-color' => '#2271b1',
					'icon'         => 'dashicons-clock',
					'label'        => array(
						'title'       => esc_html__( 'Rewriting the title', 'wpscore_lang' ) . ' <span class="loader"></span>',
						'description' => esc_html__( 'Generating the description', 'wpscore_lang' ) . ' <span class="loader"></span>',
					),
					'status'       => 'processing',
				),
				'error'      => array(
					'color'        => '#d63638',
					'border-color' => '#d63638',
					'icon'         => 'dashicons-no',
					'label'        => array(
						'title'       => esc_html__( 'Error', 'wpscore_lang' ),
						'description' => esc_html__( 'Error', 'wpscore_lang' ),
					),
					'status'       => 'error',
				),
				'success'    => array(
					'color'        => '#00a32a',
					'border-color' => '#00a32a',
					'icon'         => 'dashicons-yes',
					'label'        => array(
						'title'       => esc_html__( 'Rewritten', 'wpscore_lang' ),
						'description' => esc_html__( 'Generated', 'wpscore_lang' ),
					),
					'status'       => 'success',
				),
			);
			$default_status_by_type = array(
				'title'       => array(
					'color'        => '#50575e',
					'border-color' => '#eee',
					'icon'         => '',
					'label'        => esc_html__( 'Not rewritten', 'wpscore_lang' ),
					'button-label' => esc_html__( 'Rewrite', 'wpscore_lang' ),
					'status'       => 'nostatus',
					'details'      => '',
					'ai_job_id'    => '',
					'cost'         => 1,
				),
				'description' => array(
					'color'        => '#50575e',
					'border-color' => '#eee',
					'icon'         => '',
					'label'        => esc_html__( 'No description', 'wpscore_lang' ),
					'button-label' => esc_html__( 'Generate', 'wpscore_lang' ),
					'status'       => 'nostatus',
					'details'      => '',
					'ai_job_id'    => '',
					'cost'         => 3,
				),
			);

			foreach ( $ai_jobs as $ai_job ) {
				if ( ! isset( $data[ $ai_job->getStatus()->getValue() ] ) ) {
					continue;
				}
				switch ( $ai_job->getType()->getValue() ) {
					case 'title':
						$default_status_by_type['title'] = array_merge(
							$default_status_by_type['title'],
							$data[ $ai_job->getStatus()->getValue() ],
							array(
								'label'     => $data[ $ai_job->getStatus()->getValue() ]['label']['title'],
								'details'   => 'error' === $ai_job->getStatus()->getValue() ? $ai_job->getContent() : '',
								'ai_job_id' => $ai_job->getId(),
							)
						);
						break;
					case 'description':
						$default_status_by_type['description'] = array_merge(
							$default_status_by_type['description'],
							$data[ $ai_job->getStatus()->getValue() ],
							array(
								'label'     => $data[ $ai_job->getStatus()->getValue() ]['label']['description'],
								'details'   => 'error' === $ai_job->getStatus()->getValue() ? $ai_job->getContent() : '',
								'ai_job_id' => $ai_job->getId(),
							)
						);
						break;
				}
			}

			// Check if the post is a video post.
			$post_metas            = get_post_meta( $post->ID );
			$valid_video_post_meta = array( 'partner', 'video_id', 'video_url', 'embed', 'trailer_url' );
			$is_video_post         = false;
			foreach ( $valid_video_post_meta as $meta_key ) {
				// Exclude 'youtube' from 'partner' meta key.
				if ( 'partner' === $meta_key && isset( $post_metas[ $meta_key ] ) && 'youtube' === $post_metas[ $meta_key ][0] ) {
					continue;
				}
				if ( isset( $post_metas[ $meta_key ] ) ) {
					$is_video_post = true;
					break;
				}
			}

			echo '<div style="display: flex; flex-direction: column; gap:5px;">';

			if ( ! $is_video_post ) {
				echo '<div class="wps-ai-generation-status">';
				esc_html_e( 'The AI cannot generate content on posts that are not videos.', 'wpscore_lang' );
				echo '</div>';
				return;
			}

			// Check if the AI can generate content based on the current post title.
			$is_ai_ready = AiUtils::canGenerateContentFromTitle( $post->post_title );
			if ( false === $is_ai_ready ) {
				echo '<div class="wps-ai-generation-status">';
				esc_html_e( 'The AI cannot generate content based on the current post title', 'wpscore_lang' );
				echo ':<br><strong>' . esc_html( $post->post_title ) . '</strong>';
				echo '</div>';
				return;
			}

			foreach ( $default_status_by_type as $type => $status ) {
				$icon      = '' !== $status['icon'] ? '<span class="dashicons ' . $status['icon'] . '"></span>' : '';
				$div_id    = 'wps-ai-generation-status-' . $post->ID . '-' . $type;
				$div_class = 'wps-ai-' . $type;
				echo '<div class="wps-ai-generation-status ' . esc_attr( $div_class ) . '" id="' . esc_attr( $div_id ) . '">';
				echo wp_kses_post( '<span class="wps-ai-type">' . ucfirst( $type ) . '</span>' );
				echo '<div class="wps-ai-status-details">';
				echo '<span class="wps-ai-status" data-wps-ai-status="' . esc_attr( $status['status'] ) . '">';
				echo wp_kses_post(
					'<strong style="color:' . $status['color'] .
					';">' . $icon . $status['label'] .
					'</strong>'
				);
				echo '</span>';

				echo '<span class="wps-ai-details">';
				if ( '' !== $status['details'] ) {
					echo wp_kses_post( ' <small style="color:' . $status['color'] . ';">' . $status['details'] . '</small>' );
					if ( 'Not enough credits' === $status['details'] ) {
						echo wp_kses_post(
							'<br><small style="color:' . $status['color'] . ';">' .
							'<a target="_blank" href="' . esc_url( LinkBuilder::get( 'wps-credits' ) ) . '">(' .
							esc_html__( 'Get more Credits', 'wpscore_lang' ) .
							')</a>' .
							'</small>'
						);
					}
				}
				echo '</span>';
				echo '</div>';

				$generate_button_display_class = in_array( $status['status'], array( 'nostatus', 'success', 'error' ), true ) ? 'show' : 'hide';

				$sparkles    = '<img style="width:10px;" src="' . esc_url( WPSCORE_URL . 'admin/assets/images/sparkles-white.svg' ) . '" alt="' . esc_attr__( 'AI content generation', 'wpscore_lang' ) . '" />';
				$credit_word = $status['cost'] > 1 ? esc_html__( 'credits', 'wpscore_lang' ) : esc_html__( 'credit', 'wpscore_lang' );
				$cost        = '<span class="wps-ai-cost">-' . $status['cost'] . ' ' . $credit_word . '</span>';

				$generate_button = '<button class="wps-ai-generate-content-button ' . esc_attr( $generate_button_display_class ) . '" data-post-id="' . esc_attr( (string) $post->ID ) . '" data-type="' . esc_attr( $type ) . '" data-ai-job-id="' . esc_attr( $status['ai_job_id'] ) . '"><span>' . $sparkles . $default_status_by_type[ $type ]['button-label'] . '</span>' . $cost . '</button>';

				echo '<span class="wps-ai-button" style="flex-shrink: 0;">';
				echo wp_kses_post( $generate_button );
				echo '</span>';
				echo '</div>';
			}

			echo '</div>';
			break;
	}
}
add_action( 'manage_posts_custom_column', 'wpscore_columns_content' );


/**
 * Filter to display the original title of posts in the admin when the title has been generated.
 */
add_filter(
	'post_row_actions',
	function ( $actions, $post ) {
		// Show the original title if it exists.
		$original_title = get_post_meta( $post->ID, 'original_title', true );
		if ( '' !== $original_title ) {
			echo '<div><small><strong style="font-size: inherit; display: inline;">' . esc_html__( 'Original title', 'wpscore_lang' ) . ': </strong><span>' . esc_html( $original_title ) . '</span></small></div>';
		}

		return $actions;
	},
	10,
	2
);




/**
 * Hook to create admin thumbnails for posts listings.
 */
add_image_size( 'wpscript_thumb_admin', 95, 70, true );

/**
 * Update client signature when switching theme.
 *
 * @return void
 */
function wpscore_switch_theme() {
	// update site signature.
	WPSCORE()->update_client_signature();
	// call init.
	WPSCORE()->init( true );
}
add_action( 'after_switch_theme', 'wpscore_switch_theme' );

/**
 * Display admin notice when there are some WP-Script products updates.
 *
 * @return void
 */
function wpscript_admin_notice_updates() {
	$current_screen = get_current_screen();
	if ( ! $current_screen instanceof WP_Screen ) {
		return;
	}
	$is_core_page      = 'toplevel_page_wpscore-dashboard' === $current_screen->base ? true : false;
	$available_updates = WPSCORE()->get_available_updates();
	if ( ! current_user_can( 'update_themes' ) || ! current_user_can( 'update_plugins' ) ) {
		return;
	}
	if ( 0 === count( $available_updates ) ) {
		return;
	}
	echo '<div class="notice notice-success is-dismissible">';
	if ( $is_core_page ) {
		echo '<p>' . esc_html__( 'Some new WP-Script products versions are available.', 'wpscore_lang' ) . '</p>';
		echo '<p><i class="fa fa-arrow-down" aria-hidden="true"></i> <strong>' . esc_html__( 'Scroll down on this page and press green update buttons to update products', 'wpscore_lang' ) . '</strong> <i class="fa fa-arrow-down" aria-hidden="true"></i></p>';
	} else {
		echo '<p>' . esc_html__( 'Some new WP-Script products versions are available:', 'wpscore_lang' ) . ' </p>';
		foreach ( $available_updates as $update ) {
			if ( 'CORE' === $update['product_key'] ) {
				$update_url = 'admin.php?page=wpscore-dashboard#wp-script';
				echo '<p>&#10149; ' . esc_html( $update['product_title'] ) . ' <strong>v' . esc_html( $update['product_latest_version'] ) . '</strong> &nbsp;&bull;&nbsp; <a href="' . esc_url( $update_url ) . '">' . esc_html__( 'Update', 'wpscore_lang' ) . '</a></p>';
			} elseif ( isset( $update['product_type'], $update['product_slug'] ) ) {
				$update_url    = 'admin.php?page=wpscore-dashboard#' . $update['product_key'];
				$changelog_url = LinkBuilder::get( $update['product_type'] . '/' . $update['product_slug'] . '/#changelog' );
				echo '<p>&#10149; ' . esc_html( $update['product_title'] ) . ' <strong>v' . esc_html( $update['product_latest_version'] ) . '</strong> &nbsp;&bull;&nbsp; <a href="' . esc_url( $update_url ) . '">' . esc_html__( 'Update', 'wpscore_lang' ) . '</a> | <a href="' . esc_url( $changelog_url ) . '" target="_blank">' . esc_html__( 'Changelog', 'wpscore_lang' ) . '</a></p>';
			}
		}
	}
	echo '</div>';
}
add_action( 'admin_notices', 'wpscript_admin_notice_updates' );

/**
 * Add partner filter to posts list.
 *
 * @param string $post_type The post type.
 *
 * @return void
 */
function wpscript_admin_post_filtering_by_partner_id( $post_type ) {
	if ( 'post' !== $post_type ) {
		return;
	}

	/*
	 * Get partners from the WPS plugins.
	 * @var array $partners Array of partners. Default empty array.
	 * Example: array( array( 'id' => 'partner_id', 'name' => 'partner_name' ) ).
	 */
	$partners = apply_filters( 'wpscore_admin_post_filtering_partners', array() );

	// If partners are not an array, return.
	if ( ! is_array( $partners ) ) {
		return;
	}

	// Skip partners that are not well formatted.
	$partners = array_filter(
		$partners,
		function ( $partner ) {
			return is_array( $partner ) && isset( $partner['id'] ) && isset( $partner['name'] );
		}
	);

	// If partners are empty, return.
	if ( empty( $partners ) ) {
		return;
	}

	wp_verify_nonce( '_wpnonce' );
	$request_partner = isset( $_REQUEST['partner'] ) ? sanitize_meta( 'partner', wp_unslash( $_REQUEST['partner'] ), 'post' ) : '0';

	echo '<select id="partner" name="partner">';
	echo '<option value="0" ' . ( '0' === $request_partner ? 'selected' : '' ) . '>' . esc_html__( 'All Partners', 'wpscore_lang' ) . ' </option>';
	foreach ( $partners as $partner ) {
		$selected = $request_partner === $partner['id'] ? ' selected="selected"' : '';
		echo '<option value="' . esc_attr( $partner['id'] ) . '"' . esc_attr( $selected ) . '>' . esc_html( $partner['name'] ) . ' </option>';
	}
	echo '</select>';
}
add_action( 'restrict_manage_posts', 'wpscript_admin_post_filtering_by_partner_id', 10 );

/**
 * Filter to add partner filter to posts list.
 *
 * @param WP_Query $query The WP_Query instance (passed by reference).
 * @return WP_Query
 */
function wpscript_query_filter_posts_by_partner_id( $query ) {
	if ( ! ( is_admin() && $query->is_main_query() ) ) {
		return $query;
	}

	wp_verify_nonce( '_wpnonce' );

	if ( ! isset( $_REQUEST['partner'] ) ) {
		return $query;
	}

	$partner_id = sanitize_meta( 'partner', wp_unslash( $_REQUEST['partner'] ), 'post' );

	if ( '0' === $partner_id ) {
		return $query;
	}

	$query->query_vars['meta_key']   = 'partner';
	$query->query_vars['meta_value'] = $partner_id;

	return $query;
}
add_filter( 'parse_query', 'wpscript_query_filter_posts_by_partner_id', 10 );

add_action(
	'restrict_manage_posts',
	function ( $post_type ) {
		if ( 'post' !== $post_type ) {
			return;
		}

		$ai_types = array(
			'title'       => __( 'Title', 'wpscore_lang' ),
			'description' => __( 'Description', 'wpscore_lang' ),
		);

		$ai_statuses = array(
			''                    => __( 'All AI statuses', 'wpscore_lang' ),
			'nostatus'            => __( 'No status', 'wpscore_lang' ),
			'pendingorprocessing' => __( 'Processing', 'wpscore_lang' ),
			'success'             => __( 'Generated', 'wpscore_lang' ),
			'error'               => __( 'Error', 'wpscore_lang' ),
		);

		foreach ( $ai_types as $type => $type_label ) {
			$selected_value = isset( $_GET[ 'wps_ai_generation_status_' . $type ] ) ? sanitize_text_field( wp_unslash( $_GET[ 'wps_ai_generation_status_' . $type ] ) ) : '';
			echo '<select name="' . esc_attr( 'wps_ai_generation_status_' . $type ) . '">';
			foreach ( $ai_statuses as $value => $label ) {
				echo '<option value="' . esc_attr( $value ) . '" ' . selected( $selected_value, $value, false ) . '>' . esc_html( $label ) . ' (' . esc_html( $type_label ) . ')</option>';
			}
			echo '</select>';
		}
	}
);

add_filter(
	'posts_request',
	function ( $sql, $query ) {
		if ( ! is_admin() || ! $query->is_main_query() ) {
			return $sql;
		}

		$status_title = isset( $_GET['wps_ai_generation_status_title'] ) ? sanitize_text_field( wp_unslash( $_GET['wps_ai_generation_status_title'] ) ) : '';
		$status_desc  = isset( $_GET['wps_ai_generation_status_description'] ) ? sanitize_text_field( wp_unslash( $_GET['wps_ai_generation_status_description'] ) ) : '';

		if ( '' === $status_title && '' === $status_desc ) {
			return $sql;
		}

		global $wpdb;
		$ai_status_by_type   = array(
			'title'       => $status_title,
			'description' => $status_desc,
		);
		$custom_where_clause = '';

		foreach ( $ai_status_by_type as $ai_type => $ai_status ) {
			// Skip if no status is selected.
			if ( '' === $ai_status ) {
				continue;
			}

			// Skip if status is not valid.
			if ( ! in_array( $ai_status, array( 'nostatus', 'pendingorprocessing', 'success', 'error' ), true ) ) {
				continue;
			}

			// Add custom where clause.
			if ( 'nostatus' === $ai_status ) {
				$custom_where_clause .= $wpdb->prepare(
					" AND NOT EXISTS (
					SELECT 1
					FROM {$wpdb->postmeta} pm1
					INNER JOIN {$wpdb->postmeta} pm2 ON pm1.post_id = pm2.post_id
					WHERE pm1.meta_key = 'wps_ai_job_param_post_id_to_update'
					AND pm2.meta_key = 'wps_ai_job_type'
					AND pm2.meta_value = %s
					AND pm1.meta_value = {$wpdb->posts}.ID
				)",
					array( $ai_type )
				);
			} elseif ( 'pendingorprocessing' === $ai_status ) {
				$custom_where_clause .= $wpdb->prepare(
					" AND EXISTS (
					SELECT 1
					FROM {$wpdb->postmeta} pm1
					INNER JOIN {$wpdb->postmeta} pm2 ON pm1.post_id = pm2.post_id
					INNER JOIN {$wpdb->postmeta} pm3 ON pm1.post_id = pm3.post_id
					WHERE pm1.meta_key = 'wps_ai_job_param_post_id_to_update'
					AND pm2.meta_key = 'wps_ai_job_type'
					AND pm2.meta_value = %s
					AND pm3.meta_key = 'wps_ai_job_status'
					AND pm3.meta_value IN (%s, %s)
					AND pm1.meta_value = {$wpdb->posts}.ID
				)",
					array(
						$ai_type,
						'pending',
						'processing',
					)
				);
			} else {
				$custom_where_clause .= $wpdb->prepare(
					" AND EXISTS (
					SELECT 1
					FROM {$wpdb->postmeta} pm1
					INNER JOIN {$wpdb->postmeta} pm2 ON pm1.post_id = pm2.post_id
					INNER JOIN {$wpdb->postmeta} pm3 ON pm1.post_id = pm3.post_id
					WHERE pm1.meta_key = 'wps_ai_job_param_post_id_to_update'
					AND pm2.meta_key = 'wps_ai_job_type'
					AND pm2.meta_value = %s
					AND pm3.meta_key = 'wps_ai_job_status'
					AND pm3.meta_value = %s
					AND pm1.meta_value = {$wpdb->posts}.ID
				)",
					array(
						$ai_type,
						$ai_status,
					)
				);
			}
		}

		$sql_parts = explode( 'WHERE 1=1', $sql );
		$sql       = $sql_parts[0] . 'WHERE 1=1 ' . $custom_where_clause . $sql_parts[1];

		return $sql;
	},
	10,
	2
);

add_action( 'delete_post', 'wps_delete_ai_jobs_on_post_delete', 10, 1 );

/**
 * Delete AI jobs when post is deleted.
 *
 * @param int $post_id The post ID.
 *
 * @return void
 */
function wps_delete_ai_jobs_on_post_delete( $post_id ) {
	$ai_job_repo = new AiJobRepositoryInWpPostType();
	$ai_jobs     = $ai_job_repo->find( array( 'post_id_to_update' => (string) $post_id ) );
	foreach ( $ai_jobs as $ai_job ) {
		$ai_job_repo->delete( $ai_job->getId() );
	}
}

add_action( 'admin_enqueue_scripts', 'wpscore_admin_enqueue_scripts' );

/**
 * Enqueue scripts and styles for admin.
 *
 * @return void
 */
function wpscore_admin_enqueue_scripts() {

	// Return if the current page is not the post listing.
	if ( 'edit.php' !== $GLOBALS['pagenow'] ) {
		return;
	}

	$js_version = WPSCORE_VERSION . '.' . filemtime( WPSCORE_DIR . '/admin/assets/js/wps-admin-edit-posts.js' );

	wp_enqueue_script( 'wpscore-admin-edit-posts', WPSCORE_URL . 'admin/assets/js/wps-admin-edit-posts.js', array( 'jquery' ), $js_version, true );
	wp_localize_script(
		'wpscore-admin-edit-posts',
		'wpscore_admin_edit_posts_ajax_var',
		array(
			'url'   => str_replace( array( 'http:', 'https:' ), '', admin_url( 'admin-ajax.php' ) ),
			'nonce' => wp_create_nonce( 'ajax-nonce' ),
			'links' => array(
				'wps-credits' => LinkBuilder::get( 'wps-credits' ),
				'ai'          => LinkBuilder::get( 'ai' ),
			),
			'i18n'  => wpscore_localize(),
		)
	);

	add_thickbox();
}
