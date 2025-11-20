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
	 * Disabled for independent plugin - no longer needed.
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
		if ( ! wp_doing_cron() ) {
			parent::__construct();
		}

		// Disabled: Connect to AIOSEO Pro - independent plugin doesn't need this
		// $this->connect = new Connect();
	}

	/**
	 * Actually adds the menu items to the admin bar.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	protected function addAdminBarMenuItems() {
		// Removed: Upgrade to Pro upsell - independent plugin with all features enabled
		parent::addAdminBarMenuItems();
	}

	/**
	 * Add the menu inside of WordPress.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function addMenu() {
		parent::addMenu();

		// Removed: Upgrade to Pro menu item - independent plugin with all features enabled
	}

	/**
	 * Check the query args to see if we need to redirect to an external URL.
	 *
	 * @since 4.2.3
	 *
	 * @return void
	 */
	protected function checkForRedirects() {
		// Removed: Upgrade redirect - independent plugin doesn't need external upgrade links
	}
}