<?php
namespace AIOSEO\Plugin\Common\Api;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Api class for the admin.
 *
 * @since 4.0.0
 */
class Api {
	/**
	 * The REST API Namespace
	 *
	 * @since 4.0.0
	 *
	 * @var string
	 */
	public $namespace = 'aioseo/v1';

	/**
	 * The routes we use in the rest API.
	 *
	 * @since 4.0.0
	 *
	 * @var array
	 */
	protected $routes = [
		// phpcs:disable WordPress.Arrays.ArrayDeclarationSpacing.AssociativeArrayFound
		'GET'    => [
			'options'                                     => [ 'callback' => [ 'Settings', 'getOptions' ], 'access' => 'everyone' ],
			'ping'                                        => [ 'callback' => [ 'Ping', 'ping' ], 'access' => 'everyone' ],
			'post'                                        => [ 'callback' => [ 'PostsTerms', 'getPostData' ], 'access' => 'everyone' ],
			'post/(?P<postId>[\d]+)/first-attached-image' => [ 'callback' => [ 'PostsTerms', 'getFirstAttachedImage' ], 'access' => 'aioseo_page_social_settings' ],
			'user/(?P<userId>[\d]+)/image'                => [ 'callback' => [ 'User', 'getUserImage' ], 'access' => 'aioseo_page_social_settings' ],
			'tags'                                        => [ 'callback' => [ 'Tags', 'getTags' ], 'access' => 'everyone' ],
			'search-statistics/url/auth'                  => [ 'callback' => [ 'SearchStatistics', 'getAuthUrl' ], 'access' => [ 'aioseo_search_statistics_settings', 'aioseo_general_settings', 'aioseo_setup_wizard' ] ], // phpcs:ignore Generic.Files.LineLength.MaxExceeded
			'search-statistics/url/reauth'                => [ 'callback' => [ 'SearchStatistics', 'getReauthUrl' ], 'access' => [ 'aioseo_search_statistics_settings', 'aioseo_general_settings' ] ]
		],
		'POST'   => [
			'htaccess'                                              => [ 'callback' => [ 'Tools', 'saveHtaccess' ], 'access' => 'aioseo_tools_settings' ],
			'post'                                                  => [
				'callback' => [ 'PostsTerms', 'updatePosts' ],
				'access'   => [
					'aioseo_page_analysis',
					'aioseo_page_general_settings',
					'aioseo_page_advanced_settings',
					'aioseo_page_schema_settings',
					'aioseo_page_social_settings'
				]
			],
			'post/(?P<postId>[\d]+)/disable-primary-term-education' => [ 'callback' => [ 'PostsTerms', 'disablePrimaryTermEducation' ], 'access' => 'aioseo_page_general_settings' ],
			'post/(?P<postId>[\d]+)/disable-link-format-education'  => [ 'callback' => [ 'PostsTerms', 'disableLinkFormatEducation' ], 'access' => 'aioseo_page_general_settings' ],
			'post/(?P<postId>[\d]+)/update-internal-link-count'     => [ 'callback' => [ 'PostsTerms', 'updateInternalLinkCount' ], 'access' => 'aioseo_page_general_settings' ],
			'post/(?P<postId>[\d]+)/process-content'                => [ 'callback' => [ 'PostsTerms', 'processContent' ], 'access' => 'aioseo_page_general_settings' ],
			'posts-list/load-details-column'                        => [ 'callback' => [ 'PostsTerms', 'loadPostDetailsColumn' ], 'access' => 'aioseo_page_general_settings' ],
			'posts-list/update-details-column'                      => [ 'callback' => [ 'PostsTerms', 'updatePostDetailsColumn' ], 'access' => 'aioseo_page_general_settings' ],
			'terms-list/load-details-column'                        => [ 'callback' => [ 'PostsTerms', 'loadTermDetailsColumn' ], 'access' => 'aioseo_page_general_settings' ],
			'terms-list/update-details-column'                      => [ 'callback' => [ 'PostsTerms', 'updateTermDetailsColumn' ], 'access' => 'aioseo_page_general_settings' ],
			'keyphrases'                                            => [ 'callback' => [ 'PostsTerms', 'updatePostKeyphrases' ], 'access' => 'aioseo_page_analysis' ],
			'analyze'                                               => [ 'callback' => [ 'Analyze', 'analyzeSite' ], 'access' => 'aioseo_seo_analysis_settings' ],
			'analyze-headline'                                      => [ 'callback' => [ 'Analyze', 'analyzeHeadline' ], 'access' => 'everyone' ],
			'analyze-headline/delete'                               => [ 'callback' => [ 'Analyze', 'deleteHeadline' ], 'access' => 'aioseo_seo_analysis_settings' ],
			'analyze/delete-site'                                   => [ 'callback' => [ 'Analyze', 'deleteSite' ], 'access' => 'aioseo_seo_analysis_settings' ],
			'clear-log'                                             => [ 'callback' => [ 'Tools', 'clearLog' ], 'access' => 'aioseo_tools_settings' ],
			'connect'                                               => [ 'callback' => [ 'Connect', 'saveConnectToken' ], 'access' => [ 'aioseo_general_settings', 'aioseo_setup_wizard' ] ],
			'connect-pro'                                           => [ 'callback' => [ 'Connect', 'processConnect' ], 'access' => [ 'aioseo_general_settings', 'aioseo_setup_wizard' ] ],
			'connect-url'                                           => [ 'callback' => [ 'Connect', 'getConnectUrl' ], 'access' => [ 'aioseo_general_settings', 'aioseo_setup_wizard' ] ],
			'backup'                                                => [ 'callback' => [ 'Tools', 'createBackup' ], 'access' => 'aioseo_tools_settings' ],
			'backup/restore'                                        => [ 'callback' => [ 'Tools', 'restoreBackup' ], 'access' => 'aioseo_tools_settings' ],
			'email-debug-info'                                      => [ 'callback' => [ 'Tools', 'emailDebugInfo' ], 'access' => 'aioseo_tools_settings' ],
			'migration/fix-blank-formats'                           => [ 'callback' => [ 'Migration', 'fixBlankFormats' ], 'access' => 'any' ],
			'notification/blog-visibility-reminder'                 => [ 'callback' => [ 'Notifications', 'blogVisibilityReminder' ], 'access' => 'any' ],
			'notification/description-format-reminder'              => [ 'callback' => [ 'Notifications', 'descriptionFormatReminder' ], 'access' => 'any' ],
			'notification/conflicting-plugins-reminder'             => [ 'callback' => [ 'Notifications', 'conflictingPluginsReminder' ], 'access' => 'any' ],
			'notification/install-addons-reminder'                  => [ 'callback' => [ 'Notifications', 'installAddonsReminder' ], 'access' => 'any' ],
			'notification/install-aioseo-image-seo-reminder'        => [ 'callback' => [ 'Notifications', 'installImageSeoReminder' ], 'access' => 'any' ],
			'notification/install-aioseo-local-business-reminder'   => [ 'callback' => [ 'Notifications', 'installLocalBusinessReminder' ], 'access' => 'any' ],
			'notification/install-aioseo-news-sitemap-reminder'     => [ 'callback' => [ 'Notifications', 'installNewsSitemapReminder' ], 'access' => 'any' ],
			'notification/install-aioseo-video-sitemap-reminder'    => [ 'callback' => [ 'Notifications', 'installVideoSitemapReminder' ], 'access' => 'any' ],
			'notification/install-mi-reminder'                      => [ 'callback' => [ 'Notifications', 'installMiReminder' ], 'access' => 'any' ],
			'notification/install-om-reminder'                      => [ 'callback' => [ 'Notifications', 'installOmReminder' ], 'access' => 'any' ],
			'notification/v3-migration-custom-field-reminder'       => [ 'callback' => [ 'Notifications', 'migrationCustomFieldReminder' ], 'access' => 'any' ],
			'notification/v3-migration-schema-number-reminder'      => [ 'callback' => [ 'Notifications', 'migrationSchemaNumberReminder' ], 'access' => 'any' ],
			'notifications/dismiss'                                 => [ 'callback' => [ 'Notifications', 'dismissNotifications' ], 'access' => 'any' ],
			'objects'                                               => [ 'callback' => [ 'PostsTerms', 'searchForObjects' ], 'access' => [ 'aioseo_search_appearance_settings', 'aioseo_sitemap_settings' ] ], // phpcs:ignore Generic.Files.LineLength.MaxExceeded
			'options'                                               => [ 'callback' => [ 'Settings', 'saveChanges' ], 'access' => 'any' ],
			'plugins/deactivate'                                    => [ 'callback' => [ 'Plugins', 'deactivatePlugins' ], 'access' => 'aioseo_feature_manager_settings' ],
			'plugins/install'                                       => [ 'callback' => [ 'Plugins', 'installPlugins' ], 'access' => [ 'install_plugins', 'aioseo_feature_manager_settings' ] ],
			'plugins/upgrade'                                       => [ 'callback' => [ 'Plugins', 'upgradePlugins' ], 'access' => [ 'update_plugins', 'aioseo_feature_manager_settings' ] ],
			'reset-settings'                                        => [ 'callback' => [ 'Settings', 'resetSettings' ], 'access' => 'aioseo_tools_settings' ],
			'search-statistics/sitemap/delete'                      => [ 'callback' => [ 'SearchStatistics', 'deleteSitemap' ], 'access' => [ 'aioseo_search_statistics_settings', 'aioseo_general_settings' ] ], // phpcs:ignore Generic.Files.LineLength.MaxExceeded
			'search-statistics/sitemap/ignore'                      => [ 'callback' => [ 'SearchStatistics', 'ignoreSitemap' ], 'access' => [ 'aioseo_search_statistics_settings', 'aioseo_general_settings' ] ], // phpcs:ignore Generic.Files.LineLength.MaxExceeded
			'settings/export'                                       => [ 'callback' => [ 'Settings', 'exportSettings' ], 'access' => 'aioseo_tools_settings' ],
			'settings/export-content'                               => [ 'callback' => [ 'Settings', 'exportContent' ], 'access' => 'aioseo_tools_settings' ],
			'settings/hide-setup-wizard'                            => [ 'callback' => [ 'Settings', 'hideSetupWizard' ], 'access' => 'any' ],
			'settings/hide-upgrade-bar'                             => [ 'callback' => [ 'Settings', 'hideUpgradeBar' ], 'access' => 'any' ],
			'settings/import'                                       => [ 'callback' => [ 'Settings', 'importSettings' ], 'access' => 'aioseo_tools_settings' ],
			'settings/import/(?P<siteId>[\d]+)'                     => [ 'callback' => [ 'Settings', 'importSettings' ], 'access' => 'aioseo_tools_settings' ],
			'settings/import-plugins'                               => [ 'callback' => [ 'Settings', 'importPlugins' ], 'access' => 'aioseo_tools_settings' ],
			'settings/toggle-card'                                  => [ 'callback' => [ 'Settings', 'toggleCard' ], 'access' => 'any' ],
			'settings/toggle-radio'                                 => [ 'callback' => [ 'Settings', 'toggleRadio' ], 'access' => 'any' ],
			'settings/dismiss-alert'                                => [ 'callback' => [ 'Settings', 'dismissAlert' ], 'access' => 'any' ],
			'settings/items-per-page'                               => [ 'callback' => [ 'Settings', 'changeItemsPerPage' ], 'access' => 'any' ],
			'settings/do-task'                                      => [ 'callback' => [ 'Settings', 'doTask' ], 'access' => 'aioseo_tools_settings' ],
			'sitemap/deactivate-conflicting-plugins'                => [ 'callback' => [ 'Sitemaps', 'deactivateConflictingPlugins' ], 'access' => 'any' ],
			'sitemap/delete-static-files'                           => [ 'callback' => [ 'Sitemaps', 'deleteStaticFiles' ], 'access' => 'aioseo_sitemap_settings' ],
			'sitemap/validate-html-sitemap-slug'                    => [ 'callback' => [ 'Sitemaps', 'validateHtmlSitemapSlug' ], 'access' => 'aioseo_sitemap_settings' ],
			'tools/delete-robots-txt'                               => [ 'callback' => [ 'Tools', 'deleteRobotsTxt' ], 'access' => 'aioseo_tools_settings' ],
			'tools/import-robots-txt'                               => [ 'callback' => [ 'Tools', 'importRobotsTxt' ], 'access' => 'aioseo_tools_settings' ],
			'wizard'                                                => [ 'callback' => [ 'Wizard', 'saveWizard' ], 'access' => 'aioseo_setup_wizard' ],
			'integration/semrush/authenticate'                      => [
				'callback' => [ 'Semrush', 'semrushAuthenticate', 'AIOSEO\\Plugin\\Common\\Api\\Integrations' ],
				'access'   => 'aioseo_page_analysis'
			],
			'integration/semrush/refresh'                           => [
				'callback' => [ 'Semrush', 'semrushRefresh', 'AIOSEO\\Plugin\\Common\\Api\\Integrations' ],
				'access'   => 'aioseo_page_analysis'
			],
			'integration/semrush/keyphrases'                        => [
				'callback' => [ 'Semrush', 'semrushGetKeyphrases', 'AIOSEO\\Plugin\\Common\\Api\\Integrations' ],
				'access'   => 'aioseo_page_analysis'
			],
			'integration/wpcode/snippets'                           => [
				'callback' => [ 'WpCode', 'getSnippets', 'AIOSEO\\Plugin\\Common\\Api\\Integrations' ],
				'access'   => 'aioseo_tools_settings'
			],
			'crawl-cleanup'                                         => [
				'callback' => [ 'CrawlCleanup', 'fetchLogs', 'AIOSEO\\Plugin\\Common\\QueryArgs' ],
				'access'   => [ 'aioseo_search_appearance_settings' ]
			],
			'crawl-cleanup/block'                                   => [
				'callback' => [ 'CrawlCleanup', 'blockArg', 'AIOSEO\\Plugin\\Common\\QueryArgs' ],
				'access'   => [ 'aioseo_search_appearance_settings' ]
			],
			'crawl-cleanup/delete-blocked'                          => [
				'callback' => [ 'CrawlCleanup', 'deleteBlocked', 'AIOSEO\\Plugin\\Common\\QueryArgs' ],
				'access'   => [ 'aioseo_search_appearance_settings' ]
			],
			'crawl-cleanup/delete-unblocked'                        => [
				'callback' => [ 'CrawlCleanup', 'deleteLog', 'AIOSEO\\Plugin\\Common\\QueryArgs' ],
				'access'   => [ 'aioseo_search_appearance_settings' ]
			],
			'email-summary/send'                                    => [
				'callback' => [ 'EmailSummary', 'send' ],
				'access'   => 'aioseo_page_advanced_settings'
			],
		],
		'DELETE' => [
			'backup'                 => [ 'callback' => [ 'Tools', 'deleteBackup' ], 'access' => 'aioseo_tools_settings' ],
			'search-statistics/auth' => [ 'callback' => [ 'SearchStatistics', 'deleteAuth' ], 'access' => [ 'aioseo_search_statistics_settings', 'aioseo_general_settings' ] ]
		]
		// phpcs:enable WordPress.Arrays.ArrayDeclarationSpacing.AssociativeArrayFound
	];

