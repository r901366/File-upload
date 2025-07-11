<?php
/**
 * WP-Script API Class
 *
 * @package admin\class
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WPSCORE_Api' ) ) {
	/**
	 * WPSCORE_Api Singleton Class
	 *
	 * @phpstan-type ApiAuthParams array{license_key:string,signature:stdClass,server_addr:string,server_name:string,core_version:string,time:float}
	 *
	 * @since 1.3.9
	 */
	final class WPSCORE_Api {
		/**
		 * The WP-Script base API URL.
		 *
		 * @var string
		 */
		private $api_base_url;

		/**
		 * The WP-Script auth params array.
		 *
		 * @var ApiAuthParams
		 */
		private $api_auth_params;

		/**
		 * The instance of the CORE plugin
		 *
		 * @var WPSCORE_Api $instance
		 * @static
		 */
		private static $instance;

		/**
		 * Singleton constructor
		 *
		 * @param string        $api_base_url     The WP-Script base API URL to inject.
		 * @param ApiAuthParams $api_auth_params  The WP-Script auth params array to inject.
		 *
		 * @return void
		 */
		private function __construct( $api_base_url, $api_auth_params ) {
			$this->api_base_url    = $api_base_url;
			$this->api_auth_params = $api_auth_params;
		}

		/**
		 * __clone method
		 *
		 * @return void
		 */
		public function __clone() {
			_doing_it_wrong( __FUNCTION__, esc_html__( 'Do not clone or wake up this class', 'wpscore_lang' ), '1.0' );}

		/**
		 * __wakeup method
		 *
		 * @return void
		 */
		public function __wakeup() {
			_doing_it_wrong( __FUNCTION__, esc_html__( 'Do not clone or wake up this class', 'wpscore_lang' ), '1.0' );
		}

		/**
		 * Instance method
		 *
		 * @param string $api_base_url     The WP-Script base API URL to inject.
		 * @param array  $api_auth_params  The WP-Script auth params array to inject.
		 *
		 * @return WPSCORE_Api
		 */
		public static function instance( $api_base_url, $api_auth_params ) {
			if ( ! isset( self::$instance ) && ! ( self::$instance instanceof WPSCORE_Api ) ) {
				self::$instance = new WPSCORE_Api( $api_base_url, $api_auth_params );
			}
			return self::$instance;
		}

		/**
		 * Get WP-Script.com API url
		 *
		 * @param string              $api_route      The API route to call. (i.e. 'init', 'amve/get_feed').
		 * @param array<string,mixed> $method_params  An array with the method params.
		 *
		 * @throws WPSCORE_Exception If $params is not an array.
		 *
		 * @return string The WP-Sccript API url.
		 */
		public function get_api_url( $api_route, $method_params ) {
			if ( ! is_array( $method_params ) ) {
				throw new WPSCORE_Exception( esc_html__( 'Params must be an array', 'wpscore_lang' ), 0 );
			}
			$api_params = $this->prepare_api_params( $method_params );
			return $this->api_base_url . '/' . $api_route . '/' . $api_params;
		}

		/**
		 * Prepare the api params as a base64 + serialized string.
		 * 1- Merge $this->api_auth_params & $method_params arrays
		 *    $this->api_auth_params can be overridden by $method_params (i.e 'license_key')
		 * 2- base64_encode + serialize the merged arrays.
		 *
		 * @see get_api_url() that uses this method.
		 *
		 * @param array<string,mixed> $method_params The params to prepare.
		 *
		 * @throws WPSCORE_Exception If $params is not an array.
		 *
		 * @return string Params as a base64/serialized string.
		 */
		private function prepare_api_params( $method_params = array() ) {
			if ( ! is_array( $method_params ) ) {
				throw new WPSCORE_Exception( esc_html__( 'Params must be an array', 'wpscore_lang' ), 0 );
			}
			// 1- Merge auth and method params arrays
			$mergerd_params = array_merge( $this->api_auth_params, $method_params );

			// 2- base64_encode + serialize the merged arrays
			// PHPCS:ignore WordPress.PHP.DiscouragedPHPFunctions.serialize_serialize
			return base64_encode( serialize( $mergerd_params ) );
		}

		/**
		 * Call API with some given_params
		 *
		 * @param string              $api_route      The API route to call.
		 * @param array<string,mixed> $method_params  An array with the method params.
		 *
		 * @throws WPSCORE_Exception If ...:
		 * - $response is a wp error.
		 * - $response_body is not an object.
		 * - $response_body->code is 'error'.
		 *
		 * @return object $response_body The response body from the API.
		 */
		public function call_api( $api_route, $method_params ) {
			$api_url  = $this->get_api_url( $api_route, $method_params );
			$args     = array(
				'timeout'   => 10,
				'sslverify' => false,
			);
			$response = wp_remote_get( $api_url, $args );

			if ( is_wp_error( $response ) ) {
				throw new WPSCORE_Exception( esc_html( $response->get_error_message() ), 1 );
			}

			$response_body = json_decode( wp_remote_retrieve_body( $response ) );

			if ( ! is_object( $response_body ) ) {
				throw new WPSCORE_Exception( 'Api response body is null', 1 );
			}
			if ( isset( $response_body->code ) && 'error' === $response_body->code ) {
				$message = isset( $response_body->message ) ? $response_body->message : 'Unknown error';
				throw new WPSCORE_Exception( wp_kses_post( $message ), 1 );
			}

			return $response_body;
		}
	}

	/**
	 * Create the WPSCORE_Api instance in a function and call it.
	 *
	 * @return WPSCORE_Api
	 */
	// phpcs:ignore
	function wpscore_api() {
		return WPSCORE_Api::instance( WPSCORE_API_URL, WPSCORE()->get_api_auth_params() );
	}
	wpscore_api();
}
