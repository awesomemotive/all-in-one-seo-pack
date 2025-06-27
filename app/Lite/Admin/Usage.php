<?php
namespace AIOSEO\Plugin\Lite\Admin;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AIOSEO\Plugin\Common\Admin as CommonAdmin;

/**
 * Usage tracking class.
 *
 * @since 4.0.0
 */
class Usage extends CommonAdmin\Usage {
	/**
	 * Class Constructor
	 *
	 * @since 4.0.0
	 */
	public function __construct() {
		parent::__construct();

		$this->enabled = apply_filters( 'aioseo_usage_tracking_enable', aioseo()->options->advanced->usageTracking );
	}

	/**
	 * Get the type for the request.
	 *
	 * @since 4.0.0
	 *
	 * @return string The install type.
	 */
	public function getType() {
		return 'lite';
	}
}