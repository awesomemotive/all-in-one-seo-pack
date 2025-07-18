<?php

namespace AIOSEO\Plugin\Common\Ai;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * AI class.
 *
 * @since 4.8.4
 */
class Ai {
	/**
	 * The base URL for the licensing server.
	 *
	 * @since 4.8.4
	 *
	 * @var string
	 */
	private $licensingUrl = 'https://licensing.aioseo.com/v1/';

	/**
	 * The action name for fetching credits.
	 *
	 * @since 4.8.4
	 *
	 * @var string
	 */
	protected $creditFetchAction = 'aioseo_ai_update_credits';

	/**
	 * Class constructor.
	 *
	 * @since 4.8.4
	 */
	public function __construct() {
		add_action( 'init', [ $this, 'getAccessToken' ] );

		add_action( 'init', [ $this, 'scheduleCreditFetchAction' ] );
		add_action( $this->creditFetchAction, [ $this, 'updateCredits' ] );

		// If param is set, fetch credits but just once per 5 minutes to prevent abuse.
		if ( isset( $_REQUEST['aioseo-ai-credits'] ) && ! aioseo()->core->cache->get( 'ai_get_credits' ) ) { // phpcs:ignore HM.Security.NonceVerification.Recommended
			add_action( 'init', [ $this, 'updateCredits' ] );

			aioseo()->core->cache->update( 'ai_get_credits', true, 5 * MINUTE_IN_SECONDS );
		}
	}

	/**
	 * Gets an access token from the server.
	 * This is the one-time access token that includes 50 free credits.
	 *
	 * @since 4.8.4
	 *
	 * @param  bool $refresh Whether to refresh the access token.
	 * @return void
	 */
	public function getAccessToken( $refresh = false ) {
		// Check if user has an access token. If not, get one from the server.
		if ( aioseo()->internalOptions->internal->ai->accessToken && ! $refresh ) {
			return;
		}

		if ( aioseo()->cache->get( 'ai-access-token-error' ) ) {
			return;
		}

		$response = wp_remote_post( $this->getApiUrl() . 'ai/auth/', [
			'body' => [
				'domain' => aioseo()->helpers->getSiteDomain()
			]
		] );

		if ( is_wp_error( $response ) ) {
			aioseo()->cache->update( 'ai-access-token-error', true, 1 * HOUR_IN_SECONDS );

			// Schedule another, one-time event in approx. 1 hour from now.
			aioseo()->actionScheduler->scheduleSingle( $this->creditFetchAction, 1 * ( HOUR_IN_SECONDS + wp_rand( 0, 30 * MINUTE_IN_SECONDS ) ), [] );

			return;
		}

		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body );
		if ( empty( $data->accessToken ) ) {
			aioseo()->cache->update( 'ai-access-token-error', true, 1 * HOUR_IN_SECONDS );

			// Schedule another, one-time event in approx. 1 hour from now.
			aioseo()->actionScheduler->scheduleSingle( $this->creditFetchAction, 1 * ( HOUR_IN_SECONDS + wp_rand( 0, 30 * MINUTE_IN_SECONDS ) ), [] );

			return;
		}

		aioseo()->internalOptions->internal->ai->accessToken        = sanitize_text_field( $data->accessToken );
		aioseo()->internalOptions->internal->ai->isTrialAccessToken = $data->isFree ?? false;

		// Fetch the credit totals.
		$this->updateCredits( true );
	}

	/**
	 * Schedules the credit fetch action.
	 *
	 * @since 4.8.4
	 *
	 * @return void
	 */
	public function scheduleCreditFetchAction() {
		// If not set up, create a scheduled action to refresh the credits each day.
		if ( ! aioseo()->actionScheduler->isScheduled( $this->creditFetchAction ) ) {
			aioseo()->actionScheduler->scheduleRecurrent( $this->creditFetchAction, DAY_IN_SECONDS, DAY_IN_SECONDS, [] );
		}
	}

	/**
	 * Gets the credit data from the server and updates our options.
	 *
	 * @since 4.8.4
	 *
	 * @param  bool $refresh Whether to refresh the credits forcefully.
	 * @return void
	 */
	public function updateCredits( $refresh = false ) {
		if ( aioseo()->cache->get( 'ai-credits-error' ) && ! $refresh ) {
			return;
		}

		if ( ! aioseo()->internalOptions->internal->ai->accessToken ) {
			return;
		}

		$response = aioseo()->helpers->wpRemoteGet( $this->getApiUrl() . 'ai/credits/', [
			'headers' => $this->getRequestHeaders()
		] );

		if ( is_wp_error( $response ) ) {
			aioseo()->cache->update( 'ai-credits-error', true, HOUR_IN_SECONDS );

			// Schedule another, one-time event in approx. 1 hour from now.
			aioseo()->actionScheduler->scheduleSingle( $this->creditFetchAction, 1 * ( HOUR_IN_SECONDS + wp_rand( 0, 30 * MINUTE_IN_SECONDS ) ), [] );

			return;
		}

		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body );
		if ( empty( $data->success ) ) {
			if ( ! empty( $data->code ) && 'invalid-token' === $data->code ) {
				// Drop the access token in case it could not be found.
				aioseo()->internalOptions->internal->ai->accessToken = '';
			}

			aioseo()->cache->update( 'ai-credits-error', true, HOUR_IN_SECONDS );

			// Schedule another, one-time event in approx. 1 hour from now.
			aioseo()->actionScheduler->scheduleSingle( $this->creditFetchAction, 1 * ( HOUR_IN_SECONDS + wp_rand( 0, 30 * MINUTE_IN_SECONDS ) ), [] );

			return;
		}

		$orders = [];
		if ( ! empty( $data->orders ) ) {
			foreach ( $data->orders as $order ) {
				if (
					empty( $order->total ) ||
					! isset( $order->remaining ) ||
					! isset( $order->expires )
				) {
					continue;
				}

				$orders[] = [
					'total'     => intval( $order->total ),
					'remaining' => intval( $order->remaining ),
					'expires'   => intval( $order->expires )
				];
			}
		}

		aioseo()->internalOptions->internal->ai->credits->orders    = $orders;
		aioseo()->internalOptions->internal->ai->credits->total     = isset( $data->total ) ? intval( $data->total ) : 0;
		aioseo()->internalOptions->internal->ai->credits->remaining = isset( $data->remaining ) ? intval( $data->remaining ) : 0;

		if ( ! empty( $data->license ) ) {
			aioseo()->internalOptions->internal->ai->credits->license->total     = intval( $data->license->total );
			aioseo()->internalOptions->internal->ai->credits->license->remaining = intval( $data->license->remaining );
			aioseo()->internalOptions->internal->ai->credits->license->expires   = intval( $data->license->expires );
		} else {
			aioseo()->internalOptions->internal->ai->credits->license->reset();
		}
	}

	/**
	 * Returns the default request headers.
	 *
	 * @since 4.8.4
	 *
	 * @return array The default request headers.
	 */
	protected function getRequestHeaders() {
		$headers = [
			'X-AIOSEO-Ai-Token'  => aioseo()->internalOptions->internal->ai->accessToken,
			'X-AIOSEO-Ai-Domain' => aioseo()->helpers->getSiteDomain()
		];

		return $headers;
	}

	/**
	 * Returns the API URL of the licensing server.
	 *
	 * @since 4.8.4
	 *
	 * @return string The URL.
	 */
	protected function getApiUrl() {
		if ( defined( 'AIOSEO_LICENSING_URL' ) ) {
			return AIOSEO_LICENSING_URL;
		}

		return $this->licensingUrl;
	}
}