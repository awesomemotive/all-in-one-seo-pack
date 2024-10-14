<?php
namespace AIOSEO\Plugin\Common\Utils;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class to replace tag values with their data counterparts.
 *
 * @since 4.0.0
 */
class Tags {
	/**
	 * An array of tag values that we support.
	 *
	 * @since 4.0.0
	 *
	 * @var array
	 */
	public $tags = [];

	/**
	 * Specifies the denotation character for the tags.
	 *
	 * @since 4.0.0
	 *
	 * @var string
	 */
	public $denotationChar = '#';

	/**
	 * An array of contexts to separate tags.
	 *
	 * @since 4.0.0
	 *
	 * @var array
	 */
	private $context = [
		'authorDescription'   => [
			'author_bio',
			'author_first_name',
			'author_last_name',
			'author_name',
			'current_date',
			'current_day',
			'current_month',
			'current_year',
			'custom_field',
			'separator_sa',
			'site_title',
			'tagline'
		],
		'authorTitle'         => [
			'author_first_name',
			'author_last_name',
			'author_name',
			'current_date',
			'current_day',
			'current_month',
			'current_year',
			'custom_field',
			'separator_sa',
			'site_title',
			'tagline'
		],
		'descriptionFormat'   => [
			'current_date',
			'current_day',
			'current_month',
			'current_year',
			'custom_field',
			'description',
			'post_date',
			'post_month',
			'post_title',
			'post_year',
			'separator_sa',
			'site_title',
			'tagline'
		],
		'dateDescription'     => [
			'archive_date',
			'archive_title',
			'current_date',
			'current_day',
			'current_month',
			'current_year',
			'custom_field',
			'post_day',
			'post_month',
			'post_year',
			'separator_sa',
			'site_title',
			'tagline'
		],
		'dateTitle'           => [
			'archive_date',
			'archive_title',
			'current_date',
			'current_day',
			'current_month',
			'current_year',
			'custom_field',
			'post_day',
			'post_month',
			'post_year',
			'separator_sa',
			'site_title',
			'tagline'
		],
		'homePage'            => [
			'author_first_name',
			'author_last_name',
			'author_name',
			'current_date',
			'current_day',
			'current_month',
			'current_year',
			'post_date',
			'post_day',
			'post_excerpt_only',
			'post_excerpt',
			'post_month',
			'post_title',
			'post_year',
			'separator_sa',
			'site_title',
			'tagline'
		],
		'knowledgeGraph'      => [
			'separator_sa',
			'site_title',
			'tagline'
		],
		'pagedFormat'         => [
			'page_number',
			'separator_sa'
		],
		'postDescription'     => [
			'author_first_name',
			'author_last_name',
			'author_name',
			'current_date',
			'current_day',
			'current_month',
			'current_year',
			'custom_field',
			'permalink',
			'post_content',
			'post_date',
			'post_day',
			'post_excerpt_only',
			'post_excerpt',
			'post_month',
			'post_title',
			'post_year',
			'separator_sa',
			'site_title',
			'tagline',
			'tax_name',
			'taxonomy_title'
		],
		'postTitle'           => [
			'author_first_name',
			'author_last_name',
			'author_name',
			'categories',
			'current_date',
			'current_day',
			'current_month',
			'current_year',
			'custom_field',
			'permalink',
			'post_content',
			'post_date',
			'post_day',
			'post_excerpt_only',
			'post_excerpt',
			'post_month',
			'post_title',
			'post_year',
			'separator_sa',
			'site_title',
			'tagline',
			'tax_name',
			'taxonomy_title'
		],
		'rss'                 => [
			'author_link',
			'author_link_alt',
			'author_name',
			'featured_image',
			'post_date',
			'post_link',
			'post_link_alt',
			'post_title',
			'site_link',
			'site_link_alt',
			'site_title',
			'taxonomy_title'
		],
		'schema'              => [
			'author_first_name',
			'author_last_name',
			'author_name',
			'author_url',
			'categories',
			'current_date',
			'current_day',
			'current_month',
			'current_year',
			'custom_field',
			'permalink',
			'post_content',
			'post_date',
			'post_day',
			'post_excerpt_only',
			'post_excerpt',
			'post_month',
			'post_title',
			'post_year',
			'separator_sa',
			'site_title',
			'tagline',
			'tax_name',
			'taxonomy_title'
		],
		'searchDescription'   => [
			'current_date',
			'current_day',
			'current_month',
			'current_year',
			'custom_field',
			'search_term',
			'separator_sa',
			'site_title',
			'tagline'
		],
		'searchTitle'         => [
			'current_date',
			'current_day',
			'current_month',
			'current_year',
			'custom_field',
			'search_term',
			'separator_sa',
			'site_title',
			'tagline'
		],
		'siteDescription'     => [
			'current_date',
			'current_day',
			'current_month',
			'current_year',
			'permalink',
			'post_date',
			'post_day',
			'post_month',
			'post_year',
			'search_term',
			'separator_sa',
			'tagline'
		],
		'siteTitle'           => [
			'current_date',
			'current_day',
			'current_month',
			'current_year',
			'permalink',
			'post_date',
			'post_day',
			'post_month',
			'post_year',
			'search_term',
			'separator_sa',
			'tagline'
		],
		'taxonomyDescription' => [
			'current_date',
			'current_day',
			'current_month',
			'current_year',
			'custom_field',
			'permalink',
			'separator_sa',
			'site_title',
			'tagline',
			'taxonomy_description',
			'taxonomy_title'
		],
		'taxonomyTitle'       => [
			'current_date',
			'current_day',
			'current_month',
			'current_year',
			'custom_field',
			'permalink',
			'separator_sa',
			'site_title',
			'tagline',
			'tax_parent_name',
			'taxonomy_description',
			'taxonomy_title'
		]
	];