	/**
	 * Class constructor.
	 *
	 * @since 4.0.0
	 */
	public function __construct() {
		add_filter( 'rest_allowed_cors_headers', [ $this, 'allowedHeaders' ] );
		add_action( 'rest_api_init', [ $this, 'registerRoutes' ] );
	}

	/**
	 * Get all the routes to register.
	 *
	 * @since 4.0.0
	 *
	 * @return array An array of routes.
	 */
	protected function getRoutes() {
		return $this->routes;
	}

	/**
	 * Registers the API routes.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function registerRoutes() {
		$class = new \ReflectionClass( get_called_class() );
		foreach ( $this->getRoutes() as $method => $data ) {
			foreach ( $data as $route => $options ) {
				register_rest_route(
					$this->namespace,
					$route,
					[
						'methods'             => $method,
						'permission_callback' => empty( $options['permissions'] ) ? [ $this, 'validRequest' ] : [ $this, $options['permissions'] ],
						'callback'            => is_array( $options['callback'] )
							? [
								(
									! empty( $options['callback'][2] )
										? $options['callback'][2] . '\\' . $options['callback'][0]
										: (
											class_exists( $class->getNamespaceName() . '\\' . $options['callback'][0] )
												? $class->getNamespaceName() . '\\' . $options['callback'][0]
												: __NAMESPACE__ . '\\' . $options['callback'][0]
										)
								),
								$options['callback'][1]
							]
							: [ $this, $options['callback'] ]
					]
				);
			}
		}
	}

	/**
	 * Sets headers that are allowed for our API routes.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function allowHeaders() {
		// TODO: Remove this entire function after a while. It's only here to ensure compatibility with people that are still using Image SEO 1.0.3 or lower.
		header( 'Access-Control-Allow-Headers: X-WP-Nonce' );
	}

	/**
	 * Sets headers that are allowed for our API routes.
	 *
	 * @since 4.1.1
	 *
	 * @param  array $allowHeaders The allowed request headers.
	 * @return array $allowHeaders The allowed request headers.
	 */
	public function allowedHeaders( $allowHeaders ) {
		if ( ! array_search( 'X-WP-Nonce', $allowHeaders, true ) ) {
			$allowHeaders[] = 'X-WP-Nonce';
		}

		return $allowHeaders;
	}

