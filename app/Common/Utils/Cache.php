<?php
namespace AIOSEO\Plugin\Common\Utils;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles our cache.
 *
 * @since 4.1.5
 */
class Cache {
	/**
	 * Our cache table.
	 *
	 * @since 4.1.5
	 *
	 * @var string
	 */
	private $table = 'aioseo_cache';

	/**
	 * Our cached cache.
	 *
	 * @since 4.1.5
	 *
	 * @var array
	 */
	private static $cache = [];

	/**
	 * The action for the scheduled cache prune.
	 *
	 * @since 4.9.4.2
	 *
	 * @var string
	 */
	private $pruneAction = 'aioseo_cache_prune';

	/**
	 * The action for the scheduled old cache clean.
	 *
	 * @since 4.9.4.2
	 *
	 * @var string
	 */
	private $optionCacheCleanAction = 'aioseo_old_cache_clean';

	/**
	 * Prefix for this cache.
	 *
	 * @since 4.1.5
	 *
	 * @var string
	 */
	protected $prefix = '';

	/**
	 * Whether to use transients as fallback.
	 *
	 * @since 4.9.4.2
	 *
	 * @var bool|null
	 */
	private $useTransientFallback = null;

	/**
	 * Class constructor.
	 *
	 * @since 4.7.7.1
	 */
	public function __construct() {
		add_action( 'init', [ $this, 'checkIfTableExists' ] ); // This needs to run on init because the DB.
		// class gets instantiated along with the cache class.
	}

	/**
	 * Checks if the cache table exists and creates it if it doesn't.
	 * Also registers and schedules cache maintenance actions.
	 *
	 * @since   4.7.7.1
	 * @version 4.9.4.2 Absorb prune logic from CachePrune class.
	 *
	 * @return void
	 */
	public function checkIfTableExists() {
		if ( ! aioseo()->core->db->tableExists( $this->table ) ) {
			aioseo()->preUpdates->createCacheTable();
		}

		add_action( $this->pruneAction, [ $this, 'prune' ] );
		add_action( $this->optionCacheCleanAction, [ $this, 'optionCacheClean' ] );

		if ( aioseo()->actionScheduler->isScheduled( $this->pruneAction ) ) {
			return;
		}

		aioseo()->actionScheduler->scheduleRecurrent( $this->pruneAction, 0, DAY_IN_SECONDS );
	}

	/**
	 * Returns the cache value for a key if it exists and is not expired.
	 *
	 * @since 4.1.5
	 *
	 * @param  string     $key            The cache key name. Use a '%' for a like query.
	 * @param  bool|array $allowedClasses Deprecated. No longer used since migrating from serialize to JSON.
	 * @return mixed                      The value or null if the cache does not exist.
	 */
	public function get( $key, $allowedClasses = false ) { // phpcs:ignore VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
		// In dev mode, bypass Action Scheduler cache records so idle/lock timeouts don't block execution.
		if ( 0 === strpos( $key, 'as_' ) && aioseo()->helpers->isDev() ) {
			return null;
		}

		$key = $this->prepareKey( $key );

		// Check if we're supposed to do a LIKE get.
		$isLikeGet = preg_match( '/%/', (string) $key );

		// Check static cache first (only for non-LIKE queries).
		if ( ! $isLikeGet && isset( self::$cache[ $key ] ) ) {
			return self::$cache[ $key ];
		}

		// Check if we should use transients.
		if ( ! $this->isCacheTableAvailable() ) {
			if ( $isLikeGet ) {
				// Use custom query for LIKE patterns.
				return $this->getTransientLike( $key );
			}

			$value = $this->getTransient( $key );
			if ( null !== $value ) {
				self::$cache[ $key ] = $value;
			}

			return $value;
		}

		$result = aioseo()->core->db
			->start( $this->table )
			->select( '`key`, `value`, `is_object`' )
			->whereRaw( '( `expiration` IS NULL OR `expiration` > \'' . aioseo()->helpers->timeToMysql( time() ) . '\' )' );

		if ( $isLikeGet ) {
			$result->whereLike( 'key', $key, true );
		} else {
			$key = esc_sql( $key );
			$result->where( 'key', $key );
		}

		$result->output( ARRAY_A )->run();

		// If we have nothing in the cache let's return a hard null.
		$values = $result->nullSet() ? null : $result->result();

		// If we have something let's normalize it.
		if ( $values ) {
			foreach ( $values as &$value ) {
				// Use is_object flag to determine decode type: if 0 (false) decode to array, if 1 (true) decode to object.
				$value['value'] = json_decode( $value['value'], empty( $value['is_object'] ) );
			}
			// Return only the single cache value.
			if ( ! $isLikeGet ) {
				$values = $values[0]['value'];
			}
		}

		// Return values without a static cache.
		// This is here because clearing the like cache is not simple.
		if ( $isLikeGet ) {
			return $values;
		}

		self::$cache[ $key ] = $values;

		return self::$cache[ $key ];
	}

