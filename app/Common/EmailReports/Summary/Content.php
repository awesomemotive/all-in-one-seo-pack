<?php

namespace AIOSEO\Plugin\Common\EmailReports\Summary;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AIOSEO\Plugin\Common\Models;

/**
 * Summary content class.
 *
 * @since 4.7.2
 */
class Content {
	/**
	 * The date range data.
	 *
	 * @since 4.7.2
	 *
	 * @var array
	 */
	public $dateRange;

	/**
	 * The SEO Statistics data.
	 *
	 * @since 4.7.2
	 *
	 * @var array
	 */
	private $seoStatistics = [];

	/**
	 * The Keywords data.
	 *
	 * @since 4.7.2
	 *
	 * @var array
	 */
	private $keywords = [];

	/**
	 * The Search Statistics page URL.
	 *
	 * @since 4.7.2
	 *
	 * @var string
	 */
	public $searchStatisticsUrl;

	/**
	 * The featured image placeholder URL.
	 *
	 * @since 4.7.3
	 *
	 * @var string
	 */
	public $featuredImagePlaceholder = 'https://static.aioseo.io/report/ste/featured-image-placeholder.png';

	/**
	 * Class constructor.
	 *
	 * @since 4.7.2
	 *
	 * @param  array $dateRange The date range data.
	 * @return void
	 */
	public function __construct( $dateRange ) {
		$this->dateRange           = $dateRange;
		$this->searchStatisticsUrl = admin_url( 'admin.php?page=aioseo-search-statistics' );

		$this->setSeoStatistics();
		$this->setKeywords();
	}

	/**
	 * Sets the SEO Statistics data.
	 *
	 * @since 4.7.2
	 *
	 * @return void
	 */
	private function setSeoStatistics() {
		try {
			$seoStatistics = aioseo()->searchStatistics->getSeoStatisticsData( [
				'startDate' => gmdate( 'Y-m-d', $this->dateRange['startDateRaw'] ),
				'endDate'   => gmdate( 'Y-m-d', $this->dateRange['endDateRaw'] ),
				'orderBy'   => 'clicks',
				'orderDir'  => 'desc',
				'limit'     => '5',
				'offset'    => '0',
				'filter'    => 'all',
			] );

			if ( empty( $seoStatistics['data'] ) ) {
				return;
			}

			$this->seoStatistics = $seoStatistics['data'];
		} catch ( \Exception $e ) {
			// Do nothing.
		}
	}

	/**
	 * Sets the Keywords data.
	 *
	 * @since 4.7.2
	 *
	 * @return void
	 */
	private function setKeywords() {
		try {
			$keywords = aioseo()->searchStatistics->getKeywordsData( [
				'startDate' => gmdate( 'Y-m-d', $this->dateRange['startDateRaw'] ),
				'endDate'   => gmdate( 'Y-m-d', $this->dateRange['endDateRaw'] ),
				'orderBy'   => 'clicks',
				'orderDir'  => 'desc',
				'limit'     => '5',
				'offset'    => '0',
				'filter'    => 'all',
			] );

			if ( empty( $keywords['data'] ) ) {
				return;
			}

			$this->keywords = $keywords['data'];
		} catch ( \Exception $e ) {
			// Do nothing.
		}
	}

