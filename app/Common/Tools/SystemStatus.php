<?php
namespace AIOSEO\Plugin\Common\Tools;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SystemStatus {
	/**
	 * Get an aggregated list of all system info.
	 *
	 * @since 4.0.0
	 *
	 * @return array An array of system information.
	 */
	public static function getSystemStatusInfo() {
		return [
			'wordPress'       => self::getWordPressInfo(),
			'constants'       => self::getConstants(),
			'serverInfo'      => self::getServerInfo(),
			'muPlugins'       => self::mustUsePlugins(),
			'activeTheme'     => self::activeTheme(),
			'activePlugins'   => self::activePlugins(),
			'inactivePlugins' => self::inactivePlugins(),
			'database'        => self::getDatabaseInfo()
		];
	}

	/**
	 * Get an array of system info from WordPress.
	 *
	 * @since 4.0.0
	 *
	 * @return array An array of system info.
	 */
	public static function getWordPressInfo() {
		$uploadsDir    = wp_upload_dir();
		$version       = get_bloginfo( 'version' );
		$updates       = get_site_transient( 'update_core' );
		$updateVersion = ! empty( $updates->updates[0]->version ) ? $updates->updates[0]->version : '';
		if ( version_compare( $version, $updateVersion, '<' ) ) {
			$version .= ' (' . __( 'Latest version:', 'all-in-one-seo-pack' ) . ' ' . $updateVersion . ')';
		}

		return [
			'label'   => 'WordPress',
			'results' => [
				[
					'header' => __( 'Version', 'all-in-one-seo-pack' ),
					'value'  => $version
				],
				[
					'header' => __( 'Site Title', 'all-in-one-seo-pack' ),
					'value'  => get_bloginfo( 'name' )
				],
				[
					'header' => __( 'Site Language', 'all-in-one-seo-pack' ),
					'value'  => get_locale() ?: 'en_US'
				],
				[
					'header' => __( 'User Language', 'all-in-one-seo-pack' ),
					'value'  => get_user_locale( get_current_user_id() )
				],
				[
					'header' => __( 'Timezone', 'all-in-one-seo-pack' ),
					'value'  => wp_timezone_string()
				],
				[
					'header' => __( 'Home URL', 'all-in-one-seo-pack' ),
					'value'  => home_url()
				],
				[
					'header' => __( 'Site URL', 'all-in-one-seo-pack' ),
					'value'  => site_url()
				],
				[
					'header' => __( 'Permalink Structure', 'all-in-one-seo-pack' ),
					'value'  => get_option( 'permalink_structure' ) ? get_option( 'permalink_structure' ) : __( 'Default', 'all-in-one-seo-pack' )
				],
				[
					'header' => __( 'Multisite', 'all-in-one-seo-pack' ),
					'value'  => is_multisite() ? __( 'Yes', 'all-in-one-seo-pack' ) : __( 'No', 'all-in-one-seo-pack' )
				],
				[
					'header' => 'HTTPS',
					'value'  => is_ssl() ? __( 'Yes', 'all-in-one-seo-pack' ) : __( 'No', 'all-in-one-seo-pack' )
				],
				[
					'header' => __( 'User Count', 'all-in-one-seo-pack' ),
					'value'  => count_users()['total_users']
				],
				[
					'header' => __( 'Front Page Info', 'all-in-one-seo-pack' ),
					'value'  => 'page' === get_option( 'show_on_front' ) ? get_option( 'show_on_front' ) . ' [ID: ' . get_option( 'page_on_front' ) . ']' : get_option( 'show_on_front' )
				],
				[
					'header' => __( 'Search Engine Visibility', 'all-in-one-seo-pack' ),
					'value'  => get_option( 'blog_public' ) ? __( 'Visible', 'all-in-one-seo-pack' ) : __( 'Hidden', 'all-in-one-seo-pack' )
				],
				[
					'header' => __( 'Upload Directory Info', 'all-in-one-seo-pack' ),
					'value'  =>
						__( 'Path:', 'all-in-one-seo-pack' ) . ' ' . $uploadsDir['path'] . ', ' .
						__( 'Url:', 'all-in-one-seo-pack' ) . ' ' . $uploadsDir['url'] . ', ' .
						__( 'Base Directory:', 'all-in-one-seo-pack' ) . ' ' . $uploadsDir['basedir'] . ', ' .
						__( 'Base URL:', 'all-in-one-seo-pack' ) . ' ' . $uploadsDir['baseurl']
				]
			]
		];
	}

