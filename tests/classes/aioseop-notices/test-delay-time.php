<?php

include_once AIOSEOP_UNIT_TESTING_DIR . '\base\class-aioseop-notices-testcase.php';

/**
 * Class Test_AIOSEOP_Notices
 *
 * @since 2.4.5.1
 *
 * @package tests\classes
 */
class Test_AIOSEOP_Notices_Delay_Time extends \AIOSEOP_Notices_TestCase {

	/**
	 * Server Time
	 *
	 * @var string
	 */
	public $time = '';

	/**
	 * PHPUnit Fixture - setUp()
	 *
	 * @since 2.4.5.1
	 *
	 * @link https://make.wordpress.org/core/handbook/testing/automated-testing/writing-phpunit-tests/#shared-setup-between-related-tests
	 */
	public function setUp() {
		parent::setUp();

//		$this->time = time();
		$this->time = new \DateTime();
	}

	/**
	 * PHPUnit Fixture - tearDown()
	 *
	 * @since 2.4.5.1
	 *
	 * @link https://make.wordpress.org/core/handbook/testing/automated-testing/writing-phpunit-tests/#shared-setup-between-related-tests
	 */
	public function tearDown() {
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
			'slug'           => 'notice_delay_delay_time',
			'delay_time'     => 2, // 1 Hour.
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
			'target'         => 'site',
			'screens'        => array(),
		);
	}

	/**
	 * Test Notice Delay Time
	 *
	 * Function: Displays Notice when the delayed time has been reached.
	 * Expected: Noticed doesn't render before the delay time, and when the delayed time is reach the notice will render.
	 * Actual: Currently works as expected.
	 * Reproduce: Have a notice inserted, and wait for X amount of time to pass.
	 *
	 * @since 2.8
	 */
	public function test_notice_delay_time() {
		global $aioseop_notices;
		$this->add_notice();

		set_current_screen( 'dashboard' );

		ob_start();
		$aioseop_notices->display_notice_default();
		$buffer = ob_get_contents();
		ob_end_clean();
		$this->assertEmpty( $buffer );

		sleep( 3 );

		ob_start();
		$aioseop_notices->display_notice_default();
		$buffer = ob_get_contents();
		ob_end_clean();
		$this->assertNotEmpty( $buffer );
	}
}
