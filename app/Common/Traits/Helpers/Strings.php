<?php
namespace AIOSEO\Plugin\Common\Traits\Helpers;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Contains string specific helper methods.
 *
 * @since 4.0.13
 */
trait Strings {
	/**
	 * Convert to snake case.
	 *
	 * @since 4.0.0
	 *
	 * @param  string $string The string to convert.
	 * @return string         The converted string.
	 */
	public function toSnakeCase( $string ) {
		$string[0] = strtolower( $string[0] );

		return preg_replace_callback( '/([A-Z])/', function ( $value ) {
			return '_' . strtolower( $value[1] );
		}, $string );
	}

	/**
	 * Convert to camel case.
	 *
	 * @since 4.0.0
	 *
	 * @param  string $string     The string to convert.
	 * @param  bool   $capitalize Whether or not to capitalize the first letter.
	 * @return string             The converted string.
	 */
	public function toCamelCase( $string, $capitalize = false ) {
		$string[0] = strtolower( $string[0] );
		if ( $capitalize ) {
			$string[0] = strtoupper( $string[0] );
		}

		return preg_replace_callback( '/_([a-z0-9])/', function ( $value ) {
			return strtoupper( $value[1] );
		}, $string );
	}

	/**
	 * Converts kebab case to camel case.
	 *
	 * @since 4.0.0
	 *
	 * @param  string $string                   The string to convert.
	 * @param  bool   $capitalizeFirstCharacter Whether to capitalize the first letter.
	 * @return string                           The converted string.
	 */
	public function dashesToCamelCase( $string, $capitalizeFirstCharacter = false ) {
		$string = str_replace( ' ', '', ucwords( str_replace( '-', ' ', $string ) ) );
		if ( ! $capitalizeFirstCharacter ) {
			$string[0] = strtolower( $string[0] );
		}

		return $string;
	}

	/**
	 * Truncates a given string.
	 *
	 * @since 4.0.0
	 *
	 * @param  string  $string             The string.
	 * @param  int     $maxCharacters      The max. amount of characters.
	 * @param  boolean $shouldHaveEllipsis Whether the string should have a trailing ellipsis (defaults to true).
	 * @return string                      The string.
	 */
	public function truncate( $string, $maxCharacters, $shouldHaveEllipsis = true ) {
		$length       = strlen( $string );
		$excessLength = $length - $maxCharacters;
		if ( 0 < $excessLength ) {
			// If the string is longer than 65535 characters, we first need to shorten it due to the character limit of the regex pattern quantifier.
			if ( 65535 < $length ) {
				$string = substr( $string, 0, 65534 );
			}
			$string = preg_replace( "#[^\pZ\pP]*.{{$excessLength}}$#", '', (string) $string );
			if ( $shouldHaveEllipsis ) {
				$string = $string . ' ...';
			}
		}

		return $string;
	}

	/**
	 * Escapes special regex characters.
	 *
	 * @since 4.0.5
	 *
	 * @param  string $string    The string.
	 * @param  string $delimiter The delimiter character.
	 * @return string            The escaped string.
	 */
	public function escapeRegex( $string, $delimiter = '/' ) {
		static $escapeRegex = [];
		if ( isset( $escapeRegex[ $string ] ) ) {
			return $escapeRegex[ $string ];
		}
		$escapeRegex[ $string ] = preg_quote( (string) $string, $delimiter );

		return $escapeRegex[ $string ];
	}

	/**
	 * Escapes special regex characters inside the replacement string.
	 *
	 * @since 4.0.7
	 *
	 * @param  string $string The string.
	 * @return string         The escaped string.
	 */
	public function escapeRegexReplacement( $string ) {
		static $escapeRegexReplacement = [];
		if ( isset( $escapeRegexReplacement[ $string ] ) ) {
			return $escapeRegexReplacement[ $string ];
		}

		$escapeRegexReplacement[ $string ] = str_replace( '$', '\$', $string );

		return $escapeRegexReplacement[ $string ];
	}

