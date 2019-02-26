<?php
/**
 * Class for public facing code
 *
 * @package All-in-One-SEO-Pack
 * @since   2.3.6
 */

if ( ! class_exists( 'All_in_One_SEO_Pack_Front' ) ) {

	/**
	 * Class All_in_One_SEO_Pack_Front
	 *
	 * @since 2.3.6
	 */
	class All_in_One_SEO_Pack_Front {

		/**
		 * All_in_One_SEO_Pack_Front constructor.
		 */
		public function __construct() {

			add_action( 'template_redirect', array( $this, 'noindex_follow_rss' ) );
			add_action( 'template_redirect', array( $this, 'redirect_attachment' ) );

		}

		/**
		 * Noindex and follow RSS feeds.
		 *
		 * @Since 2.3.6
		 */
		public function noindex_follow_rss() {
			if ( is_feed() && headers_sent() === false ) {
				header( 'X-Robots-Tag: noindex, follow', true );
			}
		}

		/**
		 * Redirect attachment to parent post.
		 *
		 * @since 2.3.9
		 */
		function redirect_attachment() {
			global $aioseop_options;
			if ( ! isset( $aioseop_options['aiosp_redirect_attachement_parent'] ) || $aioseop_options['aiosp_redirect_attachement_parent'] !== 'on' ) {
				return false;
			}

			global $post;
			if ( is_attachment() && ( ( is_object( $post ) && isset( $post->post_parent ) ) && ( is_numeric( $post->post_parent ) && $post->post_parent != 0 ) ) ) {
				wp_safe_redirect( aioseop_get_permalink( $post->post_parent ), 301 );
				exit;
			}
		}
	}

}

$aiosp_front_class = new All_in_One_SEO_Pack_Front();

