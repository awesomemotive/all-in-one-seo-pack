<?php
namespace AIOSEO\Plugin\Common\Social;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AIOSEO\Plugin\Common\Models;

/**
 * Handles the Open Graph and Twitter Image.
 *
 * @since 4.0.0
 */
class Image {
	/**
	 * The type of image ("facebook" or "twitter").
	 *
	 * @since 4.1.6.2
	 *
	 * @var string
	 */
	protected $type;

	/**
	 * The post object.
	 *
	 * @since 4.1.6.2
	 *
	 * @var \WP_Post
	 */
	private $post;

	/**
	 * The default thumbnail size.
	 *
	 * @since 4.0.0
	 *
	 * @var string
	 */
	protected $thumbnailSize;

	/**
	 * Whether or not to use the cached images.
	 *
	 * @since 4.1.6
	 *
	 * @var boolean
	 */
	public $useCache = true;

	/**
	 * Returns the Facebook or Twitter image.
	 *
	 * @since 4.0.0
	 *
	 * @param  string        $type        The type ("Facebook" or "Twitter").
	 * @param  string        $imageSource The image source.
	 * @param  \WP_Post|null $post        The post object.
	 * @return string|array               The image data.
	 */
	public function getImage( $type, $imageSource, $post = null ) {
		$this->type          = $type;
		$this->post          = $post;
		$this->thumbnailSize = apply_filters( 'aioseo_thumbnail_size', 'fullsize' );
		$hash                = md5( wp_json_encode( [ $type, $imageSource, $post ] ) );

		static $images = [];
		if ( isset( $images[ $hash ] ) ) {
			return $images[ $hash ];
		}

		if ( 'auto' === $imageSource && aioseo()->helpers->getPostPageBuilderName( $post->ID ) ) {
			$imageSource = 'default';
		}

		if ( is_a( $this->post, 'WP_Post' ) ) {
			switch ( $imageSource ) {
				case 'featured':
					$image = $this->getFeaturedImage();
					break;
				case 'attach':
					$image = $this->getFirstAttachedImage();
					break;
				case 'content':
					$image = $this->getFirstImageInContent();
					break;
				case 'author':
					$image = $this->getAuthorAvatar();
					break;
				case 'auto':
					$image = $this->getFirstAvailableImage();
					break;
				case 'custom':
					$image = $this->getCustomFieldImage();
					break;
				case 'custom_image':
					$metaData = aioseo()->meta->metaData->getMetaData( $post );
					if ( empty( $metaData ) ) {
						break;
					}
					$image = 'facebook' === strtolower( $this->type )
						? $metaData->og_image_custom_url
						: $metaData->twitter_image_custom_url;
					break;
				case 'default':
				default:
					$image = aioseo()->options->social->{$this->type}->general->defaultImagePosts;
			}
		}

		if ( empty( $image ) ) {
			$image = aioseo()->options->social->{$this->type}->general->defaultImagePosts;
		}

		if ( is_array( $image ) ) {
			$images[ $hash ] = $image;

			return $images[ $hash ];
		}

		$imageWithoutDimensions = aioseo()->helpers->removeImageDimensions( $image );
		$attachmentId           = aioseo()->helpers->attachmentUrlToPostId( $imageWithoutDimensions );
		$images[ $hash ]        = $attachmentId ? wp_get_attachment_image_src( $attachmentId, $this->thumbnailSize ) : $image;

		return $images[ $hash ];
	}

	/**
	 * Returns the Featured Image for the post.
	 *
	 * @since 4.0.0
	 *
	 * @return array The image data.
	 */
	private function getFeaturedImage() {
		$cachedImage = $this->getCachedImage();
		if ( $cachedImage ) {
			return $cachedImage;
		}

		$imageId = get_post_thumbnail_id( $this->post->ID );

		return $imageId ? wp_get_attachment_image_src( $imageId, $this->thumbnailSize ) : '';
	}

	/**
	 * Returns the first attached image.
	 *
	 * @since 4.0.0
	 *
	 * @return string The image data.
	 */
	private function getFirstAttachedImage() {
		$cachedImage = $this->getCachedImage();
		if ( $cachedImage ) {
			return $cachedImage;
		}

		if ( 'attachment' === get_post_type( $this->post->ID ) ) {
			return wp_get_attachment_image_src( $this->post->ID, $this->thumbnailSize );
		}

		$attachments = get_children(
			[
				'post_parent'    => $this->post->ID,
				'post_status'    => 'inherit',
				'post_type'      => 'attachment',
				'post_mime_type' => 'image',
			]
		);

		return $attachments && count( $attachments ) ? wp_get_attachment_image_src( array_values( $attachments )[0]->ID, $this->thumbnailSize ) : '';
	}