	/**
	 * preg_replace but with the replacement escaped.
	 *
	 * @since 4.0.10
	 *
	 * @param  string $pattern     The pattern to search for.
	 * @param  string $replacement The replacement string.
	 * @param  string $subject     The subject to search in.
	 * @return string              The subject with matches replaced.
	 */
	public function pregReplace( $pattern, $replacement, $subject ) {
		if ( ! $subject ) {
			return $subject;
		}

		$key = $pattern . $replacement . $subject;

		static $pregReplace = [];
		if ( isset( $pregReplace[ $key ] ) ) {
			return $pregReplace[ $key ];
		}

		// TODO: In the future, we should consider escaping the search pattern as well.
		// We can use the following pattern for this - (?<!\\)([\/.^$*+?|()[{}\]]{1})
		// The pattern above will only escape special characters if they're not escaped yet, which makes it compatible with all our patterns that are already escaped.
		// The caveat is that we'd need to first trim off slash delimiters and add them back later - otherwise they'd be escaped as well.

		$replacement         = $this->escapeRegexReplacement( $replacement );
		$pregReplace[ $key ] = preg_replace( $pattern, $replacement, (string) $subject );

		return $pregReplace[ $key ];
	}

	/**
	 * Returns string after converting it to lowercase.
	 *
	 * @since 4.0.13
	 *
	 * @param  string $string The original string.
	 * @return string         The string converted to lowercase.
	 */
	public function toLowerCase( $string ) {
		static $lowerCased = [];
		if ( isset( $lowerCased[ $string ] ) ) {
			return $lowerCased[ $string ];
		}
		$lowerCased[ $string ] = function_exists( 'mb_strtolower' ) ? mb_strtolower( $string, $this->getCharset() ) : strtolower( $string );

		return $lowerCased[ $string ];
	}

	/**
	 * Returns the index of a substring in a string.
	 *
	 * @since 4.1.6
	 *
	 * @param  string   $stack  The stack.
	 * @param  string   $needle The needle.
	 * @param  int      $offset The offset.
	 * @return int|bool         The index where the string starts or false if it does not exist.
	 */
	public function stringIndex( $stack, $needle, $offset = 0 ) {
		$key = $stack . $needle . $offset;

		static $stringIndex = [];
		if ( isset( $stringIndex[ $key ] ) ) {
			return $stringIndex[ $key ];
		}

		$stringIndex[ $key ] = function_exists( 'mb_strpos' ) ? mb_strpos( $stack, $needle, $offset, $this->getCharset() ) : strpos( $stack, $needle, $offset );

		return $stringIndex[ $key ];
	}

	/**
	 * Checks if the given string contains the given substring.
	 *
	 * @since 4.1.0.2
	 *
	 * @param  string   $stack  The stack.
	 * @param  string   $needle The needle.
	 * @param  int      $offset The offset.
	 * @return bool             Whether the substring occurs in the main string.
	 */
	public function stringContains( $stack, $needle, $offset = 0 ) {
		$key = $stack . $needle . $offset;

		static $stringContains = [];
		if ( isset( $stringContains[ $key ] ) ) {
			return $stringContains[ $key ];
		}

		$stringContains[ $key ] = false !== $this->stringIndex( $stack, $needle, $offset );

		return $stringContains[ $key ];
	}

	/**
	 * Check if a string is JSON encoded or not.
	 *
	 * @since 4.1.2
	 *
	 * @param  mixed $string The string to check.
	 * @return bool          True if it is JSON or false if not.
	 */
	public function isJsonString( $string ) {
		if ( ! is_string( $string ) ) {
			return false;
		}

		json_decode( $string );

		// Return a boolean whether or not the last error matches.
		return json_last_error() === JSON_ERROR_NONE;
	}

