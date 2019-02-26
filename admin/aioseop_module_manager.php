<?php
/**
 * The Module Manager.
 *
 * Mostly we're activating and deactivating modules/features.
 *
 * @package All-in-One-SEO-Pack
 * @since 2.0
 */

if ( ! class_exists( 'All_in_One_SEO_Pack_Module_Manager' ) ) {

	/**
	 * Class All_in_One_SEO_Pack_Module_Manager
	 */
	class All_in_One_SEO_Pack_Module_Manager {
		protected $modules = array();
		protected $settings_update = false;
		protected $settings_reset = false;
		protected $settings_reset_all = false;
		protected $module_settings_update = false;

		/**
		 * All_in_One_SEO_Pack_Module_Manager constructor.
		 *
		 * Initialize module list.
		 *
		 * @param $mod Modules.
		 */
		function __construct( $mod ) {

			$this->modules['feature_manager'] = null;
			foreach ( $mod as $m ) {
				$this->modules[ $m ] = null;
			}
			$reset     = false;
			$reset_all = ( isset( $_POST['Submit_All_Default'] ) && '' !== $_POST['Submit_All_Default'] );
			$reset     = ( ( isset( $_POST['Submit_Default'] ) && '' !== $_POST['Submit_Default'] ) || $reset_all );
			$update    = ( isset( $_POST['action'] ) && $_POST['action']
						   && ( ( isset( $_POST['Submit'] ) && '' !== $_POST['Submit'] ) || $reset )
			);
			if ( $update ) {
				if ( $reset ) {
					$this->settings_reset = true;
				}
				if ( $reset_all ) {
					$this->settings_reset_all = true;
				}
				if ( 'aiosp_update' === $_POST['action'] ) {
					$this->settings_update = true;
				}
				if ( 'aiosp_update_module' === $_POST['action'] ) {
					$this->module_settings_update = true;
				}
			}
			$this->do_load_module( 'feature_manager', $mod );
		}

		/**
		 * Return module.
		 *
		 * @param $class
		 *
		 * @return $this|bool|mixed
		 */
		function return_module( $class ) {
			global $aiosp;
			/* This is such a strange comparison! Don't know what the intent is. */
			if ( get_class( $aiosp ) === $class ) {
				return $aiosp;
			}
			if ( get_class( $aiosp ) === $class ) {
				return $this;
			}
			foreach ( $this->modules as $m ) {
				if ( is_object( $m ) && ( get_class( $m ) === $class ) ) {
					return $m;
				}
			}

			return false;
		}

		/**
		 * @return array
		 */
		function get_loaded_module_list() {
			$module_list = array();
			if ( ! empty( $this->modules ) ) {
				foreach ( $this->modules as $k => $v ) {
					if ( ! empty( $v ) ) {
						$module_list[ $k ] = get_class( $v );
					}
				}
			}

			return $module_list;
		}

		/**
		 * @param $mod Module.
		 * @param null $args
		 *
		 * @return bool
		 */
		function do_load_module( $mod, $args = null ) {
			// Module name is used for these automatic settings:
			// The aiosp_enable_$module settings - whether each plugin is active or not.
			// The name of the .php file containing the module - aioseop_$module.php.
			// The name of the class - All_in_One_SEO_Pack_$Module.
			// The global $aioseop_$module.
			// $this->modules[$module].
			$mod_path = apply_filters( "aioseop_include_$mod", AIOSEOP_PLUGIN_DIR . "modules/aioseop_$mod.php" );
			if ( ! empty( $mod_path ) ) {
				require_once( $mod_path );
			}
			$ref                   = "aioseop_$mod";
			$classname             = 'All_in_One_SEO_Pack_' . strtr( ucwords( strtr( $mod, '_', ' ' ) ), ' ', '_' );
			$classname             = apply_filters( "aioseop_class_$mod", $classname );
			$module_class          = new $classname( $args );
			$GLOBALS[ $ref ]       = $module_class;
			$this->modules[ $mod ] = $module_class;
			if ( is_user_logged_in() && is_admin_bar_showing() && current_user_can( 'aiosp_manage_seo' ) ) {
				add_action(
					'admin_bar_menu', array(
						$module_class,
						'add_admin_bar_submenu',
					), 1001 + $module_class->menu_order()
				);
			}
			if ( is_admin() ) {
				add_action(
					'aioseop_modules_add_menus', array(
						$module_class,
						'add_menu',
					), $module_class->menu_order()
				);
				add_action( 'aiosoep_options_reset', array( $module_class, 'reset_options' ) );
				add_filter( 'aioseop_export_settings', array( $module_class, 'settings_export' ) );
			}

			return true;
		}

		/**
		 * @param $mod
		 *
		 * @return bool
		 */
		function load_module( $mod ) {
			static $feature_options = null;
			static $feature_prefix = null;
			if ( ! is_array( $this->modules ) ) {
				return false;
			}
			$v = $this->modules[ $mod ];
			if ( null !== $v ) {
				return false;
			}    // Already loaded.
			if ( 'performance' === $mod && ! is_super_admin() ) {
				return false;
			}
			if ( ( 'file_editor' === $mod )
				 && ( ( defined( 'DISALLOW_FILE_EDIT' ) && DISALLOW_FILE_EDIT )
					  || ( defined( 'DISALLOW_FILE_MODS' ) && DISALLOW_FILE_MODS )
					  || ! is_super_admin() )
			) {
				return false;
			}
			$mod_enable = false;

			$is_module_page = isset( $_REQUEST['page'] ) && trailingslashit( AIOSEOP_PLUGIN_DIRNAME ) . 'modules/aioseop_feature_manager.php' === $_REQUEST['page'];
			if ( defined( 'AIOSEOP_UNIT_TESTING' ) ) {
				// using $_REQUEST does not work because even if the parameter is set in $_POST or $_GET, it does not percolate to $_REQUEST.
				$is_module_page = ( isset( $_GET['page'] ) && trailingslashit( AIOSEOP_PLUGIN_DIRNAME ) . 'modules/aioseop_feature_manager.php' === $_GET['page'] ) || ( isset( $_POST['page'] ) && trailingslashit( AIOSEOP_PLUGIN_DIRNAME ) . 'modules/aioseop_feature_manager.php' === $_POST['page'] );
			}
			$fm_page    = $this->module_settings_update && wp_verify_nonce( $_POST['nonce-aioseop'], 'aioseop-nonce' ) && $is_module_page;
			if ( $fm_page && ! $this->settings_reset ) {
				if ( isset( $_POST[ "aiosp_feature_manager_enable_$mod" ] ) ) {
					$mod_enable = $_POST[ "aiosp_feature_manager_enable_$mod" ];
				} else {
					$mod_enable = false;
				}
			} else {
				if ( null === $feature_prefix ) {
					$feature_prefix = $this->modules['feature_manager']->get_prefix();
				}
				if ( $fm_page && $this->settings_reset ) {
					$feature_options = $this->modules['feature_manager']->default_options();
				}
				if ( null === $feature_options ) {
					if ( $this->module_settings_update && $this->settings_reset_all && wp_verify_nonce( $_POST['nonce-aioseop'], 'aioseop-nonce' ) ) {
						$feature_options = $this->modules['feature_manager']->default_options();
					} else {
						$feature_options = $this->modules['feature_manager']->get_current_options();
					}
				}
				if ( isset( $feature_options[ "{$feature_prefix}enable_$mod" ] ) ) {
					$mod_enable = $feature_options[ "{$feature_prefix}enable_$mod" ];
				}
			}
			if ( $mod_enable ) {
				return $this->do_load_module( $mod );
			}

			return false;
		}

		function load_modules() {
			if ( is_array( $this->modules ) ) {
				foreach ( $this->modules as $k => $v ) {
					$this->load_module( $k );
				}
			}
		}
	}
}
