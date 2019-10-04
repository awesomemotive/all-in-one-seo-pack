<?php
/**
 * Welcome
 *
 * @package All_in_One_SEO_Pack
 * @since ?
 */

if ( ! class_exists( 'aioseop_welcome' ) ) {

	/**
	 * Class aioseop_welcome
	 */
	// @codingStandardsIgnoreStart
	class aioseop_welcome {
	// @codingStandardsIgnoreEnd
		/**
		 * Constructor to add the actions.
		 */
		function __construct() {

			if ( AIOSEOPPRO ) {
				return;
			}

			add_action( 'admin_menu', array( $this, 'add_menus' ) );
			add_action( 'admin_menu', array( $this, 'remove_pages' ), 999 );
			add_action( 'admin_enqueue_scripts', array( $this, 'welcome_screen_assets' ) );

		}

		/**
		 * Enqueues style and script.
		 *
		 * @param $hook
		 */
		function welcome_screen_assets( $hook ) {

			if ( 'dashboard_page_aioseop-about' === $hook ) {

				wp_enqueue_style( 'aioseop_welcome_css', AIOSEOP_PLUGIN_URL . 'css/aioseop-welcome.css', array(), AIOSEOP_VERSION );
				if ( function_exists( 'is_rtl' ) && is_rtl() ) {
					wp_enqueue_style( 'aioseop_welcome_css_rtl', AIOSEOP_PLUGIN_URL . 'css/aioseop-welcome-rtl.css', array( 'aioseop_welcome_css' ), AIOSEOP_VERSION );
				}
				wp_enqueue_script( 'aioseop_welcome_js', AIOSEOP_PLUGIN_URL . 'js/welcome.js', array( 'jquery' ), AIOSEOP_VERSION, true );
			}
		}

		/**
		 * Removes unneeded pages.
		 *
		 * @since 2.3.12 Called via admin_menu action instead of admin_head.
		 */
		function remove_pages() {
			remove_submenu_page( 'index.php', 'aioseop-about' );
			remove_submenu_page( 'index.php', 'aioseop-credits' );
		}

		/**
		 * Adds (hidden) menu.
		 */
		function add_menus() {
			/* translators: %s is a placeholder, which means that it should not be translated. It will be replaced with the name of the plugin, All in One SEO Pack. */
			$welcome_text = sprintf( __( 'Welcome to %s', 'all-in-one-seo-pack' ), AIOSEOP_PLUGIN_NAME );
			add_dashboard_page(
				$welcome_text,
				$welcome_text,
				'manage_options',
				'aioseop-about',
				array( $this, 'about_screen' )
			);

		}

		/**
		 * Initial stuff.
		 *
		 * @param bool $activate
		 */
		function init( $activate = false ) {

			if ( ! is_admin() ) {
				return;
			}

			// Bail if activating from network, or bulk.
			if ( is_network_admin() || isset( $_GET['activate-multi'] ) ) {
				return;
			}

			if ( ! current_user_can( 'manage_options' ) ) {
				return;
			}

			wp_cache_flush();
			aiosp_common::clear_wpe_cache();

			delete_transient( '_aioseop_activation_redirect' );

			$seen = 0;
			$seen = get_user_meta( get_current_user_id(), 'aioseop_seen_about_page', true );

			update_user_meta( get_current_user_id(), 'aioseop_seen_about_page', AIOSEOP_VERSION );

			if ( AIOSEOPPRO ) {
				return;
			}

			// Compare the major versions so we don't show the welcome screen on minor versions.
			if ( ( get_major_version( AIOSEOP_VERSION ) === get_major_version( $seen ) ) || ( true !== $activate ) ) {
				return;
			}

			wp_safe_redirect( add_query_arg( array( 'page' => 'aioseop-about' ), admin_url( 'index.php' ) ) );
			exit;
		}

		/**
		 * Outputs the about screen.
		 */
		function about_screen() {
			aiosp_common::clear_wpe_cache();
			$version = AIOSEOP_VERSION;

			?>

			<div class="wrap about-wrap">
			<div class="aioseop-welcome-logo">
					<?php echo aioseop_get_logo( 180, 180, '#44619A' ); ?>
				</div>
				<h1>
					<?php
					/* translators: %1$s and %2$s are placeholders, which means that these should not be translated. These will be replaced with the name of the plugin, All in One SEO Pack, and the current version number. */
					printf( esc_html__( 'Welcome to %1$s %2$s', 'all-in-one-seo-pack' ), AIOSEOP_PLUGIN_NAME, $version );
					?>
				</h1>
				<div class="about-text">
					<?php
					/* translators: %1$s and %2$s are placeholders, which means that these should not be translated. These will be replaced with the name of the plugin, All in One SEO Pack, and the current version number. */
					printf( esc_html__( '%1$s %2$s contains new features, bug fixes, increased security, and tons of under the hood performance improvements.', 'all-in-one-seo-pack' ), AIOSEOP_PLUGIN_NAME, $version );
					?>
				</div>

				<h2 class="nav-tab-wrapper">
					<a
						class="nav-tab nav-tab-active" id="aioseop-about"
						href="<?php echo esc_url( admin_url( add_query_arg( array( 'page' => 'aioseop-about' ), 'index.php' ) ) ); ?>">
						<?php esc_html_e( 'What&#8217;s New', 'all-in-one-seo-pack' ); ?>
					</a>
					<a
						class="nav-tab" id="aioseop-credits"
						href="<?php echo esc_url( admin_url( add_query_arg( array( 'page' => 'aioseop-credits' ), 'index.php' ) ) ); ?>">
						<?php esc_html_e( 'Credits', 'all-in-one-seo-pack' ); ?>
					</a>
				</h2>


				<div id='sections'>
					<section><?php include_once( AIOSEOP_PLUGIN_DIR . 'admin/display/welcome-content.php' ); ?></section>
					<section><?php include_once( AIOSEOP_PLUGIN_DIR . 'admin/display/credits-content.php' ); ?></section>
				</div>

			</div>


			<?php

		}

	}

}
