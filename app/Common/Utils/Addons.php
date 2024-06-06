<?php
namespace AIOSEO\Plugin\Common\Utils;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AIOSEO\Plugin\Common\Utils;

/**
 * Contains helper methods specific to the addons.
 *
 * @since 4.0.0
 */
class Addons {
	/**
	 * Holds our list of loaded addons.
	 *
	 * @since 4.1.0
	 *
	 * @var array
	 */
	protected $loadedAddons = [];

	/**
	 * The addons URL.
	 *
	 * @since 4.1.8
	 *
	 * @var string
	 */
	protected $addonsUrl = 'https://licensing-cdn.aioseo.com/keys/lite/all-in-one-seo-pack-pro.json';

	/**
	 * The main Image SEO addon class.
	 *
	 * @since 4.4.2
	 *
	 * @var \AIOSEO\Plugin\Addon\ImageSeo\ImageSeo
	 */
	private $imageSeo = null;

	/**
	 * The main Index Now addon class.
	 *
	 * @since 4.4.2
	 *
	 * @var \AIOSEO\Plugin\Addon\IndexNow\IndexNow
	 */
	private $indexNow = null;

	/**
	 * The main Local Business addon class.
	 *
	 * @since 4.4.2
	 *
	 * @var \AIOSEO\Plugin\Addon\LocalBusiness\LocalBusiness
	 */
	private $localBusiness = null;

	/**
	 * The main News Sitemap addon class.
	 *
	 * @since 4.4.2
	 *
	 * @var \AIOSEO\Plugin\Addon\NewsSitemap\NewsSitemap
	 */
	private $newsSitemap = null;

	/**
	 * The main Redirects addon class.
	 *
	 * @since 4.4.2
	 *
	 * @var \AIOSEO\Plugin\Addon\Redirects\Redirects
	 */
	private $redirects = null;

	/**
	 * The main REST API addon class.
	 *
	 * @since 4.4.2
	 *
	 * @var \AIOSEO\Plugin\Addon\RestApi\RestApi
	 */
	private $restApi = null;

	/**
	 * The main Video Sitemap addon class.
	 *
	 * @since 4.4.2
	 *
	 * @var \AIOSEO\Plugin\Addon\VideoSitemap\VideoSitemap
	 */
	private $videoSitemap = null;

	/**
	 * The main Link Assistant addon class.
	 *
	 * @since 4.4.2
	 *
	 * @var \AIOSEO\Plugin\Addon\LinkAssistant\LinkAssistant
	 */
	private $linkAssistant = null;

	/**
	 * The main EEAT addon class.
	 *
	 * @since 4.5.4
	 *
	 * @var \AIOSEO\Plugin\Addon\LinkAssistant\LinkAssistant
	 */
	private $eeat = null;

	/**
	 * Returns our addons.
	 *
	 * @since 4.0.0
	 *
	 * @param  boolean $flushCache Whether or not to flush the cache.
	 * @return array               An array of addon data.
	 */
	public function getAddons( $flushCache = false ) {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';

		$addons = aioseo()->core->cache->get( 'addons' );
		if ( null === $addons || $flushCache ) {
			$response = aioseo()->helpers->wpRemoteGet( $this->getAddonsUrl() );
			if ( 200 === wp_remote_retrieve_response_code( $response ) ) {
				$addons = json_decode( wp_remote_retrieve_body( $response ) );
			}

			if ( ! $addons || ! empty( $addons->error ) ) {
				$addons = $this->getDefaultAddons();
			}

			aioseo()->core->cache->update( 'addons', $addons );
		}

		$installedPlugins = array_keys( get_plugins() );
		foreach ( $addons as $key => $addon ) {
			if ( ! is_object( $addon ) ) {
				continue;
			}

			$addons[ $key ]->basename          = $this->getAddonBasename( $addon->sku );
			$addons[ $key ]->installed         = in_array( $this->getAddonBasename( $addon->sku ), $installedPlugins, true );
			$addons[ $key ]->isActive          = is_plugin_active( $addons[ $key ]->basename );
			$addons[ $key ]->canInstall        = $this->canInstall();
			$addons[ $key ]->canActivate       = $this->canActivate();
			$addons[ $key ]->canUpdate         = $this->canUpdate();
			$addons[ $key ]->capability        = $this->getManageCapability( $addon->sku );
			$addons[ $key ]->minimumVersion    = '0.0.0';
			$addons[ $key ]->hasMinimumVersion = false;
		}

		return $addons;
	}

