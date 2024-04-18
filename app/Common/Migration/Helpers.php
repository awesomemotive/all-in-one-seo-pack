<?php
namespace AIOSEO\Plugin\Common\Migration;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AIOSEO\Plugin\Common\Models;

/**
 * Contains a number of helper functions for the V3 migration.
 *
 * @since 4.0.0
 */
class Helpers {
	/**
	 * Maps a list of old settings from V3 to their counterparts in V4.
	 *
	 * @since 4.0.0
	 *
	 * @param  array $mappings      The old settings, mapped to their new settings.
	 * @param  array $group         The old settings group.
	 * @param  bool  $convertMacros Whether to convert the old V3 macros to V4 smart tags.
	 * @return void
	 */
	public function mapOldToNew( $mappings, $group, $convertMacros = false ) {
		if (
			! is_array( $mappings ) ||
			! is_array( $group ) ||
			! count( $mappings ) ||
			! count( $group )
		) {
			return;
		}

		$mainOptions    = aioseo()->options->noConflict();
		$dynamicOptions = aioseo()->dynamicOptions->noConflict();
		foreach ( $mappings as $name => $values ) {
			if ( ! isset( $group[ $name ] ) ) {
				continue;
			}

			$error      = false;
			$options    = ! empty( $values['dynamic'] ) ? $dynamicOptions : $mainOptions;
			$lastOption = '';
			for ( $i = 0; $i < count( $values['newOption'] ); $i++ ) {
				$lastOption = $values['newOption'][ $i ];
				if ( ! $options->has( $lastOption, false ) ) {
					$error = true;
					break;
				}

				if ( count( $values['newOption'] ) - 1 !== $i ) {
					$options = $options->$lastOption;
				}
			}

			if ( $error ) {
				continue;
			}

			switch ( $values['type'] ) {
				case 'boolean':
					if ( ! empty( $group[ $name ] ) ) {
						$options->$lastOption = true;
						break;
					}
					$options->$lastOption = false;
					break;
				case 'integer':
				case 'float':
					$value = aioseo()->helpers->sanitizeOption( $group[ $name ] );
					if ( $value ) {
						$options->$lastOption = $value;
					}
					break;
				default:
					$value = $group[ $name ];
					if ( $convertMacros ) {
						$value = $this->macrosToSmartTags( $value );
					}
					$options->$lastOption = aioseo()->helpers->sanitizeOption( $value );
					break;
			}
		}
	}

