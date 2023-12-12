<?php
namespace AIOSEO\Plugin\Lite\Admin;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AIOSEO\Plugin\Common\Utils;

/**
 * Connect to AIOSEO Pro Worker Service to connect with our Premium Services.
 *
 * @since 4.0.0
 */
class Connect {
	/**
	 * Class constructor.
	 *
	 * @since 4.0.0
	 */
	public function __construct() {
		add_action( 'wp_ajax_nopriv_aioseo_connect_process', [ $this, 'process' ] );

		add_action( 'admin_menu', [ $this, 'addDashboardPage' ] );
		add_action( 'admin_init', [ $this, 'maybeLoadConnect' ] );
	}

	/**
	 * Adds a dashboard page for our setup wizard.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function addDashboardPage() {
		add_dashboard_page( '', '', 'aioseo_manage_seo', 'aioseo-connect-pro', '' );
		remove_submenu_page( 'index.php', 'aioseo-connect-pro' );
		add_dashboard_page( '', '', 'aioseo_manage_seo', 'aioseo-connect', '' );
		remove_submenu_page( 'index.php', 'aioseo-connect' );
	}

	/**
	 * Checks to see if we should load the setup wizard.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function maybeLoadConnect() {
		// phpcs:disable HM.Security.ValidatedSanitizedInput.InputNotSanitized, HM.Security.NonceVerification.Recommended
		// Don't load the interface if doing an ajax call.
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			return;
		}

		// Check for connect-specific parameter
		// Allow plugins to disable the connect
		// Check if current user is allowed to save settings.
		if (
			! isset( $_GET['page'] ) ||
			( 'aioseo-connect-pro' !== $_GET['page'] && 'aioseo-connect' !== sanitize_text_field( wp_unslash( $_GET['page'] ) ) ) ||
			! current_user_can( 'aioseo_manage_seo' )
		) {
			return;
		}

		set_current_screen();

		// Remove an action in the Gutenberg plugin ( not core Gutenberg ) which throws an error.
		remove_action( 'admin_print_styles', 'gutenberg_block_editor_admin_print_styles' );

		if ( 'aioseo-connect-pro' === sanitize_text_field( wp_unslash( $_GET['page'] ) ) ) {
			$this->loadConnectPro();

			return;
		}

		$this->loadConnect();
		// phpcs:enable
	}

	/**
	 * Load the Connect template.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	private function loadConnect() {
		$this->enqueueScripts();
		$this->connectHeader();
		$this->connectContent();
		$this->connectFooter();
		exit;
	}

	/**
	 * Load the Connect Pro template.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	private function loadConnectPro() {
		$this->enqueueScriptsPro();
		$this->connectHeader();
		$this->connectContent();
		$this->connectFooter( 'pro' );
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

		aioseo()->core->assets->load( 'src/vue/standalone/connect/main.js', [], aioseo()->helpers->getVueData() );
	}

	/**
	 * Enqueue's scripts for the setup wizard.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function enqueueScriptsPro() {
		// We don't want any plugin adding notices to our screens. Let's clear them out here.
		remove_all_actions( 'admin_notices' );
		remove_all_actions( 'all_admin_notices' );

		aioseo()->core->assets->load( 'src/vue/standalone/connect-pro/main.js', [], aioseo()->helpers->getVueData() );
	}

	/**
	 * Outputs the simplified header used for the Onboarding Wizard.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function connectHeader() {
		?>
		<!DOCTYPE html>
		<html <?php language_attributes(); ?>>
		<head>
			<meta name="viewport" content="width=device-width"/>
			<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
			<title>
			<?php
				// Translators: 1 - The plugin name ("All in One SEO").
				echo sprintf( esc_html__( '%1$s &rsaquo; Connect', 'all-in-one-seo-pack' ), esc_html( AIOSEO_PLUGIN_NAME ) );
			?>
			</title>
		</head>
		<body class="aioseo-connect">
		<?php
	}

	/**
	 * Outputs the content of the current step.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function connectContent() {
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
	public function connectFooter( $pro = '' ) {
		?>
		<?php
		wp_print_scripts( 'aioseo-vendors' );
		wp_print_scripts( 'aioseo-common' );
		wp_print_scripts( "aioseo-connect-$pro-script" );
		?>
		</body>
		</html>
		<?php
	}

	/**
	 * Generates and returns the AIOSEO Connect URL.
	 *
	 * @since 4.0.0
	 *
	 * @return array The AIOSEO Connect URL or an error message inside an array.
	 */
	public function generateConnectUrl( $key, $redirect = null ) {
		// Check for permissions.
		if ( ! current_user_can( 'install_plugins' ) ) {
			return [
				'error' => esc_html__( 'You are not allowed to install plugins.', 'all-in-one-seo-pack' )
			];
		}

		if ( empty( $key ) ) {
			return [
				'error' => esc_html__( 'Please enter your license key to connect.', 'all-in-one-seo-pack' ),
			];
		}

		// Verify pro version is not installed.
		$active = activate_plugin( 'all-in-one-seo-pack-pro/all_in_one_seo_pack_pro', false, false, true );

		if ( ! is_wp_error( $active ) ) {
			// Deactivate plugin.
			deactivate_plugins( plugin_basename( AIOSEO_FILE ), false, false );

			return [
				'error' => esc_html__( 'Pro version is already installed.', 'all-in-one-seo-pack' )
			];
		}

		// Just check if network is set.
		$network = isset( $_POST['network'] ) ? (bool) wp_unslash( $_POST['network'] ) : false; // phpcs:ignore HM.Security.ValidatedSanitizedInput.InputNotSanitized, HM.Security.NonceVerification.Missing, Generic.Files.LineLength.MaxExceeded
		$network = ! empty( $network );

		// Generate a hash that can be compared after the user is redirected back.
		$oth       = hash( 'sha512', wp_rand() );
		$hashedOth = hash_hmac( 'sha512', $oth, wp_salt() );

		// Save the options.
		aioseo()->internalOptions->internal->connect->key     = $key;
		aioseo()->internalOptions->internal->connect->time    = time();
		aioseo()->internalOptions->internal->connect->network = $network;
		aioseo()->internalOptions->internal->connect->token   = $oth;

		$url = add_query_arg( [
			'key'      => $key,
			'network'  => $network,
			'token'    => $hashedOth,
			'version'  => aioseo()->version,
			'siteurl'  => admin_url(),
			'homeurl'  => home_url(),
			'endpoint' => admin_url( 'admin-ajax.php' ),
			'php'      => PHP_VERSION,
			'wp'       => get_bloginfo( 'version' ),
			'redirect' => rawurldecode( base64_encode( $redirect ? $redirect : admin_url( 'admin.php?page=aioseo-settings' ) ) ),
			'v'        => 1,
		], defined( 'AIOSEO_UPGRADE_URL' ) ? AIOSEO_UPGRADE_URL : 'https://upgrade.aioseo.com' );

		// We're storing the ID of the user who is installing Pro so that we can add capabilties for him after upgrading.
		aioseo()->core->cache->update( 'connect_active_user', get_current_user_id(), 15 * MINUTE_IN_SECONDS );

		return [
			'url' => $url,
		];
	}

