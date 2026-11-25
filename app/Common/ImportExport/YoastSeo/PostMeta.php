<?php
namespace AIOSEO\Plugin\Common\ImportExport\YoastSeo;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AIOSEO\Plugin\Common\ImportExport;
use AIOSEO\Plugin\Common\Models;

// phpcs:disable WordPress.Arrays.ArrayDeclarationSpacing.AssociativeArrayFound

/**
 * Imports the post meta from Yoast SEO.
 *
 * @since 4.0.0
 */
class PostMeta {
	/**
	 * Class constructor.
	 *
	 * @since   4.0.0
	 * @version 5.0.6 Record the start time one second ahead.
	 */
	public function scheduleImport() {
		try {
			if ( as_next_scheduled_action( aioseo()->importExport->yoastSeo->postActionName ) ) {
				return;
			}

			if ( ! aioseo()->core->cache->get( 'import_post_meta_yoast_seo' ) ) {
				// A scan can still save a row in this second, so a row updated in it must count as not imported yet.
				aioseo()->core->cache->update( 'import_post_meta_yoast_seo', time() + 1, WEEK_IN_SECONDS );
			}

			as_schedule_single_action( time(), aioseo()->importExport->yoastSeo->postActionName, [], 'aioseo' );
		} catch ( \Exception $e ) {
			// Do nothing.
		}
	}

