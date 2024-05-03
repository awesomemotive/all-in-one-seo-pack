<?php
namespace AIOSEO\Plugin\Common\SearchStatistics\Api;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * API class.
 *
 * @since   4.3.0
 * @version 4.6.2 Moved from Pro to Common.
 */
class Api {
	/**
	 * Holds the instance of the Auth class.
	 *
	 * @since 4.3.0
	 *
	 * @var Auth
	 */
	public $auth;

	/**
	 * Holds the instance of the TrustToken class.
	 *
	 * @since 4.3.0
	 *
	 * @var TrustToken
	 */
	public $trustToken;

	/**
	 * Holds the instance of the Listener class.
	 *
	 * @since 4.3.0
	 *
	 * @var Listener
	 */
	public $listener;

	/**
	 * The base URL for the Search Statistics microservice.
	 *
	 * @since 4.3.0
	 *
	 * @var string
	 */
	private $url = 'google.aioseo.com';

	/**
	 * The API version for the Search Statistics microservice.
	 *
	 * @since 4.3.0
	 *
	 * @var string
	 */
	private $version = 'v1';

	/**
	 * Class constructor.
	 *
	 * @since 4.3.0
	 */
	public function __construct() {
		$this->auth       = new Auth();
		$this->trustToken = new TrustToken();
		$this->listener   = new Listener();
	}

	/**
	 * Returns the site identifier key according to the WordPress keys.
	 *
	 * @since 4.3.0
	 *
	 * @return string The site identifier key.
	 */
	public function getSiteIdentifier() {
		$authKey       = defined( 'AUTH_KEY' ) ? AUTH_KEY : '';
		$secureAuthKey = defined( 'SECURE_AUTH_KEY' ) ? SECURE_AUTH_KEY : '';
		$loggedInKey   = defined( 'LOGGED_IN_KEY' ) ? LOGGED_IN_KEY : '';

		$siteIdentifier = $authKey . $secureAuthKey . $loggedInKey;
		$siteIdentifier = preg_replace( '/[^a-zA-Z0-9]/', '', $siteIdentifier );
		$siteIdentifier = sanitize_text_field( $siteIdentifier );
		$siteIdentifier = trim( $siteIdentifier );
		$siteIdentifier = ( strlen( $siteIdentifier ) > 30 ) ? substr( $siteIdentifier, 0, 30 ) : $siteIdentifier;

		return $siteIdentifier;
	}

	/**
	 * Returns the URL of the remote endpoint.
	 *
	 * @since 4.3.0
	 *
	 * @return string The URL.
	 */
	public function getApiUrl() {
		if ( defined( 'AIOSEO_SEARCH_STATISTICS_API_URL' ) ) {
			return AIOSEO_SEARCH_STATISTICS_API_URL;
		}

		return $this->url;
	}

	/**
	 * Returns the version of the remote endpoint.
	 *
	 * @since 4.3.0
	 *
	 * @return string The version.
	 */
	public function getApiVersion() {
		if ( defined( 'AIOSEO_SEARCH_STATISTICS_API_VERSION' ) ) {
			return AIOSEO_SEARCH_STATISTICS_API_VERSION;
		}

		return $this->version;
	}
}