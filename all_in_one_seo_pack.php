<?php

/*
Plugin Name: All In One SEO Pack
Plugin URI: https://semperplugins.com/all-in-one-seo-pack-pro-version/
Description: Out-of-the-box SEO for your WordPress blog. Features like XML Sitemaps, SEO for custom post types, SEO for blogs or business sites, SEO for ecommerce sites, and much more. More than 50 million downloads since 2007.
Version: 2.11
Author: Michael Torbert
Author URI: https://semperplugins.com/all-in-one-seo-pack-pro-version/
Text Domain: all-in-one-seo-pack
Domain Path: /i18n/
*/

/*
Copyright (C) 2007-2019 Michael Torbert, https://semperfiwebdesign.com

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; version 2 of the License.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

/**
 * All in One SEO Pack.
 * The original WordPress SEO plugin.
 *
 * @package All-in-One-SEO-Pack
 * @version 2.11
 */

if ( ! defined( 'AIOSEOPPRO' ) ) {
	define( 'AIOSEOPPRO', false );
}
if ( ! defined( 'AIOSEOP_VERSION' ) ) {
	define( 'AIOSEOP_VERSION', '2.11' );
}
global $aioseop_plugin_name;
$aioseop_plugin_name = 'All in One SEO Pack';

/*
 * DO NOT EDIT BELOW THIS LINE.
 */

if ( ! defined( 'ABSPATH' ) ) {
	return;
}

if ( AIOSEOPPRO ) {

	add_action( 'admin_head', 'disable_all_in_one_free', 1 );

}

if ( ! function_exists( 'aiosp_add_cap' ) ) {

	function aiosp_add_cap() {
		/*
		 TODO we should put this into an install script. We just need to make sure it runs soon enough and we need to make
		 sure people updating from previous versions have access to it.
		*/

		$role = get_role( 'administrator' );
		if ( is_object( $role ) ) {
			$role->add_cap( 'aiosp_manage_seo' );
		}
	}
}
add_action( 'plugins_loaded', 'aiosp_add_cap' );

if ( ! defined( 'AIOSEOP_PLUGIN_NAME' ) ) {
	define( 'AIOSEOP_PLUGIN_NAME', $aioseop_plugin_name );
}