	/**
	 * Retrieves the content performance data.
	 *
	 * @since 4.7.2
	 *
	 * @return array The content performance data or an empty array.
	 */
	public function getPostsStatistics() {
		if ( ! $this->seoStatistics ) {
			return [];
		}

		$result = [
			'winning' => [
				'url'   => add_query_arg( [
					'aioseo-scroll' => 'aioseo-search-statistics-post-table',
					'aioseo-tab'    => 'seo-statistics',
					'table-filter'  => 'TopWinningPages'
				], $this->searchStatisticsUrl ),
				'items' => []
			],
			'losing'  => [
				'url'   => add_query_arg( [
					'aioseo-scroll' => 'aioseo-search-statistics-post-table',
					'aioseo-tab'    => 'seo-statistics',
					'table-filter'  => 'TopLosingPages'
				], $this->searchStatisticsUrl ),
				'items' => []
			]
		];

		foreach ( array_slice( $this->seoStatistics['pages']['topWinning']['rows'], 0, 3 ) as $row ) {
			$postId                       = $row['objectId'] ?? 0;
			$result['winning']['items'][] = [
				'title'      => $row['objectTitle'],
				'url'        => get_permalink( $postId ),
				'tru_seo'    => aioseo()->helpers->isTruSeoEligible( $postId ) ? $this->parseSeoScore( $row['seoScore'] ?? 0 ) : [],
				'clicks'     => $this->parseClicks( $row['clicks'] ),
				'difference' => [
					'clicks' => $this->parseDifference( $row['difference']['clicks'] ?? '' ),
				]
			];
		}

		$result['winning']['show_tru_seo'] = ! empty( array_filter( array_column( $result['winning']['items'], 'tru_seo' ) ) );

		foreach ( array_slice( $this->seoStatistics['pages']['topLosing']['rows'], 0, 3 ) as $row ) {
			$postId                      = $row['objectId'] ?? 0;
			$result['losing']['items'][] = [
				'title'      => $row['objectTitle'],
				'url'        => get_permalink( $postId ),
				'tru_seo'    => aioseo()->helpers->isTruSeoEligible( $postId ) ? $this->parseSeoScore( $row['seoScore'] ?? 0 ) : [],
				'clicks'     => $this->parseClicks( $row['clicks'] ),
				'difference' => [
					'clicks' => $this->parseDifference( $row['difference']['clicks'] ?? '' ),
				]
			];
		}

		$result['losing']['show_tru_seo'] = ! empty( array_filter( array_column( $result['losing']['items'], 'tru_seo' ) ) );

		return $result;
	}

