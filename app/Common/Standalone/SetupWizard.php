<?php
namespace AIOSEO\Plugin\Common\Standalone;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class that holds our setup wizard.
 *
 * @since 4.0.0
 */
class SetupWizard {
	/**
	 * Class constructor.
	 *
	 * @since 4.0.0
	 */
	public function __construct() {
		if ( ! is_admin() || wp_doing_cron() || wp_doing_ajax() ) {
			return;
		}

		add_action( 'admin_menu', [ $this, 'addDashboardPage' ] );
		add_action( 'admin_head', [ $this, 'hideDashboardPageFromMenu' ] );
		add_action( 'admin_init', [ $this, 'maybeLoadOnboardingWizard' ] );
		add_action( 'admin_init', [ $this, 'redirect' ], 9999 );
	}

	/**
	 * Onboarding Wizard redirect.
	 *
	 * This function checks if a new install or update has just occurred. If so,
	 * then we redirect the user to the appropriate page.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function redirect() {
		// Check if we should consider redirection.
		if ( ! aioseo()->core->cache->get( 'activation_redirect' ) ) {
			return;
		}

		// If we are redirecting, clear the transient so it only happens once.
		aioseo()->core->cache->delete( 'activation_redirect' );

		// Check option to disable welcome redirect.
		if ( get_option( 'aioseo_activation_redirect', false ) ) {
			return;
		}

		// Only do this for single site installs.
		if ( isset( $_GET['activate-multi'] ) || is_network_admin() ) { // phpcs:ignore HM.Security.NonceVerification.Recommended
			return;
		}

		wp_safe_redirect( admin_url( 'index.php?page=aioseo-setup-wizard' ) );
		exit;
	}

	/**
	 * Adds a dashboard page for our setup wizard.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function addDashboardPage() {
		add_dashboard_page( '', '', aioseo()->admin->getPageRequiredCapability( 'aioseo-setup-wizard' ), 'aioseo-setup-wizard', '' );
	}

	/**
	 * Hide the dashboard page from the menu.
	 *
	 * @since 4.1.5
	 *
	 * @return void
	 */
	public function hideDashboardPageFromMenu() {
		remove_submenu_page( 'index.php', 'aioseo-setup-wizard' );
	}

	/**
	 * Checks to see if we should load the setup wizard.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function maybeLoadOnboardingWizard() {
		// Don't load the interface if doing an ajax call.
		if ( wp_doing_ajax() || wp_doing_cron() ) {
			return;
		}

		// Check for wizard-specific parameter
		// Allow plugins to disable the setup wizard
		// Check if current user is allowed to save settings.
		if (
			! isset( $_GET['page'] ) || // phpcs:ignore HM.Security.NonceVerification.Recommended
			'aioseo-setup-wizard' !== sanitize_text_field( wp_unslash( $_GET['page'] ) ) || // phpcs:ignore HM.Security.NonceVerification.Recommended
			! current_user_can( aioseo()->admin->getPageRequiredCapability( 'aioseo-setup-wizard' ) )
		) {
			return;
		}

		set_current_screen();

		// Remove an action in the Gutenberg plugin ( not core Gutenberg ) which throws an error.
		remove_action( 'admin_print_styles', 'gutenberg_block_editor_admin_print_styles' );

		// If we are redirecting, clear the transient so it only happens once.
		aioseo()->core->cache->delete( 'activation_redirect' );

		$this->loadOnboardingWizard();
	}

	/**
	 * Load the Onboarding Wizard template.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	private function loadOnboardingWizard() {
		$this->enqueueScripts();
		$this->setupWizardHeader();
		$this->setupWizardContent();
		$this->setupWizardFooter();
		exit;
	}

	/**
	 * Enqueue's scripts for the setup wizard.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function enqueueScripts() {
		// We don't want any plugin adding notices to our screens. Let's clear them out here.
		remove_all_actions( 'admin_notices' );
		remove_all_actions( 'all_admin_notices' );

		aioseo()->core->assets->load( 'src/vue/standalone/setup-wizard/main.js', [], aioseo()->helpers->getVueData( 'setup-wizard' ) );

		aioseo()->main->enqueueTranslations();

		wp_enqueue_style( 'common' );
		wp_enqueue_media();
	}

	/**
	 * Outputs the simplified header used for the Onboarding Wizard.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function setupWizardHeader() {
		?>
		<!DOCTYPE html>
		<html <?php language_attributes(); ?>>
		<head>
			<meta name="viewport" content="width=device-width"/>
			<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
			<title>
			<?php
				// Translators: 1 - The plugin name ("All in One SEO").
				echo sprintf( esc_html__( '%1$s &rsaquo; Onboarding Wizard', 'all-in-one-seo-pack' ), esc_html( AIOSEO_PLUGIN_SHORT_NAME ) );
			?>
			</title>
			<?php do_action( 'admin_print_scripts' ); ?>
			<?php do_action( 'admin_print_styles' ); ?>
			<?php do_action( 'admin_head' ); ?>
		</head>
		<body class="aioseo-setup-wizard">
		<?php
	}

	/**
	 * Outputs the content of the current step.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function setupWizardContent() {
		echo '<div id="aioseo-app">';
		aioseo()->templates->getTemplate( 'admin/settings-page.php' );
		echo '</div>';
	}

	/**
	 * Outputs the simplified footer used for the Onboarding Wizard.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function setupWizardFooter() {
		?>
		<?php
		wp_print_scripts( 'aioseo-vendors' );
		wp_print_scripts( 'aioseo-common' );
		wp_print_scripts( 'aioseo-setup-wizard-script' );
		do_action( 'admin_footer', '' );
		do_action( 'admin_print_footer_scripts' );
		// do_action( 'customize_controls_print_footer_scripts' );
		?>
		</body>
		</html>
		<?php
	}

	/**
	 * Check whether or not the Setup Wizard is completed.
	 *
	 * @since 4.2.0
	 *
	 * @return boolean Whether or not the Setup Wizard is completed.
	 */
	public function isCompleted() {
		$wizard = (string) aioseo()->internalOptions->internal->wizard;
		$wizard = json_decode( $wizard );
		if ( ! $wizard ) {
			return false;
		}

		$totalStageCount   = count( $wizard->stages );
		$currentStageCount = array_search( $wizard->currentStage, $wizard->stages, true );

		// If not found, let's assume it's completed.
		if ( false === $currentStageCount ) {
			return true;
		}

		return $currentStageCount + 1 === $totalStageCount;
	}
}