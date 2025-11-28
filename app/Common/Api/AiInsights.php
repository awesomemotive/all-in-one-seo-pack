<?php
namespace AIOSEO\Plugin\Common\Api;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AIOSEO\Plugin\Common\Models;

/**
 * AI Insights related REST API endpoint callbacks.
 *
 * @since 4.9.1
 */
class AiInsights {
	/**
	 * Gets the API URL, checking for constant override.
	 *
	 * @since 4.9.1
	 *
	 * @return string The API URL.
	 */
	private static function getApiUrl( $path = '' ) {
		return trailingslashit( aioseo()->ai->getAiGeneratorApiUrl() . 'insights/' . $path );
	}

	/**
	 * Creates a new SEO report.
	 *
	 * @since 4.9.1
	 *
	 * @param  \WP_REST_Request  $request The REST Request.
	 * @return \WP_REST_Response          The response.
	 */
	public static function createReport( $request ) {
		$keyword = ! empty( $request['keyword'] ) ? sanitize_text_field( $request['keyword'] ) : '';

		if ( empty( $keyword ) ) {
			return new \WP_REST_Response( [
				'success' => false,
				'message' => 'Keyword is required.'
			], 400 );
		}

		$rateLimit = aioseo()->core->cache->get( 'ai_insights_rate_limit' );
		if ( ! empty( $rateLimit['reached'] ) && $rateLimit['reached'] ) {
			return new \WP_REST_Response( [
				'success' => false,
				'message' => ! empty( $rateLimit['message'] ) ? $rateLimit['message'] : 'Rate limit exceeded. Please try again later.',
				'code'    => 'rate_limit_exceeded'
			], 429 );
		}

		$response = aioseo()->helpers->wpRemotePost( self::getApiUrl( 'keyword-reports/' ), [
			'timeout' => 30,
			'headers' => Ai::getRequestHeaders(),
			'body'    => wp_json_encode( [
				'keyword' => $keyword
			] )
		] );

		if ( is_wp_error( $response ) ) {
			return new \WP_REST_Response( [
				'success' => false,
				'message' => $response->get_error_message()
			], $response->get_error_code() );
		}

		$responseCode = wp_remote_retrieve_response_code( $response );
		$responseBody = wp_remote_retrieve_body( $response );
		$decodedBody  = json_decode( $responseBody, true );

		// Check for rate limit (429 status code).
		if ( 429 === $responseCode ) {
			// Cache the rate limit status for 1 hour.
			$message = ! empty( $decodedBody['message'] ) ? $decodedBody['message'] : 'Rate limit exceeded. Please try again later.';
			aioseo()->core->cache->update( 'ai_insights_rate_limit', [
				'reached' => true,
				'message' => $message
			], HOUR_IN_SECONDS );

			return new \WP_REST_Response( [
				'success' => false,
				'message' => $message,
				'code'    => 'rate_limit_exceeded'
			], 429 );
		}

		if ( empty( $decodedBody['success'] ) || empty( $decodedBody['data']['report']['uuid'] ) ) {
			return new \WP_REST_Response( [
				'success' => false,
				'message' => 'Failed to create report.'
			], 500 );
		}

		$reportUuid = $decodedBody['data']['report']['uuid'];

		// Clear rate limit cache on successful request.
		aioseo()->core->cache->delete( 'ai_insights_rate_limit' );

		// Insert or update report in database.
		$report = Models\AiInsightsKeywordReport::getByUuid( $reportUuid );
		if ( ! $report->exists() ) {
			$report->uuid    = $reportUuid;
			$report->keyword = $keyword;
			$report->status  = 'pending';
			$report->save();
		}

		return new \WP_REST_Response( [
			'success' => true,
			'data'    => [
				'uuid' => $decodedBody['data']['report']['uuid']
			]
		], 200 );
	}

