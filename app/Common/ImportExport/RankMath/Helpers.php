<?php
namespace AIOSEO\Plugin\Common\ImportExport\RankMath;

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
	 * Converts the macros from Rank Math to our own smart tags.
	 *
	 * @since 4.0.0
	 *
	 * @param  string $string   The string with macros.
	 * @param  string $pageType The page type.
	 * @return string $string   The string with smart tags.
	 */
	public function macrosToSmartTags( $string, $pageType = null ) {
		$macros = $this->getMacros( $pageType );

		if ( preg_match( '#%BLOGDESCLINK%#', $string ) ) {
			$blogDescriptionLink = '<a href="' .
				aioseo()->helpers->decodeHtmlEntities( get_bloginfo( 'url' ) ) . '">' .
				aioseo()->helpers->decodeHtmlEntities( get_bloginfo( 'name' ) ) . ' - ' .
				aioseo()->helpers->decodeHtmlEntities( get_bloginfo( 'description' ) ) . '</a>';

			$string = str_replace( '%BLOGDESCLINK%', $blogDescriptionLink, $string );
		}

		if ( preg_match_all( '#%customfield\(([^%\s]*)\)%#', $string, $matches ) && ! empty( $matches[1] ) ) {
			foreach ( $matches[1] as $name ) {
				$string = aioseo()->helpers->pregReplace( "#%customfield\($name\)%#", "#custom_field-$name", $string );
			}
		}

		if ( preg_match_all( '#%customterm\(([^%\s]*)\)%#', $string, $matches ) && ! empty( $matches[1] ) ) {
			foreach ( $matches[1] as $name ) {
				$string = aioseo()->helpers->pregReplace( "#%customterm\($name\)%#", "#tax_name-$name", $string );
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
	 * @param  string $pageType The page type.
	 * @return array  $macros   The macros.
	 */
	protected function getMacros( $pageType = null ) {
		$macros = [
			'%sitename%'         => '#site_title',
			'%blog_title%'       => '#site_title',
			'%blog_description%' => '#tagline',
			'%sitedesc%'         => '#tagline',
			'%sep%'              => '#separator_sa',
			'%post_title%'       => '#post_title',
			'%page_title%'       => '#post_title',
			'%postname%'         => '#post_title',
			'%title%'            => '#post_title',
			'%seo_title%'        => '#post_title',
			'%excerpt%'          => '#post_excerpt',
			'%wc_shortdesc%'     => '#post_excerpt',
			'%category%'         => '#taxonomy_title',
			'%term%'             => '#taxonomy_title',
			'%term_description%' => '#taxonomy_description',
			'%currentdate%'      => '#current_date',
			'%currentday%'       => '#current_day',
			'%currentyear%'      => '#current_year',
			'%currentmonth%'     => '#current_month',
			'%name%'             => '#author_first_name #author_last_name',
			'%author%'           => '#author_first_name #author_last_name',
			'%date%'             => '#post_date',
			'%year%'             => '#current_year',
			'%search_query%'     => '#search_term',
			// RSS Content tags.
			'%AUTHORLINK%'       => '#author_link',
			'%POSTLINK%'         => '#post_link',
			'%BLOGLINK%'         => '#site_link',
			'%FEATUREDIMAGE%'    => '#featured_image'
		];

		switch ( $pageType ) {
			case 'archive':
				$macros['%title%'] = '#archive_title';
				break;
			case 'term':
				$macros['%title%'] = '#taxonomy_title';
				break;
			default:
				$macros['%title%'] = '#post_title';
				break;
		}

		// Strip all other tags.
		$macros['%[^%]*%'] = '';

		return $macros;
	}
}