	/**
	 * Strips punctuation from a given string.
	 *
	 * @since 4.0.0
	 *
	 * @param  string $string           The string.
	 * @param  array  $charactersToKeep The characters that can't be stripped (optional).
	 * @return string                   The string without punctuation.
	 */
	public function stripPunctuation( $string, $charactersToKeep = [] ) {
		$characterRegexPattern = '';
		if ( ! empty( $charactersToKeep ) ) {
			$characterString       = implode( '', $charactersToKeep );
			$characterRegexPattern = "(?![$characterString])";
		}

		$string = aioseo()->helpers->decodeHtmlEntities( $string );
		$string = preg_replace( "/{$characterRegexPattern}[\p{P}\d+]/u", '', (string) $string );
		$string = aioseo()->helpers->encodeOutputHtml( $string );

		// Trim both internal and external whitespace.
		return preg_replace( '/\s\s+/u', ' ', (string) trim( $string ) );
	}

	/**
	 * Returns the string after it is encoded with htmlspecialchars().
	 *
	 * @since 4.0.0
	 *
	 * @param  string $string The string to encode.
	 * @return string         The encoded string.
	 */
	public function encodeOutputHtml( $string ) {
		if ( ! is_string( $string ) ) {
			return '';
		}

		return htmlspecialchars( $string, ENT_COMPAT | ENT_HTML401, $this->getCharset(), false );
	}

	/**
	 * Returns the string after all HTML entities have been decoded.
	 *
	 * @since 4.0.0
	 *
	 * @param  string $string The string to decode.
	 * @return string         The decoded string.
	 */
	public function decodeHtmlEntities( $string ) {
		static $decodeHtmlEntities = [];
		if ( isset( $decodeHtmlEntities[ $string ] ) ) {
			return $decodeHtmlEntities[ $string ];
		}

		// We must manually decode non-breaking spaces since html_entity_decode doesn't do this.
		$string                        = $this->pregReplace( '/&nbsp;/', ' ', $string );
		$decodeHtmlEntities[ $string ] = html_entity_decode( (string) $string, ENT_QUOTES );

		return $decodeHtmlEntities[ $string ];
	}

	/**
	 * Returns the string with script tags stripped.
	 *
	 * @since 4.0.0
	 *
	 * @param  string $string The string.
	 * @return string         The modified string.
	 */
	public function stripScriptTags( $string ) {
		static $stripScriptTags = [];
		if ( isset( $stripScriptTags[ $string ] ) ) {
			return $stripScriptTags[ $string ];
		}

		$stripScriptTags[ $string ] = $this->pregReplace( '/<script(.*?)>(.*?)<\/script>/is', '', $string );

		return $stripScriptTags[ $string ];
	}

	/**
	 * Returns the string with incomplete HTML tags stripped.
	 * Incomplete tags are not unopened/unclosed pairs but rather single tags that aren't properly formed.
	 * e.g. <a href='something'
	 * e.g. href='something' >
	 *
	 * @since 4.1.6
	 *
	 * @param  string $string The string.
	 * @return string         The modified string.
	 */
	public function stripIncompleteHtmlTags( $string ) {
		static $stripIncompleteHtmlTags = [];
		if ( isset( $stripIncompleteHtmlTags[ $string ] ) ) {
			return $stripIncompleteHtmlTags[ $string ];
		}

		$stripIncompleteHtmlTags[ $string ] = $this->pregReplace( '/(^(?!<).*?(\/>)|<[^>]*?(?!\/>)$)/is', '', $string );

		return $stripIncompleteHtmlTags[ $string ];
	}


	/**
	 * Returns the given JSON formatted data tags as a comma separated list with their values instead.
	 *
	 * @since 4.1.0
	 *
	 * @param  string|array $tags The Array or JSON formatted data tags.
	 * @return string             The comma separated values.
	 */
	public function jsonTagsToCommaSeparatedList( $tags ) {
		$tags = is_string( $tags ) ? json_decode( $tags ) : $tags;

		$values = [];
		foreach ( $tags as $k => $tag ) {
			$values[ $k ] = is_object( $tag ) ? $tag->value : $tag['value'];
		}

		return implode( ',', $values );
	}

