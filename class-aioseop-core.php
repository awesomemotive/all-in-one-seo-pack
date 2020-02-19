<?php
/**
 * AIOSEOP Core Class
 *
 * Handles all the core operations required to run on a WordPress platform.
 *
 * @package All-in-One-SEO-Pack
 * @since 3.4
 */

/**
 * Class AIOSEOP_Core
 *
 * @since 3.4
 */
class AIOSEOP_Core {

	/**
	 * AIOSEOP_Core constructor.
	 *
	 * Set plugin's globals, constants, and initialization hook.
	 *
	 * @since 3.4
	 */
	public function __construct() {
		global $aiosp;
		global $aioseop_options;
		global $aioseop_modules;
		global $aioseop_module_list;
		global $aiosp_activation;
		global $aioseop_mem_limit;
		global $aioseop_get_pages_start;
		global $aioseop_admin_menu;

		$this->_define_constants();

		$aioseop_get_pages_start = 0;
		$aioseop_admin_menu      = 0;
		$aiosp_activation        = false;

		$aioseop_options = get_option( 'aioseop_options' );

		// Sets the memory limit based on settings. Default 256M.
		$memory_limit   = '';
		$execution_time = '';
		if ( ! empty( $aioseop_options['modules']['aiosp_performance_options']['aiosp_performance_memory_limit'] ) ) {
			$memory_limit = $aioseop_options['modules']['aiosp_performance_options']['aiosp_performance_memory_limit'];
		}
		if ( ! empty( $aioseop_options['modules']['aiosp_performance_options']['aiosp_performance_execution_time'] ) ) {
			$execution_time = $aioseop_options['modules']['aiosp_performance_options']['aiosp_performance_execution_time'];
		}
		$aioseop_mem_limit = $this->set_mem_limit( $memory_limit, $execution_time );

		// List all available modules here.
		$aioseop_module_list = array(
			'sitemap',
			'opengraph',
			'robots',
			'file_editor',
			'importer_exporter',
			'bad_robots',
			'performance',
		);

		if ( AIOSEOPPRO ) {
			$aioseop_module_list[] = 'video_sitemap';
		}

		// Initialize plugin.
		add_action( 'plugins_loaded', array( $this, 'init' ), 5 );
	}

