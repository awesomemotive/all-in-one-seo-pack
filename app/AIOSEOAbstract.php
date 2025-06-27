<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Abstract class holding the class properties of our main AIOSEO class.
 *
 * @since 4.2.7
 */
abstract class AIOSEOAbstract {
	/**
	 * Core class instance.
	 *
	 * @since 4.2.7
	 *
	 * @var \AIOSEO\Plugin\Common\Core\Core
	 */
	public $core = null;

	/**
	 * Helpers class instance.
	 *
	 * @since 4.2.7
	 *
	 * @var \AIOSEO\Plugin\Lite\Utils\Helpers|\AIOSEO\Plugin\Pro\Utils\Helpers
	 */
	public $helpers = null;

	/**
	 * InternalNetworkOptions class instance.
	 *
	 * @since 4.2.7
	 *
	 * @var \AIOSEO\Plugin\Common\Options\InternalNetworkOptions|\AIOSEO\Plugin\Pro\Options\InternalNetworkOptions
	 */
	public $internalNetworkOptions = null;

	/**
	 * InternalOptions class instance.
	 *
	 * @since 4.2.7
	 *
	 * @var \AIOSEO\Plugin\Lite\Options\InternalOptions|\AIOSEO\Plugin\Pro\Options\InternalOptions
	 */
	public $internalOptions = null;

	/**
	 * PreUpdates class instance.
	 *
	 * @since 4.2.7
	 *
	 * @var \AIOSEO\Plugin\Common\Main\PreUpdates|\AIOSEO\Plugin\Pro\Main\PreUpdates
	 */
	public $preUpdates = null;

	/**
	 * Db class instance.
	 * This prop is set for backwards compatibility.
	 *
	 * @since 4.2.7
	 *
	 * @var \AIOSEO\Plugin\Common\Utils\Database
	 */
	public $db = null;

	/**
	 * Transients class instance.
	 * This prop is set for backwards compatibility.
	 *
	 * @since 4.2.7
	 *
	 * @var \AIOSEO\Plugin\Common\Utils\Cache
	 */
	public $transients = null;

	/**
	 * OptionsCache class instance.
	 * This prop is set for backwards compatibility.
	 *
	 * @since 4.2.7
	 *
	 * @var \AIOSEO\Plugin\Common\Options\Cache
	 */
	public $optionsCache = null;

	/**
	 * PostSettings class instance.
	 *
	 * @since 4.2.7
	 *
	 * @var \AIOSEO\Plugin\Lite\Admin\PostSettings|\AIOSEO\Plugin\Pro\Admin\PostSettings
	 */
	public $postSettings = null;

	/**
	 * Standalone class instance.
	 *
	 * @since 4.2.7
	 *
	 * @var \AIOSEO\Plugin\Common\Standalone\Standalone
	 */
	public $standalone = null;

	/**
	 * Search Statistics class instance.
	 *
	 * @since 4.3.0
	 *
	 * @var \AIOSEO\Plugin\Pro\SearchStatistics\SearchStatistics
	 */
	public $searchStatistics = null;

	/**
	 * Tags class instance.
	 *
	 * @since 4.2.7
	 *
	 * @var \AIOSEO\Plugin\Pro\Utils\Tags
	 */
	public $tags = null;

	/**
	 * Addons class instance.
	 *
	 * @since 4.2.7
	 *
	 * @var \AIOSEO\Plugin\Common\Utils\Blocks
	 */
	public $blocks = null;

	/**
	 * Breadcrumbs class instance.
	 *
	 * @since 4.2.7
	 *
	 * @var \AIOSEO\Plugin\Common\Breadcrumbs\Breadcrumbs|\AIOSEO\Plugin\Pro\Breadcrumbs\Breadcrumbs
	 */
	public $breadcrumbs = null;

	/**
	 * DynamicBackup class instance.
	 *
	 * @since 4.2.7
	 *
	 * @var \AIOSEO\Plugin\Common\Options\DynamicBackup|\AIOSEO\Plugin\Pro\Options\DynamicBackup
	 */
	public $dynamicBackup = null;

