<?php
/*
Plugin Name: All In One SEO Pack
Plugin URI: http://semperfiwebdesign.com
Description: Out-of-the-box SEO for your WordPress blog. Features like XML Sitemaps, SEO for custom post types, SEO for blogs or business sites, SEO for ecommerce sites, and much more. Almost 30 million downloads since 2007.
Version: 2.3.4.2
Author: Michael Torbert
Author URI: http://michaeltorbert.com
Text Domain: all-in-one-seo-pack
Domain Path: /i18n/
*/

/*
Copyright (C) 2007-2016 Michael Torbert, semperfiwebdesign.com (michael AT semperfiwebdesign DOT com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

/**
 * @package All-in-One-SEO-Pack
 * @version 2.3.4.2
 */

if(!defined('AIOSEOPPRO')) define('AIOSEOPPRO', false);
if ( ! defined( 'AIOSEOP_VERSION' ) ) define( 'AIOSEOP_VERSION', '2.3.4.2' );
global $aioseop_plugin_name;
$aioseop_plugin_name = 'All in One SEO Pack';

/*******
*
* All in One SEO Pack
*
*******/

if ( ! defined( 'ABSPATH' ) ) return;


if( AIOSEOPPRO ){
	
	add_action( 'admin_init', 'disable_all_in_one_free', 1 );
	
}

if ( ! defined( 'AIOSEOP_PLUGIN_NAME' ) ) define( 'AIOSEOP_PLUGIN_NAME', $aioseop_plugin_name );


//register_activation_hook(__FILE__,'aioseop_activate_pl');

