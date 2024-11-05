<?php
namespace AIOSEO\Plugin\Common\Options;

use AIOSEO\Plugin\Common\Traits;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles the dynamic options.
 *
 * @since 4.1.4
 */
class DynamicOptions {
	use Traits\Options;

	/**
	 * The default options.
	 *
	 * @since 4.1.4
	 *
	 * @var array
	 */
	protected $defaults = [
		// phpcs:disable WordPress.Arrays.ArrayDeclarationSpacing.AssociativeArrayFound
		'sitemap'          => [
			'priority' => [
				'postTypes'  => [],
				'taxonomies' => []
			]
		],
		'social'           => [
			'facebook' => [
				'general' => [
					'postTypes' => []
				]
			]
		],
		'searchAppearance' => [
			'postTypes'  => [],
			'taxonomies' => [],
			'archives'   => []
		]
		// phpcs:enable WordPress.Arrays.ArrayDeclarationSpacing.AssociativeArrayFound
	];

	/**
	 * Class constructor.
	 *
	 * @since 4.1.4
	 *
	 * @param string $optionsName The options name.
	 */
	public function __construct( $optionsName = 'aioseo_options_dynamic' ) {
		$this->optionsName = $optionsName;

		// Load defaults in case this is a complete fresh install.
		$this->init();

		add_action( 'shutdown', [ $this, 'save' ] );
	}

	/**
	 * Initializes the options.
	 *
	 * @since 4.1.4
	 *
	 * @return void
	 */
	protected function init() {
		$this->addDynamicDefaults();
		$this->setDbOptions();
	}

	/**
	 * Sets the DB options.
	 *
	 * @since 4.1.4
	 *
	 * @return void
	 */
	protected function setDbOptions() {
		$dbOptions = $this->getDbOptions( $this->optionsName );

		// Refactor options.
		$this->defaultsMerged = array_replace_recursive( $this->defaults, $this->defaultsMerged );

		$dbOptions = array_replace_recursive(
			$this->defaultsMerged,
			$this->addValueToValuesArray( $this->defaultsMerged, $dbOptions )
		);

		// Remove any post types/taxonomies that are stored in the DB but that aren't active currently.
		// We only have to do this for the dynamic options.
		$dbOptions = $this->filterOptions( $this->defaultsMerged, $dbOptions );

		aioseo()->core->optionsCache->setOptions( $this->optionsName, $dbOptions );

		// Get the localized options.
		$dbOptionsLocalized = get_option( $this->optionsName . '_localized' );
		if ( empty( $dbOptionsLocalized ) ) {
			$dbOptionsLocalized = [];
		}
		$this->localized = $dbOptionsLocalized;
	}

	/**
	 * Sanitizes, then saves the options to the database.
	 *
	 * @since 4.1.4
	 *
	 * @param  array $options An array of options to sanitize, then save.
	 * @return void
	 */
	public function sanitizeAndSave( $options ) {
		if ( ! is_array( $options ) ) {
			return;
		}

		$cachedOptions = aioseo()->core->optionsCache->getOptions( $this->optionsName );

		aioseo()->dynamicBackup->maybeBackup( $cachedOptions );

		// First, recursively replace the new options into the cached state.
		// It's important we use the helper method since we want to replace populated arrays with empty ones if needed (when a setting was cleared out).
		$dbOptions = aioseo()->helpers->arrayReplaceRecursive(
			$cachedOptions,
			$this->addValueToValuesArray( $cachedOptions, $options, [], true )
		);

		// Now, we must also intersect both arrays to delete any individual keys that were unset.
		// We must do this because, while arrayReplaceRecursive will update the values for keys or empty them out,
		// it will keys that aren't present in the replacement array unaffected in the target array.
		$dbOptions = aioseo()->helpers->arrayIntersectRecursive(
			$dbOptions,
			$this->addValueToValuesArray( $cachedOptions, $options, [], true ),
			'value'
		);

		// Update the cache state.
		aioseo()->core->optionsCache->setOptions( $this->optionsName, $dbOptions );

		// Update localized options.
		update_option( $this->optionsName . '_localized', $this->localized );

		// Finally, save the new values to the DB.
		$this->save( true );
	}

