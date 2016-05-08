<?php

/**
 * Handles detection of new plugin version updates.
 *
 * Handles detection of new plugin version updates, migration of old settings,
 * new WP core feature support, etc.
 * AIOSEOP Updates class.
 * @author Michael Torbert.
 * @author Semper Fi Web Design.
 * @copyright http://semperplugins.com
 * @package All-in-One-SEO-Pack
 * @version 1.0.0
 */
class aioseop_updates {

	/**
	 * Constructor
	 * @since 1.0.0
	 */
	function __construct() {
	}

	/**
	 * Updates version.
	 * @since 1.0.0
	 * @global $aiosp, $aioseop_options.
	 * @return null.
	 */
	function version_updates() {
		global $aiosp, $aioseop_options;
		if ( empty( $aioseop_options ) ) {
			$aioseop_options = get_option( $aioseop_options );
			if ( empty( $aioseop_options ) ) {
				// something's wrong. bail.
				return;
			}
		}

		// Last known running plugin version
		$last_active_version = isset( $aioseop_options['last_active_version'] )
			? $aioseop_options['last_active_version'] : '0.0';

		// Compares version to see which one is the newer.
		if ( version_compare( $last_active_version, AIOSEOP_VERSION, '<' ) ) {

			// Upgrades based on previous version
			$this->do_version_updates( $last_active_version );

			// If we're running Pro, let the Pro updater set the version.
			if ( ! AIOSEOPPRO ) {

				// Save the current plugin version as the new last_active_version
				$aioseop_options['last_active_version'] = AIOSEOP_VERSION;
				$aiosp->update_class_option( $aioseop_options );
			}
		}

		/**
		 * Perform updates that are dependent on external factors, not
		 * just the plugin version.
		 */
		$this->do_feature_updates();
	}

	//TODO: the compare here should be extracted into a function

	/**
	 * Updates version.
	 * @since 1.0.0
	 * @global $aioseop_options.
	 * @param String $old_version.
	 */
	function do_version_updates( $old_version ) {
		global $aioseop_options;
		if (
			( ! AIOSEOPPRO && version_compare( $old_version, '2.3.3', '<' ) ) ||
			( AIOSEOPPRO && version_compare( $old_version, '2.4.3', '<' ) )
		  	) {
	   		$this->bad_bots_201603();
		}

		/*
		if (
			( !AIOSEOPPRO && version_compare( $old_version, '2.4', '<' ) ) ||
			( AIOSEOPPRO && version_compare( $old_version, '2.5', '<' ) )
		   ) {
			// Do changes needed for 2.4/2.5... etc
		}
		*/
	}

	/**
	 * Updates features.
	 * @since 1.0.0
	 * @return null.
	 */
	function do_feature_updates() {
		global $aioseop_options;

		// We don't need to check all the time. Use a transient to limit frequency.
		if ( get_site_transient( 'aioseop_update_check_time' ) )
			return;

		// If we're running Pro, let the Pro updater set the transient.
		if ( ! AIOSEOPPRO ) {

			// We haven't checked recently. Reset the timestamp, timeout 6 hours.
			set_site_transient(
				'aioseop_update_check_time',
				time(),
				apply_filters( 'aioseop_update_check_time', 3600 * 6 )
			);
		}

		/*
		if ( ! ( isset( $aioseop_options['version_feature_flags']['FEATURE_NAME'] ) &&
		 $aioseop_options['version_feature_flags']['FEATURE_NAME'] === 'yes' ) ) {
	   		$this->some_feature_update_method(); // sets flag to 'yes' on completion.
		}
		*/
	}

	// Functions for specific version milestones

	/**
	 * Removes overzealous 'DOC' entry which is causing false-positive bad
	 * bot blocking.
	 * @since 1.0.0
	 * @global $aiosp, $aioseop_options.
	 */
	function bad_bots_201603() {
		global $aiosp, $aioseop_options;

		// Remove 'DOC' from bad bots list to avoid false positives
		if ( isset( $aioseop_options['modules']['aiosp_bad_robots_options']['aiosp_bad_robots_blocklist'] ) ) {
			$list = $aioseop_options['modules']['aiosp_bad_robots_options']['aiosp_bad_robots_blocklist'];
			$list = str_replace( array( "DOC\r\n", "DOC\n" ), '', $list );
			$aioseop_options['modules']['aiosp_bad_robots_options']['aiosp_bad_robots_blocklist'] = $list;
			update_option( 'aioseop_options', $aioseop_options );
			$aiosp->update_class_option( $aioseop_options );
			if ( isset( $aioseop_options['modules']['aiosp_bad_robots_options']['aiosp_bad_robots_htaccess_rules'] )
				&& 'on' === $aioseop_options['modules']['aiosp_bad_robots_options']['aiosp_bad_robots_htaccess_rules'] ){
				if ( ! class_exists( 'All_in_One_SEO_Pack_Bad_Robots' ) ) {
					require_once(
						AIOSEOP_PLUGIN_DIR .
						'admin/aioseop_module_class.php'
					);
					require_once(
						AIOSEOP_PLUGIN_DIR .
						'modules/aioseop_bad_robots.php'
					);
				}
				$aiosp_reset_htaccess = new All_in_One_SEO_Pack_Bad_Robots;
				$aiosp_reset_htaccess->generate_htaccess_blocklist();
			}
			if ( ! isset( $aioseop_options['modules']['aiosp_bad_robots_options']['aiosp_bad_robots_htaccess_rules'] )
				&& extract_from_markers(
					get_home_path() . '.htaccess',
					'Bad Bot Blocker' ) ) {
				insert_with_markers(
					get_home_path() . '.htaccess',
					'Bad Bot Blocker',
					''
				);
			}
		}
	}
}
