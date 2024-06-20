<?php
namespace AIOSEO\Plugin\Common\Breadcrumbs;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class to replace tag values with their data counterparts.
 *
 * @since 4.1.1
 */
class Tags {
	/**
	 * Tags constructor.
	 *
	 * @since 4.1.1
	 */
	public function __construct() {
		aioseo()->tags->addContext( $this->getContexts() );
		aioseo()->tags->addTags( $this->getTags() );
	}

	/**
	 * Replace the tags in the string provided.
	 *
	 * @since 4.1.1
	 *
	 * @param  string  $string           The string with tags.
	 * @param  array   $item             The breadcrumb item.
	 * @param  boolean $stripPunctuation Whether we should strip punctuation after the tags have been converted.
	 * @return string                    The string with tags replaced.
	 */
	public function replaceTags( $string, $item, $stripPunctuation = false ) {
		if ( ! $string || ! preg_match( '/#/', $string ) ) {
			return $string;
		}

		// Replace separator tag so we don't strip it as punctuation.
		$separatorTag = aioseo()->tags->denotationChar . 'separator_sa';
		$string       = preg_replace( "/$separatorTag(?![a-zA-Z0-9_])/im", '>thisisjustarandomplaceholder<', $string );

		// Replace custom breadcrumb tags.
		foreach ( $this->getTags() as $tag ) {
			$tagId   = aioseo()->tags->denotationChar . $tag['id'];
			$pattern = "/$tagId(?![a-zA-Z0-9_])/im";
			if ( preg_match( $pattern, $string ) ) {
				$tagValue = str_replace( '$', '\$', $this->getTagValue( $tag, $item ) );
				$string   = preg_replace( $pattern, $tagValue, $string );
			}
		}

		if ( $stripPunctuation ) {
			$string = aioseo()->helpers->stripPunctuation( $string );
		}

		return preg_replace(
			'/>thisisjustarandomplaceholder<(?![a-zA-Z0-9_])/im',
			aioseo()->helpers->decodeHtmlEntities( aioseo()->options->searchAppearance->global->separator ),
			$string
		);
	}

	/**
	 * Get the value of the tag to replace.
	 *
	 * @since 4.1.1
	 *
	 * @param  string $tag  The tag to look for.
	 * @param  int    $item The crumb array.
	 * @return string       The value of the tag.
	 */
	public function getTagValue( $tag, $item ) {
		$product = false;
		if ( 0 === stripos( $tag['id'], 'breadcrumb_wc_product_' ) ) {
			$product = wc_get_product( $item['reference'] );
			if ( ! $product ) {
				return;
			}
		}

		switch ( $tag['id'] ) {
			case 'breadcrumb_link':
				return $item['link'];
			case 'breadcrumb_separator':
				return aioseo()->breadcrumbs->frontend->getSeparator();
			case 'breadcrumb_wc_product_price':
				return $product ? wc_price( $product->get_price() ) : '';
			case 'breadcrumb_wc_product_sku':
				return $product ? $product->get_sku() : '';
			case 'breadcrumb_wc_product_brand':
				return $product ? aioseo()->helpers->getWooCommerceBrand( $product->get_id() ) : '';
			case 'breadcrumb_author_first_name':
				return $item['reference']->first_name;
			case 'breadcrumb_author_last_name':
				return $item['reference']->last_name;
			case 'breadcrumb_archive_post_type_name':
				return $item['reference']->label;
			case 'breadcrumb_search_string':
				return $item['reference'];
			case 'breadcrumb_format_page_number':
				return $item['reference']['paged'];
			default:
				return $item['label'];
		}
	}