	/**
	 * Returns the required capability to manage the addon.
	 *
	 * @since 4.1.3
	 *
	 * @param  string $sku The addon sku.
	 * @return string      The required capability.
	 */
	protected function getManageCapability( $sku ) {
		$capability = apply_filters( 'aioseo_manage_seo', 'aioseo_manage_seo' );

		switch ( $sku ) {
			case 'aioseo-image-seo':
				$capability = 'aioseo_search_appearance_settings';
				break;
			case 'aioseo-video-sitemap':
			case 'aioseo-news-sitemap':
				$capability = 'aioseo_sitemap_settings';
				break;
			case 'aioseo-redirects':
				$capability = 'aioseo_redirects_settings';
				break;
			case 'aioseo-local-business':
				$capability = 'aioseo_local_seo_settings';
				break;
			case 'aioseo-index-now':
				$capability = 'aioseo_general_settings';
				break;
		}

		return $capability;
	}

	/**
	 * Check to see if there are unlicensed addons installed and activated.
	 *
	 * @since 4.1.3
	 *
	 * @return boolean True if there are unlicensed addons, false if not.
	 */
	public function unlicensedAddons() {
		$unlicensed = [
			'addons'  => [],
			// Translators: 1 - Opening bold tag, 2 - Plugin short name ("AIOSEO"), 3 - "Pro", 4 - Closing bold tag.
			'message' => sprintf(
				// Translators: 1 - Opening HTML strong tag, 2 - The short plugin name ("AIOSEO"), 3 - "Pro", 4 - Closing HTML strong tag.
				__( 'The following addons cannot be used, because they require %1$s%2$s %3$s%4$s to work:', 'all-in-one-seo-pack' ),
				'<strong>',
				AIOSEO_PLUGIN_SHORT_NAME,
				'Pro',
				'</strong>'
			)
		];

		$addons = $this->getAddons();
		foreach ( $addons as $addon ) {
			if ( ! is_object( $addon ) ) {
				continue;
			}

			if ( $addon->isActive ) {
				$unlicensed['addons'][] = $addon;
			}
		}

		return $unlicensed;
	}

	/**
	 * Get the data for a specific addon.
	 *
	 * We need this function to refresh the data of a given addon because installation links expire after one hour.
	 *
	 * @since 4.0.0
	 *
	 * @param  string      $sku        The addon sku.
	 * @param  bool        $flushCache Whether or not to flush the cache.
	 * @return null|object             The addon.
	 */
	public function getAddon( $sku, $flushCache = false ) {
		$addon     = null;
		$allAddons = $this->getAddons( $flushCache );
		foreach ( $allAddons as $a ) {
			if ( $sku === $a->sku ) {
				$addon = $a;
			}
		}

		if ( ! $addon || ! empty( $addon->error ) ) {
			$addon = $this->getDefaultAddon( $sku );
			aioseo()->core->cache->update( 'addon_' . $sku, $addon, 10 * MINUTE_IN_SECONDS );
		}

		return $addon;
	}

	/**
	 * Checks if the specified addon is activated.
	 *
	 * @since 4.0.0
	 *
	 * @param  string $sku The sku to check.
	 * @return string      The addon basename.
	 */
	public function getAddonBasename( $sku ) {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
		$plugins = get_plugins();

		$keys = array_keys( $plugins );
		foreach ( $keys as $key ) {
			if ( preg_match( '|^' . $sku . '|', $key ) ) {
				return $key;
			}
		}

		return $sku;
	}

	/**
	 * Returns an array of levels connected to an addon.
	 *
	 * @since 4.0.0
	 *
	 * @param  string $addonName The addon name.
	 * @return array             The array of levels.
	 */
	public function getAddonLevels( $addonName ) {
		$addons = $this->getAddons();
		foreach ( $addons as $addon ) {
			if ( $addonName !== $addon->sku ) {
				continue;
			}

			if ( ! isset( $addon->levels ) ) {
				return [];
			}

			return $addon->levels;
		}

		return [];
	}

