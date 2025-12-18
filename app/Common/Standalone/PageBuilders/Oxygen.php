<?php
namespace AIOSEO\Plugin\Common\Standalone\PageBuilders;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Integrate our SEO Panel with Oxygen Builder.
 *
 * @since 4.9.2
 */
class Oxygen extends Base {
	/**
	 * The plugin slug to integrate with.
	 *
	 * @since 4.9.2
	 *
	 * @var array
	 */
	public $plugins = [
		'oxygen/plugin.php'
	];

	/**
	 * The integration slug.
	 *
	 * @since 4.9.2
	 *
	 * @var string
	 */
	public $integrationSlug = 'oxygen';

	/**
	 * Init the integration.
	 *
	 * @since 4.9.2
	 *
	 * @return void
	 */
	public function init() {
		add_action( 'wp', [ $this, 'maybeRun' ] );

		// For singular posts rendered directly (no template).
		add_filter( 'breakdance_singular_content', [ $this, 'doContentEndAction' ] );

		// For pages with a template applied.
		add_filter( 'breakdance_render_rendered_html', [ $this, 'doTemplateContentEndAction' ], 10, 3 );
	}

	/**
	 * Fires an action at the end of Oxygen content.
	 * This bridges the gap for features that hook into `the_content` filter, which Oxygen bypasses when rendering its own stored content.
	 *
	 * @since 4.9.2
	 *
	 * @param  string $content The rendered Breakdance content.
	 * @return string          The content with any appended output.
	 */
	public function doContentEndAction( $content ) {
		if ( empty( $content ) ) {
			return $content;
		}

		if ( aioseo()->helpers->callFunc( '\Breakdance\isRequestFromBuilderIframe' ) ) {
			return $content;
		}

		ob_start();

		do_action( 'aioseo_oxygen_content_end', $content );

		$content .= ob_get_clean();

		return $content;
	}

	/**
	 * Fires an action at the end of Oxygen template content.
	 * This handles pages with a template applied.
	 *
	 * @since 4.9.2
	 *
	 * @param  string   $html               The rendered HTML.
	 * @param  int      $postId             The post ID being rendered.
	 * @param  int|null $repeaterItemNodeId The repeater item node ID (for loops).
	 * @return string                       The HTML with any appended output.
	 */
	public function doTemplateContentEndAction( $html, $postId, $repeaterItemNodeId = null ) {
		// Only proceed for template post types.
		if ( ! defined( 'BREAKDANCE_TEMPLATE_POST_TYPE' ) || BREAKDANCE_TEMPLATE_POST_TYPE !== get_post_type( $postId ) ) {
			return $html;
		}

		// Not in the builder.
		if ( aioseo()->helpers->callFunc( '\Breakdance\isRequestFromBuilderIframe' ) ) {
			return $html;
		}

		// Don't fire if repeater item (this is for loops, not the main template).
		if ( ! empty( $repeaterItemNodeId ) ) {
			return $html;
		}

		ob_start();

		do_action( 'aioseo_oxygen_content_end', $html );

		$html .= ob_get_clean();

		return $html;
	}

	/**
	 * Check if we are in the Page Builder and run the integrations.
	 *
	 * @since 4.9.2
	 *
	 * @return void
	 */
	public function maybeRun() {
		$postId = $this->getPostId();
		if ( ! aioseo()->postSettings->canAddPostSettingsMetabox( get_post_type( $postId ) ) ) {
			return;
		}

		if ( aioseo()->helpers->callFunc( '\Breakdance\isRequestFromBuilderIframe' ) ) {
			add_action( 'wp_enqueue_scripts', [ $this, 'enqueue' ], 20 );
		}
	}

	/**
	 * Returns whether the given post ID was built with Oxygen.
	 *
	 * @since 4.9.2
	 *
	 * @param  int     $postId The Post ID.
	 * @return boolean         Whether the post was built with Oxygen.
	 */
	public function isBuiltWith( $postId ) {
		return 'breakdance' === aioseo()->helpers->callFunc( '\Breakdance\Admin\get_mode', $postId );
	}

	/**
	 * Returns the Oxygen "edit post link" for the given post ID.
	 *
	 * @since 4.9.2
	 *
	 * @param  int    $postId The post ID.
	 * @return string         The Oxygen "edit post link" for the given post.
	 */
	public function getEditUrl( $postId ) {
		if ( ! $this->isBuiltWith( $postId ) ) {
			return '';
		}

		return aioseo()->helpers->callFunc( '\Breakdance\Admin\get_builder_loader_url', $postId );
	}

	/**
	 * Returns whether or not we should prevent the date from being modified.
	 *
	 * @since 4.9.2
	 *
	 * @param  int  $postId The Post ID.
	 * @return bool         Whether or not we should prevent the date from being modified.
	 */
	public function limitModifiedDate( $postId ) {
		// This method is supposed to be used in the `breakdance_save` action.
		$action = function_exists( '\Breakdance\AJAX\get_nonce_key_for_ajax_requests' ) ? \Breakdance\AJAX\get_nonce_key_for_ajax_requests() : 'breakdance_ajax';
		if ( ! isset( $_REQUEST['_ajax_nonce'] ) || ! wp_verify_nonce( $_REQUEST['_ajax_nonce'], $action ) ) {
			return false;
		}

		$requestPostId = ! empty( $_REQUEST['id'] ) ? (int) $_REQUEST['id'] : false;
		if ( $requestPostId !== $postId ) {
			return false;
		}

		return ! empty( $_REQUEST['aioseo_limit_modified_date'] );
	}

	/**
	 * Returns the processed page builder content.
	 *
	 * @since 4.9.2
	 *
	 * @param  int    $postId     The post ID.
	 * @param  mixed  $rawContent The raw content.
	 * @return string             The processed content.
	 */
	public function processContent( $postId, $rawContent = null ) {
		// Use Breakdance's render function if available.
		// Breakdance stores content in its own data structure, not post_content.
		if ( function_exists( '\Breakdance\Render\render' ) ) {
			$content = \Breakdance\Render\render( $postId );
			if ( is_string( $content ) && '' !== $content ) {
				return $content;
			}
		}

		// Fallback to the parent method.
		return parent::processContent( $postId, $rawContent );
	}
}