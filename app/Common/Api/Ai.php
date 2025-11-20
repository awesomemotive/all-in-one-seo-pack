<?php
namespace AIOSEO\Plugin\Common\Api;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AIOSEO\Plugin\Common\Models;

/**
 * AI class for the API.
 *
 * @since 4.9.0
 */
class Ai {
	/**
	 * Generate content using AI.
	 *
	 * @since 4.9.0
	 *
	 * @param  \WP_REST_Request  $request The REST Request.
	 * @return \WP_REST_Response          The response.
	 */
	public static function generateContent( $request ) {
		$body     = $request->get_json_params();
		$prompt   = sanitize_textarea_field( $body['prompt'] ?? '' );
		$provider = sanitize_text_field( $body['provider'] ?? '' );
		$postId   = isset( $body['postId'] ) ? absint( $body['postId'] ) : null;

		if ( empty( $prompt ) ) {
			return new \WP_REST_Response( [
				'success' => false,
				'error'   => __( 'Prompt is required', 'all-in-one-seo-pack' ),
			], 400 );
		}

		$aiManager = new \AIOSEO\Plugin\Common\Ai\AiManager();

		if ( ! empty( $provider ) ) {
			$aiManager->setCurrentProvider( $provider );
		}

		$result = $aiManager->generateContent( $prompt, $body['options'] ?? [] );

		// Log the interaction
		if ( $result['success'] ) {
			$interaction              = new Models\AiInteraction();
			$interaction->action      = 'generate_content';
			$interaction->provider    = $aiManager->getCurrentProvider()->getName();
			$interaction->user_id     = get_current_user_id();
			$interaction->post_id     = $postId;
			$interaction->prompt      = $prompt;
			$interaction->data        = wp_json_encode( $body['options'] ?? [] );
			$interaction->response    = wp_json_encode( $result );
			$interaction->success     = 1;
			$interaction->tokens_used = $result['usage']['total_tokens'] ?? null;
			$interaction->created_at  = current_time( 'mysql' );
			$interaction->save();
		}

		return new \WP_REST_Response( $result, $result['success'] ? 200 : 400 );
	}

	/**
	 * Analyze SEO using AI.
	 *
	 * @since 4.9.0
	 *
	 * @param  \WP_REST_Request  $request The REST Request.
	 * @return \WP_REST_Response          The response.
	 */
	public static function analyzeSeo( $request ) {
		$body     = $request->get_json_params();
		$content  = wp_kses_post( $body['content'] ?? '' );
		$keyword  = sanitize_text_field( $body['keyword'] ?? '' );
		$provider = sanitize_text_field( $body['provider'] ?? '' );
		$postId   = isset( $body['postId'] ) ? absint( $body['postId'] ) : null;

		if ( empty( $content ) ) {
			return new \WP_REST_Response( [
				'success' => false,
				'error'   => __( 'Content is required', 'all-in-one-seo-pack' ),
			], 400 );
		}

		$aiManager = new \AIOSEO\Plugin\Common\Ai\AiManager();

		if ( ! empty( $provider ) ) {
			$aiManager->setCurrentProvider( $provider );
		}

		$result = $aiManager->analyzeSeo( $content, $keyword, $body['options'] ?? [] );

		// Log the interaction
		if ( $result['success'] ) {
			$interaction              = new Models\AiInteraction();
			$interaction->action      = 'analyze_seo';
			$interaction->provider    = $aiManager->getCurrentProvider()->getName();
			$interaction->user_id     = get_current_user_id();
			$interaction->post_id     = $postId;
			$interaction->prompt      = 'SEO Analysis for keyword: ' . $keyword;
			$interaction->data        = wp_json_encode( [ 'keyword' => $keyword, 'content_length' => strlen( $content ) ] );
			$interaction->response    = wp_json_encode( $result );
			$interaction->success     = 1;
			$interaction->tokens_used = $result['usage']['total_tokens'] ?? null;
			$interaction->created_at  = current_time( 'mysql' );
			$interaction->save();
		}

		return new \WP_REST_Response( $result, $result['success'] ? 200 : 400 );
	}

	/**
	 * Research keywords using AI.
	 *
	 * @since 4.9.0
	 *
	 * @param  \WP_REST_Request  $request The REST Request.
	 * @return \WP_REST_Response          The response.
	 */
	public static function researchKeywords( $request ) {
		$body     = $request->get_json_params();
		$topic    = sanitize_text_field( $body['topic'] ?? '' );
		$provider = sanitize_text_field( $body['provider'] ?? '' );
		$postId   = isset( $body['postId'] ) ? absint( $body['postId'] ) : null;

		if ( empty( $topic ) ) {
			return new \WP_REST_Response( [
				'success' => false,
				'error'   => __( 'Topic is required', 'all-in-one-seo-pack' ),
			], 400 );
		}

		$aiManager = new \AIOSEO\Plugin\Common\Ai\AiManager();

		if ( ! empty( $provider ) ) {
			$aiManager->setCurrentProvider( $provider );
		}

		$result = $aiManager->researchKeywords( $topic, $body['options'] ?? [] );

		// Log the interaction
		if ( $result['success'] ) {
			$interaction              = new Models\AiInteraction();
			$interaction->action      = 'research_keywords';
			$interaction->provider    = $aiManager->getCurrentProvider()->getName();
			$interaction->user_id     = get_current_user_id();
			$interaction->post_id     = $postId;
			$interaction->prompt      = 'Keyword research for: ' . $topic;
			$interaction->data        = wp_json_encode( [ 'topic' => $topic ] );
			$interaction->response    = wp_json_encode( $result );
			$interaction->success     = 1;
			$interaction->tokens_used = $result['usage']['total_tokens'] ?? null;
			$interaction->created_at  = current_time( 'mysql' );
			$interaction->save();
		}

		return new \WP_REST_Response( $result, $result['success'] ? 200 : 400 );
	}

