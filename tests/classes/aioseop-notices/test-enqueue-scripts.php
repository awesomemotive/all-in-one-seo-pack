<?php
/**
 * PHPUnit Testing AIOSEOP Notice Enqueue Scripts
 *
 * @package All_in_One_SEO_Pack
 * @subpackage AIOSEOP_Notices
 * @since 3.0
 *
 * @group AIOSEOP_Notices
 * @group Admin
 * @group Notices
 */

/**
 * AIOSEOP Notices Testcase
 */
include_once AIOSEOP_UNIT_TESTING_DIR . '/base/class-aioseop-notices-testcase.php';

/**
 * Class Test_AIOSEOP_Notices_AdminEnqueueScripts
 *
 * @since 3.0
 */
class Test_AIOSEOP_Notices_AdminEnqueueScripts extends AIOSEOP_Notices_TestCase {

	/**
	 * Set Up
	 */
	public function setUp() {
		parent::setUp();

		global $aiosp;
		global $aioseop_notices;
		if ( null === $aioseop_notices ) {
			$aioseop_notices = new AIOSEOP_Notices();
		}
		if ( null === $aiosp ) {
			$aiosp = new All_in_One_SEO_Pack();
		}
	}
	/**
	 * Mock Notice
	 *
	 * @since 3.0
	 *
	 * @return array
	 */
	public function mock_notice() {
		$rtn_notice               = parent::mock_notice();
		$rtn_notice['delay_time'] = 0;
		return $rtn_notice;
	}

	/**
	 * Test enqueue scripts on screens.
	 *
	 * Function: Enqueue Scripts and Styles with the WP Enqueue hook.
	 * Expected: Registered and enqueue scripts on target screens; provided by data_screens.
	 * Actual: As expected; no current issue.
	 * Result: Scripts are ready to be printed via enqueue.
	 *
	 * * should not enqueue if before delayed amount of time.
	 * * -notices with screen restrictions should be true only on set screens
	 * * (Test Render) Should not display content if script doesn't enqueue; also should send a Debug notice.
	 *
	 * @since 3.0
	 *
	 * @dataProvider data_screens
	 *
	 * @param string $screen_id
	 * @param string $url
	 * @param string $dir
	 */
	public function test_enqueue_scripts_on_screens( $screen_id, $url, $dir ) {

		global $aioseop_notices;
		if ( null === $aioseop_notices ) {
			$aioseop_notices = new AIOSEOP_Notices();
		}
		$this->validate_class_aioseop_notices( $aioseop_notices );

		// Should be empty.
		$this->assertTrue( empty( $aioseop_notices->active_notices ) );

		$notice = $this->mock_notice();

		// Insert Successful and activated.
		add_filter( 'aioseop_admin_notice-' . $notice['slug'], array( $this, 'mock_notice' ) );
		$this->assertTrue( $aioseop_notices->activate_notice( $notice['slug'] ) );
		$this->assertTrue( in_array( $notice['slug'], $notice, true ) );

		$this->assertTrue( isset( $aioseop_notices->active_notices[ $notice['slug'] ] ) );
		$this->assertNotNull( $aioseop_notices->active_notices[ $notice['slug'] ] );

		$this->validate_class_aioseop_notices( $aioseop_notices );

		wp_deregister_script( 'aioseop-admin-notice-js' );
		wp_deregister_style( 'aioseop-admin-notice-css' );
		$this->assertFalse( wp_script_is( 'aioseop-admin-notice-js', 'registered' ), 'Screen: ' . $screen_id );

		set_current_screen( $screen_id );
		$this->go_to( $url );

		$aioseop_notices = new AIOSEOP_Notices();

		set_current_screen( $screen_id );

		$this->assertFalse( wp_script_is( 'aioseop-admin-notice-js', 'registered' ), 'Screen: ' . $screen_id );

		do_action( 'admin_enqueue_scripts' );

		$this->assertTrue( wp_script_is( 'aioseop-admin-notice-js', 'registered' ), 'Screen: ' . $screen_id );
		$this->assertTrue( wp_script_is( 'aioseop-admin-notice-js', 'enqueued' ) );
	}
}
