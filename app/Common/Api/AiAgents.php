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
	 * Returns the plugin files of every installed copy of the MCP Adapter plugin.
	 *
	 * @since 5.0.3
	 *
	 * @return string[] The plugin files relative to the plugins directory.
	 */
	private static function getMcpAdapterFiles() {
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$pluginFiles = [];
		foreach ( get_plugins() as $pluginFile => $pluginData ) {
			// A hand-renamed copy lands outside `mcp-adapter/`; upstream's `mcp-adapter` text domain stays.
			$isAdapter = 0 === strpos( $pluginFile, 'mcp-adapter/' ) ||
				( ! empty( $pluginData['TextDomain'] ) && 'mcp-adapter' === $pluginData['TextDomain'] );

			if ( $isAdapter ) {
				$pluginFiles[] = $pluginFile;
			}
		}

		return $pluginFiles;
	}

	/**
	 * Returns the plugin file of an installed MCP Adapter plugin, if any.
	 *
	 * @since   4.9.8
	 * @version 5.0.3 Also match a copy whose folder was renamed, via its text domain.
	 *
	 * @return string The plugin file relative to the plugins directory, or an empty string if not installed.
	 */
	public static function getInstalledMcpAdapterFile() {
		$pluginFiles = self::getMcpAdapterFiles();

		return $pluginFiles ? reset( $pluginFiles ) : '';
	}

	/**
	 * Returns the plugin file of an active MCP Adapter plugin, if any.
	 *
	 * NOTE: a class lookup also matches a copy vendored by another plugin, and the adapter's own
	 * server registry only fills from `rest_api_init` onward.
	 *
	 * @since 5.0.3
	 *
	 * @return string The plugin file relative to the plugins directory, or an empty string if none is active.
	 */
	public static function getActiveMcpAdapterFile() {
		// Any active copy short-circuits the install: activating a second one redeclares the
		// library's bootstrap functions and takes the request down with a fatal.
		foreach ( self::getMcpAdapterFiles() as $pluginFile ) {
			if ( is_plugin_active( $pluginFile ) ) {
				return $pluginFile;
			}
		}

		return '';
	}

	/**
	 * Returns whether the MCP Adapter's default server route is registered.
	 *
	 * NOTE: outside a REST request this builds the REST server, which fires `rest_api_init`.
	 *
	 * @since 5.0.3
	 *
	 * @return bool Whether the default server route is registered.
	 */
	public static function isMcpServerRegistered() {
		return array_key_exists( '/mcp/mcp-adapter-default-server', rest_get_server()->get_routes( 'mcp' ) );
	}

	/**
	 * Installs the MCP Adapter plugin from its GitHub release zip and activates it.
	 *
	 * @since   4.9.8
	 * @version 5.0.3 Key the already-active check on the adapter plugin's state.
	 *
	 * @return \WP_REST_Response
	 */
	public static function installMcpAdapter() {
		if ( '' !== self::getActiveMcpAdapterFile() ) {
			return new \WP_REST_Response( [
				'success'         => true,
				'already_present' => true,
				'message'         => __( 'MCP Adapter is already active on this site.', 'all-in-one-seo-pack' )
			], 200 );
		}

		require_once ABSPATH . 'wp-admin/includes/plugin.php';

		// On multisite the plugins directory is shared across the whole network, so the adapter
		// only needs to be downloaded once. If the files are already present — installed or
		// network-activated from another site — we just activate them here. Re-installing would
		// fail because the destination folder already exists.
		$installedFile = self::getInstalledMcpAdapterFile();
		if ( $installedFile ) {
			if ( ! current_user_can( 'activate_plugins' ) ) {
				return new \WP_REST_Response( [
					'success' => false,
					'message' => is_multisite()
						? __( 'The MCP Adapter is installed but not active on this site. Ask a network administrator to network-activate it.', 'all-in-one-seo-pack' )
						: __( 'You do not have permission to activate plugins.', 'all-in-one-seo-pack' )
				], 403 );
			}

			$activated = activate_plugin( $installedFile );
			if ( is_wp_error( $activated ) ) {
				return new \WP_REST_Response( [
					'success' => false,
					'message' => self::getPlainErrorMessage( $activated )
				], 500 );
			}

			return new \WP_REST_Response( [
				'success' => true,
				'plugin'  => $installedFile,
				'message' => __( 'MCP Adapter activated on this site.', 'all-in-one-seo-pack' )
			], 200 );
		}

		// Not on disk yet — downloading and installing plugin files is a network-level operation
		// on multisite, available only to network administrators.
		if ( ! current_user_can( 'install_plugins' ) || ! current_user_can( 'activate_plugins' ) ) {
			return new \WP_REST_Response( [
				'success' => false,
				'message' => is_multisite()
					? __( 'On multisite, the MCP Adapter must be installed and network-activated by a network administrator before it is available on this site.', 'all-in-one-seo-pack' )
					: __( 'You do not have permission to install plugins.', 'all-in-one-seo-pack' )
			], 403 );
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
				'message' => self::getPlainErrorMessage( $result )
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
				'message' => self::getPlainErrorMessage( $activated )
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
		$status = aioseo()->helpers->applicationPasswordsStatus( $userId );
		if ( empty( $status['available'] ) ) {
			return new \WP_REST_Response( [
				'success' => false,
				'message' => self::getAppPasswordsUnavailableMessage( $status )
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

	/**
	 * Returns a WP_Error's message as plain text.
	 *
	 * NOTE: activate_plugin() and the upgrader return HTML (e.g. `<p><strong>Error:</strong> …</p>`), which the MCP tab prints as text.
	 *
	 * @since 5.0.4
	 *
	 * @param  \WP_Error $error The error.
	 * @return string           The message without markup.
	 */
	private static function getPlainErrorMessage( $error ) {
		// Keep paragraphs apart once their tags are gone.
		$message = str_ireplace( '</p>', ' </p>', $error->get_error_message() );

		return trim( preg_replace( '/\s+/', ' ', wp_strip_all_tags( $message ) ) );
	}

	/**
	 * Returns an actionable message explaining why an Application Password can't be generated.
	 *
	 * NOTE: The admin UI renders its own guidance on load, so this mostly surfaces to direct API
	 * callers, or when the feature was disabled after the page loaded.
	 *
	 * @since 5.0.4
	 *
	 * @param  array  $status The status from {@see \AIOSEO\Plugin\Common\Utils\Helpers::applicationPasswordsStatus()}.
	 * @return string         The message to return to the caller.
	 */
	private static function getAppPasswordsUnavailableMessage( $status ) {
		if ( 'https' === $status['cause'] ) {
			return __( 'Application Passwords are disabled because this site isn\'t served over HTTPS. Switch your site to HTTPS to enable them.', 'all-in-one-seo-pack' ); // phpcs:ignore Generic.Files.LineLength.MaxExceeded
		}

		if ( 'user' === $status['scope'] ) {
			if ( ! empty( $status['pluginName'] ) && ! empty( $status['networkAdmin'] ) && empty( $status['canManage'] ) ) {
				return sprintf(
					// Translators: 1 - A plugin name, e.g. "Solid Security".
					__( 'Application Passwords are disabled for your account by %1$s. Ask a network administrator to allow your account to use them in the %1$s settings.', 'all-in-one-seo-pack' ), // phpcs:ignore Generic.Files.LineLength.MaxExceeded
					$status['pluginName']
				);
			}

			if ( ! empty( $status['pluginName'] ) ) {
				return sprintf(
					// Translators: 1 - A plugin name, e.g. "Solid Security".
					__( 'Application Passwords are disabled for your account by %1$s. Allow your account to use them in the %1$s settings, or ask an administrator to do it.', 'all-in-one-seo-pack' ), // phpcs:ignore Generic.Files.LineLength.MaxExceeded
					$status['pluginName']
				);
			}

			return __( 'Application Passwords are disabled for your account, most likely by a security or two-factor plugin. Ask an administrator to allow them for your account.', 'all-in-one-seo-pack' ); // phpcs:ignore Generic.Files.LineLength.MaxExceeded
		}

		if ( empty( $status['canManage'] ) && ! empty( $status['networkAdmin'] ) ) {
			if ( 'disableApplicationPasswords' === $status['plugin'] ) {
				return sprintf(
					// Translators: 1 - A plugin name, e.g. "Disable Application Passwords".
					__( 'Application Passwords are disabled by the %1$s plugin. Ask a network administrator to deactivate it.', 'all-in-one-seo-pack' ), // phpcs:ignore Generic.Files.LineLength.MaxExceeded
					$status['pluginName']
				);
			}

			return sprintf(
				// Translators: 1 - A plugin name, e.g. "Wordfence".
				__( 'Application Passwords appear to be disabled by %1$s. Ask a network administrator to re-enable them in the %1$s settings.', 'all-in-one-seo-pack' ), // phpcs:ignore Generic.Files.LineLength.MaxExceeded
				$status['pluginName']
			);
		}

		if ( empty( $status['canManage'] ) ) {
			if ( 'disableApplicationPasswords' === $status['plugin'] ) {
				return sprintf(
					// Translators: 1 - A plugin name, e.g. "Disable Application Passwords".
					__( 'Application Passwords are disabled by the %1$s plugin. Ask an administrator to deactivate it.', 'all-in-one-seo-pack' ), // phpcs:ignore Generic.Files.LineLength.MaxExceeded
					$status['pluginName']
				);
			}

			if ( ! empty( $status['pluginName'] ) ) {
				return sprintf(
					// Translators: 1 - A plugin name, e.g. "Wordfence".
					__( 'Application Passwords appear to be disabled by %1$s. Ask an administrator to re-enable them in the %1$s settings.', 'all-in-one-seo-pack' ), // phpcs:ignore Generic.Files.LineLength.MaxExceeded
					$status['pluginName']
				);
			}

			return __( 'Application Passwords are disabled on this site, most likely by a security plugin. Ask an administrator to re-enable them.', 'all-in-one-seo-pack' ); // phpcs:ignore Generic.Files.LineLength.MaxExceeded
		}

		if ( 'disableApplicationPasswords' === $status['plugin'] ) {
			return sprintf(
				// Translators: 1 - A plugin name, e.g. "Disable Application Passwords".
				__( 'Application Passwords are disabled by the %1$s plugin. Deactivate it to re-enable them, then try again.', 'all-in-one-seo-pack' ), // phpcs:ignore Generic.Files.LineLength.MaxExceeded
				$status['pluginName']
			);
		}

		if ( ! empty( $status['pluginName'] ) ) {
			return sprintf(
				// Translators: 1 - A plugin name, e.g. "Wordfence".
				__( 'Application Passwords appear to be disabled by %1$s. Re-enable them in the %1$s settings and try again.', 'all-in-one-seo-pack' ), // phpcs:ignore Generic.Files.LineLength.MaxExceeded
				$status['pluginName']
			);
		}

		return __( 'Application Passwords are disabled on this site. Security plugins commonly turn this feature off — check your security plugin\'s settings, or remove any filter or constant overriding it.', 'all-in-one-seo-pack' ); // phpcs:ignore Generic.Files.LineLength.MaxExceeded
	}
}