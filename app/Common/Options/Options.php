<?php
namespace AIOSEO\Plugin\Common\Options;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AIOSEO\Plugin\Common\Models;
use AIOSEO\Plugin\Common\Traits;

/**
 * Class that holds all options for AIOSEO.
 *
 * @since 4.0.0
 */
class Options {
	use Traits\Options;

	/**
	 * All the default options.
	 *
	 * @since 4.0.0
	 *
	 * @var array
	 */
	protected $defaults = [
		// phpcs:disable WordPress.Arrays.ArrayDeclarationSpacing.AssociativeArrayFound
		'internal'         => [],
		'webmasterTools'   => [
			'google'                    => [ 'type' => 'string' ],
			'bing'                      => [ 'type' => 'string' ],
			'yandex'                    => [ 'type' => 'string' ],
			'baidu'                     => [ 'type' => 'string' ],
			'pinterest'                 => [ 'type' => 'string' ],
			'microsoftClarityProjectId' => [ 'type' => 'string' ],
			'norton'                    => [ 'type' => 'string' ],
			'miscellaneousVerification' => [ 'type' => 'html' ]
		],
		'breadcrumbs'      => [
			'separator'             => [ 'type' => 'string', 'default' => '&raquo;' ],
			'homepageLink'          => [ 'type' => 'boolean', 'default' => true ],
			'homepageLabel'         => [ 'type' => 'string', 'default' => 'Home' ],
			'breadcrumbPrefix'      => [ 'type' => 'string', 'localized' => true, 'default' => '' ],
			'archiveFormat'         => [ 'type' => 'string', 'default' => 'Archives for #breadcrumb_archive_post_type_name', 'localized' => true ],
			'searchResultFormat'    => [ 'type' => 'string', 'default' => 'Search Results for \'#breadcrumb_search_string\'', 'localized' => true ],
			'errorFormat404'        => [ 'type' => 'string', 'default' => '404 - Page Not Found', 'localized' => true ],
			'showCurrentItem'       => [ 'type' => 'boolean', 'default' => true ],
			'linkCurrentItem'       => [ 'type' => 'boolean', 'default' => false ],
			'categoryFullHierarchy' => [ 'type' => 'boolean', 'default' => false ],
			'showBlogHome'          => [ 'type' => 'boolean', 'default' => false ]
		],
		'rssContent'       => [
			'before' => [ 'type' => 'html' ],
			'after'  => [
				'type'    => 'html',
				'default' => <<<TEMPLATE
&lt;p&gt;The post #post_link first appeared on #site_link.&lt;/p&gt;
TEMPLATE
			]
		],
		'advanced'         => [
			'truSeo'           => [ 'type' => 'boolean', 'default' => true ],
			'headlineAnalyzer' => [ 'type' => 'boolean', 'default' => true ],
			'seoAnalysis'      => [ 'type' => 'boolean', 'default' => true ],
			'dashboardWidgets' => [ 'type' => 'array', 'default' => [ 'seoSetup', 'seoOverview', 'seoNews' ] ],
			'announcements'    => [ 'type' => 'boolean', 'default' => true ],
			'postTypes'        => [
				'all'      => [ 'type' => 'boolean', 'default' => true ],
				'included' => [ 'type' => 'array', 'default' => [ 'post', 'page', 'product' ] ],
			],
			'taxonomies'       => [
				'all'      => [ 'type' => 'boolean', 'default' => true ],
				'included' => [ 'type' => 'array', 'default' => [ 'category', 'post_tag', 'product_cat', 'product_tag' ] ],
			],
			'uninstall'        => [ 'type' => 'boolean', 'default' => false ]
		],
		'sitemap'          => [
			'general' => [
				'enable'           => [ 'type' => 'boolean', 'default' => true ],
				'filename'         => [ 'type' => 'string', 'default' => 'sitemap' ],
				'indexes'          => [ 'type' => 'boolean', 'default' => true ],
				'linksPerIndex'    => [ 'type' => 'number', 'default' => 1000 ],
				// @TODO: [V4+] Convert this to the dynamic options like in search appearance so we can have backups when plugins are deactivated.
				'postTypes'        => [
					'all'      => [ 'type' => 'boolean', 'default' => true ],
					'included' => [ 'type' => 'array', 'default' => [ 'post', 'page', 'attachment', 'product' ] ],
				],
				// @TODO: [V4+] Convert this to the dynamic options like in search appearance so we can have backups when plugins are deactivated.
				'taxonomies'       => [
					'all'      => [ 'type' => 'boolean', 'default' => true ],
					'included' => [ 'type' => 'array', 'default' => [ 'category', 'post_tag', 'product_cat', 'product_tag' ] ],
				],
				'author'           => [ 'type' => 'boolean', 'default' => false ],
				'date'             => [ 'type' => 'boolean', 'default' => false ],
				'additionalPages'  => [
					'enable' => [ 'type' => 'boolean', 'default' => false ],
					'pages'  => [ 'type' => 'array', 'default' => [] ]
				],
				'advancedSettings' => [
					'enable'        => [ 'type' => 'boolean', 'default' => false ],
					'excludeImages' => [ 'type' => 'boolean', 'default' => false ],
					'excludePosts'  => [ 'type' => 'array', 'default' => [] ],
					'excludeTerms'  => [ 'type' => 'array', 'default' => [] ],
					'priority'      => [
						'homePage'   => [
							'priority'  => [ 'type' => 'string', 'default' => '{"label":"default","value":"default"}' ],
							'frequency' => [ 'type' => 'string', 'default' => '{"label":"default","value":"default"}' ]
						],
						'postTypes'  => [
							'grouped'   => [ 'type' => 'boolean', 'default' => true ],
							'priority'  => [ 'type' => 'string', 'default' => '{"label":"default","value":"default"}' ],
							'frequency' => [ 'type' => 'string', 'default' => '{"label":"default","value":"default"}' ]
						],
						'taxonomies' => [
							'grouped'   => [ 'type' => 'boolean', 'default' => true ],
							'priority'  => [ 'type' => 'string', 'default' => '{"label":"default","value":"default"}' ],
							'frequency' => [ 'type' => 'string', 'default' => '{"label":"default","value":"default"}' ]
						],
						'archive'    => [
							'priority'  => [ 'type' => 'string', 'default' => '{"label":"default","value":"default"}' ],
							'frequency' => [ 'type' => 'string', 'default' => '{"label":"default","value":"default"}' ]
						],
						'author'     => [
							'priority'  => [ 'type' => 'string', 'default' => '{"label":"default","value":"default"}' ],
							'frequency' => [ 'type' => 'string', 'default' => '{"label":"default","value":"default"}' ]
						]
					]
				]
			],
			'rss'     => [
				'enable'        => [ 'type' => 'boolean', 'default' => true ],
				'linksPerIndex' => [ 'type' => 'number', 'default' => 50 ],
				// @TODO: [V4+] Convert this to the dynamic options like in search appearance so we can have backups when plugins are deactivated.
				'postTypes'     => [
					'all'      => [ 'type' => 'boolean', 'default' => true ],
					'included' => [ 'type' => 'array', 'default' => [ 'post', 'page', 'product' ] ],
				]
			],
			'html'    => [
				'enable'           => [ 'type' => 'boolean', 'default' => true ],
				'pageUrl'          => [ 'type' => 'string', 'default' => '' ],
				'postTypes'        => [
					'all'      => [ 'type' => 'boolean', 'default' => true ],
					'included' => [ 'type' => 'array', 'default' => [ 'post', 'page', 'product' ] ],
				],
				'taxonomies'       => [
					'all'      => [ 'type' => 'boolean', 'default' => true ],
					'included' => [ 'type' => 'array', 'default' => [ 'category', 'post_tag', 'product_cat', 'product_tag' ] ],
				],
				'sortOrder'        => [ 'type' => 'string', 'default' => 'publish_date' ],
				'sortDirection'    => [ 'type' => 'string', 'default' => 'asc' ],
				'publicationDate'  => [ 'type' => 'boolean', 'default' => true ],
				'compactArchives'  => [ 'type' => 'boolean', 'default' => false ],
				'advancedSettings' => [
					'enable'        => [ 'type' => 'boolean', 'default' => false ],
					'nofollowLinks' => [ 'type' => 'boolean', 'default' => false ],
					'excludePosts'  => [ 'type' => 'array', 'default' => [] ],
					'excludeTerms'  => [ 'type' => 'array', 'default' => [] ]
				]
			],
		],
		'social'           => [
			'profiles' => [
				'sameUsername'   => [
					'enable'   => [ 'type' => 'boolean', 'default' => false ],
					'username' => [ 'type' => 'string' ],
					'included' => [ 'type' => 'array', 'default' => [ 'facebookPageUrl', 'twitterUrl', 'tiktokUrl', 'pinterestUrl', 'instagramUrl', 'youtubeUrl', 'linkedinUrl' ] ]
				],
				'urls'           => [
					'facebookPageUrl' => [ 'type' => 'string' ],
					'twitterUrl'      => [ 'type' => 'string' ],
					'instagramUrl'    => [ 'type' => 'string' ],
					'tiktokUrl'       => [ 'type' => 'string' ],
					'pinterestUrl'    => [ 'type' => 'string' ],
					'youtubeUrl'      => [ 'type' => 'string' ],
					'linkedinUrl'     => [ 'type' => 'string' ],
					'tumblrUrl'       => [ 'type' => 'string' ],
					'yelpPageUrl'     => [ 'type' => 'string' ],
					'soundCloudUrl'   => [ 'type' => 'string' ],
					'wikipediaUrl'    => [ 'type' => 'string' ],
					'myspaceUrl'      => [ 'type' => 'string' ],
					'googlePlacesUrl' => [ 'type' => 'string' ],
					'wordPressUrl'    => [ 'type' => 'string' ],
				],
				'additionalUrls' => [ 'type' => 'string' ]
			],
			'facebook' => [
				'general'  => [
					'enable'                  => [ 'type' => 'boolean', 'default' => true ],
					'defaultImageSourcePosts' => [ 'type' => 'string', 'default' => 'default' ],
					'customFieldImagePosts'   => [ 'type' => 'string' ],
					'defaultImagePosts'       => [ 'type' => 'string', 'default' => '' ],
					'defaultImagePostsWidth'  => [ 'type' => 'number', 'default' => '' ],
					'defaultImagePostsHeight' => [ 'type' => 'number', 'default' => '' ],
					'showAuthor'              => [ 'type' => 'boolean', 'default' => true ],
					'siteName'                => [ 'type' => 'string', 'localized' => true, 'default' => '#site_title #separator_sa #tagline' ]
				],
				'homePage' => [
					'image'       => [ 'type' => 'string', 'default' => '' ],
					'title'       => [ 'type' => 'string', 'localized' => true, 'default' => '' ],
					'description' => [ 'type' => 'string', 'localized' => true, 'default' => '' ],
					'imageWidth'  => [ 'type' => 'number', 'default' => '' ],
					'imageHeight' => [ 'type' => 'number', 'default' => '' ],
					'objectType'  => [ 'type' => 'string', 'default' => 'website' ]
				],
				'advanced' => [
					'enable'              => [ 'type' => 'boolean', 'default' => false ],
					'adminId'             => [ 'type' => 'string', 'default' => '' ],
					'appId'               => [ 'type' => 'string', 'default' => '' ],
					'authorUrl'           => [ 'type' => 'string', 'default' => '' ],
					'generateArticleTags' => [ 'type' => 'boolean', 'default' => false ],
					'useKeywordsInTags'   => [ 'type' => 'boolean', 'default' => true ],
					'useCategoriesInTags' => [ 'type' => 'boolean', 'default' => true ],
					'usePostTagsInTags'   => [ 'type' => 'boolean', 'default' => true ]
				]
			],
			'twitter'  => [
				'general'  => [
					'enable'                  => [ 'type' => 'boolean', 'default' => true ],
					'useOgData'               => [ 'type' => 'boolean', 'default' => true ],
					'defaultCardType'         => [ 'type' => 'string', 'default' => 'summary_large_image' ],
					'defaultImageSourcePosts' => [ 'type' => 'string', 'default' => 'default' ],
					'customFieldImagePosts'   => [ 'type' => 'string' ],
					'defaultImagePosts'       => [ 'type' => 'string', 'default' => '' ],
					'showAuthor'              => [ 'type' => 'boolean', 'default' => true ],
					'additionalData'          => [ 'type' => 'boolean', 'default' => false ]
				],
				'homePage' => [
					'image'       => [ 'type' => 'string', 'default' => '' ],
					'title'       => [ 'type' => 'string', 'localized' => true, 'default' => '' ],
					'description' => [ 'type' => 'string', 'localized' => true, 'default' => '' ],
					'cardType'    => [ 'type' => 'string', 'default' => 'summary' ]
				],
			]
		],
		'searchAppearance' => [
			'global'   => [
				'separator'       => [ 'type' => 'string', 'default' => '&#45;' ],
				'siteTitle'       => [ 'type' => 'string', 'localized' => true, 'default' => '#site_title #separator_sa #tagline' ],
				'metaDescription' => [ 'type' => 'string', 'localized' => true, 'default' => '#tagline' ],
				'keywords'        => [ 'type' => 'string', 'localized' => true ],
				'schema'          => [
					'websiteName'             => [ 'type' => 'string', 'default' => '#site_title' ],
					'websiteAlternateName'    => [ 'type' => 'string' ],
					'siteRepresents'          => [ 'type' => 'string', 'default' => 'organization' ],
					'person'                  => [ 'type' => 'string' ],
					'organizationName'        => [ 'type' => 'string', 'default' => '#site_title' ],
					'organizationDescription' => [ 'type' => 'string', 'default' => '#tagline' ],
					'organizationLogo'        => [ 'type' => 'string' ],
					'personName'              => [ 'type' => 'string' ],
					'personLogo'              => [ 'type' => 'string' ],
					'phone'                   => [ 'type' => 'string' ],
					'email'                   => [ 'type' => 'string' ],
					'foundingDate'            => [ 'type' => 'string' ],
					'numberOfEmployees'       => [
						'isRange' => [ 'type' => 'boolean' ],
						'from'    => [ 'type' => 'number' ],
						'to'      => [ 'type' => 'number' ],
						'number'  => [ 'type' => 'number' ]
					]
				]
			],
			'advanced' => [
				'globalRobotsMeta'             => [
					'default'           => [ 'type' => 'boolean', 'default' => true ],
					'noindex'           => [ 'type' => 'boolean', 'default' => false ],
					'nofollow'          => [ 'type' => 'boolean', 'default' => false ],
					'noindexPaginated'  => [ 'type' => 'boolean', 'default' => true ],
					'nofollowPaginated' => [ 'type' => 'boolean', 'default' => true ],
					'noindexFeed'       => [ 'type' => 'boolean', 'default' => true ],
					'noarchive'         => [ 'type' => 'boolean', 'default' => false ],
					'noimageindex'      => [ 'type' => 'boolean', 'default' => false ],
					'notranslate'       => [ 'type' => 'boolean', 'default' => false ],
					'nosnippet'         => [ 'type' => 'boolean', 'default' => false ],
					'noodp'             => [ 'type' => 'boolean', 'default' => false ],
					'maxSnippet'        => [ 'type' => 'number', 'default' => -1 ],
					'maxVideoPreview'   => [ 'type' => 'number', 'default' => -1 ],
					'maxImagePreview'   => [ 'type' => 'string', 'default' => 'large' ]
				],
				'sitelinks'                    => [ 'type' => 'boolean', 'default' => true ],
				'noIndexEmptyCat'              => [ 'type' => 'boolean', 'default' => true ],
				'removeStopWords'              => [ 'type' => 'boolean', 'default' => false ],
				'useKeywords'                  => [ 'type' => 'boolean', 'default' => false ],
				'keywordsLooking'              => [ 'type' => 'boolean', 'default' => true ],
				'useCategoriesForMetaKeywords' => [ 'type' => 'boolean', 'default' => false ],
				'useTagsForMetaKeywords'       => [ 'type' => 'boolean', 'default' => false ],
				'dynamicallyGenerateKeywords'  => [ 'type' => 'boolean', 'default' => false ],
				'pagedFormat'                  => [ 'type' => 'string', 'default' => '#separator_sa Page #page_number', 'localized' => true ],
				'runShortcodes'                => [ 'type' => 'boolean', 'default' => false ],
				'crawlCleanup'                 => [
					'enable' => [ 'type' => 'boolean', 'default' => false ],
					'feeds'  => [
						'global'         => [ 'type' => 'boolean', 'default' => true ],
						'globalComments' => [ 'type' => 'boolean', 'default' => false ],
						'staticBlogPage' => [ 'type' => 'boolean', 'default' => true ],
						'authors'        => [ 'type' => 'boolean', 'default' => true ],
						'postComments'   => [ 'type' => 'boolean', 'default' => false ],
						'search'         => [ 'type' => 'boolean', 'default' => false ],
						'attachments'    => [ 'type' => 'boolean', 'default' => false ],
						'archives'       => [
							'all'      => [ 'type' => 'boolean', 'default' => false ],
							'included' => [ 'type' => 'array', 'default' => [] ],
						],
						'taxonomies'     => [
							'all'      => [ 'type' => 'boolean', 'default' => false ],
							'included' => [ 'type' => 'array', 'default' => [ 'category' ] ],
						],
						'atom'           => [ 'type' => 'boolean', 'default' => false ],
						'rdf'            => [ 'type' => 'boolean', 'default' => false ],
						'paginated'      => [ 'type' => 'boolean', 'default' => false ]
					]
				],
				'blockArgs'                    => [
					'enable'        => [ 'type' => 'boolean', 'default' => false ],
					'logsRetention' => [ 'type' => 'string', 'default' => '{"label":"1 week","value":"week"}' ]
				]
			],
			'archives' => [
				'author' => [
					'show'            => [ 'type' => 'boolean', 'default' => true ],
					'title'           => [ 'type' => 'string', 'localized' => true, 'default' => '#author_name #separator_sa #site_title' ],
					'metaDescription' => [ 'type' => 'string', 'localized' => true, 'default' => '#author_bio' ],
					'advanced'        => [
						'robotsMeta'                => [
							'default'         => [ 'type' => 'boolean', 'default' => true ],
							'noindex'         => [ 'type' => 'boolean', 'default' => false ],
							'nofollow'        => [ 'type' => 'boolean', 'default' => false ],
							'noarchive'       => [ 'type' => 'boolean', 'default' => false ],
							'noimageindex'    => [ 'type' => 'boolean', 'default' => false ],
							'notranslate'     => [ 'type' => 'boolean', 'default' => false ],
							'nosnippet'       => [ 'type' => 'boolean', 'default' => false ],
							'noodp'           => [ 'type' => 'boolean', 'default' => false ],
							'maxSnippet'      => [ 'type' => 'number', 'default' => -1 ],
							'maxVideoPreview' => [ 'type' => 'number', 'default' => -1 ],
							'maxImagePreview' => [ 'type' => 'string', 'default' => 'large' ]
						],
						'showDateInGooglePreview'   => [ 'type' => 'boolean', 'default' => true ],
						'showPostThumbnailInSearch' => [ 'type' => 'boolean', 'default' => true ],
						'showMetaBox'               => [ 'type' => 'boolean', 'default' => true ],
						'keywords'                  => [ 'type' => 'string', 'localized' => true ]
					]
				],
				'date'   => [
					'show'            => [ 'type' => 'boolean', 'default' => true ],
					'title'           => [ 'type' => 'string', 'localized' => true, 'default' => '#archive_date #separator_sa #site_title' ],
					'metaDescription' => [ 'type' => 'string', 'localized' => true, 'default' => '' ],
					'advanced'        => [
						'robotsMeta'                => [
							'default'         => [ 'type' => 'boolean', 'default' => true ],
							'noindex'         => [ 'type' => 'boolean', 'default' => false ],
							'nofollow'        => [ 'type' => 'boolean', 'default' => false ],
							'noarchive'       => [ 'type' => 'boolean', 'default' => false ],
							'noimageindex'    => [ 'type' => 'boolean', 'default' => false ],
							'notranslate'     => [ 'type' => 'boolean', 'default' => false ],
							'nosnippet'       => [ 'type' => 'boolean', 'default' => false ],
							'noodp'           => [ 'type' => 'boolean', 'default' => false ],
							'maxSnippet'      => [ 'type' => 'number', 'default' => -1 ],
							'maxVideoPreview' => [ 'type' => 'number', 'default' => -1 ],
							'maxImagePreview' => [ 'type' => 'string', 'default' => 'large' ]
						],
						'showDateInGooglePreview'   => [ 'type' => 'boolean', 'default' => true ],
						'showPostThumbnailInSearch' => [ 'type' => 'boolean', 'default' => true ],
						'showMetaBox'               => [ 'type' => 'boolean', 'default' => true ],
						'keywords'                  => [ 'type' => 'string', 'localized' => true ]
					]
				],
				'search' => [
					'show'            => [ 'type' => 'boolean', 'default' => false ],
					'title'           => [ 'type' => 'string', 'localized' => true, 'default' => '#search_term #separator_sa #site_title' ],
					'metaDescription' => [ 'type' => 'string', 'localized' => true, 'default' => '' ],
					'advanced'        => [
						'robotsMeta'                => [
							'default'         => [ 'type' => 'boolean', 'default' => false ],
							'noindex'         => [ 'type' => 'boolean', 'default' => true ],
							'nofollow'        => [ 'type' => 'boolean', 'default' => false ],
							'noarchive'       => [ 'type' => 'boolean', 'default' => false ],
							'noimageindex'    => [ 'type' => 'boolean', 'default' => false ],
							'notranslate'     => [ 'type' => 'boolean', 'default' => false ],
							'nosnippet'       => [ 'type' => 'boolean', 'default' => false ],
							'noodp'           => [ 'type' => 'boolean', 'default' => false ],
							'maxSnippet'      => [ 'type' => 'number', 'default' => -1 ],
							'maxVideoPreview' => [ 'type' => 'number', 'default' => -1 ],
							'maxImagePreview' => [ 'type' => 'string', 'default' => 'large' ]
						],
						'showDateInGooglePreview'   => [ 'type' => 'boolean', 'default' => true ],
						'showPostThumbnailInSearch' => [ 'type' => 'boolean', 'default' => true ],
						'showMetaBox'               => [ 'type' => 'boolean', 'default' => true ],
						'keywords'                  => [ 'type' => 'string', 'localized' => true ]
					]
				]
			]
		],
		'searchStatistics' => [
			'postTypes' => [
				'all'      => [ 'type' => 'boolean', 'default' => true ],
				'included' => [ 'type' => 'array', 'default' => [ 'post', 'page' ] ],
			]
		],
		'tools'            => [
			'robots'       => [
				'enable'         => [ 'type' => 'boolean', 'default' => false ],
				'rules'          => [ 'type' => 'array', 'default' => [] ],
				'robotsDetected' => [ 'type' => 'boolean', 'default' => true ],
			],
			'importExport' => [
				'backup' => [
					'lastTime' => [ 'type' => 'string' ],
					'data'     => [ 'type' => 'string' ],
				]
			]
		],
		'deprecated'       => [
			'breadcrumbs'      => [
				'enable' => [ 'type' => 'boolean', 'default' => true ]
			],
			'searchAppearance' => [
				'global'   => [
					'descriptionFormat' => [ 'type' => 'string' ],
					'schema'            => [
						'enableSchemaMarkup' => [ 'type' => 'boolean', 'default' => true ]
					]
				],
				'advanced' => [
					'autogenerateDescriptions'               => [ 'type' => 'boolean', 'default' => true ],
					'runShortcodesInDescription'             => [ 'type' => 'boolean', 'default' => true ], // TODO: Remove this in a future update.
					'useContentForAutogeneratedDescriptions' => [ 'type' => 'boolean', 'default' => false ],
					'excludePosts'                           => [ 'type' => 'array', 'default' => [] ],
					'excludeTerms'                           => [ 'type' => 'array', 'default' => [] ],
					'noPaginationForCanonical'               => [ 'type' => 'boolean', 'default' => true ]
				]
			],
			'sitemap'          => [
				'general' => [
					'advancedSettings' => [
						'dynamic' => [ 'type' => 'boolean', 'default' => true ]
					]
				]
			],
			'tools'            => [
				'blocker' => [
					'blockBots'    => [ 'type' => 'boolean' ],
					'blockReferer' => [ 'type' => 'boolean' ],
					'track'        => [ 'type' => 'boolean' ],
					'custom'       => [
						'enable'  => [ 'type' => 'boolean' ],
						'bots'    => [ 'type' => 'html', 'default' => '' ],
						'referer' => [ 'type' => 'html', 'default' => '' ]
					]
				]
			]
		]
		// phpcs:enable WordPress.Arrays.ArrayDeclarationSpacing.AssociativeArrayFound
	];