	/**
	 * Imports the post meta.
	 *
	 * @since   4.0.0
	 * @version 5.0.6 Also imports posts left on Yoast's defaults; writes schema types to `schema`.
	 *
	 * @return void
	 */
	public function importPostMeta() {
		$postsPerAction  = apply_filters( 'aioseo_import_yoast_seo_posts_per_action', 100 );
		$publicPostTypes = aioseo()->helpers->getPublicPostTypes( true );
		$timeStarted     = esc_sql( gmdate( 'Y-m-d H:i:s', aioseo()->core->cache->get( 'import_post_meta_yoast_seo' ) ) );

		$query = aioseo()->core->db
			->start( 'posts' . ' as p' )
			->select( 'p.ID, p.post_type' )
			->leftJoin( 'aioseo_posts as ap', '`p`.`ID` = `ap`.`post_id`' )
			->leftJoin( 'postmeta as pm', "`p`.`ID` = `pm`.`post_id` AND `pm`.`meta_key` LIKE '_yoast_wpseo_%'" )
			->whereIn( 'p.post_type', $publicPostTypes )
			->whereRaw( "( ap.post_id IS NULL OR ap.updated < '$timeStarted' )" );

		// Yoast only stores post meta for values that deviate from its defaults, so a post left on
		// those defaults is invisible here and any stale AIOSEO data on it would survive the import.
		// Its indexable table is the only record of which posts it actually manages.
		if ( aioseo()->core->db->externalTableExists( 'yoast_indexable' ) ) {
			$query
				->leftJoin( 'yoast_indexable as yi', "`p`.`ID` = `yi`.`object_id` AND `yi`.`object_type` = 'post'" )
				->whereRaw( '( pm.post_id IS NOT NULL OR ( yi.object_id IS NOT NULL AND ap.post_id IS NOT NULL ) )' );
		} else {
			$query->whereRaw( 'pm.post_id IS NOT NULL' );
		}

		$posts = $query
			->groupBy( 'p.ID' )
			->orderBy( 'p.ID DESC' )
			->limit( $postsPerAction )
			->run()
			->result();

		if ( ! $posts || ! count( $posts ) ) {
			aioseo()->core->cache->delete( 'import_post_meta_yoast_seo' );

			return;
		}

		$mappedMeta = [
			'_yoast_wpseo_title'                 => 'title',
			'_yoast_wpseo_metadesc'              => 'description',
			'_yoast_wpseo_canonical'             => 'canonical_url',
			'_yoast_wpseo_meta-robots-noindex'   => 'robots_noindex',
			'_yoast_wpseo_meta-robots-nofollow'  => 'robots_nofollow',
			'_yoast_wpseo_meta-robots-adv'       => '',
			'_yoast_wpseo_focuskw'               => '',
			'_yoast_wpseo_focuskeywords'         => '',
			'_yoast_wpseo_opengraph-title'       => 'og_title',
			'_yoast_wpseo_opengraph-description' => 'og_description',
			'_yoast_wpseo_opengraph-image'       => 'og_image_custom_url',
			'_yoast_wpseo_twitter-title'         => 'twitter_title',
			'_yoast_wpseo_twitter-description'   => 'twitter_description',
			'_yoast_wpseo_twitter-image'         => 'twitter_image_custom_url',
			'_yoast_wpseo_schema_page_type'      => '',
			'_yoast_wpseo_schema_article_type'   => '',
			'_yoast_wpseo_is_cornerstone'        => 'pillar_content'
		];

		foreach ( $posts as $post ) {
			$postMeta = aioseo()->core->db
				->start( 'postmeta' . ' as pm' )
				->select( 'pm.meta_key, pm.meta_value' )
				->where( 'pm.post_id', $post->ID )
				->whereLike( 'pm.meta_key', '_yoast_wpseo_%', true )
				->run()
				->result();

			$postMeta = $postMeta ? $postMeta : [];

			$featuredImage = get_the_post_thumbnail_url( $post->ID );
			$meta          = [
				'post_id'                  => (int) $post->ID,
				'twitter_use_og'           => true,
				'og_image_type'            => $featuredImage ? 'featured' : 'content',
				'pillar_content'           => 0,
				'canonical_url'            => '',
				'robots_default'           => true,
				'robots_noarchive'         => false,
				'robots_nofollow'          => false,
				'robots_noimageindex'      => false,
				'robots_noindex'           => false,
				'robots_noodp'             => false,
				'robots_nosnippet'         => false,
				'title'                    => '',
				'description'              => '',
				'og_title'                 => '',
				'og_description'           => '',
				'og_image_custom_url'      => '',
				'twitter_title'            => '',
				'twitter_description'      => '',
				'twitter_image_custom_url' => '',
				'twitter_image_type'       => 'default',
				'keyphrases'               => null,
				'primary_term'             => null,
				'schema_type'              => 'default',
				'schema_type_options'      => null
			];

			$title = '';
			foreach ( $postMeta as $record ) {
				$name  = $record->meta_key;
				$value = $record->meta_value;

				// Handles primary taxonomy terms.
				// We need to handle it separately because it's stored in a different format.
				if ( false !== stripos( $name, '_yoast_wpseo_primary_' ) ) {
					sscanf( $name, '_yoast_wpseo_primary_%s', $taxonomy );
					if ( null === $taxonomy ) {
						continue;
					}

					$options = new \stdClass();
					if ( isset( $meta['primary_term'] ) ) {
						$options = json_decode( $meta['primary_term'] );
					}

					$options->$taxonomy   = (int) $value;
					$meta['primary_term'] = wp_json_encode( $options );
				}

				if ( ! in_array( $name, array_keys( $mappedMeta ), true ) ) {
					continue;
				}

				switch ( $name ) {
					case '_yoast_wpseo_meta-robots-noindex':
					case '_yoast_wpseo_meta-robots-nofollow':
						if ( (bool) $value ) {
							$meta[ $mappedMeta[ $name ] ] = (bool) $value;
							$meta['robots_default']       = false;
						}
						break;
					case '_yoast_wpseo_meta-robots-adv':
						$supportedValues = [ 'index', 'noarchive', 'noimageindex', 'nosnippet' ];
						foreach ( $supportedValues as $val ) {
							$meta[ "robots_$val" ] = false;
						}

						// This is a separated foreach so we can import any and all values.
						$values = explode( ',', $value );
						if ( $values ) {
							$meta['robots_default'] = false;

							foreach ( $values as $value ) {
								$meta[ "robots_$value" ] = true;
							}
						}
						break;
					case '_yoast_wpseo_canonical':
						$meta[ $mappedMeta[ $name ] ] = esc_url( $value );
						break;
					case '_yoast_wpseo_opengraph-image':
						$meta['og_image_type']        = 'custom_image';
						$meta[ $mappedMeta[ $name ] ] = esc_url( $value );
						break;
					case '_yoast_wpseo_twitter-image':
						$meta['twitter_use_og']       = false;
						$meta['twitter_image_type']   = 'custom_image';
						$meta[ $mappedMeta[ $name ] ] = esc_url( $value );
						break;
					case '_yoast_wpseo_schema_page_type':
						$value = aioseo()->helpers->pregReplace( '#\s#', '', $value );
						if ( in_array( $post->post_type, [ 'post', 'page', 'attachment' ], true ) ) {
							break;
						}

						// AIOSEO's FAQ Page graph outputs nothing without questions, and Yoast keeps those in its blocks, not in meta.
						if ( 'FAQPage' === $value || ! in_array( $value, ImportExport\SearchAppearance::$supportedWebPageGraphs, true ) ) {
							break;
						}

						$options = new \stdClass();
						if ( isset( $meta['schema_type_options'] ) ) {
							$options = json_decode( $meta['schema_type_options'] );
						}

						$options->webPage = [ 'webPageType' => $value ];

						$meta['schema_type']         = 'WebPage';
						$meta['schema_type_options'] = wp_json_encode( $options );
						break;
					case '_yoast_wpseo_schema_article_type':
						$value = aioseo()->helpers->pregReplace( '#\s#', '', $value );
						if ( 'none' === lcfirst( $value ) ) {
							$meta[ $mappedMeta[ $name ] ] = 'None';
							break;
						}

						if ( in_array( $post->post_type, [ 'page', 'attachment' ], true ) ) {
							break;
						}

						$options = new \stdClass();
						if ( isset( $meta['schema_type_options'] ) ) {
							$options = json_decode( $meta['schema_type_options'] );
						}

						$options->article = [ 'articleType' => 'Article' ];
						if ( in_array( $value, ImportExport\SearchAppearance::$supportedArticleGraphs, true ) ) {
							$options->article = [ 'articleType' => $value ];
						} else {
							$options->article = [ 'articleType' => 'BlogPosting' ];
						}

						$meta['schema_type']         = 'Article';
						$meta['schema_type_options'] = wp_json_encode( $options );
						break;
					case '_yoast_wpseo_focuskw':
						$focusKeyphrase = [
							'focus' => [ 'keyphrase' => aioseo()->helpers->sanitizeOption( $value ) ]
						];

						// Merge with existing keyphrases if the array key already exists.
						if ( ! empty( $meta['keyphrases'] ) ) {
							$meta['keyphrases'] = array_merge( $meta['keyphrases'], $focusKeyphrase );
						} else {
							$meta['keyphrases'] = $focusKeyphrase;
						}
						break;
					case '_yoast_wpseo_focuskeywords':
						$keyphrases = [];
						if ( ! empty( $meta[ $mappedMeta[ $name ] ] ) ) {
							$keyphrases = (array) json_decode( $meta[ $mappedMeta[ $name ] ] );
						}

						$yoastKeyphrases = json_decode( $value, true );
						if ( is_array( $yoastKeyphrases ) ) {
							foreach ( $yoastKeyphrases as $yoastKeyphrase ) {
								if ( ! empty( $yoastKeyphrase['keyword'] ) ) {
									$keyphrase = [ 'keyphrase' => aioseo()->helpers->sanitizeOption( $yoastKeyphrase['keyword'] ) ];

									if ( ! isset( $keyphrases['additional'] ) ) {
										$keyphrases['additional'] = [];
									}

									$keyphrases['additional'][] = $keyphrase;
								}
							}
						}

						if ( ! empty( $keyphrases ) ) {
							// Merge with existing keyphrases if the array key already exists.
							if ( ! empty( $meta['keyphrases'] ) ) {
								$meta['keyphrases'] = array_merge( $meta['keyphrases'], $keyphrases );
							} else {
								$meta['keyphrases'] = $keyphrases;
							}
						}
						break;
					case '_yoast_wpseo_title':
					case '_yoast_wpseo_metadesc':
					case '_yoast_wpseo_opengraph-title':
					case '_yoast_wpseo_opengraph-description':
					case '_yoast_wpseo_twitter-title':
					case '_yoast_wpseo_twitter-description':
						if ( 'page' === $post->post_type ) {
							$value = aioseo()->helpers->pregReplace( '#%%primary_category%%#', '', $value );
							$value = aioseo()->helpers->pregReplace( '#%%excerpt%%#', '', $value );
						}

						if ( '_yoast_wpseo_twitter-title' === $name || '_yoast_wpseo_twitter-description' === $name ) {
							$meta['twitter_use_og'] = false;
						}

						$value = aioseo()->importExport->yoastSeo->helpers->macrosToSmartTags( $value, 'post', $post->post_type );

						if ( '_yoast_wpseo_title' === $name ) {
							$title = $value;
						}

						$meta[ $mappedMeta[ $name ] ] = esc_html( wp_strip_all_tags( strval( $value ) ) );
						break;
					case '_yoast_wpseo_is_cornerstone':
						$meta['pillar_content'] = (bool) $value ? 1 : 0;
						break;
					default:
						$meta[ $mappedMeta[ $name ] ] = esc_html( wp_strip_all_tags( strval( $value ) ) );
						break;
				}
			}

			// Resetting the `twitter_use_og` option if the user has a custom title and no twitter title.
			if ( $meta['twitter_use_og'] && $title && empty( $meta['twitter_title'] ) ) {
				$meta['twitter_use_og'] = false;
				$meta['twitter_title']  = $title;
			}

			// The keyphrases column is only kept for backwards compatibility; the post editor reads the keyword columns.
			$meta = array_merge( $meta, Models\Post::getKeywordColumnsFromKeyphrases( $meta['keyphrases'] ) );

			$aioseoPost = Models\Post::getPost( (int) $post->ID );
			$aioseoPost->set( $meta );

			// The legacy schema_type columns only reach the schema column when it is empty, so apply the Yoast types to it directly.
			$schema = $this->applySchemaTypes( $aioseoPost->schema, $meta['schema_type_options'], get_post( $post->ID ) );
			if ( null !== $schema ) {
				$aioseoPost->schema = $schema;
			}
			$aioseoPost->save();

			aioseo()->migration->meta->migrateAdditionalPostMeta( $post->ID );

			// Clear the Overview cache.
			aioseo()->postSettings->clearPostTypeOverviewCache( $post->ID );
		}

		if ( count( $posts ) === $postsPerAction ) {
			try {
				as_schedule_single_action( time() + 30, aioseo()->importExport->yoastSeo->postActionName, [], 'aioseo' );
			} catch ( \Exception $e ) {
				// Do nothing.
			}
		} else {
			aioseo()->core->cache->delete( 'import_post_meta_yoast_seo' );
		}
	}

