<?php

if( class_exists( 'WPSEO_Import_Hooks' )){
class WPSEO_Import_AIOSEO_Hooks extends WPSEO_Import_Hooks {

	protected $plugin_file = 'all-in-one-seo-pack/all_in_one_seo_pack.php';

	protected $deactivation_listener = 'deactivate_aioseo';

	public function show_import_settings_notice() {
	//	$url = admin_url( 'tools.php?page=seodt' ) );
	//make these save to the database and dismissible 

	
		$yoasturl = add_query_arg( array( '_wpnonce' => wp_create_nonce( 'wpseo-import' ) ), admin_url( 'admin.php?page=wpseo_tools&tool=import-export&import=1&importaioseo=1#top#import-seo' ) );
		$aiourl = add_query_arg( array( '_wpnonce' => wp_create_nonce( 'aiosp-import' ) ), admin_url( 'tools.php?page=aiosp_import' ) );


		$aioseop_yst_detected_notice_dismissed = get_user_meta( get_current_user_id(), 'aioseop_yst_detected_notice_dismissed', true );

	  	if ( empty( $aioseop_yst_detected_notice_dismissed ) ) {	

			echo '<div class="notice notice-warning row-title is-dismissible yst_notice"><p>', sprintf( esc_html__( 'The plugin Yoast SEO has been detected. Do you want to %simport its settings%s into All in One SEO Pack?', 'all-in-one-seo-pack' ), sprintf( '<a href="%s">', esc_url( $aiourl ) ), '</a>' ), '</p></div>';

		}
		

		echo '<div class="error"><p>', sprintf( esc_html__( 'The plugin All-In-One-SEO has been detected. Do you want to %simport its settings%s?', 'wordpress-seo' ), sprintf( '<a href="%s">', esc_url( $yoasturl ) ), '</a>' ), '</p></div>';
		
		
		
	}

	public function show_deactivate_notice() {
		echo '<div class="updated"><p>', esc_html__( 'All in One SEO has been deactivated', 'all-in-one-seo-pack' ), '</p></div>';
	}
}
}else{
	delete_user_meta( get_current_user_id(), 'aioseop_yst_detected_notice_dismissed' );
}

/**
 * Register the admin menu page
 */
add_action('admin_menu', 'aiosp_seometa_settings_init');
function aiosp_seometa_settings_init() {
	global $_aiosp_seometa_admin_pagehook;
	
	// Add submenu page link
	$_aiosp_seometa_admin_pagehook = add_submenu_page('tools.php', __('Import SEO Data','all-in-one-seo-pack'), __('SEO Data Import','all-in-one-seo-pack'), 'manage_options', 'aiosp_import', 'aiosp_seometa_admin');
}

/**
 * This function intercepts POST data from the form submission, and uses that
 * data to convert values in the postmeta table from one platform to another.
 */
function aiosp_seometa_action() {
	
	//print_r($_REQUEST);
	
	if ( empty( $_REQUEST['_wpnonce'] ) )
		return;
	
	if ( empty( $_REQUEST['platform_old'] ) ) {
		printf( '<div class="error"><p>%s</p></div>', __('Sorry, you can\'t do that. Please choose a platform and then click Analyze or Convert.') );
		return;
	}
		
	if ( $_REQUEST['platform_old'] == 'All in One SEO Pack' ) {
		printf( '<div class="error"><p>%s</p></div>', __('Sorry, you can\'t do that. Please choose a platform and then click Analyze or Convert.') );
		return;
	}
		
	check_admin_referer('aiosp_nonce'); // Verify nonce
	
	if ( !empty( $_REQUEST['analyze'] ) ) {
		
		printf( '<h3>%s</h3>', __('Analysis Results', 'all-in-one-seo-pack') );
		
		$response = aiosp_seometa_post_meta_analyze( $_REQUEST['platform_old'], 'All in One SEO Pack' );
		if ( is_wp_error( $response ) ) {
			printf( '<div class="error"><p>%s</p></div>', __('Sorry, something went wrong. Please try again') );
			return;
		}
		
		printf( __('<p>Analyzing records in a %s to %s conversion&hellip;', 'all-in-one-seo-pack'), esc_html( $_POST['platform_old'] ), 'All in One SEO Pack' );
		printf( '<p><b>%d</b> Compatible Records were identified</p>', $response->update );
//		printf( '<p>%d Compatible Records will be ignored</p>', $response->ignore );
		
		printf( '<p><b>%s</b></p>', __('Compatible data:', 'all-in-one-seo-pack') );
		echo '<ol>';
		foreach ( (array)$response->elements as $element ) {
			printf( '<li>%s</li>', $element );
		}
		echo '</ol>';
		
		return;
	}
	
	printf( '<h3>%s</h3>', __('Conversion Results', 'all-in-one-seo-pack') );
	
	$result = aiosp_seometa_post_meta_convert( stripslashes($_REQUEST['platform_old']), 'All in One SEO Pack' );
	if ( is_wp_error( $result ) ) {
		printf( '<p>%s</p>', __('Sorry, something went wrong. Please try again') );
		return;
	}
	
	printf( '<p><b>%d</b> Records were updated</p>', isset( $result->updated ) ? $result->updated : 0 );
	printf( '<p><b>%d</b> Records were ignored</p>', isset( $result->ignored ) ? $result->ignored : 0 );
	
	return;
	
}

