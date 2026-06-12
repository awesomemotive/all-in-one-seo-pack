<?php
namespace AIOSEO\Plugin\Common\Api;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * REST endpoints powering the AI Suite > AI Agents tab — MCP Adapter install + app password helper.
 *
 * @since 4.9.8
 */
class AiAgents {
	/**
	 * GitHub Releases API endpoint for the canonical MCP Adapter plugin.
	 *
	 * @since 4.9.8
	 *
	 * @var string
	 */
	const GITHUB_LATEST_RELEASE_URL = 'https://api.github.com/repos/WordPress/mcp-adapter/releases/latest';

	/**
	 * Transient key for the cached GitHub release payload.
	 *
	 * @since 4.9.8
	 *
	 * @var string
	 */
	const RELEASE_CACHE_KEY = 'aioseo_mcp_adapter_release';

	/**
	 * Fetches the latest mcp-adapter release metadata from GitHub.
	 *
	 * Result is cached in a 1-hour transient so the AI Agents tab doesn't hammer GitHub
	 * on every page load. Public information — no auth required.
	 *
	 * @since 4.9.8
	 *
	 * @return \WP_REST_Response
	 */
	public static function getMcpAdapterRelease() {
		$cached = get_transient( self::RELEASE_CACHE_KEY );
		if ( false !== $cached && is_array( $cached ) ) {
			return new \WP_REST_Response( $cached, 200 );
		}

		$response = wp_remote_get( self::GITHUB_LATEST_RELEASE_URL, [
			'timeout' => 10,
			'headers' => [ 'Accept' => 'application/vnd.github+json' ]
		] );

		if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
			return new \WP_REST_Response( [
				'success' => false,
				'message' => __( 'Could not fetch the latest MCP Adapter release from GitHub.', 'all-in-one-seo-pack' )
			], 502 );
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );
		if ( ! is_array( $body ) || empty( $body['tag_name'] ) || empty( $body['assets'][0]['browser_download_url'] ) ) {
			return new \WP_REST_Response( [
				'success' => false,
				'message' => __( 'GitHub response did not include a downloadable release asset.', 'all-in-one-seo-pack' )
			], 502 );
		}

		$payload = [
			'success'      => true,
			'version'      => ltrim( (string) $body['tag_name'], 'v' ),
			'download_url' => esc_url_raw( $body['assets'][0]['browser_download_url'] ),
			'html_url'     => isset( $body['html_url'] ) ? esc_url_raw( $body['html_url'] ) : ''
		];

		set_transient( self::RELEASE_CACHE_KEY, $payload, HOUR_IN_SECONDS );

