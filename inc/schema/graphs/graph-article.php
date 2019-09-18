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
			'publisher'        => array( '@id' => home_url() . '/#' . $aioseop_options['aiosp_schema_site_represents'] ),
			'articleSection'   => implode( ', ', $post_terms ),
		);

		// Handle post Image.
		$image_schema = $this->prepare_image( $this->get_article_image_data( $post ), $post_url . '#primaryimage' );
		if ( $image_schema ) {
			$rtn_data['image'] = $image_schema;
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

		$author_url = get_author_posts_url( $post->post_author );

		$rtn_data = array(
			'@id' => $author_url . '#author',
		);

		return $rtn_data;
	}

	/**
	 * Get Image Data for Article
	 *
	 * Retrieves the image (data) required for the articles. This uses multiple sources in order to
	 * complete the required field.
	 *
	 * Attempts to access image sources by the following order.
	 *
	 * 1. Gets Featured Image from Post.
	 * 2. If 'organization', get Organization Logo.
	 * 3. If 'person', get User's avatar.
	 * 4. Get Image url from Post Content.
	 * 5. Get Site Logo from theme customizer.
	 *
	 * @since 3.2
	 *
	 * @param WP_post $post
	 * @return array
	 */
	protected function get_article_image_data( $post ) {
		global $aioseop_options;

		$rtn_image_data = $this->get_image_data_defaults();

		if ( has_post_thumbnail( $post ) ) {
			$rtn_image_data = $this->get_site_image_data( get_post_thumbnail_id() );
		} elseif ( 'organization' === $aioseop_options['aiosp_schema_site_represents'] && ! empty( $aioseop_options['aiosp_schema_organization_logo'] ) ) {
			$rtn_image_data = $this->get_site_image_data( $aioseop_options['aiosp_schema_organization_logo'] );
		} elseif ( 'person' === $aioseop_options['aiosp_schema_site_represents'] && ! empty( $post->post_author ) ) {
			$rtn_image_data = $this->get_user_image_data( intval( $post->post_author ) );
		} else {
			$content_image_url = $this->get_image_url_from_content( $post );
			if ( ! empty( $content_image_url ) ) {
				$rtn_image_data = wp_parse_args( $this->get_image_data_defaults(), array( 'url' => $content_image_url ) );
			} else {
				$blog_logo = get_theme_mod( 'custom_logo' );
				if ( $blog_logo ) {
					$rtn_image_data = $this->get_site_image_data( $blog_logo );
				}
			}
		}

		return $rtn_image_data;
	}

}