	/**
	 * Initialize plugin.
	 *
	 * TODO Refactor method on lines marked `TODO`.
	 *
	 * @since 3.4
	 */
	public function init() {
		global $aiosp;
		global $aioseop_options;

		// Error notice when class already exists.
		if ( class_exists( 'All_in_One_SEO_Pack' ) ) {
			add_action( 'admin_notices', array( $this, 'admin_notices_already_defined' ) );
		}

		$this->_requires();
		$this->add_hooks();

		// TODO Remove/Change. We no longer have a folder called i18n OR should this be called `languages`.
		load_plugin_textdomain( 'all-in-one-seo-pack', false, dirname( AIOSEOP_PLUGIN_BASENAME ) . '/i18n/' );

		// Call importer functions... this should be moved somewhere better.
		aiosp_seometa_import();

		$aiosp = new All_in_One_SEO_Pack();

		$aioseop_updates = new AIOSEOP_Updates();

		// Check for plugin version update.
		// TODO Move to AIOSEOP_Updates::__construct().
		add_action( 'plugins_loaded', array( $aioseop_updates, 'version_updates' ), 11 );
		if ( AIOSEOPPRO ) {
			$aioseop_pro_updates = new AIOSEOP_Pro_Updates();
			// TODO Move to AIOSEOP_Pro_Updates::__construct().
			add_action( 'admin_init', array( $aioseop_pro_updates, 'version_updates' ), 12 );
		}

		// Check for Pro updates.
		// vv TODO Should this be moved to (Pro) updater class?
		if ( AIOSEOPPRO ) {
			global $aioseop_update_checker;

			require( AIOSEOP_PLUGIN_DIR . 'pro/sfwd_update_checker.php' );
			$aiosp_update_url = 'https://semperplugins.com/upgrade_plugins.php';
			if ( defined( 'AIOSEOP_UPDATE_URL' ) ) {
				$aiosp_update_url = AIOSEOP_UPDATE_URL;
			}
			$aioseop_update_checker = new SFWD_Update_Checker(
				$aiosp_update_url,
				__FILE__,
				'aioseop'
			);

			$aioseop_update_checker->plugin_name     = AIOSEOP_PLUGIN_NAME;
			$aioseop_update_checker->plugin_basename = AIOSEOP_PLUGIN_BASENAME;
			if ( ! empty( $aioseop_options['aiosp_license_key'] ) ) {
				$aioseop_update_checker->license_key = $aioseop_options['aiosp_license_key'];
			} else {
				$aioseop_update_checker->license_key = '';
			}
			$aioseop_update_checker->options_page = AIOSEOP_PLUGIN_DIRNAME . '/aioseop_class.php';
			$aioseop_update_checker->renewal_page = 'https://semperplugins.com/all-in-one-seo-pack-pro-version/';

			$aioseop_update_checker->addQueryArgFilter( array( $aioseop_update_checker, 'add_secret_key' ) );
		}
		// ^^ TODO Should this be moved to (Pro) updater class?

		// TODO Move this to updates file.
		// FIXME This is executed in AIOSEOP_Core::aioseop_welcome() on admin_init hook.
		new aioseop_welcome();
		add_action( 'admin_init', array( $this, 'aioseop_welcome' ) );

		// TODO Move this add_action to All_in_One_SEO_Pack::__construct().
		add_action( 'init', array( $aiosp, 'add_hooks' ) );

		// phpcs:ignore Squiz.Commenting.InlineComment.InvalidEndChar
		// add_action( 'admin_init', array( $this, 'review_plugin_notice' ) );

		// Perform Opengraph scan from JS scan.
		// vv TODO This could be improved by using WP AJAX.
		if ( wp_doing_ajax() && ! empty( $_POST ) && ! empty( $_POST['action'] ) && 'aioseop_ajax_scan_header' === $_POST['action'] ) {
			remove_action( 'init', array( $aiosp, 'add_hooks' ) );
			add_action( 'admin_init', array( $this, 'scan_post_header' ) );
			// if the action doesn't run -- pdb.
			add_action( 'shutdown', 'aioseop_ajax_scan_header' );

			include_once( ABSPATH . 'wp-admin/includes/screen.php' );
			global $current_screen;
			if ( class_exists( 'WP_Screen' ) ) {
				$current_screen = WP_Screen::get( 'front' );
			}
		}
		// ^^ TODO This could be improved by using WP AJAX.
	}

