<?php
namespace AIOSEO\Plugin\Common\Sitemap;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AIOSEO\Plugin\Common\Models;

/**
 * Handles our sitemaps.
 *
 * @since 4.0.0
 */
class Sitemap extends SitemapAbstract {
	/**
	 * The sitemap filename.
	 *
	 * @since 4.4.2
	 *
	 * @var string
	 */
	public $filename = '';

	/**
	 * Whether the sitemap indexes are enabled.
	 *
	 * @since 4.4.2
	 *
	 * @var bool
	 */
	public $indexes = false;

	/**
	 * The sitemap index name.
	 *
	 * @since 4.4.2
	 *
	 * @var string
	 */
	public $indexName = '';

	/**
	 * The number of links per index.
	 *
	 * @since 4.4.2
	 *
	 * @var int
	 */
	public $linksPerIndex = 1000;

	/**
	 * The current page number.
	 *
	 * @since 4.4.2
	 *
	 * @var int
	 */
	public $pageNumber = 0;

	/**
	 * The entries' offset.
	 *
	 * @since 4.4.2
	 *
	 * @var int
	 */
	public $offset = 0;

	/**
	 * Whether the sitemap is static.
	 *
	 * @since 4.4.2
	 *
	 * @var bool
	 */
	public $isStatic = false;

	/**
	 * Class constructor.
	 *
	 * @since 4.0.0
	 */
	public function __construct() {
		$this->content       = new Content();
		$this->root          = new Root();
		$this->query         = new Query();
		$this->file          = new File();
		$this->image         = new Image\Image();
		$this->ping          = new Ping();
		$this->priority      = new Priority();
		$this->output        = new Output();
		$this->helpers       = new Helpers();
		$this->requestParser = new RequestParser();
		$this->xsl           = new Xsl();

		new Localization();

		$this->disableWpSitemap();
	}

	/**
	 * Adds our hooks.
	 * Note: This runs init and is triggered in the main AIOSEO class.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function init() {
		add_action( 'aioseo_static_sitemap_regeneration', [ $this, 'regenerateStaticSitemap' ] );

		// Check if static files need to be updated.
		add_action( 'wp_insert_post', [ $this, 'regenerateOnUpdate' ] );
		add_action( 'edited_term', [ $this, 'regenerateStaticSitemap' ] );

		add_action( 'admin_init', [ $this, 'detectStatic' ] );

		$this->maybeAddHtaccessRewriteRules();
	}

	/**
	 * Disables the WP Core sitemap if our general sitemap is enabled.
	 *
	 * @since 4.2.1
	 *
	 * @return void
	 */
	protected function disableWpSitemap() {
		if ( ! aioseo()->options->sitemap->general->enable ) {
			return;
		}

		remove_action( 'init', 'wp_sitemaps_get_server' );
		add_filter( 'wp_sitemaps_enabled', '__return_false' );
	}

	/**
	 * Check if the .htaccess rewrite rules are present if the user is using Apache. If not, add them.
	 *
	 * @since 4.2.5
	 *
	 * @return void
	 */
	private function maybeAddHtaccessRewriteRules() {
		if ( ! aioseo()->helpers->isApache() || wp_doing_ajax() || wp_doing_cron() ) {
			return;
		}

		ob_start();
		aioseo()->templates->getTemplate( 'sitemap/htaccess-rewrite-rules.php' );
		$rewriteRules = ob_get_clean();

		$escapedRewriteRules = aioseo()->helpers->escapeRegex( $rewriteRules );

		$contents = aioseo()->helpers->decodeHtmlEntities( aioseo()->htaccess->getContents() );
		if ( get_option( 'permalink_structure' ) ) {
			if ( preg_match( '/All in One SEO Sitemap Rewrite Rules/i', $contents ) && ! aioseo()->core->cache->get( 'aioseo_sitemap_htaccess_rewrite_rules_remove' ) ) {
				aioseo()->core->cache->update( 'aioseo_sitemap_htaccess_rewrite_rules_remove', time(), HOUR_IN_SECONDS );

				$contents = preg_replace( "/$escapedRewriteRules/i", '', $contents );
				aioseo()->htaccess->saveContents( $contents );
			}

			return;
		}

		if ( preg_match( '/All in One SEO Sitemap Rewrite Rules/i', $contents ) || aioseo()->core->cache->get( 'aioseo_sitemap_htaccess_rewrite_rules_add' ) ) {
			return;
		}

		aioseo()->core->cache->update( 'aioseo_sitemap_htaccess_rewrite_rules_add', time(), HOUR_IN_SECONDS );

		$contents .= $rewriteRules;

		aioseo()->htaccess->saveContents( $contents );
	}

