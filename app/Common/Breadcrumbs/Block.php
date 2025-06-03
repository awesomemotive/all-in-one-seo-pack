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
	 * The breadcrumb settings.
	 *
	 * @since 4.8.3
	 *
	 * @var array
	 */
	private $breadcrumbSettings = [
		'default'            => true,
		'separator'          => '›',
		'showHomeCrumb'      => true,
		'showTaxonomyCrumbs' => true,
		'showParentCrumbs'   => true,
		'parentTemplate'     => 'default',
		'template'           => 'default',
		'taxonomy'           => ''
	];

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
					'primaryTerm'        => [
						'type'    => 'string',
						'default' => null
					],
					'breadcrumbSettings' => [
						'type'    => 'object',
						'default' => $this->breadcrumbSettings
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

		if ( ! empty( $blockAttributes['breadcrumbSettings'] ) ) {
			$this->breadcrumbSettings = $blockAttributes['breadcrumbSettings'];
		}

		aioseo()->breadcrumbs->setOverride( $this->getBlockOverrides() );

		if ( aioseo()->blocks->isRenderingBlockInEditor() && ! empty( $postId ) ) {
			add_filter( 'get_object_terms', [ $this, 'temporarilyAddTerm' ], 10, 3 );
			$breadcrumbs = aioseo()->breadcrumbs->frontend->sideDisplay( false, 'post' === get_post_type( $postId ) ? 'post' : 'single', get_post( $postId ) );
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
	 * Get the block overrides.
	 *
	 * @since 4.8.3
	 *
	 * @return array
	 */
	private function getBlockOverrides() {
		$default = filter_var( $this->breadcrumbSettings['default'], FILTER_VALIDATE_BOOLEAN );
		if ( true === $default || ! aioseo()->pro ) {
			return [];
		}

		return [
			'default'            => false,
			'taxonomy'           => $this->breadcrumbSettings['taxonomy'] ?? '',
			'separator'          => $this->breadcrumbSettings['separator'] ?? '›',
			'showHomeCrumb'      => filter_var( $this->breadcrumbSettings['showHomeCrumb'], FILTER_VALIDATE_BOOLEAN ),
			'showTaxonomyCrumbs' => filter_var( $this->breadcrumbSettings['showTaxonomyCrumbs'], FILTER_VALIDATE_BOOLEAN ),
			'showParentCrumbs'   => filter_var( $this->breadcrumbSettings['showParentCrumbs'], FILTER_VALIDATE_BOOLEAN ),
			'template'           => empty( $this->breadcrumbSettings['template'] ) ? '' : [
				'templateType' => 'custom',
				'template'     => aioseo()->helpers->decodeHtmlEntities( aioseo()->helpers->encodeOutputHtml( $this->breadcrumbSettings['template'] ) )
			],
			'parentTemplate'     => empty( $this->breadcrumbSettings['parentTemplate'] ) ? '' : [
				'templateType' => 'custom',
				'template'     => aioseo()->helpers->decodeHtmlEntities( aioseo()->helpers->encodeOutputHtml( $this->breadcrumbSettings['parentTemplate'] ) )
			],
			'primaryTerm'        => ! empty( $this->primaryTerm[ $this->breadcrumbSettings['taxonomy'] ] ) ? $this->primaryTerm[ $this->breadcrumbSettings['taxonomy'] ] : null
		];
	}
}