<?php
namespace AIOSEO\Plugin\Common\Main;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Main class with methods that are called.
 *
 * @since   4.2.0
 * @version 4.7.1 Moved from Pro to Common.
 */
class CategoryBase {
	/**
	 * Class constructor.
	 *
	 * @since 4.2.0
	 */
	public function __construct() {
		if ( ! aioseo()->options->searchAppearance->advanced->removeCategoryBase ) {
			return;
		}

		add_filter( 'query_vars', [ $this, 'queryVars' ] );
		add_filter( 'request', [ $this, 'maybeRedirectCategoryUrl' ] );
		add_filter( 'category_rewrite_rules', [ $this, 'categoryRewriteRules' ] );
		add_filter( 'term_link', [ $this, 'modifyTermLink' ], 10, 3 );

		// Flush rewrite rules on any of the following actions.
		add_action( 'created_category', [ $this, 'scheduleFlushRewrite' ] );
		add_action( 'delete_category', [ $this, 'scheduleFlushRewrite' ] );
		add_action( 'edited_category', [ $this, 'scheduleFlushRewrite' ] );
	}

	/**
	 * Add the redirect var to the query vars if the "strip category bases" option is enabled.
	 *
	 * @since 4.2.0
	 *
	 * @param  array $queryVars Query vars to filter.
	 * @return array            The filtered query vars.
	 */
	public function queryVars( $queryVars ) {
		$queryVars[] = 'aioseo_category_redirect';

		return $queryVars;
	}

	/**
	 * Redirect the category URL to the new one.
	 *
	 * @param  array $queryVars Query vars to check for redirect var.
	 * @return array            The original query vars.
	 */
	public function maybeRedirectCategoryUrl( $queryVars ) {
		if ( isset( $queryVars['aioseo_category_redirect'] ) ) {
			$categoryUrl = trailingslashit( get_option( 'home' ) ) . user_trailingslashit( $queryVars['aioseo_category_redirect'], 'category' );
			wp_redirect( $categoryUrl, 301, 'AIOSEO' );
			die;
		}

		return $queryVars;
	}

	/**
	 * Rewrite the category base.
	 *
	 * @since 4.2.0
	 *
	 * @return array The rewritten rules.
	 */
	public function categoryRewriteRules() {
		global $wp_rewrite; // phpcs:ignore Squiz.NamingConventions.ValidVariableName

		$categoryRewrite = $this->getCategoryRewriteRules();

		// Redirect from the old base.
		$categoryStructure = $wp_rewrite->get_category_permastruct(); // phpcs:ignore Squiz.NamingConventions.ValidVariableName
		$categoryBase      = trim( str_replace( '%category%', '(.+)', $categoryStructure ), '/' ) . '$';

		// Add the rewrite rules.
		$categoryRewrite[ $categoryBase ] = 'index.php?aioseo_category_redirect=$matches[1]';

		return $categoryRewrite;
	}

	/**
	 * Get the rewrite rules for the category.
	 *
	 * @since 4.2.0
	 *
	 * @return array An array of category rewrite rules.
	 */
	private function getCategoryRewriteRules() {
		global $wp_rewrite; // phpcs:ignore Squiz.NamingConventions.ValidVariableName

		$categoryRewrite = [];
		$categories      = get_categories( [ 'hide_empty' => false ] );

		if ( empty( $categories ) ) {
			return $categoryRewrite;
		}

		$blogPrefix      = $this->getBlogPrefix();
		$paginationBase = $wp_rewrite->pagination_base; // phpcs:ignore Squiz.NamingConventions.ValidVariableName
		foreach ( $categories as $category ) {
			$nicename        = $this->getCategoryParents( $category ) . $category->slug;
			$categoryRewrite = $this->addCategoryRewrites( $categoryRewrite, $nicename, $blogPrefix, $paginationBase );

			// Also add the rules for uppercase.
			$filteredNicename = $this->convertEncodedToUppercase( $nicename );

			if ( $nicename !== $filteredNicename ) {
				$categoryRewrite = $this->addCategoryRewrites( $categoryRewrite, $filteredNicename, $blogPrefix, $paginationBase );
			}
		}

		return $categoryRewrite;
	}

	/**
	 * Get the blog prefix.
	 *
	 * @since 4.2.0
	 *
	 * @return string The prefix for the blog.
	 */
	private function getBlogPrefix() {
		$permalinkStructure = get_option( 'permalink_structure' );
		if (
			is_multisite() &&
			! is_subdomain_install() &&
			is_main_site() &&
			0 === strpos( $permalinkStructure, '/blog/' )
		) {
			return 'blog/';
		}

		return '';
	}

