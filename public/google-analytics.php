<?php

if ( ! class_exists( 'aioseop_google_analytics' ) ) {

	require_once( AIOSEOP_PLUGIN_DIR . 'admin/aioseop_module_class.php' ); // Include the module base class.

	class aioseop_google_analytics extends All_in_One_SEO_Pack_Module {
		// TODO Rather than extending the module base class, we should find a better way for the shared functions like moving them to our common functions class.

		function __construct() {
			$this->google_analytics();
		}

		function google_analytics() {
			global $aioseop_options;
			$analytics = '';
			if ( ! empty( $aioseop_options['aiosp_ga_advanced_options'] ) && ! empty( $aioseop_options['aiosp_ga_exclude_users'] ) && is_user_logged_in() ) {
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

				if ( ! empty( $aioseop_options['aiosp_ga_advanced_options'] ) && $aioseop_options['aiosp_ga_track_outbound_links'] ) { ?>
                    <script type="text/javascript">
                        function recordOutboundLink(link, category, action) {
                            ga('send', 'event', category, action);
                            if (link.target == '_blank') return true;
                            setTimeout('document.location = "' + link.href + '"', 100);
                            return false;
                        }

                        /* use regular Javascript for this */
                        function getAttr(ele, attr) {
                            var result = (ele.getAttribute && ele.getAttribute(attr)) || null;
                            if (!result) {
                                var attrs = ele.attributes;
                                var length = attrs.length;
                                for (var i = 0; i < length; i++)
                                    if (attr[i].nodeName === attr) result = attr[i].nodeValue;
                            }
                            return result;
                        }

                        function aiosp_addLoadEvent(func) {
                            var oldonload = window.onload;
                            if (typeof window.onload != 'function') {
                                window.onload = func;
                            } else {
                                window.onload = function () {
                                    if (oldonload) {
                                        oldonload();
                                    }
                                    func();
                                }
                            }
                        }

                        function aiosp_addEvent(element, evnt, funct) {
                            if (element.attachEvent)
                                return element.attachEvent('on' + evnt, funct);
                            else
                                return element.addEventListener(evnt, funct, false);
                        }

                        aiosp_addLoadEvent(function () {
                            var links = document.getElementsByTagName('a');
                            for (var x = 0; x < links.length; x++) {
                                if (typeof links[x] == 'undefined') continue;
                                aiosp_addEvent(links[x], 'onclick', function () {
                                    var mydomain = new RegExp(document.domain, 'i');
                                    href = getAttr(this, 'href');
                                    if (href && href.toLowerCase().indexOf('http') === 0 && !mydomain.test(href)) {
                                        recordOutboundLink(this, 'Outbound Links', href);
                                    }
                                });
                            }
                        });
                    </script>
					<?php
				}
				$analytics = ob_get_clean();
			}
			echo apply_filters( 'aiosp_google_analytics', $analytics );
			do_action( 'after_aiosp_google_analytics' );

		}

		/**
         * Handle Universal Analytics.
		 *
		 * @since ?
		 * @since 2.3.15 Added aioseop_ga_attributes filter hook for attributes.
		 *
		 * @return string
		 */
		function universal_analytics() {
			global $aioseop_options;
			$analytics    = '';
			$allow_linker = $cookie_domain = $domain = $addl_domains = $domain_list = '';
			if ( ! empty( $aioseop_options['aiosp_ga_advanced_options'] ) ) {
				$cookie_domain = $this->get_analytics_domain();
			}
			if ( ! empty( $cookie_domain ) ) {
				$cookie_domain = esc_js( $cookie_domain );
				$cookie_domain = "'cookieDomain': '{$cookie_domain}'";
			}
			if ( empty( $cookie_domain ) ) {
				$domain = ", 'auto'";
			}
			if ( ! empty( $aioseop_options['aiosp_ga_advanced_options'] ) && ! empty( $aioseop_options['aiosp_ga_multi_domain'] ) ) {
				$allow_linker = "'allowLinker': true"; // This is put into $js_options later.
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
								$domain_list .= "'" . $d . "'";
							}
						}
					}
				}
			}
			$extra_options = '';
			if ( ! empty( $aioseop_options['aiosp_ga_advanced_options'] ) && ! empty( $aioseop_options['aiosp_ga_display_advertising'] ) ) {
				$extra_options .= "ga('require', 'displayfeatures');";
			}
			if ( ! empty( $aioseop_options['aiosp_ga_advanced_options'] ) && ! empty( $aioseop_options['aiosp_ga_enhanced_ecommerce'] ) ) {
				if ( ! empty( $extra_options ) ) {
					$extra_options .= "\n\t\t\t";
				}
				$extra_options .= "ga('require', 'ec');";
			}
			if ( ! empty( $domain_list ) ) {
				if ( ! empty( $extra_options ) ) {
					$extra_options .= "\n\t\t\t";
				}
				$extra_options .= "ga('require', 'linker');\n\t\t\tga('linker:autoLink', [{$domain_list}] );";
			}
			if ( ! empty( $aioseop_options['aiosp_ga_advanced_options'] ) && ! empty( $aioseop_options['aiosp_ga_link_attribution'] ) ) {
				if ( ! empty( $extra_options ) ) {
					$extra_options .= "\n\t\t\t";
				}
				$extra_options .= "ga('require', 'linkid', 'linkid.js');";
			}

			if ( ! empty( $aioseop_options['aiosp_ga_advanced_options'] ) && ! empty( $aioseop_options['aiosp_ga_anonymize_ip'] ) ) {
				if ( ! empty( $extra_options ) ) {
					$extra_options .= "\n\t\t\t";
				}
				$extra_options .= "ga('set', 'anonymizeIp', true);";
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
			$analytics_id     = esc_js( $aioseop_options['aiosp_google_analytics_id'] );
			$attributes       = apply_filters( 'aioseop_ga_attributes', '' );
			$open_script_line = preg_replace( '/\s+/', ' ', "<script $attributes type=\"text/javascript\">" ); // This is just to get the number of spaces right.
			$analytics        = <<<EOF
					
			$open_script_line
			(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
			(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
			m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
			})(window,document,'script','//www.google-analytics.com/analytics.js','ga');

			ga('create', '{$analytics_id}'{$domain}{$js_options});
			{$extra_options}
			ga('send', 'pageview');
			</script>

EOF;

			return $analytics;
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
