<?php
namespace AIOSEO\Plugin\Common\Utils;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Block helpers.
 *
 * @since 4.1.1
 */
class Blocks {
	/**
	 * The block slugs.
	 *
	 * @since 4.9.0
	 *
	 * @var array
	 */
	private $blockSlugs = [];

	/**
	 * Class constructor.
	 *
	 * @since 4.1.1
	 */
	public function __construct() {
		add_action( 'init', [ $this, 'init' ] );
	}

	/**
	 * Initializes our blocks.
	 *
	 * @since 4.1.1
	 *
	 * @return void
	 */
	public function init() {
		add_action( 'enqueue_block_editor_assets', [ $this, 'registerBlockEditorAssets' ] );
	}

	/**
	 * Registers the block type with WordPress.
	 *
	 * @since 4.2.1
	 *
	 * @param  string               $slug The Block name.
	 * @param  array                $args Array of block type arguments with additional 'wp_min_version' arg.
	 * @return \WP_Block_Type|false       The registered block type on success, or false on failure.
	 */
	public function registerBlock( $slug = '', $args = [] ) {
		global $wp_version; // phpcs:ignore Squiz.NamingConventions.ValidVariableName

		$slugWithPrefix = str_replace( 'pro/', '', $slug );
		if ( ! strpos( $slugWithPrefix, '/' ) ) {
			$slugWithPrefix = 'aioseo/' . str_replace( 'pro/', '', $slug );
		}

		if ( ! $this->isBlockEditorActive() ) {
			return false;
		}

		// Check if the block requires a minimum WP version.
		if ( ! empty( $args['wp_min_version'] ) && version_compare( $wp_version, $args['wp_min_version'], '>' ) ) { // phpcs:ignore Squiz.NamingConventions.ValidVariableName
			return false;
		}

		// Checking whether block is registered to ensure it isn't registered twice.
		if ( $this->isRegistered( $slugWithPrefix ) ) {
			return false;
		}

		// Store the block slugs so we can enqueue the global & editor assets later on.
		// We can't do this here because the built-in functions from WP will throw notices due to things running too soon.
		$this->blockSlugs[] = $slug;

		// Check if the block has global or editor assets. If so, we'll need to enqueue them later on.
		$editorScript    = "src/vue/standalone/blocks/{$slug}/editor-script.js";
		$hasEditorScript = aioseo()->core->assets->assetExists( $editorScript );

		$editorStyle     = "src/vue/standalone/blocks/{$slug}/editor.scss";
		$hasEditorStyle  = aioseo()->core->assets->assetExists( $editorStyle );

		$globalStyle     = "src/vue/standalone/blocks/{$slug}/global.scss";
		$hasGlobalStyle  = aioseo()->core->assets->assetExists( $globalStyle );

		// Register global CSS before registering the block type (WordPress 5.8+ requirement)
		if ( $hasGlobalStyle ) {
			aioseo()->core->assets->registerCss( $globalStyle );
		}

		$styleDefault = empty( $args['style'] ) ? '' : $args['style'];

		$defaults = [
			'attributes'      => [],
			'editor_script'   => $hasEditorScript ? aioseo()->core->assets->jsHandle( $editorScript ) : '',
			'editor_style'    => $hasEditorStyle ? aioseo()->core->assets->cssHandle( $editorStyle ) : '',
			'render_callback' => null,
			'style'           => $hasGlobalStyle ? aioseo()->core->assets->cssHandle( $globalStyle ) : $styleDefault,
			'supports'        => []
		];

		$args = wp_parse_args( $args, $defaults );

		return register_block_type( $slugWithPrefix, $args );
	}

	/**
	 * Registers Block Editor assets.
	 *
	 * @since 4.2.1
	 *
	 * @return void
	 */
	public function registerBlockEditorAssets() {
		$postSettingJsAsset = 'src/vue/standalone/post-settings/main.js';
		if (
			aioseo()->helpers->isScreenBase( 'widgets' ) ||
			aioseo()->helpers->isScreenBase( 'customize' )
		) {
			/**
			 * Make sure the post settings JS asset is registered before adding it as a dependency below.
			 * This is needed because this asset is not loaded on widgets and customizer screens,
			 * {@see \AIOSEO\Plugin\Common\Admin\PostSettings::enqueuePostSettingsAssets}.
			 *

			 */
			aioseo()->core->assets->load( $postSettingJsAsset, [], aioseo()->helpers->getVueData() );
		}

		aioseo()->core->assets->loadCss( 'src/vue/standalone/blocks/schema.js' );

		$dependencies = [
			'wp-annotations',
			'wp-block-editor',
			'wp-blocks',
			'wp-components',
			'wp-element',
			'wp-i18n',
			'wp-data',
			'wp-url',
			'wp-polyfill',
			aioseo()->core->assets->jsHandle( $postSettingJsAsset )
		];

		aioseo()->core->assets->enqueueJs( 'src/vue/standalone/blocks/schema.js', $dependencies );

		foreach ( $this->blockSlugs as $slug ) {
			aioseo()->core->assets->enqueueJs( "src/vue/standalone/blocks/{$slug}/main.jsx", $dependencies );

			// Note: Since these files load conditionally, these need to be added to the vite.config as standalone entries.
			// TODO: Refactor this to use the block.json file (if possible - might conflict with hashes) in the future when 5.8.0 is the min. supported version.
			$editorStyle   = "src/vue/standalone/blocks/{$slug}/editor.scss";
			$hasEditorStyle = aioseo()->core->assets->assetExists( $editorStyle );
			if ( $hasEditorStyle ) {
				aioseo()->core->assets->registerCss( $editorStyle );
			}
		}
	}

	/**
	 * Check if a block is already registered.
	 *
	 * @since 4.2.1
	 *
	 * @param string $slug Name of block to check.
	 *
	 * @return bool
	 */
	public function isRegistered( $slug ) {
		if ( ! class_exists( 'WP_Block_Type_Registry' ) ) {
			return false;
		}

		return \WP_Block_Type_Registry::get_instance()->is_registered( $slug );
	}

	/**
	 * Helper function to determine if we're rendering the block inside Gutenberg.
	 *
	 * @since 4.1.1
	 *
	 * @return bool In gutenberg.
	 */
	public function isRenderingBlockInEditor() {
		// phpcs:disable HM.Security.NonceVerification.Recommended, WordPress.Security.NonceVerification.Recommended
		if ( ! defined( 'REST_REQUEST' ) || ! REST_REQUEST ) {
			return false;
		}

		$context = isset( $_REQUEST['context'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['context'] ) ) : '';
		// phpcs:enable HM.Security.NonceVerification.Recommended, WordPress.Security.NonceVerification.Recommended

		return 'edit' === $context;
	}

	/**
	 * Helper function to determine if we can register blocks.
	 *
	 * @since 4.1.1
	 *
	 * @return bool Can register block.
	 */
	public function isBlockEditorActive() {
		return function_exists( 'register_block_type' );
	}
}