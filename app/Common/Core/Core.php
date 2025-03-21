<?php
namespace AIOSEO\Plugin\Common\Core;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AIOSEO\Plugin\Common\Options;
use AIOSEO\Plugin\Common\Utils;

/**
 * Loads core classes.
 *
 * @since 4.1.9
 */
class Core {
	/**
	 * List of AIOSEO tables.
	 *
	 * @since 4.2.5
	 *
	 * @var array
	 */
	private $aioseoTables = [
		'aioseo_cache',
		'aioseo_crawl_cleanup_blocked_args',
		'aioseo_crawl_cleanup_logs',
		'aioseo_links',
		'aioseo_links_suggestions',
		'aioseo_notifications',
		'aioseo_posts',
		'aioseo_redirects',
		'aioseo_redirects_404',
		'aioseo_redirects_404_logs',
		'aioseo_redirects_hits',
		'aioseo_redirects_logs',
		'aioseo_terms',
		'aioseo_search_statistics_objects',
		'aioseo_revisions'
	];

	/**
	 * Filesystem class instance.
	 *
	 * @since 4.2.7
	 *
	 * @var Utils\Filesystem
	 */
	public $fs = null;

	/**
	 * Assets class instance.
	 *
	 * @since 4.2.7
	 *
	 * @var Utils\Assets
	 */
	public $assets = null;

	/**
	 * DB class instance.
	 *
	 * @since 4.2.7
	 *
	 * @var Utils\Database
	 */
	public $db = null;

	/**
	 * Cache class instance.
	 *
	 * @since 4.2.7
	 *
	 * @var Utils\Cache
	 */
	public $cache = null;

	/**
	 * NetworkCache class instance.
	 *
	 * @since 4.2.7
	 *
	 * @var Utils\NetworkCache
	 */
	public $networkCache = null;

	/**
	 * CachePrune class instance.
	 *
	 * @since 4.2.7
	 *
	 * @var Utils\CachePrune
	 */
	public $cachePrune = null;

	/**
	 * Options Cache class instance.
	 *
	 * @since 4.2.7
	 *
	 * @var Options\Cache
	 */
	public $optionsCache = null;

	/**
	 * Class constructor.
	 *
	 * @since 4.1.9
	 */
	public function __construct() {
		$this->fs           = new Utils\Filesystem( $this );
		$this->assets       = new Utils\Assets( $this );
		$this->db           = new Utils\Database();
		$this->cache        = new Utils\Cache();
		$this->networkCache = new Utils\NetworkCache();
		$this->cachePrune   = new Utils\CachePrune();
		$this->optionsCache = new Options\Cache();
	}

	/**
	 * Get all the DB tables with prefix.
	 *
	 * @since 4.2.5
	 *
	 * @return array An array of tables.
	 */
	public function getDbTables() {
		global $wpdb;

		$tables = [];
		foreach ( $this->aioseoTables as $tableName ) {
			$tables[] = $wpdb->prefix . $tableName;
		}

		return $tables;
	}

	/**
	 * Check if the current request is uninstalling (deleting) AIOSEO.
	 *
	 * @since 4.3.7
	 *
	 * @return bool Whether AIOSEO is being uninstalled/deleted or not.
	 */
	public function isUninstalling() {
		if (
			defined( 'AIOSEO_FILE' ) &&
			defined( 'WP_UNINSTALL_PLUGIN' )
		) {
			// Make sure `plugin_basename()` exists.
			include_once ABSPATH . 'wp-admin/includes/plugin.php';

			return WP_UNINSTALL_PLUGIN === plugin_basename( AIOSEO_FILE );
		}

		return false;
	}
}