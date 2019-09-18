<?php
/**
 * Class Test_Robots
 *
 * @package All_in_One_SEO_Pack
 * @since 2.7.2
 */

/**
 * Robots test case.
 */
require_once AIOSEOP_UNIT_TESTING_DIR . '/base/class-aioseop-test-base.php';

/**
 * Class Test_Robots
 *
 * @since 2.7.2
 */
class Test_Robots extends AIOSEOP_Test_Base {

	public function setUp() {
		parent::ajaxSetUp();
	}

	public function ajaxTearDown() {
		parent::ajaxTetUp();
	}

	private function create_file() {
		if ( $this->check_file_exists() ) {
			$this->delete_file();
		}

		$rule = "User-agent: Googlebot\r\nDisallow: /wow-test-folder/";

		// create a file.
		$file = fopen( ABSPATH . '/robots.txt', 'w' );
		fwrite( $file, $rule );
		fclose( $file );
	}

	private function check_file_exists() {
		return file_exists( ABSPATH . '/robots.txt' );
	}

	private function delete_file() {
		@unlink( ABSPATH . '/robots.txt' );
	}

	/**
	 * Importing a physical robots.txt file.
	 */
	public function test_import_physical_file() {
		$this->_setRole( 'administrator' );

		$this->create_file();

		$this->_setup_options( 'robots', array() );

		$_POST = array(
			'nonce-aioseop' => wp_create_nonce( 'aioseop-nonce' ),
			'settings'      => ' ',
			'options'       => 'import',
		);

		try {
			$this->_handleAjax( 'aioseop_ajax_robots_physical' );
		} catch ( WPAjaxDieContinueException  $e ) {
			// We expected this, do nothing.
		} catch ( WPAjaxDieStopException $ee ) {
			// We expected this, do nothing.
		}

		// now the file should not exist.
		$this->assertFalse( $this->check_file_exists(), 'Physical robots.txt not deleted' );

		$aioseop_options = get_option( 'aioseop_options' );
		$rules           = $aioseop_options['modules']['aiosp_robots_options']['aiosp_robots_rules'];

		$this->assertEquals( 1, count( $rules ) );
		$this->assertArrayHasKey( 'path', $rules[0], 'Rules not imported from physical robots.txt' );
		$this->assertEquals( '/wow-test-folder/', $rules[0]['path'], 'Rules not imported from physical robots.txt' );
	}

	/**
	 * Importing a physical robots.txt file.
	 */
	public function test_delete_physical_file() {
		$this->_setRole( 'administrator' );

		$this->create_file();

		$this->_setup_options( 'robots', array() );

		$_POST = array(
			'nonce-aioseop' => wp_create_nonce( 'aioseop-nonce' ),
			'settings'      => ' ',
			'options'       => 'delete',
		);

		try {
			$this->_handleAjax( 'aioseop_ajax_robots_physical' );
		} catch ( WPAjaxDieContinueException  $e ) {
			// We expected this, do nothing.
		} catch ( WPAjaxDieStopException $ee ) {
			// We expected this, do nothing.
		}

		// now the file should not exist.
		$this->assertFalse( $this->check_file_exists(), 'Physical robots.txt not deleted' );

		$aioseop_options = get_option( 'aioseop_options' );
		$rules           = $aioseop_options['modules']['aiosp_robots_options']['aiosp_robots_rules'];

		$this->assertEquals( 0, count( $rules ) );
	}

