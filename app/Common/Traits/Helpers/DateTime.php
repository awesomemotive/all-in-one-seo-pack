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
	 * Retrieves the timezone offset in seconds.
	 *
	 * @since   4.0.0
	 * @version 4.7.2 Returns the actual timezone offset.
	 *
	 * @return int The timezone offset in seconds.
	 */
	public function getTimeZoneOffset() {
		try {
			$timezone = get_option( 'timezone_string' );
			if ( $timezone ) {
				$timezone_object = new \DateTimeZone( $timezone ); // phpcs:ignore Squiz.NamingConventions.ValidVariableName

				return $timezone_object->getOffset( new \DateTime( 'now' ) ); // phpcs:ignore Squiz.NamingConventions.ValidVariableName
			}
		} catch ( \Exception $e ) {
			// Do nothing.
		}

		return intval( get_option( 'gmt_offset', 0 ) ) * HOUR_IN_SECONDS;
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

	/**
	 * Generates a random (yet unique per identifier) time offset based on a site identifier.
	 *
	 * @since 4.7.9
	 *
	 * @param  string $identifier       Data such as the site URL, site ID, or a combination of both to serve as the seed for generating a random time offset.
	 * @param  int    $maxOffsetMinutes The range for the random offset in minutes.
	 * @return int                      The random (yet unique per identifier) time offset in minutes.
	 */
	public function generateRandomTimeOffset( $identifier, $maxOffsetMinutes ) {
		$hash = md5( strval( $identifier ) );

		// Convert part of the hash to an integer.
		$hashInteger = hexdec( substr( $hash, 0, 8 ) );

		return $hashInteger % $maxOffsetMinutes;
	}
}