<?php
namespace AIOSEO\Plugin\Common\Main;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AIOSEO\Plugin\Common\Models;

/**
 * Updater class.
 *
 * @since 4.0.0
 */
class Updates {

	/**
	 * Class constructor.
	 *
	 * @since 4.0.0
	 */
	public function __construct() {
		add_action( 'aioseo_v4_migrate_post_schema', [ $this, 'migratePostSchema' ] );
		add_action( 'aioseo_v4_migrate_post_schema_default', [ $this, 'migratePostSchemaDefault' ] );
		add_action( 'aioseo_v419_remove_revision_records', [ $this, 'removeRevisionRecords' ] );

		if (
			wp_doing_ajax() ||
			wp_doing_cron()
		) {
			return;
		}

		add_action( 'init', [ $this, 'init' ], 1001 );
		add_action( 'init', [ $this, 'runUpdates' ], 1002 );
		add_action( 'init', [ $this, 'updateLatestVersion' ], 3000 );
	}

	/**
	 * Sets the latest active version if it is not set yet.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function init() {
		if ( '0.0' !== aioseo()->internalOptions->internal->lastActiveVersion ) {
			return;
		}

		// It's possible the user may not have capabilities. Let's add them now.
		aioseo()->access->addCapabilities();

		$oldOptions = get_option( 'aioseop_options' );
		if ( ! empty( $oldOptions['last_active_version'] ) ) {
			aioseo()->internalOptions->internal->lastActiveVersion = $oldOptions['last_active_version'];
		}

		$this->addInitialCustomTablesForV4();
		add_action( 'wp_loaded', [ $this, 'setDefaultSocialImages' ], 1001 );
	}

	/**
	 * Runs our migrations.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function runUpdates() {
		$lastActiveVersion = aioseo()->internalOptions->internal->lastActiveVersion;
		// Don't run updates if the last active version is the same as the current version.
		if ( aioseo()->version === $lastActiveVersion ) {
			// Allow addons to run their updates.
			do_action( 'aioseo_run_updates', $lastActiveVersion );

			return;
		}

		// Try to acquire the lock.
		if ( ! aioseo()->core->db->acquireLock( 'aioseo_run_updates_lock', 0 ) ) {
			// If we couldn't acquire the lock, exit early without doing anything.
			// This means another process is already running updates.
			return;
		}

		// Flush the object cache on plugin update to clear any stale data from persistent cache backends (e.g. Redis, Memcached).
		wp_cache_flush();

		// The dynamic options have not yet fully loaded, so let's refresh here to force that to happen.
		aioseo()->dynamicOptions->refresh();

		// Sync database schema with dbDelta - this will create tables and add missing columns automatically
		$this->updateDbSchema();

		// Data migrations and operations that dbDelta cannot handle
		if ( ! aioseo()->pro && version_compare( $lastActiveVersion, '4.0.6', '=' ) && 'posts' !== get_option( 'show_on_front' ) ) {
			aioseo()->migration->helpers->redoMigration();
		}

		if ( version_compare( $lastActiveVersion, '4.0.13', '<' ) ) {
			$this->removeDuplicateRecords();
		}

		if ( version_compare( $lastActiveVersion, '4.0.17', '<' ) ) {
			$this->removeLocationColumn();
		}

		if ( version_compare( $lastActiveVersion, '4.1.2', '<' ) ) {
			$this->clearProductImages();
		}

		if ( version_compare( $lastActiveVersion, '4.1.3', '<' ) ) {
			$this->noindexWooCommercePages();
			$this->accessControlNewCapabilities();
		}

		if ( version_compare( $lastActiveVersion, '4.1.3.3', '<' ) ) {
			$this->accessControlNewCapabilities();
		}

		if ( version_compare( $lastActiveVersion, '4.1.4.3', '<' ) ) {
			$this->migrateDynamicSettings();
		}

		if ( version_compare( $lastActiveVersion, '4.1.5', '<' ) ) {
			aioseo()->actionScheduler->unschedule( 'aioseo_cleanup_action_scheduler' );
			// Schedule routine to remove our old transients from the options table.
			aioseo()->actionScheduler->scheduleSingle( aioseo()->core->cache->getOptionCacheCleanAction(), MINUTE_IN_SECONDS );

			// Refresh with new Redirects capability.
			$this->accessControlNewCapabilities();
			aioseo()->sitemap->regenerateStaticSitemap();
		}

		if ( version_compare( $lastActiveVersion, '4.1.6', '<' ) ) {
			aioseo()->actionScheduler->unschedule( 'aioseo_admin_notifications_update' );
			$this->migrateOgTwitterImageColumns();
			aioseo()->options->social->twitter->general->useOgData = false;
		}

		if ( version_compare( $lastActiveVersion, '4.1.8', '<' ) ) {
			$this->accessControlNewCapabilities();
		}

		if ( version_compare( $lastActiveVersion, '4.1.9', '<' ) ) {
			$this->fixTaxonomyTags();
			$this->scheduleRemoveRevisionsRecords();
		}

		if ( version_compare( $lastActiveVersion, '4.0.0', '>=' ) && version_compare( $lastActiveVersion, '4.2.0', '<' ) ) {
			$this->migrateDeprecatedRunShortcodesSetting();
		}

		if ( version_compare( $lastActiveVersion, '4.2.1', '<' ) ) {
			aioseo()->options->flushRewriteRules();
			Models\Notification::deleteNotificationByName( 'deprecated-filters' );
			Models\Notification::deleteNotificationByName( 'deprecated-filters-v2' );
		}

		if ( version_compare( $lastActiveVersion, '4.2.2', '<' ) ) {
			$this->removeTabsColumn();
			$this->migrateUserContactMethods();
			aioseo()->actionScheduler->unschedule( 'aioseo_static_sitemap_regeneration' );
		}

		if ( version_compare( $lastActiveVersion, '4.2.5', '<' ) ) {
			$this->schedulePostSchemaMigration();
		}

		if ( version_compare( $lastActiveVersion, '4.2.4.2', '>' ) && version_compare( $lastActiveVersion, '4.2.6', '<' ) ) {
			$this->schedulePostSchemaDefaultMigration();
		}

		if ( version_compare( $lastActiveVersion, '4.2.8', '<' ) ) {
			$this->migrateDashboardWidgetsOptions();
		}

		if ( version_compare( $lastActiveVersion, '4.3.9', '<' ) ) {
			$this->migratePriorityColumn();
		}

		if ( version_compare( $lastActiveVersion, '4.4.2', '<' ) ) {
			$this->updateRobotsTxtRules();
		}

		if ( version_compare( $lastActiveVersion, '4.5.1', '<' ) ) {
			$this->checkForGaAnalyticsV3();
		}

		if ( version_compare( $lastActiveVersion, '4.5.8', '<' ) ) {
			$this->addQueryArgMonitorNotification();
		}

		if ( version_compare( $lastActiveVersion, '4.5.9', '<' ) ) {
			$this->deprecateNoPaginationForCanonicalUrlsSetting();
		}

		if ( version_compare( $lastActiveVersion, '4.6.5', '<' ) ) {
			$this->deprecateBreadcrumbsEnabledSetting();
		}

		if ( version_compare( $lastActiveVersion, '4.7.4', '<' ) ) {
			aioseo()->access->addCapabilities();
		}

		if ( version_compare( $lastActiveVersion, '4.7.5', '<' ) ) {
			$this->cancelScheduledSitemapPings();
		}

		if ( version_compare( $lastActiveVersion, '4.7.7', '<' ) ) {
			$this->disableEmailReports();
		}

		if ( version_compare( $lastActiveVersion, '4.7.9', '<' ) ) {
			$this->fixSavedHeadlines();
			$this->rescheduleEmailReport();
		}

		if ( version_compare( $lastActiveVersion, '4.8.3', '<' ) ) {
			$this->resetImageScanDate();
			$this->migrateSeoAnalyzerResults();
			$this->migrateSeoAnalyzerCompetitors();
		}

		if ( version_compare( $lastActiveVersion, '4.8.3.1', '<' ) ) {
			aioseo()->core->cache->delete( 'analyze_site_code' );
			aioseo()->core->cache->delete( 'analyze_site_body' );
		}

		if ( version_compare( $lastActiveVersion, '4.8.4.1', '<' ) ) {
			aioseo()->ai->updateCredits( true );
		}

		if ( version_compare( $lastActiveVersion, '4.8.7', '<' ) ) {
			$this->addColumnIndexForCornerstoneContent();
		}

		if ( version_compare( $lastActiveVersion, '4.9.1', '<' ) ) {
			aioseo()->access->addCapabilities();
		}

		if ( version_compare( $lastActiveVersion, '4.9.4', '<' ) ) {
			$this->addSeoChecklistToDashboardWidgets();
		}

		if ( version_compare( $lastActiveVersion, '4.9.6', '<' ) ) {
			$this->migrateSensitiveOptions();
		}

		if ( version_compare( $lastActiveVersion, '4.9.7', '<' ) ) {
			$this->cleanupSearchStatisticsProfile();
		}

		do_action( 'aioseo_run_updates', $lastActiveVersion );

		// Always clear the cache if the last active version is different from our current.

		if ( version_compare( $lastActiveVersion, AIOSEO_VERSION, '<' ) ) {
			aioseo()->core->cache->clear();
		}
	}

	/**
	 * Retrieve the raw options from the database for migration.
	 *
	 * @since 4.1.4
	 *
	 * @return array An array of options.
	 */
	private function getRawOptions() {
		// Options from the DB.
		$commonOptions = json_decode( get_option( aioseo()->options->optionsName ), true );
		if ( empty( $commonOptions ) ) {
			$commonOptions = [];
		}

		return $commonOptions;
	}

