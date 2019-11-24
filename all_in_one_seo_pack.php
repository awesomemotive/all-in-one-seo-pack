<?php
/*
Plugin Name: All In One SEO Pack
Plugin URI: https://semperplugins.com/all-in-one-seo-pack-pro-version/
Description: Out-of-the-box SEO for WordPress. Features like XML Sitemaps, SEO for custom post types, SEO for blogs or business sites, SEO for ecommerce sites, and much more. More than 50 million downloads since 2007.
Version: 3.3
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
 * @version 3.3
 */

if ( ! defined( 'AIOSEOPPRO' ) ) {
	define( 'AIOSEOPPRO', false );
}

/*
 * DO NOT EDIT BELOW THIS LINE.
 */

if ( ! defined( 'ABSPATH' ) ) {
	return;
}

if ( ! defined( 'AIOSEOP_PLUGIN_BASENAME' ) ) {
	$search           = wp_normalize_path( dirname( __DIR__, 1 ) ) . '/';
	$aioseop_basename = str_replace( $search, '', plugin_basename( __FILE__ ) );
	/**
	 * Plugin Basename.
	 *
	 * @since ?
	 * @since 3.4 Fix plugin basename on Travis CI & PHPUnit testing when plugin directory is outside of wp-plugins.
	 *
	 * @var string $AIOSEOP_PLUGIN_BASENAME Plugin basename on WP platform. Eg. 'all-in-one-seo-pack/all_in_one_seo_pack.php`.
	 */
	define( 'AIOSEOP_PLUGIN_BASENAME', $aioseop_basename );
}

require_once plugin_dir_path( __FILE__ ) . 'class-aioseop-core.php';
global $aioseop_core;
if ( is_null( $aioseop_core ) ) {
	$aioseop_core = new AIOSEOP_Core();
}
