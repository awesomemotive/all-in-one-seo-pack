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
	 * The prefix shared by the builder's shortcode tags.
	 *
	 * @since 5.0.3
	 *
	 * @var string
	 */
	protected $shortcodePrefix = 'et_pb_';

	/**
	 * Opening-tag attributes that hold visible copy, e.g. a module title.
	 *
	 * @since 5.0.3
	 *
	 * @var array
	 */
	protected $textAttributes = [ 'title', 'subhead', 'heading', 'button_text', 'button_one_text', 'button_two_text' ];

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
	 * Returns whether the integration extracts text instead of rendering on front-end views.
	 *
	 * NOTE: The prefix only feeds {@see extractShortcodeText()} inside {@see extractDivi5Text()}, whose
	 * front-end output must never be followed by a the_content() pass.
	 *
	 * @since 5.0.3
	 *
	 * @return bool Always false.
	 */
	public function extractsTextOnFrontEnd() {
		return false;
	}

	/**
	 * Returns the given attribute value with Divi's encoding undone.
	 *
	 * NOTE: Divi stores `"`, `\`, `[` and `]` in attribute values as %22, %92, %91 and %93.
	 *
	 * @since 5.0.3
	 *
	 * @param  string $value The raw attribute value.
	 * @return string        The decoded value.
	 */
	protected function decodeAttributeValue( $value ) {
		return str_replace( [ '%22', '%92', '%91', '%93' ], [ '"', '\\', '&#91;', '&#93;' ], $value );
	}

	/**
	 * Returns the processed page builder content.
	 *
	 * @since   4.9.6
	 * @version 4.9.9 Reset Divi's module order index after the front-end the_content() pass.
	 * @version 5.0.2 Extract Divi 5 text from the raw block markup on front-end requests instead of rendering it.
	 * @version 5.0.3 Reduce legacy et_pb tags to text with {@see extractShortcodeText()} when Divi cannot render them;
	 *                return an empty string when no text is left.
	 *
	 * @param  int    $postId  The post ID.
	 * @param  mixed  $content The raw content.
	 * @return string          The processed content.
	 */
	public function processContent( $postId, $content = null ) {
		$templateVersion = aioseo()->helpers->getThemeVersion( true ) ?? aioseo()->helpers->getThemeVersion();

		// Divi 5+ stores content as blocks whose text lives in block attributes; do_blocks()
		// (the parent's safe path) does not reliably render them across Divi 5 versions.
		if (
			version_compare( (string) $templateVersion, '5.0', '>=' ) &&
			! doing_filter( 'the_content' )
		) {
			$content = $this->getRawContent( $postId, $content );

			// In AJAX/cron/REST, Divi does not run its front-end asset pipeline, so a full render is safe
			// and gives the editor and SEO analysis the exact front-end markup.
			if ( aioseo()->helpers->isAjaxCronRestRequest() ) {
				return apply_filters( 'the_content', $content ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
			}

			// Plain text on purpose: it also defuses the runShortcodes=true path, where WpContext::theContent()
			// would otherwise run do_shortcode/do_blocks on this content out of the main loop. Divi indexes such
			// off-loop renders at its 10000 offset and caches their static CSS (e.g. et_pb_blurb_10000) into the
			// shared et-cache, which pollutes the real page render.
			return $this->extractDivi5Text( $content );
		}

		$content = $this->getRawContent( $postId, $content );

		// Legacy shortcode content cannot render where Divi registers no et_pb shortcodes (observed in WP-CLI
		// and wp-cron), so the tags would survive as-is. Reduce them to their copy before the parent's the_content
		// pass texturizes the attribute quotes, after which that copy can no longer be lifted.
		if ( ! shortcode_exists( 'et_pb_section' ) ) {
			$content = $this->extractShortcodeText( $content );

			// Nothing to pass on: the parent reads empty content as "none given" and reloads the raw tags.
			if ( '' === $content ) {
				return '';
			}
		}

		return parent::processContent( $postId, $content );
	}

	/**
	 * Extracts human-readable text from raw Divi 5 block markup without rendering it.
	 *
	 * NOTE: On front-end requests we must never run Divi 5 content through Divi's parse/render machinery.
	 * Divi swaps in its own block parser globally and advances its module order counters on every parse,
	 * an out-of-loop the_content pass consumes its one-shot order index resets, and rendered module styles
	 * pollute the static CSS registry it persists to et-cache - each of which breaks the real page render.
	 *
	 * @since 5.0.2
	 *
	 * @param  string $content The raw block content.
	 * @return string          The extracted text.
	 */
	private function extractDivi5Text( $content ) {
		if ( empty( $content ) ) {
			return '';
		}

		return $this->getDivi5BlockText( aioseo()->helpers->parseBlocksSafely( $content ) );
	}

	/**
	 * Recursively collects text from parsed Divi 5 blocks.
	 *
	 * Module text lives in `innerContent` attributes; inner HTML covers unconverted
	 * Divi 4 shortcode content inside Divi 5 placeholder blocks.
	 *
	 * @since 5.0.2
	 *
	 * @param  array  $blocks The parsed blocks.
	 * @return string         The collected text.
	 */
	private function getDivi5BlockText( $blocks ) {
		$parts = [];
		foreach ( $blocks as $block ) {
			if ( ! empty( $block['attrs'] ) && is_array( $block['attrs'] ) ) {
				$this->collectDivi5InnerContentText( $block['attrs'], $parts );
			}

			$innerHtml = trim( (string) ( $block['innerHTML'] ?? '' ) );
			if ( '' !== $innerHtml ) {
				// Lazily-unconverted Divi 4 pages parse as a freeform block whose innerHTML is the raw
				// shortcode string. Returning it verbatim would let the downstream strip_shortcodes()
				// delete the enclosed copy and empty the description, so reduce it to plain text here.
				if ( false !== strpos( $innerHtml, '[et_pb_' ) ) {
					$innerHtml = $this->extractShortcodeText( $innerHtml );
				}

				if ( '' !== $innerHtml ) {
					$parts[] = $innerHtml;
				}
			}

			if ( ! empty( $block['innerBlocks'] ) ) {
				$parts[] = $this->getDivi5BlockText( $block['innerBlocks'] );
			}
		}

		return implode( "\n", array_filter( $parts ) );
	}

	/**
	 * Recursively collects `innerContent` attribute values from a Divi 5 block attribute tree.
	 *
	 * Values follow the shape `{attribute}.innerContent.{breakpoint}.value` where the value is either
	 * an HTML/text string or an object with a `text` key (e.g. Blurb titles). Only the desktop
	 * breakpoint is read since the other breakpoints repeat the same content responsively.
	 *
	 * @since 5.0.2
	 *
	 * @param  array $attrs The block attributes (sub)tree.
	 * @param  array $parts The collected text parts, passed by reference.
	 * @return void
	 */
	private function collectDivi5InnerContentText( $attrs, &$parts ) {
		foreach ( $attrs as $key => $value ) {
			if ( ! is_array( $value ) ) {
				continue;
			}

			if ( 'innerContent' !== $key ) {
				$this->collectDivi5InnerContentText( $value, $parts );
				continue;
			}

			$desktopValue = $value['desktop']['value'] ?? null;
			if ( is_array( $desktopValue ) ) {
				$desktopValue = $desktopValue['text'] ?? null;
			}

			if ( ! is_string( $desktopValue ) ) {
				continue;
			}

			$desktopValue = trim( $desktopValue );

			// Dynamic content placeholders (e.g. "$variable({...})$") only resolve during a real render.
			if ( '' === $desktopValue || 0 === strpos( $desktopValue, '$variable(' ) ) {
				continue;
			}

			$parts[] = $desktopValue;
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