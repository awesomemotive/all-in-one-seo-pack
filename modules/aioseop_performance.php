<?php
/**
 * The Performance class.
 *
 * @package All-in-One-SEO-Pack
 */

if ( ! class_exists( 'All_in_One_SEO_Pack_Performance' ) ) {

	class All_in_One_SEO_Pack_Performance extends All_in_One_SEO_Pack_Module {

		protected $module_info = array();

		function __construct( $mod ) {
			$this->name   = __( 'Performance', 'all-in-one-seo-pack' );        // Human-readable name of the plugin.
			$this->prefix = 'aiosp_performance_';                        // Option prefix.
			$this->file   = __FILE__;                                    // The current file.
			parent::__construct();

			$this->help_text = array(
				'memory_limit'   => __( 'This setting allows you to raise your PHP memory limit to a reasonable value. Note: WordPress core and other WordPress plugins may also change the value of the memory limit.', 'all-in-one-seo-pack' ),
				'execution_time' => __( 'This setting allows you to raise your PHP execution time to a reasonable value.', 'all-in-one-seo-pack' ),
				'force_rewrites' => __( 'Use output buffering to ensure that the title gets rewritten. Enable this option if you run into issues with the title tag being set by your theme or another plugin.', 'all-in-one-seo-pack' ),
			);

			$this->default_options = array(
				'memory_limit'   => array(
					'name'            => __( 'Raise memory limit', 'all-in-one-seo-pack' ),
					'default'         => '256M',
					'type'            => 'select',
					'initial_options' => array(
						0      => __( 'Use the system default', 'all-in-one-seo-pack' ),
						'32M'  => '32MB',
						'64M'  => '64MB',
						'128M' => '128MB',
						'256M' => '256MB',
					),
				),
				'execution_time' => array(
					'name'            => __( 'Raise execution time', 'all-in-one-seo-pack' ),
					'default'         => '',
					'type'            => 'select',
					'initial_options' => array(
						''  => __( 'Use the system default', 'all-in-one-seo-pack' ),
						30  => '30s',
						60  => '1m',
						120 => '2m',
						300 => '5m',
						0   => __( 'No limit', 'all-in-one-seo-pack' ),
					),
				),
			);

			$this->help_anchors = array(
				'memory_limit'   => '#raise-memory-limit',
				'execution_time' => '#raise-execution-time',
				'force_rewrites' => '#force-rewrites',
			);

			global $aiosp, $aioseop_options;
			if ( aioseop_option_isset( 'aiosp_rewrite_titles' ) && $aioseop_options['aiosp_rewrite_titles'] ) {
				$this->default_options['force_rewrites'] = array(
					'name'            => __( 'Force Rewrites:', 'all-in-one-seo-pack' ),
					'default'         => 1,
					'type'            => 'radio',
					'initial_options' => array(
						1 => __( 'Enabled', 'all-in-one-seo-pack' ),
						0 => __( 'Disabled', 'all-in-one-seo-pack' ),
					),
				);
			}

			$this->layout = array(
				'default' => array(
					'name'      => $this->name,
					'help_link' => 'https://semperplugins.com/documentation/performance-settings/',
					'options'   => array_keys( $this->default_options ),
				),
			);

			$system_status = array(
				'status' => array( 'default' => '', 'type' => 'html', 'label' => 'none', 'save' => false ),
				'send_email' => array( 'default' => '', 'type' => 'html', 'label' => 'none', 'save' => false ),
			);

			$this->layout['system_status'] = array(
				'name'      => __( 'System Status', 'all-in-one-seo-pack' ),
				'help_link' => 'https://semperplugins.com/documentation/performance-settings/',
				'options'   => array_keys( $system_status ),
			);

			$this->default_options = array_merge( $this->default_options, $system_status );

			$this->add_help_text_links();

			add_filter( $this->prefix . 'display_options', array( $this, 'display_options_filter' ), 10, 2 );
			add_filter( $this->prefix . 'update_options', array( $this, 'update_options_filter' ), 10, 2 );
			add_action( $this->prefix . 'settings_update', array( $this, 'settings_update_action' ), 10, 2 );
		}

		function update_options_filter( $options, $location ) {
			if ( $location == null && isset( $options[ $this->prefix . 'force_rewrites' ] ) ) {
				unset( $options[ $this->prefix . 'force_rewrites' ] );
			}

			return $options;
		}

		function display_options_filter( $options, $location ) {
			if ( $location == null ) {
				$options[ $this->prefix . 'force_rewrites' ] = 1;
				global $aiosp;
				if ( aioseop_option_isset( 'aiosp_rewrite_titles' ) ) {
					$opts                                        = $aiosp->get_current_options( array(), null );
					$options[ $this->prefix . 'force_rewrites' ] = $opts['aiosp_force_rewrites'];
				}
			}

			return $options;
		}

		function settings_update_action( $options, $location ) {
			if ( $location == null && isset( $_POST[ $this->prefix . 'force_rewrites' ] ) ) {
				$force_rewrites = $_POST[ $this->prefix . 'force_rewrites' ];
				if ( ( $force_rewrites == 0 ) || ( $force_rewrites == 1 ) ) {
					global $aiosp;
					$opts                         = $aiosp->get_current_options( array(), null );
					$opts['aiosp_force_rewrites'] = $force_rewrites;
					$aiosp->update_class_option( $opts );
					wp_cache_flush();
				}
			}
		}

		function add_page_hooks() {
			$memory_usage = memory_get_peak_usage() / 1024 / 1024;
			if ( $memory_usage > 32 ) {
				unset( $this->default_options['memory_limit']['initial_options']['32M'] );
				if ( $memory_usage > 64 ) {
					unset( $this->default_options['memory_limit']['initial_options']['64M'] );
				}
				if ( $memory_usage > 128 ) {
					unset( $this->default_options['memory_limit']['initial_options']['128M'] );
				}
				if ( $memory_usage > 256 ) {
					unset( $this->default_options['memory_limit']['initial_options']['256M'] );
				}
			}
			$this->update_options();
			parent::add_page_hooks();
		}

		function settings_page_init() {
			$this->default_options['status']['default'] = $this->get_serverinfo();
			$this->default_options['send_email']['default'] = $this->get_email_input();
		}

		function menu_order() {
			return 7;
		}

		function get_serverinfo() {
			global $wpdb;
			global $wp_version;

			$sqlversion = $wpdb->get_var( "SELECT VERSION() AS version" );
			$mysqlinfo  = $wpdb->get_results( "SHOW VARIABLES LIKE 'sql_mode'" );
			if ( is_array( $mysqlinfo ) ) {
				$sql_mode = $mysqlinfo[0]->Value;
			}
			if ( empty( $sql_mode ) ) {
				$sql_mode = __( 'Not set', 'all-in-one-seo-pack' );
			}
			if ( ini_get( 'allow_url_fopen' ) ) {
				$allow_url_fopen = __( 'On', 'all-in-one-seo-pack' );
			} else {
				$allow_url_fopen = __( 'Off', 'all-in-one-seo-pack' );
			}
			if ( ini_get( 'upload_max_filesize' ) ) {
				$upload_max = ini_get( 'upload_max_filesize' );
			} else {
				$upload_max = __( 'N/A', 'all-in-one-seo-pack' );
			}
			if ( ini_get( 'post_max_size' ) ) {
				$post_max = ini_get( 'post_max_size' );
			} else {
				$post_max = __( 'N/A', 'all-in-one-seo-pack' );
			}
			if ( ini_get( 'max_execution_time' ) ) {
				$max_execute = ini_get( 'max_execution_time' );
			} else {
				$max_execute = __( 'N/A', 'all-in-one-seo-pack' );
			}
			if ( ini_get( 'memory_limit' ) ) {
				$memory_limit = ini_get( 'memory_limit' );
			} else {
				$memory_limit = __( 'N/A', 'all-in-one-seo-pack' );
			}
			if ( function_exists( 'memory_get_usage' ) ) {
				$memory_usage = round( memory_get_usage() / 1024 / 1024, 2 ) . __( ' MByte', 'all-in-one-seo-pack' );
			} else {
				$memory_usage = __( 'N/A', 'all-in-one-seo-pack' );
			}
			if ( is_callable( 'exif_read_data' ) ) {
				$exif = __( 'Yes', 'all-in-one-seo-pack' ) . ' ( V' . $this->substr( phpversion( 'exif' ), 0, 4 ) . ')';
			} else {
				$exif = __( 'No', 'all-in-one-seo-pack' );
			}
			if ( is_callable( 'iptcparse' ) ) {
				$iptc = __( 'Yes', 'all-in-one-seo-pack' );
			} else {
				$iptc = __( 'No', 'all-in-one-seo-pack' );
			}
			if ( is_callable( 'xml_parser_create' ) ) {
				$xml = __( 'Yes', 'all-in-one-seo-pack' );
			} else {
				$xml = __( 'No', 'all-in-one-seo-pack' );
			}

			$theme = wp_get_theme();

			if ( function_exists( 'is_multisite' ) ) {
				if ( is_multisite() ) {
					$ms = __( 'Yes', 'all-in-one-seo-pack' );
				} else {
					$ms = __( 'No', 'all-in-one-seo-pack' );
				}

			} else {
				$ms = __( 'N/A', 'all-in-one-seo-pack' );
			}

			$siteurl        = get_option( 'siteurl' );
			$homeurl        = get_option( 'home' );
			$db_version     = get_option( 'db_version' );
			$site_title     = get_bloginfo( 'name' );
			$language       = get_bloginfo( 'language' );
			$front_displays = get_option( 'show_on_front' );
			$page_on_front  = get_option( 'page_on_front' );
			$blog_public    = get_option( 'blog_public' );
			$perm_struct    = get_option( 'permalink_structure' );

			$debug_info                   = array(
				__( 'Operating System', 'all-in-one-seo-pack' )            => PHP_OS,
				__( 'Server', 'all-in-one-seo-pack' )                      => $_SERVER['SERVER_SOFTWARE'],
				__( 'Memory usage', 'all-in-one-seo-pack' )                => $memory_usage,
				__( 'MYSQL Version', 'all-in-one-seo-pack' )               => $sqlversion,
				__( 'SQL Mode', 'all-in-one-seo-pack' )                    => $sql_mode,
				__( 'PHP Version', 'all-in-one-seo-pack' )                 => PHP_VERSION,
				__( 'PHP Allow URL fopen', 'all-in-one-seo-pack' )         => $allow_url_fopen,
				__( 'PHP Memory Limit', 'all-in-one-seo-pack' )            => $memory_limit,
				__( 'PHP Max Upload Size', 'all-in-one-seo-pack' )         => $upload_max,
				__( 'PHP Max Post Size', 'all-in-one-seo-pack' )           => $post_max,
				__( 'PHP Max Script Execute Time', 'all-in-one-seo-pack' ) => $max_execute,
				__( 'PHP Exif support', 'all-in-one-seo-pack' )            => $exif,
				__( 'PHP IPTC support', 'all-in-one-seo-pack' )            => $iptc,
				__( 'PHP XML support', 'all-in-one-seo-pack' )             => $xml,
				__( 'Site URL', 'all-in-one-seo-pack' )                    => $siteurl,
				__( 'Home URL', 'all-in-one-seo-pack' )                    => $homeurl,
				__( 'WordPress Version', 'all-in-one-seo-pack' )           => $wp_version,
				__( 'WordPress DB Version', 'all-in-one-seo-pack' )        => $db_version,
				__( 'Multisite', 'all-in-one-seo-pack' )                   => $ms,
				__( 'Active Theme', 'all-in-one-seo-pack' )                => $theme['Name'] . ' ' . $theme['Version'],
				__( 'Site Title', 'all-in-one-seo-pack' )                  => $site_title,
				__( 'Site Language', 'all-in-one-seo-pack' )               => $language,
				__( 'Front Page Displays', 'all-in-one-seo-pack' )         => $front_displays === 'page' ? $front_displays . ' [ID = ' . $page_on_front . ']' : $front_displays,
				__( 'Search Engine Visibility', 'all-in-one-seo-pack' )    => $blog_public,
				__( 'Permalink Setting', 'all-in-one-seo-pack' )           => $perm_struct,
			);
			$debug_info['Active Plugins'] = null;
			$active_plugins               = $inactive_plugins = array();
			$plugins                      = get_plugins();
			foreach ( $plugins as $path => $plugin ) {
				if ( is_plugin_active( $path ) ) {
					$debug_info[ $plugin['Name'] ] = $plugin['Version'];
				} else {
					$inactive_plugins[ $plugin['Name'] ] = $plugin['Version'];
				}
			}
			$debug_info['Inactive Plugins'] = null;
			$debug_info                     = array_merge( $debug_info, (array) $inactive_plugins );

			$mail_text = __( 'All in One SEO Pack Pro Debug Info', 'all-in-one-seo-pack' ) . "\r\n------------------\r\n\r\n";
			$page_text = '';
			if ( ! empty( $debug_info ) ) {
				foreach ( $debug_info as $name => $value ) {
					if ( $value !== null ) {
						$page_text .= "<li><strong>$name</strong> $value</li>";
						$mail_text .= "$name: $value\r\n";
					} else {
						$page_text .= "</ul><h2>$name</h2><ul class='sfwd_debug_settings'>";
						$mail_text .= "\r\n$name\r\n----------\r\n";
					}
				}
			}

			do {
				if ( ! empty( $_REQUEST['sfwd_debug_submit'] ) ) {
					$nonce = $_REQUEST['sfwd_debug_nonce'];
					if ( ! wp_verify_nonce( $nonce, 'sfwd-debug-nonce' ) ) {
						echo "<div class='sfwd_debug_error'>" . __( 'Form submission error: verification check failed.', 'all-in-one-seo-pack' ) . '</div>';
						break;
					}
					$email = '';
					if ( ! empty( $_REQUEST['sfwd_debug_send_email'] ) ) {
						$email = sanitize_email( $_REQUEST['sfwd_debug_send_email'] );
					}
					if ( $email ) {
						$attachments = array();
						$upload_dir = wp_upload_dir();
						$dir = $upload_dir['basedir'] . '/aiosp-log/';
						if ( wp_mkdir_p( $dir ) ) {
							$file_path = $dir . 'settings_aioseop-' . date( 'Y-m-d' ) . '-' . time() . '.ini';
							if ( ! file_exists( $file_path ) ) {
								if ( $file_handle = @fopen( $file_path, 'w' ) ) {
									global $aiosp;
									$buf = '; ' . __(
											'Settings export file for All in One SEO Pack', 'all-in-one-seo-pack'
										) . "\n";

									// Adds all settings and posts data to settings file
									add_filter( 'aioseop_export_settings_exporter_post_types', array( $this, 'get_exporter_post_types' ) );
									add_filter( 'aioseop_export_settings_exporter_choices', array( $this, 'get_exporter_choices' ) );

									$buf = $aiosp->settings_export( $buf );
									$buf = apply_filters( 'aioseop_export_settings', $buf );
									fwrite( $file_handle, $buf );
									fclose( $file_handle );
									$attachments[] = $file_path;
								}
							}
						}

						if ( wp_mail( $email, sprintf( __( 'SFWD Debug Mail From Site %s.', 'all-in-one-seo-pack' ), $siteurl ), $mail_text, '', $attachments ) ) {
							echo "<div class='sfwd_debug_mail_sent'>" . sprintf( __( 'Sent to %s.', 'all-in-one-seo-pack' ), $email ) . '</div>';
						} else {
							echo "<div class='sfwd_debug_error'>" . sprintf( __( 'Failed to send to %s.', 'all-in-one-seo-pack' ), $email ) . '</div>';
						}
					} else {
						echo "<div class='sfwd_debug_error'>" . __( 'Error: please enter an e-mail address before submitting.', 'all-in-one-seo-pack' ) . '</div>';
					}
				}
			} while ( 0 ); // Control structure for use with break.
			$buf   = "<ul class='sfwd_debug_settings'>\n{$page_text}\n</ul>\n";

			return $buf;
		}

		function get_email_input() {
			$nonce = wp_create_nonce( 'sfwd-debug-nonce' );
			$buf   = '<input name="sfwd_debug_send_email" type="text" value="" placeholder="' . __( 'E-mail debug information', 'all-in-one-seo-pack' ) . '"><input name="sfwd_debug_nonce" type="hidden" value="' .
			         $nonce . '"><input name="sfwd_debug_submit" type="submit" value="' . __( 'Submit', 'all-in-one-seo-pack' ) . '" class="button-primary">';
			return $buf;
		}

		function get_exporter_choices() {
			return array( 1, 2 );
		}

		function get_exporter_post_types() {
			$post_types = $this->get_post_type_titles();
			$rempost    = array(
				'customize_changeset' => 1,
				'custom_css'          => 1,
				'revision'            => 1,
				'nav_menu_item'       => 1,
			);
			$post_types = array_diff_key(
				$post_types,
				$rempost
			);

			return array_keys( $post_types );
		}
	}
}
