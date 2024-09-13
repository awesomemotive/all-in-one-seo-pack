<?php

namespace AIOSEO\Plugin\Common\SearchStatistics;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Keyword Rank Tracker class.
 *
 * @since 4.7.0
 */
class KeywordRankTracker {
	/**
	 * Retrieves all the keywords' statistics.
	 *
	 * @since 4.7.0
	 *
	 * @param  array $formattedKeywords The formatted keywords.
	 * @param  array $args              The arguments.
	 * @return array                    The statistics for the keywords.
	 */
	public function fetchKeywordsStatistics( &$formattedKeywords = [], $args = [] ) { // phpcs:ignore VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
		return [
			'distribution'          => [
				'top3'       => '6.86',
				'top10'      => '11.03',
				'top50'      => '52.10',
				'top100'     => '30.01',
				'difference' => [
					'top3'   => '24.31',
					'top10'  => '33.70',
					'top50'  => '-30.50',
					'top100' => '-27.51'
				]
			],
			'distributionIntervals' => [
				[
					'date'   => '2022-10-23',
					'top3'   => '30.70',
					'top10'  => '38.60',
					'top50'  => '24.50',
					'top100' => '6.20'
				],
				[
					'date'   => '2022-10-30',
					'top3'   => '31.60',
					'top10'  => '42.10',
					'top50'  => '21.00',
					'top100' => '5.30'
				],
				[
					'date'   => '2022-11-06',
					'top3'   => '31.30',
					'top10'  => '44.40',
					'top50'  => '20.30',
					'top100' => '4.00'
				],
				[
					'date'   => '2022-11-13',
					'top3'   => '31.70',
					'top10'  => '44.20',
					'top50'  => '20.20',
					'top100' => '3.90'
				],
				[
					'date'   => '2022-11-20',
					'top3'   => '31.70',
					'top10'  => '45.70',
					'top50'  => '18.00',
					'top100' => '4.60'
				],
				[
					'date'   => '2022-11-27',
					'top3'   => '32.50',
					'top10'  => '47.80',
					'top50'  => '16.80',
					'top100' => '2.90'
				],
				[
					'date'   => '2022-12-04',
					'top3'   => '32.50',
					'top10'  => '47.20',
					'top50'  => '17.90',
					'top100' => '2.40'
				],
				[
					'date'   => '2022-12-11',
					'top3'   => '31.80',
					'top10'  => '43.70',
					'top50'  => '21.00',
					'top100' => '3.50'
				],
				[
					'date'   => '2022-12-18',
					'top3'   => '30.40',
					'top10'  => '43.60',
					'top50'  => '22.40',
					'top100' => '3.60'
				],
				[
					'date'   => '2022-12-25',
					'top3'   => '26.90',
					'top10'  => '37.20',
					'top50'  => '29.70',
					'top100' => '6.20'
				],
				[
					'date'   => '2023-01-01',
					'top3'   => '27.00',
					'top10'  => '33.80',
					'top50'  => '31.60',
					'top100' => '7.60'
				],
				[
					'date'   => '2023-01-08',
					'top3'   => '26.60',
					'top10'  => '38.60',
					'top50'  => '30.00',
					'top100' => '4.80'
				],
				[
					'date'   => '2023-01-16',
					'top3'   => '31.10',
					'top10'  => '43.90',
					'top50'  => '22.50',
					'top100' => '2.50'
				]
			]
		];
	}

