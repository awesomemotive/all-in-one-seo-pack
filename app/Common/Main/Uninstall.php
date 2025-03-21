<?php
namespace AIOSEO\Plugin\Common\Main;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AIOSEO\Plugin\Common\Utils;

/**
 * Handles plugin deinstallation.
 *
 * @since 4.8.1
 */
class Uninstall {
	/**
	 * Removes all data.
	 *
	 * @since 4.8.1
	 *
	 * @param  bool $force Whether we should ignore the uninstall option or not. We ignore it when we reset all data via the Debug Panel.
	 * @return void
	 */
	public function dropData( $force = false ) {
		// Don't call `aioseo()->options` as it's not loaded during uninstall.
		$aioseoOptions = get_option( 'aioseo_options', '' );
		$aioseoOptions = json_decode( $aioseoOptions, true );

		// Confirm that user has decided to remove all data, otherwise stop.
		if (
			! $force &&
			empty( $aioseoOptions['advanced']['uninstall'] )
		) {
			return;
		}

		// Drop our custom tables.
		$this->uninstallDb();

		// Delete all our custom capabilities.
		$this->uninstallCapabilities();
	}

	/**
	 * Removes all our tables and options.
	 *
	 * @since 4.2.3
	 * @version 4.8.1 Moved from Core to Uninstall.
	 *
	 * @return void
	 */
	private function uninstallDb() {
		// Delete all our custom tables.
		global $wpdb;

		// phpcs:disable WordPress.DB.DirectDatabaseQuery
		foreach ( aioseo()->core->getDbTables() as $tableName ) {
			$wpdb->query( $wpdb->prepare( 'DROP TABLE IF EXISTS %i', $tableName ) );
		}

		// Delete all AIOSEO Locations and Location Categories.
		$wpdb->delete( $wpdb->posts, [ 'post_type' => 'aioseo-location' ], [ '%s' ] );
		$wpdb->delete( $wpdb->term_taxonomy, [ 'taxonomy' => 'aioseo-location-category' ], [ '%s' ] );

		// Delete all the plugin settings.
		$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s", 'aioseo\_%' ) );

		// Remove any transients we've left behind.
		$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s", '\_aioseo\_%' ) );
		$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s", 'aioseo\_%' ) );

		// Delete all entries from the action scheduler table.
		$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}actionscheduler_actions WHERE hook LIKE %s", 'aioseo\_%' ) );
		$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}actionscheduler_groups WHERE slug = %s", 'aioseo' ) );
		// phpcs:enable
	}

	/**
	 * Removes all our custom capabilities.
	 *
	 * @since 4.8.1
	 *
	 * @return void
	 */
	private function uninstallCapabilities() {
		$access             = new Utils\Access();
		$customCapabilities = $access->getCapabilityList() ?? [];
		$roles              = aioseo()->helpers->getUserRoles();

		// Loop through roles and remove custom capabilities.
		foreach ( $roles as $roleName => $roleInfo ) {
			$role = get_role( $roleName );

			if ( $role ) {
				$role->remove_cap( 'aioseo_admin' );
				$role->remove_cap( 'aioseo_manage_seo' );

				foreach ( $customCapabilities as $capability ) {
					$role->remove_cap( $capability );
				}
			}
		}

		remove_role( 'aioseo_manager' );
		remove_role( 'aioseo_editor' );
	}
}