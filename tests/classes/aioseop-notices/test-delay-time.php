<?php
/**
 * PHPUnit Testing AIOSEOP Notice Delay Times;
 *
 * @package All_in_One_SEO_Pack
 * @subpackage AIOSEOP_Notices
 * @since 3.0
 *
 * @ticket 61
 *
 * @group AIOSEOP_Notices
 * @group Admin
 * @group Notices
 */
include_once AIOSEOP_UNIT_TESTING_DIR . '/base/class-aioseop-notices-testcase.php';

/**
 * Class Test_AIOSEOP_Notices
 *
 * @since 3.0
 *
 * @package tests\classes
 */
class Test_AIOSEOP_Notices_Delay_Time extends \AIOSEOP_Notices_TestCase {

	/**
	 * PHPUnit Fixture - setUp()
	 *
	 * @since 3.0
	 *
	 * @link https://make.wordpress.org/core/handbook/testing/automated-testing/writing-phpunit-tests/#shared-setup-between-related-tests
	 */
	public function setUp() {
		parent::setUp();
	}

	/**
	 * PHPUnit Fixture - tearDown()
	 *
	 * @since 3.0
	 *
	 * @link https://make.wordpress.org/core/handbook/testing/automated-testing/writing-phpunit-tests/#shared-setup-between-related-tests
	 */
	public function tearDown() {
		parent::tearDown();

	}

	/**
	 * Mock Single Notice
	 *
	 * @since 3.0
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
	 * Test Enqueue Scripts on Screens
	 *
	 * Override and skip.
	 *
	 * @since 3.0
	 *
	 * @dataProvider data_screens
	 */
	public function test_enqueue_scripts_on_screens( $screen_id, $url, $dir ) {
		$this->markTestSkipped( 'Skip' );
	}

	/**
	 * Test Notice Delay Time
	 *
	 * Function: Displays Notice when the delayed time has been reached.
	 * Expected: Noticed doesn't render before the delay time, and when the delayed time is reach the notice will render.
	 * Actual: Currently works as expected.
	 * Reproduce: Have a notice inserted, and wait for X amount of time to pass.
	 *
	 * @since 3.0
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
