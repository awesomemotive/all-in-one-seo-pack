<?php
namespace AIOSEO\Plugin\Common\ImportExport\SeoPress;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AIOSEO\Plugin\Common\Models;

// phpcs:disable WordPress.Arrays.ArrayDeclarationSpacing.AssociativeArrayFound

/**
 * Imports the post meta from SEOPress.
 *
 * @since 4.1.4
 */
class PostMeta {
	/**
	 * The mapped meta
	 *
	 * @since 4.1.4
	 *
	 * @var array
	 */
	private $mappedMeta = [
		'_seopress_analysis_target_kw'   => '',
		'_seopress_robots_archive'       => 'robots_noarchive',
		'_seopress_robots_canonical'     => 'canonical_url',
		'_seopress_robots_follow'        => 'robots_nofollow',
		'_seopress_robots_imageindex'    => 'robots_noimageindex',
		'_seopress_robots_index'         => 'robots_noindex',
		'_seopress_robots_odp'           => 'robots_noodp',
		'_seopress_robots_snippet'       => 'robots_nosnippet',
		'_seopress_social_twitter_desc'  => 'twitter_description',
		'_seopress_social_twitter_img'   => 'twitter_image_custom_url',
		'_seopress_social_twitter_title' => 'twitter_title',
		'_seopress_social_fb_desc'       => 'og_description',
		'_seopress_social_fb_img'        => 'og_image_custom_url',
		'_seopress_social_fb_title'      => 'og_title',
		'_seopress_titles_desc'          => 'description',
		'_seopress_titles_title'         => 'title',
		'_seopress_robots_primary_cat'   => 'primary_term'
	];

	/**
	 * Class constructor.
	 *
	 * @since 4.1.4
	 */
	public function scheduleImport() {
		if ( aioseo()->actionScheduler->scheduleSingle( aioseo()->importExport->seoPress->postActionName, 0 ) ) {
			if ( ! aioseo()->core->cache->get( 'import_post_meta_seopress' ) ) {
				aioseo()->core->cache->update( 'import_post_meta_seopress', time(), WEEK_IN_SECONDS );
			}
		}
	}

	/**
	 * Imports the post meta.
	 *
	 * @since 4.1.4
	 *
	 * @return void
	 */
	public function importPostMeta() {
		$postsPerAction  = apply_filters( 'aioseo_import_seopress_posts_per_action', 100 );
		$publicPostTypes = implode( "', '", aioseo()->helpers->getPublicPostTypes( true ) );
		$timeStarted     = gmdate( 'Y-m-d H:i:s', aioseo()->core->cache->get( 'import_post_meta_seopress' ) );

		$posts = aioseo()->core->db
			->start( 'posts as p' )
			->select( 'p.ID, p.post_type' )
			->join( 'postmeta as pm', '`p`.`ID` = `pm`.`post_id`' )
			->leftJoin( 'aioseo_posts as ap', '`p`.`ID` = `ap`.`post_id`' )
			->whereRaw( "pm.meta_key LIKE '_seopress_%'" )
			->whereRaw( "( p.post_type IN ( '$publicPostTypes' ) )" )
			->whereRaw( "( ap.post_id IS NULL OR ap.updated < '$timeStarted' )" )
			->orderBy( 'p.ID DESC' )
			->limit( $postsPerAction )
			->run()
			->result();

		if ( ! $posts || ! count( $posts ) ) {
			aioseo()->core->cache->delete( 'import_post_meta_seopress' );

			return;
		}

		foreach ( $posts as $post ) {
			$postMeta = aioseo()->core->db
				->start( 'postmeta' . ' as pm' )
				->select( 'pm.meta_key, pm.meta_value' )
				->where( 'pm.post_id', $post->ID )
				->whereRaw( "`pm`.`meta_key` LIKE '_seopress_%'" )
				->run()
				->result();

			$meta = array_merge( [
				'post_id' => (int) $post->ID,
			], $this->getMetaData( $postMeta, $post->ID ) );

			if ( ! $postMeta || ! count( $postMeta ) ) {
				$aioseoPost = Models\Post::getPost( (int) $post->ID );
				$aioseoPost->set( $meta );
				$aioseoPost->save();

				aioseo()->migration->meta->migrateAdditionalPostMeta( $post->ID );

				continue;
			}

			$aioseoPost = Models\Post::getPost( (int) $post->ID );
			$aioseoPost->set( $meta );
			$aioseoPost->save();

			aioseo()->migration->meta->migrateAdditionalPostMeta( $post->ID );

			// Clear the Overview cache.
			aioseo()->postSettings->clearPostTypeOverviewCache( $post->ID );
		}

		if ( count( $posts ) === $postsPerAction ) {
			aioseo()->actionScheduler->scheduleSingle( aioseo()->importExport->seoPress->postActionName, 5, [], true );
		} else {
			aioseo()->core->cache->delete( 'import_post_meta_seopress' );
		}
	}

	/**
	 * Get the meta data by post meta.
	 *
	 * @since 4.1.4
	 *
	 * @param object $postMeta The post meta from database.
	 * @return array           The meta data.
	 */
	public function getMetaData( $postMeta, $postId ) { // phpcs:ignore VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
		$meta = [];
		foreach ( $postMeta as $record ) {
			$name  = $record->meta_key;
			$value = $record->meta_value;

			if ( ! in_array( $name, array_keys( $this->mappedMeta ), true ) ) {
				continue;
			}

			switch ( $name ) {
				case '_seopress_analysis_target_kw':
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
				case '_seopress_robots_snippet':
				case '_seopress_robots_archive':
				case '_seopress_robots_imageindex':
				case '_seopress_robots_odp':
				case '_seopress_robots_follow':
				case '_seopress_robots_index':
					if ( 'yes' === $value ) {
						$meta['robots_default']       = false;
						$meta[ $this->mappedMeta[ $name ] ] = true;
					}
					break;
				case '_seopress_social_twitter_img':
					$meta['twitter_use_og']       = false;
					$meta['twitter_image_type']   = 'custom_image';
					$meta[ $this->mappedMeta[ $name ] ] = esc_url( $value );
					break;
				case '_seopress_social_fb_img':
					$meta['og_image_type']        = 'custom_image';
					$meta[ $this->mappedMeta[ $name ] ] = esc_url( $value );
					break;
				case '_seopress_robots_primary_cat':
					$taxonomy                           = 'category';
					$options                            = new \stdClass();
					$options->$taxonomy                 = (int) $value;
					$meta[ $this->mappedMeta[ $name ] ] = wp_json_encode( $options );
					break;
				case '_seopress_titles_title':
				case '_seopress_titles_desc':
					$value = aioseo()->importExport->seoPress->helpers->macrosToSmartTags( $value );
				default:
					$meta[ $this->mappedMeta[ $name ] ] = esc_html( wp_strip_all_tags( strval( $value ) ) );
					break;
			}
		}

		return $meta;
	}
}