	/**
	 * Adds some defaults that are dynamically generated.
	 *
	 * @since 4.1.4
	 *
	 * @return void
	 */
	public function addDynamicDefaults() {
		$this->addDynamicPostTypeDefaults();
		$this->addDynamicTaxonomyDefaults();
		$this->addDynamicArchiveDefaults();
	}

	/**
	 * Adds the dynamic defaults for the public post types.
	 *
	 * @since 4.1.4
	 *
	 * @return void
	 */
	protected function addDynamicPostTypeDefaults() {
		$postTypes = aioseo()->helpers->getPublicPostTypes();
		foreach ( $postTypes as $postType ) {
			if ( 'type' === $postType['name'] ) {
				$postType['name'] = '_aioseo_type';
			}

			$defaultTitle       = '#post_title #separator_sa #site_title';
			$defaultDescription = ! empty( $postType['supports']['excerpt'] ) ? '#post_excerpt' : '#post_content';
			$defaultSchemaType  = 'WebPage';
			$defaultWebPageType = 'WebPage';
			$defaultArticleType = 'BlogPosting';

			switch ( $postType['name'] ) {
				case 'post':
					$defaultSchemaType = 'Article';
					break;
				case 'attachment':
					$defaultDescription = '#attachment_caption';
					$defaultSchemaType  = 'ItemPage';
					$defaultWebPageType = 'ItemPage';
					break;
				case 'product':
					$defaultSchemaType  = 'WebPage';
					$defaultWebPageType = 'ItemPage';
					break;
				case 'news':
					$defaultArticleType = 'NewsArticle';
					break;
				default:
					break;
			}

			$defaultOptions = array_replace_recursive(
				$this->getDefaultSearchAppearanceOptions(),
				[
					'title'           => [
						'type'      => 'string',
						'localized' => true,
						'default'   => $defaultTitle
					],
					'metaDescription' => [
						'type'      => 'string',
						'localized' => true,
						'default'   => $defaultDescription
					],
					'schemaType'      => [
						'type'    => 'string',
						'default' => $defaultSchemaType
					],
					'webPageType'     => [
						'type'    => 'string',
						'default' => $defaultWebPageType
					],
					'articleType'     => [
						'type'    => 'string',
						'default' => $defaultArticleType
					],
					'customFields'    => [ 'type' => 'html' ],
					'advanced'        => [
						'bulkEditing' => [
							'type'    => 'string',
							'default' => 'enabled'
						]
					]
				]
			);

			if ( 'attachment' === $postType['name'] ) {
				$defaultOptions['redirectAttachmentUrls'] = [
					'type'    => 'string',
					'default' => 'attachment'
				];
			}

			$this->defaults['searchAppearance']['postTypes'][ $postType['name'] ] = $defaultOptions;
			$this->setDynamicSocialOptions( 'postTypes', $postType['name'] );
			$this->setDynamicSitemapOptions( 'postTypes', $postType['name'] );
		}
	}

	/**
	 * Adds the dynamic defaults for the public taxonomies.
	 *
	 * @since 4.1.4
	 *
	 * @return void
	 */
	protected function addDynamicTaxonomyDefaults() {
		$taxonomies = aioseo()->helpers->getPublicTaxonomies();
		foreach ( $taxonomies as $taxonomy ) {
			if ( 'type' === $taxonomy['name'] ) {
				$taxonomy['name'] = '_aioseo_type';
			}

			$defaultOptions = array_replace_recursive(
				$this->getDefaultSearchAppearanceOptions(),
				[
					'title'           => [
						'type'      => 'string',
						'localized' => true,
						'default'   => '#taxonomy_title #separator_sa #site_title'
					],
					'metaDescription' => [
						'type'      => 'string',
						'localized' => true,
						'default'   => '#taxonomy_description'
					],
				]
			);

			$this->setDynamicSitemapOptions( 'taxonomies', $taxonomy['name'] );

			$this->defaults['searchAppearance']['taxonomies'][ $taxonomy['name'] ] = $defaultOptions;
		}
	}

