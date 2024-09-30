<?php
namespace AIOSEO\Plugin\Common\Models;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AIOSEO\Plugin\Common\Models as CommonModels;

/**
 * The Crawl Cleanup Log DB Model.
 *
 * @since 4.5.8
 */
class CrawlCleanupLog extends CommonModels\Model {
	/**
	 * The name of the table in the database, without the prefix.
	 *
	 * @since 4.5.8
	 *
	 * @var string
	 */
	protected $table = 'aioseo_crawl_cleanup_logs';

	/**
	 * Fields that should be hidden when serialized.
	 *
	 * @since 4.5.8
	 *
	 * @var array
	 */
	protected $hidden = [ 'id' ];

	/**
	 * Fields that should be numeric values.
	 *
	 * @since 4.5.8
	 *
	 * @var array
	 */
	protected $integerFields = [ 'id', 'hits' ];

	/**
	 * Field to count hits.
	 *
	 * @since 4.5.8
	 *
	 * @var integer
	 */
	public $hits = 0;


	/**
	 * Create a Log in case it doesn't exist.
	 *
	 * @since 4.5.8
	 *
	 * @return void
	 */
	public function create() {
		if ( null !== $this->id ) {
			$this->hits++;
		}

		parent::save();
	}

	/**
	 * Get Crawl Cleanup passing Slug
	 *
	 * @since 4.5.8
	 *
	 * @param  string          $slug The Slug to search.
	 * @return CrawlCleanupLog       The CrawlCleanupLog object.
	 */
	public static function getBySlug( $slug ) {
		return aioseo()->core->db
			->start( 'aioseo_crawl_cleanup_logs' )
			->where( 'hash', sha1( $slug ) )
			->run()
			->model( 'AIOSEO\\Plugin\\Common\\Models\\CrawlCleanupLog' );
	}

	/**
	 * Transforms data as needed.
	 *
	 * @since 4.5.8
	 *
	 * @param  array $data The data array to transform.
	 * @return array       The transformed data.
	 */
	protected function transform( $data, $set = false ) {
		$data = parent::transform( $data, $set );

		// Create slug hash.
		if ( ! empty( $data['slug'] ) ) {
			$data['hash'] = sha1( $data['slug'] );
		}

		return $data;
	}
}