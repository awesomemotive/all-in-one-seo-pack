<?php
namespace AIOSEO\Plugin\Common\Schema\Graphs\Traits;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Trait that handles images for the graphs.
 *
 * @since 4.2.5
 */
trait Image {
	/**
	 * Builds the graph data for a given image with a given schema ID.
	 *
	 * @since 4.0.0
	 *
	 * @param int    $imageId The image ID.
	 * @param string $graphId The graph ID (optional).
	 * @return array $data    The image graph data.
	 */
	protected function image( $imageId, $graphId = '' ) {
		$attachmentId = is_string( $imageId ) && ! is_numeric( $imageId ) ? aioseo()->helpers->attachmentUrlToPostId( $imageId ) : $imageId;
		$imageUrl     = wp_get_attachment_image_url( $attachmentId, 'full' );

		$data = [
			'@type' => 'ImageObject',
			'url'   => $imageUrl ? $imageUrl : $imageId,
		];

		if ( $graphId ) {
			$baseUrl     = aioseo()->schema->context['url'] ?? aioseo()->helpers->getUrl();
			$data['@id'] = trailingslashit( $baseUrl ) . '#' . $graphId;
		}

		if ( ! $attachmentId ) {
			return $data;
		}

		$metaData = wp_get_attachment_metadata( $attachmentId );
		if ( $metaData && ! empty( $metaData['width'] ) && ! empty( $metaData['height'] ) ) {
			$data['width']  = (int) $metaData['width'];
			$data['height'] = (int) $metaData['height'];
		}

		$caption = $this->getImageCaption( $attachmentId );
		if ( ! empty( $caption ) ) {
			$data['caption'] = $caption;
		}

		return $data;
	}

	/**
	 * Get the image caption.
	 *
	 * @since 4.1.4
	 *
	 * @param  int    $attachmentId The attachment ID.
	 * @return string               The caption.
	 */
	private function getImageCaption( $attachmentId ) {
		$caption = wp_get_attachment_caption( $attachmentId );
		if ( ! empty( $caption ) ) {
			return $caption;
		}

		return get_post_meta( $attachmentId, '_wp_attachment_image_alt', true );
	}

	/**
	 * Returns the graph data for the avatar of a given user.
	 *
	 * @since 4.0.0
	 *
	 * @param  int    $userId  The user ID.
	 * @param  string $graphId The graph ID.
	 * @return array           The graph data.
	 */
	protected function avatar( $userId, $graphId ) {
		if ( ! get_option( 'show_avatars' ) ) {
			return [];
		}

		$avatar = get_avatar_data( $userId );
		if ( ! $avatar['found_avatar'] ) {
			return [];
		}

		return array_filter( [
			'@type'   => 'ImageObject',
			'@id'     => aioseo()->schema->context['url'] . "#$graphId",
			'url'     => $avatar['url'],
			'width'   => $avatar['width'],
			'height'  => $avatar['height'],
			'caption' => get_the_author_meta( 'display_name', $userId )
		] );
	}

	/**
	 * Returns the graph data for the post's featured image.
	 *
	 * @since 4.2.5
	 *
	 * @return string The featured image URL.
	 */
	protected function getFeaturedImage() {
		$post = aioseo()->helpers->getPost();

		return has_post_thumbnail( $post ) ? $this->image( get_post_thumbnail_id() ) : '';
	}
}