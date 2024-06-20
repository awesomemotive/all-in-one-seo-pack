<?php
namespace AIOSEO\Plugin\Common\ImportExport\RankMath;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AIOSEO\Plugin\Common\ImportExport;
use AIOSEO\Plugin\Common\Models;

// phpcs:disable WordPress.Arrays.ArrayDeclarationSpacing.AssociativeArrayFound

/**
 * Migrates the Search Appearance settings.
 *
 * @since 4.0.0
 */
class TitleMeta extends ImportExport\SearchAppearance {
	/**
	 * Our robot meta settings.
	 *
	 * @since 4.0.0
	 */
	private $robotMetaSettings = [
		'noindex',
		'nofollow',
		'noarchive',
		'noimageindex',
		'nosnippet'
	];

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
	 * @since 4.0.0
	 */
	public function __construct() {
		$this->options = get_option( 'rank-math-options-titles' );
		if ( empty( $this->options ) ) {
			return;
		}

		$this->migrateHomePageSettings();
		$this->migratePostTypeSettings();
		$this->migratePostTypeArchiveSettings();
		$this->migrateArchiveSettings();
		$this->migrateRobotMetaSettings();
		$this->migrateKnowledgeGraphSettings();
		$this->migrateSocialMetaSettings();

		$settings = [
			'title_separator' => [ 'type' => 'string', 'newOption' => [ 'searchAppearance', 'global', 'separator' ] ],
		];

		aioseo()->importExport->rankMath->helpers->mapOldToNew( $settings, $this->options );
	}