	/**
	 * Gets our breadcrumb custom tags.
	 *
	 * @since 4.1.1
	 *
	 * @return array An array of tags.
	 */
	public function getTags() {
		$tags = [
			[
				'id'          => 'breadcrumb_link',
				'name'        => __( 'Permalink', 'all-in-one-seo-pack' ),
				'description' => __( 'The permalink.', 'all-in-one-seo-pack' )
			],
			[
				'id'          => 'breadcrumb_label',
				'name'        => __( 'Label', 'all-in-one-seo-pack' ),
				'description' => __( 'The label.', 'all-in-one-seo-pack' )
			],
			[
				'id'          => 'breadcrumb_post_title',
				// Translators: 1 - The type of page (Post, Page, Category, Tag, etc.).
				'name'        => sprintf( __( '%1$s Title', 'all-in-one-seo-pack' ), 'Post' ),
				'description' => __( 'The original title of the current post.', 'all-in-one-seo-pack' )
			],
			[
				'id'          => 'breadcrumb_taxonomy_title',
				// Translators: 1 - The type of page (Post, Page, Category, Tag, etc.).
				'name'        => sprintf( __( '%1$s Title', 'all-in-one-seo-pack' ), 'Category' ),
				// Translators: 1 - The name of a taxonomy.
				'description' => sprintf( __( 'The %1$s title.', 'all-in-one-seo-pack' ), 'Category' )
			],
			[
				'id'          => 'breadcrumb_separator',
				'name'        => __( 'Separator', 'all-in-one-seo-pack' ),
				'description' => __( 'The crumb separator.', 'all-in-one-seo-pack' )
			],
			[
				'id'          => 'breadcrumb_blog_page_title',
				'name'        => __( 'Blog Page Title', 'all-in-one-seo-pack' ),
				'description' => __( 'The blog page title.', 'all-in-one-seo-pack' )
			],
			[
				'id'          => 'breadcrumb_author_display_name',
				'name'        => __( 'Author Display Name', 'all-in-one-seo-pack' ),
				'description' => __( 'The author\'s display name.', 'all-in-one-seo-pack' )
			],
			[
				'id'          => 'breadcrumb_author_first_name',
				'name'        => __( 'Author First Name', 'all-in-one-seo-pack' ),
				'description' => __( 'The author\'s first name.', 'all-in-one-seo-pack' )
			],
			[
				'id'          => 'breadcrumb_author_last_name',
				'name'        => __( 'Author Last Name', 'all-in-one-seo-pack' ),
				'description' => __( 'The author\'s last name.', 'all-in-one-seo-pack' )
			],
			[
				'id'          => 'breadcrumb_search_result_format',
				'name'        => __( 'Search result format', 'all-in-one-seo-pack' ),
				'description' => __( 'The search result format.', 'all-in-one-seo-pack' )
			],
			[
				'id'          => 'breadcrumb_404_error_format',
				'name'        => __( '404 Error Format', 'all-in-one-seo-pack' ),
				'description' => __( 'The 404 error format.', 'all-in-one-seo-pack' )
			],
			[
				'id'          => 'breadcrumb_date_archive_year',
				'name'        => __( 'Year', 'all-in-one-seo-pack' ),
				'description' => __( 'The year.', 'all-in-one-seo-pack' )
			],
			[
				'id'          => 'breadcrumb_date_archive_month',
				'name'        => __( 'Month', 'all-in-one-seo-pack' ),
				'description' => __( 'The month.', 'all-in-one-seo-pack' )
			],
			[
				'id'          => 'breadcrumb_date_archive_day',
				'name'        => __( 'Day', 'all-in-one-seo-pack' ),
				'description' => __( 'The day.', 'all-in-one-seo-pack' )
			],
			[
				'id'          => 'breadcrumb_search_string',
				'name'        => __( 'Search String', 'all-in-one-seo-pack' ),
				'description' => __( 'The search string.', 'all-in-one-seo-pack' )
			],
			[
				'id'          => 'breadcrumb_format_page_number',
				'name'        => __( 'Page Number', 'all-in-one-seo-pack' ),
				'description' => __( 'The page number.', 'all-in-one-seo-pack' )
			],
			[
				'id'          => 'breadcrumb_archive_post_type_format',
				'name'        => __( 'Archive format', 'all-in-one-seo-pack' ),
				'description' => __( 'The archive format.', 'all-in-one-seo-pack' )
			],
			[
				'id'          => 'breadcrumb_archive_post_type_name',
				'name'        => __( 'Post Type Name', 'all-in-one-seo-pack' ),
				'description' => __( 'The archive post type name.', 'all-in-one-seo-pack' )
			]
		];

		$postTypes = aioseo()->helpers->getPublicPostTypes();
		foreach ( $postTypes as $postType ) {
			if ( 'product' === $postType['name'] && aioseo()->helpers->isWoocommerceActive() ) {
				$tags[] = [
					'id'          => 'breadcrumb_wc_product_price',
					// Translators: 1 - The name of a post type.
					'name'        => sprintf( __( '%1$s Price', 'all-in-one-seo-pack' ), $postType['singular'] ),
					// Translators: 1 - The name of a post type.
					'description' => sprintf( __( 'The %1$s price.', 'all-in-one-seo-pack' ), $postType['singular'] )
				];
				$tags[] = [
					'id'          => 'breadcrumb_wc_product_sku',
					// Translators: 1 - The name of a post type.
					'name'        => sprintf( __( '%1$s SKU', 'all-in-one-seo-pack' ), $postType['singular'] ),
					// Translators: 1 - The name of a post type.
					'description' => sprintf( __( 'The %1$s SKU.', 'all-in-one-seo-pack' ), $postType['singular'] )
				];
				$tags[] = [
					'id'          => 'breadcrumb_wc_product_brand',
					// Translators: 1 - The name of a post type.
					'name'        => sprintf( __( '%1$s Brand', 'all-in-one-seo-pack' ), $postType['singular'] ),
					// Translators: 1 - The name of a post type.
					'description' => sprintf( __( 'The %1$s brand.', 'all-in-one-seo-pack' ), $postType['singular'] )
				];
			}
		}

		return $tags;
	}

