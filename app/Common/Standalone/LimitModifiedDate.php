<?php
namespace AIOSEO\Plugin\Common\Standalone;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AIOSEO\Plugin\Common\Models;

/**
 * Limit Modified Date class.
 *
 * @since 4.1.8
 */
class LimitModifiedDate {
	/**
	 * Class constructor.
	 *
	 * @since 4.1.8
	 *
	 * @return void
	 */
	public function __construct() {
		if ( apply_filters( 'aioseo_last_modified_date_disable', false ) ) {
			return;
		}

		// Reset modified date when the post is updated.
		add_filter( 'wp_insert_post_data', [ $this, 'resetModifiedDate' ], 99999, 2 );
		add_filter( 'wp_insert_attachment_data', [ $this, 'resetModifiedDate' ], 99999, 2 );

		add_action( 'rest_api_init', [ $this, 'registerRestHooks' ] );

		if ( ! is_admin() ) {
			return;
		}

		add_action( 'admin_enqueue_scripts', [ $this, 'enqueueScripts' ], 20 );
		add_action( 'post_submitbox_misc_actions', [ $this, 'classicEditorField' ] );
	}

	/**
	 * Register the REST API hooks.
	 *
	 * @since 4.1.8
	 *
	 * @return void
	 */
	public function registerRestHooks() {
		// Prevent REST API from dropping limit modified date value before updating the post.
		foreach ( aioseo()->helpers->getPublicPostTypes( true ) as $postType ) {
			add_filter( "rest_pre_insert_$postType", [ $this, 'addLimitModifiedDateValue' ], 10, 2 );
		}
	}

	/**
	 * Enqueues the scripts for the Limited Modified Date functionality.
	 *
	 * @since 4.1.8
	 *
	 * @return void
	 */
	public function enqueueScripts() {
		if ( ! $this->isAllowed() || ! aioseo()->helpers->isScreenBase( 'post' ) ) {
			return;
		}

		// Only enqueue this script if the post-settings-metabox is already enqueued.
		if ( wp_script_is( 'aioseo/js/src/vue/standalone/post-settings/main.js', 'enqueued' ) ) {
			aioseo()->core->assets->load( 'src/vue/standalone/limit-modified-date/main.js' );
		}
	}

	/**
	 * Adds the Limit Modified Date field to the post object to prevent it from being dropped.
	 *
	 * @since 4.1.8
	 *
	 * @param  object           $preparedPost The post data.
	 * @param  \WP_REST_Request $restRequest  The request.
	 * @return object                         The modified post data.
	 */
	public function addLimitModifiedDateValue( $preparedPost, $restRequest = null ) {
		if ( 'PUT' !== $restRequest->get_method() ) {
			return $preparedPost;
		}

		$params = $restRequest->get_json_params();
		if ( empty( $params ) || ! isset( $params['aioseo_limit_modified_date'] ) ) {
			return $preparedPost;
		}

		$preparedPost->aioseo_limit_modified_date = $params['aioseo_limit_modified_date'];

		return $preparedPost;
	}

	/**
	 * Resets the modified date when a post is updated if the Limit Modified Date option is enabled.
	 *
	 * @since 4.1.8
	 *
	 * @param  array $data      An array of slashed, sanitized, and processed post data.
	 * @param  array $postArray An array of sanitized (and slashed) but otherwise unmodified post data.
	 * @return array            The modified sanitized post data.
	 */
	public function resetModifiedDate( $data, $postArray = [] ) {
		// If the ID isn't set, a new post is being inserted.
		if ( ! isset( $postArray['ID'] ) ) {
			return $data;
		}

		static $shouldReset = false;

		// Handle the REST API request from the Block Editor.
		if ( aioseo()->helpers->isRestApiRequest() ) {
			// If the value isn't set, then the value wasn't changed in the editor, and we can grab it from the post.
			if ( ! isset( $postArray['aioseo_limit_modified_date'] ) ) {
				$aioseoPost = Models\Post::getPost( $postArray['ID'] );
				if ( $aioseoPost->exists() && $aioseoPost->limit_modified_date ) {
					$shouldReset = true;
				}
			} else {
				if ( $postArray['aioseo_limit_modified_date'] ) {
					$shouldReset = true;
				}
			}
		}

		// Handle the POST request.
		if ( isset( $postArray['aioseo-post-settings'] ) ) {
			$aioseoData = json_decode( stripslashes( $postArray['aioseo-post-settings'] ) );
			if ( ! empty( $aioseoData->limit_modified_date ) ) {
				$shouldReset = true;
			}
		}

		// Handle post revision.
		if ( ! empty( $GLOBALS['action'] ) && 'restore' === $GLOBALS['action'] ) {
			$aioseoPost = Models\Post::getPost( $postArray['ID'] );
			if ( $aioseoPost->exists() && $aioseoPost->limit_modified_date ) {
				$shouldReset = true;
			}
		}

		foreach ( aioseo()->standalone->pageBuilderIntegrations as $pageBuilder ) {
			if ( $pageBuilder->isBuiltWith( $postArray['ID'] ) && $pageBuilder->limitModifiedDate( $postArray['ID'] ) ) {
				$shouldReset = true;
				break;
			}
		}

		if ( $shouldReset && isset( $postArray['post_modified'], $postArray['post_modified_gmt'] ) ) {
			$originalPost = get_post( $postArray['ID'] );

			$data['post_modified']     = $originalPost->post_modified;
			$data['post_modified_gmt'] = $originalPost->post_modified_gmt;
		}

		return $data;
	}

	/**
	 * Add the checkbox in the Classic Editor.
	 *
	 * @since 4.1.8
	 *
	 * @param  \WP_Post $post The post object.
	 * @return void
	 */
	public function classicEditorField( $post ) {
		if ( ! $this->isAllowed( $post->post_type ) ) {
			return;
		}

		?>
		<div class="misc-pub-section">
			<div id="aioseo-limit-modified-date"></div>
		</div>
		<?php
	}

	/**
	 * Check if the Limit Modified Date functionality is allowed to run.
	 *
	 * @since 4.1.8
	 *
	 * @param  string $postType The current post type.
	 * @return bool             Whether the functionality is allowed.
	 */
	private function isAllowed( $postType = '' ) {
		if ( empty( $postType ) ) {
			$postType = get_post_type();
		}

		if ( class_exists( 'Limit_Modified_Date', false ) ) {
			return false;
		}

		if ( ! $this->isAllowedPostType( $postType ) ) {
			return false;
		}

		if ( ! aioseo()->access->hasCapability( 'aioseo_page_general_settings' ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Check if the given post type is allowed to limit the modified date.
	 *
	 * @since 4.1.8
	 *
	 * @param  string $postType The post type name.
	 * @return bool             Whether the post type is allowed.
	 */
	private function isAllowedPostType( $postType ) {
		$dynamicOptions = aioseo()->dynamicOptions->noConflict();
		$postTypes      = aioseo()->helpers->getPublicPostTypes( true );
		$postTypes      = apply_filters( 'aioseo_limit_modified_date_post_types', $postTypes );

		if ( ! in_array( $postType, $postTypes, true ) ) {
			return false;
		}

		if ( ! $dynamicOptions->searchAppearance->postTypes->has( $postType ) || ! $dynamicOptions->searchAppearance->postTypes->$postType->advanced->showMetaBox ) {
			return false;
		}

		return true;
	}
}