<?php
/**
 * AIOSEOP testing base class.
 */
class AIOSEOP_Test_Base extends WP_UnitTestCase {

	/**
	 * Upload an image and, optionally, attach to the post.
	 */
	protected final function upload_image_and_maybe_attach( $image, $id = 0 ) {
		/*
		 This factory method has a bug so we have to be a little clever.
		$this->factory->attachment->create( array( 'file' => $image, 'post_parent' => $id ) );
		*/
		$attachment_id = $this->factory->attachment->create_upload_object( $image, $id );
		if ( 0 !== $id ) {
			update_post_meta( $id, '_thumbnail_id', $attachment_id );
		}
		return $attachment_id;
	}

	/**
	 * Create attachments, and, optionally, attach to a post.
	 */
	protected final function create_attachments( $num, $id = 0 ) {
		$image = str_replace( '\\', '/', AIOSEOP_UNIT_TESTING_DIR . '/resources/images/footer-logo.png' );
		$ids = array();
		for ( $x = 0; $x < $num; $x++ ) {
			$ids[] = $this->factory->attachment->create_upload_object( $image, $id );
		}
		return $ids;
	}

	protected final function init() {
		$this->clean();
	}

	/**
	 * Clean up the flotsam and jetsam before starting.
	 */
	protected final function clean() {
		$posts  = get_posts(
			array(
				'post_type' => 'any',
				'fields'    => 'ids',
				'numberposts' => -1,
			)
		);

		foreach ( $posts as $post ) {
			wp_delete_post( $post, true );
		}
	}

	/**
	 * Set up the options for the particular module.
	 */
	protected final function _setup_options( $module, $custom_options ) {
		// so that is_admin returns true.
		set_current_screen( 'edit-post' );
		wp_set_current_user( 1 );

		global $aioseop_options;

		// activate the sitemap module.
		$aioseop_options['modules'] = array(
			'aiosp_feature_manager_options' => array(
				"aiosp_feature_manager_enable_$module"  => 'on',
			),
		);
		update_option( 'aioseop_options', $aioseop_options );

		set_current_screen( 'edit-post' );

		$nonce      = wp_create_nonce( 'aioseop-nonce' );
		$class      = 'All_in_One_SEO_Pack_' . ucwords( $module );
		$_POST      = array(
			'action'                => 'aiosp_update_module',
			'Submit_All_Default'    => 'blah',
			'Submit'                => 'blah',
			'nonce-aioseop'         => $nonce,
			'settings'              => ' ',
			'options'               => "aiosp_feature_manager_enable_{$module}=true&page=" . trailingslashit( AIOSEOP_PLUGIN_DIRNAME ) . "modules/aioseop_feature_manager.php&Submit=testing!&module={$class}&nonce-aioseop=" . $nonce,
		);

		// so that is_admin returns true.
		set_current_screen( 'edit-post' );

		// this action will also try to regenerate the sitemap, but we will not let that bother us.
		do_action( 'wp_ajax_aioseop_ajax_save_settings' );

		$this->go_to( admin_url( 'admin.php?page=all-in-one-seo-pack%2Fmodules%2Faioseop_sitemap.php' ) );
		do_action( 'admin_menu' );

		$aioseop_options = get_option( 'aioseop_options' );

		$module_options = $aioseop_options['modules'][ "aiosp_{$module}_options" ];
		$module_options = array_merge( $module_options, $custom_options );

		$aioseop_options['modules'][ "aiosp_{$module}_options" ] = $module_options;

		update_option( 'aioseop_options', $aioseop_options );

		$aioseop_options = get_option( 'aioseop_options' );
		// error_log("aioseop_options " . print_r($aioseop_options,true));
	}

	/**
	 * Set up posts of specific post type, without/without images. Use this when post attributes such as title, content etc. don't matter.
	 */
	protected final function setup_posts( $without_images = 0, $with_images = 0, $type = 'post' ) {
		if ( $without_images > 0 ) {
			$this->factory->post->create_many( $without_images, array( 'post_type' => $type, 'post_content' => 'content without image', 'post_title' => 'title without image' ) );
		}
		if ( $with_images > 0 ) {
			$ids    = $this->factory->post->create_many( $with_images, array( 'post_type' => $type, 'post_content' => 'content with image', 'post_title' => 'title with image' ) );
			foreach ( $ids as $id ) {
				$this->upload_image_and_maybe_attach( str_replace( '\\', '/', AIOSEOP_UNIT_TESTING_DIR . '/resources/images/footer-logo.png' ), $id );
			}
		}

		$posts  = get_posts(
			array(
				'post_type' => $type,
				'fields'    => 'ids',
				'numberposts' => -1,
			)
		);

		// 4 posts created?
		$this->assertEquals( $without_images + $with_images, count( $posts ) );

		foreach ( $posts as $id ) {
			get_permalink( $id );
		}

		$attachments    = get_posts(
			array(
				'post_type' => 'attachment',
				'fields'    => 'ids',
				'numberposts' => -1,
			)
		);

		// 2 attachments created?
		$this->assertEquals( $with_images, count( $attachments ) );

		$with = array();
		$without = array();

		$featured   = 0;
		foreach ( $posts as $id ) {
			if ( has_post_thumbnail( $id ) ) {
				$featured++;
				$with[] = get_permalink( $id );
				continue;
			}
			$without[] = get_permalink( $id );
		}

		// 2 posts have featured image?
		$this->assertEquals( $with_images, $featured );

		return array(
			'with'  => $with,
			'without'   => $without,
		);
	}

	/**
	 * (Barebone) Setup Posts and return IDs
	 *
	 * @param int   $amount
	 * @param array $args
	 * @return array {
	 *     @type int $post_ID ID of the post inserted.
	 * }
	 */
	protected function setup_posts_return_IDs( $amount, $args = array() ) {
		if ( 1 > $amount ) {
			return array();
		}
		$default_args = array(
			'post_type'    => 'post',
			'post_title'   => 'Post Title',
			'post_content' => 'Post Content - Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
		);

		$args = wp_parse_args( $args, $default_args );

		$ids = $this->factory->post->create_many( $amount, $args );

		$posts = get_posts(
			array(
				'post_type'   => $args['post_type'],
				'fields'      => 'OBJECT',
				'numberposts' => -1,
			)
		);

		$this->assertEquals( $amount, count( $posts ) );

		foreach ( $posts as $v1_post ) {

			foreach ( $args as $k2_args_index => $v2_args ) {
				$this->assertNotNull( $v1_post->$k2_args_index );
				if ( isset( $v1_post->$k2_args_index ) ) {
					$this->assertSame( $v2_args, $v1_post->$k2_args_index );
				}
			}
		}

		return $ids;
	}

	/*
	 * An empty test is required otherwise tests won't run.
	 */
	public function test_dont_remove_this_method() {
		$this->assertTrue( true );
	}

}