	/**
	 * Updates the given cache or creates it if it doesn't exist.
	 *
	 * @since 4.1.5
	 *
	 * @param  string $key        The cache key name.
	 * @param  mixed  $value      The value.
	 * @param  int    $expiration The expiration time in seconds. Defaults to 24 hours. 0 to no expiration.
	 * @return void
	 */
	public function update( $key, $value, $expiration = DAY_IN_SECONDS ) {
		$key = $this->prepareKey( $key );

		// If the value is null we'll convert it and give it a shorter expiration.
		if ( null === $value ) {
			$value      = false;
			$expiration = 10 * MINUTE_IN_SECONDS;
		}

		// Check if we should use transients.
		if ( ! $this->isCacheTableAvailable() ) {
			$this->updateTransient( $key, $value, $expiration );
			$this->updateStatic( $key, $value );

			return;
		}

		$isObject   = is_object( $value );
		$jsonValue  = wp_json_encode( $value );
		$expiration = 0 < $expiration ? aioseo()->helpers->timeToMysql( time() + $expiration ) : null;

		// Handle JSON encoding errors.
		if ( false === $jsonValue && JSON_ERROR_NONE !== json_last_error() ) {
			if ( aioseo()->helpers->isDev() ) {
				// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
				error_log( 'AIOSEO Cache: JSON encode failed for key "' . $key . '" - ' . json_last_error_msg() );
			}

			return;
		}

		aioseo()->core->db->insert( $this->table )
			->set( [
				'key'        => $this->prepareKey( $key ),
				'value'      => $jsonValue,
				'is_object'  => $isObject,
				'expiration' => $expiration,
				'created'    => aioseo()->helpers->timeToMysql( time() ),
				'updated'    => aioseo()->helpers->timeToMysql( time() )
			] )
			->onDuplicate( [
				'value'      => $jsonValue,
				'is_object'  => $isObject,
				'expiration' => $expiration,
				'updated'    => aioseo()->helpers->timeToMysql( time() )
			] )
			->run();

		$this->updateStatic( $key, $value );
	}

	/**
	 * Deletes the given cache key.
	 *
	 * @since 4.1.5
	 *
	 * @param  string $key The cache key.
	 * @return void
	 */
	public function delete( $key ) {
		$key = $this->prepareKey( $key );

		// Check if we should use transients.
		if ( ! $this->isCacheTableAvailable() ) {
			$this->deleteTransient( $key );
			$this->clearStatic( $key );

			return;
		}

		aioseo()->core->db->delete( $this->table )
			->where( 'key', $key )
			->run();

		$this->clearStatic( $key );
	}

	/**
	 * Prepares the key before using the cache.
	 *
	 * @since 4.1.5
	 *
	 * @param  string $key The key to prepare.
	 * @return string      The prepared key.
	 */
	private function prepareKey( $key ) {
		$key = trim( (string) $key );
		$key = $this->prefix && 0 !== strpos( $key, $this->prefix ) ? $this->prefix . $key : $key;

		if ( aioseo()->helpers->isDev() && 80 < mb_strlen( $key, 'UTF-8' ) ) {
			throw new \Exception( 'You are using a cache key that is too large, shorten your key and try again: [' . esc_html( $key ) . ']' );
		}

		return $key;
	}

