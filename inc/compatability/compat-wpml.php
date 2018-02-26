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
         * @since 2.3.12.3
         *
         * @return bool
         */
        public function exists() {
            return function_exists( 'icl_object_id' );
        }

        /**
         * Declares compatibility hooks.
         *
         * @since 2.3.12.3
         */
        public function hooks() {
            add_filter( 'aioseop_home_url', array( &$this, 'aioseop_home_url' ) );
            add_filter( 'aioseop_sitemap_xsl_url', array( &$this, 'aioseop_sitemap_xsl_url' ) );
        }

        /**
         * Returns specified url filtered by wpml.
         * This is needed to obtain the correct domain in which wordpress is running on.
         * AIOSEOP would have ran first expecting the return of home_url().
         *
         * @since 2.3.12.3
         *
         * @param string $path Relative path or url.
         *
         * @param string filtered url.
         */
        public function aioseop_home_url( $path ) {
            $url = apply_filters( 'wpml_home_url', home_url( '/' ) );
            // Remove query string
            preg_match_all( '/\?[\s\S]+/', $url, $matches );
            // Get base
            $url = preg_replace( '/\?[\s\S]+/', '', $url);
            $url = trailingslashit( $url );
            $url .= preg_replace( '/\//', '', $path, 1);
            // Add query string
            if ( count( $matches ) > 0 && count( $matches[0] ) > 0 )
                $url .= $matches[0][0];
            return $url;
        }
        /**
         * Returns XSL url without query string.
         *
         * @since 2.3.12.3
         *
         * @param string $url XSL url.
         *
         * @param string filtered url.
         */
        public function aioseop_sitemap_xsl_url( $url )
        {
            return preg_replace( '/\?[\s\S]+/', '', $url);
        }
    }
}