	/**
	 * Define plugin constants.
	 *
	 * @ignore
	 *
	 * @since 3.4
	 *
	 * @see get_file_data()
	 * @link https://developer.wordpress.org/reference/functions/get_file_data/
	 * @link https://hitchhackerguide.com/2011/02/12/get_plugin_data/
	 *
	 * @access private
	 */
	private function _define_constants() {
		if ( defined( 'AIOSEOP_VERSION' ) ) {
			return;
		}

		// PHP > 5.2 compatibility for __DIR__.
		$directory = dirname( __FILE__ );

		$root_dir    = wp_normalize_path( str_replace( basename( $directory ), '', $directory ) );
		$aioseop_dir = $root_dir . AIOSEOP_PLUGIN_BASENAME;

		// Use get_file_data with this file, and get the plugin's file data with default_headers.
		$default_headers = array(
			'Name'    => 'Plugin Name',
			'Version' => 'Version',
		);

		$plugin_data = get_file_data( $aioseop_dir, $default_headers );

		/**
		 * AIOSEOP Display Name
		 *
		 * @since ?
		 * @since 3.4 Change to file header data.
		 *
		 * @var string $AIOSEOP_PLUGIN_NAME Contains 'All In One SEO Pack'.
		 */
		define( 'AIOSEOP_PLUGIN_NAME', $plugin_data['Name'] );

		/**
		 * Plugin Version Number
		 *
		 * @since ?
		 * @since 3.4 Change to file header data.
		 *
		 * @var string $AIOSEOP_VERSION Contains the plugin's version number. Eg. '3.2.4'
		 */
		define( 'AIOSEOP_VERSION', $plugin_data['Version'] );

		if ( ! defined( 'AIOSEOPPRO' ) ) {
			define( 'AIOSEOPPRO', false );
		}

		if ( ! defined( 'AIOSEOP_PLUGIN_DIR' ) ) {

			/**
			 * Plugin Directory
			 *
			 * @since ?
			 *
			 * @var string $AIOSEOP_PLUGIN_DIR Plugin folder directory path. Eg. `C:\WebProjects\UW-WPDev-aioseop\src-plugins/all-in-one-seo-pack/`
			 */
			define( 'AIOSEOP_PLUGIN_DIR', plugin_dir_path( $aioseop_dir ) );
		}

		// Defines constants that haven't been defined.
		// Keep `! defined()` for development purposes to possibly separate plugin development from other plugins.
		// DEV NOTE: This may not be practical. WP still requires AIOSEOP to be in the plugins folder in order to be detected.
		if ( ! defined( 'AIOSEOP_PLUGIN_DIRNAME' ) ) {

			/**
			 * Plugin Directory Name
			 *
			 * @since ?
			 *
			 * @var string $AIOSEOP_PLUGIN_DIRNAME Plugin folder/directory name. Eg. `all-in-one-seo-pack`
			 */
			define( 'AIOSEOP_PLUGIN_DIRNAME', dirname( AIOSEOP_PLUGIN_BASENAME ) );
		}
		if ( ! defined( 'AIOSEOP_PLUGIN_URL' ) ) {

			/**
			 * Plugin URL
			 *
			 * @since ?
			 *
			 * @var string $AIOSEOP_PLUGIN_URL Plugin directory url. Eg `http://aioseop.test/wp-content/plugins/all-in-one-seo-pack/`
			 */
			define( 'AIOSEOP_PLUGIN_URL', plugin_dir_url( $aioseop_dir ) );
		}
		if ( ! defined( 'AIOSEOP_PLUGIN_IMAGES_URL' ) ) {

			/**
			 * Plugin Images URL
			 *
			 * @since ?
			 *
			 * @var string $AIOSEOP_PLUGIN_IMAGES_URL URL location for the plugin's image directory. Eg. `http://aioseop.test/wp-content/plugins/all-in-one-seo-pack/images/`
			 */
			define( 'AIOSEOP_PLUGIN_IMAGES_URL', AIOSEOP_PLUGIN_URL . 'images/' );
		}
		if ( ! defined( 'AIOSEOP_BASELINE_MEM_LIMIT' ) ) {

			/**
			 * Plugin Baseline Memory Limit
			 *
			 * @since ?
			 *
			 * @var string $AIOSEOP_BASELINE_MEM_LIMIT The memory limit to set the ini config to.
			 */
			define( 'AIOSEOP_BASELINE_MEM_LIMIT', '256M' );
		}

		// TODO Is this still necessary? These should already be defined by WP before plugins_loaded hook occurs.
		if ( ! defined( 'WP_CONTENT_URL' ) ) {
			define( 'WP_CONTENT_URL', site_url() . '/wp-content' );
		}
		if ( ! defined( 'WP_ADMIN_URL' ) ) {
			define( 'WP_ADMIN_URL', site_url() . '/wp-admin' );
		}
		if ( ! defined( 'WP_CONTENT_DIR' ) ) {
			define( 'WP_CONTENT_DIR', ABSPATH . 'wp-content' );
		}
		if ( ! defined( 'WP_PLUGIN_URL' ) ) {
			define( 'WP_PLUGIN_URL', WP_CONTENT_URL . '/plugins' );
		}
		if ( ! defined( 'WP_PLUGIN_DIR' ) ) {
			define( 'WP_PLUGIN_DIR', WP_CONTENT_DIR . '/plugins' );
		}
	}

