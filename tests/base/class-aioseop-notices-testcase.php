<?php

/**
 * Class AIOSEOP_Notices_TestCase
 *
 * @since 2.4.5.1
 */
class AIOSEOP_Notices_TestCase extends WP_UnitTestCase {

	/**
	 * Old AIOSEOP Notices
	 *
	 * @var null $old_aioseop_notices
	 */
	public $old_aioseop_notices = null;

	/**
	 * Old AIOSEOP Notices Options
	 *
	 * @var $old_aioseop_notices_options
	 */
	public $old_aioseop_notices_options;

	/**
	 * PHPUnit Fixture - setUp()
	 *
	 * @since 2.4.5.1
	 *
	 * @link https://make.wordpress.org/core/handbook/testing/automated-testing/writing-phpunit-tests/#shared-setup-between-related-tests
	 */
	public function setUp() {
		parent::setUp();

		wp_set_current_user( 1 );

		global $aioseop_notices;
		if ( isset( $aioseop_notices ) && ! empty( $aioseop_notices ) ) {
			$this->old_sioseop_notices = $aioseop_notices;
		}
		$this->old_aioseop_notices_options = get_option( 'aioseop_notices' );

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

		global $aioseop_notices;
		if ( isset( $this->old_aioseop_notices ) && ! empty( $this->old_aioseop_notices ) ) {
			$aioseop_notices            = $this->old_aioseop_notices;
			$GLOBALS['aioseop_notices'] = $this->old_aioseop_notices;
		}
		if ( $this->old_aioseop_notices_options ) {
			update_option( 'aioseop_notices', $this->old_aioseop_notices_options );
		}

		parent::tearDown();
	}

	/**
	 * Clean Options AIOSEOP Notices
	 *
	 * @since 2.4.5.1
	 *
	 * @return boolean True if deleted, and false if it doesn't exist.
	 */
	public function clean_aioseop_notices() {
		global $aioseop_notices;
		if ( isset( $aioseop_notices ) && ! empty( $aioseop_notices ) ) {
			$aioseop_notices = null;
			unset( $GLOBALS['aioseop_notices'] );
		}

		return delete_option( 'aioseop_notices' );
	}

	/**
	 * Clean AIOSEOP Notices
	 *
	 * @since 2.4.5.1
	 *
	 * @param string $notice_slug Target notice to delete.
	 * @return boolean True if deleted.
	 */
	public function clean_aioseop_notice( $notice_slug ) {
		$notices_options = get_option( 'aioseop_notices' );
		if ( false === $notices_options ) {
			return false;
		} elseif ( ! isset( $notices_options['notices'][ $notice_slug ] ) || ! isset( $notices_options['active_notices'][ $notice_slug ] ) ) {
			return false;
		}

		unset( $notices_options['notices'][ $notice_slug ] );
		unset( $notices_options['active_notices'][ $notice_slug ] );

		return true;
	}

	/**
	 * Validate Global AIOSEOP_Notices object.
	 *
	 * @since 2.4.5.1
	 *
	 * @param AIOSEOP_Notices $aioseop_notices The current object to test for.
	 */
	protected function validate_class_aioseop_notices( $aioseop_notices ) {
		$this->assertInstanceOf( 'AIOSEOP_Notices', $aioseop_notices, 'Not an instance of AIOSEOP_Notices.' );

		$class_attrs = array(
			'notices'               => 'array',
			'active_notices'        => 'array',
			'default_dismiss_delay' => 'int',
			'aioseop_screens'       => 'array',
		);

		// Loop through each variable, and check if isset and value type (type-case).
		foreach ( $class_attrs as $attr_name => $attr_type ) {
			$this->assertObjectHasAttribute( $attr_name, $aioseop_notices, 'Variable is not set.' );
			$this->assertAttributeInternalType( $attr_type, $attr_name, $aioseop_notices, 'Error with Type casting.' );
			if ( 'notices' === $attr_name ) {
				$this->validate_attr_notices( $aioseop_notices->$attr_name );
			}
		}
	}