	/**
	 * Replaces the macros from V3 with our new Smart Tags from V4.
	 *
	 * @since 4.0.0
	 *
	 * @param  string $string The string.
	 * @return string $string The converted string.
	 */
	public function macrosToSmartTags( $string ) {
		$macros = [
			'%site_title%'             => '#site_title',
			'%blog_title%'             => '#site_title',
			'%site_description%'       => '#tagline',
			'%blog_description%'       => '#tagline',
			'%wp_title%'               => '#post_title',
			'%post_title%'             => '#post_title',
			'%page_title%'             => '#post_title',
			'%post_date%'              => '#post_date',
			'%post_month%'             => '#post_month',
			'%post_year%'              => '#post_year',
			'%date%'                   => '#archive_date',
			'%day%'                    => '#post_day',
			'%month%'                  => '#post_month',
			'%monthnum%'               => '#post_month',
			'%year%'                   => '#post_year',
			'%current_date%'           => '#current_date',
			'%current_day%'            => '#current_day',
			'%current_month%'          => '#current_month',
			'%current_month_i18n%'     => '#current_month',
			'%current_year%'           => '#current_year',
			'%category_title%'         => '#taxonomy_title',
			'%tag%'                    => '#taxonomy_title',
			'%tag_title%'              => '#taxonomy_title',
			'%archive_title%'          => '#archive_title',
			'%taxonomy_title%'         => '#taxonomy_title',
			'%taxonomy_description%'   => '#taxonomy_description',
			'%tag_description%'        => '#taxonomy_description',
			'%category_description%'   => '#taxonomy_description',
			'%author%'                 => '#author_name',
			'%search%'                 => '#search_term',
			'%page%'                   => '#page_number',
			'%site_link%'              => '#site_link',
			'%site_link_raw%'          => '#site_link_alt',
			'%post_link%'              => '#post_link',
			'%post_link_raw%'          => '#post_link_alt',
			'%author_name%'            => '#author_name',
			'%author_link%'            => '#author_link',
			'%image_title%'            => '#image_title',
			'%image_seo_title%'        => '#image_seo_title',
			'%image_seo_description%'  => '#image_seo_description',
			'%post_seo_title%'         => '#post_seo_title',
			'%post_seo_description%'   => '#post_seo_description',
			'%alt_tag%'                => '#alt_tag',
			'%description%'            => '#description',
			// These need to run last so we don't replace other known tags.
			'%.*_title%'               => '#post_title',
			'%[^%]*_author_login%'     => '#author_first_name #author_last_name',
			'%[^%]*_author_nicename%'  => '#author_first_name #author_last_name',
			'%[^%]*_author_firstname%' => '#author_first_name',
			'%[^%]*_author_lastname%'  => '#author_last_name',
		];

		if ( preg_match_all( '#%cf_([^%]*)%#', $string, $matches ) && ! empty( $matches[1] ) ) {
			foreach ( $matches[1] as $name ) {
				if ( preg_match( '#\s#', $name ) ) {
					$notification = Models\Notification::getNotificationByName( 'v3-migration-custom-field' );
					if ( ! $notification->notification_name ) {
						Models\Notification::addNotification( [
							'slug'              => uniqid(),
							'notification_name' => 'v3-migration-custom-field',
							'title'             => __( 'Custom field names with spaces detected', 'all-in-one-seo-pack' ),
							'content'           => sprintf(
								// Translators: 1 - The plugin short name ("AIOSEO"), 2 - Same as previous.
								__( '%1$s has detected that you have one or more custom fields with spaces in their name.
								In order for %2$s to correctly parse these custom fields, their names cannot contain any spaces.', 'all-in-one-seo-pack' ),
								AIOSEO_PLUGIN_SHORT_NAME,
								AIOSEO_PLUGIN_SHORT_NAME
							),
							'type'              => 'warning',
							'level'             => [ 'all' ],
							'button1_label'     => __( 'Remind Me Later', 'all-in-one-seo-pack' ),
							'button1_action'    => 'http://action#notification/v3-migration-custom-field-reminder',
							'start'             => gmdate( 'Y-m-d H:i:s' )
						] );
					}
				} else {
					$string = aioseo()->helpers->pregReplace( "#%cf_$name%#", "#custom_field-$name", $string );
				}
			}
		}

		if ( preg_match_all( '#%tax_([^%]*)%#', $string, $matches ) && ! empty( $matches[1] ) ) {
			foreach ( $matches[1] as $name ) {
				if ( ! preg_match( '#\s#', $name ) ) {
					$string = aioseo()->helpers->pregReplace( "#%tax_$name%#", "#tax_name-$name", $string );
				}
			}
		}

		foreach ( $macros as $macro => $tag ) {
			$string = aioseo()->helpers->pregReplace( "#$macro(?![a-zA-Z0-9_])#im", $tag, $string );
		}

		$string = preg_replace( '/%([a-f0-9]{2}[^%]*)%/i', '#$1#', $string );

		return $string;
	}

	/**
	 * Converts the old comma-separated keywords format to the new JSON format.
	 *
	 * @since 4.0.0
	 *
	 * @param  string $keywords A comma-separated list of keywords.
	 * @return string $keywords The keywords formatted in JSON.
	 */
	public function oldKeywordsToNewKeywords( $keywords ) {
		if ( ! $keywords ) {
			return '';
		}

		$oldKeywords = array_filter( explode( ',', $keywords ) );
		if ( ! is_array( $oldKeywords ) ) {
			return '';
		}

		$keywords = [];
		foreach ( $oldKeywords as $oldKeyword ) {
			$oldKeyword = aioseo()->helpers->sanitizeOption( $oldKeyword );

			$keyword        = new \stdClass();
			$keyword->label = $oldKeyword;
			$keyword->value = $oldKeyword;

			$keywords[] = $keyword;
		}

		return $keywords;
	}

	/**
	 * Resets the plugin so that the migration can run again.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public static function redoMigration() {
		aioseo()->core->db->delete( 'options' )
			->whereRaw( "`option_name` LIKE 'aioseo_options_internal%'" )
			->run();

		aioseo()->core->cache->delete( 'v3_migration_in_progress_posts' );
		aioseo()->core->cache->delete( 'v3_migration_in_progress_terms' );

		aioseo()->actionScheduler->unschedule( 'aioseo_migrate_post_meta' );
		aioseo()->actionScheduler->unschedule( 'aioseo_migrate_term_meta' );
	}
}