<?php
namespace AIOSEO\Plugin\Common\Sitemap;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AIOSEO\Plugin\Common\Models;

/**
 * Handles our sitemap search engine ping feature.
 *
 * @since 4.0.0
 */
class Ping {
	/**
	 * Registers our hooks.
	 *
	 * @since 4.0.0
	 */
	public function init() {
		if ( 0 === (int) get_option( 'blog_public' ) ) {
			return;
		}

		add_action( 'init', [ $this, 'scheduleRecurring' ] );

		// Ping sitemap on each post update.
		add_action( 'save_post', [ $this, 'schedule' ], 1000, 2 );
		add_action( 'delete_post', [ $this, 'schedule' ], 1000, 2 );

		// Action Scheduler hooks.
		add_action( 'aioseo_sitemap_ping', [ $this, 'ping' ] );
		add_action( 'aioseo_sitemap_ping_recurring', [ $this, 'ping' ] );
	}

	/**
	 * Schedules a sitemap ping.
	 *
	 * @since 4.0.0
	 *
	 * @param  integer  $postId The ID of the post.
	 * @param  \WP_Post $post   The post object.
	 * @return void
	 */
	public function schedule( $postId, $post = null ) {
		if ( ! aioseo()->helpers->isValidPost( $post ) ) {
			return;
		}

		// If Limit Modified Date is enabled, let's return early.
		$aioseoPost = Models\Post::getPost( $postId );
		if ( $aioseoPost->limit_modified_date ) {
			return;
		}

		// First, unschedule any ping actions that might already be enqueued.
		aioseo()->actionScheduler->unschedule( 'aioseo_sitemap_ping' );
		// Then, schedule the new ping.
		aioseo()->actionScheduler->scheduleSingle( 'aioseo_sitemap_ping', 30 );
	}

	/**
	 * Schedules the recurring sitemap ping.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function scheduleRecurring() {
		try {
			if ( ! as_next_scheduled_action( 'aioseo_sitemap_ping_recurring' ) ) {

				$interval = apply_filters( 'aioseo_sitemap_ping_recurring', DAY_IN_SECONDS );
				as_schedule_recurring_action( strtotime( 'tomorrow' ), $interval, 'aioseo_sitemap_ping_recurring', [], 'aioseo' );
			}
		} catch ( \Exception $e ) {
			// Do nothing.
		}
	}

	/**
	 * Pings search engines when the sitemap is updated.
	 *
	 * @since 4.0.0
	 *
	 * @param  array $sitemapUrls Sitemap URLs that should be sent to the remote endpoints.
	 * @return void
	 */
	public function ping( $sitemapUrls = [] ) {
		$endpoints = apply_filters( 'aioseo_sitemap_ping_urls', [
			'https://www.google.com/ping?sitemap='
		] );

		if ( aioseo()->options->sitemap->general->enable ) {
			$sitemapUrls[] = aioseo()->sitemap->helpers->getUrl( 'general' );
		}
		if ( aioseo()->options->sitemap->rss->enable ) {
			$sitemapUrls[] = aioseo()->sitemap->helpers->getUrl( 'rss' );
		}

		$addonsSitemapUrls = aioseo()->addons->doAddonFunction( 'ping', 'getPingUrls' );
		foreach ( $addonsSitemapUrls as $addonSitemapUrls ) {
			if ( is_array( $addonSitemapUrls ) ) {
				$sitemapUrls = array_merge( $sitemapUrls, $addonSitemapUrls );
			}
		}

		foreach ( $endpoints as $endpoint ) {
			foreach ( $sitemapUrls as $url ) {
				wp_remote_get( $endpoint . urlencode( $url ) );
			}
		}
	}
}