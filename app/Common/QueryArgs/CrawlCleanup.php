<?php
namespace AIOSEO\Plugin\Common\QueryArgs;

use AIOSEO\Plugin\Common\Models;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class to control Crawl Cleanup.
 *
 * @since 4.5.8
 */
class CrawlCleanup {

	/**
	 * Construct method.
	 *
	 * @since 4.5.8
	 */
	public function __construct() {
		// Add action to clear crawl cleanup logs.
		add_action( 'aioseo_crawl_cleanup_clear_logs', [ $this, 'clearLogs' ] );
	}

	/**
	 * Schedule clearing of the logs.
	 *
	 * @since 4.5.8
	 *
	 * @return void
	 */
	public function scheduleClearingLogs() {
		aioseo()->actionScheduler->unschedule( 'aioseo_crawl_cleanup_clear_logs' );
		$optionLength = json_decode( aioseo()->options->searchAppearance->advanced->blockArgs->logsRetention )->value;
		if (
			aioseo()->options->searchAppearance->advanced->blockArgs->enable &&
			'forever' !== $optionLength
		) {
			aioseo()->actionScheduler->scheduleRecurrent( 'aioseo_crawl_cleanup_clear_logs', 0, HOUR_IN_SECONDS );
		}
	}

	/**
	 * Clears the logs.
	 *
	 * @since 4.5.8
	 *
	 * @return void
	 */
	public function clearLogs() {
		$optionLength = json_decode( aioseo()->options->searchAppearance->advanced->blockArgs->logsRetention )->value;
		if ( 'forever' === $optionLength ) {
			return;
		}

		$date = gmdate( 'Y-m-d H:i:s', strtotime( '-1 ' . $optionLength ) );
		aioseo()->core->db
			->delete( 'aioseo_crawl_cleanup_logs' )
			->where( 'updated <', $date )
			->run();
	}

