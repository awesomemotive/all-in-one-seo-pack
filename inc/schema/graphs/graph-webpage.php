<?php
/**
 * Schema Graph WebPage Class
 *
 * Acts as the web page class for Schema WebPage.
 *
 * @package All_in_One_SEO_Pack
 */

/**
 * Class AIOSEOP_Graph_WebPage
 *
 * @see AIOSEOP_Graph_Creativework
 * @see Schema WebPage
 * @link https://schema.org/WebPage
 */
class AIOSEOP_Graph_WebPage extends AIOSEOP_Graph_Creativework {

	/**
	 * Get Graph Slug.
	 *
	 * @since 3.2
	 *
	 * @return string
	 */
	protected function get_slug() {
		return 'WebPage';
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
		return 'Web Page';
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

		$current_url  = '';
		$current_name = '';
		$current_desc = '';

		if ( is_front_page() ) {
			$current_url  = home_url() . '/';
			$current_name = get_bloginfo( 'name' ) . ' - ' . get_bloginfo( 'description' );
			$current_desc = get_bloginfo( 'description' );
		} elseif ( is_singular() || is_single() ) {
			$current_url  = wp_get_canonical_url( $post );
			$current_name = get_the_title();
			$current_desc = $this->get_post_description( $post );
		} elseif ( is_tax() || is_category() || is_tag() ) {
			$term = get_queried_object();

			$current_url  = get_term_link( $term );
			$current_name = $term->name;
			$current_desc = $term->description;
		} elseif ( is_date() ) {
			if ( is_year() ) {
				$current_url = get_year_link( false );
				/* translators: Yearly archive title. %s: Year */
				$current_name = sprintf( __( 'Year: %s', 'all-in-one-seo-pack' ), get_the_date( _x( 'Y', 'yearly archives date format', 'all-in-one-seo-pack' ) ) );

			} elseif ( is_month() ) {
				$current_url = get_month_link( false, false );
				/* translators: Monthly archive title. %s: Month name and year */
				$current_name = sprintf( __( 'Month: %s', 'all-in-one-seo-pack' ), get_the_date( _x( 'F Y', 'monthly archives date format', 'all-in-one-seo-pack' ) ) );
			} else {
				$current_url = get_day_link( false, false, false );
				/* translators: Daily archive title. %s: Date */
				$current_name = sprintf( __( 'Day: %s', 'all-in-one-seo-pack' ), get_the_date( _x( 'F j, Y', 'daily archives date format', 'all-in-one-seo-pack' ) ) );
			}
		} elseif ( is_search() ) {
			$current_url = get_search_link();
			/* Translators: String used in search query: %s: Search */
			$current_name = sprintf( __( 'Search results for "%s"', 'all-in-one-seo-pack' ), esc_html( get_search_query() ) );
		}

		$rtn_data = array(
			'@type'      => $this->slug,
			'@id'        => $current_url . '#' . strtolower( $this->slug ), // TODO Should this be `#webpage`?
			'url'        => $current_url,
			'inLanguage' => get_bloginfo( 'language' ),
			'name'       => $current_name,
			'isPartOf'   => array(
				'@id' => home_url() . '/#website',
			),

		);

		// Handles pages.
		if ( is_singular() || is_single() ) {
			// TODO add functionality.
			$image_data = $this->prepare_image( $post );

			if ( $image_data ) {
				$rtn_data['image']              = $image_data;
				$rtn_data['primaryImageOfPage'] = array( '@id' => $current_url . '#primaryimage' );
			}

			$rtn_data['datePublished'] = mysql2date( DATE_W3C, $post->post_date_gmt, false );
			$rtn_data['dateModified']  = mysql2date( DATE_W3C, $post->post_modified_gmt, false );
		}

		if ( is_front_page() ) {
			$rtn_data['about'] = array(
				'@id' => home_url() . '/#' . $aioseop_options['aiosp_site_represents'],
			);
		}

		if ( ! empty( $current_desc ) ) {
			$rtn_data['description'] = $current_desc;
		}

		return $rtn_data;
	}



	/**
	 * Get Post Description.
	 *
	 * @since 3.2
	 *
	 * @param WP_Post $post See WP_Post for details.
	 * @return string
	 */
	protected function get_post_description( $post ) {
		$rtn_description = '';

		$post_description = get_post_meta( $post->ID, '_aioseop_description', true );

		if ( ! $post_description && ! post_password_required( $post ) ) {
			if ( ! empty( $post->post_excerpt ) ) {
				$post_description = $post->post_excerpt;
			} elseif ( ! empty( $post->post_content ) ) {
				$post_description = $post->post_content;
				$post_description = aioseop_do_shortcodes( $post_description );
				$post_description = strip_shortcodes( $post_description );
				$post_description = wp_strip_all_tags( $post_description );
				// TODO ?trim length of post description?
			}
		}

		if ( ! empty( $post_description ) && is_string( $post_description ) ) {
			$rtn_description = $post_description;
		}

		return $rtn_description;
	}

}