	/**
	 * Class Contructor.
	 *
	 * @since 4.0.0
	 */
	public function __construct() {
		$this->tags = [
			[
				'id'          => 'alt_tag',
				'name'        => __( 'Image Alt Tag', 'all-in-one-seo-pack' ),
				'description' => __( 'Your image\'s alt tag attribute.', 'all-in-one-seo-pack' )
			],
			[
				'id'          => 'attachment_caption',
				'name'        => __( 'Media Caption', 'all-in-one-seo-pack' ),
				'description' => __( 'Caption for the current media file.', 'all-in-one-seo-pack' )
			],
			[
				'id'          => 'attachment_description',
				'name'        => __( 'Media Description', 'all-in-one-seo-pack' ),
				'description' => __( 'Description for the current media file.', 'all-in-one-seo-pack' )
			],
			[
				'id'          => 'archive_date',
				'name'        => __( 'Archive Date', 'all-in-one-seo-pack' ),
				'description' => __( 'The date of the current archive, localized.', 'all-in-one-seo-pack' )
			],
			[
				'id'          => 'author_link',
				'name'        => __( 'Author Link', 'all-in-one-seo-pack' ),
				'description' => __( 'Author archive link (name as text).', 'all-in-one-seo-pack' )
			],
			[
				'id'          => 'author_link_alt',
				'name'        => __( 'Author Link (Alt)', 'all-in-one-seo-pack' ),
				'description' => __( 'Author archive link (link as text).', 'all-in-one-seo-pack' )
			],
			[
				'id'          => 'author_bio',
				'name'        => __( 'Author Biography', 'all-in-one-seo-pack' ),
				'description' => __( 'The biography of the author.', 'all-in-one-seo-pack' )
			],
			[
				'id'          => 'author_name',
				'name'        => __( 'Author Name', 'all-in-one-seo-pack' ),
				'description' => __( 'The display name of the post author.', 'all-in-one-seo-pack' )
			],
			[
				'id'          => 'author_first_name',
				'name'        => __( 'Author First Name', 'all-in-one-seo-pack' ),
				'description' => __( 'The first name of the post author.', 'all-in-one-seo-pack' )
			],
			[
				'id'          => 'author_last_name',
				'name'        => __( 'Author Last Name', 'all-in-one-seo-pack' ),
				'description' => __( 'The last name of the post author.', 'all-in-one-seo-pack' )
			],
			[
				'id'          => 'author_url',
				'name'        => __( 'Author URL', 'all-in-one-seo-pack' ),
				'description' => __( 'The URL of the author page.', 'all-in-one-seo-pack' )
			],
			[
				'id'          => 'archive_title',
				'name'        => __( 'Archive Title', 'all-in-one-seo-pack' ),
				'description' => __( 'The title of the current archive.', 'all-in-one-seo-pack' )
			],
			[
				'id'          => 'blog_link',
				'name'        => __( 'Site Link', 'all-in-one-seo-pack' ),
				'description' => __( 'Site link (link as text).', 'all-in-one-seo-pack' ),
				'html'        => true
			],
			[
				'id'          => 'blog_title',
				'name'        => __( 'Site Title', 'all-in-one-seo-pack' ),
				'description' => __( 'Your site title.', 'all-in-one-seo-pack' ),
				'deprecated'  => true
			],
			[
				'id'          => 'category',
				'name'        => __( 'Category', 'all-in-one-seo-pack' ),
				'description' => __( 'Current or first category title.', 'all-in-one-seo-pack' ),
				'deprecated'  => true
			],
			[
				'id'          => 'categories',
				'name'        => __( 'Categories', 'all-in-one-seo-pack' ),
				'description' => __( 'All categories that are assigned to the current post, comma-separated.', 'all-in-one-seo-pack' )
			],
			[
				'id'          => 'category_link',
				// Translators: 1 - The type of page (Post, Page, Category, Tag, etc.).
				'name'        => sprintf( __( '%1$s Link', 'all-in-one-seo-pack' ), 'Category' ),
				'description' => __( 'Current or first term link (name as text).', 'all-in-one-seo-pack' ),
				'html'        => true
			],
			[
				'id'          => 'category_link_alt',
				// Translators: 1 - The type of page (Post, Page, Category, Tag, etc.).
				'name'        => sprintf( __( '%1$s Link (Alt)', 'all-in-one-seo-pack' ), 'Category' ),
				'description' => __( 'Current or first term link (link as text).', 'all-in-one-seo-pack' ),
				'html'        => true
			],
			[
				'id'          => 'current_date',
				'name'        => __( 'Current Date', 'all-in-one-seo-pack' ),
				'description' => __( 'The current date, localized.', 'all-in-one-seo-pack' )
			],
			[
				'id'          => 'current_day',
				'name'        => __( 'Current Day', 'all-in-one-seo-pack' ),
				'description' => __( 'The current day of the month, localized.', 'all-in-one-seo-pack' )
			],
			[
				'id'          => 'current_month',
				'name'        => __( 'Current Month', 'all-in-one-seo-pack' ),
				'description' => __( 'The current month, localized.', 'all-in-one-seo-pack' )
			],
			[
				'id'          => 'current_year',
				'name'        => __( 'Current Year', 'all-in-one-seo-pack' ),
				'description' => __( 'The current year, localized.', 'all-in-one-seo-pack' )
			],
			[
				'id'          => 'custom_field',
				'name'        => __( 'Custom Field', 'all-in-one-seo-pack' ),
				'description' => __( 'A custom field from the current page/post.', 'all-in-one-seo-pack' ),
				'custom'      => true
			],
			[
				'id'          => 'description',
				'name'        => __( 'Description', 'all-in-one-seo-pack' ),
				'description' => __( 'The meta description for the current page/post.', 'all-in-one-seo-pack' )
			],
			[
				'id'          => 'featured_image',
				'name'        => __( 'Featured Image', 'all-in-one-seo-pack' ),
				'description' => __( 'The featured image of the current page/post.', 'all-in-one-seo-pack' )
			],
			[
				'id'          => 'page_number',
				'name'        => __( 'Page Number', 'all-in-one-seo-pack' ),
				'description' => __( 'The page number for the current paginated page.', 'all-in-one-seo-pack' )
			],
			[
				'id'          => 'parent_title',
				'name'        => __( 'Parent Title', 'all-in-one-seo-pack' ),
				'description' => __( 'The title of the parent post of the current page/post.', 'all-in-one-seo-pack' )
			],
			[
				'id'          => 'permalink',
				'name'        => __( 'Permalink', 'all-in-one-seo-pack' ),
				'description' => __( 'The permalink for the current page/post.', 'all-in-one-seo-pack' )
			],
			[
				'id'          => 'post_content',
				// Translators: 1 - The singular name of the post type.
				'name'        => sprintf( __( '%1$s Content', 'all-in-one-seo-pack' ), 'Post' ),
				'description' => __( 'The content of your page/post.', 'all-in-one-seo-pack' )
			],
			[
				'id'          => 'post_date',
				// Translators: 1 - The singular name of the post type.
				'name'        => sprintf( __( '%1$s Date', 'all-in-one-seo-pack' ), 'Post' ),
				'description' => __( 'The date when the page/post was published, localized.', 'all-in-one-seo-pack' )
			],
			[
				'id'          => 'post_day',
				// Translators: 1 - The singular name of the post type.
				'name'        => sprintf( __( '%1$s Day', 'all-in-one-seo-pack' ), 'Post' ),
				'description' => __( 'The day of the month when the page/post was published, localized.', 'all-in-one-seo-pack' )
			],
			[
				'id'          => 'post_excerpt',
				// Translators: 1 - The singular name of the post type.
				'name'        => sprintf( __( '%1$s Excerpt', 'all-in-one-seo-pack' ), 'Post' ),
				'description' => __( 'The excerpt defined on your page/post.', 'all-in-one-seo-pack' )
			],
			[
				'id'          => 'post_excerpt_only',
				// Translators: 1 - The singular name of the post type.
				'name'        => sprintf( __( '%1$s Excerpt Only', 'all-in-one-seo-pack' ), 'Post' ),
				'description' => __( 'The excerpt defined on your page/post. Will not fall back to the post content.', 'all-in-one-seo-pack' )
			],
			[
				'id'          => 'post_month',
				// Translators: 1 - The singular name of the post type.
				'name'        => sprintf( __( '%1$s Month', 'all-in-one-seo-pack' ), 'Post' ),
				'description' => __( 'The month when the page/post was published, localized.', 'all-in-one-seo-pack' )
			],
			[
				'id'          => 'post_year',
				// Translators: 1 - The singular name of the post type.
				'name'        => sprintf( __( '%1$s Year', 'all-in-one-seo-pack' ), 'Post' ),
				'description' => __( 'The year when the page/post was published, localized.', 'all-in-one-seo-pack' )
			],
			[
				'id'          => 'post_link',
				// Translators: 1 - The type of page (Post, Page, Category, Tag, etc.).
				'name'        => sprintf( __( '%1$s Link', 'all-in-one-seo-pack' ), 'Post' ),
				'description' => __( 'Post link (name as anchor text).', 'all-in-one-seo-pack' ),
				'html'        => true
			],
			[
				'id'          => 'post_link_alt',
				// Translators: 1 - The type of page (Post, Page, Category, Tag, etc.).
				'name'        => sprintf( __( '%1$s Link (Alt)', 'all-in-one-seo-pack' ), 'Post' ),
				'description' => __( 'Post link (link as anchor text).', 'all-in-one-seo-pack' ),
				'html'        => true
			],
			[
				'id'          => 'post_title',
				// Translators: 1 - The type of page (Post, Page, Category, Tag, etc.).
				'name'        => sprintf( __( '%1$s Title', 'all-in-one-seo-pack' ), 'Post' ),
				'description' => __( 'The original title of the current post.', 'all-in-one-seo-pack' )
			],
			[
				'id'          => 'search_term',
				'name'        => __( 'Search Term', 'all-in-one-seo-pack' ),
				'description' => __( 'The term the user is searching for.', 'all-in-one-seo-pack' )
			],
			[
				'id'          => 'separator_sa',
				'name'        => __( 'Separator', 'all-in-one-seo-pack' ),
				'description' => __( 'The separator defined in the search appearance settings.', 'all-in-one-seo-pack' )
			],
			[
				'id'          => 'site_description',
				'name'        => __( 'Site Description', 'all-in-one-seo-pack' ),
				'description' => __( 'The description for your site.', 'all-in-one-seo-pack' ),
				'deprecated'  => true
			],
			[
				'id'          => 'site_link',
				'name'        => __( 'Site Link', 'all-in-one-seo-pack' ),
				'description' => __( 'Site link (name as text).', 'all-in-one-seo-pack' ),
				'html'        => true
			],
			[
				'id'          => 'site_link_alt',
				'name'        => __( 'Site Link (Alt)', 'all-in-one-seo-pack' ),
				'description' => __( 'Site link (link as text).', 'all-in-one-seo-pack' ),
				'html'        => true
			],
			[
				'id'          => 'site_title',
				'name'        => __( 'Site Title', 'all-in-one-seo-pack' ),
				'description' => __( 'Your site title.', 'all-in-one-seo-pack' ),
				'html'        => true
			],
			[
				'id'          => 'tagline',
				'name'        => __( 'Tagline', 'all-in-one-seo-pack' ),
				'description' => __( 'The tagline for your site, set in the general settings.', 'all-in-one-seo-pack' )
			],
			[
				'id'          => 'tax_name',
				'name'        => __( 'Taxonomy Name', 'all-in-one-seo-pack' ),
				'description' => __( 'The name of the first term of a given taxonomy that is assigned to the current page/post.', 'all-in-one-seo-pack' ),
				'custom'      => true
			],
			[
				'id'          => 'tax_parent_name',
				'name'        => __( 'Parent Term', 'all-in-one-seo-pack' ),
				'description' => __( 'The name of the parent term of the current term.', 'all-in-one-seo-pack' ),
			],
			[
				'id'          => 'taxonomy_description',
				// Translators: 1 - The singular name of the current taxonomy.
				'name'        => sprintf( __( '%1$s Description', 'all-in-one-seo-pack' ), 'Category' ),
				'description' => __( 'The description of the primary term, first assigned term or the current term.', 'all-in-one-seo-pack' )
			],
			[
				'id'          => 'taxonomy_title',
				// Translators: 1 - The type of page (Post, Page, Category, Tag, etc.).
				'name'        => sprintf( __( '%1$s Title', 'all-in-one-seo-pack' ), 'Category' ),
				'description' => __( 'The title of the primary term, first assigned term or the current term.', 'all-in-one-seo-pack' )
			]
		];
	}

