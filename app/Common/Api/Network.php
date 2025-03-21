<?php
namespace AIOSEO\Plugin\Common\Api;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Route class for the API.
 *
 * @since 4.2.5
 */
class Network {
	/**
	 * Save network robots rules.
	 *
	 * @since 4.2.5
	 *
	 * @param  \WP_REST_Request  $request The REST Request
	 * @return \WP_REST_Response The response.
	 */
	public static function saveNetworkRobots( $request ) {
		$isNetwork        = 'network' === $request->get_param( 'siteId' );
		$siteId           = $isNetwork ? aioseo()->helpers->getNetworkId() : (int) $request->get_param( 'siteId' );
		$body             = $request->get_json_params();
		$rules            = ! empty( $body['rules'] ) ? array_map( 'sanitize_text_field', $body['rules'] ) : [];
		$enabled          = isset( $body['enabled'] ) ? boolval( $body['enabled'] ) : null;
		$searchAppearance = ! empty( $body['searchAppearance'] ) ? $body['searchAppearance'] : [];

		aioseo()->helpers->switchToBlog( $siteId );

		$options = $isNetwork ? aioseo()->networkOptions : aioseo()->options;
		$enabled = null === $enabled ? $options->tools->robots->enable : $enabled;

		$options->sanitizeAndSave( [
			'tools'            => [
				'robots' => [
					'enable' => $enabled,
					'rules'  => $rules
				]
			],
			'searchAppearance' => $searchAppearance
		] );

		return new \WP_REST_Response( [
			'success' => true
		], 200 );
	}
}