<?php
namespace AIOSEO\Plugin\Common\Services;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AIOSEO\Plugin\Common\Models;

/**
 * Service for AIOSEO's SEO audit features.
 *
 * Two abilities sit on top of this service:
 * - `aioseo/audit/homepage/get`: drills into the SEO Site Analysis scan result for the homepage
 *   (the check-by-check breakdown AIOSEO renders in Tools > SEO Analysis).
 * - `aioseo/audit/site/get`: returns a site-wide health snapshot pulling together the scan totals
 *   plus broader signals (sitemap on/off, count of custom robots rules, etc.).
 *
 * @internal Not a public extension surface.
 *
 * @since 4.9.8
 */
class AuditService {
	/**
	 * Returns the homepage SEO scan results — counts by severity plus the full issue list.
	 *
	 * @since 4.9.8
	 *
	 * @return array|\WP_Error
	 */
	public function getHomepage() {
		if ( ! aioseo()->access->hasAccess( 'aioseo_seo_analysis_settings' ) ) {
			return new \WP_Error( 'forbidden', __( 'You do not have permission to read SEO analysis data.', 'all-in-one-seo-pack' ), [ 'status' => 403 ] );
		}

		$results = (array) Models\SeoAnalyzerResult::getResults();
		$score   = isset( $results['score'] ) ? (int) $results['score'] : 0;
		$groups  = isset( $results['results'] ) && is_array( $results['results'] ) ? $results['results'] : [];

		$totals = [
			'errors'   => 0,
			'warnings' => 0,
			'passed'   => 0
		];
		$issues = [];

		foreach ( $groups as $groupName => $items ) {
			if ( ! is_array( $items ) ) {
				continue;
			}
			foreach ( $items as $name => $item ) {
				$severity = $this->classifySeverity( $item );
				if ( ! isset( $totals[ $severity ] ) ) {
					$severity = 'passed';
				}
				$totals[ $severity ]++;

				$issues[] = [
					'code'     => (string) $name,
					'group'    => (string) $groupName,
					'severity' => $severity,
					'status'   => is_array( $item ) && isset( $item['status'] ) ? (string) $item['status'] : ''
				];
			}
		}

		return [
			'score'  => $score,
			'totals' => $totals,
			'issues' => $issues
		];
	}

	/**
	 * Returns a site-wide health snapshot — high-level signals an agent can use to triage SEO setup.
	 *
	 * @since 4.9.8
	 *
	 * @return array|\WP_Error
	 */
	public function getSite() {
		if ( ! aioseo()->access->hasAccess( 'aioseo_seo_analysis_settings' ) ) {
			return new \WP_Error( 'forbidden', __( 'You do not have permission to read SEO analysis data.', 'all-in-one-seo-pack' ), [ 'status' => 403 ] );
		}

		$homepage = $this->getHomepage();
		if ( is_wp_error( $homepage ) ) {
			return $homepage;
		}

		$sitemapEnabled    = (bool) ( aioseo()->options->sitemap->general->enable ?? false );
		$robotsRulesCount  = is_array( aioseo()->options->tools->robots->rules ) ? count( aioseo()->options->tools->robots->rules ) : 0;
		$publicPostTypes   = array_values( aioseo()->helpers->getPublicPostTypes( true ) );
		$searchStatsActive = ! empty( aioseo()->searchStatistics ) && method_exists( aioseo()->searchStatistics, 'isConnected' ) && aioseo()->searchStatistics->isConnected();

		return [
			'score'                     => $homepage['score'],
			'totals'                    => $homepage['totals'],
			'sitemap_enabled'           => $sitemapEnabled,
			'sitemap_index_url'         => esc_url_raw( home_url( '/sitemap.xml' ) ),
			'custom_robots_rules_count' => $robotsRulesCount,
			'public_post_types'         => $publicPostTypes,
			'search_console_connected'  => $searchStatsActive
		];
	}

	/**
	 * Maps a SeoAnalyzerResult item's status into the agent-facing severity bucket.
	 *
	 * @since 4.9.8
	 *
	 * @param  mixed $item The analyzer item — array with `status` key, or a scalar.
	 * @return string One of `error`, `warning`, `passed`.
	 */
	protected function classifySeverity( $item ) {
		if ( ! is_array( $item ) ) {
			return 'passed';
		}

		$status = isset( $item['status'] ) ? strtolower( (string) $item['status'] ) : '';
		if ( in_array( $status, [ 'error', 'critical', 'fail' ], true ) ) {
			return 'errors';
		}
		if ( in_array( $status, [ 'warning', 'warn' ], true ) ) {
			return 'warnings';
		}

		return 'passed';
	}
}