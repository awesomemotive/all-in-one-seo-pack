<?php
namespace AIOSEO\Plugin\Common\Traits\Helpers;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AIOSEO\Plugin\Common\Admin\Notices\Review as ReviewNotice;
use AIOSEO\Plugin\Common\Integrations\WpCode as WpCodeIntegration;
use AIOSEO\Plugin\Common\Models;
use AIOSEO\Plugin\Common\SpellChecker\Dictionary;
use AIOSEO\Plugin\Common\SpellChecker\SafeWords;
use AIOSEO\Plugin\Common\Tools;

/**
 * Contains all Vue related helper methods.
 *
 * @since 4.1.4
 */
trait Vue {
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
		if ( isset( $this->cache[ $hash ] ) ) {
			return $this->cache[ $hash ];
		}

		// Clear the data so we start fresh.
		$this->data = [];

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
		$this->setAiBulkGenerateData();
		$this->setToolsOrSettingsData();
		$this->setWritingAssistantData();
		$this->setBreadcrumbsData();
		$this->setSeoAnalyzerData();
		$this->setAiData();
		$this->setAiAssistantData();
		$this->setAiImageGeneratorData();
		$this->setAiInsightsData();
		$this->setGeneralSettingsData();

		$this->cache[ $hash ] = $this->data;

