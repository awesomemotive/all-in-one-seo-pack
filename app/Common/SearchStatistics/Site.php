<?php
namespace AIOSEO\Plugin\Common\SearchStatistics;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles the site for the search statistics.
 *
 * @since 4.6.2
 */
class Site {
	/**
	 * The action name.
	 *
	 * @since 4.6.2
	 *
	 * @var string
	 */
	public $action = 'aioseo_search_statistics_site_check';

	/**
	 * Class constructor.
	 *
	 * @since   4.6.2
	 * @version 4.9.4.2 Change admin_init to init to allow frontend scheduling.
	 */
	public function __construct() {
		add_action( 'admin_init', [ $this, 'init' ] );
		add_action( $this->action, [ $this, 'worker' ] );
	}

	/**
	 * Initialize the class and schedule the weekly recurring check.
	 *
	 * @since   4.6.2
	 * @version 4.9.4.2 Switch to weekly recurring action.
	 *
	 * @return void
	 */
	public function init() {
		if ( ! aioseo()->searchStatistics->api->auth->isConnected() ) {
			return;
		}

		if ( aioseo()->actionScheduler->isScheduled( $this->action ) ) {
			return;
		}

		aioseo()->actionScheduler->scheduleRecurrent( $this->action, 10, WEEK_IN_SECONDS );
	}

	/**
	 * Check whether the site is verified on Google Search Console and verifies it if needed.
	 *
	 * @since   4.6.2
	 * @version 4.9.4.2 Add runtime lock; remove self-scheduling (now handled by recurring action).
	 *
	 * @return void
	 */
	public function worker() {
		// Runtime lock: Prevent concurrent execution of this action.
		$lockKey = 'as_site_check_running';
		if ( aioseo()->core->cache->get( $lockKey ) ) {
			return;
		}

		// Set lock with a safety timeout in case the action fails mid-execution.
		aioseo()->core->cache->update( $lockKey, true, 5 * MINUTE_IN_SECONDS );

		if ( ! aioseo()->searchStatistics->api->auth->isConnected() ) {
			aioseo()->core->cache->delete( $lockKey );

			return;
		}

		$siteStatus = $this->checkStatus();
		if ( ! empty( $siteStatus ) ) {
			$this->processStatus( $siteStatus );
		}

		aioseo()->core->cache->delete( $lockKey );
	}

	/**
	 * Maybe verifies the site on Google Search Console.
	 *
	 * @since 4.6.2
	 *
	 * @return void
	 */
	public function maybeVerify() {
		if ( ! aioseo()->searchStatistics->api->auth->isConnected() ) {
			return;
		}

		$siteStatus = $this->checkStatus();
		if ( empty( $siteStatus ) ) {
			return;
		}

		$this->processStatus( $siteStatus );
	}

	/**
	 * Checks the site status on Google Search Console.
	 *
	 * @since 4.6.2
	 *
	 * @return array The site status.
	 */
	private function checkStatus() {
		$api      = new Api\Request( 'google-search-console/site/check/' );
		$response = $api->request();

		if ( is_wp_error( $response ) ) {
			return [];
		}

		return $response;
	}

	/**
	 * Processes the site status.
	 *
	 * @since 4.6.3
	 *
	 * @param  array $siteStatus The site status.
	 * @return void
	 */
	private function processStatus( $siteStatus ) {
		switch ( $siteStatus['code'] ) {
			case 'site_verified':
				aioseo()->internalOptions->searchStatistics->site->verified  = true;
				aioseo()->internalOptions->searchStatistics->site->lastFetch = time();
				break;
			case 'verification_needed':
				$this->verify( $siteStatus['data'] );
				break;
			case 'site_not_found':
			case 'couldnt_get_token':
			default:
				aioseo()->internalOptions->searchStatistics->site->verified  = false;
				aioseo()->internalOptions->searchStatistics->site->lastFetch = time();
		}
	}

	/**
	 * Verifies the site on Google Search Console.
	 *
	 * @since 4.6.2
	 *
	 * @param  string $token The verification token.
	 * @return void
	 */
	private function verify( $token = '' ) {
		if ( empty( $token ) ) {
			return;
		}

		aioseo()->options->webmasterTools->google = esc_attr( $token );

		$api      = new Api\Request( 'google-search-console/site/verify/' );
		$response = $api->request();

		if ( is_wp_error( $response ) || 'site_verified' !== $response['code'] ) {
			return;
		}

		aioseo()->internalOptions->searchStatistics->site->verified  = true;
		aioseo()->internalOptions->searchStatistics->site->lastFetch = time();
	}
}