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
		 * Set to true - independent plugin with all features enabled.
		 * Note: We use Lite directory structure but enable Pro features.
		 *
		 * @since 4.0.0
		 *
		 * @var boolean
		 */
		public $pro = true;

		/**
		 * Returns 'Pro' or 'Lite'.
		 * Set to 'Lite' - we use Lite directory structure with Pro features enabled.
		 *
		 * @since 4.0.0
		 *
		 * @var string
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
		 * Modified for independent plugin: Always enable Pro features with Lite directory structure.
		 *
		 * @since 4.1.9
		 *
		 * @return void
		 */
		private function loadVersion() {
			// Independent plugin: Always enable Pro features with Lite directory structure
			$this->pro         = true;
			$this->versionPath = 'Lite';

			// Load dev environment if available
			if (
				class_exists( '\Dotenv\Dotenv' ) &&
				file_exists( AIOSEO_DIR . '/build/.env' )
			) {
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

			// Internal Options - Always use Lite classes (independent plugin with Pro features)
			$this->helpers                = new Lite\Utils\Helpers();
			$this->internalNetworkOptions = new Common\Options\InternalNetworkOptions();
			$this->internalOptions        = new Lite\Options\InternalOptions();
			$this->uninstall              = new Common\Main\Uninstall();

			// Run pre-updates - Always use Common (independent plugin)
			$this->preUpdates = new Common\Main\PreUpdates();
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
		// Disabled: Pro translations - independent plugin
		// External translations loading removed as we don't connect to AIOSEO servers

		// Always use Lite/Common classes - independent plugin with Pro features enabled
		$this->addons             = new Common\Utils\Addons();
		$this->features           = new Common\Utils\Features();
		$this->tags               = new Common\Utils\Tags();
		$this->blocks             = new Common\Utils\Blocks();
		$this->badBotBlocker      = new Common\Tools\BadBotBlocker();
		$this->breadcrumbs        = new Common\Breadcrumbs\Breadcrumbs();
		$this->dynamicBackup      = new Common\Options\DynamicBackup();
		$this->options            = new Lite\Options\Options();
		$this->networkOptions     = new Common\Options\NetworkOptions();
		$this->dynamicOptions     = new Common\Options\DynamicOptions();
		$this->backup             = new Common\Utils\Backup();
		$this->access             = new Common\Utils\Access();
		$this->usage              = new Lite\Admin\Usage();
		$this->siteHealth         = new Common\Admin\SiteHealth();
		$this->networkLicense     = null; // Disabled - no licensing needed
		$this->license            = null; // Disabled - no licensing needed
		$this->autoUpdates        = null; // Disabled - independent plugin
		$this->updates            = new Common\Main\Updates();
		$this->meta               = new Common\Meta\Meta();
		$this->social             = new Common\Social\Social();
		$this->robotsTxt          = new Common\Tools\RobotsTxt();
		$this->htaccess           = new Common\Tools\Htaccess();
		$this->term               = null; // Pro-only feature not needed
		$this->notices            = new Lite\Admin\Notices\Notices();
		$this->wpNotices          = new Common\Admin\Notices\WpNotices();
		$this->admin              = new Lite\Admin\Admin();
		$this->networkAdmin       = $this->helpers->isPluginNetworkActivated() ? new Common\Admin\NetworkAdmin() : null;
		$this->activate           = new Common\Main\Activate();
		$this->conflictingPlugins = new Common\Admin\ConflictingPlugins();
		$this->migration          = new Common\Migration\Migration();
		$this->importExport       = new Common\ImportExport\ImportExport();
		$this->sitemap            = new Common\Sitemap\Sitemap();
		$this->htmlSitemap        = new Common\Sitemap\Html\Sitemap();
		$this->templates          = new Common\Utils\Templates();
		$this->categoryBase       = new Common\Main\CategoryBase();
		$this->postSettings       = new Lite\Admin\PostSettings();
		$this->standalone         = new Common\Standalone\Standalone();
		$this->searchStatistics   = new Common\SearchStatistics\SearchStatistics();
		$this->slugMonitor        = new Common\Admin\SlugMonitor();
		$this->schema             = new Common\Schema\Schema();
		$this->actionScheduler    = new Common\Utils\ActionScheduler();
		$this->seoRevisions       = new Common\SeoRevisions\SeoRevisions();
		$this->ai                 = null; // Pro Ai not needed - we use aiManager instead
		$this->aiManager          = new Common\Ai\AiManager();
		$this->filters            = new Lite\Main\Filters();
		$this->crawlCleanup       = new Common\QueryArgs\CrawlCleanup();
		$this->searchCleanup      = new Common\SearchCleanup\SearchCleanup();
		$this->emailReports       = new Common\EmailReports\EmailReports();
		$this->thirdParty         = new Common\ThirdParty\ThirdParty();
		$this->writingAssistant   = new Common\WritingAssistant\WritingAssistant();
		$this->elementorAi        = new Common\Integrations\Elementor();

		if ( ! wp_doing_ajax() && ! wp_doing_cron() ) {
			$this->rss       = new Common\Rss();
			$this->main      = new Common\Main\Main();
			$this->head      = new Common\Main\Head();
			$this->dashboard = new Common\Admin\Dashboard();
			$this->api       = new Lite\Api\Api();
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

			$this->badBotBlocker->init();

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