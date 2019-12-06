<?php
/**
 * AIOSEOP_PHP_Functions class
 *
 * Alternative PHP functions for improved operations or compatibility with pre-existing functions that had param changes.
 *
 * @package All-in-One-SEO-Pack
 * @since 3.4.0
 */

/**
 * Class AIOSEOP_PHP_Functions
 *
 * Access to these methods is done statically.
 * Adding any additional methods for PHP functions should be reserved only for pre-existing functions.
 * Any non-existing functions in older PHP versions should use `inc/compatibility/php-functions.php`.
 *
 * @since 3.4.0
 */
class AIOSEOP_PHP_Functions {

	/**
	 * Convert a string to lower case
	 * Compatible with mb_strtolower(), an UTF-8 friendly replacement for strtolower()
	 *
	 * @since ?
	 * @since 3.4.0 Change to static method.
	 *
	 * @param string $str
	 * @return string
	 */
	public static function strtolower( $str ) {
		return AIOSEOP_PHP_Functions::convert_case( $str, 'lower' );
	}

	/**
	 * Convert a string to upper case
	 * Compatible with mb_strtoupper(), an UTF-8 friendly replacement for strtoupper()
	 *
	 * @since ?
	 * @since 3.4.0 Change to static method.
	 *
	 * @param string $str
	 * @return string
	 */
	public static function strtoupper( $str ) {
		return AIOSEOP_PHP_Functions::convert_case( $str, 'upper' );
	}

	/**
	 * Convert a string to title case
	 * Compatible with mb_convert_case(), an UTF-8 friendly replacement for ucwords()
	 *
	 * @since ?
	 * @since 3.4.0 Change to static method.
	 *
	 * @param string $str
	 * @return string
	 */
	public static function ucwords( $str ) {
		return AIOSEOP_PHP_Functions::convert_case( $str, 'title' );
	}

	/**
	 * Case conversion; handle non UTF-8 encodings and fallback **
	 *
	 * @since ?
	 * @since 3.4.0 Change to static method.
	 *
	 * @param string $str
	 * @param string $mode
	 * @return string
	 */
	private static function convert_case( $str, $mode = 'upper' ) {
		static $charset = null;
		if ( null == $charset ) {
			$charset = get_bloginfo( 'charset' );
		}
		$str = (string) $str;
		if ( 'title' == $mode ) {
			if ( function_exists( 'mb_convert_case' ) ) {
				return mb_convert_case( $str, MB_CASE_TITLE, $charset );
			} else {
				return ucwords( $str );
			}
		}

		if ( 'UTF-8' == $charset ) {
			// phpcs:disable WordPress.NamingConventions.ValidVariableName.VariableNotSnakeCase
			global $UTF8_TABLES;
			include_once( AIOSEOP_PLUGIN_DIR . 'inc/aioseop_UTF8.php' );
			if ( is_array( $UTF8_TABLES ) ) {
				if ( 'upper' == $mode ) {
					return strtr( $str, $UTF8_TABLES['strtoupper'] );
				}
				if ( 'lower' == $mode ) {
					return strtr( $str, $UTF8_TABLES['strtolower'] );
				}
			}
			// phpcs:enable
		}

		if ( 'upper' == $mode ) {
			if ( function_exists( 'mb_strtoupper' ) ) {
				return mb_strtoupper( $str, $charset );
			} else {
				return strtoupper( $str );
			}
		}

		if ( 'lower' == $mode ) {
			if ( function_exists( 'mb_strtolower' ) ) {
				return mb_strtolower( $str, $charset );
			} else {
				return strtolower( $str );
			}
		}

		return $str;
	}

	/**
	 * Wrapper for strlen() - uses mb_strlen() if possible.
	 *
	 * @since ?
	 * @since 3.4.0 Change to static method.
	 *
	 * @param $string
	 * @return int
	 */
	public static function strlen( $string ) {
		if ( function_exists( 'mb_strlen' ) ) {
			return mb_strlen( $string, 'UTF-8' );
		}

		return strlen( $string );
	}

	/**
	 * Wrapper for substr() - uses mb_substr() if possible.
	 *
	 * @since ?
	 * @since 3.4.0 Change to static method.
	 *
	 * @param     $string
	 * @param int $start
	 * @param int $length
	 * @return mixed
	 */
	public static function substr( $string, $start = 0, $length = 2147483647 ) {
		$args = func_get_args();
		if ( function_exists( 'mb_substr' ) ) {
			return call_user_func_array( 'mb_substr', $args );
		}

		return call_user_func_array( 'substr', $args );
	}

	/**
	 * Wrapper for strpos() - uses mb_strpos() if possible.
	 *
	 * @since ?
	 * @since 3.4.0 Change to static method.
	 *
	 * @param        $haystack
	 * @param string $needle
	 * @param int    $offset
	 * @return bool|int
	 */
	public static function strpos( $haystack, $needle, $offset = 0 ) {
		if ( function_exists( 'mb_strpos' ) ) {
			return mb_strpos( $haystack, $needle, $offset, 'UTF-8' );
		}

		return strpos( $haystack, $needle, $offset );
	}

	/**
	 * Wrapper for strrpos() - uses mb_strrpos() if possible.
	 *
	 * @since ?
	 * @since 3.4.0 Change to static method.
	 *
	 * @param        $haystack
	 * @param string $needle
	 * @param int    $offset
	 * @return bool|int
	 */
	public static function strrpos( $haystack, $needle, $offset = 0 ) {
		if ( function_exists( 'mb_strrpos' ) ) {
			return mb_strrpos( $haystack, $needle, $offset, 'UTF-8' );
		}

		return strrpos( $haystack, $needle, $offset );
	}
}
