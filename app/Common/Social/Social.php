<?php
namespace AIOSEO\Plugin\Common\Social;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles the Social Meta.
 *
 * @package AIOSEO\Plugin\Common\Social
 *
 * @since 4.0.0
 */
class Social {
	/**
	 * The name of the action to bust the OG cache.
	 *
	 * @since 4.2.0
	 *
	 * @var string
	 */
	private $bustOgCacheActionName = 'aioseo_og_cache_bust_post';

	/**
	 * Image class instance.
	 *
	 * @since 4.2.7
	 *
	 * @var Image
	 */
	public $image = null;

	/**
	 * Facebook class instance.
	 *
	 * @since 4.2.7
	 *
	 * @var Facebook
	 */
	public $facebook = null;

	/**
	 * Twitter class instance.
	 *
	 * @since 4.2.7
	 *
	 * @var Twitter
	 */
	public $twitter = null;

	/**
	 * Output class instance.
	 *
	 * @since 4.2.7
	 *
	 * @var Output
	 */
	public $output = null;

	/**
	 * Class constructor.
	 *
	 * @since 4.0.0
	 */
	public function __construct() {
		$this->image = new Image();

		if ( wp_doing_ajax() || wp_doing_cron() ) {
			return;
		}

		$this->facebook = new Facebook();
		$this->twitter  = new Twitter();
		$this->output   = new Output();

		$this->hooks();
	}

	/**
	 * Registers our hooks.
	 *
	 * @since 4.0.0
	 */
	protected function hooks() {
		add_action( $this->bustOgCacheActionName, [ $this, 'bustOgCachePost' ] );

		// To avoid duplicate sets of meta tags.
		add_filter( 'jetpack_enable_open_graph', '__return_false' );

		if ( ! is_admin() ) {
			add_filter( 'language_attributes', [ $this, 'addAttributes' ] );

			return;
		}

		// Forces a refresh of the Facebook cache.
		add_action( 'post_updated', [ $this, 'scheduleBustOgCachePost' ], 10, 2 );
	}

	/**
	 * Adds our attributes to the registered language attributes.
	 *
	 * @since   4.0.0
	 * @version 4.4.5 Adds trim function the html tag removing empty spaces.
	 *
	 * @param  string $htmlTag The 'html' tag as a string.
	 * @return string          The filtered 'html' tag as a string.
	 */
	public function addAttributes( $htmlTag ) {
		if ( ! aioseo()->options->social->facebook->general->enable ) {
			return $htmlTag;
		}

		$attributes = apply_filters( 'aioseo_opengraph_attributes', [ 'prefix="og: https://ogp.me/ns#"' ] );
		foreach ( $attributes as $attr ) {
			if ( strpos( $htmlTag, $attr ) === false ) {
				$htmlTag .= " $attr ";
			}
		}

		return trim( $htmlTag );
	}

	/**
	 * Schedule a ping to bust the OG cache.
	 *
	 * @since 4.2.0
	 *
	 * @param  int      $postId The post ID.
	 * @param  \WP_Post $post   The post object.
	 * @return void
	 */
	public function scheduleBustOgCachePost( $postId, $post = null ) {
		if ( ! aioseo()->helpers->isSbCustomFacebookFeedActive() || ! aioseo()->helpers->isValidPost( $post ) ) {
			return;
		}

		if ( aioseo()->actionScheduler->isScheduled( $this->bustOgCacheActionName, [ 'postId' => $postId ] ) ) {
			return;
		}

		// Schedule the new ping.
		aioseo()->actionScheduler->scheduleAsync( $this->bustOgCacheActionName, [ 'postId' => $postId ] );
	}

	/**
	 * Pings Facebook and asks them to bust the OG cache for a particular post.
	 *
	 * @since 4.2.0
	 *
	 * @see https://developers.facebook.com/docs/sharing/opengraph/using-objects#update
	 *
	 * @param  int  $postId The post ID.
	 * @return void
	 */
	public function bustOgCachePost( $postId ) {
		$post              = get_post( $postId );
		$customAccessToken = apply_filters( 'aioseo_facebook_access_token', '' );

		if (
			! aioseo()->helpers->isValidPost( $post ) ||
			( ! aioseo()->helpers->isSbCustomFacebookFeedActive() && ! $customAccessToken )
		) {
			return;
		}

		$permalink = get_permalink( $postId );
		$this->bustOgCacheHelper( $permalink );
	}

	/**
	 * Helper function for bustOgCache().
	 *
	 * @since 4.2.0
	 *
	 * @param  string $permalink The permalink.
	 * @return void
	 */
	protected function bustOgCacheHelper( $permalink ) {
		$accessToken = aioseo()->helpers->getSbAccessToken();
		$accessToken = apply_filters( 'aioseo_facebook_access_token', $accessToken );
		if ( ! $accessToken ) {
			return;
		}

		$url = sprintf(
			'https://graph.facebook.com/?%s',
			http_build_query(
				[
					'id'           => $permalink,
					'scrape'       => true,
					'access_token' => $accessToken
				]
			)
		);

		wp_remote_post( $url, [ 'blocking' => false ] );
	}
}