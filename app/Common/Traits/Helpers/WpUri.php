<?php
namespace AIOSEO\Plugin\Common\Traits\Helpers;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AIOSEO\Plugin\Common\Integrations\BuddyPress as BuddyPressIntegration;

/**
 * Contains all WordPress related URL, URI, path, slug, etc. related helper methods.
 *
 * @since 4.1.4
 */
trait WpUri {
	/**
	 * Returns the site domain.
	 *
	 * @since 4.0.0
	 *
	 * @param  bool   $unfiltered Whether to get the unfiltered value.
	 * @return string             The site's domain.
	 */
	public function getSiteDomain( $unfiltered = false ) {
		return wp_parse_url( $this->getHomeUrl( $unfiltered ), PHP_URL_HOST );
	}

	/**
	 * Returns the site URL.
	 * NOTE: For multisites inside a sub-directory, this returns the URL for the main site.
	 * This is intentional.
	 *
	 * @since 4.0.0
	 *
	 * @param  bool   $unfiltered Whether to get the unfiltered value.
	 * @return string             The site's domain.
	 */
	public function getSiteUrl( $unfiltered = false ) {
		$homeUrl = $this->getHomeUrl( $unfiltered );

		return wp_parse_url( $homeUrl, PHP_URL_SCHEME ) . '://' . wp_parse_url( $homeUrl, PHP_URL_HOST );
	}

	/**
	 * Returns the current URL.
	 *
	 * @since 4.0.0
	 *
	 * @param  boolean $canonical Whether or not to get the canonical URL.
	 * @return string             The URL.
	 */
	public function getUrl( $canonical = false ) {
		$url = '';
		if ( is_singular() ) {
			$objectId = aioseo()->helpers->getPostId();

			if ( $canonical ) {
				$url = aioseo()->helpers->wpGetCanonicalUrl( $objectId );
			}

			if ( ! $url ) {
				// wp_get_canonical_url() returns false if the post isn't published.
				// Therefore, we must to fall back to the permalink if the post isn't published, e.g. draft post or attachment (inherit).
				$url = get_permalink( $objectId );
			}
		}

		if ( $url ) {
			return $url;
		}

		global $wp, $wp_rewrite; // phpcs:ignore Squiz.NamingConventions.ValidVariableName
		// Permalink url without the query string.
		$url = user_trailingslashit( home_url( $wp->request ) );

		// If permalinks are not being used we need to append the query string to the home url.
		if ( ! $wp_rewrite->using_permalinks() ) { // phpcs:ignore Squiz.NamingConventions.ValidVariableName
			$url = home_url( ! empty( $wp->query_string ) ? '?' . $wp->query_string : '' );
		}

		return $url;
	}

