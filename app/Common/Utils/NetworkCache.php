<?php
namespace AIOSEO\Plugin\Common\Utils;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles our network cache.
 *
 * @since 4.2.5
 */
class NetworkCache extends Cache {
	/**
	 * Returns the cache value for a key if it exists and is not expired.
	 *
	 * @since 4.2.5
	 *
	 * @param  string     $key            The cache key name. Use a '%' for a like query.
	 * @param  bool|array $allowedClasses Whether to allow objects to be returned.
	 * @return mixed                      The value or null if the cache does not exist.
	 */
	public function get( $key, $allowedClasses = false ) {
		if ( ! is_multisite() ) {
			return parent::get( $key, $allowedClasses );
		}

		aioseo()->helpers->switchToBlog( aioseo()->helpers->getNetworkId() );
		$value = parent::get( $key, $allowedClasses );
		aioseo()->helpers->restoreCurrentBlog();

		return $value;
	}

	/**
	 * Updates the given cache or creates it if it doesn't exist.
	 *
	 * @since 4.2.5
	 *
	 * @param  string $key        The cache key name.
	 * @param  mixed  $value      The value.
	 * @param  int    $expiration The expiration time in seconds. Defaults to 24 hours. 0 to no expiration.
	 * @return void
	 */
	public function update( $key, $value, $expiration = DAY_IN_SECONDS ) {
		if ( ! is_multisite() ) {
			parent::update( $key, $value, $expiration );

			return;
		}

		aioseo()->helpers->switchToBlog( aioseo()->helpers->getNetworkId() );
		parent::update( $key, $value, $expiration );
		aioseo()->helpers->restoreCurrentBlog();
	}

	/**
	 * Deletes the given cache key.
	 *
	 * @since 4.2.5
	 *
	 * @param  string $key The cache key.
	 * @return void
	 */
	public function delete( $key ) {
		if ( ! is_multisite() ) {
			parent::delete( $key );

			return;
		}

		aioseo()->helpers->switchToBlog( aioseo()->helpers->getNetworkId() );
		parent::delete( $key );
		aioseo()->helpers->restoreCurrentBlog();
	}

	/**
	 * Clears all of our cache.
	 *
	 * @since 4.2.5
	 *
	 * @return void
	 */
	public function clear() {
		if ( ! is_multisite() ) {
			parent::clear();

			return;
		}

		aioseo()->helpers->switchToBlog( aioseo()->helpers->getNetworkId() );
		parent::clear();
		aioseo()->helpers->restoreCurrentBlog();
	}

	/**
	 * Clears all of our cache under a certain prefix.
	 *
	 * @since 4.2.5
	 *
	 * @param  string $prefix A prefix to clear or empty to clear everything.
	 * @return void
	 */
	public function clearPrefix( $prefix ) {
		if ( ! is_multisite() ) {
			parent::clearPrefix( $prefix );

			return;
		}

		aioseo()->helpers->switchToBlog( aioseo()->helpers->getNetworkId() );
		parent::clearPrefix( $prefix );
		aioseo()->helpers->restoreCurrentBlog();
	}
}