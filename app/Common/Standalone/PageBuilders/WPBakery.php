<?php
namespace AIOSEO\Plugin\Common\Standalone\PageBuilders;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Integrate our SEO Panel with WPBakery Page Builder.
 *
 * @since 4.5.2
 */
class WPBakery extends Base {
	/**
	 * The plugin files.
	 *
	 * @since 4.5.2
	 *
	 * @var array
	 */
	public $plugins = [
		'js_composer/js_composer.php'
	];

	/**
	 * The integration slug.
	 *
	 * @since 4.5.2
	 *
	 * @var string
	 */
	public $integrationSlug = 'wpbakery';

	/**
	 * Init the integration.
	 *
	 * @since 4.5.2
	 *
	 * @return void
	 */
	public function init() {
		if ( ! aioseo()->postSettings->canAddPostSettingsMetabox( get_post_type( $this->getPostId() ) ) ) {
			return;
		}

		add_action( 'vc_frontend_editor_enqueue_js_css', [ $this, 'enqueue' ] );
		add_action( 'vc_backend_editor_enqueue_js_css', [ $this, 'enqueue' ] );

		add_filter( 'vc_nav_front_controls', [ $this, 'addNavbarCotnrols' ] );
		add_filter( 'vc_nav_controls', [ $this, 'addNavbarCotnrols' ] );
	}

	public function addNavbarCotnrols( $controlList ) {
		$controlList[] = [
			'aioseo',
			'<li class="vc_show-mobile"><div id="aioseo-wpbakery" style="height: 100%;"></div></li>'
		];

		return $controlList;
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
		$postObj = get_post( $postId );
		if ( ! empty( $postObj ) && preg_match( '/vc_row/', $postObj->post_content ) ) {
			return true;
		}

		return false;
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
		// This method is supposed to be used in the `saveAjaxFe` action.
		if ( empty( $_REQUEST['_vcnonce'] ) || ! wp_verify_nonce( wp_unslash( $_REQUEST['_vcnonce'] ), 'vc-nonce-vc-admin-nonce' ) ) {
			return false;
		}

		$editorPostId = ! empty( $_REQUEST['post_id'] ) ? intval( $_REQUEST['post_id'] ) : 0;
		if ( $editorPostId !== $postId ) {
			return false;
		}

		return ! empty( $_REQUEST['aioseo_limit_modified_date'] ) && (bool) $_REQUEST['aioseo_limit_modified_date'];
	}

	/**
	 * Returns the processed page builder content.
	 *
	 * @since 4.5.2
	 *
	 * @param  int    $postId  The post id.
	 * @param  string $content The raw content.
	 * @return string          The processed content.
	 */
	public function processContent( $postId, $content = '' ) {
		\WPBMap::addAllMappedShortcodes();

		return parent::processContent( $postId, $content );
	}
}