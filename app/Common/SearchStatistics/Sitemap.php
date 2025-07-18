<?php
namespace AIOSEO\Plugin\Common\SearchStatistics;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles the sitemaps for the search statistics.
 *
 * @since 4.6.2
 */
class Sitemap {
	/**
	 * The action name.
	 *
	 * @since 4.6.2
	 *
	 * @var string
	 */
	public $action = 'aioseo_search_statistics_sitemap_sync';

	/**
	 * Class constructor.
	 *
	 * @since 4.6.2
	 */
	public function __construct() {
		add_action( 'admin_init', [ $this, 'init' ] );
		add_action( $this->action, [ $this, 'worker' ] );
	}

	/**
	 * Initialize the class.
	 *
	 * @since 4.6.2
	 *
	 * @return void
	 */
	public function init() {
		if (
			! aioseo()->searchStatistics->api->auth->isConnected() ||
			! aioseo()->internalOptions->searchStatistics->site->verified ||
			aioseo()->actionScheduler->isScheduled( $this->action )
		) {
			return;
		}

		aioseo()->actionScheduler->scheduleAsync( $this->action );
	}

	/**
	 * Sync the sitemap.
	 *
	 * @since 4.6.3
	 *
	 * @return void
	 */
	public function worker() {
		if ( ! $this->canSync() ) {
			return;
		}

		$api      = new Api\Request( 'google-search-console/sitemap/sync/', [ 'sitemaps' => aioseo()->sitemap->helpers->getSitemapUrls() ] );
		$response = $api->request();

		if ( ! is_wp_error( $response ) && ! empty( $response['data'] ) ) {
			aioseo()->internalOptions->searchStatistics->sitemap->list      = $response['data'];
			aioseo()->internalOptions->searchStatistics->sitemap->lastFetch = time();
		}

		// Schedule a new sync for the next week.
		aioseo()->actionScheduler->scheduleSingle( $this->action, WEEK_IN_SECONDS + wp_rand( 0, 3 * DAY_IN_SECONDS ), [], true );
	}

	/**
	 * Maybe sync the sitemap after updating the options.
	 * It will check whether the sitemap options have changed and sync the sitemap if needed.
	 *
	 * @since 4.6.2
	 *
	 * @param array $oldSitemapOptions The old sitemap options.
	 * @param array $newSitemapOptions The new sitemap options.
	 *
	 * @return void
	 */
	public function maybeSync( $oldSitemapOptions, $newSitemapOptions ) {
		if (
			! $this->canSync() ||
			empty( $oldSitemapOptions ) ||
			empty( $newSitemapOptions )
		) {
			return;
		}

		// Ignore the HTML sitemap, since it's not actually a sitemap to be synced with Google.
		unset( $newSitemapOptions['html'] );

		$shouldResync = false;
		foreach ( $newSitemapOptions as $type => $options ) {
			if ( empty( $oldSitemapOptions[ $type ] ) ) {
				continue;
			}

			if ( $oldSitemapOptions[ $type ]['enable'] !== $options['enable'] ) {
				$shouldResync = true;
				break;
			}
		}

		if ( ! $shouldResync ) {
			return;
		}

		aioseo()->actionScheduler->unschedule( $this->action );
		aioseo()->actionScheduler->scheduleAsync( $this->action );
	}

	/**
	 * Get the sitemaps with errors.
	 *
	 * @since 4.6.2
	 *
	 * @return array
	 */
	public function getSitemapsWithErrors() {
		$sitemaps = aioseo()->internalOptions->searchStatistics->sitemap->list;
		$ignored  = aioseo()->internalOptions->searchStatistics->sitemap->ignored;
		if ( empty( $sitemaps ) ) {
			return [];
		}

		$errors         = [];
		$pluginSitemaps = aioseo()->sitemap->helpers->getSitemapUrls();
		foreach ( $sitemaps as $sitemap ) {
			if (
				empty( $sitemap['errors'] ) ||
				in_array( $sitemap['path'], $ignored, true ) || // Skip user-ignored sitemaps.
				in_array( $sitemap['path'], $pluginSitemaps, true ) // Skip plugin sitemaps.
			) {
				continue;
			}

			$errors[] = $sitemap;
		}

		return $errors;
	}

	/**
	 * Check if the sitemap can be synced.
	 *
	 * @since 4.6.2
	 *
	 * @return bool
	 */
	private function canSync() {
		return aioseo()->searchStatistics->api->auth->isConnected() && aioseo()->internalOptions->searchStatistics->site->verified;
	}
}