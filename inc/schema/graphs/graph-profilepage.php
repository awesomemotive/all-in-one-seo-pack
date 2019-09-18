<?php
/**
 * Schema Graph ProfilePage Class
 *
 * Acts as the profile page class for Schema ProfilePage.
 *
 * @package All_in_One_SEO_Pack
 */

/**
 * Class AIOSEOP_Graph_ProfilePage
 *
 * @see AIOSEOP_Graph_Creativework
 * @see AIOSEOP_Graph_WebPage
 * @see Schema ProfilePage
 * @link https://schema.org/ProfilePage
 */
class AIOSEOP_Graph_ProfilePage extends AIOSEOP_Graph_WebPage {

	/**
	 * Get Graph Slug.
	 *
	 * @since 3.2
	 *
	 * @return string
	 */
	protected function get_slug() {
		return 'ProfilePage';
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
		return 'Profile Page';
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
