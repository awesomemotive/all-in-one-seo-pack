<?php
namespace AIOSEO\Plugin\Common\Main;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Abstract class that Pro and Lite both extend.
 *
 * @since 4.0.0
 */
class Activate {
	/**
	 * Construct method.
	 *
	 * @since 4.0.0
	 */
	public function __construct() {
		register_activation_hook( AIOSEO_FILE, [ $this, 'activate' ] );
		register_deactivation_hook( AIOSEO_FILE, [ $this, 'deactivate' ] );

		// The following only needs to happen when in the admin.
		if ( ! is_admin() ) {
			return;
		}

		// This needs to run on at least 1000 because we load the roles in the Access class on 999.
		add_action( 'init', [ $this, 'init' ], 1000 );
	}

	/**
	 * Initialize activation.
	 *
	 * @since 4.1.5
	 *
	 * @return void
	 */
	public function init() {
		// If Pro just deactivated the lite version, we need to manually run the activation hook, because it doesn't run here.
		$proDeactivatedLite = (bool) aioseo()->core->cache->get( 'pro_just_deactivated_lite' );
		if ( ! $proDeactivatedLite ) {
			// Also check for the old transient in the options table (because a user might switch from an older Lite version that lacks the Cache class).
			$proDeactivatedLite = (bool) get_option( '_aioseo_cache_pro_just_deactivated_lite' );
		}

		if ( $proDeactivatedLite ) {
			aioseo()->core->cache->delete( 'pro_just_deactivated_lite' );
			$this->activate( false );
		}
	}

	/**
	 * Runs on activation.
	 *
	 * @since 4.0.17
	 *
	 * @param  bool $networkWide Whether or not this is a network wide activation.
	 * @return void
	 */
	public function activate( $networkWide ) { // phpcs:ignore VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
		aioseo()->access->addCapabilities();

		// Make sure our tables exist.
		aioseo()->updates->addInitialCustomTablesForV4();

		// Set the activation timestamps.
		$time = time();
		aioseo()->internalOptions->internal->activated = $time;

		if ( ! aioseo()->internalOptions->internal->firstActivated ) {
			aioseo()->internalOptions->internal->firstActivated = $time;
		}

		aioseo()->core->cache->clear();

		$this->maybeRunSetupWizard();
	}

	/**
	 * Runs on deactivation.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function deactivate() {
		aioseo()->access->removeCapabilities();
	}

	/**
	 * Check if we should redirect on activation.
	 *
	 * @since 4.1.2
	 *
	 * @return void
	 */
	private function maybeRunSetupWizard() {
		if ( '0.0' !== aioseo()->internalOptions->internal->lastActiveVersion ) {
			return;
		}

		$oldOptions = get_option( 'aioseop_options' );
		if ( ! empty( $oldOptions ) ) {
			return;
		}

		if ( is_network_admin() ) {
			return;
		}

		if ( isset( $_GET['activate-multi'] ) ) { // phpcs:ignore HM.Security.NonceVerification.Recommended, WordPress.Security.NonceVerification.Recommended
			return;
		}

		// Sets 30 second transient for welcome screen redirect on activation.
		aioseo()->core->cache->update( 'activation_redirect', true, 30 );
	}

	/**
	 * Adds our capabilities to all roles on the next request and the installing user on the current request after upgrading to Pro.
	 *


	 *
	 * @since 4.1.4.4
	 *
	 * @return void
	 */
	public function addCapabilitiesOnUpgrade() {
		// In case the user is switching to Pro via the AIOSEO Connect feature,
		// we need to set this transient here as the regular activation hooks won't run and Pro otherwise won't clear the cache and add the required capabilities.
		aioseo()->core->cache->update( 'pro_just_deactivated_lite', true );

		// Doing the above isn't sufficient because the current user will be lacking the capabilities on the first request. Therefore, we add them manually just for him.
		$userId = function_exists( 'get_current_user_id' ) && get_current_user_id()
			? get_current_user_id() // If there is a logged in user, the user is switching from Lite to Pro via the Plugins menu.
			: aioseo()->core->cache->get( 'connect_active_user' ); // If there is no logged in user, we're upgrading via AIOSEO Connect.

		$user = get_userdata( $userId );
		if ( is_object( $user ) ) {
			$capabilities = aioseo()->access->getCapabilityList();
			foreach ( $capabilities as $capability ) {
				$user->add_cap( $capability );
			}
		}

		aioseo()->core->cache->delete( 'connect_active_user' );
	}
}