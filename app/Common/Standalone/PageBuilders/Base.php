<?php
namespace AIOSEO\Plugin\Common\Standalone\PageBuilders;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Base class for each of our page builder integrations.
 *
 * @since 4.1.7
 */
abstract class Base {
	/**
	 * The plugin files we can integrate with.
	 *
	 * @since 4.1.7
	 *
	 * @var array
	 */
	public $plugins = [];

	/**
	 * The themes names we can integrate with.
	 *
	 * @since 4.1.7
	 *
	 * @var array
	 */
	public $themes = [];

	/**
	 * The integration slug.
	 *
	 * @since 4.1.7
	 *
	 * @var string
	 */
	public $integrationSlug = '';

	/**
	 * The prefix shared by the builder's shortcode tags, e.g. `vc_`.
	 * Empty for builders whose content is not shortcode based.
	 *
	 * @since 5.0.3
	 *
	 * @var string
	 */
	protected $shortcodePrefix = '';

	/**
	 * Opening-tag attributes that hold visible copy, e.g. a module title.
	 *
	 * NOTE: Fallback for builders that expose no element map on front-end requests.
	 * {@see getTextAttributes()} reads the map where one is available.
	 *
	 * @since 5.0.3
	 *
	 * @var array
	 */
	protected $textAttributes = [];

	/**
	 * Class constructor.
	 *
	 * @since 4.1.7
	 *
	 * @return void
	 */
	public function __construct() {
		// We need to delay it to give other plugins a chance to register custom post types.
		add_action( 'init', [ $this, '_init' ], PHP_INT_MAX );
	}

	/**
	 * The internal init function.
	 *
	 * @since 4.1.7
	 *
	 * @return void
	 */
	public function _init() {
		// Check if we do have an integration slug.
		if ( empty( $this->integrationSlug ) ) {
			return;
		}

		// Check if the plugin or theme to integrate with is active.
		if ( ! $this->isPluginActive() && ! $this->isThemeActive() ) {
			return;
		}

		// Check if we can proceed with the integration.
		if ( apply_filters( 'aioseo_page_builder_integration_disable', false, $this->integrationSlug ) ) {
			return;
		}

		$this->init();
	}

	/**
	 * The init function.
	 *
	 * @since 4.1.7
	 *
	 * @return void
	 */
	public function init() {}

	/**
	 * Check if the integration is active.
	 *
	 * @since 4.4.8
	 *
	 * @return bool Whether or not the integration is active.
	 */
	public function isActive() {
		return $this->isPluginActive() || $this->isThemeActive();
	}