	/**
	 * Gets the canonical URL for the current page/post.
	 *
	 * @since 4.0.0
	 *
	 * @return string $url The canonical URL.
	 */
	public function canonicalUrl() {
		$queriedObject = get_queried_object(); // Don't use our getTerm helper here.
		$hash          = md5( wp_json_encode( $queriedObject ?? [] ) );

		static $url = [];
		if ( isset( $url[ $hash ] ) ) {
			return $url[ $hash ];
		}

		if ( is_404() || is_search() ) {
			$url[ $hash ] = apply_filters( 'aioseo_canonical_url', '' );

			return $url[ $hash ];
		}

		$metaData = [];
		$post     = $this->getPost();
		if ( $post ) {
			$metaData = aioseo()->meta->metaData->getMetaData( $post );
		}

		if ( is_category() || is_tag() || is_tax() ) {
			$metaData     = aioseo()->meta->metaData->getMetaData( $queriedObject );
			$url[ $hash ] = get_term_link( $queriedObject, $queriedObject->taxonomy ?? '' );

			// Add pagination to the URL. We need to do this here because get_term_link() doesn't handle pagination.
			// We'll strip it further down if no pagination for canonical is enabled.
			if ( $this->getPageNumber() > 1 ) {
				$url[ $hash ] = user_trailingslashit( $url[ $hash ] . 'page/' . $this->getPageNumber() );
			}
		}

		if ( $metaData && ! empty( $metaData->canonical_url ) ) {
			$url[ $hash ] = apply_filters( 'aioseo_canonical_url', $this->makeUrlAbsolute( $metaData->canonical_url ) );

			return $url[ $hash ];
		}

		if ( BuddyPressIntegration::isComponentPage() ) {
			$url[ $hash ] = aioseo()->standalone->buddyPress->component->getMeta( 'canonical' );
		}

		if ( empty( $url[ $hash ] ) || is_wp_error( $url[ $hash ] ) ) {
			$url[ $hash ] = $this->getUrl( true );
		}

		$pageNumber = $this->getPageNumber();
		if (
			in_array( 'noPaginationForCanonical', aioseo()->internalOptions->deprecatedOptions, true ) &&
			aioseo()->options->deprecated->searchAppearance->advanced->noPaginationForCanonical
		) {
			global $wp_rewrite; // phpcs:ignore Squiz.NamingConventions.ValidVariableName
			if ( 1 < $pageNumber ) {
				if ( $wp_rewrite->using_permalinks() ) { // phpcs:ignore Squiz.NamingConventions.ValidVariableName
					// Replace /page/3 and /page/3/.
					$url[ $hash ] = preg_replace( "@(?<=/)page/$pageNumber(/|)$@", '', (string) $url[ $hash ] );
					// Replace /3 and /3/.
					$url[ $hash ] = preg_replace( "@(?<=/)$pageNumber(/|)$@", '', (string) $url[ $hash ] );
				} else {
					// Replace /?page_id=457&paged=1 and /?page_id=457&page=1.
					$url[ $hash ] = aioseo()->helpers->urlRemoveQueryParameter( $url[ $hash ], [ 'page', 'paged' ] );
				}
			}

			// Comment pages.
			$url[ $hash ] = preg_replace( '/(?<=\/)comment-page-\d+\/*(#comments)*$/', '', (string) $url[ $hash ] );
		}

		$url[ $hash ] = $this->maybeRemoveTrailingSlash( $url[ $hash ] );

		// Get rid of /amp at the end of the URL.
		if (
			aioseo()->helpers->isAmpPage() &&
			! apply_filters( 'aioseo_disable_canonical_url_amp', false )
		) {
			$url[ $hash ] = preg_replace( '/\/amp$/', '', (string) $url[ $hash ] );
			$url[ $hash ] = preg_replace( '/\/amp\/$/', '/', (string) $url[ $hash ] );
		}

		$url[ $hash ] = apply_filters( 'aioseo_canonical_url', $url[ $hash ] );

		return $url[ $hash ];
	}

	/**
	 * Formats a given URL as an absolute URL if it is relative.
	 *
	 * @since 4.0.0
	 *
	 * @param  string $url The URL.
	 * @return string $url The absolute URL.
	 */
	public function makeUrlAbsolute( $url ) {
		if ( 0 !== strpos( $url, 'http' ) && '/' !== $url ) {
			$url = $this->sanitizeDomain( $url );
			if ( $this->isDomainWithPaths( $url ) ) {
				$scheme = wp_parse_url( home_url(), PHP_URL_SCHEME );
				$url    = $scheme . '://' . $url;
			} elseif ( 0 === strpos( $url, '//' ) ) {
				$scheme = wp_parse_url( home_url(), PHP_URL_SCHEME );
				$url    = $scheme . ':' . $url;
			} else {
				$url = home_url( $url );
			}
		}

		return $url;
	}

