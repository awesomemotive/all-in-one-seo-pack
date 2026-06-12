<?php
namespace AIOSEO\Plugin\Lite\Traits\Helpers;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Contains all Vue related helper methods for Lite.
 *
 * @since 4.8.6.1
 */
trait Vue {
	/**
	 * Returns the data for Vue.
	 *
	 * @since   4.8.6.1
	 * @version 4.9.8 Hooks in setTermData() for the Lite taxonomy upsell.
	 *
	 * @param  string $page         The current page.
	 * @param  int    $staticPostId Data for a specific post.
	 * @param  string $integration  Data for integration (builder).
	 * @return array                The data.
	 */
	public function getVueData( $page = null, $staticPostId = null, $integration = null ) {
		$this->args = compact( 'page', 'staticPostId', 'integration' );
		$hash       = md5( implode( '', array_map( 'strval', $this->args ) ) );
		if ( isset( $this->cache[ $hash ] ) ) {
			return $this->cache[ $hash ];
		}

		$this->data = parent::getVueData( $page, $staticPostId, $integration );

		$this->setInitialData();
		$this->setTermData();

		$this->cache[ $hash ] = $this->data;

		return $this->cache[ $hash ];
	}

	/**
	 * Set Vue initial data for Lite.
	 *
	 * @since 4.8.6.1
	 *
	 * @return void
	 */
	private function setInitialData() {
		// Override the upgrade URL for Lite users
		$this->data['urls']['upgradeUrl'] = apply_filters( 'aioseo_upgrade_link', AIOSEO_MARKETING_URL . 'lite-upgrade/' );
	}

	/**
	 * Set Vue term data for Lite.
	 *
	 * Populates currentPost with safe defaults so the post-settings Vue app mounts on the
	 * blurred taxonomy upsell on the edit-term and edit-tags (term list) screens. The Lite
	 * upsell is purely visual: the form is blurred and overlaid by a floating CTA, so no
	 * Pro-only term meta needs to be hydrated.
	 *
	 * @since 4.9.8
	 *
	 * @return void
	 */
	private function setTermData() {
		$isEditTerm = aioseo()->helpers->isScreenBase( 'term' );
		$isTermList = aioseo()->helpers->isScreenBase( 'edit-tags' );
		if ( ! $isEditTerm && ! $isTermList ) {
			return;
		}

		$screen = aioseo()->helpers->getCurrentScreen();
		if ( empty( $screen->taxonomy ) ) {
			return;
		}

		$taxonomy = $screen->taxonomy;

		if ( $isEditTerm ) {
			// phpcs:disable HM.Security.ValidatedSanitizedInput.InputNotSanitized, HM.Security.NonceVerification.Recommended, WordPress.Security.NonceVerification.Recommended
			$termId = isset( $_GET['tag_ID'] ) ? absint( wp_unslash( $_GET['tag_ID'] ) ) : 0;
			// phpcs:enable
			$term     = $termId ? aioseo()->helpers->getTerm( $termId ) : null;
			$taxonomy = ! empty( $term->taxonomy ) ? $term->taxonomy : $taxonomy;
		} else {
			// Why: the list screen has no specific term. Use a sentinel id so shouldShowMetaBox()
			// passes its truthy check while signalling "preview only" to anything that inspects it.
			$termId = -1;
			$term   = null;
		}

		$taxonomyObj   = get_taxonomy( $taxonomy );
		$typeLabel     = ( $taxonomyObj && isset( $taxonomyObj->labels->singular_name ) )
			? $taxonomyObj->labels->singular_name
			: $taxonomy;
		$defaultTitle  = $this->getLiteTaxonomyTitleDefault( $taxonomy );
		$defaultDesc   = $this->getLiteTaxonomyDescriptionDefault( $taxonomy );
		$permalink     = $isEditTerm && $termId ? get_term_link( $termId ) : '';
		$permalink     = is_wp_error( $permalink ) ? '' : $permalink;
		$defaultTags   = method_exists( aioseo()->tags, 'getDefaultTermTags' )
			? aioseo()->tags->getDefaultTermTags( $isEditTerm ? $termId : 0 )
			: [
				'title'       => $defaultTitle,
				'description' => $defaultDesc
			];

		$this->data['currentPost'] = [
			'context'                     => 'term',
			'tags'                        => $defaultTags,
			'id'                          => $termId,
			'priority'                    => 'default',
			'frequency'                   => 'default',
			'permalink'                   => $permalink,
			'title'                       => $defaultTitle,
			'description'                 => $defaultDesc,
			'keywords'                    => [],
			'type'                        => $typeLabel,
			'termType'                    => 'type' === $taxonomy ? '_aioseo_type' : $taxonomy,
			'canonicalUrl'                => '',
			'default'                     => true,
			'noindex'                     => false,
			'noarchive'                   => false,
			'nosnippet'                   => false,
			'nofollow'                    => false,
			'noimageindex'                => false,
			'noodp'                       => false,
			'notranslate'                 => false,
			'maxSnippet'                  => -1,
			'maxVideoPreview'             => -1,
			'maxImagePreview'             => 'large',
			'modalOpen'                   => false,
			'generalMobilePrev'           => false,
			'og_object_type'              => 'default',
			'og_title'                    => '',
			'og_description'              => '',
			'og_image_custom_url'         => '',
			'og_image_custom_fields'      => '',
			'og_image_type'               => 'default',
			'og_video'                    => '',
			'og_article_section'          => '',
			'og_article_tags'             => [],
			'twitter_use_og'              => false,
			'twitter_card'                => 'summary_large_image',
			'twitter_image_custom_url'    => '',
			'twitter_image_custom_fields' => '',
			'twitter_image_type'          => 'default',
			'twitter_title'               => '',
			'twitter_description'         => '',
			'redirects'                   => [
				'modalOpen' => false
			]
		];
	}

