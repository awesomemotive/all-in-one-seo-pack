<?php
namespace AIOSEO\Plugin\Common\Meta;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AIOSEO\Plugin\Common\Models;

/**
 * Instantiates the Meta classes.
 *
 * @since 4.0.0
 */
class Meta {
	/**
	 * MetaData class instance.
	 *
	 * @since 4.2.7
	 *
	 * @var MetaData
	 */
	public $metaData = null;

	/**
	 * Title class instance.
	 *
	 * @since 4.2.7
	 *
	 * @var Title
	 */
	public $title = null;

	/**
	 * Description class instance.
	 *
	 * @since 4.2.7
	 *
	 * @var Description
	 */
	public $description = null;

	/**
	 * Keywords class instance.
	 *
	 * @since 4.2.7
	 *
	 * @var Keywords
	 */
	public $keywords = null;

	/**
	 * Robots class instance.
	 *
	 * @since 4.2.7
	 *
	 * @var Robots
	 */
	public $robots = null;

	/**
	 * Class constructor.
	 *
	 * @since 4.0.0
	 */
	public function __construct() {
		$this->metaData     = new MetaData();
		$this->title        = new Title();
		$this->description  = new Description();
		$this->keywords     = new Keywords();
		$this->robots       = new Robots();

		new Amp();
		new Links();

		add_action( 'delete_post', [ $this, 'deletePostMeta' ], 1000 );
	}

	/**
	 * When we delete the meta, we want to delete our post model.
	 *
	 * @since 4.0.1
	 *
	 * @param  integer $postId The ID of the post.
	 * @return void
	 */
	public function deletePostMeta( $postId ) {
		$aioseoPost = Models\Post::getPost( $postId );
		if ( $aioseoPost->exists() ) {
			$aioseoPost->delete();
		}
	}
}