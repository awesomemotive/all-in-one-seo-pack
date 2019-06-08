<?php
/**
 * Class Test_Sitemap
 *
 * @package All_in_One_SEO_Pack
 * @since 2.4.3.1
 */

/**
 * Sitemap test case.
 */
require_once AIOSEOP_UNIT_TESTING_DIR . '/base/class-sitemap-test-base.php';

/**
 * Class Test_Sitemap
 *
 * @since 2.4.3.1
 */
class Test_Sitemap extends Sitemap_Test_Base {

	/**
	 * URLs
	 *
	 * @var array $_urls Stores the external pages that need to be added to the sitemap.
	 */
	private $_urls;

	/**
	 * Set Up
	 */
	public function setUp() {
		parent::init();
		parent::setUp();
	}

	/**
	 * Tear Down
	 */
	public function tearDown() {
		parent::init();
		parent::tearDown();
	}

	/**
	 * Test Only Pages
	 *
	 * Creates posts and pages and tests whether only pages are being shown in the sitemap.
	 */
	public function test_only_pages() {
		if ( is_multisite() ) {
			$this->markTestSkipped( 'Only for single site' );
		}

		$posts = $this->setup_posts( 2 );
		$pages = $this->setup_posts( 2, 0, 'page' );

		// create a new page with a delay so that we can test if the sitemap is created in ASCENDING order.
		// @issue ( https://github.com/semperfiwebdesign/all-in-one-seo-pack/issues/2217 ).
		sleep( 1 );
		$new_page_id = $this->factory->post->create( array( 'post_type' => 'page' ) );
		$new_page = get_permalink( $new_page_id );

		$custom_options = array();
		$custom_options['aiosp_sitemap_indexes'] = '';
		$custom_options['aiosp_sitemap_images'] = '';
		$custom_options['aiosp_sitemap_gzipped'] = '';
		$custom_options['aiosp_sitemap_posttypes'] = array( 'page' );

		$this->_setup_options( 'sitemap', $custom_options );

		$file = $this->validate_sitemap(
			array(
				$pages['without'][0] => true,
				$pages['without'][1] => true,
				$posts['without'][0] => false,
				$posts['without'][1] => false,
				$new_page            => true,
			)
		);

		// in an ASCENDING order, the index of the urls will always lie in ascending order.
		$index1 = strpos( $file, $pages['without'][0] );
		$index2 = strpos( $file, $pages['without'][1] );
		$index3 = strpos( $file, $new_page );

		$this->assertGreaterThan( $index1, $index3, 'Sitemap is not in ascending order' );
		$this->assertGreaterThan( $index2, $index3, 'Sitemap is not in ascending order' );
	}

	/**
	 * Test Featured Image
	 *
	 * @requires PHPUnit 5.7
	 * Creates posts with and without featured images and tests whether the sitemap
	 * 1) contains the image tag in the posts that have images attached.
	 * 2) does not contain the image tag in the posts that do not have images attached.
	 */
	public function test_featured_image() {
		if ( is_multisite() ) {
			$this->markTestSkipped( 'Only for single site' );
		}

		$posts = $this->setup_posts( 2, 2 );

		$custom_options = array();
		$custom_options['aiosp_sitemap_indexes'] = '';
		$custom_options['aiosp_sitemap_images'] = '';
		$custom_options['aiosp_sitemap_gzipped'] = '';
		$custom_options['aiosp_sitemap_posttypes'] = array( 'post' );

		$this->_setup_options( 'sitemap', $custom_options );

		$with = $posts['with'];
		$without = $posts['without'];
		$this->validate_sitemap(
			array(
				$with[0]    => array(
					'image'         => true,
					'image:title'   => true,
					'image:caption' => true,
				),
				$with[1]    => array(
					'image'         => true,
					'image:title'   => true,
					'image:caption' => true,
				),
				$without[0] => array(
					'image' => false,
				),
				$without[1] => array(
					'image' => false,
				),
			)
		);
	}