	/**
	 * Sanitizes a given domain.
	 *
	 * @since 4.0.0
	 *
	 * @param  string       $domain The domain to sanitize.
	 * @return mixed|string         The sanitized domain.
	 */
	public function sanitizeDomain( $domain ) {
		$domain = trim( $domain );
		$domain = strtolower( $domain );
		if ( 0 === strpos( $domain, 'http://' ) ) {
			$domain = substr( $domain, 7 );
		} elseif ( 0 === strpos( $domain, 'https://' ) ) {
			$domain = substr( $domain, 8 );
		}
		$domain = untrailingslashit( $domain );

		return $domain;
	}

	/**
	 * Remove trailing slashes if not set in the permalink structure.
	 *
	 * @since 4.0.0
	 *
	 * @param  string $url The original URL.
	 * @return string      The adjusted URL.
	 */
	public function maybeRemoveTrailingSlash( $url ) {
		$permalinks = get_option( 'permalink_structure' );
		if ( $permalinks && ( ! is_home() || ! is_front_page() ) ) {
			$trailing = substr( $permalinks, -1 );
			if ( '/' !== $trailing ) {
				$url = untrailingslashit( $url );
			}
		}

		// Don't slash urls with query args.
		if ( false !== strpos( $url, '?' ) ) {
			$url = untrailingslashit( $url );
		}

		return $url;
	}

	/**
	 * Removes image dimensions from the slug of a URL.
	 *
	 * @since 4.0.0
	 *
	 * @param  string $url The image URL.
	 * @return string      The formatted image URL.
	 */
	public function removeImageDimensions( $url ) {
		return $this->isValidAttachment( $url ) ? preg_replace( '#(-[0-9]*x[0-9]*|-scaled)#', '', (string) $url ) : $url;
	}

	/**
	 * Returns the URL for the WP content folder.
	 *
	 * @since 4.0.5
	 *
	 * @return string The URL.
	 */
	public function getWpContentUrl() {
		$info = wp_get_upload_dir();

		return isset( $info['baseurl'] ) ? $info['baseurl'] : '';
	}

	/**
	* Retrieves a post by its given path.
	* Based on the built-in get_page_by_path() function, but only checks ancestry if the post type is actually hierarchical.
	*
	* @since 4.1.4
	*
	* @param  string       $path     The path.
	* @param  string       $output   The output type. OBJECT, ARRAY_A, or ARRAY_N.
	* @param  string|array $postType The post type(s) to check against.
	* @return object|false           The post or false on failure.
	*/
	public function getPostByPath( $path, $output = OBJECT, $postType = 'page' ) {
		$lastChanged = wp_cache_get_last_changed( 'aioseo_posts_by_path' );
		$hash        = md5( $path . serialize( $postType ) );
		$cacheKey    = "get_page_by_path:$hash:$lastChanged";
		$cached      = wp_cache_get( $cacheKey, 'aioseo_posts_by_path' );

		if ( false !== $cached ) {
			// Special case: '0' is a bad `$path`.
			if ( '0' === $cached || 0 === $cached ) {
				return false;
			}

			return get_post( $cached, $output );
		}

		$path          = rawurlencode( urldecode( $path ) );
		$path          = str_replace( '%2F', '/', $path );
		$path          = str_replace( '%20', ' ', $path );
		$parts         = explode( '/', trim( $path, '/' ) );
		$reversedParts = array_reverse( $parts );
		$postNames     = "'" . implode( "','", $parts ) . "'";

		$postTypes = is_array( $postType ) ? $postType : [ $postType, 'attachment' ];
		$postTypes = "'" . implode( "','", $postTypes ) . "'";

		$posts = aioseo()->core->db->start( 'posts' )
			->select( 'ID, post_name, post_parent, post_type' )
			->whereRaw( "post_name in ( $postNames )" )
			->whereRaw( "post_type in ( $postTypes )" )
			->run()
			->result();

		$foundId = 0;
		foreach ( $posts as $post ) {
			if ( $post->post_name === $reversedParts[0] ) {
				$count = 0;
				$p     = $post;

				// Loop through the given path parts from right to left, ensuring each matches the post ancestry.
				while ( 0 !== (int) $p->post_parent && isset( $posts[ $p->post_parent ] ) ) {
					$count++;
					$parent = $posts[ $p->post_parent ];
					if ( ! isset( $reversedParts[ $count ] ) || $parent->post_name !== $reversedParts[ $count ] ) {
						break;
					}
					$p = $parent;
				}

				if (
					0 === (int) $p->post_parent &&
					( ! is_post_type_hierarchical( $p->post_type ) || count( $reversedParts ) === $count + 1 ) &&
					$p->post_name === $reversedParts[ $count ]
				) {
					$foundId = $post->ID;
					if ( $post->post_type === $postType ) {
						break;
					}
				}
			}
		}

		// We cache misses as well as hits.
		wp_cache_set( $cacheKey, $foundId, 'aioseo_posts_by_path' );

		return $foundId ? get_post( $foundId, $output ) : false;
	}