	/**
	 * The Construct method.
	 *
	 * @since 4.0.0
	 *
	 * @param string $optionsName An array of options.
	 */
	public function __construct( $optionsName = 'aioseo_options' ) {
		$this->optionsName = $optionsName;

		$this->init();

		add_action( 'shutdown', [ $this, 'save' ] );
	}

	/**
	 * Initializes the options.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function init() {
		$this->setInitialDefaults();
		$this->translateDefaults();

		$this->setDbOptions();

		add_action( 'wp_loaded', [ $this, 'maybeFlushRewriteRules' ] );
	}

	/**
	 * Sets the DB options to the class after merging in new defaults and dropping unknown values.
	 *
	 * @since 4.0.14
	 *
	 * @return void
	 */
	public function setDbOptions() {
		// Refactor options.
		$this->defaultsMerged = array_replace_recursive( $this->defaults, $this->defaultsMerged );

		$dbOptions = $this->getDbOptions( $this->optionsName );

		$options = array_replace_recursive(
			$this->defaultsMerged,
			$this->addValueToValuesArray( $this->defaultsMerged, $dbOptions )
		);

		aioseo()->core->optionsCache->setOptions( $this->optionsName, apply_filters( 'aioseo_get_options', $options ) );

		// Get the localized options.
		$dbOptionsLocalized = get_option( $this->optionsName . '_localized' );
		if ( empty( $dbOptionsLocalized ) ) {
			$dbOptionsLocalized = [];
		}
		$this->localized = $dbOptionsLocalized;
	}

