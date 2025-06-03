<?php
namespace AIOSEO\Plugin {
	// Exit if accessed directly.
	if ( ! defined( 'ABSPATH' ) ) {
		exit;
	}

	/**
	 * Main AIOSEO class.
	 * We extend the abstract class as that one holds all the class properties.
	 *
	 * @since 4.0.0
	 */
	final class AIOSEO extends \AIOSEOAbstract {

		/**
		 * Holds the instance of the plugin currently in use.
		 *
		 * @since 4.0.0
		 *
		 * @var AIOSEO
		 */
		private static $instance;

		/**
		 * Plugin version for enqueueing, etc.
		 * The value is retrieved from the AIOSEO_VERSION constant.
		 *
		 * @since 4.0.0
		 *
		 * @var string
		 */
		public $version = '';

		/**
		 * Paid returns true, free (Lite) returns false.
		 *
		 * @since 4.0.0
		 *
		 * @var boolean
		 */
		public $pro = false;

		/**
		 * Returns 'Pro' or 'Lite'.
		 *
		 * @since 4.0.0
		 *
		 * @var boolean
		 */
		public $versionPath = 'Lite';

		/**
		 * Whether we're in a dev environment.
		 *
		 * @since 4.1.9
		 *
		 * @var bool
		 */
		public $isDev = false;

		/**
		 * Uninstall class instance.
		 *
		 * @since 4.8.1
		 *
		 * @var Common\Main\Uninstall
		 */
		public $uninstall = null;

		/**
		 * Main AIOSEO Instance.
		 *
		 * Insures that only one instance of AIOSEO exists in memory at any one
		 * time. Also prevents needing to define globals all over the place.
		 *
		 * @since 4.0.0
		 *
		 * @return AIOSEO The aioseo instance.
		 */
		public static function instance() {
			if ( null === self::$instance || ! self::$instance instanceof self ) {
				self::$instance = new self();

				self::$instance->init();

				// Load our addons on the action right after plugins_loaded.
				add_action( 'sanitize_comment_cookies', [ self::$instance, 'loadAddons' ] );
			}

			return self::$instance;
		}

		/**
		 * Initialize All in One SEO!
		 *
		 * @since 4.0.0
		 *
		 * @return void
		 */
		private function init() {
			$this->constants();
			$this->includes();
			$this->preLoad();
			if ( ! $this->core->isUninstalling() ) {
				$this->load();
			}
		}

		/**
		 * Setup plugin constants.
		 * All the path/URL related constants are defined in main plugin file.
		 *
		 * @since 4.0.0
		 *
		 * @return void
		 */
		private function constants() {
			$defaultHeaders = [
				'name'    => 'Plugin Name',
				'version' => 'Version',
			];

			$pluginData = get_file_data( AIOSEO_FILE, $defaultHeaders );

			$constants = [
				'AIOSEO_PLUGIN_BASENAME'   => plugin_basename( AIOSEO_FILE ),
				'AIOSEO_PLUGIN_NAME'       => $pluginData['name'],
				'AIOSEO_PLUGIN_SHORT_NAME' => 'AIOSEO',
				'AIOSEO_PLUGIN_URL'        => plugin_dir_url( AIOSEO_FILE ),
				'AIOSEO_VERSION'           => $pluginData['version'],
				'AIOSEO_MARKETING_URL'     => 'https://aioseo.com/',
				'AIOSEO_MARKETING_DOMAIN'  => 'aioseo.com'
			];

			foreach ( $constants as $constant => $value ) {
				if ( ! defined( $constant ) ) {
					define( $constant, $value );
				}
			}

			$this->version = AIOSEO_VERSION;
		}

		/**
		 * Including the new files with PHP 5.3 style.
		 *
		 * @since 4.0.0
		 *
		 * @return void
		 */
		private function includes() {
			$dependencies = [
				'/vendor/autoload.php'                                      => true,
				'/vendor/woocommerce/action-scheduler/action-scheduler.php' => true,
				'/vendor/jwhennessey/phpinsight/autoload.php'               => false,
				'/vendor_prefixed/monolog/monolog/src/Monolog/Logger.php'   => false
			];

			foreach ( $dependencies as $path => $shouldRequire ) {
				if ( ! file_exists( AIOSEO_DIR . $path ) ) {
					// Something is not right.
					status_header( 500 );
					wp_die( esc_html__( 'Plugin is missing required dependencies. Please contact support for more information.', 'all-in-one-seo-pack' ) );
				}

				if ( $shouldRequire ) {
					require_once AIOSEO_DIR . $path;
				}
			}

			$this->loadVersion();
		}