	/**
	 * Process AIOSEO Connect.
	 *
	 * @since 1.0.0
	 *
	 * @return array An array containing a valid response or an error message.
	 */
	public function process() {
		$hashedOth   = ! empty( $_POST['token'] ) ? sanitize_text_field( wp_unslash( $_POST['token'] ) ) : ''; // phpcs:ignore HM.Security.NonceVerification.Missing
		$downloadUrl = ! empty( $_POST['file'] ) ? esc_url_raw( wp_unslash( $_POST['file'] ) ) : ''; // phpcs:ignore HM.Security.NonceVerification.Missing

		$error = sprintf(
			// Translators: 1 - The marketing site domain ("aioseo.com").
			esc_html__( 'Could not install upgrade. Please download from %1$s and install manually.', 'all-in-one-seo-pack' ),
			esc_html( AIOSEO_MARKETING_DOMAIN )
		);

		$success = esc_html__( 'Plugin installed & activated.', 'all-in-one-seo-pack' );

		// Check if all required params are present.
		if ( empty( $downloadUrl ) || empty( $hashedOth ) ) {
			wp_send_json_error( $error );
		}

		$oth = aioseo()->internalOptions->internal->connect->token;
		if ( empty( $oth ) ) {
			wp_send_json_error( $error );
		}

		// Check if the stored hash matches the salted one that is sent back from the server.
		if ( hash_hmac( 'sha512', $oth, wp_salt() ) !== $hashedOth ) {
			wp_send_json_error( $error );
		}

		// Delete connect token so we don't replay.
		aioseo()->internalOptions->internal->connect->token = null;

		// Verify pro not activated.
		if ( aioseo()->pro ) {
			wp_send_json_success( $success );
		}

		// Check license key.
		$licenseKey = aioseo()->internalOptions->internal->connect->key;
		if ( ! $licenseKey ) {
			wp_send_json_error( esc_html__( 'You are not licensed.', 'all-in-one-seo-pack' ) );
		}

		// Set the license key in a new option so we can get it when Pro is activated.
		aioseo()->internalOptions->internal->validLicenseKey = $licenseKey;

		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/class-wp-screen.php';
		require_once ABSPATH . 'wp-admin/includes/screen.php';

		// Set the current screen to avoid undefined notices.
		set_current_screen( 'toplevel_page_aioseo' );

		// Prepare variables.
		$url = esc_url_raw(
			add_query_arg(
				[
					'page' => 'aioseo-settings',
				],
				admin_url( 'admin.php' )
			)
		);

		// Verify pro not installed.
		$network = aioseo()->internalOptions->internal->connect->network;
		$active  = activate_plugin( 'all-in-one-seo-pack-pro/all_in_one_seo_pack.php', $url, $network, true );
		if ( ! is_wp_error( $active ) ) {
			aioseo()->internalOptions->internal->connect->reset();

			// Because the regular activation hooks won't run, we need to add capabilities for the installing user so that he doesn't run into an error on the first request.
			aioseo()->activate->addCapabilitiesOnUpgrade();

			deactivate_plugins( plugin_basename( AIOSEO_FILE ), false, $network );

			wp_send_json_success( $success );
		}

		$creds = request_filesystem_credentials( $url, '', false, false, null );
		// Check for file system permissions.
		if ( false === $creds ) {
			wp_send_json_error( $error );
		}

		$fs = aioseo()->core->fs->noConflict();
		$fs->init( $creds );
		if ( ! $fs->isWpfsValid() ) {
			wp_send_json_error( $error );
		}

		// Do not allow WordPress to search/download translations, as this will break JS output.
		remove_action( 'upgrader_process_complete', [ 'Language_Pack_Upgrader', 'async_upgrade' ], 20 );

		// Create the plugin upgrader with our custom skin.
		$installer = new Utils\PluginUpgraderSilentAjax( new Utils\PluginUpgraderSkin() );

		// Error check.
		if ( ! method_exists( $installer, 'install' ) ) {
			wp_send_json_error( $error );
		}

		$installer->install( $downloadUrl );

		// Flush the cache and return the newly installed plugin basename.
		wp_cache_flush();

		$pluginBasename = $installer->plugin_info();

		if ( ! $pluginBasename ) {
			wp_send_json_error( $error );
		}

		// Activate the plugin silently.
		$activated = activate_plugin( $pluginBasename, '', $network, true );
		if ( is_wp_error( $activated ) ) {
			wp_send_json_error( esc_html__( 'The Pro version installed correctly, but it needs to be activated from the Plugins page inside your WordPress admin.', 'all-in-one-seo-pack' ) );
		}

		aioseo()->internalOptions->internal->connect->reset();

		// Because the regular activation hooks won't run, we need to add capabilities for the installing user so that he doesn't run into an error on the first request.
		aioseo()->activate->addCapabilitiesOnUpgrade();

		deactivate_plugins( plugin_basename( AIOSEO_FILE ), false, $network );

		wp_send_json_success( $success );
	}
}