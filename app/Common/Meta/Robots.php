<?php
namespace AIOSEO\Plugin\Common\Meta;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AIOSEO\Plugin\Common\Integrations\BuddyPress as BuddyPressIntegration;

/**
 * Handles the robots meta tag.
 *
 * @since 4.0.0
 */
class Robots {
	/**
	 * The robots meta tag attributes.
	 *
	 * We'll already set the keys on construction so that we always output the attributes in the same order.
	 *
	 * @since 4.0.0
	 *
	 * @var array
	 */
	protected $attributes = [
		'noindex'           => '',
		'nofollow'          => '',
		'noarchive'         => '',
		'nosnippet'         => '',
		'noimageindex'      => '',
		'noodp'             => '',
		'notranslate'       => '',
		'max-snippet'       => '',
		'max-image-preview' => '',
		'max-video-preview' => ''
	];

	/**
	 * Class constructor.
	 *
	 * @since 4.0.16
	 */
	public function __construct() {
		add_action( 'wp_loaded', [ $this, 'unregisterWooCommerceNoindex' ] );
		add_action( 'template_redirect', [ $this, 'noindexFeed' ] );
		add_action( 'wp_head', [ $this, 'disableWpRobotsCore' ], -1 );
	}

	/**
	 * Prevents WooCommerce from noindexing the Cart/Checkout pages.
	 *
	 * @since 4.1.3
	 *
	 * @return void
	 */
	public function unregisterWooCommerceNoindex() {
		if ( has_action( 'wp_head', 'wc_page_noindex' ) ) {
			remove_action( 'wp_head', 'wc_page_noindex' );
		}
	}

	/**
	 * Prevents WP Core from outputting its own robots meta tag.
	 *
	 * @since 4.0.16
	 *
	 * @return void
	 */
	public function disableWpRobotsCore() {
		remove_all_filters( 'wp_robots' );
	}

	/**
	 * Noindexes RSS feed pages.
	 *
	 * @since 4.0.17
	 *
	 * @return void
	 */
	public function noindexFeed() {
		if (
			! is_feed() ||
			( ! aioseo()->options->searchAppearance->advanced->globalRobotsMeta->default && ! aioseo()->options->searchAppearance->advanced->globalRobotsMeta->noindexFeed )
		) {
			return;
		}

		header( 'X-Robots-Tag: noindex, follow', true );
	}

	/**
	 * Returns the robots meta tag value.
	 *
	 * @since 4.0.0
	 *
	 * @return mixed The robots meta tag value or false.
	 */
	public function meta() {
		// We need this check to happen first as spammers can attempt to make the page appear like a post or term by using URL params e.g. "cat=".
		if ( is_search() ) {
			$this->globalValues( [ 'archives', 'search' ] );

			return $this->metaHelper();
		}

		if ( BuddyPressIntegration::isComponentPage() ) {
			return aioseo()->standalone->buddyPress->component->getMeta( 'robots' );
		}

		if ( is_category() || is_tag() || is_tax() ) {
			$this->term();

			return $this->metaHelper();
		}

		if ( is_home() && 'page' !== get_option( 'show_on_front' ) ) {
			$this->globalValues();

			return $this->metaHelper();
		}

		$post = aioseo()->helpers->getPost();
		if ( $post ) {
			$this->post();

			return $this->metaHelper();
		}

		if ( is_author() ) {
			$this->globalValues( [ 'archives', 'author' ] );

			return $this->metaHelper();
		}

		if ( is_date() ) {
			$this->globalValues( [ 'archives', 'date' ] );

			return $this->metaHelper();
		}

		if ( is_404() ) {
			return apply_filters( 'aioseo_404_robots', 'noindex' );
		}

		if ( is_archive() ) {
			$this->archives();

			return $this->metaHelper();
		}
	}

