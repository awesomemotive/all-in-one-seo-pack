<?php
namespace AIOSEO\Plugin\Common\Sitemap;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Determines which indexes should appear in the sitemap root index.
 *
 * @since 4.0.0
 */
class Root {
	/**
	 * Returns the indexes for the sitemap root index.
	 *
	 * @since 4.0.0
	 *
	 * @return array The indexes.
	 */
	public function indexes() {
		$indexes = [];
		if ( 'general' !== aioseo()->sitemap->type ) {
			$addonIndexes = aioseo()->addons->doAddonFunction( 'root', 'indexes' );

			foreach ( $addonIndexes as $addonIndex ) {
				if ( $addonIndex ) {
					return $addonIndex;
				}
			}

			return $indexes;
		}

		$filename   = aioseo()->sitemap->filename;
		$postTypes  = aioseo()->sitemap->helpers->includedPostTypes();
		$taxonomies = aioseo()->sitemap->helpers->includedTaxonomies();

		$indexes = array_merge( $indexes, $this->getAdditionalIndexes() );

		if ( $postTypes ) {
			$postArchives = [];

			foreach ( $postTypes as $postType ) {
				$postIndexes = $this->buildIndexesPostType( $postType );
				$indexes     = array_merge( $indexes, $postIndexes );

				if (
					get_post_type_archive_link( $postType ) &&
					aioseo()->dynamicOptions->noConflict()->searchAppearance->archives->has( $postType ) &&
					(
						aioseo()->dynamicOptions->searchAppearance->archives->$postType->advanced->robotsMeta->default ||
						! aioseo()->dynamicOptions->searchAppearance->archives->$postType->advanced->robotsMeta->noindex
					)
				) {
					$lastModifiedPostTime = aioseo()->sitemap->helpers->lastModifiedPostTime( $postType );
					if ( $lastModifiedPostTime ) {
						$postArchives[ $postType ] = $lastModifiedPostTime;
					}
				}
			}

			if ( ! empty( $postArchives ) ) {
				usort( $postArchives, function( $date1, $date2 ) {
					return $date1 < $date2 ? 1 : 0;
				} );

				$indexes[] = [
					'loc'     => aioseo()->helpers->localizedUrl( "/post-archive-$filename.xml" ),
					'lastmod' => $postArchives[0],
					'count'   => count( $postArchives )
				];
			}
		}

		if ( $taxonomies ) {
			foreach ( $taxonomies as $taxonomy ) {
				$indexes = array_merge( $indexes, $this->buildIndexesTaxonomy( $taxonomy ) );
			}
		}

		$postsTable = aioseo()->core->db->db->posts;
		if (
			aioseo()->sitemap->helpers->lastModifiedPost() &&
			aioseo()->options->sitemap->general->author &&
			aioseo()->options->searchAppearance->archives->author->show &&
			(
				aioseo()->options->searchAppearance->archives->author->advanced->robotsMeta->default ||
				! aioseo()->options->searchAppearance->archives->author->advanced->robotsMeta->noindex
			) &&
			(
				aioseo()->options->searchAppearance->advanced->globalRobotsMeta->default ||
				! aioseo()->options->searchAppearance->advanced->globalRobotsMeta->noindex
			)
		) {
			$usersTable        = aioseo()->core->db->db->users; // We get the table name from WPDB since multisites share the same table.
			$authorPostTypes   = aioseo()->sitemap->helpers->getAuthorPostTypes();
			$implodedPostTypes = aioseo()->helpers->implodeWhereIn( $authorPostTypes, true );
			$result            = aioseo()->core->db->execute(
				"SELECT count(*) as amountOfAuthors FROM
				(
					SELECT u.ID FROM {$usersTable} as u
					INNER JOIN {$postsTable} as p ON u.ID = p.post_author
					WHERE p.post_status = 'publish' AND p.post_type IN ( {$implodedPostTypes} )
					GROUP BY u.ID
				) as x",
				true
			)->result();

			if ( ! empty( $result[0]->amountOfAuthors ) ) {
				$indexes = array_merge( $indexes, $this->buildAuthorIndexes( (int) $result[0]->amountOfAuthors ) );
			}
		}

		if (
			aioseo()->sitemap->helpers->lastModifiedPost() &&
			aioseo()->options->sitemap->general->date &&
			aioseo()->options->searchAppearance->archives->date->show &&
			(
				aioseo()->options->searchAppearance->archives->date->advanced->robotsMeta->default ||
				! aioseo()->options->searchAppearance->archives->date->advanced->robotsMeta->noindex
			) &&
			(
				aioseo()->options->searchAppearance->advanced->globalRobotsMeta->default ||
				! aioseo()->options->searchAppearance->advanced->globalRobotsMeta->noindex
			)
		) {
			$result = aioseo()->core->db->execute(
				"SELECT (COUNT(DISTINCT YEAR(post_date)) + COUNT(*)) as amountOfUrls FROM (
					SELECT post_date
					FROM {$postsTable}
					WHERE post_type = 'post' AND post_status = 'publish'
					GROUP BY
						YEAR(post_date),
						MONTH(post_date)
					LIMIT 50000
				) as dates",
				true
			)->result();

			$indexes[] = $this->buildIndex( 'date', $result[0]->amountOfUrls );
		}