	/**
	 * Checks if static sitemap files prevent dynamic sitemap generation.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function detectStatic() {
		$isGeneralSitemapStatic = aioseo()->options->sitemap->general->advancedSettings->enable &&
			in_array( 'staticSitemap', aioseo()->internalOptions->internal->deprecatedOptions, true ) &&
			! aioseo()->options->deprecated->sitemap->general->advancedSettings->dynamic;

		if ( $isGeneralSitemapStatic ) {
			Models\Notification::deleteNotificationByName( 'sitemap-static-files' );

			return;
		}

		require_once ABSPATH . 'wp-admin/includes/file.php';
		$files = list_files( get_home_path(), 1 );
		if ( ! count( $files ) ) {
			return;
		}

		$detectedFiles = [];
		if ( ! $isGeneralSitemapStatic ) {
			foreach ( $files as $filename ) {
				if ( preg_match( '#.*sitemap.*#', $filename ) ) {
					// We don't want to delete the video sitemap here at all.
					$isVideoSitemap = preg_match( '#.*video.*#', $filename ) ? true : false;
					if ( ! $isVideoSitemap ) {
						$detectedFiles[] = $filename;
					}
				}
			}
		}

		$this->maybeShowStaticSitemapNotification( $detectedFiles );
	}

	/**
	 * If there are files, show a notice, otherwise delete it.
	 *
	 * @since 4.0.0
	 *
	 * @param  array $detectedFiles An array of detected files.
	 * @return void
	 */
	protected function maybeShowStaticSitemapNotification( $detectedFiles ) {
		if ( ! count( $detectedFiles ) ) {
			Models\Notification::deleteNotificationByName( 'sitemap-static-files' );

			return;
		}

		$notification = Models\Notification::getNotificationByName( 'sitemap-static-files' );
		if ( $notification->notification_name ) {
			return;
		}

		Models\Notification::addNotification( [
			'slug'              => uniqid(),
			'notification_name' => 'sitemap-static-files',
			'title'             => __( 'Static sitemap files detected', 'all-in-one-seo-pack' ),
			'content'           => sprintf(
				// Translators: 1 - The plugin short name ("AIOSEO"), 2 - Same as previous.
				__( '%1$s has detected static sitemap files in the root folder of your WordPress installation.
				As long as these files are present, %2$s is not able to dynamically generate your sitemap.', 'all-in-one-seo-pack' ),
				AIOSEO_PLUGIN_SHORT_NAME,
				AIOSEO_PLUGIN_SHORT_NAME
			),
			'type'              => 'error',
			'level'             => [ 'all' ],
			'button1_label'     => __( 'Delete Static Files', 'all-in-one-seo-pack' ),
			'button1_action'    => 'http://action#sitemap/delete-static-files',
			'start'             => gmdate( 'Y-m-d H:i:s' )
		] );
	}

	/**
	 * Regenerates the static sitemap files when a post is updated.
	 *
	 * @since 4.0.0
	 *
	 * @param  integer $postId The post ID.
	 * @return void
	 */
	public function regenerateOnUpdate( $postId ) {
		if ( aioseo()->helpers->isValidPost( $postId ) ) {
			$this->scheduleRegeneration();
		}
	}

	/**
	 * Schedules an action to regenerate the static sitemap files.
	 *
	 * @since 4.0.5
	 *
	 * @return void
	 */
	public function scheduleRegeneration() {
		try {
			if (
				! aioseo()->options->deprecated->sitemap->general->advancedSettings->dynamic &&
				! as_next_scheduled_action( 'aioseo_static_sitemap_regeneration' )
			) {
				as_schedule_single_action( time() + 60, 'aioseo_static_sitemap_regeneration', [], 'aioseo' );
			}
		} catch ( \Exception $e ) {
			// Do nothing.
		}
	}

	/**
	 * Regenerates the static sitemap files.
	 *
	 * @since 4.0.5
	 *
	 * @return void
	 */
	public function regenerateStaticSitemap() {
		aioseo()->sitemap->file->generate();
	}

	/**
	 * Generates the requested sitemap.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function generate() {
		if ( empty( $this->type ) ) {
			return;
		}

		// This is a hack to prevent WordPress from running it's default stuff during our processing.
		global $wp_query;
		$wp_query->is_home = false;

		// This prevents the sitemap from including terms twice when WPML is active.
		if ( class_exists( 'SitePress' ) ) {
			global $sitepress_settings;
			// Before building the sitemap make sure links aren't translated.
			// The setting should not be updated in the DB.
			$sitepress_settings['auto_adjust_ids'] = 0;
		}

		// If requested sitemap should be static and doesn't exist, then generate it.
		// We'll then serve it dynamically for the current request so that we don't serve a blank page.
		$this->doesFileExist();

		$options = aioseo()->options->noConflict();
		if ( ! $options->sitemap->{aioseo()->sitemap->type}->enable ) {
			$this->notFoundPage();

			return;
		}

		$entries = aioseo()->sitemap->content->get();
		$total   = aioseo()->sitemap->content->getTotal();
		if ( ! $entries ) {
			$addonsEntries = aioseo()->addons->doAddonFunction( 'content', 'get' );
			$addonTotals   = aioseo()->addons->doAddonFunction( 'content', 'getTotal' );
			foreach ( $addonsEntries as $addonSlug => $addonEntries ) {
				if ( ! empty( $addonEntries ) ) {
					$entries = $addonEntries;
					$total   = ! empty( $addonTotals[ $addonSlug ] ) ? $addonTotals[ $addonSlug ] : count( $entries );
					break;
				}
			}
		}

		if ( 0 === $total && empty( $entries ) ) {
			status_header( 404 );
		}

		$this->xsl->saveXslData(
			aioseo()->sitemap->requestParser->slug,
			$entries,
			$total
		);

		$this->headers();
		aioseo()->sitemap->output->output( $entries );
		aioseo()->addons->doAddonFunction( 'output', 'output', [ $entries ] );

		exit;
	}

	/**
	 * Checks if static file should be served and generates it if it doesn't exist.
	 *
	 * This essentially acts as a safety net in case a file doesn't exist yet or has been deleted.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	protected function doesFileExist() {
		aioseo()->addons->doAddonFunction( 'sitemap', 'doesFileExist' );

		if (
			'general' !== $this->type ||
			! aioseo()->options->sitemap->general->advancedSettings->enable ||
			! in_array( 'staticSitemap', aioseo()->internalOptions->internal->deprecatedOptions, true ) ||
			aioseo()->options->sitemap->general->advancedSettings->dynamic
		) {
			return;
		}

		require_once ABSPATH . 'wp-admin/includes/file.php';
		if ( ! aioseo()->core->fs->exists( get_home_path() . $_SERVER['REQUEST_URI'] ) ) {
			$this->scheduleRegeneration();
		}
	}

	/**
	 * Sets the HTTP headers for the sitemap.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function headers() {
		$charset = aioseo()->helpers->getCharset();
		header( "Content-Type: text/xml; charset=$charset", true );
		header( 'X-Robots-Tag: noindex, follow', true );
	}

	/**
	 * Redirects to a 404 Not Found page if the sitemap is disabled.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function notFoundPage() {
		global $wp_query;
		$wp_query->set_404();
		status_header( 404 );
		include get_404_template();
		exit;
	}

	/**
	 * Registers an active sitemap addon and its classes.
	 * NOTE: This is deprecated and only there for users who already were using the previous sitemap addons version.
	 *
	 * @final 4.2.7
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function addAddon() {}
}