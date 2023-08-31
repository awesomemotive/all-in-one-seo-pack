<?php
namespace AIOSEO\Plugin\Common\Traits\Helpers;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Contains methods related to multisite.
 *
 * @since 4.2.5
 */
trait WpMultisite {
	/**
	 * Returns the network ID.
	 *
	 * @since 4.2.5
	 *
	 * @return int The integer of the blog/site id.
	 */
	public function getNetworkId() {
		if ( is_multisite() ) {
			return get_network()->site_id;
		}

		return get_current_blog_id();
	}

	/**
	 * Get a site (with aliases) by it's blog ID.
	 *
	 * @since 4.2.5
	 *
	 * @param  int          $blogId The blog ID.
	 * @return \WP_Site|null         The site.
	 */
	public function getSiteByBlogId( $blogId ) {
		$sites = $this->getSites();
		foreach ( $sites['sites'] as $site ) {
			if ( $site->blog_id === $blogId ) {
				return $site;
			}
		}

		return null;
	}

	/**
	 * Get the current site.
	 *
	 * @since 4.2.5
	 *
	 * @return \WP_Site|object A WP_Site instance of the current site or an object representing the same.
	 */
	public function getSite() {
		if ( is_multisite() ) {
			return get_site();
		}

		return (object) [
			'domain' => $this->getSiteDomain(),
			'path'   => $this->getHomePath()
		];
	}

	/**
	 * Get all sites in the multisite network.
	 *
	 * @since 4.2.5
	 *
	 * @param  int|string  $limit      The number of sites to get or 'all'.
	 * @param  int         $offset     The offset to start at.
	 * @param  null|string $searchTerm The search term to look for.
	 * @param  null|string $filter     A filter to look up sites by.
	 * @param  null|string $orderBy    The column to order results by. Defaults to null.
	 * @param  string      $orderDir   The direction to order results by. Defaults to 'DESC'.
	 * @return array                   An array of sites.
	 */
	public function getSites( $limit = 'all', $offset = 0, $searchTerm = null, $filter = 'all', $orderBy = null, $orderDir = 'DESC' ) {
		$countSites = $this->countSites();
		$sites      = get_sites( [
			'network_id' => get_current_network_id(),
			'number'     => $countSites['public'],
			'public'     => 1
		] );

		$allSites = [];
		foreach ( $sites as $site ) {
			$clonedSite           = clone $site;
			$clonedSite->adminUrl = get_admin_url( $site->blog_id );
			$clonedSite->homeUrl  = get_home_url( $site->blog_id );

			if ( $this->includeSite( $clonedSite, $filter ) ) {
				$allSites[] = $clonedSite;
			}

			// We need to look up aliases for Mercator, this checks to see if it's even enabled.
			if ( ! class_exists( '\Mercator\Mapping' ) ) {
				continue;
			}

			$aliases = $this->getSiteAliases( $site );
			foreach ( $aliases as $alias ) {
				$aliasSite               = clone $clonedSite;
				$aliasSite->domain       = $alias['domain'];
				$aliasSite->path         = '/';
				$aliasSite->alias        = $alias;
				$aliasSite->parentDomain = $site->domain;
				$aliasSite->parentPath   = $site->path;

				if ( $this->includeSite( $aliasSite, $filter ) ) {
					$allSites[] = $aliasSite;
				}
			}
		}

		// If we have a search term, let's filter down these results.
		if ( ! empty( $searchTerm ) ) {
			foreach ( $allSites as $key => $site ) {
				$keep = false;
				if (
					false !== stripos( $site->domain, $searchTerm ) ||
					false !== stripos( $site->path, $searchTerm ) ||
					false !== stripos( $site->parentDomain, $searchTerm ) ||
					false !== stripos( $site->parentPath, $searchTerm )
				) {
					$keep = true;
				}

				if ( ! $keep ) {
					unset( $allSites[ $key ] );
				}
			}
		}

		// Ordering the sites.
		if ( ! empty( $orderBy ) ) {
			usort( $allSites, function( $site1, $site2 ) use ( $orderBy, $orderDir ) {
				if ( empty( $site1->{ $orderBy } ) ) {
					return 0;
				}

				return 'ASC' === strtoupper( $orderDir )
					? ( $site1->{ $orderBy } > $site2->{ $orderBy } ? 1 : 0 )
					: ( $site1->{ $orderBy } < $site2->{ $orderBy } ? 1 : 0 );
			} );
		}

		return [
			'total' => count( $allSites ),
			'limit' => $limit,
			'sites' => 'all' === $limit ? $allSites : array_slice( $allSites, $offset, $limit )
		];
	}

