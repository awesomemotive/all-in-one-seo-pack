<?php
namespace AIOSEO\Plugin\Common\Options;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AIOSEO\Plugin\Common\Traits;
use AIOSEO\Plugin\Common\Utils;

/**
 * Class that holds all network options for AIOSEO.
 *
 * @since 4.2.5
 */
class NetworkOptions {
	use Traits\Options;
	use Traits\NetworkOptions;

	/**
	 * Holds the helpers class.
	 *
	 * @since 4.2.5
	 *
	 * @var Utils\Helpers
	 */
	protected $helpers;

	/**
	 * All the default options.
	 *
	 * @since 4.2.5
	 *
	 * @var array
	 */
	protected $defaults = [
		// phpcs:disable WordPress.Arrays.ArrayDeclarationSpacing.AssociativeArrayFound
		'searchAppearance' => [
			'advanced' => [
				'unwantedBots'  => [
					'all'      => [ 'type' => 'boolean', 'default' => false ],
					'settings' => [
						'googleAdsBot'             => [ 'type' => 'boolean', 'default' => false ],
						'openAiGptBot'             => [ 'type' => 'boolean', 'default' => false ],
						'commonCrawlCcBot'         => [ 'type' => 'boolean', 'default' => false ],
						'googleGeminiVertexAiBots' => [ 'type' => 'boolean', 'default' => false ]
					]
				],
				'searchCleanup' => [
					'settings' => [
						'preventCrawling' => [ 'type' => 'boolean', 'default' => false ]
					]
				]
			]
		],
		'tools'            => [
			'robots' => [
				'enable'         => [ 'type' => 'boolean', 'default' => false ],
				'rules'          => [ 'type' => 'array', 'default' => [] ],
				'robotsDetected' => [ 'type' => 'boolean', 'default' => true ],
			]
		]
		// phpcs:enable WordPress.Arrays.ArrayDeclarationSpacing.AssociativeArrayFound
	];

	/**
	 * The Construct method.
	 *
	 * @since 4.2.5
	 *
	 * @param string $optionsName The options name.
	 */
	public function __construct( $optionsName = 'aioseo_options_network' ) {
		$this->helpers     = new Utils\Helpers();
		$this->optionsName = $optionsName;

		$this->init();

		add_action( 'shutdown', [ $this, 'save' ] );
	}
}