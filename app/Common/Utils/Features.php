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
	 * The features URL.
	 *
	 * @since 4.3.0
	 *
	 * @var string
	 */
	protected $featuresUrl = 'https://licensing-cdn.aioseo.com/keys/lite/all-in-one-seo-pack-pro-features.json';

	/**
	 * Returns our features.
	 *
	 * @since 4.3.0
	 *
	 * @param  boolean $flushCache Whether or not to flush the cache.
	 * @return array               An array of addon data.
	 */
	public function getFeatures( $flushCache = false ) {
		$features = aioseo()->core->networkCache->get( 'license_features' );
		if ( null === $features || $flushCache ) {
			$response = aioseo()->helpers->wpRemoteGet( $this->getFeaturesUrl() );
			if ( 200 === wp_remote_retrieve_response_code( $response ) ) {
				$features = json_decode( wp_remote_retrieve_body( $response ) );
			}

			if ( ! $features || ! empty( $features->error ) ) {
				$features = $this->getDefaultFeatures();
			}

			aioseo()->core->networkCache->update( 'license_features', $features );
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
		] ) );
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