<?php
/**
 * Plugin Name: All in One SEO
 * Plugin URI:  https://aioseo.com/
 * Description: SEO for WordPress. Features like XML Sitemaps, SEO for custom post types, SEO for blogs, business sites, ecommerce sites, and much more. More than 100 million downloads since 2007.
 * Author:      All in One SEO Team
 * Author URI:  https://aioseo.com/
 * Version:     4.5.2.1
 * Text Domain: all-in-one-seo-pack
 * Domain Path: /languages
 *
 * All in One SEO is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * any later version.
 *
 * All in One SEO is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with AIOSEO. If not, see <https://www.gnu.org/licenses/>.
 *
 * @since     4.0.0
 * @author    All in One SEO Team
 * @package   AIOSEO\Plugin
 * @license   GPL-2.0+
 * @copyright Copyright (c) 2023, All in One SEO
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once dirname( __FILE__ ) . '/app/init/init.php';

// Check if this plugin should be disabled.
if ( aioseoMaybePluginIsDisabled( __FILE__ ) ) {
	return;
}

if ( ! defined( 'AIOSEO_PHP_VERSION_DIR' ) ) {
	define( 'AIOSEO_PHP_VERSION_DIR', basename( dirname( __FILE__ ) ) );
}

require_once dirname( __FILE__ ) . '/app/init/notices.php';
require_once dirname( __FILE__ ) . '/app/init/activation.php';

// We require PHP 7.0 or higher for the whole plugin to work.
if ( version_compare( PHP_VERSION, '7.0', '<' ) ) {
	add_action( 'admin_notices', 'aioseo_php_notice' );

	// Do not process the plugin code further.
	return;
}

// We require WP 4.9+ for the whole plugin to work.
global $wp_version;
if ( version_compare( $wp_version, '4.9', '<' ) ) {
	add_action( 'admin_notices', 'aioseo_wordpress_notice' );

	// Do not process the plugin code further.
	return;
}

if ( ! defined( 'AIOSEO_DIR' ) ) {
	define( 'AIOSEO_DIR', __DIR__ );
}
if ( ! defined( 'AIOSEO_FILE' ) ) {
	define( 'AIOSEO_FILE', __FILE__ );
}

// Don't allow multiple versions to be active.
if ( function_exists( 'aioseo' ) ) {
	add_action( 'activate_all-in-one-seo-pack/all_in_one_seo_pack.php', 'aioseo_lite_just_activated' );
	add_action( 'deactivate_all-in-one-seo-pack/all_in_one_seo_pack.php', 'aioseo_lite_just_deactivated' );
	add_action( 'activate_all-in-one-seo-pack-pro/all_in_one_seo_pack.php', 'aioseo_pro_just_activated' );
	add_action( 'admin_notices', 'aioseo_lite_notice' );

	// Do not process the plugin code further.
	return;
}

// We will be deprecating these versions of PHP in the future, so let's let the user know.
if ( version_compare( PHP_VERSION, '7.4', '<' ) ) {
	add_action( 'admin_notices', 'aioseo_php_notice_deprecated' );
}

// Define the class and the function.
// The AIOSEOAbstract class is required here because it can't be autoloaded.
require_once dirname( __FILE__ ) . '/app/AIOSEOAbstract.php';
require_once dirname( __FILE__ ) . '/app/AIOSEO.php';

aioseo();