		if (
			aioseo()->helpers->isWooCommerceActive() &&
			in_array( 'product_attributes', aioseo()->sitemap->helpers->includedTaxonomies(), true )
		) {
			$productAttributes = aioseo()->sitemap->content->productAttributes( true );

			if ( ! empty( $productAttributes ) ) {
				$indexes[] = $this->buildIndex( 'product_attributes', $productAttributes );
			}
		}

		if ( isset( aioseo()->standalone->buddyPress->sitemap ) ) {
			$indexes = array_merge( $indexes, aioseo()->standalone->buddyPress->sitemap->indexes() );
		}

		return apply_filters( 'aioseo_sitemap_indexes', array_filter( $indexes ) );
	}

	/**
	 * Returns the additional page indexes.
	 *
	 * @since   4.2.1
	 * @version 5.0.3 Homepage entry now follows {@see Helpers::shouldIncludeHomepage()}.
	 *
	 * @return array
	 */
	private function getAdditionalIndexes() {
		$additionalPages = [];
		if ( aioseo()->options->sitemap->general->additionalPages->enable ) {
			$additionalPages = array_map( 'json_decode', aioseo()->options->sitemap->general->additionalPages->pages );
			$additionalPages = array_filter( $additionalPages, function( $additionalPage ) {
				return ! empty( $additionalPage->url );
			} );
		}

		$entries = [];
		foreach ( $additionalPages as $additionalPage ) {
			$entries[] = [
				'loc'        => $additionalPage->url,
				'lastmod'    => aioseo()->sitemap->helpers->lastModifiedAdditionalPage( $additionalPage ),
				'changefreq' => $additionalPage->frequency->value,
				'priority'   => $additionalPage->priority->value,
				'isTimezone' => true
			];
		}

		if ( aioseo()->options->sitemap->general->additionalPages->enable ) {
			$entries = apply_filters( 'aioseo_sitemap_additional_pages', $entries );
		}

		$shouldIncludeHomepage = aioseo()->sitemap->helpers->shouldIncludeHomepage();
		if ( ! $shouldIncludeHomepage && ! count( $entries ) ) {
			return [];
		}

		$indexes = $this->buildAdditionalIndexes( $entries, $shouldIncludeHomepage );

		return $indexes;
	}

	/**
	 * Builds a given index.
	 *
	 * @since 4.0.0
	 *
	 * @param  string  $indexName    The index name.
	 * @param  integer $amountOfUrls The amount of URLs in the index.
	 * @return array                 The index.
	 */
	private function buildIndex( $indexName, $amountOfUrls ) {
		$filename = aioseo()->sitemap->filename;

		return [
			'loc'     => aioseo()->helpers->localizedUrl( "/$indexName-$filename.xml" ),
			'lastmod' => aioseo()->sitemap->helpers->lastModifiedPostTime(),
			'count'   => $amountOfUrls
		];
	}

	/**
	 * Builds the additional pages index.
	 *
	 * @since 4.0.0
	 *
	 * @param  array $entries               The additional pages.
	 * @param  bool  $shouldIncludeHomepage Whether or not the homepage should be included.
	 * @return array                        The indexes.
	 */
	private function buildAdditionalIndexes( $entries, $shouldIncludeHomepage ) {
		if ( $shouldIncludeHomepage ) {
			$entries[] = [
				'loc'     => home_url(),
				'lastmod' => aioseo()->sitemap->helpers->lastModifiedPostTime()
			];
		}

		if ( empty( $entries ) ) {
			return [];
		}

		$filename  = aioseo()->sitemap->filename;
		$chunks    = aioseo()->sitemap->helpers->chunkEntries( $entries );

		$indexes = [];
		for ( $i = 0; $i < count( $chunks ); $i++ ) {
			$chunk       = array_values( $chunks[ $i ] );
			$indexNumber = 1 < count( $chunks ) ? $i + 1 : '';

			$index = [
				'loc'     => aioseo()->helpers->localizedUrl( "/addl-$filename$indexNumber.xml" ),
				'lastmod' => ! empty( $chunk[0]['lastmod'] ) ? aioseo()->helpers->dateTimeToIso8601( $chunk[0]['lastmod'] ) : '',
				'count'   => count( $chunks[ $i ] )
			];

			$indexes[] = $index;
		}

		return $indexes;
	}

	/**
	 * Builds the author archive indexes.
	 *
	 * @since   4.3.1
	 * @version 4.9.9 Use getAuthorPostTypes() instead of includedPostTypes() for consistent lastmod with author-sitemap.xml.
	 *
	 * @param  integer $amountOfAuthors The amount of author archives.
	 * @return array                    The indexes.
	 */
	private function buildAuthorIndexes( $amountOfAuthors ) {
		if ( ! $amountOfAuthors ) {
			return [];
		}

		$postTypes = aioseo()->sitemap->helpers->getAuthorPostTypes();
		$filename  = aioseo()->sitemap->filename;
		$chunks    = $amountOfAuthors / aioseo()->sitemap->linksPerIndex;
		if ( $chunks < 1 ) {
			$chunks = 1;
		}

		$indexes = [];
		for ( $i = 0; $i < $chunks; $i++ ) {
			$indexNumber = 1 < $chunks ? $i + 1 : '';

			$usersTableName = aioseo()->core->db->db->users; // We get the table name from WPDB since multisites share the same table.
			$lastModified   = aioseo()->core->db->start( "$usersTableName as u", true )
				->select( 'MAX(p.post_modified_gmt) as lastModified' )
				->join( 'posts as p', 'u.ID = p.post_author' )
				->where( 'p.post_status', 'publish' )
				->whereIn( 'p.post_type', $postTypes )
				->groupBy( 'u.ID' )
				->orderBy( 'lastModified DESC' )
				->limit( aioseo()->sitemap->linksPerIndex, $i * aioseo()->sitemap->linksPerIndex )
				->run()
				->result();

			$lastModified = ! empty( $lastModified[0]->lastModified ) ? aioseo()->helpers->dateTimeToIso8601( $lastModified[0]->lastModified ) : '';

			$index = [
				'loc'     => aioseo()->helpers->localizedUrl( "/author-$filename$indexNumber.xml" ),
				'lastmod' => $lastModified,
				'count'   => $i + 1 === $chunks ? $amountOfAuthors % aioseo()->sitemap->linksPerIndex : aioseo()->sitemap->linksPerIndex
			];

			$indexes[] = $index;
		}

		return $indexes;
	}

	/**
	 * Builds indexes for all eligible posts of a given post type.
	 *
	 * @since 4.0.0
	 *
	 * @param  string $postType The post type.
	 * @return array            The indexes.
	 */
	private function buildIndexesPostType( $postType ) {
		if ( 'attachment' === $postType && 'disabled' !== aioseo()->dynamicOptions->searchAppearance->postTypes->attachment->redirectAttachmentUrls ) {
			return [];
		}

		$pages = $this->postTypePages( $postType );
		if ( $pages ) {
			$indexes   = [];
			$filename  = aioseo()->sitemap->filename;
			$pageCount = count( $pages );
			foreach ( $pages as $i => $page ) {
				$indexNumber = 0 !== $i && 1 < $pageCount ? $i + 1 : '';

				$indexes[] = [
					'loc'     => aioseo()->helpers->localizedUrl( "/$postType-$filename$indexNumber.xml" ),
					'lastmod' => aioseo()->helpers->dateTimeToIso8601( $page->lastmod ),
					'count'   => (int) $page->total
				];
			}

			return $indexes;
		}

		$posts       = [];
		$addonsPosts = aioseo()->addons->doAddonFunction( 'root', 'buildIndexesPostType', [ $postType ] );
		foreach ( $addonsPosts as $addonPosts ) {
			if ( $addonPosts ) {
				$posts = $addonPosts;
				break;
			}
		}

		if ( ! $posts ) {
			return [];
		}

		return $this->buildIndexes( $postType, $posts );
	}

	/**
	 * Returns the newest lastmod and the post count of each sub-sitemap page of a post type.
	 *
	 * @since 5.0.6
	 *
	 * @param  string   $postType The post type.
	 * @return object[]           One row per page, in page order.
	 */
	private function postTypePages( $postType ) {
		$query = aioseo()->sitemap->query->postsQuery( $postType, [ 'root' => true ] );
		if ( ! $query ) {
			return [];
		}

		// Number the rows of the exact query the sub-sitemaps page through, so the page boundaries can't drift
		// from it. The LIMIT keeps the subquery materialized; without it, MariaDB merges it and drops its ORDER BY.
		$postsQuery    = $query->limit( PHP_INT_MAX )->preparedQuery();
		$linksPerIndex = (int) aioseo()->sitemap->linksPerIndex;

		// GREATEST() mirrors Content::getLastModified(), which the sub-sitemap entries use.
		return aioseo()->core->db->execute(
			"SELECT MAX(lastmod) AS lastmod, COUNT(*) AS total
			FROM (
				SELECT @row := @row + 1 AS rownum, GREATEST(x.post_modified_gmt, x.post_date_gmt) AS lastmod
				FROM ( {$postsQuery} ) AS x
				CROSS JOIN (SELECT @row := 0) AS vars
			) AS y
			GROUP BY CEIL(rownum / {$linksPerIndex})
			ORDER BY CEIL(rownum / {$linksPerIndex})",
			true
		)->result();
	}

	/**
	 * Builds indexes for all eligible terms of a given taxonomy.
	 *
	 * @since 4.0.0
	 *
	 * @param  string $taxonomy The taxonomy.
	 * @return array            The indexes.
	 */
	private function buildIndexesTaxonomy( $taxonomy ) {
		$terms = aioseo()->sitemap->content->terms( $taxonomy, [ 'root' => true ] );

		if ( ! $terms ) {
			$addonsTerms = aioseo()->addons->doAddonFunction( 'root', 'buildIndexesTaxonomy', [ $taxonomy ] );

			foreach ( $addonsTerms as $addonTerms ) {
				if ( $addonTerms ) {
					$terms = $addonTerms;
					break;
				}
			}
		}

		if ( ! $terms ) {
			return [];
		}

		return $this->buildIndexes( $taxonomy, $terms );
	}

	/**
	 * Builds indexes for a given type.
	 *
	 * Acts as a helper function for buildIndexesPostTypes() and buildIndexesTaxonomies().
	 *
	 * @since   4.0.0
	 * @version 5.0.2 Match term IDs through term_taxonomy for the lastmod lookup.
	 *
	 * @param  string $name    The name of the object parent.
	 * @param  array  $entries The sitemap entries.
	 * @return array           The indexes.
	 */
	public function buildIndexes( $name, $entries ) {
		$filename = aioseo()->sitemap->filename;
		$chunks   = aioseo()->sitemap->helpers->chunkEntries( $entries );
		$indexes  = [];
		for ( $i = 0; $i < count( $chunks ); $i++ ) {
			$chunk       = array_values( $chunks[ $i ] );
			$indexNumber = 0 !== $i && 1 < count( $chunks ) ? $i + 1 : '';

			$index = [
				'loc'   => aioseo()->helpers->localizedUrl( "/$name-$filename$indexNumber.xml" ),
				'count' => count( $chunks[ $i ] )
			];

			if ( isset( $entries[0]->ID ) ) {
				$ids = array_map( function( $post ) {
					return $post->ID;
				}, $chunk );

				$lastModified = null;
				if ( ! apply_filters( 'aioseo_sitemap_lastmod_disable', false ) ) {
					$lastModified = aioseo()->core->db
						->start( aioseo()->core->db->db->posts . ' as p', true )
						->select( 'MAX(`p`.`post_modified_gmt`) as last_modified' )
						->whereIn( 'p.ID', $ids )
						->run()
						->result();
				}

				if ( ! empty( $lastModified[0]->last_modified ) ) {
					$index['lastmod'] = aioseo()->helpers->dateTimeToIso8601( $lastModified[0]->last_modified );
				}
				$indexes[] = $index;
				continue;
			}

			$termIds = [];
			foreach ( $chunk as $term ) {
				$termIds[] = $term->term_id;
			}
			$termIds = implode( "', '", $termIds );

			$termRelationshipsTable = aioseo()->core->db->db->prefix . 'term_relationships';
			$termTaxonomyTable      = aioseo()->core->db->db->prefix . 'term_taxonomy';

			$lastModified = null;
			if ( ! apply_filters( 'aioseo_sitemap_lastmod_disable', false ) ) {
				$lastModified = aioseo()->core->db
					->start( aioseo()->core->db->db->posts . ' as p', true )
					->select( 'MAX(`p`.`post_modified_gmt`) as last_modified' )
					->where( 'p.post_status', 'publish' )
					->whereRaw( "
					( `p`.`ID` IN
						(
							SELECT CONVERT(`tr`.`object_id`, unsigned)
							FROM `$termRelationshipsTable` as tr
							JOIN `$termTaxonomyTable` as tt ON `tr`.`term_taxonomy_id` = `tt`.`term_taxonomy_id`
							WHERE `tt`.`term_id` IN ( '$termIds' )
						)
					)" )
					->run()
					->result();
			}

			if ( ! empty( $lastModified[0]->last_modified ) ) {
				$index['lastmod'] = aioseo()->helpers->dateTimeToIso8601( $lastModified[0]->last_modified );
			}
			$indexes[] = $index;
		}

		return $indexes;
	}
}