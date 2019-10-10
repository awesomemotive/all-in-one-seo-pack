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

if ( ! function_exists( 'parse_ini_string' ) ) {
	/**
	 * Parse INI String
	 *
	 * Parse_ini_string() doesn't exist pre PHP 5.3.
	 *
	 * @since ?
	 * @since Moved from inc/aioseop_functions.php to inc/compatibility/php-functions.php
	 *
	 * @param string $string
	 * @param bool $process_sections
	 * @return array|bool
	 */
	function parse_ini_string( $string, $process_sections ) {
		if ( ! class_exists( 'parse_ini_filter' ) ) {

			/**
			 * Class parse_ini_filter
			 *
			 * Define our filter class.
			 */
			// @codingStandardsIgnoreStart
			class parse_ini_filter extends php_user_filter
			{
				// @codingStandardsIgnoreEnd
				/**
				 * Buffer
				 *
				 * @since ?
				 *
				 * @var string $buf
				 */
				static $buf = '';

				/**
				 * The actual filter for parsing.
				 *
				 * @param $in
				 * @param $out
				 * @param $consumed
				 * @param $closing
				 *
				 * @return int
				 */
				function filter( $in, $out, &$consumed, $closing ) {
					$bucket = stream_bucket_new( fopen( 'php://memory', 'wb' ), self::$buf );
					stream_bucket_append( $out, $bucket );

					return PSFS_PASS_ON;
				}
			}

			// Register our filter with PHP.
			if ( ! stream_filter_register( 'parse_ini', 'parse_ini_filter' ) ) {
				return false;
			}
		}
		parse_ini_filter::$buf = $string;

		return parse_ini_file( 'php://filter/read=parse_ini/resource=php://memory', $process_sections );
	}
}
