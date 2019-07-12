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

		$user_id    = ( 'person' === $aioseop_options['aiosp_site_represents'] ) ? intval( $aioseop_options['aiosp_person_user'] ) : 1;
		$author_url = '';
		$hashtag    = 'person';

		if ( 'person' === $aioseop_options['aiosp_site_represents'] && $user_id === intval( $aioseop_options['aiosp_person_user'] ) ) {
			$user_id = intval( $aioseop_options['aiosp_person_user'] );
			$author_url  = home_url() . '/';
		} elseif ( ! empty( $post->post_author ) ) {
			$user_id = intval( $post->post_author );
			$author_url = get_author_posts_url( $post->post_author );
			$hashtag    = 'author';
		}
		$author_name = get_the_author_meta( 'display_name', $user_id );

		$rtn_data = array(
			'@type'  => $this->slug,
			'@id'    => $author_url . '#' . $hashtag,
			'name'   => $author_name,
			'sameAs' => $this->get_social_profiles( $user_id ),
		);

		$logo_url = $this->get_logo_url( $user_id );
		if ( ! empty( $logo_url ) ) {
			$rtn_data['image'] = array(
				'@type'   => 'ImageObject',
				'@id'     => home_url() . '/#personlogo',
				'url'     => $this->get_logo_url( $user_id ),
				'caption' => $author_name,
			);
		}

		return $rtn_data;
	}

	/**
	 * Get User's Logo URL
	 *
	 * @since 3.2
	 *
	 * @param int $user_id
	 * @return string
	 */
	protected function get_logo_url( $user_id ) {
		$rtn_logo_url = '';

		$show_avatars = get_option( 'show_avatars' );
		if ( $show_avatars ) {
			$rtn_logo_url = get_avatar_url( $user_id );
		}

		return $rtn_logo_url;
	}

	/**
	 * Get Social Profiles from user id.
	 *
	 * @since 3.2
	 *
	 * @param int $user_id
	 * @return array
	 */
	protected function get_social_profiles( $user_id ) {
		$rtn_social_profiles = array();
		$social_sites = array(
			'facebook',
			'twitter',
		);

		foreach ( $social_sites as $social_site ) {
			$author_social_link = get_the_author_meta( $social_site, $user_id );

			if ( $author_social_link ) {
				$rtn_social_profiles[] = $author_social_link;
			}
		}

		return $rtn_social_profiles;
	}

}