	/**
	 * Test Exclude Images
	 *
	 * @requires PHPUnit 5.7
	 * Creates posts with and without featured images and switches OFF the images from the sitemap. Tests that the sitemap does not contain the image tag for any post.
	 */
	public function test_exclude_images() {
		if ( is_multisite() ) {
			$this->markTestSkipped( 'Only for single site' );
		}

		$posts = $this->setup_posts( 2, 2 );

		$custom_options = array();
		$custom_options['aiosp_sitemap_indexes'] = '';
		$custom_options['aiosp_sitemap_images'] = 'on';
		$custom_options['aiosp_sitemap_gzipped'] = '';
		$custom_options['aiosp_sitemap_posttypes'] = array( 'post' );

		$this->_setup_options( 'sitemap', $custom_options );

		$with = $posts['with'];
		$without = $posts['without'];
		$this->validate_sitemap(
			array(
				$with[0]    => array(
					'image' => false,
				),
				$with[1]    => array(
					'image' => false,
				),
				$without[0] => array(
					'image' => false,
				),
				$without[1] => array(
					'image' => false,
				),
			)
		);
	}

	/**
	 * Test RSS
	 *
	 * Test the generated RSS file for the sitemap.
	 *
	 * @ticket 561 XML Sitemap module - Add support for RSS/Atom updates.
	 */
	public function test_rss() {
		$posts = $this->setup_posts( 2 );

		$custom_options = array();
		$custom_options['aiosp_sitemap_indexes'] = '';
		$custom_options['aiosp_sitemap_images'] = 'on';
		$custom_options['aiosp_sitemap_gzipped'] = '';
		$custom_options['aiosp_sitemap_rss_sitemap'] = 'on';
		$custom_options['aiosp_sitemap_posttypes'] = array( 'post' );

		$this->_setup_options( 'sitemap', $custom_options );

		$this->validate_sitemap(
			array(
				$posts['without'][0] => true,
				$posts['without'][1] => true,
			)
		);

		$rss = ABSPATH . '/sitemap.rss';
		$this->assertFileExists( $rss );

		libxml_use_internal_errors( true );
		$dom = new DOMDocument();
		$dom->load( $rss );
		$content = file_get_contents( $rss );

		$this->assertTrue( $dom->schemaValidate( AIOSEOP_UNIT_TESTING_DIR . '/resources/xsd/rss.xsd' ) );
		$this->assertContains( $posts['without'][0], $content );
		$this->assertContains( $posts['without'][1], $content );
	}


	/**
	 * Test Exclude Trashed Pages
	 *
	 * Don't include content from trashed pages.
	 *
	 * @ticket 1423 XML Sitemap - Don't include content from trashed pages.
	 */
	public function test_exclude_trashed_pages() {
		$posts = $this->factory->post->create_many( 2 );
		wp_trash_post( $posts[0] );

		$custom_options = array();
		$custom_options['aiosp_sitemap_indexes'] = '';
		$custom_options['aiosp_sitemap_images'] = 'on';
		$custom_options['aiosp_sitemap_gzipped'] = '';
		$custom_options['aiosp_sitemap_posttypes'] = array( 'post' );

		$this->_setup_options( 'sitemap', $custom_options );

		$urls = array();
		foreach ( $posts as $id ) {
			$urls[] = get_permalink( $id );
		}
		$xml = $this->validate_sitemap(
			array(
				$urls[0] => false,
				$urls[1] => true,
			)
		);

		// check that the file does not contain the string __trashed because that's how trashed pages are included.
		$this->assertNotContains( $xml, '__trashed' );
	}


	/**
	 * Testing Post Type Archive Pages
	 *
	 * @ticket 155 XML Sitemap - Add support for post type archive pages and support to exclude them as well.
	 *
	 * @access public
	 * @dataProvider post_type_archive_pages_provider
	 */
	public function test_post_type_archive_pages( $post_types, $has_archive, $exclude ) {
		$tests = array();

		foreach ( $post_types as $post_type ) {
			$ids        = array();
			if ( ! in_array( $post_type, array( 'post', 'page' ) ) ) {
				register_post_type( $post_type, array( 'has_archive' => $has_archive ) );
			}

			$ids    = $this->factory->post->create_many( 2, array( 'post_type' => $post_type ) );
			foreach ( $ids as $id ) {
				$tests[ get_permalink( $id ) ] = true;
			}
			$url = get_post_type_archive_link( $post_type );
			$tests[ $url ] = $has_archive && ! $exclude;
		}

		if ( $exclude ) {
			add_filter( 'aiosp_sitemap_include_post_types_archives', array( $this, 'filter_aiosp_sitemap_include_post_types_archives' ) );
		}

		$custom_options = array();
		$custom_options['aiosp_sitemap_indexes'] = '';
		$custom_options['aiosp_sitemap_images'] = 'on';
		$custom_options['aiosp_sitemap_gzipped'] = '';
		$custom_options['aiosp_sitemap_archive'] = 'on';
		$custom_options['aiosp_sitemap_posttypes'] = $post_types;

		$this->_setup_options( 'sitemap', $custom_options );

		$this->validate_sitemap( $tests );
	}