	/**
	 * Clears all of our cache.
	 *
	 * @since 4.1.5
	 *
	 * @return void
	 */
	public function clear() {
		if ( $this->prefix ) {
			$this->clearPrefix( '' );

			return;
		}

		// Check if we should use transients.
		if ( ! $this->isCacheTableAvailable() ) {
			// Delete all AIOSEO cache transients.
			$this->deleteAllTransients();
			$this->clearStatic();

			return;
		}

		// Try to acquire the lock.
		if ( ! aioseo()->core->db->acquireLock( 'aioseo_cache_clear_lock', 0 ) ) {
			// If we couldn't acquire the lock, exit early without doing anything.
			// This means another process is already clearing the cache.
			return;
		}

		// If we find the activation redirect, we'll need to reset it after clearing.
		$activationRedirect = $this->get( 'activation_redirect' );

		// Create a temporary table with the same structure.
		$table    = aioseo()->core->db->prefix . $this->table;
		$newTable = aioseo()->core->db->prefix . $this->table . '_new';
		$oldTable = aioseo()->core->db->prefix . $this->table . '_old';

		try {
			// Drop the temp table if it exists from a previous failed attempt.
			if ( false === aioseo()->core->db->execute( "DROP TABLE IF EXISTS {$newTable}" ) ) {
				throw new \Exception( 'Failed to drop temporary table' );
			}

			// Create the new empty table with the same structure.
			if ( false === aioseo()->core->db->execute( "CREATE TABLE {$newTable} LIKE {$table}" ) ) {
				throw new \Exception( 'Failed to create temporary table' );
			}

			// Rename tables (atomic operation in MySQL).
			if ( false === aioseo()->core->db->execute( "RENAME TABLE {$table} TO {$oldTable}, {$newTable} TO {$table}" ) ) {
				throw new \Exception( 'Failed to rename tables' );
			}

			// Drop the old table.
			if ( false === aioseo()->core->db->execute( "DROP TABLE {$oldTable}" ) ) {
				throw new \Exception( 'Failed to drop old table' );
			}
		} catch ( \Exception $e ) {
			// If something fails, ensure we clean up any temporary tables.
			aioseo()->core->db->execute( "DROP TABLE IF EXISTS {$newTable}" );
			aioseo()->core->db->execute( "DROP TABLE IF EXISTS {$oldTable}" );

			// Truncate table to clear the cache.
			aioseo()->core->db->truncate( $this->table )->run();
		}

		$this->clearStatic();

		if ( $activationRedirect ) {
			$this->update( 'activation_redirect', $activationRedirect, 30 );
		}
	}

	/**
	 * Clears all of our cache under a certain prefix.
	 *
	 * @since 4.1.5
	 *
	 * @param  string $prefix A prefix to clear or empty to clear everything.
	 * @return void
	 */
	public function clearPrefix( $prefix ) {
		$prefix = $this->prepareKey( $prefix );

		// Check if we should use transients.
		if ( ! $this->isCacheTableAvailable() ) {
			// Delete transients by prefix.
			$this->deleteTransientsByPrefix( $prefix );
			$this->clearStaticPrefix( $prefix );

			return;
		}

		aioseo()->core->db->delete( $this->table )
			->whereLike( 'key', $prefix . '%', true )
			->run();

		$this->clearStaticPrefix( $prefix );
	}

	/**
	 * Clears all of our static in-memory cache of a prefix.
	 *
	 * @since 4.1.5
	 *
	 * @param  string $prefix A prefix to clear.
	 * @return void
	 */
	private function clearStaticPrefix( $prefix ) {
		$prefix = $this->prepareKey( $prefix );
		foreach ( array_keys( self::$cache ) as $key ) {
			if ( 0 === strpos( $key, $prefix ) ) {
				unset( self::$cache[ $key ] );
			}
		}
	}

	/**
	 * Clears all of our static in-memory cache.
	 *
	 * @since 4.1.5
	 *
	 * @param  string $key A key to clear.
	 * @return void
	 */
	private function clearStatic( $key = null ) {
		if ( empty( $key ) ) {
			self::$cache = [];

			return;
		}

		unset( self::$cache[ $this->prepareKey( $key ) ] );
	}

	/**
	 * Clears all of our static in-memory cache or the cache for a single given key.
	 *
	 * @since 4.7.1
	 *
	 * @param  string $key   A key to clear (optional).
	 * @param  string $value A value to update (optional).
	 * @return void
	 */
	private function updateStatic( $key = null, $value = null ) {
		if ( empty( $key ) ) {
			$this->clearStatic( $key );

			return;
		}

		self::$cache[ $this->prepareKey( $key ) ] = $value;
	}