/**
 * This function displays feedback to the user about compatible conversion
 * elements and the conversion process via the admin_alert hook.
 */

/**
 * The admin page output
 */
function aiosp_seometa_admin() {
	global $_aiosp_seometa_themes, $_aiosp_seometa_plugins, $_aiosp_seometa_platforms;
?>

	<div class="wrap">
		
	<?php screen_icon('tools'); ?>
	<h2><?php _e('Import SEO Settings', 'all-in-one-seo-pack'); ?></h2>
	
	<p><span class="description"><?php printf( __('Use the drop down below to choose which plugin or theme you wish to import SEO data from.', 'all-in-one-seo-pack') ); ?></span></p>
	
	<p><span class="description"><?php printf( __('Click "Analyze" for a list of SEO data that can be imported into All in One SEO Pack, along with the number of records that will be imported.', 'all-in-one-seo-pack') ); ?></span></p>
	
	<p><span class="description"><strong><?php printf( __('Please Note: ') ); ?></strong><?php printf( __('Some plugins and themes do not share similar data, or they store data in a non-standard way. If we cannot import this data, it will remain unchanged in your database. Any compatible SEO data will be displayed for you to review. If a post or page already has SEO data in All in One SEO Pack, we will not import data from another plugin/theme.', 'all-in-one-seo-pack') ); ?></span></p>
	
	<p><span class="description"><?php printf( __('Click "Convert" to perform the import. After the import has completed, you will be alerted to how many records were imported, and how many records had to be ignored, based on the criteria above.', 'all-in-one-seo-pack') ); ?></span></p>
	
	<p><span class="row-title"><?php printf( esc_html__('Before performing an import, we strongly recommend that you make a backup of your site. We use and recommend %s BackupBuddy %s for backups.', 'all-in-one-seo-pack'), sprintf( '<a target="_blank" href="%s">', esc_url( 'http://semperfiwebdesign.com/backupbuddy/' ) ), '</a>' ); ?></span></p>


		
	<form action="<?php echo admin_url('tools.php?page=aiosp_import'); ?>" method="post">
	<?php
		wp_nonce_field('aiosp_nonce');
	
		$platform_old = (!isset($_POST['platform_old'])) ? '' : $_POST['platform_old'];
	
		_e('Import SEO data from:', 'all-in-one-seo-pack');
		echo '<select name="platform_old">';
		printf( '<option value="">%s</option>', __('Choose platform:', 'all-in-one-seo-pack') );
		
		printf( '<optgroup label="%s">', __('Plugins', 'all-in-one-seo-pack') );
		foreach ( $_aiosp_seometa_plugins as $platform => $data ) {
			if($platform != "All in One SEO Pack") printf( '<option value="%s" %s>%s</option>', $platform, selected($platform, $platform_old, 0), $platform );
		}
		printf( '</optgroup>' );
		
		printf( '<optgroup label="%s">', __('Themes', 'all-in-one-seo-pack') );
		foreach ( $_aiosp_seometa_themes as $platform => $data ) {
			printf( '<option value="%s" %s>%s</option>', $platform, selected($platform, $platform_old, 0), $platform );
		}
		printf( '</optgroup>' );
		

		
		echo '</select>' . "\n\n";

	?>
	
	<input type="submit" class="button-highlighted" name="analyze" value="<?php _e('Analyze', 'genesis'); ?>" />
	<input type="submit" class="button-primary" value="<?php _e('Convert', 'genesis') ?>" />
	
	</form>
	
	<?php aiosp_seometa_action(); ?>
	
	</div>

<?php	
}



