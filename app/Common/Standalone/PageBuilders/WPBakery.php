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
		'js_composer/js_composer.php',
		'js_composer_salient/js_composer.php'
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
	 * The prefix shared by the builder's shortcode tags.
	 *
	 * @since 5.0.3
	 *
	 * @var string
	 */
	protected $shortcodePrefix = 'vc_';

	/**
	 * Opening-tag attributes that hold visible copy, e.g. a module title.
	 *
	 * NOTE: Only the fallback for a tag WPBakery has not mapped. {@see getTextAttributes()}.
	 *
	 * @since 5.0.3
	 *
	 * @var array
	 */
	protected $textAttributes = [ 'title', 'text' ];

	/**
	 * WPBakery param types whose value is visible copy.
	 *
	 * @since 5.0.3
	 *
	 * @var array
	 */
	private $textParamTypes = [ 'textfield', 'textarea', 'textarea_html' ];

	/**
	 * WPBakery param types whose value is code or an encoded blob, never copy.
	 *
	 * NOTE: `textarea_raw_html` is the legacy Raw HTML type, still registered for third-party elements.
	 *
	 * @since 5.0.3
	 *
	 * @var array
	 */
	private $rawParamTypes = [ 'textarea_ace', 'textarea_raw_html' ];

	/**
	 * Param names WPBakery uses for machine values, by its own naming convention.
	 *
	 * @since 5.0.3
	 *
	 * @var string
	 */
	private $machineParamPattern = '/^(?:content|el_class|el_id|css|link|url|size|categories|exclude)$|(?:_el_class|_el_id|_css|_id|_key|_size|_src|_srcs|_url|_links|_query|_code)$|onclick/';

	/**
	 * Param names that hold a heading or label, where a value with no letter (e.g. `2024`) is still copy.
	 *
	 * @since 5.0.3
	 *
	 * @var string
	 */
	private $headingParamPattern = '/^(?:title|heading|subheading|text|label|caption|h[1-6])$|_title$/';

	/**
	 * Init the integration.
	 *
	 * @since 4.5.2
	 *
	 * @return void
	 */
	public function init() {
		// Disable SEO meta tags from WP Bakery.
		if ( defined( 'WPB_VC_VERSION' ) && version_compare( WPB_VC_VERSION, '7.4', '>=' ) ) {
			add_filter( 'get_post_metadata', [ $this, 'maybeDisableWpBakeryMetaTags' ], 10, 3 );
		}

		if ( ! aioseo()->postSettings->canAddPageBuilderMetabox( get_post_type( $this->getPostId() ) ) ) {
			return;
		}

		add_action( 'vc_frontend_editor_enqueue_js_css', [ $this, 'enqueue' ] );
		add_action( 'vc_backend_editor_enqueue_js_css', [ $this, 'enqueue' ] );

		add_filter( 'vc_nav_front_controls', [ $this, 'addNavbarControls' ] );
		add_filter( 'vc_nav_controls', [ $this, 'addNavbarControls' ] );
	}

	/**
	 * Maybe disable WP Bakery meta tags.
	 *
	 * @since 4.7.1
	 *
	 * @param  mixed  $value    The value of the meta.
	 * @param  int    $objectId The object ID.
	 * @param  string $metaKey  The meta key.
	 * @return mixed            The value of the meta.
	 */
	public function maybeDisableWpBakeryMetaTags( $value, $objectId, $metaKey ) {
		if ( is_singular() && '_wpb_post_custom_seo_settings' === $metaKey ) {
			return null;
		}

		return $value;
	}

	/**
	 * Add AIOSEO controls to the WPBakery navbar.
	 *
	 * @since 4.5.2
	 *
	 * @param  array $controlList The control list.
	 * @return array              The control list.
	 */
	public function addNavbarControls( $controlList ) {
		if ( ! $controlList ) {
			return $controlList;
		}

		remove_filter( 'vc_nav_front_controls', [ $this, 'addNavbarControls' ] );
		remove_filter( 'vc_nav_controls', [ $this, 'addNavbarControls' ] );

		$controlList[] = [
			'aioseo',
			'<li class="vc_show-mobile"><div id="aioseo-wpbakery" style="height: 100%;"></div></li>'
		];

		return $controlList;
	}

	/**
	 * Returns the attribute names that hold visible copy on the given tag.
	 *
	 * Read from WPBakery's own element map, which is registered on front-end requests too, so the
	 * copy-bearing attributes never have to be listed here and cannot fall behind new elements.
	 *
	 * @since 5.0.3
	 *
	 * @param  string $tag The shortcode tag.
	 * @return array       The attribute names.
	 */
	protected function getTextAttributes( $tag ) {
		static $attributes = [];
		if ( isset( $attributes[ $tag ] ) ) {
			return $attributes[ $tag ];
		}

		$attributes[ $tag ] = parent::getTextAttributes( $tag );

		$params = $this->getMappedParams( $tag );
		if ( empty( $params ) ) {
			return $attributes[ $tag ];
		}

		$names = [];
		foreach ( $params as $param ) {
			$name = isset( $param['param_name'] ) ? $param['param_name'] : '';
			$type = isset( $param['type'] ) ? $param['type'] : '';
			if ( '' === $name || ! in_array( $type, $this->textParamTypes, true ) ) {
				continue;
			}

			if ( preg_match( $this->machineParamPattern, $name ) ) {
				continue;
			}

			$names[] = $name;
		}

		if ( ! empty( $names ) ) {
			$attributes[ $tag ] = $names;
		}

		return $attributes[ $tag ];
	}

	/**
	 * Returns whether the given tag stores its enclosed body encoded or as code rather than as copy.
	 *
	 * Raw HTML and Raw JS store their body base64 encoded and decode it in their own render template,
	 * so the blob would otherwise survive into the description once the tags around it are dropped.
	 *
	 * @since 5.0.3
	 *
	 * @param  string $tag  The shortcode tag.
	 * @param  string $body The enclosed body.
	 * @return bool         Whether the body is unreadable.
	 */
	protected function isRawBody( $tag, $body ) { // phpcs:disable VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
		static $raw = [];
		if ( isset( $raw[ $tag ] ) ) {
			return $raw[ $tag ];
		}

		$raw[ $tag ] = false;
		foreach ( $this->getMappedParams( $tag ) as $param ) {
			$isBodyParam = 'content' === ( isset( $param['param_name'] ) ? $param['param_name'] : '' ) || ! empty( $param['holder'] );
			if ( $isBodyParam && in_array( isset( $param['type'] ) ? $param['type'] : '', $this->rawParamTypes, true ) ) {
				$raw[ $tag ] = true;
			}
		}

		return $raw[ $tag ];
	}

	/**
	 * Returns whether the given attribute value is visible copy.
	 *
	 * WPBakery's map marks a param's type but not whether it is shown to a reader, so its text types
	 * still carry sizes, counts and delays. A value with no letter in it, or a bare CSS length or
	 * duration such as `32px` or `0.3s`, is one of those. On a heading or label param only the CSS
	 * length or duration is dropped, so a heading like `2024` or `100%` is kept.
	 *
	 * NOTE: Byte-safe on purpose. A letter in any script falls outside the classes below, and adding a
	 * /u flag would let invalid UTF-8 fail the match and silently empty the description.
	 *
	 * @since 5.0.3
	 *
	 * @param  string $value     The attribute value.
	 * @param  string $attribute The attribute name.
	 * @return bool              Whether the value is copy.
	 */
	protected function isCopyValue( $value, $attribute ) {
		if ( preg_match( $this->headingParamPattern, $attribute ) ) {
			return ! preg_match( '/^[0-9.]+(?:px|r?em|vw|vh|ms|s)$/i', $value );
		}

		return ! preg_match( '/^[0-9\s\-_.,:;#%\/()\[\]|+*]+(?:px|r?em|vw|vh|ms|s)?$/i', $value );
	}

	/**
	 * Returns the mapped params for the given tag, or an empty array when WPBakery has not mapped it.
	 *
	 * @since 5.0.3
	 *
	 * @param  string $tag The shortcode tag.
	 * @return array       The params.
	 */
	private function getMappedParams( $tag ) {
		if ( ! method_exists( '\WPBMap', 'getShortCode' ) ) {
			return [];
		}

		$definition = \WPBMap::getShortCode( $tag );

		return empty( $definition['params'] ) ? [] : (array) $definition['params'];
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
		if ( ! empty( $postObj ) && preg_match( '/vc_row/', (string) $postObj->post_content ) ) {
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
		if ( empty( $_REQUEST['_vcnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['_vcnonce'] ) ), 'vc-nonce-vc-admin-nonce' ) ) {
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
	 * @since   4.5.2
	 * @version 4.9.9  Render shortcodes on front-end views too, not just AJAX/cron/REST.
	 * @version 5.0.3 Extract text from the raw markup on front-end views instead of rendering it.
	 *
	 * @param  int    $postId  The post id.
	 * @param  mixed  $content The raw content.
	 * @return string          The processed content.
	 */
	public function processContent( $postId, $content = null ) {
		if ( method_exists( '\WPBMap', 'addAllMappedShortcodes' ) ) {
			\WPBMap::addAllMappedShortcodes();
		}

		$content = parent::processContent( $postId, $content ?? '' );

		// WPBakery content is purely [vc_*] shortcodes; the parent only expands them in AJAX/cron/REST,
		// so on front-end views they survive to strip_shortcodes() and the auto-description comes out
		// empty. Read the copy straight out of the markup instead: WPBakery elements register assets and
		// per-instance init state when they render, so running them here and again during the real render
		// leaves tabs, tours and carousels broken.
		if ( ! is_admin() && ! aioseo()->helpers->isAjaxCronRestRequest() && ! doing_filter( 'the_content' ) ) {
			$content = $this->extractShortcodeText( $content );
		}

		return $content;
	}

	/**
	 * Returns the given attribute value with WPBakery's encoding undone.
	 *
	 * NOTE: WPBakery stores `[`, `]` and `"` in attribute values as `{`, `}` and ``. The brackets are
	 * decoded first, as WPBakery does, since their tokens contain the quote token.
	 *
	 * @since 5.0.3
	 *
	 * @param  string $value The raw attribute value.
	 * @return string        The decoded value.
	 */
	protected function decodeAttributeValue( $value ) {
		return str_replace( [ '`{`', '`}`', '``' ], [ '&#91;', '&#93;', '"' ], $value );
	}
}