	/**
	 * Retrieves the milestones data.
	 *
	 * @since 4.7.2
	 *
	 * @return array The milestones data or an empty array.
	 */
	public function getMilestones() { // phpcs:ignore Generic.Files.LineLength.MaxExceeded
		$milestones = [];
		if ( ! $this->seoStatistics ) {
			return $milestones;
		}

		$currentData = [
			'impressions' => $this->seoStatistics['statistics']['impressions'] ?? null,
			'clicks'      => $this->seoStatistics['statistics']['clicks'] ?? null,
			'ctr'         => $this->seoStatistics['statistics']['ctr'] ?? null,
			'keywords'    => $this->seoStatistics['statistics']['keywords'] ?? null,
		];
		$difference  = [
			'impressions' => $this->seoStatistics['statistics']['difference']['impressions'] ?? null,
			'clicks'      => $this->seoStatistics['statistics']['difference']['clicks'] ?? null,
			'ctr'         => $this->seoStatistics['statistics']['difference']['ctr'] ?? null,
			'keywords'    => $this->seoStatistics['statistics']['difference']['keywords'] ?? null,
		];

		if ( is_numeric( $currentData['impressions'] ) && is_numeric( $difference['impressions'] ) ) {
			$intDifference = intval( $difference['impressions'] );
			$message       = esc_html__( 'Your site has received the same number of impressions compared to the previous period.', 'all-in-one-seo-pack' );

			if ( $intDifference > 0 ) {
				// Translators: 1 - The number of impressions, 2 - The percentage increase.
				$message = esc_html__( 'Your site has received %1$s more impressions compared to the previous period, which is a %2$s increase.', 'all-in-one-seo-pack' );
			}

			if ( $intDifference < 0 ) {
				// Translators: 1 - The number of impressions, 2 - The percentage increase.
				$message = esc_html__( 'Your site has received %1$s fewer impressions compared to the previous period, which is a %2$s decrease.', 'all-in-one-seo-pack' );
			}

			if ( false !== strpos( $message, '%1' ) ) {
				$percentageDiff = 0 === absint( $currentData['impressions'] )
					? 100
					: round( ( absint( $intDifference ) / absint( $currentData['impressions'] ) ) * 100, 2 );
				$percentageDiff = false !== strpos( $percentageDiff, '.' )
					? number_format_i18n( $percentageDiff, count( explode( '.', $percentageDiff ) ) )
					: $percentageDiff;
				$message        = sprintf(
					$message,
					'<strong>' . aioseo()->helpers->compactNumber( absint( $intDifference ) ) . '</strong>',
					'<strong>' . $percentageDiff . '%</strong>'
				);
			}

			$milestones[] = [
				'message'    => $message,
				'background' => '#f0f6ff',
				'color'      => '#004F9D',
				'icon'       => 'icon-milestone-impressions'
			];
		}

		if ( is_numeric( $currentData['clicks'] ) && is_numeric( $difference['clicks'] ) ) {
			$intDifference = intval( $difference['clicks'] );
			$message       = esc_html__( 'Your site has received the same number of clicks compared to the previous period.', 'all-in-one-seo-pack' );

			if ( $intDifference > 0 ) {
				// Translators: 1 - The number of clicks, 2 - The percentage increase.
				$message = esc_html__( 'Your site has received %1$s more clicks compared to the previous period, which is a %2$s increase.', 'all-in-one-seo-pack' );
			}

			if ( $intDifference < 0 ) {
				// Translators: 1 - The number of clicks, 2 - The percentage increase.
				$message = esc_html__( 'Your site has received %1$s fewer clicks compared to the previous period, which is a %2$s decrease.', 'all-in-one-seo-pack' );
			}

			if ( false !== strpos( $message, '%1' ) ) {
				$percentageDiff = 0 === absint( $currentData['clicks'] )
					? 100
					: round( ( absint( $intDifference ) / absint( $currentData['clicks'] ) ) * 100, 2 );
				$percentageDiff = false !== strpos( $percentageDiff, '.' )
					? number_format_i18n( $percentageDiff, count( explode( '.', $percentageDiff ) ) )
					: $percentageDiff;
				$message        = sprintf(
					$message,
					'<strong>' . aioseo()->helpers->compactNumber( absint( $intDifference ) ) . '</strong>',
					'<strong>' . $percentageDiff . '%</strong>'
				);
			}

			$milestones[] = [
				'message'    => $message,
				'background' => '#ecfdf5',
				'color'      => '#077647',
				'icon'       => 'icon-milestone-clicks'
			];
		}

		if ( is_numeric( $currentData['ctr'] ) && is_numeric( $difference['ctr'] ) ) {
			$intDifference = floatval( $difference['ctr'] );
			$message       = esc_html__( 'Your site has the same CTR compared to the previous period.', 'all-in-one-seo-pack' );

			if ( $intDifference > 0 ) {
				// Translators: 1 - The CTR.
				$message = esc_html__( 'Your site has a %1$s higher CTR compared to the previous period.', 'all-in-one-seo-pack' );
			}

			if ( $intDifference < 0 ) {
				// Translators: 1 - The CTR.
				$message = esc_html__( 'Your site has a %1$s lower CTR compared to the previous period.', 'all-in-one-seo-pack' );
			}

			if ( false !== strpos( $message, '%1' ) ) {
				$message = sprintf(
					$message,
					'<strong>' . number_format_i18n( abs( $intDifference ), count( explode( '.', $intDifference ) ) ) . '%</strong>'
				);
			}

			$milestones[] = [
				'message'    => $message,
				'background' => '#fffbeb',
				'color'      => '#be6903',
				'icon'       => 'icon-milestone-ctr'
			];
		}

		if ( is_numeric( $currentData['keywords'] ) && is_numeric( $difference['keywords'] ) ) {
			$intDifference = intval( $difference['keywords'] );
			$message       = esc_html__( 'Your site ranked for the same number of keywords compared to the previous period.', 'all-in-one-seo-pack' );

			if ( $intDifference > 0 ) {
				// Translators: 1 - The number of keywords, 2 - The percentage increase.
				$message = esc_html__( 'Your site ranked for %1$s more keywords compared to the previous period, which is a %2$s increase.', 'all-in-one-seo-pack' );
			}

			if ( $intDifference < 0 ) {
				// Translators: 1 - The number of keywords, 2 - The percentage increase.
				$message = esc_html__( 'Your site ranked for %1$s fewer keywords compared to the previous period, which is a %2$s decrease.', 'all-in-one-seo-pack' );
			}

			if ( false !== strpos( $message, '%1' ) ) {
				$percentageDiff = 0 === absint( $currentData['keywords'] )
					? 100
					: round( ( absint( $intDifference ) / absint( $currentData['keywords'] ) ) * 100, 2 );
				$percentageDiff = false !== strpos( $percentageDiff, '.' )
					? number_format_i18n( $percentageDiff, count( explode( '.', $percentageDiff ) ) )
					: $percentageDiff;
				$message        = sprintf(
					$message,
					'<strong>' . aioseo()->helpers->compactNumber( absint( $intDifference ) ) . '</strong>',
					'<strong>' . $percentageDiff . '%</strong>'
				);
			}

			$milestones[] = [
				'message'    => $message,
				'background' => '#fef2f2',
				'color'      => '#ab2039',
				'icon'       => 'icon-milestone-keywords'
			];
		}

		return $milestones;
	}

