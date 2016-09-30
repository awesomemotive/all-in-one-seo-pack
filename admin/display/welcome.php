<?php

if ( ! class_exists( 'aioseop_welcome' ) ) {

	class aioseop_welcome {
		function __construct() {

			if ( AIOSEOPPRO ) {
				return;
			}

			add_action( 'admin_menu', array( $this, 'add_menus' ) );
			add_action( 'admin_head', array( $this, 'remove_pages' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'welcome_screen_assets' ) );

		}

		function welcome_screen_assets( $hook ) {

			if ( 'dashboard_page_aioseop-about' === $hook ) {

				wp_enqueue_style( 'aioseop_welcome_css', AIOSEOP_PLUGIN_URL . '/css/welcome.css' );
				wp_enqueue_script( 'aioseop_welcome_js', AIOSEOP_PLUGIN_URL . '/js/welcome.js', array( 'jquery' ), AIOSEOP_VERSION, true );
			}
		}

		function remove_pages() {
			remove_submenu_page( 'index.php', 'aioseop-about' );
			remove_submenu_page( 'index.php', 'aioseop-credits' );
		}

		function add_menus() {
			add_dashboard_page(
				__( 'Welcome to All in One SEO Pack', 'all-in-one-seo-pack' ),
				__( 'Welcome to All in One SEO Pack', 'all-in-one-seo-pack' ),
				'manage_options',
				'aioseop-about',
				array( $this, 'about_screen' )
			);

		}

		function init() {

			if ( AIOSEOPPRO ) {
				return;
			}

			if ( ! is_admin() ) {
				return;
			}

			if ( ! current_user_can( 'manage_options' ) ) {
				return;
			}

			wp_safe_redirect( add_query_arg( array( 'page' => 'aioseop-about' ), admin_url( 'index.php' ) ) );
			exit;
		}

		function about_screen() {

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
