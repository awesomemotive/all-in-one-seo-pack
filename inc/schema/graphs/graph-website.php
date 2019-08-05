<?php
/**
 * Schema Graph WebSite Class
 *
 * Acts as the website class for Schema WebSite.
 *
 * @package All_in_One_SEO_Pack
 */

/**
 * Class AIOSEOP_Graph_WebPage
 *
 * @see AIOSEOP_Graph_Creativework
 * @see Schema WebSite
 * @link https://schema.org/WebSite
 */
class AIOSEOP_Graph_WebSite extends AIOSEOP_Graph_Creativework {

	/**
	 * Get Graph Slug.
	 *
	 * @since 3.2
	 *
	 * @return string
	 */
	protected function get_slug() {
		return 'WebSite';
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
		return 'Website';
	}

	/**
	 * Prepare
	 *
	 * @since 3.2
	 *
	 * @return array
	 */
	protected function prepare() {
		global $aioseop_options;

		$rtn_data = array(
			'@type'     => $this->slug,
			'@id'       => home_url() . '/#' . strtolower( $this->slug ),
			'url'       => home_url() . '/',
			'name'      => get_bloginfo( 'name' ),
			'publisher' => array(
				'@id' => home_url() . '/#' . $aioseop_options['aiosp_schema_site_represents'],
			),
		);

		if ( $aioseop_options['aiosp_schema_search_results_page'] ) {
			$rtn_data['potentialAction'] = array(
				'@type'       => 'SearchAction',
				'target'      => home_url() . '/?s={search_term_string}',
				'query-input' => 'required name=search_term_string',
			);
		}

		return $rtn_data;
	}

}
