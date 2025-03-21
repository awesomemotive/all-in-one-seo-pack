<?php
namespace AIOSEO\Plugin\Common\Migration;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AIOSEO\Plugin\Common\Models;

// phpcs:disable WordPress.Arrays.ArrayDeclarationSpacing.AssociativeArrayFound

/**
 * Migrates the General Settings from V3.
 *
 * @since 4.0.0
 */
class GeneralSettings {
	/**
	 * The old V3 options.
	 *
	 * @since 4.0.0
	 *
	 * @var array
	 */
	protected $oldOptions = [];

	/**
	 * Class constructor.
	 *
	 * @since 4.0.0
	 */
	public function __construct() {
		$this->oldOptions = aioseo()->migration->oldOptions;

		$this->migrateSeparatorCharacter();
		$this->setDefaultArticleType();
		$this->migrateHomePageMeta();
		$this->migrateTitleFormats();
		$this->migrateDescriptionFormat();
		$this->migrateNoindexSettings();
		$this->migrateNofollowSettings();
		$this->migratePostSeoColumns();
		$this->migrateSocialUrls();
		$this->migrateSchemaMarkupSettings();
		$this->migrateHomePageKeywords();
		$this->migrateDeprecatedAdvancedOptions();
		$this->migrateRssContentSettings();
		$this->migrateRedirectToParent();
		$this->migrateDisabledPosts();
		$this->migrateNoPaginationForCanonicalUrls();

		$settings = [
			'aiosp_admin_bar'                  => [ 'type' => 'boolean', 'newOption' => [ 'advanced', 'adminBarMenu' ] ],
			'aiosp_google_verify'              => [ 'type' => 'string', 'newOption' => [ 'webmasterTools', 'google' ] ],
			'aiosp_bing_verify'                => [ 'type' => 'string', 'newOption' => [ 'webmasterTools', 'bing' ] ],
			'aiosp_pinterest_verify'           => [ 'type' => 'string', 'newOption' => [ 'webmasterTools', 'pinterest' ] ],
			'aiosp_yandex_verify'              => [ 'type' => 'string', 'newOption' => [ 'webmasterTools', 'yandex' ] ],
			'aiosp_baidu_verify'               => [ 'type' => 'string', 'newOption' => [ 'webmasterTools', 'baidu' ] ],
			'aiosp_schema_site_represents'     => [ 'type' => 'string', 'newOption' => [ 'searchAppearance', 'global', 'schema', 'siteRepresents' ] ],
			'aiosp_schema_organization_name'   => [ 'type' => 'string', 'newOption' => [ 'searchAppearance', 'global', 'schema', 'organizationName' ] ],
			'aiosp_schema_person_manual_name'  => [ 'type' => 'string', 'newOption' => [ 'searchAppearance', 'global', 'schema', 'personName' ] ],
			'aiosp_schema_organization_logo'   => [ 'type' => 'string', 'newOption' => [ 'searchAppearance', 'global', 'schema', 'organizationLogo' ] ],
			'aiosp_schema_person_manual_image' => [ 'type' => 'string', 'newOption' => [ 'searchAppearance', 'global', 'schema', 'personLogo' ] ],
			'aiosp_togglekeywords'             => [ 'type' => 'boolean', 'newOption' => [ 'searchAppearance', 'advanced', 'useKeywords' ] ],
			'aiosp_use_categories'             => [ 'type' => 'boolean', 'newOption' => [ 'searchAppearance', 'advanced', 'useCategoriesForMetaKeywords' ] ],
			'aiosp_use_tags_as_keywords'       => [ 'type' => 'boolean', 'newOption' => [ 'searchAppearance', 'advanced', 'useTagsForMetaKeywords' ] ],
			'aiosp_dynamic_postspage_keywords' => [ 'type' => 'boolean', 'newOption' => [ 'searchAppearance', 'advanced', 'dynamicallyGenerateKeywords' ] ],
			'aiosp_run_shortcodes'             => [ 'type' => 'boolean', 'newOption' => [ 'searchAppearance', 'advanced', 'runShortcodes' ] ]
		];

		aioseo()->migration->helpers->mapOldToNew( $settings, aioseo()->migration->oldOptions );
	}

