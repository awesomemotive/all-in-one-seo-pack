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
	 * @since 4.0.0
	 */
	public function scheduleImport() {
		try {
			if ( as_next_scheduled_action( aioseo()->importExport->yoastSeo->postActionName ) ) {
				return;
			}

			if ( ! aioseo()->core->cache->get( 'import_post_meta_yoast_seo' ) ) {
				aioseo()->core->cache->update( 'import_post_meta_yoast_seo', time(), WEEK_IN_SECONDS );
			}

			as_schedule_single_action( time(), aioseo()->importExport->yoastSeo->postActionName, [], 'aioseo' );
		} catch ( \Exception $e ) {
			// Do nothing.
		}
	}

	/**
	 * Imports the post meta.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function importPostMeta() {
		$postsPerAction  = apply_filters( 'aioseo_import_yoast_seo_posts_per_action', 100 );
		$publicPostTypes = implode( "', '", aioseo()->helpers->getPublicPostTypes( true ) );
		$timeStarted     = gmdate( 'Y-m-d H:i:s', aioseo()->core->cache->get( 'import_post_meta_yoast_seo' ) );

		$posts = aioseo()->core->db
			->start( 'posts' . ' as p' )
			->select( 'p.ID, p.post_type' )
			->leftJoin( 'aioseo_posts as ap', '`p`.`ID` = `ap`.`post_id`' )
			->whereRaw( "( p.post_type IN ( '$publicPostTypes' ) )" )
			->whereRaw( "( ap.post_id IS NULL OR ap.updated < '$timeStarted' )" )
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
			'_yoast_wpseo_primary_category'      => 'og_article_section'
		];

		foreach ( $posts as $post ) {
			$postMeta = aioseo()->core->db
				->start( 'postmeta' . ' as pm' )
				->select( 'pm.meta_key, pm.meta_value' )
				->where( 'pm.post_id', $post->ID )
				->whereRaw( "`pm`.`meta_key` LIKE '_yoast_wpseo_%'" )
				->run()
				->result();

			$categories    = aioseo()->helpers->getAllCategories( $post->ID );
			$featuredImage = get_the_post_thumbnail_url( $post->ID );
			$meta          = [
				'post_id'            => (int) $post->ID,
				'twitter_use_og'     => true,
				'og_image_type'      => $featuredImage ? 'featured' : 'content',
				'og_article_section' => ! empty( $categories ) ? $categories[0] : null
			];

			if ( ! $postMeta || ! count( $postMeta ) ) {
				$aioseoPost = Models\Post::getPost( (int) $post->ID );
				$aioseoPost->set( $meta );
				$aioseoPost->save();

				aioseo()->migration->meta->migrateAdditionalPostMeta( $post->ID );
				continue;
			}

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
					case '_yoast_wpseo_primary_category':
						$primaryCategory = get_cat_name( $value );
						foreach ( $categories as $category ) {
							if ( aioseo()->helpers->toLowerCase( $primaryCategory ) === aioseo()->helpers->toLowerCase( $category ) ) {
								$meta[ $mappedMeta[ $name ] ] = $category;
								break 2;
							}
						}

						$meta[ $mappedMeta[ $name ] ] = ! empty( $categories ) ? $categories[0] : ( ! empty( $primaryCategory ) ? $primaryCategory : '' );
						break;
					case '_yoast_wpseo_meta-robots-noindex':
					case '_yoast_wpseo_meta-robots-nofollow':
						if ( (bool) $value ) {
							$meta[ $mappedMeta[ $name ] ]       = ! empty( $value );
							$meta['robots_default'] = false;
						}
						break;
					case '_yoast_wpseo_meta-robots-adv':
						$values = explode( ',', $value );
						if ( $values ) {
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

						if ( ! in_array( $value, ImportExport\SearchAppearance::$supportedWebPageGraphs, true ) ) {
							break;
						}

						$meta[ $mappedMeta[ $name ] ] = 'WebPage';
						$meta['schema_type_options']  = wp_json_encode( [
							'webPage' => [
								'webPageType' => $value
							]
						] );
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

			if ( ! empty( $meta['keyphrases'] ) && is_array( $meta['keyphrases'] ) ) {
				$meta['keyphrases'] = wp_json_encode( $meta['keyphrases'] );
			}

			$aioseoPost = Models\Post::getPost( (int) $post->ID );
			$aioseoPost->set( $meta );
			$aioseoPost->save();

			aioseo()->migration->meta->migrateAdditionalPostMeta( $post->ID );

			// Clear the Overview cache.
			aioseo()->postSettings->clearPostTypeOverviewCache( $post->ID );
		}

		if ( count( $posts ) === $postsPerAction ) {
			try {
				as_schedule_single_action( time() + 5, aioseo()->importExport->yoastSeo->postActionName, [], 'aioseo' );
			} catch ( \Exception $e ) {
				// Do nothing.
			}
		} else {
			aioseo()->core->cache->delete( 'import_post_meta_yoast_seo' );
		}
	}
}