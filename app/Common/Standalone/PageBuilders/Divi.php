<?php
namespace AIOSEO\Plugin\Common\Standalone\PageBuilders;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Integrate our SEO Panel with Divi Page Builder.
 *
 * @since 4.1.7
 */
class Divi extends Base {
	/**
	 * The theme name.
	 *
	 * @since 4.1.7
	 *
	 * @var array
	 */
	public $themes = [ 'Divi', 'Extra' ];

	/**
	 * The plugin files.
	 *
	 * @since 4.2.0
	 *
	 * @var array
	 */
	public $plugins = [
		'divi-builder/divi-builder.php'
	];

	/**
	 * The integration slug.
	 *
	 * @since 4.1.7
	 *
	 * @var string
	 */
	public $integrationSlug = 'divi';

	/**
	 * Init the integration.
	 *
	 * @since 4.1.7
	 *
	 * @return void
	 */
	public function init() {
		add_action( 'wp', [ $this, 'maybeRun' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueueAdmin' ] );
	}

	/**
	 * Check if we are in the Page Builder and run the integrations.
	 *
	 * @since 4.1.7
	 *
	 * @return void
	 */
	public function maybeRun() {
		$postType = get_post_type( $this->getPostId() );

		if (
			! defined( 'ET_BUILDER_PRODUCT_VERSION' ) ||
			! version_compare( '4.9.2', ET_BUILDER_PRODUCT_VERSION, '<=' ) ||
			! ( function_exists( 'et_core_is_fb_enabled' ) && et_core_is_fb_enabled() ) ||
			! aioseo()->postSettings->canAddPostSettingsMetabox( $postType )
		) {
			return;
		}

		add_action( 'wp_footer', [ $this, 'addContainers' ] );
		add_action( 'wp_footer', [ $this, 'addIframeWatcher' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue' ] );
		add_filter( 'script_loader_tag', [ $this, 'addEtTag' ], 10, 2 );
	}

	/**
	 * Enqueue the required scripts for the admin screen.
	 *
	 * @since 4.1.7
	 *
	 * @return void
	 */
	public function enqueueAdmin() {
		if ( ! aioseo()->helpers->isScreenBase( 'toplevel_page_et_divi_options' ) ) {
			return;
		}

		aioseo()->core->assets->load( 'src/vue/standalone/divi-admin/main.js', [], aioseo()->helpers->getVueData() );

		aioseo()->main->enqueueTranslations();
	}

	/**
	 * Add et attributes to script tags.
	 *
	 * @since 4.1.7
	 *
	 * @param  string $tag    The <script> tag for the enqueued script.
	 * @param  string $handle The script's registered handle.
	 * @return string         The tag.
	 */
	public function addEtTag( $tag, $handle = '' ) {
		$scriptHandles = [
			'aioseo/js/src/vue/standalone/divi/main.js',
			'aioseo/js/src/vue/standalone/app/main.js'
		];

		if ( in_array( $handle, $scriptHandles, true ) ) {
			// These tags load in parent window only, not in Divi iframe.
			return preg_replace( '/<script/', '<script class="et_fb_ignore_iframe"', $tag );
		}

		return $tag;
	}

	/**
	 * Add the Divi watcher.
	 *
	 * @since 4.1.7
	 *
	 * @return void
	 */
	public function addIframeWatcher() {
		?>
		<script type="text/javascript">
			if (typeof jQuery === 'function') {
				jQuery(window).on('et_builder_api_ready et_fb_section_content_change', function(event) {
					window.parent.postMessage({ eventType : event.type })
				})
			}
		</script>
		<?php
	}

	/**
	 * Add the containers to mount our panel.
	 *
	 * @since 4.1.7
	 *
	 * @return void
	 */
	public function addContainers() {
		echo '<div id="aioseo-app-modal" class="et_fb_ignore_iframe"><div class="et_fb_ignore_iframe"></div></div>';
		echo '<div id="aioseo-settings" class="et_fb_ignore_iframe"></div>';
		echo '<div id="aioseo-admin" class="et_fb_ignore_iframe"></div>';
		echo '<div id="aioseo-modal-portal" class="et_fb_ignore_iframe"></div>';
	}

	/**
	 * Returns whether or not the given Post ID was built with Divi.
	 *
	 * @since 4.1.7
	 *
	 * @param  int $postId The Post ID.
	 * @return boolean     Whether or not the Post was built with Divi.
	 */
	public function isBuiltWith( $postId ) {
		if ( ! function_exists( 'et_pb_is_pagebuilder_used' ) ) {
			return false;
		}

		return et_pb_is_pagebuilder_used( $postId );
	}

	/**
	 * Returns the Divi edit url for the given Post ID.
	 *
	 * @since 4.3.1
	 *
	 * @param  int    $postId The Post ID.
	 * @return string         The Edit URL.
	 */
	public function getEditUrl( $postId ) {
		if ( ! function_exists( 'et_fb_get_vb_url' ) ) {
			return '';
		}

		$isDiviLibrary = 'et_pb_layout' === get_post_type( $postId );
		$editUrl       = $isDiviLibrary ? get_edit_post_link( $postId, 'raw' ) : get_permalink( $postId );

		if ( et_pb_is_pagebuilder_used( $postId ) ) {
			$editUrl = et_fb_get_vb_url( $editUrl );
		} else {
			if ( ! et_pb_is_allowed( 'divi_builder_control' ) ) {
				// Prevent link when user lacks `Toggle Divi Builder` capability.
				return '';
			}

			$editUrl = add_query_arg(
				[ 'et_fb_activation_nonce' => wp_create_nonce( 'et_fb_activation_nonce_' . $postId ) ],
				$editUrl
			);
		}

		return $editUrl;
	}
}