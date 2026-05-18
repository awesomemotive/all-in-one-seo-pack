<?php
namespace AIOSEO\Plugin\Common\Main\Migrations;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Wraps the `aioseo_migrations_log` option that tracks per-migration execution
 * state for the {@see MigrationRunner}.
 *
 * The option is stored without autoload — healthy sites short-circuit on
 * `lastSchemaVersion === aioseo()->version` before the runner ever reads the
 * log, so paying autoload overhead on every request would be wasted work.
 *
 * @since 4.9.7.2
 */
class MigrationLog {
	/**
	 * Option name. Per-plugin namespacing means addons can each own their own
	 * log without colliding with the main plugin's entries.
	 *
	 * @since 4.9.7.2
	 *
	 * @var string
	 */
	private $optionName = 'aioseo_migrations_log';

	/**
	 * Per-request cache. The runner can call read() multiple times across
	 * migrations within one request — no need to round-trip wp_options each
	 * time.
	 *
	 * Null means "not loaded yet"; array (possibly empty) means "loaded".
	 *
	 * @since 4.9.7.2
	 *
	 * @var array|null
	 */
	private $cache = null;

	/**
	 * Read the full log.
	 *
	 * @since 4.9.7.2
	 *
	 * @return array Keyed by migration name. Empty array when the option does not exist.
	 */
	public function read() {
		if ( null !== $this->cache ) {
			return $this->cache;
		}

		$raw = get_option( $this->optionName, '' );
		if ( empty( $raw ) ) {
			$this->cache = [];

			return $this->cache;
		}

		$decoded = json_decode( $raw, true );
		if ( ! is_array( $decoded ) ) {
			$this->cache = [];

			return $this->cache;
		}

		$this->cache = $decoded;

		return $this->cache;
	}

	/**
	 * Write the full log.
	 *
	 * On first write the option is created with autoload disabled. Subsequent
	 * writes preserve that flag automatically — WordPress's update_option()
	 * does not change autoload on existing options.
	 *
	 * @since 4.9.7.2
	 *
	 * @param  array $log Log keyed by migration name.
	 * @return void
	 */
	public function write( array $log ) {
		$encoded = wp_json_encode( $log );
		if ( false === $encoded ) {
			return;
		}

		$existing = get_option( $this->optionName, null );
		if ( null === $existing ) {
			add_option( $this->optionName, $encoded, '', 'no' );
		} else {
			update_option( $this->optionName, $encoded );
		}

		$this->cache = $log;
	}
}