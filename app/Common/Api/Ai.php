<?php
namespace AIOSEO\Plugin\Common\Api;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AIOSEO\Plugin\Common\Models;

/**
 * AI route class for the API.
 *
 * @since 4.8.4
 */
class Ai {
	/**
	 * The AI Generator API URL.
	 *
	 * @since 4.8.4
	 *
	 * @var string
	 */
	private static $aiGeneratorApiUrl = 'https://ai-generator.aioseo.com/v1/';

	/**
	 * Stores the access token.
	 *
	 * @since 4.8.4
	 *
	 * @param  \WP_REST_Request  $request The REST Request
	 * @return \WP_REST_Response          The response.
	 */
	public static function storeAccessToken( $request ) {
		$body        = $request->get_json_params();
		$accessToken = sanitize_text_field( $body['accessToken'] );
		if ( ! $accessToken ) {
			return new \WP_REST_Response( [
				'success' => false,
				'message' => 'Missing access token.'
			], 400 );
		}

		aioseo()->internalOptions->internal->ai->accessToken        = $accessToken;
		aioseo()->internalOptions->internal->ai->isTrialAccessToken = false;

		aioseo()->ai->updateCredits( true );

		return new \WP_REST_Response( [
			'success'   => true,
			'aiOptions' => aioseo()->internalOptions->internal->ai->all()
		], 200 );
	}

	/**
	 * Generates title suggestions based on the provided content and options.
	 *
	 * @since 4.8.4
	 *
	 * @param  \WP_REST_Request  $request The REST Request
	 * @return \WP_REST_Response          The response.
	 */
	public static function generateTitles( $request ) {
		$body         = $request->get_json_params();
		$postId       = ! empty( $body['postId'] ) ? (int) $body['postId'] : 0;
		$postContent  = ! empty( $body['postContent'] ) ? sanitize_text_field( $body['postContent'] ) : '';
		$focusKeyword = ! empty( $body['focusKeyword'] ) ? sanitize_text_field( $body['focusKeyword'] ) : '';
		$rephrase     = isset( $body['rephrase'] ) ? boolval( $body['rephrase'] ) : false;
		$titles       = ! empty( $body['titles'] ) ? $body['titles'] : [];
		$options      = $body['options'] ?? [];
		if ( ! $postContent || empty( $options ) ) {
			return new \WP_REST_Response( [
				'success' => false,
				'message' => 'Missing required parameters.'
			], 400 );
		}

		foreach ( $options as $k => $option ) {
			$options[ $k ] = aioseo()->helpers->sanitizeOption( $option );
		}

		foreach ( $titles as $k => $title ) {
			$titles[ $k ] = sanitize_text_field( $title );
		}

		$response = aioseo()->helpers->wpRemotePost( self::getAiGeneratorApiUrl() . 'meta/title/', [
			'timeout' => 60,
			'headers' => self::getRequestHeaders(),
			'body'    => wp_json_encode( [
				'postContent'  => $postContent,
				'focusKeyword' => $focusKeyword,
				'tone'         => $options['tone'],
				'audience'     => $options['audience'],
				'rephrase'     => $rephrase,
				'titles'       => $titles
			] )
		] );

		$responseCode = wp_remote_retrieve_response_code( $response );
		if ( 200 !== $responseCode ) {
			return new \WP_REST_Response( [
				'success' => false,
				'message' => 'Failed to generate meta titles.'
			], 400 );
		}

		$responseBody = json_decode( wp_remote_retrieve_body( $response ) );
		$titles       = aioseo()->helpers->sanitizeOption( $responseBody->titles );
		if ( empty( $responseBody->success ) || empty( $titles ) ) {
			if ( 'insufficient_credits' === $responseBody->code ) {
				aioseo()->internalOptions->internal->ai->credits->remaining = $responseBody->remaining ?? 0;
			}

			return new \WP_REST_Response( [
				'success' => false,
				'message' => 'Failed to generate meta titles.'
			], 400 );
		}

		self::updateAiOptions( $responseBody );

		// Decode HTML entities again. Vue will escape data if needed.
		foreach ( $titles as $k => $title ) {
			$titles[ $k ] = aioseo()->helpers->decodeHtmlEntities( $title );
		}

		// Get the post and save the data.
		$aioseoPost             = Models\Post::getPost( $postId );
		$aioseoPost->ai         = Models\Post::getDefaultAiOptions( $aioseoPost->ai );
		$aioseoPost->ai->titles = $titles;
		$aioseoPost->save();

		return new \WP_REST_Response( [
			'success'   => true,
			'titles'    => $titles,
			'aiOptions' => aioseo()->internalOptions->internal->ai->all()
		], 200 );
	}