	/**
	 * Determine if logged in or has the proper permissions.
	 *
	 * @since 4.0.0
	 *
	 * @param  \WP_REST_Request $request The REST Request.
	 * @return bool                      True if validated, false if not.
	 */
	public function validRequest( $request ) {
		return is_user_logged_in() && $this->validateAccess( $request );
	}

	/**
	 * Validates access from the routes array.
	 *
	 * @since 4.0.0
	 *
	 * @param  \WP_REST_Request $request The REST Request.
	 * @return bool                      True if validated, false if not.
	 */
	public function validateAccess( $request ) {
		$routeData = $this->getRouteData( $request );
		if ( empty( $routeData ) || empty( $routeData['access'] ) ) {
			return false;
		}

		// Admins always have access.
		if ( aioseo()->access->isAdmin() ) {
			return true;
		}

		switch ( $routeData['access'] ) {
			case 'everyone':
				// Any user is able to access the route.
				return true;
			default:
				return aioseo()->access->hasCapability( $routeData['access'] );
		}
	}

	/**
	 * Returns the data for the route that is being accessed.
	 *
	 * @since 4.1.6
	 *
	 * @param  \WP_REST_Request $request The REST Request.
	 * @return array                     The route data.
	 */
	protected function getRouteData( $request ) {
		// NOTE: Since WordPress uses case-insensitive patterns to match routes,
		// we are forcing everything to lowercase to ensure we have the proper route.
		// This prevents users with lower privileges from accessing routes they shouldn't.
		$route     = aioseo()->helpers->toLowercase( $request->get_route() );
		$route     = untrailingslashit( str_replace( '/' . $this->namespace . '/', '', $route ) );
		$routeData = isset( $this->getRoutes()[ $request->get_method() ][ $route ] ) ? $this->getRoutes()[ $request->get_method() ][ $route ] : [];

		// No direct route name, let's try the regexes.
		if ( empty( $routeData ) ) {
			foreach ( $this->getRoutes()[ $request->get_method() ] as $routeRegex => $routeInfo ) {
				$routeRegex = str_replace( '@', '\@', $routeRegex );
				if ( preg_match( "@{$routeRegex}@", $route ) ) {
					$routeData = $routeInfo;
					break;
				}
			}
		}

		return $routeData;
	}
}