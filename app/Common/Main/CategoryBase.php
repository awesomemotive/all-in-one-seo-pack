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
	 * @since   4.2.0
	 * @version 5.0.6 Redirects to the term's own permalink.
	 *
	 * @param  array $queryVars Query vars to check for redirect var.
	 * @return array            The original query vars.
	 */
	public function maybeRedirectCategoryUrl( $queryVars ) {
		if ( ! isset( $queryVars['aioseo_category_redirect'] ) ) {
			return $queryVars;
		}

		$categoryUrl = $this->getStrippedCategoryUrl( $queryVars['aioseo_category_redirect'] );

		// Bail rather than redirect to the address we are already on, which would loop.
		if ( '' === $categoryUrl || untrailingslashit( $categoryUrl ) === untrailingslashit( home_url( add_query_arg( [] ) ) ) ) {
			return $queryVars;
		}

		wp_redirect( $categoryUrl, 301, 'AIOSEO' ); // phpcs:ignore WordPress.Security.SafeRedirect.wp_redirect_wp_redirect
		die;
	}

	/**
	 * Returns the base-less URL for the given category path.
	 *
	 * NOTE: Resolved through the term's permalink, because building it from the home URL drops the
	 * language prefix on a multilingual site and lands the visitor on another language's archive.
	 * A trailing page or feed segment is set aside while the term is looked up, then put back.
	 *
	 * @since 5.0.6
	 *
	 * @param  string $path The matched category path.
	 * @return string       The category URL, or an empty string if it can't be resolved.
	 */
	private function getStrippedCategoryUrl( $path ) {
		global $wp_rewrite; // phpcs:ignore Squiz.NamingConventions.ValidVariableName

		$path = trim( (string) $path, '/' );
		if ( '' === $path ) {
			return '';
		}

		$suffix     = '';
		$feeds      = implode( '|', array_map( 'preg_quote', (array) $wp_rewrite->feeds ) ); // phpcs:ignore Squiz.NamingConventions.ValidVariableName
		$pagination = preg_quote( $wp_rewrite->pagination_base, '#' ); // phpcs:ignore Squiz.NamingConventions.ValidVariableName
		$feedBase   = preg_quote( $wp_rewrite->feed_base, '#' ); // phpcs:ignore Squiz.NamingConventions.ValidVariableName
		$tail       = $pagination . '/\d+|' . $feedBase . '(?:/(?:' . $feeds . '))?|' . $feeds;
		if ( preg_match( '#^(.+?)/(' . $tail . ')$#', $path, $matches ) ) {
			$path   = $matches[1];
			$suffix = $matches[2];
		}

		$segments = explode( '/', $path );
		$term     = get_term_by( 'slug', end( $segments ), 'category' );
		if ( is_a( $term, 'WP_Term' ) ) {
			$link = get_term_link( $term );
			if ( ! is_wp_error( $link ) ) {
				return '' === $suffix ? $link : user_trailingslashit( trailingslashit( $link ) . $suffix );
			}
		}

		return trailingslashit( get_option( 'home' ) ) . user_trailingslashit( '' === $suffix ? $path : $path . '/' . $suffix, 'category' );
	}

	/**
	 * Rewrite the category base.
	 *
	 * @since   4.2.0
	 * @version 5.0.6 Also redirects from each translated category base.
	 *
	 * @return array The rewritten rules.
	 */
	public function categoryRewriteRules() {
		global $wp_rewrite; // phpcs:ignore Squiz.NamingConventions.ValidVariableName

		$categoryRewrite = $this->getCategoryRewriteRules();

		// Redirect from the old base.
		$categoryStructure = $wp_rewrite->get_category_permastruct(); // phpcs:ignore Squiz.NamingConventions.ValidVariableName
		$defaultBase       = trim( str_replace( '%category%', '', (string) $categoryStructure ), '/' );
		$categoryBase      = trim( str_replace( '%category%', '(.+)', (string) $categoryStructure ), '/' ) . '$';

		// Add the rewrite rules.
		$categoryRewrite[ $categoryBase ] = 'index.php?aioseo_category_redirect=$matches[1]';

		// A multilingual site can have a different base per language, and without a rule for each
		// one those URLs 404 instead of redirecting once the base is stripped.
		foreach ( $this->getTranslatedCategoryBases( $defaultBase ) as $translatedBase ) {
			$translatedRegex = str_replace( $defaultBase, $translatedBase, $categoryBase );

			$categoryRewrite[ $translatedRegex ] = 'index.php?aioseo_category_redirect=$matches[1]';
		}

		return $categoryRewrite;
	}

	/**
	 * Returns the category bases of all active languages that differ from the default one.
	 *
	 * @since 5.0.6
	 *
	 * @param  string   $defaultBase The category base of the default language.
	 * @return string[]              The translated category bases.
	 */
	private function getTranslatedCategoryBases( $defaultBase ) {
		if ( '' === $defaultBase ) {
			return [];
		}

		$languages = apply_filters( 'wpml_active_languages', null );
		if ( empty( $languages ) || ! is_array( $languages ) ) {
			return [];
		}

		$bases = [];
		foreach ( array_keys( $languages ) as $language ) {
			$translatedBase = $this->translateCategoryBase( $defaultBase, $language );
			if ( $translatedBase !== $defaultBase ) {
				$bases[] = $translatedBase;
			}
		}

		return array_values( array_unique( $bases ) );
	}

	/**
	 * Returns the category base as it appears in the given language.
	 *
	 * NOTE: Only the last segment is translated - a compound base like "blog/category" keeps its prefix.
	 *
	 * @since 5.0.6
	 *
	 * @param  string $categoryBase The category base of the default language.
	 * @param  string $language     The language code.
	 * @return string               The category base for the given language.
	 */
	private function translateCategoryBase( $categoryBase, $language ) {
		if ( empty( $language ) || '' === $categoryBase ) {
			return $categoryBase;
		}

		$segments   = explode( '/', $categoryBase );
		$slug       = (string) array_pop( $segments );
		$translated = apply_filters( 'wpml_get_translated_slug', $slug, 'category', $language, 'taxonomy' );
		$segments[] = ! empty( $translated ) && is_string( $translated ) ? $translated : $slug;

		return implode( '/', $segments );
	}

	/**
	 * Get the rewrite rules for the category.
	 *
	 * @since   4.2.0
	 * @version 5.0.6 Includes the categories of every language.
	 *
	 * @return array An array of category rewrite rules.
	 */
	private function getCategoryRewriteRules() {
		global $wp_rewrite; // phpcs:ignore Squiz.NamingConventions.ValidVariableName

		$categoryRewrite = [];

		$categories = $this->getCategories();
		if ( empty( $categories ) ) {
			return $categoryRewrite;
		}

		$blogPrefix      = $this->getBlogPrefix();
		$paginationBase = $wp_rewrite->pagination_base; // phpcs:ignore Squiz.NamingConventions.ValidVariableName
		foreach ( $categories as $category ) {
			$nicename        = $this->getCategoryPath( $category, $categories );
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
	 * Returns every category, keyed by term ID.
	 *
	 * NOTE: Queried directly because the rules are generated once and shared by every language,
	 * while get_terms() is filtered down to the active one - the rest would get no rule and 404.
	 *
	 * @since 5.0.6
	 *
	 * @return array The categories.
	 */
	private function getCategories() {
		$db     = aioseo()->core->db->noConflict();
		$prefix = aioseo()->core->db->db->prefix;

		$results = $db->execute(
			'SELECT t.term_id, t.slug, tt.parent
			FROM ' . $prefix . 'terms as t
			INNER JOIN ' . $prefix . 'term_taxonomy as tt ON tt.term_id = t.term_id
			WHERE tt.taxonomy = \'category\'',
			true
		)->result();

		$categories = [];
		foreach ( (array) $results as $result ) {
			$categories[ (int) $result->term_id ] = $result;
		}

		return $categories;
	}

	/**
	 * Returns the given category's full path, including its parents.
	 *
	 * NOTE: Walks the passed set rather than get_category_parents(), which resolves each ancestor
	 * through a filtered lookup and so can't see the other languages' terms.
	 *
	 * @since 5.0.6
	 *
	 * @param  object $category   The category.
	 * @param  array  $categories All categories, keyed by term ID.
	 * @return string             The category path.
	 */
	private function getCategoryPath( $category, $categories ) {
		$slugs  = [ $category->slug ];
		$parent = (int) $category->parent;
		$seen   = [ (int) $category->term_id => true ];

		// A term whose parent chain loops back on itself would otherwise spin forever.
		while ( $parent && isset( $categories[ $parent ] ) && ! isset( $seen[ $parent ] ) ) {
			$seen[ $parent ] = true;
			array_unshift( $slugs, $categories[ $parent ]->slug );
			$parent = (int) $categories[ $parent ]->parent;
		}

		return implode( '/', $slugs );
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
	 * @since   4.2.0
	 * @version 5.0.6 Strips the base of the term's own language.
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

		// A multilingual plugin can swap the base for its translation before this filter runs, in
		// which case the default base is no longer in the link and stripping it would be a no-op.
		$categoryBase = $this->translateCategoryBase( $categoryBase, $this->getTermLanguage( $term, $taxonomy ) );

		$categoryBase .= '/';

		return preg_replace( '`' . preg_quote( (string) $categoryBase, '`' ) . '`u', '', (string) $link, 1 );
	}

	/**
	 * Returns the language code of the given term.
	 *
	 * @since 5.0.6
	 *
	 * @param  object|null $term     The term.
	 * @param  string      $taxonomy The taxonomy.
	 * @return string                The language code, or an empty string if there is none.
	 */
	private function getTermLanguage( $term, $taxonomy ) {
		if ( ! is_object( $term ) || empty( $term->term_taxonomy_id ) ) {
			return '';
		}

		$language = apply_filters( 'wpml_element_language_code', null, [
			'element_id'   => $term->term_taxonomy_id,
			'element_type' => 'tax_' . $taxonomy
		] );

		return is_string( $language ) ? $language : '';
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