	/**
	 * Generates description suggestions based on the provided content and options.
	 *
	 * @since 4.8.4
	 *
	 * @param  \WP_REST_Request  $request The REST Request
	 * @return \WP_REST_Response          The response.
	 */
	public static function generateDescriptions( $request ) {
		$body         = $request->get_json_params();
		$postId       = ! empty( $body['postId'] ) ? (int) $body['postId'] : 0;
		$postContent  = ! empty( $body['postContent'] ) ? sanitize_text_field( $body['postContent'] ) : '';
		$focusKeyword = ! empty( $body['focusKeyword'] ) ? sanitize_text_field( $body['focusKeyword'] ) : '';
		$rephrase     = isset( $body['rephrase'] ) ? boolval( $body['rephrase'] ) : false;
		$descriptions = ! empty( $body['descriptions'] ) ? $body['descriptions'] : [];
		$options      = $body['options'] ?? [];
		if ( ! $postContent || empty( $options ) ) {
			return new \WP_REST_Response( [
				'success' => false,
				'message' => 'Missing required parameters.'
			], 400 );
		}

		foreach ( $options as $k => $option ) {
			$options[ $k ] = aioseo()->helpers->sanitizeOption( $option );
		}

		foreach ( $descriptions as $k => $description ) {
			$descriptions[ $k ] = sanitize_text_field( $description );
		}

		$response = aioseo()->helpers->wpRemotePost( self::getAiGeneratorApiUrl() . 'meta/description/', [
			'timeout' => 60,
			'headers' => self::getRequestHeaders(),
			'body'    => wp_json_encode( [
				'postContent'  => $postContent,
				'focusKeyword' => $focusKeyword,
				'tone'         => $options['tone'],
				'audience'     => $options['audience'],
				'rephrase'     => $rephrase,
				'descriptions' => $descriptions
			] )
		] );

		$responseCode = wp_remote_retrieve_response_code( $response );
		if ( 200 !== $responseCode ) {
			return new \WP_REST_Response( [
				'success' => false,
				'message' => 'Failed to generate meta descriptions.'
			], 400 );
		}

		$responseBody = json_decode( wp_remote_retrieve_body( $response ) );
		$descriptions = aioseo()->helpers->sanitizeOption( $responseBody->descriptions );
		if ( empty( $responseBody->success ) || empty( $descriptions ) ) {
			if ( 'insufficient_credits' === $responseBody->code ) {
				aioseo()->internalOptions->internal->ai->credits->remaining = $responseBody->remaining ?? 0;
			}

			return new \WP_REST_Response( [
				'success' => false,
				'message' => 'Failed to generate meta descriptions.'
			], 400 );
		}

		self::updateAiOptions( $responseBody );

		// Decode HTML entities again. Vue will escape data if needed.
		foreach ( $descriptions as $k => $description ) {
			$descriptions[ $k ] = aioseo()->helpers->decodeHtmlEntities( $description );
		}

		// Get the post and save the data.
		$aioseoPost                   = Models\Post::getPost( $postId );
		$aioseoPost->ai               = Models\Post::getDefaultAiOptions( $aioseoPost->ai );
		$aioseoPost->ai->descriptions = $descriptions;
		$aioseoPost->save();

		return new \WP_REST_Response( [
			'success'      => true,
			'descriptions' => $descriptions,
			'aiOptions'    => aioseo()->internalOptions->internal->ai->all()
		], 200 );
	}