	/**
	 * Returns all the tags.
	 *
	 * @since 4.0.0
	 *
	 * @param  bool  $sampleData Whether or not to fill empty values with sample data.
	 * @return array             An array of tags.
	 */
	public function all( $sampleData = false ) {
		$tags = $this->tags;
		foreach ( $tags as $key => $tag ) {
			$tags[ $key ]['value'] = $this->getTagValue( $tag, null, $sampleData );
		}

		usort( $tags, function ( $a, $b ) {
			return $a['name'] < $b['name']
				? -1
				: ( $a['name'] > $b['name'] ? 1 : 0 );
		} );

		return [
			'tags'    => $tags,
			'context' => $this->getContext()
		];
	}

	/**
	 * Add the context for all the post/page types.
	 *
	 * @since 4.0.0
	 *
	 * @return array An array of contextual data.
	 */
	public function getContext() {
		$context = $this->context;

		// Post types including CPT's.
		foreach ( aioseo()->helpers->getPublicPostTypes() as $postType ) {
			if ( 'post' === $postType['name'] ) {
				continue;
			}

			if ( $postType['hasArchive'] ) {
				$context[ $postType['name'] . 'ArchiveTitle' ]       = $context['dateTitle'];
				$context[ $postType['name'] . 'ArchiveDescription' ] = $context['dateDescription'];
			}

			$context[ $postType['name'] . 'Title' ]       = $context['postTitle'];
			$context[ $postType['name'] . 'Description' ] = $context['postDescription'];

			// Check if the post type has an excerpt.
			if ( empty( $postType['hasExcerpt'] ) ) {
				$phpTitleKey = array_search( 'post_excerpt', $context[ $postType['name'] . 'Title' ], true );
				if ( false !== $phpTitleKey ) {
					unset( $context[ $postType['name'] . 'Title' ][ $phpTitleKey ] );
				}

				$phpTitleKey = array_search( 'post_excerpt_only', $context[ $postType['name'] . 'Title' ], true );
				if ( false !== $phpTitleKey ) {
					unset( $context[ $postType['name'] . 'Title' ][ $phpTitleKey ] );
				}

				$phpDescriptionKey = array_search( 'post_excerpt', $context[ $postType['name'] . 'Description' ], true );
				if ( false !== $phpDescriptionKey ) {
					unset( $context[ $postType['name'] . 'Description' ][ $phpDescriptionKey ] );
				}

				$phpDescriptionKey = array_search( 'post_excerpt_only', $context[ $postType['name'] . 'Description' ], true );
				if ( false !== $phpDescriptionKey ) {
					unset( $context[ $postType['name'] . 'Description' ][ $phpDescriptionKey ] );
				}

				asort( $context[ $postType['name'] . 'Title' ] );
				$context[ $postType['name'] . 'Title' ] = array_values( $context[ $postType['name'] . 'Title' ] );
				asort( $context[ $postType['name'] . 'Description' ] );
				$context[ $postType['name'] . 'Description' ] = array_values( $context[ $postType['name'] . 'Description' ] );
			}

			if ( 'page' === $postType['name'] ) {
				$phpTitleKey = array_search( 'taxonomy_title', $context['pageTitle'], true );
				if ( false !== $phpTitleKey ) {
					unset( $context['pageTitle'][ $phpTitleKey ] );
				}

				$phpTitleKey = array_search( 'category', $context['pageTitle'], true );
				if ( false !== $phpTitleKey ) {
					unset( $context['pageTitle'][ $phpTitleKey ] );
				}

				$phpDescriptionKey = array_search( 'taxonomy_title', $context['pageDescription'], true );
				if ( false !== $phpDescriptionKey ) {
					unset( $context['pageDescription'][ $phpDescriptionKey ] );
				}

				$phpDescriptionKey = array_search( 'category', $context['pageDescription'], true );
				if ( false !== $phpDescriptionKey ) {
					unset( $context['pageDescription'][ $phpDescriptionKey ] );
				}

				$context['pageTitle']       = array_values( $context['pageTitle'] );
				$context['pageDescription'] = array_values( $context['pageDescription'] );

				asort( $context['pageTitle'] );
				$context['pageTitle'] = array_values( $context['pageTitle'] );
				asort( $context['pageDescription'] );
				$context['pageDescription'] = array_values( $context['pageDescription'] );
			}

			if ( 'attachment' === $postType['name'] ) {
				$context['attachmentTitle'][] = 'alt_tag';
				asort( $context['attachmentTitle'] );
				$context['attachmentTitle'] = array_values( $context['attachmentTitle'] );
				$context['attachmentDescription'][] = 'alt_tag';
				asort( $context['attachmentDescription'] );
				$context['attachmentDescription'] = array_values( $context['attachmentDescription'] );

				$phpTitleKey = array_search( 'taxonomy_title', $context['attachmentTitle'], true );
				if ( false !== $phpTitleKey ) {
					unset( $context['attachmentTitle'][ $phpTitleKey ] );
				}

				$phpTitleKey = array_search( 'post_content', $context['attachmentTitle'], true );
				if ( false !== $phpTitleKey ) {
					unset( $context['attachmentTitle'][ $phpTitleKey ] );
				}

				$phpTitleKey = array_search( 'post_excerpt', $context['attachmentTitle'], true );
				if ( false !== $phpTitleKey ) {
					unset( $context['attachmentTitle'][ $phpTitleKey ] );
				}

				$phpTitleKey = array_search( 'post_excerpt_only', $context['attachmentTitle'], true );
				if ( false !== $phpTitleKey ) {
					unset( $context['attachmentTitle'][ $phpTitleKey ] );
				}

				$phpDescriptionKey = array_search( 'taxonomy_title', $context['attachmentDescription'], true );
				if ( false !== $phpDescriptionKey ) {
					unset( $context['attachmentDescription'][ $phpDescriptionKey ] );
				}

				$phpDescriptionKey = array_search( 'post_content', $context['attachmentDescription'], true );
				if ( false !== $phpDescriptionKey ) {
					unset( $context['attachmentDescription'][ $phpDescriptionKey ] );
				}

				$phpDescriptionKey = array_search( 'post_excerpt', $context['attachmentDescription'], true );
				if ( false !== $phpDescriptionKey ) {
					unset( $context['attachmentDescription'][ $phpDescriptionKey ] );
				}

				$phpDescriptionKey = array_search( 'post_excerpt_only', $context['attachmentDescription'], true );
				if ( false !== $phpDescriptionKey ) {
					unset( $context['attachmentDescription'][ $phpDescriptionKey ] );
				}

				$context['attachmentTitle']       = array_merge( $context['attachmentTitle'], [ 'attachment_caption', 'attachment_description' ] );
				$context['attachmentDescription'] = array_merge( $context['attachmentDescription'], [ 'attachment_caption', 'attachment_description' ] );

				asort( $context['attachmentTitle'] );
				$context['attachmentTitle'] = array_values( $context['attachmentTitle'] );
				asort( $context['attachmentDescription'] );
				$context['attachmentDescription'] = array_values( $context['attachmentDescription'] );
			}

			if ( ! in_array( 'category', get_object_taxonomies( $postType['name'] ), true ) ) {
				$phpTitleKey = array_search( 'categories', $context[ $postType['name'] . 'Title' ], true );
				if ( false !== $phpTitleKey ) {
					unset( $context[ $postType['name'] . 'Title' ][ $phpTitleKey ] );
				}

				$phpTitleKey = array_search( 'categories', $context[ $postType['name'] . 'Description' ], true );
				if ( false !== $phpTitleKey ) {
					unset( $context[ $postType['name'] . 'Description' ][ $phpTitleKey ] );
				}

				asort( $context[ $postType['name'] . 'Title' ] );
				$context[ $postType['name'] . 'Title' ] = array_values( $context[ $postType['name'] . 'Title' ] );
				asort( $context[ $postType['name'] . 'Description' ] );
				$context[ $postType['name'] . 'Description' ] = array_values( $context[ $postType['name'] . 'Description' ] );
			}

			if ( $postType['hierarchical'] ) {
				$context[ $postType['name'] . 'Title' ][] = 'parent_title';
			}
		}

		// Taxonomies including from CPT's.
		foreach ( aioseo()->helpers->getPublicTaxonomies() as $taxonomy ) {
			$context[ $taxonomy['name'] . 'Title' ]       = $context['taxonomyTitle'];
			$context[ $taxonomy['name'] . 'Description' ] = $context['taxonomyDescription'];
		}

		return $context;
	}

