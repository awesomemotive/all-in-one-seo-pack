<?php
namespace AIOSEO\Plugin\Common\ImportExport\SeoPress;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// phpcs:disable WordPress.Arrays.ArrayDeclarationSpacing.AssociativeArrayFound

/**
 * Migrates the Titles Settings.
 *
 * @since 4.1.4
 */
class Titles {
	/**
	 * List of options.
	 *
	 * @since 4.2.7
	 *
	 * @var array
	 */
	private $options = [];

	/**
	 * Class constructor.
	 *
	 * @since 4.1.4
	 */
	public function __construct() {
		$this->options = get_option( 'seopress_titles_option_name' );
		if ( empty( $this->options ) ) {
			return;
		}

		if (
			! empty( $this->options['seopress_titles_archives_author_title'] ) ||
			! empty( $this->options['seopress_titles_archives_author_desc'] ) ||
			! empty( $this->options['seopress_titles_archives_author_noindex'] )
			) {
			aioseo()->options->searchAppearance->archives->author->show = true;
		}

		if (
			! empty( $this->options['seopress_titles_archives_date_title'] ) ||
			! empty( $this->options['seopress_titles_archives_date_desc'] ) ||
			! empty( $this->options['seopress_titles_archives_date_noindex'] )
			) {
			aioseo()->options->searchAppearance->archives->date->show = true;
		}

		if (
			! empty( $this->options['seopress_titles_archives_search_title'] ) ||
			! empty( $this->options['seopress_titles_archives_search_desc'] )
			) {
			aioseo()->options->searchAppearance->archives->search->show = true;
		}

		$this->migrateTitleFormats();
		$this->migrateDescriptionFormats();
		$this->migrateNoIndexFormats();
		$this->migratePostTypeSettings();
		$this->migrateTaxonomiesSettings();
		$this->migrateArchiveSettings();
		$this->migrateAdvancedSettings();

		$settings = [
			'seopress_titles_sep' => [ 'type' => 'string', 'newOption' => [ 'searchAppearance', 'global', 'separator' ] ],
		];

		aioseo()->importExport->seoPress->helpers->mapOldToNew( $settings, $this->options, true );
	}

	/**
	 * Migrates the title formats.
	 *
	 * @since 4.1.4
	 *
	 * @return void
	 */
	private function migrateTitleFormats() {
		$settings = [
			'seopress_titles_home_site_title'       => [ 'type' => 'string', 'newOption' => [ 'searchAppearance', 'global', 'siteTitle' ] ],
			'seopress_titles_archives_author_title' => [ 'type' => 'string', 'newOption' => [ 'searchAppearance', 'archives', 'author', 'title' ] ],
			'seopress_titles_archives_date_title'   => [ 'type' => 'string', 'newOption' => [ 'searchAppearance', 'archives', 'date', 'title' ] ],
			'seopress_titles_archives_search_title' => [ 'type' => 'string', 'newOption' => [ 'searchAppearance', 'archives', 'search', 'title' ] ],
		];

		aioseo()->importExport->seoPress->helpers->mapOldToNew( $settings, $this->options, true );
	}

	/**
	 * Migrates the description formats.
	 *
	 * @since 4.1.4
	 *
	 * @return void
	 */
	private function migrateDescriptionFormats() {
		$settings = [
			'seopress_titles_home_site_desc'       => [ 'type' => 'string', 'newOption' => [ 'searchAppearance', 'global', 'metaDescription' ] ],
			'seopress_titles_archives_author_desc' => [ 'type' => 'string', 'newOption' => [ 'searchAppearance', 'archives', 'author', 'metaDescription' ] ],
			'seopress_titles_archives_date_desc'   => [ 'type' => 'string', 'newOption' => [ 'searchAppearance', 'archives', 'date', 'metaDescription' ] ],
			'seopress_titles_archives_search_desc' => [ 'type' => 'string', 'newOption' => [ 'searchAppearance', 'archives', 'search', 'metaDescription' ] ],
		];

		aioseo()->importExport->seoPress->helpers->mapOldToNew( $settings, $this->options, true );
	}

	/**
	 * Migrates the NoIndex formats.
	 *
	 * @since 4.1.4
	 *
	 * @return void
	 */
	private function migrateNoIndexFormats() {
		$settings = [
			'seopress_titles_archives_author_noindex' => [ 'type' => 'boolean', 'newOption' => [ 'searchAppearance', 'archives', 'author', 'show' ] ],
			'seopress_titles_archives_date_noindex'   => [ 'type' => 'boolean', 'newOption' => [ 'searchAppearance', 'archives', 'date', 'show' ] ],
		];

		aioseo()->importExport->seoPress->helpers->mapOldToNew( $settings, $this->options );
	}