	/**
	 * Retrieves all reports.
	 *
	 * @since 4.9.1
	 *
	 * @param  \WP_REST_Request  $request The REST Request.
	 * @return \WP_REST_Response          The response.
	 */
	public static function getReports( $request ) {
		// Get pagination and ordering parameters from request (query params for GET).
		$orderBy    = $request->get_param( 'orderBy' ) ? sanitize_text_field( $request->get_param( 'orderBy' ) ) : 'created';
		$orderDir   = $request->get_param( 'orderDir' ) ? strtoupper( sanitize_text_field( $request->get_param( 'orderDir' ) ) ) : 'DESC';
		$limit      = $request->get_param( 'limit' ) ? intval( $request->get_param( 'limit' ) ) : 20;
		$offset     = $request->get_param( 'offset' ) ? intval( $request->get_param( 'offset' ) ) : 0;
		$searchTerm = $request->get_param( 'searchTerm' ) ? sanitize_text_field( $request->get_param( 'searchTerm' ) ) : '';
		$status     = $request->get_param( 'status' ) ? sanitize_text_field( $request->get_param( 'status' ) ) : 'all';

		// Validate order direction.
		if ( ! in_array( $orderDir, [ 'ASC', 'DESC' ], true ) ) {
			$orderDir = 'DESC';
		}

		// Validate order by field.
		$allowedOrderBy = [ 'created', 'updated', 'keyword', 'status' ];
		if ( ! in_array( $orderBy, $allowedOrderBy, true ) ) {
			$orderBy = 'created';
		}

		// Build query.
		$query = aioseo()->core->db->start( 'aioseo_ai_insights_keyword_reports' )
			->select( '*' );

		// Create separate query for total count.
		$totalQuery = aioseo()->core->db->noConflict()->start( 'aioseo_ai_insights_keyword_reports' );

		// Filter by status.
		if ( 'all' !== $status ) {
			$query->where( 'status', $status );
			$totalQuery->where( 'status', $status );
		}

		// Search by keyword.
		if ( ! empty( $searchTerm ) ) {
			$escapedSearchTerm = esc_sql( aioseo()->core->db->db->esc_like( $searchTerm ) );
			$query->whereRaw( "keyword LIKE '%{$escapedSearchTerm}%'" );
			$totalQuery->whereRaw( "keyword LIKE '%{$escapedSearchTerm}%'" );
		}

		// Get total count before pagination.
		$totalCount = $totalQuery->count();

		// Apply ordering and pagination.
		$reports = $query
			->orderBy( $orderBy . ' ' . $orderDir )
			->limit( $limit, $offset )
			->run()
			->models( 'AIOSEO\Plugin\Common\Models\AiInsightsKeywordReport' );

		// Convert models to arrays for JSON response.
		$reportsData = [];
		foreach ( $reports as $report ) {
			$reportsData[] = $report->jsonSerialize();
		}

		return new \WP_REST_Response( [
			'success' => true,
			'data'    => [
				'reports' => $reportsData,
				'total'   => $totalCount
			]
		], 200 );
	}

	/**
	 * Retrieves a specific report by UUID from local database.
	 * If status is pending or processing, checks API for updates.
	 *
	 * @since 4.9.1
	 *
	 * @param  \WP_REST_Request  $request The REST Request.
	 * @return \WP_REST_Response          The response.
	 */
	public static function getReport( $request ) {
		$uuid = ! empty( $request['uuid'] ) ? sanitize_text_field( $request['uuid'] ) : '';

		if ( empty( $uuid ) ) {
			return new \WP_REST_Response( [
				'success' => false,
				'message' => 'UUID is required.'
			], 400 );
		}

		// Get report from local database.
		$report = Models\AiInsightsKeywordReport::getByUuid( $uuid );

		if ( ! $report->exists() ) {
			return new \WP_REST_Response( [
				'success' => false,
				'message' => 'Report not found.'
			], 404 );
		}

		// If status is pending or processing, check API for updates.
		if ( in_array( $report->status, [ 'pending', 'processing' ], true ) ) {
			$url = self::getApiUrl( 'keyword-reports/' . sanitize_text_field( $uuid ) . '/' );

			$response = aioseo()->helpers->wpRemoteGet( $url, [
				'headers' => Ai::getRequestHeaders(),
				'timeout' => 30
			] );

			if ( is_wp_error( $response ) ) {
				return new \WP_REST_Response( [
					'success' => false,
					'message' => $response->get_error_message()
				], 500 );
			}

			$body = wp_remote_retrieve_body( $response );
			$data = json_decode( $body, true );

			if ( ! empty( $data['success'] ) && ! empty( $data['data']['report'] ) ) {
				$apiReport = $data['data']['report'];

				$report->status           = $apiReport['status'];
				$report->brands_mentioned = intval( $apiReport['brands_mentioned'] );
				$report->results          = $apiReport['results'];
				$report->brands           = $apiReport['brands'];
				$report->save();
			}
		}

		return new \WP_REST_Response( [
			'success' => true,
			'data'    => [
				'report' => $report->jsonSerialize()
			]
		], 200 );
	}

