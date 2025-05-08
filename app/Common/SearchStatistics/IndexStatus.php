<?php
namespace AIOSEO\Plugin\Common\SearchStatistics;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Index Status class.
 *
 * @since 4.8.2
 */
class IndexStatus {
	/**
	 * Retrieves the overview data.
	 *
	 * @since 4.8.2
	 *
	 * @return array The overview data.
	 */
	public function getOverview() {
		$data = [
			'post' => [
				'results' => [
					[
						'count'         => 164,
						'coverageState' => 'Submitted and Indexed', // No need to translate this. It's translated on the front-end.
					],
					[
						'count'         => 112,
						'coverageState' => 'Discovered - Currently Not Indexed',
					],
					[
						'count'         => 44,
						'coverageState' => 'Crawled - Currently Not Indexed',
					],
					[
						'count'         => 8,
						'coverageState' => 'URL is unknown to Google',
					]
				]
			]
		];

		$data['post']['total'] = array_sum( array_column( $data['post']['results'], 'count' ) );

		return $data;
	}

	/**
	 * Retrieves all the objects, formatted.
	 *
	 * @since 4.8.2
	 *
	 * @return array The formatted objects.
	 */
	public function getFormattedObjects() {
		$siteUrl = aioseo()->helpers->getSiteUrl();

		$rows = [
			[
				'objectId'             => 4,
				'objectTitle'          => 'Homepage',
				'verdict'              => 'PASS',
				'coverageState'        => 'Submitted and Indexed',
				'robotsTxtState'       => 'ALLOWED',
				'indexingState'        => 'INDEXING_ALLOWED',
				'pageFetchState'       => 'SUCCESSFUL',
				'crawledAs'            => 'MOBILE',
				'lastCrawlTime'        => aioseo()->helpers->dateToWpFormat( '2025-01-05 13:54:00' ),
				'userCanonical'        => $siteUrl,
				'googleCanonical'      => $siteUrl,
				'sitemap'              => [
					aioseo()->sitemap->helpers->getUrl( 'general' )
				],
				'referringUrls'        => [],
				'richResultsResult'    => [
					'detectedItems' => [
						[
							'richResultType' => 'Breadcrumbs',
							'items'          => [
								[
									'name' => 'Unnamed item'
								]
							]
						],
						[
							'richResultType' => 'FAQ',
							'items'          => [
								[
									'name' => 'Unnamed item'
								]
							]
						]
					]
				],
				'inspectionResultLink' => '#',
				'richResultsTestLink'  => '#'
			],
			[
				'objectId'             => 6,
				'objectTitle'          => 'About',
				'verdict'              => 'PASS',
				'coverageState'        => 'Submitted and Indexed',
				'robotsTxtState'       => 'ALLOWED',
				'indexingState'        => 'INDEXING_ALLOWED',
				'pageFetchState'       => 'SUCCESSFUL',
				'crawledAs'            => 'MOBILE',
				'lastCrawlTime'        => aioseo()->helpers->dateToWpFormat( '2025-01-06 09:22:00' ),
				'userCanonical'        => $siteUrl . '/about',
				'googleCanonical'      => $siteUrl . '/about',
				'sitemap'              => [
					aioseo()->sitemap->helpers->getUrl( 'general' )
				],
				'referringUrls'        => [
					$siteUrl
				],
				'richResultsResult'    => [
					'detectedItems' => [
						[
							'richResultType' => 'Breadcrumbs',
							'items'          => [
								[
									'name' => 'Unnamed item'
								]
							]
						]
					]
				],
				'inspectionResultLink' => '#',
				'richResultsTestLink'  => '#'
			],
			[
				'objectId'             => 1,
				'objectTitle'          => 'Contact Us',
				'verdict'              => 'PASS',
				'coverageState'        => 'Submitted and Indexed',
				'robotsTxtState'       => 'ALLOWED',
				'indexingState'        => 'INDEXING_ALLOWED',
				'pageFetchState'       => 'SUCCESSFUL',
				'crawledAs'            => 'DESKTOP',
				'lastCrawlTime'        => aioseo()->helpers->dateToWpFormat( '2025-01-02 16:47:00' ),
				'userCanonical'        => $siteUrl . '/contact-us',
				'googleCanonical'      => $siteUrl . '/contact-us',
				'sitemap'              => [
					aioseo()->sitemap->helpers->getUrl( 'general' )
				],
				'referringUrls'        => [
					$siteUrl
				],
				'richResultsResult'    => [
					'detectedItems' => [
						[
							'richResultType' => 'Breadcrumbs',
							'items'          => [
								[
									'name' => 'Unnamed item'
								]
							]
						],
						[
							'richResultType' => 'FAQ',
							'items'          => [
								[
									'name' => 'Unnamed item'
								]
							]
						]
					]
				],
				'inspectionResultLink' => '#',
				'richResultsTestLink'  => '#'
			],
			[
				'objectId'             => 2,
				'objectTitle'          => 'Pricing',
				'verdict'              => 'NEUTRAL',
				'coverageState'        => 'Crawled - Currently Not Indexed',
				'robotsTxtState'       => 'DISALLOWED',
				'indexingState'        => 'BLOCKED_BY_META_TAG',
				'pageFetchState'       => 'SUCCESSFUL',
				'crawledAs'            => 'DESKTOP',
				'lastCrawlTime'        => aioseo()->helpers->dateToWpFormat( '2024-01-15 11:00:00' ),
				'userCanonical'        => $siteUrl . '/pricing',
				'googleCanonical'      => $siteUrl . '/pricing',
				'sitemap'              => [
					aioseo()->sitemap->helpers->getUrl( 'general' )
				],
				'referringUrls'        => [
					$siteUrl
				],
				'richResultsResult'    => [
					'detectedItems' => [
						[
							'richResultType' => 'Breadcrumbs',
							'items'          => [
								[
									'name' => 'Unnamed item'
								]
							]
						],
						[
							'richResultType' => 'Product snippet',
							'items'          => [
								[
									'name'   => 'All in One SEO (AIOSEO)',
									'issues' => [
										[
											'issueMessage' => 'Missing field "priceValidUntil"',
											'severity'     => 'WARNING'
										]
									]
								]
							]
						]
					]
				],
				'inspectionResultLink' => '#',
				'richResultsTestLink'  => '#'
			],
			[
				'objectId'             => 3,
				'objectTitle'          => 'Blog',
				'verdict'              => 'PASS',
				'coverageState'        => 'Submitted and Indexed',
				'robotsTxtState'       => 'ALLOWED',
				'indexingState'        => 'INDEXED',
				'pageFetchState'       => 'SUCCESSFUL',
				'crawledAs'            => 'MOBILE',
				'lastCrawlTime'        => aioseo()->helpers->dateToWpFormat( '2024-03-01 08:00:00' ),
				'userCanonical'        => $siteUrl . '/blog',
				'googleCanonical'      => $siteUrl . '/blog',
				'sitemap'              => [
					aioseo()->sitemap->helpers->getUrl( 'general' )
				],
				'referringUrls'        => [
					$siteUrl
				],
				'inspectionResultLink' => '#',
				'richResultsTestLink'  => '#'
			],
		];

		return [
			'paginated' => [
				'rows'   => $rows,
				'totals' => [
					'total' => count( $rows ),
					'pages' => 1,
					'page'  => 1
				]
			]
		];
	}