	/**
	 * Get an array of database info from WordPress.
	 *
	 * @since 4.4.5
	 *
	 * @return array An array of database info.
	 */
	public static function getDatabaseInfo() {
		$dbInfo = aioseo()->core->db->getDatabaseInfo();
		if ( empty( $dbInfo['tables'] ) ) {
			return [];
		}

		if ( ! aioseo()->helpers->isDev() ) {
			return [];
		}

		$results = [];
		$tables  = array_merge( $dbInfo['tables']['aioseo'], $dbInfo['tables']['other'] );
		foreach ( $tables as $tableName => $tableData ) {
			$results[] = [
				'header' => $tableName,
				'value'  => sprintf(
					// Translators: %1$s is the data size, %2$s is the index size, %3$s is the engine type.
					__( 'Data: %1$.2f MB / Index: %2$.2f MB / Engine: %3$s / Collation: %4$s', 'all-in-one-seo-pack' ),
					$tableData['data'],
					$tableData['index'],
					$tableData['engine'],
					$tableData['collation']
				)
			];
		}

		return [
			'label'   => __( 'Database', 'all-in-one-seo-pack' ),
			'results' => array_merge( [
				[
					'header' => __( 'Database Size', 'all-in-one-seo-pack' ),
					'value'  => sprintf( '%.2f MB', $dbInfo['size']['data'] + $dbInfo['size']['index'] )
				]
			], $results )
		];
	}

	/**
	 * Get an array of system info from WordPress constants.
	 *
	 * @since 4.0.0
	 *
	 * @return array An array of system info.
	 */
	public static function getConstants() {
		return [
			'label'   => __( 'Constants', 'all-in-one-seo-pack' ),
			'results' => [
				[
					'header' => 'ABSPATH',
					'value'  => ABSPATH
				],
				[
					'header' => 'WP_CONTENT_DIR',
					'value'  => defined( 'WP_CONTENT_DIR' ) ? ( WP_CONTENT_DIR ? WP_CONTENT_DIR : __( 'Disabled', 'all-in-one-seo-pack' ) ) : __( 'Not set', 'all-in-one-seo-pack' )
				],
				[
					'header' => 'WP_CONTENT_URL',
					'value'  => defined( 'WP_CONTENT_URL' ) ? ( WP_CONTENT_URL ? WP_CONTENT_URL : __( 'Disabled', 'all-in-one-seo-pack' ) ) : __( 'Not set', 'all-in-one-seo-pack' )
				],
				[
					'header' => 'UPLOADS',
					'value'  => defined( 'UPLOADS' ) ? ( UPLOADS ? UPLOADS : __( 'Disabled', 'all-in-one-seo-pack' ) ) : __( 'Not set', 'all-in-one-seo-pack' )
				],
				[
					'header' => 'WP_DEBUG',
					'value'  => defined( 'WP_DEBUG' ) ? ( WP_DEBUG ? WP_DEBUG : __( 'Disabled', 'all-in-one-seo-pack' ) ) : __( 'Not set', 'all-in-one-seo-pack' )
				],
				[
					'header' => 'WP_DEBUG_LOG',
					'value'  => defined( 'WP_DEBUG_LOG' ) ? ( WP_DEBUG_LOG ? WP_DEBUG_LOG : __( 'Disabled', 'all-in-one-seo-pack' ) ) : __( 'Not set', 'all-in-one-seo-pack' )
				],
				[
					'header' => 'WP_DEBUG_DISPLAY',
					'value'  => defined( 'WP_DEBUG_DISPLAY' ) ? ( WP_DEBUG_DISPLAY ? WP_DEBUG_DISPLAY : __( 'Disabled', 'all-in-one-seo-pack' ) ) : __( 'Not set', 'all-in-one-seo-pack' )
				],
				[
					'header' => 'WPS_DEBUG',
					'value'  => defined( 'WPS_DEBUG' ) ? ( WPS_DEBUG ? WPS_DEBUG : __( 'Disabled', 'all-in-one-seo-pack' ) ) : __( 'Not set', 'all-in-one-seo-pack' )
				],
				[
					'header' => 'DB_CHARSET',
					'value'  => defined( 'DB_CHARSET' ) ? ( DB_CHARSET ? DB_CHARSET : __( 'Disabled', 'all-in-one-seo-pack' ) ) : __( 'Not set', 'all-in-one-seo-pack' )
				],
				[
					'header' => 'DB_COLLATE',
					'value'  => defined( 'DB_COLLATE' ) ? ( DB_COLLATE ? DB_COLLATE : __( 'Disabled', 'all-in-one-seo-pack' ) ) : __( 'Not set', 'all-in-one-seo-pack' )
				]
			]
		];
	}

