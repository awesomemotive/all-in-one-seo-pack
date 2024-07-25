<?php
namespace AIOSEO\Plugin\Common\Standalone;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AIOSEO\Plugin\Common\Models;

/**
 * Handles the admin bar noindex warning.
 *
 * @since 4.6.7
 */
class AdminBarNoindexWarning {
	/**
	 * Class constructor.
	 *
	 * @since 4.6.7
	 */
	public function __construct() {
		add_action( 'init', [ $this, 'init' ] );
	}

	/**
	 * Initializes the standalone.
	 *
	 * @since 4.6.7
	 *
	 * @return void
	 */
	public function init() {
		if ( wp_doing_ajax() || wp_doing_cron() ) {
			return;
		}

		$isSitePublic = get_option( 'blog_public' );
		if ( $isSitePublic ) {
			return;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		add_action( 'admin_enqueue_scripts', [ $this, 'enqueueScript' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueueScript' ] );

		add_action( 'admin_bar_menu', [ $this, 'addAdminBarElement' ], 99999 );
	}

	/**
	 * Enqueues the script.
	 *
	 * @since 4.6.7
	 *
	 * @return void
	 */
	public function enqueueScript() {
		aioseo()->core->assets->load( 'src/vue/standalone/admin-bar-noindex-warning/main.js', [], [
			'optionsReadingUrl' => admin_url( 'options-reading.php' ),
		], 'aioseoAdminBarNoindexWarning' );
	}

	/**
	 * Adds the admin bar element.
	 *
	 * @since 4.6.7
	 *
	 * @param  \WP_Admin_Bar $wpAdminBar The admin bar object.
	 * @return void
	 */
	public function addAdminBarElement( $wpAdminBar ) {
		$wpAdminBar->add_node(
			[
				'id'    => 'aioseo-admin-bar-noindex-warning',
				'title' => __( 'Search Engines Blocked!', 'all-in-one-seo-pack' ),
				'href'  => admin_url( 'options-reading.php' )
			]
		);
	}
}