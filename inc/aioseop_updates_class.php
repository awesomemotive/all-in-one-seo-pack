<?php

/**
 * Handles detection of new plugin version updates.
 *
 * Handles detection of new plugin version updates, migration of old settings,
 * new WP core feature support, etc.
 * AIOSEOP Updates class.
 *
 * @package All-in-One-SEO-Pack.
 */
class AIOSEOP_Updates {

	/**
	 * Update notices.
	 *
	 * @since 2.4
	 *
	 * @var array
	 */
	protected $notices;

	/**
	 * Constructor
	 *
	 * @since 2.4 Init notices property.
	 */
	function __construct() {
		$this->notices = array();
	}

	/**
	 * Displays update notices.
	 *
	 * @since 2.4
	 */
	public function show_notices() {
		ob_start();
		?>
		<!-- All In One SEO notices - BEGIN -->
		<?php foreach ( $this->notices as $notice ) : ?>
			<div class="notice
				<?php if ( isset( $notice['success'] ) && $notice['success'] === false ) : ?>notice-error<?php else : ?>notice-success<?php endif ?>
				is-dismissible"
			>
		        <p><?php echo isset( $notice['message'] ) ? $notice['message'] : $notice ?></p>
		    </div>
		<?php endforeach ?>
		<!-- All In One SEO notices - END -->
		<?php
		echo ob_get_clean();
	}

	/**
	 * Adds an update notice.
	 *
	 * @since 2.4
	 *
	 * @param string $message Notice message.
	 * @param bool   $success Flag that indicates if notice is error or success.
	 */
	private function add_notice($message, $success = true)
	{
		$this->notices[] = array(
			'message'	=> $message,
			'success'	=> $success
		);
	}

	/**
	 * Updates version.
	 *
	 * @global $aiosp , $aioseop_options.
	 * @return null
	 */
	function version_updates() {
		global $aiosp, $aioseop_options;
		if ( empty( $aioseop_options ) ) {
			$aioseop_options = get_option( $aioseop_options );
			if ( empty( $aioseop_options ) ) {
				// Something's wrong. bail.
				return;
			}
		}

		// Last known running plugin version.
		$last_active_version = '0.0';
		if ( isset( $aioseop_options['last_active_version'] ) ) {
			$last_active_version = $aioseop_options['last_active_version'];
		}

		// Compares version to see which one is the newer.
		if ( version_compare( $last_active_version, AIOSEOP_VERSION, '<' ) ) {

			// Upgrades based on previous version.
			do_action( 'before_doing_aioseop_updates' );
			$this->do_version_updates( $last_active_version );
			do_action( 'after_doing_aioseop_updates' );
			// If we're running Pro, let the Pro updater set the version.
			if ( ! AIOSEOPPRO ) {

				// Save the current plugin version as the new last_active_version.
				$aioseop_options['last_active_version'] = AIOSEOP_VERSION;
				$aiosp->update_class_option( $aioseop_options );
			}

			if( ! is_network_admin() || !isset( $_GET['activate-multi'] ) ) {
				// Replace this to reactivate update welcome screen.
				//set_transient( '_aioseop_activation_redirect', true, 30 ); // Sets 30 second transient for welcome screen redirect on activation.
			}
			delete_transient( 'aioseop_feed' );
			add_action( 'admin_init', array( $this, 'aioseop_welcome' ) );

		}

		/**
		 * Perform updates that are dependent on external factors, not
		 * just the plugin version.
		 */
		$this->do_feature_updates();
		// Call notices
		add_action( 'admin_notices', array(  &$this, 'show_notices' ) );
	}

	function aioseop_welcome(){
		if ( get_transient( '_aioseop_activation_redirect' ) ) {
			delete_transient( '_aioseop_activation_redirect' );
			//$aioseop_welcome = new aioseop_welcome();
			//$aioseop_welcome->init( TRUE );
		}

	}

	/**
	 * Updates version.
	 *
	 * @since 2.4 Version update.
	 *
	 * @todo the compare here should be extracted into a function
	 *
	 * @global       $aioseop_options .
	 *
	 * @param String $old_version
	 */
	function do_version_updates( $old_version ) {
		global $aioseop_options;
		if (
			( ! AIOSEOPPRO && version_compare( $old_version, '2.3.3', '<' ) ) ||
			( AIOSEOPPRO && version_compare( $old_version, '2.4.3', '<' ) )
		) {
			$this->bad_bots_201603();
		}

		if (
			( ! AIOSEOPPRO && version_compare( $old_version, '2.3.4.1', '<' ) ) ||
			( AIOSEOPPRO && version_compare( $old_version, '2.4.4.1', '<' ) )
		) {
			$this->bad_bots_remove_yandex_201604();
		}

		if (
			( ! AIOSEOPPRO && version_compare( $old_version, '2.3.9', '<' ) ) ||
			( AIOSEOPPRO && version_compare( $old_version, '2.4.9', '<' ) )
		) {
			$this->bad_bots_remove_seznambot_201608();
			set_transient( '_aioseop_activation_redirect', true, 30 ); // Sets 30 second transient for welcome screen redirect on activation.
		}

		if (
			( ! AIOSEOPPRO && version_compare( $old_version, '2.4', '<' ) ) ||
			( AIOSEOPPRO && version_compare( $old_version, '2.4.17', '<' ) )
		) {
			$this->db_migrate_og_type_201708();
		}
	}

