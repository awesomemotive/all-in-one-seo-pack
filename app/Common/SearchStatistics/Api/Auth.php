<?php
namespace AIOSEO\Plugin\Common\SearchStatistics\Api;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles the authentication/connection to our microservice.
 *
 * @since   4.3.0
 * @version 4.6.2 Moved from Pro to Common.
 */
class Auth {
	/**
	 * The authenticated profile data.
	 *
	 * @since 4.3.0
	 *
	 * @var array
	 */
	private $profile = [];

	/**
	 * The type of authentication.
	 *
	 * @since 4.6.2
	 *
	 * @var string
	 */
	public $type = 'lite';

	/**
	 * Class constructor.
	 *
	 * @since 4.3.0
	 */
	public function __construct() {
		$this->profile = $this->getProfile();

		if ( aioseo()->pro ) {
			$this->type = 'pro';
		}
	}

	/**
	 * Returns the authenticated profile.
	 *
	 * @since 4.3.0
	 *
	 * @param  bool  $force Busts the cache and forces an update of the profile data.
	 * @return array        The authenticated profile data.
	 */
	public function getProfile( $force = false ) {
		if ( ! empty( $this->profile ) && ! $force ) {
			return $this->profile;
		}

		$this->profile = [
			'siteurl'    => aioseo()->internalOptions->internal->searchStatistics->profile->siteurl,
			'authedsite' => aioseo()->internalOptions->internal->searchStatistics->profile->authedsite
		];

		return $this->profile;
	}

	/**
	 * Returns the profile key.
	 *
	 * @since 4.3.0
	 *
	 * @return string The profile key.
	 */
	public function getKey() {
		return aioseo()->sensitiveOptions->get( 'searchStatisticsProfileKey' );
	}

	/**
	 * Returns the profile token.
	 *
	 * @since 4.3.0
	 *
	 * @return string The profile token.
	 */
	public function getToken() {
		return aioseo()->sensitiveOptions->get( 'searchStatisticsProfileToken' );
	}

	/**
	 * Returns the authenticated site.
	 *
	 * @since 4.3.0
	 *
	 * @return string The authenticated site.
	 */
	public function getAuthedSite() {
		return ! empty( $this->profile['authedsite'] ) ? $this->profile['authedsite'] : '';
	}

	/**
	 * Sets the profile data.
	 *
	 * @since 4.3.0
	 *
	 * @return void
	 */
	public function setProfile( $data = [] ) {
		// Save sensitive data separately.
		if ( ! empty( $data['key'] ) ) {
			aioseo()->sensitiveOptions->set( 'searchStatisticsProfileKey', $data['key'] );
		}
		if ( ! empty( $data['token'] ) ) {
			aioseo()->sensitiveOptions->set( 'searchStatisticsProfileToken', $data['token'] );
		}

		$siteurl    = ! empty( $data['siteurl'] ) ? (string) $data['siteurl'] : '';
		$authedsite = ! empty( $data['authedsite'] ) ? (string) $data['authedsite'] : '';

		aioseo()->internalOptions->internal->searchStatistics->profile->siteurl    = $siteurl;
		aioseo()->internalOptions->internal->searchStatistics->profile->authedsite = $authedsite;

		$this->profile = [
			'siteurl'    => $siteurl,
			'authedsite' => $authedsite
		];
	}

	/**
	 * Deletes the profile data.
	 *
	 * @since 4.3.0
	 *
	 * @return void
	 */
	public function deleteProfile() {
		$this->setProfile( [] );

		// Clear sensitive data.
		aioseo()->sensitiveOptions->delete( 'searchStatisticsProfileKey' );
		aioseo()->sensitiveOptions->delete( 'searchStatisticsProfileToken' );
	}

	/**
	 * Check whether we are connected.
	 *
	 * @since 4.3.0
	 *
	 * @return bool Whether we are connected or not.
	 */
	public function isConnected() {
		return aioseo()->sensitiveOptions->hasValue( 'searchStatisticsProfileKey' );
	}

	/**
	 * Verifies whether the authentication details are valid.
	 *
	 * @since 4.3.0
	 *
	 * @return bool Whether the data is valid or not.
	 */
	public function verify( $credentials = [] ) {
		if ( ! empty( $credentials ) ) {
			$key   = $credentials['key'];
			$token = $credentials['token'];
		} else {
			$key   = aioseo()->sensitiveOptions->get( 'searchStatisticsProfileKey' );
			$token = aioseo()->sensitiveOptions->get( 'searchStatisticsProfileToken' );
		}

		if ( empty( $key ) ) {
			return new \WP_Error( 'validation-error', 'Authentication key is missing.' );
		}

		$request = new Request( "auth/verify/{$this->type}/", [
			'tt'      => aioseo()->searchStatistics->api->trustToken->get(),
			'key'     => $key,
			'token'   => $token,
			'testurl' => 'https://' . aioseo()->searchStatistics->api->getApiUrl() . '/v1/test/',
		] );
		$response = $request->request();

		aioseo()->searchStatistics->api->trustToken->rotate();

		return ! is_wp_error( $response );
	}

	/**
	 * Removes all authentication data.
	 *
	 * @since 4.3.0
	 *
	 * @return bool Whether the authentication data was deleted or not.
	 */
	public function delete() {
		if ( ! $this->isConnected() ) {
			return false;
		}

		$key   = aioseo()->sensitiveOptions->get( 'searchStatisticsProfileKey' );
		$token = aioseo()->sensitiveOptions->get( 'searchStatisticsProfileToken' );
		if ( empty( $key ) ) {
			return false;
		}

		( new Request( "auth/delete/{$this->type}/", [
			'tt'      => aioseo()->searchStatistics->api->trustToken->get(),
			'key'     => $key,
			'token'   => $token,
			'testurl' => 'https://' . aioseo()->searchStatistics->api->getApiUrl() . '/v1/test/',
		] ) )->request();

		aioseo()->searchStatistics->api->trustToken->rotate();
		aioseo()->searchStatistics->api->auth->deleteProfile();
		aioseo()->searchStatistics->reset();

		// Resets the results for the Google meta tag.
		aioseo()->options->webmasterTools->google = '';

		return true;
	}
}