		return new \WP_REST_Response( $payload, 200 );
	}

	/**
	 * Returns the plugin file of an installed MCP Adapter plugin, if any.
	 *
	 * @since 4.9.8
	 *
	 * @return string The plugin file relative to the plugins directory, or an empty string if not installed.
	 */
	public static function getInstalledMcpAdapterFile() {
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		foreach ( array_keys( get_plugins() ) as $pluginFile ) {
			if ( 0 === strpos( $pluginFile, 'mcp-adapter/' ) ) {
				return $pluginFile;
			}
		}

		return '';
	}

	/**
	 * Installs the MCP Adapter plugin from its GitHub release zip and activates it.
	 *
	 * Detects pre-existing installs via `WP\MCP\Core\McpAdapter` class lookup so we don't
	 * trigger the `duplicate_server_id` bug (mcp-adapter issue #172) when WooCommerce or
	 * another plugin already shipped it as a Composer dep.
	 *
	 * @since 4.9.8
	 *
	 * @return \WP_REST_Response
	 */
	public static function installMcpAdapter() {
		if ( ! current_user_can( 'install_plugins' ) || ! current_user_can( 'activate_plugins' ) ) {
			return new \WP_REST_Response( [
				'success' => false,
				'message' => __( 'You do not have permission to install plugins.', 'all-in-one-seo-pack' )
			], 403 );
		}

		if ( class_exists( '\\WP\\MCP\\Core\\McpAdapter' ) ) {
			return new \WP_REST_Response( [
				'success'         => true,
				'already_present' => true,
				'message'         => __( 'MCP Adapter is already active on this site.', 'all-in-one-seo-pack' )
			], 200 );
		}

		// If the plugin is already installed but inactive, just activate it instead of reinstalling
		// (the upgrader would otherwise fail because the destination folder already exists).
		$installedPlugin = self::getInstalledMcpAdapterFile();
		if ( $installedPlugin ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';

			$activated = activate_plugin( $installedPlugin );
			if ( is_wp_error( $activated ) ) {
				return new \WP_REST_Response( [
					'success' => false,
					'message' => $activated->get_error_message()
				], 500 );
			}

			return new \WP_REST_Response( [
				'success' => true,
				'plugin'  => $installedPlugin,
				'message' => __( 'MCP Adapter activated.', 'all-in-one-seo-pack' )
			], 200 );
		}

		$release = self::getMcpAdapterRelease();
		$body    = $release->get_data();
		if ( empty( $body['success'] ) || empty( $body['download_url'] ) ) {
			return new \WP_REST_Response( [
				'success' => false,
				'message' => isset( $body['message'] ) ? $body['message'] : __( 'Could not resolve the MCP Adapter download URL.', 'all-in-one-seo-pack' )
			], 502 );
		}

		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/misc.php';
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
		require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';

		$skin     = new \WP_Ajax_Upgrader_Skin();
		$upgrader = new \Plugin_Upgrader( $skin );
		$result   = $upgrader->install( $body['download_url'] );

		if ( is_wp_error( $result ) ) {
			return new \WP_REST_Response( [
				'success' => false,
				'message' => $result->get_error_message()
			], 500 );
		}
		if ( false === $result || ! $upgrader->plugin_info() ) {
			return new \WP_REST_Response( [
				'success' => false,
				'message' => __( 'MCP Adapter install failed. Check site write permissions and try again.', 'all-in-one-seo-pack' )
			], 500 );
		}

		$pluginFile = $upgrader->plugin_info();
		$activated  = activate_plugin( $pluginFile );
		if ( is_wp_error( $activated ) ) {
			return new \WP_REST_Response( [
				'success' => false,
				'message' => $activated->get_error_message()
			], 500 );
		}

		return new \WP_REST_Response( [
			'success' => true,
			'version' => $body['version'],
			'plugin'  => $pluginFile,
			'message' => sprintf(
				/* translators: %s: installed mcp-adapter version. */
				__( 'MCP Adapter %s installed and activated.', 'all-in-one-seo-pack' ),
				$body['version']
			)
		], 200 );
	}

	/**
	 * Generates a WordPress Application Password for the current user, scoped to AIOSEO MCP usage.
	 *
	 * Returned password is shown only once. The caller must copy it immediately — there's no recovery.
	 *
	 * @since 4.9.8
	 *
	 * @return \WP_REST_Response
	 */
	public static function generateAppPassword() {
		if ( ! class_exists( '\WP_Application_Passwords' ) ) {
			return new \WP_REST_Response( [
				'success' => false,
				'message' => __( 'Application Passwords are not available on this site.', 'all-in-one-seo-pack' )
			], 500 );
		}

		$userId = get_current_user_id();
		if ( ! $userId ) {
			return new \WP_REST_Response( [
				'success' => false,
				'message' => __( 'You must be logged in to generate an Application Password.', 'all-in-one-seo-pack' )
			], 403 );
		}

		// Honour the same per-user availability gate WP core's Application Passwords REST
		// controller enforces — 2FA/security plugins use it to disable Application Passwords
		// for specific users, and we must not provide a way around that.
		if ( ! wp_is_application_passwords_available_for_user( $userId ) ) {
			return new \WP_REST_Response( [
				'success' => false,
				'message' => __( 'Application Passwords are not available for your account. Please contact a site administrator.', 'all-in-one-seo-pack' )
			], 403 );
		}

		$created = \WP_Application_Passwords::create_new_application_password( $userId, [
			'name'   => 'AIOSEO AI Agents',
			'app_id' => 'aioseo-mcp'
		] );

		if ( is_wp_error( $created ) ) {
			return new \WP_REST_Response( [
				'success' => false,
				'message' => $created->get_error_message()
			], 500 );
		}

		$user = wp_get_current_user();

		return new \WP_REST_Response( [
			'success'  => true,
			'username' => $user ? $user->user_login : '',
			'password' => isset( $created[0] ) ? (string) $created[0] : '',
			'name'     => 'AIOSEO AI Agents',
			'message'  => __( 'Application Password generated. Copy it now — it will not be shown again.', 'all-in-one-seo-pack' )
		], 200 );
	}
}