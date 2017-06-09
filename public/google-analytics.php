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
	class aioseop_google_analytics extends All_in_One_SEO_Pack_Module {

		/**
		 * Flag that indicates if universal analytics should be used or not.
		 * @var bool
		 */
		private $aiosp_ga_use_universal_analytics = true;

		/**
		 * Default module constructor.
		 *
		 * @since 2.3.14 Code refactored and optimized.
		 */
		public function __construct() {
			// Filter universal analytics flag.
			$this->aiosp_ga_use_universal_analytics = apply_filters(
				'aiosp_universal_analytics',
				$this->aiosp_ga_use_universal_analytics
			);
			// Init google
			$this->google_analytics();
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
				if ( empty( $analytics ) ) {
					?>
					<script type="text/javascript">
						var _gaq = _gaq || [];
						<?php if ( ! empty( $aioseop_options['aiosp_ga_advanced_options'] ) && ! empty( $aioseop_options['aiosp_ga_link_attribution'] ) ) {
						?>        var pluginUrl =
							'//www.google-analytics.com/plugins/ga/inpage_linkid.js';
						_gaq.push(['_require', 'inpage_linkid', pluginUrl]);
						<?php
						}
						?>          _gaq.push(['_setAccount', '<?php
							echo $aioseop_options['aiosp_google_analytics_id'];
							?>']);
						<?php if ( ! empty( $aioseop_options['aiosp_ga_advanced_options'] ) && ! empty( $aioseop_options['aiosp_ga_anonymize_ip'] ) ) {
						?>          _gaq.push(['_gat._anonymizeIp']);
						<?php
						}
						?>
						<?php if ( ! empty( $aioseop_options['aiosp_ga_advanced_options'] ) && ! empty( $aioseop_options['aiosp_ga_multi_domain'] ) ) {
						?>          _gaq.push(['_setAllowLinker', true]);
						<?php
						}
						?>
						<?php if ( ! empty( $aioseop_options['aiosp_ga_advanced_options'] ) && ! empty( $aioseop_options['aiosp_ga_domain'] ) ) {
						$domain = $this->get_analytics_domain();
						?>          _gaq.push(['_setDomainName', '<?php echo $domain; ?>']);
						<?php
						}
						?>          _gaq.push(['_trackPageview']);
						(function () {
							var ga = document.createElement('script');
							ga.type = 'text/javascript';
							ga.async = true;
							<?php
							if ( ! empty( $aioseop_options['aiosp_ga_advanced_options'] ) && ! empty( $aioseop_options['aiosp_ga_display_advertising'] ) ) {
							?>            ga.src = ('https:' == document.location.protocol ? 'https://' : 'http://') + 'stats.g.doubleclick.net/dc.js';
							<?php
							} else {
							?>            ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
							<?php
							}
							?>            var s = document.getElementsByTagName('script')[0];
							s.parentNode.insertBefore(ga, s);
						})();
					</script>
					<?php
				}
				if ( ! empty( $aioseop_options['aiosp_ga_advanced_options'] )
					&& ($aioseop_options['aiosp_ga_track_outbound_links']
						|| $aioseop_options['aiosp_ga_track_outbound_forms']
						|| $aioseop_options['aiosp_ga_track_events']
						|| $aioseop_options['aiosp_ga_track_url_changes']
						|| $aioseop_options['aiosp_ga_track_visibility']
						|| $aioseop_options['aiosp_ga_track_media_query']
						|| $aioseop_options['aiosp_ga_track_impressions']
						|| $aioseop_options['aiosp_ga_track_scroller']
						|| $aioseop_options['aiosp_ga_track_social']
						|| $aioseop_options['aiosp_ga_track_clean_url']
					)
				) {
					$autotrack = apply_filters(
						'aiosp_google_autotrack',
						'https://cdnjs.cloudflare.com/ajax/libs/autotrack/2.4.0/autotrack.js'
					);
					?><script async src="<?php echo $autotrack ?>"></script><?php
				}
				$analytics = ob_get_clean();
			}
			echo apply_filters( 'aiosp_google_analytics', $analytics );
			do_action( 'after_aiosp_google_analytics' );

		}

		/**
		 * Adds analytics.
		 *
		 * @since 2.3.14 Refactored to work with autotrack.js and code optimized.
		 *
		 * @global array $aioseop_options All-in-on-seo saved settings/options.
		 */
		public function universal_analytics() {
			global $aioseop_options;
			if ( $this->aiosp_ga_use_universal_analytics ) {
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
				$extra_options = [];
				if ( ! empty( $domain_list ) ) {
					$extra_options[] = 'ga(\'require\', \'linker\');';
					$extra_options[] = 'ga(\'linker:autoLink\', ['. $domain_list . '] );';
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
					if ( ! empty( $aioseop_options['aiosp_ga_track_outbound_forms'] ) ) {
						$extra_options[] = 'ga(\'require\', \'outboundFormTracker\');';
					}
					if ( ! empty( $aioseop_options['aiosp_ga_track_events'] ) ) {
						$extra_options[] = 'ga(\'require\', \'eventTracker\');';
					}
					if ( ! empty( $aioseop_options['aiosp_ga_track_url_changes'] ) ) {
						$extra_options[] = 'ga(\'require\', \'urlChangeTracker\');';
					}
					if ( ! empty( $aioseop_options['aiosp_ga_track_visibility'] ) ) {
						$extra_options[] = 'ga(\'require\', \'pageVisibilityTracker\');';
					}
					if ( ! empty( $aioseop_options['aiosp_ga_track_media_query'] ) ) {
						$extra_options[] = 'ga(\'require\', \'mediaQueryTracker\');';
					}
					if ( ! empty( $aioseop_options['aiosp_ga_track_impressions'] ) ) {
						$extra_options[] = 'ga(\'require\', \'impressionTracker\');';
					}
					if ( ! empty( $aioseop_options['aiosp_ga_track_scroller'] ) ) {
						$extra_options[] = 'ga(\'require\', \'maxScrollTracker\');';
					}
					if ( ! empty( $aioseop_options['aiosp_ga_track_social'] ) ) {
						$extra_options[] = 'ga(\'require\', \'socialWidgetTracker\');';
					}
					if ( ! empty( $aioseop_options['aiosp_ga_track_clean_url'] ) ) {
						$extra_options[] = 'ga(\'require\', \'cleanUrlTracker\');';
					}
				}
				$js_options = array();
				foreach ( array( 'cookie_domain', 'allow_linker' ) as $opts ) {
					if ( ! empty( $$opts ) ) {
						$js_options[] = $$opts;
					}
				}
				if ( ! empty( $js_options ) ) {
					$js_options = implode( ',', $js_options );
					$js_options = ', { ' . $js_options . ' } ';
				} else {
					$js_options = '';
				}
				$analytics_id = esc_js( $aioseop_options['aiosp_google_analytics_id'] );
				ob_start()
				?>
				<script>
					window.ga=window.ga||function(){(ga.q=ga.q||[]).push(arguments)};ga.l=+new Date;
					ga('create', '<?php echo $analytics_id ?>'<?php echo $domain ?><?php echo $js_options ?>);
					// Plugins
					<?php foreach ( $extra_options as $option ) : ?><?php echo $option ?><?php endforeach ?>

					ga('send', 'pageview');
				</script>
				<script async src="https://www.google-analytics.com/analytics.js"></script>
				<?php
				return ob_get_clean();
			}
			return;
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

	}
}
