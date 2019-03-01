<?php
/**
 * AIOSEOP Notice API: AIOSEOP Notice Class
 *
 * Handles adding, updating, and removing notices. Then handles activating or
 * deactivating those notices site-wide or user based.
 *
 * @link https://wordpress.org/plugins/all-in-one-seo-pack/
 *
 * @package All_in_One_SEO_Pack
 * @since 3.0
 */

if ( ! class_exists( 'AIOSEOP_Notices' ) ) {
	/**
	 * AIOSEOP Notice.
	 *
	 * Admin notices for AIOSEOP.
	 *
	 * @since 3.0
	 */
	class AIOSEOP_Notices {
		/**
		 * Collection of notices to display.
		 *
		 * @since 3.0
		 * @access public
		 *
		 * @var array $notices {
		 *     @type array $slug {
		 *         @type string $slug        Required. Notice unique ID.
		 *         @type int    $delay_time  Amount of time to begin showing message.
		 *         @type string $message     Content message to display in the container.
		 *         @type array  $action_option {
		 *         Show options for users to click on. Default: See self::action_option_defaults().
		 *             @type array {
		 *                 @type int     $time    Optional. The amount of time to delay. Zero immediately displays Default: 0.
		 *                 @type string  $text    Optional. Button/Link HTML text to display. Default: ''.
		 *                 @type string  $class   Optional. Class names to add to the link/button for styling. Default: ''.
		 *                 @type string  $link    Optional. The elements href source/link. Default: '#'.
		 *                 @type boolean $dismiss Optional. Variable for AJAX to dismiss showing a notice.
		 *             }
		 *         }
		 *         @type string $class       The class notice used by WP, or a custom CSS class.
		 *                                   Ex. notice-error, notice-warning, notice-success, notice-info.
		 *         @type string $target      Shows based on site-wide or user notice data.
		 *         @todo string $perms       Displays based on user-role/permissions.
		 *         @type array  $screens     Which screens to exclusively display the notice on. Default: array().
		 *                                   array()          = all,
		 *                                   array('aioseop') = $this->aioseop_screens,
		 *                                   array('CUSTOM')  = specific screen(s).
		 *         @type int    $time_start  The time the notice was added to the object.
		 *         @type int    $time_set    Set when AJAX/Action_Option was last used to delay time. Primarily for PHPUnit tests.
		 *     }
		 * }
		 */
		public $notices = array();

		/**
		 * List of notice slugs that are currently active.
		 * NOTE: Amount is reduced by 1 second in order to display at exactly X amount of time.
		 *
		 * @since 3.0
		 * @access public
		 *
		 * @var array $active_notices {
		 *     @type string|int $slug => $display_time Contains the current active notices
		 *                                             that are scheduled to be displayed.
		 * }
		 */
		public $active_notices = array();

		/**
		 * The default dismiss time. An anti-nag setting.
		 *
		 * @var int $default_dismiss_delay
		 */
		private $default_dismiss_delay = 180;

		/**
		 * List of Screens used in AIOSEOP.
		 *
		 * @since 3.0
		 *
		 * @var array $aioseop_screens {
		 *     @type string Screen ID.
		 * }
		 */
		private $aioseop_screens = array();

		/**
		 * __constructor.
		 *
		 * @since 3.0
		 */
		public function __construct() {
			$this->_requires();
			if ( current_user_can( 'aiosp_manage_seo' ) ) {

				$this->aioseop_screens[] = 'toplevel_page_' . AIOSEOP_PLUGIN_DIRNAME . '/aioseop_class';
				$this->aioseop_screens[] = 'all-in-one-seo_page_' . AIOSEOP_PLUGIN_DIRNAME . '/modules/aioseop_performance';
				$this->aioseop_screens[] = 'all-in-one-seo_page_' . AIOSEOP_PLUGIN_DIRNAME . '/modules/aioseop_sitemap';
				$this->aioseop_screens[] = 'all-in-one-seo_page_aiosp_opengraph';
				$this->aioseop_screens[] = 'all-in-one-seo_page_aiosp_robots_generator';
				$this->aioseop_screens[] = 'all-in-one-seo_page_' . AIOSEOP_PLUGIN_DIRNAME . '/modules/aioseop_robots';
				$this->aioseop_screens[] = 'all-in-one-seo_page_' . AIOSEOP_PLUGIN_DIRNAME . '/modules/aioseop_file_editor';
				$this->aioseop_screens[] = 'all-in-one-seo_page_' . AIOSEOP_PLUGIN_DIRNAME . '/modules/aioseop_importer_exporter';
				$this->aioseop_screens[] = 'all-in-one-seo_page_' . AIOSEOP_PLUGIN_DIRNAME . '/modules/aioseop_bad_robots';
				$this->aioseop_screens[] = 'all-in-one-seo_page_' . AIOSEOP_PLUGIN_DIRNAME . '/modules/aioseop_feature_manager';

				$this->obj_load_options();

				add_action( 'admin_init', array( $this, 'init' ) );
				add_action( 'current_screen', array( $this, 'admin_screen' ) );
			}
		}

		/**
		 * _Requires
		 *
		 * Additional files required.
		 *
		 * @since 3.0
		 */
		private function _requires() {
			require_once AIOSEOP_PLUGIN_DIR . 'admin/functions-notice.php';
		}

		/**
		 * Early operations required by the plugin.
		 *
		 * AJAX requires being added early before screens have been loaded.
		 *
		 * @since 3.0
		 */
		public function init() {
			add_action( 'wp_ajax_aioseop_notice', array( $this, 'ajax_notice_action' ) );
		}

		/**
		 * Setup/Init Admin Screen
		 *
		 * Adds the initial actions to WP based on the Admin Screen being loaded.
		 * The AIOSEOP and Other Screens have separate methods that are used, and
		 * additional screens can be made exclusive/unique.
		 *
		 * @since 3.0
		 *
		 * @param WP_Screen $current_screen The current screen object being loaded.
		 */
		public function admin_screen( $current_screen ) {
			$this->deregister_scripts();
			if ( isset( $current_screen->id ) && in_array( $current_screen->id, $this->aioseop_screens, true ) ) {
				// AIOSEO Notice Content.
				add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
				add_action( 'all_admin_notices', array( $this, 'display_notice_aioseop' ) );
			} elseif ( isset( $current_screen->id ) ) {
				// Default WP Notice.
				add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
				add_action( 'all_admin_notices', array( $this, 'display_notice_default' ) );
			}
		}

		/**
		 * Load AIOSEOP_Notice Options
		 *
		 * Gets the options for AIOSEOP_Notice to set its variables to.
		 *
		 * @since 3.0
		 * @access private
		 *
		 * @see self::notices
		 * @see self::active_notices
		 */
		private function obj_load_options() {
			$notices_options = $this->obj_get_options();

			$this->notices        = $notices_options['notices'];
			$this->active_notices = $notices_options['active_notices'];
		}

		/**
		 * Get AIOSEOP_Notice Options
		 *
		 * @since 3.0
		 * @access private
		 *
		 * @return array
		 */
		private function obj_get_options() {
			$defaults = array(
				'notices'        => array(),
				'active_notices' => array(),
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
		 * @since 3.0
		 * @access private
		 *
		 * @return boolean True if successful, using update_option() return value.
		 */
		private function obj_update_options() {
			$notices_options     = array(
				'notices'        => $this->notices,
				'active_notices' => $this->active_notices,
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
		 * @since 3.0
		 *
		 * @see self::notices Array variable that stores the collection of notices.
		 *
		 * @return array Notice variable in self::notices.
		 */
		public function notice_defaults() {
			return array(
				'slug'           => '',
				'delay_time'     => 0,
				'message'        => '',
				'action_options' => array(),
				'class'          => 'notice-info',
				'target'         => 'site',
				'screens'        => array(),
				'time_start'     => time(),
				'time_set'       => time(),
			);
		}

		/**
		 * Action Options Default Values
		 *
		 * Returns the default value for action_options in self::notices[$slug]['action_options'].
		 *
		 * @since 3.0
		 *
		 * @return array Action_Options variable in self::notices[$slug]['action_options'].
		 */
		public function action_options_defaults() {
			return array(
				'time'    => 0,
				'text'    => __( 'Dismiss', 'all-in-one-seo-pack' ),
				'link'    => '#',
				'dismiss' => true,
				'class'   => '',
			);
		}

		/**
		 * Set Notice Action Options
		 *
		 * Sets the Action Options in a Notice.
		 *
		 * @since 3.0
		 * @access private
		 *
		 * @see self::insert_notice()
		 * @see self::update_notice()
		 *
		 * @param array $action_options New action options to be added/updated.
		 * @return array Action Options with new values added to old.
		 */
		private function set_action_options( $action_options ) {
			$rtn_action_options = array();
			// This helps prevent invalid notices, and empty arrays need to skip this operation when
			// there is no actions intended for notice.
			if ( ! is_array( $action_options ) ) {
				$rtn_action_options[] = $this->action_options_defaults();
				return $rtn_action_options;
			}

			foreach ( $action_options as $action_option ) {
				$tmp_action_o = $this->action_options_defaults();

				// For readability and tracking, refrane from using another Foreach loop with the array indexes.
				// Button Delay Time.
				$tmp_action_o['time'] = $this->default_dismiss_delay;
				if ( isset( $action_option['time'] ) ) {
					$tmp_action_o['time'] = $action_option['time'];
				}

				// Button Text.
				if ( isset( $action_option['text'] ) && ! empty( $action_option['text'] ) ) {
					$tmp_action_o['text'] = $action_option['text'];
				}

				// Link.
				if ( isset( $action_option['link'] ) && ! empty( $action_option['link'] ) ) {
					$tmp_action_o['link'] = $action_option['link'];
				}

				// Dismiss.
				if ( isset( $action_option['dismiss'] ) ) {
					$tmp_action_o['dismiss'] = $action_option['dismiss'];
				}

				// Class.
				if ( isset( $action_option['class'] ) && ! empty( $action_option['class'] ) ) {
					$tmp_action_o['class'] = $action_option['class'];
				}

				$rtn_action_options[] = $tmp_action_o;
			}

			return $rtn_action_options;
		}

		/**
		 * Insert Notice
		 *
		 * Initial insert for a Notice and Activates it. Used strictly for adding notices
		 * when no updating or modifications is intended.
		 *
		 * @since 3.0
		 *
		 * @uses self::activate_notice() Used to initialize a notice.
		 *
		 * @param array $notice See self::notices for more info.
		 * @return boolean True on success.
		 */
		public function insert_notice( $notice = array() ) {
			if ( empty( $notice['slug'] ) || isset( $this->notices[ $notice['slug'] ] ) ) {
				return false;
			}

			$this->notices[ $notice['slug'] ] = $this->prepare_notice( $notice );

			$this->obj_update_options();
			$this->activate_notice( $notice['slug'] );

			return true;
		}

		/**
		 * Update Notice
		 *
		 * Updates an existing Notice without resetting it. Used when modifying
		 * any existing notices without disturbing its set environment/timeline.
		 *
		 * @since 3.0
		 *
		 * @param array $notice See self::notices for more info.
		 * @return boolean True on success.
		 */
		public function update_notice( $notice = array() ) {
			if ( empty( $notice['slug'] ) || ! isset( $this->notices[ $notice['slug'] ] ) ) {
				return false;
			}

			$this->notices[ $notice['slug'] ] = $this->prepare_notice( $notice );

			$this->obj_update_options();

			return true;
		}

		/**
		 * Prepare Insert/Undate Notice
		 *
		 * @since 3.0
		 *
		 * @param array $notice The notice to prepare with the database.
		 * @return bool
		 */
		public function prepare_notice( $notice = array() ) {
			$notice_default = $this->notice_defaults();
			$new_notice     = wp_parse_args( $notice, $notice_default );

			$new_notice['action_options'] = $this->set_action_options( $new_notice['action_options'] );

			return $new_notice;
		}

		/**
		 * Used strictly for any notices that are deprecated/obsolete. To stop notices,
		 * use notice_deactivate().
		 *
		 * @since 3.0
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
		 * @since 3.0
		 *
		 * @param string $slug Notice slug.
		 * @return boolean
		 */
		public function activate_notice( $slug ) {
			if ( empty( $slug ) || ! isset( $this->notices[ $slug ] ) ) {
				return false;
			}

			// Display at exactly X time, not (X + 1) time.
			$display_time = time() + $this->notices[ $slug ]['delay_time'];
			$display_time--;

			if ( 'user' === $this->notices[ $slug ]['target'] ) {
				$current_user_id = get_current_user_id();

				update_user_meta( $current_user_id, 'aioseop_notice_dismissed_' . $slug, false );
				update_user_meta( $current_user_id, 'aioseop_notice_display_time_' . $slug, $display_time );
			}

			$this->active_notices[ $slug ] = $display_time;
			$this->obj_update_options();

			return true;
		}

		/**
		 * Deactivate Notice
		 *
		 * Deactivates a notice set as active and completely removes it from the
		 * list of active notices. Used to prevent conflicting notices that may be
		 * active at any given point in time.
		 *
		 * @since 3.0
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

		/*** DISPLAY Methods **************************************************/
		/**
		 * Deregister Scripts
		 *
		 * Initial Admin Screen action to remove aioseop script(s) from all screens;
		 * which will be registered if executed on screen.
		 * NOTE: As of 3.0, most of it is default layout, styling, & scripting
		 * that is loaded on all pages. Which can later be different.
		 *
		 * @since 3.0
		 * @access private
		 *
		 * @see self::admin_screen()
		 */
		private function deregister_scripts() {
			wp_deregister_script( 'aioseop-admin-notice-js' );
			wp_deregister_style( 'aioseop-admin-notice-css' );
		}

		/**
		 * (Register) Enqueue Scripts
		 *
		 * Used to register, enqueue, and localize any JS data. Styles can later be added.
		 *
		 * @since 3.0
		 */
		public function admin_enqueue_scripts() {
			// Register.
			wp_register_script(
				'aioseop-admin-notice-js',
				AIOSEOP_PLUGIN_URL . 'js/admin-notice.js',
				array( 'jquery' ),
				AIOSEOP_VERSION,
				true
			);

			// Localization.
			$notice_actions = array();
			foreach ( $this->active_notices as $notice_slug => $notice_display_time ) {
				foreach ( $this->notices[ $notice_slug ]['action_options'] as $action_index => $action_arr ) {
					$notice_actions[ $notice_slug ][] = $action_index;
				}
			}

			$admin_notice_localize = array(
				'notice_nonce'   => wp_create_nonce( 'aioseop_ajax_notice' ),
				'notice_actions' => $notice_actions,
			);
			wp_localize_script( 'aioseop-admin-notice-js', 'aioseop_notice_data', $admin_notice_localize );

			// Enqueue.
			wp_enqueue_script( 'aioseop-admin-notice-js' );

			wp_enqueue_style(
				'aioseop-admin-notice-css',
				AIOSEOP_PLUGIN_URL . 'css/admin-notice.css',
				false,
				AIOSEOP_VERSION,
				false
			);
		}

		/**
		 * Display Notice as Default
		 *
		 * Method for default WP Admin notices.
		 * NOTE: As of 3.0, display_notice_default() & display_notice_aioseop()
		 * have the same functionality, but serves as a future development concept.
		 *
		 * @since 3.0
		 *
		 * @uses AIOSEOP_PLUGIN_DIR . 'admin/display/notice-default.php' Template for default notices.
		 *
		 * @return void
		 */
		public function display_notice_default() {
			$this->display_notice( 'default' );
		}

		/**
		 * Display Notice as AIOSEOP Screens
		 *
		 * Method for Admin notices exclusive to AIOSEOP screens.
		 * NOTE: As of 3.0, display_notice_default() & display_notice_aioseop()
		 * have the same functionality, but serves as a future development concept.
		 *
		 * @since 3.0
		 *
		 * @uses AIOSEOP_PLUGIN_DIR . 'admin/display/notice-aioseop.php' Template for notices.
		 *
		 * @return void
		 */
		public function display_notice_aioseop() {
			$this->display_notice( 'aioseop' );
		}

		/**
		 * Display Notice
		 *
		 * @since 2.8
		 *
		 * @param string $template Slug name for template.
		 */
		public function display_notice( $template ) {
			if ( ! wp_script_is( 'aioseop-admin-notice-js', 'enqueued' ) || ! wp_style_is( 'aioseop-admin-notice-css', 'enqueued' ) ) {
				return;
			} elseif ( 'default' !== $template && 'aioseop' !== $template ) {
				return;
			} elseif ( ! current_user_can( 'aiosp_manage_seo' ) ) {
				return;
			}

			$current_screen  = get_current_screen();
			$current_user_id = get_current_user_id();
			foreach ( $this->active_notices as $a_notice_slug => $a_notice_time_display ) {
				$notice_show = true;

				// Screen Restriction.
				if ( ! empty( $this->notices[ $a_notice_slug ]['screens'] ) ) {
					// Checks if on aioseop screen.
					if ( in_array( 'aioseop', $this->notices[ $a_notice_slug ]['screens'], true ) ) {
						if ( ! in_array( $current_screen->id, $this->aioseop_screens, true ) ) {
							continue;
						}
					}

					// Checks the other screen restrictions by slug/id.
					if ( ! in_array( 'aioseop', $this->notices[ $a_notice_slug ]['screens'], true ) ) {
						if ( ! in_array( $current_screen->id, $this->notices[ $a_notice_slug ]['screens'], true ) ) {
							continue;
						}
					}
				}

				// User Settings.
				if ( 'user' === $this->notices[ $a_notice_slug ]['target'] ) {
					$user_dismissed = get_user_meta( $current_user_id, 'aioseop_notice_dismissed_' . $a_notice_slug, true );
					if ( ! $user_dismissed ) {
						$user_notice_time_display = get_user_meta( $current_user_id, 'aioseop_notice_display_time_' . $a_notice_slug, true );
						if ( ! empty( $user_notice_time_display ) ) {
							$a_notice_time_display = intval( $user_notice_time_display );
						}
					} else {
						$notice_show = false;
					}
				}

				// Display/Render.
				$important_admin_notices = array(
					'notice-error',
					'notice-warning',
					'notice-do-nag',
				);
				if ( defined( 'DISABLE_NAG_NOTICES' ) && true === DISABLE_NAG_NOTICES && ( ! in_array( $this->notices[ $a_notice_slug ]['class'], $important_admin_notices, true ) ) ) {
					// Skip if `DISABLE_NAG_NOTICES` is implemented (as true).
					// Important notices, WP's CSS `notice-error` & `notice-warning`, are still rendered.
					continue;
				} elseif ( time() > $a_notice_time_display && $notice_show ) {
					include AIOSEOP_PLUGIN_DIR . 'admin/display/notice-' . $template . '.php';
				}
			}
		}

		/**
		 * AJAX Notice Action
		 *
		 * Fires when a Action_Option is clicked and sent via AJAX. Also includes
		 * WP Default Dismiss (rendered as a clickable button on upper-right).
		 *
		 * @since 3.0
		 *
		 * @see AIOSEOP_PLUGIN_DIR . 'js/admin-notice.js'
		 */
		public function ajax_notice_action() {
			check_ajax_referer( 'aioseop_ajax_notice' );
			if ( ! current_user_can( 'aiosp_manage_seo' ) ) {
				wp_send_json_error( __( 'User doesn\' have `aiosp_manage_seo` capabilities.', 'all-in-one-seo-pack' ) );
			}
			// Notice (Slug) => (Action_Options) Index.
			$notice_slug  = null;
			$action_index = null;
			if ( isset( $_POST['notice_slug'] ) ) {
				$notice_slug = filter_input( INPUT_POST, 'notice_slug', FILTER_SANITIZE_STRING );

				// When PHPUnit is unable to use filter_input.
				if ( defined( 'AIOSEOP_UNIT_TESTING' ) && null === $notice_slug && ! empty( $_POST['notice_slug'] ) ) {
					$notice_slug = $_POST['notice_slug'];
				}
			}
			if ( isset( $_POST['action_index'] ) ) {
				$action_index = filter_input( INPUT_POST, 'action_index', FILTER_SANITIZE_STRING );

				// When PHPUnit is unable to use filter_input.
				if ( defined( 'AIOSEOP_UNIT_TESTING' ) && null === $action_index && ( ! empty( $_POST['action_index'] ) || 0 === $_POST['action_index'] ) ) {
					$action_index = $_POST['action_index'];
				}
			}
			if ( empty( $notice_slug ) ) {
				/* Translators: Displays the hordcoded slug that missing. */
				wp_send_json_error( sprintf( __( 'Missing values from `%s`.', 'all-in-one-seo-pack' ), 'notice_slug' ) );
			} elseif ( empty( $action_index ) && 0 !== $action_index ) {
				/* Translators: Displays the hordcoded slug that missing. */
				wp_send_json_error( sprintf( __( 'Missing values from `%s`.', 'all-in-one-seo-pack' ), 'action_index' ) );
			}

			$action_options            = $this->action_options_defaults();
			$action_options['time']    = $this->default_dismiss_delay;
			$action_options['dismiss'] = false;

			if ( isset( $this->notices[ $notice_slug ]['action_options'][ $action_index ] ) ) {
				$action_options = $this->notices[ $notice_slug ]['action_options'][ $action_index ];
			}

			// User Notices or Sitewide.
			if ( 'user' === $this->notices[ $notice_slug ]['target'] ) {
				// Always sets the action time, even if dismissed, so last timestamp is recorded.
				$current_user_id = get_current_user_id();
				if ( $action_options['time'] ) {
					$time_set = time();
					// Adds action_option delay time, reduced by 1 second to display at exact time.
					$metadata = $time_set + $action_options['time'] - 1;

					update_user_meta( $current_user_id, 'aioseop_notice_time_set_' . $notice_slug, $time_set );
					update_user_meta( $current_user_id, 'aioseop_notice_display_time_' . $notice_slug, $metadata );
				}
				if ( $action_options['dismiss'] ) {
					update_user_meta( $current_user_id, 'aioseop_notice_dismissed_' . $notice_slug, $action_options['dismiss'] );
				}
			} else {
				if ( $action_options['time'] ) {
					$this->notices[ $notice_slug ]['time_set'] = time();
					// Adds action_option delay time, reduced by 1 second to display at exact time.
					$this->active_notices[ $notice_slug ] = $this->notices[ $notice_slug ]['time_set'] + $action_options['time'] - 1;
				}

				if ( $action_options['dismiss'] ) {
					$this->deactivate_notice( $notice_slug );
				}
			}

			$this->obj_update_options();
			wp_send_json_success( __( 'Notice updated successfully.', 'all-in-one-seo-pack' ) );
		}

	}
	// CLASS INITIALIZATION.
	// Should this be a singleton class instead of a global?
	global $aioseop_notices;
	$aioseop_notices = new AIOSEOP_Notices();
}