		/**
		 * Load the version of the plugin we are currently using.
		 *
		 * @since 4.1.9
		 *
		 * @return void
		 */
		private function loadVersion() {
			$proDir = is_dir( plugin_dir_path( AIOSEO_FILE ) . 'app/Pro' );

			if (
				! class_exists( '\Dotenv\Dotenv' ) ||
				! file_exists( AIOSEO_DIR . '/build/.env' )
			) {
				$this->pro         = $proDir;
				$this->versionPath = $proDir ? 'Pro' : 'Lite';

				return;
			}

			$dotenv = \Dotenv\Dotenv::createUnsafeImmutable( AIOSEO_DIR, '/build/.env' );
			$dotenv->load();

			$version = defined( 'AIOSEO_DEV_VERSION' )
				? strtolower( AIOSEO_DEV_VERSION )
				: strtolower( getenv( 'VITE_VERSION' ) );
			if ( ! empty( $version ) ) {
				$this->isDev = true;

				if ( file_exists( AIOSEO_DIR . '/build/filters.php' ) ) {
					require_once AIOSEO_DIR . '/build/filters.php';
				}
			}

			if ( $proDir && 'pro' === $version ) {
				$this->pro         = true;
				$this->versionPath = 'Pro';
			}
		}

		/**
		 * Runs before we load the plugin.
		 *
		 * @since 4.0.0
		 *
		 * @return void
		 */
		private function preLoad() {
			$this->core = new Common\Core\Core();

			$this->backwardsCompatibility();

			// Internal Options.
			$this->helpers                = $this->pro ? new Pro\Utils\Helpers() : new Lite\Utils\Helpers();
			$this->internalNetworkOptions = ( $this->pro && $this->helpers->isPluginNetworkActivated() ) ? new Pro\Options\InternalNetworkOptions() : new Common\Options\InternalNetworkOptions();
			$this->internalOptions        = $this->pro ? new Pro\Options\InternalOptions() : new Lite\Options\InternalOptions();
			$this->uninstall              = new Common\Main\Uninstall();

			// Run pre-updates.
			$this->preUpdates = $this->pro ? new Pro\Main\PreUpdates() : new Common\Main\PreUpdates();
		}

		/**
		 * To prevent errors and bugs from popping up,
		 * we will run this backwards compatibility method.
		 *
		 * @since 4.1.9
		 *
		 * @return void
		 */
		private function backwardsCompatibility() {
			$this->db           = $this->core->db;
			$this->cache        = $this->core->cache;
			$this->transients   = $this->cache;
			$this->cachePrune   = $this->core->cachePrune;
			$this->optionsCache = $this->core->optionsCache;
		}

		/**
		 * To prevent errors and bugs from popping up,
		 * we will run this backwards compatibility method.
		 *
		 * @since 4.2.0
		 *
		 * @return void
		 */
		private function backwardsCompatibilityLoad() {
			$this->postSettings->integrations = $this->standalone->pageBuilderIntegrations;
		}

