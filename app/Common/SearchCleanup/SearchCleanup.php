<?php
namespace AIOSEO\Plugin\Common\SearchCleanup;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class for Search Cleanup that handles prevention of search spams.
 *
 * @since 4.8.0
 */
class SearchCleanup {
	/**
	 * Patterns to match against to find spam.
	 *
	 * @since 4.8.0
	 *
	 * @var array
	 */
	private $patterns = [
		'/[：（）【】［］]+/u',
		'/(TALK|QQ)\:/iu',
	];

	/**
	 * Class constructor.
	 *
	 * @since 4.8.0
	 */
	public function __construct() {
		// If Crawl Cleanup is disabled, return early.
		if ( ! aioseo()->options->searchAppearance->advanced->crawlCleanup->enable ) {
			return;
		}

		if ( aioseo()->options->searchAppearance->advanced->searchCleanup->enable ) {
			add_filter( 'pre_get_posts', [ $this, 'validateSearch' ] );
		}

		if ( aioseo()->options->searchAppearance->advanced->searchCleanup->settings->redirectPrettyUrls ) {
			add_action( 'template_redirect', [ $this, 'maybeRedirectSearches' ], 0 );
		}
	}

	/**
	 * Check against unwanted patterns.
	 *
	 * @since 4.8.0
	 *
	 * @param  \WP_Query $query The main query.
	 * @return \WP_Query        The main query.
	 */
	public function validateSearch( $query ) {
		if ( ! $query->is_search() ) {
			return $query;
		}

		$searchString = rawurldecode( $query->get( 's' ) );

		$this->checkEmojis( $searchString );
		$this->checkCommonSpamPatterns( $searchString );
		$this->limitCharacters();

		return $query;
	}

	/**
	 * Limits the number of characters in the search term.
	 *
	 * @since 4.8.0
	 *
	 * @return void
	 */
	private function limitCharacters() {
		// We retrieve the search term unescaped as we want to count the characters. We make sure to escape it afterwards before we continue tom process it.
		$unescapedTerm = get_search_query( false );

		$maxAllowedNumberOfChars = aioseo()->options->searchAppearance->advanced->searchCleanup->settings->maxAllowedNumberOfChars;

		$rawSearchTerm = wp_unslash( $unescapedTerm );
		if ( mb_strlen( $rawSearchTerm, 'UTF-8' ) > $maxAllowedNumberOfChars ) {
			$newS = mb_substr( $rawSearchTerm, 0, $maxAllowedNumberOfChars, 'UTF-8' );
			set_query_var( 's', wp_slash( esc_attr( $newS ) ) );
		}
	}

	/**
	 * Check if query contains emojis and special characters.
	 *
	 * @since 4.8.0
	 *
	 * @param  string $searchString The search string.
	 * @return void
	 */
	private function checkEmojis( $searchString ) {
		if ( ! aioseo()->options->searchAppearance->advanced->searchCleanup->settings->emojisAndSymbols ) {
			return;
		}

		if ( aioseo()->helpers->hasEmojis( $searchString ) ) {
			aioseo()->helpers->notFoundPage();
		}
	}

	/**
	 * Checks against common search spam patterns.
	 *
	 * @since 4.8.0
	 *
	 * @param  string $searchString Search string.
	 * @return void
	 */
	private function checkCommonSpamPatterns( $searchString ) {
		if ( ! aioseo()->options->searchAppearance->advanced->searchCleanup->settings->commonPatterns ) {
			return;
		}

		$patterns = apply_filters( 'aioseo_search_cleanup_patterns', $this->patterns );
		foreach ( $patterns as $pattern ) {
			if ( preg_match( $pattern, $searchString ) ) {
				aioseo()->helpers->notFoundPage();
			}
		}
	}

	/**
	 * Redirect pretty search URLs to the "raw" equivalent
	 *
	 * @since 4.8.0
	 *
	 * @return void
	 */
	public function maybeRedirectSearches() {
		if ( ! is_search() ) {
			return;
		}

		$requestUri = aioseo()->helpers->getRequestUrl();
		if ( stripos( $requestUri, '/search/' ) === 0 ) {
			$args = [];

			$parsed = wp_parse_url( $requestUri );
			if ( ! empty( $parsed['query'] ) ) {
				wp_parse_str( $parsed['query'], $args );
			}

			// Extract the search query directly from the REQUEST_URI.
			$searchPath = trim( str_replace( '/search/', '', $parsed['path'] ), '/' );
			$args['s']  = aioseo()->helpers->decodeUrl( $searchPath );
			$properUrl  = home_url( '/' );

			if ( intval( get_query_var( 'paged' ) ) > 1 ) {
				$properUrl .= sprintf( 'page/%s/', \get_query_var( 'paged' ) );
				unset( $args['paged'] );
			}

			$properUrl = add_query_arg( array_map( 'rawurlencode_deep', $args ), $properUrl );

			if ( ! empty( $parsed['fragment'] ) ) {
				$properUrl .= '#' . rawurlencode( $parsed['fragment'] );
			}

			aioseo()->helpers->redirect( $properUrl, 301, 'We redirect pretty URLs to the raw format.' );
		}
	}
}