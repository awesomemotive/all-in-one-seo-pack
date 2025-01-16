<?php
namespace AIOSEO\Plugin\Common\WritingAssistant\SeoBoost;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Service class for SeoBoost.
 *
 * @since 4.7.4
 */
class Service {
	/**
	 * The base URL for the SeoBoost microservice.
	 *
	 * @since 4.7.4
	 *
	 * @var string
	 */
	private $baseUrl = 'https://app.seoboost.com/api/';

	/**
	 * Sends the keyword to be processed.
	 *
	 * @since 4.7.4
	 *
	 * @param  string          $keyword  The keyword.
	 * @param  string          $country  The country code.
	 * @param  string          $language The language code.
	 * @return array|\WP_Error           The response.
	 */
	public function processKeyword( $keyword, $country = 'US', $language = 'en' ) {
		if ( empty( $keyword ) || empty( $country ) || empty( $language ) ) {
			return new \WP_Error( 'service-error', __( 'Missing parameters', 'all-in-one-seo-pack' ) );
		}

		$reportRequest = $this->doRequest( 'waAddNewReport', [
			'params' => [
				'keyword'  => $keyword,
				'country'  => $country,
				'language' => $language
			]
		] );

		if ( is_wp_error( $reportRequest ) ) {
			return $reportRequest;
		}

		if ( empty( $reportRequest ) || empty( $reportRequest['status'] ) ) {
			return new \WP_Error( 'service-error', __( 'Empty response from service', 'all-in-one-seo-pack' ) );
		}

		if ( 'success' !== $reportRequest['status'] ) {
			return new \WP_Error( 'service-error', $reportRequest['msg'] );
		}

		return $reportRequest;
	}

	/**
	 * Sends a post content to be analyzed.
	 *
	 * @since 4.7.4
	 *
	 * @param  string          $title       The title.
	 * @param  string          $description The description.
	 * @param  string          $content     The content.
	 * @param  string          $reportSlug  The report slug.
	 * @return array|\WP_Error              The response.
	 */
	public function getContentAnalysis( $title, $description, $content, $reportSlug ) {
		return $this->doRequest( 'waAnalyzeContent', [
			'title'       => $title,
			'description' => $description,
			'content'     => $content,
			'slug'        => $reportSlug
		] );
	}

	/**
	 * Gets the progress for a keyword.
	 *
	 * @since 4.7.4
	 *
	 * @param  string          $uuid The uuid.
	 * @return array|\WP_Error       The progress.
	 */
	public function getProgressAndResult( $uuid ) {
		$response = $this->doRequest( 'waGetReport', [ 'slug' => $uuid ] );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		if ( empty( $response ) ) {
			return new \WP_Error( 'empty-progress-and-result', __( 'Empty progress and result.', 'all-in-one-seo-pack' ) );
		}

		return $response;
	}

	/**
	 * Gets the user options.
	 *
	 * @since 4.7.4
	 *
	 * @return array|\WP_Error The user options.
	 */
	public function getUserOptions() {
		return $this->doRequest( 'waGetUserOptions' );
	}

	/**
	 * Gets the user information.
	 *
	 * @since 4.7.4
	 *
	 * @return array|\WP_Error The user information.
	 */
	public function getUserInfo() {
		return $this->doRequest( 'waGetUserInfo' );
	}

	/**
	 * Gets the access token.
	 *
	 * @since 4.7.4
	 *
	 * @param  string          $authToken The auth token.
	 * @return array|\WP_Error            The response.
	 */
	public function getAccessToken( $authToken ) {
		return $this->doRequest( 'oauthaccess', [ 'token' => $authToken ] );
	}

	/**
	 * Refreshes the access token.
	 *
	 * @since 4.7.4
	 *
	 * @return bool Was the token refreshed?
	 */
	private function refreshAccessToken() {
		$newAccessToken = $this->doRequest( 'waRefreshAccessToken' );
		if (
			is_wp_error( $newAccessToken ) ||
			'success' !== $newAccessToken['status']
		) {
			aioseo()->writingAssistant->seoBoost->setAccessToken( '' );

			return false;
		}

		aioseo()->writingAssistant->seoBoost->setAccessToken( $newAccessToken['token'] );

		return true;
	}

	/**
	 * Sends a POST request to the microservice.
	 *
	 * @since 4.7.4
	 *
	 * @param  string          $path        The path.
	 * @param  array           $requestBody The request body.
	 * @return array|\WP_Error              Returns the response body or WP_Error if the request failed.
	 */
	private function doRequest( $path, $requestBody = [] ) {
		// Prevent API requests if no access token is present.
		if (
			'oauthaccess' !== $path && // Except if we're getting the access token.
			empty( aioseo()->writingAssistant->seoBoost->getAccessToken() )
		) {
			return new \WP_Error( 'service-error', __( 'Missing access token', 'all-in-one-seo-pack' ) );
		}

		$requestData = [
			'headers' => [
				'X-SeoBoost-Access-Token' => aioseo()->writingAssistant->seoBoost->getAccessToken(),
				'X-SeoBoost-Domain'       => aioseo()->helpers->getMultiSiteDomain(),
				'Content-Type'            => 'application/json'
			],
			'timeout' => 60,
			'method'  => 'GET'
		];

		if ( ! empty( $requestBody ) ) {
			$requestData['method'] = 'POST';
			$requestData['body']   = wp_json_encode( $requestBody );
		}

		$path         = trailingslashit( $this->getUrl() ) . trailingslashit( $path );
		$response     = wp_remote_request( $path, $requestData );
		$responseBody = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( ! $responseBody ) {
			$response = new \WP_Error( 'service-failed', __( 'Error in the SeoBoost service. Please contact support.', 'all-in-one-seo-pack' ) );
		}

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		// Refresh access token if expired and redo the request.
		if (
			isset( $responseBody['error'] ) &&
			'invalid-access-token' === $responseBody['error']
		) {
			if ( $this->refreshAccessToken() ) {
				return $this->doRequest( $path, $requestBody );
			}
		}

		return $responseBody;
	}

	/**
	 * Returns the URL for the Writing Assistant service.
	 *
	 * @since 4.7.4
	 *
	 * @return string The URL.
	 */
	public function getUrl() {
		$url = $this->baseUrl;
		if ( defined( 'AIOSEO_WRITING_ASSISTANT_SERVICE_URL' ) ) {
			$url = AIOSEO_WRITING_ASSISTANT_SERVICE_URL;
		}

		return $url;
	}

	/**
	 * Gets the report history.
	 *
	 * @since 4.7.4
	 *
	 * @return array|\WP_Error
	 */
	public function getReportHistory() {
		return $this->doRequest( 'waGetReportHistory' );
	}
}