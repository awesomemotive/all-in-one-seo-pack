<?php
namespace AIOSEO\Plugin\Common\Utils;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Vue Settings for the user.
 *
 * @since 4.0.0
 */
class VueSettings {
	/**
	 * The name to lookup the settings with.
	 *
	 * @since 4.0.0
	 *
	 * @var string
	 */
	private $settingsName = '';

	/**
	 * The settings array.
	 *
	 * @since 4.0.0
	 *
	 * @var array
	 */
	private $settings = [];

	/**
	 * All the default settings.
	 *
	 * @since 4.0.0
	 *
	 * @var array
	 */
	private $defaults = [
		'showUpgradeBar'  => true,
		'showSetupWizard' => true,
		'toggledCards'    => [
			'dashboardOverview'            => true,
			'dashboardSeoSetup'            => true,
			'dashboardSeoSiteScore'        => true,
			'dashboardNotifications'       => true,
			'dashboardSupport'             => true,
			'license'                      => true,
			'webmasterTools'               => true,
			'enableBreadcrumbs'            => true,
			'breadcrumbSettings'           => true,
			'breadcrumbTemplates'          => true,
			'advanced'                     => true,
			'accessControl'                => true,
			'rssContent'                   => true,
			'generalSitemap'               => true,
			'generalSitemapSettings'       => true,
			'imageSitemap'                 => true,
			'videoSitemap'                 => true,
			'newsSitemap'                  => true,
			'rssSitemap'                   => true,
			'rssSitemapSettings'           => true,
			'rssAdditionalPages'           => true,
			'rssAdvancedSettings'          => true,
			'additionalPages'              => true,
			'advancedSettings'             => true,
			'videoSitemapSettings'         => true,
			'videoAdditionalPages'         => true,
			'videoAdvancedSettings'        => true,
			'videoEmbedSettings'           => true,
			'newsSitemapSettings'          => true,
			'newsAdditionalPages'          => true,
			'newsAdvancedSettings'         => true,
			'newsEmbedSettings'            => true,
			'socialProfiles'               => true,
			'facebook'                     => true,
			'facebookHomePageSettings'     => true,
			'facebookAdvancedSettings'     => true,
			'twitter'                      => true,
			'twitterHomePageSettings'      => true,
			'pinterest'                    => true,
			'searchTitleSeparator'         => true,
			'searchHomePage'               => true,
			'searchSchema'                 => true,
			'searchMediaAttachments'       => true,
			'searchAdvanced'               => true,
			'searchAdvancedCrawlCleanup'   => true,
			'authorArchives'               => true,
			'dateArchives'                 => true,
			'searchArchives'               => true,
			'imageSeo'                     => true,
			'completeSeoChecklist'         => true,
			'localBusinessInfo'            => true,
			'localBusinessOpeningHours'    => true,
			'locationsSettings'            => true,
			'advancedLocationsSettings'    => true,
			'localBusinessMapsApiKey'      => true,
			'localBusinessMapsSettings'    => true,
			'robotsEditor'                 => true,
			'badBotBlocker'                => true,
			'databaseTools'                => true,
			'htaccessEditor'               => true,
			'databaseToolsLogs'            => true,
			'systemStatusInfo'             => true,
			'addNewRedirection'            => true,
			'redirectSettings'             => true,
			'debug'                        => true,
			'fullSiteRedirectsRelocate'    => true,
			'fullSiteRedirectsAliases'     => true,
			'fullSiteRedirectsCanonical'   => true,
			'fullSiteRedirectsHttpHeaders' => true,
			'htmlSitemap'                  => true,
			'htmlSitemapSettings'          => true,
			'htmlSitemapAdvancedSettings'  => true,
			'linkAssistantSettings'        => true,
			'domainActivations'            => true,
			'404Settings'                  => true
		],
		'toggledRadio'    => [
			'breadcrumbsShowMoreSeparators' => false,
			'searchShowMoreSeparators'      => false,
			'overviewPostType'              => 'post',
		],
		'dismissedAlerts' => [
			'searchStatisticsContentRankings' => false
		],
		'internalTabs'    => [
			'authorArchives'    => 'title-description',
			'dateArchives'      => 'title-description',
			'searchArchives'    => 'title-description',
			'seoAuditChecklist' => 'all-items'
		],
		'tablePagination' => [
			'networkDomains'                     => 20,
			'redirects'                          => 20,
			'redirectLogs'                       => 20,
			'redirect404Logs'                    => 20,
			'sitemapAdditionalPages'             => 20,
			'linkAssistantLinksReport'           => 20,
			'linkAssistantPostsReport'           => 20,
			'linkAssistantDomainsReport'         => 20,
			'searchStatisticsSeoStatistics'      => 20,
			'searchStatisticsKeywordRankings'    => 20,
			'searchStatisticsContentRankings'    => 20,
			'searchStatisticsPostDetailKeywords' => 20
		]
	];

