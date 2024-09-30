<?php

namespace AIOSEO\Plugin\Common\Api;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Email Summary related REST API endpoint callbacks.
 *
 * @since 4.7.2
 */
class EmailSummary {
	/**
	 * Sends a summary.
	 *
	 * @since 4.7.2
	 *
	 * @param  \WP_REST_Request  $request The REST Request
	 * @return \WP_REST_Response          The response.
	 */
	public static function send( $request ) {
		try {
			$body = $request->get_json_params();

			$to        = $body['to'] ?? '';
			$frequency = $body['frequency'] ?? '';
			if ( $to && $frequency ) {
				aioseo()->emailReports->summary->run( [
					'recipient' => $to,
					'frequency' => $frequency,
				] );
			}

			return new \WP_REST_Response( [
				'success' => true,
			], 200 );
		} catch ( \Exception $e ) {
			return new \WP_REST_Response( [
				'success' => false,
				'message' => $e->getMessage()
			], 200 );
		}
	}
}