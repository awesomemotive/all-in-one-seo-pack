<?php
/**
 * Testing All_in_One_SEO_Pack_Sitemap::get_main_description();
 *
 * Function:  Get's the AIOSEOP, post meta data, description from the post object (including taxonomies, attachments, etc)
 *            Which is also used for the General module ( aioseop_class ), OpenGraph module, and Page Snippet.
 * Expected:  Returns a (string) description depending on the $aioseop_options configuration with auto-gen, truncate,
 *            and Never Shorten Length.
 * Actual:    In some instances, the string isn't being truncated correctly.
 * Reproduce: Set the _aioseop_description (maybe include OG module class), set the options,
 *            and current screen to target post, front page, taxonomy (maybe more).
 *
 * @since 2.4.4.1
 *
 * @ticket 1395 Support longer meta descriptions
 * @ticket 1468 broken aioseop_description filter
 *
 * @group All_in_One_SEO_Pack
 * @group get_main_description
 * @group post
 * @group meta
 * @group description
 *
 */

/**
 * Namespace for organizing other tests that may relate, or may be part of a root test.
 * NOTE: The intent is to keep tests organized & isolated. This is just an alias for extra measure.
 */
namespace test\aioseop\general\get_main_description {

	use WP_UnitTestCase;
	use All_in_One_SEO_Pack;

	/**
	 * Contains the test case scenario.
	 *
	 * Extending allows adding WP's Testing Unit; which extends to PHPUnit
	 *
	 * @package WP_UnitTestCase
	 */
	class All_in_One_SEO_Pack_Test_get_main_description extends WP_UnitTestCase	{

		public $post_count = 5;
		public $post_count_excerpt = 5;
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
		}

		/**
		 * PHPUnit Fixture - setUp()
		 *
		 * Sets up the environment to test.
		 * NOTE: Patent must be called first according to WP Handbook.
		 *
		 * @since 2.4.4.1
		 *
		 * @link https://make.wordpress.org/core/handbook/testing/automated-testing/writing-phpunit-tests/#shared-setup-between-related-tests
		 */
		public function setUp() {
			parent::setUp();
			$this->setup_posts();
		}

