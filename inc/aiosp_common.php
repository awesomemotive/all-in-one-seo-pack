<?php
/**
 * AIOSEOP Common
 *
 * @package All_in_One_SEO_Pack
 * @since ?
 */

/**
 * Class aiosp_common
 *
 * These are commonly used functions that can be pulled from anywhere.
 * (or in some cases they're functions waiting for a home)
 */
// @codingStandardsIgnoreStart
class aiosp_common {
// @codingStandardsIgnoreEnd

	/**
	 * Attachment URL => PostIDs
	 *
	 * @var null|array
	 *
	 * @since 2.9.2
	 */
	public static $attachment_url_postids = null;

	/**
	 * Constructor
	 *
	 * @since 2.3.3
	 */
	function __construct() {

	}

	/**
	 * Clear WPE Cache
	 *
	 * Clears WP Engine cache.
	 *
	 * @since 2.4.10
	 */
	static function clear_wpe_cache() {
		if ( class_exists( 'WpeCommon' ) ) {
			WpeCommon::purge_memcached();
			WpeCommon::clear_maxcdn_cache();
			WpeCommon::purge_varnish_cache();
		}
	}

	/**
	 * Get Blog Page
	 *
	 * @since 2.3.3
	 *
	 * @param null $p
	 * @return array|null|string|WP_Post
	 */
	static function get_blog_page( $p = null ) {
		static $blog_page      = '';
		static $page_for_posts = '';
		if ( null === $p ) {
			global $post;
		} else {
			$post = $p;
		}
		if ( '' === $blog_page ) {
			if ( '' === $page_for_posts ) {
				$page_for_posts = get_option( 'page_for_posts' );
			}
			if ( $page_for_posts && is_home() && ( ! is_object( $post ) || ( $page_for_posts !== $post->ID ) ) ) {
				$blog_page = get_post( $page_for_posts );
			}
		}

		return $blog_page;
	}

	/**
	 * Get Upgrade Hyperlink
	 *
	 * @since 2.3.3
	 *
	 * @param string $location
	 * @param string $title
	 * @param string $anchor
	 * @param string $target
	 * @param string $class
	 * @param string $id
	 * @return string
	 */
	static function get_upgrade_hyperlink( $location = '', $title = '', $anchor = '', $target = '', $class = '', $id = 'aio-pro-update' ) {

		$affiliate_id = '';

		// call during plugins_loaded.
		$affiliate_id = apply_filters( 'aiosp_aff_id', $affiliate_id );

		// build URL.
		$url = 'https://semperplugins.com/all-in-one-seo-pack-pro-version/';
		if ( $location ) {
			$url .= '?loc=' . $location;
		}
		if ( $affiliate_id ) {
			$url .= "?ap_id=$affiliate_id";
		}

		// build hyperlink.
		$hyperlink = '<a ';
		if ( $target ) {
			$hyperlink .= "target=\"$target\" ";
		}
		if ( $title ) {
			$hyperlink .= "title=\"$title\" ";
		}
		if ( $id ) {
			$hyperlink .= "id=\"$id\" ";
		}

		$hyperlink .= "href=\"$url\">$anchor</a>";

		return $hyperlink;
	}

	/**
	 * Get Upgrade URL
	 *
	 * Gets the upgrade to Pro version URL.
	 *
	 * @since 2.3.3
	 */
	static function get_upgrade_url() {
		// put build URL stuff in here.
	}

	/**
	 * Absolutize URL
	 *
	 * Check whether a url is relative and if it is, make it absolute.
	 *
	 * @since 2.4.2
	 *
	 * @param string $url URL to check.
	 * @return string
	 */
	static function absolutize_url( $url ) {
		if ( 0 !== strpos( $url, 'http' ) && '/' !== $url ) {
			if ( 0 === strpos( $url, '//' ) ) {
				// for //<host>/resource type urls.
				$scheme = wp_parse_url( home_url(), PHP_URL_SCHEME );
				$url    = $scheme . ':' . $url;
			} else {
				// for /resource type urls.
				$url = home_url( $url );
			}
		}
		return $url;
	}