	/**
	 * Migrates the homepage settings.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	private function migrateHomePageSettings() {
		if ( isset( $this->options['homepage_title'] ) ) {
			aioseo()->options->searchAppearance->global->siteTitle =
				aioseo()->helpers->sanitizeOption( aioseo()->importExport->rankMath->helpers->macrosToSmartTags( $this->options['homepage_title'] ) );
		}

		if ( isset( $this->options['homepage_description'] ) ) {
			aioseo()->options->searchAppearance->global->metaDescription =
				aioseo()->helpers->sanitizeOption( aioseo()->importExport->rankMath->helpers->macrosToSmartTags( $this->options['homepage_description'] ) );
		}

		if ( isset( $this->options['homepage_facebook_title'] ) ) {
			aioseo()->options->social->facebook->homePage->title = aioseo()->helpers->sanitizeOption( $this->options['homepage_facebook_title'] );
		}

		if ( isset( $this->options['homepage_facebook_description'] ) ) {
			aioseo()->options->social->facebook->homePage->description = aioseo()->helpers->sanitizeOption( $this->options['homepage_facebook_description'] );
		}

		if ( isset( $this->options['homepage_facebook_image'] ) ) {
			aioseo()->options->social->facebook->homePage->image = esc_url( $this->options['homepage_facebook_image'] );
		}
	}

	/**
	 * Migrates the archive settings.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	private function migrateArchiveSettings() {
		$archives = [
			'author',
			'date'
		];

		foreach ( $archives as $archive ) {
			// Reset existing values first.
			foreach ( $this->robotMetaSettings as $robotsMetaName ) {
				aioseo()->options->searchAppearance->archives->$archive->advanced->robotsMeta->$robotsMetaName = false;
			}

			if ( isset( $this->options[ "disable_{$archive}_archives" ] ) ) {
				aioseo()->options->searchAppearance->archives->$archive->show                          = 'off' === $this->options[ "disable_{$archive}_archives" ];
				aioseo()->options->searchAppearance->archives->$archive->advanced->robotsMeta->default = 'on' === $this->options[ "disable_{$archive}_archives" ];
				aioseo()->options->searchAppearance->archives->$archive->advanced->robotsMeta->noindex = 'on' === $this->options[ "disable_{$archive}_archives" ];
			}

			if ( isset( $this->options[ "{$archive}_archive_title" ] ) ) {
				$value = aioseo()->helpers->sanitizeOption( aioseo()->importExport->rankMath->helpers->macrosToSmartTags( $this->options[ "{$archive}_archive_title" ], 'archive' ) );
				if ( 'date' !== $archive ) {
					// Archive Title tag needs to be stripped since we don't support it for author archives.
					$value = aioseo()->helpers->pregReplace( '/#archive_title/', '', $value );
				}
				aioseo()->options->searchAppearance->archives->$archive->title = $value;
			}

			if ( isset( $this->options[ "{$archive}_archive_description" ] ) ) {
				aioseo()->options->searchAppearance->archives->$archive->metaDescription =
					aioseo()->helpers->sanitizeOption( aioseo()->importExport->rankMath->helpers->macrosToSmartTags( $this->options[ "{$archive}_archive_description" ], 'archive' ) );
			}

			if ( ! empty( $this->options[ "{$archive}_custom_robots" ] ) ) {
				aioseo()->options->searchAppearance->archives->$archive->advanced->robotsMeta->default = 'off' === $this->options[ "{$archive}_custom_robots" ];
			}

			if ( ! empty( $this->options[ "{$archive}_robots" ] ) ) {
				foreach ( $this->options[ "{$archive}_robots" ] as $robotsName ) {
					if ( 'index' === $robotsName ) {
						continue;
					}

					if ( 'noindex' === $robotsName ) {
						aioseo()->options->searchAppearance->archives->{$archive}->show = false;
					}

					aioseo()->options->searchAppearance->archives->{$archive}->advanced->robotsMeta->{$robotsName} = true;
				}
			}

			if ( ! empty( $this->options[ "{$archive}_advanced_robots" ] ) ) {
				if ( ! empty( $this->options[ "{$archive}_advanced_robots" ]['max-snippet'] ) ) {
					aioseo()->options->searchAppearance->archives->$archive->advanced->robotsMeta->maxSnippet = intval( $this->options[ "{$archive}_advanced_robots" ]['max-snippet'] );
				}
				if ( isset( $this->options[ "{$archive}_advanced_robots" ]['max-video-preview'] ) && is_numeric( isset( $this->options[ "{$archive}_advanced_robots" ]['max-video-preview'] ) ) ) {
					aioseo()->options->searchAppearance->archives->$archive->advanced->robotsMeta->maxVideoPreview = intval( $this->options[ "{$archive}_advanced_robots" ]['max-video-preview'] );
				}
				if ( ! empty( $this->options[ "{$archive}_advanced_robots" ]['max-image-preview'] ) ) {
					aioseo()->options->searchAppearance->archives->$archive->advanced->robotsMeta->maxImagePreview =
						aioseo()->helpers->sanitizeOption( lcfirst( $this->options[ "{$archive}_advanced_robots" ]['max-image-preview'] ) );
				}
			}
		}

		if ( isset( $this->options['search_title'] ) ) {
			// Archive Title tag needs to be stripped since we don't support it for search archives.
			$value = aioseo()->helpers->sanitizeOption( aioseo()->importExport->rankMath->helpers->macrosToSmartTags( $this->options['search_title'], 'archive' ) );
			aioseo()->options->searchAppearance->archives->search->title = aioseo()->helpers->pregReplace( '/#archive_title/', '', $value );
		}

		if ( ! empty( $this->options['noindex_search'] ) ) {
			aioseo()->options->searchAppearance->archives->search->show                          = 'off' === $this->options['noindex_search'];
			aioseo()->options->searchAppearance->archives->search->advanced->robotsMeta->default = 'on' === $this->options['noindex_search'];
			aioseo()->options->searchAppearance->archives->search->advanced->robotsMeta->noindex = 'on' === $this->options['noindex_search'];
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
			'description',
			'custom_robots',
			'robots',
			'advanced_robots',
			'default_rich_snippet',
			'default_article_type',
			'add_meta_box'
		];

		foreach ( aioseo()->helpers->getPublicPostTypes( true ) as $postType ) {
			// Reset existing values first.
			foreach ( $this->robotMetaSettings as $robotsMetaName ) {
				aioseo()->dynamicOptions->searchAppearance->postTypes->$postType->advanced->robotsMeta->$robotsMetaName = false;
			}

			foreach ( $this->options as $name => $value ) {
				if ( ! preg_match( "#^pt_{$postType}_(.*)$#", $name, $match ) || ! in_array( $match[1], $supportedSettings, true ) ) {
					continue;
				}

				switch ( $match[1] ) {
					case 'title':
						if ( 'page' === $postType ) {
							$value = aioseo()->helpers->pregReplace( '#%category%#', '', $value );
							$value = aioseo()->helpers->pregReplace( '#%excerpt%#', '', $value );
						}
						aioseo()->dynamicOptions->searchAppearance->postTypes->$postType->title =
							aioseo()->helpers->sanitizeOption( aioseo()->importExport->rankMath->helpers->macrosToSmartTags( $value ) );
						break;
					case 'description':
						if ( 'page' === $postType ) {
							$value = aioseo()->helpers->pregReplace( '#%category%#', '', $value );
							$value = aioseo()->helpers->pregReplace( '#%excerpt%#', '', $value );
						}
						aioseo()->dynamicOptions->searchAppearance->postTypes->$postType->metaDescription =
							aioseo()->helpers->sanitizeOption( aioseo()->importExport->rankMath->helpers->macrosToSmartTags( $value ) );
						break;
					case 'custom_robots':
						aioseo()->dynamicOptions->searchAppearance->postTypes->$postType->advanced->robotsMeta->default = 'off' === $value;
						break;
					case 'robots':
						if ( ! empty( $value ) ) {
							foreach ( $value as $robotsName ) {
								if ( 'index' === $robotsName ) {
									continue;
								}

								if ( 'noindex' === $robotsName ) {
									aioseo()->dynamicOptions->searchAppearance->postTypes->{$postType}->show = false;
								}

								aioseo()->dynamicOptions->searchAppearance->postTypes->$postType->advanced->robotsMeta->$robotsName = true;
							}
						}
						break;
					case 'advanced_robots':
						if ( ! empty( $value['max-snippet'] ) ) {
							aioseo()->dynamicOptions->searchAppearance->postTypes->$postType->advanced->robotsMeta->maxSnippet = intval( $value['max-snippet'] );
						}
						if ( isset( $value['max-video-preview'] ) && is_numeric( $value['max-video-preview'] ) ) {
							aioseo()->dynamicOptions->searchAppearance->postTypes->$postType->advanced->robotsMeta->maxVideoPreview = intval( $value['max-video-preview'] );
						}
						if ( ! empty( $value['max-image-preview'] ) ) {
							aioseo()->dynamicOptions->searchAppearance->postTypes->$postType->advanced->robotsMeta->maxImagePreview =
								aioseo()->helpers->sanitizeOption( $value['max-image-preview'] );
						}
						break;
					case 'add_meta_box':
						aioseo()->dynamicOptions->searchAppearance->postTypes->$postType->advanced->showMetaBox = 'on' === $value;
						break;
					case 'default_rich_snippet':
						$value = aioseo()->helpers->pregReplace( '#\s#', '', $value );
						if ( 'off' === lcfirst( $value ) || in_array( $postType, [ 'page', 'attachment' ], true ) ) {
							aioseo()->dynamicOptions->searchAppearance->postTypes->$postType->schemaType = 'none';
							break;
						}
						if ( in_array( ucfirst( $value ), ImportExport\SearchAppearance::$supportedSchemaGraphs, true ) ) {
							aioseo()->dynamicOptions->searchAppearance->postTypes->$postType->schemaType = ucfirst( $value );
						}
						break;
					case 'default_article_type':
						if ( in_array( $postType, [ 'page', 'attachment' ], true ) ) {
							break;
						}
						$value = aioseo()->helpers->pregReplace( '#\s#', '', $value );
						if ( in_array( ucfirst( $value ), ImportExport\SearchAppearance::$supportedArticleGraphs, true ) ) {
							aioseo()->dynamicOptions->searchAppearance->postTypes->$postType->articleType = ucfirst( $value );
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
			'description'
		];

		foreach ( aioseo()->helpers->getPublicPostTypes( true, true ) as $postType ) {
			foreach ( $this->options as $name => $value ) {
				if ( ! preg_match( "#^pt_{$postType}_archive_(.*)$#", $name, $match ) || ! in_array( $match[1], $supportedSettings, true ) ) {
					continue;
				}

				switch ( $match[1] ) {
					case 'title':
						aioseo()->dynamicOptions->searchAppearance->archives->$postType->title =
							aioseo()->helpers->sanitizeOption( aioseo()->importExport->rankMath->helpers->macrosToSmartTags( $value, 'archive' ) );
						break;
					case 'description':
						aioseo()->dynamicOptions->searchAppearance->archives->$postType->metaDescription =
							aioseo()->helpers->sanitizeOption( aioseo()->importExport->rankMath->helpers->macrosToSmartTags( $value, 'archive' ) );
						break;
					default:
						break;
				}
			}
		}
	}


	/**
	 * Migrates the robots meta settings.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	private function migrateRobotMetaSettings() {
		// Reset existing values first.
		foreach ( $this->robotMetaSettings as $robotsMetaName ) {
			aioseo()->options->searchAppearance->advanced->globalRobotsMeta->$robotsMetaName = false;
		}

		if ( ! empty( $this->options['robots_global'] ) ) {
			foreach ( $this->options['robots_global'] as $robotsName ) {
				if ( 'index' === $robotsName ) {
					continue;
				}
				aioseo()->options->searchAppearance->advanced->globalRobotsMeta->default     = false;
				aioseo()->options->searchAppearance->advanced->globalRobotsMeta->$robotsName = true;
			}
		}

		if ( ! empty( $this->options['advanced_robots_global'] ) ) {
			aioseo()->options->searchAppearance->advanced->globalRobotsMeta->default = false;

			if ( ! empty( $this->options['robots_global']['max-snippet'] ) ) {
				aioseo()->options->searchAppearance->advanced->globalRobotsMeta->maxSnippet = intval( $this->options['robots_global']['max-snippet'] );
			}
			if ( isset( $this->options['robots_global']['max-video-preview'] ) && is_numeric( $this->options['robots_global']['max-video-preview'] ) ) {
				aioseo()->options->searchAppearance->advanced->globalRobotsMeta->maxVideoPreview = intval( $this->options['robots_global']['max-video-preview'] );
			}
			if ( ! empty( $this->options['robots_global']['max-image-preview'] ) ) {
				aioseo()->options->searchAppearance->advanced->globalRobotsMeta->maxImagePreview =
					aioseo()->helpers->sanitizeOption( $this->options['robots_global']['max-image-preview'] );
			}
		}

		if ( ! empty( $this->options['noindex_paginated_pages'] ) ) {
			aioseo()->options->searchAppearance->advanced->globalRobotsMeta->default          = false;
			aioseo()->options->searchAppearance->advanced->globalRobotsMeta->noindexPaginated = 'on' === $this->options['noindex_paginated_pages'];
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
		if ( empty( $this->options['knowledgegraph_type'] ) ) {
			return;
		}

		aioseo()->options->searchAppearance->global->schema->siteRepresents =
			'company' === $this->options['knowledgegraph_type'] ? 'organization' : 'person';

		if ( ! empty( $this->options['knowledgegraph_name'] ) && 'company' === $this->options['knowledgegraph_type'] ) {
			aioseo()->options->searchAppearance->global->schema->organizationName = aioseo()->helpers->sanitizeOption( $this->options['knowledgegraph_name'] );
		} elseif ( ! empty( $this->options['knowledgegraph_logo'] ) ) {
			aioseo()->options->searchAppearance->global->schema->person     = 'manual';
			aioseo()->options->searchAppearance->global->schema->personName = aioseo()->helpers->sanitizeOption( $this->options['knowledgegraph_name'] );
		}

		if ( ! empty( $this->options['knowledgegraph_logo'] ) && 'company' === $this->options['knowledgegraph_type'] ) {
			aioseo()->options->searchAppearance->global->schema->organizationLogo = esc_url( $this->options['knowledgegraph_logo'] );
		} elseif ( ! empty( $this->options['knowledgegraph_logo'] ) ) {
			aioseo()->options->searchAppearance->global->schema->person     = 'manual';
			aioseo()->options->searchAppearance->global->schema->personLogo = esc_url( $this->options['knowledgegraph_logo'] );
		}

		$this->migrateKnowledgeGraphPhoneNumber();
	}

	/**
	 * Migrates the Knowledge Graph phone number.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	private function migrateKnowledgeGraphPhoneNumber() {
		if ( empty( $this->options['phone'] ) ) {
			return;
		}

		$phoneNumber = aioseo()->helpers->sanitizeOption( $this->options['phone'] );
		if ( ! preg_match( '#\+\d+#', $phoneNumber ) ) {
			$notification = Models\Notification::getNotificationByName( 'v3-migration-schema-number' );
			if ( $notification->notification_name ) {
				return;
			}

			Models\Notification::addNotification( [
				'slug'              => uniqid(),
				'notification_name' => 'v3-migration-schema-number',
				'title'             => __( 'Invalid Phone Number for Knowledge Graph', 'all-in-one-seo-pack' ),
				'content'           => sprintf(
					// Translators: 1 - The phone number.
					__( 'We were unable to import the phone number that you previously entered for your Knowledge Graph schema markup.
					As it needs to be internationally formatted, please enter it (%1$s) with the country code, e.g. +1 (555) 555-1234.', 'all-in-one-seo-pack' ),
					"<strong>$phoneNumber</strong>"
				),
				'type'              => 'warning',
				'level'             => [ 'all' ],
				'button1_label'     => __( 'Fix Now', 'all-in-one-seo-pack' ),
				'button1_action'    => 'http://route#aioseo-search-appearance&aioseo-scroll=schema-graph-phone&aioseo-highlight=schema-graph-phone:schema-markup',
				'button2_label'     => __( 'Remind Me Later', 'all-in-one-seo-pack' ),
				'button2_action'    => 'http://action#notification/v3-migration-schema-number-reminder',
				'start'             => gmdate( 'Y-m-d H:i:s' )
			] );

			return;
		}
		aioseo()->options->searchAppearance->global->schema->phone = $phoneNumber;
	}

	/**
	 * Migrates the Social Meta settings.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	private function migrateSocialMetaSettings() {
		if ( ! empty( $this->options['open_graph_image'] ) ) {
			$defaultImage = esc_url( $this->options['open_graph_image'] );
			aioseo()->options->social->facebook->general->defaultImagePosts = $defaultImage;
			aioseo()->options->social->twitter->general->defaultImagePosts  = $defaultImage;
		}

		if ( ! empty( $this->options['social_url_facebook'] ) ) {
			aioseo()->options->social->profiles->urls->facebookPageUrl = esc_url( $this->options['social_url_facebook'] );
		}

		if ( ! empty( $this->options['facebook_author_urls'] ) ) {
			aioseo()->options->social->facebook->advanced->enable    = true;
			aioseo()->options->social->facebook->advanced->authorUrl = esc_url( $this->options['facebook_author_urls'] );
		}

		if ( ! empty( $this->options['facebook_admin_id'] ) ) {
			aioseo()->options->social->facebook->advanced->enable  = true;
			aioseo()->options->social->facebook->advanced->adminId = aioseo()->helpers->sanitizeOption( $this->options['facebook_admin_id'] );
		}

		if ( ! empty( $this->options['facebook_app_id'] ) ) {
			aioseo()->options->social->facebook->advanced->enable = true;
			aioseo()->options->social->facebook->advanced->appId  = aioseo()->helpers->sanitizeOption( $this->options['facebook_app_id'] );
		}

		if ( ! empty( $this->options['twitter_author_names'] ) ) {
			aioseo()->options->social->profiles->urls->twitterUrl =
				'https://x.com/' . aioseo()->helpers->sanitizeOption( $this->options['twitter_author_names'] );
		}

		if ( ! empty( $this->options['twitter_card_type'] ) ) {
			preg_match( '#large#', $this->options['twitter_card_type'], $match );
			aioseo()->options->social->twitter->general->defaultCardType = ! empty( $match ) ? 'summary_large_image' : 'summary';
		}
	}

	/**
	 * Migrates the default social image for posts.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	private function migrateDefaultPostSocialImage() {
		if ( ! empty( $this->options['open_graph_image'] ) ) {
			$defaultImage = esc_url( $this->options['open_graph_image'] );
			aioseo()->options->social->facebook->general->defaultImagePosts = $defaultImage;
			aioseo()->options->social->twitter->general->defaultImagePosts  = $defaultImage;
		}
	}
}