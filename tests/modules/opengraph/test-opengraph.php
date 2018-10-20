<?php
/**
 * Class Test_Opengraph
 *
 * @package
 */

/**
 * Advance Custom Fields test cases.
 */

require_once AIOSEOP_UNIT_TESTING_DIR . '/base/class-aioseop-test-base.php';

class Test_Opengraph extends AIOSEOP_Test_Base {

	public function setUp() {
		$this->init( true );
	}

	/**
	 * Tests the different combinations for displaying "og:image".
	 *
	 * @dataProvider imageSourceProvider
	 */
	public function test_og_image( $image_source, $use_filter, $str_in_image_url = '' ) {
		if ( is_multisite() ) {
			$this->markTestSkipped( 'Only for single site' );
		}

		$image_to_use	= 'large-square.png';
		$post_url		= null;

		switch ( $image_source ) {
			case 'featured':
			case 'attach':
			case 'auto':
				$array		= $this->setup_posts( 0, 1, 'post', $image_to_use );
				$post_url	= $array['with'][0];
				break;
			case 'content':
				$array		= $this->setup_posts( 1 );
				$post_url	= $array['without'][0];
				$attachment_id	= $this->upload_image_and_maybe_attach( str_replace( '\\', '/', AIOSEOP_UNIT_TESTING_DIR . "/resources/images/$image_to_use" ) );
				$image_url	= wp_get_attachment_url( $attachment_id );
				wp_update_post( array( 'ID' => $array['ids']['without'][0], 'post_content' => "blah <img src='$image_url'/>" ) );
				break;
		}

		$custom_options = array();
		$custom_options['aiosp_opengraph_defimg'] = $image_source;
		$custom_options['aiosp_opengraph_types'] = array( 'post' );
		$this->_setup_options( 'opengraph', $custom_options );

		if ( $use_filter ) {
			add_filter( 'aioseop_attachment_size', array( $this, 'aioseop_filter_attachment_size' ) );
		}

		$meta = $this->parse_html( $post_url, array( 'meta' ) );

		// should have atleast one meta tag.
		$this->assertGreaterThan( 1, count( $meta ) );
		// should have exactly one og:image meta tag.
		$props	= array();
		foreach ( $meta as $m ) {
			if ( ! isset( $m['property'] ) ) {
				continue;
			}
			$props[ $m['property'] ] = $m['content'];
		}

		$this->assertArrayHasKey( 'og:image', $props );

		$image_url = $props[ 'og:image' ];

		$this->assertContains( '/large', $image_url, 'Incorrect image found!', true );

		// if we expect a full size image, then the url will not contain an 'x' (e.g. thumbnails might be <image>-150x150.png)
		if ( ! $use_filter ) {
			$this->assertNotContains( 'x', basename( $image_url ), 'Incorrect image found!', true );
		}
		if ( $str_in_image_url ) {
			$this->assertContains( $str_in_image_url, $image_url, 'Incorrect image size found!', true );
		}
	}

	function aioseop_filter_attachment_size( $size ) {
		return 'medium';
	}

	/**
	 * Provides data to test og:image.
	 */
	public function imageSourceProvider() {
		return array(
			array( 'featured', false ),
			array( 'featured', true, '300x300' ),
			array( 'attach', false ),
			array( 'attach', true, '300x300' ),
			array( 'auto', false ),
			array( 'auto', true, '300x300' ),
			array( 'content', false ),
			array( 'content', true, '300x300' ),
		);
	}

	/**
	 * Checks the home page's meta tags.
	 *
	 * @dataProvider metaProvider
	 */
	public function test_home_page( $title_meta, $desc_meta ) {
		$this->markTestIncomplete( 'Cannot seem to get any meta tag when accessing the home page. Have set home page as static page as well as showing latest posts. Nothing works.' );

		$id = $this->factory->post->create(array('post_type' => 'page'));
		$home_url = get_site_url();//trailingslashit( get_site_url() ) . 'house';
		//update_option( 'home', $home_url );

		update_option( 'show_on_front', 'page' );
		update_option( 'page_on_front', $id );

		$custom_options = array();
		$custom_options['aiosp_opengraph_hometitle'] = $title_meta;
		$custom_options['aiosp_opengraph_description'] = $desc_meta;

		$this->_setup_options( 'opengraph', $custom_options );

		do_action( 'init' );
		$meta = $this->parse_html( $home_url, array( 'meta' ) );

		print_r($meta);

		// should have atleast one meta tag.
		$this->assertGreaterThan( 1, count( $meta ) );

		$title = null;
		$desc = null;
		foreach ( $meta as $m ) {
			if ( ! isset( $m['property'] ) ) {
				continue;
			}
			if ( 'og:title' === $m['property'] ) {
				$title = $m['content'];
				break;
			}
			if ( 'og:description' === $m['property'] ) {
				$desc = $m['content'];
				break;
			}
		}
		$this->assertEquals( $title_meta, $title );
		$this->assertEquals( $desc_meta, $desc );
		$this->assertContains( '&', $desc );
		$this->assertNotContains( '&amp;', $desc );
		$this->assertContains( '&', $title );
		$this->assertNotContains( '&amp;', $title );
	}

	public function metaProvider() {
		return array(
			array( 'Half & Half', 'Two & a Half men' ),
		);
	}
}
