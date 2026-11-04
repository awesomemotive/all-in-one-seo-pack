<?php
namespace AIOSEO\Plugin\Common\ImportExport\YoastSeo;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// phpcs:disable WordPress.Arrays.ArrayDeclarationSpacing.AssociativeArrayFound

/**
 * Migrates settings stored in the Yoast `wpseo` option.
 * NOTE: The class name reflects the Yoast source, not the AIOSEO destination.
 *
 * @since 4.0.0
 */
class GeneralSettings {
	/**
	 * List of options.
	 *
	 * @since 4.2.7
	 *
	 * @var array
	 */
	private $options = [];

	/**
	 * Class constructor.
	 *
	 * @since   4.0.0
	 * @version 5.0.5 Added {@see self::migrateCrawlSettings()}.
	 */
	public function __construct() {
		$this->options = get_option( 'wpseo' );
		if ( empty( $this->options ) ) {
			return;
		}

		$settings = [
			'googleverify'       => [ 'type' => 'string', 'newOption' => [ 'webmasterTools', 'google' ] ],
			'msverify'           => [ 'type' => 'string', 'newOption' => [ 'webmasterTools', 'bing' ] ],
			'yandexverify'       => [ 'type' => 'string', 'newOption' => [ 'webmasterTools', 'yandex' ] ],
			'baiduverify'        => [ 'type' => 'string', 'newOption' => [ 'webmasterTools', 'baidu' ] ],
			'enable_xml_sitemap' => [ 'type' => 'boolean', 'newOption' => [ 'sitemap', 'general', 'enable' ] ]
		];

		aioseo()->importExport->yoastSeo->helpers->mapOldToNew( $settings, $this->options );

		$this->migrateCrawlSettings();
	}

