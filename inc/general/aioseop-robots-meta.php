<?php
/**
 * The aioseop-robots-meta.php file.
 *
 * Includes all code for the robots meta tag.
 *
 * @package All-in-One-SEO-Pack
 */

/**
 * The AIOSEOP_Robots_Meta class.
 *
 * @since 3.3.0 Moved code to its own dedicated file and class.
 */
class AIOSEOP_Robots_Meta {

	/**
	 * User-defined plugin options.
	 *
	 * @since 3.3.1
	 *
	 * @var array
	 */
	private $plugin_options;

	public function __construct() {
		global $aioseop_options;
		$this->plugin_options = $aioseop_options;
	}

	/**
	 * Returns the robots meta tag string.
	 *
	 * @since 2.3.5
	 * @since 2.3.11.5 Added noindex API filter hook for password protected posts.
	 * @since 3.2.0 Full refactoring.
	 * @since 3.2.4 Bug fixes.
	 * @since 3.3.0 Move conditional checks to dedicated functions.
	 *
	 * @return string
	 */
	public function get_robots_meta_tag() {
		$post_type   = get_post_type();
		$page_number = aioseop_get_page_number();

		$noindex            = false;
		$nofollow           = false;
		$post_meta_noindex  = '';
		$post_meta_nofollow = '';

		if ( ! get_option( 'blog_public' ) ) {
			return '';
		}

		if ( is_front_page() && 0 === $page_number ) {
			return $this->get_robots_meta_tag_helper( false, false );
		}

		if ( $this->is_static_page() ) {
			$post_type = 'page';
		}

		if ( $this->has_post_meta() ) {
			$post_meta_noindex  = $this->get_meta_value( 'noindex' );
			$post_meta_nofollow = $this->get_meta_value( 'nofollow' );
		}

		if ( 'on' === $post_meta_noindex ||
			$this->is_noindexed_paginated_page( $page_number ) ||
			$this->is_noindexed_password_protected_post() ||
			$this->is_noindexed_tax() ||
			$this->is_noindexed_singular( $post_type, $post_meta_noindex )
		) {
			$noindex = true;
		}

		if ( 'on' === $post_meta_nofollow || $this->is_nofollowed_paginated_page( $page_number ) || $this->is_nofollowed_singular( $post_type, $post_meta_nofollow ) ) {
			$nofollow = true;
		}

		return $this->get_robots_meta_tag_helper( $noindex, $nofollow );
	}

	/**
	 * Helper function for get_robots_meta_tag().
	 *
	 * @since 3.2.0
	 *
	 * @param bool $noindex
	 * @param bool $nofollow
	 *
	 * @return string
	 */
	private function get_robots_meta_tag_helper( $noindex, $nofollow ) {
		$index_value  = 'index';
		$follow_value = 'follow';

		if ( $noindex ) {
			$index_value = 'noindex';
		}

		if ( $nofollow ) {
			$follow_value = 'nofollow';
		}

		$robots_meta_value = sprintf( '%1$s,%2$s', $index_value, $follow_value );

		/**
		 * The aioseop_robots_meta() filter hook.
		 *
		 * Allows users to filter the robots meta tag value.
		 *
		 * @since ?
		 * @since 3.3.0 Moved to dedicated class file.
		 *
		 * @param string $robots_meta_value
		 */
		$robots_meta_value = apply_filters( 'aioseop_robots_meta', $robots_meta_value );

		if ( 'index,follow' === $robots_meta_value ) {
			return '';
		}
		return sprintf( '<meta name="robots" content="%s"', esc_attr( $robots_meta_value ) ) . " />\n";
	}

	/**
	 * The get_meta_value() function.
	 *
	 * Returns the noindex or nofollow meta value for the requested page.
	 * TODO Use $meta_opts when get_current_options() is refactored - #2729.
	 *
	 * @since 3.2.0
	 *
	 * @param string $key The requested meta key.
	 * @return string
	 */
	private function get_meta_value( $key ) {
		$meta           = array();
		$meta_value     = '';
		$requested_page = get_queried_object();

		if ( empty( $requested_page ) ) {
			return $meta_value;
		}

		// TODO Use $meta_opts when get_current_options() is refactored - #2729.
		if ( property_exists( $requested_page, 'ID' ) ) {
			$meta = get_post_meta( $requested_page->ID );
		}

		if ( property_exists( $requested_page, 'term_id' ) ) {
			$meta = get_term_meta( $requested_page->term_id );
		}

		if ( $this->is_woocommerce_shop_page() ) {
			$meta = get_post_meta( wc_get_page_id( 'shop' ) );
		}

		if ( is_array( $meta ) && array_key_exists( '_aioseop_' . $key, $meta ) ) {
			$meta_value = $meta[ '_aioseop_' . $key ][0];
		}

		return $meta_value;
	}

	/**
	 * The is_static_page() function.
	 *
	 * Checks whether the current page is a static_page.
	 *
	 * @since 3.3.0
	 *
	 * @return bool
	 */
	private function is_static_page() {
		if ( $this->is_static_posts_page() || $this->is_woocommerce_shop_page() ) {
			return true;
		}
		return false;
	}

	/**
	 * The is_static_posts_page() function.
	 *
	 * Checks whether the current page is the static posts page.
	 *
	 * @since 3.3.0
	 *
	 * @return bool
	 */
	private function is_static_posts_page() {
		if ( is_home() && 0 !== (int) get_option( 'page_for_posts' ) ) {
			return true;
		}
		return false;
	}

