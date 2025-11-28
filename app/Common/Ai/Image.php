<?php
namespace AIOSEO\Plugin\Common\Ai;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * AI Image handler for managing WordPress attachments.
 *
 * @since 4.8.8
 */
class Image {
	/**
	 * The hook name for generating image metadata.
	 *
	 * @since 4.8.8
	 *
	 * @var string
	 */
	private $generateImageMetadataHook = 'aioseo_generate_ai_image_metadata';

	/**
	 * Class constructor.
	 *
	 * @since 4.8.8
	 */
	public function __construct() {
		add_action( $this->generateImageMetadataHook, [ $this, 'generateImageMetadata' ], 10, 2 );
	}

	/**
	 * Returns the data for Vue.
	 *
	 * @since 4.8.9
	 *
	 * @param  int|null $objectId The object ID.
	 * @return array              The data.
	 */
	public function getVueDataEdit( $objectId = null ) {
		$objectId = $objectId ?: absint( get_the_ID() );

		return [
			'extend' => [
				'imageBlockToolbar'     => apply_filters( 'aioseo_ai_image_generator_extend_image_block_toolbar', true, $objectId ),
				'imageBlockPlaceholder' => apply_filters( 'aioseo_ai_image_generator_extend_image_block_placeholder', true, $objectId ),
				'featuredImageButton'   => apply_filters( 'aioseo_ai_image_generator_extend_featured_image_button', true, $objectId ),
			]
		];
	}

	/**
	 * Creates a WordPress attachment from base64 image data.
	 *
	 * @since 4.8.8
	 *
	 * @param  string      $base64Data The base64 encoded image data.
	 * @param  string      $prompt     The AI prompt used to generate the image.
	 * @param  string      $format     The image format (jpg, png, etc.).
	 * @param  int         $postId     The post ID to attach the image to.
	 * @param  array       $metadata   Additional metadata (quality, style, aspectRatio, etc.).
	 * @return array                   The attachment data on success, false on failure.
	 * @throws \Exception              If the attachment creation fails.
	 */
	public function createAttachment( $base64Data, $prompt, $format, $postId, $metadata = [] ) {
		if ( empty( $base64Data ) || empty( $prompt ) || empty( $format ) || empty( $postId ) ) {
			throw new \Exception( 'Invalid parameters.' );
		}

		$imageData = base64_decode( $base64Data );
		if ( false === $imageData ) {
			throw new \Exception( 'Failed to decode base64 image data.' );
		}

		if ( ! in_array( $format, aioseo()->helpers->getAllowedImageExtensions(), true ) ) {
			throw new \Exception( 'Invalid image format.' );
		}

		$quality     = trim( $metadata['quality'] ?? '' );
		$style       = trim( $metadata['style'] ?? '' );
		$aspectRatio = trim( $metadata['aspectRatio'] ?? '' );

		$filenameContext = substr( $prompt, 0, 25 ) . '-' . $quality . '-' . $style . '-' . $aspectRatio . '-' . date( 'Ymd-His' );
		$filename        = 'aioseo-ai-' . aioseo()->helpers->toLowerCase( sanitize_file_name( $filenameContext ) ) . '.' . $format;

		$upload = wp_upload_bits( $filename, null, $imageData );
		if ( ! empty( $upload['error'] ) ) {
			throw new \Exception( esc_html( sprintf( 'Failed to upload image. Error: %s', $upload['error'] ) ) );
		}

		$attachmentData = [
			'post_title'     => substr( $prompt, 0, 60 ),
			'post_content'   => '',
			'post_parent'    => $postId,
			'post_mime_type' => 'image/' . $format,
			'guid'           => $upload['url']
		];

		$attachmentId = wp_insert_attachment( $attachmentData, $upload['file'], $postId, true );
		if ( is_wp_error( $attachmentId ) ) {
			wp_delete_file( $upload['file'] );

			throw new \Exception( esc_html( sprintf( 'Failed to insert attachment. Error: %s', $attachmentId->get_error_message() ) ) );
		}

		if ( ! $attachmentId ) {
			wp_delete_file( $upload['file'] );

			throw new \Exception( 'Failed to insert attachment. No attachment ID returned.' );
		}

		update_post_meta( $attachmentId, '_aioseo_ai_generated', 1 );
		update_post_meta( $attachmentId, '_aioseo_ai_data', [
			'prompt'      => $prompt,
			'quality'     => $quality,
			'style'       => $style,
			'aspectRatio' => $aspectRatio
		] );

		$parentImageId = ! empty( $metadata['parentImageId'] ) ? (int) $metadata['parentImageId'] : 0;
		if ( $parentImageId ) {
			update_post_meta( $attachmentId, '_aioseo_ai_parent', $parentImageId );
		}

		// Generate attachment metadata (thumbnails) asynchronously via Action Scheduler to avoid timeout.
		aioseo()->actionScheduler->scheduleAsync( $this->generateImageMetadataHook, [ $attachmentId, $upload['file'] ] );

		$src = wp_get_attachment_image_src( $attachmentId, 'full' );

		list( $url, $width, $height ) = $src;

		if ( ! $width || ! $height ) {
			list( $width, $height ) = [ 0, 0 ];
			$wpImageSize = wp_getimagesize( $upload['file'] );
			if ( $wpImageSize ) {
				list( $width, $height ) = $wpImageSize;
			}
		}

		return [
			'alt'           => trim( wp_strip_all_tags( get_post_meta( $attachmentId, '_wp_attachment_image_alt', true ) ) ),
			'aspectRatio'   => $aspectRatio,
			'format'        => $format,
			'height'        => $height,
			'id'            => $attachmentId,
			'parentImageId' => $parentImageId,
			'prompt'        => $prompt,
			'quality'       => $quality,
			'style'         => $style,
			'url'           => $url,
			'width'         => $width,
		];
	}

