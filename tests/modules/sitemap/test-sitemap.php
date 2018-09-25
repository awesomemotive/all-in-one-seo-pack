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
	 * Add invalid external URLs to the sitemap and see if they are shown as valid in the sitemap.
	 *
	 * @dataProvider invalidExternalPagesProvider
	 */
	public function test_make_external_urls_valid( $urls ) {
		if ( is_multisite() ) {
			$this->markTestSkipped( 'Only for single site' );
		}

		$posts = $this->setup_posts( 2 );

		$pages	= array();
		foreach ( $urls as $url ) {
			$pages[ $url['loc'] ] = array(
				'prio'		=> $url['priority'],
				'freq'		=> $url['changefreq'],
				'mod'		=> $url['lastmod'],
			);
		}

		$custom_options = array();
		$custom_options['aiosp_sitemap_indexes'] = '';
		$custom_options['aiosp_sitemap_images'] = '';
		$custom_options['aiosp_sitemap_gzipped'] = '';
		$custom_options['aiosp_sitemap_posttypes'] = array( 'post' );
		$custom_options['aiosp_sitemap_addl_pages'] = $pages;

		$this->_setup_options( 'sitemap', $custom_options );

		$validate_urls	= array();
		foreach ( $urls as $url ) {
			// the ones with http should be present and the ones without http should not be present.
			$validate_urls[ $url['loc'] ] = strpos( $url['loc'], 'http' ) !== false;
			if ( strpos( $url['loc'], 'absolute' ) !== false ) {
				// the ones with absolute should be absolute.
				$validate_urls[ 'http://' . str_replace( array( 'http', '://' ), '', ltrim( $url['loc'], '/' ) ) ] = true;
			} else {
				// the ones without absolute should be relative.
				$validate_urls[ home_url( ltrim( $url['loc'], '/' ) ) ] = true;
			}
		}

		$without = $posts['without'];
		$this->validate_sitemap(
			array_merge(
				array(
					$without[0] => true,
					$without[1] => true,
				),
				$validate_urls
			)
		);

		// so all urls 
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