<?php
namespace AIOSEO\Plugin\Common\Api;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Route class for the API.
 *
 * @since 4.0.0
 */
class Connect {
	/**
	 * Get the connect URL.
	 *
	 * @since 4.0.0
	 *
	 * @param  \WP_REST_Request  $request The REST Request
	 * @return \WP_REST_Response          The response.
	 */
	public static function getConnectUrl( $request ) {
		$body    = $request->get_json_params();
		$key     = ! empty( $body['licenseKey'] ) ? sanitize_text_field( $body['licenseKey'] ) : null;
		$wizard  = ! empty( $body['wizard'] ) ? (bool) $body['wizard'] : false;
		$success = true;
		$urlData = aioseo()->admin->connect->generateConnectUrl( $key, $wizard ? admin_url( 'index.php?page=aioseo-setup-wizard#/success' ) : null );
		$url     = '';
		$message = '';

		if ( ! empty( $urlData['error'] ) ) {
			$success = false;
			$message = $urlData['error'];
		}

		$url = $urlData['url'];

		return new \WP_REST_Response( [
			'success' => $success,
			'url'     => $url,
			'message' => $message,
			'popup'   => ! isset( $urlData['popup'] ) ? true : $urlData['popup']
		], 200 );
	}

	/**
	 * Process the connection.
	 *
	 * @since 4.0.0
	 *
	 * @param  \WP_REST_Request  $request The REST Request
	 * @return \WP_REST_Response          The response.
	 */
	public static function processConnect( $request ) {
		$body        = $request->get_json_params();
		$wizard      = ! empty( $body['wizard'] ) ? sanitize_text_field( $body['wizard'] ) : null;
		$success     = true;

		if ( $wizard ) {
			aioseo()->internalOptions->internal->wizard = $wizard;
		}

		$response = aioseo()->admin->connect->process();
		if ( ! empty( $response['error'] ) ) {
			$message = $response['error'];
		} else {
			$message = $response['success'];
		}

		return new \WP_REST_Response( [
			'success' => $success,
			'message' => $message
		], 200 );
	}

	/**
	 * Saves the connect token.
	 *
	 * @since 4.0.0
	 *
	 * @param  \WP_REST_Request  $request The REST Request
	 * @return \WP_REST_Response          The response.
	 */
	public static function saveConnectToken( $request ) {
		$body    = $request->get_json_params();
		$token   = ! empty( $body['token'] ) ? sanitize_text_field( $body['token'] ) : null;
		$success = true;
		$message = 'token-saved';

		aioseo()->internalOptions->internal->siteAnalysis->connectToken = $token;

		return new \WP_REST_Response( [
			'success' => $success,
			'message' => $message
		], 200 );
	}
}