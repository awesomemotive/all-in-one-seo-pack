<?php
namespace AIOSEO\Plugin\Common\Main;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * This class contains pre-updates necessary for the next updates class to run.
 *
 * @since 4.1.5
 */
class PreUpdates {
	/**
	 * Class constructor.
	 *
	 * @since 4.1.5
	 */
	public function __construct() {
		// We don't want an AJAX request check here since the plugin might be installed/activated for the first time via AJAX (e.g. EDD/BLC).
		// If that's the case, the cache table needs to be created before the activation hook runs.
		if ( wp_doing_cron() ) {
			return;
		}

		$lastActiveVersion = aioseo()->internalOptions->internal->lastActiveVersion;

		// Acquire a database lock to prevent race conditions when multiple concurrent
		// requests (e.g., page load + AJAX) trigger the migration simultaneously.
		// Without this lock, parallel requests can pass the column existence checks
		// before either has added the column, causing "Duplicate column name" errors.
		if ( ! aioseo()->core->db->acquireLock( 'aioseo_pre_updates', 0 ) ) {
			return;
		}

		if ( version_compare( $lastActiveVersion, '4.9.7', '<' ) ) {
			$this->addIsObjectColumnToCache();
			$this->cleanCacheTable(); // Clean duplicate entries before schema changes
			$this->updateCacheTable(); // update the cache table to use the new name column
			$this->createCacheTable(); // Run dbDelta first to add the 'name' column

			$this->updateCrawlCleanupLogsTable();
			$this->createCrawlCleanupLogsTable();

			aioseo()->core->cache->delete( 'db_schema' );
		}

		if ( version_compare( $lastActiveVersion, '4.9.7.1', '<' ) ) {
			$this->dropLegacyUniqueIndexes();

			// Wipe every cache row on affected sites — the unique-index collision on `key=''`
			// meant earlier writes have been overwriting the wrong rows. Cleaner to repopulate
			// from scratch than to leave stale wrong-name-to-value mappings lying around.
			aioseo()->core->cache->clear();
		}
	}

	/**
	 * Updates the cache table to use the new name column.
	 *
	 * NOTE: This method uses raw SQL queries to check table/column existence instead of
	 * the Database helper methods (tableExists/columnExists) because those methods rely on
	 * the cache system, which is exactly what we're trying to migrate here.
	 *
	 * @since 4.9.7
	 *
	 * @return void
	 */
	private function updateCacheTable() {
		$db        = aioseo()->core->db->db;
		$tableName = $db->prefix . 'aioseo_cache';

		// Check if table exists using raw SQL (bypass cache to avoid circular dependency)
		$tableExists = $db->get_var(
			$db->prepare(
				'SELECT TABLE_NAME
				FROM INFORMATION_SCHEMA.TABLES
				WHERE TABLE_SCHEMA = DATABASE()
				AND TABLE_NAME = %s',
				$tableName
			)
		);

		if ( empty( $tableExists ) ) {
			return;
		}

		// Check if 'key' column exists using raw SQL (bypass cache)
		$keyColumnExists = $db->get_var(
			$db->prepare(
				"SELECT COLUMN_NAME
				FROM INFORMATION_SCHEMA.COLUMNS
				WHERE TABLE_SCHEMA = DATABASE()
				AND TABLE_NAME = %s
				AND COLUMN_NAME = 'key'",
				$tableName
			)
		);

		if ( ! empty( $keyColumnExists ) ) {
			// set key column as nullable to avoid retro compatibility issues
			aioseo()->core->db->execute( "ALTER TABLE {$tableName} MODIFY COLUMN `key` varchar(80) DEFAULT NULL" );
		}
	}

