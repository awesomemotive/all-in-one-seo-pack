<?php

namespace AIOSEO\Plugin\Common\EmailReports;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * EmailReports class.
 *
 * @since 4.7.2
 */
class EmailReports {
	/**
	 * Mail object.
	 *
	 * @since 4.7.2
	 *
	 * @var Mail
	 */
	public $mail = null;

	/**
	 * Summary object.
	 *
	 * @since 4.7.2
	 *
	 * @var Summary\Summary
	 */
	public $summary;

	/**
	 * Class constructor.
	 *
	 * @since 4.7.2
	 */
	public function __construct() {
		$this->mail    = new Mail();
		$this->summary = new Summary\Summary();
	}
}