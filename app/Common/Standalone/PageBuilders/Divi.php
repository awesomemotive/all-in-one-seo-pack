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
			! aioseo()->postSettings->canAddPageBuilderMetabox( $postType )
		) {
			return;
		}

		// Divi 5 renders content inside an iframe that triggers a full page load with `app_window=1`.
		// Our integration runs in the parent (top) window only; skip the iframe context entirely.
		if ( isset( $_GET['app_window'] ) && '1' === $_GET['app_window'] ) { // phpcs:ignore HM.Security.NonceVerification.Recommended, WordPress.Security.NonceVerification.Recommended
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

		aioseo()->core->assets->load( 'src/vue/standalone/page-builders/divi-admin/main.js', [], aioseo()->helpers->getVueData() );

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
			'aioseo/js/src/vue/standalone/page-builders/divi/main.js',
			'aioseo/js/src/vue/standalone/app/main.js'
		];

		if ( in_array( $handle, $scriptHandles, true ) ) {
			// These tags load in parent window only, not in Divi iframe.
			return preg_replace( '/<script/', '<script class="et_fb_ignore_iframe"', (string) $tag );
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

	/**
	 * Returns the processed page builder content.
	 *
	 * @since   4.9.6
	 * @version 4.9.9 Reset Divi's module order index after the front-end the_content() pass.
	 *
	 * @param  int    $postId  The post ID.
	 * @param  mixed  $content The raw content.
	 * @return string          The processed content.
	 */
	public function processContent( $postId, $content = null ) {
		$templateVersion = aioseo()->helpers->getThemeVersion( true ) ?? aioseo()->helpers->getThemeVersion();

		// Divi 5+ stores content as blocks that only render to text through the_content;
		// do_blocks() (the parent's safe path) returns empty for them.
		if (
			version_compare( (string) $templateVersion, '5.0', '>=' ) &&
			! doing_filter( 'the_content' )
		) {
			return $this->renderDivi5ContentForExtraction( (string) $content );
		}

		return parent::processContent( $postId, $content );
	}

	/**
	 * Renders Divi 5+ block content via the_content for text extraction.
	 *
	 * NOTE: On the front end this advances Divi's static-CSS module order index, so we reset it
	 * afterward to keep the real page render's per-index classes (and cached CSS) intact.
	 *
	 * @since 4.9.9
	 *
	 * @param  string $content The raw block content.
	 * @return string          The rendered content.
	 */
	private function renderDivi5ContentForExtraction( $content ) {
		// In AJAX/cron/REST, Divi does not generate cached static CSS, so there is no order-index
		// side effect to undo. On front-end views there is, so reset it after rendering.
		$resetOrderIndex = ! aioseo()->helpers->isAjaxCronRestRequest()
			&& is_callable( [ 'ET_Builder_Module_Order', 'reset_all_indexes' ] );

		try {
			return apply_filters( 'the_content', $content ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
		} finally {
			if ( $resetOrderIndex ) {
				\ET_Builder_Module_Order::reset_all_indexes();
			}
		}
	}

	/**
	 * Checks whether or not we should prevent the date from being modified.
	 *
	 * @since   4.5.2
	 * @version 4.9.6 Refactored to separate Divi 5+ and Divi 4.x logic.
	 *
	 * @param  int  $postId The Post ID.
	 * @return bool         Whether or not we should prevent the date from being modified.
	 */
	public function limitModifiedDate( $postId ) {
		$templateVersion = aioseo()->helpers->getThemeVersion( true ) ?? aioseo()->helpers->getThemeVersion();

		return version_compare( $templateVersion, '5.0', '>=' )
			? $this->limitModifiedDateDivi5( $postId )
			: $this->limitModifiedDateLegacy( $postId );
	}

	/**
	 * Limit modified date check for Divi 5+.
	 * Divi 5 saves via REST API and uses a cookie to signal the limit modified date flag.
	 *
	 * @since 4.9.6
	 *
	 * @param  int  $postId The Post ID.
	 * @return bool         Whether to limit the modified date.
	 */
	private function limitModifiedDateDivi5( $postId ) {
		$cookiePostId = ! empty( $_COOKIE['aioseo_limit_modified_date'] ) ? (int) $_COOKIE['aioseo_limit_modified_date'] : 0;

		return $cookiePostId === $postId;
	}

	/**
	 * Limit modified date check for Divi 4.x (legacy).
	 * Divi 4.x saves via AJAX (wp_ajax_et_fb_ajax_save) with nonce verification.
	 *
	 * @since 4.9.6
	 *
	 * @param  int  $postId The Post ID.
	 * @return bool         Whether to limit the modified date.
	 */
	private function limitModifiedDateLegacy( $postId ) {
		// This method is supposed to be used in the `wp_ajax_et_fb_ajax_save` action.
		if ( empty( $_REQUEST['et_fb_save_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['et_fb_save_nonce'] ) ), 'et_fb_save_nonce' ) ) {
			return false;
		}

		$editorPostId = ! empty( $_REQUEST['post_id'] ) ? intval( $_REQUEST['post_id'] ) : 0;
		if ( $editorPostId !== $postId ) {
			return false;
		}

		return ! empty( $_REQUEST['options']['conditional_tags']['aioseo_limit_modified_date'] );
	}
}