	/**
	 * Returns the character length of the given string.
	 *
	 * @since 4.1.6
	 *
	 * @param  string $string The string.
	 * @return int            The string length.
	 */
	public function stringLength( $string ) {
		static $stringLength = [];
		if ( isset( $stringLength[ $string ] ) ) {
			return $stringLength[ $string ];
		}

		$stringLength[ $string ] = function_exists( 'mb_strlen' ) ? mb_strlen( $string, $this->getCharset() ) : strlen( $string );

		return $stringLength[ $string ];
	}

	/**
	 * Returns the word count of the given string.
	 *
	 * @since 4.1.6
	 *
	 * @param  string $string The string.
	 * @return int            The word count.
	 */
	public function stringWordCount( $string ) {
		static $stringWordCount = [];
		if ( isset( $stringWordCount[ $string ] ) ) {
			return $stringWordCount[ $string ];
		}

		$stringWordCount[ $string ] = str_word_count( $string );

		return $stringWordCount[ $string ];
	}

	/**
	 * Explodes the given string into an array.
	 *
	 * @since 4.1.6
	 *
	 * @param  string $delimiter The delimiter.
	 * @param  string $string    The string.
	 * @return array             The exploded words.
	 */
	public function explode( $delimiter, $string ) {
		$key = $delimiter . $string;

		static $exploded = [];
		if ( isset( $exploded[ $key ] ) ) {
			return $exploded[ $key ];
		}

		$exploded[ $key ] = explode( $delimiter, $string );

		return $exploded[ $key ];
	}

	/**
	 * Implodes an array into a WHEREIN clause useable string.
	 *
	 * @since 4.1.6
	 *
	 * @param  array  $array       The array.
	 * @param  bool   $outerQuotes Whether outer quotes should be added.
	 * @return string              The imploded array.
	 */
	public function implodeWhereIn( $array, $outerQuotes = false ) {
		// Reset the keys first in case there is no 0 index.
		$array = array_values( $array );

		if ( ! isset( $array[0] ) ) {
			return '';
		}

		if ( is_numeric( $array[0] ) ) {
			return implode( ', ', $array );
		}

		return $outerQuotes ? "'" . implode( "', '", $array ) . "'" : implode( "', '", $array );
	}

	/**
	 * Returns an imploded string of placeholders for usage in a WPDB prepare statement.
	 *
	 * @since 4.1.9
	 *
	 * @param  array  $array       The array.
	 * @param  string $placeholder The placeholder (e.g. "%s" or "%d").
	 * @return string              The imploded string with placeholders.
	 */
	public function implodePlaceholders( $array, $placeholder = '%s' ) {
		return implode( ', ', array_fill( 0, count( $array ), $placeholder ) );
	}

