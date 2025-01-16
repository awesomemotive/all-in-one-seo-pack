<?php
namespace AIOSEO\Plugin\Common\Meta;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * To check whether SEO is enabled for the queried object.
 *
 * @since 4.0.0
 */
class Included {
	/**
	 * Checks whether the queried object is included.
	 *
	 * @since 4.0.0
	 *
	 * @return bool
	 */
	public function isIncluded() {
		if ( is_admin() || is_feed() ) {
			return false;
		}

		if ( apply_filters( 'aioseo_disable', false ) || $this->isExcludedGlobal() ) {
			return false;
		}

		if ( ! $this->isQueriedObjectPublic() ) {
			return false;
		}

		return true;
	}

	/**
	 * Checks whether the queried object is public.
	 *
	 * @since 4.2.2
	 *
	 * @return bool Whether the queried object is public.
	 */
	protected function isQueriedObjectPublic() {
		$queriedObject = get_queried_object(); // Don't use the getTerm helper here.

		if ( is_a( $queriedObject, 'WP_Post' ) ) {
			return aioseo()->helpers->isPostTypePublic( $queriedObject->post_type );
		}

		// Check if the current page is a post type archive page.
		if ( is_a( $queriedObject, 'WP_Post_Type' ) ) {
			return aioseo()->helpers->isPostTypePublic( $queriedObject->name );
		}

		if ( is_a( $queriedObject, 'WP_Term' ) ) {
			if ( aioseo()->helpers->isWooCommerceProductAttribute( $queriedObject->taxonomy ) ) {
				// Check if the attribute has archives enabled.
				$taxonomy = get_taxonomy( $queriedObject->taxonomy );

				return $taxonomy->public;
			}

			return aioseo()->helpers->isTaxonomyPublic( $queriedObject->taxonomy );
		}

		// Return true in all other cases (e.g. search page, date archive, etc.).
		return true;
	}

	/**
	 * Checks whether the queried object has been excluded globally.
	 *
	 * @since 4.0.0
	 *
	 * @return bool
	 */
	protected function isExcludedGlobal() {
		if ( is_category() || is_tag() || is_tax() ) {
			return $this->isTaxExcludedGlobal();
		}

		if ( ! in_array( 'excludePosts', aioseo()->internalOptions->deprecatedOptions, true ) ) {
			return false;
		}

		$excludedPosts = aioseo()->options->deprecated->searchAppearance->advanced->excludePosts;

		if ( empty( $excludedPosts ) ) {
			return false;
		}

		$ids = [];
		foreach ( $excludedPosts as $object ) {
			$object = json_decode( $object );
			if ( is_int( $object->value ) ) {
				$ids[] = (int) $object->value;
			}
		}

		$post = aioseo()->helpers->getPost();
		if ( empty( $post ) ) {
			return false;
		}

		if ( in_array( (int) $post->ID, $ids, true ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Checks whether the queried object has been excluded globally.
	 *
	 * @since 4.0.0
	 *
	 * @return bool
	 */
	protected function isTaxExcludedGlobal() {
		if ( ! in_array( 'excludeTerms', aioseo()->internalOptions->deprecatedOptions, true ) ) {
			return false;
		}

		$excludedTerms = aioseo()->options->deprecated->searchAppearance->advanced->excludeTerms;

		if ( empty( $excludedTerms ) ) {
			return false;
		}

		$ids = [];
		foreach ( $excludedTerms as $object ) {
			$object = json_decode( $object );
			if ( is_int( $object->value ) ) {
				$ids[] = (int) $object->value;
			}
		}

		$term = aioseo()->helpers->getTerm();
		if ( in_array( (int) $term->term_id, $ids, true ) ) {
			return true;
		}

		return false;
	}
}