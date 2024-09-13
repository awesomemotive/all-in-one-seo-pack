<?php
namespace AIOSEO\Plugin\Lite\Options;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AIOSEO\Plugin\Common\Options as CommonOptions;
use AIOSEO\Plugin\Lite\Traits;

/**
 * Class that holds all options for AIOSEO.
 *
 * @since 4.0.0
 */
class Options extends CommonOptions\Options {
	use Traits\Options;

	/**
	 * Defaults options for Lite.
	 *
	 * @since 4.0.0
	 *
	 * @var array
	 */
	private $liteDefaults = [
		// phpcs:disable WordPress.Arrays.ArrayDeclarationSpacing.AssociativeArrayFound
		'advanced' => [
			'autoUpdates'   => [ 'type' => 'string', 'default' => 'none' ],
			'usageTracking' => [ 'type' => 'boolean', 'default' => false ]
		]
		// phpcs:enable WordPress.Arrays.ArrayDeclarationSpacing.AssociativeArrayFound
	];
}