	/**
	 * Validates a URL.
	 *
	 * @since 4.1.2
	 *
	 * @param  string $url The url.
	 * @return bool        Is it a valid/safe url.
	 */
	public function isUrl( $url ) {
		return esc_url_raw( $url ) === $url;
	}

	/**
	 * Retrieves the parameters for a given URL.
	 *
	 * @since 4.1.5
	 *
	 * @param  string $url          The url.
	 * @return array                The parameters.
	 */
	public function getParametersFromUrl( $url ) {
		$parsedUrl  = wp_parse_url( wp_unslash( $url ) );
		$parameters = [];

		if ( empty( $parsedUrl['query'] ) ) {
			return [];
		}

		wp_parse_str( $parsedUrl['query'], $parameters );

		return $parameters;
	}

	/**
	 * Adds a leading slash to an url.
	 *
	 * @since 4.1.8
	 *
	 * @param  string $url The url.
	 * @return string      The url with a leading slash.
	 */
	public function leadingSlashIt( $url ) {
		return '/' . ltrim( $url, '/' );
	}

	/**
	 * Returns the path from a permalink.
	 * This function will help get the correct path from WP installations in subfolders.
	 *
	 * @since 4.1.8
	 *
	 * @param  string $permalink A permalink from get_permalink().
	 * @return string            The path without the home_url().
	 */
	public function getPermalinkPath( $permalink ) {
		// We want to get this value straight from the DB to prevent plugins like WPML from filtering it.
		// This will otherwise mess with things like license activation requests and redirects.
		$homeUrl = $this->getHomeUrl( true );

		return $this->leadingSlashIt( str_replace( $homeUrl, '', $permalink ) );
	}

	/**
	 * Changed if permalinks are different and the before wasn't
	 * the site url (we don't want to redirect the site URL).
	 *
	 * @since 4.2.3
	 *
	 * @param  string  $before The URL before the change.
	 * @param  string  $after  The URL after the change.
	 * @return boolean         True if the permalink has changed.
	 */
	public function hasPermalinkChanged( $before, $after ) {
		// Check it's not redirecting from the root.
		if ( $this->getHomePath() === $before || '/' === $before ) {
			return false;
		}

		// Are the URLs the same?
		return ( $before !== $after );
	}

	/**
	 * Retrieve the home path.
	 *
	 * @since 4.2.3
	 *
	 * @param  bool   $unfiltered Whether to get the unfiltered value.
	 * @return string              The home path.
	 */
	public function getHomePath( $unfiltered = false ) {
		$path = wp_parse_url( $this->getHomeUrl( $unfiltered ), PHP_URL_PATH );

		return $path ? trailingslashit( $path ) : '/';
	}

	/**
	 * Returns the home URL.
	 *
	 * @since 4.7.3
	 *
	 * @param  bool   $unfiltered Whether to get the unfiltered value.
	 * @return string             The home URL.
	 */
	private function getHomeUrl( $unfiltered = false ) {
		$homeUrl = home_url();
		if ( $unfiltered ) {
			// We want to get this value straight from the DB to prevent plugins like WPML from filtering it.
			// This will otherwise mess with things like license activation requests and redirects.
			$homeUrl = get_option( 'home' );
		}

		return $homeUrl;
	}

