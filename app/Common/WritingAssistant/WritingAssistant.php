<?php
namespace AIOSEO\Plugin\Common\WritingAssistant;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Main class.
 *
 * @since 4.7.4
 */
class WritingAssistant {
	/**
	 * Helpers.
	 *
	 * @since 4.7.4
	 *
	 * @var Utils\Helpers
	 */
	public $helpers;

	/**
	 * SeoBoost.
	 *
	 * @since 4.7.4
	 *
	 * @var SeoBoost\SeoBoost
	 */
	public $seoBoost;

	/**
	 * Load our classes.
	 *
	 * @since 4.7.4
	 *
	 * @return void
	 */
	public function __construct() {
		$this->helpers  = new Utils\Helpers();
		$this->seoBoost = new SeoBoost\SeoBoost();
	}
}