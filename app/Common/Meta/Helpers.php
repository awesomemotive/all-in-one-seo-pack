<?php
namespace AIOSEO\Plugin\Common\Meta;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Contains helper methods for the title/description classes.
 *
 * @since 4.1.2
 */
class Helpers {
	use Traits\Helpers\BuddyPress;

	/**
	 * The name of the class where this instance is constructed.
	 *
	 * @since 4.1.2
	 *
	 * @param string $name The name of the class. Either "title" or "description".
	 */
	private $name;

	/**
	 * Supported filters we can run after preparing the value.
	 *
	 * @since 4.1.2
	 *
	 * @var array
	 */
	private $supportedFilters = [
		'title'       => 'aioseo_title',
		'description' => 'aioseo_description'
	];

	/**
	 * Class constructor.
	 *
	 * @since 4.1.2
	 *
	 * @param string $name The name of the class where this instance is constructed.
	 */
	public function __construct( $name ) {
		$this->name = $name;
	}

	/**
	 * Sanitizes the title/description.
	 *
	 * @since 4.1.2
	 *
	 * @param  string   $value       The value.
	 * @param  int|bool $objectId    The post/term ID.
	 * @param  bool     $replaceTags Whether the smart tags should be replaced.
	 * @return string                The sanitized value.
	 */
	public function sanitize( $value, $objectId = false, $replaceTags = false ) {
		$value = $replaceTags ? $value : aioseo()->tags->replaceTags( $value, $objectId );
		$value = aioseo()->helpers->doShortcodes( $value );

		$value = aioseo()->helpers->decodeHtmlEntities( $value );
		$value = $this->encodeExceptions( $value );
		$value = wp_strip_all_tags( strip_shortcodes( $value ) );
		// Because we encoded the exceptions, we need to decode them again first to prevent double encoding later down the line.
		$value = aioseo()->helpers->decodeHtmlEntities( $value );

		// Trim internal and external whitespace.
		$value = preg_replace( '/[\s]+/u', ' ', (string) trim( $value ) );

		return aioseo()->helpers->internationalize( $value );
	}

	/**
	 * Prepares the title/description before returning it.
	 *
	 * @since 4.1.2
	 *
	 * @param  string   $value       The value.
	 * @param  int|bool $objectId    The post/term ID.
	 * @param  bool     $replaceTags Whether the smart tags should be replaced.
	 * @return string                The sanitized value.
	 */
	public function prepare( $value, $objectId = false, $replaceTags = false ) {
		if (
			! empty( $value ) &&
			! is_admin() &&
			1 < aioseo()->helpers->getPageNumber()
		) {
			$value .= '&nbsp;' . trim( aioseo()->options->searchAppearance->advanced->pagedFormat );
		}

		$value = $replaceTags ? $value : aioseo()->tags->replaceTags( $value, $objectId );
		$value = apply_filters( $this->supportedFilters[ $this->name ], $value );

		return $this->sanitize( $value, $objectId, $replaceTags );
	}

	/**
	 * Encodes a number of exceptions before we strip tags.
	 * We need this function to allow certain character (combinations) in the title/description.
	 *
	 * @since 4.1.1
	 *
	 * @param  string $string The string.
	 * @return string $string The string with exceptions encoded.
	 */
	public function encodeExceptions( $string ) {
		$exceptions = [ '<3' ];
		foreach ( $exceptions as $exception ) {
			$string = preg_replace( "/$exception/", aioseo()->helpers->encodeOutputHtml( $exception ), (string) $string );
		}

		return $string;
	}
}