<?php
namespace AIOSEO\Plugin\Common\ThirdParty;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Instantiates our third-party classes.
 *
 * @since 4.7.6
 */
class ThirdParty {
	/**
	 * WebStories instance.
	 *
	 * @since 4.7.6
	 *
	 * @var WebStories
	 */
	public $webStories;

	/**
	 * Class constructor.
	 *
	 * @since 4.7.6
	 */
	public function __construct() {
		$this->webStories = new WebStories();
	}
}