	/**
	 * Updates the latest version after all migrations and updates have run.
	 *
	 * @since 4.0.3
	 *
	 * @return void
	 */
	public function updateLatestVersion() {
		if ( aioseo()->internalOptions->internal->lastActiveVersion === aioseo()->version ) {
			return;
		}

		aioseo()->internalOptions->internal->lastActiveVersion = aioseo()->version;

		// Bust the tableExists and columnExists cache.
		aioseo()->core->cache->delete( 'db_schema' );

		// Bust the DB cache so we can make sure that everything is fresh.
		aioseo()->core->db->bustCache();
	}

	/**
	 * Adds our custom tables for V4.
	 *
	 * Now uses dbDelta to update DB schema instead of manual table creation.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function addInitialCustomTablesForV4() {
		// Use dbDelta to create all tables based on schema definitions
		$this->updateDbSchema();
	}

	/**
	 * Sets the default social images.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function setDefaultSocialImages() {
		$siteLogo = aioseo()->helpers->getSiteLogoUrl();
		if ( $siteLogo && ! aioseo()->internalOptions->internal->migratedVersion ) {
			if ( ! aioseo()->options->social->facebook->general->defaultImagePosts ) {
				aioseo()->options->social->facebook->general->defaultImagePosts = $siteLogo;
			}
			if ( ! aioseo()->options->social->twitter->general->defaultImagePosts ) {
				aioseo()->options->social->twitter->general->defaultImagePosts = $siteLogo;
			}
		}
	}

	/**
	 * Deletes duplicate records in our custom tables.
	 *
	 * @since 4.0.13
	 *
	 * @return void
	 */
	public function removeDuplicateRecords() {
		$duplicates = aioseo()->core->db->start( 'aioseo_posts' )
			->select( 'post_id, min(id) as id' )
			->groupBy( 'post_id having count(post_id) > 1' )
			->orderByRaw( 'count(post_id) DESC' )
			->run()
			->result();

		if ( empty( $duplicates ) ) {
			return;
		}

		foreach ( $duplicates as $duplicate ) {
			$postId        = esc_sql( $duplicate->post_id );
			$firstRecordId = esc_sql( $duplicate->id );

			aioseo()->core->db->delete( 'aioseo_posts' )
				->where( 'id >', $firstRecordId )
				->where( 'post_id', $postId )
				->run();
		}
	}

	/**
	 * Removes the location column.
	 *
	 * @since 4.0.17
	 *
	 * @return void
	 */
	public function removeLocationColumn() {
		if ( aioseo()->core->db->columnExists( 'aioseo_posts', 'location' ) ) {
			$tableName = aioseo()->core->db->db->prefix . 'aioseo_posts';
			aioseo()->core->db->execute(
				"ALTER TABLE {$tableName}
				DROP location"
			);
		}
	}

	/**
	 * Clears the image data for WooCommerce Products so that we scan them again and include product gallery images.
	 *
	 * @since 4.1.2
	 *
	 * @return void
	 */
	public function clearProductImages() {
		if ( ! aioseo()->helpers->isWooCommerceActive() ) {
			return;
		}

		aioseo()->core->db->update( 'aioseo_posts as ap' )
			->join( 'posts as p', 'ap.post_id = p.ID' )
			->where( 'p.post_type', 'product' )
			->set(
				[
					'images'          => null,
					'image_scan_date' => null
				]
			)
			->run();
	}

	/**
	 * Noindexes the WooCommerce cart, checkout and account pages.
	 *
	 * @since 4.1.3
	 *
	 * @return void
	 */
	public function noindexWooCommercePages() {
		if ( ! aioseo()->helpers->isWooCommerceActive() ) {
			return;
		}

		$cartId     = (int) get_option( 'woocommerce_cart_page_id' );
		$checkoutId = (int) get_option( 'woocommerce_checkout_page_id' );
		$accountId  = (int) get_option( 'woocommerce_myaccount_page_id' );

		$cartPage     = Models\Post::getPost( $cartId );
		$checkoutPage = Models\Post::getPost( $checkoutId );
		$accountPage  = Models\Post::getPost( $accountId );

		$newMeta = [
			'robots_default' => false,
			'robots_noindex' => true
		];

		if ( $cartPage->exists() ) {
			$cartPage->set( $newMeta );
			$cartPage->save();
		}
		if ( $checkoutPage->exists() ) {
			$checkoutPage->set( $newMeta );
			$checkoutPage->save();
		}
		if ( $accountPage->exists() ) {
			$accountPage->set( $newMeta );
			$accountPage->save();
		}
	}

	/**
	 * Adds the new capabilities for all the roles.
	 *
	 * @since 4.1.3
	 *
	 * @return void
	 */
	protected function accessControlNewCapabilities() {
		aioseo()->access->addCapabilities();
	}