		/**
		 * Load our classes.
		 *
		 * @since 4.0.0
		 *
		 * @return void
		 */
		public function load() {
			// Load external translations if this is a Pro install.
			if ( $this->pro ) {
				$translations = new Pro\Main\Translations(
					'plugin',
					'all-in-one-seo-pack',
					'https://aioseo.com/aioseo-plugin/all-in-one-seo-pack/packages.json'
				);
				$translations->init();

				$translations = new Pro\Main\Translations(
					'plugin',
					'aioseo-pro',
					'https://aioseo.com/aioseo-plugin/aioseo-pro/packages.json'
				);
				$translations->init();
			}

			$this->addons             = $this->pro ? new Pro\Utils\Addons() : new Common\Utils\Addons();
			$this->features           = $this->pro ? new Pro\Utils\Features() : new Common\Utils\Features();
			$this->tags               = $this->pro ? new Pro\Utils\Tags() : new Common\Utils\Tags();
			$this->blocks             = new Common\Utils\Blocks();
			$this->breadcrumbs        = $this->pro ? new Pro\Breadcrumbs\Breadcrumbs() : new Common\Breadcrumbs\Breadcrumbs();
			$this->dynamicBackup      = $this->pro ? new Pro\Options\DynamicBackup() : new Common\Options\DynamicBackup();
			$this->options            = $this->pro ? new Pro\Options\Options() : new Lite\Options\Options();
			$this->networkOptions     = ( $this->pro && $this->helpers->isPluginNetworkActivated() ) ? new Pro\Options\NetworkOptions() : new Common\Options\NetworkOptions();
			$this->dynamicOptions     = $this->pro ? new Pro\Options\DynamicOptions() : new Common\Options\DynamicOptions();
			$this->backup             = new Common\Utils\Backup();
			$this->access             = $this->pro ? new Pro\Utils\Access() : new Common\Utils\Access();
			$this->usage              = $this->pro ? new Pro\Admin\Usage() : new Lite\Admin\Usage();
			$this->siteHealth         = $this->pro ? new Pro\Admin\SiteHealth() : new Common\Admin\SiteHealth();
			$this->networkLicense     = $this->pro && $this->helpers->isPluginNetworkActivated() ? new Pro\Admin\NetworkLicense() : null;
			$this->license            = $this->pro ? new Pro\Admin\License() : null;
			$this->autoUpdates        = $this->pro ? new Pro\Admin\AutoUpdates() : null;
			$this->updates            = $this->pro ? new Pro\Main\Updates() : new Common\Main\Updates();
			$this->meta               = $this->pro ? new Pro\Meta\Meta() : new Common\Meta\Meta();
			$this->social             = $this->pro ? new Pro\Social\Social() : new Common\Social\Social();
			$this->robotsTxt          = new Common\Tools\RobotsTxt();
			$this->htaccess           = new Common\Tools\Htaccess();
			$this->term               = $this->pro ? new Pro\Admin\Term() : null;
			$this->notices            = $this->pro ? new Pro\Admin\Notices\Notices() : new Lite\Admin\Notices\Notices();
			$this->wpNotices          = new Common\Admin\Notices\WpNotices();
			$this->admin              = $this->pro ? new Pro\Admin\Admin() : new Lite\Admin\Admin();
			$this->networkAdmin       = $this->helpers->isPluginNetworkActivated() ? ( $this->pro ? new Pro\Admin\NetworkAdmin() : new Common\Admin\NetworkAdmin() ) : null;
			$this->activate           = $this->pro ? new Pro\Main\Activate() : new Common\Main\Activate();
			$this->conflictingPlugins = $this->pro ? new Pro\Admin\ConflictingPlugins() : new Common\Admin\ConflictingPlugins();
			$this->migration          = $this->pro ? new Pro\Migration\Migration() : new Common\Migration\Migration();
			$this->importExport       = $this->pro ? new Pro\ImportExport\ImportExport() : new Common\ImportExport\ImportExport();
			$this->sitemap            = $this->pro ? new Pro\Sitemap\Sitemap() : new Common\Sitemap\Sitemap();
			$this->htmlSitemap        = new Common\Sitemap\Html\Sitemap();
			$this->templates          = $this->pro ? new Pro\Utils\Templates() : new Common\Utils\Templates();
			$this->categoryBase       = new Common\Main\CategoryBase();
			$this->postSettings       = $this->pro ? new Pro\Admin\PostSettings() : new Lite\Admin\PostSettings();
			$this->standalone         = new Common\Standalone\Standalone();
			$this->searchStatistics   = $this->pro ? new Pro\SearchStatistics\SearchStatistics() : new Common\SearchStatistics\SearchStatistics();
			$this->slugMonitor        = new Common\Admin\SlugMonitor();
			$this->schema             = $this->pro ? new Pro\Schema\Schema() : new Common\Schema\Schema();
			$this->actionScheduler    = new Common\Utils\ActionScheduler();
			$this->seoRevisions       = $this->pro ? new Pro\SeoRevisions\SeoRevisions() : new Common\SeoRevisions\SeoRevisions();
			$this->ai                 = $this->pro ? new Pro\Ai\Ai() : null;
			$this->filters            = $this->pro ? new Pro\Main\Filters() : new Lite\Main\Filters();
			$this->crawlCleanup       = new Common\QueryArgs\CrawlCleanup();
			$this->searchCleanup      = new Common\SearchCleanup\SearchCleanup();
			$this->emailReports       = new Common\EmailReports\EmailReports();
			$this->thirdParty         = new Common\ThirdParty\ThirdParty();
			$this->writingAssistant   = new Common\WritingAssistant\WritingAssistant();

			if ( ! wp_doing_ajax() && ! wp_doing_cron() ) {
				$this->rss       = new Common\Rss();
				$this->main      = $this->pro ? new Pro\Main\Main() : new Common\Main\Main();
				$this->head      = $this->pro ? new Pro\Main\Head() : new Common\Main\Head();
				$this->dashboard = $this->pro ? new Pro\Admin\Dashboard() : new Common\Admin\Dashboard();
				$this->api       = $this->pro ? new Pro\Api\Api() : new Lite\Api\Api();
				$this->help      = new Common\Help\Help();
			}

			$this->backwardsCompatibilityLoad();

			add_action( 'init', [ $this, 'loadInit' ], 999 );
		}

		/**
		 * Things that need to load after init.
		 *
		 * @since 4.0.0
		 *
		 * @return void
		 */
		public function loadInit() {
			$this->settings = new Common\Utils\VueSettings( '_aioseo_settings' );
			$this->sitemap->init();

			// We call this again to reset any post types/taxonomies that have not yet been set up.
			$this->dynamicOptions->refresh();
		}

		/**
		 * Loads our addons.
		 *
		 * Runs right after the plugins_loaded hook.
		 *
		 * @since 4.0.0
		 *
		 * @return void
		 */
		public function loadAddons() {
			do_action( 'aioseo_loaded' );
		}
	}
}

namespace {
	// Exit if accessed directly.
	if ( ! defined( 'ABSPATH' ) ) {
		exit;
	}

	/**
	 * The function which returns the one AIOSEO instance.
	 *
	 * @since 4.0.0
	 *
	 * @return AIOSEO\Plugin\AIOSEO The instance.
	 */
	function aioseo() {
		return AIOSEO\Plugin\AIOSEO::instance();
	}
}