	/**
	 * Count the number of sites in the network. A clone of wp_count_sites. We use this because
	 * we don't yet support WordPress 5.3. Once we do, we can revert to wp_count_sites.
	 *
	 * @since 4.4.5
	 *
	 * @return array          An array of aliases.
	 */
	private function countSites() {
		$networkId = get_current_network_id();

		$counts = [];
		$args   = [
			'network_id'    => $networkId,
			'number'        => 1,
			'fields'        => 'ids',
			'no_found_rows' => false,
		];

		$q             = new \WP_Site_Query( $args );
		$counts['all'] = $q->found_sites;

		$_args    = $args;
		$statuses = [ 'public', 'archived', 'mature', 'spam', 'deleted' ];

		foreach ( $statuses as $status ) {
			$_args            = $args;
			$_args[ $status ] = 1;

			$q                 = new \WP_Site_Query( $_args );
			$counts[ $status ] = $q->found_sites;
		}

		return $counts;
	}

	/**
	 * Filter sites based on a passed in filter. Options include 'all', 'activated' or 'deactivated'.
	 *
	 * @since 4.2.5
	 *
	 * @param  Object $site   The site object.
	 * @param  string $filter The filter to use.
	 * @return bool           The site if allowed or null if not.
	 */
	private function includeSite( $site, $filter ) {
		if ( 'all' === $filter ) {
			return true;
		}

		$siteIsActive = aioseo()->networkLicense->isSiteActive( $site );
		if (
			( 'deactivated' === $filter && ! $siteIsActive ) ||
			( 'activated' === $filter && $siteIsActive )
		) {
			return true;
		}

		return false;
	}

	/**
	 * Get an array of aliases for a WP_Site.
	 *
	 * @since 4.2.5
	 *
	 * @param  \WP_Site $site The Site.
	 * @return array          An array of aliases.
	 */
	public function getSiteAliases( $site ) {
		// We need to look up aliases for Mercator, this checks to see if it's even enabled.
		if ( ! class_exists( '\Mercator\Mapping' ) ) {
			return [];
		}

		$aliases = \Mercator\Mapping::get_by_site( $site->blog_id );
		if ( empty( $aliases ) ) {
			return [];
		}

		$aliasData = [];
		foreach ( $aliases as $alias ) {
			$aliasData[] = [
				'alias_id' => $alias->get_id(),
				'domain'   => $alias->get_domain(),
				'active'   => $alias->is_active()
			];
		}

		return $aliasData;
	}

	/**
	 * Wrapper for switch_to_blog especially for non-multisite setups.
	 *
	 * @since 4.2.5
	 *
	 * @param  int  $blogId The blog ID to switch to.
	 * @return bool         Always returns true.
	 */
	public function switchToBlog( $blogId ) {
		if ( ! is_multisite() ) {
			return true;
		}

		return switch_to_blog( $blogId );
	}

	/**
	 * Wrapper for restore_current_blog especially for non-multisite setups.
	 *
	 * @since 4.2.5
	 *
	 * @return bool True on success, false if we're already on the current blog or not in a multisite environment.
	 */
	public function restoreCurrentBlog() {
		if ( ! is_multisite() ) {
			return false;
		}

		return restore_current_blog();
	}

	/**
	 * Checks if the current plugin is network activated.
	 *
	 * @since 4.2.8
	 *
	 * @param  string|null $plugin The plugin to check for network activation.
	 * @return bool                True if network activated, false if not.
	 */
	public function isPluginNetworkActivated( $plugin = null ) {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';

		if ( ! is_multisite() ) {
			return false;
		}

		$plugin = $plugin ? $plugin : plugin_basename( AIOSEO_FILE );

		// If the plugin is not network activated, then no it's not network licensed.
		if ( ! is_plugin_active_for_network( $plugin ) ) {
			return false;
		}

		return true;
	}
}