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
	 * The Cache Prune class.
	 *
	 * @since 4.1.5
	 *
	 * @var CachePrune
	 */
	public $prune;

	/**
	 * Prefix for this cache.
	 *
	 * @since 4.1.5
	 *
	 * @var string
	 */
	protected $prefix = '';

	/**
	 * Returns the cache value for a key if it exists and is not expired.
	 *
	 * @since 4.1.5
	 *
	 * @param  string     $key            The cache key name. Use a '%' for a like query.
	 * @param  bool|array $allowedClasses Whether to allow objects to be returned.
	 * @return mixed                      The value or null if the cache does not exist.
	 */
	public function get( $key, $allowedClasses = false ) {
		$key = $this->prepareKey( $key );
		if ( isset( self::$cache[ $key ] ) ) {
			return self::$cache[ $key ];
		}

		// Are we searching for a group of keys?
		$isLikeGet = preg_match( '/%/', $key );

		$result = aioseo()->core->db
			->start( $this->table )
			->select( '`key`, `value`' )
			->whereRaw( '( `expiration` IS NULL OR `expiration` > \'' . aioseo()->helpers->timeToMysql( time() ) . '\' )' );

		$isLikeGet ?
			$result->whereRaw( '`key` LIKE \'' . $key . '\'' ) :
			$result->where( 'key', $key );

		$result->output( ARRAY_A )->run();

		// If we have nothing in the cache let's return a hard null.
		$values = $result->nullSet() ? null : $result->result();

		// If we have something let's normalize it.
		if ( $values ) {
			foreach ( $values as &$value ) {
				$value['value'] = aioseo()->helpers->maybeUnserialize( $value['value'], $allowedClasses );
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
		// If the value is null we'll convert it and give it a shorter expiration.
		if ( null === $value ) {
			$value      = false;
			$expiration = 10 * MINUTE_IN_SECONDS;
		}

		$serializedValue = serialize( $value );
		$expiration      = 0 < $expiration ? aioseo()->helpers->timeToMysql( time() + $expiration ) : null;

		aioseo()->core->db->insert( $this->table )
			->set( [
				'key'        => $this->prepareKey( $key ),
				'value'      => $serializedValue,
				'expiration' => $expiration,
				'created'    => aioseo()->helpers->timeToMysql( time() ),
				'updated'    => aioseo()->helpers->timeToMysql( time() )
			] )
			->onDuplicate( [
				'value'      => $serializedValue,
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
		$key = trim( $key );
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

		// If we find the activation redirect, we'll need to reset it after clearing.
		$activationRedirect = $this->get( 'activation_redirect' );

		aioseo()->core->db->truncate( $this->table )->run();

		$this->clearStatic();

		if ( $activationRedirect ) {
			$this->update( 'activation_redirect', $activationRedirect, 30 );
		}

		// Bust the tableExists and columnExists cache.
		aioseo()->internalOptions->database->installedTables = '';
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

		aioseo()->core->db->delete( $this->table )
			->whereRaw( "`key` LIKE '$prefix%'" )
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
	 * Returns the cache table name.
	 *
	 * @since 4.1.5
	 *
	 * @return string
	 */
	public function getTableName() {
		return $this->table;
	}
}