	/**
	 * Replace the tags in the string provided.
	 *
	 * @since 4.0.0
	 *
	 * @param  string $string The string to look for tags in.
	 * @param  int    $id     The page or post ID.
	 * @return string         The string with tags replaced.
	 */
	public function replaceTags( $string, $id = 0 ) {
		if ( ! $string || ! preg_match( '/#/', $string ) ) {
			return $string;
		}

		foreach ( $this->tags as $tag ) {
			if ( 'custom_field' === $tag['id'] || 'tax_name' === $tag['id'] ) {
				continue;
			}

			$tagId = $this->denotationChar . $tag['id'];
			// Pattern explained: Exact match of tag, not followed by any additional letter, number or underscore.
			// This allows us to have tags like: #post_link and #post_link_alt
			// and it will always replace the correct one.
			$pattern = "/$tagId(?![a-zA-Z0-9_])/im";
			if ( preg_match( $pattern, $string ) ) {
				$tagValue = $this->getTagValue( $tag, $id );
				$string   = preg_replace( $pattern, '%|%' . aioseo()->helpers->escapeRegexReplacement( $tagValue ), $string );
			}
		}

		$string = $this->parseTaxonomyNames( $string, $id );

		// Custom fields are parsed separately.
		$string = $this->parseCustomFields( $string, $id );

		return preg_replace( '/%\|%/im', '', $string );
	}

