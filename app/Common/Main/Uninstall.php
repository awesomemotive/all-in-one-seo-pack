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
		// Always remove the static LLMS files we wrote to the web root. They're served
		// directly by the web server, so if left behind they keep serving stale content
		// after the plugin is gone - even when the user opts to keep their data.
		$this->deleteLlmsFiles();

		// Confirm that user has decided to remove all data, otherwise stop.
		if ( ! $this->shouldDropData( $force ) ) {
			return;
		}

		// Drop our custom tables.
		$this->uninstallDb();

		// Delete all our custom capabilities.
		$this->uninstallCapabilities();

		// Runs each loaded addon's own `dropData()`. During a genuine uninstall addons never
		// register (they hook `aioseo_loaded`, which doesn't fire then) - their leftovers are
		// covered by dropAddonData() instead.
		if ( ! empty( aioseo()->addons ) ) {
			aioseo()->addons->doAddonFunction( 'uninstall', 'dropData', [
				'force' => $force
			] );
		}
	}

	/**
	 * Whether the user has opted to remove all data on uninstall.
	 *
	 * @since 5.0.3
	 *
	 * @param  bool $force Whether to ignore the uninstall option. We ignore it when we reset all data via the Debug Panel.
	 * @return bool
	 */
	public function shouldDropData( $force = false ) {
		if ( $force ) {
			return true;
		}

		// Don't call `aioseo()->options` as it's not loaded during uninstall.
		$aioseoOptions = json_decode( (string) get_option( 'aioseo_options', '' ), true );

		return ! empty( $aioseoOptions['advanced']['uninstall'] );
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
			$wpdb->query( 'DROP TABLE IF EXISTS `' . esc_sql( $tableName ) . '`' ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		}

		// Delete all AIOSEO Locations and Location Categories.
		$wpdb->delete( $wpdb->posts, [ 'post_type' => 'aioseo-location' ], [ '%s' ] );
		$wpdb->delete( $wpdb->term_taxonomy, [ 'taxonomy' => 'aioseo-location-category' ], [ '%s' ] );

		// Delete all the plugin settings.
		$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s", 'aioseo\_%' ) );
		$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s", '%\_aioseo-%' ) );
		$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s", '%\_aioseo\_%' ) );

		// Delete all entries from the action scheduler table.
		$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}actionscheduler_actions WHERE hook LIKE %s", 'aioseo\_%' ) );
		$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}actionscheduler_groups WHERE slug = %s", 'aioseo' ) );

		// Delete all entries from postmeta table.
		$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}postmeta WHERE meta_key LIKE %s", '_aioseo\_%' ) );

		// Delete all entries from termmeta table.
		$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}termmeta WHERE meta_key LIKE %s", '_aioseo\_%' ) );

		// Delete all entries from usermeta table.
		$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}usermeta WHERE meta_key LIKE %s", '%aioseo\_%' ) );
		$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}usermeta WHERE meta_key LIKE %s", '%aioseo-%' ) );

		// Delete seoboost access tokens and user options.
		$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}usermeta WHERE meta_key LIKE %s", 'seoboost_%s' ) );
		// phpcs:enable
	}

	/**
	 * Removes the static LLMS files (llms.txt and llms-full.txt) from the web root.
	 *
	 * @since 5.0.3
	 *
	 * @return void
	 */
	private function deleteLlmsFiles() {
		// `aioseo()->llms` is null during uninstall (the plugin isn't fully loaded), so build
		// the module on demand - its constructor is uninstall-safe - to reuse its file paths.
		$llms = aioseo()->llms;
		if ( empty( aioseo()->llms ) ) {
			$llms = aioseo()->pro
				? new \AIOSEO\Plugin\Pro\Llms\Llms()
				: new \AIOSEO\Plugin\Common\Llms\Llms();
		}

		// llms-full.txt is Pro-only, but a Pro->Lite downgrade can leave it behind, so both
		// classes delete both files. Deleting a missing file is a harmless no-op.
		$llms->deleteLlmsFile();
		$llms->deleteLlmsFullTxt();
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
		$roles              = ! empty( aioseo()->helpers ) ? aioseo()->helpers->getUserRoles() : [];

		// Capabilities earlier versions granted that the current list no longer names. Not matched by the `aioseo_`
		// prefix, which our standalone plugins (e.g. Broken Link Checker) share for capabilities of their own.
		$legacyCapabilities = [
			'aioseo_internal_links_settings', // 4.0.13 to 4.1.2.2.
			'aioseo_page_redirects_settings'  // 4.1.8 to 4.8.3.2.
		];

		// Loop through roles and remove custom capabilities.
		foreach ( $roles as $roleName => $roleInfo ) {
			$role = get_role( $roleName );

			if ( $role ) {
				$role->remove_cap( 'aioseo_admin' );
				$role->remove_cap( 'aioseo_manage_seo' );

				foreach ( array_merge( $customCapabilities, $legacyCapabilities ) as $capability ) {
					$role->remove_cap( $capability );
				}
			}
		}

		remove_role( 'aioseo_manager' );
		remove_role( 'aioseo_editor' );
	}

	/**
	 * Removes data left by true addons, which can't clean up themselves during a genuine uninstall.
	 *
	 * NOTE: True addons hook `aioseo_loaded`, which never fires during a genuine uninstall. Never
	 * gate this on `WP_UNINSTALL_PLUGIN` either - a bulk delete leaves that constant holding the
	 * first-deleted plugin's basename.
	 *
	 * @since 5.0.3
	 *
	 * @param  bool $removeAllData Whether the user opted to remove all data, decided before the aioseo_options row is deleted.
	 * @return void
	 */
	public function dropAddonData( $removeAllData = false ) {
		if ( ! $removeAllData ) {
			return;
		}

		$this->removeAddonCapabilities();
	}

	/**
	 * Removes capabilities registered by true addons.
	 *
	 * NOTE: Listed explicitly - never prefix-matched - so a still-installed standalone product's
	 * caps (e.g. Broken Link Checker's `aioseo_blc_*`) are never stripped. This copy is the only
	 * remover when an addon's own uninstall.php doesn't run or can no longer read its data, so
	 * keep it in sync with the addon cap definitions cited below and extend it when a true addon
	 * adds capabilities.
	 *
	 * @since 5.0.3
	 *
	 * @return void
	 */
	private function removeAddonCapabilities() {
		$addonCapabilities = [
			// Local Business - `aioseo-location` post type caps (Addon\LocalBusiness Admin\Location::getCapabilities()).
			'edit_aioseo_location',
			'edit_aioseo_locations',
			'edit_other_aioseo_locations',
			'publish_aioseo_locations',
			'read_aioseo_location',
			'read_private_aioseo_locations',
			'delete_aioseo_location',
			// Local Business - `aioseo-location-category` taxonomy caps (Addon\LocalBusiness Admin\LocationCategory::getCapabilities()). No `aioseo` prefix by design.
			'manage_location_categories',
			'edit_location_categories',
			'delete_location_categories',
			'assign_location_categories'
		];

		$roles = ! empty( aioseo()->helpers ) ? aioseo()->helpers->getUserRoles() : [];
		foreach ( $roles as $roleName => $roleInfo ) {
			$role = get_role( $roleName );
			if ( ! $role ) {
				continue;
			}

			foreach ( $addonCapabilities as $capability ) {
				$role->remove_cap( $capability );
			}
		}
	}
}