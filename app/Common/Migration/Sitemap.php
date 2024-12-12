<?php
namespace AIOSEO\Plugin\Common\Migration;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// phpcs:disable WordPress.Arrays.ArrayDeclarationSpacing.AssociativeArrayFound

/**
 * Migrates the XML Sitemap settings from V3.
 *
 * @since 4.0.0
 */
class Sitemap {
	/**
	 * The old V3 options.
	 *
	 * @since 4.0.0
	 *
	 * @var array
	 */
	protected $oldOptions = [];

	/**
	 * Class constructor.
	 *
	 * @since 4.0.0
	 */
	public function __construct() {
		$this->oldOptions = aioseo()->migration->oldOptions;

		if ( empty( $this->oldOptions['modules']['aiosp_sitemap_options'] ) ) {
			return;
		}

		$this->checkIfStatic();
		$this->migrateLinksPerIndex();
		$this->migrateIncludedObjects();
		$this->migratePrioFreq();
		$this->migrateAdditionalPages();
		$this->migrateExcludedPages();
		$this->regenerateSitemap();

		$settings = [
			'aiosp_sitemap_indexes'          => [ 'type' => 'boolean', 'newOption' => [ 'sitemap', 'general', 'indexes' ] ],
			'aiosp_sitemap_archive'          => [ 'type' => 'boolean', 'newOption' => [ 'sitemap', 'general', 'date' ] ],
			'aiosp_sitemap_author'           => [ 'type' => 'boolean', 'newOption' => [ 'sitemap', 'general', 'author' ] ],
			'aiosp_sitemap_images'           => [ 'type' => 'boolean', 'newOption' => [ 'sitemap', 'general', 'advancedSettings', 'excludeImages' ] ],
			'aiosp_sitemap_rss_sitemap'      => [ 'type' => 'boolean', 'newOption' => [ 'sitemap', 'rss', 'enable' ] ],
			'aiosp_sitemap_filename'         => [ 'type' => 'string', 'newOption' => [ 'sitemap', 'general', 'filename' ] ],
			'aiosp_sitemap_publication_name' => [ 'type' => 'boolean', 'newOption' => [ 'sitemap', 'news', 'publicationName' ] ],
			'aiosp_sitemap_rewrite'          => [ 'type' => 'boolean', 'newOption' => [ 'deprecated', 'sitemap', 'general', 'advancedSettings', 'dynamic' ] ]
		];

		aioseo()->migration->helpers->mapOldToNew( $settings, $this->oldOptions['modules']['aiosp_sitemap_options'] );

		if (
			aioseo()->options->sitemap->general->advancedSettings->excludePosts ||
			aioseo()->options->sitemap->general->advancedSettings->excludeTerms ||
			aioseo()->options->sitemap->general->advancedSettings->excludeImages ||
			( in_array( 'staticSitemap', aioseo()->internalOptions->internal->deprecatedOptions, true ) && ! aioseo()->options->deprecated->sitemap->general->advancedSettings->dynamic )
		) {
			aioseo()->options->sitemap->general->advancedSettings->enable = true;
		}
	}