	/**
	 * Migrates the post type settings.
	 *
	 * @since 4.1.4
	 *
	 * @return void
	 */
	private function migratePostTypeSettings() {
		$titles = $this->options['seopress_titles_single_titles'];
		if ( empty( $titles ) ) {
			return;
		}

		foreach ( $titles as $postType => $options ) {
			if ( ! aioseo()->dynamicOptions->searchAppearance->postTypes->has( $postType ) ) {
				continue;
			}

			if ( ! empty( $options['title'] ) ) {
				aioseo()->dynamicOptions->searchAppearance->postTypes->$postType->title =
					aioseo()->helpers->sanitizeOption( aioseo()->importExport->seoPress->helpers->macrosToSmartTags( $options['title'] ) );
			}

			if ( ! empty( $options['description'] ) ) {
				aioseo()->dynamicOptions->searchAppearance->postTypes->$postType->metaDescription =
					aioseo()->helpers->sanitizeOption( aioseo()->importExport->seoPress->helpers->macrosToSmartTags( $options['description'] ) );
			}

			if ( ! empty( $options['enable'] ) ) {
				aioseo()->dynamicOptions->searchAppearance->postTypes->$postType->advanced->showMetaBox = false;
			}

			if ( ! empty( $options['noindex'] ) ) {
				aioseo()->dynamicOptions->searchAppearance->postTypes->$postType->show = false;
				aioseo()->dynamicOptions->searchAppearance->postTypes->$postType->advanced->robotsMeta->default = false;
				aioseo()->dynamicOptions->searchAppearance->postTypes->$postType->advanced->robotsMeta->noindex = true;
			}

			if ( ! empty( $options['nofollow'] ) ) {
				aioseo()->dynamicOptions->searchAppearance->postTypes->$postType->show = false;
				aioseo()->dynamicOptions->searchAppearance->postTypes->$postType->advanced->robotsMeta->default = false;
				aioseo()->dynamicOptions->searchAppearance->postTypes->$postType->advanced->robotsMeta->nofollow = true;
			}

			if ( ! empty( $options['date'] ) ) {
				aioseo()->dynamicOptions->searchAppearance->postTypes->$postType->advanced->showDateInGooglePreview = false;
			}

			if ( ! empty( $options['thumb_gcs'] ) ) {
				aioseo()->dynamicOptions->searchAppearance->postTypes->$postType->advanced->showPostThumbnailInSearch = true;
			}
		}
	}

	/**
	 * Migrates the taxonomies settings.
	 *
	 * @since 4.1.4
	 *
	 * @return void
	 */
	private function migrateTaxonomiesSettings() {
		$titles = ! empty( $this->options['seopress_titles_tax_titles'] ) ? $this->options['seopress_titles_tax_titles'] : '';
		if ( empty( $titles ) ) {
			return;
		}

		foreach ( $titles as $taxonomy => $options ) {
			if ( ! aioseo()->dynamicOptions->searchAppearance->taxonomies->has( $taxonomy ) ) {
				continue;
			}

			if ( ! empty( $options['title'] ) ) {
				aioseo()->dynamicOptions->searchAppearance->taxonomies->$taxonomy->title =
					aioseo()->helpers->sanitizeOption( aioseo()->importExport->seoPress->helpers->macrosToSmartTags( $options['title'] ) );
			}

			if ( ! empty( $options['description'] ) ) {
				aioseo()->dynamicOptions->searchAppearance->taxonomies->$taxonomy->metaDescription =
					aioseo()->helpers->sanitizeOption( aioseo()->importExport->seoPress->helpers->macrosToSmartTags( $options['description'] ) );
			}

			if ( ! empty( $options['enable'] ) ) {
				aioseo()->dynamicOptions->searchAppearance->taxonomies->$taxonomy->advanced->showMetaBox = false;
			}

			if ( ! empty( $options['noindex'] ) ) {
				aioseo()->dynamicOptions->searchAppearance->taxonomies->$taxonomy->show = false;
				aioseo()->dynamicOptions->searchAppearance->taxonomies->$taxonomy->advanced->robotsMeta->default = false;
				aioseo()->dynamicOptions->searchAppearance->taxonomies->$taxonomy->advanced->robotsMeta->noindex = true;
			}

			if ( ! empty( $options['nofollow'] ) ) {
				aioseo()->dynamicOptions->searchAppearance->taxonomies->$taxonomy->show = false;
				aioseo()->dynamicOptions->searchAppearance->taxonomies->$taxonomy->advanced->robotsMeta->default = false;
				aioseo()->dynamicOptions->searchAppearance->taxonomies->$taxonomy->advanced->robotsMeta->nofollow = true;
			}
		}
	}

