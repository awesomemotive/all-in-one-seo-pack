<?php
/**
 * PHPUnit bootstrap file
 *
 * @since 2.4.4.1
 *
 * @link https://github.com/JDGrimes/wpppb
 *
 * @package all-in-one-seo-pack
 */
/**
 * Bootstrap Configs
 */
// full path, no trailing slash
$wp_develop_dir   = 'C:/path-to-location/wordpress-develop';
$mu_plugin_dir    = 'C:/path-to-location/mu-plugins';
$plugin_dir       = 'C:/path-to-location/plugins';
$wp_default_theme = 'default';//twentyseventeen

// Disable xdebug backtrace. Why? Does it cause issues?
// @link https://xdebug.org/docs/all_functions
if ( function_exists( 'xdebug_disable' ) ) {
	xdebug_disable();
}

/**
 * WordPress Directory
 */
if ( false !== getenv( 'WP_DEVELOP_DIR' ) ) {
	$wp_develop_dir = getenv( 'WP_DEVELOP_DIR' );
} elseif ( empty( $wp_develop_dir ) || 'C:/path-to-location/wordpress-develop' === $wp_develop_dir ) {
	die( 'WP_DEVELOP_DIR isn\'t being set correctly in all-in-one-seo-pack/tests/bootstrap.php' );
}

/**
 * (MU) Plugin Directory
 *
 * 1. Manually set Directory (Configured above).
 * 2. Set Environment Var in IDE.
 * 3. Force directory to plugin's root folder.
 *
 * OPTION 2 DETAILS
 *
 * In PHPStorm, settings are located in Run > Edit Configurations.
 * Add PHPUnit as a Run Environment.
 * From there, you will need to...
 *   1. Give the configuration a name, for example: PHP Unit.
 *   2. Check the option ‘defined in the configuration file’.
 *   3. Check ‘use alternative configuration file’ and set the path to the phpunit.xml file.
 *   4. Set environment variables, you can do this by clicking the ‘…’ after the input field. You’ll get the next screen:
 * In Step 4, you'll add the Environment Variables.
 */

// MU Plugins Directory.
if ( false !== getenv( 'WPMU_PLUGIN_DIR' ) ) {
	// Opt 2.
	$mu_plugin_dir = getenv( 'WPMU_PLUGIN_DIR' );
} elseif ( ! isset( $mu_plugin_dir ) || empty( $mu_plugin_dir ) || 'C:/path-to-location/mu-plugins' === $mu_plugin_dir ) {
	// Opt 3.
	$mu_plugin_dir = $wp_develop_dir . '/src/wp-content/mu-plugins';;
}

// Plugins Directory.
if ( false !== getenv( 'WP_PLUGIN_DIR' ) ) {
	// Opt 2.
	$plugin_dir = getenv( 'WP_PLUGIN_DIR' );
} elseif ( ! isset( $plugin_dir ) || empty( $plugin_dir ) || 'C:/path-to-location/plugins' === $plugin_dir ) {
	// Opt 3.
	$plugin_dir = $wp_develop_dir . '/src/wp-content/plugins';
}

/**
 * Theme Default
 *
 * ToDo Add set Theme directory.
 */
if ( false !== getenv( 'WP_DEFAULT_THEME' ) ) {
	$wp_default_theme = getenv( 'WP_DEFAULT_THEME' );
}

/**
 * CONSTANTS
 */
define( 'WP_DEVELOP_DIR', $wp_develop_dir );
define( 'WP_CORE_DIR', $wp_develop_dir . '/src' );
define( 'WP_TESTS_DIR', $wp_develop_dir . '/tests/phpunit' );

define( 'AIOSEOP_DEVELOP_DIR', dirname( dirname( __DIR__ ) ) );
define( 'AIOSEOP_CORE_DIR', dirname( __DIR__ ) );
define( 'AIOSEOP_TESTS_DIR', __DIR__ );

define( 'WP_USE_THEMES', false );
define( 'WP_TESTS_FORCE_KNOWN_BUGS', true );
define( 'AIOSEOP_UNIT_TESTING', true );
define( 'AIOSEOP_UNIT_TESTING_DIR', dirname( __FILE__ ) );

// WP Constants Pre-Defined // full path, no trailing slash
define( 'WPMU_PLUGIN_DIR', $mu_plugin_dir );
define( 'WP_PLUGIN_DIR', $plugin_dir );
define( 'WP_DEFAULT_THEME', $wp_default_theme );

/**
 * Manually load the plugin being tested.
 *
 * Manually load AIOSEOP when WP's Bootstrap loads wp-settings.php
 *
 * @link https://wordpress.stackexchange.com/questions/145281/phpunit-testing-wordpress-plugin
 */
function _manually_load_plugin() {
	require dirname( dirname( __FILE__ ) ) . '/all_in_one_seo_pack.php';
}
// Give access to tests_add_filter() function.
require_once WP_DEVELOP_DIR . '/tests/phpunit/includes/functions.php';
tests_add_filter( 'muplugins_loaded', '_manually_load_plugin' );

// Start up the WP testing environment.
require( WP_DEVELOP_DIR . '/tests/phpunit/includes/bootstrap.php' );

/**
 * Common Environment Variables
 */
activate_plugin( 'all-in-one-seo-pack/all_in_one_seo_pack.php' );
global $current_user;
$current_user = new WP_User( 1 );
$current_user->set_role( 'administrator' );
wp_update_user( array( 'ID' => 1, 'first_name' => 'Admin', 'last_name' => 'User' ) );
