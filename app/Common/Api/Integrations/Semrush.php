<?php
namespace AIOSEO\Plugin\Common\Api\Integrations;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AIOSEO\Plugin\Common\Integrations\Semrush as SemrushIntegration;

/**
 * Route class for the API.
 *
 * @since 4.0.16
 */
class Semrush {
	/**
	 * Fetches the additional keyphrases.
	 *
	 * @since 4.0.16
	 *
	 * @param  \WP_REST_Request  $request The REST Request
	 * @return \WP_REST_Response          The response.
	 */
	public static function semrushGetKeyphrases( $request ) {
		$body       = $request->get_json_params();
		$keyphrases = SemrushIntegration::getKeyphrases( $body['keyphrase'], $body['database'] );
		if ( false === $keyphrases ) {
			return new \WP_REST_Response( [
				'success' => false,
				'message' => 'Tokens expired and could not be refreshed.'
			], 400 );
		}

		return new \WP_REST_Response( [
			'success'    => true,
			'keyphrases' => $keyphrases
		], 200 );
	}

	/**
	 * Authenticates with Semrush.
	 *
	 * @since 4.0.16
	 *
	 * @param  \WP_REST_Request  $request The REST Request
	 * @return \WP_REST_Response          The response.
	 */
	public static function semrushAuthenticate( $request ) {
		$body = $request->get_json_params();

		if ( empty( $body['code'] ) ) {
			return new \WP_REST_Response( [
				'success' => false,
				'message' => 'Missing authorization code.'
			], 400 );
		}

		$success = SemrushIntegration::authenticate( $body['code'] );
		if ( ! $success ) {
			return new \WP_REST_Response( [
				'success' => false,
				'message' => 'Authentication failed.'
			], 400 );
		}

		return new \WP_REST_Response( [
			'success' => true,
			'semrush' => aioseo()->internalOptions->integrations->semrush->all()
		], 200 );
	}

	/**
	 * Refreshes the API tokens.
	 *
	 * @since 4.0.16
	 *
	 * @return \WP_REST_Response          The response.
	 */
	public static function semrushRefresh() {
		$success = SemrushIntegration::refreshTokens();
		if ( ! $success ) {
			return new \WP_REST_Response( [
				'success' => false,
				'message' => 'API tokens could not be refreshed.'
			], 400 );
		}

		return new \WP_REST_Response( [
			'success' => true,
			'semrush' => aioseo()->internalOptions->integrations->semrush->all()
		], 200 );
	}
}