	/**
	 * Migrate dynamic settings to a separate options structure.
	 *
	 * @since 4.1.4
	 *
	 * @return void
	 */
	protected function migrateDynamicSettings() {
		$rawOptions = $this->getRawOptions();
		$options    = aioseo()->dynamicOptions->noConflict();

		// Sitemap post type priorities/frequencies.
		if (
			! empty( $rawOptions['sitemap']['dynamic']['priority']['postTypes'] )
		) {
			foreach ( $rawOptions['sitemap']['dynamic']['priority']['postTypes'] as $postTypeName => $data ) {
				if ( $options->sitemap->priority->postTypes->has( $postTypeName ) ) {
					$options->sitemap->priority->postTypes->$postTypeName->priority  = $data['priority'];
					$options->sitemap->priority->postTypes->$postTypeName->frequency = $data['frequency'];
				}
			}
		}

		// Sitemap taxonomy priorities/frequencies.
		if (
			! empty( $rawOptions['sitemap']['dynamic']['priority']['taxonomies'] )
		) {
			foreach ( $rawOptions['sitemap']['dynamic']['priority']['taxonomies'] as $taxonomyName => $data ) {
				if ( $options->sitemap->priority->taxonomies->has( $taxonomyName ) ) {
					$options->sitemap->priority->taxonomies->$taxonomyName->priority  = $data['priority'];
					$options->sitemap->priority->taxonomies->$taxonomyName->frequency = $data['frequency'];
				}
			}
		}

		// Facebook post type object types.
		if (
			! empty( $rawOptions['social']['facebook']['general']['dynamic']['postTypes'] )
		) {
			foreach ( $rawOptions['social']['facebook']['general']['dynamic']['postTypes'] as $postTypeName => $data ) {
				if ( $options->social->facebook->general->postTypes->has( $postTypeName ) ) {
					$options->social->facebook->general->postTypes->$postTypeName->objectType = $data['objectType'];
				}
			}
		}

		// Search appearance post type data.
		if (
			! empty( $rawOptions['searchAppearance']['dynamic']['postTypes'] )
		) {
			foreach ( $rawOptions['searchAppearance']['dynamic']['postTypes'] as $postTypeName => $data ) {
				if ( $options->searchAppearance->postTypes->has( $postTypeName ) ) {
					$options->searchAppearance->postTypes->$postTypeName->show            = $data['show'];
					$options->searchAppearance->postTypes->$postTypeName->title           = $data['title'];
					$options->searchAppearance->postTypes->$postTypeName->metaDescription = $data['metaDescription'];
					$options->searchAppearance->postTypes->$postTypeName->schemaType      = $data['schemaType'];
					$options->searchAppearance->postTypes->$postTypeName->webPageType     = $data['webPageType'];
					$options->searchAppearance->postTypes->$postTypeName->articleType     = $data['articleType'];
					$options->searchAppearance->postTypes->$postTypeName->customFields    = $data['customFields'];

					// Advanced settings.
					$advanced = ! empty( $data['advanced']['robotsMeta'] ) ? $data['advanced']['robotsMeta'] : null;
					if ( ! empty( $advanced ) ) {
						$options->searchAppearance->postTypes->$postTypeName->advanced->robotsMeta->default         = $data['advanced']['robotsMeta']['default'];
						$options->searchAppearance->postTypes->$postTypeName->advanced->robotsMeta->noindex         = $data['advanced']['robotsMeta']['noindex'];
						$options->searchAppearance->postTypes->$postTypeName->advanced->robotsMeta->nofollow        = $data['advanced']['robotsMeta']['nofollow'];
						$options->searchAppearance->postTypes->$postTypeName->advanced->robotsMeta->noarchive       = $data['advanced']['robotsMeta']['noarchive'];
						$options->searchAppearance->postTypes->$postTypeName->advanced->robotsMeta->noimageindex    = $data['advanced']['robotsMeta']['noimageindex'];
						$options->searchAppearance->postTypes->$postTypeName->advanced->robotsMeta->notranslate     = $data['advanced']['robotsMeta']['notranslate'];
						$options->searchAppearance->postTypes->$postTypeName->advanced->robotsMeta->nosnippet       = $data['advanced']['robotsMeta']['nosnippet'];
						$options->searchAppearance->postTypes->$postTypeName->advanced->robotsMeta->noodp           = $data['advanced']['robotsMeta']['noodp'];
						$options->searchAppearance->postTypes->$postTypeName->advanced->robotsMeta->maxSnippet      = $data['advanced']['robotsMeta']['maxSnippet'];
						$options->searchAppearance->postTypes->$postTypeName->advanced->robotsMeta->maxVideoPreview = $data['advanced']['robotsMeta']['maxVideoPreview'];
						$options->searchAppearance->postTypes->$postTypeName->advanced->robotsMeta->maxImagePreview = $data['advanced']['robotsMeta']['maxImagePreview'];
						$options->searchAppearance->postTypes->$postTypeName->advanced->showDateInGooglePreview     = $data['advanced']['showDateInGooglePreview'];
						$options->searchAppearance->postTypes->$postTypeName->advanced->showPostThumbnailInSearch   = $data['advanced']['showPostThumbnailInSearch'];
						$options->searchAppearance->postTypes->$postTypeName->advanced->showMetaBox                 = $data['advanced']['showMetaBox'];
						$options->searchAppearance->postTypes->$postTypeName->advanced->bulkEditing                 = $data['advanced']['bulkEditing'];
					}

					if ( 'attachment' === $postTypeName ) {
						$options->searchAppearance->postTypes->$postTypeName->redirectAttachmentUrls = $data['redirectAttachmentUrls'];
					}
				}
			}
		}

		// Search appearance taxonomy data.
		if (
			! empty( $rawOptions['searchAppearance']['dynamic']['taxonomies'] )
		) {
			foreach ( $rawOptions['searchAppearance']['dynamic']['taxonomies'] as $taxonomyName => $data ) {
				if ( $options->searchAppearance->taxonomies->has( $taxonomyName ) ) {
					$options->searchAppearance->taxonomies->$taxonomyName->show            = $data['show'];
					$options->searchAppearance->taxonomies->$taxonomyName->title           = $data['title'];
					$options->searchAppearance->taxonomies->$taxonomyName->metaDescription = $data['metaDescription'];

					// Advanced settings.
					$advanced = ! empty( $data['advanced']['robotsMeta'] ) ? $data['advanced']['robotsMeta'] : null;
					if ( ! empty( $advanced ) ) {
						$options->searchAppearance->taxonomies->$taxonomyName->advanced->robotsMeta->default         = $data['advanced']['robotsMeta']['default'];
						$options->searchAppearance->taxonomies->$taxonomyName->advanced->robotsMeta->noindex         = $data['advanced']['robotsMeta']['noindex'];
						$options->searchAppearance->taxonomies->$taxonomyName->advanced->robotsMeta->nofollow        = $data['advanced']['robotsMeta']['nofollow'];
						$options->searchAppearance->taxonomies->$taxonomyName->advanced->robotsMeta->noarchive       = $data['advanced']['robotsMeta']['noarchive'];
						$options->searchAppearance->taxonomies->$taxonomyName->advanced->robotsMeta->noimageindex    = $data['advanced']['robotsMeta']['noimageindex'];
						$options->searchAppearance->taxonomies->$taxonomyName->advanced->robotsMeta->notranslate     = $data['advanced']['robotsMeta']['notranslate'];
						$options->searchAppearance->taxonomies->$taxonomyName->advanced->robotsMeta->nosnippet       = $data['advanced']['robotsMeta']['nosnippet'];
						$options->searchAppearance->taxonomies->$taxonomyName->advanced->robotsMeta->noodp           = $data['advanced']['robotsMeta']['noodp'];
						$options->searchAppearance->taxonomies->$taxonomyName->advanced->robotsMeta->maxSnippet      = $data['advanced']['robotsMeta']['maxSnippet'];
						$options->searchAppearance->taxonomies->$taxonomyName->advanced->robotsMeta->maxVideoPreview = $data['advanced']['robotsMeta']['maxVideoPreview'];
						$options->searchAppearance->taxonomies->$taxonomyName->advanced->robotsMeta->maxImagePreview = $data['advanced']['robotsMeta']['maxImagePreview'];
						$options->searchAppearance->taxonomies->$taxonomyName->advanced->showDateInGooglePreview     = $data['advanced']['showDateInGooglePreview'];
						$options->searchAppearance->taxonomies->$taxonomyName->advanced->showPostThumbnailInSearch   = $data['advanced']['showPostThumbnailInSearch'];
						$options->searchAppearance->taxonomies->$taxonomyName->advanced->showMetaBox                 = $data['advanced']['showMetaBox'];
					}
				}
			}
		}
	}

	/**
	 * Add in image with/height columns and image URL for caching.
	 *
	 * @since 4.1.6
	 *
	 * @return void
	 */
	protected function migrateOgTwitterImageColumns() {
		if ( aioseo()->core->db->tableExists( 'aioseo_posts' ) ) {
			$tableName = aioseo()->core->db->db->prefix . 'aioseo_posts';

			// OG Columns.
			if ( ! aioseo()->core->db->columnExists( 'aioseo_posts', 'og_image_url' ) ) {
				aioseo()->core->db->execute(
					"ALTER TABLE {$tableName} ADD og_image_url text DEFAULT NULL AFTER og_image_type"
				);
			}

			if ( aioseo()->core->db->columnExists( 'aioseo_posts', 'og_custom_image_height' ) ) {
				aioseo()->core->db->execute(
					"ALTER TABLE {$tableName} CHANGE COLUMN og_custom_image_height og_image_height int(11) DEFAULT NULL AFTER og_image_url"
				);
			} elseif ( ! aioseo()->core->db->columnExists( 'aioseo_posts', 'og_image_height' ) ) {
				aioseo()->core->db->execute(
					"ALTER TABLE {$tableName} ADD og_image_height int(11) DEFAULT NULL AFTER og_image_url"
				);
			}

			if ( aioseo()->core->db->columnExists( 'aioseo_posts', 'og_custom_image_width' ) ) {
				aioseo()->core->db->execute(
					"ALTER TABLE {$tableName} CHANGE COLUMN og_custom_image_width og_image_width int(11) DEFAULT NULL AFTER og_image_url"
				);
			} elseif ( ! aioseo()->core->db->columnExists( 'aioseo_posts', 'og_image_width' ) ) {
				aioseo()->core->db->execute(
					"ALTER TABLE {$tableName} ADD og_image_width int(11) DEFAULT NULL AFTER og_image_url"
				);
			}

			// Twitter image url columnn.
			if ( ! aioseo()->core->db->columnExists( 'aioseo_posts', 'twitter_image_url' ) ) {
				aioseo()->core->db->execute(
					"ALTER TABLE {$tableName} ADD twitter_image_url text DEFAULT NULL AFTER twitter_image_type"
				);
			}

			// Reset the cache for the installed tables.
			aioseo()->core->cache->delete( 'db_schema' );
		}
	}

