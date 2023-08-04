<?php
namespace AIOSEO\Plugin\Common\ImportExport\SeoPress;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AIOSEO\Plugin\Common\ImportExport;

// phpcs:disable WordPress.Arrays.ArrayDeclarationSpacing.AssociativeArrayFound

/**
 * Contains helper methods for the import from SEOPress.
 *
 * @since 4.1.4
 */
class Helpers extends ImportExport\Helpers {
	/**
	 * Converts the macros from SEOPress to our own smart tags.
	 *
	 * @since 4.1.4
	 *
	 * @param  string $string   The string with macros.
	 * @param  string $postType The post type.
	 * @return string           The string with smart tags.
	 */
	public function macrosToSmartTags( $string, $postType = null ) {
		$macros = $this->getMacros( $postType );

		foreach ( $macros as $macro => $tag ) {
			$string = aioseo()->helpers->pregReplace( "#$macro(?![a-zA-Z0-9_])#im", $tag, $string );
		}

		return trim( $string );
	}

	/**
	 * Returns the macro mappings.
	 *
	 * @since 4.1.4
	 *
	 * @param  string $postType The post type.
	 * @param  string $pageType The page type.
	 * @return array  $macros   The macros.
	 */
	protected function getMacros( $postType = null, $pageType = null ) {
		$macros = [
			'%%sep%%'                   => '#separator_sa',
			'%%sitetitle%%'             => '#site_title',
			'%%sitename%%'              => '#site_title',
			'%%tagline%%'               => '#tagline',
			'%%sitedesc%%'              => '#tagline',
			'%%title%%'                 => '#site_title',
			'%%post_title%%'            => '#post_title',
			'%%post_excerpt%%'          => '#post_excerpt',
			'%%excerpt%%'               => '#post_excerpt',
			'%%post_content%%'          => '#post_content',
			'%%post_url%%'              => '#permalink',
			'%%post_date%%'             => '#post_date',
			'%%post_permalink%%'        => '#permalink',
			'%%date%%'                  => '#post_date',
			'%%post_author%%'           => '#author_name',
			'%%post_category%%'         => '#categories',
			'%%_category_title%%'       => '#taxonomy_title',
			'%%_category_description%%' => '#taxonomy_description',
			'%%tag_title%%'             => '#taxonomy_title',
			'%%tag_description%%'       => '#taxonomy_description',
			'%%term_title%%'            => '#taxonomy_title',
			'%%term_description%%'      => '#taxonomy_description',
			'%%search_keywords%%'       => '#search_term',
			'%%current_pagination%%'    => '#page_number',
			'%%page%%'                  => '#page_number',
			'%%archive_title%%'         => '#archive_title',
			'%%archive_date%%'          => '#archive_date',
			'%%wc_single_price%%'       => '#woocommerce_price',
			'%%wc_sku%%'                => '#woocommerce_sku',
			'%%currentday%%'            => '#current_day',
			'%%currentmonth%%'          => '#current_month',
			'%%currentmonth_short%%'    => '#current_month',
			'%%currentyear%%'           => '#current_year',
			'%%currentdate%%'           => '#current_date',
			'%%author_first_name%%'     => '#author_first_name',
			'%%author_last_name%%'      => '#author_last_name',
			'%%author_website%%'        => '#author_link',
			'%%author_nickname%%'       => '#author_first_name',
			'%%author_bio%%'            => '#author_bio',
			'%%currentmonth_num%%'      => '#current_month',
		];

		if ( $postType ) {
			$postType = get_post_type_object( $postType );
			if ( ! empty( $postType ) ) {
				$macros += [
					'%%cpt_plural%%' => $postType->labels->name,
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