	/**
	 * Get an array of system info from the server.
	 *
	 * @since 4.0.0
	 *
	 * @return array An array of system info.
	 */
	public static function getServerInfo() {
		$sqlMode   = null;
		$mysqlInfo = aioseo()->core->db->db->get_results( "SHOW VARIABLES LIKE 'sql_mode'" );
		if ( ! empty( $mysqlInfo ) && is_array( $mysqlInfo ) ) {
			$sqlMode = $mysqlInfo[0]->Value;
		}

		$dbServerInfo = method_exists( aioseo()->core->db->db, 'db_server_info' )
			? aioseo()->core->db->db->db_server_info()
			: ( function_exists( 'mysqli_get_server_info' )
				? mysqli_get_server_info( aioseo()->core->db->db->dbh ) // phpcs:ignore WordPress.DB.RestrictedFunctions.mysql_mysqli_get_server_info
				: ''
		);

		return [
			'label'   => __( 'Server Info', 'all-in-one-seo-pack' ),
			'results' => [
				[
					'header' => __( 'Operating System', 'all-in-one-seo-pack' ),
					'value'  => PHP_OS
				],
				[
					'header' => __( 'Web Server', 'all-in-one-seo-pack' ),
					'value'  => ! empty( $_SERVER['SERVER_SOFTWARE'] ) ? sanitize_text_field( wp_unslash( $_SERVER['SERVER_SOFTWARE'] ) ) : __( 'unknown', 'all-in-one-seo-pack' )
				],
				[
					'header' => __( 'Memory Usage', 'all-in-one-seo-pack' ),
					'value'  => function_exists( 'memory_get_usage' ) ? round( memory_get_usage() / 1024 / 1024, 2 ) . 'M' : __( 'N/A', 'all-in-one-seo-pack' )
				],
				[
					'header' => __( 'Database Powered By', 'all-in-one-seo-pack' ),
					'value'  => stripos( $dbServerInfo, 'mariadb' ) !== false ? 'MariaDB' : 'MySQL'
				],
				[
					'header' => __( 'Database Version', 'all-in-one-seo-pack' ),
					'value'  => aioseo()->core->db->db->db_version()
				],
				[
					'header' => __( 'SQL Mode', 'all-in-one-seo-pack' ),
					'value'  => $sqlMode ?? __( 'Not Set', 'all-in-one-seo-pack' ),
				],
				[
					'header' => __( 'PHP Version', 'all-in-one-seo-pack' ),
					'value'  => PHP_VERSION
				],
				[
					'header' => __( 'PHP Memory Limit', 'all-in-one-seo-pack' ),
					'value'  => ini_get( 'memory_limit' )
				],
				[
					'header' => __( 'PHP Max Upload Size', 'all-in-one-seo-pack' ),
					'value'  => ini_get( 'upload_max_filesize' )
				],
				[
					'header' => __( 'PHP Max Post Size', 'all-in-one-seo-pack' ),
					'value'  => ini_get( 'post_max_size' )
				],
				[
					'header' => __( 'PHP Max Script Execution Time', 'all-in-one-seo-pack' ),
					'value'  => ini_get( 'max_execution_time' )
				],
				[
					'header' => __( 'PHP Exif Support', 'all-in-one-seo-pack' ),
					'value'  => is_callable( 'exif_read_data' ) ? __( 'Yes', 'all-in-one-seo-pack' ) : __( 'No', 'all-in-one-seo-pack' )
				],
				[
					'header' => __( 'PHP IPTC Support', 'all-in-one-seo-pack' ),
					'value'  => is_callable( 'iptcparse' ) ? __( 'Yes', 'all-in-one-seo-pack' ) : __( 'No', 'all-in-one-seo-pack' )
				],
				[
					'header' => __( 'PHP XML Support', 'all-in-one-seo-pack' ),
					'value'  => is_callable( 'xml_parser_create' ) ? __( 'Yes', 'all-in-one-seo-pack' ) : __( 'No', 'all-in-one-seo-pack' )
				]
			]
		];
	}