	/**
	 * Fixes tags that should not be in the search appearance taxonomy options.
	 *
	 * @since 4.1.9
	 *
	 * @return void
	 */
	protected function fixTaxonomyTags() {
		$searchAppearanceTaxonomies = aioseo()->dynamicOptions->searchAppearance->taxonomies->all();

		$replaces = [
			'#breadcrumb_separator' => '#separator_sa',
			'#breadcrumb_'          => '#',
			'#blog_title'           => '#site_title'
		];

		foreach ( $searchAppearanceTaxonomies as $taxonomy => $searchAppearanceTaxonomy ) {
			aioseo()->dynamicOptions->searchAppearance->taxonomies->{$taxonomy}->title = str_replace(
				array_keys( $replaces ),
				array_values( $replaces ),
				$searchAppearanceTaxonomy['title']
			);

			aioseo()->dynamicOptions->searchAppearance->taxonomies->{$taxonomy}->metaDescription = str_replace(
				array_keys( $replaces ),
				array_values( $replaces ),
				$searchAppearanceTaxonomy['metaDescription']
			);
		}
	}

	/**
	 * Removes any AIOSEO Post records for revisions.
	 *
	 * @since 4.1.9
	 *
	 * @return void
	 */
	public function removeRevisionRecords() {
		$postsTableName       = aioseo()->core->db->prefix . 'posts';
		$aioseoPostsTableName = aioseo()->core->db->prefix . 'aioseo_posts';
		$limit                = 5000;

		aioseo()->core->db->execute(
			"DELETE FROM `$aioseoPostsTableName`
			WHERE `post_id` IN (
				SELECT `ID`
				FROM `$postsTableName`
				WHERE `post_parent` != 0
				AND `post_type` = 'revision'
				AND `post_status` = 'inherit'
			)
			LIMIT {$limit}"
		);

		// If the limit equals the amount of post IDs found, there might be more revisions left, so we need a new scan.
		if ( aioseo()->core->db->rowsAffected() === $limit ) {
			$this->scheduleRemoveRevisionsRecords();
		}
	}

	/**
	 * Enables the new shortcodes parsing setting if it was already enabled before as a deprecated setting.
	 *
	 * @since 4.2.0
	 *
	 * @return void
	 */
	private function migrateDeprecatedRunShortcodesSetting() {
		if (
			in_array( 'runShortcodesInDescription', aioseo()->internalOptions->deprecatedOptions, true ) &&
			! aioseo()->options->deprecated->searchAppearance->advanced->runShortcodesInDescription
		) {
			return;
		}

		aioseo()->options->searchAppearance->advanced->runShortcodes = true;
	}

	/**
	 * Remove the tabs column as it is unnecessary.
	 * This method is kept because dbDelta cannot handle DROP COLUMN operations.
	 *
	 * @since 4.2.2
	 *
	 * @return void
	 */
	protected function removeTabsColumn() {
		if ( aioseo()->core->db->columnExists( 'aioseo_posts', 'tabs' ) ) {
			$tableName = aioseo()->core->db->db->prefix . 'aioseo_posts';
			aioseo()->core->db->execute(
				"ALTER TABLE {$tableName}
				DROP tabs"
			);
		}
	}

	/**
	 * Migrates the user contact methods to the new format.
	 *
	 * @since 4.2.2
	 *
	 * @return void
	 */
	private function migrateUserContactMethods() {
		$userMetaTableName = aioseo()->core->db->db->usermeta;

		aioseo()->core->db->execute(
			"UPDATE `$userMetaTableName`
			SET `meta_key` = 'aioseo_facebook_page_url'
			WHERE `meta_key` = 'aioseo_facebook'"
		);

		aioseo()->core->db->execute(
			"UPDATE `$userMetaTableName`
			SET `meta_key` = 'aioseo_twitter_url'
			WHERE `meta_key` = 'aioseo_twitter'"
		);
	}

	/**
	 * Schedules the post schema migration.
	 *
	 * @since 4.2.5
	 *
	 * @return void
	 */
	private function schedulePostSchemaMigration() {
		aioseo()->actionScheduler->scheduleSingle( 'aioseo_v4_migrate_post_schema', 10 );

		if ( ! aioseo()->core->cache->get( 'v4_migrate_post_schema_default_date' ) ) {
			aioseo()->core->cache->update( 'v4_migrate_post_schema_default_date', gmdate( 'Y-m-d H:i:s' ), 3 * MONTH_IN_SECONDS );
		}
	}

	/**
	 * Migrates then post schema to the new JSON column.
	 *
	 * @since 4.2.5
	 *
	 * @return void
	 */
	public function migratePostSchema() {
		$posts = aioseo()->core->db->start( 'aioseo_posts' )
			->select( '*' )
			->where( 'schema', null )
			->limit( 40 )
			->run()
			->models( 'AIOSEO\\Plugin\\Common\\Models\\Post' );

		if ( empty( $posts ) ) {
			return;
		}

		foreach ( $posts as $post ) {
			$this->migratePostSchemaHelper( $post );
		}

		// Once done, schedule the next action.
		aioseo()->actionScheduler->scheduleSingle( 'aioseo_v4_migrate_post_schema', 30, [], true );
	}

	/**
	 * Schedules the post schema migration to fix the default graphs.
	 *
	 * @since 4.2.6
	 *
	 * @return void
	 */
	private function schedulePostSchemaDefaultMigration() {
		aioseo()->actionScheduler->scheduleSingle( 'aioseo_v4_migrate_post_schema_default', 30 );
	}

	/**
	 * Migrates the post schema to the new JSON column again for posts using the default.
	 * This is needed to fix an oversight because in 4.2.5 we didn't migrate any properties set to the default graph.
	 *
	 * @since 4.2.6
	 *
	 * @return void
	 */
	public function migratePostSchemaDefault() {
		$migrationStartDate = esc_sql( aioseo()->core->cache->get( 'v4_migrate_post_schema_default_date' ) );
		if ( ! $migrationStartDate ) {
			return;
		}

		$posts = aioseo()->core->db->start( 'aioseo_posts' )
			->select( '*' )
			->where( 'schema_type =', 'default' )
			->where( 'updated <', $migrationStartDate )
			->limit( 40 )
			->run()
			->models( 'AIOSEO\\Plugin\\Common\\Models\\Post' );

		if ( empty( $posts ) ) {
			aioseo()->core->cache->delete( 'v4_migrate_post_schema_default_date' );

			return;
		}

		foreach ( $posts as $post ) {
			$this->migratePostSchemaHelper( $post );
		}

		// Once done, schedule the next action.
		aioseo()->actionScheduler->scheduleSingle( 'aioseo_v4_migrate_post_schema_default', 30, [], true );
	}

