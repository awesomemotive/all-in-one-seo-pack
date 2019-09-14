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
	 * The parse_ini_string() function.
	 *
	 * Added for PHP compatibility with v5.2.
	 *
	 * @author epicmaxim@gmail.com - https://www.php.net/manual/en/function.parse-ini-string.php
	 *
	 * @since 3.3.0
	 *
	 * @param string $str
	 * @return mixed $ret Associative arrray with settings or false on failure.
	 */
	function parse_ini_string( $str ) {

		if ( empty( $str ) ) {
			return false;
		}

		$lines          = explode( "\n", $str );
		$ret            = array();
		$inside_section = false;

		foreach ( $lines as $line ) {

			$line = trim( $line );

			if ( ! $line || '#' == $line[0] || ';' == $line[0] ) {
				continue;
			}

			if ( '[' == $line[0] && strpos( $line, ']' ) == $end_idx ) {
				$inside_section = substr( $line, 1, $end_idx - 1 );
				continue;
			}

			if ( ! strpos( $line, '=' ) ) {
				continue;
			}

			$tmp = explode( '=', $line, 2 );

			if ( $inside_section ) {

				$key   = rtrim( $tmp[0] );
				$value = ltrim( $tmp[1] );

				if ( preg_match( '/^".*"$/', $value ) || preg_match( "/^'.*'$/", $value ) ) {
					$value = mb_substr( $value, 1, mb_strlen( $value, 'UTF-8' ) - 2, 'UTF-8' );
				}

				$t = preg_match( '^\[(.*?)\]^', $key, $matches );
				if ( ! empty( $matches ) && isset( $matches[0] ) ) {

					$arr_name = preg_replace( '#\[(.*?)\]#is', '', $key );

					if ( ! isset( $ret[ $inside_section ][ $arr_name ] ) || ! is_array( $ret[ $inside_section ][ $arr_name ] ) ) {
						$ret[ $inside_section ][ $arr_name ] = array();
					}

					if ( isset( $matches[1] ) && ! empty( $matches[1] ) ) {
						$ret[ $inside_section ][ $arr_name ][ $matches[1] ] = $value;
					} else {
						$ret[ $inside_section ][ $arr_name ][] = $value;
					}
				} else {
					$ret[ $inside_section ][ trim( $tmp[0] ) ] = $value;
				}
			} else {

				$ret[ trim( $tmp[0] ) ] = ltrim( $tmp[1] );
			}
		}
		return $ret;
	}
}