	/**
	 * Returns a list of addon SKUs.
	 *
	 * @since 4.5.6
	 *
	 * @return array The addon SKUs.
	 */
	public function getAddonSkus() {
		$addons = $this->getAddons();
		if ( empty( $addons ) ) {
			return [];
		}

		return array_map( function( $addon ) {
			return $addon->sku;
		}, $addons );
	}

	/**
	 * Get the URL to get addons.
	 *
	 * @since 4.1.8
	 *
	 * @return string The URL.
	 */
	protected function getAddonsUrl() {
		$url = $this->addonsUrl;
		if ( defined( 'AIOSEO_ADDONS_URL' ) ) {
			$url = AIOSEO_ADDONS_URL;
		}

		if ( defined( 'AIOSEO_INTERNAL_ADDONS' ) && AIOSEO_INTERNAL_ADDONS ) {
			$url = add_query_arg( 'internal', true, $url );
		}

		return $url;
	}

	/**
	 * Installs and activates a given addon or plugin.
	 *
	 * @since 4.0.0
	 *
	 * @param  string $name    The addon name/sku.
	 * @param  bool   $network Whether or not we are in a network environment.
	 * @return bool            Whether or not the installation was succesful.
	 */
	public function installAddon( $name, $network = false ) {
		if ( ! $this->canInstall() ) {
			return false;
		}

		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/template.php';
		require_once ABSPATH . 'wp-admin/includes/class-wp-screen.php';
		require_once ABSPATH . 'wp-admin/includes/screen.php';

		// Set the current screen to avoid undefined notices.
		set_current_screen( 'toplevel_page_aioseo' );

		// Prepare variables.
		$url = esc_url_raw(
			add_query_arg(
				[
					'page' => 'aioseo-settings',
				],
				admin_url( 'admin.php' )
			)
		);

		// Do not allow WordPress to search/download translations, as this will break JS output.
		remove_action( 'upgrader_process_complete', [ 'Language_Pack_Upgrader', 'async_upgrade' ], 20 );

		// Create the plugin upgrader with our custom skin.
		$installer = new Utils\PluginUpgraderSilentAjax( new Utils\PluginUpgraderSkin() );

		// Activate the plugin silently.
		$pluginUrl = ! empty( $installer->pluginSlugs[ $name ] ) ? $installer->pluginSlugs[ $name ] : $name;
		$activated = activate_plugin( $pluginUrl, '', $network );

		if ( ! is_wp_error( $activated ) ) {
			return $name;
		}

		// Using output buffering to prevent the FTP form from being displayed in the screen.
		ob_start();
		$creds = request_filesystem_credentials( $url, '', false, false, null );
		ob_end_clean();

		// Check for file system permissions.
		$fs = aioseo()->core->fs->noConflict();
		$fs->init( $creds );
		if ( false === $creds || ! $fs->isWpfsValid() ) {
			return false;
		}

		// Error check.
		if ( ! method_exists( $installer, 'install' ) ) {
			return false;
		}

		$installLink = ! empty( $installer->pluginLinks[ $name ] ) ? $installer->pluginLinks[ $name ] : null;

		// Check if this is an addon and if we have a download link.
		if ( empty( $installLink ) ) {
			$downloadUrl = aioseo()->addons->getDownloadUrl( $name );
			if ( empty( $downloadUrl ) ) {
				return false;
			}

			$installLink = $downloadUrl;
		}

		$installer->install( $installLink );

		// Flush the cache and return the newly installed plugin basename.
		wp_cache_flush();

		$pluginBasename = $installer->plugin_info();
		if ( ! $pluginBasename ) {
			return false;
		}

		// Activate the plugin silently.
		$activated = activate_plugin( $pluginBasename, '', $network );

		if ( is_wp_error( $activated ) ) {
			return false;
		}

		return $pluginBasename;
	}

