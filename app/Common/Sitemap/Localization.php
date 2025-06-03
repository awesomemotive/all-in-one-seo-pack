<?php
namespace AIOSEO\Plugin\Common\Sitemap;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles sitemap localization logic.
 *
 * @since 4.2.1
 */
class Localization {
	/**
	 * This is cached so we don't do the lookup each query.
	 *
	 * @since 4.0.0
	 *
	 * @var boolean
	 */
	private static $wpml = null;

	/**
	 * Class constructor.
	 *
	 * @since 4.2.1
	 */
	public function __construct() {
		if ( apply_filters( 'aioseo_sitemap_localization_disable', false ) ) {
			return;
		}

		if ( aioseo()->helpers->isWpmlActive() ) {
			self::$wpml = [
				'defaultLanguage' => apply_filters( 'wpml_default_language', null ),
				'activeLanguages' => apply_filters( 'wpml_active_languages', null )
			];

			add_filter( 'aioseo_sitemap_term', [ $this, 'localizeWpml' ], 10, 4 );
			add_filter( 'aioseo_sitemap_post', [ $this, 'localizeWpml' ], 10, 4 );
		}

		if ( aioseo()->helpers->isPluginActive( 'weglot' ) ) {
			add_filter( 'aioseo_sitemap_term', [ $this, 'localizeWeglot' ], 10, 4 );
			add_filter( 'aioseo_sitemap_post', [ $this, 'localizeWeglot' ], 10, 4 );
			add_filter( 'aioseo_sitemap_author_entry', [ $this, 'localizeWeglot' ], 10, 4 );
			add_filter( 'aioseo_sitemap_archive_entry', [ $this, 'localizeWeglot' ], 10, 4 );
			add_filter( 'aioseo_sitemap_date_entry', [ $this, 'localizeWeglot' ], 10, 4 );
			add_filter( 'aioseo_sitemap_product_attributes', [ $this, 'localizeWeglot' ], 10, 4 );
		}
	}

	/**
	 * Localize the entries for Weglot.
	 *
	 * @since 4.8.3
	 *
	 * @param  array       $entry      The entry.
	 * @param  mixed       $entryId    The object ID, null or a date object.
	 * @param  string      $objectName The post type, taxonomy name or date type ('year' or 'month').
	 * @param  string|null $entryType  Whether the entry represents a post, term, author, archive or date.
	 * @return array                   The entry.
	 */
	public function localizeWeglot( $entry, $entryId, $objectName, $entryType = null ) {
		try {
			$originalLang = function_exists( 'weglot_get_original_language' ) ? weglot_get_original_language() : '';
			$translations = function_exists( 'weglot_get_destination_languages' ) ? weglot_get_destination_languages() : [];
			if ( empty( $originalLang ) || empty( $translations ) ) {
				return $entry;
			}

			switch ( $entryType ) {
				case 'post':
					$permalink = get_permalink( $entryId );
					break;
				case 'term':
					$permalink = get_term_link( $entryId, $objectName );
					break;
				case 'author':
					$permalink = get_author_posts_url( $entryId, $objectName );
					break;
				case 'archive':
					$permalink = get_post_type_archive_link( $objectName );
					break;
				case 'date':
					$permalink = 'year' === $objectName ? get_year_link( $entryId->year ) : get_month_link( $entryId->year, $entryId->month );
					break;
				default:
					$permalink = '';
			}

			$entry['languages'] = [];
			foreach ( $translations as $translation ) {
				// If the translation is not public we skip it.
				if ( empty( $translation['public'] ) ) {
					continue;
				}

				$l10nPermalink = $this->weglotGetLocalizedUrl( $permalink, $translation['language_to'] );
				if ( ! empty( $l10nPermalink ) ) {
					$entry['languages'][] = [
						'language' => $translation['language_to'],
						'location' => $l10nPermalink
					];
				}
			}

			// Also include the main page as a translated variant, per Google's specifications, but only if we found at least one other language.
			if ( ! empty( $entry['languages'] ) ) {
				$entry['languages'][] = [
					'language' => $originalLang,
					'location' => aioseo()->helpers->decodeUrl( $entry['loc'] )
				];
			} else {
				unset( $entry['languages'] );
			}

			return $this->validateSubentries( $entry );
		} catch ( \Exception $e ) {
			// Do nothing. It only exists because some "weglot" functions above throw exceptions.
		}

		return $entry;
	}

