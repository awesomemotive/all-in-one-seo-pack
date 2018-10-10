<?php
/**
 * AIOSEOP testing base class.
 */
class AIOSEOP_Test_Base extends WP_UnitTestCase {

	public function _setUp() {
		parent::setUp();
 		// avoids error - readfile(/src/wp-includes/js/wp-emoji-loader.js): failed to open stream: No such file or directory
		remove_action('wp_head', 'print_emoji_detection_script', 7);
		
		// reset global options.
		delete_option( 'aioseop_options' );
		aioseop_initialize_options();
	}

	/**
	 * Last AJAX response.  This is set via echo -or- wp_die.
	 * @var type
	 */
	protected $_last_response = '';
	/**
	 * List of ajax actions called via POST
	 * @var type
	 */
	protected $_core_actions_get = array( 'fetch-list', 'ajax-tag-search', 'wp-compression-test', 'imgedit-preview', 'oembed_cache' );
	/**
	 * Saved error reporting level
	 * @var int
	 */
	protected $_error_level = 0;
	/**
	 * List of ajax actions called via GET
	 * @var type
	 */
	protected $_core_actions_post = array(
		'oembed_cache', 'image-editor', 'delete-comment', 'delete-tag', 'delete-link',
		'delete-meta', 'delete-post', 'trash-post', 'untrash-post', 'delete-page', 'dim-comment',
		'add-link-category', 'add-tag', 'get-tagcloud', 'get-comments', 'replyto-comment',
		'edit-comment', 'add-menu-item', 'add-meta', 'add-user', 'autosave', 'closed-postboxes',
		'hidden-columns', 'update-welcome-panel', 'menu-get-metabox', 'wp-link-ajax',
		'menu-locations-save', 'menu-quick-search', 'meta-box-order', 'get-permalink',
		'sample-permalink', 'inline-save', 'inline-save-tax', 'find_posts', 'widgets-order',
		'save-widget', 'set-post-thumbnail', 'date_format', 'time_format', 'wp-fullscreen-save-post',
		'wp-remove-post-lock', 'dismiss-wp-pointer', 'nopriv_autosave',
	);

	public function ajaxSetUp() {
		parent::setUp();
		// Register the core actions
		foreach ( array_merge( $this->_core_actions_get, $this->_core_actions_post ) as $action ) {
			if ( function_exists( 'wp_ajax_' . str_replace( '-', '_', $action ) ) ) {
				add_action( 'wp_ajax_' . $action, 'wp_ajax_' . str_replace( '-', '_', $action ), 1 );
			}
		}
		add_filter( 'wp_die_ajax_handler', array( $this, 'getDieHandler' ), 1, 1 );
		if ( ! defined( 'DOING_AJAX' ) ) {
			define( 'DOING_AJAX', true );
		}
		set_current_screen( 'ajax' );
		// Clear logout cookies
		add_action( 'clear_auth_cookie', array( $this, 'logout' ) );
		// Suppress warnings from "Cannot modify header information - headers already sent by"
		$this->_error_level = error_reporting();
		error_reporting( $this->_error_level & ~E_WARNING );
	}

	/**
	 * Tear down the test fixture.
	 * Reset $_POST, remove the wp_die() override, restore error reporting
	 */
	public function ajaxTearDown() {
		parent::tearDown();
		$_POST = array();
		$_GET = array();
		unset( $GLOBALS['post'] );
		unset( $GLOBALS['comment'] );
		remove_filter( 'wp_die_ajax_handler', array( $this, 'getDieHandler' ), 1, 1 );
		remove_action( 'clear_auth_cookie', array( $this, 'logout' ) );
		error_reporting( $this->_error_level );
		set_current_screen( 'front' );
	}

	/**
	 * Return our callback handler
	 * @return callback
	 */
	public function getDieHandler() {
		return array( $this, 'dieHandler' );
	}


