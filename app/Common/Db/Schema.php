<?php
namespace AIOSEO\Plugin\Common\Db;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Database Schema for AIOSEO Common tables.
 *
 * This class defines the complete, current state of all database tables
 * used by the Common (Lite & Pro) version of AIOSEO. These schemas are used
 * with WordPress's dbDelta() function to automatically create tables and
 * add missing columns.
 *
 * @since 4.9.7
 */
class Schema {
	/**
	 * Get all table schemas for Common tables.
	 *
	 * Returns an array of CREATE TABLE statements for all tables
	 * used by AIOSEO. These will be processed by dbDelta() to ensure
	 * the database schema matches the definitions.
	 *
	 * @since 4.9.7
	 *
	 * @return array Array of SQL CREATE TABLE statements.
	 */
	public function getSchema() {
		return [
			$this->getNotificationsTableSchema(),
			$this->getPostsTableSchema(),
			$this->getCacheTableSchema(),
			$this->getCrawlCleanupLogsTableSchema(),
			$this->getCrawlCleanupBlockedArgsTableSchema(),
			$this->getWritingAssistantPostsTableSchema(),
			$this->getWritingAssistantKeywordsTableSchema(),
			$this->getSeoAnalyzerResultsTableSchema(),
			$this->getAiInsightsKeywordReportsTableSchema()
		];
	}

