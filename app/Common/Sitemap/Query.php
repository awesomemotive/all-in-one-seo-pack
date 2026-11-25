<?php
namespace AIOSEO\Plugin\Common\Sitemap;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles all complex queries for the sitemap.
 *
 * @since 4.0.0
 */
class Query {
	/**
	 * Returns the eligible sitemap posts of the given post type(s), or their count.
	 *
	 * @since 4.0.0
	 *
	 * @param  string|string[] $postTypes      The post type, or an array of post types.
	 * @param  array           $additionalArgs Overrides for the query defaults (e.g. `orderBy`, `maxAge`), plus the
	 *                                         `root` (index fields, no paging) and `count` flags.
	 * @return object[]|int                    The posts, or their count when `count` is set.
	 */
	public function posts( $postTypes, $additionalArgs = [] ) {
		$query = $this->postsQuery( $postTypes, $additionalArgs );
		if ( ! $query ) {
			return [];
		}

		// Return the total if we are just counting the posts.
		if ( ! empty( $additionalArgs['count'] ) ) {
			return (int) $query->run( true, 'var' )
				->result();
		}

		$posts = $query->run()
			->result();

		// Convert ID from string to int.
		foreach ( $posts as $post ) {
			$post->ID = (int) $post->ID;
		}

		return $this->filterPosts( $posts );
	}

