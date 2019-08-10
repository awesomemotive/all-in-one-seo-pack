<?php
/**
 * Class for public facing code
 *
 * @package All_in_One_SEO_Pack
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
		 *
		 * @since 2.3.6
		 */
		public function __construct() {

			add_action( 'template_redirect', array( $this, 'noindex_follow_rss' ) );
			add_action( 'template_redirect', array( $this, 'redirect_attachment' ) );

		}

		/**
		 * The noindex_follow_rss() function.
		 *
		 * Adds "noindex,follow" as HTTP header for RSS feeds.
		 *
		 * @since 2.3.6
		 * @since 3.2.0 Added noindex_rss filter hook.
		 */
		public function noindex_follow_rss() {
			if ( is_feed() && headers_sent() === false ) {
				/**
				 * The aioseop_noindex_rss filter hook.
				 *
				 * Filter whether RSS feeds should or shouldn't have HTTP noindex header.
				 *
				 * @since 3.2.0
				 *
				 * @param bool
				 */
				$noindex = apply_filters( 'aioseop_noindex_rss', true );
				if ( $noindex ) {
					header( 'X-Robots-Tag: noindex, follow', true );
				}
			}
		}

		/**
		 * Redirect Attachment
		 *
		 * Redirect attachment to parent post.
		 *
		 * @since 2.3.9
		 */
		function redirect_attachment() {
			global $aioseop_options;
			if ( ! isset( $aioseop_options['aiosp_redirect_attachement_parent'] ) || 'on' !== $aioseop_options['aiosp_redirect_attachement_parent'] ) {
				return false;
			}

			global $post;
			if (
					is_attachment() &&
					(
							(
									is_object( $post ) &&
									isset( $post->post_parent )
							) &&
							(
									is_numeric( $post->post_parent ) &&
									0 != $post->post_parent
							)
					)
			) {
				wp_safe_redirect( aioseop_get_permalink( $post->post_parent ), 301 );
				exit;
			}
		}
	}

}

$aiosp_front_class = new All_in_One_SEO_Pack_Front();