	/**
	 * Handler for wp_die()
	 * Save the output for analysis, stop execution by throwing an exception.
	 * Error conditions (no output, just die) will throw <code>WPAjaxDieStopException( $message )</code>
	 * You can test for this with:
	 * <code>
	 * $this->setExpectedException( 'WPAjaxDieStopException', 'something contained in $message' );
	 * </code>
	 * Normal program termination (wp_die called at then end of output) will throw <code>WPAjaxDieContinueException( $message )</code>
	 * You can test for this with:
	 * <code>
	 * $this->setExpectedException( 'WPAjaxDieContinueException', 'something contained in $message' );
	 * </code>
	 * @param string $message
	 */
	public function dieHandler( $message ) {
		$this->_last_response .= ob_get_clean();
		ob_end_clean();
		if ( '' === $this->_last_response ) {
			if ( is_scalar( $message ) ) {
				throw new WPAjaxDieStopException( (string) $message );
			} else {
				throw new WPAjaxDieStopException( '0' );
			}
		} else {
			throw new WPAjaxDieContinueException( $message );
		}
	}

	/**
	 * Mimic the ajax handling of admin-ajax.php
	 * Capture the output via output buffering, and if there is any, store
	 * it in $this->_last_message.
	 * @param string $action
	 */
	protected function _handleAjax( $action ) {
		// Start output buffering
		ini_set( 'implicit_flush', false );
		ob_start();
		// Build the request
		$_POST['action'] = $action;
		$_GET['action']  = $action;
		$_REQUEST        = array_merge( $_POST, $_GET );
		// Call the hooks
		// do_action( 'admin_init' );
		do_action( 'wp_ajax_' . $_REQUEST['action'], null );
		// Save the output
		$buffer = ob_get_clean();
		if ( ! empty( $buffer ) ) {
			$this->_last_response = $buffer;
		}
	}

	/**
	 * Switch between user roles
	 * E.g. administrator, editor, author, contributor, subscriber
	 * @param string $role
	 */
	protected function _setRole( $role ) {
		$post = $_POST;
		$user_id = $this->factory->user->create( array( 'role' => $role ) );
		wp_set_current_user( $user_id );
		$_POST = array_merge( $_POST, $post );
	}

	/**
	 * Upload an image and, optionally, attach to the post.
	*/
	protected final function upload_image_and_maybe_attach( $image, $id = 0 ) {
		/* this factory method has a bug so we have to be a little clever.
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
		for( $x = 0; $x < $num; $x++ ) {
			$ids[] = $this->factory->attachment->create_upload_object( $image, $id );
		}
		return $ids;
	}

	protected final function init( $call_setup = false ) {
		$this->clean();
		if ( $call_setup ) {
			$this->_setUp();
		}
	}

	/**
	 * Clean up the flotsam and jetsam before starting.
	*/
	protected final function clean() {
		$posts	= get_posts(
			array(
				'post_type' => 'any',
				'fields'	=> 'ids',
				'numberposts' => -1,
		) );

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
			'aiosp_feature_manager_options'	=> array(
				"aiosp_feature_manager_enable_$module"	=> 'on'
			),
		);
		update_option( 'aioseop_options', $aioseop_options );

		set_current_screen( 'edit-post' );

		$nonce		= wp_create_nonce( 'aioseop-nonce' );
		$class		= 'All_in_One_SEO_Pack_' . ucwords( $module );
		$_POST		= array(
			'action'				=> 'aiosp_update_module',
			'Submit_All_Default'	=> 'blah',
			'Submit'				=> 'blah',
			'nonce-aioseop'			=> $nonce,
			'settings'				=> ' ',
			'options'				=> "aiosp_feature_manager_enable_{$module}=true&page=" . trailingslashit( AIOSEOP_PLUGIN_DIRNAME ) . "modules/aioseop_feature_manager.php&Submit=testing!&module={$class}&nonce-aioseop=" . $nonce,
		);

		// so that is_admin returns true.
		set_current_screen( 'edit-post' );

		// this action will also try to regenerate the sitemap, but we will not let that bother us.
		do_action( 'wp_ajax_aioseop_ajax_save_settings' );

		$this->go_to( admin_url( 'admin.php?page=all-in-one-seo-pack%2Fmodules%2Faioseop_' . $module . '.php' ) );
		do_action( 'admin_menu' );

		$aioseop_options = get_option( 'aioseop_options' );

		$module_options = $aioseop_options['modules']["aiosp_{$module}_options"];
		$module_options = array_merge( $module_options, $custom_options );

		$aioseop_options['modules']["aiosp_{$module}_options"] = $module_options;

		update_option( 'aioseop_options', $aioseop_options );

		$aioseop_options = get_option( 'aioseop_options' );
		//error_log("aioseop_options " . print_r($aioseop_options,true));
	}