	/**
	 * Get the value of the tag to replace.
	 *
	 * @since 4.0.0
	 *
	 * @param  string $tag        The tag to look for.
	 * @param  int    $id         The post ID.
	 * @param  bool   $sampleData Whether or not to fill empty values with sample data.
	 * @return mixed              The value of the tag.
	 */
	public function getTagValue( $tag, $id, $sampleData = false ) {
		$author   = new \WP_User();
		$post     = aioseo()->helpers->getPost( $id );
		$postId   = null;
		$category = null;
		if ( $post ) {
			$author   = new \WP_User( $post->post_author );
			$postId   = empty( $id ) ? $post->ID : $id;
			$category = get_the_category( $postId );
		} elseif ( is_author() && is_a( get_queried_object(), 'WP_User' ) ) {
			$author = get_queried_object();
		}

		switch ( $tag['id'] ) {
			case 'alt_tag':
				return empty( $id )
					? ( $sampleData ? __( 'A sample alt tag for your image', 'all-in-one-seo-pack' ) : '' )
					: get_post_meta( $id, '_wp_attachment_image_alt', true );
			case 'archive_date':
				$date = null;
				if ( is_year() ) {
					$date = get_the_date( 'Y' );
				}
				if ( is_month() ) {
					$date = get_the_date( 'F, Y' );
				}
				if ( is_day() ) {
					$date = get_the_date();
				}
				if ( $sampleData ) {
					$date = $this->formatDateAsI18n( date_i18n( 'U' ) );
				}
				if ( ! empty( $date ) ) {
					return $date;
				}

				break;
			case 'archive_title':
				$title = is_post_type_archive() ? post_type_archive_title( '', false ) : get_the_archive_title();

				return $sampleData ? __( 'Sample Archive Title', 'all-in-one-seo-pack' ) : wp_strip_all_tags( $title );
			case 'author_bio':
				$bio = get_the_author_meta( 'description', $author->ID );

				return empty( $bio ) && $sampleData ? __( 'Sample author biography', 'all-in-one-seo-pack' ) : $bio;
			case 'author_first_name':
				$name = $author->first_name;

				return empty( $name ) && $sampleData ? wp_get_current_user()->first_name : $author->first_name;
			case 'author_last_name':
				$name = $author->last_name;

				return empty( $name ) && $sampleData ? wp_get_current_user()->last_name : $author->last_name;
			case 'author_link':
				return '<a href="' . esc_url( get_author_posts_url( $author->ID ) ) . '">' . esc_html( $author->display_name ) . '</a>';
			case 'author_link_alt':
				return '<a href="' . esc_url( get_author_posts_url( $author->ID ) ) . '">' . esc_url( get_author_posts_url( $author->ID ) ) . '</a>';
			case 'author_name':
				$name = $author->display_name;

				return empty( $name ) && $sampleData ? wp_get_current_user()->display_name : $author->display_name;
			case 'author_url':
				$authorUrl = get_author_posts_url( $author->ID );

				return ! empty( $authorUrl ) ? $authorUrl : '';
			case 'attachment_caption':
				$caption = wp_get_attachment_caption( $postId );

				return empty( $caption ) && $sampleData ? __( 'Sample caption for media.', 'all-in-one-seo-pack' ) : $caption;
			case 'attachment_description':
				$description = ! empty( $post->post_content ) ? $post->post_content : '';

				return empty( $description ) && $sampleData ? __( 'Sample description for media.', 'all-in-one-seo-pack' ) : $description;
			case 'categories':
				if ( ! is_object( $post ) || 'post' !== $post->post_type ) {
					return ! is_object( $post ) && $sampleData ? __( 'Sample Category 1, Sample Category 2', 'all-in-one-seo-pack' ) : '';
				}
				$categories = get_the_terms( $post->ID, 'category' );

				$names = [];
				if ( ! is_array( $categories ) ) {
					return '';
				}

				foreach ( $categories as $category ) {
					$names[] = $category->name;
				}

				return implode( ', ', $names );
			case 'category_link':
				return '<a href="' . esc_url( get_category_link( $category ) ) . '">' . ( $category ? $category[0]->name : '' ) . '</a>';
			case 'category_link_alt':
				return '<a href="' . esc_url( get_category_link( $category ) ) . '">' . esc_url( get_category_link( $category ) ) . '</a>';
			case 'current_date':
				return $this->formatDateAsI18n( date_i18n( 'U' ) );
			case 'current_day':
				return date_i18n( 'd' );
			case 'current_month':
				return date_i18n( 'F' );
			case 'current_year':
				return date_i18n( 'Y' );
			case 'custom_field':
				return $sampleData ? __( 'Sample Custom Field Value', 'all-in-one-seo-pack' ) : '';
			case 'featured_image':
				if ( ! has_post_thumbnail( $postId ) ) {
					return $sampleData ? __( 'Sample featured image', 'all-in-one-seo-pack' ) : '';
				}

				$imageId = get_post_thumbnail_id( $postId );
				$image   = (array) wp_get_attachment_image_src( $imageId, 'full' );
				$image   = isset( $image[0] ) ? '<img src="' . $image[0] . '" style="display: block; margin: 1em auto">' : '';

				return $sampleData ? __( 'Sample featured image', 'all-in-one-seo-pack' ) : $image;
			case 'page_number':
				return aioseo()->helpers->getPageNumber();
			case 'parent_title':
				if ( ! is_object( $post ) || ! $post->post_parent ) {
					return ! is_object( $post ) && $sampleData ? __( 'Sample Parent', 'all-in-one-seo-pack' ) : '';
				}
				$parent = get_post( $post->post_parent );

				return $parent ? $parent->post_title : '';
			case 'permalink':
				return aioseo()->helpers->getUrl();
			case 'post_date':
				$date = $this->formatDateAsI18n( get_the_date( 'U' ) );

				return empty( $date ) && $sampleData ? $this->formatDateAsI18n( date_i18n( 'U' ) ) : $date;
			case 'post_day':
				$day = get_the_date( 'd', $post );

				return empty( $day ) && $sampleData ? date_i18n( 'd' ) : $day;
			case 'post_excerpt_only':
				return empty( $postId ) ? ( $sampleData ? __( 'Sample excerpt from a page/post.', 'all-in-one-seo-pack' ) : '' ) : $post->post_excerpt;
			case 'post_excerpt':
				if ( empty( $postId ) ) {
					return $sampleData ? __( 'Sample excerpt from a page/post.', 'all-in-one-seo-pack' ) : '';
				}

				if ( $post->post_excerpt ) {
					return $post->post_excerpt;
				}

				// Fall through if the post doesn't have an excerpt set. In that case getDescriptionFromContent() will generate it for us.
			case 'post_content':
				return empty( $postId ) ? ( $sampleData ? __( 'An example of content from your page/post.', 'all-in-one-seo-pack' ) : '' ) : aioseo()->helpers->getDescriptionFromContent( $post );
			case 'post_link':
				return '<a href="' . esc_url( get_permalink( $post ) ) . '">' . esc_html( get_the_title( $post ) ) . '</a>';
			case 'post_link_alt':
				return '<a href="' . esc_url( get_permalink( $post ) ) . '">' . esc_url( get_permalink( $post ) ) . '</a>';
			case 'post_month':
				$month = get_the_date( 'F', $post );

				return empty( $month ) && $sampleData ? date_i18n( 'F' ) : $month;
			case 'post_title':
				$title = esc_html( get_the_title( $post ) );

				return empty( $title ) && $sampleData ? __( 'Sample Post', 'all-in-one-seo-pack' ) : $title;
			case 'post_year':
				$year = get_the_date( 'Y', $post );

				return empty( $year ) && $sampleData ? date_i18n( 'Y' ) : $year;
			case 'search_term':
				$search = get_search_query();

				return empty( $search ) && $sampleData ? __( 'Example search string', 'all-in-one-seo-pack' ) : esc_attr( stripslashes( $search ) );
			case 'separator_sa':
				return aioseo()->helpers->decodeHtmlEntities( aioseo()->options->searchAppearance->global->separator );
			case 'site_link':
			case 'blog_link':
				return '<a href="' . esc_url( get_bloginfo( 'url' ) ) . '">' . esc_html( get_bloginfo( 'name' ) ) . '</a>';
			case 'site_link_alt':
				return '<a href="' . esc_url( get_bloginfo( 'url' ) ) . '">' . esc_url( get_bloginfo( 'url' ) ) . '</a>';
			case 'tag':
				return single_term_title( '', false );
			case 'tax_name':
				return $sampleData ? __( 'Sample Taxonomy Name Value', 'all-in-one-seo-pack' ) : '';
			case 'tax_parent_name':
				$termObject       = get_term( $id );
				$parentTermObject = ! empty( $termObject->parent ) ? get_term( $termObject->parent ) : '';
				$name             = is_a( $parentTermObject, 'WP_Term' ) && ! empty( $parentTermObject->name ) ? $parentTermObject->name : '';

				return $sampleData ? __( 'Sample Parent Term Name', 'all-in-one-seo-pack' ) : $name;
			case 'taxonomy_description':
				$description = term_description();

				return empty( $description ) && $sampleData ? __( 'Sample taxonomy description', 'all-in-one-seo-pack' ) : $description;
			case 'taxonomy_title':
			case 'category':
				$title = $this->getTaxonomyTitle( $postId );

				return ! $title && $sampleData ? __( 'Sample Taxonomy Title', 'all-in-one-seo-pack' ) : $title;
			case 'site_description':
			case 'blog_description':
			case 'tagline':
				return aioseo()->helpers->decodeHtmlEntities( get_bloginfo( 'description' ) );
			case 'site_title':
			case 'blog_title':
				return aioseo()->helpers->decodeHtmlEntities( get_bloginfo( 'name' ) );
			default:
				return '';
		}
	}

