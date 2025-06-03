<?php
namespace AIOSEO\Plugin\Common\Api;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AIOSEO\Plugin\Common\Models\SeoAnalyzerResult;

/**
 * Route class for the API.
 *
 * @since 4.0.0
 */
class Analyze {
	/**
	 * Analyzes the site for SEO.
	 *
	 * @since 4.0.0
	 *
	 * @param  \WP_REST_Request  $request The REST Request
	 * @return \WP_REST_Response          The response.
	 */
	public static function analyzeSite( $request ) {
		$body             = $request->get_json_params();
		$analyzeUrl       = ! empty( $body['url'] ) ? esc_url_raw( urldecode( $body['url'] ) ) : null;
		$refreshResults   = ! empty( $body['refresh'] ) ? (bool) $body['refresh'] : false;
		$analyzeOrHomeUrl = ! empty( $analyzeUrl ) ? $analyzeUrl : home_url();
		$responseCode     = null === aioseo()->core->cache->get( 'analyze_site_code' ) ? [] : aioseo()->core->cache->get( 'analyze_site_code' );
		$responseBody     = null === aioseo()->core->cache->get( 'analyze_site_body' ) ? [] : aioseo()->core->cache->get( 'analyze_site_body' );
		if (
			empty( $responseCode ) ||
			empty( $responseCode[ $analyzeOrHomeUrl ] ) ||
			empty( $responseBody ) ||
			empty( $responseBody[ $analyzeOrHomeUrl ] ) ||
			$refreshResults
		) {
			$token      = aioseo()->internalOptions->internal->siteAnalysis->connectToken;
			$url        = defined( 'AIOSEO_ANALYZE_URL' ) ? AIOSEO_ANALYZE_URL : 'https://analyze.aioseo.com';
			$response   = aioseo()->helpers->wpRemotePost( $url . '/v3/analyze/', [
				'timeout' => 60,
				'headers' => [
					'X-AIOSEO-Key' => $token,
					'Content-Type' => 'application/json'
				],
				'body'    => wp_json_encode( [
					'url' => $analyzeOrHomeUrl
				] ),
			] );

			$responseCode[ $analyzeOrHomeUrl ] = wp_remote_retrieve_response_code( $response );
			$responseBody[ $analyzeOrHomeUrl ] = json_decode( wp_remote_retrieve_body( $response ), true );

			aioseo()->core->cache->update( 'analyze_site_code', $responseCode, 10 * MINUTE_IN_SECONDS );
			aioseo()->core->cache->update( 'analyze_site_body', $responseBody, 10 * MINUTE_IN_SECONDS );
		}

		if ( 200 !== $responseCode[ $analyzeOrHomeUrl ] || empty( $responseBody[ $analyzeOrHomeUrl ]['success'] ) || ! empty( $responseBody[ $analyzeOrHomeUrl ]['error'] ) ) {
			if ( ! empty( $responseBody[ $analyzeOrHomeUrl ]['error'] ) && 'invalid-token' === $responseBody[ $analyzeOrHomeUrl ]['error'] ) {
				aioseo()->internalOptions->internal->siteAnalysis->reset();
			}

			return new \WP_REST_Response( [
				'success'  => false,
				'response' => $responseBody[ $analyzeOrHomeUrl ]
			], 400 );
		}

		if ( $analyzeUrl ) {
			$results = $responseBody[ $analyzeOrHomeUrl ]['results'];
			SeoAnalyzerResult::addResults( [
				'results' => $results,
				'score'   => $responseBody[ $analyzeOrHomeUrl ]['score']
			], $analyzeUrl );

			$result = SeoAnalyzerResult::getCompetitorsResults();

			return new \WP_REST_Response( $result, 200 );
		}

		$results = $responseBody[ $analyzeOrHomeUrl ]['results'];
		SeoAnalyzerResult::addResults( [
			'results' => $results,
			'score'   => $responseBody[ $analyzeOrHomeUrl ]['score']
		] );

		return new \WP_REST_Response( $responseBody[ $analyzeOrHomeUrl ], 200 );
	}

