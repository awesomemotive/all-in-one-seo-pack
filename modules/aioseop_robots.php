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
			// only for testing
			/*
			if ( ! defined( 'AIOSEOP_DO_LOG' ) ) {
				define( 'AIOSEOP_DO_LOG', true );
			}
			*/

			$this->name   = __( 'Robots.txt', 'all-in-one-seo-pack' );    // Human-readable name of the plugin
			$this->prefix = 'aiosp_robots_';                        // option prefix
			$this->file   = __FILE__;                                    // the current file
			parent::__construct();

			$help_text = array(
				'type' => __( 'Rule Type', 'all-in-one-seo-pack' ),
				'agent'  => __( 'User Agent', 'all-in-one-seo-pack' ),
				'path'       => __( 'Directory Path', 'all-in-one-seo-pack' ),
			);

			$this->default_options = array(
				'usage'              => array(
					'type'    => 'html',
					'label'   => 'none',
					'default' => __( 'Use the rule builder below to add/delete rules.', 'all-in-one-seo-pack' ),
					'save'    => false,
				),
			);

			$this->rule_fields      = array(
				'agent'         => array(
					'name'            => __( 'User Agent', 'all-in-one-seo-pack' ),
					'type'            => 'text',
					'label'           => 'top',
					'save'            => false,
				),
				'type'          => array(
					'name'  => __( 'Rule', 'all-in-one-seo-pack' ),
					'type'  => 'select',
					'initial_options' => array( 'allow' => __( 'Allow', 'all-in-one-seo-pack' ), 'disallow' => __( 'Disallow', 'all-in-one-seo-pack' ) ),
					'label' => 'top',
					'save'  => false,
				),
				'path'         => array(
					'name'            => __( 'Directory Path', 'all-in-one-seo-pack' ),
					'type'            => 'text',
					'label'           => 'top',
					'save'            => false,
				),
				'Submit'            => array(
					'type'  => 'submit',
					'class' => 'button-primary add-edit-rule',
					'name'  => __( 'Add Rule', 'all-in-one-seo-pack' ) . ' &raquo;',
					'style' => 'margin-left: 20px;',
					'label' => 'none',
					'save'  => false,
					'value' => 1,
				),
				"{$this->prefix}id"            => array(
					'type'  => 'hidden',
					'class' => 'edit-rule-id',
					'save'  => false,
					'value' => '',
				),
				'rules'        => array(
					'name' => __( 'Configured Rules', 'all-in-one-seo-pack' ),
					'type' => 'custom',
					'save' => true,
				),
				'robots.txt'        => array(
					'name' => __( 'Robots.txt', 'all-in-one-seo-pack' ),
					'type' => 'custom',
					'save' => true,
				),
			);

			add_filter( $this->prefix . 'submit_options', array( $this, 'submit_options'), 10, 2 );

			$this->default_options = array_merge( $this->default_options, $this->rule_fields );

			if ( ! empty( $help_text ) ) {
				foreach ( $help_text as $k => $v ) {
					$this->default_options[ $k ]['help_text'] = $v;
				}
			}

			$this->layout             = array(
				'default' => array(
					'name'    => __( 'Create a Robots.txt File', 'all-in-one-seo-pack' ),
					'help_link' => 'https://semperplugins.com/documentation/robots-txt-module/',
					'options' => array_merge( array( 'usage' ), array_keys( $this->rule_fields ) ),
				),
			);

			// load initial options / set defaults
			$this->update_options();

			add_filter( $this->prefix . 'output_option', array( $this, 'display_custom_options' ), 10, 2 );
			add_filter( $this->prefix . 'update_options', array( $this, 'filter_options' ) );
			add_filter( $this->prefix . 'display_options', array( $this, 'filter_display_options' ) );
			add_action( 'wp_ajax_aioseop_ajax_delete_rule', array( $this, 'ajax_delete_rule' ) );
			add_action( 'wp_ajax_aioseop_ajax_robots_physical', array( $this, 'ajax_action_physical_file' ) );
			add_filter( 'robots_txt', array( $this, 'robots_txt' ), 10, 2 );

			// We want to define this because calling admin init in the unit tests causes an error and does not call this method.
			if ( defined( 'AIOSEOP_UNIT_TESTING' ) ) {
				add_action( "aioseop_ut_{$this->prefix}admin_init", array( $this, 'import_default_robots' ) );
			}
		}

		function physical_file_check() {
			if ( $this->has_physical_file() ) {
				if ( ( is_multisite() && is_network_admin() ) || ( ! is_multisite() && current_user_can( 'manage_options' ) ) ) {
					// @codingStandardsIgnoreStart
					$this->default_options['usage']['default'] .= '<div id="aiosp_robots_physical_import_delete"><p>' . sprintf( __( 'A physical file exists. Do you want to %simport and delete%s it, %sdelete%s it or continue using it?', 'all-in-one-seo-pack' ), '<a href="#" class="aiosp_robots_physical aiosp_robots_import" data-action="import">', '</a>', '<a href="#" class="aiosp_robots_physical aiosp_robots_delete" data-action="delete">', '</a>' ) . '</p></div>';
					// @codingStandardsIgnoreStop
				} else {
					$this->default_options['usage']['default'] .= '<p>' . __( 'A physical file exists. This feature cannot be used.', 'all-in-one-seo-pack' ) . '</p>';
				}

				return;
			} else {
				add_action( 'admin_init', array( $this, 'import_default_robots' ) );
			}
		}

		function filter_display_options( $options ) {
			$errors = get_transient( "{$this->prefix}errors" . get_current_user_id() );
			if ( false !== $errors ) {
				if ( is_array( $errors ) ) {
					$errors = implode( '<br>', $errors );
				}
				echo sprintf( '<div class="notice notice-error"><p>%s</p></div>', $errors );
			}
			return $options;
		}

		/**
		 * First time import of the default robots.txt rules.
		 */
		function import_default_robots() {
			$options = $this->get_option_for_blog( $this->get_network_id() );
			if ( array_key_exists( 'default', $options ) ) {
				return;
			}

			$default = $this->do_robots();
			$lines = explode( "\n", $default );
			$rules = $this->extract_rules( $lines );
			aiosp_log("adding default rules: " . print_r($rules,true));

			global $aioseop_options;
			$aioseop_options['modules']["{$this->prefix}options"]['default'] = $rules;
			update_option( 'aioseop_options', $aioseop_options );
		}

		function submit_options( $submit_options, $location ) {
			unset( $submit_options['Submit'] );
			unset( $submit_options['Submit_Default'] );
			return $submit_options;
		}

		function ajax_action_physical_file() {
			aioseop_ajax_init();
			$action = $_POST['options'];

			switch ( $action ) {
				case 'import':
					$this->import_default_robots();
					if ( ! $this->import_physical_file() ) {
						wp_send_json_success( array( 'message' => __( 'Unable to read file', 'all-in-one-seo-pack' ) ) );
					}
					// fall-through.
				case 'delete':
					if ( ! $this->delete_physical_file() ) {
						wp_send_json_success( array( 'message' => __( 'Unable to delete file', 'all-in-one-seo-pack' ) ) );
					}
					break;
			}

			wp_send_json_success();
		}

		private function import_physical_file() {
			$wp_filesystem = $this->get_filesystem_object();
			$file = trailingslashit( $wp_filesystem->abspath() ) . 'robots.txt';
			if ( ! $wp_filesystem->is_readable( $file ) ) {
				return false;
			}

			$lines = $wp_filesystem->get_contents_array( $file );
			if ( ! $lines ) {
				return true;
			}

			$rules = $this->extract_rules( $lines );
			aiosp_log("importing rules: " . print_r($rules,true));

			global $aioseop_options;
			$aioseop_options['modules']["{$this->prefix}options"]["{$this->prefix}rules"] = $rules;
			update_option( 'aioseop_options', $aioseop_options );
			return true;
		}

		private function extract_rules( array $lines ) {
			$rules = array();
			$user_agent = null;
			$rule = array();
			$blog_rules = $this->get_all_rules();
			foreach ( $lines as $line ) {
				if ( empty( $line ) ) {
					continue;
				}
				$array = array_map( 'trim', explode( ':', $line ) );
				if ( $array && count( $array ) !== 2 ) {
					aiosp_log( "Ignoring $line from robots.txt" );
					continue;
				}
				$operand = $array[0];
				switch ( strtolower( $operand ) ) {
					case 'user-agent':
						$user_agent = $array[1];
						break;
					case 'disallow':
						// fall-through.
					case 'allow':
						$rule[ 'agent' ] = $user_agent;
						$rule[ 'type' ] = $operand;
						$rule[ 'path' ] = $array[1];
						break;
				}
				if ( $rule ) {
					$rule	= $this->validate_rule( $blog_rules, $rule );
					if ( is_wp_error( $rule ) ) {
						$this->add_error( $rule );
					} else {
						$rules[] = $rule;
					}
					$rule = array();
				}
			}
			return $rules;
		}

		private function delete_physical_file() {
			$wp_filesystem = $this->get_filesystem_object();
			$file = trailingslashit( $wp_filesystem->abspath() ) . 'robots.txt';
			return $wp_filesystem->delete( $file );
		}

		private function has_physical_file() {
			$access_type = get_filesystem_method();

			if ( 'direct' === $access_type ) {
				$wp_filesystem = $this->get_filesystem_object();
				$file          = trailingslashit( $wp_filesystem->abspath() ) . 'robots.txt';

				return $wp_filesystem->exists( $file );
			}
		}

		function robots_txt( $output, $public ) {
			return $output . "\r\n" . $this->get_rules();
		}

		private function get_rules() {
			$robots		= array();
			$blog_rules	= $this->get_all_rules( is_multisite() ? $this->get_network_id() : null );
			if ( is_multisite() && $this->get_network_id() != get_current_blog_id() ) {
				$blog_rules = array_merge( $blog_rules, $this->get_all_rules( get_current_blog_id() ) );
			}
			$rules		= array();
			foreach ( $blog_rules as $rule ) {
				$condition	= sprintf( '%s: %s', $rule['type'], $rule['path'] );
				$agent		= $rule['agent'];
				if ( ! array_key_exists( $agent, $rules ) ) {
					$rules[$agent]	= array();
				}
				$rules[ $agent ][]	= $condition;
			}

			foreach( $rules as $agent => $conditions ) {
				$robots[]	= sprintf( 'User-agent: %s', $agent );
				$robots[]	= implode( "\r\n", $conditions );
				$robots[]	= "";
			}
			return implode( "\r\n", $robots );
		}

		private function get_network_id() {
			if ( is_multisite() ) {
				return get_network()->site_id;
			}
			return get_current_blog_id();
		}

		private function get_option_for_blog( $id = null ) {
			if ( is_null( $id ) ) {
				$id = get_current_blog_id();
			}
			if ( is_multisite() ) {
				switch_to_blog( $id );
			}
			$options = get_option('aioseop_options');
			if ( is_multisite() ) {
				restore_current_blog();
			}
			return array_key_exists( 'modules', $options ) && array_key_exists( "{$this->prefix}options", $options['modules'] ) ? $options['modules']["{$this->prefix}options"] : array();
		}

		/**
		 * Get all rules defined for the blog.
		 */
		private function get_all_rules( $id = null ) {
			$options = $this->get_option_for_blog( $id );
			return array_key_exists( "{$this->prefix}rules", $options ) ? $options[ "{$this->prefix}rules" ] : array();
		}

		/**
		 * Get the default robot rules that were saved in the first initialization.
		 */
		private function get_default_rules() {
			$options = $this->get_option_for_blog( $this->get_network_id() );
			return array_key_exists( 'default', $options ) ? $options[ 'default' ] : array();
		}

		function ajax_delete_rule() {
			aioseop_ajax_init();
			$id = $_POST['options'];

			$this->delete_rule( $id );
		}

		private function delete_rule( $id ) {
			global $aioseop_options;

			$deleted_rule	= null;
			// first check the defined rules.
			$blog_rules	= $this->get_all_rules();
			$rules = array();
			foreach ( $blog_rules as $rule ) {
				if ( $id === $rule['id'] ) {
					$deleted_rule	= $rule;
					continue;
				}
				$rules[] = $rule;
			}
			$aioseop_options['modules']["{$this->prefix}options"]["{$this->prefix}rules"] = $rules;
			update_option( 'aioseop_options', $aioseop_options );
			return $deleted_rule;
		}

		private function add_error( $error ) {
			$errors = get_transient( "{$this->prefix}errors" . get_current_user_id() );
			if ( false === $errors ) {
				$errors = array();
			}
			$errors[] = $error->get_error_message();
			// set the error in a transient.
			set_transient( "{$this->prefix}errors" . get_current_user_id(), $errors, 5 );
		}

		/**
		 * Filter options.
		 *
		 * @param $options
		 *
		 * @return mixed
		 */
		function filter_options( $options ) {	
			$modify		= isset( $_POST[ "{$this->prefix}id" ] ) && ! empty( $_POST[ "{$this->prefix}id" ] );
			$deleted_rule = null;
			if ( $modify ) {
				// let's first delete the original rule and save it temporarily so that we can add it back in case of an error with the new rule.
				$deleted_rule	= $this->delete_rule( $_POST[ "{$this->prefix}id" ] );
			}
			
			$blog_rules = $this->get_all_rules();

			if ( ! empty( $_POST[ "{$this->prefix}path" ] ) ) {
				foreach ( array_keys( $this->rule_fields ) as $field ) {
					$post_field	= $this->prefix . "" . $field;
					if ( ! empty( $_POST[ $post_field ] ) ) {
						$_POST[ $post_field ] = esc_attr( wp_kses_post( $_POST[ $post_field ] ) );
					} else {
						$_POST[ $post_field ] = '';
					}
				}
				$new_rule = array(
					'path' => $_POST[ "{$this->prefix}path" ],
					'type' => $_POST[ "{$this->prefix}type" ],
					'agent' => $_POST[ "{$this->prefix}agent" ],
				);
				$rule	= $this->validate_rule( $blog_rules, $new_rule );
				if ( is_wp_error( $rule ) ) {
					$this->add_error( $rule );
					if ( $deleted_rule ) {
						$blog_rules[] = $deleted_rule;
					}
				} else {
					$blog_rules[] = $rule;
				}
			}
			// testing only - to clear the rules.
			//$blog_rules = array();
			$options[ "{$this->prefix}rules" ] = $blog_rules;
			return $options;
		}

		private function sanitize_path( $path ) {
			// if path does not have a trailing wild card (*) or does not refer to a file (with extension), add trailing slash.
			if ( '*' !== substr( $path, -1 ) && false === strpos( $path, '.' ) ) {
				$path = trailingslashit( $path );
			}

			// if path does not have a leading slash, add it.
			if ( '/' !== substr( $path, 0, 1 ) ) {
				$path = '/' . $path;
			}

			// convert everything to lower case.
			$path = strtolower( $path );

			return $path;
		}

		private function create_rule_id( $type, $agent, $path ) {
			return md5( $type . $agent . $path );
		}

		private function validate_rule( $rules, $new_rule ) {
			if ( empty( $new_rule[ 'agent' ] ) ) {
				return new WP_Error('invalid', __( 'User Agent cannot be empty', 'all-in-one-seo-pack' ) );
			}
			if ( empty( $new_rule[ 'path' ] ) ) {
				return new WP_Error('invalid', __( 'Directory Path cannot be empty', 'all-in-one-seo-pack' ) );
			}

			$default = $this->get_default_rules();
			$network = $this->get_all_rules( $this->get_network_id() );
			if ( ! is_array( $network ) ) {
				$network = array();
			}
			$network = array_merge( $default, $network, $rules );

			// sanitize path.
			$path = $this->sanitize_path( $new_rule[ 'path' ] );

			// generate id to check uniqueness and also for purposes of deletion.
			$id = $this->create_rule_id( $new_rule[ 'type' ],  $new_rule[ 'agent' ],  $path );
			if ( is_array( $rules ) ) {
				$ids = wp_list_pluck( $rules, 'id' );
				if ( in_array( $id, $ids ) ) {
					aiosp_log("rejected: same rule id exists - " . print_r($new_rule,true) . " vs. " . print_r($rules,true));
					return new WP_Error('duplicate', sprintf( __( 'Identical rule exists: %s', 'all-in-one-seo-pack' ), $new_rule[ 'path' ] ) );
				}
			}

			if ( $network ) {
				$nw_agent_paths = array();
				foreach ( $network as $n ) {
					$nw_agent_paths[] = $n['agent'] . $n['path'];
				}

				// the same rule cannot be duplicated by the Admin.
				$agent_path =  $new_rule[ 'agent' ] . $path;
				if ( in_array( $agent_path, $nw_agent_paths ) ) {
					aiosp_log("rejected: same agent/path being overridden - " . print_r($new_rule,true) . " vs. " . print_r($rules,true));
					return new WP_Error('duplicate', sprintf( __( 'Rule cannot be overridden: %s', 'all-in-one-seo-pack' ), $new_rule[ 'path' ] ) );
				}

				// an identical path as specified by Network Admin cannot be overriden by Admin.
				$nw_paths = wp_list_pluck( $network, 'path' );
				if ( in_array( $path, $nw_paths ) ) {
					aiosp_log("rejected: same path being overridden - " . print_r($new_rule,true) . " vs. " . print_r($rules,true));
					return new WP_Error('duplicate', sprintf( __( 'Path cannot be overridden: %s', 'all-in-one-seo-pack' ), $new_rule[ 'path' ] ) );
				}

				// a wild-carded path specified by the Admin cannot override a path specified by Network Admin.
				$pattern = str_replace(
					array(
						'.',
						'/',
						'*',
					),
					array(
						'\.',
						'\/',
						'(.*)',
					),
					$path
				);
				foreach ( $nw_paths as $nw_path ) {
					$matches = array();
					preg_match( "/{$pattern}/", $nw_path, $matches );
					if ( ! empty( $matches ) && count( $matches ) >= 2 && ! empty( $matches[1] ) ) {
						aiosp_log("rejected: wild card path being overridden - " . print_r($new_rule,true) . " vs. " . print_r($rules,true));
						return new WP_Error('conflict', sprintf( __( 'Wild-card path cannot be overridden: %s', 'all-in-one-seo-pack' ), $new_rule[ 'path' ] ) );
					}
				}
			}

			return array(
					'type' => ucwords( $new_rule[ 'type' ] ),
					'agent' => $new_rule[ 'agent' ],
					'path' => $path,
					'id' => $id,
			);
		}

		private function reorder_rules( $rules ) {
			if ( is_array( $rules ) ) {
				uasort( $rules, array( $this, 'sort_rules' ) );
			}
			return $rules;
		}

		function sort_rules( $a, $b ) {
			return $a['agent'] > $b['agent'];
		}

		private function get_display_rules( $rules ) {
			$buf = '';
			if ( ! empty( $rules ) ) {
				$rules = $this->reorder_rules( $rules );
				$buf = sprintf( "<table class='aioseop_table' cellpadding=0 cellspacing=0 data-edit-label='%s'>\n", __( 'Modify Rule', 'all-in-one-seo-pack' ) . ' &raquo;' );
				$row = "\t
					<tr>
						<td>
							<a href='#' class='dashicons dashicons-trash aiosp_robots_delete_rule' data-id='%s'></a>
							<a href='#' class='dashicons dashicons-edit aiosp_robots_edit_rule' data-id='%s' data-agent='%s' data-type='%s' data-path='%s'></a>
						</td>
						<td>%s</td>
						<td>%s</td>
						<td>%s</td>
					</tr>\n";
				foreach ( $rules as $v ) {
					$buf .= sprintf( $row, $v['id'], $v['id'], esc_attr( $v['agent'] ), esc_attr( strtolower( $v['type'] ) ), esc_attr( $v['path'] ), $v['agent'], $v['type'], $v['path'] );
				}
				$buf .= "</table>\n";
			}
			return $buf;
		}

		private function do_robots() {
			// disable header warnings.
			error_reporting(0);
			ob_start();
			do_action( 'do_robots' );
			if ( is_admin() ) {
				// conflict with WooCommerce etc. cause the page to render as text/plain.
				header( 'Content-Type:text/html' );
			}
			return ob_get_clean();
		}

		/**
		 * Custom settings.
		 *
		 * Displays boxes in a table layout.
		 *
		 * @param $buf
		 * @param $args
		 *
		 * @return string
		 */
		function display_custom_options( $buf, $args ) {
			switch ( $args['name'] ) {
				case "{$this->prefix}rules":
					$buf .= "<div id='{$this->prefix}rules'>";
					$rules = $args['value'];
					$buf .= $this->get_display_rules( $rules );
					$buf .= '</div>';
					break;
				case "{$this->prefix}robots.txt":
					$buf .= "<textarea disabled id='{$this->prefix}robot-txt' class='large-text robots-text' rows='15'>";
					$buf .= $this->do_robots();
					$buf .= "</textarea>";
					break;
			}

			$args['options']['type'] = 'hidden';
			if ( ! empty( $args['value'] ) ) {
				$args['value'] = wp_json_encode( $args['value'] );
			} else {
				$args['options']['type'] = 'html';
			}
			if ( empty( $args['value'] ) ) {
				$args['value'] = '';
			}
			$buf .= $this->get_option_html( $args );

			return $buf;
		}

		/**
		 * Add Menu
		 *
		 * (Parent) Adds the wp-admin menu, and this adds additional menu & load-hooks for
		 * the 1mporting and/or deleting the `robot.txt` file.
		 *
		 * @since 2.7.2
		 *
		 * @param $parent_slug
		 * @return bool
		 */
		public function add_menu( $parent_slug ) {
			$hook = 'all-in-one-seo_page_' . AIOSEOP_PLUGIN_DIRNAME . '/modules/aioseop_robots';
			if ( is_multisite() && is_network_admin() ) {
				// Add the robots.txt editor into the network admin menu.
				$hook = add_menu_page(
					'Robots.txt Editor',
					'Robots.txt Editor',
					'edit_themes',
					plugin_basename( $this->file ),
					array(
						$this,
						'display_settings_page',
					)
				);
			}

			add_action( 'load-' . $hook, array( $this, 'physical_file_check' ) );

			return parent::add_menu( $parent_slug );
		}
	}
}
