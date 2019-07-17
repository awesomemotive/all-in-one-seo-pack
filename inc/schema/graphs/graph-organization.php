<?php
/**
 * Schema Graph Organization Class
 *
 * Acts as the organization class for Schema Organization.
 *
 * @package All_in_One_SEO_Pack
 */

/**
 * Class AIOSEOP_Graph_Organization
 *
 * @see Schema Organization
 * @link https://schema.org/Organization
 */
class AIOSEOP_Graph_Organization extends AIOSEOP_Graph {

	/**
	 * Get Graph Slug.
	 *
	 * @since 3.2
	 *
	 * @return string
	 */
	protected function get_slug() {
		return 'Organization';
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
		return 'Organization';
	}

	/**
	 * Prepare data.
	 *
	 * @since 3.2
	 *
	 * @return array
	 */
	protected function prepare() {
		global $aioseop_options;

		$rtn_data = array(
			'@type' => $this->slug,
			'@id'   => home_url() . '/#' . strtolower( $this->slug ),
			'url'   => home_url() . '/',
		);

		// Site represents Organization or Person.
		if ( 'person' === $aioseop_options['aiosp_site_represents'] ) {
			$person_id   = intval( $aioseop_options['aiosp_person_user'] );
			$person_name = get_the_author_meta( 'display_name', $person_id );

			$rtn_data['@type']  = array( 'Person', $this->slug );
			$rtn_data['@id']    = home_url() . '/#person';
			$rtn_data['name']   = get_the_author_meta( 'display_name', $person_id );
			$rtn_data['sameAs'] = $this->get_user_social_profile_links( $person_id );

			// Handle Logo/Image.
			$user_image_url = $this->get_user_image_url( $person_id );
			if ( ! empty( $user_image_url ) ) {
				$rtn_data['image'] = array(
					'@type'   => 'ImageObject',
					'@id'     => home_url() . '/#personlogo',
					'url'     => $user_image_url,
					'caption' => $person_name,
				);
				$rtn_data['logo'] = array( '@id' => home_url() . '/#personlogo' );
			}
		} else {
			// Get Name from General > Schema Settings > Organization Name, and fallback on WP's Site Name.
			$rtn_data['name']   = $aioseop_options['aiosp_organization_name'] ?: get_bloginfo( 'name' );
			$rtn_data['sameAs'] = $this->get_site_social_profile_links();

			// Handle Logo/Image.
			$data_logo = $this->prepare_logo();
			if ( ! empty( $data_logo ) ) {
				$rtn_data['logo'] = $data_logo;

				$rtn_data['image'] = array(
					'@id' => home_url() . '/#logo',
				);
			}

			// Handle contactPoint.
			if ( ! empty( $aioseop_options['aiosp_phone_number'] ) ) {
				$rtn_data['contactPoint'] = $this->prepare_contactpoint();
			}
		}

		return $rtn_data;
	}

	/**
	 * Prepare Logo Data.
	 *
	 * @since 3.2
	 *
	 * @return array
	 */
	protected function prepare_logo() {
		$rtn_data = array();

		$logo_id   = $this->get_logo_id();
		$logo_meta = wp_get_attachment_metadata( $logo_id );
		if ( ! empty( $logo_id ) ) {
			$rtn_data = array(
				'@type'   => 'ImageObject',
				'@id'     => home_url() . '/#logo',
				'url'     => wp_get_attachment_image_url( $logo_id, 'full' ),
				'width'   => $logo_meta['width'],
				'height'  => $logo_meta['height'],
				'caption' => wp_get_attachment_caption( $logo_id ),
			);
		}

		return $rtn_data;
	}

	/**
	 * Prepare ContactPoint Data.
	 *
	 * TODO !?Move to graph.php since it is part of schema 'thing' object?!
	 *
	 * @since 3.2
	 *
	 * @return array
	 */
	protected function prepare_contactpoint() {
		global $aioseop_options;

		$rtn_data = array(
			'@type'       => 'ContactPoint',
			'telephone'   => $aioseop_options['aiosp_phone_number'],
			'contactType' => $aioseop_options['aiosp_contact_type'],
		);

		return $rtn_data;
	}

	/**
	 * Get Site Social Links
	 *
	 * @since 3.2
	 *
	 * @return array
	 */
	protected function get_site_social_profile_links() {
		// TODO Get website's Social Profile Links.
		global $aioseop_options;

		$social_links = array();

		if ( ! empty( $aioseop_options['aiosp_social_profile_links'] ) ) {
			$social_links = explode( ',', str_replace( array( "\r\n", "\r" ), '', $aioseop_options['aiosp_social_profile_links'] ) );
		}

		return $social_links;
	}

	/**
	 * Get Custom Logo
	 *
	 * Retrieves the custom logo from WP's customizer for theme customizations.
	 *
	 * @since 3.2
	 *
	 * @return int|mixed
	 */
	protected function get_logo_id() {
		global $aioseop_options;

		$logo_id = 0;

		// Uses logo selected from General Settings > Schema Settings > Organization Logo.
		if ( ! empty( $aioseop_options['aiosp_organization_logo'] ) ) {
			$logo_id = aiosp_common::attachment_url_to_postid( $aioseop_options['aiosp_organization_logo'] );
		}

		// Fallback on Customizer site logo.
		if ( ! $logo_id ) {
			$logo_id = get_theme_mod( 'custom_logo' );
		}

		// Prevent case type errors if empty/false.
		if ( ! $logo_id ) {
			$logo_id = 0;
		}

		return $logo_id;
	}
}
