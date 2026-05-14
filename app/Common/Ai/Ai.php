<?php

namespace AIOSEO\Plugin\Common\Ai;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AIOSEO\Plugin\Common\Models;

/**
 * AI class.
 *
 * @since 4.8.4
 */
class Ai {
	/**
	 * The assistant class.
	 *
	 * @since 4.9.1
	 *
	 * @var Assistant|null
	 */
	public $assistant = null;

	/**
	 * The image class.
	 *
	 * @since 4.8.8
	 *
	 * @var Image|null
	 */
	public $image = null;

	/**
	 * The bulk actions class.
	 *
	 * @since 4.9.6
	 *
	 * @var BulkActions|null
	 */
	public $bulkActions = null;

	/**
	 * All AI-related UI options.
	 *
	 * @since 4.9.6
	 *
	 * @var array
	 */
	public $options = [];

	/**
	 * The base URL for the licensing server.
	 *
	 * @since 4.8.4
	 *
	 * @var string
	 */
	private $licensingUrl = 'https://licensing.aioseo.com/v1/';

	/**
	 * The AI Generator API base URL (without version path).
	 *
	 * @since   4.8.4
	 * @version 4.8.8 Moved from {@see \AIOSEO\Plugin\Common\Api\Ai}.
	 * @version 4.9.7 Stripped version segment; version is now a parameter of {@see getAiGeneratorApiUrl()}.
	 *
	 * @var string
	 */
	private $aiGeneratorApiBaseUrl = 'https://ai-generator.aioseo.com/';

	/**
	 * The action name for getting the access token.
	 *
	 * @since 4.9.1
	 *
	 * @var string
	 */
	protected $getAccessTokenAction = 'aioseo_ai_get_access_token';

	/**
	 * The action name for fetching credits.
	 *
	 * @since 4.8.4
	 *
	 * @var string
	 */
	protected $creditFetchAction = 'aioseo_ai_update_credits';

	/**
	 * Class constructor.
	 *
	 * @since 4.8.4
	 */
	public function __construct() {
		$this->setOptions();

		add_action( 'admin_init', [ $this, 'scheduleGetAccessToken' ] );
		add_action( 'admin_init', [ $this, 'scheduleCreditFetchAction' ] );

		add_action( $this->getAccessTokenAction, [ $this, 'getAccessToken' ] );
		add_action( $this->creditFetchAction, [ $this, 'updateCredits' ] );

		$this->assistant   = new Assistant();
		$this->image       = new Image();
		$this->bulkActions = new BulkActions();
	}

	/**
	 * Schedules the initial access token fetch action if no access token is set.
	 *
	 * @since 4.9.1
	 *
	 * @return void
	 */
	public function scheduleGetAccessToken() {
		if ( aioseo()->sensitiveOptions->hasValue( 'aiAccessToken' ) ) {
			return;
		}

		if ( ! aioseo()->actionScheduler->isScheduled( $this->getAccessTokenAction ) ) {
			aioseo()->actionScheduler->scheduleSingle( $this->getAccessTokenAction, 0, [], true );
		}
	}

	/**
	 * Gets an access token from the server.
	 * This is the one-time access token that includes 50 free credits.
	 *
	 * @since 4.8.4
	 *
	 * @param  bool $refresh Whether to refresh the access token.
	 * @return void
	 */
	public function getAccessToken( $refresh = false ) {
		if (
			(
				aioseo()->sensitiveOptions->hasValue( 'aiAccessToken' ) ||
				aioseo()->core->cache->get( 'ai_access_token_idle' )
			) &&
			! $refresh
		) {
			return;
		}

		// Don't overwrite users who manually connected to their account (as opposed to via their license key).
		// Credits can still be refreshed via updateCredits() independently.
		if ( aioseo()->internalOptions->internal->ai->isManuallyConnected ) {
			return;
		}

		$response = aioseo()->helpers->wpRemotePost( $this->getApiUrl() . 'ai/auth/', [
			'body' => wp_json_encode( [
				'domain' => aioseo()->helpers->getSiteDomain()
			] )
		] );

		if ( is_wp_error( $response ) ) {
			aioseo()->core->cache->update( 'ai_access_token_idle', true, HOUR_IN_SECONDS );

			// Schedule another, one-time event in approx. 1 hour from now.
			aioseo()->actionScheduler->scheduleSingle( $this->creditFetchAction, HOUR_IN_SECONDS + wp_rand( 0, 30 * MINUTE_IN_SECONDS ), [] );

			return;
		}

		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body );
		if ( empty( $data->accessToken ) ) {
			aioseo()->core->cache->update( 'ai_access_token_idle', true, HOUR_IN_SECONDS );

			// Schedule another, one-time event in approx. 1 hour from now.
			aioseo()->actionScheduler->scheduleSingle( $this->creditFetchAction, HOUR_IN_SECONDS + wp_rand( 0, 30 * MINUTE_IN_SECONDS ), [] );

			return;
		}