	/**
	 * Migrates the separator character.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	private function migrateSeparatorCharacter() {
		aioseo()->options->searchAppearance->global->separator = '|';
	}

	/**
	 * Set the default posts schema type to Article.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	private function setDefaultArticleType() {
		if ( aioseo()->dynamicOptions->searchAppearance->postTypes->has( 'post' ) ) {
			aioseo()->dynamicOptions->searchAppearance->postTypes->post->articleType = 'Article';
		}
	}

	/**
	 * Migrates the homepage meta.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	private function migrateHomePageMeta() {
		$this->migrateHomePageTitle();
		$this->migrateHomePageDescription();

		// If the homepage is a static one, we should migrate the meta now.
		$showOnFront = get_option( 'show_on_front' );
		$pageOnFront = (int) get_option( 'page_on_front' );
		if ( 'page' !== $showOnFront || ! $pageOnFront ) {
			return;
		}

		$post       = 'page' === $showOnFront && $pageOnFront ? get_post( $pageOnFront ) : '';
		$aioseoPost = Models\Post::getPost( $post->ID );

		$postMeta = aioseo()->core->db
			->start( 'postmeta' . ' as pm' )
			->select( 'pm.meta_key, pm.meta_value' )
			->where( 'pm.post_id', $post->ID )
			->whereRaw( "`pm`.`meta_key` LIKE '_aioseop_%'" )
			->run()
			->result();

		$mappedMeta = [
			'_aioseop_nofollow'           => 'robots_nofollow',
			'_aioseop_sitemap_priority'   => 'priority',
			'_aioseop_sitemap_frequency'  => 'frequency',
			'_aioseop_keywords'           => 'keywords',
			'_aioseop_opengraph_settings' => '',
		];

		$meta = [
			'post_id' => $post->ID,
		];

		foreach ( $postMeta as $record ) {
			$name  = $record->meta_key;
			$value = $record->meta_value;

			if ( ! in_array( $name, array_keys( $mappedMeta ), true ) ) {
				continue;
			}

			switch ( $name ) {
				case '_aioseop_nofollow':
					$meta[ $mappedMeta[ $name ] ] = ! empty( $value );
					if ( ! empty( $value ) ) {
						$meta['robots_default'] = false;
					}
					break;
				case '_aioseop_keywords':
					$meta[ $mappedMeta[ $name ] ] = aioseo()->migration->helpers->oldKeywordsToNewKeywords( $value );
					break;
				case '_aioseop_opengraph_settings':
					$class = new Meta();
					$meta += $class->convertOpenGraphMeta( $value );

					// We'll deal with the OG title/description in the Social Meta migration class.
					if ( isset( $meta['og_title'] ) ) {
						unset( $meta['og_title'] );
					}
					if ( isset( $meta['og_description'] ) ) {
						unset( $meta['og_description'] );
					}
					break;
				default:
					$meta[ $mappedMeta[ $name ] ] = aioseo()->helpers->sanitizeOption( $value );
					break;
			}
		}

		$aioseoPost->set( $meta );
		$aioseoPost->save();
	}

	/**
	 * Migrates the homepage title.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	private function migrateHomePageTitle() {
		$showOnFront   = get_option( 'show_on_front' );
		$pageOnFront   = (int) get_option( 'page_on_front' );

		$homePageTitle = ! empty( $this->oldOptions['aiosp_home_title'] ) ? $this->oldOptions['aiosp_home_title'] : '';
		$format        = $this->oldOptions['aiosp_home_page_title_format'];

		if ( 'posts' === $showOnFront ) {
			$homePageTitle = $homePageTitle ? $homePageTitle : get_bloginfo( 'name' );
			$title         = empty( $format ) ? $homePageTitle : aioseo()->helpers->pregReplace( '#%page_title%#', $homePageTitle, $format );
			$title         = aioseo()->migration->helpers->macrosToSmartTags( $title );
			aioseo()->options->searchAppearance->global->siteTitle = aioseo()->helpers->sanitizeOption( $title );

			return;
		}

		// Set the setting globally regardless of what happens below.
		if ( ! empty( $homePageTitle ) ) {
			$title = aioseo()->migration->helpers->macrosToSmartTags( aioseo()->helpers->pregReplace( '#%page_title%#', $homePageTitle, $format ) );
			aioseo()->options->searchAppearance->global->siteTitle = aioseo()->helpers->sanitizeOption( $title );
		}

		$post       = 'page' === $showOnFront && $pageOnFront ? get_post( $pageOnFront ) : '';
		$metaTitle  = get_post_meta( $post->ID, '_aioseop_title', true );

		$homePageTitle = '';
		if ( empty( $this->oldOptions['aiosp_use_static_home_info'] ) ) {
			$homePageTitle = ! empty( $this->oldOptions['aiosp_home_title'] ) ? $this->oldOptions['aiosp_home_title'] : '#site_title';
			$homePageTitle = ! empty( $metaTitle ) ? $metaTitle : $homePageTitle;
			$homePageTitle = empty( $format ) ? $homePageTitle : aioseo()->helpers->pregReplace( '#%page_title%#', $homePageTitle, $format );
			$homePageTitle = aioseo()->migration->helpers->macrosToSmartTags( $homePageTitle );
		} else {
			if ( ! empty( $metaTitle ) ) {
				$homePageTitle = empty( $format ) ? $metaTitle : aioseo()->helpers->pregReplace( '#%page_title%#', $metaTitle, $format );
				$homePageTitle = aioseo()->migration->helpers->macrosToSmartTags( $homePageTitle );
			}
		}

		$aioseoPost = Models\Post::getPost( $post->ID );
		$aioseoPost->set( [
			'post_id' => $post->ID,
			'title'   => aioseo()->helpers->sanitizeOption( $homePageTitle )
		] );
		$aioseoPost->save();

		$this->maybeShowHomePageTitleNotice( $post );
	}

	/**
	 * Check if we should display a notice warning users that their homepage title may have changed.
	 *
	 * @since 4.0.0
	 *
	 * @param  \WP_Post $post The post object.
	 * @return void
	 */
	private function maybeShowHomePageTitleNotice( $post ) {
		$metaTitle     = get_post_meta( $post->ID, '_aioseop_title', true );
		$homePageTitle = ! empty( $this->oldOptions['aiosp_home_title'] ) ? $this->oldOptions['aiosp_home_title'] : '';

		if (
			empty( $this->oldOptions['aiosp_use_static_home_info'] ) &&
			$metaTitle &&
			( trim( $homePageTitle ) !== trim( $metaTitle ) )
		) {
			$this->showHomePageSettingsNotice();
		}
	}

