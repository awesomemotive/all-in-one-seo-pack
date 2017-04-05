<?php

if ( ! class_exists( 'aioseop_welcome' ) ) {

	/**
	 * Class aioseop_welcome
	 */
	class aioseop_welcome {
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

				wp_enqueue_style( 'aioseop_welcome_css', AIOSEOP_PLUGIN_URL . '/css/welcome.css', array(), AIOSEOP_VERSION );
				wp_enqueue_script( 'aioseop_welcome_js', AIOSEOP_PLUGIN_URL . '/js/welcome.js', array( 'jquery' ), AIOSEOP_VERSION, true );
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
			add_dashboard_page(
				__( 'Welcome to All in One SEO Pack', 'all-in-one-seo-pack' ),
				__( 'Welcome to All in One SEO Pack', 'all-in-one-seo-pack' ),
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

			// Bail if activating from network, or bulk
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
			
			if ( ( AIOSEOP_VERSION === $seen ) || ( true !== $activate ) ) {
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
				<h1><?php printf( esc_html__( 'Welcome to All in One SEO Pack %s', 'all-in-one-seo-pack' ), $version ); ?></h1>
				<div
					class="about-text"><?php printf( esc_html__( 'All in One SEO Pack %s contains new features, bug fixes, increased security, and tons of under the hood performance improvements.', 'all-in-one-seo-pack' ), $version ); ?></div>

				<h2 class="nav-tab-wrapper">
					<a class="nav-tab nav-tab-active" id="aioseop-about"
					   href="<?php echo esc_url( admin_url( add_query_arg( array( 'page' => 'aioseop-about' ), 'index.php' ) ) ); ?>">
						<?php esc_html_e( 'What&#8217;s New', 'all-in-one-seo-pack' ); ?>
					</a>
					<a class="nav-tab" id="aioseop-credits"
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
