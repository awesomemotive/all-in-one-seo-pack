<?php
namespace AIOSEO\Plugin\Common\Traits\Helpers;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Contains Action Scheduler specific helper methods.
 *
 * @since 4.2.4
 */
trait Api {
	/**
	 * Request the remote URL via wp_remote_post and return a json decoded response.
	 *
	 * @since 4.2.4
	 *
	 * @param  array       $body    The content to retrieve from the remote URL.
	 * @param  array       $headers The headers to send to the remote URL.
	 * @return object|null          JSON decoded response on success, false on failure.
	 */
	public function sendRequest( $url, $body = [], $headers = [] ) {
		$body = wp_json_encode( $body );

		// Build the headers of the request.
		$headers = wp_parse_args(
			$headers,
			[
				'Content-Type' => 'application/json'
			]
		);

		// Setup variable for wp_remote_post.
		$requestArgs = [
			'headers' => $headers,
			'body'    => $body,
			'timeout' => 20
		];

		// Perform the query and retrieve the response.
		$response     = $this->wpRemotePost( $url, $requestArgs );
		$responseBody = wp_remote_retrieve_body( $response );

		// Bail out early if there are any errors.
		if ( ! $responseBody ) {
			return null;
		}

		// Return the json decoded content.
		return json_decode( $responseBody );
	}

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
	 * @return array|\WP_Error      The response as an array or WP_Error on failure.
	 */
	public function wpRemotePost( $url, $args = [] ) {
		return wp_remote_post( $url, array_replace_recursive( $this->getWpApiRequestDefaults(), $args ) );
	}

	/**
	 * Sends a request using wp_remote_get.
	 *
	 * @since 4.2.4
	 *
	 * @param  string          $url  The URL to send the request to.
	 * @param  array           $args The args to use in the request.
	 * @return array|\WP_Error      The response as an array or WP_Error on failure.
	 */
	public function wpRemoteGet( $url, $args = [] ) {
		return wp_remote_get( $url, array_replace_recursive( $this->getWpApiRequestDefaults(), $args ) );
	}
}