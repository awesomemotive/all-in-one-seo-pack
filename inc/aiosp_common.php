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
		return filter_var( filter_var( $url, FILTER_SANITIZE_URL ), FILTER_VALIDATE_URL ) !== false;
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
	 * Attachment URL to Post ID
	 *
	 * Returns the (original) post/attachment ID from the URL param given. The function checks if URL is
	 * within, chacks for original attachment URLs, and then custom attachment URLs. The main intent for this function
	 * is to avoid having to query if possible (if cache was set prior), and if not, there is only 1 query per instance
	 * rather than multiple queries per instance.
	 * NOTE: Attempting to paginate the query actually caused the memory to peak higher.
	 * NOTE: The weakest point in this function is multiple calls to Result_2's SQL query for custom attachment URLs.
	 *
	 * This is intended to work much the same way as WP's `attachment_url_to_postid()`.
	 *
	 * @link https://developer.wordpress.org/reference/functions/attachment_url_to_postid/
	 *
	 * @see aiosp_common::set_transient_url_postids()
	 * @see get_transient()
	 * @link https://developer.wordpress.org/reference/functions/get_transient/
	 * @see wpdb::get_results()
	 * @link https://developer.wordpress.org/reference/classes/wpdb/get_results/
	 * @see wp_list_pluck()
	 * @link https://developer.wordpress.org/reference/functions/wp_list_pluck/
	 * @see wp_upload_dir()
	 * @link https://developer.wordpress.org/reference/functions/wp_upload_dir/
	 *
	 * @since 2.9.2
	 *
	 * @param string $url Full image URL.
	 * @return int
	 */
	public static function attachment_url_to_postid( $url ) {
		global $wpdb;
		static $results_1;
		static $results_2;

		$id      = 0;
		$url_md5 = md5( $url );

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
		if ( isset( self::$attachment_url_postids[ $url_md5 ] ) ) {
			// If static is already loaded and has URL, then return the URL's Post ID.
			$id = intval( self::$attachment_url_postids[ $url_md5 ] );
		} else {
			// Check to make sure Image URL is not outside the website.
			$uploads_dir = wp_upload_dir();
			if ( false !== strpos( $url, $uploads_dir['baseurl'] . '/' ) ) {
				// Results_1 query looks for URLs with the original guid that is uncropped and unedited.
				if ( is_null( $results_1 ) ) {
					$results_1 = aiosp_common::attachment_url_to_postid_query_1();
				}

				if ( isset( $results_1[ $url_md5 ] ) ) {
					$id = intval( $results_1[ $url_md5 ] );
				}

				// phpcs:disable Squiz.Commenting.InlineComment.InvalidEndChar
				// TODO Add setting to enable; this is TOO MEMORY INTENSE which could result in 1 or more crashes,
				// TODO however some may still need custom image URLs.
				// TODO NOTE: Transient data does prevent continual crashes.
				// else {
				// Results_2 query looks for the URL that is cropped and edited. This searches JSON strings
				// and returns the original attachment ID (there is no custom attachment IDs).
				//
				// if ( is_null( $results_2 ) ) {
				// $results_2 = aiosp_common::attachment_url_to_postid_query_2();
				// }
				//
				// if ( isset( $results_2[ $url_md5 ] ) ) {
				// $id = intval( $results_2[ $url_md5 ] );
				// }
				// }
				// phpcs:enable
			}

			self::$attachment_url_postids[ $url_md5 ] = $id;

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
	 * Set Transient URL Post IDs
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
	 * Attachment URL to Post ID - Query 1
	 *
	 * This is intended to work solely with `aiosp_common::attachment_url_to_post_id()`. Calling this multiple times
	 * is memory intense.
	 *
	 * @see wpdb::get_results()
	 * @link https://developer.wordpress.org/reference/classes/wpdb/get_results/
	 *
	 * @return array
	 */
	public static function attachment_url_to_postid_query_1() {
		global $wpdb;

		$results_1 = $wpdb->get_results(
			"SELECT ID, MD5(guid) AS guid FROM $wpdb->posts WHERE post_type='attachment' AND post_status='inherit' AND post_mime_type LIKE 'image/%';",
			ARRAY_A
		);

		if ( $results_1 ) {
			$results_1 = array_combine(
				wp_list_pluck( $results_1, 'guid' ),
				wp_list_pluck( $results_1, 'ID' )
			);
		} else {
			$results_1 = array();
		}

		return $results_1;
	}

	/**
	 * Attachment URL to Post ID - Query 2
	 *
	 * Unused/Conceptual function. This is intended to work solely with `aiosp_common::attachment_url_to_post_id()`.
	 * Calling this multiple times is memory intense. It's intended to query for custom images, and data for those types
	 * of images only exists in the postmeta database table
	 *
	 * @todo Investigate unserialize() memory consumption/leak.
	 * @link https://www.evonide.com/breaking-phps-garbage-collection-and-unserialize/
	 *
	 * @see aiosp_common::attachment_url_to_postid()
	 * @see unserialize()
	 * @link http://php.net/manual/en/function.unserialize.php
	 * @see wpdb::get_results()
	 * @link https://developer.wordpress.org/reference/classes/wpdb/get_results/
	 * @see wp_upload_dir()
	 * @link https://developer.wordpress.org/reference/functions/wp_upload_dir/
	 *
	 * @return array
	 */
	public static function attachment_url_to_postid_query_2() {
		global $wpdb;

		$tmp_arr = array();
		// @codingStandardsIgnoreStart WordPress.WP.PreparedSQL.NotPrepared
		$results_2 = $wpdb->get_results(
			"SELECT post_id, meta_value FROM {$wpdb->postmeta} WHERE `meta_key` = '_wp_attachment_metadata' AND `meta_value` != '" . serialize( array() ) . "';",
			ARRAY_A
		);
		// @codingStandardsIgnoreStop WordPress.WP.PreparedSQL.NotPrepared
		if ( $results_2 ) {
			for ( $i = 0; $i < count( $results_2 ); $i++ ) {
				// TODO Investigate potentual memory leak(s); currently with unserialize.
				$meta_value = maybe_unserialize( $results_2[ $i ]['meta_value'] );

				// TODO Needs Discussion: Should this be added? To handle errors better instead of suspecting aioseop is at fault and lessen support threads.
				/**
				 * This currently handles "warning" notices with unserialize which normally can't be handled with a try/catch.
				 * However, this notice should be identified and corrected; which is seperate from the plugin, but
				 * can also triggered by the plugin.
				 *
				 * @see aiosp_common::error_handle_images()
				 * @see set_error_handler()
				 * @link http://php.net/manual/en/function.set-error-handler.php
				 * @see restore_error_handler()
				 * @link http://php.net/manual/en/function.restore-error-handler.php
				 */
				/*
				set_error_handler( 'aiosp_common::error_handle_images' );
				try {
					$meta_value = unserialize( $results_2[ $i ]['meta_value'] );
				} catch ( Exception $e ) {
					unset( $meta_value );
					restore_error_handler();
					continue;
				}
				restore_error_handler();
				*/

				// Images and Videos use different variable structures.
				if ( false === $meta_value || ! isset( $meta_value['file'] ) && ! isset( $meta_value['sizes'] ) ) {
					continue;
				}

				// Set the URL => PostIDs.
				$uploads_dir = wp_upload_dir();
				$custom_img_base_url = $uploads_dir['baseurl'] . '/' . str_replace( wp_basename( $meta_value['file'] ), '', $meta_value['file'] );
				foreach ( $meta_value['sizes'] as $image_size_arr ) {
					$tmp_arr[ md5( ( $custom_img_base_url . $image_size_arr['file'] ) ) ] = $results_2[ $i ]['post_id'];
				}

				unset( $meta_value );
			}
		}

		$results_2 = $tmp_arr;
		unset( $tmp_arr );

		return $results_2;
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
