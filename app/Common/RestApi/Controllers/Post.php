<?php
namespace AIOSEO\Plugin\Common\RestApi\Controllers;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AIOSEO\Plugin\Common\Models;

/**
 * Handles all post routes.
 *
 * Merged into the main plugin from the legacy aioseo-rest-api addon.
 *
 * @since 4.9.8
 */
class Post extends Base {
	/**
	 * Registers the fields dynamically.
	 *
	 * @since 4.9.8
	 *
	 * @return void
	 */
	public function register() {
		$postTypes = aioseo()->helpers->getPublicPostTypes( true );
		$postTypes = apply_filters( 'aioseo_rest_api_post_types', $postTypes );

		$supportedPostTypes = [];
		foreach ( $postTypes as $postType ) {
			$postTypeObject = get_post_type_object( $postType );

			if (
				! is_a( $postTypeObject, 'WP_Post_Type' ) ||
				! $postTypeObject->show_in_rest
			) {
				continue;
			}

			$supportedPostTypes[] = $postType;
		}

		foreach ( $supportedPostTypes as $postType ) {
			$this->registerHeadFields( $postType );
			$this->registerMetaDataField( $postType );
			$this->registerBreadcrumbFields( $postType );
			$this->registerDeprecatedUpdateFields( $postType );
		}
	}

	/**
	 * Registers the meta data field.
	 *
	 * @since 4.9.8
	 *
	 * @param  string $postType The post type name.
	 * @return void
	 */
	private function registerMetaDataField( $postType ) {
		$callbacks = [
			'get_callback'    => [ $this, 'getMetaData' ],
			'update_callback' => [ $this, 'updateMetaData' ]
		];

		register_rest_field( $postType, 'aioseo_meta_data', $callbacks );
	}

	/**
	 * Registers the deprecated single value fields.
	 *
	 * @since 4.9.8
	 *
	 * @param  string $postType The post type name.
	 * @return void
	 */
	private function registerDeprecatedUpdateFields( $postType ) {
		foreach ( $this->deprecatedFields as $oldKey => $newKey ) {
			register_rest_field( $postType, $oldKey, [
				'update_callback' => [ $this, 'updateMetaData' ]
			] );
		}
	}

	/**
	 * Checks whether the user is allowed to update meta data for the given post type.
	 *
	 * @since 4.9.8
	 *
	 * @param  string $postType The post type name.
	 * @return bool             Whether the user is allowed to update meta data for the post type.
	 */
	private function isAllowedToUpdate( $postType ) {
		return apply_filters( 'aioseo_rest_api_allow_update', true, $postType ) &&
			aioseo()->helpers->canEditPostType( $postType ) &&
			$this->canEditMetaData();
	}

	/**
	 * Returns the meta data for the given post.
	 *
	 * @since 4.9.8
	 *
	 * @param  array $post The post object.
	 * @return array       The meta data.
	 */
	public function getMetaData( $post ) {
		$aioseoPost = Models\Post::getPost( $post['id'] );
		$aioseoPost = $aioseoPost->exists()
			? json_decode( wp_json_encode( $aioseoPost ), true )
			: [];

		return $this->removeInternalFields( $aioseoPost );
	}

	/**
	 * Allows users to update the meta data of the given post.
	 *
	 * @since 4.9.8
	 *
	 * @param  array    $metaData  The new meta data.
	 * @param  \WP_Post $post      The post object.
	 * @param  string   $fieldName The field name.
	 * @return void
	 */
	public function updateMetaData( $metaData, $post, $fieldName ) {
		$postId = 'product' !== $post->post_type ? $post->ID : $post->id;
		if (
			! current_user_can( 'edit_post', $postId ) ||
			! $this->isAllowedToUpdate( $post->post_type )
		) {
			return;
		}

		if ( 'aioseo_meta_data' !== $fieldName && isset( $this->deprecatedFields[ $fieldName ] ) ) {
			$metaData = [
				$this->deprecatedFields[ $fieldName ] => $metaData
			];
		}

		// Prevent the user from overriding the post ID.
		unset( $metaData['post_id'] );

		if ( empty( $metaData ) ) {
			return;
		}

		$aioseoPost = json_decode( wp_json_encode( Models\Post::getPost( $postId ) ), true );
		$aioseoPost = array_replace( $aioseoPost, $metaData );

		// We have to decode this because savePost() expects this to be an array.
		// The value can be null when the post has no AIOSEO meta yet; json_decode() deprecates null input on newer PHP versions.
		if ( ! empty( $aioseoPost['schema_type_options'] ) && is_string( $aioseoPost['schema_type_options'] ) ) {
			$aioseoPost['schema_type_options'] = json_decode( $aioseoPost['schema_type_options'] );
		}

		// We'll just pass the data into savePost() so that the main plugin can handle all sanitization.
		Models\Post::savePost( $postId, $aioseoPost );
	}

	/**
	 * Sets the given post as the queried object of the main query.
	 *
	 * @since 4.9.8
	 *
	 * @param  array $postArr The post array.
	 * @return void
	 */
	protected function setWpQuery( $postArr ) {
		// phpcs:disable Squiz.NamingConventions.ValidVariableName
		global $wp_query;
		$this->originalQuery = clone $wp_query;

		$post = get_post( $postArr['id'] );

		$wp_query->posts                 = [ $post ];
		$wp_query->post                  = $post;
		$wp_query->post_count            = 1;
		$wp_query->get_queried_object_id = (int) $post->ID;
		$wp_query->queried_object        = $post;
		$wp_query->is_single             = true;
		$wp_query->is_singular           = true;

		if ( 'page' === $postArr['type'] ) {
			$wp_query->is_page = true;
		}
		// phpcs:enable Squiz.NamingConventions.ValidVariableName
	}
}