	/**
	 * Retrieves the keyword performance data.
	 *
	 * @since 4.7.2
	 *
	 * @return array The keyword performance data or an empty array.
	 */
	public function getKeywords() {
		if ( ! $this->keywords ) {
			return [];
		}

		$result = [
			'winning' => [
				'url'   => add_query_arg( [
					'aioseo-scroll' => 'aioseo-search-statistics-keywords-table',
					'aioseo-tab'    => 'keyword-rank-tracker',
					'tab'           => 'AllKeywords',
					'table-filter'  => 'TopWinningKeywords'
				], $this->searchStatisticsUrl ),
				'items' => []
			],
			'losing'  => [
				'url'   => add_query_arg( [
					'aioseo-scroll' => 'aioseo-search-statistics-keywords-table',
					'aioseo-tab'    => 'keyword-rank-tracker',
					'tab'           => 'AllKeywords',
					'table-filter'  => 'TopLosingKeywords'
				], $this->searchStatisticsUrl ),
				'items' => []
			]
		];

		foreach ( array_slice( $this->keywords['topWinning'], 0, 3 ) as $row ) {
			$result['winning']['items'][] = [
				'title'      => $row['keyword'],
				'clicks'     => $this->parseClicks( $row['clicks'] ),
				'difference' => [
					'clicks' => $this->parseDifference( $row['difference']['clicks'] ?? '' ),
				]
			];
		}

		foreach ( array_slice( $this->keywords['topLosing'], 0, 3 ) as $row ) {
			$result['losing']['items'][] = [
				'title'      => $row['keyword'],
				'clicks'     => $this->parseClicks( $row['clicks'] ),
				'difference' => [
					'clicks' => $this->parseDifference( $row['difference']['clicks'] ?? '' ),
				]
			];
		}

		return $result;
	}

