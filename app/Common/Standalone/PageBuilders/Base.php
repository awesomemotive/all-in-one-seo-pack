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

		foreach ( $this->plugins as $basename ) {
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
		$theme = wp_get_theme();
		foreach ( $this->themes as $name ) {
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
		// phpcs:disable HM.Security.NonceVerification.Recommended
		foreach ( [ 'id', 'post', 'post_id' ] as $key ) {
			if ( ! empty( $_GET[ $key ] ) ) {
				return (int) $_GET[ $key ];
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
	 * @since 4.5.2
	 *
	 * @param  int    $postId  The post id.
	 * @param  string $content The raw content.
	 * @return string          The processed content.
	 */
	public function processContent( $postId, $content = '' ) {
		if ( empty( $content ) ) {
			$post = get_post( $postId );
			if ( is_a( $post, 'WP_Post' ) ) {
				$content = $post->post_content;
			}
		}

		return apply_filters( 'the_content', $content );
	}
}