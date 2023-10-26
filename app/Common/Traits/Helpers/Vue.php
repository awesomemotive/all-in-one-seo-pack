<?php
namespace AIOSEO\Plugin\Common\Traits\Helpers;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AIOSEO\Plugin\Common\Integrations\WpCode as WpCodeIntegration;
use AIOSEO\Plugin\Common\Models;
use AIOSEO\Plugin\Common\Tools;

/**
 * Contains all Vue related helper methods.
 *
 * @since 4.1.4
 */
trait Vue {
	/**
	 * Keeps the data for Vue.
	 *
	 * @since 4.4.9
	 *
	 * @var array
	 */
	private static $data = [];

	/**
	 * Optional arguments for setting the data.
	 *
	 * @since 4.4.9
	 *
	 * @var array
	 */
	private $args = [];

	/**
	 * Returns the data for Vue.
	 *
	 * @since   4.0.0
	 * @version 4.4.9
	 *
	 * @param  string $page         The current page.
	 * @param  int    $staticPostId Data for a specific post.
	 * @param  string $integration  Data for integration (builder).
	 * @return array                The data.
	 */
	public function getVueData( $page = null, $staticPostId = null, $integration = null ) {
		$this->args = compact( 'page', 'staticPostId', 'integration' );
		$hash       = md5( implode( '', array_map( 'strval', $this->args ) ) );
		if ( isset( self::$data[ $hash ] ) ) {
			return self::$data[ $hash ];
		}

		$this->setInitialData();
		$this->setMultisiteData();
		$this->setPostData();
		$this->setDashboardData();
		$this->setSearchStatisticsData();
		$this->setSitemapsData();
		$this->setSetupWizardData();
		$this->setSearchAppearanceData();
		$this->setSocialNetworksData();
		$this->setSeoRevisionsData();
		$this->setToolsOrSettingsData();
		$this->setDiviData();

		self::$data[ $hash ] = self::$data;

		return self::$data[ $hash ];
	}