	/**
	 * Test if rules are sanitized before being added.
	 *
	 * @dataProvider sanitizedRulesProvider
	 */
	public function test_sanitize_rules( $rule ) {
		$this->_setRole( 'administrator' );

		$this->_setup_options( 'robots', array() );

		$_POST = array(
			'aiosp_robots_path'  => $rule['path'],
			'aiosp_robots_type'  => $rule['type'],
			'aiosp_robots_agent' => $rule['agent'],
		);

		$path = $rule['path'];

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

		$options = apply_filters( 'aiosp_robots_update_options', array() );
		$this->assertArrayHasKey( 'aiosp_robots_rules', $options );
		$this->assertGreaterThan( 0, $options['aiosp_robots_rules'] );
		$this->assertArrayHasKey( 'type', $options['aiosp_robots_rules'][0] );
		$this->assertArrayHasKey( 'agent', $options['aiosp_robots_rules'][0] );
		$this->assertArrayHasKey( 'path', $options['aiosp_robots_rules'][0] );
		$this->assertArrayHasKey( 'id', $options['aiosp_robots_rules'][0] );
		$this->assertEquals( $path, $options['aiosp_robots_rules'][0]['path'], 'Rule not sanitized' );
	}

	public function sanitizedRulesProvider() {
		return array(
			array(
				'path'  => 'test.txt',
				'type'  => 'disallow',
				'agent' => '*',
			),
			array(
				'path'  => 'wp-content/image.jpg',
				'type'  => 'allow',
				'agent' => '*',
			),
			array(
				'path'  => 'temp.*',
				'type'  => 'allow',
				'agent' => '*',
			),
			array(
				'path'  => 'wp-content/*.txt',
				'type'  => 'disallow',
				'agent' => '*',
			),
		);
	}


	/**
	 * Test if overriding default rules is rejected.
	 *
	 * @dataProvider defaultRulesProvider
	 */
	public function test_override_default_rules( $rule, $message ) {
		$this->_setRole( 'administrator' );

		delete_transient( 'aiosp_robots_errors' . get_current_user_id() );

		$this->_setup_options( 'robots', array() );

		// import default WP rules.
		do_action( 'aioseop_ut_aiosp_robots_admin_init' );

		$_POST = array(
			'aiosp_robots_path'  => $rule['path'],
			'aiosp_robots_type'  => $rule['type'],
			'aiosp_robots_agent' => $rule['agent'],
		);

		$options = apply_filters( 'aiosp_robots_update_options', array() );
		$errors  = get_transient( 'aiosp_robots_errors' . get_current_user_id() );
		$this->assertGreaterThan( 0, count( $errors ) );
		$this->assertContains( $message, $errors[0], 'Default rule overriden' );

	}

	public function defaultRulesProvider() {
		return array(
			array(
				array(
					'path'  => '/wp-admin/',
					'type'  => 'disallow',
					'agent' => '*',
				),
				'Rule cannot be overridden',
			),
			array(
				array(
					'path'  => '/wp-admin/admin-ajax.php',
					'type'  => 'allow',
					'agent' => '*',
				),
				'Rule cannot be overridden',
			),
			array(
				array(
					'path'  => '/wp-admin/',
					'type'  => 'allow',
					'agent' => '*',
				),
				'Rule cannot be overridden',
			),
			array(
				array(
					'path'  => '/wp-admin/admin-ajax.php',
					'type'  => 'disallow',
					'agent' => '*',
				),
				'Rule cannot be overridden',
			),
			array(
				array(
					'path'  => '/wp-*',
					'type'  => 'disallow',
					'agent' => '*',
				),
				'Wild-card path cannot be overridden',
			),
			array(
				array(
					'path'  => '/wp-*/admin-ajax.*',
					'type'  => 'allow',
					'agent' => '*',
				),
				'Wild-card path cannot be overridden',
			),
			array(
				array(
					'path'  => '/*-admin/',
					'type'  => 'allow',
					'agent' => '*',
				),
				'Wild-card path cannot be overridden',
			),
			array(
				array(
					'path'  => '/*-admin/admin-ajax.*',
					'type'  => 'disallow',
					'agent' => '*',
				),
				'Wild-card path cannot be overridden',
			),
		);
	}