	/**
	 * NetworkOptions class instance.
	 *
	 * @since 4.2.7
	 *
	 * @var \AIOSEO\Plugin\Common\Options\NetworkOptions|\AIOSEO\Plugin\Pro\Options\NetworkOptions
	 */
	public $networkOptions = null;

	/**
	 * Backup class instance.
	 *
	 * @since 4.2.7
	 *
	 * @var \AIOSEO\Plugin\Common\Utils\Backup
	 */
	public $backup = null;

	/**
	 * Access class instance.
	 *
	 * @since 4.2.7
	 *
	 * @var \AIOSEO\Plugin\Common\Utils\Access|\AIOSEO\Plugin\Pro\Utils\Access
	 */
	public $access = null;

	/**
	 * NetworkLicense class instance.
	 *
	 * @since 4.2.7
	 *
	 * @var null|\AIOSEO\Plugin\Pro\Admin\NetworkLicense
	 */
	public $networkLicense = null;

	/**
	 * License class instance.
	 *
	 * @since 4.2.7
	 *
	 * @var null|\AIOSEO\Plugin\Pro\Admin\License
	 */
	public $license = null;

	/**
	 * Updates class isntance.
	 *
	 * @since 4.2.7
	 *
	 * @var \AIOSEO\Plugin\Common\Main\Updates|\AIOSEO\Plugin\Pro\Main\Updates
	 */
	public $updates = null;

	/**
	 * Meta class instance.
	 *
	 * @since 4.2.7
	 *
	 * @var \AIOSEO\Plugin\Pro\Meta\Meta
	 */
	public $meta = null;

	/**
	 * Social class instance.
	 *
	 * @since 4.2.7
	 *
	 * @var \AIOSEO\Plugin\Common\Social\Social|\AIOSEO\Plugin\Pro\Social\Social
	 */
	public $social = null;

	/**
	 * RobotsTxt class instance.
	 *
	 * @since 4.2.7
	 *
	 * @var \AIOSEO\Plugin\Common\Tools\RobotsTxt
	 */
	public $robotsTxt = null;

	/**
	 * Htaccess class instance.
	 *
	 * @since 4.2.7
	 *
	 * @var \AIOSEO\Plugin\Common\Tools\Htaccess
	 */
	public $htaccess = null;

	/**
	 * Term class instance.
	 *
	 * @since 4.2.7
	 *
	 * @var null|\AIOSEO\Plugin\Pro\Admin\Term
	 */
	public $term = null;

	/**
	 * Notices class instance.
	 *
	 * @since 4.2.7
	 *
	 * @var \AIOSEO\Plugin\Lite\Admin\Notices\Notices|\AIOSEO\Plugin\Pro\Admin\Notices\Notices
	 */
	public $notices = null;

	/**
	 * WpNotices class instance.
	 *
	 * @since 4.2.7
	 *
	 * @var \AIOSEO\Plugin\Common\Admin\Notices\WpNotices
	 */
	public $wpNotices = null;

	/**
	 * Admin class instance.
	 *
	 * @since 4.2.7
	 *
	 * @var \AIOSEO\Plugin\Lite\Admin\Admin|\AIOSEO\Plugin\Pro\Admin\Admin
	 */
	public $admin = null;

	/**
	 * NetworkAdmin class instance.
	 *
	 * @since 4.2.7
	 *
	 * @var \AIOSEO\Plugin\Common\Admin\NetworkAdmin|\AIOSEO\Plugin\Pro\Admin\NetworkAdmin
	 */
	public $networkAdmin = null;

	/**
	 * Activate class instance.
	 *
	 * @since 4.2.7
	 *
	 * @var \AIOSEO\Plugin\Common\Main\Activate|\AIOSEO\Plugin\Pro\Main\Activate
	 */
	public $activate = null;

	/**
	 * ConflictingPlugins class instance.
	 *
	 * @since 4.2.7
	 *
	 * @var \AIOSEO\Plugin\Common\Admin\ConflictingPlugins|\AIOSEO\Plugin\Pro\Admin\ConflictingPlugins
	 */
	public $conflictingPlugins = null;

