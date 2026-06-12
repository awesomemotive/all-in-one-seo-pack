<?php
namespace AIOSEO\Plugin\Common\RestApi;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers our REST API fields for posts and terms.
 *
 * Merged into the main plugin from the legacy aioseo-rest-api addon.
 *
 * @since 4.9.8
 */
class RestApi {
	/**
	 * Class constructor.
	 *
	 * @since 4.9.8
	 */
	public function __construct() {
		new Controllers\Post();
		new Controllers\Term();
	}
}