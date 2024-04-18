<?php
namespace AIOSEO\Plugin\Common\Meta;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles the keywords.
 *
 * @since 4.0.0
 */
class Keywords {
	/**
	 * Get the keywords for the meta output.
	 *
	 * @since 4.0.0
	 *
	 * @return string The keywords as a string.
	 */
	public function getKeywords() {
		if ( ! aioseo()->options->searchAppearance->advanced->useKeywords ) {
			return '';
		}

		$isStaticArchive = aioseo()->helpers->isWooCommerceShopPage() || aioseo()->helpers->isStaticPostsPage();
		$dynamicContent  = is_archive() || is_post_type_archive() || is_home() || aioseo()->helpers->isWooCommerceShopPage() || is_category() || is_tag() || is_tax();
		$generate        = aioseo()->options->searchAppearance->advanced->dynamicallyGenerateKeywords;
		if ( $dynamicContent && $generate ) {
			return $this->prepareKeywords( $this->getGeneratedKeywords() );
		}

		if ( is_front_page() && ! aioseo()->helpers->isStaticHomePage() ) {
			$keywords = $this->extractMetaKeywords( aioseo()->options->searchAppearance->global->keywords );

			return $this->prepareKeywords( $keywords );
		}

		if ( $dynamicContent && ! $isStaticArchive ) {
			if ( is_date() ) {
				$keywords = $this->extractMetaKeywords( aioseo()->options->searchAppearance->archives->date->advanced->keywords );

				return $this->prepareKeywords( $keywords );
			}

			if ( is_author() ) {
				$keywords = $this->extractMetaKeywords( aioseo()->options->searchAppearance->archives->author->advanced->keywords );

				return $this->prepareKeywords( $keywords );
			}

			if ( is_search() ) {
				$keywords = $this->extractMetaKeywords( aioseo()->options->searchAppearance->archives->search->advanced->keywords );

				return $this->prepareKeywords( $keywords );
			}

			$postType       = get_queried_object();
			$dynamicOptions = aioseo()->dynamicOptions->noConflict();
			if ( $postType && $dynamicOptions->searchAppearance->archives->has( $postType->name ) ) {
				$keywords = $this->extractMetaKeywords( aioseo()->dynamicOptions->searchAppearance->archives->{ $postType->name }->advanced->keywords );

				return $this->prepareKeywords( $keywords );
			}

			return '';
		}

		return $this->prepareKeywords( $this->getAllKeywords() );
	}

	/**
	 * Get generated keywords for an archive page.
	 *
	 * @since 4.0.0
	 *
	 * @return array An array of generated keywords.
	 */
	private function getGeneratedKeywords() {
		global $posts, $wp_query;

		$keywords        = [];
		$isStaticArchive = aioseo()->helpers->isWooCommerceShopPage() || aioseo()->helpers->isStaticPostsPage();
		if ( $isStaticArchive ) {
			$keywords = $this->getAllKeywords();
		} elseif ( is_front_page() && ! aioseo()->helpers->isStaticHomePage() ) {
			$keywords = $this->extractMetaKeywords( aioseo()->options->searchAppearance->global->keywords );
		} elseif ( is_category() || is_tag() || is_tax() ) {
			$metaData = aioseo()->meta->metaData->getMetaData();
			if ( ! empty( $metaData->keywords ) ) {
				$keywords = $this->extractMetaKeywords( $metaData->keywords );
			}
		}

		$wpPosts = $posts;
		if ( empty( $posts ) ) {
			$wpPosts = array_filter( [ aioseo()->helpers->getPost() ] );
		}

		// Turn off current query so we can get specific post data.
		$originalTag      = $wp_query->is_tag;
		$originalTax      = $wp_query->is_tax;
		$originalCategory = $wp_query->is_category;

		$wp_query->is_tag      = false;
		$wp_query->is_tax      = false;
		$wp_query->is_category = false;

		foreach ( $wpPosts as $post ) {
			$metaData    = aioseo()->meta->metaData->getMetaData( $post );
			$tmpKeywords = $this->extractMetaKeywords( $metaData->keywords );
			if ( count( $tmpKeywords ) ) {
				foreach ( $tmpKeywords as $keyword ) {
					$keywords[] = $keyword;
				}
			}
		}

		$wp_query->is_tag      = $originalTag;
		$wp_query->is_tax      = $originalTax;
		$wp_query->is_category = $originalCategory;

		return $keywords;
	}