	/**
	 * Prunes expired cache rows from the cache table.
	 *
	 * @since 4.9.4.2
	 *
	 * @return void
	 */
	public function prune() {
		aioseo()->core->db->delete( $this->getTableName() )
			->whereRaw( '( `expiration` IS NOT NULL AND expiration <= \'' . aioseo()->helpers->timeToMysql( time() ) . '\' )' )
			->run();
	}

	/**
	 * Cleans legacy cache entries from the wp_options table.
	 * Processes in batches and self-schedules until all entries are removed.
	 *
	 * @since 4.9.4.2
	 *
	 * @return void
	 */
	public function optionCacheClean() {
		$optionCache = aioseo()->core->db->delete( aioseo()->core->db->db->options, true )
			->whereLike( 'option_name', '_aioseo_cache_%', true )
			->limit( 10000 )
			->run();

		// Schedule a new run if we're not done cleaning.
		if ( 0 !== $optionCache->db->rows_affected ) {
			aioseo()->actionScheduler->scheduleSingle( $this->optionCacheCleanAction, MINUTE_IN_SECONDS, [], true );
		}
	}

	/**
	 * Returns the action name for the old cache clean.
	 *
	 * @since 4.9.4.2
	 *
	 * @return string
	 */
	public function getOptionCacheCleanAction() {
		return $this->optionCacheCleanAction;
	}

	/**
	 * Returns the cache table name.
	 *
	 * @since 4.1.5
	 *
	 * @return string
	 */
	public function getTableName() {
		return $this->table;
	}

