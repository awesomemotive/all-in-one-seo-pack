<?php
namespace AIOSEO\Plugin\Common\ImportExport\YoastSeo;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AIOSEO\Plugin\Common\ImportExport;

// phpcs:disable WordPress.Arrays.ArrayDeclarationSpacing.AssociativeArrayFound

/**
 * Migrates the Search Appearance settings.
 *
 * @since 4.0.0
 */
class SearchAppearance {
	/**
	 * List of options.
	 *
	 * @since 4.2.7
	 *
	 * @var array
	 */
	private $options = [];

	/**
	 * Whether the homepage social settings have been imported here.
	 *
	 * @since 4.2.4
	 *
	 * @var bool
	 */
	public $hasImportedHomepageSocialSettings = false;

	/**
	 * Class constructor.
	 *
	 * @since 4.0.0
	 */
	public function __construct() {
		$this->options = get_option( 'wpseo_titles' );
		if ( empty( $this->options ) ) {
			return;
		}

		$this->migrateSeparator();
		$this->migrateTitleFormats();
		$this->migrateDescriptionFormats();
		$this->migrateNoindexSettings();
		$this->migratePostTypeSettings();
		$this->migratePostTypeArchiveSettings();
		$this->migrateRedirectAttachments();
		$this->migrateKnowledgeGraphSettings();
		$this->migrateRssContentSettings();
		$this->migrateStripCategoryBase();
		$this->migrateHomepageSocialSettings();
	}

