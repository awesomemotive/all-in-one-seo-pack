<?php
namespace AIOSEO\Plugin\Common\ImportExport\YoastSeo;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AIOSEO\Plugin\Common\ImportExport;

class YoastSeo extends ImportExport\Importer {
	/**
	 * A list of plugins to look for to import.
	 *
	 * @since 4.0.0
	 *
	 * @var array
	 */
	public $plugins = [
		[
			'name'     => 'Yoast SEO',
			'version'  => '14.0',
			'basename' => 'wordpress-seo/wp-seo.php',
			'slug'     => 'yoast-seo'
		],
		[
			'name'     => 'Yoast SEO Premium',
			'version'  => '14.0',
			'basename' => 'wordpress-seo-premium/wp-seo-premium.php',
			'slug'     => 'yoast-seo-premium'
		],
	];

	/**
	 * The post action name.
	 *
	 * @since 4.0.0
	 *
	 * @var string
	 */
	public $postActionName = 'aioseo_import_post_meta_yoast_seo';

	/**
	 * The user action name.
	 *
	 * @since 4.1.4
	 *
	 * @var string
	 */
	public $userActionName = 'aioseo_import_user_meta_yoast_seo';

	/**
	 * UserMeta class instance.
	 *
	 * @since 4.2.7
	 *
	 * @var UserMeta
	 */
	private $userMeta = null;

	/**
	 * SearchAppearance class instance.
	 *
	 * @since 4.2.7
	 *
	 * @var SearchAppearance
	 */
	public $searchAppearance = null;

	/**
	 * The post action name.
	 *
	 * @since 4.0.0
	 *
	 * @param ImportExport\ImportExport $importer The main importer class.
	 */
	public function __construct( $importer ) {
		$this->helpers  = new Helpers();
		$this->postMeta = new PostMeta();
		$this->userMeta = new UserMeta();

		add_action( $this->postActionName, [ $this->postMeta, 'importPostMeta' ] );
		add_action( $this->userActionName, [ $this->userMeta, 'importUserMeta' ] );

		$plugins = $this->plugins;
		foreach ( $plugins as $key => $plugin ) {
			$plugins[ $key ]['class'] = $this;
		}
		$importer->addPlugins( $plugins );
	}

	/**
	 * Imports the settings.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	protected function importSettings() {
		new GeneralSettings();
		$this->searchAppearance = new SearchAppearance();
		// NOTE: The Social Meta settings need to be imported after the Search Appearance ones because some imports depend on what was imported there.
		new SocialMeta();
		$this->userMeta->scheduleImport();
	}
}