	/**
	 * Set Vue initial data.
	 *
	 * @since 4.4.9
	 *
	 * @return void
	 */
	private function setInitialData() {
		$screen           = aioseo()->helpers->getCurrentScreen();
		$isStaticHomePage = 'page' === get_option( 'show_on_front' );
		$staticHomePage   = intval( get_option( 'page_on_front' ) );

		self::$data = [
			'page'              => $this->args['page'],
			'screen'            => [
				'base'        => isset( $screen->base ) ? $screen->base : '',
				'postType'    => isset( $screen->post_type ) ? $screen->post_type : '',
				'blockEditor' => isset( $screen->is_block_editor ) ? $screen->is_block_editor : false,
				'new'         => isset( $screen->action ) && 'add' === $screen->action
			],
			'internalOptions'   => aioseo()->internalOptions->all(),
			'options'           => aioseo()->options->all(),
			'dynamicOptions'    => aioseo()->dynamicOptions->all(),
			'deprecatedOptions' => aioseo()->internalOptions->getAllDeprecatedOptions( true ),
			'settings'          => aioseo()->settings->all(),
			'tags'              => aioseo()->tags->all( true ),
			'nonce'             => wp_create_nonce( 'wp_rest' ),
			'urls'              => [
				'domain'            => $this->getSiteDomain(),
				'mainSiteUrl'       => $this->getSiteUrl(),
				'siteLogo'          => aioseo()->helpers->getSiteLogoUrl(),
				'home'              => home_url(),
				'restUrl'           => aioseo()->helpers->getRestUrl(),
				'editScreen'        => admin_url( 'edit.php' ),
				'publicPath'        => aioseo()->core->assets->normalizeAssetsHost( plugin_dir_url( AIOSEO_FILE ) ),
				'assetsPath'        => aioseo()->core->assets->getAssetsPath(),
				'generalSitemapUrl' => aioseo()->sitemap->helpers->getUrl( 'general' ),
				'rssSitemapUrl'     => aioseo()->sitemap->helpers->getUrl( 'rss' ),
				'robotsTxtUrl'      => $this->getSiteUrl() . '/robots.txt',
				'blockedBotsLogUrl' => wp_upload_dir()['baseurl'] . '/aioseo/logs/aioseo-bad-bot-blocker.log',
				'upgradeUrl'        => apply_filters( 'aioseo_upgrade_link', AIOSEO_MARKETING_URL ),
				'staticHomePage'    => 'page' === get_option( 'show_on_front' ) ? get_edit_post_link( get_option( 'page_on_front' ), 'url' ) : null,
				'feeds'             => [
					'rdf'            => get_bloginfo( 'rdf_url' ),
					'rss'            => get_bloginfo( 'rss_url' ),
					'atom'           => get_bloginfo( 'atom_url' ),
					'global'         => get_bloginfo( 'rss2_url' ),
					'globalComments' => get_bloginfo( 'comments_rss2_url' ),
					'staticBlogPage' => $this->getBlogPageId() ? trailingslashit( get_permalink( $this->getBlogPageId() ) ) . 'feed' : ''
				],
				'connect'           => add_query_arg( [
					'siteurl'  => site_url(),
					'homeurl'  => home_url(),
					'redirect' => rawurldecode( base64_encode( admin_url( 'index.php?page=aioseo-connect' ) ) )
				], defined( 'AIOSEO_CONNECT_URL' ) ? AIOSEO_CONNECT_URL : 'https://connect.aioseo.com' ),
				'aio'               => [
					'about'            => is_network_admin() ? network_admin_url( 'admin.php?page=aioseo-about' ) : admin_url( 'admin.php?page=aioseo-about' ),
					'dashboard'        => admin_url( 'admin.php?page=aioseo' ),
					'featureManager'   => admin_url( 'admin.php?page=aioseo-feature-manager' ),
					'linkAssistant'    => admin_url( 'admin.php?page=aioseo-link-assistant' ),
					'localSeo'         => admin_url( 'admin.php?page=aioseo-local-seo' ),
					'monsterinsights'  => admin_url( 'admin.php?page=aioseo-monsterinsights' ),
					'redirects'        => admin_url( 'admin.php?page=aioseo-redirects' ),
					'searchAppearance' => admin_url( 'admin.php?page=aioseo-search-appearance' ),
					'searchStatistics' => admin_url( 'admin.php?page=aioseo-search-statistics' ),
					'seoAnalysis'      => admin_url( 'admin.php?page=aioseo-seo-analysis' ),
					'settings'         => admin_url( 'admin.php?page=aioseo-settings' ),
					'sitemaps'         => admin_url( 'admin.php?page=aioseo-sitemaps' ),
					'socialNetworks'   => admin_url( 'admin.php?page=aioseo-social-networks' ),
					'tools'            => admin_url( 'admin.php?page=aioseo-tools' ),
					'wizard'           => admin_url( 'index.php?page=aioseo-setup-wizard' ),
					'networkSettings'  => is_network_admin() ? network_admin_url( 'admin.php?page=aioseo-settings' ) : '',
					'seoRevisions'     => admin_url( 'admin.php?page=aioseo-seo-revisions' ),
				],
				'admin'             => [
					'widgets'          => admin_url( 'widgets.php' ),
					'optionsReading'   => admin_url( 'options-reading.php' ),
					'scheduledActions' => admin_url( '/tools.php?page=action-scheduler&status=pending&s=aioseo' ),
					'generalSettings'  => admin_url( 'options-general.php' )
				],
				'truSeoWorker'      => aioseo()->core->assets->jsUrl( 'src/app/tru-seo/analyzer/main.js' )
			],
			'backups'           => [],
			'importers'         => [],
			'data'              => [
				'server'              => [
					'apache' => null,
					'nginx'  => null
				],
				'robots'              => [
					'defaultRules'      => [],
					'hasPhysicalRobots' => null,
					'rewriteExists'     => null,
					'sitemapUrls'       => []
				],
				'logSizes'            => [
					'badBotBlockerLog' => null
				],
				'status'              => [],
				'htaccess'            => '',
				'isMultisite'         => is_multisite(),
				'isNetworkAdmin'      => is_network_admin(),
				'currentBlogId'       => get_current_blog_id(),
				'mainSite'            => is_main_site(),
				'subdomain'           => $this->isSubdomain(),
				'isWooCommerceActive' => $this->isWooCommerceActive(),
				'isBBPressActive'     => class_exists( 'bbPress' ),
				'staticHomePage'      => $isStaticHomePage ? $staticHomePage : false,
				'staticBlogPage'      => $this->getBlogPageId(),
				'staticBlogPageTitle' => get_the_title( $this->getBlogPageId() ),
				'isDev'               => $this->isDev(),
				'isSsl'               => is_ssl(),
				'hasUrlTrailingSlash' => '/' === user_trailingslashit( '' ),
				'permalinkStructure'  => get_option( 'permalink_structure' ),
				'dateFormat'          => get_option( 'date_format' ),
				'timeFormat'          => get_option( 'time_format' )
			],
			'user'              => [
				'canManage'      => aioseo()->access->canManage(),
				'capabilities'   => aioseo()->access->getAllCapabilities(),
				'customRoles'    => $this->getCustomRoles(),
				'data'           => wp_get_current_user(),
				'locale'         => function_exists( 'get_user_locale' ) ? get_user_locale() : get_locale(),
				'roles'          => $this->getUserRoles(),
				'unfilteredHtml' => current_user_can( 'unfiltered_html' )
			],
			'plugins'           => $this->getPluginData(),
			'postData'          => [
				'postTypes'    => $this->getPublicPostTypes( false, false, true ),
				'taxonomies'   => $this->getPublicTaxonomies( false, true ),
				'archives'     => $this->getPublicPostTypes( false, true, true ),
				'postStatuses' => $this->getPublicPostStatuses()
			],
			'notifications'     => array_merge( Models\Notification::getNotifications( false ), [
				'force' => $this->showNotificationsDrawer()
			] ),
			'addons'            => aioseo()->addons->getAddons(),
			'features'          => aioseo()->features->getFeatures(),
			'version'           => AIOSEO_VERSION,
			'wpVersion'         => get_bloginfo( 'version' ),
			'helpPanel'         => aioseo()->help->getDocs(),
			'scheduledActions'  => [
				'sitemaps' => []
			],
			'integration'       => $this->args['integration'],
			'theme'             => [
				'features' => aioseo()->helpers->getThemeFeatures()
			]
		];
	}

