<?php
/**
 * Schema Graph CollectionPage Class
 *
 * Acts as the collection page class for Schema CollectionPage.
 *
 * @package All_in_One_SEO_Pack
 */

/**
 * Class AIOSEOP_Graph_CollectionPage
 *
 * @see Schema CollectionPage
 * @link https://schema.org/CollectionPage
 */
class AIOSEOP_Graph_CollectionPage extends AIOSEOP_Graph_WebPage {

	/**
	 * Get Graph Slug.
	 *
	 * @since 3.2
	 *
	 * @return string
	 */
	protected function get_slug() {
		return 'CollectionPage';
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
		return 'Collection Page';
	}

	/**
	 * Prepare data.
	 *
	 * @since 3.2
	 *
	 * @return array
	 */
	protected function prepare() {
		return parent::prepare();
	}
}
