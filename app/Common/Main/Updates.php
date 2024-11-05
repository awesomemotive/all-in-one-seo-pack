<?php
namespace AIOSEO\Plugin\Common\Main;

use AIOSEO\Plugin\Common\Models;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

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
		// The dynamic options have not yet fully loaded, so let's refresh here to force that to happen.
		aioseo()->dynamicOptions->refresh(); // TODO: Check if we still need this since it already runs on 999 in the main AIOSEO file.

		$lastActiveVersion = aioseo()->internalOptions->internal->lastActiveVersion;
		if ( version_compare( $lastActiveVersion, '4.0.5', '<' ) ) {
			$this->addImageScanDateColumn();
		}

		if ( version_compare( $lastActiveVersion, '4.0.6', '<' ) ) {
			$this->disableTwitterUseOgDefault();
			$this->updateMaxImagePreviewDefault();
		}

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
			$this->addNotificationsNewColumn();
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
			aioseo()->actionScheduler->scheduleSingle( aioseo()->core->cachePrune->getOptionCacheCleanAction(), MINUTE_IN_SECONDS );

			// Refresh with new Redirects capability.
			$this->accessControlNewCapabilities();

			// Regenerate the sitemap if using a static one to update the data for the new stylesheets.
			aioseo()->sitemap->regenerateStaticSitemap();

			$this->fixSchemaTypeDefault();
		}

		if ( version_compare( $lastActiveVersion, '4.1.6', '<' ) ) {
			// Remove the recurring scheduled action for notifications.
			aioseo()->actionScheduler->unschedule( 'aioseo_admin_notifications_update' );

			$this->migrateOgTwitterImageColumns();

			// Set the OG data to false for current installs.
			aioseo()->options->social->twitter->general->useOgData = false;
		}

		if ( version_compare( $lastActiveVersion, '4.1.8', '<' ) ) {
			$this->addLimitModifiedDateColumn();

			// Refresh with new Redirects Page capability.
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
			// Force WordPress to flush the rewrite rules.
			aioseo()->options->flushRewriteRules();

			Models\Notification::deleteNotificationByName( 'deprecated-filters' );
			Models\Notification::deleteNotificationByName( 'deprecated-filters-v2' );
		}

		if ( version_compare( $lastActiveVersion, '4.2.2', '<' ) ) {
			aioseo()->internalOptions->database->installedTables = '';

			$this->addOptionsColumn();
			$this->removeTabsColumn();
			$this->migrateUserContactMethods();

			// Unschedule any static sitemap regeneration actions to remove any that failed and are still in-progress as a result.
			aioseo()->actionScheduler->unschedule( 'aioseo_static_sitemap_regeneration' );
		}

		if ( version_compare( $lastActiveVersion, '4.2.4', '<' ) ) {
			$this->addNotificationsAddonColumn();
		}

		if ( version_compare( $lastActiveVersion, '4.2.5', '<' ) ) {
			$this->addSchemaColumn();
			$this->schedulePostSchemaMigration();
		}

		if ( version_compare( $lastActiveVersion, '4.2.4.2', '>' ) && version_compare( $lastActiveVersion, '4.2.6', '<' ) ) {
			// The default graphs only need to be remigrated if the user was on 4.2.5 or 4.2.5.1.
			$this->schedulePostSchemaDefaultMigration();
		}

		if ( version_compare( $lastActiveVersion, '4.2.8', '<' ) ) {
			$this->migrateDashboardWidgetsOptions();
		}

		if ( version_compare( $lastActiveVersion, '4.3.6', '<' ) ) {
			$this->addPrimaryTermColumn();
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
			$this->addQueryArgMonitorTables();
			$this->addQueryArgMonitorNotification();
		}

		if ( version_compare( $lastActiveVersion, '4.5.9', '<' ) ) {
			$this->deprecateNoPaginationForCanonicalUrlsSetting();
		}

		if ( version_compare( $lastActiveVersion, '4.6.5', '<' ) ) {
			$this->deprecateBreadcrumbsEnabledSetting();
		}

		if ( version_compare( $lastActiveVersion, '4.7.4', '<' ) ) {
			$this->addWritingAssistantTables();
			aioseo()->access->addCapabilities();
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
		aioseo()->internalOptions->database->installedTables = '';

		// Bust the DB cache so we can make sure that everything is fresh.
		aioseo()->core->db->bustCache();
	}

	/**
	 * Adds our custom tables for V4.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function addInitialCustomTablesForV4() {
		$db             = aioseo()->core->db->db;
		$charsetCollate = '';

		if ( ! empty( $db->charset ) ) {
			$charsetCollate .= "DEFAULT CHARACTER SET {$db->charset}";
		}
		if ( ! empty( $db->collate ) ) {
			$charsetCollate .= " COLLATE {$db->collate}";
		}

		// Check for notifications table.
		if ( ! aioseo()->core->db->tableExists( 'aioseo_notifications' ) ) {
			$tableName = $db->prefix . 'aioseo_notifications';

			aioseo()->core->db->execute(
				"CREATE TABLE {$tableName} (
					id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
					slug varchar(13) NOT NULL,
					title text NOT NULL,
					content longtext NOT NULL,
					type varchar(64) NOT NULL,
					level text NOT NULL,
					notification_id bigint(20) unsigned DEFAULT NULL,
					notification_name varchar(255) DEFAULT NULL,
					start datetime DEFAULT NULL,
					end datetime DEFAULT NULL,
					button1_label varchar(255) DEFAULT NULL,
					button1_action varchar(255) DEFAULT NULL,
					button2_label varchar(255) DEFAULT NULL,
					button2_action varchar(255) DEFAULT NULL,
					dismissed tinyint(1) NOT NULL DEFAULT 0,
					created datetime NOT NULL,
					updated datetime NOT NULL,
					PRIMARY KEY (id),
					UNIQUE KEY ndx_aioseo_notifications_slug (slug),
					KEY ndx_aioseo_notifications_dates (start, end),
					KEY ndx_aioseo_notifications_type (type),
					KEY ndx_aioseo_notifications_dismissed (dismissed)
				) {$charsetCollate};"
			);
		}

		if ( ! aioseo()->core->db->tableExists( 'aioseo_posts' ) ) {
			$tableName = $db->prefix . 'aioseo_posts';

			// Incorrect defaults are adjusted below through migrations.
			aioseo()->core->db->execute(
				"CREATE TABLE {$tableName} (
					id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
					post_id bigint(20) unsigned NOT NULL,
					title text DEFAULT NULL,
					description text DEFAULT NULL,
					keywords mediumtext DEFAULT NULL,
					keyphrases longtext DEFAULT NULL,
					page_analysis longtext DEFAULT NULL,
					canonical_url text DEFAULT NULL,
					og_title text DEFAULT NULL,
					og_description text DEFAULT NULL,
					og_object_type varchar(64) DEFAULT 'default',
					og_image_type varchar(64) DEFAULT 'default',
					og_image_custom_url text DEFAULT NULL,
					og_image_custom_fields text DEFAULT NULL,
					og_custom_image_width int(11) DEFAULT NULL,
					og_custom_image_height int(11) DEFAULT NULL,
					og_video varchar(255) DEFAULT NULL,
					og_custom_url text DEFAULT NULL,
					og_article_section text DEFAULT NULL,
					og_article_tags text DEFAULT NULL,
					twitter_use_og tinyint(1) DEFAULT 1,
					twitter_card varchar(64) DEFAULT 'default',
					twitter_image_type varchar(64) DEFAULT 'default',
					twitter_image_custom_url text DEFAULT NULL,
					twitter_image_custom_fields text DEFAULT NULL,
					twitter_title text DEFAULT NULL,
					twitter_description text DEFAULT NULL,
					seo_score int(11) DEFAULT 0 NOT NULL,
					schema_type varchar(20) DEFAULT NULL,
					schema_type_options longtext DEFAULT NULL,
					pillar_content tinyint(1) DEFAULT NULL,
					robots_default tinyint(1) DEFAULT 1 NOT NULL,
					robots_noindex tinyint(1) DEFAULT 0 NOT NULL,
					robots_noarchive tinyint(1) DEFAULT 0 NOT NULL,
					robots_nosnippet tinyint(1) DEFAULT 0 NOT NULL,
					robots_nofollow tinyint(1) DEFAULT 0 NOT NULL,
					robots_noimageindex tinyint(1) DEFAULT 0 NOT NULL,
					robots_noodp tinyint(1) DEFAULT 0 NOT NULL,
					robots_notranslate tinyint(1) DEFAULT 0 NOT NULL,
					robots_max_snippet int(11) DEFAULT NULL,
					robots_max_videopreview int(11) DEFAULT NULL,
					robots_max_imagepreview varchar(20) DEFAULT 'none',
					tabs mediumtext DEFAULT NULL,
					images longtext DEFAULT NULL,
					priority tinytext DEFAULT NULL,
					frequency tinytext DEFAULT NULL,
					videos longtext DEFAULT NULL,
					video_thumbnail text DEFAULT NULL,
					video_scan_date datetime DEFAULT NULL,
					local_seo longtext DEFAULT NULL,
					created datetime NOT NULL,
					updated datetime NOT NULL,
					PRIMARY KEY (id),
					KEY ndx_aioseo_posts_post_id (post_id)
				) {$charsetCollate};"
			);
		}

		// Reset the cache for the installed tables.
		aioseo()->internalOptions->database->installedTables = '';
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
	 * Adds the image scan date column to our posts table.
	 *
	 * @since 4.0.5
	 *
	 * @return void
	 */
	public function addImageScanDateColumn() {
		if ( ! aioseo()->core->db->columnExists( 'aioseo_posts', 'image_scan_date' ) ) {
			$tableName = aioseo()->core->db->db->prefix . 'aioseo_posts';
			aioseo()->core->db->execute(
				"ALTER TABLE {$tableName}
				ADD image_scan_date datetime DEFAULT NULL AFTER images"
			);

			// Reset the cache for the installed tables.
			aioseo()->internalOptions->database->installedTables = '';
		}
	}

	/**
	 * Modifes the default value of the twitter_use_og column.
	 *
	 * @since 4.0.6
	 *
	 * @return void
	 */
	protected function disableTwitterUseOgDefault() {
		if ( aioseo()->core->db->tableExists( 'aioseo_posts' ) ) {
			$tableName = aioseo()->core->db->db->prefix . 'aioseo_posts';
			aioseo()->core->db->execute(
				"ALTER TABLE {$tableName}
				MODIFY twitter_use_og tinyint(1) DEFAULT 0"
			);
		}
	}

	/**
	 * Modifes the default value of the robots_max_imagepreview column.
	 *
	 * @since 4.0.6
	 *
	 * @return void
	 */
	protected function updateMaxImagePreviewDefault() {
		if ( aioseo()->core->db->tableExists( 'aioseo_posts' ) ) {
			$tableName = aioseo()->core->db->db->prefix . 'aioseo_posts';
			aioseo()->core->db->execute(
				"ALTER TABLE {$tableName}
				MODIFY robots_max_imagepreview varchar(20) DEFAULT 'large'"
			);
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
			->orderBy( 'count(post_id) DESC' )
			->run()
			->result();

		if ( empty( $duplicates ) ) {
			return;
		}

		foreach ( $duplicates as $duplicate ) {
			$postId        = $duplicate->post_id;
			$firstRecordId = $duplicate->id;

			aioseo()->core->db->delete( 'aioseo_posts' )
				->whereRaw( "( id > $firstRecordId AND post_id = $postId )" )
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
	 * Adds the new flag to the notifications table.
	 *
	 * @since 4.1.3
	 *
	 * @return void
	 */
	public function addNotificationsNewColumn() {
		if ( ! aioseo()->core->db->columnExists( 'aioseo_notifications', 'new' ) ) {
			$tableName = aioseo()->core->db->db->prefix . 'aioseo_notifications';
			aioseo()->core->db->execute(
				"ALTER TABLE {$tableName}
				ADD new tinyint(1) NOT NULL DEFAULT 1 AFTER dismissed"
			);

			// Reset the cache for the installed tables.
			aioseo()->internalOptions->database->installedTables = '';

			aioseo()->core->db
				->update( 'aioseo_notifications' )
				->where( 'new', 1 )
				->set( 'new', 0 )
				->run();
		}
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
	 * Fixes the default value for the post schema type.
	 *
	 * @since 4.1.5
	 *
	 * @return void
	 */
	private function fixSchemaTypeDefault() {
		if ( aioseo()->core->db->tableExists( 'aioseo_posts' ) && aioseo()->core->db->columnExists( 'aioseo_posts', 'schema_type' ) ) {
			$tableName = aioseo()->core->db->db->prefix . 'aioseo_posts';
			aioseo()->core->db->execute(
				"ALTER TABLE {$tableName}
				MODIFY schema_type varchar(20) DEFAULT 'default'"
			);
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
			aioseo()->internalOptions->database->installedTables = '';
		}
	}

	/**
	 * Adds the limit modified date column to our posts table.
	 *
	 * @since 4.1.8
	 *
	 * @return void
	 */
	private function addLimitModifiedDateColumn() {
		if ( ! aioseo()->core->db->columnExists( 'aioseo_posts', 'limit_modified_date' ) ) {
			$tableName = aioseo()->core->db->db->prefix . 'aioseo_posts';
			aioseo()->core->db->execute(
				"ALTER TABLE {$tableName}
				ADD limit_modified_date tinyint(1) NOT NULL DEFAULT 0 AFTER local_seo"
			);

			// Reset the cache for the installed tables.
			aioseo()->internalOptions->database->installedTables = '';
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
	 * Add options column.
	 *
	 * @since 4.2.2
	 *
	 * @return void
	 */
	private function addOptionsColumn() {
		if ( ! aioseo()->core->db->columnExists( 'aioseo_posts', 'options' ) ) {
			$tableName = aioseo()->core->db->db->prefix . 'aioseo_posts';
			aioseo()->core->db->execute(
				"ALTER TABLE {$tableName}
				ADD `options` longtext DEFAULT NULL AFTER `limit_modified_date`"
			);

			// Reset the cache for the installed tables.
			aioseo()->internalOptions->database->installedTables = '';
		}
	}

	/**
	 * Remove the tabs column as it is unnecessary.
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
	 * Add an addon column to the notifications table.
	 *
	 * @since 4.2.4
	 *
	 * @return void
	 */
	private function addNotificationsAddonColumn() {
		if ( ! aioseo()->core->db->columnExists( 'aioseo_notifications', 'addon' ) ) {
			$tableName = aioseo()->core->db->db->prefix . 'aioseo_notifications';
			aioseo()->core->db->execute(
				"ALTER TABLE {$tableName}
				ADD `addon` varchar(64) DEFAULT NULL AFTER `slug`"
			);

			// Reset the cache for the installed tables.
			aioseo()->internalOptions->database->installedTables = '';
		}
	}

	/**
	 * Adds the schema column.
	 *
	 * @since 4.2.5
	 *
	 * @return void
	 */
	private function addSchemaColumn() {
		if ( ! aioseo()->core->db->columnExists( 'aioseo_posts', 'schema' ) ) {
			$tableName = aioseo()->core->db->db->prefix . 'aioseo_posts';
			aioseo()->core->db->execute(
				"ALTER TABLE {$tableName}
				ADD `schema` longtext DEFAULT NULL AFTER `seo_score`"
			);
		}
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
			->whereRaw( '`schema` IS NULL' )
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
		$migrationStartDate = aioseo()->core->cache->get( 'v4_migrate_post_schema_default_date' );
		if ( ! $migrationStartDate ) {
			return;
		}

		$posts = aioseo()->core->db->start( 'aioseo_posts' )
			->select( '*' )
			->where( 'schema_type =', 'default' )
			->whereRaw( "updated < '$migrationStartDate'" )
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
				if ( preg_match( '/gtin/i', $identifierType ) ) {
					$graph['properties']['identifiers']['gtin'] = $identifier;
				}

				if ( preg_match( '/mpn/i', $identifierType ) ) {
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
	 * Adds the primary_term column to the aioseo_posts table.
	 *
	 * @since 4.3.6
	 *
	 * @return void
	 */
	private function addPrimaryTermColumn() {
		if ( ! aioseo()->core->db->columnExists( 'aioseo_posts', 'primary_term' ) ) {
			$tableName = aioseo()->core->db->db->prefix . 'aioseo_posts';
			aioseo()->core->db->execute(
				"ALTER TABLE {$tableName}
				ADD `primary_term` longtext DEFAULT NULL AFTER `page_analysis`"
			);
		}
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
	 * Adds our custom tables for the query arg monitor.
	 *
	 * @since 4.5.8
	 *
	 * @return void
	 */
	public function addQueryArgMonitorTables() {
		$db             = aioseo()->core->db->db;
		$charsetCollate = '';

		if ( ! empty( $db->charset ) ) {
			$charsetCollate .= "DEFAULT CHARACTER SET {$db->charset}";
		}
		if ( ! empty( $db->collate ) ) {
			$charsetCollate .= " COLLATE {$db->collate}";
		}

		// Check for crawl cleanup logs table.
		if ( ! aioseo()->core->db->tableExists( 'aioseo_crawl_cleanup_logs' ) ) {
			$tableName = $db->prefix . 'aioseo_crawl_cleanup_logs';

			aioseo()->core->db->execute(
				"CREATE TABLE {$tableName} (
					`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
					`slug` text NOT NULL,
					`key` text NOT NULL,
					`value` text,
					`hash` varchar(40) NOT NULL,
					`hits` int(20) NOT NULL DEFAULT 1,
					`created` datetime NOT NULL,
					`updated` datetime NOT NULL,
					PRIMARY KEY (id),
					UNIQUE KEY ndx_aioseo_crawl_cleanup_logs_hash (hash)
				) {$charsetCollate};"
			);
		}

		// Check for crawl cleanup blocked table.
		if ( ! aioseo()->core->db->tableExists( 'aioseo_crawl_cleanup_blocked_args' ) ) {
			$tableName = $db->prefix . 'aioseo_crawl_cleanup_blocked_args';

			aioseo()->core->db->execute(
				"CREATE TABLE {$tableName} (
					`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
					`key` text,
					`value` text,
					`key_value_hash` varchar(40),
					`regex` varchar(150),
					`hits` int(20) NOT NULL DEFAULT 0,
					`created` datetime NOT NULL,
					`updated` datetime NOT NULL,
					PRIMARY KEY (id),
					UNIQUE KEY ndx_aioseo_crawl_cleanup_blocked_args_key_value_hash (key_value_hash),
					UNIQUE KEY ndx_aioseo_crawl_cleanup_blocked_args_regex (regex)
				) {$charsetCollate};"
			);
		}
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
	 * Add tables for Writing Assistant.
	 *
	 * @since 4.7.4
	 *
	 * @return void
	 */
	private function addWritingAssistantTables() {
		$db             = aioseo()->core->db->db;
		$charsetCollate = '';

		if ( ! empty( $db->charset ) ) {
			$charsetCollate .= "DEFAULT CHARACTER SET {$db->charset}";
		}
		if ( ! empty( $db->collate ) ) {
			$charsetCollate .= " COLLATE {$db->collate}";
		}

		if ( ! aioseo()->core->db->tableExists( 'aioseo_writing_assistant_posts' ) ) {
			$tableName = $db->prefix . 'aioseo_writing_assistant_posts';

			aioseo()->core->db->execute(
				"CREATE TABLE {$tableName} (
					`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
					`post_id` bigint(20) unsigned DEFAULT NULL,
					`keyword_id` bigint(20) unsigned DEFAULT NULL,
					`content_analysis_hash` VARCHAR(40) DEFAULT NULL,
					`content_analysis` text DEFAULT NULL,
					`created` datetime NOT NULL,
					`updated` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
					PRIMARY KEY (id),
					UNIQUE KEY ndx_aioseo_writing_assistant_posts_post_id (post_id),
					KEY ndx_aioseo_writing_assistant_posts_keyword_id (keyword_id)
				) {$charsetCollate};"
			);
		}

		if ( ! aioseo()->core->db->tableExists( 'aioseo_writing_assistant_keywords' ) ) {
			$tableName = $db->prefix . 'aioseo_writing_assistant_keywords';

			aioseo()->core->db->execute(
				"CREATE TABLE {$tableName} (
					`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
					`uuid` varchar(40) NOT NULL,
					`keyword` varchar(255) NOT NULL,
					`country` varchar(10) NOT NULL DEFAULT 'us',
					`language` varchar(10) NOT NULL DEFAULT 'en',
					`progress` tinyint(3) DEFAULT 0,
					`keywords` mediumtext NULL,
					`competitors` mediumtext NULL,
					`created` datetime NOT NULL,
					`updated` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
					PRIMARY KEY (id),
					UNIQUE KEY ndx_aioseo_writing_assistant_keywords_uuid (uuid),
					KEY ndx_aioseo_writing_assistant_keywords_keyword (keyword)
				) {$charsetCollate};"
			);
		}
	}
}