	/**
	 * Checks if the given URL is an internal URL for the current site.
	 *
	 * @since 4.2.6
	 *
	 * @param  string $urlToCheck The URL to check.
	 * @return bool               Whether the given URL is an internal one.
	 */
	public function isInternalUrl( $urlToCheck ) {
		$parsedHomeUrl    = wp_parse_url( home_url() );
		$parsedUrlToCheck = wp_parse_url( $urlToCheck );

		return ! empty( $parsedHomeUrl['host'] ) && ! empty( $parsedUrlToCheck['host'] )
			? $parsedHomeUrl['host'] === $parsedUrlToCheck['host']
			: false;
	}

	/**
	 * Helper for the rest url.
	 *
	 * @since 4.4.9
	 *
	 * @return string
	 */
	public function getRestUrl() {
		$restUrl = get_rest_url();

		if ( aioseo()->helpers->isWpmlActive() ) {
			global $sitepress;

			// Replace the rest url 'all' language prefix so our rest calls don't fail.
			if (
				is_object( $sitepress ) &&
				method_exists( $sitepress, 'get_current_language' ) &&
				method_exists( $sitepress, 'get_default_language' ) &&
				'all' === $sitepress->get_current_language()
			) {
				$restUrl = str_replace(
					get_home_url( null, '/all/' ),
					get_home_url( null, '/' . $sitepress->get_default_language() . '/' ),
					$restUrl
				);
			}
		}

		return $restUrl;
	}

	/**
	 * Exclude the home path from a full path.
	 *
	 * @since   1.2.3 Moved from aioseo-redirects.
	 * @version 4.5.8
	 *
	 * @param  string $path The original path.
	 * @return string       The path without WP's home path.
	 */
	public function excludeHomePath( $path ) {
		return preg_replace( '@^' . $this->getHomePath() . '@', '/', (string) $path );
	}

	/**
	 * Get the canonical URL for a post.
	 * This is a duplicate of wp_get_canonical_url() with a fix for issue #6372 where
	 * posts with paginated comment pages return the wrong canonical URL due to how WordPress sets the cpage var.
	 * We can remove this once trac ticket 60806 is resolved.
	 *
	 * @since 4.6.9
	 *
	 * @param  \WP_Post|int|null $post The post object or ID.
	 * @return string|false            The post's canonical URL, or false if the post is not published.
	 */
	public function wpGetCanonicalUrl( $post = null ) {
		$post = get_post( $post );

		if ( ! $post ) {
			return false;
		}

		if ( 'publish' !== $post->post_status ) {
			return false;
		}

		$canonical_url = get_permalink( $post ); // phpcs:ignore Squiz.NamingConventions.ValidVariableName

		// If a canonical is being generated for the current page, make sure it has pagination if needed.
		if ( get_queried_object_id() === $post->ID ) {
			$page = get_query_var( 'page', 0 );
			if ( $page >= 2 ) {
				if ( ! get_option( 'permalink_structure' ) ) {
					$canonical_url = add_query_arg( 'page', $page, $canonical_url ); // phpcs:ignore Squiz.NamingConventions.ValidVariableName
				} else {
					$canonical_url = trailingslashit( $canonical_url ) . user_trailingslashit( $page, 'single_paged' ); // phpcs:ignore Squiz.NamingConventions.ValidVariableName
				}
			}

			$cpage = aioseo()->helpers->getCommentPageNumber(); // We're calling our own function here to get the correct cpage number.
			if ( $cpage ) {
				$canonical_url = get_comments_pagenum_link( $cpage ); // phpcs:ignore Squiz.NamingConventions.ValidVariableName
			}
		}

		return apply_filters( 'get_canonical_url', $canonical_url, $post ); // phpcs:ignore Squiz.NamingConventions.ValidVariableName
	}
}