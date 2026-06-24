<?php
namespace AIOSEO\Plugin\Common\Traits\Helpers;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Contains WordPress Post Type helpers.
 *
 * @since 4.2.4
 */
trait PostType {
	/**
	 * Returns a post type feature.
	 *
	 * @since 4.2.4
	 *
	 * @param  string|\WP_Post_Type $postType The post type.
	 * @param  string               $feature  The feature to find.
	 * @return mixed|false                    The post type feature or false if not found.
	 */
	public function getPostTypeFeature( $postType, $feature ) {
		if ( is_string( $postType ) ) {
			$postType = get_post_type_object( $postType );
		}

		if ( ! is_a( $postType, 'WP_Post_Type' ) || ! isset( $postType->$feature ) ) {
			return false;
		}

		return $postType->$feature;
	}

	/**
	 * Returns the URL prefix a non-hierarchical post type's permalinks live under.
	 *
	 * NOTE: returns null when the post type has no rewrite registration (e.g., built-in `post`).
	 *
	 * @since 4.9.9
	 *
	 * @param  string      $postType The post type slug.
	 * @return string|null           The expected prefix, without leading/trailing slashes.
	 */
	public function getPostTypeUrlPrefix( $postType ) {
		$postTypeObj = get_post_type_object( $postType );
		if ( ! $postTypeObj || empty( $postTypeObj->rewrite ) ) {
			return null;
		}

		$prefix = ! empty( $postTypeObj->rewrite['slug'] )
			? trim( $postTypeObj->rewrite['slug'], '/' )
			: $postType;

		$withFront = ! isset( $postTypeObj->rewrite['with_front'] ) || ! empty( $postTypeObj->rewrite['with_front'] );
		if ( $withFront ) {
			global $wp_rewrite; // phpcs:ignore Squiz.NamingConventions.ValidVariableName
			if ( $wp_rewrite instanceof \WP_Rewrite ) { // phpcs:ignore Squiz.NamingConventions.ValidVariableName
				$front = trim( $wp_rewrite->front, '/' ); // phpcs:ignore Squiz.NamingConventions.ValidVariableName
				if ( '' !== $front ) {
					$prefix = $front . '/' . $prefix;
				}
			}
		}

		return $prefix;
	}
}