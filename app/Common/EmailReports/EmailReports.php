<?php

namespace AIOSEO\Plugin\Common\EmailReports;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AIOSEO\Plugin\Common\Models;

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

		add_action( 'aioseo_email_reports_enable_reminder', [ $this, 'enableReminder' ] );
	}

	/**
	 * Enable reminder.
	 *
	 * @since 4.7.7
	 *
	 * @return void
	 */
	public function enableReminder() {
		// User already enabled email reports.
		if ( aioseo()->options->advanced->emailSummary->enable ) {
			return;
		}

		// Check if notification exists.
		$notification = Models\Notification::getNotificationByName( 'email-reports-enable-reminder' );
		if ( $notification->exists() ) {
			return;
		}

		// Add notification.
		Models\Notification::addNotification( [
			'slug'              => uniqid(),
			'notification_name' => 'email-reports-enable-reminder',
			'title'             => __( 'Email Reports', 'all-in-one-seo-pack' ),
			'content'           => __( 'Stay ahead in SEO with our new email digest! Get the latest tips, trends, and tools delivered right to your inbox, helping you optimize smarter and faster. Enable it today and never miss an update that can take your rankings to the next level.', 'all-in-one-seo-pack' ), // phpcs:ignore Generic.Files.LineLength.MaxExceeded
			'type'              => 'info',
			'level'             => [ 'all' ],
			'button1_label'     => __( 'Enable Email Reports', 'all-in-one-seo-pack' ),
			'button1_action'    => 'https://route#aioseo-settings&aioseo-scroll=aioseo-email-summary-row&aioseo-highlight=aioseo-email-summary-row:advanced',
			'button2_label'     => __( 'All Good, I\'m already getting it', 'all-in-one-seo-pack' ),
			'button2_action'    => 'http://action#notification/email-reports-enable',
			'start'             => gmdate( 'Y-m-d H:i:s' )
		] );
	}
}