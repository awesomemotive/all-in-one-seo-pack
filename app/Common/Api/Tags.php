<?php
namespace AIOSEO\Plugin\Common\Api;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Route class for the API.
 *
 * @since 4.0.0
 */
class Tags {
	/**
	 * Get all Tags.
	 *
	 * @since 4.0.0
	 *
	 * @return \WP_REST_Response The response.
	 */
	public static function getTags() {
		return new \WP_REST_Response( aioseo()->tags->all( true ), 200 );
	}
}