<?php
namespace AIOSEO\Plugin\Common\ImportExport;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AIOSEO\Plugin\Common\Models;

/**
 * Handles the importing/exporting of settings and SEO data.
 *
 * @since 4.0.0
 */
class ImportExport {
	/**
	 * List of plugins for importing.
	 *
	 * @since 4.0.0
	 *
	 * @var array
	 */
	private $plugins = [];

	/**
	 * YoastSeo class instance.
	 *
	 * @since 4.2.7
	 *
	 * @var YoastSeo\YoastSeo
	 */
	public $yoastSeo = null;

	/**
	 * RankMath class instance.
	 *
	 * @since 4.2.7
	 *
	 * @var RankMath\RankMath
	 */
	public $rankMath = null;

	/**
	 * SeoPress class instance.
	 *
	 * @since 4.2.7
	 *
	 * @var SeoPress\SeoPress
	 */
	public $seoPress = null;

	/**
	 * Class constructor.
	 *
	 * @since 4.0.0
	 */
	public function __construct() {
		$this->yoastSeo = new YoastSeo\YoastSeo( $this );
		$this->rankMath = new RankMath\RankMath( $this );
		$this->seoPress = new SeoPress\SeoPress( $this );
	}

	/**
	 * Converts the content of a given V3 .ini settings file to an array of settings.
	 *
	 * @since 4.0.0
	 *
	 * @param  string $contents The .ini file contents.
	 * @return array            The settings.
	 */
	public function importIniData( $contents ) {
		$lines = array_filter( preg_split( '/\r\n|\r|\n/', $contents ) );

		$sections     = [];
		$sectionLabel = '';
		$sectionCount = 0;

		foreach ( $lines as $line ) {
			$line = trim( $line );
			// Ignore comments.
			if ( preg_match( '#^;.*#', $line ) || preg_match( '#\<(\?php|script)#', $line ) ) {
				continue;
			}

			$matches = [];
			if ( preg_match( '#^\[(\S+)\]$#', $line, $label ) ) {
				$sectionLabel = strval( $label[1] );
				if ( 'post_data' === $sectionLabel ) {
					$sectionCount++;
				}
				if ( ! isset( $sections[ $sectionLabel ] ) ) {
					$sections[ $sectionLabel ] = [];
				}
			} elseif ( preg_match( "#^(\S+)\s*=\s*'(.*)'$#", $line, $matches ) ) {
				if ( 'post_data' === $sectionLabel ) {
					$sections[ $sectionLabel ][ $sectionCount ][ $matches[1] ] = $matches[2];
				} else {
					$sections[ $sectionLabel ][ $matches[1] ] = $matches[2];
				}
			} elseif ( preg_match( '#^(\S+)\s*=\s*NULL$#', $line, $matches ) ) {
				if ( 'post_data' === $sectionLabel ) {
					$sections[ $sectionLabel ][ $sectionCount ][ $matches[1] ] = '';
				} else {
					$sections[ $sectionLabel ][ $matches[1] ] = '';
				}
			} else {
				continue;
			}
		}

		$sanitizedSections = [];
		foreach ( $sections as $section => $options ) {
			$sanitizedSection = [];
			foreach ( $options as $option => $value ) {
				$sanitizedSection[ $option ] = $this->convertAndSanitize( $value );
			}
			$sanitizedSections[ $section ] = $sanitizedSection;
		}

		$oldOptions = [];
		$postData   = [];
		foreach ( $sanitizedSections as $label => $data ) {
			switch ( $label ) {
				case 'aioseop_options':
					$oldOptions = array_merge( $oldOptions, $data );
					break;
				case 'aiosp_feature_manager_options':
				case 'aiosp_opengraph_options':
				case 'aiosp_sitemap_options':
				case 'aiosp_video_sitemap_options':
				case 'aiosp_schema_local_business_options':
				case 'aiosp_image_seo_options':
				case 'aiosp_robots_options':
				case 'aiosp_bad_robots_options':
					$oldOptions['modules'][ $label ] = $data;
					break;
				case 'post_data':
					$postData = $data;
					break;
				default:
					break;
			}
		}

		if ( ! empty( $oldOptions ) ) {
			aioseo()->migration->migrateSettings( $oldOptions );
		}

		if ( ! empty( $postData ) ) {
			$this->importOldPostMeta( $postData );
		}

		return true;
	}

