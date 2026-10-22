<?php
namespace AIOSEO\Plugin\Common\Main\Migrations\Definitions;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AIOSEO\Plugin\Common\Main\Migrations\Migration;

/**
 * Folds the legacy `og_custom_image_height`/`og_custom_image_width` columns into `og_image_height`/`og_image_width`.
 *
 * NOTE: 4.1.6's rename ran behind a version gate, and on tables where dbDelta had already added the
 * new columns it failed as a duplicate, so sites past 4.1.6 can still carry the legacy columns.
 *
 * @since 5.0.4
 */
class DropLegacyOgImageDimensionColumns implements Migration {
	/**
	 * The tables to migrate, without the DB prefix.
	 *
	 * @since 5.0.4
	 *
	 * @var array
	 */
	private $tables = [ 'aioseo_posts', 'aioseo_terms' ];

	/**
	 * The legacy columns, keyed by the column that replaces them.
	 *
	 * @since 5.0.4
	 *
	 * @var array
	 */
	private $columns = [
		'og_image_height' => 'og_custom_image_height',
		'og_image_width'  => 'og_custom_image_width'
	];

	/**
	 * {@inheritdoc}
	 *
	 * @since 5.0.4
	 */
	public function name() {
		return 'drop_legacy_og_image_dimension_columns';
	}

	/**
	 * {@inheritdoc}
	 *
	 * @since 5.0.4
	 */
	public function version() {
		return '5.0.4';
	}

	/**
	 * {@inheritdoc}
	 *
	 * @since 5.0.4
	 */
	public function up() {
		foreach ( $this->tables as $table ) {
			foreach ( $this->columns as $newColumn => $legacyColumn ) {
				$this->migrateColumn( $table, $legacyColumn, $newColumn );
			}
		}
	}

	/**
	 * {@inheritdoc}
	 *
	 * NOTE: a missing table verifies as done, since the runner can execute before the tables exist.
	 *
	 * @since 5.0.4
	 */
	public function verify() {
		foreach ( $this->tables as $table ) {
			$existingColumns = $this->existingColumns( $table );
			foreach ( $this->columns as $legacyColumn ) {
				if ( in_array( $legacyColumn, $existingColumns, true ) ) {
					return false;
				}
			}
		}

		return true;
	}

	/**
	 * Moves a legacy column's values into its replacement and removes the legacy column.
	 *
	 * @since 5.0.4
	 *
	 * @param  string $table        The table name, without the DB prefix.
	 * @param  string $legacyColumn The legacy column name.
	 * @param  string $newColumn    The new column name.
	 * @return void
	 */
	public function migrateColumn( $table, $legacyColumn, $newColumn ) {
		$existingColumns = $this->existingColumns( $table );
		if ( ! in_array( $legacyColumn, $existingColumns, true ) ) {
			return;
		}

		$tableName = aioseo()->core->db->db->prefix . $table;

		if ( ! in_array( $newColumn, $existingColumns, true ) ) {
			$after = in_array( 'og_image_url', $existingColumns, true ) ? ' AFTER og_image_url' : '';
			aioseo()->core->db->execute(
				"ALTER TABLE `{$tableName}` CHANGE COLUMN `{$legacyColumn}` `{$newColumn}` int(11) DEFAULT NULL{$after}"
			);
			aioseo()->core->cache->delete( 'db_schema' );

			return;
		}

		$copied = aioseo()->core->db->execute(
			"UPDATE `{$tableName}`
			SET `{$newColumn}` = COALESCE(`{$newColumn}`, `{$legacyColumn}`)
			WHERE `{$newColumn}` IS NULL
			AND `{$legacyColumn}` IS NOT NULL"
		);

		// Keep the legacy column for the next attempt rather than lose values that weren't copied.
		if ( false === $copied ) {
			return;
		}

		aioseo()->core->db->execute(
			"ALTER TABLE `{$tableName}` DROP COLUMN `{$legacyColumn}`"
		);
		aioseo()->core->cache->delete( 'db_schema' );
	}

	/**
	 * The table's current columns. An empty array means the table does not exist.
	 *
	 * NOTE: deliberately not columnExists() — it reads the `db_schema` cache, which is held for a day.
	 *
	 * @since 5.0.4
	 *
	 * @param  string $table The table name, without the DB prefix.
	 * @return array
	 */
	private function existingColumns( $table ) {
		$db = aioseo()->core->db->db;

		return $db->get_col(
			$db->prepare(
				'SELECT COLUMN_NAME
				FROM INFORMATION_SCHEMA.COLUMNS
				WHERE TABLE_SCHEMA = DATABASE()
				AND TABLE_NAME = %s',
				$db->prefix . $table
			)
		);
	}
}