	/**
	 * Gets AI-generated images for a specific post.
	 *
	 * @since 4.8.8
	 *
	 * @param  int   $postId The post ID.
	 * @return array         Array of AI image data.
	 */
	public function getByPostId( $postId ) {
		$images = [];
		if ( empty( $postId ) ) {
			return $images;
		}

		// Get all attachments for this post that are AI-generated.
		$attachmentIds = get_posts( [
			'post_type'      => 'attachment',
			'post_parent'    => $postId,
			'post_status'    => 'inherit',
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'meta_key'       => '_aioseo_ai_generated', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			'meta_value'     => 1, // phpcs:ignore HM.Performance.SlowMetaQuery.slow_query_meta_value, WordPress.DB.SlowDBQuery.slow_db_query_meta_value
			'meta_compare'   => '='
		] );

		if ( empty( $attachmentIds ) ) {
			return $images;
		}

		foreach ( $attachmentIds as $attachmentId ) {
			$images[] = $this->buildImageData( $attachmentId );
		}

		return $images;
	}

	/**
	 * Deletes the images and updates the parent image id.
	 *
	 * @since 4.8.8
	 *
	 * @param  array $ids The attachment IDs.
	 * @return array      The attachment IDs that failed to be deleted.
	 */
	public function deleteImages( $ids ) {
		$failed = [];
		foreach ( $ids as $id ) {
			if ( ! current_user_can( 'delete_post', $id ) ) {
				$failed[] = $id;
				continue;
			}

			$deleted = wp_delete_attachment( $id, true );
			if ( ! $deleted ) {
				$failed[] = $id;
				continue;
			}

			// Update all images post meta that have the parent image id set to the deleted image id.
			$attachmentIds = get_posts( [
				'post_type'      => 'attachment',
				'post_status'    => 'inherit',
				'posts_per_page' => -1,
				'fields'         => 'ids',
				'meta_key'       => '_aioseo_ai_parent', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
				'meta_value'     => $id, // phpcs:ignore HM.Performance.SlowMetaQuery.slow_query_meta_value, WordPress.DB.SlowDBQuery.slow_db_query_meta_value
				'meta_compare'   => '='
			] );

			foreach ( $attachmentIds as $attachmentId ) {
				delete_post_meta( $attachmentId, '_aioseo_ai_parent' );
			}
		}

		return $failed;
	}

	/**
	 * Generates attachment metadata (thumbnails) for AI-generated images.
	 * This is called asynchronously via Action Scheduler to avoid blocking the REST API response.
	 *
	 * @since 4.8.8
	 *
	 * @param  int    $attachmentId The attachment ID.
	 * @param  string $file         Path to the image file.
	 * @return void
	 */
	public function generateImageMetadata( $attachmentId, $file ) {
		if ( ! $attachmentId || ! $file || ! file_exists( $file ) || ! get_post( $attachmentId ) ) {
			return;
		}

		require_once ABSPATH . 'wp-admin/includes/image.php';

		$metadata = wp_generate_attachment_metadata( $attachmentId, $file );
		if ( $metadata ) {
			wp_update_attachment_metadata( $attachmentId, $metadata );
		}
	}

	/**
	 * Builds the image data for a specific attachment.
	 *
	 * @since 4.8.8
	 *
	 * @param  int   $attachmentId The attachment ID.
	 * @return array               The image data.
	 */
	private function buildImageData( $attachmentId ) {
		$aiData   = get_post_meta( $attachmentId, '_aioseo_ai_data', true );
		$aiParent = get_post_meta( $attachmentId, '_aioseo_ai_parent', true );

		$mimeType = get_post_mime_type( $attachmentId );
		$src      = wp_get_attachment_image_src( $attachmentId, 'full' );

		list( $url, $width, $height ) = $src;

		if ( ! $width || ! $height ) {
			list( $width, $height ) = [ 0, 0 ];
			$wpImageSize = wp_getimagesize( get_attached_file( $attachmentId ) );
			if ( $wpImageSize ) {
				list( $width, $height ) = $wpImageSize;
			}
		}

		return [
			'alt'           => trim( wp_strip_all_tags( get_post_meta( $attachmentId, '_wp_attachment_image_alt', true ) ) ),
			'aspectRatio'   => $aiData['aspectRatio'] ?? null,
			'format'        => $mimeType ? str_replace( 'image/', '', $mimeType ) : '',
			'height'        => $height,
			'id'            => $attachmentId,
			'parentImageId' => ! empty( $aiParent ) ? (int) $aiParent : 0,
			'prompt'        => $aiData['prompt'] ?? null,
			'quality'       => $aiData['quality'] ?? null,
			'style'         => $aiData['style'] ?? null,
			'url'           => $url,
			'width'         => $width
		];
	}
}