	/**
	 * Set Vue multisite data.
	 *
	 * @since 4.4.9
	 *
	 * @return void
	 */
	private function setMultisiteData() {
		if ( ! is_multisite() ) {
			return;
		}

		self::$data['internalNetworkOptions'] = aioseo()->internalNetworkOptions->all();
		self::$data['networkOptions']         = aioseo()->networkOptions->all();
	}

	/**
	 * Set Vue post data.
	 *
	 * @since 4.4.9
	 *
	 * @return void
	 */
	private function setPostData() {
		if ( 'post' !== $this->args['page'] ) {
			return;
		}

		$postId         = $this->args['staticPostId'] ?: get_the_ID();
		$postTypeObj    = get_post_type_object( get_post_type( $postId ) );
		$post           = Models\Post::getPost( $postId );
		$staticHomePage = intval( get_option( 'page_on_front' ) );

		self::$data['currentPost'] = [
			'context'                        => 'post',
			'tags'                           => aioseo()->tags->getDefaultPostTags( $postId ),
			'id'                             => $postId,
			'priority'                       => isset( $post->priority ) && 'default' !== $post->priority ? $post->priority : 'default',
			'frequency'                      => ! empty( $post->frequency ) ? $post->frequency : 'default',
			'permalink'                      => get_permalink( $postId ),
			'editlink'                       => aioseo()->helpers->getPostEditLink( $postId ),
			'title'                          => ! empty( $post->title ) ? $post->title : aioseo()->meta->title->getPostTypeTitle( $postTypeObj->name ),
			'description'                    => ! empty( $post->description ) ? $post->description : aioseo()->meta->description->getPostTypeDescription( $postTypeObj->name ),
			'descriptionIncludeCustomFields' => apply_filters( 'aioseo_description_include_custom_fields', true, $post ),
			'keywords'                       => ! empty( $post->keywords ) ? $post->keywords : wp_json_encode( [] ),
			'keyphrases'                     => Models\Post::getKeyphrasesDefaults( $post->keyphrases ),
			'page_analysis'                  => ! empty( $post->page_analysis )
				? json_decode( $post->page_analysis )
				: Models\Post::getPageAnalysisDefaults(),
			'loading'                        => [
				'focus'      => false,
				'additional' => [],
			],
			'type'                           => $postTypeObj->labels->singular_name,
			'postType'                       => 'type' === $postTypeObj->name ? '_aioseo_type' : $postTypeObj->name,
			'postStatus'                     => get_post_status( $postId ),
			'isSpecialPage'                  => $this->isSpecialPage( $postId ),
			'isHomePage'                     => $postId === $staticHomePage,
			'isWooCommercePageWithoutSchema' => $this->isWooCommercePageWithoutSchema( $postId ),
			'seo_score'                      => (int) $post->seo_score,
			'pillar_content'                 => ( (int) $post->pillar_content ) === 0 ? false : true,
			'canonicalUrl'                   => $post->canonical_url,
			'default'                        => ( (int) $post->robots_default ) === 0 ? false : true,
			'noindex'                        => ( (int) $post->robots_noindex ) === 0 ? false : true,
			'noarchive'                      => ( (int) $post->robots_noarchive ) === 0 ? false : true,
			'nosnippet'                      => ( (int) $post->robots_nosnippet ) === 0 ? false : true,
			'nofollow'                       => ( (int) $post->robots_nofollow ) === 0 ? false : true,
			'noimageindex'                   => ( (int) $post->robots_noimageindex ) === 0 ? false : true,
			'noodp'                          => ( (int) $post->robots_noodp ) === 0 ? false : true,
			'notranslate'                    => ( (int) $post->robots_notranslate ) === 0 ? false : true,
			'maxSnippet'                     => null === $post->robots_max_snippet ? - 1 : (int) $post->robots_max_snippet,
			'maxVideoPreview'                => null === $post->robots_max_videopreview ? - 1 : (int) $post->robots_max_videopreview,
			'maxImagePreview'                => $post->robots_max_imagepreview,
			'modalOpen'                      => false,
			'generalMobilePrev'              => false,
			'socialMobilePreview'            => false,
			'og_object_type'                 => ! empty( $post->og_object_type ) ? $post->og_object_type : 'default',
			'og_title'                       => $post->og_title,
			'og_description'                 => $post->og_description,
			'og_image_custom_url'            => $post->og_image_custom_url,
			'og_image_custom_fields'         => $post->og_image_custom_fields,
			'og_image_type'                  => ! empty( $post->og_image_type ) ? $post->og_image_type : 'default',
			'og_video'                       => ! empty( $post->og_video ) ? $post->og_video : '',
			'og_article_section'             => ! empty( $post->og_article_section ) ? $post->og_article_section : '',
			'og_article_tags'                => ! empty( $post->og_article_tags ) ? $post->og_article_tags : wp_json_encode( [] ),
			'twitter_use_og'                 => ( (int) $post->twitter_use_og ) === 0 ? false : true,
			'twitter_card'                   => $post->twitter_card,
			'twitter_image_custom_url'       => $post->twitter_image_custom_url,
			'twitter_image_custom_fields'    => $post->twitter_image_custom_fields,
			'twitter_image_type'             => $post->twitter_image_type,
			'twitter_title'                  => $post->twitter_title,
			'twitter_description'            => $post->twitter_description,
			'schema'                         => ( ! empty( $post->schema ) )
				? Models\Post::getDefaultSchemaOptions( $post->schema )
				: Models\Post::getDefaultSchemaOptions(),
			'metaDefaults'                   => [
				'title'       => aioseo()->meta->title->getPostTypeTitle( $postTypeObj->name ),
				'description' => aioseo()->meta->description->getPostTypeDescription( $postTypeObj->name )
			],
			'linkAssistant'                  => [
				'modalOpen' => false
			],
			'limit_modified_date'            => ( (int) $post->limit_modified_date ) === 0 ? false : true,
			'redirects'                      => [
				'modalOpen' => false
			],
			'options'                        => $post->options
		];

		if ( empty( $this->args['integration'] ) ) {
			self::$data['integration'] = aioseo()->helpers->getPostPageBuilderName( $postId );
		}

		if ( ! $post->exists() ) {
			$oldPostMeta = aioseo()->migration->meta->getMigratedPostMeta( $postId );
			foreach ( $oldPostMeta as $k => $v ) {
				if ( preg_match( '#robots_.*#', $k ) ) {
					$oldPostMeta[ preg_replace( '#robots_#', '', $k ) ] = $v;
					continue;
				}
				if ( 'canonical_url' === $k ) {
					$oldPostMeta['canonicalUrl'] = $v;
				}
			}
			self::$data['currentPost'] = array_merge( self::$data['currentPost'], $oldPostMeta );
		}
	}