	/**
	 * Stringifies and filters the robots meta tag value.
	 *
	 * Acts as a helper for meta().
	 *
	 * @since 4.0.0
	 *
	 * @param  bool         $array Whether or not to return the value as an array.
	 * @return array|string        The robots meta tag value.
	 */
	public function metaHelper( $array = false ) {
		$pageNumber = aioseo()->helpers->getPageNumber();
		if ( 1 < $pageNumber || aioseo()->helpers->getCommentPageNumber() ) {
			if (
				aioseo()->options->searchAppearance->advanced->globalRobotsMeta->default ||
				aioseo()->options->searchAppearance->advanced->globalRobotsMeta->noindexPaginated
			) {
				$this->attributes['noindex'] = 'noindex';
			}

			if (
				aioseo()->options->searchAppearance->advanced->globalRobotsMeta->default ||
				aioseo()->options->searchAppearance->advanced->globalRobotsMeta->nofollowPaginated
			) {
				$this->attributes['nofollow'] = 'nofollow';
			}
		}

		// Never allow users to noindex the first page of the homepage.
		if ( is_front_page() && 1 === $pageNumber ) {
			$this->attributes['noindex'] = '';
		}

		// Because we prevent WordPress Core from outputting a robots tag in disableWpRobotsCore(), we need to noindex/nofollow non-public sites ourselves.
		if ( ! get_option( 'blog_public' ) ) {
			$this->attributes['noindex']  = 'noindex';
			$this->attributes['nofollow'] = 'nofollow';
		}

		$this->attributes = array_filter( (array) apply_filters( 'aioseo_robots_meta', $this->attributes ) );

		return $array ? $this->attributes : implode( ', ', $this->attributes );
	}

	/**
	 * Sets the attributes for the current post.
	 *
	 * @since 4.0.0
	 *
	 * @param  \WP_Post|null $post The post object.
	 * @return void
	 */
	public function post( $post = null ) {
		$dynamicOptions = aioseo()->dynamicOptions->noConflict();
		$post           = aioseo()->helpers->getPost( $post );
		$metaData       = aioseo()->meta->metaData->getMetaData( $post );

		if ( ! empty( $metaData ) && ! $metaData->robots_default ) {
			$this->metaValues( $metaData );

			return;
		}

		if ( $dynamicOptions->searchAppearance->postTypes->has( $post->post_type ) ) {
			$this->globalValues( [ 'postTypes', $post->post_type ], true );
		}
	}

	/**
	 * Returns the robots meta tag value for the current term.
	 *
	 * @since 4.0.6
	 *
	 * @param  \WP_Term|null $term The term object if any.
	 * @return void
	 */
	public function term( $term = null ) {
		$dynamicOptions = aioseo()->dynamicOptions->noConflict();
		$term           = is_a( $term, 'WP_Term' ) ? $term : aioseo()->helpers->getTerm();

		// Misbehaving themes/plugins can manipulate the loop and make archives return a post as the queried object.
		if ( ! is_a( $term, 'WP_Term' ) ) {
			return;
		}

		if ( $dynamicOptions->searchAppearance->taxonomies->has( $term->taxonomy ) ) {
			$this->globalValues( [ 'taxonomies', $term->taxonomy ], true );

			return;
		}

		$this->globalValues();
	}