	/**
	 * Get the schema for aioseo_notifications table.
	 *
	 * @since 4.9.7
	 *
	 * @return string SQL CREATE TABLE statement.
	 */
	public function getNotificationsTableSchema() {
		$tableName      = aioseo()->core->db->db->prefix . 'aioseo_notifications';
		$charsetCollate = aioseo()->core->db->db->get_charset_collate();

		return "CREATE TABLE {$tableName} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			slug varchar(13) NOT NULL,
			addon varchar(64) DEFAULT NULL,
			title text NOT NULL,
			content longtext NOT NULL,
			type varchar(64) NOT NULL,
			level text NOT NULL,
			notification_id bigint(20) unsigned DEFAULT NULL,
			notification_name varchar(255) DEFAULT NULL,
			start datetime DEFAULT NULL,
			end datetime DEFAULT NULL,
			button1_label varchar(255) DEFAULT NULL,
			button1_action varchar(255) DEFAULT NULL,
			button2_label varchar(255) DEFAULT NULL,
			button2_action varchar(255) DEFAULT NULL,
			dismissed tinyint(1) NOT NULL DEFAULT 0,
			new tinyint(1) NOT NULL DEFAULT 1,
			created datetime NOT NULL,
			updated datetime NOT NULL,
			PRIMARY KEY  (id),
			UNIQUE KEY ndx_aioseo_notifications_slug (slug),
			KEY ndx_aioseo_notifications_dates (start, end),
			KEY ndx_aioseo_notifications_type (type),
			KEY ndx_aioseo_notifications_dismissed (dismissed)
		) {$charsetCollate};";
	}

	/**
	 * Get the schema for aioseo_posts table.
	 *
	 * Includes ALL columns in their final form. This table stores
	 * SEO metadata for WordPress posts.
	 *
	 * Column history (for reference):
	 * - 4.0.0: Initial table creation
	 * - 4.0.5: image_scan_date
	 * - 4.1.6: og_image_url, og_image_width, og_image_height, twitter_image_url
	 * - 4.1.8: limit_modified_date
	 * - 4.2.2: options (removed tabs column)
	 * - 4.2.5: schema
	 * - 4.3.6: primary_term
	 * - 4.3.9: priority changed to float
	 * - 4.8.3: breadcrumb_settings
	 * - 4.8.4: ai
	 * - 4.8.6: seo_analyzer_scan_date
	 * - 4.8.7: Added index on pillar_content
	 *
	 * @since 4.9.7
	 *
	 * @return string SQL CREATE TABLE statement.
	 */
	public function getPostsTableSchema() {
		$tableName      = aioseo()->core->db->db->prefix . 'aioseo_posts';
		$charsetCollate = aioseo()->core->db->db->get_charset_collate();

		return "CREATE TABLE {$tableName} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			post_id bigint(20) unsigned NOT NULL,
			title text DEFAULT NULL,
			description text DEFAULT NULL,
			keywords mediumtext DEFAULT NULL,
			keyphrases longtext DEFAULT NULL,
			page_analysis longtext DEFAULT NULL,
			primary_term longtext DEFAULT NULL,
			canonical_url text DEFAULT NULL,
			og_title text DEFAULT NULL,
			og_description text DEFAULT NULL,
			og_object_type varchar(64) DEFAULT 'default',
			og_image_type varchar(64) DEFAULT 'default',
			og_image_custom_url text DEFAULT NULL,
			og_image_custom_fields text DEFAULT NULL,
			og_image_url text DEFAULT NULL,
			og_image_width int(11) DEFAULT NULL,
			og_image_height int(11) DEFAULT NULL,
			og_video varchar(255) DEFAULT NULL,
			og_custom_url text DEFAULT NULL,
			og_article_section text DEFAULT NULL,
			og_article_tags text DEFAULT NULL,
			twitter_use_og tinyint(1) DEFAULT 0,
			twitter_card varchar(64) DEFAULT 'default',
			twitter_image_type varchar(64) DEFAULT 'default',
			twitter_image_custom_url text DEFAULT NULL,
			twitter_image_custom_fields text DEFAULT NULL,
			twitter_image_url text DEFAULT NULL,
			twitter_title text DEFAULT NULL,
			twitter_description text DEFAULT NULL,
			seo_score int(11) DEFAULT 0 NOT NULL,
			schema_type varchar(20) DEFAULT 'default',
			schema_type_options longtext DEFAULT NULL,
			`schema` longtext DEFAULT NULL,
			pillar_content tinyint(1) DEFAULT NULL,
			robots_default tinyint(1) DEFAULT 1 NOT NULL,
			robots_noindex tinyint(1) DEFAULT 0 NOT NULL,
			robots_noarchive tinyint(1) DEFAULT 0 NOT NULL,
			robots_nosnippet tinyint(1) DEFAULT 0 NOT NULL,
			robots_nofollow tinyint(1) DEFAULT 0 NOT NULL,
			robots_noimageindex tinyint(1) DEFAULT 0 NOT NULL,
			robots_noodp tinyint(1) DEFAULT 0 NOT NULL,
			robots_notranslate tinyint(1) DEFAULT 0 NOT NULL,
			robots_max_snippet int(11) DEFAULT NULL,
			robots_max_videopreview int(11) DEFAULT NULL,
			robots_max_imagepreview varchar(20) DEFAULT 'large',
			images longtext DEFAULT NULL,
			image_scan_date datetime DEFAULT NULL,
			priority float DEFAULT NULL,
			frequency tinytext DEFAULT NULL,
			videos longtext DEFAULT NULL,
			video_thumbnail text DEFAULT NULL,
			video_scan_date datetime DEFAULT NULL,
			local_seo longtext DEFAULT NULL,
			limit_modified_date tinyint(1) NOT NULL DEFAULT 0,
			options longtext DEFAULT NULL,
			ai longtext DEFAULT NULL,
			breadcrumb_settings longtext DEFAULT NULL,
			seo_analyzer_scan_date datetime DEFAULT NULL,
			created datetime NOT NULL,
			updated datetime NOT NULL,
			PRIMARY KEY  (id),
			KEY ndx_aioseo_posts_post_id (post_id),
			KEY ndx_aioseo_posts_pillar_content (pillar_content)
		) {$charsetCollate};";
	}

	/**
	 * Get the schema for aioseo_cache table.
	 *
	 * Column history:
	 * - 4.1.5: Initial table creation
	 * - 4.8.8: is_object
	 *
	 * @since 4.9.7
	 *
	 * @return string SQL CREATE TABLE statement.
	 */
	public function getCacheTableSchema() {
		$tableName      = aioseo()->core->db->db->prefix . 'aioseo_cache';
		$charsetCollate = aioseo()->core->db->db->get_charset_collate();

		return "CREATE TABLE {$tableName} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			name varchar(80) NOT NULL,
			value longtext NOT NULL,
			is_object TINYINT(1) DEFAULT 0,
			expiration datetime DEFAULT NULL,
			created datetime NOT NULL,
			updated datetime NOT NULL,
			PRIMARY KEY  (id),
			UNIQUE KEY ndx_aioseo_cache_name (name),
			KEY ndx_aioseo_cache_expiration (expiration)
		) {$charsetCollate};";
	}

	/**
	 * Get the schema for aioseo_crawl_cleanup_logs table.
	 *
	 * @since 4.9.7
	 *
	 * @return string SQL CREATE TABLE statement.
	 */
	public function getCrawlCleanupLogsTableSchema() {
		$tableName      = aioseo()->core->db->db->prefix . 'aioseo_crawl_cleanup_logs';
		$charsetCollate = aioseo()->core->db->db->get_charset_collate();

		return "CREATE TABLE {$tableName} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			slug text NOT NULL,
			param text NOT NULL,
			value text DEFAULT NULL,
			hash varchar(40) NOT NULL,
			hits int(20) NOT NULL DEFAULT 1,
			created datetime NOT NULL,
			updated datetime NOT NULL,
			PRIMARY KEY  (id),
			UNIQUE KEY ndx_aioseo_crawl_cleanup_logs_hash (hash)
		) {$charsetCollate};";
	}

	/**
	 * Get the schema for aioseo_crawl_cleanup_blocked_args table.
	 *
	 * @since 4.9.7
	 *
	 * @return string SQL CREATE TABLE statement.
	 */
	public function getCrawlCleanupBlockedArgsTableSchema() {
		$tableName      = aioseo()->core->db->db->prefix . 'aioseo_crawl_cleanup_blocked_args';
		$charsetCollate = aioseo()->core->db->db->get_charset_collate();

		return "CREATE TABLE {$tableName} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			param text DEFAULT NULL,
			value text DEFAULT NULL,
			param_value_hash varchar(40) DEFAULT NULL,
			regex varchar(150) DEFAULT NULL,
			hits int(20) NOT NULL DEFAULT 0,
			created datetime NOT NULL,
			updated datetime NOT NULL,
			PRIMARY KEY  (id),
			UNIQUE KEY ndx_aioseo_crawl_cleanup_blocked_args_param_value_hash (param_value_hash),
			UNIQUE KEY ndx_aioseo_crawl_cleanup_blocked_args_regex (regex)
		) {$charsetCollate};";
	}

	/**
	 * Get the schema for aioseo_writing_assistant_posts table.
	 *
	 * @since 4.9.7
	 *
	 * @return string SQL CREATE TABLE statement.
	 */
	public function getWritingAssistantPostsTableSchema() {
		$tableName      = aioseo()->core->db->db->prefix . 'aioseo_writing_assistant_posts';
		$charsetCollate = aioseo()->core->db->db->get_charset_collate();

		return "CREATE TABLE {$tableName} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			post_id bigint(20) unsigned DEFAULT NULL,
			keyword_id bigint(20) unsigned DEFAULT NULL,
			content_analysis_hash VARCHAR(40) DEFAULT NULL,
			content_analysis text DEFAULT NULL,
			created datetime NOT NULL,
			updated datetime NOT NULL,
			PRIMARY KEY  (id),
			UNIQUE KEY ndx_aioseo_writing_assistant_posts_post_id (post_id),
			KEY ndx_aioseo_writing_assistant_posts_keyword_id (keyword_id)
		) {$charsetCollate};";
	}

	/**
	 * Get the schema for aioseo_writing_assistant_keywords table.
	 *
	 * @since 4.9.7
	 *
	 * @return string SQL CREATE TABLE statement.
	 */
	public function getWritingAssistantKeywordsTableSchema() {
		$tableName      = aioseo()->core->db->db->prefix . 'aioseo_writing_assistant_keywords';
		$charsetCollate = aioseo()->core->db->db->get_charset_collate();

		return "CREATE TABLE {$tableName} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			uuid varchar(40) NOT NULL,
			keyword varchar(255) NOT NULL,
			country varchar(10) NOT NULL DEFAULT 'us',
			language varchar(10) NOT NULL DEFAULT 'en',
			progress tinyint(3) DEFAULT 0,
			keywords mediumtext DEFAULT NULL,
			competitors mediumtext DEFAULT NULL,
			created datetime NOT NULL,
			updated datetime NOT NULL,
			PRIMARY KEY  (id),
			UNIQUE KEY ndx_aioseo_writing_assistant_keywords_uuid (uuid),
			KEY ndx_aioseo_writing_assistant_keywords_keyword (keyword)
		) {$charsetCollate};";
	}

	/**
	 * Get the schema for aioseo_seo_analyzer_results table.
	 *
	 * @since 4.9.7
	 *
	 * @return string SQL CREATE TABLE statement.
	 */
	public function getSeoAnalyzerResultsTableSchema() {
		$tableName      = aioseo()->core->db->db->prefix . 'aioseo_seo_analyzer_results';
		$charsetCollate = aioseo()->core->db->db->get_charset_collate();

		return "CREATE TABLE {$tableName} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			data text NOT NULL,
			score varchar(255) DEFAULT NULL,
			competitor_url varchar(255) DEFAULT NULL,
			created datetime NOT NULL,
			updated datetime NOT NULL,
			PRIMARY KEY  (id),
			KEY ndx_aioseo_seo_analyzer_results_competitor_url (competitor_url)
		) {$charsetCollate};";
	}

	/**
	 * Get the schema for aioseo_seo_analyzer_results table.
	 *
	 * @since 4.9.7
	 *
	 * @return string SQL CREATE TABLE statement.
	 */
	public function getAiInsightsKeywordReportsTableSchema() {
		$db             = aioseo()->core->db->db;
		$tableName      = $db->prefix . 'aioseo_ai_insights_keyword_reports';
		$charsetCollate = '';

		if ( ! empty( $db->charset ) ) {
			$charsetCollate .= "DEFAULT CHARACTER SET {$db->charset}";
		}
		if ( ! empty( $db->collate ) ) {
			$charsetCollate .= " COLLATE {$db->collate}";
		}

		return "CREATE TABLE {$tableName} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			uuid varchar(40) NOT NULL,
			keyword varchar(255) NOT NULL,
			status varchar(20) NOT NULL DEFAULT 'pending',
			brands longtext DEFAULT NULL,
			brands_mentioned int(11) DEFAULT 0,
			results longtext DEFAULT NULL,
			created datetime NOT NULL,
			updated datetime NOT NULL,
			PRIMARY KEY  (id),
			KEY ndx_aioseo_ai_insights_keyword_reports_uuid (uuid),
			KEY ndx_aioseo_ai_insights_keyword_reports_keyword (keyword),
			KEY ndx_aioseo_ai_insights_keyword_reports_status (status)
		) {$charsetCollate};";
	}
}