	/**
	 * Set up posts of specific post type, without/without images. Use this when post attributes such as title, content etc. don't matter.
	*/
	protected final function setup_posts( $without_images = 0, $with_images = 0, $type = 'post', $image_name = 'footer-logo.png' ) {
		if ( $without_images > 0 ) {
			$this->factory->post->create_many( $without_images, array( 'post_type' => $type, 'post_content' => 'content without image', 'post_title' => 'title without image' ) );
		}
		if ( $with_images > 0 ) {
			$ids	= $this->factory->post->create_many( $with_images, array( 'post_type' => $type, 'post_content' => 'content with image', 'post_title' => 'title with image' ) );
			foreach ( $ids as $id ) {
				$this->upload_image_and_maybe_attach( str_replace( '\\', '/', AIOSEOP_UNIT_TESTING_DIR . "/resources/images/$image_name" ), $id );
			}
		}

		$posts	= get_posts(
			array(
				'post_type' => $type,
				'fields'	=> 'ids',
				'numberposts' => -1,
		) );

		// 4 posts created?
		$this->assertEquals( $without_images + $with_images, count( $posts ) );

		foreach ( $posts as $id ) {
			get_permalink( $id );
		}

		$attachments	= get_posts(
			array(
				'post_type' => 'attachment',
				'fields'	=> 'ids',
				'numberposts' => -1,
		) );

		// 2 attachments created?
		$this->assertEquals( $with_images, count( $attachments ) );

		$with = array();
		$without = array();
		$with_ids = array();
		$without_ids = array();

		$featured	= 0;
		foreach ( $posts as $id ) {
			if ( has_post_thumbnail( $id ) ) {
				$featured++;
				$with[] = get_permalink( $id );
				$with_ids[] = $id;
				continue;
			}
			$without[] = get_permalink( $id );
			$without_ids[] = $id;
		}

		// 2 posts have featured image?
		$this->assertEquals( $with_images, $featured );

		return array(
			'with'	=> $with,
			'without'	=> $without,
			'ids'	=> array(
				'with'	=> $with_ids,
				'without'	=> $without_ids,
			),
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
	protected final function parse_html( $link, $tags = array(), $debug = false ) {
		$html = $this->get_page_source( $link );
		if ( $debug ) {
			error_log( $html );
		}

		libxml_use_internal_errors( true );
		$dom = new DOMDocument();
		$dom->loadHTML( $html );

		$array = array();
		foreach ( $tags as $tag ) {
			foreach ( $dom->getElementsByTagName( $tag ) as $node ) {
				$array[] = $this->get_node_as_array( $node );
			}
		}
		return $array;
	}

	/*
	 * Extracts the node from the HTML source.
	 */
	private function get_node_as_array( $node ) {
		$array = false;

		if ( $node->hasAttributes() ) {
			foreach ( $node->attributes as $attr ) {
				$array[ $attr->nodeName ] = $attr->nodeValue;
			}
		}

		if ( $node->hasChildNodes() ) {
			if ( $node->childNodes->length == 1 ) {
				$array[ $node->firstChild->nodeName ] = $node->firstChild->nodeValue;
			} else {
				foreach ( $node->childNodes as $childNode ) {
					if ( $childNode->nodeType != XML_TEXT_NODE ) {
						$array[ $childNode->nodeName ][] = $this->get_node_as_array( $childNode );
					}
				}
			}
		}

		return $array;
	}

	/*
	 * An empty test is required otherwise tests won't run.
	 */
	public function test_dont_remove_this_method(){
		$this->assertTrue(true);
	}

}