	/**
	 * Generates social posts based on the provided content and options.
	 *
	 * @since 4.8.4
	 *
	 * @param  \WP_REST_Request  $request The REST Request
	 * @return \WP_REST_Response          The response.
	 */
	public static function generateSocialPosts( $request ) {
		$body        = $request->get_json_params();
		$postId      = ! empty( $body['postId'] ) ? (int) $body['postId'] : 0;
		$postContent = ! empty( $body['postContent'] ) ? sanitize_text_field( $body['postContent'] ) : '';
		$permalink   = ! empty( $body['permalink'] ) ? esc_url_raw( urldecode( $body['permalink'] ) ) : '';
		$options     = $body['options'] ?? [];
		if ( ! $postContent || ! $permalink || empty( $options['media'] ) ) {
			return new \WP_REST_Response( [
				'success' => false,
				'message' => 'Missing required parameters.'
			], 400 );
		}

		foreach ( $options as $k => $option ) {
			$options[ $k ] = aioseo()->helpers->sanitizeOption( $option );
		}

		$response = aioseo()->helpers->wpRemotePost( self::getAiGeneratorApiUrl() . 'social-posts/', [
			'timeout' => 60,
			'headers' => self::getRequestHeaders(),
			'body'    => wp_json_encode( [
				'postContent' => $postContent,
				'url'         => $permalink,
				'tone'        => $options['tone'],
				'audience'    => $options['audience'],
				'media'       => $options['media']
			] )
		] );

		$responseCode = wp_remote_retrieve_response_code( $response );
		if ( 200 !== $responseCode ) {
			return new \WP_REST_Response( [
				'success' => false,
				'message' => 'Failed to generate social posts.'
			], 400 );
		}

		$responseBody = json_decode( wp_remote_retrieve_body( $response ) );
		if ( empty( $responseBody->success ) || empty( $responseBody->snippets ) ) {
			if ( 'insufficient_credits' === $responseBody->code ) {
				aioseo()->internalOptions->internal->ai->credits->remaining = $responseBody->remaining ?? 0;
			}

			return new \WP_REST_Response( [
				'success' => false,
				'message' => 'Failed to generate social posts.'
			], 400 );
		}

		$socialPosts = [];
		foreach ( $responseBody->snippets as $type => $content ) {
			if ( 'email' === $type ) {
				$socialPosts[ $type ] = [
					'subject' => aioseo()->helpers->decodeHtmlEntities( sanitize_text_field( $content->subject ) ),
					'preview' => aioseo()->helpers->decodeHtmlEntities( sanitize_text_field( $content->preview ) ),
					'content' => aioseo()->helpers->decodeHtmlEntities( strip_tags( $content->content, '<a>' ) )
				];

				continue;
			}

			// Strip all tags except <a>.
			$socialPosts[ $type ] = aioseo()->helpers->decodeHtmlEntities( strip_tags( $content, '<a>' ) );
		}

		self::updateAiOptions( $responseBody );

		// Get the post and save the data.
		$aioseoPost     = Models\Post::getPost( $postId );
		$aioseoPost->ai = Models\Post::getDefaultAiOptions( $aioseoPost->ai );

		// Replace the social posts with the new ones, but don't overwrite the existing ones that weren't regenerated.
		foreach ( $socialPosts as $type => $content ) {
			$aioseoPost->ai->socialPosts->{ $type } = $content;
		}

		$aioseoPost->save();

		return new \WP_REST_Response( [
			'success'   => true,
			'snippets'  => $aioseoPost->ai->socialPosts, // Return all the social posts, not just the new ones.
			'aiOptions' => aioseo()->internalOptions->internal->ai->all()
		], 200 );
	}

