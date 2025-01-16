<?php
namespace AIOSEO\Plugin\Common\Breadcrumbs;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Breadcrumb Block.
 *
 * @since 4.1.1
 */
class Block {
	/**
	 * The primary term list.
	 *
	 * @since 4.3.6
	 *
	 * @var array
	 */
	private $primaryTerm = [];

	/**
	 * Class constructor.
	 *
	 * @since 4.1.1
	 */
	public function __construct() {
		$this->register();
	}

	/**
	 * Registers the block.
	 *
	 * @since 4.1.1
	 *
	 * @return void
	 */
	public function register() {
		aioseo()->blocks->registerBlock(
			'aioseo/breadcrumbs', [
				'attributes'      => [
					'primaryTerm' => [
						'type'    => 'string',
						'default' => null
					]
				],
				'render_callback' => [ $this, 'render' ]
			]
		);
	}

	/**
	 * Renders the block.
	 *
	 * @since 4.1.1
	 *
	 * @param  array  $blockAttributes The block attributes.
	 * @return string                  The output from the output buffering.
	 */
	public function render( $blockAttributes ) { // phpcs:ignore VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
		// phpcs:disable HM.Security.ValidatedSanitizedInput.InputNotSanitized, HM.Security.NonceVerification.Recommended, WordPress.Security.NonceVerification.Recommended
		$postId = ! empty( $_GET['post_id'] ) ? (int) sanitize_text_field( wp_unslash( $_GET['post_id'] ) ) : false;
		// phpcs:enable

		if ( ! empty( $blockAttributes['primaryTerm'] ) ) {
			$this->primaryTerm = json_decode( $blockAttributes['primaryTerm'], true );
		}

		if ( aioseo()->blocks->isRenderingBlockInEditor() && ! empty( $postId ) ) {
			add_filter( 'aioseo_post_primary_term', [ $this, 'changePrimaryTerm' ], 10, 2 );
			add_filter( 'get_object_terms', [ $this, 'temporarilyAddTerm' ], 10, 3 );
			$breadcrumbs = aioseo()->breadcrumbs->frontend->sideDisplay( false, 'post' === get_post_type( $postId ) ? 'post' : 'single', get_post( $postId ) );
			remove_filter( 'aioseo_post_primary_term', [ $this, 'changePrimaryTerm' ], 10 );
			remove_filter( 'get_object_terms', [ $this, 'temporarilyAddTerm' ], 10 );

			if (
				in_array( 'breadcrumbsEnable', aioseo()->internalOptions->deprecatedOptions, true ) &&
				! aioseo()->options->deprecated->breadcrumbs->enable
			) {
				return '<p>' .
						sprintf(
							// Translators: 1 - The plugin short name ("AIOSEO"), 2 - Opening HTML link tag, 3 - Closing HTML link tag.
							__( 'Breadcrumbs are currently disabled, so this block will be rendered empty. You can enable %1$s\'s breadcrumb functionality under %2$sGeneral Settings > Breadcrumbs%3$s.', 'all-in-one-seo-pack' ), // phpcs:ignore Generic.Files.LineLength.MaxExceeded
							AIOSEO_PLUGIN_SHORT_NAME,
							'<a href="' . esc_url( admin_url( 'admin.php?page=aioseo-settings#/breadcrumbs' ) ) . '" target="_blank">',
							'</a>'
						) .
						'</p>';
			}

			return $breadcrumbs;
		}

		return aioseo()->breadcrumbs->frontend->display( false );
	}

	/**
	 * Temporarily adds the primary term to the list of terms.
	 *
	 * @since 4.3.6
	 *
	 * @param  array  $terms      The list of terms.
	 * @param  array  $objectIds  The object IDs.
	 * @param  array  $taxonomies The taxonomies.
	 * @return array              The list of terms.
	 */
	public function temporarilyAddTerm( $terms, $objectIds, $taxonomies ) {
		$taxonomy = $taxonomies[0];
		if ( empty( $this->primaryTerm ) || empty( $this->primaryTerm[ $taxonomy ] ) ) {
			return $terms;
		}

		$term = aioseo()->helpers->getTerm( $this->primaryTerm[ $taxonomy ] );
		if ( is_a( $term, 'WP_Term' ) ) {
			$terms[] = $term;
		}

		return $terms;
	}

	/**
	 * Changes the primary term.
	 *
	 * @since 4.3.6
	 *
	 * @param  \WP_Term $term     The term object.
	 * @param  string   $taxonomy The taxonomy name.
	 * @return \WP_Term           The term object.
	 */
	public function changePrimaryTerm( $term, $taxonomy ) {
		if ( empty( $this->primaryTerm ) || empty( $this->primaryTerm[ $taxonomy ] ) ) {
			return $term;
		}

		return aioseo()->helpers->getTerm( $this->primaryTerm[ $taxonomy ], $taxonomy );
	}
}