<?php
/**
 * AIOSEOP Footer API: AIOSEOP Footer Class
 *
 * Handles adding, updating, and removing footers. Then handles activating or
 * deactivating those footers site-wide or user based.
 *
 * @link https://wordpress.org/plugins/all-in-one-seo-pack/
 *
 * @package All-in-One-SEO-Pack
 * @since 2.4.2
 */


if ( ! class_exists( 'AIOSEOP_Footers' ) ) {
	class AIOSEOP_Footers
	{
		/**
		 * Collection of footers saved to AIOSEOP_Footer database. Footers are
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
		 *         Show options for users to click on. Default: See self::action_option_defaults().
		 *             @type array {
		 *                 @type int     $time    Optional. The amount of time to delay. Zero immediately displays Default: 0.
		 *                 @type string  $text    Optional. Button/Link HTML text to display. Default: ''.
		 *                 @type string  $class   Optional. Class names to add to the link/button for styling. Default: ''.
		 *                 @type string  $link    Optional. The elements href source/link. Default: '#'.
		 *                 @type boolean $dismiss Optional. Variable for AJAX to dismiss showing a footer.
		 *             }
		 *         }
		 *         @type int    $layer_level When multiple footers exist, the highest number is shown.
		 *         @type int    $time_start  The time the footer was added to the object.
		 *         @type array  $screens     Which screens to exclusively display the footer on.
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
		private $default_dismiss_delay = 180;

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
			$this->_requires();
			if ( is_admin() ) {
				if ( ! AIOSEOPPRO ) {
					$this->obj_load_options();

					add_action( 'admin_init', array( $this, 'init' ) );
					add_action( 'current_screen', array( $this, 'admin_screen' ) );
				}
			}
		}

		/**
		 * _Requires
		 *
		 * Additional files required.
		 *
		 * @since 2.4.2
		 */
		private function _requires() {
			require_once( AIOSEOP_PLUGIN_DIR . 'admin/functions-footer.php' );
		}

		/**
		 * Early operations required by the plugin.
		 *
		 * AJAX requires being added early before screens have been loaded.
		 *
		 * @since 2.4.2
		 */
		public function init() {
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
				// AIOSEOP Footer Content.
				add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
				add_action( 'admin_footer_text', array( $this, 'display_footer' ) );
			} elseif ( isset( $current_screen->id ) ) {
				// Default WP.
				add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
			}
		}

		/**
		 * Load AIOSEOP_Footer Options
		 *
		 * Gets the options for AIOSEOP_Footer to set its variables to.
		 *
		 * @since 2.4.2
		 * @access private
		 *
		 * @see self::footers
		 * @see self::active_footers
		 */
		private function obj_load_options() {
			$footers_options = $this->obj_get_options();

			$this->footers   = $footers_options['footers'];
		}

		/**
		 * Get AIOSEOP_Footer Options
		 *
		 * @since 2.4.2
		 * @access private
		 *
		 * @return array
		 */
		private function obj_get_options() {
			$defaults = array(
				'footers'        => array(),
			);

			$footers_options = get_option( 'aioseop_footers' );
			if ( false === $footers_options ) {
				return $defaults;
			}

			return wp_parse_args( $footers_options, $defaults );
		}

		/**
		 * Update Footer Options
		 *
		 * @since 2.4.2
		 * @access private
		 *
		 * @return boolean True if successful, using update_option() return value.
		 */
		private function obj_update_options() {
			$footers_options     = array(
				'footers'        => $this->footers,
			);
			$old_footers_options = $this->obj_get_options();
			$footers_options     = wp_parse_args( $footers_options, $old_footers_options );

			return update_option( 'aioseop_footers', $footers_options );
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
		 * Delay Options Default Values
		 *
		 * Returns the default value for action_options in self::footers[$slug]['action_options'].
		 *
		 * @since 2.4.2
		 *
		 * @return array action_options variable in self::footers[$slug]['action_options'].
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
		 * Set Footer Action Options
		 *
		 * Sets the Action Options in a Footer.
		 *
		 * @since 2.4.2
		 * @access private
		 *
		 * @see self::insert_footer()
		 * @see self::update_footer()
		 *
		 * @param array $action_options New action options to be added/updated.
		 * @return array Action Options with new values added to old.
		 */
		private function set_action_options( $action_options ) {
			$rtn_action_options = array();
			if ( empty( $action_options ) && ! is_array( $action_options ) ) {
				$rtn_action_options[] = $this->action_options_defaults();
				return $rtn_action_options;
			}

			foreach ( $action_options as $action_option ) {
				$tmp_action_o = $this->action_options_defaults();

				// Action delay time.
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
		 * Insert Footer
		 *
		 * Adds a footer with a delay time, and is shown until deactivated via AJAX.
		 *
		 * @since 2.4.2
		 *
		 * @param array $footer See self::footers for more info.
		 * @return boolean True on success.
		 */
		public function insert_footer( $footer ) {
			if ( empty( $footer['slug'] ) ) {
				return false;
			} elseif ( isset( $this->footers[ $footer['slug'] ] ) ) {
				return false;
			}

			$footer_default = $this->footer_defaults();
			$new_footer = wp_parse_args( $footer, $footer_default );

			$tmp_action_options           = $this->set_action_options( $footer['action_options'] );
			$new_footer['action_options'] = $tmp_action_options;

			$this->footers[ $footer['slug'] ] = $new_footer;
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
			wp_deregister_script( 'aioseop-admin-footers-js' );
			wp_deregister_style( 'aioseop-admin-footers-css' );
		}

		/**
		 * (Register) Enqueue Scripts
		 *
		 * Used to register, enqueue, and localize any JS data. Styles can later be added.
		 *
		 * @since 2.4.2
		 */
		public function enqueue_scripts() {
			// Register.
			wp_register_script(
				'aioseop-admin-footer-js',
				AIOSEOP_PLUGIN_URL . 'js/admin-footer.js',
				array(),
				AIOSEOP_VERSION,
				true
			);

			// Localization.
			$footers = array();
			foreach ( $this->footers as $footer_slug => $footer ) {
				$footers[ $footer_slug ] = array();
				foreach ( $footer['action_options'] as $action_index => $action_arr ) {
					$footers[ $footer_slug ][] = $action_index;
				}
			}

			$admin_footer_localize = array(
				'footer_nonce'  => wp_create_nonce( 'aioseop_ajax_footer' ),
				'footers'       => $footers,
			);
			wp_localize_script( 'aioseop-admin-footer-js', 'aioseop_footer_data', $admin_footer_localize );

			// Enqueue.
			wp_enqueue_script( 'aioseop-admin-footer-js' );

			wp_enqueue_style(
				'aioseop-admin-footers-css',
				AIOSEOP_PLUGIN_URL . 'css/admin-footer.css',
				false,
				AIOSEOP_VERSION,
				false
			);
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
			foreach ( $this->footers as $footer ) {
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
			$vsprint_arr = array();
			if ( ! empty( $tmp2_footer['action_options'] ) ) {
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
		 * AJAX Footer Action
		 *
		 * Fires when a Action_Option is clicked and sent via AJAX.
		 *
		 * @since 2.4.2
		 *
		 * @see AIOSEOP_PLUGIN_DIR . 'js/admin-footer.js'
		 */
		public function ajax_footer_action() {
			check_ajax_referer( 'aioseop_ajax_footer' );

			$footer_slug = null;
			$action_index = null;
			if ( isset( $_POST['footer_slug'] ) ) {
				$footer_slug = filter_input( INPUT_POST, 'footer_slug', FILTER_SANITIZE_STRING );
			}
			if ( isset( $_POST['action_index'] ) ) {
				$action_index = filter_input( INPUT_POST, 'action_index', FILTER_SANITIZE_STRING );
				$action_index = intval( $action_index );
			}
			if ( empty( $footer_slug ) || empty( $action_index ) ) {
				wp_die();
			}

			$action_options = $this->action_options_defaults();

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
	global $aioseop_footers;
	$aioseop_footers = new AIOSEOP_Footers();
}