	/**
	 * Returns the default taxonomy title template from dynamic options.
	 *
	 * @since 4.9.8
	 *
	 * @param  string $taxonomy The taxonomy slug.
	 * @return string           The default title template, or an empty string when not configured.
	 */
	private function getLiteTaxonomyTitleDefault( $taxonomy ) {
		$taxonomyOptions = $this->getLiteTaxonomyOptions( $taxonomy );
		if ( ! is_object( $taxonomyOptions ) ) {
			return '';
		}

		$value = $taxonomyOptions->title ?? null;

		return is_string( $value ) ? $value : '';
	}

	/**
	 * Returns the default taxonomy meta description template from dynamic options.
	 *
	 * @since 4.9.8
	 *
	 * @param  string $taxonomy The taxonomy slug.
	 * @return string           The default description template, or an empty string when not configured.
	 */
	private function getLiteTaxonomyDescriptionDefault( $taxonomy ) {
		$taxonomyOptions = $this->getLiteTaxonomyOptions( $taxonomy );
		if ( ! is_object( $taxonomyOptions ) ) {
			return '';
		}

		$value = $taxonomyOptions->metaDescription ?? null;

		return is_string( $value ) ? $value : '';
	}

	/**
	 * Returns the dynamic options object for a given taxonomy, or null when unavailable.
	 *
	 * @since 4.9.8
	 *
	 * @param  string      $taxonomy The taxonomy slug.
	 * @return object|null           The taxonomy options object, or null when not configured.
	 */
	private function getLiteTaxonomyOptions( $taxonomy ) {
		$dynamicOptions = aioseo()->dynamicOptions->noConflict();

		$searchAppearance = $dynamicOptions->searchAppearance ?? null;
		if ( ! is_object( $searchAppearance ) ) {
			return null;
		}

		$taxonomies = $searchAppearance->taxonomies ?? null;
		if ( ! is_object( $taxonomies ) || ! method_exists( $taxonomies, 'has' ) ) {
			return null;
		}

		if ( ! $taxonomies->has( $taxonomy, false ) ) {
			return null;
		}

		$taxonomyOptions = $taxonomies->{$taxonomy} ?? null;

		return is_object( $taxonomyOptions ) ? $taxonomyOptions : null;
	}
}