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

		if ( is_home() ) {
			if ( is_front_page() ) {
				// Front Page for 'Your latest posts'.
				$current_url  = home_url() . '/';
				$current_name = get_bloginfo( 'name' );
				$current_desc = get_bloginfo( 'description' );
			} else {
				// A static page - Posts page.
				// Resembles elseif $wp_query->is_posts_page.
				$page_id = get_option( 'page_for_posts' );

				$current_url  = wp_get_canonical_url( $page_id );
				$current_name = get_the_title( $page_id );
				$current_desc = $this->get_post_description( get_post( $page_id ) );
			}
		} elseif ( is_front_page() && is_page() ) {
			// A static page - Homepage.
			$current_url  = home_url() . '/';
			$current_name = get_the_title();
			$current_desc = $this->get_post_description( $post );
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
				$current_name = sprintf( __( 'Year: %s', 'all-in-one-seo-pack' ), get_the_date( 'Y' ) );
			} elseif ( is_month() ) {
				$current_url = get_month_link( false, false );
				/* translators: Monthly archive title. %s: Month name and year */
				$current_name = sprintf( __( 'Month: %s', 'all-in-one-seo-pack' ), get_the_date( 'F Y' ) );
			} else {
				$current_url = get_day_link( false, false, false );
				/* translators: Daily archive title. %s: Date */
				$current_name = sprintf( __( 'Day: %s', 'all-in-one-seo-pack' ), get_the_date( 'F j, Y' ) );
			}
		} elseif ( is_author() ) {
			$user_id      = intval( $post->post_author );
			$current_url  = get_author_posts_url( $user_id );
			$current_name = get_the_author_meta( 'display_name', $user_id );
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
			if ( has_post_thumbnail( $post ) ) {
				$image_id = get_post_thumbnail_id();

				$image_schema = $this->prepare_image( $this->get_site_image_data( $image_id ), $current_url . '#primaryimage' );
				if ( $image_schema ) {
					$rtn_data['image']              = $image_schema;
					$rtn_data['primaryImageOfPage'] = array( '@id' => $current_url . '#primaryimage' );
				}
			}

			$rtn_data['datePublished'] = mysql2date( DATE_W3C, $post->post_date_gmt, false );
			$rtn_data['dateModified']  = mysql2date( DATE_W3C, $post->post_modified_gmt, false );
		}

		if ( is_front_page() ) {
			$rtn_data['about'] = array(
				'@id' => home_url() . '/#' . $aioseop_options['aiosp_schema_site_represents'],
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

		// Using AIOSEOP's description is limited in content. With Schema's descriptions, there is no cap limit.
		$post_description = get_post_meta( $post->ID, '_aioseop_description', true );

		// If there is no AIOSEOP description, and the post isn't password protected, then use post excerpt or content.
		if ( ! $post_description && ! post_password_required( $post ) ) {
			if ( ! empty( $post->post_excerpt ) ) {
				$post_description = $post->post_excerpt;
			}
		}

		if ( ! empty( $post_description ) && is_string( $post_description ) ) {
			$rtn_description = $post_description;
		}

		return $rtn_description;
	}

}