	/**
	 * Migrates the homepage description.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	private function migrateHomePageDescription() {
		$showOnFront         = get_option( 'show_on_front' );
		$pageOnFront         = (int) get_option( 'page_on_front' );

		$homePageDescription = ! empty( $this->oldOptions['aiosp_home_description'] ) ? $this->oldOptions['aiosp_home_description'] : '';
		$format              = $this->oldOptions['aiosp_description_format'];

		if ( 'posts' === $showOnFront ) {
			// If the description had the page_title macro, we want to replace it with the actual page title itself.
			$homePageDescription = $homePageDescription ? $homePageDescription : get_bloginfo( 'description' );
			$homePageTitle       = ! empty( $this->oldOptions['aiosp_home_title'] ) ? $this->oldOptions['aiosp_home_title'] : get_bloginfo( 'name' );
			$format              = aioseo()->helpers->pregReplace( '#%page_title%#', $homePageTitle, $format );
			$description         = empty( $format ) ? $homePageDescription : aioseo()->helpers->pregReplace( '#%description%#', $homePageDescription, $format );
			$description         = aioseo()->migration->helpers->macrosToSmartTags( $description );
			aioseo()->options->searchAppearance->global->metaDescription = aioseo()->helpers->sanitizeOption( $description );

			return;
		}

		// Set the setting globally regardless of what happens below.
		if ( ! empty( $homePageDescription ) ) {
			$homePageTitle = ! empty( $this->oldOptions['aiosp_home_title'] ) ? $this->oldOptions['aiosp_home_title'] : get_bloginfo( 'name' );
			$format        = aioseo()->helpers->pregReplace( '#%page_title%#', $homePageTitle, $format );
			$description   = aioseo()->migration->helpers->macrosToSmartTags( aioseo()->helpers->pregReplace( '#%description%#', $homePageDescription, $format ) );
			aioseo()->options->searchAppearance->global->metaDescription = aioseo()->helpers->sanitizeOption( $description );
		}

		$post             = 'page' === $showOnFront && $pageOnFront ? get_post( $pageOnFront ) : '';
		$metaDescription  = get_post_meta( $post->ID, '_aioseop_description', true );

		$homePageDescription = '';
		if ( empty( $this->oldOptions['aiosp_use_static_home_info'] ) ) {
			$homePageDescription = ! empty( $this->oldOptions['aiosp_home_description'] ) ? $this->oldOptions['aiosp_home_description'] : '';
			$homePageDescription = ! empty( $metaDescription ) ? $metaDescription : $homePageDescription;
		} else {
			if ( ! empty( $metaDescription ) ) {
				$homePageDescription = empty( $format ) ? $metaDescription : aioseo()->helpers->pregReplace( '#%description%#', $metaDescription, $format );
				$homePageDescription = aioseo()->migration->helpers->macrosToSmartTags( $homePageDescription );
			}
		}

		$homePageDescription = empty( $format ) ? $homePageDescription : aioseo()->helpers->pregReplace( '#(%description%|%page_title%)#', $homePageDescription, $format );
		$homePageDescription = aioseo()->migration->helpers->macrosToSmartTags( $homePageDescription );

		$aioseoPost = Models\Post::getPost( $post->ID );
		$aioseoPost->set( [
			'post_id'     => $post->ID,
			'description' => aioseo()->helpers->sanitizeOption( $homePageDescription )
		] );
		$aioseoPost->save();

		$this->maybeShowHomePageDescriptionNotice( $post );
	}

		/**
	 * Check if we should display a notice warning users that their homepage title may have changed.
	 *
	 * @since 4.0.0
	 *
	 * @param  \WP_Post $post The post object.
	 * @return void
	 */
	private function maybeShowHomePageDescriptionNotice( $post ) {
		$metaDescription     = get_post_meta( $post->ID, '_aioseop_description', true );
		$homePageDescription = ! empty( $this->oldOptions['aiosp_home_description'] ) ? $this->oldOptions['aiosp_home_description'] : '';

		if (
			empty( $this->oldOptions['aiosp_use_static_home_info'] ) &&
			$metaDescription &&
			( trim( $homePageDescription ) !== trim( $metaDescription ) )
		) {
			$this->showHomePageSettingsNotice();
		}
	}

