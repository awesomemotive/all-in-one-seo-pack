<?php
namespace AIOSEO\Plugin\Common\Utils;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Contains helper methods specific to the addons.
 *
 * @since 4.3.0
 */
class Features {
	/**
	 * The Action Scheduler action name for refreshing the features cache.
	 *
	 * @since 4.9.5.2
	 *
	 * @var string
	 */
	private $actionName = 'aioseo_features_refresh';

	/**
	 * The features URL.
	 *
	 * @since 4.3.0
	 *
	 * @var string
	 */
	protected $featuresUrl = 'https://licensing-cdn.aioseo.com/keys/lite/all-in-one-seo-pack-pro-features.json';

	/**
	 * Class constructor.
	 *
	 * @since 4.9.5.2
	 */
	public function __construct() {
		add_action( 'admin_init', [ $this, 'scheduleRefresh' ] );
		add_action( $this->actionName, [ $this, 'refresh' ] );
	}

	/**
	 * Schedules the daily recurring features cache refresh.
	 * Hooked into `admin_init` action hook.
	 *
	 * @since 4.9.5.2
	 *
	 * @return void
	 */
	public function scheduleRefresh() {
		if ( aioseo()->actionScheduler->isScheduled( $this->actionName ) ) {
			return;
		}

		aioseo()->actionScheduler->scheduleRecurrent( $this->actionName, 0, DAY_IN_SECONDS );
	}

	/**
	 * Refreshes the features cache.
	 * Hooked into `aioseo_features_refresh` action hook.
	 *
	 * @since 4.9.5.2
	 *
	 * @return void
	 */
	public function refresh() {
		$this->getFeatures( true );
	}

	/**
	 * Returns our features.
	 *
	 * @since   4.3.0
	 * @version 4.9.7.2 Always cache the fetch result (real data or {@see self::getDefaultFeatures()}); a failed fetch previously left the cache empty and re-hit the CDN on every page load.
	 * @version 4.9.7.2 Cache-miss check restored to strict null compare so cached defaults are not treated as a miss. Return value guarded to always be an array.
	 *
	 * @param  boolean $flushCache Whether or not to flush the cache.
	 * @return array               An array of addon data.
	 */
	public function getFeatures( $flushCache = false ) {
		$features = aioseo()->core->networkCache->get( 'license_features' );

		if ( null === $features || $flushCache ) {
			$remote   = null;
			$response = aioseo()->helpers->wpRemoteGet( $this->getFeaturesUrl() );
			if ( 200 === wp_remote_retrieve_response_code( $response ) ) {
				$decoded = json_decode( wp_remote_retrieve_body( $response ), true );
				if ( ! empty( $decoded ) && ( ! is_object( $decoded ) || empty( $decoded->error ) ) ) {
					$remote = $decoded;
				}
			}

			// Always cache something — real data on success, defaults on failure — so a flaky CDN can't trigger a refetch on every page load. The daily refresh cron overwrites this with fresh data.
			$features = null !== $remote ? $remote : $this->getDefaultFeatures();
			aioseo()->core->networkCache->update( 'license_features', $features );
		}

		// Convert the features array to objects using JSON. This is essential because we have lots of features that rely on this to be an object, and changing it to an array would break them.

		$features = json_decode( wp_json_encode( $features ) );

		// Guard the return so downstream foreach/array_* calls cannot fatal on PHP 8+ if the round-trip yielded null (e.g., wp_json_encode failure on malformed cached data).
		if ( ! is_array( $features ) ) {
			$features = $this->getDefaultFeatures();
		}

		return $features;
	}

	/**
	 * Get the URL to get features.
	 *
	 * @since 4.1.8
	 *
	 * @return string The URL.
	 */
	protected function getFeaturesUrl() {
		$url = $this->featuresUrl;
		if ( defined( 'AIOSEO_FEATURES_URL' ) ) {
			$url = AIOSEO_FEATURES_URL;
		}

		return $url;
	}

	/**
	 * Retrieves a default list of all external saas features available for the current user if the API cannot be reached.
	 *
	 * @since 4.3.0
	 *
	 * @return array An array of features.
	 */
	protected function getDefaultFeatures() {
		return json_decode( wp_json_encode( [
			[
				'license_level' => 'pro',
				'section'       => 'schema',
				'feature'       => 'event'
			],
			[
				'license_level' => 'elite',
				'section'       => 'schema',
				'feature'       => 'event'
			],
			[
				'license_level' => 'elite',
				'section'       => 'schema',
				'feature'       => 'job-posting'
			],
			[
				'license_level' => 'elite',
				'section'       => 'tools',
				'feature'       => 'network-tools-site-activation'
			],
			[
				'license_level' => 'elite',
				'section'       => 'tools',
				'feature'       => 'network-tools-database'
			],
			[
				'license_level' => 'elite',
				'section'       => 'tools',
				'feature'       => 'network-tools-import-export'
			],
			[
				'license_level' => 'elite',
				'section'       => 'tools',
				'feature'       => 'network-tools-robots'
			],
			[
				'license_level' => 'elite',
				'section'       => 'search-statistics',
				'feature'       => 'seo-statistics'
			],
			[
				'license_level' => 'elite',
				'section'       => 'search-statistics',
				'feature'       => 'keyword-rankings'
			],
			[
				'license_level' => 'elite',
				'section'       => 'search-statistics',
				'feature'       => 'keyword-rankings-pages'
			],
			[
				'license_level' => 'elite',
				'section'       => 'search-statistics',
				'feature'       => 'content-rankings'
			],
			[
				'license_level' => 'elite',
				'section'       => 'search-statistics',
				'feature'       => 'post-detail'
			],
			[
				'license_level' => 'elite',
				'section'       => 'search-statistics',
				'feature'       => 'post-detail-page-speed'
			],
			[
				'license_level' => 'elite',
				'section'       => 'search-statistics',
				'feature'       => 'post-detail-seo-statistics'
			],
			[
				'license_level' => 'elite',
				'section'       => 'search-statistics',
				'feature'       => 'post-detail-keywords'
			],
			[
				'license_level' => 'elite',
				'section'       => 'search-statistics',
				'feature'       => 'post-detail-focus-keyword-trend'
			],
			[
				'license_level' => 'elite',
				'section'       => 'search-statistics',
				'feature'       => 'keyword-tracking'
			],
			[
				'license_level' => 'elite',
				'section'       => 'search-statistics',
				'feature'       => 'post-detail-keyword-tracking'
			],
			[
				'license_level' => 'elite',
				'section'       => 'search-statistics',
				'feature'       => 'index-status'
			]
		] ), true );
	}

	/**
	 * Get the plans for a given feature.
	 *
	 * @since 4.3.0
	 *
	 * @param  string $sectionSlug The section name.
	 * @param  string $feature     The feature name.
	 * @return array               The plans for the feature.
	 */
	public function getPlansForFeature( $sectionSlug, $feature = '' ) {
		$plans = [];

		// Loop through all the features and find the plans that have access to the feature.
		foreach ( $this->getFeatures() as $featureArray ) {
			if ( $featureArray->section !== $sectionSlug ) {
				continue;
			}

			if ( ! empty( $feature ) && $featureArray->feature !== $feature ) {
				continue;
			}

			$plans[] = ucfirst( $featureArray->license_level );
		}

		return array_unique( $plans );
	}
}