	/**
	 * Sets the initial defaults that can't be defined in the property because of PHP 5.4.
	 *
	 * @since 4.1.4
	 *
	 * @return void
	 */
	protected function setInitialDefaults() {
		static $hasInitialized = false;
		if ( $hasInitialized ) {
			return;
		}

		$hasInitialized = true;

		$this->defaults['deprecated']['tools']['blocker']['custom']['bots']['default']         = implode( "\n", aioseo()->badBotBlocker->getBotList() );
		$this->defaults['deprecated']['tools']['blocker']['custom']['referer']['default']      = implode( "\n", aioseo()->badBotBlocker->getRefererList() );

		$this->defaults['searchAppearance']['global']['schema']['organizationLogo']['default'] = aioseo()->helpers->getSiteLogoUrl() ? aioseo()->helpers->getSiteLogoUrl() : '';
	}

	/**
	 * For our defaults array, some options need to be translated, so we do that here.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function translateDefaults() {
		static $hasInitialized = false;
		if ( $hasInitialized ) {
			return;
		}

		$hasInitialized = true;

		$default = sprintf( '{"label":"%1$s","value":"default"}', __( 'default', 'all-in-one-seo-pack' ) );
		$this->defaults['sitemap']['general']['advancedSettings']['priority']['homePage']['priority']['default']    = $default;
		$this->defaults['sitemap']['general']['advancedSettings']['priority']['homePage']['frequency']['default']   = $default;
		$this->defaults['sitemap']['general']['advancedSettings']['priority']['postTypes']['priority']['default']   = $default;
		$this->defaults['sitemap']['general']['advancedSettings']['priority']['postTypes']['frequency']['default']  = $default;
		$this->defaults['sitemap']['general']['advancedSettings']['priority']['taxonomies']['priority']['default']  = $default;
		$this->defaults['sitemap']['general']['advancedSettings']['priority']['taxonomies']['frequency']['default'] = $default;

		$this->defaults['breadcrumbs']['homepageLabel']['default']      = __( 'Home', 'all-in-one-seo-pack' );
		$this->defaults['breadcrumbs']['archiveFormat']['default']      = sprintf( '%1$s #breadcrumb_archive_post_type_name', __( 'Archives for', 'all-in-one-seo-pack' ) );
		$this->defaults['breadcrumbs']['searchResultFormat']['default'] = sprintf( '%1$s \'#breadcrumb_search_string\'', __( 'Search Results for', 'all-in-one-seo-pack' ) );
		$this->defaults['breadcrumbs']['errorFormat404']['default']     = __( '404 - Page Not Found', 'all-in-one-seo-pack' );
	}

	/**
	 * Sanitizes, then saves the options to the database.
	 *
	 * @since 4.0.0
	 *
	 * @param  array $options An array of options to sanitize, then save.
	 * @return void
	 */
	public function sanitizeAndSave( $options ) {
		// Decode urls for additional pages to fix localization issues.
		$options                         = $this->decodeAdditionalPagesUrl( $options );
		$sitemapOptions                  = ! empty( $options['sitemap'] ) ? $options['sitemap'] : null;
		$oldSitemapOptions               = aioseo()->options->sitemap->all();
		$generalSitemapOptions           = ! empty( $options['sitemap']['general'] ) ? $options['sitemap']['general'] : null;
		$oldGeneralSitemapOptions        = aioseo()->options->sitemap->general->all();
		$deprecatedGeneralSitemapOptions = ! empty( $options['deprecated']['sitemap']['general'] )
				? $options['deprecated']['sitemap']['general']
				: null;
		$oldDeprecatedGeneralSitemapOptions = aioseo()->options->deprecated->sitemap->general->all();
		$oldPhoneOption                     = aioseo()->options->searchAppearance->global->schema->phone;
		$phoneNumberOptions                 = isset( $options['searchAppearance']['global']['schema']['phone'] )
				? $options['searchAppearance']['global']['schema']['phone']
				: null;
		$oldHtmlSitemapUrl = aioseo()->options->sitemap->html->pageUrl;
		$logsRetention     = isset( $options['searchAppearance']['advanced']['blockArgs']['logsRetention'] ) ? $options['searchAppearance']['advanced']['blockArgs']['logsRetention'] : null;
		$oldLogsRetention  = aioseo()->options->searchAppearance->advanced->blockArgs->logsRetention;

		$options = $this->maybeRemoveUnfilteredHtmlFields( $options );

		$this->init();

		if ( ! is_array( $options ) ) {
			return;
		}

		// First, recursively replace the new options into the cached state.
		// It's important we use the helper method since we want to replace populated arrays with empty ones if needed (when a setting was cleared out).
		$cachedOptions = aioseo()->core->optionsCache->getOptions( $this->optionsName );
		$dbOptions     = aioseo()->helpers->arrayReplaceRecursive(
			$cachedOptions,
			$this->addValueToValuesArray( $cachedOptions, $options, [], true )
		);

		// Now, we must also intersect both arrays to delete any individual keys that were unset.
		// We must do this because, while arrayReplaceRecursive will update the values for keys or empty them out,
		// it will keys that aren't present in the replacement array unaffected in the target array.
		$dbOptions = aioseo()->helpers->arrayIntersectRecursive(
			$dbOptions,
			$this->addValueToValuesArray( $cachedOptions, $options, [], true ),
			'value'
		);

		if ( isset( $options['social']['profiles']['additionalUrls'] ) ) {
			$dbOptions['social']['profiles']['additionalUrls'] = preg_replace( '/\h/', "\n", $options['social']['profiles']['additionalUrls'] );
		}

		$newOptions = ! empty( $options['sitemap']['html'] ) ? $options['sitemap']['html'] : null;
		if ( ! empty( $newOptions ) && aioseo()->options->sitemap->html->enable ) {
			$newOptions = ! empty( $options['sitemap']['html'] ) ? $options['sitemap']['html'] : null;

			$pageUrl = wp_parse_url( $newOptions['pageUrl'] );
			$path    = ! empty( $pageUrl['path'] ) ? untrailingslashit( $pageUrl['path'] ) : '';
			if ( $path ) {
				$existingPage = get_page_by_path( $path, OBJECT );
				if ( is_object( $existingPage ) ) {
					// If the page exists, don't override the previous URL.
					$options['sitemap']['html']['pageUrl'] = $oldHtmlSitemapUrl;
				}
			}
		}

		// Update the cache state.
		aioseo()->core->optionsCache->setOptions( $this->optionsName, $dbOptions );

		// Update localized options.
		update_option( $this->optionsName . '_localized', $this->localized );

		// Finally, save the new values to the DB.
		$this->save( true );

		// If phone settings have changed, let's see if we need to dump the phone number notice.
		if (
			$phoneNumberOptions &&
			$phoneNumberOptions !== $oldPhoneOption
		) {
			$notification = Models\Notification::getNotificationByName( 'v3-migration-schema-number' );
			if ( $notification->exists() ) {
				Models\Notification::deleteNotificationByName( 'v3-migration-schema-number' );
			}
		}

		// If sitemap settings were changed, static files need to be regenerated.
		if (
			! empty( $deprecatedGeneralSitemapOptions ) &&
			! empty( $generalSitemapOptions )
		) {
			if (
				(
					aioseo()->helpers->arraysDifferent( $oldGeneralSitemapOptions, $generalSitemapOptions ) ||
					aioseo()->helpers->arraysDifferent( $oldDeprecatedGeneralSitemapOptions, $deprecatedGeneralSitemapOptions )
				) &&
				$generalSitemapOptions['advancedSettings']['enable'] &&
				! $deprecatedGeneralSitemapOptions['advancedSettings']['dynamic']
			) {
				aioseo()->sitemap->scheduleRegeneration();
			}
		}

		// Add or remove schedule for clearing crawl cleanup logs.
		if ( ! empty( $logsRetention ) && $oldLogsRetention !== $logsRetention ) {
			aioseo()->crawlCleanup->scheduleClearingLogs();
		}

		if ( ! empty( $sitemapOptions ) ) {
			aioseo()->searchStatistics->sitemap->maybeSync( $oldSitemapOptions, $sitemapOptions );
		}

		// This is required in order for the Pro options to be refreshed before they save data again.
		$this->refresh();
	}