	/**
	 * Sets the attributes for the current archive.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	private function archives() {
		$dynamicOptions = aioseo()->dynamicOptions->noConflict();
		$postType       = aioseo()->helpers->getTerm();
		if ( ! empty( $postType->name ) && $dynamicOptions->searchAppearance->archives->has( $postType->name ) ) {
			$this->globalValues( [ 'archives', $postType->name ], true );
		}
	}

	/**
	 * Sets the attributes based on the global values.
	 *
	 * @since 4.0.0
	 *
	 * @param  array   $optionOrder     The order in which the options need to be called to get the relevant robots meta settings.
	 * @param  boolean $isDynamicOption Whether this is for a dynamic option.
	 * @return void
	 */
	public function globalValues( $optionOrder = [], $isDynamicOption = false ) {
		$robotsMeta = [];
		if ( count( $optionOrder ) ) {
			$options = $isDynamicOption
				? aioseo()->dynamicOptions->noConflict( true )->searchAppearance
				: aioseo()->options->noConflict()->searchAppearance;

			foreach ( $optionOrder as $option ) {
				if ( ! $options->has( $option, false ) ) {
					return;
				}
				$options = $options->$option;
			}

			$clonedOptions = clone $options;
			if ( ! $clonedOptions->show ) {
				$this->attributes['noindex'] = 'noindex';
			}

			if ( ! isset( $options->advanced->robotsMeta ) ) {
				$robotsMeta = aioseo()->options->searchAppearance->advanced->globalRobotsMeta->all();
			} else {
				$robotsMeta = $options->advanced->robotsMeta->all();
				if ( $robotsMeta['default'] ) {
					$robotsMeta = aioseo()->options->searchAppearance->advanced->globalRobotsMeta->all();
				}
			}
		} else {
			$robotsMeta = aioseo()->options->searchAppearance->advanced->globalRobotsMeta->all();
		}

		$this->attributes['max-image-preview'] = 'max-image-preview:large';

		if ( $robotsMeta['default'] ) {
			return;
		}

		if ( $robotsMeta['noindex'] ) {
			$this->attributes['noindex'] = 'noindex';
		}
		if ( $robotsMeta['nofollow'] ) {
			$this->attributes['nofollow'] = 'nofollow';
		}
		if ( $robotsMeta['noarchive'] ) {
			$this->attributes['noarchive'] = 'noarchive';
		}
		$noSnippet = $robotsMeta['nosnippet'];
		if ( $noSnippet ) {
			$this->attributes['nosnippet'] = 'nosnippet';
		}
		if ( $robotsMeta['noodp'] ) {
			$this->attributes['noodp'] = 'noodp';
		}
		if ( $robotsMeta['notranslate'] ) {
			$this->attributes['notranslate'] = 'notranslate';
		}
		$maxSnippet = $robotsMeta['maxSnippet'];
		if ( ! $noSnippet && is_numeric( $maxSnippet ) ) {
			$this->attributes['max-snippet'] = "max-snippet:$maxSnippet";
		}
		$maxImagePreview = $robotsMeta['maxImagePreview'];
		$noImageIndex    = $robotsMeta['noimageindex'];
		if ( ! $noImageIndex && $maxImagePreview && in_array( $maxImagePreview, [ 'none', 'standard', 'large' ], true ) ) {
			$this->attributes['max-image-preview'] = "max-image-preview:$maxImagePreview";
		}
		$maxVideoPreview = $robotsMeta['maxVideoPreview'];
		if ( isset( $maxVideoPreview ) && is_numeric( $maxVideoPreview ) ) {
			$this->attributes['max-video-preview'] = "max-video-preview:$maxVideoPreview";
		}

		// Check this last so that we can prevent max-image-preview from being output if noimageindex is enabled.
		if ( $noImageIndex ) {
			$this->attributes['max-image-preview'] = '';
			$this->attributes['noimageindex']      = 'noimageindex';
		}
	}

	/**
	 * Sets the attributes from the meta data.
	 *
	 * @since 4.0.0
	 *
	 * @param  \AIOSEO\Plugin\Common\Models\Post|\AIOSEO\Plugin\Pro\Models\Term $metaData The post/term meta data.
	 * @return void
	 */
	protected function metaValues( $metaData ) {
		if ( $metaData->robots_noindex || $this->isPasswordProtected() ) {
			$this->attributes['noindex'] = 'noindex';
		}
		if ( $metaData->robots_nofollow ) {
			$this->attributes['nofollow'] = 'nofollow';
		}
		if ( $metaData->robots_noarchive ) {
			$this->attributes['noarchive'] = 'noarchive';
		}
		if ( $metaData->robots_nosnippet ) {
			$this->attributes['nosnippet'] = 'nosnippet';
		}
		if ( $metaData->robots_noodp ) {
			$this->attributes['noodp'] = 'noodp';
		}
		if ( $metaData->robots_notranslate ) {
			$this->attributes['notranslate'] = 'notranslate';
		}
		if ( ! $metaData->robots_nosnippet && isset( $metaData->robots_max_snippet ) && is_numeric( $metaData->robots_max_snippet ) ) {
			$this->attributes['max-snippet'] = "max-snippet:$metaData->robots_max_snippet";
		}
		if ( ! $metaData->robots_noimageindex && $metaData->robots_max_imagepreview && in_array( $metaData->robots_max_imagepreview, [ 'none', 'standard', 'large' ], true ) ) {
			$this->attributes['max-image-preview'] = "max-image-preview:$metaData->robots_max_imagepreview";
		}
		if ( isset( $metaData->robots_max_videopreview ) && is_numeric( $metaData->robots_max_videopreview ) ) {
			$this->attributes['max-video-preview'] = "max-video-preview:$metaData->robots_max_videopreview";
		}

		// Check this last so that we can prevent max-image-preview from being output if noimageindex is enabled.
		if ( $metaData->robots_noimageindex ) {
			$this->attributes['max-image-preview'] = '';
			$this->attributes['noimageindex']      = 'noimageindex';
		}
	}

	/**
	 * Checks whether the current post is password protected.
	 *
	 * @since 4.0.0
	 *
	 * @return bool Whether the post is password protected.
	 */
	private function isPasswordProtected() {
		$post = aioseo()->helpers->getPost();

		return is_object( $post ) && $post->post_password;
	}
}