	/**
	 * Helper function for the schema migration.
	 *
	 * @since  4.2.5
	 *
	 * @param  Models\Post $aioseoPost The AIOSEO post object.
	 * @return Models\Post             The modified AIOSEO post object.
	 */
	public function migratePostSchemaHelper( $aioseoPost ) {
		$post              = aioseo()->helpers->getPost( $aioseoPost->post_id );
		$schemaType        = $aioseoPost->schema_type;
		$schemaTypeOptions = json_decode( (string) $aioseoPost->schema_type_options );
		$schemaOptions     = Models\Post::getDefaultSchemaOptions( '', $post );

		if ( empty( $schemaTypeOptions ) ) {
			$aioseoPost->schema = $schemaOptions;
			$aioseoPost->save();

			return $aioseoPost;
		}

		// If the post is set to the default schema type, set the default for post type but then also get the properties.
		$isDefault = 'default' === $schemaType;
		if ( $isDefault ) {
			$dynamicOptions = aioseo()->dynamicOptions->noConflict();
			if ( ! empty( $post->post_type ) && $dynamicOptions->searchAppearance->postTypes->has( $post->post_type ) ) {
				$schemaOptions->default->graphName = $dynamicOptions->searchAppearance->postTypes->{$post->post_type}->schemaType;
				$schemaType                        = $dynamicOptions->searchAppearance->postTypes->{$post->post_type}->schemaType;
			}
		}

		$graph = [];
		switch ( $schemaType ) {
			case 'Article':
				$graph = [
					'id'         => '#aioseo-article-' . uniqid(),
					'slug'       => 'article',
					'graphName'  => 'Article',
					'label'      => __( 'Article', 'all-in-one-seo-pack' ),
					'properties' => [
						'type'        => ! empty( $schemaTypeOptions->article->articleType ) ? $schemaTypeOptions->article->articleType : 'Article',
						'name'        => '#post_title',
						'headline'    => '#post_title',
						'description' => '#post_excerpt',
						'image'       => '',
						'keywords'    => '',
						'author'      => [
							'name' => '#author_name',
							'url'  => '#author_url'
						],
						'dates'       => [
							'include'       => true,
							'datePublished' => '',
							'dateModified'  => ''
						]
					]
				];
				break;
			case 'Course':
				$graph = [
					'id'         => '#aioseo-course-' . uniqid(),
					'slug'       => 'course',
					'graphName'  => 'Course',
					'label'      => __( 'Course', 'all-in-one-seo-pack' ),
					'properties' => [
						'name'        => ! empty( $schemaTypeOptions->course->name ) ? $schemaTypeOptions->course->name : '#post_title',
						'description' => ! empty( $schemaTypeOptions->course->description ) ? $schemaTypeOptions->course->description : '#post_excerpt',
						'provider'    => [
							'name'  => ! empty( $schemaTypeOptions->course->provider ) ? $schemaTypeOptions->course->provider : '',
							'url'   => '',
							'image' => ''
						]
					]
				];
				break;
			case 'Product':
				$graph = [
					'id'         => '#aioseo-product-' . uniqid(),
					'slug'       => 'product',
					'graphName'  => 'Product',
					'label'      => __( 'Product', 'all-in-one-seo-pack' ),
					'properties' => [
						'autogenerate' => true,
						'name'         => '#post_title',
						'description'  => ! empty( $schemaTypeOptions->product->description ) ? $schemaTypeOptions->product->description : '#post_excerpt',
						'brand'        => ! empty( $schemaTypeOptions->product->brand ) ? $schemaTypeOptions->product->brand : '',
						'image'        => '',
						'identifiers'  => [
							'sku'  => ! empty( $schemaTypeOptions->product->sku ) ? $schemaTypeOptions->product->sku : '',
							'gtin' => '',
							'mpn'  => ''
						],
						'offer'        => [
							'price'        => ! empty( $schemaTypeOptions->product->price ) ? (float) $schemaTypeOptions->product->price : '',
							'currency'     => ! empty( $schemaTypeOptions->product->currency ) ? $schemaTypeOptions->product->currency : '',
							'availability' => ! empty( $schemaTypeOptions->product->availability ) ? $schemaTypeOptions->product->availability : '',
							'validUntil'   => ! empty( $schemaTypeOptions->product->priceValidUntil ) ? $schemaTypeOptions->product->priceValidUntil : ''
						],
						'rating'       => [
							'minimum' => 1,
							'maximum' => 5
						],
						'reviews'      => []
					]
				];

				$identifierType = ! empty( $schemaTypeOptions->product->identifierType ) ? $schemaTypeOptions->product->identifierType : '';
				$identifier     = ! empty( $schemaTypeOptions->product->identifier ) ? $schemaTypeOptions->product->identifier : '';
				if ( preg_match( '/gtin/i', (string) $identifierType ) ) {
					$graph['properties']['identifiers']['gtin'] = $identifier;
				}

				if ( preg_match( '/mpn/i', (string) $identifierType ) ) {
					$graph['properties']['identifiers']['mpn'] = $identifier;
				}

				$reviews = ! empty( $schemaTypeOptions->product->reviews ) ? $schemaTypeOptions->product->reviews : [];
				if ( ! empty( $reviews ) ) {
					foreach ( $reviews as $reviewData ) {
						$reviewData = json_decode( $reviewData );
						if ( empty( $reviewData ) ) {
							continue;
						}

						$graph['properties']['reviews'][] = [
							'rating'   => $reviewData->rating,
							'headline' => $reviewData->headline,
							'content'  => $reviewData->content,
							'author'   => $reviewData->author
						];
					}
				}
				break;
			case 'Recipe':
				$graph = [
					'id'         => '#aioseo-recipe-' . uniqid(),
					'slug'       => 'recipe',
					'graphName'  => 'Recipe',
					'label'      => __( 'Recipe', 'all-in-one-seo-pack' ),
					'properties' => [
						'name'         => ! empty( $schemaTypeOptions->recipe->name ) ? $schemaTypeOptions->recipe->name : '#post_title',
						'description'  => ! empty( $schemaTypeOptions->recipe->description ) ? $schemaTypeOptions->recipe->description : '#post_excerpt',
						'author'       => ! empty( $schemaTypeOptions->recipe->author ) ? $schemaTypeOptions->recipe->author : '#author_name',
						'ingredients'  => ! empty( $schemaTypeOptions->recipe->ingredients ) ? $schemaTypeOptions->recipe->ingredients : '',
						'dishType'     => ! empty( $schemaTypeOptions->recipe->dishType ) ? $schemaTypeOptions->recipe->dishType : '',
						'cuisineType'  => ! empty( $schemaTypeOptions->recipe->cuisineType ) ? $schemaTypeOptions->recipe->cuisineType : '',
						'keywords'     => ! empty( $schemaTypeOptions->recipe->keywords ) ? $schemaTypeOptions->recipe->keywords : '',
						'image'        => ! empty( $schemaTypeOptions->recipe->image ) ? $schemaTypeOptions->recipe->image : '',
						'nutrition'    => [
							'servings' => ! empty( $schemaTypeOptions->recipe->servings ) ? $schemaTypeOptions->recipe->servings : '',
							'calories' => ! empty( $schemaTypeOptions->recipe->calories ) ? $schemaTypeOptions->recipe->calories : ''
						],
						'timeRequired' => [
							'preparation' => ! empty( $schemaTypeOptions->recipe->preparationTime ) ? $schemaTypeOptions->recipe->preparationTime : '',
							'cooking'     => ! empty( $schemaTypeOptions->recipe->cookingTime ) ? $schemaTypeOptions->recipe->cookingTime : ''
						],
						'instructions' => [],
						'rating'       => [
							'minimum' => 1,
							'maximum' => 5
						],
						'reviews'      => []
					]
				];

				$instructions = ! empty( $schemaTypeOptions->recipe->instructions ) ? $schemaTypeOptions->recipe->instructions : [];
				if ( ! empty( $instructions ) ) {
					foreach ( $instructions as $instructionData ) {
						$instructionData = json_decode( $instructionData );
						if ( empty( $instructionData ) ) {
							continue;
						}

						$graph['properties']['instructions'][] = [
							'name'  => '',
							'text'  => $instructionData->content,
							'image' => ''
						];
					}
				}

				$reviews = ! empty( $schemaTypeOptions->recipe->reviews ) ? $schemaTypeOptions->recipe->reviews : [];
				if ( ! empty( $reviews ) ) {
					foreach ( $reviews as $reviewData ) {
						$reviewData = json_decode( $reviewData );
						if ( empty( $reviewData ) ) {
							continue;
						}

						$graph['properties']['reviews'][] = [
							'rating'   => $reviewData->rating,
							'headline' => $reviewData->headline,
							'content'  => $reviewData->content,
							'author'   => $reviewData->author
						];
					}
				}
				break;
			case 'SoftwareApplication':
				$graph = [
					'id'         => '#aioseo-software-application-' . uniqid(),
					'slug'       => 'software-application',
					'graphName'  => 'SoftwareApplication',
					'label'      => __( 'Software', 'all-in-one-seo-pack' ),
					'properties' => [
						'name'            => ! empty( $schemaTypeOptions->software->name ) ? $schemaTypeOptions->software->name : '#post_title',
						'description'     => '#post_excerpt',
						'price'           => ! empty( $schemaTypeOptions->software->price ) ? (float) $schemaTypeOptions->software->price : '',
						'currency'        => ! empty( $schemaTypeOptions->software->currency ) ? $schemaTypeOptions->software->currency : '',
						'operatingSystem' => ! empty( $schemaTypeOptions->software->operatingSystems ) ? $schemaTypeOptions->software->operatingSystems : '',
						'category'        => ! empty( $schemaTypeOptions->software->category ) ? $schemaTypeOptions->software->category : '',
						'rating'          => [
							'value'   => '',
							'minimum' => 1,
							'maximum' => 5
						],
						'review'          => [
							'headline' => '',
							'content'  => '',
							'author'   => ''
						]
					]
				];

				$reviews = ! empty( $schemaTypeOptions->software->reviews ) ? $schemaTypeOptions->software->reviews : [];
				if ( ! empty( $reviews[0] ) ) {
					$reviewData = json_decode( $reviews[0] );
					if ( empty( $reviewData ) ) {
						break;
					}

					$graph['properties']['rating']['value'] = $reviewData->rating;
					$graph['properties']['review'] = [
						'headline' => $reviewData->headline,
						'content'  => $reviewData->content,
						'author'   => $reviewData->author
					];
				}
				break;
			case 'WebPage':
				if ( 'FAQPage' === $schemaTypeOptions->webPage->webPageType ) {
					$graph = [
						'id'         => '#aioseo-faq-page-' . uniqid(),
						'slug'       => 'faq-page',
						'graphName'  => 'FAQPage',
						'label'      => __( 'FAQ Page', 'all-in-one-seo-pack' ),
						'properties' => [
							'type'        => $schemaTypeOptions->webPage->webPageType,
							'name'        => '#post_title',
							'description' => '#post_excerpt',
							'questions'   => []
						]
					];

					$faqs = $schemaTypeOptions->faq->pages;
					if ( ! empty( $faqs ) ) {
						foreach ( $faqs as $faqData ) {
							$faqData = json_decode( $faqData );
							if ( empty( $faqData ) ) {
								continue;
							}

							$graph['properties']['questions'][] = [
								'question' => $faqData->question,
								'answer'   => $faqData->answer
							];
						}
					}
				} else {
					$graph = [
						'id'         => '#aioseo-web-page-' . uniqid(),
						'slug'       => 'web-page',
						'graphName'  => 'WebPage',
						'label'      => __( 'Web Page', 'all-in-one-seo-pack' ),
						'properties' => [
							'type'        => $schemaTypeOptions->webPage->webPageType,
							'name'        => '',
							'description' => ''
						]
					];
				}
				break;
			case 'default':
				$dynamicOptions = aioseo()->dynamicOptions->noConflict();
				if ( ! empty( $post->post_type ) && $dynamicOptions->searchAppearance->postTypes->has( $post->post_type ) ) {
					$schemaOptions->defaultGraph = $dynamicOptions->searchAppearance->postTypes->{$post->post_type}->schemaType;
				}
				break;
			case 'none':
				// If "none', we simply don't have to migrate anything.
			default:
				break;
		}

		if ( ! empty( $graph ) ) {
			if ( $isDefault ) {
				$schemaOptions->default->data->{$schemaType} = $graph;
			} else {
				$schemaOptions->graphs[]           = $graph;
				$schemaOptions->default->isEnabled = false;
			}
		}

		$aioseoPost->schema = $schemaOptions;
		$aioseoPost->save();

		return $aioseoPost;
	}