	/**
	 * Migrates the archives settings.
	 *
	 * @since 4.1.4
	 *
	 * @return void
	 */
	private function migrateArchiveSettings() {
		$titles = $this->options['seopress_titles_archive_titles'];
		if ( empty( $titles ) ) {
			return;
		}

		foreach ( $titles as $archive => $options ) {
			if ( ! aioseo()->dynamicOptions->searchAppearance->archives->has( $archive ) ) {
				continue;
			}

			if ( ! empty( $options['title'] ) ) {
				aioseo()->dynamicOptions->searchAppearance->archives->$archive->title =
					aioseo()->helpers->sanitizeOption( aioseo()->importExport->seoPress->helpers->macrosToSmartTags( $options['title'] ) );
			}

			if ( ! empty( $options['description'] ) ) {
				aioseo()->dynamicOptions->searchAppearance->archives->$archive->metaDescription =
					aioseo()->helpers->sanitizeOption( aioseo()->importExport->seoPress->helpers->macrosToSmartTags( $options['description'] ) );
			}

			if ( ! empty( $options['noindex'] ) ) {
				aioseo()->dynamicOptions->searchAppearance->archives->$archive->show = false;
				aioseo()->dynamicOptions->searchAppearance->archives->$archive->advanced->robotsMeta->default = false;
				aioseo()->dynamicOptions->searchAppearance->archives->$archive->advanced->robotsMeta->noindex = true;
			}

			if ( ! empty( $options['nofollow'] ) ) {
				aioseo()->dynamicOptions->searchAppearance->archives->$archive->show = false;
				aioseo()->dynamicOptions->searchAppearance->archives->$archive->advanced->robotsMeta->default = false;
				aioseo()->dynamicOptions->searchAppearance->archives->$archive->advanced->robotsMeta->nofollow = true;
			}
		}
	}

	/**
	 * Migrates the advanced settings.
	 *
	 * @since 4.1.4
	 *
	 * @return void
	 */
	private function migrateAdvancedSettings() {
		if (
			! empty( $this->options['seopress_titles_noindex'] ) || ! empty( $this->options['seopress_titles_nofollow'] ) || ! empty( $this->options['seopress_titles_noodp'] ) ||
			! empty( $this->options['seopress_titles_noimageindex'] ) || ! empty( $this->options['seopress_titles_noarchive'] ) ||
			! empty( $this->options['seopress_titles_nosnippet'] ) || ! empty( $this->options['seopress_titles_paged_noindex'] )
		) {
			aioseo()->options->searchAppearance->advanced->globalRobotsMeta->default = false;
		}

		$settings = [
			'seopress_titles_noindex'       => [ 'type' => 'boolean', 'newOption' => [ 'searchAppearance', 'advanced', 'globalRobotsMeta', 'noindex' ] ],
			'seopress_titles_nofollow'      => [ 'type' => 'boolean', 'newOption' => [ 'searchAppearance', 'advanced', 'globalRobotsMeta', 'nofollow' ] ],
			'seopress_titles_noodp'         => [ 'type' => 'boolean', 'newOption' => [ 'searchAppearance', 'advanced', 'globalRobotsMeta', 'noodp' ] ],
			'seopress_titles_noimageindex'  => [ 'type' => 'boolean', 'newOption' => [ 'searchAppearance', 'advanced', 'globalRobotsMeta', 'noimageindex' ] ],
			'seopress_titles_noarchive'     => [ 'type' => 'boolean', 'newOption' => [ 'searchAppearance', 'advanced', 'globalRobotsMeta', 'noarchive' ] ],
			'seopress_titles_nosnippet'     => [ 'type' => 'boolean', 'newOption' => [ 'searchAppearance', 'advanced', 'globalRobotsMeta', 'nosnippet' ] ],
			'seopress_titles_paged_noindex' => [ 'type' => 'boolean', 'newOption' => [ 'searchAppearance', 'advanced', 'globalRobotsMeta', 'noindexPaginated' ] ],
		];

		aioseo()->importExport->seoPress->helpers->mapOldToNew( $settings, $this->options );
	}
}