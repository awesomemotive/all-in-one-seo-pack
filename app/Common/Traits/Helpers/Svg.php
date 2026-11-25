<?php
namespace AIOSEO\Plugin\Common\Traits\Helpers;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Contains SVG specific helper methods.
 *
 * @since 4.1.4
 */
trait Svg {
	/**
	 * Sanitizes a SVG string.
	 *
	 * @since 4.1.4
	 *
	 * @param  string $svgString The SVG to check.
	 * @return string            The sanitized SVG.
	 */
	public function escSvg( $svgString ) {
		if ( ! is_string( $svgString ) ) {
			return false;
		}

		$ksesDefaults = wp_kses_allowed_html( 'post' );

		$svgArgs = [
			'svg'   => [
				'class'           => true,
				'aria-hidden'     => true,
				'aria-labelledby' => true,
				'role'            => true,
				'xmlns'           => true,
				'width'           => true,
				'height'          => true,
				'viewbox'         => true, // <= Must be lower case!
			],
			'g'     => [ 'fill' => true ],
			'title' => [ 'title' => true ],
			'path'  => [
				'd'    => true,
				'fill' => true,
			]
		];

		return wp_kses( $svgString, array_merge( $ksesDefaults, $svgArgs ) );
	}

	/**
	 * Removes inline SVGs, whose `<title>`/`<desc>` text is never rendered as prose, from an HTML string.
	 *
	 * NOTE: Keep in sync with stripSvgs() in `src/vue/utils/html.js`.
	 *
	 * @since 5.0.6
	 *
	 * @param  string $html      The HTML.
	 * @param  bool   $scripting Whether `<noscript>` content is raw text, as in a browser with scripting on.
	 * @return string            The HTML without inline SVGs.
	 */
	public function stripInlineSvgs( $html, $scripting = true ) {
		$html = (string) $html;
		if ( false === stripos( $html, '<svg' ) ) {
			return $html;
		}

		// One linear pass over every comment and tag, so `<svg` inside a comment or an attribute value isn't read as a tag.
		// The whitespace is HTML's: PCRE's `\s` also matches a vertical tab, which doesn't end a tag name.
		$tokenPattern = '/<!--(?:-?>|.*?(?:--!?>|\z))|<!\[CDATA\[|<(\/?)([a-z][^\t\n\f\r \/<>]*+)((?:[^<>"\']++|"[^"]*+"|\'[^\']*+\')*+)>/is';

		// The HTML parser ends an SVG at these start tags (and at `</p>`/`</br>`), so the text after them is rendered.
		$breakoutTags = '/^(?:b|big|blockquote|body|br|center|code|dd|div|dl|dt|em|embed|h[1-6]|head|hr|i|img|li|listing|menu|meta|nobr|ol|p|pre'
			. '|ruby|s|small|span|strong|strike|sub|sup|table|tt|u|ul|var)$/';

		// Outside an SVG, the parser reads these elements' content as text up to their end tag, so a `<svg` in it isn't a tag.
		$rawTextTags = [ 'iframe', 'noembed', 'noframes', 'script', 'style', 'textarea', 'title', 'xmp' ];
		if ( $scripting ) {
			$rawTextTags[] = 'noscript';
		}

		$stack  = []; // Open svg tags (their offset) and the integration points inside them (false), where HTML tags don't end the SVG.
		$ranges = []; // [ start, end, replacement ] of each SVG to remove, in order.
		$record = function ( $start, $end, $replacement = '' ) use ( &$ranges ) {
			// Drop the ranges of SVGs nested in this one.
			while ( $ranges && end( $ranges )[0] >= $start ) {
				array_pop( $ranges );
			}
			$ranges[] = [ $start, $end, $replacement ];
		};

		$offset = 0;
		while ( preg_match( $tokenPattern, $html, $tag, PREG_OFFSET_CAPTURE, $offset ) ) {
			$start  = $tag[0][1];
			$end    = $start + strlen( $tag[0][0] );
			$offset = $end;
			if ( ! isset( $tag[2] ) ) {
				// In an SVG, a CDATA section is text up to its `]]>`, so a `</svg>` in it doesn't end the SVG.
				if ( $stack && '<![' === substr( $tag[0][0], 0, 3 ) ) {
					$cdataEnd = strpos( $html, ']]>', $end );
					$offset   = false === $cdataEnd ? strlen( $html ) : $cdataEnd + 3;
				}

				continue; // A comment.
			}

			$closer = '/' === $tag[1][0];
			$name   = strtolower( $tag[2][0] );
			$inSvg  = $stack && false !== end( $stack );
			// A `/` that ends an unquoted attribute value belongs to the value, not to a self-closing tag.
			$selfClosing = '/' === substr( $tag[3][0], -1 ) && ! preg_match( '/=[\t\n\f\r ]*+[^\t\n\f\r "\']*+$/', $tag[3][0] );

			if ( ! $closer && ! $inSvg && 'plaintext' === $name ) {
				break; // The rest of the content is text.
			}

			if ( ! $closer && ! $inSvg && in_array( $name, $rawTextTags, true ) ) {
				$hasEndTag  = preg_match( '/<\/' . $name . '(?=[\t\n\f\r \/>])[^>]*+>?/i', $html, $endTag, PREG_OFFSET_CAPTURE, $end );
				$contentEnd = $hasEndTag ? $endTag[0][1] : strlen( $html );
				$offset     = $hasEndTag ? $contentEnd + strlen( $endTag[0][0] ) : $contentEnd;

				// Without scripting a `<noscript>` holds HTML, so its own SVGs are stripped, but none can run past its end tag.
				if ( 'noscript' === $name ) {
					$content  = substr( $html, $end, $contentEnd - $end );
					$stripped = $this->stripInlineSvgs( $content, false );
					if ( $stripped !== $content ) {
						$record( $end, $contentEnd, $stripped );
					}
				}

				continue;
			}

			if ( 'svg' === $name ) {
				if ( ! $closer && ! $selfClosing ) {
					$stack[] = $start;
				} elseif ( ! $closer && ! $stack ) {
					$record( $start, $end );
				} elseif ( $closer && $inSvg ) {
					// Inside an integration point the parser ignores `</svg>`.
					$record( array_pop( $stack ), $end );
				}

				continue;
			}

			if ( in_array( $name, [ 'title', 'desc', 'foreignobject' ], true ) ) {
				if ( ! $closer && ! $selfClosing && $inSvg ) {
					$stack[] = false;
				} elseif ( $closer && $stack && false === end( $stack ) ) {
					array_pop( $stack );
				}

				continue;
			}

			$breaksOut = $closer ? in_array( $name, [ 'p', 'br' ], true ) : preg_match( $breakoutTags, $name );
			// A `font` start tag breaks out only with a `color`, `face` or `size` attribute.
			if ( ! $closer && 'font' === $name && $inSvg ) {
				preg_match_all( '/([^\t\n\f\r \/>][^\t\n\f\r \/>=]*+)(?:[\t\n\f\r ]*+=[\t\n\f\r ]*+(?:"[^"]*+"|\'[^\']*+\'|[^\t\n\f\r >]*+))?/', $tag[3][0], $attributes );
				$breaksOut = (bool) array_intersect( array_map( 'strtolower', $attributes[1] ), [ 'color', 'face', 'size' ] );
			}

			if ( $breaksOut && $inSvg ) {
				do {
					$svgStart = array_pop( $stack );
				} while ( $stack && false !== end( $stack ) );
				$record( $svgStart, $start );
			}
		}

		$stripped = '';
		$offset   = 0;
		foreach ( $ranges as $range ) {
			$stripped .= substr( $html, $offset, $range[0] - $offset ) . $range[2];
			$offset    = $range[1];
		}

		return $stripped . substr( $html, $offset );
	}
}