	/**
	 * The is_woocommerce_shop_page() function.
	 *
	 * Checks whether the current page is the WooCommerce shop page.
	 *
	 * @since 3.3.0
	 *
	 * @return bool
	 */
	private function is_woocommerce_shop_page() {
		if ( aioseop_is_woocommerce_active() && is_shop() ) {
			return true;
		}
		return false;
	}

	/**
	 * The has_post_meta() function.
	 *
	 * Checks whether the current page can have post meta.
	 *
	 * @since 3.3.0
	 *
	 * @return bool
	 */
	private function has_post_meta() {
		if ( ! is_date() &&
			! is_author() &&
			! is_search() &&
			! is_404()
		) {
			return true;
		}
		return false;
	}

	/**
	 * The is_noindexed_paginated_page() function.
	 *
	 * Checks whether the current page is paginated and should be noindexed.
	 *
	 * @since 3.3.0
	 *
	 * @param int $page_number
	 * @return bool
	 */
	private function is_noindexed_paginated_page( $page_number ) {
		if ( ! empty( $this->plugin_options['aiosp_paginated_noindex'] ) && 1 < $page_number ) {
			return true;
		}
		return false;
	}

	/**
	 * The is_nofollowed_paginated_page() function.
	 *
	 * Checks whether the current page is paginated and should be nofollowed.
	 *
	 * @since 3.3.0
	 *
	 * @param int $page_number
	 * @return bool
	 */
	private function is_nofollowed_paginated_page( $page_number ) {
		if ( ! empty( $this->plugin_options['aiosp_paginated_nofollow'] ) && 1 < $page_number ) {
			return true;
		}
		return false;
	}

	/**
	 * The is_noindexed_password_protected_post() function.
	 *
	 * Checks whether the current page is password protected and should be noindexed.
	 *
	 * @since 3.3.0
	 *
	 * @return bool
	 */
	private function is_noindexed_password_protected_post() {
		if ( is_singular() &&
			$this->is_password_protected()
			&& apply_filters( 'post_meta_noindex_password_posts', false )
		) {
			return true;
		}
		return false;
	}

	/**
	 * The is_noindexed_tax() function.
	 *
	 * Checks whether the current page is tax term or archive page and should be noindexed.
	 *
	 * @since 3.3.0
	 *
	 * @return bool
	 */
	private function is_noindexed_tax() {
		if (
			( is_category() && ! empty( $this->plugin_options['aiosp_category_noindex'] ) ) ||
			( is_date() && ! empty( $this->plugin_options['aiosp_archive_date_noindex'] ) ) ||
			( is_author() && ! empty( $this->plugin_options['aiosp_archive_author_noindex'] ) ) ||
			( is_tag() && ! empty( $this->plugin_options['aiosp_tags_noindex'] ) ) ||
			( is_search() && ! empty( $this->plugin_options['aiosp_search_noindex'] ) ) ||
			( is_404() && ! empty( $this->plugin_options['aiosp_404_noindex'] ) ) ||
			( is_tax() && in_array( get_query_var( 'taxonomy' ), $this->get_noindexed_taxonomies() ) )
		) {
			return true;
		}
		return false;
	}

	/**
	 * The get_noindexed_taxonomies() function.
	 *
	 * Returns a list of sitewide noindexed taxonomies.
	 *
	 * @since 3.3.0
	 *
	 * @return array
	 */
	private function get_noindexed_taxonomies() {
		if ( isset( $this->plugin_options['aiosp_tax_noindex'] ) && ! empty( $this->plugin_options['aiosp_tax_noindex'] ) ) {
			return $this->plugin_options['aiosp_tax_noindex'];
		}
		return array();
	}

	/**
	 * The is_noindexed_singular() function.
	 *
	 * Checks whether the current page is singular and should be noindexed.
	 *
	 * @since 3.3.0
	 *
	 * @param string $post_type
	 * @param string $post_meta_noindex
	 *
	 * @return bool
	 */
	private function is_noindexed_singular( $post_type, $post_meta_noindex ) {
		if ( is_singular() && '' === $post_meta_noindex &&
			! empty( $this->plugin_options['aiosp_cpostnoindex'] ) &&
			in_array( $post_type, $this->plugin_options['aiosp_cpostnoindex'] )
		) {
			return true;
		}
		return false;
	}

	/**
	 * The is_nofollowed_singular() function.
	 *
	 * Checks whether the current page is singular and should be nofollowed.
	 *
	 * @since 3.3.0
	 *
	 * @param string $post_type
	 * @param string $post_meta_follow
	 *
	 * @return bool
	 */
	private function is_nofollowed_singular( $post_type, $post_meta_follow ) {
		if ( is_singular() && '' === $post_meta_follow &&
			! empty( $this->plugin_options['aiosp_cpostnofollow'] ) &&
			in_array( $post_type, $this->plugin_options['aiosp_cpostnofollow'] )
		) {
			return true;
		}
		return false;
	}

	/**
	 * The is_password_protected() function.
	 *
	 * Determine if the current post is password protected.
	 *
	 * @since 2.3.11.5
	 * @since 3.3.0 Moved to aioseop-robots-meta.php.
	 *
	 * @return bool
	 */
	function is_password_protected() {
		global $post;
		if ( ! empty( $post->post_password ) ) {
			return true;
		}
		return false;
	}
}