	/**
	 * Migrates Yoast's Crawl Optimization settings (feeds, search cleanup, URL cleanup, unwanted bots).
	 * NOTE: Only Yoast keys with a clear AIOSEO equivalent are mapped.
	 *
	 * @since 5.0.5
	 *
	 * @return void
	 */
	private function migrateCrawlSettings() {
		$searchCleanupSettings = [
			'search_cleanup'              => [ 'type' => 'boolean', 'newOption' => [ 'searchAppearance', 'advanced', 'searchCleanup', 'enable' ] ],
			'search_cleanup_emoji'        => [
				'type'      => 'boolean',
				'newOption' => [ 'searchAppearance', 'advanced', 'searchCleanup', 'settings', 'emojisAndSymbols' ]
			],
			'search_cleanup_patterns'     => [
				'type'      => 'boolean',
				'newOption' => [ 'searchAppearance', 'advanced', 'searchCleanup', 'settings', 'commonPatterns' ]
			],
			'search_character_limit'      => [
				'type'      => 'integer',
				'newOption' => [ 'searchAppearance', 'advanced', 'searchCleanup', 'settings', 'maxAllowedNumberOfChars' ]
			],
			'redirect_search_pretty_urls' => [
				'type'      => 'boolean',
				'newOption' => [ 'searchAppearance', 'advanced', 'searchCleanup', 'settings', 'redirectPrettyUrls' ]
			],
			'deny_search_crawling'        => [
				'type'      => 'boolean',
				'newOption' => [ 'searchAppearance', 'advanced', 'searchCleanup', 'settings', 'preventCrawling' ]
			]
		];

		// Yoast's clean_permalinks (strip unregistered URL parameters) is intentionally not mapped:
		// blockArgs->enable only turns on query arg monitoring/logging against a user-curated block list, so it reproduces none of Yoast's behavior.
		$blockArgsSettings = [
			'clean_campaign_tracking_urls' => [ 'type' => 'boolean', 'newOption' => [ 'searchAppearance', 'advanced', 'blockArgs', 'optimizeUtmParameters' ] ]
		];

		$botSettings = [
			'deny_adsbot_crawling'          => [
				'type'      => 'boolean',
				'newOption' => [ 'searchAppearance', 'advanced', 'unwantedBots', 'settings', 'googleAdsBot' ]
			],
			'deny_ccbot_crawling'           => [
				'type'      => 'boolean',
				'newOption' => [ 'searchAppearance', 'advanced', 'unwantedBots', 'settings', 'commonCrawlCcBot' ]
			],
			'deny_google_extended_crawling' => [
				'type'      => 'boolean',
				'newOption' => [ 'searchAppearance', 'advanced', 'unwantedBots', 'settings', 'googleGeminiVertexAiBots' ]
			],
			'deny_gptbot_crawling'          => [
				'type'      => 'boolean',
				'newOption' => [ 'searchAppearance', 'advanced', 'unwantedBots', 'settings', 'openAiGptBot' ]
			]
		];

		$helpers = aioseo()->importExport->yoastSeo->helpers;
		$helpers->mapOldToNew( $searchCleanupSettings, $this->options );
		$helpers->mapOldToNew( $blockArgsSettings, $this->options );
		$helpers->mapOldToNew( $botSettings, $this->options );

		// The bot and prevent-crawling options only take effect through their robots.txt rules, which so far only the admin UI created.
		// This import changes the options server-side, so the rules have to be synced here.
		$robotsRuleKeys = array_merge( array_keys( $botSettings ), [ 'deny_search_crawling' ] );
		if ( array_intersect( $robotsRuleKeys, array_keys( $this->options ) ) ) {
			aioseo()->robotsTxt->syncSearchAppearanceRules();
		}

		// Yoast's feed booleans mean "remove this feed"; AIOSEO's equivalents mean "this feed is enabled". Invert each value so user intent is preserved.
		$feedMap = [
			'remove_feed_global'          => 'global',
			'remove_feed_global_comments' => 'globalComments',
			'remove_feed_post_comments'   => 'postComments',
			'remove_feed_authors'         => 'authors',
			'remove_feed_search'          => 'search'
		];
		foreach ( $feedMap as $yoastKey => $aioseoKey ) {
			if ( ! isset( $this->options[ $yoastKey ] ) ) {
				continue;
			}

			aioseo()->options->searchAppearance->advanced->crawlCleanup->feeds->{$aioseoKey} = ! (bool) $this->options[ $yoastKey ];
		}

		// Yoast has a single Atom/RDF removal toggle; AIOSEO splits them into separate "is enabled" flags.
		if ( isset( $this->options['remove_atom_rdf_feeds'] ) ) {
			$keepAtomRdf = ! (bool) $this->options['remove_atom_rdf_feeds'];
			aioseo()->options->searchAppearance->advanced->crawlCleanup->feeds->atom = $keepAtomRdf;
			aioseo()->options->searchAppearance->advanced->crawlCleanup->feeds->rdf  = $keepAtomRdf;
		}

		// Yoast tracks category/tag feed removal with per-taxonomy booleans; AIOSEO maintains an "included" list of taxonomies that keep their feed.
		$includedTaxonomies = aioseo()->options->searchAppearance->advanced->crawlCleanup->feeds->taxonomies->included;
		$includedTaxonomies = is_array( $includedTaxonomies ) ? $includedTaxonomies : [];

		$taxonomyMap = [
			'remove_feed_categories' => 'category',
			'remove_feed_tags'       => 'post_tag'
		];
		foreach ( $taxonomyMap as $yoastKey => $taxonomy ) {
			if ( ! isset( $this->options[ $yoastKey ] ) ) {
				continue;
			}

			if ( ! $this->options[ $yoastKey ] ) {
				$includedTaxonomies[] = $taxonomy;
				continue;
			}

			// "Include All Taxonomies" overrides the included list at runtime, so removing one feed means listing the kept ones explicitly.
			if ( aioseo()->options->searchAppearance->advanced->crawlCleanup->feeds->taxonomies->all ) {
				$includedTaxonomies = aioseo()->helpers->getPublicTaxonomies( true );

				aioseo()->options->searchAppearance->advanced->crawlCleanup->feeds->taxonomies->all = false;
			}

			$includedTaxonomies = array_diff( $includedTaxonomies, [ $taxonomy ] );
		}

		aioseo()->options->searchAppearance->advanced->crawlCleanup->feeds->taxonomies->included = array_values( array_unique( $includedTaxonomies ) );

		// Turn on crawlCleanup->enable if any Yoast trigger is set.
		// Never turn it off, so a re-import without Yoast triggers doesn't disable a setting the user already enabled in AIOSEO.
		$enableTriggers = [
			'remove_feed_global',
			'remove_feed_global_comments',
			'remove_feed_post_comments',
			'remove_feed_authors',
			'remove_feed_search',
			'remove_feed_categories',
			'remove_feed_tags',
			'remove_atom_rdf_feeds',
			'search_cleanup',
			'search_cleanup_emoji',
			'search_cleanup_patterns',
			'redirect_search_pretty_urls',
			'deny_search_crawling'
		];
		foreach ( $enableTriggers as $trigger ) {
			if ( ! empty( $this->options[ $trigger ] ) ) {
				aioseo()->options->searchAppearance->advanced->crawlCleanup->enable = true;
				break;
			}
		}
	}
}