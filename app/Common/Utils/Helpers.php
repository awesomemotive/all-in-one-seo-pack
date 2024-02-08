<?php
namespace AIOSEO\Plugin\Common\Utils;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AIOSEO\Plugin\Common\Traits\Helpers as TraitHelpers;

/**
 * Contains helper functions
 *
 * @since 4.0.0
 */
class Helpers {
	use TraitHelpers\Api;
	use TraitHelpers\Arrays;
	use TraitHelpers\Constants;
	use TraitHelpers\Deprecated;
	use TraitHelpers\DateTime;
	use TraitHelpers\Language;
	use TraitHelpers\PostType;
	use TraitHelpers\Request;
	use TraitHelpers\Shortcodes;
	use TraitHelpers\Strings;
	use TraitHelpers\Svg;
	use TraitHelpers\ThirdParty;
	use TraitHelpers\Url;
	use TraitHelpers\Vue;
	use TraitHelpers\Wp;
	use TraitHelpers\WpContext;
	use TraitHelpers\WpMultisite;
	use TraitHelpers\WpUri;

	/**
	 * Generate a UTM URL from the url and medium/content passed in.
	 *
	 * @since 4.0.0
	 *
	 * @param  string      $url     The URL to parse.
	 * @param  string      $medium  The UTM medium parameter.
	 * @param  string|null $content The UTM content parameter or null.
	 * @param  boolean     $esc     Whether or not to escape the URL.
	 * @return string               The new URL.
	 */
	public function utmUrl( $url, $medium, $content = null, $esc = true ) {
		// First, remove any existing utm parameters on the URL.
		$url = remove_query_arg( [
			'utm_source',
			'utm_medium',
			'utm_campaign',
			'utm_content'
		], $url );

		// Generate the new arguments.
		$args = [
			'utm_source'   => 'WordPress',
			'utm_campaign' => aioseo()->pro ? 'proplugin' : 'liteplugin',
			'utm_medium'   => $medium
		];

		// Content is not used by default.
		if ( $content ) {
			$args['utm_content'] = $content;
		}

		// Return the new URL.
		$url = add_query_arg( $args, $url );

		return $esc ? esc_url( $url ) : $url;
	}

	/**
	 * Checks if we are in a dev environment or not.
	 *
	 * @since 4.1.0
	 *
	 * @return boolean True if we are, false if not.
	 */
	public function isDev() {
		return aioseo()->isDev || isset( $_REQUEST['aioseo-dev'] ); // phpcs:ignore HM.Security.NonceVerification.Recommended
	}

	/**
	 * Checks if the server is running on Apache.
	 *
	 * @since 4.0.0
	 *
	 * @return boolean Whether or not it is on apache.
	 */
	public function isApache() {
		if ( ! isset( $_SERVER['SERVER_SOFTWARE'] ) ) {
			return false;
		}

		return stripos( sanitize_text_field( wp_unslash( $_SERVER['SERVER_SOFTWARE'] ) ), 'apache' ) !== false;
	}

	/**
	 * Checks if the server is running on nginx.
	 *
	 * @since 4.0.0
	 *
	 * @return bool Whether or not it is on nginx.
	 */
	public function isNginx() {
		if ( ! isset( $_SERVER['SERVER_SOFTWARE'] ) ) {
			return false;
		}

		$server = sanitize_text_field( wp_unslash( $_SERVER['SERVER_SOFTWARE'] ) );

		if (
			false !== stripos( $server, 'Flywheel' ) ||
			false !== stripos( $server, 'nginx' )
		) {
			return true;
		}

		return false;
	}

	/**
	 * Checks if the server is running on LiteSpeed.
	 *
	 * @since 4.5.3
	 *
	 * @return bool Whether it is on LiteSpeed.
	 */
	public function isLiteSpeed() {
		if ( ! isset( $_SERVER['SERVER_SOFTWARE'] ) ) {
			return false;
		}

		$server = strtolower( sanitize_text_field( wp_unslash( $_SERVER['SERVER_SOFTWARE'] ) ) );

		return false !== stripos( $server, 'litespeed' );
	}

	/**
	 * Returns the server name: Apache, nginx or LiteSpeed.
	 *
	 * @since 4.5.3
	 *
	 * @return string The server name. An empty string if it's unknown.
	 */
	public function getServerName() {
		if ( aioseo()->helpers->isApache() ) {
			return 'apache';
		}

		if ( aioseo()->helpers->isNginx() ) {
			return 'nginx';
		}

		if ( aioseo()->helpers->isLiteSpeed() ) {
			return 'litespeed';
		}

		return '';
	}

	/**
	 * Validate IP addresses.
	 *
	 * @since 4.0.0
	 *
	 * @param  string  $ip The IP address to validate.
	 * @return boolean     If the IP address is valid or not.
	 */
	public function validateIp( $ip ) {
		if ( filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 ) ) {
			return true;
		}