	/**
	 * Validates AIOSEOP_Notices::notices
	 *
	 * Checks to see if variables are correctly set.
	 *
	 * @since 2.4.5.1
	 *
	 * @param array $notices Class variable `AIOSEOP_Notices::notices`.
	 */
	protected function validate_attr_notices( $notices ) {
		foreach ( $notices as $notice ) {
			$this->validate_attr_notice( $notice );
		}
	}

	/**
	 * Validates notice in AIOSEOP_Notices::notices
	 *
	 * Checks to see if the array variables are correctly set.
	 *
	 * @since 2.4.5.1
	 *
	 * @param array $notice Class variable `AIOSEOP_Notices::notices`.
	 */
	protected function validate_attr_notice( $notice ) {
		$notices_attrs = array(
			'slug'           => 'string',
			'delay_time'     => 'int',
			'message'        => 'string',
			'action_options' => 'array',
			'class'          => 'string',
			'target'         => 'string',
			'screens'        => 'array',
			'time_start'     => 'int',
		);

		$action_option_attrs = array(
			'time'    => 'int',
			'text'    => 'string',
			'class'   => 'string',
			'link'    => 'string',
			'dismiss' => 'boolean',
		);

		foreach ( $notices_attrs as $attr_name => $attr_type ) {
			$this->assertArrayHasKey( $attr_name, $notice, 'Index/Key not found in Notice Array.' );
			$this->assertInternalType( $attr_type, $notice[ $attr_name ], 'Invalid value type (' . $attr_type . ') in ' . $attr_name );

			if ( 'action_option' === $attr_name ) {
				foreach ( $action_option_attrs as $action_attr_name => $action_attr_type ) {
					$this->assertArrayHasKey( $action_attr_name, $notice[ $attr_name ], 'Index/Key not found.' );
					$this->assertInternalType( $action_attr_type, $notice[ $attr_name ][ $action_attr_name ], 'Invalid value type.' );
				}
			}
		}
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
			'target'         => 'site',
			'screens'        => array(),
		);
	}

	/**
	 * Add Notice
	 *
	 * @since 2.4.5.1
	 *
	 * @param array $notice Single notice to add to object/database.
	 */
	public function test_add_notice( $notice = array() ) {
		global $aioseop_notices;
		if ( null === $aioseop_notices ) {
			$aioseop_notices = new AIOSEOP_Notices();
		}
		if ( empty( $notice ) ) {
			$notice = $this->mock_notice();
		}

		// Shouldn't exist yet.
		$this->assertFalse( $aioseop_notices->activate_notice( $notice['slug'] ) );
		$this->assertFalse( $aioseop_notices->deactivate_notice( $notice['slug'] ) );

		// Cannot update before insert.
		$this->assertFalse( $aioseop_notices->update_notice( $notice ) );

		// Insert Successful and activated.
		$this->assertTrue( $aioseop_notices->insert_notice( $notice ) );
		$this->assertTrue( in_array( $notice['slug'], $notice, true ) );

		// Re-Insert Failed.
		$this->assertFalse( $aioseop_notices->insert_notice( $notice ) );

		// Deactivate.
		$this->assertTrue( $aioseop_notices->deactivate_notice( $notice['slug'] ) );
		$this->assertFalse( $aioseop_notices->deactivate_notice( $notice['slug'] ) );
		$this->assertFalse( isset( $aioseop_notices->active_notices[ $notice['slug'] ] ) );

		// Update success, but notice is not active.
		$this->assertTrue( $aioseop_notices->update_notice( $notice ) );
		$this->assertFalse( isset( $aioseop_notices->active_notices[ $notice['slug'] ] ) );

		// Activate.
		$this->assertTrue( $aioseop_notices->activate_notice( $notice['slug'] ) );
		$this->assertTrue( isset( $aioseop_notices->active_notices[ $notice['slug'] ] ) );
		$this->assertNotNull( $aioseop_notices->active_notices[ $notice['slug'] ] );

		// Update Successful.
		$this->assertTrue( $aioseop_notices->update_notice( $notice ) );
	}
}