	/**
	 * If the user does not have access to unfiltered HTML, we need to remove them from saving.
	 *
	 * @since 4.0.0
	 *
	 * @param  array $options An array of options.
	 * @return array          An array of options.
	 */
	private function maybeRemoveUnfilteredHtmlFields( $options ) {
		if ( current_user_can( 'unfiltered_html' ) ) {
			return $options;
		}

		if (
			! empty( $options['webmasterTools'] ) &&
			isset( $options['webmasterTools']['miscellaneousVerification'] )
		) {
			unset( $options['webmasterTools']['miscellaneousVerification'] );
		}

		if (
			! empty( $options['rssContent'] ) &&
			isset( $options['rssContent']['before'] )
		) {
			unset( $options['rssContent']['before'] );
		}

		if (
			! empty( $options['rssContent'] ) &&
			isset( $options['rssContent']['after'] )
		) {
			unset( $options['rssContent']['after'] );
		}

		return $options;
	}

	/**
	 * Indicate we need to flush rewrite rules on next load.
	 *
	 * @since 4.0.17
	 *
	 * @return void
	 */
	public function flushRewriteRules() {
		update_option( 'aioseo_flush_rewrite_rules_flag', true );
	}

	/**
	 * Flush rewrite rules if needed.
	 *
	 * @since 4.0.17
	 *
	 * @return void
	 */
	public function maybeFlushRewriteRules() {
		if ( get_option( 'aioseo_flush_rewrite_rules_flag' ) ) {
			flush_rewrite_rules();
			delete_option( 'aioseo_flush_rewrite_rules_flag' );
		}
	}

	/**
	 * Decode the URLs for additional pages to fix Unicode characters.
	 *
	 * @since 4.6.7
	 *
	 * @param array  $options The options array.
	 * @return array $options The options array with the decoded URLs.
	 */
	public function decodeAdditionalPagesUrl( $options ) {
		if ( ! empty( $options['sitemap']['general']['additionalPages']['pages'] ) ) {
			foreach ( $options['sitemap']['general']['additionalPages']['pages'] as &$page ) {
				$page        = json_decode( $page, true );
				$page['url'] = aioseo()->helpers->decodeUrl( $page['url'] );
				$page        = wp_json_encode( $page, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
			}
		}

		return $options;
	}
}