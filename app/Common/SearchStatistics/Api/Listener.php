<?php
namespace AIOSEO\Plugin\Common\SearchStatistics\Api;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// phpcs:disable WordPress.Security.NonceVerification.Recommended
// phpcs:disable HM.Security.NonceVerification.Recommended

/**
 * Class that holds our listeners for the microservice.
 *
 * @since   4.3.0
 * @version 4.6.2 Moved from Pro to Common.
 */
class Listener {
	/**
	 * Class constructor.
	 *
	 * @since 4.3.0
	 */
	public function __construct() {
		add_action( 'admin_init', [ $this, 'listenForAuthentication' ] );
		add_action( 'admin_init', [ $this, 'listenForReauthentication' ] );
		add_action( 'admin_init', [ $this, 'listenForReturningBack' ] );

		add_action( 'wp_ajax_nopriv_aioseo_is_installed', [ $this, 'isInstalled' ] );
		add_action( 'wp_ajax_nopriv_aioseo_rauthenticate', [ $this, 'reauthenticate' ] );
	}

	/**
	 * Listens to the response from the microservice server.
	 *
	 * @since 4.3.0
	 *
	 * @return void
	 */
	public function listenForAuthentication() {
		if ( empty( $_REQUEST['aioseo-oauth-action'] ) || 'auth' !== $_REQUEST['aioseo-oauth-action'] ) {
			return;
		}

		if (
			! aioseo()->access->hasCapability( 'aioseo_search_statistics_settings' ) ||
			! aioseo()->access->hasCapability( 'aioseo_general_settings' ) ||
			! aioseo()->access->hasCapability( 'aioseo_setup_wizard' )
		) {
			return;
		}

		if ( empty( $_REQUEST['tt'] ) || empty( $_REQUEST['key'] ) || empty( $_REQUEST['token'] ) || empty( $_REQUEST['authedsite'] ) ) {
			return;
		}

		if ( ! aioseo()->searchStatistics->api->trustToken->validate( sanitize_text_field( wp_unslash( $_REQUEST['tt'] ) ) ) ) {
			return;
		}

		$profile = [
			'key'        => sanitize_text_field( wp_unslash( $_REQUEST['key'] ) ),
			'token'      => sanitize_text_field( wp_unslash( $_REQUEST['token'] ) ),
			'siteurl'    => site_url(),
			'authedsite' => esc_url_raw( wp_unslash( $this->getAuthenticatedDomain() ) )
		];

		$success = aioseo()->searchStatistics->api->auth->verify( $profile );
		if ( ! $success ) {
			return;
		}

		$this->saveAndRedirect( $profile );
	}

	/**
	 * Listens to for the reauthentication response from the microservice.
	 *
	 * @since 4.3.0
	 *
	 * @return void
	 */
	public function listenForReauthentication() {
		if ( empty( $_REQUEST['aioseo-oauth-action'] ) || 'reauth' !== $_REQUEST['aioseo-oauth-action'] ) {
			return;
		}

		if (
			! aioseo()->access->hasCapability( 'aioseo_search_statistics_settings' ) ||
			! aioseo()->access->hasCapability( 'aioseo_general_settings' ) ||
			! aioseo()->access->hasCapability( 'aioseo_setup_wizard' )
		) {
			return;
		}

		if ( empty( $_REQUEST['tt'] ) || empty( $_REQUEST['authedsite'] ) ) {
			return;
		}

		if ( ! aioseo()->searchStatistics->api->trustToken->validate( sanitize_text_field( wp_unslash( $_REQUEST['tt'] ) ) ) ) {
			return;
		}

		$existingProfile = aioseo()->searchStatistics->api->auth->getProfile( true );
		if ( empty( $existingProfile['key'] ) || empty( $existingProfile['token'] ) ) {
			return;
		}

		$profile = [
			'key'        => $existingProfile['key'],
			'token'      => $existingProfile['token'],
			'siteurl'    => site_url(),
			'authedsite' => esc_url_raw( wp_unslash( $this->getAuthenticatedDomain() ) )
		];

		$this->saveAndRedirect( $profile );
	}