	/**
	 * Check whether or not the plugin is active.
	 *
	 * @since 4.1.7
	 *
	 * @return bool Whether or not the plugin is active.
	 */
	public function isPluginActive() {
		include_once ABSPATH . 'wp-admin/includes/plugin.php';

		$plugins = apply_filters( 'aioseo_page_builder_integration_plugins', $this->plugins, $this->integrationSlug );

		foreach ( $plugins as $basename ) {
			if ( is_plugin_active( $basename ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check whether or not the theme is active.
	 *
	 * @since 4.1.7
	 *
	 * @return bool Whether or not the theme is active.
	 */
	public function isThemeActive() {
		$themes = apply_filters( 'aioseo_page_builder_integration_themes', $this->themes, $this->integrationSlug );

		$theme = wp_get_theme();
		foreach ( $themes as $name ) {
			if ( $name === $theme->stylesheet || $name === $theme->template ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Enqueue the scripts and styles.
	 *
	 * @since 4.1.7
	 *
	 * @return void
	 */
	public function enqueue() {
		$integrationSlug = $this->integrationSlug;
		aioseo()->core->assets->load( "src/vue/standalone/page-builders/$integrationSlug/main.js", [], aioseo()->helpers->getVueData( 'post', $this->getPostId(), $integrationSlug ) );

		aioseo()->core->assets->enqueueCss( 'src/vue/assets/scss/integrations/main.scss' );

		aioseo()->admin->addAioseoModalPortal();
		aioseo()->main->enqueueTranslations();
	}

	/**
	 * Get the post ID.
	 *
	 * @since 4.1.7
	 *
	 * @return int|null The post ID or null.
	 */
	public function getPostId() {
		// phpcs:disable HM.Security.NonceVerification.Recommended, WordPress.Security.NonceVerification.Recommended
		foreach ( [ 'id', 'post', 'post_id' ] as $key ) {
			if ( ! empty( $_GET[ $key ] ) ) {
				return (int) sanitize_text_field( wp_unslash( $_GET[ $key ] ) );
			}
		}
		// phpcs:enable

		if ( ! empty( $GLOBALS['post'] ) ) {
			return (int) $GLOBALS['post']->ID;
		}

		return null;
	}

	/**
	 * Returns the page builder edit url for the given Post ID.
	 *
	 * @since 4.3.1
	 *
	 * @param  int    $postId The Post ID.
	 * @return string         The Edit URL.
	 */
	public function getEditUrl( $postId ) { // phpcs:disable VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
		return '';
	}

	/**
	 * Returns whether or not the given Post ID was built with the Page Builder.
	 *
	 * @since 4.1.7
	 *
	 * @param  int $postId The Post ID.
	 * @return boolean     Whether or not the Post was built with the Page Builder.
	 */
	public function isBuiltWith( $postId ) { // phpcs:disable VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
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
	public function limitModifiedDate( $postId ) { // phpcs:disable VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
		return false;
	}

	/**
	 * Returns the processed page builder content.
	 *
	 * @since   4.5.2
	 * @version 5.0.2 Moved the raw content fallback to {@see getRawContent()}.
	 *
	 * @param  int    $postId  The post id.
	 * @param  mixed  $content The raw content.
	 * @return string          The processed content.
	 */
	public function processContent( $postId, $content = null ) {
		$content = $this->getRawContent( $postId, $content );

		if ( aioseo()->helpers->isAjaxCronRestRequest() && ! doing_filter( 'the_content' ) ) {
			return apply_filters( 'the_content', $content ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
		}

		return $content;
	}

	/**
	 * Returns the given content, or the post's raw content when empty.
	 *
	 * @since 5.0.2
	 *
	 * @param  int    $postId  The post ID.
	 * @param  mixed  $content The raw content.
	 * @return string          The raw content.
	 */
	protected function getRawContent( $postId, $content ) {
		if ( empty( $content ) ) {
			$post = get_post( $postId );
			if ( is_a( $post, 'WP_Post' ) ) {
				$content = $post->post_content;
			}
		}

		return (string) $content;
	}

	/**
	 * Returns whether the integration extracts text instead of rendering on front-end views.
	 *
	 * NOTE: The shortcode builders only render in AJAX/cron/REST. Everywhere else {@see processContent()}
	 * returns the raw content or the copy from {@see extractShortcodeText()}, never a render.
	 *
	 * @since 5.0.3
	 *
	 * @return bool Whether front-end views get text, not a render.
	 */
	public function extractsTextOnFrontEnd() {
		return '' !== $this->shortcodePrefix;
	}

	/**
	 * Reduces the builder's raw shortcode markup to plain readable text without rendering it.
	 *
	 * Drops only the builder's own tags - keeping the copy enclosed between them plus the text-bearing
	 * attributes on their opening tags - so the downstream strip_shortcodes() cannot delete that copy.
	 * Bracket literals ([2023]), escaped `[[…]]` tags and other plugins' shortcodes are left untouched.
	 * Quoted attribute values are treated as opaque, so an inner "]" does not truncate a tag.
	 *
	 * NOTE: Pure text extraction on purpose. Executing a builder's shortcodes out of the main render
	 * and again during it breaks their per-instance state and consumes their one-shot asset output.
	 *
	 * @since 5.0.3
	 *
	 * @param  string $content The raw shortcode content.
	 * @return string          The extracted text.
	 */
	protected function extractShortcodeText( $content ) {
		$content = (string) $content;
		if ( '' === $this->shortcodePrefix ) {
			return $content;
		}

		// Match a builder shortcode tag, treating quoted attribute values as opaque so an inner "]"
		// (e.g. title="See [PDF] guide") does not terminate the tag early.
		$tagPattern = '/(?<!\[)(\[\/?' . preg_quote( $this->shortcodePrefix, '/' ) . '(?:[^\]"\']|"[^"]*"|\'[^\']*\')*\])(?!\])/is';

		// Alternating pieces: even offsets are the copy between tags, odd offsets are the tags.
		$pieces  = (array) preg_split( $tagPattern, $content, -1, PREG_SPLIT_DELIM_CAPTURE );
		$parts   = [];
		$openTag = '';
		foreach ( $pieces as $index => $piece ) {
			if ( 1 === $index % 2 ) {
				$openTag = 0 === strpos( $piece, '[/' ) ? '' : $this->getTagName( $piece );
				$parts[] = '' === $openTag ? ' ' : $this->getTagText( $openTag, $piece );

				continue;
			}

			// An element that stores its body encoded or as code has no copy to salvage, and leaving the
			// blob in place would publish it verbatim once the tags around it are gone.
			if ( '' === trim( $piece ) || ( '' !== $openTag && $this->isRawBody( $openTag, trim( $piece ) ) ) ) {
				continue;
			}

			$parts[] = $piece;
		}

		return trim( (string) preg_replace( '/\s+/', ' ', implode( ' ', $parts ) ) );
	}

	/**
	 * Returns the attribute names that hold visible copy on the given tag.
	 *
	 * @since 5.0.3
	 *
	 * @param  string $tag The shortcode tag.
	 * @return array       The attribute names.
	 */
	protected function getTextAttributes( $tag ) { // phpcs:disable VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
		return $this->textAttributes;
	}

	/**
	 * Returns whether the given tag stores its enclosed body encoded or as code rather than as copy.
	 *
	 * @since 5.0.3
	 *
	 * @param  string $tag  The shortcode tag.
	 * @param  string $body The enclosed body.
	 * @return bool         Whether the body is unreadable.
	 */
	protected function isRawBody( $tag, $body ) { // phpcs:disable VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
		return false;
	}

	/**
	 * Returns the tag name from a raw shortcode tag.
	 *
	 * @since 5.0.3
	 *
	 * @param  string $rawTag The raw shortcode tag, brackets included.
	 * @return string         The tag name.
	 */
	private function getTagName( $rawTag ) {
		preg_match( '/^\[\/?([\w-]+)/', $rawTag, $matches );

		return isset( $matches[1] ) ? $matches[1] : '';
	}

	/**
	 * Returns the visible copy held in the given opening tag's attributes.
	 *
	 * @since 5.0.3
	 *
	 * @param  string $tag    The tag name.
	 * @param  string $rawTag The raw opening tag, brackets included.
	 * @return string         The copy, space padded, or a single space.
	 */
	private function getTagText( $tag, $rawTag ) {
		$attributes = $this->getTextAttributes( $tag );
		if ( empty( $attributes ) ) {
			return ' ';
		}

		$quotedAttributes = [];
		foreach ( $attributes as $attribute ) {
			$quotedAttributes[] = preg_quote( $attribute, '/' );
		}

		$parts = [];
		if ( preg_match_all( '/\b(' . implode( '|', $quotedAttributes ) . ')=(["\'])(.*?)\2/is', $rawTag, $matches ) ) {
			foreach ( $matches[3] as $index => $value ) {
				$value = trim( $this->decodeAttributeValue( $value ) );
				if ( '' !== $value && $this->isCopyValue( $value, strtolower( $matches[1][ $index ] ) ) ) {
					$parts[] = $value;
				}
			}
		}

		return [] === $parts ? ' ' : ' ' . implode( ' ', $parts ) . ' ';
	}

	/**
	 * Returns whether the given attribute value is visible copy.
	 *
	 * NOTE: Only builders that infer their attribute names need this. A curated
	 * {@see $textAttributes} list already excludes the attributes that hold machine values.
	 *
	 * @since 5.0.3
	 *
	 * @param  string $value     The attribute value.
	 * @param  string $attribute The attribute name.
	 * @return bool              Whether the value is copy.
	 */
	protected function isCopyValue( $value, $attribute ) { // phpcs:disable VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
		return true;
	}

	/**
	 * Returns the given attribute value with the builder's own encoding undone.
	 *
	 * @since 5.0.3
	 *
	 * @param  string $value The raw attribute value.
	 * @return string        The decoded value.
	 */
	protected function decodeAttributeValue( $value ) {
		return $value;
	}
}