	/**
	 * Check if the sitemap is statically generated.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	private function checkIfStatic() {
		if (
			isset( $this->oldOptions['modules']['aiosp_sitemap_options']['aiosp_sitemap_rewrite'] ) &&
			empty( $this->oldOptions['modules']['aiosp_sitemap_options']['aiosp_sitemap_rewrite'] )
		) {
			$deprecatedOptions = aioseo()->internalOptions->internal->deprecatedOptions;
			array_push( $deprecatedOptions, 'staticSitemap' );
			aioseo()->internalOptions->internal->deprecatedOptions = $deprecatedOptions;

			aioseo()->options->deprecated->sitemap->general->advancedSettings->dynamic = false;
		}
	}

	/**
	 * Migrates the amount of links per sitemap index.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	private function migrateLinksPerIndex() {
		if ( ! empty( $this->oldOptions['modules']['aiosp_sitemap_options']['aiosp_sitemap_max_posts'] ) ) {
			$value = intval( $this->oldOptions['modules']['aiosp_sitemap_options']['aiosp_sitemap_max_posts'] );
			if ( ! $value ) {
				return;
			}
			$value = $value > 50000 ? 50000 : $value;
			aioseo()->options->sitemap->general->linksPerIndex = $value;
		}
	}

	/**
	 * Migrates the excluded object settings.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	protected function migrateExcludedPages() {
		if (
			empty( $this->oldOptions['modules']['aiosp_sitemap_options']['aiosp_sitemap_excl_terms'] ) &&
			empty( $this->oldOptions['modules']['aiosp_sitemap_options']['aiosp_sitemap_excl_pages'] )
		) {
			return;
		}

		$excludedPosts = aioseo()->options->sitemap->general->advancedSettings->excludePosts;
		if ( ! empty( $this->oldOptions['modules']['aiosp_sitemap_options']['aiosp_sitemap_excl_pages'] ) ) {
			$pages = explode( ',', $this->oldOptions['modules']['aiosp_sitemap_options']['aiosp_sitemap_excl_pages'] );
			if ( count( $pages ) ) {
				foreach ( $pages as $page ) {
					$page = trim( $page );
					$id   = intval( $page );
					if ( ! $id ) {
						$post = get_page_by_path( $page, OBJECT, aioseo()->helpers->getPublicPostTypes( true ) );
						if ( $post && is_object( $post ) ) {
							$id = $post->ID;
						}
					}

					if ( $id ) {
						$post = get_post( $id );
						if ( ! is_object( $post ) ) {
							continue;
						}

						$excludedPost        = new \stdClass();
						$excludedPost->value = $id;
						$excludedPost->type  = $post->post_type;
						$excludedPost->label = $post->post_title;
						$excludedPost->link  = get_permalink( $id );

						array_push( $excludedPosts, wp_json_encode( $excludedPost ) );
					}
				}
			}
		}
		aioseo()->options->sitemap->general->advancedSettings->excludePosts = $excludedPosts;

		$excludedTerms = aioseo()->options->sitemap->general->advancedSettings->excludeTerms;
		if ( ! empty( $this->oldOptions['modules']['aiosp_sitemap_options']['aiosp_sitemap_excl_terms'] ) ) {
			foreach ( $this->oldOptions['modules']['aiosp_sitemap_options']['aiosp_sitemap_excl_terms'] as $taxonomy ) {
				foreach ( $taxonomy['terms'] as $id ) {
					$term = get_term( $id );
					if ( ! is_a( $term, 'WP_Term' ) ) {
						continue;
					}

					$excludedTerm        = new \stdClass();
					$excludedTerm->value = $id;
					$excludedTerm->type  = $term->taxonomy;
					$excludedTerm->label = $term->name;
					$excludedTerm->link  = get_term_link( $term );

					array_push( $excludedTerms, wp_json_encode( $excludedTerm ) );
				}
			}
		}
		aioseo()->options->sitemap->general->advancedSettings->excludeTerms = $excludedTerms;
	}

	/**
	 * Migrates the objects that are included in the sitemap.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	protected function migrateIncludedObjects() {
		if (
			! isset( $this->oldOptions['modules']['aiosp_sitemap_options']['aiosp_sitemap_posttypes'] ) &&
			! isset( $this->oldOptions['modules']['aiosp_sitemap_options']['aiosp_sitemap_taxonomies'] )
		) {
			return;
		}

		if ( ! is_array( $this->oldOptions['modules']['aiosp_sitemap_options']['aiosp_sitemap_posttypes'] ) ) {
			$this->oldOptions['modules']['aiosp_sitemap_options']['aiosp_sitemap_posttypes'] = [];
		}

		if ( ! is_array( $this->oldOptions['modules']['aiosp_sitemap_options']['aiosp_sitemap_taxonomies'] ) ) {
			$this->oldOptions['modules']['aiosp_sitemap_options']['aiosp_sitemap_taxonomies'] = [];
		}

		$publicPostTypes  = aioseo()->helpers->getPublicPostTypes( true );
		$publicTaxonomies = aioseo()->helpers->getPublicTaxonomies( true );

		if ( in_array( 'all', $this->oldOptions['modules']['aiosp_sitemap_options']['aiosp_sitemap_posttypes'], true ) ) {
			aioseo()->options->sitemap->general->postTypes->all      = true;
			aioseo()->options->sitemap->general->postTypes->included = array_values( $publicPostTypes );
		} else {
			$allPostTypes = true;
			foreach ( $publicPostTypes as $postType ) {
				if ( ! in_array( $postType, $this->oldOptions['modules']['aiosp_sitemap_options']['aiosp_sitemap_posttypes'], true ) ) {
					$allPostTypes = false;
				}
			}

			aioseo()->options->sitemap->general->postTypes->all      = $allPostTypes;
			aioseo()->options->sitemap->general->postTypes->included = array_values(
				array_intersect( $publicPostTypes, $this->oldOptions['modules']['aiosp_sitemap_options']['aiosp_sitemap_posttypes'] )
			);
		}

		if ( in_array( 'all', $this->oldOptions['modules']['aiosp_sitemap_options']['aiosp_sitemap_taxonomies'], true ) ) {
			aioseo()->options->sitemap->general->taxonomies->all      = true;
			aioseo()->options->sitemap->general->taxonomies->included = array_values( $publicTaxonomies );
		} else {
			$allTaxonomies = true;
			foreach ( $publicTaxonomies as $taxonomy ) {
				if ( ! in_array( $taxonomy, $this->oldOptions['modules']['aiosp_sitemap_options']['aiosp_sitemap_taxonomies'], true ) ) {
					$allTaxonomies = false;
				}
			}

			aioseo()->options->sitemap->general->taxonomies->all      = $allTaxonomies;
			aioseo()->options->sitemap->general->taxonomies->included = array_values(
				array_intersect( $publicTaxonomies, $this->oldOptions['modules']['aiosp_sitemap_options']['aiosp_sitemap_taxonomies'] )
			);
		}
	}

	/**
	 * Migrates the additional pages that are included in the sitemap.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	private function migrateAdditionalPages() {
		if ( empty( $this->oldOptions['modules']['aiosp_sitemap_options']['aiosp_sitemap_addl_pages'] ) ) {
			return;
		}

		$pages = [];
		foreach ( $this->oldOptions['modules']['aiosp_sitemap_options']['aiosp_sitemap_addl_pages'] as $url => $values ) {
			$page               = new \stdClass();
			$page->url          = esc_url( wp_strip_all_tags( $url ) );
			$page->priority     = [ 'label' => $values['prio'], 'value' => $values['prio'] ];
			$page->frequency    = [ 'label' => $values['freq'], 'value' => $values['freq'] ];
			$page->lastModified = gmdate( 'm/d/Y', strtotime( $values['mod'] ) );

			$pages[] = wp_json_encode( $page );
		}

		aioseo()->options->sitemap->general->additionalPages->enable = true;
		aioseo()->options->sitemap->general->additionalPages->pages  = $pages;
	}

	/**
	 * Migrates the priority/frequency settings.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	private function migratePrioFreq() {
		$settings = [
			'aiosp_sitemap_prio_homepage'   => [ 'type' => 'float', 'newOption' => [ 'sitemap', 'general', 'advancedSettings', 'priority', 'homePage', 'priority' ] ],
			'aiosp_sitemap_freq_homepage'   => [ 'type' => 'string', 'newOption' => [ 'sitemap', 'general', 'advancedSettings', 'priority', 'homePage', 'frequency' ] ],
			'aiosp_sitemap_prio_post'       => [ 'type' => 'float', 'newOption' => [ 'sitemap', 'general', 'advancedSettings', 'priority', 'postTypes', 'priority' ] ],
			'aiosp_sitemap_freq_post'       => [ 'type' => 'string', 'newOption' => [ 'sitemap', 'general', 'advancedSettings', 'priority', 'postTypes', 'frequency' ] ],
			'aiosp_sitemap_prio_post_post'  => [ 'type' => 'float', 'newOption' => [ 'sitemap', 'priority', 'postTypes', 'post', 'priority' ], 'dynamic' => true ],
			'aiosp_sitemap_freq_post_post'  => [ 'type' => 'string', 'newOption' => [ 'sitemap', 'priority', 'postTypes', 'post', 'frequency' ], 'dynamic' => true ],
			'aiosp_sitemap_prio_taxonomies' => [ 'type' => 'float', 'newOption' => [ 'sitemap', 'general', 'advancedSettings', 'priority', 'taxonomies', 'priority' ] ],
			'aiosp_sitemap_freq_taxonomies' => [ 'type' => 'string', 'newOption' => [ 'sitemap', 'general', 'advancedSettings', 'priority', 'taxonomies', 'frequency' ] ],
			'aiosp_sitemap_prio_archive'    => [ 'type' => 'float', 'newOption' => [ 'sitemap', 'general', 'advancedSettings', 'priority', 'archive', 'priority' ] ],
			'aiosp_sitemap_freq_archive'    => [ 'type' => 'string', 'newOption' => [ 'sitemap', 'general', 'advancedSettings', 'priority', 'archive', 'frequency' ] ],
			'aiosp_sitemap_prio_author'     => [ 'type' => 'float', 'newOption' => [ 'sitemap', 'general', 'advancedSettings', 'priority', 'author', 'priority' ] ],
			'aiosp_sitemap_freq_author'     => [ 'type' => 'string', 'newOption' => [ 'sitemap', 'general', 'advancedSettings', 'priority', 'author', 'frequency' ] ],
		];

		foreach ( $this->oldOptions['modules']['aiosp_sitemap_options'] as $name => $value ) {
			// Ignore fixed settings.
			if ( in_array( $name, array_keys( $settings ), true ) ) {
				continue;
			}

			$type = false;
			$slug = '';
			if ( preg_match( '#aiosp_sitemap_prio_(.*)#', (string) $name, $slug ) ) {
				$type = 'priority';
			} elseif ( preg_match( '#aiosp_sitemap_freq_(.*)#', (string) $name, $slug ) ) {
				$type = 'frequency';
			}

			if ( empty( $slug ) || empty( $slug[1] ) ) {
				continue;
			}

			$objectSlug = aioseo()->helpers->pregReplace( '#post_(?!tag)|taxonomies_#', '', $slug[1] );

			if ( in_array( $objectSlug, aioseo()->helpers->getPublicPostTypes( true ), true ) ) {
				$settings[ $name ] = [
					'type'      => 'priority' === $type ? 'float' : 'string',
					'newOption' => [ 'sitemap', 'priority', 'postTypes', $objectSlug, $type ],
					'dynamic'   => true
				];
				continue;
			}

			if ( in_array( $objectSlug, aioseo()->helpers->getPublicTaxonomies( true ), true ) ) {
				$settings[ $name ] = [
					'type'      => 'priority' === $type ? 'float' : 'string',
					'newOption' => [ 'sitemap', 'priority', 'taxonomies', $objectSlug, $type ],
					'dynamic'   => true
				];
			}
		}

		$mainOptions    = aioseo()->options->noConflict();
		$dynamicOptions = aioseo()->dynamicOptions->noConflict();
		foreach ( $settings as $name => $values ) {
			// If setting is set to default, do nothing.
			if (
				empty( $this->oldOptions['modules']['aiosp_sitemap_options'][ $name ] ) ||
				'no' === $this->oldOptions['modules']['aiosp_sitemap_options'][ $name ]
			) {
				unset( $settings[ $name ] );
				continue;
			}

			// If value is "Select Individual", set grouped to false.
			$value = $this->oldOptions['modules']['aiosp_sitemap_options'][ $name ];
			if ( 'sel' === $value ) {
				if ( preg_match( '#post$#', (string) $name ) ) {
					aioseo()->options->sitemap->general->advancedSettings->priority->postTypes->grouped = false;
				} else {
					aioseo()->options->sitemap->general->advancedSettings->priority->taxonomies->grouped = false;
				}
				continue;
			}

			$object        = new \stdClass();
			$object->label = $value;
			$object->value = $value;

			$error      = false;
			$options    = ! empty( $values['dynamic'] ) ? $dynamicOptions : $mainOptions;
			$lastOption = '';
			for ( $i = 0; $i < count( $values['newOption'] ); $i++ ) {
				$lastOption = $values['newOption'][ $i ];
				if ( ! $options->has( $lastOption, false ) ) {
					$error = true;
					break;
				}

				if ( count( $values['newOption'] ) - 1 !== $i ) {
					$options = $options->$lastOption;
				}
			}

			if ( $error ) {
				continue;
			}

			$options->$lastOption = wp_json_encode( $object );
		}

		if ( count( $settings ) ) {
			$mainOptions->sitemap->general->advancedSettings->enable = true;
		}
	}

	/**
	 * Regenerates the sitemap if it is static.
	 *
	 * We need to do this since the stylesheet URLs have changed.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	private function regenerateSitemap() {
		if (
			isset( $this->oldOptions['modules']['aiosp_sitemap_options']['aiosp_sitemap_rewrite'] ) &&
			empty( $this->oldOptions['modules']['aiosp_sitemap_options']['aiosp_sitemap_rewrite'] )
		) {
			$files         = aioseo()->sitemap->file->files();
			$detectedFiles = [];
			foreach ( $files as $filename ) {
				// We don't want to delete the video sitemap here at all.
				$isVideoSitemap = preg_match( '#.*video.*#', (string) $filename ) ? true : false;
				if ( ! $isVideoSitemap ) {
					$detectedFiles[] = $filename;
				}
			}

			$fs = aioseo()->core->fs;
			if ( count( $detectedFiles ) && $fs->isWpfsValid() ) {
				foreach ( $detectedFiles as $file ) {
					$fs->fs->delete( $file, false, 'f' );
				}
			}

			aioseo()->sitemap->file->generate( true );
		}
	}
}