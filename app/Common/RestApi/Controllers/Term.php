<?php
namespace AIOSEO\Plugin\Common\RestApi\Controllers;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles all term routes.
 *
 * Merged into the main plugin from the legacy aioseo-rest-api addon.
 * Term SEO meta is backed by the Pro Term model, so the meta data fields are only
 * registered in Pro ({@see \AIOSEO\Plugin\Pro\RestApi\Controllers\Term}).
 *
 * @since 4.9.8
 */
class Term extends Base {
	/**
	 * Registers the fields dynamically.
	 *
	 * @since 4.9.8
	 *
	 * @return void
	 */
	public function register() {
		$taxonomies = aioseo()->helpers->getPublicTaxonomies( true );
		$taxonomies = apply_filters( 'aioseo_rest_api_taxonomies', $taxonomies );

		$supportedTaxonomies = [];
		foreach ( $taxonomies as $taxonomy ) {
			$taxonomyObject = get_taxonomy( $taxonomy );

			if (
				! is_a( $taxonomyObject, 'WP_Taxonomy' ) ||
				! $taxonomyObject->show_in_rest
			) {
				continue;
			}

			$supportedTaxonomies[] = $taxonomy;
		}

		foreach ( $supportedTaxonomies as $taxonomy ) {
			if ( 'post_tag' === $taxonomy ) {
				$taxonomy = 'tag';
			}

			$this->registerHeadFields( $taxonomy );
			$this->registerMetaDataField( $taxonomy );
			$this->registerBreadcrumbFields( $taxonomy );
			$this->registerDeprecatedUpdateFields( $taxonomy );
		}
	}

	/**
	 * Registers the meta data field.
	 *
	 * Term SEO meta requires the Pro Term model, so this is a no-op in Lite.
	 * Overridden in {@see \AIOSEO\Plugin\Pro\RestApi\Controllers\Term}.
	 *
	 * @since 4.9.8
	 *
	 * @param  string $taxonomy The taxonomy name.
	 * @return void
	 */
	protected function registerMetaDataField( $taxonomy ) {} // phpcs:ignore VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable

	/**
	 * Registers the deprecated single value fields.
	 *
	 * Term SEO meta requires the Pro Term model, so this is a no-op in Lite.
	 * Overridden in {@see \AIOSEO\Plugin\Pro\RestApi\Controllers\Term}.
	 *
	 * @since 4.9.8
	 *
	 * @param  string $taxonomy The taxonomy name.
	 * @return void
	 */
	protected function registerDeprecatedUpdateFields( $taxonomy ) {} // phpcs:ignore VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable

	/**
	 * Sets the given term as the queried object of the main query.
	 *
	 * @since 4.9.8
	 *
	 * @param  array $termArr The term object.
	 * @return void
	 */
	protected function setWpQuery( $termArr ) {
		// phpcs:disable Squiz.NamingConventions.ValidVariableName
		global $wp_query;
		$this->originalQuery = clone $wp_query;

		$term = aioseo()->helpers->getTerm( $termArr['id'] );

		$wp_query->get_queried_object_id = (int) $term->term_id;
		$wp_query->queried_object        = $term;
		$wp_query->is_tax                = true;

		switch ( $term->taxonomy ) {
			case 'category':
				$wp_query->is_category = true;
				break;
			case 'post_tag':
				$wp_query->is_tag = true;
				break;
			default:
				break;
		}
		// phpcs:enable Squiz.NamingConventions.ValidVariableName
	}
}