<?php
namespace AIOSEO\Plugin\Common\Social;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AIOSEO\Plugin\Common\Integrations\BuddyPress as BuddyPressIntegration;

/**
 * Outputs our social meta.
 *
 * @since 4.0.0
 */
class Output {

	/**
	 * Checks if the current page should have social meta.
	 *
	 * @since 4.0.0
	 *
	 * @return bool Whether or not the page should have social meta.
	 */
	public function isAllowed() {
		if ( BuddyPressIntegration::isComponentPage() ) {
			return false;
		}

		if (
			! is_front_page() &&
			! is_home() &&
			! is_singular() &&
			! is_post_type_archive() &&
			! aioseo()->helpers->isWooCommerceShopPage()
		) {
			return false;
		}

		return true;
	}

	/**
	 * Returns the Open Graph meta.
	 *
	 * @since 4.0.0
	 *
	 * @return array The Open Graph meta.
	 */
	public function getFacebookMeta() {
		if ( ! $this->isAllowed() || ! aioseo()->options->social->facebook->general->enable ) {
			return [];
		}

		$meta = [
			'og:locale'      => aioseo()->social->facebook->getLocale(),
			'og:site_name'   => aioseo()->helpers->encodeOutputHtml( aioseo()->social->facebook->getSiteName() ),
			'og:type'        => aioseo()->social->facebook->getObjectType(),
			'og:title'       => aioseo()->helpers->encodeOutputHtml( aioseo()->social->facebook->getTitle() ),
			'og:description' => aioseo()->helpers->encodeOutputHtml( aioseo()->social->facebook->getDescription() ),
			'og:url'         => esc_url( aioseo()->helpers->canonicalUrl() ),
			'fb:app_id'      => aioseo()->options->social->facebook->advanced->appId,
			'fb:admins'      => implode( ',', array_map( 'trim', explode( ',', aioseo()->options->social->facebook->advanced->adminId ) ) ),
		];

		$image = aioseo()->social->facebook->getImage();
		if ( $image ) {
			$image = is_array( $image ) ? $image[0] : $image;
			$image = aioseo()->helpers->makeUrlAbsolute( $image );
			$image = set_url_scheme( esc_url( $image ) );

			$meta += [
				'og:image'            => $image,
				'og:image:secure_url' => is_ssl() ? $image : '',
				'og:image:width'      => aioseo()->social->facebook->getImageWidth(),
				'og:image:height'     => aioseo()->social->facebook->getImageHeight(),
			];
		}

		$video = aioseo()->social->facebook->getVideo();
		if ( $video ) {
			$video = set_url_scheme( esc_url( $video ) );

			$meta += [
				'og:video'            => $video,
				'og:video:secure_url' => is_ssl() ? $video : '',
				'og:video:width'      => aioseo()->social->facebook->getVideoWidth(),
				'og:video:height'     => aioseo()->social->facebook->getVideoHeight(),
			];
		}

		if ( ! empty( $meta['og:type'] ) && 'article' === $meta['og:type'] ) {
			$meta += [
				'article:section'        => aioseo()->social->facebook->getSection(),
				'article:tag'            => aioseo()->social->facebook->getArticleTags(),
				'article:published_time' => aioseo()->social->facebook->getPublishedTime(),
				'article:modified_time'  => aioseo()->social->facebook->getModifiedTime(),
				'article:publisher'      => aioseo()->social->facebook->getPublisher(),
				'article:author'         => aioseo()->social->facebook->getAuthor()
			];
		}

		return array_filter( apply_filters( 'aioseo_facebook_tags', $meta ) );
	}

	/**
	 * Returns the Twitter meta.
	 *
	 * @since 4.0.0
	 *
	 * @return array The Twitter meta.
	 */
	public function getTwitterMeta() {
		if ( ! $this->isAllowed() || ! aioseo()->options->social->twitter->general->enable ) {
			return [];
		}

		$meta = [
			'twitter:card'        => aioseo()->social->twitter->getCardType(),
			'twitter:site'        => aioseo()->social->twitter->prepareUsername( aioseo()->social->twitter->getTwitterUrl() ),
			'twitter:title'       => aioseo()->helpers->encodeOutputHtml( aioseo()->social->twitter->getTitle() ),
			'twitter:description' => aioseo()->helpers->encodeOutputHtml( aioseo()->social->twitter->getDescription() ),
			'twitter:creator'     => aioseo()->social->twitter->getCreator()
		];

		$image = aioseo()->social->twitter->getImage();
		if ( $image ) {
			$image = is_array( $image ) ? $image[0] : $image;
			$image = aioseo()->helpers->makeUrlAbsolute( $image );

			// Set the twitter image meta.
			$meta['twitter:image'] = $image;
		}

		if ( is_singular() ) {
			$additionalData = apply_filters( 'aioseo_social_twitter_additional_data', aioseo()->social->twitter->getAdditionalData() );
			if ( $additionalData ) {
				$i = 1;
				foreach ( $additionalData as $data ) {
					$meta[ "twitter:label$i" ] = $data['label'];
					$meta[ "twitter:data$i" ]  = $data['value'];
					$i++;
				}
			}
		}

		return array_filter( apply_filters( 'aioseo_twitter_tags', $meta ) );
	}
}