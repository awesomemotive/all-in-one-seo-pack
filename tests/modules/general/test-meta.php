<?php
/**
 * Class Test_Meta
 *
 * @package 
 */

/**
 * Advance Custom Fields test cases.
 */

require_once AIOSEOP_UNIT_TESTING_DIR . '/base/class-aioseop-test-base.php';

class Test_Meta extends AIOSEOP_Test_Base {

	/**
	 * Creates a custom field in the post and uses this in the meta description.
	 * NOTE: This does not require the ACF plugin because the code uses the custom field directly if ACF is not installed.
	 *
	 * @dataProvider acfDataProvider
	 */
	public function test_custom_field_in_meta_desc( $format, $custom_field = false ) {
		wp_set_current_user( 1 );

		global $aioseop_options;

		$meta_desc	= 'heyhey';
		$id = $this->factory->post->create( array( 'post_type' => 'post', 'post_title' => 'hey', 'post_content' => $meta_desc ) );
		// update the AIOSEOP description to be the same as the post description.
		update_post_meta( $id, '_aioseop_description', $meta_desc );

		// if custom field is provided, create it and change the format.
		if ( $custom_field ) {
			$meta_desc = 'holahola';
			update_post_meta( $id, $format, $meta_desc );
			$format	= "cf_{$format}";
		}

		// update the format.
		$aioseop_options['aiosp_description_format'] = "%$format%";
		update_option( 'aioseop_options', $aioseop_options );

		$link = get_permalink( $id );
		$meta = $this->parse_html( $link, array( 'meta' ) );

		// should have atleast one meta tag.
		$this->assertGreaterThan( 1, count( $meta ) );

		$description = null;
		foreach ( $meta as $m ) {
			if ( 'description' === $m['name'] ) {
				$description = $m['content'];
				break;
			}
		}
		$this->assertEquals( $meta_desc, $description );
	}

	/**
	 * Provides data to test meta with and without custom fields.
	 */
	public function acfDataProvider() {
		return array(
			array( 'description', false ),
			array( 'custom_description', true ),
		);
	}
}