	/**
	 * Generates FAQs based on the provided content and options.
	 *
	 * @since 4.8.4
	 *
	 * @param  \WP_REST_Request  $request The REST Request
	 * @return \WP_REST_Response          The response.
	 */
	public static function generateFaqs( $request ) {
		$body        = $request->get_json_params();
		$postId      = ! empty( $body['postId'] ) ? (int) $body['postId'] : 0;
		$postContent = ! empty( $body['postContent'] ) ? $body['postContent'] : '';
		$rephrase    = isset( $body['rephrase'] ) ? boolval( $body['rephrase'] ) : false;
		$faqs        = ! empty( $body['faqs'] ) ? $body['faqs'] : [];
		$options     = $body['options'] ?? [];
		if ( ! $postContent || empty( $options ) ) {
			return new \WP_REST_Response( [
				'success' => false,
				'message' => 'Missing required parameters.'
			], 400 );
		}

		foreach ( $options as $k => $option ) {
			$options[ $k ] = aioseo()->helpers->sanitizeOption( $option );
		}

		foreach ( $faqs as $k => $faq ) {
			$faqs[ $k ]['question'] = sanitize_text_field( $faq['question'] );
			$faqs[ $k ]['answer']   = sanitize_text_field( $faq['answer'] );
		}

		$response = aioseo()->helpers->wpRemotePost( self::getAiGeneratorApiUrl() . 'faqs/', [
			'timeout' => 60,
			'headers' => self::getRequestHeaders(),
			'body'    => wp_json_encode( [
				'postContent' => $postContent,
				'tone'        => $options['tone'],
				'audience'    => $options['audience'],
				'rephrase'    => $rephrase,
				'faqs'        => $faqs
			] ),
		] );

		$responseCode = wp_remote_retrieve_response_code( $response );
		if ( 200 !== $responseCode ) {
			return new \WP_REST_Response( [
				'success' => false,
				'message' => 'Failed to generate FAQs.'
			], 400 );
		}

		$responseBody = json_decode( wp_remote_retrieve_body( $response ) );
		$faqs         = aioseo()->helpers->sanitizeOption( $responseBody->faqs );
		if ( empty( $responseBody->success ) || empty( $responseBody->faqs ) ) {
			if ( 'insufficient_credits' === $responseBody->code ) {
				aioseo()->internalOptions->internal->ai->credits->remaining = $responseBody->remaining ?? 0;
			}

			return new \WP_REST_Response( [
				'success' => false,
				'message' => 'Failed to generate FAQs.'
			], 400 );
		}

		self::updateAiOptions( $responseBody );

		// Decode HTML entities again. Vue will escape data if needed.
		foreach ( $faqs as $k => $faq ) {
			$faqs[ $k ]['question'] = aioseo()->helpers->decodeHtmlEntities( $faq['question'] );
			$faqs[ $k ]['answer']   = aioseo()->helpers->decodeHtmlEntities( $faq['answer'] );
		}

		// Get the post and save the data.
		$aioseoPost           = Models\Post::getPost( $postId );
		$aioseoPost->ai       = Models\Post::getDefaultAiOptions( $aioseoPost->ai );
		$aioseoPost->ai->faqs = $faqs;
		$aioseoPost->save();

		return new \WP_REST_Response( [
			'success'   => true,
			'faqs'      => $faqs,
			'aiOptions' => aioseo()->internalOptions->internal->ai->all()
		], 200 );
	}

	/**
	 * Generates key points based on the provided content and options.
	 *
	 * @since 4.8.4
	 *
	 * @param  \WP_REST_Request  $request The REST Request
	 * @return \WP_REST_Response          The response.
	 */
	public static function generateKeyPoints( $request ) {
		$body        = $request->get_json_params();
		$postId      = ! empty( $body['postId'] ) ? (int) $body['postId'] : 0;
		$postContent = ! empty( $body['postContent'] ) ? $body['postContent'] : '';
		$rephrase    = isset( $body['rephrase'] ) ? boolval( $body['rephrase'] ) : false;
		$keyPoints   = ! empty( $body['keyPoints'] ) ? $body['keyPoints'] : [];
		$options     = $body['options'] ?? [];
		if ( ! $postContent || empty( $options ) ) {
			return new \WP_REST_Response( [
				'success' => false,
				'message' => 'Missing required parameters.'
			], 400 );
		}

		foreach ( $options as $k => $option ) {
			$options[ $k ] = aioseo()->helpers->sanitizeOption( $option );
		}

		foreach ( $keyPoints as $k => $keyPoint ) {
			$keyPoints[ $k ]['title']       = sanitize_text_field( $keyPoint['title'] );
			$keyPoints[ $k ]['explanation'] = sanitize_text_field( $keyPoint['explanation'] );
		}

		$response = aioseo()->helpers->wpRemotePost( self::getAiGeneratorApiUrl() . 'key-points/', [
			'timeout' => 60,
			'headers' => self::getRequestHeaders(),
			'body'    => wp_json_encode( [
				'postContent' => $postContent,
				'tone'        => $options['tone'],
				'audience'    => $options['audience'],
				'rephrase'    => $rephrase,
				'keyPoints'   => $keyPoints
			] ),
		] );

		$responseCode = wp_remote_retrieve_response_code( $response );
		if ( 200 !== $responseCode ) {
			return new \WP_REST_Response( [
				'success' => false,
				'message' => 'Failed to generate key points.'
			], 400 );
		}

		$responseBody = json_decode( wp_remote_retrieve_body( $response ) );
		$keyPoints    = aioseo()->helpers->sanitizeOption( $responseBody->keyPoints );
		if ( empty( $responseBody->success ) || empty( $keyPoints ) ) {
			if ( 'insufficient_credits' === $responseBody->code ) {
				aioseo()->internalOptions->internal->ai->credits->remaining = $responseBody->remaining ?? 0;
			}

			return new \WP_REST_Response( [
				'success' => false,
				'message' => 'Failed to generate key points.'
			], 400 );
		}

		self::updateAiOptions( $responseBody );

		// Decode HTML entities again. Vue will escape data if needed.
		foreach ( $keyPoints as $k => $keyPoint ) {
			$keyPoints[ $k ]['title']       = aioseo()->helpers->decodeHtmlEntities( $keyPoint['title'] );
			$keyPoints[ $k ]['explanation'] = aioseo()->helpers->decodeHtmlEntities( $keyPoint['explanation'] );
		}

		// Get the post and save the data.
		$aioseoPost                = Models\Post::getPost( $postId );
		$aioseoPost->ai            = Models\Post::getDefaultAiOptions( $aioseoPost->ai );
		$aioseoPost->ai->keyPoints = $keyPoints;
		$aioseoPost->save();

		return new \WP_REST_Response( [
			'success'   => true,
			'keyPoints' => $keyPoints,
			'aiOptions' => aioseo()->internalOptions->internal->ai->all()
		], 200 );
	}

