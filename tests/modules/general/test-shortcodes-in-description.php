<?php

require_once AIOSEOP_UNIT_TESTING_DIR . '/base/class-aioseop-test-base.php';

/**
 * Contains test cases for shortcodes in autogenerated descriptions.
 *
 * @since 3.0
 */
class Test_Shortcodes_In_Description extends AIOSEOP_Test_Base {

	/**
	 * PHPUnit fixture.
	 *
	 * Sets up the test environment.
	 *
	 * @since 3.0
	 */
	public function setUp() {
		parent::setUp();

		// required, otherwise unit tests below fail
		remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
		remove_action( 'wp_print_styles', 'print_emoji_styles' );
	}

	/**
	 * Tests whether conflicting shortcodes are not run in autogenerated descriptions.
	 *
	 * @dataProvider conflictingShortcodeProvider
	 *
	 * @since 3.0
	 *
	 * @param string $shortcode
	 */
	public function test_conflicting_shortcodes( $shortcode ) {
		global $aioseop_options;
		$expected_description = 'This is some random content.';

		$aioseop_options['aiosp_skip_excerpt'] =
		$aioseop_options['aiosp_generate_descriptions'] =
		$aioseop_options['aiosp_run_shortcodes'] = 1;
		update_option( 'aioseop_options', $aioseop_options );

		$args = array(
			'post_type' => 'post',
			'post_title' => 'Example Title',
			'post_content' => $shortcode . $expected_description,
		);
		$id = $this->factory->post->create( $args );

		$link = get_permalink( $id );
		$meta_tags = $this->parse_html( $link, array ('meta') );
		$description = preg_replace( '(\[(.*?)\])', '', $meta_tags[0]['content'] );

		$this->assertEquals( $description, $expected_description );
	}

	/**
	 * Conflicting shortcode provider.
	 *
	 * Provides conflicting shortcodes to test_conflicting_shortcodes().
	 * This function should contain all conflicting shortcodes found in aioseop_contains_conflicting_shortcodes().
	 *
	 * @since 3.0
	 */
	public function conflictingShortcodeProvider() {
		return [
			'WooCommerce My Account' => ['[woocommerce_my_account]'],
		];
	}
}
