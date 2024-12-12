<?php
namespace AIOSEO\Plugin\Common\Main;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Document Title class.
 *
 * @since 4.3.9
 */
class Title {
	/**
	 * Keeps the buffer level.
	 *
	 * @since 4.3.9
	 *
	 * @var int
	 */
	private $bufferLevel = 0;

	/**
	 * Starts the output buffering.
	 *
	 * @since   4.3.2
	 * @version 4.3.9
	 *
	 * @return void
	 */
	public function startOutputBuffering() {
		ob_start();

		$this->bufferLevel = ob_get_level();
	}

	/**
	 * Ends the output buffering.
	 *
	 * @since   4.3.2
	 * @version 4.3.9
	 *
	 * @return void
	 */
	public function endOutputBuffering() {
		// Bail if our code didn't start the output buffering at all.
		if ( 0 === $this->bufferLevel ) {
			return;
		}

		/**
		 * In case the current buffer level is different from the one we kept earlier, then: either a plugin started a new buffer or ended our buffer earlier.
		 * If that's the case, we can't properly rewrite the document title anymore since we don't know what buffer content we'd parse below.
		 * In order to avoid conflicts/errors (blank/broken pages), we just bail.
		 * If we bail, the page won't have the title set by AIOSEO, but this can be fixed if the active theme starts supporting the "title-tag" feature {@link https://codex.wordpress.org/Title_Tag}.
		 */
		if ( ob_get_level() !== $this->bufferLevel ) {
			return;
		}

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $this->rewriteTitle( (string) ob_get_clean() );
	}

	/**
	 * Replace the page document title.
	 *
	 * @since   4.0.5
	 * @version 4.3.2
	 * @version 4.3.9
	 *
	 * @param  string $content The buffer content.
	 * @return string          The rewritten title.
	 */
	private function rewriteTitle( $content ) {
		if ( strpos( $content, '<!-- All in One SEO' ) === false ) {
			return $content;
		}

		// Remove all existing title tags.
		$content   = preg_replace( '#<title.*?/title>#s', '', (string) $content );
		$pageTitle = aioseo()->helpers->escapeRegexReplacement( aioseo()->head->getTitle() );

		// Return new output with our new title tag included in our own comment block.
		return preg_replace( '/(<!--\sAll\sin\sOne\sSEO[a-z0-9\s.]+\s-\saioseo\.com\s-->)/i', "$1\r\n\t\t<title>$pageTitle</title>", (string) $content, 1 );
	}
}