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
	 * Checks whether the user is allowed to update meta data for the given taxonomy.
	 *
	 * @since   4.9.8
	 * @version 5.0.3 Moved the aioseo_rest_api_allow_update check to updateMetaData(); moved from {@see \AIOSEO\Plugin\Pro\RestApi\Controllers\Term}.
	 *
	 * @param  string $taxonomy The taxonomy name.
	 * @return bool             Whether the user is allowed to update meta data for the taxonomy.
	 */
	protected function isAllowedToUpdate( $taxonomy ) {
		return aioseo()->helpers->canEditTaxonomy( $this->getObjectName( $taxonomy ) ) && $this->canEditMetaData();
	}

	/**
	 * Returns the taxonomy name for the given REST object type.
	 *
	 * @since 5.0.3
	 *
	 * @param  string $objectType The REST object type.
	 * @return string             The taxonomy name.
	 */
	protected function getObjectName( $objectType ) {
		// The tags controller names its object type "tag" instead of the taxonomy.
		return 'tag' === $objectType ? 'post_tag' : $objectType;
	}

	/**
	 * Sets the given term as the queried object of the main query.
	 *
	 * @since   4.9.8
	 * @version 5.0.2 Bail when the term ID no longer resolves to a WP_Term.
	 * @version 5.0.3 Sets queried_object_id instead of get_queried_object_id.
	 *
	 * @param  array $termArr The term object.
	 * @return void
	 */
	protected function setWpQuery( $termArr ) {
		// phpcs:disable Squiz.NamingConventions.ValidVariableName
		global $wp_query;
		$this->originalQuery = clone $wp_query;

		$term = aioseo()->helpers->getTerm( $termArr['id'] );
		if ( ! is_a( $term, 'WP_Term' ) ) {
			return;
		}

		$wp_query->queried_object_id     = (int) $term->term_id;
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