	/**
	 * Shows the homepage settings notice.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	private function showHomePageSettingsNotice() {
		$notification = Models\Notification::getNotificationByName( 'v3-migration-homepage-settings' );
		if ( $notification->notification_name ) {
			return;
		}

		Models\Notification::addNotification( [
			'slug'              => uniqid(),
			'notification_name' => 'v3-migration-homepage-settings',
			'title'             => __( 'Review Your Homepage Title & Description', 'all-in-one-seo-pack' ),
			'content'           => sprintf(
				// Translators: 1 - All in One SEO.
				__( 'Due to a bug in the previous version of %1$s, your homepage title and description may have changed. Please take a minute to review your homepage settings to verify that they are correct.', 'all-in-one-seo-pack' ), // phpcs:ignore Generic.Files.LineLength.MaxExceeded
				AIOSEO_PLUGIN_NAME
			),
			'type'              => 'warning',
			'level'             => [ 'all' ],
			'button1_label'     => __( 'Review Now', 'all-in-one-seo-pack' ),
			'button1_action'    => 'http://route#aioseo-search-appearance&aioseo-scroll=home-page-settings&aioseo-highlight=home-page-settings:global-settings',
			'start'             => gmdate( 'Y-m-d H:i:s' )
		] );
	}

	/**
	 * Migrates the title formats.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	private function migrateTitleFormats() {
		if ( ! empty( $this->oldOptions['aiosp_archive_title_format'] ) ) {
			$archives = array_keys( aioseo()->dynamicOptions->searchAppearance->archives->all() );
			$format   = aioseo()->helpers->sanitizeOption( aioseo()->migration->helpers->macrosToSmartTags( $this->oldOptions['aiosp_archive_title_format'] ) );
			foreach ( $archives as $archive ) {
				aioseo()->dynamicOptions->searchAppearance->archives->$archive->title = $format;
			}
		}

		$settings = [
			'aiosp_post_title_format'       => [ 'type' => 'string', 'newOption' => [ 'searchAppearance', 'postTypes', 'post', 'title' ], 'dynamic' => true ],
			'aiosp_page_title_format'       => [ 'type' => 'string', 'newOption' => [ 'searchAppearance', 'postTypes', 'page', 'title' ], 'dynamic' => true ],
			'aiosp_attachment_title_format' => [ 'type' => 'string', 'newOption' => [ 'searchAppearance', 'postTypes', 'attachment', 'title' ], 'dynamic' => true ],
			'aiosp_category_title_format'   => [ 'type' => 'string', 'newOption' => [ 'searchAppearance', 'taxonomies', 'category', 'title' ], 'dynamic' => true ],
			'aiosp_tag_title_format'        => [ 'type' => 'string', 'newOption' => [ 'searchAppearance', 'taxonomies', 'post_tag', 'title' ], 'dynamic' => true ],
			'aiosp_date_title_format'       => [ 'type' => 'string', 'newOption' => [ 'searchAppearance', 'archives', 'date', 'title' ] ],
			'aiosp_author_title_format'     => [ 'type' => 'string', 'newOption' => [ 'searchAppearance', 'archives', 'author', 'title' ] ],
			'aiosp_search_title_format'     => [ 'type' => 'string', 'newOption' => [ 'searchAppearance', 'archives', 'search', 'title' ] ],
			'aiosp_paged_format'            => [ 'type' => 'string', 'newOption' => [ 'searchAppearance', 'advanced', 'pagedFormat' ] ]
		];

		foreach ( $this->oldOptions as $name => $value ) {
			if (
				! in_array( $name, array_keys( $settings ), true ) &&
				preg_match( '#aiosp_(.*)_title_format#', (string) $name, $slug )
			) {
				if ( empty( $slug[1] ) ) {
					continue;
				}

				$objectSlug = aioseo()->helpers->pregReplace( '#_tax#', '', $slug[1] );
				if ( in_array( $objectSlug, aioseo()->helpers->getPublicPostTypes( true ), true ) ) {
					$settings[ $name ] = [ 'type' => 'string', 'newOption' => [ 'searchAppearance', 'postTypes', $objectSlug, 'title' ], 'dynamic' => true ];
					continue;
				}
				if ( in_array( $objectSlug, aioseo()->helpers->getPublicTaxonomies( true ), true ) ) {
					$settings[ $name ] = [ 'type' => 'string', 'newOption' => [ 'searchAppearance', 'taxonomies', $objectSlug, 'title' ], 'dynamic' => true ];
				}
			}
		}

		aioseo()->migration->helpers->mapOldToNew( $settings, $this->oldOptions, true );

		// Check if any of the title formats were empty and register a notification if so.
		$found = false;
		foreach ( $settings as $k => $v ) {
			if ( 'aiosp_home_page_title_format' === $k ) {
				continue;
			}

			if ( isset( $this->oldOptions[ $k ] ) && empty( $this->oldOptions[ $k ] ) ) {
				$found = true;
				break;
			}
		}

		if ( ! $found ) {
			Models\Notification::deleteNotificationByName( 'v3-migration-title-formats-blank' );

			return;
		}

		$notification = Models\Notification::getNotificationByName( 'v3-migration-title-formats-blank' );
		if ( $notification->notification_name ) {
			return;
		}

		$p1 = sprintf(
			// Translators: 1 - The plugin short name ("AIOSEO"), 2 - The plugin short name ("AIOSEO"), 3 - Opening link tag, 4 - Closing link tag.
			__( '%1$s migrated all your title formats, some of which were blank. If you were purposely using blank formats in the previous version of %2$s and want WordPress to handle your titles, you can safely dismiss this message. For more information, check out our documentation on %3$sblank title formats%4$s.', 'all-in-one-seo-pack' ), // phpcs:ignore Generic.Files.LineLength.MaxExceeded
			AIOSEO_PLUGIN_SHORT_NAME,
			AIOSEO_PLUGIN_SHORT_NAME,
			'<a href="' . aioseo()->helpers->utmUrl( AIOSEO_MARKETING_URL . '/docs/blank-title-formats-detected', 'notifications-center', 'v3-migration-title-formats-blank' ) . '">',
			'</a>'
		);

		Models\Notification::addNotification( [
			'slug'              => uniqid(),
			'notification_name' => 'v3-migration-title-formats-blank',
			'title'             => __( 'Blank Title Formats Detected', 'all-in-one-seo-pack' ),
			'content'           => $p1,
			'type'              => 'warning',
			'level'             => [ 'all' ],
			'button1_label'     => __( 'Learn More', 'all-in-one-seo-pack' ),
			'button1_action'    => aioseo()->helpers->utmUrl( AIOSEO_MARKETING_URL . '/docs/blank-title-formats-detected', 'notifications-center', 'v3-migration-title-formats-blank' ),
			'start'             => gmdate( 'Y-m-d H:i:s' )
		] );
	}

	/**
	 * Migrates the description format.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	private function migrateDescriptionFormat() {
		if (
			! empty( $this->oldOptions['aiosp_generate_descriptions'] ) &&
			empty( $this->oldOptions['aiosp_skip_excerpt'] )
		) {
			foreach ( aioseo()->helpers->getPublicPostTypes() as $postType ) {
				if ( empty( $postType['supports']['excerpt'] ) ) {
					continue;
				}

				if ( aioseo()->dynamicOptions->searchAppearance->postTypes->has( $postType['name'] ) ) {
					aioseo()->dynamicOptions->searchAppearance->postTypes->{$postType['name']}->metaDescription = '#post_excerpt';
				}
			}
		}

		if (
			empty( $this->oldOptions['aiosp_description_format'] ) ||
			'%description%' === trim( $this->oldOptions['aiosp_description_format'] )
		) {
			return;
		}

		$deprecatedOptions = aioseo()->internalOptions->internal->deprecatedOptions;
		array_push( $deprecatedOptions, 'descriptionFormat' );
		aioseo()->internalOptions->internal->deprecatedOptions = $deprecatedOptions;

		$format = aioseo()->migration->helpers->macrosToSmartTags( $this->oldOptions['aiosp_description_format'] );
		aioseo()->options->deprecated->searchAppearance->global->descriptionFormat = aioseo()->helpers->sanitizeOption( $format );
	}

	/**
	 * Migrates the noindex settings.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	private function migrateNoindexSettings() {
		if ( ! isset( $this->oldOptions['aiosp_cpostnoindex'] ) && ! isset( $this->oldOptions['aiosp_tax_noindex'] ) ) {
			return;
		}

		$noindexedPostTypes = is_array( $this->oldOptions['aiosp_cpostnoindex'] ) ? $this->oldOptions['aiosp_cpostnoindex'] : explode( ', ', $this->oldOptions['aiosp_cpostnoindex'] );
		foreach ( array_intersect( aioseo()->helpers->getPublicPostTypes( true ), $noindexedPostTypes ) as $postType ) {
			if ( aioseo()->dynamicOptions->noConflict()->searchAppearance->postTypes->has( $postType ) ) {
				aioseo()->dynamicOptions->searchAppearance->postTypes->$postType->show = false;
				aioseo()->dynamicOptions->searchAppearance->postTypes->$postType->advanced->robotsMeta->default = false;
				aioseo()->dynamicOptions->searchAppearance->postTypes->$postType->advanced->robotsMeta->noindex = true;
			}
		}

		$noindexedTaxonomies = isset( $this->oldOptions['aiosp_tax_noindex'] ) ? (array) $this->oldOptions['aiosp_tax_noindex'] : [];
		if ( ! empty( $this->oldOptions['aiosp_category_noindex'] ) ) {
			$noindexedTaxonomies[] = 'category';
		}

		if ( ! empty( $this->oldOptions['aiosp_tags_noindex'] ) ) {
			$noindexedTaxonomies[] = 'post_tag';
		}

		if ( ! empty( $noindexedTaxonomies ) ) {
			foreach ( array_intersect( aioseo()->helpers->getPublicTaxonomies( true ), $noindexedTaxonomies ) as $taxonomy ) {
				if ( aioseo()->dynamicOptions->noConflict()->searchAppearance->taxonomies->has( $taxonomy ) ) {
					aioseo()->dynamicOptions->searchAppearance->taxonomies->$taxonomy->show = false;
					aioseo()->dynamicOptions->searchAppearance->taxonomies->$taxonomy->advanced->robotsMeta->default = false;
					aioseo()->dynamicOptions->searchAppearance->taxonomies->$taxonomy->advanced->robotsMeta->noindex = true;
				}
			}
		}

		if ( ! empty( $this->oldOptions['aiosp_archive_date_noindex'] ) ) {
			aioseo()->options->searchAppearance->archives->date->show = false;
			aioseo()->options->searchAppearance->archives->date->advanced->robotsMeta->default = false;
			aioseo()->options->searchAppearance->archives->date->advanced->robotsMeta->noindex = true;
		}

		if ( ! empty( $this->oldOptions['aiosp_archive_author_noindex'] ) ) {
			aioseo()->options->searchAppearance->archives->author->show = false;
			aioseo()->options->searchAppearance->archives->author->advanced->robotsMeta->default = false;
			aioseo()->options->searchAppearance->archives->author->advanced->robotsMeta->noindex = true;
		}

		if ( ! empty( $this->oldOptions['aiosp_search_noindex'] ) ) {
			aioseo()->options->searchAppearance->archives->search->show = false;
			aioseo()->options->searchAppearance->archives->search->advanced->robotsMeta->default = false;
			aioseo()->options->searchAppearance->archives->search->advanced->robotsMeta->noindex = true;
		} else {
			// We need to do this as V4 will noindex the search page otherwise.
			aioseo()->options->searchAppearance->archives->search->show = true;
			aioseo()->options->searchAppearance->archives->search->advanced->robotsMeta->default = true;
			aioseo()->options->searchAppearance->archives->search->advanced->robotsMeta->noindex = false;
		}

		if ( ! empty( $this->oldOptions['aiosp_paginated_noindex'] ) ) {
			aioseo()->options->searchAppearance->advanced->globalRobotsMeta->default          = false;
			aioseo()->options->searchAppearance->advanced->globalRobotsMeta->noindexPaginated = true;
		}
	}

	/**
	 * Migrates the nofollow settings.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	private function migrateNofollowSettings() {
		if ( ! empty( $this->oldOptions['aiosp_cpostnofollow'] ) ) {
			foreach ( array_intersect( aioseo()->helpers->getPublicPostTypes( true ), $this->oldOptions['aiosp_cpostnofollow'] ) as $postType ) {
				if ( aioseo()->dynamicOptions->noConflict()->searchAppearance->postTypes->has( $postType ) ) {
					aioseo()->dynamicOptions->searchAppearance->postTypes->$postType->advanced->robotsMeta->default  = false;
					aioseo()->dynamicOptions->searchAppearance->postTypes->$postType->advanced->robotsMeta->nofollow = true;
				}
			}
		}

		if ( ! empty( $this->oldOptions['aiosp_paginated_nofollow'] ) ) {
			aioseo()->options->searchAppearance->advanced->globalRobotsMeta->nofollowPaginated = true;
		}
	}

	/**
	 * Migrates the post SEO columns.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	private function migratePostSeoColumns() {
		if ( ! isset( $this->oldOptions['aiosp_posttypecolumns'] ) ) {
			return;
		}

		$publicPostTypes = aioseo()->helpers->getPublicPostTypes( true );
		$postTypes       = array_intersect( (array) $this->oldOptions['aiosp_posttypecolumns'], $publicPostTypes );

		aioseo()->options->advanced->postTypes->included = array_values( $postTypes );
		if ( count( $publicPostTypes ) !== count( $postTypes ) ) {
			aioseo()->options->advanced->postTypes->all = false;
		}
	}

	/**
	 * Migrates the schema social URLs.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	private function migrateSocialUrls() {
		if ( ! empty( $this->oldOptions['modules']['aiosp_opengraph_options']['aiosp_opengraph_facebook_publisher'] ) ) {
			aioseo()->options->social->profiles->urls->facebookPageUrl = esc_url( wp_strip_all_tags( $this->oldOptions['modules']['aiosp_opengraph_options']['aiosp_opengraph_facebook_publisher'] ) );
			aioseo()->options->social->profiles->sameUsername->enable = false;
		}

		if ( empty( $this->oldOptions['aiosp_schema_social_profile_links'] ) ) {
			return;
		}

		$socialUrls = aioseo()->helpers->pregReplace( '/\s/', '\r\n', $this->oldOptions['aiosp_schema_social_profile_links'] );
		$socialUrls = array_filter( explode( '\r\n', $socialUrls ) );

		if ( ! count( $socialUrls ) ) {
			return;
		}

		$supportedNetworks = [
			'facebook.com'   => 'facebookPageUrl',
			'twitter.com'    => 'twitterUrl',
			'instagram.com'  => 'instagramUrl',
			'tiktok.com'     => 'tiktokUrl',
			'pinterest.com'  => 'pinterestUrl',
			'youtube.com'    => 'youtubeUrl',
			'linkedin.com'   => 'linkedinUrl',
			'tumblr.com'     => 'tumblrUrl',
			'yelp.com'       => 'yelpPageUrl',
			'soundcloud.com' => 'soundCloudUrl',
			'wikipedia.org'  => 'wikipediaUrl',
			'myspace.com'    => 'myspaceUrl',
			'wordpress.org'  => 'wordpressUrl',
		];

		$found = false;
		foreach ( $supportedNetworks as $url => $settingName ) {
			$url = aioseo()->helpers->escapeRegex( $url );
			foreach ( $socialUrls as $socialUrl ) {
				if ( preg_match( "/.*$url.*/", (string) $socialUrl ) ) {
					aioseo()->options->social->profiles->urls->$settingName = esc_url( wp_strip_all_tags( $socialUrl ) );
					$found = true;
				}
			}
		}

		if ( $found ) {
			aioseo()->options->social->profiles->sameUsername->enable = false;
		}
	}

	/**
	 * Migrates the Schema Markup settings in the General Settings menu.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	private function migrateSchemaMarkupSettings() {
		$this->migrateSchemaPhoneNumber();

		if (
			isset( $this->oldOptions['aiosp_schema_markup'] ) &&
			empty( $this->oldOptions['aiosp_schema_markup'] )
		) {
			$deprecatedOptions = aioseo()->internalOptions->internal->deprecatedOptions;
			array_push( $deprecatedOptions, 'enableSchemaMarkup' );
			aioseo()->internalOptions->internal->deprecatedOptions = $deprecatedOptions;
			aioseo()->options->deprecated->searchAppearance->global->schema->enableSchemaMarkup = false;
		}

		if ( ! empty( $this->oldOptions['aiosp_schema_person_user'] ) ) {
			if ( -1 === (int) $this->oldOptions['aiosp_schema_person_user'] ) {
				aioseo()->options->searchAppearance->global->schema->person = 'manual';
			} else {
				aioseo()->options->searchAppearance->global->schema->person = intval( $this->oldOptions['aiosp_schema_person_user'] );
			}
		}
	}

	/**
	 * Migrates the schema phone number.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	private function migrateSchemaPhoneNumber() {
		if ( empty( $this->oldOptions['aiosp_schema_phone_number'] ) ) {
			return;
		}

		$phoneNumber = aioseo()->helpers->sanitizeOption( $this->oldOptions['aiosp_schema_phone_number'] );
		if ( ! preg_match( '#\+\d+#', (string) $phoneNumber ) ) {
			$notification = Models\Notification::getNotificationByName( 'v3-migration-schema-number' );
			if ( $notification->notification_name ) {
				return;
			}

			Models\Notification::addNotification( [
				'slug'              => uniqid(),
				'notification_name' => 'v3-migration-schema-number',
				'title'             => __( 'Invalid Phone Number for Knowledge Graph', 'all-in-one-seo-pack' ),
				'content'           => sprintf(
					// Translators: 1 - The phone number.
					__( 'The phone number that you previously entered for your Knowledge Graph schema markup is invalid. As it needs to be internationally formatted, please enter it (%1$s) again with the country code, e.g. +1 (555) 555-1234.', 'all-in-one-seo-pack' ), // phpcs:ignore Generic.Files.LineLength.MaxExceeded
					"<strong>$phoneNumber</strong>"
				),
				'type'              => 'warning',
				'level'             => [ 'all' ],
				'button1_label'     => __( 'Fix Now', 'all-in-one-seo-pack' ),
				'button1_action'    => 'http://route#aioseo-search-appearance&aioseo-scroll=schema-graph-phone&aioseo-highlight=schema-graph-phone:global-settings',
				'button2_label'     => __( 'Remind Me Later', 'all-in-one-seo-pack' ),
				'button2_action'    => 'http://action#notification/v3-migration-schema-number-reminder',
				'start'             => gmdate( 'Y-m-d H:i:s' )
			] );

			return;
		}
		aioseo()->options->searchAppearance->global->schema->phone = $phoneNumber;
	}

	/**
	 * Migrates the homepage keywords.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	private function migrateHomePageKeywords() {
		if ( ! empty( $this->oldOptions['aiosp_home_keywords'] ) ) {
			aioseo()->options->searchAppearance->global->keywords = aioseo()->migration->helpers->oldKeywordsToNewKeywords( $this->oldOptions['aiosp_home_keywords'] );
		}
	}

	/**
	 * Migrates the deprecated V3 advanced General Settings options.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	private function migrateDeprecatedAdvancedOptions() {
		$deprecatedOptions = aioseo()->internalOptions->internal->deprecatedOptions;

		if ( empty( $this->oldOptions['aiosp_generate_descriptions'] ) ) {
			array_push( $deprecatedOptions, 'autogenerateDescriptions' );
			aioseo()->options->deprecated->searchAppearance->advanced->autogenerateDescriptions = false;
		} else {
			if ( ! empty( $this->oldOptions['aiosp_skip_excerpt'] ) ) {
				array_push( $deprecatedOptions, 'useContentForAutogeneratedDescriptions' );
				aioseo()->options->deprecated->searchAppearance->advanced->useContentForAutogeneratedDescriptions = true;
			}
		}

		aioseo()->internalOptions->internal->deprecatedOptions = $deprecatedOptions;
	}

	/**
	 * Migrates the RSS content settings.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	private function migrateRssContentSettings() {
		if ( isset( $this->oldOptions['aiosp_rss_content_before'] ) ) {
			aioseo()->options->rssContent->before = esc_html( aioseo()->migration->helpers->macrosToSmartTags( $this->oldOptions['aiosp_rss_content_before'] ) );
		}

		if ( isset( $this->oldOptions['aiosp_rss_content_after'] ) ) {
			aioseo()->options->rssContent->after = esc_html( aioseo()->migration->helpers->macrosToSmartTags( $this->oldOptions['aiosp_rss_content_after'] ) );
		}
	}

	/**
	 * Migrates the Redirect Attachment to Parent setting.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	private function migrateRedirectToParent() {
		if ( isset( $this->oldOptions['aiosp_redirect_attachement_parent'] ) ) {
			if ( ! empty( $this->oldOptions['aiosp_redirect_attachement_parent'] ) ) {
				aioseo()->dynamicOptions->searchAppearance->postTypes->attachment->redirectAttachmentUrls = 'attachment_parent';
			} else {
				aioseo()->dynamicOptions->searchAppearance->postTypes->attachment->redirectAttachmentUrls = 'disabled';
			}
		}
	}

	/**
	 * Migrates the excluded posts.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	private function migrateDisabledPosts() {
		if ( empty( $this->oldOptions['aiosp_ex_pages'] ) ) {
			return;
		}

		$deprecatedOptions = aioseo()->internalOptions->internal->deprecatedOptions;
		if ( ! in_array( 'excludePosts', $deprecatedOptions, true ) ) {
			array_push( $deprecatedOptions, 'excludePosts' );
			aioseo()->internalOptions->internal->deprecatedOptions = $deprecatedOptions;
		}

		$excludedPosts = aioseo()->options->deprecated->searchAppearance->advanced->excludePosts;
		$pages         = explode( ',', $this->oldOptions['aiosp_ex_pages'] );
		if ( count( $pages ) ) {
			foreach ( $pages as $page ) {
				$page = trim( $page );
				$id   = intval( $page );
				if ( ! $id ) {
					$post = get_page_by_path( $page, OBJECT, aioseo()->helpers->getPublicPostTypes( true ) );
					if ( $post && is_object( $post ) ) {
						$id = $post->ID;
					}
				}

				if ( $id ) {
					$post = get_post( $id );
					if ( ! is_object( $post ) ) {
						continue;
					}

					$excludedPost        = new \stdClass();
					$excludedPost->value = $id;
					$excludedPost->type  = $post->post_type;
					$excludedPost->label = $post->post_title;
					$excludedPost->link  = get_permalink( $id );

					array_push( $excludedPosts, wp_json_encode( $excludedPost ) );
				}
			}
		}
		aioseo()->options->deprecated->searchAppearance->advanced->excludePosts = $excludedPosts;
	}

	/**
	 * Migrates the deprecated "No Pagination for Canonical URLs" setting.
	 *
	 * @since 4.5.9
	 *
	 * @return void
	 */
	private function migrateNoPaginationForCanonicalUrls() {
		if ( empty( $this->oldOptions['aiosp_no_paged_canonical_links'] ) ) {
			return;
		}

		$deprecatedOptions = aioseo()->internalOptions->deprecatedOptions;
		if ( ! in_array( 'noPaginationForCanonical', $deprecatedOptions, true ) ) {
			$deprecatedOptions[]                         = 'noPaginationForCanonical';
			aioseo()->internalOptions->deprecatedOptions = $deprecatedOptions;
		}

		aioseo()->options->deprecated->searchAppearance->advanced->noPaginationForCanonical = true;
	}
}