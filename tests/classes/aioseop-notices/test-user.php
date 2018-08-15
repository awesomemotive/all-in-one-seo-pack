<?php
/**
 * PHPUnit Testing AIOSEOP Notices User Perms/Restrictions
 *
 * @package All_in_One_SEO_Pack
 * @subpackage AIOSEOP_Notices
 * @since 2.4.5.1
 *
 * @group AIOSEOP_Notices
 * @group Admin
 * @group Notices
 */

/**
 * Test for a User Notices.
 *
 * - Should not show to other users.
 * - Should be handled seperately ( delayed/dismissed seperately ).
 *
 */
include_once AIOSEOP_UNIT_TESTING_DIR . '\base\class-aioseop-notices-testcase.php';
/**
 * Class Test_AIOSEOP_Notices
 *
 * @since 2.4.5.1
 *
 * @package tests\classes
 */
class Test_AIOSEOP_Notices_User extends AIOSEOP_Notices_TestCase {

	/**
	 * PHPUnit Fixture - setUp()
	 *
	 * @since 2.4.5.1
	 *
	 * @link https://make.wordpress.org/core/handbook/testing/automated-testing/writing-phpunit-tests/#shared-setup-between-related-tests
	 */

	public function setUp() {
		parent::setUp();

		set_current_screen( 'dashboard' );
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
	 * @since 2.7.2
	 *
	 * @return array
	 */
	protected function mock_notice() {
		return array(
			'slug'           => 'notice_slug_user',
			'delay_time'     => 0, // 1 Hour.
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
	 * @since 2.7.2
	 */
	public function test_enqueue_scripts_on_screens() {
		$this->markTestSkipped('Skip');
	}

	/**
	 * Test for notices showing to only admins, or manage aioseop perms.
	 *
	 * @dataProvider data_user_roles
	 */
	public function test_notice_admin_perms( $role, $expect_display ) {
		global $aioseop_notices_test;
		$this->add_notice();

		$user_id = $this->factory()->user->create(
			array(
				'user_login'    => 'user_' . $role,
				'user_nicename' => 'user' . $role,
				'user_pass'     => 'password',
				'first_name'    => 'John',
				'last_name'     => 'Doe',
				'display_name'  => 'John Doe',
				'user_email'    => 'placeholder@email.com',
				'user_url'      => 'http://semperplugins.com',
				'role'          => $role,
				'nickname'      => 'Johnny',
				'description'   => 'I am a WordPress user.',
			)
		);

		wp_set_current_user( $user_id );
		set_current_screen( 'dashboard' );

		// Test User Perms.
		$user_can = current_user_can( 'aiosp_manage_seo' );
		if ( $expect_display ) {
			$this->assertTrue( $user_can );
		} else {
			$this->assertFalse( $user_can );
		}

		// After construction, check hooks added only for users with `aiosp_manage_seo`.
		$aioseop_notices_test = new AIOSEOP_Notices();

		if ( $expect_display ) {
			$this->assertTrue( has_action( 'admin_init', array( $aioseop_notices_test, 'init' ) ) ? true : false );
			$this->assertTrue( has_action( 'current_screen', array( $aioseop_notices_test, 'admin_screen' ) ) ? true : false );
		} else {
			$this->assertFalse( has_action( 'admin_init', array( $aioseop_notices_test, 'init' ) ) ? true : false );
			$this->assertFalse( has_action( 'current_screen', array( $aioseop_notices_test, 'admin_screen' ) ) ? true : false );
		}

		// After `current_screen` action hook, check for hooks added.
		set_current_screen( 'dashboard' );

		if ( $expect_display ) {
			$this->assertTrue( has_action( 'admin_enqueue_scripts', array( $aioseop_notices_test, 'admin_enqueue_scripts' ) ) ? true : false );
			$this->assertTrue( has_action( 'all_admin_notices', array( $aioseop_notices_test, 'display_notice_default' ) ) ? true : false );
		} else {
			$this->assertFalse( has_action( 'admin_enqueue_scripts', array( $aioseop_notices_test, 'admin_enqueue_scripts' ) ) ? true : false );
			$this->assertFalse( has_action( 'all_admin_notices', array( $aioseop_notices_test, 'display_notice_default' ) ) ? true : false );
		}

		ob_start();
		$aioseop_notices_test->display_notice_default();
		$buffer = ob_get_contents();
		ob_end_clean();

		if ( $expect_display ) {
			$this->assertNotEmpty( $buffer );
		} else {
			$this->assertEmpty( $buffer );
		}
	}

	/**
	 * @return array
	 */
	public function data_user_roles() {
		return array(
			array(
				'role'           => 'administrator',
				'expect_display' => true,
			),
			array(
				'role'           => 'editor',
				'expect_display' => false,
			),
			array(
				'role'           => 'author',
				'expect_display' => false,
			),
			array(
				'role'           => 'contributor',
				'expect_display' => false,
			),
			array(
				'role'           => 'subscriber',
				'expect_display' => false,
			),
		);
	}

}