	/**
	 * Fetch Crawl Cleanup Logs.
	 *
	 * @since 4.5.8
	 *
	 * @param  \WP_REST_Request  $request The REST Request.
	 * @return \WP_REST_Response          The response.
	 */
	public static function fetchLogs( $request ) {
		$filter            = $request->get_param( 'filter' );
		$body              = $request->get_json_params();
		$orderByUnblocked  = ! empty( $body['orderBy'] ) ? sanitize_text_field( $body['orderBy'] ) : 'logs.updated';
		$orderByBlocked    = ! empty( $body['orderBy'] ) ? sanitize_text_field( $body['orderBy'] ) : 'b.id';
		$orderDir          = ! empty( $body['orderDir'] ) && ! empty( $body['orderBy'] ) ? strtoupper( sanitize_text_field( $body['orderDir'] ) ) : 'DESC';
		$limit             = ! empty( $body['limit'] ) ? intval( $body['limit'] ) : aioseo()->settings->tablePagination['queryArgs'];
		$offset            = ! empty( $body['offset'] ) ? intval( $body['offset'] ) : 0;
		$searchTerm        = ! empty( $body['searchTerm'] ) ? sanitize_text_field( $body['searchTerm'] ) : null;
		$keyValueSeparator = Models\CrawlCleanupBlockedArg::getKeyValueSeparator();
		$dateFormat        = get_option( 'date_format' );
		$timeFormat        = get_option( 'time_format' );
		$dateTimeFormat    = $dateFormat . ' ' . $timeFormat;

		// Query to get Arg Logs (unblocked) and the total.
		$queryUnblocked = aioseo()->core->db
			->start( 'aioseo_crawl_cleanup_logs as logs' )
			->select( ' logs.id,
						logs.slug,
						logs.key,
						logs.value,
						logs.hits,
						logs.updated' )
			->leftJoin( 'aioseo_crawl_cleanup_blocked_args as blocked',
				'blocked.key_value_hash = sha1(logs.key) OR
					blocked.key_value_hash = sha1(concat(logs.key, "' . $keyValueSeparator . '", logs.value))' )
			->limit( $limit, $offset );

		if ( ! empty( $searchTerm ) ) {
			// Apply escape to the search term.
			$searchTerm = esc_sql( aioseo()->core->db->db->esc_like( $searchTerm ) );
			$where = '
				(
					logs.slug LIKE \'%' . $searchTerm . '%\' OR
					logs.slug LIKE \'%' . str_replace( '%20', '-', $searchTerm ) . '%\' OR
					logs.slug LIKE \'%' . str_replace( '%20', '+', $searchTerm ) . '%\'
				)
			';

			$queryUnblocked->whereRaw( $where );
		}

		$queryUnblocked->where( 'blocked.id', null );
		$queryUnblocked->orderBy( "$orderByUnblocked $orderDir" );

		$rowsUnblocked = $queryUnblocked->run( false )->result();
		$totalUnblocked = $queryUnblocked->reset( [ 'limit' ] )->count();

		// Test logs (unblocked) to see if have some regex block.
		$regexMatches = [];
		foreach ( $rowsUnblocked as $unblocked ) {
			$blockedRegex = Models\CrawlCleanupBlockedArg::matchRegex( $unblocked->key, $unblocked->value );
			if ( $blockedRegex->exists() ) {
				$regexMatches[ $unblocked->id ] = $blockedRegex->regex;
			}
		}

		// Query to get Blocked Args and the total.
		$queryBlocked = aioseo()->core->db
			->select( ' b.id,
						b.key,
						b.value,
						b.regex,
						b.hits,
						b.updated' )
			->start( 'aioseo_crawl_cleanup_blocked_args as b' )
			->limit( $limit, $offset );

		if ( ! empty( $searchTerm ) ) {
			// Escape (esc_like) has already been applied.
			$searchTerms = [
				$searchTerm,
				str_replace( '%20', '-', $searchTerm ),
				str_replace( '%20', '+', $searchTerm )
			];

			$comparisons = [
				'b.key',
				'b.value',
				'b.regex',
				'CONCAT(b.key, \'' . $keyValueSeparator . '\', IF(b.value, b.value, \'*\'))'
			];

			$where = '';
			foreach ( $comparisons as $comparison ) {
				foreach ( $searchTerms as $s ) {
					if ( ! empty( $where ) ) {
						$where .= ' OR ';
					}

					$where .= aioseo()->db->db->prepare( " $comparison LIKE %s ", '%' . $s . '%' );
				}
			}

			$where = "( $where )";
			$queryBlocked->whereRaw( $where );
		}

		$queryBlocked->orderBy( "$orderByBlocked $orderDir" );

		$rowsBlocked = $queryBlocked->run( false )->result();
		$totalBlocked = $queryBlocked->reset( [ 'limit' ] )->count();

		switch ( $filter ) {
			case 'blocked':
				$total = $totalBlocked;
				$rows = $rowsBlocked;
				break;
			case 'unblocked':
				$total = $totalUnblocked;
				$rows = $rowsUnblocked;
				break;
			default:
				return new \WP_REST_Response( [
					'success' => false
				], 404 );
		}

		foreach ( $rows as $row ) {
			$row->updated = get_date_from_gmt( $row->updated, $dateTimeFormat );
		}

		return new \WP_REST_Response( [
			'success' => true,
			'rows'    => $rows,
			'regex'   => $regexMatches,
			'totals'  => [
				'total' => $total,
				'pages' => 0 === $total ? 1 : ceil( $total / $limit ),
				'page'  => 0 === $offset ? 1 : ( $offset / $limit ) + 1
			],
			'filters' => [
				[
					'slug'   => 'unblocked',
					'name'   => __( 'Unblocked', 'all-in-one-seo-pack' ),
					'count'  => $totalUnblocked,
					'active' => 'unblocked' === $filter
				],
				[
					'slug'   => 'blocked',
					'name'   => __( 'Blocked', 'all-in-one-seo-pack' ),
					'count'  => $totalBlocked,
					'active' => 'blocked' === $filter
				]
			]
		], 200 );
	}

	/**
	 * Set block Arg Query.
	 *
	 * @since 4.5.8
	 *
	 * @param  \WP_REST_Request  $request The REST Request.
	 * @return \WP_REST_Response          The response.
	 */
	public static function blockArg( $request ) {
		$body      = $request->get_json_params();
		$return    = true;
		$listSaved = [];
		$exists    = [];
		$error     = 0;

		try {
			foreach ( $body as $block ) {
				if ( $block ) {
					$blocked = Models\CrawlCleanupBlockedArg::getByKeyValue( $block['key'], $block['value'] );
					if ( ! $blocked->exists() && ! empty( $block['regex'] ) ) {
						$blocked = Models\CrawlCleanupBlockedArg::getByRegex( $block['regex'] );
					}

					if ( $blocked->exists() ) {
						$exists[] = [
							'key'   => $block['key'],
							'value' => $block['value']
						];

						$keyValue = sha1( Models\CrawlCleanupBlockedArg::getKeyValueString( $block['key'], $block['value'] ) );
						if ( ! in_array( $keyValue, $listSaved, true ) ) {
							$return = false;
							$error  = 1;
						}

						continue;
					}

					$blocked = new Models\CrawlCleanupBlockedArg();
					$blocked->set( $block );
					$blocked->save();

					$listSaved[] = $blocked->key_value_hash;
				}
			}
		} catch ( \Throwable $th ) {
			$return = false;
		}

		return new \WP_REST_Response( [
			'success' => $return,
			'error'   => $error,
			'exists'  => $exists
		], 200 );
	}

	/**
	 * Delete Blocked Arg.
	 *
	 * @since 4.5.8
	 *
	 * @param  \WP_REST_Request  $request The REST Request.
	 * @return \WP_REST_Response          The response.
	 */
	public static function deleteBlocked( $request ) {
		$body = $request->get_json_params();
		$return = true;

		try {
			foreach ( $body as $block ) {
				$blocked = new Models\CrawlCleanupBlockedArg( $block );
				if ( $blocked->exists() ) {
					$blocked->delete();
				}
			}
		} catch ( \Throwable $th ) {
			$return = false;
		}

		return new \WP_REST_Response( [
			'success' => $return
		], 200 );
	}

	/**
	 * Delete Log.
	 *
	 * @since 4.5.8
	 *
	 * @param  \WP_REST_Request  $request The REST Request.
	 * @return \WP_REST_Response          The response.
	 */
	public static function deleteLog( $request ) {
		$body = $request->get_json_params();
		$return = true;

		try {
			foreach ( $body as $block ) {
				$log = new Models\CrawlCleanupLog( $block );
				if ( $log->exists() ) {
					$log->delete();
				}
			}
		} catch ( \Throwable $th ) {
			$return = false;
		}

		return new \WP_REST_Response( [
			'success' => $return
		], 200 );
	}
}