<?php
namespace AIOSEO\Plugin\Common\SearchStatistics\Api;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Constructs requests to our microservice.
 *
 * @since   4.3.0
 * @version 4.6.2 Moved from Pro to Common.
 */
class Request {
	/**
	 * The base API route.
	 *
	 * @since 4.3.0
	 *
	 * @var string
	 */
	private $base = '';

	/**
	 * The URL scheme.
	 *
	 * @since 4.3.0
	 *
	 * @var string
	 */
	private $scheme = 'https://';

	/**
	 * The current API route.
	 *
	 * @since 4.3.0
	 *
	 * @var string
	 */
	private $route = '';

	/**
	 * The full API URL endpoint.
	 *
	 * @since 4.3.0
	 *
	 * @var string
	 */
	private $url = '';

	/**
	 * The current API method.
	 *
	 * @since 4.3.0
	 *
	 * @var string
	 */
	private $method = '';

	/**
	 * The API token.
	 *
	 * @since 4.3.0
	 *
	 * @var string
	 */
	private $token = '';

	/**
	 * The API key.
	 *
	 * @since 4.3.0
	 *
	 * @var string
	 */
	private $key = '';

	/**
	 * The API trust token.
	 *
	 * @since 4.3.0
	 *
	 * @var string
	 */
	private $tt = '';

	/**
	 * Plugin slug.
	 *
	 * @since 4.3.0
	 *
	 * @var bool|string
	 */
	private $plugin = false;

	/**
	 * The site URL.
	 *
	 * @since 4.3.0
	 *
	 * @var string
	 */
	private $siteurl = '';

	/**
	 * The plugin version.
	 *
	 * @since 4.3.0
	 *
	 * @var string
	 */
	private $version = '';

	/**
	 * The site identifier.
	 *
	 * @since 4.3.0
	 *
	 * @var string
	 */
	private $sitei = '';

	/**
	 * The request args.
	 *
	 * @since 4.3.0
	 *
	 * @var array
	 */
	private $args = [];

	/**
	 * Additional data to append to request body.
	 *
	 * @since 4.3.0
	 *
	 * @var array
	 */
	protected $additionalData = [];

	/**
	 * Class constructor.
	 *
	 * @since 4.3.0
	 *
	 * @param string $route  The API route.
	 * @param array  $args   List of arguments.
	 * @param string $method The API method.
	 */
	public function __construct( $route, $args = [], $method = 'POST' ) {
		$this->base    = trailingslashit( aioseo()->searchStatistics->api->getApiUrl() ) . trailingslashit( aioseo()->searchStatistics->api->getApiVersion() );
		$this->route   = trailingslashit( $route );
		$this->url     = trailingslashit( $this->scheme . $this->base . $this->route );
		$this->method  = $method;
		$this->token   = ! empty( $args['token'] ) ? $args['token'] : aioseo()->searchStatistics->api->auth->getToken();
		$this->key     = ! empty( $args['key'] ) ? $args['key'] : aioseo()->searchStatistics->api->auth->getKey();
		$this->tt      = ! empty( $args['tt'] ) ? $args['tt'] : '';
		$this->args    = ! empty( $args ) ? $args : [];
		$this->siteurl = site_url();
		$this->plugin  = 'aioseo-' . strtolower( aioseo()->versionPath );
		$this->version = aioseo()->version;
		$this->sitei   = ! empty( $args['sitei'] ) ? $args['sitei'] : '';
	}

	/**
	 * Sends and processes the API request.
	 *
	 * @since 4.3.0
	 *
	 * @return mixed The response.
	 */
	public function request() {
		// 1. BUILD BODY
		$body = [];
		if ( ! empty( $this->args ) ) {
			foreach ( $this->args as $name => $value ) {
				$body[ $name ] = $value;
			}
		}

		foreach ( [ 'sitei', 'siteurl', 'version', 'key', 'token', 'tt' ] as $key ) {
			if ( ! empty( $this->{$key} ) ) {
				$body[ $key ] = $this->{$key};
			}
		}

		// If this is a plugin API request, add the data.
		if ( 'info' === $this->route || 'update' === $this->route ) {
			$body['aioseoapi-plugin'] = $this->plugin;
		}

		// Add in additional data if needed.
		if ( ! empty( $this->additionalData ) ) {
			$body['aioseoapi-data'] = maybe_serialize( $this->additionalData );
		}

		if ( 'GET' === $this->method ) {
			$body['time'] = time(); // Add a timestamp to avoid caching.
		}

		$body['timezone'] = gmdate( 'e' );
		$body['ip']       = ! empty( $_SERVER['SERVER_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['SERVER_ADDR'] ) ) : '';

		// 2. EXECUTE REQUEST
		$data = [
			'body'    => wp_json_encode( $body ),
			'timeout' => 120
		];

		if ( 'GET' === $this->method ) {
			$queryString = http_build_query( $body, '', '&' );

			unset( $data['body'] );

			$response = aioseo()->helpers->wpRemoteGet( esc_url_raw( $this->url ) . '?' . $queryString, $data );
		} else {
			$response = aioseo()->helpers->wpRemotePost( esc_url_raw( $this->url ), $data );
		}

		// 5. VALIDATE RESPONSE
		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$responseCode = wp_remote_retrieve_response_code( $response );
		$responseBody = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( is_wp_error( $responseBody ) ) {
			return false;
		}

		if ( 200 !== $responseCode ) {
			$type = ! empty( $responseBody['type'] ) ? $responseBody['type'] : 'api-error';

			if ( empty( $responseCode ) ) {
				return new \WP_Error(
					$type,
					'The API was unreachable.'
				);
			}

			if ( empty( $responseBody ) || ( empty( $responseBody['message'] ) && empty( $responseBody['error'] ) ) ) {
				return new \WP_Error(
					$type,
					sprintf(
						'The API returned a <strong>%s</strong> response',
						$responseCode
					)
				);
			}

			if ( ! empty( $responseBody['message'] ) ) {
				return new \WP_Error(
					$type,
					sprintf(
						'The API returned a <strong>%1$d</strong> response with this message: <strong>%2$s</strong>',
						$responseCode, stripslashes( $responseBody['message'] )
					)
				);
			}

			if ( ! empty( $responseBody['error'] ) ) {
				return new \WP_Error(
					$type,
					sprintf(
						'The API returned a <strong>%1$d</strong> response with this message: <strong>%2$s</strong>', $responseCode,
						stripslashes( $responseBody['error'] )
					)
				);
			}
		}

		// Check if the trust token is required.
		if (
			! empty( $this->tt ) &&
			( empty( $responseBody['tt'] ) || ! hash_equals( $this->tt, $responseBody['tt'] ) )
		) {
			return new \WP_Error( 'validation-error', 'Invalid API request.' );
		}

		return $responseBody;
	}

	/**
	 * Sets additional data for the request.
	 *
	 * @since 4.3.0
	 *
	 * @param  array $data The additional data.
	 * @return void
	 */
	public function setAdditionalData( array $data ) {
		$this->additionalData = array_merge( $this->additionalData, $data );
	}
}