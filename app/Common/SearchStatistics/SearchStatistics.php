<?php
namespace AIOSEO\Plugin\Common\SearchStatistics;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class that holds our Search Statistics feature.
 *
 * @since 4.3.0
 */
class SearchStatistics {
	/**
	 * Holds the instance of the API class.
	 *
	 * @since 4.3.0
	 *
	 * @var Api\Api
	 */
	public $api;

	/**
	 * Holds the instance of the Site class.
	 *
	 * @since 4.6.2
	 *
	 * @var Site
	 */
	public $site;

	/**
	 * Holds the instance of the Sitemap class.
	 *
	 * @since 4.6.2
	 *
	 * @var Sitemap
	 */
	public $sitemap;

	/**
	 * Holds the instance of the Notices class.
	 *
	 * @since 4.6.2
	 *
	 * @var Notices
	 */
	public $notices;

	/**
	 * Class constructor.
	 *
	 * @since 4.3.0
	 */
	public function __construct() {
		$this->api     = new Api\Api();
		$this->site    = new Site();
		$this->sitemap = new Sitemap();
		$this->notices = new Notices();
	}

	/**
	 * Returns the data for Vue.
	 *
	 * @since 4.3.0
	 *
	 * @return array The data for Vue.
	 */
	public function getVueData() {
		$data = [
			'isConnected'         => aioseo()->searchStatistics->api->auth->isConnected(),
			'latestAvailableDate' => null,
			'range'               => [],
			'rolling'             => aioseo()->internalOptions->internal->searchStatistics->rolling,
			'authedSite'          => aioseo()->searchStatistics->api->auth->getAuthedSite(),
			'sitemapsWithErrors'  => aioseo()->searchStatistics->sitemap->getSitemapsWithErrors(),
			'data'                => [
				'seoStatistics'   => $this->getSeoOverviewData(),
				'keywords'        => $this->getKeywordsData(),
				'contentRankings' => $this->getContentRankingsData()
			]
		];

		return $data;
	}

	/**
	 * Resets the Search Statistics.
	 *
	 * @since 4.6.2
	 *
	 * @return void
	 */
	public function reset() {
		aioseo()->internalOptions->searchStatistics->sitemap->reset();
		aioseo()->internalOptions->searchStatistics->site->reset();

		// Clear the cache for the Search Statistics.
		aioseo()->searchStatistics->clearCache();
	}