	/**
	 * Test if conflicting rules are rejected.
	 *
	 * @dataProvider conflictingRulesProvider
	 */
	public function test_conflicting_rules( $existing_rules, $new_rule, $message ) {
		$this->_setRole( 'administrator' );

		delete_transient( 'aiosp_robots_errors' . get_current_user_id() );

		$this->_setup_options( 'robots', array() );

		global $aioseop_options;

		foreach ( $existing_rules as $rule ) {
			$_POST = array(
				'aiosp_robots_path'  => $rule['path'],
				'aiosp_robots_type'  => $rule['type'],
				'aiosp_robots_agent' => $rule['agent'],
			);

			$options = apply_filters( 'aiosp_robots_update_options', array() );

			$aioseop_options['modules']['aiosp_robots_options']['aiosp_robots_rules'] = $options['aiosp_robots_rules'];
			update_option( 'aioseop_options', $aioseop_options );
		}

		$_POST = array(
			'aiosp_robots_path'  => $new_rule['path'],
			'aiosp_robots_type'  => $new_rule['type'],
			'aiosp_robots_agent' => $new_rule['agent'],
		);

		$options = apply_filters( 'aiosp_robots_update_options', array() );
		$errors  = get_transient( 'aiosp_robots_errors' . get_current_user_id() );
		$this->assertGreaterThan( 0, count( $errors ) );
		$this->assertContains( $message, $errors[0] );

	}

	public function conflictingRulesProvider() {
		return array(

			// It should not be possible to add a duplicate rule for an individual file.
			array(
				array(
					array(
						'path'  => '/test.txt',
						'type'  => 'disallow',
						'agent' => '*',
					),
				),
				array(
					'path'  => '/test.txt',
					'type'  => 'disallow',
					'agent' => '*',
				),
				'Identical rule exists',
			),
			array(
				array(
					array(
						'path'  => '/wp-content/image.jpg',
						'type'  => 'allow',
						'agent' => '*',
					),
				),
				array(
					'path'  => '/wp-content/image.jpg',
					'type'  => 'allow',
					'agent' => '*',
				),
				'Identical rule exists',
			),

			// It should not be possible to add a duplicate rule using wildcards for an individual file.
			array(
				array(
					array(
						'path'  => '/test.txt',
						'type'  => 'disallow',
						'agent' => '*',
					),
				),
				array(
					'path'  => '/test.*',
					'type'  => 'disallow',
					'agent' => '*',
				),
				'Wild-card path cannot be overridden',
			),

			// It should not be possible to add a conflicting rule for an individual file.
			array(
				array(
					array(
						'path'  => '/test.txt',
						'type'  => 'disallow',
						'agent' => '*',
					),
				),
				array(
					'path'  => '/test.txt',
					'type'  => 'allow',
					'agent' => '*',
				),
				'Rule cannot be overridden',
			),

			// It should not be possible to add a conflicting rule using wildcards for an individual file.
			array(
				array(
					array(
						'path'  => '/test.txt',
						'type'  => 'disallow',
						'agent' => '*',
					),
				),
				array(
					'path'  => '/test.*',
					'type'  => 'allow',
					'agent' => '*',
				),
				'Wild-card path cannot be overridden',
			),

			// It should not be possible to add a duplicate rule for a directory.
			array(
				array(
					array(
						'path'  => '/wp-includes/',
						'type'  => 'disallow',
						'agent' => '*',
					),
				),
				array(
					'path'  => '/wp-includes/',
					'type'  => 'disallow',
					'agent' => '*',
				),
				'Identical rule exists',
			),

			// It should not be possible to add a duplicate rule using wildcards for a directory.
			array(
				array(
					array(
						'path'  => '/wp-includes/',
						'type'  => 'disallow',
						'agent' => '*',
					),
				),
				array(
					'path'  => '/wp-*/',
					'type'  => 'disallow',
					'agent' => '*',
				),
				'Wild-card path cannot be overridden',
			),

			// It should not be possible to add a conflicting rule for a directory.
			array(
				array(
					array(
						'path'  => '/wp-includes/',
						'type'  => 'disallow',
						'agent' => '*',
					),
				),
				array(
					'path'  => '/wp-includes/',
					'type'  => 'allow',
					'agent' => '*',
				),
				'Rule cannot be overridden',
			),

			// It should not be possible to add a conflicting rule using wildcards for a directory.
			array(
				array(
					array(
						'path'  => '/wp-includes/',
						'type'  => 'disallow',
						'agent' => '*',
					),
				),
				array(
					'path'  => '/wp-*/',
					'type'  => 'allow',
					'agent' => '*',
				),
				'Wild-card path cannot be overridden',
			),
		);
	}