		aioseo()->sensitiveOptions->set( 'aiAccessToken', $data->accessToken );
		aioseo()->internalOptions->internal->ai->isTrialAccessToken  = $data->isFree ?? false;
		aioseo()->internalOptions->internal->ai->isManuallyConnected = false;
		aioseo()->core->cache->update( 'ai_access_token_idle', true, 12 * HOUR_IN_SECONDS );

		$this->updateCredits( true );
	}

	/**
	 * Schedules the credit fetch action.
	 *
	 * @since 4.8.4
	 *
	 * @return void
	 */
	public function scheduleCreditFetchAction() {
		if ( apply_filters( 'aioseo_ai_disabled', false ) ) {
			aioseo()->actionScheduler->unschedule( $this->creditFetchAction );

			return;
		}

		if ( aioseo()->actionScheduler->isScheduled( $this->creditFetchAction ) ) {
			return;
		}

		aioseo()->actionScheduler->scheduleRecurrent( $this->creditFetchAction, DAY_IN_SECONDS, DAY_IN_SECONDS, [] );
	}

	/**
	 * Gets the credit data from the server and updates our options.
	 *
	 * @since 4.8.4
	 *
	 * @param  bool $refresh Whether to refresh the credits forcefully.
	 * @return void
	 */
	public function updateCredits( $refresh = false ) {
		if ( aioseo()->core->cache->get( 'ai_credits_idle' ) && ! $refresh ) {
			return;
		}

		if ( ! aioseo()->sensitiveOptions->hasValue( 'aiAccessToken' ) ) {
			return;
		}

		$response = aioseo()->helpers->wpRemoteGet( $this->getApiUrl() . 'ai/credits/', [
			'headers' => $this->getRequestHeaders()
		] );

		if ( is_wp_error( $response ) ) {
			aioseo()->core->cache->update( 'ai_credits_idle', true, HOUR_IN_SECONDS );

			// Schedule another, one-time event in approx. 1 hour from now.
			aioseo()->actionScheduler->scheduleSingle( $this->creditFetchAction, HOUR_IN_SECONDS + wp_rand( 0, 30 * MINUTE_IN_SECONDS ), [] );

			return;
		}

		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body );
		if ( empty( $data->success ) ) {
			if ( ! empty( $data->code ) && 'invalid-token' === $data->code ) {
				// Drop the access token in case it could not be found.
				aioseo()->sensitiveOptions->set( 'aiAccessToken', '' );
			}

			aioseo()->core->cache->update( 'ai_credits_idle', true, HOUR_IN_SECONDS );

			// Schedule another, one-time event in approx. 1 hour from now.
			aioseo()->actionScheduler->scheduleSingle( $this->creditFetchAction, HOUR_IN_SECONDS + wp_rand( 0, 30 * MINUTE_IN_SECONDS ), [] );

			return;
		}

		aioseo()->core->cache->update( 'ai_credits_idle', true, 12 * HOUR_IN_SECONDS );

		$orders = [];
		if ( ! empty( $data->orders ) ) {
			foreach ( $data->orders as $order ) {
				if (
					empty( $order->total ) ||
					! isset( $order->remaining ) ||
					! isset( $order->expires )
				) {
					continue;
				}

				$orders[] = [
					'total'     => intval( $order->total ),
					'remaining' => intval( $order->remaining ),
					'expires'   => intval( $order->expires )
				];
			}
		}

		aioseo()->internalOptions->internal->ai->credits->orders    = $orders;
		aioseo()->internalOptions->internal->ai->credits->total     = isset( $data->total ) ? intval( $data->total ) : 0;
		aioseo()->internalOptions->internal->ai->credits->remaining = isset( $data->remaining ) ? intval( $data->remaining ) : 0;

		if ( ! empty( $data->license ) ) {
			aioseo()->internalOptions->internal->ai->credits->license->total     = intval( $data->license->total );
			aioseo()->internalOptions->internal->ai->credits->license->remaining = intval( $data->license->remaining );
			aioseo()->internalOptions->internal->ai->credits->license->expires   = intval( $data->license->expires );
		} else {
			aioseo()->internalOptions->internal->ai->credits->license->reset();
		}

		if ( ! empty( $data->costPerFeature ) ) {
			aioseo()->internalOptions->internal->ai->costPerFeature = json_decode( wp_json_encode( $data->costPerFeature ), true );
		}
	}

	/**
	 * Returns the default request headers.
	 *
	 * @since 4.8.4
	 *
	 * @return array The default request headers.
	 */
	public function getRequestHeaders() {
		$headers = [
			'Content-Type'       => 'application/json',
			'X-AIOSEO-Ai-Token'  => aioseo()->sensitiveOptions->get( 'aiAccessToken' ),
			'X-AIOSEO-Ai-Domain' => aioseo()->helpers->getSiteDomain()
		];

		return $headers;
	}

	/**
	 * Returns the API URL of the licensing server.
	 *
	 * @since 4.8.4
	 *
	 * @return string The URL.
	 */
	protected function getApiUrl() {
		if ( defined( 'AIOSEO_LICENSING_URL' ) ) {
			return AIOSEO_LICENSING_URL;
		}

		return $this->licensingUrl;
	}

	/**
	 * Returns the AI Generator API URL for the given version.
	 *
	 * @since   4.8.4
	 * @version 4.8.8 Moved from {@see \AIOSEO\Plugin\Common\Api\Ai}.
	 * @version 4.9.7 Added $version parameter; base URL no longer includes the version segment.
	 *
	 * @param  string $version The API version (e.g. 'v1', 'v2'). Defaults to 'v1'.
	 * @return string          The AI Generator API URL.
	 */
	public function getAiGeneratorApiUrl( $version = 'v1' ) {
		if ( defined( 'AIOSEO_AI_GENERATOR_URL' ) ) {
			return AIOSEO_AI_GENERATOR_URL;
		}

		return $this->aiGeneratorApiBaseUrl . $version . '/';
	}

	/**
	 * Generates title suggestions based on the provided content and options.
	 *
	 * @since 4.9.6
	 *
	 * @param  array $data The data array.
	 * @return array       The result array.
	 */
	public function generateTitles( $data ) {
		$postId       = $data['postId'] ?? 0;
		$postContent  = $data['postContent'] ?? '';
		$focusKeyword = $data['focusKeyword'] ?? '';
		$rephrase     = $data['rephrase'] ?? false;
		$titles       = $data['titles'] ?? [];
		$options      = $data['options'] ?? [];

		if ( ! current_user_can( 'edit_post', $postId ) ) {
			return [
				'success' => false,
				'message' => 'Unauthorized.'
			];
		}

		$result = $this->callAiService( 'meta/title/', [
			'postContent'  => $postContent,
			'focusKeyword' => $focusKeyword,
			'tone'         => $options['tone'],
			'audience'     => $options['audience'],
			'rephrase'     => $rephrase,
			'titles'       => $titles
		], 'titles' );

		if ( ! $result['success'] ) {
			return $result;
		}

		$result['titles'] = array_map( [ aioseo()->helpers, 'decodeHtmlEntities' ], $result['titles'] );

		$aioseoPost             = Models\Post::getPost( $postId );
		$aioseoPost->ai         = Models\Post::getDefaultAiOptions( $aioseoPost->ai );
		$aioseoPost->ai->titles = $result['titles'];
		$aioseoPost->save();

		return $result;
	}

	/**
	 * Generates description suggestions based on the provided content and options.
	 *
	 * @since 4.9.6
	 *
	 * @param  array $data The data array.
	 * @return array       The result array.
	 */
	public function generateDescriptions( $data ) {
		$postId       = $data['postId'] ?? 0;
		$postContent  = $data['postContent'] ?? '';
		$focusKeyword = $data['focusKeyword'] ?? '';
		$rephrase     = $data['rephrase'] ?? false;
		$descriptions = $data['descriptions'] ?? [];
		$options      = $data['options'] ?? [];

		if ( ! current_user_can( 'edit_post', $postId ) ) {
			return [
				'success' => false,
				'message' => 'Unauthorized.'
			];
		}

		$result = $this->callAiService( 'meta/description/', [
			'postContent'  => $postContent,
			'focusKeyword' => $focusKeyword,
			'tone'         => $options['tone'],
			'audience'     => $options['audience'],
			'rephrase'     => $rephrase,
			'descriptions' => $descriptions
		], 'descriptions' );

		if ( ! $result['success'] ) {
			return $result;
		}

		$result['descriptions'] = array_map( [ aioseo()->helpers, 'decodeHtmlEntities' ], $result['descriptions'] );

		$aioseoPost                   = Models\Post::getPost( $postId );
		$aioseoPost->ai               = Models\Post::getDefaultAiOptions( $aioseoPost->ai );
		$aioseoPost->ai->descriptions = $result['descriptions'];
		$aioseoPost->save();

		return $result;
	}

	/**
	 * Generates ALT text for an image using the AI service.
	 *
	 * @since 4.9.6
	 *
	 * @param  array $data The data array containing attachmentId.
	 * @return array       The result array.
	 */
	public function generateImageAlt( $data ) {
		$attachmentId = (int) ( $data['attachmentId'] ?? 0 );

		if ( ! aioseo()->helpers->attachmentIs( 'image', $attachmentId ) ) {
			return [
				'success' => false,
				'code'    => 'not_an_image',
				'message' => "The attachment is not an image. (Attachment #$attachmentId)"
			];
		}

		$image = aioseo()->helpers->getBase64FromAttachment( $attachmentId );

		if ( empty( $image ) ) {
			return [
				'success' => false,
				'message' => "Could not encode image (attachment #$attachmentId)."
			];
		}

		$result = $this->callAiService( 'image/alt-text/', [
			'image' => $image
		], 'altTexts' );

		if ( ! $result['success'] ) {
			return $result;
		}

		$result['altTexts'] = array_map( [ aioseo()->helpers, 'decodeHtmlEntities' ], $result['altTexts'] );

		return $result;
	}

	/**
	 * Generates schema markup using the AI service.
	 *
	 * @since 4.9.6
	 *
	 * @param  array $body The request body to forward to the AI service.
	 * @return array       The result array.
	 */
	public function generateSchemas( $body ) {
		$postId = ! empty( $body['postId'] ) ? (int) $body['postId'] : 0;
		$result = $this->callAiService( 'schema/', $body, 'schemas', [
			'timeout'  => 90,
			'sanitize' => false
		] );

		if ( ! $result['success'] ) {
			return $result;
		}

		$rawSchemas = json_decode( wp_json_encode( $result['schemas'] ), true );

		$schemas = [];
		foreach ( $rawSchemas as $schemaEntry ) {
			$properties = $schemaEntry['properties'] ?? null;
			if ( empty( $properties ) ) {
				continue;
			}

			array_walk_recursive( $properties, function ( &$value ) {
				if ( is_string( $value ) ) {
					$value = wp_strip_all_tags( wp_check_invalid_utf8( trim( $value ) ) );
				}
			} );

			$confidence = isset( $schemaEntry['confidence'] ) ? (int) $schemaEntry['confidence'] : null;
			$reasoning  = ! empty( $schemaEntry['reasoning'] )
				? wp_strip_all_tags( wp_check_invalid_utf8( trim( $schemaEntry['reasoning'] ) ) )
				: '';

			$schemas[] = [
				'schemaType' => $properties['@type'] ?? '', // Fallback to empty string, but '@type' is always present in the service response.
				'schemaData' => $properties,
				'confidence' => $confidence,
				'reasoning'  => $reasoning
			];
		}

		$aioseoPost              = Models\Post::getPost( $postId );
		$aioseoPost->ai          = Models\Post::getDefaultAiOptions( $aioseoPost->ai );
		$aioseoPost->ai->schemas = $schemas;
		$aioseoPost->save();

		return [
			'success' => true,
			'schemas' => $schemas
		];
	}

	/**
	 * Calls the AI Generator service, handles errors, credits and sanitization.
	 *
	 * @since   4.9.6
	 * @version 4.9.6 Added $options parameter.
	 *
	 * @param  string $endpoint  The endpoint path relative to the AI Generator API URL.
	 * @param  array  $body      The request body.
	 * @param  string $resultKey The key that holds the generated results in the response (e.g. 'titles', 'descriptions', 'altTexts').
	 * @param  array  $options   Optional. 'timeout' (int, default 60) and 'sanitize' (bool, default true).
	 * @return array             Success: [ 'success' => true, $resultKey => [...] ]. Failure: [ 'success' => false, 'message' => '...' ].
	 */
	protected function callAiService( $endpoint, $body, $resultKey, $options = [] ) {
		$timeout  = ! empty( $options['timeout'] ) ? (int) $options['timeout'] : 60;
		$sanitize = ! isset( $options['sanitize'] ) || $options['sanitize'];

		$response = aioseo()->helpers->wpRemotePost( $this->getAiGeneratorApiUrl() . $endpoint, [
			'timeout' => $timeout,
			'headers' => $this->getRequestHeaders(),
			'body'    => wp_json_encode( $body )
		] );

		$responseBody = json_decode( wp_remote_retrieve_body( $response ) );
		$responseCode = wp_remote_retrieve_response_code( $response );

		// Only trust the message if `success` was explicitly set to `false` — this confirms the response came from our API's error contract.
		$serviceError = isset( $responseBody->success ) && false === $responseBody->success && ! empty( $responseBody->message )
			? 'Service error: ' . $responseBody->message
			: null;
		$errorDetails = array_filter( [ "Service response code: $responseCode", $serviceError ] );

		if ( 200 !== $responseCode ) {
			$errorDetails[] = 'The AI service returned an unexpected response';

			return [
				'success' => false,
				'message' => implode( ' | ', $errorDetails )
			];
		}

		$results = ! empty( $responseBody->$resultKey )
			? ( $sanitize ? aioseo()->helpers->sanitizeOption( $responseBody->$resultKey ) : $responseBody->$resultKey )
			: [];

		if ( empty( $responseBody->success ) || empty( $results ) ) {
			if ( ! empty( $responseBody->code ) && 'insufficient_credits' === $responseBody->code ) {
				aioseo()->internalOptions->internal->ai->credits->remaining = $responseBody->remaining ?? 0;

				$errorDetails[] = 'Not enough credits';

				return [
					'success' => false,
					'message' => implode( ' | ', $errorDetails )
				];
			}

			$errorDetails[] = "The AI service did not return any $resultKey";

			return [
				'success' => false,
				'message' => implode( ' | ', $errorDetails )
			];
		}

		$this->updateAiOptions( $responseBody );

		return [
			'success'  => true,
			$resultKey => $results
		];
	}

	/**
	 * Updates the AI options from the response body.
	 *
	 * @since   4.8.4
	 * @version 4.9.6 Moved from {@see \AIOSEO\Plugin\Common\Api\Ai}.
	 *
	 * @param  object $responseBody The response body.
	 * @return void
	 */
	public function updateAiOptions( $responseBody ) {
		aioseo()->internalOptions->internal->ai->credits->total     = (int) ( $responseBody->total ?? 0 );
		aioseo()->internalOptions->internal->ai->credits->remaining = (int) ( $responseBody->remaining ?? 0 );

		// Get existing orders and append the new ones to prevent 'Indirect modification of overloaded prop' PHP warning.
		$existingOrders = aioseo()->internalOptions->internal->ai->credits->orders ?? [];
		$existingOrders = array_merge( $existingOrders, aioseo()->helpers->sanitizeOption( $responseBody->orders ) );

		aioseo()->internalOptions->internal->ai->credits->orders = $existingOrders;

		if ( ! empty( $responseBody->license ) ) {
			aioseo()->internalOptions->internal->ai->credits->license->total     = (int) $responseBody->license->total ?? 0;
			aioseo()->internalOptions->internal->ai->credits->license->remaining = (int) $responseBody->license->remaining ?? 0;
			aioseo()->internalOptions->internal->ai->credits->license->expires   = (int) $responseBody->license->expires ?? 0;
		}

		if ( ! empty( $responseBody->costPerFeature ) ) {
			aioseo()->internalOptions->internal->ai->costPerFeature = json_decode( wp_json_encode( $responseBody->costPerFeature ), true );
		}
	}

	/**
	 * Set {@see self::$options}.
	 * Ideally set options only for Vue usage on the front-end.
	 *
	 * @since 4.9.6
	 *
	 * @return void
	 */
	private function setOptions() {
		$this->options = [
			'minContentLength' => 200
		];
	}
}