<?php
namespace AIOSEO\Plugin\Common\Traits\Helpers;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Contains API specific helper methods.
 *
 * @since 4.2.4
 */
trait Api {
	/**
	 * Default arguments for wp_remote_get and wp_remote_post.
	 *
	 * @since 4.2.4
	 *
	 * @return array An array of default arguments for the request.
	 */
	private function getWpApiRequestDefaults() {
		return [
			'timeout'    => 10,
			'headers'    => aioseo()->helpers->getApiHeaders(),
			'user-agent' => aioseo()->helpers->getApiUserAgent()
		];
	}

	/**
	 * Sends a request using wp_remote_post.
	 *
	 * @since 4.2.4
	 *
	 * @param  string          $url  The URL to send the request to.
	 * @param  array           $args The args to use in the request.
	 * @return array|\WP_Error       The response as an array or WP_Error on failure.
	 */
	public function wpRemotePost( $url, $args = [] ) {
		$skipLock = ! empty( $args['aioseo_skip_lock'] );
		unset( $args['aioseo_skip_lock'] );

		$args['method'] = 'POST';

		if ( ! $skipLock ) {
			$lockKey = $this->getCacheKey( $url, $args );
			if ( aioseo()->core->cache->get( $lockKey ) ) {
				return new \WP_Error( 'concurrent_request', 'A request to this URL is already in progress.' );
			}

			aioseo()->core->cache->update( $lockKey, true, MINUTE_IN_SECONDS );
		}

		$response = wp_remote_post( $url, array_replace_recursive( $this->getWpApiRequestDefaults(), $args ) );

		if ( ! $skipLock ) {
			aioseo()->core->cache->delete( $lockKey );
		}

		return $response;
	}

	/**
	 * Sends a request using wp_remote_get.
	 *
	 * @since 4.2.4
	 *
	 * @param  string          $url  The URL to send the request to.
	 * @param  array           $args The args to use in the request.
	 * @return array|\WP_Error       The response as an array or WP_Error on failure.
	 */
	public function wpRemoteGet( $url, $args = [] ) {
		$skipLock = ! empty( $args['aioseo_skip_lock'] );
		unset( $args['aioseo_skip_lock'] );

		if ( ! $skipLock ) {
			$lockKey = $this->getCacheKey( $url, $args );
			if ( aioseo()->core->cache->get( $lockKey ) ) {
				return new \WP_Error( 'concurrent_request', 'A request to this URL is already in progress.' );
			}

			aioseo()->core->cache->update( $lockKey, true, MINUTE_IN_SECONDS );
		}

		$response = wp_remote_get( $url, array_replace_recursive( $this->getWpApiRequestDefaults(), $args ) );

		if ( ! $skipLock ) {
			aioseo()->core->cache->delete( $lockKey );
		}

		return $response;
	}

	/**
	 * Default arguments for external (third-party) API requests.
	 * This is to be used for requests to external APIs that do not include internal AIOSEO headers like license keys.
	 *
	 * @since 4.9.4.2
	 *
	 * @return array An array of default arguments for the request.
	 */
	private function getExternalRequestDefaults() {
		return [
			'timeout' => 10,
			'headers' => [
				'Content-Type' => 'application/json'
			]
		];
	}

	/**
	 * Sends a POST request to an external (third-party) API.
	 * Unlike wpRemotePost, this does not include internal AIOSEO headers like license keys.
	 *
	 * @since 4.9.4.2
	 *
	 * @param  string          $url  The URL to send the request to.
	 * @param  array           $args The args to use in the request.
	 * @return array|\WP_Error       The response as an array or WP_Error on failure.
	 */
	public function wpRemotePostExternal( $url, $args = [] ) {
		$skipLock = ! empty( $args['aioseo_skip_lock'] );
		unset( $args['aioseo_skip_lock'] );

		$args['method'] = 'POST';

		if ( ! $skipLock ) {
			$lockKey = $this->getCacheKey( $url, $args );
			if ( aioseo()->core->cache->get( $lockKey ) ) {
				return new \WP_Error( 'concurrent_request', 'A request to this URL is already in progress.' );
			}

			aioseo()->core->cache->update( $lockKey, true, MINUTE_IN_SECONDS );
		}

		$response = wp_remote_post( $url, array_replace_recursive( $this->getExternalRequestDefaults(), $args ) );

		if ( ! $skipLock ) {
			aioseo()->core->cache->delete( $lockKey );
		}

		return $response;
	}

	/**
	 * Sends a GET request to an external (third-party) API.
	 * Unlike wpRemoteGet, this does not include internal AIOSEO headers like license keys.
	 *
	 * @since 4.9.4.2
	 *
	 * @param  string          $url  The URL to send the request to.
	 * @param  array           $args The args to use in the request.
	 * @return array|\WP_Error       The response as an array or WP_Error on failure.
	 */
	public function wpRemoteGetExternal( $url, $args = [] ) {
		$skipLock = ! empty( $args['aioseo_skip_lock'] );
		unset( $args['aioseo_skip_lock'] );

		if ( ! $skipLock ) {
			$lockKey = $this->getCacheKey( $url, $args );
			if ( aioseo()->core->cache->get( $lockKey ) ) {
				return new \WP_Error( 'concurrent_request', 'A request to this URL is already in progress.' );
			}

			aioseo()->core->cache->update( $lockKey, true, MINUTE_IN_SECONDS );
		}

		$response = wp_remote_get( $url, array_replace_recursive( $this->getExternalRequestDefaults(), $args ) );

		if ( ! $skipLock ) {
			aioseo()->core->cache->delete( $lockKey );
		}

		return $response;
	}

	/**
	 * Sends a DELETE request using wp_remote_request.
	 *
	 * @since 4.9.4.2
	 *
	 * @param  string          $url  The URL to send the request to.
	 * @param  array           $args The args to use in the request.
	 * @return array|\WP_Error       The response as an array or WP_Error on failure.
	 */
	public function wpRemoteDelete( $url, $args = [] ) {
		$skipLock = ! empty( $args['aioseo_skip_lock'] );
		unset( $args['aioseo_skip_lock'] );

		$args['method'] = 'DELETE';

		if ( ! $skipLock ) {
			$lockKey = $this->getCacheKey( $url, $args );
			if ( aioseo()->core->cache->get( $lockKey ) ) {
				return new \WP_Error( 'concurrent_request', 'A request to this URL is already in progress.' );
			}

			aioseo()->core->cache->update( $lockKey, true, MINUTE_IN_SECONDS );
		}

		$response = wp_remote_request( $url, array_replace_recursive( $this->getWpApiRequestDefaults(), $args ) );

		if ( ! $skipLock ) {
			aioseo()->core->cache->delete( $lockKey );
		}

		return $response;
	}

	/**
	 * Returns a cache key for a remote request lock based on the HTTP method, URL and body.
	 *
	 * @since 4.9.4.2
	 *
	 * @param  string $url  The request URL.
	 * @param  array  $args The request arguments.
	 * @return string       The lock cache key.
	 */
	private function getCacheKey( $url, $args = [] ) {
		$method = ! empty( $args['method'] ) ? $args['method'] : 'GET';
		$body   = ! empty( $args['body'] ) ? $args['body'] : '';
		$raw    = is_array( $body ) || is_object( $body ) ? wp_json_encode( $body ) : (string) $body;

		return 'remote_lock_' . md5( $method . $url . $raw );
	}
}