<?php
/**
 * @package All-in-One-SEO-Pack
 */
/**
 * The Robots class.
 */
if ( ! class_exists( 'All_in_One_SEO_Pack_Robots' ) ) {
	class All_in_One_SEO_Pack_Robots extends All_in_One_SEO_Pack_Module {

		function __construct() {
			$this->name   = __( 'Robots.txt', 'all-in-one-seo-pack' );    // Human-readable name of the plugin
			$this->prefix = 'aiosp_robots_';                        // option prefix
			$this->file   = __FILE__;                                    // the current file
			parent::__construct();

			$help_text = Array(
				'additional' => __( 'Rule Type', 'all-in-one-seo-pack' ),
				'useragent'  => __( 'User Agent', 'all-in-one-seo-pack' ),
				'path'       => __( 'Directory Path', 'all-in-one-seo-pack' ),
				'robotgen'   => __( 'Robots.txt editor', 'all-in-one-seo-pack' ),
			);

			$this->default_options = array(
				'usage'              => Array(
					'type'    => 'html',
					'label'   => 'none',
					'default' => __( 'Use the rule builder below to add rules to create a new Robots.txt file.  If you already have a Robots.txt file you should use the File Editor feature in All in One SEO Pack to edit it or you can delete your current Robots.txt file and start a new one with the rule builder below.', 'all-in-one-seo-pack' ),
					'save'    => false,
				),
				'additional'         => Array(
					'name'            => __( 'Rule Type', 'all-in-one-seo-pack' ),
					'save'            => false,
					'type'            => 'select',
					'initial_options' => Array( 'allow' => 'Allow', 'block' => 'Block' ),
				),
				'useragent'          => Array(
					'name' => __( 'User Agent', 'all-in-one-seo-pack' ),
					'save' => false,
					'type' => 'text',
				),
				'path'               => Array(
					'name' => __( 'Directory Path', 'all-in-one-seo-pack' ),
					'save' => false,
					'type' => 'text',
				),
				'robotgen'           => Array(
					'name'     => __( 'Generate Robots.txt', 'all-in-one-seo-pack' ),
					'save'     => false,
					'default'  => '',
					'type'     => 'textarea',
					'cols'     => 57,
					'rows'     => 20,
					'label'    => 'none',
					'readonly' => 'readonly',
				),
				'Submit_Preview'     => Array(
					'type'   => 'submit',
					'class'  => 'button-primary MRL',
					'name'   => __( 'Add Rule', 'all-in-one-seo-pack' ) . ' &raquo;',
					'nowrap' => 1,
				),
				'Submit_Update'      => Array(
					'type'   => 'submit',
					'class'  => 'button-primary',
					'name'   => __( 'Save Robots.txt File', 'all-in-one-seo-pack' ) . ' &raquo;',
					'nowrap' => 1,
				),
				'Submit_Delete'      => Array(
					'type'   => 'submit',
					'class'  => 'button-primary',
					'name'   => __( 'Delete Robots.txt File', 'all-in-one-seo-pack' ) . ' &raquo;',
					'nowrap' => 1,
				),
				'optusage'           => Array(
					'type'    => 'html',
					'label'   => 'none',
					'default' => __( 'Click the Optimize button below and All in One SEO Pack will analyze your Robots.txt file to make sure it complies with the standards for Robots.txt files.  The results will be displayed in a table below.', 'all-in-one-seo-pack' ),
					'save'    => false,
				),
				'Submit_Opt_Update'  => Array(
					'type'   => 'submit',
					'class'  => 'button-primary',
					'name'   => __( 'Update Robots.txt File', 'all-in-one-seo-pack' ) . ' &raquo;',
					'nowrap' => 1,
					'style'  => 'margin-left: 20px;',
				),
				'Submit_Opt_Preview' => Array(
					'type'   => 'submit',
					'class'  => 'button-primary',
					'name'   => __( 'Disregard Changes', 'all-in-one-seo-pack' ) . ' &raquo;',
					'nowrap' => 1,
				),
				'Submit_Optimize'    => Array(
					'type'  => 'submit',
					'class' => 'button-primary',
					'name'  => __( 'Optimize', 'all-in-one-seo-pack' ) . ' &raquo;',
				),
			);

			if ( ! empty( $help_text ) ) {
				foreach ( $help_text as $k => $v ) {
					$this->default_options[ $k ]['help_text'] = $v;
				}
			}

			$this->locations = array(
				'generator' => Array(
					'name'    => "Robots.txt",
					'type'    => 'settings',
					'options' => Array(
						'usage',
						'additional',
						'useragent',
						'path',
						'Submit_Preview',
						'Submit_Update',
						'Submit_Delete',
						'robotgen',
						'optusage',
						'Submit_Opt_Update',
						'Submit_Opt_Preview',
						'Submit_Optimize',
					),
				),
			);

			$this->layout             = Array(
				'default' => Array(
					'name'    => __( 'Create a Robots.txt File', 'all-in-one-seo-pack' ),
					'options' => Array(
						'usage',
						'additional',
						'useragent',
						'path',
						'Submit_Preview',
						'Submit_Update',
						'Submit_Delete',
						'robotgen',
					) // this is set below, to the remaining options -- pdb
				),
			);
			$this->layout['optimize'] = Array(
				'name'    => __( 'Optimize your Robots.txt File', 'all-in-one-seo-pack' ),
				'options' => Array( 'optusage', 'Submit_Optimize' ),
			);
			if ( isset( $_POST['Submit_Optimize'] ) ) {
				$this->layout['optimize']['options']          = Array(
					'optusage',
					'Submit_Opt_Update',
					'Submit_Opt_Preview',
					'robothtml',
				);
				$this->default_options['optusage']['default'] = __( "Your Robots.txt file has been optimized.  Here are the results and recommendations.  Click the Update Robots.txt File button below to write these changes to your Robots.txt file.  Click the Disregard Changes button to ignore these recommendations and keep your current Robots.txt file.", 'all-in-one-seo-pack' );
			}

			// load initial options / set defaults
			$this->update_options();

			add_action( $this->prefix . 'settings_update', Array( $this, 'do_robots' ), 10, 2 );
			add_filter( $this->prefix . 'display_options', Array( $this, 'filter_options' ), 10, 2 );
			add_filter( $this->prefix . 'submit_options', Array( $this, 'filter_submit' ), 10, 2 );
			add_filter( $this->prefix . 'display_settings', Array( $this, 'filter_settings' ), 10, 2 );
		}

		function filter_settings( $settings, $location ) {
			if ( $location == 'generator' ) {
				$prefix = $this->get_prefix( $location ) . $location . '_';
				if ( isset( $_POST['Submit_Optimize'] ) ) {
					if ( isset( $settings[ $prefix . 'robotgen' ] ) ) {
						$settings[ $prefix . 'robotgen' ]['type']      = 'hidden';
						$settings[ $prefix . 'robotgen' ]['label']     = 'none';
						$settings[ $prefix . 'robotgen' ]['help_text'] = '';
						$settings[ $prefix . 'robothtml' ]             = Array(
							'name'    => __( 'Robots.txt', 'all-in-one-seo-pack' ),
							'save'    => false,
							'default' => '',
							'type'    => 'html',
							'label'   => 'none',
							'style'   => 'margin-top:10px;',
						);
					}
				}
			}

			return $settings;
		}

		function filter_submit( $submit, $location ) {
			if ( $location == 'generator' ) {
				unset( $submit['Submit_Default'] );
				$submit['Submit']['type'] = 'hidden';
			}

			return $submit;
		}

		/**
		 * Returns the sitemap filename;
		 *
		 * @return bool
		 */
		function get_sitemap_filename() {

			global $aioseop_options;
			if ( isset( $aioseop_options['modules']['aiosp_sitemap_options']['aiosp_sitemap_filename'] ) ) {
				return $aioseop_options['modules']['aiosp_sitemap_options']['aiosp_sitemap_filename'];
			}

			return false;
		}

		/**
		 * Filters the options.
		 *
		 * @todo  Much of this couldn't be considered filtering options, and should be extracted to other functions.
		 * @since ??
		 * @since 2.3.6
		 */
		function filter_options( $options, $location ) {
			if ( $location ) {
				$prefix = $this->get_prefix( $location ) . $location . '_';
			}
			if ( $location === 'generator' ) {
				$optimize = false;
				$robotgen = '';
				if ( ! empty( $_POST[ $prefix . 'robotgen' ] ) ) {
					$robotgen = str_replace( "\r\n", "\n", $_POST[ $prefix . 'robotgen' ] );
				}
				if ( isset( $_POST['Submit_Preview'] ) ) {
					$options[ $prefix . 'robotgen' ] = $robotgen;
				}
				if ( ! isset( $_POST['Submit_Preview'] ) ) {
					if ( isset( $_POST['Submit_Optimize'] ) && ! isset( $_POST['Submit_Delete'] ) && ! isset( $_POST['Submit_Update'] ) && ! isset( $_POST['Submit_Opt_Update'] ) ) {
						$optimize = true;
					}
					if ( ! isset( $options[ $prefix . 'robotgen' ] ) || empty( $options[ $prefix . 'robotgen' ] ) ) {
						if ( $optimize ) {
							$options[ $prefix . 'robotgen' ] = $robotgen;
						}
						if ( empty( $options[ $prefix . 'robotgen' ] ) ) {
							$options = $this->load_files( $options, Array( 'robotgen' => 'robots.txt' ), $prefix );
						}
					}
				}
				$access = ( get_option( 'blog_public' ) ) ? 'allow' : 'block';
				if ( $access ) {
					global $aioseop_options;
					$sitemapurl      = '';
					$sitemap_filename = $this->get_sitemap_filename();
					if ( $sitemap_filename ) {
						$sitemapurl = trailingslashit( get_home_url() ) . $sitemap_filename . '.xml';
					}
					$allow_rule = "Sitemap: $sitemapurl \n\n# global\nUser-agent: *\nDisallow: /xmlrpc.php\n\n";
					$block_rule = "# global\nUser-agent: *\nDisallow: /\n\n";
					if ( empty( $options[ $prefix . 'robotgen' ] ) ) {
						$options[ $prefix . 'robotgen' ] = '';
					}
					if ( isset( $_POST['Submit_Preview'] ) && ( ( $options[ $prefix . 'robotgen' ] == $allow_rule ) ||
					                                            ( $options[ $prefix . 'robotgen' ] == $block_rule ) )
					) {
						$options[ $prefix . 'robotgen' ] = '';
					}
					if ( $access === 'block' && empty( $options[ $prefix . 'robotgen' ] ) ) {
						$options[ $prefix . 'robotgen' ] .= $block_rule;
					} elseif ( $access === 'allow' && empty( $options[ $prefix . 'robotgen' ] ) ) {
						$options[ $prefix . 'robotgen' ] .= $allow_rule;
					}
				}
				foreach ( Array( 'ad' => 'additional', 'ua' => 'useragent', 'dp' => 'path' ) as $k => $v ) {
					if ( isset( $_POST[ $prefix . $v ] ) ) {
						$$k = $_POST[ $prefix . $v ];
					}
				}
				if ( ! empty( $ad ) && ! empty( $ua ) && ! empty( $dp ) ) {
					if ( $ad === 'allow' ) {
						$ad = "Allow: ";
					} else {
						$ad = "Disallow: ";
					}
					$options[ $prefix . 'robotgen' ] .= "User-agent: $ua\n$ad $dp\n\n";
				}
				$file = explode( "\n", $options[ $prefix . 'robotgen' ] );
				if ( $optimize ) {
					$rules       = $this->parse_robots( $file );
					$user_agents = $this->get_robot_user_agents( $rules );
					foreach ( $user_agents as $ua => $rules ) {
						$user_agents[ $ua ]['disallow'] = $this->opt_robot_rule( $rules['disallow'] );
						$user_agents[ $ua ]['allow']    = $this->opt_robot_rule( $rules['allow'] );
					}
					$rules = $this->flatten_user_agents( $user_agents );
					unset( $user_agents );
					foreach ( $rules as $r ) {
						$r['disallow'] = $this->opt_robot_rule( $r['disallow'] );
						$r['allow']    = $this->opt_robot_rule( $r['allow'] );
					}
					$options[ $prefix . 'robotgen' ]  = $this->output_robots( $rules );
					$file2                            = explode( "\n", $options[ $prefix . 'robotgen' ] );
					$options[ $prefix . 'robothtml' ] = '<table width=100%><tr><td valign=top width=45%>' . $this->annotate_robots_html( $file, true, __( "Current File", 'all-in-one-seo-pack' ) ) . '</td><td><span style="font-size: xx-large">&#8594;</span></td><td valign=top>' . $this->annotate_robots_html( $file2, true, __( "Proposed Changes", 'all-in-one-seo-pack' ) ) . '</td></tr></table>';
				} else {
					$options[ $prefix . 'robothtml' ] = $this->annotate_robots_html( $file, true, __( "Current File", 'all-in-one-seo-pack' ) );
				}
			}

			return $options;
		}

		function do_robots( $options, $location ) {
			if ( $location ) {
				$prefix = $this->get_prefix( $location ) . $location . '_';
			}
			if ( $location === 'generator' ) {
				if ( isset( $_POST['Submit_Update'] ) || isset( $_POST['Submit_Opt_Update'] ) ) {
					$this->save_files( Array( 'robotgen' => 'robots.txt' ), $prefix );
				} elseif ( isset( $_POST['Submit_Delete'] ) ) {
					$this->delete_files( Array( 'robotgen' => 'robots.txt' ) );
				}
			}
		}

		function annotate_robots_html( $file, $show_help = false, $title = '' ) {
			$robots = $this->annotate_robots( $file );
			if ( ! empty( $robots ) ) {
				$buf = '<table class="widefat" ><thead>';
				if ( ! empty( $title ) ) {
					$buf .= "<tr><th colspan=3>" . $title . "</th></tr>";
				}
				$buf .= '<tr class="aioseop_optimize_thread">';
				$buf .= '<th style="width:5%;"></th><th style="width:78%;"><span class="column_label" >Parameter</span></th>';
				$buf .= '<th><span class="" >Status</span></th></tr></thead>';
				$buf .= "<tbody>";

				foreach ( $robots as $r ) {
					$class  = 'robots';
					$status = "#9cf975";
					$help   = '';
					if ( ! $r['valid'] || ! $r['strict'] ) {
						if ( ! $r['strict'] ) {
							$class .= ' quirks';
							$status = "yellow";
						}
						if ( ! $r['valid'] ) {
							$class .= ' invalid';
							$status = "#f9534a";
						}
						if ( $show_help ) {
							$help = '<a style="cursor:pointer;" class="' . $class . '" title="Click for Help!" onclick="toggleVisibility(\'aiosp_robots_main_legend_tip\');" title="Click for Help">'
							        . '<div class="aioseop_tip_icon"></div></a>';
						}
					}
					$buf .= "<tr class='entry-row {$class}'><td>{$help}</td><td><span class='entry_label'>{$r['content']}</td><td><div style='background:{$status};'></div></td></tr>";
				}
				$buf .= '</tbody>';

				$buf .= '</table>';
				if ( $show_help ) {
					$buf .= '<div class="aioseop_option_docs" id="aiosp_robots_main_legend_tip">
				<h3>' . __( 'Legend', 'all-in-one-seo-pack' ) . '</h3>
				<ul>
				<li>' . __( 'The yellow indicator means that a non-standard extension was recognized; not all crawlers may recognize it or interpret it the same way. The Allow and Sitemap directives are commonly used by Google and Yahoo.', 'all-in-one-seo-pack' ) . '</li>
				<li>' . __( 'The red indicator means that the syntax is invalid for a robots.txt file.', 'all-in-one-seo-pack' ) . '</li>
				</ul>
				<a target="_blank" rel="nofollow" href="https://wikipedia.org/wiki/Robots_exclusion_standard#Nonstandard_extensions">' . __( 'More Information', 'all-in-one-seo-pack' ) . '</a>
				</div>';
				}
			} else {
				$buf = '<p class="aioseop_error_notice" ><strong>Your Robots.txt file is either empty, cannot be found, or has invalid data.</strong></p>';
			}

			return $buf;
		}

		function annotate_robots( $robots ) {
			$state = 0;
			$rules = Array();
			foreach ( $robots as $l ) {
				$l = trim( $l );
				if ( empty( $l[0] ) ) {
					if ( $state > 1 ) {
						$rules[] = Array(
							'state'   => 0,
							'type'    => 'blank',
							'content' => $l,
							'valid'   => true,
							'strict'  => true,
						);
						$state   = 0;
					}
				} elseif ( $l[0] === '#' ) {
					if ( $state < 1 ) {
						$state = 1;
					}
					$rules[] = Array(
						'state'   => $state,
						'type'    => 'comment',
						'content' => $l,
						'valid'   => true,
						'strict'  => true,
					);
				} elseif ( stripos( $l, 'sitemap' ) === 0 ) {
					$state   = 2;
					$rules[] = Array(
						'state'   => $state,
						'type'    => 'sitemap',
						'content' => $l,
						'valid'   => true,
						'strict'  => false,
					);
				} elseif ( stripos( $l, 'crawl-delay' ) === 0 ) {
					$state   = 3;
					$rules[] = Array(
						'state'   => $state,
						'type'    => 'crawl-delay',
						'content' => $l,
						'valid'   => true,
						'strict'  => false,
					);
				} elseif ( stripos( $l, 'user-agent' ) === 0 ) {
					$state   = 3;
					$rules[] = Array(
						'state'   => $state,
						'type'    => 'user-agent',
						'content' => $l,
						'valid'   => true,
						'strict'  => true,
					);
				} elseif ( stripos( $l, 'useragent' ) === 0 ) {
					$state   = 3;
					$rules[] = Array(
						'state'   => $state,
						'type'    => 'user-agent',
						'content' => $l,
						'valid'   => true,
						'strict'  => false,
					);
				} elseif ( stripos( $l, 'disallow' ) === 0 ) {
					if ( $state < 3 ) {
						$rules[] = Array(
							'state'   => $state,
							'type'    => 'disallow',
							'content' => $l,
							'valid'   => false,
							'strict'  => false,
						);
						continue;
					}
					$state   = 3;
					$rules[] = Array(
						'state'   => $state,
						'type'    => 'disallow',
						'content' => $l,
						'valid'   => true,
						'strict'  => true,
					);
				} elseif ( stripos( $l, 'allow' ) === 0 ) {
					if ( $state < 3 ) {
						$rules[] = Array(
							'state'   => $state,
							'type'    => 'allow',
							'content' => $l,
							'valid'   => false,
							'strict'  => false,
						);
						continue;
					}
					$state   = 3;
					$rules[] = Array(
						'state'   => $state,
						'type'    => 'allow',
						'content' => $l,
						'valid'   => true,
						'strict'  => false,
					);
				} else {
					$rules[] = Array(
						'state'   => $state,
						'type'    => 'unknown',
						'content' => $l,
						'valid'   => false,
						'strict'  => false,
					);
				}
			}

			return $rules;
		}

		function parse_annotated_robots( $robots ) {
			$state = 0;
			$rules = Array();
			$opts  = Array( 'sitemap', 'crawl-delay', 'user-agent', 'allow', 'disallow', 'comment' );
			$rule  = Array();
			foreach ( $opts as $o ) {
				$rule[ $o ] = Array();
			}
			$blank_rule = $rule;
			foreach ( $robots as $l ) {
				switch ( $l['type'] ) {
					case 'blank':
						if ( $state >= 1 ) {
							if ( ( $state === 1 ) && ( empty( $rule['user-agent'] ) ) ) {
								$rule['user-agent'] = Array( null );
							}
							$rules[] = $rule;
							$rule    = $blank_rule;
						}
						continue;
					case 'comment':
						$rule['comment'][] = $l['content'];
						continue;
					case 'sitemap':
						$rule['sitemap'][] = trim( substr( $l['content'], 8 ) );
						break;
					case 'crawl-delay':
						$rule['crawl-delay'][] = trim( substr( $l['content'], 12 ) );
						break;
					case 'user-agent':
						if ( $l['strict'] ) {
							$ua = trim( substr( $l['content'], 11 ) );
						} else {
							$ua = trim( substr( $l['content'], 10 ) );
						}
						$rule['user-agent'][] = $ua;
						break;
					case 'disallow':
						if ( $l['valid'] ) {
							$rule['disallow'][] = trim( substr( $l['content'], 9 ) );
							break;
						}
						continue;
					case 'allow':
						if ( $l['valid'] ) {
							$rule['allow'][] = trim( substr( $l['content'], 6 ) );
							break;
						}
						continue;
					case 'unknown':
					default:
				}
				$state = $l['state'];
			}
			if ( ( $state === 1 ) && ( empty( $rule['user-agent'] ) ) ) {
				$rule['user-agent'] = Array( null );
			}
			if ( $state >= 1 ) {
				$rules[] = $rule;
			}

			return $rules;
		}

		function parse_robots( $robots ) {
			return $this->parse_annotated_robots( $this->annotate_robots( $robots ) );
		}

		function get_robot_user_agents( $rules ) {
			$opts        = Array( 'sitemap', 'crawl-delay', 'user-agent', 'allow', 'disallow', 'comment' );
			$user_agents = Array();
			foreach ( $rules as $r ) {
				if ( ! empty( $r['sitemap'] ) && empty( $r['user-agent'] ) ) {
					$r['user-agent'] = Array( null );
				}
				foreach ( $r['user-agent'] as $ua ) {
					if ( ! isset( $user_agents[ $ua ] ) ) {
						$user_agents[ $ua ] = Array();
					}
					foreach ( $opts as $o ) {
						if ( ! isset( $user_agents[ $ua ][ $o ] ) ) {
							$user_agents[ $ua ][ $o ] = $r[ $o ];
						} else {
							$user_agents[ $ua ][ $o ] = array_merge( $user_agents[ $ua ][ $o ], $r[ $o ] );
						}
					}
				}
			}

			return $user_agents;
		}

		function flatten_user_agents( $user_agents ) {
			$rules = Array();
			foreach ( $user_agents as $ua => $r ) {
				$r['user-agent'] = Array( $ua );
				$rules[]         = $r;
			}

			return $rules;
		}

		function opt_robot_rule( $dis ) {
			if ( is_array( $dis ) ) { // unique rules only
				$dis = array_unique( $dis, SORT_STRING );
				$pd  = null;
				foreach ( $dis as $k => $d ) {
					$d = trim( $d );
					if ( ! empty( $pd ) && ! empty( $d ) ) {
						if ( strpos( $d, $pd ) === 0 ) {
							unset( $dis[ $k ] );
							continue; // get rid of subpaths of $pd
						}
					}
					$l = strlen( $d );
					if ( ( $l > 0 ) && ( $d[ $l - 1 ] !== '/' ) ) {
						continue;
					}
					$pd = $d; // only allow directory paths for $pd
				}
			}

			return $dis;
		}

		function output_robots( $rules ) {
			$robots = '';
			foreach ( $rules as $r ) {
				foreach ( $r['comment'] as $c ) {
					$robots .= "$c\n";
				}
				foreach ( $r['user-agent'] as $u ) {
					if ( $u != '' ) {
						$robots .= "User-agent: $u\n";
					}
				}
				foreach ( $r['crawl-delay'] as $c ) {
					$robots .= "Crawl-Delay: $c\n";
				}
				foreach ( $r['allow'] as $a ) {
					$robots .= "Allow: $a\n";
				}
				foreach ( $r['disallow'] as $d ) {
					$robots .= "Disallow: $d\n";
				}
				foreach ( $r['sitemap'] as $s ) {
					$robots .= "Sitemap: $s\n";
				}
				$robots .= "\n";
			}

			return $robots;
		}
	}
}
