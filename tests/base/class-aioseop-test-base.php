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

	/*
	 * Generates the HTML source of the given link.
	 */
	protected final function get_page_source( $link ) {
		$html = '<html>';
		$this->go_to( $link );
		ob_start();
		do_action( 'wp_head' );
		$html .= '<head>' . ob_get_clean() . '</head>';

		ob_start();
		do_action( 'wp_footer' );
		$footer = ob_get_clean();
		$html .= '<body>' . /* somehow get the body too */ $footer . '</body>';
		return $html . '</html>';
	}

	/*
	 * Parses the HTML of the given link and returns the nodes requested.
	 */
	protected final function parse_html( $link, $tags = array(), $debug = false ){
		$html = $this->get_page_source( $link );
		if ( $debug ) {
			error_log( $html );
		}

		libxml_use_internal_errors(true);
		$dom = new DOMDocument();
		$dom->loadHTML( $html );

		$array = array();
		foreach ( $tags as $tag ) {
			foreach ( $dom->getElementsByTagName( $tag ) as $node ) {
				$array[] = $this->get_node_as_array($node);
			}
		}
		return $array;
	}

	/*
	 * Extracts the node from the HTML source.
	 */
	private function get_node_as_array($node) {
		$array = false;

		if ($node->hasAttributes()) {
			foreach ($node->attributes as $attr) {
				$array[$attr->nodeName] = $attr->nodeValue;
			}
		}

		if ($node->hasChildNodes()) {
			if ($node->childNodes->length == 1) {
				$array[$node->firstChild->nodeName] = $node->firstChild->nodeValue;
			} else {
				foreach ($node->childNodes as $childNode) {
					if ($childNode->nodeType != XML_TEXT_NODE) {
						$array[$childNode->nodeName][] = $this->get_node_as_array($childNode);
					}
				}
			}
		}

		return $array;
	}

	/*
	 * An empty test is required otherwise tests won't run.
	 */
	public function test_dont_remove_this_method() {
		$this->assertTrue( true );
	}

}