if ( ! defined( 'AIOSEOP_PLUGIN_DIR' ) ) {
    define( 'AIOSEOP_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
} elseif ( AIOSEOP_PLUGIN_DIR != plugin_dir_path( __FILE__ ) ) {

//this is not a great message
/*	
	add_action( 'admin_notices', create_function( '', 'echo "' . "<div class='error'>" . sprintf(
				__( "%s detected a conflict; please deactivate the plugin located in %s.", 'all-in-one-seo-pack' ),
				$aioseop_plugin_name, AIOSEOP_PLUGIN_DIR ) . "</div>" . '";' ) );
*/

	return;


}

if ( ! defined( 'AIOSEOP_PLUGIN_BASENAME' ) )
    define( 'AIOSEOP_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
if ( ! defined( 'AIOSEOP_PLUGIN_DIRNAME' ) )
    define( 'AIOSEOP_PLUGIN_DIRNAME', dirname( AIOSEOP_PLUGIN_BASENAME ) );
if ( ! defined( 'AIOSEOP_PLUGIN_URL' ) )
    define( 'AIOSEOP_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
if ( ! defined( 'AIOSEOP_PLUGIN_IMAGES_URL' ) )
    define( 'AIOSEOP_PLUGIN_IMAGES_URL', AIOSEOP_PLUGIN_URL . 'images/' );
if ( ! defined( 'AIOSEOP_BASELINE_MEM_LIMIT' ) )
	define( 'AIOSEOP_BASELINE_MEM_LIMIT', 268435456 ); // 256MB
if ( ! defined( 'WP_CONTENT_URL' ) )
    define( 'WP_CONTENT_URL', site_url() . '/wp-content' );
if ( ! defined( 'WP_ADMIN_URL' ) )
    define( 'WP_ADMIN_URL', site_url() . '/wp-admin' );
if ( ! defined( 'WP_CONTENT_DIR' ) )
    define( 'WP_CONTENT_DIR', ABSPATH . 'wp-content' );
if ( ! defined( 'WP_PLUGIN_URL' ) )
    define( 'WP_PLUGIN_URL', WP_CONTENT_URL. '/plugins' );
if ( ! defined( 'WP_PLUGIN_DIR' ) )
    define( 'WP_PLUGIN_DIR', WP_CONTENT_DIR . '/plugins' );

global $aiosp, $aioseop_options, $aioseop_modules, $aioseop_module_list, $aiosp_activation, $aioseop_mem_limit, $aioseop_get_pages_start, $aioseop_admin_menu;
$aioseop_get_pages_start = $aioseop_admin_menu = 0;

if ( AIOSEOPPRO ) {
	global $aioseop_update_checker;
}

$aioseop_options = get_option( 'aioseop_options' );

$aioseop_mem_limit = @ini_get( 'memory_limit' );

if ( !function_exists( 'aioseop_convert_bytestring' ) ) {
	function aioseop_convert_bytestring( $byteString ) {
		$num = 0;
		preg_match( '/^\s*([0-9.]+)\s*([KMGTPE])B?\s*$/i', $byteString, $matches );
		if ( !empty( $matches ) ) {
			$num = ( float )$matches[1];
			switch ( strtoupper( $matches[2] ) ) {
				case 'E': $num = $num * 1024;
				case 'P': $num = $num * 1024;
				case 'T': $num = $num * 1024;
				case 'G': $num = $num * 1024;
				case 'M': $num = $num * 1024;
				case 'K': $num = $num * 1024;
			}
		}
		return intval( $num );
	}
}

if ( is_array( $aioseop_options ) && isset( $aioseop_options['modules'] ) && isset( $aioseop_options['modules']['aiosp_performance_options'] ) ) {
	$perf_opts = $aioseop_options['modules']['aiosp_performance_options'];
	if ( isset( $perf_opts['aiosp_performance_memory_limit'] ) )
		$aioseop_mem_limit = $perf_opts['aiosp_performance_memory_limit'];
	if ( isset( $perf_opts['aiosp_performance_execution_time'] ) && ( $perf_opts['aiosp_performance_execution_time'] !== '' ) ) {
		@ini_set( 'max_execution_time', (int)$perf_opts['aiosp_performance_execution_time'] );
		@set_time_limit( (int)$perf_opts['aiosp_performance_execution_time'] );
	}
} else {
	$aioseop_mem_limit = aioseop_convert_bytestring( $aioseop_mem_limit );
	if ( ( $aioseop_mem_limit > 0 ) && ( $aioseop_mem_limit < AIOSEOP_BASELINE_MEM_LIMIT ) )
		$aioseop_mem_limit = AIOSEOP_BASELINE_MEM_LIMIT;
}

if ( !empty( $aioseop_mem_limit ) ) {
	if ( !is_int( $aioseop_mem_limit ) )
		$aioseop_mem_limit = aioseop_convert_bytestring( $aioseop_mem_limit );
	if ( ( $aioseop_mem_limit > 0 ) && ( $aioseop_mem_limit <= AIOSEOP_BASELINE_MEM_LIMIT ) )
		@ini_set( 'memory_limit', $aioseop_mem_limit );
}

$aiosp_activation = false;
$aioseop_module_list = Array( 'sitemap', 'opengraph', 'robots', 'file_editor', 'importer_exporter', 'bad_robots', 'performance' ); // list all available modules here

if (AIOSEOPPRO){
	$aioseop_module_list[] = 'video_sitemap';
}

if ( class_exists( 'All_in_One_SEO_Pack' ) ) {
	add_action( 'admin_notices', create_function( '', 'echo "<div class=\'error\'>The All In One SEO Pack class is already defined";'
	. "if ( class_exists( 'ReflectionClass' ) ) { \$r = new ReflectionClass( 'All_in_One_SEO_Pack' ); echo ' in ' . \$r->getFileName(); } "
	. ' echo ", preventing All In One SEO Pack from loading.</div>";' ) );
	return;	
}

if ( AIOSEOPPRO ){
	
	require( AIOSEOP_PLUGIN_DIR . 'pro/sfwd_update_checker.php');
	$aioseop_update_checker = new SFWD_Update_Checker(
	        'http://semperplugins.com/upgrade_plugins.php',
	        __FILE__,
	        'aioseop'
	);
	
	
$aioseop_update_checker->plugin_name = AIOSEOP_PLUGIN_NAME;
$aioseop_update_checker->plugin_basename = AIOSEOP_PLUGIN_BASENAME;
if ( !empty( $aioseop_options['aiosp_license_key'] ) )
	$aioseop_update_checker->license_key = $aioseop_options['aiosp_license_key'];
else
	$aioseop_update_checker->license_key = '';
$aioseop_update_checker->options_page = 'all-in-one-seo-pack-pro/aioseop_class.php';
$aioseop_update_checker->renewal_page = 'http://semperplugins.com/all-in-one-seo-pack-pro-support-updates-renewal/';

$aioseop_update_checker->addQueryArgFilter( Array( $aioseop_update_checker, 'add_secret_key' ) );
}


/**
 * Check if we just got activated.
 */
if ( !function_exists( 'aioseop_activate' ) ) {
	function aioseop_activate() {
		
	  global $aiosp_activation;
	  if ( AIOSEOPPRO ){
		global $aioseop_update_checker;
	}
	  $aiosp_activation = true;
	  delete_transient( "aioseop_oauth_current" );

		delete_user_meta( get_current_user_id(), 'aioseop_yst_detected_notice_dismissed' );

	  if ( AIOSEOPPRO ){
	  $aioseop_update_checker->checkForUpdates();
		}
	}
}

add_action( 'plugins_loaded', 'aioseop_init_class' );



if(!function_exists('aiosp_plugin_row_meta')){

add_filter( 'plugin_row_meta',     'aiosp_plugin_row_meta', 10, 2 );

function aiosp_plugin_row_meta( $actions, $plugin_file ) {

if(!AIOSEOPPRO){	

 $action_links = array(
   'donatelink' => array(
      'label' => __('Donate', 'all-in-one-seo-pack'),
      'url'   => 'https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=mrtorbert%40gmail%2ecom&item_name=All%20In%20One%20SEO%20Pack&item_number=Support%20Open%20Source&no_shipping=0&no_note=1&tax=0&currency_code=USD&lc=US&bn=PP%2dDonationsBF&charset=UTF%2d8'
    )
,
	'amazon' => array(
	      'label' => __('Amazon Wishlist', 'all-in-one-seo-pack'),
	      'url'   => 'https://www.amazon.com/wishlist/1NFQ133FNCOOA/ref=wl_web'
	    )


);

}else{
	$action_links = '';
}

  return aiosp_action_links( $actions, $plugin_file, $action_links, 'after');
}
}






if(!function_exists('aiosp_add_action_links'))  {


add_filter( 'plugin_action_links_' . plugin_basename(__FILE__) , 'aiosp_add_action_links', 10, 2 );



function aiosp_add_action_links( $actions, $plugin_file ) {
 
 $aioseop_plugin_dirname = AIOSEOP_PLUGIN_DIRNAME;
 $action_links = Array();
 $action_links = array(
 	'settings' => array(
	      'label' => __('SEO Settings', 'all-in-one-seo-pack'),
	      'url'   => get_admin_url(null, "admin.php?page=$aioseop_plugin_dirname/aioseop_class.php")
		),
		
		'forum' => array(
		      'label' => __('Support Forum', 'all-in-one-seo-pack'),
		      'url'   => 'http://semperplugins.com/support/'
			),

		'docs' => array(
		      'label' => __('Documentation', 'all-in-one-seo-pack'),
		      'url'   => 'http://semperplugins.com/documentation/'
			)
	
   	);

unset( $actions['edit'] );

if(!AIOSEOPPRO){
$action_links['proupgrade'] = 	
		  array(
		      'label' => __('Upgrade to Pro', 'all-in-one-seo-pack'),
		      'url' => 'http://semperplugins.com/plugins/all-in-one-seo-pack-pro-version/?loc=plugins'

);
}

  return aiosp_action_links( $actions, $plugin_file, $action_links, 'before');
}
}

 if(!function_exists('aiosp_action_links'))  {

function  aiosp_action_links ( $actions, $plugin_file,  $action_links = array(), $position = 'after' ) { 
  static $plugin;
  if( !isset($plugin) ) {
      $plugin = plugin_basename( __FILE__ );
  }
  if( $plugin == $plugin_file && !empty( $action_links ) ) {
     foreach( $action_links as $key => $value ) {
        $link = array( $key => '<a href="' . $value['url'] . '">' . $value['label'] . '</a>' );
         if( $position == 'after' ) {
            $actions = array_merge( $actions, $link );    
         } else {
            $actions = array_merge( $link, $actions );
         }
      }//foreach
  }// if
  return $actions;
}
}
if ( !function_exists( 'aioseop_init_class' ) ) {
	function aioseop_init_class() {
		global $aiosp;
		load_plugin_textdomain( 'all-in-one-seo-pack', false, dirname( plugin_basename( __FILE__ ) ) . '/i18n/' );
		require_once( AIOSEOP_PLUGIN_DIR . 'inc/aioseop_functions.php' );
		require_once( AIOSEOP_PLUGIN_DIR . 'aioseop_class.php' );
		require_once( AIOSEOP_PLUGIN_DIR . 'inc/aioseop_updates_class.php');
		require_once( AIOSEOP_PLUGIN_DIR . 'inc/commonstrings.php');
		require_once( AIOSEOP_PLUGIN_DIR . 'admin/display/postedit.php');
		require_once( AIOSEOP_PLUGIN_DIR . 'admin/display/general-metaboxes.php');
		require_once( AIOSEOP_PLUGIN_DIR . 'inc/aiosp_common.php');
		require_once( AIOSEOP_PLUGIN_DIR . 'admin/meta_import.php');
		
		if( AIOSEOPPRO ){
			require_once( AIOSEOP_PLUGIN_DIR . 'pro/functions_general.php' );
			require_once( AIOSEOP_PLUGIN_DIR . 'pro/functions_class.php');
			require_once( AIOSEOP_PLUGIN_DIR . 'pro/aioseop_pro_updates_class.php');
		}
		aiosp_seometa_import(); // call importer functions... this should be moved somewhere better
		
		$aiosp = new All_in_One_SEO_Pack();
		
		$aioseop_updates = new AIOSEOP_Updates();

		if( AIOSEOPPRO ){
			$aioseop_pro_updates = new AIOSEOP_Pro_Updates();
			add_action( 'admin_init', array( $aioseop_pro_updates, 'version_updates' ), 12 );
		}

		if ( aioseop_option_isset( 'aiosp_unprotect_meta' ) )
			add_filter( 'is_protected_meta', 'aioseop_unprotect_meta', 10, 3 );
		



		add_action( 'init', array( $aiosp, 'add_hooks' ) );
		add_action( 'admin_init', array( $aioseop_updates, 'version_updates' ), 11 );
		
		if ( defined( 'DOING_AJAX' ) && !empty( $_POST ) && !empty( $_POST['action'] ) && ( $_POST['action'] === 'aioseop_ajax_scan_header' ) ) {
			remove_action( 'init', array( $aiosp, 'add_hooks' ) );
			add_action('admin_init', 'aioseop_scan_post_header' );
			add_action('shutdown', 'aioseop_ajax_scan_header' ); // if the action doesn't run -- pdb
			include_once(ABSPATH . 'wp-admin/includes/screen.php');
			global $current_screen;
			if ( class_exists( 'WP_Screen' ) )
				$current_screen = WP_Screen::get( 'front' );
		}
	}
}

add_action( 'init', 'aioseop_load_modules', 1 );
//add_action( 'after_setup_theme', 'aioseop_load_modules' );

if ( is_admin() ) {
	add_action( 'wp_ajax_aioseop_ajax_save_meta',	'aioseop_ajax_save_meta' );
	add_action( 'wp_ajax_aioseop_ajax_save_url',	'aioseop_ajax_save_url' );
	add_action( 'wp_ajax_aioseop_ajax_delete_url',	'aioseop_ajax_delete_url' );
	add_action( 'wp_ajax_aioseop_ajax_scan_header',	'aioseop_ajax_scan_header' );
	if(AIOSEOPPRO){
		add_action( 'wp_ajax_aioseop_ajax_facebook_debug', 'aioseop_ajax_facebook_debug' );
	}
	add_action( 'wp_ajax_aioseop_ajax_save_settings', 'aioseop_ajax_save_settings');
	add_action( 'wp_ajax_aioseop_ajax_get_menu_links', 'aioseop_ajax_get_menu_links');
	add_action( 'wp_ajax_aioseo_dismiss_yst_notice' , 'aioseop_update_yst_detected_notice');
	add_action( 'wp_ajax_aioseo_dismiss_visibility_notice' , 'aioseop_update_user_visibilitynotice');
	add_action( 'wp_ajax_aioseo_dismiss_woo_upgrade_notice' , 'aioseop_woo_upgrade_notice_dismissed'); 
	if(AIOSEOPPRO){
		add_action( 'wp_ajax_aioseop_ajax_update_oembed',	'aioseop_ajax_update_oembed' );
	}
}

if ( !function_exists( 'aioseop_scan_post_header' ) ) {
	function aioseop_scan_post_header() {
		require_once( ABSPATH . WPINC . '/default-filters.php' );
		global $wp_query;
		$wp_query->query_vars['paged'] = 0;
		query_posts('post_type=post&posts_per_page=1');
		if (have_posts()) the_post();
	}
}

require_once( AIOSEOP_PLUGIN_DIR . 'aioseop_init.php' );


if(!function_exists('aioseop_install')){
register_activation_hook( __FILE__, 'aioseop_install' );

function aioseop_install(){
	aioseop_activate();
}
}

if(!function_exists('disable_all_in_one_free')){
function disable_all_in_one_free(){
	if ( AIOSEOPPRO && is_plugin_active( 'all-in-one-seo-pack/all_in_one_seo_pack.php' )){
		deactivate_plugins( 'all-in-one-seo-pack/all_in_one_seo_pack.php' );
	}
}}
