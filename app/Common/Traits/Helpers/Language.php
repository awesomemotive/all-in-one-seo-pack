<?php
namespace AIOSEO\Plugin\Common\Traits\Helpers;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Contains i18n and language (code) helper methods.
 *
 * @since 4.1.4
 */
trait Language {
	/**
	 * Returns the language of the current response in BCP 47 format.
	 *
	 * @since 4.1.4
	 *
	 * @return string The language code in BCP 47 format.
	 */
	public function currentLanguageCodeBCP47() {
		return str_replace( '_', '-', determine_locale() );
	}
}