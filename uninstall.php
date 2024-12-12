<?php
/**
 * Uninstall AIOSEO
 *
 * @since 4.0.0
 */

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

// Drop our custom tables.
aioseo()->core->uninstallDb();

// Remove translation files.
global $wp_filesystem; // phpcs:ignore Squiz.NamingConventions.ValidVariableName
$languages_directory = defined( 'WP_LANG_DIR' ) ? trailingslashit( WP_LANG_DIR ) : trailingslashit( WP_CONTENT_DIR ) . 'languages/'; // phpcs:ignore Squiz.NamingConventions.ValidVariableName
$translations        = glob( wp_normalize_path( $languages_directory . 'plugins/aioseo-*' ) ); // phpcs:ignore Squiz.NamingConventions.ValidVariableName
if ( ! empty( $translations ) ) {
	foreach ( $translations as $file ) {
		$wp_filesystem->delete( $file ); // phpcs:ignore Squiz.NamingConventions.ValidVariableName
	}
}

$translations = glob( wp_normalize_path( $languages_directory . 'plugins/all-in-one-seo-*' ) ); // phpcs:ignore Squiz.NamingConventions.ValidVariableName
if ( ! empty( $translations ) ) {
	foreach ( $translations as $file ) {
		$wp_filesystem->delete( $file ); // phpcs:ignore Squiz.NamingConventions.ValidVariableName
	}
}