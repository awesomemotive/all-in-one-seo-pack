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
	 * @since 4.8.4
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
				throw new ApiException( 'unauthorized', 'Unauthorized.', 401 );
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
				throw new ApiException( 'generation_failed', esc_html( $result['message'] ) );
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
	 * @since 4.8.4
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
				throw new ApiException( 'unauthorized', 'Unauthorized.', 401 );
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
				throw new ApiException( 'generation_failed', esc_html( $result['message'] ) );
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
				throw new ApiException( 'unauthorized', 'Unauthorized.', 401 );
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
			], 401 );
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

		if ( 200 !== $responseCode ) {
			$errorDetails[] = 'The AI service returned an unexpected response';

			return new \WP_REST_Response( [
				'success' => false,
				'message' => implode( ' | ', $errorDetails )
			], 400 );
		}

		if ( empty( $responseBody->success ) || empty( $responseBody->snippets ) ) {
			if ( ! empty( $responseBody->code ) && 'insufficient_credits' === $responseBody->code ) {
				aioseo()->internalOptions->internal->ai->credits->remaining = $responseBody->remaining ?? 0;

				$errorDetails[] = 'Not enough credits';

				return new \WP_REST_Response( [
					'success' => false,
					'message' => implode( ' | ', $errorDetails )
				], 400 );
			}

			$errorDetails[] = 'The AI service did not return any social post suggestions';

			return new \WP_REST_Response( [
				'success' => false,
				'message' => implode( ' | ', $errorDetails )
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
	 * @since 4.8.8
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

		if ( ! current_user_can( 'edit_post', $postId ) ) {
			return new \WP_REST_Response( [
				'success' => false,
				'message' => 'Unauthorized.'
			], 401 );
		}

		try {
			if ( ! $prompt || ! $postId ) {
				throw new \Exception( 'Missing prompt or post ID.' );
			}

			$postImages         = aioseo()->ai->image->getByPostId( $postId );
			$foundSelectedImage = [];

			if ( ! empty( $selectedImageId ) ) {
				$foundSelectedImage = wp_list_filter( $postImages, [ 'id' => $selectedImageId ] )[0] ?? $foundSelectedImage;
			}

			$response = aioseo()->helpers->wpRemotePost( aioseo()->ai->getAiGeneratorApiUrl() . 'image/', [
				'timeout' => 180,
				'headers' => aioseo()->ai->getRequestHeaders(),
				'body'    => wp_json_encode( [
					'prompt'      => $prompt,
					'quality'     => $quality,
					'style'       => $style,
					'aspectRatio' => $aspectRatio,
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
			], 401 );
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
			], 401 );
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
			], 401 );
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

		if ( 200 !== $responseCode ) {
			$errorDetails[] = 'The AI service returned an unexpected response';

			return new \WP_REST_Response( [
				'success' => false,
				'message' => implode( ' | ', $errorDetails )
			], 400 );
		}

		$faqs = ! empty( $responseBody->faqs ) ? aioseo()->helpers->sanitizeOption( $responseBody->faqs ) : [];
		if ( empty( $responseBody->success ) || empty( $faqs ) ) {
			if ( ! empty( $responseBody->code ) && 'insufficient_credits' === $responseBody->code ) {
				aioseo()->internalOptions->internal->ai->credits->remaining = $responseBody->remaining ?? 0;

				$errorDetails[] = 'Not enough credits';

				return new \WP_REST_Response( [
					'success' => false,
					'message' => implode( ' | ', $errorDetails )
				], 400 );
			}

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
	 * @since 4.9.6
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
				throw new ApiException( 'unauthorized', 'Unauthorized.', 401 );
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
				throw new ApiException( 'generation_failed', esc_html( $result['message'] ) );
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
			], 401 );
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

		if ( 200 !== $responseCode ) {
			$errorDetails[] = 'The AI service returned an unexpected response';

			return new \WP_REST_Response( [
				'success' => false,
				'message' => implode( ' | ', $errorDetails )
			], 400 );
		}

		$keyPoints = ! empty( $responseBody->keyPoints ) ? aioseo()->helpers->sanitizeOption( $responseBody->keyPoints ) : [];
		if ( empty( $responseBody->success ) || empty( $keyPoints ) ) {
			if ( ! empty( $responseBody->code ) && 'insufficient_credits' === $responseBody->code ) {
				aioseo()->internalOptions->internal->ai->credits->remaining = $responseBody->remaining ?? 0;

				$errorDetails[] = 'Not enough credits';

				return new \WP_REST_Response( [
					'success' => false,
					'message' => implode( ' | ', $errorDetails )
				], 400 );
			}

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
}