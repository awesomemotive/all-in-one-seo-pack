<?php
namespace AIOSEO\Plugin\Common\Social;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AIOSEO\Plugin\Common\Traits;

/**
 * Handles the Twitter meta.
 *
 * @since 4.0.0
 */
class Twitter {
	use Traits\SocialProfiles;

	/**
	 * Returns the Twitter URL for the site.
	 *
	 * @since 4.0.0
	 *
	 * @return string The Twitter URL.
	 */
	public function getTwitterUrl() {
		if ( ! aioseo()->options->social->profiles->sameUsername->enable ) {
			return aioseo()->options->social->profiles->urls->twitterUrl;
		}

		$userName = aioseo()->options->social->profiles->sameUsername->username;

		return ( $userName && in_array( 'twitterUrl', aioseo()->options->social->profiles->sameUsername->included, true ) )
			? 'https://x.com/' . $userName
			: '';
	}

	/**
	 * Returns the Twitter card type.
	 *
	 * @since 4.0.0
	 *
	 * @return string $card The card type.
	 */
	public function getCardType() {
		if ( is_home() && 'posts' === get_option( 'show_on_front' ) ) {
			return aioseo()->options->social->twitter->homePage->cardType;
		}

		$metaData = aioseo()->meta->metaData->getMetaData();

		return ! empty( $metaData->twitter_card ) && 'default' !== $metaData->twitter_card ? $metaData->twitter_card : aioseo()->options->social->twitter->general->defaultCardType;
	}

	/**
	 * Returns the Twitter creator.
	 *
	 * @since 4.0.0
	 *
	 * @return string The creator.
	 */
	public function getCreator() {
		$post = aioseo()->helpers->getPost();
		if (
			! is_a( $post, 'WP_Post' ) ||
			! post_type_supports( $post->post_type, 'author' ) ||
			! aioseo()->options->social->twitter->general->showAuthor
		) {
			return '';
		}

		$author       = '';
		$userProfiles = $this->getUserProfiles( $post->post_author );
		if ( ! empty( $userProfiles['twitterUrl'] ) ) {
			$author = $userProfiles['twitterUrl'];
		}

		if ( empty( $author ) ) {
			$author = aioseo()->social->twitter->getTwitterUrl();
		}

		$author = aioseo()->social->twitter->prepareUsername( $author );

		return $author;
	}

	/**
	 * Returns the Twitter image URL.
	 *
	 * @since 4.0.0
	 *
	 * @param  int    $postId The post ID (optional).
	 * @return string         The image URL.
	 */
	public function getImage( $postId = null ) {
		$post = aioseo()->helpers->getPost( $postId );
		if ( is_home() && 'posts' === get_option( 'show_on_front' ) ) {
			$image = aioseo()->options->social->twitter->homePage->image;
			if ( empty( $image ) ) {
				$image = aioseo()->options->social->facebook->homePage->image;
			}
			if ( empty( $image ) ) {
				$image = aioseo()->social->image->getImage( 'twitter', aioseo()->options->social->twitter->general->defaultImageSourcePosts, $post );
			}

			return $image ? $image : aioseo()->social->facebook->getImage();
		}

		$metaData = aioseo()->meta->metaData->getMetaData( $post );

		if ( ! empty( $metaData->twitter_use_og ) ) {
			return aioseo()->social->facebook->getImage();
		}

		$image = '';
		if ( ! empty( $metaData ) ) {
			$imageSource = ! empty( $metaData->twitter_image_type ) && 'default' !== $metaData->twitter_image_type
				? $metaData->twitter_image_type
				: aioseo()->options->social->twitter->general->defaultImageSourcePosts;

			$image = aioseo()->social->image->getImage( 'twitter', $imageSource, $post );
		}

		return $image ? $image : aioseo()->social->facebook->getImage();
	}

	/**
	 * Returns the Twitter title for the current page.
	 *
	 * @since 4.0.0
	 *
	 * @param  \WP_Post|integer $post The post object or ID (optional).
	 * @return string                 The Twitter title.
	 */
	public function getTitle( $post = null ) {
		if ( is_home() && 'posts' === get_option( 'show_on_front' ) ) {
			$title = aioseo()->meta->title->helpers->prepare( aioseo()->options->social->twitter->homePage->title );

			return $title ? $title : aioseo()->social->facebook->getTitle( $post );
		}

		$post     = aioseo()->helpers->getPost( $post );
		$metaData = aioseo()->meta->metaData->getMetaData( $post );

		if ( ! empty( $metaData->twitter_use_og ) ) {
			return aioseo()->social->facebook->getTitle( $post );
		}

		$title = '';
		if ( ! empty( $metaData->twitter_title ) ) {
			$title = aioseo()->meta->title->helpers->prepare( $metaData->twitter_title );
		}

		return $title ? $title : aioseo()->social->facebook->getTitle( $post );
	}