	/**
	 * Returns the data for the SEO Overview.
	 *
	 * @since 4.3.0
	 *
	 * @param  array $dateRange The date range.
	 * @return array            The data for the SEO Overview.
	 */
	protected function getSeoOverviewData( $dateRange = [] ) { // phpcs:ignore VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
		$pageRows = [
			'/'                       => [
				'ctr'              => '1.25',
				'page'             => '/',
				'clicks'           => 15460,
				'position'         => '74.01',
				'difference'       => [
					'ctr'         => '-0.23',
					'decay'       => 192211,
					'clicks'      => -26,
					'current'     => true,
					'position'    => '19.66',
					'comparison'  => true,
					'impressions' => 192237
				],
				'impressions'      => 1235435,
				'context'          => [],
				'objectId'         => 0,
				'objectTitle'      => '/',
				'objectType'       => 'post',
				'inspectionResult' => $this->getInspectionResult(),
				'seoScore'         => 65
			],
			'/test-page/'             => [
				'ctr'              => '0.30',
				'page'             => '/test-page/',
				'clicks'           => 5688,
				'position'         => '35.28',
				'difference'       => [
					'ctr'         => '0.05',
					'decay'       => 378973,
					'clicks'      => 1941,
					'current'     => true,
					'position'    => '-2.33',
					'comparison'  => true,
					'impressions' => 377032
				],
				'impressions'      => 1881338,
				'context'          => [],
				'objectId'         => 0,
				'objectTitle'      => '/test-page/',
				'objectType'       => 'post',
				'inspectionResult' => $this->getInspectionResult(),
				'seoScore'         => 95
			],
			'/high-ranking-page/'     => [
				'ctr'              => '6.03',
				'page'             => '/high-ranking-page/',
				'clicks'           => 3452,
				'position'         => '22.85',
				'difference'       => [
					'ctr'         => '-0.95',
					'decay'       => -5986,
					'clicks'      => -898,
					'current'     => true,
					'position'    => '-0.22',
					'comparison'  => true,
					'impressions' => -5088
				],
				'impressions'      => 57248,
				'context'          => [],
				'objectId'         => 0,
				'objectTitle'      => '/high-ranking-page/',
				'objectType'       => 'post',
				'inspectionResult' => $this->getInspectionResult(),
				'seoScore'         => 100
			],
			'/pricing/'               => [
				'ctr'              => '1.35',
				'page'             => '/pricing/',
				'clicks'           => 2749,
				'position'         => '40.40',
				'difference'       => [
					'ctr'         => '-0.16',
					'decay'       => 15991,
					'clicks'      => -94,
					'current'     => true,
					'position'    => '9.77',
					'comparison'  => true,
					'impressions' => 16085
				],
				'impressions'      => 203794,
				'context'          => [],
				'objectId'         => 0,
				'objectTitle'      => '/pricing/',
				'objectType'       => 'post',
				'inspectionResult' => $this->getInspectionResult(),
				'seoScore'         => 100
			],
			'/features-and-benefits/' => [
				'ctr'              => '2.48',
				'page'             => '/features-and-benefits/',
				'clicks'           => 2600,
				'position'         => '15.53',
				'difference'       => [
					'ctr'         => '0.99',
					'decay'       => 23466,
					'clicks'      => 1367,
					'current'     => true,
					'position'    => '1.51',
					'comparison'  => true,
					'impressions' => 22099
				],
				'impressions'      => 104769,
				'context'          => [],
				'objectId'         => 0,
				'objectTitle'      => '/features-and-benefits/',
				'objectType'       => 'post',
				'inspectionResult' => $this->getInspectionResult(),
				'seoScore'         => 90
			],
			'/documentation/'         => [
				'ctr'              => '2.64',
				'page'             => '/documentation/',
				'clicks'           => 1716,
				'position'         => '27.85',
				'difference'       => [
					'ctr'         => '-0.04',
					'decay'       => 7274,
					'clicks'      => 167,
					'current'     => true,
					'position'    => '-9.51',
					'comparison'  => true,
					'impressions' => 7107
				],
				'impressions'      => 64883,
				'context'          => [],
				'objectId'         => 0,
				'objectTitle'      => '/documentation/',
				'objectType'       => 'post',
				'inspectionResult' => $this->getInspectionResult(),
				'seoScore'         => 93
			],
			'/blog/'                  => [
				'ctr'              => '3.75',
				'page'             => '/blog/',
				'clicks'           => 1661,
				'position'         => '36.60',
				'difference'       => [
					'ctr'         => '0.42',
					'decay'       => -3145,
					'clicks'      => 77,
					'current'     => true,
					'position'    => '-9.40',
					'comparison'  => true,
					'impressions' => -3222
				],
				'impressions'      => 44296,
				'context'          => [],
				'objectId'         => 0,
				'objectTitle'      => '/blog/',
				'objectType'       => 'post',
				'inspectionResult' => $this->getInspectionResult(),
				'seoScore'         => 97
			],
			'/blog/my-best-content/'  => [
				'ctr'              => '7.08',
				'page'             => '/blog/my-best-content/',
				'clicks'           => 1573,
				'position'         => '9.61',
				'difference'       => [
					'ctr'         => '0.16',
					'decay'       => -201,
					'clicks'      => 22,
					'current'     => true,
					'position'    => '-2.03',
					'comparison'  => true,
					'impressions' => -223
				],
				'impressions'      => 22203,
				'context'          => [],
				'objectId'         => 0,
				'objectTitle'      => '/blog/my-best-content/',
				'objectType'       => 'post',
				'inspectionResult' => $this->getInspectionResult(),
				'seoScore'         => 56
			],
			'/contact-us/'            => [
				'ctr'              => '1.45',
				'page'             => '/contact-us/',
				'clicks'           => 1550,
				'position'         => '32.05',
				'difference'       => [
					'ctr'         => '0.12',
					'decay'       => 1079,
					'clicks'      => 140,
					'current'     => true,
					'position'    => '-3.47',
					'comparison'  => true,
					'impressions' => 939
				],
				'impressions'      => 106921,
				'context'          => [],
				'objectId'         => 0,
				'objectTitle'      => '/contact-us/',
				'objectType'       => 'post',
				'inspectionResult' => $this->getInspectionResult(),
				'seoScore'         => 78
			],
			'/support/'               => [
				'ctr'              => '5.94',
				'page'             => '/support/',
				'clicks'           => 1549,
				'position'         => '25.83',
				'difference'       => [
					'ctr'         => '-0.74',
					'decay'       => 3885,
					'clicks'      => 62,
					'current'     => true,
					'position'    => '-1.48',
					'comparison'  => true,
					'impressions' => 3823
				],
				'impressions'      => 26099,
				'context'          => [],
				'objectId'         => 0,
				'objectTitle'      => '/support/',
				'objectType'       => 'post',
				'inspectionResult' => $this->getInspectionResult(),
				'seoScore'         => 86
			]
		];

		return [
			'statistics' => [
				'ctr'         => '0.74',
				'clicks'      => 111521,
				'keywords'    => 19335,
				'position'    => '49.28',
				'difference'  => [
					'ctr'         => '0.03',
					'clicks'      => 1736,
					'keywords'    => 2853,
					'position'    => '1.01',
					'impressions' => -475679
				],
				'impressions' => 14978074
			],
			'intervals'  => [
				[
					'ctr'         => '0.72',
					'date'        => '2022-10-23',
					'clicks'      => 7091,
					'position'    => '48.88',
					'impressions' => 985061
				],
				[
					'ctr'         => '0.77',
					'date'        => '2022-10-30',
					'clicks'      => 8544,
					'position'    => '46.48',
					'impressions' => 1111602
				],
				[
					'ctr'         => '0.73',
					'date'        => '2022-11-06',
					'clicks'      => 9087,
					'position'    => '48.44',
					'impressions' => 1247506
				],
				[
					'ctr'         => '0.75',
					'date'        => '2022-11-13',
					'clicks'      => 9952,
					'position'    => '50.03',
					'impressions' => 1326910
				],
				[
					'ctr'         => '0.73',
					'date'        => '2022-11-20',
					'clicks'      => 9696,
					'position'    => '50.28',
					'impressions' => 1324633
				],
				[
					'ctr'         => '0.69',
					'date'        => '2022-11-27',
					'clicks'      => 9590,
					'position'    => '51.03',
					'impressions' => 1382602
				],
				[
					'ctr'         => '0.71',
					'date'        => '2022-12-04',
					'clicks'      => 9691,
					'position'    => '51.07',
					'impressions' => 1365509
				],
				[
					'ctr'         => '0.71',
					'date'        => '2022-12-11',
					'clicks'      => 9291,
					'position'    => '51.22',
					'impressions' => 1316184
				],
				[
					'ctr'         => '0.80',
					'date'        => '2022-12-18',
					'clicks'      => 8659,
					'position'    => '48.20',
					'impressions' => 1081944
				],
				[
					'ctr'         => '0.75',
					'date'        => '2022-12-25',
					'clicks'      => 6449,
					'position'    => '49.31',
					'impressions' => 857591
				],
				[
					'ctr'         => '0.66',
					'date'        => '2023-01-01',
					'clicks'      => 5822,
					'position'    => '48.16',
					'impressions' => 876828
				],
				[
					'ctr'         => '0.77',
					'date'        => '2023-01-08',
					'clicks'      => 7501,
					'position'    => '47.34',
					'impressions' => 975764
				],
				[
					'ctr'         => '0.90',
					'date'        => '2023-01-16',
					'clicks'      => 10148,
					'position'    => '48.29',
					'impressions' => 1125940
				]
			],
			'pages'      => [
				'topPages'   => [
					'rows' => $pageRows
				],
				'paginated'  => [
					'rows'              => $pageRows,
					'totals'            => [
						'page'  => 1,
						'pages' => 292,
						'total' => 2914
					],
					'filters'           => [
						[
							'slug'   => 'all',
							'name'   => 'All',
							'active' => true
						],
						[
							'slug'   => 'topLosing',
							'name'   => 'Top Losing',
							'active' => false
						],
						[
							'slug'   => 'topWinning',
							'name'   => 'Top Winning',
							'active' => false
						]
					],
					'additionalFilters' => [
						[
							'name'    => 'postType',
							'options' => [
								[
									'label' => __( 'All Content Types', 'all-in-one-seo-pack' ),
									'value' => ''
								]
							]
						]
					]
				],
				'topLosing'  => [
					'rows' => []
				],
				'topWinning' => [
					'rows' => []
				]
			]
		];
	}

