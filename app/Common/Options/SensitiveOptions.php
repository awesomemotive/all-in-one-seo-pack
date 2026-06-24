<?php
namespace AIOSEO\Plugin\Common\Options;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class that holds all sensitive options for AIOSEO.
 *
 * This class stores sensitive values (tokens, keys, etc.) separately from the
 * regular options to prevent accidental exposure. Unlike other option classes:
 * - Values can only be read via the explicit get() method.
 * - The all() method is voided to prevent bulk exposure.
 * - No magic __get/__set methods are provided.
 *
 * @since 4.9.6
 */
class SensitiveOptions {
	/**
	 * The option name used for DB storage.
	 *
	 * @since 4.9.6
	 *
	 * @var string
	 */
	protected $optionsName = 'aioseo_sensitive_options';

	/**
	 * Whether we need to save on shutdown.
	 *
	 * @since 4.9.6
	 *
	 * @var bool
	 */
	protected $shouldSave = false;

	/**
	 * The current values stored in memory.
	 *
	 * @since 4.9.6
	 *
	 * @var array
	 */
	protected $values = [];

	/**
	 * The list of allowed keys.
	 *
	 * @since 4.9.6
	 *
	 * @var array
	 */
	protected $allowedKeys = [
		'connectLicenseKey',
		'aiAccessToken',
		'semrushAccessToken',
		'semrushRefreshToken',
		'searchStatisticsProfileKey',
		'searchStatisticsProfileToken',
		'searchStatisticsTrustToken',
		'siteAnalysisConnectToken',
		'seoAlertsSlackWebhookUrl'
	];

	/**
	 * Class constructor.
	 *
	 * @since 4.9.6
	 */
	public function __construct() {
		$this->init();

		add_action( 'shutdown', [ $this, 'save' ] );
	}

	/**
	 * Initializes the options from the database.
	 *
	 * @since 4.9.6
	 *
	 * @return void
	 */
	protected function init() {
		$dbValues = $this->getFromDb();

		// Only load values for allowed keys.
		foreach ( $this->allowedKeys as $key ) {
			$this->values[ $key ] = isset( $dbValues[ $key ] ) ? $dbValues[ $key ] : '';
		}
	}

	/**
	 * Reads the raw option values from the database.
	 *
	 * Subclasses can override this to change the storage mechanism
	 * (e.g. use get_site_option for network-wide storage).
	 *
	 * @since 4.9.6
	 *
	 * @return array The decoded option values.
	 */
	protected function getFromDb() {
		$dbValues = json_decode( (string) get_option( $this->optionsName, '' ), true );

		return is_array( $dbValues ) ? $dbValues : [];
	}

	/**
	 * Gets a sensitive option value.
	 *
	 * This is the only way to read a sensitive value. Using an explicit method
	 * instead of magic __get prevents unintentional reads.
	 *
	 * @since 4.9.6
	 *
	 * @param  string $key The option key.
	 * @return string      The option value, or empty string if not found.
	 */
	public function get( $key ) {
		if ( ! $this->isAllowedKey( $key ) ) {
			return '';
		}

		return isset( $this->values[ $key ] ) ? $this->values[ $key ] : '';
	}

	/**
	 * Checks whether a sensitive option has a non-empty value.
	 *
	 * Use this instead of get() when you only need to know whether a value
	 * exists, to avoid unnecessarily reading the sensitive value into scope.
	 *
	 * @since 4.9.6
	 *
	 * @param  string $key The option key.
	 * @return bool        Whether the option has a value.
	 */
	public function hasValue( $key ) {
		if ( ! $this->isAllowedKey( $key ) ) {
			return false;
		}

		return ! empty( $this->values[ $key ] );
	}

	/**
	 * Sets a sensitive option value.
	 *
	 * @since 4.9.6
	 *
	 * @param  string $key   The option key.
	 * @param  string $value The value to store.
	 * @return void
	 */
	public function set( $key, $value ) {
		if ( ! $this->isAllowedKey( $key ) ) {
			return;
		}

		$this->values[ $key ] = sanitize_text_field( (string) $value );
		$this->shouldSave     = true;
	}

	/**
	 * Deletes a sensitive option value.
	 *
	 * @since 4.9.6
	 *
	 * @param  string $key The option key.
	 * @return void
	 */
	public function delete( $key ) {
		$this->set( $key, '' );
	}

	/**
	 * Overridden to return an empty array.
	 *
	 * This prevents bulk exposure of all sensitive values.
	 *
	 * @since 4.9.6
	 *
	 * @return array Always returns an empty array.
	 */
	public function all() {
		return [];
	}

	/**
	 * Returns an array of boolean indicators for each sensitive option.
	 *
	 * This is used to provide the frontend with information about which
	 * sensitive options have values, without exposing the values themselves.
	 *
	 * @since 4.9.6
	 *
	 * @return array An associative array of boolean indicators.
	 */
	public function allHas() {
		$result = [];
		foreach ( $this->allowedKeys as $key ) {
			$result[ 'has' . ucfirst( $key ) ] = $this->hasValue( $key );
		}

		return $result;
	}

	/**
	 * Saves the options to the database.
	 *
	 * @since 4.9.6
	 *
	 * @param  bool $force Whether to force saving.
	 * @return void
	 */
	public function save( $force = false ) {
		if ( ! $this->shouldSave && ! $force ) {
			return;
		}

		// Only save allowed keys.
		$toSave = [];
		foreach ( $this->allowedKeys as $key ) {
			if ( ! empty( $this->values[ $key ] ) ) {
				$toSave[ $key ] = $this->values[ $key ];
			}
		}

		$this->saveToDb( $toSave );

		$this->shouldSave = false;
	}

	/**
	 * Writes the option values to the database.
	 *
	 * Subclasses can override this to change the storage mechanism
	 * (e.g. use update_site_option for network-wide storage).
	 *
	 * @since 4.9.6
	 *
	 * @param  array $values The values to save.
	 * @return void
	 */
	protected function saveToDb( $values ) {
		update_option( $this->optionsName, wp_json_encode( $values ), false );
	}

	/**
	 * Checks whether a key is in the allowed keys list.
	 *
	 * @since 4.9.6
	 *
	 * @param  string $key The key to check.
	 * @return bool        Whether the key is allowed.
	 */
	protected function isAllowedKey( $key ) {
		return in_array( $key, $this->allowedKeys, true );
	}

	/**
	 * Returns the list of allowed keys.
	 *
	 * @since 4.9.6
	 *
	 * @return array The allowed keys.
	 */
	public function getAllowedKeys() {
		return $this->allowedKeys;
	}

	/**
	 * Registers an additional sensitive key.
	 *
	 * This allows addons to register their own sensitive keys.
	 *
	 * @since 4.9.6
	 *
	 * @param  string $key The key to register.
	 * @return void
	 */
	public function registerKey( $key ) {
		if ( in_array( $key, $this->allowedKeys, true ) ) {
			return;
		}

		$this->allowedKeys[] = $key;

		// Load the value from DB if it exists.
		$dbValues = $this->getFromDb();
		if ( isset( $dbValues[ $key ] ) ) {
			$this->values[ $key ] = $dbValues[ $key ];
		} else {
			$this->values[ $key ] = '';
		}
	}
}