	/**
	 * Returns the Twitter description for the current page.
	 *
	 * @since 4.0.0
	 *
	 * @param  \WP_Post|integer $post The post object or ID (optional).
	 * @return string                 The Twitter description.
	 */
	public function getDescription( $post = null ) {
		if ( is_home() && 'posts' === get_option( 'show_on_front' ) ) {
			$description = aioseo()->meta->description->helpers->prepare( aioseo()->options->social->twitter->homePage->description );

			return $description ? $description : aioseo()->social->facebook->getDescription( $post );
		}

		$post     = aioseo()->helpers->getPost( $post );
		$metaData = aioseo()->meta->metaData->getMetaData( $post );

		if ( ! empty( $metaData->twitter_use_og ) ) {
			return aioseo()->social->facebook->getDescription( $post );
		}

		$description = '';
		if ( ! empty( $metaData->twitter_description ) ) {
			$description = aioseo()->meta->description->helpers->prepare( $metaData->twitter_description );
		}

		return $description ? $description : aioseo()->social->facebook->getDescription( $post );
	}

	/**
	 * Prepare twitter username for public display.
	 *
	 * We do things like strip out the URL, etc and return just (at)username.
	 * At the moment, we'll check for 1 of 3 things... (at)username, username, and https://x.com/username.
	 *
	 * @since 4.0.0
	 *
	 * @param  string  $profile   Twitter username.
	 * @param  boolean $includeAt Whether or not ot include the @ sign.
	 * @return string             Full Twitter username.
	 */
	public function prepareUsername( $profile, $includeAt = true ) {
		if ( ! $profile ) {
			return $profile;
		}

		$profile = (string) $profile;
		if ( preg_match( '/^(\@)?[A-Za-z0-9_]+$/', (string) $profile ) ) {
			if ( '@' !== $profile[0] && $includeAt ) {
				$profile = '@' . $profile;
			} elseif ( '@' === $profile[0] && ! $includeAt ) {
				$profile = ltrim( $profile, '@' );
			}
		}

		if ( strpos( $profile, 'twitter.com' ) || strpos( $profile, 'x.com' ) ) {
			$profile = esc_url( $profile );

			// Extract the twitter username from the URL.
			$parsedTwitterProfile = wp_parse_url( $profile );

			$path      = $parsedTwitterProfile['path'];
			$pathParts = explode( '/', $path );
			$profile   = $pathParts[1];

			if ( $profile ) {
				if ( '@' !== $profile[0] && $includeAt ) {
					$profile = '@' . $profile;
				}

				if ( '@' === $profile[0] && ! $includeAt ) {
					$profile = ltrim( $profile, '@' );
				}
			}
		}

		return $profile;
	}

	/**
	 * Get additional twitter data.
	 *
	 * @since 4.0.0
	 *
	 * @return array An array of additional twitter data.
	 */
	public function getAdditionalData() {
		if ( ! aioseo()->options->social->twitter->general->additionalData ) {
			return [];
		}

		$data = [];
		$post = aioseo()->helpers->getPost();
		if ( ! is_a( $post, 'WP_Post' ) ) {
			return $data;
		}

		if ( $post->post_author && post_type_supports( $post->post_type, 'author' ) ) {
			$data[] = [
				'label' => __( 'Written by', 'all-in-one-seo-pack' ),
				'value' => get_the_author_meta( 'display_name', $post->post_author )
			];
		}

		if ( ! empty( $post->post_content ) ) {
			$minutes = $this->getReadingTime( $post->post_content );
			if ( ! empty( $minutes ) ) {
				$data[] = [
					'label' => __( 'Est. reading time', 'all-in-one-seo-pack' ),
					// Translators: 1 - The estimated reading time.
					'value' => sprintf( _n( '%1$s minute', '%1$s minutes', $minutes, 'all-in-one-seo-pack' ), $minutes )
				];
			}
		}

		return $data;
	}

	/**
	 * Returns the estimated reading time for a string.
	 *
	 * @since 4.0.0
	 *
	 * @param  string  $string The string to count.
	 * @return integer         The estimated reading time as an integer.
	 */
	private function getReadingTime( $string ) {
		$wpm  = 200;
		$word = str_word_count( wp_strip_all_tags( $string ) );

		return round( $word / $wpm );
	}
}