if ( ! defined( 'AIOSEOP_PLUGIN_DIR' ) ) {
	define( 'AIOSEOP_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
} elseif ( AIOSEOP_PLUGIN_DIR !== plugin_dir_path( __FILE__ ) ) {
	/*
	 This is not a great message.
		add_action( 'admin_notices', create_function( '', 'echo "' . "<div class='error'>" . sprintf(
					__( "%s detected a conflict; please deactivate the plugin located in %s.", 'all-in-one-seo-pack' ),
					$aioseop_plugin_name, AIOSEOP_PLUGIN_DIR ) . "</div>" . '";' ) );
	*/
	return;
}

if ( ! defined( 'AIOSEOP_PLUGIN_BASENAME' ) ) {
	define( 'AIOSEOP_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
}
if ( ! defined( 'AIOSEOP_PLUGIN_DIRNAME' ) ) {
	define( 'AIOSEOP_PLUGIN_DIRNAME', dirname( AIOSEOP_PLUGIN_BASENAME ) );
}
if ( ! defined( 'AIOSEOP_PLUGIN_URL' ) ) {
	define( 'AIOSEOP_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
}
if ( ! defined( 'AIOSEOP_PLUGIN_IMAGES_URL' ) ) {
	define( 'AIOSEOP_PLUGIN_IMAGES_URL', AIOSEOP_PLUGIN_URL . 'images/' );
}
if ( ! defined( 'AIOSEOP_BASELINE_MEM_LIMIT' ) ) {
	define( 'AIOSEOP_BASELINE_MEM_LIMIT', 268435456 );
} // 256MB
if ( ! defined( 'WP_CONTENT_URL' ) ) {
	define( 'WP_CONTENT_URL', site_url() . '/wp-content' );
}
if ( ! defined( 'WP_ADMIN_URL' ) ) {
	define( 'WP_ADMIN_URL', site_url() . '/wp-admin' );
}
if ( ! defined( 'WP_CONTENT_DIR' ) ) {
	define( 'WP_CONTENT_DIR', ABSPATH . 'wp-content' );
}
if ( ! defined( 'WP_PLUGIN_URL' ) ) {
	define( 'WP_PLUGIN_URL', WP_CONTENT_URL . '/plugins' );
}
if ( ! defined( 'WP_PLUGIN_DIR' ) ) {
	define( 'WP_PLUGIN_DIR', WP_CONTENT_DIR . '/plugins' );
}

global $aiosp, $aioseop_options, $aioseop_modules, $aioseop_module_list, $aiosp_activation, $aioseop_mem_limit, $aioseop_get_pages_start, $aioseop_admin_menu;
$aioseop_get_pages_start = $aioseop_admin_menu = 0;

if ( AIOSEOPPRO ) {
	global $aioseop_update_checker;
}

$aioseop_options = get_option( 'aioseop_options' );

// @codingStandardsIgnoreStart
$aioseop_mem_limit = @ini_get( 'memory_limit' );
// @codingStandardsIgnoreEnd

if ( ! function_exists( 'aioseop_convert_bytestring' ) ) {
	/**
	 * @param $byte_string
	 *
	 * @return int
	 */
	function aioseop_convert_bytestring( $byte_string ) {
		$num = 0;
		preg_match( '/^\s*([0-9.]+)\s*([KMGTPE])B?\s*$/i', $byte_string, $matches );
		if ( ! empty( $matches ) ) {
			$num = (float) $matches[1];
			switch ( strtoupper( $matches[2] ) ) {
				case 'E':
					$num *= 1024;
					// fall through.
				case 'P':
					$num *= 1024;
					// fall through.
				case 'T':
					$num *= 1024;
					// fall through.
				case 'G':
					$num *= 1024;
					// fall through.
				case 'M':
					$num *= 1024;
					// fall through.
				case 'K':
					$num *= 1024;
			}
		}

		return intval( $num );
	}
}

if ( is_array( $aioseop_options ) && isset( $aioseop_options['modules'] ) && isset( $aioseop_options['modules']['aiosp_performance_options'] ) ) {
	$perf_opts = $aioseop_options['modules']['aiosp_performance_options'];
	if ( isset( $perf_opts['aiosp_performance_memory_limit'] ) ) {
		$aioseop_mem_limit = $perf_opts['aiosp_performance_memory_limit'];
	}
	if ( isset( $perf_opts['aiosp_performance_execution_time'] ) && ( '' !== $perf_opts['aiosp_performance_execution_time'] ) ) {
		// @codingStandardsIgnoreStart
		@ini_set( 'max_execution_time', (int) $perf_opts['aiosp_performance_execution_time'] );
		@set_time_limit( (int) $perf_opts['aiosp_performance_execution_time'] );
		// @codingStandardsIgnoreEnd
	}
} else {
	$aioseop_mem_limit = aioseop_convert_bytestring( $aioseop_mem_limit );
	if ( ( $aioseop_mem_limit > 0 ) && ( $aioseop_mem_limit < AIOSEOP_BASELINE_MEM_LIMIT ) ) {
		$aioseop_mem_limit = AIOSEOP_BASELINE_MEM_LIMIT;
	}
}

if ( ! empty( $aioseop_mem_limit ) ) {
	if ( ! is_int( $aioseop_mem_limit ) ) {
		$aioseop_mem_limit = aioseop_convert_bytestring( $aioseop_mem_limit );
	}
	if ( ( $aioseop_mem_limit > 0 ) && ( $aioseop_mem_limit <= AIOSEOP_BASELINE_MEM_LIMIT ) ) {
		// @codingStandardsIgnoreStart
		@ini_set( 'memory_limit', $aioseop_mem_limit );
		// @codingStandardsIgnoreEnd
	}
}

$aiosp_activation    = false;
$aioseop_module_list = array(
	'sitemap',
	'opengraph',
	'robots',
	'file_editor',
	'importer_exporter',
	'bad_robots',
	'performance',
); // list all available modules here

if ( AIOSEOPPRO ) {
	$aioseop_module_list[] = 'video_sitemap';
}

if ( class_exists( 'All_in_One_SEO_Pack' ) ) {
	add_action( 'admin_notices', 'admin_notices_already_defined' );
	function admin_notices_already_defined() {
		echo "<div class=\'error\'>The All In One SEO Pack class is already defined";
		if ( class_exists( 'ReflectionClass' ) ) {
			$_r = new ReflectionClass( 'All_in_One_SEO_Pack' );
			echo ' in ' . $_r->getFileName();
		}
		echo ', preventing All In One SEO Pack from loading.</div>';
	}

	return;
}

if ( AIOSEOPPRO ) {

	require( AIOSEOP_PLUGIN_DIR . 'pro/sfwd_update_checker.php' );
	$aiosp_update_url = 'https://semperplugins.com/upgrade_plugins.php';
	if ( defined( 'AIOSEOP_UPDATE_URL' ) ) {
		$aiosp_update_url = AIOSEOP_UPDATE_URL;
	}
	$aioseop_update_checker = new SFWD_Update_Checker(
		$aiosp_update_url,
		__FILE__,
		'aioseop'
	);

	$aioseop_update_checker->plugin_name     = AIOSEOP_PLUGIN_NAME;
	$aioseop_update_checker->plugin_basename = AIOSEOP_PLUGIN_BASENAME;
	if ( ! empty( $aioseop_options['aiosp_license_key'] ) ) {
		$aioseop_update_checker->license_key = $aioseop_options['aiosp_license_key'];
	} else {
		$aioseop_update_checker->license_key = '';
	}
	$aioseop_update_checker->options_page = AIOSEOP_PLUGIN_DIRNAME . '/aioseop_class.php';
	$aioseop_update_checker->renewal_page = 'https://semperplugins.com/all-in-one-seo-pack-pro-version/';

	$aioseop_update_checker->addQueryArgFilter( array( $aioseop_update_checker, 'add_secret_key' ) );
}


if ( ! function_exists( 'aioseop_activate' ) ) {

	function aioseop_activate() {

		// Check if we just got activated.
		global $aiosp_activation;
		if ( AIOSEOPPRO ) {
			global $aioseop_update_checker;
		}
		$aiosp_activation = true;

		// These checks might be duplicated in the function being called.
		if ( ! is_network_admin() || ! isset( $_GET['activate-multi'] ) ) {
			set_transient( '_aioseop_activation_redirect', true, 30 ); // Sets 30 second transient for welcome screen redirect on activation.
		}

		delete_user_meta( get_current_user_id(), 'aioseop_yst_detected_notice_dismissed' );

		if ( AIOSEOPPRO ) {
			$aioseop_update_checker->checkForUpdates();
		}
	}
}

add_action( 'plugins_loaded', 'aioseop_init_class' );

if ( ! function_exists( 'aiosp_plugin_row_meta' ) ) {

	add_filter( 'plugin_row_meta', 'aiosp_plugin_row_meta', 10, 2 );

	/**
	 * @param $actions
	 * @param $plugin_file
	 *
	 * @return array
	 */
	function aiosp_plugin_row_meta( $actions, $plugin_file ) {

			$action_links = array(

				'settings' => array(
					'label' => __( 'Feature Request/Bug Report', 'all-in-one-seo-pack' ),
					'url'   => 'https://github.com/semperfiwebdesign/all-in-one-seo-pack/issues/new',
				),

			);

		return aiosp_action_links( $actions, $plugin_file, $action_links, 'after' );
	}
}

if ( ! function_exists( 'aiosp_add_action_links' ) ) {


	add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'aiosp_add_action_links', 10, 2 );

	/**
	 * @param $actions
	 * @param $plugin_file
	 *
	 * @return array
	 */
	function aiosp_add_action_links( $actions, $plugin_file ) {
		if ( ! is_array( $actions ) ) {
			return $actions;
		}

		$aioseop_plugin_dirname = AIOSEOP_PLUGIN_DIRNAME;
		$action_links           = array();
		$action_links           = array(
			'settings' => array(
				'label' => __( 'SEO Settings', 'all-in-one-seo-pack' ),
				'url'   => get_admin_url( null, "admin.php?page=$aioseop_plugin_dirname/aioseop_class.php" ),
			),

			'forum' => array(
				'label' => __( 'Support Forum', 'all-in-one-seo-pack' ),
				'url'   => 'https://semperplugins.com/support/',
			),

			'docs' => array(
				'label' => __( 'Documentation', 'all-in-one-seo-pack' ),
				'url'   => 'https://semperplugins.com/documentation/',
			),

		);

		unset( $actions['edit'] );

		if ( ! AIOSEOPPRO ) {
			$action_links['proupgrade'] =
				array(
					'label' => __( 'Upgrade to Pro', 'all-in-one-seo-pack' ),
					'url'   => 'https://semperplugins.com/plugins/all-in-one-seo-pack-pro-version/?loc=plugins',

				);
		}

		return aiosp_action_links( $actions, $plugin_file, $action_links, 'before' );
	}
}

if ( ! function_exists( 'aiosp_action_links' ) ) {

	/**
	 * @param $actions
	 * @param $plugin_file
	 * @param array $action_links
	 * @param string $position
	 *
	 * @return array
	 */
	function aiosp_action_links( $actions, $plugin_file, $action_links = array(), $position = 'after' ) {
		static $plugin;
		if ( ! isset( $plugin ) ) {
			$plugin = plugin_basename( __FILE__ );
		}
		if ( $plugin === $plugin_file && ! empty( $action_links ) ) {
			foreach ( $action_links as $key => $value ) {
				$link = array( $key => '<a href="' . $value['url'] . '">' . $value['label'] . '</a>' );
				if ( 'after' === $position ) {
					$actions = array_merge( $actions, $link );
				} else {
					$actions = array_merge( $link, $actions );
				}
			}//foreach
		}// if
		return $actions;
	}
}

if ( ! function_exists( 'aioseop_init_class' ) ) {
	/**
	 * Inits All-in-One-Seo plugin class.
	 *
	 * @since ?? // When was this added?
	 * @since 2.3.12.3 Loads third party compatibility class.
	 */
	function aioseop_init_class() {
		global $aiosp;
		load_plugin_textdomain( 'all-in-one-seo-pack', false, dirname( plugin_basename( __FILE__ ) ) . '/i18n/' );
		require_once( AIOSEOP_PLUGIN_DIR . 'inc/aioseop_functions.php' );
		require_once( AIOSEOP_PLUGIN_DIR . 'aioseop_class.php' );
		require_once( AIOSEOP_PLUGIN_DIR . 'inc/aioseop_updates_class.php' );
		require_once( AIOSEOP_PLUGIN_DIR . 'inc/commonstrings.php' );
		require_once( AIOSEOP_PLUGIN_DIR . 'admin/display/general-metaboxes.php' );
		require_once( AIOSEOP_PLUGIN_DIR . 'inc/aiosp_common.php' );
		require_once( AIOSEOP_PLUGIN_DIR . 'admin/meta_import.php' );
		require_once( AIOSEOP_PLUGIN_DIR . 'inc/translations.php' );
		require_once( AIOSEOP_PLUGIN_DIR . 'public/opengraph.php' );
		require_once( AIOSEOP_PLUGIN_DIR . 'inc/compatability/abstract/aiosep_compatible.php' );
		require_once( AIOSEOP_PLUGIN_DIR . 'inc/compatability/compat-init.php' );
		require_once( AIOSEOP_PLUGIN_DIR . 'public/front.php' );
		require_once( AIOSEOP_PLUGIN_DIR . 'public/google-analytics.php' );
		require_once( AIOSEOP_PLUGIN_DIR . 'admin/display/welcome.php' );
		require_once( AIOSEOP_PLUGIN_DIR . 'admin/display/dashboard_widget.php' );
		require_once( AIOSEOP_PLUGIN_DIR . 'admin/display/menu.php' );

		$aioseop_welcome = new aioseop_welcome(); // TODO move this to updates file.

		if ( AIOSEOPPRO ) {
			require_once( AIOSEOP_PLUGIN_DIR . 'pro/class-aio-pro-init.php' ); // Loads pro files and other pro init stuff.
		}
		aiosp_seometa_import(); // call importer functions... this should be moved somewhere better

		$aiosp = new All_in_One_SEO_Pack();

		$aioseop_updates = new AIOSEOP_Updates();

		if ( AIOSEOPPRO ) {
			$aioseop_pro_updates = new AIOSEOP_Pro_Updates();
			add_action( 'admin_init', array( $aioseop_pro_updates, 'version_updates' ), 12 );
		}

		add_action( 'admin_init', 'aioseop_welcome' );

		if ( aioseop_option_isset( 'aiosp_unprotect_meta' ) ) {
			add_filter( 'is_protected_meta', 'aioseop_unprotect_meta', 10, 3 );
		}

		add_action( 'init', array( $aiosp, 'add_hooks' ) );
		add_action( 'admin_init', array( $aioseop_updates, 'version_updates' ), 11 );

		if ( defined( 'DOING_AJAX' ) && ! empty( $_POST ) && ! empty( $_POST['action'] ) && 'aioseop_ajax_scan_header' === $_POST['action'] ) {
			remove_action( 'init', array( $aiosp, 'add_hooks' ) );
			add_action( 'admin_init', 'aioseop_scan_post_header' );
			add_action( 'shutdown', 'aioseop_ajax_scan_header' ); // if the action doesn't run -- pdb
			include_once( ABSPATH . 'wp-admin/includes/screen.php' );
			global $current_screen;
			if ( class_exists( 'WP_Screen' ) ) {
				$current_screen = WP_Screen::get( 'front' );
			}
		}
	}
}



if ( ! function_exists( 'aioseop_welcome' ) ) {
	function aioseop_welcome() {
		if ( get_transient( '_aioseop_activation_redirect' ) ) {
			$aioseop_welcome = new aioseop_welcome();
			delete_transient( '_aioseop_activation_redirect' );
			$aioseop_welcome->init( true );
		}

	}
}

add_action( 'init', 'aioseop_load_modules', 1 );
// add_action( 'after_setup_theme', 'aioseop_load_modules' );
if ( is_admin() || defined( 'AIOSEOP_UNIT_TESTING' ) ) {
	add_action( 'wp_ajax_aioseop_ajax_save_meta', 'aioseop_ajax_save_meta' );
	add_action( 'wp_ajax_aioseop_ajax_save_url', 'aioseop_ajax_save_url' );
	add_action( 'wp_ajax_aioseop_ajax_delete_url', 'aioseop_ajax_delete_url' );
	add_action( 'wp_ajax_aioseop_ajax_scan_header', 'aioseop_ajax_scan_header' );
	if ( AIOSEOPPRO ) {
		add_action( 'wp_ajax_aioseop_ajax_facebook_debug', 'aioseop_ajax_facebook_debug' );
	}
	add_action( 'wp_ajax_aioseop_ajax_save_settings', 'aioseop_ajax_save_settings' );
	add_action( 'wp_ajax_aioseop_ajax_get_menu_links', 'aioseop_ajax_get_menu_links' );
	add_action( 'wp_ajax_aioseo_dismiss_yst_notice', 'aioseop_update_yst_detected_notice' );
	add_action( 'wp_ajax_aioseo_dismiss_visibility_notice', 'aioseop_update_user_visibilitynotice' );
	add_action( 'wp_ajax_aioseo_dismiss_woo_upgrade_notice', 'aioseop_woo_upgrade_notice_dismissed' );
	add_action( 'wp_ajax_aioseo_dismiss_sitemap_max_url_notice', 'aioseop_sitemap_max_url_notice_dismissed' );
}

if ( ! function_exists( 'aioseop_scan_post_header' ) ) {
	function aioseop_scan_post_header() {
		require_once( ABSPATH . WPINC . '/default-filters.php' );
		global $wp_query;
		$wp_query->query_vars['paged'] = 0;
		query_posts( 'post_type=post&posts_per_page=1' );
		if ( have_posts() ) {
			the_post();
		}
	}
}

require_once( AIOSEOP_PLUGIN_DIR . 'aioseop-init.php' );

if ( ! function_exists( 'aioseop_install' ) ) {
	register_activation_hook( __FILE__, 'aioseop_install' );

	function aioseop_install() {
		aioseop_activate();
	}
}

if ( ! function_exists( 'disable_all_in_one_free' ) ) {
	function disable_all_in_one_free() {
		if ( AIOSEOPPRO && is_plugin_active( 'all-in-one-seo-pack/all_in_one_seo_pack.php' ) ) {
			deactivate_plugins( 'all-in-one-seo-pack/all_in_one_seo_pack.php' );
		}
	}
}