	/**
	 * Set Vue dashboard data.
	 *
	 * @since 4.4.9
	 *
	 * @return void
	 */
	private function setDashboardData() {
		if ( 'dashboard' !== $this->args['page'] ) {
			return;
		}

		self::$data['setupWizard']['isCompleted'] = aioseo()->standalone->setupWizard->isCompleted();
		self::$data['seoOverview']                = aioseo()->postSettings->getPostTypesOverview();
		self::$data['importers']                  = aioseo()->importExport->plugins();
	}

	/**
	 * Set Vue search statistics data.
	 *
	 * @since 4.4.9
	 *
	 * @return void
	 */
	private function setSearchStatisticsData() {
		if ( 'search-statistics' !== $this->args['page'] ) {
			return;
		}

		self::$data['seoOverview']      = aioseo()->postSettings->getPostTypesOverview();
		self::$data['searchStatistics'] = aioseo()->searchStatistics->getVueData();
	}

	/**
	 * Set Vue sitemaps data.
	 *
	 * @since 4.4.9
	 *
	 * @return void
	 */
	private function setSitemapsData() {
		if ( 'sitemaps' !== $this->args['page'] ) {
			return;
		}

		try {
			if ( as_next_scheduled_action( 'aioseo_static_sitemap_regeneration' ) ) {
				self::$data['scheduledActions']['sitemap'][] = 'staticSitemapRegeneration';
			}
		} catch ( \Exception $e ) {
			// Do nothing.
		}
	}