	/**
	 * Localize the entries for WPML.
	 *
	 * @since   4.0.0
	 * @version 4.8.3 Rename from localizeEntry to localizeWpml.
	 *
	 * @param  array  $entry      The entry.
	 * @param  int    $entryId    The post/term ID.
	 * @param  string $objectName The post type or taxonomy name.
	 * @param  string $objectType Whether the entry is a post or term.
	 * @return array              The entry.
	 */
	public function localizeWpml( $entry, $entryId, $objectName, $objectType ) {
		$elementId   = $entryId;
		$elementType = 'post_' . $objectName;
		if ( 'term' === $objectType ) {
			$term        = aioseo()->helpers->getTerm( $entryId, $objectName );
			$elementId   = $term->term_taxonomy_id;
			$elementType = 'tax_' . $objectName;
		}

		$translationGroupId = apply_filters( 'wpml_element_trid', null, $elementId, $elementType );
		$translations       = apply_filters( 'wpml_get_element_translations', null, $translationGroupId, $elementType );
		if ( empty( $translations ) ) {
			return $entry;
		}

		$entry['languages'] = [];
		$hiddenLanguages    = apply_filters( 'wpml_setting', [], 'hidden_languages' );
		foreach ( $translations as $translation ) {
			if (
				empty( $translation->element_id ) ||
				! isset( self::$wpml['activeLanguages'][ $translation->language_code ] ) ||
				in_array( $translation->language_code, $hiddenLanguages, true )
			) {
				continue;
			}

			$currentLanguage = ! empty( self::$wpml['activeLanguages'][ $translation->language_code ] ) ? self::$wpml['activeLanguages'][ $translation->language_code ] : null;
			$languageCode    = ! empty( $currentLanguage['tag'] ) ? $currentLanguage['tag'] : $translation->language_code;

			if ( (int) $elementId === (int) $translation->element_id ) {
				$entry['language'] = $languageCode;
				continue;
			}

			$translatedObjectId = apply_filters( 'wpml_object_id', $entryId, $objectName, false, $translation->language_code );
			if (
				( 'post' === $objectType && $this->isExcludedPost( $translatedObjectId ) ) ||
				( 'term' === $objectType && $this->isExcludedTerm( $translatedObjectId ) )
			) {
				continue;
			}

			if ( 'post' === $objectType ) {
				$permalink = get_permalink( $translatedObjectId );

				// Special treatment for the home page translations.
				if ( 'page' === get_option( 'show_on_front' ) && aioseo()->helpers->wpmlIsHomePage( $entryId ) ) {
					$permalink = aioseo()->helpers->wpmlHomeUrl( $translation->language_code );
				}
			} else {
				$permalink = get_term_link( $translatedObjectId, $objectName );
			}

			if ( ! empty( $languageCode ) && ! empty( $permalink ) ) {
				$entry['languages'][] = [
					'language' => $languageCode,
					'location' => aioseo()->helpers->decodeUrl( $permalink )
				];
			}
		}

		// Also include the main page as a translated variant, per Google's specifications, but only if we found at least one other language.
		if ( ! empty( $entry['language'] ) && ! empty( $entry['languages'] ) ) {
			$entry['languages'][] = [
				'language' => $entry['language'],
				'location' => aioseo()->helpers->decodeUrl( $entry['loc'] )
			];
		} else {
			unset( $entry['languages'] );
		}

		return $this->validateSubentries( $entry );
	}