	/**
	 * Updates the crawl cleanup logs table to use the new param column.
	 *
	 * NOTE: This method uses raw SQL queries to check table/column existence instead of
	 * the Database helper methods (tableExists/columnExists) because those methods rely on
	 * the cache system, which is exactly what we're trying to migrate here.
	 *
	 * Each operation checks its precondition immediately before executing to handle
	 * cases where the migration may have partially run before.
	 *
	 * @since 4.9.7
	 *
	 * @return void
	 */
	private function updateCrawlCleanupLogsTable() {
		$db = aioseo()->core->db->db;

		// Process aioseo_crawl_cleanup_logs table
		$logsTableName = $db->prefix . 'aioseo_crawl_cleanup_logs';

		// Check if logs table exists using raw SQL
		$logsTableExists = $db->get_var(
			$db->prepare(
				'SELECT TABLE_NAME
				FROM INFORMATION_SCHEMA.TABLES
				WHERE TABLE_SCHEMA = DATABASE()
				AND TABLE_NAME = %s',
				$logsTableName
			)
		);

		if ( ! empty( $logsTableExists ) ) {
			// Add the param column if it doesn't exist (check immediately before operation)
			$paramColumnExists = $db->get_var(
				$db->prepare(
					"SELECT COLUMN_NAME
					FROM INFORMATION_SCHEMA.COLUMNS
					WHERE TABLE_SCHEMA = DATABASE()
					AND TABLE_NAME = %s
					AND COLUMN_NAME = 'param'",
					$logsTableName
				)
			);

			if ( empty( $paramColumnExists ) ) {
				aioseo()->core->db->execute( "ALTER TABLE {$logsTableName} ADD COLUMN `param` TEXT NOT NULL AFTER `slug`" );
			}

			// Check if 'key' column exists immediately before trying to use
			$logsKeyColumnExists = $db->get_var(
				$db->prepare(
					"SELECT COLUMN_NAME
					FROM INFORMATION_SCHEMA.COLUMNS
					WHERE TABLE_SCHEMA = DATABASE()
					AND TABLE_NAME = %s
					AND COLUMN_NAME = 'key'",
					$logsTableName
				)
			);

			if ( ! empty( $logsKeyColumnExists ) ) {
				// Migrate data from key to param if needed
				aioseo()->core->db->execute( "UPDATE {$logsTableName} SET `param` = `key` WHERE `param` IS NULL OR `param` = ''" );

				// set key column as nullable to avoid retro compatibility issues
				aioseo()->core->db->execute( "ALTER TABLE {$logsTableName} MODIFY COLUMN `key` TEXT DEFAULT NULL" );
			}
		}

		// Process aioseo_crawl_cleanup_blocked_args table
		$blockedArgsTableName = $db->prefix . 'aioseo_crawl_cleanup_blocked_args';

		// Check if blocked args table exists using raw SQL
		$blockedArgsTableExists = $db->get_var(
			$db->prepare(
				'SELECT TABLE_NAME
				FROM INFORMATION_SCHEMA.TABLES
				WHERE TABLE_SCHEMA = DATABASE()
				AND TABLE_NAME = %s',
				$blockedArgsTableName
			)
		);

		if ( empty( $blockedArgsTableExists ) ) {
			return;
		}

		// Add the param column if it doesn't exist (check immediately before operation)
		$paramColumnExists = $db->get_var(
			$db->prepare(
				"SELECT COLUMN_NAME
				FROM INFORMATION_SCHEMA.COLUMNS
				WHERE TABLE_SCHEMA = DATABASE()
				AND TABLE_NAME = %s
				AND COLUMN_NAME = 'param'",
				$blockedArgsTableName
			)
		);

		if ( empty( $paramColumnExists ) ) {
			aioseo()->core->db->execute( "ALTER TABLE {$blockedArgsTableName} ADD COLUMN `param` TEXT DEFAULT NULL AFTER `id`" );
		}

		// Check if 'key' column exists immediately before trying to use
		$keyColumnExists = $db->get_var(
			$db->prepare(
				"SELECT COLUMN_NAME
				FROM INFORMATION_SCHEMA.COLUMNS
				WHERE TABLE_SCHEMA = DATABASE()
				AND TABLE_NAME = %s
				AND COLUMN_NAME = 'key'",
				$blockedArgsTableName
			)
		);

		if ( ! empty( $keyColumnExists ) ) {
			// Migrate data from key to param.
			aioseo()->core->db->execute( "UPDATE {$blockedArgsTableName} SET `param` = `key` WHERE `param` IS NULL OR `param` = ''" );
		}

		// Add the param_value_hash column if it doesn't exist (check immediately before operation)
		$paramValueHashColumnExists = $db->get_var(
			$db->prepare(
				"SELECT COLUMN_NAME
				FROM INFORMATION_SCHEMA.COLUMNS
				WHERE TABLE_SCHEMA = DATABASE()
				AND TABLE_NAME = %s
				AND COLUMN_NAME = 'param_value_hash'",
				$blockedArgsTableName
			)
		);

		if ( empty( $paramValueHashColumnExists ) ) {
			aioseo()->core->db->execute( "ALTER TABLE {$blockedArgsTableName} ADD COLUMN `param_value_hash` varchar(40) DEFAULT NULL" );
		}

		// Check if 'key_value_hash' column exists immediately before trying to use
		$keyValueHashColumnExists = $db->get_var(
			$db->prepare(
				"SELECT COLUMN_NAME
				FROM INFORMATION_SCHEMA.COLUMNS
				WHERE TABLE_SCHEMA = DATABASE()
				AND TABLE_NAME = %s
				AND COLUMN_NAME = 'key_value_hash'",
				$blockedArgsTableName
			)
		);

		if ( ! empty( $keyValueHashColumnExists ) ) {
			// Migrate data from key_value_hash to param_value_hash.
			aioseo()->core->db->execute( "UPDATE {$blockedArgsTableName} SET `param_value_hash` = `key_value_hash` WHERE `param_value_hash` IS NULL OR `param_value_hash` = ''" );
		}
	}