	/**
	 * Determine if addons/plugins can be installed.
	 *
	 * @since 4.0.0
	 *
	 * @return bool True if yes, false if not.
	 */
	public function canInstall() {
		if ( ! current_user_can( 'install_plugins' ) ) {
			return false;
		}

		// Determine whether file modifications are allowed.
		if ( ! wp_is_file_mod_allowed( 'aioseo_can_install' ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Determine if addons/plugins can be updated.
	 *
	 * @since 4.1.6
	 *
	 * @return bool True if yes, false if not.
	 */
	public function canUpdate() {
		if ( ! current_user_can( 'update_plugins' ) && ! aioseo()->helpers->isDoingWpCli() ) {
			return false;
		}

		// Determine whether file modifications are allowed.
		if ( ! wp_is_file_mod_allowed( 'aioseo_can_update' ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Determine if addons/plugins can be activated.
	 *
	 * @since 4.1.3
	 *
	 * @return bool True if yes, false if not.
	 */
	public function canActivate() {
		if ( ! current_user_can( 'activate_plugins' ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Load an addon into aioseo.
	 *
	 * @since 4.1.0
	 *
	 * @param  string $slug
	 * @param  object $addon Addon class instance.
	 * @return void
	 */
	public function loadAddon( $slug, $addon ) {
		$this->{$slug}        = $addon;
		$this->loadedAddons[] = $slug;
	}

	/**
	 * Return a loaded addon.
	 *
	 * @since 4.1.0
	 *
	 * @param  string $slug
	 * @return object|null
	 */
	public function getLoadedAddon( $slug ) {
		return isset( $this->{$slug} ) ? $this->{$slug} : null;
	}

	/**
	 * Returns loaded addons
	 *
	 * @since 4.1.0
	 *
	 * @return array
	 */
	public function getLoadedAddons() {
		$loadedAddonsList = [];
		if ( ! empty( $this->loadedAddons ) ) {
			foreach ( $this->loadedAddons as $addonSlug ) {
				$loadedAddonsList[ $addonSlug ] = $this->{$addonSlug};
			}
		}

		return $loadedAddonsList;
	}

	/**
	 * Run a function through all addons that support it.
	 *
	 * @since 4.2.3
	 *
	 * @param  string $class    The class name.
	 * @param  string $function The function name.
	 * @param  array  $args     The args for the function.
	 * @return array            The response from each addon.
	 */
	public function doAddonFunction( $class, $function, $args = [] ) {
		$addonResponses = [];

		foreach ( $this->getLoadedAddons() as $addonSlug => $addon ) {
			if ( isset( $addon->$class ) && method_exists( $addon->$class, $function ) ) {
				$addonResponses[ $addonSlug ] = call_user_func_array( [ $addon->$class, $function ], $args );
			}
		}

		return $addonResponses;
	}

	/**
	 * Merges the data for Vue.
	 *
	 * @since 4.4.1
	 *
	 * @param  array  $data The data to merge.
	 * @param  string $page The current page.
	 * @return array        The data.
	 */
	public function getVueData( $data = [], $page = null ) {
		foreach ( $this->getLoadedAddons() as $addon ) {
			if ( isset( $addon->helpers ) && method_exists( $addon->helpers, 'getVueData' ) ) {
				$data = array_merge( $data, $addon->helpers->getVueData( $data, $page ) );
			}
		}

		return $data;
	}

	/**
	 * Retrieves a default addon with whatever information is needed if the API cannot be reached.
	 *
	 * @since 4.0.0
	 *
	 * @param  string $sku The sku of the addon.
	 * @return array       An array of addon data.
	 */
	public function getDefaultAddon( $sku ) {
		$addons = $this->getDefaultAddons();
		$addon  = [];
		foreach ( $addons as $a ) {
			if ( $a->sku === $sku ) {
				$addon = $a;
			}
		}

		return $addon;
	}

	/**
	 * Retrieves a default list of addons if the API cannot be reached.
	 *
	 * @since 4.0.0
	 *
	 * @return array An array of addons.
	 */
	protected function getDefaultAddons() {
		return json_decode( wp_json_encode( [
			[
				'sku'                => 'aioseo-eeat',
				'name'               => 'Author SEO (E-E-A-T)',
				'version'            => '1.0.0',
				'image'              => null,
				'icon'               => 'svg-eeat',
				'levels'             => [
					'plus',
					'pro',
					'elite',
				],
				'currentLevels'      => [
					'plus',
					'pro',
					'elite'
				],
				'requiresUpgrade'    => true,
				'description'        => '<p>Optimize your site for Google\'s E-E-A-T ranking factor by proving your writer\'s expertise through author schema markup and new UI elements.</p>',
				'descriptionVersion' => 0,
				'productUrl'         => 'https://aioseo.com/author-seo-eeat/',
				'learnMoreUrl'       => 'https://aioseo.com/author-seo-eeat/',
				'manageUrl'          => 'https://route#aioseo-search-appearance:author-seo',
				'basename'           => 'aioseo-eeat/aioseo-eeat.php',
				'installed'          => false,
				'isActive'           => false,
				'canInstall'         => false,
				'canActivate'        => false,
				'canUpdate'          => false,
				'capability'         => $this->getManageCapability( 'aioseo-eeat' ),
				'minimumVersion'     => '0.0.0',
				'hasMinimumVersion'  => false
			],
			[
				'sku'                => 'aioseo-redirects',
				'name'               => 'Redirection Manager',
				'version'            => '1.0.0',
				'image'              => null,
				'icon'               => 'svg-redirect',
				'levels'             => [
					'agency',
					'business',
					'pro',
					'elite'
				],
				'currentLevels'      => [
					'pro',
					'elite'
				],
				'requiresUpgrade'    => true,
				'description'        => '<p>Our Redirection Manager allows you to easily create and manage redirects for your broken links to avoid confusing search engines and users, as well as losing valuable backlinks. It even automatically sends users and search engines from your old URLs to your new ones.</p>', // phpcs:ignore Generic.Files.LineLength.MaxExceeded
				'descriptionVersion' => 0,
				'productUrl'         => 'https://aioseo.com/features/redirection-manager/',
				'learnMoreUrl'       => 'https://aioseo.com/features/redirection-manager/',
				'manageUrl'          => 'https://route#aioseo-redirects:redirects',
				'basename'           => 'aioseo-redirects/aioseo-redirects.php',
				'installed'          => false,
				'isActive'           => false,
				'canInstall'         => false,
				'canActivate'        => false,
				'canUpdate'          => false,
				'capability'         => $this->getManageCapability( 'aioseo-redirects' ),
				'minimumVersion'     => '0.0.0',
				'hasMinimumVersion'  => false
			],
			[
				'sku'                => 'aioseo-link-assistant',
				'name'               => 'Link Assistant',
				'version'            => '1.0.0',
				'image'              => null,
				'icon'               => 'svg-link-assistant',
				'levels'             => [
					'agency',
					'pro',
					'elite'
				],
				'currentLevels'      => [
					'pro',
					'elite'
				],
				'requiresUpgrade'    => true,
				'description'        => '<p>Super-charge your SEO with Link Assistant! Get relevant suggestions for adding internal links to older content as well as finding any orphaned posts that have no internal links. Use our reporting feature to see all link suggestions or add them directly from any page or post.</p>', // phpcs:ignore Generic.Files.LineLength.MaxExceeded
				'descriptionVersion' => 0,
				'productUrl'         => 'https://aioseo.com/feature/internal-link-assistant/',
				'learnMoreUrl'       => 'https://aioseo.com/feature/internal-link-assistant/',
				'manageUrl'          => 'https://route#aioseo-link-assistant:overview',
				'basename'           => 'aioseo-link-assistant/aioseo-link-assistant.php',
				'installed'          => false,
				'isActive'           => false,
				'canInstall'         => false,
				'canActivate'        => false,
				'canUpdate'          => false,
				'capability'         => $this->getManageCapability( 'aioseo-link-assistant' ),
				'minimumVersion'     => '0.0.0',
				'hasMinimumVersion'  => false
			],
			[
				'sku'                => 'aioseo-video-sitemap',
				'name'               => 'Video Sitemap',
				'version'            => '1.0.0',
				'image'              => null,
				'icon'               => 'svg-sitemaps-pro',
				'levels'             => [
					'individual',
					'business',
					'agency',
					'pro',
					'elite'
				],
				'currentLevels'      => [
					'pro',
					'elite'
				],
				'requiresUpgrade'    => true,
				'description'        => '<p>The Video Sitemap works in much the same way as the XML Sitemap module, it generates an XML Sitemap specifically for video content on your site. Search engines use this information to display rich snippet information in search results.</p>', // phpcs:ignore Generic.Files.LineLength.MaxExceeded
				'descriptionVersion' => 0,
				'productUrl'         => 'https://aioseo.com/video-sitemap',
				'learnMoreUrl'       => 'https://aioseo.com/video-sitemap',
				'manageUrl'          => 'https://route#aioseo-sitemaps:video-sitemap',
				'basename'           => 'aioseo-video-sitemap/aioseo-video-sitemap.php',
				'installed'          => false,
				'isActive'           => false,
				'canInstall'         => false,
				'canActivate'        => false,
				'canUpdate'          => false,
				'capability'         => $this->getManageCapability( 'aioseo-video-sitemap' ),
				'minimumVersion'     => '0.0.0',
				'hasMinimumVersion'  => false
			],
			[
				'sku'                => 'aioseo-local-business',
				'name'               => 'Local Business SEO',
				'version'            => '1.0.0',
				'image'              => null,
				'icon'               => 'svg-local-business',
				'levels'             => [
					'business',
					'agency',
					'plus',
					'pro',
					'elite'
				],
				'currentLevels'      => [
					'plus',
					'pro',
					'elite'
				],
				'requiresUpgrade'    => true,
				'description'        => '<p>Local Business schema markup enables you to tell Google about your business, including your business name, address and phone number, opening hours and price range. This information may be displayed as a Knowledge Graph card or business carousel.</p>', // phpcs:ignore Generic.Files.LineLength.MaxExceeded
				'descriptionVersion' => 0,
				'productUrl'         => 'https://aioseo.com/local-business',
				'learnMoreUrl'       => 'https://aioseo.com/local-business',
				'manageUrl'          => 'https://route#aioseo-local-seo:locations',
				'basename'           => 'aioseo-local-business/aioseo-local-business.php',
				'installed'          => false,
				'isActive'           => false,
				'canInstall'         => false,
				'canActivate'        => false,
				'canUpdate'          => false,
				'capability'         => $this->getManageCapability( 'aioseo-local-business' ),
				'minimumVersion'     => '0.0.0',
				'hasMinimumVersion'  => false
			],
			[
				'sku'                => 'aioseo-news-sitemap',
				'name'               => 'News Sitemap',
				'version'            => '1.0.0',
				'image'              => null,
				'icon'               => 'svg-sitemaps-pro',
				'levels'             => [
					'business',
					'agency',
					'pro',
					'elite'
				],
				'currentLevels'      => [
					'pro',
					'elite'
				],
				'requiresUpgrade'    => true,
				'description'        => '<p>Our Google News Sitemap lets you control which content you submit to Google News and only contains articles that were published in the last 48 hours. In order to submit a News Sitemap to Google, you must have added your site to Google’s Publisher Center and had it approved.</p>', // phpcs:ignore Generic.Files.LineLength.MaxExceeded
				'descriptionVersion' => 0,
				'productUrl'         => 'https://aioseo.com/news-sitemap',
				'learnMoreUrl'       => 'https://aioseo.com/news-sitemap',
				'manageUrl'          => 'https://route#aioseo-sitemaps:news-sitemap',
				'basename'           => 'aioseo-news-sitemap/aioseo-news-sitemap.php',
				'installed'          => false,
				'isActive'           => false,
				'canInstall'         => false,
				'canActivate'        => false,
				'canUpdate'          => false,
				'capability'         => $this->getManageCapability( 'aioseo-news-sitemap' ),
				'minimumVersion'     => '0.0.0',
				'hasMinimumVersion'  => false
			],
			[
				'sku'                => 'aioseo-index-now',
				'name'               => 'IndexNow',
				'version'            => '1.0.0',
				'image'              => null,
				'icon'               => 'svg-sitemaps-pro',
				'levels'             => [
					'agency',
					'business',
					'basic',
					'plus',
					'pro',
					'elite'
				],
				'currentLevels'      => [
					'basic',
					'plus',
					'pro',
					'elite'
				],
				'requiresUpgrade'    => true,
				'description'        => '<p>Add IndexNow support to instantly notify search engines when your content has changed. This helps the search engines to prioritize the changes on your website and helps you rank faster.</p>', // phpcs:ignore Generic.Files.LineLength.MaxExceeded
				'descriptionVersion' => 0,
				'downloadUrl'        => '',
				'productUrl'         => 'https://aioseo.com/index-now/',
				'learnMoreUrl'       => 'https://aioseo.com/index-now/',
				'manageUrl'          => 'https://route#aioseo-settings:webmaster-tools',
				'basename'           => 'aioseo-index-now/aioseo-index-now.php',
				'installed'          => false,
				'isActive'           => false,
				'canInstall'         => false,
				'canActivate'        => false,
				'canUpdate'          => false,
				'capability'         => $this->getManageCapability( 'aioseo-index-now' ),
				'minimumVersion'     => '0.0.0',
				'hasMinimumVersion'  => false
			],
			[
				'sku'                => 'aioseo-rest-api',
				'name'               => 'REST API',
				'version'            => '1.0.0',
				'image'              => null,
				'icon'               => 'svg-code',
				'levels'             => [
					'plus',
					'pro',
					'elite'
				],
				'currentLevels'      => [
					'plus',
					'pro',
					'elite'
				],
				'requiresUpgrade'    => true,
				'description'        => '<p>Manage your post and term SEO meta via the WordPress REST API. This addon also works seamlessly with headless WordPress installs.</p>', // phpcs:ignore Generic.Files.LineLength.MaxExceeded
				'descriptionVersion' => 0,
				'downloadUrl'        => '',
				'productUrl'         => 'https://aioseo.com/feature/rest-api/',
				'learnMoreUrl'       => 'https://aioseo.com/feature/rest-api/',
				'manageUrl'          => null,
				'basename'           => 'aioseo-rest-api/aioseo-rest-api.php',
				'installed'          => false,
				'isActive'           => false,
				'canInstall'         => false,
				'canActivate'        => false,
				'canUpdate'          => false,
				'capability'         => null,
				'minimumVersion'     => '0.0.0',
				'hasMinimumVersion'  => false
			],
			[
				'sku'                => 'aioseo-image-seo',
				'name'               => 'Image SEO',
				'version'            => '1.0.0',
				'image'              => null,
				'icon'               => 'svg-image-seo',
				'levels'             => [
					'individual',
					'business',
					'agency',
					'plus',
					'pro',
					'elite',
				],
				'currentLevels'      => [
					'plus',
					'pro',
					'elite'
				],
				'requiresUpgrade'    => true,
				'description'        => '<p>Globally control the Title attribute and Alt text for images in your content. These attributes are essential for both accessibility and SEO.</p>',
				'descriptionVersion' => 0,
				'productUrl'         => 'https://aioseo.com/image-seo',
				'learnMoreUrl'       => 'https://aioseo.com/image-seo',
				'manageUrl'          => 'https://route#aioseo-search-appearance:media',
				'basename'           => 'aioseo-image-seo/aioseo-image-seo.php',
				'installed'          => false,
				'isActive'           => false,
				'canInstall'         => false,
				'canActivate'        => false,
				'canUpdate'          => false,
				'capability'         => $this->getManageCapability( 'aioseo-image-seo' ),
				'minimumVersion'     => '0.0.0',
				'hasMinimumVersion'  => false
			]
		] ) );
	}

	/**
	 * Check for updates for all addons.
	 *
	 * @since 4.2.4
	 *
	 * @return void
	 */
	public function registerUpdateCheck() {}

	/**
	 * Updates a given addon or plugin.
	 *
	 * @since 4.4.3
	 *
	 * @param  string $name    The addon name/sku.
	 * @param  bool   $network Whether we are in a network environment.
	 * @return bool            Whether the installation was succesful.
	 */
	public function upgradeAddon( $name, $network ) { // phpcs:ignore VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
		return false;
	}

	/**
	 * Get the download URL for the given addon.
	 *
	 * @since 4.4.3
	 *
	 * @param  string $sku The addon sku.
	 * @return string      The download url for the addon.
	 */
	public function getDownloadUrl( $sku ) { // phpcs:ignore VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
		return '';
	}
}