	/**
	 * Make URL Valid Smartly
	 *
	 * Check whether a url is relative (does not contain a . before the first /) or absolute and makes it a valid url.
	 *
	 * @since 2.8
	 *
	 * @param string $url URL to check.
	 * @return string
	 */
	static function make_url_valid_smartly( $url ) {
		$scheme = wp_parse_url( home_url(), PHP_URL_SCHEME );
		if ( 0 !== strpos( $url, 'http' ) ) {
			if ( 0 === strpos( $url, '//' ) ) {
				// for //<host>/resource type urls.
				$url = $scheme . ':' . $url;
			} elseif ( strpos( $url, '.' ) !== false && strpos( $url, '/' ) !== false && strpos( $url, '.' ) < strpos( $url, '/' ) ) {
				// if the . comes before the first / then this is absolute.
				$url = $scheme . '://' . $url;
			} else {
				// for /resource type urls.
				$url = home_url( $url );
			}
		} elseif ( strpos( $url, 'http://' ) === false ) {
			if ( 0 === strpos( $url, 'http:/' ) ) {
				$url = $scheme . '://' . str_replace( 'http:/', '', $url );
			} elseif ( 0 === strpos( $url, 'http:' ) ) {
				$url = $scheme . '://' . str_replace( 'http:', '', $url );
			}
		}
		return $url;
	}

	/**
	 * Is URL Valid
	 *
	 * Check whether a url is valid.
	 *
	 * @since 2.8
	 *
	 * @param string $url URL to check.
	 * @return bool
	 */
	public static function is_url_valid( $url ) {
		return filter_var( filter_var( $url, FILTER_SANITIZE_URL ), FILTER_VALIDATE_URL, FILTER_FLAG_SCHEME_REQUIRED | FILTER_FLAG_HOST_REQUIRED ) !== false;
	}

	/**
	 * Make XML Safe
	 *
	 * Renders the value XML safe.
	 *
	 * @since 2.10
	 */
	public static function make_xml_safe( $tag, $value ) {
		// some tags contain an array of values.
		if ( is_array( $value ) ) {
			return $value;
		}

		// sanitize the other tags.
		if ( in_array( $tag, array( 'guid', 'link', 'loc', 'image:loc' ), true ) ) {
			$value = esc_url( $value );
		} else {
			// some tags contain sanitized to some extent but they do not encode < and >.
			if ( ! in_array( $tag, array( 'image:title' ), true ) ) {
				$value = convert_chars( wptexturize( $value ) );
			}
		}
		return ent2ncr( esc_html( $value ) );
	}

	/**
	 * Attachment URL to Post ID whole search.
	 *
	 * Returns the (original) post/attachment ID from the URL param given. The function checks if URL is
	 * within, chacks for original attachment URLs, and then custom attachment URLs. The main intent for this function
	 * is to avoid having to query if possible (if cache was set prior), and if not, there is only 1 query per instance
	 * rather than multiple queries per instance.
	 * NOTE: Attempting to paginate the query actually caused the memory to peak higher use.
	 * NOTE: For a not paginatnated query case use aiosp_common:convert_image_url_to_id_individually istead.
	 *
	 * This is intended to work much the same way as WP's `attachment_url_to_postid()`
	 * But less expensive in loop case and also optionaly searching among custom image sizes.
	 *
	 * @link https://developer.wordpress.org/reference/functions/attachment_url_to_postid/
	 *
	 * @see aiosp_common::set_transient_url_postids()
	 * @see get_transient()
	 * @link https://developer.wordpress.org/reference/functions/get_transient/
	 * @see wp_upload_dir()
	 * @link https://developer.wordpress.org/reference/functions/wp_upload_dir/
	 *
	 * @since 2.9.2
	 *
	 * @param string $url Full image URL.
	 * @param bool $sizes_active Also search among image sizes
	 *
	 * @return int
	 */
	public static function convert_image_url_to_id_bulky( $url, $sizes_active = false ) {
		static $main_image_id_url;
		static $custom_image_id_url;
		static $uploads_dir;

		$id = 0;

		// Gets the URL => PostIDs array.
		// If static variable is still empty, load transient data.
		if ( is_null( self::$attachment_url_postids ) ) {
			if ( is_multisite() ) {
				self::$attachment_url_postids = get_site_transient( 'aioseop_multisite_attachment_url_postids' );
			} else {
				self::$attachment_url_postids = get_transient( 'aioseop_attachment_url_postids' );
			}

			// If no transient data, set as (default) empty array.
			if ( false === self::$attachment_url_postids ) {
				self::$attachment_url_postids = array();
			}
		}

		// Search for URL and get ID.
		if ( isset( self::$attachment_url_postids[ $url ] ) ) {
			// If static is already loaded and has URL, then return the URL's Post ID.
			$id = intval( self::$attachment_url_postids[ $url ] );
		} else {
			// Check to make sure Image URL is not outside the website.
			if ( is_null( $uploads_dir ) ) {
				$uploads_dir = wp_upload_dir();
			}

			if ( false !== strpos( $url, $uploads_dir['baseurl'] . '/' ) ) {
				if ( is_null( $main_image_id_url ) ) {
					$main_image_id_url = self::get_all_main_images();
				}
				if ( isset( $main_image_id_url[$url] ) ) {
					$id = intval( $main_image_id_url[$url] );
				}

				if ( empty( $id ) && $sizes_active ) { 
					if (is_null( $custom_image_id_url ) ) {
						$custom_image_id_url = self::get_all_custom_images();
					}
					if ( isset( $custom_image_id_url[$url] ) ) {
						$id = intval( $custom_image_id_url[$url] );
					}
				}
			}

			self::$attachment_url_postids[ $url ] = $id;

			/**
			 * Sets the transient data at the last hook instead at every call.
			 *
			 * @see aiosp_common::set_transient_url_postids()
			 */
			add_action( 'shutdown', array( 'aiosp_common', 'set_transient_url_postids' ) );
		}

		return $id;
	}