	/**
	 * Returns the data for the Keywords.
	 *
	 * @since 4.3.0
	 *
	 * @param  array $dateRange The date range.
	 * @return array            The data for the Keywords.
	 */
	protected function getKeywordsData( $dateRange = [] ) { // phpcs:ignore VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
		$keywordsRows = [
			[
				'ctr'         => '4.89',
				'clicks'      => 5000,
				'keyword'     => 'best seo plugin',
				'position'    => '1.93',
				'difference'  => [
					'ctr'         => '-1.06',
					'decay'       => 6590,
					'clicks'      => -652,
					'position'    => '0.07',
					'impressions' => 7242
				],
				'impressions' => 102233
			],
			[
				'ctr'         => '7.06',
				'clicks'      => 4404,
				'keyword'     => 'aioseo is the best',
				'position'    => '1.32',
				'difference'  => [
					'ctr'         => '0.13',
					'decay'       => 8586,
					'clicks'      => 633,
					'position'    => '0.12',
					'impressions' => 7953
				],
				'impressions' => 62357
			],
			[
				'ctr'         => '2.81',
				'clicks'      => 1715,
				'keyword'     => 'analyze my seo',
				'position'    => '6.29',
				'difference'  => [
					'ctr'         => '-0.03',
					'decay'       => 13217,
					'clicks'      => 347,
					'position'    => '-0.34',
					'impressions' => 12870
				],
				'impressions' => 61102
			],
			[
				'ctr'         => '7.46',
				'clicks'      => 717,
				'keyword'     => 'wordpress seo',
				'position'    => '1.18',
				'difference'  => [
					'ctr'         => '-0.69',
					'decay'       => 2729,
					'clicks'      => 144,
					'position'    => '-0.08',
					'impressions' => 2585
				],
				'impressions' => 9614
			],
			[
				'ctr'         => '6.66',
				'clicks'      => 446,
				'keyword'     => 'best seo plugin pro',
				'position'    => '1.65',
				'difference'  => [
					'ctr'         => '0.36',
					'decay'       => -121,
					'clicks'      => 16,
					'position'    => '0.33',
					'impressions' => -137
				],
				'impressions' => 6693
			],
			[
				'ctr'         => '7.39',
				'clicks'      => 409,
				'keyword'     => 'aioseo wordpress',
				'position'    => '1.77',
				'difference'  => [
					'ctr'         => '-0.39',
					'decay'       => 534,
					'clicks'      => 19,
					'position'    => '-0.13',
					'impressions' => 515
				],
				'impressions' => 5531
			],
			[
				'ctr'         => '1.11',
				'clicks'      => 379,
				'keyword'     => 'headline analyzer aioseo',
				'position'    => '8.41',
				'difference'  => [
					'ctr'         => '0.43',
					'decay'       => 134,
					'clicks'      => 147,
					'position'    => '-1.36',
					'impressions' => -13
				],
				'impressions' => 34043
			],
			[
				'ctr'         => '2.63',
				'clicks'      => 364,
				'keyword'     => 'best seo plugin plugin',
				'position'    => '2.38',
				'difference'  => [
					'ctr'         => '0.06',
					'decay'       => 836,
					'clicks'      => 29,
					'position'    => '0.20',
					'impressions' => 807
				],
				'impressions' => 13837
			],
			[
				'ctr'         => '1.52',
				'clicks'      => 326,
				'keyword'     => 'best seo plugin pack',
				'position'    => '4.14',
				'difference'  => [
					'ctr'         => '-0.19',
					'decay'       => -1590,
					'clicks'      => -66,
					'position'    => '0.64',
					'impressions' => -1524
				],
				'impressions' => 21450
			],
			[
				'ctr'         => '6.70',
				'clicks'      => 264,
				'keyword'     => 'youtube title analyzer aioseo',
				'position'    => '7.19',
				'difference'  => [
					'ctr'         => '4.73',
					'decay'       => 3842,
					'clicks'      => 257,
					'position'    => '-4.18',
					'impressions' => 3585
				],
				'impressions' => 3940
			]
		];

		return [
			'paginated'             => [
				'rows'    => $keywordsRows,
				'totals'  => [
					'page'  => 1,
					'pages' => 2500,
					'total' => 25000
				],
				'filters' => [
					[
						'slug'   => 'all',
						'name'   => 'All',
						'active' => true
					],
					[
						'slug'   => 'topLosing',
						'name'   => 'Top Losing',
						'active' => false
					],
					[
						'slug'   => 'topWinning',
						'name'   => 'Top Winning',
						'active' => false
					]
				]
			],
			'topLosing'             => [],
			'topWinning'            => [],
			'topKeywords'           => $keywordsRows,
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
	 * Returns the Content Rankings data.
	 *
	 * @since 4.3.6
	 *
	 * @param  array $dateRange The date range.
	 * @return array            The Content Rankings data.
	 */
	protected function getContentRankingsData( $dateRange = [] ) { // phpcs:ignore VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
		return [
			'paginated' => [
				'rows'              => [
					'/'                       => [
						'points'           => [
							'2022-04' => 13655,
							'2022-05' => 12683,
							'2022-06' => 13923,
							'2022-07' => 13031,
							'2022-08' => 10978,
							'2022-09' => 9726,
							'2022-10' => 13943,
							'2022-11' => 21813,
							'2022-12' => 11163,
							'2023-01' => 4442,
							'2023-02' => 4798,
							'2023-03' => 5405
						],
						'page'             => '/',
						'peak'             => 21813,
						'decayPercent'     => 75,
						'decay'            => 16407,
						'recovering'       => false,
						'context'          => [
							'lastUpdated' => 'December 6, 2021'
						],
						'objectTitle'      => 'Homepage',
						'objectType'       => 'post',
						'inspectionResult' => $this->getInspectionResult(),
						'objectId'         => 0
					],
					'/high-ranking-page/'     => [
						'points'           => [
							'2022-04' => 18426,
							'2022-05' => 18435,
							'2022-06' => 19764,
							'2022-07' => 14769,
							'2022-08' => 6486,
							'2022-09' => 11984,
							'2022-10' => 11539,
							'2022-11' => 9939,
							'2022-12' => 5340,
							'2023-01' => 3965,
							'2023-02' => 3799,
							'2023-03' => 5440
						],
						'page'             => '/high-ranking-page/',
						'peak'             => 19764,
						'decayPercent'     => 72,
						'decay'            => 14323,
						'recovering'       => false,
						'context'          => [
							'lastUpdated' => 'November 17, 2022'
						],
						'objectTitle'      => 'High Ranking Page',
						'objectType'       => 'post',
						'inspectionResult' => $this->getInspectionResult(),
						'objectId'         => 0
					],
					'/pricing/'               => [
						'points'           => [
							'2022-04' => 5356,
							'2022-05' => 5425,
							'2022-06' => 5165,
							'2022-07' => 5479,
							'2022-08' => 4995,
							'2022-09' => 4466,
							'2022-10' => 4545,
							'2022-11' => 5361,
							'2022-12' => 3092,
							'2023-01' => 1948,
							'2023-02' => 1630,
							'2023-03' => 2341
						],
						'page'             => '/pricing/',
						'peak'             => 5479,
						'decayPercent'     => 57,
						'decay'            => 3137,
						'recovering'       => false,
						'context'          => [
							'lastUpdated' => 'December 8, 2021'
						],
						'objectTitle'      => 'Pricing',
						'objectType'       => 'post',
						'inspectionResult' => $this->getInspectionResult(),
						'objectId'         => 0
					],
					'/features-and-benefits/' => [
						'points'           => [
							'2022-04' => 1272,
							'2022-05' => 4151,
							'2022-06' => 6953,
							'2022-07' => 7785,
							'2022-08' => 4177,
							'2022-09' => 3378,
							'2022-10' => 2553,
							'2022-11' => 3971,
							'2022-12' => 2143,
							'2023-01' => 2335,
							'2023-02' => 1666,
							'2023-03' => 4892
						],
						'page'             => '/features-and-benefits/',
						'peak'             => 7785,
						'decayPercent'     => 37,
						'decay'            => 2893,
						'recovering'       => false,
						'context'          => [
							'lastUpdated' => 'February 7, 2022'
						],
						'objectTitle'      => 'Features and Benefits',
						'objectType'       => 'post',
						'inspectionResult' => $this->getInspectionResult(),
						'objectId'         => 0
					],
					'/documentation/'         => [
						'points'           => [
							'2022-04' => 594,
							'2022-05' => 385,
							'2022-06' => 337,
							'2022-07' => 378,
							'2022-08' => 714,
							'2022-09' => 2637,
							'2022-10' => 2831,
							'2022-11' => 2907,
							'2022-12' => 1851,
							'2023-01' => 277,
							'2023-02' => 226,
							'2023-03' => 175
						],
						'page'             => '/documentation/',
						'peak'             => 2907,
						'decayPercent'     => 93,
						'decay'            => 2731,
						'recovering'       => false,
						'context'          => [
							'lastUpdated' => 'January 7, 2022'
						],
						'objectTitle'      => 'Documentation',
						'objectType'       => 'post',
						'inspectionResult' => $this->getInspectionResult(),
						'objectId'         => 0
					],
					'/blog/'                  => [
						'points'           => [
							'2022-04' => 2956,
							'2022-05' => 2363,
							'2022-06' => 2347,
							'2022-07' => 2154,
							'2022-08' => 2604,
							'2022-09' => 1995,
							'2022-10' => 1528,
							'2022-11' => 1578,
							'2022-12' => 1458,
							'2023-01' => 927,
							'2023-02' => 629,
							'2023-03' => 592
						],
						'page'             => '/blog/',
						'peak'             => 2956,
						'decayPercent'     => 79,
						'decay'            => 2363,
						'recovering'       => false,
						'context'          => [
							'lastUpdated' => 'April 21, 2022'
						],
						'objectTitle'      => 'Blog',
						'objectType'       => 'post',
						'inspectionResult' => $this->getInspectionResult(),
						'objectId'         => 0
					],
					'/blog/my-best-content/'  => [
						'points'           => [
							'2022-04' => 1889,
							'2022-05' => 1714,
							'2022-06' => 2849,
							'2022-07' => 4175,
							'2022-08' => 5343,
							'2022-09' => 6360,
							'2022-10' => 6492,
							'2022-11' => 6955,
							'2022-12' => 6930,
							'2023-01' => 5880,
							'2023-02' => 5211,
							'2023-03' => 4683
						],
						'page'             => '/blog/my-best-content/',
						'peak'             => 6955,
						'decayPercent'     => 32,
						'decay'            => 2272,
						'recovering'       => false,
						'context'          => [
							'lastUpdated' => 'April 22, 2022'
						],
						'objectTitle'      => 'My Best Content',
						'objectType'       => 'post',
						'inspectionResult' => $this->getInspectionResult(),
						'objectId'         => 0
					],
					'/contact-us/'            => [
						'points'           => [
							'2022-04' => 3668,
							'2022-05' => 3699,
							'2022-06' => 4934,
							'2022-07' => 5488,
							'2022-08' => 5092,
							'2022-09' => 5526,
							'2022-10' => 4694,
							'2022-11' => 4791,
							'2022-12' => 3989,
							'2023-01' => 4089,
							'2023-02' => 4189,
							'2023-03' => 4289
						],
						'page'             => '/contact-us/',
						'peak'             => 5526,
						'decayPercent'     => 34,
						'decay'            => 1907,
						'recovering'       => true,
						'context'          => [
							'lastUpdated' => 'January 28, 2022'
						],
						'objectTitle'      => 'Contact Us',
						'objectType'       => 'post',
						'inspectionResult' => $this->getInspectionResult(),
						'objectId'         => 0
					],
					'/support/'               => [
						'points'           => [
							'2022-04' => 2715,
							'2022-05' => 2909,
							'2022-06' => 2981,
							'2022-07' => 2988,
							'2022-08' => 2586,
							'2022-09' => 2592,
							'2022-10' => 2391,
							'2022-11' => 2446,
							'2022-12' => 2045,
							'2023-01' => 1239,
							'2023-02' => 1077,
							'2023-03' => 1198
						],
						'page'             => '/support/',
						'peak'             => 2988,
						'decayPercent'     => 59,
						'decay'            => 1789,
						'recovering'       => false,
						'context'          => [
							'lastUpdated' => 'February 21, 2021'
						],
						'objectTitle'      => 'Support',
						'objectType'       => 'post',
						'inspectionResult' => $this->getInspectionResult(),
						'objectId'         => 0
					],
					'/blog/top-10-contents/'  => [
						'points'           => [
							'2022-04' => 1889,
							'2022-05' => 1714,
							'2022-06' => 2849,
							'2022-07' => 4175,
							'2022-08' => 5343,
							'2022-09' => 6360,
							'2022-10' => 6492,
							'2022-11' => 6955,
							'2022-12' => 6930,
							'2023-01' => 5880,
							'2023-02' => 5211,
							'2023-03' => 4683
						],
						'page'             => '/blog/top-10-contents/',
						'peak'             => 6955,
						'decayPercent'     => 32,
						'decay'            => 2272,
						'recovering'       => false,
						'context'          => [
							'lastUpdated' => 'October 14, 2022'
						],
						'objectTitle'      => 'Top 10 Contents',
						'objectType'       => 'post',
						'inspectionResult' => $this->getInspectionResult(),
						'objectId'         => 0
					],
				],
				'totals'            => [
					'page'  => 1,
					'pages' => 215,
					'total' => 4296
				],
				'additionalFilters' => [
					[
						'name'    => 'postType',
						'options' => [
							[
								'label' => __( 'All Content Types', 'all-in-one-seo-pack' ),
								'value' => ''
							]
						]
					]
				]
			]
		];
	}

	/**
	 * Get minimum required values for the inspection result.
	 *
	 * @since 4.5.0
	 *
	 * @return array The inspection result.
	 */
	private function getInspectionResult() {
		$verdicts = [
			'PASS',
			'FAIL',
			'NEUTRAL'
		];

		return [
			'indexStatusResult' => [
				'verdict' => $verdicts[ array_rand( $verdicts ) ],
			]
		];
	}

	/**
	 * Clears the Search Statistics cache.
	 *
	 * @since   4.5.0
	 * @version 4.6.2 Moved from Pro to Common.
	 *
	 * @return void
	 */
	public function clearCache() {
		aioseo()->core->cache->clearPrefix( 'aioseo_search_statistics_' );
		aioseo()->core->cache->clearPrefix( 'search_statistics_' );
	}

	/**
	 * Returns all scheduled Search Statistics related actions.
	 *
	 * @since 4.6.2
	 *
	 * @return array The Search Statistics actions.
	 */
	protected function getActionSchedulerActions() {
		return [
			$this->site->action,
			$this->sitemap->action
		];
	}

	/**
	 * Cancels all scheduled Search Statistics related actions.
	 *
	 * @since   4.3.3
	 * @version 4.6.2 Moved from Pro to Common.
	 *
	 * @return void
	 */
	public function cancelActions() {
		foreach ( $this->getActionSchedulerActions() as $actionName ) {
			as_unschedule_all_actions( $actionName );
		}
	}
}