	/**
	 * Get the category title.
	 *
	 * @since 4.0.0
	 *
	 * @param  integer $postId The post ID if set.
	 * @return string          The category title.
	 */
	private function getTaxonomyTitle( $postId = null ) {
		$isWcActive = aioseo()->helpers->isWooCommerceActive();
		$title      = '';
		if ( $isWcActive && is_product_category() ) {
			$title = single_cat_title( '', false );
		} elseif ( is_category() ) {
			$title = single_cat_title( '', false );
		} elseif ( is_tag() ) {
			$title = single_tag_title( '', false );
		} elseif ( is_author() ) {
			$title = get_the_author();
		} elseif ( is_tax() ) {
			$title = single_term_title( '', false );
		} elseif ( is_post_type_archive() ) {
			$title = post_type_archive_title( '', false );
		} elseif ( is_archive() ) {
			$title = get_the_archive_title();
		}

		if ( $postId ) {
			$currentScreen  = aioseo()->helpers->getCurrentScreen();
			$isProduct      = $isWcActive && ( is_product() || 'product' === ( $currentScreen->post_type ?? '' ) );
			$post           = aioseo()->helpers->getPost( $postId );
			$postTaxonomies = get_object_taxonomies( $post, 'objects' );
			$postTerms      = [];
			foreach ( $postTaxonomies as $taxonomySlug => $taxonomy ) {
				if ( ! $taxonomy->hierarchical ) {
					continue;
				}

				$taxonomySlug = $isProduct ? 'product_cat' : $taxonomySlug;
				$primaryTerm  = aioseo()->standalone->primaryTerm->getPrimaryTerm( $postId, $taxonomySlug );
				if ( $primaryTerm ) {
					$postTerms[] = get_term( $primaryTerm, $taxonomySlug );
					break;
				}

				$postTaxonomyTerms = get_the_terms( $postId, $taxonomySlug );
				if ( is_array( $postTaxonomyTerms ) ) {
					$postTerms = array_merge( $postTerms, $postTaxonomyTerms );
					break;
				}
			}

			$title = $postTerms ? $postTerms[0]->name : '';
		}

		return wp_strip_all_tags( (string) $title );
	}

