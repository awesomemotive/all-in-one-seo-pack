<?php

if ( ! class_exists( 'aioseop_google_analytics' ) ) {

	require_once( AIOSEOP_PLUGIN_DIR . 'admin/aioseop_module_class.php' ); // Include the module base class.
	/**
	 * Google Analytics module.
	 * TODO: Rather than extending the module base class, we should find a better way
	 * for the shared functions like moving them to our common functions class.
	 *
	 * @since 2.3.14 #921 Autotrack added and class refactored.
	 */
	// @codingStandardsIgnoreStart
	class aioseop_google_analytics extends All_in_One_SEO_Pack_Module {
	// @codingStandardsIgnoreEnd

		/**
		 * The GA file URL.
		 */
		const GA_URL = 'https://www.google-analytics.com/analytics.js';

		/**
		 * @todo Rather than extending the module base class,
		 * we should find a better way for the shared functions
		 * like moving them to our common functions class.
		 */

		/**
		 * Default module constructor.
		 */
		public function __construct() {
			$this->add_hooks();
			if ( ! is_admin() ) {
				$this->google_analytics();
			}
		}

		/**
		 * Inits Google Analytics.
		 *
		 * @since 2.3.14 Refactored to work with autotrack.js.
		 *
		 * @link https://github.com/googleanalytics/autotrack
		 *
		 * @global array  $aioseop_options All-in-on-seo saved settings/options.
		 * @global object $current_user    Current logged in WP user.
		 */
		public function google_analytics() {
			global $aioseop_options;
			// Exclude tracking for users?
			if ( ! empty( $aioseop_options['aiosp_ga_advanced_options'] )
				&& ! empty( $aioseop_options['aiosp_ga_exclude_users'] )
				&& is_user_logged_in()
			) {
				global $current_user;
				if ( empty( $current_user ) ) {
					wp_get_current_user();
				}
				if ( ! empty( $current_user ) ) {
					$intersect = array_intersect( $aioseop_options['aiosp_ga_exclude_users'], $current_user->roles );
					if ( ! empty( $intersect ) ) {
						return;
					}
				}
			}
			if ( ! empty( $aioseop_options['aiosp_google_analytics_id'] ) ) {
				ob_start();
				$analytics = $this->universal_analytics();
				echo $analytics;
				if ( apply_filters(
					'aioseop_ga_enable_autotrack',
					! empty( $aioseop_options['aiosp_ga_advanced_options'] ) && $aioseop_options['aiosp_ga_track_outbound_links'],
					$aioseop_options
				) ) {
					$autotrack = apply_filters(
						'aiosp_google_autotrack',
						AIOSEOP_PLUGIN_URL . 'public/js/vendor/autotrack.js'
					);
					?><script async src="<?php echo $autotrack; ?>"></script>
<?php
// Requested indent #921
				}
				$analytics = ob_get_clean();
			}
			echo apply_filters( 'aiosp_google_analytics', $analytics );
			do_action( 'after_aiosp_google_analytics' );

		}

		/**
		 * Handle Universal Analytics.
		 * Adds analytics.
		 *
		 * @since 2.3.15 Added aioseop_ga_attributes filter hook for attributes.
		 * @since 2.3.14 Refactored to work with autotrack.js and code optimized.
		 *
		 * @global array $aioseop_options All-in-on-seo saved settings/options.
		 */
		public function universal_analytics() {
			global $aioseop_options;
			$allow_linker = $cookie_domain = $domain = $addl_domains = $domain_list = '';
			if ( ! empty( $aioseop_options['aiosp_ga_advanced_options'] ) ) {
				$cookie_domain = $this->get_analytics_domain();
			}
			if ( ! empty( $cookie_domain ) ) {
				$cookie_domain = esc_js( $cookie_domain );
				$cookie_domain = '\'cookieDomain\': \'' . $cookie_domain . '\'';
			}
			if ( empty( $cookie_domain ) ) {
				$domain = ', \'auto\'';
			}
			if ( ! empty( $aioseop_options['aiosp_ga_advanced_options'] )
				&& ! empty( $aioseop_options['aiosp_ga_multi_domain'] )
			) {
				$allow_linker = '\'allowLinker\': true';
				if ( ! empty( $aioseop_options['aiosp_ga_addl_domains'] ) ) {
					$addl_domains = trim( $aioseop_options['aiosp_ga_addl_domains'] );
					$addl_domains = preg_split( '/[\s,]+/', $addl_domains );
					if ( ! empty( $addl_domains ) ) {
						foreach ( $addl_domains as $d ) {
							$d = $this->sanitize_domain( $d );
							if ( ! empty( $d ) ) {
								if ( ! empty( $domain_list ) ) {
									$domain_list .= ', ';
								}
								$domain_list .= '\'' . $d . '\'';
							}
						}
					}
				}
			}
			$extra_options = array();
			if ( ! empty( $domain_list ) ) {
				$extra_options[] = 'ga(\'require\', \'linker\');';
				$extra_options[] = 'ga(\'linker:autoLink\', [' . $domain_list . '] );';
			}
			if ( ! empty( $aioseop_options['aiosp_ga_advanced_options'] ) ) {
				if ( ! empty( $aioseop_options['aiosp_ga_display_advertising'] ) ) {
					$extra_options[] = 'ga(\'require\', \'displayfeatures\');';
				}
				if ( ! empty( $aioseop_options['aiosp_ga_enhanced_ecommerce'] ) ) {
					$extra_options[] = 'ga(\'require\', \'ec\');';
				}
				if ( ! empty( $aioseop_options['aiosp_ga_link_attribution'] ) ) {
					$extra_options[] = 'ga(\'require\', \'linkid\', \'linkid.js\');';
				}
				if ( ! empty( $aioseop_options['aiosp_ga_anonymize_ip'] ) ) {
					$extra_options[] = 'ga(\'set\', \'anonymizeIp\', true);';
				}
				if ( ! empty( $aioseop_options['aiosp_ga_track_outbound_links'] ) ) {
					$extra_options[] = 'ga(\'require\', \'outboundLinkTracker\');';
				}
			}
			$extra_options = apply_filters( 'aioseop_ga_extra_options', $extra_options, $aioseop_options );
			$js_options = array();
			foreach ( array( 'cookie_domain', 'allow_linker' ) as $opts ) {
				if ( ! empty( $$opts ) ) {
					$js_options[] = $$opts;
				}
			}
			$js_options = empty( $js_options )
				? ''
				: ', { ' . implode( ',', $js_options ) . ' } ';
			// Prepare analytics
			$analytics_id = esc_js( $aioseop_options['aiosp_google_analytics_id'] );
			ob_start()
			?>
			<script type="text/javascript" <?php echo preg_replace( '/\s+/', ' ', apply_filters( 'aioseop_ga_attributes', '' ) ); ?>>
				window.ga=window.ga||function(){(ga.q=ga.q||[]).push(arguments)};ga.l=+new Date;
				ga('create', '<?php echo $analytics_id; ?>'<?php echo $domain; ?><?php echo $js_options; ?>);
				// Plugins
				<?php
				foreach ( $extra_options as $option ) :
?>
<?php echo $option; ?><?php endforeach ?>

				ga('send', 'pageview');
			</script>
			<script async src="<?php echo esc_url( $this->get_ga_url() ); ?>"></script>
			<?php
			return ob_get_clean();
		}

		/**
		 * @return mixed|string
		 */
		function get_analytics_domain() {
			global $aioseop_options;
			if ( ! empty( $aioseop_options['aiosp_ga_domain'] ) ) {
				return $this->sanitize_domain( $aioseop_options['aiosp_ga_domain'] );
			}
			return '';
		}

		/**
		 * Add the hooks.
		 */
		private function add_hooks() {
			if ( is_admin() ) {
				register_activation_hook( AIOSEOP_PLUGIN_FILE, array( $this, 'init_download' ) );
				register_deactivation_hook( AIOSEOP_PLUGIN_FILE, array( $this, 'deactivate' ) );
				add_action( 'aioseop_ga_local', array( $this, 'start_download' ) );
				// to handle the scenario where the GA code is added later.
				add_action( 'admin_init', array( $this, 'init_download' ) );
			}
		}

		/**
		 * Initiate the download.
		 */
		public function init_download() {
			if ( false === apply_filters( 'aioseop_ga_host_locally', true ) ) {
				return;
			}

			if ( ! aioseop_option_isset( 'aiosp_google_analytics_id' ) ) {
				return;
			}

			if ( ! wp_next_scheduled( 'aioseop_ga_local' ) ) {
				wp_schedule_event( time(), 'twicedaily', 'aioseop_ga_local' );
			}

			if ( false === $this->get_download_status() ) {
				$this->start_download();
			}
		}

		/**
		 * On plugin deactivation.
		 */
		public function deactivate() {
			if ( wp_next_scheduled( 'aioseop_ga_local' ) ) {
				wp_clear_scheduled_hook( 'aioseop_ga_local' );
			}
			delete_transient( 'aioseop_ga_local_status' );
		}

		/**
		 * Set the download status in a transient.
		 *
		 * @param string $status The status to set.
		 */
		private function set_download_status( $status ) {
			set_transient( 'aioseop_ga_local_status', $status, DAY_IN_SECONDS );
		}

		/**
		 * Get the download status from the transient.
		 */
		private function get_download_status() {
			return get_transient( 'aioseop_ga_local_status' );
		}

		/**
		 * Actually download the file.
		 */
		private function start_download() {
			$content = wp_remote_retrieve_body( wp_remote_get( self::GA_URL ) );
			if ( empty( $content ) ) {
				self::log( 'Unable to download ' . self::GA_URL );
				$this->set_download_status( 'no' );
				return;
			}

			WP_Filesystem();
			global $wp_filesystem;
			$dir = wp_upload_dir();

			if ( ! $wp_filesystem->is_dir( $dir['basedir'] ) || ! $wp_filesystem->is_writable( $dir['basedir'] ) ) {
				self::log( 'Unable to write to ' . $dir['basedir'] );
				$this->set_download_status( 'no' );
				return;
			}

			$aioseop_dir = $dir['basedir'] . '/' . sanitize_title( AIOSEOP_PLUGIN_NAME );

			$wp_filesystem->mkdir( $aioseop_dir );

			if ( ! $wp_filesystem->is_dir( $aioseop_dir ) || ! $wp_filesystem->is_writable( $aioseop_dir ) ) {
				self::log( 'Unable to write to ' . $aioseop_dir );
				$this->set_download_status( 'no' );
				return;
			}

			$wp_filesystem->put_contents( $aioseop_dir . '/ga.js', $content, 0644 );

			// let's verify if what we got is what we put.
			$verify = $wp_filesystem->get_contents( $aioseop_dir . '/ga.js' );
			if ( $verify !== $content ) {
				self::log( 'Unable to verify if ' . self::GA_URL . ' has been written correctly to ' . $aioseop_dir . '/ga.js' );
				$this->set_download_status( 'no' );
				return;
			}
			$this->set_download_status( 'yes' );
			do_action( 'aiosp_ga_download', $aioseop_dir . '/ga.js' );
		}

		/**
		 * Return the file URL to use.
		 */
		private function get_ga_url() {
			if ( false === apply_filters( 'aioseop_ga_host_locally', true ) || 'yes' !== $this->get_download_status() ) {
				return 'https://www.google-analytics.com/analytics.js';
			}

			$dir = wp_upload_dir();
			$url = $dir['baseurl'] . '/' . sanitize_title( AIOSEOP_PLUGIN_NAME ) . '/ga.js';
			return apply_filters( 'aioseop_ga_local_url', $url );
		}

		/**
		 * Log the message.
		 *
		 * @param string $msg The message to log.
		 */
		private static function log( $msg ) {
			error_log( $msg );
		}

	}
}
