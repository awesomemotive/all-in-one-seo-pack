<?php
namespace AIOSEO\Plugin\Common\Api;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AIOSEO\Plugin\Common\Migration as CommonMigration;
use AIOSEO\Plugin\Common\Models;

/**
 * Route class for the API.
 *
 * @since 4.0.6
 */
class Migration {
	/**
	 * Resets blank title formats and retriggers the post/term meta migration.
	 *
	 * @since 4.0.6
	 *
	 * @return \WP_REST_Response The response.
	 */
	public static function fixBlankFormats() {
		$oldOptions = ( new CommonMigration\OldOptions() )->oldOptions;
		if ( ! $oldOptions ) {
			return new \WP_REST_Response( [
				'success' => true,
				'message' => 'Could not load v3 options.'
			], 400 );
		}

		$postTypes  = aioseo()->helpers->getPublicPostTypes( true );
		$taxonomies = aioseo()->helpers->getPublicTaxonomies( true );
		foreach ( $oldOptions as $k => $v ) {
			if ( ! preg_match( '/^aiosp_([a-zA-Z]*)_title_format$/', (string) $k, $match ) || ! empty( $v ) ) {
				continue;
			}

			$objectName = $match[1];
			if ( in_array( $objectName, $postTypes, true ) && aioseo()->dynamicOptions->searchAppearance->postTypes->has( $objectName ) ) {
				aioseo()->dynamicOptions->searchAppearance->postTypes->$objectName->title = '#post_title #separator_sa #site_title';
				continue;
			}

			if ( in_array( $objectName, $taxonomies, true ) && aioseo()->dynamicOptions->searchAppearance->taxonomies->has( $objectName ) ) {
				aioseo()->dynamicOptions->searchAppearance->taxonomies->$objectName->title = '#taxonomy_title #separator_sa #site_title';
			}
		}

		aioseo()->migration->redoMetaMigration();

		Models\Notification::deleteNotificationByName( 'v3-migration-title-formats-blank' );

		return new \WP_REST_Response( [
			'success'       => true,
			'message'       => 'Title formats have been reset; post/term migration has been scheduled.',
			'notifications' => Models\Notification::getNotifications()
		], 200 );
	}
}