	/**
	 * Filter AIOSEOP Sitemap Include Post Types Archive
	 *
	 * Implements the filter 'aiosp_sitemap_include_post_types_archives'.
	 */
	public function filter_aiosp_sitemap_include_post_types_archives( $types ) {
		return array();
	}

	/**
	 * Pst Type Archive Pages Provider
	 *
	 * Provide the post types for testing test_post_type_archive_pages.
	 *
	 * This will enable us to test these cases:
	 * 1) When a CPT post type is selected that DOES NOT support archives => only CPT in the sitemap.
	 * 2) When a CPT post type is selected that DOES support archives => CPT and CPT archives in the sitemap.
	 * 3) When a CPT post type is selected that DOES support archives and we exclude this => only CPT in the sitemap.
	 *
	 * @access public
	 */
	public function post_type_archive_pages_provider() {
		return array(
			array( array( 'xxxx' ), false, false ),
			array( array( 'xxxx' ), true, false ),
			array( array( 'xxxx' ), true, true ),
		);
	}

	/**
	 * Test WooCommerce Gallery
	 *
	 * Add WooCommerce product gallery images to XML sitemap.
	 *
	 * @ticket 366 Add WooCommerce product gallery images to XML sitemap
	 */
	public function test_woocommerce_gallery() {
		$woo = 'woocommerce/woocommerce.php';
		$file = dirname( dirname( AIOSEOP_UNIT_TESTING_DIR ) ) . '/';

		if ( ! file_exists( $file . $woo ) ) {
			$this->markTestSkipped( 'WooCommerce not installed. Skipping.' );
		}

		$this->plugin_to_load = $file . $woo;
		tests_add_filter( 'muplugins_loaded', array( $this, 'filter_muplugins_loaded' ) );

		activate_plugin( $woo );

		if ( ! is_plugin_active( $woo ) ) {
			$this->markTestSkipped( 'WooCommerce not activated. Skipping.' );
		}

		// create 4 attachments.
		$attachments = array();
		for ( $x = 0; $x < 4; $x++ ) {
			$attachments[] = $this->upload_image_and_maybe_attach( str_replace( '\\', '/', AIOSEOP_UNIT_TESTING_DIR . '/resources/images/footer-logo.png' ) );
		}

		$id = $this->factory->post->create( array( 'post_type' => 'product' ) );
		update_post_meta( $id, '_product_image_gallery', implode( ',', $attachments ) );
		$url = get_permalink( $id );

		$custom_options = array();
		$custom_options['aiosp_sitemap_indexes'] = '';
		$custom_options['aiosp_sitemap_images'] = '';
		$custom_options['aiosp_sitemap_gzipped'] = '';
		$custom_options['aiosp_sitemap_posttypes'] = array( 'product' );
		$this->_setup_options( 'sitemap', $custom_options );
		$this->validate_sitemap(
			array(
				$url => array(
					'image' => true,
				),
			)
		);
	}

	/**
	 * Test Only Taxonomies
	 *
	 * Adds posts to taxonomies, enables only taxonomies in the sitemap.
	 */
	public function test_only_taxonomies() {
		if ( is_multisite() ) {
			$this->markTestSkipped( 'Only for single site' );
		}

		// create 3 categories.
		$test1 = wp_create_category( 'test1' );
		$test2 = wp_create_category( 'test2' );
		$test3 = wp_create_category( 'test3' );
		$ids = $this->factory->post->create_many( 10 );
		// first 3 to test1, next 3 to test2 and let others remain uncategorized.
		for ( $x = 0; $x < 3; $x++ ) {
			wp_set_post_categories( $ids[ $x ], $test1 );
		}
		for ( $x = 3; $x < 6; $x++ ) {
			wp_set_post_categories( $ids[ $x ], $test2 );
		}
		$custom_options = array();
		$custom_options['aiosp_sitemap_indexes'] = '';
		$custom_options['aiosp_sitemap_images'] = '';
		$custom_options['aiosp_sitemap_gzipped'] = '';
		$custom_options['aiosp_sitemap_taxonomies'] = array( 'category' );
		$custom_options['aiosp_sitemap_posttypes'] = array();
		$this->_setup_options( 'sitemap', $custom_options );
		// in the sitemap, test3 should not appear as no posts have been assigned to it.
		$this->validate_sitemap(
			array(
				get_category_link( $test1 ) => true,
				get_category_link( $test2 ) => true,
				get_category_link( $test3 ) => false,
				get_category_link( 1 )      => true,
			)
		);
	}