	/**
	 * Regenerates a report by creating a new one with the same keyword.
	 *
	 * @since 4.9.1
	 *
	 * @param  \WP_REST_Request  $request The REST Request.
	 * @return \WP_REST_Response          The response.
	 */
	public static function regenerateReport( $request ) {
		$uuid = ! empty( $request['uuid'] ) ? sanitize_text_field( $request['uuid'] ) : '';

		if ( empty( $uuid ) ) {
			return new \WP_REST_Response( [
				'success' => false,
				'message' => 'UUID is required.'
			], 400 );
		}

		// Get report from local database.
		$report = Models\AiInsightsKeywordReport::getByUuid( $uuid );

		if ( ! $report->exists() ) {
			return new \WP_REST_Response( [
				'success' => false,
				'message' => 'Report not found.'
			], 404 );
		}

		if ( empty( $report->keyword ) ) {
			return new \WP_REST_Response( [
				'success' => false,
				'message' => 'Report keyword is missing.'
			], 400 );
		}

		$rateLimit = aioseo()->core->cache->get( 'ai_insights_rate_limit' );
		if ( ! empty( $rateLimit['reached'] ) && $rateLimit['reached'] ) {
			return new \WP_REST_Response( [
				'success' => false,
				'message' => ! empty( $rateLimit['message'] ) ? $rateLimit['message'] : 'Rate limit exceeded. Please try again later.',
				'code'    => 'rate_limit_exceeded'
			], 429 );
		}

		$response = aioseo()->helpers->wpRemotePost( self::getApiUrl( 'keyword-reports/' ), [
			'timeout' => 30,
			'headers' => Ai::getRequestHeaders(),
			'body'    => wp_json_encode( [
				'keyword' => $report->keyword,
				'refresh' => true
			] )
		] );

		if ( is_wp_error( $response ) ) {
			return new \WP_REST_Response( [
				'success' => false,
				'message' => $response->get_error_message()
			], $response->get_error_code() );
		}

		$responseCode = wp_remote_retrieve_response_code( $response );
		$responseBody = wp_remote_retrieve_body( $response );
		$decodedBody  = json_decode( $responseBody, true );

		// Check for rate limit (429 status code).
		if ( 429 === $responseCode ) {
			// Cache the rate limit status for 1 hour.
			$message = ! empty( $decodedBody['message'] ) ? $decodedBody['message'] : 'Rate limit exceeded. Please try again later.';
			aioseo()->core->cache->update( 'ai_insights_rate_limit', [
				'reached' => true,
				'message' => $message
			], HOUR_IN_SECONDS );

			return new \WP_REST_Response( [
				'success' => false,
				'message' => $message,
				'code'    => 'rate_limit_exceeded'
			], 429 );
		}

		if ( empty( $decodedBody['success'] ) || empty( $decodedBody['data']['report']['uuid'] ) ) {
			return new \WP_REST_Response( [
				'success' => false,
				'message' => 'Failed to regenerate report.'
			], 500 );
		}

		$reportUuid = $decodedBody['data']['report']['uuid'];

		// Clear rate limit cache on successful request.
		aioseo()->core->cache->delete( 'ai_insights_rate_limit' );

		// Insert or update report in database.
		$newReport = Models\AiInsightsKeywordReport::getByUuid( $reportUuid );
		if ( ! $newReport->exists() ) {
			$newReport->uuid    = $reportUuid;
			$newReport->keyword = $report->keyword;
			$newReport->status  = 'pending';
			$newReport->save();
		}

		// Delete the old report.
		$report->delete();

		return new \WP_REST_Response( [
			'success' => true,
			'data'    => [
				'uuid' => $decodedBody['data']['report']['uuid']
			]
		], 200 );
	}

