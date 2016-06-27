<?php

if ( ! class_exists( 'All_in_One_SEO_Pack_Compatibility' ) ) {

	/**
	 * Class All_in_One_SEO_Pack_Compatibility
	 */
	class All_in_One_SEO_Pack_Compatibility {

		function __construct() {


			$this->load_compatibility_classes();


		}

		function load_compatibility_hooks(){
			// We'll use this until we set up out classes.

			if( class_exists( 'jetpack' ) ) {
				add_filter( 'jetpack_get_available_modules', array( $this, 'remove_jetpack_sitemap' ) );
			}
		}

		function remove_jetpack_sitemap( $modules ) {
			// Remove Jetpack's sitemap
			unset( $modules['sitemaps'] );
			return $modules;

		}

		function load_compatibility_classes() {
			// Eventually we'll load our other classes from here.
			$this->load_compatibility_hooks();
		}
	}

}

$aiosp_compat = new All_in_One_SEO_Pack_Compatibility();

