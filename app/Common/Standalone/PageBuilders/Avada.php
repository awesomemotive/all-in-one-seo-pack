<?php
namespace AIOSEO\Plugin\Common\Standalone\PageBuilders;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Integrate our SEO Panel with Avada Page Builder.
 *
 * @since 4.5.2
 */
class Avada extends Base {
	/**
	 * The plugin files.
	 *
	 * @since 4.5.2
	 *
	 * @var array
	 */
	public $plugins = [
		'fusion-builder/fusion-builder.php'
	];

	/**
	 * The integration slug.
	 *
	 * @since 4.5.2
	 *
	 * @var string
	 */
	public $integrationSlug = 'avada';

	/**
	 * Init the integration.
	 *
	 * @since 4.5.2
	 *
	 * @return void
	 */
	public function init() {
		add_action( 'fusion_enqueue_live_scripts', [ $this, 'enqueue' ] );
		add_action( 'fusion_builder_admin_scripts_hook', [ $this, 'enqueue' ] );
		add_action( 'wp_footer', [ $this, 'addSidebarWrapper' ] );
	}

	/**
	 * Check if we are in the front-end builder.
	 *
	 * @since 4.5.2
	 *
	 * @return boolean Whether or not we are in the front-end builder.
	 */
	public function isBuilder() {
		return function_exists( 'fusion_is_builder_frame' ) && fusion_is_builder_frame();
	}

	/**
	 * Check if we are in the front-end preview.
	 *
	 * @since 4.5.2
	 *
	 * @return boolean Whether or not we are in the front-end preview.
	 */
	public function isPreview() {
		return function_exists( 'fusion_is_preview_frame' ) && fusion_is_preview_frame();
	}

	/**
	 * Adds the sidebar wrapper in footer when is in page builder.
	 *
	 * @since 4.5.2
	 *
	 * @return void
	 */
	public function addSidebarWrapper() {
		if ( ! $this->isBuilder() ) {
			return;
		}

		echo '<div id="fusion-builder-aioseo-sidebar"></div>';
	}

	/**
	 * Enqueue the scripts and styles.
	 *
	 * @since 4.5.2
	 *
	 * @return void
	 */
	public function enqueue() {
		if ( ! aioseo()->postSettings->canAddPostSettingsMetabox( get_post_type( $this->getPostId() ) ) ) {
			return;
		}

		parent::enqueue();
	}

	/**
	 * Returns whether or not the given Post ID was built with WPBakery.
	 *
	 * @since 4.5.2
	 *
	 * @param  int $postId The Post ID.
	 * @return boolean     Whether or not the Post was built with WPBakery.
	 */
	public function isBuiltWith( $postId ) {
		return 'active' === get_post_meta( $postId, 'fusion_builder_status', true );
	}

	/**
	 * Returns whether should or not limit the modified date.
	 *
	 * @since 4.5.2
	 *
	 * @param  int     $postId The Post ID.
	 * @return boolean         Whether or not sholud limit the modified date.
	 */
	public function limitModifiedDate( $postId ) {
		// This method is supposed to be used in the `wp_ajax_fusion_app_save_post_content` action.
		if ( ! isset( $_POST['fusion_load_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['fusion_load_nonce'] ) ), 'fusion_load_nonce' ) ) {
			return false;
		}

		$editorPostId = ! empty( $_REQUEST['post_id'] ) ? intval( $_REQUEST['post_id'] ) : 0;
		if ( $editorPostId !== $postId ) {
			return false;
		}

		return ! empty( $_REQUEST['query']['aioseo_limit_modified_date'] );
	}
}