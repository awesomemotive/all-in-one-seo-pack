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
	 * @since   4.2.5
	 * @version 5.0.2 Save only the keys the body sends; restore the blog and refresh the options.
	 *
	 * @param  \WP_REST_Request  $request The REST Request
	 * @return \WP_REST_Response The response.
	 */
	public static function saveNetworkRobots( $request ) {
		$isNetwork = 'network' === $request->get_param( 'siteId' );
		$siteId    = $isNetwork ? aioseo()->helpers->getNetworkId() : (int) $request->get_param( 'siteId' );
		$body      = $request->get_json_params();
		$body      = is_array( $body ) ? $body : [];

		// Ensure the user has access to the target site.
		if (
			$siteId &&
			is_multisite() &&
			(
				! is_user_member_of_blog( get_current_user_id(), $siteId ) &&
				! is_super_admin()
			)
		) {
			return new \WP_REST_Response( [
				'success' => false,
				'message' => 'You do not have permission to access this site.'
			], 403 );
		}

		// A key the body doesn't send has to keep the target site's stored value, so only pass on what it does send.
		$robots = [];
		if ( isset( $body['enabled'] ) ) {
			$robots['enable'] = boolval( $body['enabled'] );
		}

		if ( isset( $body['rules'] ) && is_array( $body['rules'] ) ) {
			// Sanitized by sanitizeAndSave(), which has to decode each rule value first.
			$robots['rules'] = $body['rules'];
		}

		$newOptions = [];

		// An empty group is not the same as an absent one: it resets that group on the target site to its defaults.
		if ( ! empty( $robots ) ) {
			$newOptions['tools'] = [ 'robots' => $robots ];
		}

		if ( ! empty( $body['searchAppearance'] ) && is_array( $body['searchAppearance'] ) ) {
			$newOptions['searchAppearance'] = $body['searchAppearance'];
		}

		if ( empty( $newOptions ) ) {
			return new \WP_REST_Response( [
				'success' => true
			], 200 );
		}

		aioseo()->helpers->switchToBlog( $siteId );

		try {
			$options = $isNetwork ? aioseo()->networkOptions : aioseo()->options;

			$options->sanitizeAndSave( $newOptions );
		} finally {
			aioseo()->options->refreshAfterRestore();
		}

		return new \WP_REST_Response( [
			'success' => true
		], 200 );
	}
}