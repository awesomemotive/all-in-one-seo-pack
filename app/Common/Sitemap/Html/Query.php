<?php
namespace AIOSEO\Plugin\Common\Sitemap\Html;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles all queries for the HTML sitemap.
 *
 * @since 4.1.3
 */
class Query {
	/**
	 * Returns all eligible sitemap entries for a given post type.
	 *
	 * @since 4.1.3
	 *
	 * @param  string $postType   The post type.
	 * @param  array  $attributes The attributes.
	 * @return array              The post objects.
	 */
	public function posts( $postType, $attributes ) {
		$fields  = '`ID`, `post_title`,';
		$fields .= '`post_parent`, `post_date_gmt`, `post_modified_gmt`';

		$orderBy = '';
		switch ( $attributes['order_by'] ) {
			case 'last_updated':
				$orderBy = 'post_modified_gmt';
				break;
			case 'alphabetical':
				$orderBy = 'post_title';
				break;
			case 'id':
				$orderBy = 'ID';
				break;
			case 'publish_date':
			default:
				$orderBy = 'post_date_gmt';
				break;
		}

		switch ( strtolower( $attributes['order'] ) ) {
			case 'desc':
				$orderBy .= ' DESC';
				break;
			default:
				$orderBy .= ' ASC';
		}

		$query = aioseo()->core->db
			->start( 'posts' )
			->select( $fields )
			->where( 'post_status', 'publish' )
			->where( 'post_type', $postType );

		$excludedPosts = $this->getExcludedObjects( $attributes );
		if ( $excludedPosts ) {
			$query->whereRaw( "( `ID` NOT IN ( $excludedPosts ) )" );
		}

		$posts = $query->orderBy( $orderBy )
			->run()
			->result();

		foreach ( $posts as $post ) {
			$post->ID = (int) $post->ID;
		}

		return $posts;
	}

