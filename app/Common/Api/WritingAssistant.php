<?php
namespace AIOSEO\Plugin\Common\Api;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AIOSEO\Plugin\Common\Models;

/**
 * WritingAssistant class for the API.
 *
 * @since 4.7.4
 */
class WritingAssistant {
	/**
	 * Process the keyword.
	 *
	 * @since 4.7.4
	 *
	 * @param  \WP_REST_Request  $request The REST Request
	 * @return \WP_REST_Response          The response.
	 */
	public static function processKeyword( $request ) {
		$body        = $request->get_json_params();
		$postId      = absint( $body['postId'] );
		$keywordText = sanitize_text_field( $body['keyword'] );
		$country     = sanitize_text_field( $body['country'] );
		$language    = sanitize_text_field( strtolower( $body['language'] ) );

		if ( empty( $keywordText ) || empty( $country ) || empty( $language ) ) {
			return new \WP_REST_Response( [
				'success' => false,
				'error'   => __( 'Missing data to generate a report', 'all-in-one-seo-pack' )
			] );
		}

		$keyword              = Models\WritingAssistantKeyword::getKeyword( $keywordText, $country, $language );
		$writingAssistantPost = Models\WritingAssistantPost::getPost( $postId );
		if ( $keyword->exists() ) {
			$writingAssistantPost->attachKeyword( $keyword->id );

			// Returning early will let the UI code start polling the keyword.
			return new \WP_REST_Response( [
				'success'  => true,
				'progress' => $keyword->progress
			], 200 );
		}

		// Start a new keyword process.
		$processResult = aioseo()->writingAssistant->seoBoost->service->processKeyword( $keywordText, $country, $language );
		if ( is_wp_error( $processResult ) ) {
			return new \WP_REST_Response( [
				'success' => false,
				'error'   => $processResult->get_error_message()
			] );
		}

		// Store the new keyword.
		$keyword->uuid     = $processResult['slug'];
		$keyword->progress = 0;
		$keyword->save();

		// Update the writing assistant post with the current keyword.
		$writingAssistantPost->attachKeyword( $keyword->id );

		return new \WP_REST_Response( [ 'success' => true ], 200 );
	}

	/**
	 * Get current keyword for a Post.
	 *
	 * @since 4.7.4
	 *
	 * @param  \WP_REST_Request  $request The REST Request
	 * @return \WP_REST_Response          The response.
	 */
	public static function getPostKeyword( $request ) {
		$postId = $request->get_param( 'postId' );

		if ( empty( $postId ) ) {
			return new \WP_REST_Response( [
				'success' => false,
				'message' => __( 'Empty Post ID', 'all-in-one-seo-pack' )
			], 404 );
		}

		$keyword = Models\WritingAssistantPost::getKeyword( $postId );
		if ( $keyword && 100 !== $keyword->progress ) {
			// Update progress.
			$newProgress = aioseo()->writingAssistant->seoBoost->service->getProgressAndResult( $keyword->uuid );
			if ( is_wp_error( $newProgress ) ) {
				return new \WP_REST_Response( [
					'success' => false,
					'error'   => $newProgress->get_error_message()
				], 200 );
			}

			if ( 'success' !== $newProgress['status'] ) {
				return new \WP_REST_Response( [
					'success' => false,
					'error'   => $newProgress['msg']
				], 200 );
			}

			$keyword->progress = ! empty( $newProgress['report']['progress'] ) ? $newProgress['report']['progress'] : $keyword->progress;

			if ( ! empty( $newProgress['report']['keywords'] ) ) {
				$keyword->keywords = $newProgress['report']['keywords'];
			}

			if ( ! empty( $newProgress['report']['competitors'] ) ) {
				$keyword->competitors = [
					'competitors' => $newProgress['report']['competitors'],
					'summary'     => $newProgress['report']['competitors_summary']
				];
			}

			$keyword->save();
		}

		// Return a refreshed keyword here because we need some parsed data.
		$keyword = Models\WritingAssistantPost::getKeyword( $postId );

		return new \WP_REST_Response( $keyword, 200 );
	}

