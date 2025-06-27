<?php
namespace AIOSEO\Plugin\Common\Migration;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// phpcs:disable WordPress.Arrays.ArrayDeclarationSpacing.AssociativeArrayFound

/**
 * Migrates the Bad Robots Blocker settings from V3.
 *
 * @since 4.0.0
 */
class BadRobots {

	/**
	 * Class constructor.
	 *
	 * @since 4.0.0
	 */
	public function __construct() {
		$oldOptions = aioseo()->migration->oldOptions;

		$deprecatedOptions = aioseo()->internalOptions->internal->deprecatedOptions;
		array_push( $deprecatedOptions, 'badBotBlocker' );
		aioseo()->internalOptions->internal->deprecatedOptions = $deprecatedOptions;

		if ( empty( $oldOptions['modules']['aiosp_bad_robots_options'] ) ) {
			return;
		}

		if ( ! empty( $oldOptions['modules']['aiosp_bad_robots_options']['aiosp_bad_robots_blocklist'] ) ) {
			$badBots = explode( '\r\n', $oldOptions['modules']['aiosp_bad_robots_options']['aiosp_bad_robots_blocklist'] );
			if ( $badBots ) {
				foreach ( $badBots as $k => $v ) {
					$badBots[ $k ] = aioseo()->helpers->sanitizeOption( $v );
				}
				aioseo()->options->deprecated->tools->blocker->custom->bots = implode( "\r\n", $badBots );
			}
		}

		if ( ! empty( $oldOptions['modules']['aiosp_bad_robots_options']['aiosp_bad_robots_referlist'] ) ) {
			$badReferers = explode( '\r\n', $oldOptions['modules']['aiosp_bad_robots_options']['aiosp_bad_robots_referlist'] );
			if ( $badReferers ) {
				foreach ( $badReferers as $k => $v ) {
					$badReferers[ $k ] = aioseo()->helpers->sanitizeOption( $v );
				}
				aioseo()->options->deprecated->tools->blocker->custom->referer = implode( "\r\n", $badReferers );
			}
		}

		$settings = [
			'aiosp_bad_robots_block_bots'   => [ 'type' => 'boolean', 'newOption' => [ 'deprecated', 'tools', 'blocker', 'blockBots' ] ],
			'aiosp_bad_robots_block_refer'  => [ 'type' => 'boolean', 'newOption' => [ 'deprecated', 'tools', 'blocker', 'blockReferer' ] ],
			'aiosp_bad_robots_track_blocks' => [ 'type' => 'boolean', 'newOption' => [ 'deprecated', 'tools', 'blocker', 'track' ] ],
			'aiosp_bad_robots_edit_blocks'  => [ 'type' => 'boolean', 'newOption' => [ 'deprecated', 'tools', 'blocker', 'custom', 'enable' ] ],
		];

		aioseo()->migration->helpers->mapOldToNew( $settings, $oldOptions['modules']['aiosp_bad_robots_options'] );
	}
}