	/**
	 * Imports the post meta from V3.
	 *
	 * @since 4.0.0
	 *
	 * @param  array $postData The post data.
	 * @return void
	 */
	private function importOldPostMeta( $postData ) {
		$mappedMeta = [
			'_aioseop_title'              => 'title',
			'_aioseop_description'        => 'description',
			'_aioseop_custom_link'        => 'canonical_url',
			'_aioseop_sitemap_exclude'    => '',
			'_aioseop_disable'            => '',
			'_aioseop_noindex'            => 'robots_noindex',
			'_aioseop_nofollow'           => 'robots_nofollow',
			'_aioseop_sitemap_priority'   => 'priority',
			'_aioseop_sitemap_frequency'  => 'frequency',
			'_aioseop_keywords'           => 'keywords',
			'_aioseop_opengraph_settings' => ''
		];

		$excludedPosts        = [];
		$sitemapExcludedPosts = [];

		require_once ABSPATH . 'wp-admin/includes/post.php';
		foreach ( $postData as $post => $values ) {
			$postId = \post_exists( $values['post_title'], '', $values['post_date'] );
			if ( ! $postId ) {
				continue;
			}

			$meta = [
				'post_id' => $postId,
			];

			foreach ( $values as $name => $value ) {
				if ( ! in_array( $name, array_keys( $mappedMeta ), true ) ) {
					continue;
				}

				switch ( $name ) {
					case '_aioseop_sitemap_exclude':
						if ( empty( $value ) ) {
							break;
						}
						$sitemapExcludedPosts[] = $postId;
						break;
					case '_aioseop_disable':
						if ( empty( $value ) ) {
							break;
						}
						$excludedPosts[] = $postId;
						break;
					case '_aioseop_noindex':
					case '_aioseop_nofollow':
						$meta[ $mappedMeta[ $name ] ] = ! empty( $value );
						if ( ! empty( $value ) ) {
							$meta['robots_default'] = false;
						}
						break;
					case '_aioseop_keywords':
						$meta[ $mappedMeta[ $name ] ] = aioseo()->migration->helpers->oldKeywordsToNewKeywords( $value );
						break;
					case '_aioseop_opengraph_settings':
						$class = new \AIOSEO\Plugin\Common\Migration\Meta();
						$meta += $class->convertOpenGraphMeta( $value );
						break;
					default:
						$meta[ $mappedMeta[ $name ] ] = esc_html( wp_strip_all_tags( strval( $value ) ) );
						break;
				}
			}
			$post = Models\Post::getPost( $postId );
			$post->set( $meta );
			$post->save();
		}

		if ( count( $excludedPosts ) ) {
			$deprecatedOptions = aioseo()->internalOptions->internal->deprecatedOptions;
			if ( ! in_array( 'excludePosts', $deprecatedOptions, true ) ) {
				array_push( $deprecatedOptions, 'excludePosts' );
				aioseo()->internalOptions->internal->deprecatedOptions = $deprecatedOptions;
			}

			$posts = aioseo()->options->deprecated->searchAppearance->advanced->excludePosts;

			foreach ( $excludedPosts as $id ) {
				if ( ! intval( $id ) ) {
					continue;
				}
				$post = get_post( $id );
				if ( ! is_object( $post ) ) {
					continue;
				}
				$excludedPost        = new \stdClass();
				$excludedPost->type  = $post->post_type;
				$excludedPost->value = $post->ID;
				$excludedPost->label = $post->post_title;
				$excludedPost->link  = get_permalink( $post );

				$posts[] = wp_json_encode( $excludedPost );
			}
			aioseo()->options->deprecated->searchAppearance->advanced->excludePosts = $posts;
		}

		if ( count( $sitemapExcludedPosts ) ) {
			aioseo()->options->sitemap->general->advancedSettings->enable = true;

			$posts = aioseo()->options->sitemap->general->advancedSettings->excludePosts;
			foreach ( $sitemapExcludedPosts as $id ) {
				if ( ! intval( $id ) ) {
					continue;
				}
				$post = get_post( $id );
				if ( ! is_object( $post ) ) {
					continue;
				}
				$excludedPost        = new \stdClass();
				$excludedPost->type  = $post->post_type;
				$excludedPost->value = $post->ID;
				$excludedPost->label = $post->post_title;
				$excludedPost->link  = get_permalink( $post );

				$posts[] = wp_json_encode( $excludedPost );
			}
			aioseo()->options->sitemap->general->advancedSettings->excludePosts = $posts;
		}
	}

