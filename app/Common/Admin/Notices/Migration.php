<?php
namespace AIOSEO\Plugin\Common\Admin\Notices;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * V3 to V4 migration notice.
 *
 * @since 4.0.0
 */
class Migration {
	/**
	 * Go through all the checks to see if we should show the notice.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function maybeShowNotice() {
		$transientPosts = aioseo()->core->cache->get( 'v3_migration_in_progress_posts' );
		$transientTerms = aioseo()->core->cache->get( 'v3_migration_in_progress_terms' );
		if ( ! $transientPosts && ! $transientTerms ) {
			return;
		}

		$this->showNotice();
	}

	/**
	 * Register the notice so that it appears.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function showNotice() {
		// Translators: 1 - The plugin name ("AIOSEO).
		$string1 = sprintf( __( '%1$s V3->V4 Migration In Progress', 'all-in-one-seo-pack' ), AIOSEO_PLUGIN_SHORT_NAME );
		// Translators: 1 - The plugin name ("All in One SEO").
		$string2 = sprintf( __( '%1$s is currently upgrading your database and migrating your SEO data in the background.', 'all-in-one-seo-pack' ), AIOSEO_PLUGIN_NAME );
		$string3 = __( 'This notice will automatically disappear as soon as the migration has completed. Meanwhile, everything should continue to work as expected.', 'all-in-one-seo-pack' );
		?>
		<div class="notice notice-info aioseo-migration">
			<p><strong><?php echo esc_html( $string1 ); ?></strong></p>
			<p><?php echo esc_html( $string2 ); ?></p>
			<p><?php echo esc_html( $string3 ); ?></p>
		</div>
		<style>
		</style>
		<?php
	}
}