	/**
	 * Set Vue setup wizard data.
	 *
	 * @since 4.4.9
	 *
	 * @return void
	 */
	private function setSetupWizardData() {
		if ( 'setup-wizard' !== $this->args['page'] ) {
			return;
		}

		$isStaticHomePage = 'page' === get_option( 'show_on_front' );
		$staticHomePage   = intval( get_option( 'page_on_front' ) );

		self::$data['users']     = $this->getSiteUsers( [ 'administrator', 'editor', 'author' ] );
		self::$data['importers'] = aioseo()->importExport->plugins();
		self::$data['data']      += [
			'staticHomePageTitle'       => $isStaticHomePage ? aioseo()->meta->title->getTitle( $staticHomePage ) : '',
			'staticHomePageDescription' => $isStaticHomePage ? aioseo()->meta->description->getDescription( $staticHomePage ) : '',
		];
	}

	/**
	 * Set Vue search appearance data.
	 *
	 * @since 4.4.9
	 *
	 * @return void
	 */
	private function setSearchAppearanceData() {
		if ( 'search-appearance' !== $this->args['page'] ) {
			return;
		}

		$isStaticHomePage = 'page' === get_option( 'show_on_front' );
		$staticHomePage   = intval( get_option( 'page_on_front' ) );

		self::$data['users'] = $this->getSiteUsers( [ 'administrator', 'editor', 'author' ] );
		self::$data['data']  += [
			'staticHomePageTitle'       => $isStaticHomePage ? aioseo()->meta->title->getTitle( $staticHomePage ) : '',
			'staticHomePageDescription' => $isStaticHomePage ? aioseo()->meta->description->getDescription( $staticHomePage ) : '',
		];
	}

