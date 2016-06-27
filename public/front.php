<?php

if ( ! class_exists( 'All_in_One_SEO_Pack_Front' ) ) {

	/**
	 * Class All_in_One_SEO_Pack_Front
	 */
	class All_in_One_SEO_Pack_Front {

		function __construct() {

			add_action( 'template_redirect', array( $this, 'noindex_follow_rss' ) );

		}

		/**
		 * Noindex and follow RSS feeds.
		 *
		 * @Since 2.3.6
		 */
		public function noindex_follow_rss(){
			if ( is_feed() && headers_sent() === false ) {
				header( 'X-Robots-Tag: noindex, follow', true );
			}
		}
	}

}

$aiosp_front_class = new All_in_One_SEO_Pack_Front();

