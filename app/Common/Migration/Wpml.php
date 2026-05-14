<?php
namespace AIOSEO\Plugin\Common\Migration;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Migrates the WPML settings from V3.
 *
 * @since 4.0.0
 */
class Wpml {
	/**
	 * Class constructor.
	 *
	 * @since 4.0.0
	 */
	public function __construct() {
		// If the tables don't exist (could happen), return early.
		if ( ! aioseo()->core->db->externalTableExists( 'icl_strings' ) && ! aioseo()->core->db->externalTableExists( 'icl_string_translations' ) ) {
			return;
		}

		$strings = [
			'[aioseop_options]aiosp_home_title'       => '[aioseo_options_localized]searchAppearance_global_siteTitle',
			'[aioseop_options]aiosp_home_description' => '[aioseo_options_localized]searchAppearance_global_metaDescription',
			'[aioseop_options]aiosp_home_keywords'    => '[aioseo_options_localized]searchAppearance_global_keywords'
		];

		try {
			$v3Results = aioseo()->core->db->start( 'icl_strings' )
				->where( 'context', 'admin_texts_aioseop_options' )
				->whereIn( 'name', array_keys( $strings ) )
				->run()
				->result();

			$v4Results = aioseo()->core->db->start( 'icl_strings' )
				->where( 'context', 'admin_texts_aioseo_options_localized' )
				->whereIn( 'name', array_values( $strings ) )
				->run()
				->result();

			if ( ! empty( $v3Results ) ) {
				foreach ( $v3Results as $result ) {
					$translations = aioseo()->core->db->start( 'icl_string_translations' )
						->where( 'string_id', $result->id )
						->run()
						->result();

					if ( empty( $translations ) ) {
						continue;
					}

					$v4ResultId = null;
					if ( ! empty( $v4Results ) ) {
						foreach ( $v4Results as $r ) {
							if ( $r->name === $strings[ $result->name ] ) {
								$v4ResultId = $r->id;
								break;
							}
						}
					}

					if ( ! $v4ResultId ) {
						$v4ResultId = aioseo()->core->db
							->insert( 'icl_strings' )
							->set( [
								'language'                => $result->language,
								'context'                 => 'admin_texts_aioseo_options_localized',
								'name'                    => $strings[ $result->name ],
								'value'                   => $result->value,
								'string_package_id'       => $result->string_package_id,
								'location'                => $result->location,
								'wrap_tag'                => $result->wrap_tag,
								'type'                    => $result->type,
								'title'                   => $result->title,
								'status'                  => $result->status,
								'gettext_context'         => $result->gettext_context,
								'domain_name_context_md5' => md5( 'admin_texts_aioseo_options_localized' . $strings[ $result->name ] ),
								'translation_priority'    => $result->translation_priority,
								'word_count'              => $result->word_count
							] )
							->run()
							->insertId();
					}

					foreach ( $translations as $translation ) {
						// Check if the translation exists first or we'll get a DB error.
						$v4Translation = aioseo()->core->db->start( 'icl_string_translations' )
							->where( 'string_id', $v4ResultId )
							->where( 'language', $translation->language )
							->run()
							->result();

						if ( ! empty( $v4Translation ) ) {
							aioseo()->core->db->update( 'icl_string_translations' )
								->where( 'string_id', $v4ResultId )
								->where( 'language', $translation->language )
								->set( [
									'value' => $translation->value
								] )
								->run();
							continue;
						}

						aioseo()->core->db
							->insert( 'icl_string_translations' )
							->set( [
								'string_id'           => $v4ResultId,
								'language'            => $translation->language,
								'status'              => $translation->status,
								'value'               => $translation->value,
								'mo_string'           => $translation->mo_string,
								'translator_id'       => $translation->translator_id,
								'translation_service' => $translation->translation_service,
								'batch_id'            => $translation->batch_id,
								'translation_date'    => $translation->translation_date
							] )
							->run();
					}
				}
			}
		} catch ( \Exception $e ) {
			// If there are any errors, let's just abort. We dont' want to do anything more.
		}
	}
}