	/**
	 * Set Vue social networks data.
	 *
	 * @since 4.4.9
	 *
	 * @return void
	 */
	private function setSocialNetworksData() {
		if ( 'social-networks' !== $this->args['page'] ) {
			return;
		}

		$isStaticHomePage = 'page' === get_option( 'show_on_front' );
		$staticHomePage   = intval( get_option( 'page_on_front' ) );

		self::$data['data'] += [
			'staticHomePageOgTitle'            => $isStaticHomePage ? aioseo()->social->facebook->getTitle( $staticHomePage ) : '',
			'staticHomePageOgDescription'      => $isStaticHomePage ? aioseo()->social->facebook->getDescription( $staticHomePage ) : '',
			'staticHomePageTwitterTitle'       => $isStaticHomePage ? aioseo()->social->twitter->getTitle( $staticHomePage ) : '',
			'staticHomePageTwitterDescription' => $isStaticHomePage ? aioseo()->social->twitter->getDescription( $staticHomePage ) : '',
		];
	}

	/**
	 * Set Vue seo revisions data.
	 *
	 * @since 4.4.9
	 *
	 * @return void
	 */
	private function setSeoRevisionsData() {
		if ( 'post' === $this->args['page'] ) {
			self::$data['seoRevisions'] = aioseo()->seoRevisions->getVueDataEdit();
		}

		if ( 'seo-revisions' === $this->args['page'] ) {
			self::$data['seoRevisions'] = aioseo()->seoRevisions->getVueDataCompare();
		}
	}