	/**
	 * Cleans the cache table before schema changes.
	 *
	 * Removes duplicate and empty entries that would prevent adding a UNIQUE KEY constraint.
	 *
	 * NOTE: This method uses raw SQL queries to check table/column existence instead of
	 * the Database helper methods (tableExists/columnExists) because those methods rely on
	 * the cache system, which is exactly what we're trying to migrate here.
	 *
	 * @since 4.9.7
	 *
	 * @return void
	 */
	private function cleanCacheTable() {
		$db        = aioseo()->core->db->db;
		$tableName = $db->prefix . 'aioseo_cache';

		// Check if table exists using raw SQL (bypass cache to avoid circular dependency)
		$tableExists = $db->get_var(
			$db->prepare(
				'SELECT TABLE_NAME
				FROM INFORMATION_SCHEMA.TABLES
				WHERE TABLE_SCHEMA = DATABASE()
				AND TABLE_NAME = %s',
				$tableName
			)
		);

		if ( empty( $tableExists ) ) {
			return;
		}

		// Only truncate if the table still has the old 'key' column, meaning this is an
		// upgrade that needs cleaning before adding the UNIQUE KEY on 'name'.
		// Fresh installs and already-migrated tables don't need cleaning and may contain
		// important cache entries like 'activation_redirect' for the setup wizard.
		$keyColumnExists = $db->get_var(
			$db->prepare(
				"SELECT COLUMN_NAME
				FROM INFORMATION_SCHEMA.COLUMNS
				WHERE TABLE_SCHEMA = DATABASE()
				AND TABLE_NAME = %s
				AND COLUMN_NAME = 'key'",
				$tableName
			)
		);

		if ( empty( $keyColumnExists ) ) {
			return;
		}

		aioseo()->core->db->execute( "TRUNCATE TABLE {$tableName}" );
	}

	/**
	 * Creates a new aioseo_cache table.
	 *
	 * Now uses dbDelta to update DB schema instead of manual table creation.
	 *
	 * @since 4.1.5
	 *
	 * @return void
	 */
	public function createCacheTable() {
		if ( ! function_exists( 'dbDelta' ) ) {
			require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		}

		// Truncate existing rows BEFORE dbDelta to prevent "Duplicate entry" errors
		// when adding the UNIQUE KEY on the new 'name' column. Without this, dbDelta adds
		// the column with default '' for all existing rows, causing duplicate key violations.
		// Only needed when upgrading from an old schema that has the 'key' column.
		$db        = aioseo()->core->db->db;
		$tableName = $db->prefix . 'aioseo_cache';
		$keyColumnExists = $db->get_var(
			$db->prepare(
				"SELECT COLUMN_NAME
				FROM INFORMATION_SCHEMA.COLUMNS
				WHERE TABLE_SCHEMA = DATABASE()
				AND TABLE_NAME = %s
				AND COLUMN_NAME = 'key'",
				$tableName
			)
		);

		if ( ! empty( $keyColumnExists ) ) {
			aioseo()->core->db->execute( "TRUNCATE TABLE {$tableName}" );
		}

		// Use dbDelta to create the cache table based on schema definition.
		$schema = aioseo()->dbSchema->getCacheTableSchema();
		dbDelta( $schema );

		// Only clear cache if we migrated from the old schema, to avoid wiping
		// important cache entries like 'activation_redirect' on fresh installs.
		if ( ! empty( $keyColumnExists ) ) {
			aioseo()->core->cache->clearPrefix( '' );
		}
	}

