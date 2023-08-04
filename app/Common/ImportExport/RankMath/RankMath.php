<?php
namespace AIOSEO\Plugin\Common\ImportExport\RankMath;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AIOSEO\Plugin\Common\ImportExport;

class RankMath extends ImportExport\Importer {
	/**
	 * A list of plugins to look for to import.
	 *
	 * @since 4.0.0
	 *
	 * @var array
	 */
	public $plugins = [
		[
			'name'     => 'Rank Math SEO',
			'version'  => '1.0',
			'basename' => 'seo-by-rank-math/rank-math.php',
			'slug'     => 'rank-math-seo'
		]
	];

	/**
	 * The post action name.
	 *
	 * @since 4.0.0
	 *
	 * @var string
	 */
	public $postActionName = 'aioseo_import_post_meta_rank_math';

	/**
	 * Class constructor.
	 *
	 * @since 4.0.0
	 *
	 * @param ImportExport\ImportExport $importer the ImportExport class.
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
	 * @since 4.0.0
	 *
	 * @return void
	 */
	protected function importSettings() {
		new GeneralSettings();
		new TitleMeta();
		new Sitemap();
	}
}