<?php
namespace AIOSEO\Plugin\Common\Admin;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Monitors changes to post slugs.
 *
 * @since 4.2.3
 */
class SlugMonitor {
	/**
	 * Holds posts that have been updated.
	 *
	 * @since 4.2.3
	 *
	 * @var array
	 */
	private $updatedPosts = [];

	/**
	 * Class constructor.
	 *
	 * @since 4.2.3
	 */
	public function __construct() {
		// We can't monitor changes without permalinks enabled.
		if ( ! get_option( 'permalink_structure' ) ) {
			return;
		}

		add_action( 'pre_post_update', [ $this, 'prePostUpdate' ] );

		// WP 5.6+.
		if ( function_exists( 'wp_after_insert_post' ) ) {
			add_action( 'wp_after_insert_post', [ $this, 'afterInsertPost' ], 11, 4 );
		} else {
			add_action( 'post_updated', [ $this, 'postUpdated' ], 11, 3 );
		}
	}

	/**
	 * Remember the previous post permalink.
	 *
	 * @since 4.2.3
	 *
	 * @param  integer $postId The post ID.
	 * @return void
	 */
	public function prePostUpdate( $postId ) {
		$this->updatedPosts[ $postId ] = get_permalink( $postId );
	}

	/**
	 * Called when a post has been completely inserted ( with categories and meta ).
	 *
	 * @since 4.2.3
	 *
	 * @param  integer       $postId     The post ID.
	 * @param  \WP_Post      $post       The post object.
	 * @param  bool          $update     Whether this is an existing post being updated.
	 * @param  null|\WP_Post $postBefore The post object before changes were made.
	 * @return void
	 */
	public function afterInsertPost( $postId, $post = null, $update = false, $postBefore = null ) {
		if ( ! $update ) {
			return;
		}

		$this->postUpdated( $postId, $post, $postBefore );
	}

	/**
	 * Called when a post has been updated - check if the slug has changed.
	 *
	 * @since 4.2.3
	 *
	 * @param  integer  $postId     The post ID.
	 * @param  \WP_Post $post       The post object.
	 * @param  \WP_Post $postBefore The post object before changes were made.
	 * @return void
	 */
	public function postUpdated( $postId, $post = null, $postBefore = null ) {
		if ( ! isset( $this->updatedPosts[ $postId ] ) ) {
			return;
		}

		$before = aioseo()->helpers->getPermalinkPath( $this->updatedPosts[ $postId ] );
		$after  = aioseo()->helpers->getPermalinkPath( get_permalink( $postId ) );
		if ( ! aioseo()->helpers->hasPermalinkChanged( $before, $after ) ) {
			return;
		}

		// Can we monitor this slug?
		if ( ! $this->canMonitorPost( $post, $postBefore ) ) {
			return;
		}

		// Ask aioseo-redirects if automatic redirects is monitoring it.
		if ( $this->automaticRedirect( $post->post_type, $before, $after ) ) {
			return;
		}

		// Filter to allow users to disable the slug monitor messages.
		if ( apply_filters( 'aioseo_redirects_disable_slug_monitor', false ) ) {
			return;
		}

		$redirectUrl = $this->manualRedirectUrl( [
			'url'    => $before,
			'target' => $after,
			'type'   => 301
		] );

		$message = __( 'The permalink for this post just changed! This could result in 404 errors for your site visitors.', 'all-in-one-seo-pack' );

		// Default notice redirecting to the Redirects screen.
		$action = [
			'url'   => $redirectUrl,
			'label' => __( 'Add Redirect to improve SEO', 'all-in-one-seo-pack' ),
			'class' => 'aioseo-redirects-slug-changed'
		];

		aioseo()->wpNotices->addNotice( $message, 'warning', [ 'actions' => [ $action ] ], [ 'posts' ] );
	}

	/**
	 * Checks if this is a post we can monitor.
	 *
	 * @since 4.2.3
	 *
	 * @param  \WP_Post $post       The post object.
	 * @param  \WP_Post $postBefore The post object before changes were made.
	 * @return boolean              True if we can monitor this post.
	 */
	private function canMonitorPost( $post, $postBefore ) {
		// Check that this is for the expected post.
		if ( ! isset( $post->ID ) || ! isset( $this->updatedPosts[ $post->ID ] ) ) {
			return false;
		}

		// Don't do anything if we're not published.
		if ( 'publish' !== $post->post_status || 'publish' !== $postBefore->post_status ) {
			return false;
		}

		// Don't do anything is the post type is not public.
		if ( ! is_post_type_viewable( $post->post_type ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Tries to add a automatic redirect.
	 *
	 * @since 4.2.3
	 *
	 * @param  string $postType The post type.
	 * @param  string $before   The url before.
	 * @param  string $after    The url after.
	 * @return bool             True if an automatic redirect was added.
	 */
	private function automaticRedirect( $postType, $before, $after ) {
		if ( empty( aioseo()->redirects ) ) {
			return false;
		}

		return aioseo()->redirects->monitor->automaticRedirect( $postType, $before, $after );
	}

	/**
	 * Generates a URL for adding manual redirects.
	 *
	 * @since 4.2.3
	 *
	 * @param  array  $urls An array of [url, target, type, slash, case, regex].
	 * @return string       The redirect link.
	 */
	public function manualRedirectUrl( $urls ) {
		if ( empty( aioseo()->redirects ) ) {
			return admin_url( 'admin.php?page=aioseo-redirects' );
		}

		return aioseo()->redirects->helpers->manualRedirectUrl( $urls );
	}
}