	/**
	 * Filter MU Plugins Loaded
	 *
	 * Loads the specified plugin.
	 */
	public function filter_muplugins_loaded() {
		require $this->plugin_to_load;
	}

	/**
	 * Test Schemeless Images
	 *
	 * @requires PHPUnit 5.7
	 * Creates posts with schemeless images in the content and checks if they are being correctly included in the sitemap.
	 */
	public function test_schemeless_images() {
		if ( is_multisite() ) {
			$this->markTestSkipped( 'Only for single site' );
		}

		$id1 = $this->factory->post->create(
			array(
				'post_type'    => 'post',
				'post_content' => 'content <img src="http://example.org/image1.jpg">',
				'post_title'   => 'title with image',
			)
		);
		$id2 = $this->factory->post->create(
			array(
				'post_type'    => 'post',
				'post_content' => 'content <img src="//example.org/image2.jpg">',
				'post_title'   => 'title with image',
			)
		);
		$id3 = $this->factory->post->create(
			array(
				'post_type'    => 'post',
				'post_content' => 'content <img src="/image3.jpg">',
				'post_title'   => 'title with image',
			)
		);
		$urls = array( get_permalink( $id1 ), get_permalink( $id2 ), get_permalink( $id3 ) );

		$custom_options = array();
		$custom_options['aiosp_sitemap_indexes'] = '';
		$custom_options['aiosp_sitemap_images'] = '';
		$custom_options['aiosp_sitemap_gzipped'] = '';
		$custom_options['aiosp_sitemap_posttypes'] = array( 'post' );

		$this->_setup_options( 'sitemap', $custom_options );

		$this->validate_sitemap(
			array(
				$urls[0] => array(
					'image' => true,
				),
				$urls[1] => array(
					'image' => true,
				),
				$urls[2] => array(
					'image' => true,
				),
			)
		);
	}

	/**
	 * Test Sitemap Index Pagination
	 *
	 * Creates different types of posts, enables indexes and pagination and checks if the posts are being paginated correctly without additional/blank sitemaps.
	 *
	 * @requires PHPUnit 5.7
	 * @dataProvider enabledPostTypes
	 */
	public function test_sitemap_index_pagination( $enabled_post_type, $enabled_post_types_count, $cpt ) {
		if ( is_multisite() ) {
			$this->markTestSkipped( 'Only for single site' );
		}

		// choose numbers which are not multiples of each other.
		$num_posts = 22;
		$per_xml = 7;

		if ( in_array( 'post', $enabled_post_type ) ) {
			$this->factory->post->create_many( $num_posts );
		}

		if ( in_array( 'page', $enabled_post_type ) ) {
			$this->factory->post->create_many( $num_posts, array( 'post_type' => 'page' ) );
		}

		if ( in_array( 'attachment', $enabled_post_type ) ) {
			$this->create_attachments( $num_posts );
		}

		if ( ! is_null( $cpt ) ) {
			register_post_type( $cpt );
			$this->factory->post->create_many( $num_posts, array( 'post_type' => $cpt ) );
		}

		$custom_options = array();
		$custom_options['aiosp_sitemap_indexes'] = 'on';
		$custom_options['aiosp_sitemap_max_posts'] = $per_xml;
		$custom_options['aiosp_sitemap_images'] = 'on';
		$custom_options['aiosp_sitemap_gzipped'] = '';
		$custom_options['aiosp_sitemap_posttypes'] = $enabled_post_type;
		$custom_options['aiosp_sitemap_taxonomies'] = array();

			$this->_setup_options( 'sitemap', $custom_options );

		// calculate the number of sitemaps expected in the index. The +1 is for the sitemap_addl.xml that includes the home page.
		$expected = intval( $enabled_post_types_count * ceil( $num_posts / $per_xml ) + 1 );
		$got = $this->count_sitemap_elements( array( '<sitemap>' ) );

		$this->assertEquals( $expected, $got['<sitemap>'] );
	}

