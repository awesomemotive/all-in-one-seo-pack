<?php
/**
 * Google Analytice
 *
 * @package All_in_One_SEO_Pack
 * @since ?
 */

if ( ! class_exists( 'aioseop_google_analytics' ) ) {

	require_once( AIOSEOP_PLUGIN_DIR . 'admin/aioseop_module_class.php' ); // Include the module base class.

	if ( AIOSEOPPRO ) {
		require_once( AIOSEOP_PLUGIN_DIR . 'pro/aioseop_google_tag_manager.php' );
	}

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
		 * TODO Rather than extending the module base class,
		 * we should find a better way for the shared functions
		 * like moving them to our common functions class.
		 */

		/**
		 * Constructor
		 *
		 * Default module constructor.
		 *
		 * @since 2.3.9.2
		 */
		public function __construct() {
			$this->google_analytics();
		}

		/**
		 * Google Analytics
		 *
		 * @since 2.3.9.2
		 * @since 2.3.14 Refactored to work with autotrack.js.
		 * @since 3.3.0 Don't run if Google Analytics ID is blank.
		 *
		 * @link https://github.com/googleanalytics/autotrack
		 *
		 * @global array  $aioseop_options All-in-on-seo saved settings/options.
		 * @global object $current_user    Current logged in WP user.
		 */
		public function google_analytics() {
			global $aioseop_options;

			if ( empty( $aioseop_options['aiosp_google_analytics_id'] ) ) {
				return;
			}

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
				// Requested indent #921.
			}
			$analytics = ob_get_clean();

			echo apply_filters( 'aiosp_google_analytics', $analytics );
			do_action( 'after_aiosp_google_analytics' );
		}

		/**
		 * Universal Analytics
		 *
		 * Adds analytics.
		 *
		 * @since 2.3.9.2
		 * @since 2.3.15 Added aioseop_ga_attributes filter hook for attributes.
		 * @since 2.3.14 Refactored to work with autotrack.js and code optimized.
		 *
		 * @global array $aioseop_options All-in-on-seo saved settings/options.
		 *
		 * @return false|string
		 */
		public function universal_analytics() {
			global $aioseop_options;
			$allow_linker  = '';
			$cookie_domain = '';
			$domain        = '';
			$addl_domains  = '';
			$domain_list   = '';
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

			/**
			 * Internal filter. Don't output certain GA features if Google Tag Manager is active.
			 *
			 * @since 3.3.0
			 */
			if ( apply_filters( 'aioseop_pro_gtm_enabled', __return_false() ) ) {
				$options_to_remove = array(
					"ga('require', 'ec');",
					"ga('require', 'outboundLinkTracker');",
					"ga('require', 'outboundFormTracker');",
					"ga('require', 'eventTracker');",
					"ga('require', 'urlChangeTracker');",
					"ga('require', 'pageVisibilityTracker');",
					"ga('require', 'mediaQueryTracker');",
					"ga('require', 'impressionTracker');",
					"ga('require', 'maxScrollTracker');",
					"ga('require', 'socialWidgetTracker');",
					"ga('require', 'cleanUrlTracker');",
				);
				foreach ( $options_to_remove as $option ) {
					$index = array_search( $option, $extra_options, true );
					if ( $index ) {
						unset( $extra_options[ $index ] );
					}
					continue;
				}
			}

			$js_options = array();
			foreach ( array( 'cookie_domain', 'allow_linker' ) as $opts ) {
				if ( ! empty( $$opts ) ) {
					$js_options[] = $$opts;
				}
			}
			$js_options = empty( $js_options )
				? ''
				: ', { ' . implode( ',', $js_options ) . ' } ';
			// Prepare analytics.
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
			<script async src="https://www.google-analytics.com/analytics.js"></script>
			<?php
			return ob_get_clean();
		}

		/**
		 * Get Analytics Domain
		 *
		 * @since 2.3.9.2
		 *
		 * @return mixed|string
		 */
		function get_analytics_domain() {
			global $aioseop_options;
			if ( ! empty( $aioseop_options['aiosp_ga_domain'] ) ) {
				return $this->sanitize_domain( $aioseop_options['aiosp_ga_domain'] );
			}
			return '';
		}

	}
}