	/**
	 * Returns the first image found in the post content.
	 *
	 * @since 4.0.0
	 *
	 * @return string The image URL.
	 */
	private function getFirstImageInContent() {
		$cachedImage = $this->getCachedImage();
		if ( $cachedImage ) {
			return $cachedImage;
		}

		$postContent = aioseo()->helpers->getPostContent( $this->post );
		preg_match_all( '|<img.*?src=[\'"](.*?)[\'"].*?>|i', (string) $postContent, $matches ); // phpcs:ignore PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage

		// Ignore cover block background image - WP >= 5.7.
		if ( ! empty( $matches[0] ) && apply_filters( 'aioseo_social_image_ignore_cover_block', true, $this->post, $matches ) ) {
			foreach ( $matches[0] as $key => $match ) {
				if ( false !== stripos( $match, 'wp-block-cover__image-background' ) ) {
					unset( $matches[1][ $key ] );
				}
			}
		}

		return ! empty( $matches[1] ) ? current( $matches[1] ) : '';
	}

	/**
	 * Returns the author avatar.
	 *
	 * @since 4.0.0
	 *
	 * @return string The image URL.
	 */
	private function getAuthorAvatar() {
		$avatar = get_avatar( $this->post->post_author, 300 );
		preg_match( "/src='(.*?)'/i", (string) $avatar, $matches );

		return ! empty( $matches[1] ) ? $matches[1] : '';
	}

	/**
	 * Returns the first available image.
	 *
	 * @since 4.0.0
	 *
	 * @return string The image URL.
	 */
	private function getFirstAvailableImage() {
		// Disable the cache.
		$this->useCache = false;

		$image = $this->getCustomFieldImage();

		if ( ! $image ) {
			$image = $this->getFeaturedImage();
		}

		if ( ! $image ) {
			$image = $this->getFirstAttachedImage();
		}

		if ( ! $image ) {
			$image = $this->getFirstImageInContent();
		}

		if ( ! $image && 'twitter' === strtolower( $this->type ) ) {
			$image = aioseo()->options->social->twitter->homePage->image;
		}

		// Enable the cache.
		$this->useCache = true;

		return $image ? $image : aioseo()->options->social->facebook->homePage->image;
	}

	/**
	 * Returns the image from a custom field.
	 *
	 * @since 4.0.0
	 *
	 * @return string The image URL.
	 */
	private function getCustomFieldImage() {
		$cachedImage = $this->getCachedImage();
		if ( $cachedImage ) {
			return $cachedImage;
		}

		$prefix = 'facebook' === strtolower( $this->type ) ? 'og_' : 'twitter_';

		$aioseoPost   = Models\Post::getPost( $this->post->ID );
		$customFields = ! empty( $aioseoPost->{ $prefix . 'image_custom_fields' } )
			? $aioseoPost->{ $prefix . 'image_custom_fields' }
			: aioseo()->options->social->{$this->type}->general->customFieldImagePosts;

		if ( ! $customFields ) {
			return '';
		}

		$customFields = explode( ',', $customFields );
		foreach ( $customFields as $customField ) {
			$image = get_post_meta( $this->post->ID, $customField, true );

			if ( ! empty( $image ) ) {
				$image = is_array( $image ) ? $image[0] : $image;

				return is_numeric( $image )
					? wp_get_attachment_image_src( $image, $this->thumbnailSize )
					: $image;
			}
		}

		return '';
	}

	/**
	 * Returns the cached image if there is one.
	 *
	 * @since 4.1.6.2
	 *
	 * @param  \WP_Term     $object The object for which we need to get the cached image.
	 * @return string|array         The image URL or data.
	 */
	protected function getCachedImage( $object = null ) {
		if ( null === $object ) {
			// This isn't null if we call it from the Pro class.
			$object = $this->post;
		}

		$metaData = aioseo()->meta->metaData->getMetaData( $object );

		switch ( $this->type ) {
			case 'facebook':
				if ( ! empty( $metaData->og_image_url ) && $this->useCache ) {
					return aioseo()->meta->metaData->getCachedOgImage( $metaData );
				}
				break;
			case 'twitter':
				if ( ! empty( $metaData->twitter_image_url ) && $this->useCache ) {
					return $metaData->twitter_image_url;
				}
				break;
			default:
				break;
		}

		return '';
	}
}