<?php
namespace AIOSEO\Plugin\Common\Models;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AIOSEO\Plugin\Common\Models as CommonModels;
/**
 * The Crawl Cleanup Blocked Arg DB Model.
 *
 * @since 4.5.8
 */
class CrawlCleanupBlockedArg extends CommonModels\Model {
	/**
	 * The name of the table in the database, without the prefix.
	 *
	 * @since 4.5.8
	 *
	 * @var string
	 */
	protected $table = 'aioseo_crawl_cleanup_blocked_args';

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
	protected $numericFields = [ 'id', 'hits' ];

	/**
	 * Field to count hits.
	 *
	 * @since 4.5.8
	 *
	 * @var integer
	 */
	protected $hits = 0;

	/**
	 * Field for Regex.
	 *
	 * @since 4.5.8
	 *
	 * @var string
	 */
	public $regex = null;

	/**
	 * Field that contains the hash for key+value
	 *
	 * @since 4.5.8
	 *
	 * @var string
	 */
	public $key_value_hash = null;

	/**
	 * Separator used to merge key and value string.
	 *
	 * @since 4.5.8
	 *
	 * @var string
	 */
	private static $keyValueSeparator = '=';

	/**
	 * Separator used to merge key and value string.
	 *
	 * @since 4.5.8
	 *
	 * @var CrawlCleanupBlockedArg|null
	 */
	private static $regexBlockedArgs = null;

	/**
	 * Class constructor.
	 *
	 * @since 4.5.8
	 *
	 * @param mixed $var This can be the primary key of the resource, or it could be an array of data to manufacture a resource without a database query.
	 */
	public function __construct( $var = null ) {
		parent::__construct( $var );
	}

	/**
	 * Get Blocked row using Key and Value.
	 *
	 * @since 4.5.8
	 *
	 * @param  string                 $key   The key to search.
	 * @param  string                 $value The value to search.
	 * @return CrawlCleanupBlockedArg        The CrawlCleanupBlockedArg object.
	 */
	public static function getByKeyValue( $key, $value ) {
		$keyValue = self::getKeyValueString( $key, $value );

		return aioseo()->core->db
			->start( 'aioseo_crawl_cleanup_blocked_args' )
			->where( 'key_value_hash', sha1( $keyValue ) )
			->run()
			->model( 'AIOSEO\\Plugin\\Common\\Models\\CrawlCleanupBlockedArg' );
	}

	/**
	 * Get Blocked row using Regex Value.
	 *
	 * @since 4.5.8
	 *
	 * @param  string                 $regex The regex value to search.
	 * @return CrawlCleanupBlockedArg        The CrawlCleanupBlockedArg object.
	 */
	public static function getByRegex( $regex ) {
		return aioseo()->core->db
			->start( 'aioseo_crawl_cleanup_blocked_args' )
			->where( 'regex', $regex )
			->run()
			->model( 'AIOSEO\\Plugin\\Common\\Models\\CrawlCleanupBlockedArg' );
	}

	/**
	 * Look for regex match by key and value.
	 *
	 * @since 4.5.8
	 *
	 * @param  string                 $key   The key to search.
	 * @param  string                 $value The value to search.
	 * @return CrawlCleanupBlockedArg        The CrawlCleanupBlockedArg object.
	 */
	public static function matchRegex( $key, $value ) {
		$keyValue = self::getKeyValueString( $key, $value );
		$regexBlockedArgs = self::getRegexBlockedArgs();

		foreach ( $regexBlockedArgs as $regexQueryArg ) {
			$escapedRegex = str_replace( '@', '\@', $regexQueryArg->regex );
			if ( preg_match( "@{$escapedRegex}@", $keyValue ) ) {
				return new CrawlCleanupBlockedArg( $regexQueryArg->id );
			}
		}

		return new CrawlCleanupBlockedArg();
	}

	/**
	 * Get Regex rows.
	 *
	 * @since 4.5.8
	 *
	 * @return CrawlCleanupBlockedArg The CrawlCleanupBlockedArg object.
	 */
	public static function getRegexBlockedArgs() {
		if ( null === self::$regexBlockedArgs ) {
			self::$regexBlockedArgs = aioseo()->core->db
				->start( 'aioseo_crawl_cleanup_blocked_args' )
				->select( 'id, regex' )
				->whereRaw( 'regex IS NOT NULL' )
				->run()
				->result();
		}

		return self::$regexBlockedArgs;
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

		// Create key+value hash.
		if ( ! empty( $data['key'] ) ) {
			$keyValue = self::getKeyValueString( $data['key'], $data['value'] );
			$data['key_value_hash'] = sha1( $keyValue );
		}

		// Case hits number are empty start with 0.
		if ( empty( $data['hits'] ) ) {
			$data['hits'] = 0;
		}

		return $data;
	}

	/**
	 * Increase hits and save.
	 *
	 * @since 4.5.8
	 *
	 */
	public function addHit() {
		if ( $this->id ) {
			$this->hits++;
			parent::save();
		}
	}

	/**
	 * Return string with key and value with pattern model defined.
	 *
	 * @since 4.5.8
	 *
	 * @param  string $key   The key to merge.
	 * @param  string $value The value to merge.
	 * @return string        The result string merging key and value (case not empty).
	 */
	public static function getKeyValueString( $key, $value ) {
		return $key . ( $value ? self::getKeyValueSeparator() . $value : '' );
	}

	/**
	 * Return string to separate key and value.
	 *
	 * @since 4.5.8
	 *
	 * @return string The separator for key and value.
	 */
	public static function getKeyValueSeparator() {
		return self::$keyValueSeparator;
	}
}