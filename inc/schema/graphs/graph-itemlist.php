<?php
/**
 * Schema Graph ItemList Class
 *
 * Acts as the Item List class for Schema ItemList.
 *
 * @package All_in_One_SEO_Pack
 */

/**
 * Class AIOSEOP_Graph_ItemList
 *
 * @see Schema ItemList
 * @link https://schema.org/ItemList
 */
class AIOSEOP_Graph_ItemList extends AIOSEOP_Graph {

	/**
	 * Get Graph Slug.
	 *
	 * @since 3.4.0
	 *
	 * @return string
	 */
	protected function get_slug() {
		return 'ItemList';
	}

	/**
	 * Get Graph Name.
	 *
	 * Intended for frontend use when displaying which schema graphs are available.
	 *
	 * @since 3.4.0
	 *
	 * @return string
	 */
	protected function get_name() {
		return 'Item List';
	}

	/**
	 * Prepare data.
	 *
	 * @since 3.4.0
	 *
	 * @return array
	 */
	protected function prepare() {
		return parent::prepare();
	}
}