	/**
	 * Updates the dashboardWidgets with the new array format.
	 *
	 * @since 4.2.8
	 *
	 * @return void
	 */
	private function migrateDashboardWidgetsOptions() {
		$rawOptions = $this->getRawOptions();

		if ( empty( $rawOptions ) || ! is_bool( $rawOptions['advanced']['dashboardWidgets'] ) ) {
			return;
		}

		$widgets = [ 'seoNews' ];

		// If the dashboardWidgets was activated, let's turn on the other widgets.
		if ( ! empty( $rawOptions['advanced']['dashboardWidgets'] ) ) {
			$widgets[] = 'seoOverview';
			$widgets[] = 'seoSetup';
		}

		aioseo()->options->advanced->dashboardWidgets = $widgets;
	}

	/**
	 * Adds the seoChecklist widget to existing dashboardWidgets arrays.
	 *
	 * @since 4.9.4
	 *
	 * @return void
	 */
	private function addSeoChecklistToDashboardWidgets() {
		$rawOptions = $this->getRawOptions();

		if ( empty( $rawOptions ) || ! isset( $rawOptions['advanced']['dashboardWidgets'] ) || ! is_array( $rawOptions['advanced']['dashboardWidgets'] ) ) {
			return;
		}

		$widgets = $rawOptions['advanced']['dashboardWidgets'];

		// If seoChecklist is already in the array, don't add it again.
		if ( in_array( 'seoChecklist', $widgets, true ) ) {
			return;
		}

		$widgets[] = 'seoChecklist';

		aioseo()->options->advanced->dashboardWidgets = $widgets;
	}

	/**
	 * Schedules the revision records removal.
	 *
	 * @since 4.3.1
	 *
	 * @return void
	 */
	private function scheduleRemoveRevisionsRecords() {
		aioseo()->actionScheduler->scheduleSingle( 'aioseo_v419_remove_revision_records', 10, [], true );
	}

	/**
	 * Casts the priority column to a float.
	 *
	 * @since 4.3.9
	 *
	 * @return void
	 */
	private function migratePriorityColumn() {
		if ( ! aioseo()->core->db->columnExists( 'aioseo_posts', 'priority' ) ) {
			return;
		}

		$prefix               = aioseo()->core->db->prefix;
		$aioseoPostsTableName = $prefix . 'aioseo_posts';

		// First, cast the default value to NULL since it's a string.
		aioseo()->core->db->execute( "UPDATE {$aioseoPostsTableName} SET priority = NULL WHERE priority = 'default'" );

		// Then, alter the column to a float.
		aioseo()->core->db->execute( "ALTER TABLE {$aioseoPostsTableName} MODIFY priority float" );
	}

	/**
	 * Update the custom robots.txt rules to the new format,
	 * by replacing `rule` and `directoryPath` with `directive` and `fieldValue`, respectively.
	 *
	 * @since 4.4.2
	 *
	 * @return void
	 */
	private function updateRobotsTxtRules() {
		$rawOptions   = $this->getRawOptions();
		$currentRules = $rawOptions && ! empty( $rawOptions['tools']['robots']['rules'] )
			? $rawOptions['tools']['robots']['rules']
			: [];
		if ( empty( $currentRules ) || ! is_array( $currentRules ) ) {
			return;
		}

		$newRules = [];
		foreach ( $currentRules as $oldRule ) {
			$parsedRule = json_decode( $oldRule, true );
			if ( empty( $parsedRule['rule'] ) && empty( $parsedRule['directoryPath'] ) ) {
				continue;
			}

			$newRule = [
				'userAgent'  => array_key_exists( 'userAgent', $parsedRule ) ? $parsedRule['userAgent'] : '',
				'directive'  => array_key_exists( 'rule', $parsedRule ) ? $parsedRule['rule'] : '',
				'fieldValue' => array_key_exists( 'directoryPath', $parsedRule ) ? $parsedRule['directoryPath'] : '',
			];

			$newRules[] = wp_json_encode( $newRule );
		}

		if ( $newRules ) {
			aioseo()->options->tools->robots->rules = $newRules;
		}
	}

	/**
	 * Checks if the user is currently using the old GA Analytics v3 integration and create a notification.
	 *
	 * @since 4.5.1
	 *
	 * @return void
	 */
	private function checkForGaAnalyticsV3() {
		// If either MonsterInsights or ExactMetrics is active, let's return early.
		$pluginData = aioseo()->helpers->getPluginData();
		if (
			$pluginData['miPro']['activated'] ||
			$pluginData['miLite']['activated'] ||
			$pluginData['emPro']['activated'] ||
			$pluginData['emLite']['activated']
		) {
			return;
		}

		$rawOptions = $this->getRawOptions();
		if ( empty( $rawOptions['deprecated']['webmasterTools']['googleAnalytics']['id'] ) ) {
			return;
		}

		// Let's clear the notification if the search is working again.
		$notification = Models\Notification::getNotificationByName( 'google-analytics-v3-deprecation' );
		if ( $notification->exists() ) {
			$notification->dismissed = false;
			$notification->save();

			return;
		}

		// Determine which plugin name to use.
		$pluginName = 'MonsterInsights';
		if (
			(
				$pluginData['emPro']['installed'] ||
				$pluginData['emLite']['installed']
			) &&
			! $pluginData['miPro']['installed'] &&
			! $pluginData['miLite']['installed']
		) {
			$pluginName = 'ExactMetrics';
		}

		Models\Notification::addNotification( [
			'slug'              => uniqid(),
			'notification_name' => 'google-analytics-v3-deprecation',
			'title'             => __( 'Universal Analytics V3 Deprecation Notice', 'all-in-one-seo-pack' ),
			'content'           => sprintf(
				// Translators: 1 - Line break HTML tags, 2 - Plugin short name ("AIOSEO"), Analytics plugin name (e.g. "MonsterInsights").
				__( 'You have been using the %2$s Google Analytics V3 (Universal Analytics) integration which has been deprecated by Google and is no longer supported. This may affect your website\'s data accuracy and performance.%1$sTo ensure a seamless analytics experience, we recommend migrating to %3$s, a powerful analytics solution.%1$s%3$s offers advanced features such as real-time tracking, enhanced e-commerce analytics, and easy-to-understand reports, helping you make informed decisions to grow your online presence effectively.%1$sClick the button below to be redirected to the %3$s setup process, where you can start benefiting from its robust analytics capabilities immediately.', 'all-in-one-seo-pack' ), // phpcs:ignore Generic.Files.LineLength.MaxExceeded
				'<br><br>',
				AIOSEO_PLUGIN_SHORT_NAME,
				$pluginName
			),
			'type'              => 'error',
			'level'             => [ 'all' ],
			'button1_label'     => __( 'Fix Now', 'all-in-one-seo-pack' ),
			'button1_action'    => admin_url( 'admin.php?page=aioseo-monsterinsights' ),
			'start'             => gmdate( 'Y-m-d H:i:s' )
		] );
	}

