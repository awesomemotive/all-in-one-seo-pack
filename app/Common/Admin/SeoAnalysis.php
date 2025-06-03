<?php
namespace AIOSEO\Plugin\Common\Admin;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AIOSEO\Plugin\Common\Models\SeoAnalyzerResult;

/**
 * Handles all admin code for the SEO Analysis menu.
 *
 * @since 4.2.6
 */
class SeoAnalysis {
	/**
	 * Class constructor.
	 *
	 * @since 4.2.6
	 */
	public function __construct() {
		add_action( 'save_post', [ $this, 'bustStaticHomepageResults' ] );
	}

	/**
	 * Busts the SEO Analysis for the static homepage when it is updated.
	 *
	 * @since 4.2.6
	 *
	 * @param  int  $postId The post ID.
	 * @return void
	 */
	public function bustStaticHomepageResults( $postId ) {
		if ( ! aioseo()->helpers->isStaticHomePage( $postId ) ) {
			return;
		}

		aioseo()->internalOptions->internal->siteAnalysis->score = 0;
		SeoAnalyzerResult::deleteByUrl( null );

		aioseo()->core->cache->delete( 'analyze_site_code' );
		aioseo()->core->cache->delete( 'analyze_site_body' );
	}
}