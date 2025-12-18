<?php
namespace AIOSEO\Plugin\Common\Utils;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles our cache pruning.
 *
 * @since 4.1.5
 */
class CachePrune {
	/**
	 * The action for the scheduled cache prune.
	 *
	 * @since 4.1.5
	 *
	 * @var string
	 */
	private $pruneAction = 'aioseo_cache_prune';

	/**
	 * The action for the scheduled old cache clean.
	 *
	 * @since 4.1.5
	 *
	 * @var string
	 */
	private $optionCacheCleanAction = 'aioseo_old_cache_clean';

	/**
	 * Class constructor.
	 *
	 * @since 4.1.5
	 */
	public function __construct() {
		add_action( 'init', [ $this, 'init' ] );
	}

	/**
	 * Inits our class.
	 *
	 * @since 4.1.5
	 *
	 * @return void
	 */
	public function init() {
		add_action( $this->pruneAction, [ $this, 'prune' ] );
		add_action( $this->optionCacheCleanAction, [ $this, 'optionCacheClean' ] );

		if ( ! is_admin() ) {
			return;
		}

		if ( ! aioseo()->actionScheduler->isScheduled( $this->pruneAction ) ) {
			aioseo()->actionScheduler->scheduleRecurrent( $this->pruneAction, 0, DAY_IN_SECONDS );
		}
	}

	/**
	 * Prunes our expired cache.
	 *
	 * @since 4.1.5
	 *
	 * @return void
	 */
	public function prune() {
		aioseo()->core->db->delete( aioseo()->core->cache->getTableName() )
			->whereRaw( '( `expiration` IS NOT NULL AND expiration <= \'' . aioseo()->helpers->timeToMysql( time() ) . '\' )' )
			->run();
	}

	/**
	 * Cleans our old options cache.
	 *
	 * @since 4.1.5
	 *
	 * @return void
	 */
	public function optionCacheClean() {
		$optionCache = aioseo()->core->db->delete( aioseo()->core->db->db->options, true )
			->whereLike( 'option_name', '_aioseo_cache_%', true )
			->limit( 10000 )
			->run();

		// Schedule a new run if we're not done cleaning.
		if ( 0 !== $optionCache->db->rows_affected ) {
			aioseo()->actionScheduler->scheduleSingle( $this->optionCacheCleanAction, MINUTE_IN_SECONDS, [], true );
		}
	}

	/**
	 * Returns the action name for the old cache clean.
	 *
	 * @since 4.1.5
	 *
	 * @return string
	 */
	public function getOptionCacheCleanAction() {
		return $this->optionCacheCleanAction;
	}
}