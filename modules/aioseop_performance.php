<?php
/**
 * The Performance class.
 *
 * @package All_in_One_SEO_Pack
 * @since ?
 */

if ( ! class_exists( 'All_in_One_SEO_Pack_Performance' ) ) {

	/**
	 * Class All_in_One_SEO_Pack_Performance
	 *
	 * @since ?
	 */
	class All_in_One_SEO_Pack_Performance extends All_in_One_SEO_Pack_Module {

		/**
		 * Module Info
		 *
		 * @since ?
		 *
		 * @var array $module_info
		 */
		protected $module_info = array();

		/**
		 * All_in_One_SEO_Pack_Performance constructor.
		 *
		 * @since ?
		 *
		 * @param $mod
		 */
		function __construct( $mod ) {
			/* translators: This is the title of our Performance module. */
			$this->name   = __( 'Performance', 'all-in-one-seo-pack' );
			$this->prefix = 'aiosp_performance_';
			$this->file   = __FILE__;
			parent::__construct();

			$this->default_options = array(
				'memory_limit'   => array(
					/* translators: This is the name of a setting which allows users to increase their PHP memory limit. */
					'name'            => __( 'Raise memory limit', 'all-in-one-seo-pack' ),
					'default'         => '256M',
					'type'            => 'select',
					'initial_options' => array(
						/* translators: This a dropdown value for the "Raise memory limit" setting. If this is selected, All in One SEO Pack will not override the PHP memory limit and use the default system value. */
						0      => __( 'Use the system default', 'all-in-one-seo-pack' ),
						'32M'  => '32MB',
						'64M'  => '64MB',
						'128M' => '128MB',
						'256M' => '256MB',
					),
				),
				'execution_time' => array(
					/* translators: This is the name of a setting which allows users to increase their PHP execution time limit. */
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

			global $aiosp, $aioseop_options;
			$this->default_options['force_rewrites'] = array(
				/* translators: This is the name of a setting which forces the plugin to use output buffering to rewrite the title tag in the source code. */
				'name'            => __( 'Force Rewrites', 'all-in-one-seo-pack' ),
				'default'         => 1,
				'type'            => 'radio',
				'initial_options' => array(
					1 => __( 'Enabled', 'all-in-one-seo-pack' ),
					0 => __( 'Disabled', 'all-in-one-seo-pack' ),
				),
			);

			$this->layout = array(
				'default' => array(
					'name'      => $this->name,
					'help_link' => 'https://semperplugins.com/documentation/performance-settings/',
					'options'   => array_keys( $this->default_options ),
				),
			);

			$system_status = array(
				'status'     => array(
					'default' => '',
					'type'    => 'html',
					'label'   => 'none',
					'save'    => false,
				),
				'send_email' => array(
					'default' => '',
					'type'    => 'html',
					'label'   => 'none',
					'save'    => false,
				),
			);

			$this->layout['system_status'] = array(
				/* translators: This is the header of a table in which All in One SEO Pack displays data about the user's WordPress installation and server. */
				'name'      => __( 'System Status', 'all-in-one-seo-pack' ),
				'help_link' => 'https://semperplugins.com/documentation/performance-settings/',
				'options'   => array_keys( $system_status ),
			);

			$this->default_options = array_merge( $this->default_options, $system_status );

			add_filter( $this->prefix . 'display_options', array( $this, 'display_options_filter' ), 10, 2 );
			add_filter( $this->prefix . 'update_options', array( $this, 'update_options_filter' ), 10, 2 );
			add_action( $this->prefix . 'settings_update', array( $this, 'settings_update_action' ), 10, 2 );
		}

		/**
		 * Update Options Filter
		 *
		 * @since ?
		 *
		 * @param $options
		 * @param $location
		 * @return mixed
		 */
		function update_options_filter( $options, $location ) {
			if ( null == $location && isset( $options[ $this->prefix . 'force_rewrites' ] ) ) {
				unset( $options[ $this->prefix . 'force_rewrites' ] );
			}

			return $options;
		}

		/**
		 * Display Options Filter
		 *
		 * @since ?
		 *
		 * @param $options
		 * @param $location
		 * @return mixed
		 */
		function display_options_filter( $options, $location ) {
			if ( null == $location ) {
				$options[ $this->prefix . 'force_rewrites' ] = 1;
				global $aiosp;
				$opts                                        = $aiosp->get_current_options( array(), null );
				$options[ $this->prefix . 'force_rewrites' ] = $opts['aiosp_force_rewrites'];
			}
			return $options;
		}

		/**
		 * Settings Update Action
		 *
		 * @since ?
		 *
		 * @param $options
		 * @param $location
		 */
		function settings_update_action( $options, $location ) {
			if ( null == $location && isset( $_POST[ $this->prefix . 'force_rewrites' ] ) ) {
				$force_rewrites = $_POST[ $this->prefix . 'force_rewrites' ];
				if ( ( 0 == $force_rewrites ) || ( 1 == $force_rewrites ) ) {
					global $aiosp;
					$opts                         = $aiosp->get_current_options( array(), null );
					$opts['aiosp_force_rewrites'] = $force_rewrites;
					$aiosp->update_class_option( $opts );
					wp_cache_flush();
				}
			}
		}

		/**
		 * Add Page Hooks
		 *
		 * @since ?
		 */
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

		/**
		 * Settings Page Initialization
		 *
		 * @since ?
		 */
		function settings_page_init() {
			$this->default_options['status']['default']     = $this->get_serverinfo();
			$this->default_options['send_email']['default'] = $this->get_email_input();
		}

		/**
		 * Menu Order
		 *
		 * @since ?
		 *
		 * @return int
		 */
		function menu_order() {
			return 7;
		}

		/**
		 * Get Server Info
		 *
		 * @since ?
		 *
		 * @return mixed|string|void
		 */
		function get_serverinfo() {
			global $wpdb;
			global $wp_version;

			$sqlversion = $wpdb->get_var( 'SELECT VERSION() AS version' );
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
				/* translators: "N/A" is an abbreviation for "Non Applicable". */
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
				$memory_usage = round( memory_get_usage() / 1024 / 1024, 2 ) . 'M';
			} else {
				$memory_usage = __( 'N/A', 'all-in-one-seo-pack' );
			}
			if ( is_callable( 'exif_read_data' ) ) {
				$exif = __( 'Yes', 'all-in-one-seo-pack' ) . ' ( V' . AIOSEOP_PHP_Functions::substr( phpversion( 'exif' ), 0, 4 ) . ')';
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

			// TODO Change array keys to NOT be translations. Try to use a separate array for translations.
			$debug_info = array(
				__( 'Operating System', 'all-in-one-seo-pack' ) => PHP_OS,
				__( 'Server', 'all-in-one-seo-pack' )      => $_SERVER['SERVER_SOFTWARE'],
				/* translators: "Memory" in this context refers to RAM memory. */
				__( 'Memory usage', 'all-in-one-seo-pack' ) => $memory_usage,
				/* translators: "MYSQL" is the name of a database software and should not be translated. */
				__( 'MYSQL Version', 'all-in-one-seo-pack' ) => $sqlversion,
				/* translators: "SQL" is a programming language that is used to store or retrieve data from databases and should not be translated. */
				__( 'SQL Mode', 'all-in-one-seo-pack' )    => $sql_mode,
				__( 'PHP Version', 'all-in-one-seo-pack' ) => PHP_VERSION,
				/* translators: This is a setting in the PHP interpreter of the server. Leave this untranslated if there's no proper translation for this. */
				__( 'PHP Allow URL fopen', 'all-in-one-seo-pack' ) => $allow_url_fopen,
				/* translators: "Memory" in this context refers to RAM memory. */
				__( 'PHP Memory Limit', 'all-in-one-seo-pack' ) => $memory_limit,
				__( 'PHP Max Upload Size', 'all-in-one-seo-pack' ) => $upload_max,
				__( 'PHP Max Post Size', 'all-in-one-seo-pack' ) => $post_max,
				__( 'PHP Max Script Execute Time', 'all-in-one-seo-pack' ) => $max_execute,
				/* translators: The "PHP Exif" part should not be translated. */
				__( 'PHP Exif support', 'all-in-one-seo-pack' ) => $exif,
				/* translators: The "PHP IPTC" part should not be translated. */
				__( 'PHP IPTC support', 'all-in-one-seo-pack' ) => $iptc,
				/* translators: The "PHP XML" part should not be translated. */
				__( 'PHP XML support', 'all-in-one-seo-pack' ) => $xml,
				/* translators: This is the base URL (e.g. "examplewebsite.com") of the website. */
				__( 'Site URL', 'all-in-one-seo-pack' )    => $siteurl,
				/* translators: This is the URL of the homepage (e.g. "examplewebsite.com/home") of the website. */
				__( 'Home URL', 'all-in-one-seo-pack' )    => $homeurl,
				__( 'WordPress Version', 'all-in-one-seo-pack' ) => $wp_version,
				/* translators: "DB" is an abbreviation for "Database". */
				__( 'WordPress DB Version', 'all-in-one-seo-pack' ) => $db_version,
				/* translators: "Multisite" or "WordPress Multisite" is a feature that allows users to create a network of websites. Leave this in English if there is no translation for this in your locale glossary. */
				__( 'Multisite', 'all-in-one-seo-pack' )   => $ms,
				__( 'Active Theme', 'all-in-one-seo-pack' ) => $theme['Name'] . ' ' . $theme['Version'],
				__( 'Site Title', 'all-in-one-seo-pack' )  => $site_title,
				__( 'Site Language', 'all-in-one-seo-pack' ) => $language,
				/* translators: This is a label that shows what page is used as the homepage/front page. */
				__( 'Front Page Displays', 'all-in-one-seo-pack' ) => 'page' === $front_displays ? $front_displays . ' [ID = ' . $page_on_front . ']' : $front_displays,
				__( 'Search Engine Visibility', 'all-in-one-seo-pack' ) => $blog_public,
				/* translators: This is a label that shows what the current permalink structure is. The permalink structure is the way that the URLs of the website are formatted, e.g. "examplesite.com/?p=123" or "examplesite.com/1970/01/01/sample-post/". */
				__( 'Permalink Setting', 'all-in-one-seo-pack' ) => $perm_struct,
			);
			$debug_info[ __( 'Active Plugins', 'all-in-one-seo-pack' ) ] = null;
			$active_plugins   = array();
			$inactive_plugins = array();
			$plugins          = get_plugins();
			foreach ( $plugins as $path => $plugin ) {
				if ( is_plugin_active( $path ) ) {
					$debug_info[ $plugin['Name'] ] = $plugin['Version'];
				} else {
					$inactive_plugins[ $plugin['Name'] ] = $plugin['Version'];
				}
			}

			$debug_key                = __( 'Inactive Plugins', 'all-in-one-seo-pack' );
			$debug_info[ $debug_key ] = null;
			$debug_info               = array_merge( $debug_info, (array) $inactive_plugins );

			/* translators: "%s" is a placeholder so it should not be translated. It will be replaced with the name of the premium version of the plugin, All in One SEO Pack Pro. */
			$mail_text = sprintf( __( '%s Debug Info', 'all-in-one-seo-pack' ), 'All in One SEO Pack Pro' ) . "\r\n------------------\r\n\r\n";
			$page_text = '';
			if ( ! empty( $debug_info ) ) {
				foreach ( $debug_info as $name => $value ) {
					if ( null !== $value ) {
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
						echo "<div class='sfwd_debug_error'>" .
						/* translators: This message is shown when a form could not be submitted due to a verification error (e.g. when a field is required and is still blank). */
						__( 'Form submission error: verification check failed.', 'all-in-one-seo-pack' )
						. '</div>';
						break;
					}
					$email = '';
					if ( ! empty( $_REQUEST['sfwd_debug_send_email'] ) ) {
						$email = sanitize_email( $_REQUEST['sfwd_debug_send_email'] );
					}
					if ( $email ) {
						$attachments = array();
						$upload_dir  = wp_upload_dir();
						$dir         = $upload_dir['basedir'] . '/aiosp-log/';
						if ( wp_mkdir_p( $dir ) ) {
							$file_path = $dir . 'settings_aioseop-' . date( 'Y-m-d' ) . '-' . time() . '.ini';
							if ( ! file_exists( $file_path ) ) {
								// @codingStandardsIgnoreStart
								if ( $file_handle = @fopen( $file_path, 'w' ) ) {
								// @codingStandardsIgnoreEnd
									global $aiosp;
									$buf = '; ' . sprintf(
										/* translators: %s is a placeholder so it should not be translated. It will be replaced with the name of the plugin, All in One SEO Pack. */
										__( 'Settings export file for %s', 'all-in-one-seo-pack' ),
										AIOSEOP_PLUGIN_NAME
									) . "\n";

									// Adds all settings and posts data to settings file.
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

						/* translators: %s is a placeholder and should not be translated. It will be replaced with the URL of the website. Also, "SFWD" is an abbreviation for our business name and shouldn't be translated either. */
						if ( wp_mail(
							$email,
							sprintf( __( 'SFWD Debug Mail From Site %s.', 'all-in-one-seo-pack' ), $siteurl ),
							$mail_text,
							'',
							$attachments
						) ) {
							echo "<div class='sfwd_debug_mail_sent'>" .
							/* translators: %s is a placeholder and should not be translated. It will be replaced with an e-mail address. */
							sprintf( __( 'Sent to %s.', 'all-in-one-seo-pack' ), $email ) . '</div>';
						} else {
							echo "<div class='sfwd_debug_error'>" .
							/* translators: %s is a placeholder and should not be translated. It will be replaced with an e-mail address. */
							sprintf( __( 'Failed to send to %s.', 'all-in-one-seo-pack' ), $email ) . '</div>';
						}
					} else {
						echo "<div class='sfwd_debug_error'>" . __( 'Error: please enter an e-mail address before submitting.', 'all-in-one-seo-pack' ) . '</div>';
					}
				}
			} while ( 0 ); // Control structure for use with break.
			$buf = "<ul class='sfwd_debug_settings'>\n{$page_text}\n</ul>\n";

			return $buf;
		}

		/**
		 * Get Email Input
		 *
		 * @since ?
		 *
		 * @return string
		 */
		function get_email_input() {
			$nonce = wp_create_nonce( 'sfwd-debug-nonce' );
			$buf   =
				'<input name="sfwd_debug_send_email" type="text" value="" placeholder="' .
				/* translators: This is the text of a button that can be clicked. Therefore, "E-mail" is used as a verb in this context. */
				__( 'E-mail debug information', 'all-in-one-seo-pack' ) . '" aria-label="' . __( 'Enter the email address provided by Semper Plugins Support to send your debug information', 'all-in-one-seo-pack' ) . '">' .
				'<input name="sfwd_debug_nonce" type="hidden" value="' . $nonce . '">' .
				'<input name="sfwd_debug_submit" type="submit" value="' . __( 'Submit', 'all-in-one-seo-pack' ) . '" class="button-primary">';
			return $buf;
		}

		/**
		 * Get Exporter Choices
		 *
		 * @since 2.3.13
		 *
		 * @return array
		 */
		function get_exporter_choices() {
			return array( 1, 2 );
		}

		/**
		 * Get Exporter Post Types
		 *
		 * @since 2.3.13
		 *
		 * @return array
		 */
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
