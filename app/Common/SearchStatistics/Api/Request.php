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
	 * URL to test connection with.
	 *
	 * @since 4.3.0
	 *
	 * @var string
	 */
	private $testurl = '';

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
		$this->testurl = ! empty( $args['testurl'] ) ? $args['testurl'] : '';
	}

	/**
	 * Sends and processes the API request.
	 *
	 * @since 4.3.0
	 *
	 * @return mixed $value The response.
	 */
	public function request() {
		// Make sure we're not blocked.
		$blocked = $this->isBlocked( $this->url );
		if ( is_wp_error( $blocked ) ) {
			return new \WP_Error(
				'api-error',
				sprintf(
					'The firewall of the server is blocking outbound calls. Please contact your hosting provider to fix this issue. %s',
					$blocked->get_error_message()
				)
			);
		}

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

		$body['timezone']        = date( 'e' );
		$body['ip']              = ! empty( $_SERVER['SERVER_ADDR'] ) ? $_SERVER['SERVER_ADDR'] : '';
		$body['internalOptions'] = aioseo()->internalOptions->internal->searchStatistics->all();

		// 2. SET HEADERS
		$headers = array_merge( [
			'Content-Type'      => 'application/json',
			'Cache-Control'     => 'no-store, no-cache, must-revalidate, max-age=0, post-check=0, pre-check=0',
			'Pragma'            => 'no-cache',
			'Expires'           => 0,
			'AIOSEOAPI-Referer' => site_url(),
			'AIOSEOAPI-Sender'  => 'WordPress',
			'X-AIOSEO-Key'      => aioseo()->internalOptions->internal->siteAnalysis->connectToken,
		], aioseo()->helpers->getApiHeaders() );

		// 3. COMPILE REQUEST DATA
		$data = [
			'headers'    => $headers,
			'body'       => wp_json_encode( $body ),
			'timeout'    => 3000,
			'user-agent' => aioseo()->helpers->getApiUserAgent()
		];

		// 4. EXECUTE REQUEST
		$response = null;
		if ( 'GET' === $this->method ) {
			$queryString = http_build_query( $body, '', '&' );

			unset( $data['body'] );

			$response = wp_remote_get( esc_url_raw( $this->url ) . '?' . $queryString, $data );
		} else {
			$response = wp_remote_post( esc_url_raw( $this->url ), $data );
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

	/**
	 * Checks if the given URL is blocked for a request.
	 *
	 * @since 4.3.0
	 *
	 * @param  string         $url The URL to test against.
	 * @return bool|\WP_Error      False or WP_Error if it is blocked.
	 */
	private function isBlocked( $url = '' ) {
		// The below page is a test HTML page used for firewall/router login detection
		// and for image linking purposes in Google Images. We use it to test outbound connections
		// It's on Google's main CDN so it loads in most countries in 0.07 seconds or less. Perfect for our
		// use case of testing outbound connections.
		$testurl = ! empty( $this->testurl ) ? $this->testurl : 'https://www.google.com/blank.html';
		if ( defined( 'WP_HTTP_BLOCK_EXTERNAL' ) && WP_HTTP_BLOCK_EXTERNAL ) {
			if ( defined( 'WP_ACCESSIBLE_HOSTS' ) ) {
				$wpHttp      = new \WP_Http();
				$onBlacklist = $wpHttp->block_request( $url );
				if ( $onBlacklist ) {
					return new \WP_Error( 'api-error', 'The API was unreachable because the API url is on the WP HTTP blocklist.' );
				} else {
					$params = [
						'sslverify'  => false,
						'timeout'    => 2,
						'user-agent' => aioseo()->helpers->getApiUserAgent(),
						'body'       => ''
					];

					$response = wp_remote_get( $testurl, $params );
					if ( ! is_wp_error( $response ) && $response['response']['code'] >= 200 && $response['response']['code'] < 300 ) {
						return false;
					} else {
						if ( is_wp_error( $response ) ) {
							return $response;
						} else {
							return new \WP_Error( 'api-error', 'The API was unreachable because the call to Google failed.' );
						}
					}
				}
			} else {
				return new \WP_Error( 'api-error', 'The API was unreachable because no external hosts are allowed on this site.' );
			}
		} else {
			$params = [
				'sslverify'  => false,
				'timeout'    => 2,
				'user-agent' => aioseo()->helpers->getApiUserAgent(),
				'body'       => ''
			];

			$response = wp_remote_get( $testurl, $params );
			if ( ! is_wp_error( $response ) && $response['response']['code'] >= 200 && $response['response']['code'] < 300 ) {
				return false;
			} else {
				if ( is_wp_error( $response ) ) {
					return $response;
				} else {
					return new \WP_Error( 'api-error', 'The API was unreachable because the call to Google failed.' );
				}
			}
		}
	}
}