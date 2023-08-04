<?php
namespace AIOSEO\Plugin\Common\ImportExport\SeoPress;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AIOSEO\Plugin\Common\ImportExport;

class SeoPress extends ImportExport\Importer {
	/**
	 * A list of plugins to look for to import.
	 *
	 * @since 4.1.4
	 *
	 * @var array
	 */
	public $plugins = [
		[
			'name'     => 'SEOPress',
			'version'  => '4.0',
			'basename' => 'wp-seopress/seopress.php',
			'slug'     => 'seopress'
		],
		[
			'name'     => 'SEOPress PRO',
			'version'  => '4.0',
			'basename' => 'wp-seopress-pro/seopress-pro.php',
			'slug'     => 'seopress-pro'
		],
	];

	/**
	 * The post action name.
	 *
	 * @since 4.1.4
	 *
	 * @var string
	 */
	public $postActionName = 'aioseo_import_post_meta_seopress';

	/**
	 * The post action name.
	 *
	 * @since 4.1.4
	 *
	 * @param ImportExport\ImportExport $importer The main importer class.
	 */
	public function __construct( $importer ) {
		$this->helpers  = new Helpers();
		$this->postMeta = new PostMeta();
		add_action( $this->postActionName, [ $this->postMeta, 'importPostMeta' ] );

		$plugins = $this->plugins;
		foreach ( $plugins as $key => $plugin ) {
			$plugins[ $key ]['class'] = $this;
		}
		$importer->addPlugins( $plugins );
	}

	/**
	 * Imports the settings.
	 *
	 * @since 4.1.4
	 *
	 * @return void
	 */
	protected function importSettings() {
		new GeneralSettings();
		new Analytics();
		new SocialMeta();
		new Titles();
		new Sitemap();
		new RobotsTxt();
		new Rss();
		new Breadcrumbs();
	}
}