	/**
	 * Migration class instance.
	 *
	 * @since 4.2.7
	 *
	 * @var \AIOSEO\Plugin\Pro\Migration\Migration
	 */
	public $migration = null;

	/**
	 * ImportExport class instance.
	 *
	 * @since 4.2.7
	 *
	 * @var \AIOSEO\Plugin\Common\ImportExport\ImportExport
	 */
	public $importExport = null;

	/**
	 * Sitemap class instance.
	 *
	 * @since 4.2.7
	 *
	 * @var \AIOSEO\Plugin\Common\Sitemap\Sitemap|\AIOSEO\Plugin\Pro\Sitemap\Sitemap
	 */
	public $sitemap = null;

	/**
	 * HtmlSitemap class instance.
	 *
	 * @since 4.2.7
	 *
	 * @var \AIOSEO\Plugin\Common\Sitemap\Html\Sitemap
	 */
	public $htmlSitemap = null;

	/**
	 * CategoryBase class instance.
	 *
	 * @since   4.2.7
	 * @version 4.7.1 Moved from Pro to Common.
	 *
	 * @var null|\AIOSEO\Plugin\Common\Main\CategoryBase
	 */
	public $categoryBase = null;

	/**
	 * SlugMonitor class instance.
	 *
	 * @since 4.2.7
	 *
	 * @var \AIOSEO\Plugin\Common\Admin\SlugMonitor
	 */
	public $slugMonitor = null;

	/**
	 * Schema class instance.
	 *
	 * @since 4.2.7
	 *
	 * @var \AIOSEO\Plugin\Pro\Schema\Schema
	 */
	public $schema = null;

	/**
	 * Rss class instance.
	 *
	 * @since 4.2.7
	 *
	 * @var \AIOSEO\Plugin\Common\Rss
	 */
	public $rss = null;

	/**
	 * Main class instance.
	 *
	 * @since 4.2.7
	 *
	 * @var \AIOSEO\Plugin\Common\Main\Main|\AIOSEO\Plugin\Pro\Main\Main
	 */
	public $main = null;

	/**
	 * Head class instance.
	 *
	 * @since 4.2.7
	 *
	 * @var \AIOSEO\Plugin\Common\Main\Head|\AIOSEO\Plugin\Pro\Main\Head
	 */
	public $head = null;

	/**
	 * Dashboard class instance.
	 *
	 * @since 4.2.7
	 *
	 * @var \AIOSEO\Plugin\Common\Admin\Dashboard|\AIOSEO\Plugin\Pro\Admin\Dashboard
	 */
	public $dashboard = null;

	/**
	 * API class instance.
	 *
	 * @since 4.2.7
	 *
	 * @var \AIOSEO\Plugin\Lite\Api\Api|\AIOSEO\Plugin\Pro\Api\Api
	 */
	public $api = null;

	/**
	 * Help class instance.
	 *
	 * @since 4.2.7
	 *
	 * @var \AIOSEO\Plugin\Common\Help\Help
	 */
	public $help = null;

	/**
	 * Settings class instance.
	 *
	 * @since 4.2.7
	 *
	 * @var \AIOSEO\Plugin\Common\Utils\VueSettings
	 */
	public $settings = null;

	/**
	 * Cache class instance.
	 *
	 * @since 4.2.7
	 *
	 * @var \AIOSEO\Plugin\Common\Utils\Cache
	 */
	public $cache = null;

	/**
	 * CachePrune class instance.
	 *
	 * @since 4.2.7
	 *
	 * @var \AIOSEO\Plugin\Common\Utils\CachePrune
	 */
	public $cachePrune = null;

	/**
	 * Addons class instance.
	 *
	 * @since 4.2.7
	 *
	 * @var \AIOSEO\Plugin\Pro\Utils\Addons|\AIOSEO\Plugin\Common\Utils\Addons
	 */
	public $addons = null;