	/**
	 * Test Jetpack Gallery
	 *
	 * @requires PHPUnit 5.7
	 * Tests posts with and without images with dependency on jetpack gallery.
	 *
	 * @ticket 1230 XML Sitemap - Add support for images in JetPack and NextGen galleries
	 */
	public function test_jetpack_gallery() {
		$this->markTestSkipped( 'Skipping this till actual use case is determined.' );

		$jetpack = 'jetpack/jetpack.php';
		$file = dirname( dirname( AIOSEOP_UNIT_TESTING_DIR ) ) . '/';
		if ( ! file_exists( $file . $jetpack ) ) {
			$this->markTestSkipped( 'JetPack not installed. Skipping.' );
		}
		$this->plugin_to_load = $file . $jetpack;
		tests_add_filter( 'muplugins_loaded', array( $this, 'filter_muplugins_loaded' ) );
		activate_plugin( $jetpack );
		if ( ! is_plugin_active( $jetpack ) ) {
			$this->markTestSkipped( 'JetPack not activated. Skipping.' );
		}
		$posts = $this->setup_posts( 1, 1 );
		// create 4 attachments.
		$attachments = array();
		for ( $x = 0; $x < 4; $x++ ) {
			$attachments[] = $this->upload_image_and_maybe_attach( str_replace( '\\', '/', AIOSEOP_UNIT_TESTING_DIR . '/resources/images/footer-logo.png' ) );
		}
		$id = $this->factory->post->create(
			array(
				'post_type'    => 'post',
				'post_content' => '[gallery size="medium" link="file" columns="5" type="slideshow" ids="' . implode( ',', $attachments ) . '"]',
				'post_title'   => 'jetpack',
			)
		);
		$posts['with'][] = get_permalink( $id );
		$custom_options = array();
		$custom_options['aiosp_sitemap_indexes'] = '';
		$custom_options['aiosp_sitemap_images'] = '';
		$custom_options['aiosp_sitemap_gzipped'] = '';
		$custom_options['aiosp_sitemap_posttypes'] = array( 'post' );
		$this->_setup_options( 'sitemap', $custom_options );
		$with = $posts['with'];
		$without = $posts['without'];
		$this->validate_sitemap(
			array(
				$with[0]    => array(
					'image' => true,
				),
				$with[1]    => array(
					'image' => true,
				),
				$without[0] => array(
					'image' => false,
				),
			)
		);
	}