		if ( filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6 ) ) {
			return true;
		}

		// Doesn't seem to be a valid IP.
		return false;
	}

	/**
	 * Convert bytes to readable format.
	 *
	 * @since 4.0.0
	 *
	 * @param  integer $bytes The size of the file.
	 * @return string         The size as a string.
	 */
	public function convertFileSize( $bytes ) {
		if ( empty( $bytes ) ) {
			return [
				'original' => 0,
				'readable' => '0 B'
			];
		}
		$i = floor( log( $bytes ) / log( 1024 ) );
		$sizes = [ 'B', 'KB', 'MB', 'GB', 'TB' ];

		return [
			'original' => $bytes,
			'readable' => sprintf( '%.02F', $bytes / pow( 1024, $i ) ) * 1 . ' ' . $sizes[ $i ]
		];
	}

	/**
	 * Sanitizes a given option value before we store it in the DB.
	 *
	 * Used by the migration and importer classes.
	 *
	 * @since 4.0.0
	 *
	 * @param  mixed $value The value.
	 * @return mixed $value The sanitized value.
	 */
	public function sanitizeOption( $value ) {
		switch ( gettype( $value ) ) {
			case 'boolean':
				return (bool) $value;
			case 'string':
				$value = aioseo()->helpers->decodeHtmlEntities( $value );

				return aioseo()->helpers->encodeOutputHtml( wp_strip_all_tags( wp_check_invalid_utf8( trim( $value ) ) ) );
			case 'integer':
				return intval( $value );
			case 'double':
				return floatval( $value );
			case 'array':
				$sanitized = [];
				foreach ( (array) $value as $child ) {
					$sanitized[] = aioseo()->helpers->sanitizeOption( $child );
				}

				return $sanitized;
			default:
				return false;
		}
	}

	/**
	 * Checks if the given string is serialized, and if so, unserializes it.
	 * If the serialized string contains an object, we abort to prevent PHP object injection.
	 *
	 * @since 4.1.0.2
	 *
	 * @param  string       $string The string.
	 * @return string|array         The string or unserialized data.
	 */
	public function maybeUnserialize( $string ) {
		if ( ! is_string( $string ) ) {
			return $string;
		}

		$string = trim( $string );
		if ( is_serialized( $string ) && ! $this->stringContains( $string, 'O:' ) ) {
			// We want to add extra hardening for PHP versions greater than 5.6.
			return version_compare( PHP_VERSION, '7.0', '<' )
				? @unserialize( $string )
				: @unserialize( $string, [ 'allowed_classes' => false ] ); // phpcs:disable PHPCompatibility.FunctionUse.NewFunctionParameters.unserialize_optionsFound
		}

		return $string;
	}

	/**
	 * Returns a deep clone of the given object.
	 * The built-in PHP clone KW provides a shallow clone. This method returns a deep clone that also clones nested object properties.
	 * You can use this method to sever the reference to nested objects.
	 *
	 * @since 4.4.7
	 *
	 * @return object The cloned object.
	 */
	public function deepClone( $object ) {
		return unserialize( serialize( $object ) );
	}

	/**
	 * Sanitizes a given variable
	 *
	 * @since 4.5.6
	 *
	 * @param  mixed $variable             The variable.
	 * @param  bool  $preserveHtml         Whether or not to preserve HTML for ALL fields.
	 * @param  array $fieldsToPreserveHtml Specific fields to preserve HTML for.
	 * @param  string $fieldName           The name of the current field (when looping over a list).
	 * @return mixed                       The sanitized variable.
	 */
	public function sanitize( $variable, $preserveHtml = false, $fieldsToPreserveHtml = [], $fieldName = '' ) {
		$type = gettype( $variable );
		switch ( $type ) {
			case 'boolean':
				return (bool) $variable;
			case 'string':
				if ( $preserveHtml || in_array( $fieldName, $fieldsToPreserveHtml, true ) ) {
					return aioseo()->helpers->decodeHtmlEntities( sanitize_text_field( htmlspecialchars( $variable, ENT_NOQUOTES, 'UTF-8' ) ) );
				}

				return sanitize_text_field( $variable );
			case 'integer':
				return intval( $variable );
			case 'float':
			case 'double':
				return floatval( $variable );
			case 'array':
				$array = [];
				foreach ( (array) $variable as $k => $v ) {
					$array[ $k ] = $this->sanitize( $v, $preserveHtml, $fieldsToPreserveHtml, $k );
				}

				return $array;
			default:
				return false;
		}
	}

	/**
	 * Return the version number with a filter to enable users to hide the version.
	 *
	 * @since 4.3.7
	 *
	 * @return string The current version or empty if the filter is active. Using ?aioseo-dev will override the filter.
	 */
	public function getAioseoVersion() {
		$version = aioseo()->version;

		if ( ! $this->isDev() && apply_filters( 'aioseo_hide_version_number', false ) ) {
			$version = '';
		}

		return $version;
	}
}