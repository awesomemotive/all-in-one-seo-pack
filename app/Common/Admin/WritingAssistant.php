<?php
namespace AIOSEO\Plugin\Common\Admin;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AIOSEO\Plugin\Common\Models;

/**
 * The Admin class.
 *
 * @since 4.7.4
 */
class WritingAssistant {
	/**
	 * Class constructor.
	 *
	 * @since 4.7.4
	 */
	public function __construct() {
		add_action( 'add_meta_boxes', [ $this, 'addMetabox' ] );
		add_action( 'delete_post', [ $this, 'deletePost' ] );
	}

	/**
	 * Deletes the writing assistant post.
	 *
	 * @since 4.7.4
	 *
	 * @param  int  $postId The post id.
	 * @return void
	 */
	public function deletePost( $postId ) {
		Models\WritingAssistantPost::getPost( $postId )->delete();
	}

	/**
	 * Adds a meta box to the page/posts screens.
	 *
	 * @since 4.7.4
	 *
	 * @return void
	 */
	public function addMetabox() {
		if ( ! aioseo()->access->hasCapability( 'aioseo_page_writing_assistant_settings' ) ) {
			return;
		}

		if (
			! aioseo()->options->writingAssistant->postTypes->all &&
			! in_array( get_post_type(), aioseo()->options->writingAssistant->postTypes->included, true )
		) {
			return;
		}

		// Skip post types that do not support an editor.
		if ( ! post_type_supports( get_post_type(), 'editor' ) ) {
			return;
		}

		add_action( 'admin_enqueue_scripts', [ $this, 'enqueueAssets' ] );

		// Translators: 1 - The plugin short name ("AIOSEO").
		$aioseoMetaboxTitle = sprintf( esc_html__( '%1$s Writing Assistant', 'all-in-one-seo-pack' ), AIOSEO_PLUGIN_SHORT_NAME );

		add_meta_box(
			'aioseo-writing-assistant-metabox',
			$aioseoMetaboxTitle,
			[ $this, 'renderMetabox' ],
			null,
			'normal',
			'low'
		);
	}

	/**
	 * Render the on-page settings metabox with the Vue App wrapper.
	 *
	 * @since 4.7.4
	 *
	 * @return void
	 */
	public function renderMetabox() {
		?>
		<div id="aioseo-writing-assistant-metabox-app">
			<?php aioseo()->templates->getTemplate( 'parts/loader.php' ); ?>
		</div>
		<?php
	}

	/**
	 * Enqueues the JS/CSS for the standalone.
	 *
	 * @since 4.7.4
	 *
	 * @return void
	 */
	public function enqueueAssets() {
		if ( ! aioseo()->helpers->isScreenBase( 'post' ) ) {
			return;
		}

		aioseo()->core->assets->load(
			'src/vue/standalone/writing-assistant/main.js',
			[],
			aioseo()->writingAssistant->helpers->getStandaloneVueData(),
			'aioseoWritingAssistant'
		);
	}
}