	/**
	 * Test NextGen Gallery
	 *
	 * @requires PHPUnit 5.7
	 * Tests posts with and without images with dependency on nextgen gallery.
	 *
	 * @ticket 1230 XML Sitemap - Add support for images in JetPack and NextGen galleries
	 */
	public function test_nextgen_gallery() {
		wp_set_current_user( 1 );
		$nextgen = 'nextgen-gallery/nggallery.php';
		$file = dirname( dirname( AIOSEOP_UNIT_TESTING_DIR ) ) . '/';

		if ( ! file_exists( $file . $nextgen ) ) {
			$this->markTestSkipped( 'NextGen Gallery not installed. Skipping.' );
		}
		$this->plugin_to_load = $file . $nextgen;
		tests_add_filter( 'muplugins_loaded', array( $this, 'filter_muplugins_loaded' ) );
		activate_plugin( $nextgen );
		if ( ! is_plugin_active( $nextgen ) ) {
			$this->markTestSkipped( 'NextGen Gallery not activated. Skipping.' );
		}
		do_action( 'init' );
		// nextgen shortcode does not work without creating a gallery or images. So we will have to create a gallery to do this.
		$nggdb      = new nggdb();
		$gallery_id = nggdb::add_gallery();
		$images = array(
			$nggdb->add_image( $gallery_id, 'x.png', 'x', 'x', 'eyJiYWNrdXAiOnsiZmlsZW5hbWUiOiJzYW1wbGUucG5nIiwid2lkdGgiOjI0OCwiaGVpZ2h0Ijo5OCwiZ2VuZXJhdGVkIjoiMC4wMjM3MzMwMCAxNTA3MDk1MTcwIn0sImFwZXJ0dXJlIjpmYWxzZSwiY3JlZGl0IjpmYWxzZSwiY2FtZXJhIjpmYWxzZSwiY2FwdGlvbiI6ZmFsc2UsImNyZWF0ZWRfdGltZXN0YW1wIjpmYWxzZSwiY29weXJpZ2h0IjpmYWxzZSwiZm9jYWxfbGVuZ3RoIjpmYWxzZSwiaXNvIjpmYWxzZSwic2h1dHRlcl9zcGVlZCI6ZmFsc2UsImZsYXNoIjpmYWxzZSwidGl0bGUiOmZhbHNlLCJrZXl3b3JkcyI6ZmFsc2UsIndpZHRoIjoyNDgsImhlaWdodCI6OTgsInNhdmVkIjp0cnVlLCJtZDUiOiI3ZWUyMjVjOTNkZmNhMTMyYjQzMTc5ZjJiMGYwZTc2NiIsImZ1bGwiOnsid2lkdGgiOjI0OCwiaGVpZ2h0Ijo5OCwibWQ1IjoiN2VlMjI1YzkzZGZjYTEzMmI0MzE3OWYyYjBmMGU3NjYifSwidGh1bWJuYWlsIjp7IndpZHRoIjoyNDAsImhlaWdodCI6OTgsImZpbGVuYW1lIjoidGh1bWJzX3NhbXBsZS5wbmciLCJnZW5lcmF0ZWQiOiIwLjMwNDUzNDAwIDE1MDcwOTUxNzAifSwibmdnMGR5bi0weDB4MTAwLTAwZjB3MDEwYzAxMHIxMTBmMTEwcjAxMHQwMTAiOnsid2lkdGgiOjI0OCwiaGVpZ2h0Ijo5OCwiZmlsZW5hbWUiOiJzYW1wbGUucG5nLW5nZ2lkMDE3LW5nZzBkeW4tMHgweDEwMC0wMGYwdzAxMGMwMTByMTEwZjExMHIwMTB0MDEwLnBuZyIsImdlbmVyYXRlZCI6IjAuMTgwMzI0MDAgMTUyMTAxMTI1NCJ9fQ==' ),
			$nggdb->add_image( $gallery_id, 'x.png', 'x', 'x', 'eyJiYWNrdXAiOnsiZmlsZW5hbWUiOiJzYW1wbGUucG5nIiwid2lkdGgiOjI0OCwiaGVpZ2h0Ijo5OCwiZ2VuZXJhdGVkIjoiMC4wMjM3MzMwMCAxNTA3MDk1MTcwIn0sImFwZXJ0dXJlIjpmYWxzZSwiY3JlZGl0IjpmYWxzZSwiY2FtZXJhIjpmYWxzZSwiY2FwdGlvbiI6ZmFsc2UsImNyZWF0ZWRfdGltZXN0YW1wIjpmYWxzZSwiY29weXJpZ2h0IjpmYWxzZSwiZm9jYWxfbGVuZ3RoIjpmYWxzZSwiaXNvIjpmYWxzZSwic2h1dHRlcl9zcGVlZCI6ZmFsc2UsImZsYXNoIjpmYWxzZSwidGl0bGUiOmZhbHNlLCJrZXl3b3JkcyI6ZmFsc2UsIndpZHRoIjoyNDgsImhlaWdodCI6OTgsInNhdmVkIjp0cnVlLCJtZDUiOiI3ZWUyMjVjOTNkZmNhMTMyYjQzMTc5ZjJiMGYwZTc2NiIsImZ1bGwiOnsid2lkdGgiOjI0OCwiaGVpZ2h0Ijo5OCwibWQ1IjoiN2VlMjI1YzkzZGZjYTEzMmI0MzE3OWYyYjBmMGU3NjYifSwidGh1bWJuYWlsIjp7IndpZHRoIjoyNDAsImhlaWdodCI6OTgsImZpbGVuYW1lIjoidGh1bWJzX3NhbXBsZS5wbmciLCJnZW5lcmF0ZWQiOiIwLjMwNDUzNDAwIDE1MDcwOTUxNzAifSwibmdnMGR5bi0weDB4MTAwLTAwZjB3MDEwYzAxMHIxMTBmMTEwcjAxMHQwMTAiOnsid2lkdGgiOjI0OCwiaGVpZ2h0Ijo5OCwiZmlsZW5hbWUiOiJzYW1wbGUucG5nLW5nZ2lkMDE3LW5nZzBkeW4tMHgweDEwMC0wMGYwdzAxMGMwMTByMTEwZjExMHIwMTB0MDEwLnBuZyIsImdlbmVyYXRlZCI6IjAuMTgwMzI0MDAgMTUyMTAxMTI1NCJ9fQ==' ),
		);
		$shortcode = '[ngg_images display_type="photocrati-nextgen_basic_thumbnails" image_ids="' . implode( ',', $images ) . '"]';
		$content = aioseop_do_shortcodes( $shortcode );
		if ( 'We cannot display this gallery' === $content ) {
			$this->markTestSkipped( 'NextGen Gallery not working properly. Skipping.' );
		}
		// $content will output div and img tags but the img tags have an empty src.
		$this->markTestIncomplete( 'We cannot add images in such a way that the shortcode displays the "src" attribute in the image tags. Skipping.' );
		$id = $this->factory->post->create(
			array(
				'post_type'    => 'post',
				'post_content' => $shortcode,
				'post_title'   => 'nextgen',
			)
		);
		$url = get_permalink( $id );
		$custom_options = array();
		$custom_options['aiosp_sitemap_indexes'] = '';
		$custom_options['aiosp_sitemap_images'] = '';
		$custom_options['aiosp_sitemap_gzipped'] = '';
		$custom_options['aiosp_sitemap_posttypes'] = array( 'post' );
		$this->_setup_options( 'sitemap', $custom_options );
		$this->validate_sitemap(
			array(
				$url => array(
					'image' => true,
				),
			)
		);
	}