	/**
	 * Deletes a report.
	 *
	 * @since 4.9.1
	 *
	 * @param  \WP_REST_Request  $request The REST Request.
	 * @return \WP_REST_Response          The response.
	 */
	public static function deleteReport( $request ) {
		$uuid = ! empty( $request['uuid'] ) ? sanitize_text_field( $request['uuid'] ) : '';

		if ( empty( $uuid ) ) {
			return new \WP_REST_Response( [
				'success' => false,
				'message' => 'UUID is required.'
			], 400 );
		}

		// Get report from local database.
		$report = Models\AiInsightsKeywordReport::getByUuid( $uuid );

		if ( ! $report->exists() ) {
			return new \WP_REST_Response( [
				'success' => false,
				'message' => 'Report not found.'
			], 404 );
		}

		// Delete from local database.
		$report->delete();

		return new \WP_REST_Response( [
			'success' => true
		], 200 );
	}

	/**
	 * Subscribes an email to the brand tracker newsletter.
	 *
	 * @since 4.9.1
	 *
	 * @param  \WP_REST_Request  $request The REST Request.
	 * @return \WP_REST_Response          The response.
	 */
	public static function subscribeBrandTracker( $request ) {
		$email = ! empty( $request['email'] ) ? sanitize_email( $request['email'] ) : '';

		if ( empty( $email ) ) {
			return new \WP_REST_Response( [
				'success' => false,
				'message' => 'Email is required.'
			], 400 );
		}

		if ( ! is_email( $email ) ) {
			return new \WP_REST_Response( [
				'success' => false,
				'message' => 'Invalid email address.'
			], 400 );
		}

		// Get marketing site URL.
		$marketingSiteUrl = defined( 'AIOSEO_MARKETING_SITE_URL' ) && AIOSEO_MARKETING_SITE_URL
			? AIOSEO_MARKETING_SITE_URL
			: 'https://aioseo.com/';
		$marketingSiteUrl = trailingslashit( $marketingSiteUrl );

		// Construct the endpoint URL for the marketing site REST API.
		$endpointUrl = $marketingSiteUrl . 'wp-json/aioseo-site/v1/newsletter/subscribe';

		$response = aioseo()->helpers->wpRemotePost( $endpointUrl, [
			'timeout' => 30,
			'headers' => [
				'Content-Type' => 'application/json'
			],
			'body'    => wp_json_encode( [
				'email'  => $email,
				'source' => 'ai-insights-brand-tracker'
			] )
		] );

		if ( is_wp_error( $response ) ) {
			return new \WP_REST_Response( [
				'success' => false,
				'message' => $response->get_error_message()
			], $response->get_error_code() );
		}

		$responseCode = wp_remote_retrieve_response_code( $response );
		$responseBody = wp_remote_retrieve_body( $response );
		$decodedBody  = json_decode( $responseBody, true );

		if ( 200 !== $responseCode && 201 !== $responseCode ) {
			$message = ! empty( $decodedBody['message'] ) ? $decodedBody['message'] : 'Failed to subscribe to newsletter.';

			return new \WP_REST_Response( [
				'success' => false,
				'message' => $message
			], $responseCode );
		}

		return new \WP_REST_Response( [
			'success' => true,
			'message' => ! empty( $decodedBody['message'] ) ? $decodedBody['message'] : 'Successfully subscribed to newsletter.'
		], 200 );
	}
}