	/**
	 * Addons class instance.
	 *
	 * @since 4.3.0
	 *
	 * @var \AIOSEO\Plugin\Common\Utils\Features|\AIOSEO\Plugin\Pro\Utils\Features
	 */
	public $features = null;

	/**
	 * Options class instance.
	 *
	 * @since 4.2.7
	 *
	 * @var \AIOSEO\Plugin\Common\Options\Options|\AIOSEO\Plugin\Pro\Options\Options
	 */
	public $options = null;

	/**
	 * DynamicOptions class instance.
	 *
	 * @since 4.2.7
	 *
	 * @var \AIOSEO\Plugin\Common\Options\DynamicOptions|\AIOSEO\Plugin\Pro\Options\DynamicOptions
	 */
	public $dynamicOptions = null;

	/**
	 * Usage class instance.
	 *
	 * @since 4.2.7
	 *
	 * @var \AIOSEO\Plugin\Lite\Admin\Usage|\AIOSEO\Plugin\Pro\Admin\Usage
	 */
	public $usage = null;

	/**
	 * SiteHealth class instance.
	 *
	 * @since 4.2.7
	 *
	 * @var \AIOSEO\Plugin\Common\Admin\SiteHealth|\AIOSEO\Plugin\Pro\Admin\SiteHealth
	 */
	public $siteHealth = null;

	/**
	 * AutoUpdates class instance.
	 *
	 * @since 4.2.7
	 *
	 * @var \AIOSEO\Plugin\Pro\Admin\AutoUpdates
	 */
	public $autoUpdates = null;

	/**
	 * Templates class instance.
	 *
	 * @since 4.2.7
	 *
	 * @var \AIOSEO\Plugin\Common\Utils\Templates|\AIOSEO\Plugin\Pro\Utils\Templates
	 */
	public $templates = null;

	/**
	 * Filters class instance.
	 *
	 * @since 4.2.7
	 *
	 * @var \AIOSEO\Plugin\Lite\Main\Filters|\AIOSEO\Plugin\Pro\Main\Filters
	 */
	public $filters = null;

	/**
	 * ActionScheduler class instance.
	 *
	 * @since 4.2.7
	 *
	 * @var \AIOSEO\Plugin\Common\Utils\ActionScheduler
	 */
	public $actionScheduler = null;

	/**
	 * AI class instance.
	 *
	 * @since 4.3.3
	 *
	 * @var null|\AIOSEO\Plugin\Pro\Ai\Ai
	 */
	public $ai = null;

	/**
	 * SeoRevisions class instance.
	 *
	 * @since 4.4.0
	 *
	 * @var null|\AIOSEO\Plugin\Pro\SeoRevisions\SeoRevisions
	 */
	public $seoRevisions = null;

	/**
	 * Crawl Cleanup class instance.
	 *
	 * @since 4.5.8
	 *
	 * @var \AIOSEO\Plugin\Common\QueryArgs\CrawlCleanup
	 */
	public $crawlCleanup = null;

	/**
	 * Search Cleanup class instance.
	 *
	 * @since 4.8.0
	 *
	 * @var \AIOSEO\Plugin\Common\SearchCleanup\SearchCleanup
	 */
	public $searchCleanup = null;

	/**
	 * EmailReports class instance.
	 *
	 * @since 4.7.2
	 *
	 * @var null|\AIOSEO\Plugin\Common\EmailReports\EmailReports
	 */
	public $emailReports = null;

	/**
	 * ThirdParty class instance.
	 *
	 * @since 4.7.6
	 *
	 * @var \AIOSEO\Plugin\Common\ThirdParty\ThirdParty
	 */
	public $thirdParty = null;

	/**
	 * WritingAssistant class instance.
	 *
	 * @since 4.7.4
	 *
	 * @var \AIOSEO\Plugin\Common\WritingAssistant\WritingAssistant
	 */
	public $writingAssistant = null;

	/**
	 * Llms class instance.
	 *
	 * @since 4.8.4
	 *
	 * @var \AIOSEO\Plugin\Common\Llms\Llms
	 */
	public $llms = null;
}