	/**
	 * Attachment URL to Post ID individual search.
	 *
	 * Returns the (original) post/attachment ID from the URL param given.
	 * NOTE: Attempting to paginate the query actually caused the memory to peak higher use 
	 * NOTE: For a paginatnated query case try to use aiosp_common:convert_image_url_to_id_bulky istead.
	 *
	 * This is intended to work much the same way as WP's `attachment_url_to_postid()`
	 * But also optionaly searching among custom image sizes.
	 *
	 * @link https://developer.wordpress.org/reference/functions/attachment_url_to_postid/
	 * @link https://developer.wordpress.org/reference/functions/wp_upload_dir/
	 *
	 * @since 3.4
	 *
	 * @param string $url Full image URL.
	 * @param bool $sizes_active Also search among image sizes
	 *
	 * @return int
	 */
	public static function convert_image_url_to_id_individually( $url, $sizes_active = false ) {
		$id = 0;

		$uploads_dir = wp_upload_dir();
		// Check to make sure Image URL is not outside the website.
		if ( false !== strpos( $url, $uploads_dir['baseurl'] . '/' ) ) {
			// search like main image
			$id = attachment_url_to_postid( $url );

			if ( ! $id ) {
				// search like custom image
				$id = self::get_single_custom_image_id( $url );
			}
		}

		return $id;
	}

	/**
	 * Attachment URL to Post ID main images look over
	 *
	 * This is intended to work solely with `aiosp_common::convert_image_url_to_id_bulky()`.
	 * NOTE: Calling this multiple times is memory intense.
	 *
	 * @see wpdb::get_results()
	 * @link https://developer.wordpress.org/reference/classes/wpdb/get_results/
	 *
	 * @return array
	 */
	public static function get_all_main_images() {
		global $wpdb;
		$image_id_url = [];

		$results = $wpdb->get_results(
			"SELECT post_id, meta_value FROM $wpdb->postmeta WHERE meta_key='_wp_attached_file' ",
			ARRAY_A
		);

		$upload_dir = wp_upload_dir();
		$upload_dir = $upload_dir['baseurl'] . '/';

		$image_id_url = array();
		foreach ( $results as $image_data ) {
			$post_id = $image_data['post_id'];
			$image_meta = $image_data['meta_value'];

			$image_id_url[$upload_dir . $image_meta] = $post_id;
		}

		return $image_id_url;
	}

