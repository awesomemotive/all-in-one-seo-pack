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
		if ( version_compare( $lastActiveVersion, '4.1.5', '<' ) ) {
			$this->createCacheTable();
		}

		if ( version_compare( $lastActiveVersion, '4.9.1', '<' ) ) {
			$this->addIsObjectColumnToCache();
		}

		// This should be executed AFTER the cache table is created.
		if ( aioseo()->version !== $lastActiveVersion ) {
			// Bust the table/columns cache so that we can start the update migrations with a fresh slate.
			aioseo()->core->cache->delete( 'db_schema' );
		}
	}

	/**
	 * Creates a new aioseo_cache table.
	 *
	 * @since 4.1.5
	 *
	 * @return void
	 */
	public function createCacheTable() {
		$db             = aioseo()->core->db->db;
		$charsetCollate = '';

		if ( ! empty( $db->charset ) ) {
			$charsetCollate .= "DEFAULT CHARACTER SET {$db->charset}";
		}
		if ( ! empty( $db->collate ) ) {
			$charsetCollate .= " COLLATE {$db->collate}";
		}

		// Check if the cache table exists with SQL. We don't want to use our own helper method here because
		// it relies on the cache table being created.
		$result = $db->get_var( "SHOW TABLES LIKE '{$db->prefix}aioseo_cache'" );
		if ( empty( $result ) ) {
			$tableName = $db->prefix . 'aioseo_cache';

			aioseo()->core->db->execute(
				"CREATE TABLE {$tableName} (
					`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
					`key` varchar(80) NOT NULL,
					`value` longtext NOT NULL,
					`is_object` TINYINT(1) DEFAULT 0,
					`expiration` datetime NULL,
					`created` datetime NOT NULL,
					`updated` datetime NOT NULL,
					PRIMARY KEY (`id`),
					UNIQUE KEY ndx_aioseo_cache_key (`key`),
					KEY ndx_aioseo_cache_expiration (`expiration`)
				) {$charsetCollate};"
			);
		}
	}

	/**
	 * Adds the is_object column to the cache table.
	 *
	 * @since 4.9.1
	 *
	 * @return void
	 */
	public function addIsObjectColumnToCache() {
		$db = aioseo()->core->db->db;
		$tableName = $db->prefix . 'aioseo_cache';

		// Try to acquire a lock to prevent race conditions (0 timeout = don't wait)
		if ( ! aioseo()->core->db->acquireLock( 'aioseo_add_is_object_column', 0 ) ) {
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
			aioseo()->core->db->execute(
				"ALTER TABLE {$tableName}
				ADD `is_object` TINYINT(1) DEFAULT 0 AFTER `value`"
			);

			// Clear the cache since existing entries won't have the is_object flag.
			aioseo()->core->cache->clear();

			// Reset the cache for the installed tables.
			aioseo()->core->cache->delete( 'db_schema' );
		}

		aioseo()->core->db->releaseLock( 'aioseo_add_is_object_column' );
	}
}