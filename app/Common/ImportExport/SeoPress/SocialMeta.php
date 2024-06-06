<?php
namespace AIOSEO\Plugin\Common\ImportExport\SeoPress;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// phpcs:disable WordPress.Arrays.ArrayDeclarationSpacing.AssociativeArrayFound

/**
 * Migrates the Social Meta Settings.
 *
 * @since 4.1.4
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
	 * @since 4.1.4
	 */
	public function __construct() {
		$this->options = get_option( 'seopress_social_option_name' );
		if ( empty( $this->options ) ) {
			return;
		}

		$this->migrateSocialUrls();
		$this->migrateKnowledge();
		$this->migrateFacebookSettings();
		$this->migrateTwitterSettings();
	}

	/**
	 * Migrates Basic Social Profiles URLs.
	 *
	 * @since 4.1.4
	 *
	 * @return void
	 */
	private function migrateSocialUrls() {
		$settings = [
			'seopress_social_accounts_facebook'   => [ 'type' => 'string', 'newOption' => [ 'social', 'profiles', 'urls', 'facebookPageUrl' ] ],
			'seopress_social_accounts_twitter'    => [ 'type' => 'string', 'newOption' => [ 'social', 'profiles', 'urls', 'twitterUrl' ] ],
			'seopress_social_accounts_pinterest'  => [ 'type' => 'string', 'newOption' => [ 'social', 'profiles', 'urls', 'pinterestUrl' ] ],
			'seopress_social_accounts_instagram'  => [ 'type' => 'string', 'newOption' => [ 'social', 'profiles', 'urls', 'instagramUrl' ] ],
			'seopress_social_accounts_youtube'    => [ 'type' => 'string', 'newOption' => [ 'social', 'profiles', 'urls', 'youtubeUrl' ] ],
			'seopress_social_accounts_linkedin'   => [ 'type' => 'string', 'newOption' => [ 'social', 'profiles', 'urls', 'linkedinUrl' ] ],
			'seopress_social_accounts_myspace'    => [ 'type' => 'string', 'newOption' => [ 'social', 'profiles', 'urls', 'myspaceUrl' ] ],
			'seopress_social_accounts_soundcloud' => [ 'type' => 'string', 'newOption' => [ 'social', 'profiles', 'urls', 'soundCloudUrl' ] ],
			'seopress_social_accounts_tumblr'     => [ 'type' => 'string', 'newOption' => [ 'social', 'profiles', 'urls', 'tumblrUrl' ] ],
			'seopress_social_accounts_wordpress'  => [ 'type' => 'string', 'newOption' => [ 'social', 'profiles', 'urls', 'wordPressUrl' ] ],
		];

		aioseo()->importExport->seoPress->helpers->mapOldToNew( $settings, $this->options );
	}

	/**
	 * Migrates Knowledge Graph data.
	 *
	 * @since 4.1.4
	 *
	 * @return void
	 */
	private function migrateKnowledge() {
		$type = 'organization';
		if ( ! empty( $this->options['seopress_social_knowledge_type'] ) ) {
			$type = strtolower( $this->options['seopress_social_knowledge_type'] );
			if ( 'person' === $type ) {
				aioseo()->options->searchAppearance->global->schema->person = 'manual';
			}
		}

		aioseo()->options->searchAppearance->global->schema->siteRepresents = $type;

		$settings = [
			'seopress_social_knowledge_img'   => [ 'type' => 'string', 'newOption' => [ 'searchAppearance', 'global', 'schema', $type . 'Logo' ] ],
			'seopress_social_knowledge_name'  => [ 'type' => 'string', 'newOption' => [ 'searchAppearance', 'global', 'schema', $type . 'Name' ] ],
			'seopress_social_knowledge_phone' => [ 'type' => 'string', 'newOption' => [ 'searchAppearance', 'global', 'schema', 'phone' ] ],
		];

		aioseo()->importExport->seoPress->helpers->mapOldToNew( $settings, $this->options );
	}

	/**
	 * Migrates the Facebook settings.
	 *
	 * @since 4.1.4
	 *
	 * @return void
	 */
	private function migrateFacebookSettings() {
		if ( ! empty( $this->options['seopress_social_facebook_admin_id'] ) || ! empty( $this->options['seopress_social_facebook_app_id'] ) ) {
			aioseo()->options->social->facebook->advanced->enable = true;
		}

		$settings = [
			'seopress_social_facebook_og'       => [ 'type' => 'boolean', 'newOption' => [ 'social', 'facebook', 'general', 'enable' ] ],
			'seopress_social_facebook_img'      => [ 'type' => 'string', 'newOption' => [ 'social', 'facebook', 'homePage', 'image' ] ],
			'seopress_social_facebook_admin_id' => [ 'type' => 'string', 'newOption' => [ 'social', 'facebook', 'advanced', 'adminId' ] ],
			'seopress_social_facebook_app_id'   => [ 'type' => 'string', 'newOption' => [ 'social', 'facebook', 'advanced', 'appId' ] ],
		];

		aioseo()->importExport->seoPress->helpers->mapOldToNew( $settings, $this->options );
	}

	/**
	 * Migrates the Twitter settings.
	 *
	 * @since 4.1.4
	 *
	 * @return void
	 */
	private function migrateTwitterSettings() {
		if ( ! empty( $this->options['seopress_social_twitter_card_img_size'] ) ) {
			$twitterCard = ( 'large' === $this->options['seopress_social_twitter_card_img_size'] ) ? 'summary-card' : 'summary';
			aioseo()->options->social->twitter->general->defaultCardType = $twitterCard;
		}

		$settings = [
			'seopress_social_twitter_card'     => [ 'type' => 'boolean', 'newOption' => [ 'social', 'twitter', 'general', 'enable' ] ],
			'seopress_social_twitter_card_img' => [ 'type' => 'string', 'newOption' => [ 'social', 'twitter', 'homePage', 'image' ] ],
		];

		aioseo()->importExport->seoPress->helpers->mapOldToNew( $settings, $this->options );
	}
}