	/**
	 * Validates the subentries with translated variants to ensure all required values are set.
	 *
	 * @since 4.2.3
	 *
	 * @param  array $entry The entry.
	 * @return array        The validated entry.
	 */
	private function validateSubentries( $entry ) {
		if ( ! isset( $entry['languages'] ) ) {
			return $entry;
		}

		foreach ( $entry['languages'] as $index => $subentry ) {
			if ( empty( $subentry['language'] ) || empty( $subentry['location'] ) ) {
				unset( $entry['languages'][ $index ] );
			}
		}

		return $entry;
	}

	/**
	 * Checks whether the given post should be excluded.
	 *
	 * @since 4.2.4
	 *
	 * @param  int  $postId The post ID.
	 * @return bool         Whether the post should be excluded.
	 */
	private function isExcludedPost( $postId ) {
		static $excludedPostIds = null;
		if ( null === $excludedPostIds ) {
			$excludedPostIds = explode( ', ', aioseo()->sitemap->helpers->excludedPosts() );
			$excludedPostIds = array_map( function ( $postId ) {
				return (int) $postId;
			}, $excludedPostIds );
		}

		if ( in_array( $postId, $excludedPostIds, true ) ) {
			return true;
		}

		// Let's also check if the post is published and not password-protected.
		$post = get_post( $postId );
		if ( ! is_a( $post, 'WP_Post' ) ) {
			return true;
		}

		if ( ! empty( $post->post_password ) || 'publish' !== $post->post_status ) {
			return true;
		}

		// Now, we must also check for noindex.
		$metaData = aioseo()->meta->metaData->getMetaData( $post );
		if ( ! empty( $metaData->robots_noindex ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Checks whether the given term should be excluded.
	 *
	 * @since 4.2.4
	 *
	 * @param  int  $termId The term ID.
	 * @return bool         Whether the term should be excluded.
	 */
	private function isExcludedTerm( $termId ) {
		static $excludedTermIds = null;
		if ( null === $excludedTermIds ) {
			$excludedTermIds = explode( ', ', aioseo()->sitemap->helpers->excludedTerms() );
			$excludedTermIds = array_map( function ( $termId ) {
				return (int) $termId;
			}, $excludedTermIds );
		}

		if ( in_array( $termId, $excludedTermIds, true ) ) {
			return true;
		}

		// Now, we must also check for noindex.
		$term = aioseo()->helpers->getTerm( $termId );
		if ( ! is_a( $term, 'WP_Term' ) ) {
			return true;
		}

		// At least one post must be assigned to the term.
		$posts = aioseo()->core->db->start( 'term_relationships' )
			->select( 'object_id' )
			->where( 'term_taxonomy_id =', $term->term_taxonomy_id )
			->limit( 1 )
			->run()
			->result();

		if ( empty( $posts ) ) {
			return true;
		}

		$metaData = aioseo()->meta->metaData->getMetaData( $term );
		if ( ! empty( $metaData->robots_noindex ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Retrieves the localized URL.
	 *
	 * @since 4.8.3
	 *
	 * @param  string       $url  The page URL to localize.
	 * @param  string       $code The language code (e.g. 'br', 'en').
	 * @return string|false       The localized URL or false if it fails.
	 */
	private function weglotGetLocalizedUrl( $url, $code ) {
		try {
			if (
				! $url ||
				! function_exists( 'weglot_get_service' )
			) {
				return false;
			}

			$languageService   = weglot_get_service( 'Language_Service_Weglot' );
			$requestUrlService = weglot_get_service( 'Request_Url_Service_Weglot' );
			$wgUrl             = $requestUrlService->create_url_object( $url );
			$language          = $languageService->get_language_from_internal( $code );

			return $wgUrl->getForLanguage( $language );
		} catch ( \Exception $e ) {
			// Do nothing. It only exists because some "weglot" functions above throw exceptions.
		}

		return false;
	}
}