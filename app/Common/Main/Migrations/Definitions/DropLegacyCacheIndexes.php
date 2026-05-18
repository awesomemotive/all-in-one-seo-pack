<?php
namespace AIOSEO\Plugin\Common\Main\Migrations\Definitions;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AIOSEO\Plugin\Common\Main\Migrations\Migration;

/**
 * Drops the legacy unique indexes left behind by the 4.9.7 column renames:
 *
 *   - aioseo_cache.ndx_aioseo_cache_key
 *   - aioseo_crawl_cleanup_blocked_args.ndx_aioseo_crawl_cleanup_blocked_args_key_value_hash
 *
 * Both indexes survived the column rename because dbDelta cannot drop indexes.
 * The legacy `MODIFY COLUMN ... DEFAULT NULL` in 4.9.7's PreUpdates was meant
 * to neuter them by leaving the column NULL on new writes, but on some MariaDB
 * builds the column ended up `NOT NULL DEFAULT ''` instead — every INSERT then
 * collided on the legacy unique index, fired ON DUPLICATE KEY UPDATE against
 * an unrelated row, and silently corrupted cache data.
 *
 * Originally shipped in 4.9.7.1's PreUpdates as a version-gated block, but
 * concurrent-request races at the upgrade moment left some sites with
 * lastActiveVersion = 4.9.7.1 yet the indexes still in place — the migration
 * never actually ran. This migration owns the same repair through the
 * runner, where verify() is the truth signal and the runner keeps retrying
 * until the indexes are confirmed gone.
 *
 * @since 4.9.7.2
 */
class DropLegacyCacheIndexes implements Migration {
	/**
	 * {@inheritdoc}
	 *
	 * @since 4.9.7.2
	 */
	public function name() {
		return 'drop_legacy_cache_indexes';
	}

	/**
	 * {@inheritdoc}
	 *
	 * @since 4.9.7.2
	 */
	public function version() {
		return '4.9.7.2';
	}

	/**
	 * {@inheritdoc}
	 *
	 * Drops each legacy index if it still exists. Clears the cache once the
	 * drops complete — earlier writes on affected sites collided on `key=''`
	 * and overwrote unrelated rows, so the surviving entries can't be trusted.
	 *
	 * @since 4.9.7.2
	 */
	public function up() {
		foreach ( $this->legacyIndexes() as $tableName => $indexName ) {
			if ( ! $this->indexExists( $tableName, $indexName ) ) {
				continue;
			}

			// $tableName and $indexName are hardcoded in legacyIndexes(); no user input.
			aioseo()->core->db->execute( "ALTER TABLE `{$tableName}` DROP INDEX `{$indexName}`" );
		}

		// Verify all drops landed before wiping the cache. If something failed,
		// verify() will return false and the runner will retry on the next request.
		if ( ! $this->verify() ) {
			return;
		}

		aioseo()->core->cache->clear();
	}

	/**
	 * {@inheritdoc}
	 *
	 * @since 4.9.7.2
	 */
	public function verify() {
		foreach ( $this->legacyIndexes() as $tableName => $indexName ) {
			if ( $this->indexExists( $tableName, $indexName ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * The legacy unique indexes this migration drops, keyed by full table name.
	 *
	 * @since 4.9.7.2
	 *
	 * @return array<string,string>
	 */
	private function legacyIndexes() {
		$prefix = aioseo()->core->db->db->prefix;

		return [
			$prefix . 'aioseo_cache'                      => 'ndx_aioseo_cache_key',
			$prefix . 'aioseo_crawl_cleanup_blocked_args' => 'ndx_aioseo_crawl_cleanup_blocked_args_key_value_hash'
		];
	}

	/**
	 * Whether the given index currently exists on the given table.
	 *
	 * @since 4.9.7.2
	 *
	 * @param  string $tableName Full table name (with prefix).
	 * @param  string $indexName Index name.
	 * @return bool
	 */
	private function indexExists( $tableName, $indexName ) {
		$db = aioseo()->core->db->db;

		$result = $db->get_var(
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

		return ! empty( $result );
	}
}