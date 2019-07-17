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

		$user_image_url = $this->get_user_image_url( $user_id );
		if ( ! empty( $user_image_url ) ) {
			$rtn_data['image'] = array(
				'@type'   => 'ImageObject',
				'@id'     => home_url() . '/#personlogo',
				'url'     => $user_image_url,
				'caption' => $author_name,
			);
		}

		if ( is_author() ) {
			$rtn_data['mainEntityOfPage'] = array( '@id' => $author_url . '#profilepage' );
		}

		return $rtn_data;
	}

}
