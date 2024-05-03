<?php
namespace AIOSEO\Plugin\Common\Api;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AIOSEO\Plugin\Common\SearchStatistics\Api;

/**
 * Route class for the API.
 *
 * @since   4.3.0
 * @version 4.6.2 Moved from Pro to Common.
 */
class SearchStatistics {
	/**
	 * Get the authorize URL.
	 *
	 * @since 4.3.0
	 *
	 * @param  \WP_REST_Request  $request The REST Request
	 * @return \WP_REST_Response          The response.
	 */
	public static function getAuthUrl( $request ) {
		$body = $request->get_params();

		if ( aioseo()->searchStatistics->api->auth->isConnected() ) {
			return new \WP_REST_Response( [
				'success' => false,
				'message' => 'Cannot authenticate. Please re-authenticate.'
			], 200 );
		}

		$returnTo = ! empty( $body['returnTo'] ) ? sanitize_key( $body['returnTo'] ) : '';
		$url      = add_query_arg( [
			'tt'      => aioseo()->searchStatistics->api->trustToken->get(),
			'sitei'   => aioseo()->searchStatistics->api->getSiteIdentifier(),
			'version' => aioseo()->version,
			'ajaxurl' => admin_url( 'admin-ajax.php' ),
			'siteurl' => site_url(),
			'return'  => urlencode( admin_url( 'admin.php?page=aioseo&return-to=' . $returnTo ) ),
			'testurl' => 'https://' . aioseo()->searchStatistics->api->getApiUrl() . '/v1/test/'
		], 'https://' . aioseo()->searchStatistics->api->getApiUrl() . '/v1/auth/new/' . aioseo()->searchStatistics->api->auth->type . '/' );

		$url = apply_filters( 'aioseo_search_statistics_auth_url', $url );

		return new \WP_REST_Response( [
			'success' => true,
			'url'     => $url,
		], 200 );
	}

	/**
	 * Get the reauthorize URL.
	 *
	 * @since 4.3.0
	 *
	 * @param  \WP_REST_Request  $request The REST Request
	 * @return \WP_REST_Response          The response.
	 */
	public static function getReauthUrl( $request ) {
		$body = $request->get_params();

		if ( ! aioseo()->searchStatistics->api->auth->isConnected() ) {
			return new \WP_REST_Response( [
				'success' => false,
				'message' => 'Cannot re-authenticate. Please authenticate.',
			], 200 );
		}

		$returnTo = ! empty( $body['returnTo'] ) ? sanitize_key( $body['returnTo'] ) : '';
		$url      = add_query_arg( [
			'tt'      => aioseo()->searchStatistics->api->trustToken->get(),
			'sitei'   => aioseo()->searchStatistics->api->getSiteIdentifier(),
			'version' => aioseo()->version,
			'ajaxurl' => admin_url( 'admin-ajax.php' ),
			'siteurl' => site_url(),
			'key'     => aioseo()->searchStatistics->api->auth->getKey(),
			'token'   => aioseo()->searchStatistics->api->auth->getToken(),
			'return'  => urlencode( admin_url( 'admin.php?page=aioseo&return-to=' . $returnTo ) ),
			'testurl' => 'https://' . aioseo()->searchStatistics->api->getApiUrl() . '/v1/test/'
		], 'https://' . aioseo()->searchStatistics->api->getApiUrl() . '/v1/auth/reauth/' . aioseo()->searchStatistics->api->auth->type . '/' );

		$url = apply_filters( 'aioseo_search_statistics_reauth_url', $url );

		return new \WP_REST_Response( [
			'success' => true,
			'url'     => $url,
		], 200 );
	}

	/**
	 * Delete the authorization.
	 *
	 * @since 4.3.0
	 *
	 * @param  \WP_REST_Request  $request The REST Request
	 * @return \WP_REST_Response          The response.
	 */
	public static function deleteAuth( $request ) {
		$body = $request->get_json_params();

		if ( ! aioseo()->searchStatistics->api->auth->isConnected() ) {
			return new \WP_REST_Response( [
				'success' => false,
				'message' => 'Cannot deauthenticate. You are not currently authenticated.'
			], 200 );
		}

		$force   = ! empty( $body['force'] ) && true === $body['force'];
		$deleted = aioseo()->searchStatistics->api->auth->delete( $force );

		if ( $deleted || $force ) {
			aioseo()->searchStatistics->cancelActions();

			return new \WP_REST_Response( [
				'success' => true,
				'message' => 'Successfully deauthenticated.'
			], 200 );
		}

		return new \WP_REST_Response( [
			'success' => false,
			'message' => 'Could not deauthenticate, please try again.'
		], 200 );
	}

	/**
	 * Deletes a sitemap.
	 *
	 * @since 4.6.2
	 *
	 * @param  \WP_REST_Request  $request The REST Request
	 * @return \WP_REST_Response          The response.
	 */
	public static function deleteSitemap( $request ) {
		$body    = $request->get_json_params();
		$sitemap = ! empty( $body['sitemap'] ) ? $body['sitemap'] : '';

		if ( empty( $sitemap ) ) {
			return new \WP_REST_Response( [
				'success' => false,
				'message' => 'No sitemap provided.'
			], 200 );
		}

		$args = [
			'sitemap' => $sitemap
		];

		$api      = new Api\Request( 'google-search-console/sitemap/delete/', $args, 'POST' );
		$response = $api->request();

		if ( is_wp_error( $response ) ) {
			return new \WP_REST_Response( [
				'success' => false,
				'message' => $response['message']
			], 200 );
		}

		aioseo()->internalOptions->searchStatistics->sitemap->list      = $response['data'];
		aioseo()->internalOptions->searchStatistics->sitemap->lastFetch = time();

		return new \WP_REST_Response( [
			'success' => true,
			'data'    => [
				'internalOptions'    => aioseo()->internalOptions->searchStatistics->sitemap->all(),
				'sitemapsWithErrors' => aioseo()->searchStatistics->sitemap->getSitemapsWithErrors()
			]
		], 200 );
	}

	/**
	 * Ignores a sitemap.
	 *
	 * @since 4.6.2
	 *
	 * @param  \WP_REST_Request  $request The REST Request
	 * @return \WP_REST_Response          The response.
	 */
	public static function ignoreSitemap( $request ) {
		$body    = $request->get_json_params();
		$sitemap = ! empty( $body['sitemap'] ) ? $body['sitemap'] : '';

		if ( empty( $sitemap ) ) {
			return new \WP_REST_Response( [
				'success' => false,
				'message' => 'No sitemap provided.'
			], 200 );
		}

		$ignoredSitemaps = aioseo()->internalOptions->searchStatistics->sitemap->ignored;
		if ( is_array( $sitemap ) ) {
			$ignoredSitemaps = array_merge( $ignoredSitemaps, $sitemap );
		} else {
			$ignoredSitemaps[] = $sitemap;
		}

		$ignoredSitemaps = array_unique( $ignoredSitemaps ); // Remove duplicates.
		$ignoredSitemaps = array_filter( $ignoredSitemaps ); // Remove empty values.

		aioseo()->internalOptions->searchStatistics->sitemap->ignored = $ignoredSitemaps;

		return new \WP_REST_Response( [
			'success' => true,
			'data'    => [
				'internalOptions'    => aioseo()->internalOptions->searchStatistics->sitemap->all(),
				'sitemapsWithErrors' => aioseo()->searchStatistics->sitemap->getSitemapsWithErrors()
			]
		], 200 );
	}
}