<?php
namespace AIOSEO\Plugin\Common\Traits\Helpers;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Contains date/time specific helper methods.
 *
 * @since 4.1.2
 */
trait DateTime {
	/**
	 * Formats a date in ISO8601 format.
	 *
	 * @since 4.1.2
	 *
	 * @param  string $date The date.
	 * @return string       The date formatted in ISO8601 format.
	 */
	public function dateToIso8601( $date ) {
		return date( 'Y-m-d', strtotime( $date ) ); // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
	}

	/**
	 * Formats a date & time in ISO8601 format.
	 *
	 * @since 4.0.0
	 *
	 * @param  string $dateTime The date.
	 * @return string           The date formatted in ISO8601 format.
	 */
	public function dateTimeToIso8601( $dateTime ) {
		return date( 'c', strtotime( $dateTime ) ); // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
	}

	/**
	 * Formats a date & time in RFC-822 format.
	 *
	 * @since 4.2.1
	 *
	 * @param  string $dateTime The date.
	 * @return string           The date formatted in RFC-822 format.
	 */
	public function dateTimeToRfc822( $dateTime ) {
		return date( 'D, d M Y H:i:s O', strtotime( $dateTime ) ); // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
	}

	/**
	 * Returns the timezone offset.
	 * We use the code from wp_timezone_string() which became available in WP 5.3+
	 *
	 * @since 4.0.0
	 *
	 * @return string The timezone offset.
	 */
	public function getTimeZoneOffset() {
		$timezoneString = get_option( 'timezone_string' );
		if ( $timezoneString ) {
			return $timezoneString;
		}

		$offset   = (float) get_option( 'gmt_offset' );
		$hours    = (int) $offset;
		$minutes  = ( $offset - $hours );
		$sign     = ( $offset < 0 ) ? '-' : '+';
		$absHour  = abs( $hours );
		$absMins  = abs( $minutes * 60 );
		$tzOffset = sprintf( '%s%02d:%02d', $sign, $absHour, $absMins );

		return $tzOffset;
	}

	/**
	 * Formats an amount of days, hours and minutes in ISO8601 duration format.
	 * This is used in our JSON schema to adhere to Google's standards.
	 *
	 * @since 4.2.5
	 *
	 * @param  integer|string $days    The days.
	 * @param  integer|string $hours   The hours.
	 * @param  integer|string $minutes The minutes.
	 * @return string                  The days, hours and minutes formatted in ISO8601 duration format.
	 */
	public function timeToIso8601DurationFormat( $days, $hours, $minutes ) {
		$duration = 'P';
		if ( $days ) {
			$duration .= $days . 'D';
		}

		$duration .= 'T';
		if ( $hours ) {
			$duration .= $hours . 'H';
		}

		if ( $minutes ) {
			$duration .= $minutes . 'M';
		}

		return $duration;
	}

	/**
	 * Returns a MySQL formatted date.
	 *
	 * @since 4.1.5
	 *
	 * @param  int|string   $time Any format accepted by strtotime.
	 * @return false|string       The MySQL formatted string.
	 */
	public function timeToMysql( $time ) {
		$time = is_string( $time ) ? strtotime( $time ) : $time;

		return date( 'Y-m-d H:i:s', $time ); // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
	}
}