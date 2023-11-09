<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'aioseoMaybePluginIsDisabled' ) ) {
	/**
	 * Disable the AIOSEO if triggered externally.
	 *
	 * @since   4.1.5
	 * @version 4.5.0 Added the $file parameter and Lite check.
	 *
	 * @param  string $file The plugin file.
	 * @return bool         True if the plugin should be disabled.
	 */
	function aioseoMaybePluginIsDisabled( $file ) {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
		if (
			'all-in-one-seo-pack/all_in_one_seo_pack.php' === plugin_basename( $file ) &&
			is_plugin_active( 'all-in-one-seo-pack-pro/all_in_one_seo_pack.php' )
		) {
			return true;
		}

		if ( ! defined( 'AIOSEO_DEV_VERSION' ) && ! isset( $_REQUEST['aioseo-dev'] ) ) { // phpcs:ignore HM.Security.NonceVerification.Recommended
			return false;
		}

		if ( ! isset( $_REQUEST['aioseo-disable-plugin'] ) ) { // phpcs:ignore HM.Security.NonceVerification.Recommended
			return false;
		}

		return true;
	}
}