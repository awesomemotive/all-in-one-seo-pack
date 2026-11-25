<?php
namespace AIOSEO\Plugin\Common\Traits\Helpers;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AIOSEO\Plugin\Common\SpellChecker\Dictionary;

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

	/**
	 * Returns the locale TruSEO analyzes a post or term in.
	 *
	 * NOTE: Mirrors the editor's fallback, so the posts and terms lists score alike.
	 *
	 * @since 5.0.6
	 *
	 * @param  \WP_Post|\WP_Term|null $object       The post or term.
	 * @param  string|null            $storedLocale The locale picked in the editor, if any.
	 * @return string                               The locale.
	 */
	public function getTruSeoLocale( $object, $storedLocale = '' ) {
		if ( ! empty( $storedLocale ) ) {
			return (string) $storedLocale;
		}

		$locale = $this->getTruSeoObjectLocale( $object );

		return '' !== $locale ? $locale : $this->resolveTruSeoLocale( get_locale() );
	}

	/**
	 * Returns the TruSEO locale of the WPML language a post or term is in.
	 *
	 * @since 5.0.6
	 *
	 * @param  \WP_Post|\WP_Term|null $object The post or term.
	 * @return string                         The locale, or an empty string if it has no language.
	 */
	public function getTruSeoObjectLocale( $object ) {
		if ( is_a( $object, 'WP_Post' ) ) {
			$element = [
				'element_id'   => $object->ID,
				'element_type' => $object->post_type
			];
		} elseif ( is_a( $object, 'WP_Term' ) ) {
			$element = [
				'element_id'   => $object->term_taxonomy_id,
				'element_type' => 'tax_' . $object->taxonomy
			];
		} else {
			return '';
		}

		$language = apply_filters( 'wpml_element_language_code', null, $element );

		// WPML only assigns a new post its language on the first save, but it's already edited in the requested one.
		if (
			empty( $language ) &&
			is_a( $object, 'WP_Post' ) &&
			'auto-draft' === $object->post_status &&
			apply_filters( 'wpml_is_translated_post_type', false, $object->post_type )
		) {
			$language = apply_filters( 'wpml_current_language', null );
		}

		if ( empty( $language ) || ! is_string( $language ) ) {
			return '';
		}

		$languages = apply_filters( 'wpml_active_languages', null );
		if ( empty( $languages[ $language ]['default_locale'] ) ) {
			return '';
		}

		return $this->resolveTruSeoLocale( (string) $languages[ $language ]['default_locale'] );
	}

	/**
	 * Maps a WordPress locale to the analysis locale TruSEO supports, e.g. de_DE_formal to de_DE.
	 *
	 * @since 5.0.6
	 *
	 * @param  string $locale The WordPress locale.
	 * @return string         The analysis locale, or the given one when no language matches.
	 */
	public function resolveTruSeoLocale( $locale ) {
		$resolved = ( new Dictionary() )->resolveUserLocale( $locale );

		return ! empty( $resolved['locale'] ) ? $resolved['locale'] : (string) $locale;
	}
}