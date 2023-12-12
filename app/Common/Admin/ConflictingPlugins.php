<?php
namespace AIOSEO\Plugin\Common\Admin;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AIOSEO\Plugin\Common\Models;

/**
 * Checks for conflicting plugins.
 *
 * @since 4.0.0
 */
class ConflictingPlugins {
	/**
	 * A list of conflicting plugin slugs.
	 *
	 * @since 4.5.1
	 *
	 * @var array
	 */
	private $conflictingPluginSlugs = [
		// Note: We should NOT add Jetpack here since they automatically disable their SEO module when ours is active.
		'wordpress-seo',
		'seo-by-rank-math',
		'wp-seopress',
		'autodescription',
		'slim-seo',
		'squirrly-seo',
		'redirection',
		'eps-301-redirects',
		'simple-301-redirects',
		'301-redirects',
		'404-to-homepage',
		'quick-301-redirects',
		'all-404-redirect-to-homepage',
		'redirect-redirection',
		'safe-redirect-manager',
		'google-sitemap-generator',
		'xml-sitemap-feed',
		'www-xml-sitemap-generator-org',
		'google-sitemap-plugin',
	];

	/**
	 * Class Constructor.
	 *
	 * @since 4.0.0
	 */
	public function __construct() {
		// We don't want to trigger our notices when not in the admin.
		if ( ! is_admin() ) {
			return;
		}

		add_action( 'init', [ $this, 'init' ] );
	}

	/**
	 * Initialize the conflicting plugins check.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function init() {
		// Only do this for users who can install/deactivate plugins.
		if ( ! current_user_can( 'install_plugins' ) ) {
			return;
		}

		$conflictingPlugins = $this->getAllConflictingPlugins();

		$notification = Models\Notification::getNotificationByName( 'conflicting-plugins' );
		if ( empty( $conflictingPlugins ) ) {
			if ( ! $notification->exists() ) {
				return;
			}

			Models\Notification::deleteNotificationByName( 'conflicting-plugins' );

			return;
		}

		aioseo()->notices->conflictingPlugins( $conflictingPlugins );
	}

	/**
	 * Get a list of all conflicting plugins.
	 *
	 * @since 4.0.0
	 *
	 * @return array An array of conflicting plugins.
	 */
	public function getAllConflictingPlugins() {
		$conflictingSeoPlugins     = $this->getConflictingPlugins( 'seo' );
		$conflictingSitemapPlugins = [];

		if (
			aioseo()->options->sitemap->general->enable ||
			aioseo()->options->sitemap->rss->enable
		) {
			$conflictingSitemapPlugins = $this->getConflictingPlugins( 'sitemap' );
		}

		return array_merge( $conflictingSeoPlugins, $conflictingSitemapPlugins );
	}

	/**
	 * Get a list of conflicting plugins for AIOSEO.
	 *
	 * @since 4.0.0
	 *
	 * @param  string $type A type to look for.
	 * @return array        An array of conflicting plugins.
	 */
	public function getConflictingPlugins( $type ) {
		$activePlugins = get_option( 'active_plugins' );

		$conflictingPlugins = [];
		switch ( $type ) {
			// Note: We should NOT add Jetpack here since they automatically disable their SEO module when ours is active.
			case 'seo':
				$conflictingPlugins = [
					'Rank Math SEO'     => 'seo-by-rank-math/rank-math.php',
					'Rank Math SEO Pro' => 'seo-by-rank-math-pro/rank-math-pro.php',
					'SEOPress'          => 'wp-seopress/seopress.php',
					'The SEO Framework' => 'autodescription/autodescription.php',
					'Yoast SEO'         => 'wordpress-seo/wp-seo.php',
					'Yoast SEO Premium' => 'wordpress-seo-premium/wp-seo-premium.php'
				];
				break;
			case 'sitemap':
				$conflictingPlugins = [
					'Google XML Sitemaps'          => 'google-sitemap-generator/sitemap.php',
					'Google XML Sitemap Generator' => 'www-xml-sitemap-generator-org/www-xml-sitemap-generator-org.php',
					'Sitemap by BestWebSoft'       => 'google-sitemap-plugin/google-sitemap-plugin.php',
					'XML Sitemap & Google News'    => 'xml-sitemap-feed/xml-sitemap.php'
				];
				break;
		}

		return array_intersect( $conflictingPlugins, $activePlugins );
	}

	/**
	 * Deactivate conflicting plugins.
	 *
	 * @since 4.5.1
	 *
	 * @param array $types An array of types to look for.
	 * @return void
	 */
	public function deactivateConflictingPlugins( $types ) {
		$seo     = in_array( 'seo', $types, true ) ? $this->getConflictingPlugins( 'seo' ) : [];
		$sitemap = in_array( 'sitemap', $types, true ) ? $this->getConflictingPlugins( 'sitemap' ) : [];
		$plugins = array_merge(
			$seo,
			$sitemap
		);

		require_once ABSPATH . 'wp-admin/includes/plugin.php';

		foreach ( $plugins as $pluginPath ) {
			if ( is_plugin_active( $pluginPath ) ) {
				deactivate_plugins( $pluginPath );
			}
		}
	}

	/**
	 * Get a list of conflicting plugin slugs.
	 *
	 * @since 4.5.1
	 *
	 * @return array An array of conflicting plugin slugs.
	 */
	public function getConflictingPluginSlugs() {
		return $this->conflictingPluginSlugs;
	}
}