	/**
	 * Adds a notification for the query arg monitor.
	 *
	 * @since 4.5.8
	 *
	 * @return void
	 */
	private function addQueryArgMonitorNotification() {
		$options = $this->getRawOptions();
		if (
			empty( $options['searchAppearance']['advanced']['crawlCleanup']['enable'] ) ||
			empty( $options['searchAppearance']['advanced']['crawlCleanup']['removeUnrecognizedQueryArgs'] )
		) {
			return;
		}

		$notification = Models\Notification::getNotificationByName( 'crawl-cleanup-updated' );
		if ( $notification->exists() ) {
			return;
		}

		Models\Notification::addNotification( [
			'slug'              => uniqid(),
			'notification_name' => 'crawl-cleanup-updated',
			'title'             => __( 'Crawl Cleanup changes you should know about', 'all-in-one-seo-pack' ),
			'content'           => __( 'We\'ve made some significant changes to how we monitor Query Args for our Crawl Cleanup feature. Instead of DISABLING all query args and requiring you to add individual exceptions, we\'ve now changed it to ALLOW all query args by default with the option to easily block unrecognized ones through our new log table.', 'all-in-one-seo-pack' ), // phpcs:ignore Generic.Files.LineLength.MaxExceeded
			'type'              => 'info',
			'level'             => [ 'all' ],
			'button1_label'     => __( 'Learn More', 'all-in-one-seo-pack' ),
			'button1_action'    => 'http://route#aioseo-search-appearance&aioseo-scroll=aioseo-query-arg-monitoring&aioseo-highlight=aioseo-query-arg-monitoring:advanced',
			'start'             => gmdate( 'Y-m-d H:i:s' )
		] );
	}

	/**
	 * Deprecates the "No Pagination for Canonical URLs" setting.
	 *
	 * @since 4.5.9
	 *
	 * @return void
	 */
	public function deprecateNoPaginationForCanonicalUrlsSetting() {
		$options = $this->getRawOptions();
		if ( empty( $options['searchAppearance']['advanced']['noPaginationForCanonical'] ) ) {
			return;
		}

		$deprecatedOptions = aioseo()->internalOptions->deprecatedOptions;
		if ( ! in_array( 'noPaginationForCanonical', $deprecatedOptions, true ) ) {
			$deprecatedOptions[]                         = 'noPaginationForCanonical';
			aioseo()->internalOptions->deprecatedOptions = $deprecatedOptions;
		}

		aioseo()->options->deprecated->searchAppearance->advanced->noPaginationForCanonical = true;
	}

	/**
	 * Deprecates the "Breadcrumbs enabled" setting.
	 *
	 * @since 4.6.5
	 *
	 * @return void
	 */
	public function deprecateBreadcrumbsEnabledSetting() {
		$options = $this->getRawOptions();
		if ( ! isset( $options['breadcrumbs']['enable'] ) || 1 === intval( $options['breadcrumbs']['enable'] ) ) {
			return;
		}

		$deprecatedOptions = aioseo()->internalOptions->deprecatedOptions;
		if ( ! in_array( 'breadcrumbsEnable', $deprecatedOptions, true ) ) {
			$deprecatedOptions[]                         = 'breadcrumbsEnable';
			aioseo()->internalOptions->deprecatedOptions = $deprecatedOptions;
		}

		aioseo()->options->deprecated->breadcrumbs->enable = false;
	}

	/**
	 * Cancels all outstanding sitemap ping actions.
	 * This is needed because we've removed the Ping class.
	 *
	 * @since 4.7.5
	 *
	 * @return void
	 */
	private function cancelScheduledSitemapPings() {
		as_unschedule_all_actions( 'aioseo_sitemap_ping' );
		as_unschedule_all_actions( 'aioseo_sitemap_ping_recurring' );
	}

	/**
	 * Disable email reports.
	 *
	 * @since 4.7.7
	 *
	 * @return void
	 */
	private function disableEmailReports() {
		aioseo()->options->advanced->emailSummary->enable = false;

		// Schedule a notification to remind the user to enable email reports in 2 weeks.
		aioseo()->actionScheduler->scheduleSingle( 'aioseo_email_reports_enable_reminder', 2 * WEEK_IN_SECONDS );
	}

	/**
	 * Cancels all occurrences of the report summary task.
	 * This is needed in order to force the scheduled date to be reset.
	 *
	 * @since 4.7.9
	 *
	 * @return void
	 */
	private function rescheduleEmailReport() {
		as_unschedule_all_actions( aioseo()->emailReports->summary->actionHook );
	}

	/**
	 * Fixes headlines that could not be analyzed.
	 *
	 * @since 4.7.9
	 *
	 * @return void
	 */
	private function fixSavedHeadlines() {
		$headlines = aioseo()->internalOptions->internal->headlineAnalysis->headlines;
		if ( empty( $headlines ) ) {
			return;
		}

		foreach ( $headlines as $key => $headline ) {
			if ( ! json_decode( $headline ) ) {
				unset( $headlines[ $key ] );
			}
		}

		aioseo()->internalOptions->internal->headlineAnalysis->headlines = $headlines;
	}

	/**
	 * Resets the image scan date in order to force a new scan.
	 * This is needed because we're now storing relative URLs in order to support site migrations.
	 *
	 * @since 4.8.3
	 *
	 * @return void
	 */
	private function resetImageScanDate() {
		aioseo()->core->db->update( 'aioseo_posts' )
			->set(
				[
					'image_scan_date' => null
				]
			)
			->run();
	}

	/**
	 * Migrate the SeoAnalyzer homepage results from the Internal Optinos to the new table.
	 *
	 * @since 4.8.3
	 *
	 * @return void
	 */
	private function migrateSeoAnalyzerResults() {
		$internalOptions = $this->getRawInternalOptions();
		$results         = ! empty( $internalOptions['internal']['siteAnalysis']['results'] ) ? $internalOptions['internal']['siteAnalysis']['results'] : [];
		if ( empty( $results ) ) {
			return;
		}

		$parsedData = [
			'results' => is_string( $results ) ? json_decode( $results, true ) : $results,
			'score'   => $internalOptions['internal']['siteAnalysis']['score'],
		];

		Models\SeoAnalyzerResult::addResults( $parsedData );

		aioseo()->core->cache->delete( 'analyze_site_code' );
		aioseo()->core->cache->delete( 'analyze_site_body' );
	}

	/**
	 * Migrate the SeoAnalyzer competitors results from the Internal Optinos to the new table.
	 *
	 * @since 4.8.3
	 *
	 * @return void
	 */
	private function migrateSeoAnalyzerCompetitors() {
		$internalOptions = $this->getRawInternalOptions();
		$competitors     = ! empty( $internalOptions['internal']['siteAnalysis']['competitors'] ) ? $internalOptions['internal']['siteAnalysis']['competitors'] : [];
		if ( empty( $competitors ) ) {
			return;
		}

		foreach ( $competitors as $url => $competitor ) {
			$parsedData = is_string( $competitor ) ? json_decode( $competitor, true ) : $competitor;
			$results    = empty( $parsedData['results'] ) ? [] : $parsedData['results'];
			if ( empty( $results ) ) {
				continue;
			}

			Models\SeoAnalyzerResult::addResults( [
				'results' => $results,
				'score'   => $parsedData['score'],
			], $url );
		}

		aioseo()->core->cache->delete( 'analyze_site_code' );
		aioseo()->core->cache->delete( 'analyze_site_body' );
	}

	/**
	 * Returns the raw options from the database.
	 *
	 * @since 4.8.3
	 *
	 * @return array
	 */
	private function getRawInternalOptions() {
		// Options from the DB.
		$internalOptions = json_decode( get_option( aioseo()->internalOptions->optionsName ), true );
		if ( empty( $internalOptions ) ) {
			$internalOptions = [];
		}

		return $internalOptions;
	}

