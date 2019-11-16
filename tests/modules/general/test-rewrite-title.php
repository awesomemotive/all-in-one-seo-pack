<?php
/**
 * Test Rewrite Title
 *
 * @package All_in_One_SEO_Pack
 * @since 3.0
 */

/**
 * AIOSEOP test base
 */
require_once AIOSEOP_UNIT_TESTING_DIR . '/base/class-aioseop-test-base.php';

/**
 * Contains test cases for title rewrite feature (macros).
 *
 * @since 3.0
 */
class Test_Rewrite_Title extends AIOSEOP_Test_Base {

	/**
	 * PHPUnit fixture.
	 *
	 * Sets up the test environment.
	 *
	 * @since 3.0
	 */
	public function setUp() {
		parent::setUp();

		// required, otherwise unit tests below fail.
		remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
		remove_action( 'wp_print_styles', 'print_emoji_styles' );
	}

	/**
	 * Title format macro test.
	 *
	 * Tests whether the rewrite feature replaces macros in the title format.
	 *
	 * @dataProvider macroProvider
	 *
	 * @group title_macros
	 *
	 * @since 3.0
	 */
	public function test_title_format_macros( $macro, $type = 'post' ) {

		$this->markTestIncomplete( 'Test does not trigger wp_title() - https://github.com/semperfiwebdesign/all-in-one-seo-pack/issues/2387' );

		wp_set_current_user( 1 );
		global $aioseop_options;

		$blog_name = 'Example Blog Name';

		$aioseop_options['aiosp_rewrite_titles']        = 1;
		$aioseop_options['aiosp_post_title_format']     = $macro;
		$aioseop_options['aiosp_category_title_format'] = $macro;

		update_option( 'aioseop_options', $aioseop_options );
		update_option( 'blogname', $blog_name );

		$id = 0;
		switch ( $type ) {
			case 'post':
				$args = array(
					'post_type'  => 'post',
					'post_title' => 'Example Title',
				);
				$id   = $this->factory->post->create( $args );
				break;
			case 'category':
				$args = array( 'taxonomy' => 'category' );
				$id   = $this->factory->term->create( $args );
				break;
		}

		$link  = get_permalink( $id );
		$title = $this->parse_html( $link, array( 'title' ) );

		$this->assertEquals( 1, count( $title ) );
		$this->assertContains( $blog_name, $title[0]['#text'] );

		return $title;
	}

	/**
	 * %xxx_title% macro comparison test.
	 *
	 * Tests whether the rewrite feature returns the same result for both site title macros.
	 *
	 * @group title_macros
	 *
	 * @since 3.0
	 */
	public function test_compare_title_macros() {
		$site_title = $this->test_title_format_macros( '%site_title%' );
		$blog_title = $this->test_title_format_macros( '%blog_title%' );

		$this->assertSame( $site_title, $blog_title );
	}

	/**
	 * Title format macro provider.
	 *
	 * Provides combinations to test_title_format_macros().
	 *
	 * @group title_macros
	 *
	 * @since 3.0
	 */
	public function macroProvider() {
		return array(
			'%site_title% & post'     => array( '%site_title', 'post' ),
			'%site_title% & category' => array( '%site_title', 'category' ),
			'%blog_title% & post'     => array( '%blog_title', 'post' ),
			'%blog_title% & category' => array( '%blog_title', 'category' ),
		);
	}
}