	/**
	 * Formatted Date
	 *
	 * Get formatted date based on WP options.
	 *
	 * @since 4.0.0
	 *
	 * @param  null|int    $date   Date in UNIX timestamp format. Otherwise, current time.
	 * @return string              Date internationalized.
	 */
	public function formatDateAsI18n( $date = null ) {
		if ( ! $date ) {
			$date = time();
		}

		$format        = get_option( 'date_format' );
		$formattedDate = date_i18n( $format, $date );

		return apply_filters(
			'aioseo_format_date',
			$formattedDate,
			[
				$date,
				$format
			]
		);
	}

	/**
	 * Parses custom taxonomy tags by replacing them with the name of the first assigned term of the given taxonomy.
	 *
	 * @since 4.0.0
	 *
	 * @param  string $string The string to parse.
	 * @return mixed          The new title.
	 */
	private function parseTaxonomyNames( $string, $id ) { // phpcs:ignore VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
		$pattern = '/' . $this->denotationChar . 'tax_name-([a-zA-Z0-9_-]+)/im';
		$string  = preg_replace_callback( $pattern, [ $this, 'replaceTaxonomyName' ], $string );
		$pattern = '/' . $this->denotationChar . 'tax_name(?![a-zA-Z0-9_-])/im';

		return preg_replace( $pattern, '', $string );
	}