	/**
	 * Returns the data for Vue.
	 *
	 * @since 4.8.2
	 *
	 * @return array The data for Vue.
	 */
	public function getVueData() {
		return [
			'objects'  => $this->getFormattedObjects(),
			'overview' => $this->getOverview(),
			'options'  => $this->getUiOptions()
		];
	}

	/**
	 * Retrieves options ideally only for Vue usage on the front-end.
	 *
	 * @since 4.8.2
	 *
	 * @return array The options.
	 */
	protected function getUiOptions() {
		$postTypeOptions = [
			[
				'label' => __( 'All Post Types', 'all-in-one-seo-pack' ),
				'value' => ''
			],
			[
				'label' => __( 'Post', 'all-in-one-seo-pack' ),
				'value' => 'post'
			],
			[
				'label' => __( 'Page', 'all-in-one-seo-pack' ),
				'value' => 'page'
			]
		];

		$statusOptions = [
			[
				'label' => __( 'Status (All)', 'all-in-one-seo-pack' ),
				'value' => ''
			],
			[
				'label' => __( 'Indexed', 'all-in-one-seo-pack' ),
				'value' => 'submitted',
				'color' => '#00AA63',
			],
			[
				'label' => __( 'Crawled, Not Indexed', 'all-in-one-seo-pack' ),
				'value' => 'crawled',
				'color' => '#F18200',
			],
			[
				'label' => __( 'Discovered, Not Indexed', 'all-in-one-seo-pack' ),
				'value' => 'discovered',
				'color' => '#005AE0',
			],
			[
				'label' => __( 'Other, Not Indexed', 'all-in-one-seo-pack' ),
				'value' => 'unknown|excluded|invalid|error',
				'color' => '#DF2A4A',
			],
			[
				'label' => __( 'No Results Yet', 'all-in-one-seo-pack' ),
				'value' => 'empty',
				'color' => '#999999',
			]
		];

		$robotsTxtStateOptions = [
			[
				'label' => __( 'Robots.txt (All)', 'all-in-one-seo-pack' ),
				'value' => ''
			],
			[
				'label' => __( 'Allowed', 'all-in-one-seo-pack' ),
				'value' => 'ALLOWED'
			],
			[
				'label' => __( 'Blocked', 'all-in-one-seo-pack' ),
				'value' => 'DISALLOWED'
			]
		];

		$crawledAsOptions = [
			[
				'label' => __( 'Crawled As (All)', 'all-in-one-seo-pack' ),
				'value' => ''
			],
			[
				'label' => __( 'Desktop', 'all-in-one-seo-pack' ),
				'value' => 'DESKTOP'
			],
			[
				'label' => __( 'Mobile', 'all-in-one-seo-pack' ),
				'value' => 'MOBILE'
			]
		];

		$pageFetchStateOptions = [
			[
				'label' => __( 'Page Fetch (All)', 'all-in-one-seo-pack' ),
				'value' => ''
			],
			[
				'label' => __( 'Successful', 'all-in-one-seo-pack' ),
				'value' => 'SUCCESSFUL'
			],
			[
				'label' => __( 'Error', 'all-in-one-seo-pack' ),
				'value' => 'SOFT_404,BLOCKED_ROBOTS_TXT,NOT_FOUND,ACCESS_DENIED,SERVER_ERROR,REDIRECT_ERROR,ACCESS_FORBIDDEN,BLOCKED_4XX,INTERNAL_CRAWL_ERROR,INVALID_URL'
			]
		];

		$additionalFilters = [
			'postTypeOptions'       => [
				'name'    => 'postType',
				'options' => $postTypeOptions
			],
			'statusOptions'         => [
				'name'    => 'status',
				'options' => $statusOptions
			],
			'robotsTxtStateOptions' => [
				'name'    => 'robotsTxtState',
				'options' => $robotsTxtStateOptions
			],
			'pageFetchStateOptions' => [
				'name'    => 'pageFetchState',
				'options' => $pageFetchStateOptions
			],
			'crawledAsOptions'      => [
				'name'    => 'crawledAs',
				'options' => $crawledAsOptions
			],
		];

		return [
			'table' => [
				'additionalFilters' => $additionalFilters
			]
		];
	}
}