	/**
	 * Get the content analysis for a post.
	 *
	 * @since 4.7.4
	 *
	 * @param  \WP_REST_Request  $request The REST Request
	 * @return \WP_REST_Response          The response.
	 */
	public static function getContentAnalysis( $request ) {
		$title       = $request->get_param( 'title' );
		$description = $request->get_param( 'description' );
		$content     = apply_filters( 'the_content', $request->get_param( 'content' ) );
		$postId      = $request->get_param( 'postId' );
		if ( empty( $content ) || empty( $postId ) ) {
			return new \WP_REST_Response( [
				'success' => false,
				'message' => __( 'Empty Content or Post ID', 'all-in-one-seo-pack' )
			], 200 );
		}

		$keyword = Models\WritingAssistantPost::getKeyword( $postId );
		if (
			! $keyword ||
			! $keyword->exists() ||
			100 !== $keyword->progress
		) {
			return new \WP_REST_Response( [
				'success' => false,
				'error'   => __( 'Keyword not found or not ready', 'all-in-one-seo-pack' )
			], 200 );
		}

		$writingAssistantPost = Models\WritingAssistantPost::getPost( $postId );

		// Make sure we're not analysing the same content again.
		$contentHash = sha1( $content );
		if (
			! empty( $writingAssistantPost->content_analysis ) &&
			$writingAssistantPost->content_analysis_hash === $contentHash
		) {
			return new \WP_REST_Response( $writingAssistantPost->content_analysis, 200 );
		}

		// Call SEOBoost service to get the content analysis.
		$contentAnalysis = aioseo()->writingAssistant->seoBoost->service->getContentAnalysis( $title, $description, $content, $keyword->uuid );
		if ( is_wp_error( $contentAnalysis ) ) {
			return new \WP_REST_Response( [
				'success' => false,
				'error'   => $contentAnalysis->get_error_message()
			], 200 );
		}

		// Update the post with the content analysis.
		$writingAssistantPost->content_analysis      = $contentAnalysis['result'];
		$writingAssistantPost->content_analysis_hash = $contentHash;
		$writingAssistantPost->save();

		return new \WP_REST_Response( $contentAnalysis['result'], 200 );
	}

	/**
	 * Get the user info.
	 *
	 * @since 4.7.4
	 *
	 * @return \WP_REST_Response The response.
	 */
	public static function getUserInfo() {
		$userInfo = aioseo()->writingAssistant->seoBoost->service->getUserInfo();
		if ( is_wp_error( $userInfo ) ) {
			return new \WP_REST_Response( [
				'success' => false,
				'error'   => $userInfo->get_error_message()
			], 200 );
		}

		if ( empty( $userInfo['status'] ) ) {
			return new \WP_REST_Response( [
				'success' => false,
				'error'   => __( 'Empty response from service', 'all-in-one-seo-pack' )
			], 200 );
		}

		if ( 'success' !== $userInfo['status'] ) {
			return new \WP_REST_Response( [
				'success' => false,
				'error'   => $userInfo['msg']
			], 200 );
		}

		return new \WP_REST_Response( $userInfo, 200 );
	}

	/**
	 * Get the user info.
	 *
	 * @since 4.7.4
	 *
	 * @return \WP_REST_Response The response.
	 */
	public static function getUserOptions() {
		$userOptions = aioseo()->writingAssistant->seoBoost->getUserOptions();
		if ( empty( $userOptions ) ) {
			aioseo()->writingAssistant->seoBoost->refreshUserOptions();
		}

		return new \WP_REST_Response( $userOptions, 200 );
	}

	/**
	 * Get the report history.
	 *
	 * @since 4.7.4
	 *
	 * @return \WP_REST_Response The response.
	 */
	public static function getReportHistory() {
		$reportHistory = aioseo()->writingAssistant->seoBoost->getReportHistory();

		if ( is_wp_error( $reportHistory ) ) {
			return new \WP_REST_Response( [
				'success' => false,
				'error'   => $reportHistory->get_error_message()
			], 200 );
		}

		return new \WP_REST_Response( $reportHistory, 200 );
	}

	/**
	 * Disconnect the user.
	 *
	 * @since 4.7.4
	 *
	 * @return \WP_REST_Response The response.
	 */
	public static function disconnect() {
		delete_user_meta( get_current_user_id(), 'seoboost_access_token' );

		return new \WP_REST_Response( [ 'success' => true ], 200 );
	}

	/**
	 * Save user options.
	 *
	 * @since 4.7.4
	 *
	 * @param  \WP_REST_Request  $request The REST Request
	 * @return \WP_REST_Response          The response.
	 */
	public static function saveUserOptions( $request ) {
		$body = $request->get_json_params();

		$userOptions = [
			'country'  => $body['country'],
			'language' => $body['language'],
		];

		aioseo()->writingAssistant->seoBoost->setUserOptions( $userOptions );

		return new \WP_REST_Response( [ 'success' => true ], 200 );
	}

	/**
	 * Set the report progress.
	 *
	 * @since 4.7.4
	 *
	 * @param  \WP_REST_Request  $request The REST Request
	 * @return \WP_REST_Response          The response.
	 */
	public static function setReportProgress( $request ) {
		$body              = $request->get_json_params();
		$keyword           = Models\WritingAssistantPost::getKeyword( (int) $body['postId'] );
		$keyword->progress = (int) $body['progress'];
		$keyword->save();

		return new \WP_REST_Response( [ 'success' => true ], 200 );
	}
}