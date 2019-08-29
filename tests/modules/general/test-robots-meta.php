<?php
/**
 * The test-robots-meta.php file.
 *
 * @package All_in_One_SEO_Pack
 * @since 3.3.0
 */

/**
 * Include aioseop-test-base.php.
 */
require_once AIOSEOP_UNIT_TESTING_DIR . '/base/class-aioseop-test-base.php';

/**
 * The AIOSEOP_Test_Robots_Meta class.
 *
 * Contains test cases for the robots meta tag (noindex/nofollow).
 *
 * @since 3.3.0
 */
class AIOSEOP_Test_Robots_Meta extends AIOSEOP_Test_Base {

	/**
	 * PHPUnit fixture.
	 *
	 * Sets up the test environment.
	 *
	 * @since 3.3.0
	 */
	public function setUp() {
		parent::setUp();

		wp_set_current_user( 1 );

		remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
		remove_action( 'wp_print_styles', 'print_emoji_styles' );
	}

	/**
	 * The test_robots_meta_sitewide_noindex() function.
	 *
	 * Tests whether our code does not run when the site is set to noindex under Settings > Reading.
	 * TODO add cat & ctax.
	 *
	 * @group robots_meta
	 *
	 * @since 3.3.0
	 */
	public function test_robots_meta_sitewide_noindex() {
		// Arrange.
		global $aioseop_options;

		add_action( 'init', 'register_cpt_movie' );
		// add_action( 'init', 'register_ctax_movie_genre' );

		$cpt_nofollow = array( 'post', 'movie' );
		// $ctax_nofollow = array('category', 'genre');

		$aioseop_options['aiosp_cpostnofollow'] = $cpt_nofollow;
		// $aioseop_options['aiosp_ctaxnofollow'] = $cpt_nofollow;

		update_option( 'blog_public', false );
		update_option( 'aioseop_options', $aioseop_options );

		$ids = array(
			$this->create_new_post( 'post' ),
			$this->create_new_post( 'movie' ),
			// create cat.
			// create ctax.
		);

		// Act.
		$robots_meta_tags = $this->get_robots_meta_tags( $ids );

		// Assert.
		$this->assert_robots_meta_results( $ids, $robots_meta_tags, 'noindex,follow' );
	}

	/**
	 * The test_robots_meta_filter() function.
	 *
	 * Tests whether the aioseop_robots_meta filter works.
	 *
	 * @group robots_meta
	 *
	 * @since 3.3.0
	 */
	public function test_aioseop_robots_meta_filter() {
		// Arrange.
		global $aioseop_options;

		$cpt_nofollow = array( 'post' );
		$aioseop_options['aiosp_cpostnofollow'] = $cpt_nofollow;
		update_option( 'aioseop_options', $aioseop_options );

		$ids = array(
			$this->create_new_post( 'post' ),
		);

		// Act.
		add_filter( 'aioseop_robots_meta', array( $this, 'filter_change_robots_meta' ) );

		$robots_meta_tags = $this->get_robots_meta_tags( $ids );

		// Assert.
		$this->assert_robots_meta_results( $ids, $robots_meta_tags, $this->filter_change_robots_meta() );
	}

	/**
	 * The test_robots_meta_individual() function.
	 *
	 * Tests whether noindexing/nofollowing posts and tax terms via the meta box works.
	 * TODO add cat & ctax.
	 * TODO add support for nofollow.
	 *
	 * @group robots_meta
	 *
	 * @since 3.3.0
	 */
	public function test_robots_meta_individual() {

		// Arrange.
		global $aioseop_options;

		$ids = array(
			$this->create_new_post( 'post' ),
			$this->create_new_post( 'movie' ),
			// create cat.
			// create tax.
		);

		$post_meta_values = array();
		foreach ( $ids as $id ) {
			$values = array();
			array_push( $values, get_random_meta_value(), get_random_meta_value() );
			array_push( $post_meta_values, $values );
		}

		for ( $i = 0; $i < count( $ids ); $i++ ) {
			add_post_meta( $id, '_aioseop_noindex', $post_meta_values[ $i ][0], true );
			add_post_meta( $id, '_aioseop_nofollow', $post_meta_values[ $i ][1], true );
		}

		function get_random_meta_value() {
			$random_value = rand( 0, 1 );
			switch ( $random_value ) {
				case 0: {
					return '';
				}
				case 1: {
					return 'on';
				}
			}
		}

		// Act.
		$robots_meta_tags = $this->get_robots_meta_tags( $ids );

		// Assert.
		$this->assert_robots_meta_results( $ids, $robots_meta_tags, 'noindex,follow' );

		// Assert.
		// assert if tag values are the same as expected result

		for ( $i = 0; $i < count( $ids ); $i++ ) {

		}

	}

	public function test_robots_meta_global() {

		// feed via data provider
		// noindex or nofollow something

		// act
		// assert
	}

	private function get_robots_meta_tags( $ids ) {
		$post_meta_tags = array();
		$robots_meta_tags = array();

		foreach ( $ids as $id ) {
			$link = get_permalink( $id );
			array_push( $post_meta_tags, $this->parse_html( $link, array( 'meta' ) ) );
		}

		foreach ( $post_meta_tags as $post => $tags ) {
			foreach ( $tags as $tag => $val ) {
				if ( $val['name'] === 'robots' ) {
					array_push( $robots_meta_tags, $val['content'] );
				}
			}
		}
		return $robots_meta_tags;
	}

	private function assert_robots_meta_results( $ids, $robots_meta_tags, $expected_result ) {
		$this->assertTrue( count( $robots_meta_tags ) === count( $ids ) );

		foreach ( $robots_meta_tags as $val ) {
			$this->assertTrue( $expected_result == $val );
		}
	}

	private function create_new_post( $post_type ) {
		return $this->factory->post->create(
			array(
				'post_type'    => $post_type,
			)
		);
	}

	private function register_cpt_movie() {
		$args = array(
			'public' => true,
			'label'  => 'Movies',
		);
		register_post_type( 'movie', $args );
	}

	private function register_ctax_movie_genre() {
		register_taxonomy(
			'genre',
			'movie'
		);
	}

	/**
	 * The filter_change_robots_meta() function.
	 *
	 * Acts as the callback function for test_aioseop_robots_meta_filter().
	 *
	 * @since 3.0.0
	 *
	 * @return string
	 */
	function filter_change_robots_meta() {
		return 'noindex,follow';
	}
}