	/**
	 * The Construct method.
	 *
	 * @since 4.0.0
	 *
	 * @param string $settings An array of settings.
	 */
	public function __construct( $settings = '_aioseo_settings' ) {
		$this->addDynamicDefaults();

		$this->settingsName = $settings;
		$this->settings     = get_user_meta( get_current_user_id(), $settings, true )
			? array_replace_recursive( $this->defaults, get_user_meta( get_current_user_id(), $settings, true ) )
			: $this->defaults;
	}

	/**
	 * Adds some defaults that are dynamically generated.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	private function addDynamicDefaults() {
		$postTypes = aioseo()->helpers->getPublicPostTypes( false, false, true );
		foreach ( $postTypes as $postType ) {
			$this->defaults['toggledCards'][ $postType['name'] . 'SA' ] = true;
			$this->defaults['internalTabs'][ $postType['name'] . 'SA' ] = 'title-description';
		}

		$taxonomies = aioseo()->helpers->getPublicTaxonomies( false, true );
		foreach ( $taxonomies as $taxonomy ) {
			$this->defaults['toggledCards'][ $taxonomy['name'] . 'SA' ] = true;
			$this->defaults['internalTabs'][ $taxonomy['name'] . 'SA' ] = 'title-description';
		}

		$postTypes = aioseo()->helpers->getPublicPostTypes( false, true, true );
		foreach ( $postTypes as $postType ) {
			$this->defaults['toggledCards'][ $postType['name'] . 'ArchiveArchives' ] = true;
			$this->defaults['internalTabs'][ $postType['name'] . 'ArchiveArchives' ] = 'title-description';
		}
	}

	/**
	 * Retrieves all settings.
	 *
	 * @since 4.0.0
	 *
	 * @return array An array of settings.
	 */
	public function all() {
		return array_replace_recursive( $this->defaults, $this->settings );
	}

	/**
	 * Retrieve a setting or null if missing.
	 *
	 * @since 4.0.0
	 *
	 * @param  string $name      The name of the property that is missing on the class.
	 * @param  array  $arguments The arguments passed into the method.
	 * @return mixed             The value from the settings or default/null.
	 */
	public function __call( $name, $arguments = [] ) {
		$value = isset( $this->settings[ $name ] ) ? $this->settings[ $name ] : ( ! empty( $arguments[0] ) ? $arguments[0] : $this->getDefault( $name ) );

		return $value;
	}

	/**
	 * Retrieve a setting or null if missing.
	 *
	 * @since 4.0.0
	 *
	 * @param  string $name The name of the property that is missing on the class.
	 * @return mixed        The value from the settings or default/null.
	 */
	public function __get( $name ) {
		$value = isset( $this->settings[ $name ] ) ? $this->settings[ $name ] : $this->getDefault( $name );

		return $value;
	}

	/**
	 * Sets the settings value and saves to the database.
	 *
	 * @since 4.0.0
	 *
	 * @param  string $name  The name of the settings.
	 * @param  mixed  $value The value to set.
	 * @return void
	 */
	public function __set( $name, $value ) {
		$this->settings[ $name ] = $value;

		$this->update();
	}

	/**
	 * Checks if an settings is set or returns null if not.
	 *
	 * @since 4.0.0
	 *
	 * @param  string $name The name of the settings.
	 * @return mixed        True or null.
	 */
	public function __isset( $name ) {
		return isset( $this->settings[ $name ] ) ? false === empty( $this->settings[ $name ] ) : null;
	}

	/**
	 * Unsets the settings value and saves to the database.
	 *
	 * @since 4.0.0
	 *
	 * @param  string $name  The name of the settings.
	 * @return void
	 */
	public function __unset( $name ) {
		if ( ! isset( $this->settings[ $name ] ) ) {
			return;
		}

		unset( $this->settings[ $name ] );

		$this->update();
	}

	/**
	 * Gets the default value for a setting.
	 *
	 * @since 4.0.0
	 *
	 * @param  string $name The settings name.
	 * @return mixed        The default value.
	 */
	public function getDefault( $name ) {
		return isset( $this->defaults[ $name ] ) ? $this->defaults[ $name ] : null;
	}

	/**
	 * Updates the settings in the database.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function update() {
		update_user_meta( get_current_user_id(), $this->settingsName, $this->settings );
	}
}