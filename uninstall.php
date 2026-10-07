<?php
/**
 * Uninstall AIOSEO
 *
 * @since 4.0.0
 */

// phpcs:disable Squiz.NamingConventions.ValidVariableName
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound

// Exit if accessed directly.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Load plugin file.
require_once 'all_in_one_seo_pack.php';

// In case any of the versions - Lite or Pro - is still activated we bail.
// Meaning, if you delete Lite while the Pro is activated we bail, and vice-versa.
if (
	defined( 'AIOSEO_FILE' ) &&
	is_plugin_active( plugin_basename( AIOSEO_FILE ) )
) {
	return;
}

// Disable Action Scheduler Queue Runner.
if ( class_exists( 'ActionScheduler_QueueRunner' ) ) {
	ActionScheduler_QueueRunner::instance()->unhook_dispatch_async_request();
}

// Decide whether to remove all data BEFORE dropData() runs - its uninstallDb() deletes the
// aioseo_options row, so deciding afterwards would depend on a stale object cache.
$removeAllData = aioseo()->uninstall->shouldDropData();

// Drop our custom tables and data.
aioseo()->uninstall->dropData();

// Drop data left by true addons that can't clean up themselves during a genuine uninstall.
aioseo()->uninstall->dropAddonData( $removeAllData );

// Remove translation files.
global $wp_filesystem;
$languages_directory = defined( 'WP_LANG_DIR' ) ? trailingslashit( WP_LANG_DIR ) : trailingslashit( WP_CONTENT_DIR ) . 'languages/';
$translations        = glob( wp_normalize_path( $languages_directory . 'plugins/aioseo-*' ) );
if ( ! empty( $translations ) ) {
	foreach ( $translations as $file ) {
		$wp_filesystem->delete( $file );
	}
}

$translations = glob( wp_normalize_path( $languages_directory . 'plugins/all-in-one-seo-*' ) );
if ( ! empty( $translations ) ) {
	foreach ( $translations as $file ) {
		$wp_filesystem->delete( $file );
	}
}