<?php
namespace AIOSEO\Plugin\Common\Standalone;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles the flyout menu.
 *
 * @since 4.2.0
 */
class FlyoutMenu {
	/**
	 * Class constructor.
	 *
	 * @since 4.2.0
	 */
	public function __construct() {
		if (
			! is_admin() ||
			wp_doing_ajax() ||
			wp_doing_cron() ||
			! $this->isEnabled()
		) {
			return;
		}

		add_action( 'admin_enqueue_scripts', [ $this, 'enqueueAssets' ], 11 );
		add_filter( 'admin_body_class', [ $this, 'addBodyClass' ] );
	}

	/**
	 * Enqueues the required assets.
	 *
	 * @since 4.2.0
	 *
	 * @return void
	 */
	public function enqueueAssets() {
		if ( ! $this->shouldEnqueue() ) {
			return;
		}

		aioseo()->core->assets->load( 'src/vue/standalone/flyout-menu/main.js' );
	}

	/**
	 * Filters the CSS classes for the body tag in the admin.
	 *
	 * @since 4.2.0
	 *
	 * @param  string $classes Space-separated list of CSS classes.
	 * @return string          Space-separated list of CSS classes.
	 */
	public function addBodyClass( $classes ) {
		if ( $this->shouldEnqueue() ) {
			// This adds a bottom margin to our menu so that we push the footer down and prevent the flyout menu from overlapping the "Save Changes" button.
			$classes .= ' aioseo-flyout-menu-enabled ';
		}

		return $classes;
	}

	/**
	 * Checks whether the flyout menu script should be enqueued.
	 *
	 * @since 4.2.0
	 *
	 * @return bool Whether the flyout menu script should be enqueued.
	 */
	private function shouldEnqueue() {
		return aioseo()->admin->isAioseoScreen();
	}

	/**
	 * Checks whether the flyout menu is enabled.
	 *
	 * @since 4.2.0
	 *
	 * @return bool Whether the flyout menu is enabled.
	 */
	public function isEnabled() {
		return apply_filters( 'aioseo_flyout_menu_enable', true );
	}
}