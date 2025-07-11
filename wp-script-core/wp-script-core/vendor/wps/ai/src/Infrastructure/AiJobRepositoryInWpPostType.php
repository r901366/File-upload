<?php

namespace WPS\Ai\Infrastructure;

use Exception;
use WP_Post;
use WPS\Ai\Application\Services\AiDebugMode;
use WPS\Ai\Domain\Entities\AiJob;
use WPS\Ai\Domain\Repositories\AiJobRepositoryInterface;
use WPS\Ai\Domain\ValueObjects\AiJobId;
use WPS\Ai\Domain\ValueObjects\AiJobStatus;
use WPS\Ai\Domain\ValueObjects\AiJobType;

/**
 * Repository class to manipulate AI jobs using a custom post type.
 */
final class AiJobRepositoryInWpPostType implements AiJobRepositoryInterface
{
    /**
     * Custom post type name for AI jobs.
     *
     * @return string Custom post type name.
     */
    public static function getCustomPostTypeName()
    {
        return 'wps_ai_job';
    }

    /**
     * Get the post meta key with prefix for a given meta key.
     *
     * @param ?string $meta_key Meta key.
     *
     * @return string The post meta key with prefix.
     */
    private static function getPostMetaKeyWithPrefix($meta_key = null)
    {
        $prefix = 'wps_ai_job_';
        if (is_string($meta_key)) {
            return $prefix . $meta_key;
        }

        return $prefix;
    }

    /**
     * Get the dynamic param post meta key with prefix for a given meta key.
     *
     * @param ?string $param_meta_key Meta key.
     *
     * @return string The post meta key with prefix.
     */
    private static function getPostMetaDynamicParamKeyWithPrefix($param_meta_key = null)
    {
        $prefix = 'wps_ai_job_param_';
        if (is_string($param_meta_key)) {
            return $prefix . $param_meta_key;
        }

        return $prefix;
    }

    /**
     * Register the custom post type for AI jobs.
     *
     * @return void
     */
    public static function registerPostType()
    {
        if (post_type_exists(self::getCustomPostTypeName())) {
            return;
        }
        register_post_type(
            self::getCustomPostTypeName(),
            array(
                'label'           => 'AI Jobs',
                'public'          => false,
                'show_ui'         => AiDebugMode::isEnabled(),
                'capability_type' => 'post',
                'supports'        => array( 'title', 'custom-fields' ),
                'show_in_rest'    => false,
            )
        );

        self::addAdminColumns();
        self::filterPostMetaInserted();
    }

    /**
     * Add admin columns to the custom post type.
     *
     * @return void
     */
    private static function addAdminColumns()
    {
        add_filter(
            'manage_' . self::getCustomPostTypeName() . '_posts_columns',
            function ($columns) {
                $columns['title'] = 'Job ID';
                $columns[self::getPostMetaKeyWithPrefix('type')]    = 'Job Type';
                $columns[self::getPostMetaKeyWithPrefix('status')]  = 'Job Status';
                $columns[self::getPostMetaKeyWithPrefix('content')] = 'Content';
                $columns[self::getPostMetaKeyWithPrefix('params')]  = 'Params';
                return $columns;
            }
        );

        add_action(
            'manage_' . self::getCustomPostTypeName() . '_posts_custom_column',
            function ($column, $post_id) {
                try {
                    $ai_job = self::hydrate(get_post($post_id));
                    switch ($column) {
                        case 'title':
                            echo esc_html($ai_job->getAiJobId()->getValue());
                            break;
                        case self::getPostMetaKeyWithPrefix('type'):
                            echo esc_html($ai_job->getType()->getValue());
                            break;
                        case self::getPostMetaKeyWithPrefix('status'):
                            echo esc_html($ai_job->getStatus()->getValue());
                            break;
                        case self::getPostMetaKeyWithPrefix('content'):
                            echo esc_html($ai_job->getContent());
                            break;
                        case self::getPostMetaKeyWithPrefix('params'):
                            echo '<ul>';
                            foreach ($ai_job->getParams() as $key => $value) {
                                echo '<li><strong>' . esc_html($key) . ':</strong> ' . esc_html($value) . '<br>';
                            }
                            echo '</ul>';
                            break;
                    }
                } catch (\Exception $e) {
                    error_log('Failed to display AI job column: ' . $e->getMessage());
                }
            },
            10,
            2
        );
    }

