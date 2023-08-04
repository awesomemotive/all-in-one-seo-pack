<?php
namespace AIOSEO\Plugin\Common\Standalone;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles the Primary Term feature.
 *
 * @since 4.3.6
 */
class PrimaryTerm {
	/**
	 * Class constructor.
	 *
	 * @since 4.3.6
	 */
	public function __construct() {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( wp_doing_ajax() || wp_doing_cron() || ! is_admin() ) {
			return;
		}

		add_action( 'admin_enqueue_scripts', [ $this, 'enqueueAssets' ] );
	}

	/**
	 * Enqueues the JS/CSS for the on page/posts settings.
	 *
	 * @since 4.3.6
	 *
	 * @return void
	 */
	public function enqueueAssets() {
		if ( ! aioseo()->helpers->isScreenBase( 'post' ) ) {
			return;
		}

		aioseo()->core->assets->load( 'src/vue/standalone/primary-term/main.js', [], aioseo()->helpers->getVueData( 'post' ) );
	}

	/**
	 * Returns the primary term for the given taxonomy name.
	 *
	 * @since 4.3.6
	 *
	 * @param  int            $postId       The post ID.
	 * @param  string         $taxonomyName The taxonomy name.
	 * @return \WP_Term|false               The term or false.
	 */
	public function getPrimaryTerm( $postId, $taxonomyName ) { // phpcs:ignore VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
		return false;
	}
}