	/**
	 * Listens for the response from the microservice when the user returns back.
	 *
	 * @since 4.6.2
	 *
	 * @return void
	 */
	public function listenForReturningBack() {
		if ( empty( $_REQUEST['aioseo-oauth-action'] ) || 'back' !== $_REQUEST['aioseo-oauth-action'] ) {
			return;
		}

		if (
			! aioseo()->access->hasCapability( 'aioseo_search_statistics_settings' ) ||
			! aioseo()->access->hasCapability( 'aioseo_general_settings' ) ||
			! aioseo()->access->hasCapability( 'aioseo_setup_wizard' )
		) {
			return;
		}

		wp_safe_redirect( $this->getRedirectUrl() );
		exit;
	}

	/**
	 * Return a success status code indicating that the plugin is installed.
	 *
	 * @since 4.3.0
	 *
	 * @return void
	 */
	public function isInstalled() {
		wp_send_json_success( [
			'version' => aioseo()->version,
			'pro'     => aioseo()->pro
		] );
	}

	/**
	 * Validate the trust token and tells the microservice that we can reauthenticate.
	 *
	 * @since 4.3.0
	 *
	 * @return void
	 */
	public function reauthenticate() {
		foreach ( [ 'key', 'token', 'tt' ] as $arg ) {
			if ( empty( $_REQUEST[ $arg ] ) ) {
				wp_send_json_error( [
					'error'   => 'authenticate_missing_arg',
					'message' => 'Authentication request missing parameter: ' . $arg,
					'version' => aioseo()->version,
					'pro'     => aioseo()->pro
				] );
			}
		}

		$trustToken = ! empty( $_REQUEST['tt'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['tt'] ) ) : '';
		if ( ! aioseo()->searchStatistics->api->trustToken->validate( $trustToken ) ) {
			wp_send_json_error( [
				'error'   => 'authenticate_invalid_tt',
				'message' => 'Invalid TT sent',
				'version' => aioseo()->version,
				'pro'     => aioseo()->pro
			] );
		}

		// If the trust token is validated, send a success response to trigger the regular auth process.
		wp_send_json_success();
	}

	/**
	 * Saves the authenticated account, clear the existing data and redirect back to the settings page.
	 *
	 * @since 4.3.0
	 *
	 * @return void
	 */
	private function saveAndRedirect( $profile ) {
		// Reset the search statistics data.
		aioseo()->searchStatistics->reset();

		// Save the authenticated profile.
		aioseo()->searchStatistics->api->auth->setProfile( $profile );

		// Reset dismissed alerts.
		$dismissedAlerts = aioseo()->settings->dismissedAlerts;
		foreach ( $dismissedAlerts as $key => $alert ) {
			if ( in_array( $key, [ 'searchConsoleNotConnected', 'searchConsoleSitemapErrors' ], true ) ) {
				$dismissedAlerts[ $key ] = false;
			}
		}
		aioseo()->settings->dismissedAlerts = $dismissedAlerts;

		// Maybe verifies the site.
		aioseo()->searchStatistics->site->maybeVerify();

		// Redirects to the original page.
		wp_safe_redirect( $this->getRedirectUrl() );
		exit;
	}

	/**
	 * Returns the authenticated domain.
	 *
	 * @since 4.3.0
	 *
	 * @return string The authenticated domain.
	 */
	private function getAuthenticatedDomain() {
		if ( empty( $_REQUEST['authedsite'] ) ) {
			return '';
		}

		$authedSite = sanitize_text_field( wp_unslash( $_REQUEST['authedsite'] ) );
		if ( false !== aioseo()->helpers->stringIndex( $authedSite, 'sc-domain:' ) ) {
			$authedSite = str_replace( 'sc-domain:', '', $authedSite );
		}

		return $authedSite;
	}

	/**
	 * Gets the redirect URL.
	 *
	 * @since 4.6.2
	 *
	 * @return string The redirect URL.
	 */
	private function getRedirectUrl() {
		$returnTo    = ! empty( $_REQUEST['return-to'] ) ? sanitize_key( $_REQUEST['return-to'] ) : '';
		$redirectUrl = 'admin.php?page=aioseo';

		switch ( $returnTo ) {
			case 'webmaster-tools':
				$redirectUrl = 'admin.php?page=aioseo-settings#/webmaster-tools?activetool=googleSearchConsole';
				break;
			case 'setup-wizard':
				$redirectUrl = 'index.php?page=aioseo-setup-wizard#/' . aioseo()->standalone->setupWizard->getNextStage();
				break;
			case 'search-statistics':
				$redirectUrl = 'admin.php?page=aioseo-search-statistics/#search-statistics';
				break;
		}

		return admin_url( $redirectUrl );
	}
}