    /**
     * Prevent unwanted post meta inserted to ensure that the meta key is prefixed.
     *
     * @return void
     */
    private static function filterPostMetaInserted()
    {
        add_filter(
            'add_post_metadata',
            function ($check, $object_id, $meta_key) {
                if ('_edit_lock' === $meta_key || '_edit_last' === $meta_key) {
                    return $check;
                }
                $is_wps_ai_job_cpt = get_post_type($object_id) === self::getCustomPostTypeName();
                $is_wps_ai_job_meta = strpos($meta_key, self::getPostMetaKeyWithPrefix()) === 0;
                if ($is_wps_ai_job_cpt && ! $is_wps_ai_job_meta) {
                    return false;
                }
                return $check;
            },
            10,
            3
        );

        add_filter(
            'update_post_metadata',
            function ($check, $object_id, $meta_key) {
                if ('_edit_lock' === $meta_key || '_edit_last' === $meta_key) {
                    return $check;
                }
                $is_wps_ai_job_cpt = get_post_type($object_id) === self::getCustomPostTypeName();
                $is_wps_ai_job_meta = strpos($meta_key, self::getPostMetaKeyWithPrefix()) === 0;
                if ($is_wps_ai_job_cpt && ! $is_wps_ai_job_meta) {
                    return false;
                }
                return $check;
            },
            10,
            3
        );
    }

    /**
     * Get all AI jobs from the custom post type.
     *
     * @param ?array{ai_job_id?:AiJobId,ai_job_type?:AiJobType,ai_job_status?:AiJobStatus,post_id_to_update?:string,processed_from?:string,limit?:int} $params Params.
     *
     * @return array<AiJob> AI jobs.
     */
    public function find($params = array())
    {

        $query_args = array(
            'post_type'      => self::getCustomPostTypeName(),
            'post_status'    => 'publish',
            'posts_per_page' => isset($params['limit']) ? $params['limit'] : -1,
            'meta_query'     => array(
                'relation' => 'AND',
            ),
        );

        if (isset($params['ai_job_id'])) {
            $query_args['title'] = $params['ai_job_id']->getValue();
        }

        if (isset($params['ai_job_type'])) {
            $query_args['meta_query'][] = array(
                'key'   => self::getPostMetaKeyWithPrefix('type'),
                'value' => $params['ai_job_type']->getValue(),
            );
        }

        if (isset($params['ai_job_status'])) {
            $query_args['meta_query'][] = array(
                'key'   => self::getPostMetaKeyWithPrefix('status'),
                'value' => $params['ai_job_status']->getValue(),
            );
        }

        if (isset($params['post_id_to_update'])) {
            $query_args['meta_query'][] = array(
                'key'   => self::getPostMetaDynamicParamKeyWithPrefix('post_id_to_update'),
                'value' => $params['post_id_to_update'],
            );
        }

        if (isset($params['empty_post_id_to_update'])) {
            $query_args['meta_query'][] = array(
                'key'     => self::getPostMetaDynamicParamKeyWithPrefix('post_id_to_update'),
                'compare' => 'NOT EXISTS',
            );
        }

        if (isset($params['processed_from'])) {
            $query_args['meta_query'][] = array(
                'key'   => self::getPostMetaDynamicParamKeyWithPrefix('processed_from'),
                'value' => $params['processed_from'],
            );
        }

        $query   = new \WP_Query($query_args);
        $ai_jobs = array();

        foreach ($query->posts as $post) {
            try {
                if (! $post instanceof WP_Post) {
                    continue;
                }
                $ai_jobs[] = self::hydrate($post);
            } catch (\Exception $e) {
                // Ignore invalid AI jobs.
                error_log($e->getMessage());
            }
        }

        return $ai_jobs;
    }

