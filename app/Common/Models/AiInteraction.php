<?php
namespace AIOSEO\Plugin\Common\Models;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The AI Interaction DB Model.
 *
 * @since 4.9.0
 */
class AiInteraction extends Model {
	/**
	 * The table name.
	 *
	 * @since 4.9.0
	 *
	 * @var string
	 */
	protected $table = 'aioseo_ai_interactions';

	/**
	 * Fields that should be json encoded on save and json decoded on get.
	 *
	 * @since 4.9.0
	 *
	 * @var array
	 */
	protected $jsonFields = [ 'data', 'response' ];

	/**
	 * Fields that should be hidden when serialized.
	 *
	 * @since 4.9.0
	 *
	 * @var array
	 */
	protected $hidden = [];

	/**
	 * Fields that should be boolean values.
	 *
	 * @since 4.9.0
	 *
	 * @var array
	 */
	protected $booleanFields = [];

	/**
	 * The schema definition for the table.
	 *
	 * @since 4.9.0
	 *
	 * @return string The schema.
	 */
	public static function getSchema() {
		global $wpdb;

		$tableName      = $wpdb->prefix . 'aioseo_ai_interactions';
		$charsetCollate = $wpdb->get_charset_collate();

		return "CREATE TABLE {$tableName} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			action varchar(100) NOT NULL,
			provider varchar(50) NOT NULL,
			user_id bigint(20) unsigned NOT NULL,
			post_id bigint(20) unsigned DEFAULT NULL,
			prompt text,
			data longtext,
			response longtext,
			success tinyint(1) DEFAULT 1,
			tokens_used int DEFAULT NULL,
			created_at datetime NOT NULL,
			PRIMARY KEY  (id),
			KEY action (action),
			KEY provider (provider),
			KEY user_id (user_id),
			KEY post_id (post_id),
			KEY created_at (created_at)
		) {$charsetCollate};";
	}
}
