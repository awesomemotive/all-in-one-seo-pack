<?php
namespace AIOSEO\Plugin\Common\SearchStatistics;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AIOSEO\Plugin\Common\Models;

/**
 * Handles the notices for the Search Statistics.
 *
 * @since 4.6.2
 */
class Notices {
	/**
	 * Class constructor.
	 *
	 * @since 4.6.2
	 */
	public function __construct() {
		if ( ! is_admin() ) {
			return;
		}

		add_action( 'init', [ $this, 'init' ] );
	}

	/**
	 * Initialize the class.
	 *
	 * @since 4.6.2
	 *
	 * @return void
	 */
	public function init() {
		$this->siteConnected();
		$this->siteVerified();
		$this->sitemapHasErrors();
	}

	/**
	 * Add a notice if the site is not connected.
	 *
	 * @since 4.6.2
	 *
	 * @return void
	 */
	private function siteConnected() {
		$notification = Models\Notification::getNotificationByName( 'search-console-site-not-connected' );
		if ( aioseo()->searchStatistics->api->auth->isConnected() ) {
			if ( $notification->exists() ) {
				Models\Notification::deleteNotificationByName( 'search-console-site-not-connected' );
			}

			return;
		}

		if ( $notification->exists() ) {
			return;
		}

		Models\Notification::addNotification( [
			'slug'              => uniqid(),
			'notification_name' => 'search-console-site-not-connected',
			'title'             => __( 'Have you connected your site to Google Search Console?', 'all-in-one-seo-pack' ),
			'content'           => sprintf(
				// Translators: 1 - The plugin short name ("AIOSEO").
				__( '%1$s can now verify whether your site is correctly verified with Google Search Console and that your sitemaps have been submitted correctly. Connect with Google Search Console now to ensure your content is being added to Google as soon as possible for increased rankings.', 'all-in-one-seo-pack' ), // phpcs:ignore Generic.Files.LineLength.MaxExceeded
				AIOSEO_PLUGIN_SHORT_NAME
			),
			'type'              => 'warning',
			'level'             => [ 'all' ],
			'button1_label'     => __( 'Connect to Google Search Console', 'all-in-one-seo-pack' ),
			'button1_action'    => 'https://route#aioseo-settings&aioseo-scroll=google-search-console-settings&aioseo-highlight=google-search-console-settings:webmaster-tools?activetool=googleSearchConsole', // phpcs:ignore Generic.Files.LineLength.MaxExceeded
			'start'             => gmdate( 'Y-m-d H:i:s' )
		] );
	}

	/**
	 * Add a notice if the site is not verified or was deleted.
	 *
	 * @since 4.6.2
	 *
	 * @return void
	 */
	private function siteVerified() {
		$notification = Models\Notification::getNotificationByName( 'search-console-site-not-verified' );
		if (
			! aioseo()->searchStatistics->api->auth->isConnected() ||
			aioseo()->internalOptions->searchStatistics->site->verified ||
			0 === aioseo()->internalOptions->searchStatistics->site->lastFetch // Not fetched yet.
		) {
			if ( $notification->exists() ) {
				Models\Notification::deleteNotificationByName( 'search-console-site-not-verified' );
			}

			return;
		}

		if ( $notification->exists() ) {
			return;
		}

		Models\Notification::addNotification( [
			'slug'              => uniqid(),
			'notification_name' => 'search-console-site-not-verified',
			'title'             => __( 'Your site was removed from Google Search Console.', 'all-in-one-seo-pack' ),
			'content'           => __( 'We detected that your site has been removed from Google Search Console. If this was done in error, click below to re-sync and resolve this issue.', 'all-in-one-seo-pack' ), // phpcs:ignore Generic.Files.LineLength.MaxExceeded
			'type'              => 'warning',
			'level'             => [ 'all' ],
			'button1_label'     => __( 'Reconnect Google Search Console', 'all-in-one-seo-pack' ),
			'button1_action'    => 'https://route#aioseo-settings&aioseo-scroll=google-search-console-settings&aioseo-highlight=google-search-console-settings:webmaster-tools?activetool=googleSearchConsole', // phpcs:ignore Generic.Files.LineLength.MaxExceeded
			'start'             => gmdate( 'Y-m-d H:i:s' )
		] );
	}

	/**
	 * Add a notice if the sitemap has errors.
	 *
	 * @since 4.6.2
	 *
	 * @return void
	 */
	private function sitemapHasErrors() {
		$notification = Models\Notification::getNotificationByName( 'search-console-sitemap-has-errors' );
		if (
			! aioseo()->searchStatistics->api->auth->isConnected() ||
			! aioseo()->internalOptions->searchStatistics->site->verified ||
			0 === aioseo()->internalOptions->searchStatistics->sitemap->lastFetch || // Not fetched yet.
			! aioseo()->searchStatistics->sitemap->getSitemapsWithErrors()
		) {
			if ( $notification->exists() ) {
				Models\Notification::deleteNotificationByName( 'search-console-sitemap-has-errors' );
			}

			return;
		}

		if ( $notification->exists() ) {
			return;
		}

		$lastFetch = aioseo()->internalOptions->searchStatistics->sitemap->lastFetch;
		$lastFetch = date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $lastFetch );

		Models\Notification::addNotification( [
			'slug'              => uniqid(),
			'notification_name' => 'search-console-sitemap-has-errors',
			'title'             => __( 'Your sitemap has errors.', 'all-in-one-seo-pack' ),
			'content'           => sprintf(
				// Translators: 1 - Last fetch date.
				__( 'We detected that your sitemap has errors. The last fetch was on %1$s. Click below to resolve this issue.', 'all-in-one-seo-pack' ), // phpcs:ignore Generic.Files.LineLength.MaxExceeded
				$lastFetch
			),
			'type'              => 'warning',
			'level'             => [ 'all' ],
			'button1_label'     => __( 'Fix Sitemap Errors', 'all-in-one-seo-pack' ),
			'button1_action'    => 'https://route#aioseo-sitemaps&open-modal=true:general-sitemap', // phpcs:ignore Generic.Files.LineLength.MaxExceeded
			'start'             => gmdate( 'Y-m-d H:i:s' )
		] );
	}
}