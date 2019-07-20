<?php
/**
 * Schema Graph Article Class
 *
 * Acts as the article class for Schema Article.
 *
 * @package All_in_One_SEO_Pack
 */

/**
 * Class AIOSEOP_Graph_Article
 *
 * @since 3.2
 *
 * @see AIOSEOP_Graph_Creativework
 * @see Schema Article
 * @link https://schema.org/Article
 */
class AIOSEOP_Graph_Article extends AIOSEOP_Graph_CreativeWork {

	/**
	 * Get Graph Slug.
	 *
	 * @since 3.2
	 *
	 * @return string
	 */
	protected function get_slug() {
		return 'Article';
	}

	/**
	 * Get Graph Name.
	 *
	 * Intended for frontend use when displaying which schema graphs are available.
	 *
	 * @since 3.2
	 *
	 * @return string
	 */
	protected function get_name() {
		return 'Article';
	}

	/**
	 * Prepare data.
	 *
	 * @since 3.2
	 *
	 * @return array
	 */
	protected function prepare() {
		global $post;
		global $aioseop_options;

		$comment_count   = get_comment_count( $post->ID );
		$post_url        = wp_get_canonical_url( $post );
		$post_taxonomies = get_post_taxonomies( $post );
		$post_terms      = array();
		foreach ( $post_taxonomies as $taxonomy_slug ) {
			$post_taxonomy_terms = get_the_terms( $post, $taxonomy_slug );
			if ( is_array( $post_taxonomy_terms ) ) {
				$post_terms = array_merge( $post_terms, wp_list_pluck( $post_taxonomy_terms, 'name' ) );
			}
		}

		$rtn_data = array(
			'@type'            => $this->slug,
			'@id'              => $post_url . '#' . strtolower( $this->slug ),
			'isPartOf'         => array( '@id' => $post_url . '#webpage' ),
			'author'           => $this->prepare_author(),
			'headline'         => get_the_title(),
			'datePublished'    => mysql2date( DATE_W3C, $post->post_date_gmt, false ),
			'dateModified'     => mysql2date( DATE_W3C, $post->post_modified_gmt, false ),
			'commentCount'     => $comment_count['approved'],
			'mainEntityOfPage' => array( '@id' => $post_url . '#webpage' ),
			'publisher'        => array( '@id' => home_url() . '/#' . $aioseop_options['aiosp_site_represents'] ),
			'articleSection'   => implode( ', ', $post_terms ),
		);

		// Handle post Image.
		if ( has_post_thumbnail( $post ) ) {
			$image_id = get_post_thumbnail_id();

			$rtn_data['image'] = $this->prepare_image( $image_id, $post_url . '#primaryimage' );
		}

		return $rtn_data;
	}

	/**
	 * Prepare Author Data
	 *
	 * TODO ?Move to Graph (Thing) Properties?
	 *
	 * @since 3.2
	 *
	 * @return array
	 */
	protected function prepare_author() {
		global $post;
		global $aioseop_options;

//		if ( 'person' === $aioseop_options['aiosp_site_represents'] && intval( $post->post_author ) === intval( $aioseop_options['aiosp_person_user'] ) ) {
//			$author_url = home_url() . '/';
//		} else {
//			$author_url = get_author_posts_url( $post->post_author );
//		}

		$author_url = get_author_posts_url( $post->post_author );

		$rtn_data = array(
			'@id' => $author_url . '#author',
		);

		return $rtn_data;
	}


}
