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
			'usageTracking' => [ 'type' => 'boolean', 'default' => false ]
		]
		// phpcs:enable WordPress.Arrays.ArrayDeclarationSpacing.AssociativeArrayFound
	];

	/**
	 * Sanitizes, then saves the options to the database.
	 *
	 * @since 4.7.2
	 *
	 * @param  array $options An array of options to sanitize, then save.
	 * @return void
	 */
	public function sanitizeAndSave( $options ) {
		if ( isset( $options['advanced']['emailSummary']['recipients'] ) ) {
			$options['advanced']['emailSummary']['recipients']                 = [ array_shift( $options['advanced']['emailSummary']['recipients'] ) ];
			$options['advanced']['emailSummary']['recipients'][0]['frequency'] = 'monthly';
		}

		parent::sanitizeAndSave( $options );
	}
}