	/**
	 * Adds the column index for the cornerstone content table.
	 *
	 * @since 4.8.7
	 *
	 * @return void
	 */
	private function addColumnIndexForCornerstoneContent() {
		if (
			! aioseo()->core->db->columnExists( 'aioseo_posts', 'pillar_content' ) ||
			aioseo()->core->db->indexExists( 'aioseo_posts', 'ndx_aioseo_posts_pillar_content' )
		) {
			return;
		}

		$tableName = aioseo()->core->db->db->prefix . 'aioseo_posts';
		aioseo()->core->db->execute(
			"ALTER TABLE {$tableName}
			ADD INDEX ndx_aioseo_posts_pillar_content (pillar_content)"
		);
	}

	/**
	 * Synchronizes database schema with defined schema using dbDelta.
	 *
	 * This method uses WordPress's dbDelta() function to automatically:
	 * - Create tables that don't exist
	 * - Add missing columns to existing tables
	 * - Modify column definitions that have changed
	 *
	 * Note: dbDelta CANNOT drop columns or rename columns. Those operations
	 * must be handled separately with custom SQL in version-gated migrations.
	 *
	 * @since 4.9.7
	 *
	 * @return void
	 */
	private function updateDbSchema() {
		if ( ! function_exists( 'dbDelta' ) ) {
			require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		}

		// Ensure the cache table is properly cleaned before running dbDelta.
		// This is a safety net for cases where PreUpdates couldn't acquire its lock
		// (e.g. concurrent requests) and the table still has the old schema with rows.
		// Without this, dbDelta adds the 'name' column with default '' to all existing rows,
		// causing "Duplicate entry" errors when adding the UNIQUE KEY.
		aioseo()->preUpdates->createCacheTable();
		aioseo()->preUpdates->createCrawlCleanupLogsTable();

		// Get all schema definitions and run dbDelta
		$schemas = aioseo()->dbSchema->getSchema();
		dbDelta( $schemas );

		// Clear schema cache so columnExists/tableExists work correctly
		aioseo()->core->cache->delete( 'db_schema' );
	}

	/**
	 * Migrates sensitive values from Options/InternalOptions to the new SensitiveOptions storage.
	 *
	 * @since 4.9.5
	 *
	 * @return void
	 */
	private function migrateSensitiveOptions() {
		// Migrate from InternalOptions.
		$rawInternalOptions = json_decode( (string) get_option( 'aioseo_options_internal', '' ), true );
		if ( ! is_array( $rawInternalOptions ) ) {
			$rawInternalOptions = [];
		}

		$internalMappings = [
			'connectLicenseKey'          => [ 'internal', 'connectLicenseKey' ],
			'aiAccessToken'              => [ 'internal', 'ai', 'accessToken' ],
			'semrushAccessToken'         => [ 'integrations', 'semrush', 'accessToken' ],
			'semrushRefreshToken'        => [ 'integrations', 'semrush', 'refreshToken' ],
			'searchStatisticsTrustToken' => [ 'internal', 'searchStatistics', 'trustToken' ],
			'siteAnalysisConnectToken'   => [ 'internal', 'siteAnalysis', 'connectToken' ]
		];

		$internalOptionsChanged = false;
		foreach ( $internalMappings as $newKey => $path ) {
			$value = aioseo()->helpers->getNestedValue( $rawInternalOptions, $path );
			if ( ! empty( $value ) && is_string( $value ) ) {
				aioseo()->sensitiveOptions->set( $newKey, $value );

				// Remove the old value from the internal options.
				$this->unsetNestedValue( $rawInternalOptions, $path );
				$internalOptionsChanged = true;
			}
		}

		// Migrate search statistics profile key and token from the profile array.
		$profile = aioseo()->helpers->getNestedValue( $rawInternalOptions, [ 'internal', 'searchStatistics', 'profile' ] );
		if ( is_array( $profile ) ) {
			if ( ! empty( $profile['key'] ) && is_string( $profile['key'] ) ) {
				aioseo()->sensitiveOptions->set( 'searchStatisticsProfileKey', $profile['key'] );
				unset( $rawInternalOptions['internal']['searchStatistics']['profile']['key'] );
				$internalOptionsChanged = true;
			}
			if ( ! empty( $profile['token'] ) && is_string( $profile['token'] ) ) {
				aioseo()->sensitiveOptions->set( 'searchStatisticsProfileToken', $profile['token'] );
				unset( $rawInternalOptions['internal']['searchStatistics']['profile']['token'] );
				$internalOptionsChanged = true;
			}
		}

		if ( $internalOptionsChanged ) {
			update_option( 'aioseo_options_internal', wp_json_encode( $rawInternalOptions ), false );
		}

		// Migrate Lite connect key/token.
		if ( ! aioseo()->pro ) {
			$rawLiteInternalOptions = json_decode( (string) get_option( 'aioseo_options_internal_lite', '' ), true );
			if ( is_array( $rawLiteInternalOptions ) ) {
				$liteOptionsChanged = false;

				$connectKey = aioseo()->helpers->getNestedValue( $rawLiteInternalOptions, [ 'internal', 'connect', 'key' ] );
				if ( ! empty( $connectKey ) && is_string( $connectKey ) ) {
					aioseo()->sensitiveOptions->set( 'connectKey', $connectKey );
					unset( $rawLiteInternalOptions['internal']['connect']['key'] );
					$liteOptionsChanged = true;
				}

				$connectToken = aioseo()->helpers->getNestedValue( $rawLiteInternalOptions, [ 'internal', 'connect', 'token' ] );
				if ( ! empty( $connectToken ) && is_string( $connectToken ) ) {
					aioseo()->sensitiveOptions->set( 'connectToken', $connectToken );
					unset( $rawLiteInternalOptions['internal']['connect']['token'] );
					$liteOptionsChanged = true;
				}

				if ( $liteOptionsChanged ) {
					update_option( 'aioseo_options_internal_lite', wp_json_encode( $rawLiteInternalOptions ), false );
				}
			}
		}

		// Force save the sensitive options.
		aioseo()->sensitiveOptions->save( true );
	}

	/**
	 * Removes leaked `key` and `token` from the Search Statistics profile array.
	 *
	 * The 4.9.6 sensitive-options migration unset these on the raw DB option, but the
	 * in-memory InternalOptions model (already loaded at boot) re-saved them on shutdown
	 * because `searchStatistics.profile` was a generic `array` leaf — its inner keys
	 * weren't filtered against the schema. The schema is now structured, so a forced
	 * save strips legacy subkeys automatically; backfill sensitive options first in case
	 * the prior migration missed them.
	 *
	 * @since 4.9.7
	 *
	 * @return void
	 */
	private function cleanupSearchStatisticsProfile() {
		$rawInternalOptions = json_decode( (string) get_option( 'aioseo_options_internal', '' ), true );
		$profile            = is_array( $rawInternalOptions )
			? aioseo()->helpers->getNestedValue( $rawInternalOptions, [ 'internal', 'searchStatistics', 'profile' ] )
			: null;

		if ( is_array( $profile ) ) {
			if ( ! empty( $profile['key'] ) && is_string( $profile['key'] ) && ! aioseo()->sensitiveOptions->hasValue( 'searchStatisticsProfileKey' ) ) {
				aioseo()->sensitiveOptions->set( 'searchStatisticsProfileKey', $profile['key'] );
			}
			if ( ! empty( $profile['token'] ) && is_string( $profile['token'] ) && ! aioseo()->sensitiveOptions->hasValue( 'searchStatisticsProfileToken' ) ) {
				aioseo()->sensitiveOptions->set( 'searchStatisticsProfileToken', $profile['token'] );
			}
			aioseo()->sensitiveOptions->save( true );
		}

		// Force-save internal options so the structured `profile` schema strips legacy subkeys.
		aioseo()->internalOptions->save( true );
	}

	/**
	 * Unsets a nested value in an array by path.
	 *
	 * @since 4.9.6
	 *
	 * @param  array $array The array to modify (passed by reference).
	 * @param  array $path  The path to the value.
	 * @return void
	 */
	private function unsetNestedValue( &$array, $path ) {
		$lastKey = array_pop( $path );
		$current = &$array;
		foreach ( $path as $key ) {
			if ( ! isset( $current[ $key ] ) || ! is_array( $current[ $key ] ) ) {
				return;
			}
			$current = &$current[ $key ];
		}

		unset( $current[ $lastKey ] );
	}
}