	/**
	 * Returns the keywords.
	 *
	 * @since 4.0.0
	 *
	 * @return string A comma-separated list of unique keywords.
	 */
	public function getAllKeywords() {
		$keywords = [];
		$post     = aioseo()->helpers->getPost();
		$metaData = aioseo()->meta->metaData->getMetaData();
		if ( ! empty( $metaData->keywords ) ) {
			$keywords = $this->extractMetaKeywords( $metaData->keywords );
		}

		if ( $post ) {
			if ( aioseo()->options->searchAppearance->advanced->useTagsForMetaKeywords ) {
				$keywords = array_merge( $keywords, aioseo()->helpers->getAllTags( $post->ID ) );
			}

			if ( aioseo()->options->searchAppearance->advanced->useCategoriesForMetaKeywords && ! is_page() ) {
				$keywords = array_merge( $keywords, aioseo()->helpers->getAllCategories( $post->ID ) );
			}
		}

		return $keywords;
	}

	/**
	 * Prepares the keywords for display.
	 *
	 * @since 4.0.0
	 *
	 * @param  array  $keywords Raw keywords.
	 * @return string           A list of prepared keywords, comma-separated.
	 */
	protected function prepareKeywords( $keywords ) {
		$keywords = $this->getUniqueKeywords( $keywords );
		$keywords = trim( $keywords );
		$keywords = aioseo()->helpers->internationalize( $keywords );
		$keywords = stripslashes( $keywords );
		$keywords = str_replace( '"', '', $keywords );
		$keywords = wp_filter_nohtml_kses( $keywords );

		return apply_filters( 'aioseo_keywords', $keywords );
	}

	/**
	 * Returns an array of keywords, based on a stringified list separated by commas.
	 *
	 * @since 4.0.0
	 *
	 * @param  string $keywords The keywords string.
	 * @return array            The keywords.
	 */
	public function keywordStringToList( $keywords ) {
		$keywords = str_replace( '"', '', $keywords );

		return ! empty( $keywords ) ? explode( ',', $keywords ) : [];
	}

	/**
	 * Returns a stringified list of unique keywords, separated by commas.
	 *
	 * @since 4.0.0
	 *
	 * @param  array   $keywords The keywords.
	 * @param  boolean $toString Whether or not to turn it into a comma separated string.
	 * @return string            The keywords string.
	 */
	public function getUniqueKeywords( $keywords, $toString = true ) {
		$keywords = $this->keywordsToLowerCase( $keywords );

		return $toString ? implode( ',', $keywords ) : $keywords;
	}

	/**
	 * Returns the keywords in lowercase.
	 *
	 * @since 4.0.0
	 *
	 * @param  array $keywords The keywords.
	 * @return array           The formatted keywords.
	 */
	private function keywordsToLowerCase( $keywords ) {
		$smallKeywords = [];
		if ( ! is_array( $keywords ) ) {
			$keywords = $this->keywordStringToList( $keywords );
		}
		if ( ! empty( $keywords ) ) {
			foreach ( $keywords as $keyword ) {
				$smallKeywords[] = trim( aioseo()->helpers->toLowercase( $keyword ) );
			}
		}

		return array_unique( $smallKeywords );
	}

	/**
	 * Extract keywords and then return as a string.
	 *
	 * @since 4.0.0
	 *
	 * @param  array|string $keywords An array of keywords or a json string.
	 * @return array                  An array of keywords that were extracted.
	 */
	public function extractMetaKeywords( $keywords ) {
		$extracted = [];

		$keywords = is_string( $keywords ) ? json_decode( $keywords ) : $keywords;

		if ( ! empty( $keywords ) ) {
			foreach ( $keywords as $keyword ) {
				$extracted[] = trim( $keyword->value );
			}
		}

		return $extracted;
	}
}