	/**
	 * Handles require_once files.
	 *
	 * @ignore
	 *
	 * @since 3.4
	 *
	 * @access private
	 */
	private function _requires() {
		require_once AIOSEOP_PLUGIN_DIR . 'inc/aioseop_functions.php';
		require_once AIOSEOP_PLUGIN_DIR . 'aioseop_class.php';
		require_once AIOSEOP_PLUGIN_DIR . 'inc/aioseop_updates_class.php';
		require_once AIOSEOP_PLUGIN_DIR . 'inc/commonstrings.php';
		require_once AIOSEOP_PLUGIN_DIR . 'admin/display/general-metaboxes.php';
		require_once AIOSEOP_PLUGIN_DIR . 'inc/aiosp_common.php';
		require_once AIOSEOP_PLUGIN_DIR . 'admin/meta_import.php';
		require_once AIOSEOP_PLUGIN_DIR . 'inc/translations.php';
		require_once AIOSEOP_PLUGIN_DIR . 'public/opengraph.php';
		require_once AIOSEOP_PLUGIN_DIR . 'inc/compatibility/abstract/aiosep_compatible.php';
		require_once AIOSEOP_PLUGIN_DIR . 'inc/compatibility/compat-init.php';
		require_once AIOSEOP_PLUGIN_DIR . 'inc/compatibility/php-functions.php';
		require_once AIOSEOP_PLUGIN_DIR . 'inc/compatibility/class-aioseop-php-functions.php';
		require_once AIOSEOP_PLUGIN_DIR . 'public/front.php';
		require_once AIOSEOP_PLUGIN_DIR . 'public/google-analytics.php';
		require_once AIOSEOP_PLUGIN_DIR . 'admin/display/welcome.php';
		require_once AIOSEOP_PLUGIN_DIR . 'admin/display/dashboard_widget.php';
		require_once AIOSEOP_PLUGIN_DIR . 'admin/display/menu.php';
		require_once AIOSEOP_PLUGIN_DIR . 'admin/class-aioseop-notices.php';
		require_once AIOSEOP_PLUGIN_DIR . 'inc/schema/schema-builder.php';

		// Loads pro files and other pro init stuff.
		if ( AIOSEOPPRO ) {
			require_once AIOSEOP_PLUGIN_DIR . 'pro/class-aio-pro-init.php';
		}
	}

	/**
	 * Set ini memory limit.
	 *
	 * Set by the Performance settings to adjust the memory limit on the system ini config.
	 *
	 * TODO This could be moved to the performance module, but may need the ability to fire early (before other operations occur).
	 * TODO Should this also set the execution time even if the mem_limit is empty (both are set by the same module options).
	 * TODO Add Try/Catch for ini_set() & ini_time_limit().
	 *
	 * @since 3.4
	 *
	 * @param string $memory_limit   Amount of memory to set the memory limit to.
	 * @param string $execution_time Amount of time to set the timeout to.
	 * @return string
	 */
	private function set_mem_limit( $memory_limit, $execution_time ) {
		// @codingStandardsIgnoreStart
		$aioseop_mem_limit = @ini_get( 'memory_limit' );
		// @codingStandardsIgnoreEnd

		if ( ! empty( $memory_limit ) ) {
			if ( ! empty( $execution_time ) ) {
				// @codingStandardsIgnoreStart
				@ini_set( 'max_execution_time', (int) $execution_time );
				@set_time_limit( (int) $execution_time );
				// @codingStandardsIgnoreEnd
			}
		} else {
			$aioseop_mem_limit = $this->convert_bytestring( $aioseop_mem_limit );
			if ( ( $aioseop_mem_limit > 0 ) && ( $aioseop_mem_limit < AIOSEOP_BASELINE_MEM_LIMIT ) ) {
				$aioseop_mem_limit = AIOSEOP_BASELINE_MEM_LIMIT;
			}
		}

		if ( ! empty( $aioseop_mem_limit ) ) {
			if ( ! is_int( $aioseop_mem_limit ) ) {
				$aioseop_mem_limit = $this->convert_bytestring( $aioseop_mem_limit );
			}
			if ( ( $aioseop_mem_limit > 0 ) && ( $aioseop_mem_limit <= AIOSEOP_BASELINE_MEM_LIMIT ) ) {
				// @codingStandardsIgnoreStart
				@ini_set( 'memory_limit', $aioseop_mem_limit );
				// @codingStandardsIgnoreEnd
			}
		}

		return $aioseop_mem_limit;
	}

