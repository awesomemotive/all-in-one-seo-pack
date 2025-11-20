<?php
/**
 * Plugin Name: SEO AI Pro - Independent Edition
 * Plugin URI:  https://github.com/bestroofingnow/all-in-one-seo-pack
 * Description: Independent SEO plugin with AI-Powered Content Generation, SEO Analysis, Keyword Research & AEO Optimization. Supports OpenAI, Claude, and Google Gemini. Features XML Sitemaps, SEO for custom post types, Elementor integration, and much more. No license required - fully independent.
 * Author:      Your Company
 * Author URI:  https://yourwebsite.com/
 * Version:     1.0.0
 * Text Domain: all-in-one-seo-pack
 * Domain Path: /languages
 * License:     GPL-3.0+
 *
 * This plugin is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * any later version.
 *
 * This plugin is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this plugin. If not, see <https://www.gnu.org/licenses/>.
 *
 * This is an independent fork based on the GPL-licensed All in One SEO Pack.
 * Original source: https://wordpress.org/plugins/all-in-one-seo-pack/
 *
 * @since     1.0.0
 * @author    Your Company
 * @package   AIOSEO\Plugin
 * @license   GPL-3.0+
 * @copyright Copyright © 2025, Your Company
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

// We require WordPress 5.3+ for the whole plugin to work.
// Support for 5.3 is scheduled to be dropped in April 2025. 5.4, 5.5 and 5.6 will be dropped at the end of 2025.
global $wp_version; // phpcs:ignore Squiz.NamingConventions.ValidVariableName
if ( version_compare( $wp_version, '5.3', '<' ) ) { // phpcs:ignore Squiz.NamingConventions.ValidVariableName
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

// We will be deprecating these versions of PHP in the future, so we'll let the user know.
if ( version_compare( PHP_VERSION, '7.4', '<' ) ) {
	add_action( 'admin_notices', 'aioseo_php_notice_deprecated' );
}

// Define the class and the function.
// The AIOSEOAbstract class is required here because it can't be autoloaded.
require_once dirname( __FILE__ ) . '/app/AIOSEOAbstract.php';
require_once dirname( __FILE__ ) . '/app/AIOSEO.php';

aioseo();