	/**
	 * Optimize content for AEO using AI.
	 *
	 * @since 4.9.0
	 *
	 * @param  \WP_REST_Request  $request The REST Request.
	 * @return \WP_REST_Response          The response.
	 */
	public static function optimizeAeo( $request ) {
		$body      = $request->get_json_params();
		$content   = wp_kses_post( $body['content'] ?? '' );
		$questions = array_map( 'sanitize_text_field', $body['questions'] ?? [] );
		$provider  = sanitize_text_field( $body['provider'] ?? '' );
		$postId    = isset( $body['postId'] ) ? absint( $body['postId'] ) : null;

		if ( empty( $content ) ) {
			return new \WP_REST_Response( [
				'success' => false,
				'error'   => __( 'Content is required', 'all-in-one-seo-pack' ),
			], 400 );
		}

		$aiManager = new \AIOSEO\Plugin\Common\Ai\AiManager();

		if ( ! empty( $provider ) ) {
			$aiManager->setCurrentProvider( $provider );
		}

		$result = $aiManager->optimizeForAeo( $content, $questions, $body['options'] ?? [] );

		// Log the interaction
		if ( $result['success'] ) {
			$interaction              = new Models\AiInteraction();
			$interaction->action      = 'optimize_aeo';
			$interaction->provider    = $aiManager->getCurrentProvider()->getName();
			$interaction->user_id     = get_current_user_id();
			$interaction->post_id     = $postId;
			$interaction->prompt      = 'AEO optimization with ' . count( $questions ) . ' questions';
			$interaction->data        = wp_json_encode( [ 'questions' => $questions, 'content_length' => strlen( $content ) ] );
			$interaction->response    = wp_json_encode( $result );
			$interaction->success     = 1;
			$interaction->tokens_used = $result['usage']['total_tokens'] ?? null;
			$interaction->created_at  = current_time( 'mysql' );
			$interaction->save();
		}

		return new \WP_REST_Response( $result, $result['success'] ? 200 : 400 );
	}

	/**
	 * Get available AI providers.
	 *
	 * @since 4.9.0
	 *
	 * @param  \WP_REST_Request  $request The REST Request.
	 * @return \WP_REST_Response          The response.
	 */
	public static function getProviders( $request ) {
		$aiManager = new \AIOSEO\Plugin\Common\Ai\AiManager();
		$providers = $aiManager->getAvailableProviders();

		$providerList = [];
		foreach ( $providers as $key => $provider ) {
			$providerList[] = [
				'key'  => $key,
				'name' => $provider->getName(),
			];
		}

		return new \WP_REST_Response( [
			'success'   => true,
			'providers' => $providerList,
			'current'   => $aiManager->getCurrentProvider() ? $aiManager->getCurrentProvider()->getName() : null,
		], 200 );
	}

	/**
	 * Get AI interaction history.
	 *
	 * @since 4.9.0
	 *
	 * @param  \WP_REST_Request  $request The REST Request.
	 * @return \WP_REST_Response          The response.
	 */
	public static function getHistory( $request ) {
		global $wpdb;

		$postId = $request->get_param( 'postId' );
		$limit  = absint( $request->get_param( 'limit' ) ?? 50 );
		$offset = absint( $request->get_param( 'offset' ) ?? 0 );

		$table = $wpdb->prefix . 'aioseo_ai_interactions';

		$where = '';
		if ( ! empty( $postId ) ) {
			$where = $wpdb->prepare( 'WHERE post_id = %d', absint( $postId ) );
		}

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$results = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$table} {$where} ORDER BY created_at DESC LIMIT %d OFFSET %d",
				$limit,
				$offset
			)
		);

		return new \WP_REST_Response( [
			'success' => true,
			'history' => $results,
		], 200 );
	}

	/**
	 * Validate API key for a provider.
	 *
	 * @since 4.9.0
	 *
	 * @param  \WP_REST_Request  $request The REST Request.
	 * @return \WP_REST_Response          The response.
	 */
	public static function validateApiKey( $request ) {
		$body     = $request->get_json_params();
		$provider = sanitize_text_field( $body['provider'] ?? '' );
		$apiKey   = sanitize_text_field( $body['apiKey'] ?? '' );

		if ( empty( $provider ) || empty( $apiKey ) ) {
			return new \WP_REST_Response( [
				'success' => false,
				'error'   => __( 'Provider and API key are required', 'all-in-one-seo-pack' ),
			], 400 );
		}

		$providerClass = null;
		switch ( strtolower( $provider ) ) {
			case 'openai':
				$providerClass = new \AIOSEO\Plugin\Common\Ai\OpenAi( $apiKey );
				break;
			case 'claude':
				$providerClass = new \AIOSEO\Plugin\Common\Ai\Claude( $apiKey );
				break;
			case 'gemini':
				$providerClass = new \AIOSEO\Plugin\Common\Ai\Gemini( $apiKey );
				break;
			default:
				return new \WP_REST_Response( [
					'success' => false,
					'error'   => __( 'Invalid provider', 'all-in-one-seo-pack' ),
				], 400 );
		}

		// Test with a simple prompt
		$result = $providerClass->generateContent( 'Say "test"' );

		return new \WP_REST_Response( [
			'success' => $result['success'],
			'valid'   => $result['success'],
			'error'   => $result['error'] ?? null,
		], 200 );
	}
}