	/**
	 * Applies the imported Yoast page/article types to the post's schema.
	 *
	 * NOTE: Once Yoast is back on its default type, a re-import reverts what an earlier import changed.
	 *
	 * @since 5.0.6
	 *
	 * @param  object|string|null $schema            The post's current schema.
	 * @param  string|null        $schemaTypeOptions The JSON-encoded legacy schema type options.
	 * @param  \WP_Post|null      $post              The post.
	 * @return object|null                           The updated schema, or null if it doesn't change.
	 */
	private function applySchemaTypes( $schema, $schemaTypeOptions, $post ) {
		$schemaTypeOptions = json_decode( (string) $schemaTypeOptions );
		$types             = [
			'WebPage' => ! empty( $schemaTypeOptions->webPage->webPageType ) ? $schemaTypeOptions->webPage->webPageType : '',
			'Article' => ! empty( $schemaTypeOptions->article->articleType ) ? $schemaTypeOptions->article->articleType : ''
		];

		$graphs      = is_object( $schema ) && ! empty( $schema->graphs ) && is_array( $schema->graphs ) ? $schema->graphs : [];
		$hasImported = false;
		foreach ( $graphs as $graph ) {
			if ( $this->isImportedGraph( $graph ) || isset( $graph->importedOriginalType ) ) {
				$hasImported = true;
				break;
			}
		}

		if ( ! $hasImported && ! array_filter( $types ) ) {
			return null;
		}

		$schema         = Models\Post::getDefaultSchemaOptions( $schema, $post );
		$graphs         = is_array( $schema->graphs ) ? array_values( $schema->graphs ) : [];
		$defaultEnabled = ! empty( $schema->default->isEnabled );
		$replaced       = false;
		$restoreDefault = false;
		foreach ( $types as $graphName => $type ) {
			$imported = [];
			$existing = null;
			foreach ( $graphs as $index => $graph ) {
				if ( ! is_object( $graph ) || ! isset( $graph->graphName ) || $graphName !== $graph->graphName || ! isset( $graph->properties ) ) {
					continue;
				}

				if ( $this->isImportedGraph( $graph ) ) {
					$imported[] = $index;
				} elseif ( null === $existing ) {
					$existing = $graph;
				}

				// A graph of the user's that an earlier import retyped goes back to its own type.
				if ( ! $type && isset( $graph->importedOriginalType ) ) {
					$graph->properties->type = $graph->importedOriginalType;
					$restoreDefault          = $restoreDefault || ! empty( $graph->replacedDefault );
					unset( $graph->importedOriginalType, $graph->replacedDefault );
				}
			}

			if ( ! $type ) {
				foreach ( $imported as $index ) {
					$restoreDefault = $restoreDefault || ! empty( $graphs[ $index ]->replacedDefault );
					unset( $graphs[ $index ] );
				}

				continue;
			}

			if ( $imported ) {
				$graphs[ array_shift( $imported ) ]->properties->type = $type;
				foreach ( $imported as $index ) {
					unset( $graphs[ $index ] );
				}

				continue;
			}

			// Yoast only supplies the type, so keep everything else the user configured on a matching graph.
			/** @var \stdClass|null $existing */
			if ( null !== $existing ) {
				if ( ! isset( $existing->importedOriginalType ) ) {
					$existing->importedOriginalType = isset( $existing->properties->type ) ? $existing->properties->type : '';
					$existing->replacedDefault      = $defaultEnabled;
				}

				$existing->properties->type = $type;
				$replaced                   = true;

				continue;
			}

			$graph                  = json_decode( (string) wp_json_encode( $this->getSchemaGraph( $graphName, $type ) ) );
			$graph->replacedDefault = $defaultEnabled;
			$graphs[]               = $graph;
			$replaced               = true;
		}

		$schema->graphs = array_values( $graphs );
		if ( $replaced ) {
			$schema->default->isEnabled = false;
		}

		// The default graph comes back once no imported or retyped graph is left in its place.
		if ( $restoreDefault ) {
			$hasImported = false;
			foreach ( $schema->graphs as $graph ) {
				$hasImported = $hasImported || $this->isImportedGraph( $graph ) || isset( $graph->importedOriginalType );
			}

			$schema->default->isEnabled = ! $hasImported;
		}

		return $schema;
	}