	/**
	 * Test Additional External URLS
	 *
	 * Add external URLs to the sitemap using the filter 'aiosp_sitemap_addl_pages_only'.
	 *
	 * @dataProvider externalPagesProvider
	 */
	public function test_add_external_urls( $url1, $url2 ) {
		if ( is_multisite() ) {
			$this->markTestSkipped( 'Only for single site' );
		}

		$this->_urls = array( $url1, $url2 );

		$posts = $this->setup_posts( 2 );

		add_filter( 'aiosp_sitemap_addl_pages_only', array( $this, 'filter_aiosp_sitemap_addl_pages_only' ) );

		$custom_options = array();
		$custom_options['aiosp_sitemap_indexes'] = '';
		$custom_options['aiosp_sitemap_images'] = '';
		$custom_options['aiosp_sitemap_gzipped'] = '';
		$custom_options['aiosp_sitemap_posttypes'] = array( 'post' );

		$this->_setup_options( 'sitemap', $custom_options );

		$without = $posts['without'];
		$this->validate_sitemap(
			array(
				$without[0]  => true,
				$without[1]  => true,
				$url1['loc'] => true,
				$url2['loc'] => true,
			)
		);
	}

	/**
	 * Test Index
	 *
	 * @requires PHPUnit 5.7
	 * Enables indexes and tests that the index and individual sitemaps are all valid according to the schema.
	 *
	 * @ticket 1371 Correct tags order according to Sitemap protocol
	 */
	public function test_index() {
		if ( is_multisite() ) {
			$this->markTestSkipped( 'Only for single site' );
		}

		$posts = $this->setup_posts( 2, 2 );

		$custom_options = array();
		$custom_options['aiosp_sitemap_indexes'] = 'on';
		$custom_options['aiosp_sitemap_images'] = '';
		$custom_options['aiosp_sitemap_gzipped'] = '';
		$custom_options['aiosp_sitemap_posttypes'] = array( 'post' );

		$this->_setup_options( 'sitemap', $custom_options );

		$this->validate_sitemap_index( array( 'post' ) );
	}

	/**
	 * Filter AIOSEOP Sitemap Additional Pages Only
	 *
	 * Returns the urls to be added to the sitemap.
	 */
	public function filter_aiosp_sitemap_addl_pages_only() {
		return $this->_urls;
	}