	/**
	 * Retrieves the posts data.
	 *
	 * @since 4.7.2
	 *
	 * @return array The posts' data.
	 */
	public function getAioPosts() {
		$result = [
			'publish'  => [],
			'optimize' => [],
			'cta'      => [
				'text' => esc_html__( 'Create New Post', 'all-in-one-seo-pack' ),
				'url'  => admin_url( 'post-new.php' )
			],
		];

		// 1. Retrieve the published posts.
		$publishPosts = aioseo()->core->db
			->start( 'posts as wp' )
			->select( 'wp.ID, wp.post_title, aio.seo_score' )
			->join( 'aioseo_posts as aio', 'aio.post_id = wp.ID', 'INNER' )
			->whereIn( 'wp.post_type', [ 'post' ] )
			->whereIn( 'wp.post_status', [ 'publish' ] )
			->orderBy( 'wp.post_date DESC' )
			->limit( 5 )
			->run()
			->result();

		if ( $publishPosts ) {
			$items             = $this->parsePosts( $publishPosts );
			$result['publish'] = [
				'url'          => admin_url( 'edit.php?post_status=publish&post_type=post' ),
				'items'        => $items,
				'show_stats'   => ! empty( array_filter( array_column( $items, 'stats' ) ) ),
				'show_tru_seo' => ! empty( array_filter( array_column( $items, 'tru_seo' ) ) ),
			];
		}

		// 2. Retrieve the posts to optimize.
		$optimizePosts = aioseo()->searchStatistics->getContentRankingsData( [
			'endDate'  => gmdate( 'Y-m-d', $this->dateRange['endDateRaw'] ),
			'orderBy'  => 'decayPercent',
			'orderDir' => 'asc',
			'limit'    => '3',
			'offset'   => '0',
			'filter'   => 'all',
		] );

		if ( is_array( $optimizePosts['data']['paginated']['rows'] ?? '' ) ) {
			$items = [];
			foreach ( array_slice( $optimizePosts['data']['paginated']['rows'], 0, 3 ) as $i => $row ) {
				$postId      = $row['objectId'] ?? 0;
				$items[ $i ] = [
					'title'         => $row['objectTitle'],
					'url'           => get_permalink( $postId ),
					'image_url'     => $this->getThumbnailUrl( $postId ),
					'tru_seo'       => aioseo()->helpers->isTruSeoEligible( $postId ) ? $this->parseSeoScore( $row['seoScore'] ?? 0 ) : [],
					'decay_percent' => $this->parseDifference( $row['decayPercent'] ?? '', true ),
					'issues'        => [
						'url'   => add_query_arg( [
							'aioseo-tab' => 'post-detail',
							'post'       => $postId
						], $this->searchStatisticsUrl ),
						'items' => []
					]
				];

				$aioPost = Models\Post::getPost( $postId );
				if ( $aioPost ) {
					$items[ $i ]['issues']['items'] = aioseo()->searchStatistics->helpers->getSuggestedChanges( $aioPost );
				}
			}

			$result['optimize'] = [
				'url'          => add_query_arg( [
					'aioseo-tab' => 'content-rankings',
				], $this->searchStatisticsUrl ),
				'items'        => $items,
				'show_tru_seo' => ! empty( array_filter( array_column( $items, 'tru_seo' ) ) ),
			];
		}

		return $result;
	}

	/**
	 * Retrieves the resources data.
	 *
	 * @since 4.7.2
	 *
	 * @return array The resources' data.
	 */
	public function getResources() {
		$items = aioseo()->helpers->fetchAioseoArticles( true );

		return array_slice( array_filter( $items ), 0, 3 );
	}

	/**
	 * Returns if Search Statistics content is allowed.
	 *
	 * @since 4.7.3
	 *
	 * @return bool Whether Search Statistics content is allowed.
	 */
	public function allowSearchStatistics() {
		static $return = null;
		if ( isset( $return ) ) {
			return $return;
		}

		$return = aioseo()->searchStatistics->api->auth->isConnected() &&
					aioseo()->license &&
					aioseo()->license->hasCoreFeature( 'search-statistics', 'seo-statistics' ) &&
					aioseo()->license->hasCoreFeature( 'search-statistics', 'keyword-rankings' );

		return $return;
	}

	/**
	 * Parses the SEO score.
	 *
	 * @since 4.7.2
	 *
	 * @param  int|string $score The SEO score.
	 * @return array             The parsed SEO score.
	 */
	private function parseSeoScore( $score ) {
		$score  = intval( $score );
		$parsed = [
			'value' => $score,
			'color' => '#a1a1a1',
			'text'  => $score ? "$score/100" : esc_html__( 'N/A', 'all-in-one-seo-pack' ),
		];

		if ( $parsed['value'] > 80 ) {
			$parsed['color'] = '#00aa63';
		} elseif ( $parsed['value'] > 50 ) {
			$parsed['color'] = '#ff8c00';
		} elseif ( $parsed['value'] > 0 ) {
			$parsed['color'] = '#df2a4a';
		}

		return $parsed;
	}