	/**
	 * Unserializes an option value if needed and then sanitizes it.
	 *
	 * @since 4.0.0
	 *
	 * @param  string $value The option value.
	 * @return mixed         The sanitized, converted option value.
	 */
	private function convertAndSanitize( $value ) {
		$value = aioseo()->helpers->maybeUnserialize( $value );

		switch ( gettype( $value ) ) {
			case 'boolean':
				return (bool) $value;
			case 'string':
				return esc_html( wp_strip_all_tags( wp_check_invalid_utf8( trim( $value ) ) ) );
			case 'integer':
				return intval( $value );
			case 'double':
				return floatval( $value );
			case 'array':
				$sanitized = [];
				foreach ( (array) $value as $k => $v ) {
					$sanitized[ $k ] = $this->convertAndSanitize( $v );
				}

				return $sanitized;
			default:
				return '';
		}
	}

	/**
	 * Starts an import.
	 *
	 * @since 4.0.0
	 *
	 * @param  string $plugin  The slug of the plugin to import.
	 * @param  array $settings Which settings to import.
	 * @return void
	 */
	public function startImport( $plugin, $settings ) {
		// First cancel any scans running that might interfere with our import.
		$this->cancelScans();

		foreach ( $this->plugins as $pluginData ) {
			if ( $pluginData['slug'] === $plugin ) {
				$pluginData['class']->doImport( $settings );

				return;
			}
		}
	}

	/**
	 * Cancel scans that are currently running and could conflict with our migration.
	 *
	 * @since 4.1.4
	 *
	 * @return void
	 */
	private function cancelScans() {
		// Figure out how to check if these addons are enabled and then get the action names that way.
		aioseo()->actionScheduler->unschedule( 'aioseo_video_sitemap_scan' );
		aioseo()->actionScheduler->unschedule( 'aioseo_image_sitemap_scan' );
	}

	/**
	 * Checks if an import is currently running.
	 *
	 * @since 4.1.4
	 *
	 * @return boolean True if an import is currently running.
	 */
	public function isImportRunning() {
		$importsRunning = aioseo()->core->cache->get( 'import_%_meta_%' );

		return ! empty( $importsRunning );
	}

	/**
	 * Adds plugins to the import/export.
	 *
	 * @since 4.0.0
	 *
	 * @param  array $plugins The plugins to add.
	 * @return void
	 */
	public function addPlugins( $plugins ) {
		$this->plugins = array_merge( $this->plugins, $plugins );
	}

	/**
	 * Get the plugins we allow importing from.
	 *
	 * @since 4.0.0
	 *
	 * @return array
	 */
	public function plugins() {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
		$plugins          = [];
		$installedPlugins = array_keys( get_plugins() );
		foreach ( $this->plugins as $importerPlugin ) {
			$data = [
				'slug'      => $importerPlugin['slug'],
				'name'      => $importerPlugin['name'],
				'version'   => null,
				'canImport' => false,
				'basename'  => $importerPlugin['basename'],
				'installed' => false
			];

			if ( in_array( $importerPlugin['basename'], $installedPlugins, true ) ) {
				$pluginData = get_file_data( trailingslashit( WP_PLUGIN_DIR ) . $importerPlugin['basename'], [
					'name'    => 'Plugin Name',
					'version' => 'Version',
				] );

				$canImport = false;
				if ( version_compare( $importerPlugin['version'], $pluginData['version'], '<=' ) ) {
					$canImport = true;
				}

				$data['name']      = $pluginData['name'];
				$data['version']   = $pluginData['version'];
				$data['canImport'] = $canImport;
				$data['installed'] = true;
			}

			$plugins[] = $data;
		}

		return $plugins;
	}
}