	/**
	 * Removes overzealous 'DOC' entry which is causing false-positive bad
	 * bot blocking.
	 *
	 * @since 2.3.3
	 * @global $aiosp , $aioseop_options.
	 */
	function bad_bots_201603() {
		global $aiosp, $aioseop_options;

		// Remove 'DOC' from bad bots list to avoid false positives.
		if ( isset( $aioseop_options['modules']['aiosp_bad_robots_options']['aiosp_bad_robots_blocklist'] ) ) {
			$list                                                                                 = $aioseop_options['modules']['aiosp_bad_robots_options']['aiosp_bad_robots_blocklist'];
			$list                                                                                 = str_replace( array(
				"DOC\r\n",
				"DOC\n",
			), '', $list );
			$aioseop_options['modules']['aiosp_bad_robots_options']['aiosp_bad_robots_blocklist'] = $list;
			update_option( 'aioseop_options', $aioseop_options );
			$aiosp->update_class_option( $aioseop_options );
		}
	}

	/*
	 * Functions for specific version milestones.
	 */

	/**
	 * Remove 'yandex' entry. This is a major Russian search engine, and no longer needs to be blocked.
	 *
	 * @since 2.3.4.1
	 * @global $aiosp , $aioseop_options.
	 */
	function bad_bots_remove_yandex_201604() {
		global $aiosp, $aioseop_options;

		// Remove 'yandex' from bad bots list to avoid false positives.
		if ( isset( $aioseop_options['modules']['aiosp_bad_robots_options']['aiosp_bad_robots_blocklist'] ) ) {
			$list                                                                                 = $aioseop_options['modules']['aiosp_bad_robots_options']['aiosp_bad_robots_blocklist'];
			$list                                                                                 = str_replace( array(
				"yandex\r\n",
				"yandex\n",
			), '', $list );
			$aioseop_options['modules']['aiosp_bad_robots_options']['aiosp_bad_robots_blocklist'] = $list;
			update_option( 'aioseop_options', $aioseop_options );
			$aiosp->update_class_option( $aioseop_options );
		}
	}

	/**
	 * Remove 'SeznamBot' entry.
	 *
	 * @since 2.3.8
	 * @global $aiosp , $aioseop_options.
	 */
	function bad_bots_remove_seznambot_201608() {
		global $aiosp, $aioseop_options;

		// Remove 'SeznamBot' from bad bots list to avoid false positives.
		if ( isset( $aioseop_options['modules']['aiosp_bad_robots_options']['aiosp_bad_robots_blocklist'] ) ) {
			$list                                                                                 = $aioseop_options['modules']['aiosp_bad_robots_options']['aiosp_bad_robots_blocklist'];
			$list                                                                                 = str_replace( array(
				"SeznamBot\r\n",
				"SeznamBot\n",
			), '', $list );
			$aioseop_options['modules']['aiosp_bad_robots_options']['aiosp_bad_robots_blocklist'] = $list;
			update_option( 'aioseop_options', $aioseop_options );
			$aiosp->update_class_option( $aioseop_options );
		}
	}

	/**
	 * Updates features.
	 *
	 * @return null
	 *
	 * if ( ! ( isset( $aioseop_options['version_feature_flags']['FEATURE_NAME'] ) &&
	 * $aioseop_options['version_feature_flags']['FEATURE_NAME'] === 'yes' ) ) {
	 * $this->some_feature_update_method(); // sets flag to 'yes' on completion.
	 */
	public function do_feature_updates() {
		global $aioseop_options;

		// We don't need to check all the time. Use a transient to limit frequency.
		if ( get_site_transient( 'aioseop_update_check_time' ) ) {
			return;
		}

		// If we're running Pro, let the Pro updater set the transient.
		if ( ! AIOSEOPPRO ) {

			// We haven't checked recently. Reset the timestamp, timeout 6 hours.
			set_site_transient(
				'aioseop_update_check_time',
				time(),
				apply_filters( 'aioseop_update_check_time', 3600 * 6 )
			);
		}
	}

	/**
	 * Updates posts' og:type "blog" into "article".
	 * Issue #1013: "Blog" not available anymore.
	 *
	 * @since 2.4
	 *
	 * @global object $wpdb Wordpress Database accesor.
	 */
	public function db_migrate_og_type_201708() {
		global $wpdb;
		$res = $wpdb->query( $wpdb->prepare(
			'UPDATE ' . $wpdb->postmeta . '
			SET meta_value = replace(meta_value,%s,%s)
			WHERE meta_key = %s AND meta_value like %s;',
			's:35:"aioseop_opengraph_settings_category";s:4:"blog";',
			's:35:"aioseop_opengraph_settings_category";s:7:"article";',
			'_aioseop_opengraph_settings',
			'%s:35:"aioseop_opengraph_settings_category";s:4:"blog";%'
		) );
		if ( $res === false ) {
			$this->add_notice( '<strong>All In One SEO Plugin:</strong> failed to update <i>deprecated</i> <code>og:type</code>.', false );
		} else {
			$this->add_notice( '<strong>All In One SEO Plugin:</strong> updated <i>deprecated</i> <code>og:type</code> successfully.' );
		}
	}
}