	/**
	 * Migrates the title/description separator.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	private function migrateSeparator() {
		$separators = [
			'sc-dash'   => '-',
			'sc-ndash'  => '&ndash;',
			'sc-mdash'  => '&mdash;',
			'sc-colon'  => ':',
			'sc-middot' => '&middot;',
			'sc-bull'   => '&bull;',
			'sc-star'   => '*',
			'sc-smstar' => '&#8902;',
			'sc-pipe'   => '|',
			'sc-tilde'  => '~',
			'sc-laquo'  => '&laquo;',
			'sc-raquo'  => '&raquo;',
			'sc-lt'     => '&lt;',
			'sc-gt'     => '&gt;',
		];

		if ( ! empty( $this->options['separator'] ) && in_array( $this->options['separator'], array_keys( $separators ), true ) ) {
			aioseo()->options->searchAppearance->global->separator = $separators[ $this->options['separator'] ];
		}
	}

	/**
	 * Migrates the title formats.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	private function migrateTitleFormats() {
		aioseo()->options->searchAppearance->global->siteTitle =
			aioseo()->helpers->sanitizeOption( aioseo()->importExport->yoastSeo->helpers->macrosToSmartTags( $this->options['title-home-wpseo'] ) );

		aioseo()->options->searchAppearance->archives->date->title =
			aioseo()->helpers->sanitizeOption( aioseo()->importExport->yoastSeo->helpers->macrosToSmartTags( $this->options['title-archive-wpseo'], null, 'archive' ) );

		// Archive Title tag needs to be stripped since we don't support it for these two archives.
		$value = aioseo()->helpers->sanitizeOption( aioseo()->importExport->yoastSeo->helpers->macrosToSmartTags( $this->options['title-author-wpseo'], null, 'archive' ) );
		aioseo()->options->searchAppearance->archives->author->title = aioseo()->helpers->pregReplace( '/#archive_title/', '', $value );

		$value = aioseo()->helpers->sanitizeOption( aioseo()->importExport->yoastSeo->helpers->macrosToSmartTags( $this->options['title-search-wpseo'], null, 'archive' ) );
		aioseo()->options->searchAppearance->archives->search->title = aioseo()->helpers->pregReplace( '/#archive_title/', '', $value );
	}

	/**
	 * Migrates the description formats.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	private function migrateDescriptionFormats() {
		$settings = [
			'metadesc-home-wpseo'    => [ 'type' => 'string', 'newOption' => [ 'searchAppearance', 'global', 'metaDescription' ] ],
			'metadesc-author-wpseo'  => [ 'type' => 'string', 'newOption' => [ 'searchAppearance', 'archives', 'author', 'metaDescription' ] ],
			'metadesc-archive-wpseo' => [ 'type' => 'string', 'newOption' => [ 'searchAppearance', 'archives', 'date', 'metaDescription' ] ],
		];

		aioseo()->importExport->yoastSeo->helpers->mapOldToNew( $settings, $this->options, true );
	}

	/**
	 * Migrates the noindex settings.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	private function migrateNoindexSettings() {
		if ( ! empty( $this->options['noindex-author-wpseo'] ) ) {
			aioseo()->options->searchAppearance->archives->author->show = false;
			aioseo()->options->searchAppearance->archives->author->advanced->robotsMeta->default = false;
			aioseo()->options->searchAppearance->archives->author->advanced->robotsMeta->noindex = true;
		} else {
			aioseo()->options->searchAppearance->archives->author->show = true;
		}

		if ( ! empty( $this->options['noindex-archive-wpseo'] ) ) {
			aioseo()->options->searchAppearance->archives->date->show = false;
			aioseo()->options->searchAppearance->archives->date->advanced->robotsMeta->default = false;
			aioseo()->options->searchAppearance->archives->date->advanced->robotsMeta->noindex = true;
		} else {
			aioseo()->options->searchAppearance->archives->date->show = true;
		}
	}

	/**
	 * Migrates the post type settings.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	private function migratePostTypeSettings() {
		$supportedSettings = [
			'title',
			'metadesc',
			'noindex',
			'display-metabox-pt',
			'schema-page-type',
			'schema-article-type'
		];

		foreach ( aioseo()->helpers->getPublicPostTypes( true ) as $postType ) {
			foreach ( $this->options as $name => $value ) {
				if ( ! preg_match( "#(.*)-$postType$#", (string) $name, $match ) || ! in_array( $match[1], $supportedSettings, true ) ) {
					continue;
				}

				switch ( $match[1] ) {
					case 'title':
						if ( 'page' === $postType ) {
							$value = aioseo()->helpers->pregReplace( '#%%primary_category%%#', '', $value );
							$value = aioseo()->helpers->pregReplace( '#%%excerpt%%#', '', $value );
						}
						aioseo()->dynamicOptions->searchAppearance->postTypes->$postType->title =
							aioseo()->helpers->sanitizeOption( aioseo()->importExport->yoastSeo->helpers->macrosToSmartTags( $value, $postType ) );
						break;
					case 'metadesc':
						if ( 'page' === $postType ) {
							$value = aioseo()->helpers->pregReplace( '#%%primary_category%%#', '', $value );
							$value = aioseo()->helpers->pregReplace( '#%%excerpt%%#', '', $value );
						}
						aioseo()->dynamicOptions->searchAppearance->postTypes->$postType->metaDescription =
							aioseo()->helpers->sanitizeOption( aioseo()->importExport->yoastSeo->helpers->macrosToSmartTags( $value, $postType ) );
						break;
					case 'noindex':
						aioseo()->dynamicOptions->searchAppearance->postTypes->$postType->show = empty( $value ) ? true : false;
						aioseo()->dynamicOptions->searchAppearance->postTypes->$postType->advanced->robotsMeta->default = empty( $value ) ? true : false;
						aioseo()->dynamicOptions->searchAppearance->postTypes->$postType->advanced->robotsMeta->noindex = empty( $value ) ? false : true;
						break;
					case 'display-metabox-pt':
						if ( empty( $value ) ) {
							aioseo()->dynamicOptions->searchAppearance->postTypes->$postType->advanced->showMetaBox = false;
						}
						break;
					case 'schema-page-type':
						$value = aioseo()->helpers->pregReplace( '#\s#', '', $value );
						if ( in_array( $postType, [ 'post', 'page', 'attachment' ], true ) ) {
							break;
						}
						aioseo()->dynamicOptions->searchAppearance->postTypes->$postType->schemaType = 'WebPage';
						if ( in_array( $value, ImportExport\SearchAppearance::$supportedWebPageGraphs, true ) ) {
							aioseo()->dynamicOptions->searchAppearance->postTypes->$postType->webPageType = $value;
						}
						break;
					case 'schema-article-type':
						$value = aioseo()->helpers->pregReplace( '#\s#', '', $value );
						if ( 'none' === lcfirst( $value ) ) {
							aioseo()->dynamicOptions->searchAppearance->postTypes->$postType->articleType = 'none';
							break;
						}

						aioseo()->dynamicOptions->searchAppearance->postTypes->$postType->articleType = 'Article';
						if ( in_array( $value, ImportExport\SearchAppearance::$supportedArticleGraphs, true ) ) {
							if ( ! in_array( $postType, [ 'page', 'attachment' ], true ) ) {
								aioseo()->dynamicOptions->searchAppearance->postTypes->$postType->articleType = $value;
							}
						} else {
							aioseo()->dynamicOptions->searchAppearance->postTypes->$postType->articleType = 'BlogPosting';
						}
						break;
					default:
						break;
				}
			}
		}
	}

	/**
	 * Migrates the post type archive settings.
	 *
	 * @since 4.0.16
	 *
	 * @return void
	 */
	private function migratePostTypeArchiveSettings() {
		$supportedSettings = [
			'title',
			'metadesc',
			'noindex'
		];

		foreach ( aioseo()->helpers->getPublicPostTypes( true, true ) as $postType ) {
			foreach ( $this->options as $name => $value ) {
				if ( ! preg_match( "#(.*)-ptarchive-$postType$#", (string) $name, $match ) || ! in_array( $match[1], $supportedSettings, true ) ) {
					continue;
				}

				switch ( $match[1] ) {
					case 'title':
						aioseo()->dynamicOptions->searchAppearance->archives->$postType->title =
							aioseo()->helpers->sanitizeOption( aioseo()->importExport->yoastSeo->helpers->macrosToSmartTags( $value, $postType, 'archive' ) );
						break;
					case 'metadesc':
						aioseo()->dynamicOptions->searchAppearance->archives->$postType->metaDescription =
							aioseo()->helpers->sanitizeOption( aioseo()->importExport->yoastSeo->helpers->macrosToSmartTags( $value, $postType, 'archive' ) );
						break;
					case 'noindex':
						aioseo()->dynamicOptions->searchAppearance->archives->$postType->show = empty( $value ) ? true : false;
						aioseo()->dynamicOptions->searchAppearance->archives->$postType->advanced->robotsMeta->default = empty( $value ) ? true : false;
						aioseo()->dynamicOptions->searchAppearance->archives->$postType->advanced->robotsMeta->noindex = empty( $value ) ? false : true;
						break;
					default:
						break;
				}
			}
		}
	}

