<?php

/**
 * Testing AIOSEOP_Notices();
 *
 * @since 2.4.4.1
 *
 * @ticket 61
 *
 * @group AIOSEOP_Notices
 * @group Admin
 * @group Notices
 */



include_once AIOSEOP_UNIT_TESTING_DIR . '\base\class-aioseop-notices-testcase.php';
//	use AIOSEOP_Notices_TestCase;
//	use AIOSEOP_Notices;

/**
 * Class Test_AIOSEOP_Notices
 *
 * @since 2.4.5.1
 *
 * @package tests\classes
 */
class Test_AIOSEOP_Notices_Init extends AIOSEOP_Notices_TestCase {

	/**
	 * PHPUnit Fixture - setUp()
	 *
	 * @since 2.4.5.1
	 *
	 * @link https://make.wordpress.org/core/handbook/testing/automated-testing/writing-phpunit-tests/#shared-setup-between-related-tests
	 */
	public function setUp() {
		parent::setUp();

		$this->clean_aioseop_notices();
	}

	/**
	 * PHPUnit Fixture - tearDown()
	 *
	 * @since 2.4.5.1
	 *
	 * @link https://make.wordpress.org/core/handbook/testing/automated-testing/writing-phpunit-tests/#shared-setup-between-related-tests
	 */
	public function tearDown() {
		$this->clean_aioseop_notices();

		parent::tearDown();
	}

	/**
	 * Mock Single Notice
	 *
	 * @since 2.4.5.1
	 *
	 * @return array
	 */
	protected function mock_notice() {
		return array(
			'slug'           => 'notice_slug_1',
			'delay_time'     => 3600, // 1 Hour.
			'message'        => __( 'Admin Sample Message.', 'all-in-one-seo-pack' ),
			'action_options' => array(
				array(
					'time'    => 0,
					'text'    => __( 'Link and close', 'all-in-one-seo-pack' ),
					'link'    => 'https://wordpress.org/support/plugin/all-in-one-seo-pack',
					'dismiss' => false,
					'class'   => '',
				),
				array(
					'text'    => 'Delay',
					'time'    => 432000,
					'dismiss' => false,
					'class'   => '',
				),
				array(
					'time'    => 0,
					'text'    => 'Dismiss',
					'dismiss' => true,
					'class'   => '',
				),
			),
			'target'         => 'user',
			'screens'        => array(),
		);
	}

	/**
	 * Test AIOSEOP_Notices' initial values.
	 *
	 * Statically called.
	 *
	 * Function:  Contructs the object to be used with the server.
	 * Expected:  The class should initialize and set it's internal variables.
	 * Actual:    Currently, as expected.
	 * Reproduce: The class is loaded and initialized when the plugin is activated and loaded.
	 *
	 * TODO Rename: _static_new_*
	 *
	 * @since 2.4.5.1
	 */
	public function test_static_construct() {

		global $aioseop_notices;
		$this->assertNull( $aioseop_notices, 'The Global, \'$aioseop_options\', isn\' being cleaned upon setUp' );

		$wp_options_aioseop_options = get_option( 'aioseop_notices' );
		$this->assertFalse( $wp_options_aioseop_options, 'The WP Options, \'aioseop_options\', isn\' being cleaned upon setUp' );

		$aioseop_notices = new AIOSEOP_Notices();

		$this->validate_class_aioseop_notices( $aioseop_notices );
	}

	/**
	 * Test Add Notice
	 *
	 * Function:
	 * Expected:
	 * Actual:
	 * Reproduce:
	 *
	 * @since 2.4.5.1
	 *
	 * @param array $notice Notice to test
	 */
	public function test_add_notice( $notice = array() ) {
		if ( empty( $notice ) ) {
			$notice = $this->mock_notice();
		}

		global $aioseop_notices;
		$this->assertNull( $aioseop_notices, 'The Global, \'$aioseop_options\', isn\' being cleaned upon setUp' );

		$wp_options_aioseop_options = get_option( 'aioseop_notices' );
		$this->assertFalse( $wp_options_aioseop_options, 'The WP Options, \'aioseop_options\', isn\' being cleaned upon setUp' );

		$aioseop_notices = new AIOSEOP_Notices();

//			$this->add_notice( $notice );
		parent::test_add_notice( $notice );

		$this->validate_attr_notices( $aioseop_notices->notices );
	}
}

