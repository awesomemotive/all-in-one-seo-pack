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
		if ( aioseo()->version !== $lastActiveVersion ) {
			// Bust the table/columns cache so that we can start the update migrations with a fresh slate.
			aioseo()->internalOptions->database->installedTables = '';
		}

		if ( version_compare( $lastActiveVersion, '4.1.5', '<' ) ) {
			$this->createCacheTable();
		}

		if ( version_compare( $lastActiveVersion, AIOSEO_VERSION, '<' ) ) {
			aioseo()->core->cache->clear();
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

		$tableName = aioseo()->core->cache->getTableName();
		if ( ! aioseo()->core->db->tableExists( $tableName ) ) {
			$tableName = $db->prefix . $tableName;

			aioseo()->core->db->execute(
				"CREATE TABLE {$tableName} (
					`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
					`key` varchar(80) NOT NULL,
					`value` longtext NOT NULL,
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
}