	/**
	 * Retrieve category parents with separator.
	 *
	 * @since 4.2.0
	 *
	 * @param  \WP_Term $category the category instance.
	 * @return string             A list of category parents.
	 */
	private function getCategoryParents( $category ) {
		if (
			$category->parent === $category->term_id ||
			absint( $category->parent ) < 1
		) {
			return '';
		}

		$parents = get_category_parents( $category->parent, false, '/', true );

		return is_wp_error( $parents ) ? '' : $parents;
	}

	/**
	 * Walks through category nicename and convert encoded parts
	 * into uppercase using $this->encode_to_upper().
	 *
	 * @since 4.2.0
	 *
	 * @param  string $nicename The encoded category string.
	 * @return string           The converted category string.
	 */
	private function convertEncodedToUppercase( $nicename ) {
		// Checks if name has any encoding in it.
		if ( false === strpos( $nicename, '%' ) ) {
			return $nicename;
		}

		$nicenames = explode( '/', $nicename );
		$nicenames = array_map( [ $this, 'convertToUppercase' ], $nicenames );

		return implode( '/', $nicenames );
	}

	/**
	 * Converts the encoded URI string to uppercase.
	 *
	 * @since 4.2.0
	 *
	 * @param  string $encoded The encoded category string.
	 * @return string          The converted category string.
	 */
	private function convertToUppercase( $encoded ) {
		if ( false === strpos( $encoded, '%' ) ) {
			return $encoded;
		}

		return strtoupper( $encoded );
	}

	/**
	 * Adds the required category rewrites rules.
	 *
	 * @since 4.2.0
	 *
	 * @param  array  $categoryRewrite  The current set of rules.
	 * @param  string $categoryNicename The category nicename.
	 * @param  string $blogPrefix       Multisite blog prefix.
	 * @param  string $paginationBase   WP_Query pagination base.
	 * @return array                    The added set of rules.
	 */
	private function addCategoryRewrites( $categoryRewrite, $categoryNicename, $blogPrefix, $paginationBase ) {
		$categoryRewrite[ $blogPrefix . '(' . $categoryNicename . ')/(?:feed/)?(feed|rdf|rss|rss2|atom)/?$' ]   = 'index.php?category_name=$matches[1]&feed=$matches[2]';
		$categoryRewrite[ $blogPrefix . '(' . $categoryNicename . ')/' . $paginationBase . '/?([0-9]{1,})/?$' ] = 'index.php?category_name=$matches[1]&paged=$matches[2]';
		$categoryRewrite[ $blogPrefix . '(' . $categoryNicename . ')/?$' ]                                      = 'index.php?category_name=$matches[1]';

		return $categoryRewrite;
	}

	/**
	 * Remove the category base from the category link.
	 *
	 * @since 4.2.0
	 *
	 * @param  string $link     Term link.
	 * @param  object $term     The current Term Object.
	 * @param  string $taxonomy The current Taxonomy.
	 * @return string           The modified term link.
	 */
	public function modifyTermLink( $link, $term = null, $taxonomy = '' ) {
		if ( 'category' !== $taxonomy ) {
			return $link;
		}

		$categoryBase = get_option( 'category_base' );
		if ( empty( $categoryBase ) ) {
			global $wp_rewrite; // phpcs:ignore Squiz.NamingConventions.ValidVariableName
			$categoryStructure = $wp_rewrite->get_category_permastruct(); // phpcs:ignore Squiz.NamingConventions.ValidVariableName
			$categoryBase      = trim( str_replace( '%category%', '', $categoryStructure ), '/' );
		}

		// Remove initial slash, if there is one (we remove the trailing slash in the regex replacement and don't want to end up short a slash).
		if ( '/' === substr( $categoryBase, 0, 1 ) ) {
			$categoryBase = substr( $categoryBase, 1 );
		}

		$categoryBase .= '/';

		return preg_replace( '`' . preg_quote( (string) $categoryBase, '`' ) . '`u', '', (string) $link, 1 );
	}

	/**
	 * Flush the rewrite rules.
	 *
	 * @since 4.2.0
	 *
	 * @return void
	 */
	public function scheduleFlushRewrite() {
		aioseo()->options->flushRewriteRules();
	}
}