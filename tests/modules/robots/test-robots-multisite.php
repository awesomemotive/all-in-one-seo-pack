<?php
/**
 * Class Test_Robots_Multisite
 *
 * @package All_in_One_SEO_Pack
 * @since 2.7.2
 */

/**
 * AIOSEOP test base
 */
require_once AIOSEOP_UNIT_TESTING_DIR . '/modules/robots/test-robots.php';

/**
 * Robots test case for multisite.
 */
class Test_Robots_Multisite extends Test_Robots {

	/**
	 * Test if conflicting rules are rejected.
	 *
	 * @dataProvider conflictingRulesProvider
	 */
	public function test_conflicting_rules( $network_rule, $site_rule, $message ) {
		if ( ! is_multisite() ) {
			$this->markTestSkipped( 'Only for multi site' );
		}

		$this->_setRole( 'administrator' );

		delete_transient( 'aiosp_robots_errors' . get_current_user_id() );

		$this->_setup_options( 'robots', array() );

		$network = get_network()->site_id;
		switch_to_blog( $network );

		$_POST = array(
			'aiosp_robots_path'  => $network_rule['path'],
			'aiosp_robots_type'  => $network_rule['type'],
			'aiosp_robots_agent' => $network_rule['agent'],
		);

		$options = apply_filters( 'aiosp_robots_update_options', array() );
		$aioseop_options['modules']['aiosp_robots_options']['aiosp_robots_rules'] = $options['aiosp_robots_rules'];
		update_option( 'aioseop_options', $aioseop_options );

		restore_current_blog();

		$network = get_network()->site_id;
		switch_to_blog( $network );

		$_POST = array(
			'aiosp_robots_path'  => $site_rule['path'],
			'aiosp_robots_type'  => $site_rule['type'],
			'aiosp_robots_agent' => $site_rule['agent'],
		);

		$options = apply_filters( 'aiosp_robots_update_options', array() );

		$aioseop_options['modules']['aiosp_robots_options']['aiosp_robots_rules'] = $options['aiosp_robots_rules'];
		update_option( 'aioseop_options', $aioseop_options );

		$errors = get_transient( 'aiosp_robots_errors' . get_current_user_id() );
		$this->assertGreaterThan( 0, count( $errors ) );
		$this->assertContains( $message, $errors[0] );
	}

	public function conflictingRulesProvider() {
		return array(
			array(
				array(
					'path'  => 'test.txt',
					'type'  => 'disallow',
					'agent' => '*',
				),
				array(
					'path'  => 'test.txt',
					'type'  => 'disallow',
					'agent' => '*',
				),
				'Identical rule exists',
			),
			array(
				array(
					'path'  => 'wp-content/image.jpg',
					'type'  => 'allow',
					'agent' => '*',
				),
				array(
					'path'  => 'wp-content/image.jpg',
					'type'  => 'allow',
					'agent' => '*',
				),
				'Identical rule exists',
			),
			array(
				array(
					'path'  => 'temp.*',
					'type'  => 'allow',
					'agent' => '*',
				),
				array(
					'path'  => 'temp.*',
					'type'  => 'allow',
					'agent' => '*',
				),
				'Identical rule exists',
			),
			array(
				array(
					'path'  => 'wp-content/*.txt',
					'type'  => 'disallow',
					'agent' => '*',
				),
				array(
					'path'  => 'wp-content/*.txt',
					'type'  => 'disallow',
					'agent' => '*',
				),
				'Identical rule exists',
			),
			array(
				array(
					'path'  => 'wp-content/image.jpg',
					'type'  => 'allow',
					'agent' => '*',
				),
				array(
					'path'  => 'wp-*/*.jpg',
					'type'  => 'disallow',
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