	/**
	 * Parses a difference.
	 *
	 * @since 4.7.2
	 *
	 * @param  int|string $number     The number to parse.
	 * @param  bool       $percentage Whether to return the text result as a percentage.
	 * @return array                  The parsed result.
	 */
	private function parseDifference( $number, $percentage = false ) {
		$parsed = [
			'color' => '#a1a1a1',
			'text'  => esc_html__( 'N/A', 'all-in-one-seo-pack' ),
		];
		if ( ! is_numeric( $number ) ) {
			return $parsed;
		}

		$number         = intval( $number );
		$parsed['text'] = aioseo()->helpers->compactNumber( absint( $number ) );

		if ( $percentage ) {
			$parsed['text'] = $number . '%';
		}

		if ( $number > 0 ) {
			$parsed['color'] = '#00aa63';
		} elseif ( $number < 0 ) {
			$parsed['color'] = '#df2a4a';
		}

		return $parsed;
	}

	/**
	 * Parses the clicks number.
	 *
	 * @since 4.7.2
	 *
	 * @param  float|int|string $number The number of clicks.
	 * @return string                   The parsed number of clicks.
	 */
	private function parseClicks( $number ) {
		return aioseo()->helpers->compactNumber( $number );
	}

	/**
	 * Parses the posts data.
	 *
	 * @since 4.7.2
	 *
	 * @param  array $posts The posts.
	 * @return array        The parsed posts' data.
	 */
	private function parsePosts( $posts ) {
		$parsed = [];
		foreach ( $posts as $k => $item ) {
			$parsed[ $k ] = [
				'title'     => aioseo()->helpers->truncate( $item->post_title, 75 ),
				'url'       => get_permalink( $item->ID ),
				'image_url' => $this->getThumbnailUrl( $item->ID ),
				'tru_seo'   => aioseo()->helpers->isTruSeoEligible( $item->ID ) ? $this->parseSeoScore( $item->seo_score ?? 0 ) : [],
				'stats'     => []
			];

			try {
				$statistics = [];
				if (
					$this->allowSearchStatistics() &&
					method_exists( aioseo()->searchStatistics, 'getPostDetailSeoStatisticsData' )
				) {
					$statistics = aioseo()->searchStatistics->getPostDetailSeoStatisticsData( [
						'startDate' => gmdate( 'Y-m-d', $this->dateRange['startDateRaw'] ),
						'endDate'   => gmdate( 'Y-m-d', $this->dateRange['endDateRaw'] ),
						'postId'    => $item->ID,
					], false );
				}

				if ( isset( $statistics['data']['statistics']['position'] ) ) {
					$parsed[ $k ]['stats'][] = [
						'icon'  => 'position',
						'label' => esc_html__( 'Position', 'all-in-one-seo-pack' ),
						'value' => round( floatval( $statistics['data']['statistics']['position'] ) ),
					];
				}

				if ( isset( $statistics['data']['statistics']['ctr'] ) ) {
					$value                   = round( floatval( $statistics['data']['statistics']['ctr'] ), 2 );
					$parsed[ $k ]['stats'][] = [
						'icon'  => 'ctr',
						'label' => 'CTR',
						'value' => ( number_format_i18n( $value, count( explode( '.', $value ) ) ) ) . '%',
					];
				}

				if ( isset( $statistics['data']['statistics']['impressions'] ) ) {
					$parsed[ $k ]['stats'][] = [
						'icon'  => 'impressions',
						'label' => esc_html__( 'Impressions', 'all-in-one-seo-pack' ),
						'value' => aioseo()->helpers->compactNumber( $statistics['data']['statistics']['impressions'] ),
					];
				}
			} catch ( \Exception $e ) {
				// Do nothing.
			}
		}

		return $parsed;
	}

	/**
	 * Retrieves the thumbnail URL.
	 *
	 * @since 4.7.2
	 *
	 * @param  int    $postId The post ID.
	 * @return string         The post featured image URL (thumbnail size).
	 */
	private function getThumbnailUrl( $postId ) {
		$imageUrl = get_the_post_thumbnail_url( $postId );

		return $imageUrl ?: $this->featuredImagePlaceholder;
	}
}