<?php
/**
 * Testing AIOSEOP_Notices();
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
class Test_AIOSEOP_Notices_Init extends AIOSEOP_Notices_TestCase {

	/**
	 * PHPUnit Fixture - setUp()
	 *
	 * @since 3.0
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
	 * @since 3.0
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
	 * @since 3.0
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
	 * Test Enqueue Scripts on Screens
	 *
	 * Override and skip.
	 *
	 * @since 3.0
	 *
	 * @dataProvider data_screens
	 */
	public function test_enqueue_scripts_on_screens( $screen_id, $url, $dir ) {
		$this->markTestSkipped('Skip');
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
	 * @since 3.0
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
	 * Function: Inserts, and Updates, a single notice into wp_options.
	 * Expected: If no notice exists, it shouldn't be operational, and new notices should insert instead of update. Then
	 *           should be able to update without effecting the active notices.
	 * Actual: As expected; no current issue.
	 * Result: Inserts and Updates successfully to the database (wp_options).
	 *
	 * @since 3.0
	 *
	 * @param array $notice Notice to test.
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

		parent::test_add_notice( $notice );

		$this->validate_attr_notices( $aioseop_notices->notices );
	}
}

/**
 * ADDITIONAL DEV NOTES
 *
 * Objectives & Notes with target concepts/senarios. Possible for future use.
 *
 * Legend.
 *   * = Completed
 */

/*
 * SETUP
 *
 * *The object's wp_options will need to be deleted to force a full reset.
 */

/*
 * OBJECT OPTIONS TO DATABASE
 *
 * *Test get_options are set as default, and if the saved options are the expected output.
 *
 * Test Filter Hooks firing when they should. Both Admin_Init and Current_Screen.
 *
 * *Test for basic notice save.
 */

/*
 * NOTICE OPTIONS/DATABASE - _Notice_TestCase::add_notice() does most of this.
 *
 * *Test for default notice, and default actions
 *
 * *Test Insert_Notice. True on new notice, and false (no modifications) if already exists. Test it is rendered.
 *
 * *Test Update_Notice. True on old notice, and false if it doesn't exist. Test it doesn't reset a dismissed notice.
 *
 * Test Remove_Notice. True on success, and doesn't render. False on failure, and should not render assumed notice or change any others.
 *
 * *Test Activate_notice.
 */

/*
 * SCRIPTS AND STYLES.
 *
 * *Test for enqueued scripts and styles with multiple admin screens.
 *
 * Test for Display Notice returning false when there is no JS to fire AJAX; which is required.
 *
 * *Test if scripts have been deregistered. Both statically and on page expected to have no script.
 */

/*
 * NOTICE RENDERING - GENERAL OPERATIONS
 *
 * Test the differentiation between sitewide and user.
 * - Sitewide: displays to admin group, and only needs 1 admin to dismiss/delay.
 *     - TODO Could have a buffer incase someone else dismisses when the webmaster delays, or vice-versa.
 *       TODO OR Could have it as the first come first serve.
 * - Users: displays individually set to admins, and is dismissed/delayed seperately.
 *
 * *Test for notice delayed/timed rendering. Will need to Mock Time/Date.
 *
 * *Test for notice delay/dismiss, and rendering at a later time. ( Simular to Enqueue test, but will do the opposite ).
 *
 * *Test for notice activation
 *
 * Test DISABLE_NAG_NOTICES when set to true.
 */

/*
 * NOTICE AJAX DISMISS/DELAY
 *
 * *Test the response from the server.
 *
 * Test the response from server on failure.
 * *1. Controlled/expected fail.
 * 2. Unexpected PHP error.
 * *3. Security threat with invalid/null wp_nonce value.
 */

/*
 * NOTICE USER PERMISSIONS & RESTRICTIONS
 *
 * Test rendering for target user-roles.
 *
 * Test not rendering for non-target user-roles.
 */




/*
 * EACH NOTICE FUNCTION
 *
 * Test seperately firing the notice with the plugin conditions, and manual use of the notice function.
 *
 * Test that it is rendered, and if delayed, test delay then render.
 *
 * Test delay and/or dismiss works as intended.
 *
 * Test User perms & restrictions.
 */
