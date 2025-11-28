<?php
namespace AIOSEO\Plugin\Common\Models;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The AiInsightsKeywordReport Model.
 *
 * @since 4.9.1
 *
 * @property string      $uuid             The report UUID.
 * @property string      $keyword          The keyword being analyzed.
 * @property string      $status           The report status (pending, processing, completed, etc.).
 * @property int         $brands_mentioned The number of brands mentioned.
 * @property array|null  $results          The report results.
 * @property array|null  $brands           The brands.
 */
class AiInsightsKeywordReport extends Model {
	/**
	 * The name of the table in the database, without the prefix.
	 *
	 * @since 4.9.1
	 *
	 * @var string
	 */
	protected $table = 'aioseo_ai_insights_keyword_reports';

	/**
	 * Fields that should be integer values.
	 *
	 * @since 4.9.1
	 *
	 * @var array
	 */
	protected $integerFields = [
		'id',
		'brands_mentioned'
	];

	/**
	 * Fields that should be encoded/decoded on save/get.
	 *
	 * @since 4.9.2
	 *
	 * @var array
	 */
	protected $jsonFields = [
		'results',
		'brands'
	];

	/**
	 * Fields that can be null when saved.
	 *
	 * @since 4.9.2
	 *
	 * @var array
	 */
	protected $nullFields = [
		'results',
		'brands'
	];

	/**
	 * Fields that should be hidden when serialized.
	 *
	 * @since 4.9.1
	 *
	 * @var array
	 */
	protected $hidden = [ 'id' ];

	/**
	 * Gets a report by UUID.
	 *
	 * @since 4.9.1
	 *
	 * @param  string $uuid The report UUID.
	 * @return AiInsightsKeywordReport The report object.
	 */
	public static function getByUuid( $uuid ) {
		$report = aioseo()->core->db->start( 'aioseo_ai_insights_keyword_reports' )
			->where( 'uuid', $uuid )
			->run()
			->model( 'AIOSEO\Plugin\Common\Models\AiInsightsKeywordReport' );

		return $report;
	}
}