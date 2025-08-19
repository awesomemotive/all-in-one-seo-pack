<?php
namespace AIOSEO\Plugin\Common\SeoAnalysis;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AIOSEO\Plugin\Common\Models;

/**
 * Class that holds our Seo Analysis feature.
 *
 * @since 4.8.6
 */
class SeoAnalysis {

	/**
	 * Returns the data for Vue.
	 *
	 * @since 4.8.6
	 *
	 * @return array The data for Vue.
	 */
	public function getVueData() {
		$data = [
			'homeResults'    => Models\SeoAnalyzerResult::getResults(),
			'competitors'    => Models\SeoAnalyzerResult::getCompetitorsResults(),
			'allUrlsResults' => $this->getAllUrlsForUnlicensed()
		];

		return $data;
	}

	/**
	 * Get all URLs for unlicensed, this is only used for the unlicensed version of the plugin.
	 *
	 * @since 4.8.6
	 *
	 * @return array The all URLs.
	 */
	private function getAllUrlsForUnlicensed() {
		$posts = get_posts( [
			'post_type'      => aioseo()->helpers->getPublicPostTypes( true ),
			'posts_per_page' => 10,
			'post_status'    => aioseo()->helpers->getPublicPostStatuses( true )
		] );

		$rows = array_map( function( $post ) {
			$postType     = get_post_type_object( $post->post_type );
			$subtypeLabel = $postType ? $postType->labels->singular_name : $post->post_type;

			return [
				'counts'           => [
					'error'   => 6,
					'warning' => 5,
					'passed'  => 10
				],
				'id'               => $post->ID,
				'isTruSeoEligible' => true,
				'permalink'        => get_permalink( $post->ID ),
				'type'             => $post->post_type,
				'subtype'          => [
					'value' => $post->post_type,
					'label' => $subtypeLabel
				],
				'title'            => $post->post_title,
			];
		}, $posts );

		return [
			'rows'   => $rows,
			'totals' => [
				'page'  => 1,
				'pages' => 1,
				'total' => 10
			]
		];
	}
}