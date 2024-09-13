<?php
namespace AIOSEO\Plugin\Common\ImportExport\RankMath;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// phpcs:disable WordPress.Arrays.ArrayDeclarationSpacing.AssociativeArrayFound

/**
 * Migrates the General Settings.
 *
 * @since 4.0.0
 */
class GeneralSettings {
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
		$this->options = get_option( 'rank-math-options-general' );
		if ( empty( $this->options ) ) {
			return;
		}

		$this->isTruSeoDisabled();
		$this->migrateRedirectAttachments();
		$this->migrateStripCategoryBase();
		$this->migrateRssContentSettings();

		$settings = [
			'google_verify'    => [ 'type' => 'string', 'newOption' => [ 'webmasterTools', 'google' ] ],
			'bing_verify'      => [ 'type' => 'string', 'newOption' => [ 'webmasterTools', 'bing' ] ],
			'yandex_verify'    => [ 'type' => 'string', 'newOption' => [ 'webmasterTools', 'yandex' ] ],
			'baidu_verify'     => [ 'type' => 'string', 'newOption' => [ 'webmasterTools', 'baidu' ] ],
			'pinterest_verify' => [ 'type' => 'string', 'newOption' => [ 'webmasterTools', 'pinterest' ] ],
		];

		aioseo()->importExport->rankMath->helpers->mapOldToNew( $settings, $this->options );
	}

	/**
	 * Checks whether TruSEO should be disabled.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	private function isTruSeoDisabled() {
		if ( ! empty( $this->options['frontend_seo_score'] ) ) {
			aioseo()->options->advanced->truSeo = 'on' === $this->options['frontend_seo_score'];
		}
	}

	/**
	 * Migrates the Redirect Attachments setting.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	private function migrateRedirectAttachments() {
		if ( isset( $this->options['attachment_redirect_urls'] ) ) {
			if ( 'on' === $this->options['attachment_redirect_urls'] ) {
				aioseo()->dynamicOptions->searchAppearance->postTypes->attachment->redirectAttachmentUrls = 'attachment_parent';
			} else {
				aioseo()->dynamicOptions->searchAppearance->postTypes->attachment->redirectAttachmentUrls = 'disabled';
			}
		}
	}

	/**
	 * Migrates the Strip Category Base setting.
	 *
	 * @since 4.2.0
	 *
	 * @return void
	 */
	private function migrateStripCategoryBase() {
		if ( isset( $this->options['strip_category_base'] ) ) {
			aioseo()->options->searchAppearance->advanced->removeCategoryBase = 'on' === $this->options['strip_category_base'] ? true : false;
		}
	}

	/**
	 * Migrates the RSS content settings.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	private function migrateRssContentSettings() {
		if ( isset( $this->options['rss_before_content'] ) ) {
			aioseo()->options->rssContent->before = esc_html( aioseo()->importExport->rankMath->helpers->macrosToSmartTags( $this->options['rss_before_content'] ) );
		}

		if ( isset( $this->options['rss_after_content'] ) ) {
			aioseo()->options->rssContent->after = esc_html( aioseo()->importExport->rankMath->helpers->macrosToSmartTags( $this->options['rss_after_content'] ) );
		}
	}
}