	/**
	 * Retrieves all the keywords, formatted.
	 *
	 * @since 4.7.0
	 *
	 * @param  array $args The arguments.
	 * @return array       The formatted keywords.
	 */
	public function getFormattedKeywords( $args = [] ) { // phpcs:ignore VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
		$statistics = [];
		for ( $i = 1; $i < 9; $i++ ) {
			$statistics[ $i ] = [
				'clicks'      => wp_rand( 1, 1000 ),
				'impressions' => wp_rand( 10, 10000 ),
				'ctr'         => wp_rand( 1, 99 ),
				'position'    => wp_rand( 1, 100 ),
				'history'     => [
					[
						'date'     => gmdate( 'Y-m-d', strtotime( '-30 days' ) ),
						'position' => wp_rand( 1, 15 ),
						'clicks'   => wp_rand( 10, 100 ),
					],
					[
						'date'     => gmdate( 'Y-m-d', strtotime( '-23 days' ) ),
						'position' => wp_rand( 1, 15 ),
						'clicks'   => wp_rand( 10, 100 ),
					],
					[
						'date'     => gmdate( 'Y-m-d', strtotime( '-16 days' ) ),
						'position' => wp_rand( 1, 15 ),
						'clicks'   => wp_rand( 10, 100 ),
					],
					[
						'date'     => gmdate( 'Y-m-d', strtotime( '-9 days' ) ),
						'position' => wp_rand( 1, 15 ),
						'clicks'   => wp_rand( 10, 100 ),
					],
					[
						'date'     => gmdate( 'Y-m-d', strtotime( '-2 days' ) ),
						'position' => wp_rand( 1, 15 ),
						'clicks'   => wp_rand( 10, 100 ),
					]
				]
			];
		}

		return [
			'rows'   => [
				[
					'id'         => 1,
					'name'       => 'best seo plugin',
					'favorited'  => false,
					'groups'     => [
						[
							'id'   => 1,
							'name' => 'Blog Pages Group'
						]
					],
					'statistics' => $statistics[1]
				],
				[
					'id'         => 2,
					'name'       => 'aioseo is the best',
					'favorited'  => true,
					'groups'     => [
						[
							'id'   => 2,
							'name' => 'Low Performance Group'
						]
					],
					'statistics' => $statistics[2]
				],
				[
					'id'         => 3,
					'name'       => 'analyze my seo',
					'favorited'  => false,
					'groups'     => [
						[
							'id'   => 3,
							'name' => 'High Performance Group'
						]
					],
					'statistics' => $statistics[3]
				],
				[
					'id'         => 4,
					'name'       => 'wordpress seo',
					'favorited'  => false,
					'groups'     => [],
					'statistics' => $statistics[4]
				],
				[
					'id'         => 5,
					'name'       => 'best seo plugin pro',
					'favorited'  => false,
					'groups'     => [],
					'statistics' => $statistics[5]
				],
				[
					'id'         => 6,
					'name'       => 'aioseo wordpress',
					'favorited'  => false,
					'groups'     => [],
					'statistics' => $statistics[6]
				],
				[
					'id'         => 7,
					'name'       => 'headline analyzer aioseo',
					'favorited'  => false,
					'groups'     => [],
					'statistics' => $statistics[7]
				],
				[
					'id'         => 8,
					'name'       => 'best seo plugin plugin',
					'favorited'  => false,
					'groups'     => [],
					'statistics' => $statistics[8]
				]
			],
			'totals' => [
				'total' => 8,
				'pages' => 1,
				'page'  => 1
			],
		];
	}

	/**
	 * Retrieves all the keyword groups, formatted.
	 *
	 * @since 4.7.0
	 *
	 * @param  array $args The arguments.
	 * @return array       The formatted keyword groups.
	 */
	public function getFormattedGroups( $args = [] ) { // phpcs:ignore VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
		$statistics = [];
		for ( $i = 1; $i < 4; $i++ ) {
			$statistics[ $i ] = [
				'clicks'      => wp_rand( 1, 1000 ),
				'impressions' => wp_rand( 10, 10000 ),
				'ctr'         => wp_rand( 1, 99 ),
				'position'    => wp_rand( 1, 100 )
			];
		}

		return [
			'rows'   => [
				[
					'id'          => 1,
					'name'        => 'Blog Pages Group',
					'keywordsQty' => 1,
					'keywords'    => [],
					'statistics'  => $statistics[1]
				],
				[
					'id'          => 2,
					'name'        => 'Low Performance Group',
					'keywordsQty' => 1,
					'keywords'    => [],
					'statistics'  => $statistics[2]
				],
				[
					'id'          => 3,
					'name'        => 'High Performance Group',
					'keywordsQty' => 1,
					'keywords'    => [],
					'statistics'  => $statistics[3]
				]
			],
			'totals' => [
				'total' => 8,
				'pages' => 1,
				'page'  => 1
			],
		];
	}

	/**
	 * Returns the data for Vue.
	 *
	 * @since 4.7.0
	 *
	 * @return array The data for Vue.
	 */
	public function getVueData() {
		$formattedKeywords = $this->getFormattedKeywords();
		$formattedGroups   = $this->getFormattedGroups();

		return [
			// Dummy data to show on the UI.
			'keywords' => [
				'all'        => $formattedKeywords,
				'paginated'  => $formattedKeywords,
				'count'      => count( $formattedKeywords['rows'] ),
				'statistics' => $this->fetchKeywordsStatistics( $formattedKeywords ),
			],
			'groups'   => [
				'all'       => $formattedGroups,
				'paginated' => $formattedGroups,
				'count'     => count( $formattedGroups['rows'] ),
			],
		];
	}

	/**
	 * Returns the data for Vue.
	 *
	 * @since 4.7.0
	 *
	 * @return array The data.
	 */
	public function getVueDataEdit() {
		$formattedKeywords = $this->getFormattedKeywords();

		return [
			// Dummy data to show on the UI.
			'keywords' => [
				'all'       => $formattedKeywords,
				'paginated' => $formattedKeywords,
				'count'     => count( $formattedKeywords['rows'] ),
			],
		];
	}
}