	/**
	 * Migrates the Knowledge Graph settings.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	private function migrateKnowledgeGraphSettings() {
		if ( ! empty( $this->options['company_or_person'] ) ) {
			aioseo()->options->searchAppearance->global->schema->siteRepresents =
				'company' === $this->options['company_or_person'] ? 'organization' : 'person';
		}

		$settings = [
			'company_or_person_user_id' => [ 'type' => 'string', 'newOption' => [ 'searchAppearance', 'global', 'schema', 'person' ] ],
			'person_logo'               => [ 'type' => 'string', 'newOption' => [ 'searchAppearance', 'global', 'schema', 'personLogo' ] ],
			'person_name'               => [ 'type' => 'string', 'newOption' => [ 'searchAppearance', 'global', 'schema', 'personName' ] ],
			'company_name'              => [ 'type' => 'string', 'newOption' => [ 'searchAppearance', 'global', 'schema', 'organizationName' ] ],
			'company_logo'              => [ 'type' => 'string', 'newOption' => [ 'searchAppearance', 'global', 'schema', 'organizationLogo' ] ],
			'org-email'                 => [ 'type' => 'string', 'newOption' => [ 'searchAppearance', 'global', 'schema', 'email' ] ],
			'org-phone'                 => [ 'type' => 'string', 'newOption' => [ 'searchAppearance', 'global', 'schema', 'phone' ] ],
			'org-description'           => [ 'type' => 'string', 'newOption' => [ 'searchAppearance', 'global', 'schema', 'organizationDescription' ] ],
			'org-founding-date'         => [ 'type' => 'string', 'newOption' => [ 'searchAppearance', 'global', 'schema', 'foundingDate' ] ],
		];

		aioseo()->importExport->yoastSeo->helpers->mapOldToNew( $settings, $this->options );

		// Additional Info
		// Reset data
		aioseo()->options->noConflict()->searchAppearance->global->schema->numberOfEmployees->reset();

		$numberOfEmployees = $this->options['org-number-employees'];
		if ( ! empty( $numberOfEmployees ) ) {
			list( $num1, $num2 ) = explode( '-', $numberOfEmployees );

			if ( $num2 ) {
				aioseo()->options->noConflict()->searchAppearance->global->schema->numberOfEmployees->isRange = true;
				aioseo()->options->noConflict()->searchAppearance->global->schema->numberOfEmployees->from    = (int) $num1;
				aioseo()->options->noConflict()->searchAppearance->global->schema->numberOfEmployees->to      = (int) $num2;
			} else {
				aioseo()->options->noConflict()->searchAppearance->global->schema->numberOfEmployees->number = (int) $num1;
			}
		}
	}

	/**
	 * Migrates the RSS content settings.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	private function migrateRssContentSettings() {
		if ( isset( $this->options['rssbefore'] ) ) {
			aioseo()->options->rssContent->before = esc_html( aioseo()->importExport->yoastSeo->helpers->macrosToSmartTags( $this->options['rssbefore'] ) );
		}

		if ( isset( $this->options['rssafter'] ) ) {
			aioseo()->options->rssContent->after = esc_html( aioseo()->importExport->yoastSeo->helpers->macrosToSmartTags( $this->options['rssafter'] ) );
		}
	}

	/**
	 * Migrates the Redirect Attachments setting.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	private function migrateRedirectAttachments() {
		aioseo()->dynamicOptions->searchAppearance->postTypes->attachment->redirectAttachmentUrls = empty( $this->options['disable-attachment'] ) ? 'disabled' : 'attachment';
	}

	/**
	 * Migrates the strip category base option.
	 *
	 * @since 4.2.0
	 *
	 * @return void
	 */
	private function migrateStripCategoryBase() {
		aioseo()->options->searchAppearance->advanced->removeCategoryBase = empty( $this->options['stripcategorybase'] ) ? false : true;
	}

	/**
	 * Migrate the social settings for the homepage.
	 *
	 * @since 4.2.4
	 *
	 * @return void
	 */
	private function migrateHomepageSocialSettings() {
		if (
			empty( $this->options['open_graph_frontpage_title'] ) &&
			empty( $this->options['open_graph_frontpage_desc'] ) &&
			empty( $this->options['open_graph_frontpage_image'] )
		) {
			return;
		}

		$this->hasImportedHomepageSocialSettings = true;

		$settings = [
			// These settings can also be found in the SocialMeta class, but Yoast recently moved them here.
			// We'll still keep them in the other class for backwards compatibility.
			'open_graph_frontpage_title' => [ 'type' => 'string', 'newOption' => [ 'social', 'facebook', 'homePage', 'title' ] ],
			'open_graph_frontpage_desc'  => [ 'type' => 'string', 'newOption' => [ 'social', 'facebook', 'homePage', 'description' ] ],
			'open_graph_frontpage_image' => [ 'type' => 'string', 'newOption' => [ 'social', 'facebook', 'homePage', 'image' ] ]
		];

		aioseo()->importExport->yoastSeo->helpers->mapOldToNew( $settings, $this->options, true );
	}
}