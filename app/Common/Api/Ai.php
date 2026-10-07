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

		aioseo()->sensitiveOptions->set( 'aiAccessToken', $accessToken );
		aioseo()->internalOptions->internal->ai->isTrialAccessToken  = false;
		aioseo()->internalOptions->internal->ai->isManuallyConnected = true;

		aioseo()->ai->updateCredits( true );

		// Build response manually since we know we just set a valid access token.
		$aiOptions                   = self::getAiOptionsPayload();
		$aiOptions['hasAccessToken'] = true;

		return new \WP_REST_Response( [
			'success'   => true,
			'aiOptions' => $aiOptions
		], 200 );
	}

	/**
	 * Fetches the current balance of AI credits.
	 *
	 * @since 4.8.8
	 *
	 * @param  \WP_REST_Request  $request The REST Request
	 * @return \WP_REST_Response          The response.
	 */
	public static function getCredits( $request ) {
		$body    = $request->get_json_params();
		$refresh = isset( $body['refresh'] ) ? boolval( $body['refresh'] ) : false;

		aioseo()->ai->getAccessToken( $refresh );
		aioseo()->ai->updateCredits( $refresh );

		return new \WP_REST_Response( [
			'success'   => true,
			'aiOptions' => self::getAiOptionsPayload()
		], 200 );
	}

	/**
	 * Generates title suggestions based on the provided content and options.
	 *
	 * @since   4.8.4
	 * @version 5.0.3 Forwards the error code of a failed generation.
	 *
	 * @param  \WP_REST_Request  $request The REST Request
	 * @return \WP_REST_Response          The response.
	 */
	public static function generateTitles( $request ) {
		try {
			$body         = $request->get_json_params();
			$postId       = ! empty( $body['postId'] ) ? (int) $body['postId'] : 0;
			$postContent  = ! empty( $body['postContent'] ) ? $body['postContent'] : '';
			$focusKeyword = ! empty( $body['focusKeyword'] ) ? sanitize_text_field( $body['focusKeyword'] ) : '';
			$rephrase     = isset( $body['rephrase'] ) ? boolval( $body['rephrase'] ) : false;
			$titles       = ! empty( $body['titles'] ) ? $body['titles'] : [];
			$options      = $body['options'] ?? [];

			if ( ! current_user_can( 'edit_post', $postId ) ) {
				throw new ApiException( 'unauthorized', 'Unauthorized.', 403 );
			}

			$wpObject = $postId ? aioseo()->helpers->getPost( $postId ) : null;

			if ( empty( $postContent ) && $postId ) {
				if ( ! $wpObject ) {
					throw new ApiException( 'post_not_found', 'Post not found.' );
				}

				$postContent = aioseo()->helpers->getPostContent( $wpObject );

				// Bulk generate has no frontend validation, so we gate content length here to avoid wasting AI credits.
				if ( strlen( wp_strip_all_tags( $postContent ) ) < aioseo()->ai->options['minContentLength'] ) {
					throw new ApiException( 'content_too_short', 'Post content is too short.' );
				}
			}

			if ( empty( $focusKeyword ) && $postId ) {
				$aioseoPost   = Models\Post::getPost( $postId );
				$focusKeyword = Models\Post::getKeyphrasesDefaults( $aioseoPost->keyphrases )->focus->keyphrase;
			}

			if ( empty( $postContent ) ) {
				throw new ApiException( 'no_content', 'Missing post content.' );
			}

			if ( empty( $options ) ) {
				throw new ApiException( 'missing_options', 'Missing options.' );
			}

			$options = array_map( [ aioseo()->helpers, 'sanitizeOption' ], $options );
			$titles  = array_map( 'sanitize_text_field', $titles );

			$result = aioseo()->ai->generateTitles( [
				'postId'       => $postId,
				'postContent'  => $postContent,
				'focusKeyword' => $focusKeyword,
				'rephrase'     => $rephrase,
				'titles'       => $titles,
				'options'      => $options
			] );

			if ( ! $result['success'] ) {
				throw new ApiException( $result['code'] ?? 'generation_failed', esc_html( $result['message'] ) );
			}

			return new \WP_REST_Response( [
				'success'   => true,
				'titles'    => $result['titles'],
				'aiOptions' => self::getAiOptionsPayload()
			], 200 );
		} catch ( ApiException $e ) {
			return new \WP_REST_Response( [
				'success' => false,
				'message' => $e->getMessage(),
				'code'    => $e->getErrorCode()
			], $e->getCode() );
		}
	}

	/**
	 * Generates description suggestions based on the provided content and options.
	 *
	 * @since   4.8.4
	 * @version 5.0.3 Forwards the error code of a failed generation.
	 *
	 * @param  \WP_REST_Request  $request The REST Request
	 * @return \WP_REST_Response          The response.
	 */
	public static function generateDescriptions( $request ) {
		try {
			$body         = $request->get_json_params();
			$postId       = ! empty( $body['postId'] ) ? (int) $body['postId'] : 0;
			$postContent  = ! empty( $body['postContent'] ) ? $body['postContent'] : '';
			$focusKeyword = ! empty( $body['focusKeyword'] ) ? sanitize_text_field( $body['focusKeyword'] ) : '';
			$rephrase     = isset( $body['rephrase'] ) ? boolval( $body['rephrase'] ) : false;
			$descriptions = ! empty( $body['descriptions'] ) ? $body['descriptions'] : [];
			$options      = $body['options'] ?? [];

			if ( ! current_user_can( 'edit_post', $postId ) ) {
				throw new ApiException( 'unauthorized', 'Unauthorized.', 403 );
			}

			$wpObject = $postId ? aioseo()->helpers->getPost( $postId ) : null;

			if ( empty( $postContent ) && $postId ) {
				if ( ! $wpObject ) {
					throw new ApiException( 'post_not_found', 'Post not found.' );
				}

				$postContent = aioseo()->helpers->getPostContent( $wpObject );

				// Bulk generate has no frontend validation, so we gate content length here to avoid wasting AI credits.
				if ( strlen( wp_strip_all_tags( $postContent ) ) < aioseo()->ai->options['minContentLength'] ) {
					throw new ApiException( 'content_too_short', 'Post content is too short.' );
				}
			}

			if ( empty( $focusKeyword ) && $postId ) {
				$aioseoPost   = Models\Post::getPost( $postId );
				$focusKeyword = Models\Post::getKeyphrasesDefaults( $aioseoPost->keyphrases )->focus->keyphrase;
			}

			if ( empty( $postContent ) ) {
				throw new ApiException( 'no_content', 'Missing post content.' );
			}

			if ( empty( $options ) ) {
				throw new ApiException( 'missing_options', 'Missing options.' );
			}

			$options      = array_map( [ aioseo()->helpers, 'sanitizeOption' ], $options );
			$descriptions = array_map( 'sanitize_text_field', $descriptions );

			$result = aioseo()->ai->generateDescriptions( [
				'postId'       => $postId,
				'postContent'  => $postContent,
				'focusKeyword' => $focusKeyword,
				'rephrase'     => $rephrase,
				'descriptions' => $descriptions,
				'options'      => $options
			] );

			if ( ! $result['success'] ) {
				throw new ApiException( $result['code'] ?? 'generation_failed', esc_html( $result['message'] ) );
			}

			return new \WP_REST_Response( [
				'success'      => true,
				'descriptions' => $result['descriptions'],
				'aiOptions'    => self::getAiOptionsPayload()
			], 200 );
		} catch ( ApiException $e ) {
			return new \WP_REST_Response( [
				'success' => false,
				'message' => $e->getMessage(),
				'code'    => $e->getErrorCode()
			], $e->getCode() );
		}
	}

	/**
	 * Generates ALT text for an image attachment.
	 *
	 * @since 4.9.6
	 *
	 * @param  \WP_REST_Request  $request The REST Request
	 * @return \WP_REST_Response          The response.
	 */
	public static function generateImageAlt( $request ) {
		try {
			$body         = $request->get_json_params();
			$attachmentId = ! empty( $body['attachmentId'] ) ? (int) $body['attachmentId'] : 0;

			if ( ! $attachmentId ) {
				throw new ApiException( 'missing_attachment_id', 'Missing attachment ID.' );
			}

			if ( ! current_user_can( 'edit_post', $attachmentId ) ) {
				throw new ApiException( 'unauthorized', 'Unauthorized.', 403 );
			}

			$attachment = get_post( $attachmentId );
			if ( ! $attachment || 'attachment' !== $attachment->post_type ) {
				throw new ApiException( 'attachment_not_found', 'Attachment not found.' );
			}

			$result = aioseo()->ai->generateImageAlt( [
				'attachmentId' => $attachmentId
			] );

			if ( ! $result['success'] ) {
				throw new ApiException( $result['code'] ?? 'generation_failed', esc_html( $result['message'] ) );
			}

			return new \WP_REST_Response( [
				'success'   => true,
				'altTexts'  => $result['altTexts'],
				'aiOptions' => self::getAiOptionsPayload()
			], 200 );
		} catch ( ApiException $e ) {
			return new \WP_REST_Response( [
				'success' => false,
				'message' => $e->getMessage(),
				'code'    => $e->getErrorCode()
			], $e->getCode() );
		}
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
		$postContent = ! empty( $body['postContent'] ) ? $body['postContent'] : '';
		$permalink   = ! empty( $body['permalink'] ) ? esc_url_raw( urldecode( $body['permalink'] ) ) : '';
		$options     = $body['options'] ?? [];

		if ( ! $postContent || ! $permalink || empty( $options['media'] ) ) {
			return new \WP_REST_Response( [
				'success' => false,
				'message' => 'Missing post content, permalink, or media options.'
			], 400 );
		}

		if ( strlen( $postContent ) < aioseo()->ai->options['minContentLength'] ) {
			return new \WP_REST_Response( [
				'success' => false,
				'message' => 'Post content is too short to generate AI content.'
			], 400 );
		}

		if ( ! current_user_can( 'edit_post', $postId ) ) {
			return new \WP_REST_Response( [
				'success' => false,
				'message' => 'Unauthorized.'
			], 403 );
		}

		$options = array_map( [ aioseo()->helpers, 'sanitizeOption' ], $options );

		$response = aioseo()->helpers->wpRemotePost( aioseo()->ai->getAiGeneratorApiUrl() . 'social-posts/', [
			'timeout' => 60,
			'headers' => aioseo()->ai->getRequestHeaders(),
			'body'    => wp_json_encode( [
				'postContent' => $postContent,
				'url'         => $permalink,
				'tone'        => $options['tone'],
				'audience'    => $options['audience'],
				'media'       => $options['media']
			] )
		] );

		$responseBody = json_decode( wp_remote_retrieve_body( $response ) );
		$responseCode = wp_remote_retrieve_response_code( $response );

		// Only trust the message if `success` was explicitly set to `false` — this confirms the response came from our API's error contract.
		$serviceError = isset( $responseBody->success ) && false === $responseBody->success && ! empty( $responseBody->message ) ? 'Service error: ' . $responseBody->message : null;
		$errorDetails = array_filter( [ "Service response code: $responseCode", $serviceError ] );

		// `insufficient_credits` arrives with a 402, so detect it before the generic
		// non-200 guard below — otherwise the credit-specific detail is never added.
		if ( ! empty( $responseBody->code ) && 'insufficient_credits' === $responseBody->code ) {
			aioseo()->internalOptions->internal->ai->credits->remaining = $responseBody->remaining ?? 0;

			$errorDetails[] = 'Not enough credits';

			return new \WP_REST_Response( [
				'success' => false,
				'message' => implode( ' | ', $errorDetails )
			], 400 );
		}

		if ( 200 !== $responseCode ) {
			$errorDetails[] = 'The AI service returned an unexpected response';

			return new \WP_REST_Response( [
				'success' => false,
				'message' => implode( ' | ', $errorDetails )
			], 400 );
		}

		if ( empty( $responseBody->success ) || empty( $responseBody->snippets ) ) {
			$errorDetails[] = 'The AI service did not return any social post suggestions';

			return new \WP_REST_Response( [
				'success' => false,
				'message' => implode( ' | ', $errorDetails )
			], 400 );
		}

		$socialPosts = aioseo()->ai->sanitizeSocialPosts( $responseBody->snippets );

		aioseo()->ai->updateAiOptions( $responseBody );

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
			'aiOptions' => self::getAiOptionsPayload()
		], 200 );
	}

	/**
	 * Generates a completion for the assistant.
	 *
	 * @since 4.8.8
	 *
	 * @param  \WP_REST_Request $request The REST Request
	 * @return void
	 */
	public static function generateAssistantCompletion( $request ) {
		header( 'Content-Type: text/event-stream' );
		header( 'X-Accel-Buffering: no' );

		while ( ob_get_level() > 0 ) {
			ob_end_flush();
		}

		$body          = $request->get_json_params();
		$postId        = ! empty( $body['postId'] ) ? (int) $body['postId'] : 0;
		$sseDataPrefix = 'data: ';

		if ( ! current_user_can( 'edit_post', $postId ) ) {
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- SSE format with JSON-encoded data.
			echo $sseDataPrefix . wp_json_encode( [ 'error' => 'Unauthorized.' ] ) . "\n\n";
			flush();
			exit;
		}

		$requestHeaders = aioseo()->ai->getRequestHeaders();

		// phpcs:disable WordPress.WP.AlternativeFunctions
		$ch = curl_init();

		curl_setopt_array( $ch, [
			CURLOPT_URL            => aioseo()->ai->getAiGeneratorApiUrl( 'v2' ) . 'text/',
			CURLOPT_POST           => true,
			CURLOPT_POSTFIELDS     => wp_json_encode( $body ),
			CURLOPT_TIMEOUT        => 180,
			CURLOPT_CONNECTTIMEOUT => 15,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_USERAGENT      => aioseo()->helpers->getApiUserAgent(),
			CURLOPT_ENCODING       => '',
			CURLOPT_HTTPHEADER     => array_map(
				function ( $key, $value ) {
					return $key . ': ' . $value;
				},
				array_keys( $requestHeaders ),
				$requestHeaders
			),
			CURLOPT_WRITEFUNCTION  => function ( $ch, $data ) use ( $sseDataPrefix ) {
				$lines = explode( "\n", $data );
				foreach ( $lines as $line ) {
					if ( strpos( $line, $sseDataPrefix ) !== 0 ) {
						continue;
					}

					$json = json_decode( substr( $line, strlen( $sseDataPrefix ) ), true );

					$content = $json['content'] ?? null;
					$content = $content ? strip_tags( $content ) : null;

					// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- SSE format with JSON-encoded data.
					echo $sseDataPrefix . wp_json_encode( [
						'content' => $content,
						'error'   => $json['error'] ?? null
					] ) . "\n\n";
					flush();

					if ( connection_aborted() ) {
						break;
					}
				}

				return strlen( $data );
			}
		] );

		$result = curl_exec( $ch );
		$error  = curl_error( $ch );
		// phpcs:enable WordPress.WP.AlternativeFunctions

		if ( false === $result || ! empty( $error ) ) {
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- SSE format with JSON-encoded data.
			echo $sseDataPrefix . wp_json_encode( [ 'error' => 'Connection error: ' . $error ] ) . "\n\n";
			flush();
		}

		// Exit to prevent WordPress from adding any additional output.
		exit;
	}

	/**
	 * Generates an image based on the provided prompt and other options.
	 *
	 * @since   4.8.8
	 * @version 5.0.3 Require the upload_files capability and authorize the seed image.
	 *
	 * @param  \WP_REST_Request  $request The REST Request
	 * @return \WP_REST_Response          The response.
	 */
	public static function generateImage( $request ) {
		$body            = $request->get_json_params();
		$prompt          = ! empty( $body['prompt'] ) ? sanitize_textarea_field( wp_unslash( $body['prompt'] ) ) : '';
		$quality         = ! empty( $body['quality'] ) ? sanitize_text_field( $body['quality'] ) : '';
		$style           = ! empty( $body['style'] ) ? sanitize_text_field( $body['style'] ) : '';
		$aspectRatio     = ! empty( $body['aspectRatio'] ) ? sanitize_text_field( $body['aspectRatio'] ) : '';
		$postId          = ! empty( $body['postId'] ) ? (int) $body['postId'] : 0;
		$selectedImageId = ! empty( $body['selectedImageId'] ) ? (int) $body['selectedImageId'] : 0;

		$allowedModels = [ 'gemini-3.1-flash-image', 'gpt-image-2' ];
		$model         = ! empty( $body['model'] ) ? sanitize_text_field( $body['model'] ) : 'gemini-3.1-flash-image';
		if ( ! in_array( $model, $allowedModels, true ) ) {
			$model = 'gemini-3.1-flash-image';
		}

		if ( ! current_user_can( 'edit_post', $postId ) || ! current_user_can( 'upload_files' ) ) {
			return new \WP_REST_Response( [
				'success' => false,
				'message' => 'Unauthorized.'
			], 403 );
		}

		try {
			if ( ! $prompt || ! $postId ) {
				throw new \Exception( 'Missing prompt or post ID.' );
			}

			$postImages         = aioseo()->ai->image->getByPostId( $postId );
			$foundSelectedImage = [];

			if ( ! empty( $selectedImageId ) ) {
				// `wp_list_filter()` keeps the original keys, so the match is rarely at index 0.
				$foundSelectedImage = array_values( wp_list_filter( $postImages, [ 'id' => $selectedImageId ] ) )[0] ?? $foundSelectedImage;

				// A seed is either one of this post's own AI images, which `edit_post` above already
				// covers, or an attachment the caller could pick in the media modal. `read_post`
				// matches what that modal offers a `upload_files` holder; `edit_post` would reject
				// library images the caller didn't upload themselves.
				if ( empty( $foundSelectedImage ) && ! current_user_can( 'read_post', $selectedImageId ) ) {
					return new \WP_REST_Response( [
						'success' => false,
						'message' => 'Unauthorized.'
					], 403 );
				}

				// Without this, any attached file would be base64'd and sent to the AI service.
				if ( ! aioseo()->helpers->attachmentIs( 'image', $selectedImageId ) ) {
					throw new \Exception( 'The selected image is not an image attachment.' );
				}
			}

			$response = aioseo()->helpers->wpRemotePost( aioseo()->ai->getAiGeneratorApiUrl() . 'image/', [
				'timeout' => 180,
				'headers' => aioseo()->ai->getRequestHeaders(),
				'body'    => wp_json_encode( [
					'prompt'      => $prompt,
					'quality'     => $quality,
					'style'       => $style,
					'aspectRatio' => $aspectRatio,
					'model'       => $model,
					'image'       => aioseo()->helpers->getBase64FromAttachment( $selectedImageId )
				] )
			] );

			// If for any reason the response is not a correctly formatted JSON, then `json_decode` returns `null`.
			$responseBody = json_decode( wp_remote_retrieve_body( $response ) );
			if ( empty( $responseBody ) ) {
				throw new \Exception( is_wp_error( $response ) ? $response->get_error_message() : 'Empty response body.' );
			}

			if ( empty( $responseBody->success ) || empty( $responseBody->data ) ) {
				if ( 'insufficient_credits' === ( $responseBody->code ?? '' ) ) {
					aioseo()->internalOptions->internal->ai->credits->remaining = $responseBody->remaining ?? 0;
				}

				// Only trust the message if `success` was explicitly set to `false` — this confirms the response came from our API's error contract.
				$message = isset( $responseBody->success ) && false === $responseBody->success && ! empty( $responseBody->message )
					? $responseBody->message
					: 'The AI service did not return image data';

				throw new \Exception( $message );
			}

			try {
				$attachment = aioseo()->ai->image->createAttachment( $responseBody->data->encodedImage, $prompt, $responseBody->data->outputFormat, $postId, [
					'quality'       => $quality,
					'style'         => $style,
					'aspectRatio'   => $aspectRatio,
					'model'         => $model,
					'parentImageId' => $foundSelectedImage['id'] ?? 0
				] );
			} catch ( \Exception $e ) {
				throw new \Exception( $e->getMessage() );
			}

			// At this point a new image was generated and saved as an attachment.
			// So if the selected image already has a parent, then remove it by simply deleting the parent meta.
			if ( ! empty( $foundSelectedImage['parentImageId'] ) ) {
				delete_post_meta( $foundSelectedImage['id'], '_aioseo_ai_parent' );
			}

			return new \WP_REST_Response( [
				'success' => true,
				'data'    => $attachment
			], 200 );
		} catch ( \Exception $e ) {
			$responseCode = isset( $response ) ? wp_remote_retrieve_response_code( $response ) : null;

			return new \WP_REST_Response( [
				'success'      => false,
				'message'      => $e->getMessage(),
				'responseCode' => $responseCode
			], 400 );
		}
	}

	/**
	 * Fetch the images generated for a post.
	 *
	 * @since 4.8.8
	 *
	 * @param  \WP_REST_Request  $request The REST Request
	 * @return \WP_REST_Response          The response.
	 */
	public static function fetchImages( $request ) {
		$params = $request->get_params();
		$postId = ! empty( $params['postId'] ) ? (int) $params['postId'] : 0;

		if ( ! current_user_can( 'edit_post', $postId ) ) {
			return new \WP_REST_Response( [
				'success' => false,
				'message' => 'Unauthorized.'
			], 403 );
		}

		$images = aioseo()->ai->image->getByPostId( $postId );

		return new \WP_REST_Response( [
			'success' => true,
			'all'     => [
				'rows' => $images
			],
			'count'   => count( $images )
		], 200 );
	}

	/**
	 * Deletes the images generated for a post.
	 *
	 * @since 4.8.8
	 *
	 * @param  \WP_REST_Request  $request The REST Request
	 * @return \WP_REST_Response          The response.
	 */
	public static function deleteImages( $request ) {
		$params = $request->get_params();
		$ids    = (array) ( $params['ids'] ?? [] );

		if ( empty( $ids ) ) {
			return new \WP_REST_Response( [
				'success' => false,
				'message' => 'Missing IDs.'
			], 400 );
		}

		// Filter to only IDs the user can delete.
		$authorizedIds   = [];
		$unauthorizedIds = [];
		foreach ( $ids as $id ) {
			$id = (int) $id;
			if ( current_user_can( 'delete_post', $id ) ) {
				$authorizedIds[] = $id;
			} else {
				$unauthorizedIds[] = $id;
			}
		}

		if ( empty( $authorizedIds ) ) {
			return new \WP_REST_Response( [
				'success' => false,
				'message' => 'Unauthorized.'
			], 403 );
		}

		aioseo()->ai->image->deleteImages( $authorizedIds );

		return new \WP_REST_Response( [
			'success'   => true,
			'failedIds' => $unauthorizedIds
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
				'message' => 'Missing post content or options.'
			], 400 );
		}

		if ( strlen( $postContent ) < aioseo()->ai->options['minContentLength'] ) {
			return new \WP_REST_Response( [
				'success' => false,
				'message' => 'Post content is too short to generate AI content.'
			], 400 );
		}

		if ( ! current_user_can( 'edit_post', $postId ) ) {
			return new \WP_REST_Response( [
				'success' => false,
				'message' => 'Unauthorized.'
			], 403 );
		}

		foreach ( $options as $k => $option ) {
			$options[ $k ] = aioseo()->helpers->sanitizeOption( $option );
		}

		foreach ( $faqs as $k => $faq ) {
			$faqs[ $k ]['question'] = sanitize_text_field( $faq['question'] );
			$faqs[ $k ]['answer']   = sanitize_text_field( $faq['answer'] );
		}

		$response = aioseo()->helpers->wpRemotePost( aioseo()->ai->getAiGeneratorApiUrl() . 'faqs/', [
			'timeout' => 60,
			'headers' => aioseo()->ai->getRequestHeaders(),
			'body'    => wp_json_encode( [
				'postContent' => $postContent,
				'tone'        => $options['tone'],
				'audience'    => $options['audience'],
				'rephrase'    => $rephrase,
				'faqs'        => $faqs
			] ),
		] );

		$responseBody = json_decode( wp_remote_retrieve_body( $response ) );
		$responseCode = wp_remote_retrieve_response_code( $response );

		// Only trust the message if `success` was explicitly set to `false` — this confirms the response came from our API's error contract.
		$serviceError = isset( $responseBody->success ) && false === $responseBody->success && ! empty( $responseBody->message ) ? 'Service error: ' . $responseBody->message : null;
		$errorDetails = array_filter( [ "Service response code: $responseCode", $serviceError ] );

		// `insufficient_credits` arrives with a 402, so detect it before the generic
		// non-200 guard below — otherwise the credit-specific detail is never added.
		if ( ! empty( $responseBody->code ) && 'insufficient_credits' === $responseBody->code ) {
			aioseo()->internalOptions->internal->ai->credits->remaining = $responseBody->remaining ?? 0;

			$errorDetails[] = 'Not enough credits';

			return new \WP_REST_Response( [
				'success' => false,
				'message' => implode( ' | ', $errorDetails )
			], 400 );
		}

		if ( 200 !== $responseCode ) {
			$errorDetails[] = 'The AI service returned an unexpected response';

			return new \WP_REST_Response( [
				'success' => false,
				'message' => implode( ' | ', $errorDetails )
			], 400 );
		}

		$faqs = ! empty( $responseBody->faqs ) ? aioseo()->helpers->sanitizeOption( $responseBody->faqs ) : [];
		if ( empty( $responseBody->success ) || empty( $faqs ) ) {
			$errorDetails[] = 'The AI service did not return any FAQ suggestions';

			return new \WP_REST_Response( [
				'success' => false,
				'message' => implode( ' | ', $errorDetails )
			], 400 );
		}

		aioseo()->ai->updateAiOptions( $responseBody );

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
			'aiOptions' => self::getAiOptionsPayload()
		], 200 );
	}

	/**
	 * Generates schema markup based on the provided content.
	 *
	 * @since   4.9.6
	 * @version 5.0.3 Forwards the error code of a failed generation.
	 *
	 * @param  \WP_REST_Request  $request The REST Request
	 * @return \WP_REST_Response          The response.
	 */
	public static function generateSchemas( $request ) {
		try {
			$body        = $request->get_json_params();
			$postId      = ! empty( $body['postId'] ) ? (int) $body['postId'] : 0;
			$postContent = ! empty( $body['postContent'] ) ? $body['postContent'] : '';

			if ( ! current_user_can( 'edit_post', $postId ) ) {
				throw new ApiException( 'unauthorized', 'Unauthorized.', 403 );
			}

			$wpObject = $postId ? aioseo()->helpers->getPost( $postId ) : null;

			if ( empty( $postContent ) && $postId ) {
				if ( ! $wpObject ) {
					throw new ApiException( 'post_not_found', 'Post not found.' );
				}

				$postContent = aioseo()->helpers->getPostContent( $wpObject );
			}

			if ( empty( $postContent ) ) {
				throw new ApiException( 'no_content', 'Missing post content.' );
			}

			$result = aioseo()->ai->generateSchemas( $body );

			if ( ! $result['success'] ) {
				throw new ApiException( $result['code'] ?? 'generation_failed', esc_html( $result['message'] ) );
			}

			return new \WP_REST_Response( [
				'success'   => true,
				'schemas'   => $result['schemas'],
				'aiOptions' => self::getAiOptionsPayload()
			], 200 );
		} catch ( ApiException $e ) {
			return new \WP_REST_Response( [
				'success' => false,
				'message' => $e->getMessage(),
				'code'    => $e->getErrorCode()
			], $e->getCode() );
		}
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
				'message' => 'Missing post content or options.'
			], 400 );
		}

		if ( strlen( $postContent ) < aioseo()->ai->options['minContentLength'] ) {
			return new \WP_REST_Response( [
				'success' => false,
				'message' => 'Post content is too short to generate AI content.'
			], 400 );
		}

		if ( ! current_user_can( 'edit_post', $postId ) ) {
			return new \WP_REST_Response( [
				'success' => false,
				'message' => 'Unauthorized.'
			], 403 );
		}

		foreach ( $options as $k => $option ) {
			$options[ $k ] = aioseo()->helpers->sanitizeOption( $option );
		}

		foreach ( $keyPoints as $k => $keyPoint ) {
			$keyPoints[ $k ]['title']       = sanitize_text_field( $keyPoint['title'] );
			$keyPoints[ $k ]['explanation'] = sanitize_text_field( $keyPoint['explanation'] );
		}

		$response = aioseo()->helpers->wpRemotePost( aioseo()->ai->getAiGeneratorApiUrl() . 'key-points/', [
			'timeout' => 60,
			'headers' => aioseo()->ai->getRequestHeaders(),
			'body'    => wp_json_encode( [
				'postContent' => $postContent,
				'tone'        => $options['tone'],
				'audience'    => $options['audience'],
				'rephrase'    => $rephrase,
				'keyPoints'   => $keyPoints
			] ),
		] );

		$responseBody = json_decode( wp_remote_retrieve_body( $response ) );
		$responseCode = wp_remote_retrieve_response_code( $response );

		// Only trust the message if `success` was explicitly set to `false` — this confirms the response came from our API's error contract.
		$serviceError = isset( $responseBody->success ) && false === $responseBody->success && ! empty( $responseBody->message ) ? 'Service error: ' . $responseBody->message : null;
		$errorDetails = array_filter( [ "Service response code: $responseCode", $serviceError ] );

		// `insufficient_credits` arrives with a 402, so detect it before the generic
		// non-200 guard below — otherwise the credit-specific detail is never added.
		if ( ! empty( $responseBody->code ) && 'insufficient_credits' === $responseBody->code ) {
			aioseo()->internalOptions->internal->ai->credits->remaining = $responseBody->remaining ?? 0;

			$errorDetails[] = 'Not enough credits';

			return new \WP_REST_Response( [
				'success' => false,
				'message' => implode( ' | ', $errorDetails )
			], 400 );
		}

		if ( 200 !== $responseCode ) {
			$errorDetails[] = 'The AI service returned an unexpected response';

			return new \WP_REST_Response( [
				'success' => false,
				'message' => implode( ' | ', $errorDetails )
			], 400 );
		}

		$keyPoints = ! empty( $responseBody->keyPoints ) ? aioseo()->helpers->sanitizeOption( $responseBody->keyPoints ) : [];
		if ( empty( $responseBody->success ) || empty( $keyPoints ) ) {
			$errorDetails[] = 'The AI service did not return any key point suggestions';

			return new \WP_REST_Response( [
				'success' => false,
				'message' => implode( ' | ', $errorDetails )
			], 400 );
		}

		aioseo()->ai->updateAiOptions( $responseBody );

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
			'aiOptions' => self::getAiOptionsPayload()
		], 200 );
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

		// Reset the manually connected flag when disconnecting.
		$internalOptions->internal->ai->isManuallyConnected = false;

		aioseo()->ai->getAccessToken( true );

		return new \WP_REST_Response( [
			'success' => true,
			'aiData'  => self::getAiOptionsPayload()
		], 200 );
	}

	/**
	 * Returns the AI options payload for API responses.
	 *
	 * This helper ensures we never accidentally expose the access token
	 * and maintains consistency across all AI API endpoints.
	 *
	 * @since 4.9.4
	 *
	 * @return array The AI options payload.
	 */
	public static function getAiOptionsPayload() {
		return [
			'hasAccessToken'      => aioseo()->sensitiveOptions->hasValue( 'aiAccessToken' ),
			'isTrialAccessToken'  => aioseo()->internalOptions->internal->ai->isTrialAccessToken,
			'isManuallyConnected' => aioseo()->internalOptions->internal->ai->isManuallyConnected,
			'credits'             => aioseo()->internalOptions->internal->ai->credits->all(),
			'costPerFeature'      => aioseo()->internalOptions->internal->ai->costPerFeature
		];
	}

	/**
	 * Generates AI suggestions for a batch of same-type TruSEO Highlighter issues.
	 *
	 * Proxies the flagged items (each with bounded local context) plus shared context
	 * (analyzer/scope/keyphrase/locale/options) to the remote AI Generator endpoint at
	 * `{aiGeneratorApiUrl}/truseo/suggest/` in a single request. The remote service owns
	 * the per-analyzer prompt construction and returns a `results` envelope keyed by issue
	 * id that the frontend fans out into its per-item suggestion cache.
	 *
	 * @since 5.0.0
	 *
	 * @param  \WP_REST_Request  $request The REST Request.
	 * @return \WP_REST_Response          The response.
	 */
	public static function generateTruSeoSuggestion( $request ) {
		$body           = $request->get_json_params();
		$postId         = ! empty( $body['postId'] ) ? (int) $body['postId'] : 0;
		$analyzer       = ! empty( $body['analyzer'] ) ? sanitize_text_field( $body['analyzer'] ) : '';
		$scope          = ! empty( $body['scope'] ) ? sanitize_key( $body['scope'] ) : '';
		$focusKeyphrase = ! empty( $body['focusKeyphrase'] ) ? sanitize_text_field( $body['focusKeyphrase'] ) : '';
		$focusSynonyms  = ! empty( $body['focusSynonyms'] ) ? sanitize_text_field( $body['focusSynonyms'] ) : '';
		$locale         = ! empty( $body['locale'] ) ? sanitize_text_field( $body['locale'] ) : get_locale();
		$rephrase       = isset( $body['rephrase'] ) ? boolval( $body['rephrase'] ) : false;
		$options        = $body['options'] ?? [];
		$issues         = isset( $body['issues'] ) && is_array( $body['issues'] ) ? $body['issues'] : [];

		$allowedAnalyzers = [
			'passiveVoice',
			'textSentenceLength',
			'textParagraphTooLong',
			'sentenceBeginnings',
			'textTransitionWords',
			'wordComplexity',
			'textCompetingLinks',
			'keyphraseDistribution',
			'keyphraseDensity'
		];
		$allowedScopes = [
			'word',
			'sentence',
			'paragraph',
			'section',
			'anchor'
		];

		if ( ! $postId || ! $analyzer || ! $scope || empty( $issues ) ) {
			return new \WP_REST_Response( [
				'success' => false,
				'message' => 'Missing required parameters.'
			], 400 );
		}

		if ( ! in_array( $analyzer, $allowedAnalyzers, true ) ) {
			return new \WP_REST_Response( [
				'success' => false,
				'message' => 'Unsupported analyzer.'
			], 400 );
		}

		if ( ! in_array( $scope, $allowedScopes, true ) ) {
			return new \WP_REST_Response( [
				'success' => false,
				'message' => 'Unsupported scope.'
			], 400 );
		}

		if ( ! current_user_can( 'edit_post', $postId ) ) {
			return new \WP_REST_Response( [
				'success' => false,
				'message' => 'Unauthorized.'
			], 403 );
		}

		// Sanitize options.
		foreach ( $options as $k => $option ) {
			$options[ $k ] = aioseo()->helpers->sanitizeOption( $option );
		}

		// Normalize and sanitize the issue batch, dropping empties/duplicates and
		// capping the count so one remote LLM call stays bounded.
		$maxIssues       = 25;
		$sanitizedIssues = [];
		$seenIds         = [];
		foreach ( $issues as $issue ) {
			$issue      = (array) $issue;
			$id         = isset( $issue['id'] ) ? sanitize_text_field( (string) $issue['id'] ) : '';
			$targetText = ! empty( $issue['targetText'] ) ? wp_kses_post( wp_unslash( $issue['targetText'] ) ) : '';

			if ( '' === $id || '' === $targetText || isset( $seenIds[ $id ] ) ) {
				continue;
			}
			$seenIds[ $id ] = true;

			$metadata = isset( $issue['metadata'] ) && is_array( $issue['metadata'] ) ? $issue['metadata'] : [];
			foreach ( $metadata as $k => $value ) {
				$metadata[ $k ] = is_scalar( $value ) ? sanitize_text_field( (string) $value ) : '';
			}

			$sanitizedIssues[] = [
				'id'          => $id,
				'targetText'  => $targetText,
				'contextText' => ! empty( $issue['contextText'] ) ? wp_kses_post( wp_unslash( $issue['contextText'] ) ) : '',
				'metadata'    => $metadata
			];

			if ( count( $sanitizedIssues ) >= $maxIssues ) {
				break;
			}
		}

		if ( empty( $sanitizedIssues ) ) {
			return new \WP_REST_Response( [
				'success' => false,
				'message' => 'Missing required parameters.'
			], 400 );
		}

		$response = aioseo()->helpers->wpRemotePost( untrailingslashit( aioseo()->ai->getAiGeneratorApiUrl() ) . '/truseo/suggest/', [
			'timeout' => 60,
			'headers' => aioseo()->ai->getRequestHeaders(),
			'body'    => wp_json_encode( [
				'analyzer'       => $analyzer,
				'scope'          => $scope,
				'focusKeyphrase' => $focusKeyphrase,
				'focusSynonyms'  => $focusSynonyms,
				'locale'         => $locale,
				'tone'           => $options['tone'] ?? '',
				'audience'       => $options['audience'] ?? '',
				'rephrase'       => $rephrase,
				'issues'         => $sanitizedIssues
			] )
		] );

		$responseCode = wp_remote_retrieve_response_code( $response );
		$responseBody = json_decode( wp_remote_retrieve_body( $response ) );

		// `insufficient_credits` arrives with a 402, so detect it before the generic
		// non-200 guard below — otherwise the actionable message is never reached.
		if ( ! empty( $responseBody->code ) && 'insufficient_credits' === $responseBody->code ) {
			aioseo()->internalOptions->internal->ai->credits->remaining = $responseBody->remaining ?? 0;

			return new \WP_REST_Response( [
				'success'   => false,
				'code'      => 'insufficient_credits',
				'message'   => 'Not enough AI credits to generate suggestions.',
				'aiOptions' => self::getAiOptionsPayload()
			], 402 );
		}

		if ( 200 !== $responseCode || empty( $responseBody ) || empty( $responseBody->success ) ) {
			return new \WP_REST_Response( [
				'success' => false,
				'message' => 'Failed to generate AI suggestions.'
			], 400 );
		}

		$results = [];
		if ( ! empty( $responseBody->results ) && is_array( $responseBody->results ) ) {
			foreach ( $responseBody->results as $result ) {
				$id = isset( $result->id ) ? sanitize_text_field( (string) $result->id ) : '';
				if ( '' === $id ) {
					continue;
				}

				$suggestions = [];
				if ( ! empty( $result->suggestions ) && is_array( $result->suggestions ) ) {
					foreach ( $result->suggestions as $suggestion ) {
						$text = isset( $suggestion->text ) ? (string) $suggestion->text : '';

						// Preserve heading markers and paragraph breaks the frontend depends on.
						if ( in_array( $scope, [ 'section', 'paragraph' ], true ) ) {
							$text = wp_strip_all_tags( $text );
						} else {
							$text = sanitize_text_field( $text );
						}

						$suggestions[] = [
							'text'            => aioseo()->helpers->decodeHtmlEntities( $text ),
							'rationale'       => isset( $suggestion->rationale ) ? sanitize_text_field( (string) $suggestion->rationale ) : '',
							'replaceStrategy' => isset( $suggestion->replaceStrategy ) ? sanitize_key( $suggestion->replaceStrategy ) : ''
						];
					}
				}

				$results[] = [
					'id'          => $id,
					'suggestions' => $suggestions
				];
			}
		}

		aioseo()->ai->updateAiOptions( $responseBody );

		return new \WP_REST_Response( [
			'success'   => true,
			'results'   => $results,
			'aiOptions' => self::getAiOptionsPayload()
		], 200 );
	}

	/**
	 * Optimizes an entire post — SEO title, meta description, focus keyword, headline (H1),
	 * and body text blocks — via the AI Generator's `{aiGeneratorApiUrl}/truseo/optimize-post/`
	 * endpoint. The body is sent as an ordered list of text blocks; the service rewrites the
	 * blocks that need work and returns the replacement block(s) per id. This proxies the request,
	 * syncs credits, and returns the optimized fields plus the per-block content changes.
	 *
	 * @since 5.0.0
	 *
	 * @param  \WP_REST_Request  $request The REST Request.
	 * @return \WP_REST_Response          The response.
	 */
	public static function generateTruSeoOptimizePost( $request ) {
		$body           = $request->get_json_params();
		$postId         = ! empty( $body['postId'] ) ? (int) $body['postId'] : 0;
		$seoTitle       = isset( $body['seo_title'] ) ? sanitize_text_field( (string) $body['seo_title'] ) : '';
		$seoDescription = isset( $body['seo_description'] ) ? sanitize_text_field( (string) $body['seo_description'] ) : '';
		$focusKeyword   = isset( $body['focus_keyword'] ) ? sanitize_text_field( (string) $body['focus_keyword'] ) : '';
		$postTitle      = isset( $body['post_title'] ) ? sanitize_text_field( (string) $body['post_title'] ) : '';
		$locale         = ! empty( $body['locale'] ) ? sanitize_text_field( $body['locale'] ) : get_locale();
		$options        = is_array( $body['options'] ?? null ) ? $body['options'] : [];
		$content        = self::sanitizeOptimizePostContent( $body['content'] ?? null );

		if ( ! $postId || empty( $content ) ) {
			return new \WP_REST_Response( [
				'success' => false,
				'message' => 'Missing required parameters.'
			], 400 );
		}

		if ( ! current_user_can( 'edit_post', $postId ) ) {
			return new \WP_REST_Response( [
				'success' => false,
				'message' => 'Unauthorized.'
			], 403 );
		}

		foreach ( $options as $k => $option ) {
			$options[ $k ] = aioseo()->helpers->sanitizeOption( $option );
		}

		$response = aioseo()->helpers->wpRemotePost( untrailingslashit( aioseo()->ai->getAiGeneratorApiUrl() ) . '/truseo/optimize-post/', [
			'timeout' => 60,
			'headers' => aioseo()->ai->getRequestHeaders(),
			'body'    => wp_json_encode( [
				'seo_title'       => $seoTitle,
				'seo_description' => $seoDescription,
				'focus_keyword'   => $focusKeyword,
				'post_title'      => $postTitle,
				'locale'          => $locale,
				'options'         => $options,
				'content'         => $content
			] )
		] );

		$responseCode = wp_remote_retrieve_response_code( $response );
		$responseBody = json_decode( wp_remote_retrieve_body( $response ) );

		// The service signals an exhausted balance with HTTP 402 and an
		// `insufficient_credits` code, so this must run before the generic non-200
		// guard below — otherwise the actionable message is never reached.
		if ( ! empty( $responseBody->code ) && 'insufficient_credits' === $responseBody->code ) {
			aioseo()->internalOptions->internal->ai->credits->remaining = $responseBody->remaining ?? 0;

			return new \WP_REST_Response( [
				'success'   => false,
				'code'      => 'insufficient_credits',
				'message'   => 'Not enough AI credits to optimize this post.',
				'aiOptions' => self::getAiOptionsPayload()
			], 402 );
		}

		if ( 200 !== $responseCode || empty( $responseBody ) || empty( $responseBody->success ) ) {
			return new \WP_REST_Response( [
				'success' => false,
				'message' => 'We couldn\'t optimize your post right now. Please try again in a moment.'
			], 400 );
		}

		aioseo()->ai->updateAiOptions( $responseBody );

		return new \WP_REST_Response( [
			'success'         => true,
			'seo_title'       => isset( $responseBody->seo_title ) ? sanitize_text_field( (string) $responseBody->seo_title ) : $seoTitle,
			'seo_description' => isset( $responseBody->seo_description ) ? sanitize_text_field( (string) $responseBody->seo_description ) : $seoDescription,
			'focus_keyword'   => isset( $responseBody->focus_keyword ) ? sanitize_text_field( (string) $responseBody->focus_keyword ) : $focusKeyword,
			'headline'        => isset( $responseBody->headline ) ? sanitize_text_field( (string) $responseBody->headline ) : $postTitle,
			'content'         => self::sanitizeOptimizePostResponseContent( $responseBody->content ?? null ),
			'aiOptions'       => self::getAiOptionsPayload()
		], 200 );
	}

	/**
	 * Corrects a batch of misspelled words via the AI Generator's
	 * `{aiGeneratorApiUrl}/truseo/spelling/` endpoint. Each flagged word is sent with its
	 * Hunspell suggestions (and optional context); the service returns the best correction
	 * per word, or flags legitimate brand/technical words for the dictionary. This proxies
	 * the request, syncs credits, handles `insufficient_credits`, and returns the sanitized
	 * results. An empty `correction` with `addToDictionary` false means leave the word as-is.
	 *
	 * @since 5.0.0
	 *
	 * @param  \WP_REST_Request  $request The REST Request.
	 * @return \WP_REST_Response          The response.
	 */
	public static function generateTruSeoSpelling( $request ) {
		$body    = $request->get_json_params();
		$postId  = ! empty( $body['postId'] ) ? (int) $body['postId'] : 0;
		$locale  = ! empty( $body['locale'] ) ? sanitize_text_field( $body['locale'] ) : get_locale();
		$options = is_array( $body['options'] ?? null ) ? $body['options'] : [];
		$words   = isset( $body['words'] ) && is_array( $body['words'] ) ? $body['words'] : [];

		if ( ! $postId || empty( $words ) ) {
			return new \WP_REST_Response( [
				'success' => false,
				'message' => 'Missing required parameters.'
			], 400 );
		}

		if ( ! current_user_can( 'edit_post', $postId ) ) {
			return new \WP_REST_Response( [
				'success' => false,
				'message' => 'Unauthorized.'
			], 403 );
		}

		foreach ( $options as $k => $option ) {
			$options[ $k ] = aioseo()->helpers->sanitizeOption( $option );
		}

		// Normalize the word batch: drop empties/duplicates and cap the count and each
		// word's suggestion list so one remote fan-out stays bounded.
		$maxWords         = 50;
		$maxSuggestions   = 15;
		$sanitizedWords   = [];
		$seenWords        = [];
		foreach ( $words as $entry ) {
			$entry = (array) $entry;
			$word  = isset( $entry['word'] ) ? sanitize_text_field( (string) $entry['word'] ) : '';
			if ( '' === $word || isset( $seenWords[ $word ] ) ) {
				continue;
			}
			$seenWords[ $word ] = true;

			$cleanSuggestions = [];
			$suggestions      = isset( $entry['suggestions'] ) && is_array( $entry['suggestions'] ) ? $entry['suggestions'] : [];
			foreach ( $suggestions as $suggestion ) {
				$suggestion = sanitize_text_field( (string) $suggestion );
				if ( '' === $suggestion ) {
					continue;
				}

				$cleanSuggestions[] = $suggestion;
				if ( count( $cleanSuggestions ) >= $maxSuggestions ) {
					break;
				}
			}

			$context = isset( $entry['context'] ) ? sanitize_textarea_field( (string) $entry['context'] ) : '';

			$sanitizedWords[] = [
				'word'        => $word,
				'suggestions' => $cleanSuggestions,
				'context'     => mb_substr( $context, 0, 1000 )
			];

			if ( count( $sanitizedWords ) >= $maxWords ) {
				break;
			}
		}

		if ( empty( $sanitizedWords ) ) {
			return new \WP_REST_Response( [
				'success' => false,
				'message' => 'Missing required parameters.'
			], 400 );
		}

		$response = aioseo()->helpers->wpRemotePost( untrailingslashit( aioseo()->ai->getAiGeneratorApiUrl() ) . '/truseo/spelling/', [
			'timeout' => 60,
			'headers' => aioseo()->ai->getRequestHeaders(),
			'body'    => wp_json_encode( [
				'words'    => $sanitizedWords,
				'locale'   => $locale,
				'tone'     => $options['tone'] ?? '',
				'audience' => $options['audience'] ?? ''
			] )
		] );

		$responseCode = wp_remote_retrieve_response_code( $response );
		$responseBody = json_decode( wp_remote_retrieve_body( $response ) );

		// `insufficient_credits` arrives with a 402, so detect it before the generic
		// non-200 guard below — otherwise the actionable message is never reached.
		if ( ! empty( $responseBody->code ) && 'insufficient_credits' === $responseBody->code ) {
			aioseo()->internalOptions->internal->ai->credits->remaining = $responseBody->remaining ?? 0;

			return new \WP_REST_Response( [
				'success'   => false,
				'code'      => 'insufficient_credits',
				'message'   => 'Not enough AI credits to correct spelling.',
				'aiOptions' => self::getAiOptionsPayload()
			], 402 );
		}

		if ( 200 !== $responseCode || empty( $responseBody ) || empty( $responseBody->success ) ) {
			return new \WP_REST_Response( [
				'success' => false,
				'message' => 'Failed to correct spelling.'
			], 400 );
		}

		$results = [];
		if ( ! empty( $responseBody->results ) && is_array( $responseBody->results ) ) {
			foreach ( $responseBody->results as $result ) {
				$word = isset( $result->word ) ? sanitize_text_field( (string) $result->word ) : '';
				if ( '' === $word ) {
					continue;
				}

				$results[] = [
					'word'            => $word,
					'correction'      => isset( $result->correction ) ? sanitize_text_field( (string) $result->correction ) : '',
					'addToDictionary' => isset( $result->addToDictionary ) ? (bool) $result->addToDictionary : false
				];
			}
		}

		aioseo()->ai->updateAiOptions( $responseBody );

		return new \WP_REST_Response( [
			'success'   => true,
			'results'   => $results,
			'aiOptions' => self::getAiOptionsPayload()
		], 200 );
	}

	/**
	 * Validates + sanitizes the incoming post-body blocks for optimize-post.
	 *
	 * @since 5.0.0
	 *
	 * @param  mixed $content The raw content value from the request.
	 * @return array          The sanitized blocks ( id, type, level, text ), or [] if invalid.
	 */
	private static function sanitizeOptimizePostContent( $content ) {
		if ( ! is_array( $content ) ) {
			return [];
		}

		$blocks = [];
		foreach ( $content as $item ) {
			if ( ! is_array( $item ) ) {
				continue;
			}

			$id   = isset( $item['id'] ) ? sanitize_text_field( (string) $item['id'] ) : '';
			$text = isset( $item['text'] ) ? sanitize_textarea_field( (string) $item['text'] ) : '';
			if ( '' === $id || '' === trim( $text ) ) {
				continue;
			}

			$type  = 'heading' === ( $item['type'] ?? '' ) ? 'heading' : 'paragraph';
			$level = (int) ( $item['level'] ?? 0 );

			$blocks[] = [
				'id'    => $id,
				'type'  => $type,
				'level' => 'heading' === $type ? min( 4, max( 2, $level ) ) : 0,
				'text'  => mb_substr( $text, 0, 10000 )
			];
		}

		return $blocks;
	}

	/**
	 * Sanitizes the optimizer's per-block results before returning them to the client.
	 *
	 * @since 5.0.0
	 *
	 * @param  mixed $content The `content` array from the service response (stdClass items).
	 * @return array          The sanitized results ( id, blocks[] ).
	 */
	private static function sanitizeOptimizePostResponseContent( $content ) {
		if ( ! is_array( $content ) ) {
			return [];
		}

		$results = [];
		foreach ( $content as $result ) {
			$id     = isset( $result->id ) ? sanitize_text_field( (string) $result->id ) : '';
			$blocks = isset( $result->blocks ) && is_array( $result->blocks ) ? $result->blocks : [];
			if ( '' === $id || empty( $blocks ) ) {
				continue;
			}

			$cleanBlocks = [];
			foreach ( $blocks as $block ) {
				$text = isset( $block->text ) ? (string) $block->text : '';
				if ( '' === trim( $text ) ) {
					continue;
				}

				$type  = 'heading' === ( $block->type ?? '' ) ? 'heading' : 'paragraph';
				$level = (int) ( $block->level ?? 0 );

				$cleanBlocks[] = [
					'type'  => $type,
					'level' => 'heading' === $type ? min( 4, max( 2, $level ) ) : 0,
					'text'  => wp_kses_post( $text )
				];
			}

			if ( ! empty( $cleanBlocks ) ) {
				$results[] = [
					'id'     => $id,
					'blocks' => $cleanBlocks
				];
			}
		}

		return $results;
	}
}