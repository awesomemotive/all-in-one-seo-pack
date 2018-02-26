<?php
if ( ! class_exists( 'All_in_One_SEO_Pack_Compatible' ) ) {
    /**
     * Abstract class to be used to create compatibility with 3rd party wordpress plugins.
     *
     * @package All-in-One-SEO-Pack
     * @author Alejandro Mostajo
     * @copyright Semperfi Web Design <https://semperplugins.com/>
     * @version 2.3.13
     * @since 2.3.12.3
     */
    abstract class All_in_One_SEO_Pack_Compatible {
        /**
         * Returns flag indicating if compatible plugin exists in current instalation or not.
         * This function should be overwritten on child class.
         * @since 2.3.12.3
         *
         * @return bool
         */
        public function exists() {
            return false;
        }

        /**
         * Method executed by compatibility handler to declare hooks and/or any other compatibility code needed.
         * @since 2.3.12.3
         */
        public function hooks() {
            // TODO per compatible plugin.
        }
    }
}
