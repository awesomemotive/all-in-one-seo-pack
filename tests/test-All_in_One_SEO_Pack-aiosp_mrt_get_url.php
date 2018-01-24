<?php
/**
 * Testing All_in_One_SEO_Pack_Sitemap::aiosp_mrt_get_url();
 *
 * Function:  Gets the url associated to the post in WP_Query
 * Expected:  The post permalink is returned.
 * Actual:    It is throwing a PHP Error. count(): `Parameter must be an array or an object that implements Countable`
 * Reproduce: Go to post edit screen.
 *
 * @since 2.4.4
 *
 * @ticket 1491 Warning: count(): Parameter must be an array or an object that implements Countable #1491
 * @link https://github.com/semperfiwebdesign/all-in-one-seo-pack/issues/1355
 *
 * @group url
 * @group post_permalink
 * @group All_in_One_SEO_Pack
 * @group aiosp_mrt_get_url
 *
 *
 */

/**
 * Namespace for other tests that may relate, or may be part of a root test (sitemap).
 * NOTE: The intent is to keep tests isolated.
 */
namespace test\aioseop\sitemap\aiosp_mrt_get_url {

	use WP_UnitTestCase;
	use All_in_One_SEO_Pack_Sitemap;

	/**
	 * Contains the test case scenario.
	 *
	 * Extending allows adding WP's Testing Unit; which extends to PHPUnit
	 *
	 * @package WP_UnitTestCase
	 */
	class All_in_One_SEO_Pack_Test_aiosp_mrt_get_url extends WP_UnitTestCase	{

		/**
		 * PHPUnit Fixture - setUpBeforeClass()
		 *
		 * the setUpBeforeClass() and tearDownAfterClass() template methods are called before the first test of the test
		 * case class is run and after the last test of the test case class is run, respectively.
		 *
		 * @link https://phpunit.de/manual/current/en/fixtures.html
		 */
		public static function setUpBeforeClass() {
			// Do Stuff...
			$a = '';
		}



		/**
		 * PHPUnit Fixture - setUp()
		 *
		 * Sets up the environment to test.
		 * NOTE: Patent must be called first according to WP Handbook.
		 *
		 * @link https://make.wordpress.org/core/handbook/testing/automated-testing/writing-phpunit-tests/#shared-setup-between-related-tests
		 */
		public function setUp() {
			parent::setUp();
			// Do Stuff...
			$ids = $this->setup_posts( 10 );
		}

		public function setup_posts( $how_many = 0 ) {
			$args = array(
				'post_type'    => 'post',
				'post_content' => 'content without image',
				'post_title'   => 'title without image'
			);

			$ids = $this->factory->post->create_many( $how_many, $args );

			foreach ( $ids as $v1_id ) {
				$image = str_replace( '\\', '/', __DIR__ ) . '/resources/images/footer-logo.png';
				$attachment_id = $this->factory->attachment->create_upload_object( $image, $v1_id );
				if ( 0 !== $v1_id ) {
					update_post_meta( $v1_id, '_thumbnail_id', $attachment_id );
				}
			}

			return $ids;
		}

		/**
		 * PHPUnit Fixture - tearDown()
		 *
		 * Sets up the environment to test.
		 * NOTE: Patent must be called last according to WP Handbook.
		 *
		 * @link https://make.wordpress.org/core/handbook/testing/automated-testing/writing-phpunit-tests/#shared-setup-between-related-tests
		 */
		public function tearDown() {
			// Do Stuff...
			parent::tearDown();
		}

		/**
		 * PHPUnit Fixture - tearDownAfterClass()
		 */
		public static function tearDownAfterClass() {
			// Do Stuff...
		}

		/**
		 * PHPUnit Fixture - onNotSuccessfulTest()
		 *
		 * @param Exception $e
		 */
//		public function onNotSuccessfulTest( Throwable $e ) {
//			// Do Stuff...
//			throw $e;
//			//parent::onNotSuccessfulTest( $e );
//		}

		/**
		 *
		 */
		private function _set_attachment() {

		}

		/**
		 * Test - AIOSEOP_Sitemap::is_image_valid()
		 *
		 * Issue #1491 Class Method to test.
		 *
		 * set_current_screen
		 * @link https://codex.wordpress.org/Plugin_API/Admin_Screen_Reference
		 *
		 * @group url
		 * @group post_permalink
		 * @group All_in_One_SEO_Pack
		 * @group aiosp_mrt_get_url
		 *
		 * @test
		 * @expectedException count(): Parameter must be an array or an object that implements Countable
		 * @expectedExceptionMessage Error Exception Message in Annotations.
		 *
		 */
		public function test_aiosp_mrt_get_url() {

			$aioseop_class = new \All_in_One_SEO_Pack();
			// On post Edit Screen. ERROR.
			set_current_screen( 'post.php?post=2&action=edit' );

			global $wp_query;
			$post_permalink = get_permalink( 2 );
			//ERROR
			$this->assertSame( $post_permalink, $aioseop_class->aiosp_mrt_get_url( $wp_query, true ), 'All_in_One_SEO_Pack_Sitemap::aiosp_mrt_get_url() has failed. \tests\test-All_in_One_SEO_Pack_get_all_keywords.php' );

		}

	}
}
