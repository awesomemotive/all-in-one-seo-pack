<?php

/**
 * Class AIOSEOPAdminMenus
 *
 * @since 2.3.11.5
 */
class AIOSEOPAdminMenus {

	/**
	 * Constructor to add the actions.
	 */
	function __construct() {
		if ( current_user_can( 'manage_options' ) || current_user_can( 'aiosp_manage_seo' ) ) {
			add_action( 'admin_menu', array( $this, 'add_pro_submenu' ), 11 );
		} else {
			return;
		}
	}

	/**
	 * Adds Upgrade link to our menu.
	 *
	 * @since 2.3.11.5
	 */
	function add_pro_submenu() {
		global $submenu;
		$url = 'https://semperplugins.com/all-in-one-seo-pack-pro-version/?loc=aio_menu';
		$upgrade_text = __( 'Upgrade to Pro', 'all-in-one-seo-pack' );
		$submenu['all-in-one-seo-pack/aioseop_class.php'][] = array(
			"<span class='upgrade_menu_link'>$upgrade_text</span>",
			'manage_options',
			$url,
		);
	}
}

new AIOSEOPAdminMenus();
