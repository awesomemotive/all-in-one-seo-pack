<?php
/**
 * Schema Graph SearchResultsPage Class
 *
 * Acts as the search results page class for Schema SearchResultsPage.
 *
 * @package All_in_One_SEO_Pack
 */

/**
 * Class AIOSEOP_Graph_SearchResultsPage
 *
 * @see AIOSEOP_Graph_Creativework
 * @see AIOSEOP_Graph_WebPage
 * @see Schema SearchResultsPage
 * @link https://schema.org/SearchResultsPage
 */
class AIOSEOP_Graph_SearchResultsPage extends AIOSEOP_Graph_WebPage {

	/**
	 * Get Graph Slug.
	 *
	 * @since 3.2
	 *
	 * @return string
	 */
	protected function get_slug() {
		return 'SearchResultsPage';
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
		return 'Search Results Page';
	}

	/**
	 * Prepare
	 *
	 * @since 3.2
	 *
	 * @return array
	 */
	protected function prepare() {
		return parent::prepare();
	}
}
