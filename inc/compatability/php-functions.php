<?php
/**
 * Compatibility functions for PHP.
 *
 * @package All_in_One_SEO_Pack
 */

if ( ! function_exists( 'array_column' ) ) {
	/**
	 * Array Column PHP 5 >= 5.5.0, PHP 7
	 *
	 * Return the values from a single column in the input array.
	 *
	 * Pre-5.5 replacement/drop-in.
	 *
	 * @since 3.2
	 *
	 * @param array  $input
	 * @param string $column_key
	 * @return array
	 */
	function array_column( $input, $column_key ) {
		return array_combine( array_keys( $input ), wp_list_pluck( $input, $column_key ) );
	}
}