		/**
		 * Set up Posts.
		 */
		public function setup_posts() {
			$args = array(
				'post_type'    => 'post',
				'post_title'   => 'Test Title',
				'post_content' => 'Praesent dictum maximus lectus ut varius. Nam mollis orci sollicitudin dolor laoreet finibus. Maecenas dictum elit non lorem sagittis ullamcorper. Praesent ullamcorper, elit varius condimentum accumsan, urna nisl tristique neque, ac blandit neque elit sed mauris. Donec finibus fringilla neque, a tristique massa gravida vel. Nulla aliquet felis at ipsum pharetra, id fringilla ligula euismod. Sed rutrum ante turpis, eget faucibus mi pretium id. Suspendisse potenti. Integer neque est, pulvinar vitae sollicitudin sed, faucibus vel orci. Fusce vitae lectus a purus fringilla ultricies. Duis vitae odio a turpis dapibus auctor in sit amet nisl. Proin consequat risus urna, in vulputate ex consequat a. Mauris euismod, leo varius finibus volutpat, orci mauris aliquam elit, non pretium eros arcu ac sapien. Curabitur rutrum, tortor et vestibulum laoreet, lacus ligula suscipit dui, sed hendrerit quam metus sit amet nisl. Morbi ultrices nulla nisi, vel vulputate diam pharetra quis. Mauris at tincidunt ipsum. \r Quisque dolor neque, gravida in tortor id, porttitor consectetur nulla. Nunc eleifend urna sapien, nec luctus enim tempor eu. Nullam sed augue semper, malesuada massa ut, tempor risus. Nunc semper lacus vel aliquam lobortis. Aenean tristique gravida nisl ut imperdiet. Integer tristique sed purus vel pellentesque. Quisque elementum imperdiet ultrices. Curabitur semper lobortis enim sit amet tristique. Nulla ut elit at arcu varius vehicula vel a orci. Morbi aliquam ullamcorper lorem non mattis. Donec sollicitudin lacinia tortor eget sollicitudin. Maecenas urna augue, pellentesque pulvinar justo eget, convallis venenatis dui.',
			);
			$aioseop_descriptions = array(
				'',
				'Short Description',
				'Long Description. Lorem ipsum dolor sit amet, consectetur adipiscing elit. In vel elementum lacus. Mauris facilisis pellentesque facilisis. Nam eget enim eleifend, facilisis tortor eu, vehicula urna. Pellentesque faucibus massa in felis interdum tempus. Nulla facilisi. In egestas, elit non faucibus faucibus, augue erat elementum purus, in commodo velit massa quis mauris. Vestibulum sapien arcu, pretium vitae arcu ac, elementum interdum libero. Suspendisse hendrerit arcu sed enim iaculis, id dapibus odio auctor. Praesent molestie eros id purus mattis, non efficitur purus commodo. Nam fringilla ultricies massa quis finibus. Vestibulum sed pulvinar justo, sit amet imperdiet magna.',
				'',
			);

			$ids = $this->factory->post->create_many( $this->post_count, $args );

			$total_count = 0;
			foreach ( $ids as $v1_id ) {
				$mod_index = $total_count % count( $aioseop_descriptions );
				update_post_meta( $v1_id, '_aioseop_description', $aioseop_descriptions[ $mod_index ] );
				$total_count++;
			}

			$args['post_excerpt'] = 'Nam mollis orci sollicitudin dolor laoreet finibus. Maecenas dictum elit non lorem sagittis ullamcorper. Praesent ullamcorper, elit varius condimentum accumsan, urna nisl tristique neque, ac blandit neque elit sed mauris. Donec finibus fringilla neque, a tristique massa gravida vel. Nulla aliquet felis at ipsum pharetra, id fringilla ligula euismod. Sed rutrum ante turpis, eget faucibus mi pretium id. Suspendisse potenti. Integer neque est, pulvinar vitae sollicitudin sed, faucibus vel orci. Fusce vitae lectus a purus fringilla ultricies. Duis vitae odio a turpis dapibus auctor in sit amet nisl. Proin consequat risus urna, in vulputate ex consequat a.';

			$ids = $this->factory->post->create_many( $this->post_count_excerpt, $args );

			$total_count = 0;
			foreach ( $ids as $v1_id ) {
				$mod_index = $total_count % count( $aioseop_descriptions );
				update_post_meta( $v1_id, '_aioseop_description', $aioseop_descriptions[ $mod_index ] );
				$total_count++;
			}
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
		 * Test - AIOSEOP_Sitemap::is_image_valid()
		 *
		 * Issue #1491 Class Method to test.
		 *
		 * set_current_screen
		 * @link https://codex.wordpress.org/Plugin_API/Admin_Screen_Reference
		 *
		 * @dataProvider dataProvider_get_main_description
		 *
		 * @expectedException count(): Parameter must be an array or an object that implements Countable
		 * @expectedExceptionMessage Error Exception Message in Annotations.
		 *
		 * @since 2.4.4.1
		 *
		 * @ticket 1395 Support longer meta descriptions
		 * @ticket 1468 broken aioseop_description filter
		 *
		 * @group All_in_One_SEO_Pack
		 * @group get_main_description
		 * @group post
		 * @group meta
		 * @group description
		 * @test
		 */
		public function test_get_main_description_test_length( $aioseop_options_config = array() ) {
			global $post;
			global $aioseop_options;

			// Simulate. Options first then module.
			foreach( $aioseop_options_config as $key_config => $value_config ) {
				$aioseop_options[ $key_config ] = $value_config;
			}
			$aioseop_class = new All_in_One_SEO_Pack();

			$args = array(
				'post_type' => 'post',
				'posts_per_page' => ( $this->post_count + $this->post_count_excerpt ),
			);
			$t_posts = get_posts( $args );

			foreach ( $t_posts as $t_post ) {
				// Admin - On post Edit Screen.
				//set_current_screen( 'post.php?post=' . $t_post->ID . '&action=edit' );
				// Post Screen.
				$this->go_to( get_permalink( $t_post->ID ) );

				$raw_aioseop_description = get_post_meta( $t_post->ID, '_aioseop_description');
				$post_meta_aioseop_description = $raw_aioseop_description[0];

				$test_description = $aioseop_class->get_main_description( $post );

				$count_expected = 0;
				$test_desc_length = strlen( $test_description );
				$message = 'Description Count FAILED in ' . basename( __FILE__ ) . ' --- ';
				if ( empty( $post_meta_aioseop_description ) ) {
					if ( 'on' === $aioseop_options['aiosp_generate_descriptions'] ) {
						if ( $aioseop_options['aiosp_dont_truncate_descriptions'] ) {
							$message .= '"' . $test_description . '" ( $test_description ) should be the same as "' . $t_post->post_content . '" ( Post_Content ).';
							$this->assertSame( $t_post->post_excerpt, $test_description, $message );
						} else {
							// 320 length, but incremented to avoid "Less Than Or Equal To"; simplified operation.
							$count_expected = 321;
							$message .= '$aioseop_options config: ' . print_r( $aioseop_options_config ) . '. ';
							$message .= 'Expected Desc. Length: ' . $count_expected . '. ';
							$message .= 'Actual Desc. Length: ' . $test_desc_length . '. ';
							$this->assertLessThan( $count_expected, $test_desc_length, $message  );
						}
//						if ( $aioseop_options['aiosp_skip_excerpt'] ) {
//							$this->assertEmpty( $test_description, $message  );
//						} else {
//							$this->assertEmpty( $test_description, $message  );
//						}
					} elseif ( empty( $aioseop_options['aiosp_generate_descriptions'] ) || 'off'  === $aioseop_options['aiosp_generate_descriptions'] ) {
						$message .= 'Not Empty. Actual Test Description: "' . $test_description . '"';
						if ( empty( $post->post_excerpt ) ) {
							$this->assertEmpty( $test_description, $message );
						} else {
							$this->assertSame( $post->post_excerpt, $test_description, $message );
						}
					}
				} else {
					$message .= '"' . $test_description . '" ( $test_description ) should be the same as "' . $post_meta_aioseop_description . '" ( Post meta \'_aioseop_description\' ).';
					$this->assertSame( $post_meta_aioseop_description, $test_description, $message  );
				}
			}

		}

		/**
		 * Data Provider for (test_) get_main_description
		 *
		 * Provides data for the testing function using PHPUnit's annotation.
		 *
		 * @since 2.4.4.1
		 *
		 * @return array
		 */
		public function dataProvider_get_main_description() {
			return array(
				// 1st Iteration.
				array(
					// Mocks $aioseop_options
					array(
						// Auto-Gen.
						'aiosp_generate_descriptions' => 'on',
						// Never Shorten Long Descriptions.
						'aiosp_dont_truncate_descriptions' => 0,
						// Use Content For Autogenerated Descriptions.
						'aiosp_skip_excerpt' => 0,
					),
				),
				// 2nd Iteration.
				array(
					array(
						'aiosp_generate_descriptions' => 'on',
						'aiosp_dont_truncate_descriptions' => 1,
					),
				),
				// 3nd Iteration - Empty value for Auto-Gen.
				array(
					array(
						'aiosp_generate_descriptions' => '',
						'aiosp_dont_truncate_descriptions' => 0,
					),
				),
				// 4nd Iteration - "Off" value for Auto-Gen.
				array(
					array(
						'aiosp_generate_descriptions' => 'off',
						'aiosp_dont_truncate_descriptions' => 0,
					),
				),
			);
		}

	}
}
