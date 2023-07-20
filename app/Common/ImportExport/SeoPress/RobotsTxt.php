<?php
namespace AIOSEO\Plugin\Common\ImportExport\SeoPress;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// phpcs:disable WordPress.Arrays.ArrayDeclarationSpacing.AssociativeArrayFound

/**
 * Migrates the robots.txt settings.
 *
 * @since 4.1.4
 */
class RobotsTxt {
	/**
	 * List of options.
	 *
	 * @since 4.2.7
	 *
	 * @var array
	 */
	private $options = [];

	/**
	 * Class constructor.
	 *
	 * @since 4.1.4
	 */
	public function __construct() {
		$this->options = get_option( 'seopress_pro_option_name', [] );
		if ( empty( $this->options ) ) {
			return;
		}

		$this->migrateRobotsTxt();

		$settings = [
			'seopress_robots_enable' => [ 'type' => 'boolean', 'newOption' => [ 'tools', 'robots', 'enable' ] ],
		];

		aioseo()->importExport->seoPress->helpers->mapOldToNew( $settings, $this->options );
	}

	/**
	 * Migrates the robots.txt.
	 *
	 * @since 4.1.4
	 *
	 * @return void
	 */
	public function migrateRobotsTxt() {
		$lines = ! empty( $this->options['seopress_robots_file'] ) ? (string) $this->options['seopress_robots_file'] : '';

		if ( $lines ) {
			$allRules = aioseo()->robotsTxt->extractRules( $lines );

			aioseo()->options->tools->robots->rules = aioseo()->robotsTxt->prepareRobotsTxt( $allRules );
		}
	}
}