	/**
	 * Add Hooks.
	 *
	 * @since 3.4
	 */
	public function add_hooks() {
		add_action( 'plugins_loaded', array( $this, 'add_cap' ) );

		add_action( 'init', 'aioseop_load_modules', 1 );

		if ( aioseop_option_isset( 'aiosp_unprotect_meta' ) ) {
			add_filter( 'is_protected_meta', 'aioseop_unprotect_meta', 10, 3 );
		}

		// phpcs:ignore Squiz.Commenting.InlineComment.InvalidEndChar
		// add_action( 'after_setup_theme', 'aioseop_load_modules' );

		if ( AIOSEOPPRO ) {
			add_action( 'admin_head', array( $this, 'disable_all_in_one_free' ), 1 );
		}

		// TODO vv Move to aioseop_admin class.
		add_filter( 'plugin_row_meta', array( $this, 'plugin_row_meta' ), 10, 2 );
		add_filter( 'plugin_action_links_' . AIOSEOP_PLUGIN_BASENAME, array( $this, 'add_action_links' ), 10, 2 );
		if ( is_admin() || defined( 'AIOSEOP_UNIT_TESTING' ) ) {
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_styles' ) );

			$file_dir = AIOSEOP_PLUGIN_DIR . 'all_in_one_seo_pack.php';
			register_activation_hook( $file_dir, array( 'AIOSEOP_Core', 'activate' ) );

			// TODO Move AJAX to aioseop_admin class, and could be a separate function hooked onto admin_init.
			add_action( 'wp_ajax_aioseop_ajax_save_meta', 'aioseop_ajax_save_meta' );
			add_action( 'wp_ajax_aioseop_ajax_save_url', 'aioseop_ajax_save_url' );
			add_action( 'wp_ajax_aioseop_ajax_delete_url', 'aioseop_ajax_delete_url' );
			add_action( 'wp_ajax_aioseop_ajax_scan_header', 'aioseop_ajax_scan_header' );
			add_action( 'wp_ajax_aioseop_ajax_save_settings', 'aioseop_ajax_save_settings' );
			add_action( 'wp_ajax_aioseop_ajax_get_menu_links', 'aioseop_ajax_get_menu_links' );
			add_action( 'wp_ajax_aioseo_dismiss_yst_notice', 'aioseop_update_yst_detected_notice' );
			add_action( 'wp_ajax_aioseo_dismiss_visibility_notice', 'aioseop_update_user_visibilitynotice' );
			add_action( 'wp_ajax_aioseo_dismiss_woo_upgrade_notice', 'aioseop_woo_upgrade_notice_dismissed' );

			if ( AIOSEOPPRO ) {
				add_action( 'wp_ajax_aioseop_ajax_facebook_debug', 'aioseop_ajax_facebook_debug' );
			}
		}
		// TODO ^^ Move to aioseop_admin class.
	}

	/**
	 * AIOSEOP Activate
	 *
	 * @since ?
	 */
	public static function activate() {
		// Check if we just got activated.
		global $aiosp_activation;
		$aiosp_activation = true;

		// phpcs:disable
		// require_once AIOSEOP_PLUGIN_DIR . 'admin/class-aioseop-notices.php';
		// global $aioseop_notices;
		// $aioseop_notices->reset_notice( 'review_plugin' );
		// phpcs:enable

		// These checks might be duplicated in the function being called.
		if ( ! is_network_admin() || ! isset( $_GET['activate-multi'] ) ) {
			set_transient( '_aioseop_activation_redirect', true, 30 ); // Sets 30 second transient for welcome screen redirect on activation.
		}

		delete_user_meta( get_current_user_id(), 'aioseop_yst_detected_notice_dismissed' );

		if ( AIOSEOPPRO ) {
			global $aioseop_options;
			global $aioseop_update_checker;

			$aioseop_update_checker->checkForUpdates();

			if (
					isset( $aioseop_options['modules']['aiosp_feature_manager_options']['aiosp_feature_manager_enable_video_sitemap'] ) &&
					'on' === $aioseop_options['modules']['aiosp_feature_manager_options']['aiosp_feature_manager_enable_video_sitemap']
			) {
				$next_scan_timestamp = wp_next_scheduled( 'aiosp_video_sitemap_scan' );
				if ( false !== $next_scan_timestamp && 10 < ( $next_scan_timestamp - time() ) ) {
					// Reschedule cron job to avoid waiting for next (daily) scan.
					wp_unschedule_event( $next_scan_timestamp, 'aiosp_video_sitemap_scan' );
					$next_scan_timestamp = false;
				}

				if ( false === $next_scan_timestamp ) {
					wp_schedule_single_event( time() + 10, 'aiosp_video_sitemap_scan' );
				}
			}
		}
	}

