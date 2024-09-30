<?php

namespace AIOSEO\Plugin\Common\Traits\Helpers;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Numbers trait.
 *
 * @since 4.7.2
 */
trait Numbers {
	/**
	 * Formats a number to a compact format.
	 *
	 * @since 4.7.2
	 *
	 * @param  float|int|string $number   The number to format.
	 * @param  int              $decimals The number of decimal places to include.
	 * @return string                     Formatted number in string format.
	 */
	public function compactNumber( $number, $decimals = 1 ) {
		$suffixes    = [ '', 'K', 'M', 'B', 'T', 'q', 'Q' ];
		$suffixIndex = 0;

		while ( abs( $number ) >= 1000 && $suffixIndex < count( $suffixes ) - 1 ) {
			$suffixIndex++;
			$number /= 1000;
		}

		// Remove trailing zeros.
		return preg_replace( '/\D0+$/', '', number_format_i18n( $number, $decimals ) ) . $suffixes[ $suffixIndex ];
	}
}