//////////////////FUNCTIONS//////////////////

	/**
	 * This function converts $old meta_key entries in the postmeta table into $new entries.
	 *
	 * It first checks to see what records for the $new meta_key already exist,
	 * storing the corresponding post_id values in an array. When the conversion
	 * happens, rows that contain a post_id in that array will be ignored, to
	 * avoid duplicate $new meta_key entries.
	 *
	 * The $old entries will be left as-is if $delete_old is left false. If set
	 * to true, the $old entries will be deleted, rather than retained.
	 *
	 * The function returns an object for error detection, and the number of affected rows.
	 */
	function aiosp_seometa_meta_key_convert( $old = '', $new = '', $delete_old = false ) {

		do_action( 'pre_aiosp_seometa_meta_key_convert_before', $old, $new, $delete_old );

		global $wpdb;

		$output = new stdClass;

		if ( !$old || !$new ) {
			$output->WP_Error = 1;
			return $output;
		}

		// 	See which records we need to ignore, if any
		$exclude = $wpdb->get_results( $wpdb->prepare( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key = %s", $new ) );

		//	If no records to ignore, we'll do a basic UPDATE and DELETE
		if ( !$exclude ) {

			$output->updated = $wpdb->update( $wpdb->postmeta, array( 'meta_key' => $new ), array( 'meta_key' => $old ) );
			$output->deleted = $delete_old ? $wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->postmeta WHERE meta_key = %s", $old ) ) : 0;
			$output->ignored = 0;

		} 
		//	Else, do a more complex UPDATE and DELETE
		else {

			foreach ( (array)$exclude as $key => $value ) {
				$not_in[] = $value->post_id;
			}
			$not_in = implode(', ', (array)$not_in );

			$output->updated = $wpdb->query( $wpdb->prepare( "UPDATE $wpdb->postmeta SET meta_key = %s WHERE meta_key = %s AND post_id NOT IN ($not_in)", $new, $old ) );
			$output->deleted = $delete_old ? $wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->postmeta WHERE meta_key = %s", $old ) ) : 0;
			$output->ignored = count( $exclude );

		}

		do_action( 'aiosp_seometa_meta_key_convert', $output, $old, $new, $delete_old );

		return $output;

	}

	/**
	 * This function cycles through all compatible SEO entries of two platforms,
	 * performs a aiosp_seometa_meta_key_convert() conversion for each key, and returns
	 * the results as an object.
	 * 
	 * It first checks for compatible entries between the two platforms. When it
	 * finds compatible entries, it loops through them and preforms the conversion
	 * on each entry.
	 */
	function aiosp_seometa_post_meta_convert( $old_platform = '', $new_platform = 'All in One SEO Pack', $delete_old = false ) {

		do_action( 'pre_aiosp_seometa_post_meta_convert', $old_platform, $new_platform, $delete_old );

		global $_aiosp_seometa_platforms;

		$output = new stdClass;

		if ( empty( $_aiosp_seometa_platforms[$old_platform] ) || empty( $_aiosp_seometa_platforms[$new_platform] ) ) {
			$output->WP_Error = 1;
			return $output;
		}

		$output->updated = 0;
		$output->deleted = 0;
		$output->ignored = 0;

		foreach ( (array)$_aiosp_seometa_platforms[$old_platform] as $label => $meta_key ) {

			// skip iterations where no $new analog exists
			if ( empty( $_aiosp_seometa_platforms[$new_platform][$label] ) )
				continue;

			// set $old and $new meta_key values
			$old = $_aiosp_seometa_platforms[$old_platform][$label];
			$new = $_aiosp_seometa_platforms[$new_platform][$label];

			// convert
			$result = aiosp_seometa_meta_key_convert( $old, $new, $delete_old );

			// error check
			if ( is_wp_error( $result ) )
				continue;

			// update total updated/ignored count
			$output->updated = $output->updated + (int)$result->updated;
			$output->ignored = $output->ignored + (int)$result->ignored;

		}

		do_action( 'aiosp_seometa_post_meta_convert', $output, $old_platform, $new_platform, $delete_old );

		return $output;

	}

	/**
	 * This function analyzes two platforms to see what Compatible elements they share,
	 * what data can be converted from one to the other, and which elements to ignore (future).
	 */
	function aiosp_seometa_post_meta_analyze( $old_platform = '', $new_platform = 'All in One SEO Pack' ) {

		do_action( 'pre_aiosp_seometa_post_meta_analyze', $old_platform, $new_platform );

		global $wpdb, $_aiosp_seometa_platforms;

		$output = new stdClass;

		if ( empty( $_aiosp_seometa_platforms[$old_platform] ) || empty( $_aiosp_seometa_platforms[$new_platform] ) ) {
			$output->WP_Error = 1;
			return $output;
		}

		$output->update = 0;
		$output->ignore = 0;
		$output->elements = '';

		foreach ( (array)$_aiosp_seometa_platforms[$old_platform] as $label => $meta_key ) {

			// skip iterations where no $new analog exists
			if ( empty( $_aiosp_seometa_platforms[$new_platform][$label] ) )
				continue;

			$elements[] = $label;

			// see which records to ignore, if any
			$ignore = 0;
	//		$ignore = $wpdb->get_results( $wpdb->prepare( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key = %s", $meta_key ) );

			// see which records to update, if any
			$update = $wpdb->get_results( $wpdb->prepare( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key = %s", $meta_key ) );

			// count items in returned arrays
	//		$ignore = count( (array)$ignore );
			$update = count( (array)$update );

			// calculate update/ignore by comparison
	//		$update = ( (int)$update > (int)$ignore ) ? ( (int)$update - (int)$ignore ) : 0;

			// update output numbers
			$output->update = $output->update + (int)$update;
			$output->ignore = $output->ignore + (int)$ignore;

		} // endforeach

		$output->elements = $elements;

		do_action( 'aiosp_seometa_post_meta_analyze', $output, $old_platform, $new_platform );

		return $output;

	}
	
	
	
////////////PLUGIN/////////


//	define('aiosp_seometa_PLUGIN_DIR', dirname(__FILE__));

	//add_action( 'plugins_loaded', 'aiosp_seometa_import' );
	/**
	 * Initialize the SEO Data Transporter plugin
	 */
	function aiosp_seometa_import() {

		global $_aiosp_seometa_themes, $_aiosp_seometa_plugins, $_aiosp_seometa_platforms;

		/**
		 * The associative array of supported themes.
		 */
		$_aiosp_seometa_themes = array(
			// alphabatized
			'Builder' => array(
				'Custom Doctitle' => '_builder_seo_title',
				'META Description' => '_builder_seo_description',
				'META Keywords' => '_builder_seo_keywords',
			),
			'Catalyst' => array(
				'Custom Doctitle' => '_catalyst_title',
				'META Description' => '_catalyst_description',
				'META Keywords' => '_catalyst_keywords',
				'noindex' => '_catalyst_noindex',
				'nofollow' => '_catalyst_nofollow',
				'noarchive' => '_catalyst_noarchive',
			),
			'Frugal' => array(
				'Custom Doctitle' => '_title',
				'META Description' => '_description',
				'META Keywords' => '_keywords',
				'noindex' => '_noindex',
				'nofollow' => '_nofollow',
			),
			'Genesis' => array(
				'Custom Doctitle' => '_genesis_title',
				'META Description' => '_genesis_description',
				'META Keywords' => '_genesis_keywords',
				'noindex' => '_genesis_noindex',
				'nofollow' => '_genesis_nofollow',
				'noarchive' => '_genesis_noarchive',
				'Canonical URI' => '_genesis_canonical_uri',
				'Custom Scripts' => '_genesis_scripts',
				'Redirect URI' => 'redirect',
			),
			'Headway' => array(
				'Custom Doctitle' => '_title',
				'META Description' => '_description',
				'META Keywords' => '_keywords',
			),
			'Hybrid' => array(
				'Custom Doctitle' => 'Title',
				'META Description' => 'Description',
				'META Keywords' => 'Keywords',
			),
			'Thesis 1.x' => array(
				'Custom Doctitle' => 'thesis_title',
				'META Description' => 'thesis_description',
				'META Keywords' => 'thesis_keywords',
				'Custom Scripts' => 'thesis_javascript_scripts',
				'Redirect URI' => 'thesis_redirect',
			),
			/*
			'Thesis 2.x' => array(
				'Custom Doctitle' => '_thesis_title_tag',
				'META Description' => '_thesis_meta_description',
				'META Keywords' => '_thesis_meta_keywords',
				'Custom Scripts' => '_thesis_javascript_scripts',
				'Canonical URI' => '_thesis_canonical_link',
				'Redirect URI' => '_thesis_redirect',
			),
			*/
			'WooFramework' => array(
				'Custom Doctitle' => 'seo_title',
				'META Description' => 'seo_description',
				'META Keywords' => 'seo_keywords',
			)
		);

		/**
		 * The associative array of supported plugins.
		 */
		$_aiosp_seometa_plugins = array(
			// alphabatized
			'Add Meta Tags' => array(
				'META Description' => 'description',
				'META Keywords' => 'keywords',
			),
			'All in One SEO Pack' => array(
				'Custom Doctitle' => '_aioseop_title',
				'META Description' => '_aioseop_description',
				'META Keywords' => '_aioseop_keywords',
			),
			'Greg\'s High Performance SEO' => array(
				'Custom Doctitle' => '_ghpseo_secondary_title',
				'META Description' => '_ghpseo_alternative_description',
				'META Keywords' => '_ghpseo_keywords',
			),
			'Headspace2' => array(
				'Custom Doctitle' => '_headspace_page_title',
				'META Description' => '_headspace_description',
				'META Keywords' => '_headspace_keywords',
				'Custom Scripts' => '_headspace_scripts',
			),
			'Infinite SEO' => array(
				'Custom Doctitle' => '_wds_title',
				'META Description' => '_wds_metadesc',
				'META Keywords' => '_wds_keywords',
				'noindex' => '_wds_meta-robots-noindex',
				'nofollow' => '_wds_meta-robots-nofollow',
				'Canonical URI' => '_wds_canonical',
				'Redirect URI' => '_wds_redirect',
			),
			'Meta SEO Pack' => array(
				'META Description' => '_msp_description',
				'META Keywords' => '_msp_keywords',
			),
			'Platinum SEO' => array(
				'Custom Doctitle' => 'title',
				'META Description' => 'description',
				'META Keywords' => 'keywords',
			),
			'SEO Title Tag' => array(
				'Custom Doctitle' => 'title_tag',
				'META Description' => 'meta_description',
			),
			'SEO Ultimate' => array(
				'Custom Doctitle' => '_su_title',
				'META Description' => '_su_description',
				'META Keywords' => '_su_keywords',
				'noindex' => '_su_meta_robots_noindex',
				'nofollow' => '_su_meta_robots_nofollow',
			),
			'Yoast SEO' => array(
				'Custom Doctitle' => '_yoast_wpseo_title',
				'META Description' => '_yoast_wpseo_metadesc',
				'META Keywords' => '_yoast_wpseo_metakeywords',
				'noindex' => '_yoast_wpseo_meta-robots-noindex',
				'nofollow' => '_yoast_wpseo_meta-robots-nofollow',
				'Canonical URI' => '_yoast_wpseo_canonical',
				'Redirect URI' => '_yoast_wpseo_redirect',
			)
		);

		/**
		 * The combined array of supported platforms.
		 */
		$_aiosp_seometa_platforms = array_merge( $_aiosp_seometa_themes, $_aiosp_seometa_plugins );

		/**
		 * Include the other elements of the plugin.
		 */
	//	require_once( aiosp_seometa_PLUGIN_DIR . '/admin.php' );
//		require_once( aiosp_seometa_PLUGIN_DIR . '/functions.php' );

		/**
		 * Init hook.
		 *
		 * Hook fires after plugin functions are loaded.
		 *
		 * @since 0.9.10
		 *
		 */
		do_action( 'aiosp_seometa_import' );

	}

	/**
	 * Activation Hook
	 * @since 0.9.4
	 */
	register_activation_hook( __FILE__, 'aiosp_seometa_activation_hook' );
	function aiosp_seometa_activation_hook() {

	//	require_once( aiosp_seometa_PLUGIN_DIR . '/functions.php' );

		aiosp_seometa_meta_key_convert( '_yoast_seo_title', 'yoast_wpseo_title', true );
		aiosp_seometa_meta_key_convert( '_yoast_seo_metadesc', 'yoast_wpseo_metadesc', true );	

	}

	/**
	 * Manual conversion test
	 */
	/*
	$aiosp_seometa_convert = aiosp_seometa_post_meta_convert( 'All in One SEO Pack', 'Genesis', false );
	printf( '%d records were updated', $aiosp_seometa_convert->updated );
	/**/

	/**
	 * Manual analysis test
	 */
	/*
	$aiosp_seometa_analyze = aiosp_seometa_post_meta_analyze( 'All in One SEO Pack', 'Genesis' );
	printf( '<p><b>%d</b> Compatible Records were identified</p>', $aiosp_seometa_analyze->update );
	/**/

	/**
	 * Delete all SEO data, from every platform
	 */
	/*
	foreach ( $_aiosp_seometa_platforms as $platform => $data ) {

		foreach ( $data as $field ) {
			$deleted = $wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->postmeta WHERE meta_key = %s", $field ) );
			printf( '%d %s records deleted<br />', $deleted, $field );
		}

	}
	/**/

	/**
	 * Query all SEO data to find the number of records to change
	 */


