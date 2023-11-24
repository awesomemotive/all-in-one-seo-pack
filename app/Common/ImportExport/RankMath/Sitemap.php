<?php
namespace AIOSEO\Plugin\Common\ImportExport\RankMath;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// phpcs:disable WordPress.Arrays.ArrayDeclarationSpacing.AssociativeArrayFound

/**
 * Migrates the sitemap settings.
 *
 * @since 4.0.0
 */
class Sitemap {
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
	 * @since 4.0.0
	 */
	public function __construct() {
		$this->options = get_option( 'rank-math-options-sitemap' );
		if ( empty( $this->options ) ) {
			return;
		}

		$this->migrateIncludedObjects();
		$this->migrateIncludeImages();
		$this->migrateExcludedPosts();
		$this->migrateExcludedTerms();

		$settings = [
			'items_per_page' => [ 'type' => 'string', 'newOption' => [ 'sitemap', 'general', 'linksPerIndex' ] ],
		];

		aioseo()->options->sitemap->general->indexes = true;
		aioseo()->importExport->rankMath->helpers->mapOldToNew( $settings, $this->options );
	}

	/**
	 * Migrates the included post types and taxonomies.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	private function migrateIncludedObjects() {
		$includedPostTypes  = [];
		$includedTaxonomies = [];

		$allowedPostTypes = array_values( array_diff( aioseo()->helpers->getPublicPostTypes( true ), aioseo()->helpers->getNoindexedPostTypes() ) );
		foreach ( $allowedPostTypes as $postType ) {
			foreach ( $this->options as $name => $value ) {
				if ( preg_match( "#pt_{$postType}_sitemap$#", $name, $match ) && 'on' === $this->options[ $name ] ) {
					$includedPostTypes[] = $postType;
				}
			}
		}

		$allowedTaxonomies = array_values( array_diff( aioseo()->helpers->getPublicTaxonomies( true ), aioseo()->helpers->getNoindexedTaxonomies() ) );
		foreach ( $allowedTaxonomies as $taxonomy ) {
			foreach ( $this->options as $name => $value ) {
				if ( preg_match( "#tax_{$taxonomy}_sitemap$#", $name, $match ) && 'on' === $this->options[ $name ] ) {
					$includedTaxonomies[] = $taxonomy;
				}
			}
		}

		aioseo()->options->sitemap->general->postTypes->included = $includedPostTypes;
		if ( count( $allowedPostTypes ) !== count( $includedPostTypes ) ) {
			aioseo()->options->sitemap->general->postTypes->all = false;
		}

		aioseo()->options->sitemap->general->taxonomies->included = $includedTaxonomies;
		if ( count( $allowedTaxonomies ) !== count( $includedTaxonomies ) ) {
			aioseo()->options->sitemap->general->taxonomies->all = false;
		}
	}

	/**
	 * Migrates the Redirect Attachments setting.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	private function migrateIncludeImages() {
		if ( ! empty( $this->options['include_images'] ) ) {
			if ( 'off' === $this->options['include_images'] ) {
				aioseo()->options->sitemap->general->advancedSettings->enable        = true;
				aioseo()->options->sitemap->general->advancedSettings->excludeImages = true;
			}
		}
	}

	/**
	 * Migrates the posts that are excluded from the sitemap.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	private function migrateExcludedPosts() {
		if ( empty( $this->options['exclude_posts'] ) ) {
			return;
		}

		$rmExcludedPosts = array_filter( explode( ',', $this->options['exclude_posts'] ) );
		$excludedPosts   = aioseo()->options->sitemap->general->advancedSettings->excludePosts;

		if ( count( $rmExcludedPosts ) ) {
			foreach ( $rmExcludedPosts as $rmExcludedPost ) {
				$post = get_post( trim( $rmExcludedPost ) );
				if ( ! is_object( $post ) ) {
					continue;
				}

				$excludedPost        = new \stdClass();
				$excludedPost->value = $post->ID;
				$excludedPost->type  = $post->post_type;
				$excludedPost->label = $post->post_title;
				$excludedPost->link  = get_permalink( $post->ID );

				array_push( $excludedPosts, wp_json_encode( $excludedPost ) );
			}
			aioseo()->options->sitemap->general->advancedSettings->enable = true;
		}
		aioseo()->options->sitemap->general->advancedSettings->excludePosts = $excludedPosts;
	}

	/**
	 * Migrates the terms that are excluded from the sitemap.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	private function migrateExcludedTerms() {
		if ( empty( $this->options['exclude_terms'] ) ) {
			return;
		}

		$rmExcludedTerms = array_filter( explode( ',', $this->options['exclude_terms'] ) );
		$excludedTerms   = aioseo()->options->sitemap->general->advancedSettings->excludeTerms;

		if ( count( $rmExcludedTerms ) ) {
			foreach ( $rmExcludedTerms as $rmExcludedTerm ) {
				$term = get_term( trim( $rmExcludedTerm ) );
				if ( ! is_object( $term ) ) {
					continue;
				}

				$excludedTerm        = new \stdClass();
				$excludedTerm->value = $term->term_id;
				$excludedTerm->type  = $term->taxonomy;
				$excludedTerm->label = $term->name;
				$excludedTerm->link  = get_term_link( $term );

				array_push( $excludedTerms, wp_json_encode( $excludedTerm ) );
			}
			aioseo()->options->sitemap->general->advancedSettings->enable = true;
		}
		aioseo()->options->sitemap->general->advancedSettings->excludeTerms = $excludedTerms;
	}
}