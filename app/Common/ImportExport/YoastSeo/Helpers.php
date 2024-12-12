<?php
namespace AIOSEO\Plugin\Common\ImportExport\YoastSeo;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AIOSEO\Plugin\Common\ImportExport;

// phpcs:disable WordPress.Arrays.ArrayDeclarationSpacing.AssociativeArrayFound

/**
 * Contains helper methods for the import from Rank Math.
 *
 * @since 4.0.0
 */
class Helpers extends ImportExport\Helpers {
	/**
	 * Converts the macros from Yoast SEO to our own smart tags.
	 *
	 * @since 4.0.0
	 *
	 * @param  string $string   The string with macros.
	 * @param  string $postType The post type.
	 * @param  string $pageType The page type.
	 * @return string $string   The string with smart tags.
	 */
	public function macrosToSmartTags( $string, $postType = null, $pageType = null ) {
		$macros = $this->getMacros( $postType, $pageType );

		if ( preg_match( '#%%BLOGDESCLINK%%#', (string) $string ) ) {
			$blogDescriptionLink = '<a href="' .
				aioseo()->helpers->decodeHtmlEntities( get_bloginfo( 'url' ) ) . '">' .
				aioseo()->helpers->decodeHtmlEntities( get_bloginfo( 'name' ) ) . ' - ' .
				aioseo()->helpers->decodeHtmlEntities( get_bloginfo( 'description' ) ) . '</a>';

			$string = str_replace( '%%BLOGDESCLINK%%', $blogDescriptionLink, $string );
		}

		if ( preg_match_all( '#%%cf_([^%]*)%%#', (string) $string, $matches ) && ! empty( $matches[1] ) ) {
			foreach ( $matches[1] as $name ) {
				if ( ! preg_match( '#\s#', (string) $name ) ) {
					$string = aioseo()->helpers->pregReplace( "#%%cf_$name%%#", "#custom_field-$name", $string );
				}
			}
		}

		if ( preg_match_all( '#%%tax_([^%]*)%%#', (string) $string, $matches ) && ! empty( $matches[1] ) ) {
			foreach ( $matches[1] as $name ) {
				if ( ! preg_match( '#\s#', (string) $name ) ) {
					$string = aioseo()->helpers->pregReplace( "#%%tax_$name%%#", "#tax_name-$name", $string );
				}
			}
		}

		foreach ( $macros as $macro => $tag ) {
			$string = aioseo()->helpers->pregReplace( "#$macro(?![a-zA-Z0-9_])#im", $tag, $string );
		}

		// Strip out all remaining tags.
		$string = aioseo()->helpers->pregReplace( '/%[^\%\s]*\([^\%]*\)%/i', '', aioseo()->helpers->pregReplace( '/%[^\%\s]*%/i', '', $string ) );

		return trim( $string );
	}

	/**
	 * Returns the macro mappings.
	 *
	 * @since 4.1.1
	 *
	 * @param  string $postType The post type.
	 * @param  string $pageType The page type.
	 * @return array  $macros   The macros.
	 */
	protected function getMacros( $postType = null, $pageType = null ) {
		$macros = [
			'%%sitename%%'             => '#site_title',
			'%%sitedesc%%'             => '#tagline',
			'%%sep%%'                  => '#separator_sa',
			'%%term_title%%'           => '#taxonomy_title',
			'%%term_description%%'     => '#taxonomy_description',
			'%%category_description%%' => '#taxonomy_description',
			'%%tag_description%%'      => '#taxonomy_description',
			'%%primary_category%%'     => '#taxonomy_title',
			'%%archive_title%%'        => '#archive_title',
			'%%pagenumber%%'           => '#page_number',
			'%%caption%%'              => '#attachment_caption',
			'%%name%%'                 => '#author_first_name #author_last_name',
			'%%user_description%%'     => '#author_bio',
			'%%date%%'                 => '#archive_date',
			'%%currentday%%'           => '#current_day',
			'%%currentmonth%%'         => '#current_month',
			'%%currentyear%%'          => '#current_year',
			'%%searchphrase%%'         => '#search_term',
			'%%AUTHORLINK%%'           => '#author_link',
			'%%POSTLINK%%'             => '#post_link',
			'%%BLOGLINK%%'             => '#site_link',
			'%%category%%'             => '#categories',
			'%%parent_title%%'         => '#parent_title',
			'%%wc_sku%%'               => '#woocommerce_sku',
			'%%wc_price%%'             => '#woocommerce_price',
			'%%wc_brand%%'             => '#woocommerce_brand',
			'%%excerpt%%'              => '#post_excerpt',
			'%%excerpt_only%%'         => '#post_excerpt_only'
			/* '%%tag%%'                  => '',
			'%%id%%'                   => '',
			'%%page%%'                 => '',
			'%%modified%%'             => '',
			'%%pagetotal%%'            => '',
			'%%focuskw%%'              => '',
			'%%term404%%'              => '',
			'%%ct_desc_[^%]*%%'        => '' */
		];

		if ( $postType ) {
			$postType = get_post_type_object( $postType );
			if ( ! empty( $postType ) ) {
				$macros += [
					'%%pt_single%%' => $postType->labels->singular_name,
					'%%pt_plural%%' => $postType->labels->name,
				];
			}
		}

		switch ( $pageType ) {
			case 'archive':
				$macros['%%title%%'] = '#archive_title';
				break;
			case 'term':
				$macros['%%title%%'] = '#taxonomy_title';
				break;
			default:
				$macros['%%title%%'] = '#post_title';
				break;
		}

		// Strip all other tags.
		$macros['%%[^%]*%%'] = '';

		return $macros;
	}
}