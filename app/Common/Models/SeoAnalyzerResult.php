<?php
namespace AIOSEO\Plugin\Common\Models;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The SeoAnalyzerResult Model.
 *
 * @since 4.8.3
 */
class SeoAnalyzerResult extends Model {
	/**
	 * The name of the table in the database, without the prefix.
	 *
	 * @since 4.8.3
	 *
	 * @var string
	 */
	protected $table = 'aioseo_seo_analyzer_results';

	/**
	 * Fields that should be json encoded on save and decoded on get.
	 *
	 * @since 4.8.3
	 *
	 * @var array
	 */
	protected $jsonFields = [
		'data'
	];

	/**
	 * Fields that should be hidden when serialized.
	 *
	 * @since 4.8.3
	 *
	 * @var array
	 */
	protected $hidden = [ 'id' ];

	/**
	 * Fields that can be null when saved.
	 *
	 * @since 4.8.3
	 *
	 * @var array
	 */
	protected $nullFields = [
		'competitor_url',
	];

	/**
	 * An array of columns from the DB that we can use.
	 *
	 * @since 4.8.3
	 *
	 * @var array
	 */
	protected $columns = [
		'id',
		'score',
		'data',
		'competitor_url',
		'created',
		'updated',
	];

	/**
	 * Returns all not competitors results.
	 *
	 * @since 4.8.3
	 *
	 * @return array List of results.
	 */
	public static function getResults() {
		$results = aioseo()->core->db->start( 'aioseo_seo_analyzer_results' )
			->select( '*' )
			->where( 'competitor_url', null )
			->run()
			->result();

		if ( empty( $results ) ) {
			return [];
		}

		return self::parseObjects( $results );
	}

	/**
	 * Returns all competitors results.
	 *
	 * @since 4.8.3
	 *
	 * @return array List of results.
	 */
	public static function getCompetitorsResults() {
		$results = aioseo()->core->db->start( 'aioseo_seo_analyzer_results' )
			->select( '*' )
			->whereRaw( 'competitor_url IS NOT NULL' )
			->orderBy( 'updated DESC' )
			->run()
			->result();

		if ( empty( $results ) ) {
			return [];
		}

		return self::parseObjects( $results, true );
	}

	/**
	 * Parse results to the front end format.
	 *
	 * @since 4.8.3
	 *
	 * @param  array $objects      List of objects.
	 * @param  bool  $isCompetitor Flag that indicates if is parsing a competitor or a homepage result.
	 * @return array               List of results.
	 */
	private static function parseObjects( $objects, $isCompetitor = false ) {
		$results = [];

		foreach ( $objects as $obj ) {
			$data = json_decode( $obj->data ?? '[]', true );

			if ( ! $isCompetitor ) {
				$results['score'] = $obj->score ?? 0;
			}

			foreach ( $data as $result ) {
				$metadata = $result['metadata'] ?? [];
				$item     = empty( $result['status'] ) && ! empty( $metadata['value'] ) ? $metadata['value'] : array_merge( $metadata, [ 'status' => $result['status'] ] );

				if ( $isCompetitor ) {
					if ( empty( $obj->competitor_url ) || empty( $result['group'] ) || empty( $result['name'] ) ) {
						continue;
					}

					$results[ $obj->competitor_url ]['results'][ $result['group'] ][ $result['name'] ] = $item;
					$results[ $obj->competitor_url ]['score'] = ! empty( $obj->score ) ? $obj->score : 0;
				} else {
					$results['results'][ $result['group'] ][ $result['name'] ] = $item;
				}
			}
		}

		return $results;
	}

	/**
	 * Delete results by competitor url, if null we are deleting the homepage results.
	 *
	 * @since 4.8.3
	 *
	 * @param  string $url The competitor url.
	 * @return void
	 */
	public static function deleteByUrl( $url ) {
		aioseo()->core->db
			->delete( 'aioseo_seo_analyzer_results' )
			->where( 'competitor_url', $url )
			->run();
	}

	/**
	 * Add multiple results at once.
	 *
	 * @since 4.8.3
	 *
	 * @return void
	 */
	public static function addResults( $results, $competitorUrl = null ) {
		if ( empty( $results['results'] ) ) {
			return;
		}

		// Delete the results for the competitor url if it exists.
		self::deleteByUrl( $competitorUrl );

		$data = [
			'competitor_url' => $competitorUrl,
			'score'          => $results['score'],
			'data'           => []
		];

		foreach ( $results['results'] as $group => $items ) {
			foreach ( $items as $name => $result ) {
				$fields = [
					'name'     => $name,
					'group'    => $group,
					'status'   => empty( $result['status'] ) ? null : $result['status'],
					'metadata' => null,
				];

				if ( ! is_array( $result ) ) {
					$fields['metadata'] = [ 'value' => $result ];
				} else {
					$metadata = [];
					foreach ( $result as $key => $value ) {
						if ( 'status' !== $key ) {
							$metadata[ $key ] = $value;
						}
					}

					if ( ! empty( $metadata ) ) {
						$fields['metadata'] = $metadata;
					}
				}

				$data['data'][] = $fields;
			}
		}

		$data['data'] = wp_json_encode( $data['data'] );
		$newResult = new SeoAnalyzerResult( $data );
		$newResult->save();
	}
}