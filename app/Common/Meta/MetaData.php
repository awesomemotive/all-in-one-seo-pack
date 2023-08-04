<?php
namespace AIOSEO\Plugin\Common\Meta;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AIOSEO\Plugin\Common\Models;

/**
 * Handles fetching metadata for the current object.
 *
 * @since 4.0.0
 */
class MetaData {
	/**
	 * The cached meta data for posts.
	 *
	 * @since 4.1.7
	 *
	 * @var array
	 */
	private $posts = [];

	/**
	 * Class constructor.
	 *
	 * @since 4.0.0
	 */
	public function __construct() {
		add_action( 'wpml_pro_translation_completed', [ $this, 'updateWpmlLocalization' ], 1000, 3 );
	}

	/**
	 * Update the localized data in our posts table.
	 *
	 * @since 4.0.0
	 *
	 * @param  integer $postId The post ID.
	 * @param  array   $fields An array of fields to update.
	 * @return void
	 */
	public function updateWpmlLocalization( $postId, $fields = [], $job = null ) {
		$aioseoFields = [
			'_aioseo_title',
			'_aioseo_description',
			'_aioseo_keywords',
			'_aioseo_og_title',
			'_aioseo_og_description',
			'_aioseo_twitter_title',
			'_aioseo_twitter_description'
		];

		$parentId    = $job->original_doc_id;
		$parentPost  = Models\Post::getPost( $parentId );
		$currentPost = Models\Post::getPost( $postId );
		$columns     = $parentPost->getColumns();
		foreach ( $columns as $column => $value ) {
			// Skip the ID columns.
			if ( 'id' === $column || 'post_id' === $column ) {
				continue;
			}

			$currentPost->$column = $parentPost->$column;
		}

		$currentPost->post_id = $postId;

		foreach ( $aioseoFields as $aioseoField ) {
			if ( ! empty( $fields[ 'field-' . $aioseoField . '-0' ] ) ) {
				$value = $fields[ 'field-' . $aioseoField . '-0' ]['data'];
				if ( '_aioseo_keywords' === $aioseoField ) {
					$value = explode( ',', $value );
					foreach ( $value as $k => $keyword ) {
						$value[ $k ] = [
							'label' => $keyword,
							'value' => $keyword
						];
					}

					$value = wp_json_encode( $value );
				}
				$currentPost->{ str_replace( '_aioseo_', '', $aioseoField ) } = $value;
			}
		}

		$currentPost->save();
	}

	/**
	 * Returns the metadata for the current object.
	 *
	 * @since 4.0.0
	 *
	 * @param  \WP_Post         $post The post object (optional).
	 * @return Models\Post|bool       The meta data or false.
	 */
	public function getMetaData( $post = null ) {
		if ( ! $post ) {
			$post = aioseo()->helpers->getPost();
		}

		if ( $post ) {
			$post = is_object( $post ) ? $post : aioseo()->helpers->getPost( $post );
			// If we still have no post, let's return false.
			if ( ! is_a( $post, 'WP_Post' ) ) {
				return false;
			}

			if ( isset( $this->posts[ $post->ID ] ) ) {
				return $this->posts[ $post->ID ];
			}

			$this->posts[ $post->ID ] = Models\Post::getPost( $post->ID );

			if ( ! $this->posts[ $post->ID ]->exists() ) {
				$migratedMeta = aioseo()->migration->meta->getMigratedPostMeta( $post->ID );
				if ( ! empty( $migratedMeta ) ) {
					foreach ( $migratedMeta as $k => $v ) {
						$this->posts[ $post->ID ]->{$k} = $v;
					}

					$this->posts[ $post->ID ]->save();
				}
			}

			return $this->posts[ $post->ID ];
		}

		return false;
	}

	/**
	 * Returns the cached OG image from the meta data.
	 *
	 * @since 4.1.6
	 *
	 * @param  Object $metaData The meta data object.
	 * @return array            An array of image data.
	 */
	public function getCachedOgImage( $metaData ) {
		return [
			$metaData->og_image_url,
			isset( $metaData->og_image_width ) ? $metaData->og_image_width : null,
			isset( $metaData->og_image_height ) ? $metaData->og_image_height : null
		];
	}

	/**
	 * Busts the meta data cache for a given post.
	 *
	 * @since 4.1.7
	 *
	 * @param  int         $postId   The post ID.
	 * @param  Models\Post $metaData The meta data.
	 * @return void
	 */
	public function bustPostCache( $postId, $metaData = null ) {
		if ( null === $metaData || ! is_a( $metaData, 'AIOSEO\Plugin\Common\Models\Post' ) ) {
			unset( $this->posts[ $postId ] );
		}

		$this->posts[ $postId ] = $metaData;
	}
}