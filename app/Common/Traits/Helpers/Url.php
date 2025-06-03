<?php
namespace AIOSEO\Plugin\Common\Traits\Helpers;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Contains URL helper methods.
 *
 * @since 4.2.5
 */
trait Url {
	/**
	 * Removes a query string parameter from a URL.
	 *
	 * @since 4.2.5
	 *
	 * @param  string $url        The url.
	 * @param  array  $parameters The parameter keys to remove.
	 * @return string             The url without the parameters removed.
	 */
	public function urlRemoveQueryParameter( $url, $parameters ) {
		$url = wp_parse_url( $url );
		if ( ! empty( $url['query'] ) ) {
			// Take the query string apart.
			parse_str( $url['query'], $queryStringArray );

			// Remove parameters.
			foreach ( $parameters as $parameter ) {
				if ( isset( $queryStringArray[ $parameter ] ) ) {
					unset( $queryStringArray[ $parameter ] );
				}
			}

			// Rebuild the query string.
			$url['query'] = build_query( $queryStringArray );

			// Rebuild the URL from parse_url.
			$url = $this->buildUrl( $url );
		}

		return $url;
	}

	/**
	 * Builds a URL from a parse_url array.
	 *
	 * @since 4.2.5
	 *
	 * @param  array  $params  The params array.
	 * @param  array  $include The keys to include [scheme, user, pass, host, port, path, query, fragment].
	 * @param  array  $exclude The keys to exclude [scheme, user, pass, host, port, path, query, fragment].
	 * @return string          The built url.
	 */
	public function buildUrl( $params, $include = [], $exclude = [] ) {
		if ( ! is_array( $params ) ) {
			return $params;
		}

		if ( ! empty( $include ) ) {
			foreach ( array_keys( $params ) as $includeKey ) {
				if ( ! in_array( $includeKey, $include, true ) ) {
					unset( $params[ $includeKey ] );
				}
			}
		}

		if ( ! empty( $exclude ) ) {
			foreach ( array_keys( $params ) as $excludeKey ) {
				if ( in_array( $excludeKey, $exclude, true ) ) {
					unset( $params[ $excludeKey ] );
				}
			}
		}

		$url = '';
		if ( ! empty( $params['scheme'] ) ) {
			$url .= $params['scheme'] . '://';
		}
		if ( ! empty( $params['user'] ) ) {
			$url .= $params['user'];

			if ( isset( $params['pass'] ) ) {
				$url .= ':' . $params['pass'];
			}

			$url .= '@';
		}

		if ( ! empty( $params['host'] ) ) {
			$url .= $params['host'];
		}

		if ( ! empty( $params['port'] ) ) {
			$url .= ':' . $params['port'];
		}

		if ( ! empty( $params['path'] ) ) {
			$url .= $params['path'];
		}

		if ( ! empty( $params['query'] ) ) {
			$url .= '?' . $params['query'];
		}

		if ( ! empty( $params['fragment'] ) ) {
			$url .= '#' . $params['fragment'];
		}

		return $url;
	}

	/**
	 * Checks if a URL is considered a local one.
	 *
	 * @since 4.5.9
	 *
	 * @param  string $url The URL.
	 * @return bool        Whether the URL is a local one or not.
	 */
	public function isLocalUrl( $url ) {
		$domain = wp_parse_url( $url, PHP_URL_HOST );
		if ( empty( $domain ) ) {
			return false;
		}

		if (
			false !== ip2long( $domain ) &&
			! filter_var( $domain, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE )
		) {
			return true;
		}

		if ( 'localhost' === $domain ) {
			return true;
		}

		if ( ! $this->isValidDomain( $domain ) ) {
			return true;
		}

		$tldsToCheck = [
			'.local',
			'.test',
		];

		foreach ( $tldsToCheck as $tld ) {
			if ( false !== strpos( $this->getTld( $domain ), $tld ) ) {
				return true;
			}
		}

		if ( substr_count( $domain, '.' ) > 1 ) {
			$subdomainsToCheck = [
				'dev',
				'development',
				'staging',
				'stage',
				'test',
				'staging*',
				'*staging',
				'dev*',
				'*dev',
				'test*',
				'*test'
			];

			foreach ( $subdomainsToCheck as $subdomain ) {
				foreach ( $this->getSubdomains( $domain ) as $sd ) {

					$subdomain = str_replace( '.', '(.)', $subdomain );
					$subdomain = str_replace( [ '*', '(.)' ], '(.*)', $subdomain );

					if ( preg_match( '/^(' . $subdomain . ')$/', (string) $sd ) ) {
						return true;
					}
				}
			}
		}

		return false;
	}

	/**
	 * Checks if a domain is valid.
	 *
	 * @since 4.5.9
	 *
	 * @param  string $domain The domain.
	 * @return bool           Whether the domain is valid or not.
	 */
	private function isValidDomain( $domain ) {
		// In case there are unicode characters, convert it into
		// IDNA ASCII URLs
		if ( function_exists( 'idn_to_ascii' ) ) {
			$domain = idn_to_ascii( $domain, IDNA_DEFAULT, INTL_IDNA_VARIANT_UTS46 );
		}

		if ( ! $domain ) {
			return false;
		}

		$domain = preg_replace( '/^\*\.+/', '', (string) $domain );

		return preg_match( '/^(?!\-)(?:[a-z\d\-]{0,62}[a-z\d]\.){1,126}(?!\d+)[a-z\d]{1,63}$/i', (string) $domain );
	}