	/**
	 * Test modification of rules.
	 *
	 * @dataProvider modifyRulesProvider
	 */
	public function test_modify_rules( $existing_rules, $rule_to_modify, $new_rule, $error_message = '' ) {
		$this->_setRole( 'administrator' );

		delete_transient( 'aiosp_robots_errors' . get_current_user_id() );

		$this->_setup_options( 'robots', array() );

		global $aioseop_options;

		foreach ( $existing_rules as $rule ) {
			$_POST = array(
				'aiosp_robots_path'  => $rule['path'],
				'aiosp_robots_type'  => $rule['type'],
				'aiosp_robots_agent' => $rule['agent'],
			);

			$options = apply_filters( 'aiosp_robots_update_options', array() );

			$aioseop_options['modules']['aiosp_robots_options']['aiosp_robots_rules'] = $options['aiosp_robots_rules'];
			update_option( 'aioseop_options', $aioseop_options );
		}

		// get the rule_to_modify.
		$rule_modified = null;
		foreach ( $aioseop_options['modules']['aiosp_robots_options']['aiosp_robots_rules'] as $rule ) {
			if ( $rule_to_modify === $rule['path'] ) {
				$rule_modified = $rule;
				break;
			}
		}

		$_POST = array(
			'aiosp_robots_id'    => $rule_modified['id'],
			'aiosp_robots_path'  => $new_rule['path'],
			'aiosp_robots_type'  => $new_rule['type'],
			'aiosp_robots_agent' => $new_rule['agent'],
		);

		$options = apply_filters( 'aiosp_robots_update_options', array() );

		$aioseop_options['modules']['aiosp_robots_options']['aiosp_robots_rules'] = $options['aiosp_robots_rules'];
		update_option( 'aioseop_options', $aioseop_options );

		$errors = get_transient( 'aiosp_robots_errors' . get_current_user_id() );
		$paths  = wp_list_pluck( $options['aiosp_robots_rules'], 'path' );
		if ( $error_message ) {
			$this->assertGreaterThan( 0, count( $errors ), 'Error not logged!' );
			$this->assertContains( $error_message, $errors[0], 'Error message not found!' );
			$this->assertContains( $rule_modified['path'], $paths, 'Rule modified!' );
			$this->assertNotContains( $new_rule['path'], $paths, 'Rule modified!' );
		} else {
			$this->assertNotContains( $rule_modified['path'], $paths, 'Rule not modified' );
			$this->assertContains( $new_rule['path'], $paths, 'Rule not modified' );
		}
	}

	public function modifyRulesProvider() {
		return array(
			array(
				array(
					array(
						'path'  => '/test.txt',
						'type'  => 'disallow',
						'agent' => '*',
					),
					array(
						'path'  => '/wp-admin',
						'type'  => 'disallow',
						'agent' => '*',
					),
				),
				'/test.txt',
				array(
					'path'  => '/testttt.txt',
					'type'  => 'disallow',
					'agent' => '*',
				),
			),
			array(
				array(
					array(
						'path'  => '/test.txt',
						'type'  => 'disallow',
						'agent' => '*',
					),
					array(
						'path'  => '/wp-admin',
						'type'  => 'disallow',
						'agent' => '*',
					),
				),
				'/test.txt',
				array(
					'path'  => '/wp-admin',
					'type'  => 'disallow',
					'agent' => '*',
				),
				'Identical rule exists',
			),
		);
	}
}
