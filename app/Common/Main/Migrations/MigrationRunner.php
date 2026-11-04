<?php
namespace AIOSEO\Plugin\Common\Main\Migrations;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Discovers, executes, and logs {@see Migration} instances.
 *
 * The runner closes the failure mode that left 4.9.7.1 sites half-applied:
 * the version flag is only advanced when EVERY registered migration's
 * verify() returns true. A concurrent-request lock loser bails without
 * touching state; a lock holder that dies mid-migration leaves the relevant
 * log entry at status = 0 (or absent), and the runner retries on the next
 * request until verify() actually passes.
 *
 * @since 4.9.7.2
 */
class MigrationRunner {
	/**
	 * Registered migrations, in registration order.
	 *
	 * @since 4.9.7.2
	 *
	 * @var Migration[]
	 */
	private $migrations = [];

	/**
	 * Log accessor.
	 *
	 * @since 4.9.7.2
	 *
	 * @var MigrationLog
	 */
	private $log;

	/**
	 * Lock name. GET_LOCK is held per connection, so this serializes concurrent PHP processes.
	 *
	 * NOTE: Deliberately the same name {@see \AIOSEO\Plugin\Common\Main\Updates::runUpdates()}
	 * takes, and it must stay that way. Both issue DDL against the same tables, and a lock of
	 * its own would not exclude the other: dbDelta introspects every table, queues its ALTERs,
	 * then executes them all afterwards, so a migration's ALTER landing in that gap turns the
	 * queue into "Duplicate column name" and "Multiple primary key defined" errors. The two
	 * never nest — the runner releases during preLoad, well before `init` — so one plain name
	 * is enough. Also note the name is server-wide rather than per-database, so subsites on one
	 * network do serialize with each other here.
	 *
	 * @since   4.9.7.2
	 * @version 5.0.1 Shared with the updaters instead of a private lock domain.
	 *
	 * @var string
	 */
	private $lockName = 'aioseo_run_updates_lock';

	/**
	 * Seconds to wait after the first failed attempt. Doubles with each attempt, up to {@see MAX_RETRY_DELAY}.
	 *
	 * @since 5.0.5
	 *
	 * @var int
	 */
	const RETRY_DELAY = MINUTE_IN_SECONDS;

	/**
	 * The longest wait between attempts at a failing migration.
	 *
	 * @since 5.0.5
	 *
	 * @var int
	 */
	const MAX_RETRY_DELAY = DAY_IN_SECONDS;

	/**
	 * @since 4.9.7.2
	 */
	public function __construct() {
		$this->log = new MigrationLog();
	}

	/**
	 * Register a migration. Order is preserved — later registrations run
	 * after earlier ones within the same request.
	 *
	 * @since 4.9.7.2
	 *
	 * @param  Migration $migration The migration to register.
	 * @return void
	 */
	public function register( Migration $migration ) {
		$this->migrations[] = $migration;
	}

	/**
	 * Execute pending migrations.
	 *
	 * Short-circuits early when the recorded schema version matches the
	 * current plugin version — healthy sites never touch the log option.
	 * Lock losers exit silently and do NOT advance any version flag, so a
	 * future-bumped lastSchemaVersion can't strand work the way 4.9.7.1's
	 * lastActiveVersion bump did.
	 *
	 * @since   4.9.7.2
	 * @version 5.0.5 Back off between attempts at a failing migration; skip the log write when nothing changed.
	 *
	 * @return void
	 */
	public function run() {
		if ( aioseo()->internalOptions->internal->lastSchemaVersion === aioseo()->version ) {
			return;
		}

		if ( empty( $this->migrations ) ) {
			aioseo()->internalOptions->internal->lastSchemaVersion = aioseo()->version;

			return;
		}

		if ( ! aioseo()->core->db->acquireLock( $this->lockName, 0 ) ) {
			return;
		}

		try {
			$log     = $this->log->read();
			$oldLog  = $log;
			$allDone = true;

			foreach ( $this->migrations as $migration ) {
				$name = $migration->name();

				if ( $this->verifySafely( $migration ) ) {
					if ( ! isset( $log[ $name ] ) || 1 !== ( $log[ $name ]['status'] ?? 0 ) ) {
						$log[ $name ] = $this->successEntry( $migration, $log );
					}
					continue;
				}

				// A persistent failure (e.g. a DB user without ALTER) would otherwise rerun up() on every request.
				if ( $this->isWaitingToRetry( $log[ $name ] ?? null ) ) {
					$allDone = false;
					continue;
				}

				try {
					$migration->up();
					$verified = $this->verifySafely( $migration );

					$log[ $name ] = $verified
						? $this->successEntry( $migration, $log )
						: $this->failureEntry( $migration, $log, 'verify() returned false after up()' );

					if ( ! $verified ) {
						$allDone = false;
					}
				} catch ( \Throwable $e ) {
					$log[ $name ] = $this->failureEntry( $migration, $log, $e->getMessage() );
					$allDone = false;
				}
			}

			if ( $log !== $oldLog ) {
				$this->log->write( $log );
			}

			if ( $allDone ) {
				aioseo()->internalOptions->internal->lastSchemaVersion = aioseo()->version;
			}
		} finally {
			// Explicit release shrinks the hold window to the migration work itself.
			// acquireLock() also registers a shutdown function as belt-and-braces in
			// case execution exits before we reach this point.
			aioseo()->core->db->releaseLock( $this->lockName );
		}
	}