	/**
	 * Verifies that a string is indeed a valid regular expression.
	 *
	 * @since 4.2.1
	 *
	 * @return boolean True if the string is a valid regular expression.
	 */
	public function isValidRegex( $pattern ) {
		// Set a custom error handler to prevent throwing errors on a bad Regular Expression.
		set_error_handler( function() {}, E_WARNING ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_set_error_handler

		$isValid = true;

		if ( false === preg_match( $pattern, '' ) ) {
			$isValid = false;
		}

		// Restore the error handler.
		restore_error_handler();

		return $isValid;
	}

	/**
	 * Removes the leading slash(es) from a string.
	 *
	 * @since 4.2.3
	 *
	 * @param  string $string The string.
	 * @return string         The modified string.
	 */
	public function unleadingSlashIt( $string ) {
		return ltrim( $string, '/' );
	}

	/**
	 * Convert the case of the given string.
	 *
	 * @since 4.2.4
	 *
	 * @param  string $string The string.
	 * @param  string $type   The casing ("lower", "title", "sentence").
	 * @return string         The converted string.
	 */
	public function convertCase( $string, $type ) {
		switch ( $type ) {
			case 'lower':
				return strtolower( $string );
			case 'title':
				return $this->toTitleCase( $string );
			case 'sentence':
				return $this->toSentenceCase( $string );
			default:
				return $string;
		}
	}

	/**
	 * Converts the given string to title case.
	 *
	 * @since 4.2.4
	 *
	 * @param  string $string The string.
	 * @return string         The converted string.
	 */
	public function toTitleCase( $string ) {
		// List of common English words that aren't typically modified.
		$exceptions = apply_filters( 'aioseo_title_case_exceptions', [
			'of',
			'a',
			'the',
			'and',
			'an',
			'or',
			'nor',
			'but',
			'is',
			'if',
			'then',
			'else',
			'when',
			'at',
			'from',
			'by',
			'on',
			'off',
			'for',
			'in',
			'out',
			'over',
			'to',
			'into',
			'with'
		] );

		$words = explode( ' ', strtolower( $string ) );

		foreach ( $words as $k => $word ) {
			if ( ! in_array( $word, $exceptions, true ) ) {
				$words[ $k ] = ucfirst( $word );
			}
		}

		$string = implode( ' ', $words );

		return $string;
	}

	/**
	 * Converts the given string to sentence case.
	 *
	 * @since 4.2.4
	 *
	 * @param  string $string The string.
	 * @return string         The converted string.
	 */
	public function toSentenceCase( $string ) {
		$phrases = preg_split( '/([.?!]+)/', (string) $string, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE );

		$convertedString = '';
		foreach ( $phrases as $index => $sentence ) {
			$convertedString .= ( $index & 1 ) === 0 ? ucfirst( strtolower( trim( $sentence ) ) ) : $sentence . ' ';
		}

		return trim( $convertedString );
	}

	/**
	 * Returns the substring with a given start index and length.
	 *
	 * @since 4.2.5
	 *
	 * @param  string $string     The string.
	 * @param  int    $startIndex The start index.
	 * @param  int    $length     The length.
	 * @return string             The substring.
	 */
	public function substring( $string, $startIndex, $length ) {
		return function_exists( 'mb_substr' ) ? mb_substr( $string, $startIndex, $length, $this->getCharset() ) : substr( $string, $startIndex, $length );
	}

	/**
	 * Strips emoji characters from a given string.
	 *
	 * @since 4.7.3
	 *
	 * @param  string $string The string.
	 * @return string         The string without emoji characters.
	 */
	public function stripEmoji( $string ) {
		// First, decode HTML entities to convert them to actual Unicode characters.
		$string = $this->decodeHtmlEntities( $string );

		// Pattern to match emoji characters.
		$emojiPattern = '/[\x{1F600}-\x{1F64F}' . // Emoticons
						'\x{1F300}-\x{1F5FF}' . // Misc Symbols and Pictographs
						'\x{1F680}-\x{1F6FF}' . // Transport and Map Symbols
						'\x{1F1E0}-\x{1F1FF}' . // Flags (iOS)
						'\x{2600}-\x{26FF}' . // Misc symbols
						'\x{2700}-\x{27BF}' . // Dingbats
						'\x{FE00}-\x{FE0F}' . // Variation Selectors
						'\x{1F900}-\x{1F9FF}' . // Supplemental Symbols and Pictographs
						']/u';

		$filteredString = preg_replace( $emojiPattern, '', (string) $string );

		// Re-encode special characters to HTML entities.
		return $this->encodeOutputHtml( $filteredString );
	}

	/**
	 * Creates a sha1 hash from the given arguments.
	 *
	 * @since 4.7.8
	 *
	 * @param  mixed  ...$args The arguments to create a sha1 hash from.
	 * @return string          The sha1 hash.
	 */
	public function createHash( ...$args ) {
		return sha1( wp_json_encode( $args ) );
	}
}