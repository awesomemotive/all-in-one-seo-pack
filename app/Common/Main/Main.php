<?php
namespace AIOSEO\Plugin\Common\Main;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AIOSEO\Plugin\Common\Models;

/**
 * Abstract class that Pro and Lite both extend.
 *
 * @since 4.0.0
 */
class Main {
	/**
	 * Construct method.
	 *
	 * @since 4.0.0
	 */
	public function __construct() {
		new Media();
		new QueryArgs();

		add_action( 'admin_enqueue_scripts', [ $this, 'enqueueTranslations' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueueFrontEndAssets' ] );
		add_action( 'admin_footer', [ $this, 'adminFooter' ] );
	}

	/**
	 * Enqueues the translations seperately so it can be called from anywhere.
	 *
	 * @since 4.1.9
	 *
	 * @return void
	 */
	public function enqueueTranslations() {
		aioseo()->core->assets->load( 'src/vue/standalone/app/main.js', [], [
			'translations' => aioseo()->helpers->getJedLocaleData( 'all-in-one-seo-pack' )
		], 'aioseoTranslations' );
	}

	/**
	 * Enqueue styles on the front-end.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function enqueueFrontEndAssets() {
		$canManageSeo = apply_filters( 'aioseo_manage_seo', 'aioseo_manage_seo' );
		if (
			! aioseo()->helpers->isAdminBarEnabled() ||
			! ( current_user_can( $canManageSeo ) || aioseo()->access->canManage() )
		) {
			return;
		}

		aioseo()->core->assets->enqueueCss( 'src/vue/assets/scss/app/admin-bar.scss' );
	}

	/**
	 * Enqueue the footer file to let vue attach.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function adminFooter() {
		echo '<div id="aioseo-admin"></div>';
	}
}