<?php
namespace AIOSEO\Plugin\Common\Migration;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Updates and holds the old options from V3.
 *
 * @since 4.0.0
 */
class OldOptions {
	/**
	 * The old options from V3.
	 *
	 * @since 4.0.0
	 *
	 * @var array
	 */
	public $oldOptions = [];

	/**
	 * Class constructor.
	 *
	 * @since 4.0.0
	 *
	 * @param array $oldOptions The old options. We pass it in directly via the Importer/Exporter.
	 */
	public function __construct( $oldOptions = [] ) {
		$this->oldOptions = ! empty( $oldOptions ) ? $oldOptions : get_option( 'aioseop_options' );

		if (
			! $this->oldOptions ||
			! is_array( $this->oldOptions ) ||
			! count( $this->oldOptions )
		) {
			return;
		}

		$this->runPreV4Migrations();
		$this->fixSettingValues();
	}

	/**
	 * Runs all pre-V4 migrations to update the old options to the latest state.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function runPreV4Migrations() {
		$lastActiveVersion = aioseo()->internalOptions->internal->lastActiveVersion;
		if ( version_compare( $lastActiveVersion, aioseo()->version, '<' ) ) {

			$this->doVersionUpdates( $lastActiveVersion );
			aioseo()->internalOptions->internal->lastActiveVersion = aioseo()->version;
		}
	}

	/**
	 * Runs all pre-V4 version-based migrations.
	 *
	 * @since 4.0.0
	 *
	 * @param  string $oldVersion The old version number to compare against.
	 * @return void
	 */
	protected function doVersionUpdates( $oldVersion ) {
		if ( version_compare( $oldVersion, '3.0', '<' ) ) {
			$this->sitemapExclTerms201905();
		}

		if ( version_compare( $oldVersion, '3.1', '<' ) ) {
			$this->resetFlushRewriteRules201906();
		}

		if (
			version_compare( $oldVersion, '3.2', '<' ) ||
			version_compare( $oldVersion, '3.2.6', '<' )
		) {
			$this->updateSchemaMarkup201907();
		}

		if ( version_compare( $oldVersion, '4.0.0', '<' ) ) {
			$this->updateArchiveNoIndexSettings20200413();
			$this->updateArchiveTitleFormatSettings20200413();
		}
	}

	/**
	 * Converts "excl_categories" to "excl_terms".
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	protected function sitemapExclTerms201905() {
		if (
			empty( $this->oldOptions['modules'] ) ||
			empty( $this->oldOptions['modules']['aiosp_sitemap_options'] )
		) {
			return;
		}

		$options = $this->oldOptions['modules']['aiosp_sitemap_options'];
		if ( ! empty( $options['aiosp_sitemap_excl_categories'] ) ) {
			$options['aiosp_sitemap_excl_terms']['category']['taxonomy'] = 'category';
			$options['aiosp_sitemap_excl_terms']['category']['terms']    = $options['aiosp_sitemap_excl_categories'];
			unset( $options['aiosp_sitemap_excl_categories'] );

			$this->oldOptions['modules']['aiosp_sitemap_options'] = $options;
		}
	}

	/**
	 * Flushes rewrite rules for XML Sitemap URL changes.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	protected function resetFlushRewriteRules201906() {
		add_action( 'shutdown', 'flush_rewrite_rules' );
	}

	/**
	 * Adds a number of schema markup settings.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	protected function updateSchemaMarkup201907() {
		$updateValues = [
			'aiosp_schema_markup'               => '1',
			'aiosp_schema_search_results_page'  => '1',
			'aiosp_schema_social_profile_links' => '',
			'aiosp_schema_site_represents'      => 'organization',
			'aiosp_schema_organization_name'    => '',
			'aiosp_schema_organization_logo'    => '',
			'aiosp_schema_person_user'          => '1',
			'aiosp_schema_phone_number'         => '',
			'aiosp_schema_contact_type'         => 'none',
		];

		if ( isset( $this->oldOptions['aiosp_schema_markup'] ) ) {
			if ( empty( $this->oldOptions['aiosp_schema_markup'] ) || 'off' === $this->oldOptions['aiosp_schema_markup'] ) {
				$updateValues['aiosp_schema_markup'] = '0';
			}
		}
		if ( isset( $this->oldOptions['aiosp_google_sitelinks_search'] ) ) {
			if ( empty( $this->oldOptions['aiosp_google_sitelinks_search'] ) || 'off' === $this->oldOptions['aiosp_google_sitelinks_search'] ) {
				$updateValues['aiosp_schema_search_results_page'] = '0';
			}
		}
		if ( isset( $this->oldOptions['modules']['aiosp_opengraph_options']['aiosp_opengraph_profile_links'] ) ) {
			$updateValues['aiosp_schema_social_profile_links'] = $this->oldOptions['modules']['aiosp_opengraph_options']['aiosp_opengraph_profile_links'];
		}
		if ( isset( $this->oldOptions['modules']['aiosp_opengraph_options']['aiosp_opengraph_person_or_org'] ) ) {
			if ( 'person' === $this->oldOptions['modules']['aiosp_opengraph_options']['aiosp_opengraph_person_or_org'] ) {
				$updateValues['aiosp_schema_site_represents'] = 'person';
			}
		}
		if ( isset( $this->oldOptions['modules']['aiosp_opengraph_options']['aiosp_opengraph_social_name'] ) ) {
			$updateValues['aiosp_schema_organization_name'] = $this->oldOptions['modules']['aiosp_opengraph_options']['aiosp_opengraph_social_name'];
		}

		foreach ( $updateValues as $k => $v ) {
			$this->oldOptions[ $k ] = $v;
		}
	}

	/**
	 * Migrate setting for noindex archives.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	protected function updateArchiveNoIndexSettings20200413() {
		if ( isset( $this->oldOptions['aiosp_archive_noindex'] ) ) {
			$this->oldOptions['aiosp_archive_date_noindex']   = $this->oldOptions['aiosp_archive_noindex'];
			$this->oldOptions['aiosp_archive_author_noindex'] = $this->oldOptions['aiosp_archive_noindex'];
			unset( $this->oldOptions['aiosp_archive_noindex'] );
		}
	}

	/**
	 * Migrate settings for archive title formats.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	protected function updateArchiveTitleFormatSettings20200413() {
		if (
			isset( $this->oldOptions['aiosp_archive_title_format'] ) &&
			empty( $this->oldOptions['aiosp_date_title_format'] )
		) {
			$this->oldOptions['aiosp_date_title_format'] = $this->oldOptions['aiosp_archive_title_format'];
			unset( $this->oldOptions['aiosp_archive_title_format'] );
		}

		if (
			isset( $this->oldOptions['aiosp_archive_title_format'] ) &&
			'%date% | %site_title%' === $this->oldOptions['aiosp_archive_title_format']
		) {
			unset( $this->oldOptions['aiosp_archive_title_format'] );
		}
	}

	/**
	 * Corrects the value of a number of settings in V3 that are illogical.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	protected function fixSettingValues() {
		$settingsToFix = [
			'aiosp_togglekeywords'
		];
		foreach ( $settingsToFix as $settingToFix ) {
			if ( isset( $this->oldOptions[ $settingToFix ] ) ) {
				if ( '1' === (string) $this->oldOptions[ $settingToFix ] ) {
					$this->oldOptions[ $settingToFix ] = '';
					continue;
				}
				$this->oldOptions[ $settingToFix ] = 'on';
			}
		}
	}
}