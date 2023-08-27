<?php
namespace AIOSEO\Plugin\Common\Admin;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Abstract class that Pro and Lite both extend.
 *
 * @since 4.2.5
 */
class NetworkAdmin extends Admin {
	/**
	 * Construct method.
	 *
	 * @since 4.2.5
	 */
	public function __construct() {
		include_once ABSPATH . 'wp-admin/includes/plugin.php';
		if (
			is_network_admin() &&
			! is_plugin_active_for_network( plugin_basename( AIOSEO_FILE ) )
		) {
			return;
		}

		if ( wp_doing_ajax() || wp_doing_cron() ) {
			return;
		}

		add_action( 'sanitize_comment_cookies', [ $this, 'init' ], 21 );
	}

	/**
	 * Initialize the admin.
	 *
	 * @since 4.2.5
	 *
	 * @return void
	 */
	public function init() {
		add_action( 'network_admin_menu', [ $this, 'addNetworkMenu' ] );

		$this->setPages();
	}

	/**
	 * Add the network menu inside of WordPress.
	 *
	 * @since 4.2.5
	 *
	 * @return void
	 */
	public function addNetworkMenu() {
		$this->addMainMenu( 'aioseo' );

		foreach ( $this->pages as $slug => $page ) {
			if (
				'aioseo-settings' !== $slug &&
				'aioseo-tools' !== $slug &&
				'aioseo-about' !== $slug &&
				'aioseo-feature-manager' !== $slug
			) {
				continue;
			}

			$hook = add_submenu_page(
				$this->pageSlug,
				! empty( $page['page_title'] ) ? $page['page_title'] : $page['menu_title'],
				$page['menu_title'],
				$this->getPageRequiredCapability( $slug ),
				$slug,
				[ $this, 'page' ]
			);
			add_action( "load-{$hook}", [ $this, 'hooks' ] );
		}

		// Remove the "dashboard" submenu page that is not needed in the network admin.
		remove_submenu_page( $this->pageSlug, $this->pageSlug );
	}
}