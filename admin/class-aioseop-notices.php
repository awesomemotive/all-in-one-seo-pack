<?php
/**
 * AIOSEOP Notice API: AIOSEOP Notice Class
 *
 * Handles adding, updating, and removing notices. Then handles activating or
 * deactivating those notices site-wide or user based.
 *
 * @link https://wordpress.org/plugins/all-in-one-seo-pack/
 *
 * @package All-in-One-SEO-Pack
 * @since 2.4.2
 */

if ( ! class_exists( 'AIOSEOP_Notice' ) ) {
	/**
	 * AIOSEOP Notice.
	 *
	 * Admin notices for AIOSEOP.
	 *
	 * @since 2.4.2
	 */
	class AIOSEOP_Notices {
		/**
		 * Collection of notices to display.
		 *
		 * @since 2.4.2
		 * @access public
		 *
		 * @var array $notices {
		 *     @type array $slug {
		 *         @type string $slug        Required and is a unique ID.
		 *         @type int    $delay_time  Amount of time to begin showing message.
		 *         @type string $message     Content message to display in the container.
		 *         @type array  $delay_option {
		 *         Show options for users to click on. Default: See self::delay_option_defaults().
		 *             @type array {
		 *                 @type int     $time    Optional. The amount of time to delay. Zero immediately displays Default: 0.
		 *                 @type string  $text    Optional. Button/Link HTML text to display. Default: ''.
		 *                 @type string  $class   Optional. Class names to add to the link/button for styling. Default: ''.
		 *                 @type string  $link    Optional. The elements href source/link. Default: '#'.
		 *                 @type boolean $dismiss Optional. Variable for AJAX to dismiss showing a notice.
		 *             }
		 *         }
		 *         @type int    $time_start  The time the notice was added to the object.
		 *         @type string $target      Shows based on site-wide or user notice data.
		 *         @type array  $screens     Which screens to exclusively display the notice on.
		 *                                   An empty array() = all,
		 *                                   array('aioseop') = $this->aioseop_screens,
		 *                                   and array('CUSTOM') = specific screen(s).
		 *                                   Default: array().
		 *     }
		 * }
		 */
		public $notices = array();

		/**
		 * List of notice slugs that are currently active.
		 *
		 * @since 2.4.2
		 * @access public
		 *
		 * @var array $active_notices {
		 *     @type string/int $slug => $display_time Contains the current active notices
		 *                                             that are scheduled to be displayed.
		 * }
		 */
		public $active_notices = array();

		/**
		 * Collection of footers saved to AIOSEOP_Notice database. Footers are
		 * shown if they are active, and are deactivated in self::ajax_footer_action().
		 *
		 * @since 2.4.2
		 *
		 * @var array $footers {
		 *     @type array $slug {
		 *         @type string $slug        Required and is a unique ID.
		 *         @type int    $delay_time  Amount of time to begin showing message.
		 *         @type string $html        Content HTML to rendered in the container.
		 *         @type array  $action_option {
		 *         Show options for users to click on. Default: See self::delay_option_defaults().
		 *             @type array {
		 *                 @type int     $time    Optional. The amount of time to delay. Zero immediately displays Default: 0.
		 *                 @type string  $text    Optional. Button/Link HTML text to display. Default: ''.
		 *                 @type string  $class   Optional. Class names to add to the link/button for styling. Default: ''.
		 *                 @type string  $link    Optional. The elements href source/link. Default: '#'.
		 *                 @type boolean $dismiss Optional. Variable for AJAX to dismiss showing a notice.
		 *             }
		 *         }
		 *         @type int    $layer_level When multiple footers exist, the highest number is shown.
		 *         @type int    $time_start  The time the notice was added to the object.
		 *         @type array  $screens     Which screens to exclusively display the notice on.
		 *                                   An empty array() = all,
		 *                                   array('aioseop') = $this->aioseop_screens,
		 *                                   and array('CUSTOM') = specific screen(s).
		 *                                   Default: array().
		 *     }
		 * }
		 */
		public $footers = array();

		/**
		 * The default dismiss time. An anti-nag setting.
		 *
		 * @var int $default_dismiss_delay
		 */
		private $default_dismiss_delay = 120;

		/**
		 * List of Screens used in AIOSEOP.
		 *
		 * @since 2.4.2
		 *
		 * @var array $aioseop_screens {
		 *     @type string Screen ID.
		 * }
		 */
		private $aioseop_screens = array(
			'toplevel_page_all-in-one-seo-pack/aioseop_class',
			'all-in-one-seo_page_all-in-one-seo-pack/modules/aioseop_performance',
			'all-in-one-seo_page_all-in-one-seo-pack/modules/aioseop_sitemap',
			'all-in-one-seo_page_aiosp_opengraph',
			'all-in-one-seo_page_aiosp_robots_generator',
			'all-in-one-seo_page_all-in-one-seo-pack/modules/aioseop_file_editor',
			'all-in-one-seo_page_all-in-one-seo-pack/modules/aioseop_importer_exporter',
			'all-in-one-seo_page_all-in-one-seo-pack/modules/aioseop_bad_robots',
			'all-in-one-seo_page_all-in-one-seo-pack/modules/aioseop_feature_manager',
		);

		/**
		 * __constructor.
		 *
		 * @since 2.4.2
		 */
		public function __construct() {
			if ( is_admin() ) {
				if ( ! AIOSEOPPRO ) {
					$this->obj_load_options();

					add_action( 'admin_init', array( $this, 'init' ) );
					add_action( 'current_screen', array( $this, 'admin_screen' ) );
				}
			}
		}

		/**
		 * Early operations required by the plugin.
		 *
		 * AJAX requires being added early before screens have been loaded.
		 *
		 * @since 2.4.2
		 */
		public function init() {
			add_action( 'wp_ajax_aioseop_notice', array( $this, 'ajax_notice_action' ) );
			add_action( 'wp_ajax_aioseop_footer', array( $this, 'ajax_footer_action' ) );
		}

		/**
		 * Setup/Init Admin Screen
		 *
		 * Adds the initial actions to WP based on the Admin Screen being loaded.
		 * The AIOSEOP and Other Screens have separate methods that are used, and
		 * additional screens can be made exclusive/unique.
		 *
		 * @since 2.4.2
		 *
		 * @param WP_Screen $current_screen The current screen object being loaded.
		 */
		public function admin_screen( $current_screen ) {
			$this->deregister_scripts();
			if ( in_array( $current_screen->id, $this->aioseop_screens, true ) && isset( $current_screen->id ) ) {
				// AIOSEO Notice Content.
				add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
				add_action( 'all_admin_notices', array( $this, 'display_notice_aioseop' ) );
				add_action( 'admin_footer_text', array( $this, 'display_footer' ) );
			} elseif ( isset( $current_screen->id ) ) {
				// Default WP Notice.
				add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
				add_action( 'all_admin_notices', array( $this, 'display_notice_default' ) );
			}
		}

		/**
		 * Load AIOSEOP_Notice Options
		 *
		 * Gets the options for AIOSEOP_Notice to set its variables to.
		 *
		 * @since 2.4.2
		 * @access private
		 *
		 * @see self::notices
		 * @see self::active_notices
		 */
		private function obj_load_options() {
			$notices_options = $this->obj_get_options();

			$this->notices        = $notices_options['notices'];
			$this->active_notices = $notices_options['active_notices'];
			$this->footers        = $notices_options['footers'];
		}

		/**
		 * Get AIOSEOP_Notice Options
		 *
		 * @since 2.4.2
		 * @access private
		 *
		 * @return array
		 */
		private function obj_get_options() {
			$defaults = array(
				'notices'        => array(),
				'active_notices' => array(),
				'footers'        => array(),
			);

			$notices_options = get_option( 'aioseop_notices' );
			if ( false === $notices_options ) {
				return $defaults;
			}

			return wp_parse_args( $notices_options, $defaults );
		}

		/**
		 * Update Notice Options
		 *
		 * @since 2.4.2
		 * @access private
		 *
		 * @return boolean True if successful, using update_option() return value.
		 */
		private function obj_update_options() {
			$notices_options     = array(
				'notices'        => $this->notices,
				'active_notices' => $this->active_notices,
				'footers'        => $this->footers,
			);
			$old_notices_options = $this->obj_get_options();
			$notices_options     = wp_parse_args( $notices_options, $old_notices_options );

			return update_option( 'aioseop_notices', $notices_options );
		}

		/**
		 * Notice Default Values
		 *
		 * Returns the default value for a variable to be used in self::notices[].
		 *
		 * @since 2.4.2
		 *
		 * @return array Notice variable in self::notices.
		 */
		public function notice_defaults() {
			return array(
				'slug'          => '',
				'delay_time'    => 0,
				'message'       => '',
				'delay_options' => array(),
				'target'        => 'site',
				'screen'        => array(),
				'time_start'    => time(),
			);
		}

		/**
		 * Delay Options Default Values
		 *
		 * Returns the default value for delay_options in self::notices[$slug]['delay_options'].
		 *
		 * @since 2.4.2
		 *
		 * @return array Delay_Options variable in self::notices[$slug]['delay_options'].
		 */
		public function delay_options_defaults() {
			return array(
				'time'    => 0,
				'text'    => __( 'Dismiss', 'all-in-one-seo-pack' ),
				'link'    => '#',
				'dismiss' => true,
				'class'   => '',
			);
		}

		/**
		 * Set Notice Delay Options
		 *
		 * Sets the Delay Options in a Notice.
		 *
		 * @since 2.4.2
		 * @access private
		 *
		 * @see self::insert_notice()
		 * @see self::update_notice()
		 *
		 * @param array $delay_options New delay options to be added/updated.
		 * @return array Delay Options with new values added to old.
		 */
		private function set_delay_options( $delay_options ) {
			if ( empty( $delay_options ) && ! is_array( $delay_options ) ) {
				return array(
					[ 0 ] => $this->delay_options_defaults(),
				);
			}

			$rtn_delay_options = array();
			foreach ( $delay_options as $delay_option ) {
				$tmp_delay_o = $this->delay_options_defaults();

				// Button Delay Time.
				$tmp_delay_o['time'] = $this->default_dismiss_delay;
				if ( isset( $delay_option['time'] ) ) {
					$tmp_delay_o['time'] = $delay_option['time'];
				}

				// Button Text.
				if ( isset( $delay_option['text'] ) && ! empty( $delay_option['text'] ) ) {
					$tmp_delay_o['text'] = $delay_option['text'];
				}

				// Link.
				if ( isset( $delay_option['link'] ) && ! empty( $delay_option['link'] ) ) {
					$tmp_delay_o['link'] = $delay_option['link'];
				}

				// Dismiss.
				if ( isset( $delay_option['dismiss'] ) ) {
					$tmp_delay_o['dismiss'] = $delay_option['dismiss'];
				}

				// Class.
				if ( isset( $delay_option['class'] ) && ! empty( $delay_option['class'] ) ) {
					$tmp_delay_o['class'] = $delay_option['class'];
				}

				$rtn_delay_options[] = $tmp_delay_o;
			}

			return $rtn_delay_options;
		}

		/**
		 * Insert Notice
		 *
		 * Initial insert for a Notice and Activates it. Used strictly for adding notices
		 * when no updating or modifications is intended.
		 *
		 * @since 2.4.2
		 *
		 * @uses self::activate_notice() Used to initialize a notice.
		 *
		 * @param type $slug          Notice unique slug.
		 * @param type $delay_time    Time to begin displaying notice site-wide.
		 * @param type $message       Message, or notice body, to display to user. HTML can be used.
		 * @param type $delay_options See self::notices for more details. Contains the buttons/options to display to the user.
		 * @param type $target        Target is either the site as a whole, or based on each user.
		 * @param type $screens       Which screens to exclusively load the notice on. 'aioseop' = all aioseop screens. Default empty is all screens.
		 * @return boolean True on success.
		 */
		public function insert_notice( $slug, $delay_time = 0, $message = '', $delay_options = array(), $target = 'site', $screens = array() ) {
			if ( empty( $slug ) ) {
				return false;
			} elseif ( isset( $this->notices[ $slug ] ) ) {
				return false;
			}

			$notice = array(
				'slug'          => sanitize_key( $slug ),
				'delay_time'    => intval( $delay_time ),
				'message'       => $message,
				'delay_options' => array(),
				'target'        => $target,
				'screens'       => array(),
			);

			$notice['delay_options'] = $this->set_delay_options( $delay_options );
			if ( is_array( $screens ) ) {
				$notice['screens'] = $screens;
			}

			$this->notices[ $slug ] = $notice;
			$this->obj_update_options();
			$this->activate_notice( $slug );

			return true;
		}

		/**
		 * Update Notice
		 *
		 * Updates an existing Notice without resetting it. Used when modifying
		 * any existing notices without disturbing its set environment/timeline.
		 *
		 * @since 2.4.2
		 *
		 * @param type $slug          Notice unique slug.
		 * @param type $delay_time    Time to begin displaying notice site-wide.
		 * @param type $message       Message, or notice body, to display to user. HTML can be used.
		 * @param type $delay_options See self::notices for more details. Contains the buttons/options to display to the user.
		 * @param type $target        Target is either the site as a whole, or based on each user.
		 * @param type $screens       Which screens to exclusively load the notice on. 'aioseop' = all aioseop screens. Default empty is all screens.
		 * @return boolean True on success.
		 */
		public function update_notice( $slug, $delay_time = 0, $message = '', $delay_options = array(), $target = 'site', $screens = array() ) {
			if ( empty( $slug ) ) {
				return false;
			} elseif ( ! isset( $this->notices[ $slug ] ) ) {
				return false;
			}

			$notice = array(
				'slug'          => sanitize_key( $slug ),
				'delay_time'    => intval( $delay_time ),
				'message'       => $message,
				'delay_options' => array(),
				'target'        => $target,
				'screens'       => array(),
			);

			$notice['delay_options'] = $this->set_delay_options( $delay_options );
			if ( is_array( $screens ) ) {
				$notice['screens'] = $screens;
			}

			$this->notices[ $slug ] = $notice;
			$this->obj_update_options();
			// DO NOT use activate. This is intended to update pre-existing data.
			//$this->activate_notice( $slug );

			return true;
		}

		/**
		 * Used strictly for any notices that are deprecated/obsolete. To stop notices,
		 * use notice_deactivate().
		 *
		 * @since 2.4.2
		 *
		 * @param string $slug Unique notice slug.
		 * @return boolean True if successfully removed.
		 */
		public function remove_notice( $slug ) {
			if ( isset( $this->notices[ $slug ] ) ) {
				unset( $this->notices[ $slug ] );
				$this->obj_update_options();
				return true;
			}

			return false;
		}

		/**
		 * Activate Notice
		 *
		 * Activates a notice, or Re-activates with a new display time. Used after
		 * updating a notice that requires a hard reset.
		 *
		 * @since 2.4.2
		 *
		 * @param string $slug Notice slug.
		 */
		public function activate_notice( $slug ) {
			$display_time = time() + $this->notices[ $slug ]['delay_time'];

			$this->active_notices[ $slug ] = $display_time;
			$this->obj_update_options();
		}

		/**
		 * Deactivate Notice
		 *
		 * Deactivates a notice set as active and completely removes it from the
		 * list of active notices. Used to prevent conflicting notices that may be
		 * active at any given point in time.
		 *
		 * @since 2.4.2
		 *
		 * @param string $slug Notice slug.
		 * @return boolean
		 */
		public function deactivate_notice( $slug ) {
			if ( ! isset( $this->active_notices[ $slug ] ) ) {
				return false;
			} elseif ( ! isset( $this->notices[ $slug ] ) ) {
				return false;
			}

			$this->notices[ $slug ]['active'] = false;
			unset( $this->active_notices[ $slug ] );
			$this->obj_update_options();

			return true;
		}

		/**
		 * Footer Defaults
		 *
		 * Returns a default variable array for in item self::footers.
		 *
		 * @since 2.4.2
		 *
		 * @return array
		 */
		public function footer_defaults() {
			return array(
				'slug'           => '',
				'delay_time'     => 0,
				'html'           => '',
				'action_options' => array(),
				'layer_level'    => 10,
				'screens'        => array(),

				'time_start'     => time(),
				'active'         => true,
			);
		}

		/**
		 * Insert Footer
		 *
		 * Adds a footer with a delay time, and is shown until deactivated via AJAX.
		 *
		 * @since 2.4.2
		 *
		 * @param type $slug           Notice unique slug.
		 * @param type $delay_time     Time to begin displaying notice site-wide.
		 * @param type $html           HTML to display to user(s).
		 * @param type $action_options See self::footers for more details. Contains the buttons/options to display to the user.
		 * @param type $layer_level    Highest level is shown to the user(s).
		 * @param type $screens        Which screens to exclusively load the notice on. 'aioseop' = all aioseop screens. Default empty is all screens.
		 * @return boolean True on success.
		 */
		public function insert_footer( $slug, $delay_time = 0, $html, $action_options, $layer_level = 10, $screens = array() ) {
			if ( empty( $slug ) ) {
				return false;
			} elseif ( isset( $this->footers[ $slug ] ) ) {
				return false;
			}

			$new_footer = $this->footer_defaults();

			$new_footer['slug']        = $slug;
			$new_footer['delay_time']  = $delay_time;
			$new_footer['html']        = $html;
			$new_footer['layer_level'] = $layer_level;
			$new_footer['screens']     = $screens;

			$tmp_action_options           = $this->set_delay_options( $action_options );
			$new_footer['action_options'] = $tmp_action_options;

			$this->footers[ $slug ] = $new_footer;
			$this->obj_update_options();

			return true;
		}

		/**
		 * Remove Footer
		 *
		 * Used for removing any footers that may exist.
		 *
		 * @since 2.4.2
		 *
		 * @param string $slug The slug of an item in self::footers.
		 * @return boolean True on success.
		 */
		public function remove_footer( $slug ) {
			if ( empty( $slug ) || ! isset( $this->footers[ $slug ] ) ) {
				return false;
			}

			unset( $this->footers[ $slug ] );
			$this->obj_update_options();

			return true;
		}

		/*** DISPLAY Methods **************************************************/
		/**
		 * Deregister Scripts
		 *
		 * Initial Admin Screen action to remove aioseop script(s) from all screens;
		 * which will be registered if executed on screen.
		 * NOTE: As of 2.4.2, most of it is default layout, styling, & scripting
		 * that is loaded on all pages. Which can later be different.
		 *
		 * @since 2.4.2
		 * @access private
		 *
		 * @see self::admin_screen()
		 */
		private function deregister_scripts() {
			wp_deregister_script( 'aioseop-admin-notices' );
		}

		/**
		 * (Register) Enqueue Scripts
		 *
		 * Used to register, enqueue, and localize any JS data. Styles can later be added.
		 *
		 * @since 2.4.2
		 *
		 * @param string $hook_suffix The current Admin page.
		 */
		public function enqueue_scripts( $hook_suffix ) {
			wp_register_script(
				'aioseop-admin-notice',
				AIOSEOP_PLUGIN_URL . 'js/admin-notice.js',
				array(),
				AIOSEOP_VERSION,
				true
			);
			wp_enqueue_script( 'aioseop-admin-notice' );

			// JS Localization.
			$notice_delays = array();
			foreach ( $this->active_notices as $notice_slug => $notice_display_time ) {
				foreach ( $this->notices[ $notice_slug ]['delay_options'] as $delay_index => $delay_arr ) {
					$notice_delays[ $notice_slug ][] = $delay_index;
				}
			}

			$footers = array();
			foreach ( $this->footers as $footer_slug => $footer ) {
				$footers[ $footer_slug ] = array();
				foreach ( $footer['action_options'] as $action_index => $action_arr ) {
					$footers[ $footer_slug ][] = $action_index;
				}
			}

			$admin_notice_localize = array(
				'notice_nonce'  => wp_create_nonce( 'aioseop_ajax_notice' ),
				'notice_delays' => $notice_delays,
				'footer_nonce'  => wp_create_nonce( 'aioseop_ajax_footer' ),
				'footers'       => $footers,
			);
			wp_localize_script( 'aioseop-admin-notice', 'aioseop_notice_data', $admin_notice_localize );
		}

		/**
		 * Display Notice as Default
		 *
		 * Method for default WP Admin notices.
		 * NOTE: As of 2.4.2, display_notice_default() & display_notice_aioseop()
		 * have the same functionality, but serves as a future development concept.
		 *
		 * @since 2.4.2
		 *
		 * @uses AIOSEOP_PLUGIN_DIR . 'admin/display/notice-default.php' Template for default notices.
		 *
		 * @return void
		 */
		public function display_notice_default() {
			if ( AIOSEOPPRO ) {
				return;
			}

			$current_screen = get_current_screen();

			$notice_show     = true;
			$current_user_id = get_current_user_id();
			foreach ( $this->active_notices as $a_notice_slug => $a_notice_time_display ) {
				if ( ! empty( $this->notices[ $a_notice_slug ]['screens'] ) ) {
					if ( ! in_array( 'aioseop', $this->notices[ $a_notice_slug ]['screens'], true ) ) {
						if ( ! in_array( $current_screen->id, $this->notices[ $a_notice_slug ]['screens'], true ) ) {
							continue;
						}
					}
				}

				if ( 'user' === $this->notices[ $a_notice_slug ]['target'] ) {
					$user_dismissed = get_user_meta( $current_user_id, 'aioseop_notice_dismissed_' . $a_notice_slug, true );
					if ( ! $user_dismissed ) {
						$a_notice_time_display = get_user_meta( $current_user_id, 'aioseop_notice_display_time_' . $a_notice_slug, true );
					} else {
						$noticed_show = false;
					}
				}
				if ( time() > $a_notice_time_display && $notice_show ) {
					$notice = $this->notices[ $a_notice_slug ];
					include( AIOSEOP_PLUGIN_DIR . 'admin/display/notice-default.php' );
				}
			}
		}

		/**
		 * Display Notice as AIOSEOP Screens
		 *
		 * Method for Admin notices exclusive to AIOSEOP screens.
		 * NOTE: As of 2.4.2, display_notice_default() & display_notice_aioseop()
		 * have the same functionality, but serves as a future development concept.
		 *
		 * @since 2.4.2
		 *
		 * @uses AIOSEOP_PLUGIN_DIR . 'admin/display/notice-aioseop.php' Template for notices.
		 *
		 * @return void
		 */
		public function display_notice_aioseop() {
			if ( AIOSEOPPRO ) {
				return;
			}

			$current_screen = get_current_screen();

			$notice_show     = true;
			$current_user_id = get_current_user_id();
			foreach ( $this->active_notices as $a_notice_slug => $a_notice_time_display ) {
				if ( ! empty( $this->notices[ $a_notice_slug ]['screens'] ) ) {
					if ( ! in_array( 'aioseop', $this->notices[ $a_notice_slug ]['screens'], true ) ) {
						if ( ! in_array( $current_screen->id, $this->notices[ $a_notice_slug ]['screens'], true ) ) {
							continue;
						}
					}
				}

				if ( 'user' === $this->notices[ $a_notice_slug ]['target'] ) {
					$user_dismissed = get_user_meta( $current_user_id, 'aioseop_notice_dismissed_' . $a_notice_slug, true );
					if ( ! $user_dismissed ) {
						$a_notice_time_display = get_user_meta( $current_user_id, 'aioseop_notice_display_time_' . $a_notice_slug, true );
					} else {
						$noticed_show = false;
					}
				}
				if ( time() > $a_notice_time_display && $notice_show ) {
					$notice = $this->notices[ $a_notice_slug ];
					include( AIOSEOP_PLUGIN_DIR . 'admin/display/notice-aioseop.php' );
				}
			}
		}

		/**
		 * Display Footer
		 *
		 * Renders the appropriate footer with the highest layer level. As well
		 * as any other footer configurations.
		 *
		 * @since 2.4.2
		 *
		 * @param string $footer_text The current footer text to be displayed.
		 * @return string
		 */
		public function display_footer( $footer_text ) {
			$current_screen = get_current_screen();
			$rtn_footer = $footer_text;

			// Screens.
			$tmp_footer = array();
			foreach ( $this->footers as $footer_slug => $footer ) {
				if ( ! empty( $footer['screens'] ) ) {
					if ( ! in_array( 'aioseop', $footer['screens'], true ) ) {
						if ( ! in_array( $current_screen->id, $footer['screens'], true ) ) {
							continue;
						}
					}
				}

				$tmp_footer[] = $footer;
			}

			// Highest Layer Level & Display Time.
			$layer = 0;
			$tmp2_footer = array();
			foreach ( $tmp_footer as $footer ) {
				$footer_display_time = $footer['time_start'] + $footer['delay_time'];
				if ( $layer < $footer['layer_level'] && time() > $footer_display_time && $footer['active'] ) {
					if ( ! empty( $tmp2_footer ) && $layer > $tmp2_footer['layer_level'] ) {
						continue;
					}

					$tmp2_footer = $footer;
				}
			}

			// AJAX Action Links.
			if ( ! empty( $tmp2_footer['action_options'] ) ) {
				$vsprint_arr = array();
				foreach ( $tmp2_footer['action_options'] as $key => $action_options ) {
					$vsprint_arr[] = '<a href="' . $action_options['link'] . '" id="aioseop-footer-action-' . $key . '" class="aioseop-footer-action-' . $key . ' ' . $action_options['class'] . '" target="_blank" rel="noopener">' . $action_options['text'] . '</a>';
				}
			}

			if ( ! empty( $tmp2_footer['html'] ) ) {
				$rtn_footer  = '';
				$rtn_footer .= '<div class="aioseop-footer-container aioseop-footer-' . $tmp2_footer['slug'] . '">';
				$rtn_footer .= vsprintf( $tmp2_footer['html'], $vsprint_arr );
				$rtn_footer .= '</div>';
			}

			return $rtn_footer;
		}

		/**
		 * AJAX Notice Action
		 *
		 * Fires when a Delay_Option is clicked and sent via AJAX. Also includes
		 * WP Default Dismiss (rendered as a clickable button on upper-right).
		 *
		 * @since 2.4.2
		 *
		 * @see AIOSEOP_PLUGIN_DIR . 'js/admin-notice.js'
		 */
		public function ajax_notice_action() {
			check_ajax_referer( 'aioseop_ajax_notice' );
			// Notice (Slug) => (Delay_Options) Index.
			if ( isset( $_POST['notice_slug'] ) ) {
				$notice_slug = filter_input( INPUT_POST, 'notice_slug', FILTER_SANITIZE_STRING );
			}
			if ( isset( $_POST['delay_index'] ) ) {
				$delay_index = filter_input( INPUT_POST, 'delay_index', FILTER_SANITIZE_STRING );
			}

			$delay_options = $this->delay_options_defaults();

			$delay_options['dismiss'] = false;
			if ( isset( $this->notices[ $notice_slug ]['delay_options'][ $delay_index ] ) ) {
				$delay_options = $this->notices[ $notice_slug ]['delay_options'][ $delay_index ];
			}
			// User Notices or Sitewide.
			if ( 'user' === $this->notices[ $notice_slug ]['target'] ) {
				$current_user_id = get_current_user_id();
				if ( $delay_options['time'] ) {
					$metadata = time() + $delay_options['time'];
					add_user_meta( $current_user_id, 'aioseop_notice_display_time_' . $notice_slug, $metadata );
				}
				if ( $delay_options['dismiss'] ) {

					add_user_meta( $current_user_id, 'aioseop_notice_dismissed_' . $notice_slug, $delay_options['dismiss'] );
				}
			} else {
				if ( $delay_options['time'] ) {
					$this->active_notices[ $notice_slug ] = time() + $delay_options['time'];
				}

				if ( $delay_options['dismiss'] ) {
					$this->deactivate_notice( $notice_slug );
				}
			}

			$this->obj_update_options();
			wp_die();
		}

		/**
		 * AJAX Footer Action
		 *
		 * Fires when a Action_Option is clicked and sent via AJAX.
		 *
		 * @since 2.4.2
		 *
		 * @see AIOSEOP_PLUGIN_DIR . 'js/admin-notice.js'
		 */
		public function ajax_footer_action() {
			check_ajax_referer( 'aioseop_ajax_footer' );

			if ( isset( $_POST['footer_slug'] ) ) {
				$footer_slug = filter_input( INPUT_POST, 'footer_slug', FILTER_SANITIZE_STRING );
			} else {
				wp_die();
			}
			if ( isset( $_POST['action_index'] ) ) {
				$action_index = filter_input( INPUT_POST, 'action_index', FILTER_SANITIZE_STRING );
				$action_index = intval( $action_index );
			} else {
				wp_die();
			}

			$action_options = $this->delay_options_defaults();

			$action_options['dismiss'] = false;
			if ( isset( $this->footers[ $footer_slug ]['action_options'][ $action_index ] ) ) {
				$action_options = $this->footers[ $footer_slug ]['action_options'][ $action_index ];
			}

			if ( $action_options['time'] ) {
				$this->footers[ $footer_slug ]['time_start'] = time();
				$this->footers[ $footer_slug ]['delay_time'] = $action_options['time'];
			}

			$rtn_data = array(
				'footer_slug' => $footer_slug,
				'dismissed'   => false,
			);
			if ( $action_options['dismiss'] ) {
				$this->footers[ $footer_slug ]['active'] = false;

				$rtn_data['dismissed'] = true;
			}

			$this->obj_update_options();
			echo wp_json_encode( $rtn_data );
			wp_die();
		}

	}
	// CLASS INITIALIZATION.
	// Should this be a singleton class instead of a global?
	global $aioseop_notices;
	$aioseop_notices = new AIOSEOP_Notices();

	/***************************************************************************
	 *** NON-CLASS FUNCTIONS ***************************************************
	 ***************************************************************************/

	/**
	 * First concept. Primarily for Dev Purposes.
	 *
	 * @global AIOSEOP_Notices $aioseop_notices
	 */
	function aioseop_activation_review_plugin_set_notice() {
		global $aioseop_notices;

		$slug            = 'activation_review_plugin';
		$delay_time      = 0;//864000;// 10 days. // CHANGE AFTER DEV TESTS.
		$message         = __( 'Looks like you\'ve been using All in One SEO Plugin for awhile now, and that\'s awesome! We are an open source community built from other\'s contributions. By helping us with a 5-star review, it also helps us to reach out to more people.', 'all-in-one-seo-pack' );
		$delay_options   = array();
		$delay_options[] = array(
			'time'    => 15,
			'text'    => __( 'Yes, absolutely!', 'all-in-one-seo-pack' ),
			'link'    => 'https://wordpress.org/support/plugin/all-in-one-seo-pack/reviews?rate=5#new-post',
			'dismiss' => false,
			'class'   => '',
		);
		$delay_options[] = array(
			'text'    => 'Remind me in an Hour',
			'time'    => 3600,
			'dismiss' => false,
			'class'   => '',
		);
		$delay_options[] = array(
			'text'    => 'Maybe, give me a Week.',
			'time'    => 432000,// 5 days.
			'dismiss' => false,
			'class'   => '',
		);
		$delay_options[] = array(
			'time'    => 0,
			'text'    => 'Already Rated. Thank You!',
			'dismiss' => true,
			'class'   => '',
		);
		$target          = 'user';
		$screen          = array();

		// Methods of adding Notices.
		$update = true;
		$reset  = true;
		if ( $aioseop_notices->insert_notice( $slug, $delay_time, $message, $delay_options, $target, $screen ) ) {
			aioseop_footer_review();
		} elseif ( $update ) {
			$aioseop_notices->update_notice( $slug, $delay_time, $message, $delay_options, $target, $screen );

			if ( $reset ) {
				$aioseop_notices->activate_notice( $slug );
				aioseop_footer_review_remove();
				aioseop_footer_review();
			}
		}
	}

	/**
	 * Remove Footer Review Notice
	 *
	 * Concept for removing review footer.
	 *
	 * @see aioseop_activation_review_plugin_set_notice()
	 *
	 * @global AIOSEOP_Notices $aioseop_notices
	 */
	function aioseop_footer_review_remove() {
		global $aioseop_notices;
		$slug = 'activation_review';
		$aioseop_notices->remove_footer( $slug );
	}

	/**
	 * Footer Review Notices
	 *
	 * Concept for footer.
	 *
	 * @see aioseop_activation_review_plugin_set_notice()
	 *
	 * @global AIOSEOP_Notices $aioseop_notices
	 */
	function aioseop_footer_review() {
		global $aioseop_notices;

		$slug             = 'activation_review';
		$delay_time       = 0;
		$html             = __( 'Thank you for using All in One SEO Plugin for WordPress. If you enjoy your experience, feel free to give us a %1$s rating. %2$s / %3$s', 'all-in-one-seo-pack' );
		$action_options   = array();
		$action_options[] = array(
			'time'    => 0,
			'text'    => '&#9733;&#9733;&#9733;&#9733;&#9733;',
			'link'    => 'https://wordpress.org/support/plugin/all-in-one-seo-pack/reviews?rate=5#new-post',
			'dismiss' => false,
			'class'   => 'aioseop-rating-link',
		);
		$action_options[] = array(
			'time'    => 0,
			'text'    => 'Review AIOSEOP',
			'link'    => 'https://wordpress.org/support/plugin/all-in-one-seo-pack/reviews?rate=5#new-post',
			'dismiss' => false,
			'class'   => 'button-secondary',
		);
		$action_options[] = array(
			'time'    => 0,
			'text'    => 'Dismiss',
			'dismiss' => true,
			'class'   => '',
		);
		$layer_level      = 10;
		$screens          = array( 'aioseop' );

		$aioseop_notices->insert_footer( $slug, $delay_time, $html, $action_options, $layer_level, $screens );
	}
}