	/**
	 * Get an array of system info from the active theme.
	 *
	 * @since 4.0.0
	 *
	 * @return array An array of system info.
	 */
	public static function activeTheme() {
		$themeData = wp_get_theme();

		return [
			'label'   => __( 'Active Theme', 'all-in-one-seo-pack' ),
			'results' => [
				[
					'header' => $themeData->name,
					'value'  => $themeData->version
				]
			]
		];
	}

	/**
	 * Get an array of system info from must-use plugins.
	 *
	 * @since 4.0.0
	 *
	 * @return array An array of system info.
	 */
	public static function mustUsePlugins() {
		$plugins   = [];
		$muPlugins = get_mu_plugins();
		if ( ! empty( $muPlugins ) ) {
			foreach ( $muPlugins as $pluginData ) {
				$plugins[] = [
					'header' => $pluginData['Name'],
					'value'  => $pluginData['Version']
				];
			}
		}

		return [
			'label'   => __( 'Must-Use Plugins', 'all-in-one-seo-pack' ),
			'results' => $plugins
		];
	}

	/**
	 * Get an array of system info from active plugins.
	 *
	 * @since 4.0.0
	 *
	 * @return array An array of system info.
	 */
	public static function activePlugins() {
		$plugins       = [];
		$allPlugins    = get_plugins();
		$activePlugins = get_option( 'active_plugins', [] );
		$updates       = get_plugin_updates();
		if ( ! empty( $allPlugins ) ) {
			foreach ( $allPlugins as $pluginPath => $pluginData ) {
				if ( ! in_array( $pluginPath, $activePlugins, true ) ) {
					continue;
				}

				$update    = ( array_key_exists( $pluginPath, $updates ) ) ? ' (' . __( 'needs update', 'all-in-one-seo-pack' ) . ' - ' . $updates[ $pluginPath ]->update->new_version . ')' : '';
				$plugins[] = [
					'header' => $pluginData['Name'],
					'value'  => $pluginData['Version'] . $update
				];
			}
		}

		return [
			'label'   => __( 'Active Plugins', 'all-in-one-seo-pack' ),
			'results' => $plugins
		];
	}

	/**
	 * Get an array of system info from inactive plugins.
	 *
	 * @since 4.0.0
	 *
	 * @return array An array of system info.
	 */
	public static function inactivePlugins() {
		$plugins       = [];
		$allPlugins    = get_plugins();
		$activePlugins = get_option( 'active_plugins', [] );
		$updates       = get_plugin_updates();
		if ( ! empty( $allPlugins ) ) {
			foreach ( $allPlugins as $pluginPath => $pluginData ) {
				if ( in_array( $pluginPath, $activePlugins, true ) ) {
					continue;
				}

				$update    = ( array_key_exists( $pluginPath, $updates ) ) ? ' (' . __( 'needs update', 'all-in-one-seo-pack' ) . ' - ' . $updates[ $pluginPath ]->update->new_version . ')' : '';
				$plugins[] = [
					'header' => $pluginData['Name'],
					'value'  => $pluginData['Version'] . $update
				];
			}
		}

		return [
			'label'   => __( 'Inactive Plugins', 'all-in-one-seo-pack' ),
			'results' => $plugins
		];
	}
}