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
	 * @since 4.6.2
	 */
	public function __construct() {
		add_action( 'admin_init', [ $this, 'init' ] );
		add_action( $this->action, [ $this, 'worker' ] );
	}

	/**
	 * Initialize the class.
	 *
	 * @since 4.6.2
	 *
	 * @return void
	 */
	public function init() {
		if (
			! aioseo()->searchStatistics->api->auth->isConnected() ||
			aioseo()->actionScheduler->isScheduled( $this->action )
		) {
			return;
		}

		aioseo()->actionScheduler->scheduleAsync( $this->action );
	}

	/**
	 * Check whether the site is verified on Google Search Console and verifies it if needed.
	 *
	 * @since 4.6.2
	 *
	 * @return void
	 */
	public function worker() {
		if ( ! aioseo()->searchStatistics->api->auth->isConnected() ) {
			return;
		}

		$siteStatus = $this->checkStatus();
		if ( ! empty( $siteStatus ) ) {
			$this->processStatus( $siteStatus );
		}

		// Schedule a new check for the next week.
		aioseo()->actionScheduler->scheduleSingle( $this->action, WEEK_IN_SECONDS + wp_rand( 0, 3 * DAY_IN_SECONDS ), [], true );
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