	/**
	 * Adds support for using #custom_field-[custom_field_title] for using
	 * custom fields / Advanced Custom Fields in titles / descriptions etc.
	 *
	 * @since 4.0.0
	 *
	 * @param  string $string The string to parse customs fields out of.
	 * @param  int    $postId The page or post ID.
	 * @return mixed          The new title.
	 */
	public function parseCustomFields( $string, $postId = 0 ) {
		$pattern = '/' . $this->denotationChar . 'custom_field-([a-zA-Z0-9_-]+)/im';
		$matches = [];
		preg_match_all( $pattern, $string, $matches, PREG_SET_ORDER );

		$string  = $this->replaceCustomField( $string, $matches, $postId );
		$pattern = '/' . $this->denotationChar . 'custom_field(?![a-zA-Z0-9_-])/im';

		return preg_replace( $pattern, '', $string );
	}

	/**
	 * Add context to our internal context.
	 *
	 * @since 4.0.0
	 *
	 * @param  array $context A context array to append.
	 * @return void
	 */
	public function addContext( $context ) {
		$this->context = array_merge( $this->context, $context );
	}

	/**
	 * Add tags to our internal tags.
	 *
	 * @since 4.0.0
	 *
	 * @param  array $tags A tags array to append.
	 * @return void
	 */
	public function addTags( $tags ) {
		$this->tags = array_merge( $this->tags, $tags );
	}

	/**
	 * Replaces a taxonomy name tag with its respective value.
	 *
	 * @since 4.0.0
	 *
	 * @param  array  $matches The matches.
	 * @return string          The replaced matches.
	 */
	private function replaceTaxonomyName( $matches ) {
		$termName = '';
		$post     = aioseo()->helpers->getPost();
		if ( ! empty( $matches[1] ) && $post ) {
			$taxonomy = get_taxonomy( $matches[1] );
			if ( ! $taxonomy ) {
				return '';
			}

			$term = aioseo()->standalone->primaryTerm->getPrimaryTerm( $post->ID, $taxonomy->name );
			if ( ! $term ) {
				$terms = get_the_terms( $post->ID, $taxonomy->name );
				if ( ! $terms || is_wp_error( $terms ) ) {
					return '';
				}

				$term = array_shift( $terms );
			}

			$termName = $term->name;
		}

		return '%|%' . $termName;
	}

	/**
	 * (ACF) Custom Field Replace.
	 *
	 * @since 4.0.0
	 *
	 * @param  string      $string  The string to parse customs fields out of.
	 * @param  array       $matches Array of matched values.
	 * @param  int         $postId  The page or post ID.
	 * @return bool|string          New title/text.
	 */
	private function replaceCustomField( $string, $matches, $postId ) {
		if ( empty( $matches ) ) {
			return $string;
		}

		foreach ( $matches as $match ) {
			$str = '';
			if ( ! empty( $match[1] ) ) {
				if ( function_exists( 'get_field' ) ) {
					$str = get_field( $match[1], get_queried_object() ?? $postId );
				}

				if ( empty( $str ) ) {
					global $post;
					if ( ! empty( $post ) ) {
						$str = get_post_meta( $post->ID, $match[1], true );
					}
				}
			} else {
				$str = $match[0];
			}

			$str = wp_strip_all_tags( $str );
			$string = str_replace( $match[0], '%|%' . $str, $string );
		}

		return $string;
	}

	/**
	 * Get the default tags for the current post.
	 *
	 * @since 4.0.0
	 *
	 * @param  integer $postId The Post ID.
	 * @return array           An array of tags.
	 */
	public function getDefaultPostTags( $postId ) {
		$post = get_post( $postId );

		$title       = aioseo()->meta->title->getTitle( $post, true );
		$description = aioseo()->meta->description->getDescription( $post, true );

		return [
			'title'       => empty( $title ) ? '' : $title,
			'description' => empty( $description ) ? '' : $description
		];
	}
}