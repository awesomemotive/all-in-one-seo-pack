<?php
namespace AIOSEO\Plugin\Common\ImportExport\YoastSeo;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AIOSEO\Plugin\Common\Models;

// phpcs:disable WordPress.Arrays.ArrayDeclarationSpacing.AssociativeArrayFound

/**
 * Migrates the Social Meta.
 *
 * @since 4.0.0
 */
class SocialMeta {
	/**
	 * List of options.
	 *
	 * @since 4.2.7
	 *
	 * @var array
	 */
	private $options = [];

	/**
	 * Class constructor.
	 *
	 * @since 4.0.0
	 */
	public function __construct() {
		$this->options = get_option( 'wpseo_social' );

		if ( empty( $this->options ) ) {
			return;
		}

		$this->migrateSocialUrls();
		$this->migrateFacebookSettings();
		$this->migrateTwitterSettings();
		$this->migrateFacebookAdminId();
		$this->migrateSiteName();
		$this->migrateArticleTags();
		$this->migrateAdditionalTwitterData();

		$settings = [
			'pinterestverify' => [ 'type' => 'string', 'newOption' => [ 'webmasterTools', 'pinterest' ] ]
		];

		aioseo()->importExport->yoastSeo->helpers->mapOldToNew( $settings, $this->options );
	}

	/**
	 * Migrates the Social URLs.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	private function migrateSocialUrls() {
		$settings = [
			'facebook_site' => [ 'type' => 'string', 'newOption' => [ 'social', 'profiles', 'urls', 'facebookPageUrl' ] ],
			'instagram_url' => [ 'type' => 'string', 'newOption' => [ 'social', 'profiles', 'urls', 'instagramUrl' ] ],
			'linkedin_url'  => [ 'type' => 'string', 'newOption' => [ 'social', 'profiles', 'urls', 'linkedinUrl' ] ],
			'myspace_url'   => [ 'type' => 'string', 'newOption' => [ 'social', 'profiles', 'urls', 'myspaceUrl' ] ],
			'pinterest_url' => [ 'type' => 'string', 'newOption' => [ 'social', 'profiles', 'urls', 'pinterestUrl' ] ],
			'youtube_url'   => [ 'type' => 'string', 'newOption' => [ 'social', 'profiles', 'urls', 'youtubeUrl' ] ],
			'wikipedia_url' => [ 'type' => 'string', 'newOption' => [ 'social', 'profiles', 'urls', 'wikipediaUrl' ] ],
			'wordpress_url' => [ 'type' => 'string', 'newOption' => [ 'social', 'profiles', 'urls', 'wordPressUrl' ] ],
		];

		if ( ! empty( $this->options['twitter_site'] ) ) {
			aioseo()->options->social->profiles->urls->twitterUrl =
				'https://x.com/' . aioseo()->helpers->sanitizeOption( $this->options['twitter_site'] );
		}

		aioseo()->importExport->yoastSeo->helpers->mapOldToNew( $settings, $this->options );
	}

	/**
	 * Migrates the Facebook settings.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	private function migrateFacebookSettings() {
		if ( ! empty( $this->options['og_default_image'] ) ) {
			$defaultImage = esc_url( $this->options['og_default_image'] );
			aioseo()->options->social->facebook->general->defaultImagePosts       = $defaultImage;
			aioseo()->options->social->facebook->general->defaultImageSourcePosts = 'default';

			aioseo()->options->social->twitter->general->defaultImagePosts       = $defaultImage;
			aioseo()->options->social->twitter->general->defaultImageSourcePosts = 'default';
		}

		$settings = [
			'opengraph' => [ 'type' => 'boolean', 'newOption' => [ 'social', 'facebook', 'general', 'enable' ] ],
		];

		if ( ! aioseo()->importExport->yoastSeo->searchAppearance->hasImportedHomepageSocialSettings ) {
			// These settings were moved to the Search Appearance tab of Yoast, but we'll leave this here to support older versions.
			// However, we want to make sure we import them only if the other ones aren't set.
			$settings = array_merge( $settings, [
				'og_frontpage_title' => [ 'type' => 'string', 'newOption' => [ 'social', 'facebook', 'homePage', 'title' ] ],
				'og_frontpage_desc'  => [ 'type' => 'string', 'newOption' => [ 'social', 'facebook', 'homePage', 'description' ] ],
				'og_frontpage_image' => [ 'type' => 'string', 'newOption' => [ 'social', 'facebook', 'homePage', 'image' ] ]
			] );
		}

		aioseo()->importExport->yoastSeo->helpers->mapOldToNew( $settings, $this->options, true );

		// Migrate home page object type.
		aioseo()->options->social->facebook->homePage->objectType = 'website';
		if ( 'page' === get_option( 'show_on_front' ) ) {
			$staticHomePageId = get_option( 'page_on_front' );

			// We must check if the ID exists because one might select the static homepage option but not actually set one.
			if ( ! $staticHomePageId ) {
				return;
			}

			$aioseoPost = Models\Post::getPost( (int) $staticHomePageId );
			$aioseoPost->set( [
				'og_object_type' => 'website'
			] );
			$aioseoPost->save();
		}
	}

	/**
	 * Migrates the Twitter settings.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	private function migrateTwitterSettings() {
		$settings = [
			'twitter'           => [ 'type' => 'boolean', 'newOption' => [ 'social', 'twitter', 'general', 'enable' ] ],
			'twitter_card_type' => [ 'type' => 'string', 'newOption' => [ 'social', 'twitter', 'general', 'defaultCardType' ] ],
		];

		aioseo()->importExport->yoastSeo->helpers->mapOldToNew( $settings, $this->options );
	}

	/**
	 * Migrates the Facebook admin ID.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	private function migrateFacebookAdminId() {
		if ( ! empty( $this->options['fbadminapp'] ) ) {
			aioseo()->options->social->facebook->advanced->enable = true;
			aioseo()->options->social->facebook->advanced->adminId = aioseo()->helpers->sanitizeOption( $this->options['fbadminapp'] );
		}
	}

	/**
	 * Yoast sets the og:site_name to '#site_title';
	 *
	 * @since 4.1.4
	 *
	 * @return void
	 */
	private function migrateSiteName() {
		aioseo()->options->social->facebook->general->siteName = '#site_title';
	}

	/**
	 * Yoast uses post tags by default, so we need to enable this.
	 *
	 * @since 4.1.4
	 *
	 * @return void
	 */
	private function migrateArticleTags() {
		aioseo()->options->social->facebook->advanced->enable              = true;
		aioseo()->options->social->facebook->advanced->generateArticleTags = true;
		aioseo()->options->social->facebook->advanced->usePostTagsInTags   = true;
		aioseo()->options->social->facebook->advanced->useKeywordsInTags   = false;
		aioseo()->options->social->facebook->advanced->useCategoriesInTags = false;
	}

	/**
	 * Enable additional Twitter Data.
	 *
	 * @since 4.1.4
	 *
	 * @return void
	 */
	private function migrateAdditionalTwitterData() {
		aioseo()->options->social->twitter->general->additionalData = true;
	}
}