	/**
	 * Checks if the cache table is available for use.
	 *
	 * @since 4.9.4.2
	 *
	 * @return bool True if table exists and is accessible.
	 */
	private function isCacheTableAvailable() {
		if ( null !== $this->useTransientFallback ) {
			return ! $this->useTransientFallback;
		}

		// Check transient first to avoid a DB query on every request.
		if ( get_transient( 'aioseo_cache_table_exists' ) ) {
			$this->useTransientFallback = false;

			return true;
		}

		// Transient not set — check the DB directly (avoids circular dependency with Database::tableExists).
		global $wpdb;
		$tableName = $wpdb->prefix . $this->table;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$tableExists = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $tableName ) );

		if ( ! $tableExists ) {
			$this->useTransientFallback = true;

			return false;
		}

		set_transient( 'aioseo_cache_table_exists', 1, WEEK_IN_SECONDS );
		$this->useTransientFallback = false;

		return true;
	}

	/**
	 * Gets the transient name for a cache key.
	 *
	 * @since 4.9.4.2
	 *
	 * @param  string $key The cache key.
	 * @return string      The transient name.
	 */
	private function getTransientName( $key ) {
		// Store the original key to maintain compatibility with LIKE queries.
		return 'aioseo_cache_' . $key;
	}

	/**
	 * Gets a value from transients.
	 *
	 * @since 4.9.4.2
	 *
	 * @param  string $key The cache key.
	 * @return mixed       The cached value or null.
	 */
	private function getTransient( $key ) {
		$transientName = $this->getTransientName( $key );
		$data          = get_transient( $transientName );

		if ( false === $data ) {
			return null;
		}

		// Decode the wrapper structure.
		$wrapper = json_decode( $data, true );
		if ( ! is_array( $wrapper ) || ! isset( $wrapper['value'] ) ) {
			return null;
		}

		// Decode JSON value using is_object flag (matching database cache behavior).
		// If is_object is 1 (true), decode to object; if 0 (false), decode to array.
		$decoded = json_decode( $wrapper['value'], empty( $wrapper['is_object'] ) );

		return $decoded;
	}

	/**
	 * Gets multiple transients using a LIKE query.
	 *
	 * @since 4.9.4.2
	 *
	 * @param  string $pattern The pattern to match (with % wildcards).
	 * @return mixed           Array of matching cache entries or null.
	 */
	private function getTransientLike( $pattern ) {
		global $wpdb;

		$transientPattern = $this->getTransientName( $pattern );

		// Query for non-expired transients matching the pattern.
		$results = $wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$wpdb->prepare(
				"SELECT REPLACE(option_name, '_transient_', '') as `key`, option_value as `value`
				FROM {$wpdb->options}
				WHERE option_name LIKE %s
				AND option_name NOT LIKE %s
				AND (
					NOT EXISTS (
						SELECT 1 FROM {$wpdb->options} timeout
						WHERE timeout.option_name = CONCAT('_transient_timeout_', REPLACE(option_name, '_transient_', ''))
						AND CAST(timeout.option_value AS UNSIGNED) > 0
						AND CAST(timeout.option_value AS UNSIGNED) < UNIX_TIMESTAMP()
					)
				)",
				'_transient_' . implode( '%', array_map( [ $wpdb, 'esc_like' ], explode( '%', $transientPattern ) ) ),
				'_transient_timeout_%'
			),
			ARRAY_A
		);

		if ( empty( $results ) ) {
			return null;
		}

		// Decode JSON values with is_object flag.
		foreach ( $results as &$result ) {
			$result['key'] = str_replace( 'aioseo_cache_', '', $result['key'] );

			// Decode the wrapper structure.
			$wrapper = json_decode( $result['value'], true );
			if ( is_array( $wrapper ) && isset( $wrapper['value'] ) ) {
				// Decode JSON value using is_object flag (matching database cache behavior).
				// If is_object is 1 (true), decode to object; if 0 (false), decode to array.
				$result['value'] = json_decode( $wrapper['value'], empty( $wrapper['is_object'] ) );
			} else {
				// Fallback for malformed data.
				$result['value'] = null;
			}
		}

		return $results;
	}

	/**
	 * Updates a transient value.
	 *
	 * @since 4.9.4.2
	 *
	 * @param  string $key        The cache key.
	 * @param  mixed  $value      The value to cache.
	 * @param  int    $expiration Expiration in seconds.
	 * @return void
	 */
	private function updateTransient( $key, $value, $expiration ) {
		$transientName = $this->getTransientName( $key );

		// Detect if value is an object to match database cache behavior.
		$isObject = is_object( $value );

		// Encode as JSON to match database cache behavior.
		$jsonValue = wp_json_encode( $value );

		if ( false === $jsonValue && JSON_ERROR_NONE !== json_last_error() ) {
			if ( aioseo()->helpers->isDev() ) {
				// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
				error_log( 'AIOSEO Cache: JSON encode failed for key "' . $key . '" - ' . json_last_error_msg() );
			}

			return;
		}

		// Wrap the value with metadata including is_object flag.
		$wrapper = [
			'value'     => $jsonValue,
			'is_object' => $isObject
		];

		$wrappedValue = wp_json_encode( $wrapper );

		if ( false === $wrappedValue ) {
			if ( aioseo()->helpers->isDev() ) {
				// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
				error_log( 'AIOSEO Cache: JSON encode failed for wrapper on key "' . $key . '"' );
			}

			return;
		}

		set_transient( $transientName, $wrappedValue, $expiration );
	}

	/**
	 * Deletes a transient.
	 *
	 * @since 4.9.4.2
	 *
	 * @param  string $key The cache key.
	 * @return void
	 */
	private function deleteTransient( $key ) {
		$transientName = $this->getTransientName( $key );
		delete_transient( $transientName );
	}

	/**
	 * Deletes all transients matching a prefix.
	 *
	 * @since 4.9.4.2
	 *
	 * @param  string $prefix The prefix to match.
	 * @return void
	 */
	private function deleteTransientsByPrefix( $prefix ) {
		global $wpdb;

		$escapedPrefix = $wpdb->esc_like( $this->getTransientName( $prefix ) );

		// Delete both the transient and its timeout.
		$wpdb->query( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$wpdb->prepare(
				"DELETE FROM {$wpdb->options}
				WHERE option_name LIKE %s
				OR option_name LIKE %s",
				'_transient_' . $escapedPrefix . '%',
				'_transient_timeout_' . $escapedPrefix . '%'
			)
		);
	}

	/**
	 * Deletes all AIOSEO cache transients.
	 *
	 * @since 4.9.4.2
	 *
	 * @return void
	 */
	private function deleteAllTransients() {
		global $wpdb;

		$prefix = 'aioseo_cache_%';

		// Delete both transients and their timeouts.
		$wpdb->query( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$wpdb->prepare(
				"DELETE FROM {$wpdb->options}
				WHERE option_name LIKE %s
				OR option_name LIKE %s",
				'_transient_' . $wpdb->esc_like( $prefix ),
				'_transient_timeout_' . $wpdb->esc_like( $prefix )
			)
		);
	}
}