	/**
	 * Set Vue tools or settings data.
	 *
	 * @since 4.4.9
	 *
	 * @return void
	 */
	private function setToolsOrSettingsData() {
		if (
			'tools' !== $this->args['page'] &&
			'settings' !== $this->args['page']
		) {
			return;
		}

		if ( 'tools' === $this->args['page'] ) {
			self::$data['backups']                = array_reverse( aioseo()->backup->all() );
			self::$data['importers']              = aioseo()->importExport->plugins();
			self::$data['data']['server']         = [
				'apache' => $this->isApache(),
				'nginx'  => $this->isNginx()
			];
			self::$data['data']['robots']         = [
				'defaultRules'      => $this->args['page'] ? aioseo()->robotsTxt->extractRules( aioseo()->robotsTxt->getDefaultRobotsTxtContent() ) : [],
				'hasPhysicalRobots' => aioseo()->robotsTxt->hasPhysicalRobotsTxt(),
				'rewriteExists'     => aioseo()->robotsTxt->rewriteRulesExist(),
				'sitemapUrls'       => array_merge( aioseo()->sitemap->helpers->getSitemapUrls(), aioseo()->sitemap->helpers->extractSitemapUrlsFromRobotsTxt() )
			];
			self::$data['data']['logSizes']       = [
				'badBotBlockerLog' => $this->convertFileSize( aioseo()->badBotBlocker->getLogSize() )
			];
			self::$data['data']['status']         = Tools\SystemStatus::getSystemStatusInfo();
			self::$data['data']['htaccess']       = aioseo()->htaccess->getContents();
			self::$data['data']['v3Options']      = ! empty( get_option( 'aioseop_options' ) );
			self::$data['integrations']['wpcode'] = [
				'snippets'          => WpCodeIntegration::loadWpCodeSnippets(),
				'pluginInstalled'   => WpCodeIntegration::isPluginInstalled(),
				'pluginActive'      => WpCodeIntegration::isPluginActive(),
				'pluginNeedsUpdate' => WpCodeIntegration::pluginNeedsUpdate()
			];
		}

		if ( 'settings' === $this->args['page'] ) {
			self::$data['breadcrumbs']['defaultTemplate'] = aioseo()->helpers->encodeOutputHtml( aioseo()->breadcrumbs->frontend->getDefaultTemplate() );
		}

		if (
			is_multisite() &&
			is_network_admin()
		) {
			self::$data['data']['network'] = [
				'sites'   => aioseo()->helpers->getSites( aioseo()->settings->tablePagination['networkDomains'] ),
				'backups' => []
			];
		}
	}

	/**
	 * Set Vue Divi data.
	 *
	 * @since 4.4.9
	 *
	 * @return void
	 */
	private function setDiviData() {
		if ( 'divi' !== $this->args['integration'] ) {
			return;
		}

		// This needs to be dropped in order to prevent JavaScript errors in Divi's visual builder.
		// Some of the data from the site analysis can contain HTML tags, e.g. the search preview, and somehow that causes JSON.parse to fail on our localized Vue data.
		unset( self::$data['internalOptions']['internal']['siteAnalysis'] );
	}

	/**
	 * Returns Jed-formatted localization data. Added for backwards-compatibility.
	 *
	 * @since 4.0.0
	 *
	 * @param  string $domain Translation domain.
	 * @return array          The information of the locale.
	 */
	public function getJedLocaleData( $domain ) {
		$translations = get_translations_for_domain( $domain );

		$locale = [
			'' => [
				'domain' => $domain,
				'lang'   => is_admin() && function_exists( 'get_user_locale' ) ? get_user_locale() : get_locale(),
			],
		];

		if ( ! empty( $translations->headers['Plural-Forms'] ) ) {
			$locale['']['plural_forms'] = $translations->headers['Plural-Forms'];
		}

		foreach ( $translations->entries as $msgid => $entry ) {
			if ( empty( $entry->translations ) || ! is_array( $entry->translations ) ) {
				continue;
			}

			foreach ( $entry->translations as $translation ) {
				// If any of the translated strings contains an HTML line break, we need to ignore it. Otherwise, logging into the admin breaks.
				if ( preg_match( '/<br[\s\/\\\\]*>/', $translation ) ) {
					continue 2;
				}
			}

			$locale[ $msgid ] = $entry->translations;
		}

		return $locale;
	}

	/**
	 * Whether the notifications drawer should be shown or not.
	 *
	 * @since 4.4.9
	 *
	 * @return bool True if it should be shown, false otherwise.
	 */
	private function showNotificationsDrawer() {
		static $showNotificationsDrawer = null;
		if ( null === $showNotificationsDrawer ) {
			$showNotificationsDrawer = (bool) aioseo()->core->cache->get( 'show_notifications_drawer' );

			// If this is set to true, let's disable it now, so it doesn't pop up again.
			if ( $showNotificationsDrawer ) {
				aioseo()->core->cache->delete( 'show_notifications_drawer' );
			}
		}

		return $showNotificationsDrawer;
	}
}