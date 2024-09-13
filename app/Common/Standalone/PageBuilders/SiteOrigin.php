<?php
namespace AIOSEO\Plugin\Common\Standalone\PageBuilders;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Integrate our SEO Panel with SiteOrigin Page Builder.
 *
 * @since 4.6.6
 */
class SiteOrigin extends Base {
	/**
	 * The plugin files.
	 *
	 * @since 4.6.6
	 *
	 * @var array
	 */
	public $plugins = [
		'siteorigin-panels/siteorigin-panels.php'
	];

	/**
	 * The integration slug.
	 *
	 * @since 4.6.6
	 *
	 * @var string
	 */
	public $integrationSlug = 'siteorigin';

	/**
	 * Init the integration.
	 *
	 * @since 4.6.6
	 *
	 * @return void
	 */
	public function init() {
		$postType = get_post_type( $this->getPostId() );
		if ( empty( $postType ) ) {
			$postType = ! empty( $_GET['post_type'] ) ? sanitize_text_field( wp_unslash( $_GET['post_type'] ) ) : 'post'; // phpcs:ignore HM.Security.NonceVerification.Recommended, WordPress.Security.NonceVerification.Recommended, Generic.Files.LineLength.MaxExceeded
		}

		if ( ! aioseo()->postSettings->canAddPostSettingsMetabox( $postType ) ) {
			return;
		}

		add_action( 'siteorigin_panel_enqueue_admin_scripts', [ $this, 'enqueue' ] );
	}

	/**
	 * Returns whether or not the given Post ID was built with SiteOrigin.
	 *
	 * @since 4.6.6
	 *
	 * @param  int $postId The Post ID.
	 * @return bool        Whether or not the Post was built with SiteOrigin.
	 */
	public function isBuiltWith( $postId ) {
		$postObj = get_post( $postId );
		if (
			! empty( $postObj ) &&
			(
				preg_match( '/siteorigin_widget/', $postObj->post_content ) ||
				preg_match( '/so-panel widget/', $postObj->post_content )
			)
		) {
			return true;
		}

		return false;
	}

	/**
	 * Returns the processed page builder content.
	 *
	 * @since 4.6.6
	 *
	 * @param  int    $postId  The post id.
	 * @param  string $content The raw content.
	 * @return string          The processed content.
	 */
	public function processContent( $postId, $content = '' ) {
		// When performing a save_post action, we must execute the siteorigin_widget shortcodes if there are image widgets.
		// This ensures that the getFirstImageInContent method can locate the images, as SiteOrigin uses shortcodes for images.
		// We cache the first image in the content during post saving.
		if (
			doing_action( 'save_post' ) &&
			aioseo()->options->searchAppearance->advanced->runShortcodes &&
			(
				stripos( $content, 'SiteOrigin_Widget_Image_Widget' ) !== false ||
				stripos( $content, 'WP_Widget_Media_Image' ) !== false
			)
		) {
			$content = aioseo()->helpers->doAllowedShortcodes( $content, $postId, [ 'siteorigin_widget' ] );
		}

		return parent::processContent( $postId, $content );
	}
}