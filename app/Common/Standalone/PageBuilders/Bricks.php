<?php
namespace AIOSEO\Plugin\Common\Standalone\PageBuilders;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Integrate our SEO Panel with Bricks Builder.
 *
 * @since 4.9.2
 */
class Bricks extends Base {
	/**
	 * The theme name to integrate with.
	 *
	 * @since 4.9.2
	 *
	 * @var array
	 */
	public $themes = [
		'bricks'
	];

	/**
	 * The integration slug.
	 *
	 * @since 4.9.2
	 *
	 * @var string
	 */
	public $integrationSlug = 'bricks';

	/**
	 * Init the integration.
	 *
	 * @since 4.9.2
	 *
	 * @return void
	 */
	public function init() {
		add_action( 'wp', [ $this, 'maybeRun' ] );

		add_filter( 'bricks/content/html_before_end', [ $this, 'doContentEndAction' ], 10, 2 );

		// Disable Bricks' native SEO and Open Graph meta tags since AIOSEO handles these.
		add_filter( 'bricks/frontend/disable_seo', '__return_true' );
		add_filter( 'bricks/frontend/disable_opengraph', '__return_true' );
	}

	/**
	 * Fires an action at the end of Bricks content, allowing addons to append content.
	 *
	 * This bridges the gap for features that hook into `the_content` filter,
	 * which Bricks bypasses when rendering its own stored content.
	 *
	 * @since 4.9.2
	 *
	 * @param  string $htmlBeforeEnd The HTML to append before the closing content tag.
	 * @param  array  $bricksData    The Bricks content data.
	 * @return string                The HTML with any appended content.
	 */
	public function doContentEndAction( $htmlBeforeEnd, $bricksData ) {
		if ( empty( $bricksData ) || ! is_array( $bricksData ) ) {
			return $htmlBeforeEnd;
		}

		ob_start();

		do_action( 'aioseo_bricks_content_end', $bricksData );

		$htmlBeforeEnd .= ob_get_clean();

		return $htmlBeforeEnd;
	}

	/**
	 * Check if we are in the Page Builder and run the integrations.
	 *
	 * @since 4.9.2
	 *
	 * @return void
	 */
	public function maybeRun() {
		if (
			aioseo()->postSettings->canAddPostSettingsMetabox( get_post_type( $this->getPostId() ) ) &&
			aioseo()->helpers->callFunc( 'bricks_is_builder_main' )
		) {
			add_action( 'wp_enqueue_scripts', [ $this, 'enqueue' ], 20 );
		}
	}

	/**
	 * Returns whether the given post ID was built with Bricks.
	 *
	 * @since 4.9.2
	 *
	 * @param  int     $postId The Post ID.
	 * @return boolean         Whether the post was built with Bricks.
	 */
	public function isBuiltWith( $postId ) {
		$editorMode = defined( 'BRICKS_DB_EDITOR_MODE' ) && is_string( BRICKS_DB_EDITOR_MODE ) ? BRICKS_DB_EDITOR_MODE : '_bricks_editor_mode';

		return 'bricks' === get_post_meta( $postId, $editorMode, true );
	}

	/**
	 * Returns the Bricks "edit post link" for the given post ID.
	 *
	 * @since 4.9.2
	 *
	 * @param  int    $postId The post ID.
	 * @return string         The Bricks "edit post link" for the given post.
	 */
	public function getEditUrl( $postId ) {
		if ( ! $this->isBuiltWith( $postId ) ) {
			return '';
		}

		$builderParam = defined( 'BRICKS_BUILDER_PARAM' ) && is_string( BRICKS_BUILDER_PARAM ) ? BRICKS_BUILDER_PARAM : 'bricks';

		return add_query_arg( [ $builderParam => 'run' ], get_permalink( $postId ) );
	}

	/**
	 * Checks whether or not we should prevent the date from being modified.
	 *
	 * @since 4.9.2
	 *
	 * @param  int  $postId The Post ID.
	 * @return bool         Whether or not we should prevent the date from being modified.
	 */
	public function limitModifiedDate( $postId ) {
		// This method is supposed to be used in the `bricks_save_post` action.
		if ( ! isset( $_REQUEST['nonce'] ) || ! wp_verify_nonce( $_REQUEST['nonce'], 'bricks-nonce-builder' ) ) {
			return false;
		}

		$requestPostId = ! empty( $_REQUEST['postId'] ) ? (int) $_REQUEST['postId'] : false;
		if ( $requestPostId !== $postId ) {
			return false;
		}

		return ! empty( $_REQUEST['aioseo_limit_modified_date'] );
	}
	/**
	 * Returns the processed page builder content.
	 *
	 * @since 4.9.2
	 *
	 * @param  int    $postId     The post ID.
	 * @param  mixed  $rawContent The raw content.
	 * @return string             The processed content.
	 */
	public function processContent( $postId, $rawContent = null ) {
		if ( ! class_exists( '\Bricks\Database' ) || ! class_exists( '\Bricks\Frontend' ) ) {
			return '';
		}

		// If no raw content provided or it's not an array, fetch from Bricks post meta.
		// This happens when called from the frontend (PHP) where $post->post_content is passed.
		// Bricks stores its actual content in BRICKS_DB_PAGE_CONTENT post meta, not post_content.
		if ( empty( $rawContent ) || ! is_array( $rawContent ) ) {
			$rawContent = \Bricks\Database::get_data( $postId, 'content' );
		}

		// If still no content, return empty.
		if ( empty( $rawContent ) || ! is_array( $rawContent ) ) {
			return '';
		}

		\Bricks\Database::$page_data['preview_or_post_id'] = $postId;

		ob_start();
		try {
			\Bricks\Frontend::render_content( $rawContent );

			return ob_get_clean();
		} catch ( \Exception $e ) {
			ob_end_clean();

			return '';
		}
	}
}