	/**
	 * Updates the AI options.
	 *
	 * @since 4.8.4
	 *
	 * @param object $responseBody The response body.
	 */
	private static function updateAiOptions( $responseBody ) {
		aioseo()->internalOptions->internal->ai->credits->total     = (int) $responseBody->total ?? 0;
		aioseo()->internalOptions->internal->ai->credits->remaining = (int) $responseBody->remaining ?? 0;

		// Get existing orders and append the new ones to prevent 'Indirect modification of overloaded prop' PHP warning.
		$existingOrders = aioseo()->internalOptions->internal->ai->credits->orders ?? [];
		$existingOrders = array_merge( $existingOrders, aioseo()->helpers->sanitizeOption( $responseBody->orders ) );

		aioseo()->internalOptions->internal->ai->credits->orders = $existingOrders;

		if ( ! empty( $responseBody->license ) ) {
			aioseo()->internalOptions->internal->ai->credits->license->total     = (int) $responseBody->license->total ?? 0;
			aioseo()->internalOptions->internal->ai->credits->license->remaining = (int) $responseBody->license->remaining ?? 0;
			aioseo()->internalOptions->internal->ai->credits->license->expires   = (int) $responseBody->license->expires ?? 0;
		}
	}

	/**
	 * Returns the default request headers.
	 *
	 * @since 4.8.4
	 *
	 * @return array The default request headers.
	 */
	protected static function getRequestHeaders() {
		$headers = [
			'Content-Type'       => 'application/json',
			'X-AIOSEO-Ai-Token'  => aioseo()->internalOptions->internal->ai->accessToken,
			'X-AIOSEO-Ai-Domain' => aioseo()->helpers->getSiteDomain()
		];

		if ( aioseo()->pro && aioseo()->license->getLicenseKey() ) {
			$headers['X-AIOSEO-License'] = aioseo()->license->getLicenseKey();
		}

		return $headers;
	}

	/**
	 * Returns the AI Generator API URL.
	 *
	 * @since 4.8.4
	 *
	 * @return string The AI Generator API URL.
	 */
	public static function getAiGeneratorApiUrl() {
		return defined( 'AIOSEO_AI_GENERATOR_URL' ) ? AIOSEO_AI_GENERATOR_URL : self::$aiGeneratorApiUrl;
	}

	/**
	 * Deactivates the access token.
	 *
	 * @since 4.8.4
	 *
	 * @param  \WP_REST_Request  $request The REST Request
	 * @return \WP_REST_Response          The response.
	 */
	public static function deactivate( $request ) {
		$body    = $request->get_json_params();
		$network = is_multisite() && ! empty( $body['network'] ) ? (bool) $body['network'] : false;

		$internalOptions = aioseo()->internalOptions;
		if ( $network ) {
			$internalOptions = aioseo()->internalNetworkOptions;
		}

		$internalOptions->internal->ai->reset();

		aioseo()->ai->getAccessToken( true );

		return new \WP_REST_Response( [
			'success' => true,
			'aiData'  => $internalOptions->internal->ai->all()
		], 200 );
	}
}