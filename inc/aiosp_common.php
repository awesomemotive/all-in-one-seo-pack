<?php

/**
 * @package All-in-One-SEO-Pack
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
	 * The allowed image extensions.
	 *
	 * @var      array $image_extensions The allowed image extensions.
	 */
	private static $image_extensions    = array(
		'jpg',
		'jpeg',
		'png',
		'gif',
	);

	/**
	 * aiosp_common constructor.
	 *
	 */
	function __construct() {

	}

	/**
	 * Clears WP Engine cache.
	 */
	static function clear_wpe_cache() {
		if ( class_exists( 'WpeCommon' ) ) {
			WpeCommon::purge_memcached();
			WpeCommon::clear_maxcdn_cache();
			WpeCommon::purge_varnish_cache();
		}
	}

	/**
	 * @param null $p
	 *
	 * @return array|null|string|WP_Post
	 */
	static function get_blog_page( $p = null ) {
		static $blog_page = '';
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
	 * @param string $location
	 * @param string $title
	 * @param string $anchor
	 * @param string $target
	 * @param string $class
	 * @param string $id
	 *
	 * @return string
	 */
	static function get_upgrade_hyperlink( $location = '', $title = '', $anchor = '', $target = '', $class = '', $id = 'aio-pro-update' ) {

		$affiliate_id = '';

		// call during plugins_loaded
		$affiliate_id = apply_filters( 'aiosp_aff_id', $affiliate_id );

		// build URL
		$url = 'https://semperplugins.com/all-in-one-seo-pack-pro-version/';
		if ( $location ) {
			$url .= '?loc=' . $location;
		}
		if ( $affiliate_id ) {
			$url .= "?ap_id=$affiliate_id";
		}

		// build hyperlink
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
	 * Gets the upgrade to Pro version URL.
	 */
	static function get_upgrade_url() {
		// put build URL stuff in here
	}

	/**
	 * Check whether a url is relative and if it is, make it absolute.
	 *
	 * @param string $url URL to check.
	 *
	 * @return string
	 */
	static function absolutize_url( $url ) {
		if ( 0 !== strpos( $url, 'http' ) && '/' !== $url ) {
			if ( 0 === strpos( $url, '//' ) ) {
				// for //<host>/resource type urls.
				$scheme = parse_url( home_url(), PHP_URL_SCHEME );
				$url    = $scheme . ':' . $url;
			} else {
				// for /resource type urls.
				$url = home_url( $url );
			}
		}
		return $url;
	}

	/**
	 * Check whether a url is relative (does not contain a . before the first /) or absolute and makes it a valid url.
	 *
	 * @param string $url URL to check.
	 *
	 * @return string
	 */
	static function make_url_valid_smartly( $url ) {
		$scheme = parse_url( home_url(), PHP_URL_SCHEME );
		if ( 0 !== strpos( $url, 'http' ) ) {
			if ( 0 === strpos( $url, '//' ) ) {
				// for //<host>/resource type urls.
				$url    = $scheme . ':' . $url;
			} elseif ( strpos( $url, '.' ) !== false && strpos( $url, '/' ) !== false && strpos( $url, '.' ) < strpos( $url, '/' ) ) {
				// if the . comes before the first / then this is absolute.
				$url    = $scheme . '://' . $url;
			} else {
				// for /resource type urls.
				$url = home_url( $url );
			}
		} else if ( strpos( $url, 'http://' ) === false ) {
			if ( 0 === strpos( $url, 'http:/' ) ) {
				$url	= $scheme . '://' .  str_replace( 'http:/', '', $url );
			} else if ( 0 === strpos( $url, 'http:' ) ) {
				$url	= $scheme . '://' . str_replace( 'http:', '', $url );
			}
		}
		return $url;
	}

	/**
	 * Check whether a url is valid.
	 *
	 * @param string $url URL to check.
	 *
	 * @return bool
	 */
	public static function is_url_valid( $url ) {
		return filter_var( filter_var( $url, FILTER_SANITIZE_URL ), FILTER_VALIDATE_URL, FILTER_FLAG_SCHEME_REQUIRED | FILTER_FLAG_HOST_REQUIRED ) !== false;
	}

	/**
	 * Validate the image.
	 * NOTE: We will use parse_url here instead of wp_parse_url as we will correct the URLs beforehand and 
	 * this saves us the need to check PHP version support.
	 *
	 * @param string $image The image src.
	 *
	 * @since 2.4.1
	 * @since 2.4.3 Compatibility with Pre v4.7 wp_parse_url().
	 *
	 * @return bool
	 */
	public static function is_image_valid( $image ) {
		global $wp_version;

		// Bail if empty image.
		if ( empty( $image ) ) {
			return false;
		}

		global $wp_version;
		if ( version_compare( $wp_version, '4.4', '<' ) ) {
			$p_url = parse_url( $image );
			$url = $p_url['scheme'] . $p_url['host'] . $p_url['path'];
		} elseif ( version_compare( $wp_version, '4.7', '<' ) ) {
			// Compatability for older WP version that don't have 4.7 changes.
			// @link https://core.trac.wordpress.org/changeset/38726
			$p_url = wp_parse_url( $image );
			$url = $p_url['scheme'] . $p_url['host'] . $p_url['path'];
		} else {
			$component = PHP_URL_PATH;
			$url = wp_parse_url( $image, $component );
		}

		// make the url absolute, if its relative.
		$image      = aiosp_common::absolutize_url( $image );

		$extn       = pathinfo( parse_url( $image, PHP_URL_PATH ), PATHINFO_EXTENSION );
		$allowed    = apply_filters( 'aioseop_allowed_image_extensions', self::$image_extensions );
		// Bail if image does not refer to an image file otherwise google webmaster tools might reject the sitemap.
		if ( ! in_array( $extn, $allowed, true ) ) {
			return false;
		}

		$image_host = parse_url( $image, PHP_URL_HOST );
		$host       = parse_url( home_url(), PHP_URL_HOST );

		if ( $image_host !== $host ) {
			// Allowed hosts will be provided in a wildcard format i.e. img.yahoo.* or *.akamai.*.
			// And we will convert that into a regular expression for matching.
			$whitelist  = apply_filters( 'aioseop_images_allowed_from_hosts', array() );
			$allowed    = false;
			if ( $whitelist ) {
				foreach ( $whitelist as $pattern ) {
					if ( preg_match( '/' . str_replace( '*', '.*', $pattern ) . '/', $image_host ) === 1 ) {
						$allowed = true;
						break;
					}
				}
			}
			return $allowed;

		}
		return true;
	}

}