    /**
     * Save an AI job to the custom post type.
     *
     * @param AiJob $ai_job Data to save.
     *
     * @throws \Exception If the post could not be saved.
     *
     * @return string The ID.
     */
    public function save($ai_job)
    {
        $meta_input = array(
            self::getPostMetaKeyWithPrefix('type')    => $ai_job->getType()->getValue(),
            self::getPostMetaKeyWithPrefix('status')  => $ai_job->getStatus()->getValue(),
            self::getPostMetaKeyWithPrefix('content') => $ai_job->getContent(),
        );

        // Save params independently.
        foreach ($ai_job->getParams() as $key => $value) {
            try {
                $meta_input[ self::getPostMetaDynamicParamKeyWithPrefix((string) $key) ] = (string) $value;
            } catch (\InvalidArgumentException $e) {
                error_log('Failed to save AI job param: ' . $e->getMessage());
                continue;
            }
        }

        $existing_ai_job = $this->findOne($ai_job->getId());

        // If post exists, update it.
        if ($existing_ai_job instanceof AiJob) {
            $update_post_response = wp_update_post(
                array(
                'ID'          => (int) $ai_job->getId(),
                'post_title'  => $ai_job->getAiJobId()->getValue(),
                'meta_input'  => $meta_input,
                )
            );
            if (is_wp_error($update_post_response)) {
                throw new \Exception(
                    'Failed to update AI job: ' . wp_kses_post($update_post_response->get_error_message())
                );
            }
            return $ai_job->getId();
        }

        // Else, create it.
        $insert_created_id = wp_insert_post(
            array(
            'post_type'   => self::getCustomPostTypeName(),
            'post_status' => 'publish',
            'post_title'  => $ai_job->getAiJobId()->getValue(),
            'meta_input'  => $meta_input,
            )
        );

        if (is_wp_error($insert_created_id)) {
            throw new \Exception('Failed to create AI job: ' . wp_kses_post($insert_created_id->get_error_message()));
        }

        return (string) $insert_created_id;
    }

    /**
     * Delete an AI job from the custom post type.
     *
     * @param string $id ID.
     *
     * @return void
     */
    public function delete($id)
    {
        wp_delete_post((int) $id, true);
    }

    /**
     * Find a single AI job by ID.
     *
     * @param string $id The ID.
     *
     * @return ?AiJob AI job or null if not found.
     */
    public function findOne($id)
    {
        $query_args = array(
            'post__in'          => array( (int) $id ),
            'post_type'   => self::getCustomPostTypeName(),
            'post_status' => 'publish',
        );

        $query = new \WP_Query($query_args);

        if (empty($query->posts)) {
            return null;
        }

        if (! $query->posts[0] instanceof \WP_Post) {
            return null;
        }

        return self::hydrate($query->posts[0]);
    }

    /**
     * Hydrate an AiJob entity from a wps_ai_job WordPress custom post type.
     *
     * @param \WP_Post $wps_ai_job WordPress post object.
     *
     * @throws \Exception If the post could not be hydrated.
     *
     * @return AiJob AI job.
     */
    private static function hydrate($wps_ai_job)
    {
        $meta = get_post_meta($wps_ai_job->ID);
        try {
            if (empty($wps_ai_job->post_title)) {
                throw new Exception('Missing AI job ID.');
            }
            if (! isset($meta[self::getPostMetaKeyWithPrefix('type')][0])) {
                throw new Exception('Missing AI job type.');
            }
            if (! isset($meta[self::getPostMetaKeyWithPrefix('status')][0])) {
                throw new Exception('Missing AI job status.');
            }
            if (! isset($meta[self::getPostMetaKeyWithPrefix('content')][0])) {
                throw new Exception('Missing AI job content.');
            }
            $ai_job_id     = AiJobId::from($wps_ai_job->post_title);
            $ai_job_type    = AiJobType::from($meta[self::getPostMetaKeyWithPrefix('type')][0]);
            $ai_job_status  = AiJobStatus::from($meta[self::getPostMetaKeyWithPrefix('status')][0]);
            $ai_job_content = $meta[self::getPostMetaKeyWithPrefix('content')][0];

            // Extract params.
            $ai_job_params = array();
            foreach ($meta as $key => $value) {
                if (strpos($key, self::getPostMetaDynamicParamKeyWithPrefix()) === 0) {
                    $ai_job_params[ substr($key, strlen(self::getPostMetaDynamicParamKeyWithPrefix())) ] = $value[0];
                }
            }

            return new AiJob(
                (string) $wps_ai_job->ID,
                $ai_job_id,
                $ai_job_status,
                $ai_job_type,
                $ai_job_content,
                $ai_job_params
            );
        } catch (\Exception $e) {
            throw new \Exception('Failed to hydrate AI job #' . (string) $wps_ai_job->ID . ': ' . esc_html($e->getMessage()));
        }
    }
}