	/**
	 * Creates a new aioseo_crawl_cleanup_logs table.
	 *
	 * Now uses dbDelta to update DB schema instead of manual table creation.
	 *
	 * @since 4.9.7
	 *
	 * @return void
	 */
	public function createCrawlCleanupLogsTable() {
		if ( ! function_exists( 'dbDelta' ) ) {
			require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		}

		// Use dbDelta to create the crawl cleanup logs and blocked args tables based on schema definition
		$schemas = [ aioseo()->dbSchema->getCrawlCleanupLogsTableSchema(), aioseo()->dbSchema->getCrawlCleanupBlockedArgsTableSchema() ];
		dbDelta( $schemas );
	}

	/**
	 * Adds the is_object column to the cache table.
	 *
	 * NOTE: This method uses raw SQL queries instead of the Cache class methods
	 * (clear/delete) because those methods rely on the 'name' column which may
	 * not exist yet during the migration from 'key' to 'name'.
	 *
	 * @since 4.9.1
	 *
	 * @return void
	 */
	public function addIsObjectColumnToCache() {
		$db        = aioseo()->core->db->db;
		$tableName = $db->prefix . 'aioseo_cache';

		// Check if table exists using raw SQL (on fresh installs, the table won't exist yet).
		$tableExists = $db->get_var(
			$db->prepare(
				'SELECT TABLE_NAME
				FROM INFORMATION_SCHEMA.TABLES
				WHERE TABLE_SCHEMA = DATABASE()
				AND TABLE_NAME = %s',
				$tableName
			)
		);

		if ( empty( $tableExists ) ) {
			return;
		}

		// Check if column exists using raw SQL (bypass cache completely), otherwise we will get errors
		$columnExists = $db->get_var(
			$db->prepare(
				"SELECT COLUMN_NAME
				FROM INFORMATION_SCHEMA.COLUMNS
				WHERE TABLE_SCHEMA = DATABASE()
				AND TABLE_NAME = %s
				AND COLUMN_NAME = 'is_object'",
				$tableName
			)
		);

		if ( empty( $columnExists ) ) {
			// Try to acquire a lock to prevent race conditions (0 timeout = don't wait)
			if ( ! aioseo()->core->db->acquireLock( 'aioseo_add_is_object_column', 0 ) ) {
				return;
			}

			aioseo()->core->db->execute(
				"ALTER TABLE {$tableName}
				ADD `is_object` TINYINT(1) DEFAULT 0 AFTER `value`"
			);

			// Clear the cache using raw SQL since existing entries won't have the is_object flag.
			// We use raw SQL because the Cache class methods rely on the 'name' column which
			// may not exist yet during the migration from 'key' to 'name'.
			aioseo()->core->db->execute( "TRUNCATE TABLE {$tableName}" );

			aioseo()->core->db->releaseLock( 'aioseo_add_is_object_column' );
		}
	}

	/**
	 * Drops the legacy unique indexes left behind after the 4.9.7 column renames
	 * (`aioseo_cache.key` -> `name`, `aioseo_crawl_cleanup_blocked_args.key_value_hash`
	 * -> `param_value_hash`). The new write path does not set the legacy column, but
	 * when its post-migration DEFAULT ends up as `''` instead of `NULL` on some
	 * MariaDB builds, the surviving unique index collides on every INSERT and
	 * `ON DUPLICATE KEY UPDATE` corrupts an unrelated row.
	 *
	 * Idempotent — indexes are only dropped if present.
	 *
	 * @since 4.9.7.1
	 *
	 * @return void
	 */
	public function dropLegacyUniqueIndexes() {
		$db = aioseo()->core->db->db;

		$indexes = [
			$db->prefix . 'aioseo_cache'                      => 'ndx_aioseo_cache_key',
			$db->prefix . 'aioseo_crawl_cleanup_blocked_args' => 'ndx_aioseo_crawl_cleanup_blocked_args_key_value_hash',
		];

		foreach ( $indexes as $tableName => $indexName ) {
			$indexExists = $db->get_var(
				$db->prepare(
					'SELECT INDEX_NAME
					FROM INFORMATION_SCHEMA.STATISTICS
					WHERE TABLE_SCHEMA = DATABASE()
					AND TABLE_NAME = %s
					AND INDEX_NAME = %s',
					$tableName,
					$indexName
				)
			);

			if ( empty( $indexExists ) ) {
				continue;
			}

			// Use backticks on identifiers; both names are hardcoded above, no user input
			// reaches this query.
			aioseo()->core->db->execute( "ALTER TABLE `{$tableName}` DROP INDEX `{$indexName}`" );
		}
	}
}