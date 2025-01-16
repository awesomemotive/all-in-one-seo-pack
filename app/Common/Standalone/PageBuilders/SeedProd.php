<?php
namespace AIOSEO\Plugin\Common\Standalone\PageBuilders;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Integrate our SEO Panel with SeedProd Page Builder.
 *
 * @since 4.1.7
 */
class SeedProd extends Base {
	/**
	 * The plugin files.
	 *
	 * @since 4.1.7
	 *
	 * @var array
	 */
	public $plugins = [
		'coming-soon/coming-soon.php',
		'seedprod-coming-soon-pro-5/seedprod-coming-soon-pro-5.php',
	];

	/**
	 * The integration slug.
	 *
	 * @since 4.1.7
	 *
	 * @var string
	 */
	public $integrationSlug = 'seedprod';

	/**
	 * Init the integration.
	 *
	 * @since 4.1.7
	 *
	 * @return void
	 */
	public function init() {
		$postType = get_post_type( $this->getPostId() );

		if ( ! aioseo()->postSettings->canAddPostSettingsMetabox( $postType ) ) {
			return;
		}

		// SeedProd de-enqueues and de-register scripts/styles on priority PHP_INT_MAX.
		// Thus, we need to enqueue our scripts at the same priority for more compatibility.
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue' ], PHP_INT_MAX );
		add_filter( 'style_loader_tag', [ $this, 'replaceStyleTag' ], 10, 2 );
	}

	/**
	 * Enqueue the scripts and styles.
	 *
	 * @since 4.1.7
	 *
	 * @return void
	 */
	public function enqueue() {
		if ( ! $this->isBuilderScreen() ) {
			return;
		}

		parent::enqueue();
	}

	/**
	 * Check whether or not is builder screen.
	 *
	 * @since 4.1.7
	 *
	 * @return boolean Whether or not is builder screen.
	 */
	public function isBuilderScreen() {
		$currentScreen = aioseo()->helpers->getCurrentScreen();

		return $currentScreen && preg_match( '/seedprod.*?_builder$/i', (string) $currentScreen->base );
	}

	/**
	 * Replace original tag to prevent being removed by SeedProd.
	 *
	 * @param  string $tag    The <link> tag for the enqueued style.
	 * @param  string $handle The style's registered handle.
	 * @return string         The tag.
	 */
	public function replaceStyleTag( $tag, $handle = '' ) {
		if ( ! $this->isBuilderScreen() ) {
			return $tag;
		}

		$aioseoCommonHandle = 'aioseo-' . $this->integrationSlug . '-common';

		if ( $aioseoCommonHandle === $handle ) {
			// All the *common.css links are removed from SeedProd.
			// https://github.com/awesomemotive/seedprod-plugins/blob/32854442979bfa068aadf9b8a8a929e5f9f353e5/seedprod-pro/resources/views/builder.php#L406
			$tag = str_ireplace( 'href=', 'data-href=', $tag );
		}

		return $tag;
	}

	/**
	 * Returns whether or not the given Post ID was built with SeedProd.
	 *
	 * @since 4.1.7
	 *
	 * @param  int $postId The Post ID.
	 * @return boolean     Whether or not the Post was built with SeedProd.
	 */
	public function isBuiltWith( $postId ) {
		$isSeedProd = get_post_meta( $postId, '_seedprod_page', true );
		if ( ! empty( $isSeedProd ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Checks whether or not we should prevent the date from being modified.
	 *
	 * @since 4.5.2
	 *
	 * @param  int  $postId The Post ID.
	 * @return bool         Whether or not we should prevent the date from being modified.
	 */
	public function limitModifiedDate( $postId ) {
		// This method is supposed to be used in the `wp_ajax_seedprod_pro_save_lpage` action.
		if ( wp_doing_ajax() && ! check_ajax_referer( 'seedprod_nonce', false, false ) ) {
			return false;
		}

		$landingPageId = ! empty( $_REQUEST['lpage_id'] ) ? (int) $_REQUEST['lpage_id'] : false;
		if ( $landingPageId !== $postId ) {
			return false;
		}

		$settings = ! empty( $_REQUEST['settings'] ) ? json_decode( sanitize_text_field( wp_unslash( $_REQUEST['settings'] ) ) ) : false;
		if ( empty( $settings ) || empty( $settings->aioseo_limit_modified_date ) ) {
			return false;
		}

		return true;
	}
}