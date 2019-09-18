<?php
/**
 * Class Test_Meta
 *
 * @package All_in_One_SEO_Pack
 * @since 2.4.5.1
 */

/**
 * Advance Custom Fields test cases.
 */
require_once AIOSEOP_UNIT_TESTING_DIR . '/base/class-aioseop-test-base.php';

/**
 * Class Test_Meta
 *
 * @since 2.4.5.1
 */
class Test_Meta extends AIOSEOP_Test_Base {

	public function setUp() {
		$this->init( true );
	}

	/**
	 * Creates a custom field in the post and uses this in the meta description.
	 * NOTE: This does not require the ACF plugin because the code uses the custom field directly if ACF is not installed.
	 *
	 * @dataProvider acfDataProvider
	 */
	public function test_custom_field_in_meta_desc( $format, $custom_field = false ) {
		wp_set_current_user( 1 );

		global $aioseop_options;

		$meta_desc = 'heyhey';

		$id = $this->factory->post->create(
			array(
				'post_type'    => 'post',
				'post_title'   => 'hey',
				'post_content' => $meta_desc,
			)
		);
		// update the AIOSEOP description to be the same as the post description.
		update_post_meta( $id, '_aioseop_description', $meta_desc );

		// if custom field is provided, create it and change the format.
		if ( $custom_field ) {
			$meta_desc = 'holahola';
			update_post_meta( $id, $format, $meta_desc );
			$format = "cf_{$format}";
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
	 * Creates a custom field in the post and uses this in the meta description.
	 */
	public function test_custom_field_in_meta_desc_no_content() {
		wp_set_current_user( 1 );

		global $aioseop_options;

		$meta_desc = 'heyhey';
		// very, very important: post excerpt has to be empty or this will not work.
		$id = $this->factory->post->create(
			array(
				'post_type'    => 'post',
				'post_title'   => 'hey',
				'post_content' => '',
				'post_excerpt' => '',
			)
		);
		// update the AIOSEOP description.
		update_post_meta( $id, 'custom_description', $meta_desc );

		// update the format.
		$aioseop_options['aiosp_description_format'] = '%cf_custom_description%';
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
	 * Test whether the meta description is correctly auto generated given different types of content.
	 *
	 * @test
	 * @dataProvider postContentProvider
	 */
	public function test_auto_generate_description( $content, $meta_desc, $excerpt = '' ) {
		wp_set_current_user( 1 );
		global $aioseop_options;
		$id = $this->factory->post->create(
			array(
				'post_type'    => 'post',
				'post_title'   => 'hey' . rand(),
				'post_excerpt' => $excerpt,
				'post_content' => $content,
			)
		);
		// update the format.
		$aioseop_options['aiosp_description_format']    = '%description%';
		$aioseop_options['aiosp_generate_descriptions'] = 'on';

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
	 * Test whether the meta description contains exactly what is expected.
	 *
	 * @dataProvider metaDescProvider
	 */
	public function test_post_title_in_meta_desc( $title, $meta_desc, $format ) {
		wp_set_current_user( 1 );
		global $aioseop_options;
		$id = $this->factory->post->create(
			array(
				'post_type'  => 'post',
				'post_title' => $title,
			)
		);
		// update the format.
		$aioseop_options['aiosp_description_format'] = $format;
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
	 * Provides the different contents to test whether auto-generated description is generated correctly.
	 */
	public function postContentProvider() {
		return array(
			array( 'content part 1 content part 2', 'content part 1 content part 2' ),
			array( 'blah part 1 blahhhhhhh', 'blah part 1 content part 2', 'blah part 1 content part 2' ),
			array( 'content blah 1 <img src="http://someurl.com/someimage.jpg" /> content part 2', 'content blah 1 content part 2' ),
			array( '<img src="http://someurl.com/someimage.jpg" /> content part blah <img src="http://someurl.com/someimage.jpg" /> content part 2', 'content part blah content part 2' ),
			array( 'content part 1a content part 2 content part 3', 'content part 1a content part 2', 'content part 1a content part 2' ),
			array( 'content part 10 <img src="http://someurl.com/someimage.jpg" /> content part 2 <img src="http://someurl.com/someimage.jpg" /> content part 3', 'content part 10 content part 2 content part 3' ),
			array( str_repeat( 'blah', 300 ), substr( str_repeat( 'blah', 300 ), 0, 160 ) ),
		);
	}

	/**
	 * The data provider for meta description.
	 */
	public function metaDescProvider() {
		return array(
			array( 'heyhey', 'heyhey', '%post_title%' ),
			array( 'heyhey', 'heyhey' . get_option( 'blogname' ), '%post_title%%site_title%' ),
		);
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

	/**
	 * Test whether the given post type's SEO tags are included/excluded in the source if SEO is enabled/disabled.
	 *
	 * @dataProvider postTypeEnabledProvider
	 */
	public function test_cpt_seo( $type, $enabled ) {
		wp_set_current_user( 1 );
		global $aioseop_options;

		$id = $this->factory->post->create(
			array(
				'post_type'  => $type,
				'post_title' => 'heyhey',
			)
		);

		// remove the default action so that canonical is not included by default.
		remove_action( 'wp_head', 'rel_canonical' );

		$aioseop_options['aiosp_can']                = 'on';
		$aioseop_options['aiosp_cpostactive']        = $enabled ? array( $type ) : array();
		$aioseop_options['aiosp_description_format'] = '---- desc desc';
		update_option( 'aioseop_options', $aioseop_options );

		$url   = get_permalink( $id );
		$meta  = $this->parse_html( $url, array( 'meta' ) );
		$links = $this->parse_html( $url, array( 'link' ) );

		$canonical = wp_list_pluck( $links, 'rel' );

		if ( $enabled ) {
			// should have atleast one meta tag.
			$this->assertGreaterThan( 1, count( $meta ) );
			$this->assertGreaterThan( 1, count( $links ) );
			$this->assertContains( 'canonical', $canonical, 'Does not contain link for canonical' );
		} else {
			$this->assertNotContains( 'canonical', $canonical, 'Contains link for canonical' );
		}

		$canonical = null;
		if ( $links ) {
			foreach ( $links as $link ) {
				if ( isset( $link['rel'] ) && 'canonical' === $link['rel'] ) {
					$canonical = $link['href'];
					break;
				}
			}
		}

		$meta_content = wp_list_pluck( $meta, 'content' );
		if ( $enabled ) {
			$this->assertContains( '---- desc desc', $meta_content );
			$this->assertEquals( $url, $canonical );
		} else {
			$this->assertNotContains( '---- desc desc', $meta_content );
			$this->assertEmpty( $canonical );
		}

	}

	/**
	 * Tests all title formats.
	 */
	public function test_title_formats() {
		$this->markTestIncomplete( 'Cannot seem to get the correct title in the header' );

		global $aioseop_options;

		$post_id = $this->factory->post->create(
			array(
				'post_type'  => 'post',
				'post_title' => 'heyhey',
			)
		);

		$page_id = $this->factory->post->create(
			array(
				'post_type'  => 'page',
				'post_title' => 'heyhey',
			)
		);

		$attachment_ids = $this->create_attachments( 1 );

		// what keyword should each title contain.
		$ids = array(
			'MEDIA' => $attachment_ids[0],
			'POST'  => $post_id,
			'PAGE'  => $page_id,
		);

		$aioseop_options['aiosp_attachment_title_format']     = '%post_title% - MEDIA';
		$aioseop_options['aiosp_post_title_format']           = '%post_title% - POST';
		$aioseop_options['aiosp_page_title_format']           = '%post_title% - PAGE';
		$aioseop_options['aiosp_cpostactive']                 = array( 'post', 'page', 'attachment' );
		$aioseop_options['aiosp_redirect_attachement_parent'] = '';
		$aioseop_options['aiosp_rewrite_titles']              = 'on';
		update_option( 'aioseop_options', $aioseop_options );

		foreach ( $ids as $contains => $id ) {
			$link  = get_permalink( $id );
			$title = $this->parse_html( $link, array( 'title' ) );

			// should have one title tag.
			$this->assertEquals( 1, count( $title ) );
			$this->assertContains( $contains, $title[0]['#text'] );
		}
	}

	/**
	 * Provides the post types and whether they are enabled/disabled for SEO.
	 */
	public function postTypeEnabledProvider() {
		return array(
			array( 'post', true ),
			array( 'post', false ),
		);
	}
}
