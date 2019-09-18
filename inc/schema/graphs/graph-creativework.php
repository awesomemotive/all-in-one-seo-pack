<?php
/**
 * Schema Graph CollectionPage Class
 *
 * Acts as the collection page class for Schema CollectionPage.
 *
 * @package All_in_One_SEO_Pack
 */

/**
 * Class AIOSEOP_Graph_CreativeWork
 *
 * @see Schema CreativeWork
 * @link https://schema.org/CreativeWork
 */
abstract class AIOSEOP_Graph_CreativeWork extends AIOSEOP_Graph {

	/**
	 * Get Graph Slug.
	 *
	 * @since 3.2
	 *
	 * @return string
	 */
	protected function get_slug() {
		return 'creativework';
	}
}