	/**
	 * Deletes the analyzed site for SEO.
	 *
	 * @since 4.0.0
	 *
	 * @param  \WP_REST_Request  $request The REST Request
	 * @return \WP_REST_Response          The response.
	 */
	public static function deleteSite( $request ) {
		$body       = $request->get_json_params();
		$analyzeUrl = ! empty( $body['url'] ) ? esc_url_raw( urldecode( $body['url'] ) ) : null;

		SeoAnalyzerResult::deleteByUrl( $analyzeUrl );

		$competitors = aioseo()->internalOptions->internal->siteAnalysis->competitors;

		unset( $competitors[ $analyzeUrl ] );

		// Reset the competitors.
		aioseo()->internalOptions->internal->siteAnalysis->competitors = $competitors;

		return new \WP_REST_Response( $competitors, 200 );
	}

	/**
	 * Analyzes the title for SEO.
	 *
	 * @since 4.1.2
	 *
	 * @param  \WP_REST_Request  $request The REST Request.
	 * @return \WP_REST_Response          The response.
	 */
	public static function analyzeHeadline( $request ) {
		$body                = $request->get_json_params();
		$headline            = ! empty( $body['headline'] ) ? sanitize_text_field( $body['headline'] ) : '';
		$shouldStoreHeadline = ! empty( $body['shouldStoreHeadline'] ) ? rest_sanitize_boolean( $body['shouldStoreHeadline'] ) : false;

		if ( empty( $headline ) ) {
			return new \WP_REST_Response( [
				'success' => false,
				'message' => __( 'Please enter a valid headline.', 'all-in-one-seo-pack' )
			], 400 );
		}

		$result = aioseo()->standalone->headlineAnalyzer->getResult( $headline );

		if ( ! $result['analysed'] ) {
			return new \WP_REST_Response( [
				'success' => false,
				'message' => $result['result']->msg
			], 400 );
		}

		$headlines = aioseo()->internalOptions->internal->headlineAnalysis->headlines;
		$headlines = array_reverse( $headlines, true );

		// Remove a headline from the list if it already exists.
		// This will ensure the new analysis is the first and open/highlighted.
		if ( array_key_exists( $headline, $headlines ) ) {
			unset( $headlines[ $headline ] );
		}

		$headlines[ $headline ] = wp_json_encode( $result );

		$headlines = array_reverse( $headlines, true );

		// Store the headlines with the latest one.
		if ( $shouldStoreHeadline ) {
			aioseo()->internalOptions->internal->headlineAnalysis->headlines = $headlines;
		}

		return new \WP_REST_Response( $headlines, 200 );
	}

	/**
	 * Deletes the analyzed Headline for SEO.
	 *
	 * @since 4.1.6
	 *
	 * @param  \WP_REST_Request  $request The REST Request.
	 * @return \WP_REST_Response          The response.
	 */
	public static function deleteHeadline( $request ) {
		$body     = $request->get_json_params();
		$headline = sanitize_text_field( $body['headline'] );

		$headlines = aioseo()->internalOptions->internal->headlineAnalysis->headlines;

		unset( $headlines[ $headline ] );

		// Reset the headlines.
		aioseo()->internalOptions->internal->headlineAnalysis->headlines = $headlines;

		return new \WP_REST_Response( $headlines, 200 );
	}

	/**
	 * Get Homepage results.
	 *
	 * @since 4.8.3
	 *
	 * @return \WP_REST_Response The response.
	 */
	public static function getHomeResults() {
		$results = SeoAnalyzerResult::getResults();

		return new \WP_REST_Response( [
			'success' => true,
			'result'  => $results,
		], 200 );
	}

	/**
	 * Get competitors results.
	 *
	 * @since 4.8.3
	 *
	 * @return \WP_REST_Response The response.
	 */
	public static function getCompetitorsResults() {
		$results = SeoAnalyzerResult::getCompetitorsResults();

		return new \WP_REST_Response( [
			'success' => true,
			'result'  => $results,
		], 200 );
	}
}