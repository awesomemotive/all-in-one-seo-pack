<?php
namespace AIOSEO\Plugin\Lite\Admin;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AIOSEO\Plugin\Common\Admin as CommonAdmin;

/**
 * Abstract class that Pro and Lite both extend.
 *
 * @since 4.0.0
 */
class Admin extends CommonAdmin\Admin {
	/**
	 * Connect class instance.
	 *
	 * @since 4.2.7
	 *
	 * @var Connect
	 */
	public $connect = null;

	/**
	 * Class constructor.
	 *
	 * @since 4.0.0
	 */
	public function __construct() {
		// The parent registers the Action Scheduler callbacks WP-Cron runs, and returns early for cron itself.
		parent::__construct();

		$this->connect = new Connect();
	}

	/**
	 * Actually adds the menu items to the admin bar.
	 *
	 * @since   4.0.0
	 * @version 5.0.2 Upgrade link carries the current screen slug for utm_content.
	 *
	 * @return void
	 */
	protected function addAdminBarMenuItems() {
		// Add an upsell to Pro.
		if ( current_user_can( $this->getPageRequiredCapability( '' ) ) ) {
			$this->adminBarMenuItems['aioseo-pro-upgrade'] = [
				'parent' => 'aioseo-main',
				'title'  => '<span class="aioseo-menu-highlight lite">' . __( 'Upgrade to Pro', 'all-in-one-seo-pack' ) . '</span>',
				'id'     => 'aioseo-pro-upgrade',
				'href'   => admin_url( 'admin.php?page=aioseo-tools&aioseo-redirect-upgrade-admin-bar=' . $this->getCurrentScreenSlug() ),
				'meta'   => [ 'target' => '_blank' ],
			];
		}

		parent::addAdminBarMenuItems();
	}

	/**
	 * Add the menu inside of WordPress.
	 *
	 * @since   4.0.0
	 * @version 5.0.2 Upgrade link carries the current screen slug for utm_content.
	 *
	 * @return void
	 */
	public function addMenu() {
		parent::addMenu();

		$capability = $this->getPageRequiredCapability( '' );

		// We use the global submenu, because we are adding an external link here.
		if ( current_user_can( $capability ) ) {
			global $submenu;
			$submenu[ $this->pageSlug ][] = [
				'<span class="aioseo-menu-highlight lite">' . esc_html__( 'Upgrade to Pro', 'all-in-one-seo-pack' ) . '</span>',
				$capability,
				admin_url( 'admin.php?page=aioseo-tools&aioseo-redirect-upgrade=' . $this->getCurrentScreenSlug() )
			];
		}
	}

	/**
	 * Check the query args to see if we need to redirect to an external URL.
	 *
	 * @since   4.2.3
	 * @version 5.0.2 Uses the query arg value as the utm_content of the upgrade URL.
	 *
	 * @return void
	 */
	protected function checkForRedirects() {
		// We redirect through our own admin page to resolve an issue with the open_basedir in the IIS.

		$queryArgs = [
			'aioseo-redirect-upgrade'           => 'admin-menu',
			'aioseo-redirect-upgrade-admin-bar' => 'admin-bar'
		];

		// phpcs:disable HM.Security.NonceVerification.Recommended, WordPress.Security.NonceVerification.Recommended
		foreach ( $queryArgs as $queryArg => $medium ) {
			if ( ! isset( $_GET[ $queryArg ] ) ) {
				continue;
			}

			$content = is_string( $_GET[ $queryArg ] ) ? sanitize_key( wp_unslash( $_GET[ $queryArg ] ) ) : '';

			// Old links use "1" as the query arg value, which doesn't identify a screen.
			$redirectUrl = apply_filters(
				'aioseo_upgrade_link',
				aioseo()->helpers->utmUrl( AIOSEO_MARKETING_URL . 'lite-upgrade/', $medium, '1' !== $content ? $content : null, false )
			);

			wp_redirect( $redirectUrl ); // phpcs:ignore WordPress.Security.SafeRedirect.wp_redirect_wp_redirect
			exit;
		}
		// phpcs:enable
	}

	/**
	 * Returns a slug that identifies the current screen for the upgrade link utm_content.
	 *
	 * @since 5.0.2
	 *
	 * @return string The current screen slug.
	 */
	private function getCurrentScreenSlug() {
		if ( ! is_admin() ) {
			return 'frontend';
		}

		// phpcs:disable HM.Security.NonceVerification.Recommended, WordPress.Security.NonceVerification.Recommended
		$page = isset( $_GET['page'] ) && is_string( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : '';
		// phpcs:enable
		if ( $page ) {
			// Match the slugs the Vue app uses for its pages.
			return 'aioseo' === $page ? 'dashboard' : preg_replace( '/^aioseo-/', '', $page );
		}

		global $pagenow;

		return $pagenow ? sanitize_key( str_replace( '.php', '', $pagenow ) ) : 'admin';
	}
}