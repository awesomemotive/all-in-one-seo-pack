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
	 * @since   4.6.2
	 * @version 4.9.4.2 Change admin_init to init to allow frontend scheduling.
	 */
	public function __construct() {
		add_action( 'admin_init', [ $this, 'init' ] );
		add_action( $this->action, [ $this, 'worker' ] );
	}

	/**
	 * Initialize the class and schedule the weekly recurring sync.
	 *
	 * @since   4.6.2
	 * @version 4.9.4.2 Switch to weekly recurring action.
	 *
	 * @return void
	 */
	public function init() {
		if (
			! aioseo()->searchStatistics->api->auth->isConnected() ||
			! aioseo()->internalOptions->searchStatistics->site->verified
		) {
			return;
		}

		if ( aioseo()->actionScheduler->isScheduled( $this->action ) ) {
			return;
		}

		aioseo()->actionScheduler->scheduleRecurrent( $this->action, 10, WEEK_IN_SECONDS );
	}

	/**
	 * Sync the sitemap.
	 *
	 * @since   4.6.3
	 * @version 4.9.4.2 Add runtime lock; remove self-scheduling (now handled by recurring action).
	 *
	 * @return void
	 */
	public function worker() {
		// Runtime lock: Prevent concurrent execution of this action.
		$lockKey = 'as_sitemap_sync_running';
		if ( aioseo()->core->cache->get( $lockKey ) ) {
			return;
		}

		// Set lock with a safety timeout in case the action fails mid-execution.
		aioseo()->core->cache->update( $lockKey, true, 5 * MINUTE_IN_SECONDS );

		if ( ! $this->canSync() ) {
			aioseo()->core->cache->delete( $lockKey );

			return;
		}

		$api      = new Api\Request( 'google-search-console/sitemap/sync/', [ 'sitemaps' => aioseo()->sitemap->helpers->getSitemapUrls() ] );
		$response = $api->request();

		if ( ! is_wp_error( $response ) && ! empty( $response['data'] ) ) {
			aioseo()->internalOptions->searchStatistics->sitemap->list      = $response['data'];
			aioseo()->internalOptions->searchStatistics->sitemap->lastFetch = time();
		}

		aioseo()->core->cache->delete( $lockKey );
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