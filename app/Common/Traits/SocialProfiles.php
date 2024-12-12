<?php
namespace AIOSEO\Plugin\Common\Traits;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Trait that handles the social profiles.
 *
 * @since 4.2.2
 */
trait SocialProfiles {
	/**
	 * List of base URLs.
	 *
	 * @since 4.2.2
	 *
	 * @var array
	 */
	private $baseUrls = [
		'facebookPageUrl' => 'https://facebook.com/',
		'twitterUrl'      => 'https://x.com/',
		'instagramUrl'    => 'https://instagram.com/',
		'tiktokUrl'       => 'https://tiktok.com/@',
		'pinterestUrl'    => 'https://pinterest.com/',
		'youtubeUrl'      => 'https://youtube.com/',
		'linkedinUrl'     => 'https://linkedin.com/in/',
		'tumblrUrl'       => 'https://tumblr.com/',
		'yelpPageUrl'     => 'https://yelp.com/biz/',
		'soundCloudUrl'   => 'https://soundcloud.com/',
		'wikipediaUrl'    => 'https://en.wikipedia.org/wiki/',
		'myspaceUrl'      => 'https://myspace.com/',
		'wordPressUrl'    => 'https://profiles.wordpress.org/',
	];

	/**
	 * Returns the profiles of the organization, set under Social Networks.
	 *
	 * @since 4.2.2
	 *
	 * @return array List of social profiles.
	 */
	protected function getOrganizationProfiles() {
		$socialProfiles = [
			'facebookPageUrl' => aioseo()->options->social->profiles->urls->facebookPageUrl,
			'twitterUrl'      => aioseo()->options->social->profiles->urls->twitterUrl,
			'instagramUrl'    => aioseo()->options->social->profiles->urls->instagramUrl,
			'tiktokUrl'       => aioseo()->options->social->profiles->urls->tiktokUrl,
			'pinterestUrl'    => aioseo()->options->social->profiles->urls->pinterestUrl,
			'youtubeUrl'      => aioseo()->options->social->profiles->urls->youtubeUrl,
			'linkedinUrl'     => aioseo()->options->social->profiles->urls->linkedinUrl,
			'tumblrUrl'       => aioseo()->options->social->profiles->urls->tumblrUrl,
			'yelpPageUrl'     => aioseo()->options->social->profiles->urls->yelpPageUrl,
			'soundCloudUrl'   => aioseo()->options->social->profiles->urls->soundCloudUrl,
			'wikipediaUrl'    => aioseo()->options->social->profiles->urls->wikipediaUrl,
			'myspaceUrl'      => aioseo()->options->social->profiles->urls->myspaceUrl,
			'wordPressUrl'    => aioseo()->options->social->profiles->urls->wordPressUrl,
		];

		if ( aioseo()->options->social->profiles->sameUsername->enable ) {
			$username          = aioseo()->options->social->profiles->sameUsername->username;
			$includedPlatforms = aioseo()->options->social->profiles->sameUsername->included;

			foreach ( $this->baseUrls as $platformKey => $baseUrl ) {
				if ( ! in_array( $platformKey, $includedPlatforms, true ) ) {
					continue;
				}

				$socialProfiles[ $platformKey ] = $baseUrl . $username;
			}
		}

		if ( aioseo()->options->social->profiles->additionalUrls ) {
			$additionalUrls = preg_split( '/\n|\r|\r\n/', (string) aioseo()->options->social->profiles->additionalUrls );
			$socialProfiles = array_merge( $socialProfiles, $additionalUrls );
		}

		if ( ! aioseo()->options->social->facebook->general->showAuthor ) {
			unset( $socialProfiles['facebookPageUrl'] );
		}

		if ( ! aioseo()->options->social->twitter->general->showAuthor ) {
			unset( $socialProfiles['twitterUrl'] );
		}

		return array_filter( $socialProfiles );
	}

	/**
	 * Returns the profiles of the given user, set under the User Profile.
	 *
	 * @since 4.2.2
	 *
	 * @param  int   $userId The user ID.
	 * @return array         List of social profiles.
	 */
	protected function getUserProfiles( $userId ) {
		$socialProfiles = $this->baseUrls;
		foreach ( $socialProfiles as $platformKey => $v ) {
			$metaName                       = 'aioseo_' . aioseo()->helpers->toSnakeCase( $platformKey );
			$socialProfiles[ $platformKey ] = get_user_meta( $userId, $metaName, true );
		}

		$sameUsernameData = get_user_meta( $userId, 'aioseo_profiles_same_username', true );
		if ( is_array( $sameUsernameData ) && (bool) $sameUsernameData['enable'] ) {
			foreach ( $this->baseUrls as $platform => $baseUrl ) {
				if ( ! in_array( $platform, $sameUsernameData['included'], true ) ) {
					continue;
				}

				$socialProfiles[ $platform ] = $baseUrl . $sameUsernameData['username'];
			}
		}

		$additionalUrls = get_user_meta( $userId, 'aioseo_profiles_additional_urls', true );
		if ( $additionalUrls ) {
			$additionalUrls = preg_split( '/\n|\r|\r\n/', (string) $additionalUrls );
			foreach ( $additionalUrls as $additionalUrl ) {
				// We need to set a random key because otherwise we'll override the ones from the organization.
				$socialProfiles[ uniqid() ] = $additionalUrl;
			}
		}

		if ( ! aioseo()->options->social->facebook->general->showAuthor ) {
			unset( $socialProfiles['facebookPageUrl'] );
		}

		if ( ! aioseo()->options->social->twitter->general->showAuthor ) {
			unset( $socialProfiles['twitterUrl'] );
		}

		return array_filter( $socialProfiles );
	}
}