	/**
	 * Wrap verify() so a throwing implementation degrades to "not verified"
	 * instead of bringing down the runner. The error surfaces via the log's
	 * failureEntry on the next up()/verify() cycle.
	 *
	 * @since 4.9.7.2
	 *
	 * @param  Migration $migration The migration to verify.
	 * @return bool
	 */
	private function verifySafely( Migration $migration ) {
		try {
			return (bool) $migration->verify();
		} catch ( \Throwable $e ) {
			return false;
		}
	}

	/**
	 * Whether a failed migration's last attempt is too recent to try again.
	 *
	 * NOTE: a plugin update retries right away, since it may ship the fix.
	 *
	 * @since 5.0.5
	 *
	 * @param  array|null $entry The migration's log entry.
	 * @return bool
	 */
	private function isWaitingToRetry( $entry ) {
		if (
			! is_array( $entry ) ||
			1 === ( $entry['status'] ?? 0 ) ||
			empty( $entry['ran_at'] ) ||
			( $entry['plugin_version'] ?? '' ) !== aioseo()->version
		) {
			return false;
		}

		$attempts = max( 1, (int) ( $entry['attempts'] ?? 1 ) );
		$delay    = min( self::RETRY_DELAY * pow( 2, min( $attempts - 1, 20 ) ), self::MAX_RETRY_DELAY );

		// UTC, since a plugin can change PHP's default time zone between the write and this read.
		return time() < strtotime( $entry['ran_at'] . ' UTC' ) + $delay;
	}

	/**
	 * Build a success log entry, preserving the prior attempts counter so
	 * support can see how many retries it took to land.
	 *
	 * @since 4.9.7.2
	 *
	 * @param  Migration $migration The migration.
	 * @param  array     $log       The current log state.
	 * @return array
	 */
	private function successEntry( Migration $migration, array $log ) {
		$name     = $migration->name();
		$attempts = isset( $log[ $name ]['attempts'] ) ? (int) $log[ $name ]['attempts'] : 0;

		return [
			'version'    => $migration->version(),
			'ran_at'     => gmdate( 'Y-m-d H:i:s' ),
			'status'     => 1,
			'attempts'   => $attempts + 1,
			'last_error' => null
		];
	}

	/**
	 * Build a failure log entry, incrementing attempts so retry pressure is
	 * visible in the log.
	 *
	 * @since   4.9.7.2
	 * @version 5.0.5 Records the plugin version, so an update retries right away.
	 *
	 * @param  Migration $migration The migration.
	 * @param  array     $log       The current log state.
	 * @param  string    $error     Reason for the failure.
	 * @return array
	 */
	private function failureEntry( Migration $migration, array $log, $error ) {
		$name     = $migration->name();
		$attempts = isset( $log[ $name ]['attempts'] ) ? (int) $log[ $name ]['attempts'] : 0;

		return [
			'version'        => $migration->version(),
			'ran_at'         => gmdate( 'Y-m-d H:i:s' ),
			'status'         => 0,
			'attempts'       => $attempts + 1,
			'last_error'     => $error,
			'plugin_version' => aioseo()->version
		];
	}
}