	/**
	 * External Pages Provider
	 *
	 * Provides the external pages that need to be added to the sitemap.
	 */
	public function externalPagesProvider() {
		return array(
			array(
				array(
					'loc'        => 'http://www.one.com',
					'lastmod'    => '2018-01-18T21:46:44Z',
					'changefreq' => 'daily',
					'priority'   => '1.0',
				),
				array(
					'loc'        => 'http://www.two.com',
					'lastmod'    => '2018-01-18T21:46:44Z',
					'changefreq' => 'daily',
					'priority'   => '1.0',
				),
			),
		);
	}

	/**
	 * Test External Images
	 *
	 * Creates posts with external images and uses the filter 'aioseop_images_allowed_from_hosts' to allow only a particular host's images to be included in the sitemap.
	 */
	public function test_external_images() {
		if ( is_multisite() ) {
			$this->markTestSkipped( 'Only for single site' );
		}

		$posts = $this->setup_posts( 2 );

		$id1 = $this->factory->post->create(
			array(
				'post_type'    => 'post',
				'post_content' => 'content <img src="http://www.x.com/image.jpg">',
				'post_title'   => 'title with image',
			)
		);
		$id2 = $this->factory->post->create(
			array(
				'post_type'    => 'post',
				'post_content' => 'content <img src="http://www.y.com/image.jpg">',
				'post_title'   => 'title with image',
			)
		);
		$posts['with'] = array( get_permalink( $id1 ), get_permalink( $id2 ) );

		// allow only www.x.com.
		add_filter( 'aioseop_images_allowed_from_hosts', array( $this, 'filter_aioseop_images_allowed_from_hosts' ) );

		$custom_options = array();
		$custom_options['aiosp_sitemap_indexes'] = '';
		$custom_options['aiosp_sitemap_images'] = '';
		$custom_options['aiosp_sitemap_gzipped'] = '';
		$custom_options['aiosp_sitemap_posttypes'] = array( 'post' );

		$this->_setup_options( 'sitemap', $custom_options );

		$with = $posts['with'];
		$without = $posts['without'];
		$this->validate_sitemap(
			array(
				$with[0]    => array(
					'image' => true,
				),
				$with[1]    => array(
					'image' => false,
				),
				$without[0] => array(
					'image' => false,
				),
				$without[1] => array(
					'image' => false,
				),
			)
		);
	}

	/**
	 * Filter AIOSEOP Images Allowed from Hosts
	 *
	 * Implements the filter 'aioseop_images_allowed_from_hosts' to allow speficic hosts.
	 */
	public function filter_aioseop_images_allowed_from_hosts( $hosts ) {
		$hosts[] = 'www.x.com';
		return $hosts;
	}

	/**
	 * Enabled Post Types
	 *
	 * Provides posts types to test test_sitemap_index_pagination against.
	 */
	public function enabledPostTypes() {
		return array(
			array( array( 'post' ), 1, null ),
			array( array( 'post', 'page' ), 2, null ),
			array( array( 'product' ), 1, 'product' ),
			array( array( 'attachment', 'product' ), 2, 'product' ),
			array( array( 'all', 'post', 'page' ), 2, null ),
			array( array( 'all', 'post', 'page', 'attachment', 'product' ), 4, 'product' ),
		);
	}

	/**
	 * Test Make External URLs Valid
	 *
	 * Add invalid external URLs to the sitemap and see if they are shown as valid in the sitemap.
	 *
	 * @dataProvider invalidExternalPagesProvider
	 */
	public function test_make_external_urls_valid( $urls ) {
		$posts = $this->setup_posts( 2 );

		$pages  = array();
		foreach ( $urls as $url ) {
			$pages[ $url['loc'] ] = array(
				'prio' => $url['priority'],
				'freq' => $url['changefreq'],
				'mod'  => $url['lastmod'],
			);
		}

		$custom_options = array();
		$custom_options['aiosp_sitemap_indexes'] = '';
		$custom_options['aiosp_sitemap_images'] = '';
		$custom_options['aiosp_sitemap_gzipped'] = '';
		$custom_options['aiosp_sitemap_posttypes'] = array( 'post' );
		$custom_options['aiosp_sitemap_addl_pages'] = $pages;

		$this->_setup_options( 'sitemap', $custom_options );

		$validate_urls  = array();
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

		// so all urls.
	}

	/**
	 * Invalid External Pages Provider
	 *
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

