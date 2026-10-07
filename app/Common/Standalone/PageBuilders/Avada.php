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
	 * The prefix shared by the builder's shortcode tags.
	 *
	 * @since 5.0.3
	 *
	 * @var string
	 */
	protected $shortcodePrefix = 'fusion_';

	/**
	 * Opening-tag attributes that hold visible copy, e.g. a module title.
	 *
	 * NOTE: Listed rather than read from Fusion's element map, because fusion_builder_map() only fills
	 * in a element's params inside wp-admin, so no element is registered on a front-end request.
	 *
	 * @since 5.0.3
	 *
	 * @var array
	 */
	protected $textAttributes = [
		'title',
		'title_front',
		'title_back',
		'text_front',
		'long_title',
		'caption_title',
		'heading',
		'heading_text',
		'subheading_text',
		'tagline',
		'before_text',
		'after_text',
		'highlight_text',
		'link_text',
		'linktext',
		'button',
		'long_text',
		'caption_text',
		'caption',
		'before_label',
		'after_label',
		'home_label',
		'description',
		'name',
		'company'
	];

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
		if ( ! aioseo()->postSettings->canAddPageBuilderMetabox( get_post_type( $this->getPostId() ) ) ) {
			return;
		}

		parent::enqueue();
	}

	/**
	 * Returns the attribute names that hold visible copy on the given tag.
	 *
	 * NOTE: `name` is copy only on Person and Testimonial. Everywhere else Fusion uses it for an element
	 * id or slug (menu anchors, widget areas, modals, form fields).
	 *
	 * @since 5.0.3
	 *
	 * @param  string $tag The shortcode tag.
	 * @return array       The attribute names.
	 */
	protected function getTextAttributes( $tag ) {
		if ( in_array( $tag, [ 'fusion_person', 'fusion_testimonial' ], true ) ) {
			return $this->textAttributes;
		}

		return array_diff( $this->textAttributes, [ 'name' ] );
	}

	/**
	 * Returns whether the given tag stores its enclosed body encoded or as code rather than as copy.
	 *
	 * Fusion's Code Block and Syntax Highlighter store their body base64 encoded and decode it in their
	 * own render, so the blob would otherwise survive into the description once the tags around it are
	 * dropped. Every other element decodes attributes, never its body.
	 *
	 * @since 5.0.3
	 *
	 * @param  string $tag  The shortcode tag.
	 * @param  string $body The enclosed body.
	 * @return bool         Whether the body is unreadable.
	 */
	protected function isRawBody( $tag, $body ) { // phpcs:disable VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
		return in_array( $tag, [ 'fusion_code', 'fusion_syntax_highlighter' ], true );
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

	/**
	 * Returns the processed page builder content.
	 *
	 * @since   4.9.9
	 * @version 5.0.3 Extract text from the raw markup on front-end views instead of rendering it;
	 *                include the text of global elements.
	 *
	 * @param  int    $postId  The post id.
	 * @param  mixed  $content The raw content.
	 * @return string          The processed content.
	 */
	public function processContent( $postId, $content = null ) {
		$content = parent::processContent( $postId, $content ?? '' );

		// Avada/Fusion Builder content is purely [fusion_*] shortcodes; the parent only expands them in
		// AJAX/cron/REST, so on front-end views they survive to strip_shortcodes() and the auto-description
		// comes out empty. Read the copy straight out of the markup instead: Fusion elements build state as
		// they render (the form components index a global registry), so running them out of Fusion's own
		// pipeline throws before the page ever reaches the browser.
		if ( ! is_admin() && ! aioseo()->helpers->isAjaxCronRestRequest() && ! doing_filter( 'the_content' ) ) {
			$content = $this->extractShortcodeText( $this->expandGlobalElements( $content ) );
		}

		return $content;
	}

	/**
	 * Replaces each global element tag with the content of the library element it points to.
	 *
	 * NOTE: A global element only holds the ID of an Avada Library element, so the extractor has no copy to read from it.
	 *
	 * @since 5.0.3
	 *
	 * @param  string $content The raw shortcode content.
	 * @param  int[]  $visited The IDs of the global elements being expanded.
	 * @return string          The content.
	 */
	private function expandGlobalElements( $content, $visited = [] ) {
		return (string) preg_replace_callback( '/\[fusion_global\s+id=(["\']?)(\d+)\1[^\]]*\]/i', function ( $matches ) use ( $visited ) {
			$elementId = (int) $matches[2];
			$element   = get_post( $elementId );

			// A global element can contain another one, or itself, so each is expanded once per branch.
			if (
				in_array( $elementId, $visited, true ) ||
				! is_a( $element, 'WP_Post' ) ||
				'fusion_element' !== $element->post_type
			) {
				return ' ';
			}

			$visited[] = $elementId;

			return ' ' . $this->expandGlobalElements( $element->post_content, $visited ) . ' ';
		}, (string) $content );
	}
}