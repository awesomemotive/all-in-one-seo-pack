<?php
if ( ! class_exists( 'All_in_One_SEO_Pack_Wpml' ) ) {
    /**
     * Compatibility with WPML - Wordpress Multilingual Plugin
     *
     * @link https://wpml.org/
     * @package All-in-One-SEO-Pack
     * @author Alejandro Mostajo
     * @copyright Semperfi Web Design <https://semperplugins.com/>
     * @version 2.3.13
     */
    class All_in_One_SEO_Pack_Wpml extends All_in_One_SEO_Pack_Compatible {
        /**
         * Returns flag indicating if WPML is present.
         *
         * @since 2.3.13
         *
         * @return bool
         */
        public function exists() {
            return function_exists( 'icl_object_id' );
        }

        /**
         * Declares compatibility hooks.
         *
         * @since 2.3.13
         */
        public function hooks() {
            add_filter( 'aioseop_home_url', array( &$this, 'aioseop_home_url' ) );
        }

        /**
         * Returns specified url filtered by wpml.
         * This is needed to obtain the correct domain in which wordpress is running on.
         * AIOSEOP would have ran first expecting the return of home_url().
         *
         * @since 2.3.13
         *
         * @param string $path Relative path or url.
         *
         * @param string filtered url.
         */
        public function aioseop_home_url( $path ) {
            return apply_filters( 'wpml_home_url', home_url( '/' ) ) . preg_replace( '/\//', '', $path, 1);
        }
    }
}