	/**
	 * Returns the unexecuted query behind {@see posts()}.
	 *
	 * @since 5.0.6
	 *
	 * @param  string|string[]                           $postTypes      The post type, or an array of post types.
	 * @param  array                                     $additionalArgs Query overrides, as in {@see posts()}.
	 * @return \AIOSEO\Plugin\Common\Utils\Database|null                 The query, or null if there is nothing to query.
	 */
	public function postsQuery( $postTypes, $additionalArgs = [] ) {
		$includedPostTypes = $postTypes;
		$postTypesArray    = ! is_array( $postTypes ) ? [ $postTypes ] : $postTypes;
		if ( is_array( $postTypes ) ) {
			$includedPostTypes = implode( "', '", $postTypes );
		}

		if (
			empty( $includedPostTypes ) ||
			( 'attachment' === $includedPostTypes && 'disabled' !== aioseo()->dynamicOptions->searchAppearance->postTypes->attachment->redirectAttachmentUrls )
		) {
			return null;
		}

		// Set defaults.
		$maxAge = '';
		$fields = implode( ', ', [
			'p.ID',
			'p.post_excerpt',
			'p.post_type',
			'p.post_password',
			'p.post_parent',
			'p.post_date_gmt',
			'p.post_modified_gmt',
			'ap.priority',
			'ap.frequency'
		] );

		if ( in_array( aioseo()->sitemap->type, [ 'html', 'rss', 'llms' ], true ) ) {
			$fields .= ', p.post_title';
		}

		if ( 'general' !== aioseo()->sitemap->type || ! aioseo()->sitemap->helpers->excludeImages() ) {
			$fields .= ', ap.images';
		}

		// Order by highest priority first (highest priority at the top),
		// then by post modified date (most recently updated at the top).
		// Default-priority posts store NULL, so substitute the post type's resolved
		// default priority; otherwise NULL sorts last and pushes them out of order.
		$priorityColumn = is_array( $postTypes ) ? 'ap.priority' : $this->priorityOrderColumn( $includedPostTypes );

		// The ID breaks ties so that every page of a sub-sitemap, and the index built from it, sees the same order.
		$orderBy = $priorityColumn . ' DESC, p.post_modified_gmt DESC, p.ID DESC';

		// For llms sitemap type, prioritize posts with pillar_content = 1
		if ( 'llms' === aioseo()->sitemap->type ) {
			$orderBy = 'ap.pillar_content DESC, ' . $orderBy;
		}

		// Override defaults if passed as additional arg.
		foreach ( $additionalArgs as $name => $value ) {
			// Attachments need to be fetched with all their fields because we need to get their post parent further down the line.
			$$name = esc_sql( $value );
			if ( 'root' === $name && $value && 'attachment' !== $includedPostTypes ) {
				$fields = 'p.ID, p.post_type, p.post_date_gmt, p.post_modified_gmt';
			}
			if ( 'count' === $name && $value ) {
				$fields = 'count(p.ID) as total';
			}
		}

		$query = aioseo()->core->db
			->start( aioseo()->core->db->db->posts . ' as p', true )
			->select( $fields )
			->leftJoin( 'aioseo_posts as ap', 'ap.post_id = p.ID' )
			->where( 'p.post_status', 'attachment' === $includedPostTypes ? 'inherit' : 'publish' )
			->where( 'p.post_password', '' )
			->whereIn( 'p.post_type', $postTypesArray );

		// The front page is only exempt from the robots filter while it is in effect. WordPress keeps
		// page_on_front after the Customizer switches the homepage back to the latest posts, so a raw
		// read exempts a plain page that merely used to be the front page.
		$homePageId = (int) aioseo()->helpers->getHomePageId();

		// Note the effective check: a post type that only inherits the site-wide noindex still needs
		// the restrictive branch, or one explicitly indexed post pulls the whole type's posts back in.
		if ( ! is_array( $postTypes ) ) {
			if ( ! aioseo()->sitemap->helpers->isPostTypeEffectivelyNoindexed( $includedPostTypes ) ) {
				$query->whereRaw( "( `ap`.`robots_noindex` IS NULL OR `ap`.`robots_default` = 1 OR `ap`.`robots_noindex` = 0 OR post_id = $homePageId )" );
			} else {
				$query->whereRaw( "( `ap`.`robots_default` = 0 AND `ap`.`robots_noindex` = 0 OR post_id = $homePageId )" );
			}
		} else {
			$robotsMetaSql = [];
			foreach ( $postTypes as $postType ) {
				if ( ! aioseo()->sitemap->helpers->isPostTypeEffectivelyNoindexed( $postType ) ) {
					$robotsMetaSql[] = "( `p`.`post_type` = '$postType' AND ( `ap`.`robots_noindex` IS NULL OR `ap`.`robots_default` = 1 OR `ap`.`robots_noindex` = 0 OR post_id = $homePageId ) )";
				} else {
					$robotsMetaSql[] = "( `p`.`post_type` = '$postType' AND ( `ap`.`robots_default` = 0 AND `ap`.`robots_noindex` = 0 OR post_id = $homePageId ) )";
				}
			}
			$query->whereRaw( '( ' . implode( ' OR ', $robotsMetaSql ) . ' )' );
		}

		$excludedPosts = aioseo()->sitemap->helpers->excludedPosts();

		if ( $excludedPosts ) {
			$query->whereRaw( "( `p`.`ID` NOT IN ( $excludedPosts ) OR post_id = $homePageId )" );
		}

		// Exclude posts assigned to excluded terms.
		$excludedTerms = aioseo()->sitemap->helpers->excludedTerms();
		if ( $excludedTerms ) {
			$termRelationshipsTable = aioseo()->core->db->db->prefix . 'term_relationships';
			$termTaxonomyTable      = aioseo()->core->db->db->prefix . 'term_taxonomy';
			$query->whereRaw("
				( `p`.`ID` NOT IN
					(
						SELECT `tr`.`object_id`
						FROM `$termRelationshipsTable` as tr
						INNER JOIN `$termTaxonomyTable` as tt ON `tt`.`term_taxonomy_id` = `tr`.`term_taxonomy_id`
						WHERE `tt`.`term_id` IN ( $excludedTerms )
					)
				)" );
		}

		if ( $maxAge ) {
			$query->where( 'p.post_date_gmt >=', $maxAge );
		}

		if (
			'rss' === aioseo()->sitemap->type ||
			(
				aioseo()->sitemap->indexes &&
				empty( $additionalArgs['root'] ) &&
				empty( $additionalArgs['count'] )
			)
		) {
			$query->limit( aioseo()->sitemap->linksPerIndex, aioseo()->sitemap->offset );
		}

		$isStaticHomepage = 'page' === get_option( 'show_on_front' );
		if ( $isStaticHomepage ) {
			$excludedPostIds = array_map( 'intval', explode( ',', $excludedPosts ) );
			$blogPageId      = (int) get_option( 'page_for_posts' );

			if ( in_array( 'page', $postTypesArray, true ) ) {
				// Exclude the blog page from the pages post type.
				if ( $blogPageId ) {
					$query->where( 'p.ID !=', $blogPageId );
				}

				// Custom order by statement to always move the home page to the top.
				if ( $homePageId ) {
					$orderBy = "case when `p`.`ID` = $homePageId then 0 else 1 end, $orderBy";
				}
			}

			// Include the blog page in the posts post type unless manually excluded.
			if (
				$blogPageId &&
				! in_array( $blogPageId, $excludedPostIds, true ) &&
				in_array( 'post', $postTypesArray, true )
			) {
				// We are using a database class hack to get in an OR clause to
				// bypass all the other WHERE statements and just include the
				// blog page ID manually.
				$query->whereRaw( "1=1 OR `p`.`ID` = $blogPageId" );

				// Custom order by statement to always move the blog posts page to the top.
				$orderBy = "case when `p`.`ID` = $blogPageId then 0 else 1 end, $orderBy";
			}
		}

		$query->orderByRaw( $orderBy );

		return $this->filterPostQuery( $query, $postTypes );
	}

	/**
	 * Returns the SQL expression for ordering posts by priority, substituting the post
	 * type's resolved default priority for NULL so default-priority posts sort correctly.
	 *
	 * @since 5.0.2
	 *
	 * @param  string $postType The post type.
	 * @return string           The COALESCE expression wrapping `ap.priority`.
	 */
	public function priorityOrderColumn( $postType ) {
		$defaultPriority = (float) aioseo()->sitemap->priority->priority( 'postTypes', false, $postType );

		// The column is a single-precision FLOAT, so a stored 0.7 widens to 0.699999988...
		// and would sort below an exact decimal substitute instead of tying with it. Casting
		// the column to the same exact type as the substitute keeps equal priorities tied.
		// %F is locale-independent (always a dot decimal separator).
		return sprintf( 'COALESCE( CAST( ap.priority AS DECIMAL(10,6) ), %F )', $defaultPriority );
	}

	/**
	 * Filters the post query.
	 *
	 * @since 4.1.4
	 *
	 * @param  \AIOSEO\Plugin\Common\Utils\Database $query    The query.
	 * @param  string                               $postType The post type.
	 * @return \AIOSEO\Plugin\Common\Utils\Database           The filtered query.
	 */
	private function filterPostQuery( $query, $postType ) {
		switch ( $postType ) {
			case 'product':
				return $this->excludeHiddenProducts( $query );
			default:
				break;
		}

		return $query;
	}

	/**
	 * Adds a condition to the query to exclude hidden WooCommerce products.
	 *
	 * @since 4.1.4
	 *
	 * @param  \AIOSEO\Plugin\Common\Utils\Database $query The query.
	 * @return \AIOSEO\Plugin\Common\Utils\Database        The filtered query.
	 */
	private function excludeHiddenProducts( $query ) {
		if (
			! aioseo()->helpers->isWooCommerceActive() ||
			! apply_filters( 'aioseo_sitemap_woocommerce_exclude_hidden_products', true )
		) {
			return $query;
		}

		static $hiddenProductIds = null;
		if ( null === $hiddenProductIds ) {
			$termRelationshipsTable = aioseo()->core->db->db->prefix . 'term_relationships';
			$termTaxonomyTable      = aioseo()->core->db->db->prefix . 'term_taxonomy';
			$termsTable             = aioseo()->core->db->db->prefix . 'terms';

			// Exclude products when excluded from both catalog and search.
			$hiddenProducts = aioseo()->core->db->execute(
				"SELECT tr.object_id
				FROM {$termRelationshipsTable} AS tr
				JOIN {$termTaxonomyTable} AS tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
				JOIN {$termsTable} AS t ON tt.term_id = t.term_id
				WHERE t.name IN ('exclude-from-catalog', 'exclude-from-search')
				GROUP BY tr.object_id
				HAVING COUNT(DISTINCT t.name) = 2",
				true
			)->result();

			$hiddenProductIds = [];
			foreach ( $hiddenProducts as $hiddenProduct ) {
				$hiddenProductIds[] = (int) $hiddenProduct->object_id;
			}
		}

		if ( ! empty( $hiddenProductIds ) ) {
			$query->whereNotIn( 'p.ID', $hiddenProductIds );
		}

		return $query;
	}

	/**
	 * Filters the queried posts.
	 *
	 * @since 4.0.0
	 *
	 * @param  array $posts          The posts.
	 * @return array $remainingPosts The remaining posts.
	 */
	public function filterPosts( $posts ) {
		$remainingPosts = [];
		foreach ( $posts as $post ) {
			switch ( $post->post_type ) {
				case 'attachment':
					if ( ! $this->isInvalidAttachment( $post ) ) {
						$remainingPosts[] = $post;
					}
					break;
				default:
					$remainingPosts[] = $post;
					break;
			}
		}

		return $remainingPosts;
	}

	/**
	 * Excludes attachments if their post parent isn't published or parent post type isn't registered anymore.
	 *
	 * @since 4.0.0
	 *
	 * @param  Object  $post The post.
	 * @return boolean       Whether the attachment is invalid.
	 */
	private function isInvalidAttachment( $post ) {
		if ( empty( $post->post_parent ) ) {
			return false;
		}

		$parent = get_post( $post->post_parent );
		if ( ! is_object( $parent ) ) {
			return false;
		}

		if (
			'publish' !== $parent->post_status ||
			! in_array( $parent->post_type, get_post_types(), true ) ||
			$parent->post_password
		) {
			return true;
		}

		return false;
	}

	/**
	 * Returns all eligible sitemap entries for a given taxonomy.
	 *
	 * @since   4.0.0
	 * @version 5.0.2 Compare excluded term IDs to term_id directly.
	 *
	 * @param  string           $taxonomy       The taxonomy.
	 * @param  array            $additionalArgs Any additional arguments for the term query.
	 * @return array[object|int]                The term objects or the term count.
	 */
	public function terms( $taxonomy, $additionalArgs = [] ) {
		// Set defaults.
		$fields  = 't.term_id';
		$offset  = aioseo()->sitemap->offset;

		// Include term name for llms sitemap type
		if ( 'llms' === aioseo()->sitemap->type ) {
			$fields .= ', t.name';
		}

		// Override defaults if passed as additional arg.
		foreach ( $additionalArgs as $name => $value ) {
			$$name = esc_sql( $value );
			if ( 'root' === $name && $value ) {
				$fields = 't.term_id, tt.count';
			}
			if ( 'count' === $name && $value ) {
				$fields = 'count(t.term_id) as total';
			}
		}

		$termTaxonomyTable = aioseo()->core->db->db->prefix . 'term_taxonomy';

		// Include all terms that have assigned posts or whose children have assigned posts.
		$query = aioseo()->core->db
			->start( aioseo()->core->db->db->terms . ' as t', true )
			->select( $fields )
			->leftJoin( 'term_taxonomy as tt', '`tt`.`term_id` = `t`.`term_id`' )
			->where( 'tt.taxonomy', $taxonomy )
			->whereRaw( "
				(
					`tt`.`count` > 0 OR
					EXISTS (
						SELECT 1
						FROM `$termTaxonomyTable` as tt2
						WHERE `tt2`.`parent` = `tt`.`term_id` 
						AND `tt2`.`count` > 0
					)
				)
			" );

		$excludedTerms = aioseo()->sitemap->helpers->excludedTerms();
		if ( $excludedTerms ) {
			$query->whereRaw( "( `t`.`term_id` NOT IN ( $excludedTerms ) )" );
		}

		if (
			aioseo()->sitemap->indexes &&
			empty( $additionalArgs['root'] ) &&
			empty( $additionalArgs['count'] )
		) {
			$query->limit( aioseo()->sitemap->linksPerIndex, $offset );
		}

		// Return the total if we are just counting the terms.
		if ( ! empty( $additionalArgs['count'] ) ) {
			return (int) $query->run( true, 'var' )
				->result();
		}

		$terms = $query->orderBy( 't.term_id ASC' )
			->run()
			->result();

		foreach ( $terms as $term ) {
			// Convert ID from string to int.
			$term->term_id = (int) $term->term_id;
			// Add taxonomy name to object manually instead of querying it to prevent redundant join.
			$term->taxonomy = $taxonomy;
		}

		return $terms;
	}

	/**
	 * Wipes all data and forces the plugin to rescan the site for images.
	 *
	 * @since 4.0.13
	 *
	 * @return void
	 */
	public function resetImages() {
		aioseo()->core->db
			->update( 'aioseo_posts' )
			->set(
				[
					'images'          => null,
					'image_scan_date' => null
				]
			)
			->run();
	}
}