<?php
/**
 * Class Test_Sitemap
 *
 * @package
 */

/**
 * Sitemap test case.
 */

require_once AIOSEOP_UNIT_TESTING_DIR . '/base/class-sitemap-test-base.php';

class Test_Sitemap extends Sitemap_Test_Base {

	/**
	 * @var array $_urls Stores the external pages that need to be added to the sitemap.
	 */
	private $_urls;

	public function setUp() {
		parent::init();
		parent::setUp();
	}

	public function tearDown() {
		parent::init();
		parent::tearDown();
	}

	/**
	 * Attaches images to posts and checks that the image included in the sitemap is the full size image.
	 *
	 * @dataProvider fullSizeImageProvider
	 */
	public function test_images_are_full_size( $type ) {
		if ( is_multisite() ) {
			$this->markTestSkipped( 'Only for single site' );
		}

		$url = null;

		$image_to_use	= 'large-square.png';

		switch ( $type ) {
			case 'featured':
				$posts	= $this->setup_posts( 0, 1, 'post', $image_to_use );
				$url	= $posts['with'][0];
				break;
			case 'content':
				$array		= $this->setup_posts( 1 );
				$url		= $array['without'][0];
				$attachment_id	= $this->upload_image_and_maybe_attach( str_replace( '\\', '/', AIOSEOP_UNIT_TESTING_DIR . "/resources/images/$image_to_use" ) );
				$image_url	= wp_get_attachment_url( $attachment_id );
				wp_update_post( array( 'ID' => $array['ids']['without'][0], 'post_content' => "blah <img src='$image_url'/>" ) );
				break;
		}

		$custom_options = array();
		$custom_options['aiosp_sitemap_indexes'] = '';
		$custom_options['aiosp_sitemap_images'] = '';
		$custom_options['aiosp_sitemap_gzipped'] = '';
		$custom_options['aiosp_sitemap_posttypes'] = array( 'post' );

		$this->_setup_options( 'sitemap', $custom_options );

		$xml	= $this->validate_sitemap(
			array(
				$url => array(
					'image' => true,
				),
			)
		);

		// the sitemap will contain something like large-square-54.png.
		$this->assertRegExp( '/large\-square(\-\d+)?\.png/', $xml );
	}



	/**
	 * Provides arguments to test test_images_are_full_size.
	 */
	public function fullSizeImageProvider() {
		return array(
			array( 'featured' ),
			array( 'content' ),
		);
	}

	/**
	 * Provides the invalid external pages that need to be added to the sitemap.
	 */
	public function invalidExternalPagesProvider() {
		return array(
			array(
				array(
					array(
						'loc'        => 'http://www.absolute.com',
						'lastmod'    => '2018-01-18T21:46:44Z',
						'changefreq' => 'daily',
						'priority'   => '1.0',
					),
					array(
						'loc'        => 'http://www.absolute.com/',
						'lastmod'    => '2018-01-18T21:46:44Z',
						'changefreq' => 'daily',
						'priority'   => '1.0',
					),
					array(
						'loc'        => 'www.absolute.com/page1',
						'lastmod'    => '2018-01-18T21:46:44Z',
						'changefreq' => 'daily',
						'priority'   => '1.0',
					),
					array(
						'loc'        => '//www.absolute.com/page2',
						'lastmod'    => '2018-01-18T21:46:44Z',
						'changefreq' => 'daily',
						'priority'   => '1.0',
					),
					array(
						'loc'        => '/five/page',
						'lastmod'    => '2018-01-18T21:46:44Z',
						'changefreq' => 'daily',
						'priority'   => '1.0',
					),
				),
			),
		);
	}
}