	/**
	 * Attachment URL to Post ID custom size images look over.
	 *
	 * It's intended to query for custom images, and data for those types
	 * of images only exists in the postmeta database table
	 * This is intended to work solely with `aiosp_common::convert_image_url_to_id_bulky()`.
	 * NOTE: Calling this multiple times is memory intense.
	 *
	 * @see wpdb::get_results()
	 * @link https://developer.wordpress.org/reference/classes/wpdb/get_results/
	 *
	 * @return array
	 */
	public static function get_all_custom_images() {
		global $wpdb;
		$image_id_url = [];

		$results = $wpdb->get_results(
			"SELECT post_id, meta_value FROM $wpdb->postmeta WHERE meta_key='_wp_attachment_metadata'",
			ARRAY_A
		);

		$upload_dir = wp_upload_dir();
		$upload_dir = $upload_dir['baseurl'] . '/';

		$image_id_url = array();
		foreach ( $results as $image_data ) {
			$image_meta = $image_data['meta_value'];
			$post_id = $image_data['post_id'];

			$image_meta = unserialize( $image_meta );

			if ( ! empty( $image_meta['file'] ) && ! empty( $image_meta['sizes'] ) ) {

				$main_image = $image_meta['file'];
				$upload_path = substr( $main_image, 0,  strpos( $main_image, strrchr( $main_image, '/' ) ) );
				$image_sizes = $image_meta['sizes'];

				foreach ( $image_sizes as $size ) {
					$image_id_url[$upload_dir . $upload_path . '/' . $size['file']] = $post_id;
				}
			}
		}

		return $image_id_url;
	}

	/**
	 * Attachment URL to Post ID - Query 2.
	 *
	 * It's intended to query for custom images, and data for those types
	 * of images only exists in the postmeta database table
	 * This is intended to work solely with `aiosp_common::convert_image_url_to_id_individually()`.
	 * NOTE: Calling this multiple times is memory intense.
	 * NOTE: For a paginatnated query case try to use aiosp_common:convert_image_url_to_id_bulky istead.
	 *
	 * @see get_posts
	 * @link https://developer.wordpress.org/reference/functions/get_posts/
	 * @see wp_upload_dir()
	 * @link https://developer.wordpress.org/reference/functions/wp_upload_dir/
	 *
	 * @param string $url Custom image URL.
	 *
	 * @return null|int
	 */
	public static function get_single_custom_image_id( $url ) {
		$file  = basename( $url );
		$query = array(
			'post_type'  => 'attachment',
			'fields'     => 'ids',
			'meta_query' => array(
				array(
					'key'     => '_wp_attachment_metadata',
					'value'   => $file,
					'compare' => 'LIKE',
				),
			)
		);

		$ids = get_posts( $query );

		if ( ! empty( $ids ) ) {

			foreach ( $ids as $id ) {
				$meta = wp_get_attachment_metadata( $id );

				foreach ( $meta['sizes'] as $size => $values ) {
					$image_src = wp_get_attachment_image_src( $id, $size );
					if ( $values['file'] === $file && $url === array_shift( $image_src ) ) {
						return $id;
					}
				}
			}
		}
	}

	/**
	 * Set Transient URL Post IDs.
	 *
	 * Sets the transient data at the last hook instead at every call.
	 *
	 * @see set_transient()
	 * @link https://developer.wordpress.org/reference/functions/set_transient/
	 *
	 * @since 2.9.2
	 */
	public static function set_transient_url_postids() {
		if ( is_multisite() ) {
			set_site_transient( 'aioseop_multisite_attachment_url_postids', self::$attachment_url_postids, 24 * HOUR_IN_SECONDS );
		} else {
			set_transient( 'aioseop_attachment_url_postids', self::$attachment_url_postids, 24 * HOUR_IN_SECONDS );
		}
	}

	/**
	 * Error Hand Images
	 *
	 * Unused/Conceptual function potentually used in `aiosp_common::attachment_url_to_post_id_query_2()`.
	 * This is to handle errors where a normal try/catch wouldn't have the exception needed to catch.
	 *
	 * @see aiosp_common::attachment_url_to_post_id_query_2()
	 *
	 * @param $errno
	 * @param $errstr
	 * @param $errfile
	 * @param $errline
	 * @return bool
	 * @throws ErrorException
	 */
	public static function error_handle_images( $errno, $errstr, $errfile, $errline ) {
		// Possibly handle known issues differently.
		// Handles unserialize() warning notice.
		if ( 8 === $errno || strpos( $errstr , 'unserialize():' ) ) {
			throw new ErrorException( $errstr, $errno, 0, $errfile, $errline );
		} else {
			throw new ErrorException( $errstr, $errno, 0, $errfile, $errline );
		}

		return false;
	}
}
