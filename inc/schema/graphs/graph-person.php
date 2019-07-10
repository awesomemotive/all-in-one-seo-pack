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

		$user_id   = $this->get_site_user_id();
		$user_data = get_userdata( $user_id );

		$rtn_data = array(
			'@type' => $this->slug,
			'@id'   => home_url() . '#' . strtolower( $this->slug ),
			'@id'   => get_author_posts_url( $post->post_author ) . '#author',
			'name'  => $user_data->display_name,
		);

		$logo_url = $this->get_logo_url( $user_id );
		if ( ! empty( $logo_url ) ) {
			$rtn_data['image'] = array(
				'@type'   => 'ImageObject',
				'@id'     => home_url() . '/#personlogo',
				'url'     => $this->get_logo_url( $user_id ),
				'caption' => $user_data->display_name,
			);
		}

		return $rtn_data;
	}

	/**
	 * Get Website's User's ID
	 *
	 * @since 3.2
	 *
	 * @return string
	 */
	protected function get_site_user_id() {
		// TODO Add settings to determine site owner.
		// Fallback: Get list of Admins, and pick the first admin.
		return '1';
	}

	/**
	 * Get User's Logo URL
	 *
	 * @since 3.2
	 *
	 * @param $user_id
	 */
	protected function get_logo_url( $user_id ) {
		$rtn_logo_url = '';

		$show_avatars = get_option( 'show_avatars' );
		if ( $show_avatars ) {
			$rtn_logo_url = get_avatar_url( $user_id );
		}

		return $rtn_logo_url;
	}

}