	/**
	 * Disable AIOSEOP Free version.
	 *
	 * @since ?
	 */
	public function disable_all_in_one_free() {
		if ( AIOSEOPPRO && is_plugin_active( 'all-in-one-seo-pack/all_in_one_seo_pack.php' ) ) {
			deactivate_plugins( 'all-in-one-seo-pack/all_in_one_seo_pack.php' );
		}
	}

	/**
	 * Admin Enqueue Styles
	 *
	 * Styles used in various parts of WordPress admin, and not just AIOSEOP's screens.
	 * Note: If styles are specific to a given module, then use that module's admin_enqueue_styles() method, or parent method.
	 *
	 * @since 3.0
	 *
	 * @see 'admin_enqueue_scripts' hook
	 * @link https://developer.wordpress.org/reference/hooks/admin_enqueue_scripts/
	 *
	 * @param string $hook_suffix
	 */
	public function admin_enqueue_styles( $hook_suffix ) {
		// Font Icons.
		if ( ! wp_style_is( 'aioseop-font-icons', 'registered' ) && ! wp_style_is( 'aioseop-font-icons', 'enqueued' ) ) {
			wp_enqueue_style(
				'aioseop-font-icons',
				AIOSEOP_PLUGIN_URL . 'css/aioseop-font-icons.css',
				array(),
				AIOSEOP_VERSION
			);
		}

		// Left-to-Right css.
		if ( function_exists( 'is_rtl' ) && is_rtl() ) {
			if ( ! wp_style_is( 'aioseop-font-icons-rtl', 'registered' ) && ! wp_style_is( 'aioseop-font-icons-rtl', 'enqueued' ) ) {
				wp_enqueue_style(
					'aioseop-font-icons-rtl',
					AIOSEOP_PLUGIN_URL . 'css/aioseop-font-icons-rtl.css',
					array(),
					AIOSEOP_VERSION
				);
			}
		}
	}

	/**
	 * AIOSEOP Add Capabilities
	 *
	 * @since 2.3.6
	 */
	public function add_cap() {
		$role = get_role( 'administrator' );
		if ( is_object( $role ) ) {
			$role->add_cap( 'aiosp_manage_seo' );
		}
	}

	/**
	 * Scan Post Header
	 *
	 * TODO Move to Opengraph module when AJAX-like operations in \AIOSEOP_Core::init() are refactored as well.
	 *
	 * @since ?
	 */
	public function scan_post_header() {
		require_once ABSPATH . WPINC . '/default-filters.php';
		global $wp_query;
		$wp_query->query_vars['paged'] = 0;
		query_posts( 'post_type=post&posts_per_page=1' );

		if ( have_posts() ) {
			the_post();
		}
	}

	/**
	 * AIOSEOP Convert Bytestring
	 *
	 * TODO Should this be in a functions file?
	 *
	 * @since ?
	 *
	 * @param $byte_string
	 * @return int
	 */
	private function convert_bytestring( $byte_string ) {
		$num = 0;
		preg_match( '/^\s*([0-9.]+)\s*([KMGTPE])B?\s*$/i', $byte_string, $matches );
		if ( ! empty( $matches ) ) {
			$num = (float) $matches[1];
			switch ( strtoupper( $matches[2] ) ) {
				case 'E':
					$num *= 1024;
					// fall through.
				case 'P':
					$num *= 1024;
					// fall through.
				case 'T':
					$num *= 1024;
					// fall through.
				case 'G':
					$num *= 1024;
					// fall through.
				case 'M':
					$num *= 1024;
					// fall through.
				case 'K':
					$num *= 1024;
			}
		}

		return intval( $num );
	}

	/**
	 * AIOSEOP Plugin Row Meta
	 *
	 * @since 2.3.3
	 *
	 * @uses `plugin_row_meta` hook.
	 * @link https://developer.wordpress.org/reference/hooks/plugin_row_meta/
	 *
	 * @param $actions
	 * @param $plugin_file
	 * @return array
	 */
	public function plugin_row_meta( $actions, $plugin_file ) {
		$action_links = array(
			'settings' => array(
				/* translators: This is an action link users can click to open a feature request/bug report on GitHub. */
				'label' => __( 'Feature Request/Bug Report', 'all-in-one-seo-pack' ),
				'url'   => 'https://github.com/semperfiwebdesign/all-in-one-seo-pack/issues/new',
			),

		);

		return $this->action_links( $actions, $plugin_file, $action_links, 'after' );
	}

