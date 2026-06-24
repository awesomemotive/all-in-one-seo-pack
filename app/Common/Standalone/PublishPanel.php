<?php
namespace AIOSEO\Plugin\Common\Standalone;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AIOSEO\Plugin\Common\Models;

/**
 * Handles the Publish Panel in the Block Editor.
 *
 * @since 4.2.0
 */
class PublishPanel {
	/**
	 * Class constructor.
	 *
	 * @since 4.2.0
	 */
	public function __construct() {
		if ( ! is_admin() || wp_doing_ajax() || wp_doing_cron() ) {
			return;
		}

		add_action( 'admin_enqueue_scripts', [ $this, 'enqueueScript' ] );
	}

	/**
	 * Enqueues the script.
	 *
	 * @since 4.2.0
	 *
	 * @return void
	 */
	public function enqueueScript() {
		if ( ! aioseo()->helpers->isScreenBase( 'post' ) ) {
			return;
		}

		// Declare wp-editor as a dependency so window.wp.editor is populated by the time the
		// module reads PluginDocumentSettingPanel / PluginPrePublishPanel. Without this the
		// fallback `wp.editor?.X || wp.editPost?.X` chain reaches `wp.editPost.X`, which
		// triggers a deprecation warning on WP 6.6+. See issue #7927.
		aioseo()->core->assets->load( 'src/vue/standalone/publish-panel/main.js', [ 'wp-editor' ] );
	}
}