		return $this->cache[ $hash ];
	}

	/**
	 * Removes site-global configuration from a Vue data payload for users who cannot manage AIOSEO.
	 *
	 * Surfaces that localize the full payload for low-privilege users (the user's own profile page, the
	 * posts/terms list details column) call this so site options and the admin email are not disclosed.
	 * Pro features (e.g. Redirects) nest a copy of the payload under their own key, so that copy is
	 * stripped as well.
	 *
	 * @since 4.9.10
	 *
	 * @param  array $data A Vue data payload from {@see getVueData()}.
	 * @return array       The payload, with site-global configuration removed for non-managers.
	 */
	public function filterPrivilegedVueData( $data ) {
		if ( aioseo()->access->canManage() ) {
			return $data;
		}

		$data = $this->stripPrivilegedVueData( $data );

		// Pro features nest a copy of the payload under their own key (e.g. 'redirects'), which would
		// otherwise re-expose the groups removed above.
		if ( ! empty( $data['redirects'] ) && is_array( $data['redirects'] ) ) {
			$data['redirects'] = $this->stripPrivilegedVueData( $data['redirects'] );
		}

		return $data;
	}

	/**
	 * Removes the site-global configuration groups from a single Vue data payload level.
	 *
	 * @since 4.9.10
	 *
	 * @param  array $data A Vue data payload level.
	 * @return array       The payload level without site-global configuration.
	 */
	private function stripPrivilegedVueData( $data ) {
		unset( $data['options'], $data['internalOptions'], $data['dynamicOptions'] );

		if ( isset( $data['data']['adminEmail'] ) ) {
			unset( $data['data']['adminEmail'] );
		}

		return $data;
	}

	/**
	 * Set Vue initial data.
	 *
	 * @since 4.4.9
	 *
	 * @return void
	 */
	private function setInitialData() {
		$screen             = aioseo()->helpers->getCurrentScreen();
		$isStaticHomePage   = 'page' === get_option( 'show_on_front' );
		$staticHomePage     = intval( get_option( 'page_on_front' ) );
		$themeVersion       = aioseo()->helpers->getThemeVersion();
		$themeParentVersion = aioseo()->helpers->getThemeVersion( true );
		$settingsPagePath   = 'admin.php?page=aioseo-settings';

		$this->data = [
			'page'               => $this->args['page'],
			'screen'             => [
				'base'        => isset( $screen->base ) ? $screen->base : '',
				'postType'    => isset( $screen->post_type ) ? $screen->post_type : '',
				'blockEditor' => isset( $screen->is_block_editor ) ? $screen->is_block_editor : false,
				'new'         => isset( $screen->action ) && 'add' === $screen->action
			],
			'internalOptions'    => aioseo()->internalOptions->all(),
			'options'            => aioseo()->options->all(),
			'sensitiveOptions'   => array_merge(
				aioseo()->sensitiveOptions->allHas(),
				! empty( aioseo()->networkSensitiveOptions ) ? aioseo()->networkSensitiveOptions->allHas() : []
			),
			'dynamicOptions'     => aioseo()->dynamicOptions->all(),
			'deprecatedOptions'  => aioseo()->internalOptions->getAllDeprecatedOptions( true ),
			'settings'           => aioseo()->settings ? aioseo()->settings->all() : [],
			'additional_scripts' => apply_filters( 'aioseo_vue_additional_scripts_enabled', true ),
			'tags'               => aioseo()->tags->all( true ),
			'nonce'              => wp_create_nonce( 'wp_rest' ),
			'urls'               => [
				'domain'            => $this->getSiteDomain(),
				'mainSiteUrl'       => $this->getSiteUrl(),
				'siteFavicon'       => get_site_icon_url(),
				'siteLogo'          => aioseo()->helpers->getSiteLogoUrl(),
				'home'              => home_url(),
				'restUrl'           => aioseo()->helpers->getRestUrl(),
				'editScreen'        => admin_url( 'edit.php' ),
				'publicPath'        => aioseo()->core->assets->normalizeAssetsHost( plugin_dir_url( AIOSEO_FILE ) ),
				'assetsPath'        => aioseo()->core->assets->getAssetsPath(),
				'generalSitemapUrl' => aioseo()->sitemap->helpers->getUrl( 'general' ),
				'rssSitemapUrl'     => aioseo()->sitemap->helpers->getUrl( 'rss' ),
				'llmsUrl'           => aioseo()->helpers->getLlmsUrl(),
				'robotsTxtUrl'      => $this->getSiteUrl() . '/robots.txt',
				'marketingSiteUrl'  => $this->getMarketingSiteUrl(),
				'upgradeUrl'        => apply_filters( 'aioseo_upgrade_link', AIOSEO_MARKETING_URL . 'lite-upgrade/' ),
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
					'aiSuite'          => admin_url( 'admin.php?page=aioseo-ai-insights' ),
					'dashboard'        => admin_url( 'admin.php?page=aioseo' ),
					'featureManager'   => admin_url( 'admin.php?page=aioseo-feature-manager' ),
					'linkAssistant'    => admin_url( 'admin.php?page=aioseo-link-assistant' ),
					'localSeo'         => admin_url( 'admin.php?page=aioseo-local-seo' ),
					'monsterinsights'  => admin_url( 'admin.php?page=aioseo-monsterinsights' ),
					'redirects'        => admin_url( 'admin.php?page=aioseo-redirects' ),
					'searchAppearance' => admin_url( 'admin.php?page=aioseo-search-appearance' ),
					'searchStatistics' => admin_url( 'admin.php?page=aioseo-search-statistics' ),
					'seoAnalysis'      => admin_url( 'admin.php?page=aioseo-seo-analysis' ),
					'settings'         => admin_url( $settingsPagePath ),
					'sitemaps'         => admin_url( 'admin.php?page=aioseo-sitemaps' ),
					'socialNetworks'   => admin_url( 'admin.php?page=aioseo-social-networks' ),
					'tools'            => admin_url( 'admin.php?page=aioseo-tools' ),
					'wizard'           => admin_url( 'index.php?page=aioseo-setup-wizard' ),
					'networkSettings'  => is_network_admin() ? network_admin_url( $settingsPagePath ) : '',
					'seoRevisions'     => admin_url( 'admin.php?page=aioseo-seo-revisions' ),
					'aiBulkGenerate'   => admin_url( 'admin.php?page=aioseo-ai-bulk-generate' )
				],
				'admin'             => [
					'widgets'          => admin_url( 'widgets.php' ),
					'optionsReading'   => admin_url( 'options-reading.php' ),
					'scheduledActions' => admin_url( '/tools.php?page=action-scheduler&status=pending&s=aioseo' ),
					'generalSettings'  => admin_url( 'options-general.php' )
				],
			],
			'backups'            => [],
			'importers'          => [],
			'data'               => [
				'server'                => aioseo()->helpers->getServerName(),
				'robots'                => [
					'defaultRules'      => [],
					'hasPhysicalRobots' => null,
					'rewriteExists'     => null,
					'sitemapUrls'       => []
				],
				'status'                => [],
				'htaccess'              => '',
				'isMultisite'           => is_multisite(),
				'isNetworkAdmin'        => is_network_admin(),
				'currentBlogId'         => get_current_blog_id(),
				'mainSite'              => is_main_site(),
				'subdomain'             => $this->isSubdomain(),
				'isBBPressActive'       => class_exists( 'bbPress' ),
				'isClassicEditorActive' => $this->isClassicEditorActive(),
				'isWooCommerceActive'   => $this->isWooCommerceActive(),
				'isWooCommerceShopPage' => $this->isWooCommerceShopPage(),
				'staticHomePage'        => $isStaticHomePage ? $staticHomePage : false,
				'staticBlogPage'        => $this->getBlogPageId(),
				'staticBlogPageTitle'   => get_the_title( $this->getBlogPageId() ),
				'isDev'                 => $this->isDev(),
				'isLocal'               => $this->isLocalUrl( site_url() ),
				'isSsl'                 => is_ssl(),
				'hasUrlTrailingSlash'   => '/' === user_trailingslashit( '' ),
				'permalinkStructure'    => get_option( 'permalink_structure' ),
				'usingPermalinks'       => aioseo()->helpers->usingPermalinks(),
				'dateFormat'            => get_option( 'date_format' ),
				'timeFormat'            => get_option( 'time_format' ),
				'siteName'              => aioseo()->helpers->getWebsiteName(),
				'adminEmail'            => get_bloginfo( 'admin_email' ),
				'blocks'                => [
					'toc' => [
						'hashPrefix' => apply_filters( 'aioseo_toc_hash_prefix', 'aioseo-' )
					]
				],
				'vueComponentsDefaults' => $this->getVueComponentsDefaults(),
			],
			'plugins'            => $this->getPluginData(),
			'postData'           => [
				'postTypes'    => array_values( $this->getPublicPostTypes( false, false, true ) ),
				'taxonomies'   => array_values( $this->getPublicTaxonomies( false, true ) ),
				'archives'     => array_values( $this->getPublicPostTypes( false, true, true ) ),
				'postStatuses' => array_values( $this->getPublicPostStatuses() )
			],
			// Object types TruSEO can never analyse. Localized so the include-list controls hide them
			// instead of offering a checkbox that silently does nothing.
			'truSeoIneligible'   => [
				'postTypes'  => array_values( $this->getTruSeoIneligiblePostTypes() ),
				'taxonomies' => array_values( $this->getTruSeoIneligibleTaxonomies() )
			],
			'notifications'      => array_merge( Models\Notification::getNotifications( true ), [
				'force' => $this->showNotificationsDrawer()
			] ),
			'newsroom'           => [
				'items'      => array_map(
					function ( $item ) {
						// Tagged here rather than in the feed: the medium names the surface, and
						// the same item is served to the widget and modal under their own.
						$item['url'] = aioseo()->helpers->utmUrl( $item['url'], 'newsroom-drawer', null, false );
						// Formatted here so the drawer shows the site's date format without
						// reimplementing PHP's format tokens in JS.
						$item['dateFormatted'] = aioseo()->newsroom->formatDate( $item['date'] );

						return $item;
					},
					array_slice( aioseo()->newsroom->getItems(), 0, 6 )
				),
				'archiveUrl' => aioseo()->newsroom->getArchiveUrl( 'newsroom-drawer' )
			],
			'addons'             => aioseo()->addons->getAddons(),
			'features'           => aioseo()->features->getFeatures(),
			'version'            => AIOSEO_VERSION,
			'wpVersion'          => get_bloginfo( 'version' ),
			'phpVersion'         => PHP_VERSION,
			'helpPanel'          => aioseo()->help->getDocs(),
			'scheduledActions'   => [
				'sitemaps' => []
			],
			'integration'        => $this->args['integration'],
			'theme'              => [
				'features'        => aioseo()->helpers->getThemeFeatures(),
				'version'         => $themeVersion, // The active skin/child version
				'parentVersion'   => $themeParentVersion, // The parent version (nullable)
				'templateVersion' => $themeParentVersion ?? $themeVersion // Always the framework/base version
			]
		];

		// In multisite, super admins may not have explicit roles on subsites.
		// Ensure they have administrator role and capabilities for proper access.
		$userData     = wp_get_current_user();
		$roles        = $userData->roles;
		$capabilities = $userData->allcaps;

		// If the user is a network admin, and doesn't have a user on the subsite, give him admin role/caps.
		if ( is_multisite() && is_super_admin() && empty( $roles ) ) {
			$roles     = [ 'administrator' ];
			$adminRole = get_role( 'administrator' );
			if ( is_a( $adminRole, 'WP_Role' ) ) {
				$capabilities = $adminRole->capabilities;
			}
		}

		$this->data['user'] = [
			'login'          => $userData->user_login,
			'emailAddress'   => $userData->user_email,
			'roles'          => $roles,
			'capabilities'   => $capabilities,
			'customRoles'    => $this->getCustomRoles(),
			'userRoles'      => aioseo()->helpers->getUserRoles(),
			'locale'         => function_exists( 'get_user_locale' ) ? get_user_locale() : get_locale(),
			'unfilteredHtml' => current_user_can( 'unfiltered_html' ),
			'canManage'      => aioseo()->access->canManage()
		];

		$dictionary                 = new Dictionary();
		$safeWords                  = new SafeWords();
		// TruSEO analyzes the post content, which is in the site language — not the
		// editor's per-user admin locale. Resolve from get_locale() so the "Default"
		// option matches the content and the backend pre-download in Activate/Updates.
		$userLocaleResolved         = $dictionary->resolveUserLocale( get_locale() );
		$this->data['spellChecker'] = [
			'enabled'                 => (bool) aioseo()->options->advanced->spellChecker,
			'dictionaryBaseUrl'       => $dictionary->getDictionaryBaseUrl(),
			'safeWordsUrl'            => $safeWords->exists() ? $safeWords->getSafeWordsUrl() : '',
			'safeWordsMetaUrl'        => $safeWords->matchCaseExists() ? $safeWords->getMatchCaseUrl() : '',
			'settingsUrl'             => admin_url( $settingsPagePath ) . '#/advanced',
			'userLocale'              => $userLocaleResolved['locale'],
			'userLanguageLabel'       => $userLocaleResolved['nativeLabel'] ?: $userLocaleResolved['label'],
			'userLocaleSupported'     => $userLocaleResolved['supported'],
			'userLocaleHasSpellCheck' => $userLocaleResolved['hasSpellChecker'],
			'userLocaleNeedsDownload' => $userLocaleResolved['needsDownload'],
			'supportedLanguages'      => $dictionary->getSupportedLanguages(),
			'installedLocales'        => $dictionary->getInstalledLocales()
		];
	}

	/**
	 * Determines whether the 5-star review CTA should be shown in the post editor.
	 *
	 * NOTE: Eligibility is shared with the admin review notice, so acting on either one silences
	 * both. {@see \AIOSEO\Plugin\Common\Admin\Notices\Review::canPromptUser()}
	 *
	 * @since 5.0.2
	 *
	 * @return bool Whether the review CTA should be shown.
	 */
	private function shouldShowReviewCta() {
		if ( ! ReviewNotice::canPromptUser() ) {
			return false;
		}

		$minimumOptimizedPosts = 10;

		return $this->getOptimizedPostCount() >= $minimumOptimizedPosts;
	}

	/**
	 * Returns the number of optimized posts, i.e. published posts that have a TruSEO score.
	 *
	 * @since 5.0.2
	 *
	 * @return int The number of optimized posts.
	 */
	private function getOptimizedPostCount() {
		// Reuse the dashboard overview so what counts as an optimized post is defined once, and so
		// the count inherits its cache invalidation.
		$count = 0;
		foreach ( aioseo()->postSettings->getPostTypesOverview() as $overview ) {
			$count += $overview['needsImprovement'] + $overview['okay'] + $overview['good'];
		}

		return $count;
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

		$this->data['internalNetworkOptions'] = aioseo()->internalNetworkOptions->all();
		$this->data['networkOptions']         = aioseo()->networkOptions->all();
	}

	/**
	 * Set Vue post data.
	 *
	 * @since   4.4.9
	 * @version 5.0.0.1 Keyword columns fall back to the legacy keyphrases column.
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
		$wpPost         = get_post( $postId );
		$staticHomePage = intval( get_option( 'page_on_front' ) );
		$keywordColumns = Models\Post::getKeywordColumnsWithLegacyFallback( $post );

		$this->data['currentPost'] = [
			'context'                        => 'post',
			'tags'                           => aioseo()->tags->getDefaultPostTags( $postId ),
			'id'                             => $postId,
			'priority'                       => isset( $post->priority ) && null !== $post->priority ? (float) $post->priority : 'default',
			'frequency'                      => ! empty( $post->frequency ) ? $post->frequency : 'default',
			'permalink'                      => get_permalink( $postId ),
			'editlink'                       => aioseo()->helpers->getPostEditLink( $postId ),
			'title'                          => ! empty( $post->title ) ? $post->title : aioseo()->meta->title->getPostTypeTitle( $postTypeObj->name ),
			'description'                    => ! empty( $post->description ) ? $post->description : aioseo()->meta->description->getPostTypeDescription( $postTypeObj->name ),
			'descriptionIncludeCustomFields' => apply_filters( 'aioseo_description_include_custom_fields', true, $post ),
			'keywords'                       => ! empty( $post->keywords ) ? $post->keywords : [],
			'keyphrases'                     => Models\Post::getKeyphrasesDefaults( $post->keyphrases ),
			'page_analysis'                  => Models\Post::getPageAnalysisDefaults( $post->page_analysis ),
			'truseo'                         => Models\Post::getTruseoDefaults( $post->truseo ?? null ),
			'focus_keyword'                  => $keywordColumns['focus_keyword'],
			'additional_keywords'            => $keywordColumns['additional_keywords'],
			'truseo_locale'                  => $post->truseo_locale,
			'wooProduct'                     => aioseo()->helpers->getWooCommerceProductData( $postId ),
			'loading'                        => [
				'focus'      => false,
				'additional' => [],
				'score'      => false,
			],
			'type'                           => $postTypeObj->labels->singular_name,
			'postType'                       => 'type' === $postTypeObj->name ? '_aioseo_type' : $postTypeObj->name,
			'postStatus'                     => get_post_status( $postId ),
			'postAuthor'                     => (int) $wpPost->post_author,
			'isSpecialPage'                  => $this->isSpecialPage( $postId ),
			'isTruSeoEligible'               => $this->isTruSeoEligible( $postId ),
			'supportsPageAnalysis'           => $this->supportsPageAnalysis( $postId ),
			'isStaticPostsPage'              => aioseo()->helpers->isStaticPostsPage(),
			'isHomePage'                     => $postId === $staticHomePage,
			'isWooCommercePageWithoutSchema' => $this->isWooCommercePageWithoutSchema( $postId ),
			'seo_score'                      => (int) $post->seo_score,
			'showReviewCta'                  => $this->shouldShowReviewCta(),
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
			'maxSnippet'                     => null === $post->robots_max_snippet ? -1 : (int) $post->robots_max_snippet,
			'maxVideoPreview'                => null === $post->robots_max_videopreview ? -1 : (int) $post->robots_max_videopreview,
			'maxImagePreview'                => $post->robots_max_imagepreview,
			'modalOpen'                      => false,
			'generalMobilePrev'              => false,
			'og_object_type'                 => ! empty( $post->og_object_type ) ? $post->og_object_type : 'default',
			'og_title'                       => $post->og_title,
			'og_description'                 => $post->og_description,
			'og_image_custom_url'            => $post->og_image_custom_url,
			'og_image_custom_fields'         => $post->og_image_custom_fields,
			'og_image_type'                  => ! empty( $post->og_image_type ) ? $post->og_image_type : 'default',
			'og_video'                       => ! empty( $post->og_video ) ? $post->og_video : '',
			'og_article_section'             => ! empty( $post->og_article_section ) ? $post->og_article_section : '',
			'og_article_tags'                => ! empty( $post->og_article_tags ) ? $post->og_article_tags : [],
			'twitter_use_og'                 => ( (int) $post->twitter_use_og ) === 0 ? false : true,
			'twitter_card'                   => $post->twitter_card,
			'twitter_image_custom_url'       => $post->twitter_image_custom_url,
			'twitter_image_custom_fields'    => $post->twitter_image_custom_fields,
			'twitter_image_type'             => $post->twitter_image_type,
			'twitter_title'                  => $post->twitter_title,
			'twitter_description'            => $post->twitter_description,
			'ai'                             => Models\Post::getDefaultAiOptions( $post->ai ),
			'schema'                         => Models\Post::getDefaultSchemaOptions( $post->schema, aioseo()->helpers->getPost( $postId ) ),
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
			'options'                        => $post->options,
			'maxAdditionalKeyphrases'        => 0,
		];

		if ( empty( $this->args['integration'] ) ) {
			$this->data['integration'] = aioseo()->helpers->getPostPageBuilderName( $postId );
		}

		if ( ! $post->exists() ) {
			$oldPostMeta = aioseo()->migration->meta->getMigratedPostMeta( $postId );
			foreach ( $oldPostMeta as $k => $v ) {
				if ( preg_match( '#robots_.*#', (string) $k ) ) {
					$oldPostMeta[ preg_replace( '#robots_#', '', (string) $k ) ] = $v;
					continue;
				}
				if ( 'canonical_url' === $k ) {
					$oldPostMeta['canonicalUrl'] = $v;
				}
			}
			$this->data['currentPost'] = array_merge( $this->data['currentPost'], $oldPostMeta );
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

		$this->data['setupWizard']['isCompleted'] = aioseo()->standalone->setupWizard->isCompleted();
		$this->data['seoOverview']                = aioseo()->postSettings->getPostTypesOverview();
		$this->data['importers']                  = aioseo()->importExport->plugins();

		$this->setSeoChecklistData();
	}

	/**
	 * Set Vue search statistics data.
	 *
	 * @since 4.4.9
	 *
	 * @return void
	 */
	private function setSearchStatisticsData() {
		$this->data['searchStatistics'] = [
			'isConnected'        => aioseo()->searchStatistics->api->auth->isConnected(),
			'sitemapsWithErrors' => aioseo()->searchStatistics->sitemap->getSitemapsWithErrors(),
		];

		if ( 'post' === $this->args['page'] ) {
			$this->data['keywordRankTracker'] = aioseo()->searchStatistics->keywordRankTracker->getVueDataEdit();
		}

		if ( 'search-statistics' === $this->args['page'] ) {
			$this->data['seoOverview']        = aioseo()->postSettings->getPostTypesOverview();
			$this->data['searchStatistics']   = array_merge( $this->data['searchStatistics'], aioseo()->searchStatistics->getVueData() );
			$this->data['keywordRankTracker'] = aioseo()->searchStatistics->keywordRankTracker->getVueData();
			$this->data['indexStatus']        = aioseo()->searchStatistics->indexStatus->getVueData();
		}
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

		$this->data['data']['sitemapUrls'] = aioseo()->sitemap->helpers->getSitemapUrls();

		try {
			if ( as_next_scheduled_action( 'aioseo_static_sitemap_regeneration' ) ) {
				$this->data['scheduledActions']['sitemap'][] = 'staticSitemapRegeneration';
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

		$this->data['users']     = $this->getSiteUsers( [ 'administrator', 'editor', 'author' ] );
		$this->data['importers'] = aioseo()->importExport->plugins();
		$this->data['data']      += [
			'staticHomePageTitle'       => $isStaticHomePage ? aioseo()->meta->title->getTitle( $staticHomePage ) : '',
			'staticHomePageDescription' => $isStaticHomePage ? aioseo()->meta->description->getDescription( $staticHomePage ) : '',
		];

		$this->setSeoChecklistData();
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

		$this->data['users'] = $this->getSiteUsers( [ 'administrator', 'editor', 'author' ] );
		$this->data['data']  += [
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

		$this->data['data'] += [
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
			$this->data['seoRevisions'] = aioseo()->seoRevisions->getVueDataEdit( $this->args['staticPostId'] ?? null );
		}

		if ( 'seo-revisions' === $this->args['page'] ) {
			$this->data['seoRevisions'] = aioseo()->seoRevisions->getVueDataCompare();
		}
	}

	/**
	 * Set Vue AI bulk generate data.
	 *
	 * @since   4.9.6
	 * @version 5.0.3 Added the returnUrl key.
	 *
	 * @return void
	 */
	private function setAiBulkGenerateData() {
		if ( 'ai-bulk-generate' !== $this->args['page'] ) {
			return;
		}

		// phpcs:disable HM.Security.NonceVerification.Recommended, WordPress.Security.NonceVerification.Recommended
		$ids = [];
		if ( ! empty( $_GET['ids'] ) && is_string( $_GET['ids'] ) ) {
			$ids = array_map( 'intval', explode( ',', sanitize_text_field( wp_unslash( $_GET['ids'] ) ) ) );
			$ids = array_values( array_filter( $ids, function ( $id ) {
				return 0 < $id;
			} ) );
		}

		$type = 'title';
		if ( ! empty( $_GET['type'] ) && in_array( $_GET['type'], [ 'title', 'description', 'alt' ], true ) ) {
			$type = sanitize_text_field( wp_unslash( $_GET['type'] ) );
		}

		$returnUrl = '';
		if ( ! empty( $_GET['return'] ) && is_string( $_GET['return'] ) ) {
			$requested = wp_unslash( $_GET['return'] ); // phpcs:ignore HM.Security.ValidatedSanitizedInput.InputNotSanitized

			// Reject rather than repair: the value is interpolated into an HTML attribute without
			// escaping, so anything that does not survive both sanitizers byte for byte is dropped.
			if ( aioseo()->helpers->isUrl( $requested ) && wp_validate_redirect( $requested, '' ) === $requested ) {
				$returnUrl = $requested;
			}
		}
		// phpcs:enable HM.Security.NonceVerification.Recommended, WordPress.Security.NonceVerification.Recommended

		$postType    = '';
		$posts       = [];
		if ( ! empty( $ids ) ) {
			$isAlt       = 'alt' === $type;
			$postObjects = get_posts( [
				'post__in'               => $ids,
				'post_type'              => 'any',
				'post_status'            => 'any',
				'posts_per_page'         => count( $ids ),
				'orderby'                => 'post__in',
				'update_post_meta_cache' => false,
				'update_post_term_cache' => false
			] );

			if ( $isAlt ) {
				// Prime meta cache to avoid N+1 queries in wp_get_attachment_image_url().
				update_postmeta_cache( $ids );
			}

			if ( ! empty( $postObjects ) ) {
				$postType = $postObjects[0]->post_type;
			}

			foreach ( $postObjects as $post ) {
				// Intentionally missing the translation domain to use the WordPress core translation.
				$data = [ 'title' => $post->post_title ?: __( '(no title)' ) ]; // phpcs:ignore AIOSEO.Wp.I18n.MissingArgDomain, WordPress.WP.I18n.MissingArgDomain

				if ( $isAlt ) {
					$data['thumbnailUrl'] = wp_get_attachment_image_url( $post->ID, 'thumbnail' );
				}

				$posts[ $post->ID ] = $data;
			}
		}

		$this->data['aiBulkGenerate'] = [
			'ids'       => $ids,
			'type'      => $type,
			'postType'  => $postType,
			'posts'     => $posts,
			'returnUrl' => $returnUrl
		];
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
			$this->data['backups']                = array_reverse( aioseo()->backup->all() );
			$this->data['importers']              = aioseo()->importExport->plugins();
			$this->data['data']['robots']         = [
				'defaultRules'      => $this->args['page'] ? aioseo()->robotsTxt->extractRules( aioseo()->robotsTxt->getDefaultRobotsTxtContent() ) : [],
				'hasPhysicalRobots' => aioseo()->robotsTxt->hasPhysicalRobotsTxt(),
				'rewriteExists'     => aioseo()->robotsTxt->rewriteRulesExist(),
				'sitemapUrls'       => array_merge( aioseo()->sitemap->helpers->getSitemapUrlsPrefixed(), aioseo()->sitemap->helpers->extractSitemapUrlsFromRobotsTxt() )
			];
			$this->data['data']['status']         = Tools\SystemStatus::getSystemStatusInfo();
			$this->data['data']['htaccess']       = aioseo()->htaccess->getContents();
			$this->data['data']['v3Options']      = ! empty( get_option( 'aioseop_options' ) );
			$this->data['integrations']['wpcode'] = [
				'snippets'          => WpCodeIntegration::loadWpCodeSnippets(),
				'pluginInstalled'   => WpCodeIntegration::isPluginInstalled(),
				'pluginActive'      => WpCodeIntegration::isPluginActive(),
				'pluginNeedsUpdate' => WpCodeIntegration::pluginNeedsUpdate()
			];
		}

		if ( 'settings' === $this->args['page'] ) {
			$this->data['breadcrumbs']['defaultTemplate'] = aioseo()->helpers->encodeOutputHtml( aioseo()->breadcrumbs->frontend->getDefaultTemplate() );
		}

		if (
			is_multisite() &&
			is_network_admin()
		) {
			$this->data['data']['network'] = [
				'sites'   => aioseo()->helpers->getSites(),
				'backups' => []
			];
		}
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
				'lang'   => is_admin() && function_exists( 'get_user_locale' ) ? get_user_locale() : get_locale()
			],
		];

		if ( ! empty( $translations->headers['Plural-Forms'] ) ) {
			$locale['']['plural_forms'] = $translations->headers['Plural-Forms'];
		}

		foreach ( $translations->entries as $entry ) {
			if ( empty( $entry->translations ) || ! is_array( $entry->translations ) ) {
				continue;
			}

			foreach ( $entry->translations as $translation ) {
				// If any of the translated strings contains an HTML line break, we need to ignore it. Otherwise, logging into the admin breaks.

				if ( preg_match( '/<br[\s\/\\\\]*>/', (string) $translation ) ) {
					continue 2;
				}
			}

			// Set the translation data using the singular string as the index. This is how Jed expects it, even for plural strings.
			$locale[ $entry->singular ] = $entry->translations;
		}

		return $locale;
	}

	/**
	 * Set Vue writing assistant data.
	 *
	 * @since 4.7.4
	 *
	 * @return void
	 */
	private function setWritingAssistantData() {
		// Settings page or not a post screen.
		if (
			'settings' !== $this->args['page'] &&
			! aioseo()->helpers->isScreenBase( 'post' )
		) {
			return;
		}

		$this->data['writingAssistantSettings'] = aioseo()->writingAssistant->helpers->getSettingsVueData();
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

	/**
	 * Set Vue breadcrumbs data.
	 *
	 * @since 4.8.3
	 *
	 * @return void
	 */
	private function setBreadcrumbsData() {
		if (
			! empty( $this->args['page'] ) &&
			'settings' !== $this->args['page'] &&
			'post' !== $this->args['page'] &&
			! aioseo()->helpers->isScreenBase( 'term' )
		) {
			return;
		}

		$this->data['breadcrumbs']['defaultTemplate'] = aioseo()->helpers->encodeOutputHtml( aioseo()->breadcrumbs->frontend->getDefaultTemplate() );
	}

	/**
	 * Set Vue SEO Analyzer data.
	 *
	 * @since 4.8.3
	 *
	 * @return void
	 */
	private function setSeoAnalyzerData() {
		if ( 'seo-analysis' !== $this->args['page'] ) {
			return;
		}

		$this->data['analyzer'] = aioseo()->seoAnalysis->getVueData();
	}

	/**
	 * Set Vue AI data.
	 *
	 * @since 4.9.6
	 *
	 * @return void
	 */
	private function setAiData() {
		$this->data['ai'] = [
			'options' => aioseo()->ai->options
		];
	}

	/**
	 * Set Vue AI Assistant data.
	 *
	 * @since 4.9.1
	 *
	 * @return void
	 */
	private function setAiAssistantData() {
		if ( 'post' === $this->args['page'] ) {
			$this->data['aiAssistant'] = aioseo()->ai->assistant->getVueDataEdit();
		}
	}

	/**
	 * Set Vue AI Image Generator data.
	 *
	 * @since 4.8.9
	 *
	 * @return void
	 */
	private function setAiImageGeneratorData() {
		if ( 'post' === $this->args['page'] ) {
			$this->data['aiImageGenerator'] = aioseo()->ai->image->getVueDataEdit();
		}
	}

	/**
	 * Returns the marketing site URL.
	 *
	 * @since 4.8.4
	 *
	 * @return string The marketing site URL.
	 */
	private function getMarketingSiteUrl() {
		if ( defined( 'AIOSEO_MARKETING_SITE_URL' ) && AIOSEO_MARKETING_SITE_URL ) {
			return AIOSEO_MARKETING_SITE_URL;
		}

		return 'https://aioseo.com/';
	}

	/**
	 * Returns default values and settings for Vue components. These settings can be customized
	 * by a filter.
	 *
	 * @since 4.8.7
	 *
	 * @return array The default values for Vue components.
	 */
	private function getVueComponentsDefaults() {
		$defaults = [
			'fieldGroupRepeater' => [
				'maxGroups' => 50
			]
		];

		return apply_filters( 'aioseo_vue_components_defaults', $defaults );
	}

	/**
	 * Set Vue AI Insights data.
	 *
	 * @since   4.9.1
	 * @version 4.9.8 Added the `mcp` sub-array with server-side status data for the AIOSEO MCP tab.
	 * @version 5.0.3 `mcpAdapterActive` reports the adapter plugin's state; added `mcpServerRegistered`.
	 *
	 * @return void
	 */
	private function setAiInsightsData() {
		if ( 'ai-insights' !== $this->args['page'] ) {
			return;
		}

		$rateLimit        = aioseo()->core->cache->get( 'ai_insights_rate_limit' );
		$mcpAdapterActive = '' !== \AIOSEO\Plugin\Common\Api\AiAgents::getActiveMcpAdapterFile();

		// A plugin vendoring the adapter (WooCommerce's MCP feature) registers the server with no adapter plugin.
		// Runs before wp_get_abilities(): the default server hooks its own abilities in from `rest_api_init`.
		$mcpServerRegistered = ! $mcpAdapterActive && \AIOSEO\Plugin\Common\Api\AiAgents::isMcpServerRegistered();

		$this->data['aiInsights'] = [
			'rateLimit' => ! empty( $rateLimit ) ? $rateLimit : null,
			'mcp'       => [
				'abilitiesApiAvailable' => function_exists( 'wp_register_ability' ),
				// Total across all plugins; 0 on WP 6.9+ means the Abilities API is being suppressed
				// since Core always registers its own abilities.
				'totalAbilities'        => function_exists( 'wp_get_abilities' ) ? count( wp_get_abilities() ) : 0,
				'mcpAdapterActive'      => $mcpAdapterActive,
				'mcpServerRegistered'   => $mcpServerRegistered,
				'mcpAdapterInstalled'   => '' !== \AIOSEO\Plugin\Common\Api\AiAgents::getInstalledMcpAdapterFile(),
				'hasAppPassword'        => $this->currentUserHasMcpAppPassword(),
				// `supported` is core's HTTPS/local-env gate (replicated inline — the core helper
				// is WP 5.9+); `available` also accounts for a security plugin or filter/constant
				// disabling the feature. Each false-by-cause drives distinct guidance in the UI.
				'appPasswordsSupported' => is_ssl() || 'local' === wp_get_environment_type(),
				'appPasswordsAvailable' => $this->applicationPasswordsAvailable(),
				'abilities'             => $this->getRegisteredMcpAbilities()
			]
		];
	}

	/**
	 * Returns the AIOSEO abilities registered with the WordPress Abilities API.
	 *
	 * @since 4.9.8
	 *
	 * @return array The registered AIOSEO abilities with name, label, description and category data.
	 */
	private function getRegisteredMcpAbilities() {
		if ( ! function_exists( 'wp_get_abilities' ) ) {
			return [];
		}

		$categoryLabels = [];
		if ( function_exists( 'wp_get_ability_categories' ) ) {
			foreach ( wp_get_ability_categories() as $category ) {
				$categoryLabels[ $category->get_slug() ] = $category->get_label();
			}
		}

		$abilities = [];
		foreach ( wp_get_abilities() as $ability ) {
			$name = $ability->get_name();
			if ( 0 !== strpos( $name, 'aioseo-' ) ) {
				continue;
			}

			$category    = $ability->get_category();
			$abilities[] = [
				'name'          => $name,
				'label'         => $ability->get_label(),
				'description'   => $ability->get_description(),
				'category'      => $category,
				'categoryLabel' => ! empty( $categoryLabels[ $category ] ) ? $categoryLabels[ $category ] : $category
			];
		}

		return $abilities;
	}

	/**
	 * Checks whether Application Passwords can be generated for the current user.
	 *
	 * Returns false when the feature is disabled — by the HTTPS/local-env gate, a security
	 * plugin, or the `wp_is_application_passwords_available[_for_user]` filter/constant.
	 * The per-user core function internally calls the global one, so this covers every cause.
	 *
	 * @since 4.9.10
	 *
	 * @return bool
	 */
	private function applicationPasswordsAvailable() {
		if ( ! function_exists( 'wp_is_application_passwords_available' ) ) {
			return false;
		}

		$userId = get_current_user_id();
		if ( $userId && function_exists( 'wp_is_application_passwords_available_for_user' ) ) {
			return wp_is_application_passwords_available_for_user( $userId );
		}

		return wp_is_application_passwords_available();
	}

	/**
	 * Checks whether the current user already has an AIOSEO MCP Application Password.
	 *
	 * @since 4.9.8
	 *
	 * @return bool Whether the current user has an Application Password generated by AIOSEO MCP.
	 */
	private function currentUserHasMcpAppPassword() {
		$userId = get_current_user_id();
		if ( ! $userId || ! class_exists( 'WP_Application_Passwords' ) ) {
			return false;
		}

		foreach ( \WP_Application_Passwords::get_user_application_passwords( $userId ) as $appPassword ) {
			if ( ! empty( $appPassword['app_id'] ) && 'aioseo-mcp' === $appPassword['app_id'] ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Set Vue General Settings data.
	 *
	 * @since 4.9.4
	 *
	 * @return void
	 */
	protected function setGeneralSettingsData() {
		if ( 'settings' !== $this->args['page'] ) {
			return;
		}

		$this->setSeoChecklistData();
	}


	/**
	 * Set Vue SEO Checklist data.
	 *
	 * @since 4.9.4
	 *
	 * @return void
	 */
	protected function setSeoChecklistData() {
		$this->data['seoChecklist'] = aioseo()->seoChecklist->getChecks();
	}
}