	/**
	 * AIOSEOP Add Action Links
	 *
	 * Adds additional links to the plugin on the admin Plugins page.
	 *
	 * @since 2.3
	 *
	 * @param $actions
	 * @param $plugin_file
	 * @return array
	 */
	public function add_action_links( $actions, $plugin_file ) {
		if ( ! is_array( $actions ) ) {
			return $actions;
		}

		$aioseop_plugin_dirname = AIOSEOP_PLUGIN_DIRNAME;
		$action_links           = array(
			'settings' => array(
				/* translators: This is an action link users can click to open the General Settings menu. */
				'label' => __( 'SEO Settings', 'all-in-one-seo-pack' ),
				'url'   => get_admin_url( null, "admin.php?page=$aioseop_plugin_dirname/aioseop_class.php" ),
			),

			'forum'    => array(
				/* translators: This is an action link users can click to open our premium support forum. */
				'label' => __( 'Support Forum', 'all-in-one-seo-pack' ),
				'url'   => 'https://semperplugins.com/support/',
			),

			'docs'     => array(
				/* translators: This is an action link users can click to open our general documentation page. */
				'label' => __( 'Documentation', 'all-in-one-seo-pack' ),
				'url'   => 'https://semperplugins.com/documentation/',
			),

		);

		unset( $actions['edit'] );

		if ( ! AIOSEOPPRO ) {
			$action_links['proupgrade'] = array(
				/* translators: This is an action link users can click to purchase a license for All in One SEO Pack Pro. */
				'label' => __( 'Upgrade to Pro', 'all-in-one-seo-pack' ),
				'url'   => 'https://semperplugins.com/plugins/all-in-one-seo-pack-pro-version/?loc=plugins',

			);
		}

		return $this->action_links( $actions, $plugin_file, $action_links, 'before' );
	}

	/**
	 * AIOSEOP Action Links
	 *
	 * @since 2.3
	 *
	 * @param $actions
	 * @param $plugin_file
	 * @param array $action_links
	 * @param string $position
	 * @return array
	 */
	public function action_links( $actions, $plugin_file, $action_links = array(), $position = 'after' ) {
		static $plugin;

		if ( ! isset( $plugin ) ) {
			$plugin = AIOSEOP_PLUGIN_BASENAME;
		}
		if ( $plugin === $plugin_file && ! empty( $action_links ) ) {
			foreach ( $action_links as $key => $value ) {
				$link = array( $key => '<a href="' . $value['url'] . '">' . $value['label'] . '</a>' );
				if ( 'after' === $position ) {
					$actions = array_merge( $actions, $link );
				} else {
					$actions = array_merge( $link, $actions );
				}
			}
		}

		return $actions;
	}

	/**
	 * AIOSEOP's Welcome Page
	 *
	 * @since ?
	 */
	public function aioseop_welcome() {
		if ( get_transient( '_aioseop_activation_redirect' ) ) {
			$aioseop_welcome = new aioseop_welcome();
			delete_transient( '_aioseop_activation_redirect' );
			$aioseop_welcome->init( true );
		}
	}

	/**
	 * Admin Notices Already Defined
	 *
	 * @since ?
	 *
	 * @throws ReflectionException
	 */
	public function admin_notices_already_defined() {
		$text = '';
		if ( class_exists( 'ReflectionClass' ) ) {
			$_r   = new ReflectionClass( 'All_in_One_SEO_Pack' );
			$text = ' in ' . $_r->getFileName();
		}

		echo '<div class="error">The All In One SEO Pack class is already defined' . $text . ', preventing All In One SEO Pack from loading.</div>';
	}

	/**
	 * Review Plugin Notice
	 *
	 * Activates the review notice.
	 * Note: This couldn't be used directly in `aioseop_init_class()` since ajax instances was causing
	 * the database options to reset.
	 *
	 * @since 3.0
	 */
	public function review_plugin_notice() {
		global $aioseop_notices;
		// $aioseop_notices->activate_notice( 'review_plugin' );
	}

}