	/**
	 * Checks whether the given schema graph was added by this importer.
	 *
	 * @since 5.0.6
	 *
	 * @param  mixed $graph The schema graph.
	 * @return bool         Whether the graph was added by this importer.
	 */
	private function isImportedGraph( $graph ) {
		return is_object( $graph ) && isset( $graph->importedFrom ) && 'yoastSeo' === $graph->importedFrom;
	}

	/**
	 * Returns a new schema graph for the given Yoast type, shaped like the ones the legacy schema migration creates.
	 *
	 * @since 5.0.6
	 *
	 * @param  string $graphName The graph name (WebPage or Article).
	 * @param  string $type      The graph subtype.
	 * @return array             The graph.
	 */
	private function getSchemaGraph( $graphName, $type ) {
		if ( 'Article' === $graphName ) {
			return [
				'id'           => '#aioseo-article-' . uniqid(),
				'slug'         => 'article',
				'graphName'    => 'Article',
				'label'        => __( 'Article', 'all-in-one-seo-pack' ),
				'importedFrom' => 'yoastSeo',
				'properties'   => [
					'type'        => $type,
					'name'        => '#post_title',
					'headline'    => '#post_title',
					'description' => '#post_excerpt',
					'image'       => '',
					'keywords'    => '',
					'author'      => [
						'name' => '#author_name',
						'url'  => '#author_url'
					],
					'dates'       => [
						'include'       => true,
						'datePublished' => '',
						'dateModified'  => ''
					]
				]
			];
		}

		return [
			'id'           => '#aioseo-web-page-' . uniqid(),
			'slug'         => 'web-page',
			'graphName'    => 'WebPage',
			'label'        => __( 'Web Page', 'all-in-one-seo-pack' ),
			'importedFrom' => 'yoastSeo',
			'properties'   => [
				'type'        => $type,
				'name'        => '',
				'description' => ''
			]
		];
	}
}