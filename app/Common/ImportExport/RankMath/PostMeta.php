<?php
namespace AIOSEO\Plugin\Common\ImportExport\RankMath;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AIOSEO\Plugin\Common\Models;

// phpcs:disable WordPress.Arrays.ArrayDeclarationSpacing.AssociativeArrayFound

/**
 * Imports the post meta from Rank Math.
 *
 * @since 4.0.0
 */
class PostMeta {
	/**
	 * Schedules the post meta import.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function scheduleImport() {
		try {
			if ( as_next_scheduled_action( aioseo()->importExport->rankMath->postActionName ) ) {
				return;
			}

			if ( ! aioseo()->core->cache->get( 'import_post_meta_rank_math' ) ) {
				aioseo()->core->cache->update( 'import_post_meta_rank_math', time(), WEEK_IN_SECONDS );
			}

			as_schedule_single_action( time(), aioseo()->importExport->rankMath->postActionName, [], 'aioseo' );
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
		$postsPerAction  = 100;
		$publicPostTypes = implode( "', '", aioseo()->helpers->getPublicPostTypes( true ) );
		$timeStarted     = gmdate( 'Y-m-d H:i:s', aioseo()->core->cache->get( 'import_post_meta_rank_math' ) );

		$posts = aioseo()->core->db
			->start( 'posts' . ' as p' )
			->select( 'p.ID, p.post_type' )
			->join( 'postmeta as pm', '`p`.`ID` = `pm`.`post_id`' )
			->leftJoin( 'aioseo_posts as ap', '`p`.`ID` = `ap`.`post_id`' )
			->whereRaw( "pm.meta_key LIKE 'rank_math_%'" )
			->whereRaw( "( p.post_type IN ( '$publicPostTypes' ) )" )
			->whereRaw( "( ap.post_id IS NULL OR ap.updated < '$timeStarted' )" )
			->orderBy( 'p.ID DESC' )
			->groupBy( 'p.ID' )
			->limit( $postsPerAction )
			->run()
			->result();

		if ( ! $posts || ! count( $posts ) ) {
			aioseo()->core->cache->delete( 'import_post_meta_rank_math' );

			return;
		}

		$mappedMeta = [
			'rank_math_title'                => 'title',
			'rank_math_description'          => 'description',
			'rank_math_canonical_url'        => 'canonical_url',
			'rank_math_focus_keyword'        => 'keyphrases',
			'rank_math_robots'               => '',
			'rank_math_advanced_robots'      => '',
			'rank_math_facebook_title'       => 'og_title',
			'rank_math_facebook_description' => 'og_description',
			'rank_math_facebook_image'       => 'og_image_custom_url',
			'rank_math_twitter_use_facebook' => 'twitter_use_og',
			'rank_math_twitter_title'        => 'twitter_title',
			'rank_math_twitter_description'  => 'twitter_description',
			'rank_math_twitter_image'        => 'twitter_image_custom_url',
			'rank_math_twitter_card_type'    => 'twitter_card',
			'rank_math_primary_category'     => 'primary_term',
		];

		foreach ( $posts as $post ) {
			$postMeta = aioseo()->core->db
				->start( 'postmeta' . ' as pm' )
				->select( 'pm.meta_key, pm.meta_value' )
				->where( 'pm.post_id', $post->ID )
				->whereRaw( "`pm`.`meta_key` LIKE 'rank_math_%'" )
				->run()
				->result();

			$meta = [
				'post_id' => $post->ID,
			];

			if ( ! $postMeta || ! count( $postMeta ) ) {
				$aioseoPost = Models\Post::getPost( (int) $post->ID );
				$aioseoPost->set( $meta );
				$aioseoPost->save();

				aioseo()->migration->meta->migrateAdditionalPostMeta( $post->ID );
				continue;
			}

			foreach ( $postMeta as $record ) {
				$name  = $record->meta_key;
				$value = $record->meta_value;

				if (
					! in_array( $post->post_type, [ 'page', 'attachment' ], true ) &&
					preg_match( '#^rank_math_schema_([^\s]*)$#', $name, $match ) && ! empty( $match[1] )
				) {
					switch ( $match[1] ) {
						case 'Article':
						case 'NewsArticle':
						case 'BlogPosting':
							$meta['schema_type'] = 'Article';
							$meta['schema_type_options'] = wp_json_encode(
								[ 'article' => [ 'articleType' => $match[1] ] ]
							);
							break;
						default:
							break;
					}
				}

				if ( ! in_array( $name, array_keys( $mappedMeta ), true ) ) {
					continue;
				}

				switch ( $name ) {
					case 'rank_math_focus_keyword':
						$keyphrases     = array_map( 'trim', explode( ',', $value ) );
						$keyphraseArray = [
							'focus'      => [ 'keyphrase' => aioseo()->helpers->sanitizeOption( $keyphrases[0] ) ],
							'additional' => []
						];
						unset( $keyphrases[0] );
						foreach ( $keyphrases as $keyphrase ) {
							$keyphraseArray['additional'][] = [ 'keyphrase' => aioseo()->helpers->sanitizeOption( $keyphrase ) ];
						}

						$meta['keyphrases'] = wp_json_encode( $keyphraseArray );
						break;
					case 'rank_math_robots':
						$value = aioseo()->helpers->maybeUnserialize( $value );
						if ( ! empty( $value ) ) {
							$meta['robots_default'] = false;
							foreach ( $value as $robotsName ) {
								$meta[ "robots_$robotsName" ] = true;
							}
						}
						break;
					case 'rank_math_advanced_robots':
						$value = aioseo()->helpers->maybeUnserialize( $value );
						if ( ! empty( $value['max-snippet'] ) && intval( $value['max-snippet'] ) ) {
							$meta['robots_max_snippet'] = intval( $value['max-snippet'] );
						}
						if ( isset( $value['max-video-preview'] ) && is_numeric( $value['max-video-preview'] ) ) {
							$meta['robots_max_videopreview'] = intval( $value['max-video-preview'] );
						}
						if ( ! empty( $value['max-image-preview'] ) ) {
							$meta['robots_max_imagepreview'] = aioseo()->helpers->sanitizeOption( lcfirst( $value['max-image-preview'] ) );
						}
						break;
					case 'rank_math_facebook_image':
						$meta['og_image_type']        = 'custom_image';
						$meta[ $mappedMeta[ $name ] ] = esc_url( $value );
						break;
					case 'rank_math_twitter_image':
						$meta['twitter_image_type']   = 'custom_image';
						$meta[ $mappedMeta[ $name ] ] = esc_url( $value );
						break;
					case 'rank_math_twitter_card_type':
						preg_match( '#large#', $value, $match );
						$meta[ $mappedMeta[ $name ] ] = ! empty( $match ) ? 'summary_large_image' : 'summary';
						break;
					case 'rank_math_twitter_use_facebook':
						$meta[ $mappedMeta[ $name ] ] = 'on' === $value;
						break;
					case 'rank_math_primary_category':
						$taxonomy                     = 'category';
						$options                      = new \stdClass();
						$options->$taxonomy           = (int) $value;
						$meta[ $mappedMeta[ $name ] ] = wp_json_encode( $options );
						break;
					case 'rank_math_title':
					case 'rank_math_description':
						if ( 'page' === $post->post_type ) {
							$value = aioseo()->helpers->pregReplace( '#%category%#', '', $value );
							$value = aioseo()->helpers->pregReplace( '#%excerpt%#', '', $value );
						}
						$value = aioseo()->importExport->rankMath->helpers->macrosToSmartTags( $value );
					default:
						$meta[ $mappedMeta[ $name ] ] = esc_html( wp_strip_all_tags( strval( $value ) ) );
						break;
				}
			}

			$aioseoPost = Models\Post::getPost( $post->ID );
			$aioseoPost->set( $meta );
			$aioseoPost->save();

			aioseo()->migration->meta->migrateAdditionalPostMeta( $post->ID );

			// Clear the Overview cache.
			aioseo()->postSettings->clearPostTypeOverviewCache( $post->ID );
		}

		if ( count( $posts ) === $postsPerAction ) {
			try {
				as_schedule_single_action( time() + 5, aioseo()->importExport->rankMath->postActionName, [], 'aioseo' );
			} catch ( \Exception $e ) {
				// Do nothing.
			}
		} else {
			aioseo()->core->cache->delete( 'import_post_meta_rank_math' );
		}
	}
}