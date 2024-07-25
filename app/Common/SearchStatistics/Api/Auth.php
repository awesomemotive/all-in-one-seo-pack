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

		$this->profile = aioseo()->internalOptions->internal->searchStatistics->profile;

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
		return ! empty( $this->profile['key'] ) ? $this->profile['key'] : '';
	}

	/**
	 * Returns the profile token.
	 *
	 * @since 4.3.0
	 *
	 * @return string The profile token.
	 */
	public function getToken() {
		return ! empty( $this->profile['token'] ) ? $this->profile['token'] : '';
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
		$this->profile = $data;

		aioseo()->internalOptions->internal->searchStatistics->profile = $this->profile;
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
	}

	/**
	 * Check whether we are connected.
	 *
	 * @since 4.3.0
	 *
	 * @return bool Whether we are connected or not.
	 */
	public function isConnected() {
		return ! empty( $this->profile['key'] );
	}

	/**
	 * Verifies whether the authentication details are valid.
	 *
	 * @since 4.3.0
	 *
	 * @return bool Whether the data is valid or not.
	 */
	public function verify( $credentials = [] ) {
		$creds = ! empty( $credentials ) ? $credentials : aioseo()->internalOptions->internal->searchStatistics->profile;

		if ( empty( $creds['key'] ) ) {
			return new \WP_Error( 'validation-error', 'Authentication key is missing.' );
		}

		$request = new Request( "auth/verify/{$this->type}/", [
			'tt'      => aioseo()->searchStatistics->api->trustToken->get(),
			'key'     => $creds['key'],
			'token'   => $creds['token'],
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
	 * @param  bool $force Whether we should force the deletion in case of errors.
	 * @return bool        Whether the authentication data was deleted or not.
	 */
	public function delete( $force = false ) {
		if ( ! $this->isConnected() ) {
			return false;
		}

		$creds = aioseo()->searchStatistics->api->auth->getProfile( true );
		if ( empty( $creds['key'] ) ) {
			return false;
		}

		$request = new Request( "auth/delete/{$this->type}/", [
			'tt'      => aioseo()->searchStatistics->api->trustToken->get(),
			'key'     => $creds['key'],
			'token'   => $creds['token'],
			'testurl' => 'https://' . aioseo()->searchStatistics->api->getApiUrl() . '/v1/test/',
		] );
		$response = $request->request();

		aioseo()->searchStatistics->api->trustToken->rotate();

		if ( is_wp_error( $response ) && ! $force ) {
			return false;
		}

		aioseo()->searchStatistics->api->auth->deleteProfile();
		aioseo()->searchStatistics->reset();

		// Resets the results for the Google meta tag.
		aioseo()->options->webmasterTools->google = '';

		return true;
	}
}