	/**
	 * Checks if a domain is valid and optionally contains paths at the end.
	 *
	 * @since 4.7.7
	 *
	 * @param  string $domain The domain.
	 * @return bool           Whether the domain is valid or not.
	 */
	private function isDomainWithPaths( $domain ) {
		// In case there are unicode characters, convert it into IDNA ASCII URLs.
		if ( function_exists( 'idn_to_ascii' ) ) {
			$domain = idn_to_ascii( $domain, IDNA_DEFAULT, INTL_IDNA_VARIANT_UTS46 );
		}

		if ( ! $domain ) {
			return false;
		}

		$domain = preg_replace( '/^\*\.+/', '', $domain );

		return preg_match( '/^(?!\-)(?:[a-z\d\-]{0,62}[a-z\d]\.){1,126}(?!\d+)[a-z\d]{1,63}(\/[a-z\d\-\/]*)?$/i', $domain );
	}

	/**
	 * Returns a single string of all subdomains associated with this domain.
	 * Example 1: www
	 * Example 2: ww2.www
	 *
	 * @since 4.5.9
	 *
	 * @return array The subdomains associated with this domain.
	 */
	public function getSubdomains( $domain ) {
		// If we can't find a TLD, we won't be able to parse a subdomain.
		if ( empty( $this->getTld( $domain ) ) ) {
			return [];
		}

		// Return any subdomains as an array.
		return array_filter( explode( '.', rtrim( strstr( $domain, $this->getTld( $domain ), true ), '.' ) ) );
	}

	/**
	 * Returns the TLD associated with the given domain.
	 *
	 * @since 4.5.9
	 *
	 * @param  string $domain The domain.
	 * @return string         The TLD.
	 */
	public function getTld( $domain ) {
		if ( preg_match( '/(?P<tld>[a-z0-9][a-z0-9\-]{1,63}\.[a-z\.]{2,6})$/i', (string) $domain, $matches ) ) {
			return $matches['tld'];
		}

		return $domain;
	}

	/**
	 * Returns a decoded URL string.
	 *
	 * @since 4.6.7
	 *
	 * @param  string $url The URL string.
	 * @return string      The decoded URL.
	 */
	public function decodeUrl( $url ) {
		// Ensure input is a string to prevent errors.
		if ( ! is_string( $url ) ) {
			return $url;
		}

		// Set a reasonable iteration limit to prevent infinite loops.
		$maxIterations = 10;
		$iterations    = 0;

		$decodedUrl = rawurldecode( $url );
		while ( $decodedUrl !== $url && $iterations < $maxIterations ) {
			$url        = $decodedUrl;
			$decodedUrl = rawurldecode( $url );
			$iterations++;
		}

		return $decodedUrl;
	}

	/**
	 * Redirects to a specific URL.
	 *
	 * @since 4.8.0
	 *
	 * @param string $url    The URL to redirect to.
	 * @param int    $status The status code to use.
	 * @param string $reason The reason for redirecting.
	 *
	 * @return void
	 */
	public function redirect( $url, $status = 301, $reason = '' ) {
		$redirectBy = 'AIOSEO';
		if ( ! empty( $reason ) ) {
			$redirectBy .= ': ' . $reason;
		}

		wp_safe_redirect( $url, $status, $redirectBy );
		exit;
	}

	/**
	 * Checks if the given URL is external.
	 *
	 * @since 4.8.3
	 *
	 * @param  string $url The URL to check.
	 * @return bool        Whether the URL is external or not.
	 */
	public function isExternalUrl( $url ) {
		$parsedUrl = wp_parse_url( $url );
		if ( ! $parsedUrl ) {
			return false;
		}

		static $parsedSiteUrl = null;
		if ( ! $parsedSiteUrl ) {
			$parsedSiteUrl = wp_parse_url( site_url() );
		}

		return $parsedSiteUrl['host'] !== $parsedUrl['host'];
	}

	/**
	 * Checks if the given URL is relative.
	 *
	 * @since 4.8.3
	 *
	 * @param  string $url The URL to check.
	 * @return bool        Whether the URL is relative or not.
	 */
	public function isRelativeUrl( $url ) {
		$parsedUrl = wp_parse_url( $url );
		if ( ! $parsedUrl ) {
			return false;
		}

		return empty( $parsedUrl['scheme'] ) && empty( $parsedUrl['host'] );
	}

	/**
	 * Makes the given URL relative.
	 *
	 * @since 4.8.3
	 *
	 * @param  string $url The URL to make relative.
	 * @return string      The relative URL.
	 */
	public function makeUrlRelative( $url ) {
		$parsedUrl = wp_parse_url( $url );
		if ( ! $parsedUrl ) {
			return $url;
		}

		static $parsedSiteUrl = null;
		if ( ! $parsedSiteUrl ) {
			$parsedSiteUrl = wp_parse_url( site_url() );
		}

		if ( $parsedSiteUrl['host'] !== $parsedUrl['host'] ) {
			return $url;
		}

		return ! empty( $parsedUrl['path'] ) ? $parsedUrl['path'] : $url;
	}

	/**
	 * Formats a given URL as an absolute URL if it is relative.
	 *
	 * @since   4.0.0
	 * @version 4.8.3 Moved from WpUri trait to Url trait.
	 *
	 * @param  string $url The URL.
	 * @return string      The absolute URL.
	 */
	public function makeUrlAbsolute( $url ) {
		if ( 0 !== strpos( $url, 'http' ) && '/' !== $url ) {
			$url = $this->sanitizeDomain( $url );
			if ( $this->isDomainWithPaths( $url ) ) {
				$scheme = wp_parse_url( site_url(), PHP_URL_SCHEME );
				$url    = $scheme . '://' . $url;
			} elseif ( 0 === strpos( $url, '//' ) ) {
				$scheme = wp_parse_url( site_url(), PHP_URL_SCHEME );
				$url    = $scheme . ':' . $url;
			} else {
				$url = site_url( $url );
			}
		}

		return $url;
	}
}