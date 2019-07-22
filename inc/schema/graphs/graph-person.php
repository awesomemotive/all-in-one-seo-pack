<?php
/**
 * Schema Graph Person Class
 *
 * Acts as the person class for Schema Person.
 *
 * @package All_in_One_SEO_Pack
 */

/**
 * Class AIOSEOP_Graph_Person
 *
 * @see Schema Person
 * @link https://schema.org/Person
 */
class AIOSEOP_Graph_Person extends AIOSEOP_Graph {

	/**
	 * Get Graph Slug.
	 *
	 * @since 3.2
	 *
	 * @return string
	 */
	protected function get_slug() {
		return 'Person';
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
		return 'Person';
	}

	/**
	 * Prepare
	 *
	 * @since 3.2
	 *
	 * @return array
	 */
	protected function prepare() {
		global $post;
		global $aioseop_options;

		$user_id    = 1;
		$author_url = '';
		$hashtag    = 'person';

		if ( ! empty( $post->post_author ) ) {
			$user_id    = intval( $post->post_author );
			$author_url = get_author_posts_url( $post->post_author );
			$hashtag    = 'author';
		}
		$author_name = get_the_author_meta( 'display_name', $user_id );

		$rtn_data = array(
			'@type'  => $this->slug,
			'@id'    => $author_url . '#' . $hashtag,
			'name'   => $author_name,
			'sameAs' => $this->get_user_social_profile_links( $user_id ),
		);

		// Handle Logo/Image.
		$image_schema = $this->prepare_image( $this->get_user_image_data( $user_id ), home_url() . '/#personlogo' );
		if ( $image_schema ) {
			$rtn_data['image'] = $image_schema;
		}

		if ( is_author() ) {
			$rtn_data['mainEntityOfPage'] = array( '@id' => $author_url . '#profilepage' );
		}

		return $rtn_data;
	}

}