	/**
	 * Returns all eligble sitemap entries for a given taxonomy.
	 *
	 * @since 4.1.3
	 *
	 * @param  string $taxonomy   The taxonomy name.
	 * @param  array  $attributes The attributes.
	 * @return array              The term objects.
	 */
	public function terms( $taxonomy, $attributes = [] ) {
		$fields                 = 't.term_id, t.name, tt.parent';
		$termRelationshipsTable = aioseo()->core->db->db->prefix . 'term_relationships';
		$termTaxonomyTable      = aioseo()->core->db->db->prefix . 'term_taxonomy';

		$orderBy = '';
		switch ( $attributes['order_by'] ) {
			case 'alphabetical':
				$orderBy = 't.name';
				break;
			// We can only sort by date after getting the terms.
			case 'id':
			case 'publish_date':
			case 'last_updated':
			default:
				$orderBy = 't.term_id';
				break;
		}

		switch ( strtolower( $attributes['order'] ) ) {
			case 'desc':
				$orderBy .= ' DESC';
				break;
			default:
				$orderBy .= ' ASC';
		}

		$query = aioseo()->core->db
			->start( 'terms as t' )
			->select( $fields )
			->join( 'term_taxonomy as tt', 't.term_id = tt.term_id' )
			->whereRaw( "
			( `t`.`term_id` IN
				(
					SELECT `tt`.`term_id`
					FROM `$termTaxonomyTable` as tt
					WHERE `tt`.`taxonomy` = '$taxonomy'
					AND `tt`.`count` > 0
				)
			)" );

		$excludedTerms = $this->getExcludedObjects( $attributes, false );
		if ( $excludedTerms ) {
			$query->whereRaw("
				( `t`.`term_id` NOT IN
					(
						SELECT `tr`.`term_taxonomy_id`
						FROM `$termRelationshipsTable` as tr
						WHERE `tr`.`term_taxonomy_id` IN ( $excludedTerms )
					)
				)" );
		}

		$terms = $query->orderBy( $orderBy )
			->run()
			->result();

		foreach ( $terms as $term ) {
			$term->term_id  = (int) $term->term_id;
			$term->taxonomy = $taxonomy;
		}

		$shouldSort = false;
		if ( 'last_updated' === $attributes['order_by'] ) {
			$shouldSort = true;
			foreach ( $terms as $term ) {
				$term->timestamp = strtotime( aioseo()->sitemap->content->getTermLastModified( $term->term_id ) );
			}
		}

		if ( 'publish_date' === $attributes['order_by'] ) {
			$shouldSort = true;
			foreach ( $terms as $term ) {
				$term->timestamp = strtotime( $this->getTermPublishDate( $term->term_id ) );
			}
		}

		if ( $shouldSort ) {
			if ( 'asc' === strtolower( $attributes['order'] ) ) {
				usort( $terms, function( $term1, $term2 ) {
					return $term1->timestamp > $term2->timestamp ? 1 : 0;
				} );
			} else {
				usort( $terms, function( $term1, $term2 ) {
					return $term1->timestamp < $term2->timestamp ? 1 : 0;
				} );
			}
		}

		return $terms;
	}

	/**
	 * Returns a list of date archives that can be included.
	 *
	 * @since 4.1.3
	 *
	 * @return array The date archives.
	 */
	public function archives() {
		$result = aioseo()->core->db
			->start( 'posts', false, 'SELECT DISTINCT' )
			->select( 'YEAR(post_date) AS year, MONTH(post_date) AS month' )
			->where( 'post_type', 'post' )
			->where( 'post_status', 'publish' )
			->whereRaw( "post_password=''" )
			->orderBy( '`year` DESC, `month` DESC' )
			->run()
			->result();

		$dates = [];
		foreach ( $result as $date ) {
			$dates[ $date->year ][ $date->month ] = 1;
		}

		return $dates;
	}

	/**
	 * Returns the publish date for a given term.
	 * This is the publish date of the oldest post that is assigned to the term.
	 *
	 * @since 4.1.3
	 *
	 * @param  int $termId The term ID.
	 * @return int         The publish date timestamp.
	 */
	public function getTermPublishDate( $termId ) {
		$termRelationshipsTable = aioseo()->core->db->db->prefix . 'term_relationships';

		$post = aioseo()->core->db
			->start( 'posts as p' )
			->select( 'MIN(`p`.`post_date_gmt`) as publish_date' )
			->whereRaw( "
			( `p`.`ID` IN
				(
					SELECT `tr`.`object_id`
					FROM `$termRelationshipsTable` as tr
					WHERE `tr`.`term_taxonomy_id` = '$termId'
				)
			)" )
			->run()
			->result();

		return ! empty( $post[0]->publish_date ) ? strtotime( $post[0]->publish_date ) : 0;
	}

	/**
	 * Returns a comma-separated string of excluded object IDs.
	 *
	 * @since 4.1.3
	 *
	 * @param  array   $attributes The attributes.
	 * @param  boolean $posts      Whether the objects are posts.
	 * @return string              The excluded object IDs.
	 */
	private function getExcludedObjects( $attributes, $posts = true ) {
		$excludedObjects = $posts
			? aioseo()->sitemap->helpers->excludedPosts()
			: aioseo()->sitemap->helpers->excludedTerms();
		$key             = $posts ? 'excluded_posts' : 'excluded_terms';

		if ( ! empty( $attributes[ $key ] ) ) {
			$ids = explode( ',', $excludedObjects );

			$extraIds = [];
			if ( is_array( $attributes[ $key ] ) ) {
				$extraIds = $attributes[ $key ];
			}
			if ( is_string( $attributes[ $key ] ) ) {
				$extraIds = array_map( 'trim', explode( ',', $attributes[ $key ] ) );
			}

			$ids = array_filter( array_merge( $ids, $extraIds ), 'is_numeric' );

			$excludedObjects = esc_sql( implode( ', ', $ids ) );
		}

		return $excludedObjects;
	}
}