	/**
	 * Gets our breadcrumb contexts.
	 *
	 * @since 4.1.1
	 *
	 * @return array An array of contexts.
	 */
	public function getContexts() {
		$contexts = [];

		$baseTags = [ 'breadcrumb_link', 'breadcrumb_separator' ];

		$postTypes = aioseo()->helpers->getPublicPostTypes();
		foreach ( $postTypes as $postType ) {
			$contexts[ 'breadcrumbs-post-type-' . $postType['name'] ] = array_merge( $baseTags, [ 'breadcrumb_post_title' ] );

			if ( 'product' === $postType['name'] && aioseo()->helpers->isWoocommerceActive() ) {
				$contexts[ 'breadcrumbs-post-type-' . $postType['name'] ] = array_merge( $contexts[ 'breadcrumbs-post-type-' . $postType['name'] ], [
					'breadcrumb_wc_product_price',
					'breadcrumb_wc_product_sku',
					'breadcrumb_wc_product_brand'
				] );
			}
		}

		$taxonomies = aioseo()->helpers->getPublicTaxonomies();
		foreach ( $taxonomies as $taxonomy ) {
			$contexts[ 'breadcrumbs-taxonomy-' . $taxonomy['name'] ] = array_merge( $baseTags, [ 'breadcrumb_taxonomy_title' ] );
		}

		$archives = aioseo()->helpers->getPublicPostTypes( false, true, true );
		foreach ( $archives as $archive ) {
			$contexts[ 'breadcrumbs-post-type-archive-' . $archive['name'] ] = array_merge( $baseTags, [
				'breadcrumb_archive_post_type_format',
				'breadcrumb_archive_post_type_name'
			] );
		}

		$contexts['breadcrumbs-blog-archive'] = array_merge( $baseTags, [ 'breadcrumb_blog_page_title' ] );

		$contexts['breadcrumbs-author'] = array_merge( $baseTags, [
			'breadcrumb_author_display_name',
			'breadcrumb_author_first_name',
			'breadcrumb_author_last_name'
		] );

		$contexts['breadcrumbs-search']             = array_merge( $baseTags, [ 'breadcrumb_search_result_format', 'breadcrumb_search_string' ] );
		$contexts['breadcrumbs-notFound']           = array_merge( $baseTags, [ 'breadcrumb_404_error_format' ] );
		$contexts['breadcrumbs-date-archive-year']  = array_merge( $baseTags, [ 'breadcrumb_date_archive_year' ] );
		$contexts['breadcrumbs-date-archive-month'] = array_merge( $baseTags, [ 'breadcrumb_date_archive_month' ] );
		$contexts['breadcrumbs-date-archive-day']   = array_merge( $baseTags, [ 'breadcrumb_date_archive_day' ] );

		$contexts['breadcrumbs-format-archive'] = [ 'breadcrumb_archive_post_type_name' ];
		$contexts['breadcrumbs-format-search']  = [ 'breadcrumb_search_string' ];
		$contexts['breadcrumbs-format-paged']   = [ 'breadcrumb_format_page_number' ];

		return $contexts;
	}
}