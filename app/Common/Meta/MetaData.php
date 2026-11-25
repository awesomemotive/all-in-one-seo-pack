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
		add_action( 'icl_make_duplicate', [ $this, 'duplicateWpmlSeoData' ], 10, 4 );
	}

	/**
	 * Update the localized data in our posts table.
	 *
	 * @since   4.0.0
	 * @version 5.0.6 Restores every column in {@see Models\Post::getLocalizedMetaMap()}.
	 * @version 5.0.6 Sets truseo_locale from the translation's own language.
	 *
	 * @param  integer     $postId The post ID.
	 * @param  array       $fields An array of fields to update.
	 * @param  object|null $job    The WPML translation job.
	 * @return void
	 */
	public function updateWpmlLocalization( $postId, $fields = [], $job = null ) {
		if ( empty( $job->original_doc_id ) ) {
			return;
		}

		$parentId    = $job->original_doc_id;
		$parentPost  = Models\Post::getPost( $parentId );
		$currentPost = Models\Post::getPost( $postId );

		$this->copyColumnsFromOriginal( $parentPost, $currentPost );

		$currentPost->post_id = $postId;

		// Everything above came from the source language, so the translated values have to win.
		foreach ( Models\Post::getLocalizedMetaMap() as $column => $metaKey ) {
			$field = 'field-' . $metaKey . '-0';
			if ( empty( $fields[ $field ] ) ) {
				continue;
			}

			$currentPost->$column = Models\Post::localizedValueFromMeta( $column, $fields[ $field ]['data'] );
		}

		// The legacy column is a mirror of the keyword ones, so it has to follow them.
		$currentPost->keyphrases = Models\Post::getKeyphrasesFromKeywordColumns(
			$currentPost->focus_keyword,
			$currentPost->additional_keywords,
			$currentPost->keyphrases,
			$currentPost->truseo
		);

		// The analysis language is the translation's, not the source's.
		$locale = aioseo()->helpers->getTruSeoObjectLocale( get_post( $postId ) );
		if ( '' !== $locale ) {
			$currentPost->truseo_locale = $locale;
		}

		$currentPost->save();
	}

	/**
	 * Copies the original's SEO data to a post that WPML duplicated.
	 *
	 * @since 5.0.6
	 *
	 * @param  int    $masterPostId The original post ID.
	 * @param  string $lang         The duplicate's language code.
	 * @param  array  $postArray    The duplicate's post data.
	 * @param  int    $postId       The duplicate's post ID.
	 * @return void
	 */
	public function duplicateWpmlSeoData( $masterPostId, $lang, $postArray, $postId ) {
		$parentPost = Models\Post::getPost( $masterPostId );
		if ( ! $parentPost->exists() ) {
			return;
		}

		$currentPost = Models\Post::getPost( $postId );

		$this->copyColumnsFromOriginal( $parentPost, $currentPost );

		$currentPost->post_id = $postId;

		$locale = aioseo()->helpers->getTruSeoObjectLocale( get_post( $postId ) );
		if ( '' !== $locale ) {
			$currentPost->truseo_locale = $locale;
		}

		$currentPost->save();
	}

	/**
	 * Copies every column but the IDs, the timestamps and the canonical URL, which belongs to the original's URL.
	 *
	 * @since 5.0.6
	 *
	 * @param  Models\Post $source The original's row.
	 * @param  Models\Post $target The translation's or duplicate's row.
	 * @return void
	 */
	private function copyColumnsFromOriginal( $source, $target ) {
		foreach ( $source->getColumns() as $column => $value ) {
			// save() sets the timestamps, and an unsaved source model doesn't have them.
			if ( in_array( $column, [ 'id', 'post_id', 'created', 'updated', 'canonical_url' ], true ) ) {
				continue;
			}

			$target->$column = $source->$column;
		}
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