	/**
	 * Adds the dynamic defaults for the archive pages.
	 *
	 * @since 4.1.4
	 *
	 * @return void
	 */
	protected function addDynamicArchiveDefaults() {
		$postTypes = aioseo()->helpers->getPublicPostTypes( false, true );
		foreach ( $postTypes as $postType ) {
			if ( 'type' === $postType['name'] ) {
				$postType['name'] = '_aioseo_type';
			}

			$defaultOptions = array_replace_recursive(
				$this->getDefaultSearchAppearanceOptions(),
				[
					'title'           => [
						'type'      => 'string',
						'localized' => true,
						'default'   => '#archive_title #separator_sa #site_title'
					],
					'metaDescription' => [
						'type'      => 'string',
						'localized' => true,
						'default'   => ''
					],
					'customFields'    => [ 'type' => 'html' ],
					'advanced'        => [
						'keywords' => [
							'type'      => 'string',
							'localized' => true
						]
					]
				]
			);

			$this->defaults['searchAppearance']['archives'][ $postType['name'] ] = $defaultOptions;
		}
	}

	/**
	 * Returns the search appearance options for dynamic objects.
	 *
	 * @since 4.1.4
	 *
	 * @return array The default options.
	 */
	protected function getDefaultSearchAppearanceOptions() {
		return [ // phpcs:disable WordPress.Arrays.ArrayDeclarationSpacing.AssociativeArrayFound
			'show'     => [ 'type' => 'boolean', 'default' => true ],
			'advanced' => [
				'robotsMeta'                => [
					'default'         => [ 'type' => 'boolean', 'default' => true ],
					'noindex'         => [ 'type' => 'boolean', 'default' => false ],
					'nofollow'        => [ 'type' => 'boolean', 'default' => false ],
					'noarchive'       => [ 'type' => 'boolean', 'default' => false ],
					'noimageindex'    => [ 'type' => 'boolean', 'default' => false ],
					'notranslate'     => [ 'type' => 'boolean', 'default' => false ],
					'nosnippet'       => [ 'type' => 'boolean', 'default' => false ],
					'noodp'           => [ 'type' => 'boolean', 'default' => false ],
					'maxSnippet'      => [ 'type' => 'number', 'default' => -1 ],
					'maxVideoPreview' => [ 'type' => 'number', 'default' => -1 ],
					'maxImagePreview' => [ 'type' => 'string', 'default' => 'large' ]
				],
				'showDateInGooglePreview'   => [ 'type' => 'boolean', 'default' => true ],
				'showPostThumbnailInSearch' => [ 'type' => 'boolean', 'default' => true ],
				'showMetaBox'               => [ 'type' => 'boolean', 'default' => true ]
			]
		]; // phpcs:enable WordPress.Arrays.ArrayDeclarationSpacing.AssociativeArrayFound
	}

	/**
	 * Sets the dynamic social settings for a given post type or taxonomy.
	 *
	 * @since 4.1.4
	 *
	 * @param  string $objectType Whether the object belongs to the dynamic "postTypes" or "taxonomies".
	 * @param  string $objectName The object name.
	 * @return void
	 */
	protected function setDynamicSocialOptions( $objectType, $objectName ) {
		$defaultOptions = [
			'objectType' => [
				'type'    => 'string',
				'default' => 'article'
			]
		];

		$this->defaults['social']['facebook']['general'][ $objectType ][ $objectName ] = $defaultOptions;
	}

	/**
	 * Sets the dynamic sitemap settings for a given post type or taxonomy.
	 *
	 * @since 4.1.4
	 *
	 * @param  string $objectType Whether the object belongs to the dynamic "postTypes" or "taxonomies".
	 * @param  string $objectName The object name.
	 * @return void
	 */
	protected function setDynamicSitemapOptions( $objectType, $objectName ) {
		$this->defaults['sitemap']['priority'][ $objectType ][ $objectName ] = [
			'priority'  => [
				'type'    => 'string',
				'default' => '{"label":"default","value":"default"}'
			],
			'frequency' => [
				'type'    => 'string',
				'default' => '{"label":"default","value":"default"}'
			]
		];
	}
}