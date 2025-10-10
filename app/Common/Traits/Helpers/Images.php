<?php
namespace AIOSEO\Plugin\Common\Traits\Helpers;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Contains helper methods for images.
 *
 * @since 4.8.8
 */
trait Images {
	/**
	 * Gets the base64 encoded image data from an attachment.
	 *
	 * @since 4.8.8
	 *
	 * @param  int          $attachmentId The attachment ID.
	 * @return string|false               The base64 encoded image data on success, false on failure.
	 */
	public function getBase64FromAttachment( $attachmentId ) {
		if ( ! $attachmentId ) {
			return false;
		}

		$filePath = get_attached_file( $attachmentId );
		if ( ! $filePath ) {
			return false;
		}

		$imageContent = aioseo()->core->fs->getContents( $filePath );
		if ( ! $imageContent ) {
			return false;
		}

		$extension = pathinfo( $filePath, PATHINFO_EXTENSION );

		return 'data:image/' . $extension . ';base64,' . base64_encode( $imageContent );
	}

	/**
	 * Get the allowed image extensions.
	 *
	 * @since 4.8.8
	 *
	 * @return array The allowed image extensions.
	 */
	public function getAllowedImageExtensions() {
		$extensions = [];

		foreach ( get_allowed_mime_types() as $extPattern => $mimeType ) {
			// Fast check: only process if the mime type starts with 'image/'.
			if ( strpos( $mimeType, 'image/' ) !== 0 ) {
				continue;
			}

			// Handle single or multiple extensions (e.g., 'jpg' or 'jpg|jpeg|jpe').
			if ( strpos( $extPattern, '|' ) !== false ) {
				$extensions = array_merge( $extensions, explode( '|', $extPattern ) );
			} else {
				$extensions[] = $extPattern;
			}
		}

		return array_unique( $extensions );
	}
}